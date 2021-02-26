<?php

/**
 * i-doit
 *
 * CMDB Active Directory: Specific category Uninterruptible power supply (ups).
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_ups extends isys_cmdb_ui_category_specific
{
    /**
     * Show the detail-template for specific category ups.
     *
     * @param  isys_cmdb_dao_category_s_ups $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Make some additional rules.
        $l_ct_row = $p_cat->get_dialog("isys_unit_of_time", $l_catdata["isys_cats_ups_list__charge_time__isys_unit_of_time__id"])
            ->get_row();
        $l_ct_time = isys_convert::time($l_catdata["isys_cats_ups_list__charge_time"], $l_ct_row["isys_unit_of_time__const"], C__CONVERT_DIRECTION__BACKWARD);

        $l_at_row = $p_cat->get_dialog("isys_unit_of_time", $l_catdata["isys_cats_ups_list__autonomy_time__isys_unit_of_time__id"])
            ->get_row();
        $l_at_time = isys_convert::time($l_catdata["isys_cats_ups_list__autonomy_time"], $l_at_row["isys_unit_of_time__const"], C__CONVERT_DIRECTION__BACKWARD);

        $l_rules["C__CMDB__CATS__UPS__CHARGE_TIME"]["p_strValue"] = $l_ct_time;
        $l_rules["C__CMDB__CATS__UPS__AUTONOMY_TIME"]["p_strValue"] = $l_at_time;

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}