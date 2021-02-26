<?php

use idoit\Component\Helper\Ip;

/**
 * i-doit
 *
 * CMDB host addresses category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_ip extends isys_cmdb_ui_category_global
{
    // indicator if primary ip should be shown (only for overview category)
    private $m_show_primary_ip = false;

    /**
     * Sets indicator for the primary host address
     */
    public function show_primary_ip()
    {
        $this->m_show_primary_ip = true;
    }

    /**
     * Process method.
     *
     * @param isys_cmdb_dao_category|isys_cmdb_dao_category_g_ip $p_cat
     *
     * @return array
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_cmdb
     * @throws isys_exception_database
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_new = false;

        $this->m_object_id = $this->m_object_id ?: $_GET[C__CMDB__GET__OBJECT];

        if ($this->m_show_primary_ip && $this->m_object_id !== null) {
            $l_catdata = $p_cat->get_data(null, $this->m_object_id, ' AND isys_catg_ip_list__primary = 1 ')
                ->get_row();
        } else {
            $l_catdata = $p_cat->get_general_data();
        }

        $l_primary_exists = 0;
        $l_primary = [];

        if ($this->m_object_id) {
            $l_primary_ip = $p_cat->get_primary_ip_by_object_id($this->m_object_id);
            $l_primary_exists = $l_primary_ip->num_rows();
            $l_primary = $l_primary_ip->get_row();
        }

        $l_dao_net = new isys_cmdb_dao_category_s_net($p_cat->get_database_component());
        $l_properties = $p_cat->get_properties();

        if ($l_catdata === null) {
            $l_new = true;
        }

        $l_global_net = true;

        $l_dhcp_reserved_ranges = $l_dhcp_dynamic_ranges = $l_rules = [];
        $l_posts = isys_module_request::get_instance()
            ->get_posts();

        // Type (v4, v6,...).
        $l_type = null;

        if (is_array($l_catdata) && is_numeric($l_catdata['isys_catg_ip_list__isys_net_type__id'])) {
            $l_rules['C__NET__TYPE']['p_bDisabled'] = 1;
            $l_type = $l_catdata['isys_catg_ip_list__isys_net_type__id'];
        }

        // If IP has been assigned get range.
        if (!empty($l_catdata['isys_cats_net_ip_addresses_list__isys_obj__id'])) {
            $l_global_net = false;
            $l_net_row = $l_dao_net->get_all_net_information_by_obj_id($l_catdata['isys_cats_net_ip_addresses_list__isys_obj__id']);

            $l_rules["C__CATP__IP__ADDRESS_V4_FROM"]["p_strValue"] = $l_net_row["isys_cats_net_list__address_range_from"];
            $l_rules["C__CATP__IP__ADDRESS_V4_TO"]["p_strValue"] = $l_net_row["isys_cats_net_list__address_range_to"];

            if ($l_catdata['isys_catg_ip_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
                $l_net_range = [
                    'from' => $l_net_row["isys_cats_net_list__address_range_from_long"],
                    'to'   => $l_net_row["isys_cats_net_list__address_range_to_long"]
                ];
            } else {
                $l_net_range = [
                    'from' => $l_net_row["isys_cats_net_list__address_range_from"],
                    'to'   => $l_net_row["isys_cats_net_list__address_range_to"]
                ];
            }

            if (is_array($l_net_row['dhcp_ranges']['C__NET__DHCP_DYNAMIC'])) {
                foreach ($l_net_row['dhcp_ranges']['C__NET__DHCP_DYNAMIC'] as $l_range) {
                    $l_dhcp_dynamic_ranges[] = [
                        'from' => $l_range['from'],
                        'to'   => $l_range['to']
                    ];
                }
            }

            if (is_array($l_net_row['dhcp_ranges']['C__NET__DHCP_RESERVED'])) {
                foreach ($l_net_row['dhcp_ranges']['C__NET__DHCP_RESERVED'] as $l_range) {
                    $l_dhcp_reserved_ranges[] = [
                        'from' => $l_range['from'],
                        'to'   => $l_range['to']
                    ];
                }
            }

            if (empty($l_net_row['used_ips']) || !is_array($l_net_row['used_ips'])) {
                $l_net_row['used_ips'] = [];
            }

            $l_used_ips = array_unique($l_net_row['used_ips']);
            sort($l_used_ips);

            // refs #4904
            $l_rules["C__CATP__IP__PRIMARY"]["p_strSelectedID"] = ($l_primary["isys_catg_ip_list__id"] != $l_catdata["isys_catg_ip_list__id"]) ? 0 : 1;
            $l_rules["C__CATP__IP__ACTIVE"]["p_strSelectedID"] = $l_catdata["isys_catg_ip_list__active"];

            $this->get_template_component()
                ->assign('net_range', isys_format_json::encode($l_net_range))
                ->assign('dhcp_dynamic_ranges', isys_format_json::encode($l_dhcp_dynamic_ranges))
                ->assign('dhcp_reserved_ranges', isys_format_json::encode($l_dhcp_reserved_ranges))
                ->assign('used_ips', isys_format_json::encode($l_used_ips));
        } elseif (!is_array($l_catdata)) {
            $l_net_row = $l_dao_net->get_all_net_information_by_obj_id(defined_or_default('C__OBJ__NET_GLOBAL_IPV4'));

            $l_rules["C__CATP__IP__ADDRESS_V4_FROM"]["p_strValue"] = $l_net_row["isys_cats_net_list__address_range_from"];
            $l_rules["C__CATP__IP__ADDRESS_V4_TO"]["p_strValue"] = $l_net_row["isys_cats_net_list__address_range_to"];

            $l_net_range = [
                'from' => $l_net_row["isys_cats_net_list__address_range_from_long"],
                'to'   => $l_net_row["isys_cats_net_list__address_range_to_long"]
            ];

            if (is_array($l_net_row['dhcp_ranges']['C__NET__DHCP_DYNAMIC'])) {
                foreach ($l_net_row['dhcp_ranges']['C__NET__DHCP_DYNAMIC'] as $l_range) {
                    $l_dhcp_dynamic_ranges[] = [
                        'from' => $l_range['from'],
                        'to'   => $l_range['to']
                    ];
                }
            }

            if (is_array($l_net_row['dhcp_ranges']['C__NET__DHCP_RESERVED'])) {
                foreach ($l_net_row['dhcp_ranges']['C__NET__DHCP_RESERVED'] as $l_range) {
                    $l_dhcp_reserved_ranges[] = [
                        'from' => $l_range['from'],
                        'to'   => $l_range['to']
                    ];
                }
            }

            if (empty($l_net_row['used_ips']) || !is_array($l_net_row['used_ips'])) {
                $l_net_row['used_ips'] = [];
            }

            $l_used_ips = array_unique($l_net_row['used_ips']);
            sort($l_used_ips);

            // refs #4904
            $l_rules["C__CATP__IP__PRIMARY"]["p_strSelectedID"] = ($l_primary_exists) ? 0 : $l_properties['primary'][C__PROPERTY__UI][C__PROPERTY__UI__DEFAULT];
            $l_rules["C__CATP__IP__ACTIVE"]["p_strSelectedID"] = $l_properties['active'][C__PROPERTY__UI][C__PROPERTY__UI__DEFAULT];

            $this->get_template_component()
                ->assign('net_range', isys_format_json::encode($l_net_range))
                ->assign('dhcp_dynamic_ranges', isys_format_json::encode($l_dhcp_dynamic_ranges))
                ->assign('dhcp_reserved_ranges', isys_format_json::encode($l_dhcp_reserved_ranges))
                ->assign('used_ips', isys_format_json::encode($l_used_ips));
        }

        // 1/0 values
        $l_rules["C__CATG__IP__GW__CHECK"]["p_arData"] = get_smarty_arr_YES_NO();
        $l_rules["C__CATG__IP__GW__CHECK"]["p_strSelectedID"] = (!empty($l_net_row['isys_cats_net_list__isys_catg_ip_list__id']) && is_array($l_catdata) &&
            $l_net_row['isys_cats_net_list__isys_catg_ip_list__id'] == $l_catdata['isys_catg_ip_list__id']) ? 1 : 0;

        $l_rules["C__CATP__IP__ACTIVE"]["p_arData"] = get_smarty_arr_YES_NO();
        $l_rules["C__CATP__IP__PRIMARY"]["p_arData"] = get_smarty_arr_YES_NO();

        $l_rules["C__CATP__IP__ASSIGN"]["p_strTable"] = "isys_ip_assignment";
        $l_rules["C__CATP__IP__ASSIGN"]["p_strSelectedID"] = $l_catdata["isys_catg_ip_list__isys_ip_assignment__id"];

        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_ip_list__description"];
        $l_rules["C__NET__TYPE"]["p_strSelectedID"] = (isset($l_catdata["isys_catg_ip_list__isys_net_type__id"])) ? $l_catdata["isys_catg_ip_list__isys_net_type__id"] : defined_or_default('C__CATS_NET_TYPE__IPV4', 1);

        if ($l_global_net) {
            $l_rules["C__CATG__IP__NET"]["p_strSelectedID"] = defined_or_default('C__OBJ__NET_GLOBAL_IPV4');
        } else {
            $l_rules["C__CATG__IP__NET"]["p_strSelectedID"] = $l_catdata["isys_cats_net_ip_addresses_list__isys_obj__id"];
        }

        $l_rules["C__CATS__NET__CIDR"]["p_strValue"] = $l_net_row["isys_cats_net_list__cidr_suffix"];

        // Assign IP Addresses (V4, V6, ...).
        $l_rules["C__CATP__IP__ADDRESS_V4"]["p_strValue"] = $l_catdata["isys_cats_net_ip_addresses_list__title"];
        $l_rules["C__CATP__IP__SUBNETMASK_V4"]["p_strValue"] = $l_net_row["isys_cats_net_list__mask"];

        $l_rules["C__CATP__IP__DEFAULTGATEWAY_V4"]["p_strValue"] = $l_catdata["isys_catg_ip_list__gateway"];

        if (defined('C__CATS_NET_TYPE__IPV6') && ($l_type == C__CATS_NET_TYPE__IPV6 || $l_type === null)) {
            $l_rules['C__CMDB__CATG__IP__IPV6_ASSIGNMENT']['p_strSelectedID'] = (isset($l_catdata['isys_catg_ip_list__isys_ipv6_assignment__id'])) ? $l_catdata['isys_catg_ip_list__isys_ipv6_assignment__id'] : defined_or_default('C__CMDB__CATG__IP__STATIC', 1);
            $l_rules['C__CMDB__CATG__IP__IPV6_ASSIGNMENT']['p_strTable'] = 'isys_ipv6_assignment';
            $l_rules['C__CMDB__CATG__IP__IPV6_SCOPE']['p_strSelectedID'] = $l_catdata['isys_catg_ip_list__isys_ipv6_scope__id'];
            $l_rules['C__CMDB__CATG__IP__IPV6_SCOPE']['p_strTable'] = 'isys_ipv6_scope';
            $l_rules['C__CMDB__CATG__IP__IPV6_ADDRESS']['p_strValue'] = Ip::validate_ipv6($l_catdata['isys_cats_net_ip_addresses_list__title'], true);
        }

        if ($l_new) {
            $l_rules['C__CMDB__CATG__IP__IPV6_SCOPE']['p_strSelectedID'] = defined_or_default('C__CMDB__CATG__IP__GLOBAL_UNICAST');
        }

        $l_rules["C__CATP__IP__DNS_SERVER"]["p_strSelectedID"] = $l_catdata["isys_catg_ip_list__isys_net_dns_server__id"];

        // Here we prepare the "assigned ports".
        $l_port_array = [];
        $l_selected = null;

        if (isys_application::instance()->template->editmode()) {
            if ($l_catdata['isys_catg_ip_list__isys_catg_port_list__id'] > 0) {
                $l_selected = $l_catdata['isys_catg_ip_list__isys_catg_port_list__id'] . '_C__CATG__NETWORK_PORT';
            } elseif ($l_catdata['isys_catg_ip_list__isys_catg_log_port_list__id'] > 0) {
                $l_selected = $l_catdata['isys_catg_ip_list__isys_catg_log_port_list__id'] . '_C__CATG__NETWORK_LOG_PORT';
            }

            // @see  ID-7620  Use natural sorting - happenes inside the callbaack.
            $l_port_array = $p_cat->callback_property_ports(isys_request::factory()
                ->set_object_id($this->m_object_id));
        } else {
            /**
             * @note DS: Just retrieve the selected port in view mode instead of creating the complete $l_port_array
             */
            $l_sql = 'SELECT isys_catg_port_list__id AS id, isys_catg_port_list__title AS title FROM isys_catg_port_list
                WHERE isys_catg_port_list__status = \'' . C__RECORD_STATUS__NORMAL . '\' AND
                isys_catg_port_list__id = ' . $l_dao_net->convert_sql_id($l_catdata['isys_catg_ip_list__isys_catg_port_list__id']) . ' AND
                isys_catg_port_list__isys_obj__id = ' . $l_dao_net->convert_sql_id($this->m_object_id) .

                ' UNION SELECT isys_catg_log_port_list__id AS id, isys_catg_log_port_list__title AS title FROM isys_catg_log_port_list
                WHERE isys_catg_log_port_list__status = \'' . C__RECORD_STATUS__NORMAL . '\' AND
                isys_catg_log_port_list__id = ' . $l_dao_net->convert_sql_id($l_catdata['isys_catg_ip_list__isys_catg_log_port_list__id']) . ' AND
                isys_catg_log_port_list__isys_obj__id = ' . $l_dao_net->convert_sql_id($this->m_object_id);

            $l_res_port = $l_dao_net->retrieve($l_sql);
            while ($l_row = $l_res_port->get_row()) {
                $l_port_array[$l_row['id'] . '_C__CATG__NETWORK_PORT'] = $l_row['title'];
                $l_selected = $l_row['id'] . '_C__CATG__NETWORK_PORT';
            }

            // @see  ID-7620  Use natural sorting.
            uasort($l_port_array, 'strnatcasecmp');
        }

        $l_rules["C__CATG__IP__ASSIGNED_PORTS"]["p_arData"] = $l_port_array;
        $l_rules["C__CATG__IP__ASSIGNED_PORTS"]["p_strSelectedID"] = $l_selected;

        $l_object_ipv4 = $p_cat->get_object_by_id(defined_or_default('C__OBJ__NET_GLOBAL_IPV4'))
            ->get_row();
        $l_object_ipv6 = $p_cat->get_object_by_id(defined_or_default('C__OBJ__NET_GLOBAL_IPV6'))
            ->get_row();

        // Assign net for graying some fields and net type for displaying the correct fields.
        $this->get_template_component()
            ->assign('ajax_url_ip_list', '?call=ip_addresses&method=show_ip_list&ajax=1')
            ->assign('ip_id', $l_catdata["isys_catg_ip_list__id"])
            ->assign("net", $l_catdata["isys_cats_net_ip_addresses_list__isys_obj__id"])
            ->assign("type", $l_catdata["isys_catg_ip_list__isys_net_type__id"])
            ->assign("global_net_ipv4_title", isys_application::instance()->container->get('language')
                    ->get($l_object_ipv4['isys_obj_type__title']) . " >> " . $l_object_ipv4['isys_obj__title'])
            ->assign("global_net_ipv6_title", isys_application::instance()->container->get('language')
                    ->get($l_object_ipv6['isys_obj_type__title']) . " >> " . $l_object_ipv6['isys_obj__title'])
            ->assign("ip_unique_check", isys_tenantsettings::get('cmdb.unique.ip-address'));

        // DNS DOMAIN.
        if ($_GET['get_dns']) {
            $l_res_dns_domain = $l_dao_net->get_dns_domains();

            $l_cat_list = [];
            while ($l_row_dns_domain = $l_res_dns_domain->get_row()) {
                $l_cat_list[] = [
                    "caption" => $l_row_dns_domain['isys_net_dns_domain__title'],
                    "value"   => $l_row_dns_domain['isys_net_dns_domain__id']
                ];
            }

            echo isys_format_json::encode($l_cat_list);
            die();
        }

        $l_assigned_dns_domain = $p_cat->get_assigned_dns_domain(null, $l_catdata['isys_catg_ip_list__id']);

        $l_dns_domains = [];

        if ($l_assigned_dns_domain) {
            while ($l_row_dns_domain = $l_assigned_dns_domain->get_row()) {
                $l_dns_domains[] = (int)$l_row_dns_domain['isys_net_dns_domain__id'];
            }
        }

        if (!isset($l_rules["C__CATP__IP__SEARCH_DOMAIN"])) {
            $l_rules['C__CATP__IP__SEARCH_DOMAIN'] = [
                'p_strTable'      => 'isys_net_dns_domain',
                'placeholder'     => isys_application::instance()->container->get('language')
                    ->get('LC__CATP__IP__DNSDOMAIN'),
                'emptyMessage'    => isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__CATS__NET__NO_DNS_DOMAINS_FOUND'),
                'p_onComplete'    => "idoit.callbackManager.triggerCallback('cmdb-catg-ip-dns_domain-update', selected);",
                'p_strSelectedID' => implode(',', $l_dns_domains),
                'multiselect'     => true
            ];
        }

        // DNS Server.
        $l_dns_server = $p_cat->get_assigned_dns_server($l_catdata["isys_catg_ip_list__id"]);

        // Assign the constants to the object-browser.
        $l_rules['C__CATG__IP__ASSIGNED_DNS_SERVER']['p_strSelectedID'] = isys_format_json::encode($l_dns_server);

        // Validate posted form
        if (!$p_cat->get_validation()) {
            foreach ($l_posts as $l_key => $l_value) {
                $l_rules[$l_key]["p_strValue"] = $l_value;
            }

            $l_ip_data = $p_cat->merge_posted_ip_data($_POST["C__NET__TYPE"]);

            $l_rules["C__CATP__IP__ADDRESS_V4"]["p_strValue"] = $l_ip_data[C__IP__ADDRESS];
            $l_rules["C__CATP__IP__SUBNETMASK_V4"]["p_strValue"] = $l_ip_data[C__IP__SUBNET];

            $l_rules["C__CATP__IP__ADDRESS_V6"]["p_strValue"] = $l_ip_data[C__IP__ADDRESS];
            $l_rules["C__CATP__IP__SUBNETMASK_V6"]["p_strValue"] = $l_ip_data[C__IP__SUBNET];

            $l_rules["C__CATP__IP__ADDRESS_V4_FROM"]["p_strValue"] = implode('.', $l_posts['C__CATP__IP__ADDRESS_V4_FROM']);
            $l_rules["C__CATP__IP__ADDRESS_V4_TO"]["p_strValue"] = implode('.', $l_posts['C__CATP__IP__ADDRESS_V4_TO']);

            $l_rules["C__CATG__IP__NET"]["p_strValue"] = $l_posts["C__CATG__IP__NET__HIDDEN"];

            $l_rules["C__CATP__IP__ASSIGN"]["p_strSelectedID"] = $l_posts["C__CATP__IP__ASSIGN"];
            $l_rules["C__CATP__IP__PRIMARY"]["p_strSelectedID"] = $l_posts["C__CATP__IP__PRIMARY"];
            $l_rules["C__CATP__IP__ACTIVE"]["p_strSelectedID"] = $l_posts["C__CATP__IP__ACTIVE"];

            $l_assigned_dns_server = isys_format_json::decode($l_posts["C__CATG__IP__ASSIGNED_DNS_SERVER__HIDDEN"], true);
            $l_rules["C__CATG__IP__ASSIGNED_DNS_SERVER"]["p_strValue"] = implode(',', $l_assigned_dns_server);
            $l_rules["C__NET__TYPE"]["p_strSelectedID"] = $l_posts["C__NET__TYPE"];
        }

        $l_zone_option_dao = isys_cmdb_dao_category_g_net_zone_options::instance(isys_application::instance()->database);
        $l_zone_options = [0 => ['color' => 'transparent']];
        $l_zone_data[0] = isys_tenantsettings::get('gui.empty_value', '-');

        if ($this->get_template_component()
            ->editmode()) {
            $l_zone_res = isys_cmdb_dao_category_s_net_zone::instance(isys_application::instance()->database)
                ->get_data(null, $l_rules["C__CATG__IP__NET"]["p_strSelectedID"], '', null, C__RECORD_STATUS__NORMAL);

            while ($l_zone_row = $l_zone_res->get_row()) {
                $l_zone_option = $l_zone_option_dao->get_data(null, $l_zone_row['isys_obj__id'])
                    ->get_row();

                $l_zone_data[$l_zone_row['isys_obj__id']] = $l_zone_row['isys_obj__title'] . ' (' . $l_zone_row['isys_cats_net_zone_list__range_from'] . ' - ' .
                    $l_zone_row['isys_cats_net_zone_list__range_to'] . ')';

                $l_zone_options[$l_zone_row['isys_obj__id']] = [
                    'color'  => $l_zone_option['isys_catg_net_zone_options_list__color'] ?: '#ffffff',
                    'domain' => $l_zone_option['isys_catg_net_zone_options_list__domain'] ?: ''
                ];
            }
        } elseif ($l_catdata['isys_catg_ip_list__isys_obj__id__zone'] > 0) {
            $l_zone_row = $l_zone_option_dao->get_data(null, $l_catdata['isys_catg_ip_list__isys_obj__id__zone'])
                ->get_row();

            $l_zone_data[$l_catdata['isys_catg_ip_list__isys_obj__id__zone']] = $l_zone_row['isys_obj__title'] ?: $p_cat->obj_get_title_by_id_as_string($l_catdata['isys_catg_ip_list__isys_obj__id__zone']);

            $l_zone_options[$l_catdata['isys_catg_ip_list__isys_obj__id__zone']] = [
                'color'  => $l_zone_row['isys_catg_net_zone_options_list__color'] ?: '#ffffff',
                'domain' => $l_zone_row['isys_catg_net_zone_options_list__domain'] ?: ''
            ];
        }

        // Assign primary hostname and domain.
        $l_rules["C__CATP__IP__HOSTNAME"]["p_strValue"] = trim($l_catdata["isys_catg_ip_list__hostname"]);
        $l_rules['C__CATG__IP__DOMAIN']['p_strValue'] = trim($l_catdata['isys_catg_ip_list__domain']);

        $l_hostname_pairs = [];

        $l_res = $p_cat->get_hostname_pairs($l_catdata['isys_catg_ip_list__id']);

        while ($l_row = $l_res->get_row()) {
            $l_hostname_pairs[] = [
                'host'   => $l_row['isys_hostaddress_pairs__hostname'],
                'domain' => $l_row['isys_hostaddress_pairs__domain']
            ];
        }

        $l_rules['C__CATP__IP__HOSTNAME_ADDITIONAL[]'] = $l_rules['C__CATP__IP__DOMAIN_ADDITIONAL[]'] = [
            'disableInputGroup' => true,
            'p_bInfoIconSpacer' => 0,
            'p_strPlaceholder'  => 'LC__CATG__IP__DOMAIN'
        ];

        // Assigning the IP zone.
        $l_rules['C__CATG__IP__ZONE']['p_arData'] = $l_zone_data;
        $l_rules['C__CATG__IP__ZONE']['p_strSelectedID'] = $l_catdata['isys_catg_ip_list__isys_obj__id__zone'] ?: 0;

        // Apply rules.
        $this->get_template_component()
            ->assign('hostname', $l_rules["C__CATP__IP__HOSTNAME"]["p_strValue"])
            ->assign('domain', $l_rules['C__CATG__IP__DOMAIN']['p_strValue'])
            ->assign('zone_options', $l_zone_options)
            ->assign('current_zone_color', $l_zone_options[(int)$l_catdata['isys_catg_ip_list__isys_obj__id__zone']]['color'])
            ->assign('hostname_pairs', $l_hostname_pairs)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        return $l_rules;
    }
}
