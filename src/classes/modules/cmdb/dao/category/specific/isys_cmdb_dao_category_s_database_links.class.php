<?php

/**
 * i-doit
 *
 * DAO: Specific category DBMS.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_database_links extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'database_links';

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
    protected $m_object_id_field = 'isys_cats_database_links_list__isys_obj__id';

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

        $l_sql = "SELECT " . "db_schema.isys_obj__title as schema_title, " . "link.*, " . "isys_cats_database_links_list.*, " . "isys_connection.* " .
            "FROM isys_cats_database_links_list " . "INNER JOIN isys_obj link " . "ON " . "link.isys_obj__id = " . "isys_cats_database_links_list__isys_obj__id " .
            "LEFT OUTER JOIN isys_connection " . "ON " . "isys_connection__id = " . "isys_cats_database_links_list__isys_connection__id " .
            "LEFT OUTER JOIN isys_obj db_schema " . "ON " . "isys_connection__isys_obj__id = " . "db_schema.isys_obj__id " . "WHERE TRUE ";

        $l_sql .= $p_condition;

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_cats_list_id)) {
            $l_sql .= " AND (isys_cats_database_links_list__id = '{$p_cats_list_id}')";
        }

        if (!empty($p_status)) {
            $l_sql .= " AND (isys_cats_database_links_list__status = '{$p_status}')";
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Creates the condition to the object table
     *
     * @param int|array $p_obj_id
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                $l_sql = ' AND (link.isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (link.isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'title'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_database_links_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_database_links_list__title FROM isys_cats_database_links_list',
                        'isys_cats_database_links_list',
                        'isys_cats_database_links_list__id',
                        'isys_cats_database_links_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_database_links_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__DATABASE_LINKS__TITLE'
                ]
            ]),
            'link'        => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__DATABASE_SCHEMA__CONNECTED_DATABASE_SCHEMA',
                    C__PROPERTY__INFO__DESCRIPTION => 'Connected database schema'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_cats_database_links_list__isys_connection__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__DATABASE_LINK'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_s_database_links',
                        'callback_property_relation_handler'
                    ], [
                        'isys_cmdb_dao_category_s_database_links',
                        true
                    ]),
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_obj__title
                            FROM isys_cats_database_links_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_cats_database_links_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id',
                        'isys_cats_database_links_list',
                        'isys_cats_database_links_list__id',
                        'isys_cats_database_links_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_database_links_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_database_links_list',
                            'LEFT',
                            'isys_cats_database_links_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_cats_database_links_list__isys_connection__id',
                            'isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__DATABASE_LINKS__SCHEMA',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPopupType' => 'browser_object_ng',
                        'catFilter'      => 'C__CATS__DATABASE_SCHEMA'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__LIST   => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connection'
                    ]
                ]
            ]),
            'target_user' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__DATABASE_LINKS__TARGET_USER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Target user'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_database_links_list__target_user',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_database_links_list__target_user FROM isys_cats_database_links_list',
                        'isys_cats_database_links_list',
                        'isys_cats_database_links_list__id',
                        'isys_cats_database_links_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_database_links_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__DATABASE_LINKS__TARGET_USER'
                ]
            ]),
            'owner'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__DATABASE_LINKS__OWNER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Owner'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_database_links_list__owner',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_database_links_list__owner FROM isys_cats_database_links_list',
                        'isys_cats_database_links_list',
                        'isys_cats_database_links_list__id',
                        'isys_cats_database_links_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_database_links_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__DATABASE_LINKS__OWNER'
                ]
            ]),
            'public'      => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO   => [
                    C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__PUBLIC',
                    C__PROPERTY__INFO__DESCRIPTION => 'Public'
                ],
                C__PROPERTY__DATA   => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_database_links_list__public',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_cats_database_links_list__public = \'1\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__YES') . '
                        	    WHEN isys_cats_database_links_list__public = \'0\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__NO') . ' END)
                                FROM isys_cats_database_links_list',
                        'isys_cats_database_links_list',
                        'isys_cats_database_links_list__id',
                        'isys_cats_database_links_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_database_links_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_database_links_list',
                            'LEFT',
                            'isys_cats_database_links_list__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI     => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__DATABASE_LINKS__PUBLIC',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => get_smarty_arr_YES_NO()
                    ]
                ],
                C__PROPERTY__FORMAT => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_yes_or_no'
                    ]
                ]
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_database_links_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_database_links_list__description FROM isys_cats_database_links_list',
                        'isys_cats_database_links_list',
                        'isys_cats_database_links_list__id',
                        'isys_cats_database_links_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_database_links_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__DATABASE_LINKS', 'C__CATS__DATABASE_LINKS')
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
                if ($p_category_data['data_id'] = $this->create(
                    $p_object_id,
                    $p_category_data['properties']['link'][C__DATA__VALUE],
                    $p_category_data['properties']['title'][C__DATA__VALUE],
                    $p_category_data['properties']['target_user'][C__DATA__VALUE],
                    $p_category_data['properties']['owner'][C__DATA__VALUE],
                    $p_category_data['properties']['public'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    C__RECORD_STATUS__NORMAL
                )) {
                    $l_indicator = true;
                }
            } elseif ($p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data:
                //($p_id, $p_schema_object, $p_title, $p_target_user, $p_owner, $p_public, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    $p_category_data['properties']['link'][C__DATA__VALUE],
                    $p_category_data['properties']['title'][C__DATA__VALUE],
                    $p_category_data['properties']['target_user'][C__DATA__VALUE],
                    $p_category_data['properties']['owner'][C__DATA__VALUE],
                    $p_category_data['properties']['public'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
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
        if ($_GET[C__CMDB__GET__CATLEVEL]) {
            $l_catdata = $this->get_data($_GET[C__CMDB__GET__CATLEVEL])
                ->__to_array();
        }

        if (!$l_catdata) {
            $l_list_id = $this->create($_GET[C__CMDB__GET__OBJECT], "", null, "", "", 1, "");
        } else {
            $l_list_id = $l_catdata["isys_cats_database_links_list__id"];
        }

        $l_bRet = $this->save(
            $l_list_id,
            $_POST["C__CMDB__CATS__DATABASE_LINKS__SCHEMA__HIDDEN"],
            $_POST["C__CMDB__CATS__DATABASE_LINKS__TITLE"],
            $_POST["C__CMDB__CATS__DATABASE_LINKS__TARGET_USER"],
            $_POST["C__CMDB__CATS__DATABASE_LINKS__OWNER"],
            $_POST["C__CMDB__CATS__DATABASE_LINKS__PUBLIC"],
            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
            C__RECORD_STATUS__NORMAL
        );

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_bRet == true ? $l_list_id : -1;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level
     *
     * @return boolean true, if transaction executed successfully, else false
     * @author Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function save($p_id, $p_schema_object, $p_title, $p_target_user, $p_owner, $p_public, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {
        if (!$p_public) {
            $p_public = 0;
        }

        $l_strSql = "UPDATE isys_cats_database_links_list " . "SET " . "isys_cats_database_links_list__isys_connection__id = " .
            $this->convert_sql_id($this->handle_connection($p_id, $p_schema_object)) . ", " . "isys_cats_database_links_list__title = " . $this->convert_sql_text($p_title) .
            ", " . "isys_cats_database_links_list__target_user = " . $this->convert_sql_text($p_target_user) . ", " . "isys_cats_database_links_list__owner = " .
            $this->convert_sql_text($p_owner) . ", " . "isys_cats_database_links_list__public = " . $this->convert_sql_int($p_public) . ", " .
            "isys_cats_database_links_list__description = " . $this->convert_sql_text($p_description) . ", " . "isys_cats_database_links_list__status = " .
            $this->convert_sql_id($p_status) . " " .

            "WHERE isys_cats_database_links_list__id = " . $this->convert_sql_id($p_id);

        if ($this->update($l_strSql) && $this->apply_update()) {

            /**
             * Handle relation
             */
            $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
            $l_data = $this->get_data($p_id)
                ->__to_array();

            $l_relation_dao->handle_relation(
                $p_id,
                "isys_cats_database_links_list",
                defined_or_default('C__RELATION_TYPE__DATABASE_LINK'),
                $l_data["isys_cats_database_links_list__isys_catg_relation_list__id"],
                $p_schema_object,
                $l_data["isys_cats_database_links_list__isys_obj__id"]
            );

            return true;
        } else {
            return false;
        }
    }

    /**
     * Executes the query to create the category entry
     *
     * @param int           $p_object_id
     * @param int|null      $p_schema_object
     * @param string|null   $p_title
     * @param string|null   $p_target_user
     * @param string|null   $p_owner
     * @param int|null      $p_public
     * @param string        $p_description
     * @param int    $p_status
     *
     * @return int the newly created ID or false
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @author Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function create($p_object_id, $p_schema_object = null, $p_title = null, $p_target_user = null, $p_owner = null, $p_public = null, $p_description = '', $p_status = C__RECORD_STATUS__NORMAL)
    {
        if (!$p_public) {
            $p_public = 0;
        }

        $l_dao_connection = new isys_cmdb_dao_connection($this->m_db);

        $l_strSql = "INSERT INTO isys_cats_database_links_list SET " . "isys_cats_database_links_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ", " .
            "isys_cats_database_links_list__isys_connection__id = " . $this->convert_sql_id($l_dao_connection->add_connection($p_schema_object)) . ", " .
            "isys_cats_database_links_list__title = " . $this->convert_sql_text($p_title) . ", " . "isys_cats_database_links_list__target_user = " .
            $this->convert_sql_text($p_target_user) . ", " . "isys_cats_database_links_list__owner = " . $this->convert_sql_text($p_owner) . ", " .
            "isys_cats_database_links_list__public = " . $this->convert_sql_int($p_public) . ", " . "isys_cats_database_links_list__description = " .
            $this->convert_sql_text($p_description) . ", " . "isys_cats_database_links_list__status = " . $this->convert_sql_id($p_status) . ";";

        if ($this->update($l_strSql) && $this->apply_update()) {
            $l_last_id = $this->get_last_insert_id();

            if (!is_null($p_schema_object) && $p_schema_object > 0) {
                /**
                 * Handle relation
                 */
                $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
                $l_relation_dao->handle_relation($l_last_id, "isys_cats_database_links_list", defined_or_default('C__RELATION_TYPE__DATABASE_LINK'), null, $p_schema_object, $p_object_id);
            }

            return $l_last_id;
        } else {
            return false;
        }
    }
}
