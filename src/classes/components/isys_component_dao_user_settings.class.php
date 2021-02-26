<?php

/**
 * i-doit
 *
 * Settings DAO.
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.8
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_dao_user_settings extends isys_component_dao_abstract_settings
{
    /**
     * @param $key
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function remove($p_key)
    {
        $this->update('DELETE FROM isys_settings WHERE isys_settings__key = ' . $this->convert_sql_text($p_key) . ' AND isys_settings__isys_obj__id = ' .
            $this->convert_sql_id(isys_application::instance()->session->get_user_id()));

        return $this->apply_update();
    }

    /**
     * Return all settings as an array.
     *
     * @return  array
     */
    public function get_settings()
    {
        if ($this->m_cached_settings === null) {
            $this->m_cached_settings = [];
            $l_query = 'SELECT * FROM isys_settings WHERE isys_settings__isys_obj__id = ' . $this->convert_sql_id(isys_application::instance()->session->get_user_id());

            $l_res = $this->retrieve($l_query . ' ORDER BY isys_settings__key ASC;');

            if ($l_res->count()) {
                while ($l_row = $l_res->get_row()) {
                    if (isys_format_json::is_json_array($l_row['isys_settings__value'])) {
                        $this->m_cached_settings[$l_row['isys_settings__key']] = isys_format_json::decode($l_row['isys_settings__value'], true);
                    } else {
                        $this->m_cached_settings[$l_row['isys_settings__key']] = $l_row['isys_settings__value'];
                    }
                }
            }
        }

        return $this->m_cached_settings;
    }

    /**
     * Save key and value to database.
     *
     * @param   string $p_key
     * @param   mixed  $p_value
     *
     * @return  isys_component_dao_settings
     */
    public function set($p_key, $p_value, $p_user_id = null)
    {
        if ($p_value === true) {
            $p_value = 1;
        }

        if ($p_value === false) {
            $p_value = 0;
        }

        if ($p_user_id === null) {
            $p_user_id = isys_application::instance()->session->get_user_id();
        }

        if (is_array($p_value) || is_object($p_value)) {
            $p_value = isys_format_json::encode($p_value);
        }

        $l_sql = 'SELECT * FROM isys_settings WHERE isys_settings__key = ' . $this->convert_sql_text($p_key);

        $l_objectCondition = ' AND isys_settings__isys_obj__id = ' . $this->convert_sql_id($p_user_id);

        $l_sql .= $l_objectCondition;

        $res = $this->retrieve($l_sql);
        if (is_countable($res) && count($res) > 0) {
            $l_sql = 'UPDATE isys_settings SET isys_settings__value = ' . $this->convert_sql_text($p_value) . ' WHERE isys_settings__key = ' .
                $this->convert_sql_text($p_key) . $l_objectCondition . ';';
        } else {
            $l_sql = 'INSERT INTO isys_settings SET isys_settings__value = ' . $this->convert_sql_text($p_value) . ', isys_settings__isys_obj__id = ' .
                $this->convert_sql_id($p_user_id) . ', ' . 'isys_settings__key = ' . $this->convert_sql_text($p_key) . ';';
        }

        $this->update($l_sql . ';');

        return $this;
    }
}