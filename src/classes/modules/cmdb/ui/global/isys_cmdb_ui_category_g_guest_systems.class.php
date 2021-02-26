<?php

/**
 * i-doit
 * CMDB UI: Guest systems category (category type is global).
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_guest_systems extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_g_guest_systems $p_cat
     *
     * @return  void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata = $p_cat->get_result()
            ->get_row();
        $l_dao_con = new isys_cmdb_dao_connection($p_cat->get_database_component());

        $l_rules["C__CATG__GUEST_SYSTEM_CONNECTED_OBJECT"]["p_strSelectedID"] = $l_dao_con->get_object_id_by_connection($l_catdata["isys_catg_guest_systems_list__isys_connection__id"]);
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() .
        $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_guest_systems_list__description"];

        if (!$p_cat->get_validation()) {
            $l_rules = isys_glob_array_merge($l_rules, $p_cat->get_additional_rules());
        }

        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }

    /**
     * Show the list-template for subcategories of maintenance.
     *
     * @param   isys_cmdb_dao_category_g_guest_systems &$p_cat
     * @param   array                                  $p_get_param_override
     * @param   string                                 $p_strVarName
     * @param   string                                 $p_strTemplateName
     * @param   boolean                                $p_bCheckbox
     * @param   boolean                                $p_bOrderLink
     * @param   string                                 $p_db_field_name
     *
     * @return  null
     * @throws  isys_exception_general
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
        $l_object_id = $_GET[C__CMDB__GET__OBJECT];
        $l_obj_type = $_GET[C__CMDB__GET__OBJECTTYPE];

        if (!$l_obj_type) {
            $l_obj_type = $p_cat->get_objTypeID($l_object_id);
        }

        $this->object_browser_as_new([
            'name'                                           => 'C__CATG__GUEST_SYSTEM_CONNECTED_OBJECT',
            isys_popup_browser_object_ng::C__MULTISELECTION  => true,
            isys_popup_browser_object_ng::C__RELATION_FILTER => "C__RELATION_TYPE__SOFTWARE;C__RELATION_TYPE__CLUSTER_SERVICE",
            isys_popup_browser_object_ng::C__FORM_SUBMIT     => true,
            isys_popup_browser_object_ng::C__CAT_FILTER      => "C__CATG__VIRTUAL_MACHINE;C__CATG__VIRTUAL_MACHINE__ROOT",
            isys_popup_browser_object_ng::C__RETURN_ELEMENT  => C__POST__POPUP_RECEIVER,
            isys_popup_browser_object_ng::C__DATARETRIEVAL   => [
                [get_class($p_cat), "get_data_by_object"],
                $l_object_id,
                [
                    "isys_obj__id",
                    "isys_obj__title",
                    "isys_obj__isys_obj_type__id",
                    "isys_obj__sysid"
                ]
            ]
        ], "LC__UNIVERSAL__OBJECT_ADD_REMOVE", "LC__UNIVERSAL__OBJECT_ADD_REMOVE_DESCRIPTION");

        $l_supervisor = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::SUPERVISOR, $l_object_id, $p_cat->get_category_const());

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons()
            ->deactivate_all_buttons()
            ->set_active($l_supervisor, C__NAVBAR_BUTTON__PURGE)
            ->set_active((isys_auth_cmdb::instance()
                ->has_rights_in_obj_and_category(isys_auth::EDIT, $l_object_id, $p_cat->get_category_const())), C__NAVBAR_BUTTON__NEW)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW)
            ->set_visible(true, C__NAVBAR_BUTTON__PURGE);

        // Add the "inherited" rows - these will be displayed when the current object does not inherit the "cluster" category but is a "cluster member" (with data).
        if (!$p_cat->objtype_is_catg_assigned($l_obj_type, defined_or_default('C__CATG__CLUSTER')) && $p_cat->objtype_is_catg_assigned($l_obj_type, defined_or_default('C__CATG__CLUSTER_MEMBERSHIPS'))) {
            $l_res_cluster_member = isys_cmdb_dao_category_g_cluster_memberships::instance($p_cat->get_database_component())
                ->get_data(null, $l_object_id, '', null, C__RECORD_STATUS__NORMAL);

            if ($l_res_cluster_member->count()) {
                $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');
                $l_inherited_services = [];

                while ($l_row_cluster_member = $l_res_cluster_member->get_row()) {
                    // We found a cluster - now we'll look for guest systems on this cluster.
                    $l_res_guest_system = $p_cat->get_data(null, $l_row_cluster_member['isys_obj__id']);

                    if ($l_res_guest_system->count()) {
                        while ($l_row_guest_system = $l_res_guest_system->get_row()) {
                            // We found a guest system of the cluster - now we'll check if the "runs on" property is set to the original object or "nothing".
                            if ($l_row_guest_system['isys_catg_virtual_machine_list__primary'] == null ||
                                $l_row_guest_system['isys_catg_virtual_machine_list__primary'] == $l_object_id) {
                                $l_primary_host = isys_cmdb_dao_category_g_ip::instance($p_cat->get_database_component())
                                    ->get_ips_by_obj_id($l_row_guest_system['isys_obj__id'], true)
                                    ->get_row();

                                // Bingo! We'll add this to the array to display it in the frontend.
                                $l_inherited_services[] = [
                                    'obj_title'      => $l_row_guest_system['isys_obj__title'],
                                    'obj_type_title' => isys_application::instance()->container->get('language')
                                        ->get($p_cat->get_objtype_name_by_id_as_string($l_row_guest_system['isys_obj__isys_obj_type__id'])),
                                    'hostname'       => $l_primary_host['isys_catg_ip_list__hostname'] ?: $l_empty_value,
                                    'ip_address'     => $l_primary_host['isys_cats_net_ip_addresses_list__title'] ?: $l_empty_value,
                                    'runs_on'        => isys_application::instance()->container->get('language')
                                            ->get($p_cat->get_objtype_name_by_id_as_string($l_row_cluster_member['isys_obj__isys_obj_type__id'])) . ' > ' .
                                        $l_row_cluster_member['isys_obj__title']
                                ];
                            }
                        }
                    }
                }

                $l_table_content = $this->get_template_component()
                    ->assign('inherited_guest_systems', $l_inherited_services)
                    ->fetch('content/bottom/content/catg__guest_systems_list.tpl');

                $this->get_template_component()
                    ->assign('additional_object_table_data', $l_table_content);
            }
        }

        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
    }
}
