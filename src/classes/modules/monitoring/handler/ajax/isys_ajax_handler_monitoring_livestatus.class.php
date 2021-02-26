<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.3.0
 */
class isys_ajax_handler_monitoring_livestatus extends isys_ajax_handler
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

        $l_return = [
            'success' => true,
            'message' => null,
            'data'    => null
        ];

        try {
            switch ($_GET['func']) {
                case 'query_livestatus':
                    $l_return['data'] = $this->query_livestatus($_POST['host_id'], isys_format_json::decode($_POST['query']));
                    break;

                case 'load_livestatus':
                    $l_return['data'] = $this->load_livestatus(isys_format_json::decode($_POST['obj_ids']), isys_format_json::decode($_POST['columns']));
                    break;

                case 'load_livestatus_state':
                    // @see ID-2962
                    isys_core::expire(-60);

                    $l_return['data'] = current($this->load_livestatus_states([$_POST[C__CMDB__GET__OBJECT]]));
                    break;

                case 'load_livestatus_states':
                    // @see ID-2962
                    isys_core::expire(-60);
                    $l_return['data'] = $this->load_livestatus_states(isys_format_json::decode($_POST['obj_ids']) ?: []);
                    break;
            }
        } catch (Exception $e) {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
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
     * Method for querying the livestatus API.
     *
     * @param   integer $p_host
     * @param   array   $p_query
     * @param   boolean $p_force
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function query_livestatus($p_host, array $p_query, $p_force = false)
    {
        return isys_monitoring_livestatus::factory($p_host)
            ->query($p_query, $p_force);
    }

    /**
     * This method will retrieve the "livestatus" data of a given host in realtime.
     *
     * @param   mixed $p_obj_id  This may be a integer or a array of integers.
     * @param   mixed $p_columns This may be empty, a string or a array of strings.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function load_livestatus($p_obj_id, $p_columns = null)
    {
        $l_columns = null;

        if ($p_columns !== null) {
            if (!is_array($p_columns)) {
                $p_columns = [$p_columns];
            }

            $l_columns = 'Columns: ' . implode(' ', $p_columns);
        }

        if (is_array($p_obj_id)) {
            $l_return = [];

            foreach ($p_obj_id as $l_obj_id) {
                try {
                    $l_return[] = [
                        'success' => true,
                        'message' => null,
                        'obj_id'  => $l_obj_id,
                        'data'    => $this->query_livestatus(isys_cmdb_dao_category_g_monitoring::instance($this->m_database_component)
                            ->get_data(null, $l_obj_id)
                            ->get_row_value('isys_catg_monitoring_list__isys_monitoring_hosts__id'), [
                            'GET hosts',
                            'Filter: host_name = ' . isys_monitoring_helper::render_export_hostname($l_obj_id),
                            $l_columns
                        ])
                    ];
                } catch (Exception $e) {
                    $l_return[] = [
                        'success' => false,
                        'message' => $e->getMessage(),
                        'obj_id'  => $l_obj_id,
                        'data'    => null
                    ];
                }
            }
        } else {
            $l_return = $this->query_livestatus(isys_cmdb_dao_category_g_monitoring::instance($this->m_database_component)
                ->get_data(null, $p_obj_id)
                ->get_row_value('isys_catg_monitoring_list__isys_monitoring_hosts__id'), [
                'GET hosts',
                'Filter: host_name = ' . isys_monitoring_helper::render_export_hostname($p_obj_id),
                $l_columns
            ]);
        }

        return $l_return;
    }

    /**
     * This method will retrieve the "livestatus" data of a given host in realtime.
     *
     * @param   array $p_obj_ids
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function load_livestatus_states(array $p_obj_ids)
    {
        $p_obj_ids = array_filter($p_obj_ids);
        $l_states = isys_monitoring_helper::get_state_info();
        $l_host_states = isys_monitoring_helper::get_host_state_info();
        $l_return = $l_hosts = $l_matcher = [];

        if (count($p_obj_ids) > 0) {
            foreach ($p_obj_ids as $l_obj_id) {
                // Get the host name of the given objects.
                $l_hostname = isys_monitoring_helper::render_export_hostname($l_obj_id);
                $l_host = isys_cmdb_dao_category_g_monitoring::instance($this->m_database_component)
                    ->get_data(null, $l_obj_id)
                    ->get_row();

                if (is_array($l_host) && !empty($l_hostname)) {
                    $l_matcher[$l_hostname] = $l_obj_id;

                    if (empty($l_host['isys_catg_monitoring_list__isys_monitoring_hosts__id']) || $l_host['isys_monitoring_hosts__type'] != C__MONITORING__TYPE_LIVESTATUS ||
                        $l_host['isys_monitoring_hosts__active'] != 1) {
                        continue;
                    }

                    $l_hosts[$l_host['isys_catg_monitoring_list__isys_monitoring_hosts__id']][] = 'Filter: host_name = ' . $l_hostname;
                }
            }
        }

        // This awkward looping is necessary because (in theory) every host can have its own host.
        foreach ($l_hosts as $l_host => $l_filters) {
            $l_query = ['GET hosts'];

            foreach ($l_filters as $l_filter) {
                $l_query[] = $l_filter;
            }

            if (count($l_filters) > 1) {
                $l_query[] = 'Or: ' . count($l_filters);
            }

            $l_query[] = 'Columns: state host_name';

            try {
                $l_result = $this->query_livestatus($l_host, $l_query, $p_force);

                if (count($l_result)) {
                    foreach ($l_result as $l_item) {
                        $l_return[] = [
                            'obj_id'     => $l_matcher[$l_item[1]],
                            'hostname'   => $l_item[1],
                            'state'      => $l_states[$l_item[0]],
                            'host_state' => $l_host_states[$l_item[0]]
                        ];
                    }
                } else {
                    $l_return[] = false;
                }
            } catch (isys_exception $e) {

            }
        }

        return $l_return;
    }
}