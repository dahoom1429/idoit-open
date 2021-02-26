<?php

/**
 * i-doit
 *
 * DAO: global category for virtual switches
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_virtual_switch extends isys_cmdb_dao_category_global
{

    /**
     * Category's name. Will be used for the identifier, constant, main table,
     * and many more.
     *
     * @var string
     */
    protected $m_category = 'virtual_switch';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var bool
     */
    protected $m_multivalued = true;

    /**
     * Callback method for the ports dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_ports(isys_request $p_request)
    {
        $l_return = [];
        $l_res = $this->get_ports($p_request->get_object_id());

        if (is_countable($l_res) && count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                $l_return[$l_row['isys_catg_port_list__id']] = $l_row['isys_catg_port_list__title'];
            }
        }

        return $l_return;
    }

    /**
     * Fetches category's data from database.
     *
     * @param int    $p_catg_list_id (optional) List's identifier
     * @param int    $p_obj_id       (optional) Object's identifier
     * @param string $p_condition    (optional) Condition
     * @param mixed  $p_filter       (optional) Filter string or array
     * @param int    $p_status       (optional) Status
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_virtual_switch_list' . ' INNER JOIN isys_obj ON isys_obj__id = isys_catg_virtual_switch_list__isys_obj__id' . ' WHERE TRUE ' .
            $p_condition . ' ' . $this->prepare_filter($p_filter);

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_catg_list_id)) {
            $l_sql .= ' AND isys_catg_virtual_switch_list__id = ' . $this->convert_sql_id($p_catg_list_id);
        }

        if (!empty($p_status)) {
            $l_sql .= ' AND isys_catg_virtual_switch_list__status = ' . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    protected function properties()
    {
        return [
            'title'               => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_virtual_switch_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_catg_virtual_switch_list__title FROM isys_catg_virtual_switch_list',
                        'isys_catg_virtual_switch_list', 'isys_catg_virtual_switch_list__id', 'isys_catg_virtual_switch_list__isys_obj__id', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_virtual_switch_list__isys_obj__id']))
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__VSWITCH_TITLE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'ports'               => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_list(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VIRTUAL_SWITCH__PORTS',
                    C__PROPERTY__INFO__DESCRIPTION => 'assigned ports'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_virtual_switch_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_virtual_switch_2_port',
                        'isys_virtual_switch_2_port__isys_catg_virtual_switch_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT CONCAT(isys_catg_virtual_switch_list__title, \' > \', isys_catg_port_list__title)
                            FROM isys_catg_virtual_switch_list
                              INNER JOIN isys_virtual_switch_2_port ON isys_virtual_switch_2_port__isys_catg_virtual_switch_list__id = isys_catg_virtual_switch_list__id
                              INNER JOIN isys_catg_port_list ON isys_catg_port_list__id = isys_virtual_switch_2_port__isys_catg_port_list__id',
                        'isys_catg_virtual_switch_list', 'isys_catg_virtual_switch_list__id', 'isys_catg_virtual_switch_list__isys_obj__id', '', '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_virtual_switch_list__isys_obj__id'])),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_virtual_switch_list', 'LEFT', 'isys_catg_virtual_switch_list__isys_obj__id',
                            'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_virtual_switch_2_port', 'LEFT', 'isys_catg_virtual_switch_list__id',
                            'isys_virtual_switch_2_port__isys_catg_virtual_switch_list__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_port_list', 'LEFT', 'isys_virtual_switch_2_port__isys_catg_port_list__id',
                            'isys_catg_port_list__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID           => 'C__CATG__VSWITCH_PORTS',
                    C__PROPERTY__UI__PLACEHOLDER  => 'LC__CMDB__CATG__VIRTUAL_SWITCH__PORTS_PLACEHOLDER',
                    C__PROPERTY__UI__EMPTYMESSAGE => 'LC__CMDB__CATG__VIRTUAL_SWITCH__PORTS_EMPTY',
                    C__PROPERTY__UI__PARAMS       => [
                        'p_arData'     => new isys_callback([
                            'isys_cmdb_dao_category_g_virtual_switch',
                            'callback_property_ports'
                        ]),
                        'p_bDbFieldNN' => 1,
                        'emptyMessage' => 'LC__CMDB__CATG__INTERFACE_L__EMPTY_MESSAGE_PORT'
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'ports'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'portgroup'           => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VSWITCH__PORT_GROUPS',
                    C__PROPERTY__INFO__DESCRIPTION => 'assigned portgroups'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_virtual_port_group__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_virtual_port_group',
                        'isys_virtual_port_group__isys_catg_virtual_switch_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT CONCAT(isys_catg_virtual_switch_list__title, \' > \', isys_virtual_port_group__title, \' (VLAN ID: \', isys_virtual_port_group__vlanid, \')\')
                              FROM isys_catg_virtual_switch_list
                              INNER JOIN isys_virtual_port_group ON isys_virtual_port_group__isys_catg_virtual_switch_list__id = isys_catg_virtual_switch_list__id',
                        'isys_catg_virtual_switch_list', 'isys_catg_virtual_switch_list__id', 'isys_catg_virtual_switch_list__isys_obj__id', '', '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_virtual_switch_list__isys_obj__id'])),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_virtual_switch_list', 'LEFT', 'isys_catg_virtual_switch_list__isys_obj__id',
                            'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_virtual_port_group', 'LEFT', 'isys_catg_virtual_switch_list__id',
                            'isys_virtual_port_group__isys_catg_virtual_switch_list__id')
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'portgroups'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false
                ]
            ]),
            'serviceconsoleports' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VSWITCH__SERVICE_CONSOLE_PORTS',
                    C__PROPERTY__INFO__DESCRIPTION => 'assigned service console ports'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_service_console_port__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_service_console_port',
                        'isys_service_console_port__isys_catg_virtual_switch_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT CONCAT(isys_service_console_port__title, \' (IP: \', isys_cats_net_ip_addresses_list__title, \')\')
                              FROM isys_catg_virtual_switch_list
                              INNER JOIN isys_service_console_port ON isys_service_console_port__isys_catg_virtual_switch_list__id = isys_catg_virtual_switch_list__id
                              LEFT JOIN isys_catg_ip_list ON isys_catg_ip_list__id = isys_service_console_port__isys_catg_ip_list__id
                              LEFT JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
                        'isys_catg_virtual_switch_list', 'isys_catg_virtual_switch_list__id', 'isys_catg_virtual_switch_list__isys_obj__id', '', '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_virtual_switch_list__isys_obj__id'])),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_virtual_switch_list', 'LEFT', 'isys_catg_virtual_switch_list__isys_obj__id',
                            'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_service_console_port', 'LEFT', 'isys_catg_virtual_switch_list__id',
                            'isys_service_console_port__isys_catg_virtual_switch_list__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_ip_list', 'LEFT', 'isys_service_console_port__isys_catg_ip_list__id',
                            'isys_catg_ip_list__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_net_ip_addresses_list', 'LEFT',
                            'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id', 'isys_cats_net_ip_addresses_list__id')
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'serviceconsoleports'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false
                ]
            ]),
            'vmkernelports'       => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VSWITCH__VMKERNEL_PORTS',
                    C__PROPERTY__INFO__DESCRIPTION => 'assigned vmkernel ports'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_vmkernel_port__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_vmkernel_port',
                        'isys_vmkernel_port__isys_catg_virtual_switch_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT CONCAT(isys_vmkernel_port__title, \' (IP: \', isys_cats_net_ip_addresses_list__title, \')\')
                            FROM isys_catg_virtual_switch_list
                              INNER JOIN isys_vmkernel_port ON isys_vmkernel_port__isys_catg_virtual_switch_list__id = isys_catg_virtual_switch_list__id
                              LEFT JOIN isys_catg_ip_list ON isys_catg_ip_list__id = isys_vmkernel_port__isys_catg_ip_list__id
                              LEFT JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
                        'isys_catg_virtual_switch_list', 'isys_catg_virtual_switch_list__id', 'isys_catg_virtual_switch_list__isys_obj__id', '', '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_virtual_switch_list__isys_obj__id'])),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_virtual_switch_list', 'LEFT', 'isys_catg_virtual_switch_list__isys_obj__id',
                            'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_vmkernel_port', 'LEFT', 'isys_catg_virtual_switch_list__id',
                            'isys_vmkernel_port__isys_catg_virtual_switch_list__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_ip_list', 'LEFT', 'isys_vmkernel_port__isys_catg_ip_list__id',
                            'isys_catg_ip_list__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_net_ip_addresses_list', 'LEFT',
                            'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id', 'isys_cats_net_ip_addresses_list__id')
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'vmkernelports'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false
                ]
            ]),
            'description'         => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'categories description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_virtual_switch_list__description',
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__VIRTUAL_SWITCH', 'C__CATG__VIRTUAL_SWITCH'),
                ],
            ])
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param array $p_category_data Values of category data to be saved.
     * @param int   $p_object_id     Current object identifier (from database)
     * @param int   $p_status        Decision whether category data should be created or
     *                               just updated.
     *
     * @return mixed Returns category data identifier (int) on success, true
     * (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $this->m_sync_catg_data = $p_category_data;
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if (($p_category_data['data_id'] = $this->create($p_object_id, C__RECORD_STATUS__NORMAL, $this->get_property('title'),
                        $this->get_property('description')))) {
                        $l_indicator = true;
                        $l_ports = $this->get_property('ports');
                        $this->attach_ports($p_category_data['data_id'], $l_ports);
                        $l_port_groups = $this->get_property('portgroup');
                        $this->attach_port_groups($p_category_data['data_id'], $l_port_groups);
                        $l_serviceconsoleports = $this->get_property('serviceconsoleports');
                        $this->attach_service_console_ports($p_category_data['data_id'], $l_serviceconsoleports);
                        $l_vmkernelports = $this->get_property('vmkernelports');
                        $this->attach_vmkernel_ports($p_category_data['data_id'], $l_vmkernelports);
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save($p_category_data['data_id'], C__RECORD_STATUS__NORMAL, $this->get_property('title'), $this->get_property('description'));
                    $l_ports = $this->get_property('ports');
                    $this->attach_ports($p_category_data['data_id'], $l_ports);
                    $l_port_groups = $this->get_property('portgroup');
                    $this->attach_port_groups($p_category_data['data_id'], $l_port_groups);
                    $l_serviceconsoleports = $this->get_property('serviceconsoleports');
                    $this->attach_service_console_ports($p_category_data['data_id'], $l_serviceconsoleports);
                    $l_vmkernelports = $this->get_property('vmkernelports');
                    $this->attach_vmkernel_ports($p_category_data['data_id'], $l_vmkernelports);
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Fetches information about the service console ports from database.
     *
     * @param int $p_vswitchID Virtual switch's identifier
     *
     * @return isys_component_dao_result
     */
    public function get_service_console_ports($p_vswitchID)
    {
        $l_query = "SELECT * FROM isys_service_console_port " . "LEFT JOIN isys_catg_ip_list ON isys_catg_ip_list__id = isys_service_console_port__isys_catg_ip_list__id " .
            "LEFT JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id " .
            "WHERE isys_service_console_port__isys_catg_virtual_switch_list__id = " . $this->convert_sql_id($p_vswitchID);

        return $this->retrieve($l_query);
    }

    /**
     * @param $p_vswitchID
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_vmkernel_ports($p_vswitchID)
    {
        $l_query = "SELECT * FROM isys_vmkernel_port " . "LEFT JOIN isys_catg_ip_list ON isys_catg_ip_list__id = isys_vmkernel_port__isys_catg_ip_list__id " .
            "LEFT JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id " .
            "WHERE isys_vmkernel_port__isys_catg_virtual_switch_list__id = " . $this->convert_sql_id($p_vswitchID);

        return $this->retrieve($l_query);
    }

    /**
     * @param $p_vswitchID
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_port_groups($p_vswitchID)
    {
        $l_query = "SELECT * FROM isys_virtual_port_group " . "WHERE isys_virtual_port_group__isys_catg_virtual_switch_list__id = " . $this->convert_sql_id($p_vswitchID);

        return $this->retrieve($l_query);
    }

    /**
     * @param $p_catlevel
     * @param $p_arPorts
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function attach_ports($p_catlevel, $p_arPorts)
    {
        $l_update = "DELETE FROM isys_virtual_switch_2_port WHERE " . "isys_virtual_switch_2_port__isys_catg_virtual_switch_list__id = " . $this->convert_sql_id($p_catlevel);

        if (!$this->update($l_update)) {
            return false;
        }

        if (!is_countable($p_arPorts) || count($p_arPorts) == 0) {
            return true;
        }

        $l_update = "INSERT INTO isys_virtual_switch_2_port " . "(isys_virtual_switch_2_port__isys_catg_virtual_switch_list__id, " .
            "isys_virtual_switch_2_port__isys_catg_port_list__id) " . "VALUES ";
        $l_is_correct = false;
        foreach ($p_arPorts as $l_port) {
            if ($l_port) {
                $l_update .= '(' . $this->convert_sql_id($p_catlevel) . ", " . $this->convert_sql_id($l_port) . '), ';
                $l_is_correct = true;
            }
        }

        $l_update = rtrim($l_update, ", ");

        if (!$l_is_correct) {
            return false;
        }

        if (!$this->update($l_update)) {
            return false;
        }

        return true;
    }

    /**
     * @param $p_catlevel
     * @param $p_scps
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function attach_service_console_ports($p_catlevel, $p_scps)
    {
        $l_update = "DELETE FROM isys_service_console_port WHERE " . "isys_service_console_port__isys_catg_virtual_switch_list__id = " . $this->convert_sql_id($p_catlevel);

        if (!$this->update($l_update)) {
            return false;
        }

        if (!is_countable($p_scps) && count($p_scps) == 0) {
            return true;
        }

        $l_update = "INSERT INTO isys_service_console_port " . "(isys_service_console_port__isys_catg_virtual_switch_list__id, " .
            "isys_service_console_port__isys_catg_ip_list__id, " . "isys_service_console_port__title) " . "VALUES ";
        $l_exe = false;
        foreach ($p_scps as $l_scp) {
            if (!empty($l_scp[1]) && $l_scp[1] > 0) {
                $l_update .= '(' . $this->convert_sql_id($p_catlevel) . ', ' . $this->convert_sql_id($l_scp[1]) . ', ' . $this->convert_sql_text($l_scp[0]) . '), ';
                $l_exe = true;
            }
        }

        if (!$l_exe) {
            return false;
        }

        $l_update = rtrim($l_update, ", ");

        if (!$this->update($l_update)) {
            return false;
        }

        return true;
    }

    /**
     * @param $p_catlevel
     * @param $p_vmks
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function attach_vmkernel_ports($p_catlevel, $p_vmks)
    {
        $l_update = "DELETE FROM isys_vmkernel_port WHERE " . "isys_vmkernel_port__isys_catg_virtual_switch_list__id = " . $this->convert_sql_id($p_catlevel);

        if (!$this->update($l_update)) {
            return false;
        }

        if (!is_countable($p_vmks) && count($p_vmks) == 0) {
            return true;
        }

        $l_update = "INSERT INTO isys_vmkernel_port " . "(isys_vmkernel_port__isys_catg_virtual_switch_list__id, " . "isys_vmkernel_port__isys_catg_ip_list__id, " .
            "isys_vmkernel_port__title) " . "VALUES ";
        $l_exe = false;
        foreach ($p_vmks as $l_vmk) {
            if (!empty($l_vmk[1]) && $l_vmk[1] > 0) {
                $l_update .= "(" . $this->convert_sql_id($p_catlevel) . ", " . $this->convert_sql_id($l_vmk[1]) . ", " . $this->convert_sql_text($l_vmk[0]) . "), ";
                $l_exe = true;
            }
        }

        if (!$l_exe) {
            return false;
        }

        $l_update = rtrim($l_update, ", ");

        if (!$this->update($l_update)) {
            return false;
        }

        return true;
    }

    /**
     * @param $p_catlevel
     * @param $p_pgs
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function attach_port_groups($p_catlevel, $p_pgs)
    {
        $l_update = "DELETE FROM isys_virtual_port_group WHERE " . "isys_virtual_port_group__isys_catg_virtual_switch_list__id = " . $this->convert_sql_id($p_catlevel);

        if (!$this->update($l_update)) {
            return false;
        }

        if (!is_countable($p_pgs) && count($p_pgs) == 0) {
            return true;
        }

        $l_update = "INSERT INTO isys_virtual_port_group " . "(isys_virtual_port_group__isys_catg_virtual_switch_list__id, " . "isys_virtual_port_group__title, " .
            "isys_virtual_port_group__vlanid) " . "VALUES ";
        $l_exe = false;
        foreach ($p_pgs as $l_vmk) {
            if (!empty($l_vmk[0])) {
                $l_update .= "(" . $this->convert_sql_id($p_catlevel) . ", " . $this->convert_sql_text($l_vmk[0]) . ", " . $this->convert_sql_text($l_vmk[1]) . "), ";
                $l_exe = true;
            }
        }

        if (!$l_exe) {
            return false;
        }

        $l_update = rtrim($l_update, ", ");

        if (!$this->update($l_update)) {
            return false;
        }

        return true;
    }

    /**
     * Save element method.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_intOldRecStatus
     *
     * @return  mixed
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus)
    {
        if (!empty($_POST['C__CATG__VSWITCH_PORTS__selected_values'])) {
            $l_ports = explode(',', $_POST['C__CATG__VSWITCH_PORTS__selected_values']);
        } else {
            $l_ports = [];
        }

        $l_arPG = $l_arSCP = $l_arVMK = [];

        foreach ($_POST as $l_key => $l_value) {
            if (strpos($l_key, 'C__CATG__VSWITCH_PG_NAME_') === 0) {
                $l_arPG[] = [
                    $l_value,
                    $_POST['C__CATG__VSWITCH_PG_VLANID_' . substr($l_key, 25)]
                ];
            }

            if (strpos($l_key, 'C__CATG__VSWITCH_SCP_NAME_') === 0) {
                $l_arSCP[] = [
                    $l_value,
                    $_POST['C__CATG__VSWITCH_SCP_ADDRESS_' . substr($l_key, 26)]
                ];
            }

            if (strpos($l_key, 'C__CATG__VSWITCH_VMK_NAME_') === 0) {
                $l_arVMK[] = [
                    $l_value,
                    $_POST['C__CATG__VSWITCH_VMK_ADDRESS_' . substr($l_key, 26)]
                ];
            }
        }

        if ($_GET[C__CMDB__GET__CATLEVEL] != -1 && $_GET[C__CMDB__GET__CATLEVEL] > 0) {
            $l_ret = $this->save($_GET[C__CMDB__GET__CATLEVEL], C__RECORD_STATUS__NORMAL, $_POST['C__CATG__VSWITCH_TITLE'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()]);

            $this->attach_ports($_GET[C__CMDB__GET__CATLEVEL], $l_ports);
            $this->attach_port_groups($_GET[C__CMDB__GET__CATLEVEL], $l_arPG);
            $this->attach_service_console_ports($_GET[C__CMDB__GET__CATLEVEL], $l_arSCP);
            $this->attach_vmkernel_ports($_GET[C__CMDB__GET__CATLEVEL], $l_arVMK);
        } else {
            $l_ret = $this->create($_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__NORMAL, $_POST['C__CATG__VSWITCH_TITLE'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()]);

            $this->attach_ports($l_ret, $l_ports);
            $this->attach_port_groups($l_ret, $l_arPG);
            $this->attach_service_console_ports($l_ret, $l_arSCP);
            $this->attach_vmkernel_ports($l_ret, $l_arVMK);
            $p_cat_level = null;
        }

        return $l_ret;
    }

    /**
     * Executes the operations to create the category entry for the object referenced by isys_obj__id $p_objID
     *
     * @param int    $p_objID
     * @param int    $p_recStatus
     * @param String $p_title
     * @param String $p_description
     *
     * @return int the newly created ID or false
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_recStatus, $p_title, $p_description)
    {
        $l_update = "INSERT INTO isys_catg_virtual_switch_list SET " . "isys_catg_virtual_switch_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ", " .
            "isys_catg_virtual_switch_list__status = " . $this->convert_sql_id($p_recStatus) . ", " . "isys_catg_virtual_switch_list__title = " .
            $this->convert_sql_text($p_title) . ", " . "isys_catg_virtual_switch_list__description = " . $this->convert_sql_text($p_description);

        if ($this->update($l_update) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    /**
     * Executes the operations to save the category entry referenced bv its ID $p_cat_level
     *
     * @param int    $p_cat_level
     * @param int    $p_recStatus
     * @param String $p_title
     * @param String $p_description
     *
     * @return boolean true, if operations executed successfully, else false
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_recStatus, $p_title, $p_description)
    {
        $l_update = "UPDATE isys_catg_virtual_switch_list SET " . "isys_catg_virtual_switch_list__status = " . $this->convert_sql_id($p_recStatus) . ", " .
            "isys_catg_virtual_switch_list__title = " . $this->convert_sql_text($p_title) . ", " . "isys_catg_virtual_switch_list__description = " .
            $this->convert_sql_text($p_description) . "WHERE isys_catg_virtual_switch_list__id = " . $this->convert_sql_id($p_cat_level);

        if ($this->update($l_update)) {
            return $this->apply_update();
        } else {
            return false;
        }
    }

    /**
     * @param      $p_objID
     * @param null $p_status
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_ports($p_objID, $p_status = null)
    {
        $l_query = "SELECT * FROM isys_catg_port_list WHERE isys_catg_port_list__isys_obj__id = " . $this->convert_sql_id($p_objID);

        if (!is_null($p_status)) {
            $l_query .= " AND isys_catg_port_list__status = " . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_query);
    }

    /**
     * @param $p_vswitchID
     *
     * @return array
     * @throws isys_exception_database
     */
    public function get_connected_ports($p_vswitchID)
    {
        $l_query = "SELECT * FROM isys_virtual_switch_2_port WHERE isys_virtual_switch_2_port__isys_catg_virtual_switch_list__id = " . $this->convert_sql_id($p_vswitchID);

        $l_res = $this->retrieve($l_query);
        $l_ports = [];
        while ($l_row = $l_res->get_row()) {
            $l_ports[] = $l_row['isys_virtual_switch_2_port__isys_catg_port_list__id'];
        }

        return $l_ports;
    }

    /**
     * @param $p_vswitchID
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_assigned_ports($p_vswitchID)
    {
        $l_query = "SELECT * FROM isys_virtual_switch_2_port " .
            "INNER JOIN isys_catg_port_list ON isys_catg_port_list__id = isys_virtual_switch_2_port__isys_catg_port_list__id " .
            "WHERE isys_virtual_switch_2_port__isys_catg_virtual_switch_list__id = " . $this->convert_sql_id($p_vswitchID);

        return $this->retrieve($l_query);
    }

    /**
     * @param $p_objID
     * @param $p_pgName
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_connected_clients($p_objID, $p_pgName)
    {
        $p_objID = (!is_array($p_objID) ? [$p_objID] : $p_objID);

        $l_query = "SELECT isys_obj__id, isys_obj__isys_obj_type__id, isys_obj__title FROM isys_virtual_device_host " .
            "INNER JOIN isys_catg_virtual_device_list ON isys_catg_virtual_device_list__id = isys_virtual_device_host__isys_catg_virtual_device_list__id " .
            "INNER JOIN isys_obj ON isys_obj__id = isys_catg_virtual_device_list__isys_obj__id " .
            "INNER JOIN isys_catg_virtual_machine_list ON isys_catg_virtual_machine_list__isys_obj__id = isys_obj__id " .
            "INNER JOIN isys_connection ON isys_connection__id = isys_catg_virtual_machine_list__isys_connection__id " .
            "WHERE isys_virtual_device_host__switch_port_group = " . $this->convert_sql_text($p_pgName) . " " . "AND isys_connection__isys_obj__id " .
            $this->prepare_in_condition($p_objID) . " " . "AND isys_catg_virtual_machine_list__vm = " . $this->convert_sql_id(C__VM__GUEST) . ' GROUP BY isys_obj__id;';

        return $this->retrieve($l_query);
    }

    /**
     * Adds new port group.
     *
     * @param int    $p_catlevel
     * @param string $p_title
     * @param string $p_vlanid
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function add_port_group($p_catlevel, $p_title, $p_vlanid)
    {
        $l_update = "INSERT INTO isys_virtual_port_group " . "(isys_virtual_port_group__isys_catg_virtual_switch_list__id, " . "isys_virtual_port_group__title, " .
            "isys_virtual_port_group__vlanid) " . "VALUES ";

        $l_update .= "(" . $this->convert_sql_id($p_catlevel) . ", " . $this->convert_sql_text($p_title) . ", " . $this->convert_sql_text($p_vlanid) . ") ";

        if (!$this->update($l_update)) {
            return false;
        }

        return $this->apply_update();
    }

}

?>