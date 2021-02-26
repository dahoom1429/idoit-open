<?php

use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @version     Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_database_assignment extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'database_assignment';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__TREE__DATABASE_ASSIGNMENT';

    /**
     * @var  string
     */
    protected $m_entry_identifier = 'database_assignment';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Main table where properties are stored persistently.
     *
     * @var   string
     * @todo  Breaks with developer guidelines! No standard behaviour!
     */
    protected $m_table = 'isys_cats_database_access_list';

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_id
     * @param   array   $p_targetSchemaID
     * @param   integer $p_connectedObjID
     * @param   integer $p_status
     * @param   string  $p_commentary
     *
     * @return  boolean
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function save($p_id, $p_targetSchemaID, $p_connectedObjID, $p_status = C__RECORD_STATUS__NORMAL, $p_commentary = '')
    {
        $l_dao_access = new isys_cmdb_dao_category_s_database_access($this->m_db);

        if ($l_dao_access->save($p_id, $p_connectedObjID, $p_status)) {
            if ($p_targetSchemaID) {
                $l_sql = "UPDATE isys_cats_database_access_list
				SET isys_cats_database_access_list__isys_obj__id = " . $this->convert_sql_id($p_targetSchemaID) . ",
				isys_cats_database_access_list__description = " . $this->convert_sql_text($p_commentary) . "
				WHERE isys_cats_database_access_list__id = " . $this->convert_sql_id($p_id) . ";";

                return $l_dao_access->update($l_sql) && $this->apply_update();
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Executes the query to create the category entry.
     *
     * @param   integer $p_object_id
     * @param   integer $p_connectedObjID
     * @param   integer $p_status
     * @param   string  $p_commentary
     *
     * @return  mixed  Integer of the newly created ID or boolean false on failure.
     * @throws  isys_exception_cmdb
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     */
    public function create($p_object_id, $p_connectedObjID, $p_status = C__RECORD_STATUS__NORMAL, $p_commentary = '')
    {
        if ($p_object_id > 0) {
            $l_dao_access = new isys_cmdb_dao_category_s_database_access($this->m_db);

            if ($l_last_id = $l_dao_access->create($p_object_id, $p_connectedObjID, $p_status, $p_commentary)) {
                return $l_last_id;
            }
        }

        return false;
    }

    /**
     * Save category.
     *
     * @param   integer $p_cat_level
     * @param   integer & $p_intOldRecStatus
     *
     * @return  integer
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_cats_database_access_list__status"];

        $l_list_id = $l_catdata["isys_cats_database_access_list__id"];

        if (empty($l_list_id)) {
            if ($_POST["C__CATG__DATABASE_ASSIGNMENT__TARGET_SCHEMA__HIDDEN"] > 0) {
                $l_list_id = $this->create(
                    $_POST["C__CATG__DATABASE_ASSIGNMENT__TARGET_SCHEMA__HIDDEN"],
                    $_POST["C__CATG__DATABASE_ASSIGNMENT__RELATION_OBJECT__HIDDEN"],
                    C__RECORD_STATUS__NORMAL,
                    $_POST["C__CMDB__CAT__COMMENTARY_" . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__DATABASE_ASSIGNMENT', 'C__CATG__DATABASE_ASSIGNMENT')]
                );
            }
        } else {
            $this->save(
                $l_list_id,
                $_POST["C__CATG__DATABASE_ASSIGNMENT__TARGET_SCHEMA__HIDDEN"],
                $_POST["C__CATG__DATABASE_ASSIGNMENT__RELATION_OBJECT__HIDDEN"],
                C__RECORD_STATUS__NORMAL,
                $_POST["C__CMDB__CAT__COMMENTARY_" . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__DATABASE_ASSIGNMENT', 'C__CATG__DATABASE_ASSIGNMENT')]
            );
        }

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_list_id;
    }

    /**
     * Checks if a connection to a database schema object exists.
     *
     * @param   integer $p_object
     * @param   integer $p_schema_object
     *
     * @return  boolean
     */
    public function connection_exists($p_object, $p_schema_object)
    {
        return !!count($this->get_data(null, $p_object, ' AND isys_connection__isys_obj__id = ' . (int)$p_schema_object));
    }

    /**
     * Method for counting the category rows.
     *
     * @param  integer $objectId
     *
     * @return integer
     * @throws isys_exception_database
     */
    public function get_count($objectId = null)
    {
        if (empty($objectId)) {
            $objectId = $this->m_object_id;
        }

        $sql = 'SELECT COUNT(*) AS count
			FROM isys_cats_database_access_list
			INNER JOIN isys_obj AS self ON self.isys_obj__id = isys_cats_database_access_list__isys_obj__id
			INNER JOIN isys_connection ON isys_cats_database_access_list__isys_connection__id = isys_connection__id
			LEFT OUTER JOIN isys_obj AS assign ON assign.isys_obj__id = isys_connection__isys_obj__id
			LEFT OUTER JOIN isys_catg_relation_list ON isys_catg_relation_list__isys_obj__id = assign.isys_obj__id
			WHERE TRUE 
			' . $this->get_object_condition($objectId) . '
			AND isys_cats_database_access_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			LIMIT 1';

        return (int)$this->retrieve($sql)
            ->get_row_value('count');

        /*
        $l_sql = "SELECT COUNT(self.isys_obj__id) AS count
            FROM isys_cats_database_access_list
            INNER JOIN isys_obj AS self ON self.isys_obj__id = isys_cats_database_access_list__isys_obj__id
            INNER JOIN isys_connection ON isys_cats_database_access_list__isys_connection__id = isys_connection__id
            LEFT OUTER JOIN isys_obj AS assign ON assign.isys_obj__id = isys_connection__isys_obj__id
            LEFT OUTER JOIN isys_catg_relation_list ON isys_catg_relation_list__isys_obj__id = assign.isys_obj__id
            WHERE isys_cats_database_access_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if (!empty($l_obj_id))
        {
            $l_subquery = "SELECT isys_catg_relation_list__isys_obj__id
                FROM isys_catg_relation_list
                WHERE isys_catg_relation_list__isys_obj__id__slave = " . $this->convert_sql_id($l_obj_id) . "
                AND isys_catg_relation_list__isys_relation_type__id = " . $this->convert_sql_id(C__RELATION_TYPE__SOFTWARE);

            $l_sql .= " AND isys_connection__isys_obj__id IN (" . $l_subquery . ")";
        }

        return (int) $this->retrieve($l_sql)->get_row_value('count');
        */
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
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT isys_cats_database_access_list.*, self.*, assign.isys_obj__title AS assigned_title, assign.isys_obj__id AS assigned_obj_id, isys_catg_relation_list.*
			FROM isys_cats_database_access_list
			INNER JOIN isys_obj AS self ON self.isys_obj__id = isys_cats_database_access_list__isys_obj__id
			INNER JOIN isys_connection ON isys_cats_database_access_list__isys_connection__id = isys_connection__id
			LEFT OUTER JOIN isys_obj AS assign ON assign.isys_obj__id = isys_connection__isys_obj__id
			LEFT OUTER JOIN isys_catg_relation_list ON isys_catg_relation_list__isys_obj__id = assign.isys_obj__id
			WHERE TRUE " . $p_condition . " " . $this->prepare_filter($p_filter) . " ";

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= " AND isys_cats_database_access_list__id = " . $this->convert_sql_id($p_catg_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_cats_database_access_list__status = " . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Creates the condition to the object table.
     *
     * @param   mixed $p_obj_id
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                $l_subquery = 'SELECT isys_catg_relation_list__isys_obj__id
					FROM isys_catg_relation_list
					WHERE (isys_catg_relation_list__isys_obj__id__slave ' . $this->prepare_in_condition($p_obj_id) . ' OR isys_catg_relation_list__isys_obj__id__master ' .
                    $this->prepare_in_condition($p_obj_id) . ')
					AND isys_catg_relation_list__isys_relation_type__id = ' . $this->convert_sql_id(defined_or_default('C__RELATION_TYPE__SOFTWARE'));
            } else {
                $l_subquery = 'SELECT isys_catg_relation_list__isys_obj__id
					FROM isys_catg_relation_list
					WHERE (isys_catg_relation_list__isys_obj__id__slave = ' . $this->convert_sql_id($p_obj_id) . ' OR isys_catg_relation_list__isys_obj__id__master = ' .
                    $this->convert_sql_id($p_obj_id) . ')
					AND isys_catg_relation_list__isys_relation_type__id = ' . $this->convert_sql_id(defined_or_default('C__RELATION_TYPE__SOFTWARE'));
            }

            $l_sql = ' AND isys_connection__isys_obj__id IN (' . $l_subquery . ') ';
        }

        return $l_sql;
    }

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function dynamic_properties()
    {
        return [
            '_database_assignment' => new DynamicProperty(
                'LC__CMDB__CATS__DATABASE_GATEWAY__TARGET_SCHEMA',
                'isys_cats_database_access_list__id',
                'isys_cats_database_access_list',
                [
                    $this,
                    'dynamic_property_callback_database_assignment'
                ]
            ),
            '_runs_on' => new DynamicProperty(
                'LC__CMDB__CATG__DATABASE_ASSIGNMENT__SOFTWARE_RUNS_ON',
                'isys_cats_database_access_list__id',
                'isys_cats_database_access_list',
                [
                    $this,
                    'dynamic_property_callback_runs_on'
                ]
            )
        ];
    }

    /**
     * @param $p_row
     *
     * @return mixed
     * @throws isys_exception_database
     */
    public function dynamic_property_callback_database_assignment($p_row)
    {
        $title = null;

        if (isset($p_row['isys_cats_database_access_list__id'])) {
            $sql = 'SELECT dba.isys_obj__title as title
                              FROM isys_cats_database_access_list
                                INNER JOIN isys_obj AS dba ON dba.isys_obj__id = isys_cats_database_access_list__isys_obj__id
                WHERE isys_cats_database_access_list__id = ' . $this->convert_sql_id($p_row['isys_cats_database_access_list__id']) . ';';

            $title = $this
                ->retrieve($sql)
                ->get_row_value('title');
        }

        return $title ?: isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * @param $p_row
     *
     * @return mixed
     * @throws isys_exception_database
     */
    public function dynamic_property_callback_runs_on($p_row)
    {
        $title = null;

        if (isset($p_row['isys_cats_database_access_list__id'])) {
            $sql = 'SELECT device.isys_obj__title as title
                              FROM isys_cats_database_access_list
                                INNER JOIN isys_connection ON isys_connection__id = isys_cats_database_access_list__isys_connection__id
                                INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__isys_obj__id = isys_connection__isys_obj__id
                                INNER JOIN isys_obj AS device ON isys_catg_relation_list__isys_obj__id__master = device.isys_obj__id
                WHERE isys_cats_database_access_list__id = ' . $this->convert_sql_id($p_row['isys_cats_database_access_list__id']) . ';';

            $title = $this
                ->retrieve($sql)
                ->get_row_value('title');
        }

        return $title ?: isys_tenantsettings::get('gui.empty_value', '-');
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
            'database_assignment' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__DATABASE_GATEWAY__TARGET_SCHEMA',
                    C__PROPERTY__INFO__DESCRIPTION => 'Target schema'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_database_access_list__isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT dba.isys_obj__title
                              FROM isys_cats_database_access_list
                                INNER JOIN isys_obj AS dba ON dba.isys_obj__id = isys_cats_database_access_list__isys_obj__id
                                INNER JOIN isys_connection ON isys_connection__id = isys_cats_database_access_list__isys_connection__id
                                INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__isys_obj__id = isys_connection__isys_obj__id
                                INNER JOIN isys_obj AS app ON isys_catg_relation_list__isys_obj__id__slave = app.isys_obj__id',
                        'isys_obj',
                        '',
                        'app.isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([
                            'isys_catg_relation_list__isys_relation_type__id = (SELECT isys_relation_type__id FROM isys_relation_type WHERE isys_relation_type__const = \'C__RELATION_TYPE__SOFTWARE\')' .
                            ' AND dba.isys_obj__status = ' . C__RECORD_STATUS__NORMAL .
                            ' AND isys_cats_database_access_list__status = ' . C__RECORD_STATUS__NORMAL
                        ]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['app.isys_obj__id'])
                    ),
                    // @todo C__PROPERTY__DATA__JOIN
                    /*C__PROPERTY__DATA__JOIN => idoit\Module\Report\SqlQuery\Structure\JoinSubSelect::factory(
                        'SELECT isys_cats_database_access_list__id AS id, app.isys_obj__id AS objectID, dba.isys_obj__title AS title, dba.isys_obj__id AS reference
                          FROM isys_cats_database_access_list
                            INNER JOIN isys_obj AS dba ON dba.isys_obj__id = isys_cats_database_access_list__isys_obj__id
                            INNER JOIN isys_connection ON isys_connection__id = isys_cats_database_access_list__isys_connection__id
                            INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__isys_obj__id = isys_connection__isys_obj__id
                            INNER JOIN isys_obj AS app ON isys_catg_relation_list__isys_obj__id__slave = app.isys_obj__id AND isys_catg_relation_list__isys_relation_type__id = 1
                        ',
                        'LEFT',
                        [
                            'isys_cats_database_access_list',
                        ],
                        'isys_cats_database_access_list',
                        'isys_cats_database_access_list__isys_obj__id'
                    )*/
                    C__PROPERTY__DATA__INDEX  => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__DATABASE_ASSIGNMENT__TARGET_SCHEMA',
                    C__PROPERTY__UI__PARAMS => [
                        'catFilter' => 'C__CATS__DATABASE_SCHEMA'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__MANDATORY => true
                ]
            ]),
            'runs_on'             => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DATABASE_ASSIGNMENT__SOFTWARE_RUNS_ON',
                    C__PROPERTY__INFO__DESCRIPTION => 'Software runs on'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_relation_list__isys_obj__id__slave',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT ci.isys_obj__title
                              FROM isys_cats_database_access_list
                                INNER JOIN isys_connection ON isys_connection__id = isys_cats_database_access_list__isys_connection__id
                                INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__isys_obj__id = isys_connection__isys_obj__id
                                INNER JOIN isys_obj AS app ON isys_catg_relation_list__isys_obj__id__slave = app.isys_obj__id
                                INNER JOIN isys_obj AS ci ON isys_catg_relation_list__isys_obj__id__master = ci.isys_obj__id',
                        'isys_obj',
                        '',
                        'app.isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([
                            'isys_catg_relation_list__isys_relation_type__id =
                                (SELECT isys_relation_type__id FROM isys_relation_type WHERE isys_relation_type__const = \'C__RELATION_TYPE__SOFTWARE\')' .
                            ' AND isys_cats_database_access_list__status = ' . C__RECORD_STATUS__NORMAL
                        ]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['app.isys_obj__id'])
                    ),
                    // @todo C__PROPERTY__DATA__JOIN
                    /*C__PROPERTY__DATA__JOIN => idoit\Module\Report\SqlQuery\Structure\JoinSubSelect::factory(
                        'SELECT isys_cats_database_access_list__id AS id, app.isys_obj__id AS objectID,
                          ci.isys_obj__title AS title, ci.isys_obj__id AS reference
                          FROM isys_cats_database_access_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_cats_database_access_list__isys_connection__id
                            INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__isys_obj__id = isys_connection__isys_obj__id
                            INNER JOIN isys_obj AS app ON isys_catg_relation_list__isys_obj__id__slave = app.isys_obj__id AND isys_catg_relation_list__isys_relation_type__id = 1
                            INNER JOIN isys_obj AS ci ON isys_catg_relation_list__isys_obj__id__master = ci.isys_obj__id AND isys_catg_relation_list__isys_relation_type__id = 1
                        ',
                        'LEFT',
                        [
                            'isys_cats_database_access_list',
                            'isys_connection',
                            'isys_catg_relation_list',
                            'isys_obj'
                        ],
                        'isys_cats_database_access_list',
                        'isys_obj__id'
                    )*/
                    C__PROPERTY__DATA__INDEX  => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__DATABASE_ASSIGNMENT__RELATION_OBJECT',
                    C__PROPERTY__UI__PARAMS => [
                        isys_popup_browser_object_ng::C__CUSTOM_CONDITIONS => [idoit\Module\Cmdb\Component\Browser\Condition\LocalSoftwareRelationCondition::class],
                        isys_popup_browser_object_ng::C__DISABLE_PRIMARY_CONDITIONS => true,
                        isys_popup_browser_object_ng::C__DISABLE_SECONDARY_CONDITIONS => true,
                        'p_bDisableDetach' => true,
                        'p_bReadonly' => true
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__MANDATORY => true
                ]
            ]),
            'description'         => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_database_access_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__DATABASE_ASSIGNMENT', 'C__CATG__DATABASE_ASSIGNMENT')
                ]
            ])
        ];
    }

    /**
     * Sync method.
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  mixed  Integer with the last inserted ID on success, false on failure.
     * @see     isys_cmdb_dao_category::sync()
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            // Do some assertions for QS.
            if ($p_status == isys_import_handler_cmdb::C__CREATE && $p_object_id > 0) {
                $p_category_data['data_id'] = $this->create($p_object_id, $p_category_data['properties']['database_assignment'][C__DATA__VALUE]);
                $p_status = isys_import_handler_cmdb::C__UPDATE;
            }
            if ($p_status == isys_import_handler_cmdb::C__UPDATE && $p_category_data['data_id'] > 0) {
                $this->save(
                    $p_category_data['data_id'],
                    $p_category_data['properties']['runs_on'][C__DATA__VALUE],
                    $p_category_data['properties']['database_assignment'][C__DATA__VALUE]
                );

                return $p_category_data['data_id'];
            }
        }

        return false;
    }

    /**
     * Validates property data.
     *
     * @param   array $p_data
     * @param   mixed $p_prepend_table_field
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function validate(array $p_data = [], $p_prepend_table_field = false)
    {
        $l_empty_fields = [];

        if (count($p_data) > 1 && empty($p_data['database_assignment'])) {
            $l_empty_fields['database_assignment'] = isys_application::instance()->container->get('language')
                ->get('LC__CMDB__OBJECT_BROWSER__PLEASE_MAKE_A_SELECTION');
        }

        if (count($p_data) > 1 && empty($p_data['runs_on'])) {
            $l_empty_fields['runs_on'] = isys_application::instance()->container->get('language')
                ->get('LC__CMDB__OBJECT_BROWSER__PLEASE_MAKE_A_SELECTION');
        }

        if (count($l_empty_fields)) {
            return $l_empty_fields;
        }

        return parent::validate($p_data);
    }
}
