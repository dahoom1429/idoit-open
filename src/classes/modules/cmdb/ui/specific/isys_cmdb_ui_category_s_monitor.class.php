<?php

/**
 * i-doit
 *
 * CMDB Active Directory: Specific category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_monitor extends isys_cmdb_ui_category_specific
{
    /**
     * Show the detail-template for specific category monitor.
     *
     * @global  array                            $index_includes
     *
     * @param   isys_cmdb_dao_category_s_monitor $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Make some additional (complex) rules.
        $l_rules["C__CATS__MONITOR_DISPLAY"]["p_strValue"] = (!empty($l_catdata["isys_cats_monitor_list__display"])) ? $this->formatNumberViewMode(isys_convert::measure($l_catdata["isys_cats_monitor_list__display"],
            $l_catdata["isys_cats_monitor_list__isys_depth_unit__id"], C__CONVERT_DIRECTION__BACKWARD)) : null;

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}