<?php

use idoit\Component\Helper\Ip;

/**
 * i-doit
 *
 * CMDB Active Directory: Specific category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.de>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_net extends isys_cmdb_ui_category_specific
{
    /**
     * Show the detail-template for specific category net.
     *
     * @param   isys_cmdb_dao_category_s_net $p_cat
     *
     * @global  array                        $g_dirs
     * @return  array|void
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata = $p_cat->get_general_data();

        // Once we saved this category, it shall not be possible to change the net type.
        if (empty($l_catdata['isys_cats_net_list__address'])) {
            $l_rules['C__CATS__NET__TYPE']['p_bDisabled'] = false;
        }

        // If no net type was chosen, we assign one by ourself.
        if ($l_catdata['isys_cats_net_list__isys_net_type__id'] === null) {
            // This prevents the page from displaying an empty template (#3400).
            $l_catdata['isys_cats_net_list__isys_net_type__id'] = defined_or_default('C__CATS_NET_TYPE__IPV4');
        }

        // Make rules.
        $l_rules['C__CMDB__CAT__COMMENTARY_' . $p_cat->get_category_type() . $p_cat->get_category_id()]['p_strValue'] = $l_catdata['isys_cats_net_list__description'];
        $l_rules['C__CATS__NET__TITLE']['p_strValue'] = $l_catdata['isys_cats_net_list__title'];
        $l_rules['C__CATS__NET__TYPE']['p_strSelectedID'] = $l_catdata['isys_cats_net_list__isys_net_type__id'];

        // IP Address assignments V4.
        if ($l_catdata['isys_cats_net_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
            $l_rules['C__CATS__NET__NET_V4']['p_strValue'] = $l_catdata['isys_cats_net_list__address'];
            $l_rules['C__CATS__NET__MASK_V4']['p_strValue'] = $l_catdata['isys_cats_net_list__mask'];
            $l_rules['C__CATS__NET__DEF_GW_V4']['p_arData'] = $p_cat->callback_property_gateway(isys_request::factory()->set_row($l_catdata));
            $l_rules['C__CATS__NET__DEF_GW_V4']['p_strSelectedID'] = $l_catdata['isys_cats_net_list__isys_catg_ip_list__id'];
            $l_rules['C__CATS__NET__ADDRESS_RANGE_FROM_V4']['p_strValue'] = $l_catdata['isys_cats_net_list__address_range_from'];
            $l_rules['C__CATS__NET__ADDRESS_RANGE_TO_V4']['p_strValue'] = $l_catdata['isys_cats_net_list__address_range_to'];
            $l_rules['C__CATS__NET__CIDR']['p_strValue'] = isset($l_catdata['isys_cats_net_list__cidr_suffix']) ? $l_catdata['isys_cats_net_list__cidr_suffix'] : 32;
            $l_rules['C__CATS__NET__CIDR']['p_bReadonly'] = ($_GET[C__CMDB__GET__OBJECT] == defined_or_default('C__OBJ__NET_GLOBAL_IPV4'));
            $l_rules['C__CATS__NET__MASK_V4']['p_bReadonly'] = ($_GET[C__CMDB__GET__OBJECT] == defined_or_default('C__OBJ__NET_GLOBAL_IPV4'));
        }

        // IP Address assignments V6.
        if ($l_catdata['isys_cats_net_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
            $l_rules['C__CATS__NET__NET_V6']['p_strValue'] = Ip::validate_ipv6($l_catdata['isys_cats_net_list__address'], true);
            $l_rules['C__CATS__NET__NET_V6_CIDR']['p_strValue'] = $l_catdata['isys_cats_net_list__cidr_suffix'];
            $l_rules['C__CATS__NET__NET_V6_CIDR']['p_bReadonly'] = ($_GET[C__CMDB__GET__OBJECT] == defined_or_default('C__OBJ__NET_GLOBAL_IPV6'));
            $l_rules['C__CATS__NET__ADDRESS_RANGE_FROM']['p_strValue'] = Ip::validate_ipv6($l_catdata['isys_cats_net_list__address_range_from'], true);
            $l_rules['C__CATS__NET__ADDRESS_RANGE_TO']['p_strValue'] = Ip::validate_ipv6($l_catdata['isys_cats_net_list__address_range_to'], true);
            $l_rules['C__CATS__NET__MASK_V6']['p_bReadonly'] = ($_GET[C__CMDB__GET__OBJECT] == defined_or_default('C__OBJ__NET_GLOBAL_IPV6'));
            // @see  ID-6744  We apply this data to make the "default gateway" logic work for IPv6 nets.
            $l_rules['C__CATS__NET__DEF_GW_V4']['p_arData'] = $p_cat->callback_property_gateway(isys_request::factory()->set_row($l_catdata));
            $l_rules['C__CATS__NET__DEF_GW_V4']['p_strSelectedID'] = $l_catdata['isys_cats_net_list__isys_catg_ip_list__id'];
        }

        $this->get_template_component()
            ->assign('net_type', $l_catdata['isys_cats_net_list__isys_net_type__id'])
            ->assign('net_id', $l_catdata['isys_cats_net_list__isys_obj__id']);

        $l_rules['C__CATS__NET__ASSIGNED_DNS_SERVER']['p_strSelectedID'] = $l_catdata['isys_cats_net_list__isys_net_dns_server__id'];

        $l_dns_domains = [];
        if (isset($_GET[C__CMDB__GET__OBJECT])) {
            $l_assigned_dns_domain = $p_cat->get_assigned_dns_domain($_GET[C__CMDB__GET__OBJECT]);

            while ($l_row_dns_domain = $l_assigned_dns_domain->get_row()) {
                $l_dns_domains[] = (int)$l_row_dns_domain['isys_net_dns_domain__id'];
            }
        }

        $l_rules['C__CATS__NET__DNS_DOMAIN'] = [
            'p_strTable'      => 'isys_net_dns_domain',
            'placeholder'     => isys_application::instance()->container->get('language')->get('LC__CMDB__CATS__NET__DNS_DOMAIN'),
            'emptyMessage'    => isys_application::instance()->container->get('language')->get('LC__CMDB__CATS__NET__NO_DNS_DOMAINS_FOUND'),
            'p_onComplete'    => "idoit.callbackManager.triggerCallback('cmdb-cats-net-dns_domain-update', selected);",
            'p_strSelectedID' => implode(',', $l_dns_domains),
            'multiselect'     => true
        ];

        // Assign the constants to the object-browser.
        $l_dns_server = $p_cat->get_assigned_dns_server($l_catdata['isys_cats_net_list__id']);

        $l_rules['C__CATS__NET__ASSIGNED_DNS_SERVER']['catFilter'] = 'C__CATG__IP';
        $l_rules['C__CATS__NET__ASSIGNED_DNS_SERVER']['p_strSelectedID'] = isys_format_json::encode($l_dns_server);

        $l_rules['C__CATS__NET__REVERSE_DNS']["p_strValue"] = $l_catdata['isys_cats_net_list__reverse_dns'];

        $l_layer2_assignments = $p_cat->get_assigned_layer_2_ids($_GET[C__CMDB__GET__OBJECT], true);
        $l_rules['C__CATS__NET__LAYER2']['p_strSelectedID'] = isys_format_json::encode($l_layer2_assignments);

        // Find the supernet.
        $l_quickinfo = new isys_ajax_handler_quick_info();
        $l_supernets = [];
        $l_supernet_res = $p_cat->find_responsible_supernet($_GET[C__CMDB__GET__OBJECT]);

        if (is_countable($l_supernet_res) && count($l_supernet_res) > 0) {
            while ($l_row = $l_supernet_res->get_row()) {
                $l_supernets[] = $l_quickinfo->get_quick_info($l_row['isys_obj__id'], $l_row['isys_obj__title'], C__LINK__OBJECT);
            }
        }

        // Apply rules.
        $this->get_template_component()
            ->assign('supernets', implode(', ', $l_supernets))
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    }
}
