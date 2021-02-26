<?php

/**
 * i-doit
 *
 * CMDB Active Directory: Specific category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_cluster_service extends isys_cmdb_ui_category_specific
{
    /**
     * Show the detail-template for specific category room.
     *
     * @param  isys_cmdb_dao_category $p_cat
     *
     * @return array|void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        if (@$_GET["runs_on_ajax_call"]) {
            $this->process_runs_on($p_cat);
            die();
        }

        $l_catlevel = $_GET[C__CMDB__GET__CATLEVEL];

        if ($l_catlevel > 0) {
            $l_catdata = $p_cat->get_data($l_catlevel, null, "", null, C__RECORD_STATUS__NORMAL)
                ->__to_array();

            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_cats_room_list__description"];
            $l_rules["C__CATS__CLUSTER_SERVICE__RUNS_ON"]["p_bDisabled"] = !($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT);

            $l_dao_cs = new isys_cmdb_dao_category_g_cluster_service($p_cat->get_database_component());

            $l_addresses = [];
            $l_drives = [];
            $l_cluster_shares = [];
            $l_cat_list = [];
            $l_arData = [];

            $l_res_addresses = $l_dao_cs->get_cluster_addresses($l_catdata["isys_catg_cluster_service_list__id"]);
            $l_res_drives = $l_dao_cs->get_cluster_drives($l_catdata["isys_catg_cluster_service_list__id"]);
            $l_res_cluster_shares = $l_dao_cs->get_cluster_shares($l_catdata["isys_catg_cluster_service_list__id"]);

            while ($l_row = $l_res_addresses->get_row()) {
                $l_addresses[] = $l_row["isys_catg_ip_list__id"];
            }

            while ($l_row = $l_res_drives->get_row()) {
                $l_drives[] = $l_row["isys_catg_drive_list__id"];
            }

            while ($l_row = $l_res_cluster_shares->get_row()) {
                $l_cluster_shares[] = $l_row["isys_catg_shares_list__id"];
            }

            $l_dao_c_members = new isys_cmdb_dao_category_g_cluster_members($p_cat->get_database_component());

            $l_res_members = $l_dao_c_members->get_data(null, $l_catdata["isys_catg_cluster_service_list__isys_obj__id"]);
            $l_members = '';

            while ($l_row = $l_res_members->get_row()) {
                $l_selected = ($l_dao_cs->get_cluster_members($l_catdata["isys_catg_cluster_service_list__id"], $l_row["isys_catg_cluster_members_list__id"])
                        ->num_rows() > 0);

                $l_title = $p_cat->get_obj_name_by_id_as_string($l_row["isys_connection__isys_obj__id"]);

                $l_cat_list[] = [
                    "val" => $l_title,
                    "hid" => 0,
                    "sel" => $l_selected,
                    "id"  => $l_row["isys_catg_cluster_members_list__id"]
                ];

                if ($l_selected) {
                    $l_arData[$l_row["isys_catg_cluster_members_list__id"]] = $l_title;
                }
            }

            if ($l_catdata["isys_obj__id"]) {
                $l_rules["C__CATS__CLUSTER_SERVICE__RUNS_ON"]["p_strValue"] = $l_members;
                $l_rules["C__CATS__CLUSTER_SERVICE__TYPE"]["p_strSelectedID"] = $l_catdata["isys_catg_cluster_service_list__isys_cluster_type__id"];
                $l_rules["C__CATS__CLUSTER_SERVICE__ASSIGNED_CLUSTER"]["p_strValue"] = $l_catdata["isys_obj__id"];
                $l_rules["C__CATS__CLUSTER_SERVICE__HOST_ADDRESSES"]["p_preSelection"] = isys_glob_htmlentities(isys_format_json::encode($l_addresses));
                $l_rules["C__CATS__CLUSTER_SERVICE__VOLUMES"]["p_preSelection"] = isys_glob_htmlentities(isys_format_json::encode($l_drives));
                $l_rules["C__CATS__CLUSTER_SERVICE__SHARES"]["p_preSelection"] = isys_glob_htmlentities(isys_format_json::encode($l_cluster_shares));

                $l_rules["C__CATS__CLUSTER_SERVICE__RUNS_ON"]["p_arData"] = $l_cat_list;

                // Default server dialog hack.
                $l_rules["C__CATS__CLUSTER_SERVICE__DEFAULT_SERVER"]["p_arData"] = $l_arData;
                $l_rules["C__CATS__CLUSTER_SERVICE__DEFAULT_SERVER"]["p_strSelectedID"] = $l_catdata["isys_catg_cluster_service_list__cluster_members_list__id"];

                // Get DBMS
                $l_dbms_data = isys_cmdb_dao_category_g_cluster_service::get_dbms($l_catdata['isys_catg_cluster_service_list__isys_catg_relation_list__id']);
                $l_rules['C__CATS__CLUSTER_SERVICE_DATABASE_SCHEMATA']['p_strSelectedID'] = $l_dbms_data['isys_obj__id'];

                $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() .
                $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_cluster_service_list__description"];

                // Cluster service status dialog
                $l_rules["C__CATS__CLUSTER_SERVICE__SERVICE_STATUS"]["p_arData"] = isys_cmdb_dao_category_g_cluster_service::getServiceStatusLabels();
                $l_rules["C__CATS__CLUSTER_SERVICE__SERVICE_STATUS"]["p_strSelectedID"] = $l_catdata["isys_catg_cluster_service_list__service_status"];
            }
        }

        isys_module_request::get_instance()
            ->get_navbar()
            ->set_active(false, C__NAVBAR_BUTTON__ARCHIVE);

        $this->m_template->smarty_tom_add_rules('tom.content.bottom.content', $l_rules)
            ->assign(
                'cluster_service_ajax_url',
                "?" . http_build_query($_GET, null, "&") . "&call=category&runs_on_ajax_call=1&" . C__CMDB__GET__CATLEVEL . "=" . $l_catlevel
            );
    }

    /**
     * Method for processing the list.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @param null                     $p_get_param_override
     * @param null                     $p_strVarName
     * @param null                     $p_strTemplateName
     * @param bool                     $p_bCheckbox
     * @param bool                     $p_bOrderLink
     * @param null                     $p_db_field_name
     *
     * @return bool
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
        $this->list_view("isys_catg_cluster_service", $_GET[C__CMDB__GET__OBJECT], isys_cmdb_dao_list_cats_cluster_service::build($this->get_database_component(), $p_cat));

        return true;
    }

    /**
     * @param isys_cmdb_dao_category $p_cat
     */
    public function process_runs_on(isys_cmdb_dao_category $p_cat)
    {
        $l_cat_list = $l_catdata = [];
        $l_selected = false;
        $l_instance = new isys_smarty_plugin_f_dialog_list();

        if ($_GET[C__CMDB__GET__CATLEVEL] > 0) {
            $l_catdata = $p_cat->get_data($_GET[C__CMDB__GET__CATLEVEL], null, "", null, C__RECORD_STATUS__NORMAL)
                ->__to_array();
        }

        $l_dao_c_members = new isys_cmdb_dao_category_g_cluster_members($p_cat->get_database_component());
        $l_dao_cs = new isys_cmdb_dao_category_g_cluster_service($p_cat->get_database_component());

        $l_res_members = $l_dao_c_members->get_data(null, $_POST["cluster_id"]);

        while ($l_row = $l_res_members->get_row()) {
            if ($l_catdata) {
                $l_selected = ($l_dao_cs->get_cluster_members($l_catdata["isys_catg_cluster_service_list__id"], $l_row["isys_catg_cluster_members_list__id"])
                        ->num_rows() > 0);
            }

            $l_title = $p_cat->get_obj_name_by_id_as_string($l_row["isys_connection__isys_obj__id"]);

            $l_cat_list[] = [
                "val" => $l_title,
                "hid" => 0,
                "sel" => $l_selected,
                "id"  => $l_row["isys_catg_cluster_members_list__id"]
            ];
        }

        $l_params = [
            "name"            => "C__CATS__CLUSTER_SERVICE__RUNS_ON",
            "remove_callback" => "idoit.callbackManager.triggerCallback('clusterservice__runs_on_callback').triggerCallback('clusterservice__set_default_server');",
            "p_arData"        => $l_cat_list,
        ];

        if ($_POST[C__GET__NAVMODE] == "") {
            $_GET["editMode"] = 0;
        }

        die($l_instance->navigation_edit($this->m_template, $l_params));
    }
}
