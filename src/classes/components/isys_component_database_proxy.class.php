<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

class isys_component_database_proxy extends isys_component_database
{
    /**
     * @var isys_component_database
     */
    private $db;

    /**
     * @param isys_component_database $database
     */
    public function setDatabase(isys_component_database $database)
    {
        if ($this->db && $this->db instanceof isys_component_database) {
            $this->db->close();
        }
        $this->db = $database;
    }

    /**
     * @return mixed
     */
    public function affected_rows()
    {
        if ($this->db !== null) {
            return $this->db->affected_rows();
        }

        $this->logDatabaseIsNotSet();

        return 0;
    }

    /**
     * @return mixed
     */
    public function begin()
    {
        if ($this->db !== null) {
            return $this->db->begin();
        }

        $this->logDatabaseIsNotSet();

        return false;
    }

    /**
     * @return mixed
     */
    public function close()
    {
        if ($this->db !== null) {
            $res = $this->db->close();

            $this->db = null;

            return $res;
        }

        $this->logDatabaseIsNotSet();

        return false;
    }

    /**
     * @return mixed
     */
    public function commit()
    {
        if ($this->db !== null) {
            return $this->db->commit();
        }

        $this->logDatabaseIsNotSet();

        return true;
    }

    /**
     * @param     $p_res
     * @param int $p_row_number
     *
     * @return mixed
     */
    public function data_seek($p_res, $p_row_number = 0)
    {
        if ($this->db !== null) {
            return $this->db->data_seek($p_res, $p_row_number = 0);
        }

        $this->logDatabaseIsNotSet();

        return false;
    }

    /**
     * @param $p_datepart
     * @param $p_number
     * @param $p_date
     *
     * @return mixed
     */
    public function date_add($p_datepart, $p_number, $p_date)
    {
        if ($this->db !== null) {
            return $this->db->date_add($p_datepart, $p_number, $p_date);
        }

        $this->logDatabaseIsNotSet();

        return '';
    }

    /**
     * @param $p_datepart
     * @param $p_number
     * @param $p_date
     *
     * @return mixed
     */
    public function date_sub($p_datepart, $p_number, $p_date)
    {
        if ($this->db !== null) {
            return $this->db->date_sub($p_datepart, $p_number, $p_date);
        }

        $this->logDatabaseIsNotSet();

        return '';
    }

    /**
     * @param string $p_value
     *
     * @return string
     */
    public function format_number($p_value)
    {
        if ($this->db !== null) {
            return $this->db->format_number($p_value);
        }

        $this->logDatabaseIsNotSet();

        return '';
    }

    /**
     * @param $p_str
     *
     * @return string
     */
    public function escape_string($p_str)
    {
        if ($this->db !== null) {
            return $this->db->escape_string($p_str);
        }

        return parent::escape_string($p_str);
    }

    /**
     * @param $p_res
     *
     * @return mixed
     */
    public function fetch_array($p_res)
    {
        if ($this->db !== null) {
            return $this->db->fetch_array($p_res);
        }

        $this->logDatabaseIsNotSet();

        return null;
    }

    /**
     * @param $p_res
     *
     * @return mixed
     */
    public function fetch_row($p_res)
    {
        if ($this->db !== null) {
            return $this->db->fetch_row($p_res);
        }

        $this->logDatabaseIsNotSet();

        return [];
    }

    /**
     * @param $p_res
     *
     * @return mixed
     */
    public function fetch_row_assoc($p_res)
    {
        if ($this->db !== null) {
            return $this->db->fetch_row_assoc($p_res);
        }

        $this->logDatabaseIsNotSet();

        return [];
    }

    /**
     * @param $p_res
     * @param $p_i
     *
     * @return mixed
     */
    public function field_flags($p_res, $p_i)
    {
        if ($this->db !== null) {
            return $this->db->field_flags($p_res, $p_i);
        }

        $this->logDatabaseIsNotSet();

        return '';
    }

