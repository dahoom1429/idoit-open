<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: specific category for emergency power suppliers.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_eps extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'eps';

    /**
     * Category entry is purgable.
     *
     * @var  boolean
     */
    protected $m_is_purgable = true;

    /**
     * Dynamic property handling for getting the formatted autonomy time.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_property_callback_autonomy_time($p_row)
    {
        global $g_comp_database;

        if (!empty($p_row['isys_cats_eps_list__id'])) {
            $l_dao = isys_cmdb_dao_category_s_eps::instance($g_comp_database);
            $l_return = $l_dao->retrieve('SELECT CONCAT(ROUND(isys_cats_eps_list__autonomy_time / isys_unit_of_time__factor),  \' \', isys_unit_of_time__title) AS val
                            FROM isys_cats_eps_list
                            INNER JOIN isys_unit_of_time ON isys_unit_of_time__id = isys_cats_eps_list__autonomy_time__isys_unit_of_time__id
                            WHERE isys_cats_eps_list__id = ' . $l_dao->convert_sql_id($p_row['isys_cats_eps_list__id']))
                ->get_row_value('val');
            if (!empty($l_return)) {
                return $l_return;
            }
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Dynamic property handling for getting the formatted warmup time.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_property_callback_warmup_time($p_row)
    {
        global $g_comp_database;

        if (!empty($p_row['isys_cats_eps_list__id'])) {
            $l_dao = isys_cmdb_dao_category_s_eps::instance($g_comp_database);
            $l_return = $l_dao->retrieve('SELECT CONCAT(ROUND(isys_cats_eps_list__warmup_time / isys_unit_of_time__factor), \' \', isys_unit_of_time__title) AS val
                            FROM isys_cats_eps_list
                            INNER JOIN isys_unit_of_time ON isys_unit_of_time__id = isys_cats_eps_list__warmup_time__isys_unit_of_time__id
                            WHERE isys_cats_eps_list__id = ' . $l_dao->convert_sql_id($p_row['isys_cats_eps_list__id']))
                ->get_row_value('val');
            if (!empty($l_return)) {
                return $l_return;
            }
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Dynamic property handling for getting the formatted fuel tank.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_property_callback_fuel_tank($p_row)
    {
        global $g_comp_database;

        if (!empty($p_row['isys_cats_eps_list__id'])) {
            $l_dao = isys_cmdb_dao_category_s_eps::instance($g_comp_database);
            $l_return = $l_dao->retrieve('SELECT CONCAT(ROUND(isys_cats_eps_list__fuel_tank / isys_volume_unit__factor), \' \', isys_volume_unit__title) AS val
                            FROM isys_cats_eps_list
                            INNER JOIN isys_volume_unit ON isys_volume_unit__id = isys_cats_eps_list__isys_volume_unit__id
                            WHERE isys_cats_eps_list__id = ' . $l_dao->convert_sql_id($p_row['isys_cats_eps_list__id']))
                ->get_row_value('val');
            if (!empty($l_return)) {
                return $l_return;
            }
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

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
        $p_data['fuel_tank'] = isys_convert::volume($p_data['fuel_tank'], $p_data['volume_unit']);
        $p_data['warmup_time'] = isys_convert::time($p_data['warmup_time'], $p_data['warmup_time_unit']);
        $p_data['autonomy_time'] = isys_convert::time($p_data['autonomy_time'], $p_data['autonomy_time_unit']);

        return parent::create_data($p_data);
    }

    /**
     * Method for retrieving the dynamic properties of this dao.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function dynamic_properties()
    {
        return [
            '_autonomy_time' => new DynamicProperty(
                'LC__CMDB__CATS__EPS__AUTONOMY_TIME',
                'isys_cats_eps_list__id',
                'isys_cats_eps_list',
                [
                    $this,
                    'dynamic_property_callback_autonomy_time'
                ]
            ),
            '_warmup_time' => new DynamicProperty(
                'LC__CMDB__CATS__EPS__WARMUP_TIME',
                'isys_cats_eps_list__id',
                'isys_cats_eps_list',
                [
                    $this,
                    'dynamic_property_callback_warmup_time'
                ]
            ),
            '_fuel_tank' => new DynamicProperty(
                'LC__CMDB__CATS__EPS__FUEL_TANK',
                'isys_cats_eps_list__id',
                'isys_cats_eps_list',
                [
                    $this,
                    'dynamic_property_callback_fuel_tank'
                ]
            ),
        ];
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @todo    Dynamic properties for "warmup_time", "fuel_tank" and "autonomy_time".
     */
    protected function properties()
    {
        return [
            'type' => (new DialogPlusProperty(
                'C__CMDB__CATS__POBJ_TYPE',
                'LC__CMDB__CATS__POBJ_TYPE',
                'isys_cats_eps_list__isys_cats_eps_type__id',
                'isys_cats_eps_list',
                'isys_cats_eps_type'
            ))->mergePropertyUiParams([
                'p_strClass' => 'input-small'
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false
            ]),
            'warmup_time'        => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__EPS__WARMUP_TIME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Warmup time'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_eps_list__warmup_time',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(ROUND(isys_cats_eps_list__warmup_time / isys_unit_of_time__factor), \' \', isys_unit_of_time__title)
                            FROM isys_cats_eps_list
                            INNER JOIN isys_unit_of_time ON isys_unit_of_time__id = isys_cats_eps_list__warmup_time__isys_unit_of_time__id',
                        'isys_cats_eps_list',
                        'isys_cats_eps_list__id',
                        'isys_cats_eps_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_eps_list', 'LEFT', 'isys_cats_eps_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_unit_of_time',
                            'LEFT',
                            'isys_cats_eps_list__warmup_time__isys_unit_of_time__id',
                            'isys_unit_of_time__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__EPS__WARMUP_TIME',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-mini'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['time']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'warmup_time_unit'
                ]
            ]),
            'warmup_time_unit' => (new DialogProperty(
                'C__CMDB__CATS__EPS__WARMUP_TIME_UNIT',
                'LC__CMDB__CATG__UNIT',
                'isys_cats_eps_list__warmup_time__isys_unit_of_time__id',
                'isys_cats_eps_list',
                'isys_unit_of_time'
            ))->mergePropertyUiParams([
                'p_strClass' => 'input-small ml20'
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__REPORT => false
            ]),
            'fuel_tank'          => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__EPS__FUEL_TANK',
                    C__PROPERTY__INFO__DESCRIPTION => 'Fuel tank'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_eps_list__fuel_tank',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(ROUND(isys_cats_eps_list__fuel_tank / isys_volume_unit__factor), \' \', isys_volume_unit__title)
                            FROM isys_cats_eps_list
                            INNER JOIN isys_volume_unit ON isys_volume_unit__id = isys_cats_eps_list__isys_volume_unit__id',
                        'isys_cats_eps_list',
                        'isys_cats_eps_list__id',
                        'isys_cats_eps_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_eps_list', 'LEFT', 'isys_cats_eps_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__EPS__FUEL_TANK',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-mini'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['volume']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'volume_unit'
                ]
            ]),
            'volume_unit' => (new DialogProperty(
                'C__CMDB__CATS__EPS__FUEL_TANK_UNIT',
                'LC__CMDB__CATG__UNIT',
                'isys_cats_eps_list__isys_volume_unit__id',
                'isys_cats_eps_list',
                'isys_volume_unit'
            ))->mergePropertyUiParams([
                'p_strClass' => 'input-small ml20'
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__REPORT => false
            ]),
            'autonomy_time'      => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__EPS__AUTONOMY_TIME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Autonomy time'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_eps_list__autonomy_time',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(ROUND(isys_cats_eps_list__autonomy_time / isys_unit_of_time__factor),  \' \', isys_unit_of_time__title)
                            FROM isys_cats_eps_list
                            INNER JOIN isys_unit_of_time ON isys_unit_of_time__id = isys_cats_eps_list__autonomy_time__isys_unit_of_time__id',
                        'isys_cats_eps_list',
                        'isys_cats_eps_list__id',
                        'isys_cats_eps_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_eps_list', 'LEFT', 'isys_cats_eps_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_unit_of_time',
                            'LEFT',
                            'isys_cats_eps_list__autonomy_time__isys_unit_of_time__id',
                            'isys_unit_of_time__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__EPS__AUTONOMY_TIME',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-mini'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['time']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'autonomy_time_unit'
                ]
            ]),
            'autonomy_time_unit' => (new DialogProperty(
                'C__CMDB__CATS__EPS__AUTONOMY_TIME_UNIT',
                'LC__CMDB__CATG__UNIT',
                'isys_cats_eps_list__autonomy_time__isys_unit_of_time__id',
                'isys_cats_eps_list',
                'isys_unit_of_time'
            ))->mergePropertyUiParams([
                'p_strClass' => 'input-small ml20'
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__REPORT => false
            ]),
            'description'        => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_eps_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__EPS', 'C__CATS__EPS')
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
        $p_data['fuel_tank'] = isys_convert::volume($p_data['fuel_tank'], $p_data['volume_unit']);
        $p_data['warmup_time'] = isys_convert::time($p_data['warmup_time'], $p_data['warmup_time_unit']);
        $p_data['autonomy_time'] = isys_convert::time($p_data['autonomy_time'], $p_data['autonomy_time_unit']);

        return parent::save_data($p_category_data_id, $p_data);
    }
}
