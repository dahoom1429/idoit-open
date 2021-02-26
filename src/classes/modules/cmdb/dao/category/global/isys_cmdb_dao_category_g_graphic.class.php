<?php

use idoit\Component\Property\Type\DynamicProperty;
use idoit\Component\Property\Type\TextProperty;

/**
 * i-doit
 *
 * DAO: global category for graphics
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_graphic extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'graphic';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__GRAPHIC';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Return Category Data
     *
     * @param [int $p_id]
     * @param [int $p_obj_id]
     * @param [string $p_condition]
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_catg_graphic_list " . "INNER JOIN isys_obj " . "ON " . "isys_catg_graphic_list__isys_obj__id = " . "isys_obj__id " .
            "LEFT JOIN isys_memory_unit " . "ON " . "isys_memory_unit__id = " . "isys_catg_graphic_list__isys_memory_unit__id " . "LEFT JOIN isys_graphic_manufacturer " .
            "ON " . "isys_graphic_manufacturer__id = " . "isys_catg_graphic_list__isys_graphic_manufacturer__id " .

            "WHERE TRUE ";

        $l_sql .= $p_condition;

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_catg_list_id)) {
            $l_sql .= " AND (isys_catg_graphic_list__id = " . $this->convert_sql_id($p_catg_list_id) . ")";
        }

        if (!empty($p_status)) {
            $l_sql .= " AND (isys_catg_graphic_list__status = '{$p_status}')";
        }

        return $this->retrieve($l_sql);
    }

    /**
     * @param $p_row
     *
     * @return mixed
     * @throws isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_memory($p_row)
    {
        return isys_convert::retrieveFormattedMemoryByDao($p_row, $this, '__memory');
    }

    /**
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function dynamic_properties()
    {
        return [
            '_memory' => new DynamicProperty(
                'LC__CMDB__CATG__MEMORY',
                'isys_catg_graphic_list__id',
                'isys_catg_graphic_list',
                [
                    $this,
                    'dynamic_property_callback_memory'
                ]
            )
        ];
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'title'        => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_graphic_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_graphic_list__title FROM isys_catg_graphic_list',
                        'isys_catg_graphic_list',
                        'isys_catg_graphic_list__id',
                        'isys_catg_graphic_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_graphic_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__GRAPHIC_TITLE'
                ]
            ]),
            'manufacturer' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__MANUFACTURE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Manufacturer'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_graphic_list__isys_graphic_manufacturer__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_graphic_manufacturer',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_graphic_manufacturer',
                        'isys_graphic_manufacturer__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_graphic_manufacturer__title
                            FROM isys_catg_graphic_list
                            INNER JOIN isys_graphic_manufacturer ON isys_graphic_manufacturer__id = isys_catg_graphic_list__isys_graphic_manufacturer__id',
                        'isys_catg_graphic_list',
                        'isys_catg_graphic_list__id',
                        'isys_catg_graphic_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_graphic_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_graphic_list', 'LEFT', 'isys_catg_graphic_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_graphic_manufacturer',
                            'LEFT',
                            'isys_catg_graphic_list__isys_graphic_manufacturer__id',
                            'isys_graphic_manufacturer__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID      => 'C__CATG__GRAPHIC_MANUFACTURER',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_strTable' => 'isys_graphic_manufacturer'
                    ],
                    C__PROPERTY__UI__DEFAULT => '-1'
                ]
            ]),
            'memory'       => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__MEMORY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Memory'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_graphic_list__memory',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(ROUND(isys_catg_graphic_list__memory / isys_memory_unit__factor), \' \', isys_memory_unit__title)
                            FROM isys_catg_graphic_list
                            INNER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_graphic_list__isys_memory_unit__id',
                        'isys_catg_graphic_list',
                        'isys_catg_graphic_list__id',
                        'isys_catg_graphic_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_graphic_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_graphic_list', 'LEFT', 'isys_catg_graphic_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_graphic_list__isys_memory_unit__id',
                            'isys_memory_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__GRAPHIC_MEMORY',
                    C__PROPERTY__UI__PARAMS => [
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
            'unit'         => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__MEMORY_UNIT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Unit'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_graphic_list__isys_memory_unit__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_memory_unit',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_memory_unit',
                        'isys_memory_unit__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_memory_unit__title
                            FROM isys_catg_graphic_list
                            INNER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_graphic_list__isys_memory_unit__id',
                        'isys_catg_graphic_list',
                        'isys_catg_graphic_list__id',
                        'isys_catg_graphic_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_graphic_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_graphic_list', 'LEFT', 'isys_catg_graphic_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_graphic_list__isys_memory_unit__id',
                            'isys_memory_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__CATG__GRAPHIC_MEMORY_UNIT',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_strTable'   => 'isys_memory_unit',
                        'p_strClass'   => 'input-mini',
                        'p_bDbFieldNN' => 1
                    ],
                    C__PROPERTY__UI__DEFAULT => defined_or_default('C__MEMORY_UNIT__B', 1000)
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__LIST   => false,
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'firmware'     => new TextProperty(
                'C__CATG__GRAPHIC_FIRMWARE',
                'LC__CMDB__CATG__GRAPHIC_FIRMWARE',
                'isys_catg_graphic_list__firmware',
                'isys_catg_graphic_list'
            ),
            'description'  => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_graphic_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_graphic_list__description FROM isys_catg_graphic_list',
                        'isys_catg_graphic_list',
                        'isys_catg_graphic_list__id',
                        'isys_catg_graphic_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_graphic_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__GRAPHIC', 'C__CATG__GRAPHIC')
                ]
            ])
        ];
    }

    /**
     * @param array $p_category_data
     * @param int   $p_object_id
     * @param int   $p_status
     *
     * @return bool|mixed
     */
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
                            $p_category_data['properties']['memory'][C__DATA__VALUE],
                            $p_category_data['properties']['unit'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            $p_category_data['properties']['firmware'][C__DATA__VALUE]
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
                            $p_category_data['properties']['memory'][C__DATA__VALUE],
                            $p_category_data['properties']['unit'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            $p_category_data['properties']['firmware'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * Import-Handler for this category
     *
     * @author Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function import($p_data)
    {
        if (is_countable($p_data) && count($p_data) > 0) {
            /* Iterate through Graphic-Adapters */
            foreach ($p_data as $l_graphic) {
                /* Save / Create */
                $l_status = -1;
                /* Cat-New: 0, Cat-Save: ? */
                $l_cat = -1;

                /* Prepare additional _POST variables */

                /* !empty() checks are done inside isys_import::check_dialog, so they are not needed here */
                $_POST["C__CATG__GRAPHIC_MANUFACTURER"] = isys_import::check_dialog("isys_graphic_manufacturer", $l_graphic["manufacturer"]);

                /* Use MB as default unit, if memory_unit is empty */
                if (empty($l_graphic["memory_unit"])) {
                    $_POST["C__CATG__GRAPHIC_MEMORY_UNIT"] = isys_import::check_dialog("isys_memory_unit", "MB");
                } else {
                    $_POST["C__CATG__GRAPHIC_MEMORY_UNIT"] = isys_import::check_dialog("isys_memory_unit", $l_graphic["memory_unit"]);
                }

                $_POST['C__CATG__GRAPHIC_MEMORY'] = $l_graphic["memory"];
                $_POST['C__CATG__GRAPHIC_TITLE'] = $l_graphic["name"];
                $_POST['C__CATG__GRAPHIC_FIRMWARE'] = $l_graphic["firmware"];

                $l_ids[] = $this->save_element($l_cat, $l_status, true);
            }
        }

        return $l_ids;
    }

    /**
     * Executes the query to create the category entry.
     *
     * @param   integer $p_object_id
     * @param   integer $p_status
     * @param   string  $p_title
     * @param   integer $p_manufacturer_id
     * @param   string  $p_memory
     * @param   integer $p_memory_unit_id
     * @param   string  $p_description
     * @param   string  $p_firmware
     *
     * @return  mixed
     */
    public function create($p_object_id, $p_status, $p_title, $p_manufacturer_id, $p_memory, $p_memory_unit_id, $p_description, $p_firmware)
    {
        $p_memory = isys_convert::memory($p_memory, $p_memory_unit_id);

        $l_sql = "INSERT INTO isys_catg_graphic_list SET
            isys_catg_graphic_list__memory = " . $this->convert_sql_text($p_memory) . ",
            isys_catg_graphic_list__isys_memory_unit__id = " . $this->convert_sql_id($p_memory_unit_id) . ",
            isys_catg_graphic_list__title = " . $this->convert_sql_text($p_title) . ",
            isys_catg_graphic_list__isys_graphic_manufacturer__id = " . $this->convert_sql_id($p_manufacturer_id) . ",
            isys_catg_graphic_list__status = " . $this->convert_sql_id($p_status) . ",
            isys_catg_graphic_list__firmware  = " . $this->convert_sql_text($p_firmware) . ",
            isys_catg_graphic_list__description = " . $this->convert_sql_text($p_description) . ",
            isys_catg_graphic_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ";";

        if ($this->update($l_sql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        }

        return false;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_status
     * @param   string  $p_title
     * @param   string  $p_manufacturer_id
     * @param   string  $p_memory
     * @param   string  $p_memory_unit_id
     * @param   string  $p_description
     * @param   string  $p_firmware
     *
     * @return  boolean
     */
    public function save($p_cat_level, $p_status, $p_title, $p_manufacturer_id, $p_memory, $p_memory_unit_id, $p_description, $p_firmware)
    {
        $p_memory = isys_convert::memory($p_memory, $p_memory_unit_id);

        $l_strSql = "UPDATE isys_catg_graphic_list SET
            isys_catg_graphic_list__memory = " . $this->convert_sql_text($p_memory) . ",
            isys_catg_graphic_list__isys_memory_unit__id = " . $this->convert_sql_id($p_memory_unit_id) . ",
            isys_catg_graphic_list__title = " . $this->convert_sql_text($p_title) . ",
            isys_catg_graphic_list__isys_graphic_manufacturer__id = " . $this->convert_sql_id($p_manufacturer_id) . ",
            isys_catg_graphic_list__status = " . $this->convert_sql_id($p_status) . ",
            isys_catg_graphic_list__firmware  = " . $this->convert_sql_text($p_firmware) . ",
            isys_catg_graphic_list__description = " . $this->convert_sql_text($p_description) . "
            WHERE isys_catg_graphic_list__id = " . $this->convert_sql_id($p_cat_level) . ";";

        return ($this->update($l_strSql) && $this->apply_update());
    }

    /**
     * Save element method.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_status
     * @param   boolean $p_create
     *
     * @return  mixed
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_status, $p_create = false)
    {
        $l_catdata = $this->get_result()
            ->__to_array();
        $p_status = $l_catdata["isys_catg_graphic_list__status"];

        try {
            if ($p_create) {
                $l_id = $this->create(
                    $_GET[C__CMDB__GET__OBJECT],
                    C__RECORD_STATUS__NORMAL,
                    $_POST["C__CATG__GRAPHIC_TITLE"],
                    $_POST["C__CATG__GRAPHIC_MANUFACTURER"],
                    $_POST["C__CATG__GRAPHIC_MEMORY"],
                    $_POST["C__CATG__GRAPHIC_MEMORY_UNIT"],
                    $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                    $_POST['C__CATG__GRAPHIC_FIRMWARE']
                );

                if ($l_id != false) {
                    $this->m_strLogbookSQL = $this->get_last_query();
                    $p_cat_level = null;

                    return $l_id;
                }
            } else {
                $l_bRet = $this->save(
                    $l_catdata["isys_catg_graphic_list__id"],
                    $l_catdata["isys_catg_graphic_list__status"],
                    $_POST["C__CATG__GRAPHIC_TITLE"],
                    $_POST["C__CATG__GRAPHIC_MANUFACTURER"],
                    $_POST["C__CATG__GRAPHIC_MEMORY"],
                    $_POST["C__CATG__GRAPHIC_MEMORY_UNIT"],
                    $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                    $_POST['C__CATG__GRAPHIC_FIRMWARE']
                );

                if ($l_bRet) {
                    $this->m_strLogbookSQL = $this->get_last_query();

                    return null;
                }
            }
        } catch (isys_exception_cmdb $e) {
            isys_glob_display_message("Graphic: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Builds an array with minimal requirement for the sync function
     *
     * @param $p_data
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_import_array($p_data)
    {
        if (!empty($p_data['manufacturer'])) {
            $l_manufacturer = isys_import_handler::check_dialog('isys_graphic_manufacturer', $p_data['manufacturer']);
        } else {
            $l_manufacturer = null;
        }

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'title'        => [
                    'value' => $p_data['title']
                ],
                'manufacturer' => [
                    'value' => $l_manufacturer
                ],
                'memory'       => [
                    'value' => $p_data['memory']
                ],
                'unit'         => [
                    'value' => $p_data['unit']
                ],
                'description'  => [
                    'value' => $p_data['description']
                ],
                'firmware'     => [
                    'value' => $p_data['firmware']
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
        $l_title = strtolower($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['title']['value']);
        if (isset($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['memory']['value_converted'])) {
            $l_memory = round($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['memory']['value_converted'], 0);
        } else {
            $l_unit = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['unit']['const'];
            $l_memory = strval(round(isys_convert::memory(
                $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['memory']['value'],
                $l_unit,
                C__CONVERT_DIRECTION__FORMWARD
            ), 0));
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

            $l_dataset_title = strtolower($l_dataset['isys_catg_graphic_list__title']);
            if ($l_dataset_title === $l_title && $l_dataset['isys_catg_graphic_list__memory'] == $l_memory) {
                // Check properties
                // We found our dataset
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;

                return;
            } elseif ($l_dataset_title === $l_title && $l_dataset['isys_catg_graphic_list__memory'] !== $l_memory) {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY][$l_dataset_key] = $p_dataset_id;
            } else {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
            }
        }
    }
}
