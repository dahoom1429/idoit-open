<?php

use idoit\Component\Helper\Ip;

/**
 * i-doit
 *
 * DAO: Specific net zone list.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_net_zone extends isys_component_dao_category_table_list
{
    /**
     * Method for retrieving the category ID.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__NET_ZONE');
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
        if ($p_column == 'isys_cats_net_zone_list__range_from') {
            $p_column = 'isys_cats_net_zone_list__range_from_long';
        } else if ($p_column == 'isys_cats_net_zone_list__range_to') {
            $p_column = 'isys_cats_net_zone_list__range_to_long';
        }

        return $p_column . " " . $p_direction;
    }

    /**
     * Method for modifying the single rows for displaying links or getting translations.
     *
     * @param   array & $p_row
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function modify_row(&$p_row)
    {
        $l_net_dao = new isys_cmdb_dao_category_s_net(isys_application::instance()->database);
        $l_net_row = $l_net_dao->get_data(null, $p_row['isys_cats_net_zone_list__isys_obj__id'])
            ->get_row();

        // This is a bugfix, because we don't update the IP assignments for IPv6 yet.
        if ($l_net_row['isys_cats_net_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
            $p_row['isys_cats_net_zone_list__range_from'] = Ip::validate_ipv6($p_row['isys_cats_net_zone_list__range_from'], true);
            $p_row['isys_cats_net_zone_list__range_to'] = Ip::validate_ipv6($p_row['isys_cats_net_zone_list__range_to'], true);
        }

        $p_row['zone_object'] = isys_tenantsettings::get('gui.empty_value', '-');

        if ($p_row['isys_cats_net_zone_list__isys_obj__id__zone'] > 0) {
            // @see ID-4825 Use global information for the object and type name, because net zone options might not be set yet.
            $zoneObject = isys_cmdb_dao::instance(isys_application::instance()->database)
                ->get_object($p_row['isys_cats_net_zone_list__isys_obj__id__zone'])
                ->get_row();

            $zoneColor = isys_cmdb_dao_category_g_net_zone_options::instance(isys_application::instance()->database)
                ->get_data(null, $p_row['isys_cats_net_zone_list__isys_obj__id__zone'])
                ->get_row_value('isys_catg_net_zone_options_list__color');

            $p_row['zone_object'] = '<div class="cmdb-marker" style="background-color:' . ($zoneColor ?: '#fff') . ';"></div> ' .
                (new isys_ajax_handler_quick_info())->get_quick_info($zoneObject['isys_obj__id'], isys_application::instance()->container->get('language')
                        ->get($zoneObject['isys_obj_type__title']) . ' &raquo; ' . $zoneObject['isys_obj__title'], C__LINK__OBJECT);
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
            'isys_cats_net_zone_list__id'              => 'ID',
            'isys_cats_net_zone_list__range_from'      => 'LC__CMDB__CATS__NET__ZONE_RANGE_FROM',
            'isys_cats_net_zone_list__range_from_long' => false,
            'isys_cats_net_zone_list__range_to'        => 'LC__CMDB__CATS__NET__ZONE_RANGE_TO',
            'isys_cats_net_zone_list__range_to_long'   => false,
            'zone_object'                              => 'LC__CMDB__CATS__NET__ZONE',
        ];
    }
}
