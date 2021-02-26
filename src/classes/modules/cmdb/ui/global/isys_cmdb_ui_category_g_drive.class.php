<?php

/**
 * i-doit
 *
 * CMDB Drive: Global category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_drive extends isys_cmdb_ui_category_global
{
    /**
     * Show the detail-template for global category formfactor.
     *
     * @param   isys_cmdb_dao_category_g_drive $p_cat
     *
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $p_cat->get_general_data());

        if ($l_catdata["isys_catg_drive_list__const"] == "C__CATG__STORAGE") {
            $l_rules["C__CATD__DRIVE_DEVICE"]["p_strSelectedID"] = $l_catdata["isys_catg_drive_list__isys_catg_stor_list__id"] . "_" .
                $l_catdata["isys_catg_drive_list__const"];
        } else if ($l_catdata["isys_catg_drive_list__const"] == "C__CATG__RAID") {
            $l_rules["C__CATD__DRIVE_DEVICE"]["p_strSelectedID"] = $l_catdata["isys_catg_drive_list__isys_catg_raid_list__id"] . "_" .
                $l_catdata["isys_catg_drive_list__const"];
        } else if ($l_catdata["isys_catg_drive_list__const"] == "C__CATG__LDEV_CLIENT") {
            $l_rules["C__CATD__DRIVE_DEVICE"]["p_strSelectedID"] = $l_catdata["isys_catg_drive_list__isys_catg_ldevclient_list__id"] . "_" .
                $l_catdata["isys_catg_drive_list__const"];
        }

        $l_rules["C__CATD__DRIVE_CAPACITY"]["p_strValue"] = isys_convert::memory($l_catdata["isys_catg_drive_list__capacity"],
            $l_catdata["isys_catg_drive_list__isys_memory_unit__id"], C__CONVERT_DIRECTION__BACKWARD);

        $l_rules["C__CATD__DRIVE_RAIDGROUP"]["p_arData"] = $p_cat->callback_property_assigned_raid(isys_request::factory()
            ->set_object_id($_GET[C__CMDB__GET__OBJECT]));
        $l_rules["C__CATD__DRIVE_RAIDGROUP"]["p_strSelectedID"] = $l_catdata["isys_catg_drive_list__isys_catg_raid_list__id"];

        // LF: Looks like this and C__CATD__DRIVE_RAIDGROUP are the same... I'll just leave this here, so nothing breaks.
        $l_rules["C__CATD__SOFTWARE_RAID"]["p_arData"] = $l_rules["C__CATD__DRIVE_RAIDGROUP"]["p_arData"];
        $l_rules["C__CATD__SOFTWARE_RAID"]["p_strSelectedID"] = $l_catdata["isys_catg_drive_list__id__raid_pool"];

        $l_rules["C__CMDB__CATG__DRIVE__FREE_SPACE"]["p_strValue"] = isys_convert::memory($l_catdata["isys_catg_drive_list__free_space"], $l_catdata["free_space_unit"],
            C__CONVERT_DIRECTION__BACKWARD);
        $l_rules["C__CMDB__CATG__DRIVE__USED_SPACE"]["p_strValue"] = isys_convert::memory($l_catdata["isys_catg_drive_list__used_space"], $l_catdata["used_space_unit"],
            C__CONVERT_DIRECTION__BACKWARD);

        $l_rules["C__CATD__DRIVE_CAPACITY"]["p_strValue"] = isys_convert::formatNumber($l_rules["C__CATD__DRIVE_CAPACITY"]["p_strValue"]);
        $l_rules["C__CMDB__CATG__DRIVE__FREE_SPACE"]["p_strValue"] = isys_convert::formatNumber($l_rules["C__CMDB__CATG__DRIVE__FREE_SPACE"]["p_strValue"]);
        $l_rules["C__CMDB__CATG__DRIVE__USED_SPACE"]["p_strValue"] = isys_convert::formatNumber($l_rules["C__CMDB__CATG__DRIVE__USED_SPACE"]["p_strValue"]);

        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}