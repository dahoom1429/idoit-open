<?php

namespace idoit\Console\Command\Cleanup;

use isys_cmdb_dao;
use Exception;
use idoit\Console\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class ConvertNonUtf8TablesCommand for converting "non-UTF8" tables/fields to UTF8.
 *
 * @package idoit\Console\Command\Idoit
 * @author  Leonard Fischer <lfischer@i-doit.com>
 */
class ConvertNonUtf8TablesCommand extends AbstractCommand
{
    const NAME = 'system-convert-non-utf8-tables';

    /**
     * @var isys_cmdb_dao
     */
    private $dao;

    /**
     * Get name for command
     *
     * @return string
     */
    public function getCommandName()
    {
        return self::NAME;
    }

    /**
     * Get description for command
     *
     * @return string
     */
    public function getCommandDescription()
    {
        return 'Changes all non-UTF8-tables to UTF8 (Affects database encoding. Use with caution!)';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();

        $definition->addOption(new InputOption('convert', null, InputOption::VALUE_NONE, 'Starts the conversion process'));

        return $definition;
    }

    /**
     * Checks if a command can have a config file via --config
     *
     * @return bool
     */
    public function isConfigurable()
    {
        return false;
    }

    /**
     * Returns an array of command usages
     *
     * @return string[]
     */
    public function getCommandUsages()
    {
        return [];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('convert')) {
            $output->writeln([
                '<comment>You did not pass the <info>--convert</info> option, the following output will only list the affected tables and NOT convert them to UTF8.</comment>',
                ''
            ]);
        } else {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');

            $question = new ConfirmationQuestion('Did you backup your database? (<comment>yes</comment>/<comment>no</comment>)', true);

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('Please backup you database, before converting any tables to UTF8!');

                return Command::SUCCESS;
            }

