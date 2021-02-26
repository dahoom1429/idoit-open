<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Type\DialogYesNoProperty;
use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: specific category for monitors.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_monitor extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'monitor';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Dynamic property handling for getting the formatted CPU data.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_property_callback_size($p_row)
    {
        global $g_comp_database;

        $objId = ($p_row['isys_cats_monitor_list__isys_obj__id'] ?: ($p_row['isys_obj__id'] ?: null));

        if ($objId === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        $l_monitor_row = isys_cmdb_dao_category_s_monitor::instance($g_comp_database)
            ->get_data(null, $objId)
            ->get_row();

        return isys_convert::measure($l_monitor_row['isys_cats_monitor_list__display'], $l_monitor_row['isys_depth_unit__const'], C__CONVERT_DIRECTION__BACKWARD) . ' ' .
            isys_application::instance()->container->get('language')
                ->get($l_monitor_row['isys_depth_unit__title']);
    }

    /**
     * Creates new entity.
     *
     * @param   array $p_data Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  mixed  Returns created entity's identifier (int) or false (bool).
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    public function create_data($p_data)
    {
        $p_data['size'] = isys_convert::measure($p_data['size'], $p_data['size_unit']);

        return parent::create_data($p_data);
    }

    /**
     * Abstract method for retrieving the dynamic properties of this category dao.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_properties()
    {
        return [
            '_size' => new DynamicProperty(
                'LC__CMDB__CATS__MONITOR_DISPLAY',
                'isys_cats_monitor_list__isys_obj__id',
                'isys_cats_monitor_list',
                [
                    $this,
                    'dynamic_property_callback_size'
                ]
            ),
        ];
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'size'        => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__MONITOR_DISPLAY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Display'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_monitor_list__display',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(' . $this->m_db->format_number('isys_cats_monitor_list__display / isys_depth_unit__factor') . ', \' \', isys_depth_unit__title)
                            FROM isys_cats_monitor_list
                            INNER JOIN isys_depth_unit ON isys_depth_unit__id = isys_cats_monitor_list__isys_depth_unit__id',
                        'isys_cats_monitor_list',
                        'isys_cats_monitor_list__id',
                        'isys_cats_monitor_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_monitor_list', 'LEFT', 'isys_cats_monitor_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_depth_unit',
                            'LEFT',
                            'isys_cats_monitor_list__isys_depth_unit__id',
                            'isys_depth_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__MONITOR_DISPLAY',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-medium',
                    ],
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['measure']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'size_unit'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'size_unit' => (new DialogProperty(
                'C__CATS__MONITOR_UNIT',
                'LC__CMDB__CATS__MONITOR_UNIT',
                'isys_cats_monitor_list__isys_depth_unit__id',
                'isys_cats_monitor_list',
                'isys_depth_unit'
            ))->mergePropertyUiParams([
                'p_strClass' => 'input-mini'
            ]),
            'type' => (new DialogPlusProperty(
                'C__CATS__MONITOR_TYPE',
                'LC__CMDB__CATS__MONITOR_TYPE',
                'isys_cats_monitor_list__isys_monitor_type__id',
                'isys_cats_monitor_list',
                'isys_monitor_type'
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false
            ]),
            'resolution' => (new DialogPlusProperty(
                'C__CATS__MONITOR_RESOLUTION',
                'LC__CMDB__CATS__MONITOR_RESOLUTION',
                'isys_cats_monitor_list__isys_monitor_resolution__id',
                'isys_cats_monitor_list',
                'isys_monitor_resolution'
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false
            ]),
            'pivot' => new DialogYesNoProperty(
                'C__CATS__MONITOR_PIVOT',
                'LC__CMDB__CATS__MONITOR_PIVOT',
                'isys_cats_monitor_list__pivot',
                'isys_cats_monitor_list'
            ),
            'speaker' => new DialogYesNoProperty(
                'C__CATS__MONITOR_SPEAKER',
                'LC__CMDB__CATS__MONITOR_SPEAKER',
                'isys_cats_monitor_list__speaker',
                'isys_cats_monitor_list'
            ),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_monitor_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__MONITOR', 'C__CATS__MONITOR')
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
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    public function save_data($p_category_data_id, $p_data)
    {
        $p_data['size'] = isys_convert::measure($p_data['size'], $p_data['size_unit']);

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
        $_POST["C__CATS__MONITOR_DISPLAY"] = isys_helper::filter_number($_POST["C__CATS__MONITOR_DISPLAY"]);
        $_POST = parent::sanitize_post_data();

        return $_POST;
    }
}
