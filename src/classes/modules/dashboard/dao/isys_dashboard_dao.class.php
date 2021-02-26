<?php

/**
 * i-doit
 *
 * Dashboard DAO.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.2.0
 */
class isys_dashboard_dao extends isys_module_dao
{
    /**
     * Method for retrieving widgets.
     *
     * @param   integer $p_widget_id
     * @param   string  $p_identifier
     * @param   string  $p_const
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_data($p_widget_id = null, $p_identifier = null, $p_const = null)
    {
        $l_sql = 'SELECT * FROM isys_widgets WHERE TRUE';

        if ($p_widget_id !== null) {
            $l_sql .= ' AND isys_widgets__id = ' . $this->convert_sql_id($p_widget_id);
        }

        if ($p_identifier !== null) {
            $l_sql .= ' AND isys_widgets__identifier = ' . $this->convert_sql_text($p_identifier);
        }

        if ($p_const !== null) {
            $l_sql .= ' AND isys_widgets__const = ' . $this->convert_sql_text($p_const);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for retrieving widgets.
     *
     * @param   integer $p_widget_id
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_data_by_user_widget_id($p_widget_id)
    {
        $l_sql = 'SELECT * FROM isys_widgets
		    LEFT JOIN isys_widgets_config ON isys_widgets_config__isys_widgets__id = isys_widgets__id
			WHERE isys_widgets_config__id = ' . $this->convert_sql_id($p_widget_id) . ';';

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for retrieving widgets by a given user.
     *
     * @param   integer $p_user_id
     * @param   integer $p_user_config_id
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_widgets_by_user($p_user_id = null, $p_user_config_id = null)
    {
        $l_sql = 'SELECT * FROM isys_widgets_config
			INNER JOIN isys_widgets ON isys_widgets_config__isys_widgets__id = isys_widgets__id
			WHERE TRUE';

        if ($p_user_id !== null) {
            $l_sql .= ' AND isys_widgets_config__isys_obj__id = ' . $this->convert_sql_id($p_user_id);
        }

        if ($p_user_config_id !== null) {
            $l_sql .= ' AND isys_widgets_config__id = ' . $this->convert_sql_id($p_user_config_id);
        }

        return $this->retrieve($l_sql . ' ORDER BY isys_widgets_config__sorting ASC;');
    }

    /**
     * Method for retrieving the default dashboard widgets.
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_widgets_by_default()
    {
        // This is necessary for the "widget loading" logic...
        $l_mapping = [
            '*',
            'isys_widgets__default_config AS isys_widgets_config__configuration',
            'isys_widgets__id AS isys_widgets_config__id',
        ];

        $l_sql = 'SELECT ' . implode(',', $l_mapping) . ' FROM isys_widgets ' . 'WHERE isys_widgets__default = 1 ' . 'ORDER BY isys_widgets__sorting ASC;';

        return $this->retrieve($l_sql);
    }

    /**
     * Method for saving a certain widget configuration.
     *
     * @param   integer $p_id
     * @param   array   $p_config
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function update_user_widget($p_id, array $p_config = [])
    {
        $l_fields = [
            'isys_widgets_config__isys_widgets__id',
            'isys_widgets_config__isys_obj__id',
            'isys_widgets_config__configuration',
            'isys_widgets_config__sorting'
        ];

        $l_query = [];

        foreach ($p_config as $l_field => $l_config) {
            if (in_array($l_field, $l_fields)) {
                $l_query[] = $l_field . ' = ' . (is_numeric($l_config) ? $this->convert_sql_int($l_config) : $this->convert_sql_text($l_config));
            }
        }

        if (count($l_query) > 0) {
            $l_sql = 'UPDATE isys_widgets_config
				SET ' . implode(', ', $l_query) . '
				WHERE isys_widgets_config__id = ' . $this->convert_sql_id($p_id) . ';';

            return ($this->update($l_sql) && $this->apply_update());
        }

        return true;
    }

    /**
     * Method for saving a certain module widget configuration.
     *
     * @param   integer $p_id
     * @param   array   $p_config
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function update_module_widget($p_id, array $p_config = [])
    {
        $l_fields = [
            'isys_widgets__title',
            'isys_widgets__description',
            'isys_widgets__const',
            'isys_widgets__default_config',
            'isys_widgets__sorting',
            'isys_widgets__default'
        ];

        $l_query = [];

        foreach ($p_config as $l_field => $l_config) {
            if (in_array($l_field, $l_fields)) {
                $l_query[] = $l_field . ' = ' . (is_numeric($l_config) ? $this->convert_sql_int($l_config) : $this->convert_sql_text($l_config));
            }
        }

        if (count($l_query) > 0) {
            $l_sql = 'UPDATE isys_widgets
				SET ' . implode(', ', $l_query) . '
				WHERE isys_widgets__id = ' . $this->convert_sql_id($p_id) . ';';

            return ($this->update($l_sql) && $this->apply_update());
        }

        return true;
    }

    /**
     * Method for creating a new widget on a users dashboard.
     *
     * @param   integer $p_person_id
     * @param   integer $p_widget_id
     * @param   string  $p_config
     * @param   integer $p_sorting
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function add_widget_to_dashboard($p_person_id, $p_widget_id, $p_config = '', $p_sorting = 0)
    {
        $l_sql = 'INSERT INTO isys_widgets_config
			SET isys_widgets_config__isys_widgets__id = ' . $this->convert_sql_id($p_widget_id) . ',
			isys_widgets_config__isys_obj__id = ' . $this->convert_sql_id($p_person_id) . ',
			isys_widgets_config__configuration = ' . $this->convert_sql_text($p_config) . ',
			isys_widgets_config__sorting = ' . $this->convert_sql_int($p_sorting) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Removes a widget from the users dashboard.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function remove_widget_from_dashboard($p_id)
    {
        return ($this->update('DELETE FROM isys_widgets_config WHERE isys_widgets_config__id = ' . $this->convert_sql_id($p_id) . ';') && $this->apply_update());
    }

    /**
     * Method for creating a new widget.
     *
     * @param   string $p_title
     * @param   string $p_identifier
     * @param   string $p_const
     * @param   string $p_default_config
     * @param   string $p_description
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function create_new_widget($p_title, $p_identifier, $p_const = '', $p_default_config = '', $p_description = '')
    {
        $p_identifier = strtolower($p_identifier);

        if (empty ($p_const)) {
            $p_const = 'C__WIDGET__' . strtoupper($p_identifier);
        }

        $l_sql = 'INSERT INTO isys_widgets
			SET isys_widgets__title = ' . $this->convert_sql_text($p_title) . ',
			isys_widgets__description = ' . $this->convert_sql_text($p_description) . ',
			isys_widgets__identifier = ' . $this->convert_sql_text($p_identifier) . ',
			isys_widgets__const = ' . $this->convert_sql_text($p_const) . ',
			isys_widgets__default_config = ' . $this->convert_sql_text($p_default_config) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Method for checking, if a widget has been created.
     *
     * @param   string $p_identifier
     * @param   string $p_const
     *
     * @throws  BadMethodCallException
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function widget_exists($p_identifier = null, $p_const = null)
    {
        if ($p_identifier === null && $p_const === null) {
            throw new BadMethodCallException('Please assign a identifier or a constant!');
        }

        $l_condition = [];

        if ($p_identifier !== null) {
            $l_condition[] = 'isys_widgets__identifier = ' . $this->convert_sql_text($p_identifier);
        }

        if ($p_const !== null) {
            $l_condition[] = 'isys_widgets__const = ' . $this->convert_sql_text($p_const);
        }

        // We use "OR" because both, the constant and identifier, may only exist once.
        $l_sql = 'SELECT * FROM isys_widgets WHERE (' . implode(' OR ', $l_condition) . ');';

        return count($this->retrieve($l_sql)) > 0;
    }

    /**
     * Resets the default widgets.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function reset_default_widgets()
    {
        return ($this->update('UPDATE isys_widgets SET isys_widgets__default = 0, isys_widgets__sorting = 99;') && $this->apply_update());
    }

    /**
     * Resets the users dashboard.
     *
     * @param   integer $p_user_id
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function reset_user_dashboard($p_user_id)
    {
        return ($this->update('DELETE FROM isys_widgets_config WHERE isys_widgets_config__isys_obj__id = ' . $this->convert_sql_id($p_user_id) . ';') &&
            $this->apply_update());
    }
}