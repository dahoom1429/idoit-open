<?php

/**
 * i-doit
 *
 * DAO: specific category for persons.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_cmdb_dao_category_s_person extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'person';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * Field for the object id
     *
     * @var  string
     */
    protected $m_object_id_field = 'isys_cats_person_list__isys_obj__id';

    /**
     * @param int    $p_user_id
     * @param int    $p_group_id
     * @param string $p_changetyp
     */
    public static function slotBeforeUserGroupChanged($p_user_id, $p_group_id, $p_changetype)
    {
        // delete auth cache of the person
        isys_caching::factory('auth-' . $p_user_id)
            ->clear();

        /* Delete Object cache for this person */
        isys_auth_cmdb_objects::invalidate_cache($p_user_id);
    }

    /**
     * Retrieve custom properties
     *
     * @return array
     */
    public function custom_properties()
    {
        return [
            'custom_1' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'Custom 1',
                    C__PROPERTY__INFO__DESCRIPTION => 'Custom property 1'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__custom1'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__CUSTOM1'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]),
            'custom_2' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'Custom 2',
                    C__PROPERTY__INFO__DESCRIPTION => 'Custom property 2'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__custom2'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__CUSTOM2'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]),
            'custom_3' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'Custom 3',
                    C__PROPERTY__INFO__DESCRIPTION => 'Custom property 3'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__custom3'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__CUSTOM3'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]),
            'custom_4' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'Custom 4',
                    C__PROPERTY__INFO__DESCRIPTION => 'Custom property 4'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__custom4'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__CUSTOM4'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]),
            'custom_5' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'Custom 5',
                    C__PROPERTY__INFO__DESCRIPTION => 'Custom property 5'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__custom5'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__CUSTOM5'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]),
            'custom_6' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'Custom 6',
                    C__PROPERTY__INFO__DESCRIPTION => 'Custom property 6'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__custom6'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__CUSTOM6'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]),
            'custom_7' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'Custom 7',
                    C__PROPERTY__INFO__DESCRIPTION => 'Custom property 7'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__custom7'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__CUSTOM7'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]),
            'custom_8' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'Custom 8',
                    C__PROPERTY__INFO__DESCRIPTION => 'Custom property 8'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__custom8'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__CUSTOM8'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]),
        ];
    }

    /**
     * Callback method for property assigned_variant.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function callback_property_salutation()
    {
        return [
            'm' => isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__MISTER'),
            'f' => isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__MISSES')
        ];
    }

    /**
     * Method for creating a formatted user-name, for example "Herr Dr. Leonard Fischer".
     *
     * @param   integer $p_object_id
     *
     * @return  string
     * @throws  isys_exception_database
     */
    public function get_formatted_name($p_object_id)
    {
        $l_sql = 'SELECT isys_cats_person_list__salutation AS salutation,
            isys_cats_person_list__academic_degree AS degree,
            isys_cats_person_list__first_name AS firstname,
            isys_cats_person_list__last_name AS lastname
            FROM isys_cats_person_list
            WHERE isys_cats_person_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ';';

        $l_row = $this->retrieve($l_sql)
            ->get_row();

        // We use this array stuff so that the name will not look like " dr.  fischer" (one space between every item).
        return implode(' ', array_filter([$this->callback_property_salutation()[$l_row['salutation']], $l_row['degree'], $l_row['firstname'], $l_row['lastname']]));
    }

    /**
     * Method for creating multiple formatted user-name, for example "Herr Dr. Leonard Fischer, Frau Mustermann".
     *
     * @param   array $p_object_ids
     *
     * @return  string
     * @throws  isys_exception_database
     */
    public function get_formatted_names(array $p_object_ids)
    {
        $l_names = [];
        $l_sql = 'SELECT isys_cats_person_list__salutation AS salutation,
            isys_cats_person_list__academic_degree AS degree,
            isys_cats_person_list__first_name AS firstname,
            isys_cats_person_list__last_name AS lastname
            FROM isys_cats_person_list
            WHERE isys_cats_person_list__isys_obj__id ' . $this->prepare_in_condition($p_object_ids) . ';';

        $l_res = $this->retrieve($l_sql);

        while ($l_row = $l_res->get_row()) {
            $l_names[] = implode(' ', array_filter([$this->callback_property_salutation()[$l_row['salutation']], $l_row['degree'], $l_row['firstname'], $l_row['lastname']]));
        }

        // We use this array stuff so that the name will not look like " dr.  fischer" (one space between every item).
        return implode(', ', $l_names);
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
        $l_sql = 'SELECT *, isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address FROM isys_cats_person_list
			INNER JOIN isys_obj ON isys_cats_person_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_connection ON isys_connection__id = isys_cats_person_list__isys_connection__id
			LEFT JOIN isys_cats_organization_list ON isys_cats_organization_list__isys_obj__id = isys_connection__isys_obj__id
			LEFT JOIN isys_catg_mail_addresses_list ON isys_catg_mail_addresses_list__isys_obj__id = isys_cats_person_list__isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
			WHERE TRUE ' . $p_condition . $this->prepare_filter($p_filter);

        if ($p_cats_list_id !== null) {
            $l_sql .= ' AND isys_cats_person_list__id = ' . $this->convert_sql_id($p_cats_list_id);
        }

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_status !== null) {
            $l_sql .= ' AND isys_obj__status = ' . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for returning the properties.
     * Property mail won´t be imported/exported because the category E-Mail handles the import/export.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'title'               => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__title'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__PERSON_MASTER__TITLE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__MULTIEDIT => false
                ]
            ]),
            'salutation'          => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_SALUTATION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Salutation'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_person_list__salutation',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('(CASE WHEN isys_cats_person_list__salutation = \'m\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__MISTER') . '
                                    WHEN isys_cats_person_list__salutation = \'f\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__MISSES') . '
                                    ELSE ' . $this->convert_sql_text(isys_tenantsettings::get('gui.empty_value')) . ' END)', 'isys_cats_person_list'),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_person_list', 'LEFT', 'isys_cats_person_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CONTACT__PERSON_SALUTATION',
                    C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPopupType' => 'dialog',
                        'p_arData'       => new isys_callback([
                            'isys_cmdb_dao_category_s_person_master',
                            'callback_property_salutation'
                        ]),
                    ]
                ]
            ]),
            'first_name'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_FIRST_NAME',
                    C__PROPERTY__INFO__DESCRIPTION => 'First name'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__first_name'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__PERSON_FIRST_NAME'
                ]
            ]),
            'last_name'           => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_LAST_NAME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Last name'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__last_name'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__PERSON_LAST_NAME'
                ]
            ]),
            'academic_degree'     => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_ACADEMIC_DEGREE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Academic degree'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__academic_degree'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__PERSON_ACADEMIC_DEGREE'
                ]
            ]),
            'function'            => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_FUNKTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Function'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__function'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__PERSON_FUNKTION'
                ]
            ]),
            'service_designation' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_SERVICE_DESIGNATION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Service designation'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__service_designation'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__PERSON_SERVICE_DESIGNATION'
                ]
            ]),
            'street'              => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_STEET',
                    C__PROPERTY__INFO__DESCRIPTION => 'Street'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__street'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__PERSON_STREET'
                ]
            ]),
            'city'                => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_CITY',
                    C__PROPERTY__INFO__DESCRIPTION => 'City'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__city'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__PERSON_CITY'
                ]
            ]),
            'zip_code'            => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_ZIP_CODE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Zip-Code'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__zip_code'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__PERSON_ZIP_CODE'
                ]
            ]),
            'mail'                => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_MAIL_ADDRESS',
                    C__PROPERTY__INFO__DESCRIPTION => 'E-mail address'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_mail_addresses_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_mail_addresses_list__title  FROM isys_catg_mail_addresses_list',
                        'isys_catg_mail_addresses_list',
                        'isys_catg_mail_addresses_list__id',
                        'isys_catg_mail_addresses_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([' isys_catg_mail_addresses_list__primary = 1'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_mail_addresses_list',
                            'LEFT',
                            'isys_catg_mail_addresses_list__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__PERSON_MAIL_ADDRESS'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__EXPORT => false,
                    // C__PROPERTY__PROVIDES__IMPORT => false
                ]
            ]),
            'phone_company'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_TELEPHONE_COMPANY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Telephone company'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__phone_company'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__PERSON_PHONE_COMPANY'
                ]
            ]),
            'phone_home'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_TELEPHONE_HOME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Telephone home'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__phone_home'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__PERSON_PHONE_HOME'
                ]
            ]),
            'phone_mobile'        => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_TELEPHONE_MOBILE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Cellphone'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__phone_mobile'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__PERSON_PHONE_MOBILE'
                ]
            ]),
            'fax'                 => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_FAX',
                    C__PROPERTY__INFO__DESCRIPTION => 'Fax'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__fax'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__PERSON_FAX'
                ]
            ]),
            'pager'               => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_PAGER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Pager'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__pager'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__PERSON_PAGER'
                ]
            ]),
            'personnel_number'    => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_PERSONNEL_NUMBER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Personnel number'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__personnel_number'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__PERSON_PERSONNEL_NUMBER'
                ]
            ]),
            'department'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_DEPARTMENT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Department'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__department'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__PERSON_DEPARTMENT'
                ]
            ]),
            'organization'        => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_ASSIGNED_ORGANISATION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Organisation'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_cats_person_list__isys_connection__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__ORGANIZATION'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_s_person',
                        'callback_property_relation_handler'
                    ], [
                        'isys_cmdb_dao_category_s_person',
                        true
                    ]),
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                                FROM isys_cats_person_list
                                INNER JOIN isys_connection ON isys_connection__id = isys_cats_person_list__isys_connection__id
                                INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id',
                        'isys_cats_person_list',
                        'isys_cats_person_list__id',
                        'isys_cats_person_list__isys_obj__id',
                        '',
                        '',
                        null,
                        null,
                        '',
                        1
                    ),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_person_list', 'LEFT', 'isys_cats_person_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_cats_person_list__isys_connection__id',
                            'isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_obj',
                            'LEFT',
                            'isys_connection__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CONTACT__PERSON_ASSIGNED_ORGANISATION',
                    C__PROPERTY__UI__PARAMS => [
                        'title'     => 'LC__POPUP__BROWSER__ORGANISATION',
                        'catFilter' => 'C__CATS__ORGANIZATION;C__CATS__ORGANIZATION_MASTER_DATA;C__CATS__ORGANIZATION_PERSONS'
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connection'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'ldap_id'             => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__ID',
                    C__PROPERTY__INFO__DESCRIPTION => 'ID'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__isys_ldap__id'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => ''
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__IMPORT    => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'person_property_ldap_id'
                    ]
                ]
            ]),
            'ldap_dn'             => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'DN',
                    C__PROPERTY__INFO__DESCRIPTION => 'DN'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__ldap_dn'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => ''
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__IMPORT    => false
                ]
            ]),
            'description'         => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__PERSON', 'C__CATS__PERSON')
                ]
            ]),
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        return true;
    }

    /**
     *
     * @param   integer $p_object_id
     *
     * @return  string
     */
    public function get_username_by_id_as_string($p_object_id)
    {
        $l_data = $this->get_data(null, $p_object_id);

        if (is_countable($l_data) && count($l_data)) {
            return $l_data->get_row_value('isys_cats_person_list__title');
        }

        return '';
    }

    /**
     * @param      $p_id
     * @param      $p_username
     * @param      $p_pass
     * @param      $p_description
     * @param int  $p_status
     * @param bool $p_generate_md5
     * @param int  $disabledLogin
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function save_login($p_id, $p_username, $p_pass, $p_description, $p_status = C__RECORD_STATUS__NORMAL, $p_generate_md5 = true, $disabledLogin = null)
    {
        $l_sql = "UPDATE isys_cats_person_list SET
			isys_cats_person_list__title = " . $this->convert_sql_text($p_username) . ", ";

        /**
         * ID-5677: preg_match('/^[a-f0-9]{32}$/') checks if given password is already a md5
         */
        if (preg_match('/^[a-f0-9]{32}$/', $p_pass) === 1) {
            $p_generate_md5 = false;
        }

        if (!empty($p_pass)) {
            $l_sql .= "isys_cats_person_list__user_pass = " . $this->convert_sql_text($p_generate_md5 ? md5($p_pass) : $p_pass) . ", ";
        }

        if ($disabledLogin !== null) {
            $l_sql .= "isys_cats_person_list__disabled_login = " . $this->convert_sql_int($disabledLogin) . ', ';
        }

        $l_sql .= 'isys_cats_person_list__description = ' . $this->convert_sql_text($p_description) . ',
			isys_cats_person_list__status = ' . $this->convert_sql_id($p_status) . '
			WHERE isys_cats_person_list__id = ' . $this->convert_sql_id($p_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Method for detaching a user from a group.
     *
     * @param   integer $p_user_id
     * @param   integer $p_group_id
     * @param   string  $p_condition
     *
     * @return  boolean
     */
    public function detach_groups($p_user_id, $p_group_id = null, $p_condition = null, $p_user_created = false)
    {
        // Need to remove the relation object first
        $l_sql = 'SELECT isys_person_2_group__id, isys_person_2_group__isys_catg_relation_list__id FROM isys_person_2_group WHERE
            isys_person_2_group__isys_obj__id__person = ' . $this->convert_sql_id($p_user_id);

        if ($p_group_id) {
            $l_sql .= " AND isys_person_2_group__isys_obj__id__group = " . $this->convert_sql_id($p_group_id);
        }

        if ($p_condition) {
            $l_sql .= $p_condition;
        }
        $l_res = $this->retrieve($l_sql);

        // Only call signal if user already existed it´s not necessary for users which were newly created
        if (!$p_user_created) {
            isys_component_signalcollection::get_instance()
                ->emit('mod.cmdb.beforeUserGroupChanged', $p_user_id, $p_group_id, 'detach-person');
        }

        if ($l_res->num_rows() > 0) {
            /**
             * @var $l_dao_rel isys_cmdb_dao_category_g_relation
             */
            $l_dao_rel = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());
            $l_delete_arr = [];
            while ($l_row = $l_res->get_row()) {
                $l_delete_arr[] = $l_row['isys_person_2_group__id'];
                if ($l_row['isys_person_2_group__isys_catg_relation_list__id']) {
                    $l_dao_rel->delete_relation($l_row['isys_person_2_group__isys_catg_relation_list__id']);
                }
            }

            if (count($l_delete_arr)) {
                $l_delete = 'DELETE FROM isys_person_2_group WHERE isys_person_2_group__id IN (' . implode(',', $l_delete_arr) . ')';
                $this->update($l_delete);
            }

            return $this->apply_update();
        }

        return false;
    }

    /**
     * Attaches user $p_user_id (objid) to group $p_group_id (objid).
     *
     * @param   integer $p_user_id
     * @param   integer $p_group_id
     * @param   string  $p_ldap
     *
     * @return  boolean
     */
    public function attach_group($p_user_id, $p_group_id, $p_ldap = '0')
    {
        if ($p_user_id > 0 && $p_group_id > 0) {
            $person2GroupId = null;
            $relationId = null;
            // Check before attaching so that there are no duplicate assignments
            $checkSql = 'SELECT isys_person_2_group__id, isys_person_2_group__isys_catg_relation_list__id FROM isys_person_2_group 
                WHERE isys_person_2_group__isys_obj__id__group = ' . $this->convert_sql_id($p_group_id) . ' 
                AND isys_person_2_group__isys_obj__id__person = ' . $this->convert_sql_id($p_user_id);
            $checkResult = $this->retrieve($checkSql);

            $l_sql = 'INSERT INTO isys_person_2_group SET
                isys_person_2_group__isys_obj__id__person = ' . $this->convert_sql_id($p_user_id) . ',
                isys_person_2_group__isys_obj__id__group = ' . $this->convert_sql_id($p_group_id) . ',
                isys_person_2_group__ldap = ' . $this->convert_sql_int($p_ldap) . ';';

            if (is_countable($checkResult) && count($checkResult) > 0) {
                // Person is already attached to the group set the ldap flag
                $data = $checkResult->get_row();
                $person2GroupId = $data['isys_person_2_group__id'];
                $relationId = $data['isys_person_2_group__isys_catg_relation_list__id'];

                $l_sql = 'UPDATE isys_person_2_group SET isys_person_2_group__ldap = ' . $this->convert_sql_int($p_ldap) . '
                    WHERE isys_person_2_group__id = ' . $this->convert_sql_id($person2GroupId);
            }

            if ($this->update($l_sql)) {
                try {
                    $lastId = ($person2GroupId !== null) ? $person2GroupId : $this->get_last_insert_id();

                    // @see  ID-7106  Get the relation type ID from the database, if necessary.
                    if (!defined('C__RELATION_TYPE__PERSON_ASSIGNED_GROUPS')) {
                        $sql = "SELECT isys_relation_type__id AS id
                            FROM isys_relation_type
                            WHERE isys_relation_type__const = 'C__RELATION_TYPE__PERSON_ASSIGNED_GROUPS'
                            LIMIT 1;";

                        $groupAssignmentRelationType = $this
                            ->retrieve($sql)
                            ->get_row_value('id');
                    }

                    // Add relation see ID-2284
                    isys_cmdb_dao_category_g_relation::instance($this->get_database_component())
                        ->handle_relation(
                            $lastId,
                            'isys_person_2_group',
                            defined_or_default('C__RELATION_TYPE__PERSON_ASSIGNED_GROUPS', $groupAssignmentRelationType),
                            $relationId,
                            $p_group_id,
                            $p_user_id
                        );
                } catch (Exception $e) {
                    // Catching Error:
                    // CMDB Error: Error: Your relation type for table 'isys_person_2_group' is empty. The constant cache is maybe not available here. [] []
                }

                isys_component_signalcollection::get_instance()
                    ->emit('mod.cmdb.beforeUserGroupChanged', $p_user_id, $p_group_id, 'attach-person');

                return $this->apply_update();
            }
        }

        return false;
    }

    /**
     * Get person id by username.
     *
     * @param   string $p_username
     *
     * @return  integer
     */
    public function get_person_id_by_username($p_username, $p_status = null)
    {
        $l_sql = 'SELECT isys_cats_person_list__isys_obj__id FROM isys_cats_person_list
			INNER JOIN isys_obj ON isys_obj__id = isys_cats_person_list__isys_obj__id
			WHERE BINARY LOWER(isys_cats_person_list__title) = LOWER(' . $this->convert_sql_text($p_username) . ')';
        if ($p_status !== null) {
            $l_sql .= ' AND isys_obj__status = ' . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql . ';')
            ->get_row_value('isys_cats_person_list__isys_obj__id');
    }

    /**
     * Retrieves a person by username.
     *
     * @param  integer $p_username
     * @param  integer $p_status
     *
     * @return isys_component_dao_result
     */
    public function get_person_by_username($p_username, $p_status = null)
    {
        $l_sql = 'SELECT *, isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address
			FROM isys_cats_person_list
			INNER JOIN isys_obj ON isys_obj__id = isys_cats_person_list__isys_obj__id
			LEFT JOIN isys_connection ON isys_cats_person_list__isys_connection__id = isys_connection__id
			LEFT JOIN isys_catg_mail_addresses_list ON isys_catg_mail_addresses_list__isys_obj__id = isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
			WHERE (BINARY LOWER(isys_cats_person_list__title) = LOWER(' . $this->convert_sql_text($p_username) . '))';

        if ($p_status !== null) {
            $l_sql .= ' AND isys_obj__status = ' . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Retrieve persons by object ID.
     *
     * @param int      $p_object_id
     * @param null|int $status
     *
     * @return  isys_component_dao_result
     */
    public function get_person_by_id($p_object_id, $status = null)
    {
        return $this->get_data(null, $p_object_id, null, null, $status);
    }

    /**
     * Retrieve persons by one or multiple email address(es).
     *
     * @param   mixed $p_email_address May be a string or an array.
     *
     * @return  isys_component_dao_result
     */
    public function get_persons_by_email($p_email_address)
    {
        if (!is_array($p_email_address)) {
            $p_email_address[] = $p_email_address;
        }

        $l_parts = [];

        foreach ($p_email_address as $l_address) {
            $l_parts[] = 'isys_catg_mail_addresses_list__title = ' . $this->convert_sql_text($l_address);
        }

        $l_condition = '';

        if (count($l_parts) > 0) {
            $l_condition = 'AND (' . implode(' OR ', $l_parts) . ')';
        }

        return $this->get_data(null, null, $l_condition);
    }

    /**
     * Get Person data by Custom Attribute
     *
     * @param string $customPropertyField
     * @param string $customPropertyValue
     *
     * @return isys_component_dao_result
     */
    public function getPersonByCustomProperty($customPropertyField, $customPropertyValue)
    {
        $condition = "AND {$customPropertyField} = {$this->convert_sql_text($customPropertyValue)}";
        return $this->get_data(null, null, $condition);
    }

    /**
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        $this->m_category_id = defined_or_default('C__CATS__PERSON');
        parent::__construct($p_db);

        isys_component_signalcollection::get_instance()
            ->connect('mod.cmdb.beforeUserGroupChanged', [
                'isys_cmdb_dao_category_s_person',
                'slotBeforeUserGroupChanged'
            ]);
    }
}
