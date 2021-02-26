<?php

/**
 * i-doit
 *
 * DAO: ObjectType list for logical interfaces (subcategory of network)
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_network_ifacel extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__NETWORK_LOG_PORT');
    }

    /**
     * Return constant of category type
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Exchange column to create individual links in columns
     *
     * @param   array & $p_arrRow
     *
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function modify_row(&$p_arrRow)
    {
        $l_nInterfaceID = $p_arrRow['isys_catg_log_port_list__id'];
        $l_dao = isys_cmdb_dao_category_g_network_ifacel::instance($this->m_db);
        $l_quick_info = isys_ajax_handler_quick_info::instance();
        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');

        $l_layer_2_nets = $l_dao->get_attached_layer_2_net($l_nInterfaceID, null);
        $assignedPorts = $l_dao->get_ports_for_ifacel($l_nInterfaceID);

        $p_arrRow['isys_catg_log_port_list__active'] = $p_arrRow['isys_catg_log_port_list__active'] > 0? 'LC__UNIVERSAL__YES': 'LC__UNIVERSAL__NO';
        $p_arrRow['portAssignments'] = $assignedPorts ?: $l_empty_value;

        if (is_array($l_layer_2_nets) > 0) {
            $p_arrRow['object_connection'] = [];
            $i = 1;
            foreach ($l_layer_2_nets as $l_obj_id) {
                if ($i++ === isys_tenantsettings::get('cmdb.limits.port-lists-layer2', 5)) {
                    $p_arrRow['object_connection'][] = '...';
                    break;
                }

                $p_arrRow['object_connection'][] = $l_quick_info->get_quick_info($l_obj_id, $l_dao->get_obj_name_by_id_as_string($l_obj_id), C__LINK__OBJECT);
            }
        }

        $p_arrRow['attached_log_port'] = $l_empty_value;

        if ($p_arrRow['isys_catg_log_port_list__isys_catg_log_port_list__id'] > 0) {
            $l_attached_log_port = $l_dao->get_attached_log_port($p_arrRow['isys_catg_log_port_list__isys_catg_log_port_list__id'])
                ->get_row();

            if ($l_attached_log_port) {
                $p_arrRow['attached_log_port'] = '<ul class="list-style-none m0"><li>' . $l_quick_info->get_quick_info(
                    $l_attached_log_port['isys_obj__id'],
                    $l_dao->get_obj_name_by_id_as_string($l_attached_log_port['isys_obj__id']) . ' -> ' . $l_attached_log_port['isys_catg_log_port_list__title']
                ) .
                    '</li></ul>';
            }
        }

        if (!$p_arrRow['object_connection']) {
            $p_arrRow['object_connection'] = $l_empty_value;
        }

        // Here we fetch the assigned host addresses.
        $l_hostaddress_dao = isys_cmdb_dao_category_g_ip::instance($this->m_db);

        $l_hostaddress_res = $l_hostaddress_dao->get_data(null, null, 'AND isys_catg_ip_list__isys_catg_log_port_list__id = ' . (int)$p_arrRow['isys_catg_log_port_list__id']);

        $p_arrRow['isys_cats_net_ip_addresses_list__title'] = [];

        while ($l_hostaddress_row = $l_hostaddress_res->get_row()) {
            $p_arrRow['isys_cats_net_ip_addresses_list__title'][] = $l_hostaddress_row['isys_cats_net_ip_addresses_list__title'];
        }

        if (is_countable($p_arrRow['isys_cats_net_ip_addresses_list__title']) && count($p_arrRow['isys_cats_net_ip_addresses_list__title']) > 0) {
            $p_arrRow['isys_cats_net_ip_addresses_list__title'] = implode(', ', $p_arrRow['isys_cats_net_ip_addresses_list__title']);
        } else {
            $p_arrRow['isys_cats_net_ip_addresses_list__title'] = $l_empty_value;
        }
    }

    /**
     * Returns array with table headers.
     *
     * @return  array
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_fields()
    {
        return [
            'isys_catg_log_port_list__title' => 'LC__CMDB__CATG__INTERFACE_L__TITLE',
            'isys_netx_ifacel_type__title' => 'LC__CMDB__CATS__NET__TYPE',
            'isys_cats_net_ip_addresses_list__title' => 'LC__CMDB__TREE__SYSTEM__SETTINGS_SYSTEM__IPS',
            'object_connection' => 'LC__CMDB__CATS__NET__LAYER2_NET',
            'attached_log_port' => 'LC__CMDB__CATG__NETWORK__TARGET_OBJECT',
            'isys_catg_log_port_list__mac' => 'LC__CMDB__CATG__NETWORK__MAC',
            'isys_netp_ifacel_standard__title' => 'LC__CMDB__CATG__PORT__STANDARD',
            'isys_catg_log_port_list__active' => 'LC__CATP__IP__ACTIVE',
            'portAssignments' => 'LC__CMDB__CATG__INTERFACE_L__PORT_ALLOCATION'
        ];
    }
}
