<?php

/**
 * i-doit
 *
 * Global category connector
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_connectors_assignment extends isys_cmdb_ui_category_specific
{

    /**
     * Show the detail-template for global category connections
     *
     * @param isys_cmdb_dao_category_g_connector $p_cat
     *
     * @author Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata = $p_cat->get_general_data();

        if (!isset($l_catdata["isys_catg_connector_list__id"]) || is_null($l_catdata["isys_catg_connector_list__id"])) {
            $this->m_template->assign("new", true);
        } else {
            $this->m_template->assign("new", false);
        }

        $l_rules["C__CATG__CONNECTOR__CONNECTED_NET"]["p_strSelectedID"] = $l_catdata["isys_connection__isys_obj__id"];
        $l_rules["C__CATG__CONNECTOR__CONNECTION_TYPE"]["p_strSelectedID"] = $l_catdata["isys_connection_type__id"];
        $l_rules["C__CATG__CONNECTOR__DISTRIBUTION"]["p_strSelectedID"] = $l_catdata["isys_distribution__id"];
        $l_rules["C__UNIVERSAL__TITLE"]["p_strValue"] = $l_catdata["isys_catg_connector_list__title"];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_connector_list__description"];

        if (!$p_cat->get_validation()) {
            $l_rules["C__CATG__CONNECTOR__CONNECTED_NET"]["p_strSelectedID"] = $_POST["C__CATG__CONNECTOR__CONNETED"];
            $l_rules["C__CATG__CONNECTOR__CONNECTION_TYPE"]["p_strSelectedID"] = $_POST["C__CATG__CONNECTOR__CONNECTION_TYPE"];
            $l_rules["C__CATG__CONNECTOR__DISTRIBUTION"]["p_strSelectedID"] = $_POST["C__CATG__CONNECTOR__DISTRIBUTION"];
            $l_rules["C__UNIVERSAL__TITLE"]["p_strValue"] = $_POST["C__CATG__CONNECTOR__TITLE"];

            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $_POST["C__CMDB__CAT__COMMENTARY_" .
            $p_cat->get_category_type() . $p_cat->get_category_id()];

            $l_rules = isys_glob_array_merge($l_rules, $p_cat->get_additional_rules());
        }

        $this->m_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }

    /**
     * isys_cmdb_ui_category_s_connectors_assignment constructor.
     *
     * @param isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("catg__connector.tpl");
    }

}