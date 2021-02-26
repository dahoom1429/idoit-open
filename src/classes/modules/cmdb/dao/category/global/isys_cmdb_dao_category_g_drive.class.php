<?php

use idoit\Component\Property\Type\DynamicProperty;
use idoit\Component\Property\Type\MemoryProperty;
use idoit\Component\Property\Type\TextProperty;

/**
 * i-doit
 * DAO: global category for storage drives
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_drive extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'drive';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__STORAGE_DRIVE';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function dynamic_properties()
    {
        return [
            '_capacity' => new DynamicProperty(
                'LC__CMDB_CATG__MEMORY_CAPACITY',
                'isys_catg_drive_list__id',
                'isys_catg_drive_list',
                [
                    $this,
                    'dynamic_property_callback_capacity'
                ]
            ),
            '_free_space' => new DynamicProperty(
                'LC__CMDB__CATG__DRIVE__FREE_SPACE',
                'isys_catg_drive_list__id',
                'isys_catg_drive_list',
                [
                    $this,
                    'dynamic_property_callback_free_space'
                ]
            ),
            '_used_space' => new DynamicProperty(
                'LC__CMDB__CATG__DRIVE__USED_SPACE',
                'isys_catg_drive_list__id',
                'isys_catg_drive_list',
                [
                    $this,
                    'dynamic_property_callback_used_space'
                ]
            ),
            '_on_device'  => new DynamicProperty(
                'LC__CATD__DRIVE_DEVICE',
                'isys_catg_drive_list__id',
                'isys_catg_drive_list',
                [
                    $this,
                    'retrieveOnDevice'
                ]
            )
        ];
    }

    /**
     * @param array $categoryData
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function retrieveOnDevice(array $categoryData)
    {
        if (!is_array($categoryData)) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        if (!isset($categoryData["isys_catg_drive_list__const"]) && $categoryData["isys_catg_drive_list__id"]) {
            $query = sprintf('SELECT 
                            isys_catg_drive_list__const, 
                            isys_catg_drive_list__isys_catg_raid_list__id, 
                            isys_catg_drive_list__isys_catg_ldevclient_list__id, 
                            isys_catg_drive_list__isys_catg_stor_list__id 
                        FROM %s WHERE %s = %s', $this->m_table, 'isys_catg_drive_list__id', $categoryData['isys_catg_drive_list__id']);
            $categoryData = $this->retrieve($query)
                ->get_row();
        } else {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        switch ($categoryData["isys_catg_drive_list__const"]) {
            case 'C__CATG__RAID':
                $id = $categoryData["isys_catg_drive_list__isys_catg_raid_list__id"];
                $property = isys_cmdb_dao_category_g_raid::instance(isys_application::instance()->container->database)
                    ->get_property_by_key('title');
                break;
            case 'C__CATG__LDEV_CLIENT':
                $id = $categoryData["isys_catg_drive_list__isys_catg_ldevclient_list__id"];
                $property = isys_cmdb_dao_category_g_ldevclient::instance(isys_application::instance()->container->database)
                    ->get_property_by_key('title');
                break;
            case 'C__CATG__STORAGE':
                $id = $categoryData["isys_catg_drive_list__isys_catg_stor_list__id"];
                $property = isys_cmdb_dao_category_g_stor::instance(isys_application::instance()->container->database)
                    ->get_property_by_key('title');
                break;
            default:
                return isys_tenantsettings::get('gui.empty_value', '-');
                break;
        }

        if (!$id) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        /**
         * @var $selectObject \idoit\Module\Report\SqlQuery\Structure\SelectSubSelect
         */
        $selectObject = $property[C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT];
        $dataField = $property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
        $query = $selectObject->getSelectQuery() . ' WHERE ' . $selectObject->getSelectPrimaryKey() . ' = ' . $this->convert_sql_id($id);

        return $this->retrieve($query)
            ->get_row_value($dataField);
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_catd_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function _get_data($p_catd_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT isys_catg_drive_list.*, isys_obj.*, isys_stor_raid_level.*, isys_filesystem_type.*, isys_memory_unit.*, isys_catd_drive_type.* " .
            "isys_catg_stor_list.*, isys_catg_raid_list.*, isys_catg_ldevclient_list.*, " . "FROM isys_catg_drive_list " .
            "LEFT OUTER JOIN isys_obj ON isys_catg_drive_list__isys_obj__id = isys_obj__id " .
            "LEFT OUTER JOIN isys_stor_raid_level ON isys_stor_raid_level__id = isys_catg_drive_list__isys_stor_raid_level__id " .
            "LEFT JOIN isys_filesystem_type ON isys_filesystem_type__id = isys_catg_drive_list__isys_filesystem_type__id " .
            "LEFT JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_drive_list__isys_memory_unit__id " .

            "LEFT JOIN isys_catd_drive_type ON isys_catd_drive_type__id = isys_catg_drive_list__isys_catd_drive_type__id " .
            "LEFT JOIN isys_catg_stor_list ON isys_catg_stor_list__id = isys_catg_drive_list__isys_catg_stor_list__id " .
            "LEFT JOIN isys_catg_raid_list ON isys_catg_raid_list__id = isys_catg_drive_list__isys_catg_raid_list__id " .
            "LEFT JOIN isys_catg_ldevclient_list ON isys_catg_ldevclient_list__id = isys_catg_drive_list__isys_catg_ldevclient_list__id " . 'WHERE TRUE ' . $p_condition .
            $this->prepare_filter($p_filter) . ' ';

        if ($p_catd_list_id !== null) {
            $l_sql .= " AND isys_catg_drive_list__id = " . $this->convert_sql_id($p_catd_list_id);
        }

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_catg_drive_list__status = " . $this->convert_sql_id($p_status);
        }

        return $this->retrieve($l_sql . ';');
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
            'mount_point'     => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DRIVE_DRIVELETTER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Driveletter'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_drive_list__driveletter',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_drive_list__driveletter FROM isys_catg_drive_list',
                        'isys_catg_drive_list',
                        'isys_catg_drive_list__id',
                        'isys_catg_drive_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_drive_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATD__DRIVE_LETTER'
                ]
            ]),
            'title'           => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_drive_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_drive_list__title FROM isys_catg_drive_list',
                        'isys_catg_drive_list',
                        'isys_catg_drive_list__id',
                        'isys_catg_drive_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_drive_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATD__DRIVE_TITLE'
                ]
            ]),
            'system_drive'    => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DRIVE__SYSTEM_DRIVE',
                    C__PROPERTY__INFO__DESCRIPTION => 'System drive'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_drive_list__system_drive',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_catg_drive_list__system_drive = \'1\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__YES') . '
                                WHEN isys_catg_drive_list__system_drive = \'0\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__NO') . ' END)
                             FROM isys_catg_drive_list',
                        'isys_catg_drive_list',
                        'isys_catg_drive_list__id',
                        'isys_catg_drive_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_drive_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_drive_list', 'LEFT', 'isys_catg_drive_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__DRIVE__SYSTEM_DRIVE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData'     => get_smarty_arr_YES_NO(),
                        'p_bDbFieldNN' => 1
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_yes_or_no'
                    ]
                ]
            ]),
            'filesystem'      => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'isys_filesystem_type',
                    C__PROPERTY__INFO__DESCRIPTION => 'Filesystem'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_drive_list__isys_filesystem_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_filesystem_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_filesystem_type',
                        'isys_filesystem_type__id',
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_filesystem_type__title
                            FROM isys_catg_drive_list
                            INNER JOIN isys_filesystem_type ON isys_filesystem_type__id = isys_catg_drive_list__isys_filesystem_type__id',
                        'isys_catg_drive_list',
                        'isys_catg_drive_list__id',
                        'isys_catg_drive_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_drive_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_drive_list', 'LEFT', 'isys_catg_drive_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_filesystem_type',
                            'LEFT',
                            'isys_catg_drive_list__isys_filesystem_type__id',
                            'isys_filesystem_type__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATD__DRIVE_FILESYSTEM',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_filesystem_type'
                    ]
                ]
            ]),
            'capacity'        => new MemoryProperty(
                'C__CATD__DRIVE_CAPACITY',
                'LC__CMDB_CATG__MEMORY_CAPACITY',
                'isys_catg_drive_list__capacity',
                'isys_catg_drive_list',
                'unit'
            ),
            'unit'            => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__MEMORY_UNIT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Unit'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_drive_list__isys_memory_unit__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_memory_unit',
                    C__PROPERTY__DATA__FIELD_ALIAS  => 'capacity_unit',
                    C__PROPERTY__DATA__TABLE_ALIAS  => 'c_unit',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_memory_unit',
                        'isys_memory_unit__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_memory_unit__title
                                    FROM isys_catg_drive_list
                                    INNER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_drive_list__isys_memory_unit__id',
                        'isys_catg_drive_list',
                        'isys_catg_drive_list__id',
                        'isys_catg_drive_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_drive_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_drive_list', 'LEFT', 'isys_catg_drive_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_drive_list__isys_memory_unit__id',
                            'isys_memory_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATD__DRIVE_UNIT',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_memory_unit',
                        'p_strClass' => 'input-mini',
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'serial'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SERIAL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Serial number'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_drive_list__serial',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_drive_list__serial FROM isys_catg_drive_list',
                        'isys_catg_drive_list',
                        'isys_catg_drive_list__id',
                        'isys_catg_drive_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_drive_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATD__DRIVE_SERIAL'
                ]
            ]),
            'assigned_raid'   => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATD_DRIVE_TYPE__RAID_GROUP',
                    C__PROPERTY__INFO__DESCRIPTION => 'Software RAID group'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_drive_list__id__raid_pool',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_raid_list',
                        'isys_catg_raid_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_raid_list__title
                            FROM isys_catg_drive_list
                            INNER JOIN isys_catg_raid_list ON isys_catg_raid_list__id = isys_catg_drive_list__id__raid_pool',
                        'isys_catg_drive_list',
                        'isys_catg_drive_list__id',
                        'isys_catg_drive_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_drive_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_drive_list', 'LEFT', 'isys_catg_drive_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_raid_list',
                            'LEFT',
                            'isys_catg_drive_list__id__raid_pool',
                            'isys_catg_raid_list__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATD__DRIVE_RAIDGROUP',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_drive',
                            'callback_property_assigned_raid'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_reference_value'
                    ]
                ]
            ]),
            'drive_type'      => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Typ'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_drive_list__isys_catd_drive_type__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catd_drive_type',
                        'isys_catd_drive_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catd_drive_type__title
                            FROM isys_catg_drive_list
                            INNER JOIN isys_catd_drive_type ON isys_catd_drive_type__id = isys_catg_drive_list__isys_catd_drive_type__id',
                        'isys_catg_drive_list',
                        'isys_catg_drive_list__id',
                        'isys_catg_drive_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_drive_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_drive_list', 'LEFT', 'isys_catg_drive_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catd_drive_type',
                            'LEFT',
                            'isys_catg_drive_list__isys_catd_drive_type__id',
                            'isys_catd_drive_type__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATD__DRIVE_TYPE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ]),
            'device'          => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATD__DRIVE_DEVICE',
                    C__PROPERTY__INFO__DESCRIPTION => 'On device'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_drive_list__isys_catg_stor_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_stor_list',
                        'isys_catg_stor_list__id'
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATD__DRIVE_DEVICE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_drive',
                            'callback_property_devices'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__VIRTUAL   => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_reference_value'
                    ]
                ]
            ]),
            'raid'            => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATD__DRIVE_DEVICE_RAID_ARRAY',
                    C__PROPERTY__INFO__DESCRIPTION => 'On device Raid-Array'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_drive_list__isys_catg_raid_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_raid_list',
                        'isys_catg_raid_list__id'
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATD__DRIVE_DEVICE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_drive',
                            'callback_property_devices'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__VIRTUAL   => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_reference_value'
                    ]
                ]
            ]),
            'ldev'            => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATD__DRIVE_DEVICE_LOGICAL_DEVICES_CLIENT',
                    C__PROPERTY__INFO__DESCRIPTION => 'On device Logical devices (Client)'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_drive_list__isys_catg_ldevclient_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_ldevclient_list',
                        'isys_catg_ldevclient_list__id'
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATD__DRIVE_DEVICE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_drive',
                            'callback_property_devices'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__VIRTUAL   => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_reference_value'
                    ]
                ]
            ]),
            'category_const'  => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__OBJTYPE__CONST',
                    C__PROPERTY__INFO__DESCRIPTION => 'Constant'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_drive_list__const'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__DRIVE__CATEGORY_CONST'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__VIRTUAL   => true
                ]
            ]),
            'free_space'      => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DRIVE__FREE_SPACE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Free space'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_drive_list__free_space',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(\'{mem\', \',\', isys_catg_drive_list__free_space , \',\', isys_memory_unit__title, \'}\')
                            FROM isys_catg_drive_list
                            INNER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_drive_list__free_space__isys_memory_unit__id',
                        'isys_catg_drive_list',
                        'isys_catg_drive_list__id',
                        'isys_catg_drive_list__isys_obj__id',
                        '',
                        '',
                        \idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory(['isys_catg_drive_list__free_space > 0']),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_drive_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_drive_list', 'LEFT', 'isys_catg_drive_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_drive_list__free_space__isys_memory_unit__id',
                            'isys_memory_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__DRIVE__FREE_SPACE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-medium'
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['memory']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'free_space_unit',
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                ]
            ]),
            'free_space_unit' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__MEMORY_UNIT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Unit'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_drive_list__free_space__isys_memory_unit__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_memory_unit',
                    C__PROPERTY__DATA__FIELD_ALIAS  => 'free_space_unit',
                    C__PROPERTY__DATA__TABLE_ALIAS  => 'fs_unit',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_memory_unit',
                        'isys_memory_unit__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_memory_unit__title
                                    FROM isys_catg_drive_list
                                    INNER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_drive_list__free_space__isys_memory_unit__id',
                        'isys_catg_drive_list',
                        'isys_catg_drive_list__id',
                        'isys_catg_drive_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_drive_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_drive_list', 'LEFT', 'isys_catg_drive_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_drive_list__free_space__isys_memory_unit__id',
                            'isys_memory_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__DRIVE__FREE_SPACE_UNIT',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_memory_unit',
                        'p_strClass' => 'input-mini',
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'used_space'      => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DRIVE__USED_SPACE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Free space'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_drive_list__used_space',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(\'{mem\', \',\', isys_catg_drive_list__used_space  , \',\', isys_memory_unit__title, \'}\')
                            FROM isys_catg_drive_list
                            INNER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_drive_list__used_space__isys_memory_unit__id',
                        'isys_catg_drive_list',
                        'isys_catg_drive_list__id',
                        'isys_catg_drive_list__isys_obj__id',
                        '',
                        '',
                        \idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory(['isys_catg_drive_list__used_space > 0']),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_drive_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_drive_list', 'LEFT', 'isys_catg_drive_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_drive_list__used_space__isys_memory_unit__id',
                            'isys_memory_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__DRIVE__USED_SPACE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-medium'
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['memory']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'used_space_unit',
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                ]
            ]),
            'used_space_unit' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__MEMORY_UNIT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Unit'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_drive_list__used_space__isys_memory_unit__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_memory_unit',
                    C__PROPERTY__DATA__FIELD_ALIAS  => 'used_space_unit',
                    C__PROPERTY__DATA__TABLE_ALIAS  => 'us_unit',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_memory_unit',
                        'isys_memory_unit__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_memory_unit__title
                                    FROM isys_catg_drive_list
                                    INNER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_drive_list__used_space__isys_memory_unit__id',
                        'isys_catg_drive_list',
                        'isys_catg_drive_list__id',
                        'isys_catg_drive_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_drive_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_drive_list', 'LEFT', 'isys_catg_drive_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_drive_list__used_space__isys_memory_unit__id',
                            'isys_memory_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__DRIVE__USED_SPACE_UNIT',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_memory_unit',
                        'p_strClass' => 'input-mini',
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'firmware'     => new TextProperty(
                'C__CATG__DRIVE_FIRMWARE',
                'LC__CMDB__CATG__DRIVE__FIRMWARE',
                'isys_catg_drive_list__firmware',
                'isys_catg_drive_list'
            ),
            'description'     => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_drive_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_drive_list__description FROM isys_catg_drive_list',
                        'isys_catg_drive_list',
                        'isys_catg_drive_list__id',
                        'isys_catg_drive_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_drive_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__DRIVE', 'C__CATG__DRIVE')
                ]
            ])
        ];
    }

    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0) {
                        return $this->create(
                            $p_object_id,
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['drive_type'][C__DATA__VALUE],
                            $p_category_data['properties']['mount_point'][C__DATA__VALUE],
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['filesystem'][C__DATA__VALUE],
                            $p_category_data['properties']['capacity'][C__DATA__VALUE],
                            $p_category_data['properties']['unit'][C__DATA__VALUE],
                            $p_category_data['properties']['device'][C__DATA__VALUE],
                            $p_category_data['properties']['raid'][C__DATA__VALUE],
                            null,
                            $p_category_data['properties']['ldev'][C__DATA__VALUE],
                            $p_category_data['properties']['category_const'][C__DATA__VALUE],
                            $p_category_data['properties']['assigned_raid'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            $p_category_data['properties']['system_drive'][C__DATA__VALUE],
                            $p_category_data['properties']['serial'][C__DATA__VALUE],
                            $p_category_data['properties']['free_space'][C__DATA__VALUE],
                            $p_category_data['properties']['free_space_unit'][C__DATA__VALUE],
                            $p_category_data['properties']['used_space'][C__DATA__VALUE],
                            $p_category_data['properties']['used_space_unit'][C__DATA__VALUE],
                            $p_category_data['properties']['firmware'][C__DATA__VALUE]
                        );
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $this->save(
                            $p_category_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['drive_type'][C__DATA__VALUE],
                            $p_category_data['properties']['mount_point'][C__DATA__VALUE],
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['filesystem'][C__DATA__VALUE],
                            $p_category_data['properties']['capacity'][C__DATA__VALUE],
                            $p_category_data['properties']['unit'][C__DATA__VALUE],
                            $p_category_data['properties']['device'][C__DATA__VALUE],
                            $p_category_data['properties']['raid'][C__DATA__VALUE],
                            null,
                            $p_category_data['properties']['ldev'][C__DATA__VALUE],
                            $p_category_data['properties']['category_const'][C__DATA__VALUE],
                            $p_category_data['properties']['assigned_raid'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            $p_category_data['properties']['system_drive'][C__DATA__VALUE],
                            $p_category_data['properties']['serial'][C__DATA__VALUE],
                            $p_category_data['properties']['free_space'][C__DATA__VALUE],
                            $p_category_data['properties']['free_space_unit'][C__DATA__VALUE],
                            $p_category_data['properties']['used_space'][C__DATA__VALUE],
                            $p_category_data['properties']['used_space_unit'][C__DATA__VALUE],
                            $p_category_data['properties']['firmware'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * Dynamic property handling for getting the formatted drive capacities.
     *
     * @param $rowData
     *
     * @return string
     */
    public function dynamic_property_callback_capacity($rowData)
    {
        return isys_convert::retrieveFormattedMemoryByDao($rowData, $this, '__capacity');
    }

    /**
     * Dynamic property handling for getting the formatted drive free space.
     *
     * @param $rowData
     *
     * @return string
     */
    public function dynamic_property_callback_free_space($rowData)
    {
        return $this->callbackForDriveMemoryProperties('isys_catg_drive_list__free_space', 'isys_catg_drive_list__free_space__isys_memory_unit__id', $rowData);
    }

    /**
     * Dynamic property handling for getting the formatted drive used space.
     *
     * @param $rowData
     *
     * @return string
     */
    public function dynamic_property_callback_used_space($rowData)
    {
        return $this->callbackForDriveMemoryProperties('isys_catg_drive_list__used_space', 'isys_catg_drive_list__used_space__isys_memory_unit__id', $rowData);
    }

    /**
     * Dynamic method to format the memory unit and value for report
     * isys_convert::retrieveFormattedMemoryByDao can't be used for used_space and free_space because of the different column notation
     *
     * @param   string $columnName
     * @param   string $columnNameUnit
     * @param   array  $rowData
     *
     * @return  string  $return
     */
    private function callbackForDriveMemoryProperties($columnName, $columnNameUnit, $rowData)
    {
        if (isset($rowData['isys_catg_drive_list__id'])) {
            $driveId = $rowData['isys_catg_drive_list__id'];

            $driveResult = $this->get_data($driveId);

            if (is_countable($driveResult) && count($driveResult) > 0) {
                $driveRow = $driveResult->get_row();

                if ($driveRow[$columnName] > 0 && isset($driveRow[$columnNameUnit])) {
                    return isys_convert::formatNumber(isys_convert::memory($driveRow[$columnName], $driveRow[$columnNameUnit], C__CONVERT_DIRECTION__BACKWARD)) . ' ' .
                        isys_factory_cmdb_dialog_dao::get_instance('isys_memory_unit', isys_application::instance()->container->database)
                            ->get_data($driveRow[$columnNameUnit])['isys_memory_unit__title'];
                }
            }
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Callback method for the "catdata" browser. Maybe we can switch the first parameter to an instance of isys_request?
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function catdata_browser($p_obj_id)
    {
        $l_return = [];
        $l_res = $this->get_data(null, $p_obj_id, "", null, C__RECORD_STATUS__NORMAL);

        while ($l_row = $l_res->get_row()) {
            $l_val = '<strong>' . $l_row['isys_catg_drive_list__title'] . '</strong>';

            if (!empty($l_row['isys_catg_drive_list__driveletter'])) {
                $l_val .= ' (' . $l_row['isys_catg_drive_list__driveletter'] . ')';
            }

            if (!empty($l_row['isys_filesystem_type__title'])) {
                $l_val .= ', ' . $l_row['isys_filesystem_type__title'];
            }

            $l_capacity = isys_convert::memory($l_row["isys_catg_drive_list__capacity"], $l_row["isys_memory_unit__const"], C__CONVERT_DIRECTION__BACKWARD);

            if ($l_capacity > 0) {
                $l_val .= ', ' . $l_capacity . ' ' . $l_row['isys_memory_unit__title'];
            }

            $l_return[$l_row['isys_catg_drive_list__id']] = $l_val;
        }

        return $l_return;
    }

    /**
     * Callback method for the device dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_devices(isys_request $p_request)
    {
        $l_return = [];

        $l_db = $this->get_database_component();

        $l_storage_res = isys_cmdb_dao_category_g_stor::instance($l_db)
            ->get_data(null, $p_request->get_object_id());
        $l_raid_dao = isys_cmdb_dao_category_g_raid::instance($l_db)
            ->get_data(null, $p_request->get_object_id());
        $l_ldev_dao = isys_cmdb_dao_category_g_ldevclient::instance($l_db)
            ->get_data(null, $p_request->get_object_id());

        while ($l_row = $l_storage_res->get_row()) {
            $l_return[$l_row['isys_catg_stor_list__id'] . '_C__CATG__STORAGE'] = $l_row['isys_catg_stor_list__title'];
        }

        while ($l_row = $l_raid_dao->get_row()) {
            $l_return[$l_row['isys_catg_raid_list__id'] . '_C__CATG__RAID'] = $l_row['isys_catg_raid_list__title'];
        }

        while ($l_row = $l_ldev_dao->get_row()) {
            $l_return[$l_row['isys_catg_ldevclient_list__id'] . '_C__CATG__LDEV_CLIENT'] = $l_row['isys_catg_ldevclient_list__title'];
        }

        return $l_return;
    }

    /**
     * Callback method for the assigned raid dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_assigned_raid(isys_request $p_request)
    {
        $l_return = [];

        $l_dao_raid = new isys_cmdb_dao_category_g_raid($this->get_database_component());
        $l_res = $l_dao_raid->get_raids(null, $l_dao_raid->get_raid_type_by_const("C__CMDB__RAID_TYPE__SOFTWARE"), $p_request->get_object_id());

        while ($l_row = $l_res->get_row()) {
            $l_return[$l_row["isys_catg_raid_list__id"]] = $l_row["isys_catg_raid_list__title"];
        }

        return $l_return;
    }

    public function create(
        $p_objID,
        $p_newRecStatus,
        $p_typeID = null,
        $p_letter = null,
        $p_title = null,
        $p_fsID = null,
        $p_capacity = null,
        $p_unitID = null,
        $p_deviceID = null,
        $p_raidID = null,
        $p_raidLevelID = null,
        $p_ldevclientID = null,
        $p_const = null,
        $p_software_raid = null,
        $p_description = null,
        $p_system_drive = null,
        $p_serial = null,
        $p_free_space = null,
        $p_free_space_unit = null,
        $p_used_space = null,
        $p_used_space_unit = null,
        $p_firmware = null
    ) {
        $p_capacity = isys_convert::memory($p_capacity, $p_unitID);
        $p_free_space = isys_convert::memory($p_free_space, $p_free_space_unit);
        $p_used_space = isys_convert::memory($p_used_space, $p_used_space_unit);

        if (is_numeric($p_const)) {
            foreach ([
                         'C__CATG__STORAGE',
                         'C__CATG__RAID',
                         'C__CATG__LDEV_CLIENT'
                     ] as $constant) {
                if ($p_const == defined_or_default($constant)) {
                    $p_const = $constant;
                    break;
                }
            }
        }

        $l_update = "INSERT INTO isys_catg_drive_list SET 
            isys_catg_drive_list__isys_stor_raid_level__id = " . $this->convert_sql_id($p_raidLevelID) . ",
            isys_catg_drive_list__isys_filesystem_type__id = " . $this->convert_sql_id($p_fsID) . ",
            isys_catg_drive_list__isys_memory_unit__id = " . $this->convert_sql_id($p_unitID) . ",
            isys_catg_drive_list__isys_catd_drive_type__id = " . $this->convert_sql_id($p_typeID) . ",
            isys_catg_drive_list__title = " . $this->convert_sql_text($p_title) . ",
            isys_catg_drive_list__capacity = " . $this->convert_sql_text($p_capacity) . ",
            isys_catg_drive_list__firmware = " . $this->convert_sql_text($p_firmware) . ",
            isys_catg_drive_list__description = " . $this->convert_sql_text($p_description) . ",
            isys_catg_drive_list__driveletter = " . $this->convert_sql_text($p_letter) . ",
            isys_catg_drive_list__isys_catg_stor_list__id = " . $this->convert_sql_id($p_deviceID) . ",
            isys_catg_drive_list__isys_catg_raid_list__id = " . $this->convert_sql_id($p_raidID) . ",
            isys_catg_drive_list__isys_catg_ldevclient_list__id = " . $this->convert_sql_id($p_ldevclientID) . ",
            isys_catg_drive_list__status = " . $this->convert_sql_id($p_newRecStatus) . ",
            isys_catg_drive_list__const = " . $this->convert_sql_text($p_const) . ",
            isys_catg_drive_list__id__raid_pool = " . $this->convert_sql_id($p_software_raid) . ",
            isys_catg_drive_list__system_drive = " . $this->convert_sql_int($p_system_drive) . ",
            isys_catg_drive_list__serial = " . $this->convert_sql_text($p_serial) . ",
            isys_catg_drive_list__free_space = " . $this->convert_sql_text($p_free_space) . ",
            isys_catg_drive_list__free_space__isys_memory_unit__id = " . $this->convert_sql_id($p_free_space_unit) . ",
            isys_catg_drive_list__used_space = " . $this->convert_sql_text($p_used_space) . ",
            isys_catg_drive_list__used_space__isys_memory_unit__id = " . $this->convert_sql_id($p_used_space_unit) . ",
            isys_catg_drive_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ";";

        if ($this->update($l_update) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    public function save(
        $p_catLevel,
        $p_newRecStatus,
        $p_typeID = null,
        $p_letter = null,
        $p_title = null,
        $p_fsID = null,
        $p_capacity = null,
        $p_unitID = null,
        $p_deviceID = null,
        $p_raidID = null,
        $p_raidLevelID = null,
        $p_ldevclientID = null,
        $p_const = null,
        $p_software_raid = null,
        $p_description = null,
        $p_system_drive = null,
        $p_serial = null,
        $p_free_space = null,
        $p_free_space_unit = null,
        $p_used_space = null,
        $p_used_space_unit = null,
        $p_firmware = null
    ) {
        $p_capacity = isys_convert::memory($p_capacity, $p_unitID);
        $p_free_space = isys_convert::memory($p_free_space, $p_free_space_unit);
        $p_used_space = isys_convert::memory($p_used_space, $p_used_space_unit);

        if (is_numeric($p_const)) {
            foreach ([
                         'C__CATG__STORAGE',
                         'C__CATG__RAID',
                         'C__CATG__LDEV_CLIENT'
                     ] as $constant) {
                if ($p_const == defined_or_default($constant)) {
                    $p_const = $constant;
                    break;
                }
            }
        }

        $l_update = "UPDATE isys_catg_drive_list SET 
            isys_catg_drive_list__isys_stor_raid_level__id = " . $this->convert_sql_id($p_raidLevelID) . ",
            isys_catg_drive_list__isys_filesystem_type__id = " . $this->convert_sql_id($p_fsID) . ",
            isys_catg_drive_list__isys_memory_unit__id = " . $this->convert_sql_id($p_unitID) . ",
            isys_catg_drive_list__isys_catd_drive_type__id = " . $this->convert_sql_id($p_typeID) . ",
            isys_catg_drive_list__title = " . $this->convert_sql_text($p_title) . ",
            isys_catg_drive_list__capacity = " . $this->convert_sql_text($p_capacity) . ",
            isys_catg_drive_list__firmware = " . $this->convert_sql_text($p_firmware) . ",
            isys_catg_drive_list__description = " . $this->convert_sql_text($p_description) . ",
            isys_catg_drive_list__driveletter = " . $this->convert_sql_text($p_letter) . ",
            isys_catg_drive_list__const = " . $this->convert_sql_text($p_const) . ",
            isys_catg_drive_list__isys_catg_stor_list__id = " . $this->convert_sql_id($p_deviceID) . ",
            isys_catg_drive_list__isys_catg_raid_list__id = " . $this->convert_sql_id($p_raidID) . ",
            isys_catg_drive_list__isys_catg_ldevclient_list__id = " . $this->convert_sql_id($p_ldevclientID) . ",
            isys_catg_drive_list__system_drive = " . $this->convert_sql_int($p_system_drive) . ",
            isys_catg_drive_list__serial = " . $this->convert_sql_text($p_serial) . ",
            isys_catg_drive_list__id__raid_pool = " . $this->convert_sql_id($p_software_raid) . ",
            isys_catg_drive_list__free_space = " . $this->convert_sql_text($p_free_space) . ",
            isys_catg_drive_list__free_space__isys_memory_unit__id = " . $this->convert_sql_id($p_free_space_unit) . ",
            isys_catg_drive_list__used_space = " . $this->convert_sql_text($p_used_space) . ",
            isys_catg_drive_list__used_space__isys_memory_unit__id = " . $this->convert_sql_id($p_used_space_unit) . ",
            isys_catg_drive_list__status = " . $this->convert_sql_id($p_newRecStatus) . " 
            WHERE isys_catg_drive_list__id = " . $this->convert_sql_id($p_catLevel) . ";";

        if ($this->update($l_update)) {
            return $this->apply_update();
        } else {
            return false;
        }
    }

    /**
     * Import-Handler for this category
     *
     * @author Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function import($p_data, $p_object_id)
    {
        if (is_array($p_data) && count($p_data)) {
            foreach ($p_data as $drive) {
                $id = $this->get_data_by_object($p_object_id, ' AND isys_catg_drive_list__title = ' . $this->convert_sql_text($drive['name']))
                    ->get_row_value('isys_catg_drive_list__id');

                if (!$id) {
                    $id = $this->create_connector('isys_catg_drive_list', $p_object_id);
                }

                $this->save(
                    $id,
                    C__RECORD_STATUS__NORMAL,
                    1, // C__CATD_DRIVE_TYPE__PARTION
                    str_replace('Partition ', '', $drive['name']),
                    $drive['name'],
                    isset($drive['filesystem']) && $drive['filesystem'] ? isys_import::check_dialog('isys_filesystem_type', $drive['filesystem']) : null,
                    $drive['size'] / 1024,
                    defined_or_default('C__MEMORY_UNIT__GB', 3),
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    'Free space: ' . $drive['freespace'],
                    strstr($drive['name'], 'C:') ? true : false
                );
            }
        }

        return true;
    }

    /**
     * @param      $p_cat_level
     * @param      $p_intOldRecStatus
     * @param bool $p_create
     *
     * @return bool|int
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_catdata = $this->get_result()
            ->__to_array();

        $l_drive_device = substr($_POST['C__CATD__DRIVE_DEVICE'], 0, strpos($_POST['C__CATD__DRIVE_DEVICE'], "_"));
        $l_const = substr($_POST['C__CATD__DRIVE_DEVICE'], strpos($_POST['C__CATD__DRIVE_DEVICE'], "_") + 1, strlen($_POST['C__CATD__DRIVE_DEVICE']));

        if (isset($_GET[C__CMDB__GET__CATLEVEL]) && $_GET[C__CMDB__GET__CATLEVEL] > 0) {
            $l_id = $_GET[C__CMDB__GET__CATLEVEL];
        } else {
            $l_id = $l_catdata["isys_catg_drive_list__id"];
        }

        if ($l_const == "C__CATG__STORAGE") {
            $l_device_id = $l_drive_device;
            $l_raid_id = null;
            $l_ldevclient_id = null;
        } elseif ($l_const == "C__CATG__RAID") {
            $l_device_id = null;
            $l_raid_id = $l_drive_device;
            $l_ldevclient_id = null;
        } elseif ($l_const == "C__CATG__LDEV_CLIENT") {
            $l_device_id = null;
            $l_raid_id = null;
            $l_ldevclient_id = $l_drive_device;
        } elseif ($_POST['C__CATD__DRIVE_DEVICE'] == -1) {
            $l_device_id = null;
            $l_raid_id = null;
            $l_ldevclient_id = null;
            $l_const = null;
        } else {
            $l_device_id = $l_catdata["isys_catg_drive_list__isys_catg_stor_list__id"];
            $l_raid_id = $l_catdata["isys_catg_drive_list__isys_catg_raid_list__id"];
            $l_ldevclient_id = $l_catdata["isys_catg_drive_list__isys_catg_ldevclient_list__id"];
            $l_const = $l_catdata["isys_catg_drive_list__const"];
        }

        if ($p_create) {
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST["C__CATD__DRIVE_TYPE"],
                $_POST["C__CATD__DRIVE_LETTER"],
                $_POST["C__CATD__DRIVE_TITLE"],
                $_POST["C__CATD__DRIVE_FILESYSTEM"],
                $_POST["C__CATD__DRIVE_CAPACITY"],
                $_POST["C__CATD__DRIVE_UNIT"],
                $l_device_id,
                $l_raid_id,
                $_POST["C__CATD__DRIVE_RAIDLEVEL"],
                $l_ldevclient_id,
                $l_const,
                $_POST["C__CATD__SOFTWARE_RAID"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST["C__CATG__DRIVE__SYSTEM_DRIVE"],
                $_POST["C__CATD__DRIVE_SERIAL"],
                $_POST["C__CMDB__CATG__DRIVE__FREE_SPACE"],
                $_POST["C__CMDB__CATG__DRIVE__FREE_SPACE_UNIT"],
                $_POST["C__CMDB__CATG__DRIVE__USED_SPACE"],
                $_POST["C__CMDB__CATG__DRIVE__USED_SPACE_UNIT"],
                $_POST["C__CATG__DRIVE_FIRMWARE"]
            );

            if ($l_id) {
                $this->m_strLogbookSQL = $this->get_last_query();
                $p_cat_level = -1;

                return $l_id;
            }
        } else {
            $l_bRet = $this->save(
                $l_id,
                C__RECORD_STATUS__NORMAL,
                $_POST["C__CATD__DRIVE_TYPE"],
                $_POST["C__CATD__DRIVE_LETTER"],
                $_POST["C__CATD__DRIVE_TITLE"],
                $_POST["C__CATD__DRIVE_FILESYSTEM"],
                $_POST["C__CATD__DRIVE_CAPACITY"],
                $_POST["C__CATD__DRIVE_UNIT"],
                $l_device_id,
                $l_raid_id,
                $_POST["C__CATD__DRIVE_RAIDLEVEL"],
                $l_ldevclient_id,
                $l_const,
                $_POST["C__CATD__SOFTWARE_RAID"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST["C__CATG__DRIVE__SYSTEM_DRIVE"],
                $_POST["C__CATD__DRIVE_SERIAL"],
                $_POST["C__CMDB__CATG__DRIVE__FREE_SPACE"],
                $_POST["C__CMDB__CATG__DRIVE__FREE_SPACE_UNIT"],
                $_POST["C__CMDB__CATG__DRIVE__USED_SPACE"],
                $_POST["C__CMDB__CATG__DRIVE__USED_SPACE_UNIT"],
                $_POST["C__CATG__DRIVE_FIRMWARE"]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        return $l_bRet;
    }

    public function get_devices($p_objID)
    {
        $l_return = [];

        $l_sql = 'SELECT isys_catg_stor_list__id, isys_catg_stor_list__title FROM isys_catg_stor_list
			WHERE isys_catg_stor_list__isys_obj__id = ' . $this->convert_sql_id($p_objID) . '
			AND isys_catg_stor_list__id__raid_pool IS NULL;';

        $l_res = $this->retrieve($l_sql);

        if (is_countable($l_res) && count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                $l_return[$l_row['isys_catg_stor_list__id']] = $l_row['isys_catg_stor_list__title'];
            }
        }

        return $l_return;
    }

    /**
     * get all drives in this object which have the type raid-group.
     *
     * @param   integer $p_object_id
     *
     * @return  array
     */
    public function get_raid_groups($p_object_id)
    {
        $l_return = [];

        $l_sql = 'SELECT isys_catg_drive_list__id, isys_catg_drive_list__title FROM isys_catg_drive_list
			INNER JOIN isys_catd_drive_type ON isys_catg_drive_list__isys_catd_drive_type__id = isys_catd_drive_type__id
			WHERE isys_catd_drive_type__const = "C__CATD_DRIVE_TYPE__RAID_GROUP"
			AND isys_catg_drive_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ';';

        $l_res = $this->retrieve($l_sql);

        if (is_countable($l_res) && count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                $l_return[$l_row['isys_catg_drive_list__id']] = $l_row['isys_catg_drive_list__title'];
            }
        }

        return $l_return;
    }

    /**
     * Gets drives with more information.
     *
     * @param   integer $p_raid_id
     * @param   integer $p_obj__id
     * @param   string  $p_condition
     * @param   boolean $p_show_raid
     *
     * @return  isys_component_dao_result
     */
    public function get_drives($p_raid_id = null, $p_obj__id = null, $p_condition = null, $p_show_raid = false)
    {
        $l_sql = 'SELECT * FROM isys_catg_drive_list
			LEFT OUTER JOIN isys_stor_raid_level ON isys_stor_raid_level__id = isys_catg_drive_list__isys_stor_raid_level__id
			LEFT JOIN isys_filesystem_type ON isys_filesystem_type__id = isys_catg_drive_list__isys_filesystem_type__id
			LEFT JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_drive_list__isys_memory_unit__id
			LEFT JOIN isys_catd_drive_type ON isys_catd_drive_type__id = isys_catg_drive_list__isys_catd_drive_type__id
			WHERE isys_catg_drive_list__isys_catd_drive_type__id = 1 ' . $p_condition;

        if ($p_raid_id != null) {
            $l_sql .= ' AND isys_catg_drive_list__id__raid_pool = ' . $this->convert_sql_id($p_raid_id);
        } elseif (!$p_show_raid) {
            $l_sql .= ' AND isys_catg_drive_list__id__raid_pool IS NULL';
        }

        if ($p_obj__id !== null) {
            $l_sql .= ' AND isys_catg_drive_list__isys_obj__id = ' . $this->convert_sql_id($p_obj__id);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Adds drive to raid pool.
     *
     * @param   integer $p_drive__id
     * @param   integer $p_raid__id
     *
     * @return  boolean
     */
    public function add_raid_to_item($p_drive__id, $p_raid__id)
    {
        $l_update = 'UPDATE isys_catg_drive_list SET isys_catg_drive_list__id__raid_pool = ' . $this->convert_sql_id($p_raid__id) . '
			WHERE isys_catg_drive_list__id = ' . $this->convert_sql_id($p_drive__id);

        return $this->update($l_update) && $this->apply_update();
    }

    /**
     * Detach a raid.
     *
     * @param   integer $p_raidID
     *
     * @return  boolean
     */
    public function detach_raid($p_raidID)
    {
        $l_sql = 'UPDATE isys_catg_drive_list SET isys_catg_drive_list__id__raid_pool = NULL WHERE isys_catg_drive_list__id__raid_pool = ' . $this->convert_sql_id($p_raidID) .
            ';';

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * Removes drive from raid pool.
     *
     * @param   integer $p_drive__id
     *
     * @return  boolean
     */
    public function remove_raid_from_item($p_drive__id)
    {
        $l_update = 'UPDATE isys_catg_drive_list SET isys_catg_drive_list__id__raid_pool = NULL WHERE isys_catg_drive_list__id = ' . $this->convert_sql_id($p_drive__id);

        return $this->update($l_update) && $this->apply_update();
    }

    /**
     * Get System drives.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_cat_id
     *
     * @return  isys_component_dao_result
     */
    public function get_system_drives($p_obj_id = null, $p_cat_id = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_drive_list
			WHERE isys_catg_drive_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			AND isys_catg_drive_list__system_drive = 1';

        if ($p_obj_id !== null) {
            $l_sql .= ' AND isys_catg_drive_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        }

        if ($p_cat_id !== null) {
            $l_sql .= ' AND isys_catg_drive_list__id = ' . $this->convert_sql_id($p_cat_id);
        }

        return $this->retrieve($l_sql);
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
        if (!empty($p_data['filesystem'])) {
            $l_filesystem = isys_import_handler::check_dialog('isys_filesystem_type', $p_data['filesystem']);
        } else {
            $l_filesystem = null;
        }

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'mount_point' => [
                    'value' => $p_data['mount_point']
                ],
                'title'       => [
                    'value' => $p_data['title']
                ],
                'filesystem'  => [
                    'value' => $l_filesystem
                ],
                'unit'        => [
                    'value' => $p_data['unit']
                ],
                'capacity'    => [
                    'value' => $p_data['capacity']
                ],
                'description' => [
                    'value' => $p_data['description']
                ],
                'firmware'    => [
                    'value' => $p_data['firmware']
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
        $l_mount_point = strtolower($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['mount_point'][C__DATA__VALUE]);
        // Use number_format instead of round, because round can display messy float values.
        $l_capacity = number_format($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['capacity'][C__DATA__VALUE], 2, '.', '') ?: '0.00';
        $l_free_space = number_format($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['free_space'][C__DATA__VALUE], 2, '.', '') ?: '0.00';
        $l_used_space = number_format($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['used_space'][C__DATA__VALUE], 2, '.', '') ?: '0.00';

        // @see  ID-4856  Change the parameter value, because it's a reference!
        $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['capacity'][C__DATA__VALUE] = number_format($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['capacity'][C__DATA__VALUE], 3, '.', '');
        $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['free_space'][C__DATA__VALUE] = number_format($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['free_space'][C__DATA__VALUE], 3, '.', '');
        $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['used_space'][C__DATA__VALUE] = number_format($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['used_space'][C__DATA__VALUE], 3, '.', '');


        // Iterate through local data sets:
        foreach ($p_object_category_dataset as $l_dataset_key => $l_dataset) {
            $p_dataset_id_changed = false;
            $p_dataset_id = $l_dataset[$p_table . '__id'];

            if (isset($p_already_used_data_ids[$p_dataset_id])) {
                // Skip it because ID has already been used for another entry.
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                $p_logger->debug('  Dateset ID "' . $p_dataset_id . '" has already been handled. Skipping to next entry.');
                continue;
            }

            // Test the category data identifier:
            if ($p_category_data_values['data_id'] !== null) {
                if ($p_mode === isys_import_handler_cmdb::C__USE_IDS && $p_category_data_values['data_id'] !== $p_dataset_id) {
                    $p_badness[$p_dataset_id]++;
                    $p_dataset_id_changed = true;
                    if ($p_mode === isys_import_handler_cmdb::C__USE_IDS) {
                        continue;
                    }
                }
            }

            $l_dataset_title = strtolower($l_dataset['isys_catg_drive_list__title']);
            $l_dataset_mount_point = strtolower($l_dataset['isys_catg_drive_list__driveletter']);

            // Use number_format instead of round, because round can display messy float values.
            $l_dataset_capacity = number_format(isys_convert::memory($l_dataset['isys_catg_drive_list__capacity'], $l_dataset['capacity_unit'], C__CONVERT_DIRECTION__BACKWARD), 2, '.', '');
            $l_dataset_free_space = number_format(isys_convert::memory($l_dataset['isys_catg_drive_list__free_space'], $l_dataset['free_space_unit'], C__CONVERT_DIRECTION__BACKWARD), 2, '.', '');
            $l_dataset_used_space = number_format(isys_convert::memory($l_dataset['isys_catg_drive_list__used_space'], $l_dataset['used_space_unit'], C__CONVERT_DIRECTION__BACKWARD), 2, '.', '');

            if ($l_dataset_title === $l_title && $l_dataset_mount_point === $l_mount_point && $l_dataset_capacity == $l_capacity && $l_dataset_used_space == $l_used_space &&
                $l_dataset_free_space == $l_free_space) {
                // Check properties - We found our dataset.
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;

                return;
            } elseif ($l_dataset_title === $l_title && $l_dataset_mount_point === $l_mount_point &&
                ($l_dataset_capacity != $l_capacity || $l_dataset_used_space != $l_used_space || $l_dataset_free_space != $l_free_space)) {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY][$l_dataset_key] = $p_dataset_id;
            } else {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
            }
        }
    }
}
