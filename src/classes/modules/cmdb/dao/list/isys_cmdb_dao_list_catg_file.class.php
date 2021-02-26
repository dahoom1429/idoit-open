<?php

/**
 * i-doit
 *
 * DAO: ObjectType lists.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stuecken <dstuecken@synetics.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_file extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__FILE');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     *
     * @param    string  $p_table
     * @param    integer $p_objID
     * @param    null    $p_cRecStatus
     *
     * @return   isys_component_dao_result
     * @author   Niclas Potthast <npotthast@i-doit.org>
     * @author   Dennis Stuecken <dstuecken@synetics.de>
     */
    public function get_result($p_table = null, $p_objID, $p_cRecStatus = null)
    {
        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        $l_strSQL = "SELECT
			isys_catg_file_list__id,
			isys_catg_file_list__link,
			isys_file_version__isys_file_physical__id,
			isys_file_version__revision,
			isys_file_category__title,
			isys_obj__id,
			isys_obj__title,
			isys_obj_type__title,
			isys_catg_file_list__status
			FROM isys_catg_file_list
			LEFT JOIN isys_connection ON isys_connection__id = isys_catg_file_list__isys_connection__id
			LEFT JOIN isys_cats_file_list ON isys_cats_file_list__isys_obj__id = isys_connection__isys_obj__id
			LEFT JOIN isys_file_version ON isys_cats_file_list__isys_file_version__id=isys_file_version__id
			LEFT JOIN isys_obj ON isys_file_version__isys_obj__id = isys_obj__id
			LEFT JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			LEFT JOIN isys_file_category ON isys_file_category__id = isys_cats_file_list__isys_file_category__id
			WHERE isys_catg_file_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . "
			AND isys_catg_file_list__status = " . $this->convert_sql_int($l_cRecStatus) . "
			GROUP BY isys_catg_file_list__id";

        return $this->retrieve($l_strSQL);
    }

    /**
     * @param   array $p_row
     *
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function modify_row(&$p_row)
    {
        global $g_dirs;

        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');

        if ($p_row["isys_file_version__isys_file_physical__id"] !== null) {
            $allowedToView = true;

            if (isys_tenantsettings::get('auth.use-in-file-browser', false)) {
                $allowedToView = isys_auth_cmdb::instance()->is_allowed_to(isys_auth::VIEW, 'OBJ_ID/' . $p_row["isys_obj__id"]);
            }

            $l_dlgets = isys_module_request::get_instance()
                ->get_gets();
            $l_dlgets[C__GET__FILE_MANAGER] = "get";
            $l_dlgets[C__GET__FILE__ID] = $p_row["isys_file_version__isys_file_physical__id"];
            $l_dlgets[C__GET__MODULE_ID] = defined_or_default('C__MODULE__CMDB');

            $l_file_name = isys_application::instance()->container->get('language')
                    ->get($p_row['isys_obj_type__title']) . ' > ' . $p_row['isys_obj__title'];
            $l_download_link = isys_glob_build_url(urldecode(isys_glob_http_build_query($l_dlgets)));

            $p_row["download_file_name"] = $l_file_name;

            if ($allowedToView) {
                // ID-2223  Adding "event.stopPropagation();" stops the browser from opening the category itself.
                $p_row["download"] = '<a target="_blank" class="btn btn-small" href="' . $l_download_link . '" onclick="event.stopPropagation();">' .
                    '<img src="' . $g_dirs["images"] . '/icons/silk/disk.png" class="mr5" />' .
                    '<span>' . isys_application::instance()->container->get('language')->get('LC__UNIVERSAL__DOWNLOAD_FILE') . '</span>' .
                    '</a>';
            } else {
                $p_row["download"] = $l_empty_value;
            }
        } else {
            $p_row["download_file_name"] = $l_empty_value;
            $p_row["download"] = $l_empty_value;
        }

        if (!empty($p_row["isys_catg_file_list__link"])) {
            $p_row["isys_catg_file_list__link"] = isys_helper_link::create_anker($p_row["isys_catg_file_list__link"], '_blank',
                '<img src="' . $g_dirs["images"] . 'icons/silk/link.png" class="vam" />&nbsp;');
        } else {
            $p_row["isys_catg_file_list__link"] = $l_empty_value;
        }
    }

    /**
     * Method for retrieving the category list header.
     *
     * @return   array
     * @author   Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_fields()
    {
        return [
            'download_file_name'          => 'LC__CMDB__CATG__FILE_OBJ_FILE',
            'isys_file_version__revision' => 'LC_UNIVERSAL__REVISION',
            'isys_catg_file_list__link'   => 'Link',
            'isys_file_category__title'   => 'LC__CMDB__CATG__GLOBAL_CATEGORY',
            'download'                    => 'LC__CMDB__CATS__FILE_DOWNLOAD'
        ];
    }
}
