<?php

use idoit\Component\Helper\Ip;

/**
 * i-doit
 *
 * CMDB specific category for DCHP.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-8
 */
class isys_cmdb_ui_category_s_net_dhcp extends isys_cmdb_ui_category_specific
{
    /**
     * Show the detail-template for specific category dhcp.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_general
     * @return  array|void
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_ipv4 = true;
        $l_catdata = $p_cat->get_general_data();

        // We get the address range from the NET category, to validate the user input (look in the template).
        $l_row = isys_cmdb_dao_category_s_net::instance($this->m_database_component)
            ->get_data(null, $_GET[C__CMDB__GET__OBJECT], '', null, C__RECORD_STATUS__NORMAL)
            ->get_row();

        $l_dhcp_ranges = [];
        $l_address_range_from = '0';
        $l_address_range_to = '0';

        // Because we have to deal with IPv4 and IPv6 seperately, we have to assign seperately.
        $l_rules['C__CATS__NET_DHCP_TYPE']['p_strTable'] = 'isys_net_dhcp_type';
        $l_rules['C__CATS__NET_DHCP_TYPE']['p_strSelectedID'] = $l_catdata['isys_cats_net_dhcp_list__isys_net_dhcp_type__id'];
        $l_rules['C__CATS__NET_DHCPV6_TYPE']['p_strTable'] = 'isys_net_dhcpv6_type';
        $l_rules['C__CATS__NET_DHCPV6_TYPE']['p_strSelectedID'] = $l_catdata['isys_cats_net_dhcp_list__isys_net_dhcpv6_type__id'];
        $l_rules['C__CMDB__CAT__COMMENTARY_' . $p_cat->get_category_type() . $p_cat->get_category_id()]['p_strValue'] = $l_catdata['isys_cats_net_dhcp_list__description'];

        // We look if we are inside a IPv4 layer3 net, because for IPv6 we're not able to validate yet.
        if ($l_row['isys_cats_net_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
            $l_rules['C__CATS__NET_DHCP_RANGE_FROM']['p_strValue'] = $l_catdata['isys_cats_net_dhcp_list__range_from'];
            $l_rules['C__CATS__NET_DHCP_RANGE_TO']['p_strValue'] = $l_catdata['isys_cats_net_dhcp_list__range_to'];

            // We save the address range for later validation (see template).
            $l_address_range_from = $l_row['isys_cats_net_list__address_range_from'];
            $l_address_range_to = $l_row['isys_cats_net_list__address_range_to'];

            $l_condition = '';

            // If we are in edit-mode, we don't want to retrieve this entry itself!.
            if ($l_catdata !== null) {
                $l_condition = 'AND isys_cats_net_dhcp_list__id != ' . (int)$l_catdata['isys_cats_net_dhcp_list__id'];
            }

            // But we also need all other DHCP address-ranges, so that we can check that no ranges overleap.
            $l_dhcp_dao = new isys_cmdb_dao_category_s_net_dhcp($this->get_database_component());
            $l_dhcp_res = $l_dhcp_dao->get_data(null, $_GET[C__CMDB__GET__OBJECT], $l_condition, null, C__RECORD_STATUS__NORMAL);

            while ($l_dhcp_row = $l_dhcp_res->get_row()) {
                $l_dhcp_ranges[] = [
                    'from' => Ip::ip2long($l_dhcp_row['isys_cats_net_dhcp_list__range_from']),
                    'to'   => Ip::ip2long($l_dhcp_row['isys_cats_net_dhcp_list__range_to'])
                ];
            }
        } else if ($l_row['isys_cats_net_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
            $l_ipv4 = false;
            $l_rules['C__CATS__NET_DHCP_RANGE_FROM']['p_strValue'] = Ip::validate_ipv6($l_catdata['isys_cats_net_dhcp_list__range_from'], true);
            $l_rules['C__CATS__NET_DHCP_RANGE_TO']['p_strValue'] = Ip::validate_ipv6($l_catdata['isys_cats_net_dhcp_list__range_to'], true);
        }

        $this->get_template_component()
            ->assign('ip_type', ($l_ipv4 === true ? 'ipv4' : 'ipv6'))
            ->assign('address_range_from', $l_address_range_from)
            ->assign('address_range_to', $l_address_range_to)
            ->assign('dhcp_ranges', isys_format_json::encode($l_dhcp_ranges));

        // Also, when we create a new entry - Set the full address range as default.
        if ($l_catdata === null) {
            $l_rules['C__CATS__NET_DHCP_TYPE']['p_strSelectedID'] = defined_or_default('C__NET__DHCP_DYNAMIC');
            $l_rules['C__CATS__NET_DHCPV6_TYPE']['p_strSelectedID'] = defined_or_default('C__NET__DHCPV6__DHCPV6');
            $l_rules['C__CATS__NET_DHCP_RANGE_FROM']['p_strValue'] = $l_row['isys_cats_net_list__address_range_from'];
            $l_rules['C__CATS__NET_DHCP_RANGE_TO']['p_strValue'] = $l_row['isys_cats_net_list__address_range_to'];
        }

        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }

    /**
     * Process the list-view.
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
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */

    /*
    public function process_list(isys_cmdb_dao_category &$p_cat, $p_get_param_override = NULL, $p_strVarName = NULL, $p_strTemplateName = NULL, $p_bCheckbox = true, $p_bOrderLink = true, $p_db_field_name = NULL)
    {
        // We create our list DAO.
        $l_dao_list = new isys_cmdb_dao_list_cats_net_dhcp($this->get_database_component());

        // We cast the object-id to INT so nobody can do bad bad things to our code.
        $l_obj_id = (int) $_GET[C__CMDB__GET__OBJECT];

        // We call the list_view method, which handles the rest.
        $this->list_view("isys_cats_net_dhcp_list", $l_obj_id, $l_dao_list);

        return true;
    }
    */
}
