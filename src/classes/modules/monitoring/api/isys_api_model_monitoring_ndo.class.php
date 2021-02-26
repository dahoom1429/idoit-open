<?php

/**
 * i-doit
 *
 * API model for monitoring NDO
 *
 * @package    i-doit
 * @subpackage API
 * @author     Leonard Fischer <lfischer@i-doit.com>
 * @copyright  synetics GmbH
 * @since      1.10.2
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_api_model_monitoring_ndo extends isys_api_model_monitoring implements isys_api_model_interface
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
        'isys_monitoring_hosts__id'       => 'id',
        'isys_monitoring_hosts__title'    => 'title',
        'isys_monitoring_hosts__active'   => 'active',
        'isys_monitoring_hosts__dbname'   => 'database',
        'isys_monitoring_hosts__dbprefix' => 'prefix',
        'isys_monitoring_hosts__username' => 'username',
        'isys_monitoring_hosts__password' => 'password'
    ];

    /**
     * isys_api_model_monitoring_ndo constructor.
     *
     * @param isys_component_database $db
     */
    public function __construct(isys_component_database $db)
    {
        $this->db = $db;
        $this->dao = isys_monitoring_dao_hosts::instance($this->db);
    }

    /**
     * Read NDO hosts.
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

        $result = $this->dao->get_data($id, C__MONITORING__TYPE_NDO, false, $title);

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
     */
    public function create($parameters)
    {
        if (! isset($parameters['data']['title']) || empty($parameters['data']['title'])) {
            throw new isys_exception_api_validation('Mandatory parameter "title" not found in your request.', isys_api_controller_jsonrpc::ERR_Parameters);
        }

        if (! isset($parameters['data']['database']) || empty($parameters['data']['database'])) {
            throw new isys_exception_api_validation('Mandatory parameter "database" not found in your request.', isys_api_controller_jsonrpc::ERR_Parameters);
        }

        if (! isset($parameters['data']['username']) || empty($parameters['data']['username'])) {
            throw new isys_exception_api_validation('Mandatory parameter "username" not found in your request.', isys_api_controller_jsonrpc::ERR_Parameters);
        }

        if (! isset($parameters['data']['password']) || empty($parameters['data']['password'])) {
            throw new isys_exception_api_validation('Mandatory parameter "password" not found in your request.', isys_api_controller_jsonrpc::ERR_Parameters);
        }

        $parameters['id'] = null;

        unset($parameters['data']['id']);

        if (!isset($parameters['data']['active'])) {
            $parameters['data']['active'] = true;
        }

        $return = $this->update($parameters);

        if ($return['id'] > 0) {
            $return['message'] = 'NDO host successfully created.';
        } else {
            $return['message'] = 'NDO host was not created.';
        }

        return $return;
    }

    /**
     * @param $parameters
     *
     * @return array
     * @throws isys_exception_dao
     */
    public function update($parameters)
    {
        $data = [
            'type' => C__MONITORING__TYPE_NDO
        ];

        if (isset($parameters['data']['title'])) {
            $data['title'] = trim($parameters['data']['title']);
        }

        if (isset($parameters['data']['active'])) {
            $data['active'] = (bool)$parameters['data']['active'];
        }

        if (isset($parameters['data']['database'])) {
            $data['database'] = trim($parameters['data']['database']);
        }

        if (isset($parameters['data']['prefix'])) {
            $data['prefix'] = trim($parameters['data']['prefix']);
        }

        if (isset($parameters['data']['username'])) {
            $data['username'] = $parameters['data']['username'];
        }

        if (isset($parameters['data']['password'])) {
            $data['password'] = $parameters['data']['password'];
        }

        $id = $this->dao->save($parameters['id'], $data, C__MONITORING__TYPE_NDO);

        return [
            'id'      => $id,
            'message' => ($id > 0 ? 'NDO host successfully updated.' : 'NDO host was not updated'),
            'success' => ($id > 0)
        ];
    }

    /**
     * Delete one or multiple NDO hosts.
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

        if ($this->dao->delete($id, $title, C__MONITORING__TYPE_NDO)) {
            return [
                'success' => true,
                'message' => 'NDO host(s) has been deleted.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'NDO host(s) has not been deleted.'
            ];
        }
    }
}
