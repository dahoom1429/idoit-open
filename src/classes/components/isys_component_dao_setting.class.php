<?php

class isys_component_dao_setting extends isys_component_dao
{
    /**
     * @param   $p_settingID
     * @param   $p_settingKey
     *
     * @return  array
     */
    private static function defaulter($p_settingID, $p_settingKey)
    {
        $l_column = ($p_settingID) ? "isys_setting__id" : "isys_setting__isys_setting_key__id";
        $l_value = ($p_settingID) ? $p_settingID : $p_settingKey;

        return [
            $l_value,
            $l_column
        ];
    }

    /**
     * @param   integer $p_settingID
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_setting_by_key($p_settingID)
    {
        return $this->retrieve('SELECT * FROM isys_setting WHERE isys_setting__isys_setting_key__id = ' . $this->convert_sql_id($p_settingID) . ';');
    }

    /**
     * @param   string $p_setting_constant
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_setting_by_constantname($p_setting_constant)
    {
        $l_sql = 'SELECT * FROM isys_setting
            INNER JOIN isys_setting_key ON isys_setting__isys_setting_key__id = isys_setting_key__id
            WHERE isys_setting_key__const = ' . $this->convert_sql_text($p_setting_constant) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * @param   integer $p_settingID
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_setting_by_id($p_settingID)
    {
        return $this->retrieve('SELECT * FROM isys_setting WHERE isys_setting__id = ' . $this->convert_sql_id($p_settingID) . ';');
    }

    /**
     * @param   integer $p_settingID
     * @param   string  $p_settingKey
     * @param   string  $p_settingConst
     * @param   mixed   $p_defaultValue
     *
     * @return  mixed
     */
    public function get($p_settingID = null, $p_settingKey = null, $p_settingConst = null, $p_defaultValue = false)
    {
        if ($p_settingID) {
            $l_res = $this->get_setting_by_id($p_settingID);
        } else if ($p_settingKey) {
            $l_res = $this->get_setting_by_key($p_settingKey);
        } else {
            $l_res = $this->get_setting_by_constantname($p_settingConst);
        }

        if (is_countable($l_res) && count($l_res) > 0) {
            return $l_res->get_row_value('isys_setting__value');
        }

        return $p_defaultValue;
    }

    /**
     * @param   $p_settingID
     * @param   $p_settingKey
     *
     * @return  mixed
     * @throws  isys_exception_database
     */
    public function exists($p_settingID, $p_settingKey)
    {
        list($l_value, $l_column) = self::defaulter($p_settingID, $p_settingKey);

        $l_res = $this->retrieve("SELECT isys_setting__id FROM isys_setting WHERE " . $l_column . " = " . $this->convert_sql_id($l_value) . ";");

        if (is_countable($l_res) && count($l_res) > 0) {
            return $l_res->get_row_value('isys_setting__id');
        }

        return false;
    }

    /**
     * @param   $p_settingID
     * @param   $p_settingKey
     * @param   $p_value
     *
     * @return  boolean
     * @throws  isys_exception_dao
     */
    public function set($p_settingID, $p_settingKey, $p_value)
    {
        list($l_value, $l_column) = self::defaulter($p_settingID, $p_settingKey);
        $l_op = 'INSERT INTO';
        $l_condition = ",isys_setting__isys_setting_key__id = " . $this->convert_sql_id($l_value) . " ";

        if ($this->exists($p_settingID, $p_settingKey)) {
            $l_op = 'UPDATE';
            $l_condition = " WHERE " . $l_column . " = " . $this->convert_sql_id($l_value) . ";";
        }

        $l_sql = $l_op . ' isys_setting SET isys_setting__value = ' . $this->convert_sql_text($p_value) . $l_condition . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * @return  array
     * @throws  isys_exception_database
     */
    public function get_settings()
    {
        $l_result_array = [];
        $l_sql = "SELECT isys_setting_key__const, isys_setting__value
            FROM isys_setting
            INNER JOIN isys_setting_key ON isys_setting__isys_setting_key__id = isys_setting_key__id
            WHERE TRUE;";

        $l_res = $this->retrieve(($l_sql));

        if (is_countable($l_res) && count($l_res) > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_result_array[constant($l_row['isys_setting_key__const'])] = $l_row['isys_setting__value'];
            }
        }

        return $l_result_array;
    }
}