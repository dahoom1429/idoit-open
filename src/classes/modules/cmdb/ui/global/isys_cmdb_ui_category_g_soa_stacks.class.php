<?php

/**
 * i-doit
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_soa_stacks extends isys_cmdb_ui_category_global
{

    /**
     * @global                       $index_includes
     *
     * @param isys_cmdb_dao_category & $p_cat
     *
     * @version Niclas Potthast <npotthast@i-doit.org> - 2006-11-10
     * @desc    show the detail-template for subcategories of application
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;
        global $g_comp_database;

        $l_catdata = $p_cat->get_result()
            ->__to_array();
        $l_posts = isys_module_request::get_instance()
            ->get_posts();
        $l_cat_list = [];

        $l_rules = [];

        /* Fill fields */
        $l_rules["C__CATG__SOA_STACKS__TITLE"]["p_strValue"] = $l_catdata["isys_catg_soa_stacks_list__title"];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_soa_stacks_list__description"];

        $l_dao_application = new isys_cmdb_dao_category_g_application($g_comp_database);
        $l_dao_relation = new isys_cmdb_dao_category_g_relation($g_comp_database);

        $l_res = $l_dao_application->get_data(null, $_GET[C__CMDB__GET__OBJECT]);
        // Applications
        while ($l_row = $l_res->get_row()) {

            $l_relation_obj = $l_dao_relation->get_data($l_row["isys_catg_application_list__isys_catg_relation_list__id"])
                ->get_row();

            if (is_countable($l_catdata) && count($l_catdata) > 0) {
                $l_res_assigned = ($p_cat->get_assigned_object($l_catdata["isys_catg_soa_stacks_list__id"], $l_relation_obj["isys_catg_relation_list__isys_obj__id"]));
            } else {
                $l_res_assigned = false;
            }

            if ($l_res_assigned) {
                $l_selected = true;
            } else {
                $l_selected = false;
            }

            $l_cat_list[] = [
                "val" => $l_relation_obj["slave_title"] . ' >> ' . $l_relation_obj["master_title"],
                "hid" => 0,
                "sel" => $l_selected,
                "id"  => $l_relation_obj["isys_catg_relation_list__isys_obj__id"]
            ];

            if ($l_selected) {
                $l_arData[$l_relation_obj["isys_catg_relation_list__isys_obj__id"]] = $l_relation_obj["slave_title"] . ' >> ' . $l_relation_obj["master_title"];
            }
        }

        $l_rel_res = $l_dao_relation->get_data(null, null,
            "AND (isys_catg_relation_list__isys_relation_type__id = '" . defined_or_default('C__RELATION_TYPE__SOFTWARE') . "' AND isys_catg_relation_list__isys_obj__id__slave = " .
            $p_cat->convert_sql_id($_GET[C__CMDB__GET__OBJECT]) . ") OR (isys_catg_relation_list__isys_relation_type__id = '" . defined_or_default('C__RELATION_TYPE__CLUSTER_SERVICE') .
            "' AND isys_catg_relation_list__isys_obj__id__slave = " . $p_cat->convert_sql_id($_GET[C__CMDB__GET__OBJECT]) . ")");
        // Application from relation objects

        while ($l_row = $l_rel_res->get_row()) {
            $l_res = $l_dao_application->get_data(null, $l_row["isys_catg_relation_list__isys_obj__id"]);

            while ($l_row_app = $l_res->get_row()) {

                $l_relation_obj = $l_dao_relation->get_data($l_row_app["isys_catg_application_list__isys_catg_relation_list__id"])
                    ->get_row();

                if (is_countable($l_catdata) && count($l_catdata) > 0) {
                    $l_res_assigned = ($p_cat->get_assigned_object($l_catdata["isys_catg_soa_stacks_list__id"], $l_relation_obj["isys_catg_relation_list__isys_obj__id"]));
                } else {
                    $l_res_assigned = false;
                }

                if ($l_res_assigned) {
                    $l_selected = true;
                } else {
                    $l_selected = false;
                }

                $l_cat_list[] = [
                    "val" => $l_relation_obj["slave_title"] . ' >> ' . $l_relation_obj["master_title"],
                    "hid" => 0,
                    "sel" => $l_selected,
                    "id"  => $l_relation_obj["isys_catg_relation_list__isys_obj__id"]
                ];

                if ($l_selected) {
                    $l_arData[$l_relation_obj["isys_catg_relation_list__isys_obj__id"]] = $l_relation_obj["slave_title"] . ' >> ' . $l_relation_obj["master_title"];
                }
            }
        }

        //$l_relation_obj = $l_dao_relation->get_data($l_catdata["isys_catg_soa_stacks_list__isys_catg_relation_list__id"])->get_row();
        $l_res_it_services = $p_cat->get_assigned_it_services($l_catdata["isys_connection__isys_obj__id"]);
        $l_it_service_str = '';
        if ($l_res_it_services) {
            while ($l_row_its = $l_res_it_services->get_row()) {
                $l_it_service_str .= $l_row_its["isys_catg_its_components_list__isys_obj__id"] . ",";
            }
        }

        if (strlen($l_it_service_str) > 1) {
            $l_it_service_str = substr($l_it_service_str, 0, -1);
        } else {
            $l_it_service_str = "";
        }

        $l_rules["C__CATG__SOA_STACKS__IT_SERVICE"]["p_strSelectedID"] = $l_it_service_str;

        if ($l_posts[C__GET__NAVMODE] == C__NAVMODE__EDIT || $l_posts[C__GET__NAVMODE] == C__NAVMODE__NEW) {
            $l_rules["C__CATG__SOA_STACKS__COMPONENTS_LIST"]["p_bDisabled"] = "0";
        } else {
            $l_rules["C__CATG__SOA_STACKS__COMPONENTS_LIST"]["p_bDisabled"] = "1";
        }

        isys_application::instance()->template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->assign("soa_components", $l_cat_list);

        $index_includes["contentbottomcontent"] = $this->get_template();
    }

    /**
     * isys_cmdb_ui_category_g_soa_stacks constructor.
     *
     * @param isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        $this->set_template("catg__soa_stacks.tpl");
        parent::__construct($p_template);
    }
}