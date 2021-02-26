<?php

/**
 * i-doit
 *
 * API model for monitoring Livestatus
 *
 * @package    i-doit
 * @subpackage API
 * @author     Leonard Fischer <lfischer@i-doit.com>
 * @copyright  synetics GmbH
 * @since      1.10.2
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_api_model_monitoring_livestatus extends isys_api_model_monitoring implements isys_api_model_interface
{
    /**
     * @var isys_component_database
     */
    private $db;

    /**
     * @var isys_monitoring_dao_hosts
     */
    private $dao;

    /**
     * Validation.
     *
     * @var  array
     */
    protected $m_validation = [
        'read'   => [],
        'create' => [
            'data'
        ],
        'update' => [
            'id',
            'data'
        ],
        'delete' => []
    ];

    /**
     * Data formatting used in format methods.
     *
     * @var  array
     */
    protected $mapping = [
        'isys_monitoring_hosts__id'         => 'id',
        'isys_monitoring_hosts__title'      => 'title',
        'isys_monitoring_hosts__active'     => 'active',
        'isys_monitoring_hosts__connection' => 'connection',
        'isys_monitoring_hosts__address'    => 'address',
        'isys_monitoring_hosts__port'       => 'port',
        'isys_monitoring_hosts__path'       => 'path'
    ];

    /**
     * isys_api_model_monitoring_livestatus constructor.
     *
     * @param isys_component_database $db
     */
    public function __construct(isys_component_database $db)
    {
        $this->db = $db;
        $this->dao = isys_monitoring_dao_hosts::instance($this->db);
    }

    /**
     * Read Livestatus hosts.
     *
     * @param  array $parameters
     *
     * @return array
     * @throws isys_exception_database
     */
    public function read($parameters)
    {
        $id = null;
        $title = null;
        $return = [];

        if (isset($parameters['id'])) {
            $id = (int)$parameters['id'];
        } else if (isset($parameters['ids']) && is_array($parameters['ids'])) {
            $id = array_filter($parameters['ids'], function ($id) {
                return is_numeric($id) && $id > 0;
            });
        }

        if (isset($parameters['title']) && !empty($parameters['title'])) {
            $title = $parameters['title'];
        }

        $result = $this->dao->get_data($id, C__MONITORING__TYPE_LIVESTATUS, false, $title);

        while ($row = $result->get_row()) {
            $return[] = $this->format_by_mapping($this->mapping, $row);
        }

        return $return;
    }

    /**
     * @param  array $parameters
     *
     * @return array
     * @throws isys_exception_api_validation
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function create($parameters)
    {
        if (! isset($parameters['data']['title']) || empty($parameters['data']['title'])) {
            throw new isys_exception_api_validation('Mandatory parameter "title" not found in your request.', isys_api_controller_jsonrpc::ERR_Parameters);
        }

        if (! isset($parameters['data']['connection']) || empty($parameters['data']['connection'])) {
            throw new isys_exception_api_validation('Mandatory parameter "connection" not found in your request.', isys_api_controller_jsonrpc::ERR_Parameters);
        }

        $parameters['id'] = null;

        unset($parameters['data']['id']);

        if (!isset($parameters['data']['active'])) {
            $parameters['data']['active'] = true;
        }

        if (!isset($parameters['data']['port'])) {
            $parameters['data']['port'] = 6557;
        }

        $return = $this->update($parameters);

        if ($return['id'] > 0) {
            $return['message'] = 'Livestatus host successfully created.';
        } else {
            $return['message'] = 'Livestatus host was not created.';
        }

        return $return;
    }

    /**
     * @param $parameters
     *
     * @return array
     * @throws isys_exception_api_validation
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function update($parameters)
    {
        $data = [
            'type' => C__MONITORING__TYPE_LIVESTATUS
        ];

        unset($parameters['data']['id']);

        if (isset($parameters['id']) && is_numeric($parameters['id']) && $parameters['id'] > 0) {
            if ($this->dao->get_data($parameters['id'], C__MONITORING__TYPE_LIVESTATUS)->count() === 0) {
                throw new isys_exception_api_validation('The given ID does not exist.', isys_api_controller_jsonrpc::ERR_Parameters);
            }
        }

        if (isset($parameters['data']['title']))
        {
            $data['title'] = trim($parameters['data']['title']);
        }

        if (isset($parameters['data']['active']))
        {
            $data['active'] = (bool) $parameters['data']['active'];
        }

        if (isset($parameters['data']['connection']))
        {
            $parameters['data']['connection'] = strtolower(trim($parameters['data']['connection']));

            if ($parameters['data']['connection'] !== 'tcp' && $parameters['data']['connection'] !== 'unix') {
                throw new isys_exception_api_validation('The given Livestatus connection "' . $parameters['data']['connection'] . '" is not valid. Please provide "unix", "unix socket" or "tcp".', isys_api_controller_jsonrpc::ERR_Parameters);
            }

            $data['connection'] = $parameters['data']['connection'];
        }

        if (isset($parameters['data']['address']))
        {
            $data['address'] = $parameters['data']['address'];
        }

        if (isset($parameters['data']['port']))
        {
            $data['port'] = (int) $parameters['data']['port'];
        }

        if (isset($parameters['data']['path']))
        {
            $data['path'] = $parameters['data']['path'];
        }

        $id = $this->dao->save($parameters['id'], $data, C__MONITORING__TYPE_LIVESTATUS);

        return [
            'id'      => $id,
            'message' => ($id > 0 ? 'Livestatus host successfully updated.' : 'Livestatus host was not updated'),
            'success' => ($id > 0)
        ];
    }

    /**
     * Delete one or multiple Livestatus hosts.
     *
     * @param  array $parameters
     *
     * @return array
     * @throws isys_exception_dao
     * @throws isys_exception_general
     */
    public function delete($parameters)
    {
        $id = null;
        $title = null;

        if (isset($parameters['id'])) {
            $id = (int)$parameters['id'];
        } else if (isset($parameters['ids']) && is_array($parameters['ids'])) {
            $id = array_filter($parameters['ids'], function ($id) {
                return is_numeric($id) && $id > 0;
            });
        }

        if (isset($parameters['title']) && !empty($parameters['title'])) {
            $title = $parameters['title'];
        }

        if ($this->dao->delete($id, $title, C__MONITORING__TYPE_LIVESTATUS)) {
            return [
                'success' => true,
                'message' => 'Livestatus host(s) has been deleted.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Livestatus host(s) has not been deleted.'
            ];
        }
    }
}
