<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DialogProperty;

/**
 * i-doit
 *
 * DAO: global category for form factors
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_formfactor extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'formfactor';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__FORMFACTOR';

    /**
     * Category entry is purgable
     *
     * @var bool
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
        /**
         * Check whether call coming from object which is in birth stage
         *
         * @see ID-6396
         */
        if ($this->isCreationInOverview()) {
            // Setup keys to check
            $keysToCheck = [
                'formfactor'   => '-1',
                'rackunits'    => '1',
                'unit'         => '3',
                'width'        => '',
                'height'       => '',
                'depth'        => '',
                'weight'       => '',
                'weight_unit'  => '1',
                'description'  => '',
                'isys_obj__id' => $p_data['isys_obj__id'],
                'status'       => $p_data['status']
            ];

            // Calculate difference between data and untouched data
            $difference = [];
            $keys = array_unique(array_keys($keysToCheck) + array_keys($p_data));
            foreach ($keys as $key) {
                if (!array_key_exists($key, $p_data)
                    || !array_key_exists($key, $keysToCheck)
                    || $p_data[$key] !== $keysToCheck[$key]
                ) {
                    $difference[] = $key;
                }
            }

            // Check whether we get totally untouched data
            if (is_countable($difference) && count($difference) === 0) {
                // Skip category data creation
                return true;
            }
        }

        // ID-3292 - Check if the current object contains the "rack" category and create an entry, if none exists.
        if (defined('C__CATS__ENCLOSURE') && $this->objtype_is_cats_assigned($this->get_objTypeID($p_data['isys_obj__id']), C__CATS__ENCLOSURE) && class_exists('isys_cmdb_dao_category_s_enclosure')) {
            $l_dao = isys_cmdb_dao_category_s_enclosure::instance($this->m_db);

            $l_row = $l_dao->get_data(null, $p_data['isys_obj__id'])
                ->get_row();

            if ($l_row === null || !is_array($l_row)) {
                $l_dao->create_data([
                    'isys_obj__id'         => $p_data['isys_obj__id'],
                    'vertical_slots_front' => 0,
                    'vertical_slots_rear'  => 0,
                    'slot_sorting'         => 'asc',
                    'status' => C__RECORD_STATUS__NORMAL
                ]);
            }
        }

        $p_data['width'] = (isset($p_data['width']) && $p_data['width']) ? isys_convert::measure($p_data['width'], $p_data['unit']) : '';
        $p_data['height'] = (isset($p_data['height']) && $p_data['height']) ? isys_convert::measure($p_data['height'], $p_data['unit']) : '';
        $p_data['depth'] = (isset($p_data['depth']) && $p_data['depth']) ? isys_convert::measure($p_data['depth'], $p_data['unit']) : '';
        $p_data['weight'] = (isset($p_data['weight']) && $p_data['weight']) ? isys_convert::weight($p_data['weight'], $p_data['weight_unit']) : '';

        return parent::create_data($p_data);
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
        $l_data_join = [
            idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_formfactor_list', 'LEFT', 'isys_catg_formfactor_list__isys_obj__id', 'isys_obj__id'),
            idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_depth_unit', 'LEFT', 'isys_catg_formfactor_list__isys_depth_unit__id', 'isys_depth_unit__id')
        ];

        return [
            'formfactor' => (new DialogPlusProperty(
                'C__CATG__FORMFACTOR_TYPE',
                'LC__CMDB__CATG__FORMFACTOR',
                'isys_catg_formfactor_list__isys_catg_formfactor_type__id',
                'isys_catg_formfactor_list',
                'isys_catg_formfactor_type'
            ))->mergePropertyUiParams(
                [
                    'p_strClass' => 'input-small'
                ]
            ),
            'rackunits'   => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__RACKUNITS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Rack units'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_formfactor_list__rackunits'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__FORMFACTOR_RACKUNITS',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-mini',
                        'allowZero'  => true
                    ]
                ]
            ]),
            'unit' => (new DialogProperty(
                'C__CATG__FORMFACTOR_INSTALLATION_DEPTH_UNIT',
                'LC__CMDB__CATG__FORMFACTOR_INSTALLATION_DIMENSION_UNIT',
                'isys_catg_formfactor_list__isys_depth_unit__id',
                'isys_catg_formfactor_list',
                'isys_depth_unit'
            ))->mergePropertyUiParams(
                [
                    'p_strClass' => 'input-mini',
                    'p_bDbFieldNN' => true
                ]
            )->setPropertyUiDefault(defined_or_default('C__DEPTH_UNIT__INCH')),
            'width'       => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO   => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__FORMFACTOR_INSTALLATION_WIDTH',
                    C__PROPERTY__INFO__DESCRIPTION => 'Width'
                ],
                C__PROPERTY__DATA   => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_formfactor_list__installation_width',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(' . $this->m_db->format_number('isys_catg_formfactor_list__installation_width / isys_depth_unit__factor') . ', \' \', isys_depth_unit__title)
                            FROM isys_catg_formfactor_list
                            INNER JOIN isys_depth_unit ON isys_depth_unit__id = isys_catg_formfactor_list__isys_depth_unit__id',
                        'isys_catg_formfactor_list',
                        'isys_catg_formfactor_list__id',
                        'isys_catg_formfactor_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => $l_data_join
                ],
                C__PROPERTY__UI     => [
                    C__PROPERTY__UI__ID => 'C__CATG__FORMFACTOR_INSTALLATION_WIDTH',
                ],
                C__PROPERTY__FORMAT => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['measure']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'unit'
                ]
            ]),
            'height'      => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO   => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__FORMFACTOR_INSTALLATION_HEIGHT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Height'
                ],
                C__PROPERTY__DATA   => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_formfactor_list__installation_height',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(' . $this->m_db->format_number('isys_catg_formfactor_list__installation_height / isys_depth_unit__factor') . ', \' \', isys_depth_unit__title)
                            FROM isys_catg_formfactor_list
                            INNER JOIN isys_depth_unit ON isys_depth_unit__id = isys_catg_formfactor_list__isys_depth_unit__id',
                        'isys_catg_formfactor_list',
                        'isys_catg_formfactor_list__id',
                        'isys_catg_formfactor_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => $l_data_join,
                ],
                C__PROPERTY__UI     => [
                    C__PROPERTY__UI__ID => 'C__CATG__FORMFACTOR_INSTALLATION_HEIGHT',
                ],
                C__PROPERTY__FORMAT => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['measure']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'unit'
                ]
            ]),
            'depth'       => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO   => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__FORMFACTOR_INSTALLATION_DEPTH',
                    C__PROPERTY__INFO__DESCRIPTION => 'Depth'
                ],
                C__PROPERTY__DATA   => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_formfactor_list__installation_depth',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(' . $this->m_db->format_number('isys_catg_formfactor_list__installation_depth / isys_depth_unit__factor') . ', \' \', isys_depth_unit__title)
                            FROM isys_catg_formfactor_list
                            INNER JOIN isys_depth_unit ON isys_depth_unit__id = isys_catg_formfactor_list__isys_depth_unit__id',
                        'isys_catg_formfactor_list',
                        'isys_catg_formfactor_list__id',
                        'isys_catg_formfactor_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => $l_data_join
                ],
                C__PROPERTY__UI     => [
                    C__PROPERTY__UI__ID => 'C__CATG__FORMFACTOR_INSTALLATION_DEPTH',
                ],
                C__PROPERTY__FORMAT => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['measure']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'unit'
                ]
            ]),
            'weight'      => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO   => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__FORMFACTOR_INSTALLATION_WEIGHT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Weight'
                ],
                C__PROPERTY__DATA   => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_formfactor_list__installation_weight',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(' . $this->m_db->format_number('isys_catg_formfactor_list__installation_weight / isys_weight_unit__factor') . ', \' \', isys_weight_unit__title)
                            FROM isys_catg_formfactor_list
                            INNER JOIN isys_weight_unit ON isys_weight_unit__id = isys_catg_formfactor_list__isys_weight_unit__id',
                        'isys_catg_formfactor_list',
                        'isys_catg_formfactor_list__id',
                        'isys_catg_formfactor_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_formfactor_list',
                            'LEFT',
                            'isys_catg_formfactor_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_weight_unit',
                            'LEFT',
                            'isys_catg_formfactor_list__isys_weight_unit__id',
                            'isys_weight_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI     => [
                    C__PROPERTY__UI__ID     => 'C__CATG__FORMFACTOR_INSTALLATION_WEIGHT',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-medium'
                    ]
                ],
                C__PROPERTY__FORMAT => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['weight']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'weight_unit'
                ]
            ]),
            'weight_unit' => (new DialogProperty(
                'C__CATG__FORMFACTOR_INSTALLATION_WEIGHT_UNIT',
                'LC__CMDB__CATG__FORMFACTOR_INSTALLATION_WEIGHT_UNIT',
                'isys_catg_formfactor_list__isys_weight_unit__id',
                'isys_catg_formfactor_list',
                'isys_weight_unit'
            ))->mergePropertyProvides(
                [
                    Property::C__PROPERTY__PROVIDES__REPORT => true
                ]
            )->mergePropertyUiParams(
                [
                    'p_strClass'   => 'input-mini',
                    'p_bDbFieldNN' => 1,
                ]
            )->setPropertyUiDefault(defined_or_default('C__WEIGHT_UNIT__G')),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_formfactor_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__FORMFACTOR', 'C__CATG__FORMFACTOR')
                ]
            ])
        ];
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
        $_POST["C__CATG__FORMFACTOR_INSTALLATION_WIDTH"] = isys_helper::filter_number($_POST["C__CATG__FORMFACTOR_INSTALLATION_WIDTH"]);
        $_POST["C__CATG__FORMFACTOR_INSTALLATION_HEIGHT"] = isys_helper::filter_number($_POST["C__CATG__FORMFACTOR_INSTALLATION_HEIGHT"]);
        $_POST["C__CATG__FORMFACTOR_INSTALLATION_DEPTH"] = isys_helper::filter_number($_POST["C__CATG__FORMFACTOR_INSTALLATION_DEPTH"]);
        $_POST["C__CATG__FORMFACTOR_INSTALLATION_WEIGHT"] = isys_helper::filter_number($_POST["C__CATG__FORMFACTOR_INSTALLATION_WEIGHT"]);
        $_POST = parent::sanitize_post_data();

        return $_POST;
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
        // ID-3292 - Check if the current object contains the "rack" category and create an entry, if none exists.
        if (defined('C__CATS__ENCLOSURE') && $this->objtype_is_cats_assigned($this->get_objTypeID($p_data['isys_obj__id']), C__CATS__ENCLOSURE) && class_exists('isys_cmdb_dao_category_s_enclosure')) {
            $l_dao = isys_cmdb_dao_category_s_enclosure::instance($this->m_db);

            $l_row = $l_dao->get_data(null, $p_data['isys_obj__id'])
                ->get_row();

            if ($l_row === null || !is_array($l_row)) {
                $l_dao->create_data([
                    'isys_obj__id'         => $p_data['isys_obj__id'],
                    'vertical_slots_front' => 0,
                    'vertical_slots_rear'  => 0,
                    'slot_sorting'         => 'asc',
                    'status' => C__RECORD_STATUS__NORMAL
                ]);
            }
        }

        $p_data['width'] = (isset($p_data['width']) && $p_data['width']) ? isys_convert::measure($p_data['width'], $p_data['unit']) : '';
        $p_data['height'] = (isset($p_data['height']) && $p_data['height']) ? isys_convert::measure($p_data['height'], $p_data['unit']) : '';
        $p_data['depth'] = (isset($p_data['depth']) && $p_data['depth']) ? isys_convert::measure($p_data['depth'], $p_data['unit']) : '';
        $p_data['weight'] = (isset($p_data['weight']) && $p_data['weight']) ? isys_convert::weight($p_data['weight'], $p_data['weight_unit']) : '';

        return parent::save_data($p_category_data_id, $p_data);
    }

    /**
     * @param  integer $p_objID
     */
    public function calcGroupRU($p_objID)
    {
        $l_dao = new isys_cmdb_dao_category_s_group($this->m_db);
        $l_query = "SELECT isys_cats_group_list__isys_obj__id FROM isys_cats_group_list
			INNER JOIN isys_connection ON isys_connection__id = isys_cats_group_list__isys_connection__id
			WHERE isys_connection__isys_obj__id = " . $this->convert_sql_id($p_objID);

        $l_res = $this->retrieve($l_query);

        while ($l_row = $l_res->get_row()) {
            $l_dao->calcRU($l_row["isys_cats_group_list__isys_obj__id"]);
        }
    }

    /**
     * Get height units for a rack object (how high is the object?).
     *
     * @param   integer $p_object_id
     *
     * @return  integer
     */
    public function get_rack_hu($p_object_id)
    {
        $l_nHU = null;

        $l_strSQL = "SELECT isys_catg_formfactor_list__rackunits FROM isys_catg_formfactor_list
			WHERE isys_catg_formfactor_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ";";

        $l_ret = $this->retrieve($l_strSQL);

        if ($l_ret->num_rows() > 0) {
            $l_nHU = $l_ret->get_row_value('isys_catg_formfactor_list__rackunits');
        }

        return $l_nHU;
    }

    /**
     * Get height units for a rack object (how high is the object?).
     *
     * @param   integer $p_object_id
     * @param   integer $p_ru
     *
     * @return  integer
     */
    public function set_rack_hu($p_object_id, $p_ru)
    {
        $l_sql = "UPDATE isys_catg_formfactor_list
            SET isys_catg_formfactor_list__rackunits = " . $this->convert_sql_int($p_ru) . "
            WHERE isys_catg_formfactor_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ";";

        return $this->update($l_sql) && $this->apply_update();
    }
}
