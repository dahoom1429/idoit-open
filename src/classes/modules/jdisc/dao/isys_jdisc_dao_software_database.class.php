<?php

/**
 * i-doit
 *
 * JDisc software DAO
 *
 * @package     i-doit
 * @subpackage  Modules
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-9
 */
class isys_jdisc_dao_software_database extends isys_jdisc_dao_data
{
    const MEMORY_KB_IN_BYTE = '1024';
    const MEMORY_MB_IN_BYTE = '1048576';
    const MEMORY_GB_IN_BYTE = '1073741824';

    /**
     * Helper array which holds all connections
     *
     * @var null
     */
    private $databaseConnections = null;

    /**
     * @var null
     */
    private $dbTableConnections = null;

    /**
     *
     */
    public function reset()
    {
        $this->databaseConnections = new isys_array();
        $this->dbTableConnections = new isys_array();
        return $this;
    }

    private function mapExistingDbTableConnections()
    {
        $queryDatabaseTable = 'SELECT * FROM isys_catg_database_table_list
            LEFT JOIN isys_catg_database_sa_list ON isys_catg_database_sa_list__id = isys_catg_database_table_list__isys_catg_database_sa_list__id
            WHERE isys_catg_database_table_list__isys_obj__id = ' . $this->convert_sql_id($this->get_current_object_id());
        $result = $this->retrieve($queryDatabaseTable);
        while ($row = $result->get_row()) {
            $key = $row['isys_catg_database_table_list__import_key'] . '|' . $row['isys_catg_database_sa_list__title'];
            $this->dbTableConnections[$key] = $row;
        }
    }

    /**
     * @param $data
     *
     * @return bool
     */
    private function changesExistInDbTableConnections($data)
    {
        $key = $data['identifier'] . '|' . $data['dbname'];

        if (!empty($data['dbsname'])) {
            $key .= '|' . $data['dbsname'];
        }

        $key .= '|' . $data['dbtable'];

        if (!isset($this->dbTableConnections[$key])) {
            return true;
        }

        if ((int)$data['size'] === (int)$this->dbTableConnections[$key]['isys_catg_database_table_list__size'] &&
            (int)$data['maxsize'] === (int)$this->dbTableConnections[$key]['isys_catg_database_table_list__max_size'] &&
            (int)$data['rowcount'] === (int)$this->dbTableConnections[$key]['isys_catg_database_table_list__row_count']) {
            return false;
        }

        return true;
    }

