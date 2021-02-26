<?php

/**
 * i-doit
 *
 * CMDB DAO: Global category for manuals.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @since       1.7
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_manual extends isys_cmdb_dao_category_global
{

    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'manual';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Create connector (for multivalue).
     *
     * @param   string  $p_table
     * @param   integer $p_obj_id
     *
     * @return  null
     */
    public function create_connector($p_table, $p_obj_id = null)
    {
        return null;
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
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_manual_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_manual_list__isys_obj__id
			INNER JOIN isys_connection ON isys_catg_manual_list__isys_connection__id = isys_connection__id
			LEFT JOIN isys_cats_file_list ON isys_connection__isys_obj__id = isys_cats_file_list__isys_obj__id
			LEFT JOIN isys_file_version ON isys_cats_file_list__isys_file_version__id = isys_file_version__id
			LEFT JOIN isys_file_physical ON isys_file_version__isys_file_physical__id = isys_file_physical__id
			WHERE TRUE ' . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= ' AND (isys_catg_manual_list__id = ' . $this->convert_sql_id($p_catg_list_id) . ')';
        }

        if ($p_status !== null) {
            $l_sql .= ' AND (isys_catg_manual_list__status = ' . $this->convert_sql_int($p_status) . ')';
        }

        $l_sql .= ' GROUP BY isys_catg_manual_list__id;';

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
            'title'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_manual_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_manual_list__title FROM isys_catg_manual_list',
                        'isys_catg_manual_list',
                        'isys_catg_manual_list__id',
                        'isys_catg_manual_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_manual_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__MANUAL_TITLE'
                ]
            ]),
            'manual'      => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__MANUAL_OBJ_FILE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Manual file'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_manual_list__isys_connection__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_manual_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_manual_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id',
                        'isys_catg_manual_list',
                        'isys_catg_manual_list__id',
                        'isys_catg_manual_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_manual_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_manual_list', 'LEFT', 'isys_catg_manual_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_catg_manual_list__isys_connection__id',
                            'isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id'),
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__MANUAL_OBJ_FILE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPopupType' => 'browser_file'
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
                        'connection'
                    ]
                ]
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_manual_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_manual_list__description FROM isys_catg_manual_list',
                        'isys_catg_manual_list',
                        'isys_catg_manual_list__id',
                        'isys_catg_manual_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_manual_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__MANUAL', 'C__CATG__MANUAL')
                ]
            ])
        ];
    }

    /**
     *
     * @param   array   $p_category_data
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
                            $p_category_data['properties']['manual'][C__DATA__VALUE],
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
                            $p_category_data['properties']['manual'][C__DATA__VALUE],
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
     * Trigger save process of global category manual.
     *
     * @param   integer &$p_cat_level
     * @param   integer &$p_intOldRecStatus
     *
     * @return  mixed
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus)
    {
        $l_intErrorCode = -1;

        $l_id = isys_glob_get_param(C__CMDB__GET__CATLEVEL);

        if (empty($l_id) || $l_id == "-1") {
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__MANUAL_TITLE'],
                $_POST['C__CATG__MANUAL_OBJ_FILE__HIDDEN'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            if ($l_id) {
                $this->m_strLogbookSQL = $this->get_last_query();
                $p_cat_level = null;

                return $l_id;
            }
        } else {
            $l_catdata = $this->get_general_data();
            $p_intOldRecStatus = $l_catdata["isys_catg_manual_list__status"];

            $l_bRet = $this->save(
                $l_id,
                $p_intOldRecStatus,
                $_POST['C__CATG__MANUAL_TITLE'],
                $_POST['C__CATG__MANUAL_OBJ_FILE__HIDDEN'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        return $l_bRet == true ? null : $l_intErrorCode;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_list_id
     * @param   integer $p_newRecStatus
     * @param   string  $p_title
     * @param   integer $p_connected_object_id
     * @param   string  $p_description
     *
     * @return  boolean
     * @throws  isys_exception_dao
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     */
    public function save($p_list_id, $p_newRecStatus, $p_title, $p_connected_object_id, $p_description)
    {
        $l_strSql = "UPDATE isys_catg_manual_list SET
			isys_catg_manual_list__isys_connection__id = " . $this->convert_sql_id($this->handle_connection($p_list_id, $p_connected_object_id)) . ",
			isys_catg_manual_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_manual_list__title  = " . $this->convert_sql_text($p_title) . ",
			isys_catg_manual_list__status = " . $this->convert_sql_id($p_newRecStatus) . "
			WHERE isys_catg_manual_list__id = " . $this->convert_sql_id($p_list_id) . ";";

        if ($this->update($l_strSql) && $this->apply_update()) {
            // Create connection if necessary
            if ($p_connected_object_id > 0) {
                $data = $this->get_data($p_list_id);

                isys_cmdb_dao_category_g_relation::instance($this->get_database_component())
                    ->handle_relation($p_list_id, "isys_catg_manual_list", defined_or_default('C__RELATION_TYPE__MANUAL'), null, $data['isys_catg_manual_list__isys_obj__id'], $p_connected_object_id);
            }

            return true;
        }

        return false;
    }

    /**
     * Executes the query to create the category entry referenced by isys_catg_manual__id $p_fk_id.
     *
     * @param   integer $p_object_id
     * @param   integer $p_newRecStatus
     * @param   string  $p_title
     * @param   integer $p_connected_obj_id
     * @param   string  $p_description
     *
     * @return  mixed  Integer of the newly created ID or boolean false.
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     */
    public function create($p_object_id, $p_newRecStatus, $p_title, $p_connected_obj_id, $p_description)
    {
        $l_connection = new isys_cmdb_dao_connection($this->get_database_component());

        $l_strSql = "INSERT INTO isys_catg_manual_list SET
			isys_catg_manual_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_manual_list__title  = " . $this->convert_sql_text($p_title) . ",
			isys_catg_manual_list__isys_connection__id  = " . $this->convert_sql_id($l_connection->add_connection($p_connected_obj_id)) . ",
			isys_catg_manual_list__status = " . $this->convert_sql_id($p_newRecStatus) . ",
			isys_catg_manual_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ";";

        if ($this->update($l_strSql) && $this->apply_update()) {
            $categoryEntryId = $this->get_last_insert_id();

            // Create connection if necessary
            if ($p_connected_obj_id > 0) {
                isys_cmdb_dao_category_g_relation::instance($this->get_database_component())
                    ->handle_relation($categoryEntryId, "isys_catg_manual_list", defined_or_default('C__RELATION_TYPE__MANUAL'), null, $p_object_id, $p_connected_obj_id);
            }

            return $categoryEntryId;
        } else {
            return false;
        }
    }
}
