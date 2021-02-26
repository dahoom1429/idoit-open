<?php

/**
 * i-doit
 *
 * DAO: global category for emergency plans
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_emergency_plan extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'emergency_plan';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CATG_EMERGENCY_PLAN';

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

        $l_sql = "SELECT * FROM isys_catg_emergency_plan_list " . "LEFT JOIN isys_connection ON isys_connection__id = isys_catg_emergency_plan_list__isys_connection__id " .
            "LEFT JOIN isys_obj ON isys_obj__id = isys_catg_emergency_plan_list__isys_obj__id " . "WHERE TRUE ";

        $l_sql .= $p_condition;

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= " AND (isys_catg_emergency_plan_list__id = " . (int)$p_catg_list_id . ") ";
        }

        if ($p_status !== null) {
            $l_sql .= " AND (isys_catg_emergency_plan_list__status = " . (int)$p_status . ") ";
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
                $l_sql = ' AND (isys_catg_emergency_plan_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_catg_emergency_plan_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
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
            'title'            => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATD__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_emergency_plan_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_emergency_plan_list__title FROM isys_catg_emergency_plan_list',
                        'isys_catg_emergency_plan_list',
                        'isys_catg_emergency_plan_list__id',
                        'isys_catg_emergency_plan_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_emergency_plan_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__EMERGENCY_PLAN_TITLE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'emergency_plan'   => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__MAINTENANCE_OBJ_EMERGENCY_PLAN',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned emergency plan'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_emergency_plan_list__isys_connection__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_emergency_plan_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_emergency_plan_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id',
                        'isys_catg_emergency_plan_list',
                        'isys_catg_emergency_plan_list__id',
                        'isys_catg_emergency_plan_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_emergency_plan_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_emergency_plan_list',
                            'LEFT',
                            'isys_catg_emergency_plan_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_catg_emergency_plan_list__isys_connection__id',
                            'isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__EMERGENCY_PLAN_OBJ_EMERGENCY_PLAN',
                    C__PROPERTY__UI__PARAMS => [
                        'catFilter' => 'C__CATS__EMERGENCY_PLAN;C__CATS__FILE'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connection'
                    ]
                ]
            ]),
            'time_needed'      => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__EMERGENCY_PLAN_CALC_TIME_NEEDED',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_emergency_plan_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(ROUND(isys_cats_emergency_plan_list__calc_time_need / isys_unit_of_time__factor), \' \', isys_unit_of_time__title)
                            FROM isys_catg_emergency_plan_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_emergency_plan_list__isys_connection__id
                            INNER JOIN isys_cats_emergency_plan_list ON isys_cats_emergency_plan_list__isys_obj__id = isys_connection__isys_obj__id
                            INNER JOIN isys_unit_of_time ON isys_unit_of_time__id = isys_cats_emergency_plan_list__isys_unit_of_time__id',
                        'isys_catg_emergency_plan_list',
                        'isys_catg_emergency_plan_list__id',
                        'isys_catg_emergency_plan_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_emergency_plan_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_emergency_plan_list',
                            'LEFT',
                            'isys_catg_emergency_plan_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_catg_emergency_plan_list__isys_connection__id',
                            'isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_emergency_plan_list',
                            'LEFT',
                            'isys_connection__isys_obj__id',
                            'isys_cats_emergency_plan_list__isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_unit_of_time',
                            'LEFT',
                            'isys_cats_emergency_plan_list__isys_unit_of_time__id',
                            'isys_unit_of_time__id'
                        )
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'emergency_plan_property_time_needed'
                    ]
                ]
            ]),
            'time_needed_unit' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__EMERGENCY_PLAN_CALC_TIME_NEEDED__UNIT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Time period unit'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_emergency_plan_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_unit_of_time__title
                            FROM isys_catg_emergency_plan_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_emergency_plan_list__isys_connection__id
                            INNER JOIN isys_cats_emergency_plan_list ON isys_cats_emergency_plan_list__isys_obj__id = isys_connection__isys_obj__id
                            INNER JOIN isys_unit_of_time ON isys_unit_of_time__id = isys_cats_emergency_plan_list__isys_unit_of_time__id',
                        'isys_catg_emergency_plan_list',
                        'isys_catg_emergency_plan_list__id',
                        'isys_catg_emergency_plan_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_emergency_plan_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false
                ]
            ]),
            'practice_date'    => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__EMERGENCY_PLAN_PRACTICE_ACTUAL_DATE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_emergency_plan_list__id'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => true,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'emergency_plan_property_practice_date'
                    ]
                ]
            ]),
            'description'      => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_emergency_plan_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__EMERGENCY_PLAN', 'C__CATG__EMERGENCY_PLAN')
                ],
            ])
        ];
    }

    /**
     * Sync method.
     *
     * @param   integer $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  mixed
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
                            $p_category_data['properties']['emergency_plan'][C__DATA__VALUE],
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
                            $p_category_data['properties']['emergency_plan'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * Save global category EMERGENCY_PLAN element.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @return  mixed
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        // ErrorCode
        $l_intErrorCode = -1;

        if (isys_glob_get_param(C__CMDB__GET__CATLEVEL) == 0 && isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW') &&
            isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__SAVE) {
            $p_create = true;
        }

        if ($p_create) {
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__EMERGENCY_PLAN_TITLE'],
                $_POST['C__CATG__EMERGENCY_PLAN_OBJ_EMERGENCY_PLAN__HIDDEN'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();

            if ($l_id) {
                $this->m_strLogbookSQL = $this->get_last_query();
                $p_cat_level = null;

                return $l_id;
            }
        } else {
            $l_catdata = $this->get_general_data();
            $p_intOldRecStatus = $l_catdata["isys_catg_emergency_plan_list__status"];

            $l_bRet = $this->save(
                $l_catdata["isys_catg_emergency_plan_list__id"],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__EMERGENCY_PLAN_TITLE'],
                $_POST['C__CATG__EMERGENCY_PLAN_OBJ_EMERGENCY_PLAN__HIDDEN'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        return $l_bRet == true ? null : $l_intErrorCode;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_newRecStatus
     * @param   string  $p_title
     * @param   integer $p_connectedObjID
     * @param   string  $p_description
     *
     * @return  boolean
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_title, $p_connectedObjID, $p_description)
    {
        $l_strSql = "UPDATE isys_catg_emergency_plan_list SET " . "isys_catg_emergency_plan_list__isys_connection__id = " .
            $this->convert_sql_id($this->handle_connection($p_cat_level, $p_connectedObjID)) . ", " . "isys_catg_emergency_plan_list__title = " .
            $this->convert_sql_text($p_title) . ", " . "isys_catg_emergency_plan_list__description	 = " . $this->convert_sql_text($p_description) . ", " .
            "isys_catg_emergency_plan_list__status = " . $this->convert_sql_id($p_newRecStatus) . " " . "WHERE isys_catg_emergency_plan_list__id = " . $p_cat_level;

        if ($this->update($l_strSql) && $this->apply_update()) {
            // Create connection if necessary
            if ($p_connectedObjID > 0) {
                $data = $this->get_data($p_cat_level);

                isys_cmdb_dao_category_g_relation::instance($this->get_database_component())
                    ->handle_relation($p_cat_level, "isys_catg_emergency_plan_list", defined_or_default('C__RELATION_TYPE__EMERGENCY_PLAN'), null, $data['isys_catg_emergency_plan_list__isys_obj__id'], $p_connectedObjID);
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Executes the query to create the category entry referenced by isys_catg_emergency_plan__id $p_fk_id.
     *
     * @param   integer $p_objID
     * @param   integer $p_newRecStatus
     * @param   string  $p_title
     * @param   integer $p_connectedObjID
     * @param   string  $p_description
     *
     * @return  mixed  Integer of the newly created ID or boolean false.
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus, $p_title, $p_connectedObjID, $p_description)
    {
        $l_connection_dao = new isys_cmdb_dao_connection($this->m_db);

        $l_strSql = "INSERT INTO isys_catg_emergency_plan_list SET " . "isys_catg_emergency_plan_list__title = " . $this->convert_sql_text($p_title) . ", " .
            "isys_catg_emergency_plan_list__isys_obj__id  = " . $this->convert_sql_id($p_objID) . ", " . "isys_catg_emergency_plan_list__description	 = " .
            $this->convert_sql_text($p_description) . ", " . "isys_catg_emergency_plan_list__status = " . $this->convert_sql_id($p_newRecStatus) . ", " .
            "isys_catg_emergency_plan_list__isys_connection__id = " . $this->convert_sql_id($l_connection_dao->add_connection($p_connectedObjID));

        if ($this->update($l_strSql) && $this->apply_update()) {
            $categoryEntryId = $this->get_last_insert_id();

            // Create connection if necessary
            if ($p_connectedObjID > 0) {
                isys_cmdb_dao_category_g_relation::instance($this->get_database_component())
                    ->handle_relation($categoryEntryId, "isys_catg_emergency_plan_list", defined_or_default('C__RELATION_TYPE__EMERGENCY_PLAN'), null, $p_objID, $p_connectedObjID);
            }

            return $categoryEntryId;
        } else {
            return false;
        }
    }
}
