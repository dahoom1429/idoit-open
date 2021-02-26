<?php
use idoit\Component\Property\Type\DialogPlusMultiselectProperty;
use idoit\Component\Helper\Ip;
use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Type\DynamicProperty;
use idoit\Module\Report\SqlQuery\Structure\SelectCondition;

/**
 * i-doit
 *
 * DAO: specific category for networks
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Van Quyen Hoang <qhoang@i-doit.de>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_net extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'net';

    /**
     * Dynamic property handling for '_gateway'
     *
     * @param array $p_row
     *
     * @return mixed|string
     * @author Kevin Mauel <kmauel@i-doit.com>
     */
    public function retrieveDefaultGateway(array $p_row)
    {
        $request = new isys_request();

        if (!array_key_exists('isys_cats_net_list__isys_catg_ip_list__id', $p_row) && isset($p_row['isys_cats_net_list__id'])) {
            $p_row = $this->get_data($p_row['isys_cats_net_list__id'])->get_row();
        } else {
            $p_row = $p_row + [
                'isys_cats_net_list__isys_obj__id' => $p_row['isys_obj__id']
            ];
        }

        if ($p_row['isys_cats_net_list__isys_catg_ip_list__id'] === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        $request->set_row($p_row);
        $gateway = $this->callback_property_gateway($request);

        return $gateway[$p_row['isys_cats_net_list__isys_catg_ip_list__id']];
    }

    /**
     * Dynamic property handling for '_layer2_assignments'
     *
     * @param array $p_row
     *
     * @return mixed|string
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_layer2_assignment(array $p_row)
    {
        global $g_comp_database;

        $objId = ($p_row['isys_cats_net_list__isys_obj__id'] ?: ($p_row['isys_obj__id'] ?: null));

        if ($objId === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        $l_dao = isys_cmdb_dao_category_s_net::instance($g_comp_database);
        $l_res = $l_dao->get_assigned_layer_2($objId);
        $l_list = [];

        if ($l_res->num_rows() > 0) {
            $l_quicklink = new isys_ajax_handler_quick_info();

            $i = 0;
            while ($l_row = $l_res->get_row()) {
                if ($i++ == isys_tenantsettings::get('cmdb.limits.port-lists-vlans', 10)) {
                    $l_list[] = '...';
                    break;
                }

                if (empty($l_row['isys_obj__id'])) {
                    $l_row['vlan'] = '-';
                }

                $l_list[] = $l_quicklink->get_quick_info($l_row['isys_obj__id'], $l_row['isys_obj__title'], C__LINK__OBJECT);
            }
            $l_res->free_result();
        }

        if (count($l_list)) {
            return '<ul class="fl"><li>' . implode(',&nbsp</li><li>', $l_list) . '</li></ul>';
        } else {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }
    }

    /**
     * Callback method for the DNS domain field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_dns_domain(isys_request $p_request)
    {
        $l_cat_list = [];
        $l_res_dns_domain = $this->get_dns_domains();

        while ($l_row_dns_domain = $l_res_dns_domain->get_row()) {
            $l_cat_list[] = [
                "caption" => $l_row_dns_domain['isys_net_dns_domain__title'],
                "value"   => $l_row_dns_domain['isys_net_dns_domain__id']
            ];
        }

        return $l_cat_list;
    }

    /**
     * Callback method for the default gateway.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_gateway(isys_request $p_request)
    {
        global $g_comp_database;

        $l_return = [];

        if ($p_request->get_row('isys_cats_net_list__isys_obj__id') > 0) {
            $l_assHosts = isys_cmdb_dao_category_s_net::instance($g_comp_database)
                ->get_assigned_hosts($p_request->get_row('isys_cats_net_list__isys_obj__id'));

            if ($l_assHosts->num_rows() > 0) {
                while ($l_row = $l_assHosts->get_row()) {
                    $l_return[$l_row['isys_catg_ip_list__id']] = $l_row['isys_cats_net_ip_addresses_list__title'] . ' - ' . $l_row['isys_obj__title'];
                }
            }
        }

        return $l_return;
    }

    /**
     * Dynamic property handling for getting the net address range.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_address_range(array $p_row)
    {
        global $g_comp_database;

        // $this will not work, because the method is called like a static method.
        $l_row = isys_cmdb_dao_category_s_net::instance($g_comp_database)
            ->get_data($p_row['isys_cats_net_list__id'])
            ->get_row();

        // When we handle a IPv4 address, we don't need to shorten.
        if ($l_row['isys_cats_net_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
            return '<span data-sort="' . str_pad($l_row['isys_cats_net_list__address_range_from_long'], 10, 0, STR_PAD_LEFT) . '">' .
                $l_row['isys_cats_net_list__address_range_from'] . ' - ' . $l_row['isys_cats_net_list__address_range_to'] . '</span>';
        }

        return Ip::validate_ipv6($l_row['isys_cats_net_list__address_range_from'], true) . ' - ' . Ip::validate_ipv6($l_row['isys_cats_net_list__address_range_to'], true);
    }

    /**
     * Dynamic property handling for getting the (shortened) net address.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_address(array $p_row)
    {
        global $g_comp_database;

        // $this will not work, because the method is called like a static method.
        $l_row = isys_cmdb_dao_category_s_net::instance($g_comp_database)
            ->get_data($p_row['isys_cats_net_list__id'])
            ->get_row();

        // When we handle a IPv4 address, we don't need to shorten.
        if ($l_row['isys_cats_net_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
            return '<span data-sort="' . str_pad($l_row['isys_cats_net_list__address_range_from_long'], 10, 0, STR_PAD_LEFT) . '">' . $l_row['isys_cats_net_list__address'] .
                '</span>';
        }

        return Ip::validate_ipv6($l_row['isys_cats_net_list__address'], true);
    }

    /**
     * Dynamic property handling for getting the (shortened) net address with suffix.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_address_with_suffix(array $p_row)
    {
        global $g_comp_database;

        // $this will not work, because the method is called like a static method.
        $l_row = isys_cmdb_dao_category_s_net::instance($g_comp_database)
            ->get_data($p_row['isys_cats_net_list__id'])
            ->get_row();

        // When we handle a IPv4 address, we don't need to shorten.
        if ($l_row['isys_cats_net_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
            return '<span data-sort="' . str_pad($l_row['isys_cats_net_list__address_long'], 10, 0, STR_PAD_LEFT) . '">' . $l_row['isys_cats_net_list__address'] . ' /' .
                $l_row['isys_cats_net_list__cidr_suffix'] . '</span>';
        }

        if ($l_row['isys_cats_net_list__cidr_suffix'] == 0) {
            $l_row['isys_cats_net_list__cidr_suffix'] = '-';
        }

        return Ip::validate_ipv6($l_row['isys_cats_net_list__address'], true) . ' /' . $l_row['isys_cats_net_list__cidr_suffix'];
    }

    /**
     * Dynamic property handling for getting the (shortened) net address with suffix.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_free_addresses(array $p_row)
    {
        global $g_comp_database;

        // $this will not work, because the method is called like a static method.
        $l_net_dao = isys_cmdb_dao_category_s_net::instance($g_comp_database);

        $l_row = $l_net_dao->get_data($p_row['isys_cats_net_list__id'])
            ->get_row();

        $l_net_num = $l_net_dao->get_assigned_hosts($l_row['isys_cats_net_list__isys_obj__id'], 'AND isys_cats_net_ip_addresses_list__title != ""')
            ->num_rows();
        $l_net_num = '<span data-sort="' . htmlentities(str_pad($l_net_num, 10, 0, STR_PAD_LEFT)) . '">' . $l_net_num . '</span>';

        // When we handle a IPv4 address, we don't need to shorten.
        if ($l_row['isys_cats_net_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
            // "+1" because 0.0.0.5 -> 0.0.0.6 = 2 free addresses, not 1.
            return $l_net_num . '/' . ((1 + $l_row['isys_cats_net_list__address_range_to_long']) - $l_row['isys_cats_net_list__address_range_from_long']);
        }

        return $l_net_num;
    }

    /**
     * Dynamic property handling for getting the (shortened) net address with suffix.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_netmask(array $p_row)
    {
        global $g_comp_database;

        // $this will not work, because the method is called like a static method.
        $l_row = isys_cmdb_dao_category_s_net::instance($g_comp_database)
            ->get_data($p_row['isys_cats_net_list__id'])
            ->get_row();

        // When we handle a IPv4 address, we don't need to shorten.
        if ($l_row['isys_cats_net_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
            return '<span data-sort="' . str_pad($l_row['isys_cats_net_list__mask_long'], 10, 0, STR_PAD_LEFT) . '">' . $l_row['isys_cats_net_list__mask'] . '</span>';
        }

        if ($l_row['isys_cats_net_list__cidr_suffix'] == 0) {
            return '-';
        }

        return Ip::calc_subnet_by_cidr_suffix_ipv6($l_row['isys_cats_net_list__cidr_suffix']);
    }

    /**
     * Finds the responsible supernet(s) of the given object.
     *
     * @param   integer $p_obj_id
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function find_responsible_supernet($p_obj_id)
    {
        $l_l3_net = $this->get_data(null, $p_obj_id)
            ->get_row();

        if ($l_l3_net['isys_cats_net_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
            $l_sql = 'SELECT *
				FROM isys_cats_net_list
				LEFT JOIN isys_obj ON isys_obj__id = isys_cats_net_list__isys_obj__id
				WHERE isys_obj__isys_obj_type__id = ' . $this->convert_sql_id(defined_or_default('C__OBJTYPE__SUPERNET')) . '
				AND isys_cats_net_list__address_range_from_long <= ' . $this->convert_sql_text($l_l3_net['isys_cats_net_list__address_range_from_long']) . '
				AND isys_cats_net_list__address_range_to_long >= ' . $this->convert_sql_text($l_l3_net['isys_cats_net_list__address_range_to_long']) . ';';
        } else {
            $l_sql = 'SELECT *
				FROM isys_cats_net_list
				LEFT JOIN isys_obj ON isys_obj__id = isys_cats_net_list__isys_obj__id
				WHERE isys_obj__isys_obj_type__id = ' . $this->convert_sql_id(defined_or_default('C__OBJTYPE__SUPERNET')) . '
				AND isys_cats_net_list__address_range_from <= ' . $this->convert_sql_text($l_l3_net['isys_cats_net_list__address_range_from']) . '
				AND isys_cats_net_list__address_range_to >= ' . $this->convert_sql_text($l_l3_net['isys_cats_net_list__address_range_to']) . ';';
        }

        return $this->retrieve($l_sql);
    }

    /**
     * This method finds a free IPv6 by a given IP assignment from the "isys_ipv6_assignment" table.
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   integer                 $p_net_obj
     * @param   integer                 $p_ip_assignment Important: This has to be an ID from the isys_ipv6_assignment table!
     *
     * @return  mixed  String with a free IPv6 address or boolean false.
     */
    public function find_free_ipv6_by_assignment($p_net_obj, $p_ip_assignment)
    {
        global $g_comp_database;

        $l_ip_dao = new isys_cmdb_dao_category_g_ip($g_comp_database);
        $l_dhcp_dao = new isys_cmdb_dao_category_s_net_dhcp($g_comp_database);

        // Because the values of the assignments differ between our two tables, we need this mapping.
        if ($p_ip_assignment == defined_or_default('C__CMDB__CATG__IP__DHCPV6_RESERVED')) {
            $p_ip_assignment = defined_or_default('C__NET__DHCPV6__DHCPV6_RESERVED');
        } elseif ($p_ip_assignment == defined_or_default('C__CMDB__CATG__IP__DHCPV6')) {
            $p_ip_assignment = defined_or_default('C__NET__DHCPV6__DHCPV6');
        } elseif ($p_ip_assignment == defined_or_default('C__CMDB__CATG__IP__SLAAC')) {
            return '';
        } elseif ($p_ip_assignment == defined_or_default('C__CMDB__CATG__IP__SLAAC_AND_DHCPV6')) {
            $l_rows = $l_dhcp_dao->get_data(
                null,
                $p_net_obj,
                'AND isys_cats_net_dhcp_list__isys_net_dhcpv6_type__id = ' . defined_or_default('C__NET__DHCPV6__SLAAC_AND_DHCPV6'),
                null,
                C__RECORD_STATUS__NORMAL
            )
                ->num_rows();

            if ($l_rows > 0) {
                return '';
            }
            return $l_ip_dao->get_free_ipv6($p_net_obj);
        } else {
            return $l_ip_dao->get_free_ipv6($p_net_obj);
        }

        $l_dhcp_res = $l_dhcp_dao->get_data(null, $p_net_obj, 'AND isys_cats_net_dhcp_list__isys_net_dhcpv6_type__id = ' . $l_dhcp_dao->convert_sql_id($p_ip_assignment));

        while ($l_dhcp_row = $l_dhcp_res->get_row()) {
            $l_free_ip = $l_ip_dao->get_free_ipv6($p_net_obj, $l_dhcp_row['isys_cats_net_dhcp_list__range_from'], $l_dhcp_row['isys_cats_net_dhcp_list__range_to']);

            if ($l_free_ip) {
                return $l_free_ip;
            }
        }

        return $l_ip_dao->get_free_ipv6($p_net_obj);
    }

    /**
     * Method for getting the GLOBAL layer3 IPv4 net object. The ID of the global layer3 net should also be available via C__OBJ__NET_GLOBAL_IPV4.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_global_ipv4_net()
    {
        $l_sql = 'SELECT * FROM isys_cats_net_list
			INNER JOIN isys_obj ON isys_obj__id = isys_cats_net_list__isys_obj__id
			LEFT JOIN isys_net_type ON isys_cats_net_list__isys_net_type__id = isys_net_type__id
			WHERE isys_obj__const = "C__OBJ__NET_GLOBAL_IPV4"
			AND isys_obj__undeletable = 1;';

        return $this->retrieve($l_sql)
            ->get_row();
    }

    /**
     * Method for getting the GLOBAL layer3 IPv6 net object. The ID of the global layer3 net should also be available via defined_or_default('C__OBJ__NET_GLOBAL_IPV6').
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_global_ipv6_net()
    {
        $l_sql = 'SELECT isys_cats_net_list__isys_obj__id FROM isys_cats_net_list
			INNER JOIN isys_obj ON isys_obj__id = isys_cats_net_list__isys_obj__id
			LEFT JOIN isys_net_type ON isys_cats_net_list__isys_net_type__id = isys_net_type__id
			WHERE isys_obj__const = "C__OBJ__NET_GLOBAL_IPV6"
			AND isys_obj__undeletable = 1;';

        return $this->retrieve($l_sql)
            ->get_row();
    }

    /**
     * Save category entry.
     *
     * @param   integer $p_cat_level
     * @param   integer & $p_intOldRecStatus
     *
     * @return  integer
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata['isys_cats_net_list__status'];

        $l_list_id = $l_catdata['isys_cats_net_list__id'];

        $l_ip_data = $this->merge_posted_ip_data($_POST['C__CATS__NET__TYPE']);

        $l_cidr = 0;

        // We have to check, which CIDR field we have to take.
        if ($_POST['C__CATS__NET__TYPE'] == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
            $l_cidr = $_POST['C__CATS__NET__CIDR'];
        } elseif ($_POST['C__CATS__NET__TYPE'] == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
            $l_cidr = $_POST['C__CATS__NET__NET_V6_CIDR'];
            $l_ip_data[C__IP__SUBNET] = Ip::calc_subnet_by_cidr_suffix_ipv6($l_cidr);

            if ($l_ip_data['ADDRESS_FROM'] == '') {
                $l_ip_range = Ip::calc_ip_range_ipv6($l_ip_data[C__IP__NET], $l_cidr);
                $l_ip_data['ADDRESS_FROM'] = $l_ip_range['from'];
                $l_ip_data['ADDRESS_TO'] = $l_ip_range['to'];
            }

            $l_ip_data['ADDRESS_FROM'] = Ip::validate_ipv6($l_ip_data['ADDRESS_FROM']);
            $l_ip_data['ADDRESS_TO'] = Ip::validate_ipv6($l_ip_data['ADDRESS_TO']);
        }

        if (empty($l_list_id)) {
            $l_list_id = $this->create_connector('isys_cats_net_list');
        }

        $l_bRet = $this->save(
            $l_list_id,
            C__RECORD_STATUS__NORMAL,
            $_POST['C__CATS__NET__TITLE'],
            $_POST['C__CATS__NET__TYPE'],
            $l_ip_data[C__IP__NET],
            $l_ip_data[C__IP__SUBNET],
            $_POST['C__CATS__NET__DEF_GW_V4'],
            $l_ip_data['ADDRESS_FROM'],
            $l_ip_data['ADDRESS_TO'],
            $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()],
            $l_cidr,
            isys_format_json::decode($_POST['C__CATS__NET__ASSIGNED_DNS_SERVER__HIDDEN']),
            $_POST['C__CATS__NET__DNS_DOMAIN'],
            $_POST['C__CATS__NET__REVERSE_DNS'],
            isys_format_json::decode($_POST['C__CATS__NET__LAYER2__HIDDEN'])
        );

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_bRet ? $l_list_id : -1;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_newRecStatus
     * @param   string  $p_title
     * @param   integer $p_typeID
     * @param   string  $p_address
     * @param   string  $p_netmask
     * @param   string  $p_gateway
     * @param   string  $p_from
     * @param   string  $p_to
     * @param   string  $p_description
     * @param   integer $p_cidr_suffix
     * @param   array   $p_dns_server_selected
     * @param   array   $p_dns_domain_selected
     * @param   integer $p_reverse_dns
     * @param   integer $p_layer_2_assignment
     *
     * @return  boolean
     * @throws  Exception
     * @throws  isys_exception_dao
     * @throws  isys_exception_dao_cmdb
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function save(
        $p_cat_level,
        $p_newRecStatus,
        $p_title,
        $p_typeID,
        $p_address,
        $p_netmask,
        $p_gateway,
        $p_from,
        $p_to,
        $p_description,
        $p_cidr_suffix,
        $p_dns_server_selected = null,
        $p_dns_domain_selected = null,
        $p_reverse_dns = 0,
        $p_layer_2_assignment = null
    ) {
        $this->check_missing_net_params($p_typeID, $p_address, $p_netmask, $p_cidr_suffix, $p_from, $p_to);

        $l_ip_from_long = Ip::ip2long($p_from);
        $l_ip_to_long = Ip::ip2long($p_to);

        if ($l_ip_from_long > $l_ip_to_long) {
            $l_temp = $l_ip_from_long;
            $l_ip_from_long = $l_ip_to_long;
            $l_ip_to_long = $l_temp;

            $l_temp = $p_from;
            $p_from = $p_to;
            $p_to = $l_temp;
        }

        $l_strSql = 'UPDATE isys_cats_net_list
            SET isys_cats_net_list__description = ' . $this->convert_sql_text($p_description) . ',
            isys_cats_net_list__title = ' . $this->convert_sql_text($p_title) . ',
            isys_cats_net_list__address = ' . $this->convert_sql_text($p_address) . ',
            isys_cats_net_list__mask = ' . $this->convert_sql_text($p_netmask) . ',
            isys_cats_net_list__address_range_from = ' . $this->convert_sql_text($p_from) . ',
            isys_cats_net_list__address_range_to = ' . $this->convert_sql_text($p_to) . ',
            isys_cats_net_list__isys_net_type__id = ' . $this->convert_sql_id($p_typeID) . ',
            isys_cats_net_list__status = ' . $this->convert_sql_id($p_newRecStatus) . ',
            isys_cats_net_list__cidr_suffix = ' . $this->convert_sql_int($p_cidr_suffix) . ',
            isys_cats_net_list__isys_catg_ip_list__id = ' . $this->convert_sql_id($p_gateway) . ',
            isys_cats_net_list__address_long = ' . $this->convert_sql_text(Ip::ip2long($p_address)) . ',
            isys_cats_net_list__mask_long = ' . $this->convert_sql_text(Ip::ip2long($p_netmask)) . ',
            isys_cats_net_list__address_range_from_long = ' . $this->convert_sql_text($l_ip_from_long) . ',
            isys_cats_net_list__address_range_to_long = ' . $this->convert_sql_text($l_ip_to_long) . ',
            isys_cats_net_list__reverse_dns = ' . $this->convert_sql_text($p_reverse_dns) . '
            WHERE isys_cats_net_list__id = ' . $this->convert_sql_id($p_cat_level) . ';';

        if ($this->m_object_id > 0) {
            $l_assigned_layer2 = $this->get_assigned_layer_2_ids($this->m_object_id);
            $l_layer2_ids = $this->getOrCreateLayer2Ids($p_layer_2_assignment);

            if (is_array($l_layer2_ids) && count($l_layer2_ids) > 0) {
                foreach ($l_layer2_ids as $l_key1 => $l_id) {
                    if (count($l_assigned_layer2) > 0 && isset($l_assigned_layer2[$l_key1])) {
                        unset($l_assigned_layer2[$l_key1]);
                        unset($l_layer2_ids[$l_key1]);
                    }
                }

                if (count($l_layer2_ids) > 0) {
                    $this->add_layer2_assignment($l_layer2_ids, $this->m_object_id);
                }
            }

            if (is_array($l_assigned_layer2) && count($l_assigned_layer2) > 0) {
                $this->remove_layer2_assignment($l_assigned_layer2, $this->m_object_id);
            }
        } else {
            throw new isys_exception_dao_cmdb('Object ID not available in ' . get_class($this) . '::save()');
        }

        $this->clear_dns_server_attachments($p_cat_level);

        if (!empty($p_dns_server_selected)) {
            if (is_string($p_dns_server_selected) || is_array($p_dns_server_selected)) {
                if (!is_array($p_dns_server_selected)) {
                    $l_dns_server_arr = json_decode($p_dns_server_selected);
                } else {
                    $l_dns_server_arr = $p_dns_server_selected;
                }

                if (is_array($l_dns_server_arr) && count($l_dns_server_arr) > 0) {
                    foreach ($l_dns_server_arr as $l_dns_server_id) {
                        if (is_numeric($l_dns_server_id) && $l_dns_server_id > 0) {
                            $this->attach_dns_server($p_cat_level, $l_dns_server_id);
                        }
                    }
                }
            }
        }

        $this->clear_dns_domain_attachments($p_cat_level);

        if (!empty($p_dns_domain_selected)) {
            if (is_string($p_dns_domain_selected) || is_array($p_dns_domain_selected)) {
                if (!is_array($p_dns_domain_selected)) {
                    $l_dns_domain_arr = explode(",", $p_dns_domain_selected);
                } else {
                    $l_dns_domain_arr = $p_dns_domain_selected;
                }

                $l_selected_dns_domain = $this->get_assigned_dns_domain(null, $p_cat_level);

                $l_ar_selected_domains = [];
                if ($l_selected_dns_domain) {
                    while ($l_row = $l_selected_dns_domain->get_row()) {
                        $l_ar_selected_domains[] = $l_row['isys_net_dns_domain__id'];
                    }
                    if (is_array($l_dns_domain_arr) && count($l_dns_domain_arr) > 0) {
                        foreach ($l_dns_domain_arr as $l_dns_domain_id) {
                            if (!in_array($l_dns_domain_id, $l_ar_selected_domains) && $l_dns_domain_id > 0) {
                                $this->attach_dns_domain($p_cat_level, $l_dns_domain_id);
                            }
                        }
                    }
                }
            }
        }
        if ($this->update($l_strSql)) {
            return $this->apply_update();
        } else {
            return false;
        }
    }

    /**
     * Create category entry.
     *
     * @param   $p_cat_level
     * @param   $p_id
     *
     * @return  null
     * @author   Dennis Blümer <dbluemer@i-doit.org>
     */
    public function attachObjects(array $p_post)
    {
        return null;
    }

    /**
     * Executes the query to create the category entry.
     *
     * @param   integer $p_objID
     * @param   integer $p_newRecStatus
     * @param   string  $p_title
     * @param   integer $p_typeID
     * @param   string  $p_address
     * @param   string  $p_netmask
     * @param   string  $p_gateway
     * @param   boolean $p_dhcp
     * @param   string  $p_from
     * @param   string  $p_to
     * @param   integer $p_dnsID
     * @param   integer $p_domainID
     * @param   string  $p_description
     * @param   integer $p_cidr_suffix
     * @param   mixed   $p_dns_server_selected
     * @param   mixed   $p_dns_domain_selected
     * @param   integer $p_reverse_dns
     *
     * @return  mixed  The newly created ID or false
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create(
        $p_objID,
        $p_newRecStatus,
        $p_title,
        $p_typeID,
        $p_address,
        $p_netmask,
        $p_gateway,
        $p_dhcp,
        $p_from,
        $p_to,
        $p_dnsID,
        $p_domainID,
        $p_description,
        $p_cidr_suffix,
        $p_dns_server_selected = null,
        $p_dns_domain_selected = null,
        $p_reverse_dns = 0,
        $p_layer_2_assignment = null
    ) {
        $this->check_missing_net_params($p_typeID, $p_address, $p_netmask, $p_cidr_suffix, $p_from, $p_to);

        $l_ip_from_long = Ip::ip2long($p_from);
        $l_ip_to_long = Ip::ip2long($p_to);

        if ($l_ip_from_long > $l_ip_to_long) {
            $l_temp = $l_ip_from_long;
            $l_ip_from_long = $l_ip_to_long;
            $l_ip_to_long = $l_temp;

            $l_temp = $p_from;
            $p_from = $p_to;
            $p_to = $l_temp;
        }

        $l_strSql = 'INSERT IGNORE INTO isys_cats_net_list
            SET isys_cats_net_list__description = ' . $this->convert_sql_text($p_description) . ',
            isys_cats_net_list__title = ' . $this->convert_sql_text($p_title) . ',
            isys_cats_net_list__address = ' . $this->convert_sql_text($p_address) . ',
            isys_cats_net_list__mask = ' . $this->convert_sql_text($p_netmask) . ',
            isys_cats_net_list__address_range_from = ' . $this->convert_sql_text($p_from) . ',
            isys_cats_net_list__address_range_to = ' . $this->convert_sql_text($p_to) . ',
            isys_cats_net_list__isys_net_type__id = ' . $this->convert_sql_id($p_typeID) . ',
            isys_cats_net_list__isys_net_dns_server__id = ' . $this->convert_sql_id($p_dnsID) . ',
            isys_cats_net_list__isys_net_dns_domain__id = ' . $this->convert_sql_id($p_domainID) . ',
            isys_cats_net_list__status = ' . $this->convert_sql_id($p_newRecStatus) . ',
            isys_cats_net_list__isys_obj__id = ' . $this->convert_sql_id($p_objID) . ',
            isys_cats_net_list__cidr_suffix = ' . $this->convert_sql_int($p_cidr_suffix) . ',
            isys_cats_net_list__isys_catg_ip_list__id = ' . $this->convert_sql_id($p_gateway) . ',
            isys_cats_net_list__address_long = ' . $this->convert_sql_text(Ip::ip2long($p_address)) . ',
            isys_cats_net_list__mask_long = ' . $this->convert_sql_text(Ip::ip2long($p_netmask)) . ',
            isys_cats_net_list__address_range_from_long = ' . $this->convert_sql_text($l_ip_from_long) . ',
            isys_cats_net_list__address_range_to_long = ' . $this->convert_sql_text($l_ip_to_long) . ',
            isys_cats_net_list__reverse_dns = ' . $this->convert_sql_text($p_reverse_dns) . ';';

        if ($this->update($l_strSql) && $this->apply_update()) {
            $l_last_id = $this->get_last_insert_id();

            if (!empty($p_dns_server_selected)) {
                if (is_string($p_dns_server_selected) || is_array($p_dns_server_selected)) {
                    if (!is_array($p_dns_server_selected)) {
                        $l_dns_server_arr = explode(",", $p_dns_server_selected);
                    } else {
                        $l_dns_server_arr = $p_dns_server_selected;
                    }

                    if (count($l_dns_server_arr) > 0) {
                        foreach ($l_dns_server_arr as $l_dns_server_id) {
                            $this->attach_dns_server($l_last_id, $l_dns_server_id);
                        }
                    }
                }
            }

            if (!empty($p_dns_domain_selected)) {
                if (is_string($p_dns_domain_selected) || is_array($p_dns_domain_selected)) {
                    if (!is_array($p_dns_domain_selected)) {
                        $l_dns_domain_arr = explode(",", $p_dns_domain_selected);
                    } else {
                        $l_dns_domain_arr = $p_dns_domain_selected;
                    }

                    if (count($l_dns_domain_arr) > 0) {
                        foreach ($l_dns_domain_arr as $l_dns_domain_id) {
                            $this->attach_dns_domain($l_last_id, $l_dns_domain_id);
                        }
                    }
                }
            }

            $l_layer2_ids = $this->getOrCreateLayer2Ids($p_layer_2_assignment);

            if (is_array($l_layer2_ids) && count($l_layer2_ids) > 0) {
                $this->add_layer2_assignment($l_layer2_ids, $p_objID);
            }

            return $l_last_id;
        } else {
            return false;
        }
    }

    /**
     * Method for retrieving assigned hosts from an object.
     *
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   integer $p_record_status
     * @param   string  $p_sort
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_assigned_hosts($p_obj_id, $p_condition = '', $p_record_status = C__RECORD_STATUS__NORMAL, $p_sort = null)
    {
        if (empty($p_obj_id)) {
            return false;
        }

        $l_sql = 'SELECT isys_catg_ip_list__isys_ip_assignment__id, isys_catg_ip_list__isys_ipv6_assignment__id, isys_catg_ip_list__isys_cats_net_ip_addresses_list__id, isys_cats_net_ip_addresses_list__isys_ip_assignment__id, isys_catg_ip_list__id, isys_obj__id, isys_obj__title, isys_obj_type__title, isys_cats_net_ip_addresses_list__title, isys_catg_ip_list__hostname
			FROM isys_catg_ip_list
			LEFT JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
			LEFT JOIN isys_obj ON isys_obj__id = isys_catg_ip_list__isys_obj__id
			LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_cats_net_ip_addresses_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			' . $p_condition . '
			AND isys_obj__status = ' . $this->convert_sql_int($p_record_status) . '
			AND isys_catg_ip_list__status = ' . $this->convert_sql_int($p_record_status) . ' ';

        if ($p_sort === null) {
            $l_sql .= 'ORDER BY isys_cats_net_ip_addresses_list__ip_address_long ASC;';
        } else {
            $l_sql .= $p_sort;
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for retrieving assigned hosts and their DNS data from an object (This might result in multiple rows).
     *
     * @param   integer $p_obj_id
     * @param   integer $p_record_status
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_assigned_hosts_with_dns($p_obj_id, $p_record_status = C__RECORD_STATUS__NORMAL)
    {
        if (empty($p_obj_id)) {
            return false;
        }

        $l_sql = 'SELECT
			main.isys_catg_ip_list__isys_ip_assignment__id,
			main.isys_catg_ip_list__isys_ipv6_assignment__id,
			main.isys_catg_ip_list__isys_cats_net_ip_addresses_list__id,
			main.isys_catg_ip_list__isys_obj__id__zone,
			isys_cats_net_ip_addresses_list__isys_ip_assignment__id,
			main.isys_catg_ip_list__id,
			isys_obj__id,
			isys_obj__title,
			isys_obj_type__title,
			isys_cats_net_ip_addresses_list__title,
			main.isys_catg_ip_list__hostname,
			dnstable.isys_net_dns_domain__id,
			dnstable.isys_net_dns_domain__title
			FROM isys_catg_ip_list AS main
			LEFT JOIN isys_cats_net_ip_addresses_list ON main.isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
			LEFT JOIN isys_obj ON isys_obj__id = main.isys_catg_ip_list__isys_obj__id
			LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			LEFT JOIN isys_catg_ip_list_2_isys_net_dns_domain AS sub ON sub.isys_catg_ip_list__id = main.isys_catg_ip_list__id
			LEFT JOIN isys_net_dns_domain AS dnstable ON sub.isys_net_dns_domain__id = dnstable.isys_net_dns_domain__id
			WHERE isys_cats_net_ip_addresses_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_obj__status = ' . $this->convert_sql_int($p_record_status) . '
			AND main.isys_catg_ip_list__status = ' . $this->convert_sql_int($p_record_status) . '
			ORDER BY isys_cats_net_ip_addresses_list__ip_address_long ASC;';

        return $this->retrieve($l_sql);
    }

    /**
     * Gets assigned DNS Server.
     *
     * @param   integer $p_cat_id
     * @param   integer $p_obj_id
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_assigned_dns_server($p_cat_id = null, $p_obj_id = null)
    {
        if (empty($p_cat_id) && empty($p_connected_obj_id)) {
            return [];
        }

        $l_arr = [];
        $l_sql = 'SELECT * FROM isys_cats_net_list_2_isys_catg_ip_list ';

        if (!empty($p_cat_id) && empty($p_obj_id)) {
            $l_sql .= 'WHERE isys_cats_net_list__id = ' . $this->convert_sql_id($p_cat_id) . ';';
        } elseif (empty($p_cat_id) && !empty($p_obj_id)) {
            $l_sql .= 'WHERE isys_cats_net_list__id = (SELECT isys_cats_net_list__id FROM isys_cats_net_list WHERE isys_cats_net_list__isys_obj__id = ' .
                $this->convert_sql_id($p_obj_id) . ');';
        }

        $l_res = $this->retrieve($l_sql);

        if (is_countable($l_res) && count($l_res) > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_arr[] = $l_row['isys_catg_ip_list__id'];
            }
        }

        return $l_arr;
    }

    /**
     * Gets assigned dns domains for the transferred object id
     *
     * @param   integer $p_obj_id
     * @param   integer $p_id
     *
     * @return  isys_component_dao_result
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_assigned_dns_domain($p_obj_id = null, $p_id = null)
    {
        if (empty($p_obj_id) && empty($p_id)) {
            return false;
        }
        $l_condition = '';

        $l_sql = 'SELECT dnstable.isys_net_dns_domain__id, dnstable.isys_net_dns_domain__title
			FROM isys_cats_net_list_2_isys_net_dns_domain AS main
			INNER JOIN isys_net_dns_domain dnstable ON main.isys_net_dns_domain__id = dnstable.isys_net_dns_domain__id';

        if ($p_obj_id > 0) {
            $l_condition = ' WHERE main.isys_cats_net_list__id = (SELECT isys_cats_net_list__id FROM isys_cats_net_list WHERE isys_cats_net_list__isys_obj__id = ' .
                $this->convert_sql_id($p_obj_id) . ')';
        }

        if ($p_id > 0) {
            $l_condition = ' WHERE main.isys_cats_net_list__id = ' . $this->convert_sql_id($p_id);
        }

        return $this->retrieve($l_sql . $l_condition . ';');
    }

    /**
     * Gets all existing dns domains with a normal status.
     *
     * @param   string $p_filter
     *
     * @return  isys_component_dao_result
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_dns_domains($p_filter = '')
    {
        return $this->retrieve('SELECT * FROM isys_net_dns_domain WHERE isys_net_dns_domain__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' ' . $p_filter .
            ';');
    }

    /**
     * Deletes dns domain connection for the specified category entry id.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     * @throws  Exception
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function clear_dns_domain_attachments($p_id)
    {
        if (empty($p_id)) {
            return true;
        }

        try {
            $l_sql = 'DELETE FROM isys_cats_net_list_2_isys_net_dns_domain WHERE isys_cats_net_list__id = ' . $this->convert_sql_id($p_id) . ';';

            return ($this->update($l_sql) && $this->apply_update());
        } catch (Exception $e) {
            throw new Exception('Error while clearing attachments.');
        }
    }

    /**
     * Deletes dns server connection for the specified category entry id.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     * @throws  Exception
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function clear_dns_server_attachments($p_id)
    {
        if (empty($p_id)) {
            return true;
        }

        try {
            $l_sql = 'DELETE FROM isys_cats_net_list_2_isys_catg_ip_list WHERE isys_cats_net_list__id = ' . $this->convert_sql_id($p_id) . ';';

            return ($this->update($l_sql) && $this->apply_update());
        } catch (Exception $e) {
            throw new Exception('Error while clearing attachments.');
        }
    }

    /**
     * Creates new dns server connection with the specified category id.
     *
     * @param   integer $p_cat_id
     * @param   integer $p_cat_dns_server_id
     *
     * @return  boolean
     */
    public function attach_dns_server($p_cat_id, $p_cat_dns_server_id)
    {
        if (empty($p_cat_id) || empty($p_cat_dns_server_id)) {
            return true;
        }

        $l_insert = 'INSERT INTO isys_cats_net_list_2_isys_catg_ip_list (isys_cats_net_list__id, isys_catg_ip_list__id)
			VALUES (' . $this->convert_sql_id($p_cat_id) . ', ' . $this->convert_sql_id($p_cat_dns_server_id) . ')';

        return ($this->update($l_insert) && $this->apply_update());
    }

    /**
     * Creates new dns domain connection with the specified domain id.
     *
     * @param   integer $p_cat_id
     * @param   integer $p_dns_domain_id
     *
     * @return  boolean
     */
    public function attach_dns_domain($p_cat_id, $p_dns_domain_id)
    {
        if (empty($p_cat_id) || empty($p_dns_domain_id)) {
            return true;
        }

        $l_insert = 'INSERT INTO isys_cats_net_list_2_isys_net_dns_domain (isys_cats_net_list__id, isys_net_dns_domain__id)
			VALUES (' . $this->convert_sql_id($p_cat_id) . ', ' . $this->convert_sql_id($p_dns_domain_id) . ')';

        return ($this->update($l_insert) && $this->apply_update());
    }

    /**
     * A method, which bundles the handle_ajax_request and handle_preselection.
     *
     * @todo   Check if this is still in use.
     *
     * @param  integer $p_context
     * @param  array   $p_parameters
     *
     * @return array|string
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function object_browser($p_context, array $p_parameters)
    {
        $language = isys_application::instance()->container->get('language');
        $daoIp = isys_cmdb_dao_category_g_ip::instance($this->m_db);

        switch ($p_context) {
            case isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST:
                return $daoIp->object_browser($p_context, $p_parameters);

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PREPARATION:
                // Preselection
                $l_return = [
                    'category' => [],
                    'first'    => [],
                    'second'   => []
                ];

                $p_preselection = $p_parameters['preselection'];

                if ($p_preselection > 0) {
                    // Save a bit memory: Only select needed fields!
                    $l_sql = "SELECT * 
                        FROM isys_catg_ip_list
                        INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
                        LEFT JOIN isys_obj ON isys_obj__id = isys_catg_ip_list__isys_obj__id
                        LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id 
                        WHERE isys_catg_ip_list__id = " . $this->convert_sql_id($p_preselection) . " 
                        LIMIT 1;";

                    $l_dao = new isys_component_dao($this->m_db);

                    $l_res = $l_dao->retrieve($l_sql);

                    if ($l_res->num_rows() > 0) {
                        $l_row = $l_res->get_row();

                        $l_type = $language->get($l_row['isys_obj_type__title']);

                        // Prepare return data.
                        $l_return['category'] = $l_row['isys_obj__isys_obj_type__id'];

                        // @see  ID-6220  Also return a 'first' selection.
                        $l_return['first'][] = (int)$l_row['isys_obj__id'];

                        $l_return['second'] = [
                            $l_row['isys_catg_ip_list__id'],
                            $l_row['isys_cats_net_ip_addresses_list__title'],
                        ];
                    }
                }

                return $l_return;

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PRESELECTION:
                return $daoIp->object_browser($p_context, $p_parameters);
        }
    }

    /**
     * A method, which bundles the handle_ajax_request and handle_preselection.
     *
     * @param  integer $p_context
     * @param  array   $p_parameters
     *
     * @return array|string
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function object_browser2($p_context, array $p_parameters)
    {
        return isys_cmdb_dao_category_g_ip::instance($this->m_db)->object_browser($p_context, $p_parameters);
    }

    /**
     * This method retrieves the formatted string for the connected DNS server inside the layer3 net category.
     *
     * @param  integer $p_ip_id
     *
     * @return string
     * @throws Exception
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function format_selection($p_ip_id)
    {
        $language = isys_application::instance()->container->get('language');

        if (empty($p_ip_id)) {
            return $language->get("LC__CMDB__BROWSER_OBJECT__NONE_SELECTED");
        }

        // We need a DAO for the object name.
        $l_dao_ip = new isys_cmdb_dao_category_g_ip($this->m_db);
        $l_quick_info = new isys_ajax_handler_quick_info();

        $l_row = $l_dao_ip->get_ip_by_id($p_ip_id);

        $p_object_type = $l_dao_ip->get_objTypeID($l_row["isys_catg_ip_list__isys_obj__id"]);

        $l_title = $language->get($l_dao_ip->get_objtype_name_by_id_as_string($p_object_type)) . " >> " .
            $l_dao_ip->get_obj_name_by_id_as_string($l_row["isys_catg_ip_list__isys_obj__id"]) . " >> " .
            $l_row["isys_cats_net_ip_addresses_list__title"];

        if (isys_glob_is_edit_mode()) {
            return $l_title;
        }

        return $l_quick_info->get_quick_info($l_row['isys_catg_ip_list__isys_obj__id'], $l_title, C__LINK__OBJECT);
    }

    /**
     * Retrieve a matching layer 3 net by an inherited ipv4 address
     *
     * @param $p_ip_address
     *
     * @return array
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_matching_net_by_ipv4_address($p_ip_address)
    {
        if ($p_ip_address) {
            return $this->retrieve('SELECT isys_cats_net_list__isys_obj__id AS netID, isys_cats_net_list__address_range_from AS rangeFrom, isys_cats_net_list__address_range_to AS rangeTo ' .
                'FROM isys_cats_net_list ' . 'WHERE (' . Ip::ip2long($p_ip_address) .
                ' BETWEEN isys_cats_net_list__address_range_from_long AND isys_cats_net_list__address_range_to_long)' . ' AND (isys_cats_net_list__isys_obj__id != ' .
                ((int)defined_or_default('C__OBJ__NET_GLOBAL_IPV4')) . ') ' . // Order by the smallest possible range (which should be the most exact one)
                ' AND isys_cats_net_list__isys_net_type__id = ' . ((int)defined_or_default('C__CATS_NET_TYPE__IPV4')) .
                ' ORDER BY (isys_cats_net_list__address_range_to_long - isys_cats_net_list__address_range_from_long) ASC ' . 'LIMIT 1')
                ->get_row();
        }

        return [
            'netID'     => null,
            'rangeFrom' => '',
            'rangeTo'   => ''
        ];
    }

    /**
     * Gets all information about the net by the specified object id.
     *
     * @param   integer $p_obj_id
     *
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     * @return  array
     */
    public function get_all_net_information_by_obj_id($p_obj_id)
    {
        $l_net_row = [];

        if (class_exists('isys_cmdb_dao_category_s_net_ip_addresses')) {
            $l_dao_ip_list = new isys_cmdb_dao_category_s_net_ip_addresses($this->get_database_component());
            $l_ip_list_res = $l_dao_ip_list->get_data(null, $p_obj_id, '', [], C__RECORD_STATUS__NORMAL);

            $l_ip_list_data = [];
            while ($l_ip_list_row = $l_ip_list_res->get_row()) {
                $l_ip_list_data[] = ($l_ip_list_row['isys_catg_ip_list__isys_net_type__id'] ==
                    defined_or_default('C__CATS_NET_TYPE__IPV4')) ? Ip::ip2long($l_ip_list_row['isys_cats_net_ip_addresses_list__title']) : $l_ip_list_row['isys_cats_net_ip_addresses_list__title'];
            }

            $l_net_row['used_ips'] = $l_ip_list_data;
        } else {
            $l_net_row['used_ips'] = [];
        }

        $l_dao_dhcp = new isys_cmdb_dao_category_s_net_dhcp($this->get_database_component());
        $l_dao_ip = new isys_cmdb_dao_category_g_ip($this->get_database_component());

        $l_net_row = $this->get_data(null, $p_obj_id, '', null, C__RECORD_STATUS__NORMAL)
            ->get_row();
        $l_objtype = isys_application::instance()->container->get('language')
            ->get($this->get_objtype_name_by_id_as_string($l_net_row['isys_obj__isys_obj_type__id']));
        $l_net_row['object_browser_title'] = $l_objtype . ' >> ' . $l_net_row['isys_obj__title'];

        // Get DNS Server from net.
        $l_assigned_dns_server = $this->get_assigned_dns_server($l_net_row['isys_cats_net_list__id']);
        $assignedDnsServerCount = is_countable($l_assigned_dns_server) ? count($l_assigned_dns_server) : 0;
        for ($i = 0;$i < $assignedDnsServerCount;$i++) {
            $l_assigned_dns_server[$i] = [
                'id'      => $l_assigned_dns_server[$i],
                'details' => $l_dao_ip->format_selection($l_assigned_dns_server[$i], true)
            ];
        }

        $l_net_row['assigned_dns_server'] = $l_assigned_dns_server;

        // Get DNS Domain from net.
        $l_domain_arr = [];
        $l_res_dns_domain = $this->get_assigned_dns_domain(null, $l_net_row['isys_cats_net_list__id']);
        if ($l_res_dns_domain) {
            while ($l_dns_domain_row = $l_res_dns_domain->get_row()) {
                $l_domain_arr[] = [
                    'caption' => $l_dns_domain_row['isys_net_dns_domain__title'],
                    'value'   => $l_dns_domain_row['isys_net_dns_domain__id']
                ];
            }
        }

        $l_net_row['assigned_dns_domain'] = $l_domain_arr;

        $dhcpTypeTable = $l_net_row['isys_cats_net_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV6') ? 'isys_net_dhcpv6_type' : 'isys_net_dhcp_type';

        $l_dhcp_types_res = $this->get_dialog($dhcpTypeTable);
        $l_dhcp_types = [];
        while ($l_dhcp_type_row = $l_dhcp_types_res->get_row()) {
            $l_dhcp_types[$l_dhcp_type_row[$dhcpTypeTable . '__const']] = $l_dhcp_type_row[$dhcpTypeTable . '__id'];
        }

        if (count($l_dhcp_types) > 0) {
            $l_dhcp_arr = [];
            foreach ($l_dhcp_types as $l_dhcp_key => $l_dhcp_type_id) {
                $l_dhcp_res = $l_dao_dhcp->get_data(
                    null,
                    $p_obj_id,
                    ' AND ' . $dhcpTypeTable . '__id = ' . $this->convert_sql_id($l_dhcp_type_id),
                    null,
                    C__RECORD_STATUS__NORMAL
                );
                while ($l_dhcp_row = $l_dhcp_res->get_row()) {
                    $l_dhcp_arr[$l_dhcp_key][] = [
                        'from' => Ip::ip2long($l_dhcp_row['isys_cats_net_dhcp_list__range_from']),
                        'to'   => Ip::ip2long($l_dhcp_row['isys_cats_net_dhcp_list__range_to'])
                    ];
                }
            }
            $l_net_row['dhcp_ranges'] = $l_dhcp_arr;
        }

        return $l_net_row;
    }

    /**
     * Gets net type.
     *
     * @param   integer $p_id
     *
     * @return  isys_component_dao_result
     */
    public function get_net_types($p_id = null)
    {
        $l_sql = 'SELECT * FROM isys_net_type ';

        if (is_numeric($p_id)) {
            $l_sql .= 'WHERE isys_net_type__id = ' . $this->convert_sql_id($p_id);
        } elseif (is_string($p_id)) {
            $l_sql .= 'WHERE isys_net_type__const = ' . $this->convert_sql_text($p_id);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Retrieves all L3 nets, which may collide with the given range.
     *
     * @param   string  $p_from
     * @param   string  $p_to
     * @param   integer $p_net_type Optional net-type to find IPv4 or IPv6 layer3 nets.
     * @param   integer $p_obj_id   Optional object-id to skip the own object during the search.
     *
     * @return  isys_component_dao_result
     */
    public function find_net_collision($p_from, $p_to, $p_net_type = null, $p_obj_id = null)
    {
        if ($p_net_type === null && defined('C__CATS_NET_TYPE__IPV4')) {
            $p_net_type = C__CATS_NET_TYPE__IPV4;
        }
        $l_obj_id = null;
        $l_from = $this->convert_sql_text(Ip::ip2long($p_from));
        $l_to = $this->convert_sql_text(Ip::ip2long($p_to));

        if ($p_obj_id !== null && $this->convert_sql_id($p_obj_id) !== 'NULL') {
            $l_obj_id = $this->convert_sql_id($p_obj_id);
        }

        $l_condition = 'AND isys_obj__isys_obj_type__id = ' . $this->convert_sql_id(defined_or_default('C__OBJTYPE__LAYER3_NET')) . '
			AND (
				((' . $l_from . ' BETWEEN isys_cats_net_list__address_range_from_long AND isys_cats_net_list__address_range_to_long) OR (' . $l_to . ' BETWEEN isys_cats_net_list__address_range_from_long AND isys_cats_net_list__address_range_to_long))
				OR
				(' . $l_from . ' <= isys_cats_net_list__address_range_from_long AND isys_cats_net_list__address_range_to_long <= ' . $l_to . ')
				OR
				(isys_cats_net_list__address_range_from_long <= ' . $l_from . ' AND ' . $l_to . ' <= isys_cats_net_list__address_range_to_long)
			)
			AND isys_cats_net_list__isys_net_type__id = ' . $this->convert_sql_id($p_net_type) . '
			AND isys_obj__id ' . $this->prepare_in_condition([
                defined_or_default('C__OBJ__NET_GLOBAL_IPV4'),
                defined_or_default('C__OBJ__NET_GLOBAL_IPV6'),
                $l_obj_id
            ], true);

        return $this->get_data(null, null, $l_condition, null, C__RECORD_STATUS__NORMAL);
    }

    /**
     * @param $objectIds
     *
     * @return array|false
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_database
     */
    public function getOrCreateLayer2Ids($objectIds)
    {
        $ids = $objectIds;
        $results = [];

        if (is_numeric($objectIds) && $objectIds > 0) {
            $ids = [$objectIds];
        } elseif (is_string($objectIds)) {
            try {
                $ids = isys_format_json::decode($objectIds);
            } catch (\idoit\Exception\JsonException $e) {
            }
        }

        if (!is_array($ids)) {
            return false;
        }

        $res = $this->retrieve('SELECT isys_obj__id as objectId, isys_cats_layer2_net_list__id as categoryId ' .
            'FROM isys_obj ' .
            'LEFT JOIN isys_cats_layer2_net_list ON isys_cats_layer2_net_list__isys_obj__id = isys_obj__id ' .
            'WHERE isys_obj__id IN ('. implode(',', $ids) .');');

        if ($res->count()) {
            while ($row = $res->get_row()) {
                $categoryId = $row['categoryId'] ?: $this->create_connector('isys_cats_layer2_net_list', $row['objectId']);

                $results[$row['objectId']] = $categoryId;
            }
        }

        return $results;
    }

    /**
     * Gets assigned layer 2 net ids
     *
     * @param      $p_obj_id
     * @param bool $p_as_obj_id
     *
     * @return array|null
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_assigned_layer_2_ids($p_obj_id, $p_as_obj_id = false)
    {
        $l_sql = 'SELECT l2.isys_cats_layer2_net_list__id, l2.isys_cats_layer2_net_list__isys_obj__id FROM isys_cats_layer2_net_2_layer3
            INNER JOIN isys_cats_layer2_net_list AS l2 ON l2.isys_cats_layer2_net_list__id = isys_cats_layer2_net_2_layer3.isys_cats_layer2_net_list__id 
            WHERE isys_obj__id = ' . $this->convert_sql_id($p_obj_id);

        $l_res = $this->retrieve($l_sql);
        $l_return = null;
        if ($l_res->num_rows() > 0) {
            while ($l_row = $l_res->get_row()) {
                if ($p_as_obj_id) {
                    $l_return[] = $l_row['isys_cats_layer2_net_list__isys_obj__id'];
                } else {
                    $l_return[$l_row['isys_cats_layer2_net_list__isys_obj__id']] = $l_row['isys_cats_layer2_net_list__id'];
                }
            }
        }

        return $l_return;
    }

    /**
     * Retrieves layer2 assignments as result set or as array with object informations
     *
     * @param      $p_obj_id
     * @param bool $p_as_array
     *
     * @return array|isys_component_dao_result
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_assigned_layer_2($p_obj_id, $p_as_array = false)
    {
        $l_sql = 'SELECT isys_obj.isys_obj__id, isys_obj.isys_obj__title, isys_obj.isys_obj__isys_obj_type__id, isys_obj.isys_obj__status
			FROM isys_cats_layer2_net_2_layer3
			INNER JOIN isys_cats_layer2_net_list AS l2 ON l2.isys_cats_layer2_net_list__id = isys_cats_layer2_net_2_layer3.isys_cats_layer2_net_list__id
			INNER JOIN isys_obj ON isys_obj.isys_obj__id = l2.isys_cats_layer2_net_list__isys_obj__id WHERE isys_cats_layer2_net_2_layer3.isys_obj__id = ' .
            $this->convert_sql_id($p_obj_id);

        $l_res = $this->retrieve($l_sql);

        if ($p_as_array) {
            $l_return = [];
            while ($l_row = $l_res->get_row()) {
                $l_return[] = $l_row;
            }
        } else {
            $l_return = $l_res;
        }

        return $l_return;
    }

    /**
     * Adds an assignment to a layer 2 net
     *
     * @param array $layer2Ids Key: object ID, value: category entry ID
     * @param int   $assignedObjectId
     *
     * @return bool
     * @throws isys_exception_dao
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function add_layer2_assignment(array $layer2Ids = [], $assignedObjectId)
    {
        if (empty($layer2Ids)) {
            return false;
        }

        $l_insert = 'INSERT INTO isys_cats_layer2_net_2_layer3 (isys_cats_layer2_net_list__id, isys_obj__id) VALUES';
        $relationDao = isys_cmdb_dao_category_g_relation::instance(isys_application::instance()->container->get('database'));

        foreach ($layer2Ids as $layer2Id) {
            $l_insert .= ' (' . $this->convert_sql_id($layer2Id) . ', ' . $this->convert_sql_id($assignedObjectId) . '), ';
        }
        $l_insert = rtrim($l_insert, ', ');
        $this->update($l_insert);

        foreach ($layer2Ids as $layer2Objectid => $layer2Id) {
            $query = 'SELECT isys_cats_layer2_net_2_layer3__id FROM isys_cats_layer2_net_2_layer3 WHERE 
                isys_cats_layer2_net_list__id = ' . $this->convert_sql_id($layer2Id) . ' AND isys_obj__id = ' . $this->convert_sql_id($assignedObjectId);
            $entryId = $this->retrieve($query)->get_row_value('isys_cats_layer2_net_2_layer3__id');

            $relationDao->create_relation(
                'isys_cats_layer2_net_2_layer3',
                $entryId,
                $assignedObjectId,
                $layer2Objectid,
                'C__RELATION_TYPE__LAYER3_2_LAYER2'
            );
        }

        return $this->apply_update();
    }

    /**
     * Removes an assignment to a layer 2 net
     *
     * @param $p_layer2_id
     * @param $p_obj_id
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function remove_layer2_assignment($p_layer2_id, $p_obj_id)
    {
        // First delete relation
        $relationDao = isys_cmdb_dao_category_g_relation::instance(isys_application::instance()->container->get('database'));

        if (is_numeric($p_layer2_id)) {
            $additionalCondition = ' isys_cats_layer2_net_list__id = ' . $this->convert_sql_id($p_layer2_id);
        } elseif (is_array($p_layer2_id) && count($p_layer2_id) > 0) {
            $additionalCondition = ' isys_cats_layer2_net_list__id IN (' . implode(',', $p_layer2_id) . ')';
        } else {
            $additionalCondition = ' isys_cats_layer2_net_list__id = FALSE';
        }
        $condition = 'isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' AND ' . $additionalCondition;

        $query = 'SELECT isys_cats_layer2_net_2_layer3__isys_catg_relation_list__id as relId FROM isys_cats_layer2_net_2_layer3 WHERE ' . $condition;
        $result = $this->retrieve($query);
        if (count($result)) {
            while ($row = $result->get_row()) {
                $relationDao->delete_relation($row['relId']);
            }
        }

        return ($this->update('DELETE FROM isys_cats_layer2_net_2_layer3 WHERE ' . $condition) && $this->apply_update());
    }

    /**
     * @param $row
     *
     * @return string
     * @throws isys_exception_database
     */
    public function retrieveDnsServer($row)
    {
        // get_assigned_dns_server
        if (!isset($row['isys_cats_net_list__id'])) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        /**
         * dns_server is a old fashioned property - an array
         *
         * @var array $property
         */
        $property = $this->property('dns_server');
        $select = $property[C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT];
        $select->setSelectGroupBy(null);
        $select->setSelectCondition(SelectCondition::factory(['dns_server.isys_cats_net_list__id = ' . $this->convert_sql_id($row['isys_cats_net_list__id'])]));

        $result = $this->retrieve($select);

        if ($result instanceof isys_component_dao_result && count($result)) {
            $dnsServers = [];
            while ($data = $result->get_row()) {
                $dnsServers[] = $data['dnsTitle'];
            }
            return implode(', ', $dnsServers);
        }
        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * @param $row
     *
     * @return string
     * @throws isys_exception_database
     */
    public function retrieveDnsDomain($row)
    {
        // get_assigned_dns_server
        if (!isset($row['isys_cats_net_list__id'])) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        /**
         * @var Property $property
         */
        $property = $this->property('dns_domain');
        $select = $property[C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT];
        $select->setSelectGroupBy(null);
        $select->setSelectCondition(SelectCondition::factory(['main.isys_cats_net_list__id = ' . $this->convert_sql_id($row['isys_cats_net_list__id'])]));

        $result = $this->retrieve($select);

        if ($result instanceof isys_component_dao_result && count($result)) {
            $dnsDomains = [];
            while ($data = $result->get_row()) {
                $dnsDomains[] = $data['isys_net_dns_domain__title'];
            }
            return implode(', ', $dnsDomains);
        }
        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_address_range' => new DynamicProperty(
                'LC__CMDB__CATS__NET__ADDRESS_RANGE',
                'isys_cats_net_list__id',
                'isys_cats_net_list',
                [
                    $this,
                    'dynamic_property_callback_address_range'
                ]
            ),
            '_address_with_suffix' => new DynamicProperty(
                'LC__CMDB__CATS__NET__ADDRESS_WITH_SUFFIX',
                'isys_cats_net_list__id',
                'isys_cats_net_list',
                [
                    $this,
                    'dynamic_property_callback_address_with_suffix'
                ]
            ),
            '_address' => new DynamicProperty(
                'LC__CMDB__CATS__NET',
                'isys_cats_net_list__id',
                'isys_cats_net_list',
                [
                    $this,
                    'dynamic_property_callback_address'
                ]
            ),
            '_free_addresses' => new DynamicProperty(
                'LC__CMDB__CATG__NETWORK__ASS_IP',
                'isys_cats_net_list__id',
                'isys_cats_net_list',
                [
                    $this,
                    'dynamic_property_callback_free_addresses'
                ]
            ),
            '_netmask' => new DynamicProperty(
                'LC__CATP__IP__SUBNETMASK',
                'isys_cats_net_list__id',
                'isys_cats_net_list',
                [
                    $this,
                    'dynamic_property_callback_netmask'
                ]
            ),
            '_layer2_assignments'  => new DynamicProperty(
                'LC__CMDB__CATS__NET__LAYER2_NET',
                'isys_cats_net_list__isys_obj__id',
                'isys_cats_net_list',
                [
                    $this,
                    'dynamic_property_callback_layer2_assignment'
                ]
            ),
            '_gateway' => new DynamicProperty(
                'LC__CMDB__CATS__NET__DEF_GW',
                'isys_cats_net_list__id',
                'isys_cats_net_list',
                [
                    $this,
                    'retrieveDefaultGateway'
                ]
            ),
            '_dns_server' => (new DynamicProperty(
                'LC__CMDB__CATS__NET__DNS_SERVER',
                'isys_cats_net_list__id',
                'isys_cats_net_list',
                [
                    $this,
                    'retrieveDnsServer'
                ]
            )),
            '_dns_domain' => new DynamicProperty(
                'LC__CMDB__CATS__NET__DNS_DOMAIN',
                'isys_cats_net_list__id',
                'isys_cats_net_list',
                [
                    $this,
                    'retrieveDnsDomain'
                ]
            )
        ];
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_cats_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_cats_net_list
			INNER JOIN isys_obj ON isys_cats_net_list__isys_obj__id = isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			LEFT OUTER JOIN isys_net_type ON isys_net_type__id = isys_cats_net_list__isys_net_type__id
			WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_cats_list_id !== null) {
            $l_sql .= ' AND isys_cats_net_list__id = ' . $this->convert_sql_id($p_cats_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= ' AND isys_cats_net_list__status = ' . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'title'               => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_net_list__title'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATS__NET__TITLE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => false,
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__IMPORT => false,
                    C__PROPERTY__PROVIDES__EXPORT => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'type' => (new DialogProperty(
                'C__CATS__NET__TYPE',
                'LC__CMDB__CATS__NET__TYPE',
                'isys_cats_net_list__isys_net_type__id',
                'isys_cats_net_list',
                'isys_net_type'
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__INDEX => true
            ])->mergePropertyUiParams([
                'p_bDisabled'  => 1,
                'p_bDbFieldNN' => 1
            ]),
            'address'             => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__NET',
                    C__PROPERTY__INFO__DESCRIPTION => 'Net'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_net_list__address',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT (CASE WHEN IS_IPV6(isys_cats_net_list__address) THEN INET6_NTOA(INET6_ATON(isys_cats_net_list__address))
                                  WHEN IS_IPV4(isys_cats_net_list__address) THEN CONCAT(\'<span data-sort="\', LPAD(isys_cats_net_list__address_long, 10, \'0\'), \'">\', isys_cats_net_list__address, \'</span>\')
                                  ELSE ' . $this->convert_sql_text(isys_tenantsettings::get('gui.empty_value')) . ' END)
                            FROM isys_cats_net_list', 'isys_cats_net_list', 'isys_cats_net_list__id', 'isys_cats_net_list__isys_obj__id'),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_net_list', 'LEFT', 'isys_cats_net_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__NET__NET_V4',
                    C__PROPERTY__UI__PARAMS => [
                        'p_bReadonly' => '',
                        'p_strClass'  => 'input-mini'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => true,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'netmask'             => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__NET__MASK',
                    C__PROPERTY__INFO__DESCRIPTION => 'Netmask'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_net_list__mask'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__NET__MASK_V4',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass'  => 'input-mini',
                        'p_bReadonly' => ''
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'gateway'             => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__NET__DEF_GW',
                    C__PROPERTY__INFO__DESCRIPTION => 'Default Gateway'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_net_list__isys_catg_ip_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_cats_net_ip_addresses_list__title, \' - \', isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_cats_net_list
                            INNER JOIN isys_catg_ip_list ON isys_catg_ip_list__id = isys_cats_net_list__isys_catg_ip_list__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_ip_list__isys_obj__id
                            INNER JOIN isys_cats_net_ip_addresses_list AS gw ON gw.isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
                        'isys_cats_net_list',
                        'isys_cats_net_list__id',
                        'isys_cats_net_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_net_list', 'LEFT', 'isys_cats_net_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_ip_list',
                            'LEFT',
                            'isys_cats_net_list__isys_catg_ip_list__id',
                            'isys_catg_ip_list__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_ip_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_net_ip_addresses_list',
                            'LEFT',
                            'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
                            'isys_cats_net_ip_addresses_list__id',
                            '',
                            'gw',
                            'gw'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__NET__DEF_GW_V4',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_s_net',
                            'callback_property_gateway'
                        ])
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_specific_net_export_helper',
                        'exportGateway'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'range_from'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__NET__ADDRESS_FROM',
                    C__PROPERTY__INFO__DESCRIPTION => 'DHCP from'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_net_list__address_range_from',
                    C__PROPERTY__DATA__SORT_ALIAS => 'isys_cats_net_list__address_range_from_long',
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__NET__ADDRESS_RANGE_FROM',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-mini'
                    ]
                ]
            ]),
            'range_to'            => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__NET__ADDRESS_TO',
                    C__PROPERTY__INFO__DESCRIPTION => 'DHCP to'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_net_list__address_range_to',
                    C__PROPERTY__DATA__SORT_ALIAS => 'isys_cats_net_list__address_range_to_long',
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__NET__ADDRESS_RANGE_TO',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-mini'
                    ]
                ]
            ]),
            'dns_server'          => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__NET__DNS_SERVER',
                    C__PROPERTY__INFO__DESCRIPTION => 'DNS server'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_cats_net_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_cats_net_list_2_isys_catg_ip_list',
                        'isys_cats_net_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj_type__title, \' > \', isys_obj__title, \' > \', isys_cats_net_ip_addresses_list__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_cats_net_list AS dns_server
                            INNER JOIN isys_cats_net_list_2_isys_catg_ip_list AS con ON con.isys_cats_net_list__id = dns_server.isys_cats_net_list__id
                            INNER JOIN isys_catg_ip_list AS ip ON ip.isys_catg_ip_list__id = con.isys_catg_ip_list__id
                            INNER JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = ip.isys_catg_ip_list__isys_cats_net_ip_addresses_list__id
                            INNER JOIN isys_obj ON isys_obj__id = ip.isys_catg_ip_list__isys_obj__id
                            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id',
                        'isys_cats_net_list',
                        'dns_server.isys_cats_net_list__id',
                        'dns_server.isys_cats_net_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['dns_server.isys_cats_net_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__NET__ASSIGNED_DNS_SERVER',
                    C__PROPERTY__UI__PARAMS => [
                        'multiselection'   => true,
                        'catFilter'        => 'C__CATG__IP',
                        'secondSelection'  => 'true',
                        'secondList'       => 'isys_cmdb_dao_category_s_net::object_browser2',
                        'secondListFormat' => 'isys_cmdb_dao_category_s_net::format_selection'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH  => false,
                    C__PROPERTY__PROVIDES__REPORT  => false,
                    C__PROPERTY__PROVIDES__VIRTUAL => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_specific_net_export_helper',
                        'exportDnsServer'
                    ]
                ]
            ]),
            'dns_domain'          => (new DialogPlusMultiselectProperty(
                'C__CATS__NET__DNS_DOMAIN',
                'LC__CMDB__CATS__NET__DNS_DOMAIN',
                'isys_cats_net_list__id',
                'isys_cats_net_list',
                'isys_cats_net_list_2_isys_net_dns_domain',
                'isys_net_dns_domain',
                ''
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH     => false,
                Property::C__PROPERTY__PROVIDES__VALIDATION => false,
                Property::C__PROPERTY__PROVIDES__REPORT     => false,
                Property::C__PROPERTY__PROVIDES__VIRTUAL    => true
            ])->mergePropertyUiParams([
                'placeholder'  => isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__CATS__NET__DNS_DOMAIN'),
                'emptyMessage' => isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__CATS__NET__NO_DNS_DOMAINS_FOUND'),
                'p_onComplete' => "idoit.callbackManager.triggerCallback('cmdb-cats-net-dns_domain-update', selected);",
            ]),
            'cidr_suffix'         => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__NET__CIDR_SUFFIX',
                    C__PROPERTY__INFO__DESCRIPTION => 'CIDR-Suffix'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_net_list__cidr_suffix'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__NET__CIDR',
                    C__PROPERTY__UI__PARAMS => [
                        'p_bReadonly' => '',
                        'p_strClass'  => 'input input-mini'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'reverse_dns'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATS__NET__REVERSE_DNS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Reverse dns'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_net_list__reverse_dns'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__NET__REVERSE_DNS'
                ]
            ]),
            'layer2_assignments'  => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__NET__LAYER2_NET',
                    C__PROPERTY__INFO__DESCRIPTION => 'Layer-2-net assignments'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj_type__title, \' > \', obj.isys_obj__title, \' {\', obj.isys_obj__id, \'}\')
                            FROM isys_cats_net_list AS layer2_assignment
                            INNER JOIN isys_cats_layer2_net_2_layer3 AS con ON con.isys_obj__id = layer2_assignment.isys_cats_net_list__isys_obj__id
                            INNER JOIN isys_cats_layer2_net_list AS l2 ON l2.isys_cats_layer2_net_list__id= con.isys_cats_layer2_net_list__id
                            INNER JOIN isys_obj AS obj ON obj.isys_obj__id = l2.isys_cats_layer2_net_list__isys_obj__id
                            INNER JOIN isys_obj_type ON isys_obj_type__id = obj.isys_obj__isys_obj_type__id',
                        'isys_cats_net_list',
                        'layer2_assignment.isys_cats_net_list__id',
                        'layer2_assignment.isys_cats_net_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['layer2_assignment.isys_cats_net_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__NET__LAYER2',
                    C__PROPERTY__UI__PARAMS => [
                        isys_popup_browser_object_ng::C__TITLE          => 'LC__BROWSER__TITLE__NET',
                        isys_popup_browser_object_ng::C__MULTISELECTION => true,
                        isys_popup_browser_object_ng::C__CAT_FILTER     => 'C__CATS__LAYER2_NET'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH  => false,
                    C__PROPERTY__PROVIDES__VIRTUAL => true,
                    C__PROPERTY__PROVIDES__REPORT  => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_specific_net_export_helper',
                        'exportLayer2Assignments'
                    ]
                ]
            ]),
            'address_v6'          => array_replace_recursive(isys_cmdb_dao_category_pattern::virtual(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__NET',
                    C__PROPERTY__INFO__DESCRIPTION => 'Net v6'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__NET__NET_V6',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-mini'
                    ]
                ]
            ]),
            'address_range'       => array_replace_recursive(isys_cmdb_dao_category_pattern::virtual(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__NET__ADDRESS_RANGE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Address range'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE
                                WHEN IS_IPV4(isys_cats_net_list__address_range_from) THEN
                                    CONCAT(isys_cats_net_list__address_range_from, \' - \', isys_cats_net_list__address_range_to)
                                WHEN IS_IPV6(isys_cats_net_list__address_range_from) THEN
                                    CONCAT(INET6_NTOA(INET6_ATON(isys_cats_net_list__address_range_from)), \' - \', INET6_NTOA(INET6_ATON(isys_cats_net_list__address_range_to)))
                             END)
                            FROM isys_cats_net_list',
                        'isys_cats_net_list',
                        'isys_cats_net_list__id',
                        'isys_cats_net_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_net_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__SORT => 'SELECT isys_cats_net_list__address_range_from_long FROM isys_cats_net_list
                                WHERE isys_cats_net_list__isys_obj__id = obj_main.isys_obj__id LIMIT 1'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                ]
            ]),
            'address_with_suffix' => array_replace_recursive(isys_cmdb_dao_category_pattern::virtual(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__NET__ADDRESS_WITH_SUFFIX',
                    C__PROPERTY__INFO__DESCRIPTION => 'Net address with suffix'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE
                                WHEN IS_IPV6(isys_cats_net_list__address) THEN CONCAT(INET6_NTOA(INET6_ATON(isys_cats_net_list__address)), \' / \', isys_cats_net_list__cidr_suffix)
                                WHEN IS_IPV4(isys_cats_net_list__address) THEN CONCAT(INET_NTOA(INET_ATON(isys_cats_net_list__address)), \' / \', isys_cats_net_list__cidr_suffix)
                                ELSE ' . $this->convert_sql_text(isys_tenantsettings::get('gui.empty_value', '-')) . ' END)
                            FROM isys_cats_net_list',
                        'isys_cats_net_list',
                        'isys_cats_net_list__id',
                        'isys_cats_net_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_net_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__SORT => 'SELECT isys_cats_net_list__address_range_from_long FROM isys_cats_net_list
                                WHERE isys_cats_net_list__isys_obj__id = obj_main.isys_obj__id LIMIT 1'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                ]
            ]),
            'free_addresses'      => array_replace_recursive(isys_cmdb_dao_category_pattern::virtual(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__NETWORK__ASS_IP',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned addresses and free addresses'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT CONCAT((SELECT COUNT(isys_cats_net_ip_addresses_list__id)
                                FROM isys_cats_net_ip_addresses_list LEFT JOIN isys_catg_ip_list
                                ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
                                WHERE isys_cats_net_ip_addresses_list__isys_obj__id = isys_cats_net_list__isys_obj__id 
                                  AND isys_catg_ip_list__id IS NOT NULL 
                                  AND (isys_cats_net_ip_addresses_list__title != \'\' OR isys_cats_net_ip_addresses_list__ip_address_long > 0)
                                  AND isys_catg_ip_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '),
                                (CASE WHEN isys_cats_net_list__address_range_to_long = 0 AND isys_cats_net_list__address_range_from_long = 0 THEN \'\' ELSE \' / \' END),
                                (CASE
                                  WHEN isys_cats_net_list__address_range_to_long = 0 AND isys_cats_net_list__address_range_from_long = 0 THEN \'\'
                                  WHEN isys_cats_net_list__address_range_to_long >= isys_cats_net_list__address_range_from_long THEN
                                    (1 + isys_cats_net_list__address_range_to_long - isys_cats_net_list__address_range_from_long)
                                  WHEN isys_cats_net_list__address_range_from_long >= isys_cats_net_list__address_range_to_long THEN
                                    (1 + isys_cats_net_list__address_range_from_long - isys_cats_net_list__address_range_to_long)
                                  ELSE
                                    \'LC__CMDB__CATG__IP__INVALID_IP_RANGE\'
                                  END)
                              )
                            FROM isys_cats_net_list', 'isys_cats_net_list', 'isys_cats_net_list__id', 'isys_cats_net_list__isys_obj__id'),
                    C__PROPERTY__DATA__SORT   => 'SELECT COUNT(isys_cats_net_ip_addresses_list__id)
                                FROM isys_cats_net_ip_addresses_list LEFT JOIN isys_catg_ip_list
                                ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
                                WHERE isys_cats_net_ip_addresses_list__isys_obj__id = obj_main.isys_obj__id
                                  AND isys_catg_ip_list__id IS NOT NULL
                                  AND isys_catg_ip_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL)
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                ]
            ]),
            'description'         => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_net_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__NET', 'C__CATS__NET')
                ]
            ])
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                $p_category_data['data_id'] = $this->create_connector('isys_cats_net_list', $p_object_id);
            }

            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Translate dns domains to IDs
                if (isset($p_category_data['properties']['dns_domain'][C__DATA__VALUE])) {
                    if (is_string($p_category_data['properties']['dns_domain'][C__DATA__VALUE])) {
                        $p_category_data['properties']['dns_domain'][C__DATA__VALUE] = explode(',', $p_category_data['properties']['dns_domain'][C__DATA__VALUE]);
                    }

                    $l_dialog_admin = new isys_cmdb_dao_dialog_admin($this->get_database_component());
                    foreach ($p_category_data['properties']['dns_domain'][C__DATA__VALUE] as $l_index => $l_dns_domain) {
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

                // Translate dns server to IDs
                if (isset($p_category_data['properties']['dns_server'][C__DATA__VALUE]) && is_array($p_category_data['properties']['dns_server'][C__DATA__VALUE])) {
                    if (is_array(current($p_category_data['properties']['dns_server'][C__DATA__VALUE]))) {
                        $l_dns_server_arr = $p_category_data['properties']['dns_server'][C__DATA__VALUE];
                        $p_category_data['properties']['dns_server'][C__DATA__VALUE] = [];
                        foreach ($l_dns_server_arr as $l_dns_server_content) {
                            if (isset($l_dns_server_content['ref_id'])) {
                                $p_category_data['properties']['dns_server'][C__DATA__VALUE][] = $l_dns_server_content['ref_id'];
                            }
                        }
                    }
                }

                // In case m_object_id is not set
                if ($this->m_object_id !== $p_object_id) {
                    $this->set_object_id($p_object_id);
                }

                if (!isset($p_category_data['properties']['type'][C__DATA__VALUE])) {
                    if (Ip::validate_ip($p_category_data['properties']['address'][C__DATA__VALUE])) {
                        $p_category_data['properties']['type'][C__DATA__VALUE] = defined_or_default('C__CATS_NET_TYPE__IPV4');
                    } else {
                        $p_category_data['properties']['type'][C__DATA__VALUE] = defined_or_default('C__CATS_NET_TYPE__IPV6');
                    }
                }

                // Calculate netmask or cidr suffix if they are not set
                if (!isset($p_category_data['properties']['netmask'][C__DATA__VALUE]) && isset($p_category_data['properties']['cidr_suffix'][C__DATA__VALUE])) {
                    if ($p_category_data['properties']['type'][C__DATA__VALUE] == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
                        $p_category_data['properties']['netmask'][C__DATA__VALUE] = Ip::calc_subnet_by_cidr_suffix_ipv6($p_category_data['properties']['cidr_suffix'][C__DATA__VALUE]);
                    } else {
                        $p_category_data['properties']['netmask'][C__DATA__VALUE] = Ip::calc_subnet_by_cidr_suffix($p_category_data['properties']['cidr_suffix'][C__DATA__VALUE]);
                    }
                } elseif (isset($p_category_data['properties']['netmask'][C__DATA__VALUE]) && !isset($p_category_data['properties']['cidr_suffix'][C__DATA__VALUE])) {
                    if ($p_category_data['properties']['type'][C__DATA__VALUE] == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
                        $p_category_data['properties']['cidr_suffix'][C__DATA__VALUE] = Ip::calc_cidr_suffix_ipv6($p_category_data['properties']['netmask'][C__DATA__VALUE]);
                    } else {
                        $p_category_data['properties']['cidr_suffix'][C__DATA__VALUE] = Ip::calc_cidr_suffix($p_category_data['properties']['netmask'][C__DATA__VALUE]);
                    }
                }

                // Save category data:
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    C__RECORD_STATUS__NORMAL,
                    $p_category_data['properties']['title'][C__DATA__VALUE],
                    $p_category_data['properties']['type'][C__DATA__VALUE],
                    $p_category_data['properties']['address'][C__DATA__VALUE],
                    $p_category_data['properties']['netmask'][C__DATA__VALUE],
                    $p_category_data['properties']['gateway'][C__DATA__VALUE],
                    $p_category_data['properties']['range_from'][C__DATA__VALUE],
                    $p_category_data['properties']['range_to'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    $p_category_data['properties']['cidr_suffix'][C__DATA__VALUE],
                    $p_category_data['properties']['dns_server'][C__DATA__VALUE],
                    $p_category_data['properties']['dns_domain'][C__DATA__VALUE],
                    $p_category_data['properties']['reverse_dns'][C__DATA__VALUE],
                    $p_category_data['properties']['layer2_assignments'][C__DATA__VALUE]
                );
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Validates property data.
     *
     * @param   array $p_data Associative array of property tags as keys and their values as values.
     * @param   mixed $p_prepend_table_field
     *
     * @return  mixed  Returns true on a successful validation, otherwise an associative array with property tags as keys and error messages as values.
     * @author  Benjamin Heisig <bheisig@synetics.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function validate(array $p_data = [], $p_prepend_table_field = false)
    {
        $l_return = [];

        if ($p_data['type'] == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
            if (filter_var($p_data['address'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
                $l_return['address'] = isys_application::instance()->container->get('language')->get('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
            }

            if (filter_var($p_data['netmask'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
                $l_return['netmask'] = isys_application::instance()->container->get('language')->get('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
            }
        } elseif ($p_data['type'] == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
            // @todo  ID-6705 Set `address` to the value of `address_v6` to work with category saving as well as duplicating.
            if (isset($p_data['address_v6'])) {
                $p_data['address'] = $p_data['address_v6'];
            }

            if (filter_var($p_data['address'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
                $l_return['address'] = isys_application::instance()->container->get('language')->get('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
            }
        }

        if (count($l_return)) {
            return $l_return;
        }

        return parent::validate($p_data);
    }

    /**
     * Merges posted ipv6 and ipv4 data into one address so that the addresses are stored in only one database field.
     *
     * @param   integer $p_net_type
     *
     * @return  array
     */
    private function merge_posted_ip_data($p_net_type)
    {
        $l_subnet = $l_gw = $l_from = $l_to = $l_net = '';

        if ($p_net_type == 1001 || $p_net_type == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
            $l_subnet = $_POST['C__CATS__NET__MASK_V4'];
            $l_from = $_POST['C__CATS__NET__ADDRESS_RANGE_FROM_V4'];
            $l_to = $_POST['C__CATS__NET__ADDRESS_RANGE_TO_V4'];
            $l_net = $_POST['C__CATS__NET__NET_V4'];
        } elseif ($p_net_type == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
            $l_gw = $_POST['C__CATS__NET__DEF_GW_V6'];
            $l_subnet = $_POST['C__CATS__NET__MASK_V6'];
            $l_from = $_POST['C__CATS__NET__ADDRESS_RANGE_FROM'];
            $l_to = $_POST['C__CATS__NET__ADDRESS_RANGE_TO'];
            $l_net = $_POST['C__CATS__NET__NET_V6'];
        }

        return [
            C__IP__SUBNET  => $l_subnet,
            C__IP__GATEWAY => $l_gw,
            C__IP__NET     => $l_net,
            'ADDRESS_FROM' => $l_from,
            'ADDRESS_TO'   => $l_to
        ];
    }

    /**
     * Helper method which checks if cidr suffix or the ip range is set and updates them if they are not set.
     *
     * @param $p_typeID
     * @param $p_address
     * @param $p_netmask
     * @param $p_cidr_suffix
     * @param $p_from
     * @param $p_to
     *
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function check_missing_net_params($p_typeID, $p_address, $p_netmask, &$p_cidr_suffix, &$p_from, &$p_to)
    {
        if ($p_typeID == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
            if (empty($p_cidr_suffix) && $p_netmask != '') {
                $p_cidr_suffix = Ip::calc_cidr_suffix($p_netmask);
            }

            if (empty($p_from) || empty($p_to)) {
                $l_range = Ip::calc_ip_range($p_address, $p_netmask);
                $p_from = $l_range['from'];
                $p_to = $l_range['to'];
            }
        } elseif ($p_typeID == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
            if (empty($p_cidr_suffix) && $p_netmask != '') {
                $p_cidr_suffix = Ip::calc_cidr_suffix_ipv6($p_netmask);
            }

            if (empty($p_from) || empty($p_to)) {
                $l_range = Ip::calc_ip_range_ipv6($p_address, $p_cidr_suffix);
                $p_from = $l_range['from'];
                $p_to = $l_range['to'];
            }
        }
    }
}
