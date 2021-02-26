<?php

/**
 * i-doit
 *
 * CMDB Specific category EPS
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dsteucken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_eps extends isys_cmdb_ui_category_specific
{
    /**
     * Process method.
     *
     * @global  array                        $index_includes
     *
     * @param   isys_cmdb_dao_category_s_eps $p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        $l_rules["C__CMDB__CATS__EPS__FUEL_TANK"]["p_strValue"] = isys_convert::volume($l_catdata["isys_cats_eps_list__fuel_tank"],
            $l_catdata["isys_cats_eps_list__isys_volume_unit__id"], C__CONVERT_DIRECTION__BACKWARD);
        $l_rules["C__CMDB__CATS__EPS__WARMUP_TIME"]["p_strValue"] = isys_convert::time($l_catdata["isys_cats_eps_list__warmup_time"],
            $l_catdata["isys_cats_eps_list__warmup_time__isys_unit_of_time__id"], C__CONVERT_DIRECTION__BACKWARD);
        $l_rules["C__CMDB__CATS__EPS__AUTONOMY_TIME"]["p_strValue"] = isys_convert::time($l_catdata["isys_cats_eps_list__autonomy_time"],
            $l_catdata["isys_cats_eps_list__autonomy_time__isys_unit_of_time__id"], C__CONVERT_DIRECTION__BACKWARD);

        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}