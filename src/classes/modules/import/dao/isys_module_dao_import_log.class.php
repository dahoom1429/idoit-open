<?php

/**
 * i-doit
 *
 * Import DAO
 *
 * @package    i-doit
 * @subpackage Modules
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 */
class isys_module_dao_import_log extends isys_module_dao
{
    /**
     * Import types
     *
     * @var array
     */
    protected $m_import_types = [];

    /**
     * get_data always retrieves the data of the main table of this module
     */
    public function get_data()
    {
        return $this->retrieve('SELECT * FROM isys_import LIMIT 1000;');
    }

    /**
     * Retrieve import type by constant
     *
     * @param string $p_const
     *
     * @return string
     */
    public function get_import_type_by_const($p_const)
    {
        if (!isset($this->m_import_types[$p_const])) {
            $l_sql = 'SELECT isys_import_type__id FROM isys_import_type WHERE isys_import_type__const = ' . $this->convert_sql_text($p_const);

            $l_res = $this->retrieve($l_sql);
            if ($l_res) {
                $this->m_import_types[$p_const] = $l_res->get_row_value('isys_import_type__id');
            }
        }

        return $this->m_import_types[$p_const] ?: null;
    }

    /**
     * Adds a new entry
     *
     * @param int    $p_import_type
     * @param string $p_title
     * @param int    $p_import_profile
     *
     * @return bool|int
     */
    public function add_import_entry($p_import_type, $p_title = null, $p_import_profile = null)
    {
        $l_insert = 'INSERT INTO isys_import SET
            isys_import__title = ' . $this->convert_sql_text($p_title) . ', 
            isys_import__import_date = NOW(), 
            isys_import__isys_import_type__id = ' . $this->convert_sql_id($p_import_type) . ', 
            isys_import__isys_import_profile__id = ' . $this->convert_sql_id($p_import_profile) . ';';

        if ($this->update($l_insert) && $this->apply_update()) {
            return $this->get_last_insert_id();
        }

        return false;
    }
}
