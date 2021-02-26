<?php

use idoit\Component\Helper\Ip;

/**
 * i-doit
 *
 * JDisc network DAO
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-9
 */
class isys_jdisc_dao_network extends isys_jdisc_dao_data
{
    const C_FILTER_JDISC__LOOPBACK = 'softwareLoopback';
    const C_FILTER_JDISC__VIRTUAL = 'propVirtual';
    /**
     * Network Port map for faster existing checks
     *
     * @var isys_array
     */
    public static $m_port_map = null;

    /**
     * A simple counter variable for the "cable-id".
     *
     * @var  integer
     */
    private static $m_cable_count = 0;

    /**
     * Defines the current vlan configuration of JDisc
     *
     * @var
     */
    private static $m_vlanconfiguration = false;

    protected $m_allowed_imports = [
        'interfaces'  => false,
        'vrrpcluster' => false
    ];

    /**
     * @var isys_array
     */
    protected $m_connector_types = null;

    /**
     * Instance of the "isys_cmdb_dao_jdisc".
     *
     * @var  isys_cmdb_dao_jdisc
     */
    protected $m_dao = null;

    /**
     * Holds dialog data for ports
     *
     * @var array
     */
    protected $m_dialog_data = [
        'port_type'         => [],
        'port_mode'         => [],
        'port_negotiation'  => [],
        'port_duplex'       => [],
        'dns_domain'        => [],
        'port_type_logical' => [],
        'port_speed'        => []
    ];

    /**
     * Determines if interfaces should be imported in the category or into chassis or in both
     * 0 = interface category
     * 1 = chassis category
     * 2 = interface and chassis category
     *
     * @var int
     */
    protected $m_import_type_interfaces = 0;

    /**
     * Defines if vlans should be imported
     *
     * @var bool
     */
    protected $m_import_vlans = true;

    /**
     * This array has all virtual interface types as ids
     *
     * @var array
     */
    protected $m_interface_types = [
        'virtual'      => [
            'title'   => null,
            'content' => []
        ],
        'ethernet'     => [
            'title'   => 'Ethernet',
            'content' => []
        ],
        'isdn'         => [
            'title'   => 'ISDN',
            'content' => []
        ],
        'wan'          => [
            'title'   => 'WAN',
            'content' => []
        ],
        'wlan'         => [
            'title'   => 'WLAN',
            'content' => []
        ],
        'vlan'         => [
            'title'   => 'VLAN',
            'content' => [],
            'dialog'  => false
        ],
        'loopback'     => [
            'title'   => 'LOOPBACK',
            'content' => [],
            'dialog'  => false
        ],
        'tunnel'       => [
            'title'   => 'TUNNEL',
            'content' => [],
            'dialog'  => false
        ],
        'bridge'       => [
            'title'   => 'BRIDGE',
            'content' => [],
            'dialog'  => false
        ],
        'fibreChannel' => [
            'title'   => 'FIBRECHANNEL',
            'content' => []
        ]
    ];

    /**
     * Array which holds the "static" IP assignment for IPv4 and IPv6.
     *
     * @var  array
     */
    protected $m_ip_assignments = [];

    /**
     * This array will cache NOT found layer3 nets, so we can save database resources.
     *
     * @var  array
     */
    protected $m_missing_net_cache = [];

    /**
     * This array will cache found layer3 nets, so we can save database resources.
     *
     * @var  array
     */
    protected $m_net_cache = [];

    /**
     * Array which holds the various net types.
     *
     * @var  array
     */
    protected $m_net_types = [];

    /**
     * Holds all network interfaces
     *
     * @var array
     */
    protected $m_network_interfaces = [];

    /**
     * Holds all network interfaces connections
     *
     * @var array
     */
    protected $m_network_interfaces_connection = [];

    /**
     * Contains raw port filter
     *
     * @var array
     */
    protected $m_port_filter = [];

    /**
     * Contains raw port filter type
     *
     * @var array
     */
    protected $m_port_filter_import_type = [];

    /**
     * Holds all VRRP-Addresses
     *
     * @var
     */
    protected $m_vrrp_addresses = [];

    /**
     * Helper array which holds all fc ports
     *
     * @var array
     */
    private $m_fc_port_array = [];

    /**
     * Current Object title
     *
     * @var
     */
    private $m_idoit_obj_name;

    /**
     * Current Object type
     *
     * @var
     */
    private $m_idoit_obj_type;

    /**
     * Helper array which holds all layer2 nets with values as vlanid
     *
     * @var isys_array
     */
    private $m_layer2_net_array = null;

    /**
     * Helper array which holds all layer2 nets with values as vlanid::object_title
     *
     * @var isys_array
     */
    private $m_layer2_net_array2 = null;

    /**
     * Helper array which holds all logical ports
     *
     * @var array
     */
    private $m_logical_port_array = [];

    /**
     * Current os id
     *
     * @var
     */
    private $m_os_id;

    /**
     * Current os version (title)
     *
     * @var
     */
    private $m_osversion;

    /**
     * Helper array which holds all ports
     *
     * @var array
     */
    private $m_port_array = null;

    /**
     * Contains all physical ports of the current device which has been filtered and created to be logical ports
     *
     * @var array
     */
    private $m_port_filter_logical = [];

    /**
     * Contains all logical ports of the current device which has been filtered and created to be physical ports
     *
     * @var array
     */
    private $m_port_filter_physical = [];

    /**
     * Contains filtered port filter
     *
     * @var array
     */
    private $m_port_filtered_filter = [];

    /**
     * Contains filtered port filter type
     *
     * @var array
     */
    private $m_port_filtered_filter_type = [];

    /**
     * Current device type id
     *
     * @var
     */
    private $m_type_id;

    /**
     * Current device type title
     *
     * @var
     */
    private $m_type_title;

    /**
     * @var bool
     */
    private $endPointImport = false;

    /**
     * @param false $endPointImport
     */
    public function setEndpointImport($endPointImport = false)
    {
        $this->endPointImport = $endPointImport;
    }

    /**
     * @return mixed
     */
    public function getIdoitObjName()
    {
        return $this->m_idoit_obj_name;
    }

    /**
     * @return mixed
     */
    public function getIdoitObjType()
    {
        return $this->m_idoit_obj_type;
    }

    /**
     * @return array
     */
    public function getInterfaceTypes()
    {
        return $this->m_interface_types;
    }

    /**
     * Gets import type for interfaces, chassis
     *
     * @return int
     */
    public function get_import_type_interfaces()
    {
        return $this->m_import_type_interfaces;
    }

    /**
     * Sets port filter
     *
     * @param array $p_array
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_port_filter(array $p_array)
    {
        $this->m_port_filter = $p_array;
    }

    /**
     * Gets port filter
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_port_filter()
    {
        return $this->m_port_filter;
    }

    /**
     * Sets port import type filter
     *
     * @param array $p_array
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_port_filter_import_type(array $p_array)
    {
        $this->m_port_filter_import_type = $p_array;
    }

    /**
     * Gets port import filter type
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_port_filter_import_type()
    {
        return $this->m_port_filter_import_type;
    }

    /**
     * @param null|string $key
     *
     * @return array
     */
    public function getDialogData($key = null)
    {
        return ($key !== null ? $this->m_dialog_data[$key]: $this->m_dialog_data);
    }

    /**
     * @param string $key
     * @param string $newType
     * @param int|array $data
     */
    public function addDialogData($key, $newType, $data)
    {
        $this->m_dialog_data[$key][$newType] = $data;
    }

    /**
     * Resets ports filters and sets required member variables for the filter
     *
     * @param $p_type_id
     * @param $p_type_title
     * @param $p_os_id
     * @param $p_osversion
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function reset_filtered_ports_logical_ports($p_type_id, $p_type_title, $p_os_id, $p_osversion, $p_idoit_obj_type, $p_idoit_obj_name)
    {
        unset($this->m_port_filter_logical);
        unset($this->m_port_filter_physical);
        unset($this->m_port_filtered_filter);
        $this->m_type_id = $p_type_id;
        $this->m_type_title = $p_type_title;
        $this->m_os_id = $p_os_id;
        $this->m_osversion = $p_osversion;
        $this->m_idoit_obj_type = $p_idoit_obj_type;
        $this->m_idoit_obj_name = $p_idoit_obj_name;
    }

    /**
     * Gets ports which will be imported as logical ports
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_ports_filterd_logical()
    {
        return $this->m_port_filter_logical;
    }

    /**
     * Gets ports which will be imported as physical ports
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_ports_filterd_physical()
    {
        return $this->m_port_filter_physical;
    }

    /**
     * Method for retrieving the imported port data.
     *
     * @return  array
     */
    public function get_ports_array()
    {
        return $this->m_port_array;
    }

    /**
     * @param $key
     * @param $data
     */
    public function addToPorts($key, $data)
    {
        $this->m_port_array[$key] = $data;
    }

    /**
     * Method for retrieving the imported logical port data.
     *
     * @return  array
     */
    public function get_logical_ports_array()
    {
        return $this->m_logical_port_array;
    }

    /**
     * Method for counting all layer3-entries in JDisc.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function count_networks()
    {
        $l_query = $this->fetch('SELECT COUNT(*) AS c FROM ip4network;');
        $l_ipv4_count = $this->m_pdo->fetch_row_assoc($l_query);
        $this->m_pdo->free_result($l_query);

        $l_query = $this->fetch('SELECT COUNT(*) AS c FROM ip6network;');
        $l_ipv6_count = $this->m_pdo->fetch_row_assoc($l_query);
        $this->m_pdo->free_result($l_query);

        return $l_ipv4_count['c'] + $l_ipv6_count['c'];
    }

    /**
     * Fetches all virtual interface types from JDisc.
     *
     * @param   string $p_type
     *
     * @return  array
     */
    public function get_interface_types($p_type)
    {
        $l_result = [];
        $l_res = $this->fetch("SELECT id FROM interfacetypelookup WHERE name LIKE '%" . $p_type . "%' OR name LIKE '%" . ucfirst($p_type) . "%' OR name LIKE '%" .
            strtoupper($p_type) . "%'");

        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
            $l_result[] = array_pop($l_row);
        }
        $this->m_pdo->free_result($l_res);

