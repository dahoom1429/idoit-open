<?php

/**
 * i-doit
 *
 * DAO: Gloabl category 'drive'.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_drive extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__DRIVE');
    }

    /**
     * Return constant of category type
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     *
     * @param   string  $p_table
     * @param   integer $p_object_id
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     * @author  Niclas Potthast <npotthast@i-doit.de>
     * @author  Dennis Stuecken <dstuecken@i-doit.de>
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function get_result($p_table = null, $p_object_id, $p_cRecStatus = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_drive_list
			LEFT OUTER JOIN isys_filesystem_type ON isys_catg_drive_list__isys_filesystem_type__id = isys_filesystem_type__id
			LEFT OUTER JOIN isys_memory_unit ON isys_catg_drive_list__isys_memory_unit__id = isys_memory_unit__id
			LEFT OUTER JOIN isys_catd_drive_type ON isys_catg_drive_list__isys_catd_drive_type__id = isys_catd_drive_type__id
			LEFT OUTER JOIN isys_stor_raid_level ON isys_catg_drive_list__isys_stor_raid_level__id = isys_stor_raid_level__id
			LEFT OUTER JOIN isys_catg_stor_list ON isys_catg_drive_list__isys_catg_stor_list__id = isys_catg_stor_list__id
			LEFT OUTER JOIN isys_catg_ldevclient_list ON isys_catg_drive_list__isys_catg_ldevclient_list__id = isys_catg_ldevclient_list__id
			LEFT OUTER JOIN isys_catg_raid_list ON isys_catg_drive_list__isys_catg_raid_list__id = isys_catg_raid_list__id
			WHERE isys_catg_drive_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id);

        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        if (!empty($l_cRecStatus)) {
            $l_sql .= ' AND isys_catg_drive_list__status = ' . $l_cRecStatus;
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        $l_loc = isys_locale::get_instance();

        if ($p_arrRow["isys_catd_drive_type__const"] == "C__CATD_DRIVE_TYPE__RAID_GROUP") {
            $p_arrRow["isys_catd_drive_type__title"] = isys_application::instance()->container->get('language')
                    ->get($p_arrRow["isys_catd_drive_type__title"]) . " (" . $p_arrRow["isys_stor_raid_level__title"] . ")";
        } else {
            if (!empty($p_arrRow["isys_filesystem_type__title"])) {
                $p_arrRow["isys_catd_drive_type__title"] = isys_application::instance()->container->get('language')
                        ->get($p_arrRow["isys_catd_drive_type__title"]) . " (" . isys_application::instance()->container->get('language')
                        ->get($p_arrRow["isys_filesystem_type__title"]) . ")";
            }
        }

        $p_arrRow["isys_catg_drive_list__capacity"] = $l_loc->fmt_numeric(isys_convert::memory(
            $p_arrRow["isys_catg_drive_list__capacity"],
            $p_arrRow["isys_memory_unit__const"],
            C__CONVERT_DIRECTION__BACKWARD
        )) . " " . $p_arrRow["isys_memory_unit__title"];

        $l_free_space_unit = isys_factory_cmdb_dialog_dao::get_instance('isys_memory_unit', isys_application::instance()->database)
            ->get_data($p_arrRow["isys_catg_drive_list__free_space__isys_memory_unit__id"]);

        $p_arrRow["isys_catg_drive_list__free_space"] = $l_loc->fmt_numeric(isys_convert::memory(
            $p_arrRow["isys_catg_drive_list__free_space"],
            $l_free_space_unit['isys_memory_unit__const'],
            C__CONVERT_DIRECTION__BACKWARD
        )) . " " . $l_free_space_unit['isys_memory_unit__title'];

        $l_used_space_unit = isys_factory_cmdb_dialog_dao::get_instance('isys_memory_unit', isys_application::instance()->database)
            ->get_data($p_arrRow["isys_catg_drive_list__used_space__isys_memory_unit__id"]);

        $p_arrRow["isys_catg_drive_list__used_space"] = $l_loc->fmt_numeric(isys_convert::memory(
            $p_arrRow["isys_catg_drive_list__used_space"],
            $l_used_space_unit['isys_memory_unit__const'],
            C__CONVERT_DIRECTION__BACKWARD
        )) . " " . $l_used_space_unit['isys_memory_unit__title'];

        if ($p_arrRow["isys_catg_drive_list__const"] == "C__CATG__STORAGE") {
            $p_arrRow["device_title"] = $p_arrRow["isys_catg_stor_list__title"];
        } elseif ($p_arrRow["isys_catg_drive_list__const"] == "C__CATG__RAID") {
            $p_arrRow["device_title"] = $p_arrRow["isys_catg_raid_list__title"];
        } elseif ($p_arrRow["isys_catg_drive_list__const"] == "C__CATG__LDEV_CLIENT") {
            $p_arrRow["device_title"] = $p_arrRow["isys_catg_ldevclient_list__title"];
        }
    }

    /**
     *
     * @return  array
     * @author  Niclas Potthast <npotthast@i-doit.de>
     */
    public function get_fields()
    {
        return [
            "isys_catg_drive_list__title"       => "LC__CATD__DRIVE_TITLE",
            "isys_catd_drive_type__title"       => "LC__CATD__DRIVE_TYPE",
            "isys_catg_drive_list__driveletter" => "LC__CATD__DRIVE_LETTER",
            "device_title"                      => "LC__CATD__DRIVE_DEVICE",
            "isys_catg_drive_list__capacity"    => "LC__CATD__DRIVE_CAPACITY",
            "isys_catg_drive_list__free_space"  => 'LC__CMDB__CATG__DRIVE__FREE_SPACE',
            "isys_catg_drive_list__used_space"  => 'LC__CMDB__CATG__DRIVE__USED_SPACE',
            "isys_catg_drive_list__firmware"    => 'LC__CMDB__CATG__DRIVE__FIRMWARE'
        ];
    }
}
