<?php

/**
 * i-doit
 *
 * DAO: specific category router.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_router extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'router';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'routing_protocol';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * @param  isys_request $p_request
     */
    public function callback_property_gateway_address(isys_request $p_request)
    {
        // @todo
    }

    /**
     * Executes the query to create the category entry.
     *
     * @param   integer $p_objID
     * @param   integer $p_newRecStatus
     * @param   integer $p_routing_protocol
     * @param   string  $p_description
     *
     * @return  integer  Integer with latest inserted ID on success, boolean false on failure.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus, $p_routing_protocol = null, $p_description = null)
    {
        $l_strSql = "INSERT INTO " . $this->m_table . " SET " . "isys_cats_router_list__description = " . $this->convert_sql_text($p_description) . ", " .
            "isys_cats_router_list__routing_protocol  = " . $this->convert_sql_id($p_routing_protocol) . ", " . "isys_cats_router_list__status = " .
            $this->convert_sql_id($p_newRecStatus) . ", " . "isys_cats_router_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ";";

        if ($this->update($l_strSql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_cats_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT * FROM " . $this->m_table . " " . "INNER JOIN isys_obj " . "ON isys_obj__id = isys_cats_router_list__isys_obj__id " . "WHERE TRUE " . $p_condition .
            $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_cats_list_id !== null) {
            $l_sql .= "AND isys_cats_router_list__id = " . $this->convert_sql_id($p_cats_list_id) . " ";
        }

        if ($p_status !== null) {
            $l_sql .= "AND isys_cats_router_list__status = " . $this->convert_sql_id($p_status) . " ";
        }

        return $this->retrieve($l_sql . ";");
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'routing_protocol' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__ROUTER__ROUTING_PROTOCOL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Routing protocol'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_cats_router_list__routing_protocol',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_routing_protocol',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_routing_protocol',
                        'isys_routing_protocol__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_routing_protocol__title
                            FROM isys_cats_router_list
                            INNER JOIN isys_routing_protocol ON isys_routing_protocol__id = isys_cats_router_list__routing_protocol', 'isys_cats_router_list',
                        'isys_cats_router_list__id', 'isys_cats_router_list__isys_obj__id', '', '', idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_router_list__isys_obj__id'])),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_router_list', 'LEFT', 'isys_cats_router_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_routing_protocol', 'LEFT', 'isys_cats_router_list__routing_protocol',
                            'isys_routing_protocol__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__ROUTER__ROUTING_PROTOCOL',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_routing_protocol'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'gateway_address'  => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_list(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__ROUTER__GATEWAY_ADDRESS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Gateway address'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_router_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT CONCAT(\'(\', isys_cats_net_list__address, \') - \', isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_cats_router_list AS main
                            INNER JOIN isys_catg_ip_list_2_isys_cats_router_list AS ip2r ON ip2r.isys_cats_router_list__id = main.isys_cats_router_list__id
                            INNER JOIN isys_catg_ip_list AS ip ON ip.isys_catg_ip_list__id = ip2r.isys_catg_ip_list__id
                            INNER JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id
                            INNER JOIN isys_obj AS obj ON obj.isys_obj__id = isys_cats_net_ip_addresses_list__isys_obj__id
                            INNER JOIN isys_cats_net_list ON isys_cats_net_list__isys_obj__id = isys_obj__id', 'isys_cats_router_list', 'isys_cats_router_list__id',
                        'isys_cats_router_list__isys_obj__id', '', '', idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_router_list__isys_obj__id'])),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_router_list', 'LEFT', 'isys_cats_router_list__isys_obj__id', 'isys_obj__id',
                            'main', '', 'main'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_ip_list_2_isys_cats_router_list', 'LEFT', 'isys_cats_router_list__id',
                            'isys_cats_router_list__id', 'main', 'ip2r', 'ip2r'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_ip_list', 'LEFT', 'isys_catg_ip_list__id', 'isys_catg_ip_list__id', 'ip2r', 'ip',
                            'ip'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_net_ip_addresses_list', 'LEFT',
                            'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id', 'isys_cats_net_ip_addresses_list__id', 'ip'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_cats_net_ip_addresses_list__isys_obj__id', 'isys_obj__id', 'ip',
                            'obj', 'obj'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_net_list', 'LEFT', 'isys_obj__id', 'isys_cats_net_list__isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__ROUTER__GATEWAY_ADDRESS',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            "isys_cmdb_dao_category_s_router",
                            "callback_property_gateway_address"
                        ]),
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'routing_gateway'
                    ]
                ]
            ]),
            'description'      => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_router_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_cats_router_list__description FROM isys_cats_router_list',
                        'isys_cats_router_list', 'isys_cats_router_list__id', 'isys_cats_router_list__isys_obj__id', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_router_list__isys_obj__id']))
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__ROUTER', 'C__CATS__ROUTER')
                ]
            ])
        ];
    }

    /**
     * Synchronize category content with $p_data.
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     * @author  Benjaming Heisig <bheisig@synetics.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            // Create category data identifier if needed.
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                $p_category_data['data_id'] = $this->create($p_object_id, C__RECORD_STATUS__NORMAL);
            }
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data.
                $l_indicator = $this->save($p_category_data['data_id'], C__RECORD_STATUS__NORMAL, $p_category_data['properties']['routing_protocol'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE]);
            }
            // First clear all IP's from the current list.
            foreach ($p_category_data['properties']['gateway_address'][C__DATA__VALUE] as $l_ip_id) {
                if ($l_ip_id > 0) {
                    // Then add the ones from our POST array.
                    $l_attach_ip = $this->attach_ip($p_category_data['data_id'], $l_ip_id);
                    if (!$l_attach_ip) {
                        return false;
                    }
                }
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Executes the query to save the category entry.
     *
     * @param   integer $p_id
     * @param   integer $p_status
     * @param   integer $p_routing_protocol
     * @param   string  $p_description
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save($p_id, $p_status, $p_routing_protocol, $p_description)
    {
        $l_strSql = "UPDATE " . $this->m_table . " SET " . "isys_cats_router_list__description = " . $this->convert_sql_text($p_description) . ", " .
            "isys_cats_router_list__routing_protocol  = " . $this->convert_sql_int($p_routing_protocol) . ", " . "isys_cats_router_list__status = " .
            $this->convert_sql_id($p_status) . " " . "WHERE isys_cats_router_list__id = " . $this->convert_sql_id($p_id) . ";";

        return ($this->update($l_strSql) && $this->apply_update());
    }

    /**
     * Save specific category router.
     *
     * @param   integer $p_cat_level
     * @param   integer & $p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @return  integer  Last inserted ID or error-code.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_bRet = false;

        // Don't save empty contents on overview page.
        if (isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW') &&
            (!$_POST['C__CATS__ROUTER__GATEWAY_ADDRESS__selected_values'] && $_POST['C__CATS__ROUTER__ROUTING_PROTOCOL'] == '-1')) {
            return false;
        }

        // Save the IP's from the dialog-list.
        $l_ip_connection = explode(",", $_POST['C__CATS__ROUTER__GATEWAY_ADDRESS__selected_values']);

        $l_catdata = $this->get_general_data();
        $l_list_id = $l_catdata["isys_cats_router_list__id"];

        if (empty($l_list_id)) {
            $l_list_id = $this->create_connector("isys_cats_router_list", $_GET[C__CMDB__GET__OBJECT]);
        }

        if (!empty($l_list_id)) {
            $l_bRet = $this->save($l_list_id, C__RECORD_STATUS__NORMAL, $_POST['C__CATS__ROUTER__ROUTING_PROTOCOL'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()]);

            // Store SQL Statement for logbook.
            $this->m_strLogbookSQL = $this->get_last_query();

            // First clear all IP's from the current list.
            if ($this->clear_ip_attachments($l_list_id)) {
                if (is_array($l_ip_connection)) {
                    foreach ($l_ip_connection as $l_ip_id) {
                        if ($l_ip_id > 0) {
                            // Then add the ones from our POST array.
                            $this->attach_ip($l_list_id, $l_ip_id);
                        }
                    }
                }
            }
        }

        return ($l_bRet == true) ? null : -1;
    }

    /**
     * Clears all ip attachments for $p_netp_port_id.
     *
     * @param   integer $p_netp_port_id
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function clear_ip_attachments($p_netp_port_id)
    {
        return ($this->update("DELETE FROM isys_catg_ip_list_2_isys_cats_router_list WHERE isys_cats_router_list__id = " . $this->convert_sql_id($p_netp_port_id) . ";") &&
            $this->apply_update());
    }

    /**
     * Attaches an IP address to a port.
     *
     * @param   integer $p_router_port_id
     * @param   integer $p_catg_ip_id
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function attach_ip($p_router_port_id, $p_catg_ip_id)
    {
        if ($p_router_port_id > 0 && $p_catg_ip_id > 0) {
            $l_sql = 'SELECT * FROM isys_catg_ip_list_2_isys_cats_router_list
				WHERE isys_catg_ip_list__id = ' . $this->convert_sql_id($p_catg_ip_id) . '
				AND isys_cats_router_list__id = ' . $this->convert_sql_id($p_router_port_id) . ';';

            $res = $this->retrieve($l_sql);
            if (!is_countable($res) || count($res) == 0) {
                $l_sql = 'INSERT INTO isys_catg_ip_list_2_isys_cats_router_list SET
					isys_catg_ip_list__id = ' . $this->convert_sql_id($p_catg_ip_id) . ',
					isys_cats_router_list__id = ' . $this->convert_sql_id($p_router_port_id) . ';';

                return ($this->update($l_sql) && $this->apply_update());
            }

            return true;
        }

        return false;
    }
}
