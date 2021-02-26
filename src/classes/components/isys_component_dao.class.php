<?php

use idoit\Component\Location\Coordinate;

define("IDOIT_C__DAO_RESULT_TYPE_ARRAY", 1);
define("IDOIT_C__DAO_RESULT_TYPE_ROW", 2);
define("IDOIT_C__DAO_RESULT_TYPE_ALL", 3);

/**
 * i-doit
 *
 * DAO Base classes.
 *
 * @package     i-doit›
 * @subpackage  Components
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     Dennis Stücken <dstuecken@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * This component is the client to the database component.
 */
class isys_component_dao extends isys_component
{
    /**
     * @var isys_component_dao[]
     */
    protected static $instances = [];

    /**
     * Database component.
     *
     * @var isys_component_database
     */
    protected $m_db;

    /**
     * Last known error
     *
     * @var string
     */
    protected $m_last_error;

    /**
     * The last executed query.
     *
     * @var  string
     */
    protected $m_last_query;

    /**
     * Return instance of current class
     *
     * @return static
     */
    public static function factory($p_database)
    {
        return new static($p_database);
    }

    /**
     * Return singleton instance of current class
     *
     * @param isys_component_database $p_database
     *
     * @return static
     */
    public static function instance(isys_component_database $p_database)
    {
        $db_name = $p_database->get_db_name();
        $class = static::class;

        if (!isset(self::$instances[$class . ':' . $db_name])) {
            self::$instances[$class . ':' . $db_name] = new $class($p_database);
        }

        return self::$instances[$class . ':' . $db_name];
    }

    /**
     * Return last insert id.
     *
     * @return integer
     */
    public function get_last_insert_id()
    {
        // Retrieving last insert id from MySQL instead of the database driver, since we are experiencing several issues with insert id being "0".
        return (int)$this->retrieve('SELECT LAST_INSERT_ID() as id;')->get_row_value('id');
    }

    /**
     * Executes $p_query and returns DAO result or NULL on failure.
     * This is only for read access! For write access use self::update().
     *
     * @param   string $p_query
     *
     * @throws  isys_exception_database
     * @return  isys_component_dao_result
     */
    public function retrieve($p_query)
    {
        try {
            $this->m_last_query = $p_query;

            if ($this->m_db) {
                return new isys_component_dao_result($this->m_db, $this->m_db->query($p_query, false), $p_query);
            }

            throw new isys_exception_database('Retrieve failed. Database component not loaded!');
        } catch (isys_exception_database $e) {
            throw $e;
        }
    }

    /**
     * Executes $p_query and returns a boolean result. This is for write access (UPDATE, INSERT etc.) only.
     * All write queries have to be executed in a transaction, so we need to start one if there is noone running.
     *
     * @param   string $p_query
     *
     * @throws  isys_exception_dao
     * @return  boolean
     */
    public function update($p_query)
    {
        if ($this->m_db->is_connected()) {
            $this->m_last_query = $p_query;
            $l_ret = $this->m_db->query($p_query) or $this->m_last_error = $this->m_db->get_last_error_as_string();

            if ($l_ret) {
                return $l_ret;
            }

            $l_mailto_support = isys_helper_link::create_mailto('support@i-doit.org', ['subject' => 'i-doit Exception: ' . $this->m_last_error]);

            throw new isys_exception_dao(nl2br('<strong>MySQL-Error</strong>: ' . $this->m_last_error . PHP_EOL . PHP_EOL .
                '<strong>Query</strong>: ' . $this->m_last_query . PHP_EOL . PHP_EOL .
                'Try <a href="./updates">updating</a> your database. If this error occurs permanently, contact the i-doit team, please. (<a href="https://community.i-doit.com/" target="_blank">https://community.i-doit.com/</a>, ' .
                '<a href="' . $l_mailto_support . '">support@i-doit.org</a>)'));
        }

        return false;
    }

    /**
     * Change transaction behaviour
     *
     * @param $bool
     *
     * @return $this
     */
    public function set_autocommit($bool)
    {
        $this->m_db->set_autocommit($bool);

        return $this;
    }

    /**
     * Begins a transaction.
     */
    public function begin_update()
    {
        return $this->m_db->begin();
    }

    /**
     * After you made some update()-queries, this function will commit the transaction.
     *
     * @return  boolean
     */
    public function apply_update()
    {
        return $this->m_db->commit();
    }

    /**
     * Use this, if you want to rollback a transaction
     */
    public function cancel_update()
    {
        return $this->m_db->rollback();
    }

