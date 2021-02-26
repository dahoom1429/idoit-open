<?php

use idoit\Component\Property\Type\TextProperty;

/**
 * i-doit
 * DAO: Global category for storage controller
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_controller extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'controller';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__CONTROLLER';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * @param int $p_cat_level
     * @param int $p_intOldRecStatus
     *
     * @return bool|int
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus)
    {
        if ($_GET[C__CMDB__GET__CATLEVEL] != -1 && $_GET[C__CMDB__GET__CATLEVEL] > 0) {
            $l_ret = $this->save(
                $_GET[C__CMDB__GET__CATLEVEL],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__STORAGE_CONTROLLER_TYPE'],
                $_POST['C__CATG__STORAGE_CONTROLLER_TITLE'],
                $_POST['C__CATG__STORAGE_CONTROLLER_MANUFACTURER'],
                $_POST['C__CATG__STORAGE_CONTROLLER_MODEL'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()],
                $_POST['C__CATG__STORAGE_CONTROLLER_FIRMWARE']
            );
        } else {
            $l_ret = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__STORAGE_CONTROLLER_TYPE'],
                $_POST['C__CATG__STORAGE_CONTROLLER_TITLE'],
                $_POST['C__CATG__STORAGE_CONTROLLER_MANUFACTURER'],
                $_POST['C__CATG__STORAGE_CONTROLLER_MODEL'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()],
                $_POST['C__CATG__STORAGE_CONTROLLER_FIRMWARE']
            );
            $p_cat_level = -1;
        }

        return $l_ret;
    }

    /**
     * @param $p_data
     * @param $p_object_id
     *
     * @return bool
     */
    public function import($p_data, $p_object_id)
    {
        if (is_array($p_data)) {
            $l_cat_level = 1;

            foreach ($p_data as $l_type => $l_types) {
                foreach ($l_types as $l_data) {
                    if ($l_type) {
                        $l_type_id = isys_import::check_dialog("isys_controller_type", strtoupper($l_type));

                        if ($l_type_id) {
                            if (preg_match("/^.* ([0-9a-zA-Z\/]{5,20}) .*$/", $l_data["name"], $l_reg)) {
                                $l_model = isys_import::check_dialog("isys_controller_model", $l_reg[1]);
                            } else {
                                $l_model = null;
                            }

                            $_POST["C__CATG__STORAGE_CONTROLLER_TYPE"] = $l_type_id;
                            $_POST["C__CATG__STORAGE_CONTROLLER_TITLE"] = $l_data["name"];
                            $_POST["C__CATG__STORAGE_CONTROLLER_MANUFACTURER"] = isys_import::check_dialog("isys_controller_manufacturer", $l_data["manufacturer"]);
                            $_POST["C__CATG__STORAGE_CONTROLLER_MODEL"] = $l_model;
                            $_POST['C__CATG__STORAGE_CONTROLLER_FIRMWARE'] = $l_data["firmware"];
                            $l_status = C__RECORD_STATUS__NORMAL;

                            if (!$_GET[C__CMDB__GET__OBJECT] && !is_null($p_object_id)) {
                                $_GET[C__CMDB__GET__OBJECT] = $p_object_id;
                            }
                            $_GET[C__CMDB__GET__CATLEVEL] = -1;

                            isys_module_request::get_instance()
                                ->_internal_set_private("m_post", $_POST)
                                ->_internal_set_private("m_get", $_GET);

                            $this->save_element($l_cat_level, $l_status);
                        }
                    }
                }
            }

            return true;
        }
    }

    /**
     * Executes the operations to create the category entry for the object referenced by isys_obj__id $p_objID
     * The device type is specified by its constant C__CATG__STORAGE_CONTROLLER_TYPE $p_deviceType
     *
     * @param int    $p_objID
     * @param int    $p_recStatus
     * @param String $p_title
     * @param int    $p_typeID
     * @param int    $p_manufacturerID
     * @param int    $p_modelID
     * @param String $p_description
     * @param String $p_firmware
     *
     * @return int the newly created ID or false
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_recStatus, $p_typeID, $p_title, $p_manufacturerID, $p_modelID, $p_description, $p_firmware)
    {
        $l_update = "INSERT INTO isys_catg_controller_list SET
            isys_catg_controller_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ",
            isys_catg_controller_list__status = " . $this->convert_sql_id($p_recStatus) . ",
            isys_catg_controller_list__isys_controller_type__id = " . $this->convert_sql_id($p_typeID) . ",
            isys_catg_controller_list__title = " . $this->convert_sql_text($p_title) . ",
            isys_catg_controller_list__isys_controller_manufacturer__id = " . $this->convert_sql_id($p_manufacturerID) . ",
            isys_catg_controller_list__isys_controller_model__id = " . $this->convert_sql_id($p_modelID) . ",
            isys_catg_controller_list__firmware = " . $this->convert_sql_text($p_firmware) . ",
            isys_catg_controller_list__description = " . $this->convert_sql_text($p_description) . ";";

        if ($this->update($l_update) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    /**
     * Executes the operations to save the category entry referenced bv its ID $p_cat_level
     * The device type is specified by its constant C__CATG__STORAGE_CONTROLLER_TYPE $p_deviceType.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_recStatus
     * @param   integer $p_typeID
     * @param   string  $p_title
     * @param   integer $p_manufacturerID
     * @param   integer $p_modelID
     * @param   string  $p_description
     * @param   String  $p_firmware
     *
     * @return  boolean
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_recStatus, $p_typeID, $p_title, $p_manufacturerID, $p_modelID, $p_description, $p_firmware)
    {
        $l_update = "UPDATE isys_catg_controller_list SET
            isys_catg_controller_list__status = " . $this->convert_sql_id($p_recStatus) . ",
            isys_catg_controller_list__isys_controller_type__id = " . $this->convert_sql_id($p_typeID) . ",
            isys_catg_controller_list__title = " . $this->convert_sql_text($p_title) . ",
            isys_catg_controller_list__isys_controller_manufacturer__id = " . $this->convert_sql_id($p_manufacturerID) . ",
            isys_catg_controller_list__isys_controller_model__id = " . $this->convert_sql_id($p_modelID) . ",
            isys_catg_controller_list__firmware = " . $this->convert_sql_text($p_firmware) . ",
            isys_catg_controller_list__description = " . $this->convert_sql_text($p_description) . "
            WHERE isys_catg_controller_list__id = " . $this->convert_sql_id($p_cat_level) . ";";

        return $this->update($l_update) && $this->apply_update();
    }

    /**
     * Builds an array with minimal requirements for the sync function.
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_import_array($p_data)
    {
        if (!empty($p_data['type'])) {
            $l_type = isys_import_handler::check_dialog('isys_controller_type', $p_data['type']);
        } else {
            $l_type = null;
        }

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'title' => [
                    'value' => $p_data['title']
                ],
                'type'  => [
                    'value' => $l_type
                ],
                'firmware'     => [
                    'value' => $p_data['firmware']
                ]
            ]
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
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_controller_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_controller_list__title FROM isys_catg_controller_list',
                        'isys_catg_controller_list',
                        'isys_catg_controller_list__id',
                        'isys_catg_controller_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_controller_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__STORAGE_CONTROLLER_TITLE'
                ]
            ]),
            'type'         => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__STORAGE_CONTROLLER_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Type'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_controller_list__isys_controller_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_controller_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_controller_type',
                        'isys_controller_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_controller_type__title FROM isys_catg_controller_list
                            INNER JOIN isys_controller_type ON isys_controller_type__id = isys_catg_controller_list__isys_controller_type__id',
                        'isys_catg_controller_list',
                        'isys_catg_controller_list__id',
                        'isys_catg_controller_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_controller_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_controller_list',
                            'LEFT',
                            'isys_catg_controller_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_controller_type',
                            'LEFT',
                            'isys_catg_controller_list__isys_controller_type__id',
                            'isys_controller_type__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_CONTROLLER_TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_controller_type'
                    ]
                ]
            ]),
            'manufacturer' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__STORAGE_MANUFACTURER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Manufacturer'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_controller_list__isys_controller_manufacturer__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_controller_manufacturer',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_controller_manufacturer',
                        'isys_controller_manufacturer__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_controller_manufacturer__title FROM isys_catg_controller_list
                            INNER JOIN isys_controller_manufacturer ON isys_controller_manufacturer__id = isys_catg_controller_list__isys_controller_manufacturer__id',
                        'isys_catg_controller_list',
                        'isys_catg_controller_list__id',
                        'isys_catg_controller_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_controller_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_controller_list',
                            'LEFT',
                            'isys_catg_controller_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_controller_manufacturer',
                            'LEFT',
                            'isys_catg_controller_list__isys_controller_manufacturer__id',
                            'isys_controller_manufacturer__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_CONTROLLER_MANUFACTURER',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_controller_manufacturer'
                    ]
                ]
            ]),
            'model'        => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__STORAGE_CONTROLLER_MODEL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Model'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_controller_list__isys_controller_model__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_controller_model',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_controller_model',
                        'isys_controller_model__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_controller_model__title FROM isys_catg_controller_list
                            INNER JOIN isys_controller_model ON isys_controller_model__id = isys_catg_controller_list__isys_controller_model__id',
                        'isys_catg_controller_list',
                        'isys_catg_controller_list__id',
                        'isys_catg_controller_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_controller_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_controller_list',
                            'LEFT',
                            'isys_catg_controller_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_controller_model',
                            'LEFT',
                            'isys_catg_controller_list__isys_controller_model__id',
                            'isys_controller_model__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_CONTROLLER_MODEL',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_controller_model'
                    ]
                ]
            ]),
            'firmware'     => new TextProperty(
                'C__CATG__STORAGE_CONTROLLER_FIRMWARE',
                'LC__CATG__STORAGE_CONTROLLER_FIRMWARE',
                'isys_catg_controller_list__firmware',
                'isys_catg_controller_list'
            ),
            'description'  => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_controller_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_controller_list__description FROM isys_catg_controller_list',
                        'isys_catg_controller_list',
                        'isys_catg_controller_list__id',
                        'isys_catg_controller_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_controller_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__CONTROLLER', 'C__CATG__CONTROLLER')
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
                            $p_category_data['properties']['type'][C__DATA__VALUE],
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['manufacturer'][C__DATA__VALUE],
                            $p_category_data['properties']['model'][C__DATA__VALUE],
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
                            $p_category_data['properties']['type'][C__DATA__VALUE],
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['manufacturer'][C__DATA__VALUE],
                            $p_category_data['properties']['model'][C__DATA__VALUE],
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
}
