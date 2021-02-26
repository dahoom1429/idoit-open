<?php

use idoit\Component\Property\Type\DynamicProperty;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: Global category for contacts
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_contact extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'contact';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__CONTACT';

    /**
     * Category constants for filtering.
     *
     * @var  array
     */
    protected $m_cats_filter = [
        'C__CATS__PERSON',
        'C__CATS__PERSON_GROUP',
        'C__CATS__ORGANIZATION'
    ];

    /**
     *
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     *
     * @var  boolean
     */
    protected $m_has_relation = true;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Flag which defines if the category is only a list with an object browser.
     *
     * @var  boolean
     */
    protected $m_object_browser_category = true;

    /**
     * Property of the object browser
     *
     * @var  string
     */
    protected $m_object_browser_property = 'contact_object';

    /**
     * Field for the object id.
     *
     * @var  string
     */
    protected $m_object_id_field = 'isys_catg_contact_list__isys_obj__id';

    /**
     * All object types which can be assigned as contact.
     *
     * @var  array
     */
    private $m_assignable_object_types = [];

    /**
     * Callback method which returns the relation type because contact assignment can have custom relation types.
     *
     * @param   isys_request $p_request
     *
     * @return  integer
     * @throws  isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function callback_property_relation_type_handler(isys_request $p_request)
    {
        $l_relation_type_id = isys_cmdb_dao_category_g_contact::instance($this->m_db)
            ->get_data_by_id($p_request->get_category_data_id())
            ->get_row_value('isys_contact_tag__isys_relation_type__id');

        return ($l_relation_type_id > 0) ? $l_relation_type_id : defined_or_default('C__RELATION_TYPE__USER');
    }

    /**
     * Callback method which returns the master and slave object for the relation.
     *
     * @param   isys_request $p_request
     * @param   array        $p_array
     *
     * @return  isys_array
     * @throws  isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function callback_property_relation_handler(isys_request $p_request, $p_array = [])
    {
        $l_return = [];
        $l_dao = isys_cmdb_dao_category_g_contact::instance(isys_application::instance()->database);
        $l_data = $l_dao->get_data_by_id($p_request->get_category_data_id())
            ->get_row();

        if (isset($l_data[$l_dao->m_object_id_field])) {
            $l_master = $l_data[$l_dao->m_object_id_field];
            $l_slave = $l_data[$l_dao->m_connected_object_id_field];

            if ($l_data['isys_contact_tag__isys_relation_type__id'] !== null) {
                $l_relation_default = isys_cmdb_dao_category_g_relation::instance(isys_application::instance()->database)
                    ->get_relation_type($l_data['isys_contact_tag__isys_relation_type__id'])
                    ->get_row_value('isys_relation_type__default');

                if ((int)$l_relation_default === C__RELATION_DIRECTION__I_DEPEND_ON) {
                    $l_cache = $l_master;
                    $l_master = $l_slave;
                    $l_slave = $l_cache;
                }
            }

            $l_return[C__RELATION_OBJECT__MASTER] = $l_master;
            $l_return[C__RELATION_OBJECT__SLAVE] = $l_slave;
        }

        return $l_return;
    }

    /**
     * Return Category Data
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
        $l_sql = 'SELECT *, isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address
            FROM isys_catg_contact_list
            INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list__isys_connection__id
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_contact_list__isys_obj__id
            LEFT JOIN isys_contact_tag ON isys_catg_contact_list__isys_contact_tag__id = isys_contact_tag__id
            LEFT JOIN isys_cats_person_list ON isys_cats_person_list__isys_obj__id = isys_connection__isys_obj__id
            LEFT JOIN isys_cats_person_group_list ON isys_cats_person_group_list__isys_obj__id = isys_connection__isys_obj__id
            LEFT JOIN isys_cats_organization_list ON isys_cats_organization_list__isys_obj__id = isys_connection__isys_obj__id
            LEFT JOIN isys_catg_mail_addresses_list ON isys_connection__isys_obj__id = isys_catg_mail_addresses_list__isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
            WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter);

        if ($p_catg_list_id !== null) {
            $l_sql .= " AND (isys_catg_contact_list__id = " . $this->convert_sql_id($p_catg_list_id) . ")";
        }

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_status !== null) {
            $l_sql .= ' AND isys_catg_contact_list__status = ' . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Prepare filter for usage in get_data()
     *
     * @param $p_filter
     *
     * @return string
     */
    protected function prepare_filter($p_filter)
    {
        /**
         * Get default prepared filter which will has following structure:
         *
         * AND ((column = '%filter%') OR (column = '%filter%') OR (column = '%filter%') [...])
         */
        $preparedFilter = parent::prepare_filter($p_filter);

        // Ensure that filter is needed
        if (!empty($preparedFilter)) {
            // Replace last paranthesis to extend default filter
            $preparedFilter[strrpos($preparedFilter, ')')] = ' ';

            /**
             * Additional columns which should
             * be considered in filtering procedure
             */
            $columnsToCheck = [
                // Person related columns
                'isys_cats_person_list__title',
                'isys_cats_person_list__first_name',
                'isys_cats_person_list__last_name',

                // Person group related columns
                'isys_cats_person_group_list__title',

                // Organization related columns
                'isys_cats_organization_list__title',
            ];

            // Create additional filters based on above column list
            $additionalFilters = implode(' OR ', array_map(function ($column) use ($p_filter) {
                return '(' . $column . ' LIKE \'%' . $p_filter . '%\')';
            }, $columnsToCheck));

            // Add column filters to default filter
            $preparedFilter .= ' OR ' . $additionalFilters . ')';
        }

        return $preparedFilter;
    }

    /**
     * Get entry identifier.
     *
     * @param   array $p_entry_data
     *
     * @return  string
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function get_entry_identifier($p_entry_data)
    {
        $l_data = $this->get_data($p_entry_data['isys_catg_contact_list__id'])
            ->get_row();

        if (is_array($l_data)) {
            if (isset($l_data['isys_cats_person_list__id'])) {
                return $l_data['isys_cats_person_list__first_name'] . ' ' . $l_data['isys_cats_person_list__last_name'];
            } elseif ($l_data['isys_cats_person_group_list__id']) {
                return $l_data['isys_cats_person_group_list__title'];
            }
        }

        return parent::get_entry_identifier($p_entry_data); // TODO: Change the autogenerated stub
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
                $l_sql = ' AND (isys_catg_contact_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_catg_contact_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * Method for returning the properties.
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     * @return  array
     */
    protected function properties()
    {
        return [
            'contact'         => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_CONTACT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Contact'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_contact_list__id'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__CONTACT__CONTACT'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_global_contact_export_helper',
                        'exportContactAssignment'
                    ]
                ]
            ]),
            'primary_contact' => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONTACT_PRIMARY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Primary contact object'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_contact_list__isys_connection__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT
                            CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_contact_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list__isys_connection__id AND isys_catg_contact_list__primary_contact = 1
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id',
                        'isys_catg_contact_list',
                        'isys_catg_contact_list__id',
                        'isys_catg_contact_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([' isys_catg_contact_list__primary_contact = 1']),
                        null,
                        '',
                        1
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_contact_list', 'LEFT', 'isys_catg_contact_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_catg_contact_list__isys_connection__id',
                            'isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true
                ]
            ]),
            'contact_object'  => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_CONTACT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Contact object'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_catg_contact_list__isys_connection__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => new isys_callback([
                        'isys_cmdb_dao_category_g_contact',
                        'callback_property_relation_type_handler'
                    ]),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_g_contact',
                        'callback_property_relation_handler'
                    ], ['isys_cmdb_dao_category_g_contact']),
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, " {", isys_obj__id, "}")
                            FROM isys_catg_contact_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id',
                        'isys_catg_contact_list',
                        'isys_catg_contact_list__id',
                        'isys_catg_contact_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_contact_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_contact_list', 'LEFT', 'isys_catg_contact_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_catg_contact_list__isys_connection__id',
                            'isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__CONTACT__CONNECTED_OBJECT',
                    C__PROPERTY__UI__PARAMS => [
                        isys_popup_browser_object_ng::C__MULTISELECTION => false,
                        isys_popup_browser_object_ng::C__CAT_FILTER     => 'C__CATS__PERSON;C__CATS__PERSON_GROUP;C__CATS__ORGANIZATION'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => true,
                    C__PROPERTY__PROVIDES__REPORT     => true,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__SEARCH_INDEX => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connection'
                    ]
                ]
            ]),
            'primary'         => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONTACT_LIST__PRIMARY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Primary'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_contact_list__primary_contact',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_catg_contact_list__primary_contact = \'1\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__YES') . ' WHEN isys_catg_contact_list__primary_contact = \'0\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__NO') . ' END) FROM isys_catg_contact_list',
                        'isys_catg_contact_list',
                        'isys_catg_contact_list__id',
                        'isys_catg_contact_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_contact_list', 'LEFT', 'isys_catg_contact_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__CONTACT__PRIMARY',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => get_smarty_arr_YES_NO()
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => true,
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_yes_or_no'
                    ]
                ]
            ]),
            'role'            => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
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
                        'SELECT isys_contact_tag__title FROM isys_contact_tag',
                        'isys_contact_tag',
                        'isys_contact_tag__id',
                        '',
                        'isys_contact_tag__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_contact_list', 'LEFT', 'isys_catg_contact_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_contact_tag',
                            'LEFT',
                            'isys_catg_contact_list__isys_contact_tag__id',
                            'isys_contact_tag__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__CONTACT_TAG',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_contact_tag'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => true,
                    C__PROPERTY__PROVIDES__SEARCH_INDEX => true
                ]
            ]),
            'contact_list'    => array_replace_recursive(isys_cmdb_dao_category_pattern::virtual(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__NAVIGATION__MAINMENU__TITLE_CONTACT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Contacts'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_contact_list__isys_connection__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE
                              WHEN isys_catg_contact_list__primary_contact > 0 THEN CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\', \' (LC__CATG__CONTACT_PRIMARY)\')
                              ELSE CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\') END)
                            FROM isys_catg_contact_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL),
                        'isys_catg_contact_list',
                        'isys_catg_contact_list__id',
                        'isys_catg_contact_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_contact_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ]
            ]),
            'contact_list_with_roles'    => array_replace_recursive(isys_cmdb_dao_category_pattern::virtual(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONTACT_AND_ROLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Contacts (Role)'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_contact_list__isys_connection__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\', \' (\', isys_contact_tag__title, \')\')
                            FROM isys_catg_contact_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) .'
                            LEFT JOIN isys_contact_tag ON isys_contact_tag.isys_contact_tag__id = isys_catg_contact_list.isys_catg_contact_list__isys_contact_tag__id',
                        'isys_catg_contact_list',
                        'isys_catg_contact_list__id',
                        'isys_catg_contact_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_contact_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ]
            ]),
            'description'     => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_contact_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__CONTACT', 'C__CATG__CONTACT')
                ],
            ])
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed    Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;

        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            if (isset($p_category_data['properties']['role'][C__DATA__VALUE]) && $p_category_data['properties']['role'][C__DATA__VALUE] > 0) {
                // Because of ID-2643 We'll check if the used user role has a relation type. If not: use a default.
                $l_role_data = isys_factory_cmdb_dialog_dao::get_instance('isys_contact_tag', $this->m_db)
                    ->get_data($p_category_data['properties']['role'][C__DATA__VALUE]);

                if ($l_role_data['isys_contact_tag__isys_relation_type__id'] === null) {
                    $l_sql = 'UPDATE isys_contact_tag
						SET isys_contact_tag__isys_relation_type__id = ' . $this->convert_sql_id(defined_or_default('C__RELATION_TYPE__USER')) . '
						WHERE isys_contact_tag__id = ' . $this->convert_sql_id($l_role_data['isys_contact_tag__id']) . ';';

                    $this->update($l_sql) && $this->apply_update();
                }
            }

            $l_value['contact'] = $p_category_data['properties']['contact'][C__DATA__VALUE];

            if (is_array($l_value['contact'])) {
                // Check for value directly inside of contact
                if (!empty($l_value['contact'][C__DATA__VALUE]) && is_numeric($l_value['contact'][C__DATA__VALUE])) {
                    $l_contact = $l_value['contact'][C__DATA__VALUE];
                } else {
                    $l_contact = $l_value['contact'][0][C__DATA__VALUE];
                }
            } else {
                $l_contact = $l_value['contact'];
            }

            if (($l_contact === null || $l_contact === 0) && isset($p_category_data['properties']['contact_object'])) {
                $l_contact = (isset($p_category_data['properties']['contact_object'][C__DATA__VALUE]) ? $p_category_data['properties']['contact_object'][C__DATA__VALUE] : null);
            }

            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    // Create assignment only if object id is set and the contact otherwise we have data corpses
                    if ($p_object_id > 0 && $l_contact > 0) {
                        $p_category_data['data_id'] = $this->create(
                            $p_object_id,
                            $l_contact,
                            $p_category_data['properties']['role'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );

                        $l_indicator = true;
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $this->save(
                            $p_category_data['data_id'],
                            $l_contact,
                            $p_category_data['properties']['role'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );

                        $l_indicator = true;
                    }
                    break;
            }

            // Primary:
            if (is_numeric($p_category_data['properties']['primary'][C__DATA__VALUE]) && $l_contact > 0) {
                $l_indicator = ((int)$p_category_data['properties']['primary'][C__DATA__VALUE]) ? $this->make_primary(
                    $p_object_id,
                    $p_category_data['data_id']
                ) : $this->reset_primary($p_object_id, $p_category_data['data_id']);
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Get the primary contact
     *
     * @param int $p_primType
     * @param int $p_primID
     */
    public function contact_get_primary(&$p_primType, &$p_primID)
    {
        $l_dao_ref = new isys_contact_dao_reference($this->m_db);
        $l_catdata = $this->get_data(null, $this->m_object_id, "AND isys_catg_contact_list__primary_contact = 1");

        if ($l_catdata->num_rows() > 0) {
            $l_row = $l_catdata->get_row();

            if ($l_row["isys_connection__isys_obj__id"] > 0) {
                $p_primID = $l_row["isys_connection__isys_obj__id"];
                $p_primType = $l_row["isys_obj__isys_obj_type__id"];

                $l_contact_info = $l_dao_ref->get_data_item_info($l_row["isys_connection__isys_obj__id"]);

                if (is_object($l_contact_info)) {
                    return $l_contact_info->get_row();
                }
            }
        }

        return false;
    }


    // Removed: save_element() & isys_rs_system

    /**
     * Check if assigned object is assignable as a contact
     *
     * @param $p_obj_id
     *
     * @return bool
     * @author Van Quyen hoang <qhoang@i-doit.org>
     */
    public function is_object_assignable($p_obj_id)
    {
        $l_objtype_id = $this->get_objTypeID($p_obj_id);

        if (count($this->m_assignable_object_types) == 0) {
            foreach ($this->m_cats_filter as $l_constant) {
                if (defined($l_constant)) {
                    $this->m_assignable_object_types = array_merge($this->get_object_types_by_category(constant($l_constant), 's', false), $this->m_assignable_object_types);
                }
            }
        }

        return in_array($l_objtype_id, $this->m_assignable_object_types);
    }

    /**
     * Creates a contact assignment
     *
     * @param int $p_objID
     * @param int $p_connected_obj_id (Connected User, Group or Organisation)
     * @param int $p_role_id
     * @param int $p_status
     *
     * @return boolean
     */
    public function create($p_objID, $p_connected_obj_id, $p_role_id = null, $p_description = null, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_connection = new isys_cmdb_dao_connection($this->m_db);
        $l_connection_id = $l_connection->add_connection($p_connected_obj_id);

        /* Insert category record */
        $l_q = "INSERT INTO isys_catg_contact_list SET 
            isys_catg_contact_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ",
            isys_catg_contact_list__isys_connection__id = " . $this->convert_sql_id($l_connection_id) . ",
            isys_catg_contact_list__isys_contact_tag__id = " . $this->convert_sql_id($p_role_id) . ",
            isys_catg_contact_list__status = " . $this->convert_sql_id($p_status) . ';';

        $this->m_strLogbookSQL .= $l_q;

        $l_res = $this->update($l_q);
        if (!$l_res) {
            return -11;
        }

        $l_id = $this->get_last_insert_id();

        /* Create implicit relation */
        $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());

        if ($p_role_id > 0) {
            $l_contact_tag_data = $this->get_contact_tag_data($p_role_id)
                ->get_row();
        } else {
            $l_contact_tag_data = null;
        }

        if (!empty($l_contact_tag_data) && !empty($l_contact_tag_data['isys_contact_tag__isys_relation_type__id'])) {
            $l_relation_type_arr = $l_relation_dao->get_relation_type($l_contact_tag_data['isys_contact_tag__isys_relation_type__id'])
                ->__to_array();

            $l_relation_type = $l_contact_tag_data['isys_contact_tag__isys_relation_type__id'];
            switch ($l_relation_type_arr['isys_relation_type__default']) {
                case C__RELATION_DIRECTION__DEPENDS_ON_ME:
                    $l_slave = $p_connected_obj_id;
                    $l_master = $p_objID;
                    break;
                case C__RELATION_DIRECTION__I_DEPEND_ON:
                default:
                    $l_slave = $p_objID;
                    $l_master = $p_connected_obj_id;
                    break;
            }

            $l_relation_dao->handle_relation($l_id, "isys_catg_contact_list", $l_relation_type, null, $l_master, $l_slave);
        } else {
            $l_relation_dao->handle_relation($l_id, "isys_catg_contact_list", defined_or_default('C__RELATION_TYPE__USER'), null, $p_objID, $p_connected_obj_id);
        }

        return $l_id;
    }

    /**
     * Saves a contact assignment
     *
     * @param   integer $p_cat_level
     * @param   integer $p_connected_obj_id
     * @param   integer $p_tag
     * @param   string  $p_description
     * @param   integer $p_record_status
     *
     * @return  boolean
     */
    public function save($p_cat_level, $p_connected_obj_id, $p_role_id = null, $p_description = null, $p_record_status = C__RECORD_STATUS__NORMAL)
    {
        // Contact should not be created without an contact object
        if ($p_connected_obj_id > 0) {
            $l_sql = "UPDATE isys_catg_contact_list
				INNER JOIN isys_connection ON isys_catg_contact_list__isys_connection__id = isys_connection__id
				SET
				isys_connection__isys_obj__id = " . $this->convert_sql_id($p_connected_obj_id) . ", " . "isys_catg_contact_list__isys_contact_tag__id = " .
                $this->convert_sql_id($p_role_id) . ", " . "isys_catg_contact_list__description = " . $this->convert_sql_text($p_description) . ", " .
                "isys_catg_contact_list__status = " . $this->convert_sql_id($p_record_status) . "
				WHERE isys_catg_contact_list__id = " . $this->convert_sql_id($p_cat_level);

            $this->update($l_sql);
            if ($this->apply_update()) {
                /* Create implicit relation */
                $l_data = $this->get_data($p_cat_level)
                    ->__to_array();
                $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());
                if ($p_role_id > 0) {
                    $l_contact_tag_data = $this->get_contact_tag_data($p_role_id)
                        ->get_row();
                } else {
                    $l_contact_tag_data = null;
                }
                if (!empty($l_contact_tag_data) && !empty($l_contact_tag_data['isys_contact_tag__isys_relation_type__id'])) {
                    $l_relation_type_arr = $l_relation_dao->get_relation_type($l_contact_tag_data['isys_contact_tag__isys_relation_type__id'])
                        ->__to_array();
                    $l_relation_type = $l_contact_tag_data['isys_contact_tag__isys_relation_type__id'];
                    switch ($l_relation_type_arr['isys_relation_type__default']) {
                        case C__RELATION_DIRECTION__DEPENDS_ON_ME:
                            $l_slave = $p_connected_obj_id;
                            $l_master = $l_data["isys_catg_contact_list__isys_obj__id"];
                            break;
                        case C__RELATION_DIRECTION__I_DEPEND_ON:
                        default:
                            $l_slave = $l_data["isys_catg_contact_list__isys_obj__id"];
                            $l_master = $p_connected_obj_id;
                            break;
                    }
                    $l_relation_dao->handle_relation(
                        $p_cat_level,
                        "isys_catg_contact_list",
                        $l_relation_type,
                        $l_data["isys_catg_contact_list__isys_catg_relation_list__id"],
                        $l_master,
                        $l_slave
                    );
                } else {
                    $l_relation_dao->handle_relation(
                        $p_cat_level,
                        "isys_catg_contact_list",
                        defined_or_default('C__RELATION_TYPE__USER'),
                        $l_data["isys_catg_contact_list__isys_catg_relation_list__id"],
                        $l_data["isys_catg_contact_list__isys_obj__id"],
                        $p_connected_obj_id
                    );
                }

                return true;
            } else {
                return false;
            }
        } else {
            // Remove entry because there is no contact object defined
            return $this->delete($p_cat_level);
        }
    }

    public function delete($p_id = null, $p_obj_id = null)
    {
        $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());

        $l_res = $this->get_data($p_id, $p_obj_id);

        while ($l_data = $l_res->get_row()) {
            if ($l_data["isys_catg_contact_list__isys_catg_relation_list__id"] > 0 && !empty($l_data["isys_catg_contact_list__isys_catg_relation_list__id"])) {
                $l_relation_dao->delete_relation($l_data["isys_catg_contact_list__isys_catg_relation_list__id"]);
            }
        }

        $l_sql = "DELETE FROM isys_catg_contact_list WHERE TRUE";

        if ($p_id) {
            $l_sql .= " AND isys_catg_contact_list__id = " . $this->convert_sql_id($p_id);
        }

        if ($p_obj_id) {
            $l_sql .= " AND isys_catg_contact_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id);
        }

        $this->m_strLogbookSQL .= $l_sql . ';';

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     *
     * @param   string $p_string
     *
     * @return  mixed|string
     * @throws  isys_exception_database
     */
    public function get_tag_id_by_string($p_string)
    {
        $l_res = $this->retrieve('SELECT isys_contact_tag__id FROM isys_contact_tag WHERE isys_contact_tag__title = ' . $this->convert_sql_text($p_string) . ';');

        if (is_countable($l_res) && count($l_res)) {
            return $l_res->get_row_value('isys_contact_tag__id');
        } else {
            return "null";
        }
    }

    /**
     *
     * @param   integer $p_id
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_contact_tag_data($p_id = null)
    {
        $l_sql = 'SELECT * FROM isys_contact_tag WHERE TRUE';

        if (is_numeric($p_id)) {
            $l_sql .= ' AND isys_contact_tag__id = ' . $this->convert_sql_id($p_id);
        } elseif (is_string($p_id)) {
            $l_sql .= ' AND isys_contact_tag__const = ' . $this->convert_sql_text($p_id);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Convert data item to primary.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_cat_level
     *
     * @return  integer
     * @throws  isys_exception_dao
     */
    public function make_primary($p_obj_id, $p_cat_level)
    {
        $this->reset_primary($p_obj_id);

        $l_q = "UPDATE isys_catg_contact_list
			SET isys_catg_contact_list__primary_contact = '1'
			WHERE isys_catg_contact_list__id = " . $this->convert_sql_id($p_cat_level) . ';';

        $this->m_strLogbookSQL .= $l_q;

        return $this->update($l_q) && $this->apply_update($l_q);
    }

    /**
     * @param   integer $p_cat_level
     *
     * @return  boolean
     * @throws  isys_exception_database
     */
    public function is_primary($p_cat_level)
    {
        $l_primary_contact = $this->retrieve('SELECT isys_catg_contact_list__primary_contact FROM isys_catg_contact_list WHERE isys_catg_contact_list__id = ' .
            $this->convert_sql_id($p_cat_level) . ';')
            ->get_row_value('isys_catg_contact_list__primary_contact');

        return ($l_primary_contact != 0);
    }

    /**
     *
     * @param   integer $p_obj_id
     * @param   integer $p_cat_level
     *
     * @return  boolean
     * @throws  isys_exception_dao
     */
    public function reset_primary($p_obj_id, $p_cat_level = null)
    {
        $l_sql = 'UPDATE isys_catg_contact_list SET
			isys_catg_contact_list__primary_contact = 0
			WHERE isys_catg_contact_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);

        if ($p_cat_level !== null && $p_cat_level > 0) {
            $l_sql .= ' AND isys_catg_contact_list__id = ' . $this->convert_sql_id($p_cat_level);
        }

        return $this->update($l_sql . ';') && $this->apply_update();
    }

    /**
     * @param int $p_cat_level
     * @param int $p_new_id
     *
     * @return NULL
     * @throws Exception
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $l_last_id = null;
        $l_unassignable = [];
        $l_set_primary = false;

        if (!is_null($p_objects)) {
            // Select all items from the database-table for deleting them.
            $l_res = $this->get_assigned_contacts($p_object_id);

            if ($l_res->num_rows() == 0) {
                $l_set_primary = true;
            }

            /**
             * @desc Don't delete any contacts because multi assignments should be possible
             * @see  https://i-doit.atlassian.net/browse/ID-521
             * while ($l_row = $l_res->get_row())
             * {
             * //$l_existing[] = $l_row['isys_connection__isys_obj__id'];
             *
             * // Collect only items, which are not to be saved.
             * if (!in_array($l_row['isys_connection__isys_obj__id'], $l_objects))
             * {
             * // Collect items to delete, so we don't have to execute dozens of queries but only one.
             * $this->delete($l_row['isys_catg_contact_list__id'], $_GET[C__CMDB__GET__OBJECT]);
             * } // if
             * } // while
             */

            // Now insert new items.
            foreach ($p_objects as $l_object) {
                $l_assignable = $this->is_object_assignable($l_object);

                // But don't insert any items, that already exist!
                if ($l_assignable) {
                    if ($l_object > 0) {
                        // Create the new items.
                        $l_last_id = $this->create($p_object_id, $l_object);
                        if ($l_set_primary) {
                            $this->make_primary($p_object_id, $l_last_id);
                            $l_set_primary = false;
                        }
                    }
                } else {
                    $l_unassignable[] = $this->get_obj_name_by_id_as_string($l_object);
                }
            }
        }

        return $l_last_id;
    }

    /**
     * @param $p_list_id
     * @param $p_direction
     * @param $p_table
     */
    public function pre_rank($p_list_id, $p_direction, $p_table)
    {
        if ($this->is_primary($p_list_id)) {
            $this->reset_primary($_GET[C__CMDB__GET__OBJECT]);
        }
    }

    /**
     * @param $p_list_id
     * @param $p_direction
     * @param $p_table
     */
    public function post_rank($p_list_id, $p_direction, $p_table)
    {
        $l_primary_element = $this->get_data(null, $_GET[C__CMDB__GET__OBJECT], " AND isys_catg_contact_list__primary_contact = 1", C__RECORD_STATUS__NORMAL)
            ->get_row();

        if (!$l_primary_element) {
            $l_rows = $this->get_data(null, $_GET[C__CMDB__GET__OBJECT], null, null, C__RECORD_STATUS__NORMAL);
            $l_num = $l_rows->num_rows();

            if ($l_num) {
                $l_row = $l_rows->get_row();
                $this->make_primary($_GET[C__CMDB__GET__OBJECT], $l_row["isys_catg_contact_list__id"]);
            }
        }
    }

    /**
     * @param   integer $p_obj_id
     * @param   integer $p_catg_obj_id
     *
     * @return  boolean
     */
    public function check_contacts($p_obj_id, $p_catg_obj_id)
    {
        $l_sql = 'SELECT isys_catg_contact_list__id FROM isys_catg_contact_list
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list__isys_connection__id
			WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_catg_contact_list__isys_obj__id = ' . $this->convert_sql_id($p_catg_obj_id) . ';';

        $res = $this->retrieve($l_sql);
        return is_countable($res) && count($res) > 0;
    }

    /**
     *
     * @param   integer $p_obj_id
     *
     * @return  mixed
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_assigned_contacts_as_id_string($p_obj_id)
    {
        $l_sql = 'SELECT isys_connection__isys_obj__id FROM isys_catg_contact_list
            INNER JOIN isys_connection ON isys_catg_contact_list__isys_connection__id = isys_connection__id
            WHERE isys_catg_contact_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';';

        $l_res = $this->retrieve($l_sql);

        if (is_countable($l_res) && count($l_res)) {
            $l_id_array = [];

            while ($l_row = $l_res->get_row()) {
                $l_id_array[] = $l_row["isys_connection__isys_obj__id"];
            }

            return implode(',', $l_id_array);
        } else {
            return false;
        }
    }

    /**
     * Do nothing otherwise we get an exception.
     *
     * @return  null
     */
    public function save_element()
    {
        return null;
    }

    /**
     * Updates the contact tag
     *
     * @param int $p_contact_id
     * @param int $p_contact_tag
     */
    public function save_contact_tag($p_catg_contact_id, $p_contact_tag)
    {
        isys_component_signalcollection::get_instance()
            ->emit('mod.cmdb.contact.beforeSaveTag', $this, $p_catg_contact_id, $p_contact_tag);
        $l_old_data = $this->get_data($p_catg_contact_id)
            ->__to_array();

        $l_query = "UPDATE isys_catg_contact_list " . "SET isys_catg_contact_list__isys_contact_tag__id = " . $this->convert_sql_id($p_contact_tag) . " " .
            "WHERE isys_catg_contact_list__id = " . $this->convert_sql_id($p_catg_contact_id) . ";";

        if ($this->update($l_query)) {
            $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());

            $l_data = $this->get_data($p_catg_contact_id)
                ->__to_array();

            // In case the relation type is not set
            if ($l_data['isys_contact_tag__isys_relation_type__id'] === null) {
                $this->update_contact_tag($p_catg_contact_id, null, defined_or_default('C__RELATION_TYPE__USER'));
                $l_data['isys_contact_tag__isys_relation_type__id'] = defined_or_default('C__RELATION_TYPE__USER');
            }

            $l_relation_type_arr = $l_relation_dao->get_relation_type($l_data['isys_contact_tag__isys_relation_type__id'])
                ->__to_array();

            $l_relation_type = $l_data['isys_contact_tag__isys_relation_type__id'];
            switch ($l_relation_type_arr['isys_relation_type__default']) {
                case C__RELATION_DIRECTION__DEPENDS_ON_ME:
                    $l_slave = $l_data["isys_connection__isys_obj__id"];
                    $l_master = $l_data["isys_catg_contact_list__isys_obj__id"];
                    break;
                case C__RELATION_DIRECTION__I_DEPEND_ON:
                default:
                    $l_slave = $l_data["isys_catg_contact_list__isys_obj__id"];
                    $l_master = $l_data["isys_connection__isys_obj__id"];
                    break;
            }

            $l_relation_dao->handle_relation(
                $p_catg_contact_id,
                "isys_catg_contact_list",
                $l_relation_type,
                $l_data["isys_catg_contact_list__isys_catg_relation_list__id"],
                $l_master,
                $l_slave
            );

            // Get tags
            $l_tags = isys_factory_cmdb_dialog_dao::get_instance('isys_contact_tag', $this->m_db)
                ->get_data();

            // Build changes array
            $l_changes = [
                'isys_cmdb_dao_category_g_contact::role' => [
                    'from' => $l_tags[$l_old_data['isys_catg_contact_list__isys_contact_tag__id']]['title'],
                    'to'   => $l_tags[$p_contact_tag]['title']
                ]
            ];

            // Create logbook entry
            $l_logbook_dao = new isys_component_dao_logbook($this->m_db);

            $l_logbook_dao->set_entry(
                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                $this->get_last_query(),
                null,
                defined_or_default('C__LOGBOOK__ALERT_LEVEL__0', 1),
                $l_data['isys_obj__id'],
                $l_data['isys_obj__title'],
                $this->get_obj_type_name_by_obj_id($l_data['isys_obj__id']),
                'LC__CMDB__CATG__CONTACT',
                null,
                serialize($l_changes),
                isys_application::instance()->container->get('language')
                    ->get('LC__CATG__CONTACT_HAS_BEEN_UPDATED'),
                null
            );

            return $this->apply_update();
        }

        return false;
    }

    /**
     * This method gets the assigned contacts by an object-id for the contact-browser.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_status
     * @param   boolean $p_primary
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_assigned_contacts($p_obj_id, $p_status = null, $p_primary = false)
    {
        // Prepare SQL statement for retrieving contacts, assigned to a certain object.
        $l_sql = 'SELECT cl.isys_catg_contact_list__id, conn.isys_connection__isys_obj__id, obj.isys_obj__id, obj.isys_obj__title, obj.isys_obj__isys_obj_type__id, obj.isys_obj__sysid
            FROM isys_catg_contact_list AS cl
            LEFT JOIN isys_connection AS conn ON conn.isys_connection__id = cl.isys_catg_contact_list__isys_connection__id
            LEFT JOIN isys_obj AS obj ON obj.isys_obj__id = conn.isys_connection__isys_obj__id
            WHERE cl.isys_catg_contact_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);

        if ($p_status !== null) {
            $l_sql .= ' AND isys_catg_contact_list__status = ' . $this->convert_sql_int($p_status);
        }

        if ($p_primary) {
            $l_sql .= ' AND isys_catg_contact_list__primary_contact = 1';
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for retrieving the contacts, assigned to a certain relation-ID defined in the table "isys_contact_2_isys_obj".
     *
     * @param   integer $p_rel_id
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_assigned_contacts_by_relation_id($p_rel_id)
    {
        $l_sql = 'SELECT isys_obj__id, isys_obj__title FROM isys_contact_2_isys_obj AS c2o
            LEFT JOIN isys_obj AS o ON c2o.isys_contact_2_isys_obj__isys_obj__id = o.isys_obj__id
            WHERE c2o.isys_contact_2_isys_obj__isys_contact__id = ' . ($p_rel_id + 0) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Gets all internal contacts of an object given by its ID.
     *
     * @param   integer $p_objID
     * @param   boolean $p_only_primary
     *
     * @return  isys_component_dao_result
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    public function get_contacts_by_obj_id($p_objID, $p_only_primary = false)
    {
        $l_sql = 'SELECT isys_cats_person_list__id, isys_cats_person_list__first_name, isys_cats_person_list__last_name, isys_connection__isys_obj__id, isys_cats_person_list__isys_obj__id, isys_contact_tag__title
			FROM isys_cats_person_list
			INNER JOIN isys_connection ON isys_connection__isys_obj__id = isys_cats_person_list__isys_obj__id
			INNER JOIN isys_catg_contact_list ON isys_catg_contact_list__isys_connection__id = isys_connection__id
			LEFT JOIN isys_contact_tag ON isys_catg_contact_list__isys_contact_tag__id = isys_contact_tag__id
			WHERE isys_catg_contact_list__isys_obj__id = ' . $this->convert_sql_id($p_objID);

        if ($p_only_primary === true) {
            $l_sql .= ' AND isys_catg_contact_list__primary_contact = 1';
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Retrieves the email address of a person by the data-ID.
     *
     * @param   integer $p_id
     *
     * @return  string
     */
    public function get_email_by_id($p_id)
    {
        $l_sql = 'SELECT isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address FROM isys_cats_person_list
            LEFT JOIN isys_catg_mail_addresses_list ON isys_catg_mail_addresses_list__isys_obj__id = isys_cats_person_list__isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
            WHERE isys_cats_person_list__id = ' . $this->convert_sql_id($p_id) . ';';

        return $this->retrieve($l_sql)
            ->get_row_value('isys_cats_person_list__mail_address');
    }

    /**
     * Retrieve a person by its ID.
     *
     * @param   integer $p_id
     *
     * @return  array
     */
    public function getPersonInternByID($p_id)
    {
        $l_query = 'SELECT *, isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address FROM isys_cats_person_list
	        LEFT JOIN isys_catg_mail_addresses_list ON isys_catg_mail_addresses_list__isys_obj__id = isys_cats_person_list__isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
	        WHERE isys_cats_person_list__id = ' . $this->convert_sql_id($p_id) . ';';

        return $this->retrieve($l_query)
            ->get_row();
    }

    /**
     * Retrieve all persons.
     *
     * @return  isys_component_dao_result
     */
    public function getContacts()
    {
        $l_query = 'SELECT *, isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address
			FROM isys_cats_person_list
			LEFT JOIN isys_catg_mail_addresses_list
			ON isys_catg_mail_addresses_list__isys_obj__id = isys_cats_person_list__isys_obj__id AND isys_catg_mail_addresses_list__primary = 1';

        return $this->retrieve($l_query);
    }

    /**
     * Retrieves all contact objects by tag ID.
     *
     * @param null $p_obj_id
     * @param null $p_tag_id
     * @param null $p_condition
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_contact_objects_by_tag($p_obj_id = null, $p_tag_id = null, $p_condition = null)
    {
        $l_tag_condition = '';

        if (is_array($p_tag_id)) {
            $l_tag_condition = ' AND isys_contact_tag__id IN(' . implode(',', $p_tag_id) . ') ';
        } elseif ($p_tag_id !== null) {
            $l_tag_condition = ' AND isys_contact_tag__id = ' . $this->convert_sql_id($p_tag_id) . ' ';
        }

        if ($p_obj_id !== null) {
            $l_tag_condition .= ' AND isys_catg_contact_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        }

        if ($p_condition !== null) {
            $l_tag_condition .= $p_condition;
        }

        $l_query = 'SELECT isys_obj.*, isys_catg_contact_list.*, isys_contact_tag.* FROM isys_catg_contact_list
            INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list__isys_connection__id
            INNER JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id
            INNER JOIN isys_contact_tag ON isys_contact_tag__id = isys_catg_contact_list__isys_contact_tag__id
            WHERE TRUE ' . $l_tag_condition . ';';

        return $this->retrieve($l_query);
    }

    /**
     * Builds an array with minimal requirements for the sync function.
     *
     * @param   $p_data
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_import_array($p_data)
    {
        if (!empty($p_data['role'])) {
            $l_role = isys_import_handler::check_dialog('isys_contact_tag', $p_data['role']);
        } else {
            $l_role = null;
        }

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'contact' => [
                    'value' => $p_data['contact']
                ],
                'role'    => [
                    'value' => $l_role
                ]
            ]
        ];
    }

    /**
     * Adds a new contact role.
     *
     * @param   integer $p_contact_tag_title
     * @param   integer $p_contact_tag_relation_type
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function add_contact_tag($p_contact_tag_title, $p_contact_tag_relation_type)
    {
        $l_update = 'INSERT INTO isys_contact_tag SET
            isys_contact_tag__title = ' . $this->convert_sql_text($p_contact_tag_title) . ',
			isys_contact_tag__isys_relation_type__id = ' . $this->convert_sql_id($p_contact_tag_relation_type) . ',
			isys_contact_tag__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        if ($this->update($l_update) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    /**
     * Updates an existing contact role.
     *
     * @param   integer $p_contact_tag_id
     * @param   string  $p_contact_tag_title
     * @param   integer $p_contact_tag_relation_type
     *
     * @return  bool
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function update_contact_tag($p_contact_tag_id, $p_contact_tag_title = null, $p_contact_tag_relation_type = null)
    {
        if ($p_contact_tag_id === null || ($p_contact_tag_relation_type === null && $p_contact_tag_relation_type === null)) {
            return false;
        }

        $l_update = 'UPDATE isys_contact_tag SET ';

        if ($p_contact_tag_title !== null) {
            $l_update .= 'isys_contact_tag__title = ' . $this->convert_sql_text($p_contact_tag_title) . ' ';
        }

        if ($p_contact_tag_relation_type !== null) {
            if ($p_contact_tag_title !== null) {
                $l_update .= ',';
            }

            $l_update .= 'isys_contact_tag__isys_relation_type__id = ' . $this->convert_sql_id($p_contact_tag_relation_type) . ' ';
        }

        $l_update .= 'WHERE isys_contact_tag__id = ' . $this->convert_sql_id($p_contact_tag_id);

        return ($this->update($l_update) && $this->apply_update());
    }

    /**
     * Deletes existing contact roles.
     *
     * @param   mixed $p_contact_tag_id
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function delete_contact_tag($p_contact_tag_id)
    {
        if (is_array($p_contact_tag_id)) {
            $l_delete = 'WHERE isys_contact_tag__id IN (' . implode(',', $p_contact_tag_id) . ')';
        } elseif (is_numeric($p_contact_tag_id)) {
            $l_delete = 'WHERE isys_contact_tag__id = ' . $this->convert_sql_id($p_contact_tag_id);
        } else {
            return false;
        }

        return ($this->update('DELETE FROM isys_contact_tag ' . $l_delete) && $this->apply_update());
    }

    /**
     * Gets assigned objects by contact object id or via e-mail
     *
     * @param int    $p_contact_obj_id
     * @param string $p_email
     * @param bool   $p_group_by_obj_id
     *
     * @return bool|isys_component_dao_result
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_assigned_objects_by_contact($p_contact_obj_id = null, $p_email = null)
    {
        if (empty($p_contact_obj_id) && empty($p_email)) {
            return false;
        }

        $l_sql = 'SELECT o1.*, ot.*, isys_contact_tag__title, isys_catg_contact_list__primary_contact
			FROM isys_catg_contact_list
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list__isys_connection__id
			LEFT JOIN isys_contact_tag ON isys_contact_tag__id = isys_catg_contact_list__isys_contact_tag__id
			INNER JOIN isys_obj AS o1 ON isys_catg_contact_list__isys_obj__id = o1.isys_obj__id
			INNER JOIN isys_obj_type AS ot ON o1.isys_obj__isys_obj_type__id = ot.isys_obj_type__id
			INNER JOIN isys_obj AS o2 ON o2.isys_obj__id = isys_connection__isys_obj__id
			WHERE ';
        if ($p_contact_obj_id !== null) {
            $l_sql .= 'o2.isys_obj__id = ' . $this->convert_sql_id($p_contact_obj_id) . ' ';
        } else {
            $l_sql .= 'o2.isys_obj__id = ' . '(SELECT isys_catg_mail_addresses_list__isys_obj__id FROM isys_catg_mail_addresses_list ' .
                'WHERE isys_catg_mail_addresses_list__title = ' . $this->convert_sql_text($p_email) . ') ';
        }

        $l_sql .= ';';

        return $this->retrieve($l_sql);
    }

    public function get_contact_objects_by_tags($p_obj_id, $p_tagArray)
    {
        $l_query = 'SELECT isys_obj.* FROM isys_catg_contact_list ' . 'INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list__isys_connection__id ' .
            'INNER JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id ' .
            'INNER JOIN isys_contact_tag ON isys_contact_tag__id = isys_catg_contact_list__isys_contact_tag__id ' . 'WHERE isys_catg_contact_list__isys_obj__id = ' .
            $this->convert_sql_id($p_obj_id) . ' ' . 'AND isys_contact_tag__id IN( ' . implode(',', $p_tagArray) . ');';

        return $this->retrieve($l_query);
    }

    public function import($p_data, $p_object_id)
    {
        $l_dao_person = new isys_cmdb_dao_category_s_person_master($this->get_database_component());

        if (is_array($p_data)) {
            $l_contacts_res = $this->get_contacts_by_obj_id($p_object_id);
            $l_already_assigned = [];
            while ($l_contacts_row = $l_contacts_res->get_row()) {
                $l_already_assigned[] = $l_contacts_row['isys_connection__isys_obj__id'];
            }

            foreach ($p_data as $l_contacts) {
                $l_login_username = null;
                if (($l_posi = strrpos($l_contacts["contact"], "\\")) !== false) {
                    $l_login_username = substr($l_contacts["contact"], $l_posi + 1, strlen($l_contacts["contact"]));
                } else {
                    $l_login_username = $l_contacts["contact"];
                }

                if ($l_login_username !== null) {
                    // Check if user with username exists
                    $l_res = $l_dao_person->get_person_by_username($l_login_username);
                    if ($l_res->num_rows() > 0) {
                        $l_contact_obj_id = $l_res->get_row_value('isys_obj__id');

                        if (count($l_already_assigned) > 0 && in_array($l_contact_obj_id, $l_already_assigned)) {
                            continue;
                        }

                        $this->create($p_object_id, $l_contact_obj_id, defined_or_default('C__CONTACT_TYPE__USER'));
                        $l_already_assigned[] = $l_contact_obj_id;
                    }
                }
            }

            return true;
        }
    }
}