    /**
     * @param      $id
     * @param bool $raw
     * @param      $objectIds
     * @param      $connections
     *
     * @return array
     * @throws Exception
     */
    public function getDatabaseTablesByDevice($id, $raw = false, &$objectIds, &$connections)
    {
        $this->mapExistingDbTableConnections();

        $query = 'SELECT TRIM(BOTH \' \' FROM dbt.name) AS dbtable, dbt.size AS size, dbt.maxsize AS maxsize, dbt.rowcount AS rowcount, 
                TRIM(BOTH \' \' FROM LOWER(app.name)) AS lowername, 
                TRIM(BOTH \' \' FROM LOWER(CONCAT(app.name, \'|\', app.version, \'|\', appinst.instancename))) AS identifier,  
                TRIM(BOTH \' \' FROM db.name) as dbname, 
                TRIM(BOTH \' \' FROM dbs.name) AS dbsname
            FROM databasetable AS dbt
            INNER JOIN databaseschema AS dbs ON dbs.id = dbt.databaseschemaid 
            INNER JOIN database AS db ON db.id = dbs.databaseid
                LEFT JOIN applicationinstance AS appinst ON appinst.id = db.applicationinstanceid
                LEFT JOIN applicationinstanceport AS appinstport ON appinstport.applicationinstanceid = appinst.id
                LEFT JOIN portconnection AS pcon ON pcon.id = appinstport.portconnectionid
                LEFT JOIN application AS app ON app.id = appinst.applicationid
                LEFT JOIN operatingsystem AS os ON os.id = appinst.operatingsystemid
                LEFT JOIN device AS d ON d.operatingsystemid = os.id
			WHERE d.id = ' . $this->convert_sql_id($id);

        $softwareDao = isys_jdisc_dao_software::instance(isys_application::instance()->container->get('database'));
        $return = [];

        $result = $this->fetch($query);
        $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($result) . ' database schema rows');
        while ($data = $this->m_pdo->fetch_row_assoc($result)) {
            $dbsName = 'UNASSIGNED';
            if (isset($this->databaseConnections[$id][$data['identifier']][$data['dbname']]) &&
                !in_array($data['dbtable'], $this->databaseConnections[$id][$data['identifier']][$data['dbname']]) &&
                $this->changesExistInDbTableConnections($data)) {
                if (!empty($data['dbsname'])) {
                    $dbsName = $data['dbsname'];
                }
                $this->databaseConnections[$id][$data['identifier']][$data['dbname']][$dbsName][] = $data['dbtable'];
                $return[] = $this->prepareDatabaseTable($data);
            }
        }

        if ($raw === true || count($return) == 0) {
            return $return;
        } else {
            return [
                C__DATA__TITLE      => isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__CATG__DATABASE_TABLE'),
                'const'             => 'C__CATG__DATABASE_TABLE',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => $return
            ];
        }
    }

    /**
     * @param      $id
     * @param bool $raw
     * @param      $objectIds
     * @param      $connections
     *
     * @return array
     * @throws Exception
     */
    public function getDatabasesByDevice($id, $raw = false, &$objectIds, &$connections)
    {
        $query = 'SELECT TRIM(BOTH \' \' FROM db.name) AS dbname, db.size AS size, db.maxsize AS maxsize, appinst.instancename AS instancename, 
                appinst.applicationpath AS instancepath, app.name AS appname, app.version AS appversion, pcon.fromport AS port, pcon.fromname AS portname, 
                TRIM(BOTH \' \' FROM LOWER(app.name)) AS lowername, 
                (
                    SELECT STRING_AGG(TRIM(BOTH \' \' FROM dbs.name), \',\') as dbsname FROM databaseschema as dbs
                    WHERE dbs.databaseid = db.id GROUP BY dbs.databaseid
                ) as schemes,
                TRIM(BOTH \' \' FROM LOWER(CONCAT(app.name, \'|\', app.version, \'|\', appinst.instancename))) AS identifier
                FROM applicationinstance AS appinst
                LEFT JOIN database AS db ON appinst.id = db.applicationinstanceid
                LEFT JOIN applicationinstanceport AS appinstport ON appinstport.applicationinstanceid = appinst.id
                LEFT JOIN portconnection AS pcon ON pcon.id = appinstport.portconnectionid
                LEFT JOIN application AS app ON app.id = appinst.applicationid
                LEFT JOIN operatingsystem AS os ON os.id = appinst.operatingsystemid
                LEFT JOIN device AS d ON d.operatingsystemid = os.id
			WHERE d.id = ' . $this->convert_sql_id($id) . ' AND db.name IS NOT NULL';

        $result = $this->fetch($query);
        $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($result) . ' database schema rows');
        while ($data = $this->m_pdo->fetch_row_assoc($result)) {
            if (!isset($this->databaseConnections[$id][$data['identifier']][$data['dbname']])) {
                $this->databaseConnections[$id][$data['identifier']][$data['dbname']] = [];
                $return[] = $this->prepareDatabase($data);
            }
        }

        if ($raw === true || count($return) == 0) {
            return $return;
        } else {
            return [
                C__DATA__TITLE      => isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__CATG__DATABASE_SA'),
                'const'             => 'C__CATG__DATABASE_SA',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => $return
            ];
        }
    }

    /**
     * @param      $id
     * @param bool $raw
     * @param      $objectIds
     * @param      $connections
     *
     * @return array
     * @throws Exception
     */
    public function getDbmsByDevice($id, $raw = false, &$objectIds, &$connections)
    {
        $query = 'SELECT appinst.instancename AS instancename, 
                appinst.applicationpath AS instancepath, app.name AS appname, app.version AS appversion, pcon.fromport AS port, pcon.fromname AS portname, 
                TRIM(BOTH \' \' FROM LOWER(app.name)) AS lowername, 
                TRIM(BOTH \' \' FROM LOWER(CONCAT(app.name, \'|\', app.version, \'|\', appinst.instancename))) AS identifier
            FROM applicationinstance AS appinst
                LEFT JOIN applicationinstanceport AS appinstport ON appinstport.applicationinstanceid = appinst.id
                LEFT JOIN portconnection AS pcon ON pcon.id = appinstport.portconnectionid
                LEFT JOIN application AS app ON app.id = appinst.applicationid
                LEFT JOIN operatingsystem AS os ON os.id = appinst.operatingsystemid
                LEFT JOIN device AS d ON d.operatingsystemid = os.id
			WHERE d.id = ' . $this->convert_sql_id($id);

        $softwareDao = isys_jdisc_dao_software::instance(isys_application::instance()->container->get('database'));
        $return = [];

        $result = $this->fetch($query);
        $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($result) . ' databases rows');
        while ($data = $this->m_pdo->fetch_row_assoc($result)) {
            $dbms = $softwareDao->does_software_exist_in_idoit($data['lowername'], C__OBJTYPE__DBMS);
            if (!$dbms) {
                continue;
            }

            $objectIds[$dbms[isys_jdisc_dao_software::OBJ_ID]] = $dbms[isys_jdisc_dao_software::OBJ_ID];
            $return[] = $this->prepareDbms($data, $dbms);

            $this->databaseConnections[$id][$data['identifier']] = [];
        }

        if ($raw === true || count($return) == 0) {
            return $return;
        } else {
            return [
                C__DATA__TITLE      => isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__CATG__DATABASE'),
                'const'             => 'C__CATG__DATABASE',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => $return
            ];
        }
    }

    /**
     * @return array
     */
    private function getMemoryMap()
    {
        return [
            isys_jdisc_dao_software_database::MEMORY_KB_IN_BYTE => [
                'value' => 'KB',
                'id' => defined_or_default('C__MEMORY_UNIT__KB'),
                'const' => 'C__MEMORY_UNIT__KB',
                'title_lang' => 'KB',
            ],
            isys_jdisc_dao_software_database::MEMORY_MB_IN_BYTE => [
                'value' => 'MB',
                'id' => defined_or_default('C__MEMORY_UNIT__MB'),
                'const' => 'C__MEMORY_UNIT__MB',
                'title_lang' => 'MB',
            ],
            isys_jdisc_dao_software_database::MEMORY_GB_IN_BYTE => [
                'value' => 'GB',
                'id' => defined_or_default('C__MEMORY_UNIT__GB'),
                'const' => 'C__MEMORY_UNIT__GB',
                'title_lang' => 'GB',
            ]
        ];
    }

    /**
     * @param $data
     *
     * @return array|null
     */
    private function prepareDatabaseTable($data)
    {
        if (!empty($data)) {
            $unit = [
                'tag' => 'size_unit',
                'value' => 'B',
                'id' => defined_or_default('C__MEMORY_UNIT__B'),
                'const' => 'C__MEMORY_UNIT__B',
                'title_lang' => 'B',
                'title'      => 'LC__CATG__DATABASE_TABLE__SIZE_UNIT',
            ];

            $maxUnit = $unit;
            $maxUnit['tag'] = 'max_size_unit';
            $maxUnit['title'] = 'LC__CATG__DATABASE_TABLE__MAX_SIZE_UNIT';

            $memoryMap = $this->getMemoryMap();

            $sizes = [
                'size' => [
                    'value' => $data['size'],
                    'unit' => $unit
                ],
                'max_size' => [
                    'value' => $data['maxsize'],
                    'unit' => $maxUnit
                ]
            ];

            foreach ($sizes as $propertyKey => $sizeContent) {
                if ($sizeContent['value'] > 0) {
                    $sizeValue = (int)$sizeContent['value'];
                    $sizeUnit = $sizeContent['unit'];
                    $factor = 1;

                    foreach ($memoryMap as $key => $value) {
                        $key = (int)$key;
                        if ($key > $sizeValue) {
                            break;
                        }

                        $factor = $key;
                        $sizeUnit = array_replace($sizeUnit, $value);
                    }
                    $sizes[$propertyKey]['value'] = $sizeValue / $factor;
                    $sizes[$propertyKey]['unit'] = $sizeUnit;
                }
            }

            $importKey = $data['identifier'] . '|' . $data['dbname'];

            if ($data['dbsname']) {
                $importKey .= '|' . $data['dbsname'];
            }

            $properties = [
                'title' => [
                    'tag' => 'title',
                    'value' => $data['dbtable'],
                    'title' => 'LC__CATG__DATABASE_TABLE__TITLE'
                ],
                'row_count' => [
                    'tag' => 'row_count',
                    'value' => $data['rowcount'],
                    'title' => 'LC__CATG__DATABASE_TABLE__ROW_COUNT'
                ],
                'assigned_database' => [
                    'tag' => 'assigned_database',
                    'resolve' => $data['dbname'],
                    'title' => 'LC__CATG__DATABASE_TABLE__ASSIGNED_DATABASE'
                ],
                'assigned_schema' => [
                    'tag' => 'assigned_schema',
                    'resolve' => $data['dbsname'],
                    'title' => 'LC__CATG__DATABASE_TABLE__SCHEMA'
                ],
                'size' => [
                    'tag' => 'size',
                    'value' => $sizes['size']['value'],
                    'title' => 'LC__CATG__DATABASE_TABLE__SIZE'
                ],
                'size_unit' => $sizes['size']['unit'],
                'max_size' => [
                    'tag' => 'max_size',
                    'value' => $sizes['max_size']['value'],
                    'title' => 'LC__CATG__DATABASE_TABLE__MAX_SIZE'
                ],
                'max_size_unit' => $sizes['max_size']['unit'],
                'import_key' => [
                    'tag' => 'import_key',
                    'value' => $importKey,
                    'title' => 'LC__CATG__DATABASE_TABLE__IMPORTKEY'
                ]
            ];

            $l_return = [
                'data_id'    => null,
                'properties' => $properties
            ];

            return $l_return;
        }

        return null;
    }

    /**
     * @param $data
     *
     * @return array|null
     */
    private function prepareDatabase($data)
    {
        if (!empty($data)) {
            $unit = [
                'tag' => 'size_unit',
                'value' => 'B',
                'id' => defined_or_default('C__MEMORY_UNIT__B'),
                'const' => 'C__MEMORY_UNIT__B',
                'title_lang' => 'B',
                'title'      => 'LC__CATG__DATABASE_SA__SIZE_UNIT',
            ];

            $maxUnit = $unit;
            $maxUnit['tag'] = 'max_size_unit';
            $maxUnit['title'] = 'LC__CATG__DATABASE_SA__MAX_SIZE_UNIT';

            $memoryMap = $this->getMemoryMap();

            $sizes = [
                'size' => [
                    'value' => $data['size'],
                    'unit' => $unit
                ],
                'max_size' => [
                    'value' => $data['maxsize'],
                    'unit' => $maxUnit
                ]
            ];

            foreach ($sizes as $propertyKey => $sizeContent) {
                if ($sizeContent['value'] > 0) {
                    $sizeValue = (int)$sizeContent['value'];
                    $sizeUnit = $sizeContent['unit'];
                    $factor = 1;

                    foreach ($memoryMap as $key => $value) {
                        $key = (int)$key;
                        if ($key > $sizeValue) {
                            break;
                        }

                        $factor = $key;
                        $sizeUnit = array_replace($sizeUnit, $value);
                    }
                    $sizes[$propertyKey]['value'] = $sizeValue / $factor;
                    $sizes[$propertyKey]['unit'] = $sizeUnit;
                }
            }
            $schemes = explode(',', $data['schemes']);

            if (count($schemes)) {
                $schemes = array_map(function ($item) {
                    return ['title' => $item];
                }, $schemes);
            }

            $properties = [
                'title' => [
                    'tag' => 'title',
                    'value' => $data['dbname'],
                    'title' => 'LC__CATG__DATABASE__TITLE'
                ],
                'size' => [
                    'tag' => 'size',
                    'value' => $sizes['size']['value'],
                    'title' => 'LC__CATG__DATABASE_SA__SIZE'
                ],
                'size_unit' => $sizes['size']['unit'],
                'max_size' => [
                    'tag' => 'max_size',
                    'value' => $sizes['max_size']['value'],
                    'title' => 'LC__CATG__DATABASE_SA__MAX_SIZE'
                ],
                'max_size_unit' => $sizes['max_size']['unit'],
                'assigned_schemas' => [
                    'tag' => 'assigned_schemas',
                    'value' => $schemes,
                    'title' => 'LC__CATG__DATABASE_SA__SCHEMATA'
                ],
                'assigned_database' => [
                    'tag' => 'assigned_database',
                    'resolve' => $data['appname'],
                    'title' => 'LC__CATG__DATABASE_SA__SCHEMATA'
                ],
                'assigned_instance' => [
                    'tag' => 'assigned_instance',
                    'resolve' => $data['instancename'],
                    'title' => 'LC__CATG__DATABASE_SA__SCHEMATA'
                ],
                'import_key' => [
                    'tag' => 'import_key',
                    'value' => $data['identifier'],
                    'title' => 'LC__CATG__DATABASE_SA__IMPORTKEY'
                ]
            ];

            $l_return = [
                'data_id'    => null,
                'properties' => $properties
            ];

            return $l_return;
        }

        return null;
    }


    /**
     * @param $data
     * @param $dbms
     *
     * @return array|null
     * @throws Exception
     */
    private function prepareDbms($data, $dbms)
    {
        // We should always have the application in our system by now!
        if (!empty($data)) {
            $unit = [
                'tag' => 'size_unit',
                'value' => 'B',
                'id' => defined_or_default('C__MEMORY_UNIT__B'),
                'const' => 'C__MEMORY_UNIT__B',
                'title_lang' => 'B',
                'title'      => 'LC__CATG__DATABASE__SIZE_UNIT',
            ];

            $memoryMap = $this->getMemoryMap();

            if ($data['dbmaxsize'] > 0) {
                $data['dbmaxsize'] = (int)$data['dbmaxsize'];
                $factor = 1;
                foreach ($memoryMap as $key => $value) {
                    $key = (int)$key;
                    if ($key > $data['dbmaxsize']) {
                        break;
                    }
                    $factor = $key;
                    $unit = array_replace($unit, $value);
                }
                $data['dbmaxsize'] = $data['dbmaxsize'] / $factor;
            }

            $properties = [
                'instance_name' => [
                    'tag' => 'instance_name',
                    'value' => $data['instancename'],
                    'title' => 'LC__CATG__DATABASE__INSTANCE_NAME'
                ],
                'size' => [
                    'tag' => 'size',
                    'value' => $data['dbmaxsize'],
                    'title' => 'LC__CATG__DATABASE__SIZE'
                ],
                'size_unit' => $unit,
                'assigned_dbms' => [
                    'tag' => 'assigned_dbms',
                    'id'       => $dbms[isys_jdisc_dao_software::OBJ_ID],
                    'value' => $dbms[isys_jdisc_dao_software::OBJ_TITLE],
                    'type'     => $dbms[isys_jdisc_dao_software::OBJ_TYPE_CONST],
                    'type_id'  => $dbms[isys_jdisc_dao_software::OBJ_TYPE_ID],
                    'sysid'    => $dbms[isys_jdisc_dao_software::OBJ_SYSID],
                    'lc_title' => isys_application::instance()->container->get('language')->get($dbms[isys_jdisc_dao_software::OBJ_TYPE_TITLE]),
                    'title'    => $dbms[isys_jdisc_dao_software::OBJ_TITLE]
                ],
                'version' => [
                    'tag'   => 'version',
                    'id'        => $dbms[isys_jdisc_dao_software::OBJ_ID],
                    'title'     => $dbms[isys_jdisc_dao_software::OBJ_TITLE],
                    'sysid'     => $dbms[isys_jdisc_dao_software::OBJ_SYSID],
                    'type'      => $dbms[isys_jdisc_dao_software::OBJ_TYPE_ID],
                    'ref_id'    => null,
                    'ref_title' => $data['appversion'],
                    'ref_type'  => 'C__CATG__VERSION',
                    'lc_title'  => isys_application::instance()->container->get('language')
                        ->get('LC__CATG__VERSION_TITLE')
                ],
                'path'    => [
                    'tag'   => 'path',
                    'value' => $data['instancepath'],
                    'title' => 'LC__CATG__DATABASE__PATH'
                ],
                'port'    => [
                    'tag'   => 'port',
                    'value' => $data['port'],
                    'title' => 'LC__CATG__DATABASE__PORT'
                ],
                'port_name' => [
                    'tag'   => 'port_name',
                    'value' => $data['portname'],
                    'title' => 'LC__CATG__DATABASE__PORT_NAME'
                ],
                'import_key' => [
                    'tag' => 'import_key',
                    'value' => $data['identifier'],
                    'title' => 'LC__CATG__DATABASE__IMPORTKEY'
                ]
            ];

            $l_return = [
                'data_id'    => null,
                'properties' => $properties
            ];

            return $l_return;
        }

        return null;
    }

    /**
     * @param int $objectId
     * @param int $deviceId
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function createDatabaseConnections($objectId, $deviceId)
    {
        $connections = $this->databaseConnections[$deviceId];

        foreach ($connections as $connectionKey => $connectionData) {
            list($dbmsTitle, $dbmsVersion, $dbmsInstance) = explode('|', $connectionKey);

            $queryDbms = 'SELECT * FROM isys_catg_database_list WHERE isys_catg_database_list__isys_obj__id = ' . $this->convert_sql_id($objectId) . ' 
                AND isys_catg_database_list__import_key = ' . $this->convert_sql_text($connectionKey);
            $queryDatabase = 'SELECT * FROM isys_catg_database_sa_list WHERE isys_catg_database_sa_list__isys_obj__id = ' . $this->convert_sql_id($objectId) . '
                AND isys_catg_database_sa_list__import_key = ' . $this->convert_sql_text($connectionKey);
            $queryDatabaseTable = 'SELECT * FROM isys_catg_database_table_list WHERE isys_catg_database_table_list__isys_obj__id = ' . $this->convert_sql_id($objectId) . '
                AND isys_catg_database_table_list__import_key LIKE ' . $this->convert_sql_text($connectionKey . '%');
            $queryDatabaseSchema = 'SELECT * from isys_catg_database_sa_list_2_isys_database_schema main
                LEFT JOIN isys_database_schema ref ON ref.isys_database_schema__id = main.isys_database_schema__id
                WHERE main.isys_catg_database_sa_list__id = %s';


            $dbmsData = $this->retrieve($queryDbms)->get_row();
            $databaseResult = $this->retrieve($queryDatabase);
            $databaseTableResult = $this->retrieve($queryDatabaseTable);
            $databaseSchemaData = [];
            $databaseData = [];

            // 1. Connection between dbms and database

            while ($data = $databaseResult->get_row()) {
                if (!empty($dbmsData)) {
                    $update = 'UPDATE isys_catg_database_sa_list SET 
                        isys_catg_database_sa_list__isys_catg_application_list__id = 
                            ' . $this->convert_sql_id($dbmsData['isys_catg_database_list__isys_catg_application_list__id']) . ',
                        isys_catg_database_sa_list__isys_catg_database_list__id = ' . $this->convert_sql_id($dbmsData['isys_catg_database_list__id']) . ' 
                        WHERE isys_catg_database_sa_list__id = ' . $data['isys_catg_database_sa_list__id'];
                    $this->update($update);
                }
                $key = $data['isys_catg_database_sa_list__import_key'] . '|' . $data['isys_catg_database_sa_list__title'];
                $databaseData[$key] = [
                    'id' => $data['isys_catg_database_sa_list__id'],
                    'schemas' => []
                ];


                // Retrieve Schemas of the database
                $query = sprintf($queryDatabaseSchema, $data['isys_catg_database_sa_list__id']);
                $databaseSchemaResult = $this->retrieve($query);
                while ($schemaData = $databaseSchemaResult->get_row()) {
                    $databaseData[$key]['schemas'][$schemaData['isys_database_schema__title']] = $schemaData['isys_database_schema__id'];
                }
            }

            // 2. Connection between database table and database
            while ($data = $databaseTableResult->get_row()) {
                list($dbmsTitle, $dbmsVersion, $dbmsInstance, $databaseTitle, $databaseSchema) = explode('|', $data['isys_catg_database_table_list__import_key']);
                $databaseKey = $dbmsTitle . '|' . $dbmsVersion . '|' . $dbmsInstance . '|' . $databaseTitle;
                $databaseSchemaId = null;

                if (!isset($databaseData[$databaseKey])) {
                    continue;
                }

                if (isset($databaseData[$databaseKey]['schemas'][$databaseSchema])) {
                    $databaseSchemaId = $databaseData[$databaseKey]['schemas'][$databaseSchema];
                }

                $update = 'UPDATE isys_catg_database_table_list SET 
                    isys_catg_database_table_list__isys_catg_database_sa_list__id = ' . $this->convert_sql_id($databaseData[$databaseKey]['id']);

                if ($databaseSchemaId !== null) {
                    $update .= ', isys_catg_database_table_list__isys_database_schema__id = ' . $this->convert_sql_id($databaseSchemaId);
                }

                $update .= ' WHERE isys_catg_database_table_list__id = ' . $this->convert_sql_id($data['isys_catg_database_table_list__id']);

                $this->update($update);
            }
        }
        return $this->apply_update();
    }

    /**
     * Cache port relevant data into temporary table
     *
     * @param $p_id
     * @param $p_data
     * @param $p_type
     *
     * @return $this|void
     */
    public function cache_data($p_id, $p_data, $p_type)
    {
        if (count($this->databaseConnections) > 0) {
            parent::cache_data($p_id, $this->databaseConnections, self::C__CACHE__DATABASE);
            unset($this->databaseConnections);
        }
    }

    /**
     * Load relevant data from temporary table
     *
     * @param $p_obj_id
     *
     * @throws Exception
     */
    public function load_cache($p_obj_id, $p_type = null)
    {
        $result = parent::load_cache($p_obj_id, ' AND type = ' . self::C__CACHE__DATABASE);

        if ($this->m_db->num_rows($result) > 0) {
            $data = $this->m_db->fetch_row($result);
            $this->databaseConnections = isys_format_json::decode($data[1]);
        } else {
            return false;
        }

        return true;
    }
}
