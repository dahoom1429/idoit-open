<?php

/**
 * i-doit
 *
 * DAO: specific category list for network connector
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_net_connector extends isys_component_dao_category_table_list
{

    /**
     * Gets fields to display in the list view.
     *
     * @return  array
     */
    public function get_fields()
    {
        $l_properties = $this->m_cat_dao->get_properties();

        return [
            @$l_properties['ip_address'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] .
            '__title'               => $l_properties['ip_address'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            'port_range'            => isys_application::instance()->container->get('language')
                    ->get('Port') . ' / ' . isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__PORT_RANGE'),
            'connected_to_listener' => 'LC__CMDB__CATG__NET_CONNECTOR__CONNECTED_TO',
            'protocol'              => 'LC__CMDB__CATG__NET_LISTENER__PROTOCOL',
            'opened_by'             => 'LC__UNIVERSAL__APPLICATION',
            'gateway_title'         => 'LC__CATG__NET_CONNECTIONS__GATEWAY'
        ];
    }

    /**
     * Modifies single rows for displaying links or getting translations
     *
     * @param   array & $p_row
     */
    public function modify_row(&$p_row)
    {
        $quickinfo = new isys_ajax_handler_quick_info();

        $p_row['isys_id'] = $p_row['isys_catg_net_connector_list__id'];

        $p_row['port_range'] = $p_row['isys_catg_net_connector_list__port_from'];

        if ($p_row['isys_catg_net_connector_list__port_from'] != $p_row['isys_catg_net_connector_list__port_to']) {
            $p_row['port_range'] .= '-' . $p_row['isys_catg_net_connector_list__port_to'];
        }

        if ($p_row['isys_catg_net_connector_list__isys_catg_net_listener_list__id'] > 0) {
            $l_listener_dao = isys_cmdb_dao_category_g_net_listener::instance($this->m_db);
            $l_listener_data = $l_listener_dao->get_data_by_id($p_row['isys_catg_net_connector_list__isys_catg_net_listener_list__id'])
                ->get_row();

            if (isset($l_listener_data['isys_obj__id'])) {
                $l_listener_url = isys_helper_link::create_url([
                    C__CMDB__GET__OBJECT   => $l_listener_data['isys_obj__id'],
                    C__CMDB__GET__CATG     => defined_or_default('C__CATG__NET_LISTENER'),
                    C__CMDB__GET__CATLEVEL => $l_listener_data['isys_catg_net_listener_list__id']
                ]);
                $p_row['connected_to_listener'] = $quickinfo->get_quick_info($l_listener_data['isys_obj__id'], $l_listener_data['isys_obj__title'] . ' (' .
                    isys_application::instance()->container->get('language')
                        ->get($l_listener_data['isys_obj_type__title']) . ')', $l_listener_url);
            }

            $l_protocol = $l_listener_dao->get_dialog('isys_net_protocol', $p_row['isys_catg_net_listener_list__isys_net_protocol__id'])
                ->__to_array();
            $p_row['protocol'] = $l_protocol['isys_net_protocol__title'];
        } else {
            $p_row['connected_to_listener'] = '-';
        }

        if ($p_row['isys_catg_net_listener_list__opened_by']) {
            $p_row['opened_by'] = $quickinfo->get_quick_info(
                $p_row['isys_catg_net_listener_list__opened_by'],
                $this->m_cat_dao->obj_get_title_by_id_as_string($p_row['isys_catg_net_listener_list__opened_by']),
                C__LINK__OBJECT
            );
        } else {
            $p_row['opened_by'] = '-';
        }
    }
}
