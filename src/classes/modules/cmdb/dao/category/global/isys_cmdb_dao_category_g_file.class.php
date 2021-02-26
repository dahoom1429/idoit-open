<?php

/**
 * i-doit
 *
 * DAO: global category for files.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_file extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'file';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__FILE';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * Name of property which should be used as identifier
     *
     * @var string
     */
    protected $m_entry_identifier = 'file';

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
     * Object ID field
     *
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_file_list__isys_obj__id';

    /**
     * @param string $p_table
     * @param null   $p_obj_id
     *
     * @return null
     */
    public function create_connector($p_table, $p_obj_id = null)
    {
        return null;
    }

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
        $l_sql = "SELECT * FROM isys_catg_file_list
			LEFT JOIN isys_connection ON isys_connection__id = isys_catg_file_list__isys_connection__id
			LEFT JOIN isys_cats_file_list ON isys_cats_file_list__isys_obj__id = isys_connection__isys_obj__id
			LEFT JOIN isys_file_version ON isys_file_version__id = isys_cats_file_list__isys_file_version__id
			LEFT JOIN isys_obj ON isys_obj__id = isys_catg_file_list__isys_obj__id
			WHERE TRUE " . $p_condition . " " . $this->prepare_filter($p_filter) . " ";

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= " AND (isys_catg_file_list__id = " . $this->convert_sql_id($p_catg_list_id) . ") ";
        }

        if ($p_status !== null) {
            $l_sql .= " AND (isys_catg_file_list__status = " . $this->convert_sql_int($p_status) . ") ";
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Creates the condition to the object table.
     *
     * @param   mixed $p_obj_id May be an integer or an array of integers.
     *
     * @return  string
     * @author   Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                $l_sql = ' AND (isys_catg_file_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_catg_file_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
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
            'file'        => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IMAGE_OBJ_FILE',
                    C__PROPERTY__INFO__DESCRIPTION => 'File'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_catg_file_list__isys_connection__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__FILE'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_g_file',
                        'callback_property_relation_handler'
                    ], ['isys_cmdb_dao_category_g_file']),
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\' )
                            FROM isys_catg_file_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_file_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id',
                        'isys_catg_file_list',
                        'isys_catg_file_list__id',
                        'isys_catg_file_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_file_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_file_list', 'LEFT', 'isys_catg_file_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_catg_file_list__isys_connection__id',
                            'isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__FILE_OBJ_FILE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPopupType' => 'browser_file',
                        'name'           => 'C__CATG__FILE_OBJ_FILE'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connection'
                    ]
                ]
            ]),
            'revision'    => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC_UNIVERSAL__REVISION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Revision'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_file_version__revision',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_file_version__revision
                            FROM isys_catg_file_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_file_list__isys_connection__id
                            INNER JOIN isys_cats_file_list ON isys_cats_file_list__isys_obj__id = isys_connection__isys_obj__id
                            INNER JOIN isys_file_version ON isys_file_version__id = isys_cats_file_list__isys_file_version__id',
                        'isys_catg_file_list',
                        'isys_catg_file_list__id',
                        'isys_catg_file_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_file_list__isys_obj__id'])
                    ),
                    /*C__PROPERTY__DATA__JOIN => idoit\Module\Report\SqlQuery\Structure\JoinSubSelect::factory(
                        'SELECT isys_catg_file_list__id AS id, isys_catg_file_list__isys_obj__id AS objectID,
                         isys_file_version__revision AS title, isys_file_version__id AS reference
                        FROM isys_catg_file_list
                        INNER JOIN isys_connection ON isys_connection__id = isys_catg_file_list__isys_connection__id
                        INNER JOIN isys_cats_file_list ON isys_cats_file_list__isys_obj__id = isys_connection__isys_obj__id
                        INNER JOIN isys_file_version ON isys_file_version__id = isys_cats_file_list__isys_file_version__id
                        ',
                        'LEFT',
                        [
                            'isys_catg_file_list',
                            'isys_connection'
                        ],
                        'isys_catg_file_list__id',
                        'isys_catg_file_list__isys_obj__id'
                    )*/
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__REPORT     => true,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__EXPORT     => true,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true
                ],
            ]),
            'link'        => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'HTTP-Link (extern)',
                    C__PROPERTY__INFO__DESCRIPTION => 'external File-Link'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_file_list__link',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_file_list__link FROM isys_catg_file_list',
                        'isys_catg_file_list',
                        'isys_catg_file_list__id',
                        'isys_catg_file_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_file_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__FILE_LINK'
                ]
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_file_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_file_list__description FROM isys_catg_file_list',
                        'isys_catg_file_list',
                        'isys_catg_file_list__id',
                        'isys_catg_file_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_file_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__FILE', 'C__CATG__FILE')
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
                            $p_category_data['properties']['link'][C__DATA__VALUE],
                            $p_category_data['properties']['file'][C__DATA__VALUE],
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
                            $p_category_data['properties']['link'][C__DATA__VALUE],
                            $p_category_data['properties']['file'][C__DATA__VALUE],
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
     * Save global category file element.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     *
     * @return  mixed
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus)
    {
        $l_intErrorCode = -1;
        $l_bRet = null;
        $l_bNewItem = false;

        if (isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW') && isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__SAVE) {
            $l_bNewItem = true;
        }

        if (!isset($_GET[C__CMDB__GET__CATLEVEL]) || ($_GET[C__CMDB__GET__CATLEVEL] <= 0 || !$_GET[C__CMDB__GET__CATLEVEL])) {
            $l_bNewItem = true;
        }

        if ($l_bNewItem) {
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__FILE_TITLE'],
                $_POST['C__CATG__FILE_LINK'],
                $_POST['C__CATG__FILE_OBJ_FILE__HIDDEN'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();

            if ($l_id) {
                $p_cat_level = null;

                return $l_id;
            }
        } else {
            $l_catdata = $this->get_general_data();

            $p_intOldRecStatus = $l_catdata["isys_catg_file_list__status"];

            $l_bRet = $this->save(
                $l_catdata['isys_catg_file_list__id'],
                $p_intOldRecStatus,
                $_POST['C__CATG__FILE_TITLE'],
                $_POST['C__CATG__FILE_LINK'],
                $_POST['C__CATG__FILE_OBJ_FILE__HIDDEN'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        return $l_bRet == true ? null : $l_intErrorCode;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param  int    $p_cat_level
     * @param  int    $p_newRecStatus
     * @param  string $p_title
     * @param  string $p_link
     * @param  int    $p_connectedObjID
     * @param  string $p_description
     *
     * @return bool
     */
    public function save($p_cat_level, $p_newRecStatus, $p_title, $p_link, $p_connectedObjID, $p_description)
    {
        $l_strSql = 'UPDATE isys_catg_file_list SET
			isys_catg_file_list__isys_connection__id = ' . $this->convert_sql_id($this->handle_connection($p_cat_level, $p_connectedObjID)) . ',
			isys_catg_file_list__link	= ' . $this->convert_sql_text($p_link) . ',
			isys_catg_file_list__description = ' . $this->convert_sql_text($p_description) . ',
			isys_catg_file_list__status = ' . $this->convert_sql_id($p_newRecStatus) . '
			WHERE isys_catg_file_list__id = ' . $this->convert_sql_id($p_cat_level);

        if ($this->update($l_strSql)) {
            $l_data = $this->get_data($p_cat_level)->get_row();

            isys_cmdb_dao_category_g_relation::instance($this->get_database_component())
                ->handle_relation(
                    $p_cat_level,
                    'isys_catg_file_list',
                    defined_or_default('C__RELATION_TYPE__FILE'),
                    $l_data['isys_catg_file_list__isys_catg_relation_list__id'],
                    $l_data['isys_catg_file_list__isys_obj__id'],
                    $p_connectedObjID
                );

            return $this->apply_update();
        }

        return false;
    }

    /**
     * Executes the query to create the category entry referenced by isys_catg_file__id $p_fk_id.
     *
     * @param   int    $p_objID
     * @param   int    $p_newRecStatus
     * @param   string $p_title
     * @param   string $p_link
     * @param   int    $p_connectedObjID
     * @param   string $p_description
     *
     * @return  int|bool
     */
    public function create($p_objID, $p_newRecStatus, $p_title, $p_link, $p_connectedObjID, $p_description)
    {
        $l_connection = new isys_cmdb_dao_connection($this->get_database_component());

        $l_strSql = 'INSERT INTO isys_catg_file_list
			SET isys_catg_file_list__link	= ' . $this->convert_sql_text($p_link) . ',
			isys_catg_file_list__isys_obj__id  = ' . $this->convert_sql_id($p_objID) . ',
			isys_catg_file_list__description = ' . $this->convert_sql_text($p_description) . ',
			isys_catg_file_list__status = ' . $this->convert_sql_id($p_newRecStatus) . ',
			isys_catg_file_list__isys_connection__id = ' . $this->convert_sql_id($l_connection->add_connection($p_connectedObjID));

        if ($this->update($l_strSql) && $this->apply_update()) {
            $l_last_id = $this->get_last_insert_id();
            if ($p_connectedObjID > 0) {
                isys_cmdb_dao_category_g_relation::instance($this->get_database_component())
                    ->handle_relation(
                        $l_last_id,
                        'isys_catg_file_list',
                        defined_or_default('C__RELATION_TYPE__FILE'),
                        null,
                        $p_objID,
                        $p_connectedObjID
                    );
            }

            return $l_last_id;
        }

        return false;
    }
}
