<?php

/**
 * i-doit
 *
 * Status DAO.
 *
 * @package    i-doit
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_status extends isys_cmdb_dao
{

    /**
     * Cache of all cmdb status that have been requested
     *
     * @var array
     */
    protected $m_cmdb_status_cache = [];

    /**
     * Memory Resource cache for all cmdb status
     *
     * @var isys_component_dao_result[]
     */
    protected $m_cmdb_status_dao_result = null;

    /**
     * Const cache
     *
     * @var array
     */
    protected $m_const_cache = [];

    /**
     * @param int $p_status
     *
     * @return array
     */
    public function get_cmdb_status_as_array($p_status = null)
    {
        if (!is_countable($this->m_cmdb_status_cache) || !count($this->m_cmdb_status_cache)) {
            $l_data = $this->get_cmdb_status();
            while ($l_row = $l_data->get_row()) {
                $this->m_cmdb_status_cache[$l_row['isys_cmdb_status__id']] = $l_row;
            }
        }

        if ($p_status) {
            if (isset($this->m_cmdb_status_cache[$p_status])) {
                return $this->m_cmdb_status_cache[$p_status];
            } else {
                return null;
            }
        } else {
            return $this->m_cmdb_status_cache;
        }
    }

    /**
     * Return CMDB/ITIL Status
     *
     * @param   mixed  $p_status
     * @param   string $p_condition
     *
     * @return  isys_component_dao_result
     */
    public function get_cmdb_status($p_status = null, $p_condition = '')
    {
        if (isset($this->m_cmdb_status_dao_result[$p_status][$p_condition])) {
            $this->m_cmdb_status_dao_result[$p_status][$p_condition]->reset_pointer();

            return $this->m_cmdb_status_dao_result[$p_status][$p_condition];
        }

        $l_sql = "SELECT isys_cmdb_status__id, isys_cmdb_status__title, isys_cmdb_status__const, isys_cmdb_status__color, isys_cmdb_status__editable, isys_cmdb_status__status, isys_cmdb_status__description FROM isys_cmdb_status WHERE TRUE";

        if ($p_status !== null) {
            if (is_numeric($p_status)) {
                $l_sql .= " AND isys_cmdb_status__id = " . $this->convert_sql_id($p_status);
            } else {
                $l_sql .= " AND isys_cmdb_status__const = " . $this->convert_sql_text($p_status);
            }
        }

        $this->m_cmdb_status_dao_result[$p_status][$p_condition] = $this->retrieve($l_sql . ' ' . $p_condition . ';');

        return $this->m_cmdb_status_dao_result[$p_status][$p_condition];
    }

    /**
     * Description.
     *
     * @param   string $p_const
     *
     * @return  isys_component_dao_result
     */
    public function get_cmdb_status_by_const($p_const)
    {
        return $this->get_cmdb_status(null, 'AND isys_cmdb_status__const = ' . $this->convert_sql_text($p_const));
    }

    /**
     * Retrieves a CMDB status ID by its constant.
     *
     * @param   string $p_const
     *
     * @return  integer
     */
    public function get_cmdb_status_by_const_as_int($p_const)
    {
        if (!isset($this->m_const_cache[$p_const])) {
            $l_return = $this->get_cmdb_status(null, 'AND isys_cmdb_status__const = ' . $this->convert_sql_text($p_const))
                ->get_row();

            $this->m_const_cache[$p_const] = (is_array($l_return) ? $l_return['isys_cmdb_status__id'] : null);
        }

        return $this->m_const_cache[$p_const];
    }

    /**
     * Return CMDB/ITIL Status.
     *
     * @deprecated  Just use get_cmdb_status().
     *
     * @param       integer $p_id
     * @param       string  $p_condition
     *
     * @return      isys_component_dao_result
     */
    public function get_status($p_id = null, $p_condition = '')
    {
        return $this->get_cmdb_status($p_id, $p_condition);
    }

    /**
     * Description.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_new_status
     *
     * @return  mixed
     */
    public function add_change($p_obj_id, $p_new_status)
    {
        if ($p_obj_id > 0) {
            $l_sql = 'INSERT INTO isys_cmdb_status_changes
				SET isys_cmdb_status_changes__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ',
				isys_cmdb_status_changes__isys_cmdb_status__id = ' . $this->convert_sql_id($p_new_status) . ';';

            if ($this->update($l_sql) && $this->apply_update()) {
                return $this->get_last_insert_id();
            }
        }

        return false;
    }

    /**
     * Method for getting all CMDB status changes of one or more objects.
     *
     * @param   mixed $p_obj May be an integer or a array of integers.
     *
     * @return  isys_component_dao_result
     */
    public function get_changes_by_obj_id($p_obj)
    {
        if (!is_array($p_obj)) {
            $p_obj = [$p_obj];
        }

        $l_sql = 'SELECT * FROM isys_cmdb_status_changes
			LEFT JOIN isys_obj ON isys_obj__id = isys_cmdb_status_changes__isys_obj__id
			LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_obj__id ' . $this->prepare_in_condition($p_obj) . '
			ORDER BY isys_cmdb_status_changes__timestamp ASC;';

        return $this->retrieve($l_sql);
    }

    /**
     * Description.
     *
     * @param   integer $p_id
     * @param   string  $p_const
     * @param   string  $p_title
     * @param   string  $p_color
     *
     * @return  boolean
     */
    public function save($p_id, $p_const, $p_title, $p_color = "FFFFFF")
    {
        $p_const = preg_replace('/[^0-9a-z_]/i', '', $p_const);
        if (is_numeric($p_const[0])) {
            $p_const = 'C' . $p_const;
        }

        $l_sql = 'UPDATE isys_cmdb_status
			SET isys_cmdb_status__const = ' . $this->convert_sql_text($p_const) . ',
			isys_cmdb_status__title = ' . $this->convert_sql_text($p_title) . ',
			isys_cmdb_status__color = ' . $this->convert_sql_text($p_color) . '
			WHERE isys_cmdb_status__id = ' . $this->convert_sql_id($p_id) . ';';

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * Description.
     *
     * @param   string $p_const
     * @param   string $p_title
     * @param   string $p_color
     *
     * @return  mixed
     */
    public function create($p_const, $p_title, $p_color)
    {
        $l_sql = 'INSERT INTO isys_cmdb_status
			SET isys_cmdb_status__const = ' . $this->convert_sql_text($p_const) . ',
			isys_cmdb_status__title = ' . $this->convert_sql_text($p_title) . ',
			isys_cmdb_status__color = ' . $this->convert_sql_text($p_color) . ',
			isys_cmdb_status__editable = 1;';

        if ($this->update($l_sql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        }

        return false;
    }

    /**
     * Description.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function delete_status($p_id)
    {
        $this->update('UPDATE isys_obj SET isys_obj__isys_cmdb_status__id = (SELECT isys_cmdb_status__id FROM isys_cmdb_status WHERE isys_cmdb_status__const = \'C__CMDB_STATUS__IDOIT_STATUS\') WHERE isys_obj__isys_cmdb_status__id = ' .
            $this->convert_sql_int($p_id) . ';');

        return ($this->update('DELETE FROM isys_cmdb_status WHERE isys_cmdb_status__id = ' . $this->convert_sql_id($p_id) . ';') && $this->apply_update());

    }

    /**
     * Description.
     *
     * @param   integer $p_status_id
     *
     * @return  mixed
     */
    public function get_cmdb_status_color($p_status_id)
    {
        return $this->get_cmdb_status($p_status_id)
            ->get_row_value('isys_cmdb_status__color');
    }
}