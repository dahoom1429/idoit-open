<?php

use idoit\Component\Property\Type\DialogPlusCategoryDependencyProperty;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Type\DynamicProperty;
use idoit\Component\Property\Type\MemoryProperty;
use idoit\Component\Property\Type\ObjectBrowserProperty;
use idoit\Component\Property\Type\TextProperty;
use idoit\Component\Property\Type\VirtualProperty;
use idoit\Component\Property\Type\CommentaryProperty;

/**
 * i-doit
 *
 * Category dao for "Database"
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     1.13
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.13
 */
class isys_cmdb_dao_category_g_database extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'database';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CATG__DBMS';

    /**
     * Field for the object id
     *
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_database_list__isys_obj__id';

    /**
     * Category's database table.
     *
     * @var    string
     */
    protected $m_table = 'isys_catg_database_list';

    /**
     * @var bool
     */
    protected $m_multivalued = true;

    /**
     * Counts the number of assigned modules
     *
     * @param   int $p_obj_id
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

        $l_sql = 'SELECT 1 AS count FROM isys_catg_database_list WHERE isys_catg_database_list__isys_obj__id = ' . $this->convert_sql_id($l_obj_id) .
            ' AND (isys_catg_database_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ')';

        return (int)$this->retrieve($l_sql)->get_row_value('count');
    }

    /**
     * Return Category Data.
     *
     * @param   int $p_catg_list_id
     * @param   int $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   int $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT *, (SELECT isys_obj__title FROM isys_obj WHERE isys_obj__id = isys_connection__isys_obj__id) AS assigned_dbms 
            FROM isys_catg_database_list 
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_database_list__isys_obj__id
            LEFT JOIN isys_database_instance_type ON isys_database_instance_type__id = isys_catg_database_list__isys_database_instance_type__id
            LEFT JOIN isys_catg_application_list ON isys_catg_application_list__id = isys_catg_database_list__isys_catg_application_list__id
            LEFT JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
            LEFT JOIN isys_cats_application_list ON isys_cats_application_list__isys_obj__id = isys_connection__isys_obj__id
            LEFT JOIN isys_application_manufacturer ON isys_application_manufacturer__id = isys_cats_application_list__isys_application_manufacturer__id
            LEFT JOIN isys_catg_version_list ON isys_catg_version_list__id = isys_catg_application_list__isys_catg_version_list__id
          WHERE TRUE " . $p_condition . " ";

        if ($p_obj_id !== null) {
            $l_sql .= "AND isys_catg_database_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id) . " ";
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= "AND isys_catg_database_list__id = " . $this->convert_sql_id($p_catg_list_id) . " ";
        }

        if ($p_status !== null) {
            $l_sql .= "AND isys_catg_database_list__status = " . $this->convert_sql_int($p_status) . " ";
        }

        $l_sql .= "AND isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ";";

        return $this->retrieve($l_sql);
    }

    /**
     * @param int       $objectId
     * @param null      $instanceTitle
     * @param null      $dbmsTitle
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function getDbms($objectId, $instanceTitle = null, $dbmsTitle = null)
    {
        $sql = 'SELECT *, main.*, dbms.isys_obj__title AS assigned_dbms 
            FROM isys_catg_database_list 
            INNER JOIN isys_obj main ON main.isys_obj__id = isys_catg_database_list__isys_obj__id
            LEFT JOIN isys_database_instance_type ON isys_database_instance_type__id = isys_catg_database_list__isys_database_instance_type__id
            LEFT JOIN isys_catg_application_list ON isys_catg_application_list__id = isys_catg_database_list__isys_catg_application_list__id
            LEFT JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
            LEFT JOIN isys_obj dbms ON isys_connection__isys_obj__id = dbms.isys_obj__id
            LEFT JOIN isys_cats_application_list ON isys_cats_application_list__isys_obj__id = isys_connection__isys_obj__id
            LEFT JOIN isys_application_manufacturer ON isys_application_manufacturer__id = isys_cats_application_list__isys_application_manufacturer__id
            LEFT JOIN isys_catg_version_list ON isys_catg_version_list__id = isys_catg_application_list__isys_catg_version_list__id
          WHERE isys_catg_database_list__isys_obj__id = ' . $this->convert_sql_id($objectId);

        if ($instanceTitle) {
            $sql .= ' AND isys_catg_database_list__instance_name = ' . $this->convert_sql_text($instanceTitle);
        }

        if ($dbmsTitle) {
            $sql .= ' AND dbms.isys_obj__title = ' . $this->convert_sql_text($dbmsTitle);
        }

        return $this->retrieve($sql);
    }

    /**
     * @param isys_request $p_request
     *
     * @return array
     * @throws Exception
     */
    public function getAssignedDbms(isys_request $p_request)
    {
        $objectId = $p_request->get_object_id();

        if (empty($objectId)) {
            return [];
        }

        $dao = isys_cmdb_dao_category_g_application::instance(isys_application::instance()->container->get('database'));
        $categoryDbmsConstant = defined_or_default('C__CATS__DBMS');

        if ($categoryDbmsConstant === null) {
            return [];
        }

        $objectTypes = $dao->get_object_types_by_category($categoryDbmsConstant, 's', false);

        $result = $dao->get_data(null, $p_request->get_object_id());
        $return = [];

        while ($row = $result->get_row()) {
            if (!in_array($row['isys_obj_type__id'], $objectTypes)) {
                continue;
            }

            $title = $row['isys_obj__title'];

            if (!empty($row['isys_catg_version_list__title'])) {
                $title .= ' ' . $row['isys_catg_version_list__title'];
            }

            if (!empty($row['isys_cats_app_variant_list__title'])) {
                $title .= ' ' .$row['isys_cats_app_variant_list__title'];
            }

            $return[$row['isys_catg_application_list__id']] = $title;
        }

        return $return;
    }

    /**
     * @param int $objectId
     * @param int $applicationId
     *
     * @return array
     * @throws isys_exception_database
     */
    public function getApplicationData($objectId, $applicationId)
    {
        if ($objectId > 0 && $applicationId > 0) {
            $query = 'SELECT isys_application_manufacturer__title AS manufacturer, isys_catg_version_list__id AS version 
                FROM isys_catg_application_list
                INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
                INNER JOIN isys_cats_application_list ON isys_cats_application_list__isys_obj__id = isys_connection__isys_obj__id
                LEFT JOIN isys_application_manufacturer ON isys_application_manufacturer__id = isys_cats_application_list__isys_application_manufacturer__id
                LEFT JOIN isys_catg_version_list ON isys_catg_version_list__id = isys_catg_application_list__isys_catg_version_list__id
                WHERE isys_catg_application_list__isys_obj__id = ' . $this->convert_sql_id($objectId) . ' AND isys_cats_application_list__isys_obj__id = ' . $this->convert_sql_id($applicationId);
            return $this->retrieve($query)->get_row();
        }

        return [];
    }

    /**
     * @param isys_request $p_request
     *
     * @return string|null
     * @throws isys_exception_database
     */
    public function getManufacturerFromDbms(isys_request $p_request)
    {
        $objectId = $p_request->get_object_id();
        $id = $p_request->get_category_data_id();

        if (!is_numeric($id)) {
            return null;
        }

        $query = 'SELECT isys_application_manufacturer__title AS title FROM isys_catg_database_list 
          LEFT JOIN isys_catg_application_list ON isys_catg_application_list__id = isys_catg_database_list__isys_catg_application_list__id
          LEFT JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
          LEFT JOIN isys_cats_application_list ON isys_cats_application_list__isys_obj__id = isys_connection__isys_obj__id
          LEFT JOIN isys_application_manufacturer ON isys_application_manufacturer__id = isys_cats_application_list__isys_application_manufacturer__id
          WHERE isys_catg_database_list__id = ' . $this->convert_sql_id($id);
        return $this->retrieve($query)->get_row_value('title') ?: isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * @param isys_request $p_request
     *
     * @return int|null
     * @throws isys_exception_database
     */
    public function getAssignedVersionFromApplicationAssignment(isys_request $p_request)
    {
        $objectId = $p_request->get_object_id();
        $id = $p_request->get_category_data_id();

        if (!is_numeric($id)) {
            return null;
        }

        $query = 'SELECT isys_catg_version_list__id AS id FROM isys_catg_database_list 
          LEFT JOIN isys_catg_application_list ON isys_catg_application_list__id = isys_catg_database_list__isys_catg_application_list__id
          LEFT JOIN isys_catg_version_list ON isys_catg_version_list__id = isys_catg_application_list__isys_catg_version_list__id
          WHERE isys_catg_database_list__id = ' . $this->convert_sql_id($id);
        return (int)$this->retrieve($query)->get_row_value('id');
    }

    /**
     * @param isys_request $p_request
     *
     * @return array
     * @throws isys_exception_database
     */
    public function getVersionsFromApplication(isys_request $p_request)
    {
        $id = $p_request->get_category_data_id();
        $versions = [];

        if (!is_numeric($id)) {
            return $versions;
        }

        $query = 'SELECT isys_catg_version_list__id, isys_catg_version_list__title FROM isys_catg_database_list 
            INNER JOIN isys_catg_application_list ON isys_catg_application_list__id = isys_catg_database_list__isys_catg_application_list__id
            INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
            INNER JOIN isys_catg_version_list ON isys_catg_version_list__isys_obj__id = isys_connection__isys_obj__id 
            WHERE isys_catg_database_list__id = ' . $this->convert_sql_id($id);
        $result = $this->retrieve($query);
        if ($result instanceof isys_component_dao_result && count($result)) {
            while ($data = $result->get_row()) {
                $versions[$data['isys_catg_version_list__id']] = $data['isys_catg_version_list__title'];
            }
        }

        return $versions;
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
                FROM isys_catg_database_list
                  INNER JOIN isys_catg_application_list ON isys_catg_application_list__id = isys_catg_database_list__isys_catg_application_list__id
                  INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
                WHERE isys_catg_database_list__id = ' . $this->convert_sql_id($id);
        return (int)$this->retrieve($query)->get_row_value('isys_connection__isys_obj__id');
    }

    /**
     * @param $data
     *
     * @return array
     * @throws isys_exception_dao
     * @throws isys_exception_general
     */
    private function prepareDatabaseData($data)
    {
        $preparedData = $this->prepare_data($data);

        if (empty($preparedData)) {
            return [];
        }

        $application = $preparedData['assigned_dbms'] ?: null;
        $version = $data['version'] ? (string)$data['version']: null;
        $preparedData['size'] = isys_convert::memory($preparedData['size'], $preparedData['size_unit']);

        if ($application) {
            $preparedData['assigned_dbms'] = $this->handleAssignedApplication($preparedData['isys_obj__id'], $application, $version);
        }

        unset($preparedData['version']);
        return $preparedData;
    }

    /**
     * @param $objectId
     * @param $applicationObjectId
     * @param $versionId
     *
     * @return int
     * @throws isys_exception_dao
     * @throws isys_exception_general
     */
    public function handleAssignedApplication($objectId, $applicationObjectId, $versionId)
    {
        $applicationDao = isys_cmdb_dao_category_g_application::instance(isys_application::instance()->container->get('database'));
        $condition = ' AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($applicationObjectId);

        if (!$versionId) {
            $condition .= ' AND isys_catg_application_list__isys_catg_version_list__id IS NULL';
        } else {
            $condition .= ' AND isys_catg_application_list__isys_catg_version_list__id = ' . $this->convert_sql_id($versionId);
        }

        $result = $applicationDao->get_data(null, $objectId, $condition);
        if ($result instanceof isys_component_dao_result && count($result) === 1) {
            $applicationAssignmentId = $result->get_row_value('isys_catg_application_list__id');
        } else {
            // Create new application assignment
            $applicationAssignmentId = $applicationDao->create(
                $objectId,
                C__RECORD_STATUS__NORMAL,
                $applicationObjectId,
                '',
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                $versionId
            );
        }

        return $applicationAssignmentId;
    }

    /**
     * @param int   $categoryDataId
     * @param array $data
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_dao_cmdb
     * @throws isys_exception_general
     */
    public function save_data($categoryDataId, $data)
    {
        return parent::save_data($categoryDataId, $this->prepareDatabaseData($data));
    }

    /**
     * @param array $data
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_general
     */
    public function create_data($data)
    {
        return parent::create_data($this->prepareDatabaseData($data));
    }

    public function sync($p_category_data, $p_object_id, $p_status)
    {
        return parent::sync($p_category_data, $p_object_id, $p_status); // TODO: Change the autogenerated stub
    }

    public function findOrCreateAssignedDbms(string $dbmsTitle)
    {
        $sql = "SELECT t.isys_obj_type__id
            FROM isys_obj_type AS t
            WHERE (t.isys_obj_type__title = 'DBMS')
            LIMIT 1";
        $dbmsObjTypeID = (int)$this->retrieve($sql)->get_row_value('isys_obj_type__id');

        $sql = "SELECT o.isys_obj__id
            FROM isys_obj AS o
            INNER JOIN isys_obj_type AS t ON t.isys_obj_type__id = o.isys_obj__isys_obj_type__id
            WHERE (t.isys_obj_type__title = 'DBMS')
            AND (o.isys_obj__title = " . $this->convert_sql_text($dbmsTitle) . ")
            LIMIT 1";
        $isysObjID = (int)$this->retrieve($sql)->get_row_value('isys_obj__id');

        if (!$isysObjID) {
            $isysObjID = $this->create_object($dbmsTitle, $dbmsObjTypeID);
        }

        return (int)$isysObjID;
    }

    /**
     * @return array|DynamicProperty[]
     * @throws \idoit\Component\Property\Exception\UnsupportedConfigurationTypeException
     */
    public function dynamic_properties()
    {
        return [
            '_assigned_dbms' => new DynamicProperty(
                'LC__CATG__DATABASE__ASSIGNED_DBMS',
                'isys_catg_database_list__id',
                'isys_catg_database_list',
                [
                    $this,
                    'getAssignedDbmsForReport'
                ]
            ),
            '_version' => new DynamicProperty(
                'LC__CATG__DATABASE__VERSION',
                'isys_catg_database_list__id',
                'isys_catg_database_list',
                [
                    $this,
                    'getVersionForReport'
                ]
            ),
            '_manufacturer' => new DynamicProperty(
                'LC__CATG__DATABASE__MANUFACTURER',
                'isys_catg_database_list__id',
                'isys_catg_database_list',
                [
                    $this,
                    'getManufacturerForReport'
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
    public function getVersionForReport($p_row)
    {
        $catId = $p_row['isys_catg_database_list__id'];

        $sql = "SELECT isys_catg_version_list__title AS title 
            FROM isys_catg_database_list 
            INNER JOIN isys_catg_application_list ON isys_catg_application_list__id = isys_catg_database_list__isys_catg_application_list__id
            INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
            INNER JOIN isys_catg_version_list ON isys_catg_version_list__isys_obj__id = isys_connection__isys_obj__id 
            WHERE isys_catg_database_list__id = " . $this->convert_sql_int($catId) . " LIMIT 1;";

        return $this->retrieve($sql)->get_row_value('title') ?: isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * @param $p_row
     *
     * @return mixed|null
     * @throws isys_exception_database
     */
    public function getManufacturerForReport($p_row)
    {
        $catId = $p_row['isys_catg_database_list__id'];

        $sql = "SELECT isys_application_manufacturer__title AS title FROM isys_catg_database_list 
          LEFT JOIN isys_catg_application_list ON isys_catg_application_list__id = isys_catg_database_list__isys_catg_application_list__id
          LEFT JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
          LEFT JOIN isys_cats_application_list ON isys_cats_application_list__isys_obj__id = isys_connection__isys_obj__id
          LEFT JOIN isys_application_manufacturer ON isys_application_manufacturer__id = isys_cats_application_list__isys_application_manufacturer__id
          WHERE isys_catg_database_list__id = " . $this->convert_sql_int($catId) . " LIMIT 1;";

        return $this->retrieve($sql)->get_row_value('title') ?: isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * @param $p_row
     *
     * @return mixed|null
     * @throws isys_exception_database
     */
    public function getAssignedDbmsForReport($p_row)
    {
        $catId = $p_row['isys_catg_database_list__id'];

        $sql = "SELECT CONCAT(isys_obj__title, ' {', isys_obj__id, '}') as title
                FROM isys_catg_database_list
                INNER JOIN isys_catg_application_list ON isys_catg_application_list__id = isys_catg_database_list__isys_catg_application_list__id
                INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
                INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id
                WHERE isys_catg_database_list__id = " . $this->convert_sql_int($catId) . " LIMIT 1;";

        return $this->retrieve($sql)->get_row_value('title');
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @throws \idoit\Component\Property\Exception\UnsupportedConfigurationTypeException
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function properties()
    {
        return [
            'assigned_dbms' => (new ObjectBrowserProperty(
                'C__CATG__DATABASE__ASSIGNED_DBMS',
                'LC__CATG__DATABASE__ASSIGNED_DBMS',
                'isys_catg_database_list__isys_catg_application_list__id',
                'isys_catg_database_list',
                [
                    'isys_global_database_export_helper',
                    'assignedDbms'
                ],
                'C__CATS__DBMS'
            ))->mergePropertyData([
                C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                    'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_database_list
                            INNER JOIN isys_catg_application_list ON isys_catg_application_list__id = isys_catg_database_list__isys_catg_application_list__id
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id',
                    'isys_catg_database_list',
                    'isys_catg_database_list__id',
                    'isys_catg_database_list__isys_obj__id',
                    '',
                    '',
                    null,
                    \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_database_list__isys_obj__id'])
                ),
                C__PROPERTY__DATA__JOIN       => [
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_database_list',
                        'LEFT',
                        'isys_catg_database_list__isys_obj__id',
                        'isys_obj__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_application_list',
                        'LEFT',
                        'isys_catg_database_list__isys_catg_application_list__id',
                        'isys_catg_application_list__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_connection',
                        'LEFT',
                        'isys_catg_application_list__isys_connection__id',
                        'isys_connection__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_obj',
                        'LEFT',
                        'isys_connection__isys_obj__id',
                        'isys_obj__id'
                    )
                ]
            ])->mergePropertyUiParams([
                isys_popup_browser_object_ng::C__CALLBACK__ACCEPT => "$('C__CATG__DATABASE__ASSIGNED_DBMS__HIDDEN').fire('softwareSelection:updated');",
                isys_popup_browser_object_ng::C__CALLBACK__DETACH => "$('C__CATG__DATABASE__ASSIGNED_DBMS__HIDDEN').fire('softwareSelection:updated');",
                isys_popup_browser_object_ng::C__DISABLE_SECONDARY_CONDITIONS => true,
                'p_bDisableDetach' => true,
                'p_bReadonly' => true,
                'p_strValue' => new isys_callback([
                    'isys_cmdb_dao_category_g_database',
                    'retrieveAssignedApplication',
                ])
            ])->mergePropertyProvides([
                C__PROPERTY__PROVIDES__REPORT     => false,
                C__PROPERTY__PROVIDES__MULTIEDIT  => true,
                C__PROPERTY__PROVIDES__LIST       => true,
                C__PROPERTY__PROVIDES__VIRTUAL    => false,
            ]),
            'instance_name' => new TextProperty(
                'C__CATG__DATABASE__INSTANCE_NAME',
                'LC__CATG__DATABASE__INSTANCE_NAME',
                'isys_catg_database_list__instance_name',
                'isys_catg_database_list'
            ),
            'instance_type' => (new DialogPlusProperty(
                'C__CATG__DATABASE__INSTANCE_TYPE',
                'LC__CATG__DATABASE__INSTANCE_TYPE',
                'isys_catg_database_list__isys_database_instance_type__id',
                'isys_catg_database_list',
                'isys_database_instance_type'
            ))->mergePropertyProvides([
                C__PROPERTY__PROVIDES__SEARCH => false
            ]),
            'manufacturer' => (new VirtualProperty(
                'C__CATG__DATABASE__MANUFACTURER',
                'LC__CATG__DATABASE__MANUFACTURER',
                'isys_catg_database_list__id',
                'isys_catg_database_list'
            ))->mergePropertyData([
                C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                    'SELECT isys_application_manufacturer__title FROM isys_catg_database_list 
                        INNER JOIN isys_catg_application_list ON isys_catg_application_list__id = isys_catg_database_list__isys_catg_application_list__id
                        INNER JOIN isys_cats_application_list ON isys_cats_application_list__isys_obj__id = isys_catg_application_list__isys_obj__id
                        INNER JOIN isys_application_manufacturer ON isys_cats_application_list__isys_application_manufacturer__id = isys_application_manufacturer__id',
                    'isys_catg_database_list',
                    'isys_catg_database_list__id',
                    'isys_catg_database_list__isys_obj__id',
                    '',
                    '',
                    null,
                    \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_database_list__isys_obj__id'])
                ),
                C__PROPERTY__DATA__JOIN => [
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_database_list',
                        'LEFT',
                        'isys_catg_database_list__isys_obj__id',
                        'isys_obj__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_application_list',
                        'LEFT',
                        'isys_catg_database_list__isys_catg_application_list__id',
                        'isys_catg_application_list__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_cats_application_list',
                        'LEFT',
                        'isys_cats_application_list__isys_obj__id',
                        'isys_catg_application_list__isys_obj__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_application_manufacturer',
                        'LEFT',
                        'isys_cats_application_list__isys_application_manufacturer__id',
                        'isys_application_manufacturer__id'
                    )
                ]
            ])->mergePropertyUiParams([
                'p_bReadonly' => true,
                'p_strValue' => new isys_callback([
                    'isys_cmdb_dao_category_g_database',
                    'getManufacturerFromDbms'
                ])
            ])->mergePropertyProvides([
                C__PROPERTY__PROVIDES__VALIDATION   => false,
            ]),
            'size' => new MemoryProperty(
                'C__CATG__DATABASE__SIZE',
                'LC__CATG__DATABASE__SIZE',
                'isys_catg_database_list__size',
                'isys_catg_database_list',
                'size_unit',
                'isys_catg_database_list__size_unit'
            ),
            'size_unit' => (new DialogProperty(
                'C__CATG__DATABASE__SIZE_UNIT',
                'LC__CATG__DATABASE__SIZE_UNIT',
                'isys_catg_database_list__size_unit',
                'isys_catg_database_list',
                'isys_memory_unit'
            ))->mergePropertyUiParams([
                'p_strClass'   => 'input-mini'
            ])->mergePropertyProvides([
                C__PROPERTY__PROVIDES__REPORT => false,
            ]),
            'version' => (new DialogPlusCategoryDependencyProperty(
                'C__CATG__DATABASE__VERSION',
                'LC__CATG__DATABASE__VERSION',
                'isys_catg_application_list__isys_catg_version_list__id',
                'isys_catg_database_list',
                'isys_catg_version_list',
                'assigned_dbms',
                new isys_callback([
                    'isys_cmdb_dao_category_g_database',
                    'getVersionsFromApplication'
                ]),
                [
                    'isys_global_application_export_helper',
                    'applicationAssignedVersion'
                ]
            ))->mergePropertyData([
                C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                    'SELECT isys_catg_version_list__title
                                FROM isys_catg_database_list
                                INNER JOIN isys_catg_application_list ON isys_catg_application_list__id = isys_catg_database_list__isys_catg_application_list__id 
                                INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
                                INNER JOIN isys_catg_version_list ON isys_catg_version_list__isys_obj__id = isys_connection__isys_obj__id',
                    'isys_catg_database_list',
                    'isys_catg_database_list__id',
                    'isys_catg_database_list__isys_obj__id',
                    '',
                    '',
                    idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([
                        'isys_catg_application_list__isys_catg_version_list__id = isys_catg_version_list__id'
                    ]),
                    \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_database_list__isys_obj__id'])
                ),
                C__PROPERTY__DATA__JOIN       => [
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_database_list',
                        'LEFT',
                        'isys_catg_database_list__isys_obj__id',
                        'isys_obj__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_application_list',
                        'LEFT',
                        'isys_catg_database_list__isys_catg_application_list__id',
                        'isys_catg_application_list__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_connection',
                        'LEFT',
                        'isys_catg_application_list__isys_connection__id',
                        'isys_connection__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_version_list',
                        'LEFT',
                        'isys_connection__isys_obj__id',
                        'isys_catg_version_list__isys_obj__id'
                    )
                ],
            ])->mergePropertyUiParams([
                'p_strSelectedID' => new isys_callback([
                    'isys_cmdb_dao_category_g_database',
                    'getAssignedVersionFromApplicationAssignment'
                ]),
                'p_strTable' => 'isys_catg_version_list'
            ])->mergePropertyProvides([
                C__PROPERTY__PROVIDES__LIST      => false,
                C__PROPERTY__PROVIDES__MULTIEDIT => false,
                C__PROPERTY__PROVIDES__VIRTUAL   => true
            ]),
            'path' => new TextProperty(
                'C__CATG__DATABASE__PATH',
                'LC__CATG__DATABASE__PATH',
                'isys_catg_database_list__path',
                'isys_catg_database_list'
            ),
            'port' => new TextProperty(
                'C__CATG__DATABASE__PORT',
                'LC__CATG__DATABASE__PORT',
                'isys_catg_database_list__port',
                'isys_catg_database_list'
            ),
            'port_name' => new TextProperty(
                'C__CATG__DATABASE__PORT_NAME',
                'LC__CATG__DATABASE__PORT_NAME',
                'isys_catg_database_list__port_name',
                'isys_catg_database_list'
            ),
            'import_key' => (new TextProperty(
                '',
                'LC__CATG__DATABASE__IMPORTKEY',
                'isys_catg_database_list__import_key',
                'isys_catg_database_list'
            ))->mergePropertyProvides([
                C__PROPERTY__PROVIDES__SEARCH       => false,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => false,
                C__PROPERTY__PROVIDES__EXPORT       => false,
                C__PROPERTY__PROVIDES__REPORT       => false,
                C__PROPERTY__PROVIDES__LIST         => false,
                C__PROPERTY__PROVIDES__MULTIEDIT    => false,
                C__PROPERTY__PROVIDES__VALIDATION   => false,
                C__PROPERTY__PROVIDES__VIRTUAL      => true,
                C__PROPERTY__PROVIDES__FILTERABLE   => false
            ]),
            'description'              => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__DATABASE', 'C__CATG__DATABASE'),
                'isys_catg_database_list__description',
                'isys_catg_database_list'
            )
        ];
    }

    /**
     * @param array $p_data
     *
     * @return string
     * @throws isys_exception_dao_cmdb
     * @throws isys_exception_database
     */
    protected function prepare_query(array $p_data)
    {
        if (!isset($p_data['import_key'])) {
            $application = '';
            $version = '';

            if (!empty($p_data['assigned_dbms'])) {
                $query = 'SELECT isys_obj__title, isys_catg_version_list__title FROM isys_catg_application_list
                    LEFT JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
                    LEFT JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id
                    LEFT JOIN isys_catg_version_list ON isys_catg_version_list__id = isys_catg_application_list__isys_catg_version_list__id
                    WHERE isys_catg_application_list__id = ' . $this->convert_sql_id($p_data['assigned_dbms']);
                $applicationData = $this->retrieve($query)->get_row();
                $application = $applicationData['isys_obj__title'];
                $version = $applicationData['isys_catg_version_list__title'];
            }

            $p_data['import_key'] = $application . '|' . $version . '|' . $p_data['instance_name'];
        }

        return parent::prepare_query($p_data);
    }
}
