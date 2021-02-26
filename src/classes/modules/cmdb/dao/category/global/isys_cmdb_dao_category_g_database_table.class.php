<?php

use idoit\Component\Property\Type\DynamicProperty;
use idoit\Component\Property\Type\VirtualProperty;
use idoit\Component\Property\Type\DialogDataProperty;
use idoit\Component\Property\Type\CommentaryProperty;

/**
 * i-doit
 *
 * Category dao for "Database table"
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     1.13
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.13
 */
class isys_cmdb_dao_category_g_database_table extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'database_table';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CATG__DATABASE_TABLE';

    /**
     * Field for the object id
     *
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_database_table_list__isys_obj__id';

    /**
     * Category's database table.
     *
     * @var    string
     */
    protected $m_table = 'isys_catg_database_table_list';

    /**
     * @var bool
     */
    protected $m_multivalued = true;

    /**
     * @var null
     */
    private $cachedDatabaseSchemas = null;

    /**
     * Counts the number of assigned modules
     *
     * @param   integer $p_obj_id
     *
     * @return  mixed
     */
    public function get_count($p_obj_id = null)
    {
        if (!empty($p_obj_id)) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        $l_sql = 'SELECT 1 AS count FROM isys_catg_database_table_list WHERE isys_catg_database_table_list__isys_obj__id = ' . $this->convert_sql_id($l_obj_id) .
            ' AND (isys_catg_database_table_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ')';

        return (int)$this->retrieve($l_sql)->get_row_value('count');
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_catg_database_table_list 
          INNER JOIN isys_obj ON isys_obj__id = isys_catg_database_table_list__isys_obj__id
          LEFT JOIN isys_catg_database_sa_list ON isys_catg_database_sa_list__id = isys_catg_database_table_list__isys_catg_database_sa_list__id  
          WHERE TRUE " . $p_condition . " ";

        if ($p_obj_id !== null) {
            $l_sql .= "AND isys_catg_database_table_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id) . " ";
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= "AND isys_catg_database_table_list__id = " . $this->convert_sql_id($p_catg_list_id) . " ";
        }

        if ($p_status !== null) {
            $l_sql .= "AND isys_catg_database_table_list__status = " . $this->convert_sql_int($p_status) . " ";
        }

        $l_sql .= "AND isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ";";

        return $this->retrieve($l_sql);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function prepareDatabaseTableData($data)
    {
        $preparedData = $this->prepare_data($data);
        $preparedData['size'] = isys_convert::memory($preparedData['size'], $preparedData['size_unit']);
        $preparedData['max_size'] = isys_convert::memory($preparedData['max_size'], $preparedData['max_size_unit']);
        $preparedData['schema_size'] = isys_convert::memory($preparedData['schema_size'], $preparedData['schema_size_unit']);
        return $preparedData;
    }

    /**
     * @param int   $categoryDataId
     * @param array $data
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_dao_cmdb
     */
    public function save_data($categoryDataId, $data)
    {
        return parent::save_data($categoryDataId, $this->prepareDatabaseTableData($data));
    }

    /**
     * @param array $data
     *
     * @return int|bool
     */
    public function create_data($data)
    {
        return parent::create_data($this->prepareDatabaseTableData($data));
    }

    /**
     * @param isys_request $request
     *
     * @return string|null
     * @throws isys_exception_database
     */
    public function getDatabaseInstance(isys_request $request)
    {
        $entryId = (int)$request->get_category_data_id();
        $data = $request->get_row();
        if (empty($data)) {
            $data = $this->get_data($entryId)->get_row();
        }

        /**
         * @var \idoit\Component\Property\Property $property
         * @var \idoit\Module\Report\SqlQuery\Structure\SelectSubSelect $subSelect
         */
        $property = $this->property('instance');
        $subSelect = clone $property->getPropertyDataOffset(\idoit\Component\Property\Property::C__PROPERTY__DEPENDENCY__SELECT);

        $condition = $subSelect->getSelectCondition();
        $condition->setCondition(['isys_catg_database_list__id = ' . $this->convert_sql_id($data['isys_catg_database_sa_list__isys_catg_database_list__id'])]);

        $instanceName = $this->retrieve($subSelect)->get_row_value('isys_catg_database_list__instance_name');

        return ($instanceName ?: isys_tenantsettings::get('gui.empty_value', '-'));
    }

    /**
     * @param isys_request $request
     *
     * @return array
     * @throws isys_exception_database
     */
    public function getDatabases(isys_request $request)
    {
        $objectId = (int)$request->get_object_id();
        $return = [];
        if ($objectId > 0) {
            $query = 'SELECT isys_catg_database_sa_list__id as id, isys_catg_database_sa_list__title as title 
              FROM isys_catg_database_sa_list WHERE isys_catg_database_sa_list__isys_obj__id = ' . $this->convert_sql_id($objectId);
            $result = $this->retrieve($query);
            if ($result instanceof isys_component_dao_result && count($result)) {
                while ($data = $result->get_row()) {
                    $return[$data['id']] = $data['title'];
                }
            }
        }
        return $return;
    }

    /**
     * @param $databaseId
     *
     * @return array
     * @throws isys_exception_database
     */
    public function getDatabaseSchemas($databaseId)
    {
        $result = isys_cmdb_dao_category_g_database_sa::instance(isys_application::instance()->container->get('database'))
            ->getAssignedSchemas($databaseId);
        $return = [];

        if ($result instanceof isys_component_dao_result && count($result)) {
            while ($row = $result->get_row()) {
                $return[$row['isys_database_schema__id']] = $row['isys_database_schema__title'];
            }
        }

        return $return;
    }

    /**
     * @param isys_request $request
     *
     * @return array
     * @throws isys_exception_database
     */
    public function getDatabaseSchemasByRequest(isys_request $request)
    {
        $entryId = (int)$request->get_category_data_id();
        $return = [];

        if ($request->get_row()) {
            $databaseId = $request->get_row('isys_catg_database_sa_list__id');
        } else {
            $databaseId = $this->get_data($entryId)->get_row()['isys_catg_database_sa_list__id'];
        }

        if ($databaseId) {
            $return = $this->getDatabaseSchemas($databaseId);
        }

        return $return;
    }

    public function dynamic_properties()
    {
        return [
            '_size' => new DynamicProperty(
                'LC__CATG__DATABASE_TABLE__SIZE',
                'isys_catg_database_table_list__id',
                'isys_catg_database_table_list',
                [
                    $this,
                    'getSizeForReport'
                ]
            ),
            '_max_size' => new DynamicProperty(
                'LC__CATG__DATABASE_TABLE__MAX_SIZE',
                'isys_catg_database_table_list__id',
                'isys_catg_database_table_list',
                [
                    $this,
                    'getMaxSizeForReport'
                ]
            ),
            '_schema_size' => new DynamicProperty(
                'LC__CATG__DATABASE_TABLE__SCHEMA_SIZE',
                'isys_catg_database_table_list__id',
                'isys_catg_database_table_list',
                [
                    $this,
                    'getSchemaSizeForReport'
                ]
            ),
            '_size_unit' => new DynamicProperty(
                'LC__CATG__DATABASE_TABLE__SIZE_UNIT',
                'isys_catg_database_table_list__id',
                'isys_catg_database_table_list',
                [
                    $this,
                    'getSizeUnitForReport'
                ]
            ),
            '_max_size_unit' => new DynamicProperty(
                'LC__CATG__DATABASE_TABLE__MAX_SIZE_UNIT',
                'isys_catg_database_table_list__id',
                'isys_catg_database_table_list',
                [
                    $this,
                    'getMaxSizeUnitForReport'
                ]
            ),
            '_schema_size_unit' => new DynamicProperty(
                'LC__CATG__DATABASE_TABLE__SCHEMA_SIZE_UNIT',
                'isys_catg_database_table_list__id',
                'isys_catg_database_table_list',
                [
                    $this,
                    'getSchemaSizeUnitForReport'
                ]
            ),
            '_assigned_schema' => new DynamicProperty(
                'LC__CATG__DATABASE_TABLE__SCHEMA',
                'isys_catg_database_table_list__id',
                'isys_catg_database_table_list',
                [
                    $this,
                    'getSchemaForReport'
                ]
            ),
            '_instance' => new DynamicProperty(
                'LC__CATG__DATABASE_TABLE__VIEW_INSTANCE',
                'isys_catg_database_table_list__id',
                'isys_catg_database_table_list',
                [
                    $this,
                    'getInstanceForReport'
                ]
            ),
        ];
    }

    /**
     * @param $p_row
     *
     * @return mixed|string
     * @throws isys_exception_database
     */
    public function getInstanceForReport($p_row)
    {
        $catId = $p_row['isys_catg_database_table_list__id'];
        $result = $this->get_data($catId);

        $databaseId = null;
        while ($row = $result->get_row()) {
            $databaseId = (int)$row['isys_catg_database_table_list__isys_catg_database_sa_list__id'];
            break;
        }

        if ($databaseId) {
            $sql = "SELECT isys_catg_database_list__instance_name as title 
                FROM isys_obj 
                INNER JOIN isys_catg_database_table_list ON isys_catg_database_table_list__isys_obj__id = isys_obj__id
                LEFT JOIN isys_catg_database_sa_list ON isys_catg_database_sa_list__id = isys_catg_database_table_list__isys_catg_database_sa_list__id
                LEFT JOIN isys_catg_database_list ON isys_catg_database_list__id = isys_catg_database_sa_list__isys_catg_database_list__id 
                WHERE isys_catg_database_table_list__isys_catg_database_sa_list__id = " . $this->convert_sql_int($databaseId) . " LIMIT 1;";
            $return = $this->retrieve($sql)->get_row_value('title');
        } else {
            $return = '-';
        }

        return $return;
    }

    /**
     * @param $p_row
     *
     * @return mixed|string
     * @throws isys_exception_database
     */
    public function getSchemaForReport($p_row)
    {
        $catId = $p_row['isys_catg_database_table_list__id'];
        $result = $this->get_data($catId);

        $databaseId = null;
        while ($row = $result->get_row()) {
            $databaseId = (int)$row['isys_catg_database_table_list__isys_catg_database_sa_list__id'];
            break;
        }

        if ($databaseId) {
            $result = isys_cmdb_dao_category_g_database_sa::instance(isys_application::instance()->container->get('database'))
                ->getAssignedSchemas($databaseId);
            if ($result instanceof isys_component_dao_result && count($result)) {
                while ($row = $result->get_row()) {
                    $return = $row['isys_database_schema__title'];
                    break;
                }
            }
        } else {
            $return = '-';
        }

        return $return;
    }

    /**
     * @param $p_row
     * @param $field
     *
     * @return mixed|string
     * @throws isys_exception_database
     */
    public function getSizeUnit($p_row, $field)
    {
        $catId = $p_row['isys_catg_database_table_list__id'];
        $result = $this->get_data($catId);

        while ($row = $result->get_row()) {
            $unitID = (int)$row[$field];
            break;
        }

        if ($unitID) {
            $sql = "SELECT isys_memory_unit__title AS title
                    FROM isys_memory_unit 
                    WHERE isys_memory_unit__id = " . $this->convert_sql_int($unitID) .
                " LIMIT 1;";
            $unit = $this->retrieve($sql)->get_row_value('title');
        } else {
            $unit = '-';
        }

        return $unit;
    }

    /**
     * @param $p_row
     *
     * @return mixed|string
     * @throws isys_exception_database
     */
    public function getSizeUnitForReport($p_row)
    {
        return $this->getSizeUnit($p_row, 'isys_catg_database_table_list__size_unit');
    }

    /**
     * @param $p_row
     *
     * @return mixed|string
     * @throws isys_exception_database
     */
    public function getMaxSizeUnitForReport($p_row)
    {
        return $this->getSizeUnit($p_row, 'isys_catg_database_table_list__max_size_unit');
    }

    /**
     * @param $p_row
     *
     * @return mixed|string
     * @throws isys_exception_database
     */
    public function getSchemaSizeUnitForReport($p_row)
    {
        return $this->getSizeUnit($p_row, 'isys_catg_database_table_list__schema_size_unit');
    }

    /**
     * @param $p_row
     * @param $fields
     *
     * @return float|int|string
     * @throws isys_exception_database
     */
    public function getSize($p_row, $fields)
    {
        $catId = $p_row['isys_catg_database_table_list__id'];
        $result = $this->get_data($catId);

        $return = isys_tenantsettings::get('gui.empty_value', '-');
        while ($row = $result->get_row()) {
            $size = (int)$row[$fields['size']];
            $unitID = (int)$row[$fields['unit']];
            break;
        }

        if (!$unitID) {
            if (!$size) {
                $return = isys_tenantsettings::get('gui.empty_value', '-');
            } else {
                return $size;
            }
        }

        $sql = "SELECT isys_memory_unit__const 
                FROM isys_memory_unit 
                WHERE isys_memory_unit__id = " . $this->convert_sql_int($unitID) .
            " LIMIT 1;";
        $unit = (int)$this->retrieve($sql)->get_row_value('isys_memory_unit__const');

        if (!$size) {
            $return = isys_tenantsettings::get('gui.empty_value', '-');
        } else {
            $return = isys_convert::memory($size, $unit, C__CONVERT_DIRECTION__BACKWARD);
        }

        return $return;
    }

    /**
     * @param $p_row
     *
     * @return float|int|string
     */
    public function getMaxSizeForReport($p_row)
    {
        return $this->getSize($p_row, [
            'size' => 'isys_catg_database_table_list__max_size',
            'unit' => 'isys_catg_database_table_list__max_size_unit',
        ]);
    }

    public function getSizeForReport($p_row)
    {
        return $this->getSize($p_row, [
            'size' => 'isys_catg_database_table_list__size',
            'unit' => 'isys_catg_database_table_list__size_unit',
        ]);
    }
    public function getSchemaSizeForReport($p_row)
    {
        return $this->getSize($p_row, [
            'size' => 'isys_catg_database_table_list__schema_size',
            'unit' => 'isys_catg_database_table_list__schema_size_unit',
        ]);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function properties()
    {
        return [
            'title'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__DATABASE_TABLE__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_database_table_list__title',
                    C__PROPERTY__DATA__INDEX       => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_database_table_list__title FROM isys_obj 
                      INNER JOIN isys_catg_database_table_list ON isys_catg_database_table_list__isys_obj__id = isys_obj__id',
                        'isys_catg_database_table_list',
                        'isys_catg_database_table_list__id',
                        'isys_catg_database_table_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_database_table_list',
                            'LEFT',
                            'isys_catg_database_table_list__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__DATABASE_TABLE__TITLE'
                ]
            ]),
            'row_count'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__DATABASE_TABLE__ROW_COUNT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Table row count'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_database_table_list__row_count',
                    C__PROPERTY__DATA__INDEX       => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_database_table_list__row_count FROM isys_obj 
                      INNER JOIN isys_catg_database_table_list ON isys_catg_database_table_list__isys_obj__id = isys_obj__id',
                        'isys_catg_database_table_list',
                        'isys_catg_database_table_list__id',
                        'isys_catg_database_table_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_database_table_list',
                            'LEFT',
                            'isys_catg_database_table_list__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__DATABASE_TABLE__ROW_COUNT'
                ]
            ]),

            'assigned_database'  => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__DATABASE_TABLE__ASSIGNED_DATABASE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Version'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_database_table_list__isys_catg_database_sa_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_database_sa_list',
                        'isys_catg_database_sa_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_catg_database_sa_list__title , \' \', isys_catg_database_sa_list__instance_name)
                                FROM isys_catg_database_table_list
                                INNER JOIN isys_catg_database_sa_list ON isys_catg_database_sa_list__id = isys_catg_database_table_list__isys_catg_database_sa_list__id',
                        'isys_catg_database_table_list',
                        'isys_catg_database_table_list__id',
                        'isys_catg_database_table_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_database_table_list',
                            'LEFT',
                            'isys_catg_database_table_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_database_sa_list',
                            'LEFT',
                            'isys_catg_database_table_list__isys_catg_database_sa_list__id',
                            'isys_catg_database_sa_list__id'
                        )
                    ],
                    C__PROPERTY__DATA__INDEX      => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__DATABASE_TABLE__ASSIGNED_DATABASE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_catg_database_sa_list',
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_database_table',
                            'getDatabases'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_global_database_table_export_helper',
                        'assignedDatabaseSchema'
                    ]
                ],
            ]),

            'instance' => (new VirtualProperty(
                'C__CATG__DATABASE_TABLE__VIEW_INSTANCE',
                'LC__CATG__DATABASE_TABLE__VIEW_INSTANCE',
                'isys_catg_database_table_list__isys_catg_database_sa_list__id',
                'isys_catg_database_table_list'
            ))->mergePropertyData([
                \idoit\Component\Property\Property::C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                    'SELECT isys_catg_database_list__instance_name FROM isys_obj 
                      INNER JOIN isys_catg_database_table_list ON isys_catg_database_table_list__isys_obj__id = isys_obj__id
                        LEFT JOIN isys_catg_database_sa_list ON isys_catg_database_sa_list__id = isys_catg_database_table_list__isys_catg_database_sa_list__id
                        LEFT JOIN isys_catg_database_list ON isys_catg_database_list__id = isys_catg_database_sa_list__isys_catg_database_list__id',
                    'isys_catg_database_table_list',
                    'isys_catg_database_table_list__id',
                    'isys_catg_database_table_list__isys_obj__id'
                ),
                \idoit\Component\Property\Property::C__PROPERTY__DATA__JOIN => [
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_database_table_list',
                        'LEFT',
                        'isys_catg_database_table_list__isys_obj__id',
                        'isys_obj__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_database_sa_list',
                        'LEFT',
                        'isys_catg_database_table_list__isys_catg_database_sa_list__id',
                        'isys_catg_database_sa_list__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_database_list',
                        'LEFT',
                        'isys_catg_database_sa_table_list__isys_catg_database_list__id',
                        'isys_catg_database_list__id'
                    )
                ],
            ])->mergePropertyUiParams([
                'p_strValue' => new isys_callback([
                    'isys_cmdb_dao_category_g_database_table',
                    'getDatabaseInstance'
                ])
            ]),

            'assigned_schema' => (new DialogDataProperty(
                'C__CATG__DATABASE_TABLE__SCHEMA',
                'LC__CATG__DATABASE_TABLE__SCHEMA',
                'isys_catg_database_table_list__isys_database_schema__id',
                'isys_catg_database_table_list',
                new isys_callback([
                    'isys_cmdb_dao_category_g_database_table',
                    'getDatabaseSchemasByRequest'
                ])
            ))->mergePropertyUi([
                \idoit\Component\Property\Property::C__PROPERTY__UI__DEFAULT => null
            ]),

            'schema_size'       => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__DATABASE_TABLE__SCHEMA_SIZE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Table size'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_database_table_list__schema_size',
                    C__PROPERTY__DATA__INDEX       => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(\'{mem\', \',\', isys_catg_database_table_list__schema_size, \',\', isys_memory_unit__title, \'}\') FROM isys_obj 
                      INNER JOIN isys_catg_database_table_list ON isys_catg_database_table_list__isys_obj__id = isys_obj__id
                      INNER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_database_table_list__schema_size_unit',
                        'isys_catg_database_table_list',
                        'isys_catg_database_table_list__id',
                        'isys_catg_database_table_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_database_table_list',
                            'LEFT',
                            'isys_catg_database_table_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_database_table_list__schema_size_unit',
                            'isys_memory_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__DATABASE_TABLE__SCHEMA_SIZE',
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
                    C__PROPERTY__FORMAT__UNIT     => 'schema_size_unit'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'schema_size_unit' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__DATABASE_TABLE__SCHEMA_SIZE_UNIT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Table size unit'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_database_table_list__schema_size_unit',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_memory_unit',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_memory_unit',
                        'isys_memory_unit__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_memory_unit__title FROM isys_catg_database_table_list
                        LEFT JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_database_table_list__schema_size_unit',
                        'isys_catg_database_table_list',
                        'isys_catg_database_table_list__id',
                        'isys_catg_database_table_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_database_table_list',
                            'LEFT',
                            'isys_catg_database_table_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_database_table_list__schema_size_unit',
                            'isys_memory_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__DATABASE_TABLE__SCHEMA_SIZE_UNIT',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'   => 'isys_memory_unit',
                        'p_strClass'   => 'input-mini',
                        'p_bDbFieldNN' => 0
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),

            'size'       => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__DATABASE_TABLE__SIZE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Table size'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_database_table_list__size',
                    C__PROPERTY__DATA__INDEX       => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(\'{mem\', \',\', isys_catg_database_table_list__size, \',\', isys_memory_unit__title, \'}\') FROM isys_obj 
                      INNER JOIN isys_catg_database_table_list ON isys_catg_database_table_list__isys_obj__id = isys_obj__id
                      INNER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_database_table_list__size_unit',
                        'isys_catg_database_table_list',
                        'isys_catg_database_table_list__id',
                        'isys_catg_database_table_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_database_table_list',
                            'LEFT',
                            'isys_catg_database_table_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_database_table_list__size_unit',
                            'isys_memory_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__DATABASE_TABLE__SIZE',
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
                    C__PROPERTY__FORMAT__UNIT     => 'size_unit'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'size_unit' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__DATABASE_TABLE__SIZE_UNIT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Table size unit'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_database_table_list__size_unit',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_memory_unit',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_memory_unit',
                        'isys_memory_unit__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_memory_unit__title FROM isys_catg_database_table_list
                        LEFT JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_database_table_list__size_unit',
                        'isys_catg_database_table_list',
                        'isys_catg_database_table_list__id',
                        'isys_catg_database_table_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_database_table_list',
                            'LEFT',
                            'isys_catg_database_table_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_database_table_list__size_unit',
                            'isys_memory_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__DATABASE_TABLE__SIZE_UNIT',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'   => 'isys_memory_unit',
                        'p_strClass'   => 'input-mini',
                        'p_bDbFieldNN' => 0
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'max_size'       => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__DATABASE_TABLE__MAX_SIZE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Table max size'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_database_table_list__max_size',
                    C__PROPERTY__DATA__INDEX       => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(\'{mem\', \',\', isys_catg_database_table_list__max_size, \',\', isys_memory_unit__title, \'}\') FROM isys_obj 
                      INNER JOIN isys_catg_database_table_list ON isys_catg_database_table_list__isys_obj__id = isys_obj__id
                      INNER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_database_table_list__max_size_unit',
                        'isys_catg_database_table_list',
                        'isys_catg_database_table_list__id',
                        'isys_catg_database_table_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_database_table_list',
                            'LEFT',
                            'isys_catg_database_table_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_database_table_list__max_size_unit',
                            'isys_memory_unit__id'
                        )

                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__DATABASE_TABLE__MAX_SIZE',
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
                    C__PROPERTY__FORMAT__UNIT     => 'max_size_unit'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'max_size_unit' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__DATABASE_TABLE__MAX_SIZE_UNIT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Table max size unit'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_database_table_list__max_size_unit',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_memory_unit',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_memory_unit',
                        'isys_memory_unit__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_memory_unit__title FROM isys_catg_database_table_list
                        LEFT JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_database_table_list__max_size_unit',
                        'isys_catg_database_table_list',
                        'isys_catg_database_table_list__id',
                        'isys_catg_database_table_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_database_table_list',
                            'LEFT',
                            'isys_catg_database_table_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_database_table_list__max_size_unit',
                            'isys_memory_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__DATABASE_TABLE__MAX_SIZE_UNIT',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'   => 'isys_memory_unit',
                        'p_strClass'   => 'input-mini',
                        'p_bDbFieldNN' => 0
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'import_key' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__DATABASE_TABLE__IMPORTKEY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Import Key'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__INDEX       => false,
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_database_table_list__import_key',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_database_table_list__import_key FROM isys_obj 
                      INNER JOIN isys_catg_database_table_list ON isys_catg_database_table_list__isys_obj__id = isys_obj__id',
                        'isys_catg_database_table_list',
                        'isys_catg_database_table_list__id',
                        'isys_catg_database_table_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_database_table_list',
                            'LEFT',
                            'isys_catg_database_table_list__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH       => false,
                    C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                    C__PROPERTY__PROVIDES__IMPORT       => true,
                    C__PROPERTY__PROVIDES__EXPORT       => false,
                    C__PROPERTY__PROVIDES__REPORT       => false,
                    C__PROPERTY__PROVIDES__LIST         => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT    => false,
                    C__PROPERTY__PROVIDES__VALIDATION   => false,
                    C__PROPERTY__PROVIDES__VIRTUAL      => true,
                    C__PROPERTY__PROVIDES__FILTERABLE   => false
                ]
            ]),
            'description'              => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__DATABASE_TABLE', 'C__CATG__DATABASE_TABLE'),
                'isys_catg_database_table_list__description',
                'isys_catg_database_table_list'
            )
        ];
    }

    /**
     * @param array $data
     *
     * @return string
     * @throws isys_exception_dao_cmdb
     * @throws isys_exception_database
     */
    protected function prepare_query(array $data)
    {
        if (!isset($data['import_key'])) {
            $application = '';
            $version = '';
            $instanceName = '';
            $database = '';
            $databaseSchema = '';

            if ($this->cachedDatabaseSchemas === null) {
                $this->cachedDatabaseSchemas = new isys_array();
            }

            if (!empty($data['assigned_database'])) {
                if (!isset($this->cachedDatabaseSchemas[$data['assigned_database']])) {
                    $applicationData = $this->cachedDatabaseSchemas[$data['assigned_database']];
                } else {
                    $query = 'SELECT 
                       isys_obj__title, 
                       isys_catg_version_list__title, 
                       isys_catg_database_list__instance_name, 
                       isys_catg_database_list__title, 
                       isys_catg_database_sa_list__title
                    FROM isys_catg_database_sa_list 
                        LEFT JOIN isys_catg_database_list ON isys_catg_database_list__id = isys_catg_database_sa_list__isys_catg_database_list__id
                        LEFT JOIN isys_catg_application_list ON isys_catg_application_list__id = isys_catg_database_list__isys_catg_application_list__id
                        LEFT JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
                        LEFT JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id
                        LEFT JOIN isys_catg_version_list ON isys_catg_version_list__id = isys_catg_application_list__isys_catg_version_list__id
                    WHERE isys_catg_database_sa_list__id = ' . $this->convert_sql_id($data['assigned_database']);

                    $applicationData = $this->retrieve($query)->get_row();
                }

                $application = $applicationData['isys_obj__title'];
                $version = $applicationData['isys_catg_version_list__title'];
                $instanceName = $applicationData['isys_catg_database_list__instance_name'];
                $database = $applicationData['isys_catg_database_list__title'];
                $databaseSchema = $applicationData['isys_catg_database_sa_list__title'];
            } else {
                $data['assigned_database'] = '';
            }

            $data['import_key'] = $application . '|' . $version . '|' . $instanceName . '|' . $database . '|' . $databaseSchema;
        }

        if ($data['assigned_schema'] === null) {
            $data['assigned_schema'] = '';
        }

        $query = parent::prepare_query($data);
        return $query;
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
        // Iterate through local data sets:
        $title = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['title'][C__DATA__VALUE];
        $importKey = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['import_key'][C__DATA__VALUE];
        $rowCount = (int)$p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['row_count'][C__DATA__VALUE];
        $size = (int)$p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['size']['value_converted'];
        $maxSize = (int)$p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['max_size']['value_converted'];

        // Iterate through local data sets:
        foreach ($p_object_category_dataset as $datasetKey => $dataset) {
            $p_dataset_id_changed = false;
            $p_dataset_id = $dataset[$p_table . '__id'];

            if (isset($p_already_used_data_ids[$p_dataset_id])) {
                // Skip it because ID has already been used for another entry.
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$datasetKey] = $p_dataset_id;
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

            $datasetTitle = $dataset['isys_catg_database_table_list__title'];
            $datasetImportKey = $dataset['isys_catg_database_table_list__import_key'];
            $datasetRowCount = (int)$dataset['isys_catg_database_table_list__row_count'];
            $datasetSize = (int)$dataset['isys_catg_database_table_list__size'];
            $datasetMaxSize = (int)$dataset['isys_catg_database_table_list__max_size'];

            if ($datasetTitle === $title && $datasetImportKey === $importKey) {
                if ($datasetRowCount === $rowCount && $datasetSize === $size && $datasetMaxSize === $maxSize) {
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$datasetKey] = $p_dataset_id;
                    return;
                }

                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY][$datasetKey] = $p_dataset_id;
                return;
            }

            $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$datasetKey] = $p_dataset_id;
        }
    }

    /**
     * @param array $p_category_data
     * @param int   $p_object_id
     * @param int   $p_status
     *
     * @return bool|mixed
     * @throws isys_exception_validation
     */
    public function sync($p_category_data, $p_object_id, $p_status)
    {
        $dbName = $p_category_data['properties']['assigned_database']['resolve'];

        if ($p_category_data['properties']['assigned_database'][C__DATA__VALUE] === null &&
            strlen($dbName) > 0) {
            $dbDao = isys_cmdb_dao_category_g_database_sa::instance(isys_application::instance()->container->get('database'));

            $dbId = $dbDao->get_data(
                null,
                $p_object_id,
                'AND isys_catg_database_sa_list__title = ' . $dbDao->convert_sql_text($dbName)
            )->get_row_value('isys_catg_database_sa_list__id');

            if ($dbId) {
                $p_category_data['properties']['assigned_database'][C__DATA__VALUE] = $dbId;

                $dbsName = $p_category_data['properties']['assigned_schema']['resolve'];

                if (strlen($dbsName) > 0) {
                    try {
                        $dbsId = $dbDao->getAssignedSchemas($dbId, $dbsName)->get_row_value('isys_database_schema__id');

                        if ($dbsId) {
                            $p_category_data['properties']['assigned_schema'][C__DATA__VALUE] = $dbsId;
                        }
                    } catch (Exception $e) {
                    }
                }
            }
        }

        return parent::sync($p_category_data, $p_object_id, $p_status);
    }
}
