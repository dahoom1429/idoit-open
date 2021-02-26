<?php

use MySQL\Error\Server as MySQLServerErrors;

/**
 * i-doit
 *
 * Database wrapper class for MySQLi.
 *
 * @package    i-doit
 * @subpackage Components
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_database_mysqli extends isys_component_database
{
    /**
     * How often a query should repeat on deadlock
     */
    const MAX_DEADLOCK_QUERY_REPEAT = 2;

    /**
     * @var mysqli
     */
    protected $m_db_link;

    /**
     * @var int
     */
    protected $m_port = 3306;

    /**
     * @var bool
     */
    protected $m_transaction_running = false;

    /**
     * @var int
     */
    private $deadlockQueryRepeat = 0;

    /**
     * @return void
     */
    private function incrementDeadlockQueryRepeat()
    {
        $this->deadlockQueryRepeat++;
    }

    /**
     * @return void
     */
    private function resetDeadlockQueryRepeat()
    {
        $this->deadlockQueryRepeat = 0;
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
     * @desc Get the name of the specified field in a result
     *
     * @todo Could'nt find any method or attribute for MySQLi.
     */
    public function field_table($p_res, $p_i)
    {
        return '';
    }

    /**
     * @return integer
     * @desc Returns the number of affected rows by the last query.
     */
    public function affected_rows()
    {
        if ($this->m_db_link) {
            return mysqli_affected_rows($this->m_db_link);
        }

        return 0;
    }

    /**
     * Method for beginning a new transaction.
     */
    public function begin()
    {
        if (is_object($this->m_db_link) && !$this->m_transaction_running) {
            $this->set_autocommit(false);
            $this->m_transaction_running = true;

            return $this->query('BEGIN;');
        }

        return false;
    }

    /**
     * @return boolean
     * @version Niclas Potthast <npotthast@i-doit.org> - 2007-01-15
     * @version Dennis Stücken 01-2009
     * @desc    Closes the database connection if valid.
     */
    public function close()
    {
        if ($this->is_connected()) {
            try {
                $this->m_db_link->close();
                unset($this->m_db_link);
            } catch (ErrorException $e) {
            }

            return true;
        }

        return false;
    }

    /**
     * Method for comitting a new transaction.
     */
    public function commit()
    {
        if (is_object($this->m_db_link)) {
            $this->set_autocommit(false);
            $this->m_transaction_running = false;

            $committed = mysqli_commit($this->m_db_link);

            $this->set_autocommit(true);

            return $committed;
        }

        return true;
    }

    /**
     * Reset pointer to zero.
     *
     * @param  mysqli_result $p_res
     * @param  integer       $p_row_number
     *
     * @return boolean
     */
    public function data_seek($p_res, $p_row_number = 0)
    {
        if ($p_res) {
            return mysqli_data_seek($p_res, $p_row_number);
        }

        return false;
    }

    /**
     *
     * @param   string  $p_datepart
     * @param   integer $p_number
     * @param   string  $p_date
     *
     * @return  string
     */
    public function date_add($p_datepart, $p_number, $p_date)
    {
        return "DATE_ADD(" . $p_date . ", INTERVAL " . ((int)$p_number) . " " . ($p_datepart ?: 'SECONDS') . ")";
    }

    /**
     *
     * @param   string  $p_datepart
     * @param   integer $p_number
     * @param   string  $p_date
     *
     * @return  string
     */
    public function date_sub($p_datepart, $p_number, $p_date)
    {
        return "DATE_SUB(" . $p_date . ", INTERVAL " . ((int)$p_number) . " " . ($p_datepart ?: 'SECONDS') . ")";
    }

    /**
     *
     * @param string $p_value
     *
     * @return string
     */
    public function format_number($p_value)
    {
        $decimalSeparator = isys_application::instance()->container->get('locales')->get_user_settings(LC_NUMERIC);
        return 'REPLACE(ROUND(' . $p_value . ', 2), ".", "' . $decimalSeparator['decimal_point'] . '")';
    }

    /**
     * @param $p_str
     *
     * @return string
     */
    public function escape_string($p_str)
    {
        if ($this->is_connected()) {
            return mysqli_real_escape_string($this->m_db_link, (string)$p_str);
        }

        return str_replace(
            ["\\", "\x00", "\n", "\r", "'", '"', "\x1a"],
            ["\\\\", "\\0", "\\n", "\\r", "\\'", '\"', "\\Z"],
            $p_str
        );
    }

    /**
     * Fetches a row as numeric+assoc array from the result set.
     *
     * @param mysqli_result $p_res
     *
     * @return array
     */
    public function fetch_array($p_res)
    {
        if ($p_res) {
            return mysqli_fetch_array($p_res);
        }

        return [];
    }

    /**
     * Fetches a row from the result set.
     *
     * @param   mysqli_result $p_res
     *
     * @return  array
     */
    public function fetch_row($p_res)
    {
        if ($p_res) {
            return mysqli_fetch_row($p_res);
        }

        return [];
    }

    /**
     * Fetches a row as associative array from the result set.
     *
     * @param mysqli_result $p_res
     *
     * @return array
     */
    public function fetch_row_assoc($p_res)
    {
        if ($p_res) {
            return mysqli_fetch_assoc($p_res);
        }

        return [];
    }

    /**
     * @desc Get the flags associated with the specified field in a result
     *
     * @todo Could'nt find any method or attribute for MySQLi.
     */
    public function field_flags($p_res, $p_i)
    {
        return '';
    }

    /**
     * @desc Returns the length of the specified field
     */
    public function field_len($p_res, $p_i)
    {
        $lengths = mysqli_fetch_lengths($p_res);

        return isset($lengths[$p_i]) ? $lengths[$p_i] : $lengths[$p_i];
    }

    /**
     * @desc Get the name of the specified field in a result
     *
     * @todo Could'nt find any method or attribute for MySQLi.
     */
    public function field_name($p_res, $p_i)
    {
        return '';
    }

    /**
     * @desc Get the type of the specified field in a result
     *
     * @todo Could'nt find any method or attribute for MySQLi.
     */
    public function field_type($p_res, $p_i)
    {
        return 'string';
    }

    /**
     *
     * @return  void
     *
     * @param   mysqli_result $p_res
     */
    public function free_result($p_res)
    {
        if (is_object($p_res)) {
            $p_res->free();
        }
    }

    /**
     * Retrieve mysql settings
     *
     * @param $p_key
     */
    public function get_config_value($p_key)
    {
        $l_get = $this->query('SELECT @@global.' . $this->escape_string($p_key) . ';');
        $l_row = $this->fetch_array($l_get);

        return $l_row[0];
    }

    /**
     * Returns the ID of the last error.
     *
     * @return  integer
     */
    public function get_last_error_as_id()
    {
        if (is_object($this->m_db_link)) {
            return mysqli_errno($this->m_db_link);
        } else {
            return -1;
        }
    }

    /**
     * Returns the description of the last error.
     *
     * @return  string
     */
    public function get_last_error_as_string()
    {
        if (is_object($this->m_db_link)) {
            return mysqli_error($this->m_db_link);
        } else {
            return '';
        }
    }

    /**
     * Returns the last ID of an inserted record. Session-scope function.
     *
     * @return integer
     */
    public function get_last_insert_id()
    {
        if ($this->m_db_link) {
            // Try this function, instead of an extra query (see below).
            return $this->m_db_link->insert_id;
        }

        return null;

        /*
        $l_res = $this->query("SELECT LAST_INSERT_ID() as id");
        if ($l_res && $this->num_rows($l_res) > 0)
        {
            $l_data = $this->fetch_row_assoc($l_res);
            return $l_data["id"];
        }
        return 0;
        */
    }

    /**
     * Retrieve table names by a given string ("%" wildchard is allowed).
     *
     * @param   string $p_like
     *
     * @return  array
     */
    public function get_table_names($p_like)
    {
        $l_tables = [];

        $l_res = $this->query("SHOW TABLES LIKE '" . $p_like . "'");

        while ($l_row = $this->fetch_row($l_res)) {
            $l_tables[] = $l_row[0];
        }

        return $l_tables;
    }

    /**
     * Returns an array with the version information of the mySQL-DBS. On failure, it'll return null.
     *
     * @return  mixed
     */
    public function get_version()
    {
        if ($this->is_connected()) {
            return [
                "server" => mysqli_get_server_info($this->m_db_link),
                "host"   => mysqli_get_host_info($this->m_db_link),
                "client" => mysqli_get_client_info(),
                "proto"  => mysqli_get_proto_info($this->m_db_link)
            ];
        }

        return null;
    }

    /**
     * Has been a connection established yet?
     *
     * @return bool
     */
    public function is_connected()
    {
        return ($this->m_db_link instanceof mysqli);
    }

    /**
     * Tests if $p_table is existent.
     *
     * @param   string $p_table
     * @param   string $p_field
     *
     * @return  boolean
     * @todo    In some cases this query is totally unlogic
     */
    public function is_field_existent($p_table, $p_field)
    {
        $l_res = $this->query("DESC " . $p_table . " '" . $p_field . "';");

        return !!($this->num_rows($l_res));
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
     * Tests if $p_table is existent.
     *
     * @param   string $p_table
     *
     * @return  boolean
     */
    public function is_table_existent($p_table)
    {
        return !!($this->num_rows($this->query("SHOW TABLES LIKE '" . $p_table . "'")));
    }

    /**
     *
     * @param   string  $p_query
     * @param   integer $p_len
     * @param   integer $p_offset
     *
     * @return  string
     */
    public function limit_query($p_query, $p_len, $p_offset)
    {
        return $p_query . " LIMIT " . ((int)$p_len) . " OFFSET " . $p_offset;
    }

    /**
     * @return integer
     *
     * @param mysqli_result $p_res
     *
     * @desc  Retrieves the number of fields from a query
     */
    public function num_fields($p_res)
    {
        if ($p_res) {
            return mysqli_num_fields($p_res);
        }

        return 0;
    }

    /**
     * @return integer
     *
     * @param mysqli_result $p_res
     *
     * @desc Returns the count of rows in the result set.
     */
    public function num_rows($p_res)
    {
        if ($p_res) {
            return @mysqli_num_rows($p_res);
        }

        return 0;
    }

    /**
     * Queries the database.
     *
     * @param string $p_query      Query
     * @param bool   $p_unbuffered Is this an unbuffered query? Defaults to false.
     *
     * @return mysqli_result
     * @throws isys_exception_database_mysql when something goes wrong
     */
    public function query($p_query, $p_unbuffered = false)
    {
        try {
            // The connetion sometimes gets lost unexpectetly. We didn't find out why, set.
            // So let's try to reconnect as a quick-fix for this problem (see ID-3670)
            if (!is_object($this->m_db_link)) {
                $this->reconnect();
            }

            if (is_object($this->m_db_link)) {
                if ($this->m_logger) {
                    $start = microtime(true);
                }

                if ($p_unbuffered) {
                    $l_res = mysqli_query($this->m_db_link, $p_query, MYSQLI_USE_RESULT);
                } else {
                    $l_res = mysqli_query($this->m_db_link, $p_query, MYSQLI_STORE_RESULT);
                }

                if ($this->m_logger) {
                    /** @var float $start */
                    $this->m_logger->debug($p_query, [
                        'duration' => microtime(true) - $start,
                        'success'  => $l_res !== false,
                        'result'   => $l_res,
                        'database' => $this->get_db_name()
                    ]);
                }

                if ($l_res === false) {
                    throw new isys_exception_database_mysql(
                        "Query error: '" . $p_query . "':\n" . $this->get_last_error_as_string(),
                        $this->get_version(),
                        $this->get_last_error_as_id()
                    );
                }
            } else {
                throw new isys_exception_database_mysql('MySQLi error: Lost link to database. (' . mysqli_connect_error() . ', ' .
                    ($this->get_last_error_as_string() ?: 'Unknown error') . ')');
            }
        } catch (isys_exception_database_mysql $e) {
            isys_application::instance()->container->get('logger')->emergency($e->getMessage());

            if ($e->getCode() === MySQLServerErrors::ER_LOCK_DEADLOCK && $this->deadlockQueryRepeat < self::MAX_DEADLOCK_QUERY_REPEAT) {
                // wait for deadlock to be removed by the database engine
                usleep(500);
                // retry
                $this->incrementDeadlockQueryRepeat();
                return $this->query($p_query, $p_unbuffered);
            }

            throw $e;
        }

        unset($p_query);
        $this->resetDeadlockQueryRepeat();

        return $l_res;
    }

    /**
     * @throws Exception
     * @throws isys_exception_database_mysql
     */
    public function reconnect()
    {
        try {
            if (!$this->m_db_link = mysqli_connect($this->m_host, $this->m_user, $this->m_pass, $this->m_db_name, $this->m_port)) {
                throw new isys_exception_database_mysql(mysqli_connect_error());
            }
        } catch (isys_exception_database_mysql $e) {
            throw $e;
        }
    }

    /**
     * Method for rolling back a transaction.
     */
    public function rollback()
    {
        if (is_object($this->m_db_link)) {
            $this->set_autocommit(false);
            $this->m_transaction_running = false;

            return mysqli_rollback($this->m_db_link);
        }

        return false;
    }

    /**
     * Select a database.
     *
     * @param   string $p_databasename
     *
     * @return  boolean
     */
    public function select_database($p_databasename)
    {
        return mysqli_select_db($this->m_db_link, $p_databasename);
    }

    /**
     * Method for setting the auto-commit function on/off.
     *
     * @param   boolean $p_value
     *
     * @return  boolean
     */
    public function set_autocommit($p_value)
    {
        if (is_object($this->m_db_link)) {
            return mysqli_autocommit($this->m_db_link, !!$p_value);
        }

        return false;
    }

    /**
     * Method for setting the transaction isolation level.
     *
     * @param  string $p_level
     */
    public function set_isolation_level($p_level)
    {
        return $this->query("SET SESSION TRANSACTION ISOLATION LEVEL " . $p_level . ';');
    }

    /**
     * Destructor method, closes the connection.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Connects to a database and returns a resource link.
     *
     * @param string  $p_host
     * @param integer $p_port
     * @param string  $p_user
     * @param string  $p_password
     *
     * @return resource
     * @throws isys_exception_database_mysql when connection refused
     * @throws Exception when something goes wrong
     */
    private function connect($p_host, $p_port, $p_user, $p_password)
    {
        try {
            $this->m_db_link = mysqli_connect($p_host, $p_user, $p_password, '', $p_port);

            if ($this->m_db_link->connect_error) {
                throw new isys_exception_database_mysql(
                    nl2br("<strong>Database connection to " . $p_user . "@" . $p_host . ':' . $p_port .
                    " failed.</strong>\n\n" . "Possible errors: \n" . "* MySQL Server not loaded.\n" .
                    "* Password or settings for tenant connection in table \"isys_mandator\" (system database) wrong.\n" .
                    "* Database settings wrong in configuration file: src/config.inc.php\n\n" . "MySQL-Reports: <strong>" . mysqli_connect_error() . "</strong>"),
                    [],
                    mysqli_connect_errno()
                );
            }

            // Disable SQL strict mode:
            [$this->m_strictmode] = $this->fetch_row($this->query("SELECT @@SESSION.sql_mode;"));
            $this->m_strictmode = ($this->m_strictmode != '');
            $this->query("SET sql_mode='';");
            $this->query("SET names utf8;");
        } catch (isys_exception_database_mysql $e) {
            throw $e;
        }

        return $this->m_db_link;
    }

    /**
     * Constructor. Connects to the specified database and selects the requested database.
     *
     * @param $p_host
     * @param $p_port
     * @param $p_user
     * @param $p_password
     * @param $p_databasename
     *
     * @throws Exception
     */
    public function __construct($p_host, $p_port, $p_user, $p_password, $p_databasename)
    {
        try {
            $this->m_db_link = $this->connect($p_host, $p_port, $p_user, $p_password);

            if ($this->is_connected()) {
                $this->m_user = $p_user;
                $this->m_port = $p_port;
                $this->m_host = $p_host;
                $this->m_pass = $p_password;
                if (!$this->select_database($p_databasename)) {
                    $l_message = '';

                    global $g_db_system;
                    global $g_comp_database_system;

                    if ($p_databasename != $g_db_system["name"] && is_object($g_comp_database_system) && $g_comp_database_system->is_connected()) {
                        $l_dao = new isys_component_dao_mandator($g_comp_database_system);
                        $l_mandator_id = $l_dao->get_mandator_id_by_db_name($p_databasename);

                        if ($l_mandator_id) {
                            $l_dao->deactivate_mandator($l_mandator_id);
                            $l_message = " // This tenant has been deactivated. Check table isys_mandator in your system-database for more information. (SELECT * FROM isys_mandator)";
                        }
                    }

                    throw new isys_exception_database_mysql(
                        "Could not select database: " . $p_databasename . $l_message,
                        $this->get_last_error_as_string(),
                        $this->get_last_error_as_id()
                    );
                }

                $this->m_db_name = $p_databasename;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}
