<?php

/**
 * i-doit
 *
 * Dialog admin module DAO
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.1
 */
class isys_dialog_admin_dao extends isys_module_dao
{
    /**
     * Standard set of tables, which will not be found by $this->get_tables().
     *
     * @see ID-3363 isys_obj_type_group removed from list
     *
     * @var  array
     */
    private $m_dialog_tables = [
        'isys_catg_global_category',
        'isys_cats_prt_emulation',
        'isys_purpose',
        'isys_p_mode',
        'isys_wlan_auth',
        'isys_wlan_channel',
        'isys_wlan_encryption',
        'isys_wlan_function',
        'isys_wlan_standard',
        'isys_cats_prt_paper',
        'isys_file_category',
        'isys_ip_assignment',
        'isys_model_title',
        'isys_ui_plugtype',
        'isys_account',
        'isys_contract_status',
        'isys_maintenance_status',
        'isys_contract_reaction_rate',
        'isys_logbook_reason',
        'isys_dbms',
        'isys_ldev_multipath',
        'isys_unit_of_time',
        'isys_wan_role',
        'isys_maintenance_reaction_rate',
        'isys_replication_mechanism',
        'isys_monitor_resolution',
        'isys_network_provider',
        'isys_telephone_rate',
        'isys_ldap_directory',
        'isys_net_dns_domain',
        'isys_database_objects',
        'isys_backup_cycle',
        'isys_voip_phone_button_template',
        'isys_voip_phone_softkey_template',
        'isys_interval',
        'isys_layer2_net_subtype',
        'isys_snmp_community',
        'isys_chassis_role',
        'isys_vlan_management_protocol',
        'isys_switch_role',
        'isys_switch_spanning_tree',
        'isys_routing_protocol',
        'isys_port_speed',
        'isys_role',
        'isys_currency',
        'isys_fc_port_medium',
        'isys_net_dns_server',
        'isys_port_duplex',
        'isys_port_negotiation',
        'isys_port_mode',
        'isys_contract_payment_period',
        'isys_net_protocol',
        'isys_net_protocol_layer_5',
        'isys_sla_service_level',
        'isys_memory_title',
        'isys_tierclass',
        'isys_calendar',
        'isys_interface',
        'isys_tag',
        'isys_service_category', // @see ID-5026
        'isys_catg_accounting_procurement', // @see ID-7481
    ];

    /**
     * Dialog tables to be skipped, they do not work properly or should not be displayed as "Dialog+" table.
     *
     * @var  array
     */
    private $m_skip = [
        'isys_obj_type',
        'isys_wan_capacity_unit',      // Primary key there has wrong name!
        'isys_workflow_type',
        'isys_monitor_unit',           // No longer used.
        'isys_pobj_type',              // No longer used.
        'isys_catg_model',
        'isys_workflow_action_type',
        'isys_source_type',
        'isys_port_standard',
        'isys_unit',
        'isys_jdisc_ca_type',
        'isys_notification_type',
        'isys_organisation_intern_iop',
        'isys_file_group',             // No longer used.
        'viva_application_information_type', // Should not be editable
        'viva_information_type',       // Should not be editable
        'isys_contact_tag',            // Use administration tool
        'isys_relation_type',          // Use administration tool
        'isys_catg_application_type',  // This is NO dialog+ field
        'isys_jdisc_device_type',      // This is NO dialog+ field
        'isys_tts_type',               // @see ID-4143  TTS types shall not be editable!!
        'isys_logbook_event',
        'isys_frequency_unit',         // @see ID-3823 Unit tables should not be editable!
        'isys_weight_unit',            // @see ID-3823 Unit tables should not be editable!
        'isys_depth_unit',             // @see ID-3823 Unit tables should not be editable!
        'isys_memory_unit',            // @see ID-3823 Unit tables should not be editable!
        'isys_volume_unit',            // @see ID-3823 Unit tables should not be editable!
    ];

    /**
     * Unused method.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_data()
    {
    }

    /**
     * Retrieves all common dialog tables.
     *
     * @param   boolean $p_filter_skipped_tables
     *
     * @return  array
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_dialog_tables($p_filter_skipped_tables = false)
    {
        return $this->load_tables('%_unit')
            ->load_tables('%_type')
            ->load_tables('%_manufacturer')
            ->load_tables('%_model')
            ->load_additional_tables($p_filter_skipped_tables)->m_dialog_tables;
    }

    /**
     * Retrieves all dialog titles + identifier of custom category Dialog+ fields.
     *
     * @return  array
     * @throws  isys_exception_database
     */
    public function get_custom_dialogs()
    {
        $l_return = [];

        $l_res = $this->retrieve('SELECT isysgui_catg_custom__title, isysgui_catg_custom__config FROM isysgui_catg_custom;');

        while ($l_row = $l_res->get_row()) {
            $l_config = unserialize($l_row['isysgui_catg_custom__config']);

            if (!is_array($l_config)) {
                continue;
            }

            foreach ($l_config as $l_field) {
                $selectableFieldTypes = class_exists('isys_custom_fields_dao', true) ? isys_custom_fields_dao::getMultipleSelectableFieldTypes() : ['dialog_plus'];
                if ($l_field['type'] === 'f_popup' && in_array($l_field['popup'], $selectableFieldTypes)) {
                    $l_return[] = [
                        'categoryTitle' => _L($l_row['isysgui_catg_custom__title']),
                        'fieldTitle'    => _L($l_field['title']),
                        'identifier'    => $l_field['identifier']
                    ];
                }
            }
        }

        return $l_return;
    }

    /**
     * Returns the found table-names by a given "filter"-string.
     *
     * @param   string $p_filter
     *
     * @return  isys_dialog_admin_dao
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function load_tables($p_filter)
    {
        $l_res = $this->retrieve('SHOW TABLES LIKE ' . $this->convert_sql_text($p_filter) . ';');

        if (is_countable($l_res) && count($l_res) > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_table_name = current($l_row);

                if (!in_array($l_table_name, $this->m_skip) && !in_array($l_table_name, $this->m_dialog_tables)) {
                    $this->m_dialog_tables[] = $l_table_name;
                }
            }
        }

        return $this;
    }

    /**
     * This method can be used to add "non-core" (module) tables to the dialog admin.
     *
     * @param bool $p_filter_skipped_tables
     *
     * @return  $this
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function load_additional_tables($p_filter_skipped_tables = false)
    {
        $this->m_dialog_tables = array_merge($this->m_dialog_tables, array_keys(isys_register::factory('additional-dialog-admin-tables')
            ->get()));

        // Remove skipped dialog tables if
        if ($p_filter_skipped_tables) {
            $l_skip = $this->m_skip;
            array_walk($this->m_dialog_tables, function ($item, $key) use ($l_skip) {
                if (in_array($item, $l_skip)) {
                    unset($this->m_dialog_tables[$key]);
                }
            });
        }

        return $this;
    }
}
