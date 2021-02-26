<?php

/**
 * i-doit
 *
 * Database wrapper class for PDO
 *
 * @package    i-doit
 * @subpackage Components
 * @author     Dennis Blümer <dbluemer@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_database_pdo extends isys_component_database
{

    /**
     * @var PDO
     */
    protected $m_db_link;

    /**
     * @var int
     */
    protected $m_port = 5432;

    private $m_dbType;

    /**
     * @return mixed
     */
    public function get_dbType()
    {
        return $this->m_dbType;
    }

    /**
     * @param $p_update
     * @param $p_len
     *
     * @return string
     */
    public function limit_update($p_update, $p_len)
    {
        $p_len = (int)$p_len;
        switch ($this->m_dbType) {
            case "pgsql":
                // pgSQl does not support LIMIT of UPDATEs
                return $p_update;
            default:
            case "mysql":
                return $p_update . " LIMIT " . $p_len;
        }
    }

    /**
     * @return integer
     * @desc Returns the number of affected rows by the last query.
     */
    public function affected_rows()
    {
        if (method_exists($this->m_db_link, 'rowCount')) {
            return $this->m_db_link->rowCount();
        }

        return 0;
    }

    /**
     * Method for beginning a new transaction.
     *
     * @todo  Test, if this is really needed!
     */
    public function begin()
    {
        // Transactions don't work and cause several thousand queries. We currently test the i-doit when this code is commented out.
        // $this->m_db_link->beginTransaction();
    }

    /**
     *
     */
    public function close()
    {
        unset($this->m_db_link);
    }

    /**
     * Method for commiting a new transaction.
     *
     * @todo  Test, if this is really needed!
     */
    public function commit()
    {
        // Transactions don't work and cause several thousand queries. We currently test the i-doit when this code is commented out.
        // $this->m_db_link->commit();
    }

    /**
     * Reset pointer to zero
     *
     * @param resource $p_res
     *
     * @return boolean
     */
    public function data_seek($p_res, $p_row_number = 0)
    {
        // PDO does not support pointer resetting, so we have to query again...
        return $p_res->execute();
    }

    /**
     * @param $p_datepart
     * @param $p_number
     * @param $p_date
     *
     * @return string
     */
    public function date_add($p_datepart, $p_number, $p_date)
    {
        $p_number = (int)$p_number;
        switch ($this->m_dbType) {
            case "pgsql":
                return $p_date . " + INTERVAL '" . $p_number . " " . $p_datepart . "'";
            default:
            case "mysql":
                return "DATE_ADD(" . $p_date . ", INTERVAL " . $p_number . " " . $p_datepart . ")";
        }
    }

    /**
     * @param $p_datepart
     * @param $p_number
     * @param $p_date
     *
     * @return string
     */
    public function date_sub($p_datepart, $p_number, $p_date)
    {
        $p_number = (int)$p_number;
        switch ($this->m_dbType) {
            case "pgsql":
                return $p_date . " - INTERVAL '" . $p_number . " " . $p_datepart . "'";
            default:
            case "mysql":
                return "DATE_SUB(" . $p_date . ", INTERVAL " . $p_number . " " . $p_datepart . ")";
        }
    }

    /**
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
     * @return array
     *
     * @param PDOStatement $p_res
     *
     * @desc Fetches a row as numeric+assoc array from the result set
     */
    public function fetch_array($p_res)
    {
        return $p_res->fetch(PDO::FETCH_BOTH);
    }

    /**
     * @return array
     *
     * @param PDOStatement $p_res
     *
     * @desc Fetches a row from the result set.
     */
    public function fetch_row($p_res)
    {
        return $p_res->fetch(PDO::FETCH_NUM);
    }

    /**
     * @return array
     *
     * @param PDOStatement $p_res
     *
     * @desc Fetches a row as associative array from the result set.
     */
    public function fetch_row_assoc($p_res)
    {
        return $p_res->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param PDOStatement $p_res
     *
     * @desc Get the flags associated with the specified field in a result
     */
    public function field_flags($p_res, $p_i)
    {
        $l_meta = $p_res->getColumnMeta($p_i);

        return $l_meta["flags"];
    }

    /**
     * @param PDOStatement $p_res
     *
     * @desc Returns the length of the specified field
     */
    public function field_len($p_res, $p_i)
    {
        $l_meta = $p_res->getColumnMeta($p_i);

        return $l_meta["len"];
    }

    /**
     * @param PDOStatement $p_res
     *
     * @desc Get the name of the specified field in a result
     */
    public function field_name($p_res, $p_i)
    {
        $l_meta = $p_res->getColumnMeta($p_i);

        return $l_meta["name"];
    }

    /**
     * @param PDOStatement $p_res
     *
     * @desc Get the type of the specified field in a result
     */
    public function field_type($p_res, $p_i)
    {
        $l_meta = $p_res->getColumnMeta($p_i);

        return $l_meta["native_type"];
    }

    /**
     * @param PDOStatement $p_res
     *
     * @return bool
     */
    public function free_result($p_res)
    {
        return $p_res->closeCursor();
    }

    /**
     * Retrieve mysql settings
     *
     * @param $p_key
     */
    public function get_config_value($p_key)
    {
        $l_get = $this->query('SHOW ' . $this->escape_string($p_key) . ';');
        $l_row = $this->fetch_array($l_get);

        return $l_row[0];
    }

    /**
     * @return integer
     * @desc Returns the ID of the last error.
     */
    public function get_last_error_as_id()
    {
        return $this->m_db_link->errorCode();
    }

    /**
     * @return string
     * @desc Returns the description of the last error
     */
    public function get_last_error_as_string()
    {
        $l_arError = $this->m_db_link->errorInfo();

        return $l_arError[2];
    }

    /**
     * @return integer
     * @desc Returns the last ID of an inserted record. Session-scope function.
     */
    public function get_last_insert_id()
    {
        return $this->m_db_link->lastInsertId();
    }

    /**
     * @param $p_like
     *
     * @return array
     * @throws Exception
     */
    public function get_table_names($p_like)
    {
        $l_tables = [];

        switch ($this->m_dbType) {
            case "pgsql":
                $l_query = 'SELECT table_name 
                    FROM INFORMATION_SCHEMA.TABLES 
                    WHERE table_catalog = \'' . $this->escape_string($this->m_db_name) . '\'
                    AND table_name LIKE \'' . $this->escape_string($p_like) . '\';';
                $l_res = $this->query($l_query);

                while ($l_row = $this->fetch_row($l_res)) {
                    $l_tables[] = $l_row[0];
                }

                break;
        }

        return $l_tables;
    }

    /**
     * @return array
     * @desc    Returns an array with the version information of the underlying DBMS
     *       On failure, it'll return null.
     * @version Dennis Blümer <dbluemer@i-doit.org>
     */
    public function get_version()
    {
        if ($this->is_connected()) {
            return [
                "server" => $this->m_db_link->getAttribute(PDO::ATTR_SERVER_VERSION),
                "host"   => $this->m_db_link->getAttribute(PDO::ATTR_SERVER_INFO),
                "client" => $this->m_db_link->getAttribute(PDO::ATTR_CLIENT_VERSION),
                "proto"  => $this->m_db_link->getAttribute(PDO::ATTR_DRIVER_NAME)
            ];
        }

        return null;
    }

    /**
     * @return boolean
     *
     * @param string $p_table
     *
     * @desc Tests if $p_table is existent.
     * @todo In some cases this query is totally unlogic
     */
    public function is_field_existent($p_table, $p_field)
    {
        $l_strQuery = "DESC " . $p_table . " '" . $p_field . "'";
        $l_res = $this->query($l_strQuery);

        return !!($this->num_rows($l_res));
    }

    /**
     * @param $p_resource
     *
     * @return bool
     */
    public function is_resource($p_resource)
    {
        return is_object($p_resource);
    }

    /**
     * Checks if $p_table is existent.
     *
     * @param $p_table
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function is_table_existent($p_table)
    {
        switch ($this->m_dbType) {
            case "pgsql":
                $l_query = 'SELECT * FROM INFORMATION_SCHEMA.TABLES
                    WHERE TABLE_CATALOG = \'' . $this->escape_string($this->m_db_name) . '\'
                    AND TABLE_NAME LIKE \'' . $this->escape_string($p_table) . '\';';
                break;
        }

        return $this->num_rows($this->query($l_query)) > 0 ? true : false;
    }

    /**
     * @param $p_query
     * @param $p_len
     * @param $p_offset
     *
     * @return string
     */
    public function limit_query($p_query, $p_len, $p_offset)
    {
        switch ($this->m_dbType) {
            default:
            case "mysql":
            case "pgsql":
                return $p_query . " LIMIT " . (int)$p_len . " OFFSET " . (int)$p_offset;
        }
    }

    /**
     * @return integer
     *
     * @param PDOStatement $p_res
     *
     * @desc  Retrieves the number of fields from a query
     */
    public function num_fields($p_res)
    {
        return $p_res->columnCount();
    }

    /**
     * @return integer
     *
     * @param PDOStatement $p_res
     *
     * @desc Returns the count of rows in the result set.
     */
    public function num_rows($p_res)
    {
        // Works for MySQL, but in general a SELECT COUNT(*) statement should be issued, having the same predicates as the original query
        return $p_res->rowCount();
    }

    /**
     * @param      $p_query
     * @param bool $p_unbuffered
     *
     * @return PDOStatement
     * @throws Exception
     */
    public function query($p_query, $p_unbuffered = false)
    {
        try {
            if ($this->m_db_link) {
                if ($this->m_logger) {
                    $this->m_logger->debug($p_query);
                }

                $l_res = $this->m_db_link->query($p_query);

                if ($l_res === false) {
                    throw new isys_exception_database("Query error: '" . $p_query . "':\n" . $this->get_last_error_as_string(), $this->get_version());
                }

                return $l_res;
            }
        } catch (Exception $e) {
            isys_application::instance()->container->get('logger')->emergency($e->getMessage());

            throw $e;
        }

        return null;
    }

    /**
     * @throws isys_exception_database
     */
    public function reconnect()
    {
        unset($this->m_db_link);

        try {
            $l_dsn = $this->m_dbType . ":host=" . $this->m_host . ";port=" . $this->m_port . ";dbname=" . $this->m_db_name . ";user=" . $this->m_user . ";password=" .
                $this->m_pass;

            if ($l_dsn) {
                $this->m_db_link = new PDO($l_dsn);
            } else {
                return false;
            }

            return $this->m_db_link;
        } catch (PDOException $e) {
            throw new isys_exception_database($e->getMessage());
        }
    }

    /**
     * Method for rolling back a transaction.
     *
     * @todo  Test, if this is really needed!
     */
    public function rollback()
    {
        // Transactions don't work and cause several thousand queries. We currently test the i-doit when this code is commented out.
        // $this->m_db_link->rollBack();
    }

    /**
     * @return boolean
     * @desc Select a database.
     */
    public function select_database($p_databasename)
    {
        return is_object($this->m_db_link->query("USE " . $p_databasename)) ? true : false;
    }

    /**
     * Method for setting the auto-commit function.
     *
     * @todo   Test, if this is really needed!
     *
     * @param  boolean $p_value
     */
    public function set_autocommit($p_value)
    {
        // Transactions don't work and cause several thousand queries. We currently test the i-doit when this code is commented out.

        switch ($this->m_dbType) {
            case "mysql":
                // return $this->query("SET AUTOCOMMIT = " . ($p_value == true) ? "1" : "0");
            case "pgsql":
                // return true;
        }
    }

    /**
     * Method for setting the auto-commit function.
     *
     * @todo   Test, if this is really needed!
     *
     * @param  string $p_level
     */
    public function set_isolation_level($p_level)
    {
        // Transactions don't work and cause several thousand queries. We currently test the i-doit when this code is commented out.

        switch ($this->m_dbType) {
            case "mysql":
                // return $this->query("SET SESSION TRANSACTION ISOLATION LEVEL " . $p_level);
            case "pgsql":
                // return true;
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->m_db_link);
    }

    /**
     * @param string  $p_host
     * @param integer $p_port
     * @param string  $p_user
     * @param string  $p_password
     * @param string  $p_databasename
     *
     * @throws isys_exception_database
     * @desc Constructor. Connects to the specified database and selects the
     *       requested database. The constructor also needs and assigns the
     *       transaction manager.
     */
    public function __construct($p_dbType, $p_host, $p_port, $p_user, $p_password, $p_databasename, $p_dsn = '')
    {
        try {
            if ($p_dsn != '') {
                $l_dsn = $p_dsn;
            } else {
                switch ($p_dbType) {
                    case 'dblib':
                        $l_host = 'host=' . $p_host . ':' . $p_port;
                        break;
                    case 'mysql':
                    default:
                        $l_host = 'host=' . $p_host . ';port=' . $p_port;
                        break;
                }
                $l_dsn = $p_dbType . ":" . $l_host . ";dbname=" . $p_databasename;
            }

            $this->m_db_link = new PDO($l_dsn, $p_user, $p_password, [
                PDO::ATTR_TIMEOUT => isys_tenantsettings::get('system.pdo.pgsql.timeout', 5),
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            $this->m_dbType = $p_dbType;
            $this->m_port = $p_port;
            $this->m_host = $p_host;
            $this->m_db_name = $p_databasename;
            $this->m_user = $p_user;
            $this->m_pass = $p_password;
        } catch (PDOException $e) {
            $l_pdoDrivers = pdo_drivers();
            if (!in_array('pgsql', $l_pdoDrivers)) {
                $l_drivermessage = "\n\nPlease make sure your PDO Database driver \"{$p_dbType}\" is installed and the PDO database is reachable at \"{$p_host}:{$p_port}\". \nCheck https://php.net/manual/de/pdo.installation.php for more information.\n\n" .
                    "You currently have the following drivers installed: " . implode(', ', $l_pdoDrivers);
            } else {
                $l_drivermessage = '';
            }

            throw new isys_exception_database($e->getMessage() . $l_drivermessage);
        }
    }
}
