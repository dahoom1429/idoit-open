<?php

/**
 * Class isys_jdisc_dao_category_connection_endpoint
 */
class isys_jdisc_dao_category_connection_endpoint extends isys_jdisc_dao_category implements isys_jdisc_dao_category_interface
{
    /**
     * @var bool
     */
    protected $isSelectable = false;

    /**
     * @var string
     */
    protected $category = 'C__CATG__CONNECTION_ENDPOINT';

    /**
     * @var string
     */
    protected $title = 'LC__CMDB__CATG__CONNECTION_ENDPOINT';

    /**
     * @var array
     */
    private $interfaceConnections = [];

    /**
     * @var array
     */
    private $cachedInterfaces = [];

    /**
     * @var array
     */
    private $endPointData = [];

    /**
     * @param $deviceId
     * @param $portId
     * @param $interfaceId
     */
    private function addToAssignedInterfaceConnection($deviceId, $portId, $interfaceId)
    {
        $this->interfaceConnections[$deviceId][$portId] = $interfaceId;
    }

    /**
     * @param $id
     * @param $data
     */
    public function addEndpointData($id, $data)
    {
        $this->endPointData[$id] = $data;
    }

    /**
     * @return string
     */
    private function getQuery()
    {
        // At first we prepare the SQL to receive the ports.
        return 'SELECT m.*, d.name as devicename, dl.id AS type_id, dl.singular AS type_custom, os.id AS os_id, os.osversion AS os_custom, mo.id AS if_id, ift.name AS port_type
                , (SELECT string_agg(distinct CONCAT(m1.ifdescr, \'||\', d1.name), \', \') from macmacrelation as mmr
                    INNER JOIN mac as m1 ON m1.id = mmr.macid1
                    INNER JOIN device as d1 On d1.id = m1.deviceid
                        WHERE macid2 = m.id) as connectedToMacId1
                , (SELECT string_agg(distinct CONCAT(m2.ifdescr,\'||\', d2.name), \', \') from macmacrelation as mmr
                   INNER JOIN mac as m2 ON m2.id = mmr.macid2
                   INNER JOIN device as d2 On d2.id = m2.deviceid
                        WHERE macid1 = m.id) as connectedToMacId2
            FROM mac AS m LEFT JOIN device AS d ON d.id = m.deviceid LEFT JOIN devicetypelookup AS dl ON dl.id = d.type 
            LEFT JOIN operatingsystem AS os ON d.operatingsystemid = os.id LEFT JOIN module AS mo ON mo.id = m.moduleid 
            LEFT JOIN interfacetypelookup AS ift ON ift.id = m.iftype %s';
    }

    /**
     * @param int $deviceId
     *
     * @return string
     *
     * @throws Exception
     */
    private function getCondition(int $deviceId)
    {
        $networkDao = isys_jdisc_dao_network::instance(isys_application::instance()->container->get('database'));
        $interfaceTypes = $networkDao->getInterfaceTypes();
        $portFilter = $networkDao->get_port_filter_import_type();

        $condition = 'WHERE (m.id IN (SELECT macid1 from macmacrelation) OR m.id IN (SELECT macid2 from macmacrelation)) AND 
            (m.ifdescr IS NOT NULL OR m.ifphysaddress IS NOT NULL) AND m.deviceid = ' . $this->convert_sql_int($deviceId) . ' 
            AND (m.iftype NOT IN (' . implode(
            ',',
            array_merge(
                $interfaceTypes['virtual']['content'],
                $interfaceTypes['vlan']['content'],
                $interfaceTypes['loopback']['content'],
                $interfaceTypes['tunnel']['content'],
                $interfaceTypes['fibreChannel']['content']
            )
        ) . ') OR m.iftype IS NULL)';

        if (count($portFilter) > 0) {
            $condition .= $networkDao->get_port_filter_query(2);
        }

        return $condition;
    }

    /**
     * @param int $objectId
     *
     * @return array
     * @throws Exception
     */
    private function getEndpointEntryChecks(int $objectId)
    {
        $dbInstance = isys_application::instance()->container->get('database');
        $result = isys_cmdb_dao_category_g_connection_endpoint::instance($dbInstance)
            ->get_data(null, $objectId);
        $connectionDao = isys_cmdb_dao_connection::instance($dbInstance);

        $return = [];
        while ($data = $result->get_row()) {
            $connectedToObjectId = $data['isys_catg_connection_endpoint_list__isys_obj__id__connectedto'];
            $connectedToObjectData = $connectionDao->get_object($connectedToObjectId)->get_row();

            if ($data['isys_catg_connection_endpoint_list__isys_obj__id__connectedto'] == $objectId) {
                $return[$data['isys_catg_connection_endpoint_list__title__connectedto'] . '||' . $data['isys_catg_connection_endpoint_list__title'] . '||' . $data['objectA_title']] = [
                    'id' => $data['isys_catg_connection_endpoint_list__id'],
                    'object' => $data['isys_catg_connection_endpoint_list__isys_obj__id'],
                    'mainObject' => $objectId
                ];
            } else {
                $return[$data['isys_catg_connection_endpoint_list__title'] . '||' . $data['isys_catg_connection_endpoint_list__title__connectedto'] . '||' . $data['objectB_title']] = [
                    'id' => $data['isys_catg_connection_endpoint_list__id'],
                    'object' => $data['isys_catg_connection_endpoint_list__isys_obj__id__connectedto'],
                    'mainObject' => $objectId
                ];
            }
        }
        return $return;
    }

    /**
     * @param int   $deviceId
     * @param false $asRaw
     * @param array $deviceToObjectIds
     * @param array $idoitObjects
     * @param null  $currentObjectId
     *
     * @return array|void
     * @throws Exception
     */
    public function getDataForImport(int $deviceId, $asRaw = false, $deviceToObjectIds = [], $idoitObjects = [], $currentObjectId = null)
    {
        $return = [];
        /**
         * @var $networkDao isys_jdisc_dao_network
         */
        $networkDao = isys_jdisc_dao_network::instance(isys_application::instance()->container->get('database'));
        $objectId = (int)(isset($deviceToObjectIds[$deviceId]) ? $deviceToObjectIds[$deviceId] : $currentObjectId);

        if ($objectId === 0) {
            return;
        }

        $entryChecks = $this->getEndpointEntryChecks($objectId);
        $result = $this->fetch(sprintf($this->getQuery(), $this->getCondition($deviceId)));
        $foundData = $this->m_pdo->num_rows($result);

        $this->m_log->debug('> Found ' . $foundData . ' rows for ports');
        $speedUnits = $networkDao->getDialogData('port_speed');

        if ($foundData > 0) {
            $newId = isys_cmdb_dao::instance(isys_application::instance()->container->get('database'))
                ->get_last_id_from_table('isys_catg_port_list');
            while ($rowData = $this->m_pdo->fetch_row_assoc($result)) {
                if ($rowData['if_id'] > 0) {
                    // Network interface
                    $this->addToAssignedInterfaceConnection($deviceId, $rowData['id'], $rowData['if_id']);
                }

                if ($rowData['port_type'] !== null) {
                    // Retrieve the port type
                    if (!isset($networkDao->getDialogData('port_type')[$rowData['port_type']])) {
                        $networkDao->addDialogData('port_type', $rowData['port_type'], [
                            isys_cmdb_dao_dialog_admin::instance($this->m_db)
                                ->create('isys_port_type', $rowData['port_type'], null, null, C__RECORD_STATUS__NORMAL),
                            null,
                            $rowData['port_type']
                        ]);
                    }
                    $rowData['port_type'] = $networkDao->getDialogData('port_type')[$rowData['port_type']];
                }

                $rowData['speedUnit'] = $networkDao->getDialogData(['port_speed'])['C__PORT_SPEED__BIT_S'];

                // Set speed unit
                if ($rowData['ifspeed'] > 1000) {
                    $rowData['speedUnit'] = $networkDao->getDialogData(['port_speed'])['C__PORT_SPEED__KBIT_S'];
                    if ($rowData['ifspeed'] >= 1000000) {
                        $rowData['speedUnit'] = $networkDao->getDialogData(['port_speed'])['C__PORT_SPEED__MBIT_S'];
                        if ($rowData['ifspeed'] >= 1000000000) {
                            $rowData['speedUnit'] = $networkDao->getDialogData(['port_speed'])['C__PORT_SPEED__GBIT_S'];
                        }
                    }
                }

                $connectionOne = !empty($rowData['connectedToMacId1']) ? explode(',', $rowData['connectedToMacId1']): [];
                $connectionTwo = !empty($rowData['connectedToMacId2']) ? explode(',', $rowData['connectedToMacId2']): [];
                $connections = array_merge($connectionOne, $connectionTwo);

                foreach ($connections as $connection) {
                    $newId++;
                    [$connectorTitle, $connectorObjectTitle] = explode('||', $connection);
                    $update = isset($entryChecks[$rowData['ifdescr'] . '||' . $connection]) ||
                        isset($entryChecks[$connectorTitle . '||' . $rowData['ifdescr'] . '||' . $rowData['devicename']]);
                    /*
                     * @note: comparestring is used for the update which is handled in create_port_connections.
                     * */
                    $this->addEndpointData($rowData['id'] . '||' . $newId, new isys_array([
                        'title'       => $rowData['ifdescr'],
                        'interface'   => $rowData['if_id'],
                        'type'        => $rowData['port_type'],
                        'speed'       => $rowData['ifspeed'],
                        'speedUnit'   => $rowData['speedUnit'],
                        'objectId'    => $objectId,
                        'objectTitle' => $rowData['devicename'],
                        'connections' => $connection,
                        'update'      => $update
                    ]));

                    if ($update === false) {
                        // Only new entries are being parsed
                        $return[] = $this->prepareEndpoint($rowData, $newId);
                    }
                }
            }
        }

        $this->m_pdo->free_result($result);

        if ($asRaw === true || count($return) === 0) {
            return $return;
        } else {
            return [
                C__DATA__TITLE      => isys_application::instance()->container->get('language')
                    ->get('LC__CATD__PORT'),
                'const'             => 'C__CATG__CONNECTION_ENDPOINT',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => $return
            ];
        }
    }

    /**
     * @param $data
     * @param $newId
     *
     * @return array|null
     *
     * @throws Exception
     */
    private function prepareEndpoint($data, $newId)
    {
        if (!empty($data)) {
            $language = isys_application::instance()->container->get('language');
            $type = [
                'tag'        => 'type',
                'value'      => null,
            ];

            if (!empty($data['port_type'])) {
                $type = [
                    'tag'        => 'type',
                    'value'      => $data['port_type'][2],
                    'id'         => $data['port_type'][0],
                    'const'      => $data['port_type'][1],
                    'title_lang' => $language->get($data['port_type'][2]),
                    'title'      => 'LC__CMDB__CATG__TYPE'
                ];
            }

            $speedUnit = [
                'tag'        => 'speedUnit',
                'value'      => $data['speedUnit'][2],
                'id'         => $data['speedUnit'][0],
                'const'      => $data['speedUnit'][1],
                'title_lang' => $language->get($data['speedUnit'][2]),
                'title'      => 'LC__CMDB__CATG__UNIT'
            ];


            $return = [
                'data_id'    => $newId,
                'properties' => [
                    'title'       => [
                        'tag'   => 'title',
                        'value' => $data['ifdescr'],
                        'title' => 'LC__CMDB__CATG__TITLE'
                    ],
                    'type'   => $type,
                    'speedUnit' => $speedUnit,
                ]
            ];

            $convertedSpeed = isys_convert::speed($data['ifspeed'], $data['speedUnit'][0], C__CONVERT_DIRECTION__BACKWARD);
            $return['properties']['speed'] = [
                'tag'   => 'speed',
                'value' => $convertedSpeed,
                'title' => 'LC__CMDB__CATG__PORT__SPEED'
            ];

            return $return;
        }
        return null;
    }

    /**
     * Cache port relevant data into temporary table
     *
     * @param $id
     * @param $data
     * @param $type
     *
     * @return $this|void
     */
    public function cache_data($id, $data, $type)
    {
        if (count($this->interfaceConnections)) {
            parent::cache_data($id, $this->interfaceConnections, self::C__CACHE__ENDPOINT_INTERFACE_CONNECTIONS);
            unset($this->interfaceConnections);
        }
        if (count($this->endPointData)) {
            parent::cache_data($id, $this->endPointData, self::C__CACHE__ENDPOINT_CONNECTIONS);
            unset($this->endPointData);
        }
    }

    /**
     * @param        $p_obj_id
     * @param string $p_condition
     *
     * @return bool|mixed
     * @throws \idoit\Exception\JsonException
     */
    public function load_cache($p_obj_id, $p_condition = '')
    {
        $l_res = parent::load_cache(
            $p_obj_id,
            ' AND type IN (' . implode(',', [
                self::C__CACHE__ENDPOINT_INTERFACE_CONNECTIONS,
                self::C__CACHE__ENDPOINT_CONNECTIONS,
                self::C__CACHE__INTERFACE
            ]) . ')'
        );

        $this->endPointData = new isys_array();
        $this->interfaceConnections = new isys_array();
        $this->cachedInterfaces = new isys_array();

        if ($this->m_db->num_rows($l_res) > 0) {
            while ($data = $this->m_db->fetch_row($l_res)) {
                switch ($data[2]) {
                    case self::C__CACHE__INTERFACE:
                        $this->cachedInterfaces = isys_format_json::decode($data[1]);
                        break;
                    case self::C__CACHE__ENDPOINT_CONNECTIONS:
                        $this->endPointData = isys_format_json::decode($data[1]);
                        break;
                    case self::C__CACHE__ENDPOINT_INTERFACE_CONNECTIONS:
                        $this->interfaceConnections = isys_format_json::decode($data[1]);
                        break;
                }
            }
            $this->m_db->free_result($l_res);
        } else {
            return false;
        }

        return true;
    }

    /**
     * Create connections between the endpoints and the category interface
     */
    public function createEndpointConnections()
    {
        if (is_array($this->endPointData) && !empty($this->endPointData)) {
            $categoryDao = isys_cmdb_dao_category_g_connection_endpoint::instance(isys_application::instance()->container->get('database'));

            foreach ($this->endPointData as $data) {
                $endPointChecks = $this->getEndpointEntryChecks($data['objectId']);
                [$connectorBTitle, $connectorBObjectTitle] = explode('||', $data['connections']);
                $connectorBObjectId = $categoryDao->get_obj_id_by_title($connectorBObjectTitle);
                $connectorATitle = $data['title'];
                $connectorAObjectId = $data['objectId'];
                $connectorAObjectTitle = $data['devicename'] ?: $categoryDao->obj_get_title_by_id_as_string($connectorAObjectId);
                $update = $data['update'];
                $connectionExists = false;
                $connectionExistsPartly = false;
                $interface = $this->cachedInterfaces[$data['interface']];

                if (isset($endPointChecks[$connectorATitle . '||' . $connectorBTitle . '||' . $connectorBObjectTitle])) {
                    // Connection exists
                    $connectionExists = true;
                    $connectorData = $endPointChecks[$connectorATitle . '||' . $connectorBTitle . '||' . $connectorBObjectTitle];
                }

                if (isset($endPointChecks[$connectorBTitle . '||' . $connectorATitle . '||' . $connectorAObjectTitle])) {
                    // Connection exists
                    $connectionExists = true;
                    $connectorData = $endPointChecks[$connectorBTitle . '||' . $connectorATitle . '||' . $connectorAObjectTitle];
                }

                if (isset($endPointChecks[$connectorATitle . '||||'])) {
                    // Connection exists partly
                    $connectionExistsPartly = true;
                    $connectorData = $endPointChecks[$connectorATitle . '||||'];
                    $connectorTitle = $connectorBTitle;
                    $connectorObjectId = $connectorBObjectId;
                    $update = true;
                }

                if (isset($endPointChecks[$connectorBTitle . '||||'])) {
                    // Connection exists partly
                    $connectionExistsPartly = true;
                    $connectorData = $endPointChecks[$connectorBTitle . '||||'];
                    $connectorTitle = $connectorATitle;
                    $connectorObjectId = $connectorAObjectId;
                    $update = true;
                }

                $syncData = [
                    'interface' => is_array($interface) ? $interface['id']: null,
                    'speed' => $data['speed'],
                    'speedUnit' => $data['speedUnit'][0],
                    'type' => $data['type'][0]
                ];

                if ($connectionExists === false) {
                    $syncData['isys_obj__id'] = $connectorAObjectId;
                    $syncData['title'] = $connectorATitle;
                    $syncData['connectedToTitle'] = $connectorBTitle;
                    $syncData['connectedTo'] = $connectorBObjectId;

                    // Create a new connection
                    $categoryDao->create_data($syncData);
                    continue;
                }

                if ($update) {
                    if ($connectionExistsPartly === true) {
                        $syncData['connectedToTitle'] = $connectorTitle;
                        $syncData['connectedTo'] = $connectorObjectId;
                    }
                    // Update entry
                    $categoryDao->save_data(
                        $connectorData['id'],
                        $syncData
                    );
                }
            }
        }
    }
}
