<?php

/**
 * i-doit
 *
 * CMDB Air condition: Specific category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @version     Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_ac extends isys_cmdb_ui_category_specific
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_s_ac $p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Make rules.
        $l_rules["C__CATS__AC_REFRIGERATING_CAPACITY"]["p_strValue"] = isys_convert::watt($l_catdata["isys_cats_ac_list__capacity"],
            $l_catdata["isys_cats_ac_list__isys_ac_refrigerating_capacity_unit__id"], C__CONVERT_DIRECTION__BACKWARD);
        $l_rules["C__CATS__AC_DIMENSIONS_WIDTH"]["p_strValue"] = $this->formatNumberViewMode(isys_convert::measure($l_catdata["isys_cats_ac_list__width"],
            $l_catdata["isys_cats_ac_list__isys_depth_unit__id"], C__CONVERT_DIRECTION__BACKWARD));
        $l_rules["C__CATS__AC_DIMENSIONS_HEIGHT"]["p_strValue"] = $this->formatNumberViewMode(isys_convert::measure($l_catdata["isys_cats_ac_list__height"],
            $l_catdata["isys_cats_ac_list__isys_depth_unit__id"], C__CONVERT_DIRECTION__BACKWARD));
        $l_rules["C__CATS__AC_DIMENSIONS_DEPTH"]["p_strValue"] = $this->formatNumberViewMode(isys_convert::measure($l_catdata["isys_cats_ac_list__depth"],
            $l_catdata["isys_cats_ac_list__isys_depth_unit__id"], C__CONVERT_DIRECTION__BACKWARD));

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}