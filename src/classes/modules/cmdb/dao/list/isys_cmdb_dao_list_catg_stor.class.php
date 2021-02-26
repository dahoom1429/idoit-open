<?php

/**
 * i-doit
 *
 * DAO: ObjectType list for storage devices
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_stor extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__STORAGE_DEVICE');
    }

    /**
     * Return constant of category type-
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     *
     * @param  array &$p_row
     */
    public function modify_row(&$p_row)
    {
        if ($p_row["isys_stor_type__const"] == "C__STOR_TYPE_DEVICE_RAID_GRP") {
            // Compute total capacity of RAID group.
            $l_dao = new isys_cmdb_dao_category_g_stor($this->m_db);
            $l_res = $l_dao->get_devices(null, $_GET[C__CMDB__GET__OBJECT], $p_row["isys_catg_stor_list__id"], defined_or_default('C__STOR_TYPE_DEVICE_HD'));
            $l_numDisks = $l_res->num_rows();

            if (is_countable($l_res) && count($l_res) > 0) {
                $l_row = $l_res->get_row();
                $l_id = $l_row["isys_catg_stor_list__id"];
                $l_lo = isys_convert::memory($l_row["isys_catg_stor_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);

                while ($l_row = $l_res->get_row()) {
                    if (isys_convert::memory($l_row["isys_catg_stor_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD) < $l_lo) {
                        $l_lo = isys_convert::memory($l_row["isys_catg_stor_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);
                        $l_lo = isys_convert::formatNumber($l_lo);
                    }

                    if ($l_row["isys_catg_stor_list__hotspare"] == "1") {
                        $l_numDisks--;
                    }
                }

                // @todo  ID-2188
                $p_row["isys_catg_stor_list__capacity"] = "<div id=\"raidcapacity_" . $l_id . "\"></div>" .
                    "<script type=\"text/javascript\">" .
                    "idoit.Require.require('smartyRaid', function () { " .
                    "raidcalc('" . $l_numDisks . "', '" . $l_lo . "', '" . $p_row["isys_stor_raid_level__title"] . "', 'raidcapacity_" . $l_id . "', null); " .
                    "});" .
                    "</script>";
            }
        } else {
            $p_row["isys_catg_stor_list__capacity"] = isys_convert::memory(
                $p_row["isys_catg_stor_list__capacity"],
                $p_row["isys_memory_unit__const"],
                C__CONVERT_DIRECTION__BACKWARD
            );
            $p_row["isys_catg_stor_list__capacity"] = isys_convert::formatNumber($p_row["isys_catg_stor_list__capacity"]) . " " . $p_row["isys_memory_unit__title"];
        }
    }

    /**
     * Returns array with table headers.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_catg_stor_list__title"       => "LC__CATG__STORAGE_TITLE",
            "isys_stor_type__title"            => "LC__CATG__STORAGE_TYPE",
            "isys_catg_stor_list__capacity"    => "LC__CATG__STORAGE_CAPACITY",
            "isys_catg_controller_list__title" => "LC__CATG__STORAGE_CONTROLLER",
            "isys_catg_stor_list__firmware"    => "LC__CATG__STORAGE_FIRMWARE"
        ];
    }
}
