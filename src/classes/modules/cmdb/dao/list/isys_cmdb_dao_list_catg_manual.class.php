<?php

/**
 * i-doit
 *
 * DAO: ObjectType list for manuals
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_manual extends isys_component_dao_category_table_list
{
    /**
     * Return category constant.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__MANUAL');
    }

    /**
     * Return category type constant.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * @param   array &$p_row
     *
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function modify_row(&$p_row)
    {
        global $g_dirs;
        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');

        if ($p_row["isys_file_version__isys_file_physical__id"] !== null) {
            $l_file_obj = $this->m_cat_dao->get_object_by_id($p_row['isys_connection__isys_obj__id'])
                ->get_row();

            $l_dlgets = isys_module_request::get_instance()
                ->get_gets();
            $l_dlgets[C__GET__FILE_MANAGER] = "get";
            $l_dlgets[C__GET__FILE__ID] = $p_row["isys_file_version__isys_file_physical__id"];
            $l_dlgets[C__GET__MODULE_ID] = defined_or_default('C__MODULE__CMDB');

            $l_file_name = isys_application::instance()->container->get('language')
                    ->get($l_file_obj['isys_obj_type__title']) . ' > ' . $l_file_obj['isys_obj__title'];
            $l_download_link = isys_glob_build_url(urldecode(isys_glob_http_build_query($l_dlgets)));

            $p_row["download_file_name"] = $l_file_name;
            $p_row["download"] = '<a target="_blank" href="' . $l_download_link . '"><img src="' . $g_dirs["images"] . '/icons/silk/disk.png" class="vam" />&nbsp;' .
                isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__DOWNLOAD_FILE') . '</a>';
        } else {
            $p_row["download_file_name"] = $l_empty_value;
            $p_row["download"] = $l_empty_value;
        }
    }

    /**
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_catg_manual_list__title' => 'LC__CMDB__CATG__MANUAL_TITLE',
            'download_file_name'           => 'LC__CMDB__CATG__FILE_OBJ_FILE',
            'isys_file_version__revision'  => 'LC_UNIVERSAL__REVISION',
            'download'                     => 'LC__CMDB__CATS__FILE_DOWNLOAD'
        ];
    }
}
