<?php

use idoit\Component\Property\Type\DynamicProperty;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Component\Property\Type\IntProperty;
use idoit\Component\Property\Type\FloatProperty;
use idoit\Component\Property\Property;

/**
 * i-doit
 *
 * DAO: global category for memories
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_memory extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'memory';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Dynamic property handling for retrieving the RAM with unit.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function dynamic_property_callback_capacity(array $p_row)
    {
        return isys_convert::retrieveFormattedMemoryByDao($p_row, $this, '__capacity');
    }

    /**
     * Dynamic property handling for retrieving the RAM with unit.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function dynamic_property_callback_total_capacity(array $p_row)
    {
        $l_dao = isys_factory::get_instance('isys_cmdb_dao', isys_application::instance()->database);

        $objectId = ($p_row['isys_catg_memory_list__isys_obj__id'] ?: ($p_row['isys_obj__id'] ?: null));

        if ($objectId === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        $l_res = $l_dao->retrieve('SELECT isys_catg_memory_list__capacity, isys_memory_unit__const, isys_memory_unit__title
            FROM isys_catg_memory_list
            LEFT JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_memory_list__isys_memory_unit__id
            WHERE isys_catg_memory_list__isys_obj__id = ' .
            $l_dao->convert_sql_id($objectId) . '
            AND isys_catg_memory_list__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';');

        if (is_countable($l_res) && count($l_res)) {
            $l_capacity = 0;
            $l_unit = 'C__MEMORY_UNIT__GB';
            $l_unit_title = 'GB';

            while ($l_row = $l_res->get_row()) {
                $l_capacity += $l_row['isys_catg_memory_list__capacity'];

                if ($l_row['isys_memory_unit__const']) {
                    // We use the const to save a further query in "isys_convert::memory()".
                    $l_unit = $l_row['isys_memory_unit__const'];
                    $l_unit_title = $l_row['isys_memory_unit__title'];
                }
            }

            return isys_convert::formatNumber(isys_convert::memory($l_capacity, $l_unit, C__CONVERT_DIRECTION__BACKWARD)) . ' ' . $l_unit_title;
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Import-Handler for category memory
     *
     * @author Dennis Stücken <dstuecken@i-doit.org>
     */
    public function import($p_data)
    {
        $l_status = -1;
        $l_cat = -1;
        $l_arID = [];

        if (is_array($p_data)) {
            $l_object_id = $_GET[C__CMDB__GET__OBJECT];

            foreach ($p_data as $l_data) {
                $l_list_id = $this->create_connector($this->get_table(), $l_object_id);

                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()] = "";

                /* Memory Title */
                if (empty($l_data["title"])) {
                    $l_title = $l_data["form"];

                    if (strlen(($l_data["bank"])) > 1) {
                        $l_title .= " (" . $l_data["bank"] . ")";
                    }
                } else {
                    $l_title = $l_data["title"];
                }

                $_POST['C__CATG__MEMORY_TITLE_ID'] = isys_import::check_dialog("isys_memory_title", $l_title);

                /* Manufacturer */
                $_POST['C__CATG__MEMORY_MANUFACTURER'] = isys_import::check_dialog(
                    "isys_memory_manufacturer",
                    (empty($l_data["manufacturer"])) ? "n/a" : $l_data["manufacturer"]
                );

                /* Unit */
                if (empty($l_data["unit"])) {
                    $l_unit = "MB";
                } else {
                    $l_unit = $l_data["unit"];
                }

                $_POST['C__CATG__MEMORY_UNIT'] = isys_import::check_dialog("isys_memory_unit", $l_unit);

                $_POST['C__CATG__MEMORY_TYPE'] = isys_import::check_dialog("isys_memory_type", $l_data["type"]);

                /* Capacity */
                if (empty($l_data["capacity"])) {
                    $l_capacity = $l_data["size"];
                } else {
                    $l_capacity = $l_data["capacity"];
                }

                $_POST['C__CATG__MEMORY_CAPACITY'] = $l_capacity;
                $_POST["C__CATG__MEMORY_QUANTITY"] = 1;

                $l_arID[] = $this->save_element($l_cat, $l_status, $l_list_id);
            }
        }

        return true;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_newRecStatus
     * @param   integer $p_manufacturerID
     * @param   integer $p_titleID
     * @param   integer $p_typeID
     * @param   integer $p_unitID
     * @param   integer $p_capacity
     * @param   string  $p_description
     *
     * @return  boolean
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_titleID, $p_manufacturerID, $p_typeID, $p_unitID, $p_capacity, $p_description)
    {
        $l_strSql = "UPDATE isys_catg_memory_list SET
			isys_catg_memory_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_memory_list__isys_memory_title__id = " . $this->convert_sql_id($p_titleID) . ",
			isys_catg_memory_list__isys_memory_manufacturer__id  = " . $this->convert_sql_id($p_manufacturerID) . ",
			isys_catg_memory_list__isys_memory_type__id  = " . $this->convert_sql_id($p_typeID) . ",
			isys_catg_memory_list__isys_memory_unit__id  = " . $this->convert_sql_id($p_unitID) . ",
			isys_catg_memory_list__capacity  = " . $this->convert_sql_float(isys_convert::memory($p_capacity, $p_unitID)) . ",
			isys_catg_memory_list__status = " . $this->convert_sql_id($p_newRecStatus) . "
			WHERE isys_catg_memory_list__id = " . $this->convert_sql_id($p_cat_level) . ";";

        return ($this->update($l_strSql) && $this->apply_update());
    }

    /**
     * Save global category menory element
     *
     * @param  integer &$p_cat_level default 0
     * @param  integer &$p_intOldRecStatus
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_intErrorCode = -1;

        if (isys_glob_get_param(C__CMDB__GET__CATLEVEL) == 0 && isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW') &&
            isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__SAVE) {
            $p_create = true;
            if (!isset($_POST['C__CATG__MEMORY_QUANTITY'])) {
                $_POST['C__CATG__MEMORY_QUANTITY'] = 1;
            }
        }

        $l_catdata = $this->get_result()
            ->__to_array();
        $p_intOldRecStatus = $l_catdata["isys_catg_memory_list__status"];

        // Filter number from user input
        $_POST['C__CATG__MEMORY_CAPACITY'] = isys_helper::filter_number($_POST['C__CATG__MEMORY_CAPACITY']);

        if ($p_create) {
            // Overview page and no input was given
            if (isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW') && $_POST['C__CATG__MEMORY_MANUFACTURER'] == -1 && $_POST['C__CATG__MEMORY_TITLE_ID'] == -1 &&
                $_POST['C__CATG__MEMORY_TYPE'] == -1 && $_POST['C__CATG__MEMORY_UNIT'] == -1 && $_POST['C__CATG__MEMORY_CAPACITY'] == null) {
                return null;
            }

            $l_quantity = $_POST["C__CATG__MEMORY_QUANTITY"];

            if (!is_numeric($l_quantity) || $l_quantity < 1) {
                throw new isys_exception_dao("Invalid value for quantity");
            }

            $i = 1;
            while ($i++ <= $l_quantity) {
                $l_id = $this->create(
                    $_GET[C__CMDB__GET__OBJECT],
                    C__RECORD_STATUS__NORMAL,
                    $_POST['C__CATG__MEMORY_TITLE_ID'],
                    $_POST['C__CATG__MEMORY_MANUFACTURER'],
                    $_POST['C__CATG__MEMORY_TYPE'],
                    $_POST['C__CATG__MEMORY_UNIT'],
                    $_POST['C__CATG__MEMORY_CAPACITY'],
                    $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
                );

                if ($l_id != false) {
                    $this->m_strLogbookSQL = $this->get_last_query();
                }
            }
            $p_cat_level = null;

            return $l_id;
        } else {
            // existing entry, so update this

            if (!empty($p_id)) {
                $l_catdata['isys_catg_memory_list__id'] = $p_id;
            }

            if ($l_catdata['isys_catg_memory_list__id'] != "") {
                $l_bRet = $this->save(
                    $l_catdata["isys_catg_memory_list__id"],
                    C__RECORD_STATUS__NORMAL,
                    $_POST['C__CATG__MEMORY_TITLE_ID'],
                    $_POST['C__CATG__MEMORY_MANUFACTURER'],
                    $_POST['C__CATG__MEMORY_TYPE'],
                    $_POST['C__CATG__MEMORY_UNIT'],
                    $_POST['C__CATG__MEMORY_CAPACITY'],
                    $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
                );

                $this->m_strLogbookSQL = $this->get_last_query();
            } elseif ($l_catdata['isys_catg_memory_list__id'] > 0) {
                if ($l_catdata['isys_catg_memory_list__status'] == C__RECORD_STATUS__BIRTH) {
                    $this->delete($l_catdata["isys_catg_memory_list__id"]);
                }
            }
        }

        return $l_bRet == true ? null : $l_intErrorCode;
    }

    /**
     * @param   integer $p_list_id
     *
     * @return  boolean
     * @throws  isys_exception_dao
     */
    public function delete($p_list_id)
    {
        return $this->update('DELETE FROM isys_catg_memory_list WHERE isys_catg_memory_list__id = ' . $this->convert_sql_id($p_list_id) . ';') && $this->apply_update();
    }

    /**
     * Executes the query to create the category entry referenced by isys_catg_memory__id $p_fk_id.
     *
     * @param   integer $p_objID
     * @param   integer $p_newRecStatus
     * @param   integer $p_titleID
     * @param   integer $p_manufacturerID
     * @param   integer $p_typeID
     * @param   integer $p_unitID
     * @param   integer $p_capacity
     * @param   string  $p_description
     *
     * @return  mixed  Integer with the newly created ID or boolean false.
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus, $p_titleID, $p_manufacturerID, $p_typeID, $p_unitID, $p_capacity, $p_description)
    {
        $l_strSql = "INSERT INTO isys_catg_memory_list SET
			isys_catg_memory_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_memory_list__isys_memory_manufacturer__id  = " . $this->convert_sql_id($p_manufacturerID) . ",
			isys_catg_memory_list__isys_memory_title__id = " . $this->convert_sql_id($p_titleID) . ",
			isys_catg_memory_list__isys_memory_type__id  = " . $this->convert_sql_id($p_typeID) . ",
			isys_catg_memory_list__isys_memory_unit__id  = " . $this->convert_sql_id($p_unitID) . ",
			isys_catg_memory_list__capacity  = " . $this->convert_sql_float(isys_convert::memory($p_capacity, $p_unitID)) . ",
			isys_catg_memory_list__status = " . $this->convert_sql_id($p_newRecStatus) . ",
			isys_catg_memory_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ";";

        if ($this->update($l_strSql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    /**
     *
     * @param   integer $p_obj_id
     * @param   integer $p_cat_id
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_memory($p_obj_id = null, $p_cat_id = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_memory_list
			LEFT JOIN isys_memory_unit ON isys_catg_memory_list__isys_memory_unit__id = isys_memory_unit__id
			WHERE isys_catg_memory_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if ($p_obj_id !== null) {
            $l_sql .= ' AND isys_catg_memory_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        }

        if ($p_cat_id !== null) {
            $l_sql .= ' AND isys_catg_memroy_list__id = ' . $this->convert_sql_id($p_cat_id);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Builds an array with minimal requirement for the sync function.
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_import_array($p_data)
    {
        $l_manufacturer = (!empty($p_data['manufacturer']) ? isys_import_handler::check_dialog('isys_memory_manufacturer', $p_data['manufacturer']) : null);
        $l_type = (!empty($p_data['type']) ? isys_import_handler::check_dialog('isys_memory_type', $p_data['type']) : null);
        $l_title = (!empty($p_data['title']) ? isys_import_handler::check_dialog('isys_memory_title', $p_data['title']) : null);

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'title'        => [
                    'value' => $l_title
                ],
                'manufacturer' => [
                    'value' => $l_manufacturer
                ],
                'type'         => [
                    'value' => $l_type
                ],
                'capacity'     => [
                    'value' => $p_data['capacity']
                ],
                'unit'         => [
                    'value' => $p_data['unit']
                ],
                'description'  => [
                    'value' => $p_data['description']
                ]
            ]
        ];
    }

    /**
     * Compares category data for import.
     *
     * @param  array    $p_category_data_values
     * @param  array    $p_object_category_dataset
     * @param  array    $p_used_properties
     * @param  array    $p_comparison
     * @param  integer  $p_badness
     * @param  integer  $p_mode
     * @param  integer  $p_category_id
     * @param  string   $p_unit_key
     * @param  array    $p_category_data_ids
     * @param  mixed    $p_local_export
     * @param  boolean  $p_dataset_id_changed
     * @param  integer  $p_dataset_id
     * @param  isys_log $p_logger
     * @param  string   $p_category_name
     * @param  string   $p_table
     * @param  mixed    $p_cat_multi
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
        $l_title = strtolower($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['title']['title_lang']);
        if (isset($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['capacity']['value_converted'])) {
            $l_capacity = strval(round($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['capacity']['value_converted'], 0));
        } else {
            $l_unit = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['unit']['const'];
            $l_capacity = strval(isys_convert::memory(
                $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['capacity']['value'],
                $l_unit,
                C__CONVERT_DIRECTION__FORMWARD
            ));
        }

        // Iterate through local data sets:
        foreach ($p_object_category_dataset as $l_dataset_key => $l_dataset) {
            $p_dataset_id_changed = false;
            $p_dataset_id = $l_dataset[$p_table . '__id'];

            if (isset($p_already_used_data_ids[$p_dataset_id])) {
                // Skip it because ID has already been used for another entry
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                $p_logger->debug('  Dateset ID "' . $p_dataset_id . '" has already been handled. Skipping to next entry.');
                continue;
            }

            //$p_logger->debug(sprintf('Handle dataset %s.', $p_dataset_id));
            // Test the category data identifier:
            if ($p_category_data_values['data_id'] !== null) {
                if ($p_mode === isys_import_handler_cmdb::C__USE_IDS && $p_category_data_values['data_id'] !== $p_dataset_id) {
                    //$p_logger->debug('Category data identifier is different.');
                    $p_badness[$p_dataset_id]++;
                    $p_dataset_id_changed = true;
                    if ($p_mode === isys_import_handler_cmdb::C__USE_IDS) {
                        continue;
                    }
                }
            }

            if (strtolower($l_dataset['isys_memory_title__title']) === $l_title && $l_dataset['isys_catg_memory_list__capacity'] === $l_capacity) {
                // Check properties
                // We found our dataset
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;

                return;
            } elseif ($l_dataset['isys_memory_title__title'] === $l_title && $l_dataset['isys_catg_memory_list__capacity'] !== $l_capacity) {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY][$l_dataset_key] = $p_dataset_id;
            } else {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
            }
        }
    }

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function dynamic_properties()
    {
        return [
            '_total_capacity' => new DynamicProperty(
                'LC__CATG__CMDB_MEMORY_TOTALCAPACITY',
                'isys_catg_memory_list__isys_obj__id',
                'isys_catg_memory_list',
                [
                    $this,
                    'dynamic_property_callback_total_capacity'
                ]
            ),
            '_capacity' => new DynamicProperty(
                'LC__CMDB_CATG__MEMORY_CAPACITY',
                'isys_catg_memory_list__id',
                'isys_catg_memory_list',
                [
                    $this,
                    'dynamic_property_callback_capacity'
                ]
            )
        ];
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT * FROM isys_catg_memory_list
			 LEFT JOIN isys_obj ON isys_catg_memory_list__isys_obj__id = isys_obj__id
			 LEFT JOIN isys_memory_title ON isys_memory_title__id = isys_catg_memory_list__isys_memory_title__id
			 LEFT JOIN isys_memory_manufacturer ON isys_memory_manufacturer__id = isys_catg_memory_list__isys_memory_manufacturer__id
			 LEFT JOIN isys_memory_type ON isys_memory_type__id = isys_catg_memory_list__isys_memory_type__id
			 LEFT JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_memory_list__isys_memory_unit__id
			 WHERE TRUE " . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= ' AND isys_catg_memory_list__id = ' . $this->convert_sql_id($p_catg_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= ' AND isys_catg_memory_list__status = ' . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'quantity'       => (new IntProperty(
                'C__CATG__MEMORY_QUANTITY',
                'LC__CMDB_CATG__MEMORY_QUANTITY',
                'isys_catg_memory_list__quantity',
                'isys_catg_memory_list'
            ))->setPropertyProvides(
                [
                    Property::C__PROPERTY__PROVIDES__SEARCH     => false,
                    Property::C__PROPERTY__PROVIDES__REPORT     => false,
                    Property::C__PROPERTY__PROVIDES__VALIDATION => false,
                    Property::C__PROPERTY__PROVIDES__IMPORT     => false,
                    Property::C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    Property::C__PROPERTY__PROVIDES__LIST       => false,
                    Property::C__PROPERTY__PROVIDES__EXPORT     => false,
                    Property::C__PROPERTY__PROVIDES__VIRTUAL    => true
                ]
            )->setPropertyUiOffset(
                Property::C__PROPERTY__UI__PARAMS,
                [
                    'p_onChange' => 'idoit.callbackManager.triggerCallback(\'memory__calc_capacity\');',
                    'p_strClass' => 'input-mini'
                ]
            ),
            'title'          => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_memory_list__isys_memory_title__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_memory_title',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_memory_title',
                        'isys_memory_title__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_memory_title__title
                            FROM isys_catg_memory_list
                            INNER JOIN isys_memory_title ON isys_memory_title__id = isys_catg_memory_list__isys_memory_title__id',
                        'isys_catg_memory_list',
                        'isys_catg_memory_list__id',
                        'isys_catg_memory_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_memory_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_memory_list', 'LEFT', 'isys_catg_memory_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_title',
                            'LEFT',
                            'isys_catg_memory_list__isys_memory_title__id',
                            'isys_memory_title__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__MEMORY_TITLE_ID',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'   => 'isys_memory_title',
                        'p_bDbFieldNN' => '0'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_plus',
                        ['isys_memory_title']
                    ]
                ],
            ]),
            'manufacturer'   => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB_CATG__MEMORY_MANUFACTURER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Manufacturer'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_memory_list__isys_memory_manufacturer__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_memory_manufacturer',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_memory_manufacturer',
                        'isys_memory_manufacturer__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_memory_manufacturer__title
                            FROM isys_catg_memory_list
                            INNER JOIN isys_memory_manufacturer ON isys_memory_manufacturer__id = isys_catg_memory_list__isys_memory_manufacturer__id',
                        'isys_catg_memory_list',
                        'isys_catg_memory_list__id',
                        'isys_catg_memory_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_memory_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_memory_list', 'LEFT', 'isys_catg_memory_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_manufacturer',
                            'LEFT',
                            'isys_catg_memory_list__isys_memory_manufacturer__id',
                            'isys_memory_manufacturer__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__MEMORY_MANUFACTURER',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'   => 'isys_memory_manufacturer',
                        'p_bDbFieldNN' => '0'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_plus',
                        ['isys_memory_manufacturer']
                    ]
                ]
            ]),
            'type'           => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB_CATG__MEMORY_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Type'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_memory_list__isys_memory_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_memory_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_memory_type',
                        'isys_memory_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_memory_type__title
                            FROM isys_catg_memory_list
                            INNER JOIN isys_memory_type ON isys_memory_type__id = isys_catg_memory_list__isys_memory_type__id',
                        'isys_catg_memory_list',
                        'isys_catg_memory_list__id',
                        'isys_catg_memory_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_memory_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_memory_list', 'LEFT', 'isys_catg_memory_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_type',
                            'LEFT',
                            'isys_catg_memory_list__isys_memory_type__id',
                            'isys_memory_type__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__MEMORY_TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'   => 'isys_memory_type',
                        'p_bDbFieldNN' => '0'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_plus',
                        ['isys_memory_type']
                    ]
                ]
            ]),
            'total_capacity' => (new FloatProperty(
                'C__CATG__MEMORY_CAPACITY',
                'LC__CATG__CMDB_MEMORY_TOTALCAPACITY',
                'isys_catg_memory_list__capacity',
                'isys_catg_memory_list'
            ))->setPropertyDataOffset(
                Property::C__PROPERTY__DATA__SELECT,
                SelectSubSelect::factory('SELECT CONCAT(ROUND(SUM(isys_catg_memory_list__capacity) / (
                CASE
                  WHEN SUM(isys_catg_memory_list__capacity) >= 1073741824 THEN 1073741824
                  WHEN SUM(isys_catg_memory_list__capacity) >= 1048576 THEN 1048576
                  WHEN SUM(isys_catg_memory_list__capacity) > 1024 THEN 1024 ELSE 1 END
                )),
                (CASE
                  WHEN SUM(isys_catg_memory_list__capacity) >= 1073741824 THEN \' GB\'
                  WHEN SUM(isys_catg_memory_list__capacity) >= 1048576 THEN \' MB\'
                  WHEN SUM(isys_catg_memory_list__capacity) > 1024 THEN \' KB\' ELSE \' B\' END)
                )
                FROM isys_catg_memory_list', 'isys_catg_memory_list', 'isys_catg_memory_list__id', 'isys_catg_memory_list__isys_obj__id')
            )->setPropertyProvides(
                [
                    Property::C__PROPERTY__PROVIDES__LIST       => true,
                    Property::C__PROPERTY__PROVIDES__REPORT     => false,
                    Property::C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    Property::C__PROPERTY__PROVIDES__IMPORT     => false,
                    Property::C__PROPERTY__PROVIDES__EXPORT     => false,
                    Property::C__PROPERTY__PROVIDES__VALIDATION => false,
                    Property::C__PROPERTY__PROVIDES__MULTIEDIT  => false
                ]
            )->mergePropertyUiParams([
                'p_onChange' => 'idoit.callbackManager.triggerCallback(\'memory__calc_capacity\');',
                'p_onKeyUp'  => 'idoit.callbackManager.triggerCallback(\'memory__calc_capacity\');'
            ]),
            'capacity'       => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB_CATG__MEMORY_CAPACITY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Capacity'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_memory_list__capacity',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(\'{mem\', \',\', isys_catg_memory_list__capacity, \',\', isys_memory_unit__title, \'}\')
                            FROM isys_catg_memory_list
                            INNER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_memory_list__isys_memory_unit__id',
                        'isys_catg_memory_list',
                        'isys_catg_memory_list__id',
                        'isys_catg_memory_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_memory_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_memory_list', 'LEFT', 'isys_catg_memory_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_memory_list__isys_memory_unit__id',
                            'isys_memory_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__MEMORY_CAPACITY',
                    C__PROPERTY__UI__PARAMS => [
                        'p_onChange' => 'idoit.callbackManager.triggerCallback(\'memory__calc_capacity\');',
                        'p_onKeyUp'  => 'idoit.callbackManager.triggerCallback(\'memory__calc_capacity\');',
                        'p_strClass' => 'input-medium'
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['memory']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'unit'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'unit'           => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__MEMORY_UNIT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Unit'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_memory_list__isys_memory_unit__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_memory_unit',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_memory_unit',
                        'isys_memory_unit__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_memory_unit__title
                            FROM isys_catg_memory_list
                            INNER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_memory_list__isys_memory_unit__id',
                        'isys_catg_memory_list',
                        'isys_catg_memory_list__id',
                        'isys_catg_memory_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_memory_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_memory_list', 'LEFT', 'isys_catg_memory_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_memory_list__isys_memory_unit__id',
                            'isys_memory_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__MEMORY_UNIT',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'   => 'isys_memory_unit',
                        'p_strClass'   => 'input-mini',
                        'p_bDbFieldNN' => 0,
                        'p_onChange'   => 'idoit.callbackManager.triggerCallback(\'memory__calc_capacity\');',
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false,
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_plus'
                    ]
                ]
            ]),
            'description'    => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_memory_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_memory_list__description FROM isys_catg_memory_list',
                        'isys_catg_memory_list',
                        'isys_catg_memory_list__id',
                        'isys_catg_memory_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_memory_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__MEMORY', 'C__CATG__MEMORY')
                ]
            ])
        ];
    }

    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0) {
                        return $this->create(
                            $p_object_id,
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['manufacturer'][C__DATA__VALUE],
                            $p_category_data['properties']['type'][C__DATA__VALUE],
                            $p_category_data['properties']['unit'][C__DATA__VALUE],
                            $p_category_data['properties']['capacity'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $this->save(
                            $p_category_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['manufacturer'][C__DATA__VALUE],
                            $p_category_data['properties']['type'][C__DATA__VALUE],
                            $p_category_data['properties']['unit'][C__DATA__VALUE],
                            $p_category_data['properties']['capacity'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    }
}
