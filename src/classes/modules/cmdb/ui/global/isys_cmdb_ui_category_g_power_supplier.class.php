<?php

/**
 * i-doit
 *
 * CMDB power_supplier
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_power_supplier extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @global  array                                   $index_includes
     *
     * @param   isys_cmdb_dao_category_g_power_supplier $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_catdata = $p_cat->get_general_data();

        $l_daoCon = new isys_cmdb_dao_cable_connection($this->get_database_component());

        $l_rules = [];

        $index_includes["contentbottomcontent"] = $this->activate_commentary($p_cat)
            ->fill_formfields($p_cat, $l_rules, $l_catdata)
            ->get_template();

        $l_rules["C__CATG__POWER_SUPPLIER__DEST"]["p_strSelectedID"] = $l_daoCon->get_assigned_connector_id($l_catdata["isys_catg_power_supplier_list__isys_catg_connector_list__id"]);
        $l_rules["C__CATG__POWER_SUPPLIER__CABLE"]["p_strSelectedID"] = $l_daoCon->get_assigned_cable($l_catdata["isys_catg_power_supplier_list__isys_catg_connector_list__id"]);

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}
