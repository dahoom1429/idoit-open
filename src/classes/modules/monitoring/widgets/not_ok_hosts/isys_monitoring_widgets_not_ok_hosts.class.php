<?php

/**
 * i-doit
 *
 * Dashboard widget class
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.4.3
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_monitoring_widgets_not_ok_hosts extends isys_dashboard_widgets
{
    /**
     * Path and Filename of the configuration template.
     *
     * @var string
     */
    protected $m_config_tpl_file = '';

    /**
     * Path and Filename of the template.
     *
     * @var string
     */
    protected $m_tpl_file = '';

    /**
     * Returns a boolean value, if the current widget has an own configuration page.
     *
     * @return boolean
     */
    public function has_configuration()
    {
        return true;
    }

    /**
     * Init method.
     *
     * @param array $p_config
     *
     * @return isys_monitoring_widgets_not_ok_hosts
     * @throws Exception
     */
    public function init($p_config = [])
    {
        $this->m_tpl_file = __DIR__ . '/templates/widget.tpl';
        $this->m_config_tpl_file = __DIR__ . '/templates/config.tpl';

        parent::init($p_config);

        return $this;
    }

    /**
     * Method for loading the widget configuration.
     *
     * @param array $p_row The current widget row from "isys_widgets".
     * @param int   $p_id  The ID from "isys_widgets_config".
     *
     * @return string
     * @throws SmartyException
     * @throws isys_exception_database
     */
    public function load_configuration(array $p_row, $p_id)
    {
        $l_host_id = (isset($this->m_config['host']) && $this->m_config['host'] > 0) ? $this->m_config['host'] : 0;
        $l_hosts = [];
        $l_host_res = isys_monitoring_dao_hosts::instance($this->database)
            ->get_data(null, null, true);

        if (count($l_host_res) > 0) {
            while ($l_row = $l_host_res->get_row()) {
                $l_hosts[$l_row['isys_monitoring_hosts__id']] = $l_row['isys_monitoring_hosts__title'] . ' (' . $l_row['isys_monitoring_hosts__type'] . ')';
            }
        }

        $l_rules = [
            'hosts'         => serialize($l_hosts),
            'selected_host' => $l_host_id
        ];

        return $this->m_tpl->activate_editmode()
            ->assign('title', $this->language->get('LC__MONITORING__WIDGET__NOT_OK_HOSTS__HOST_SELECTION'))
            ->assign('rules', $l_rules)
            ->fetch($this->m_config_tpl_file);
    }

    /**
     * Render method.
     *
     * @param string $p_unique_id
     *
     * @return string
     * @throws isys_exception_general
     * @throws SmartyException
     * @throws isys_exception_database
     */
    public function render($p_unique_id)
    {
        $l_monitoring_dao = new isys_monitoring_dao_hosts($this->database);
        $l_host_id = (isset($this->m_config['host']) && $this->m_config['host'] > 0) ? $this->m_config['host'] : 0;
        $l_host = $l_monitoring_dao->get_data($l_host_id, null, true)
            ->get_row();
        $l_objects = [];
        $l_error = false;

        if ($l_host_id > 0 && is_array($l_host)) {
            try {
                if ($l_host['isys_monitoring_hosts__type'] === C__MONITORING__TYPE_LIVESTATUS) {
                    $l_objects = $this->get_livestatus_objects((int)$l_host_id);
                } elseif ($l_host['isys_monitoring_hosts__type'] === C__MONITORING__TYPE_NDO) {
                    $l_objects = $this->get_ndo_hosts((int)$l_host_id);
                } else {
                    $l_error = $this->language->get('LC__MONITORING__WIDGET__NOT_OK_HOSTS__UNSUPPORTED_HOST');
                }
            } catch (Exception $e) {
                $l_error = $e->getMessage();
            }
        } else {
            $l_error = $this->language->get('LC__MONITORING__WIDGET__NOT_OK_HOSTS__PLEASE_SELECT_HOST');
        }

        return $this->m_tpl->assign('unique_id', $p_unique_id)
            ->assign('title', $this->language->get('LC__MONITORING__WIDGET__NOT_OK_HOSTS'))
            ->assign('error', $l_error)
            ->assign('hosts', $l_objects['hosts'])
            ->assign('services', $l_objects['services'])
            ->fetch($this->m_tpl_file);
    }

    /**
     * Method for retrieving livestatus hosts and services with the status != OK.
     *
     * @param int $hostId
     *
     * @return array
     * @throws isys_exception_general
     */
    private function get_livestatus_objects(int $hostId): array
    {
        $l_objects = [
            'hosts'    => [],
            'services' => []
        ];

        $l_host_states = isys_monitoring_helper::get_host_state_info();
        $l_service_states = isys_monitoring_helper::get_state_info();
        $l_host_service_dao = false;

        if (class_exists('isys_cmdb_dao_category_g_cmk_host_service')) {
            $l_host_service_dao = isys_cmdb_dao_category_g_cmk_host_service::instance($this->database);
        }

        $l_app_dao = isys_cmdb_dao_category_g_application::instance($this->database);

        // Retrieving the livestatus connector and data...
        $l_livestatus = isys_monitoring_livestatus::factory($hostId);

        $l_not_ok_hosts = $l_livestatus->query([
            'GET hosts',
            'Filter: state > 0',
            'Columns: name plugin_output state'
        ], true);

        $l_not_ok_services = $l_livestatus->disconnect()
            ->connect()
            ->query([
                'GET services',
                'Filter: state > 0',
                'Columns: host_name description plugin_output state'
            ], true);

        foreach ($l_not_ok_hosts as $l_host) {
            $l_object = isys_monitoring_helper::get_objects_by_hostname($hostId, $l_host[0]);

            if ($l_object['isys_obj__status'] != C__RECORD_STATUS__NORMAL) {
                continue;
            }

            $l_objects['hosts'][$l_object['isys_obj__id']] = [
                'obj_id'         => $l_object['isys_obj__id'],
                'obj_title'      => $l_object['isys_obj__title'],
                'obj_type_id'    => $l_object['isys_obj_type__id'],
                'obj_type_title' => $this->language->get($l_object['isys_obj_type__title']),
                'state'          => $l_host_states[$l_host[2]],
                'state_info'     => $l_host[1]
            ];
        }

        if ($l_host_service_dao !== false) {
            foreach ($l_not_ok_services as $l_service) {
                $l_host = $l_host_service_dao->get_objects_by_inherited_service($l_service[1], $l_service[0]);

                // Get the objects, which "inherit" this service.
                if (count($l_host)) {
                    $l_host = current($l_host);

                    if (!isset($l_objects['services'][$l_host['isys_obj__id']])) {
                        $l_state = $l_livestatus->disconnect()
                            ->connect()
                            ->query([
                                'GET hosts',
                                'Filter: host_name = ' . $l_service[0],
                                'Columns: state'
                            ]);

                        $l_objects['services'][$l_host['isys_obj__id']] = [
                            'obj_id'            => $l_host['isys_obj__id'],
                            'obj_title'         => $l_host['isys_obj__title'],
                            'obj_type_id'       => $l_host['isys_obj_type__id'],
                            'obj_type_title'    => $this->language->get($l_host['isys_obj_type__title']),
                            'hostname'          => $l_service[0],
                            'state'             => $l_host_states[$l_state[0][0]],
                            'host_service'      => [],
                            'inherited_service' => [],
                        ];
                    }

                    $l_app = $l_app_dao->get_data($l_host['isys_catg_cmk_host_service_list__application__id'], null, '', null, C__RECORD_STATUS__NORMAL)
                        ->get_row();

                    $l_objects['services'][$l_host['isys_obj__id']]['inherited_service'][] = [
                        'app_id'         => $l_app['isys_obj__id'],
                        'app_title'      => $l_app['isys_obj__title'],
                        'app_type_id'    => $l_app['isys_obj_type__id'],
                        'app_type_title' => $this->language->get($l_app['isys_obj_type__title']),
                        'service'        => $l_service[1],
                        'state'          => $l_service_states[$l_service[3]],
                        'state_info'     => $l_service[2]
                    ];
                }

                $l_host = $l_host_service_dao->get_object_by_service($l_service[1], $l_service[0]);

                if (count($l_host)) {
                    $l_host = current($l_host);

                    if (!isset($l_objects['services'][$l_host['isys_obj__id']])) {
                        $l_state = $l_livestatus->disconnect()
                            ->connect()
                            ->query([
                                'GET hosts',
                                'Filter: host_name = ' . $l_service[0],
                                'Columns: state'
                            ]);

                        $l_objects['services'][$l_host['isys_obj__id']] = [
                            'obj_id'            => $l_host['isys_obj__id'],
                            'obj_title'         => $l_host['isys_obj__title'],
                            'obj_type_id'       => $l_host['isys_obj_type__id'],
                            'obj_type_title'    => $this->language->get($l_host['isys_obj_type__title']),
                            'state'             => $l_host_states[$l_state[0][0]],
                            'state_info'        => '',
                            'host_service'      => [],
                            'inherited_service' => [],
                        ];
                    }

                    $l_app = $l_app_dao->get_data($l_host['isys_catg_cmk_host_service_list__application__id'], null, '', null, C__RECORD_STATUS__NORMAL)
                        ->get_row();

                    $l_objects['services'][$l_host['isys_obj__id']]['host_service'][] = [
                        'app_id'         => $l_app['isys_obj__id'],
                        'app_title'      => $l_app['isys_obj__title'],
                        'app_type_id'    => $l_app['isys_obj_type__id'],
                        'app_type_title' => $this->language->get($l_app['isys_obj_type__title']),
                        'service'        => $l_service[1],
                        'state'          => $l_service_states[$l_service[3]],
                        'state_info'     => $l_service[2]
                    ];
                }
            }
        }

        return $l_objects;
    }

    /**
     * Method for retrieving the "not OK" nagios hosts by the given monitoring host.
     *
     * @param int $hostId
     *
     * @return array
     * @throws isys_exception_general
     */
    private function get_ndo_hosts(int $hostId): array
    {
        $l_objects = [
            'hosts'    => [],
            'services' => false
        ];

        $l_host_states = isys_monitoring_helper::get_host_state_info();

        // Retrieving the NDO connector.
        $l_ndo = isys_monitoring_ndo::factory($hostId);

        $l_res = $l_ndo->get_ndo_dao()
            ->get_not_ok_hosts();

        if (count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                $l_object = isys_monitoring_helper::get_objects_by_hostname($hostId, $l_row['hostname']);

                if (empty($l_object) || $l_object['isys_obj__status'] != C__RECORD_STATUS__NORMAL) {
                    continue;
                }

                $l_objects['hosts'][$l_object['isys_obj__id']] = [
                    'obj_id'         => $l_object['isys_obj__id'],
                    'obj_title'      => $l_object['isys_obj__title'],
                    'obj_type_id'    => $l_object['isys_obj_type__id'],
                    'obj_type_title' => $this->language->get($l_object['isys_obj_type__title']),
                    'state'          => $l_host_states[$l_row['state']],
                    'state_info'     => $l_row['state_info']
                ];
            }
        }

        return $l_objects;
    }
}