            $output->writeln('');
        }

        $success = 0;
        $fail = 0;

        $this->dao = isys_cmdb_dao::instance($this->container->get('database'));

        $tables = $this->getAffectedTables();

        if (!count($tables)) {
            $output->writeln([
                'All tables are UTF8 encoded, no need for any conversion!'
            ]);
        }

        foreach ($tables as $table => $oldEncoding) {
            if ($input->getOption('convert')) {
                $sql = "ALTER TABLE {$table} CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;";

                try {
                    if ($this->dao->update($sql) && $this->dao->apply_update()) {
                        $success++;
                        $output->writeln("  [<info>OK</info>] Table <comment>{$table}</comment> converted from <comment>{$oldEncoding}</comment> to <comment>utf8/utf8_unicode_ci</comment>!");
                    } else {
                        $fail++;
                        $output->writeln("  [<error>FAIL</error>] Table <comment>{$table}</comment> could not be converted from <comment>{$oldEncoding}</comment> to <comment>utf8/utf8_unicode_ci</comment>!");
                    }
                } catch (Exception $e) {
                    $fail++;
                    $output->writeln("<error>An error occured while converting table {$table} from <comment>{$oldEncoding}</comment> to <comment>utf8/utf8_unicode_ci</comment>:</error> {$e->getMessage()}");
                }
            } else {
                $output->writeln("Found table <info>{$table}</info> with encoding <comment>{$oldEncoding}</comment>.");
            }
        }

        $output->writeln([
            '',
            'Processed ' . ($success + $fail) . ' tables, <info>' . $success . '</info> successful and <error>' . $fail . '</error> failed.'
        ]);
        return Command::SUCCESS;
    }


    /**
     * This method will return all tables, which contain non-UTF8 encoding.
     *
     * @return array
     */
    private function getAffectedTables()
    {
        $databaseName = $this->container->get('database')->get_db_name();
        $tablesToConvert = [];

        $whitelistedTables = "'" . implode("', '", $this->tableWhitelist()) . "'";

        $tableSql = "SELECT T.TABLE_NAME, T.TABLE_COLLATION
            FROM information_schema.`TABLES` T
            WHERE T.TABLE_SCHEMA = '{$databaseName}'
            AND T.TABLE_NAME IN ({$whitelistedTables})
            AND T.TABLE_COLLATION NOT LIKE '%utf8%';";
        $tableResult = $this->dao->retrieve($tableSql);

        while ($row = $tableResult->get_row()) {
            $tablesToConvert[$row['TABLE_NAME']] = $row['TABLE_COLLATION'];
        }

        $fieldSql = "SELECT C.TABLE_NAME, C.CHARACTER_SET_NAME, C.COLLATION_NAME
            FROM information_schema.`COLUMNS` C
            WHERE C.TABLE_SCHEMA = '{$databaseName}'
            AND C.TABLE_NAME IN ({$whitelistedTables})
            AND C.CHARACTER_SET_NAME <> ''
            AND C.COLLATION_NAME <> ''
            AND (C.CHARACTER_SET_NAME NOT LIKE '%utf8%' OR C.COLLATION_NAME NOT LIKE '%utf8_unicode%');";
        $fieldResult = $this->dao->retrieve($fieldSql);

        while ($row = $fieldResult->get_row()) {
            $tablesToConvert[$row['TABLE_NAME']] = $row['CHARACTER_SET_NAME'] . '/' . $row['COLLATION_NAME'];
        }

        return $tablesToConvert;
    }

    /**
     * Parsed from "idoit_data.sql".
     *
     * @return array
     */
    private function tableWhitelist()
    {
        return [
            'isys_ac_air_quantity_unit',
            'isys_ac_refrigerating_capacity_unit',
            'isys_ac_type',
            'isys_access_type',
            'isys_account',
            'isys_agent',
            'isys_application_manufacturer',
            'isys_auth',
            'isys_backup_cycle',
            'isys_backup_type',
            'isys_business_unit',
            'isys_cable_colour',
            'isys_cable_connection',
            'isys_cable_occupancy',
            'isys_cable_type',
            'isys_cache_qinfo',
            'isys_calendar',
            'isys_catd_drive',
            'isys_catd_drive_type',
            'isys_catd_sanpool',
            'isys_catg_access_list',
            'isys_catg_accounting_cost_unit',
            'isys_catg_accounting_list',
            'isys_catg_accounting_procurement',
            'isys_catg_address_list',
            'isys_catg_aircraft_list',
            'isys_catg_application_list',
            'isys_catg_application_priority',
            'isys_catg_application_type',
            'isys_catg_assigned_cards_list',
            'isys_catg_audit_list',
            'isys_catg_audit_type',
            'isys_catg_backup_list',
            'isys_catg_cable_list',
            'isys_catg_certificate_list',
            'isys_catg_cluster_adm_service_list',
            'isys_catg_cluster_list',
            'isys_catg_cluster_list_2_isys_obj',
            'isys_catg_cluster_members_list',
            'isys_catg_cluster_members_list_2_isys_catg_cluster_service_list',
            'isys_catg_cluster_service_list',
            'isys_catg_computing_resources_list',
            'isys_catg_connector_list',
            'isys_catg_connector_list_2_isys_fiber_wave_length',
            'isys_catg_contact_list',
            'isys_catg_contract_assignment_list',
            'isys_catg_controller_list',
            'isys_catg_cpu_list',
            'isys_catg_cpu_manufacturer',
            'isys_catg_cpu_type',
            'isys_catg_custom_fields_list',
            'isys_catg_database_assignment_list',
            'isys_catg_drive_list',
            'isys_catg_drive_list_2_isys_catg_cluster_service_list',
            'isys_catg_emergency_plan_list',
            'isys_catg_fc_port_list',
            'isys_catg_fiber_lead_list',
            'isys_catg_file_list',
            'isys_catg_formfactor_list',
            'isys_catg_formfactor_type',
            'isys_catg_global_category',
            'isys_catg_global_list',
            'isys_catg_graphic_list',
            'isys_catg_guest_systems_list',
            'isys_catg_hba_list',
            'isys_catg_identifier_list',
            'isys_catg_identifier_type',
            'isys_catg_image_list',
            'isys_catg_images_list',
            'isys_catg_invoice_list',
            'isys_catg_ip_list',
            'isys_catg_ip_list_2_isys_catg_cluster_service_list',
            'isys_catg_ip_list_2_isys_catg_ip_list',
            'isys_catg_ip_list_2_isys_catg_log_port_list',
            'isys_catg_ip_list_2_isys_catg_port_list',
            'isys_catg_ip_list_2_isys_cats_router_list',
            'isys_catg_ip_list_2_isys_net_dns_domain',
            'isys_catg_ip_list_2_isys_netp_ifacel',
            'isys_catg_its_components_list',
            'isys_catg_its_type_list',
            'isys_catg_jdisc_ca_list',
            'isys_catg_last_login_user_list',
            'isys_catg_ldap_dn_list',
            'isys_catg_ldevclient_list',
            'isys_catg_ldevserver_list',
            'isys_catg_location_list',
            'isys_catg_log_port_list',
            'isys_catg_log_port_list_2_isys_obj',
            'isys_catg_logb_list',
            'isys_catg_logical_unit_list',
            'isys_catg_mail_addresses_list',
            'isys_catg_manual_list',
            'isys_catg_memory_list',
            'isys_catg_model_list',
            'isys_catg_monitoring_list',
            'isys_catg_net_connector_list',
            'isys_catg_net_listener_list',
            'isys_catg_net_type_list',
            'isys_catg_net_zone_options_list',
            'isys_catg_netp_list',
            'isys_catg_overview_list',
            'isys_catg_password_list',
            'isys_catg_pc_list',
            'isys_catg_planning_list',
            'isys_catg_port_list',
            'isys_catg_port_list_2_isys_catg_log_port_list',
            'isys_catg_port_list_2_isys_netp_ifacel',
            'isys_catg_power_supplier_list',
            'isys_catg_qinq_list',
            'isys_catg_raid_list',
            'isys_catg_relation_list',
            'isys_catg_rm_controller_list',
            'isys_catg_sanpool_list',
            'isys_catg_sanpool_list_2_isys_catg_raid_list',
            'isys_catg_sanpool_list_2_isys_catg_stor_list',
            'isys_catg_service_list',
            'isys_catg_service_list_2_isys_service_alias',
            'isys_catg_share_access_list',
            'isys_catg_shares_list',
            'isys_catg_shares_list_2_isys_catg_cluster_service_list',
            'isys_catg_sim_card_list',
            'isys_catg_sla_list',
            'isys_catg_smartcard_certificate_list',
            'isys_catg_snmp_list',
            'isys_catg_soa_components_list',
            'isys_catg_soa_stacks_list',
            'isys_catg_sound_list',
            'isys_catg_stack_member_list',
            'isys_catg_stacking_list',
            'isys_catg_stor_list',
            'isys_catg_telephone_fax_list',
            'isys_catg_tsi_service_list',
            'isys_catg_ui_list',
            'isys_catg_vehicle_list',
            'isys_catg_version_list',
            'isys_catg_virtual_device_list',
            'isys_catg_virtual_host_list',
            'isys_catg_virtual_list',
            'isys_catg_virtual_machine_list',
            'isys_catg_virtual_switch_list',
            'isys_catg_voip_phone_line_2_isys_obj',
            'isys_catg_voip_phone_line_list',
            'isys_catg_voip_phone_list',
            'isys_catg_vrrp_list',
            'isys_catg_vrrp_member_list',
            'isys_catg_wan_list',
            'isys_catg_wan_list_2_net',
            'isys_catg_wan_list_2_router',
            'isys_catp_appletalk',
            'isys_catp_appletalk_list',
            'isys_cats_ac_list',
            'isys_cats_access_point_list',
            'isys_cats_app_variant_list',
            'isys_cats_application_list',
            'isys_cats_building_list',
            'isys_cats_chassis_list',
            'isys_cats_chassis_list_2_isys_cats_chassis_slot_list',
            'isys_cats_chassis_slot_list',
            'isys_cats_chassis_view_list',
            'isys_cats_client_list',
            'isys_cats_contract_list',
            'isys_cats_cp_contract_list',
            'isys_cats_database_access_list',
            'isys_cats_database_gateway_list',
            'isys_cats_database_instance_list',
            'isys_cats_database_links_list',
            'isys_cats_database_objects_list',
            'isys_cats_database_schema_list',
            'isys_cats_dbms_list',
            'isys_cats_emergency_plan_list',
            'isys_cats_enclosure_list',
            'isys_cats_eps_list',
            'isys_cats_eps_type',
            'isys_cats_file_list',
            'isys_cats_group_list',
            'isys_cats_group_type_list',
            'isys_cats_krypto_card_list',
            'isys_cats_layer2_net_2_iphelper',
            'isys_cats_layer2_net_2_layer3',
            'isys_cats_layer2_net_assigned_ports_list',
            'isys_cats_layer2_net_list',
            'isys_cats_lic_list',
            'isys_cats_mobile_phone_list',
            'isys_cats_monitor_list',
            'isys_cats_net_dhcp_list',
            'isys_cats_net_ip_addresses_list',
            'isys_cats_net_list',
            'isys_cats_net_list_2_isys_catg_ip_list',
            'isys_cats_net_list_2_isys_net_dns_domain',
            'isys_cats_net_zone_list',
            'isys_cats_organization_list',
            'isys_cats_pdu_branch_list',
            'isys_cats_pdu_list',
            'isys_cats_person_group_list',
            'isys_cats_person_list',
            'isys_cats_prt_emulation',
            'isys_cats_prt_list',
            'isys_cats_prt_paper',
            'isys_cats_prt_type',
            'isys_cats_relpool_list',
            'isys_cats_relpool_list_2_isys_obj',
            'isys_cats_replication_list',
            'isys_cats_replication_partner_list',
            'isys_cats_room_list',
            'isys_cats_router_list',
            'isys_cats_san_list',
            'isys_cats_san_zoning_list',
            'isys_cats_service_list',
            'isys_cats_switch_fc_list',
            'isys_cats_switch_net_list',
            'isys_cats_tapelib_list',
            'isys_cats_ups_list',
            'isys_cats_virtual',
            'isys_cats_virtual_list',
            'isys_cats_wan_list',
            'isys_cats_ws_net_type_list',
            'isys_cats_ws_net_type_list_2_isys_obj',
            'isys_certificate_type',
            'isys_chassis_connector_type',
            'isys_chassis_role',
            'isys_client_type',
            'isys_cluster_type',
            'isys_cmdb_status',
            'isys_cmdb_status_changes',
            'isys_connection',
            'isys_connection_type',
            'isys_contact',
            'isys_contact_2_isys_obj',
            'isys_contact_tag',
            'isys_container',
            'isys_contract_end_type',
            'isys_contract_notice_period_type',
            'isys_contract_payment_period',
            'isys_contract_reaction_rate',
            'isys_contract_status',
            'isys_contract_type',
            'isys_controller_manufacturer',
            'isys_controller_model',
            'isys_controller_type',
            'isys_cp_contract_type',
            'isys_csv_profile',
            'isys_currency',
            'isys_custom_properties',
            'isys_database_objects',
            'isys_db_init',
            'isys_dbms',
            'isys_dependency',
            'isys_depth_unit',
            'isys_dialog_plus_custom',
            'isys_drive_list_2_stor_list',
            'isys_export',
            'isys_fc_port_medium',
            'isys_fc_port_path',
            'isys_fc_port_type',
            'isys_fiber_category',
            'isys_fiber_wave_length',
            'isys_file_category',
            'isys_file_physical',
            'isys_file_version',
            'isys_filesystem_type',
            'isys_frequency_unit',
            'isys_graphic_manufacturer',
            'isys_guarantee_period_unit',
            'isys_hba_type',
            'isys_hostaddress_pairs',
            'isys_iface_manufacturer',
            'isys_iface_model',
            'isys_import',
            'isys_import_type',
            'isys_installation_type',
            'isys_interface',
            'isys_interval',
            'isys_ip_assignment',
            'isys_ipv6_assignment',
            'isys_ipv6_scope',
            'isys_its_type',
            'isys_itservice_filter_config',
            'isys_jdisc_ca_type',
            'isys_jdisc_db',
            'isys_jdisc_device_type',
            'isys_jdisc_object_type_assignment',
            'isys_jdisc_profile',
            'isys_layer2_iphelper_type',
            'isys_layer2_net_subtype',
            'isys_layer2_net_type',
            'isys_ldap',
            'isys_ldap_directory',
            'isys_ldev_multipath',
            'isys_ldevclient_fc_port_path',
            'isys_lock',
            'isys_logbook',
            'isys_logbook_2_isys_import',
            'isys_logbook_archive',
            'isys_logbook_configuration',
            'isys_logbook_event',
            'isys_logbook_event_class',
            'isys_logbook_lc_parameter',
            'isys_logbook_level',
            'isys_logbook_reason',
            'isys_logbook_source',
            'isys_maintenance_contract_type',
            'isys_maintenance_reaction_rate',
            'isys_maintenance_status',
            'isys_memory_manufacturer',
            'isys_memory_title',
            'isys_memory_type',
            'isys_memory_unit',
            'isys_migration',
            'isys_model_manufacturer',
            'isys_model_title',
            'isys_module',
            'isys_module_sorting',
            'isys_monitor_resolution',
            'isys_monitor_type',
            'isys_monitor_unit',
            'isys_monitoring_export_config',
            'isys_monitoring_hosts',
            'isys_net_dhcp_type',
            'isys_net_dhcpv6_type',
            'isys_net_dns_domain',
            'isys_net_dns_server',
            'isys_net_protocol',
            'isys_net_protocol_layer_5',
            'isys_net_type',
            'isys_net_type_title',
            'isys_netp_ifacel',
            'isys_netp_ifacel_2_isys_obj',
            'isys_netp_ifacel_standard',
            'isys_netv_ifacel',
            'isys_network_provider',
            'isys_netx_ifacel_type',
            'isys_notification',
            'isys_notification_domain',
            'isys_notification_role',
            'isys_notification_template',
            'isys_notification_type',
            'isys_obj',
            'isys_obj_2_itcockpit',
            'isys_obj_match',
            'isys_obj_type',
            'isys_obj_type_2_isysgui_catg',
            'isys_obj_type_2_isysgui_catg_custom',
            'isys_obj_type_2_isysgui_catg_custom_overview',
            'isys_obj_type_2_isysgui_catg_overview',
            'isys_obj_type_group',
            'isys_obj_type_list',
            'isys_ocs_db',
            'isys_organisation_intern_iop',
            'isys_p_mode',
            'isys_pc_manufacturer',
            'isys_pc_model',
            'isys_pc_title',
            'isys_person_2_group',
            'isys_plug_type',
            'isys_pobj_type',
            'isys_port_duplex',
            'isys_port_mode',
            'isys_port_negotiation',
            'isys_port_speed',
            'isys_port_standard',
            'isys_port_type',
            'isys_pos_gps',
            'isys_power_connection_type',
            'isys_power_fuse_ampere',
            'isys_power_fuse_type',
            'isys_property_2_cat',
            'isys_purpose',
            'isys_qr_code_configuration',
            'isys_raid_type',
            'isys_relation_type',
            'isys_replication_mechanism',
            'isys_replication_type',
            'isys_request_tracker_config',
            'isys_right',
            'isys_right_2_isys_role',
            'isys_role',
            'isys_room_type',
            'isys_routing_protocol',
            'isys_san_capacity_unit',
            'isys_san_zoning_fc_port',
            'isys_search',
            'isys_search_idx',
            'isys_service_alias',
            'isys_service_category',
            'isys_service_console_port',
            'isys_service_manufacturer',
            'isys_service_type',
            'isys_setting',
            'isys_setting_key',
            'isys_settings',
            'isys_site',
            'isys_sla_service_level',
            'isys_snmp_community',
            'isys_sound_manufacturer',
            'isys_stor_con_type',
            'isys_stor_lto_type',
            'isys_stor_manufacturer',
            'isys_stor_model',
            'isys_stor_raid_level',
            'isys_stor_type',
            'isys_stor_unit',
            'isys_switch_role',
            'isys_switch_spanning_tree',
            'isys_tag',
            'isys_tag_2_isys_obj',
            'isys_tapelib_type',
            'isys_telephone_fax_type',
            'isys_telephone_rate',
            'isys_temp_unit',
            'isys_tierclass',
            'isys_tree_group',
            'isys_tts_config',
            'isys_tts_type',
            'isys_ui_con_type',
            'isys_ui_plugtype',
            'isys_unit',
            'isys_unit_of_time',
            'isys_ups_battery_type',
            'isys_ups_type',
            'isys_user_locale',
            'isys_user_mydoit',
            'isys_user_session',
            'isys_user_setting',
            'isys_user_ui',
            'isys_validation_config',
            'isys_virtual_device_host',
            'isys_virtual_device_local',
            'isys_virtual_network_type',
            'isys_virtual_port_group',
            'isys_virtual_storage_type',
            'isys_virtual_switch_2_port',
            'isys_visualization_profile',
            'isys_vlan_management_protocol',
            'isys_vm_type',
            'isys_vmkernel_port',
            'isys_voip_phone_button_template',
            'isys_voip_phone_softkey_template',
            'isys_volume_unit',
            'isys_vrrp_type',
            'isys_wan_capacity_unit',
            'isys_wan_role',
            'isys_wan_type',
            'isys_wato_folder',
            'isys_weight_unit',
            'isys_weighting',
            'isys_widgets',
            'isys_widgets_config',
            'isys_wlan_auth',
            'isys_wlan_channel',
            'isys_wlan_encryption',
            'isys_wlan_function',
            'isys_wlan_standard',
            'isysgui_catg',
            'isysgui_catg_custom',
            'isysgui_cats',
            'isysgui_cats_2_subcategory',
            'temp_obj_data',
        ];
    }
}
