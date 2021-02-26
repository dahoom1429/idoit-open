<?php

use idoit\Component\Helper\Ip;

/**
 * i-doit
 *
 * DAO: Specific DHCP list.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_net_dhcp extends isys_component_dao_category_table_list
{
    /**
     * Method for retrieving the category ID.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__NET_DHCP');
    }

    /**
     * Method for retrieving the category-type.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     * Order condition.
     *
     * @param   string $p_column
     * @param   string $p_direction
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function get_order_condition($p_column, $p_direction)
    {
        if ($p_column == 'isys_cats_net_dhcp_list__range_from') {
            $p_column = 'isys_cats_net_dhcp_list__range_from_long';
        } else if ($p_column == 'isys_cats_net_dhcp_list__range_to') {
            $p_column = 'isys_cats_net_dhcp_list__range_to_long';
        }

        return $p_column . " " . $p_direction;
    }

    /**
     * Method for modifying the single rows for displaying links or getting translations.
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   array                   & $p_row
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function modify_row(&$p_row)
    {
        $l_net_dao = new isys_cmdb_dao_category_s_net(isys_application::instance()->database);
        $l_net_row = $l_net_dao->get_data(null, $p_row['isys_cats_net_dhcp_list__isys_obj__id'])
            ->get_row();

        // This is a bugfix, because we don't update the IP assignments for IPv6 yet.
        if ($l_net_row['isys_cats_net_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
            $l_sql = 'SELECT isys_net_dhcpv6_type__title
                FROM isys_net_dhcpv6_type
                WHERE isys_net_dhcpv6_type__id = ' . $this->convert_sql_id($p_row['isys_cats_net_dhcp_list__isys_net_dhcpv6_type__id']) . ';';

            $p_row['type_title'] = isys_application::instance()->container->get('language')
                ->get($this->retrieve($l_sql)
                    ->get_row_value('isys_net_dhcpv6_type__title'));
            $p_row['isys_cats_net_dhcp_list__range_from'] = Ip::validate_ipv6($p_row['isys_cats_net_dhcp_list__range_from'], true);
            $p_row['isys_cats_net_dhcp_list__range_to'] = Ip::validate_ipv6($p_row['isys_cats_net_dhcp_list__range_to'], true);
        } else {
            $l_sql = 'SELECT isys_net_dhcp_type__title
                FROM isys_net_dhcp_type
                WHERE isys_net_dhcp_type__id = ' . $this->convert_sql_id($p_row['isys_cats_net_dhcp_list__isys_net_dhcp_type__id']) . ';';

            $p_row['type_title'] = isys_application::instance()->container->get('language')
                ->get($this->retrieve($l_sql)
                    ->get_row_value('isys_net_dhcp_type__title'));
        }
    }

    /**
     * Method for retrieving the fields to display in the list-view.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_fields()
    {
        return [
            'isys_cats_net_dhcp_list__id'              => 'ID',
            'isys_cats_net_dhcp_list__range_from'      => 'LC__CMDB__CATS__NET__DHCP_RANGE_FROM',
            'isys_cats_net_dhcp_list__range_from_long' => false,
            'isys_cats_net_dhcp_list__range_to'        => 'LC__CMDB__CATS__NET__DHCP_RANGE_TO',
            'isys_cats_net_dhcp_list__range_to_long'   => false,
            'type_title'                               => 'LC__CMDB__CATS__NET__DHCP_TYPE',
        ];
    }
}
