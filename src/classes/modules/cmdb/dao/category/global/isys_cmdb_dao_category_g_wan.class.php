<?php

use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DynamicProperty;
use idoit\Component\Property\Type\IntProperty;
use idoit\Component\Property\Type\ObjectBrowserMultiselectProperty;
use idoit\Component\Property\Type\ObjectBrowserProperty;
use idoit\Component\Property\Type\TextAreaProperty;
use idoit\Component\Property\Type\TextProperty;

/**
 * i-doit
 *
 * CMDB DAO class for the WAN category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @version     Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_wan extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'wan';

    /**
     * Category entry is purgable.
     *
     * @var  boolean
     */
    protected $m_is_purgable = true;

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @throws Exception
     */
    protected function properties()
    {
        return [
            'title' => new TextProperty(
                'C__CATG__WAN__TITLE',
                'LC__CATG__WAN__TITLE',
                'isys_catg_wan_list__title',
                'isys_catg_wan_list'
            ),
            'role' => (new DialogPlusProperty(
                'C__CATG__WAN__ROLE',
                'LC__CATG__WAN__ROLE',
                'isys_catg_wan_list__isys_wan_role__id',
                'isys_catg_wan_list',
                'isys_wan_role'
            ))->mergePropertyUiParams([
                'p_strClass' => 'input-small'
            ]),
            'type' => (new DialogPlusProperty(
                'C__CATG__WAN__TYPE',
                'LC__CATG__WAN__TYPE',
                'isys_catg_wan_list__isys_wan_type__id',
                'isys_catg_wan_list',
                'isys_wan_type'
            ))->mergePropertyUiParams([
                'p_strClass' => 'input-small'
            ]),
            'channels'               => (new IntProperty(
                'C__CATG__WAN__CHANNELS',
                'LC__CATG__WAN__CHANNELS',
                'isys_catg_wan_list__channels',
                'isys_catg_wan_list'
            ))->mergePropertyUiParams([
                'p_strClass' => 'input-mini'
            ]),
            'call_numbers'           => (new TextAreaProperty(
                'C__CATG__WAN__CALL_NUMBERS',
                'LC__CATG__WAN__CALL_NUMBERS',
                'isys_catg_wan_list__call_numbers',
                'isys_catg_wan_list'
            ))->mergePropertyCheck([
                // This is necessary to keep linebreaks
                C__PROPERTY__CHECK__SANITIZATION => null
            ]),
            'connection_location'    => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__CONNECTION_LOCATION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Connection location'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_wan_list__connection_location',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_obj',
                        'isys_obj__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(o2.isys_obj__title, \' {\', o2.isys_obj__id, \'}\', \' >> \', o1.isys_obj__title, \' {\', o1.isys_obj__id, \'}\')
                                FROM isys_catg_wan_list
                                INNER JOIN isys_obj o1 ON o1.isys_obj__id = isys_catg_wan_list__connection_location
                                INNER JOIN isys_catg_location_list ON isys_catg_location_list__isys_obj__id = o1.isys_obj__id
                                INNER JOIN isys_obj o2 ON o2.isys_obj__id = isys_catg_location_list__parentid',
                        'isys_catg_wan_list',
                        'isys_catg_wan_list__id',
                        'isys_catg_wan_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_wan_list', 'LEFT', 'isys_catg_wan_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_obj',
                            'LEFT',
                            'isys_catg_wan_list__connection_location',
                            'isys_obj__id',
                            '',
                            'o1',
                            'o1'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_location_list',
                            'LEFT',
                            'isys_obj__id',
                            'isys_catg_location_list__isys_obj__id',
                            'o1'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_location_list__parentid', 'isys_obj__id', '', 'o2', 'o2')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__WAN__CONNECTION_LOCATION',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPopupType' => 'browser_location'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'capacity_up'            => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__CAPACITY_UP',
                    C__PROPERTY__INFO__DESCRIPTION => 'Capacity up'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_wan_list__capacity_up',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(ROUND(isys_catg_wan_list__capacity_up / isys_wan_capacity_unit__factor), \' \', isys_wan_capacity_unit__title)
                            FROM isys_catg_wan_list
                            INNER JOIN isys_wan_capacity_unit ON isys_wan_capacity_unit__id = isys_catg_wan_list__capacity_up_unit',
                        'isys_catg_wan_list',
                        'isys_catg_wan_list__id',
                        'isys_catg_wan_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_wan_list', 'LEFT', 'isys_catg_wan_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_wan_capacity_unit',
                            'LEFT',
                            'isys_catg_wan_list__capacity_up_unit',
                            'isys_wan_capacity_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__WAN__CAPACITY_UP',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-medium'
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['speed_wan']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'capacity_up_unit'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'capacity_up_unit'       => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__CAPACITY_UP_UNIT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Capacity up unit'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_wan_list__capacity_up_unit',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_wan_capacity_unit',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_wan_capacity_unit',
                        'isys_wan_capacity_unit__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_wan_capacity_unit__title
                            FROM isys_catg_wan_list
                            INNER JOIN isys_wan_capacity_unit ON isys_wan_capacity_unit__id = isys_catg_wan_list__capacity_up_unit',
                        'isys_catg_wan_list',
                        'isys_catg_wan_list__id',
                        'isys_catg_wan_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_wan_list', 'LEFT', 'isys_catg_wan_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_wan_capacity_unit',
                            'LEFT',
                            'isys_catg_wan_list__capacity_up_unit',
                            'isys_wan_capacity_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__WAN__CAPACITY_UP_UNIT',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-mini',
                        'p_strTable' => 'isys_wan_capacity_unit',
                        'p_bSort'    => false
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'capacity_down'          => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__CAPACITY_DOWN',
                    C__PROPERTY__INFO__DESCRIPTION => 'Capacity down'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_wan_list__capacity_down',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(ROUND(isys_catg_wan_list__capacity_down / isys_wan_capacity_unit__factor), \' \', isys_wan_capacity_unit__title)
                            FROM isys_catg_wan_list
                            INNER JOIN isys_wan_capacity_unit ON isys_wan_capacity_unit__id = isys_catg_wan_list__capacity_down_unit',
                        'isys_catg_wan_list',
                        'isys_catg_wan_list__id',
                        'isys_catg_wan_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_wan_list', 'LEFT', 'isys_catg_wan_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_wan_capacity_unit',
                            'LEFT',
                            'isys_catg_wan_list__capacity_down_unit',
                            'isys_wan_capacity_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__WAN__CAPACITY_DOWN',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-medium'
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['speed_wan']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'capacity_down_unit'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'capacity_down_unit'     => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__CAPACITY_DOWN_UNIT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Capacity down unit'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_wan_list__capacity_down_unit',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_wan_capacity_unit',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_wan_capacity_unit',
                        'isys_wan_capacity_unit__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_wan_capacity_unit__title
                            FROM isys_catg_wan_list
                            INNER JOIN isys_wan_capacity_unit ON isys_wan_capacity_unit__id = isys_catg_wan_list__capacity_down_unit',
                        'isys_catg_wan_list',
                        'isys_catg_wan_list__id',
                        'isys_catg_wan_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_wan_list', 'LEFT', 'isys_catg_wan_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_wan_capacity_unit',
                            'LEFT',
                            'isys_catg_wan_list__capacity_down_unit',
                            'isys_wan_capacity_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__WAN__CAPACITY_DOWN_UNIT',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-mini',
                        'p_strTable' => 'isys_wan_capacity_unit',
                        'p_bSort'    => false
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'max_capacity_up'        => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__MAX_CAPACITY_UP',
                    C__PROPERTY__INFO__DESCRIPTION => 'Max capacity up'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_wan_list__max_capacity_up',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(ROUND(isys_catg_wan_list__max_capacity_up / isys_wan_capacity_unit__factor), \' \', isys_wan_capacity_unit__title)
                            FROM isys_catg_wan_list
                            INNER JOIN isys_wan_capacity_unit ON isys_wan_capacity_unit__id = isys_catg_wan_list__max_capacity_up_unit',
                        'isys_catg_wan_list',
                        'isys_catg_wan_list__id',
                        'isys_catg_wan_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_wan_list', 'LEFT', 'isys_catg_wan_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_wan_capacity_unit',
                            'LEFT',
                            'isys_catg_wan_list__max_capacity_up_unit',
                            'isys_wan_capacity_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__WAN__MAX_CAPACITY_UP',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-medium'
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['speed_wan']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'max_capacity_up_unit'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'max_capacity_up_unit'   => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__MAX_CAPACITY_UP_UNIT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Max capacity up unit'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_wan_list__max_capacity_up_unit',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_wan_capacity_unit',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_wan_capacity_unit',
                        'isys_wan_capacity_unit__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_wan_capacity_unit__title
                            FROM isys_catg_wan_list
                            INNER JOIN isys_wan_capacity_unit ON isys_wan_capacity_unit__id = isys_catg_wan_list__max_capacity_up_unit',
                        'isys_catg_wan_list',
                        'isys_catg_wan_list__id',
                        'isys_catg_wan_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_wan_list', 'LEFT', 'isys_catg_wan_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_wan_capacity_unit',
                            'LEFT',
                            'isys_catg_wan_list__max_capacity_up_unit',
                            'isys_wan_capacity_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__WAN__MAX_CAPACITY_UP_UNIT',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-mini',
                        'p_strTable' => 'isys_wan_capacity_unit',
                        'p_bSort'    => false
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'max_capacity_down'      => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__MAX_CAPACITY_DOWN',
                    C__PROPERTY__INFO__DESCRIPTION => 'Max capacity down'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_wan_list__max_capacity_down',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(ROUND(isys_catg_wan_list__max_capacity_down / isys_wan_capacity_unit__factor), \' \', isys_wan_capacity_unit__title)
                            FROM isys_catg_wan_list
                            INNER JOIN isys_wan_capacity_unit ON isys_wan_capacity_unit__id = isys_catg_wan_list__max_capacity_down_unit',
                        'isys_catg_wan_list',
                        'isys_catg_wan_list__id',
                        'isys_catg_wan_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_wan_list', 'LEFT', 'isys_catg_wan_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_wan_capacity_unit',
                            'LEFT',
                            'isys_catg_wan_list__max_capacity_down_unit',
                            'isys_wan_capacity_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__WAN__MAX_CAPACITY_DOWN',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-medium'
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['speed_wan']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'max_capacity_down_unit'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'max_capacity_down_unit' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__MAX_CAPACITY_DOWN_UNIT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Max capacity down unit'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_wan_list__max_capacity_down_unit',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_wan_capacity_unit',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_wan_capacity_unit',
                        'isys_wan_capacity_unit__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_wan_capacity_unit__title
                            FROM isys_catg_wan_list
                            INNER JOIN isys_wan_capacity_unit ON isys_wan_capacity_unit__id = isys_catg_wan_list__max_capacity_down_unit',
                        'isys_catg_wan_list',
                        'isys_catg_wan_list__id',
                        'isys_catg_wan_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_wan_list', 'LEFT', 'isys_catg_wan_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_wan_capacity_unit',
                            'LEFT',
                            'isys_catg_wan_list__max_capacity_down_unit',
                            'isys_wan_capacity_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__WAN__MAX_CAPACITY_DOWN_UNIT',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-mini',
                        'p_strTable' => 'isys_wan_capacity_unit',
                        'p_bSort'    => false
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'project_no'             => new TextProperty(
                'C__CATG__WAN__PROJECT_NO',
                'LC__CATG__WAN__PROJECT_NO',
                'isys_catg_wan_list__project_no',
                'isys_catg_wan_list'
            ),
            'vlan_id'                => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__WAN__VLAN_ID',
                    C__PROPERTY__INFO__DESCRIPTION => 'VLAN-ID'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_wan_list__vlan',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_obj',
                        'isys_obj__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj_type__title, \' >> \', isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_wan_list
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_wan_list__vlan
                            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id',
                        'isys_catg_wan_list',
                        'isys_catg_wan_list__id',
                        'isys_catg_wan_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_wan_list', 'LEFT', 'isys_catg_wan_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_wan_list__vlan', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__WAN__VLAN_ID',
                    C__PROPERTY__UI__PARAMS => [
                        isys_popup_browser_object_ng::C__CAT_FILTER => 'C__CATS__LAYER2_NET;C__CATS__LAYER2_NET_ASSIGNED_PORTS;C__CATS__LAYER2_NET_ASSIGNED_LOGICAL_PORTS',
                        'p_strValue' => new isys_callback([
                            'isys_cmdb_dao_category_g_wan',
                            'retrieveAssignedVlan',
                        ]),
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'shopping_cart_no'       => new TextProperty(
                'C__CATG__WAN__SHOPPING_CART_NO',
                'LC__CATG__WAN__SHOPPING_CART_NO',
                'isys_catg_wan_list__shopping_cart_no',
                'isys_catg_wan_list'
            ),
            'ticket_no'              => new TextProperty(
                'C__CATG__WAN__TICKET_NO',
                'LC__CATG__WAN__TICKET_NO',
                'isys_catg_wan_list__ticket_no',
                'isys_catg_wan_list'
            ),
            'customer_no'            => new TextProperty(
                'C__CATG__WAN__CUSTOMER_NO',
                'LC__CATG__WAN__CUSTOMER_NO',
                'isys_catg_wan_list__customer_no',
                'isys_catg_wan_list'
            ),
            'router' => (new ObjectBrowserMultiselectProperty(
                'C__CATG__WAN__ROUTER',
                'LC__CATG__WAN__ROUTER',
                'isys_catg_wan_list__id',
                'isys_catg_wan_list',
                'isys_catg_wan_list_2_router',
                'isys_catg_wan_list_2_router',
                null,
                ['isys_export_helper', 'wan_connected_router'],
                'C__CATS__ROUTER'
            ))->mergePropertyUiParams([
                'p_strValue' => new isys_callback(['isys_cmdb_dao_category_g_wan', 'retrieveAssignedRouters',])
            ])->mergePropertyProvides([
                C__PROPERTY__PROVIDES__SEARCH => false,
                C__PROPERTY__PROVIDES__REPORT => false
            ]),
            'net'                    => (new ObjectBrowserMultiselectProperty(
                'C__CATG__WAN__NET',
                'LC__CATG__WAN__NET',
                'isys_catg_wan_list__id',
                'isys_catg_wan_list',
                'isys_catg_wan_list_2_net',
                'isys_catg_wan_list_2_net',
                null,
                ['isys_export_helper', 'wan_connected_net'],
                'C__CATS__NET'
            ))->mergePropertyUiParams([
                'p_strValue' => new isys_callback(['isys_cmdb_dao_category_g_wan', 'retrieveAssignedNets',])
            ])->mergePropertyProvides([
                C__PROPERTY__PROVIDES__SEARCH => false,
                C__PROPERTY__PROVIDES__REPORT => false
            ]),
            'description'            => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__WAN', 'C__CATG__WAN'),
                'isys_catg_wan_list__description',
                'isys_catg_wan_list'
            )
        ];
    }

    /**
     * @param int       $categoryEntryId
     * @param int|array $objectId
     * @param string    $condition
     * @param mixed     $filter
     * @param int       $status
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_data($categoryEntryId = null, $objectId = null, $condition = '', $filter = null, $status = null)
    {
        $sql = 'SELECT *
            FROM isys_catg_wan_list
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_wan_list__isys_obj__id
            INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
            LEFT JOIN isys_wan_role ON isys_catg_wan_list__isys_wan_role__id = isys_wan_role__id
            LEFT JOIN isys_wan_type ON isys_catg_wan_list__isys_wan_type__id = isys_wan_type__id
            LEFT JOIN isys_wan_capacity_unit ON isys_catg_wan_list__capacity_up_unit = isys_wan_capacity_unit__id
            WHERE TRUE ' . $condition . ' ' . $this->prepare_filter($filter). ' ';

        if ($objectId !== null) {
            $sql .= $this->get_object_condition($objectId);
        }

        if ($categoryEntryId !== null) {
            $sql .= ' AND isys_catg_wan_list__id = ' . $this->convert_sql_id($categoryEntryId);
        }

        if ($status !== null) {
            $sql .= ' AND isys_catg_wan_list__status = ' . $this->convert_sql_int($status);
        }

        return $this->retrieve($sql);
    }

    /**
     * Callback: Retrieve connected resources by method
     *
     * @param int       $categoryEntryId
     * @param string    $method
     *
     * @return array
     * @throws isys_exception_dao_cmdb
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    public function retrieveConnectedResources($categoryEntryId, $method)
    {
        // Ensure that request provides necessary information
        if (!empty($categoryEntryId)) {
            // Check whether method exists or not
            if (method_exists($this, $method)) {
                // Get connected resources by categoryId
                /** @var isys_component_dao_result $resource */
                $resource = $this->$method($categoryEntryId);

                // Check number of results
                if ($resource->num_rows()) {
                    $connectedResourceIds = [];

                    // Iterate over results and collect connected router Ids
                    while ($connectedResource = $resource->get_row()) {
                        $connectedResourceIds[] = (int)$connectedResource['isys_obj__id'];
                    }

                    return $connectedResourceIds;
                }
            } else {
                throw new isys_exception_dao_cmdb('Required method is not available: ' . self::class . '::' . $method);
            }
        }

        // Return empty array to ensure valid handling
        return [];
    }

    /**
     * Callback: Get connected routers
     *
     * @param isys_request $request
     *
     * @return array
     * @throws isys_exception_dao_cmdb
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    public function retrieveAssignedRouters(isys_request $request)
    {
        return $this->retrieveConnectedResources(
            $request->get_category_data_id(),
            'get_connected_routers'
        );
    }

    /**
     * Callback: Get connected nets
     *
     * @param isys_request $request
     *
     * @return array
     * @throws isys_exception_dao_cmdb
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    public function retrieveAssignedNets(isys_request $request)
    {
        return $this->retrieveConnectedResources(
            $request->get_category_data_id(),
            'get_connected_nets'
        );
    }

    /**
     * Callback: Get VLAN ID
     *
     * @param isys_request $request
     *
     * @return int
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    public function retrieveAssignedVlan(isys_request $request)
    {
        // Check for necessary category entry id
        if ($request->get_category_data_id()) {
            $vlanId = $request->get_row('isys_catg_wan_list__vlan');

            // Return -1 to prevent some defaulting procedures by multi edit
            return (!empty($vlanId)) ? $vlanId : -1;
        }

        return -1;
    }

    /**
     * Abstract method for retrieving the dynamic properties.
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function dynamic_properties()
    {
        $l_return = [
            '_capacity_up' => new DynamicProperty(
                'LC__CATG__WAN__CAPACITY_UP',
                'isys_catg_wan_list__id',
                'isys_catg_wan_list',
                [
                    $this,
                    'dynamic_property_callback_capacity_up'
                ]
            ),
            '_max_capacity_up' => new DynamicProperty(
                'LC__CATG__WAN__MAX_CAPACITY_UP',
                'isys_catg_wan_list__id',
                'isys_catg_wan_list',
                [
                    $this,
                    'dynamic_property_callback_max_capacity_up'
                ]
            ),
            '_capacity_down' => new DynamicProperty(
                'LC__CATG__WAN__CAPACITY_DOWN',
                'isys_catg_wan_list__id',
                'isys_catg_wan_list',
                [
                    $this,
                    'dynamic_property_callback_capacity_down'
                ]
            ),
            '_max_capacity_down' => new DynamicProperty(
                'LC__CATG__WAN__MAX_CAPACITY_DOWN',
                'isys_catg_wan_list__id',
                'isys_catg_wan_list',
                [
                    $this,
                    'dynamic_property_callback_max_capacity_down'
                ]
            ),
            '_net' => new DynamicProperty(
                'LC__CATG__WAN__NET',
                'isys_catg_wan_list__id',
                'isys_catg_wan_list',
                [
                    $this,
                    'dynamic_property_callback_net'
                ]
            ),
            '_router' => new DynamicProperty(
                'LC__CATG__WAN__ROUTER',
                'isys_catg_wan_list__id',
                'isys_catg_wan_list',
                [
                    $this,
                    'dynamic_property_callback_router'
                ]
            ),
        ];

        return $l_return;
    }

    /**
     * DynamicCallback: Net
     *
     * @param $p_row
     *
     * @return mixed|string
     * @throws isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_net($p_row)
    {
        global $g_comp_database;

        if (!empty($p_row['isys_catg_wan_list__id'])) {
            $l_dao = isys_cmdb_dao_category_g_wan::instance($g_comp_database);
            $l_res = $l_dao->get_connected_nets($p_row['isys_catg_wan_list__id']);
            if ($l_res->num_rows() > 0) {
                $l_return = [];
                while ($l_row = $l_res->get_row()) {
                    $l_return[] = $l_row['isys_obj__title'] . ' {' . $l_row['isys_obj__id'] . '}';
                }

                return '<ul><li>' . implode('</li><li>', $l_return) . '</li></ul>';
            }
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * DynamicCallback: Router
     *
     * @param $p_row
     *
     * @return mixed|string
     * @throws isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_router($p_row)
    {
        global $g_comp_database;

        if (!empty($p_row['isys_catg_wan_list__id'])) {
            $l_dao = isys_cmdb_dao_category_g_wan::instance($g_comp_database);
            $l_res = $l_dao->get_connected_routers($p_row['isys_catg_wan_list__id']);
            if ($l_res->num_rows() > 0) {
                $l_return = [];
                while ($l_row = $l_res->get_row()) {
                    $l_return[] = $l_row['isys_obj__title'] . ' {' . $l_row['isys_obj__id'] . '}';
                }

                return '<ul><li>' . implode('</li><li>', $l_return) . '</li></ul>';
            }
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Helper method which formats the value to the specified unit.
     *
     * @param integer $p_list_id
     * @param integer $p_obj_id
     * @param string  $p_field
     * @param string  $p_table
     *
     * @return string
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     * @throws Exception
     */
    public function dynamic_property_callback_capacity_helper($p_list_id = null, $p_obj_id = null, $p_field = '', $p_table = 'isys_wan_capacity_unit')
    {
        if ($p_list_id !== null || $p_obj_id !== null) {
            $l_db = isys_application::instance()->container->get('database');

            /** @var isys_cmdb_dao_category_g_wan $l_dao */
            $l_dao = isys_factory_cmdb_category_dao::get_instance('isys_cmdb_dao_category_g_wan', $l_db);
            $l_data = $l_dao->get_data($p_list_id, $p_obj_id)
                ->get_row();

            $l_value = $l_data[$p_field];
            if ($l_value > 0) {
                $l_unit_field = $p_field . '_unit';
                $l_dao_dialog = isys_factory_cmdb_dialog_dao::get_instance($p_table, $l_db);
                $l_unit_id = ($l_data[$l_unit_field] > 0) ? $l_data[$l_unit_field] : defined_or_default('C__WAN_CAPACITY_UNIT__KBITS', 2);
                $l_data_dialog = $l_dao_dialog->get_data($l_unit_id);
                $l_unit = $l_data_dialog['isys_wan_capacity_unit__title'];

                return isys_convert::speed_wan($l_value, $l_unit_id, C__CONVERT_DIRECTION__BACKWARD) . ' ' . $l_unit;
            }
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Dynamic callback function for property capacity_up.
     *
     * @param $p_row
     *
     * @return mixed|string
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     * @throws Exception
     */
    public function dynamic_property_callback_capacity_up($p_row)
    {
        /**
         * @var $l_dao        isys_cmdb_dao_category_g_wan
         */
        $l_dao = isys_factory_cmdb_category_dao::get_instance('isys_cmdb_dao_category_g_wan', isys_application::instance()->container->get('database'));

        return $l_dao->dynamic_property_callback_capacity_helper(
            (isset($p_row['isys_catg_wan_list__id']) ? $p_row['isys_catg_wan_list__id'] : null),
            (isset($p_row['isys_obj__id']) ? $p_row['isys_obj__id'] : null),
            'isys_catg_wan_list__capacity_up'
        );
    }

    /**
     * Dynamic callback function for property max_capacity_up.
     *
     * @param $p_row
     *
     * @return mixed|string
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     * @throws Exception
     */
    public function dynamic_property_callback_max_capacity_up($p_row)
    {
        /**
         * @var $l_dao        isys_cmdb_dao_category_g_wan
         */
        $l_dao = isys_factory_cmdb_category_dao::get_instance('isys_cmdb_dao_category_g_wan', isys_application::instance()->container->get('database'));
        $l_return = $l_dao->dynamic_property_callback_capacity_helper(
            (isset($p_row['isys_catg_wan_list__id']) ? $p_row['isys_catg_wan_list__id'] : null),
            (isset($p_row['isys_obj__id']) ? $p_row['isys_obj__id'] : null),
            'isys_catg_wan_list__max_capacity_up'
        );

        return $l_return;
    }

    /**
     * Dynamic callback function for property capacity_down.
     *
     * @param $p_row
     *
     * @return mixed|string
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     * @throws Exception
     */
    public function dynamic_property_callback_capacity_down($p_row)
    {
        /**
         * @var $l_dao        isys_cmdb_dao_category_g_wan
         */
        $l_dao = isys_factory_cmdb_category_dao::get_instance('isys_cmdb_dao_category_g_wan', isys_application::instance()->container->get('database'));
        $l_return = $l_dao->dynamic_property_callback_capacity_helper(
            (isset($p_row['isys_catg_wan_list__id']) ? $p_row['isys_catg_wan_list__id'] : null),
            (isset($p_row['isys_obj__id']) ? $p_row['isys_obj__id'] : null),
            'isys_catg_wan_list__capacity_down'
        );

        return $l_return;
    }

    /**
     * Dynamic callback function for property max_capacity_down.
     *
     * @param $p_row
     *
     * @return mixed|string
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     * @throws Exception
     */
    public function dynamic_property_callback_max_capacity_down($p_row)
    {
        /**
         * @var $l_dao        isys_cmdb_dao_category_g_wan
         */
        $l_dao = isys_factory_cmdb_category_dao::get_instance('isys_cmdb_dao_category_g_wan', isys_application::instance()->container->get('database'));
        $l_return = $l_dao->dynamic_property_callback_capacity_helper(
            (isset($p_row['isys_catg_wan_list__id']) ? $p_row['isys_catg_wan_list__id'] : null),
            (isset($p_row['isys_obj__id']) ? $p_row['isys_obj__id'] : null),
            'isys_catg_wan_list__max_capacity_down'
        );

        return $l_return;
    }

    /**
     * Create a new entity.
     *
     * @param   array $p_data
     *
     * @return  mixed
     * @throws Exception
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function create_data($p_data)
    {
        $p_data['capacity_up'] = isys_convert::speed_wan($p_data['capacity_up'], $p_data['capacity_up_unit']);
        $p_data['capacity_down'] = isys_convert::speed_wan($p_data['capacity_down'], $p_data['capacity_down_unit']);
        $p_data['max_capacity_up'] = isys_convert::speed_wan($p_data['max_capacity_up'], $p_data['max_capacity_up_unit']);
        $p_data['max_capacity_down'] = isys_convert::speed_wan($p_data['max_capacity_down'], $p_data['max_capacity_down_unit']);

        $l_result = parent::create_data($p_data);

        $l_router = null;
        $l_net = null;

        // If the result is not false, we connect the routers and nets.
        if ($l_result && is_numeric($l_result)) {
            if (isset($p_data['router'])) {
                $l_router = $p_data['router'];
            }

            if (isset($p_data['net'])) {
                $l_net = $p_data['net'];
            }

            $this->assign_router_net($l_result, $l_router, $l_net);
        }

        return $l_result;
    }

    /**
     * Updates existing entity.
     *
     * @param   integer $p_category_data_id
     * @param   array   $p_data
     *
     * @return  boolean
     * @throws Exception
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function save_data($p_category_data_id, $p_data)
    {
        $p_data['capacity_up'] = isys_convert::speed_wan($p_data['capacity_up'], $p_data['capacity_up_unit']);
        $p_data['capacity_down'] = isys_convert::speed_wan($p_data['capacity_down'], $p_data['capacity_down_unit']);
        $p_data['max_capacity_up'] = isys_convert::speed_wan($p_data['max_capacity_up'], $p_data['max_capacity_up_unit']);
        $p_data['max_capacity_down'] = isys_convert::speed_wan($p_data['max_capacity_down'], $p_data['max_capacity_down_unit']);

        $l_result = parent::save_data($p_category_data_id, $p_data);
        $l_router = null;
        $l_net = null;

        // If the result is not false, we connect the routers and nets.
        if ($l_result) {
            if (isset($p_data['router'])) {
                $l_router = $p_data['router'];
            }

            if (isset($p_data['net'])) {
                $l_net = $p_data['net'];
            }

            $this->assign_router_net($p_category_data_id, $l_router, $l_net);
        }

        return $l_result;
    }

    /**
     * Method for retrieving all connected routers.
     *
     * @param   integer $p_cat_entry_id
     *
     * @return isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_connected_routers($p_cat_entry_id)
    {
        $l_sql = 'SELECT * FROM isys_catg_wan_list_2_router
			LEFT JOIN isys_obj ON isys_obj__id = isys_catg_wan_list_2_router__isys_obj__id
			LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_catg_wan_list_2_router__isys_catg_wan_list__id = ' . $this->convert_sql_id($p_cat_entry_id) . '
			AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Method for setting certain routers to a given WAN-category entry.
     *
     * @param   integer $p_cat_entry_id
     * @param   mixed   $p_routers
     *
     * @return  boolean
     * @throws  isys_exception_dao
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_connect_routers($p_cat_entry_id, $p_routers)
    {
        if (!is_numeric($p_cat_entry_id) || !$p_cat_entry_id) {
            return true;
        }

        if (!is_array($p_routers)) {
            $p_routers = [$p_routers];
        }

        // Remove all assignments first
        $this->update('DELETE FROM isys_catg_wan_list_2_router WHERE isys_catg_wan_list_2_router__isys_catg_wan_list__id = ' . $this->convert_sql_id($p_cat_entry_id) . ';');

        if (count($p_routers)) {
            // Assign all selected routers to the wan
            $l_items = [];
            $l_sql = 'INSERT INTO isys_catg_wan_list_2_router (isys_catg_wan_list_2_router__isys_catg_wan_list__id, isys_catg_wan_list_2_router__isys_obj__id) VALUES ';
            foreach ($p_routers as $l_router) {
                $l_items[] = '(' . $this->convert_sql_id($p_cat_entry_id) . ', ' . $this->convert_sql_id($l_router) . ')';
            }

            return $this->update($l_sql . implode(', ', $l_items) . ';') && $this->apply_update();
        }

        return true;
    }

    /**
     * Method for retrieving all connected nets.
     *
     * @param   integer $p_cat_entry_id
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_connected_nets($p_cat_entry_id)
    {
        $l_sql = 'SELECT * FROM isys_catg_wan_list_2_net
			LEFT JOIN isys_obj ON isys_obj__id = isys_catg_wan_list_2_net__isys_obj__id
			LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_catg_wan_list_2_net__isys_catg_wan_list__id = ' . $this->convert_sql_id($p_cat_entry_id) . '
			AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Method for setting certain nets to a given WAN-category entry.
     *
     * @param   integer $p_cat_entry_id
     * @param   mixed   $p_nets
     *
     * @return  boolean
     * @throws  isys_exception_dao
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_connect_nets($p_cat_entry_id, $p_nets)
    {
        if (!is_numeric($p_cat_entry_id) || !$p_cat_entry_id) {
            return true;
        }

        if (!is_array($p_nets)) {
            $p_nets = [$p_nets];
        }

        $wanObjectId = $this->retrieve(
            'SELECT isys_catg_wan_list__isys_obj__id FROM isys_catg_wan_list 
                WHERE isys_catg_wan_list__id = ' . $this->convert_sql_id($p_cat_entry_id) . ';'
        )->get_row_value('isys_catg_wan_list__isys_obj__id');

        $currentNets = $this->retrieve(
            'SELECT * FROM isys_catg_wan_list_2_net 
                WHERE isys_catg_wan_list_2_net__isys_catg_wan_list__id = ' . $this->convert_sql_id($p_cat_entry_id) . ';'
        )->__as_array();

        $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());

        // find nets that have to be deleted
        $currentNetsIds = [];
        foreach ($currentNets as $currentNet) {
            $currentNetsIds[] = $currentNet['isys_catg_wan_list_2_net__isys_obj__id'];
            if (!in_array($currentNet['isys_catg_wan_list_2_net__isys_obj__id'], $p_nets)) {
                $deleteSql = 'DELETE FROM isys_catg_wan_list_2_net 
                    WHERE isys_catg_wan_list_2_net__id = ' . $this->convert_sql_id($currentNet['isys_catg_wan_list_2_net__id']) . ';';

                if ($this->update($deleteSql) && $this->apply_update()) {
                    $l_relation_dao->delete_relation($currentNet['isys_catg_wan_list_2_net__isys_catg_relation_list__id']);
                }
            }
        }

        // find new Nets to add
        $newNetsIds = [];
        foreach ($p_nets as $l_net) {
            if (!in_array($l_net, $currentNetsIds)) {
                $newNetsIds[] = $l_net;
            }
        }

        foreach ($newNetsIds as $newNetId) {
            $insertSql = 'INSERT INTO isys_catg_wan_list_2_net '.
                '(isys_catg_wan_list_2_net__isys_catg_wan_list__id, isys_catg_wan_list_2_net__isys_obj__id) VALUES ' .
                '(' . $this->convert_sql_id($p_cat_entry_id) . ', ' . $this->convert_sql_id($newNetId) . ')';

            if ($this->update($insertSql) && $this->apply_update()) {
                $l_relation_dao->handle_relation(
                    $this->get_last_insert_id(),
                    "isys_catg_wan_list_2_net",
                    defined_or_default('C__RELATION_TYPE__WAN_CONNECTION'),
                    null,
                    $wanObjectId,
                    $newNetId
                );
            }
        }

        return true;
    }

    /**
     * Helper method which assigns routers and nets to the WAN.
     *
     * @param integer $p_id
     * @param mixed   $p_router
     * @param mixed   $p_net
     *
     * @return $this
     * @throws Exception
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function assign_router_net($p_id, $p_router = null, $p_net = null)
    {
        if ($p_router !== null) {
            $l_router = [];
            if (isys_format_json::is_json_array($p_router)) {
                $l_router = array_filter(isys_format_json::decode($p_router));
            } elseif (is_array($p_router)) {
                $l_router = $p_router;
            }

            $this->set_connect_routers($p_id, $l_router);
        }

        if ($p_net !== null) {
            $l_net = [];
            if (isys_format_json::is_json_array($p_net)) {
                $l_net = array_filter(isys_format_json::decode($p_net));
            } elseif (is_array($p_net)) {
                $l_net = $p_net;
            }

            $this->set_connect_nets($p_id, $l_net);
        }

        return $this;
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed    Returns category data identifier (int) on success, true (bool) if nothing has to be done, otherwise false.
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @throws isys_exception_validation
     */
    public function sync($p_category_data, $p_object_id, $p_status)
    {
        // If we are in "create" mode (or have no "data_id") simply try to retrieve it or create a new entry.
        if ($p_status == isys_import_handler_cmdb::C__CREATE || !isset($p_category_data['data_id']) || !$p_category_data['data_id']) {
            $l_result = $this->retrieve('SELECT isys_catg_wan_list__id FROM isys_catg_wan_list WHERE isys_catg_wan_list__isys_obj__id = ' .
                $this->convert_sql_id($p_object_id) . ';');

            if (!is_countable($l_result) && count($l_result)) {
                $p_category_data['data_id'] = $this->create_connector('isys_catg_wan_list', $p_object_id);
            } else {
                $p_category_data['data_id'] = $l_result->get_row_value('isys_catg_wan_list__id');
            }

            $p_status = isys_import_handler_cmdb::C__UPDATE;
        }

        // Process assigned "router" and "net" objects.
        if ($p_category_data['data_id'] > 0) {
            if (isset($p_category_data[isys_import_handler_cmdb::C__PROPERTIES]['router'])) {
                $this->set_connect_routers(
                    $p_category_data['data_id'],
                    ($p_category_data[isys_import_handler_cmdb::C__PROPERTIES]['router'][C__DATA__VALUE] ?: [])
                );
                unset($p_category_data[isys_import_handler_cmdb::C__PROPERTIES]['router']);
            }

            if (isset($p_category_data[isys_import_handler_cmdb::C__PROPERTIES]['net'])) {
                $this->set_connect_nets(
                    $p_category_data['data_id'],
                    ($p_category_data[isys_import_handler_cmdb::C__PROPERTIES]['net'][C__DATA__VALUE] ?: [])
                );
                unset($p_category_data[isys_import_handler_cmdb::C__PROPERTIES]['net']);
            }
        }

        // Leave the rest to the generic sync method.
        return parent::sync($p_category_data, $p_object_id, $p_status);
    }
}
