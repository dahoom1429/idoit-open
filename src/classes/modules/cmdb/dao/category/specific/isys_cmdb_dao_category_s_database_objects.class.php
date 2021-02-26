<?php

/**
 * i-doit
 *
 * DAO: Specific category for database objects.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_database_objects extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'database_objects';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

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
        $l_sql = "SELECT * FROM isys_cats_database_objects_list " . "INNER JOIN isys_obj ON isys_obj__id = isys_cats_database_objects_list__isys_obj__id " .
            "LEFT JOIN isys_database_objects ON isys_database_objects__id = isys_cats_database_objects_list__isys_database_objects__id " . "WHERE TRUE ";

        $l_sql .= $p_condition;

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_cats_list_id)) {
            $l_sql .= " AND (isys_cats_database_objects_list__id = '{$p_cats_list_id}')";
        }

        if (!empty($p_status)) {
            $l_sql .= " AND (isys_cats_database_objects_list__status = '{$p_status}')";
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'title'           => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_database_objects_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_database_objects_list__title FROM isys_cats_database_objects_list',
                        'isys_cats_database_objects_list',
                        'isys_cats_database_objects_list__id',
                        'isys_cats_database_objects_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_database_objects_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__DATABASE_OBJECTS__TITLE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'database_object' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Tabelle'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_cats_database_objects_list__isys_database_objects__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_database_objects',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_database_objects',
                        'isys_database_objects__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_database_objects__title
                            FROM isys_cats_database_objects_list
                            INNER JOIN isys_database_objects ON isys_database_objects__id = isys_cats_database_objects_list__isys_database_objects__id',
                        'isys_cats_database_objects_list',
                        'isys_cats_database_objects_list__id',
                        'isys_cats_database_objects_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_database_objects_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_database_objects_list',
                            'LEFT',
                            'isys_cats_database_objects_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_database_objects',
                            'LEFT',
                            'isys_cats_database_objects_list__isys_database_objects__id',
                            'isys_database_objects__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__DATABASE_OBJECTS__TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_database_objects'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'description'     => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_database_objects_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_database_objects_list__description FROM isys_cats_database_objects_list',
                        'isys_cats_database_objects_list',
                        'isys_cats_database_objects_list__id',
                        'isys_cats_database_objects_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_database_objects_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__DATABASE_OBJECTS', 'C__CATS__DATABASE_OBJECTS')
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
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                $p_category_data['data_id'] = $this->create_connector('isys_cats_database_objects_list', $p_object_id);
            }
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data:
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    $p_category_data['properties']['database_object'][C__DATA__VALUE],
                    $p_category_data['properties']['title'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    C__RECORD_STATUS__NORMAL
                );
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level
     *
     * @return boolean true, if transaction executed successfully, else false
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save($p_id, $p_type_id, $p_title, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_strSql = "UPDATE isys_cats_database_objects_list " . "SET " . "isys_cats_database_objects_list__isys_database_objects__id = " . $this->convert_sql_id($p_type_id) .
            ", " . "isys_cats_database_objects_list__title = " . $this->convert_sql_text($p_title) . ", " . "isys_cats_database_objects_list__description = " .
            $this->convert_sql_text($p_description) . ", " . "isys_cats_database_objects_list__status = " . $this->convert_sql_id($p_status) . " " .

            "WHERE isys_cats_database_objects_list__id = " . $this->convert_sql_id($p_id);

        if ($this->update($l_strSql)) {
            return $this->apply_update();
        } else {
            return false;
        }
    }

    /**
     * Executes the query to create the category entry.
     *
     * @param int    $p_object_id
     * @param int    $p_type_id
     * @param string $p_title
     * @param string $p_description
     * @param int    $p_status
     *
     * @return bool|int
     * @throws isys_exception_dao
     */
    public function create($p_object_id, $p_type_id, $p_title, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = 'INSERT INTO isys_cats_database_objects_list
            SET isys_cats_database_objects_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ',
            isys_cats_database_objects_list__isys_database_objects__id = ' . $this->convert_sql_id($p_type_id) . ',
            isys_cats_database_objects_list__description = ' . $this->convert_sql_text($p_description) . ',
            isys_cats_database_objects_list__title = ' . $this->convert_sql_text($p_title) . ',
            isys_cats_database_objects_list__status = ' . $this->convert_sql_int($p_status) . ';';

        if ($this->update($l_sql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    public function save_element($p_cat_level, &$p_status, $p_create = false)
    {
        if ($_GET[C__CMDB__GET__CATLEVEL]) {
            $l_catdata = $this->get_data($_GET[C__CMDB__GET__CATLEVEL])
                ->__to_array();
        }

        if (!$l_catdata) {
            $l_list_id = $this->create($_GET[C__CMDB__GET__OBJECT], null, "", "", C__RECORD_STATUS__NORMAL);
        } else {
            $l_list_id = $l_catdata["isys_cats_database_objects_list__id"];
        }

        $l_bRet = $this->save(
            $l_list_id,
            $_POST["C__CMDB__CATS__DATABASE_OBJECTS__TYPE"],
            $_POST["C__CMDB__CATS__DATABASE_OBJECTS__TITLE"],
            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
            C__RECORD_STATUS__NORMAL
        );

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_bRet == true ? $l_list_id : -1;
    }
}
