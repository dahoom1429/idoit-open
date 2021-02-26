<?php

/**
 * i-doit
 *
 * CMDB UI: Cluster Services
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Dennis Stuecken <dstuecken@i-doit.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_cluster_service extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_g_cluster_service $p_cat
     *
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        if (isset($_GET["cluster_options"]) && $_GET['cluster_options']) {
            $this->process_options($p_cat);
            die;
        }

        $l_catdata = $p_cat->get_general_data();
        $l_posts = isys_module_request::get_instance()
            ->get_posts();

        $l_id = $l_catdata["isys_catg_cluster_service_list__id"];

        if ($l_posts[C__GET__NAVMODE] == C__NAVMODE__EDIT || $_POST[C__GET__NAVMODE] == C__NAVMODE__NEW) {
            $l_editMode = "&editMode=1&navMode=" . C__NAVMODE__EDIT;
            $l_rules["C__CMDB__CATG__CLUSTER_SERVICE__RUNS_ON"]["p_bDisabled"] = "0";
        } else {
            $l_editMode = "";
            $l_rules["C__CMDB__CATG__CLUSTER_SERVICE__RUNS_ON"]["p_bDisabled"] = "1";
        }

        isys_application::instance()->template->assign("cluster_service_ajax_url",
            "?" . http_build_query($_GET, null, "&") . "&ajax=1&call=category&cluster_options=1" . $l_editMode . "&" . C__CMDB__GET__CATLEVEL . "=" . $l_id)
            ->assign("cluster_service_obj_id", $l_catdata["isys_connection__isys_obj__id"]);

        $l_rules["C__CMDB__CATG__CLUSTER_SERVICE__APPLICATION"]["p_strValue"] = $l_catdata["isys_connection__isys_obj__id"];
        $l_rules["C__CMDB__CATG__CLUSTER_SERVICE__TYPE"]["p_strSelectedID"] = $l_catdata["isys_catg_cluster_service_list__isys_cluster_type__id"];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() .
        $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_cluster_service_list__description"];

        $l_request = isys_request::factory()
            ->set_category_type(defined_or_default('C__CATG__CLUSTER_SERVICE'))
            ->set_category_data_id($l_catdata["isys_catg_cluster_service_list__id"])
            ->set_object_id($_GET[C__CMDB__GET__OBJECT]);

        $l_default_server = $p_cat->callback_property_default_server($l_request);
        $l_runs_on = $p_cat->callback_property_runs_on($l_request);

        // Assigning cluster members.
        $l_rules["C__CMDB__CATG__CLUSTER_SERVICE__RUNS_ON"]["p_arData"] = $l_runs_on;

        // Default server dialog hack.
        $l_rules["C__CMDB__CATG__CLUSTER_SERVICE__DEFAULT_SERVER"]["p_arData"] = $l_default_server;
        $l_rules["C__CMDB__CATG__CLUSTER_SERVICE__DEFAULT_SERVER"]["p_strSelectedID"] = $l_catdata["isys_catg_cluster_service_list__cluster_members_list__id"];

        // Service status dialog
        $l_rules["C__CATG__CLUSTER_SERVICE__SERVICE_STATUS"]["p_arData"] = isys_cmdb_dao_category_g_cluster_service::getServiceStatusLabels();
        $l_rules["C__CATG__CLUSTER_SERVICE__SERVICE_STATUS"]["p_strSelectedID"] = $l_catdata["isys_catg_cluster_service_list__service_status"];

        if (!$p_cat->get_validation()) {
            $l_rules = isys_glob_array_merge($l_rules, $p_cat->get_additional_rules());
        }

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }

    /**
     * Process list method.
     *
     * @param   isys_cmdb_dao_category &$p_cat
     *
     * @return  null
     */
    public function process_list(
        isys_cmdb_dao_category &$p_cat,
        $p_get_param_override = null,
        $p_strVarName = null,
        $p_strTemplateName = null,
        $p_bCheckbox = true,
        $p_bOrderLink = true,
        $p_db_field_name = null
    ) {
        if (@$_GET["cluster_options"]) {
            $this->process_options($p_cat);
            die;
        } else {
            return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
        }
    }

    /**
     * @param isys_cmdb_dao_category_g_cluster_service $p_cat
     */
    public function process_options(isys_cmdb_dao_category_g_cluster_service $p_cat)
    {
        global $g_comp_database;

        /* initialization */
        $l_app_id = $_POST["application_id"];
        $l_drives = [];
        $l_ips = [];
        $l_shares = [];

        /* process */
        if ($_GET[C__CMDB__GET__OBJECT]) {
            $l_data = $p_cat->get_data($_GET[C__CMDB__GET__CATLEVEL])
                ->get_row();

            // Get DBMS
            $l_dbms_data = isys_cmdb_dao_category_g_cluster_service::get_dbms($l_data['isys_catg_cluster_service_list__isys_catg_relation_list__id']);

            /* Existing */
            if ($_GET[C__CMDB__GET__CATLEVEL] > 0 && ($l_data['isys_connection__isys_obj__id'] == $l_app_id)) {
                $l_res_drives = $p_cat->get_cluster_drives($_GET[C__CMDB__GET__CATLEVEL]);
                $l_res_ip = $p_cat->get_cluster_addresses($_GET[C__CMDB__GET__CATLEVEL]);
                $l_res_shares = $p_cat->get_cluster_shares($_GET[C__CMDB__GET__CATLEVEL]);

                while ($l_row = $l_res_drives->get_row()) {
                    if ($l_row["isys_catg_drive_list__isys_obj__id"] == $l_app_id) {
                        $l_drives[] = $l_row["isys_catg_drive_list__id"];
                    }
                }

                while ($l_row = $l_res_ip->get_row()) {
                    if ($l_row["isys_catg_ip_list__isys_obj__id"] == $l_app_id) {
                        $l_ips[] = $l_row["isys_catg_ip_list__id"];
                    }
                }

                while ($l_row = $l_res_shares->get_row()) {
                    if ($l_row["isys_catg_shares_list__isys_obj__id"] == $l_app_id) {
                        $l_shares[] = $l_row["isys_catg_shares_list__id"];
                    }
                }
            } else {
                /* Try to set preselection if the clusterservice includes a single entry  */

                /* DAOS */
                $l_dao_drives = new isys_cmdb_dao_category_g_drive($g_comp_database);
                $l_dao_shares = new isys_cmdb_dao_category_g_shares($g_comp_database);
                $l_dao_ips = new isys_cmdb_dao_category_g_ip($g_comp_database);

                /* Ressources */
                $l_res_drives = $l_dao_drives->get_data(null, $l_app_id);
                $l_res_ip = $l_dao_ips->get_ips_by_obj_id($l_app_id);
                $l_res_shares = $l_dao_shares->get_data(null, $l_app_id);
                $l_ressources = [
                    "l_drives" => $l_res_drives,
                    "l_ips"    => $l_res_ip,
                    "l_shares" => $l_res_shares
                ];

                foreach ($l_ressources AS $s_var => $s_ressource) {
                    if ($s_ressource->num_rows() == 1) {
                        $s_row = $s_ressource->get_row(IDOIT_C__DAO_RESULT_TYPE_ROW);
                        $$s_var = [$s_row[0]];
                    }
                }
            }

            $this->get_template_component()
                ->assign("drive_preselection", isys_glob_htmlentities(isys_format_json::encode($l_drives)))
                ->assign("ip_preselection", isys_glob_htmlentities(isys_format_json::encode($l_ips)))
                ->assign("preselectionDBMS", $l_dbms_data['isys_obj__id'])
                ->assign("preselectionShares", isys_glob_htmlentities(isys_format_json::encode($l_shares)));
        }

        if ($_POST[C__GET__NAVMODE] == "") {
            $_GET["editMode"] = 0;
        }

        if ($_GET[C__GET__NAVMODE] == C__NAVMODE__EDIT) {
            $_GET["editMode"] = 1;
        }

        isys_application::instance()->template->display("content/bottom/content/catg__cluster_service_options.tpl");
        die;
    }
}