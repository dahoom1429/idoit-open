<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.0
 */
class isys_ajax_handler_chassis extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [];

        switch ($_GET['func']) {
            case 'add_log_port_to_device':
                $l_return = $this->add_log_port_to_device($_POST['obj_id'], $_POST['dest_obj_id'], $_POST['layer2_nets']);
                break;
            case 'remove_log_port_from_device':
                $l_return = $this->remove_log_port_from_device($_POST['cat_id']);
                break;
            case 'assign_slot_position':
                $l_return = $this->assign_slot_position($_POST['obj_id'], $_POST['chassis_slot_id'], $_POST['x1'], $_POST['x2'], $_POST['y1'], $_POST['y2'],
                    $_POST['insertion']);
                break;
            case 'remove_slot_position':
                $l_return = $this->remove_slot_position($_POST['obj_id'], $_POST['chassis_slot_id']);
                break;
            case 'get_assigned_devices':
                $l_return = $this->get_assigned_devices($_POST['chassis_slot_id']);
                break;
        }

        echo isys_format_json::encode($l_return);

        $this->_die();
    }

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     */
    public static function needs_hypergate()
    {
        return true;
    }

    /**
     * @param   integer $p_slot_id
     *
     * @return  array
     */
    protected function get_assigned_devices($p_slot_id)
    {
        $l_dao = isys_cmdb_dao_category_s_chassis_slot::instance($this->m_database_component);
        $l_data = $l_dao->get_assigned_chassis_items_by_cat_id($p_slot_id);
        $l_return = [
            'success' => false,
            'message' => isys_application::instance()->container->get('language')
                ->get('LC__POPUP__BROWSER__RELATION_BROWSER__NO_ASSIGNED_OBJECTS_FOUND'),
            'devices' => []
        ];

        if (is_array($l_data) && count($l_data) > 0) {
            try {
                foreach ($l_data AS $l_slot_info) {
                    $l_sql = 'SELECT isys_obj__id AS id, isys_obj__title AS title, isys_obj__isys_obj_type__id AS objtype FROM isys_connection
						INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id
						WHERE isys_connection__id = ' . $l_dao->convert_sql_id($l_slot_info['isys_cats_chassis_list__isys_connection__id']);
                    $l_obj_data = $l_dao->retrieve($l_sql)
                        ->get_row();
                    $l_return['devices'][] = [
                        'id'    => $l_obj_data['id'],
                        'title' => $l_obj_data['title'],
                        'type'  => $l_obj_data['objtype']
                    ];
                }
                $l_return['success'] = true;
                $l_return['message'] = '';
            } catch (Exception $e) {
                $l_return['message'] = $e->getMessage();
            }
        }

        return $l_return;
    }

    protected function add_log_port_to_device($p_obj_id, $p_dest_obj_id, $p_layer2nets)
    {
        $l_log_port_dao = isys_cmdb_dao_category_g_network_ifacel::instance($this->m_database_component);

        $l_obj_a = $l_log_port_dao->get_obj_name_by_id_as_string($p_obj_id);
        $l_obj_b = $l_log_port_dao->get_obj_name_by_id_as_string($p_dest_obj_id);

        $l_title_a = isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATG__NETWORK_TREE_CONFIG_PORT_L') . ' - ' . $l_obj_b;
        $l_title_b = isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATG__NETWORK_TREE_CONFIG_PORT_L') . ' - ' . $l_obj_a;

        $l_log_port_a = $l_log_port_dao->create($p_obj_id, $l_title_a, isys_format_json::decode($p_layer2nets), 1, null, null, null, '');
        $l_log_port_b = $l_log_port_dao->create($p_dest_obj_id, $l_title_b, isys_format_json::decode($p_layer2nets), 1, null, null, null, '');

        $l_log_port_dao->attach_log_port($l_log_port_a, $l_log_port_b);

        $l_chassis_cabling_dao = isys_cmdb_dao_category_s_chassis_cabling::instance($this->m_database_component);

        // With this method we retrieve our formatted port title "<port-title> (VLAN: <assigned-vlan's>)".
        $l_conn_title_suffix_a = $l_chassis_cabling_dao->get_log_port_title_formatted($l_log_port_a, true);
        $l_conn_title_suffix_b = $l_chassis_cabling_dao->get_log_port_title_formatted($l_log_port_b, true);

        return [
            [
                'cat_id'     => $l_log_port_a,
                'obj_id'     => $p_obj_id,
                'title'      => $l_conn_title_suffix_a,
                'conn_title' => $l_obj_b . isys_tenantsettings::get('gui.separator.connector', ' > ') . $l_conn_title_suffix_b
            ],
            [
                'cat_id'     => $l_log_port_b,
                'obj_id'     => $p_dest_obj_id,
                'title'      => $l_conn_title_suffix_b,
                'conn_title' => $l_obj_a . isys_tenantsettings::get('gui.separator.connector', ' > ') . $l_conn_title_suffix_a
            ]
        ];
    }

    protected function remove_log_port_from_device($p_log_port_id)
    {
        try {
            $l_dao = isys_cmdb_dao_category_g_network_ifacel::instance($this->m_database_component);
            $l_row = $l_dao->get_data($p_log_port_id)
                ->get_row();

            $l_removed = [$p_log_port_id];

            if ($l_row['isys_catg_log_port_list__isys_catg_log_port_list__id'] > 0) {
                $l_removed[] = $l_row['isys_catg_log_port_list__isys_catg_log_port_list__id'];
                $l_dao->delete($l_row['isys_catg_log_port_list__isys_catg_log_port_list__id']);
            }

            $l_dao->delete($p_log_port_id);

            return [
                'success' => true,
                'removed' => $l_removed
            ];
        } catch (isys_exception_dao_cmdb $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Method for positioning a slot.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_slot_id
     * @param   integer $p_x1
     * @param   integer $p_x2
     * @param   integer $p_y1
     * @param   integer $p_y2
     * @param   integer $p_insertion
     *
     * @return  array
     */
    protected function assign_slot_position($p_obj_id, $p_slot_id, $p_x1, $p_x2, $p_y1, $p_y2, $p_insertion)
    {
        $l_dao = isys_cmdb_dao_category_s_chassis_slot::instance($this->m_database_component);
        $l_chassis_view_dao = isys_cmdb_dao_category_s_chassis_view::instance($this->m_database_component);

        // First we bring the X and Y values in the right order (from small to big).
        $l_x_from = ($p_x1 > $p_x2) ? $p_x2 : $p_x1;
        $l_x_to = ($p_x1 > $p_x2) ? $p_x1 : $p_x2;
        $l_y_from = ($p_y1 > $p_y2) ? $p_y2 : $p_y1;
        $l_y_to = ($p_y1 > $p_y2) ? $p_y1 : $p_y2;

        // Then we check if the new assignment would cross any other assignments.
        $l_collision = $l_dao->check_for_colliding_slots($p_obj_id, $p_x1, $p_x2, $p_y1, $p_y2, $p_insertion);

        if ($l_collision === false) {
            return [
                'success' => $l_dao->assign_chassis_slot_position($p_slot_id, $l_x_from, $l_x_to, $l_y_from, $l_y_to),
                'message' => isys_application::instance()->container->get('language')
                    ->get('LC_UNIVERSAL__SAVED'),
                'matrix'  => $l_chassis_view_dao->get_chassis_matrix($p_obj_id),
                'devices' => [
                    'front' => $l_chassis_view_dao->process_matrix_devices($p_obj_id, C__INSERTION__FRONT),
                    'rear'  => $l_chassis_view_dao->process_matrix_devices($p_obj_id, C__INSERTION__REAR)
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__CATS__CHASSIS_SLOTS__ALREADY_ASSIGNED')
            ];
        }
    }

    /**
     * Method for removing a slot position.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_slot_id
     *
     * @return  array
     */
    protected function remove_slot_position($p_obj_id, $p_slot_id)
    {
        $l_chassis_view_dao = isys_cmdb_dao_category_s_chassis_view::instance($this->m_database_component);

        return [
            'success' => isys_cmdb_dao_category_s_chassis_slot::instance($this->m_database_component)
                ->remove_assigned_slot_position($p_slot_id),
            'message' => isys_application::instance()->container->get('language')
                ->get('LC_UNIVERSAL__SAVED'),
            'matrix'  => $l_chassis_view_dao->get_chassis_matrix($p_obj_id),
            'devices' => [
                'front' => $l_chassis_view_dao->process_matrix_devices($p_obj_id, C__INSERTION__FRONT),
                'rear'  => $l_chassis_view_dao->process_matrix_devices($p_obj_id, C__INSERTION__REAR)
            ]
        ];
    }
}
