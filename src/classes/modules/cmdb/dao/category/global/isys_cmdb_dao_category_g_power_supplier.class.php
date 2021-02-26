<?php
use idoit\Component\Property\Type\FloatProperty;
use idoit\Component\Property\Property;

/**
 * i-doit
 *
 * DAO: global category for power suppliers
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_power_supplier extends isys_cmdb_dao_category_global
{

    /**
     * Category's name. Will be used for the identifier, constant, main table,
     * and many more.
     *
     * @var string
     */
    protected $m_category = 'power_supplier';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Callback method for the "assigned connector" object-browser.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     */
    public function callback_property_assigned_connector(isys_request $p_request)
    {
        $l_dao = new isys_cmdb_dao_cable_connection($this->get_database_component());

        return $l_dao->get_assigned_connector_id($p_request->get_row("isys_catg_power_supplier_list__isys_catg_connector_list__id"));
    }

    /**
     * Return Category Data
     *
     * @param null   $p_catg_list_id
     * @param null   $p_obj_id
     * @param string $p_condition
     * @param null   $p_filter
     * @param null   $p_status
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = 'SELECT isys_obj.*, isys_catg_power_supplier_list.*, 
                         connected.isys_catg_connector_list__title AS connector_name, 
                         connected.isys_catg_connector_list__id AS con_connector, 
                         mine.*
            FROM isys_catg_power_supplier_list
            INNER JOIN isys_obj                               ON isys_catg_power_supplier_list__isys_obj__id = isys_obj__id
            LEFT  JOIN isys_catg_connector_list AS mine       ON mine.isys_catg_connector_list__id = isys_catg_power_supplier_list__isys_catg_connector_list__id
            LEFT  JOIN isys_cable_connection                  ON mine.isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id
            LEFT  JOIN isys_catg_connector_list AS connected  ON connected.isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id
                                                              AND (connected.isys_catg_connector_list__id != mine.isys_catg_connector_list__id 
                                                              OR connected.isys_catg_connector_list__id IS NULL)
            WHERE TRUE ';

        $l_sql .= $p_condition;

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_catg_list_id)) {
            $l_sql .= ' AND (isys_catg_power_supplier_list__id = ' . $this->convert_sql_id($p_catg_list_id) . ') ';
        }

        if (!empty($p_status)) {
            $l_sql .= ' AND (isys_catg_power_supplier_list__status = ' . $this->convert_sql_int($p_status) . ')';
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @throws Exception
     */
    protected function properties()
    {
        return [
            'title' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_connector_list__title',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'mine'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false
                ]
            ]),

            'volt'               => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__POWER_SUPPLIER__VOLT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Volt'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_power_supplier_list__volt'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__POWER_SUPPLIER__VOLT'
                    ]
                ]),
            'watt'               => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__POWER_SUPPLIER__WATT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Watt'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_power_supplier_list__watt'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__POWER_SUPPLIER__WATT'
                    ]
                ]),
            'ampere'             => (new FloatProperty(
                'C__CMDB__CATG__POWER_SUPPLIER__AMPERE',
                'LC__CMDB__CATG__POWER_SUPPLIER__AMPERE',
                'isys_catg_power_supplier_list__ampere',
                'isys_catg_power_supplier_list'
            ))->setPropertyUiOffset(
                Property::C__PROPERTY__UI__PARAMS,
                []
            ),
            'assigned_connector' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__POWER_SUPPLIER__DEST',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned to connector'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_power_supplier_list__isys_catg_connector_list__id',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'connected_connector',
                    C__PROPERTY__DATA__FIELD_ALIAS => 'con_connector',
                    C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_power_supplier_list
                            INNER JOIN isys_catg_connector_list con1 ON con1.isys_catg_connector_list__id = isys_catg_power_supplier_list__isys_catg_connector_list__id
                            LEFT JOIN isys_catg_connector_list con2 ON con2.isys_catg_connector_list__isys_cable_connection__id = con1.isys_catg_connector_list__isys_cable_connection__id
                              AND con2.isys_catg_connector_list__id != con1.isys_catg_connector_list__id
                            INNER JOIN isys_obj ON isys_obj__id = con2.isys_catg_connector_list__isys_obj__id',
                        'isys_catg_power_supplier_list',
                        'isys_catg_power_supplier_list__id',
                        'isys_catg_power_supplier_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_power_supplier_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__POWER_SUPPLIER__DEST',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPopupType'                                  => 'browser_cable_connection_ng',
                        isys_popup_browser_object_ng::C__SECOND_SELECTION => true,
                        isys_popup_browser_object_ng::C__CAT_FILTER       => 'C__CATG__NETWORK;C__CATG__CONTROLLER_FC_PORT;C__CATG__CABLING',
                        isys_popup_browser_object_ng::C__SECOND_LIST      => 'isys_cmdb_dao_category_g_network_port::object_browser',
                        'p_strValue'                                      => new isys_callback([
                            'isys_cmdb_dao_category_g_power_supplier',
                            'callback_property_assigned_connector'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => true,
                    C__PROPERTY__PROVIDES__VIRTUAL   => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'assigned_connector'
                    ]
                ]
            ]),
            'connector'          => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONNECTOR__ASSIGNED_CONNECTOR',
                    C__PROPERTY__INFO__DESCRIPTION => 'Connector'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_power_supplier_list__isys_catg_connector_list__id',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'connected',
                    C__PROPERTY__DATA__FIELD_ALIAS => 'con_connector',
                    C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT con2.isys_catg_connector_list__title
                            FROM isys_catg_power_supplier_list
                            INNER JOIN isys_catg_connector_list con1 ON con1.isys_catg_connector_list__id = isys_catg_power_supplier_list__isys_catg_connector_list__id
                            LEFT JOIN isys_catg_connector_list con2 ON con2.isys_catg_connector_list__isys_cable_connection__id = con1.isys_catg_connector_list__isys_cable_connection__id
                              AND con2.isys_catg_connector_list__id != con1.isys_catg_connector_list__id',
                        'isys_catg_power_supplier_list',
                        'isys_catg_power_supplier_list__id',
                        'isys_catg_power_supplier_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_power_supplier_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__INDEX       => true
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ]
            ]),
            'connector_sibling'  => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONNECTOR__SIBLING_IN_OR_OUT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned Input/Output'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_connector_list__isys_catg_connector_list__id'
                ],
                // @todo This property has no field ID and has to be renamed.
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connector'
                    ]
                ]
            ]),
            'assigned_category'  => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                // This property has no UI field.
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONNECTOR__CATEGORY_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CATG__CONNECTOR__CATEGORY_TYPE'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_connector_list__assigned_category'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__VIRTUAL   => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_connector_assigned_category'
                    ]
                ]
            ]),
            'description'        => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_power_supplier_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__POWER_SUPPLIER', 'C__CATG__POWER_SUPPLIER')
                ]
            ])
        ];
    }

    /**
     * Synchronizes data
     *
     * @param     $p_category_data
     * @param     $p_object_id
     * @param int $p_status
     *
     * @return bool
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $this->m_sync_catg_data = $p_category_data;
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    $p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        C__RECORD_STATUS__NORMAL,
                        null,
                        $this->get_property('volt'),
                        $this->get_property('watt'),
                        $this->get_property('ampere'),
                        $this->get_property('title'),
                        $this->get_property('description'),
                        $this->get_property('connector_sibling')
                    );

                    /**
                     * @see ID-5589
                     *
                     * create() inserted a new connector connection
                     *
                     * Let us retrieve it otherwise save() will overwrite it
                     * and lose the connected ahead connector
                     */
                    $createdConnector = $this->get_connector($p_category_data['data_id']);
                    // no break
                case isys_import_handler_cmdb::C__UPDATE:

                    // Prefer existing connector over provided one
                    $connector = $createdConnector ?: $this->get_property('connector');

                    $l_indicator = $this->save(
                        $p_category_data['data_id'],
                        C__RECORD_STATUS__NORMAL,
                        $connector,
                        $this->get_property('assigned_connector'),
                        null,
                        $this->get_property('volt'),
                        $this->get_property('watt'),
                        $this->get_property('ampere'),
                        $this->get_property('title'),
                        $this->get_property('description'),
                        $this->get_property('connector_sibling')
                    );
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Save global category power_supplier.
     *
     * @param $p_cat_level
     * @param &$p_intOldRecStatus
     *
     * @return int|null
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws Exception
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_intErrorCode = -1; // ErrorCode
        $l_catdata = $this->get_general_data();
        $p_intOldRecStatus = $l_catdata['isys_catg_power_supplier_list__status'];
        $l_bRet = true;

        $l_milli_volt = $_POST['C__CMDB__CATG__POWER_SUPPLIER__VOLT'];
        $l_milli_watt = $_POST['C__CMDB__CATG__POWER_SUPPLIER__WATT'];
        $l_milli_ampere = $_POST['C__CMDB__CATG__POWER_SUPPLIER__AMPERE'];

        $l_dao_connector = new isys_cmdb_dao_category_g_connector($this->m_db);

        if (empty($l_catdata['isys_catg_power_supplier_list__isys_catg_connector_list__id'])) {
            $l_catdata['isys_catg_power_supplier_list__isys_catg_connector_list__id'] = $l_dao_connector->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__CONNECTOR__OUTPUT,
                null,
                null,
                $this->obj_get_title_by_id_as_string($_GET[C__CMDB__GET__OBJECT])
            );
        }

        $l_cable_name = $_POST['C__CATG__POWER_SUPPLIER__DEST__CABLE_NAME'];
        $l_cableID = $_POST['C__CATG__POWER_SUPPLIER__CABLE__HIDDEN'];
        $l_connected_connector = $_POST['C__CATG__POWER_SUPPLIER__DEST__HIDDEN'];

        if ($l_catdata['isys_catg_power_supplier_list__id'] != '') {
            $l_bRet = $this->save(
                $l_catdata['isys_catg_power_supplier_list__id'],
                C__RECORD_STATUS__NORMAL,
                $l_catdata['isys_catg_power_supplier_list__isys_catg_connector_list__id'],
                $l_connected_connector,
                $l_cableID,
                $l_milli_volt,
                $l_milli_watt,
                $l_milli_ampere,
                ($l_cable_name ?: isys_application::instance()->container->get('language')
                        ->get('LC__CMDB__CATG__POWER_SUPPLIER') . ' ' . $l_catdata['isys_catg_power_supplier_list__isys_obj__id']),
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()],
                null
            );
        }

        return $l_bRet == true ? null : $l_intErrorCode;
    }

    /**
     * Save category entry
     *
     * @param        $p_catlevel
     * @param int    $p_status
     * @param        $p_connectorRearID
     * @param        $p_connectorAheadID
     * @param        $p_cableID
     * @param null   $p_volt
     * @param null   $p_watt
     * @param null   $p_ampere
     * @param string $p_title
     * @param string $p_description
     * @param null   $p_connector_sibling
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function save(
        $p_catlevel,
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_connectorRearID,
        $p_connectorAheadID,
        $p_cableID,
        $p_volt = null,
        $p_watt = null,
        $p_ampere = null,
        $p_title = '',
        $p_description = '',
        $p_connector_sibling = null
    ) {
        $success = false;
        $l_strSql = 'UPDATE isys_catg_power_supplier_list SET 
                    isys_catg_power_supplier_list__description                  = ' . $this->convert_sql_text($p_description) . ', 
                    isys_catg_power_supplier_list__volt                         = ' . $this->convert_sql_text($p_volt) . ', 
                    isys_catg_power_supplier_list__watt                         = ' . $this->convert_sql_text($p_watt) . ', 
                    isys_catg_power_supplier_list__ampere                       = ' . $this->convert_sql_text($p_ampere) . ',
                    isys_catg_power_supplier_list__status                       = ' . $this->convert_sql_int($p_status) . ',
                    isys_catg_power_supplier_list__isys_catg_connector_list__id = ' . $this->convert_sql_id($p_connectorRearID) . ' 
                    WHERE ' . 'isys_catg_power_supplier_list__id                = ' . $this->convert_sql_id($p_catlevel) . ';';

        $this->m_strLogbookSQL = $l_strSql;

        if ($this->update($l_strSql) && $this->apply_update()) {
            $connectorId = $this->get_connector($p_catlevel);

            if (!is_numeric($connectorId)) {
                throw new isys_exception_cmdb("Error: Your FC-Port has lost its connector reference and is therefore inconsistent. " .
                    "You should remove and recreate it in order to reference any other port.");
            }

            $connectorUpdates = [
                'isys_catg_connector_list__title' => $this->convert_sql_text($p_title)
            ];

            if ($p_connector_sibling > 0) {
                $connectorUpdates['isys_catg_connector_list__isys_catg_connector_list__id'] = $this->convert_sql_id($p_connector_sibling);
            }

            $success = isys_cmdb_dao_cable_connection::instance($this->m_db)
                ->handleConnectorUpdate($connectorId, false, $p_connectorAheadID, $p_cableID, $p_title, $connectorUpdates);
        }

        return $success;
    }

    /**
     * Attach object to USV
     *
     * @param array $p_post
     *
     * @return int
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @desc Save global category power_supplier element
     */
    public function attachObjects(array $p_post)
    {
        global $g_comp_database;
        $p_new_id = -1;

        // CREATE CONNECTOR
        $l_strTitle = $this->get_obj_name_by_id_as_string($_GET[C__CMDB__GET__OBJECT]);

        $l_dao_connector = new isys_cmdb_dao_category_g_connector($g_comp_database);
        $l_list_id = $l_dao_connector->create($_GET[C__CMDB__GET__OBJECT], C__CONNECTOR__OUTPUT, null, null, null, null, null, null, "C__CATG__POWER_SUPPLIER", null);

        $l_strSql = 'INSERT IGNORE INTO ' . 'isys_catg_power_supplier_list ' . 'SET ' . 'isys_catg_power_supplier_list__isys_catg_connector_list__id = ' .
            $this->convert_sql_id($l_list_id) . ', ' . 'isys_catg_power_supplier_list__isys_obj__id	= ' . $this->convert_sql_id($_GET[C__CMDB__GET__OBJECT]) . ', ' .
            'isys_catg_power_supplier_list__status 		= ' . C__RECORD_STATUS__NORMAL . ' ' . ';';

        $this->m_strLogbookSQL = $l_strSql;

        if ($this->update($l_strSql) && $this->apply_update()) {
            $p_new_id = $this->get_last_insert_id();
        }

        return $p_new_id;
    }

    /**
     * Create new category entry
     *
     * @param        $p_cat_level
     * @param        $p_status
     * @param        $p_connection_id
     * @param null   $p_volt
     * @param null   $p_watt
     * @param null   $p_ampere
     * @param string $p_title
     * @param string $p_description
     * @param        $p_connector_sibling
     *
     * @return int
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     */
    public function create(
        $p_cat_level,
        $p_status,
        $p_connection_id,
        $p_volt = null,
        $p_watt = null,
        $p_ampere = null,
        $p_title = '',
        $p_description = '',
        $p_connector_sibling
    ) {
        global $g_comp_database;
        $p_new_id = -1;

        // CREATE CONNECTOR
        $l_strTitle = $this->get_obj_name_by_id_as_string($p_cat_level);

        $l_dao_connector = new isys_cmdb_dao_category_g_connector($g_comp_database);

        if ($p_connection_id == null) {
            $p_connection_id = $l_dao_connector->create(
                $p_cat_level,
                C__CONNECTOR__OUTPUT,
                null,
                null,
                $p_title,
                null,
                $p_connector_sibling,
                null,
                'C__CATG__POWER_SUPPLIER',
                null
            );
        }

        $l_strSql = 'INSERT INTO ' . 'isys_catg_power_supplier_list ' . 'SET ' . 'isys_catg_power_supplier_list__isys_catg_connector_list__id = ' .
            $this->convert_sql_id($p_connection_id) . ', ' . 'isys_catg_power_supplier_list__isys_obj__id	= ' . $this->convert_sql_id($p_cat_level) . ', ' .
            'isys_catg_power_supplier_list__status 		= ' . $this->convert_sql_int($p_status) . ', ' . 'isys_catg_power_supplier_list__description = ' .
            $this->convert_sql_text($p_description) . ', ' . 'isys_catg_power_supplier_list__volt = ' . $this->convert_sql_text($p_volt) . ', ' .
            'isys_catg_power_supplier_list__watt = ' . $this->convert_sql_text($p_watt) . ', ' . 'isys_catg_power_supplier_list__ampere = ' .
            $this->convert_sql_text($p_ampere) . ' ' . ';';

        $this->m_strLogbookSQL = $l_strSql;

        if ($this->update($l_strSql) && $this->apply_update()) {
            $p_new_id = $this->get_last_insert_id();
        }

        return $p_new_id;
    }

    /**
     * Get conenctor by category entry
     *
     * @param $p_list_id
     *
     * @return mixed
     * @throws isys_exception_database
     */
    public function get_connector($p_list_id)
    {
        $l_query = 'SELECT isys_catg_power_supplier_list__isys_catg_connector_list__id AS id 
                    FROM isys_catg_power_supplier_list 
                    WHERE isys_catg_power_supplier_list__id = ' . $this->convert_sql_id($p_list_id);

        return $this->retrieve($l_query)
            ->get_row_value('id');
    }

    /**
     * Get connector by cable connection
     *
     * @param $p_list_id
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_connector_mod($p_list_id)
    {
        $l_sql = 'SELECT * FROM isys_catg_power_supplier_list
            INNER JOIN isys_catg_connector_list ON isys_catg_connector_list__id = isys_catg_power_supplier_list__isys_catg_connector_list__id
            WHERE isys_catg_power_supplier_list__id = ' . $this->convert_sql_id($p_list_id) . ';';

        $l_res = $this->retrieve($l_sql);

        if ($l_res->num_rows()) {
            $l_row = $l_res->get_row();
            $l_tmp_connector = $l_row['isys_catg_connector_list__id'];
            $l_cable = $l_row['isys_catg_connector_list__isys_cable_connection__id'];
            $l_sql = 'SELECT * FROM isys_catg_connector_list WHERE isys_catg_connector_list__id != ' . $this->convert_sql_id($l_tmp_connector) .
                ' AND isys_catg_connector_list__isys_cable_connection__id = ' . $this->convert_sql_id($l_cable) . ';';

            return $this->retrieve($l_sql);
        }
    }
}
