<?php
/**
 * Notizen:
 * --------------------------------------------------------------------------
 * - isys_component_dao -> isys_component
 * - isys_component_dao_result
 * - isys_component_dao_user -> isys_component_dao
 */

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
 */
class isys_component_dao_result implements isys_component_dao_result_interface
{
    /**
     * Current database component.
     *
     * @var  isys_component_database
     */
    protected $m_db;

    /**
     * Current databse resource.
     *
     * @var  resource
     */
    protected $m_dbres;

    /**
     * The last occured error.
     *
     * @var  string
     */
    protected $m_last_error;

    /**
     * Current SQL query.
     *
     * @var  string
     */
    protected $m_query;

    /**
     * Row data
     *
     * @var array
     */
    protected $m_row_data = [];

    /**
     * Is m_dbres still actively bound or already freed?
     *
     * @var bool
     */
    private $m_resource_active = true;

    /**
     * Returns a row from a DAO result. The result type is specified by $p_result_type and defaults to a assoc+numeric array as result.
     *
     * @param   integer $p_result_type
     *
     * @return  array
     */
    public function get_row($p_result_type = IDOIT_C__DAO_RESULT_TYPE_ARRAY)
    {
        /*
         if ($this->m_dbres)
        {*/
        switch ($p_result_type) {
            case IDOIT_C__DAO_RESULT_TYPE_ROW:
                return $this->m_db->fetch_row($this->m_dbres);
                break;
            case IDOIT_C__DAO_RESULT_TYPE_ALL:
                return $this->m_db->fetch_array($this->m_dbres);
                break;
            default:
            case IDOIT_C__DAO_RESULT_TYPE_ARRAY:
                return $this->m_db->fetch_row_assoc($this->m_dbres);
                break;
        }
        /*}
        else throw new Exception('Error while retrieving dataset. $this->m_dbres is empty.');
        */
    }

    /**
     * Returns the specified key value from the fetched row.
     *
     * @param   string $p_key
     *
     * @return  mixed
     */
    public function get_row_value($p_key)
    {
        $this->m_row_data = $this->m_db->fetch_row_assoc($this->m_dbres);
        $this->m_db->free_result($this->m_dbres);

        return (isset($this->m_row_data[$p_key])) ? $this->m_row_data[$p_key] : null;
    }

    /**
     * @inheritDoc
     * @deprecated
     */
    public function __to_array($p_result_type = IDOIT_C__DAO_RESULT_TYPE_ARRAY)
    {
        if ($this->count() > 0) {
            $this->m_row_data = $this->get_row($p_result_type);
            $this->free_result();
        }

        return $this->m_row_data;
    }

    /**
     * @inheritDoc
     * @deprecated
     */
    public function __as_array($p_result_type = IDOIT_C__DAO_RESULT_TYPE_ARRAY)
    {
        $l_ret = [];

        if ($this->count() > 0) {
            while ($l_row = $this->get_row($p_result_type)) {
                $l_ret[] = $l_row;
            }
        }
        $this->free_result();

        return $l_ret;
    }

    /**
     * @return bool|mixed
     */
    public function reset_pointer()
    {
        if ($this->count()) {
            return $this->m_db->data_seek($this->m_dbres);
        }

        return true;
    }

    /**
     * @return $this
     */
    public function free_result()
    {
        if ($this->m_resource_active) {
            $this->m_resource_active = false;

            return $this->m_db->free_result($this->m_dbres);
        }

        return false;
    }

    /**
     * Returns the number of rows - A wrapper method for "count()".
     *
     * @deprecated  Use "count()" instead.
     * @return      integer
     */
    public function num_rows()
    {
        return $this->count();
    }

    /**
     * Retrieves the number of fields from a query.
     *
     * @return  integer
     */
    public function num_fields()
    {
        return $this->m_db->num_fields($this->m_dbres);
    }

    /**
     * Get the type of the specified field in a result
     *
     * @param   integer $p_i
     *
     * @return  string
     */
    public function field_type($p_i)
    {
        return $this->m_db->field_type($this->m_dbres, $p_i);
    }

    /**
     *  Get the name of the specified field in a result
     *
     * @param   integer $p_i
     *
     * @return  string
     */
    public function field_name($p_i)
    {
        return $this->m_db->field_name($this->m_dbres, $p_i);
    }

    /**
     * Returns the length of the specified field
     *
     * @param   integer $p_i
     *
     * @return  integer
     */
    public function field_len($p_i)
    {
        return $this->m_db->field_len($this->m_dbres, $p_i);
    }

    /**
     * Get the flags associated with the specified field in a result.
     *
     * @param   integer $p_i
     *
     * @return  string
     */
    public function field_flags($p_i)
    {
        return $this->m_db->field_flags($this->m_dbres, $p_i);
    }

    /**
     * Returns the current query.
     *
     * @return  mixed  Might be an SQL query or null.
     */
    public function get_query()
    {
        return $this->m_query;
    }

    /**
     * Requery the last query.
     *
     * @return  isys_component_dao_result
     * @todo    Is this really used? Only found one single occurence.
     */
    public function requery()
    {
        $this->m_dbres = $this->m_db->query($this->get_query());

        return $this;
    }

    /**
     * Free memory on destruction
     */
    public function __destruct()
    {
        //if ($this->m_dbres) $this->free_result();
    }

    /**
     * Count method, called by Countable interface.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @link    http://php.net/manual/en/countable.count.php
     */
    public function count()
    {
        if ($this->m_db->is_resource($this->m_dbres)) {
            return (int) $this->m_db->num_rows($this->m_dbres);
        }

        return 0;
    }

    /**
     * Constructor. Needs the database component and a database resource.
     */
    public function __construct(isys_component_database &$p_db, $p_dbres, $p_query = null)
    {
        $this->m_db = $p_db;
        $this->m_dbres = $p_dbres;

        if ($p_query !== null) {
            $this->m_query = $p_query;
        }
    }
}