    /**
     * @param $p_res
     * @param $p_i
     *
     * @return mixed
     */
    public function field_len($p_res, $p_i)
    {
        if ($this->db !== null) {
            return $this->db->field_len($p_res, $p_i);
        }

        $this->logDatabaseIsNotSet();

        return 0;
    }

    /**
     * @param $p_res
     * @param $p_i
     *
     * @return mixed
     */
    public function field_name($p_res, $p_i)
    {
        if ($this->db !== null) {
            return $this->db->field_name($p_res, $p_i);
        }

        $this->logDatabaseIsNotSet();

        return '';
    }

    /**
     * @param $p_res
     * @param $p_i
     *
     * @return mixed
     */
    public function field_type($p_res, $p_i)
    {
        if ($this->db !== null) {
            return $this->db->field_type($p_res, $p_i);
        }

        $this->logDatabaseIsNotSet();

        return null;
    }

    /**
     * @param $p_res
     *
     * @return mixed
     */
    public function free_result($p_res)
    {
        if ($this->db !== null) {
            return $this->db->free_result($p_res);
        }

        $this->logDatabaseIsNotSet();

        return null;
    }

    /**
     * @param $p_key
     *
     * @return mixed
     */
    public function get_config_value($p_key)
    {
        if ($this->db !== null) {
            return $this->db->get_config_value($p_key);
        }

        $this->logDatabaseIsNotSet();

        return null;
    }

    /**
     * @return mixed
     */
    public function get_last_error_as_id()
    {
        if ($this->db !== null) {
            return $this->db->get_last_error_as_id();
        }

        return -1;
    }

    /**
     * @return mixed
     */
    public function get_last_error_as_string()
    {
        if ($this->db !== null) {
            return $this->db->get_last_error_as_string();
        }

        $this->logDatabaseIsNotSet();

        return '';
    }

    /**
     * @return mixed
     */
    public function get_last_insert_id()
    {
        if ($this->db !== null) {
            return $this->db->get_last_insert_id();
        }

        $this->logDatabaseIsNotSet();

        return null;
    }

    /**
     * @param $p_like
     *
     * @return mixed
     */
    public function get_table_names($p_like)
    {
        if ($this->db !== null) {
            return $this->db->get_table_names($p_like);
        }

        $this->logDatabaseIsNotSet();

        return [];
    }

    /**
     * @return mixed
     */
    public function get_version()
    {
        if ($this->db !== null) {
            return $this->db->get_version();
        }

        $this->logDatabaseIsNotSet();

        return [
            'server' => null,
            'host'   => null,
            'client' => null,
            'proto'  => null
        ];
    }

    /**
     * Has been a connection established yet?
     *
     * @return bool
     */
    public function is_connected()
    {
        if ($this->db !== null) {
            return $this->db->is_connected();
        }

        $this->logDatabaseIsNotSet();

        return false;
    }

    /**
     * @param $p_table
     * @param $p_field
     *
     * @return mixed
     */
    public function is_field_existent($p_table, $p_field)
    {
        if ($this->db !== null) {
            return $this->db->is_field_existent($p_table, $p_field);
        }

        $this->logDatabaseIsNotSet();

        return false;
    }

    /**
     * Is given parameter a valid resource?
     *
     * @param $p_resource Resource
     *
     * @return bool
     */
    public function is_resource($p_resource)
    {
        return is_object($p_resource);
    }

    /**
     * @param $p_table
     *
     * @return mixed
     */
    public function is_table_existent($p_table)
    {
        if ($this->db !== null) {
            return $this->db->is_table_existent($p_table);
        }

        $this->logDatabaseIsNotSet();

        return false;
    }

    /**
     * @param $p_res
     *
     * @return mixed
     */
    public function num_fields($p_res)
    {
        if ($this->db !== null) {
            return $this->db->num_fields($p_res);
        }

        $this->logDatabaseIsNotSet();

        return 0;
    }

    /**
     * @param $p_res
     *
     * @return mixed
     */
    public function num_rows($p_res)
    {
        if ($this->db !== null) {
            return $this->db->num_rows($p_res);
        }

        $this->logDatabaseIsNotSet();

        return 0;
    }

