<?php

/**
 * i-doit
 *
 * DAO for NDO data retrieval
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Dennis Bluemer <dbluemer@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_dao_ndo extends isys_component_dao
{
    /**
     * @var  string
     */
    private $m_ndo_prefix;

    /**
     * Get current_state and output from table nagios_hoststatus of given hostobject.
     *
     * @param    string $p_name1
     *
     * @return   isys_component_dao_result
     * @throws   isys_exception_database
     */
    public function getCurrentHostState($p_name1)
    {
        $l_query = "SELECT *
			FROM " . $this->m_ndo_prefix . "hoststatus
			NATURAL JOIN " . $this->m_ndo_prefix . "hosts
			INNER JOIN " . $this->m_ndo_prefix . "objects ON host_object_id = object_id
			WHERE name1 = " . $this->convert_sql_text($p_name1) . ";";

        $l_result = $this->retrieve($l_query);

        if (!$l_result) {
            throw new isys_exception_database($this->get_database_component()
                ->get_last_error_as_string());
        }

        return $l_result;
    }

    /**
     * Get current_state and output from table nagios_servicestatus of given hostobject and serviceobject.
     *
     * @param   string  $p_host_name
     * @param   integer $p_service_description
     *
     * @return  array
     * @throws  isys_exception_database
     */
    public function getCurrentServiceState($p_host_name, $p_service_description)
    {
        $l_query = "SELECT *
			FROM " . $this->m_ndo_prefix . "servicestatus
			NATURAL JOIN " . $this->m_ndo_prefix . "services
			INNER JOIN " . $this->m_ndo_prefix . "objects ON service_object_id = object_id
			WHERE name1='" . $p_host_name . "'
			AND name2='" . $p_service_description . "';";

        $l_result = $this->retrieve($l_query);

        if (!$l_result) {
            throw new isys_exception_database($this->get_database_component()
                ->get_last_error_as_string());
        }

        return $l_result->get_row();
    }

    /**
     *
     * @param   string $p_host_name
     *
     * @return  boolean
     * @throws  isys_exception_database
     */
    public function hostExists($p_host_name)
    {
        $l_result = $this->retrieve("SELECT object_id FROM " . $this->m_ndo_prefix . "objects WHERE objecttype_id = 1 AND name1 ='" . $p_host_name . "';");

        if (!$l_result) {
            throw new isys_exception_database($this->get_database_component()
                ->get_last_error_as_string());
        }

        return !empty($l_result);
    }

    /**
     *
     * @param   string $p_host_name
     * @param   string $p_service_description
     *
     * @return  boolean
     * @throws  isys_exception_database
     */
    public function serviceExists($p_host_name, $p_service_description)
    {
        $l_result = $this->retrieve("SELECT object_id FROM " . $this->m_ndo_prefix . "objects WHERE objecttype_id = 2 AND name1 ='" . $p_host_name . "' AND name2 ='" .
            $p_service_description . "';");

        if (!$l_result) {
            throw new isys_exception_database($this->get_database_component()
                ->get_last_error_as_string());
        }

        return !empty($l_result);
    }

    /**
     *
     * @param   string $p_host_name
     *
     * @return  array
     * @throws  isys_exception_database
     */
    public function getHostStateHistory($p_host_name)
    {
        $l_query = 'SELECT * FROM ' . $this->m_ndo_prefix . 'statehistory
			NATURAL JOIN ' . $this->m_ndo_prefix . 'objects
			WHERE objecttype_id = 1
			AND state_type = 1
			AND name1 = ' . $this->convert_sql_text($p_host_name) . '
			ORDER BY state_time DESC;';

        $l_result = $this->retrieve($l_query);

        if (!$l_result) {
            throw new isys_exception_database($this->get_database_component()
                ->get_last_error_as_string());
        }

        $l_states = [];

        while ($l_row = $l_result->get_row()) {
            $l_states[] = $l_row;
        }

        return $l_states;
    }

    /**
     *
     * @param   string $p_host_name
     * @param   string $p_service_description
     *
     * @return  array
     * @throws  isys_exception_database
     */
    public function getServiceStateHistory($p_host_name, $p_service_description)
    {
        $l_query = 'SELECT * FROM ' . $this->m_ndo_prefix . 'statehistory
			NATURAL JOIN ' . $this->m_ndo_prefix . 'objects
			WHERE objecttype_id = 2
			AND state_type = 1
			AND (state = 0 OR state = 2)
			AND name1 = ' . $this->convert_sql_text($p_host_name) . '
			AND name2 = ' . $this->convert_sql_text($p_service_description) . '
			ORDER BY state_time DESC;';

        $l_result = $this->retrieve($l_query);

        if (!$l_result) {
            throw new isys_exception_database($this->get_database_component()
                ->get_last_error_as_string());
        }

        $l_states = [];

        while ($l_row = $l_result->get_row()) {
            $l_states[] = $l_row;
        }

        return $l_states;
    }

    /**
     * DAO Constructor.
     *
     * @param  isys_component_database $p_db
     * @param  string                  $p_ndo_prefix
     */
    public function __construct(isys_component_database &$p_db, $p_ndo_prefix)
    {
        parent::__construct($p_db);

        $this->m_ndo_prefix = $p_ndo_prefix;
    }
}
