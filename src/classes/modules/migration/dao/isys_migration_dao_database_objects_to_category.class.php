<?php

/**
 * @version     1.13
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_migration_dao_database_objects_to_category extends isys_migration_dao implements isys_migration_interface
{
    /**
     * @var array
     */
    private $databases = [];

    /**
     * @return string
     */
    public function getMigrationTitle()
    {
        return 'databaseObjectsToCategory';
    }

    /**
     * Check if migration has already been executed.
     *
     * @return bool
     */
    public function migrationAlreadyExecuted()
    {
        return (bool)isys_tenantsettings::get('cmdb.migration.database-object-to-category', false);
    }

    /**
     * deactivate migration
     */
    public function deactivateMigration()
    {
        isys_tenantsettings::set('cmdb.migration.database-object-to-category', true);
        return $this;
    }

    /**
     * 1. Iterate through all database instances and check category "Instance / Oracle database"
     * 2. collect all assigned objects via assigned dbms which the databases will be created
     * 3. collect all database schemas which will be the databases
     *
     * @return array
     * @throws isys_exception_database
     */
    private function collectDatabaseInstances()
    {
        try {
            $query = 'SELECT isys_obj__id, isys_obj__title, dbi.isys_cats_database_instance_list__title, dbi.isys_cats_database_instance_list__listener, con1.isys_connection__isys_obj__id as assignedDbms,
            (
                SELECT GROUP_CONCAT(CONCAT(isys_catg_application_list__isys_obj__id, \'-\', 
                    (CASE WHEN isys_catg_version_list__id IS NULL THEN \'\' ELSE isys_catg_version_list__id END))) FROM isys_catg_application_list
                LEFT JOIN isys_catg_version_list ON isys_catg_version_list__id = isys_catg_application_list__isys_catg_version_list__id
                INNER JOIN isys_connection con2 ON con2.isys_connection__id = isys_catg_application_list__isys_connection__id
                WHERE con2.isys_connection__isys_obj__id = con1.isys_connection__isys_obj__id
            ) as assignedToObject,
            (
                SELECT GROUP_CONCAT(CONCAT(isys_catg_application_list__isys_obj__id, \'-\', 
                    (CASE WHEN isys_catg_version_list__id IS NULL THEN \'\' ELSE isys_catg_version_list__id END))) FROM isys_catg_application_list
                LEFT JOIN isys_catg_version_list ON isys_catg_version_list__id = isys_catg_application_list__isys_catg_version_list__id
                INNER JOIN isys_connection con2 ON con2.isys_connection__id = isys_catg_application_list__isys_connection__id
                INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__id = isys_catg_application_list__isys_catg_relation_list__id
                WHERE isys_catg_relation_list__isys_obj__id = con1.isys_connection__isys_obj__id
            ) as assignedToObject2,
            (
                SELECT GROUP_CONCAT(CONCAT(isys_catg_application_list__isys_obj__id, \'-\', 
                    (CASE WHEN isys_catg_version_list__id IS NULL THEN \'\' ELSE isys_catg_version_list__id END))) FROM isys_catg_application_list
                LEFT JOIN isys_catg_version_list ON isys_catg_version_list__id = isys_catg_application_list__isys_catg_version_list__id
                INNER JOIN isys_connection con2 ON con2.isys_connection__id = isys_catg_application_list__isys_connection__id
                INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__id = isys_catg_application_list__isys_catg_relation_list__id
                WHERE isys_catg_relation_list__isys_obj__id__slave = dbi.isys_cats_database_instance_list__isys_obj__id
            ) as assignedToObject3,
            (
                SELECT GROUP_CONCAT(isys_cats_database_schema_list__id) FROM isys_cats_database_schema_list
                WHERE isys_cats_database_schema_list__isys_cats_db_instance_list__id = dbi.isys_cats_database_instance_list__id
            ) as assignedDatabaseSchemas
            FROM isys_cats_database_instance_list dbi
            INNER JOIN isys_obj ON isys_obj__id = isys_cats_database_instance_list__isys_obj__id
            LEFT JOIN isys_connection con1 ON con1.isys_connection__id = dbi.isys_cats_database_instance_list__isys_connection__id';

            $result = $this->retrieve($query);

            $return = [];
            if ($result instanceof isys_component_dao_result && count($result)) {
                while ($row = $result->get_row()) {
                    $return[] = $row;
                }
            }

            return $return;
        } catch (Exception $e) {
            throw new Exception('Collecting database instance objects failed with message: ' . $e->getMessage());
        }
    }

    /**
     * @param $relationObjectId
     *
     * @return array
     * @throws isys_exception_database
     */
    private function getObjectDetailsFromRelation($relationObjectId)
    {
        $query = 'SELECT isys_catg_relation_list__isys_obj__id__master hardware, isys_catg_relation_list__isys_obj__id__slave software 
            FROM isys_catg_relation_list WHERE isys_catg_relation_list__isys_obj__id = ' . $this->convert_sql_id($relationObjectId);
        return $this->retrieve($query)->get_row();
    }

    /**
     * @param int       $applicationId
     * @param int       $hardwareObject
     * @param int       $softwareObject
     * @param int|null  $dbmsEntryId
     * @param array     $assignedDatabaseSchemas
     */
    private function createDatabases($applicationId, $hardwareObject, $softwareObject, $dbmsEntryId = null, $assignedDatabaseSchemas = [])
    {
        try {
            $databaseSchemaQuery = "SELECT isys_obj__title, isys_cats_database_schema_list__title, isys_obj__id 
                FROM isys_cats_database_schema_list
                INNER JOIN isys_obj ON isys_obj__id = isys_cats_database_schema_list__isys_obj__id
                WHERE isys_cats_database_schema_list__id = %s";

            $dao = isys_cmdb_dao_category_g_database::instance(isys_application::instance()->container->get('database'));
            $daoDatabases = isys_cmdb_dao_category_g_database_sa::instance(isys_application::instance()->container->get('database'));

            foreach ($assignedDatabaseSchemas as $databaseSchemaId) {
                $query = sprintf($databaseSchemaQuery, $this->convert_sql_id($databaseSchemaId));
                $databaseSchemaData = $this->retrieve($query)
                    ->get_row();

                // Create entry in database category C__CATG__DATABASE_SA
                $rawData = [
                    'isys_obj__id' => $hardwareObject,
                    'assigned_database' => $applicationId,
                    'assigned_instance' => $dbmsEntryId,
                    'title' => $databaseSchemaData['isys_cats_database_schema_list__title'] ?: $databaseSchemaData['isys_obj__title'],
                ];

                $id = $daoDatabases->create_data($rawData);
                $rawData['id'] = $id;

                $this->databases[$databaseSchemaData['isys_obj__id']][] = $rawData;
            }
        } catch (Exception $e) {
            throw new Exception('Could not create database entries in object ' . $hardwareObject . ' in category database with message: ' . $e->getMessage());
        }
    }

    /**
     * @param $data
     */
    private function createDatabaseAccess($data)
    {
        try {
            foreach ($data as $applicationId => $access) {
                $dbId = [];
                foreach ($access as $accessInfo) {
                    $databaseTitle = $accessInfo['dbSchemaTitle'] ?: $accessInfo['dbObjectTitle'];

                    if (!isset($this->databases[$accessInfo['dbObjectId']])) {
                        // Does not exist
                        continue;
                    }

                    foreach ($this->databases[$accessInfo['dbObjectId']] as $dbInfo) {
                        if (isset($dbInfo['title']) == $databaseTitle) {
                            $dbId[] = $dbInfo['id'];
                            break;
                        }
                    }
                }

                if (count($dbId)) {
                    $insert = 'INSERT INTO isys_catg_application_list_2_isys_catg_database_sa_list (isys_catg_application_list__id, isys_catg_database_sa_list__id) VALUES ';
                    $insert .= implode(',', array_map(function ($item) use ($applicationId) {
                        return '(' . $this->convert_sql_id($applicationId) . ', ' . $this->convert_sql_id($item) . ')';
                    }, $dbId));
                    $this->update($insert);
                }
            }
        } catch (Exception $e) {
            echo  '<font style=\'color: #f0a19e\'>Could not migrate database access data with error message: ' . $e->getMessage() . '</font><br />';
        }
    }

    /**
     * @param $relationObjectId
     *
     * @return int|null
     * @throws isys_exception_database
     */
    private function getApplicationIdFromRelationObject($relationObjectId)
    {
        $query = 'SELECT isys_catg_application_list__id FROM isys_catg_application_list
            LEFT JOIN isys_catg_relation_list ON isys_catg_application_list__isys_catg_relation_list__id = isys_catg_relation_list__id
            WHERE isys_catg_relation_list__isys_obj__id = ' . $this->convert_sql_id($relationObjectId);
        return $this->retrieve($query)->get_row_value('isys_catg_application_list__id');
    }

    /**
     * @param int  $hardwareObjectId
     * @param int  $softwareObjectId
     * @param int $versionId
     *
     * @return int|null
     * @throws isys_exception_database
     */
    private function getApplicationIdFromObjects($hardwareObjectId, $softwareObjectId, $versionId = null)
    {
        $query = 'SELECT isys_catg_application_list__id FROM isys_catg_application_list
            INNER JOIN isys_connection on isys_connection__id = isys_catg_application_list__isys_connection__id
            WHERE isys_catg_application_list__isys_obj__id = ' . $this->convert_sql_id($hardwareObjectId) . ' 
            AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($softwareObjectId);

        if ($versionId) {
            $query .= ' AND isys_catg_application_list__isys_catg_version_list__id = ' . $this->convert_sql_id($versionId);
        }

        return $this->retrieve($query)->get_row_value('isys_catg_application_list__id');
    }

    /**
     * @return array
     * @throws isys_exception_database
     */
    private function collectDatabasesWithoutInstance()
    {
        $query = 'SELECT isys_obj.*, isys_cats_database_schema_list.* FROM isys_obj
            LEFT JOIN isys_cats_database_schema_list ON isys_cats_database_schema_list__isys_obj__id = isys_obj__id
            LEFT JOIN isys_cats_database_instance_list ON isys_cats_database_instance_list__id = isys_cats_database_schema_list__isys_cats_db_instance_list__id
            LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
        
            WHERE isys_obj_type__isysgui_cats__id = (
                SELECT isysgui_cats__id FROM isysgui_cats WHERE isysgui_cats__const = \'C__CATS__DATABASE_SCHEMA\'
            ) 
            AND isys_cats_database_schema_list__isys_cats_db_instance_list__id IS NULL
         HAVING (SELECT COUNT(1) FROM isys_cats_database_access_list WHERE isys_cats_database_access_list__isys_obj__id = isys_obj__id) = 1';
        $result = $this->retrieve($query);
        $return = [];

        if ($result instanceof isys_component_dao_result && count($result)) {
            while ($row = $result->get_row()) {
                $return[] = $row;
            }
        }
        return $return;
    }

    /**
     * @return array
     * @throws isys_exception_database
     */
    private function collectDatabaseAccessData()
    {
        $query = 'SELECT 
                isys_cats_database_access_list__isys_obj__id AS dbObjectId,
                dbobj.isys_obj__title as dbObjectTitle,
                isys_cats_database_schema_list__title as dbSchemaTitle,
                isys_catg_relation_list__isys_obj__id__slave as dbmsObjectId,
                dbmsobj.isys_obj__title as dbmsObjectTitle,
                isys_catg_relation_list__isys_obj__id__master as hardwareObjectId,
                hardwareobj.isys_obj__title as hardwareObjectTitle,
                isys_catg_application_list__id as applicationId
            FROM isys_cats_database_access_list
                LEFT JOIN isys_connection ON isys_connection__id = isys_cats_database_access_list__isys_connection__id
                LEFT JOIN isys_catg_relation_list ON isys_catg_relation_list__isys_obj__id = isys_connection__isys_obj__id
                LEFT JOIN isys_catg_application_list ON isys_catg_relation_list__id = isys_catg_application_list__isys_catg_relation_list__id
                LEFT JOIN isys_obj as dbobj ON dbobj.isys_obj__id = isys_cats_database_access_list__isys_obj__id
                LEFT JOIN isys_cats_database_schema_list ON isys_cats_database_schema_list__isys_obj__id = dbobj.isys_obj__id
                LEFT JOIN isys_obj as dbmsobj ON dbmsobj.isys_obj__id = isys_catg_relation_list__isys_obj__id__slave
                LEFT JOIN isys_obj as hardwareobj ON hardwareobj.isys_obj__id = isys_catg_relation_list__isys_obj__id__master
            WHERE isys_catg_relation_list__id IS NOT NULL';
        $result = $this->retrieve($query);
        $return = [];
        if ($result instanceof isys_component_dao_result && count($result)) {
            while ($row = $result->get_row()) {
                $return[$row['applicationId']][] = $row;
            }
        }
        return $return;
    }

    /**
     * @param $databasesWithoutInstance
     *
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    private function createDatabasesWithoutInstance($databasesWithoutInstance)
    {
        $daoDbms = isys_cmdb_dao_category_g_database::instance(isys_application::instance()->container->get('database'));
        $daoDatabases = isys_cmdb_dao_category_g_database_sa::instance(isys_application::instance()->container->get('database'));


        $query = 'SELECT isys_catg_relation_list__isys_obj__id__master as hardwareObject, isys_catg_relation_list__isys_obj__id__slave as softwareObject,
                    isys_catg_application_list.*
                    FROM isys_cats_database_access_list
                    INNER JOIN isys_obj self ON self.isys_obj__id = isys_cats_database_access_list__isys_obj__id
                    LEFT JOIN isys_connection ON isys_connection__id = isys_cats_database_access_list__isys_connection__id
                    LEFT JOIN isys_catg_relation_list ON isys_catg_relation_list__isys_obj__id = isys_connection__isys_obj__id
                    LEFT JOIN isys_catg_application_list ON isys_catg_application_list__isys_catg_relation_list__id = isys_catg_relation_list__id
                    WHERE isys_cats_database_access_list__isys_obj__id = %s';

        foreach ($databasesWithoutInstance as $database) {
            $databaseDetails = $this->retrieve(sprintf($query, $database['isys_obj__id']))
                ->get_row();

            $rawData = [
                'isys_obj__id'  => $databaseDetails['hardwareObject'],
                'instance_name' => null,
                'assigned_dbms' => $databaseDetails['softwareObject'],
                'version'       => $databaseDetails['isys_catg_application_list__isys_catg_version_list__id']
            ];

            $dbmsEntryId = $daoDbms->create_data($rawData);

            $rawData = [
                'isys_obj__id'      => $databaseDetails['hardwareObject'],
                'assigned_database' => $databaseDetails['isys_catg_application_list__id'],
                'assigned_instance' => null,
                'title'             => $database['isys_obj__title'],
            ];

            $id = $daoDatabases->create_data($rawData);

            $rawData['id'] = $id;

            $this->databases[$database['isys_obj__id']][] = $rawData;
        }
    }

    /**
     * @param $databasesWithInstances
     *
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    private function createDatabasesWithInstances($databasesWithInstances)
    {
        $dao = isys_cmdb_dao_category_g_application::instance(isys_application::instance()->container->get('database'));
        $daoDbms = isys_cmdb_dao_category_g_database::instance(isys_application::instance()->container->get('database'));

        foreach ($databasesWithInstances as $data) {
            if (($data['assignedToObject'] === null && $data['assignedToObject2'] === null && $data['assignedToObject3'] === null) ||
                $data['assignedDatabaseSchemas'] === null) {
                continue;
            }
            $assignedDbmsRelationObjectId = $data['assignedDbms'];
            $applicationIds = $assignmentVersions = $hardwareObjects = [];

            $assignedDbmsTypeId = $dao->get_objTypeID($assignedDbmsRelationObjectId);
            list($hardwareObject, $assignmentVersionId) = explode('-', $data['assignedToObject2']);

            if ($assignedDbmsTypeId !== defined_or_default('C__OBJTYPE__RELATION')) {
                $softwareObject = $assignedDbmsRelationObjectId;

                if (empty($hardwareObject) && !empty($data['assignedToObject3'])) {
                    list($hardwareObject, $assignmentVersionId) = explode('-', $data['assignedToObject3']);
                }

                if (!empty($hardwareObject) && !empty($softwareObject)) {
                    $applicationId = $this->getApplicationIdFromObjects($hardwareObject, $softwareObject);
                }

                if (empty($hardwareObject) && !empty($data['assignedToObject'])) {
                    $hardwareObjectsWithVersion = array_unique(explode(',', $data['assignedToObject']));

                    foreach ($hardwareObjectsWithVersion as $item) {
                        $data = explode('-', $item);
                        $hardwareObjects[] = $data[0];
                        $assignmentVersions[] = $data[1];
                    }

                    foreach ($hardwareObjects as $key => $hardwareObject) {
                        $applicationIds[] = $this->getApplicationIdFromObjects(
                            $hardwareObject,
                            $softwareObject,
                            isset($assignmentVersions[$key]) ? $assignmentVersions[$key] : null
                        );
                    }
                }

                if (empty($softwareObject) || (empty($hardwareObject) && empty($hardwareObjects))) {
                    echo  '<font style=\'color: #f0a19e\'>Assignment between database instance and object for ' . $data['isys_obj__title'] . ' could not be resolved
                                (ID: ' . $data['isys_obj__id'] . '). Skipping database instance!</font><br />';
                    continue;
                }
            } else {
                $applicationId = $this->getApplicationIdFromRelationObject($assignedDbmsRelationObjectId);
                $softwareObject = $this->getObjectDetailsFromRelation($assignedDbmsRelationObjectId)['software'];
            }

            $instanceName = $data['isys_cats_database_instance_list__title'] ?: $data['isys_obj__title'];

            $rawData = [
                'instance_name' => $instanceName,
                'assigned_dbms' => $softwareObject,
                'port_name' => $data['isys_cats_database_instance_list__listener'],
            ];
            $assignedDatabaseSchemas = explode(',', $data['assignedDatabaseSchemas']);

            if (!empty($hardwareObjects)) {
                foreach ($hardwareObjects as $key => $hardwareObject) {
                    $applicationId = $applicationIds[$key];
                    $applicationAssignmentData = $dao->get_data($applicationId)->get_row();

                    $rawData['isys_obj__id'] = $hardwareObject;
                    $rawData['version'] = $applicationAssignmentData['isys_catg_application_list__isys_catg_version_list__id'];
                    $dbmsEntryId = $daoDbms->create_data($rawData);

                    $this->createDatabases($applicationId, $hardwareObject, $softwareObject, $dbmsEntryId, $assignedDatabaseSchemas);
                }
            } else {
                $applicationAssignmentData = $dao->get_data($applicationId)->get_row();

                $rawData['isys_obj__id'] = $hardwareObject;
                $rawData['version'] = $applicationAssignmentData['isys_catg_application_list__isys_catg_version_list__id'];

                $dbmsEntryId = $daoDbms->create_data($rawData);

                $this->createDatabases($applicationId, $hardwareObject, $softwareObject, $dbmsEntryId, $assignedDatabaseSchemas);
            }
        }
    }

    /**
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    public function executeMigration()
    {
        try {
            $databasesWithInstances = $this->collectDatabaseInstances();

            echo "Database assignment migration: <br />";

            if (count($databasesWithInstances)) {
                $this->createDatabasesWithInstances($databasesWithInstances);
            }

            $databasesWithoutInstance = $this->collectDatabasesWithoutInstance();

            if (count($databasesWithoutInstance)) {
                $this->createDatabasesWithoutInstance($databasesWithoutInstance);
            }

            $accessData = $this->collectDatabaseAccessData();

            if (count($accessData)) {
                $this->createDatabaseAccess($accessData);
            }

            $this->apply_update();
        } catch (Exception $e) {
            throw new Exception('Migration failed with message: ' . $e->getMessage());
        }
    }
}
