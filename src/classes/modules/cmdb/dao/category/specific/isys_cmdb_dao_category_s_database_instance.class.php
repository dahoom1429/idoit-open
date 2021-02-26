<?php

/**
 * i-doit
 *
 * DAO: specific category database instances.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_database_instance extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'database_instance';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * @var bool
     */
    protected $m_has_relation = true;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * @var string
     */
    protected $m_object_id_field = 'isys_cats_database_instance_list__isys_obj__id';

    public function callback_property_database_schema_selection(isys_request $p_request)
    {
        global $g_comp_database;
        $l_dao = isys_cmdb_dao_category_s_database_schema::instance($g_comp_database);

        $l_res = $l_dao->get_data(null, null, "AND isys_connection__isys_obj__id = " . $l_dao->convert_sql_id($p_request->get_object_id()));
        $l_arr = [];

        while ($l_data = $l_res->get_row()) {
            $l_arr[] = $l_data['isys_cats_database_schema_list__isys_obj__id'];
        }

        return $l_arr;
    }

    /**
     * Return Category Data
     *
     * @param [int $p_id]h
     * @param [int $p_obj_id]
     * @param [string $p_condition]
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_cats_database_instance_list " . "INNER JOIN isys_obj ON isys_obj__id = isys_cats_database_instance_list__isys_obj__id " .
            "LEFT JOIN isys_connection ON isys_connection__id = isys_cats_database_instance_list__isys_connection__id WHERE TRUE ";

        $l_sql .= $p_condition;

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_cats_list_id)) {
            $l_sql .= " AND (isys_cats_database_instance_list__id = '{$p_cats_list_id}')";
        }

        if (!empty($p_status)) {
            $l_sql .= " AND (isys_cats_database_instance_list__status = '{$p_status}')";
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Get relation filter
     *
     * @return array
     */
    private function getRelationFilter()
    {
        $filterArr = array_map('constant', ['C__RELATION_TYPE__DBMS', 'C__RELATION_TYPE__SOFTWARE', 'C__RELATION_TYPE__CLUSTER_SERVICE']);
        return array_filter($filterArr, function ($constantId) {
            return is_numeric($constantId);
        });
    }

    /**
     * Retrieve Second list Data for Relation object browser
     *
     * @param      $context
     * @param null $request
     *
     * @return false|string|null
     * @throws Exception
     */
    public function getRelationData($context, $request = null)
    {
        $language = isys_application::instance()->container->get('language');

        switch ($context) {
            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PREPARATION:
                // Not used.

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST:
                $return = [];
                $filters = [];
                $relationDao = isys_cmdb_dao_category_g_relation::instance(isys_application::instance()->container->get('database'));

                if ($request[0] !== null) {
                    $filters = array_map('constant', explode(';', $request[0]));
                }

                $query = 'SELECT 
                        isys_catg_relation_list__isys_obj__id, 
                        objMaster.isys_obj__title as mObjectTitle, 
                        objSlave.isys_obj__title as sObjectTitle,
                        objMaster.isys_obj__id as mObjectId, 
                        objSlave.isys_obj__id as sObjectId, 
                        isys_relation_type__title, 
                        isys_catg_version_list__title,
                        isys_catg_version_list__hotfix
                    FROM isys_catg_relation_list
                    INNER JOIN isys_relation_type ON isys_catg_relation_list__isys_relation_type__id = isys_relation_type__id
                    INNER JOIN isys_obj as objMaster ON objMaster.isys_obj__id = isys_catg_relation_list__isys_obj__id__master
                    INNER JOIN isys_obj as objSlave ON objSlave.isys_obj__id = isys_catg_relation_list__isys_obj__id__slave
                    INNER JOIN isys_catg_application_list ON isys_catg_application_list__isys_catg_relation_list__id = isys_catg_relation_list__id
                    LEFT JOIN isys_catg_version_list ON isys_catg_application_list__isys_catg_version_list__id = isys_catg_version_list__id
                    WHERE isys_catg_relation_list__isys_relation_type__id IN (' . implode(',', $this->getRelationFilter()) . ')
                    AND (
                        isys_catg_relation_list__isys_obj__id__master = ' . $this->convert_sql_id($_GET[C__CMDB__GET__OBJECT]) . ' OR
                        isys_catg_relation_list__isys_obj__id__slave = ' . $this->convert_sql_id($_GET[C__CMDB__GET__OBJECT]) . ' 
                    ) AND isys_catg_application_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' 
                    AND objMaster.isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
                    AND objSlave.isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

                $result = $this->retrieve($query);

                while ($row = $result->get_row()) {
                    $return[] = [
                        '__checkbox__'                            => $row['isys_catg_relation_list__isys_obj__id'],
                        $language->get('LC__UNIVERSAL__TITLE')    => ($_GET[C__CMDB__GET__OBJECT] != $row['mObjectId'] ? $row['mObjectTitle'] : $row['sObjectTitle']),
                        $language->get('LC__UNIVERSAL__RELATION') => $language->get($row['isys_relation_type__title']),
                        $language->get('LC__CATG__VERSION_TITLE') => !empty($row['isys_catg_version_list__hotfix']) ?
                            $row['isys_catg_version_list__title'] . ' (' . $row['isys_catg_version_list__hotfix'] . ')': $row['isys_catg_version_list__title']
                    ];
                }

                return isys_format_json::encode($return);

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PRESELECTION:
                // Not necessary, because the selection will only contain objects.
        }

        return null;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'instance'        => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'DBMS',
                    C__PROPERTY__INFO__DESCRIPTION => 'DBMS'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_cats_database_instance_list__isys_connection__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__DBMS'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_s_database_instance',
                        'callback_property_relation_handler'
                    ], [
                        'isys_cmdb_dao_category_s_database_instance',
                        true
                    ]),
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_cats_database_instance_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_cats_database_instance_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id',
                        'isys_cats_database_instance_list',
                        'isys_cats_database_instance_list__id',
                        'isys_cats_database_instance_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_database_instance_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_database_instance_list',
                            'LEFT',
                            'isys_cats_database_instance_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_cats_database_instance_list__isys_connection__id',
                            'isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__DATABASE_INSTANCE__DBMS',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPopupType' => 'browser_object_relation',
                        'relationFilter' => 'C__RELATION_TYPE__DBMS;C__RELATION_TYPE__SOFTWARE;C__RELATION_TYPE__CLUSTER_SERVICE',
                        isys_popup_browser_object_ng::C__SECOND_LIST => [
                            ['isys_cmdb_dao_category_s_database_instance::getRelationData']
                        ]
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
                        'database_instance'
                    ]
                ]
            ]),
            'title'           => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_database_instance_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_database_instance_list__title FROM isys_cats_database_instance_list',
                        'isys_cats_database_instance_list',
                        'isys_cats_database_instance_list__id',
                        'isys_cats_database_instance_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_database_instance_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__DATABASE_INSTANCE__TITLE'
                ]
            ]),
            'listener'        => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'Listener',
                    C__PROPERTY__INFO__DESCRIPTION => 'Listener'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_database_instance_list__listener',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_database_instance_list__listener FROM isys_cats_database_instance_list',
                        'isys_cats_database_instance_list',
                        'isys_cats_database_instance_list__id',
                        'isys_cats_database_instance_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_database_instance_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__DATABASE_INSTANCE__LISTENER'
                ]
            ]),
            'database_schema' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__OBJTYPE__DATABASE_SCHEMA',
                    C__PROPERTY__INFO__DESCRIPTION => 'Database schema'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_database_instance_list__isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                                FROM isys_cats_database_instance_list
                            INNER JOIN isys_cats_database_schema_list ON isys_cats_database_schema_list__isys_cats_db_instance_list__id = isys_cats_database_instance_list__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_cats_database_schema_list__isys_obj__id',
                        'isys_cats_database_instance_list',
                        'isys_cats_database_instance_list__id',
                        'isys_cats_database_instance_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([
                            ' isys_obj__status = ' . C__RECORD_STATUS__NORMAL
                        ]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_database_instance_list__isys_obj__id'])
                    ),
                        C__PROPERTY__DATA__JOIN   => [
                            idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                                'isys_cats_database_instance_list',
                                'LEFT',
                                'isys_cats_database_instance_list__isys_obj__id',
                                'isys_obj__id'
                            ),
                            idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                                'isys_cats_database_schema_list',
                                'LEFT',
                                'isys_cats_database_instance_list__id',
                                'isys_cats_database_schema_list__isys_cats_db_instance_list__id'
                            ),
                            idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_cats_database_schema_list__isys_obj__id', 'isys_obj__id')
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__DATABASE_INSTANCE__CONNECTED',
                        // 'C__CMDB__CATS__DATABASE_INSTANCE__DATABASE_SCHEMA', // This ID does not exist. Found out via ID-700
                        C__PROPERTY__UI__PARAMS => [
                            'multiselection'  => true,
                            'catFilter' => 'C__CATS__DATABASE_SCHEMA',
                            'p_strSelectedID' => new isys_callback([
                                    'isys_cmdb_dao_category_s_database_instance',
                                    'callback_property_database_schema_selection'
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
                            'isys_specific_database_instance_helper',
                            'databaseSchema'
                        ]
                    ]
                ]),
            'description'     => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_database_instance_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_database_instance_list__description FROM isys_cats_database_instance_list',
                        'isys_cats_database_instance_list',
                        'isys_cats_database_instance_list__id',
                        'isys_cats_database_instance_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_database_instance_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__DATABASE_INSTANCE', 'C__CATS__DATABASE_INSTANCE')
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
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                //create(	$p_object_id, $p_title, $p_listener, $p_description, $p_dbms, $p_connected_schemas, $p_status = C__RECORD_STATUS__NORMAL)
                if (($p_category_data['data_id'] = $this->create(
                    $p_object_id,
                    $p_category_data['properties']['title'][C__DATA__VALUE],
                    $p_category_data['properties']['listener'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    $p_category_data['properties']['instance'][C__DATA__VALUE],
                    ($_POST['duplicate'] != '1' ? $p_category_data['properties']['database_schema'][C__DATA__VALUE] : null),
                    C__RECORD_STATUS__NORMAL
                ))) {
                    $l_indicator = true;
                }
            } elseif ($p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data:
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    $p_category_data['properties']['title'][C__DATA__VALUE],
                    $p_category_data['properties']['listener'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    $p_category_data['properties']['instance'][C__DATA__VALUE],
                    $p_category_data['properties']['database_schema'][C__DATA__VALUE],
                    C__RECORD_STATUS__NORMAL
                );
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * @param integer $p_cat_level
     * @param integer &$p_intOldRecStatus
     */
    public function save_element($p_cat_level, &$p_status, $p_create = false)
    {
        if ($_GET[C__CMDB__GET__OBJECT]) {
            $l_catdata = $this->get_data(null, $_GET[C__CMDB__GET__OBJECT])
                ->__to_array();
        }

        if (!$l_catdata) {
            $l_list_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                $_POST["C__CMDB__CATS__DATABASE_INSTANCE__TITLE"],
                $_POST["C__CMDB__CATS__DATABASE_INSTANCE__LISTENER"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST["C__CMDB__CATS__DATABASE_INSTANCE__DBMS__HIDDEN"],
                $_POST["C__CMDB__CATS__DATABASE_INSTANCE__CONNECTED__HIDDEN"],
                C__RECORD_STATUS__NORMAL
            );
            if ($l_list_id > 0) {
                $l_bRet = true;
            }
        } else {
            $l_bRet = $this->save(
                $l_catdata["isys_cats_database_instance_list__id"],
                $_POST["C__CMDB__CATS__DATABASE_INSTANCE__TITLE"],
                $_POST["C__CMDB__CATS__DATABASE_INSTANCE__LISTENER"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST["C__CMDB__CATS__DATABASE_INSTANCE__DBMS__HIDDEN"],
                $_POST["C__CMDB__CATS__DATABASE_INSTANCE__CONNECTED__HIDDEN"],
                C__RECORD_STATUS__NORMAL
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
    public function save($p_id, $p_title, $p_listener, $p_description, $p_dbms, $p_connected_schemas = "", $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_strSql = "UPDATE isys_cats_database_instance_list " . "SET " . "isys_cats_database_instance_list__isys_connection__id = " .
            $this->convert_sql_id($this->handle_connection($p_id, $p_dbms)) . ", " . "isys_cats_database_instance_list__title = " . $this->convert_sql_text($p_title) . ", " .
            "isys_cats_database_instance_list__listener = " . $this->convert_sql_text($p_listener) . ", " . "isys_cats_database_instance_list__description = " .
            $this->convert_sql_text($p_description) . ", " . "isys_cats_database_instance_list__status = " . $this->convert_sql_id($p_status) . " " .

            "WHERE isys_cats_database_instance_list__id = " . $this->convert_sql_id($p_id);

        if ($this->update($l_strSql) && $this->apply_update()) {
            $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);
            $l_dao_db_schema = new isys_cmdb_dao_category_s_database_schema($this->m_db);

            /**
             * Database schema relation
             */
            $l_catdata = $this->get_data($p_id)
                ->get_row();
            $l_connected_res = $this->get_connected_database_schema($l_catdata["isys_cats_database_instance_list__isys_obj__id"], $p_id);

            if ($l_connected_res->num_rows() > 0) {
                while ($l_row = $l_connected_res->get_row()) {
                    $l_schema_data = $l_dao_db_schema->get_data(null, $l_row["isys_obj__id"])
                        ->get_row();
                    $this->detach_connected_database_schema($l_row["isys_obj__id"]);
                    $l_dao_relation->delete_relation($l_schema_data["isys_cats_database_schema_list__isys_catg_relation_list__id"]);
                }
            }

            if (!empty($p_connected_schemas) && $p_connected_schemas != '[]') {
                if (!is_array($p_connected_schemas)) {
                    $l_connected_obj_arr = (array)isys_format_json::decode($p_connected_schemas);
                    if (count($l_connected_obj_arr) === 0) {
                        $l_connected_obj_arr = explode(',', $p_connected_schemas);
                    }
                } else {
                    $l_connected_obj_arr = $p_connected_schemas;
                }

                foreach ($l_connected_obj_arr as $l_obj_id) {
                    if ($this->attach_connected_database_schema($l_obj_id, $l_catdata["isys_cats_database_instance_list__isys_obj__id"], $p_id)) {
                        $l_schema_data = $l_dao_db_schema->get_data(null, $l_obj_id)
                            ->get_row();

                        $l_dao_relation->handle_relation(
                            $l_schema_data["isys_cats_database_schema_list__id"],
                            "isys_cats_database_schema_list",
                            defined_or_default('C__RELATION_TYPE__DATABASE_INSTANCE'),
                            $l_schema_data["isys_cats_database_schema_list__isys_catg_relation_list__id"],
                            $l_catdata["isys_cats_database_instance_list__isys_obj__id"],
                            $l_obj_id
                        );
                    }
                }
            }

            /**
             * DBMS relation
             */
            $l_dao_relation->handle_relation(
                $p_id,
                "isys_cats_database_instance_list",
                defined_or_default('C__RELATION_TYPE__DBMS'),
                $l_catdata["isys_cats_database_instance_list__isys_catg_relation_list__id"],
                $p_dbms,
                $l_catdata["isys_cats_database_instance_list__isys_obj__id"]
            );

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
    public function create($p_object_id, $p_title, $p_listener, $p_description, $p_dbms, $p_connected_schemas, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_connection = new isys_cmdb_dao_connection($this->m_db);

        $l_strSql = "INSERT IGNORE INTO isys_cats_database_instance_list SET " . "isys_cats_database_instance_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) .
            ", " . "isys_cats_database_instance_list__isys_connection__id = " . $this->convert_sql_id($l_connection->add_connection($p_dbms)) . ", " .
            "isys_cats_database_instance_list__title = " . $this->convert_sql_text($p_title) . ", " . "isys_cats_database_instance_list__listener = " .
            $this->convert_sql_text($p_listener) . ", " . "isys_cats_database_instance_list__description = " . $this->convert_sql_text($p_description) . ", " .
            "isys_cats_database_instance_list__status = " . $this->convert_sql_id($p_status) . ";";

        if ($this->update($l_strSql) && $this->apply_update()) {
            $l_last_id = $this->get_last_insert_id();

            $l_dao_db_schema = new isys_cmdb_dao_category_s_database_schema($this->m_db);
            $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);

            if (!empty($p_connected_schemas) && $p_connected_schemas != '[]') {
                if (!is_array($p_connected_schemas)) {
                    $l_connected_obj_arr = (array)isys_format_json::decode($p_connected_schemas);
                    if (count($l_connected_obj_arr) === 0) {
                        $l_connected_obj_arr = explode(',', $p_connected_schemas);
                    }
                } else {
                    $l_connected_obj_arr = $p_connected_schemas;
                }

                foreach ($l_connected_obj_arr as $l_obj_id) {
                    if ($this->attach_connected_database_schema($l_obj_id, $p_object_id, $l_last_id)) {
                        $l_schema_data = $l_dao_db_schema->get_data(null, $l_obj_id)
                            ->get_row();

                        $l_dao_relation->handle_relation(
                            $l_schema_data["isys_cats_database_schema_list__id"],
                            "isys_cats_database_schema_list",
                            defined_or_default('C__RELATION_TYPE__DATABASE_INSTANCE'),
                            $l_schema_data["isys_cats_database_schema_list__isys_catg_relation_list__id"],
                            $p_object_id,
                            $l_obj_id
                        );
                    }
                }
            }

            /**
             * DBMS relation
             */
            $l_dao_relation->handle_relation($l_last_id, "isys_cats_database_instance_list", defined_or_default('C__RELATION_TYPE__DBMS'), null, $p_dbms, $p_object_id);

            return $l_last_id;
        } else {
            return false;
        }
    }

    /**
     * Gets all connected database schemas which are connected to the dbms and instance
     *
     * @param int $p_obj_id
     * @param int $p_instance_id
     *
     * @return resultset
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_connected_database_schema($p_obj_id, $p_instance_id)
    {
        $l_sql = "SELECT * FROM isys_cats_database_schema_list " . "INNER JOIN isys_connection ON isys_connection__id = isys_cats_database_schema_list__isys_connection__id " .
            "INNER JOIN isys_obj ON isys_obj__id = isys_cats_database_schema_list__isys_obj__id " . "WHERE isys_obj__status = " . C__RECORD_STATUS__NORMAL . " AND isys_connection__isys_obj__id = " .
            $this->convert_sql_id($p_obj_id) . " " . "AND isys_cats_database_schema_list__isys_cats_db_instance_list__id = " . $this->convert_sql_id($p_instance_id);

        $l_res = $this->retrieve($l_sql);

        return $l_res;
    }

    /**
     * Attaches database schema to database instance
     *
     * @param int $p_schema_obj_id
     * @param int $p_cat_id
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function attach_connected_database_schema($p_schema_obj_id, $p_dbms_obj_id, $p_cat_id)
    {
        $l_dao_db_schema = new isys_cmdb_dao_category_s_database_schema($this->m_db);
        $l_res = $l_dao_db_schema->get_data(null, $p_schema_obj_id);

        if ($l_res->num_rows() == 0) {
            $l_dao_db_schema->create($p_schema_obj_id, "", "", "", "", "", C__RECORD_STATUS__NORMAL);
        }

        $l_update = "UPDATE isys_cats_database_schema_list " . "INNER JOIN isys_connection ON isys_connection__id = isys_cats_database_schema_list__isys_connection__id " .
            "SET isys_connection__isys_obj__id = " . $this->convert_sql_id($p_dbms_obj_id) . ", " . "isys_cats_database_schema_list__isys_cats_db_instance_list__id = " .
            $this->convert_sql_id($p_cat_id) . " " . "WHERE isys_cats_database_schema_list__isys_obj__id = " . $this->convert_sql_id($p_schema_obj_id);

        if ($this->update($l_update) && $this->apply_update()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Detaches database schema from database instance
     *
     * @param int $p_schema_obj_id
     * @param int $p_cat_id
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function detach_connected_database_schema($p_schema_obj_id)
    {
        $l_update = "UPDATE isys_cats_database_schema_list " . "INNER JOIN isys_connection ON isys_connection__id = isys_cats_database_schema_list__isys_connection__id " .
            "SET isys_connection__isys_obj__id = NULL, " . "isys_cats_database_schema_list__isys_cats_db_instance_list__id = NULL, " .
            "isys_cats_database_schema_list__isys_catg_relation_list__id = NULL " . "WHERE isys_cats_database_schema_list__isys_obj__id = " .
            $this->convert_sql_id($p_schema_obj_id);

        if ($this->update($l_update) && $this->apply_update()) {
            return true;
        } else {
            return false;
        }
    }
}
