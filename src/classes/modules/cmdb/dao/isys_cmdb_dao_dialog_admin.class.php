<?php

/**
 * i-doit
 *
 * Dialog Admin
 *
 * @package     i-doit
 * @subpackage  CMDB_Low-Level_API
 * @author      Dennis Stuecken <dstuecken@synetics.de
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_dialog_admin extends isys_cmdb_dao
{
    /**
     * Cache which contains the table fields of dialog tables
     *
     * @var array
     */
    private static $m_table_fields = [];

    /**
     * Relation addons.
     *
     * @param   integer $p_id
     * @param   string  $p_master
     * @param   string  $p_slave
     *
     * @return  boolean
     */
    public function mod_relation_type($p_id, $p_master, $p_slave)
    {
        $l_sql = 'UPDATE isys_relation_type SET ' . 'isys_relation_type__master = ' . $this->convert_sql_text($p_master) . ', ' . 'isys_relation_type__slave = ' .
            $this->convert_sql_text($p_slave) . ' ' . 'WHERE isys_relation_type__id = ' . $this->convert_sql_id($p_id);

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Creates a new dialog entry.
     *
     * @param   string  $p_table
     * @param   string  $p_title
     * @param   integer $p_sort
     * @param   integer $p_const
     * @param   integer $p_status
     * @param   integer $p_parent_id
     * @param   string  $p_identifier
     *
     * @return  mixed
     */
    public function create($p_table, $p_title, $p_sort, $p_const, $p_status, $p_parent_id = null, $p_identifier = null, $p_description = '', array $p_custom_data = [])
    {
        if (!empty($p_table)) {
            $l_fields = $this->get_table_fields($p_table);

            $l_sql = 'INSERT INTO ' . $p_table . ' SET ' . $p_table . '__title = ' . $this->convert_sql_text(trim($p_title)) . ' ';

            if (mb_strlen($p_const) && in_array($p_table . '__const', $l_fields)) {
                $p_const = $this->get_constant_prefix($p_table) . $this->format_constant($p_table, $p_const);

                // If it already exists than we add a timestamp to the constant
                if (defined($p_const)) { // @See ID-3363
                    $p_const .= time();
                }

                $l_sql .= ',' . $p_table . '__const = ' . $this->convert_sql_text($p_const) . ' ';
            }

            if (!empty($p_sort) && in_array($p_table . '__sort', $l_fields)) {
                $l_sql .= ',' . $p_table . '__sort = ' . $this->convert_sql_int($p_sort) . ' ';
            }

            if (!empty($p_description) && in_array($p_table . '__description', $l_fields)) {
                $l_sql .= ',' . $p_table . '__description = ' . $this->convert_sql_text($p_description) . ' ';
            }

            if (in_array($p_table . '__status', $l_fields)) {
                $l_sql .= ',' . $p_table . '__status = ' . $this->convert_sql_int($p_status) . ' ';
            }

            if (is_array($p_custom_data) && count($p_custom_data)) {
                foreach ($p_custom_data as $l_key => $l_value) {
                    $l_sql .= ',' . $p_table . '__' . $l_key . ' = ' . $this->convert_sql_text($l_value) . ' ';
                }
            }

            if (!empty($p_parent_id)) {
                $l_parent_table = $this->get_parent_table($p_table);
                $l_sql .= ',' . $p_table . '__' . $l_parent_table . '__id = ' . $this->convert_sql_id($p_parent_id) . ' ';
            }

            if (!empty($p_identifier)) {
                $l_sql .= ',' . $p_table . '__identifier = ' . $this->convert_sql_text($p_identifier);
            }

            if ($this->update($l_sql) && $this->apply_update()) {
                return $this->get_last_insert_id();
            }
        }

        return false;
    }

    /**
     * Saves an existing dialog entry.
     *
     * @param   integer $p_id
     * @param   string  $p_table
     * @param   string  $p_title
     * @param   integer $p_sort
     * @param   integer $p_const
     * @param   integer $p_status
     * @param   integer $p_parent_id
     * @param   string  $p_description
     *
     * @return  boolean
     */
    public function save($p_id, $p_table, $p_title, $p_sort, $p_const, $p_status, $p_parent_id = null, $p_description = '')
    {
        if (!empty($p_table)) {
            $l_fields = $this->get_table_fields($p_table);

            $l_sql = 'UPDATE ' . $p_table . ' SET ' . $p_table . '__title = ' . $this->convert_sql_text($p_title) . ', ' . $p_table . '__sort = ' .
                $this->convert_sql_int($p_sort) . ', ';

            if (mb_strlen($p_const)) {
                $l_sql .= $p_table . '__const = ' . $this->convert_sql_text($p_const) . ', ';
            }

            if (!empty($p_description) && in_array($p_table . '__description', $l_fields)) {
                $l_sql .= $p_table . '__description = ' . $this->convert_sql_text($p_description) . ', ';
            }

            $l_sql .= $p_table . '__status = ' . $this->convert_sql_int($p_status) . ' ';

            if (!empty($p_parent_id)) {
                $l_parent_table = $this->get_parent_table($p_table);
                $l_sql .= ',' . $p_table . '__' . $l_parent_table . '__id = ' . $this->convert_sql_id($p_parent_id) . ' ';
            }

            $l_sql .= 'WHERE (' . $p_table . '__id = ' . $this->convert_sql_id($p_id) . ');';

            return $this->update($l_sql) && $this->apply_update();
        } else {
            return false;
        }
    }

    /**
     * Method for getting certain constant prefixes (like for object type groups).
     * This method should only be used when creating new entries - when updating existing ones, the constant field should be disabled.
     *
     * @param string $p_table
     *
     * @return string
     */
    protected function get_constant_prefix($p_table)
    {
        switch ($p_table) {
            case 'isys_obj_type_group':
                return 'C__OBJTYPE_GROUP__SD_';

            default:
                return '';
        }
    }

    /**
     * Method for formatting certain constants (uppercase, no "non-word-characters", etc.).
     * This method should only be used when creating new entries - when updating existing ones, the constant field should be disabled.
     *
     * @param string $p_table
     * @param string $p_const
     *
     * @return string
     */
    protected function format_constant($p_table, $p_const)
    {
        switch ($p_table) {
            case 'isys_obj_type_group':
                return strtoupper(preg_replace('~\W~', '_', $p_const));

            default:
                return $p_const;
        }
    }

    /**
     * Deletes a dialog entry.
     *
     * @param   string  $p_table
     * @param   integer $p_id
     *
     * @throws  Exception
     * @return  boolean
     */
    public function delete($p_table, $p_id)
    {
        if ($this->check_delete($p_table, $p_id)) {
            $l_sql = 'DELETE FROM ' . $p_table . ' WHERE ' . '(' . $p_table . '__id = ' . $this->convert_sql_id($p_id) . ');';

            try {
                /**
                 * Coded foreign key check for custom dialog+ entries
                 */
                if ($p_table == 'isys_dialog_plus_custom') {
                    // Get dialog identifier
                    $l_dialog_identifier = $this->retrieve(
                        'SELECT isys_dialog_plus_custom__identifier AS identifier ' .
                        'FROM isys_dialog_plus_custom ' .
                        'WHERE isys_dialog_plus_custom__id = '.$this->convert_sql_id($p_id)
                    )->get_row_value('identifier');

                    if ($l_dialog_identifier) {
                        $l_custom_dao = new isys_cmdb_dao_category_g_custom_fields($this->get_database_component());

                        // Check for usage
                        if ($l_custom_dao->checkDialogEntryInUse($l_dialog_identifier, $p_id)) {
                            // Let's throw an exception to use the default procedure
                            throw new Exception();
                        }
                    }
                }

                // Delete dialog+ entry
                return $this->update($l_sql) && $this->apply_update();
            } catch (Exception $e) {
                throw new Exception('Could not delete, because the entry is being used.');
            }
        } else {
            throw new Exception('Could not delete, because the constant is used for internal calculation.');
        }
    }

    /**
     * Get DialogEntry by title
     *
     * @param $p_table
     * @param $p_title
     *
     * @return isys_component_dao_result
     */
    public function get_by_title($p_table, $p_title)
    {
        $l_sql = 'SELECT * FROM ' . $p_table . ' ';

        if (($l_parent_table = $this->get_parent_table($p_table))) {
            $l_sql .= 'LEFT JOIN ' . $l_parent_table . ' ON ' . $p_table . '__' . $l_parent_table . '__id = ' . $l_parent_table . '__id ';
        }

        $l_sql .= ' WHERE TRUE';

        if (!empty($p_title)) {
            $l_sql .= ' AND (' . $p_table . '__title = ' . $this->convert_sql_text($p_title) . ')';
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Retrieve data from given table.
     *
     * @param   string  $p_table
     * @param   integer $p_id
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_table, $p_id = null, $p_condition = null)
    {
        if (isys_cmdb_dao::instance($this->m_db)->fieldsExistsInTable($p_table, [$p_table . '__const'])) {
            $sqlDeleatable = ' IF(LOCATE(\'C__\', ' . $p_table . '__const) > 0, \'' . isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__NO') . '\', \'' . isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__YES') . '\') AS deleteable ';
        } else {
            $sqlDeleatable = '\'' . isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__YES') . '\' AS deleteable ';
        }
        $l_sql = 'SELECT *, ' . $sqlDeleatable . ' FROM ' . $p_table . ' ';

        if (($l_parent_table = $this->get_parent_table($p_table))) {
            $l_sql .= 'LEFT JOIN ' . $l_parent_table . ' ON ' . $p_table . '__' . $l_parent_table . '__id = ' . $l_parent_table . '__id ';
        }

        $l_sql .= ' WHERE TRUE';

        if (!empty($p_id)) {
            $l_sql .= ' AND (' . $p_table . '__id = ' . $this->convert_sql_id($p_id) . ')';
        }

        if ($p_condition) {
            $l_sql .= ' AND (' . $p_condition . ')';
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Retrieve fields from table.
     *
     * @param   string $p_table
     *
     * @return  array
     */
    public function get_table_fields($p_table)
    {
        if (isset(self::$m_table_fields[$p_table])) {
            return self::$m_table_fields[$p_table];
        }

        $l_fields = [];
        $l_res = $this->retrieve('SHOW FIELDS FROM ' . $p_table . ';');

        while ($l_row = $l_res->get_row()) {
            $l_fields[] = $l_row['Field'];
        }
        self::$m_table_fields[$p_table] = $l_fields;

        return $l_fields;
    }

    /**
     * Get parent table if exists.
     *
     * @param   string $p_table
     *
     * @return  mixed
     */
    public function get_parent_table($p_table)
    {
        $l_table_fields = $this->get_table_fields($p_table);

        foreach ($l_table_fields as $l_field) {
            $l_field_arr = explode('__isys_', $l_field);

            if (count($l_field_arr) > 1) {
                $l_parent_table = 'isys_' . substr($l_field_arr[1], 0, strpos($l_field_arr[1], '__id'));

                return $l_parent_table;
            }
        }

        return false;
    }

    /**
     * Get all custom fields of type `dialog`.
     *
     * @see        Better use "isys_dialog_admin_dao->get_custom_dialogs()".
     * @deprecated This is never used (i think)
     */
    public function get_custom_dialogs()
    {
        $l_res = $this->retrieve('SELECT isysgui_catg_custom__config FROM isysgui_catg_custom WHERE TRUE;');

        $l_custom_catg = [];

        if (is_countable($l_res) && count($l_res) > 0) {
            while (($l_row = $l_res->get_row())) {
                $l_config = unserialize($l_row['isysgui_catg_custom__config']);
                if (is_array($l_config) && count($l_config) > 0) {
                    foreach ($l_config as $l_field) {
                        if ($l_field['type'] == 'f_popup' && $l_field['popup'] == 'dialog_plus') {
                            $l_custom_catg[] = [
                                'title'      => $l_field['title'],
                                'identifier' => $l_field['identifier'],
                            ];
                        }
                    }
                }
            }
        }

        return $l_custom_catg;
    }

    /**
     *
     * @param   string $p_identifier
     *
     * @return  isys_component_dao_result
     */
    public function get_custom_dialog_data($p_identifier = null)
    {
        if (!empty($p_identifier)) {
            return $this->retrieve('SELECT * FROM isys_dialog_plus_custom WHERE isys_dialog_plus_custom__identifier = ' . $this->convert_sql_text($p_identifier) . ';');
        }

        return false;
    }

    /**
     * Cache dialog table content
     *
     * @var array
     */
    private static $m_table_content = [];

    /**
     * @param string $p_table
     * @param string $p_value
     * @param string $p_identifier
     * @param bool   $p_partial_search
     * @param int    $p_parentID
     *
     * @param bool   $considerParentTable
     *
     * @return int|mixed|string
     * @throws Exception
     */
    public function get_id($p_table, $p_value, $p_identifier = null, $p_partial_search = true, $p_parentID = null, $considerParentTable = true)
    {
        if (isset($p_table)) {
            $lang = isys_application::instance()->container->get('language');

            // Check whether parentTable should be considered or not
            if ($considerParentTable) {
                $l_parent_table = $this->get_parent_table($p_table);
            }

            $l_id = null;

            if (!isset(self::$m_table_content[$p_table])) {
                // Retrieve dialog data.
                if (empty($p_identifier)) {
                    $l_res = $this->get_data($p_table);
                } else {
                    $l_res = $this->get_custom_dialog_data($p_identifier);
                }

                if ($l_res->num_rows()) {
                    // Get dialog table data.
                    while ($l_row = $l_res->get_row()) {
                        self::$m_table_content[$p_table][$l_row[$p_table . "__id"]] = $lang->get($l_row[$p_table . "__title"]);

                        // Add table entry with parentid + title as Index
                        if ($l_parent_table && isset($l_row[$p_table . "__" . $l_parent_table . "__id"])) {
                            // For check if $p_value is a string
                            self::$m_table_content[$p_table][$l_row[$p_table . "__" . $l_parent_table . "__id"] . "-" .
                            $lang->get($l_row[$p_table . "__title"])] = $l_row[$p_table . "__id"];

                            // For check if $p_value is numeric
                            self::$m_table_content[$p_table][$l_row[$p_table . "__" . $l_parent_table . "__id"] . "_" . $l_row[$p_table . "__id"]] = $l_row[$p_table . "__id"];
                        }
                    }
                }
            }

            if ($p_parentID > 0) {
                // Cases if $p_value is numeric and case if $p_value is a string
                if (is_numeric($p_value) && isset(self::$m_table_content[$p_table][$p_parentID . "_" . $p_value])) {
                    $l_id = self::$m_table_content[$p_table][$p_parentID . "_" . $p_value];
                } elseif (is_string($p_value) && isset(self::$m_table_content[$p_table][$p_parentID . "-" . $p_value])) {
                    $l_id = self::$m_table_content[$p_table][$p_parentID . "-" . $p_value];
                }
            } elseif ($p_parentID === null && !($l_id = array_search($p_value, self::$m_table_content[$p_table] ?: [])) &&
                isset(self::$m_table_content[$p_table][$p_value])) {
                $l_id = $p_value;
            }

            // Start partial search
            if (!$l_id && $p_partial_search) {
                foreach (self::$m_table_content[$p_table] as $l_table_id => $l_table_title) {
                    if ($p_parentID > 0) {
                        if (is_numeric($p_value) && $l_table_title == $p_value && strpos($l_table_id, $p_parentID . '-') !== false) {
                            $l_id = $l_table_title;
                            break;
                        } elseif ($p_value && $l_table_id == $p_parentID . '-' . $p_value) {
                            $l_id = $l_table_title;
                            break;
                        }
                    } else {
                        if (is_numeric($p_value) && $p_value == $l_table_id) {
                            $l_id = $l_table_id;
                            break;
                        } elseif ($p_value && stristr($l_table_title, $p_value)) {
                            $l_id = $l_table_id;
                            break;
                        }
                    }
                }
            }

            if ($l_id) {
                return $l_id;
            }

            $l_id = $this->create($p_table, $p_value, 50, null, C__RECORD_STATUS__NORMAL, $p_parentID, $p_identifier);

            self::$m_table_content[$p_table][$l_id] = $p_value;

            // @see  API-63  We reset the cache for the next request, because the "parent-child" ID matching is a bit complicated.
            if ($p_parentID > 0) {
                unset(self::$m_table_content[$p_table]);
            }

            return $l_id;
        } else {
            return $p_value;
        }
    }

    /**
     * Check if a entry may be deleted.
     *
     * @param   string  $p_table
     * @param   integer $p_id
     *
     * @return  boolean
     * @throws  Exception
     */
    private function check_delete($p_table, $p_id)
    {
        $l_strConst = "";

        // Check if entry is allowed to be deleted.
        $l_sql = 'SELECT ' . $p_table . '__const ' . 'FROM ' . $p_table . ' ' . 'WHERE ' . $p_table . '__id = ' . $this->convert_sql_id($p_id) . '; ';

        $l_ret = $this->retrieve($l_sql);

        if ($l_ret->num_rows() > 0) {
            $l_row = $l_ret->get_row(IDOIT_C__DAO_RESULT_TYPE_ROW);
            $l_strConst = $l_row[0];
        }

        if (strpos($l_strConst, 'C__') === 0) {
            throw new Exception('Could not delete dialog entry: Entries containing constants are mandatory for i-doit.');
        }

        return true;
    }
}
