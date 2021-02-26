<?php

use idoit\Component\Property\Type\TextProperty;

/**
 * i-doit
 *
 * DAO: global category for host adapters (HBA).
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_hba extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'hba';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__HBA';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_catg_hba_list " . "INNER JOIN isys_obj " . "ON " . "isys_obj__id = " . "isys_catg_hba_list__isys_obj__id " .
            "LEFT JOIN isys_controller_manufacturer " . "ON " . "isys_controller_manufacturer__id = " . "isys_catg_hba_list__isys_controller_manufacturer__id " .
            "LEFT JOIN isys_controller_model " . "ON " . "isys_controller_model__id = " . "isys_catg_hba_list__isys_controller_model__id " . "LEFT JOIN isys_hba_type " .
            "ON " . "isys_hba_type__id = " . "isys_catg_hba_list__isys_hba_type__id " .

            "WHERE TRUE ";

        $l_sql .= $p_condition;

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_catg_list_id)) {
            $l_sql .= " AND (isys_catg_hba_list__id = " . $this->convert_sql_id($p_catg_list_id) . ")";
        }

        if (!empty($p_status)) {
            $l_sql .= " AND (isys_catg_hba_list__status = '{$p_status}')";
        }

        return $this->retrieve($l_sql);
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
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_hba_list__title',
                    C__PROPERTY__DATA__INDEX  => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_hba_list__title FROM isys_catg_hba_list',
                        'isys_catg_hba_list',
                        'isys_catg_hba_list__id',
                        'isys_catg_hba_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_hba_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__STORAGE_CONTROLLER_TITLE'
                ]
            ]),
            'type'         => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Typ'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_hba_list__isys_hba_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_hba_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_hba_type',
                        'isys_hba_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_hba_type__title
                            FROM isys_catg_hba_list
                            INNER JOIN isys_hba_type ON isys_hba_type__id = isys_catg_hba_list__isys_hba_type__id',
                        'isys_catg_hba_list',
                        'isys_catg_hba_list__id',
                        'isys_catg_hba_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_hba_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_hba_list', 'LEFT', 'isys_catg_hba_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_hba_type', 'LEFT', 'isys_catg_hba_list__isys_hba_type__id', 'isys_hba_type__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_CONTROLLER_TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_hba_type'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'manufacturer' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__MANUFACTURE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Manufacturer'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_hba_list__isys_controller_manufacturer__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_controller_manufacturer',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_controller_manufacturer',
                        'isys_controller_manufacturer__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_controller_manufacturer__title
                            FROM isys_catg_hba_list
                            INNER JOIN isys_controller_manufacturer ON isys_controller_manufacturer__id = isys_catg_hba_list__isys_controller_manufacturer__id',
                        'isys_catg_hba_list',
                        'isys_catg_hba_list__id',
                        'isys_catg_hba_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_hba_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_hba_list', 'LEFT', 'isys_catg_hba_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_controller_manufacturer',
                            'LEFT',
                            'isys_catg_hba_list__isys_controller_manufacturer__id',
                            'isys_controller_manufacturer__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_CONTROLLER_MANUFACTURER',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_controller_manufacturer'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'model'        => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__STORAGE_MODEL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Model'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_hba_list__isys_controller_model__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_controller_model',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_controller_model',
                        'isys_controller_model__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_controller_model__title
                            FROM isys_catg_hba_list
                            INNER JOIN isys_controller_model ON isys_controller_model__id = isys_catg_hba_list__isys_controller_model__id',
                        'isys_catg_hba_list',
                        'isys_catg_hba_list__id',
                        'isys_catg_hba_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_hba_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_hba_list', 'LEFT', 'isys_catg_hba_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_controller_model',
                            'LEFT',
                            'isys_catg_hba_list__isys_controller_model__id',
                            'isys_controller_model__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_CONTROLLER_MODEL',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_controller_model'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'firmware'     => new TextProperty(
                'C__CATG__STORAGE_CONTROLLER_FIRMWARE',
                'LC__CATG__STORAGE_CONTROLLER_FIRMWARE',
                'isys_catg_hba_list__firmware',
                'isys_catg_hba_list'
            ),
            'description'  => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_hba_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__HBA', 'C__CATG__HBA')
                ]
            ])
        ];
    }

    /**
     * @param array $p_category_data
     * @param int   $p_object_id
     * @param int   $p_status
     *
     * @return bool|int
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

    /**
     * @param int        $p_cat_level
     * @param int        $p_intOldRecStatus
     * @param bool|false $p_create
     *
     * @return bool|int
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        if ($p_create) {
            $l_ret = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__STORAGE_CONTROLLER_TYPE'],
                $_POST['C__CATG__STORAGE_CONTROLLER_TITLE'],
                $_POST['C__CATG__STORAGE_CONTROLLER_MANUFACTURER'],
                $_POST['C__CATG__STORAGE_CONTROLLER_MODEL'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST['C__CATG__STORAGE_CONTROLLER_FIRMWARE']
            );
            $p_cat_level = -1;
        } else {
            $l_ret = $this->save(
                $_GET[C__CMDB__GET__CATLEVEL],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__STORAGE_CONTROLLER_TYPE'],
                $_POST['C__CATG__STORAGE_CONTROLLER_TITLE'],
                $_POST['C__CATG__STORAGE_CONTROLLER_MANUFACTURER'],
                $_POST['C__CATG__STORAGE_CONTROLLER_MODEL'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST['C__CATG__STORAGE_CONTROLLER_FIRMWARE']
            );
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
                        $l_type_id = isys_import::check_dialog("isys_hba_type", strtoupper($l_type));

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

                            if (!$_GET[C__CMDB__GET__OBJECT] && !is_null($p_object_id)) {
                                $_GET[C__CMDB__GET__OBJECT] = $p_object_id;
                            }
                            $_GET[C__CMDB__GET__CATLEVEL] = -1;

                            isys_module_request::get_instance()
                                ->_internal_set_private("m_post", $_POST)
                                ->_internal_set_private("m_get", $_GET);

                            $this->save_element($l_cat_level, $l_catdata["isys_catg_hba_list__status"]);
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
     * @param int    $p_typeID
     * @param String $p_title
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
        $l_update = "INSERT INTO isys_catg_hba_list SET
            isys_catg_hba_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ",
            isys_catg_hba_list__status = " . $this->convert_sql_id($p_recStatus) . ",
            isys_catg_hba_list__isys_hba_type__id = " . $this->convert_sql_id($p_typeID) . ",
            isys_catg_hba_list__title = " . $this->convert_sql_text($p_title) . ",
            isys_catg_hba_list__isys_controller_manufacturer__id = " . $this->convert_sql_id($p_manufacturerID) . ",
            isys_catg_hba_list__isys_controller_model__id = " . $this->convert_sql_id($p_modelID) . ",
            isys_catg_hba_list__description = " . $this->convert_sql_text($p_description) . ",
            isys_catg_hba_list__firmware = " . $this->convert_sql_text($p_firmware);

        if ($this->update($l_update) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    /**
     * Executes the operations to save the category entry referenced bv its ID $p_cat_level
     * The device type is specified by its constant C__CATG__STORAGE_CONTROLLER_TYPE $p_deviceType
     *
     * @param int    $p_cat_level
     * @param int    $p_deviceType
     * @param int    $p_recStatus
     * @param int    $p_typeID
     * @param String $p_title
     * @param int    $p_manufacturerID
     * @param int    $p_modelID
     * @param String $p_description
     * @param String $p_firmware
     *
     * @return boolean true, if operations executed successfully, else false
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_recStatus, $p_typeID, $p_title, $p_manufacturerID, $p_modelID, $p_description, $p_firmware)
    {
        $l_update = "UPDATE isys_catg_hba_list SET
            isys_catg_hba_list__status = " . $this->convert_sql_id($p_recStatus) . ",
            isys_catg_hba_list__isys_hba_type__id = " . $this->convert_sql_id($p_typeID) . ",
            isys_catg_hba_list__title = " . $this->convert_sql_text($p_title) . ",
            isys_catg_hba_list__isys_controller_manufacturer__id = " . $this->convert_sql_id($p_manufacturerID) . ",
            isys_catg_hba_list__isys_controller_model__id = " . $this->convert_sql_id($p_modelID) . ",
            isys_catg_hba_list__description = " . $this->convert_sql_text($p_description) . ",
            isys_catg_hba_list__firmware = " . $this->convert_sql_text($p_firmware) . "
            WHERE isys_catg_hba_list__id = " . $this->convert_sql_id($p_cat_level);

        if ($this->update($l_update)) {
            return $this->apply_update();
        } else {
            return false;
        }
    }

    /**
     * Returns a result set with all FC controllers assigned to the specified object id.
     *
     * @param   integer $p_obj_id
     *
     * @return  isys_component_dao_result
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     */
    public function get_fc_controllers($p_obj_id)
    {
        $l_q = "SELECT isys_catg_hba_list__title, isys_catg_hba_list__id, isys_catg_hba_list__status, isys_hba_type__const " . "FROM isys_catg_hba_list " .
            "INNER JOIN isys_hba_type ON isys_catg_hba_list__isys_hba_type__id = isys_hba_type__id " . "WHERE isys_catg_hba_list__isys_obj__id = " .
            $this->convert_sql_id($p_obj_id) . " " . "AND isys_hba_type__const = 'C__STOR_TYPE_FC_CONTROLLER';";

        return $this->retrieve($l_q);
    }

    /**
     * Retrieves the title of an host-adapter by a given category ID.
     *
     * @param   integer $p_id
     *
     * @return  string
     */
    public function get_device_name($p_id)
    {
        return $this->retrieve('SELECT isys_catg_hba_list__title FROM isys_catg_hba_list WHERE isys_catg_hba_list__id = ' . $this->convert_sql_id($p_id) . ';')
            ->get_row_value('isys_catg_hba_list__title');
    }

    /**
     * Formats the title of the object for the object browser.
     *
     * @param   integer $p_ip_id
     * @param   boolean $p_plain
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function format_selection($p_ip_id, $p_plain = false)
    {
        // We need a DAO for the object name.
        $l_dao = isys_cmdb_dao_category_g_hba::instance($this->m_db);
        $l_quick_info = new isys_ajax_handler_quick_info();

        $l_row = $l_dao->get_data($p_ip_id)
            ->__to_array();

        $l_object_type = $l_dao->get_objTypeID($l_row["isys_catg_hba_list__isys_obj__id"]);

        if (!empty($p_ip_id)) {
            $l_editmode = ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT || isys_glob_get_param("editMode") == C__EDITMODE__ON ||
                    isys_glob_get_param("edit") == C__EDITMODE__ON || isset($this->m_params["edit"])) && !isset($this->m_params["plain"]);

            $l_title = isys_application::instance()->container->get('language')
                    ->get($l_dao->get_objtype_name_by_id_as_string($l_object_type)) . " >> " .
                $l_dao->get_obj_name_by_id_as_string($l_row["isys_catg_hba_list__isys_obj__id"]) . " >> " . $l_row["isys_catg_hba_list__title"];

            if (!$l_editmode && !$p_plain) {
                return $l_quick_info->get_quick_info($l_row["isys_catg_hba_list__isys_obj__id"], $l_title, C__LINK__OBJECT);
            } else {
                return $l_title;
            }
        }

        return isys_application::instance()->container->get('language')
            ->get("LC__CMDB__BROWSER_OBJECT__NONE_SELECTED");
    }
}
