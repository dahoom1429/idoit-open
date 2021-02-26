<?php

/**
 * i-doit
 *
 * Monitoring DAO.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.3.0
 */
class isys_monitoring_dao_hosts extends isys_component_dao
{
    /**
     * Method for retrieving the monitoring configuration.
     *
     * @param   integer|array $id
     * @param   string        $type
     * @param   boolean       $onlyActive
     * @param   string        $title
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_data($id = null, $type = null, $onlyActive = false, $title = null)
    {
        $sql = 'SELECT * FROM isys_monitoring_hosts WHERE TRUE ';

        if ($id !== null) {
            if (is_array($id)) {
                $sql .= ' AND isys_monitoring_hosts__id ' . $this->prepare_in_condition($id);
            } else {
                $sql .= ' AND isys_monitoring_hosts__id = ' . $this->convert_sql_id($id);
            }
        }

        if ($title !== null && !empty($title))
        {
            $sql .= ' AND isys_monitoring_hosts__title = ' . $this->convert_sql_text($title);
        }

        if ($type !== null) {
            $sql .= ' AND isys_monitoring_hosts__type = ' . $this->convert_sql_text($type);
        }

        if ($onlyActive) {
            $sql .= ' AND isys_monitoring_hosts__active = 1';
        }

        return $this->retrieve($sql . ';');
    }

    /**
     * Method for creating/saving a monitoring host definition.
     *
     * @param   integer $id
     * @param   array   $values
     * @param   string  $typeCondition
     *
     * @return  integer
     * @throws  isys_exception_dao
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function save($id, $values, $typeCondition = null)
    {
        $data = [];

        if ($id === null || empty($id)) {
            $sql = 'INSERT INTO isys_monitoring_hosts SET %s;';
        } else {
            if ($typeCondition !== null && !empty($typeCondition)) {
                $typeCondition = ' AND isys_monitoring_hosts__type = ' . $this->convert_sql_text($typeCondition);
            } else {
                $typeCondition = '';
            }

            $sql = 'UPDATE isys_monitoring_hosts SET %s WHERE isys_monitoring_hosts__id = ' . $this->convert_sql_id($id) . $typeCondition . ';';
        }

        if (count($values) > 0) {
            foreach ($values as $key => $value) {
                $data[] = 'isys_monitoring_hosts__' . $key . ' = ' . $this->convert_sql_text($value);
            }

            $this->update(str_replace('%s', implode(', ', $data), $sql)) && $this->apply_update();
        }

        return $id ?: $this->get_last_insert_id();
    }

    /**
     * This method will remove all configurations, whose IDs are explicitly given as parameter.
     *
     * @param   integer|array $id
     * @param   string        $title
     * @param   string        $type
     *
     * @return  boolean
     * @throws  isys_exception_dao
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function delete($id = null, $title = null, $type = null)
    {
        if (($id === null || empty($id)) && ($title === null || empty($title))) {
            throw new isys_exception_general('You need to provide a ID or a title!');
        }

        $condition = '';

        if ($id !== null && !empty($id)) {
            if (!is_array($id)) {
                $id = [$id];
            }

            $condition .= ' AND isys_monitoring_hosts__id ' . $this->prepare_in_condition($id);
        }

        if ($title !== null && !empty($title)) {
            $condition .= ' AND isys_monitoring_hosts__title = ' . $this->convert_sql_text($title);
        }

        if ($type !== null && !empty($type))
        {
            $condition .= ' AND isys_monitoring_hosts__type = ' . $this->convert_sql_text($type);
        }

        return ($this->update('DELETE FROM isys_monitoring_hosts WHERE TRUE' . $condition . ';') && $this->apply_update());
    }

    /**
     * Method for retrieving the monitoring export configuration.
     *
     * @param   integer|array $p_id
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_export_data($p_id = null)
    {
        $l_sql = 'SELECT * FROM isys_monitoring_export_config WHERE TRUE ';

        if ($p_id !== null) {
            if (is_array($p_id)) {
                $l_sql .= ' AND isys_monitoring_export_config__id ' . $this->prepare_in_condition($p_id);
            } else {
                $l_sql .= ' AND isys_monitoring_export_config__id = ' . $this->convert_sql_id($p_id);
            }
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for finding all children of a given (check_mk) configuration.
     *
     * @param   integer $p_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_child_configurations($p_id)
    {
        $l_return = [];

        $l_res = $this->retrieve('SELECT * FROM isys_monitoring_export_config
			WHERE isys_monitoring_export_config__type = "check_mk"
			AND isys_monitoring_export_config__id != ' . $this->convert_sql_id($p_id) . ';');

        while ($l_row = $l_res->get_row()) {
            if (isys_format_json::is_json_array($l_row['isys_monitoring_export_config__options'])) {
                $l_config = isys_format_json::decode($l_row['isys_monitoring_export_config__options']);

                if ($l_config['master'] == $p_id) {
                    $l_return[] = $l_row;
                }
            }
        }

        return $l_return;
    }

    /**
     * Method for creating/saving a monitoring export definition.
     *
     * @param   integer $p_id
     * @param   array   $p_values
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function save_export_config($p_id = null, array $p_values = [])
    {
        $l_data = [];

        if ($p_id === null || empty($p_id)) {
            $l_sql = 'INSERT INTO isys_monitoring_export_config SET %s;';
        } else {
            $l_sql = 'UPDATE isys_monitoring_export_config SET %s WHERE isys_monitoring_export_config__id = ' . $this->convert_sql_id($p_id) . ';';
        }

        if (count($p_values) > 0) {
            foreach ($p_values as $l_key => $l_value) {
                $l_data[] = 'isys_monitoring_export_config__' . $l_key . ' = ' . $this->convert_sql_text($l_value);
            }

            $this->update(str_replace('%s', implode(', ', $l_data), $l_sql)) && $this->apply_update();
        }

        return $p_id ?: $this->get_last_insert_id();
    }

    /**
     * This method will remove all export configurations, whose IDs are explicitly given as parameter.
     *
     * @param   mixed $p_id
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function delete_export_config($p_id)
    {
        if (!is_array($p_id)) {
            $p_id = [$p_id];
        }

        return ($this->update('DELETE FROM isys_monitoring_export_config WHERE isys_monitoring_export_config__id IN (' . implode(',', array_map('intval', $p_id)) . ');') &&
            $this->apply_update());
    }
}