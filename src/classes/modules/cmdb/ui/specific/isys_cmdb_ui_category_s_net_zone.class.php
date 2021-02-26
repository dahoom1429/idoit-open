<?php

use idoit\Component\Helper\Ip;

/**
 * i-doit
 *
 * CMDB specific category for net zones.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.8.1
 */
class isys_cmdb_ui_category_s_net_zone extends isys_cmdb_ui_category_specific
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
        $l_rules = parent::process($p_cat);

        $l_object_id = (int)($p_cat->get_object_id() ?: $_GET[C__CMDB__GET__OBJECT]);
        $l_catdata = $p_cat->get_general_data();

        // We get the address range from the NET category, to validate the user input (look in the template).
        $l_row = isys_cmdb_dao_category_s_net::instance($this->m_database_component)
            ->get_data(null, $l_object_id, '', null, C__RECORD_STATUS__NORMAL)
            ->get_row();

        $l_zone_ranges = [];
        $l_address_range_from = '0';
        $l_address_range_to = '0';

        $l_rules['C__CMDB__CAT__COMMENTARY_' . $p_cat->get_category_type() . $p_cat->get_category_id()]['p_strValue'] = $l_catdata['isys_cats_net_zone_list__description'];

        // We look if we are inside a IPv4 layer3 net, because for IPv6 we're not able to validate yet.
        if ($l_row['isys_cats_net_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
            // We save the address range for later validation (see template).
            $l_address_range_from = $l_row['isys_cats_net_list__address_range_from'];
            $l_address_range_to = $l_row['isys_cats_net_list__address_range_to'];

            $l_condition = '';

            // If we are in edit-mode, we don't want to retrieve this entry itself!.
            if ($l_catdata !== null) {
                $l_condition = 'AND isys_cats_net_zone_list__id != ' . (int)$l_catdata['isys_cats_net_zone_list__id'];
            }

            // But we also need all other DHCP address-ranges, so that we can check that no ranges overleap.
            $l_zone_dao = new isys_cmdb_dao_category_s_net_zone($this->get_database_component());
            $l_zone_res = $l_zone_dao->get_data(null, $l_object_id, $l_condition, null, C__RECORD_STATUS__NORMAL);

            while ($l_zone_row = $l_zone_res->get_row()) {
                $l_zone_ranges[] = [
                    'from' => Ip::ip2long($l_zone_row['isys_cats_net_zone_list__range_from']),
                    'to'   => Ip::ip2long($l_zone_row['isys_cats_net_zone_list__range_to'])
                ];
            }
        } else if ($l_row['isys_cats_net_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
            $l_rules['C__CATS__NET_ZONE_RANGE_FROM']['p_strValue'] = Ip::validate_ipv6($l_catdata['isys_cats_net_zone_list__range_from'], true);
            $l_rules['C__CATS__NET_ZONE_RANGE_FROM']['p_strClass'] = 'input-small';
            $l_rules['C__CATS__NET_ZONE_RANGE_TO']['p_strValue'] = Ip::validate_ipv6($l_catdata['isys_cats_net_zone_list__range_to'], true);
            $l_rules['C__CATS__NET_ZONE_RANGE_TO']['p_strClass'] = 'input-small';
        }

        $this->get_template_component()
            ->assign('address_range_from', $l_address_range_from)
            ->assign('address_range_to', $l_address_range_to)
            ->assign('zone_ranges', $l_zone_ranges);

        // Also, when we create a new entry - Set the full address range as default.
        if ($l_catdata === null) {
            $l_rules['C__CATS__NET_ZONE_RANGE_FROM']['p_strValue'] = $l_row['isys_cats_net_list__address_range_from'];
            $l_rules['C__CATS__NET_ZONE_RANGE_TO']['p_strValue'] = $l_row['isys_cats_net_list__address_range_to'];
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
        $l_dao_list = new isys_cmdb_dao_list_cats_net_zone($this->get_database_component());

        // We cast the object-id to INT so nobody can do bad bad things to our code.
        $l_object_id = (int) ($p_cat->get_object_id() ?: $_GET[C__CMDB__GET__OBJECT]);

        // We call the list_view method, which handles the rest.
        $this->list_view("isys_cats_net_zone_list", $l_object_id, $l_dao_list);

        return true;
    }
    */
}
