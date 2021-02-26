<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_connector extends isys_ajax_handler
{
    /**
     * Init
     *
     * @throws isys_exception_database
     * @throws isys_exception_general
     * @throws isys_exception_missing_function
     */
    public function init()
    {
        $l_method = $_GET['method'];

        switch ($l_method) {
            case 'get_fiber_lead':
                $l_return = $this->get_fiber_lead((int)$_POST['cable_object_id'], (int)$_POST['connector_id']);
                break;

            case 'load_listeners':
                $l_return = $this->load_listeners((int)$_POST['id']);
                break;

            case 'detachConnector':
                $l_return = $this->detach_connector(array_filter(explode(',', $_POST['connector'])));
                break;

            case 'connectConnectors':
                $l_return = $this->attachConnectors(array_filter(explode(',', $_POST['a'])), array_filter(explode(',', $_POST['b'])));
                break;

            default:
                throw new isys_exception_missing_function(sprintf('unknown method "%s"', $l_method));
        }

        header('Content-Type: application/json');
        echo isys_format_json::encode($l_return);
        $this->_die();
    }

    /**
     * @param   integer $p_cable_object_id
     * @param   integer $p_connector_id
     *
     * @return  array
     * @throws  Exception
     * @throws  isys_exception_database
     * @throws  isys_exception_general
     */
    protected function get_fiber_lead($p_cable_object_id, $p_connector_id)
    {
        $l_dao = isys_cmdb_dao_category_g_fiber_lead::instance($this->m_database_component);

        $l_fibers_leads = $l_dao->get_data_by_object($p_cable_object_id)
            ->__as_array();

        $l_sql = 'SELECT isys_catg_connector_list__id, isys_catg_connector_list__used_fiber_lead_rx, isys_catg_connector_list__used_fiber_lead_tx
            FROM isys_catg_connector_list
            INNER JOIN isys_catg_fiber_lead_list AS rx ON rx.isys_catg_fiber_lead_list__id = isys_catg_connector_list__used_fiber_lead_rx
            INNER JOIN isys_catg_fiber_lead_list AS tx ON tx.isys_catg_fiber_lead_list__id = isys_catg_connector_list__used_fiber_lead_tx
            WHERE isys_catg_connector_list__id = ' . $l_dao->convert_sql_id($p_connector_id) . '
            AND (rx.isys_catg_fiber_lead_list__isys_obj__id = ' . $l_dao->convert_sql_id($p_cable_object_id) . ' OR
                tx.isys_catg_fiber_lead_list__isys_obj__id = ' . $l_dao->convert_sql_id($p_cable_object_id) . ');';

        $l_used_fibers_leads = $l_dao->retrieve($l_sql)
            ->__as_array();

        $l_options = [];

        foreach ($l_fibers_leads as $l_fiber_lead) {
            $l_option = [
                'isys_catg_fiber_lead_list__id'    => $l_fiber_lead['isys_catg_fiber_lead_list__id'],
                'isys_catg_fiber_lead_list__label' => $l_fiber_lead['isys_catg_fiber_lead_list__label'],
                'isys_fiber_category__title'       => $l_fiber_lead['isys_fiber_category__title'],
                'isys_cable_colour__title'         => $l_fiber_lead['isys_cable_colour__title'],
                'disabled'                         => false
            ];

            foreach ($l_used_fibers_leads as $l_used_fiber_lead) {
                if ($l_fiber_lead['isys_catg_fiber_lead__id'] === $l_used_fiber_lead['isys_catg_connector_list__used_fiber_lead_rx'] ||
                    $l_fiber_lead['isys_catg_fiber_lead__id'] === $l_used_fiber_lead['isys_catg_connector_list__used_fiber_lead_rx']) {
                    $l_option['disabled'] = true;
                    break;
                }
            }

            $l_options[] = $l_option;
        }

        return $l_options;
    }

    /**
     * Method which retrieves all listeners of the selected object
     *
     * @param $p_obj_id
     *
     * @return array
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function load_listeners($p_obj_id)
    {
        $l_dao = isys_cmdb_dao_category_g_net_listener::instance(isys_application::instance()->container->get('database'));
        $l_res = $l_dao->get_data(null, $p_obj_id);
        $l_return = [];

        if ($l_res->num_rows()) {
            while ($l_row = $l_res->get_row()) {
                $l_return[$l_row['isys_catg_net_listener_list__id']] = $l_row['isys_net_protocol__title'] . '/' . $l_row['isys_cats_net_ip_addresses_list__title'] . ':' .
                    $l_row['isys_catg_net_listener_list__port_from'] . ' | ' . $l_row['isys_obj__title'];
            }
        }

        return $l_return;
    }

    /**
     * Method for detaching a connector.
     *
     * @param array $p_connectors
     *
     * @return array
     */
    protected function detach_connector(array $p_connectors)
    {
        $l_return = ['success' => true, 'data' => [], 'message' => ''];

        try {
            $l_dao = isys_cmdb_dao_cable_connection::instance(isys_application::instance()->container->get('database'));

            foreach ($p_connectors as $l_connector) {
                $l_connection_id = $l_dao->get_cable_connection_id_by_connector_id((int)$l_connector);

                if (isys_tenantsettings::get('cmdb.cable.change-cmdb-status-on-detach', 1)) {
                    $l_cable_object_id = $l_dao->get_cable_object_id_by_connection_id($l_connection_id);

                    $l_dao->set_object_cmdb_status($l_cable_object_id, defined_or_default('C__CMDB_STATUS__INOPERATIVE'));
                }

                $l_return['data'][] = $l_dao->delete_cable_connection($l_connection_id);
            }
        } catch (Exception $e) {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        }

        return $l_return;
    }

    /**
     * Method for connecting two connectors with each other.
     *
     * @param array $connectorsA
     * @param array $connectorsB
     *
     * @return array
     */
    protected function attachConnectors(array $connectorsA, array $connectorsB)
    {
        $return = ['success' => true, 'data' => [], 'message' => ''];
        $counter = count($connectorsA);

        try {
            if (empty($connectorsA) || empty($connectorsB)) {
                throw new isys_exception_general('At least two connectors needed to connect!');
            }

            $daoCableConnection = isys_cmdb_dao_cable_connection::instance(isys_application::instance()->container->get('database'));
            $daoConnector = isys_cmdb_dao_category_g_connector::instance(isys_application::instance()->container->get('database'));

            for ($i = 0; $i < $counter; $i++) {
                $cableId = 0;

                // @see ID-5761  Check if a cable is documented and re-use it.
                $connectionId = $daoCableConnection->get_cable_connection_id_by_connector_id((int) $connectorsA[$i]);

                if ($connectionId) {
                    $cableId = $daoCableConnection->get_cable_connection($connectionId)->get_row_value('isys_cable_connection__isys_obj__id');
                }

                if (!$cableId) {
                    $connectionId = $daoCableConnection->get_cable_connection_id_by_connector_id((int) $connectorsB[$i]);

                    if ($connectionId) {
                        $cableId = $daoCableConnection->get_cable_connection($connectionId)->get_row_value('isys_cable_connection__isys_obj__id');
                    }
                }

                // Remove the current cable connection of the local and destination controller.
                $this->detach_connector([$connectorsA[$i], $connectorsB[$i]]);

                if (!$cableId) {
                    $cableId = isys_cmdb_dao_cable_connection::recycle_cable();
                }

                $cableConnectionId = $daoCableConnection->add_cable_connection($cableId);

                if ($daoCableConnection->save_connection($connectorsA[$i], $connectorsB[$i], $cableConnectionId, $connectorsA[$i])) {
                    if (isys_tenantsettings::get('cmdb.cable.change-cmdb-status-on-attach', 1)) {
                        $daoCableConnection->set_object_cmdb_status($cableId, defined_or_default('C__CMDB_STATUS__IN_OPERATION'));
                    }

                    $destinationData = $daoConnector
                        ->get_data($connectorsB[$i])
                        ->get_row();

                    $return['data'][] = [
                        'objId'      => $destinationData['isys_obj__id'],
                        'objTitle'   => $destinationData['isys_obj__title'],
                        'connId'     => $connectorsB[$i],
                        'connTitle'  => $destinationData['isys_catg_connector_list__title'],
                        'cableId'    => $destinationData['cable_id'],
                        'cableTitle' => $destinationData['cable_title']
                    ];
                } else {
                    $return['data'][] = false;
                }
            }
        } catch (Exception $e) {
            $return['success'] = false;
            $return['message'] = $e->getMessage();
        }

        return $return;
    }
}
