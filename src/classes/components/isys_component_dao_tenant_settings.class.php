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
class isys_component_dao_tenant_settings extends isys_component_dao_abstract_settings
{
    /**
     * Tenant ID.
     *
     * @var  integer
     */
    protected $m_tenant_id = null;

    /**
     * Tenant ID getter.
     *
     * @return  integer
     */
    public function get_tenant_id()
    {
        return $this->m_tenant_id;
    }

    /**
     * Tenant ID setter.
     *
     * @param   integer $p_tenant_id
     *
     * @return  $this
     */
    public function set_tenant_id($p_tenant_id)
    {
        $this->m_tenant_id = $p_tenant_id;

        return $this;
    }

    /**
     * @param string $p_key
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function remove($p_key)
    {
        $this->update('DELETE FROM isys_settings WHERE isys_settings__key = ' . $this->convert_sql_text($p_key) . ' AND isys_settings__isys_mandator__id = ' .
            $this->convert_sql_id($this->m_tenant_id));

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

            if ($this->m_tenant_id > 0) {
                $l_condition = 'WHERE isys_settings__isys_mandator__id = ' . $this->convert_sql_id($this->m_tenant_id);

                $l_res = $this->retrieve('SELECT * FROM isys_settings ' . $l_condition . ' ORDER BY isys_settings__key ASC;');

                if (is_countable($l_res) && count($l_res)) {
                    while ($l_row = $l_res->get_row()) {
                        if (isys_format_json::is_json($l_row['isys_settings__value'])) {
                            $this->m_cached_settings[$l_row['isys_settings__key']] = isys_format_json::decode($l_row['isys_settings__value'], true);
                        } else {
                            $this->m_cached_settings[$l_row['isys_settings__key']] = $l_row['isys_settings__value'];
                        }
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
    public function set($p_key, $p_value)
    {
        if ($p_value === true) {
            $p_value = 1;
        }

        if ($p_value === false) {
            $p_value = 0;
        }

        if (is_array($p_value) || is_object($p_value)) {
            $p_value = isys_format_json::encode($p_value);
        }

        $l_sql = 'SELECT * FROM isys_settings WHERE isys_settings__key = ' . $this->convert_sql_text($p_key);
        $l_objectUpdate = $l_objectCondition = '';

        if ($this->m_tenant_id > 0) {
            $l_objectCondition = ' AND isys_settings__isys_mandator__id = ' . $this->convert_sql_id($this->m_tenant_id);
            $l_objectUpdate = 'isys_settings__isys_mandator__id = ' . $this->convert_sql_id($this->m_tenant_id) . ', ';

            $l_sql .= $l_objectCondition;
        }
        $res = $this->retrieve($l_sql);
        if (is_countable($res) && count($res) > 0) {
            $l_sql = 'UPDATE isys_settings SET isys_settings__value = ' . $this->convert_sql_text($p_value) . ' WHERE isys_settings__key = ' .
                $this->convert_sql_text($p_key) . $l_objectCondition . ';';
        } else {
            $l_sql = 'INSERT INTO isys_settings SET isys_settings__value = ' . $this->convert_sql_text($p_value) . ', ' . $l_objectUpdate . 'isys_settings__key = ' .
                $this->convert_sql_text($p_key) . ';';
        }

        $this->update($l_sql . ';');

        return $this;
    }

    /**
     * isys_component_dao_tenant_settings constructor.
     *
     * @param  isys_component_database $p_db
     * @param  integer                 $p_tenant_id
     */
    public function __construct(isys_component_database $p_db, $p_tenant_id = null)
    {
        $this->m_tenant_id = $p_tenant_id ?: isys_component_session::instance()
            ->get_mandator_id();

        parent::__construct($p_db);
    }
}