<?php

/**
 * i-doit
 *
 * DAO: global category for fiber/lead
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_fiber_lead extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'fiber_lead';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__FIBER_LEAD';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'label'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__FIBER_LEAD__LABEL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Fiber label'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_fiber_lead_list__label',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_catg_fiber_lead_list__label FROM isys_catg_fiber_lead_list',
                        'isys_catg_fiber_lead_list', 'isys_catg_fiber_lead_list__id', 'isys_catg_fiber_lead_list__isys_obj__id', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_fiber_lead_list__isys_obj__id']))
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__FIBER_LEAD__LABEL'
                ]
            ]),
            'category'    => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__FIBER_LEAD__CATEGORY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Fiber category'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_fiber_lead_list__isys_fiber_category__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_fiber_category',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_fiber_category',
                        'isys_fiber_category__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_fiber_category__title
                            FROM isys_catg_fiber_lead_list
                            INNER JOIN isys_fiber_category ON isys_fiber_category__id = isys_catg_fiber_lead_list__isys_fiber_category__id', 'isys_catg_fiber_lead_list',
                        'isys_catg_fiber_lead_list__id', 'isys_catg_fiber_lead_list__isys_obj__id', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_fiber_lead_list__isys_obj__id'])),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_fiber_lead_list', 'LEFT', 'isys_catg_fiber_lead_list__isys_obj__id',
                            'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_fiber_category', 'LEFT', 'isys_catg_fiber_lead_list__isys_fiber_category__id',
                            'isys_fiber_category__id')
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__FIBER_LEAD__CATEGORY',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'   => 'isys_fiber_category',
                        'p_bDbFieldNN' => 1
                    ]
                ]
            ]),
            'color'       => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__FIBER_LEAD__COLOR',
                    C__PROPERTY__INFO__DESCRIPTION => 'Fiber color'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_fiber_lead_list__isys_cable_colour__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_cable_colour',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_cable_colour',
                        'isys_cable_colour__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_cable_colour__title
                            FROM isys_catg_fiber_lead_list
                            INNER JOIN isys_cable_colour ON isys_cable_colour__id = isys_catg_fiber_lead_list__isys_cable_colour__id', 'isys_catg_fiber_lead_list',
                        'isys_catg_fiber_lead_list__id', 'isys_catg_fiber_lead_list__isys_obj__id', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_fiber_lead_list__isys_obj__id'])),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_fiber_lead_list', 'LEFT', 'isys_catg_fiber_lead_list__isys_obj__id',
                            'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cable_colour', 'LEFT', 'isys_catg_fiber_lead_list__isys_cable_colour__id',
                            'isys_cable_colour__id')
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__FIBER_LEAD__COLOR',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_cable_colour'
                    ]
                ]
            ]),
            'damping'     => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__FIBER_LEAD__DAMPING',
                    C__PROPERTY__INFO__DESCRIPTION => 'Damping'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_fiber_lead_list__damping',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT CONCAT(isys_catg_fiber_lead_list__damping, " DB") FROM isys_catg_fiber_lead_list',
                        'isys_catg_fiber_lead_list', 'isys_catg_fiber_lead_list__id', 'isys_catg_fiber_lead_list__isys_obj__id', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_fiber_lead_list__isys_obj__id']))
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__FIBER_LEAD__DAMPING'
                ]
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Categories description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_fiber_lead_list__description',
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__FIBER_LEAD', 'C__CATG__FIBER_LEAD')
                ],
            ])
        ];
    }

    /**
     * Method for finding out if a given fiber is in use.
     *
     * @param integer $p_category_entry_id
     * @param string  $p_type
     * @param integer $p_status
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function find_fiber_usage($p_category_entry_id, $p_type = 'rx', $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_type = ($p_type == 'rx' ? 'rx' : 'tx');

        $l_sql = 'SELECT isys_catg_connector_list__id, isys_catg_connector_list__title, isys_obj__id, isys_obj__title, isys_obj__isys_obj_type__id, isys_obj_type__title
            FROM isys_catg_connector_list
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_connector_list__isys_obj__id
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            WHERE isys_catg_connector_list__used_fiber_lead_' . $l_type . ' = ' . $this->convert_sql_id($p_category_entry_id) . '
            AND isys_catg_connector_list__status = ' . $this->convert_sql_int($p_status);

        return $this->retrieve($l_sql);
    }
}
