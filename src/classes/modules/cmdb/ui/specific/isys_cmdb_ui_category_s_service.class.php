<?php

/**
 * i-doit
 *
 * CMDB Active Directory: Specific category (dienste)
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_service extends isys_cmdb_ui_category_specific
{
    /**
     * @global                       $index_includes
     *
     * @param isys_cmdb_dao_category $p_cat
     *
     * @desc show the detail-template for specific category service / (dienste)
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;
        global $g_comp_database;

        $l_catdata = $p_cat->get_data(null, $_GET[C__CMDB__GET__OBJECT])
            ->get_row();

        $l_rules["C__CATS__SERVICE_RELEASE"]["p_strValue"] = $l_catdata["isys_cats_service_list__release"];
        $l_rules["C__CATS__SERVICE_SPECIFICATION"]["p_strValue"] = $l_catdata["isys_cats_service_list__specification"];
        $l_rules["C__CATS__SERVICE_MANUFACTURER_ID"]["p_strSelectedID"] = $l_catdata["isys_cats_service_list__isys_service_manufacturer__id"];

        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_cats_service_list__description"];

        /* - associated objects */
        $l_dao_app = new isys_cmdb_dao_category_g_application($g_comp_database);
        $l_assigned = $l_dao_app->get_assigned_objects($_GET[C__CMDB__GET__OBJECT]);

        if (!$p_cat->get_validation()) {
            // display the posted value in fields
            // so fill posted values to $l_rules
            // dont forget the hidden one...

            $l_rules["C__CATS__SERVICE_RELEASE"]["p_strValue"] = $_POST["C__CATS__SERVICE_RELEASE"];
            $l_rules["C__CATS__SERVICE_SPECIFICATION"]["p_strValue"] = $_POST["C__CATS__SERVICE_SPECIFICATION"];
            $l_rules["C__CATS__SERVICE_MANUFACTURER_ID"]["p_strSelectedID"] = $_POST["C__CATS__SERVICE_MANUFACTURER_ID"];
            // new field C__CMDB__CAT__COMMENTARY /content/bottom/main.tpl to edit comments per category
            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $_POST["C__CMDB__CAT__COMMENTARY_" .
            $p_cat->get_category_type() . $p_cat->get_category_id()];

            // merge exiting rules with given error roles
            // error Roles override exiting roles
            $l_rules = isys_glob_array_merge($l_rules, $p_cat->get_additional_rules());
        }
        // Apply rules
        $this->m_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
        $this->activate_commentary($p_cat);
        $index_includes["contentbottomcontent"] = $this->get_template();
    }

    /**
     * isys_cmdb_ui_category_s_service constructor.
     *
     * @param isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        $this->set_template("cats__service.tpl");
        parent::__construct($p_template);
    }
}
