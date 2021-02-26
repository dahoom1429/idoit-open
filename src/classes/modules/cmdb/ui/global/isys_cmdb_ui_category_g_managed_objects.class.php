<?php

/**
 * i-doit
 *
 * UI: global category for managed objects view
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_managed_objects extends isys_cmdb_ui_category_global
{
    /**
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @return  void
     * @throws  isys_exception_cmdb
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        if (!($p_cat instanceof isys_cmdb_dao_category_g_managed_objects)) {
            return;
        }

        $this->object_browser_as_new([
            'name'                                               => 'C__CATS__GROUP__OBJECT',
            isys_popup_browser_object_ng::C__MULTISELECTION      => true,
            isys_popup_browser_object_ng::C__FORM_SUBMIT         => true,
            isys_popup_browser_object_ng::C__CAT_FILTER          => 'C__CATG__VIRTUAL_HOST_ROOT',
            isys_popup_browser_object_ng::C__RETURN_ELEMENT      => C__POST__POPUP_RECEIVER,
            isys_popup_browser_object_ng::C__OBJECT_BROWSER__TAB => [
                isys_popup_browser_object_ng::C__OBJECT_BROWSER__TAB__LOCATION => false
            ],
            isys_popup_browser_object_ng::C__DATARETRIEVAL       => [
                [
                    get_class($p_cat),
                    "get_assigned_objects"
                ],
                $_GET[C__CMDB__GET__OBJECT],
                [
                    "isys_obj__id",
                    "isys_obj__title",
                    "isys_obj__isys_obj_type__id",
                    "isys_obj__sysid"
                ]
            ]
        ], "LC__UNIVERSAL__OBJECT_ADD_REMOVE", "LC__UNIVERSAL__OBJECT_ADD_REMOVE_DESCRIPTION");

        $l_cluster_devices_res = $p_cat->get_all_assigned_clusters($_GET[C__CMDB__GET__OBJECT]);
        $l_physical_devices_res = $p_cat->get_all_physical_devices($_GET[C__CMDB__GET__OBJECT]);
        $l_virtual_devices_res = $p_cat->get_all_virtual_machines($_GET[C__CMDB__GET__OBJECT]);

        // Build array for cluster devices
        $l_cluster_devices = $this->get_device_data($l_cluster_devices_res);

        /**
         * @var $l_dao_cluster_mem isys_cmdb_dao_category_g_cluster_members
         */
        $l_dao_cluster_mem = isys_cmdb_dao_category_g_cluster_members::instance(isys_application::instance()->database);
        $l_cluster_members = [];

        if (is_countable($l_cluster_devices) && count($l_cluster_devices)) {
            $l_cluster_arr = array_keys($l_cluster_devices);
            $l_cluster_members = [];
            foreach ($l_cluster_arr AS $l_cluster_id) {
                $l_cluster_members[$l_cluster_id] = $l_dao_cluster_mem->get_assigned_members_as_array($l_cluster_id);
            }
        }

        // Build array for physical devices
        $l_physical_devices = $this->get_device_data($l_physical_devices_res, $l_cluster_members);

        /**
         * @var $l_dao_gs isys_cmdb_dao_category_g_guest_systems
         */
        $l_dao_gs = isys_cmdb_dao_category_g_guest_systems::instance(isys_application::instance()->database);
        $l_cluster_members_vms = [];

        if (count($l_cluster_members)) {
            foreach ($l_cluster_members AS $l_members) {
                if ($l_members) {
                    foreach ($l_members AS $l_host_id) {
                        $l_res = $l_dao_gs->get_data(null, $l_host_id);
                        if ($l_res->num_rows()) {
                            while ($l_row = $l_res->get_row()) {
                                $l_cluster_members_vms[$l_host_id][] = $l_row['isys_catg_virtual_machine_list__isys_obj__id'];
                            }
                        }
                    }
                }
            }
        }

        // Build array for virtual computers
        $l_virtual_devices = $this->get_device_data($l_virtual_devices_res, $l_cluster_members_vms);

        $this->get_template_component()
            ->assign('cluster_objects', $l_cluster_devices)
            ->assign('physical_objects', $l_physical_devices)
            ->assign('virtual_computers', $l_virtual_devices)
            ->include_template('contentbottomcontent', 'content/bottom/content/catg__managed_objects.tpl');

        $this->deactivate_commentary();
    }

    /**
     * @inheritdoc
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
        $this->process($p_cat);
    }

    /**
     * @param isys_component_dao_result $p_res
     * @param array                     $p_additional_devices
     *
     * @return array
     * @throws isys_exception_database
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function get_device_data($p_res, &$p_additional_devices = [])
    {
        $l_quickinfo = new isys_ajax_handler_quick_info();
        $l_dao_ip = isys_cmdb_dao_category_g_ip::instance(isys_application::instance()->database);
        $l_return = [];
        if (!defined('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM') || !defined('C__CATG__APPLICATION_PRIORITY__PRIMARY')) {
            return $l_return;
        }

        if (is_object($p_res)) {
            /**
             * @var $l_dao_ip    isys_cmdb_dao_category_g_ip
             * @var $l_dao_model isys_cmdb_dao_category_g_model
             */
            $l_return = [];
            while ($l_row = $p_res->get_row()) {
                $l_os_query = 'SELECT isys_connection__isys_obj__id
				FROM  `isys_catg_application_list`
				INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
				WHERE  `isys_catg_application_list__isys_obj__id` = ' . $l_dao_ip->convert_sql_id($l_row['isys_obj__id']) . '
				AND  `isys_catg_application_list__isys_catg_application_type__id` = ' . $l_dao_ip->convert_sql_int(C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM) . '
				AND  `isys_catg_application_list__isys_catg_application_priority__id` = ' . $l_dao_ip->convert_sql_int(C__CATG__APPLICATION_PRIORITY__PRIMARY);
                $l_return[$l_row['isys_obj__id']] = [
                    'link'       => $l_quickinfo->get_quick_info($l_row['isys_obj__id'], $l_row['isys_obj__title'], C__LINK__OBJECT),
                    'type'       => isys_application::instance()->container->get('language')
                        ->get($l_dao_ip->get_objtype_name_by_id_as_string($l_row['isys_obj__isys_obj_type__id'])),
                    'primary_ip' => $l_dao_ip->get_primary_ip($l_row['isys_obj__id'])
                        ->get_row_value('isys_cats_net_ip_addresses_list__title'),
                    'serial'     => $l_dao_ip->retrieve('SELECT isys_catg_model_list__serial FROM isys_catg_model_list WHERE isys_catg_model_list__isys_obj__id = ' .
                        $l_dao_ip->convert_sql_id($l_row['isys_obj__id']))
                        ->get_row_value('isys_catg_model_list__serial'),
                    'os'         => $l_dao_ip->get_obj_name_by_id_as_string($l_dao_ip->retrieve($l_os_query)
                        ->get_row_value('isys_connection__isys_obj__id'))
                ];
                if (isset($l_row['isys_catg_virtual_machine_list__id'])) {
                    $l_return[$l_row['isys_obj__id']]['parent'] = $l_dao_ip->get_obj_name_by_id_as_string($l_row['isys_connection__isys_obj__id']);
                }
            }
        }

        if (count($p_additional_devices) > 0) {
            $l_res_objtypes = $l_dao_ip->get_obj_type_by_catg(filter_defined_constants(['C__CATG__VIRTUAL_MACHINE__ROOT']));
            $l_allowed_objecttypes = [];
            while ($l_row = $l_res_objtypes->get_row()) {
                $l_allowed_objecttypes[$l_row['isys_obj_type__id']] = true;
            }

            foreach ($p_additional_devices AS $l_parent_id => $l_members) {
                if ($l_members) {
                    foreach ($l_members AS $l_key => $l_id) {
                        if (!isset($l_return[$l_id])) {
                            $l_objtype_id = $l_dao_ip->get_objTypeID($l_id);
                            if (isset($l_allowed_objecttypes[$l_objtype_id])) {
                                $l_os_query = 'SELECT isys_connection__isys_obj__id
							FROM  `isys_catg_application_list` INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
							WHERE  `isys_catg_application_list__isys_obj__id` = ' . $l_dao_ip->convert_sql_id($l_id) . '
							AND  `isys_catg_application_list__isys_catg_application_type__id` = ' . $l_dao_ip->convert_sql_int(C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM) . '
							AND  `isys_catg_application_list__isys_catg_application_priority__id` = ' . $l_dao_ip->convert_sql_int(C__CATG__APPLICATION_PRIORITY__PRIMARY);
                                $l_return[$l_id] = [
                                    'link'       => $l_quickinfo->get_quick_info($l_id, $l_dao_ip->get_obj_name_by_id_as_string($l_id), C__LINK__OBJECT),
                                    'type'       => isys_application::instance()->container->get('language')
                                        ->get($l_dao_ip->get_objtype_name_by_id_as_string($l_objtype_id)),
                                    'primary_ip' => $l_dao_ip->get_primary_ip($l_id)
                                        ->get_row_value('isys_cats_net_ip_addresses_list__title'),
                                    'serial'     => $l_dao_ip->retrieve('SELECT isys_catg_model_list__serial FROM isys_catg_model_list WHERE isys_catg_model_list__isys_obj__id = ' .
                                        $l_dao_ip->convert_sql_id($l_id))
                                        ->get_row_value('isys_catg_model_list__serial'),
                                    'os'         => $l_dao_ip->get_obj_name_by_id_as_string($l_dao_ip->retrieve($l_os_query)
                                        ->get_row_value('isys_connection__isys_obj__id')),
                                    'parent'     => $l_dao_ip->get_obj_name_by_id_as_string($l_parent_id)
                                ];
                            } else {
                                unset($p_additional_devices[$l_parent_id][$l_key]);
                            }
                        } else {
                            unset($p_additional_devices[$l_key]);
                        }
                    }
                }
            }
        }

        return $l_return;
    }
}
