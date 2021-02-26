<?php

use idoit\Component\Property\Type\IntProperty;

/**
 * i-doit
 *
 * DAO: global category for network listener
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_net_listener extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'net_listener';

    /**
     * Name of property which should be used as identifier
     *
     * @var string
     */
    protected $m_entry_identifier = 'gateway';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * @param isys_request $p_request
     *
     * @return array
     */
    public function callback_property_opened_by(isys_request $p_request)
    {
        $l_return = [];
        $l_object_id = $p_request->get_object_id();
        if ($l_object_id > 0) {
            $l_dao_software = new isys_cmdb_dao_category_g_application($this->m_db);
            $l_dao_result = $l_dao_software->get_data_by_object($l_object_id, null, C__RECORD_STATUS__NORMAL);
            while ($l_row = $l_dao_result->get_row()) {
                $l_return[$l_row['isys_obj__id']] = $l_row['isys_obj__title'];
            }
        }

        return $l_return;
    }

    /**
     * @param isys_request $p_request
     *
     * @return array
     */
    public function callback_property_ip_addresses(isys_request $p_request)
    {
        /**
         * @var isys_cmdb_dao_category_g_ip
         */
        $l_dao_ip = isys_cmdb_dao_category_g_ip::instance($this->m_db);
        $l_object_id = $p_request->get_object_id();

        $l_return = [];

        if ($l_object_id > 0) {
            if ($this->get_type_by_object_id($l_object_id) == defined_or_default('C__OBJTYPE__RELATION')) {
                $l_dao_relation = isys_cmdb_dao_category_g_relation::instance($this->m_db);
                $l_relation_data = $l_dao_relation->get_relation_members_by_obj_id($l_object_id);
                if (isset($l_relation_data[0])) {
                    $l_object_id = $l_relation_data[0];
                }
            }

            $l_data = $l_dao_ip->get_ips_by_obj_id($l_object_id);
            while ($l_row = $l_data->get_row()) {
                $l_return[$l_row['isys_cats_net_ip_addresses_list__id']] = $l_row['isys_cats_net_ip_addresses_list__title'];
            }
        }

        return $l_return;
    }

    /**
     * @param $p_object_id
     *
     * @return isys_component_dao_result
     */
    public function get_connections_by_listener_object($p_object_id)
    {
        return $this->get_connections(' AND isys_catg_net_listener_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id));
    }

    /**
     * @param $p_object_id
     *
     * @return isys_component_dao_result
     */
    public function get_connections_by_connector_object($p_object_id)
    {
        return $this->get_connections(' AND isys_catg_net_connector_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id));
    }

    /**
     * @param string $p_condition
     * @param int    $p_status
     *
     * @return isys_component_dao_result
     */
    public function get_connections($p_condition = '', $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = 'SELECT 
            isys_net_protocol__title AS protocol, 
            isys_net_protocol_layer_5__title AS protocol_layer_5, 
            source_obj.isys_obj__title AS source_object, 
            source_obj.isys_obj__id AS source_object_id, 
            source.isys_cats_net_ip_addresses_list__title AS source_ip,
            isys_catg_net_connector_list__port_from AS source_port_from, 
            isys_catg_net_connector_list__port_to AS source_port_to,
            bind_obj.isys_obj__title AS bind_object, 
            bind_obj.isys_obj__id AS bind_object_id, 
            bind_app_obj.isys_obj__title AS bind_application, 
            bind.isys_cats_net_ip_addresses_list__title AS bind_ip, 
            isys_catg_net_listener_list__port_from AS bind_port_from, 
            isys_catg_net_listener_list__port_to AS bind_port_to,
            rel_obj.isys_obj__title AS relation_object, 
            rel_obj.isys_obj__id AS relation_object_id, 
            isys_cats_net_list__address AS net_address, 
            network.isys_obj__title AS network, 
            source_gateway.isys_obj__title AS source_gateway, 
            bind_gateway.isys_obj__title AS bind_gateway
            FROM isys_catg_net_connector_list
            INNER JOIN isys_catg_net_listener_list ON isys_catg_net_listener_list__id = isys_catg_net_connector_list__isys_catg_net_listener_list__id
            INNER JOIN isys_obj source_obj ON isys_catg_net_connector_list__isys_obj__id = source_obj.isys_obj__id
            INNER JOIN isys_obj bind_obj ON isys_catg_net_listener_list__isys_obj__id = bind_obj.isys_obj__id
            LEFT JOIN isys_obj bind_app_obj ON isys_catg_net_listener_list__opened_by = bind_app_obj.isys_obj__id
            LEFT JOIN isys_catg_relation_list ON isys_catg_relation_list__id = isys_catg_net_connector_list__isys_catg_relation_list__id
            LEFT JOIN isys_obj rel_obj ON isys_catg_relation_list__isys_obj__id = rel_obj.isys_obj__id
            INNER JOIN isys_net_protocol ON isys_catg_net_listener_list__isys_net_protocol__id = isys_net_protocol__id 
            LEFT JOIN isys_cats_net_ip_addresses_list source ON isys_catg_net_connector_list__ip_addresses_list__id = source.isys_cats_net_ip_addresses_list__id 
            LEFT JOIN isys_cats_net_ip_addresses_list bind ON isys_catg_net_listener_list__isys_cats_net_ip_addresses_list__id = bind.isys_cats_net_ip_addresses_list__id 
            LEFT JOIN isys_cats_net_list ON bind.isys_cats_net_ip_addresses_list__isys_obj__id = isys_cats_net_list__isys_obj__id 
            LEFT JOIN isys_obj network ON bind.isys_cats_net_ip_addresses_list__isys_obj__id = network.isys_obj__id 
            LEFT JOIN isys_net_protocol_layer_5 ON isys_catg_net_listener_list__isys_net_protocol_layer_5__id = isys_net_protocol_layer_5__id 
            LEFT JOIN isys_obj source_gateway ON isys_catg_net_connector_list__gateway = source_gateway.isys_obj__id 
            LEFT JOIN isys_obj bind_gateway ON isys_catg_net_listener_list__gateway = bind_gateway.isys_obj__id
            WHERE bind_obj.isys_obj__status = ' . $this->convert_sql_id($p_status) . ' 
            AND source_obj.isys_obj__status = ' . $this->convert_sql_id($p_status) . '
            AND isys_catg_net_connector_list__status = ' . $this->convert_sql_id($p_status) . ' ';

        return $this->retrieve($l_sql . $p_condition);
    }

    /**
     * Return database field to be used as breadcrumb title
     *
     * @return string
     */
    public function get_breadcrumb_field($p_data = null)
    {
        return 'isys_cats_net_ip_addresses_list__title';
    }

    /**
     * Retrieves the number of saved category-entries to the given object.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {
        if ($p_obj_id !== null && $p_obj_id > 0) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        $l_table = $this->m_source_table . '_list';

        if ($l_table && $l_obj_id > 0) {
            $l_sql = "SELECT COUNT(" . $l_table . "__id) as count
				FROM " . $l_table . "
				WHERE (" . $l_table . "__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . " OR " . $l_table . "__status = " .
                $this->convert_sql_int(C__RECORD_STATUS__TEMPLATE) . ")
				AND " . $l_table . "__isys_obj__id = " . $this->convert_sql_id($l_obj_id) . ";";

            $l_amount = $this->retrieve($l_sql)
                ->get_row();

            return (int)$l_amount["count"];
        }

        return false;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'protocol'         => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__NET_LISTENER__PROTOCOL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Layer 3/4 Protocol'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_net_listener_list__isys_net_protocol__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_net_protocol',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_net_protocol',
                        'isys_net_protocol__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_net_protocol__title
                            FROM isys_catg_net_listener_list
                            INNER JOIN isys_net_protocol ON isys_net_protocol__id = isys_catg_net_listener_list__isys_net_protocol__id',
                        'isys_catg_net_listener_list',
                        'isys_catg_net_listener_list__id',
                        'isys_catg_net_listener_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_net_listener_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_net_listener_list',
                            'LEFT',
                            'isys_catg_net_listener_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_net_protocol',
                            'LEFT',
                            'isys_catg_net_listener_list__isys_net_protocol__id',
                            'isys_net_protocol__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID      => 'C__CMDB__CATG__NET_LISTENER__PROTOCOL',
                    C__PROPERTY__UI__DEFAULT => 1,
                    C__PROPERTY__UI__PARAMS  => [
                        'p_strTable' => 'isys_net_protocol'
                    ]
                ],
            ]),
            'protocol_layer_5' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__NET_LISTENER__LAYER_5_PROTOCOL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Layer 5-7 Protocol'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_net_listener_list__isys_net_protocol_layer_5__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_net_protocol_layer_5',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_net_protocol_layer_5',
                        'isys_net_protocol_layer_5__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_net_protocol_layer_5__title
                            FROM isys_catg_net_listener_list
                            INNER JOIN isys_net_protocol_layer_5 ON isys_net_protocol_layer_5__id = isys_catg_net_listener_list__isys_net_protocol_layer_5__id',
                        'isys_catg_net_listener_list',
                        'isys_catg_net_listener_list__id',
                        'isys_catg_net_listener_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_net_listener_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_net_listener_list',
                            'LEFT',
                            'isys_catg_net_listener_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_net_protocol_layer_5',
                            'LEFT',
                            'isys_catg_net_listener_list__isys_net_protocol_layer_5__id',
                            'isys_net_protocol_layer_5__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__NET_LISTENER__PROTOCOL_LAYER_5',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'   => 'isys_net_protocol_layer_5',
                        'p_bDbFieldNN' => 0
                    ]
                ],
            ]),
            'ip_address'       => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__NET_LISTENER__IP_ADDRESS',
                    C__PROPERTY__INFO__DESCRIPTION => '(Bind) ip address'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_net_listener_list__isys_cats_net_ip_addresses_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_cats_net_ip_addresses_list',
                        'isys_cats_net_ip_addresses_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_net_ip_addresses_list__title
                            FROM isys_catg_net_listener_list
                            INNER JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = isys_catg_net_listener_list__isys_cats_net_ip_addresses_list__id',
                        'isys_catg_net_listener_list',
                        'isys_catg_net_listener_list__id',
                        'isys_catg_net_listener_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_net_listener_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_net_listener_list',
                            'LEFT',
                            'isys_catg_net_listener_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_net_ip_addresses_list',
                            'LEFT',
                            'isys_catg_net_listener_list__isys_cats_net_ip_addresses_list__id',
                            'isys_cats_net_ip_addresses_list__id'
                        )
                    ],
                    C__PROPERTY__DATA__INDEX      => true
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__NET_LISTENER__IP_ADDRESS',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_net_listener',
                            'callback_property_ip_addresses'
                        ])
                    ]
                ],
            ]),
            'port_from' => (new IntProperty(
                'C__CMDB__CATG__NET_LISTENER__PORT_FROM',
                'LC_UNIVERSAL__FROM',
                'isys_catg_net_listener_list__port_from',
                'isys_catg_net_listener_list'
            ))->mergePropertyUiParams([
                'p_strClass'       => 'input-mini',
                'p_strPlaceholder' => '1',
                'default'          => ''
            ]),
            'port_to' => (new IntProperty(
                'C__CMDB__CATG__NET_LISTENER__PORT_TO',
                'LC__UNIVERSAL__TO',
                'isys_catg_net_listener_list__port_to',
                'isys_catg_net_listener_list'
            ))->mergePropertyUiParams([
                'p_strClass'       => 'input-mini',
                'p_bInfoIconSpacer' => '0',
                'p_strPlaceholder'  => '65535',
                'default'          => ''
            ]),
            'opened_by'        => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO   => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__NET_LISTENER__OPENED_BY_APPLICATION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Opened by application'
                ],
                C__PROPERTY__DATA   => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_net_listener_list__opened_by',
                    //C__PROPERTY__DATA__FIELD_ALIAS => 'opened_by',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'opened_by',
                    C__PROPERTY__DATA__REFERENCES  => [
                        'isys_obj',
                        'isys_obj__id'
                    ],
                    C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_net_listener_list
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_net_listener_list__opened_by',
                        'isys_catg_net_listener_list',
                        'isys_catg_net_listener_list__id',
                        'isys_catg_net_listener_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_net_listener_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN        => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_net_listener_list',
                            'LEFT',
                            'isys_catg_net_listener_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_net_listener_list__opened_by', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI     => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__NET_LISTENER__OPENED_BY_APPLICATION',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_net_listener',
                            'callback_property_opened_by'
                        ])
                    ]
                ],
                C__PROPERTY__FORMAT => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ],
            ]),
            'gateway'          => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__NET_CONNECTIONS__GATEWAY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Firewall gateway'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_net_listener_list__gateway',
                    //C__PROPERTY__DATA__FIELD_ALIAS => 'gateway',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'gateway',
                    C__PROPERTY__DATA__REFERENCES  => [
                        'isys_obj',
                        'isys_obj__id'
                    ],
                    C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_net_listener_list
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_net_listener_list__gateway',
                        'isys_catg_net_listener_list',
                        'isys_catg_net_listener_list__id',
                        'isys_catg_net_listener_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_net_listener_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN        => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_net_listener_list',
                            'LEFT',
                            'isys_catg_net_listener_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_net_listener_list__gateway', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__NET_LISTENER__GATEWAY',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPopupType' => 'browser_object_ng'
                    ]
                ],
            ]),
            'description'      => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_net_listener_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_net_listener_list__description FROM isys_catg_net_listener_list',
                        'isys_catg_net_listener_list',
                        'isys_catg_net_listener_list__id',
                        'isys_catg_net_listener_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_net_listener_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__NET_LISTENER', 'C__CATG__NET_LISTENER')
                ],
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
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            if (isset($p_category_data['properties']['opened_by']['sysid'])) {
                $l_openend_by = $p_category_data['properties']['opened_by']['id'];
            } else {
                $l_openend_by = $p_category_data['properties']['opened_by'][C__DATA__VALUE];
            }

            if (isset($p_category_data['properties']['gateway']['sysid'])) {
                $l_gateway = $p_category_data['properties']['gateway']['id'];
            } else {
                $l_gateway = $p_category_data['properties']['gateway'][C__DATA__VALUE];
            }

            if (!isset($p_category_data['properties']['ip_address'][C__DATA__VALUE])) {
                /**
                 * Get primary ip address
                 *
                 * @var $l_dao isys_cmdb_dao_category_g_ip
                 */
                $l_dao = isys_cmdb_dao_category_g_ip::instance($this->m_db);
                $p_category_data['properties']['ip_address'][C__DATA__VALUE] = $l_dao->get_primary_ip($p_object_id)
                    ->get_row_value('isys_cats_net_ip_addresses_list__id');
            }

            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0) {
                        $l_arr = [
                            'isys_obj__id'     => $p_object_id,
                            'status'           => C__RECORD_STATUS__NORMAL,
                            'protocol'         => $p_category_data['properties']['protocol'][C__DATA__VALUE],
                            'protocol_layer_5' => $p_category_data['properties']['protocol_layer_5'][C__DATA__VALUE],
                            'ip_address'       => $p_category_data['properties']['ip_address'][C__DATA__VALUE],
                            'port_from'        => $p_category_data['properties']['port_from'][C__DATA__VALUE],
                            'port_to'          => $p_category_data['properties']['port_to'][C__DATA__VALUE],
                            'opened_by'        => $l_openend_by,
                            'gateway'          => $l_gateway,
                            'description'      => $p_category_data['properties']['description'][C__DATA__VALUE],
                        ];

                        return $this->create_data($l_arr);
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $l_arr = [
                            'protocol'         => $p_category_data['properties']['protocol'][C__DATA__VALUE],
                            'protocol_layer_5' => $p_category_data['properties']['protocol_layer_5'][C__DATA__VALUE],
                            'ip_address'       => $p_category_data['properties']['ip_address'][C__DATA__VALUE],
                            'port_from'        => $p_category_data['properties']['port_from'][C__DATA__VALUE],
                            'port_to'          => $p_category_data['properties']['port_to'][C__DATA__VALUE],
                            'opened_by'        => $l_openend_by,
                            'gateway'          => $l_gateway,
                            'description'      => $p_category_data['properties']['description'][C__DATA__VALUE],
                        ];

                        $this->save_data($p_category_data['data_id'], $l_arr);

                        return $p_category_data['data_id'];
                    }
            }
        }

        return false;
    }

    /**
     * Compares category data which will be used in the import module
     *
     * @param      $p_category_data_values
     * @param      $p_object_category_dataset
     * @param      $p_used_properties
     * @param      $p_comparison
     * @param      $p_badness
     * @param      $p_mode
     * @param      $p_category_id
     * @param      $p_unit_key
     * @param      $p_category_data_ids
     * @param      $p_local_export
     * @param      $p_dataset_id_changed
     * @param      $p_dataset_id
     * @param      $p_logger
     * @param null $p_category_name
     * @param null $p_table
     * @param null $p_cat_multi
     * @param null $p_category_type_id
     * @param null $p_category_ids
     * @param null $p_object_ids
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
        $l_portfrom = $p_category_data_values['properties']['port_from'][C__DATA__VALUE];
        $l_protocol = $p_category_data_values['properties']['protocol'][C__DATA__VALUE];

        foreach ($p_object_category_dataset as $l_dataset_key => $l_dataset) {
            $p_dataset_id_changed = false;
            $p_dataset_id = $l_dataset[$p_table . '__id'];

            if (isset($p_already_used_data_ids[$p_dataset_id])) {
                // Skip it ID has already been used
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                $p_logger->debug('Dateset ID "' . $p_dataset_id . '" has already been handled. Skipping to next entry.');
                continue;
            }

            // Test the category data identifier:
            if ($p_mode === isys_import_handler_cmdb::C__USE_IDS && $p_category_data_values['data_id'] !== $p_dataset_id) {
                $p_logger->debug('Category data identifier is different.');
                $p_badness[$p_dataset_id]++;
                $p_dataset_id_changed = true;

                if ($p_mode === isys_import_handler_cmdb::C__USE_IDS) {
                    continue;
                }
            }

            if ($l_portfrom == $l_dataset['isys_catg_net_listener_list__port_from'] && $l_protocol == $l_dataset['isys_catg_net_listener_list__isys_net_protocol__id']) {
                // Entry found
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;

                return;
            } else {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
            }
        }
    }
}