    /**
     * @param      $p_query
     * @param bool $p_unbuffered
     *
     * @return mixed
     */
    public function query($p_query, $p_unbuffered = false)
    {
        if ($this->db !== null) {
            return $this->db->query($p_query, $p_unbuffered = false);
        }

        $this->logDatabaseIsNotSet();

        return null;
    }

    /**
     * @return mixed
     */
    public function reconnect()
    {
        if ($this->db !== null) {
            return $this->db->reconnect();
        }

        $this->logDatabaseIsNotSet();

        return null;
    }

    /**
     * @return mixed
     */
    public function rollback()
    {
        if ($this->db !== null) {
            return $this->db->rollback();
        }

        $this->logDatabaseIsNotSet();

        return false;
    }

    /**
     * @param $p_databasename
     *
     * @return mixed
     */
    public function select_database($p_databasename)
    {
        if ($this->db !== null) {
            return $this->db->select_database($p_databasename);
        }

        $this->logDatabaseIsNotSet();

        return null;
    }

    /**
     * @param $p_value
     *
     * @return mixed
     */
    public function set_autocommit($p_value)
    {
        if ($this->db !== null) {
            return $this->db->set_autocommit($p_value);
        }

        $this->logDatabaseIsNotSet();

        return false;
    }

    /**
     * Method for setting the transaction isolation level.
     *
     * @param  string $p_level
     */
    public function set_isolation_level($p_level)
    {
        if ($this->db !== null) {
            return $this->db->set_isolation_level($p_level);
        }

        $this->logDatabaseIsNotSet();

        return '';
    }

    /**
     * Sends an unbuffered query.
     *
     * @param string $p_query
     *
     * @return resource
     */
    public function unbuffered_query($p_query)
    {
        return $this->query($p_query, true);
    }

    /**
     *
     * @param   string  $p_update
     * @param   integer $p_len
     *
     * @return  string
     */
    public function limit_update($p_update, $p_len)
    {
        return $p_update . " LIMIT " . (int)$p_len;
    }

    /**
     * @return string
     */
    public function get_db_name()
    {
        if ($this->db !== null) {
            return $this->db->get_db_name();
        }

        $this->logDatabaseIsNotSet();

        return '';
    }

    /**
     * @return string
     */
    public function get_user()
    {
        if ($this->db !== null) {
            return $this->db->get_user();
        }

        $this->logDatabaseIsNotSet();

        return '';
    }

    /**
     * @return string
     */
    public function get_pass()
    {
        if ($this->db !== null) {
            return $this->db->get_pass();
        }

        $this->logDatabaseIsNotSet();

        return '';
    }

    /**
     * @return string
     */
    public function get_host()
    {
        if ($this->db !== null) {
            return $this->db->get_host();
        }

        $this->logDatabaseIsNotSet();

        return '';
    }

    /**
     * @return string
     */
    public function get_port()
    {
        if ($this->db !== null) {
            return $this->db->get_port();
        }

        $this->logDatabaseIsNotSet();

        return null;
    }

    /**
     * @return resource
     */
    public function get_link()
    {
        if ($this->db !== null) {
            return $this->db->get_link();
        }

        $this->logDatabaseIsNotSet();

        return null;
    }

    /**
     * @param isys_cache $p_cache
     *
     * @return $this
     */
    public function set_querycache(isys_cache $p_cache)
    {
        if ($this->db !== null) {
            return $this->db->set_querycache($p_cache);
        }
        $this->logDatabaseIsNotSet();

        return $this;
    }

    /**
     * @param \Psr\Log\LoggerInterface $p_logger
     *
     * @return $this
     */
    public function set_logger(\Psr\Log\LoggerInterface $p_logger)
    {
        $this->m_logger = $p_logger;
        if ($this->db !== null) {
            return $this->db->set_logger($p_logger);
        }
        $this->logDatabaseIsNotSet();

        return $this;
    }

    private function logDatabaseIsNotSet()
    {
        $message = 'Database is not connected - use defaults';
        if ($this->m_logger) {
            $this->m_logger->error($message);
        }
    }
}
