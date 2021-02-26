<?php

/**
 * i-doit
 *
 * CMDB Drive: Dynamic category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      André Wösten <awoesten@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_san_zoning extends isys_cmdb_ui_category_specific
{
    /**
     * Process method.
     *
     * @global  array                               $index_includes
     *
     * @param   isys_cmdb_dao_category_s_san_zoning $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_catdata = $p_cat->get_general_data();

        $l_rules = $l_obj_ids = $l_selected_fcports = $l_selectedWWNs = [];

        $index_includes["contentbottomcontent"] = $this->activate_commentary($p_cat)
            ->fill_formfields($p_cat, $l_rules, $l_catdata)
            ->get_template();

        // Read FC-Ports and associated objects for the object-browser preselection.
        $l_selected_fcports = $p_cat->get_san_zoning_fc_ports($l_catdata["isys_cats_san_zoning_list__id"]);

        foreach ($l_selected_fcports as $l_fc_port_data) {
            $l_obj_ids[$l_fc_port_data['obj_id']] = $l_fc_port_data['obj_id'];
        }

        $l_rules['C__CATS__SAN_ZONING__MEMBERS']['p_strSelectedID'] = implode(',', $l_obj_ids);

        $l_data = isys_cmdb_dao_category_g_controller_fcport::instance($this->get_database_component())
            ->prepare_data_for_gui($l_obj_ids, $l_catdata['isys_cats_san_zoning_list__id']);

        // Apply rules.
        $this->get_template_component()
            ->assign('cat_id', $l_catdata['isys_cats_san_zoning_list__id'])
            ->assign('data', isys_format_json::encode($l_data))
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}