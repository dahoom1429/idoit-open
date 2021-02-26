<?php

/**
 * i-doit
 *
 * CMDB UI: Global category storage controller
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org> - 2006-06-27
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_controller_fc_port extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @global  array                                      $index_includes
     *
     * @param   isys_cmdb_dao_category_g_controller_fcport $p_cat
     *
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_gets = isys_module_request::get_instance()
            ->get_gets();

        $l_rules = [];

        if (isset($l_gets[C__CMDB__GET__CATLEVEL]) && $l_gets[C__CMDB__GET__CATLEVEL] != "-1") {
            $l_bNewPort = false;
        } else {
            $l_bNewPort = true;
        }

        $l_catdata = $p_cat->get_result()
            ->__to_array();

        $l_objController = new isys_cmdb_dao_category_g_hba($this->get_database_component());
        $l_resController = $l_objController->get_fc_controllers($l_gets[C__CMDB__GET__OBJECT]);

        $l_arInterface = [];

        if ($l_resController->num_rows() > 0) {
            while ($l_row = $l_resController->get_row()) {
                $l_arInterface[$l_row["isys_catg_hba_list__id"]] = $l_row["isys_catg_hba_list__title"];
            }
        }

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        $l_speed = isys_convert::speed($l_catdata["isys_catg_fc_port_list__port_speed"], $l_catdata["isys_catg_fc_port_list__isys_port_speed__id"],
            C__CONVERT_DIRECTION__BACKWARD);

        $l_rules["C__CATG__FCPORT__SPEED_VALUE"]["p_strValue"] = $l_speed;
        $l_rules["C__CATG__CONTROLLER_FC_PORTS2CREATE"]["p_strValue"] = 1;
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_fc_port_list__description"];

        // Get assigned port.
        $l_daoCon = new isys_cmdb_dao_cable_connection($this->get_database_component());
        $l_rules["C__CATG__FCPORT__DEST"]["p_strSelectedID"] = $l_daoCon->get_assigned_connector_id($l_catdata["isys_catg_fc_port_list__isys_catg_connector_list__id"]);
        $l_rules["C__CATG__FCPORT__CABLE"]["p_strSelectedID"] = $l_daoCon->get_assigned_cable($l_catdata["isys_catg_fc_port_list__isys_catg_connector_list__id"]);

        // Get assigned zones.
        $l_dao_zoning = new isys_cmdb_dao_category_s_san_zoning($this->get_database_component());
        $l_rules["C__CMDB__CATS__SAN_ZONE"]["p_strValue"] = isys_format_json::encode($l_dao_zoning->get_assigned_san_zones_id($l_catdata["isys_catg_fc_port_list__id"]));
        $l_rules["C__CMDB__CATS__SAN_ZONE"]["p_strSelFCPort"] = isys_format_json::encode($l_dao_zoning->get_selected_fc_ports_by_fc_port_id($l_catdata["isys_catg_fc_port_list__id"]));
        $l_rules["C__CMDB__CATS__SAN_ZONE"]["p_strSelWWN"] = isys_format_json::encode($l_dao_zoning->get_selected_wwns_by_fc_port_id($l_catdata["isys_catg_fc_port_list__id"]));
        $l_rules["C__CMDB__CATS__SAN_ZONE"]["p_strExtraField"] = "C__CATG__CONTROLLER_FC_PORT_NODE_WWN";

        // Assign saved stuff back to gui.
        if (!$p_cat->get_validation() || $l_gets[C__CMDB__GET__CATLEVEL] == "-1") {
            $l_rules["C__CATG__CONTROLLER_FC_PORT_TITLE"]["p_strValue"] = $_POST["C__CATG__CONTROLLER_FC_PORT_TITLE"];
            $l_rules["C__CATG__CONTROLLER_FC_PORT_TYPE"]["p_strSelectedID"] = $_POST["C__CATG__CONTROLLER_FC_PORT_TYPE"];
            $l_rules["C__CATG__CONTROLLER_FC_PORT_MEDIUM"]["p_strSelectedID"] = $_POST["C__CATG__CONTROLLER_FC_PORT_MEDIUM"];
            $l_rules["C__CATG__CONTROLLER_FC_PORT_NODE_WWN"]["p_strValue"] = $_POST["C__CATG__CONTROLLER_FC_PORT_NODE_WWN"];
            $l_rules["C__CATG__CONTROLLER_FC_PORT_PORT_WWN"]["p_strValue"] = $_POST["C__CATG__CONTROLLER_FC_PORT_PORT_WWN"];
            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $_POST["C__CMDB__CAT__COMMENTARY_" .
            $p_cat->get_category_type() . $p_cat->get_category_id()];
            $l_rules["C__CATG__CONTROLLER_FC_CONTROLLER"]["p_strSelectedID"] = $_POST["C__CATG__CONTROLLER_FC_CONTROLLER"];
            $l_rules["C__CATG__CONTROLLER_FC_PORTS2CREATE"]["p_strValue"] = $_POST["C__CATG__CONTROLLER_FC_PORTS2CREATE"];
            $l_rules["C__CATG__PORT__START_WITH"]["p_strValue"] = $_POST["C__CATG__PORT__START_WITH"];
            $l_rules["C__CATG__FCPORT__SPEED_VALUE"]["p_strValue"] = $_POST["C__CATG__PORT__SPEED_VALUE"];
            $l_rules["C__CATG__FCPORT__SPEED"]["p_strSelectedID"] = $_POST["C__CATG__PORT__SPEED"];

            $l_rules = isys_glob_array_merge($l_rules, $p_cat->get_additional_rules());
        }

        // Assign if it's a new port.
        if ($l_bNewPort) {
            $this->get_template_component()
                ->assign("nNewPort", "1");
        }

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
        $this->activate_commentary($p_cat);
        $index_includes["contentbottomcontent"] = $this->get_template();
    }

    /**
     * UI constructor.
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("catg__fc_port.tpl");
    }
}

?>