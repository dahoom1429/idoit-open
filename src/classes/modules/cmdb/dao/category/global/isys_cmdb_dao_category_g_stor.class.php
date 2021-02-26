<?php

use idoit\Component\Property\Type\DynamicProperty;
use idoit\Component\Property\Type\TextProperty;

/**
 * i-doit
 *
 * DAO: global category for storage devices.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_stor extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'stor';

    /**
     * Category's constant.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CATG__STORAGE_DEVICE';

    /**
     * Category's identifier.
     *
     * @var    integer
     * @fixme  No standard behavior!
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATG__STORAGE_DEVICE;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Callback method to get the model manufacturer id.
     *
     * @param   isys_request $p_request
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function callback_property_title_ui_params_secTableID(isys_request $p_request)
    {
        $l_cat_id = $p_request->get_category_data_id();
        $l_obj_id = $p_request->get_object_id();

        if ($l_cat_id > 0) {
            return $this->get_data($l_cat_id)
                ->get_row_value('isys_stor_model__isys_stor_manufacturer__id');
        } elseif ($l_obj_id > 0) {
            return $this->get_data(null, $l_obj_id)
                ->get_row_value('isys_stor_model__isys_stor_manufacturer__id');
        } else {
            return -1;
        }
    }

    /**
     * Callback method for the controller dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_controller(isys_request $p_request)
    {
        $l_return = [];

        if (!$p_request->get_object_id()) {
            return $l_return;
        }

        $l_res = $this->get_controller_by_object_id($p_request->get_object_id());

        if (is_countable($l_res) && count($l_res) > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_return[$l_row['isys_catg_controller_list__id']] = $l_row['isys_catg_controller_list__title'];
            }
        }

        return $l_return;
    }

    /**
     * Callback method for the controller dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_raid_group(isys_request $p_request)
    {
        $l_return = [];

        if (!$p_request->get_object_id()) {
            return $l_return;
        }

        $l_res = isys_cmdb_dao_category_g_raid::instance($this->get_database_component())
            ->get_raids(null, null, $p_request->get_object_id());

        if (is_countable($l_res) && count($l_res) > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_return[$l_row['isys_catg_raid_list__id']] = $l_row['isys_catg_raid_list__title'];
            }
        }

        return $l_return;
    }

    /**
     * @param $p_row
     *
     * @return mixed
     * @throws isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_capacity($p_row)
    {
        return isys_convert::retrieveFormattedMemoryByDao($p_row, $this, '__capacity');
    }

    /**
     * @param $p_row
     *
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function dynamic_properties()
    {
        return [
            '_total_capacity_hard_disk' => new DynamicProperty(
                'LC__CATG__STORAGE_TOTAL_CAPACITY_HARDDISK',
                'isys_catg_stor_list__isys_obj__id',
                'isys_catg_stor_list',
                [
                    $this,
                    'getTotalCapacityHarddisk'
                ]
            ),
            '_total_capacity_floppy'    => new DynamicProperty(
                'LC__CATG__STORAGE_TOTAL_CAPACITY_FLOPPY',
                'isys_catg_stor_list__isys_obj__id',
                'isys_catg_stor_list',
                [
                    $this,
                    'getTotalCapacityFloppy'
                ]
            ),
            '_total_capacity_cd_rom' => new DynamicProperty(
                'LC__CATG__STORAGE_TOTAL_CAPACITY_CD_ROM',
                'isys_catg_stor_list__isys_obj__id',
                'isys_catg_stor_list',
                [
                    $this,
                    'getTotalCapacityCdRom'
                ]
            ),
            '_total_capacity_tape' => new DynamicProperty(
                'LC__CATG__STORAGE_TOTAL_CAPACITY_TAPE',
                'isys_catg_stor_list__isys_obj__id',
                'isys_catg_stor_list',
                [
                    $this,
                    'getTotalCapacityTape'
                ]
            ),
            '_total_capacity_stick' => new DynamicProperty(
                'LC__CATG__STORAGE_TOTAL_CAPACITY_STICK',
                'isys_catg_stor_list__isys_obj__id',
                'isys_catg_stor_list',
                [
                    $this,
                    'getTotalCapacityStick'
                ]
            ),
            '_total_capacity_ssd' => new DynamicProperty(
                'LC__CATG__STORAGE_TOTAL_CAPACITY_SSD',
                'isys_catg_stor_list__isys_obj__id',
                'isys_catg_stor_list',
                [
                    $this,
                    'getTotalCapacitySsd'
                ]
            ),
            '_total_capacity_sd_card' => new DynamicProperty(
                'LC__CATG__STORAGE_TOTAL_CAPACITY_SD_CARD',
                'isys_catg_stor_list__isys_obj__id',
                'isys_catg_stor_list',
                [
                    $this,
                    'getTotalCapacitySdCard'
                ]
            ),
            '_total_capacity_streamer' => new DynamicProperty(
                'LC__CATG__STORAGE_TOTAL_CAPACITY_STREAMER',
                'isys_catg_stor_list__isys_obj__id',
                'isys_catg_stor_list',
                [
                    $this,
                    'getTotalCapacityStreamer'
                ]
            ),
            '_capacity' => new DynamicProperty(
                'LC__CMDB_CATG__MEMORY_CAPACITY',
                'isys_catg_stor_list__id',
                'isys_catg_stor_list',
                [
                    $this,
                    'dynamic_property_callback_capacity'
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
            'type'         => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Typ'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_stor_list__isys_stor_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_stor_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_stor_type',
                        'isys_stor_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_stor_type__title
                            FROM isys_catg_stor_list
                            INNER JOIN isys_stor_type ON isys_stor_type__id = isys_catg_stor_list__isys_stor_type__id',
                        'isys_catg_stor_list',
                        'isys_catg_stor_list__id',
                        'isys_catg_stor_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_stor_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_stor_list', 'LEFT', 'isys_catg_stor_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_stor_type', 'LEFT', 'isys_catg_stor_list__isys_stor_type__id', 'isys_stor_type__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_stor_type'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_plus'
                    ]
                ]
            ]),
            'title'        => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_stor_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_stor_list__title FROM isys_catg_stor_list',
                        'isys_catg_stor_list',
                        'isys_catg_stor_list__id',
                        'isys_catg_stor_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_stor_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__STOR__TITLE'
                ]
            ]),
            'manufacturer' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__MANUFACTURE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Manufacturer'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_stor_list__isys_stor_manufacturer__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_stor_manufacturer',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_stor_manufacturer',
                        'isys_stor_manufacturer__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_stor_manufacturer__title
                            FROM isys_catg_stor_list
                            INNER JOIN isys_stor_manufacturer ON isys_stor_manufacturer__id = isys_catg_stor_list__isys_stor_manufacturer__id',
                        'isys_catg_stor_list',
                        'isys_catg_stor_list__id',
                        'isys_catg_stor_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_stor_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_stor_list', 'LEFT', 'isys_catg_stor_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_stor_manufacturer',
                            'LEFT',
                            'isys_catg_stor_list__isys_stor_manufacturer__id',
                            'isys_stor_manufacturer__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_MANUFACTURER',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'       => 'isys_stor_manufacturer',
                        'p_ajaxTable'      => 'isys_stor_model',
                        'p_ajaxIdentifier' => 'C__CATG__STORAGE_MODEL'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_plus',
                        ['isys_stor_manufacturer']
                    ]
                ]
            ]),
            'model'        => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO             => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__STORAGE_MODEL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Model'
                ],
                C__PROPERTY__DATA             => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_stor_list__isys_stor_model__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_stor_model',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_stor_model',
                        'isys_stor_model__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_stor_model__title
                            FROM isys_catg_stor_list
                            INNER JOIN isys_stor_model ON isys_stor_model__id = isys_catg_stor_list__isys_stor_model__id',
                        'isys_catg_stor_list',
                        'isys_catg_stor_list__id',
                        'isys_catg_stor_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_stor_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_stor_list', 'LEFT', 'isys_catg_stor_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_stor_model',
                            'LEFT',
                            'isys_catg_stor_list__isys_stor_model__id',
                            'isys_stor_model__id'
                        )
                    ]
                ],
                C__PROPERTY__UI               => [
                    C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_MODEL',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'              => 'isys_stor_model',
                        'secTable'                => 'isys_stor_manufacturer',
                        'secTableID'              => new isys_callback([
                            'isys_cmdb_dao_category_g_stor',
                            'callback_property_title_ui_params_secTableID'
                        ]),
                        'p_strSecTableIdentifier' => 'C__CATG__STORAGE_MANUFACTURER',
                        'p_strSecDataIdentifier'  => 'isys_cmdb_dao_category_g_stor::manufacturer',
                    ]
                ],
                C__PROPERTY__PROVIDES         => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT           => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_global_stor_export_helper',
                        'storageModel'
                    ]
                ],
                C__PROPERTY__FORMAT__REQUIRES => 'manufacturer',
                C__PROPERTY__DEPENDENCY => [
                    C__PROPERTY__DEPENDENCY__PROPKEY => 'manufacturer',
                    C__PROPERTY__DEPENDENCY__SMARTYPARAMS => [
                        'p_strTable'     => 'isys_stor_model',
                        C__PROPERTY__DEPENDENCY__CONDITION      => 'isys_stor_model__id = %s',
                        C__PROPERTY__DEPENDENCY__CONDITION_VALUE => 'isys_catg_stor_list__isys_stor_model__id',
                    ]
                ]
            ]),
            'unit'         => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__MEMORY_UNIT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Unit'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_stor_list__isys_memory_unit__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_memory_unit',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_memory_unit',
                        'isys_memory_unit__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_memory_unit__title
                            FROM isys_catg_stor_list
                            INNER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_stor_list__isys_memory_unit__id',
                        'isys_catg_stor_list',
                        'isys_catg_stor_list__id',
                        'isys_catg_stor_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_stor_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_stor_list', 'LEFT', 'isys_catg_stor_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_stor_list__isys_memory_unit__id',
                            'isys_memory_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_UNIT',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_memory_unit',
                        'p_strClass' => 'input-mini',
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_plus'
                    ]
                ]
            ]),
            'capacity'     => array_replace_recursive(isys_cmdb_dao_category_pattern::double(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB_CATG__MEMORY_CAPACITY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Capacity'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_stor_list__capacity',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(\'{mem\', \',\', isys_catg_stor_list__capacity, \',\', isys_memory_unit__title, \'}\')
                            FROM isys_catg_stor_list
                            INNER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_stor_list__isys_memory_unit__id',
                        'isys_catg_stor_list',
                        'isys_catg_stor_list__id',
                        'isys_catg_stor_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_stor_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_stor_list', 'LEFT', 'isys_catg_stor_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_stor_list__isys_memory_unit__id',
                            'isys_memory_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_CAPACITY',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-medium'
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
                        ['memory']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'unit'
                ]
            ]),
            'hotspare'     => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__STORAGE_HOTSPARE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Hotspare'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_stor_list__hotspare',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_catg_stor_list__hotspare = \'1\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__YES') . '
                        	    WHEN isys_catg_stor_list__hotspare = \'0\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__NO') . ' END)
                                FROM isys_catg_stor_list',
                        'isys_catg_stor_list',
                        'isys_catg_stor_list__id',
                        'isys_catg_stor_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_stor_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_stor_list', 'LEFT', 'isys_catg_stor_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__CATG__STORAGE_HOTSPARE',
                    C__PROPERTY__UI__DEFAULT => 0,
                    C__PROPERTY__UI__PARAMS  => [
                        'p_arData' => get_smarty_arr_YES_NO()
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_yes_or_no'
                    ]
                ]
            ]),
            'connected'    => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__STORAGE_CONNECTION_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Connection'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_stor_list__isys_stor_con_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_stor_con_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_stor_con_type',
                        'isys_stor_con_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_stor_con_type__title
                            FROM isys_catg_stor_list
                            INNER JOIN isys_stor_con_type ON isys_stor_con_type__id = isys_catg_stor_list__isys_stor_con_type__id',
                        'isys_catg_stor_list',
                        'isys_catg_stor_list__id',
                        'isys_catg_stor_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_stor_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_stor_list', 'LEFT', 'isys_catg_stor_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_stor_con_type',
                            'LEFT',
                            'isys_catg_stor_list__isys_stor_con_type__id',
                            'isys_stor_con_type__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_CONNECTION',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'  => 'isys_stor_con_type',
                        'p_bLinklist' => '1'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_plus'
                    ]
                ]
            ]),
            'controller'   => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__STORAGE_CONTROLLER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Controller'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_stor_list__isys_catg_controller_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_controller_list',
                        'isys_catg_controller_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_controller_list__title
                            FROM isys_catg_stor_list
                            INNER JOIN isys_catg_controller_list ON isys_catg_controller_list__id = isys_catg_stor_list__isys_catg_controller_list__id',
                        'isys_catg_stor_list',
                        'isys_catg_stor_list__id',
                        'isys_catg_stor_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_stor_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_stor_list', 'LEFT', 'isys_catg_stor_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_controller_list',
                            'LEFT',
                            'isys_catg_stor_list__isys_catg_controller_list__id',
                            'isys_catg_controller_list__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_CONTROLLER',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_stor',
                            'callback_property_controller'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_reference_value'
                    ]
                ]
            ]),
            'raid_group'   => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__RAIDGROUP',
                    C__PROPERTY__INFO__DESCRIPTION => 'Hardware RAID group'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_stor_list__isys_catg_raid_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_raid_list',
                        'isys_catg_raid_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_raid_list__title
                            FROM isys_catg_stor_list
                            INNER JOIN isys_catg_raid_list ON isys_catg_raid_list__id = isys_catg_stor_list__isys_catg_raid_list__id',
                        'isys_catg_stor_list',
                        'isys_catg_stor_list__id',
                        'isys_catg_stor_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_stor_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_stor_list', 'LEFT', 'isys_catg_stor_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_raid_list',
                            'LEFT',
                            'isys_catg_stor_list__isys_catg_raid_list__id',
                            'isys_catg_raid_list__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_RAIDGROUP',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_stor',
                            'callback_property_raid_group'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_reference_value'
                    ]
                ]
            ]),
            'serial'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__STORAGE_SERIAL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Serial number'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_stor_list__serial',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_stor_list__serial FROM isys_catg_stor_list',
                        'isys_catg_stor_list',
                        'isys_catg_stor_list__id',
                        'isys_catg_stor_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_stor_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__STORAGE_SERIAL'
                ]
            ]),
            'description'  => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_stor_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_stor_list__description FROM isys_catg_stor_list',
                        'isys_catg_stor_list',
                        'isys_catg_stor_list__id',
                        'isys_catg_stor_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_stor_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__STORAGE_DEVICE', 'C__CATG__STORAGE_DEVICE'),
                ]
            ]),
            'lto_type'     => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__STORAGE_LTO_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'LTO Type'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_stor_list__isys_stor_lto_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_stor_lto_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_stor_lto_type',
                        'isys_stor_lto_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_stor_lto_type__title
                            FROM isys_catg_stor_list
                            INNER JOIN isys_stor_lto_type ON isys_stor_lto_type__id = isys_catg_stor_list__isys_stor_lto_type__id',
                        'isys_catg_stor_list',
                        'isys_catg_stor_list__id',
                        'isys_catg_stor_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_stor_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_stor_list', 'LEFT', 'isys_catg_stor_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_stor_ltp_type',
                            'LEFT',
                            'isys_catg_stor_list__isys_stor_lto_type__id',
                            'isys_stor_ltp_type__id'
                        )
                    ],
                    C__PROPERTY__DATA__INDEX        => true
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__STORAGE_LTO_TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_stor_lto_type'
                    ]
                ]
            ]),
            'fc_address'   => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__STORAGE_FC_ADDRESS',
                    C__PROPERTY__INFO__DESCRIPTION => 'FC Address'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_stor_list__fc_address',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_stor_list__fc_address FROM isys_catg_stor_list',
                        'isys_catg_stor_list',
                        'isys_catg_stor_list__id',
                        'isys_catg_stor_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_stor_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__STORAGE_FC_ADDRESS'
                ]
            ]),
            'firmware'     => new TextProperty(
                'C__CATG__STORAGE_FIRMWARE',
                'LC__CATG__STORAGE_FIRMWARE',
                'isys_catg_stor_list__firmware',
                'isys_catg_stor_list'
            )
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array $p_category_data Values of category data to be saved.
     * @param   int   $p_object_id     Current object identifier (from database)
     * @param   int   $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    $p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        $p_category_data['properties']['type'][C__DATA__VALUE],
                        C__RECORD_STATUS__NORMAL,
                        $p_category_data['properties']['title'][C__DATA__VALUE],
                        $p_category_data['properties']['capacity'][C__DATA__VALUE],
                        $p_category_data['properties']['unit'][C__DATA__VALUE],
                        $p_category_data['properties']['connected'][C__DATA__VALUE],
                        $p_category_data['properties']['controller'][C__DATA__VALUE],
                        $p_category_data['properties']['manufacturer'][C__DATA__VALUE],
                        $p_category_data['properties']['model'][C__DATA__VALUE],
                        $p_category_data['properties']['raid_group'][C__DATA__VALUE],
                        null,
                        null,
                        $p_category_data['properties']['hotspare'][C__DATA__VALUE],
                        $p_category_data['properties']['serial'][C__DATA__VALUE],
                        null,
                        $p_category_data['properties']['description'][C__DATA__VALUE],
                        $p_category_data['properties']['lto_type'][C__DATA__VALUE],
                        $p_category_data['properties']['fc_address'][C__DATA__VALUE],
                        $p_category_data['properties']['firmware'][C__DATA__VALUE]
                    );
                    if ($p_category_data['data_id'] > 0) {
                        $l_indicator = true;
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save(
                        $p_category_data['data_id'],
                        $p_category_data['properties']['type'][C__DATA__VALUE],
                        C__RECORD_STATUS__NORMAL,
                        $p_category_data['properties']['title'][C__DATA__VALUE],
                        $p_category_data['properties']['capacity'][C__DATA__VALUE],
                        $p_category_data['properties']['unit'][C__DATA__VALUE],
                        $p_category_data['properties']['connected'][C__DATA__VALUE],
                        $p_category_data['properties']['controller'][C__DATA__VALUE],
                        $p_category_data['properties']['manufacturer'][C__DATA__VALUE],
                        $p_category_data['properties']['model'][C__DATA__VALUE],
                        $p_category_data['properties']['raid_group'][C__DATA__VALUE],
                        null,
                        null,
                        $p_category_data['properties']['hotspare'][C__DATA__VALUE],
                        $p_category_data['properties']['serial'][C__DATA__VALUE],
                        null,
                        $p_category_data['properties']['description'][C__DATA__VALUE],
                        $p_category_data['properties']['lto_type'][C__DATA__VALUE],
                        $p_category_data['properties']['fc_address'][C__DATA__VALUE],
                        $p_category_data['properties']['firmware'][C__DATA__VALUE]
                    );
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Method which calculates the total capacity by object id and type
     *
     * @param $objId int
     * @param $type  int
     *
     * @return mixed|string
     */
    private function calculateTotalCapacity($objId, $type)
    {
        if (!$objId && !$type) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        $l_dao = isys_factory::get_instance('isys_cmdb_dao', isys_application::instance()->database);

        $l_res = $l_dao->retrieve('SELECT isys_catg_stor_list__capacity, isys_memory_unit__const, isys_memory_unit__title
            FROM isys_catg_stor_list
              LEFT JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_stor_list__isys_memory_unit__id
            WHERE isys_catg_stor_list__isys_obj__id = ' . $l_dao->convert_sql_id($objId) . '
                AND isys_catg_stor_list__isys_stor_type__id = ' . $l_dao->convert_sql_id($type) . '
                AND isys_catg_stor_list__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';');

        if (is_countable($l_res) && count($l_res)) {
            $l_capacity = 0;
            $l_unit = 'C__MEMORY_UNIT__GB';
            $l_unit_title = 'GB';

            while ($l_row = $l_res->get_row()) {
                $l_capacity += $l_row['isys_catg_stor_list__capacity'];

                if ($l_row['isys_memory_unit__const']) {
                    // We use the const to save a further query in "isys_convert::memory()".
                    $l_unit = $l_row['isys_memory_unit__const'];
                    $l_unit_title = $l_row['isys_memory_unit__title'];
                }
            }

            return isys_convert::formatNumber(isys_convert::memory($l_capacity, $l_unit, C__CONVERT_DIRECTION__BACKWARD)) . ' ' . $l_unit_title;
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Dynamic property handling for retrieving the total capacity of all hard disks with unit.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getTotalCapacityHarddisk(array $p_row)
    {
        $objId = ($p_row['isys_catg_stor_list__isys_obj__id'] ?: ($p_row['isys_obj__id'] ?: null));

        if ($objId === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        return $this->calculateTotalCapacity($objId, defined_or_default('C__STOR_TYPE_DEVICE_HD'));
    }

    /**
     * Dynamic property handling for retrieving the total capacity of all floppys with unit.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getTotalCapacityFloppy(array $p_row)
    {
        $objId = ($p_row['isys_catg_stor_list__isys_obj__id'] ?: ($p_row['isys_obj__id'] ?: null));

        if ($objId === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        return $this->calculateTotalCapacity($objId, defined_or_default('C__STOR_TYPE_DEVICE_FLOPPY'));
    }

    /**
     * Dynamic property handling for retrieving the total capacity of all CD/DVD-ROMS with unit.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getTotalCapacityCdRom(array $p_row)
    {
        $objId = ($p_row['isys_catg_stor_list__isys_obj__id'] ?: ($p_row['isys_obj__id'] ?: null));

        if ($objId === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        return $this->calculateTotalCapacity($objId, defined_or_default('C__STOR_TYPE_DEVICE_CD_ROM'));
    }

    /**
     * Dynamic property handling for retrieving the total capacity of all tapes with unit.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getTotalCapacityTape(array $p_row)
    {
        $objId = ($p_row['isys_catg_stor_list__isys_obj__id'] ?: ($p_row['isys_obj__id'] ?: null));

        if ($objId === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        return $this->calculateTotalCapacity($objId, defined_or_default('C__STOR_TYPE_DEVICE_TAPE'));
    }

    /**
     * Dynamic property handling for retrieving the total capacity of all usb-sticks with unit.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getTotalCapacityStick(array $p_row)
    {
        $objId = ($p_row['isys_catg_stor_list__isys_obj__id'] ?: ($p_row['isys_obj__id'] ?: null));

        if ($objId === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        return $this->calculateTotalCapacity($objId, defined_or_default('C__STOR_TYPE_DEVICE_STICK'));
    }

    /**
     * Dynamic property handling for retrieving the total capacity of all SSDs with unit.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getTotalCapacitySsd(array $p_row)
    {
        $objId = ($p_row['isys_catg_stor_list__isys_obj__id'] ?: ($p_row['isys_obj__id'] ?: null));

        if ($objId === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        return $this->calculateTotalCapacity($objId, defined_or_default('C__STOR_TYPE_DEVICE_SSD'));
    }

    /**
     * Dynamic property handling for retrieving the total capacity of all SD-Cards with unit.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getTotalCapacitySdCard(array $p_row)
    {
        $objId = ($p_row['isys_catg_stor_list__isys_obj__id'] ?: ($p_row['isys_obj__id'] ?: null));

        if ($objId === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        return $this->calculateTotalCapacity($objId, defined_or_default('C__STOR_TYPE_DEVICE_SD_CARD'));
    }

    /**
     * Dynamic property handling for retrieving the total capacity of all streamers with unit.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getTotalCapacityStreamer(array $p_row)
    {
        $objId = ($p_row['isys_catg_stor_list__isys_obj__id'] ?: ($p_row['isys_obj__id'] ?: null));

        if ($objId === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        return $this->calculateTotalCapacity($objId, defined_or_default('C__STOR_TYPE_DEVICE_STREAMER'));
    }

    /**
     * Returns a result set with all devices connected to the specified storage controller.
     *
     * @param   integer $p_controller_id
     * @param   integer $p_object_id
     * @param   integer $p_nRaidPoolID
     * @param   integer $p_cDeviceType
     * @param   string  $p_condition
     * @param   boolean $p_show_raid
     *
     * @return  isys_component_dao_result
     * @author  Niclas Potthast <npotthast@i-doit.de>
     * @author  Andre Wösten <awoesten@i-doit.org>
     */
    public function get_devices($p_controller_id = null, $p_object_id = null, $p_nRaidPoolID = null, $p_cDeviceType = null, $p_condition = null, $p_show_raid = false)
    {
        $l_sql = "SELECT * FROM isys_catg_stor_list
			LEFT OUTER JOIN isys_stor_manufacturer ON isys_stor_manufacturer__id = isys_catg_stor_list__isys_stor_manufacturer__id
			LEFT OUTER JOIN isys_stor_model ON isys_stor_model__id = isys_catg_stor_list__isys_stor_model__id
			LEFT OUTER JOIN isys_stor_raid_level ON isys_stor_raid_level__id = isys_catg_stor_list__isys_stor_raid_level__id
			LEFT OUTER JOIN isys_catg_controller_list ON isys_catg_controller_list__id = isys_catg_stor_list__isys_catg_controller_list__id
			LEFT OUTER JOIN isys_catg_sanpool_list ON isys_catg_sanpool_list__id = isys_catg_stor_list__isys_catg_sanpool_list__id
			LEFT JOIN isys_stor_con_type ON isys_stor_con_type__id = isys_catg_stor_list__isys_stor_con_type__id
			LEFT OUTER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_stor_list__isys_memory_unit__id
			LEFT OUTER JOIN isys_stor_type ON isys_stor_type__id = isys_catg_stor_list__isys_stor_type__id
			LEFT OUTER JOIN isys_catg_raid_list ON isys_catg_raid_list__id = isys_catg_stor_list__isys_catg_raid_list__id
			LEFT OUTER JOIN isys_raid_type ON isys_raid_type__id = isys_catg_raid_list__isys_raid_type__id
            LEFT OUTER JOIN isys_stor_lto_type ON isys_stor_lto_type__id = isys_catg_stor_list__isys_stor_lto_type__id ";

        if (is_numeric($p_controller_id)) {
            $l_sql .= "WHERE isys_catg_stor_list__isys_catg_controller_list__id= " . $this->convert_sql_id($p_controller_id);
        } elseif (is_numeric($p_object_id)) {
            $l_sql .= "JOIN isys_obj ON isys_catg_stor_list__isys_obj__id=isys_obj__id WHERE isys_obj__id = " . $this->convert_sql_id($p_object_id);
        } else {
            $l_sql .= "WHERE TRUE ";
        }

        if ($p_nRaidPoolID != null || $p_nRaidPoolID != 0) {
            $l_sql .= " AND isys_catg_stor_list__isys_catg_raid_list__id = " . $this->convert_sql_id($p_nRaidPoolID) . " ";
        } elseif (!$p_show_raid) {
            $l_sql .= " AND isys_catg_stor_list__isys_catg_raid_list__id IS NULL AND isys_catg_stor_list__status = " . C__RECORD_STATUS__NORMAL;
        }

        if ($p_cDeviceType) {
            $l_sql .= " AND isys_catg_stor_list__isys_stor_type__id = '$p_cDeviceType' ";
        }

        if ($p_condition != null) {
            $l_sql .= $p_condition;
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for returning the raids.
     *
     * @param   integer $p_controllerID
     *
     * @return  isys_component_dao_result
     */
    public function get_raids($p_controllerID)
    {
        return $this->retrieve("SELECT * FROM isys_catg_raid_list WHERE isys_catg_raid_list__isys_catg_controller_list__id = " . $this->convert_sql_id($p_controllerID));
    }

    /**
     * Method for returning unassigned raids.
     *
     * @param   integer $p_objID
     *
     * @return  isys_component_dao_result
     */
    public function get_unassigned_raids($p_objID)
    {
        return $this->retrieve("SELECT * FROM isys_catg_raid_list WHERE isys_catg_raid_list__isys_obj__id = " . $this->convert_sql_id($p_objID) .
            " AND ISNULL(isys_catg_raid_list__isys_catg_controller_list__id)");
    }

    /**
     * Returns a result set with all controllers assigned to the specified object id.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_cType
     *
     * @return  isys_component_dao_result
     * @author  Andre Wösten <awoesten@i-doit.org>
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_controller_by_object_id($p_obj_id, $p_cType = null)
    {
        $l_q = "SELECT isys_catg_controller_list__title, isys_catg_controller_list__id, isys_catg_controller_list__status, isys_controller_type__const
			FROM isys_catg_controller_list
			JOIN isys_obj ON isys_catg_controller_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_controller_type ON isys_catg_controller_list__isys_controller_type__id = isys_controller_type__id
			WHERE isys_obj__id = " . $this->convert_sql_id($p_obj_id) . ";";
        return $this->retrieve($l_q);
    }

    /**
     * Return all SAN pools.
     *
     * @return  isys_component_dao_result
     */
    public function get_san_pools()
    {
        return $this->retrieve("SELECT * FROM isys_catg_sanpool_list");
    }

    /**
     * Returns Parent Object of SAN Pool
     *
     * @param   integer $p_SANPoolID
     *
     * @return  isys_component_dao_result
     */
    public function get_san_pool_parent($p_SANPoolID)
    {
        if (!$p_SANPoolID || !is_numeric($p_SANPoolID)) {
            return false;
        }

        $l_strSQL = "SELECT *
			FROM isys_catg_sanpool_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_sanpool_list__isys_obj__id
			WHERE isys_catg_sanpool_list__id = " . $this->convert_sql_id($p_SANPoolID) . ";";

        return $this->retrieve($l_strSQL);
    }

    /**
     * @return integer (newly created id)
     *
     * @param int $p_cat_level
     * @param int $p_intOldRecStatus __status of record before update
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus)
    {
        $p_cat_level = null;

        $l_posts = isys_module_request::get_instance()
            ->get_posts();
        $l_gets = isys_module_request::get_instance()
            ->get_gets();

        $l_id = $l_gets[C__CMDB__GET__CATLEVEL] ?: $l_posts["stor_id"];

        // create new one, or just save it?
        if ($l_id != -1) {
            $l_ret = $this->save(
                $l_id,
                $l_posts["C__CATG__STORAGE_TYPE"],
                C__RECORD_STATUS__NORMAL,
                $l_posts["C__CATG__STORAGE_TITLE"],
                $l_posts["C__CATG__STORAGE_CAPACITY"],
                $l_posts["C__CATG__STORAGE_UNIT"],
                $l_posts["C__CATG__STORAGE_CONNECTION_TYPE"],
                $l_posts["C__CATG__STORAGE_CONTROLLER"],
                $l_posts["C__CATG__STORAGE_MANUFACTURER"],
                $l_posts["C__CATG__STORAGE_MODEL"],
                $l_posts["C__CATG__STORAGE_RAIDGROUP"],
                $l_posts["C__CATG__STORAGE_RAIDLEVEL"],
                $l_posts["C__CATG__STORAGE_SANPOOL"],
                $l_posts["C__CATG__STORAGE_HOTSPARE"],
                $l_posts["C__CATG__STORAGE_SERIAL"],
                explode(",", $l_posts["C__CATG__STORAGE_CONNECTION__selected_values"]),
                $l_posts["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $l_posts["C__CATG__STORAGE_LTO_TYPE"],
                $l_posts["C__CATG__STORAGE_FC_ADDRESS"],
                $l_posts["C__CATG__STORAGE_FIRMWARE"]
            );
        } else {
            $l_storage_count = 1;

            // Determine how many devices are to be created.
            if (is_numeric($l_posts["C__CATG__STORAGE__SUFFIX_COUNT"])) {
                if ($l_posts["C__CATG__STORAGE__SUFFIX_COUNT"] > 1) {
                    $l_storage_count = $l_posts["C__CATG__STORAGE__SUFFIX_COUNT"];
                }
            }

            $l_title_arr = isys_smarty_plugin_f_title_suffix_counter::generate_title_as_array($_POST, 'C__CATG__STORAGE', 'C__CATG__STORAGE_TITLE');

            for ($i = 0;$l_storage_count > $i;$i++) {
                $l_title = $l_title_arr[$i];

                $l_ret = $this->create(
                    $l_gets[C__CMDB__GET__OBJECT],
                    $l_posts["C__CATG__STORAGE_TYPE"],
                    C__RECORD_STATUS__NORMAL,
                    $l_title,
                    $l_posts["C__CATG__STORAGE_CAPACITY"],
                    $l_posts["C__CATG__STORAGE_UNIT"],
                    $l_posts["C__CATG__STORAGE_CONNECTION_TYPE"],
                    $l_posts["C__CATG__STORAGE_CONTROLLER"],
                    $l_posts["C__CATG__STORAGE_MANUFACTURER"],
                    $l_posts["C__CATG__STORAGE_MODEL"],
                    $l_posts["C__CATG__STORAGE_RAIDGROUP"],
                    $l_posts["C__CATG__STORAGE_RAIDLEVEL"],
                    $l_posts["C__CATG__STORAGE_SANPOOL"],
                    $l_posts["C__CATG__STORAGE_HOTSPARE"],
                    $l_posts["C__CATG__STORAGE_SERIAL"],
                    explode(",", $l_posts["C__CATG__STORAGE_CONNECTION__selected_values"]),
                    $l_posts["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                    $l_posts["C__CATG__STORAGE_LTO_TYPE"],
                    $l_posts["C__CATG__STORAGE_FC_ADDRESS"],
                    $l_posts["C__CATG__STORAGE_FIRMWARE"]
                );
            }

            $p_cat_level = 1;
        }

        return $l_ret;
    }

    /**
     * Executes the operations to create the category entry for the object referenced by isys_obj__id $p_objID
     * The device type is specified by its constant C__CATG__STORAGE_TYPE $p_deviceType
     *
     * @param int     $p_objID
     * @param int     $p_deviceType
     * @param int     $p_recStatus
     * @param String  $p_title
     * @param float   $p_capacity
     * @param integer $p_capacityUnitID
     * @param integer $p_conTypeID
     * @param integer $p_controllerID
     * @param integer $p_manufacturerID
     * @param integer $p_modelID
     * @param integer $p_raidGroupID
     * @param integer $p_raidLevelID
     * @param integer $p_sanPoolID
     * @param boolean $p_hotspare
     * @param         $p_serial
     * @param array   $p_connectedHDs
     * @param String  $p_description
     *
     * @return int the newly created ID or false
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create(
        $p_objID,
        $p_deviceType,
        $p_recStatus,
        $p_title,
        $p_capacity,
        $p_capacityUnitID,
        $p_conTypeID,
        $p_controllerID,
        $p_manufacturerID,
        $p_modelID,
        $p_raidGroupID,
        $p_raidLevelID,
        $p_sanPoolID,
        $p_hotspare,
        $p_serial,
        $p_connectedHDs,
        $p_description,
        $p_lto_type,
        $p_fc_address,
        $p_firmware
    ) {
        // Convert capacity from user's locale to invariant.
        $p_capacity = isys_convert::memory(isys_helper::filter_number($p_capacity), $p_capacityUnitID);

        try {
            $this->begin_update();

            $l_update = "INSERT INTO isys_catg_stor_list SET
				isys_catg_stor_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ",
				isys_catg_stor_list__status = " . $this->convert_sql_id($p_recStatus) . ",
				isys_catg_stor_list__isys_stor_type__id = " . $this->convert_sql_id($p_deviceType) . ",
				isys_catg_stor_list__title = " . $this->convert_sql_text($p_title) . ",
				isys_catg_stor_list__capacity = " . $this->convert_sql_text($p_capacity) . ",
				isys_catg_stor_list__isys_memory_unit__id = " . $this->convert_sql_id($p_capacityUnitID) . ",
				isys_catg_stor_list__isys_stor_con_type__id = " . $this->convert_sql_id($p_conTypeID) . ",
				isys_catg_stor_list__isys_catg_controller_list__id = " . $this->convert_sql_id($p_controllerID) . ",
				isys_catg_stor_list__isys_stor_manufacturer__id = " . $this->convert_sql_id($p_manufacturerID) . ",
				isys_catg_stor_list__isys_stor_model__id = " . $this->convert_sql_id($p_modelID) . ",
				isys_catg_stor_list__isys_catg_raid_list__id = " . $this->convert_sql_id($p_raidGroupID) . ",
				isys_catg_stor_list__isys_stor_raid_level__id = " . $this->convert_sql_id($p_raidLevelID) . ",
				isys_catg_stor_list__isys_catg_sanpool_list__id = " . $this->convert_sql_id($p_sanPoolID) . ",
				isys_catg_stor_list__hotspare = " . $this->convert_sql_text($p_hotspare) . ",
				isys_catg_stor_list__serial = " . $this->convert_sql_text($p_serial) . ",
				isys_catg_stor_list__description = " . $this->convert_sql_text($p_description) . ",
                isys_catg_stor_list__isys_stor_lto_type__id = " . $this->convert_sql_id($p_lto_type) . ",
                isys_catg_stor_list__fc_address = " . $this->convert_sql_text($p_fc_address) . ",
                isys_catg_stor_list__firmware = " . $this->convert_sql_text($p_firmware);

            if (!$this->update($l_update)) {
                throw new Exception();
            }

            $l_id = $this->get_last_insert_id();

            if ($p_deviceType == defined_or_default('C__STOR_TYPE_DEVICE_RAID_GRP') && is_array($p_connectedHDs)) {
                // Set this as raidpool for the given HDDs by $p_connectedHDs.
                foreach ($p_connectedHDs as $l_dev) {
                    $l_update = "UPDATE isys_catg_stor_list SET
						isys_catg_stor_list__isys_catg_raid_list__id = " . $this->convert_sql_id($l_id) . "
						WHERE isys_catg_stor_list__id = " . $this->convert_sql_id($l_dev);

                    if (!$this->update($l_update)) {
                        throw new Exception();
                    }
                }
            }

            if (!$this->apply_update()) {
                throw new Exception();
            }
        } catch (Exception $e) {
            $this->cancel_update();

            return false;
        }

        return $l_id;
    }

    /**
     * Executes the operations to save the category entry referenced by isys_catg_stor_list__id $p_cat_level
     * The device type is specified by its constant C__CATG__STORAGE_TYPE $p_deviceType.
     *
     * @param int    $p_cat_level
     * @param int    $p_deviceType
     * @param int    $p_recStatus
     * @param string $p_title
     * @param float  $p_capacity
     * @param int    $p_capacityUnitID
     * @param int    $p_conTypeID
     * @param int    $p_controllerID
     * @param int    $p_manufacturerID
     * @param int    $p_modelID
     * @param int    $p_raidGroupID
     * @param int    $p_raidLevelID
     * @param int    $p_sanPoolID
     * @param bool   $p_hotspare
     * @param string $p_serial
     * @param array  $p_connectedHDs
     * @param string $p_description
     * @param int    $p_lto_type
     * @param string $p_fc_address
     * @param string $p_firmware
     *
     * @return boolean
     */
    public function save(
        $p_cat_level,
        $p_deviceType,
        $p_recStatus,
        $p_title,
        $p_capacity,
        $p_capacityUnitID,
        $p_conTypeID,
        $p_controllerID,
        $p_manufacturerID,
        $p_modelID,
        $p_raidGroupID,
        $p_raidLevelID,
        $p_sanPoolID,
        $p_hotspare,
        $p_serial,
        $p_connectedHDs,
        $p_description,
        $p_lto_type,
        $p_fc_address,
        $p_firmware
    ) {
        $p_capacity = isys_convert::memory(isys_helper::filter_number($p_capacity), $p_capacityUnitID);

        try {
            $this->begin_update();

            $l_update = "UPDATE isys_catg_stor_list SET
				isys_catg_stor_list__status = " . $this->convert_sql_id($p_recStatus) . ",
				isys_catg_stor_list__isys_stor_type__id = " . $this->convert_sql_id($p_deviceType) . ",
				isys_catg_stor_list__title = " . $this->convert_sql_text($p_title) . ",
				isys_catg_stor_list__capacity = " . $this->convert_sql_text($p_capacity) . ",
				isys_catg_stor_list__isys_memory_unit__id = " . $this->convert_sql_id($p_capacityUnitID) . ",
				isys_catg_stor_list__isys_stor_con_type__id = " . $this->convert_sql_id($p_conTypeID) . ",
				isys_catg_stor_list__isys_catg_controller_list__id = " . $this->convert_sql_id($p_controllerID) . ",
				isys_catg_stor_list__isys_stor_manufacturer__id = " . $this->convert_sql_id($p_manufacturerID) . ",
				isys_catg_stor_list__isys_stor_model__id = " . $this->convert_sql_id($p_modelID) . ",
				isys_catg_stor_list__isys_catg_raid_list__id = " . $this->convert_sql_id($p_raidGroupID) . ",
				isys_catg_stor_list__isys_stor_raid_level__id = " . $this->convert_sql_id($p_raidLevelID) . ",
				isys_catg_stor_list__isys_catg_sanpool_list__id = " . $this->convert_sql_id($p_sanPoolID) . ",
				isys_catg_stor_list__hotspare = " . $this->convert_sql_text($p_hotspare) . ",
				isys_catg_stor_list__serial = " . $this->convert_sql_text($p_serial) . ",
				isys_catg_stor_list__description = " . $this->convert_sql_text($p_description) . ",
                isys_catg_stor_list__isys_stor_lto_type__id = " . $this->convert_sql_id($p_lto_type) . ",
                isys_catg_stor_list__fc_address = " . $this->convert_sql_text($p_fc_address) . ",
                isys_catg_stor_list__firmware = " . $this->convert_sql_text($p_firmware) . "
				WHERE isys_catg_stor_list__id = " . $this->convert_sql_id($p_cat_level) . ";";

            if (!$this->update($l_update)) {
                throw new Exception();
            }

            if ($p_deviceType == defined_or_default('C__STOR_TYPE_DEVICE_RAID_GRP')) {
                $l_update = "UPDATE isys_catg_stor_list SET
					isys_catg_stor_list__isys_catg_raid_list__id = NULL
					WHERE isys_catg_stor_list__isys_catg_raid_list__id = " . $this->convert_sql_id($p_cat_level);

                if (!$this->update($l_update)) {
                    throw new Exception();
                }

                // Set this as raidpool for the given HDDs by $p_connectedHDs.
                if (is_array($p_connectedHDs)) {
                    foreach ($p_connectedHDs as $l_dev) {
                        $l_update = "UPDATE isys_catg_stor_list SET
                            isys_catg_stor_list__isys_catg_raid_list__id = " . $this->convert_sql_id($p_cat_level) . "
                            WHERE isys_catg_stor_list__id = " . $this->convert_sql_id($l_dev);

                        if (!$this->update($l_update)) {
                            throw new Exception();
                        }
                    }
                }
            }

            if (!$this->apply_update()) {
                throw new Exception();
            }
        } catch (Exception $e) {
            $this->cancel_update();

            return false;
        }

        return true;
    }

    /**
     * Import-Handler for storage (hard disk)
     *
     * @param array
     *
     * @return array (newly created storage IDs)
     * @author  Niclas Potthast <npotthast@i-doit.org> - 2008-03-27
     * @version Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function import($p_data, $p_object_id)
    {
        $l_status = -1;
        $l_cat = -1;
        $l_ar_ID = [];

        if (is_array($p_data)) {
            foreach ($p_data as $l_key => $l_data) {
                foreach ($l_data as $l_arStor) {
                    switch ($l_key) {
                        case "cd":
                            $_POST['C__CATG__STORAGE_TYPE'] = defined_or_default('C__STOR_TYPE_DEVICE_CD_ROM');
                            break;
                        case "floppy":
                            $_POST['C__CATG__STORAGE_TYPE'] = defined_or_default('C__STOR_TYPE_DEVICE_FLOPPY');
                            break;
                        default:
                            $_POST['C__CATG__STORAGE_TYPE'] = defined_or_default('C__STOR_TYPE_DEVICE_HD');
                            break;
                    }

                    $_POST['C__CATG__STORAGE_MANUFACTURER'] = isys_import::check_dialog("isys_stor_manufacturer", $l_arStor["manufacturer"]);
                    $_POST['C__CATG__STORAGE_CAPACITY'] = $l_arStor["size"];
                    $_POST['C__CATG__STORAGE_UNIT'] = isys_import::check_dialog("isys_stor_unit", "C__STOR_UNIT__MB", "isys_stor_unit__const");
                    $_POST['C__CATG__STORAGE_TITLE'] = trim($l_arStor["name"]) . " (" . trim($l_arStor["number"]) . ")";
                    $_POST['C__CATG__STORAGE_FIRMWARE'] = $l_arStor["firmware"];

                    if (!$_GET[C__CMDB__GET__OBJECT] && !is_null($p_object_id)) {
                        $_GET[C__CMDB__GET__OBJECT] = $p_object_id;
                    }

                    $_GET[C__CMDB__GET__CATLEVEL] = -1;

                    isys_module_request::get_instance()
                        ->_internal_set_private("m_post", $_POST)
                        ->_internal_set_private("m_get", $_GET);

                    $l_ar_ID[] = $this->save_element($l_cat, $l_status);
                }
            }
        }

        return $l_ar_ID;
    }

    /**
     * Gets info of the raid system
     *
     * @param int $p_obj__id
     *
     * @return array
     */
    public function get_raid_memory_info($p_obj__id)
    {
        $l_sql = 'SELECT isys_catg_stor_list__capacity, isys_catg_stor_list__isys_memory_unit__id, isys_memory_unit__title
            FROM isys_catg_stor_list
            LEFT JOIN isys_memory_unit ON isys_catg_stor_list__isys_memory_unit__id = isys_memory_unit__id
            WHERE isys_catg_stor_list__isys_catg_raid_list__id = ' . $this->convert_sql_id($p_obj__id) . ';';

        $l_res = $this->retrieve($l_sql);
        $l_num_disks = $l_res->num_rows();
        $l_capacity = 0;
        while ($l_row = $l_res->get_row()) {
            if ($l_capacity == 0 || $l_capacity >
                isys_convert::memory($l_row["isys_catg_stor_list__capacity"], $l_row["isys_catg_stor_list__isys_memory_unit__id"], C__CONVERT_DIRECTION__BACKWARD)) {
                $l_capacity = isys_convert::memory(
                    $l_row["isys_catg_stor_list__capacity"],
                    $l_row["isys_catg_stor_list__isys_memory_unit__id"],
                    C__CONVERT_DIRECTION__BACKWARD
                );
            }

            $l_capacity_type = $l_row["isys_memory_unit__title"];
        }

        return [
            $l_capacity,
            $l_capacity_type,
            $l_num_disks
        ];
    }

    /**
     * Gets the Raid-Level.
     *
     * @param   integer $p_level__id
     *
     * @return  string
     */
    public function get_raid_level($p_level__id)
    {
        return $this->retrieve('SELECT isys_stor_raid_level__title FROM isys_stor_raid_level WHERE isys_stor_raid_level__id = ' . $this->convert_sql_id($p_level__id) . ';')
            ->get_row_value('isys_stor_raid_level__title');
    }

    /**
     * Calculates Capacity for the specific Raid-Level
     *
     * @param int   $p_disks
     * @param float $p_space
     * @param int   $p_raidtype
     *
     * @return float
     */
    public function raidcalc($p_disks, $p_space, $p_raidtype)
    {
        $l_num_disks = $p_disks;
        $l_space = 0;

        if ($l_num_disks % 2 != 0 && $p_raidtype == "1") {
            return false;
        }
        if ($l_num_disks % 2 != 0 && $p_raidtype == "10") {
            return false;
        }

        $l_diskspace_each = $p_space;
        $l_diskspace = ($l_diskspace_each * $l_num_disks);

        switch ($p_raidtype) {
            case "0":
                if ($l_num_disks >= 2) {
                    $l_strUtilization = $l_diskspace;
                    $l_space = $l_strUtilization * 100 / 100;
                }
                break;

            case "1":
                if ($l_num_disks >= 2) {
                    $l_strUtilization = $l_diskspace / 2;
                    $l_space = $l_strUtilization * 100 / 100;
                }
                break;

            case "10":
                if ($l_num_disks >= 4) {
                    $l_strUtilization = $l_diskspace / 2;
                    $l_space = $l_strUtilization * 100 / 100;
                }
                break;

            case "2":
                $l_strUtilization = $l_diskspace;
                $l_space = $l_strUtilization * 100 / 100;
                break;

            case "3":
                if ($l_num_disks >= 3) {
                    $l_strUtilization = ($l_diskspace * (((($l_num_disks - 1) / $l_num_disks) * 10000 / 100) / 100));
                    $l_space = $l_strUtilization * 100 / 100;
                }
                break;

            case "4":
                if ($l_num_disks >= 3) {
                    $l_strUtilization = ($l_diskspace * (((($l_num_disks - 1) / $l_num_disks * 10000) / 100) / 100));
                    $l_space = $l_strUtilization * 100 / 100;
                }
                break;

            case "5":
                if ($l_num_disks >= 3) {
                    $l_strUtilization = ($l_diskspace * (((($l_num_disks - 1) / $l_num_disks) * 10000 / 100) / 100));
                    $l_space = $l_strUtilization * 100 / 100;
                }
                break;

            case "6":
                if ($l_num_disks >= 4) {
                    $l_strUtilization = ($l_diskspace * (((($l_num_disks - 2) / $l_num_disks) * 10000 / 100) / 100));
                    $l_space = $l_strUtilization * 100 / 100;
                }
                break;
        }

        if ($l_space >= 0) {
            return $l_space;
        }
        return false;
    }

    /**
     * Attach new raid.
     *
     * @param   integer $p_stor__id
     * @param   integer $p_raid__id
     * @param   integer $p_controller__id
     *
     * @return  boolean
     */
    public function add_raid_to_item($p_stor__id, $p_raid__id, $p_controller__id = null)
    {
        $l_update = 'UPDATE isys_catg_stor_list
			SET isys_catg_stor_list__isys_catg_raid_list__id = ' . $this->convert_sql_id($p_raid__id) . '
			WHERE isys_catg_stor_list__id = ' . $this->convert_sql_id($p_stor__id);

        return ($this->update($l_update) && $this->apply_update());
    }

    /**
     * Detach a certain raid.
     *
     * @param   integer $p_raidID
     *
     * @return  boolean
     */
    public function detach_raid($p_raidID)
    {
        $l_sql = 'UPDATE isys_catg_stor_list
			SET isys_catg_stor_list__isys_catg_raid_list__id = NULL
			WHERE isys_catg_stor_list__isys_catg_raid_list__id = ' . $this->convert_sql_id($p_raidID) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Method for removing a certain raid from something.
     *
     * @param   integer $p_stor__id
     *
     * @return  boolean
     */
    public function remove_raid_from_item($p_stor__id)
    {
        $l_update = 'UPDATE isys_catg_stor_list
			SET isys_catg_stor_list__isys_catg_raid_list__id = NULL
			WHERE isys_catg_stor_list__id = ' . $this->convert_sql_id($p_stor__id) . ';';

        return ($this->update($l_update) && $this->apply_update);
    }

    /**
     * Method for retrieving device subsets.
     *
     * @param   array $p_subset
     *
     * @return  isys_component_dao_result
     */
    public function get_device_subset($p_subset = [])
    {
        $l_query = 'SELECT * FROM isys_catg_stor_list
			LEFT JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_stor_list__isys_memory_unit__id
			WHERE FALSE';

        if (is_array($p_subset) && count($p_subset) > 0) {
            foreach ($p_subset as $l_item) {
                $l_query .= " OR isys_catg_stor_list__id = " . $this->convert_sql_id($l_item);
            }
        }

        return $this->retrieve($l_query);
    }

    /**
     * Retrieves a device name.
     *
     * @param   integer $p_id
     *
     * @return  string
     */
    public function get_device_name($p_id)
    {
        return $this->retrieve('SELECT isys_catg_stor_list__title FROM isys_catg_stor_list WHERE isys_catg_stor_list__id = ' . $this->convert_sql_id($p_id) . ';')
            ->get_row_value('isys_catg_stor_list__title');
    }

    /**
     * Builds an array with minimal requirements for the sync function.
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_import_array($p_data)
    {
        $l_model = null;
        $l_manufacturer = null;
        $l_type = null;

        if (!empty($p_data['model'])) {
            $l_model = isys_import_handler::check_dialog('isys_stor_model', $p_data['model']);
        }

        if (!empty($p_data['manufacturer'])) {
            $l_manufacturer = isys_import_handler::check_dialog('isys_stor_manufacturer', $p_data['manufacturer']);
        }

        if (!is_numeric($p_data['type']) && !empty($p_data['type'])) {
            $l_type = isys_import_handler::check_dialog('isys_stor_type', $p_data['type']);
        } elseif (is_numeric($p_data['type'])) {
            $l_type = $p_data['type'];
        }

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'title'        => [
                    C__DATA__VALUE => $p_data['title']
                ],
                'type'         => [
                    C__DATA__VALUE => $l_type
                ],
                'manufacturer' => [
                    C__DATA__VALUE => $l_manufacturer
                ],
                'model'        => [
                    C__DATA__VALUE => $l_model
                ],
                'unit'         => [
                    C__DATA__VALUE => $p_data['unit']
                ],
                'capacity'     => [
                    C__DATA__VALUE => $p_data['capacity']
                ],
                'description'  => [
                    C__DATA__VALUE => $p_data['description']
                ],
                'lto_type'     => [
                    C__DATA__VALUE => $p_data['lto_type']
                ],
                'fc_address'   => [
                    C__DATA__VALUE => $p_data['fc_address']
                ],
                'firmware'     => [
                    C__DATA__VALUE => $p_data['firmware']
                ]
            ]
        ];
    }

    /**
     * Compares category data for import.
     *
     * @param  array    $p_category_data_values
     * @param  array    $p_object_category_dataset
     * @param  array    $p_used_properties
     * @param  array    $p_comparison
     * @param  integer  $p_badness
     * @param  integer  $p_mode
     * @param  integer  $p_category_id
     * @param  string   $p_unit_key
     * @param  array    $p_category_data_ids
     * @param  mixed    $p_local_export
     * @param  boolean  $p_dataset_id_changed
     * @param  integer  $p_dataset_id
     * @param  isys_log $p_logger
     * @param  string   $p_category_name
     * @param  string   $p_table
     * @param  mixed    $p_cat_multi
     */
    public function compare_category_data(
        &$p_category_data_values,
        &$p_object_category_dataset,
        &$p_used_properties,
        &$p_comparison,
        &$p_badness,
        &$p_mode,
        &$p_category_id,
        &$p_unit_key,
        &$p_category_data_ids,
        &$p_local_export,
        &$p_dataset_id_changed,
        &$p_dataset_id,
        &$p_logger,
        &$p_category_name = null,
        &$p_table = null,
        &$p_cat_multi = null,
        &$p_category_type_id = null,
        &$p_category_ids = null,
        &$p_object_ids = null,
        &$p_already_used_data_ids = null
    ) {
        $l_title = strtolower($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['title'][C__DATA__VALUE]);
        $l_type = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['type'][C__DATA__VALUE];

        if ($l_type !== defined_or_default('C__STOR_TYPE_DEVICE_TAPE')) {
            $l_serial = strtolower($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['serial'][C__DATA__VALUE]);
            $l_byte_capacity = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['capacity']['value_converted'];
            $l_capacity_by_unit = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['capacity'][C__DATA__VALUE];
            $l_capacity_unit = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['unit']['const'];

            // @see  ID-4856  Change the parameter value, because it's a reference!
            $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['capacity'][C__DATA__VALUE] = number_format($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['capacity'][C__DATA__VALUE], 3, '.', '');

            $l_capacity = trim(($l_byte_capacity > 0 ? $l_byte_capacity : ($l_capacity_unit != '' && $l_capacity_by_unit > 0 ? isys_convert::memory(
                $l_capacity_by_unit,
                $l_capacity_unit
            ) : $l_capacity_by_unit)));

            if (strlen(substr($l_capacity, strpos($l_capacity, '.') + 1)) === 1) {
                $l_capacity .= '0';
            }

            // Use number_format instead of round, because round can display messy float values.
            $l_capacity = number_format($l_capacity, 2, '.', '');

            // Iterate through local data sets:
            foreach ($p_object_category_dataset as $l_dataset_key => $l_dataset) {
                $p_dataset_id_changed = false;
                $p_dataset_id = $l_dataset[$p_table . '__id'];

                // Ignore Devices from type Tape or if ID has already been used skip this entry
                if ($l_dataset['isys_catg_stor_list__isys_stor_type__id'] === defined_or_default('C__STOR_TYPE_DEVICE_TAPE') || isset($p_already_used_data_ids[$p_dataset_id])) {
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                    continue;
                }

                //$p_logger->debug(sprintf('Handle dataset %s.', $p_dataset_id));
                // Test the category data identifier:
                if ($p_category_data_values['data_id'] !== null) {
                    if ($p_mode === isys_import_handler_cmdb::C__USE_IDS && $p_category_data_values['data_id'] !== $p_dataset_id) {
                        //$p_logger->debug('Category data identifier is different.');
                        $p_badness[$p_dataset_id]++;
                        $p_dataset_id_changed = true;
                        if ($p_mode === isys_import_handler_cmdb::C__USE_IDS) {
                            continue;
                        }
                    }
                }

                // Use number_format instead of round, because round can display messy float values.
                $l_dataset_capacity = number_format($l_dataset['isys_catg_stor_list__capacity'], 2, '.', '');
                $l_dataset_title = strtolower($l_dataset['isys_catg_stor_list__title']);
                $l_dataset_serial = strtolower($l_dataset['isys_catg_stor_list__serial']);

                if ($l_dataset_title === $l_title && $l_dataset_serial === $l_serial && $l_dataset_capacity == $l_capacity) {
                    // Check properties
                    // We found our dataset
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;

                    return;
                } elseif ($l_dataset_serial === $l_serial && ($l_dataset_title !== $l_title || $l_dataset_capacity != $l_capacity)) {
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY][$l_dataset_key] = $p_dataset_id;
                } else {
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                }
            }
        } else {
            // Iterate through local data sets:
            foreach ($p_object_category_dataset as $l_dataset_key => $l_dataset) {
                $p_dataset_id_changed = false;
                $p_dataset_id = $l_dataset[$p_table . '__id'];

                if ($l_dataset['isys_catg_stor_list__isys_stor_type__id'] !== defined_or_default('C__STOR_TYPE_DEVICE_TAPE')) {
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                    continue;
                }

                //$p_logger->debug(sprintf('Handle dataset %s.', $p_dataset_id));
                // Test the category data identifier:
                if ($p_category_data_values['data_id'] !== null) {
                    if ($p_mode === isys_import_handler_cmdb::C__USE_IDS && $p_category_data_values['data_id'] !== $p_dataset_id &&
                        !isset($p_already_used_data_ids[$p_dataset_id])) {
                        //$p_logger->debug('Category data identifier is different.');
                        $p_badness[$p_dataset_id]++;
                        $p_dataset_id_changed = true;
                        if ($p_mode === isys_import_handler_cmdb::C__USE_IDS) {
                            continue;
                        }
                    }
                }
                if (strtolower($l_dataset['isys_catg_stor_list__title']) === $l_title && !isset($p_already_used_data_ids[$l_dataset['isys_catg_stor_list__id']])) {
                    // Check properties
                    // We found our dataset
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;

                    return;
                } else {
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                }
            }
        }
    }
}
