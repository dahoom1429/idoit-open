<?php

/**
 * i-doit
 *
 * DAO: ObjectType list for manuals
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_file_version extends isys_component_dao_category_table_list
{
    /**
     * Return category constant.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__FILE_VERSIONS');
    }

    /**
     * Return category type constant.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     * Modify row method.
     *
     * @param   array &$p_arrRow
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function modify_row(&$p_arrRow)
    {
        global $g_dirs;

        $p_arrRow['isys_file_size'] = isys_tenantsettings::get('gui.empty_value', '-');
        $l_filepath = $p_arrRow["isys_file_physical__filename"] ? isys_application::instance()->getUploadFilePath($p_arrRow["isys_file_physical__filename"]) : '';

        if ($l_filepath && file_exists($l_filepath)) {
            $l_filesize = filesize($l_filepath);

            if ($l_filesize > 0) {
                $l_dlgets = isys_module_request::get_instance()
                    ->get_gets();
                $l_dlgets[C__GET__FILE_MANAGER] = "get";
                $l_dlgets[C__GET__FILE__ID] = $p_arrRow["isys_file_version__isys_file_physical__id"];
                $l_dlgets[C__GET__MODULE_ID] = defined_or_default('C__MODULE__CMDB');

                $p_arrRow['isys_download'] = '<a target="_blank" href="' . isys_glob_build_url(urldecode(isys_glob_http_build_query($l_dlgets))) . '">' . '<img src="' .
                    $g_dirs["images"] . '/icons/silk/disk.png" class="vam" /><span class="ml5 vam">' . isys_application::instance()->container->get('language')
                        ->get('LC__UNIVERSAL__DOWNLOAD_FILE') . '</span>' . '</a>';

                if ($l_filesize < 100000) {
                    $p_arrRow['isys_file_size'] = isys_convert::memory($l_filesize, 'C__MEMORY_UNIT__KB', C__CONVERT_DIRECTION__BACKWARD);
                    $p_arrRow['isys_file_size'] = isys_convert::formatNumber($p_arrRow['isys_file_size']) . ' ' . isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__MEMORY_UNIT__KB');
                } else {
                    $p_arrRow['isys_file_size'] = isys_convert::memory($l_filesize, 'C__MEMORY_UNIT__MB', C__CONVERT_DIRECTION__BACKWARD);
                    $p_arrRow['isys_file_size'] = isys_convert::formatNumber($p_arrRow['isys_file_size']) . ' ' . isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__MEMORY_UNIT__MB');
                }
            }
        }

        // Formatting the upload-date.
        $p_arrRow["isys_file_physical__date_uploaded"] = isys_locale::get_instance()
            ->fmt_date($p_arrRow["isys_file_physical__date_uploaded"]);
    }

    /**
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_fields()
    {
        return [
            "isys_file_physical__filename_original" => "LC__CMDB__CATS__FILE_NAME",
            "isys_file_version__title"              => "LC__CMDB__CATS__FILE_TITLE",
            "isys_file_version__revision"           => "LC__CMDB__CATS__FILE_REVISION",
            "isys_file_physical__date_uploaded"     => "LC__CMDB__CATS__FILE_UPLOAD_DATE",
            "isys_file_size"                        => "LC__CMDB__CATS__FILE__SIZE",
            "isys_download"                         => "LC__CMDB__CATS__FILE_DOWNLOAD"
        ];
    }

    /**
     * The isys_component_dao_object_table_list constructor differentiates if $p_cat is an instance of isys_cmdb_dao_category or isys_component database.
     *
     * @param  isys_component_database $p_db
     */
    public function __construct($p_db)
    {
        $this->set_rec_status_list(false);
        parent::__construct($p_db);
    }
}
