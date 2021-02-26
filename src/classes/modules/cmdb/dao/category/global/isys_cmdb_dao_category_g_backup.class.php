<?php
use idoit\Component\Property\Type\ObjectBrowserConnectionProperty;

/**
 * i-doit
 *
 * DAO: global category for backups
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_backup extends isys_cmdb_dao_category_global
{

    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'backup';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__BACKUP';

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
     * Field for the object id. This variable is needed for multiedit (for example global category guest systems or it service).
     *
     * @var  string
     */
    protected $m_object_id_field = 'isys_catg_backup_list__isys_obj__id';

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function properties()
    {
        return [
            'title'        => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATD__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_backup_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_backup_list__title FROM isys_catg_backup_list',
                        'isys_catg_backup_list',
                        'isys_catg_backup_list__id',
                        'isys_catg_backup_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_backup_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__BACKUP_TITLE'
                ]
            ]),
            'backup'       => (new ObjectBrowserConnectionProperty(
                'C__CATG__BACKUP__ASSIGNED_OBJECT',
                'LC__CMDB__CATG__BACKUP__IS_BACKUPEP',
                'isys_catg_backup_list__isys_connection__id',
                'isys_catg_backup_list'
            ))->setPropertyDataRelationType(defined_or_default('C__RELATION_TYPE__BACKUP'))
            ->setPropertyDataRelationHandler(
                new isys_callback([
                    'isys_cmdb_dao_category_g_backup',
                    'callback_property_relation_handler'
                ], [
                    'isys_cmdb_dao_category_g_backup',
                    true
                ])
            ),
            'backup_type'  => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__BACKUP__BACKUP_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Backup type'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_backup_list__isys_backup_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_backup_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_backup_type',
                        'isys_backup_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_backup_type__title FROM isys_catg_backup_list
                            INNER JOIN isys_backup_type ON isys_backup_type__id = isys_catg_backup_list__isys_backup_type__id',
                        'isys_catg_backup_list',
                        'isys_catg_backup_list__id',
                        'isys_catg_backup_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_backup_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_backup_list', 'LEFT', 'isys_catg_backup_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_backup_type',
                            'LEFT',
                            'isys_catg_backup_list__isys_backup_type__id',
                            'isys_backup_type__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__BACKUP__TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_backup_type',
                        'p_onChange' => 'idoit.callbackManager.triggerCallback(\'backup__show_path_to_save\', this.value);'
                    ]
                ]
            ]),
            'cycle'        => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__BACKUP__CYCLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Cycle'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_backup_list__isys_backup_cycle__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_backup_cycle',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_backup_cycle',
                        'isys_backup_cycle__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_backup_cycle__title
                            FROM isys_catg_backup_list
                            INNER JOIN isys_backup_cycle ON isys_backup_cycle__id = isys_catg_backup_list__isys_backup_cycle__id',
                        'isys_catg_backup_list',
                        'isys_catg_backup_list__id',
                        'isys_catg_backup_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_backup_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_backup_list', 'LEFT', 'isys_catg_backup_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_backup_cycle',
                            'LEFT',
                            'isys_catg_backup_list__isys_backup_cycle__id',
                            'isys_backup_cycle__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__BACKUP__CYCLE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_backup_cycle'
                    ]
                ]
            ]),
            'path_to_save' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__BACKUP__PATH_TO_SAVE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Path to save',
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_backup_list__path_to_save',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_backup_list__path_to_save FROM isys_catg_backup_list',
                        'isys_catg_backup_list',
                        'isys_catg_backup_list__id',
                        'isys_catg_backup_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_backup_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__BACKUP__PATH_TO_SAVE'
                ]
            ]),
            'description'  => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Categories description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_backup_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_backup_list__description FROM isys_catg_backup_list',
                        'isys_catg_backup_list',
                        'isys_catg_backup_list__id',
                        'isys_catg_backup_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_backup_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__BACKUP', 'C__CATG__BACKUP')
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
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['backup'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            $p_category_data['properties']['backup_type'][C__DATA__VALUE],
                            $p_category_data['properties']['cycle'][C__DATA__VALUE],
                            $p_category_data['properties']['path_to_save'][C__DATA__VALUE]
                        );
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $this->save(
                            $p_category_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['backup'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            $p_category_data['properties']['backup_type'][C__DATA__VALUE],
                            $p_category_data['properties']['cycle'][C__DATA__VALUE],
                            $p_category_data['properties']['path_to_save'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * Save global category backup element.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @return  mixed
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_intErrorCode = -1;
        $l_bRet = null;

        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_catg_backup_list__status"];

        if ($p_create) {
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__BACKUP_TITLE'],
                $_POST['C__CATG__BACKUP__ASSIGNED_OBJECT__HIDDEN'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()],
                $_POST['C__CATG__BACKUP__TYPE'],
                $_POST['C__CATG__BACKUP__CYCLE'],
                $_POST['C__CATG__BACKUP__PATH_TO_SAVE']
            );

            if ($l_id != false) {
                $this->m_strLogbookSQL = $this->get_last_query();
            }

            $p_cat_level = null;

            return $l_id;
        } else {
            // This case can only happen if category is saved via overview category and on new objects
            if ($l_catdata === null) {
                $l_query = 'SELECT isys_catg_backup_list__id, isys_catg_backup_list__status FROM isys_catg_backup_list';
                $l_query .= ' WHERE isys_catg_backup_list__isys_obj__id = ' . $this->convert_sql_id($_GET[C__CMDB__GET__OBJECT]) . ' LIMIT 1;';

                $l_catdata = $this->retrieve($l_query)
                    ->get_row();
            }

            if ($l_catdata['isys_catg_backup_list__id'] != "") {
                $l_bRet = $this->save(
                    $l_catdata['isys_catg_backup_list__id'],
                    C__RECORD_STATUS__NORMAL,
                    $_POST['C__CATG__BACKUP_TITLE'],
                    $_POST['C__CATG__BACKUP__ASSIGNED_OBJECT__HIDDEN'],
                    $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()],
                    $_POST['C__CATG__BACKUP__TYPE'],
                    $_POST['C__CATG__BACKUP__CYCLE'],
                    $_POST['C__CATG__BACKUP__PATH_TO_SAVE']
                );

                $this->m_strLogbookSQL = $this->get_last_query();
            }

            return $l_bRet == true ? null : $l_intErrorCode;
        }
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param  integer   $p_cat_level
     * @param  array|int $p_newRecStatus
     * @param  string    $p_title
     * @param  integer   $p_connectedObjID
     * @param  string    $p_description
     * @param  integer   $p_backup_type
     * @param  integer   $p_backup_cycle
     * @param  string    $p_path_to_save
     *
     * @return boolean
     * @throws Exception
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     */
    public function save($p_cat_level, $p_newRecStatus = C__RECORD_STATUS__NORMAL, $p_title = null, $p_connectedObjID = null, $p_description = '', $p_backup_type = null, $p_backup_cycle = null, $p_path_to_save = null)
    {
        if ($p_backup_type != defined_or_default('C__CMDB__BACKUP_TYPE__FILE')) {
            $p_path_to_save = null;
        }

        $l_strSql = "UPDATE isys_catg_backup_list SET
			isys_catg_backup_list__isys_connection__id = " . $this->convert_sql_id($this->handle_connection($p_cat_level, $p_connectedObjID)) . ",
			isys_catg_backup_list__title = " . $this->convert_sql_text($p_title) . ",
			isys_catg_backup_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_backup_list__isys_backup_type__id = " . $this->convert_sql_id($p_backup_type) . ",
			isys_catg_backup_list__isys_backup_cycle__id = " . $this->convert_sql_id($p_backup_cycle) . ",
			isys_catg_backup_list__path_to_save = " . $this->convert_sql_text($p_path_to_save) . ",
			isys_catg_backup_list__status = " . $this->convert_sql_id($p_newRecStatus) . "
			WHERE isys_catg_backup_list__id = " . $this->convert_sql_id($p_cat_level) . ";";

        if ($this->update($l_strSql) && $this->apply_update()) {
            $l_data = $this->get_data($p_cat_level)
                ->__to_array();

            isys_cmdb_dao_category_g_relation::instance($this->get_database_component())
                ->handle_relation($p_cat_level, "isys_catg_backup_list", defined_or_default('C__RELATION_TYPE__BACKUP'), $l_data["isys_catg_backup_list__isys_catg_relation_list__id"], $p_connectedObjID, $l_data["isys_catg_backup_list__isys_obj__id"]);

            return true;
        }

        return false;
    }

    /**
     * Executes the query to create the category entry referenced by isys_catg_backup__id $p_fk_id
     *
     * @param  integer $p_objID
     * @param  integer $p_newRecStatus
     * @param  string  $p_title
     * @param  integer $p_connectedObjID
     * @param  string  $p_description
     * @param  integer $p_backup_type
     * @param  integer $p_backup_cycle
     * @param  string  $p_path_to_save
     *
     * @return mixed  The newly created ID (integer) or false (boolean).
     * @throws Exception
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus = C__RECORD_STATUS__NORMAL, $p_title = null, $p_connectedObjID = null, $p_description = '', $p_backup_type = null, $p_backup_cycle = null, $p_path_to_save = null)
    {
        $l_connection = new isys_cmdb_dao_connection($this->get_database_component());

        if (empty($p_newRecStatus)) {
            $p_newRecStatus = C__RECORD_STATUS__NORMAL;
        }

        if ($p_backup_type != defined_or_default('C__CMDB__BACKUP_TYPE__FILE')) {
            $p_path_to_save = null;
        }

        $l_strSql = "INSERT INTO isys_catg_backup_list SET 
            isys_catg_backup_list__title = " . $this->convert_sql_text($p_title) . ",
            isys_catg_backup_list__isys_connection__id = " . $this->convert_sql_id($l_connection->add_connection($p_connectedObjID)) . ",
            isys_catg_backup_list__description = " . $this->convert_sql_text($p_description) . ",
            isys_catg_backup_list__status = " . $this->convert_sql_id($p_newRecStatus) . ",
            isys_catg_backup_list__isys_backup_type__id = " . $this->convert_sql_id($p_backup_type) . ",
            isys_catg_backup_list__isys_backup_cycle__id = " . $this->convert_sql_id($p_backup_cycle) . ",
            isys_catg_backup_list__path_to_save = " . $this->convert_sql_text($p_path_to_save) . ",
            isys_catg_backup_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ";";

        if ($this->update($l_strSql) && $this->apply_update()) {
            $l_last_id = $this->get_last_insert_id();

            isys_cmdb_dao_category_g_relation::instance($this->get_database_component())
                ->handle_relation($l_last_id, "isys_catg_backup_list", defined_or_default('C__RELATION_TYPE__BACKUP'), null, $p_connectedObjID, $p_objID);

            return $l_last_id;
        } else {
            return false;
        }
    }
}
