<?php

/**
 * i-doit
 *
 * DAO: Specific category for persons with assigned contacts.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_person_contact_assign extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'person_contact_assign';

    /**
     * Category's constant.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CATS__PERSON_CONTACT_ASSIGNMENT';

    /**
     * Category's identifier.
     *
     * @var    integer
     * @fixme  No standard behavior!
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATS__PERSON_CONTACT_ASSIGNMENT;

    /**
     * @var string
     */
    protected $m_entry_identifier = 'object';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Category's main table
     *
     * @var  string
     */
    protected $m_table = 'isys_catg_contact_list';

    /**
     * Category's template.
     *
     * @var  string
     */
    protected $m_tpl = 'cats__contact_assign.tpl';

    /**
     * Save specific category contact assign
     *
     * @param $p_cat_level
     * @param $p_intOldRecStatus
     *
     * @return integer
     * @throws isys_exception_cmdb
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_catg_contact_list__status"];

        $l_list_id = $l_catdata["isys_catg_contact_list__id"];

        if (empty($l_list_id)) {
            $l_list_id = $this->create_connector("isys_catg_contact_list", $_POST["C__CONTACT__ORGANISATION_TARGET_OBJECT__HIDDEN"]);
            $l_connection_id = isys_cmdb_dao_connection::instance($this->m_db)
                ->add_connection($_GET[C__CMDB__GET__OBJECT]);

            if ($l_list_id === 0) {
                $l_list_id = $this->get_last_id_from_table('isys_catg_contact_list');
            }

            if ($l_connection_id === 0) {
                $l_connection_id = $this->get_last_id_from_table('isys_connection');
            }
        } else {
            $l_connection_id = $l_catdata["isys_catg_contact_list__isys_connection__id"];
        }

        if ($l_list_id) {
            $l_bRet = $this->save(
                $l_list_id,
                C__RECORD_STATUS__NORMAL,
                $l_connection_id,
                $_POST["C__CONTACT__ORGANISATION_ROLE"],
                $_POST["C__CONTACT__ORGANISATION_TARGET_OBJECT__HIDDEN"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        return $l_bRet == true ? $l_list_id : -1;
    }

    public function save($p_catlevel, $p_status = C__RECORD_STATUS__NORMAL, $p_connection, $p_role, $p_objID, $p_description)
    {
        return isys_cmdb_dao_category_s_person_group_contact_assign::instance(isys_application::instance()->container->get('database'))
            ->save($p_catlevel, $p_status, $p_connection, $p_role, $p_objID, $p_description);
    }

    /**
     * Executes the query to create the category entry.
     *
     * @param   array   $p_objID
     * @param   integer $p_newRecStatus
     * @param   integer $p_description
     *
     * @return  mixed
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus, $p_description)
    {
        return null;
    }

    /**
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     * @throws  isys_exception_database
     */
    public function get_count($p_obj_id = null)
    {
        if ($p_obj_id !== null) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        $l_sql = 'SELECT COUNT(isys_obj__id) AS count FROM isys_catg_contact_list
			INNER JOIN isys_connection ON isys_catg_contact_list__isys_connection__id = isys_connection__id
			INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id
			WHERE TRUE
			AND isys_catg_contact_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if ($l_obj_id > 0) {
            $l_sql .= ' AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($l_obj_id);
        }

        return (int)$this->retrieve($l_sql . ';')
            ->get_row_value('count');
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_cats_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT isys_catg_contact_list.*, o1.*, isys_connection.*
			FROM isys_catg_contact_list
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list__isys_connection__id
			LEFT JOIN isys_contact_tag ON isys_contact_tag__id = isys_catg_contact_list__isys_contact_tag__id
			INNER JOIN isys_obj AS o1 ON isys_catg_contact_list__isys_obj__id = o1.isys_obj__id
			INNER JOIN isys_obj AS o2 ON o2.isys_obj__id = isys_connection__isys_obj__id
			WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter);

        if ($p_cats_list_id !== null) {
            $l_sql .= ' AND isys_catg_contact_list__id = ' . $this->convert_sql_id($p_cats_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= ' AND isys_catg_contact_list__status = ' . $this->convert_sql_int($p_status);
        }

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Creates the condition to the object table.
     *
     * @param   mixed $p_obj_id May be an integer or an array of integers.
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id)) {
            $l_sql = ' AND isys_connection__isys_obj__id ' . (is_array($p_obj_id) ? $this->prepare_in_condition($p_obj_id) : '= ' . $this->convert_sql_id($p_obj_id));
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
            'object'      => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC_UNIVERSAL__OBJECT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Object',
                    C__PROPERTY__INFO__BACKWARD    => true
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_contact_list__isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_contact_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_contact_list__isys_obj__id',
                        'isys_connection',
                        'isys_connection__id',
                        'isys_connection__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_connection__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_connection', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_contact_list',
                            'LEFT',
                            'isys_connection__id',
                            'isys_catg_contact_list__isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_contact_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CONTACT__ORGANISATION_TARGET_OBJECT',
                    C__PROPERTY__UI__PARAMS => [
                        isys_popup_browser_object_ng::C__CAT_FILTER => 'C__CATG__CONTACT',
                    ],
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__LIST   => true
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__MANDATORY => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ]
            ]),
            'object_type' => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__OBJTYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Object type'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_obj__isys_obj_type__id'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => ''
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__EXPORT     => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'obj_type'
                    ]
                ]
            ]),
            'role'        => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CONTACT_ROLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Role'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_contact_list__isys_contact_tag__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_contact_tag',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_contact_tag',
                        'isys_contact_tag__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_contact_tag__title
                            FROM isys_catg_contact_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list__isys_connection__id
                            INNER JOIN isys_contact_tag ON isys_contact_tag__id = isys_catg_contact_list__isys_contact_tag__id',
                        'isys_connection',
                        'isys_connection__id',
                        'isys_connection__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_connection__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_connection', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_contact_list',
                            'LEFT',
                            'isys_connection__id',
                            'isys_catg_contact_list__isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_contact_tag',
                            'LEFT',
                            'isys_catg_contact_list__isys_contact_tag__id',
                            'isys_contact_tag__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CONTACT__ORGANISATION_ROLE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_contact_tag'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_contact_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__PERSON_CONTACT_ASSIGNMENT', 'C__CATS__PERSON_CONTACT_ASSIGNMENT')
                ],
            ])
        ];
    }

    public function rank_records($p_objects, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        global $g_comp_database;

        $l_dao_contact = new isys_cmdb_dao_category_g_contact($g_comp_database);
        $l_dao_relation = new isys_cmdb_dao_category_g_relation($g_comp_database);

        switch ($_POST[C__GET__NAVMODE]) {
            case C__NAVMODE__ARCHIVE:
                $l_status = C__RECORD_STATUS__ARCHIVED;
                break;
            case C__NAVMODE__DELETE:
                $l_status = C__RECORD_STATUS__DELETED;
                break;
            case C__NAVMODE__RECYCLE:

                if (intval(isys_glob_get_param("cRecStatus")) == C__RECORD_STATUS__ARCHIVED) {
                    $l_status = C__RECORD_STATUS__NORMAL;
                } elseif (intval(isys_glob_get_param("cRecStatus")) == C__RECORD_STATUS__DELETED) {
                    $l_status = C__RECORD_STATUS__ARCHIVED;
                }
                break;
            case C__NAVMODE__QUICK_PURGE:
            case C__NAVMODE__PURGE:
                if (!empty($_POST["id"])) {
                    foreach ($_POST["id"] as $l_val) {
                        $l_dao_contact->delete($l_val);
                    }
                    unset($_POST["id"]);
                }

                return true;
                break;

        }

        foreach ($p_objects as $l_catg_id) {
            $l_data = $l_dao_contact->get_data($l_catg_id)
                ->__to_array();

            if ($l_dao_contact->save(
                $l_catg_id,
                $_GET[C__CMDB__GET__OBJECT],
                $l_data["isys_catg_contact_list__isys_contact_tag__id"],
                $l_data["isys_catg_contact_list__description"],
                $l_status
            )) {
                $l_data = $l_dao_contact->get_data($l_catg_id)
                    ->__to_array();
                $l_rel_data = $l_dao_relation->get_data($l_data["isys_catg_contact_list__isys_catg_relation_list__id"])
                    ->__to_array();

                $l_dao_relation->set_object_status($l_rel_data["isys_catg_relation_list__isys_obj__id"], $l_status);
            }
        }

        return true;
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
            $l_dao_contact = new isys_cmdb_dao_category_g_contact($this->get_database_component());
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                if ($p_category_data['data_id'] = $l_dao_contact->create(
                    $p_category_data['properties']['object'][C__DATA__VALUE],
                    $p_object_id,
                    $p_category_data['properties']['role'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    C__RECORD_STATUS__NORMAL
                )) {
                    $l_indicator = true;
                }
            }
            if ($p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data:
                $l_indicator = $l_dao_contact->save(
                    $p_category_data['data_id'],
                    $p_object_id,
                    $p_category_data['properties']['role'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    C__RECORD_STATUS__NORMAL
                );
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }
}
