<?php

/**
 * i-doit
 *
 * DAO: specific category for chassis enclosure.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.0
 * @author      Leonard Fischer <lfischer@i-doit.org>
 */
class isys_cmdb_dao_category_s_chassis_cabling extends isys_cmdb_dao_category_s_virtual
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'chassis_cabling';

    /**
     * Returns the amount of assigned objects to highlight the navigation point.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_count($p_obj_id = null)
    {
        return count(isys_cmdb_dao_category_s_chassis::instance($this->get_database_component())
            ->get_assigned_objects($p_obj_id));
    }

    /**
     * Save method.
     *
     * @param   boolean $p_create
     *
     * @return  mixed Category data's identifier (int) or false (bool), otherwise null if nothing is created/saved.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_user_data($p_create)
    {
        /**
         * LF: Seems as if the connection methods for "ports" and "FC-ports" are identical.
         * I noticed this to late, but now we have separated logic "just in case" ;).
         */
        $l_ports = $l_fcports = $l_logports = [];

        // We iterate through the POST array to find all the hidden values.
        foreach ($_POST as $l_key => $l_value) {
            if (strpos($l_key, 'C__CMDB__CATS__CHASSIS_CABLING__LOG_PORT_') === 0 && substr($l_key, -8) === '__HIDDEN') {
                $l_option = explode('_', substr($l_key, 41, -8));

                // It's a connected FC-port.
                $l_logports[$l_option[0]][$l_option[1]] = $l_value;
            }

            if (strpos($l_key, 'C__CMDB__CATS__CHASSIS_CABLING__PORT_') === 0 && substr($l_key, -8) === '__HIDDEN') {
                $l_option = explode('_', substr($l_key, 37, -8));

                if ($l_option[0] === 'C') {
                    // It's a cable.
                    $l_ports[$l_option[1]][$l_option[2]]['cable'] = $l_value;
                } else {
                    // It's a connected port.
                    $l_ports[$l_option[0]][$l_option[1]]['port'] = $l_value;
                }
            }

            if (strpos($l_key, 'C__CMDB__CATS__CHASSIS_CABLING__FCPORT_') === 0 && substr($l_key, -8) === '__HIDDEN') {
                $l_option = explode('_', substr($l_key, 39, -8));

                if ($l_option[0] === 'C') {
                    // It's a cable.
                    $l_fcports[$l_option[1]][$l_option[2]]['cable'] = $l_value;
                } else {
                    // It's a connected FC-port.
                    $l_fcports[$l_option[0]][$l_option[1]]['port'] = $l_value;
                }
            }
        }

        $l_dao_cable_con = isys_factory::get_instance('isys_cmdb_dao_cable_connection', $this->get_database_component());

        // Now we save the port assignments.
        if (is_array($l_logports)) {
            $l_logport_dao = isys_cmdb_dao_category_g_network_ifacel::instance($this->get_database_component());

            foreach ($l_logports as $l_obj_id => $l_logport_array) {
                foreach ($l_logport_array as $l_port_id => $l_logport_values) {
                    if (!empty($l_logport_values)) {
                        $l_logport_dao->attach_log_port($l_port_id, $l_logport_values);
                    } else {
                        $l_logport_dao->detach_log_port($l_port_id);
                    }
                }
            }
        }

        // Now we save the port assignments.
        if (is_array($l_ports)) {
            $l_port_dao = isys_cmdb_dao_category_g_network_port::instance($this->get_database_component());

            foreach ($l_ports as $l_obj_id => $l_port_array) {
                foreach ($l_port_array as $l_port_id => $l_port_values) {
                    $l_cable_id = $l_port_values['cable'];

                    if (!empty($l_port_values['port'])) {
                        // If a port but no cable was selected, we create one.
                        if (empty($l_cable_id)) {
                            $l_cable_name = $_POST['C__CMDB__CATS__CHASSIS_CABLING__PORT_' . $l_obj_id . '_' . $l_port_id . '__CABLE_NAME'];
                            $l_cable_id = $l_cableID = isys_cmdb_dao_cable_connection::add_cable($l_cable_name);
                        }

                        $l_port_dao->connection_save($l_port_id, $l_port_values['port'], $l_cable_id);
                    } else {
                        $l_dao_cable_con->delete_cable_connection($l_dao_cable_con->get_cable_connection_id_by_connector_id($l_port_id));
                    }
                }
            }
        }

        // And now the FC-port assignments.
        if (is_array($l_fcports)) {
            $l_fcport_dao = isys_cmdb_dao_category_g_controller_fcport::instance($this->get_database_component());

            foreach ($l_fcports as $l_obj_id => $l_fcport_array) {
                foreach ($l_fcport_array as $l_fcport_id => $l_fcport_values) {
                    $l_cable_id = $l_fcport_values['cable'];

                    if (!empty($l_fcport_values['port'])) {
                        // If a port but no cable was selected, we create one.
                        if (empty($l_cable_id)) {
                            $l_cable_name = $_POST['C__CMDB__CATS__CHASSIS_CABLING__FCPORT_' . $l_obj_id . '_' . $l_fcport_id . '__CABLE_NAME'];
                            $l_cable_id = $l_cableID = isys_cmdb_dao_cable_connection::add_cable($l_cable_name);
                        }

                        $l_fcport_dao->connection_save($l_fcport_id, $l_fcport_values['port'], $l_cable_id);
                    } else {
                        $l_dao_cable_con->delete_cable_connection($l_dao_cable_con->get_cable_connection_id_by_connector_id($l_fcport_id));
                    }
                }
            }
        }

        return null;
    }

    /**
     * Method for retrieving all fibre channel-ports of a given device in the needed format.
     *
     * @param  integer $p_device_obj_id
     * @param  array   &$p_rules
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_fc_ports_for_ui($p_device_obj_id, &$p_rules = [])
    {
        $l_fc_ports = [];

        $l_fc_port_dao = isys_cmdb_dao_category_g_controller_fcport::instance($this->get_database_component());
        $l_conn_dao = isys_cmdb_dao_category_g_connector::instance($this->get_database_component());
        $l_cable_conn_dao = isys_factory::get_instance('isys_cmdb_dao_cable_connection', $this->get_database_component());

        $l_fc_port_res = $l_fc_port_dao->get_data(null, $p_device_obj_id, '', null, C__RECORD_STATUS__NORMAL);

        while ($l_fc_port_row = $l_fc_port_res->get_row()) {
            $l_conn_obj = $l_cable_obj = [];

            if ($l_fc_port_row["con_connector"] > 0) {
                $l_conn_obj = $l_conn_dao->get_data($l_fc_port_row["con_connector"])
                    ->get_row();

                $l_cable_obj = $l_cable_conn_dao->get_object_by_id($l_conn_obj['isys_cable_connection__isys_obj__id'])
                    ->get_row();
            }

            $l_cable_obj_browser_name = 'C__CMDB__CATS__CHASSIS_CABLING__FCPORT_C_' . $p_device_obj_id . '_' .
                $l_fc_port_row['isys_catg_fc_port_list__isys_catg_connector_list__id'];
            $l_conn_obj_browser_name = 'C__CMDB__CATS__CHASSIS_CABLING__FCPORT_' . $p_device_obj_id . '_' .
                $l_fc_port_row['isys_catg_fc_port_list__isys_catg_connector_list__id'];

            $l_fc_ports[] = [
                'id'                     => $l_fc_port_row['isys_catg_fc_port_list__id'],
                'title'                  => $l_fc_port_row['isys_catg_fc_port_list__title'],
                'type_title'             => $l_fc_port_row['isys_fc_port_type__title'],
                'cable_obj_id'           => $l_cable_obj['isys_obj__id'],
                'cable_obj_title'        => $l_cable_obj['isys_obj__title'],
                'cable_obj_type'         => isys_application::instance()->container->get('language')
                    ->get($l_cable_obj['isys_obj_type__title']),
                'cable_obj_browser_name' => $l_cable_obj_browser_name,
                'conn_obj_id'            => $l_conn_obj['isys_obj__id'],
                'conn_obj_title'         => $l_conn_obj['isys_obj__title'],
                'conn_obj_type'          => isys_application::instance()->container->get('language')
                    ->get($l_conn_dao->get_objtype_name_by_id_as_string($l_conn_obj['isys_obj__isys_obj_type__id'])),
                'conn_obj_browser_name'  => $l_conn_obj_browser_name,
                'conn_obj_port_id'       => $l_conn_obj['isys_catg_connector_list__id'],
                'conn_obj_port_title'    => $l_conn_obj['isys_catg_connector_list__title']
            ];

            if (count($l_conn_obj) > 0) {
                $p_rules[$l_conn_obj_browser_name]["p_strValue"] = $l_cable_conn_dao->get_assigned_connector_id($l_fc_port_row["isys_catg_fc_port_list__isys_catg_connector_list__id"]);
                $p_rules[$l_cable_obj_browser_name]["p_strValue"] = $l_cable_conn_dao->get_assigned_cable($l_fc_port_row["isys_catg_fc_port_list__isys_catg_connector_list__id"]);
            }
        }

        return $l_fc_ports;
    }

    /**
     * Method for retrieving all logical-ports of a given device in the needed format.
     *
     * @param   integer $p_device_obj_id
     * @param   array   &$p_rules
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_log_ports_for_ui($p_device_obj_id, &$p_rules = [])
    {
        $l_log_ports = [];

        $l_log_port_dao = isys_cmdb_dao_category_g_network_ifacel::instance($this->get_database_component());
        $l_conn_dao = isys_cmdb_dao_category_g_connector::instance($this->get_database_component());

        $l_log_port_res = $l_log_port_dao->get_data(null, $p_device_obj_id, '', null, C__RECORD_STATUS__NORMAL);

        while ($l_log_port_row = $l_log_port_res->get_row()) {
            $l_conn_obj = [];

            if ($l_log_port_row['isys_catg_log_port_list__isys_catg_log_port_list__id'] > 0) {
                $l_conn_obj = $l_log_port_dao->get_data($l_log_port_row['isys_catg_log_port_list__isys_catg_log_port_list__id'])
                    ->get_row();
            }

            $l_conn_obj_browser_name = 'C__CMDB__CATS__CHASSIS_CABLING__LOG_PORT_' . $p_device_obj_id . '_' . $l_log_port_row['isys_catg_log_port_list__id'];

            $l_log_ports[] = [
                'id'                    => $l_log_port_row['isys_catg_log_port_list__id'],
                'title'                 => $this->get_log_port_title_formatted($l_log_port_row['isys_catg_log_port_list__id'], true),
                'type_title'            => $l_log_port_row['isys_netx_ifacel_type__title'],
                'conn_obj_id'           => $l_conn_obj['isys_obj__id'],
                'conn_obj_title'        => $this->get_log_port_title_formatted($l_log_port_row['isys_catg_log_port_list__isys_catg_log_port_list__id']),
                'conn_obj_type'         => isys_application::instance()->container->get('language')
                    ->get($l_conn_dao->get_objtype_name_by_id_as_string($l_conn_obj['isys_obj__isys_obj_type__id'])),
                'conn_obj_browser_name' => $l_conn_obj_browser_name,
                'conn_obj_port_id'      => $l_conn_obj['isys_catg_log_port_list__id'],
                'conn_obj_port_title'   => $l_conn_obj['isys_catg_log_port_list__title']
            ];

            // We define some new rules for the "callback-accept" to work properly.
            $p_rules[$l_conn_obj_browser_name]["p_strClass"] = 'log-port-' . $l_log_port_row['isys_catg_log_port_list__id'];
            $p_rules[$l_conn_obj_browser_name]["hidden_class"] = 'log-port-hidden-' . $l_log_port_row['isys_catg_log_port_list__id'];
            $p_rules[$l_conn_obj_browser_name]["callback_accept"] = 'window.copy_value_from(' . (int)$l_log_port_row['isys_catg_log_port_list__id'] . ');';

            if (count($l_conn_obj) > 0) {
                $p_rules[$l_conn_obj_browser_name]["p_strValue"] = $l_log_port_row['isys_catg_log_port_list__isys_catg_log_port_list__id'];
                $p_rules[$l_conn_obj_browser_name]["callback_detach"] = 'window.copy_value_from(' . (int)$l_log_port_row['isys_catg_log_port_list__id'] . ', ' .
                    (int)$l_log_port_row['isys_catg_log_port_list__isys_catg_log_port_list__id'] . ');';
            }

        }

        return $l_log_ports;
    }

    /**
     * Method for quickly retrieving the detailed log. port name:
     *   <obj-title> > <log.port-title> (VLAN: <assigned vlan's>), <assigned chassis slot>
     *
     * @param   integer $p_cat_id
     * @param   boolean $p_exclude_object_title
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_log_port_title_formatted($p_cat_id, $p_exclude_object_title = false)
    {
        if (!$p_cat_id) {
            return '';
        }

        /**
         * IDE typehinting.
         *
         * @var  $l_log_port_dao  isys_cmdb_dao_category_g_network_ifacel
         */
        $l_log_port_dao = isys_cmdb_dao_category_g_network_ifacel::instance($this->get_database_component());

        // First we retrieve the data of the given port to find it's object and name.
        $l_log_port_row = $l_log_port_dao->get_data($p_cat_id)
            ->get_row();

        // Now we check if we need to exclude the object-title.
        if ($p_exclude_object_title === true) {
            $l_title = $l_log_port_row['isys_catg_log_port_list__title'];
        } else {
            $l_title = $l_log_port_dao->get_obj_name_by_id_as_string($l_log_port_row['isys_catg_log_port_list__isys_obj__id']) .
                isys_tenantsettings::get('gui.separator.connector', ' > ') . $l_log_port_row['isys_catg_log_port_list__title'];
        }

        $l_layer2 = '';

        $l_l2nets = $l_log_port_dao->get_attached_layer_2_net($p_cat_id);

        if (is_countable($l_l2nets) && count($l_l2nets) > 0) {
            $l_tmp = [];

            foreach ($l_l2nets as $l_l2net_obj_id) {
                $l_tmp[] = $l_log_port_dao->get_obj_name_by_id_as_string($l_l2net_obj_id);
            }

            $l_layer2 .= ' <em>(VLAN: ' . implode(', ', $l_tmp) . ')</em>';
        }

        return $l_title . $l_layer2;
    }

    /**
     * Method for retrieving all network-ports of a given device in the needed format.
     *
     * @param   integer $p_device_obj_id
     * @param   array   &$p_rules
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_ports_for_ui($p_device_obj_id, &$p_rules = [])
    {
        $l_ports = [];

        $l_port_dao = isys_cmdb_dao_category_g_network_port::instance($this->get_database_component());
        $l_conn_dao = isys_cmdb_dao_category_g_connector::instance($this->get_database_component());
        $l_cable_conn_dao = isys_factory::get_instance('isys_cmdb_dao_cable_connection', $this->get_database_component());

        $l_port_res = $l_port_dao->get_data(null, $p_device_obj_id, '', null, C__RECORD_STATUS__NORMAL);

        while ($l_port_row = $l_port_res->get_row()) {
            $l_conn_obj = $l_cable_obj = [];

            if ($l_port_row["con_connector"] > 0) {
                $l_conn_obj = $l_conn_dao->get_data($l_port_row["con_connector"])
                    ->get_row();

                $l_cable_obj = $l_cable_conn_dao->get_object_by_id($l_conn_obj['isys_cable_connection__isys_obj__id'])
                    ->get_row();
            }

            $l_cable_obj_browser_name = 'C__CMDB__CATS__CHASSIS_CABLING__PORT_C_' . $p_device_obj_id . '_' . $l_port_row['isys_catg_port_list__isys_catg_connector_list__id'];
            $l_conn_obj_browser_name = 'C__CMDB__CATS__CHASSIS_CABLING__PORT_' . $p_device_obj_id . '_' . $l_port_row['isys_catg_port_list__isys_catg_connector_list__id'];

            $l_ports[] = [
                'id'                     => $l_port_row['isys_catg_port_list__id'],
                'title'                  => $l_port_row['isys_catg_port_list__title'],
                'type_title'             => $l_port_row['isys_port_type__title'],
                'cable_obj_id'           => $l_cable_obj['isys_obj__id'],
                'cable_obj_title'        => $l_cable_obj['isys_obj__title'],
                'cable_obj_type'         => isys_application::instance()->container->get('language')
                    ->get($l_cable_obj['isys_obj_type__title']),
                'cable_obj_browser_name' => $l_cable_obj_browser_name,
                'conn_obj_id'            => $l_conn_obj['isys_obj__id'],
                'conn_obj_title'         => $l_conn_obj['isys_obj__title'],
                'conn_obj_type'          => isys_application::instance()->container->get('language')
                    ->get($l_conn_dao->get_objtype_name_by_id_as_string($l_conn_obj['isys_obj__isys_obj_type__id'])),
                'conn_obj_browser_name'  => $l_conn_obj_browser_name,
                'conn_obj_port_id'       => $l_conn_obj['isys_catg_connector_list__id'],
                'conn_obj_port_title'    => $l_conn_obj['isys_catg_connector_list__title']
            ];

            if (count($l_conn_obj) > 0) {
                $l_request = isys_request::factory()
                    ->set_row($l_port_row);

                $p_rules[$l_cable_obj_browser_name]["p_strValue"] = $l_port_dao->callback_property_cable($l_request);
                $p_rules[$l_conn_obj_browser_name]["p_strValue"] = $l_port_dao->callback_property_assigned_connector($l_request);
            }
        }

        return $l_ports;
    }

    /**
     * Old save method, used by the system.
     *
     * @param   integer $p1
     * @param   integer $p2
     * @param   boolean $p_create
     *
     * @return  mixed  See $this->save_user_data().
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @todo    Remove this method, once "save_user_data" is called directly.
     */
    public function save_element($p1, $p2, $p_create)
    {
        return $this->save_user_data($p_create);
    }
}

?>
