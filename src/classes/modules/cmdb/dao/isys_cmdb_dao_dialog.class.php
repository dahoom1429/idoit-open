<?php

/**
 * i-doit
 *
 * Dialog DAO.
 *
 * @package     i-doit
 * @subpackage  CMDB_Low-Level_API
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_dialog extends isys_cmdb_dao
{
    /**
     * The static cache variable.
     *
     * @var  array
     */
    protected $m_cache = [];

    /**
     * Cache which contains parent tables
     *
     * @var array
     */
    protected $m_cache_parent_tables = [];

    /**
     * The dialogs table-name.
     *
     * @var  string
     */
    protected $m_table = '';

    /**
     * Checks a dialog entry for its existence and creates a new one or returns
     * the identifier of the existing one.
     *
     * @param   string  $p_table
     * @param   string  $p_title
     * @param   string  $p_check_name  // Parameter has no function!!
     * @param   integer $p_parent_id
     * @param   array   $p_custom_data Attach custom data to dialog (e.g. ['description' => 'custom description for this dialog field])
     *
     * @author Dennis Stücken <dstuecken@i-doit.com>
     * @note   moved from isys_import::check_dialog
     *
     * @return  integer  Returns null if no data could be found.
     */
    public function check_dialog($p_table, $p_title, $p_check_name = null, $p_parent_id = null, array $p_custom_data = [])
    {
        $this->m_table = $p_table;
        $this->load();

        if ($p_title !== '') {
            $p_title = (string)$p_title;

            // We use the dialog factory for cached data, saves thousands of queries during im-/export.
            if ($p_check_name === null && $p_parent_id === null) {
                $l_data = $this->get_data(null, $p_title);

                if ($l_data !== false) {
                    return $l_data[$p_table . '__id'];
                }
            } else {
                $l_data = $this->get_data_by_parent($p_title, $p_parent_id);

                if ($l_data !== false) {
                    return $l_data[$p_table . '__id'];
                }
            }

            $l_id = isys_cmdb_dao_dialog_admin::instance($this->get_database_component())
                ->create($p_table, $p_title, 0, '', C__RECORD_STATUS__NORMAL, $p_parent_id, null, '', $p_custom_data);

            // reload dialog data
            $this->reset();

            return $l_id;
        } else {
            return null;
        }
    }

    /**
     * Retrieves data with the specified title and parent id
     *
     * @param  string  $p_title
     * @param  integer $p_parent_id
     * @param  array   $excludeIds
     *
     * @return boolean|array
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_data_by_parent($p_title, $p_parent_id, array $excludeIds = [])
    {
        if (!$this->m_cache_parent_tables[$this->m_table]) {
            $this->m_cache_parent_tables[$this->m_table] = isys_cmdb_dao_dialog_admin::instance($this->m_db)
                ->get_parent_table($this->m_table);
        }

        if (!isset($this->m_cache[$this->m_table])) {
            $this->load();
        }

        $l_title_lower = trim(strtolower($p_title));
        if (isset($this->m_cache[$this->m_table])) {
            foreach ($this->m_cache[$this->m_table] as $l_data) {
                $l_data_lower_title = isset($l_data['title_lower'])
                    ? $l_data['title_lower']
                    : strtolower((isset($l_data['title'])
                        ? $l_data['title']
                        : $l_data[$this->m_table . '__title']));

                if ($l_data[$this->m_table . '__' . $this->m_cache_parent_tables[$this->m_table] . '__id'] == $p_parent_id && $l_data_lower_title === $l_title_lower && !in_array($l_data[$this->m_table . '__id'], $excludeIds)) {
                    return $l_data;
                }
            }
        }

        return false;
    }

    /**
     * Method for retrieving data from a dialog-table.
     *
     * @param   mixed  $p_id Can be the numeric ID or a constant as string.
     * @param   string $p_title
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_data($p_id = null, $p_title = null)
    {
        // This should never fail, but we want to go sure.
        if (!$this->m_cache[$this->m_table]) {
            $this->load();
        }

        if ($p_id !== null) {
            if (is_numeric($p_id)) {
                if (isset($this->m_cache[$this->m_table]) && isset($this->m_cache[$this->m_table][$p_id])) {
                    return $this->m_cache[$this->m_table][$p_id];
                }
            } else {
                // Find the entry by constant.
                foreach ($this->m_cache[$this->m_table] as $l_data) {
                    if (is_string($p_id) && strtolower($l_data[$this->m_table . '__const']) == strtolower($p_id)) {
                        return $l_data;
                    }
                }
            }

            return false;
        }

        if ($p_title !== null) {
            $p_title = strtolower($p_title);

            if (is_array($this->m_cache[$this->m_table])) {
                foreach ($this->m_cache[$this->m_table] as $l_data) {
                    // @see  ID-4842  We use type safe comparison, because "1" !== "01"
                    if (strtolower($l_data[$this->m_table . '__title']) === $p_title) {
                        return $l_data;
                    }
                }
            }

            // If we can't find the given title, we return false.
            return false;
        }

        return $this->m_cache[$this->m_table];
    }

    /**
     * Method for retrieving the raw data from a dialog-table.
     *
     * @param   integer $p_id
     * @param   string  $p_title
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_data_raw($p_id = null, $p_title = null)
    {
        // We cache the data, as soon as this class is instanced.
        return $this->get_dialog($this->m_table, $p_id, $p_title);
    }

    /**
     * Method for (re-)loading the dialog-data.
     *
     * @return  isys_cmdb_dao_dialog
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function load()
    {
        if (!isset($this->m_cache[$this->m_table]) || !$this->m_cache[$this->m_table]) {
            // We cache the data, as soon as this class is instanced.
            $l_res = $this->get_dialog($this->m_table);

            while ($l_row = $l_res->get_row()) {
                $this->m_cache[$this->m_table][$l_row[$this->m_table . '__id']] = $l_row;
                $this->m_cache[$this->m_table][$l_row[$this->m_table . '__id']]['title'] = isys_application::instance()->container->get('language')
                    ->get(trim($l_row[$this->m_table . '__title']));
                $this->m_cache[$this->m_table][$l_row[$this->m_table . '__id']]['title_lower'] = strtolower($this->m_cache[$this->m_table][$l_row[$this->m_table .
                '__id']]['title']);
            }
        }

        return $this;
    }

    /**
     * Method for resetting and reloading the dialog-data.
     *
     * @return  isys_cmdb_dao_dialog
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function reset()
    {
        unset($this->m_cache[$this->m_table], $this->m_cache_parent_tables[$this->m_table]);

        return $this->load();
    }

    /**
     * Setter Method which sets the current table
     *
     * @param $p_table
     *
     * @return $this
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_table($p_table)
    {
        $this->m_table = $p_table;

        return $this;
    }

    /**
     * Getter method which retrieves the current table
     *
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_table()
    {
        return $this->m_table;
    }

    /**
     * Constructor.
     *
     * @param   isys_component_database $p_db
     * @param   string                  $p_table
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function __construct(isys_component_database &$p_db, $p_table = null)
    {
        parent::__construct($p_db);

        if ($p_table !== null) {
            $this->m_table = $p_table;

            // Immediately load the dialog-data.
            $this->load();
        }
    }

    /**
     *
     * @param  string $value
     * @param  string $identifier
     * @param  array  $exclude
     *
     * @return boolean
     * @throws isys_exception_database
     */
    public function entryExists($value, $identifier = null, array $exclude = [])
    {
        $sql = 'SELECT COUNT(*) AS count 
            FROM ' . $this->m_table . '
            WHERE ' . $this->m_table . '__title = ' . $this->convert_sql_text($value);

        if ($identifier !== null) {
            $sql .= ' AND ' . $this->m_table . '__identifier = ' . $this->convert_sql_text($identifier);
        }

        if (count($exclude)) {
            $sql .= ' AND ' . $this->m_table . '__id ' . $this->prepare_in_condition($exclude, true);
        }

        return (bool) $this->retrieve($sql . ';')->get_row_value('count');
    }

    /**
     * Appends data to the cache
     *
     * @param string $table
     * @param int    $id
     * @param array  $data
     */
    public function appendToCache($id, array $data, $table = null)
    {
        $table = $table ?: $this->m_table;
        $this->m_cache[$table][$id] = $data;
        return $this;
    }
}
