<?php

use idoit\Context\Context;
use idoit\Module\Cmdb\Interfaces\Printable;

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_custom_fields extends isys_cmdb_dao_category_global implements Printable
{
    /**
     * Custom configuration.
     *
     * @var  array  Defaults to an empty array.
     */
    protected static $m_config = [];

    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'custom_fields';

    /**
     * Custom category info
     *
     * @var array
     */
    protected $m_category_config = [];

    /**
     * Custom category identifier.
     *
     * @var  integer  Defaults to null.
     */
    protected $m_catg_custom_id = null;

    /**
     * Contains the relation type data
     *
     * @var array
     */
    private $m_relation_type_data = [];

    /**
     * Category's template.
     *
     * @var  string
     */
    protected $m_tpl = 'catg__custom_fields';

    /**
     * Method for retrieving the name of a category.
     *
     * @return string
     * @throws isys_exception_database
     */
    public function getCategoryTitle()
    {
        if ($this->categoryTitle === null) {
            $sql = 'SELECT isysgui_catg_custom__title AS title 
                FROM isysgui_catg_custom
                WHERE isysgui_catg_custom__id = ' . $this->convert_sql_id($this->m_catg_custom_id) . ';';

            $this->categoryTitle = $this->retrieve($sql)->get_row_value('title');
        }

        return $this->categoryTitle;
    }

    /**
     * Callback function for dialog properties
     *
     * @param isys_request $p_request
     * @param              $p_arr
     *
     * @return array
     * @internal param $p_identifier
     */
    public function callback_property_dialog_field(isys_request $p_request, $p_arr)
    {
        global $g_comp_database;
        $l_data = [];

        $l_identifier = substr($p_arr[0], strpos($p_arr[0], 'c'), strlen($p_arr[0]));
        $this->set_catg_custom_id($p_arr[1]);

        $l_catg = isys_factory::get_instance('isys_custom_fields_dao', $g_comp_database)
            ->get_data($this->get_catg_custom_id())
            ->get_row();
        $l_config = unserialize($l_catg['isysgui_catg_custom__config']);

        $l_sql = 'SELECT * FROM isys_dialog_plus_custom ' . 'WHERE isys_dialog_plus_custom__identifier = ' . $this->convert_sql_text($l_config[$l_identifier]['identifier']);

        /**
         * ID-6180
         *
         * THe following routine prevent retrieving all available
         * dialog entries. In cases of approximately 5k entries
         * and some category entries the category list will struggle.
         */

        // Get selected ids
        $selectedIds = $p_request->get_data('selectedDialogIds');

        // Check whether selectedIds is an array
        if (is_array($selectedIds)) {
            // Filter to get only valid ids
            $selectedIds = array_map(function ($value) {
                return (int)$value;
            }, array_filter($selectedIds, 'is_numeric'));

            // Check whether there are any valid ids
            if (is_countable($selectedIds) && count($selectedIds)) {
                $l_sql .= ' AND isys_dialog_plus_custom__id IN('. implode(',', $selectedIds) .')';
            } else {
                // Prevent fetching all entries if no selection was made
                $l_sql .= ' AND FALSE';
            }
        }

        $l_res = $this->retrieve($l_sql);
        while ($l_row = $l_res->get_row()) {
            $l_data[$l_row['isys_dialog_plus_custom__id']] = $l_row['isys_dialog_plus_custom__title'];
        }

        return $l_data;
    }

    /**
     * Creates new custom field.
     *
     * @param   integer $p_obj_id      Object identifier
     * @param   integer $p_custom_id   Custom category identifier
     * @param   string  $p_key         Field key
     * @param   string  $p_value       Field value
     * @param   string  $p_type        Field type
     * @param   integer $p_status      Record status
     * @param   string  $p_description Description
     * @param   integer $p_data_id
     *
     * @return  mixed  Last inserted identifier (integer) or false (boolean).
     * @throws  isys_exception_dao
     * @version Van Quyen Hoang    <qhoang@i-doit.org>
     */
    public function create($p_obj_id, $p_custom_id, $p_key, $p_value, $p_type, $p_status = C__RECORD_STATUS__NORMAL, $p_description = '', $p_data_id = 1)
    {
        if (!isset(self::$m_config[$p_custom_id])) {
            $this->set_config($p_custom_id);
        }

        $l_sql = "INSERT INTO isys_catg_custom_fields_list SET
            isys_catg_custom_fields_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id) . ",
            isys_catg_custom_fields_list__isysgui_catg_custom__id = " . $this->convert_sql_id($p_custom_id) . ",
            isys_catg_custom_fields_list__field_key = " . $this->convert_sql_text($p_key) . ",
            isys_catg_custom_fields_list__field_content = " . $this->convert_sql_text($p_value) . ",
            isys_catg_custom_fields_list__field_type = " . $this->convert_sql_text($p_type) . ",
            isys_catg_custom_fields_list__status = " . $this->convert_sql_int($p_status) . ",
            isys_catg_custom_fields_list__description = " . $this->convert_sql_text($p_description) . ",
            isys_catg_custom_fields_list__data__id = " . $this->convert_sql_id($p_data_id) . ";";

        if ($this->update($l_sql)) {
            if ($this->apply_update()) {
                $this->m_strLogbookSQL = $l_sql;

                return $this->get_last_insert_id();
            }
        }

        return false;
    }

    /**
     * Gets custom category identifier.
     *
     * @return  integer
     */
    public function get_catg_custom_id()
    {
        return $this->m_catg_custom_id;
    }

    /**
     * Sets custom category identifier.
     *
     * @param  int $p_custom_id Custom category identifier
     *
     * @return $this
     */
    public function set_catg_custom_id($p_custom_id)
    {
        $this->m_catg_custom_id = $p_custom_id;

        $this->categoryTitle = null;

        if (isset($this->m_cached_properties)) {
            $this->unset_properties();
        }

        return $this;
    }

    /**
     * Fetches custom category configuration from database.
     *
     * @param int $p_custom_id Custom category identifier
     *
     * @return array
     */
    public function get_config($p_custom_id)
    {
        if ($p_custom_id > 0) {
            if (!isset(self::$m_config[$p_custom_id])) {
                $this->set_config($p_custom_id);
            }

            return self::$m_config[$p_custom_id];
        } else {
            return [];
        }
    }

    /**
     * Sets custom category configuration into member variable
     *
     * @param $p_custom_id
     *
     * @return array
     */
    public function set_config($p_custom_id)
    {
        return self::$m_config[$p_custom_id] = isys_custom_fields_dao::instance($this->m_db)
            ->get_config($p_custom_id);
    }

    /**
     * Set category info
     *
     * @param $customId
     */
    public function setCategoryInfo($customId)
    {
        $this->m_category_config = isys_custom_fields_dao::instance($this->m_db)->get_data($customId)->get_row();
    }

    /**
     * Fetches custom category info from db
     *
     * @param $p_custom_id
     *
     * @return mixed
     */
    public function get_category_info($p_custom_id)
    {
        // @see  ID-6697  It is possible that a different category info was cached before.
        if (!isset($this->m_category_config['isysgui_catg_custom__id']) ||
            !is_countable($this->m_category_config) ||
            count($this->m_category_config) === 0 ||
            $this->m_category_config['isysgui_catg_custom__id'] != $p_custom_id) {
            $this->setCategoryInfo($p_custom_id);
        }

        return $this->m_category_config;
    }

    /**
     * Fetches category data by key from database.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_custom_id
     * @param   string  $p_key
     * @param   integer $p_data_id
     *
     * @return  isys_component_dao_result
     */
    public function get_data_by_key($p_obj_id, $p_custom_id, $p_key, $p_data_id)
    {
        return $this->get_data($p_data_id, $p_obj_id, 'AND isys_catg_custom_fields_list__field_key = ' . $this->convert_sql_text($p_key) . '
			AND isys_catg_custom_fields_list__isysgui_catg_custom__id = ' . $this->convert_sql_id($p_custom_id));
    }

    /**
     * Updates an existing custom field.
     *
     * @param   integer $p_id          Category data identifier
     * @param   integer $p_custom_id   Custom category identifier
     * @param   string  $p_key         Field key
     * @param   string  $p_value       Field value
     * @param   string  $p_type        Field type
     * @param   integer $p_status      Record status
     * @param   string  $p_description Description. Default to ''.
     * @param   integer $p_data_id
     *
     * @return  boolean
     * @throws  Exception
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_dao
     * @throws  isys_exception_database
     * @version Van Quyen Hoang    <qhoang@i-doit.org>
     */
    public function save($p_id = null, $p_custom_id, $p_key, $p_value, $p_type, $p_status, $p_description = '', $p_data_id = null)
    {
        $l_return = false;

        try {
            if (!isset(self::$m_config[$p_custom_id])) {
                $this->set_config($p_custom_id);
            }

            if (self::$m_config[$p_custom_id][$p_key]['multiselection'] > 0) {
                $daoDialog = isys_cmdb_dao_dialog_admin::instance($this->m_db);
                $l_assigned_objects = array_flip($this->get_assigned_entries($p_key, $p_data_id, true));
                $l_value = [];

                if (isys_format_json::is_json_array($p_value)) {
                    $l_value = isys_format_json::decode($p_value);
                } elseif (is_array($p_value)) {
                    $l_value = $p_value;
                } elseif (is_numeric($p_value)) {
                    $l_value = [$p_value];
                }

                if (is_countable($l_value) && count($l_value)) {
                    $l_main_obj_id = null;
                    foreach ($l_value as $l_id) {
                        if (isset($l_assigned_objects[$l_id])) {
                            unset($l_assigned_objects[$l_id]);
                            $l_return = true;
                            continue;
                        }

                        // @see  API-18  In case the given values are not numeric, get their IDs (happenes, when using API).
                        if (in_array(self::$m_config[$p_custom_id][$p_key]['popup'], isys_custom_fields_dao::getMultipleSelectableFieldTypes(), true) && !is_numeric($l_id)) {
                            $l_id = $daoDialog->get_id('isys_dialog_plus_custom', $l_id, self::$m_config[$p_custom_id][$p_key]['identifier']);
                        }

                        $p_id = $this->create($this->get_object_id(), $p_custom_id, null, null, null, $p_status, null, $p_data_id);
                        $l_return = $this->save_helper($p_id, $p_custom_id, $p_key, $l_id, $p_type, $p_status, $p_description, $p_data_id);
                        $p_id = null;
                    }
                } else {
                    // Nothing to do
                    $l_return = true;
                }

                if (count($l_assigned_objects)) {
                    foreach ($l_assigned_objects as $l_obj_id => $l_entry_id) {
                        $this->delete_entry($l_entry_id, 'isys_catg_custom_fields_list');
                    }
                }
            } else {
                $l_return = $this->save_helper($p_id, $p_custom_id, $p_key, $p_value, $p_type, $p_status, $p_description, $p_data_id);
            }
        } catch (ErrorException $e) {
            throw new Exception($e->getMessage());
        }

        return $l_return;
    }

    /**
     * Updates existing data in database given by HTTP POST.
     *
     * @param   integer $p_cat_level Category data identifier
     * @param   integer $p_status    Record status
     * @param   boolean $p_create    UNUSED
     *
     * @return  boolean Success?
     * @version    Van Quyen Hoang    <qhoang@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_status, $p_create = false)
    {
        $l_list_id = null;
        $l_config_id = ($this->get_catg_custom_id() ? $this->get_catg_custom_id() : $_GET[C__CMDB__GET__CATG_CUSTOM]);

        $this->set_config($l_config_id);
        $l_category_info = $this->get_category_info($l_config_id);

        if (isset($_GET[C__CMDB__GET__CATLEVEL])) {
            $l_list_id = $_GET[C__CMDB__GET__CATLEVEL];
        }

        if ($p_status === null) {
            $p_status = C__RECORD_STATUS__NORMAL;

            if ($l_list_id > 0) {
                $l_catdata = $this->get_data($l_list_id, $_GET[C__CMDB__GET__OBJECT])
                    ->get_row();
                if (is_array($l_catdata)) {
                    $p_status = $l_catdata["isys_catg_custom_fields_list__status"];
                }
            }
        }

        $_POST["C__CATG__CUSTOM__C__CMDB__CAT__COMMENTARY_" . C__CMDB__CATEGORY__TYPE_CUSTOM . $l_config_id] = $_POST["C__CMDB__CAT__COMMENTARY_" .
        C__CMDB__CATEGORY__TYPE_CUSTOM . $l_config_id];
        self::$m_config[$l_config_id]["C__CMDB__CAT__COMMENTARY_" . C__CMDB__CATEGORY__TYPE_CUSTOM . $l_config_id]["type"] = "commentary";

        /**
         * Special handling for empty dialog+ properties
         *
         * @see ID-5431
         */

        // Retrieve custom category configuration
        $customCategoryConfiguration = self::$m_config[$l_config_id];

        // Validate configuration
        if (!empty($customCategoryConfiguration) && is_array($customCategoryConfiguration)) {
            /**
             * Detect multiple dialog+ fields and set corresponding
             * $_POST key to allow flushing these properties
             */
            foreach ($customCategoryConfiguration as $propertyKey => $propertyConfiguration) {
                // Guarantee property is an multiple dialog+
                if ($propertyConfiguration['type'] == 'f_popup' && in_array($propertyConfiguration['popup'], isys_custom_fields_dao::getMultipleSelectableFieldTypes()) && $propertyConfiguration['multiselection'] == 1 &&
                    !isset($_POST['C__CATG__CUSTOM__' . $propertyKey])) {
                    // Set property to empty array
                    $_POST['C__CATG__CUSTOM__' . $propertyKey] = [];
                }
            }
        }

        foreach ($_POST as $l_key => $l_value) {
            if (($l_pos = strpos($l_key, "C__CATG__CUSTOM__")) === 0 && !empty($l_key)) {
                // See ID-2713: Always reset the current ID, so that entries will not get overwritten.
                $l_id = 0;

                if (!empty($_POST[$l_key . '__HIDDEN'])) {
                    continue;
                }

                $l_new_key = substr($l_key, 17, (strlen($l_key) - 17));

                if (strstr($l_new_key, "__VIEW")) {
                    continue;
                }

                if (strstr($l_new_key, "__HIDDEN")) {
                    $l_new_key = str_replace("__HIDDEN", "", $l_new_key);
                }

                if (!isset(self::$m_config[$l_config_id][$l_new_key])) {
                    continue;
                }

                if (self::$m_config[$l_config_id][$l_new_key]["type"] == 'html' || self::$m_config[$l_config_id][$l_new_key]["type"] == 'hr') {
                    continue;
                }

                // @see  ID-4876
                if (self::$m_config[$l_config_id][$l_new_key]["type"] === 'f_popup' && self::$m_config[$l_config_id][$l_new_key]['popup'] === 'calendar') {
                    if (strtotime($l_value) !== false) {
                        $format = 'Y-m-d';

                        if (self::$m_config[$l_config_id][$l_new_key]['default'] == '1') {
                            $format = 'Y-m-d H:i:s';
                        }

                        $l_value = date($format, strtotime($l_value));
                    } else {
                        $l_value = '';
                    }
                }

                if ($l_list_id === null && (int)$l_category_info['isysgui_catg_custom__list_multi_value'] === 1) {
                    $l_list_id = $this->get_data_id($l_config_id);
                }

                $l_data = $this->get_data_by_key($_GET[C__CMDB__GET__OBJECT], $l_config_id, $l_new_key, $l_list_id);
                $l_entry_count = $l_data->num_rows();
                if ($l_entry_count > 0) {
                    $l_row = $l_data->get_row();
                    $l_id = ($l_entry_count >
                        1) ? null : $l_row["isys_catg_custom_fields_list__id"]; // ($l_entry_count > 1) Set null only if there are several entries (Multi Object Browser)
                    $l_list_id = $l_row['isys_catg_custom_fields_list__data__id'];
                } else {
                    if (empty($l_list_id) && (int)$l_category_info['isysgui_catg_custom__list_multi_value'] === 0) {
                        $l_list_id = $this->get_data_id_by_object_id($l_config_id, $_GET[C__CMDB__GET__OBJECT]);
                    }

                    if (!isset(self::$m_config[$l_config_id][$l_new_key]['multiselection']) && $l_value != '' && $l_new_key != '' ||
                        strpos($l_key, 'C__CATG__CUSTOM__C__CMDB__CAT__COMMENTARY_') === 0) {
                        $l_id = $this->create($_GET[C__CMDB__GET__OBJECT], $l_config_id, $l_new_key, null, null, $p_status, null, $l_list_id);
                    }
                }

                $this->save($l_id, $l_config_id, $l_new_key, $l_value, self::$m_config[$l_config_id][$l_new_key]["type"], $p_status, '', $l_list_id);
            }
        }

        return $l_list_id;
    }

    /**
     * Gets the next data id for the associated custom category.
     *
     * @param   integer $p_catg_custom_id
     * @param   integer $p_cat_id
     *
     * @return  integer
     * @author  Van Quyen Hoang    <qhoang@i-doit.org>
     */
    public function get_data_id($p_catg_custom_id = null, $p_cat_id = null)
    {
        if ($p_catg_custom_id === null) {
            $p_catg_custom_id = $this->get_catg_custom_id();
        }

        $l_return = 1;
        $l_sql = 'SELECT MAX(DISTINCT(isys_catg_custom_fields_list__data__id)) AS last_id
			FROM isys_catg_custom_fields_list
			WHERE isys_catg_custom_fields_list__isysgui_catg_custom__id = ' . $this->convert_sql_id($p_catg_custom_id);

        if ($p_cat_id !== null) {
            $l_sql .= ' AND isys_catg_custom_fields_list__id = ' . $this->convert_sql_id($p_cat_id);
        }

        $l_res = $this->retrieve($l_sql);

        if (is_countable($l_res) && count($l_res)) {
            $l_last_id = $l_res->get_row_value('last_id');
            $l_return = ((is_null($p_cat_id)) ? $l_last_id + 1 : $l_last_id);
        }

        return (int)$l_return;
    }

    /**
     * Retrieve data id by category id and object id
     *
     * @param $p_catg_custom_id
     * @param $p_object_id
     *
     * @return int|mixed
     * @throws isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_data_id_by_object_id($p_catg_custom_id, $p_object_id)
    {
        if ($p_catg_custom_id === null) {
            $p_catg_custom_id = $this->get_catg_custom_id();
        }

        $l_sql = 'SELECT isys_catg_custom_fields_list__data__id
			FROM isys_catg_custom_fields_list
			WHERE isys_catg_custom_fields_list__isysgui_catg_custom__id = ' . $this->convert_sql_id($p_catg_custom_id) . ' AND isys_catg_custom_fields_list__isys_obj__id = ' .
            $this->convert_sql_id($p_object_id);

        $l_data_id = $this->retrieve($l_sql)
            ->get_row_value('isys_catg_custom_fields_list__data__id');

        if ($l_data_id) {
            return $l_data_id;
        } else {
            return $this->get_data_id($p_catg_custom_id);
        }
    }

    /**
     * Clear existing data
     *
     * @param int    $p_object_id
     * @param string $p_category_table
     * @param bool   $p_has_relation
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function clear_data($p_object_id, $p_category_table, $p_has_relation = true)
    {
        if ($p_object_id > 0 && $this->m_catg_custom_id > 0) {
            $l_sql = 'DELETE FROM isys_catg_custom_fields_list WHERE isys_catg_custom_fields_list__isys_obj__id = ' . $this->m_db->escape_string($p_object_id) .
                ' AND isys_catg_custom_fields_list__isysgui_catg_custom__id = ' . $this->m_db->escape_string($this->m_catg_custom_id);

            return $this->update($l_sql) && $this->apply_update();
        }

        return true;
    }

    /**
     * Gets custom category title.
     *
     * @param   integer $p_custom_category_id
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_category_title($p_custom_category_id)
    {
        return $this->retrieve('SELECT isysgui_catg_custom__title FROM isysgui_catg_custom WHERE isysgui_catg_custom__id = ' . $this->convert_sql_id($p_custom_category_id) .
            ';')
            ->get_row_value('isysgui_catg_custom__title');
    }

    /**
     * Compares category data which will be used in the import module
     *
     * @param      $p_category_data_values
     * @param      $p_object_category_dataset
     * @param      $p_used_properties
     * @param      $p_comparison
     * @param      $p_badness
     * @param      $p_mode
     * @param      $p_category_id
     * @param      $p_unit_key
     * @param      $p_category_data_ids
     * @param      $p_local_export
     * @param      $p_dataset_id_changed
     * @param      $p_dataset_id
     * @param      $p_logger
     * @param null $p_category_name
     * @param null $p_table
     * @param null $p_cat_multi
     * @param null $p_category_type_id
     * @param null $p_category_ids
     * @param null $p_object_ids
     */
    public function compare_category_data(
        &$p_category_data_values,
        &$p_object_category_dataset,
        &$p_used_properties,
        &$p_comparison,
        &$p_badness,
        &$p_mode,
        &$p_category_id,
        &$p_unit_key,
        &$p_category_data_ids,
        &$p_local_export,
        &$p_dataset_id_changed,
        &$p_dataset_id,
        &$p_logger,
        &$p_category_name = null,
        &$p_table = null,
        &$p_cat_multi = null,
        &$p_category_type_id = null,
        &$p_category_ids = null,
        &$p_object_ids = null,
        &$p_already_used_data_ids = null
    ) {
        foreach ($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES] as $l_key => $l_value) {
            if ($l_key != 'description') {
                $l_field_key = strchr($l_key, 'c_');
                $l_type = substr($l_key, 0, strpos($l_key, '_c'));
            } else {
                $l_field_key = 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__CUSTOM_FIELDS', 'C__CATG__CUSTOM_FIELDS');
                $l_type = 'description';
            }

            if ($l_type == 'hr') {
                continue;
            }

            foreach ($p_object_category_dataset as $l_dataset) {
                if ($l_dataset['isys_catg_custom_fields_list__field_key'] !== $l_field_key) {
                    continue;
                }

                if ($l_dataset['isys_catg_custom_fields_list__data__id'] !== $p_category_data_values['data_id']) {
                    continue;
                }

                $p_dataset_id = $l_dataset['isys_catg_custom_fields_list__data__id'];
                $l_local = $l_dataset['isys_catg_custom_fields_list__field_content'];
                $l_import = null;

                switch ($l_type) {
                    case 'f_popup':
                        if (isset($l_value['id'])) {
                            // Object-Browser
                            $l_import = $l_value['id'];
                        }
                        break;
                    default:
                        $l_import = $l_value[C__DATA__VALUE];
                        break;
                }

                if ($l_local != $l_import) {
                    $p_badness[$p_dataset_id] += 1;
                }
            }
        }

        if ($p_badness[$p_dataset_id] > isys_import_handler_cmdb::C__COMPARISON__THRESHOLD) {
            $p_logger->debug('Dataset differs completly from category data.');
            $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$p_dataset_id] = $p_dataset_id;
        } // @todo check badness again
        else {
            if ($p_badness[$p_dataset_id] == 0 /*|| ($p_dataset_id_changed && $p_badness[$p_dataset_id] == 1)*/) {
                $p_logger->debug('Dataset and category data are the same.');
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$p_dataset_id] = $p_dataset_id;
            } else {
                $p_logger->debug('Dataset differs partly from category data.');
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY][$p_dataset_id] = $p_dataset_id;
            }
        }
    }

    /**
     * Gets additional query condition to fetch data from database.
     *
     * @return  string
     */
    public function get_export_condition()
    {
        return " AND isys_catg_custom_fields_list__isysgui_catg_custom__id = " . $this->convert_sql_id($this->m_catg_custom_id) . " ";
    }

    /**
     * Checks if an entry exists
     *
     * @param $p_obj_id
     * @param $p_key
     * @param $p_data_id
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function entry_exists($p_obj_id, $p_key, $p_data_id)
    {
        $l_sql = 'SELECT isys_catg_custom_fields_list__id
			FROM isys_catg_custom_fields_list
			WHERE isys_catg_custom_fields_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_catg_custom_fields_list__field_key = ' . $this->convert_sql_text($p_key) . '
			AND isys_catg_custom_fields_list__data__id = ' . $this->convert_sql_id($p_data_id);

        return ($this->retrieve($l_sql)
                ->num_rows() > 0);
    }

    /**
     *
     * @param isys_array $p_array_reference
     * @param int        $p_record_status
     * @param array      $p_provides
     *
     * @return isys_array
     */
    public function category_data(&$p_array_reference, $p_record_status = C__RECORD_STATUS__NORMAL, array $p_provides = [])
    {
        // Retrieve category data.
        $l_catdata = $this->get_data_by_object($this->get_object_id(), null, $p_record_status);

        $languageManager = isys_application::instance()->container->get('language');
        $locales = isys_application::instance()->container->get('locales');

        $l_rows = [];

        // Format category result.
        while ($l_row = $l_catdata->get_row()) {
            if (!isset(self::$m_config[$l_row['isysgui_catg_custom__id']])) {
                self::$m_config[$l_row['isysgui_catg_custom__id']] = $this->get_config($l_row['isysgui_catg_custom__id']);
            }

            $l_value = $l_row['isys_catg_custom_fields_list__field_content'];

            if ($l_row['isys_catg_custom_fields_list__field_type'] == 'commentary') {
                $l_key = 'description';
            } else {
                $l_key = $l_row['isys_catg_custom_fields_list__field_type'] . '_' . $l_row['isys_catg_custom_fields_list__field_key'];
            }

            if ($l_row['isys_catg_custom_fields_list__field_type'] === 'f_popup') {
                if (self::$m_config[$l_row['isysgui_catg_custom__id']][$l_row['isys_catg_custom_fields_list__field_key']]['popup'] === 'dialog_plus') {
                    $l_value = $l_row['isys_dialog_plus_custom__title'];
                } elseif (self::$m_config[$l_row['isysgui_catg_custom__id']][$l_row['isys_catg_custom_fields_list__field_key']]['popup'] === 'calendar') {
                    if (self::$m_config[$l_row['isysgui_catg_custom__id']][$l_row['isys_catg_custom_fields_list__field_key']]['default'] == '1') {
                        // @see  ID-7507  New "date + time" property
                        $l_value = $locales->fmt_datetime($l_row['isys_catg_custom_fields_list__field_content']);
                    } else {
                        // @see  DOKU-70 ID-5325  Fixed the date formatting.
                        $l_value = $locales->fmt_date($l_row['isys_catg_custom_fields_list__field_content']);
                    }
                }
            }

            $l_rows[$l_row['isys_catg_custom_fields_list__data__id']][$l_key] = new isys_cmdb_dao_category_data_value($languageManager->get($l_value));
        }

        if (count($l_rows)) {
            foreach ($l_rows as $l_id => $l_row) {
                $p_array_reference[$l_id] = $l_row;
            }
        }

        unset($l_rows);

        return $p_array_reference;
    }

    /**
     * @param string $p_table
     * @param null   $p_obj_id
     *
     * @return bool
     */
    public function create_connector($p_table, $p_obj_id = null)
    {
        return null;
    }

    /**
     * Return database field to be used as breadcrumb title. This is set to an empty string, so we receive the "#<id>" catlevel.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_breadcrumb_field()
    {
        return '';
    }

    /**
     * Retrieves the number of saved category-entries to the given object
     * or considers the number of rows of an linked report.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     * @throws  isys_exception_database
     * @author  Lenard Fischer <lfischer@i-doit.com>
     * @throws Exception
     */
    public function get_count($p_obj_id = null)
    {
        $report = $this->getReport($this->m_object_id);

        if ($report === null) {
            return 0;
        }

        if ($report !== false && !empty($report['isys_report__query'])) {
            return 1;
        }

        /**
         * General handling: Counting category rows in database
         */
        $l_sql = 'SELECT COUNT(*) AS count
            FROM isys_catg_custom_fields_list
            WHERE isys_catg_custom_fields_list__isysgui_catg_custom__id = ' . $this->convert_sql_id($this->m_catg_custom_id);

        if ($p_obj_id !== null) {
            $l_sql .= ' AND isys_catg_custom_fields_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        }

        return (int)$this->retrieve($l_sql . ';')
            ->get_row_value('count');
    }

    /**
     * Fetches category data from database.
     *
     * @param   integer $p_catg_list_id Category data identifier. Defaults to null.
     * @param   integer $p_obj_id       Object identifier. Defaults to null.
     * @param   string  $p_condition    Query condition. Defaults to ''.
     * @param   mixed   $p_filter       Filter (string or array). Defaults to null.
     * @param   integer $p_status       Record status. Defaults to null.
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     * @version Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_data($p_catg_list_id = 1, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_custom_fields_list
            INNER JOIN isysgui_catg_custom ON isys_catg_custom_fields_list__isysgui_catg_custom__id = isysgui_catg_custom__id
            INNER JOIN isys_obj ON isys_catg_custom_fields_list__isys_obj__id = isys_obj__id
            LEFT JOIN isys_dialog_plus_custom ON isys_dialog_plus_custom__id = isys_catg_custom_fields_list__field_content 
            WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= ' ' . $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null && $p_catg_list_id > 0) {
            $l_sql .= " AND isys_catg_custom_fields_list__data__id = " . $this->convert_sql_id($p_catg_list_id);
        }

        if ($p_catg_list_id === null && $p_obj_id === null) {
            $l_sql .= " AND FALSE ";
        }

        if (isset($this->m_catg_custom_id)) {
            $l_sql .= " AND isys_catg_custom_fields_list__isysgui_catg_custom__id = " . $this->convert_sql_id($this->m_catg_custom_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_catg_custom_fields_list__status = " . $this->convert_sql_id($p_status);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Simple wrapper of get_data()
     *
     * @param null   $p_category_data_id
     * @param null   $p_obj_id
     * @param string $p_condition
     * @param null   $p_filter
     * @param null   $p_status
     *
     * @return array Category result as array
     */
    public function get_data_as_array($p_category_data_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        // Get result.
        $l_res = $this->get_data($p_category_data_id, $p_obj_id, $p_condition, $p_filter, $p_status);
        $l_catentries = [];

        if ($l_res->num_rows()) {
            while ($l_row = $l_res->get_row()) {
                // Set category data id index used for grouping entries
                $dataId = $l_row['isys_catg_custom_fields_list__data__id'];

                // Build field key.
                $l_field_key = $l_row['isys_catg_custom_fields_list__field_type'] . '_' . $l_row['isys_catg_custom_fields_list__field_key'];

                // @see  API-19  If a value appears more than once we'll return every entry.
                if (isset($l_catentries[$dataId][$l_field_key]) && !empty($l_catentries[$dataId][$l_field_key])) {
                    if (!is_array($l_catentries[$dataId][$l_field_key])) {
                        $l_catentries[$dataId][$l_field_key] = [$l_catentries[$dataId][$l_field_key]];
                    }

                    $l_catentries[$dataId][$l_field_key][] = $l_row['isys_catg_custom_fields_list__field_content'];
                } else {
                    $l_catentries[$dataId][$l_field_key] = $l_row['isys_catg_custom_fields_list__field_content'];
                }

                // Special-Handling for description field: Alias is not conform with other fields. It does not contain the type as alias.
                if (strpos($l_row['isys_catg_custom_fields_list__field_key'], 'C__CMDB__CAT__COMMENTARY_') !== false) {
                    $l_catentries[$dataId][$l_row['isys_catg_custom_fields_list__field_key']] = $l_row['isys_catg_custom_fields_list__field_content'];
                }

                // Add object and category entry ID.
                $l_catentries[$dataId]['isys_catg_custom_fields_list__id'] = $l_row['isys_catg_custom_fields_list__data__id'];
                $l_catentries[$dataId]['isys_catg_custom_fields_list__isys_obj__id'] = $l_row['isys_catg_custom_fields_list__isys_obj__id'];

                // @see API-158 / ID-6593 Set the object id.
                $l_catentries[$dataId]['isys_obj__id'] = $l_row['isys_catg_custom_fields_list__isys_obj__id'];
            }
        }

        /**
         * Category entries will be grouped by
         * data__id field instead of an simple counter
         * that will be changed immediately when data__id changes.
         *
         * This caused duplicate entries when following happens:
         *
         * --row-- data_id = 1
         * --row-- data_id = 2
         * --row-- data_id = 1
         * --row-- data_id = 2
         *
         * We would get 4 entries for 2 entries only
         *
         * Because the previous logic returns an array
         * with an index starting at 0 we will do the same here.
         *
         * @see ID-5715
         */
        $l_catentries = array_values($l_catentries);

        return $l_catentries;
    }

    /**
     * Get entry identifier
     *
     * @author  Selcuk Kekec <skekec@i-doit.com>
     *
     * @param   array $p_entry_data
     *
     * @return  string
     */
    public function get_entry_identifier($p_entry_data)
    {
        try {
            if (isset($p_entry_data[0]['isys_catg_custom_fields_list__field_content'])) {
                return $p_entry_data[0]['isys_catg_custom_fields_list__field_content'];
            }

            if (isset($p_entry_data['isys_catg_custom_fields_list__field_content'])) {
                return $p_entry_data['isys_catg_custom_fields_list__field_content'];
            }
        } catch (isys_exception_cmdb $e) {
        }

        return '';
    }

    /**
     * @return int
     */
    public function get_list_id()
    {
        return (int)$this->m_list_id;
    }

    /**
     * @return isys_cmdb_ui_category_g_custom_fields
     */
    public function &get_ui()
    {
        return new isys_cmdb_ui_category_g_custom_fields(isys_application::instance()->template);
    }

    /**
     * Checks if the custom category is a multivalued category
     *
     * @param null $p_custom_category_id
     *
     * @return bool
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function is_multivalued($p_custom_category_id = null)
    {
        $l_sql = 'SELECT isysgui_catg_custom__list_multi_value
			FROM isysgui_catg_custom
			WHERE isysgui_catg_custom__id = ' . $this->convert_sql_id((!is_null($p_custom_category_id) ? $p_custom_category_id : $this->m_catg_custom_id));

        return (bool)$this->retrieve($l_sql)
            ->get_row_value('isysgui_catg_custom__list_multi_value');
    }

    /**
     * Method for returning the properties.
     *
     * @version  Van Quyen Hoang    <qhoang@i-doit.org>
     * @throws   isys_exception_dao
     * @return   array
     */
    protected function properties()
    {
        $l_return = [];

        if ($this->m_catg_custom_id > 0 && $this->m_catg_custom_id !== null) {
            try {
                $l_config = $this->get_config($this->m_catg_custom_id);
                $this->m_multivalued = $this->is_multivalued($this->m_catg_custom_id);
                $l_const = $this->retrieve('SELECT isysgui_catg_custom__const FROM isysgui_catg_custom WHERE isysgui_catg_custom__id = ' .
                    $this->convert_sql_id($this->m_catg_custom_id))
                    ->get_row_value('isysgui_catg_custom__const');

                // Check category has fields
                if (is_array($l_config) && count($l_config) > 0) {
                    foreach ($l_config as $l_field_key => $l_field) {
                        $l_tag = $l_field['type'] . '_' . $l_field_key;

                        $l_return[$l_tag] = array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                            C__PROPERTY__INFO     => [
                                C__PROPERTY__INFO__TITLE       => $l_field['title'],
                                C__PROPERTY__INFO__DESCRIPTION => $l_field['type']
                            ],
                            C__PROPERTY__DATA     => [
                                C__PROPERTY__DATA__TYPE        => C__TYPE__TEXT,
                                C__PROPERTY__DATA__FIELD       => 'isys_catg_custom_fields_list__field_content',
                                C__PROPERTY__DATA__FIELD_ALIAS => $l_tag
                            ],
                            C__PROPERTY__UI       => [
                                C__PROPERTY__UI__ID     => $l_field_key,
                                /** Validation bricks if type is not set to text or textarea, so we need to replace the f_ See ID-3016 */
                                C__PROPERTY__UI__TYPE   => str_replace('f_', '', $l_field['type']),
                                C__PROPERTY__UI__PARAMS => $l_field
                            ],
                            C__PROPERTY__PROVIDES => [
                                C__PROPERTY__PROVIDES__REPORT => true,
                                C__PROPERTY__PROVIDES__SEARCH => true,
                            ]
                        ]);

                        $l_query = 'SELECT joined_content.isys_catg_custom_fields_list__field_content 
                            FROM isys_catg_custom_fields_list AS root_custom_field
                            LEFT JOIN isys_catg_custom_fields_list AS joined_content
                            ON joined_content.isys_catg_custom_fields_list__data__id = root_custom_field.isys_catg_custom_fields_list__data__id
                            AND joined_content.isys_catg_custom_fields_list__field_key = ' . $this->convert_sql_text($l_field_key);

                        if ($l_field['type'] === 'f_wysiwyg') {
                            $l_return[$l_tag][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VALIDATION] = false;

                            // @see  ID-7100 / API-201  the property info type was not set correctly.
                            $l_return[$l_tag][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] = C__PROPERTY__INFO__TYPE__TEXTAREA;

                            $l_return[$l_tag][C__PROPERTY__CHECK] = [
                                C__PROPERTY__CHECK__MANDATORY  => false,
                                C__PROPERTY__CHECK__VALIDATION => [
                                    FILTER_CALLBACK,
                                    [
                                        'options' => [
                                            'isys_helper',
                                            'filter_textarea'
                                        ]
                                    ]
                                ]
                            ];
                        }

                        if ($l_field['type'] === 'f_textarea') {
                            $l_return[$l_tag][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] = C__PROPERTY__INFO__TYPE__TEXTAREA;
                            $l_return[$l_tag][C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE] = C__TYPE__TEXT_AREA;
                            $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__TYPE] = C__PROPERTY__UI__TYPE__TEXTAREA;
                            $l_return[$l_tag][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][1]['options'][1] = 'filter_textarea';
                            unset($l_return[$l_tag][C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION]);
                        }

                        if ($l_field['type'] === 'f_time') {
                            $l_return[$l_tag][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] = C__PROPERTY__INFO__TYPE__TIME;
                            $l_return[$l_tag][C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE] = C__TYPE__TIME;
                            $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__TYPE] = C__PROPERTY__UI__TYPE__TIME;
                        }

                        // Check whether property is an masked text field and register export helper
                        if (str_replace('f_', '', $l_field['type']) === 'password') {
                            $l_return[$l_tag][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK] = [
                                'isys_global_custom_fields_export_helper',
                                'exportCustomFieldPassword'
                            ];
                        }

                        if ($l_field['extra'] === 'yes-no') {
                            $l_return[$l_tag][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] = C__PROPERTY__INFO__TYPE__DIALOG;
                            $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__DEFAULT] = $l_field['default'] ? 'LC__UNIVERSAL__YES' : 'LC__UNIVERSAL__NO';
                            $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'] = [
                                'LC__UNIVERSAL__YES' => isys_application::instance()->container->get('language')->get('LC__UNIVERSAL__YES'),
                                'LC__UNIVERSAL__NO'  => isys_application::instance()->container->get('language')->get('LC__UNIVERSAL__NO')
                            ];

                            $l_return[$l_tag][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK] = [
                                'isys_global_custom_fields_export_helper',
                                'exportCustomFieldYesNoDialog'
                            ];
                        }

                        if ($l_field['type'] === 'f_popup') {
                            $l_return[$l_tag][C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE] = C__TYPE__INT;
                            $l_return[$l_tag][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][] = 'isys_global_custom_fields_export_helper';
                            switch ($l_field['popup']) {
                                case 'calendar':
                                    if ($l_field['default'] == '1') {
                                        $l_return[$l_tag][C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE] = C__TYPE__DATE_TIME;
                                        $l_return[$l_tag][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] = C__PROPERTY__INFO__TYPE__DATETIME;
                                        $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__TYPE] = C__PROPERTY__UI__TYPE__DATETIME;
                                        $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_bTime'] = true;
                                        $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strStyle'] = 'width:70%;';
                                        $l_return[$l_tag][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][] = 'exportCustomFieldCalendarWithTime';
                                    } else {
                                        $l_return[$l_tag][C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE] = C__TYPE__DATE;
                                        $l_return[$l_tag][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] = C__PROPERTY__INFO__TYPE__DATE;
                                        $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__TYPE] = C__PROPERTY__UI__TYPE__DATE;
                                        $l_return[$l_tag][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][] = 'exportCustomFieldCalendar';
                                    }

                                    break;

                                case 'file':
                                case 'browser_object':
                                    $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__TYPE] = C__PROPERTY__UI__TYPE__POPUP;
                                    $l_return[$l_tag][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] = C__PROPERTY__INFO__TYPE__OBJECT_BROWSER;
                                    $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] = 'browser_object_ng';
                                    $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][isys_popup_browser_object_ng::C__MULTISELECTION] = !!$l_field['multiselection'];
                                    $l_return[$l_tag][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][] = 'exportCustomFieldObject';
                                    if (isset($l_field['identifier'])) {
                                        $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_identifier'] = $l_field['identifier'];
                                    }

                                    $l_query = 'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\') FROM isys_catg_custom_fields_list AS root_custom_field
                                        LEFT JOIN isys_catg_custom_fields_list AS joined_content
                                        ON joined_content.isys_catg_custom_fields_list__data__id = root_custom_field.isys_catg_custom_fields_list__data__id
                                        AND joined_content.isys_catg_custom_fields_list__field_key = ' . $this->convert_sql_text($l_field_key) . '
                                        LEFT JOIN isys_obj ON isys_obj__id = joined_content.isys_catg_custom_fields_list__field_content';

                                    break;
                                case 'report_browser':
                                    /**
                                     * Set export helper for report fields
                                     *
                                     * @see ID-6059
                                     */
                                    $l_return[$l_tag][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][] = 'exportCustomReport';
                                    $l_return[$l_tag][C__PROPERTY__PROVIDES] = [
                                        C__PROPERTY__PROVIDES__SEARCH       => false,
                                        C__PROPERTY__PROVIDES__IMPORT       => true,
                                        C__PROPERTY__PROVIDES__EXPORT       => true,
                                        C__PROPERTY__PROVIDES__REPORT       => false,
                                        C__PROPERTY__PROVIDES__LIST         => false,
                                        C__PROPERTY__PROVIDES__MULTIEDIT    => false,
                                        C__PROPERTY__PROVIDES__VALIDATION   => false,
                                        C__PROPERTY__PROVIDES__VIRTUAL      => false,
                                        C__PROPERTY__PROVIDES__SEARCH_INDEX => false
                                    ];
                                    break;
                                default:
                                    $l_ui_params = [
                                        'p_strTable' => 'isys_dialog_plus_custom',
                                        'condition' => 'isys_dialog_plus_custom__identifier = ' . $this->convert_sql_text($l_field['identifier']),
                                        'p_arData'       => new isys_callback([
                                            'isys_cmdb_dao_category_g_custom_fields',
                                            'callback_property_dialog_field'
                                        ], [
                                            $l_tag,
                                            $this->m_catg_custom_id
                                        ]),
                                        'p_strPopupType' => 'dialog_plus',
                                        'p_identifier'   => $l_field['identifier']
                                    ];

                                    if ($l_field['multiselection'] > 0) {
                                        $l_return[$l_tag][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] = C__PROPERTY__INFO__TYPE__MULTISELECT;
                                        $l_ui_params['multiselect'] = true;
                                        $l_ui_params['multiselection'] = 1;
                                    } else {
                                        $l_return[$l_tag][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] = C__PROPERTY__INFO__TYPE__DIALOG_PLUS;
                                    }

                                    $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS] = array_merge(
                                        $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS],
                                        $l_ui_params
                                    );

                                    $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__TYPE] = C__PROPERTY__UI__TYPE__POPUP;
                                    $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__DEFAULT] = null;

                                    $l_return[$l_tag][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][] = 'exportCustomFieldDialogPlus';

                                    $l_return[$l_tag][C__PROPERTY__DATA][C__PROPERTY__DATA__SOURCE_TABLE] = 'isys_catg_custom_fields_list';

                                    $l_return[$l_tag][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES] = [
                                        'isys_dialog_plus_custom',
                                        'isys_dialog_plus_custom__id'
                                    ];

                                    $l_query = 'SELECT isys_dialog_plus_custom__title 
                                        FROM isys_catg_custom_fields_list AS root_custom_field
                                        LEFT JOIN isys_catg_custom_fields_list AS joined_content
                                        ON joined_content.isys_catg_custom_fields_list__data__id = root_custom_field.isys_catg_custom_fields_list__data__id
                                        AND joined_content.isys_catg_custom_fields_list__field_key = ' . $this->convert_sql_text($l_field_key) . '
                                        LEFT JOIN isys_dialog_plus_custom ON isys_dialog_plus_custom__id = joined_content.isys_catg_custom_fields_list__field_content';

                                    break;
                            }
                        }

                        // Build Data Select
                        $l_return[$l_tag][C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT] = idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                            $l_query,
                            'isys_catg_custom_fields_list',
                            '',
                            'root_custom_field.isys_catg_custom_fields_list__isys_obj__id',
                            '',
                            '',
                            idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([
                                'AND root_custom_field.isys_catg_custom_fields_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL),
                                'AND root_custom_field.isys_catg_custom_fields_list__isysgui_catg_custom__id =
                                        (SELECT isysgui_catg_custom__id FROM isysgui_catg_custom WHERE isysgui_catg_custom__const = ' . $this->convert_sql_text($l_const) .
                                ')',
                                'AND root_custom_field.isys_catg_custom_fields_list__field_type = \'commentary\''
                            ]),
                            idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['root_custom_field.isys_catg_custom_fields_list__isys_obj__id'])
                        );

                        if ($l_field['type'] === 'html' || $l_field['type'] === 'script' || $l_field['type'] === 'hr') {
                            $l_return[$l_tag][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VIRTUAL] = true;
                        }

                        // Setup property visibility ui parameters
                        if (is_array($l_field) && array_key_exists('visibility', $l_field)) {
                            switch ($l_field['visibility']) {
                                case 'hidden':
                                    $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_bInvisible'] = true;
                                    break;
                                case 'readonly':
                                    $l_return[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_bReadonly'] = true;
                                    break;
                            }
                        }
                    }
                }

                $l_return['description'] = array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__TYPE        => C__TYPE__TEXT_AREA,
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_custom_fields_list__field_content',
                        C__PROPERTY__DATA__FIELD_ALIAS => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_CUSTOM . $this->m_catg_custom_id,
                        C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                            'SELECT root_custom_field.isys_catg_custom_fields_list__field_content FROM isys_catg_custom_fields_list AS root_custom_field',
                            'isys_catg_custom_fields_list',
                            '',
                            'root_custom_field.isys_catg_custom_fields_list__isys_obj__id',
                            '',
                            '',
                            idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([
                                'AND root_custom_field.isys_catg_custom_fields_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL),
                                'AND root_custom_field.isys_catg_custom_fields_list__field_type = \'commentary\'',
                                'AND root_custom_field.isys_catg_custom_fields_list__field_key = ' .
                                $this->convert_sql_text('C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_CUSTOM . $this->m_catg_custom_id)
                            ]),
                            idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['root_custom_field.isys_catg_custom_fields_list__isys_obj__id'])
                        )
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_CUSTOM . $this->m_catg_custom_id
                    ]
                ]);
            } catch (Exception $e) {
                throw new isys_exception_dao($e->getMessage());
            }
        }

        return $l_return;
    }

    /**
     * Prepare filter for get_data()
     *
     * @param $filterTerm
     *
     * @return string
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @see ID-5500
     */
    protected function prepare_filter($filterTerm)
    {
        // Check whether filter is empty
        if ($filterTerm === null) {
            return '';
        }

        // Get properties of custom category
        $properties = $this->get_properties();

        // Check for existing properties
        if (is_countable($properties) && count($properties) === 0) {
            return '';
        }

        // Setup sql condition for filter
        $sqlCondition = '
        AND (isys_catg_custom_fields_list__data__id 
                IN(
                    SELECT isys_catg_custom_fields_list__data__id 
                    FROM isys_catg_custom_fields_list 
                    WHERE (%s) 
                    GROUP BY isys_catg_custom_fields_list__data__id
                )
        )';

        // Store for field conditions
        $fieldConditions = [];

        // Validate filter term
        if (is_string($filterTerm) && strlen($filterTerm) >= (int)isys_tenantsettings::get('maxlength.search.filter', 3)) {
            // Iterate over properties
            foreach ($properties as $property) {
                /**
                 * @todo Add filter capabilities for dialogs and object assignments
                 */

                // Dialog+ handling
                if ($property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] === 'dialog_plus' ||
                    $property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] === 'multiselect') {
                    $fieldConditions[] = sprintf(
                        '
                        (   %s IN
                            ( 
                                SELECT isys_dialog_plus_custom__id 
                                FROM isys_dialog_plus_custom
                                WHERE isys_dialog_plus_custom__title LIKE \'%%%s%%\'
                                AND isys_dialog_plus_custom__identifier = \'%s\'
                            )
                            AND isys_catg_custom_fields_list__field_key = \'%s\'
                        )
                        ',
                        $property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD],
                        addslashes($filterTerm),
                        $property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['identifier'],
                        $property[C__PROPERTY__UI][C__PROPERTY__UI__ID]
                    );
                } elseif ($property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] === 'object_browser') {
                    // Object Browser handling
                    $fieldConditions[] = sprintf(
                        '
                        (
                            %s IN
                            (
                                SELECT isys_obj__id 
                                FROM isys_catg_custom_fields_list
                                INNER JOIN isys_obj ON  isys_obj__id = isys_catg_custom_fields_list__field_content 
                                WHERE isys_obj__title LIKE \'%%%s%%\' AND isys_catg_custom_fields_list__field_key = \'%s\'
                            )
                        )
                        ',
                        $property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD],
                        addslashes($filterTerm),
                        $property[C__PROPERTY__UI][C__PROPERTY__UI__ID]
                    );
                } elseif ($property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] === 'text') {
                    // Yes/No handling
                    if ($property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['extra'] === 'yes-no') {
                        $dialogEntries = $property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'];
                        $translatedValue = null;

                        // Determine LC for filter
                        if (strcasecmp($dialogEntries['LC__UNIVERSAL__YES'], $filterTerm) === 0) {
                            $translatedValue = 'LC__UNIVERSAL__YES';
                        } elseif (strcasecmp($dialogEntries['LC__UNIVERSAL__NO'], $filterTerm) === 0) {
                            $translatedValue = 'LC__UNIVERSAL__NO';
                        }

                        // Found a matched for filter?
                        if (!empty($translatedValue)) {
                            $fieldConditions[] = sprintf(
                                '( %s = \'%s\' AND isys_catg_custom_fields_list__field_key = \'%s\')',
                                $property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD],
                                addslashes($translatedValue),
                                $property[C__PROPERTY__UI][C__PROPERTY__UI__ID]
                            );
                        }

                        continue;
                    }

                    // Default field condition
                    $fieldConditions[] = sprintf(
                        '( %s LIKE \'%%%s%%\' AND isys_catg_custom_fields_list__field_key = \'%s\' )',
                        $property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD],
                        addslashes($filterTerm),
                        $property[C__PROPERTY__UI][C__PROPERTY__UI__ID]
                    );
                }
            }

            // Build filter condition and return it
            return sprintf($sqlCondition, implode(' OR ', $fieldConditions));
        }

        return '';
    }

    /**
     * Rank records method
     *
     * @param  integer $categoryDataId
     * @param  integer $rankDirection
     * @param  string  $table
     * @param  null    $checkMethod
     * @param  boolean $purge
     *
     * @return boolean
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function rank_record($categoryDataId, $rankDirection, $table, $checkMethod = null, $purge = false)
    {
        $l_custom_id = ($this->get_catg_custom_id()) ?: $this->get_category_id();
        $categoryTitle = $this->get_category_title($l_custom_id);

        $l_sql_condition = ' WHERE isys_catg_custom_fields_list__isysgui_catg_custom__id = ' . $this->convert_sql_id($l_custom_id) . ' 
            AND isys_catg_custom_fields_list__data__id = ' . $this->convert_sql_id($categoryDataId);
        $l_sql_update = '';
        $l_targetStatus = null;

        // Get entry data.
        $l_row = $this->retrieve('SELECT * FROM isys_catg_custom_fields_list ' . $l_sql_condition . ';')->__as_array();

        if ($this->m_multivalued === false && $purge === true && $rankDirection == C__CMDB__RANK__DIRECTION_DELETE) {
            // For non multivalued custom categories
            $l_current_status = C__RECORD_STATUS__DELETED;
        } else {
            $l_current_status = ($purge === true && $rankDirection == C__CMDB__RANK__DIRECTION_DELETE) ?
                C__RECORD_STATUS__DELETED :
                $l_row[0]['isys_catg_custom_fields_list__status'];

            $l_sql_update = 'UPDATE isys_catg_custom_fields_list SET isys_catg_custom_fields_list__status = ';
        }

        // Is there a corresponding entry for the given ID?
        if ($l_current_status) {
            // Query for selecting relation objects.
            $relationSql = 'SELECT isys_catg_relation_list__id, isys_catg_relation_list__isys_obj__id 
                FROM isys_catg_custom_fields_list
                LEFT JOIN isys_catg_relation_list ON isys_catg_relation_list__id = isys_catg_custom_fields_list__isys_catg_relation_list__id 
                ' . $l_sql_condition . ';';

            if ($rankDirection == C__CMDB__RANK__DIRECTION_DELETE) {
                switch ($l_current_status) {
                    case C__NAVMODE__QUICK_PURGE:
                    case C__RECORD_STATUS__DELETED:
                        // Get a relation DAO instance.
                        $relationDao = isys_cmdb_dao_category_g_relation::instance($this->m_db);

                        // Get the relation objects to purge.
                        $relationResult = $this->retrieve($relationSql);

                        while ($relationRow = $relationResult->get_row()) {
                            if (!empty($relationRow['isys_catg_relation_list__isys_obj__id'])) {
                                $relationDao->delete_object_and_relations($relationRow['isys_catg_relation_list__isys_obj__id']);
                            }
                        }

                        $l_sql_update = 'DELETE FROM isys_catg_custom_fields_list ' . $l_sql_condition;

                        $l_targetStatus = C__RECORD_STATUS__PURGE;
                        $logbookConstEvent = 'C__LOGBOOK_EVENT__CATEGORY_PURGED';
                        break;

                    case C__RECORD_STATUS__ARCHIVED:
                        $l_targetStatus = C__RECORD_STATUS__DELETED;
                        $logbookConstEvent = 'C__LOGBOOK_EVENT__CATEGORY_DELETED';
                        break;

                    case C__RECORD_STATUS__NORMAL:
                        $l_targetStatus = C__RECORD_STATUS__ARCHIVED;
                        $logbookConstEvent = 'C__LOGBOOK_EVENT__CATEGORY_ARCHIVED';
                        break;
                }

                // @see  ID-6632  Remove relation object and relation itself.
                $relationResult = $this->retrieve($relationSql);

                while ($relationRow = $relationResult->get_row()) {
                    if (is_numeric($relationRow['isys_catg_relation_list__isys_obj__id']) && $relationRow['isys_catg_relation_list__isys_obj__id'] > 0) {
                        $this->set_object_status($relationRow['isys_catg_relation_list__isys_obj__id'], $l_targetStatus);
                    }

                    if (is_numeric($relationRow['isys_catg_relation_list__id']) && $relationRow['isys_catg_relation_list__id'] > 0) {
                        $relationStatusSql = 'UPDATE isys_catg_relation_list 
                            SET isys_catg_relation_list__status = ' . $this->convert_sql_id($l_targetStatus) . '
                            WHERE isys_catg_relation_list__id = ' . $this->convert_sql_id($relationRow['isys_catg_relation_list__id']) . ';';

                        $this->update($relationStatusSql);
                    }
                }
            } elseif ($rankDirection == C__CMDB__RANK__DIRECTION_RECYCLE) {
                $logbookConstEvent = 'C__LOGBOOK_EVENT__CATEGORY_RECYCLED';
                switch ($l_current_status) {
                    case C__RECORD_STATUS__ARCHIVED:
                        $l_targetStatus = C__RECORD_STATUS__NORMAL;
                        break;

                    case C__RECORD_STATUS__DELETED:
                        $l_targetStatus = C__RECORD_STATUS__ARCHIVED;
                        break;
                }

                $relationResult = $this->retrieve($relationSql);

                // @see  ID-6632  Recycle relation object and relation itself.
                while ($relationRow = $relationResult->get_row()) {
                    if (is_numeric($relationRow['isys_catg_relation_list__isys_obj__id']) && $relationRow['isys_catg_relation_list__isys_obj__id'] > 0) {
                        $this->set_object_status($relationRow['isys_catg_relation_list__isys_obj__id'], $l_targetStatus);
                    }

                    if (is_numeric($relationRow['isys_catg_relation_list__id']) && $relationRow['isys_catg_relation_list__id'] > 0) {
                        $relationStatusSql = 'UPDATE isys_catg_relation_list 
                            SET isys_catg_relation_list__status = ' . $this->convert_sql_id($l_targetStatus) . '
                            WHERE isys_catg_relation_list__id = ' . $this->convert_sql_id($relationRow['isys_catg_relation_list__id']) . ';';

                        $this->update($relationStatusSql);
                    }
                }
            }

            if ($l_targetStatus != C__RECORD_STATUS__PURGE) {
                $l_sql_update .= $this->convert_sql_int($l_targetStatus) . ' ' . $l_sql_condition;
            }

            $entryTitle = null;

            if (isset($l_row[0]['isys_catg_custom_fields_list__field_content'])) {
                $entryTitle = $l_row[0]['isys_catg_custom_fields_list__field_content'];
            }

            // Identify object id.
            $objectId = $this->get_object_id();

            if (isset($l_row[0]['isys_catg_custom_fields_list__isys_obj__id'])) {
                $objectId = $l_row[0]['isys_catg_custom_fields_list__isys_obj__id'];
            }

            $mappingCategory = [
                C__RECORD_STATUS__DELETED => Context::CONTEXT_RANK_CATEGORY_DELETED,
                C__RECORD_STATUS__PURGE => Context::CONTEXT_RANK_CATEGORY_PURGED,
                C__RECORD_STATUS__ARCHIVED => Context::CONTEXT_RANK_CATEGORY_ARCHIVED,
                C__RECORD_STATUS__NORMAL => Context::CONTEXT_RANK_CATEGORY_RECYCLED
            ];

            Context::instance()
                ->setContextTechnical(Context::CONTEXT_RANK_CATEGORY)
                ->setGroup(Context::CONTEXT_GROUP_DAO)
                ->setContextCustomer($mappingCategory[$l_targetStatus]);

            // Emit mod.cmdb.beforeRankRecord before ranking the object/category entry.
            isys_component_signalcollection::get_instance()
                ->emit(
                    'mod.cmdb.beforeRankRecord',
                    $this,
                    $objectId,
                    $categoryDataId,
                    $entryTitle,
                    $l_row,
                    'isys_catg_custom_fields_list',
                    $l_current_status,
                    $l_targetStatus,
                    $this->m_category_type_const,
                    $rankDirection
                );

            if ($this->update($l_sql_update) && $this->apply_update()) {
                $this->logbook_rank($objectId, $logbookConstEvent, $l_sql_update, $categoryTitle);
                return true;
            }
        }

        return false;
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = true;
        $l_new_data_id = null;

        if (is_array($p_category_data) && count($p_category_data) > 0 && is_numeric($p_object_id) && $p_object_id >= 0 && is_numeric($this->m_catg_custom_id)) {
            $this->set_config($this->m_catg_custom_id);
            $l_cache_obj_id = null;
            if ($this->get_object_id() != $p_object_id) {
                $l_cache_obj_id = $this->get_object_id();
                $this->set_object_id($p_object_id);
            }

            if (!$this->m_multivalued) {
                $l_new_data_id = $this->get_data_id_by_object_id($this->m_catg_custom_id, $p_object_id);
            } elseif ($p_status == isys_import_handler_cmdb::C__CREATE || $p_category_data['data_id'] === null) {
                $l_new_data_id = $this->get_data_id($this->m_catg_custom_id);
            } else {
                $l_new_data_id = $p_category_data['data_id'];
            }

            /**
             * Handle description field separately
             *
             * Description field should always be created because reports always joins the description field at first for the data__id.
             *
             * @see ID-2504, ID-3117
             */
            $l_key = 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_CUSTOM . $this->m_catg_custom_id;
            if (!$this->entry_exists($p_object_id, $l_key, $l_new_data_id)) {
                // Create the description entry
                $this->create(
                    $p_object_id,
                    $this->m_catg_custom_id,
                    $l_key,
                    isset($p_category_data['properties']['description'][C__DATA__VALUE]) ? $p_category_data['properties']['description'][C__DATA__VALUE] : '',
                    'commentary',
                    C__RECORD_STATUS__NORMAL,
                    null, // description
                    $l_new_data_id
                );
            } else {
                // Update description only if there is one given
                if (isset($p_category_data['properties']['description'][C__DATA__VALUE])) {
                    $this->save(
                        null,
                        $this->m_catg_custom_id,
                        $l_key,
                        $p_category_data['properties']['description'][C__DATA__VALUE],
                        'commentary',
                        C__RECORD_STATUS__NORMAL,
                        null, // description
                        $l_new_data_id
                    );
                }
            }

            // Description was handled above, so remove it from properties that will be iterated next
            if (isset($p_category_data['properties']['description'])) {
                unset($p_category_data['properties']['description']);
            }

            // Get properties.
            $l_properties = $this->get_properties();

            // Iterate through the rest
            foreach ($p_category_data['properties'] as $l_tag => $l_property) {
                if (!isset($l_properties[$l_tag])) {
                    // This check prevents the creation of non category property entries.
                    continue;
                }

                $l_type = null;
                $l_key = null;
                $l_multiselection = (bool)$l_properties[$l_tag][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][isys_popup_browser_object_ng::C__MULTISELECTION];

                $l_key = strrchr($l_tag, 'c_');
                $l_type = substr($l_tag, 0, strrpos($l_tag, '_c'));

                if (!$l_multiselection && !$this->entry_exists($p_object_id, $l_key, $l_new_data_id)) {
                    $this->create($p_object_id, $this->m_catg_custom_id, $l_key, $l_property[C__DATA__VALUE], $l_type, C__RECORD_STATUS__NORMAL, null, $l_new_data_id);
                }

                // ID-2993 simply removing the "else" method, so that every freshly created row gets processed correctly.
                $l_status = $this->save(null, $this->m_catg_custom_id, $l_key, $l_property[C__DATA__VALUE], $l_type, C__RECORD_STATUS__NORMAL, null, $l_new_data_id);

                if ($l_status === false) {
                    return false;
                }
            }

            if ($l_cache_obj_id !== null) {
                $this->set_object_id($l_cache_obj_id);
            }
        }

        return ($l_indicator === true) ? $l_new_data_id : false;
    }

    /**
     * Retrieve all assigne objects for the multi object browser
     *
     * @param string $p_field_key
     * @param int    $p_data_id
     * @param bool   $p_with_keys
     *
     * @return array
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_assigned_entries($p_field_key, $p_data_id, $p_with_keys = false)
    {
        $l_sql = 'SELECT isys_catg_custom_fields_list__field_content, isys_catg_custom_fields_list__id FROM isys_catg_custom_fields_list WHERE isys_catg_custom_fields_list__isysgui_catg_custom__id = ' .
            $this->convert_sql_id($this->get_catg_custom_id()) . ' AND isys_catg_custom_fields_list__field_key = ' . $this->convert_sql_text($p_field_key) .
            ' AND isys_catg_custom_fields_list__data__id = ' . $this->convert_sql_id($p_data_id);

        $l_res = $this->retrieve($l_sql);
        $l_return = [];
        if ($l_res->num_rows()) {
            while ($l_row = $l_res->get_row()) {
                if ($p_with_keys) {
                    $l_return[$l_row['isys_catg_custom_fields_list__id']] = $l_row['isys_catg_custom_fields_list__field_content'];
                } else {
                    $l_return[] = $l_row['isys_catg_custom_fields_list__field_content'];
                }
            }
        }

        return $l_return;
    }

    /**
     * Save helper method
     *
     * @param null   $p_id
     * @param        $p_custom_id
     * @param        $p_key
     * @param        $p_value
     * @param        $p_type
     * @param        $p_status
     * @param string $p_description
     * @param null   $p_data_id
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function save_helper($p_id = null, $p_custom_id, $p_key, $p_value, $p_type, $p_status, $p_description = '', $p_data_id = null)
    {
        /**
         * @var $l_dao_relation isys_cmdb_dao_category_g_relation
         */
        $l_dao_relation = isys_cmdb_dao_category_g_relation::instance(isys_application::instance()->database)
            ->reset();
        $l_relation_type_data = [];
        $l_return = false;

        // Relation Object browser
        if (self::$m_config[$p_custom_id][$p_key]['popup'] == 'browser_object' && isset(self::$m_config[$p_custom_id][$p_key]['identifier'])) {
            $l_sql_relation = "SELECT isys_catg_custom_fields_list__isys_obj__id, isys_catg_custom_fields_list__id, isys_catg_custom_fields_list__isys_catg_relation_list__id FROM isys_catg_custom_fields_list ";
            if (!isset($this->m_relation_type_data[self::$m_config[$p_custom_id][$p_key]['identifier']])) {
                $l_relation_type_identifier = self::$m_config[$p_custom_id][$p_key]['identifier'];
                $l_relation_type_data = $l_dao_relation->get_relation_type($l_relation_type_identifier, null, true);
            }

            if (is_countable($l_relation_type_data) && count($l_relation_type_data) == 0) {
                unset($l_sql_relation);
            }
        }

        if (!empty($p_id)) {
            $l_sql_condition = 'WHERE isys_catg_custom_fields_list__id = ' . $this->convert_sql_id($p_id) . ';';
        } else {
            $l_sql_condition = 'WHERE isys_catg_custom_fields_list__isysgui_catg_custom__id = ' . $this->convert_sql_id($p_custom_id) . '
                AND isys_catg_custom_fields_list__field_key = ' . $this->convert_sql_text($p_key) . '
                AND isys_catg_custom_fields_list__data__id = ' . $this->convert_sql_id($p_data_id) . ';';
        }

        $l_obj_id = $l_cat_id = $l_relation_id = null;
        if (isset($l_sql_relation)) {
            $l_sql_relation .= $l_sql_condition;
            $l_row = $this->retrieve($l_sql_relation)->get_row();
            $l_relation_id = $l_row['isys_catg_custom_fields_list__isys_catg_relation_list__id'];
            $l_cat_id = $l_row['isys_catg_custom_fields_list__id'];
            $l_obj_id = $l_row['isys_catg_custom_fields_list__isys_obj__id'];
        }

        $l_sql = "UPDATE isys_catg_custom_fields_list
			SET isys_catg_custom_fields_list__isysgui_catg_custom__id = " . $this->convert_sql_id($p_custom_id) . ",
			isys_catg_custom_fields_list__field_key = " . $this->convert_sql_text($p_key) . ",
			isys_catg_custom_fields_list__field_content = " . $this->convert_sql_text($p_value) . ",
			isys_catg_custom_fields_list__field_type = " . $this->convert_sql_text($p_type) . ",
			isys_catg_custom_fields_list__status = " . $this->convert_sql_int($p_status) . ",
			isys_catg_custom_fields_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_custom_fields_list__data__id = " . $this->convert_sql_id($p_data_id) . "
			";

        $l_sql .= $l_sql_condition;

        if ($this->update($l_sql)) {
            $this->m_strLogbookSQL = $l_sql;
            if ($l_return = $this->apply_update()) {
                if (isset($l_sql_relation) && is_countable($l_relation_type_data) && count($l_relation_type_data)) {
                    if ($l_relation_type_data['isys_relation_type__default'] == C__RELATION_DIRECTION__I_DEPEND_ON) {
                        $l_master = $p_value;
                        $l_slave = $l_obj_id;
                    } else {
                        $l_master = $l_obj_id;
                        $l_slave = $p_value;
                    }

                    $l_dao_relation->handle_relation(
                        $l_cat_id,
                        'isys_catg_custom_fields_list',
                        $l_relation_type_data['isys_relation_type__id'],
                        $l_relation_id,
                        $l_master,
                        $l_slave
                    );
                }
            }
        }

        return $l_return;
    }

    /**
     * Get fields using specific dialog identifier
     *
     * @param string $dialogIdentifier
     *
     * @return array
     * @throws isys_exception_database
     */
    public function getFieldKeysByDialogIdentifer($dialogIdentifier)
    {
        // Store for fields using dialog identifier
        $fieldConstants = [];

        // Retrieve custom category configs
        $sql = "SELECT isysgui_catg_custom__config FROM isysgui_catg_custom WHERE TRUE;";
        $sqlRes = $this->retrieve($sql);

        while ($row = $sqlRes->get_row()) {
            // Deserialize category config
            $categoryConfig = unserialize($row['isysgui_catg_custom__config']);

            if ($categoryConfig && is_array($categoryConfig)) {
                // Determine fields which using dialog
                foreach ($categoryConfig as $fieldKey => $fieldConfig) {
                    if (array_key_exists('identifier', $fieldConfig) && $fieldConfig['identifier'] == $dialogIdentifier) {
                        $fieldConstants[] = $fieldKey;
                    }
                }
            }
        }

        return $fieldConstants;
    }

    /**
     * Check whether dialog entry is in use
     *
     * @param string    $dialogIdentifier
     * @param int       $dialogEntryId
     *
     * @return bool
     * @throws isys_exception_database
     */
    public function checkDialogEntryInUse($dialogIdentifier, $dialogEntryId)
    {
        // Retrieve fields using dialog
        $fieldKeys = $this->getFieldKeysByDialogIdentifer($dialogIdentifier);

        if (is_countable($fieldKeys) && count($fieldKeys)) {
            // Retrieve number of entries using this specific dialog entry
            $sql = "SELECT COUNT(*) as used_count from isys_catg_custom_fields_list 
                    WHERE isys_catg_custom_fields_list__field_key IN ('" . implode("','", $fieldKeys) . "') " .
                   "AND isys_catg_custom_fields_list__field_content = " . $this->convert_sql_int($dialogEntryId) . ";";

            return (int)$this->retrieve($sql)->get_row_value('used_count') > 0;
        }

        return false;
    }

    /**
     * Get constant of custom category
     *
     * @return string
     */
    public function get_catg_custom_const()
    {
        return $this->m_category_config['isysgui_catg_custom__const'];
    }

    /**
     * Get data for print view only
     *
     * @param int|string $categoryEntryId
     * @param int|string $objectId
     * @param string     $condition
     * @param string     $filter
     * @param int        $status
     *
     * @return \isys_component_dao_result_interface
     */
    public function getDataForPrintView($categoryEntryId, $objectId, $condition = null, $filter = null, $status = null)
    {
        $report = $this->getReport($objectId);

        if (is_array($report)) {
            try {
                $result = isys_report_dao::instance($this->get_database_component())->query($report['isys_report__query']);
            } catch (Exception $e) {
                $result = [];
            }

            return new isys_component_dao_result_static((array)$result['content']);
        }

        return $this->get_data($categoryEntryId, $objectId, (string)$condition, $filter, $status);
    }

    /**
     * @param $objectId
     *
     * @return array|null|bool
     */
    public function getReport($objectId)
    {
        /**
         * Consider report results first
         *
         * Assumption:  Variable Reports are only available
         *              in single value custom categories.
         */
        // Retrieve custom category config
        $customCategoryConfig = $this->get_config($this->get_catg_custom_id());
        $reportProperties = [];

        // Check whether configuration is present
        if (is_array($customCategoryConfig) && count($customCategoryConfig)) {
            $reportProperties = array_filter($customCategoryConfig, function ($property) {
                // @todo No constant for report browser found
                return $property[C__PROPERTY__UI__TYPE__POPUP] === 'report_browser';
            });
        }

        // Check for variable report
        if (!empty($reportProperties)) {
            try {
                $reportKey = key($reportProperties);

                // Retrieve reportID from category entry
                $resource = $this->retrieve('
                    SELECT isys_catg_custom_fields_list__field_content AS reportId
                    FROM isys_catg_custom_fields_list
                    WHERE isys_catg_custom_fields_list__isys_obj__id = ' . $this->convert_sql_id($objectId) . '
                    AND isys_catg_custom_fields_list__field_key = ' . $this->convert_sql_text($reportKey) . '; 
                ');

                $reportId = null;

                // First choice should be the default report given by field indentifier
                if (isset($reportProperties[$reportKey]['identifier']) && !empty($reportProperties[$reportKey]['identifier'])) {
                    $reportId = $reportProperties[$reportKey]['identifier'];
                }

                // Second choice should be category data
                if ($resource->num_rows()) {
                    $reportId = $resource->get_row_value('reportId');
                }

                // Check whether report is selected
                if ($reportId !== null) {
                    // Get report data
                    $reportDao = isys_report_dao::instance($this->get_database_component());

                    return $reportDao->get_report($reportId);
                }
            } catch (Exception $exception) {
                // Report does not exist
                return null;
            }
        }

        return false;
    }

    /**
     * method for getting title of a dialog plus custom cat value by id
     * @author Illia Polianskyi
     * @param array $ids
     *
     * @return array
     */
    public function getDialogTitleById(array $ids)
    {
        $sql = "SELECT isys_dialog_plus_custom__title as title FROM isys_dialog_plus_custom 
                WHERE isys_dialog_plus_custom__id IN (" . implode(',', $ids) . ")";
        $result = $this->m_db->query($sql);
        $titles = [];
        while ($row = $result->fetch_assoc()) {
            $titles[] = $row['title'];
        }
        return $titles;
    }
}
