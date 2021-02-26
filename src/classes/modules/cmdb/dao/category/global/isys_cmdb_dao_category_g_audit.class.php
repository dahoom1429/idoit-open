<?php
use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DynamicProperty;
use idoit\Component\Property\Type\TextProperty;
use idoit\Component\Property\Type\ObjectBrowserContactProperty;

/**
 * i-doit
 *
 * DAO: global category for audits
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_audit extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'audit';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__AUDIT';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Callback method for the multiselection object-browser.
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   isys_request            $request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_commission(isys_request $request)
    {
        global $g_comp_database;
        $return = [];

        $auditData = isys_cmdb_dao_category_g_audit::instance($g_comp_database)
            ->get_data($request->get_category_data_id())
            ->get_row();

        $personRes = isys_cmdb_dao_category_g_contact::instance($g_comp_database)
            ->get_assigned_contacts_by_relation_id($auditData["isys_catg_audit_list__commission"]);

        while ($row = $personRes->get_row()) {
            $return[] = $row['isys_obj__id'];
        }

        return $return;
    }

    /**
     * Callback method for the multiselection object-browser.
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   isys_request            $request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_responsible(isys_request $request)
    {
        global $g_comp_database;
        $return = [];

        $auditData = isys_cmdb_dao_category_g_audit::instance($g_comp_database)
            ->get_data($request->get_category_data_id())
            ->get_row();

        $personRes = isys_cmdb_dao_category_g_contact::instance($g_comp_database)
            ->get_assigned_contacts_by_relation_id($auditData["isys_catg_audit_list__responsible"]);

        while ($row = $personRes->get_row()) {
            $return[] = $row['isys_obj__id'];
        }

        return $return;
    }

    /**
     * Callback method for the multiselection object-browser.
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   isys_request            $request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_involved(isys_request $request)
    {
        global $g_comp_database;
        $return = [];

        $auditData = isys_cmdb_dao_category_g_audit::instance($g_comp_database)
            ->get_data($request->get_category_data_id())
            ->get_row();

        $personRes = isys_cmdb_dao_category_g_contact::instance($g_comp_database)
            ->get_assigned_contacts_by_relation_id($auditData["isys_catg_audit_list__involved"]);

        while ($row = $personRes->get_row()) {
            $return[] = $row['isys_obj__id'];
        }

        return $return;
    }

    /**
     * Build query for contact properties
     *
     * @param string $p_field
     *
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function build_contact_subselect($p_field = 'isys_catg_audit_list__commission')
    {
        return 'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id ,\'}\')
                            FROM isys_catg_audit_list
                            INNER JOIN isys_contact_2_isys_obj ON isys_contact_2_isys_obj__isys_contact__id = ' . $p_field . '
                            INNER JOIN isys_obj ON isys_obj__id = isys_contact_2_isys_obj__isys_obj__id
                            ';
    }

    private function handle_callback_contact($p_id)
    {
        $l_contact_res = isys_cmdb_dao_category_g_contact::instance(isys_application::instance()->database)
            ->get_assigned_contacts_by_relation_id($p_id);

        if ($l_contact_res->num_rows() > 0) {
            $l_strOut = '';
            while ($l_row = $l_contact_res->get_row()) {
                $l_strOut .= $l_row['isys_obj__title'] . ' {' . $l_row['isys_obj__id'] . '}, ';
            }
            $l_strOut = rtrim($l_strOut, ', ');
        }

        return $l_strOut;
    }

    /**
     * Dynamic property callback to handle contact only for object list
     *
     * @param $p_row
     *
     * @return string
     * @throws isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_commission($p_row)
    {
        if (!empty($p_row['isys_catg_audit_list__commission'])) {
            return $this->handle_callback_contact($p_row['isys_catg_audit_list__commission']);
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Dynamic property callback to handle contact only for object list
     *
     * @param $p_row
     *
     * @return string
     * @throws isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_responsible($p_row)
    {
        if (!empty($p_row['isys_catg_audit_list__responsible'])) {
            return $this->handle_callback_contact($p_row['isys_catg_audit_list__responsible']);
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Dynamic property callback to handle contact only for object list
     *
     * @param $p_row
     *
     * @return string
     * @throws isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_involved($p_row)
    {
        if (!empty($p_row['isys_catg_audit_list__involved'])) {
            return $this->handle_callback_contact($p_row['isys_catg_audit_list__involved']);
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function dynamic_properties()
    {
        return [
            '_commission' => new DynamicProperty(
                'LC__CMDB__CATG__AUDIT__COMMISSION',
                'isys_catg_audit_list__commission',
                'isys_catg_audit_list',
                [
                    $this,
                    'dynamic_property_callback_commission'
                ]
            ),
            '_responsible' => new DynamicProperty(
                'LC__CMDB__CATG__AUDIT__RESPONSIBLE',
                'isys_catg_audit_list__responsible',
                'isys_catg_audit_list',
                [
                    $this,
                    'dynamic_property_callback_responsible'
                ]
            ),
            '_involved' => new DynamicProperty(
                'LC__CMDB__CATG__AUDIT__INVOLVED',
                'isys_catg_audit_list__involved',
                'isys_catg_audit_list',
                [
                    $this,
                    'dynamic_property_callback_involved'
                ]
            )
        ];
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
            'title'               => (new TextProperty(
                'C__CMDB__CATG__AUDIT__TITLE',
                'LC__CMDB__CATG__AUDIT__TITLE',
                'isys_catg_audit_list__title',
                'isys_catg_audit_list'
            )),
            'type'                => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__AUDIT__TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Type'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_audit_list__type',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_catg_audit_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_catg_audit_type',
                        'isys_catg_audit_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_audit_type__title FROM isys_catg_audit_list
                            INNER JOIN isys_catg_audit_type ON isys_catg_audit_type__id = isys_catg_audit_list__type',
                        'isys_catg_audit_list',
                        'isys_catg_audit_list__id',
                        'isys_catg_audit_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_audit_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_audit_list', 'LEFT', 'isys_catg_audit_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_audit_type', 'LEFT', 'isys_catg_audit_list__type', 'isys_catg_audit_type__id')
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__AUDIT__TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_catg_audit_type'
                    ]
                ]
            ]),
            'commission'          => (new ObjectBrowserContactProperty(
                'C__CMDB__CATG__AUDIT__COMMISSION',
                'LC__CMDB__CATG__AUDIT__COMMISSION',
                'isys_catg_audit_list__commission',
                'isys_catg_audit_list',
                new isys_callback([
                    'isys_cmdb_dao_category_g_audit',
                    'callback_property_commission'
                ]),
                [],
                'C__CATS__PERSON;C__CATS__PERSON_GROUP;C__CATS__ORGANIZATION'
            )),
            'responsible'         => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__AUDIT__RESPONSIBLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Responsible'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_audit_list__responsible',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_contact',
                        'isys_contact__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        $this->build_contact_subselect('isys_catg_audit_list__responsible'),
                        'isys_catg_audit_list',
                        'isys_catg_audit_list__id',
                        'isys_catg_audit_list__isys_obj__id',
                        '',
                        '',
                        null,
                        \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_audit_list__isys_obj__id']),
                        'isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_audit_list', 'LEFT', 'isys_catg_audit_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_contact_2_isys_obj',
                            'LEFT',
                            'isys_catg_audit_list__responsible',
                            'isys_contact_2_isys_obj__isys_contact__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_contact_2_isys_obj__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__AUDIT__RESPONSIBLE',
                    C__PROPERTY__UI__PARAMS => [
                        'catFilter'              => 'C__CATS__PERSON;C__CATS__PERSON_GROUP;C__CATS__ORGANIZATION',
                        'multiselection'         => true,
                        'p_bReadonly'            => 1,
                        'p_strValue'             => new isys_callback([
                            'isys_cmdb_dao_category_g_audit',
                            'callback_property_responsible'
                        ])
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'contact'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => true
                ]
            ]),
            'involved'            => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__AUDIT__INVOLVED',
                    C__PROPERTY__INFO__DESCRIPTION => 'Involved contacts'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_audit_list__involved',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_contact',
                        'isys_contact__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        $this->build_contact_subselect('isys_catg_audit_list__involved'),
                        'isys_catg_audit_list',
                        'isys_catg_audit_list__id',
                        'isys_catg_audit_list__isys_obj__id',
                        '',
                        '',
                        null,
                        \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_audit_list__isys_obj__id']),
                        'isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_audit_list', 'LEFT', 'isys_catg_audit_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_contact_2_isys_obj',
                            'LEFT',
                            'isys_catg_audit_list__involved',
                            'isys_contact_2_isys_obj__isys_contact__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_contact_2_isys_obj__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__AUDIT__INVOLVED',
                    C__PROPERTY__UI__PARAMS => [
                        'catFilter'              => 'C__CATS__PERSON;C__CATS__PERSON_GROUP;C__CATS__ORGANIZATION',
                        'multiselection'         => true,
                        'p_bReadonly'            => 1,
                        'p_strValue'             => new isys_callback([
                            'isys_cmdb_dao_category_g_audit',
                            'callback_property_involved'
                        ])
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'contact'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => true
                ]
            ]),
            'period_manufacturer' => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__AUDIT__PERIOD_MANUFACTURER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Period manufacturer'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_audit_list__period_manufacturer',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_audit_list__period_manufacturer FROM isys_catg_audit_list',
                        'isys_catg_audit_list',
                        'isys_catg_audit_list__id',
                        'isys_catg_audit_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_audit_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__AUDIT__PERIOD_MANUFACTURER'
                ]
            ]),
            'period_operator'     => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__AUDIT__PERIOD_OPERATOR',
                    C__PROPERTY__INFO__DESCRIPTION => 'Period manufacturer'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_audit_list__period_operator',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_audit_list__period_operator FROM isys_catg_audit_list',
                        'isys_catg_audit_list',
                        'isys_catg_audit_list__id',
                        'isys_catg_audit_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_audit_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__AUDIT__PERIOD_OPERATOR'
                ]
            ]),
            'apply'               => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__AUDIT__APPLY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Applied'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_audit_list__apply',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_audit_list__apply FROM isys_catg_audit_list',
                        'isys_catg_audit_list',
                        'isys_catg_audit_list__id',
                        'isys_catg_audit_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_audit_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__AUDIT__APPLY'
                ]
            ]),
            'result'              => (new \idoit\Component\Property\Type\TextAreaProperty(
                'C__CMDB__CATG__AUDIT__RESULT',
                'LC__CMDB__CATG__AUDIT__RESULT',
                'isys_catg_audit_list__result',
                'isys_catg_audit_list'
            )),
            'fault'               => (new \idoit\Component\Property\Type\TextAreaProperty(
                'C__CMDB__CATG__AUDIT__FAULT',
                'LC__CMDB__CATG__AUDIT__FAULT',
                'isys_catg_audit_list__fault',
                'isys_catg_audit_list'
            )),
            'incident'            => (new \idoit\Component\Property\Type\TextAreaProperty(
                'C__CMDB__CATG__AUDIT__INCIDENT',
                'LC__CMDB__CATG__AUDIT__INCIDENT',
                'isys_catg_audit_list__incident',
                'isys_catg_audit_list'
            )),
            'description'         => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Categories description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_audit_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_audit_list__description FROM isys_catg_audit_list',
                        'isys_catg_audit_list',
                        'isys_catg_audit_list__id',
                        'isys_catg_audit_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_audit_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__AUDIT', 'C__CATG__AUDIT')
                ]
            ])
        ];
    }
}
