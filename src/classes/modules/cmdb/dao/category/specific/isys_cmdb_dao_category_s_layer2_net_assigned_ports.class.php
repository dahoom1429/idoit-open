<?php

use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: global category for layer2-net assigned ports
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_layer2_net_assigned_ports extends isys_cmdb_dao_category_specific implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'layer2_net_assigned_ports';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'isys_obj__id';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Flag which defines if the category is only a list with an object browser
     *
     * @var bool
     */
    protected $m_object_browser_category = true;

    /**
     * Property of the object browser
     *
     * @var string
     */
    protected $m_object_browser_property = 'isys_catg_port_list__id';

    /**
     * @param null $p_obj_id
     *
     * @return int
     */
    public function get_count($p_obj_id = null)
    {
        if ($p_obj_id !== null && $p_obj_id > 0) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        if ($this->m_table && $l_obj_id > 0) {
            $l_sql = "SELECT COUNT(" . $this->m_table . "__isys_obj__id) as count
				FROM " . $this->m_table . "
				WHERE (" . $this->m_table . "__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . " OR " . $this->m_table . "__status = " .
                $this->convert_sql_int(C__RECORD_STATUS__TEMPLATE) . ")
				AND " . $this->m_table . "__isys_obj__id = " . $this->convert_sql_id($l_obj_id) . ";";

            $l_amount = $this->retrieve($l_sql)
                ->get_row();

            return (int)$l_amount["count"];
        }

        return false;
    }

    /**
     * Get-data method. Note that this category has no PRIMARY __id field. The primary key consists of the two fields port_id and obj_id.
     *
     * @param   integer $p_cats_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM ' . $this->m_table . '
			LEFT JOIN isys_catg_port_list ON isys_catg_port_list.isys_catg_port_list__id = ' . $this->m_table . '.isys_catg_port_list__id
			LEFT JOIN isys_obj ON isys_obj__id = isys_cats_layer2_net_assigned_ports_list__isys_obj__id
			WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter) . ' ';

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_status !== null) {
            $l_sql .= 'AND isys_obj__status = ' . $this->convert_sql_int($p_status) . ' ';
        }

        return $this->retrieve($l_sql . ' ORDER BY isys_cats_layer2_net_assigned_ports_list__default DESC;');
    }

    /**
     * Creates the condition to the object table
     *
     * @param int|array $p_obj_id
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                $l_sql = ' AND (' . $this->m_table . '__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (' . $this->m_table . '__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'isys_obj__id'             => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__OBJECT_TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Object title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_layer2_net_assigned_ports_list__isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(obj.isys_obj__title, \' {\', obj.isys_obj__id, \'}\')
                                FROM isys_cats_layer2_net_assigned_ports_list AS main
                                INNER JOIN isys_catg_port_list AS p ON p.isys_catg_port_list__id = main.isys_catg_port_list__id
                                INNER JOIN isys_obj obj ON obj.isys_obj__id = p.isys_catg_port_list__isys_obj__id',
                        'isys_cats_layer2_net_assigned_ports_list',
                        'main.isys_cats_layer2_net_assigned_ports_list__id',
                        'main.isys_cats_layer2_net_assigned_ports_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['main.isys_cats_layer2_net_assigned_ports_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_layer2_net_assigned_ports_list',
                            'LEFT',
                            'isys_cats_layer2_net_assigned_ports_list__isys_obj__id',
                            'isys_obj__id',
                            'main',
                            '',
                            'main'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_port_list',
                            'LEFT',
                            'isys_catg_port_list__id',
                            'isys_catg_port_list__id',
                            'main',
                            'p',
                            'p'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__LAYER2_NET_ASSIGNED_PORTS__ISYS_OBJ__ID'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'isys_catg_port_list__id'  => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LAYER2_NET_ASSIGNED_PORTS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned objects'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_port_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT p.isys_catg_port_list__title
                                FROM isys_cats_layer2_net_assigned_ports_list AS main
                                INNER JOIN isys_catg_port_list AS p ON p.isys_catg_port_list__id = main.isys_catg_port_list__id',
                        'isys_cats_layer2_net_assigned_ports_list',
                        'main.isys_cats_layer2_net_assigned_ports_list__id',
                        'main.isys_cats_layer2_net_assigned_ports_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['main.isys_cats_layer2_net_assigned_ports_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_layer2_net_assigned_ports_list',
                            'LEFT',
                            'isys_cats_layer2_net_assigned_ports_list__isys_obj__id',
                            'isys_obj__id',
                            'main',
                            '',
                            'main'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_port_list',
                            'LEFT',
                            'isys_catg_port_list__id',
                            'isys_catg_port_list__id',
                            'main',
                            'p',
                            'p'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_obj',
                            'LEFT',
                            'isys_catg_port_list__isys_obj__id',
                            'isys_obj__id',
                            'p'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__LAYER2_NET_ASSIGNED_PORTS__ISYS_CATG_PORT_LIST__ID',
                    C__PROPERTY__UI__PARAMS => [
                        isys_popup_browser_object_ng::C__MULTISELECTION   => true,
                        isys_popup_browser_object_ng::C__FORM_SUBMIT      => true,
                        isys_popup_browser_object_ng::C__CAT_FILTER       => 'C__CATG__NETWORK;C__CATG__NETWORK_PORT',
                        isys_popup_browser_object_ng::C__RETURN_ELEMENT   => C__POST__POPUP_RECEIVER,
                        isys_popup_browser_object_ng::C__SECOND_SELECTION => true,
                        isys_popup_browser_object_ng::C__SECOND_LIST      => [
                            'isys_cmdb_dao_category_s_layer2_net_assigned_ports::object_browser',
                            [C__CMDB__GET__OBJECT => (isset($_GET[C__CMDB__GET__OBJECT]) ? $_GET[C__CMDB__GET__OBJECT] : 0)]
                        ],
                        isys_popup_browser_object_ng::C__SECOND_LIST_FORMAT => 'isys_cmdb_dao_category_s_layer2_net_assigned_ports::format_selection'
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'port'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'isys_catg_port_list__mac' => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__MAC',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned port mac'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_port_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT p.isys_catg_port_list__mac
                                FROM isys_cats_layer2_net_assigned_ports_list AS main
                                INNER JOIN isys_catg_port_list AS p ON p.isys_catg_port_list__id = main.isys_catg_port_list__id',
                        'isys_cats_layer2_net_assigned_ports_list',
                        'isys_cats_layer2_net_assigned_ports_list__id',
                        'isys_cats_layer2_net_assigned_ports_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_layer2_net_assigned_ports_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_layer2_net_assigned_ports_list',
                            'LEFT',
                            'isys_cats_layer2_net_assigned_ports_list__isys_obj__id',
                            'isys_obj__id',
                            'main',
                            '',
                            'main'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_port_list',
                            'LEFT',
                            'isys_catg_port_list__id',
                            'isys_catg_port_list__id',
                            'main',
                            'p',
                            'p'
                        )
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__REPORT     => true,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false
                ]
            ])
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                if (($l_id = $this->create($p_object_id, $p_category_data['properties']['isys_catg_port_list__id']['ref_id']))) {
                    $l_indicator = true;
                }
            }
        }

        return ($l_indicator === true) ? $l_id : false;
    }

    /**
     * @param     $p_obj_id
     * @param int $p_status
     *
     * @return isys_component_dao_result
     * @throws Exception
     * @throws isys_exception_database
     */
    public function find_assigned_ports($p_obj_id, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = 'SELECT * FROM ' . $this->m_table . ' AS l2 ' .

            // Here we connect to the table which holds all the ports.
            'INNER JOIN isys_catg_port_list AS port ' . 'ON port.isys_catg_port_list__id = l2.isys_catg_port_list__id ' .

            // Now we connect to the isys_obj table to get the data of the connected object.
            'INNER JOIN isys_obj AS obj ' . 'ON obj.isys_obj__id = l2.isys_cats_layer2_net_assigned_ports_list__isys_obj__id ' .

            // Connection to specific category
            'INNER JOIN isys_cats_layer2_net_list AS cats ' . 'ON obj.isys_obj__id = cats.isys_cats_layer2_net_list__isys_obj__id ' .

            // And finally we connect to the isys_obj_type table to get the object type.
            'INNER JOIN isys_obj_type AS type ' . 'ON obj.isys_obj__isys_obj_type__id = type.isys_obj_type__id ' .

            'WHERE isys_catg_port_list__isys_obj__id = ' . $this->convert_sql_int($p_obj_id) . ' ' . 'AND ' . $this->m_table . '__status = ' .
            $this->convert_sql_int($p_status) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Create method.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_port_id
     * @param   integer $p_default_vlan
     * @param   integer $p_status
     *
     * @return  mixed  Integer with last inserted ID on success, boolean false on failure.
     * @throws isys_exception_dao
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function create($p_obj_id, $p_port_id, $p_default_vlan = 0, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = 'INSERT INTO ' . $this->m_table .
                 ' SET ' . $this->m_table . '__status = ' . $this->convert_sql_int($p_status) . ', ' .
                  $this->m_table . '__isys_obj__id = ' . $this->convert_sql_int($p_obj_id) . ', ' .
                  $this->m_table . '__default = ' . $this->convert_sql_int($p_default_vlan) . ', ' .
                 'isys_catg_port_list__id = ' . $this->convert_sql_int($p_port_id) . ';';

        if ($this->update($l_sql) && $this->apply_update()) {
            $this->m_strLogbookSQL .= $l_sql;

            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    /**
     * Create element method, gets called from the object browser after confirming the selection.
     *
     * @param       $objectId
     * @param array $portIds
     *
     * @return void
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.org>#
     */
    public function attachObjects($objectId, array $portIds)
    {
        // Retrieve mapping of portIds to default vlan
        $ports2Vlan = $this->getPortsWithDefaultVlanIds($objectId, $portIds);

        // Delete all port assignments
        $this->delete_entries_by_obj_id($objectId);

        // Check for ports to be assigned
        if (count($portIds) > 0) {
            foreach ($portIds as $portId) {
                // Create port assignment
                $this->create(
                    $objectId,
                    $portId,
                    $ports2Vlan[$portId] ?: 0
                );
            }
        }
    }

    /**
     * Get array that maps ports to default vlan
     *
     * @param integer   $objectId
     * @param integer[] $portIds
     *
     * @return array
     * @throws isys_exception_database
     */
    public function getPortsWithDefaultVlanIds($objectId, $portIds)
    {
        // Sanitize port ids
        $portIds = array_map(function ($value) {
            return $this->convert_sql_id($value);
        }, $portIds);

        // SQL
        $sql = '
                SELECT isys_catg_port_list__id AS portId, 
                       isys_cats_layer2_net_assigned_ports_list__default AS defaultVlanId
                FROM isys_cats_layer2_net_assigned_ports_list
                WHERE isys_cats_layer2_net_assigned_ports_list__isys_obj__id = ' . $this->convert_sql_id($objectId) . '
                AND   isys_catg_port_list__id IN (' . implode(',', $portIds) . ');
               ';

        $res = $this->retrieve($sql);
        $data = [];

        // Check for existing results and create mapping
        if ($res->num_rows()) {
            while ($row = $res->get_row()) {
                $data[$row['portId']] = $row['defaultVlanId'];
            }
        }

        return $data;
    }

    /**
     * A method, which bundles the handle_ajax_request and handle_preselection.
     *
     * @param  integer $p_context
     * @param  array   $p_parameters
     *
     * @return string|array
     * @throws Exception
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function object_browser($p_context, array $p_parameters)
    {
        global $g_comp_database;

        $language = isys_application::instance()->container->get('language');

        switch ($p_context) {
            case isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST:
                $l_obj = (!empty($_GET[C__CMDB__GET__OBJECT])) ? $_GET[C__CMDB__GET__OBJECT] : $p_parameters[C__CMDB__GET__OBJECT];

                $l_return = [];
                $l_dao_port = new isys_cmdb_dao_category_g_network_port($g_comp_database);
                $l_res_port = $l_dao_port->get_data(null, $l_obj, '', null, C__RECORD_STATUS__NORMAL);

                if ($l_res_port->num_rows() > 0) {
                    while ($l_row_port = $l_res_port->get_row()) {
                        $l_return[] = [
                            '__checkbox__'                              => $l_row_port["isys_catg_port_list__id"],
                            $language->get('LC__CATD__PORT')            => $l_row_port["isys_catg_port_list__title"],
                            $language->get('LC__CMDB__CATG__PORT__MAC') => $l_row_port["isys_catg_port_list__mac"]
                        ];
                    }
                }

                return isys_format_json::encode($l_return);

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PREPARATION:
                $l_return = [
                    'category' => [],
                    'first'    => [],
                    'second'   => []
                ];

                if (isset($p_parameters['preselection'])) {
                    $preselection = isys_format_json::is_json_array($p_parameters['preselection'])? isys_format_json::decode($p_parameters['preselection']): $p_parameters['preselection'];

                    $dao = isys_cmdb_dao_category_g_network_port::instance(isys_application::instance()->container->get('database'));
                    $l_res = $dao->get_data(null, null, 'AND isys_catg_port_list__id IN (' . (is_array($preselection) ? implode(',', $preselection): $preselection) . ')');
                } else {
                    $l_obj = (!empty($_GET[C__CMDB__GET__OBJECT])) ? $_GET[C__CMDB__GET__OBJECT] : $p_parameters[C__CMDB__GET__OBJECT];

                    // Create this class, because we can't just use "this" or we'll get an exception "Database component not loaded!".
                    $l_dao = new isys_cmdb_dao_category_s_layer2_net_assigned_ports($g_comp_database);
                    $l_res = $l_dao->get_data(null, $l_obj, '', null, C__RECORD_STATUS__NORMAL);
                }

                while ($l_row = $l_res->get_row()) {
                    // @see  ID-6220  Also return a 'first' selection.
                    $l_return['first'][] = (int)$l_row['isys_obj__id'];

                    $l_return['second'][] = [
                        $l_row['isys_catg_port_list__id'],
                        $l_row['isys_catg_port_list__title'],
                        $l_row['isys_catg_port_list__mac'],
                    ];
                }

                return $l_return;

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PRESELECTION:
                // @see  ID-5688  New callback case.
                $preselection = [];

                if (is_array($p_parameters['dataIds']) && count($p_parameters['dataIds'])) {
                    foreach ($p_parameters['dataIds'] as $dataId) {
                        $categoryRow = isys_cmdb_dao_category_g_network_port::instance($this->m_db)->get_data($dataId)->get_row();

                        $preselection[] = [
                            $categoryRow['isys_catg_port_list__id'],
                            $categoryRow['isys_obj__title'],
                            $language->get($categoryRow['isys_obj_type__title']),
                            $categoryRow['isys_catg_port_list__title'],
                            $categoryRow['isys_catg_port_list__mac']
                        ];
                    }
                }

                return [
                    'header' => [
                        '__checkbox__',
                        $language->get('LC__UNIVERSAL__OBJECT_TITLE'),
                        $language->get('LC__UNIVERSAL__OBJECT_TYPE'),
                        $language->get('LC__CATD__PORT'),
                        $language->get('LC__CMDB__CATG__PORT__MAC')
                    ],
                    'data' => $preselection
                ];
        }
    }

    /**
     * Format selection for the object browser.
     *
     * @param   int $portId
     *
     * @return  string
     */
    public function format_selection($portId = null)
    {
        $dao = new isys_cmdb_dao_category_g_network_port($this->m_db);
        $objPlugin = new isys_smarty_plugin_f_text();

        if (!$portId) {
            return null;
        }

        $data = $dao->get_data($portId)->get_row();
        return (!empty($data)) ? $data['isys_obj__title'] . " >> " . $data['isys_catg_port_list__title']: null;
    }
}
