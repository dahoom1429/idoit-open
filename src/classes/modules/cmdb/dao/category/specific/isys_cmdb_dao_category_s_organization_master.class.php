<?php
use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: specific category for master organizations
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_organization_master extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'organization_master';

    /**
     * Category's constant.
     *
     * @var    string
     */
    protected $m_category_const = 'C__CATS__ORGANIZATION_MASTER_DATA';

    /**
     * Category's identifier.
     *
     * @var    integer
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATS__ORGANIZATION_MASTER_DATA;

    /**
     * @var  string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * @var  boolean
     */
    protected $m_has_relation = true;

    /**
     * Field for the object ID.
     *
     * @var  string
     */
    protected $m_object_id_field = 'isys_cats_organization_list__isys_obj__id';

    /**
     * Main table where properties are stored persistently.
     *
     * @var  string
     */
    protected $m_table = 'isys_cats_organization_list';

    /**
     * Return Category Data.
     *
     * @param   mixed   $p_cats_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT * FROM isys_cats_organization_list
            INNER JOIN isys_obj ON isys_cats_organization_list__isys_obj__id = isys_obj__id
            LEFT JOIN isys_connection ON isys_cats_organization_list__isys_connection__id = isys_connection__id
            WHERE TRUE " . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_cats_list_id !== null) {
            $l_sql .= " AND isys_cats_organization_list__id = " . $this->convert_sql_id($p_cats_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_cats_organization_list__status = " . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for retrieving the dynamic properties of this dao.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_properties()
    {
        return [
            '_headquarter' => new DynamicProperty(
                'LC__CONTACT__ORGANISATION_ASSIGNMENT',
                'isys_cats_organization_list__id',
                'isys_cats_organization_list',
                [
                    $this,
                    'dynamic_property_callback_headquarter'
                ]
            ),
        ];
    }

    /**
     * @param $p_row
     *
     * @return isys_component_dao_result|mixed
     * @throws isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_headquarter($p_row)
    {
        global $g_comp_database;

        if (!empty($p_row['isys_cats_organization_list__id'])) {
            $l_dao = isys_cmdb_dao_category_s_organization_master::instance($g_comp_database);
            $l_title = $l_dao->retrieve('SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id , \'}\') AS val FROM isys_cats_organization_list
              INNER JOIN isys_connection ON isys_connection__id = isys_cats_organization_list__isys_connection__id
              INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id
              WHERE isys_cats_organization_list__id = ' . $l_dao->convert_sql_id($p_row['isys_cats_organization_list__id']))
                ->get_row_value('val');
            if ($l_title) {
                return $l_title;
            }
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
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
                C__PROPERTY__INFO  => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA  => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_organization_list__title'
                ],
                C__PROPERTY__UI    => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__ORGANISATION_TITLE'
                ],
                C__PROPERTY__CHECK => [
                    C__PROPERTY__CHECK__MANDATORY => true
                ]
            ]),
            'telephone'   => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__ORGANISATION_PHONE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Telephone'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_organization_list__telephone'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__ORGANISATION_PHONE'
                ]
            ]),
            'fax'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__ORGANISATION_FAX',
                    C__PROPERTY__INFO__DESCRIPTION => 'Fax'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_organization_list__fax'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__ORGANISATION_FAX'
                ]
            ]),
            'website'     => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__ORGANISATION_WEBSITE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Website'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_organization_list__website'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CONTACT__ORGANISATION_WEBSITE',
                    C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__LINK,
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTarget' => '_blank',
                    ],
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]),
            'headquarter' => (new DialogProperty(
                'C__CONTACT__ORGANISATION_ASSIGNMENT',
                'LC__CONTACT__ORGANISATION_ASSIGNMENT',
                'isys_cats_organization_list__isys_connection__id',
                'isys_cats_organization_list',
                'isys_connection',
                true,
                [
                    'isys_export_helper',
                    'connection'
                ]
            ))->mergePropertyData(
                [
                    Property::C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_cats_organization_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_cats_organization_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id',
                        'isys_cats_organization_list',
                        'isys_cats_organization_list__id',
                        'isys_cats_organization_list__isys_obj__id'
                    ),
                    Property::C__PROPERTY__DATA__JOIN => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_organization_list',
                            'LEFT',
                            'isys_cats_organization_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_cats_organization_list__isys_connection__id',
                            'isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id')
                    ],
                    Property::C__PROPERTY__DATA__RELATION_TYPE => defined_or_default('C__RELATION_TYPE__ORGANIZATION_HEADQUARTER'),
                    Property::C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback(
                        [
                            'isys_cmdb_dao_category_s_organization_master',
                            'callback_property_relation_handler'
                        ],
                        [
                            'isys_cmdb_dao_category_s_organization_master',
                            true
                        ]
                    )
                ]
            )->setPropertyUiOffset(
                Property::C__PROPERTY__UI__PARAMS,
                [
                    'p_arData' => new isys_callback([
                        'isys_cmdb_dao_category_s_organization_master',
                        'callback_property_headquarter_selection'
                    ]),
                    'chosen'   => true
                ]
            )->setPropertyProvidesOffset(
                Property::C__PROPERTY__PROVIDES__REPORT,
                false
            ),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_organization_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__ORGANIZATION', 'C__CATS__ORGANIZATION')
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
                $p_category_data['data_id'] = $this->create_connector('isys_cats_organization_list', $p_object_id);
            }
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                /*
                 * @see  ID-5546  Overwrite "properties->title" if "properties->value" is set.
                 * This is necessary during duplication/import when the contact assignment category is beeing imported,
                 * because in this case "title" will be set to "LC__CMDB__CATG__GLOBAL_CONTACT" and "value" holds the real organization name.
                 */
                if (isset($p_category_data['properties']['value'][C__DATA__VALUE]) && !empty($p_category_data['properties']['value'][C__DATA__VALUE])) {
                    /*
                     * @see  ID-7171  When creating objects out of templates we'll receive an array here - in this case we empty the "title".
                     * If the title is empty it will be filled a few lines further down.
                     */
                    if (is_array($p_category_data['properties']['value'][C__DATA__VALUE])) {
                        $p_category_data['properties']['title'][C__DATA__VALUE] = '';
                    } else {
                        $p_category_data['properties']['title'][C__DATA__VALUE] = $p_category_data['properties']['value'][C__DATA__VALUE];
                    }
                }

                // @see  ID-5519  Always add the title or else we end up with a unnamed object.
                if (!isset($p_category_data['properties']['title'][C__DATA__VALUE]) || empty($p_category_data['properties']['title'][C__DATA__VALUE])) {
                    $p_category_data['properties']['title'][C__DATA__VALUE] = $this->get_obj_name_by_id_as_string($p_object_id);
                }

                // Save category data:
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    C__RECORD_STATUS__NORMAL,
                    $p_category_data['properties']['title'][C__DATA__VALUE],
                    $p_category_data['properties']['telephone'][C__DATA__VALUE],
                    $p_category_data['properties']['fax'][C__DATA__VALUE],
                    $p_category_data['properties']['website'][C__DATA__VALUE],
                    $p_category_data['properties']['headquarter'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE]
                );
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Dynamic property handling for getting the formatted website attribute.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_property_callback_website($p_row)
    {
        if (empty($p_row['isys_cats_organization_list__website'])) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        return isys_factory::get_instance('isys_smarty_plugin_f_link')
            ->navigation_view(isys_application::instance()->template, [
                'name'              => 'dynamic-property-organization-website',
                'p_strValue'        => $p_row['isys_cats_organization_list__website'],
                'p_strTarget'       => '_blank',
                'p_bInfoIconSpacer' => 0
            ]);
    }

    public function callback_property_headquarter_selection(isys_request $p_request)
    {
        global $g_comp_database;
        /**
         * @var $l_dao isys_cmdb_dao_category_s_organization
         */
        $l_dao = isys_cmdb_dao_category_s_organization::instance($g_comp_database);
        $l_obj_id = $p_request->get_object_id();

        $l_obj_types = $l_dao->get_object_types_by_category(defined_or_default('C__CATS__ORGANIZATION_MASTER_DATA'), 's', false);

        $l_condition = '';
        if (is_countable($l_obj_types) && count($l_obj_types) > 0) {
            $l_condition = ' isys_obj__isys_obj_type__id IN (' . implode(',', $l_obj_types) . ') ';
        }

        if ($l_obj_id !== null) {
            if ($l_condition != '') {
                $l_condition .= 'AND ';
            }
            $l_condition .= ' isys_obj__id = ' . $l_dao->convert_sql_id($l_obj_id);
        }

        if ($l_condition != '') {
            $l_condition = 'WHERE ' . $l_condition;
        }

        $l_sql = 'SELECT isys_obj__id, isys_obj__title FROM isys_obj ' . $l_condition;

        $l_res = $l_dao->retrieve($l_sql);
        $l_arr = [];

        if ($l_res->num_rows() > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_arr[$l_row['isys_obj__id']] = $l_row['isys_obj__title'];
            }
        }

        return $l_arr;
    }

    /**
     * @param            $p_title
     * @param bool|false $p_create
     *
     * @return int
     */
    public function get_object_id_by_title($p_title, $p_create = false)
    {
        $l_res = $this->get_objtype_by_cats_id(defined_or_default('C__CATS__ORGANIZATION'));
        $l_arr = [];
        while ($l_row = $l_res->get_row()) {
            $l_arr[] = $l_row['isys_obj_type__id'];
        }
        $l_obj_id = $this->get_obj_id_by_title($p_title, $l_arr);

        if (!$l_obj_id) {
            if ($p_create) {
                $l_obj_id = $this->insert_new_obj(defined_or_default('C__OBJTYPE__ORGANIZATION'), false, $p_title, null, C__RECORD_STATUS__NORMAL);

                $this->sync([
                    'properties' => [
                        'title' => [
                            C__DATA__VALUE => $p_title
                        ]
                    ]
                ], $l_obj_id, isys_import_handler_cmdb::C__CREATE);
            }
        } else {
            /* Workaround for organizations without isys_cats_organization_list entry */
            if (!$this->get_data_by_object($l_obj_id)
                ->num_rows()) {
                $this->sync([
                    'properties' => [
                        'title' => [
                            C__DATA__VALUE => $p_title
                        ]
                    ]
                ], $l_obj_id, isys_import_handler_cmdb::C__CREATE);
            }
        }

        return $l_obj_id;
    }

    /**
     *
     * @param   integer $p_cat_level
     * @param   integer $p_intOldRecStatus
     *
     * @return  integer
     * @throws  Exception
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_dao_cmdb
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata['isys_cats_organization_list__status'];

        $l_list_id = $l_catdata['isys_cats_organization_list__id'];

        if (empty($l_list_id)) {
            $l_list_id = $this->create_connector('isys_cats_organization_list', $_GET[C__CMDB__GET__OBJECT]);
        }

        if ($l_list_id) {
            if (empty($_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()])) {
                $l_description = $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . defined_or_default('C__CATS__ORGANIZATION')];
            } else {
                $l_description = $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()];
            }

            $l_bRet = $this->save(
                $l_list_id,
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CONTACT__ORGANISATION_TITLE'],
                $_POST['C__CONTACT__ORGANISATION_PHONE'],
                $_POST['C__CONTACT__ORGANISATION_FAX'],
                $_POST['C__CONTACT__ORGANISATION_WEBSITE'],
                $_POST['C__CONTACT__ORGANISATION_ASSIGNMENT'],
                $l_description
            );

            $this->m_strLogbookSQL = $this->get_last_query();

            if ($l_bRet && !$this->update_orga_object($_GET[C__CMDB__GET__OBJECT], $_POST['C__CONTACT__ORGANISATION_TITLE'])) {
                throw new isys_exception_dao_cmdb('Error while updating organization object');
            }
        }

        return $l_bRet == true ? $l_list_id : -1;
    }

    /**
     * @param      $p_catlevel
     * @param int  $p_status
     * @param null $p_title
     * @param null $p_telephone
     * @param null $p_fax
     * @param null $p_website
     * @param null $p_headquarter
     * @param null $p_description
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function save($p_catlevel, $p_status = C__RECORD_STATUS__NORMAL, $p_title = null, $p_telephone = null, $p_fax = null, $p_website = null, $p_headquarter = null, $p_description = null)
    {
        $l_old_data = $this->get_data($p_catlevel)->get_row();

        if (empty($l_old_data['isys_cats_organization_list__isys_connection__id'])) {
            $l_id = isys_cmdb_dao_connection::instance($this->m_db)->add_connection($p_headquarter);
        } else {
            $l_id = isys_cmdb_dao_connection::instance($this->m_db)->update_connection($l_old_data['isys_cats_organization_list__isys_connection__id'], $p_headquarter);
        }

        // @see ID-6582  Always set "isys_cats_organization_list__isys_connection__id" with the connection ID.

        $changes = [
            'isys_obj__status = ' . $this->convert_sql_id($p_status),
            'isys_cats_organization_list__status = ' . $this->convert_sql_id($p_status),
            'isys_cats_organization_list__isys_connection__id = ' . $this->convert_sql_id($l_id),
        ];

        if ($p_title !== null) {
            $changes[] = 'isys_obj__title = ' . $this->convert_sql_text($p_title);
            $changes[] = 'isys_cats_organization_list__title = ' . $this->convert_sql_text($p_title);
        }

        if ($p_telephone !== null) {
            $changes[] = 'isys_cats_organization_list__telephone = ' . $this->convert_sql_text($p_telephone);
        }

        if ($p_fax !== null) {
            $changes[] = 'isys_cats_organization_list__fax = ' . $this->convert_sql_text($p_fax);
        }

        if ($p_website !== null) {
            $changes[] = 'isys_cats_organization_list__website = ' . $this->convert_sql_text($p_website);
        }

        if ($p_description !== null) {
            $changes[] = 'isys_cats_organization_list__description = ' . $this->convert_sql_text($p_description);
        }

        $l_sql = 'UPDATE isys_cats_organization_list
            INNER JOIN isys_obj ON isys_obj__id = isys_cats_organization_list__isys_obj__id
            SET ' . implode(', ', $changes) . ' 
            WHERE isys_cats_organization_list__id = ' . $this->convert_sql_id($p_catlevel) . ';';

        if (!$this->update($l_sql) || !$this->apply_update()) {
            return false;
        }

        isys_cmdb_dao_category_g_global::instance($this->m_db)
            ->handle_template_status($l_old_data['isys_obj__status'], $l_old_data['isys_obj__id']);

        // Create implicit relation.
        $l_data = $this->get_data($p_catlevel)->get_row();

        if ($l_data && $l_data['isys_cats_organization_list__isys_obj__id'] > 0) {
            $l_relation_dao = isys_factory::get_instance('isys_cmdb_dao_category_g_relation', $this->get_database_component());

            if ($p_headquarter > 0) {
                // Update relation.
                $l_relation_dao->handle_relation(
                    $p_catlevel,
                    $this->m_table,
                    defined_or_default('C__RELATION_TYPE__ORGANIZATION_HEADQUARTER'),
                    $l_data[$this->m_table . '__isys_catg_relation_list__id'],
                    $p_headquarter,
                    $l_data[$this->m_table . '__isys_obj__id']
                );
            } else {
                // Remove relation.
                $l_relation_dao->delete_relation($l_data['isys_cats_organization_list__isys_catg_relation_list__id']);
            }
        }

        return true;
    }

    /**
     *
     * @param   integer $p_object_id
     * @param   string  $p_title
     *
     * @return  boolean
     * @throws  isys_exception_dao
     */
    public function update_orga_object($p_object_id, $p_title)
    {
        $l_sql = 'UPDATE isys_obj 
            SET isys_obj__title = ' . $this->convert_sql_text($p_title) . ' 
            WHERE isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ';';

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * Executes the query to create new oragization
     *
     * @param $p_objID
     * @param $p_newRecStatus
     * @param $p_title
     * @param $p_street
     * @param $p_zip_code
     * @param $p_city
     * @param $p_country
     * @param $p_telephone
     * @param $p_fax
     * @param $p_website
     * @param $p_connection_id
     * @param $p_headquarter_id
     * @param $p_description
     *
     * @return bool|int
     * @throws isys_exception_dao
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus, $p_title, $p_street, $p_zip_code, $p_city, $p_country, $p_telephone, $p_fax, $p_website, $p_connection_id, $p_headquarter_id = null, $p_description = '')
    {
        if ($p_connection_id === null) {
            $l_dao_connection = new isys_cmdb_dao_connection($this->m_db);
            $p_connection_id = $l_dao_connection->add_connection($p_headquarter_id);
        }

        $l_sql = 'INSERT IGNORE INTO isys_cats_organization_list SET
            isys_cats_organization_list__isys_obj__id = ' . $this->convert_sql_id($p_objID) . ',
            isys_cats_organization_list__title = ' . $this->convert_sql_text($p_title) . ',
            isys_cats_organization_list__status = ' . $this->convert_sql_id($p_newRecStatus) . ',
            isys_cats_organization_list__telephone = ' . $this->convert_sql_text($p_telephone) . ',
            isys_cats_organization_list__fax = ' . $this->convert_sql_text($p_fax) . ',
            isys_cats_organization_list__website = ' . $this->convert_sql_text($p_website) . ',
            isys_cats_organization_list__isys_connection__id = ' . $this->convert_sql_id($p_connection_id) . ',
            isys_cats_organization_list__headquarter = ' . $this->convert_sql_id($p_headquarter_id) . ',
            isys_cats_organization_list__description = ' . $this->convert_sql_text($p_description) . ';';

        if ($this->update($l_sql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        }

        return false;
    }

    /**
     * Get PersonData by $p_orga_id (location).
     *
     * @param   integer $p_orga_id
     *
     * @return  isys_component_dao_result
     */
    public function get_persons_by_id($p_orga_id)
    {
        $l_sql = 'SELECT *, isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address
            FROM isys_cats_person_list
            INNER JOIN isys_connection ON isys_connection__id = isys_cats_person_list__isys_connection__id
            LEFT JOIN isys_catg_mail_addresses_list ON isys_connection__isys_obj__id = isys_catg_mail_addresses_list__isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
            WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_orga_id) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     *
     * @param   integer $p_orga_id
     *
     * @return  string
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_organisation_title_by_id($p_orga_id)
    {
        $l_sql = 'SELECT isys_cats_organization_list__title
            FROM isys_cats_organization_list
            WHERE isys_cats_organization_list__isys_obj__id = ' . $this->convert_sql_id($p_orga_id) . ';';

        return $this->retrieve($l_sql)
            ->get_row_value('isys_cats_organization_list__title');
    }

    /**
     * Method for simply updating the organization title.
     *
     * @param   integer $p_organization_object_id
     * @param   string  $p_title
     *
     * @return  boolean
     * @throws  isys_exception_dao
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_organization_title($p_organization_object_id, $p_title)
    {
        $l_sql = 'UPDATE isys_cats_organization_list
			SET isys_cats_organization_list__title = ' . $this->convert_sql_text($p_title) . '
			WHERE isys_cats_organization_list__isys_obj__id = ' . $this->convert_sql_id($p_organization_object_id) . ';';

        return $this->update($l_sql) && $this->apply_update();
    }
}