        return $l_result;
    }

    /**
     * Set Flag if vlans should be imported
     *
     * @param $p_value
     */
    public function set_import_vlans($p_value)
    {
        $this->m_import_vlans = (bool)$p_value;
    }

    /**
     * Method for receiving the layer3 nets, assigned to a given device.
     * This method implements more logic than usual - Because we want to create layer3 nets with specific information.
     *
     * @param   integer $p_id
     * @param   boolean $p_raw
     * @param   boolean $p_all_layer3 If set to true we create objects for every layer3-net JDisc could find.
     * @param   array $p_filterAddresses
     * @param   integer $p_dhcpAddresses
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_layer3_by_device($p_id, $p_raw = false, $p_all_layer3 = false, &$p_connections = [], $p_filterAddresses = null, $p_dhcpAddresses = 0)
    {
        if (!defined('C__CATS_NET_TYPE__IPV4') || !defined('C__CATS_NET_TYPE__IPV6')) {
            return [];
        }
        $l_return = [];
        $l_already_imported = [];

        $useIpv4 = true;
        $useIpv6 = true;
        $useLoopback = true;
        $useVirtual = true;

        if (is_array($p_filterAddresses) && count($p_filterAddresses) > 0) {
            $useIpv4 = false;
            if (in_array(isys_jdisc_dao::C_FILTER__IPV4, $p_filterAddresses)) {
                $useIpv4 = true;
            }
            $useIpv6 = false;
            if (in_array(isys_jdisc_dao::C_FILTER__IPV6, $p_filterAddresses)) {
                $useIpv6 = true;
            }
            $useLoopback = false;
            if (in_array(isys_jdisc_dao::C_FILTER__LOOPBACK, $p_filterAddresses)) {
                $useLoopback = true;
            }
            $useVirtual = false;
            if (in_array(isys_jdisc_dao::C_FILTER__VIRTUAL, $p_filterAddresses)) {
                $useVirtual = true;
            }
        }

        /**
         * @var  $l_dao      isys_cmdb_dao_jdisc
         * @var  $l_net_dao  isys_cmdb_dao_category_s_net
         */
        $l_dao = isys_cmdb_dao_jdisc::instance($this->m_db);
        $l_net_dao = isys_cmdb_dao_category_s_net::instance($this->m_db);

        if ($useIpv4 === true) {
            // At first we prepare the SQL to receive IPv4 layer-3 nets.
            $l_sql = 'SELECT i4n.*, i4t.*, m.id AS portid, m.iftype FROM ip4transport AS i4t
                LEFT JOIN ip4network AS i4n ON i4t.ip4networkid = i4n.id
                LEFT JOIN device AS d ON i4t.deviceid = d.id
                LEFT JOIN mac AS m ON i4t.macid = m.id
                LEFT JOIN interfacetypelookup AS i on m.iftype = i.id
                WHERE d.id = ' . $this->convert_sql_id($p_id);

            if (($useLoopback === true) && ($useVirtual === false)) {
                $l_sql .= ' AND (i.name NOT LIKE ' . $this->convert_sql_text(self::C_FILTER_JDISC__VIRTUAL) . ')';
            } elseif (($useLoopback === false) && ($useVirtual === true)) {
                $l_sql .= ' AND (i.name NOT LIKE ' . $this->convert_sql_text(self::C_FILTER_JDISC__LOOPBACK) . ')';
            } elseif (($useLoopback === false) && ($useVirtual === false)) {
                $l_sql .= ' AND (i.name NOT LIKE ' . $this->convert_sql_text(self::C_FILTER_JDISC__LOOPBACK) .
                    ') AND (i.name NOT LIKE ' . $this->convert_sql_text(self::C_FILTER_JDISC__VIRTUAL) . ')';
            }

            if ($p_dhcpAddresses === 1) {
                $l_sql .= ' AND (m.dhcpenabled = true)';
            }

            $l_res = $this->fetch($l_sql);

            $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($l_res) . ' rows for IPv4');

            while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
                $l_dns_domain_id = null;
                $l_dns_domain = null;
                $l_tmp = null;
                $l_layer3 = false;
                $l_networkaddress = null;
                $l_subnetmask = null;
                $l_firstaddress = null;
                $l_lastaddress = null;

                $l_from_address = null;
                $l_to_address = null;
                $l_parent_network_address = null;

                if (!empty($l_row['fqdn'])) {
                    $l_fqdn_arr = explode('.', $l_row['fqdn']);
                    $l_fqdn_count = count($l_fqdn_arr);
                    if ($l_fqdn_count >= 3) {
                        for ($l_i = 1; $l_i < $l_fqdn_count; $l_i++) {
                            $l_dns_domain .= $l_fqdn_arr[$l_i] . '.';
                        }
                        $l_dns_domain = rtrim($l_dns_domain, '.');
                        $l_dns_domain_id = isys_import_handler_cmdb::check_dialog('isys_net_dns_domain', $l_dns_domain);
                    }
                }

                if (!empty($l_row['ip4networkid'])) {
                    $l_networkaddress = Ip::long2ip($l_row['networkaddress']);
                    $l_subnetmask = Ip::long2ip($l_row['subnetmask']);
                    $l_firstaddress = Ip::long2ip($l_row['firstaddress']);
                    $l_lastaddress = Ip::long2ip($l_row['lastaddress']);
                    $l_calculated_ranges = Ip::calc_ip_range($l_networkaddress, $l_subnetmask);
                    $l_firstaddress_idoit = $l_calculated_ranges['from'];
                    $l_lastaddress_idoit = $l_calculated_ranges['to'];
                } elseif (!empty($l_row['address']) && !empty($l_row['configuredsubnetmask'])) {
                    $l_subnetmask = Ip::long2ip($l_row['configuredsubnetmask']);
                    $l_networkaddress = Ip::validate_net_ip(Ip::long2ip($l_row['address']), $l_subnetmask, null, true);
                    $l_calculated_ranges = Ip::calc_ip_range($l_networkaddress, $l_subnetmask);
                    $l_firstaddress = $l_firstaddress_idoit = $l_calculated_ranges['from'];
                    $l_lastaddress = $l_lastaddress_idoit = $l_calculated_ranges['to'];
                }

                if ($l_networkaddress && $l_subnetmask && (($l_firstaddress && $l_lastaddress) || ($l_firstaddress_idoit && $l_lastaddress_idoit))) {
                    $l_tmp = C__CATS_NET_TYPE__IPV4 . '_' . $l_networkaddress . '_' . $l_subnetmask . '_' . $l_firstaddress_idoit . '_' . $l_lastaddress_idoit;
                    // Check with idoit ip range
                    $l_layer3 = $this->does_layer3_exist_in_idoit(C__CATS_NET_TYPE__IPV4, $l_networkaddress, $l_subnetmask, $l_firstaddress_idoit, $l_lastaddress_idoit);

                    $l_layer3_2 = false;
                    if (!$l_layer3) {
                        $l_tmp_2 = C__CATS_NET_TYPE__IPV4 . '_' . $l_networkaddress . '_' . $l_subnetmask . '_' . $l_firstaddress . '_' . $l_lastaddress;
                        // Check with jdisc ip range
                        $l_layer3_2 = $this->does_layer3_exist_in_idoit(C__CATS_NET_TYPE__IPV4, $l_networkaddress, $l_subnetmask, $l_firstaddress, $l_lastaddress);
                        if ($l_layer3_2) {
                            $l_tmp = $l_tmp_2;
                            $l_layer3 = $l_layer3_2;
                        }
                    }

                    if ((!$l_layer3 && !$l_layer3_2) || $l_layer3) {
                        $l_firstaddress = $l_firstaddress_idoit;
                        $l_lastaddress = $l_lastaddress_idoit;
                    }
                }

                if ($p_raw === true) {
                    $l_return[] = $l_row;
                } else {
                    // The layer3-net does not exist - So we create it! Changed the IF-clause #4221
                    if (defined('C__OBJTYPE__LAYER3_NET') && !empty($l_row['ip4networkid']) && !empty($l_tmp) && !isset($l_already_imported[$l_tmp]) && $p_all_layer3 && !$l_layer3) {
                        // We reset the "not-found" cache, because we create a new object.
                        unset($this->m_missing_net_cache[$l_tmp]);
                        $l_already_imported[$l_tmp] = true;
                        $l_name = (empty($l_row['name'])) ? 'JDisc ' . $l_networkaddress : $l_row['name'];

                        // @todo Check for import-mode before blindly creating new objects!
                        // We create the new layer3 net.
                        $l_new_obj = $l_dao->insert_new_obj(
                            C__OBJTYPE__LAYER3_NET,
                            false,
                            $l_name,
                            null,
                            C__RECORD_STATUS__NORMAL,
                            null,
                            $l_row['discoverytime'],
                            true,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            'By JDisc import: ip4network ID #' . $l_row['id']
                        );

                        $l_id = $l_net_dao->create_connector('isys_cats_net_list', $l_new_obj);
                        $l_net_dao->set_object_id($l_new_obj);
                        $l_net_dao->save(
                            $l_id,
                            C__RECORD_STATUS__NORMAL,
                            $l_name,
                            C__CATS_NET_TYPE__IPV4,
                            $l_networkaddress,
                            $l_subnetmask,
                            null,
                            $l_firstaddress,
                            $l_lastaddress,
                            'By JDisc import: ip4network ID ' . $l_row['id'],
                            Ip::calc_cidr_suffix($l_subnetmask),
                            null,
                            ((!is_null($l_dns_domain_id)) ? [$l_dns_domain_id] : null)
                        );

                        // We'll search again for the layer3-net - By now it HAS TO BE in the system.
                        $l_layer3 = $this->does_layer3_exist_in_idoit(
                            C__CATS_NET_TYPE__IPV4,
                            $l_networkaddress,
                            $l_subnetmask,
                            $l_firstaddress,
                            $l_lastaddress
                        );

                        parent::set_object_id($l_new_obj);
                    }

                    // If no net was found, we use the global V4 net.
                    if (!$l_layer3) {
                        $l_layer3 = $l_net_dao->get_data(null, defined_or_default('C__OBJ__NET_GLOBAL_IPV4'))
                            ->get_row();
                    }

                    if (!is_null($l_dns_domain_id)) {
                        $l_layer3['dns_domain'] = $l_dns_domain;
                    }

                    // 60000 is the type for VRRP Addresses
                    if ($l_row['iftype'] === 60000 && $this->m_allowed_imports['vrrpcluster']) {
                        // get cluster service
                        $l_cluster_services = $this->get_assigned_clusterservice($l_row['deviceid']);
                        //$l_cluster_services = array(5);
                        // VRRP address
                        $this->m_vrrp_addresses['ipv4'][$l_row['deviceid']][$l_row['address']] = $l_cluster_services;
                        continue;
                    } else {
                        $l_return[] = $this->prepare_layer3_ipv4($l_row, $l_layer3, $p_connections);
                    }
                }
            }
            $this->m_pdo->free_result($l_res);
        }
        // @todo This could not yet be tested, because our JDisc instance has no IPv6 data!
        // Now we prepare the IPv6 layer-3 nets.
        $l_already_imported = [];

        if ($useIpv6 === true) {
            $l_sql = 'SELECT i6n.*, i6t.*, m.id AS portid, m.iftype,
                (CASE 
                    WHEN ip6networkid IS NOT NULL THEN networkaddress 
                    WHEN configuredprefix IS NOT NULL THEN NETWORK(INET(CONCAT(address, \'/\', TEXT(configuredprefix)))) 
                    ELSE NETWORK(address) 
                END) as possibleNetworkAddress,
                netmask(CASE 
                        WHEN ip6networkid IS NOT NULL THEN INET(CONCAT(networkaddress, \'/\', TEXT(networkprefix))) 
                        WHEN configuredprefix IS NOT NULL THEN NETWORK(INET(CONCAT(address, \'/\', TEXT(configuredprefix)))) 
                        ELSE NULL 
                    END) as possibleNetmask
                FROM ip6transport AS i6t
                LEFT JOIN ip6network AS i6n
                    ON i6t.ip6networkid = i6n.id
                LEFT JOIN device AS d
                    ON i6t.deviceid = d.id
                LEFT JOIN mac AS m
                    ON i6t.macid = m.id
                LEFT JOIN interfacetypelookup AS i on m.iftype = i.id
                    WHERE d.id = ' . $this->convert_sql_id($p_id) . ' AND i6t.addresstype != 1';

            if (($useLoopback === true) && ($useVirtual === false)) {
                $l_sql .= ' AND (i.name NOT LIKE ' . $this->convert_sql_text(self::C_FILTER_JDISC__VIRTUAL) . ')';
            } elseif (($useLoopback === false) && ($useVirtual === true)) {
                $l_sql .= ' AND (i.name NOT LIKE ' . $this->convert_sql_text(self::C_FILTER_JDISC__LOOPBACK) . ')';
            } elseif (($useLoopback === false) && ($useVirtual === false)) {
                $l_sql .= ' AND (i.name NOT LIKE ' . $this->convert_sql_text(self::C_FILTER_JDISC__LOOPBACK) .
                    ') AND (i.name NOT LIKE ' . $this->convert_sql_text(self::C_FILTER_JDISC__VIRTUAL) . ')';
            }

            if ($p_dhcpAddresses === 1) {
                $l_sql .= ' AND (m.dhcpenabled = true)';
            }

            $l_res = $this->fetch($l_sql);

            $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($l_res) . ' rows for IPv6');

            while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
                $l_dns_domain_id = null;
                $l_dns_domain = null;
                $l_tmp = null;
                $l_layer3 = false;
                $l_networkaddress = null;
                $l_subnetmask = null;
                $l_firstaddress = null;
                $l_lastaddress = null;

                if (!empty($l_row['fqdn'])) {
                    $l_fqdn_arr = explode('.', $l_row['fqdn']);
                    if (count($l_fqdn_arr) >= 3) {
                        $l_dns_domain = $l_fqdn_arr[count($l_fqdn_arr) - 2] . '.' . $l_fqdn_arr[count($l_fqdn_arr) - 1];
                        $l_dns_domain_id = isys_import_handler_cmdb::check_dialog('isys_net_dns_domain', $l_dns_domain);
                        if (!isset($this->m_dialog_data['dns_domain'][$l_dns_domain])) {
                            $this->addDialogData('dns_domain', $l_dns_domain, $l_dns_domain_id);
                        }
                    }
                }

                if (!empty($l_row['ip6networkid'])) {
                    $l_networkaddress = Ip::validate_ipv6($l_row['networkaddress']);
                    $l_row['subnetmask'] = $l_subnetmask = Ip::validate_ipv6($l_row['possiblenetmask']);
                    $l_firstaddress = Ip::validate_ipv6($l_row['firstaddress']);
                    $l_lastaddress = Ip::validate_ipv6($l_row['lastaddress']);
                } elseif (!empty($l_row['possiblenetworkaddress']) && !empty($l_row['possiblenetmask'])) {
                    $networkParts = explode('/', $l_row['possiblenetworkaddress']);
                    $cidrSuffix = (int)($l_row['configuredprefix'] ?: (isset($networkParts[1]) ? $networkParts[1] : 128));

                    $l_networkaddress = Ip::validate_ipv6($networkParts[0]);
                    $l_subnetmask = $l_row['possiblenetmask'];
                    $l_row['subnetmask'] = $l_row['possiblenetmask'];

                    $ipRange = Ip::calc_ip_range_ipv6($l_networkaddress, $l_subnetmask);

                    $l_firstaddress = $ipRange['from'];
                    $l_lastaddress = $ipRange['to'];
                }

                if ($l_networkaddress && $l_subnetmask && $l_firstaddress && $l_lastaddress) {
                    $l_tmp = C__CATS_NET_TYPE__IPV6 . '_' . $l_networkaddress . '_' . $l_subnetmask . '_' . $l_firstaddress . '_' . $l_lastaddress;
                    $l_layer3 = $this->does_layer3_exist_in_idoit(
                        C__CATS_NET_TYPE__IPV6,
                        $l_networkaddress,
                        $l_subnetmask,
                        $l_firstaddress,
                        $l_lastaddress
                    );
                }

                if ($p_raw === true) {
                    $l_return[] = $l_row;
                } else {
                    // The layer3-net does not exist - So we create it! Changed the IF-clause #4221
                    if (defined('C__OBJTYPE__LAYER3_NET') && $l_networkaddress && !empty($l_tmp) && !isset($l_already_imported[$l_tmp]) && $p_all_layer3 && !$l_layer3) {
                        // We reset the "not-found" cache, because we create a new object.
                        unset($this->m_missing_net_cache[$l_tmp]);
                        $l_already_imported[$l_tmp] = true;
                        $l_name = (empty($l_row['name'])) ? 'JDisc ' . $l_networkaddress : $l_row['name'];

                        $this->m_log->info('Creating layer-3 network "' . $l_name . '"');

                        // @todo Check for import-mode before blindly creating new objects!
                        // We create the new layer3 net.
                        $l_new_obj = $l_dao->insert_new_obj(
                            C__OBJTYPE__LAYER3_NET,
                            false,
                            $l_name,
                            null,
                            C__RECORD_STATUS__NORMAL,
                            null,
                            $l_row['discoverytime'],
                            true,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            'By JDisc import: ip6network ID #' . $l_row['id']
                        );

                        $l_id = $l_net_dao->create_connector('isys_cats_net_list', $l_new_obj);
                        $l_net_dao->set_object_id($l_new_obj);
                        $l_net_dao->save(
                            $l_id,
                            C__RECORD_STATUS__NORMAL,
                            $l_name,
                            C__CATS_NET_TYPE__IPV6,
                            $l_networkaddress,
                            $l_subnetmask,
                            null,
                            $l_firstaddress,
                            $l_lastaddress,
                            'By JDisc import: ip6network ID ' . $l_row['id'],
                            Ip::calc_cidr_suffix_ipv6($l_row['subnetmask']),
                            null,
                            ((!is_null($l_dns_domain_id)) ? [$l_dns_domain_id] : null)
                        );

                        // We'll search again for the layer3-net - By now it HAS TO BE in the system.
                        $l_layer3 = $this->does_layer3_exist_in_idoit(
                            C__CATS_NET_TYPE__IPV6,
                            $l_networkaddress,
                            $l_subnetmask,
                            $l_firstaddress,
                            $l_lastaddress
                        );

                        parent::set_object_id($l_new_obj);
                    }

                    // If no net was found, we use the global V4 net.
                    if (!$l_layer3) {
                        $l_layer3 = $l_net_dao->get_data(null, defined_or_default('C__OBJ__NET_GLOBAL_IPV6'))
                            ->get_row();
                    }

                    if (!is_null($l_dns_domain_id)) {
                        $l_layer3['dns_domain'] = $l_dns_domain;
                    }

                    // 60000 is the type for VRRP Addresses
                    if ($l_row['iftype'] === 60000 && $this->m_allowed_imports['vrrpcluster']) {
                        // get cluster service
                        $l_cluster_services = $this->get_assigned_clusterservice($l_row['deviceid']);
                        //$l_cluster_services = array(5);
                        $this->m_vrrp_addresses['ipv6'][$l_row['address']][$l_row['deviceid']] = $l_cluster_services;
                        // VRRP address
                        continue;
                    } else {
                        $l_return[] = $this->prepare_layer3_ipv6($l_row, $l_layer3, $p_connections);
                    }
                }
            }
            $this->m_pdo->free_result($l_res);
        }

        if ($p_raw === true || count($l_return) === 0) {
            return $l_return;
        } else {
            return [
                C__DATA__TITLE      => isys_application::instance()->container->get('language')
                    ->get('LC__CATG__IP_ADDRESS'),
                'const'             => 'C__CATG__IP',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => $l_return
            ];
        }
    }

    /**
     * Method for preparing the data from JDisc to a "i-doit-understandable" format.
     *
     * @param   array $p_data
     * @param   array $p_idoit_data
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function prepare_layer3_ipv4(array $p_data, array $p_idoit_data, &$p_connections = [])
    {
        if (!defined('C__CATS_NET_TYPE__IPV4')) {
            return [];
        }
        //$this->m_log->debug('>> Prepairing layer3 data (IPv4)');

        // We should always have the layer3-net in our system by now!
        if (!empty($p_data) && !empty($p_idoit_data) && isset($p_data['address'])) {
            if ($p_data['address'] > 0) {
                $language = isys_application::instance()->container->get('language');
                $l_addr = Ip::long2ip($p_data['address']);
                $this->m_log->debug(' Parsing ip address "' . $l_addr . '"');

                // Set connection object so that the import knows the object
                $p_connections[$p_idoit_data['isys_obj__id']]['properties'] = $l_net = [
                    'tag'        => 'net',
                    'value'      => $p_idoit_data['isys_obj__title'],
                    'id'         => $p_idoit_data['isys_obj__id'],
                    'type'       => 'C__OBJTYPE__LAYER3_NET',
                    'sysid'      => $p_idoit_data['isys_obj__sysid'],
                    'title_lang' => $language->get('LC__CMDB__OBJTYPE__LAYER3_NET'),
                    'title'      => 'LC__CMDB__CATS__NET'
                ];

                $l_return = [
                    'data_id'    => null,
                    'properties' => [
                        'net_type'        => [
                            'tag'        => 'net_type',
                            'value'      => $this->m_net_types[C__CATS_NET_TYPE__IPV4]['isys_net_type__title'],
                            'id'         => C__CATS_NET_TYPE__IPV4,
                            'const'      => $this->m_net_types[C__CATS_NET_TYPE__IPV4]['isys_net_type__const'],
                            'title_lang' => $this->m_net_types[C__CATS_NET_TYPE__IPV4]['isys_net_type__title'],
                            'title'      => 'LC__CMDB__CATG__NETWORK__TYPE',
                        ],
                        'primary'         => [
                            'tag'   => 'primary',
                            'value' => (($p_data['isdiscoverytransport'] === true) ? 1 : 0),
                            'title' => 'LC__CATG__CONTACT_LIST__PRIMARY'
                        ],
                        'active'          => [
                            'tag'   => 'active',
                            'value' => 1,
                            'title' => 'LC__CATP__IP__ACTIVE'
                        ],
                        'net'             => $l_net,
                        'ipv4_assignment' => [
                            'tag'        => 'ipv4_assignment',
                            'value'      => $language->get($this->m_ip_assignments[C__CATS_NET_TYPE__IPV4]['isys_ip_assignment__title']),
                            'id'         => $this->m_ip_assignments[C__CATS_NET_TYPE__IPV4]['isys_ip_assignment__id'],
                            'const'      => $this->m_ip_assignments[C__CATS_NET_TYPE__IPV4]['isys_ip_assignment__const'],
                            'title_lang' => $this->m_ip_assignments[C__CATS_NET_TYPE__IPV4]['isys_ip_assignment__title'],
                            'title'      => 'LC__CATP__IP__ASSIGN_IPV4',
                        ],
                        'ipv4_address'    => [
                            'tag'       => 'ipv4_address',
                            'value'     => $p_idoit_data['isys_obj__title'],
                            'id'        => $p_idoit_data['isys_obj__id'],
                            'type'      => 'C__OBJTYPE__LAYER3_NET',
                            'sysid'     => $p_idoit_data['isys_obj__sysid'],
                            'ref_id'    => null,
                            'ref_title' => $l_addr,
                            'ref_type'  => 'C__CATS__NET_IP_ADDRESSES',
                        ],
                        'hostname'        => [
                            'tag'   => 'hostname',
                            'value' => (isset($p_idoit_data['dns_domain']) ? rtrim(str_replace($p_idoit_data['dns_domain'], '', $p_data['fqdn']), '.') : $p_data['fqdn']),
                            'title' => 'LC__CATP__IP__HOSTNAME'
                        ],
                        'domain'          => [
                            'tag'   => 'domain',
                            'value' => (isset($p_idoit_data['dns_domain']) ? $p_idoit_data['dns_domain'] : ''),
                            'title' => 'LC__CATG__IP__DOMAIN'
                        ],
                    ]
                ];

                if (isset($p_idoit_data['dns_domain'])) {
                    $l_dns_domain = [
                        'tag'   => 'dns_domain',
                        'value' => [
                            [
                                'id'    => $this->m_dialog_data['dns_domain'][$p_idoit_data['dns_domain']],
                                'title' => $p_idoit_data['dns_domain'],
                            ]
                        ],
                        'title' => 'LC__CATP__IP__DNSDOMAIN'
                    ];
                    $l_return['properties']['dns_domain'] = $l_dns_domain;
                }

                if (isset($this->m_port_array[$p_data['portid']])) {
                    if (isset($this->m_port_array[$p_data['portid']]['id']) && $this->m_port_array[$p_data['portid']]['id'] > 0) {
                        $this->m_port_array[$p_data['portid']]['ip'][] = Ip::long2ip($p_data['address']);
                    }
                } elseif (isset($this->m_logical_port_array[$p_data['portid']])) {
                    if (isset($this->m_logical_port_array[$p_data['portid']]['id']) && $this->m_logical_port_array[$p_data['portid']]['id'] > 0) {
                        $this->m_logical_port_array[$p_data['portid']]['ip'][] = Ip::long2ip($p_data['address']);
                    }
                }

                return $l_return;
            } else {
                $this->m_log->debug(' Skipping ip address "' . $p_data['address'] . '"');
            }
        }

        return [];
    }

    /**
     * Method for preparing the data from JDisc to a "i-doit-understandable" format.
     *
     * @param   array $p_data
     * @param   array $p_idoit_data
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @todo    This could not yet be tested, because our JDisc data has no IPv6 stuff!!!
     */
    public function prepare_layer3_ipv6(array $p_data, array $p_idoit_data, &$p_connections = [])
    {
        if (!defined('C__CATS_NET_TYPE__IPV6')) {
            return [];
        }
        //$this->m_log->debug('>> Prepairing layer3 data (IPv6)');

        // We should always have the layer3-net in our system by now!
        if (!empty($p_data) && !empty($p_idoit_data)) {
            $language = isys_application::instance()->container->get('language');
            // Set connections so that the import knows this object
            $p_connections[$p_idoit_data['isys_obj__id']]['properties'] = $l_net = [
                'tag'        => 'net',
                'value'      => $p_idoit_data['isys_obj__title'],
                'id'         => $p_idoit_data['isys_obj__id'],
                'type'       => 'C__OBJTYPE__LAYER3_NET',
                'sysid'      => $p_idoit_data['isys_obj__sysid'],
                'title_lang' => $language
                    ->get('LC__CMDB__OBJTYPE__LAYER3_NET'),
                'title'      => 'LC__CMDB__CATS__NET'
            ];

            $l_return = [
                'data_id'    => null,
                'properties' => [
                    'net_type'        => [
                        'tag'        => 'net_type',
                        'value'      => $this->m_net_types[C__CATS_NET_TYPE__IPV6]['isys_net_type__title'],
                        'id'         => C__CATS_NET_TYPE__IPV6,
                        'const'      => $this->m_net_types[C__CATS_NET_TYPE__IPV6]['isys_net_type__const'],
                        'title_lang' => $this->m_net_types[C__CATS_NET_TYPE__IPV6]['isys_net_type__title'],
                        'title'      => 'LC__CMDB__CATG__NETWORK__TYPE',
                    ],
                    'primary'         => [
                        'tag'   => 'primary',
                        'value' => 0,
                        'title' => 'LC__CATG__CONTACT_LIST__PRIMARY'
                    ],
                    'active'          => [
                        'tag'   => 'active',
                        'value' => 1,
                        'title' => 'LC__CATP__IP__ACTIVE'
                    ],
                    'net'             => $l_net,
                    'ipv6_assignment' => [
                        'tag'        => 'ipv6_assignment',
                        'value'      => $language
                            ->get($this->m_ip_assignments[C__CATS_NET_TYPE__IPV6]['isys_ipv6_assignment__title']),
                        'id'         => $this->m_ip_assignments[C__CATS_NET_TYPE__IPV6]['isys_ipv6_assignment__id'],
                        'const'      => $this->m_ip_assignments[C__CATS_NET_TYPE__IPV6]['isys_ipv6_assignment__const'],
                        'title_lang' => $this->m_ip_assignments[C__CATS_NET_TYPE__IPV6]['isys_ipv6_assignment__title'],
                        'title'      => 'LC__CATP__IP__ASSIGN_IPV6',
                    ],
                    'ipv6_address'    => [
                        'tag'       => 'ipv6_address',
                        'value'     => $p_idoit_data['isys_obj__title'],
                        'id'        => $p_idoit_data['isys_obj__id'],
                        'type'      => 'C__OBJTYPE__LAYER3_NET',
                        'sysid'     => $p_idoit_data['isys_obj__sysid'],
                        'ref_id'    => null,
                        'ref_title' => Ip::validate_ipv6($p_data['address']),
                        'ref_type'  => 'C__CATS__NET_IP_ADDRESSES',
                    ],
                    // have to set it otherwise the logbook identifies it as a change
                    'ipv6_scope'      => [
                        'tag'        => 'ipv6_scope',
                        'value'      => 'LC__CMDB__CATG__IP__IPV6_SCOPE',
                        'id'         => defined_or_default('C__CMDB__CATG__IP__GLOBAL_UNICAST'),
                        'const'      => 'C__CMDB__CATG__IP__GLOBAL_UNICAST',
                        'title_lang' => 'LC__CMDB__CATG__IP__GLOBAL_UNICAST',
                        'title'      => 'LC__CMDB__CATG__IP__IPV6_SCOPE',
                    ],
                    'hostname'        => [
                        'tag'   => 'hostname',
                        'value' => (isset($p_idoit_data['dns_domain']) ? rtrim(str_replace($p_idoit_data['dns_domain'], '', $p_data['fqdn']), '.') : $p_data['fqdn']),
                        'title' => 'LC__CATP__IP__HOSTNAME'
                    ],
                    'domain'          => [
                        'tag'   => 'domain',
                        'value' => (isset($p_idoit_data['dns_domain']) ? $p_idoit_data['dns_domain'] : ''),
                        'title' => 'LC__CATG__IP__DOMAIN'
                    ],
                    'dns_domain'      => [
                        'tag'   => 'dns_domain',
                        'value' => [
                            [
                                'title' => $p_idoit_data['dns_domain']
                            ]
                        ],
                        'title' => 'LC__CATP__IP__DNSDOMAIN',
                    ]
                ]
            ];
            if (isset($this->m_port_array[$p_data['portid']])) {
                if (isset($this->m_port_array[$p_data['portid']]['id']) && $this->m_port_array[$p_data['portid']]['id'] > 0) {
                    $this->m_port_array[$p_data['portid']]['ip'][] = Ip::validate_ipv6($p_data['address']);
                }
            } elseif (isset($this->m_logical_port_array[$p_data['portid']])) {
                if (isset($this->m_logical_port_array[$p_data['portid']]['id']) && $this->m_logical_port_array[$p_data['portid']]['id'] > 0) {
                    $this->m_logical_port_array[$p_data['portid']]['ip'][] = Ip::validate_ipv6($p_data['address']);
                }
            }

            return $l_return;
        }
    }

    /**
     * Method for receiving logical ports, assigned to a given device.
     *
     * @param   integer $p_id
     * @param   boolean $p_raw
     *
     * @return  array
     */
    public function get_logical_ports_by_device($p_id, $p_raw = false)
    {
        $l_return = [];

        /**
         * IDE typehinting helper.
         *
         * @var  $l_dao            isys_cmdb_dao_jdisc
         * @var  $l_dao_layer2_net isys_cmdb_dao_category_s_layer2_net
         */
        $l_dao = isys_cmdb_dao_jdisc::instance($this->m_db);
        $l_local_ports = new isys_array();

        // Get local logical ports only in update mode
        if (isys_jdisc_dao_data::clear_data() === false && defined('C__CATG__NETWORK_LOG_PORT')) {
            $l_local_ports = $this->create_port_map($this->get_current_object_id(), C__CATG__NETWORK_LOG_PORT);
        }

        // At first we prepare the SQL to receive the ports.
        // Version 2.9
        $l_sql = 'SELECT mac.*, dl.id AS type_id, dl.singular AS type_custom, os.id AS os_id, os.osversion AS os_custom, ift.name AS port_type 
            FROM mac
            JOIN device AS d ON d.id = mac.deviceid 
            JOIN devicetypelookup AS dl ON dl.id = d.type 
            LEFT JOIN operatingsystem AS os ON d.operatingsystemid = os.id 
            LEFT JOIN interfacetypelookup AS ift ON ift.id = mac.iftype 
            WHERE (mac.ifdescr IS NOT NULL OR mac.ifphysaddress IS NOT NULL) 
            AND deviceid = ' . $this->convert_sql_id($p_id) . ' 
            AND mac.portid IS NULL AND mac.iftype IN (' . implode(
            ',',
            array_merge(
                $this->m_interface_types['virtual']['content'],
                $this->m_interface_types['vlan']['content'],
                $this->m_interface_types['loopback']['content'],
                $this->m_interface_types['tunnel']['content']
            )
        ) . ')';

        if (count($this->m_port_filter_import_type) > 0) {
            $l_sql .= $this->get_port_filter_query(1);
        }

        if (count($this->m_logical_port_array) === 0) {
            $l_new_id = $l_dao->get_last_id_from_table('isys_catg_log_port_list');
        } else {
            $l_keys = array_keys($this->m_logical_port_array);
            $l_new_id = $l_keys[count($l_keys) - 1];
        }

        $l_res = $this->fetch($l_sql);

        $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($l_res) . ' rows for logical ports');

        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
            $l_new_id++;

            if ($this->m_import_vlans && $l_row['ifphysaddress'] !== '') {
                $this->m_layer2_net_array2[] = $l_row['id'] . '$$' . $this->get_current_object_id() . '$$' . $l_row['ifphysaddress'] . '$$' . $l_row['ifdescr'];
            }

            $this->m_logical_port_array[$l_row['id']] = [
                'id'       => $l_new_id,
                'value'    => $l_row['ifdescr'],
                'mac'      => $l_row['ifphysaddress'],
                'obj_name' => $this->m_idoit_obj_name,
                'obj_type' => $this->m_idoit_obj_type
            ];

            // Check if already exist
            if ($l_row['ifdescr'] !== '' && isset($l_local_ports[$l_row['ifdescr'] . '|' . $this->get_current_object_id() . '|' . $l_row['ifphysaddress']])) {
                // Update simple fields
                //				if($l_port_info['isys_catg_log_port_list__active'] != $l_row['ifoperstatus'])
                //				{
                //					$l_update = 'UPDATE isys_catg_log_port_list SET isys_catg_log_port_list__active = ' . $this->convert_sql_int((($l_row['ifoperstatus'] > 1)? 0: 1)) .
                //						' WHERE isys_catg_log_port_list__id = ' . $this->convert_sql_id($l_port_info['portid']);
                //					$this->update($l_update);
                //				}
                continue;
            }

            $l_return[$l_new_id] = $this->prepare_logical_ports($l_row, $l_new_id);
        }
        $this->m_pdo->free_result($l_res);

        unset($l_local_ports);

        if ($p_raw === true || count($l_return) === 0) {
            return $l_return;
        } else {
            return [
                C__DATA__TITLE      => isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__CATG__NETWORK_TREE_CONFIG_PORT_L'),
                'const'             => 'C__CATG__NETWORK_LOG_PORT',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => $l_return
            ];
        }
    }

    /**
     * Method for receiving ports, assigned to a given device.
     *
     * @param   integer $p_id
     * @param   boolean $p_raw
     *
     * @return  array
     */
    public function get_ports_by_device($p_id, $p_raw = false)
    {
        $l_return = [];

        /**
         * IDE typehinting helper.
         *
         * @var  $l_dao            isys_cmdb_dao_jdisc
         * @var  $l_dao_layer2_net isys_cmdb_dao_category_s_layer2_net
         */
        $l_dao = isys_cmdb_dao_jdisc::instance($this->m_db);
        $l_local_ports = new isys_array();
        $language = isys_application::instance()->container->get('language');

        // Get local ports
        if (isys_jdisc_dao_data::clear_data() === false && defined('C__CATG__NETWORK_PORT')) {
            $l_local_ports = $this->create_port_map($this->get_current_object_id(), C__CATG__NETWORK_PORT);
        }

        // At first we prepare the SQL to receive the ports.
        $l_sql = 'SELECT m.*, dl.id AS type_id, ' . 'dl.singular AS type_custom, os.id AS os_id, os.osversion AS os_custom, ' . 'mo.id AS if_id, ift.name AS port_type ' .
            'FROM mac AS m ' . 'LEFT JOIN device AS d ON d.id = m.deviceid ' . 'LEFT JOIN devicetypelookup AS dl ON dl.id = d.type ' .
            'LEFT JOIN operatingsystem AS os ON d.operatingsystemid = os.id ' . 'LEFT JOIN module AS mo ON mo.id = m.moduleid ' .
            'LEFT JOIN interfacetypelookup AS ift ON ift.id = m.iftype ' . 'WHERE (m.ifdescr IS NOT NULL OR m.ifphysaddress IS NOT NULL) AND m.deviceid = ' .
            $this->convert_sql_id($p_id) . ' ' . 'AND ' . '(m.iftype NOT IN (' . implode(
                ',',
                array_merge(
                    $this->m_interface_types['virtual']['content'],
                    $this->m_interface_types['vlan']['content'],
                    $this->m_interface_types['loopback']['content'],
                    $this->m_interface_types['tunnel']['content'],
                    $this->m_interface_types['fibreChannel']['content']
                )
            ) . ')' . ' OR m.iftype IS NULL)';

        if (count($this->m_port_filter_import_type) > 0) {
            $l_sql .= $this->get_port_filter_query(2);
        }

        if (count($this->m_port_array) === 0) {
            $l_new_id = $l_dao->get_last_id_from_table('isys_catg_port_list');
        } else {
            $l_keys = array_keys($this->m_port_array);
            $l_new_id = $l_keys[count($l_keys) - 1];
        }

        $l_res = $this->fetch($l_sql);
        $l_num_rows = $this->m_pdo->num_rows($l_res);

        $this->m_log->debug('> Found ' . $l_num_rows . ' rows for ports');

        if ($l_num_rows > 0) {
            while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
                $l_new_id++;
                if ($l_row['if_id'] > 0) {
                    // Network interface
                    $this->m_network_interfaces_connection[$p_id][$l_row['id']] = $l_row['if_id'];
                }

                if ($this->m_import_vlans && $l_row['ifphysaddress'] !== '') {
                    $this->m_layer2_net_array[] = $l_row['id'] . '$$' . $this->get_current_object_id() . '$$' . $l_row['ifphysaddress'] . '$$' . $l_row['ifdescr'];
                }

                // Determine if port already exists or not updating ports is being handled in the create_port_connections
                $l_update = $l_local_ports[$l_row['ifdescr'] . '|' . $this->get_current_object_id() . '|' . $l_row['ifphysaddress']] ?: false;

                // Retrieve the duplex mode
                if ($l_row['duplexmode'] === 3) {
                    $l_duplex_id = $this->m_dialog_data['port_duplex'][$language->get('LC__PORT_DUPLEX__FULL')][0];
                } else {
                    $l_duplex_id = $this->m_dialog_data['port_duplex'][$language->get('LC__PORT_DUPLEX__HALF')][0];
                }

                // Retrieve the port type
                if (!isset($this->m_dialog_data['port_type'][$l_row['port_type']])) {
                    $this->addDialogData(
                        'port_type',
                        $l_row['port_type'],
                        [
                            isys_cmdb_dao_dialog_admin::instance($this->m_db)
                                ->create('isys_port_type', $l_row['port_type'], null, null, C__RECORD_STATUS__NORMAL),
                            null,
                            $l_row['port_type']
                        ]
                    );
                }

                // Set speed unit
                if ($l_row['ifspeed'] > 1000) {
                    $l_speed_type = $this->m_dialog_data['port_speed']['C__PORT_SPEED__KBIT_S'][0];
                    if ($l_row['ifspeed'] >= 1000000) {
                        $l_speed_type = $this->m_dialog_data['port_speed']['C__PORT_SPEED__MBIT_S'][0];
                        if ($l_row['ifspeed'] >= 1000000000) {
                            $l_speed_type = $this->m_dialog_data['port_speed']['C__PORT_SPEED__GBIT_S'][0];
                        }
                    }
                } else {
                    $l_speed_type = $this->m_dialog_data['port_speed']['C__PORT_SPEED__BIT_S'][0];
                }

                /*
                 * @note: comparestring is used for the update which is handled in create_port_connections.
                 * */
                $this->m_port_array[$l_row['id']] = new isys_array([
                    'id'            => $l_new_id,
                    'value'         => $l_row['ifdescr'],
                    'mac'           => $l_row['ifphysaddress'],
                    'interface_id'  => $l_row['if_id'],
                    'obj_name'      => $this->m_idoit_obj_name,
                    'obj_type'      => $this->m_idoit_obj_type,
                    'update'        => $l_update,
                    'active'        => ($l_row['ifoperstatus'] > 1) ? 0 : 1,
                    'comparestring' => $l_row['ifphysaddress'] . '|' . $l_duplex_id . '|' . $this->m_dialog_data['port_type'][$l_row['port_type']][0] . '|' .
                        $l_row['ifspeed'] . '|' . $l_speed_type . '|' . (($l_row['ifoperstatus'] > 1) ? 0 : 1) . '|' . $l_row['ifannotation']
                ]);

                if (!$l_update) {
                    // Only new ports are being parsed
                    $l_return[$l_new_id] = $this->prepare_ports($l_row, $l_new_id);
                }
            }
        }

        unset($l_local_ports);
        $this->m_pdo->free_result($l_res);

        if ($p_raw === true || count($l_return) === 0) {
            return $l_return;
        } else {
            return [
                C__DATA__TITLE      => $language
                    ->get('LC__CATD__PORT'),
                'const'             => 'C__CATG__NETWORK_PORT',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => $l_return
            ];
        }
    }

    /**
     * Method for receiving network interfaces, assigned to a given device.
     *
     * @param   integer $p_id
     * @param   boolean $p_raw
     *
     * @return  array
     */
    public function get_interfaces_by_device($p_id, $p_raw = false)
    {
        $l_return = [];

        //if(!$this->m_allowed_imports['interfaces']) return $l_return;
        // Check if interfaces should be imported to the interface category
        if ($this->m_import_type_interfaces === 1) {
            return $l_return;
        }

        /**
         * IDE typehinting helper.
         *
         * @var  $l_dao           isys_cmdb_dao_jdisc
         * @var  $l_dao_interface isys_cmdb_dao_category_g_network_interface
         */
        $l_dao = isys_cmdb_dao_jdisc::instance($this->m_db);

        // At first we prepare the SQL to receive the ports.
        $l_sql = 'SELECT mo.*, ms.socketdesignation ' . 'FROM mac AS m ' . 'INNER JOIN module AS mo ON mo.id = m.moduleid ' .
            'LEFT JOIN moduleslot AS ms ON ms.itemid = mo.id ' . 'LEFT JOIN device AS d ON d.id = m.deviceid ' . 'WHERE m.deviceid = ' . $this->convert_sql_id($p_id) .
            ' AND m.iftype NOT IN (' . implode(
                ',',
                array_merge(
                    $this->m_interface_types['virtual']['content'],
                    $this->m_interface_types['vlan']['content'],
                    $this->m_interface_types['loopback']['content'],
                    $this->m_interface_types['tunnel']['content']
                )
            ) . ')';

        if (count($this->m_network_interfaces) === 0) {
            $l_new_id = $l_dao->get_last_id_from_table('isys_catg_netp_list');
        } else {
            $l_keys = array_keys($this->m_network_interfaces);
            $l_new_id = $l_keys[count($l_keys) - 1];
        }

        $l_res = $this->fetch($l_sql);
        if (($l_if_amount = $this->m_pdo->num_rows($l_res)) > 0) {
            $this->m_log->info($l_if_amount . ' interfaces found.');

            while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
                if (!isset($this->m_network_interfaces[$l_row['id']])) {
                    $l_new_id++;
                    $l_return[$l_new_id] = $this->prepare_network_interface($l_row, $l_new_id);
                    $l_title = ($l_row['description'] !== '' && !is_numeric($l_row['description'])) ? $l_row['description'] : ($l_row['model'] .
                        (($l_row['serialnumber']) ? ' - ' . $l_row['serialnumber'] : ''));

                    $this->m_network_interfaces[$l_row['id']] = [
                        'id'       => $l_new_id,
                        'value'    => $l_title,
                        'obj_name' => $this->m_idoit_obj_name,
                        'obj_type' => $this->m_idoit_obj_type
                    ];
                }
            }
            $this->m_pdo->free_result($l_res);
        } else {
            $this->m_log->info('No interfaces found for device ID ' . $p_id);
        }

        if ($p_raw === true || count($l_return) === 0) {
            return $l_return;
        } else {
            return [
                C__DATA__TITLE      => isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE_P'),
                'const'             => 'C__CATG__NETWORK_INTERFACE',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => $l_return
            ];
        }
    }

    /**
     * Method for retrieving fc-ports of a device
     *
     * @param            $p_id
     * @param bool|false $p_raw
     *
     * @return array
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_fc_ports_by_device($p_id, $p_raw = false)
    {
        $l_return = [];

        /**
         * IDE typehinting helper.
         *
         * @var  $l_dao            isys_cmdb_dao_jdisc
         * @var  $l_dao_layer2_net isys_cmdb_dao_category_s_layer2_net
         */
        $l_dao = isys_cmdb_dao_jdisc::instance($this->m_db);
        $l_local_ports = new isys_array();

        // Get local ports
        if (isys_jdisc_dao_data::clear_data() === false && defined('C__CATG__CONTROLLER_FC_PORT')) {
            $l_local_ports = $this->create_port_map($this->get_current_object_id(), C__CATG__CONTROLLER_FC_PORT);
        }

        // At first we prepare the SQL to receive the ports.
        $l_sql = 'SELECT m.*, dl.id AS type_id, ' . 'dl.singular AS type_custom, os.id AS os_id, os.osversion AS os_custom, ' . 'ift.name AS port_type ' . 'FROM mac AS m ' .
            'LEFT JOIN device AS d ON d.id = m.deviceid ' . 'LEFT JOIN devicetypelookup AS dl ON dl.id = d.type ' .
            'LEFT JOIN operatingsystem AS os ON d.operatingsystemid = os.id ' . 'LEFT JOIN interfacetypelookup AS ift ON ift.id = m.iftype ' .
            'WHERE (m.ifdescr IS NOT NULL OR m.ifphysaddress IS NOT NULL) AND m.deviceid = ' . $this->convert_sql_id($p_id) . ' ' . 'AND m.portid IS NULL ' .
            'AND m.iftype IN (' . implode(',', $this->m_interface_types['fibreChannel']['content']) . ')';

        if (count($this->m_port_filter_import_type) > 0) {
            $l_sql .= $this->get_port_filter_query(4);
        }

        if (count($this->m_fc_port_array) === 0) {
            $l_new_id = $l_dao->get_last_id_from_table('isys_catg_fc_port_list');
        } else {
            $l_keys = array_keys($this->m_fc_port_array);
            $l_new_id = $l_keys[count($l_keys) - 1];
        }

        $l_res = $this->fetch($l_sql);

        $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($l_res) . ' rows for ports');

        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
            $l_new_id++;

            $this->m_fc_port_array[$l_row['id']] = new isys_array([
                'id'       => $l_new_id,
                'value'    => $l_row['ifdescr'],
                'mac'      => $l_row['ifphysaddress'],
                'obj_id'   => isys_jdisc_dao_data::$m_current_object_id,
                'obj_name' => $this->m_idoit_obj_name,
                'obj_type' => $this->m_idoit_obj_type
            ]);

            // Check if already exist
            if ($l_row['ifdescr'] !== '' && isset($l_local_ports[$l_row['ifdescr'] . '|' . $this->get_current_object_id()])) {
                /*
                // Set speed unit
                if ($l_row['ifspeed'] > 1000)
                {
                    $l_speed_type = C__PORT_SPEED__KBIT_S;
                    if ($l_row['ifspeed'] >= 1000000)
                    {
                        $l_speed_type = C__PORT_SPEED__MBIT_S;
                        if ($l_row['ifspeed'] >= 1000000000)
                        {
                            $l_speed_type = C__PORT_SPEED__GBIT_S;
                        }
                    }
                }
                else
                {
                    $l_speed_type = C__PORT_SPEED__BIT_S;
                }

                */
                continue;
            }

            $l_return[$l_new_id] = $this->prepare_fc_ports($l_row, $l_new_id);
        }
        $this->m_pdo->free_result($l_res);

        unset($l_local_ports);

        if ($p_raw === true || count($l_return) === 0) {
            return $l_return;
        } else {
            return [
                C__DATA__TITLE      => isys_application::instance()->container->get('language')
                    ->get('LC__STORAGE_FCPORT'),
                'const'             => 'C__CATG__CONTROLLER_FC_PORT',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => $l_return
            ];
        }
    }

    public function prepare_fc_ports(array $p_data, $p_new_id)
    {
        $l_return = [];
        if (!empty($p_data)) {
            $language = isys_application::instance()->container->get('language');
            $l_return = [
                'data_id'    => $p_new_id,
                'properties' => [
                    'title'       => [
                        'tag'   => 'title',
                        'value' => $p_data['ifdescr'],
                        'title' => 'LC__CMDB__CATG__TITLE'
                    ],
                    'description' => [
                        'tag'   => 'description',
                        'value' => $p_data['ifannotation'],
                        'title' => 'LC__CMDB__LOGBOOK__DESCRIPTION'
                    ]
                ]
            ];

            if (isset($p_data['ifspeed'])) {
                if ($p_data['ifspeed'] > 1000) {
                    if ($p_data['ifspeed'] >= 1000000) {
                        if ($p_data['ifspeed'] >= 1000000000) {
                            $l_speed_type = $this->m_dialog_data['port_speed']['C__PORT_SPEED__GBIT_S'][0];
                            $l_speed_unit = $language->get('LC__CMDB__PORT_SPEED__GBITS');
                            $l_speed_const = 'C__PORT_SPEED__GBIT_S';
                        } else {
                            $l_speed_type = $this->m_dialog_data['port_speed']['C__PORT_SPEED__MBIT_S'][0];
                            $l_speed_unit = $language->get('LC__CMDB__PORT_SPEED__MBITS');
                            $l_speed_const = 'C__PORT_SPEED__MBIT_S';
                        }
                    } else {
                        $l_speed_type = $this->m_dialog_data['port_speed']['C__PORT_SPEED__KBIT_S'][0];
                        $l_speed_unit = $language->get('LC__CMDB__PORT_SPEED__KBITS');
                        $l_speed_const = 'C__PORT_SPEED__KBIT_S';
                    }
                } else {
                    $l_speed_type = $this->m_dialog_data['port_speed']['C__PORT_SPEED__BIT_S'][0];
                    $l_speed_unit = $language->get('LC__CMDB__PORT_SPEED__BITS');
                    $l_speed_const = 'C__PORT_SPEED__BIT_S';
                }

                $l_conveted_speed = isys_convert::speed($p_data['ifspeed'], $l_speed_type, C__CONVERT_DIRECTION__BACKWARD);
                $l_return['properties']['speed'] = [
                    'tag'   => 'speed',
                    'value' => $l_conveted_speed,
                    'title' => 'LC__CMDB__CATG__PORT__SPEED'
                ];
                $l_return['properties']['speed_unit'] = [
                    'tag'        => 'speed_unit',
                    'value'      => $l_speed_unit,
                    'id'         => $l_speed_type,
                    'const'      => $l_speed_const,
                    'title_lang' => $l_speed_unit,
                    'title'      => 'LC__CMDB__CATG__UNIT'
                ];
            }
        }

        return $l_return;
    }

    /**
     * Method for preparing the data from JDisc to a "i-doit-understandable" format.
     * Category network interfaces
     *
     * @param   array   $p_data
     * @param   integer $p_new_id
     *
     * @return  array
     */
    public function prepare_network_interface(array $p_data, $p_new_id)
    {
        //$this->m_log->debug('>> Preparing network interface data');

        if (!empty($p_data)) {
            $language = isys_application::instance()->container->get('language');
            $l_jdisc_model = trim($p_data['model']);
            if ($l_jdisc_model !== '') {
                $l_jdisc_model = isys_import_handler::check_dialog('isys_iface_model', $l_jdisc_model);
                $l_dialog_data['model'] = isys_factory_cmdb_dialog_dao::get_instance($this->m_db, 'isys_iface_model')
                    ->get_data($l_jdisc_model);
            }

            $l_jdisc_manufacturer = trim($p_data['manufacturer']);
            if ($l_jdisc_manufacturer !== '') {
                $l_jdisc_manufacturer = isys_import_handler::check_dialog('isys_iface_manufacturer', $l_jdisc_manufacturer);
                $l_dialog_data['manufacturer'] = isys_factory_cmdb_dialog_dao::get_instance($this->m_db, 'isys_iface_manufacturer')
                    ->get_data($l_jdisc_manufacturer);
            }

            $p_data['serialnumber'] = trim($p_data['serialnumber']);
            $l_title = ($p_data['description'] !== '' && !is_numeric($p_data['description'])) ? $p_data['description'] : ($p_data['model'] .
                (($p_data['serialnumber']) ? ' - ' . $p_data['serialnumber'] : ''));

            $l_return = [
                'data_id'    => $p_new_id,
                'properties' => [
                    'title'       => [
                        'tag'   => 'title',
                        'value' => $l_title,
                        'title' => 'LC__CMDB__CATG__TITLE'
                    ],
                    'serial'      => [
                        'tag'   => 'serial',
                        'value' => $p_data['serialnumber'],
                        'title' => 'LC__CATP__IP__ACTIVE'
                    ],
                    'slot'        => [
                        'tag'   => 'slot',
                        'value' => $p_data['socketdesignation'],
                        'title' => 'LC__CATG__SWITCH_COUNT_SLOT'
                    ],
                    'description' => [
                        'tag'   => 'description',
                        'value' => $p_data['description'],
                        'title' => 'LC__CMDB__LOGBOOK__DESCRIPTION'
                    ]
                ]
            ];

            if (isset($l_dialog_data['manufacturer'])) {
                $l_return['properties']['manufacturer'] = [
                    'tag'        => 'manufacturer',
                    'value'      => $language->get($l_dialog_data['manufacturer']['isys_iface_manufacturer__title']),
                    'id'         => $l_dialog_data['manufacturer']['isys_iface_manufacturer__id'],
                    'const'      => $l_dialog_data['manufacturer']['isys_iface_manufacturer__const'],
                    'title_lang' => $language->get($l_dialog_data['manufacturer']['isys_iface_manufacturer__title']),
                    'title'      => $l_dialog_data['manufacturer']['isys_iface_manufacturer__title']
                ];
            }

            if (isset($l_dialog_data['model'])) {
                $l_return['properties']['model'] = [
                    'tag'        => 'model',
                    'value'      => $language->get($l_dialog_data['model']['isys_iface_model__title']),
                    'id'         => $l_dialog_data['model']['isys_iface_model__id'],
                    'const'      => $l_dialog_data['model']['isys_iface_model__const'],
                    'title_lang' => $language->get($l_dialog_data['model']['isys_iface_model__title']),
                    'title'      => $l_dialog_data['model']['isys_iface_model__title']
                ];
            }

            return $l_return;
        }
    }

    /**
     * Method for preparing the data from JDisc to a "i-doit-understandable" format.
     * Category logical ports
     *
     * @param   array   $p_data
     * @param   integer $p_new_id
     * @param   array   $p_layer2_net_data
     *
     * @return  array
     */
    public function prepare_logical_ports(array $p_data, $p_new_id)
    {
        //$this->m_log->debug('>> Preparing logical port data');
        $l_return = [];

        if (!empty($p_data)) {
            $l_layer2_net_data = null;

            if (!isset($this->m_dialog_data['port_type_logical'][$p_data['port_type']])) {
                $this->addDialogData(
                    'port_type_logical',
                    $p_data['port_type'],
                    [
                        isys_cmdb_dao_dialog_admin::instance($this->m_db)
                            ->create('isys_netx_ifacel_type', $p_data['port_type'], null, null, C__RECORD_STATUS__NORMAL),
                        null,
                        $p_data['port_type']
                    ]
                );
            }

            $l_return = [
                'data_id'    => $p_new_id,
                'properties' => [
                    'title'     => [
                        'tag'   => 'title',
                        'value' => $p_data['ifdescr'],
                        'title' => 'LC__CMDB__CATG__TITLE'
                    ],
                    'mac'       => [
                        'tag'   => 'mac',
                        'value' => $p_data['ifphysaddress'],
                        'title' => 'LC__CMDB__CATG__PORT__MAC'
                    ],
                    'port_type' => [
                        'tag'        => 'port_type',
                        'value'      => $p_data['port_type'],
                        'id'         => $this->m_dialog_data['port_type_logical'][$p_data['port_type']][0],
                        'const'      => $this->m_dialog_data['port_type_logical'][$p_data['port_type']][1],
                        'title_lang' => isys_application::instance()->container->get('language')
                            ->get($this->m_dialog_data['port_type_logical'][$p_data['port_type']][2]),
                        'title'      => 'LC__CMDB__CATG__NETWORK__TYPE'
                    ],
                    //					'net' => array(
                    //						'tag' => 'net',
                    //						'value' => $l_layer2_net_data,
                    //						'title' => 'LC__CMDB__CATG__INTERFACE_L__NET'
                    //					),
                    'active'    => [
                        'tag'   => 'active',
                        'value' => ($p_data['ifoperstatus'] > 1) ? 0 : 1,
                        'title' => 'LC__CATP__IP__ACTIVE'
                    ]
                ]
            ];
            unset($l_layer2_net_data);
        }

        return $l_return;
    }

    /**
     * Method for preparing the data from JDisc to a "i-doit-understandable" format.
     *
     * @param array   $p_data
     * @param integer $p_new_id
     *
     * @return  array
     * @throws Exception
     */
    public function prepare_ports(array $p_data, $p_new_id)
    {
        //$this->m_log->debug('>> Preparing port data');

        if (!empty($p_data)) {
            $language = isys_application::instance()->container->get('language');
            // port type
            if (!isset($this->m_dialog_data['port_type'][$p_data['port_type']])) {
                $this->addDialogData(
                    'port_type',
                    $p_data['port_type'],
                    [
                        isys_cmdb_dao_dialog_admin::instance($this->m_db)
                            ->create('isys_port_type', $p_data['port_type'], null, null, C__RECORD_STATUS__NORMAL),
                        null,
                        $p_data['port_type']
                    ]
                );
            }

            $l_return = [
                'data_id'    => $p_new_id,
                'properties' => [
                    'title'       => [
                        'tag'   => 'title',
                        'value' => $p_data['ifdescr'],
                        'title' => 'LC__CMDB__CATG__TITLE'
                    ],
                    'port_type'   => [
                        'tag'        => 'port_type',
                        'value'      => $p_data['port_type'],
                        'id'         => $this->m_dialog_data['port_type'][$p_data['port_type']][0],
                        'const'      => $this->m_dialog_data['port_type'][$p_data['port_type']][1],
                        'title_lang' => $language->get($this->m_dialog_data['port_type'][$p_data['port_type']][2]),
                        'title'      => 'LC__CMDB__CATG__TYPE'
                    ],
                    'port_mode'   => [
                        'tag'        => 'port_mode',
                        'value'      => $language->get($this->m_dialog_data['port_mode']['Standard'][2]),
                        'id'         => $this->m_dialog_data['port_mode']['Standard'][0],
                        'const'      => $this->m_dialog_data['port_mode']['Standard'][1],
                        'title_lang' => $language->get($this->m_dialog_data['port_mode']['Standard'][2]),
                        'title'      => 'LC__CMDB__CATG__PORT__MODE'
                    ],
                    'negotiation' => [
                        'tag'        => 'negotiation',
                        'value'      => $language->get($this->m_dialog_data['port_negotiation'][$language->get('LC__PORT_NEGOTIATION__AUTO')][2]),
                        'id'         => $this->m_dialog_data['port_negotiation'][$language->get('LC__PORT_NEGOTIATION__AUTO')][0],
                        'const'      => $this->m_dialog_data['port_negotiation'][$language->get('LC__PORT_NEGOTIATION__AUTO')][1],
                        'title_lang' => $language->get($this->m_dialog_data['port_negotiation'][$language
                                ->get('LC__PORT_NEGOTIATION__AUTO')][2]),
                        'title'      => 'LC__CMDB__CATG__PORT__NEGOTIATION'
                    ],
                    'mac'         => [
                        'tag'   => 'mac',
                        'value' => $p_data['ifphysaddress'],
                        'title' => 'LC__CMDB__CATG__PORT__MAC'
                    ],
                    'active'      => [
                        'tag'   => 'active',
                        'value' => ($p_data['ifoperstatus'] > 1) ? 0 : 1,
                        'title' => 'LC__CATP__IP__ACTIVE'
                    ],
                    //					'layer2_assignment' => array(
                    //						'tag' => 'layer2_assignment',
                    //						'value' => $l_layer2_net_data,
                    //						'title' => 'LC__CMDB__CATS__LAYER2_NET'
                    //					),
                    'description' => [
                        'tag'   => 'description',
                        'value' => $p_data['ifannotation'],
                        'title' => 'LC__CMDB__LOGBOOK__DESCRIPTION'
                    ]
                ]
            ];
            unset($l_layer2_net_data);

            if (!empty($p_data['duplexmode'])) {
                /**
                 * In JDisc
                 * 2 = Half Duplex
                 * 3 = Full Duplex
                 */
                if ($p_data['duplexmode'] === 3 || $p_data['duplexmode'] === 2) {
                    if ($p_data['duplexmode'] === 3) {
                        $l_duplex_id = $this->m_dialog_data['port_duplex'][$language->get('LC__PORT_DUPLEX__FULL')][0];
                        $l_duplex_const = $this->m_dialog_data['port_duplex'][$language->get('LC__PORT_DUPLEX__FULL')][1];
                        $l_duplex_title = $this->m_dialog_data['port_duplex'][$language->get('LC__PORT_DUPLEX__FULL')][2];
                    } else {
                        $l_duplex_id = $this->m_dialog_data['port_duplex'][$language->get('LC__PORT_DUPLEX__HALF')][0];
                        $l_duplex_const = $this->m_dialog_data['port_duplex'][$language->get('LC__PORT_DUPLEX__HALF')][1];
                        $l_duplex_title = $this->m_dialog_data['port_duplex'][$language->get('LC__PORT_DUPLEX__HALF')][2];
                    }

                    $l_return['properties']['duplex'] = [
                        'tag'        => 'duplex',
                        'value'      => $language->get($l_duplex_title),
                        'id'         => $l_duplex_id,
                        'const'      => $l_duplex_const,
                        'title_lang' => $l_duplex_title,
                        'title'      => 'LC__CMDB__CATG__PORT__DUPLEX'
                    ];
                }
            }

            if (isset($p_data['ifspeed'])) {
                if ($p_data['ifspeed'] > 1000) {
                    if ($p_data['ifspeed'] >= 1000000) {
                        if ($p_data['ifspeed'] >= 1000000000) {
                            $l_speed_type = $this->m_dialog_data['port_speed']['C__PORT_SPEED__GBIT_S'][0];
                            $l_speed_unit = $language->get('LC__CMDB__PORT_SPEED__GBITS');
                            $l_speed_const = 'C__PORT_SPEED__GBIT_S';
                        } else {
                            $l_speed_type = $this->m_dialog_data['port_speed']['C__PORT_SPEED__MBIT_S'][0];
                            $l_speed_unit = $language->get('LC__CMDB__PORT_SPEED__MBITS');
                            $l_speed_const = 'C__PORT_SPEED__MBIT_S';
                        }
                    } else {
                        $l_speed_type = $this->m_dialog_data['port_speed']['C__PORT_SPEED__KBIT_S'][0];
                        $l_speed_unit = $language->get('LC__CMDB__PORT_SPEED__KBITS');
                        $l_speed_const = 'C__PORT_SPEED__KBIT_S';
                    }
                } else {
                    $l_speed_type = $this->m_dialog_data['port_speed']['C__PORT_SPEED__BIT_S'][0];
                    $l_speed_unit = $language->get('LC__CMDB__PORT_SPEED__BITS');
                    $l_speed_const = 'C__PORT_SPEED__BIT_S';
                }

                $l_conveted_speed = isys_convert::speed($p_data['ifspeed'], $l_speed_type, C__CONVERT_DIRECTION__BACKWARD);
                $l_return['properties']['speed'] = [
                    'tag'   => 'speed',
                    'value' => $l_conveted_speed,
                    'title' => 'LC__CMDB__CATG__PORT__SPEED'
                ];
                $l_return['properties']['speed_type'] = [
                    'tag'        => 'speed_type',
                    'value'      => $l_speed_unit,
                    'id'         => $l_speed_type,
                    'const'      => $l_speed_const,
                    'title_lang' => $l_speed_unit,
                    'title'      => 'LC__CMDB__CATG__UNIT'
                ];
            }

            return $l_return;
        }
    }

    /**
     * Method for finding an layer3-net in idoit.
     *
     * @param   integer $p_type
     * @param   string  $p_address
     * @param   string  $p_subnetmask
     * @param   string  $p_range_from
     * @param   string  $p_range_to
     *
     * @return  mixed  May be an array or boolean false.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function does_layer3_exist_in_idoit($p_type, $p_address, $p_subnetmask, $p_range_from, $p_range_to)
    {
        $l_key = $p_type . '_' . $p_address . '_' . $p_subnetmask . '_' . $p_range_from . '_' . $p_range_to;

        if (isset($this->m_net_cache[$l_key])) {
            $this->m_log->debug('Layer 3 net ' . $l_key . ' exists in cache.');

            return $this->m_net_cache[$l_key];
        }
        if (isset($this->m_missing_net_cache[$l_key])) {
            $this->m_log->debug('Layer 3 net ' . $l_key . ' does not exist in cache.');

            return false;
        }

        $condition = ' AND isys_cats_net_list__address = ' . $this->convert_sql_text($p_address) . '
          AND isys_cats_net_list__mask = ' . $this->convert_sql_text($p_subnetmask) . '
          AND isys_cats_net_list__address_range_from = ' . $this->convert_sql_text($p_range_from) . '
          AND isys_cats_net_list__address_range_to = ' . $this->convert_sql_text($p_range_to);

        if ($p_type === C__CATS_NET_TYPE__IPV6) {
            // Check with long and short ipv6 notation
            $condition = ' AND (
                isys_cats_net_list__address = ' . $this->convert_sql_text($p_address) . ' OR 
                isys_cats_net_list__address = INET6_NTOA(INET6_ATON(' . $this->convert_sql_text($p_address) . '))
            )
            AND (
                isys_cats_net_list__mask = ' . $this->convert_sql_text($p_subnetmask) . ' OR 
                isys_cats_net_list__mask = INET6_NTOA(INET6_ATON(' . $this->convert_sql_text($p_subnetmask) . '))
            )
            AND (
                isys_cats_net_list__address_range_from = ' . $this->convert_sql_text($p_range_from) . ' OR 
                isys_cats_net_list__address_range_from = INET6_NTOA(INET6_ATON(' . $this->convert_sql_text($p_range_from) . '))
            )
            AND (
                isys_cats_net_list__address_range_to = ' . $this->convert_sql_text($p_range_to) . ' OR 
                isys_cats_net_list__address_range_to = INET6_NTOA(INET6_ATON(' . $this->convert_sql_text($p_range_to) . '))
            )';
        }

        $l_sql = 'SELECT * FROM isys_obj
			INNER JOIN isys_cats_net_list ON isys_cats_net_list__isys_obj__id = isys_obj__id
			WHERE isys_cats_net_list__isys_net_type__id = ' . $this->convert_sql_int($p_type) . $condition;
        $l_query = $this->m_db->query($l_sql);
        if ($l_row = $this->m_db->fetch_row_assoc($l_query)) {
            $this->m_net_cache[$l_key] = $l_row;
        }
        $this->m_db->free_result($l_query);

        if ($l_row === false) {
            $this->m_missing_net_cache[$l_key] = null;
        }

        return $l_row;
    }

    /**
     * Method for collecting and preparing the ports, which shall be connected.
     *
     * @return  isys_jdisc_dao_network
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function create_port_connections()
    {
        $l_already_prepared = [];

        $this->m_dao = isys_cmdb_dao_jdisc::instance($this->m_db);
        $l_port_dao = isys_cmdb_dao_category_g_network_port::instance($this->m_db);

        $l_sql_port = 'SELECT 
            isys_catg_port_list__id, 
            isys_catg_port_list__title,
            isys_catg_port_list__mac,
            isys_catg_port_list__isys_obj__id,
            isys_cable_connection__isys_obj__id, 
            localConnector.isys_catg_connector_list__id, 
            localConnector.isys_catg_connector_list__isys_cable_connection__id,
            localObject.isys_obj__isys_obj_type__id as localObjectTypeId,
            localObject.isys_obj__title as localObjectTitle,
            externalObject.isys_obj__title as externalObjectTitle,
            externalConnector.isys_catg_connector_list__title as externalConnectorTitle,
            CONCAT(
                isys_catg_port_list__mac, \'|\', 
                isys_catg_port_list__isys_port_duplex__id, \'|\', 
                isys_catg_port_list__isys_port_type__id, \'|\', 
                isys_catg_port_list__port_speed_value, \'|\', 
                isys_catg_port_list__isys_port_speed__id, \'|\', 
                isys_catg_port_list__state_enabled, \'|\', 
                isys_catg_port_list__description
            ) AS comparestring
			FROM isys_catg_port_list
                INNER JOIN isys_obj as localObject ON localObject.isys_obj__id = isys_catg_port_list__isys_obj__id
                LEFT JOIN isys_catg_connector_list as localConnector ON localConnector.isys_catg_connector_list__id = isys_catg_port_list__isys_catg_connector_list__id
                LEFT JOIN isys_catg_connector_list as externalConnector ON 
                    localConnector.isys_catg_connector_list__isys_cable_connection__id = externalConnector.isys_catg_connector_list__isys_cable_connection__id AND 
                    externalConnector.isys_catg_connector_list__id != localConnector.isys_catg_connector_list__id
			    LEFT JOIN isys_obj as externalObject ON externalObject.isys_obj__id = externalConnector.isys_catg_connector_list__isys_obj__id
                LEFT JOIN isys_cable_connection ON isys_cable_connection__id = localConnector.isys_catg_connector_list__isys_cable_connection__id
			WHERE TRUE ';
        $l_sql_condition1 = ' AND isys_catg_port_list__mac = ';
        $l_sql_condition2 = ' AND isys_catg_port_list__title = ';

        // Iterate through our port-array...
        // These ports are created ports
        if (is_array($this->m_port_array) && count($this->m_port_array) > 0) {
            foreach ($this->m_port_array as $l_port_id => $l_port_data) {
                $l_local_port_row = null;
                // Update local port
                if (isset($l_port_data['update']) && isset($l_port_data['comparestring']) && $l_port_data['update'] > 0) {
                    $l_sql_update_check = $l_sql_port . ' AND isys_catg_port_list__id = ' . $this->convert_sql_id($l_port_data['update']);
                    $l_local_port_row = $l_port_dao->retrieve($l_sql_update_check)
                        ->get_row();
                    $l_mac = substr($l_port_data['comparestring'], 0, strpos($l_port_data['comparestring'], '|'));

                    // Check if there are is a difference between the port from jdisc and i-doit
                    if ($l_port_data['comparestring'] !== $l_local_port_row['comparestring']) {
                        $l_data = explode('|', $l_port_data['comparestring']);

                        $this->m_log->debug('>> Updating existing Port with MAC: "' . $l_mac . '"!');
                        // Update this port
                        $this->update_port($l_local_port_row['isys_catg_port_list__id'], $l_data[1], $l_data[2], $l_data[3], $l_data[4], $l_data[5], $l_data[6]);
                    }
                }

                // And retrieve all the port connections.
                $l_sql = 'SELECT port1.ifphysaddress AS mac1, port1.ifdescr AS port_title1, port1.id AS port1id, port2.ifphysaddress AS mac2, port2.ifdescr AS port_title2, port2.id AS port2id
					FROM macmacrelation
					LEFT JOIN mac AS port1 ON port1.id = macid1
					LEFT JOIN mac AS port2 ON port2.id = macid2
					WHERE macid2 = ' . $this->convert_sql_id($l_port_id) . ' OR macid1 = ' . $this->convert_sql_id($l_port_id) . ';';

                $l_res = $this->fetch($l_sql);

                if ($this->m_pdo->num_rows($l_res) === 1) {
                    $l_row = $this->m_pdo->fetch_row_assoc($l_res);
                    $this->m_pdo->free_result($l_res);

                    // Load the i-doit port data.
                    //					$l_local_port_condition = 'AND isys_catg_port_list__mac = ' . $this->convert_sql_text($l_row['mac1']).
                    //						' AND isys_catg_port_list__title = ' . $this->convert_sql_text($l_row['port_title1']);
                    //					$l_external_port_condition = 'AND isys_catg_port_list__mac = ' . $this->convert_sql_text($l_row['mac2']).
                    //						' AND isys_catg_port_list__title = ' . $this->convert_sql_text($l_row['port_title2']);

                    if ($l_row['port1id'] == $l_port_id) {
                        $l_sql_local_port = $l_sql_port . $l_sql_condition1 . $this->convert_sql_text($l_row['mac1']) . $l_sql_condition2 .
                            $this->convert_sql_text($l_row['port_title1']);
                        $l_sql_external_port = $l_sql_port . $l_sql_condition1 . $this->convert_sql_text($l_row['mac2']) . $l_sql_condition2 .
                            $this->convert_sql_text($l_row['port_title2']);
                        $l_destination_mac_id = $l_row['mac2'];
                    } else {
                        $l_sql_external_port = $l_sql_port . $l_sql_condition1 . $this->convert_sql_text($l_row['mac1']) . $l_sql_condition2 .
                            $this->convert_sql_text($l_row['port_title1']);
                        $l_sql_local_port = $l_sql_port . $l_sql_condition1 . $this->convert_sql_text($l_row['mac2']) . $l_sql_condition2 .
                            $this->convert_sql_text($l_row['port_title2']);
                        $l_destination_mac_id = $l_row['mac1'];
                    }

                    if ($l_local_port_row === null) {
                        $l_local_port_row = $l_port_dao->retrieve($l_sql_local_port)
                            ->get_row();
                    }
                    $l_external_port_row = $l_port_dao->retrieve($l_sql_external_port)
                        ->get_row();

                    //					$l_local_port_row = $l_port_dao->get_data(null, null, $l_local_port_condition)->get_row();
                    //					$l_external_port_row = $l_port_dao->get_data(null, null, $l_external_port_condition)->get_row();

                    if (isset($l_already_prepared[$l_local_port_row['isys_catg_port_list__id']]) ||
                        isset($l_already_prepared[$l_external_port_row['isys_catg_port_list__id']]) ||
                        ($l_local_port_row['isys_catg_connector_list__isys_cable_connection__id'] > 0 &&
                            $l_external_port_row['isys_catg_connector_list__isys_cable_connection__id'] > 0 &&
                            $l_local_port_row['isys_catg_connector_list__isys_cable_connection__id'] ===
                            $l_external_port_row['isys_catg_connector_list__isys_cable_connection__id'])) {
                        // Skip it, ports are already connected
                        continue;
                    }

                    $l_already_prepared[$l_local_port_row['isys_catg_port_list__id']] = true;
                    $l_already_prepared[$l_external_port_row['isys_catg_port_list__id']] = true;

                    if (is_array($l_external_port_row) && is_array($l_local_port_row)) {
                        // Both ports exists
                        //$this->m_log->debug('>> Preparing the port connection for "' . $l_port_data['mac'] . '" (to "' . $l_row['mac2'] . '") !');

                        $this->connect_ports($l_local_port_row, $l_external_port_row);
                    } else {
                        $this->m_log->debug('>> Destination port for "' . $l_port_data['mac'] . '" could not be found (searched for "' . $l_destination_mac_id . '")!');
                    }
                } elseif ($this->m_pdo->num_rows($l_res) > 1) {
                    $l_mac_addresses = '';
                    while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
                        if ($l_row['mac1'] == $l_port_id) {
                            $l_mac_addresses .= $l_row['mac2'] . ', ';
                        } else {
                            $l_mac_addresses .= $l_row['mac1'] . ', ';
                        }
                    }
                    $this->m_pdo->free_result($l_res);

                    $l_mac_addresses = rtrim($l_mac_addresses, ', ');
                    $this->m_log->debug('> Connection for "' . $l_port_data['mac'] . '" skipped. Because there are several connections to following mac-addresses: ' .
                        $l_mac_addresses);
                } else {
                    $this->m_log->debug('> No connection for "' . $l_port_data['mac'] . '"');
                }

                $this->m_pdo->free_result($l_res);
            }
        }

        $this->apply_update();

        return $this;
    }

    /**
     * Method for connecting the two given ports (very "oldschool" via the DAOs "sync()" method).
     *
     * @param   array $p_local_port
     * @param   array $p_external_port
     *
     * @return  isys_jdisc_dao_network
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function connect_ports($p_local_port, $p_external_port)
    {
        /**
         * @var     $l_dao     isys_cmdb_dao_cable_connection
         * @var     $l_dao_rel isys_cmdb_dao_category_g_relation
         */
        $l_dao = isys_cmdb_dao_cable_connection::instance($this->m_db);
        $l_dao_rel = isys_cmdb_dao_category_g_relation::instance($this->m_db);

        // REMOVE cable connection
        if ($p_local_port['isys_catg_connector_list__isys_cable_connection__id'] > 0) {
            $l_dao->delete_cable_connection($p_local_port['isys_catg_connector_list__isys_cable_connection__id']);
        }
        if ($p_external_port['isys_catg_connector_list__isys_cable_connection__id'] > 0) {
            $l_dao->delete_cable_connection($p_external_port['isys_catg_connector_list__isys_cable_connection__id']);
        }

        $l_cable_obj = (($p_local_port['isys_cable_connection__isys_obj__id']) ? $p_local_port['isys_cable_connection__isys_obj__id'] : (($p_external_port['isys_cable_connection__isys_obj__id']) ? $p_external_port['isys_cable_connection__isys_obj__id'] : null));

        if ($l_cable_obj === null || !$l_dao->obj_exists($l_cable_obj)) {
            $l_connection_id = $l_dao->add_cable_connection($l_dao->recycle_cable(null));
        } else {
            $l_connection_id = $l_dao->add_cable_connection($l_cable_obj);
        }

        $l_relation_type = $l_dao_rel->get_relation_type_by_const('C__RELATION_TYPE__NETWORK_PORT');
        $fromChanges = $p_local_port['externalObjectTitle'] . ' > ' . $p_local_port['externalConnectorTitle'];
        $toChanges = $p_external_port['localObjectTitle'] . ' > ' . $p_external_port['isys_catg_port_list__title'];
        $objId = $p_local_port['isys_catg_port_list__isys_obj__id'];
        $objTypeId = $p_local_port['localObjectTypeId'];

        if ($l_relation_type) {
            $l_connector_puffer = $p_local_port;
            $p_local_port = $p_external_port;
            $p_external_port = $l_connector_puffer;
        }

        $l_rel_id = $l_dao_rel->create_relation(
            "isys_catg_connector_list",
            $p_local_port["isys_catg_connector_list__id"],
            $p_external_port["isys_catg_port_list__isys_obj__id"],
            $p_local_port["isys_catg_port_list__isys_obj__id"],
            $l_relation_type
        );

        $l_update = "UPDATE isys_catg_connector_list SET
            isys_catg_connector_list__isys_cable_connection__id = " . $this->convert_sql_id($l_connection_id) . ",
            isys_catg_connector_list__isys_catg_relation_list__id = " . $this->convert_sql_id($l_rel_id) . "
            WHERE isys_catg_connector_list__id = " . $this->convert_sql_id($p_local_port['isys_catg_connector_list__id']) . "
            OR isys_catg_connector_list__id = " . $this->convert_sql_id($p_external_port['isys_catg_connector_list__id']) . ";";

        if ($this->update($l_update)) {
            $this->m_log->info('Okay! Port "' . $p_local_port['isys_catg_port_list__mac'] . ' was successfully connected to "' . $p_external_port['isys_catg_port_list__mac'] .
                '"');

            $changes = serialize([
                'isys_cmdb_dao_category_g_network_port::assigned_connector' => [
                    'from' => $fromChanges,
                    'to' => $toChanges
                ]
            ]);

            isys_event_manager::getInstance()
                ->triggerCMDBEvent(
                    'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                    null,
                    $objId,
                    $objTypeId,
                    'LC__CMDB__CATG__NETWORK_TREE_CONFIG_PORT',
                    $changes
                );
        } else {
            $this->m_log->error('Something did not work - Port "' . $p_local_port['isys_catg_port_list__mac'] . ' could not be connected with "' .
                $p_external_port['isys_catg_port_list__mac'] . '"');
        }

        return $this;
    }

    /**
     * Method for assigning the network ports and logical ports to hostaddresses
     *
     * @param int $l_object_id
     *
     * @return  isys_jdisc_dao_network
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function update_ip_port_assignments($l_object_id)
    {
        /**
         * Typehinting
         *
         * @var $l_dao_ip       isys_cmdb_dao_category_g_ip
         * @var $l_dao_port     isys_cmdb_dao_category_g_network_port
         * @var $l_dao_log_port isys_cmdb_dao_category_g_network_ifacel
         */
        $l_dao_ip = isys_cmdb_dao_category_g_ip::instance($this->m_db);
        $l_dao_port = isys_cmdb_dao_category_g_network_port::instance($this->m_db);
        $l_dao_log_port = isys_cmdb_dao_category_g_network_ifacel::instance($this->m_db);

        if (is_array($this->m_port_array) && count($this->m_port_array) > 0) {
            foreach ($this->m_port_array as $l_port) {
                if (!is_array($l_port['ip'])) {
                    continue;
                }

                if (!empty($l_port['mac'])) {
                    $l_condition = 'AND isys_catg_port_list__mac = ' . $this->convert_sql_text($l_port['mac']);
                } else {
                    $l_condition = 'AND isys_obj__title = ' . $this->convert_sql_text($l_port['obj_name']) . ' ' . 'AND isys_obj__isys_obj_type__id = ' .
                        $this->convert_sql_id($l_port['obj_type']) . ' ' . 'AND isys_catg_port_list__title = ' . $this->convert_sql_text($l_port['value']);
                }

                $l_external_port_res = $l_dao_port->get_data(null, null, $l_condition);
                if ($l_external_port_res->num_rows() > 1) {
                    //Have to check with title and not with the mac
                    $l_condition = 'AND isys_obj__title = ' . $this->convert_sql_text($l_port['obj_name']) . ' ' . 'AND isys_obj__isys_obj_type__id = ' .
                        $this->convert_sql_id($l_port['obj_type']) . ' ' . 'AND isys_catg_port_list__title = ' . $this->convert_sql_text($l_port['value']);
                    $l_external_port_row = $l_dao_port->get_data(null, null, $l_condition)
                        ->get_row();
                } else {
                    $l_external_port_row = $l_external_port_res->get_row();
                }

                foreach ($l_port['ip'] as $l_ip) {
                    $l_ip_data = $l_dao_ip->get_data(null, $l_object_id, 'AND ipv4.isys_cats_net_ip_addresses_list__title = \'' . $this->m_db->escape_string($l_ip) . '\'')
                        ->get_row();
                    if ($l_ip_data['isys_catg_port_list__id'] !== $l_external_port_row['isys_catg_port_list__id']) {
                        $this->detach_port_2_ip($l_ip_data['isys_catg_ip_list__id'], 'log_port');
                        $this->assign_port_2_ip($l_ip_data['isys_catg_ip_list__id'], $l_external_port_row['isys_catg_port_list__id'], 'port');
                    }
                }
            }
        }

        if (is_array($this->m_logical_port_array) && count($this->m_logical_port_array) > 0) {
            foreach ($this->m_logical_port_array as $l_port) {
                $l_external_port_row = null;
                $l_loopback_tunnel = false;

                if (strpos($l_port['value'], 'Loopback') !== false || strpos($l_port['value'], 'Tunnel') !== false) {
                    $l_loopback_tunnel = true;

                    if (!is_array($l_port['ip']) || empty($l_port['ip'])) {
                        $l_port['ip'] = ['127.0.0.1'];
                    }
                }

                if (!$l_loopback_tunnel) {
                    if (!is_array($l_port['ip'])) {
                        continue;
                    }
                }

                if ($l_loopback_tunnel === false) {
                    if (!empty($l_port['mac'])) {
                        $l_condition = 'AND isys_catg_log_port_list.isys_catg_log_port_list__title = ' . $this->convert_sql_text($l_port['value']) .
                            ' AND isys_catg_log_port_list.isys_catg_log_port_list__mac = ' . $this->convert_sql_text($l_port['mac']);
                    } else {
                        $l_condition = 'AND isys_catg_log_port_list.isys_catg_log_port_list__title = ' . $this->convert_sql_text($l_port['value']);
                    }

                    $l_external_port_res = $l_dao_log_port->get_data(null, $l_object_id, $l_condition);
                    if ($l_external_port_res->num_rows() > 1) {
                        //Have to check with title and not with the mac
                        $l_condition = 'AND isys_catg_log_port_list.isys_catg_log_port_list__title = ' . $this->convert_sql_text($l_port['value']);
                        $l_external_port_row = $l_dao_log_port->get_data(null, $l_object_id, $l_condition)
                            ->get_row();
                    } else {
                        $l_external_port_row = $l_external_port_res->get_row();
                    }
                }

                if (isset($l_port['ip']) && is_array($l_port['ip'])) {
                    foreach ($l_port['ip'] as $l_ip) {
                        $l_ip_data = $l_dao_ip->get_data(null, $l_object_id, 'AND ipv4.isys_cats_net_ip_addresses_list__title = \'' . $this->m_db->escape_string($l_ip) . '\'')
                            ->get_row();

                        if ($l_loopback_tunnel && empty($l_external_port_row)) {
                            // IP is a loopback or tunnel
                            $l_external_port_row = $l_dao_log_port->get_data(
                                null,
                                $l_ip_data['isys_obj__id'],
                                'AND isys_catg_log_port_list.isys_catg_log_port_list__title = ' . $this->convert_sql_text($l_port['value'])
                            )
                                ->get_row();
                        }

                        if ($l_ip_data['isys_catg_log_port_list__id'] !== $l_external_port_row['isys_catg_log_port_list__id']) {
                            $this->detach_port_2_ip($l_ip_data['isys_catg_ip_list__id'], 'port');
                            $this->assign_port_2_ip($l_ip_data['isys_catg_ip_list__id'], $l_external_port_row['isys_catg_log_port_list__id'], 'log_port');
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Method for access point data for the specific category access point
     *
     * @param      $p_id
     * @param bool $p_raw
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@synetics.de>
     */
    public function get_access_point_by_device($p_id, $p_raw = false)
    {
        $l_return = [];

        $l_dao = isys_cmdb_dao_jdisc::instance($this->m_db);

        if (count($this->m_port_array) === 0) {
            $l_new_id = $l_dao->get_last_id_from_table('isys_catg_port_list');
        } else {
            $l_keys = array_keys($this->m_port_array);
            $l_new_id = $l_keys[count($l_keys) - 1];
        }

        $l_sql = 'SELECT * FROM wlancipheralgorithmlookup';
        $l_res = $this->fetch($l_sql);
        $l_encryption_data = [];
        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
            $l_encryption_data[$l_row['id']] = [
                'id'    => isys_import_handler_cmdb::check_dialog('isys_wlan_encryption', $l_row['name']),
                'title' => $l_row['name']
            ];
        }
        $this->m_pdo->free_result($l_res);

        $l_sql = 'SELECT * FROM wlanauthalgorithmlookup';
        $l_res = $this->fetch($l_sql);
        $l_auth_data = [];
        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
            $l_auth_data[$l_row['id']] = [
                'id'    => isys_import_handler_cmdb::check_dialog('isys_wlan_auth', $l_row['name']),
                'title' => $l_row['name']
            ];
        }
        $this->m_pdo->free_result($l_res);

        $l_sql = 'SELECT w.*, clm.id AS clientmacid, clm.ifdescr AS clientdescr,
				clm.ifphysaddress AS clientmac, d.name AS devicetitle, apm.ifdescr,
				wca.id AS cipherid, wca.name AS ciphername, waa.id AS authid, waa.name
				AS authname ' . 'FROM wlanbssmacrelation AS wbm ' . 'LEFT JOIN wlan AS w ON w.id = wbm.wlanid ' .        // WLAN
            'LEFT JOIN wlancipheralgorithmlookup AS wca ON wca.id = w.cipheralgorithm ' . 'LEFT JOIN wlanauthalgorithmlookup AS waa ON waa.id = w.authalgorithm ' .
            'LEFT JOIN mac AS apm ON apm.id = wbm.bssmacid ' . 'LEFT JOIN device AS d ON d.id = apm.deviceid ' .    // ACCESS POINT OBJECT
            'LEFT JOIN mac AS clm ON clm.id = wbm.macid ' . 'WHERE d.id = ' . $this->convert_sql_id($p_id);

        $l_dialog_data = [
            'encryption' => $l_encryption_data,
            'auth'       => $l_auth_data
        ];

        $l_res = $this->fetch($l_sql);

        $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($l_res) . ' rows for WLANs');

        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
            $l_new_id++;
            if ($p_raw === true) {
                $l_return[] = $l_row;
            } else {
                $l_return[] = $this->prepare_access_point($l_row, $l_dialog_data);
                $this->m_port_array[$l_row['clientmacid']] = [
                    'id'       => $l_new_id,
                    'value'    => $l_row['clientdescr'],
                    'mac'      => $l_row['clientmac'],
                    'obj_name' => $this->m_idoit_obj_name,
                    'obj_type' => $this->m_idoit_obj_type
                ];
            }
        }
        $this->m_pdo->free_result($l_res);

        if ($p_raw === true || count($l_return) === 0) {
            return $l_return;
        } else {
            return [
                C__DATA__TITLE      => isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__CATS__ACCESS_POINT'),
                'const'             => 'C__CATS__ACCESS_POINT',
                'category_type'     => C__CMDB__CATEGORY__TYPE_SPECIFIC,
                'category_entities' => $l_return
            ];
        }
    }

    /**
     * Prepares import array for import
     *
     * @param $p_data
     * @param $p_dialog_data
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@synetics.de>
     */
    public function prepare_access_point($p_data, $p_dialog_data)
    {
        //$this->m_log->debug('>> Preparing access point array');

        return [
            'data_id'    => null,
            'properties' => [
                'title'         => [
                    'tag'   => 'title',
                    'value' => $p_data['ifdescr'],
                    'title' => 'LC__CMDB__CATG__TITLE'
                ],
                'channel'       => [
                    'tag'   => 'channel',
                    'value' => $p_data['channelnumber'],
                    'title' => 'LC__CMDB__CATS__ACCESS_POINT_CHANNEL'
                ],
                'ssid'          => [
                    'tag'   => 'ssid',
                    'value' => $p_data['ssid'],
                    'title' => 'LC__CMDB__CATS__ACCESS_POINT_SSID'
                ],
                'auth'          => [
                    'tag'        => 'auth',
                    'value'      => $p_data['authname'],
                    'id'         => $p_dialog_data['auth'][$p_data['authid']]['id'],
                    'title_lang' => $p_data['authname'],
                    'title'      => 'LC__CMDB__CATS__ACCESS_POINT_AUTH'
                ],
                'encryption_id' => [
                    'tag'        => 'encryption_id',
                    'value'      => $p_data['ciphername'],
                    'id'         => $p_dialog_data['encryption'][$p_data['encryption']['id']]['id'],
                    'title_lang' => $p_data['ciphername'],
                    'title'      => 'LC__CMDB__CATS__ACCESS_POINT_ENCRYPTION'
                ]
            ]
        ];
    }

    /**
     * Sets current vlanconfiguration
     *
     * @return bool
     */
    public function set_vlan_configuration()
    {
        try {
            $l_result_set = $this->m_pdo->query('SELECT intvalue FROM discoverysetting WHERE name = ' . $this->convert_sql_text('vlanIdentificationMethod'));
            if ($this->m_pdo->num_rows($l_result_set) > 0) {
                $l_arr = $this->m_pdo->fetch_row_assoc($l_result_set);
                self::$m_vlanconfiguration = (($l_arr['intvalue'] === 0) ? false : true);
            }
            $this->m_pdo->free_result($l_result_set);
        } catch (Exception $e) {
            self::$m_vlanconfiguration = false;
            // default value will be used because value 'vlanIdentificationMethod' does not exist in table 'discoverysetting'
        }
    }

    /**
     * Prepares physical ports from ports filter
     *
     * @param $p_last_key
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function prepare_ports_from_filter($p_last_key)
    {
        $l_return = [];

        foreach ($this->m_port_filter_physical as $l_row) {
            $p_last_key++;

            if ($this->m_import_vlans && $l_row['ifphysaddress'] !== '') {
                $this->m_layer2_net_array[] = $l_row['id'] . '$$' . $this->get_current_object_id() . '$$' . $l_row['ifphysaddress'] . '$$' . $l_row['ifdescr'];
            }

            $l_return[$p_last_key] = $this->prepare_ports($l_row, $p_last_key);
            $this->m_port_array[$l_row['id']] = [
                'id'       => $p_last_key,
                'value'    => $l_row['ifdescr'],
                'mac'      => $l_row['ifphysaddress'],
                'obj_name' => $this->m_idoit_obj_name,
                'obj_type' => $this->m_idoit_obj_type
            ];
        }

        return $l_return;
    }

    /**
     * Prepares logical ports from port filter
     *
     * @param $p_last_key
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function prepare_logical_ports_from_filter($p_last_key)
    {
        $l_return = [];

        foreach ($this->m_port_filter_logical as $l_row) {
            $p_last_key++;

            if ($this->m_import_vlans && $l_row['ifphysaddress'] !== '') {
                $this->m_layer2_net_array2[] = $l_row['id'] . '$$' . $this->get_current_object_id() . '$$' . $l_row['ifphysaddress'] . '$$' . $l_row['ifdescr'];
            }

            $l_return[$p_last_key] = $this->prepare_logical_ports($l_row, $p_last_key);
            $this->m_logical_port_array[$l_row['id']] = [
                'id'       => $p_last_key,
                'value'    => $l_row['ifdescr'],
                'mac'      => $l_row['ifphysaddress'],
                'obj_name' => $this->m_idoit_obj_name,
                'obj_type' => $this->m_idoit_obj_type
            ];
        }

        return $l_return;
    }

    /**
     * Gets vrrp addresses
     *
     * @return array
     */
    public function get_vrrp_addresses()
    {
        return $this->m_vrrp_addresses;
    }

    /**
     * Gets assigned cluster services for the device
     *
     * @param $p_deviceid
     *
     * @return array|bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_assigned_clusterservice($p_deviceid)
    {
        $l_sql = 'SELECT id FROM clusterservice WHERE deviceid = ' . $this->convert_sql_id($p_deviceid);
        $l_res = $this->fetch($l_sql);
        if ($this->m_pdo->num_rows($l_res) > 0) {
            $l_return = [];
            while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
                $l_return[] = $l_row['id'];
            }
            $this->m_pdo->free_result($l_res);

            return $l_return;
        }

        return false;
    }

    /**
     * Gets all port to interface connections
     *
     * @return array
     */
    public function get_assigned_network_interfaces()
    {
        return $this->m_network_interfaces_connection;
    }

    /**
     * Creates connections between port and interfaces
     *
     * @param $p_jdisc_to_idoit_objects
     *
     * @return isys_jdisc_dao_network
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function create_network_interface_connections($p_obj_id)
    {
        if (count($this->m_network_interfaces_connection) > 0) {
            foreach ($this->m_network_interfaces_connection as $l_device_id => $l_data) {
                if ($p_obj_id > 0) {
                    foreach ($l_data as $l_port_id => $l_interface_id) {
                        $l_idoit_interface_id = 0;

                        if (defined('C__CATG__NETWORK_PORT') &&
                            isset(self::$m_port_map[C__CATG__NETWORK_PORT][$this->m_port_array[$l_port_id]['value'] . '|' . $p_obj_id . '|' .
                            $this->m_port_array[$l_port_id]['mac']])) {
                            $l_idoit_port_id = self::$m_port_map
                            [C__CATG__NETWORK_PORT][$this->m_port_array[$l_port_id]['value'] . '|' . $p_obj_id . '|' . $this->m_port_array[$l_port_id]['mac']]
                            ['id'];
                        } else {

                            /* @note This should be useless, because the port should be found via $m_port_map */
                            $l_port_data_sql = 'SELECT isys_catg_port_list__id FROM isys_catg_port_list ' .
                                'INNER JOIN isys_obj ON isys_obj__id = isys_catg_port_list__isys_obj__id ' . 'WHERE isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' ' .
                                'AND isys_catg_port_list__title = ' . $this->convert_sql_text($this->m_port_array[$l_port_id]['value']);
                            $l_idoit_port_id = $this->m_dao->retrieve($l_port_data_sql)
                                ->get_row_value('isys_catg_port_list__id');
                        }

                        // Check if interface exists
                        if ($l_interface_id > 0) {
                            if (isset($this->m_network_interfaces[$l_interface_id]) && is_array($this->m_network_interfaces[$l_interface_id])) {
                                $l_interface_data_sql = 'SELECT isys_catg_netp_list__id FROM isys_catg_netp_list ' . 'WHERE isys_catg_netp_list__isys_obj__id = ' .
                                    $this->convert_sql_id($p_obj_id) . ' ' . 'AND isys_catg_netp_list__title = ' .
                                    $this->convert_sql_text($this->m_network_interfaces[$l_interface_id]['value']);
                                $l_idoit_interface_id = $this->m_dao->retrieve($l_interface_data_sql)
                                    ->get_row_value('isys_catg_netp_list__id');

                                /* @note Just in case the interface does not exist somehow: */
                                /*
                                if (!$l_idoit_interface_id)
                                {
                                    $this->m_dao->update(
                                        'INSERT INTO isys_catg_netp_list SET isys_catg_netp_list__isys_obj__id = \'' .$p_jdisc_to_idoit_objects[$l_device_id].'\', '.
                                        'isys_catg_netp_list__title = \'' . $this->m_network_interfaces[$l_interface_id]['value'] . '\', '.
                                        'isys_catg_netp_list__serial = \''.$this->m_network_interfaces[$l_interface_id]['value'].'\''
                                    );
                                    $l_idoit_interface_id = $this->m_dao->get_last_insert_id();
                                }
                                */
                            }
                        }

                        if ($l_idoit_port_id > 0 && $l_idoit_interface_id > 0) {
                            $l_update = 'UPDATE isys_catg_port_list SET isys_catg_port_list__isys_catg_netp_list__id = ' . $this->convert_sql_id($l_idoit_interface_id) . ' ' .
                                'WHERE isys_catg_port_list__id = ' . $this->convert_sql_id($l_idoit_port_id);
                            $this->update($l_update);
                        }
                    }
                }
            }
            $this->apply_update();
        }

        return $this;
    }

    public function set_additional_info($p_import_type_interfaces = 0)
    {
        $this->m_import_type_interfaces = $p_import_type_interfaces;

        $l_sql = 'SELECT id FROM interfacetypelookup WHERE id = ' . $this->convert_sql_id(60000);
        $l_res = $this->fetch($l_sql);
        if ($this->m_pdo->num_rows($l_res) > 0) {
            $this->m_allowed_imports['vrrpcluster'] = true;
        }

        $this->m_layer2_net_array = new isys_array();
        $this->m_layer2_net_array2 = new isys_array();
    }

    /**
     * Check if Port or logical Port already exists in the current object
     *
     * @param int    $p_type
     * @param int    $p_object_id
     * @param string $p_title
     * @param string $p_mac
     *
     * @return isys_array|null
     */
    public function does_port_already_exist($p_type, $p_object_id, $p_title, $p_mac = '')
    {
        return isset(self::$m_port_map[$p_type][$p_title . '|' . $p_object_id . '|' . $p_mac]) ? self::$m_port_map[$p_type][$p_title . '|' . $p_object_id . '|' .
        $p_mac] : null;
    }

    /**
     * Create port map cache
     */
    public function create_port_map($p_object_id, $p_type = null)
    {
        // This is if we want it returned directly
        if ($p_type !== null) {
            $l_return = new isys_array();
            $l_portsResource = $this->prepare_ports_for_cache_by_type($p_type, $p_object_id, false);

            if ($l_portsResource) {
                while ($l_row = $this->m_db->fetch_row_assoc($l_portsResource)) {
                    $l_return[$l_row['title']] = $l_row['id'];
                }
                $this->m_db->free_result($l_portsResource);
            }

            return $l_return;
        }

        self::$m_port_map = new isys_array();

        // Cache Network Ports,
        // Cache Logical Ports
        // and connectors to Port Map
        foreach (filter_defined_constants([
                     'C__CATG__NETWORK_LOG_PORT',
                     'C__CATG__NETWORK_PORT',
                     'C__CATG__CONTROLLER_FC_PORT'
                 ]) as $l_portType) {
            $l_portsResource = $this->prepare_ports_for_cache_by_type($l_portType, $p_object_id);

            if ($l_portsResource) {
                while ($l_row = $this->m_db->fetch_row_assoc($l_portsResource)) {
                    self::$m_port_map[$l_portType][$l_row['title']] = new isys_array([
                        'id'    => $l_row['id'],
                        'objid' => $l_row['objid'],
                        'vlans' => $l_row['vlans']
                    ]);
                }
                $this->m_db->free_result($l_portsResource);
            }
        }

        return true;
    }

    /**
     * Assign vlans to ports and logical ports
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @throws isys_exception_general
     *
     * @todo   Create a delta and delete vlan assignments if they are not attached to port anymore!!!! This does not happen right now!!
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function update_vlan_assignments($p_obj_id, $p_device_id)
    {
        if ($this->m_import_vlans) {
            // Get all macid from table mac for the specified devices
            $l_mac_condition = 'deviceid = ' . (int)$p_device_id . '';
            $l_res_mac = $this->fetch('SELECT id FROM mac WHERE ' . $l_mac_condition . ' LIMIT 1');
            $l_mac_count = $this->m_pdo->num_rows($l_res_mac);
            if ($l_mac_count == 0) {
                // do not update because there are no ports
                return;
            }

            /**
             * @var $l_dao_layer2_net isys_cmdb_dao_category_s_layer2_net
             */
            $l_dao_layer2_net = isys_cmdb_dao_category_s_layer2_net::instance($this->m_db);
            $l_res_check = null;
            $l_vlans = new isys_array();

            // Retrieve all vlans
            $l_query_vlan = 'SELECT isys_cats_layer2_net_list__isys_obj__id, isys_cats_layer2_net_list__ident, isys_obj__title
				FROM isys_cats_layer2_net_list INNER JOIN isys_obj ON isys_cats_layer2_net_list__isys_obj__id = isys_obj__id;';

            $l_vlan_res = $this->m_db->query($l_query_vlan);
            while ($l_vlan_row = $this->m_db->fetch_row_assoc($l_vlan_res)) {
                //$l_vlans[$l_vlan_row['isys_cats_layer2_net_list__ident']] = $l_vlan_row['isys_cats_layer2_net_list__isys_obj__id'];
                if (self::$m_vlanconfiguration === false) {
                    $l_vlans[$l_vlan_row['isys_cats_layer2_net_list__ident']] = $l_vlan_row['isys_cats_layer2_net_list__isys_obj__id'];
                } else {
                    $l_vlans[$l_vlan_row['isys_obj__title'] . '|' . $l_vlan_row['isys_cats_layer2_net_list__ident']] = $l_vlan_row['isys_cats_layer2_net_list__isys_obj__id'];
                }
            }
            $this->m_db->free_result($l_vlan_res);

            $this->m_log->debug('Starting updating vlan connections to ports.');

            /**
             * Create port cache if not existing
             */
            $this->create_port_map($p_obj_id);

            /**
             * Get all vlan relations for the selected ports
             */
            $l_query = 'SELECT v.vlanid, v.name AS vlan_title, vmr.macid, m.ifphysaddress AS mac, m.ifdescr AS port_title, m.deviceid FROM vlan AS v ' .
                'INNER JOIN vlanmacrelation AS vmr ON vmr.vlanid = v.id ' . 'INNER JOIN mac AS m ON m.id = vmr.macid ' . 'WHERE m.id IN (SELECT id FROM mac WHERE ' .
                $l_mac_condition . ');';
            $l_res_vlan = $this->fetch($l_query);

            if ($this->m_pdo->num_rows($l_res_vlan) > 0) {
                $this->m_log->debug(' Parsing ' . $this->m_pdo->num_rows($l_res_vlan) . ' VLAN to port relations..');

                while ($l_row_jd_vlan = $this->m_pdo->fetch_row_assoc($l_res_vlan)) {
                    $l_mac = $l_row_jd_vlan['mac'];
                    $l_deviceid = $l_row_jd_vlan['deviceid'];

                    if ($l_mac !== '' && $l_deviceid == $p_device_id) {
                        $l_vlanid = $l_row_jd_vlan['vlanid'];
                        $l_port_title = $l_row_jd_vlan['port_title'];

                        if (!isset($l_row_jd_vlan['vlan_title']) || trim($l_row_jd_vlan['vlan_title']) === '') {
                            $l_row_jd_vlan['vlan_title'] = $l_row_jd_vlan['vlanid'];
                        }

                        // Identify vlan by VLAN ID
                        if (self::$m_vlanconfiguration === false) {
                            $l_identifier = $l_row_jd_vlan['vlanid'];
                        } else {
                            $l_identifier = $l_row_jd_vlan['vlan_title'] . '|' . $l_row_jd_vlan['vlanid'];
                        }

                        /**
                         * @note DS: Saving one select query for -each- port by simply creating a port map with all ports identified by object, port-title and mac.
                         *           In addition, this port map holds all vlans in a comma-separated string list.
                         * @note VQH: Check key with port title|objectid|macaddress
                         */
                        if (defined('C__CATG__NETWORK_PORT') &&
                            isset(self::$m_port_map[C__CATG__NETWORK_PORT][$l_port_title . '|' . $p_obj_id . '|' . $l_mac])) {
                            $l_port = self::$m_port_map[C__CATG__NETWORK_PORT][$l_port_title . '|' . $p_obj_id . '|' . $l_mac];

                            /**
                             * Create a delta map array with all currently assigned vlans
                             * All vlans in this array get removed at the end
                             *
                             * @note DS: Delta map is too slow. Using str_replace instead to shrink the vlans in m_port_map. Removing the rest then.
                             */
                            //$l_deltamap = array_flip(explode(',', $l_port['vlans']));

                            if (isset($l_port['id']) && $l_port['id'] > 0) {
                                /**
                                 * Go further and do the REPLACE INTO only if vlan is not already assigned
                                 *
                                 * @note DS: This saves the forthcoming REPLACE INTO completly by checking wheather the vlan is already assigned or not
                                 *           And this by simply checking with a fast binary-safe string search function
                                 */
                                if (strpos(' ' . $l_port['vlans'], (string)$l_identifier)) {
                                    /** @note DS: Remove VLAN from port-map to not delete it afterwards and make forthcoming strpos checks faster */
                                    self::$m_port_map[C__CATG__NETWORK_PORT][$l_port_title . '|' . $p_obj_id . '|' . $l_mac]['vlans'] = str_replace(
                                        ',' .
                                        (string)$l_identifier . ',',
                                        ',',
                                        ',' . self::$m_port_map[C__CATG__NETWORK_PORT][$l_port_title . '|' . $p_obj_id . '|' . $l_mac]['vlans'] . ','
                                    );

                                    continue;
                                }

                                if (isset($l_vlans[$l_identifier])) {
                                    $l_object_id = $l_vlans[$l_identifier];
                                } else {
                                    // VLAN does not exist, so create it and push it to $l_vlans cache
                                    $l_object_id = $l_vlans[$l_identifier] = $l_dao_layer2_net->insert_new_obj(
                                        defined_or_default('C__OBJTYPE__LAYER2_NET'),
                                        false,
                                        $l_row_jd_vlan['vlan_title'],
                                        null,
                                        C__RECORD_STATUS__NORMAL
                                    );

                                    $this->m_log->debug('New layer2-net "' . $l_row_jd_vlan['vlan_title'] . '" created.');
                                    $l_dao_layer2_net->create($l_object_id, C__RECORD_STATUS__NORMAL, $l_vlanid);
                                }

                                if ($l_object_id > 0) {
                                    /**
                                     * @note DS: Forced "isys_catg_port_list__id and isys_cats_layer2_net_assigned_ports_list__isys_obj__id" to be a combined PRIMARY key
                                     *           That allows us to switch from INSERT INTO to REPLACE INTO to completely save one select query for -each- vlan in -each- port.
                                     */
                                    $this->update('REPLACE INTO isys_cats_layer2_net_assigned_ports_list (isys_catg_port_list__id, isys_cats_layer2_net_assigned_ports_list__isys_obj__id, isys_cats_layer2_net_assigned_ports_list__status) VALUES
                                            (' . $this->convert_sql_id($l_port['id']) . ',
                                            ' . $this->convert_sql_id($l_object_id) . ',
                                            ' . C__RECORD_STATUS__NORMAL . ');');
                                }
                            } // if port id > 0
                        } // if port
                        if (defined('C__CATG__NETWORK_LOG_PORT') &&
                            isset(self::$m_port_map[C__CATG__NETWORK_LOG_PORT][$l_port_title . '|' . $p_obj_id . '|' . $l_mac])) {
                            $l_port = self::$m_port_map[C__CATG__NETWORK_LOG_PORT][$l_port_title . '|' . $p_obj_id . '|' . $l_mac];

                            /**
                             * Create a delta map array with all currently assigned vlans
                             * All vlans in this array get removed at the end
                             */
                            //$l_deltamap = array_flip(explode(',', $l_port['vlans']));

                            if (isset($l_port['id']) && $l_port['id'] > 0) {
                                /**
                                 * Go further and do the REPLACE INTO only if vlan is not already assigned
                                 *
                                 * @note DS: This saves the forthcoming REPLACE INTO completly by checking wheather the vlan is already assigned or not
                                 *           And this by simply checking with a fast binary-safe string search function
                                 */
                                if (strpos(' ' . $l_port['vlans'], (string)$l_identifier)) {
                                    // Remove VLAN from port-map to not delete it afterwards
                                    self::$m_port_map[C__CATG__NETWORK_LOG_PORT][$l_port_title . '|' . $p_obj_id . '|' . $l_mac]['vlans'] = str_replace(
                                        ',' .
                                        (string)$l_identifier . ',',
                                        ',',
                                        ',' . self::$m_port_map[C__CATG__NETWORK_LOG_PORT][$l_port_title . '|' . $p_obj_id . '|' . $l_mac]['vlans'] . ','
                                    );

                                    continue;
                                }

                                if (isset($l_vlans[$l_identifier])) {
                                    $l_object_id = $l_vlans[$l_identifier];
                                } else {
                                    // VLAN does not exist, so create it and push it to $l_vlans cache
                                    $l_object_id = $l_vlans[$l_identifier] = $l_dao_layer2_net->insert_new_obj(
                                        defined_or_default('C__OBJTYPE__LAYER2_NET'),
                                        false,
                                        $l_row_jd_vlan['vlan_title'],
                                        null,
                                        C__RECORD_STATUS__NORMAL
                                    );

                                    $this->m_log->debug('New layer2-net "' . $l_row_jd_vlan['vlan_title'] . '" created.');
                                    $l_dao_layer2_net->create($l_object_id, C__RECORD_STATUS__NORMAL, $l_vlanid);
                                }

                                if ($l_object_id > 0) {
                                    /**
                                     * @note DS: Forced "isys_catg_log_port_list__id and isys_obj__id" to be a combined PRIMARY key
                                     *           Changed from INSERT INTO to REPLACE INTO afterwards to completely save one select query for -each- vlan in -each- port.
                                     */
                                    $this->update('REPLACE INTO isys_catg_log_port_list_2_isys_obj' .
                                        '(isys_catg_log_port_list__id,  isys_obj__id,  isys_catg_log_port_list_2_isys_obj__status) ' . 'VALUES ' . '(' .
                                        $this->convert_sql_id($l_port['id']) . ', ' . $this->convert_sql_id($l_object_id) . ', ' . C__RECORD_STATUS__NORMAL . ');');
                                }
                            }
                        } else {
                            // port does not exist
                        }
                    }
                } // while row = jdisc vlans
            } // if num_rows > 0

            $this->m_pdo->free_result($l_res_vlan);

            /**
             * Delete VLANs which are not assigned anymore
             */
            if (defined('C__CATG__NETWORK_PORT') &&
                isset(self::$m_port_map[C__CATG__NETWORK_PORT]) && is_array(self::$m_port_map[C__CATG__NETWORK_PORT])) {
                foreach (self::$m_port_map[C__CATG__NETWORK_PORT] as $l_port) {
                    // We only want to delete the vlan assignments of the object which has been imported
                    // Therefore we check if the object id is in the cache array $p_jdisc_to_idoit
                    if ($l_port['vlans'] && $l_port['id'] > 0 && $l_port['objid'] == $p_obj_id) {
                        $l_remove_list = array_map(function ($p_val) use ($l_vlans) {
                            return isset($l_vlans[$p_val]) && $l_vlans[$p_val] > 0 ? (int)$l_vlans[$p_val] : '0';
                        }, explode(',', trim($l_port['vlans'], ',')));

                        /* Cast all elements to integer to not run into SQL problems */
                        if (count($l_remove_list) > 0) {
                            $this->update('DELETE FROM isys_cats_layer2_net_assigned_ports_list ' . 'WHERE isys_catg_port_list__id = \'' . $l_port['id'] . '\' AND ' .
                                'isys_cats_layer2_net_assigned_ports_list__isys_obj__id IN (' . implode(',', $l_remove_list) . ')');
                        }

                        unset($l_remove_list);
                    }
                }
            }
            /**
             * Delete VLANs which are not assigned anymore
             */
            if (defined('C__CATG__NETWORK_LOG_PORT') &&
                isset(self::$m_port_map[C__CATG__NETWORK_LOG_PORT]) && is_array(self::$m_port_map[C__CATG__NETWORK_LOG_PORT])) {
                foreach (self::$m_port_map[C__CATG__NETWORK_LOG_PORT] as $l_port) {
                    // We only want to delete the vlan assignments of the object which we has been imported
                    // Therefore we check if the object id is in the cache array $p_jdisc_to_idoit
                    if ($l_port['vlans'] && $l_port['id'] > 0 && $l_port['objid'] == $p_obj_id) {
                        $l_remove_list = array_map(function ($p_val) use ($l_vlans) {
                            return isset($l_vlans[$p_val]) && $l_vlans[$p_val] > 0 ? (int)$l_vlans[$p_val] : '0';
                        }, explode(',', trim($l_port['vlans'], ',')));
                        if (count($l_remove_list) > 0) {
                            $this->update('DELETE FROM isys_catg_log_port_list_2_isys_obj ' . 'WHERE isys_catg_log_port_list__id = \'' . $l_port['id'] . '\' AND ' .
                                'isys_obj__id IN (' . implode(',', $l_remove_list) . ')');
                        }
                        unset($l_remove_list);
                    }
                }
            }

            return $this->apply_update();
        }

        return false;
    }

    /**
     * This method updates the only data which are considered in the import
     *
     * @param $p_port_id
     * @param $p_duplex_id
     * @param $p_port_type_id
     * @param $p_speed
     * @param $p_speed_unit
     * @param $p_active
     * @param $p_description
     */
    public function update_port($p_port_id, $p_duplex_id, $p_port_type_id, $p_speed, $p_speed_unit, $p_active, $p_description)
    {
        $l_sql = 'UPDATE isys_catg_port_list SET ' . 'isys_catg_port_list__isys_port_duplex__id = ' . $this->convert_sql_id($p_duplex_id) . ', ' .
            'isys_catg_port_list__isys_port_type__id = ' . $this->convert_sql_id($p_port_type_id) . ', ' . 'isys_catg_port_list__port_speed_value = \'' . $p_speed . '\', ' .
            'isys_catg_port_list__isys_port_speed__id = ' . $this->convert_sql_id($p_speed_unit) . ', ' . 'isys_catg_port_list__state_enabled = ' .
            $this->convert_sql_int($p_active) . ', ' . 'isys_catg_port_list__description = ' .
            $this->convert_sql_text($p_description) . ' ' . ' WHERE isys_catg_port_list__id = \'' . $p_port_id . '\'';
        $this->update($l_sql);
    }

    /**
     * Gets a prepared import array for category universal interface
     *
     * @param            $p_id
     * @param            $p_idoit_objects
     * @param bool|false $p_raw
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_universal_interface_by_device($p_id, $p_idoit_objects, $p_raw = false)
    {
        $l_return = [];

        if ($this->m_connector_types === null) {
            $this->map_connector_types();
        }

        $l_sql = 'SELECT ddc.fromdeviceid, ddc.todeviceid, LOWER(ddc.internalid) AS type, ddc.internalid AS title FROM devicedeviceconnection AS ddc
			WHERE ddc.connectortype = 20000 AND
			(ddc.todeviceid = ' . $this->convert_sql_id($p_id) . ' OR ddc.fromdeviceid = ' . $this->convert_sql_id($p_id) . ')';

        $l_res = $this->fetch($l_sql);

        $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($l_res) . ' rows');

        while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
            $l_connected_device_obj_id = $l_connected_device = 0;

            if ($p_raw === true) {
                $l_return[] = $l_row;
            } else {
                if (strpos($l_row['type'], 'monitor') !== false || strpos($l_row['type'], 'display') !== false) {
                    $l_type = $this->m_connector_types['monitor'];
                } elseif (strpos($l_row['type'], 'mouse') !== false) {
                    $l_type = $this->m_connector_types['mouse'];
                } elseif (strpos($l_row['type'], 'keyboard') !== false) {
                    $l_type = $this->m_connector_types['keyboard'];
                } elseif (strpos($l_row['type'], 'printer') !== false) {
                    $l_type = $this->m_connector_types['printer'];
                } else {
                    $l_type = $this->m_connector_types['other'];
                }

                if ($l_row['fromdeviceid'] == $p_id && isset($p_idoit_objects[$l_row['todeviceid']])) {
                    $l_connected_device_obj_id = $p_idoit_objects[$l_row['todeviceid']];
                } elseif ($l_row['todeviceid'] == $p_id && isset($p_idoit_objects[$l_row['fromdeviceid']])) {
                    $l_connected_device_obj_id = $p_idoit_objects[$l_row['fromdeviceid']];
                } elseif ($l_row['todeviceid'] > 0 && $l_row['fromdeviceid'] > 0) {
                    $l_connected_device_obj_id = isys_jdisc_dao_matching::instance()
                        ->get_object_id_by_device_id(($p_id != $l_row['fromdeviceid'] ? $l_row['fromdeviceid'] : $l_row['todeviceid']));
                }

                $l_return[] = $this->prepare_universal_interface($l_connected_device_obj_id, $l_type, $l_row['title']);
            }
        }
        $this->m_pdo->free_result($l_res);

        if ($p_raw === true || count($l_return) == 0) {
            return $l_return;
        } else {
            return [
                C__DATA__TITLE      => isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__CATG__UNIVERSAL_INTERFACE'),
                'const'             => 'C__CATG__UNIVERSAL_INTERFACE',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => $l_return
            ];
        }
    }

    /**
     * Cache port relevant data into temporary table
     *
     * @param $p_id
     * @param $p_data
     * @param $p_type
     *
     * @return $this|void
     */
    public function cache_data($p_id, $p_data, $p_type)
    {
        if (count($this->m_port_array)) {
            parent::cache_data($p_id, $this->m_port_array, self::C__CACHE__PORT);
            unset($this->m_port_array);
        }
        if (count($this->m_logical_port_array)) {
            parent::cache_data($p_id, $this->m_logical_port_array, self::C__CACHE__LOGICAL_PORT);
            unset($this->m_logical_port_array);
        }
        if (count($this->m_network_interfaces)) {
            parent::cache_data($p_id, $this->m_network_interfaces, self::C__CACHE__INTERFACE);
            unset($this->m_network_interfaces);
        }
        if (count($this->m_network_interfaces_connection)) {
            parent::cache_data($p_id, $this->m_network_interfaces_connection, self::C__CACHE__INTERFACE_CONNECTIONS);
            unset($this->m_network_interfaces_connection);
        }
        if (count($this->m_fc_port_array)) {
            parent::cache_data($p_id, $this->m_fc_port_array, self::C__CACHE__FC_PORT);
            unset($this->m_fc_port_array);
        }
    }

    /**
     * Load relevant data from temporary table
     *
     * @param $p_obj_id
     *
     * @throws Exception
     */
    public function load_cache($p_obj_id, $p_type = null)
    {
        $l_res = parent::load_cache(
            $p_obj_id,
            ' AND type IN (' . self::C__CACHE__PORT . ',' . self::C__CACHE__LOGICAL_PORT . ',' . self::C__CACHE__INTERFACE . ',' . self::C__CACHE__INTERFACE_CONNECTIONS .
            ',' . self::C__CACHE__FC_PORT . ')'
        );

        $this->m_port_array = new isys_array();
        $this->m_logical_port_array = new isys_array();
        $this->m_network_interfaces = new isys_array();
        $this->m_network_interfaces_connection = new isys_array();
        $this->m_fc_port_array = new isys_array();

        if ($this->m_db->num_rows($l_res) > 0) {
            while ($l_row = $this->m_db->fetch_row($l_res)) {
                switch ($l_row[2]) {
                    case self::C__CACHE__PORT:
                        $this->m_port_array = isys_format_json::decode($l_row[1]);
                        break;
                    case self::C__CACHE__LOGICAL_PORT:
                        $this->m_logical_port_array = isys_format_json::decode($l_row[1]);
                        break;
                    case self::C__CACHE__INTERFACE:
                        $this->m_network_interfaces = isys_format_json::decode($l_row[1]);
                        break;
                    case self::C__CACHE__INTERFACE_CONNECTIONS:
                        $this->m_network_interfaces_connection = isys_format_json::decode($l_row[1]);
                        break;
                    case self::C__CACHE__FC_PORT:
                        $this->m_fc_port_array = isys_format_json::decode($l_row[1]);
                        break;
                }
            }
            $this->m_db->free_result($l_res);
        } else {
            return false;
        }

        return true;
    }

    /**
     * Builds filter for ports if filter is set
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_port_filter_query($p_type)
    {
        $l_type = null;
        $l_filter = null;
        $l_filter_type = null;
        $l_os_arr = new isys_array();

        if (isset($this->m_port_filter_import_type[$this->m_type_id])) {
            $l_type = $this->m_type_id;
            $l_os_arr = array_keys($this->m_port_filter[$this->m_type_id]);
        } else {
            $l_filter_type_arr = array_keys($this->m_port_filter_import_type);

            if (count($l_filter_type_arr)) {
                foreach ($l_filter_type_arr as $l_filter_key) {
                    if (!is_numeric($l_filter_key)) {
                        if (strpos($l_filter_key, '*') !== false) {
                            $l_left_side = false;
                            $l_right_side = false;
                            if (strpos($l_filter_key, '*') === 0) {
                                $l_left_side = true;
                            }
                            if (strpos($l_filter_key, '*') === (strlen($l_filter_key) - 1)) {
                                $l_right_side = true;
                            }
                            $l_filter_key2 = str_replace('*', '', $l_filter_key);
                            if ($l_left_side && $l_right_side) {
                                if (strpos($this->m_type_title, $l_filter_key2) !== false) {
                                    $l_type = $l_filter_key;
                                    $l_os_arr = array_keys($this->m_port_filter[$l_type]);
                                    break;
                                }
                            } elseif ($l_left_side) {
                                if (strpos($this->m_type_title, $l_filter_key2) === (strlen($this->m_type_title) - strlen($l_filter_key2)) - 1) {
                                    $l_type = $l_filter_key;
                                    $l_os_arr = array_keys($this->m_port_filter[$l_type]);
                                    break;
                                }
                            } else {
                                if (strpos($this->m_type_title, $l_filter_key2) === 0) {
                                    $l_type = $l_filter_key;
                                    $l_os_arr = array_keys($this->m_port_filter[$l_type]);
                                    break;
                                }
                            }
                        } elseif ($this->m_type_title === $l_filter_key) {
                            $l_type = $l_filter_key;
                            $l_os_arr = array_keys($this->m_port_filter[$l_type]);
                            break;
                        }
                    }
                }
            }
        }

        if ($l_type !== null) {
            if (isset($this->m_port_filter[$l_type][$this->m_os_id])) {
                $l_filter = isys_format_json::decode($this->m_port_filter[$l_type][$this->m_os_id]);
                $l_filter_type = isys_format_json::decode($this->m_port_filter_import_type[$l_type][$this->m_os_id]);
            } else {
                if (count($l_os_arr)) {
                    foreach ($l_os_arr as $l_os_key) {
                        if (!is_numeric($l_os_key) && $l_os_key !== '*') {
                            if (strpos($l_os_key, '*') !== false) {
                                $l_left_side = false;
                                $l_right_side = false;
                                if (strpos($l_os_key, '*') === 0) {
                                    $l_left_side = true;
                                }
                                if (strpos($l_os_key, '*') === (strlen($l_os_key) - 1)) {
                                    $l_right_side = true;
                                }
                                $l_os_key2 = str_replace('*', '', $l_os_key);
                                if ($l_left_side && $l_right_side) {
                                    if (strpos($this->m_osversion, $l_os_key2) !== false) {
                                        $l_filter = isys_format_json::decode($this->m_port_filter[$l_type][$l_os_key]);
                                        $l_filter_type = isys_format_json::decode($this->m_port_filter_import_type[$l_type][$l_os_key]);
                                        break;
                                    }
                                } elseif ($l_left_side) {
                                    if (strpos($this->m_osversion, $l_os_key2) === (strlen($this->m_osversion) - strlen($l_os_key2)) - 1) {
                                        $l_filter = isys_format_json::decode($this->m_port_filter[$l_type][$l_os_key]);
                                        $l_filter_type = isys_format_json::decode($this->m_port_filter_import_type[$l_type][$l_os_key]);
                                        break;
                                    }
                                } else {
                                    if (strpos($this->m_osversion, $l_os_key2) === 0) {
                                        $l_filter = isys_format_json::decode($this->m_port_filter[$l_type][$l_os_key]);
                                        $l_filter_type = isys_format_json::decode($this->m_port_filter_import_type[$l_type][$l_os_key]);
                                        break;
                                    }
                                }
                            } elseif ($this->m_osversion === $l_os_key) {
                                $l_filter = isys_format_json::decode($this->m_port_filter[$l_type][$l_os_key]);
                                $l_filter_type = isys_format_json::decode($this->m_port_filter_import_type[$l_type][$l_os_key]);
                                break;
                            }
                        } elseif ($l_os_key === '*') {
                            $l_filter = isys_format_json::decode($this->m_port_filter[$l_type][$l_os_key]);
                            $l_filter_type = isys_format_json::decode($this->m_port_filter_import_type[$l_type][$l_os_key]);
                            break;
                        }
                    }
                }
            }
        }

        $l_sql_condition = '';
        if ($l_filter !== null || $l_filter_type !== null) {
            $this->m_port_filtered_filter = $l_filter;
            $this->m_port_filtered_filter_type = $l_filter_type;
            arsort($l_filter_type);

            if (count($l_filter_type)) {
                $noImportPortFilter = $importPortFilter = [];
                foreach ($l_filter_type as $l_filter_key => $l_filter_type_id) {
                    $l_filter_type_id = (int)$l_filter_type_id;

                    if ($l_filter_type_id === 3) {
                        if ($l_filter[$l_filter_key] != '') {
                            $noImportPortFilter[] = ' ifdescr NOT ILIKE ' . $this->m_dao->convert_sql_text(str_replace('*', '%', $l_filter[$l_filter_key]));
                        } else {
                            // if there is a filter with no import set then we do not import any ports
                            return ' AND FALSE';
                        }
                    } elseif ($l_filter_type_id === $p_type) {
                        $importPortFilter[] = 'ifdescr ILIKE ' . $this->m_dao->convert_sql_text(str_replace('*', '%', $l_filter[$l_filter_key]));
                    }
                }

                if (count($noImportPortFilter)) {
                    $l_sql_condition = ' AND ' . implode(' AND ', $noImportPortFilter);
                }

                if (count($importPortFilter)) {
                    $l_sql_condition .= ' AND (' . implode(' OR ', $importPortFilter) . ') ';
                }
            }
        }

        return $l_sql_condition;
    }

    /**
     * Helper method which updates the assignes the network port or logical port to the specified hostaddress
     *
     * @param $p_ip_id
     * @param $p_port_id
     * @param $p_port_type
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function assign_port_2_ip($p_ip_id, $p_port_id, $p_port_type)
    {
        $l_update = 'UPDATE isys_catg_ip_list SET ';
        switch ($p_port_type) {
            case 'port':
                $l_update .= 'isys_catg_ip_list__isys_catg_port_list__id ';
                break;
            case 'log_port':
                $l_update .= 'isys_catg_ip_list__isys_catg_log_port_list__id ';
                break;
        }
        $l_update .= ' = ' . $this->m_dao->convert_sql_id($p_port_id) . ' ' . 'WHERE isys_catg_ip_list__id = ' . $this->m_dao->convert_sql_id($p_ip_id);

        return $this->m_dao->update($l_update);
    }

    /**
     * Helper method which detaches the host from network ports,logical ports or both ports.
     *
     * @param      $p_ip_id
     * @param      $p_port_id
     * @param null $p_port_type
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function detach_port_2_ip($p_ip_id, $p_port_type = null)
    {
        $l_update = 'UPDATE isys_catg_ip_list SET ';
        switch ($p_port_type) {
            case 'port':
                $l_update .= 'isys_catg_ip_list__isys_catg_port_list__id = NULL';
                break;
            case 'log_port':
                $l_update .= 'isys_catg_ip_list__isys_catg_log_port_list__id = NULL';
                break;
            default:
                $l_update .= 'isys_catg_ip_list__isys_catg_log_port_list__id = NULL, isys_catg_ip_list__isys_catg_port_list__id = NULL';
                break;
        }
        $l_update .= ' WHERE isys_catg_ip_list__id = ' . $this->m_dao->convert_sql_id($p_ip_id);

        return $this->m_dao->update($l_update);
    }

    /**
     * @param int $p_type
     *
     * @note DS: This function creates a cached port map table with a assigned vlans as a comma-separated list.
     *           The vlan list is later used to check wheather a vlan is already assigned to a port or not.
     * @note VQH: We need the object id in the title because some ports or logical ports don't have a mac-address so
     *          that the key is not distinct.
     *
     * @return resource
     */
    private function prepare_ports_for_cache_by_type($p_type, $p_object_id, $p_with_vlans = true)
    {
        $l_vlan_table_join = $l_group_concat = '';

        if (is_value_in_constants($p_type, [
            'C__CATG__NETWORK_PORT',
            'C__CMDB__SUBCAT__NETWORK_PORT'
        ])) {
            //, isys_catg_port_list__id AS portid, isys_catg_port_list__isys_port_duplex__id, isys_catg_port_list__port_speed_value, isys_catg_port_list__state_enabled';

            if ($p_with_vlans) {
                $l_vlan_table_join = 'LEFT JOIN isys_cats_layer2_net_list ON isys_cats_layer2_net_assigned_ports_list__isys_obj__id = isys_cats_layer2_net_list__isys_obj__id ';
                // Determine if vlans should be checked by VLAN ID or VLAN Object title
                if (self::$m_vlanconfiguration === false) {
                    $l_vlan_ident_selection = 'isys_cats_layer2_net_list__ident';
                } else {
                    $l_vlan_ident_selection = 'CONCAT(isys_obj__title, \'|\', isys_cats_layer2_net_list__ident)';
                }
                $l_group_concat = ', GROUP_CONCAT(' . $l_vlan_ident_selection . ') as vlans ';
            }

            $l_sql = 'SELECT DISTINCT isys_catg_port_list.isys_catg_port_list__id as id, isys_catg_port_list__isys_obj__id as objid,
						CONCAT(isys_catg_port_list__title, \'|\', isys_catg_port_list__isys_obj__id, \'|\', isys_catg_port_list__mac) as title ';

            if ($p_with_vlans) {
                $l_sql .= $l_group_concat;
            }

            $l_sql .= ' FROM isys_catg_port_list
						LEFT JOIN isys_cats_layer2_net_assigned_ports_list ON isys_cats_layer2_net_assigned_ports_list.isys_catg_port_list__id = isys_catg_port_list.isys_catg_port_list__id
						' . $l_vlan_table_join . '
						LEFT JOIN isys_obj on isys_cats_layer2_net_assigned_ports_list__isys_obj__id = isys_obj.isys_obj__id ';

            if ($p_object_id > 0) {
                $l_sql .= 'WHERE (isys_catg_port_list__isys_obj__id = ' . (int)$p_object_id . ') ';
            } else {
                // Object id is not set
                return false;
            }

            $l_sql .= 'GROUP BY isys_catg_port_list.isys_catg_port_list__id;';
        } elseif (is_value_in_constants($p_type, [
            'C__CATG__NETWORK_LOG_PORT',
            'C__CMDB__SUBCAT__NETWORK_INTERFACE_L'
        ])) {
            if ($p_with_vlans) {
                $l_vlan_table_join = 'LEFT JOIN isys_cats_layer2_net_list ON isys_catg_log_port_list_2_isys_obj.isys_obj__id = isys_cats_layer2_net_list__isys_obj__id ';
                // Determine if vlans should be checked by VLAN ID or VLAN Object title
                if (self::$m_vlanconfiguration === false) {
                    $l_vlan_ident_selection = 'isys_cats_layer2_net_list__ident';
                } else {
                    $l_vlan_ident_selection = 'CONCAT(isys_obj__title, \'|\', isys_cats_layer2_net_list__ident)';
                }
                $l_group_concat = ', GROUP_CONCAT(DISTINCT ' . $l_vlan_ident_selection . ') as vlans ';
            }

            $l_sql = 'SELECT
						DISTINCT isys_catg_log_port_list.isys_catg_log_port_list__id as id,
						isys_catg_log_port_list__isys_obj__id as objid,
						CONCAT(isys_catg_log_port_list__title, \'|\', isys_catg_log_port_list__isys_obj__id, \'|\', isys_catg_log_port_list__mac) as title ';

            if ($p_with_vlans) {
                $l_sql .= $l_group_concat;
            }

            $l_sql .= ' FROM isys_catg_log_port_list

						LEFT JOIN isys_catg_log_port_list_2_isys_obj ON isys_catg_log_port_list_2_isys_obj.isys_catg_log_port_list__id = isys_catg_log_port_list.isys_catg_log_port_list__id
						' . $l_vlan_table_join . '
						LEFT JOIN isys_obj on isys_catg_log_port_list_2_isys_obj.isys_obj__id = isys_obj.isys_obj__id ';

            if ($p_object_id > 0) {
                $l_sql .= 'WHERE (isys_catg_log_port_list__isys_obj__id = ' . (int)$p_object_id . ') ';
            } else {
                // Object id is not set
                return false;
            }

            $l_sql .= 'GROUP BY isys_catg_log_port_list.isys_catg_log_port_list__id;';
        } elseif ($p_type == defined_or_default('C__CATG__CONTROLLER_FC_PORT')) {
            $l_sql = 'SELECT isys_catg_fc_port_list__id AS id, isys_catg_fc_port_list__isys_obj__id AS objid,
					CONCAT(isys_catg_fc_port_list__title, \'|\', isys_catg_fc_port_list__isys_obj__id) AS title FROM isys_catg_fc_port_list ';

            if ($p_object_id > 0) {
                $l_sql .= 'WHERE (isys_catg_fc_port_list__isys_obj__id = ' . (int)$p_object_id . ')';
            } else {
                return false;
            }
        } else {
            $l_table = 'isys_catg_connector_list';
            $l_selection = ', isys_catg_connector_list__title as title';
            // Exclude ports, because they are handled separately, power consumer and universal interfaces, because they don't get imported by jdisc

            $l_sql = 'SELECT isys_catg_connector_list__id, isys_catg_connector_list__isys_obj__id AS objid, isys_catg_connector_list__title AS title, \'\' AS vlans FROM isys_catg_connector_list ' .
                'WHERE isys_catg_connector_list__assigned_category NOT IN (\'C__CATG__NETWORK_PORT\', \'C__CATG__POWER_CONSUMER\', \'C__CATG__UNIVERSAL_INTERFACE\')';
        }

        //. ' WHERE ' . $l_table . '__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        return $this->m_db->query($l_sql);
    }

    private function get_all_vlans()
    {
    }

    /**
     * Maps connector types for category universal interface
     *
     * @throws Exception
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function map_connector_types()
    {
        $l_dao = isys_cmdb_dao::factory(isys_application::instance()->database);
        $l_sql = 'SELECT * FROM isys_ui_con_type';
        $l_res = $l_dao->retrieve($l_sql);
        $this->m_connector_types = new isys_array();

        while ($l_row = $l_res->get_row()) {
            if ($l_row['isys_ui_con_type__const'] === 'C__UI_CON_TYPE__MONITOR') {
                $this->m_connector_types['monitor'] = $l_row;
                $this->m_connector_types['display'] = $l_row;
            } else {
                $l_constant = strtolower(str_replace([
                    'C__UI_CON_TYPE__',
                    '_'
                ], [
                    '',
                    ' '
                ], $l_row['isys_ui_con_type__const']));
                $this->m_connector_types[$l_constant] = $l_row;
            }
        }
    }

    /**
     * Helper method which prepares the import data for category universal interface
     *
     * @param $p_connected_device
     * @param $p_type
     * @param $p_title
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function prepare_universal_interface($p_connected_device, $p_type, $p_title)
    {
        $l_type = [
            'tag'        => 'type',
            'value'      => $p_type['isys_ui_con_type__title'],
            'id'         => $p_type['isys_ui_con_type__id'],
            'const'      => $p_type['isys_ui_con_type__const'],
            'title_lang' => $p_type['isys_ui_con_type__title'],
            'title'      => 'LC__CMDB__CATG__UI_CONNECTION_TYPE',
        ];

        $l_return = [
            'data_id'    => null,
            'properties' => [
                'title' => [
                    'tag'   => 'title',
                    'value' => $p_title,
                    'title' => 'LC__CMDB__CATG__TITLE',
                ],
                'type'  => $l_type
            ]
        ];

        if ($p_connected_device > 0) {
            // Get object info
            $l_obj_data = isys_factory::get_instance('isys_cmdb_dao', isys_application::instance()->database)
                ->get_object_by_id($p_connected_device)
                ->get_row();

            $l_return['properties']['assigned_connector'] = [
                'tag'   => 'assigned_connector',
                'value' => [
                    [
                        'name'              => $p_title,
                        'id'                => $p_connected_device,
                        'sysid'             => $l_obj_data['isys_obj__sysid'],
                        'type'              => $l_obj_data['isys_obj__isys_obj_type__id'],
                        'assigned_category' => 'C__CATG__UNIVERSAL_INTERFACE',
                        'title'             => $l_obj_data['isys_obj_type__title'],
                        'tag'               => 'sub_assigned_connector',
                    ]
                ],
                'title' => 'LC__CMDB__CATG__UI_ASSIGNED_UI',
            ];
        }

        return $l_return;
    }

    /**
     * Constructor
     *
     * @param   isys_component_database     $p_db Database component
     * @param   isys_component_database_pdo $p_pdo
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function __construct(isys_component_database $p_db, isys_component_database_pdo $p_pdo)
    {
        $language = isys_application::instance()->container->get('language');

        // We get ourselves the net types, because we are going to need them a few times.
        $this->m_net_types = isys_factory_cmdb_dialog_dao::get_instance($p_db, 'isys_net_type')
            ->get_data();

        // We define theese IP-assignment types as default.
        $this->m_ip_assignments = [];
        if (defined('C__CATS_NET_TYPE__IPV4') && defined('C__CATP__IP__ASSIGN__STATIC')) {
            $this->m_ip_assignments[C__CATS_NET_TYPE__IPV4] = isys_factory_cmdb_dialog_dao::get_instance($p_db, 'isys_ip_assignment')
                ->get_data(C__CATP__IP__ASSIGN__STATIC);
        }
        if (defined('C__CATS_NET_TYPE__IPV6') && defined('C__CMDB__CATG__IP__STATIC')) {
            $this->m_ip_assignments[C__CATS_NET_TYPE__IPV6] = isys_factory_cmdb_dialog_dao::get_instance($p_db, 'isys_ipv6_assignment')
                ->get_data(C__CMDB__CATG__IP__STATIC);
        }
        parent::__construct($p_db, $p_pdo);

        $this->m_dao = isys_cmdb_dao_jdisc::instance($this->m_db);

        // We fetch all virtual interface types as ids
        foreach ($this->m_interface_types as $l_type => $l_content) {
            if (!isset($l_content['dialog']) && $l_content['dialog'] === false) {
                $this->m_interface_types[$l_type]['id'] = isys_import_handler_cmdb::check_dialog('isys_port_type', $l_content['title']);
            }
            $this->m_interface_types[$l_type]['content'] = $this->get_interface_types($l_type);
        }

        $l_dialog_data = isys_factory_cmdb_dialog_dao::get_instance($this->m_db, 'isys_port_type')
            ->get_data();

        if (is_array($l_dialog_data)) {
            foreach ($l_dialog_data as $l_data) {
                $this->addDialogData(
                    'port_type',
                    $language->get($l_data['isys_port_type__title']),
                    [
                        $l_data['isys_port_type__id'],
                        $l_data['isys_port_type__const'],
                        $l_data['isys_port_type__title']
                    ]
                );
            }
        }

        $l_dialog_data = isys_factory_cmdb_dialog_dao::get_instance($this->m_db, 'isys_port_mode')
            ->get_data();

        if (is_array($l_dialog_data)) {
            foreach ($l_dialog_data as $l_data) {
                $this->addDialogData(
                    'port_mode',
                    $language->get($l_data['isys_port_mode__title']),
                    [
                        $l_data['isys_port_mode__id'],
                        $l_data['isys_port_mode__const'],
                        $l_data['isys_port_mode__title'],
                    ]
                );
            }
        }

        $l_dialog_data = isys_factory_cmdb_dialog_dao::get_instance($this->m_db, 'isys_port_negotiation')
            ->get_data();

        if (is_array($l_dialog_data)) {
            foreach ($l_dialog_data as $l_data) {
                $this->addDialogData(
                    'port_negotiation',
                    $language->get($l_data['isys_port_negotiation__title']),
                    [
                        $l_data['isys_port_negotiation__id'],
                        $l_data['isys_port_negotiation__const'],
                        $l_data['isys_port_negotiation__title']
                    ]
                );
            }
        }

        $l_dialog_data = isys_factory_cmdb_dialog_dao::get_instance($this->m_db, 'isys_port_duplex')
            ->get_data();

        if (is_array($l_dialog_data)) {
            foreach ($l_dialog_data as $l_data) {
                $this->addDialogData(
                    'port_duplex',
                    $language->get($l_data['isys_port_duplex__title']),
                    [
                        $l_data['isys_port_duplex__id'],
                        $l_data['isys_port_duplex__const'],
                        $l_data['isys_port_duplex__title']
                    ]
                );
            }
        }

        $l_dialog_data = isys_factory_cmdb_dialog_dao::get_instance($this->m_db, 'isys_net_dns_domain')
            ->get_data();

        if (is_array($l_dialog_data)) {
            foreach ($l_dialog_data as $l_data) {
                $this->addDialogData(
                    'dns_domain',
                    $language->get($l_data['isys_net_dns_domain__title']),
                    $l_data['isys_net_dns_domain__id']
                );
            }
        }

        $l_dialog_data = isys_factory_cmdb_dialog_dao::get_instance($this->m_db, 'isys_netx_ifacel_type')
            ->get_data();

        if (is_array($l_dialog_data)) {
            foreach ($l_dialog_data as $l_data) {
                $this->addDialogData(
                    'port_type_logical',
                    $language->get($l_data['isys_netx_ifacel_type__title']),
                    [
                        $l_data['isys_netx_ifacel_type__id'],
                        $l_data['isys_netx_ifacel_type__const'],
                        $l_data['isys_netx_ifacel_type__title'],
                    ]
                );
            }
        }

        $l_dialog_data = isys_factory_cmdb_dialog_dao::get_instance($this->m_db, 'isys_port_speed')
            ->get_data();

        if (is_array($l_dialog_data)) {
            foreach ($l_dialog_data as $l_data) {
                $this->addDialogData(
                    'port_speed',
                    $language->get($l_data['isys_port_speed__const']),
                    [
                        $l_data['isys_port_speed__id'],
                        $l_data['isys_port_speed__const'],
                        $l_data['isys_port_speed__title']
                    ]
                );
            }
        }

        // Gets and sets the vlan configuration from JDisc
        $this->set_vlan_configuration();
    }
}
