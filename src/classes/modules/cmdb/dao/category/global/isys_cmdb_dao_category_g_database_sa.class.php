<?php

use idoit\Component\Property\Type\DialogDataProperty;
use idoit\Component\Property\Type\DialogPlusMultiselectProperty;
use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * Category dao for "Database schema"
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     1.13
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.13
 */
class isys_cmdb_dao_category_g_database_sa extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'database_sa';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CATG__DATABASE_SA';

    /**
     * Field for the object id
     *
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_database_sa_list__isys_obj__id';

    /**
     * Category's database table.
     *
     * @var    string
     */
    protected $m_table = 'isys_catg_database_sa_list';

    /**
     * @var bool
     */
    protected $m_multivalued = true;

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

        $l_sql = 'SELECT 1 AS count FROM isys_catg_database_sa_list WHERE isys_catg_database_sa_list__isys_obj__id = ' . $this->convert_sql_id($l_obj_id) .
            ' AND (isys_catg_database_sa_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ')';

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

        $l_sql = "SELECT *, (SELECT isys_obj__title FROM isys_obj WHERE isys_obj__id = isys_connection__isys_obj__id) AS assigned_dbms  FROM isys_catg_database_sa_list 
          INNER JOIN isys_obj ON isys_obj__id = isys_catg_database_sa_list__isys_obj__id
          LEFT JOIN isys_catg_database_list ON isys_catg_database_list__id = isys_catg_database_sa_list__isys_catg_database_list__id
          LEFT JOIN isys_catg_application_list ON isys_catg_application_list__id = isys_catg_database_sa_list__isys_catg_application_list__id
            LEFT JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
            LEFT JOIN isys_cats_application_list ON isys_cats_application_list__isys_obj__id = isys_connection__isys_obj__id
            LEFT JOIN isys_catg_version_list ON isys_catg_version_list__id = isys_catg_application_list__isys_catg_version_list__id
          WHERE TRUE " . $p_condition . " ";

        if ($p_obj_id !== null) {
            $l_sql .= "AND isys_catg_database_sa_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id) . " ";
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= "AND isys_catg_database_sa_list__id = " . $this->convert_sql_id($p_catg_list_id) . " ";
        }

        if ($p_status !== null) {
            $l_sql .= "AND isys_catg_database_sa_list__status = " . $this->convert_sql_int($p_status) . " ";
        }

        $l_sql .= "AND isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ";";

        return $this->retrieve($l_sql);
    }

    /**
     * @param array $p_data
     *
     * @return array
     */
    public function prepareDatabaseSchemaData($p_data)
    {
        $preparedData = $p_data;
        $preparedData['size'] = isys_convert::memory($preparedData['size'], $preparedData['size_unit']);
        $preparedData['max_size'] = isys_convert::memory($preparedData['max_size'], $preparedData['max_size_unit']);
        return $preparedData;
    }

    /**
     * @param isys_request $request
     *
     * @return string
     * @throws isys_exception_database
     */
    public function getAssignedSchemasByRequest(isys_request $request)
    {
        $id = $request->get_category_data_id();
        $result = $this->getAssignedSchemas($id);
        $return = [];
        if ($result instanceof isys_component_dao_result && count($result)) {
            while ($row = $result->get_row()) {
                $return[] = $row['isys_database_schema__id'];
            }
        }
        return implode(',', $return);
    }

    /**
     * @param int  $id
     * @param null $schemaTitle
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function getAssignedSchemas($id, $schemaTitle = null)
    {
        $query = 'SELECT * FROM isys_catg_database_sa_list_2_isys_database_schema main
            INNER JOIN isys_database_schema as ref ON ref.isys_database_schema__id = main.isys_database_schema__id 
            WHERE isys_catg_database_sa_list__id = ' . $this->convert_sql_id($id);

        if ($schemaTitle) {
            $query .= ' AND ref.isys_database_schema__title = ' . $this->convert_sql_text($schemaTitle);
        }


        return $this->retrieve($query);
    }

    /**
     * @param int $id
     * @param array $schemas
     *
     * @throws isys_exception_dao
     */
    public function detachSchemas($id, array $schemas)
    {
        $ids = implode(',', $schemas);
        // Remove from n2m table
        $delete = 'DELETE FROM isys_catg_database_sa_list_2_isys_database_schema 
                WHERE isys_catg_database_sa_list__id = ' . $this->convert_sql_id($id) . ' 
                AND isys_database_schema__id IN (' . $ids . ');';
        $this->update($delete);

        // Set to null in isys_catg_database_table_list
        $deleteFromTable = 'UPDATE isys_catg_database_table_list SET 
             isys_catg_database_table_list__isys_database_schema__id = null 
            WHERE isys_catg_database_table_list__isys_catg_database_sa_list__id = ' . $this->convert_sql_id($id) . '
            AND isys_catg_database_table_list__isys_database_schema__id IN (' . $ids . ');';

        $this->update($deleteFromTable);
    }

    /**
     * @param int $id
     * @param array $schemas
     *
     * @throws isys_exception_dao
     */
    public function attachSchemas($id, array $schemas)
    {
        $insert = 'INSERT INTO isys_catg_database_sa_list_2_isys_database_schema (isys_catg_database_sa_list__id, isys_database_schema__id) 
            VALUES ' . implode(
            ',',
            array_map(function ($item) use ($id) {
                return '(' . $this->convert_sql_id($id) . ', ' . $this->convert_sql_id($item) . ')';
            }, $schemas)
        );
        $this->update($insert);
    }

    /**
     * @param int $id
     * @param array $assignedSchemas
     */
    private function handleSchemas($id, array $assignedSchemas)
    {
        $result = $this->getAssignedSchemas($id);
        $deleteAssignment = $currentSchemas = [];
        if ($result instanceof isys_component_dao_result && count($result)) {
            while ($row = $result->get_row()) {
                if (!in_array($row['isys_database_schema__id'], $assignedSchemas)) {
                    $deleteAssignment[] = $row['isys_database_schema__id'];
                } else {
                    $key = array_search($row['isys_database_schema__id'], $assignedSchemas);
                    unset($assignedSchemas[$key]);
                }
            }
        }

        if (count($deleteAssignment)) {
            $this->detachSchemas($id, $deleteAssignment);
        }

        if (count($assignedSchemas)) {
            $this->attachSchemas($id, $assignedSchemas);
        }
    }

    /**
     * @param int   $categoryDataId
     * @param array $data
     *
     * @return bool
     */
    public function save_data($categoryDataId, $data)
    {
        if (isys_format_json::is_json_array($data['assigned_schemas'])) {
            $data['assigned_schemas'] = isys_format_json::decode($data['assigned_schemas']);
        } elseif (strpos($data['assigned_schemas'], ',')) {
            $data['assigned_schemas'] = explode(',', $data['assigned_schemas']);
        }
        $this->handleSchemas($categoryDataId, (is_array($data['assigned_schemas']) ? $data['assigned_schemas']: []));
        return parent::save_data($categoryDataId, $this->prepareDatabaseSchemaData($data));
    }

    /**
     * @param array $data
     *
     * @return int|bool
     */
    public function create_data($data)
    {
        $id = parent::create_data($this->prepareDatabaseSchemaData($data));

        if (isys_format_json::is_json_array($data['assigned_schemas'])) {
            $data['assigned_schemas'] = isys_format_json::decode($data['assigned_schemas']);
        } elseif (strpos($data['assigned_schemas'], ',')) {
            $data['assigned_schemas'] = explode(',', $data['assigned_schemas']);
        }

        $this->handleSchemas($id, (is_array($data['assigned_schemas']) ? $data['assigned_schemas']: []));
        return $id;
    }

    /**
     * @param isys_request $request
     *
     * @return array
     * @throws isys_exception_database
     */
    public function getDbms(isys_request $request)
    {
        $objectId = (int)$request->get_object_id();
        $return = [];
        if ($objectId > 0) {
            $query = 'SELECT isys_catg_application_list__id as id, 
                CONCAT(isys_obj__title, \' (\', if(isys_catg_version_list__id > 0, isys_catg_version_list__title, \'-\'), \')\') as title 
              FROM isys_catg_database_list 
              INNER JOIN isys_catg_application_list ON isys_catg_application_list__id = isys_catg_database_list__isys_catg_application_list__id
                LEFT JOIN isys_catg_version_list ON isys_catg_version_list__id = isys_catg_application_list__isys_catg_version_list__id
              INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
              INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id 
              WHERE isys_catg_database_list__isys_obj__id = ' . $this->convert_sql_id($objectId) . ' 
              GROUP BY isys_catg_application_list__id';

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
     * @param int $applicationId
     *
     * @return array
     */
    public function getDbmsInstancesByApplicationId($applicationId)
    {
        $result = isys_cmdb_dao_category_g_database::instance(isys_application::instance()->container->get('database'))
            ->get_data(null, $objId, ' AND isys_catg_database_list__isys_catg_application_list__id = ' . $this->convert_sql_id($applicationId));
        $return = [];

        if ($result instanceof isys_component_dao_result && count($result)) {
            while ($row = $result->get_row()) {
                if (empty($row['isys_catg_database_list__instance_name'])) {
                    continue;
                }
                $return[$row['isys_catg_database_list__id']] = $row['isys_catg_database_list__instance_name'];
            }
        }
        return $return;
    }

    /**
     * @param isys_request $request
     *
     * @return array
     * @throws Exception
     */
    public function getDbmsInstancesByRequest(isys_request $request)
    {
        $dataId = (int)$request->get_category_data_id();
        $objId = (int)$request->get_object_id();
        $return = [];

        if ($request->get_row()) {
            $applicationId = $request->get_row('isys_catg_database_sa_list__isys_catg_application_list__id');
        } else {
            $applicationId = $this->get_data($dataId)->get_row()['isys_catg_database_sa_list__isys_catg_application_list__id'];
        }

        if ($applicationId) {
            $return = $this->getDbmsInstancesByApplicationId($applicationId);
        }
        return $return;
    }

    /**
     * @param isys_request $request
     *
     * @return int|null
     */
    public function retrieveAssignedApplication(isys_request $request)
    {
        $id = $request->get_category_data_id();
        if (!is_numeric($id)) {
            return null;
        }

        $query = 'SELECT isys_connection__isys_obj__id 
                FROM isys_catg_database_sa_list
                  INNER JOIN isys_catg_application_list ON isys_catg_application_list__id = isys_catg_database_sa_list__isys_catg_application_list__id
                  INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
                WHERE isys_catg_database_sa_list__id = ' . $this->convert_sql_id($id);
        return (int)$this->retrieve($query)->get_row_value('isys_connection__isys_obj__id');
    }

    /**
     * @param string $dbmsTitle
     * @param int    $objectID
     *
     * @return int
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    public function findOrCreateAssignedDbms(string $dbmsTitle, int $objectID) : int
    {
        $isysObjID = isys_cmdb_dao_category_g_database::instance($this->m_db)
        ->findOrCreateAssignedDbms($dbmsTitle);

        $sql = "SELECT a.isys_catg_application_list__id AS id
            FROM isys_catg_application_list AS a
            INNER JOIN isys_connection AS c ON c.isys_connection__id = a.isys_catg_application_list__isys_connection__id
            WHERE c.isys_connection__isys_obj__id = " . $this->convert_sql_int($isysObjID) . "
            AND a.isys_catg_application_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) .
          " AND a.isys_catg_application_list__isys_obj__id = " . $this->convert_sql_int($objectID) .
          " LIMIT 1";

        $appListID = (int)$this->retrieve($sql)->get_row_value('id');

        if ($appListID !== 0) {
            return $appListID;
        }

        $appListID = isys_cmdb_dao_category_g_application::instance($this->m_db)->sync(
            [
                'properties' => [
                    'application' => [C__DATA__VALUE => $isysObjID],
                    'application_type' => [C__DATA__VALUE => defined_or_default('C__CATG__APPLICATION_TYPE__SOFTWARE')],
                ],
            ],
            $objectID
        );

        return $appListID;
    }

    /**
     * @return array|DynamicProperty[]
     * @throws \idoit\Component\Property\Exception\UnsupportedConfigurationTypeException
     */
    public function dynamic_properties()
    {
        return [
            '_assigned_schemas' => new DynamicProperty(
                'LC__CATG__DATABASE_SA__SCHEMATA',
                'isys_catg_database_sa_list__id',
                'isys_catg_database_sa_list',
                [
                    $this,
                    'getAssignedSchemaForReport'
                ]
            ),
            '_assigned_instance' => new DynamicProperty(
                'LC__CATG__DATABASE_SA__ASSIGNED_INSTANCE',
                'isys_catg_database_sa_list__isys_catg_database_list__id',
                'isys_catg_database_sa_list',
                [
                    $this,
                    'getAssignedInstanceForReport'
                ]
            ),
            '_assigned_database' => new DynamicProperty(
                'LC__CATG__DATABASE_SA__ASSIGNED_DBMS',
                'isys_catg_database_sa_list__id',
                'isys_catg_database_sa_list',
                [
                    $this,
                    'getAssignedDbmsForReport'
                ]
            ),
            '_size' => new DynamicProperty(
                'LC__CATG__DATABASE_SA__SIZE',
                'isys_catg_database_sa_list__id',
                'isys_catg_database_sa_list',
                [
                    $this,
                    'getSizeForReport'
                ]
            ),
            '_max_size' => new DynamicProperty(
                'LC__CATG__DATABASE_SA__MAX_SIZE',
                'isys_catg_database_sa_list__id',
                'isys_catg_database_sa_list',
                [
                    $this,
                    'getMaxSizeForReport'
                ]
            ),
        ];
    }

    /**
     * @param $p_row
     *
     * @return mixed|null
     * @throws isys_exception_database
     */
    public function getAssignedSchemaForReport($p_row)
    {
        $id = $p_row['isys_catg_database_sa_list__id'];
        $query = 'SELECT j11.isys_database_schema__title AS title
                        FROM isys_obj AS obj_main 
                        LEFT JOIN isys_catg_database_sa_list AS j2 
                        ON j2.isys_catg_database_sa_list__isys_obj__id = obj_main.isys_obj__id 
                        LEFT JOIN isys_catg_database_sa_list_2_isys_database_schema AS j4 
                        ON j4.isys_catg_database_sa_list__id = j2.isys_catg_database_sa_list__id 
                        LEFT JOIN isys_database_schema AS j11
                        ON j11.isys_database_schema__id = j4.isys_database_schema__id
                WHERE j2.isys_catg_database_sa_list__id = ' . $this->convert_sql_int($id) . ' LIMIT 1';

        $result = $this->retrieve($query)->get_row_value('title');
        if (!$result) {
            return '-';
        }
        return $result;
    }

    /**
     * @param $p_row
     *
     * @return mixed|string
     * @throws Exception
     */
    public function getAssignedInstanceForReport($p_row)
    {
        $catId = $p_row['isys_catg_database_sa_list__isys_catg_database_list__id'];
        $result = isys_cmdb_dao_category_g_database::instance(isys_application::instance()->container->get('database'))
            ->get_data($catId);

        $return = '-';
        if ($result instanceof isys_component_dao_result && count($result)) {
            while ($row = $result->get_row()) {
                if (empty($row['isys_catg_database_list__instance_name'])) {
                    continue;
                }
                $return = $row['isys_catg_database_list__instance_name'];
                break;
            }
        }

        return $return;
    }

    /**
     * @param $p_row
     *
     * @return string
     */
    public function getAssignedDbmsForReport($p_row)
    {
        $catId = $p_row['isys_catg_database_sa_list__id'];
        $result = $this->get_data($catId);

        $return = '-';
        while ($row = $result->get_row()) {
            $return = $row['assigned_dbms'] . ' (' . $row['isys_catg_version_list__title'] . ')';
            break;
        }

        return $return;
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
        $catId = $p_row['isys_catg_database_sa_list__id'];
        $result = $this->get_data($catId);

        $return = '-';
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
            'size' => 'isys_catg_database_sa_list__max_size',
            'unit' => 'isys_catg_database_sa_list__max_size_unit',
        ]);
    }

    /**
     * @param $p_row
     *
     * @return float|int|string
     */
    public function getSizeForReport($p_row)
    {
        return $this->getSize($p_row, [
            'size' => 'isys_catg_database_sa_list__size',
            'unit' => 'isys_catg_database_sa_list__size_unit',
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
            'assigned_database' => (new DialogDataProperty(
                'C__CATG__DATABASE_SA__ASSIGNED_DBMS',
                'LC__CATG__DATABASE_SA__ASSIGNED_DBMS',
                'isys_catg_database_sa_list__isys_catg_application_list__id',
                'isys_catg_database_sa_list',
                new isys_callback([
                    'isys_cmdb_dao_category_g_database_sa',
                    'getDbms'
                ]),
                false,
                [
                    'isys_global_database_sa_export_helper',
                    'assignedDbmsSa'
                ]
            ))->mergePropertyUi([
                \idoit\Component\Property\Property::C__PROPERTY__UI__DEFAULT => null
            ]),
            'assigned_instance' => (new DialogDataProperty(
                'C__CATG__DATABASE_SA__ASSIGNED_INSTANCE',
                'LC__CATG__DATABASE_SA__ASSIGNED_INSTANCE',
                'isys_catg_database_sa_list__isys_catg_database_list__id',
                'isys_catg_database_sa_list',
                new isys_callback([
                    'isys_cmdb_dao_category_g_database_sa',
                    'getDbmsInstancesByRequest'
                ]),
                false,
                [
                    'isys_global_database_sa_export_helper',
                    'assignedInstance'
                ]
            ))->mergePropertyUi([
                \idoit\Component\Property\Property::C__PROPERTY__UI__DEFAULT => null
            ]),
            'assigned_schemas' => (new DialogPlusMultiselectProperty(
                'C__CATG__DATABASE_SA__SCHEMAS',
                'LC__CATG__DATABASE_SA__SCHEMATA',
                'isys_catg_database_sa_list__id',
                'isys_catg_database_sa_list',
                'isys_catg_database_sa_list_2_isys_database_schema',
                'isys_database_schema',
                ''
            ))->mergePropertyUiParams([
                'p_strSelectedID' => new isys_callback([
                    'isys_cmdb_dao_category_g_database_sa',
                    'getAssignedSchemasByRequest'
                ])
            ])->mergePropertyProvides([
                C__PROPERTY__PROVIDES__REPORT => false
            ]),
            'title'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__DATABASE_SA__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_database_sa_list__title',
                    C__PROPERTY__DATA__INDEX       => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_database_sa_list__title FROM isys_obj 
                      INNER JOIN isys_catg_database_sa_list ON isys_catg_database_sa_list__isys_obj__id = isys_obj__id',
                        'isys_catg_database_sa_list',
                        'isys_catg_database_sa_list__id',
                        'isys_catg_database_sa_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_database_sa_list',
                            'LEFT',
                            'isys_catg_database_sa_list__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__DATABASE_SA__TITLE'
                ]
            ]),
            'size'       => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__DATABASE_SA__SIZE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Table size'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_database_sa_list__size',
                    C__PROPERTY__DATA__INDEX       => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(\'{mem\', \',\', isys_catg_database_sa_list__size, \',\', isys_memory_unit__title, \'}\') FROM isys_obj 
                      INNER JOIN isys_catg_database_sa_list ON isys_catg_database_sa_list__isys_obj__id = isys_obj__id
                      INNER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_database_sa_list__size_unit',
                        'isys_catg_database_sa_list',
                        'isys_catg_database_sa_list__id',
                        'isys_catg_database_sa_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_database_sa_list',
                            'LEFT',
                            'isys_catg_database_sa_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_database_sa_list__size_unit',
                            'isys_memory_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__DATABASE_SA__SIZE',
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
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__DATABASE_SA__SIZE_UNIT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Table size unit'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_database_sa_list__size_unit',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_memory_unit',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_memory_unit',
                        'isys_memory_unit__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_memory_unit__title FROM isys_catg_database_sa_list
                        LEFT JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_database_sa_list__size_unit',
                        'isys_catg_database_sa_list',
                        'isys_catg_database_sa_list__id',
                        'isys_catg_database_sa_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_database_sa_list',
                            'LEFT',
                            'isys_catg_database_sa_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_database_sa_list__size_unit',
                            'isys_memory_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__DATABASE_SA__SIZE_UNIT',
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
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__DATABASE_SA__MAX_SIZE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Table max size'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_database_sa_list__max_size',
                    C__PROPERTY__DATA__INDEX       => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(\'{mem\', \',\', isys_catg_database_sa_list__max_size, \',\', isys_memory_unit__title, \'}\') FROM isys_obj 
                      INNER JOIN isys_catg_database_sa_list ON isys_catg_database_sa_list__isys_obj__id = isys_obj__id
                      INNER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_database_sa_list__max_size_unit',
                        'isys_catg_database_sa_list',
                        'isys_catg_database_sa_list__id',
                        'isys_catg_database_sa_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_database_sa_list',
                            'LEFT',
                            'isys_catg_database_sa_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_database_sa_list__max_size_unit',
                            'isys_memory_unit__id'
                        )

                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__DATABASE_SA__MAX_SIZE',
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
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__DATABASE_SA__MAX_SIZE_UNIT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Table max size unit'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_database_sa_list__max_size_unit',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_memory_unit',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_memory_unit',
                        'isys_memory_unit__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_memory_unit__title FROM isys_catg_database_sa_list
                        LEFT JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_database_sa_list__max_size_unit',
                        'isys_catg_database_sa_list',
                        'isys_catg_database_sa_list__id',
                        'isys_catg_database_sa_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_database_sa_list',
                            'LEFT',
                            'isys_catg_database_sa_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_database_sa_list__max_size_unit',
                            'isys_memory_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__DATABASE_SA__MAX_SIZE_UNIT',
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
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__DATABASE_SA__IMPORTKEY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Import Key'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_database_sa_list__import_key',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_database_sa_list__import_key FROM isys_obj 
                      INNER JOIN isys_catg_database_sa_list ON isys_catg_database_sa_list__isys_obj__id = isys_obj__id',
                        'isys_catg_database_sa_list',
                        'isys_catg_database_sa_list__id',
                        'isys_catg_database_sa_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_database_sa_list',
                            'LEFT',
                            'isys_catg_database_sa_list__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH       => false,
                    C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                    C__PROPERTY__PROVIDES__IMPORT       => false,
                    C__PROPERTY__PROVIDES__EXPORT       => false,
                    C__PROPERTY__PROVIDES__REPORT       => true,
                    C__PROPERTY__PROVIDES__LIST         => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT    => false,
                    C__PROPERTY__PROVIDES__VALIDATION   => false,
                    C__PROPERTY__PROVIDES__VIRTUAL      => true,
                    C__PROPERTY__PROVIDES__FILTERABLE   => false
                ]
            ]),
            'description'              => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__DATABASE_SA', 'C__CATG__DATABASE_SA'),
                'isys_catg_database_sa_list__description',
                'isys_catg_database_sa_list'
            )
        ];
    }

    /**
     * @param int $entryId
     *
     * @return string
     * @throws isys_exception_database
     */
    public function getDatabaseAccess($entryId)
    {
        $quickinfo =  new isys_ajax_handler_quick_info();

        $query = 'SELECT obj.*, objt.* FROM isys_catg_application_list_2_isys_catg_database_sa_list main
            INNER JOIN isys_catg_application_list ref ON ref.isys_catg_application_list__id = main.isys_catg_application_list__id
            INNER JOIN isys_obj obj ON isys_obj__id = ref.isys_catg_application_list__isys_obj__id
            INNER JOIN isys_obj_type objt ON objt.isys_obj_type__id = obj.isys_obj__isys_obj_type__id
            WHERE main.isys_catg_database_sa_list__id = ' . $this->convert_sql_id($entryId) .'
            GROUP BY isys_obj__id';
        $result = $this->retrieve($query);
        $return = '<span class="ml20">' . isys_tenantsettings::get('gui.empty_value', '-') . '</span>';
        if ($result instanceof isys_component_dao_result && count($result)) {
            $list = [];
            while ($data = $result->get_row()) {
                $list[] = $quickinfo->get_quick_info(
                    $data["isys_obj__id"],
                    isys_application::instance()->container->get('language')
                        ->get($data['isys_obj_type__title']) . " &raquo; " . $data["isys_obj__title"],
                    C__LINK__OBJECT
                );
            }
            if (!empty($list)) {
                $return = '<ul class="m0 ml20 list-style-none"><li>' . implode('</li><li>', $list) . '</li></ul>';
            }
        }
        return $return;
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
        if (!isset($data['import_key']) || strpos($data['import_key'], '|||') !== false) {
            $application = '';
            $version = '';
            $instanceName = '';
            $database = '';

            if (!empty($data['assigned_database'])) {
                $query = 'SELECT isys_obj__title, isys_catg_version_list__title, isys_catg_database_list__instance_name 
                    FROM isys_catg_database_list
                    LEFT JOIN isys_catg_application_list ON isys_catg_application_list__id = isys_catg_database_list__isys_catg_application_list__id
                    LEFT JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
                    LEFT JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id
                    LEFT JOIN isys_catg_version_list ON isys_catg_version_list__id = isys_catg_application_list__isys_catg_version_list__id
                    WHERE isys_catg_application_list__id = ' . $this->convert_sql_id($data['assigned_database']);

                if ($data['assigned_instance']) {
                    $query .= ' AND isys_catg_database_list__id = ' . $this->convert_sql_id($data['assigned_instance']);
                }

                $applicationData = $this->retrieve($query)->get_row();
                $application = $applicationData['isys_obj__title'];
                $version = $applicationData['isys_catg_version_list__title'];
                $instanceName = $applicationData['isys_catg_database_list__instance_name'];
                $database = $applicationData['isys_catg_database_sa_list__title'];
            }
            $data['import_key'] = $application . '|' . $version . '|' . $instanceName;
        }

        if ($data['assigned_instance'] === null) {
            $data['assigned_instance'] = '';
        }

        return parent::prepare_query($data);
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
        $dao = isys_cmdb_dao_category_g_database::instance(isys_application::instance()->container->get('database'));

        $db = $p_category_data['properties']['assigned_database']['resolve'];
        $instance = $p_category_data['properties']['assigned_instance']['resolve'];

        if (strlen($db) && strlen($instance)) {
            try {
                $row = $dao->getDbms($p_object_id, $instance, $db)->get_row();
                $instanceId = is_array($row) ? $row['isys_catg_database_list__id'] : null;
                $dbId = is_array($row) ? $row['isys_catg_database_list__isys_catg_application_list__id'] : null;

                if ($instanceId > 0 && $dbId > 0) {
                    $p_category_data['properties']['assigned_database'][C__DATA__VALUE] = $dbId;
                    $p_category_data['properties']['assigned_instance'][C__DATA__VALUE] = $instanceId;
                }
            } catch (Exception $e) {
            }
        }

        return parent::sync($p_category_data, $p_object_id, $p_status);
    }
}