    /**
     * Returns how many rows were affected after an update.
     *
     * @return  integer
     */
    public function affected_after_update()
    {
        return $this->m_db->affected_rows();
    }

    /**
     * Returns the last query string.
     *
     * @return  string
     */
    public function get_last_query()
    {
        return $this->m_last_query;
    }

    /**
     * Returns the associated database component.
     *
     * @return  isys_component_database
     */
    public function get_database_component()
    {
        return $this->m_db;
    }

    /**
     * Convert id in sql compliant syntax depending on the value of $p_value.
     * It is used almost everywhere in i-doit.
     *
     * @param   mixed $p_value
     *
     * @return  int|string
     */
    public function convert_sql_id($p_value)
    {
        $l_id = (int)$p_value;

        if ($l_id <= 0) {
            return 'NULL';
        }

        return $l_id;
    }

    /**
     * Converts a numeric value or a string to a integer.
     *
     * @param   mixed $p_value Can be something numeric or a string.
     *
     * @return  int|string
     */
    public function convert_sql_int($p_value)
    {
        if ($p_value === null) {
            return 'NULL';
        }

        return (int)$p_value;
    }

    /**
     * @param  Coordinate $p_coord
     *
     * @return string
     */
    public function convert_sql_point($p_coord)
    {
        if (is_a($p_coord, Coordinate::class)) {
            return 'POINT(' . $this->convert_sql_text($p_coord->getLatitude()) . ', ' . $this->convert_sql_text($p_coord->getLongitude()) . ')';
        }

        return 'NULL';
    }

    /**
     * Convert text in SQL compliant syntax depending on system settings it is used in the methode save_element.
     *
     * @param  string $textValue
     *
     * @return string
     */
    public function convert_sql_text($textValue)
    {
        return "'" . $this->m_db->escape_string($textValue) . "'";
    }

    /**
     * Method for converting a numeric value to a float-variable as SQL understands it.
     *
     * @param  mixed $floatValue Can be a string or anything numeric.
     *
     * @return string
     * @author Dennis Stücken <dstuecken@i-doit.org>
     * @author Leonard Fischer <lfischer@i-doit.org>
     * @uses   isys_helper::filter_number()
     */
    public function convert_sql_float($floatValue)
    {
        // @see  ID-5297  Replaced the "is_numeric" check with "trim + strlen" because values like "199,99" are not numeric.
        // Also we can not use "empty" because 0 would be considered empty.
        if ($floatValue === null || trim($floatValue) === '') {
            return 'NULL';
        }

        return "'" . isys_helper::filter_number($floatValue) . "'";
    }

    /**
     * Method for avoiding SQL to saving an empty date string.
     *
     * @param  string $dateString
     *
     * @return string
     */
    public function convert_sql_datetime($dateString)
    {
        if (!empty($dateString) && $dateString !== '1970-01-01' && $dateString !== '0000-00-00') {
            // ID-1933  Because of the data type DATE the "NOW()" function will not work, so we need "CURDATE()".
            if ($dateString === 'NOW()' || $dateString === 'CURDATE()') {
                return $dateString;
            }

            if (is_numeric($dateString)) {
                return "'" . date('Y-m-d H:i:s', (int)$dateString) . "'";
            }

            $l_date = strtotime($dateString);

            if ($l_date === false) {
                return 'NULL';
            }

            return "'" . date('Y-m-d H:i:s', $l_date) . "'";
        }

        return 'NULL';
    }

    /**
     * Method for converting a boolean value to something, SQL can understand.
     *
     * @param  mixed $value Can be a boolean, (numeric) string or integer - Should be true (bool), 1 or "1" (NOT "false" or "true").
     *
     * @return integer
     */
    public function convert_sql_boolean($value)
    {
        return (int)(bool)$value;
    }

    /**
     * Prepares a MySQL conform IN() condition.
     *
     * @param  array   $array
     * @param  boolean $p_negate
     *
     * @return string
     */
    public function prepare_in_condition(array $array, $p_negate = false)
    {
        $items = [];

        if (count($array)) {
            foreach ($array as $arrayValue) {
                if (!is_numeric($arrayValue)) {
                    if (defined($arrayValue)) {
                        $arrayValue = constant($arrayValue);
                    } else {
                        continue;
                    }
                }

                $items[] = $this->convert_sql_int($arrayValue);
            }

            if (count($items)) {
                return ($p_negate ? 'NOT ' : '') . 'IN(' . implode(',', $items) . ')';
            }
        }

        return 'IS NULL';
    }

    /**
     * Constructor. Assigns database component.
     *
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        $this->m_db = $p_db;
        $this->m_last_query = '';
    }
}
