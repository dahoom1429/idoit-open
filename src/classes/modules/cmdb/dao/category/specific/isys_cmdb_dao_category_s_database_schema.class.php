<?php

use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: specific category database schemas.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_database_schema extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'database_schema';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * @var bool
     */
    protected $m_has_relation = true;

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * @var string
     */
    protected $m_object_id_field = 'isys_cats_database_schema_list__isys_obj__id';

    /**
     * Dynamic property handling for retrieving the object name with a link.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_instance(array $p_row)
    {
        global $g_comp_database;

        $objId = ($p_row['isys_cats_database_schema_list__isys_obj__id'] ?: ($p_row['isys_obj__id'] ?: null));

        if ($objId === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        $l_return = '';

        $l_dao = isys_cmdb_dao_category_s_database_schema::instance($g_comp_database);

        $l_row = $l_dao->get_data(null, $objId)
            ->get_row();

        if ($l_row !== false && $l_row['isys_cats_database_instance_list__id'] > 0) {
            $l_instance_row = isys_cmdb_dao_category_s_database_instance::instance($g_comp_database)
                ->get_data($l_row['isys_cats_database_instance_list__id'])
                ->get_row();

            $l_return = $l_instance_row["isys_obj__title"] . " " . strtolower(isys_application::instance()->container->get('language')
                    ->get("LC__UNIVERSAL__ON")) . " " . $l_dao->get_obj_name_by_id_as_string($l_instance_row["isys_connection__isys_obj__id"]);
        }

        return $l_return;
    }

    public function callback_property_instance(isys_request $p_request)
    {
        global $g_comp_database;

        $l_dao_instance = isys_cmdb_dao_category_s_database_instance::instance($g_comp_database);
        $l_instances = $l_dao_instance->get_data();
        $l_on = strtolower(isys_application::instance()->container->get('language')
            ->get("LC__UNIVERSAL__ON"));
        $l_arInstances = [];

        while ($l_row = $l_instances->get_row()) {
            $l_arInstances[$l_row["isys_obj__id"]] = $l_row["isys_obj__title"] . " " . $l_on . " " .
                $l_dao_instance->get_obj_name_by_id_as_string($l_row["isys_connection__isys_obj__id"]);
        }

        return $l_arInstances;
    }

    /**
     * @param integer $p_cat_level
     * @param integer &$p_intOldRecStatus
     */
    public function save_element($p_cat_level, &$p_status, $p_create = false)
    {
        $l_catdata = $this->get_data(null, $_GET[C__CMDB__GET__OBJECT])
            ->__to_array();

        if (!$l_catdata) {
            $l_list_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                $_POST["C__CMDB__CATS__DB_SCHEMA__RUNS_ON"],
                $_POST["C__CMDB__CATS__DB_SCHEMA__TITLE"],
                $_POST["C__CMDB__CATS__DB_SCHEMA__STORAGE_ENGINE"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );
            $l_bRet = ($l_list_id > 0) ? true : false;
        } else {
            $p_status = $l_catdata["isys_cats_database_schema_list__status"];
            $l_list_id = $l_catdata["isys_cats_database_schema_list__id"];
            $l_bRet = $this->save(
                $l_catdata["isys_cats_database_schema_list__id"],
                $_POST["C__CMDB__CATS__DB_SCHEMA__RUNS_ON"],
                $_POST["C__CMDB__CATS__DB_SCHEMA__TITLE"],
                $_POST["C__CMDB__CATS__DB_SCHEMA__STORAGE_ENGINE"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );
        }

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_bRet == true ? $l_list_id : -1;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level
     *
     * @return boolean true, if transaction executed successfully, else false
     * @author Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function save($p_id, $p_runs_on, $p_title, $p_storage_engine, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_strSql = "UPDATE isys_cats_database_schema_list " . "SET " . "isys_cats_database_schema_list__isys_connection__id = " .
            $this->convert_sql_id($this->handle_connection($p_id, $p_runs_on)) . ", " . "isys_cats_database_schema_list__title = " . $this->convert_sql_text($p_title) . ", " .
            "isys_cats_database_schema_list__storage_engine = " . $this->convert_sql_text($p_storage_engine) . ", " . "isys_cats_database_schema_list__description = " .
            $this->convert_sql_text($p_description) . ", " . "isys_cats_database_schema_list__status = " . $this->convert_sql_id($p_status) . " " .

            "WHERE isys_cats_database_schema_list__id = " . $this->convert_sql_id($p_id);

        if ($this->update($l_strSql) && $this->apply_update()) {
            $l_catdata = $this->get_data($p_id)
                ->get_row();
            $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);

            if (isset($p_runs_on) && $p_runs_on > 0) {
                $l_dao_db_instance = new isys_cmdb_dao_category_s_database_instance($this->get_database_component());
                $l_db_instance_data = $l_dao_db_instance->get_data(null, $p_runs_on)
                    ->__to_array();

                $l_dao_db_instance->attach_connected_database_schema(
                    $l_catdata["isys_cats_database_schema_list__isys_obj__id"],
                    $p_runs_on,
                    $l_db_instance_data["isys_cats_database_instance_list__id"]
                );

                $l_dao_relation->handle_relation(
                    $p_id,
                    "isys_cats_database_schema_list",
                    defined_or_default('C__RELATION_TYPE__DATABASE_INSTANCE'),
                    $l_catdata["isys_cats_database_schema_list__isys_catg_relation_list__id"],
                    $p_runs_on,
                    $l_catdata["isys_cats_database_schema_list__isys_obj__id"]
                );
            } else {
                $this->detach_dbms($l_catdata["isys_obj__id"]);
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Executes the query to create the category entry
     *
     * @return int the newly created ID or false
     * @author Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function create($p_object_id, $p_runs_on, $p_title, $p_storage_engine, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_dao_connection = new isys_cmdb_dao_connection($this->m_db);
        $l_connection_id = $l_dao_connection->add_connection($p_runs_on);

        $l_strSql = "INSERT IGNORE INTO isys_cats_database_schema_list SET " . "isys_cats_database_schema_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ", " .
            "isys_cats_database_schema_list__isys_connection__id = " . $this->convert_sql_id($l_connection_id) . ", " . "isys_cats_database_schema_list__title = " .
            $this->convert_sql_text($p_title) . ", " . "isys_cats_database_schema_list__storage_engine = " . $this->convert_sql_text($p_storage_engine) . ", " .
            "isys_cats_database_schema_list__description = " . $this->convert_sql_text($p_description) . ", " . "isys_cats_database_schema_list__status = " .
            $this->convert_sql_id($p_status) . ";";

        if ($this->update($l_strSql) && $this->apply_update()) {
            $l_last_id = $this->get_last_insert_id();

            if (isset($p_runs_on) && $p_runs_on > 0) {
                $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);
                $l_dao_db_instance = new isys_cmdb_dao_category_s_database_instance($this->get_database_component());
                $l_db_instance_data = $l_dao_db_instance->get_data(null, $p_runs_on)
                    ->__to_array();

                $l_dao_db_instance->attach_connected_database_schema($p_object_id, $p_runs_on, $l_db_instance_data["isys_cats_database_instance_list__id"]);

                $l_dao_relation->handle_relation($l_last_id, "isys_cats_database_schema_list", defined_or_default('C__RELATION_TYPE__DATABASE_INSTANCE'), null, $p_runs_on, $p_object_id);
            }

            return $l_last_id;
        } else {
            return false;
        }
    }

    public function detach_dbms($p_obj_id)
    {
        $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);
        $l_catdata = $this->get_data(null, $p_obj_id)
            ->get_row();

        $l_update = "UPDATE isys_cats_database_schema_list SET " . "isys_cats_database_schema_list__isys_cats_db_instance_list__id = NULL, " .
            "isys_cats_database_schema_list__isys_catg_relation_list__id = NULL " . "WHERE isys_cats_database_schema_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id);

        if ($this->update($l_update) && $this->apply_update()) {
            if ($l_dao_relation->delete_relation($l_catdata["isys_cats_database_schema_list__isys_catg_relation_list__id"])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_instance' => new DynamicProperty(
                'LC__OBJTYPE__DATABASE_INSTANCE',
                'isys_cats_database_schema_list__isys_obj__id',
                'isys_cats_database_schema_list',
                [
                    $this,
                    'dynamic_property_callback_instance'
                ]
            ),
        ];
    }

    /**
     * Return Category Data.
     *
     * @param integer $p_cats_list_id
     * @param mixed   $p_obj_id
     * @param string  $p_condition
     * @param mixed   $p_filter
     * @param integer $p_status
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_cats_database_schema_list
			INNER JOIN isys_obj ON isys_obj__id = isys_cats_database_schema_list__isys_obj__id
			LEFT JOIN isys_connection ON isys_connection__id = isys_cats_database_schema_list__isys_connection__id
			LEFT JOIN isys_cats_database_instance_list ON isys_cats_database_instance_list__id = isys_cats_database_schema_list__isys_cats_db_instance_list__id
			WHERE TRUE ' . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_cats_list_id !== null) {
            $l_sql .= " AND isys_cats_database_schema_list__id = " . $this->convert_sql_id($p_cats_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_cats_database_schema_list__status = " . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'link'           => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER_SERVICE__RUNS_ON',
                    C__PROPERTY__INFO__DESCRIPTION => 'Runs on'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_database_schema_list__isys_connection__id'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__DB_SCHEMA__RUNS_ON',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => ''
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connection'
                    ]
                ]
            ]),
            'instance'       => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__OBJTYPE__DATABASE_INSTANCE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Database instance',
                    C__PROPERTY__INFO__BACKWARD    => true
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_cats_database_schema_list__isys_cats_db_instance_list__id',
                    C__PROPERTY__DATA__SOURCE_TABLE     => 'isys_cats_database_instance_list',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__DATABASE_INSTANCE'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_s_database_schema',
                        'callback_property_relation_handler'
                    ], [
                        'isys_cmdb_dao_category_s_database_schema',
                        true
                    ]),
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_cats_database_instance_list',
                        'isys_cats_database_instance_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(inst.isys_obj__title, \' {\', inst.isys_obj__id, \'}\', \' LC__UNIVERSAL__ON \', rel.isys_obj__title, \' {\', rel.isys_obj__id, \'}\')
                            FROM isys_cats_database_schema_list
                            INNER JOIN isys_cats_database_instance_list ON isys_cats_database_instance_list__id = isys_cats_database_schema_list__isys_cats_db_instance_list__id
                            INNER JOIN isys_obj AS inst ON inst.isys_obj__id = isys_cats_database_instance_list__isys_obj__id
                            INNER JOIN isys_connection ON isys_connection__id = isys_cats_database_instance_list__isys_connection__id
                            INNER JOIN isys_obj AS rel ON rel.isys_obj__id = isys_connection__isys_obj__id',
                        'isys_cats_database_schema_list',
                        'isys_cats_database_schema_list__id',
                        'isys_cats_database_schema_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_database_schema_list__isys_obj__id'])
                    ),
                        C__PROPERTY__DATA__JOIN             => [
                            idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                                'isys_cats_database_schema_list',
                                'LEFT',
                                'isys_cats_database_schema_list__isys_obj__id',
                                'isys_obj__id'
                            ),
                            idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                                'isys_cats_database_instance_list',
                                'LEFT',
                                'isys_cats_database_schema_list__isys_cats_db_instance_list__id',
                                'isys_cats_database_instance_list__id'
                            ),
                            idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                                'isys_obj',
                                'LEFT',
                                'isys_cats_database_instance_list__isys_obj__id',
                                'isys_obj__id',
                                '',
                                'inst',
                                'inst'
                            ),
                            idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                                'isys_connection',
                                'LEFT',
                                'isys_cats_database_instance_list__isys_connection__id',
                                'isys_connection__id',
                                '',
                                '',
                                '',
                                'isys_cats_database_instance_list'
                            ),
                            idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id', '', 'rel', 'rel')
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => '',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_cats_database_instance_list',
                            'p_arData'   => new isys_callback([
                                    'isys_cmdb_dao_category_s_database_schema',
                                    'callback_property_instance'
                                ])
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => true,
                        C__PROPERTY__PROVIDES__LIST   => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_reference_value'
                        ]
                    ]
                ]),
            'title'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_database_schema_list__title'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__DB_SCHEMA__TITLE'
                ]
            ]),
            'storage_engine' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'Storage-Engine',
                    C__PROPERTY__INFO__DESCRIPTION => 'Storage-Engine'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_database_schema_list__storage_engine'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__DB_SCHEMA__STORAGE_ENGINE'
                ]
            ]),
            'description'    => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_database_schema_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__DATABASE_SCHEMA', 'C__CATS__DATABASE_SCHEMA')
                ]
            ])
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param array $p_category_data Values of category data to be saved.
     * @param int   $p_object_id     Current object identifier (from database)
     * @param int   $p_status        Decision whether category data should be created or
     *                               just updated.
     *
     * @return mixed Returns category data identifier (int) on success, true
     * (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            if ($p_category_data['properties']['link'][C__DATA__VALUE] > 0) {
                $l_dao_db_instance = new isys_cmdb_dao_category_s_database_instance($this->get_database_component());
                $l_db_instance_data = $l_dao_db_instance->get_data(null, $p_category_data['properties']['link'][C__DATA__VALUE])
                    ->__to_array();
                if (!$l_db_instance_data) {
                    $l_dao_db_instance->create(
                        $p_category_data['properties']['link'][C__DATA__VALUE],
                        $this->get_obj_name_by_id_as_string($p_category_data['properties']['link'][C__DATA__VALUE]),
                        null,
                        null,
                        null,
                        null
                    );
                }
            }
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                if (($p_category_data['data_id'] = $this->create(
                    $p_object_id,
                    $p_category_data['properties']['link'][C__DATA__VALUE],
                    $p_category_data['properties']['title'][C__DATA__VALUE],
                    $p_category_data['properties']['storage_engine'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    C__RECORD_STATUS__NORMAL
                ))) {
                    $l_indicator = true;
                }
            } elseif ($p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data:
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    $p_category_data['properties']['link'][C__DATA__VALUE],
                    $p_category_data['properties']['title'][C__DATA__VALUE],
                    $p_category_data['properties']['storage_engine'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    C__RECORD_STATUS__NORMAL
                );
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }
}
