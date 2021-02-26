<?php

use idoit\Component\Helper\Ip;
use idoit\Component\Property\Type\DynamicDialogProperty;
use idoit\Component\Property\Type\DynamicProperty;
use idoit\Component\Property\Type\DynamicYesNoProperty;

define('C__IP__ADDRESS', 1);
define('C__IP__SUBNET', 2);
define('C__IP__GATEWAY', 3);
define('C__IP__NET', 4);
define('C__IP__ASSIGNMENT', 5);
define('C__IP__IPV6_SCOPE', 6);
define('C__IP__IPV6_PREFIX', 7);

/**
 * i-doit
 *
 * CMDB DAO: global category for host addresses
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstucken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_ip extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table and many more.
     *
     * @var  string
     */
    protected $m_category = 'ip';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CATG__IP_ADDRESS';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_cats_net_ip_addresses_list__isys_obj__id';

    /**
     * Name of property which should be used as identifier
     *
     * @var string
     */
    protected $m_entry_identifier = 'hostname';

    /**
     * @var bool
     */
    protected $m_has_relation = true;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_ip_list__isys_obj__id';

    /**
     * Dynamic property handling for getting the primary IP of an object.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_primary_ip($p_row)
    {
        global $g_comp_database;

        $objectId = ($p_row['isys_catg_ip_list__isys_obj__id'] ?: $p_row['isys_obj__id']);

        if (!$objectId) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        $result = isys_cmdb_dao_category_g_ip::instance($g_comp_database)
            ->get_data(null, $objectId, "AND isys_catg_ip_list__primary = 1", null, C__RECORD_STATUS__NORMAL);

        if (is_countable($result) && count($result)) {
            return $result->get_row_value('isys_cats_net_ip_addresses_list__title');
        }
        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Dynamic property handling for getting the primary hostname of an object.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_primary_hostname($p_row)
    {
        global $g_comp_database;

        $objectId = ($p_row['isys_catg_ip_list__isys_obj__id'] ?: $p_row['isys_obj__id']);

        if (!$objectId) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        $l_hostname = isys_cmdb_dao_category_g_ip::instance($g_comp_database)
            ->get_data(null, $objectId, "AND isys_catg_ip_list__primary = 1", null, C__RECORD_STATUS__NORMAL)
            ->get_row_value('isys_catg_ip_list__hostname');

        if ($l_hostname === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        return $l_hostname;
    }

    /**
     * Dynamic property callback for retrieving the FQDN
     *
     * @param $p_row
     *
     * @return mixed|string
     * @throws isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_hostname_fqdn($p_row)
    {
        $l_dao = isys_cmdb_dao_category_g_ip::instance(isys_application::instance()->database);
        $l_res = $l_dao->retrieve("SELECT CONCAT(isys_catg_ip_list__hostname , '.', isys_catg_ip_list__domain) AS val
            FROM isys_catg_ip_list
            WHERE isys_catg_ip_list__id = " . $l_dao->convert_sql_id($p_row['isys_catg_ip_list__id']) . "
            AND isys_catg_ip_list__hostname != ''

            UNION

            SELECT CONCAT(isys_hostaddress_pairs__hostname , '.', isys_hostaddress_pairs__domain) AS val
            FROM isys_catg_ip_list
            INNER JOIN isys_hostaddress_pairs ON isys_catg_ip_list__id = isys_hostaddress_pairs__isys_catg_ip_list__id
            WHERE isys_catg_ip_list__id = " . $l_dao->convert_sql_id($p_row['isys_catg_ip_list__id']) . "
            AND isys_hostaddress_pairs__hostname != '';");
        $l_return = isys_tenantsettings::get('gui.empty_value', '-');

        if ($l_res->num_rows() > 0) {
            $l_return = '';
            while ($l_row = $l_res->get_row()) {
                $l_return .= $l_row['val'] . ', ';
            }
            $l_return = rtrim($l_return, ', ');
        }

        return $l_return;
    }

    /**
     * Dynamic property callback for retrieving the FQDN
     *
     * @param $p_row
     *
     * @return mixed|string
     * @throws isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_dns_domain($p_row)
    {
        $l_dao = isys_cmdb_dao_category_g_ip::instance(isys_application::instance()->database);
        $l_res = $l_dao->retrieve('SELECT dns.isys_net_dns_domain__title AS val
            FROM isys_catg_ip_list AS ip1
            INNER JOIN isys_catg_ip_list_2_isys_net_dns_domain AS ip2dns ON ip2dns.isys_catg_ip_list__id = ip1.isys_catg_ip_list__id
            INNER JOIN isys_net_dns_domain AS dns ON dns.isys_net_dns_domain__id = ip2dns.isys_net_dns_domain__id
            WHERE ip1.isys_catg_ip_list__id = ' . $l_dao->convert_sql_id($p_row['isys_catg_ip_list__id']));
        $l_return = isys_tenantsettings::get('gui.empty_value', '-');

        if ($l_res->num_rows() > 0) {
            $l_return = '';
            while ($l_row = $l_res->get_row()) {
                $l_return .= $l_row['val'] . ', ';
            }
            $l_return = rtrim($l_return, ', ');
        }

        return $l_return;
    }

    /**
     * @param isys_request $p_request
     *
     * @return int
     */
    public function callback_property_use_standard_gateway(isys_request $p_request)
    {
        $l_catdata = $p_request->get_row();

        if (empty($l_catdata) && $p_request->get_category_data_id()) {
            $query = 'SELECT isys_cats_net_ip_addresses_list__isys_obj__id, isys_catg_ip_list__id FROM isys_catg_ip_list
                INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
                WHERE isys_catg_ip_list__id = ' . $this->convert_sql_int($p_request->get_category_data_id());
            $l_catdata = $this->retrieve($query)->get_row();
        }

        $l_net_row = isys_cmdb_dao_category_s_net::instance($this->get_database_component())
            ->get_all_net_information_by_obj_id($l_catdata['isys_cats_net_ip_addresses_list__isys_obj__id']);

        return ((!empty($l_net_row['isys_cats_net_list__isys_catg_ip_list__id']) && is_array($l_catdata) &&
            $l_net_row['isys_cats_net_list__isys_catg_ip_list__id'] == $l_catdata['isys_catg_ip_list__id']) ? 1 : 0);
    }

    /**
     * Get DNS domains.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @throws  isys_exception_general
     */
    public function callback_property_dns_domain(isys_request $p_request)
    {
        $l_catdata = $p_request->get_row();
        $l_ar_data = [];

        // Let us prevent returning all available domains
        if (isset($l_catdata['isys_catg_ip_list__id']) && is_numeric($l_catdata['isys_catg_ip_list__id'])) {
            // Get assigned domains
            $l_assigned_dns_domain = isys_cmdb_dao_category_g_ip::instance($this->get_database_component())
                ->get_assigned_dns_domain(null, $l_catdata['isys_catg_ip_list__id']);

            if (is_countable($l_assigned_dns_domain) && count($l_assigned_dns_domain)) {
                // Build array
                while ($l_row_dns_domain = $l_assigned_dns_domain->get_row()) {
                    $l_ar_data[] = [
                        "caption" => $l_row_dns_domain['isys_net_dns_domain__title'],
                        "value"   => $l_row_dns_domain['isys_net_dns_domain__id']
                    ];
                }
            }
        }

        return $l_ar_data;
    }

    /**
     * Callback method for dns server
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   isys_request            $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function callback_property_dns_server(isys_request $p_request)
    {
        $l_return = [];

        $l_row = $p_request->get_row();
        if (isset($l_row['isys_catg_ip_list__id'])) {
            $l_return = $this->get_assigned_dns_server($l_row['isys_catg_ip_list__id']);
        }

        return $l_return;
    }

    /**
     * Callback method for the ports dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_ports(isys_request $p_request)
    {
        $l_obj_id = $p_request->get_object_id();

        $l_return = $l_vrrp_cache = [];
        $l_dao_port = isys_cmdb_dao_category_g_network_port::instance($this->get_database_component());
        $l_dao_log_port = isys_cmdb_dao_category_g_network_ifacel::instance($this->get_database_component());
        $l_dao_vrrp_member = isys_cmdb_dao_category_g_vrrp_member::instance($this->m_db);

        $l_res_port = $l_dao_port->get_data(null, $l_obj_id, '', null, C__RECORD_STATUS__NORMAL);
        $l_res_log_port = $l_dao_log_port->get_data_with_vrrp($l_obj_id, C__RECORD_STATUS__NORMAL);

        // We retrieve the language string once, instead of in every iteration!
        $l_port = isys_application::instance()->container->get('language')
            ->get('LC__CMDB__CATG__NETWORK_TREE_CONFIG_PORTS');
        $l_log_port = isys_application::instance()->container->get('language')
            ->get('LC__CMDB__CATG__NETWORK_TREE_CONFIG_PORT_L');

        while ($l_row = $l_res_port->get_row()) {
            $l_return[$l_port][$l_row['isys_catg_port_list__id'] . '_C__CATG__NETWORK_PORT'] = $l_row['isys_catg_port_list__title'];
        }

        while ($l_row = $l_res_log_port->get_row()) {
            $l_return[$l_log_port][$l_row['local_logport_id'] . '_C__CATG__NETWORK_LOG_PORT'] = $l_row['local_logport_title'];

            // Add logical ports from connected VRRP objects.
            if (!isset($l_vrrp_cache[$l_row['vrrp_obj_id']]) && $l_row['vrrp_obj_id'] > 0) {
                $l_vrrp_cache[$l_row['vrrp_obj_id']] = true;

                $l_vrrp_res = $l_dao_vrrp_member->get_vrrp_members($l_row['vrrp_obj_id'], C__RECORD_STATUS__NORMAL);

                while ($l_vrrp_row = $l_vrrp_res->get_row()) {
                    if ($l_vrrp_row['isys_obj__id'] != $l_obj_id) {
                        $l_return[$l_log_port . ' (' . isys_application::instance()->container->get('language')
                            ->get($l_row['vrrp_obj_type_title']) . ' &raquo; ' . $l_row['vrrp_obj_title'] . ')'][$l_vrrp_row['isys_catg_log_port_list__id'] .
                        '_C__CATG__NETWORK_LOG_PORT'] = $l_vrrp_row['isys_catg_log_port_list__title'];
                    }
                }
            }
        }

        // @see  ID-7620  Use natural sorting.
        foreach ($l_return as &$portCollection) {
            if (is_array($portCollection) && count($portCollection)) {
                uasort($portCollection, 'strnatcasecmp');
            }
        }

        return $l_return;
    }

    /**
     * Callback method for the "catdata" browser. Maybe we can switch the first parameter to an instance of isys_request?
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function catdata_browser($p_obj_id)
    {
        $l_return = [];
        $l_res = $this->get_data(null, $p_obj_id, "", null, C__RECORD_STATUS__NORMAL);

        while ($l_row = $l_res->get_row()) {
            $l_val = $l_row['isys_cats_net_ip_addresses_list__title'];

            if (!empty($l_row['isys_catg_ip_list__hostname'])) {
                $l_val .= ' (' . $l_row['isys_catg_ip_list__hostname'] . ')';
            }

            $l_return[$l_row['isys_catg_ip_list__id']] = $l_val;
        }

        return $l_return;
    }

    /**
     * Compares category data for import.
     *
     * If your unique properties needs them, implement it!
     *
     * @param array        $p_category_data_values
     * @param array        $p_object_category_dataset
     * @param array        $p_used_properties
     * @param array        $p_comparison
     * @param array        $p_badness
     * @param integer      $p_mode
     * @param integer      $p_category_id
     * @param string       $p_unit_key
     * @param array        $p_category_data_ids
     * @param array|object $p_local_export
     * @param boolean      $p_dataset_id_changed
     * @param integer      $p_dataset_id
     * @param isys_log     $p_logger
     * @param string       $p_category_name
     * @param string       $p_table
     * @param mixed        $p_cat_multi
     * @param integer      $p_category_type_id
     * @param array        $p_category_ids
     * @param array        $p_object_ids
     * @param array        $p_already_used_data_ids
     *
     * @throws isys_exception_cmdb
     */
    public function compare_category_data(
        &$p_category_data_values,
        &$p_object_category_dataset,
        &$p_used_properties,
        &$p_comparison,
        &$p_badness,
        &$p_mode,
        &$p_category_id,
        &$p_unit_key,
        &$p_category_data_ids,
        &$p_local_export,
        &$p_dataset_id_changed,
        &$p_dataset_id,
        &$p_logger,
        &$p_category_name = null,
        &$p_table = null,
        &$p_cat_multi = null,
        &$p_category_type_id = null,
        &$p_category_ids = null,
        &$p_object_ids = null,
        &$p_already_used_data_ids = null
    ) {
        if (defined('C__CATG__NETWORK_PORT') && !empty($p_category_data_values['properties']['assigned_port']['id'])) {
            $p_category_data_values['properties']['assigned_port']['value'] = $p_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__NETWORK_PORT][$p_category_data_values['properties']['assigned_port']['id']];
        }

        if (defined('C__CATG__NETWORK_LOG_PORT') && !empty($p_category_data_values['properties']['assigned_logical_port']['id'])) {
            $p_category_data_values['properties']['assigned_logical_port']['value'] = $p_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__NETWORK_LOG_PORT][$p_category_data_values['properties']['assigned_logical_port']['id']];
        }

        foreach ($p_object_category_dataset as $l_dataset_key => $l_dataset) {
            $p_dataset_id_changed = false;
            $p_dataset_id = $l_dataset[$p_table . '__id'];

            if (isset($p_already_used_data_ids[$p_dataset_id])) {
                // Skip it ID has already been used
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                $p_logger->debug('Dateset ID "' . $p_dataset_id . '" has already been handled. Skipping to next entry.');
                continue;
            }

            // Test the category data identifier:
            if ($p_mode === isys_import_handler_cmdb::C__USE_IDS && $p_category_data_values['data_id'] !== $p_dataset_id) {
                $p_logger->debug('Category data identifier is different.');
                $p_badness[$p_dataset_id]++;
                $p_dataset_id_changed = true;

                if ($p_mode === isys_import_handler_cmdb::C__USE_IDS) {
                    continue;
                }
            }

            if (defined('C__CATS_NET_TYPE__IPV4') && $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['net_type']['id'] == C__CATS_NET_TYPE__IPV4) {
                $l_import_ip = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['ipv4_address']['ref_title'];
                $l_net_object = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['ipv4_address']['id'];
            } else {
                $l_import_ip = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['ipv6_address']['ref_title'];
                $l_net_object = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['ipv6_address']['id'];
            }

            if ($l_import_ip === $l_dataset['isys_cats_net_ip_addresses_list__title'] && $l_net_object === $l_dataset['isys_cats_net_ip_addresses_list__isys_obj__id'] &&
                trim($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['hostname']['value']) == $l_dataset['isys_catg_ip_list__hostname'] &&
                trim($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['domain']['value']) == $l_dataset['isys_catg_ip_list__domain']) {
                // Entry found
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;

                return;
            } elseif (($l_import_ip === $l_dataset['isys_cats_net_ip_addresses_list__title'] &&
                    (trim($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['hostname']['value']) != $l_dataset['isys_catg_ip_list__hostname'] ||
                        trim($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['domain']['value']) != $l_dataset['isys_catg_ip_list__domain'])) ||
                ($l_import_ip !== $l_dataset['isys_cats_net_ip_addresses_list__title'] &&
                    trim($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['hostname']['value']) == $l_dataset['isys_catg_ip_list__hostname'] &&
                    trim($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['domain']['value']) == $l_dataset['isys_catg_ip_list__domain'])) {
                // @See ID-4753: Check IP is different but hostname and domain are the same or if IP is the same but hostname or domain are differnt
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY][$l_dataset_key] = $p_dataset_id;
            } else {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
            }
        }
    }

    /**
     * Save method.
     *
     * @param   integer $p_id
     * @param   string  $p_hostname
     * @param   integer $p_assign
     * @param   string  $p_address
     * @param   integer $p_primary
     * @param   string  $p_gw
     * @param   array   $p_dns_server
     * @param   array   $p_dns_domain
     * @param   integer $p_active
     * @param   integer $p_net_type
     * @param   integer $p_net_connection
     * @param   string  $p_description
     * @param   integer $p_status
     * @param   integer $p_port_assignment
     * @param   integer $p_log_port_assignment
     * @param   integer $p_ipv6_assignment
     * @param   integer $p_ipv6_scope
     * @param   integer $p_zone_obj_id
     * @param   string  $p_domain
     * @param   array   $p_fqdn_pairs
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     * @return  boolean
     */
    public function save(
        $p_id,
        $p_hostname,
        $p_assign,
        $p_address,
        $p_primary,
        $p_gw,
        $p_dns_server,
        $p_dns_domain,
        $p_active,
        $p_net_type,
        $p_net_connection,
        $p_description,
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_port_assignment = null,
        $p_log_port_assignment = null,
        $p_ipv6_assignment = null,
        $p_ipv6_scope = null,
        $p_zone_obj_id = null,
        $p_domain = null,
        $p_fqdn_pairs = null
    ) {
        $l_dao_ip_address = isys_cmdb_dao_category_s_net_ip_addresses::instance($this->get_database_component());

        $l_data = $this->get_data($p_id)
            ->__to_array();

        if (!$p_assign) {
            $p_assign = defined_or_default('C__CATP__IP__ASSIGN__STATIC', 2);
        }

        if (!$p_net_type) {
            /**
             * Find out the correct network type
             */
            if (strstr($p_address, '.')) {
                $p_net_type = defined_or_default('C__CATS_NET_TYPE__IPV4');
            } elseif (strstr($p_address, ':')) {
                $p_net_type = defined_or_default('C__CATS_NET_TYPE__IPV6');
            }
        }

        $l_catg = "isys_catg_ip";

        $l_new_ip = true;

        if (empty($p_dns_server) || $p_dns_server < 0) {
            $p_dns_server = null;
        }

        if (empty($p_dns_domain)) {
            $p_dns_domain = null;
        }

        if ($l_data['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'] > 0) {
            $l_net_connection = $l_data['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'];
            $l_ip_address_data = $l_dao_ip_address->get_data($l_data['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'])
                ->get_row();

            $l_new_ip = false;

            if ($l_ip_address_data['isys_cats_net_ip_addresses_list__title'] != $p_address ||
                $l_ip_address_data['isys_cats_net_ip_addresses_list__isys_obj__id'] != $p_net_connection) {
                $l_dao_ip_address->save(
                    $l_data['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'],
                    $p_address,
                    $p_net_connection,
                    $l_ip_address_data['isys_cats_net_ip_addresses_list__isys_ip_assignment__id'],
                    $l_ip_address_data['isys_cats_net_ip_addresses_list__status']
                );
            }
        } else {
            $l_data['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'] = $l_dao_ip_address->create($p_address, $p_net_connection, $p_assign);
        }

        // key primary as string otherwise we get a NULL which we don't wanna have
        $l_content = [
            "hostname"                            => $p_hostname,
            "domain"                              => $p_domain,
            "primary"                             => ($p_primary > 0) ? $p_primary : '0',
            "active"                              => $p_active,
            "description"                         => $p_description,
            "isys_net_type__id"                   => $p_net_type,
            "isys_cats_net_ip_addresses_list__id" => $l_data['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'],
            "status"                              => $p_status,
            "isys_catg_port_list__id"             => $p_port_assignment,
            "isys_catg_log_port_list__id"         => $p_log_port_assignment,
            "isys_obj__id__zone"                  => ($p_zone_obj_id > 0 ? $p_zone_obj_id : null)
        ];

        if ($p_net_type == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
            $l_content['isys_ip_assignment__id'] = $p_assign;
        } elseif ($p_net_type == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
            $l_content['isys_ipv6_assignment__id'] = $p_ipv6_assignment;
            $l_content['isys_ipv6_scope__id'] = $p_ipv6_scope;
        }

        if (is_numeric($p_id)) {
            if ($p_primary == 1) {
                $this->update_primary_hostaddress($p_id);
            }

            $l_sql = $this->build_query($l_catg . "_list", $l_content, $p_id);

            // Create implicit relation.
            $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());

            $l_data = $this->get_data($p_id)
                ->__to_array();

            if ($this->update($l_sql) && $this->apply_update()) {
                if ($l_new_ip) {
                    $l_net_connection = $l_dao_ip_address->create($p_address, $p_net_connection, $p_assign, C__RECORD_STATUS__NORMAL);
                }

                // Add DNS server.
                $this->clear_dns_server_attachments($p_id);
                if (is_array($p_dns_server) && count($p_dns_server) > 0) {
                    foreach ($p_dns_server as $l_dns_id) {
                        $this->attach_dns_server($p_id, $l_dns_id);
                    }
                } elseif (is_scalar($p_dns_server) && is_numeric($p_dns_server)) {
                    $this->attach_dns_server($p_id, $p_dns_server);
                }

                // Add DNS domain.
                $this->clear_dns_domain_attachments($p_id);

                if (is_array($p_dns_domain) && count($p_dns_domain) > 0) {
                    // Prevent duplicates
                    $p_dns_domain = array_unique($p_dns_domain);

                    foreach ($p_dns_domain as $l_domain_id) {
                        $this->attach_dns_domain($p_id, $l_domain_id);
                    }
                }

                if ($p_gw > 0) {
                    $this->attach_gateway(null, $p_net_connection, $p_id);
                } elseif ($this->is_gateway($p_id, $p_net_connection)) {
                    $this->attach_gateway(null, $p_net_connection, null);
                }

                $l_relation_dao->handle_relation(
                    $p_id,
                    "isys_catg_ip_list",
                    defined_or_default('C__RELATION_TYPE__IP_ADDRESS'),
                    $l_data["isys_catg_ip_list__isys_catg_relation_list__id"],
                    $p_net_connection,
                    $l_data["isys_catg_ip_list__isys_obj__id"]
                );

                // Attach additional hostnames / domains.
                if (is_array($p_fqdn_pairs)) {
                    $this->set_hostname_pairs($p_id, $p_fqdn_pairs);
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Create method.
     *
     * @param   integer $p_object_id
     * @param   string  $p_hostname
     * @param   integer $p_ip_assignment
     * @param   string  $p_address
     * @param   integer $p_primary
     * @param   integer $p_gw
     * @param   array   $p_dns_server
     * @param   array   $p_dns_domain
     * @param   integer $p_active
     * @param   integer $p_net_type
     * @param   integer $p_net_connection
     * @param   string  $p_description
     * @param   integer $p_status
     * @param   integer $p_port_assignment
     * @param   integer $p_log_port_assignment
     * @param   integer $p_ipv6_scope
     * @param   integer $p_ip6_assignment
     * @param   integer $p_zone_obj_id
     * @param   string  $p_domain
     * @param   array   $p_fqdn_pairs
     *
     * @return boolean
     * @throws Exception
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @author Dennis Stücken <dstuecken@synetics.de>
     * @author Van Quyen Hoang <qhoang@synetics.de>
     * @author Leonard Fischer <lfischre@i-doit.com>
     */
    public function create(
        $p_object_id,
        $p_hostname,
        $p_ip_assignment,
        $p_address,
        $p_primary,
        $p_gw,
        $p_dns_server,
        $p_dns_domain,
        $p_active,
        $p_net_type,
        $p_net_connection,
        $p_description,
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_port_assignment = null,
        $p_log_port_assignment = null,
        $p_ipv6_scope = null,
        $p_ip6_assignment = null,
        $p_zone_obj_id = null,
        $p_domain = null,
        $p_fqdn_pairs = null
    ) {
        /**
         * @var $l_dao_ip_address isys_cmdb_dao_category_s_net_ip_addresses
         */
        $l_dao_ip_address = isys_cmdb_dao_category_s_net_ip_addresses::instance($this->get_database_component());

        if (!$p_ip_assignment) {
            $p_ip_assignment = defined_or_default('C__CATP__IP__ASSIGN__STATIC', 2);
        }

        if (!$p_net_type) {
            /**
             * Find out the correct network type
             */
            if (strstr($p_address, '.')) {
                $p_net_type = defined_or_default('C__CATS_NET_TYPE__IPV4');
            } elseif (strstr($p_address, ':')) {
                $p_net_type = defined_or_default('C__CATS_NET_TYPE__IPV6');
            }
        }

        $l_last_net_list_id = $l_dao_ip_address->create(
            $p_address,
            ((!empty($p_net_connection)) ? $p_net_connection : (($p_net_type == defined_or_default('C__CATS_NET_TYPE__IPV4')) ? defined_or_default('C__OBJ__NET_GLOBAL_IPV4') : defined_or_default('C__OBJ__NET_GLOBAL_IPV6'))),
            $p_ip_assignment,
            C__RECORD_STATUS__NORMAL
        );

        // key primary as string otherwise we get a NULL which we don't wanna have
        $l_content = [
            "isys_obj__id"                        => $p_object_id,
            "hostname"                            => $p_hostname,
            "domain"                              => $p_domain,
            "isys_net_type__id"                   => $p_net_type,
            "primary"                             => ($p_primary > 0) ? $p_primary : '0',
            "active"                              => $p_active,
            "description"                         => $p_description,
            "isys_cats_net_ip_addresses_list__id" => $l_last_net_list_id,
            "status"                              => $p_status,
            "isys_catg_port_list__id"             => $p_port_assignment,
            "isys_catg_log_port_list__id"         => $p_log_port_assignment,
            "isys_obj__id__zone"                  => ($p_zone_obj_id > 0 ? $p_zone_obj_id : null),
        ];

        if ($p_net_type == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
            $l_content['isys_ip_assignment__id'] = $p_ip_assignment;
        } elseif ($p_net_type == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
            $l_content['isys_ipv6_assignment__id'] = $p_ip6_assignment;
            $l_content['isys_ipv6_scope__id'] = $p_ipv6_scope;
        }

        if (is_numeric($p_object_id)) {
            $l_sql = $this->build_query("isys_catg_ip_list", $l_content, null, C__DB_GENERAL__INSERT);

            if ($this->update($l_sql) && $this->apply_update()) {
                $l_last_id = $this->get_last_insert_id();

                // When the new host address is saved as primary, we have to set all the others to "not-primary".
                if ($p_primary == 1) {
                    $this->update_primary_hostaddress($l_last_id);
                }

                /**
                 * Attaching dns server(s)
                 */
                if (is_array($p_dns_server)) {
                    if (count($p_dns_server) > 0) {
                        foreach ($p_dns_server as $l_dns_id) {
                            $this->attach_dns_server($l_last_id, $l_dns_id);
                        }
                    }
                } elseif (is_scalar($p_dns_server) && is_numeric($p_dns_server)) {
                    $this->attach_dns_server($l_last_id, $p_dns_server);
                }

                if (is_array($p_dns_domain) && count($p_dns_domain)) {
                    // Prevent duplicates.
                    $p_dns_domain = array_unique($p_dns_domain);

                    foreach ($p_dns_domain as $l_domain_id) {
                        $this->attach_dns_domain($l_last_id, $l_domain_id);
                    }
                }

                if ($p_gw > 0) {
                    $this->attach_gateway(null, $p_net_connection, $l_last_id);
                }

                // Create implicit relation.
                $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());
                $l_relation_dao->handle_relation($l_last_id, "isys_catg_ip_list", defined_or_default('C__RELATION_TYPE__IP_ADDRESS'), null, $p_net_connection, $p_object_id);

                // Attach additional hostnames / domains.
                if (is_array($p_fqdn_pairs) && count($p_fqdn_pairs)) {
                    $this->set_hostname_pairs($l_last_id, $p_fqdn_pairs);
                }

                return $l_last_id;
            }
        } else {
            throw new Exception("Object ID not numeric. IP creation failed!");
        }

        return false;
    }

    /**
     * Merges posted IPv6 and IPv4 data into one data array so that the addresses could be stored in only one database field.
     *
     * @param int $p_net_type Net type (IPv4, IPv6,...)
     *
     * @return array
     */
    public function merge_posted_ip_data($p_net_type, $p_key = null, $p_data = null)
    {
        $l_result = [];

        if ($p_data === null) {
            $p_data = $_POST;
        }

        if ($p_net_type == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
            if ($p_key === null) {
                $p_key = 'C__CATP__IP__ADDRESS_V4';
            }

            if (isset($p_data['C__CATP__IP__SUBNETMASK_V4'])) {
                if (is_array($p_data['C__CATP__IP__SUBNETMASK_V4'])) {
                    $l_result[C__IP__SUBNET] = implode('.', $p_data['C__CATP__IP__SUBNETMASK_V4']);
                } else {
                    $l_result[C__IP__SUBNET] = $p_data['C__CATP__IP__SUBNETMASK_V4'];
                }

                $l_result[C__IP__SUBNET] = long2ip(ip2long($l_result[C__IP__SUBNET]));
            }

            if (isset($p_data[$p_key])) {
                if (is_array($p_data[$p_key])) {
                    $l_result[C__IP__ADDRESS] = implode('.', $p_data[$p_key]);
                } else {
                    $l_result[C__IP__ADDRESS] = $p_data[$p_key];
                }

                // If we enter a "empty" IP-address we don't want it transformed to "0.0.0.0".
                if (Ip::validate_ip($l_result[C__IP__ADDRESS])) {
                    $l_result[C__IP__ADDRESS] = long2ip(ip2long($l_result[C__IP__ADDRESS]));
                }
            }
        } elseif ($p_net_type == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
            if ($p_key === null) {
                $p_key = 'C__CMDB__CATG__IP__IPV6_ADDRESS';
            }

            if (array_key_exists('C__CMDB__CATG__IP__IPV6_ASSIGNMENT', $p_data)) {
                $l_result[C__IP__ASSIGNMENT] = $p_data['C__CMDB__CATG__IP__IPV6_ASSIGNMENT'];
            }
            if (array_key_exists('C__CMDB__CATG__IP__IPV6_SCOPE', $p_data)) {
                $l_result[C__IP__IPV6_SCOPE] = $p_data['C__CMDB__CATG__IP__IPV6_SCOPE'];
            }
            if (array_key_exists('C__CMDB__CATG__IP__IPV6_PREFIX', $p_data)) {
                $l_result[C__IP__IPV6_PREFIX] = $p_data['C__CMDB__CATG__IP__IPV6_PREFIX'];
            }
            if (array_key_exists($p_key, $p_data)) {
                $l_result[C__IP__ADDRESS] = Ip::validate_ipv6($p_data[$p_key]);
            }
        }

        return $l_result;
    }

    /**
     * Save element method.
     *
     * @param   integer $p_cat_layer
     * @param   integer & $p_status
     * @param   boolean $p_create
     *
     * @return  mixed
     * @author  Dennis Stücken <dstuecken@synetics.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_element(&$p_cat_layer, &$p_status, $p_create = false)
    {
        $l_cat = $this->get_general_data();

        // Unset entryID for calls from overview
        if (isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW')) {
            unset($_GET[C__CMDB__GET__CATLEVEL]);
        }

        if ($l_cat === null && !empty($_POST['C__CATG__IP__ID'])) {
            $l_cat = $this->get_data($_POST['C__CATG__IP__ID'])
                ->get_row();
            if ($l_cat && is_countable($l_cat) && count($l_cat) > 0) {
                $p_create = false;
            } else {
                $p_create = true;
            }
        } elseif ($l_cat === null && isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW')) {
            $l_cat = $this->get_primary_ip($_GET[C__CMDB__GET__OBJECT])
                ->get_row();
            $l_id = $l_cat['isys_catg_ip_list__id'];
            if ($l_cat && is_countable($l_cat) && count($l_cat) > 0) {
                $p_create = false;
            } else {
                $p_create = true;
            }
        } elseif ($l_cat === null) {
            $p_create = true;
        }

        $p_status = $l_cat['isys_catg_ip_list__status'];
        $l_port_type = null;
        $l_port_id = null;

        if (empty($l_id)) {
            if (isset($_GET[C__CMDB__GET__CATLEVEL]) && $_GET[C__CMDB__GET__CATLEVEL] > 0) {
                $l_id = $_GET[C__CMDB__GET__CATLEVEL];
            } elseif (!empty($_POST['C__CATG__IP__ID'])) {
                $l_id = $_POST['C__CATG__IP__ID'];
            }
        }
        //Check which network address type is selected and assign its corresponding address values.
        $l_ip_data = $this->merge_posted_ip_data($_POST['C__NET__TYPE']);

        // Bugfix for not be able to save empty IP-addresses.
        if ($l_ip_data[C__IP__ADDRESS] == '...') {
            $l_ip_data[C__IP__ADDRESS] = '';
        }

        // Dont save on overview category if values are empty.
        if (isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW') && ($l_ip_data[C__IP__ADDRESS] == '' || $l_ip_data[C__IP__ADDRESS] == '0.0.0.0') &&
            ($l_ip_data[C__IP__SUBNET] == '' || $l_ip_data[C__IP__SUBNET] == '0.0.0.0') && $_POST['C__CATG__IP__NET__HIDDEN'] == defined_or_default('C__OBJ__NET_GLOBAL_IPV4') &&
            empty($_POST['C__CATP__IP__SEARCH_DOMAIN']) && $_POST['C__CATP__IP__HOSTNAME'] == '' && $_POST['C__CATG__IP__ASSIGNED_DNS_SERVER__HIDDEN'] == '[]' &&
            $_POST['C__CATP__IP__ACTIVE'] == '1' && $_POST['C__CATP__IP__PRIMARY'] == '1') {
            return null;
        }

        // We check if a layer3 net has been assigned - If now, we assign the global v4 or v6 (depends on the net type).
        if (empty($_POST['C__CATG__IP__NET__HIDDEN'])) {
            if ($_POST['C__NET__TYPE'] == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
                $_POST['C__CATG__IP__NET__HIDDEN'] = defined_or_default('C__OBJ__NET_GLOBAL_IPV6');
            } else {
                $_POST['C__CATG__IP__NET__HIDDEN'] = defined_or_default('C__OBJ__NET_GLOBAL_IPV4');
            }
        }

        // Port assignment.
        if (isset($_POST["C__CATG__IP__ASSIGNED_PORTS"]) && $_POST["C__CATG__IP__ASSIGNED_PORTS"] != -1) {
            $l_port_type = substr($_POST["C__CATG__IP__ASSIGNED_PORTS"], (strpos($_POST["C__CATG__IP__ASSIGNED_PORTS"], '_') + 1));

            if ($l_port_type == 'C__CATG__NETWORK_PORT') {
                $l_port_id = substr($_POST["C__CATG__IP__ASSIGNED_PORTS"], 0, (strpos($_POST["C__CATG__IP__ASSIGNED_PORTS"], '_')));
                $l_log_port_id = null;
            } elseif ($l_port_type == 'C__CATG__NETWORK_LOG_PORT') {
                $l_port_id = null;
                $l_log_port_id = substr($_POST["C__CATG__IP__ASSIGNED_PORTS"], 0, (strpos($_POST["C__CATG__IP__ASSIGNED_PORTS"], '_')));
            }
        }

        $l_ipv6_assignment = null;
        if (array_key_exists('C__CMDB__CATG__IP__IPV6_ASSIGNMENT', $_POST)) {
            $l_ipv6_assignment = $_POST['C__CMDB__CATG__IP__IPV6_ASSIGNMENT'];
        }
        $l_ipv6_scope = null;

        if (array_key_exists('C__CMDB__CATG__IP__IPV6_SCOPE', $_POST)) {
            $l_ipv6_scope = $_POST['C__CMDB__CATG__IP__IPV6_SCOPE'];
        }

        // Before we create or save the host-address, we check if we are forced to create a new DHCP (reserved) area.
        if (!empty($l_ip_data[C__IP__ADDRESS])) {
            // We know that we have a IP address and a DHCP (v4/v6) or DHCP reserved (v4/v6) assignment.
            $l_dhcp_dao = isys_cmdb_dao_category_s_net_dhcp::instance($this->get_database_component());

            // We can not handle IPv4 and IPv6 the same way.
            if ($_POST['C__NET__TYPE'] == defined_or_default('C__CATS_NET_TYPE__IPV4') &&
                ($_POST['C__CATP__IP__ASSIGN'] == defined_or_default('C__CATP__IP__ASSIGN__DHCP_RESERVED') || $_POST['C__CATP__IP__ASSIGN'] == defined_or_default('C__CATP__IP__ASSIGN__DHCP'))) {
                $l_type = defined_or_default('C__NET__DHCP_DYNAMIC');

                if ($_POST['C__CATP__IP__ASSIGN'] == defined_or_default('C__CATP__IP__ASSIGN__DHCP_RESERVED')) {
                    $l_type = defined_or_default('C__NET__DHCP_RESERVED');
                }

                $l_dhcp_dao->check_and_merge_new_dhcp_range_inside_existing($_POST['C__CATG__IP__NET__HIDDEN'], $l_type, $l_ip_data[C__IP__ADDRESS]);
            } elseif ($_POST['C__NET__TYPE'] == defined_or_default('C__CATS_NET_TYPE__IPV6') && ($_POST['C__CMDB__CATG__IP__IPV6_ASSIGNMENT'] == defined_or_default('C__CMDB__CATG__IP__DHCPV6 ')||
                    $_POST['C__CMDB__CATG__IP__IPV6_ASSIGNMENT'] == defined_or_default('C__CMDB__CATG__IP__DHCPV6_RESERVED'))) {
                // Handle IPv6 addresses here.
                $l_type = defined_or_default('C__NET__DHCPV6__DHCPV6');

                if ($_POST['C__CMDB__CATG__IP__IPV6_ASSIGNMENT'] == defined_or_default('C__CMDB__CATG__IP__DHCPV6_RESERVED')) {
                    $l_type = defined_or_default('C__NET__DHCPV6__DHCPV6_RESERVED');
                }

                $l_dhcp_dao->check_and_merge_new_dhcpv6_range_inside_existing($_POST['C__CATG__IP__NET__HIDDEN'], $l_type, $l_ip_data[C__IP__ADDRESS]);
            }
        }

        $l_fqdn_pairs = [];

        if (is_array($_POST['C__CATP__IP__HOSTNAME_ADDITIONAL'])) {
            foreach ($_POST['C__CATP__IP__HOSTNAME_ADDITIONAL'] as $i => $hostnameAdditional) {
                $l_fqdn_pairs[] = [
                    'host'   => $hostnameAdditional,
                    'domain' => $_POST['C__CATP__IP__DOMAIN_ADDITIONAL'][$i]
                ];
            }
        }

        if ($p_create) {
            $l_return = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                $_POST['C__CATP__IP__HOSTNAME'],
                ($_POST['C__NET__TYPE'] != defined_or_default('C__CATS_NET_TYPE__IPV6')) ? $_POST['C__CATP__IP__ASSIGN'] : $l_ipv6_assignment,
                $l_ip_data[C__IP__ADDRESS],
                $_POST['C__CATP__IP__PRIMARY'],
                $_POST['C__CATG__IP__GW__CHECK'],
                isys_format_json::decode($_POST['C__CATG__IP__ASSIGNED_DNS_SERVER__HIDDEN']),
                $_POST['C__CATP__IP__SEARCH_DOMAIN'],
                $_POST['C__CATP__IP__ACTIVE'],
                $_POST['C__NET__TYPE'],
                $_POST['C__CATG__IP__NET__HIDDEN'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()],
                C__RECORD_STATUS__NORMAL,
                $l_port_id,
                $l_log_port_id,
                $l_ipv6_scope,
                $l_ipv6_assignment,
                $_POST['C__CATG__IP__ZONE'],
                $_POST['C__CATG__IP__DOMAIN'],
                $l_fqdn_pairs
            );

            $p_cat_layer = null;
        } else {
            if ($l_id) {
                $this->save(
                    $l_id,
                    $_POST['C__CATP__IP__HOSTNAME'],
                    $_POST['C__CATP__IP__ASSIGN'],
                    $l_ip_data[C__IP__ADDRESS],
                    $_POST['C__CATP__IP__PRIMARY'],
                    $_POST['C__CATG__IP__GW__CHECK'],
                    isys_format_json::decode($_POST['C__CATG__IP__ASSIGNED_DNS_SERVER__HIDDEN']),
                    $_POST['C__CATP__IP__SEARCH_DOMAIN'],
                    $_POST['C__CATP__IP__ACTIVE'],
                    $_POST['C__NET__TYPE'],
                    $_POST['C__CATG__IP__NET__HIDDEN'],
                    $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()],
                    C__RECORD_STATUS__NORMAL,
                    $l_port_id,
                    $l_log_port_id,
                    $l_ipv6_assignment,
                    $l_ipv6_scope,
                    $_POST['C__CATG__IP__ZONE'],
                    $_POST['C__CATG__IP__DOMAIN'],
                    $l_fqdn_pairs
                );
            }
        }

        return ($l_return > 0) ? $l_return : null;
    }

    /**
     * Returns an ip entry by the ip address (Can exclude one ip entry and one object id).
     *
     * @param   string  $p_ip_address
     * @param   integer $p_exclude_catg_list_id
     * @param   integer $p_exclude_object_id
     * @param   integer $p_assigned_net
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_ip_by_address($p_ip_address, $p_exclude_catg_list_id = null, $p_exclude_object_id = null, $p_assigned_net = null, $p_status = C__RECORD_STATUS__NORMAL)
    {
        if (($l_ipv6 = Ip::validate_ipv6($p_ip_address))) {
            $p_ip_address = $l_ipv6;
            $l_table_alias = 'ipv6';
        } else {
            $l_table_alias = 'ipv4';
        }

        $l_sql = " AND ((" . $l_table_alias . ".isys_cats_net_ip_addresses_list__title = " . $this->convert_sql_text($p_ip_address) . ") " .
            "AND (isys_catg_ip_list__status = " . C__RECORD_STATUS__NORMAL . "))";

        if (!empty($p_exclude_object_id)) {
            $l_sql .= " AND (isys_obj__id != " . $this->convert_sql_id($p_exclude_object_id) . ")";
        }

        if (!empty($p_exclude_catg_list_id)) {
            $l_sql .= " AND (isys_catg_ip_list__id != " . $this->convert_sql_id($p_exclude_catg_list_id) . ")";
        }

        if (!empty($p_assigned_net)) {
            $l_sql .= " AND (" . $l_table_alias . ".isys_cats_net_ip_addresses_list__isys_obj__id = " . $this->convert_sql_id($p_assigned_net) . ")";
        }

        return $this->get_data(null, null, $l_sql, null, $p_status);
    }

    /**
     * Is hostname unique or not.
     *
     * @param   integer $p_object_id Object-ID
     * @param   string  $p_hostname  Hostname
     * @param   integer $p_net_id    ID of Layer-3-Net
     *
     * @author  Selcuk Kekec <skekec@i-doit.com>
     * @return  boolean
     */
    public function is_unique_hostname($p_object_id, $p_hostname, $p_net_id)
    {
        // Hostname and net are setted
        if ($p_hostname != '' && $p_net_id > 0) {
            $l_host = $this->get_data(
                null,
                null,
                " AND (isys_catg_ip_list__hostname = " . $this->convert_sql_text($p_hostname) . " " . "AND isys_catg_ip_list__isys_obj__id != " .
                $this->convert_sql_id($p_object_id) . " " . "AND (ipv4.isys_cats_net_ip_addresses_list__isys_obj__id = " . $this->convert_sql_id($p_net_id) .
                " OR ipv6.isys_cats_net_ip_addresses_list__isys_obj__id = " . $this->convert_sql_id($p_net_id) . ")) ",
                null,
                C__RECORD_STATUS__NORMAL
            );

            return ($l_host->num_rows() == 0);
        }

        return true;
    }

    /**
     * Is ip unique or not? This method does not consider the i-doit global nets Globalv4 and Globalv6.
     *
     * @param   integer $p_object_id  Object-ID
     * @param   string  $p_ip_address IP address
     * @param   integer $p_net_id     ID of Layer-3-Net
     *
     * @author  Selcuk Kekec <skekec@i-doit.com>
     * @return  boolean
     */
    public function is_unique_ip($p_object_id, $p_ip_address, $p_net_id)
    {
        return !($this->ip_already_in_use($p_net_id, $p_ip_address, $p_object_id) && $p_net_id != defined_or_default('C__OBJ__NET_GLOBAL_IPV4') && $p_net_id != defined_or_default('C__OBJ__NET_GLOBAL_IPV6'));
    }

    /**
     * Check whether an ip-address is in use or not.
     *
     * @param   integer $p_netID         IP of the net
     * @param   string  $p_ipAddress     IP-Address
     * @param   integer $p_catToIgnore   CatLevel-ID to ignore
     * @param   boolean $p_ignoreGlobals Switch to ignore the global NET
     *
     * @throws  Exception
     * @throws  isys_exception_database
     * @return  boolean
     * @author  Selcuk Kekec <skekec@synetics.de>
     */
    public function in_use($p_netID, $p_ipAddress, $p_catToIgnore, $p_ignoreGlobals = true)
    {
        // Ignore empty ip-addresses.
        if ($p_ipAddress == "") {
            return false;
        }

        $l_return = false;

        if ($p_ignoreGlobals && in_array($p_netID, [
                defined_or_default('C__OBJ__NET_GLOBAL_IPV4'),
                defined_or_default('C__OBJ__NET_GLOBAL_IPV6')
            ])) {
            return false;
        }

        $l_netInfo = $this->retrieve('SELECT isys_cats_net_list__isys_net_type__id FROM isys_cats_net_list WHERE isys_cats_net_list__isys_obj__id = ' .
            $this->convert_sql_id($p_netID))
            ->get_row_value('isys_cats_net_list__isys_net_type__id');

        // Status-Handling.
        $l_condition = ' AND isys_catg_ip_list__status = ' . C__RECORD_STATUS__NORMAL . ' AND isys_obj__status = ' . C__RECORD_STATUS__NORMAL . ' ';

        if ($p_catToIgnore) {
            $l_condition = ' AND isys_catg_ip_list__id != ' . $this->convert_sql_id($p_catToIgnore);
        }

        if ($l_netInfo == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
            $l_condition .= ' AND (ipv4.isys_cats_net_ip_addresses_list__title = ' . $this->convert_sql_text($p_ipAddress) .
                ' OR ipv4.isys_cats_net_ip_addresses_list__ip_address_long = ' . $this->convert_sql_text(Ip::ip2long($p_ipAddress)) .
                ') AND ipv4.isys_cats_net_ip_addresses_list__isys_obj__id = ' . $this->convert_sql_id($p_netID);
        } elseif ($l_netInfo == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
            $l_condition .= ' AND ipv6.isys_cats_net_ip_addresses_list__title = ' . $this->convert_sql_text($p_ipAddress) .
                ' AND ipv6.isys_cats_net_ip_addresses_list__isys_obj__id = ' . $this->convert_sql_id($p_netID);
        } else {
            // This branch will be executed in cases the provided net object has no entries in net category
            return false;
        }

        $l_res = $this->get_data(null, null, $l_condition, null, C__RECORD_STATUS__NORMAL);

        if (is_countable($l_res) && count($l_res) > 0) {
            $l_row = $l_res->get_row();

            $l_return = $l_row['isys_catg_ip_list__id'];
        }

        return $l_return;
    }

    /**
     * Get primary ip only.
     *
     * @param   integer $p_object_id
     *
     * @return  isys_component_dao_result
     */
    public function get_primary_ip($p_object_id)
    {
        return $this->get_ips_by_obj_id($p_object_id, true);
    }

    /**
     * Get primary ip as string
     *
     * @param   integer $p_object_id
     *
     * @return  isys_component_dao_result
     */
    public function get_primary_ip_as_string($p_object_id)
    {
        return $this->get_ips_by_obj_id($p_object_id, true)
            ->get_row_value('isys_cats_net_ip_addresses_list__title');
    }

    /**
     * Delete all IPs in a port.
     *
     * @param   integer $p_port_id
     * @param   integer $p_status
     *
     * @return  boolean
     */
    public function delete_ips($p_port_id, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_ips = $this->get_ips_by_port_id($p_port_id);

        while ($l_row = $l_ips->get_row()) {
            $l_sql = 'DELETE FROM isys_catg_ip_list WHERE isys_catg_ip_list__id = ' . $this->convert_sql_int($l_row["isys_catg_ip_list__id"]) . ' ';

            if ($p_status) {
                $l_sql .= ' AND isys_catg_ip_list__status = ' . $this->convert_sql_int($p_status);
            }

            return ($this->update($l_sql . ";") && $this->apply_update());
        }

        return true;
    }

    /**
     * Method resetting the IP addresses of given objects.
     *
     * @param   mixed $p_obj_id May be a single ID or an array of IDs.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function empty_ip_addresses_from_obj($p_obj_id)
    {
        $l_sql = 'SELECT isys_catg_ip_list__isys_cats_net_ip_addresses_list__id FROM isys_catg_ip_list WHERE TRUE ';

        if (is_array($p_obj_id) && count($p_obj_id) > 0) {
            $l_sql .= 'AND isys_catg_ip_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ';';
        } elseif ($p_obj_id > 0) {
            $l_sql .= 'AND isys_catg_ip_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';';
        } else {
            return true;
        }

        $l_res = $this->retrieve($l_sql);

        if ($l_res->num_rows() > 0) {
            $l_ip_address_id = [];

            while ($l_row = $l_res->get_row()) {
                $l_ip_address_id[] = $l_row['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'];
            }

            if (count($l_ip_address_id)) {
                $l_sql = 'UPDATE isys_cats_net_ip_addresses_list
					SET isys_cats_net_ip_addresses_list__title = "",
					isys_cats_net_ip_addresses_list__ip_address_long = 0
					WHERE isys_cats_net_ip_addresses_list__id ' . $this->prepare_in_condition($l_ip_address_id);

                return ($this->update($l_sql) && $this->apply_update());
            }
        }

        // Nothing to empty!
        return true;
    }

    /**
     * Receive IPs by Port ID.
     *
     * @param   integer $p_port_id
     * @param   boolean $p_primary_only
     *
     * @return  isys_component_dao_result
     */
    public function get_ips_by_port_id($p_port_id, $p_primary_only = false)
    {
        return $this->get_ips_by_obj_id(null, $p_primary_only, $p_port_id);
    }

    /**
     * Returns all ips connected to table $p_table_name.
     *
     * @param string $p_table_name
     * @param int    $p_id
     * @param int    $p_obj_id
     * @param bool   $p_primary_only
     * @param bool   $p_short_fields
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_ips_by_connection_table($p_table_name = "isys_catg_port_list", $p_id = null, $p_obj_id = null, $p_primary_only = false, $p_short_fields = false)
    {
        $l_query = 'SELECT * FROM isys_catg_ip_list
            INNER JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id
            INNER JOIN isys_catg_ip_list_2_' . $p_table_name . ' ON ';

        if (!$p_short_fields) {
            $l_query .= 'isys_catg_ip_list_2_' . $p_table_name . '__isys_catg_ip_list__id = isys_catg_ip_list__id ';
        } else {
            $l_query .= 'isys_catg_ip_list_2_' . $p_table_name . '.isys_catg_ip_list__id = isys_catg_ip_list.isys_catg_ip_list__id ';
        }

        $l_query .= 'WHERE TRUE';

        if (!empty($p_obj_id)) {
            $l_query .= ' AND isys_catg_ip_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        }

        if ($p_primary_only) {
            $l_query .= ' AND isys_catg_ip_list__primary = 1';
        }

        if (!empty($p_id)) {
            if (!$p_short_fields) {
                $l_query .= ' AND isys_catg_ip_list_2_' . $p_table_name . '__' . $p_table_name . '__id = ' . $this->convert_sql_id($p_id);
            } else {
                $l_query .= ' AND isys_catg_ip_list_2_' . $p_table_name . '.' . $p_table_name . '__id = ' . $this->convert_sql_id($p_id);
            }
        }

        return $this->retrieve($l_query . ';');
    }

    /**
     * Gets all IPs over all Ports of an object given by its id.
     *
     * @param   integer $p_objID
     * @param   boolean $p_primary_only
     * @param   boolean $p_unused_parameter
     * @param   integer $p_port_id
     * @param   string  $p_condition
     *
     * @return  isys_component_dao_result  The result set
     * @throws  isys_exception_database
     * @author  Dennis Bluemer <dbluemer@synetcis.de>
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function get_ips_by_obj_id($p_objID, $p_primary_only = false, $p_unused_parameter = false, $p_port_id = null, $p_condition = '')
    {
        $sql = 'SELECT * 
            FROM isys_catg_ip_list
			INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
			INNER JOIN isys_cats_net_list ON isys_cats_net_list__isys_obj__id = isys_cats_net_ip_addresses_list__isys_obj__id
			WHERE TRUE ' . $p_condition;

        if ($p_objID) {
            $sql .= " AND isys_catg_ip_list__isys_obj__id = " . $this->convert_sql_id($p_objID);
        }

        if ($p_primary_only) {
            $sql .= " AND isys_catg_ip_list__primary = 1";
        }

        if (!empty($p_port_id)) {
            $sql .= ' AND isys_catg_ip_list__isys_catg_port_list__id = ' . $this->convert_sql_id($p_port_id);
        }

        if ($p_primary_only) {
            $sql .= " LIMIT 1";
        }

        return $this->retrieve($sql . ';');
    }

    /**
     * Gets all IPs over all Ports of an object given by its id
     *
     * @param   integer $p_obj_id
     * @param   integer $p_router_list_id
     * @param   integer $p_record_status
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@synetcis.de>
     */
    public function get_ips_for_router_list_by_obj_id($p_obj_id = null, $p_router_list_id = null, $p_record_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = 'SELECT router.* 
            FROM isys_catg_ip_list AS ip
			LEFT JOIN isys_catg_ip_list_2_isys_cats_router_list AS router ON ip.isys_catg_ip_list__id = router.isys_catg_ip_list__id
			WHERE TRUE ';

        if ($p_obj_id !== null) {
            $l_sql .= 'AND ip.isys_catg_ip_list__isys_obj__id = ' . $this->convert_sql_id(intval($p_obj_id)) . ' ';
        }

        if ($p_router_list_id !== null) {
            $l_sql .= 'AND router.isys_cats_router_list__id = ' . $this->convert_sql_id(intval($p_router_list_id)) . ' ';
        }

        if ($p_record_status > 0) {
            $l_sql .= 'AND ip.isys_catg_ip_list__status = ' . $this->convert_sql_id(intval($p_record_status));
        }

        return $this->retrieve($l_sql . ';');
    }

    public function getObjIDsByIP($p_ip)
    {
        $l_result = $this->get_ip_by_address($p_ip);
        $l_objIDs = [];
        while ($l_row = $l_result->get_row()) {
            $l_objIDs[] = $l_row;
        }

        return $l_objIDs;
    }

    /**
     * @param string $p_hostname
     *
     * @return array
     * @throws isys_exception_database
     */
    public function getObjIDsByHostName($p_hostname)
    {
        $l_return = [];
        $l_res = $this->retrieve("SELECT DISTINCT isys_catg_ip_list__isys_obj__id AS 'id' FROM isys_catg_ip_list WHERE isys_catg_ip_list__hostname = " .
            $this->convert_sql_text($p_hostname) . ";");

        while ($l_row = $l_res->get_row()) {
            $l_return[] = $l_row;
        }

        return $l_return;
    }

    /**
     * Checks if an ip address belongs to the net object. $p_ip_address should be an ipv4 string.
     *
     * @param   string  $p_ip_address
     * @param   integer $p_net_object
     *
     * @return  boolean
     */
    public function is_ip_inside_net($p_ip_address, $p_net_object)
    {
        $l_net_dao = isys_cmdb_dao_category_s_net::instance($this->m_db);
        $l_data = $l_net_dao->get_data(null, $p_net_object)
            ->__to_array();

        $l_range_from = Ip::ip2long($l_data["isys_cats_net_list__dhcp_range_from"]);
        $l_range_to = Ip::ip2long($l_data["isys_cats_net_list__dhcp_range_to"]);
        $l_ip = Ip::ip2long($p_ip_address);

        return ($l_range_from <= $l_ip && $l_range_to >= $l_ip);
    }

    /**
     * Returns an entry of the isys_catg_ip_list, by a given ID.
     *
     * @param   integer $p_id
     *
     * @return  array
     */
    public function get_ip_by_id($p_id)
    {
        $l_query = 'SELECT * FROM isys_catg_ip_list
			INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
			WHERE isys_catg_ip_list__id = ' . $this->convert_sql_id($p_id) . ';';

        return $this->retrieve($l_query)
            ->get_row();
    }

    /**
     * Method for retrieving certain rows of "isys_catg_ip" and "isys_cats_net_ip_addresses".
     *
     * @param   array $p_ids
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_ip_addresses_by_ids(array $p_ids)
    {
        $l_query = 'SELECT * FROM isys_catg_ip_list
			INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
			WHERE isys_catg_ip_list__id ' . $this->prepare_in_condition($p_ids) . ';';

        return $this->retrieve($l_query);
    }

    /**
     * A method, which bundles the handle_ajax_request and handle_preselection.
     *
     * @param  integer $p_context
     * @param  array   $p_parameters
     *
     * @return string|array
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function object_browser($p_context, array $p_parameters)
    {
        $language = isys_application::instance()->container->get('language');

        switch ($p_context) {
            case isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST:
                // Handle Ajax-Request.
                $l_return = [];

                $l_obj = isys_cmdb_dao_category_g_ip::instance($this->m_db);
                $l_objects = $l_obj->get_ips_by_obj_id($_GET[C__CMDB__GET__OBJECT]);

                if ($l_objects->num_rows() > 0) {
                    while ($l_row = $l_objects->get_row()) {
                        $l_return[] = [
                            '__checkbox__'                         => $l_row["isys_catg_ip_list__id"],
                            $language->get('LC__CATG__IP_ADDRESS') => $l_row["isys_cats_net_ip_addresses_list__title"]
                        ];
                    }
                }

                return json_encode($l_return);

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PREPARATION:
                // Preselection
                $l_return = [
                    'category' => [],
                    'first'    => [],
                    'second'   => []
                ];

                $p_preselection = $p_parameters['preselection'];

                if (is_string($p_preselection) && isys_format_json::is_json_array($p_preselection)) {
                    $p_preselection = isys_format_json::decode($p_preselection);
                }

                if (!empty($p_preselection) && is_array($p_preselection)) {
                    $l_sql = "SELECT isys_obj__id, isys_obj__isys_obj_type__id, isys_catg_ip_list__id, isys_cats_net_ip_addresses_list__title, isys_obj_type__title
                        FROM isys_catg_ip_list
                        INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
                        LEFT JOIN isys_obj ON isys_obj__id = isys_catg_ip_list__isys_obj__id
                        LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
                        WHERE isys_catg_ip_list__id " . $this->prepare_in_condition($p_preselection);

                    $l_res = $this->retrieve($l_sql);

                    while ($l_row = $l_res->get_row()) {
                        $l_return['category'][] = $l_row['isys_obj__isys_obj_type__id'];

                        // @see  ID-6220  Also return a 'first' selection.
                        $l_preselection['first'][] = $l_row['isys_obj__id'];

                        $l_return['second'][] = [
                            $l_row['isys_catg_ip_list__id'],
                            $l_row['isys_cats_net_ip_addresses_list__title'],
                            $language->get($l_row['isys_obj_type__title']),
                        ];
                    }
                }

                return $l_return;

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PRESELECTION:
                // @see  ID-5688  New callback case.
                $preselection = [];

                if (is_array($p_parameters['dataIds']) && count($p_parameters['dataIds'])) {
                    foreach ($p_parameters['dataIds'] as $dataId) {
                        $categoryRow = $this->get_data($dataId)->get_row();

                        $preselection[] = [
                            $categoryRow['isys_catg_ip_list__id'],
                            $categoryRow['isys_cats_net_ip_addresses_list__title'],
                            $categoryRow['isys_obj__title'],
                            $language->get($categoryRow['isys_obj_type__title'])
                        ];
                    }
                }

                return [
                    'header' => [
                        '__checkbox__',
                        $language->get('LC__CATG__IP_ADDRESS'),
                        $language->get('LC__UNIVERSAL__OBJECT_TITLE'),
                        $language->get('LC__UNIVERSAL__OBJECT_TYPE')
                    ],
                    'data'   => $preselection
                ];
        }
    }

    /**
     * Formats the title of the object for the object browser.
     *
     * @param   integer $p_ip_id
     * @param   boolean $p_plain
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function format_selection($p_ip_id, $p_plain = false)
    {
        // We need a DAO for the object name.
        $l_dao_ip = isys_cmdb_dao_category_g_ip::instance($this->m_db);
        $l_quick_info = new isys_ajax_handler_quick_info();

        $l_row = $l_dao_ip->get_ip_by_id($p_ip_id);

        $p_object_type = $l_dao_ip->get_objTypeID($l_row["isys_catg_ip_list__isys_obj__id"]);

        if (!empty($p_ip_id)) {
            $l_editmode = ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT || isys_glob_get_param("editMode") == C__EDITMODE__ON || isset($this->m_params["edit"])) &&
                !isset($this->m_params["plain"]);

            $l_title = isys_application::instance()->container->get('language')
                    ->get($l_dao_ip->get_objtype_name_by_id_as_string($p_object_type)) . " >> " .
                $l_dao_ip->get_obj_name_by_id_as_string($l_row["isys_catg_ip_list__isys_obj__id"]) . " >> " . $l_row["isys_cats_net_ip_addresses_list__title"];

            if (!$l_editmode && !$p_plain) {
                return $l_quick_info->get_quick_info($l_row["isys_catg_ip_list__isys_obj__id"], $l_title, C__LINK__OBJECT);
            } else {
                return $l_title;
            }
        }

        return isys_application::instance()->container->get('language')
            ->get("LC__CMDB__BROWSER_OBJECT__NONE_SELECTED");
    }

    /**
     * Gets assigned dns domains assigned to the host address.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_id
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     * @return  isys_component_dao_result
     */
    public function get_assigned_dns_domain($p_obj_id = null, $p_id = null)
    {
        if (empty($p_obj_id) && empty($p_id)) {
            return false;
        }

        $l_sql = 'SELECT dnstable.isys_net_dns_domain__id, dnstable.isys_net_dns_domain__title
			FROM isys_catg_ip_list_2_isys_net_dns_domain AS main
			INNER JOIN isys_net_dns_domain dnstable ON main.isys_net_dns_domain__id = dnstable.isys_net_dns_domain__id';

        if ($p_obj_id > 0) {
            $l_condition = ' WHERE main.isys_catg_ip_list__id = (SELECT isys_catg_ip_list__id FROM isys_catg_ip_list WHERE isys_catg_ip_list__isys_obj__id = ' .
                $this->convert_sql_id($p_obj_id) . ' ORDER BY isys_catg_ip_list__primary DESC LIMIT 1)';
        }

        if ($p_id > 0) {
            $l_condition = ' WHERE main.isys_catg_ip_list__id = ' . $this->convert_sql_id($p_id);
        }

        return $this->retrieve($l_sql . $l_condition . ';');
    }

    /**
     * Gets assigned dns server for the host address.
     *
     * @param   integer $p_cat_id
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_assigned_dns_server($p_cat_id)
    {
        $l_arr = [];

        $l_res = $this->retrieve('SELECT isys_catg_ip_list__id__dns FROM isys_catg_ip_list_2_isys_catg_ip_list WHERE isys_catg_ip_list__id = ' .
            $this->convert_sql_id($p_cat_id) . ';');

        if (is_countable($l_res) && count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                $l_arr[] = $l_row['isys_catg_ip_list__id__dns'];
            }
        }

        return $l_arr;
    }

    /**
     * Deletes dns server connection for the specified category entry id.
     *
     * @param   integer $p_id
     *
     * @throws  Exception
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function clear_dns_server_attachments($p_id)
    {
        if (empty($p_id)) {
            return true;
        }

        try {
            $this->update('DELETE FROM isys_catg_ip_list_2_isys_catg_ip_list WHERE isys_catg_ip_list__id = ' . $this->convert_sql_id($p_id) . ';');
        } catch (Exception $e) {
            throw new Exception('Error while clearing attachments.');
        }

        return $this->apply_update();
    }

    /**
     * Deletes dns domain connection for the specified category entry id.
     *
     * @param   integer $p_id
     *
     * @throws  Exception
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function clear_dns_domain_attachments($p_id)
    {
        if (empty($p_id)) {
            return true;
        }

        try {
            $this->update('DELETE FROM isys_catg_ip_list_2_isys_net_dns_domain WHERE isys_catg_ip_list__id = ' . $this->convert_sql_id($p_id) . ';');
        } catch (Exception $e) {
            throw new Exception('Error while clearing attachments.');
        }

        return $this->apply_update();
    }

    /**
     * Creates new dns server connection with the specified object id.
     *
     * @param   integer $p_cat_id
     * @param   integer $p_cat_dns_server_id
     *
     * @throws  isys_exception_dao
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     * @return  boolean
     */
    public function attach_dns_server($p_cat_id, $p_cat_dns_server_id)
    {
        if (empty($p_cat_id) || empty($p_cat_dns_server_id)) {
            return true;
        }

        $l_insert = 'INSERT INTO isys_catg_ip_list_2_isys_catg_ip_list (isys_catg_ip_list__id, isys_catg_ip_list__id__dns) VALUES ' . '(' . $this->convert_sql_id($p_cat_id) .
            ', ' . $this->convert_sql_id($p_cat_dns_server_id) . ')';

        return ($this->update($l_insert) && $this->apply_update());
    }

    /**
     * Creates new dns domain connection with the specified domain id.
     *
     * @param   integer $p_cat_id
     * @param   integer $p_dns_domain_id
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     * @return  boolean
     */
    public function attach_dns_domain($p_cat_id, $p_dns_domain_id)
    {
        if (empty($p_cat_id) || empty($p_dns_domain_id)) {
            return true;
        }

        if ($this->is_dns_domain_attached($p_cat_id, $p_dns_domain_id)) {
            return true;
        }

        $l_insert = 'INSERT INTO isys_catg_ip_list_2_isys_net_dns_domain SET
			isys_catg_ip_list__id = ' . $this->convert_sql_id($p_cat_id) . ',
			isys_net_dns_domain__id = ' . $this->convert_sql_id($p_dns_domain_id) . ';';

        return ($this->update($l_insert) && $this->apply_update());
    }

    /**
     * Method for attaching a DNS domain (also checking if it has already been added).
     *
     * @param   integer $p_cat_id
     * @param   string  $p_dns_domain
     *
     * @return  boolean
     * @throws  Exception
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function attach_dns_domain_by_string($p_cat_id, $p_dns_domain)
    {
        $p_dns_domain = trim($p_dns_domain);

        if (!($p_cat_id > 0) || empty($p_dns_domain)) {
            return false;
        }

        $l_dns = $this->retrieve('SELECT * FROM isys_net_dns_domain WHERE isys_net_dns_domain__title LIKE ' . $this->convert_sql_text($p_dns_domain) . ' LIMIT 1;');

        if (is_countable($l_dns) && count($l_dns)) {
            $l_dns_id = $l_dns->get_row_value('isys_net_dns_domain__id');
        } else {
            $l_insert = "INSERT INTO isys_net_dns_domain SET
				isys_net_dns_domain__title = " . $this->convert_sql_text($p_dns_domain) . ",
				isys_net_dns_domain__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ";";

            if (!($this->update($l_insert) && $this->apply_update())) {
                return false;
            }

            $l_dns_id = $this->get_last_insert_id();
        }

        $this->attach_dns_domain($p_cat_id, $l_dns_id);
    }

    /**
     * This method checks, if the given DNS domain id has already been assigned to the given category entry.
     *
     * @param   integer $p_cat_id
     * @param   integer $p_dns_domain_id
     *
     * @return  boolean
     * @throws  Exception
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function is_dns_domain_attached($p_cat_id, $p_dns_domain_id)
    {
        return !!count($this->retrieve('SELECT isys_catg_ip_list_2_isys_net_dns_domain__id
			FROM isys_catg_ip_list_2_isys_net_dns_domain
			WHERE isys_catg_ip_list__id = ' . $this->convert_sql_id($p_cat_id) . '
			AND isys_net_dns_domain__id = ' . $this->convert_sql_id($p_dns_domain_id) . '
			LIMIT 1;'));
    }

    /**
     * Checks if ip is the gateway for the net
     *
     * @param $p_cat_id
     * @param $p_net_obj_id
     *
     * @return bool
     * @throws isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function is_gateway($p_cat_id, $p_net_obj_id)
    {
        return !!count($this->retrieve('SELECT isys_cats_net_list__id FROM isys_cats_net_list
            WHERE isys_cats_net_list__isys_obj__id = ' . $this->convert_sql_id($p_net_obj_id) . '
            AND isys_cats_net_list__isys_catg_ip_list__id = ' . $this->convert_sql_id($p_cat_id)));
    }

    /**
     * Sets the default gateway of the net.
     *
     * @param  integer $p_net_id
     * @param  integer $p_net_obj_id
     * @param  integer $p_cat_id
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     * @return  boolean
     */
    public function attach_gateway($p_net_id = null, $p_net_obj_id = null, $p_cat_id)
    {
        $l_update = 'UPDATE isys_cats_net_list SET isys_cats_net_list__isys_catg_ip_list__id = ' . $this->convert_sql_id($p_cat_id) . ' ';

        if ($p_net_id > 0) {
            $l_update .= 'WHERE isys_cats_net_list__id = ' . $this->convert_sql_id($p_net_id);
        } elseif ($p_net_obj_id) {
            $l_update .= 'WHERE isys_cats_net_list__isys_obj__id = ' . $this->convert_sql_id($p_net_obj_id);
        }

        return ($this->update($l_update) && $this->apply_update());
    }

    /**
     * Gets active hostaddress.
     *
     * @param   integer $p_obj_id
     *
     * @return  isys_component_dao_result
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_active_ip_by_object_id($p_obj_id = null)
    {
        $l_sql = 'SELECT isys_catg_ip_list__id FROM isys_catg_ip_list WHERE isys_catg_ip_list__active = 1 AND isys_catg_ip_list__status = ' .
            $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' ';

        if (!empty($p_obj_id)) {
            $l_sql .= ' AND isys_catg_ip_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Gets primary hostaddress.
     *
     * @param   integer $p_obj_id
     *
     * @return  isys_component_dao_result
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_primary_ip_by_object_id($p_obj_id = null)
    {
        $l_sql = 'SELECT isys_catg_ip_list__id FROM isys_catg_ip_list WHERE isys_catg_ip_list__primary = 1 AND isys_catg_ip_list__status = ' .
            $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' ';

        if (!empty($p_obj_id)) {
            $l_sql .= ' AND isys_catg_ip_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Calculates the next free ip.
     *
     * @param   integer $netObjectId     object id of the net
     * @param   integer $ipAssignType    IP assignment type id (for DHCP, DHCP reserved, ...)
     * @param   integer $netZoneObjectId Net zone object ID
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     * @return  string
     */
    public function get_free_ip($netObjectId, $ipAssignType = null, $netZoneObjectId = null)
    {
        // Unnumbered addresses don't need any further checks.
        if ($ipAssignType == defined_or_default('C__CATP__IP__ASSIGN__UNNUMBERED')) {
            return '';
        }

        $netZoneDao = isys_cmdb_dao_category_s_net_zone::instance($this->m_db);
        $netData = isys_cmdb_dao_category_s_net::instance($this->m_db)
            ->get_data(null, $netObjectId)
            ->get_row();
        $netAreaFrom = $netData['isys_cats_net_list__address_range_from_long'];
        $netAreaTo = $netData['isys_cats_net_list__address_range_to_long'];
        $assignmentType = ($ipAssignType == defined_or_default('C__CATP__IP__ASSIGN__DHCP')) ? 'C__NET__DHCP_RESERVED' : ($ipAssignType ==
        defined_or_default('C__CATP__IP__ASSIGN__DHCP_RESERVED') ? 'C__NET__DHCP_DYNAMIC' : null);

        // Cache all used ips in an array
        $usedIps = [];
        $netUsedIpsResult = isys_cmdb_dao_category_s_net_ip_addresses::instance($this->m_db)
            ->get_data(null, $netObjectId);
        if (is_countable($netUsedIpsResult) && count($netUsedIpsResult) > 0) {
            while ($ipData = $netUsedIpsResult->get_row()) {
                if (empty($ipData['isys_cats_net_ip_addresses_list__title']) || $ipData['isys_catg_ip_list__status'] != C__RECORD_STATUS__NORMAL ||
                    $this->get_object_status_by_id($ipData['isys_catg_ip_list__isys_obj__id']) != C__RECORD_STATUS__NORMAL) {
                    continue;
                }

                $usedIps[$ipData['isys_cats_net_ip_addresses_list__title']] = true;
            }
        }

        // Cache all dhcp ranges from the net
        $netDhcpRanges = [];
        $netDhcpRangesResult = isys_cmdb_dao_category_s_net_dhcp::instance($this->m_db)
            ->get_data(null, $netObjectId, '', null, C__RECORD_STATUS__NORMAL);
        if (is_countable($netDhcpRangesResult) && count($netDhcpRangesResult) > 0) {
            while ($netDhcpRangeData = $netDhcpRangesResult->get_row()) {
                $netDhcpRanges[] = [
                    'from' => $netDhcpRangeData['isys_cats_net_dhcp_list__range_from_long'],
                    'to'   => $netDhcpRangeData['isys_cats_net_dhcp_list__range_to_long'],
                ];
            }
        }

        // Net zones
        $netZoneRanges = [];
        if ($netZoneObjectId) {
            $netZoneData = $netZoneDao->get_data(
                null,
                $netObjectId,
                'AND isys_cats_net_zone_list__isys_obj__id__zone = ' . $this->convert_sql_id($netZoneObjectId),
                null,
                C__RECORD_STATUS__NORMAL
            )
                ->get_row();

            $netAreaFrom = $netZoneData['isys_cats_net_zone_list__range_from_long'];
            $netAreaTo = $netZoneData['isys_cats_net_zone_list__range_to_long'];
        } elseif ($netZoneObjectId === 0) {
            // Special treatment, when we explicitly select "none".
            $netZoneResult = $netZoneDao->get_data(null, $netObjectId, '', null, C__RECORD_STATUS__NORMAL);

            while ($netZoneData = $netZoneResult->get_row()) {
                $netZoneRanges[] = [
                    'from' => $netZoneData['isys_cats_net_zone_list__range_from_long'],
                    'to'   => $netZoneData['isys_cats_net_zone_list__range_to_long']
                ];
            }
        }

        // Special case for global v4 net with dhcp ranges and dhcp/dhcp reserved assignment type
        if ($netObjectId === defined_or_default('C__OBJ__NET_GLOBAL_IPV4') && $assignmentType && count($netDhcpRanges)) {
            // first iterate through all net dhcp ranges and maybe a free ip is available
            foreach ($netDhcpRanges as $dhcpRange) {
                $currentIpLong = $dhcpRange['from'] + 1;
                if ($currentIpLong < $dhcpRange['to']) {
                    // When the user explicitly selects NO zone, we skip all IPs that lie within our ranges.
                    if (!$netZoneObjectId && count($netZoneRanges)) {
                        foreach ($netZoneRanges as $zoneRange) {
                            if ($zoneRange['from'] <= $currentIpLong && $zoneRange['to'] >= $currentIpLong) {
                                // Skip the foreach loop, not this (inner) foreach.
                                continue 2;
                            }
                        }
                    }

                    $currentIp = Ip::long2ip($currentIpLong);

                    if (isset($usedIps[$currentIp])) {
                        continue;
                    }

                    return $currentIp;
                }
            }

            // if no ip has been found in the dhcp range then Iterate through every IP in the net.
            for ($i = $netAreaFrom;$i <= $netAreaTo;$i++) {
                // When the user explicitly selects NO zone, we skip all IPs that lie within our ranges.
                if (!$netZoneObjectId && count($netZoneRanges)) {
                    foreach ($netZoneRanges as $zoneRange) {
                        if ($zoneRange['from'] <= $i && $zoneRange['to'] >= $i) {
                            // Skip the FOR loop, not this (inner) foreach.
                            continue 2;
                        }
                    }
                }

                $currentIp = Ip::long2ip($i);

                // Skip, if the current IP is already in use.
                if (isset($usedIps[$currentIp])) {
                    continue;
                }

                isys_notify::info(isys_application::instance()->container->get('language')
                    ->get_in_text('LC__CMDB__CATG__IP__IP_IS_NOT_INSIDE_RANGE_OF_DHCP ' . $currentIp), ['life' => 5]);

                return $currentIp;
            }
        } else {
            // Iterate through every IP in the net.
            for ($i = $netAreaFrom;$i <= $netAreaTo;$i++) {
                $currentIp = Ip::long2ip($i);

                // Skip, if the current IP is already in use.
                if (isset($usedIps[$currentIp])) {
                    continue;
                }

                // When the user explicitly selects NO zone, we skip all IPs that lie within our ranges.
                if (!$netZoneObjectId && count($netZoneRanges)) {
                    foreach ($netZoneRanges as $zoneRange) {
                        if ($zoneRange['from'] <= $i && $zoneRange['to'] >= $i) {
                            // Skip the FOR loop, not this (inner) foreach.
                            continue 2;
                        }
                    }
                }

                if ($assignmentType && count($netDhcpRanges)) {
                    // iterate through the dhcp ranges
                    foreach ($netDhcpRanges as $dhcpRange) {
                        if ($dhcpRange['from'] <= $i && $dhcpRange['to'] >= $i) {
                            return $currentIp;
                        }
                    }
                } else {
                    // Skip ip if its in a dhcp range
                    if (count($netDhcpRanges)) {
                        foreach ($netDhcpRanges as $dhcpRange) {
                            if ($dhcpRange['from'] <= $i && $dhcpRange['to'] >= $i) {
                                continue 2;
                            }
                        }
                    } elseif ($assignmentType) {
                        // Notify that there are no dhcp ranges set in the net
                        isys_notify::error(isys_application::instance()->container->get('language')
                            ->get('LC__CATP__IP__ASSIGN__NO_FREE_IP__NOTIFY'), ['life' => 5]);

                        return false;
                    }

                    return $currentIp;
                }
            }
        }

        // Message that no free ip has been found
        isys_notify::error(isys_application::instance()->container->get('language')
            ->get('LC__CMDB__CATG__IP__NO_IP_FOUND'), ['life' => 5]);

        return false;
    }

    /**
     * With this method you can change the IP-assignment ID by giving an IP range as parameter.
     *
     * @param   integer $p_net_id        The Layer3-Net object ID
     * @param   integer $p_ip_assignment The new IP-assignment ID
     * @param   string  $p_from
     * @param   string  $p_to
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function update_ip_assignment_by_ip_range($p_net_id, $p_ip_assignment, $p_from, $p_to = null)
    {
        $l_net_dao = isys_cmdb_dao_category_s_net::instance($this->m_db);
        $l_net_row = $l_net_dao->get_data(null, $p_net_id)
            ->get_row();

        $l_id = [];

        if (empty($p_from)) {
            return false;
        }

        // If we only get one IP, we set the other one so we don't have to prepare two queries.
        if ($p_to === null || empty($p_to)) {
            $p_to = $p_from;
        }

        $l_sql = 'SELECT isys_cats_net_ip_addresses_list__id FROM isys_cats_net_ip_addresses_list ' . 'WHERE isys_cats_net_ip_addresses_list__isys_obj__id = ' .
            $this->convert_sql_int($p_net_id) . ' ' . 'AND isys_cats_net_ip_addresses_list__ip_address_long BETWEEN ' . Ip::ip2long($p_from) . ' AND ' . Ip::ip2long($p_to) .
            ';';

        $l_res = $this->retrieve($l_sql);

        while ($l_row = $l_res->get_row()) {
            $l_id[] = $l_row['isys_cats_net_ip_addresses_list__id'];
        }

        if (count($l_id) > 0) {
            // Determine which field should be updated
            if ($l_net_row['isys_cats_net_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
                $l_assignment_field = 'isys_catg_ip_list__isys_ipv6_assignment__id';
            } else {
                $l_assignment_field = 'isys_catg_ip_list__isys_ip_assignment__id';
            }

            $l_sql = 'UPDATE isys_catg_ip_list SET ' . $l_assignment_field . ' = ' . $this->convert_sql_int($p_ip_assignment) . ' ' .
                'WHERE isys_catg_ip_list__isys_cats_net_ip_addresses_list__id IN (' . implode(', ', $l_id) . ');';

            $this->update($l_sql);
            if ($this->apply_update()) {
                return true;
            }
        }

        return false;
    }

    /**
     * With this method you can change the Zone-assignment by a given IP range.
     *
     * @param   integer $p_net_obj_id  The Layer3-Net object ID
     * @param   integer $p_zone_obj_id The new IP-assignment ID
     * @param   string  $p_from
     * @param   string  $p_to
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function update_ip_zones_by_range($p_net_obj_id, $p_zone_obj_id, $p_from, $p_to = null)
    {
        if (empty($p_from)) {
            return false;
        }

        // If we only get one IP, we set the other one so we don't have to prepare two queries.
        if ($p_to === null || empty($p_to)) {
            $p_to = $p_from;
        }

        $l_sql = 'UPDATE isys_catg_ip_list
            INNER JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id
            SET isys_catg_ip_list__isys_obj__id__zone = ' . $this->convert_sql_id($p_zone_obj_id) . '
            WHERE isys_cats_net_ip_addresses_list__isys_obj__id = ' . $this->convert_sql_id($p_net_obj_id) . '
            AND isys_cats_net_ip_addresses_list__ip_address_long BETWEEN ' . Ip::ip2long($p_from) . ' AND ' . Ip::ip2long($p_to) . '
            AND isys_catg_ip_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * Method for simply updating a IP address (inside the same net).
     *
     * @param   integer $p_id
     * @param   string  $p_new_ip
     *
     * @return  boolean
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function update_ip_address($p_id, $p_new_ip)
    {
        $l_dao_ip_address = isys_cmdb_dao_category_s_net_ip_addresses::instance($this->get_database_component());

        $l_data = $this->get_data($p_id)
            ->get_row();
        $l_ip_address_data = $l_dao_ip_address->get_data($l_data['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'])
            ->get_row();

        return $l_dao_ip_address->save(
            $l_data['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'],
            $p_new_ip,
            $l_ip_address_data['isys_cats_net_ip_addresses_list__isys_obj__id'],
            $l_ip_address_data['isys_cats_net_ip_addresses_list__isys_ip_assignment__id'],
            $l_ip_address_data['isys_cats_net_ip_addresses_list__status']
        );
    }

    /**
     * Inside this method, we set the given host-address as primary and all the others as non-primary.
     *
     * @param   integer $p_catg_id
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @return  boolean
     */
    public function update_primary_hostaddress($p_catg_id)
    {
        $l_sql = "SELECT isys_catg_ip_list__isys_obj__id FROM isys_catg_ip_list WHERE isys_catg_ip_list__id = " . $this->convert_sql_int($p_catg_id) . ";";

        $l_obj_id = $this->retrieve($l_sql)
            ->get_row_value('isys_catg_ip_list__isys_obj__id');

        $l_sql = "UPDATE isys_catg_ip_list SET isys_catg_ip_list__primary = '0' WHERE isys_catg_ip_list__isys_obj__id = " . $this->convert_sql_int($l_obj_id) .
            " AND isys_catg_ip_list__id != " . $this->convert_sql_int($p_catg_id) . ";";

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Builds an array with minimal requirement for the sync function.
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_import_array($p_data)
    {
        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'net_type'              => [
                    'value' => $p_data['net_type']
                ],
                'primary'               => [
                    'value' => $p_data['primary']
                ],
                'active'                => [
                    'value' => $p_data['active']
                ],
                'net'                   => [
                    'value' => $p_data['net']
                ],
                'ipv4_assignment'       => [
                    'value' => $p_data['ipv4_assignment']
                ],
                'ipv4_address'          => [
                    'value' => $p_data['ipv4_address']
                ],
                'ipv6_assignment'       => [
                    'value' => $p_data['ipv6_assignment']
                ],
                'ipv6_address'          => [
                    'value' => $p_data['ipv6_address']
                ],
                'hostname'              => [
                    'value' => $p_data['hostname']
                ],
                'domain'                => [
                    'value' => $p_data['domain']
                ],
                'assigned_port'         => [
                    'value' => $p_data['assigned_port']
                ],
                'assigned_logical_port' => [
                    'value' => $p_data['assigned_logical_port']
                ],
                'dns_domain'            => [
                    'value' => $p_data['dns_domain']
                ],
                'description'           => [
                    'value' => $p_data['description']
                ]
            ]
        ];
    }

    /**
     * Reassigns an ip address to the specified net
     *
     * @param $p_ip_id
     * @param $p_new_net_obj
     *
     * @return bool
     */
    public function reassign_ip($p_object_id, $p_address, $p_type, $p_new_net_obj)
    {
        /**
         * @var $l_dao_relation isys_cmdb_dao_category_g_relation
         */
        $l_dao_relation = isys_cmdb_dao_category_g_relation::instance($this->m_db);

        $l_res = $this->get_ip_info_by_address($p_address, $p_type);
        while ($l_ip_data = $l_res->get_row()) {
            $l_catg_id = $l_ip_data['isys_catg_ip_list__id'];
            $l_relation_id = $l_ip_data['isys_catg_ip_list__isys_catg_relation_list__id'];
            $l_cats_id = $l_ip_data['isys_cats_net_ip_addresses_list__id'];
            $l_obj_id = $l_ip_data['isys_catg_ip_list__isys_obj__id'];

            if ($l_obj_id == $p_object_id) {
                continue;
            }

            $l_update = 'UPDATE isys_cats_net_ip_addresses_list SET ' . 'isys_cats_net_ip_addresses_list__isys_obj__id = ' . $this->convert_sql_id($p_new_net_obj) . ' ' .
                'WHERE isys_cats_net_ip_addresses_list__id = ' . $this->convert_sql_id($l_cats_id);

            if ($this->update($l_update) && $this->apply_update()) {
                $l_dao_relation->handle_relation($l_catg_id, 'isys_catg_ip_list', defined_or_default('C__RELATION_TYPE__IP_ADDRESS'), $l_relation_id, $p_new_net_obj, $l_obj_id);
            }
        }
    }

    public function get_ip_info_by_address($p_address, $p_type)
    {
        if ($p_type == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
            $l_condition = 'WHERE isys_cats_net_ip_addresses_list__title = ' . $this->convert_sql_text(Ip::validate_ipv6($p_address));
        } else {
            $l_condition = 'WHERE isys_cats_net_ip_addresses_list__ip_address_long = ' . $this->convert_sql_int(Ip::ip2long($p_address));
        }

        $l_sql = 'SELECT * FROM isys_cats_net_ip_addresses_list INNER JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id ' .
            $l_condition;

        return $this->retrieve($l_sql);
    }

    /**
     * Checks if the specified ip is in use.
     *
     * @param   integer $p_net_obj_id
     * @param   string  $p_ip_address
     * @param   integer $p_object_id
     *
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     * @return  boolean
     */
    public function ip_already_in_use($p_net_obj_id, $p_ip_address, $p_object_id = null)
    {
        $l_sql = "SELECT isys_cats_net_ip_addresses_list__id
			FROM isys_cats_net_ip_addresses_list
			LEFT JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
			LEFT JOIN isys_obj AS net ON net.isys_obj__id = isys_cats_net_ip_addresses_list__isys_obj__id
			LEFT JOIN isys_obj AS obj ON obj.isys_obj__id = isys_catg_ip_list__isys_obj__id
			WHERE isys_cats_net_ip_addresses_list__isys_obj__id = " . $this->convert_sql_id($p_net_obj_id) . "
			AND isys_cats_net_ip_addresses_list__title = " . $this->convert_sql_text($p_ip_address) . "
			AND (net.isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . "
			AND isys_cats_net_ip_addresses_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . "
			AND isys_catg_ip_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . "
			AND obj.isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ')';

        if ($p_object_id !== null) {
            $l_sql .= " AND isys_catg_ip_list__isys_obj__id != " . $this->convert_sql_id($p_object_id);
        }

        return ($this->retrieve($l_sql . ";")
                ->num_rows() > 0);
    }

    /**
     * Calculates ip range.
     *
     * @deprecated
     *
     * @param   string $p_net_address
     * @param   string $p_subnet_mask
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     * @todo    Wait until next release to remove, this is used in 0.9.9-9 migration.
     */
    public function calc_ip_range($p_net_address, $p_subnet_mask)
    {
        return Ip::calc_ip_range($p_net_address, $p_subnet_mask);
    }

    /**
     * Gets next free ipv6 address from the specified net Object.
     *
     * @param   integer $p_net_obj
     * @param   string  $p_range_from
     * @param   string  $p_range_to
     *
     * @return  mixed  string with free IP address or boolean false.
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function get_free_ipv6($p_net_obj, $p_range_from = null, $p_range_to = null)
    {
        $l_dao_ip_list = isys_cmdb_dao_category_s_net_ip_addresses::instance($this->m_db);
        $l_dao_net = isys_cmdb_dao_category_s_net::instance($this->m_db);

        $l_data = $l_dao_net->get_data(null, $p_net_obj)
            ->get_row();
        $l_assigned_ip_list = $l_dao_ip_list->get_assigned_ips_as_array($p_net_obj);

        // When setted, we use the given FROM and TO values from the parameters.
        $l_range_from = ($p_range_from !== null) ? $p_range_from : $l_data['isys_cats_net_list__address_range_from'];
        $l_range_to = ($p_range_to !== null) ? $p_range_to : $l_data['isys_cats_net_list__address_range_to'];

        $l_cidr_suffix = $l_data['isys_cats_net_list__cidr_suffix'];

        $l_current_ip_arr = explode(':', $l_range_from);
        $l_counter = count($l_current_ip_arr) - 1;
        $l_found = false;
        $l_max_dec = hexdec('ffff');
        $l_dec = 0;

        if ($l_cidr_suffix == 128) {
            if (is_countable($l_assigned_ip_list) && count($l_assigned_ip_list) > 0) {
                return false;
            } else {
                return Ip::validate_ipv6(implode(':', $l_current_ip_arr));
            }
        } else {
            while (!$l_found && $l_counter >= 0) {
                if ($l_current_ip_arr[$l_counter] != 'ffff') {
                    while ($l_dec < $l_max_dec && !$l_found) {
                        $l_current_ip = implode(':', $l_current_ip_arr);

                        if (!in_array($l_current_ip, $l_assigned_ip_list)) {
                            $l_found = true;
                            continue;
                        }

                        $l_dec = hexdec($l_current_ip_arr[$l_counter]);
                        $l_dec = $l_dec + 1;
                        $l_current_ip_arr[$l_counter] = dechex($l_dec);

                        $l_hex_length = strlen($l_current_ip_arr[$l_counter]);
                        $l_zeros = '';

                        while ($l_hex_length < 4) {
                            $l_zeros .= '0';
                            $l_hex_length++;
                        }

                        $l_current_ip_arr[$l_counter] = $l_zeros . $l_current_ip_arr[$l_counter];
                    }
                }

                $l_counter--;
            }
        }

        if ($l_found && Ip::is_ipv6_in_range($l_current_ip, $l_range_from, $l_range_to)) {
            return Ip::validate_ipv6($l_current_ip);
        } else {
            return false;
        }
    }

    /**
     * SignalSlot-Method to guarantee unique ips
     *
     * @author Selcuk Kekec <skekec@synetics.de>
     *
     * @param isys_cmdb_dao $p_cmdb_dao
     * @param type          $p_direction
     * @param array         $p_objectIDs
     */
    public function unique_handling(isys_cmdb_dao $p_cmdb_dao, $p_direction, array $p_objectIDs)
    {
        $l_ips = [];
        $l_dao = new self($p_cmdb_dao->get_database_component());

        /* ? Recycle-Mode ? */
        if ($p_direction == 2 && $_POST['cRecStatus'] == C__RECORD_STATUS__ARCHIVED && count($p_objectIDs)) {
            foreach ($p_objectIDs as $l_objectID) {
                $l_tmp = $l_dao->get_object_by_id($l_objectID)
                    ->get_row();
                $l_object_title = $l_tmp['isys_obj__title'];
                $l_data = $l_dao->get_data(null, $l_objectID, null, null, C__RECORD_STATUS__NORMAL);

                /* Do we have ip addresses to handle */
                if (is_countable($l_data) && count($l_data)) {
                    while ($l_row = $l_data->get_row()) {
                        /* Is this ip already in use */
                        $l_catLevel = $l_dao->in_use($l_row['isys_cats_net_ip_addresses_list__isys_obj__id'], /* Net */
                            $l_row['isys_cats_net_ip_addresses_list__title'], /* IP-Address */
                            $l_row['isys_catg_ip_list__id'], /* Catg-ID to ignore */
                            !isys_tenantsettings::get('cmdb.unique.ip-address')); /* GlobalNet ignore switch */

                        if ($l_catLevel) {
                            /* Here we have an ip that is detected as duplicate and needs special-handling */
                            $l_dao->update_catlevel($l_row['isys_catg_ip_list__id'], C__RECORD_STATUS__ARCHIVED);
                            $l_ips[$l_object_title][] = $l_row['isys_cats_net_ip_addresses_list__title'];
                        }
                    }
                }
            }

            if (count($l_ips)) {
                $l_strInfo = isys_application::instance()->container->get('language')
                        ->get('LC__CMDB__UNIQUE__IPS__ARCHIVED_ENTRIES') . "<br><br>";

                foreach ($l_ips as $l_object_title => $l_dupeIPs) {
                    $l_strInfo .= "<b>" . $l_object_title . '</b><br>';
                    $l_strInfo .= "<ul><li>" . implode("</li><li>", $l_dupeIPs) . "</li></ul>";
                }

                isys_application::instance()->container['notify']->error($l_strInfo);
            }
        }
    }

    /**
     * Method for retrieving hostname pairs defined in a given category entry.
     *
     * @param   integer $p_data_id
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_hostname_pairs($p_data_id)
    {
        return $this->retrieve('SELECT * FROM isys_hostaddress_pairs WHERE isys_hostaddress_pairs__isys_catg_ip_list__id = ' . $this->convert_sql_id($p_data_id) . ';');
    }

    /**
     * Method for removing hostname pairs of a given category entry.
     *
     * @param integer $p_data_id
     *
     * @return boolean
     * @author Leonard Fischer <lfischer@i-doit.com>
     */
    public function remove_hostname_pairs($p_data_id)
    {
        return $this->update('DELETE FROM isys_hostaddress_pairs WHERE isys_hostaddress_pairs__isys_catg_ip_list__id = ' . $this->convert_sql_id($p_data_id) . ';');
    }

    // ################################
    // ### Functions for ipv4 START ###
    // ################################

    /**
     * Method for adding hostname pairs to a given category entry.
     *
     * @param integer $p_data_id
     * @param array   $p_pairs
     *
     * @return boolean
     * @author Leonard Fischer <lfischer@i-doit.com>
     */
    public function add_hostname_pairs($p_data_id, array $p_pairs = [])
    {
        $l_sql_inserts = [];
        $l_sql = 'INSERT INTO isys_hostaddress_pairs (isys_hostaddress_pairs__isys_catg_ip_list__id, isys_hostaddress_pairs__hostname, isys_hostaddress_pairs__domain) VALUES ';

        foreach ($p_pairs as $p_pair) {
            if (!isset($p_pair['host']) || !isset($p_pair['domain'])) {
                continue;
            }

            $l_sql_inserts[] = '(' . $this->convert_sql_id($p_data_id) . ', ' . $this->convert_sql_text($p_pair['host']) . ', ' . $this->convert_sql_text($p_pair['domain']) .
                ')';
        }

        if (count($l_sql_inserts)) {
            return $this->update($l_sql . implode(',', $l_sql_inserts) . ';');
        }

        return true;
    }

    /**
     * Method for setting hostname pairs to a given category entry.
     *
     * @param integer $p_data_id
     * @param array   $p_pairs
     *
     * @return boolean
     * @author Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_hostname_pairs($p_data_id, array $p_pairs = [])
    {
        // At first we remove all.
        $this->remove_hostname_pairs($p_data_id);

        // Then we add the given pairs.
        return (count($p_pairs)) ? $this->add_hostname_pairs($p_data_id, $p_pairs) : true;
    }

    public function retrieveUseStandardGateway(array $p_row)
    {
        $ipData = $this->get_data($p_row['isys_catg_ip_list__id'], null, '', '', C__RECORD_STATUS__NORMAL)->__as_array();
        $language = isys_application::instance()->container->get('language');
        if (!empty($ipData)) {
            $request = new isys_request();
            $request->set_row($ipData[0]);

            $useStandardGateway = $this->callback_property_use_standard_gateway($request);

            return $useStandardGateway === 0 ? $language->get('LC__UNIVERSAL__NO') : $language->get('LC__UNIVERSAL__YES');
        }
        return $language->get('LC__UNIVERSAL__YES');
    }

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_primary_ip' => (new DynamicProperty(
                'LC__CATG__IP__PRIMARY_ADDRESS',
                'isys_catg_ip_list__isys_obj__id',
                'isys_catg_ip_list',
                [
                    $this,
                    'dynamic_property_callback_primary_ip'
                ]
            ))->mergePropertyData([C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                '',
                '',
                '',
                '',
                '',
                '',
                idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory(['isys_catg_ip_list__primary=1'])
            )]),
            '_primary_hostname' => (new DynamicProperty(
                'LC__CATP__IP__PRIMARY_HOSTNAME',
                'isys_catg_ip_list__isys_obj__id',
                'isys_catg_ip_list',
                [
                    $this,
                    'dynamic_property_callback_primary_hostname'
                ]
            ))->mergePropertyData([C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                '',
                '',
                '',
                '',
                '',
                '',
                idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory(['isys_catg_ip_list__primary=1'])
            )]),
            '_fqdn' => new DynamicProperty(
                'LC__CATP__IP__HOSTNAME_FQDN',
                'isys_catg_ip_list__id',
                'isys_catg_ip_list',
                [
                    $this,
                    'dynamic_property_callback_hostname_fqdn'
                ]
            ),
            '_dns_domain' => new DynamicProperty(
                'LC__CMDB__CATS__NET__SEARCH_DOMAIN',
                'isys_catg_ip_list__id',
                'isys_catg_ip_list',
                [
                    $this,
                    'dynamic_property_callback_dns_domain'
                ]
            ),
            '_use_standard_gateway' => (new DynamicYesNoProperty(
                'LC__CATG__IP__DEFAULT_GATEWAY_FOR_THE_NET',
                'isys_catg_ip_list__id',
                'isys_catg_ip_list',
                [
                    $this,
                    'retrieveUseStandardGateway'
                ]
            )),
            '_aliases' => (new DynamicProperty(
                'LC__CATG__IP__ALIASES',
                'isys_catg_ip_list__id',
                'isys_catg_ip_list',
                [
                    $this,
                    'retrieveHostaddressAliase'
                ]
            ))
        ];
    }

    /**
     * @param $row
     *
     * @return string
     */
    public function retrieveHostaddressAliase($row)
    {
        $result = $this->get_hostname_pairs($row['isys_catg_ip_list__id']);

        if ($result instanceof isys_component_dao_result && count($result)) {
            $aliases = [];
            while ($ipData = $result->get_row()) {
                $aliases[] = $ipData['isys_hostaddress_pairs__hostname'] . (!empty($ipData['isys_hostaddress_pairs__domain']) ?
                    '.' . $ipData['isys_hostaddress_pairs__domain'] : '');
            }
            return implode(', ', $aliases);
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Return database field to be used as breadcrumb title
     *
     * @return string
     */
    public function get_breadcrumb_field()
    {
        return 'isys_catg_ip_list__address';
    }

    // ################################
    // ### Functions for ipv4 START ###
    // ################################

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'primary_layer3_net'   => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__IP__LAYER3_NET',
                    C__PROPERTY__INFO__DESCRIPTION => 'Primary net title',
                    C__PROPERTY__INFO__PRIMARY     => true
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_cats_net_ip_addresses_list',
                        'isys_cats_net_ip_addresses_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT o.isys_obj__title
                              FROM isys_catg_ip_list c
                                INNER JOIN isys_cats_net_ip_addresses_list l ON c.isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = l.isys_cats_net_ip_addresses_list__id 
                                INNER JOIN isys_obj as o ON l.isys_cats_net_ip_addresses_list__isys_obj__id = o.isys_obj__id',
                        'isys_catg_ip_list',
                        'isys_catg_ip_list__id',
                        'isys_catg_ip_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory(['isys_catg_ip_list__primary = 1']),
                        null,
                        '',
                        1
                    ),
                    C__PROPERTY__DATA__SORT => 'SELECT isys_cats_net_ip_addresses_list__ip_address_long FROM isys_catg_ip_list
                                INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
                                WHERE isys_catg_ip_list__primary = 1 AND isys_catg_ip_list__isys_obj__id = obj_main.isys_obj__id LIMIT 1',
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_ip_list', 'LEFT', 'isys_catg_ip_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_net_ip_addresses_list',
                            'LEFT',
                            'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
                            'isys_cats_net_ip_addresses_list__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_obj',
                            'LEFT',
                            'isys_cats_net_ip_addresses_list__isys_obj__id',
                            'isys_obj__id'
                        )
                    ],
                    C__PROPERTY__DATA__INDEX      => true
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__MANDATORY  => false,
                    C__PROPERTY__CHECK__VALIDATION => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_global_ip_export_helper',
                        'exportPrimaryIpReference'
                    ]
                ]
            ]),
            'primary_hostaddress'   => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__IP__PRIMARY_ADDRESS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Hostaddress',
                    C__PROPERTY__INFO__PRIMARY     => true
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_cats_net_ip_addresses_list',
                        'isys_cats_net_ip_addresses_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_net_ip_addresses_list__title
                              FROM isys_catg_ip_list
                                INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id',
                        'isys_catg_ip_list',
                        'isys_catg_ip_list__id',
                        'isys_catg_ip_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory(['isys_catg_ip_list__primary = 1']),
                        null,
                        '',
                        1
                    ),
                    C__PROPERTY__DATA__SORT => 'SELECT isys_cats_net_ip_addresses_list__ip_address_long FROM isys_catg_ip_list
                                INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
                                WHERE isys_catg_ip_list__primary = 1 AND isys_catg_ip_list__isys_obj__id = obj_main.isys_obj__id LIMIT 1',
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_ip_list', 'LEFT', 'isys_catg_ip_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_net_ip_addresses_list',
                            'LEFT',
                            'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
                            'isys_cats_net_ip_addresses_list__id'
                        )
                    ],
                    C__PROPERTY__DATA__INDEX      => true
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__MANDATORY  => false,
                    C__PROPERTY__CHECK__VALIDATION => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_global_ip_export_helper',
                        'exportPrimaryIpReference'
                    ]
                ]
            ]),
            'primary_hostname'      => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATP__IP__PRIMARY_HOSTNAME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Hostname'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_ip_list__hostname',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_ip_list__hostname
                              FROM isys_catg_ip_list',
                        'isys_catg_ip_list',
                        'isys_catg_ip_list__id',
                        'isys_catg_ip_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory(['isys_catg_ip_list__primary = 1']),
                        null,
                        '',
                        1
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_ip_list', 'LEFT', 'isys_catg_ip_list__isys_obj__id', 'isys_obj__id')
                    ],
                    C__PROPERTY__DATA__INDEX  => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_global_ip_export_helper',
                        'exportHostname'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__MANDATORY  => false,
                    C__PROPERTY__CHECK__VALIDATION => false
                ]
            ]),
            'net_type'              => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__NETWORK__TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Type'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_ip_list__isys_net_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_net_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_net_type',
                        'isys_net_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_net_type__title
                            FROM isys_catg_ip_list
                            INNER JOIN isys_net_type ON isys_net_type__id = isys_catg_ip_list__isys_net_type__id',
                        'isys_catg_ip_list',
                        'isys_catg_ip_list__id',
                        'isys_catg_ip_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_ip_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_ip_list', 'LEFT', 'isys_catg_ip_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_net_type', 'LEFT', 'isys_catg_ip_list__isys_net_type__id', 'isys_net_type__id')
                    ],
                    C__PROPERTY__DATA__INDEX        => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__NET__TYPE',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_strTable'   => 'isys_net_type',
                        'p_bDbFieldNN' => 1
                    ],
                    C__PROPERTY__UI__DEFAULT => defined_or_default('C__CATS_NET_TYPE__IPV4')
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false
                ]
            ]),
            'primary'               => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONTACT_LIST__PRIMARY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Primary'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_ip_list__primary',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_catg_ip_list__primary = \'1\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__YES') . '
                        	        WHEN isys_catg_ip_list__primary = \'0\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__NO') . ' END)
                                FROM isys_catg_ip_list',
                        'isys_catg_ip_list',
                        'isys_catg_ip_list__id',
                        'isys_catg_ip_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_ip_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_ip_list', 'LEFT', 'isys_catg_ip_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__CATP__IP__PRIMARY',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_arData'     => get_smarty_arr_YES_NO(),
                        'p_bDbFieldNN' => 1,
                        'p_strClass'   => 'input-mini'
                    ],
                    C__PROPERTY__UI__DEFAULT => 1
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_yes_or_no'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => false
                ]
            ]),
            'active'                => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATP__IP__ACTIVE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Active'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_ip_list__active',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_catg_ip_list__active = \'1\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__YES') . '
                        	        WHEN isys_catg_ip_list__active = \'0\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__NO') . ' END)
                                FROM isys_catg_ip_list',
                        'isys_catg_ip_list',
                        'isys_catg_ip_list__id',
                        'isys_catg_ip_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_ip_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_ip_list', 'LEFT', 'isys_catg_ip_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__CATP__IP__ACTIVE',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_arData'              => get_smarty_arr_YES_NO(),
                        'p_bDbFieldNN'          => 1,
                        'p_bInfoIconSpacer'     => 0,
                        'p_strClass'            => 'input-mini',
                        'inputGroupMarginClass' => ''
                    ],
                    // refs #4904
                    C__PROPERTY__UI__DEFAULT => 1
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_yes_or_no'
                    ]
                ]
            ]),
            'net'                   => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__NET',
                    C__PROPERTY__INFO__DESCRIPTION => 'Net'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_cats_net_ip_addresses_list__isys_obj__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__IP_ADDRESS'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_g_ip',
                        'callback_property_relation_handler'
                    ], [
                        'isys_cmdb_dao_category_g_ip',
                        true
                    ]),
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(IF(isys_cats_net_list__address = \'\', isys_obj__title, CONCAT(isys_cats_net_list__address, \'/\' ,isys_cats_net_list__cidr_suffix)), \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_ip_list
                            INNER JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_cats_net_ip_addresses_list__isys_obj__id
                            INNER JOIN isys_cats_net_list ON isys_cats_net_list__isys_obj__id = isys_obj__id',
                        'isys_catg_ip_list',
                        'isys_catg_ip_list__id',
                        'isys_catg_ip_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_ip_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_ip_list', 'LEFT', 'isys_catg_ip_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_net_ip_addresses_list',
                            'LEFT',
                            'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
                            'isys_cats_net_ip_addresses_list__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_cats_net_ip_addresses_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__CATG__IP__NET',
                    C__PROPERTY__UI__PARAMS  => [
                        'catFilter'     => 'C__CATS__NET;C__CATS__NET_IP_ADDRESSES',
                        'typeBlacklist' => 'C__OBJTYPE__SUPERNET;C__OBJTYPE__MIGRATION_OBJECT',
                    ],
                    C__PROPERTY__UI__DEFAULT => defined_or_default('C__OBJ__NET_GLOBAL_IPV4')
                ],
                C__PROPERTY__CHECK    => [],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ]
            ]),
            'zone'                  => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO   => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__NET__ZONE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Net zone'
                ],
                C__PROPERTY__DATA   => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_ip_list__isys_obj__id__zone',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj_type__title, \' > \', isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_ip_list
                            INNER JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_ip_list__isys_obj__id__zone
                            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id',
                        'isys_catg_ip_list',
                        'isys_catg_ip_list__id',
                        'isys_catg_ip_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_ip_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_ip_list', 'LEFT', 'isys_catg_ip_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_net_ip_addresses_list',
                            'LEFT',
                            'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
                            'isys_cats_net_ip_addresses_list__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_ip_list__isys_obj__id__zone', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI     => [
                    C__PROPERTY__UI__ID      => 'C__CATG__IP__ZONE',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_arData'   => 'C__CATS__NET_IP_ADDRESSES',
                        'disableInputGroup' => true,
                        'p_bInfoIconSpacer' => 0
                    ],
                    C__PROPERTY__UI__DEFAULT => 0
                ],
                C__PROPERTY__FORMAT => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ],
                C__PROPERTY__DEPENDENCY => [
                    C__PROPERTY__DEPENDENCY__PROPKEY => 'net', // property key
                    C__PROPERTY__DEPENDENCY__SMARTYPARAMS => [
                        C__PROPERTY__DEPENDENCY__CONDITION      => 'isys_cats_net_zone_list__isys_obj__id = %s',
                        C__PROPERTY__DEPENDENCY__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                            'SELECT isys_obj__id, isys_obj__title
                            FROM isys_cats_net_zone_list
                            INNER JOIN isys_obj ON isys_obj__id = isys_cats_net_zone_list__isys_obj__id__zone',
                            'isys_obj',
                            'isys_cats_net_zone_list__id',
                            'isys_cats_net_zone_list__isys_obj__id'
                        )
                    ]
                ]
            ]),
            'ipv4_assignment'       => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATP__IP__ASSIGN_IPV4',
                    C__PROPERTY__INFO__DESCRIPTION => 'Address allocation IPv4'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_ip_list__isys_ip_assignment__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_ip_assignment',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_ip_assignment',
                        'isys_ip_assignment__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_ip_assignment__title
                            FROM isys_catg_ip_list
                            INNER JOIN isys_ip_assignment ON isys_ip_assignment__id = isys_catg_ip_list__isys_ip_assignment__id',
                        'isys_catg_ip_list',
                        'isys_catg_ip_list__id',
                        'isys_catg_ip_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_ip_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_ip_list', 'LEFT', 'isys_catg_ip_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_ip_assignment',
                            'LEFT',
                            'isys_catg_ip_list__isys_ip_assignment__id',
                            'isys_ip_assignment__id'
                        ),
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__CATP__IP__ASSIGN',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_strTable'   => 'isys_ip_assignment',
                        'p_bDbFieldNN' => 1,
                        'p_strClass'   => 'input-small'
                    ],
                    C__PROPERTY__UI__DEFAULT => defined_or_default('C__CATP__IP__ASSIGN__DHCP')
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => true
                ]
            ]),
            'ipv4_address'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IP__IPV4_ADDRESS',
                    C__PROPERTY__INFO__DESCRIPTION => 'IPv4 address',
                    C__PROPERTY__INFO__PRIMARY     => true
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'ipv4',
                    C__PROPERTY__DATA__REFERENCES  => [
                        'isys_cats_net_ip_addresses_list',
                        'isys_cats_net_ip_addresses_list__id'
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATP__IP__ADDRESS_V4'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_global_ip_export_helper',
                        'exportIpReference'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__SEARCH => false
                    // key 'hostaddress' is used for searching
                ]
            ]),
            'ipv6_assignment'       => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATP__IP__ASSIGN_IPV6',
                    C__PROPERTY__INFO__DESCRIPTION => 'Address allocation IPv6'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_ip_list__isys_ipv6_assignment__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_ipv6_assignment',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_ipv6_assignment',
                        'isys_ipv6_assignment__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_ipv6_assignment__title
                            FROM isys_catg_ip_list
                            INNER JOIN isys_ipv6_assignment ON isys_ipv6_assignment__id = isys_catg_ip_list__isys_ipv6_assignment__id',
                        'isys_catg_ip_list',
                        'isys_catg_ip_list__id',
                        'isys_catg_ip_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_ip_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_ip_list', 'LEFT', 'isys_catg_ip_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_ipv6_assignment',
                            'LEFT',
                            'isys_catg_ip_list__isys_ipv6_assignment__id',
                            'isys_ipv6_assignment__id'
                        ),
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__IP__IPV6_ASSIGNMENT',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'   => 'isys_ipv6_assignment',
                        'p_bDbFieldNN' => 1
                    ],
                    C__PROPERTY__UI__DEFAULT => defined_or_default('C__CMDB__CATG__IP__STATIC')
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => true
                ]
            ]),
            'ipv6_scope'            => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IP__IPV6_SCOPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'IPv6 scope'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_ip_list__isys_ipv6_scope__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_ipv6_scope',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_ipv6_scope',
                        'isys_ipv6_scope__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_ipv6_scope__title
                            FROM isys_catg_ip_list
                            INNER JOIN isys_ipv6_scope ON isys_ipv6_scope__id = isys_catg_ip_list__isys_ipv6_scope__id',
                        'isys_catg_ip_list',
                        'isys_catg_ip_list__id',
                        'isys_catg_ip_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_ip_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_ip_list', 'LEFT', 'isys_catg_ip_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_ipv6_scope', 'LEFT', 'isys_catg_ip_list__isys_ipv6_scope__id', 'isys_ipv6_scope__id'),
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__CMDB__CATG__IP__IPV6_SCOPE',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_strTable' => 'isys_ipv6_scope'
                    ],
                    C__PROPERTY__UI__DEFAULT => defined_or_default('C__CMDB__CATG__IP__GLOBAL_UNICAST')
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__REPORT    => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false
                ]
            ]),
            'ipv6_address'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IP__IPV6_ADDRESS',
                    C__PROPERTY__INFO__DESCRIPTION => 'IPv6 address',
                    C__PROPERTY__INFO__PRIMARY     => true
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'ipv6',
                    C__PROPERTY__DATA__REFERENCES  => [
                        'isys_cats_net_ip_addresses_list',
                        'isys_cats_net_ip_addresses_list__id'
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__IP__IPV6_ADDRESS'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_global_ip_export_helper',
                        'exportIpReference'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__SEARCH => false
                    // key 'hostaddress' is used for searching
                ]
            ]),
            'hostaddress'           => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__IP_ADDRESS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Hostaddress',
                    C__PROPERTY__INFO__PRIMARY     => true
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_cats_net_ip_addresses_list',
                        'isys_cats_net_ip_addresses_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_net_ip_addresses_list__title
                              FROM isys_catg_ip_list
                                INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id',
                        'isys_catg_ip_list',
                        'isys_catg_ip_list__id',
                        'isys_catg_ip_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_ip_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_ip_list', 'LEFT', 'isys_catg_ip_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_net_ip_addresses_list',
                            'LEFT',
                            'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
                            'isys_cats_net_ip_addresses_list__id'
                        )
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__REPORT     => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__SEARCH     => true,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__VIRTUAL    => false
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__MANDATORY  => false,
                    C__PROPERTY__CHECK__VALIDATION => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_global_ip_export_helper',
                        'exportIpReference'
                    ]
                ]
            ]),
            'hostname'              => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATP__IP__HOSTNAME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Hostname'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_ip_list__hostname'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_global_ip_export_helper',
                        'exportHostname'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => false,
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATP__IP__HOSTNAME',
                    C__PROPERTY__UI__PARAMS => [
                        'disableInputGroup' => true,
                        'p_bInfoIconSpacer' => 0,
                        'p_strPlaceholder'  => 'LC__CATP__IP__HOSTNAME'
                    ]
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__SANITIZATION => [
                        FILTER_CALLBACK,
                        [
                            'options' => [
                                'isys_helper',
                                'strip_whitespaces'
                            ]
                        ]
                    ]
                ]
            ]),
            'domain'                => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__IP__DOMAIN',
                    C__PROPERTY__INFO__DESCRIPTION => 'Domain'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_ip_list__domain'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_global_ip_export_helper',
                        'exportHostname'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => false,
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__IP__DOMAIN',
                    C__PROPERTY__UI__PARAMS => [
                        'disableInputGroup' => true,
                        'p_bInfoIconSpacer' => 0,
                        'p_strPlaceholder'  => 'LC__CATG__IP__DOMAIN'
                    ]
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__SANITIZATION => [
                        FILTER_CALLBACK,
                        [
                            'options' => [
                                'isys_helper',
                                'strip_whitespaces'
                            ]
                        ]
                    ]
                ]
            ]),
            'dns_server'            => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATP__IP__DNSSERVER',
                    C__PROPERTY__INFO__DESCRIPTION => 'DNS Server'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_ip_list__id',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'dns',
                    C__PROPERTY__DATA__REFERENCES  => [
                        'isys_catg_ip_list_2_isys_catg_ip_list',
                        'isys_catg_ip_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                              FROM isys_catg_ip_list AS ip1
                                INNER JOIN isys_catg_ip_list_2_isys_catg_ip_list AS ip2ip ON ip2ip.isys_catg_ip_list__id = ip1.isys_catg_ip_list__id
                                INNER JOIN isys_catg_ip_list AS dns ON dns.isys_catg_ip_list__id = ip2ip.isys_catg_ip_list__id__dns
                                INNER JOIN isys_obj ON isys_obj__id = dns.isys_catg_ip_list__isys_obj__id',
                        'isys_catg_ip_list',
                        'ip1.isys_catg_ip_list__id',
                        'ip1.isys_catg_ip_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['ip1.isys_catg_ip_list__id'])
                    ),
                    C__PROPERTY__DATA__JOIN        => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_ip_list',
                            'LEFT',
                            'isys_catg_ip_list__isys_obj__id',
                            'isys_obj__id',
                            'ip',
                            '',
                            'ip'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_ip_list_2_isys_catg_ip_list',
                            'LEFT',
                            'isys_catg_ip_list__id',
                            'isys_catg_ip_list__id',
                            'ip',
                            'ip2ip',
                            'ip2ip'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_ip_list',
                            'LEFT',
                            'isys_catg_ip_list__id__dns',
                            'isys_catg_ip_list__id',
                            'ip2ip',
                            'dns',
                            'dns'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_obj',
                            'LEFT',
                            'isys_catg_ip_list__isys_obj__id',
                            'isys_obj__id',
                            'dns'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__IP__ASSIGNED_DNS_SERVER',
                    C__PROPERTY__UI__PARAMS => [
                        'catFilter'        => 'C__CATG__IP',
                        'multiselection'   => true,
                        // @todo Property Callback for multiedit (in future).
                        'secondSelection'  => true,
                        'secondList'       => 'isys_cmdb_dao_category_g_ip::object_browser',
                        'secondListFormat' => 'isys_cmdb_dao_category_g_ip::format_selection',
                        'p_strSelectedID'  => new isys_callback([
                            'isys_cmdb_dao_category_g_ip',
                            'callback_property_dns_server'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false,
                    C__PROPERTY__PROVIDES__SEARCH => false,
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_specific_net_export_helper',
                        'exportDnsServer'
                    ]
                ]
            ]),
            'dns_server_address'    => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATP__IP__DNSSERVER_ADDRESS',
                    C__PROPERTY__INFO__DESCRIPTION => 'DNS Server address'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_ip_list__id',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'dns',
                    C__PROPERTY__DATA__REFERENCES  => [
                        'isys_catg_ip_list_2_isys_catg_ip_list',
                        'isys_catg_ip_list__id'
                    ],

                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_net_ip_addresses_list__title
                              FROM isys_catg_ip_list AS ip1
                                INNER JOIN isys_catg_ip_list_2_isys_catg_ip_list AS ip2ip ON ip2ip.isys_catg_ip_list__id = ip1.isys_catg_ip_list__id
                                INNER JOIN isys_catg_ip_list AS dns ON dns.isys_catg_ip_list__id = ip2ip.isys_catg_ip_list__id__dns
                                INNER JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = dns.isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
                        'isys_catg_ip_list',
                        'ip1.isys_catg_ip_list__id',
                        'ip1.isys_catg_ip_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['ip1.isys_catg_ip_list__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_ip_list',
                            'LEFT',
                            'isys_catg_ip_list__isys_obj__id',
                            'isys_obj__id',
                            'ip',
                            '',
                            'ip'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_ip_list_2_isys_catg_ip_list',
                            'LEFT',
                            'isys_catg_ip_list__id',
                            'isys_catg_ip_list__id',
                            'ip',
                            'ip2ip',
                            'ip2ip'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_ip_list',
                            'LEFT',
                            'isys_catg_ip_list__id__dns',
                            'isys_catg_ip_list__id',
                            'ip2ip',
                            'dns',
                            'dns'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_net_ip_addresses_list',
                            'LEFT',
                            'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
                            'isys_cats_net_ip_addresses_list__id',
                            'dns'
                        )
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ]
            ]),
            'dns_domain'         => array_replace_recursive(isys_cmdb_dao_category_pattern::multiselect(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__NET__SEARCH_DOMAIN',
                    C__PROPERTY__INFO__DESCRIPTION => 'Search Domain'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_ip_list__id',
                    C__PROPERTY__DATA__TABLE_ALIAS  => 'dns_domain',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_net_dns_domain',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_catg_ip_list_2_isys_net_dns_domain',
                        'isys_catg_ip_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT dns.isys_net_dns_domain__title
                              FROM isys_catg_ip_list AS ip1
                                INNER JOIN isys_catg_ip_list_2_isys_net_dns_domain AS ip2dns ON ip2dns.isys_catg_ip_list__id = ip1.isys_catg_ip_list__id
                                INNER JOIN isys_net_dns_domain AS dns ON dns.isys_net_dns_domain__id = ip2dns.isys_net_dns_domain__id',
                        'isys_catg_ip_list',
                        'ip1.isys_catg_ip_list__id',
                        'ip1.isys_catg_ip_list__isys_obj__id',
                        'ip1.isys_catg_ip_list__id',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['ip1.isys_catg_ip_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_ip_list',
                            'LEFT',
                            'isys_catg_ip_list__isys_obj__id',
                            'isys_obj__id',
                            'ip',
                            '',
                            'ip'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_ip_list_2_isys_net_dns_domain',
                            'LEFT',
                            'isys_catg_ip_list__id',
                            'isys_catg_ip_list_2_isys_net_dns_domain',
                            'ip',
                            'ip2dns',
                            'ip2dns'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_net_dns_domain',
                            'LEFT',
                            'isys_net_dns_domain__id',
                            'isys_net_dns_domain__id',
                            'ip2dns',
                            'dns',
                            'dns'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__CATP__IP__SEARCH_DOMAIN',
                    C__PROPERTY__UI__PARAMS  => [
                        'type'           => 'f_popup',
                        'p_strPopupType' => 'dialog_plus',
                        'p_strTable'     => 'isys_net_dns_domain',
                        'placeholder'    => isys_application::instance()->container->get('language')
                            ->get('LC__UNIVERSAL__CHOOSEN_PLACEHOLDER'),
                        'emptyMessage'   => isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS__NET__NO_DNS_DOMAINS_FOUND'),
                        'p_onComplete'   => "idoit.callbackManager.triggerCallback('cmdb-catg-ip-dns_domain-update', selected);",
                        'multiselect'    => true
                        //'p_arData'       => new isys_callback(array('isys_cmdb_dao_category_g_ip', 'callback_property_dns_domain')),
                        //'data'           => new isys_callback(array('isys_cmdb_dao_category_s_net', 'callback_property_dns_domain'))
                    ],
                    C__PROPERTY__UI__DEFAULT => null
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT     => true,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__SEARCH     => true,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => true
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__MANDATORY  => false,
                    C__PROPERTY__CHECK__VALIDATION => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_multiselect'
                    ]
                ]
            ]),
            'use_standard_gateway'  => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__IP__DEFAULT_GATEWAY_FOR_THE_NET',
                    C__PROPERTY__INFO__DESCRIPTION => 'Default gateway for the net'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_ip_list__id',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'gateway',
                    C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_cats_net_list__isys_catg_ip_list__id > 0 THEN \'LC__UNIVERSAL__YES\' ELSE \'LC__UNIVERSAL__NO\' END)
                                 FROM isys_catg_ip_list
                                LEFT JOIN isys_cats_net_list ON isys_cats_net_list__isys_catg_ip_list__id = isys_catg_ip_list__id',
                        'isys_catg_ip_list',
                        'isys_catg_ip_list__id',
                        'isys_catg_ip_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_ip_list__id'])
                    ),
                    C__PROPERTY__DATA__JOIN        => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_ip_list', 'LEFT', 'isys_catg_ip_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_net_list',
                            'LEFT',
                            'isys_catg_ip_list',
                            'isys_cats_net_list__isys_catg_ip_list__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__CATG__IP__GW__CHECK',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_arData'        => get_smarty_arr_YES_NO(),
                        'p_strSelectedID' => new isys_callback([
                            'isys_cmdb_dao_category_g_ip',
                            'callback_property_use_standard_gateway'
                        ]),
                        'p_bDbFieldNN'    => 1
                    ],
                    C__PROPERTY__UI__DEFAULT => 0
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false,
                    C__PROPERTY__PROVIDES__IMPORT => false,
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_yes_or_no'
                    ]
                ]
            ]),
            'assigned_port'         => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__IP__ASSIGNED_PORT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned port'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_ip_list__isys_catg_port_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_port_list',
                        'isys_catg_port_list__id'
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__IP__ASSIGNED_PORTS',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_ip',
                            'callback_property_ports'
                        ]),
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_reference_value'
                    ]
                ]
            ]),
            'assigned_logical_port' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__IP__ASSIGNED_PORT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned port'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_ip_list__isys_catg_log_port_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_log_port_list',
                        'isys_catg_log_port_list__id'
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__IP__ASSIGNED_PORTS',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_ip',
                            'callback_property_ports'
                        ]),
                    ]
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__VALIDATION => [
                        FILTER_CALLBACK,
                        [
                            'options' => [
                                'isys_helper',
                                'filter_combined_dialog'
                            ]
                        ]
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__VIRTUAL   => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_reference_value'
                    ]
                ]
            ]),
            'all_ips'               => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER_SERVICE__HOST_ADDRESSES',
                    C__PROPERTY__INFO__DESCRIPTION => 'All IP addresses'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_net_ip_addresses_list__title
                              FROM isys_catg_ip_list
                                INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id',
                        'isys_catg_ip_list',
                        'isys_catg_ip_list__id',
                        'isys_catg_ip_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_ip_list__isys_obj__id']),
                        '',
                        isys_tenantsettings::get('cmdb.limits.ip-lists', 5)
                    ),
                        C__PROPERTY__DATA__SORT => 'SELECT isys_cats_net_ip_addresses_list__ip_address_long FROM isys_catg_ip_list
                                INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
                                WHERE isys_catg_ip_list__isys_obj__id = obj_main.isys_obj__id LIMIT 1',
                        C__PROPERTY__DATA__JOIN   => [
                            idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_ip_list', 'LEFT', 'isys_catg_ip_list__isys_obj__id', 'isys_obj__id'),
                            idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                                'isys_cats_net_ip_addresses_list',
                                'LEFT',
                                'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
                                'isys_cats_net_ip_addresses_list__id'
                            )
                        ],
                        C__PROPERTY__DATA__INDEX  => true
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__VIRTUAL    => true,
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__LIST       => true,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__IMPORT     => false,
                        C__PROPERTY__PROVIDES__EXPORT     => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false
                    ]
                ]),
            'primary_fqdn'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATP__IP__HOSTNAME_FQDN',
                    C__PROPERTY__INFO__DESCRIPTION => 'Hostname (FQDN)'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_ip_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_catg_ip_list__hostname, ".", isys_catg_ip_list__domain)
                              FROM isys_catg_ip_list',
                        'isys_catg_ip_list',
                        'isys_catg_ip_list__id',
                        'isys_catg_ip_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory(['isys_catg_ip_list__hostname != ""']),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_ip_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => true,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ],
                C__PROPERTY__FORMAT   =>[
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_global_ip_export_helper',
                        'exportFqdn'
                    ]
                ]
            ]),
            'aliases'               => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__IP__ALIASES',
                    C__PROPERTY__INFO__DESCRIPTION => 'Aliases',
                    // @see  API-36
                    C__PROPERTY__INFO__TYPE        => C__PROPERTY__INFO__TYPE__N2M
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_ip_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_hostaddress_pairs__hostname, ".", isys_hostaddress_pairs__domain)
                              FROM isys_catg_ip_list
                              INNER JOIN isys_hostaddress_pairs ON isys_hostaddress_pairs__isys_catg_ip_list__id = isys_catg_ip_list__id',
                        'isys_catg_ip_list',
                        'isys_catg_ip_list__id',
                        'isys_catg_ip_list__isys_obj__id',
                        '',
                        'alias',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory(['isys_hostaddress_pairs__isys_catg_ip_list__id = isys_catg_ip_list__id']),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_hostaddress_pairs__isys_catg_ip_list__id'])
                    )
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__IMPORT     => true,
                    C__PROPERTY__PROVIDES__EXPORT     => true,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_global_ip_export_helper',
                        'exportHostaddressAliases'
                    ]
                ]
            ]),
            'description'           => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Categories description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_ip_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_ip_list__description FROM isys_catg_ip_list',
                        'isys_catg_ip_list',
                        'isys_catg_ip_list__id',
                        'isys_catg_ip_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_ip_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__IP', 'C__CATG__IP')
                ]
            ])
        ];
    }

    /**
     * "Rank Record" method is used to check the "unique ip-address" setting and act accordingly (only "notify" for now).
     *
     * @param  integer $entryId
     * @param  integer $direction
     * @param  string  $table
     * @param  mixed   $checkMethod
     * @param  boolean $purge
     *
     * @see     ID-4972  Notify the user if recycled records duplicate IP addresses.
     * @return  bool
     * @throws  Exception
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_dao
     * @throws  isys_exception_database
     * @throws  isys_exception_general
     */
    public function rank_record($entryId, $direction, $table, $checkMethod = null, $purge = false)
    {
        // @see  ID-4972  Notify the user if a recycled record duplicates IP addresses.
        if (isys_tenantsettings::get('cmdb.unique.ip-address', 1) && $direction == C__CMDB__RANK__DIRECTION_RECYCLE) {
            $row = $this->get_data($entryId)
                ->get_row();

            if ($this->in_use($row['isys_cats_net_ip_addresses_list__isys_obj__id'], $row['isys_cats_net_ip_addresses_list__title'], $entryId)) {
                $message = _L('LC__CATG__IP__UNIQUE_IP_WARNING_RECYCLE', [$row['isys_cats_net_ip_addresses_list__title'], $row['isys_obj__title'], $row['isys_obj__id']]);

                isys_notify::warning($message, ['sticky' => true]);
            }
        }

        return parent::rank_record($entryId, $direction, $table, $checkMethod, $purge);
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param  array   $p_category_data Values of category data to be saved.
     * @param  integer $p_object_id     Current object identifier (from database)
     * @param  integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     * @throws Exception
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            // Find out the correct ip address.
            if (isset($p_category_data['properties']['ipv4_address']['ref_title'])) {
                $l_address = $p_category_data['properties']['ipv4_address']['ref_title'];
            } elseif (isset($p_category_data['properties']['ipv4_address'][C__DATA__VALUE])) {
                $l_address = $p_category_data['properties']['ipv4_address'][C__DATA__VALUE];
            } elseif (isset($p_category_data['properties']['ipv6_address']['ref_title'])) {
                $l_address = $p_category_data['properties']['ipv6_address']['ref_title'];
            } elseif (isset($p_category_data['properties']['ipv6_address'][C__DATA__VALUE])) {
                $l_address = $p_category_data['properties']['ipv6_address'][C__DATA__VALUE];
            } else {
                $l_address = '';
            }

            // Setting defaults.
            if (isset($p_category_data['properties']['net_type'][C__DATA__VALUE])) {
                if ($p_category_data['properties']['net_type'][C__DATA__VALUE] == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
                    if (isset($p_category_data['properties']['ipv4_assignment'][C__DATA__VALUE])) {
                        $p_category_data['properties']['ip_assignment'][C__DATA__VALUE] = $p_category_data['properties']['ipv4_assignment'][C__DATA__VALUE];
                    }
                } elseif ($p_category_data['properties']['net_type'][C__DATA__VALUE] == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
                    if (isset($p_category_data['properties']['ipv6_assignment'][C__DATA__VALUE])) {
                        $p_category_data['properties']['ip_assignment'][C__DATA__VALUE] = $p_category_data['properties']['ipv6_assignment'][C__DATA__VALUE];
                    }
                }
            }

            // If net_type still is not set, set it to the IPV4 default.
            if (!isset($p_category_data['properties']['net_type'][C__DATA__VALUE])) {
                $p_category_data['properties']['net_type'][C__DATA__VALUE] = defined_or_default('C__CATS_NET_TYPE__IPV4');
            }

            $l_net_obj = isset($p_category_data['properties']['net'][C__DATA__VALUE]) ? $p_category_data['properties']['net'][C__DATA__VALUE] : null;
            $l_ip_assignment = isset($p_category_data['properties']['ip_assignment'][C__DATA__VALUE]) ? $p_category_data['properties']['ip_assignment'][C__DATA__VALUE] : defined_or_default('C__CATP__IP__ASSIGN__STATIC');

            if (defined('C__CATS_NET_TYPE__IPV6') && $p_category_data['properties']['net_type'][C__DATA__VALUE] === C__CATS_NET_TYPE__IPV6) {
                $l_net_type = C__CATS_NET_TYPE__IPV6;
                if (empty($l_net_obj)) {
                    $l_net_obj = defined_or_default('C__OBJ__NET_GLOBAL_IPV6');
                }
            } elseif (defined('C__CATS_NET_TYPE__IPV4')) {
                $l_net_type = C__CATS_NET_TYPE__IPV4;
                if (empty($l_net_obj)) {
                    $l_net_obj = defined_or_default('C__OBJ__NET_GLOBAL_IPV4');
                }
            }

            if ($l_address != '' && $l_net_obj > 0) {
                if (!$this->is_unique_ip($p_object_id, $l_address, $l_net_obj) && isys_tenantsettings::get('cmdb.unique.ip-address')) {
                    if (isys_import_handler_cmdb::get_overwrite_ip_conflicts()) {
                        // reassign local hostaddress to global net
                        $this->reassign_ip(
                            $p_object_id,
                            $l_address,
                            $l_net_type,
                            (($l_net_type == defined_or_default('C__CATS_NET_TYPE__IPV4')) ? defined_or_default('C__OBJ__NET_GLOBAL_IPV4') : defined_or_default('C__OBJ__NET_GLOBAL_IPV6'))
                        );
                    } else {
                        if ($l_net_type == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
                            $l_address = $this->get_free_ip($l_net_obj, $l_ip_assignment);
                        } elseif ($l_net_type == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
                            $l_address = $this->get_free_ipv6($l_net_obj);
                        }
                    }
                }
            }

            // API-Problem.
            if (is_numeric($l_address) && $p_status == isys_import_handler_cmdb::C__UPDATE) {
                $l_row = $this->get_ip_by_id($p_category_data['data_id']);
                $l_address = $l_row['isys_cats_net_ip_addresses_list__title'];
            }

            if (isset($p_category_data['properties']['dns_domain'][C__DATA__VALUE])) {
                // Convert comma separated values into array
                if (is_string($p_category_data['properties']['dns_domain'][C__DATA__VALUE])) {
                    $p_category_data['properties']['dns_domain'][C__DATA__VALUE] = explode(',', $p_category_data['properties']['dns_domain'][C__DATA__VALUE]);
                }

                if (is_array($p_category_data['properties']['dns_domain'][C__DATA__VALUE])) {
                    $l_arr_data = $p_category_data['properties']['dns_domain'][C__DATA__VALUE];
                    unset($p_category_data['properties']['dns_domain'][C__DATA__VALUE]);
                    foreach ($l_arr_data as $l_data) {
                        if (is_array($l_data)) {
                            if (isset($l_data['id'])) {
                                $p_category_data['properties']['dns_domain'][C__DATA__VALUE][] = $l_data['id'];
                            } elseif (isset($l_data['title'])) {
                                $p_category_data['properties']['dns_domain'][C__DATA__VALUE][] = $l_data['title'];
                            }
                        } else {
                            $p_category_data['properties']['dns_domain'][C__DATA__VALUE][] = $l_data;
                        }
                    }
                } elseif (is_numeric($p_category_data['properties']['dns_domain'][C__DATA__VALUE])) {
                    $l_dns_domain_id = $p_category_data['properties']['dns_domain'][C__DATA__VALUE];
                    unset($p_category_data['properties']['dns_domain'][C__DATA__VALUE]);
                    $p_category_data['properties']['dns_domain'][C__DATA__VALUE][] = $l_dns_domain_id;
                }
            }

            // Translate dns domains to IDs
            if (isset($p_category_data['properties']['dns_domain'][C__DATA__VALUE]) && is_array($p_category_data['properties']['dns_domain'][C__DATA__VALUE])) {
                $l_dialog_admin = new isys_cmdb_dao_dialog_admin($this->get_database_component());
                foreach ($p_category_data['properties']['dns_domain'][C__DATA__VALUE] as $l_index => $l_dns_domain) {
                    if (is_array($l_dns_domain)) {
                        if (isset($l_dns_domain['id']) && is_numeric($l_dns_domain['id'])) {
                            $l_dns_domain = $l_dns_domain['id'];
                        } elseif (isset($l_dns_domain['title'])) {
                            $l_dns_domain = $l_dns_domain['title'];
                        }
                    }

                    if (!is_numeric($l_dns_domain)) {
                        // Create/Retrieve dns domain
                        $p_category_data['properties']['dns_domain'][C__DATA__VALUE][$l_index] = $l_dialog_admin->get_id('isys_net_dns_domain', $l_dns_domain);
                    } else {
                        // Check for existent id first
                        if (!$l_dialog_admin->get_data('isys_net_dns_domain', $l_dns_domain)
                            ->num_rows()) {
                            unset($p_category_data['properties']['dns_domain'][C__DATA__VALUE][$l_index]);
                        }
                    }
                }
            }

            if (isset($p_category_data['properties']['dns_server'][C__DATA__VALUE]) &&
                isys_format_json::is_json_array($p_category_data['properties']['dns_server'][C__DATA__VALUE])) {
                $p_category_data['properties']['dns_server'][C__DATA__VALUE] =
                    isys_format_json::decode($p_category_data['properties']['dns_server'][C__DATA__VALUE]);
            }

            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0) {
                        return $this->create(
                            $p_object_id,
                            $p_category_data['properties']['hostname'][C__DATA__VALUE],
                            $l_ip_assignment,
                            $l_address,
                            $p_category_data['properties']['primary'][C__DATA__VALUE],
                            $p_category_data['properties']['use_standard_gateway'][C__DATA__VALUE],
                            $p_category_data['properties']['dns_server'][C__DATA__VALUE],
                            $p_category_data['properties']['dns_domain'][C__DATA__VALUE],
                            $p_category_data['properties']['active'][C__DATA__VALUE],
                            $p_category_data['properties']['net_type'][C__DATA__VALUE],
                            $l_net_obj,
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['assigned_port'][C__DATA__VALUE],
                            $p_category_data['properties']['assigned_logical_port'][C__DATA__VALUE],
                            $p_category_data['properties']['ipv6_scope'][C__DATA__VALUE],
                            $p_category_data['properties']['ipv6_assignment'][C__DATA__VALUE],
                            $p_category_data['properties']['zone'][C__DATA__VALUE],
                            $p_category_data['properties']['domain'][C__DATA__VALUE],
                            $p_category_data['properties']['aliases'][C__DATA__VALUE]
                        );
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $this->save(
                            $p_category_data['data_id'],
                            $p_category_data['properties']['hostname'][C__DATA__VALUE],
                            $l_ip_assignment,
                            $l_address,
                            $p_category_data['properties']['primary'][C__DATA__VALUE],
                            $p_category_data['properties']['use_standard_gateway'][C__DATA__VALUE],
                            $p_category_data['properties']['dns_server'][C__DATA__VALUE],
                            $p_category_data['properties']['dns_domain'][C__DATA__VALUE],
                            $p_category_data['properties']['active'][C__DATA__VALUE],
                            $p_category_data['properties']['net_type'][C__DATA__VALUE],
                            $l_net_obj,
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['assigned_port'][C__DATA__VALUE],
                            $p_category_data['properties']['assigned_logical_port'][C__DATA__VALUE],
                            $p_category_data['properties']['ipv6_assignment'][C__DATA__VALUE],
                            $p_category_data['properties']['ipv6_scope'][C__DATA__VALUE],
                            $p_category_data['properties']['zone'][C__DATA__VALUE],
                            $p_category_data['properties']['domain'][C__DATA__VALUE],
                            $p_category_data['properties']['aliases'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * Validate method.
     *
     * @param  array $p_data
     * @param  mixed $p_prepend_table_field
     *
     * @return boolean
     * @throws Exception
     * @throws isys_exception_database
     */
    public function validate(array $p_data = [], $p_prepend_table_field = false)
    {
        // This is used for the overview page.
        if (!$this->get_list_id() && isset($_POST['C__CATG__IP__ID']) && $_POST['C__CATG__IP__ID'] > 0) {
            $this->set_list_id($_POST['C__CATG__IP__ID']);
        }

        $l_errors = parent::validate($p_data);

        if (!is_array($l_errors)) {
            $l_errors = [];
        }

        if ($l_errors === true) {
            $l_errors = [];
        }

        // Unset hostaddress because either ipv4_address or ipv6_address will be used
        if (is_array($l_errors) && isset($l_errors['hostaddress'])) {
            unset($l_errors['hostaddress']);
        }

        // Specific rules for net objects.
        if (isset($p_data['net']) && $p_data['net'] !== '' && is_numeric($p_data['net'])) {
            $this->validateNetObject($p_data['net'], $l_errors);
        }

        // Specific rules for IPv4 addresses.
        if (isset($p_data['ipv4_address']) && $p_data['ipv4_address'] !== '') {
            $this->validateIpAddress('ipv4_address', $p_data, $l_errors);
        }

        // Specific rules for IPv6 addresses.
        if (isset($p_data['ipv6_address']) && $p_data['ipv6_address'] !== '') {
            $this->validateIpAddress('ipv6_address', $p_data, $l_errors);
        }

        if (isset($p_data['net_type']) && !empty($p_data['net_type'])) {
            $netType = (is_array($p_data['net_type']) && isset($p_data['net_type']['value']) ? $p_data['net_type']['value'] : $p_data['net_type']);

            if ($netType == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
                unset($l_errors['ipv4_address']);
            } else {
                unset($l_errors['ipv6_address']);
            }
        }

        if (count($l_errors)) {
            return $l_errors;
        }

        return true;
    }

    /**
     * Validating net object id
     *
     * @param int   $netObjectId
     * @param array $errors
     *
     * @throws isys_exception_database
     */
    public function validateNetObject($netObjectId, &$errors)
    {
        // Get object data
        $resource = $this->get_object($netObjectId);

        // Check for results
        if ($resource->num_rows()) {
            // Get object type id
            $objectTypeId = $resource->get_row_value('isys_obj__isys_obj_type__id');

            // Initialize dao
            $objectTypeDao = new isys_cmdb_dao_object_type(isys_application::instance()->container->get('database'));

            // Check whether object type has category or not
            if (!$objectTypeDao->has_cat($objectTypeId, ['C__CATS__NET', 'C__CATS__NET_IP_ADDRESSES'])) {
                $errors['net'] = isys_application::instance()->container->get('language')->get('LC__CATG__IP__UNIQUE_IP_NET_WARNING');
            }
        } else {
            // Object could not be found by the provided objectId
            $errors['net'] = isys_application::instance()->container->get('language')->get('LC__CATG__IP__UNIQUE_IP_NET_OBJECT_NOT_FOUND');
        }
    }

    /**
     * Wrapper function which validates ipv4 and ipv6 address
     *
     * @param  string $ipType
     * @param  array  $ipData
     * @param  array  $errors
     *
     * @throws Exception
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function validateIpAddress($ipType, &$ipData, &$errors)
    {
        switch ($ipType) {
            case 'ipv6_address':
                unset($ipData['ipv4_address'], $errors['ipv4_address']);
                $globalNetObject = defined_or_default('C__OBJ__NET_GLOBAL_IPV6');
                $validateMethod = 'validate_ipv6';
                break;
            case 'ipv4_address':
            default:
                unset($ipData['ipv6_address'], $errors['ipv6_address']);
                $globalNetObject = defined_or_default('C__OBJ__NET_GLOBAL_IPV4');
                $validateMethod = 'validate_ip';
                break;
        }

        $ipAddress = (is_array($ipData[$ipType]) && isset($ipData[$ipType]['value']) ? $ipData[$ipType]['value'] : $ipData[$ipType]);

        if (!isset($ipData['net'])) {
            $netObject = $globalNetObject;
        } else {
            $netObject = (is_array($ipData['net']) && isset($ipData['net']['value']) ? $ipData['net']['value'] : $ipData['net']);
        }

        if (!empty($ipAddress) && $netObject !== null) {
            if (!Ip::$validateMethod($ipAddress)) {
                $errors[$ipType] = isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__CATS__NET_IP_ADDRESSES__IP_INVALID');
            } elseif (isys_tenantsettings::get('cmdb.unique.ip-address', 1)) {
                $l_catLevel = $this->in_use($netObject, $ipAddress, $this->get_list_id());

                if ($l_catLevel) {
                    $l_row = $this->get_data($l_catLevel)
                        ->get_row();

                    $errors[$ipType] = isys_application::instance()->container->get('language')
                        ->get('LC__CATG__IP__UNIQUE_IP_WARNING', [$l_row['isys_obj__title'], $l_row['isys_obj__id']]);
                }
            }

            // @see  ID-6570  Fixing the validation for IPv4 and IPv6.
            if (!isset($errors[$ipType])) {
                $l_properties = $this->get_properties(C__PROPERTY__WITH__VALIDATION);
                $ipCheck = $l_properties[$ipType][C__PROPERTY__CHECK];

                $sqlConditions = null;
                $sqlBase = 'SELECT isys_catg_ip_list__id AS id, isys_obj__id AS objId, isys_obj__title AS objTitle, isys_obj_type__title AS objTypeTitle
                        FROM isys_cats_net_ip_addresses_list 
                        INNER JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id 
                        INNER JOIN isys_obj ON isys_obj__id = isys_catg_ip_list__isys_obj__id
                        INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
                        WHERE isys_cats_net_ip_addresses_list__title = ' . $this->convert_sql_text($ipAddress) . '
                        AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
                        AND isys_catg_ip_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
                        AND isys_cats_net_ip_addresses_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' ';

                if (isset($ipCheck['unique_global']) && $ipCheck['unique_global']) {
                    $message = 'LC__SETTINGS__CMDB__VALIDATION_MESSAGE__UNIQUE_GLOBAL';

                    $sqlConditions = ';';
                } elseif (isset($ipCheck['unique_objtype']) && $ipCheck['unique_objtype'] && $this->m_object_type_id) {
                    $message = 'LC__SETTINGS__CMDB__VALIDATION_MESSAGE__UNIQUE_OBJTYPE';

                    $sqlConditions = 'AND isys_obj__isys_obj_type__id = ' . $this->convert_sql_id($this->m_object_type_id) . ';';
                } elseif (isset($ipCheck['unique_obj']) && $ipCheck['unique_obj'] && $this->m_object_id) {
                    $message = 'LC__SETTINGS__CMDB__VALIDATION_MESSAGE__UNIQUE_OBJ';

                    $sqlConditions = 'AND isys_catg_ip_list__isys_obj__id = ' . $this->convert_sql_id($this->m_object_id) . ';';
                }

                if ($sqlConditions) {
                    $result = $this->retrieve($sqlBase . $sqlConditions);

                    if ($result !== false && is_countable($result) && count($result)) {
                        $language = isys_application::instance()->container->get('language');
                        $objects = [];

                        while ($objectRow = $result->get_row()) {
                            // This is necessary to not count the current table entry.
                            if ($l_properties[$ipType][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJ] && $this->get_list_id() > 0 && $objectRow['id'] == $this->get_list_id()) {
                                continue;
                            }

                            // Only add the object to the list if it's not the own object OR if the address has to be unique inside this object.
                            if ($objectRow['objId'] != $this->m_object_id || $l_properties[$ipType][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJ]) {
                                $objects[] = '<span>' . $language->get($objectRow['objTypeTitle']) . ' » ' . $objectRow['objTitle'] . '</span>';
                            }
                        }

                        // Remove duplicates
                        $objects = array_unique($objects);

                        if ($objectCount = count($objects)) {
                            if ($objectCount > self::UNIQUE_VALIDATION_OBJECT_COUNT) {
                                $objects = array_slice($objects, 0, self::UNIQUE_VALIDATION_OBJECT_COUNT);
                                $objects[] = $language->get('LC__SETTINGS__CMDB__VALIDATION_MESSAGE__UNIQUE_AND_MORE', ($objectCount - self::UNIQUE_VALIDATION_OBJECT_COUNT));
                            }

                            $errors[$ipType] = $language->get($message) . '<ul class="m0 mt10 list-style-none"><li>' . implode('</li><li>', $objects) . '</li></ul>';
                        }
                    }
                }
            }
        }
    }
}
