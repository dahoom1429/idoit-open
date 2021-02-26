<?php

use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DialogProperty;

/**
 * i-doit
 *
 * DAO: specific category for air conditioners.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_ac extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'ac';

    /**
     * Category entry is purgable
     *
     * @var  boolean
     */
    protected $m_is_purgable = true;

    /**
     * Creates new entity.
     *
     * @param   array $p_data Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  mixed  Returns created entity's identifier (int) or false (bool).
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function create_data($p_data)
    {
        $p_data['height'] = isys_convert::measure($p_data['height'], $p_data['dimension_unit']) ?: 0;
        $p_data['width'] = isys_convert::measure($p_data['width'], $p_data['dimension_unit']) ?: 0;
        $p_data['depth'] = isys_convert::measure($p_data['depth'], $p_data['dimension_unit']) ?: 0;
        $p_data['capacity'] = isys_convert::watt($p_data['capacity'], $p_data['capacity_unit']);

        return parent::create_data($p_data);
    }

    /**
     * Method for returning the properties.
     *
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     * @return  array
     */
    protected function properties()
    {
        $decimalSeparator = isys_application::instance()->container->get('locales')->get_user_settings(LC_NUMERIC);

        return [
            'type' => new DialogPlusProperty(
                'C__CATS__AC_TYPE',
                'LC__CATS__AC_TYPE',
                'isys_cats_ac_list__isys_ac_type__id',
                'isys_cats_ac_list',
                'isys_ac_type'
            ),
            'threshold'         => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE => 'LC__CATS__AC_THRESHOLD'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__AC_THRESHOLD',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-medium',
                        'p_strTable' => 'isys_ac_type',
                    ]
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_cats_ac_list__threshold',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_ac_type',
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_cats_ac_list__threshold, \' \', isys_temp_unit__title)
                            FROM isys_cats_ac_list
                            INNER JOIN isys_temp_unit ON isys_temp_unit__id = isys_cats_ac_list__isys_temp_unit__id',
                        'isys_cats_ac_list',
                        'isys_cats_ac_list__id',
                        'isys_cats_ac_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_ac_list', 'LEFT', 'isys_cats_ac_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_temp_unit', 'LEFT', 'isys_cats_ac_list__isys_temp_unit__id', 'isys_temp_unit__id')
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__UNIT => 'threshold_unit',
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['temperature']
                    ],
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'threshold_unit' => (new DialogProperty(
                'C__CATS__AC_THRESHOLD_UNIT',
                'LC__CMDB_CATG__MEMORY_UNIT',
                'isys_cats_ac_list__isys_temp_unit__id',
                'isys_cats_ac_list',
                'isys_temp_unit'
            ))->mergePropertyUiParams([
                'p_strClass' => 'input-mini'
            ]),
            'capacity_unit' => (new DialogProperty(
                'C__CATS__AC_REFRIGERATING_CAPACITY_UNIT',
                'isys_capacity_unit',
                'isys_cats_ac_list__isys_ac_refrigerating_capacity_unit__id',
                'isys_cats_ac_list',
                'isys_ac_refrigerating_capacity_unit'
            ))->mergePropertyUiParams([
                'p_strClass' => 'input-mini'
            ]),
            'capacity'          => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE => 'LC__CATS__AC_REFRIGERATING_CAPACITY'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__AC_REFRIGERATING_CAPACITY',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-medium',
                    ]
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_ac_list__capacity',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT CONCAT(ROUND(isys_cats_ac_list__capacity / isys_ac_refrigerating_capacity_unit__factor), \' \', isys_ac_refrigerating_capacity_unit__title)
                            FROM isys_cats_ac_list
                            INNER JOIN isys_ac_refrigerating_capacity_unit ON isys_ac_refrigerating_capacity_unit__id = isys_cats_ac_list__isys_ac_refrigerating_capacity_unit__id
                            ', 'isys_cats_ac_list', 'isys_cats_ac_list__id', 'isys_cats_ac_list__isys_obj__id'),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_ac_list', 'LEFT', 'isys_cats_ac_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_ac_refrigerating_capacity_unit',
                            'LEFT',
                            'isys_cats_ac_list__isys_ac_refrigerating_capacity_unit__id',
                            'isys_ac_refrigerating_capacity_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['watt']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'capacity_unit'
                ]
            ]),
            'air_quantity'      => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATS__AC_AIR_QUANTITY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Air quantity'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_ac_list__air_quantity',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT CONCAT(isys_cats_ac_list__air_quantity, \' \', isys_ac_air_quantity_unit__title)
                            FROM isys_cats_ac_list
                            INNER JOIN isys_ac_air_quantity_unit ON isys_ac_air_quantity_unit__id = isys_cats_ac_list__isys_ac_air_quantity_unit__id
                            ', 'isys_cats_ac_list', 'isys_cats_ac_list__id', 'isys_cats_ac_list__isys_obj__id'),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_ac_list', 'LEFT', 'isys_cats_ac_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_ac_air_quantity_unit',
                            'LEFT',
                            'isys_cats_ac_list__isys_ac_air_quantity_unit__id',
                            'isys_ac_air_quantity_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__AC_AIR_QUANTITY',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-medium'
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['volume']
                    ],
                    C__PROPERTY__FORMAT__UNIT => 'air_quantity_unit'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'air_quantity_unit' => (new DialogProperty(
                'C__CATS__AC_AIR_QUANTITY_UNIT',
                'isys_volume_unit',
                'isys_cats_ac_list__isys_ac_air_quantity_unit__id',
                'isys_cats_ac_list',
                'isys_ac_air_quantity_unit'
            ))->mergePropertyUiParams([
                'p_strClass' => 'input-mini'
            ]),
            'width'             => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__RACK_WIDTH',
                    C__PROPERTY__INFO__DESCRIPTION => 'Width'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_ac_list__width',

                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(' . $this->m_db->format_number('isys_cats_ac_list__width / isys_depth_unit__factor') . ', \' \', isys_depth_unit__title)
                            FROM isys_cats_ac_list
                            INNER JOIN isys_depth_unit ON isys_depth_unit__id = isys_cats_ac_list__isys_depth_unit__id',
                        'isys_cats_ac_list',
                        'isys_cats_ac_list__id',
                        'isys_cats_ac_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_ac_list', 'LEFT', 'isys_cats_ac_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_depth_unit', 'LEFT', 'isys_cats_ac_list__isys_depth_unit__id', 'isys_depth_unit__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__AC_DIMENSIONS_WIDTH',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass'        => 'input-mini',
                        'p_bInfoIconSpacer' => 0,
                        'disableInputGroup' => true
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['measure']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'dimension_unit'
                ]
            ]),
            'height'            => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__RACK_HEIGHT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Height'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_ac_list__height',

                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(' . $this->m_db->format_number('isys_cats_ac_list__height / isys_depth_unit__factor') . ', \' \', isys_depth_unit__title)
                            FROM isys_cats_ac_list
                            INNER JOIN isys_depth_unit ON isys_depth_unit__id = isys_cats_ac_list__isys_depth_unit__id',
                        'isys_cats_ac_list',
                        'isys_cats_ac_list__id',
                        'isys_cats_ac_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_ac_list', 'LEFT', 'isys_cats_ac_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_depth_unit', 'LEFT', 'isys_cats_ac_list__isys_depth_unit__id', 'isys_depth_unit__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__AC_DIMENSIONS_HEIGHT',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass'        => 'input-mini',
                        'p_bInfoIconSpacer' => 0,
                        'disableInputGroup' => true
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['measure']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'dimension_unit'
                ]
            ]),
            'depth'             => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__RACK_DEPTH',
                    C__PROPERTY__INFO__DESCRIPTION => 'Depth'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_ac_list__depth',

                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(' . $this->m_db->format_number('isys_cats_ac_list__depth / isys_depth_unit__factor') . ', \' \', isys_depth_unit__title)
                            FROM isys_cats_ac_list
                            INNER JOIN isys_depth_unit ON isys_depth_unit__id = isys_cats_ac_list__isys_depth_unit__id',
                        'isys_cats_ac_list',
                        'isys_cats_ac_list__id',
                        'isys_cats_ac_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_ac_list', 'LEFT', 'isys_cats_ac_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_depth_unit', 'LEFT', 'isys_cats_ac_list__isys_depth_unit__id', 'isys_depth_unit__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__AC_DIMENSIONS_DEPTH',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass'        => 'input-mini',
                        'p_bInfoIconSpacer' => 0,
                        'disableInputGroup' => true
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['measure']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'dimension_unit'
                ]
            ]),
            'dimension_unit' => (new DialogProperty(
                'C__CATS__AC_DIMENSIONS_UNIT',
                'isys_depth_unit',
                'isys_cats_ac_list__isys_depth_unit__id',
                'isys_cats_ac_list',
                'isys_depth_unit'
            ))->mergePropertyUiParams([
                'p_strClass'        => 'input-mini',
                'p_bDbFieldNN'      => 1,
                'disableInputGroup' => true
            ])->setPropertyUiDefault(defined_or_default('C__DEPTH_UNIT__INCH')),
            'description'       => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_ac_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__AC', 'C__CATS__AC')
                ]
            ])

        ];
    }

    /**
     * Updates existing entity.
     *
     * @param   integer $p_category_data_id Entity's identifier
     * @param   array   $p_data             Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_data($p_category_data_id, $p_data)
    {
        $p_data['height'] = isys_convert::measure($p_data['height'], $p_data['dimension_unit']);
        $p_data['width'] = isys_convert::measure($p_data['width'], $p_data['dimension_unit']);
        $p_data['depth'] = isys_convert::measure($p_data['depth'], $p_data['dimension_unit']);
        $p_data['capacity'] = isys_convert::watt($p_data['capacity'], $p_data['capacity_unit']);

        return parent::save_data($p_category_data_id, $p_data);
    }

    /**
     * Hack for ID-7135 to prevent the warning
     *
     * @return array
     * @throws isys_exception_ui
     */
    public function sanitize_post_data()
    {
        $postCache = $_POST;
        $_POST["C__CATS__AC_DIMENSIONS_DEPTH"] = isys_helper::filter_number($_POST["C__CATS__AC_DIMENSIONS_DEPTH"]);
        $_POST["C__CATS__AC_DIMENSIONS_HEIGHT"] = isys_helper::filter_number($_POST["C__CATS__AC_DIMENSIONS_HEIGHT"]);
        $_POST["C__CATS__AC_DIMENSIONS_WIDTH"] = isys_helper::filter_number($_POST["C__CATS__AC_DIMENSIONS_WIDTH"]);
        $_POST["C__CATS__AC_AIR_QUANTITY"] = isys_helper::filter_number($_POST["C__CATS__AC_AIR_QUANTITY"]);
        $_POST = parent::sanitize_post_data();

        return $_POST;
    }
}
