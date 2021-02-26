<?php

use idoit\Component\Helper\Ip;
use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: global category for physical network ports
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_network_port extends isys_cmdb_dao_category_global
{

    /**
     * Category's name. Will be used for the identifier, constant, main table,
     * and many more.
     *
     * @var string
     */
    protected $m_category = 'network_port';

    /**
     * Category's constant
     *
     * @var string
     *
     * @fixme No standard behavior!
     */
    protected $m_category_const = 'C__CATG__NETWORK_PORT';

    /**
     * Category's identifier
     *
     * @var int
     *
     * @fixme No standard behavior!
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATG__NETWORK_PORT;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var bool
     */
    protected $m_multivalued = true;

    /**
     * Main table where properties are stored persistently
     *
     * @var string
     *
     * @fixme No standard behavior!
     */
    protected $m_table = 'isys_catg_port_list';

    /**
     * Category's template
     *
     * @var string
     *
     * @fixme No standard behavior!
     */
    protected $m_tpl = 'catg__port.tpl';

    /**
     * @var int
     */
    private $m_default_vlan_id = 0;

    /**
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__NETWORK_TREE_CONFIG_PORT';

    /**
     * Adds needed functions for the order by.
     *
     * @param   isys_component_database $p_db
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public static function add_sql_functions_for_order(isys_component_database &$p_db)
    {
        //$p_db->query("DROP FUNCTION IF EXISTS alphas;");
        if (!$p_db->num_rows($p_db->query('SHOW FUNCTION STATUS WHERE NAME = \'alphas\' AND Db = \'' . $p_db->get_db_name() . '\';'))) {
            // This function strips all numeric characters
            $p_db->query("CREATE FUNCTION alphas(str CHAR(100)) RETURNS CHAR(100) DETERMINISTIC READS SQL DATA
					BEGIN
					  DECLARE i, len SMALLINT DEFAULT 1;
					  DECLARE ret CHAR(100) DEFAULT '';
					  DECLARE c CHAR(1);
					  SET len = CHAR_LENGTH( str );
					  REPEAT
						BEGIN
						  SET c = MID( str, i, 1 );
						  IF c REGEXP '[[:alpha:]]' THEN
							SET ret=CONCAT(ret,c);
						  END IF;
						  SET i = i + 1;
						END;
					  UNTIL i > len END REPEAT;
					  RETURN ret;
					 END");
        }

        if (!$p_db->num_rows($p_db->query('SHOW FUNCTION STATUS WHERE NAME = \'digits\' AND Db = \'' . $p_db->get_db_name() . '\';'))) {
            // This function strips all non numeric characters
            $p_db->query("CREATE FUNCTION digits( str CHAR(100) ) RETURNS CHAR(100) DETERMINISTIC READS SQL DATA
					BEGIN
					  DECLARE i, len SMALLINT DEFAULT 1;
					  DECLARE ret CHAR(100) DEFAULT '';
					  DECLARE c CHAR(1);
					  SET len = CHAR_LENGTH( str );
					  REPEAT
						BEGIN
						  SET c = MID( str, i, 1 );
						  IF c BETWEEN '0' AND '9' THEN
							SET ret=CONCAT(ret,c);
						  END IF;
						  SET i = i + 1;
						END;
					  UNTIL i > len END REPEAT;
					  RETURN ret;
					END");
        }

        if (!$p_db->num_rows($p_db->query('SHOW FUNCTION STATUS WHERE NAME = \'substr_order\' AND Db = \'' . $p_db->get_db_name() . '\';'))) {
            // This function cuts the string from 0 to last index of the specified delimiter
            $p_db->query("CREATE FUNCTION substr_order( str CHAR(100), delim CHAR(5) ) RETURNS CHAR(100) DETERMINISTIC READS SQL DATA
					BEGIN
					  DECLARE i, len, posi SMALLINT DEFAULT 1;
					  DECLARE c CHAR(1);
					  DECLARE ret CHAR(100);
					  SET len = CHAR_LENGTH(str);
					  REPEAT
						BEGIN
						SET c = MID( str, i, 1 );
						IF c = delim THEN
							SET posi = i;
						END IF;
						SET i = i + 1;
						END;
					  UNTIL i > len END REPEAT;

					  IF posi BETWEEN '2' AND len THEN
						SET ret = SUBSTR(str, 1, posi);
					  ELSE
						SET ret = alphas(str);
					  END IF;

					  RETURN ret;
					END");
        }

        return true;
    }

    /**
     * Callback method for the interface dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_interface(isys_request $p_request)
    {
        $l_iface_res = isys_cmdb_dao_category_g_network_interface::instance($this->get_database_component())
            ->get_data(null, $p_request->get_object_id());
        $l_hba_res = isys_cmdb_dao_category_g_hba::instance($this->get_database_component())
            ->get_data(null, $p_request->get_object_id());
        $l_return = [];

        while ($l_row = $l_iface_res->get_row()) {
            $l_return[$l_row['isys_catg_netp_list__id'] . '_C__CATG__NETWORK_INTERFACE'] = $l_row['isys_catg_netp_list__title'];
        }

        while ($l_row = $l_hba_res->get_row()) {
            $l_return[$l_row['isys_catg_hba_list__id'] . '_C__CATG__HBA'] = $l_row['isys_catg_hba_list__title'];
        }

        return $l_return;
    }

    /**
     * Callback method for the hostaddress dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_addresses(isys_request $p_request)
    {
        $l_obj_id = $p_request->get_object_id();
        $l_cat_id = $p_request->get_category_data_id();

        $l_res = isys_cmdb_dao_category_g_ip::instance($this->get_database_component())
            ->get_data(null, $l_obj_id);
        $l_return = [];

        if (is_countable($l_res) && count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                $l_address = $l_row["isys_cats_net_ip_addresses_list__title"] ?: $l_row["isys_catg_ip_list__hostname"];

                if ($l_row['isys_catg_ip_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
                    $l_return[] = [
                        "id"   => $l_row["isys_catg_ip_list__id"],
                        "val"  => $l_address ? $l_address : isys_application::instance()->container->get('language')
                            ->get("LC__IP__EMPTY_ADDRESS"),
                        "sel"  => (($l_cat_id == $l_row['isys_catg_ip_list__isys_catg_port_list__id'] && !is_null($l_cat_id)) ? true : false),
                        "link" => isys_helper_link::create_catg_item_url([
                            C__CMDB__GET__OBJECT   => $l_obj_id,
                            C__CMDB__GET__CATG     => defined_or_default('C__CATG__IP'),
                            C__CMDB__GET__CATLEVEL => $l_row["isys_catg_ip_list__id"]
                        ])
                    ];
                } else {
                    $l_return[] = [
                        "id"   => $l_row["isys_catg_ip_list__id"],
                        "val"  => $l_address ? Ip::validate_ipv6($l_address, true) : isys_application::instance()->container->get('language')
                            ->get("LC__IP__EMPTY_ADDRESS"),
                        "sel"  => (($l_cat_id == $l_row['isys_catg_ip_list__isys_catg_port_list__id'] && !is_null($l_cat_id)) ? true : false),
                        "link" => isys_helper_link::create_catg_item_url([
                            C__CMDB__GET__OBJECT   => $l_obj_id,
                            C__CMDB__GET__CATG     => defined_or_default('C__CATG__IP'),
                            C__CMDB__GET__CATLEVEL => $l_row["isys_catg_ip_list__id"]
                        ])
                    ];
                }
            }
        }

        return $l_return;
    }

    /**
     * Callback method for the "assigned connector" object-browser.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_assigned_connector(isys_request $p_request)
    {
        return isys_cmdb_dao_cable_connection::instance($this->get_database_component())
            ->get_assigned_connector_id($p_request->get_row("isys_catg_port_list__isys_catg_connector_list__id"));
    }

    /**
     * Callback method for the "assigned connector" object-browser.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_cable(isys_request $p_request)
    {
        return isys_cmdb_dao_cable_connection::instance($this->get_database_component())
            ->get_assigned_cable($p_request->get_row("isys_catg_port_list__isys_catg_connector_list__id"));
    }

    /**
     * Callback method for the "assigned connector" object-browser.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_layer2_assignment(isys_request $p_request)
    {
        return $this->get_attached_layer2_net_as_array($p_request->get_row("isys_catg_port_list__id"));
    }

    /**
     * Method for retrieving data from this DAO.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        return $this->get_ports($p_obj_id, null, $p_status, $p_catg_list_id, $p_filter, $p_condition);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'title'              => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_port_list__title',
                    C__PROPERTY__DATA__INDEX  => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_port_list__title FROM isys_catg_port_list',
                        'isys_catg_port_list',
                        'isys_catg_port_list__id',
                        'isys_catg_port_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_port_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__PORT__TITLE'
                ]
            ]),
            'interface'          => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__CON_INTERFACE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Connected interface'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_port_list__isys_catg_netp_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_netp_list',
                        'isys_catg_netp_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_catg_port_list__isys_catg_netp_list__id > 0 THEN isys_catg_netp_list__title
                                WHEN isys_catg_port_list__isys_catg_hba_list__id > 0 THEN isys_catg_hba_list__title ELSE NULL END)
                                FROM isys_catg_port_list
                              LEFT JOIN isys_catg_netp_list ON isys_catg_netp_list__id = isys_catg_port_list__isys_catg_netp_list__id
                              LEFT JOIN isys_catg_hba_list ON isys_catg_hba_list__id = isys_catg_port_list__isys_catg_hba_list__id',
                        'isys_catg_port_list',
                        'isys_catg_port_list__id',
                        'isys_catg_port_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_port_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_port_list', 'LEFT', 'isys_catg_port_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_netp_list',
                            'LEFT',
                            'isys_catg_port_list__isys_catg_netp_list__id',
                            'isys_catg_netp_list__id',
                            '',
                            '',
                            '',
                            'isys_catg_port_list'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_hba_list',
                            'LEFT',
                            'isys_catg_port_list__isys_catg_hba_list__id',
                            'isys_catg_hba_list__id',
                            '',
                            '',
                            '',
                            'isys_catg_port_list'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__PORT__INTERFACE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_network_port',
                            'callback_property_interface'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
//                      C__PROPERTY__PROVIDES__VIRTUAL => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'interface_p'
                    ]
                ]
            ]),
            'port_type'          => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Typ'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_port_list__isys_port_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_port_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_port_type',
                        'isys_port_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_port_type__title
                            FROM isys_catg_port_list
                            INNER JOIN isys_port_type ON isys_port_type__id = isys_catg_port_list__isys_port_type__id',
                        'isys_catg_port_list',
                        'isys_catg_port_list__id',
                        'isys_catg_port_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_port_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_port_list', 'LEFT', 'isys_catg_port_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_port_type', 'LEFT', 'isys_catg_port_list__isys_port_type__id', 'isys_port_type__id')
                    ],
                    C__PROPERTY__DATA__INDEX        => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__PORT__TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'   => 'isys_port_type',
                        'p_bDbFieldNN' => '1'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_plus'
                    ]
                ]
            ]),
            'port_mode'          => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__MODE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Mode'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_port_list__isys_port_mode__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_port_mode',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_port_mode',
                        'isys_port_mode__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_port_mode__title
                            FROM isys_catg_port_list
                            INNER JOIN isys_port_mode ON isys_port_mode__id = isys_catg_port_list__isys_port_type__id',
                        'isys_catg_port_list',
                        'isys_catg_port_list__id',
                        'isys_catg_port_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_port_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_port_list', 'LEFT', 'isys_catg_port_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_port_mode', 'LEFT', 'isys_catg_port_list__isys_port_mode__id', 'isys_port_mode__id')
                    ],
                    C__PROPERTY__DATA__INDEX        => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__PORT__MODE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'   => 'isys_port_mode',
                        'p_bDbFieldNN' => '1'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog'
                    ]
                ]
            ]),
            'plug_type'          => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__PLUG',
                    C__PROPERTY__INFO__DESCRIPTION => 'Plug'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_port_list__isys_plug_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_connection_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_connection_type',
                        'isys_connection_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_connection_type__title
                            FROM isys_catg_port_list
                            INNER JOIN isys_connection_type ON isys_connection_type__id = isys_catg_port_list__isys_plug_type__id',
                        'isys_catg_port_list',
                        'isys_catg_port_list__id',
                        'isys_catg_port_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_port_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_port_list', 'LEFT', 'isys_catg_port_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_connection_type', 'LEFT', 'isys_catg_port_list__isys_plug_type__id', 'isys_connection_type__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__PORT__PLUG',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_connection_type'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_plus'
                    ]
                ]
            ]),
            'negotiation'        => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__NEGOTIATION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Negotiation'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_port_list__isys_port_negotiation__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_port_negotiation',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_port_negotiation',
                        'isys_port_negotiation__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_port_negotiation__title
                            FROM isys_catg_port_list
                            INNER JOIN isys_port_negotiation ON isys_port_negotiation__id = isys_catg_port_list__isys_port_negotiation__id',
                        'isys_catg_port_list',
                        'isys_catg_port_list__id',
                        'isys_catg_port_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_port_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_port_list', 'LEFT', 'isys_catg_port_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_port_negotiation',
                            'LEFT',
                            'isys_catg_port_list__isys_port_negotiation__id',
                            'isys_port_negotiation__id'
                        )
                    ],
                    C__PROPERTY__DATA__INDEX        => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__PORT__NEGOTIATION',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_port_negotiation'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog'
                    ]
                ]
            ]),
            'duplex'             => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__DUPLEX',
                    C__PROPERTY__INFO__DESCRIPTION => 'Duplex'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_port_list__isys_port_duplex__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_port_duplex',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_port_duplex',
                        'isys_port_duplex__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_port_duplex__title
                            FROM isys_catg_port_list
                            INNER JOIN isys_port_duplex ON isys_port_duplex__id = isys_catg_port_list__isys_port_duplex__id',
                        'isys_catg_port_list',
                        'isys_catg_port_list__id',
                        'isys_catg_port_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_port_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_port_list', 'LEFT', 'isys_catg_port_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_port_duplex',
                            'LEFT',
                            'isys_catg_port_list__isys_port_duplex__id',
                            'isys_port_duplex__id'
                        )
                    ],
                    C__PROPERTY__DATA__INDEX        => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__PORT__DUPLEX',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_port_duplex'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog'
                    ]
                ]
            ]),
            'speed'              => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__SPEED',
                    C__PROPERTY__INFO__DESCRIPTION => 'Speed'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_port_list__port_speed_value',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(ROUND(isys_catg_port_list__port_speed_value / isys_port_speed__factor), \' \', isys_port_speed__title)
                            FROM isys_catg_port_list
                            INNER JOIN isys_port_speed ON isys_port_speed__id = isys_catg_port_list__isys_port_speed__id',
                        'isys_catg_port_list',
                        'isys_catg_port_list__id',
                        'isys_catg_port_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory(['isys_catg_port_list__port_speed_value > 0']),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_port_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_port_list', 'LEFT', 'isys_catg_port_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_port_speed',
                            'LEFT',
                            'isys_catg_port_list__isys_port_speed__id',
                            'isys_port_speed__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__PORT__SPEED_VALUE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass'       => 'input-mini',
                        'p_strPlaceholder' => '0.00'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['speed']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'speed_type'
                ]
            ]),
            'speed_type'         => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__UNIT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Unit'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_port_list__isys_port_speed__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_port_speed',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_port_speed',
                        'isys_port_speed__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_port_speed__title
                            FROM isys_catg_port_list
                            INNER JOIN isys_port_speed ON isys_port_speed__id = isys_catg_port_list__isys_port_speed__id',
                        'isys_catg_port_list',
                        'isys_catg_port_list__id',
                        'isys_catg_port_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_port_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_port_list', 'LEFT', 'isys_catg_port_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_port_speed',
                            'LEFT',
                            'isys_catg_port_list__isys_port_speed__id',
                            'isys_port_speed__id'
                        )
                    ],
                    C__PROPERTY__DATA__INDEX        => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__PORT__SPEED',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_port_speed',
                        'p_strClass' => 'input-medium'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'standard'           => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__STANDARD',
                    C__PROPERTY__INFO__DESCRIPTION => 'Standard'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_port_list__isys_port_standard__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_port_standard',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_port_standard',
                        'isys_port_standard__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_port_standard__title
                            FROM isys_catg_port_list
                            INNER JOIN isys_port_standard ON isys_port_standard__id = isys_catg_port_list__isys_port_standard__id',
                        'isys_catg_port_list',
                        'isys_catg_port_list__id',
                        'isys_catg_port_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_port_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_port_list', 'LEFT', 'isys_catg_port_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_port_standard',
                            'LEFT',
                            'isys_catg_port_list__isys_port_standard__id',
                            'isys_port_standard__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__PORT__STANDARD',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_port_standard'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_plus'
                    ]
                ]
            ]),
            'mac'                => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO  => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__MAC',
                    C__PROPERTY__INFO__DESCRIPTION => 'MAC-address'
                ],
                C__PROPERTY__DATA  => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_port_list__mac',
                    C__PROPERTY__DATA__INDEX  => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_port_list__mac FROM isys_catg_port_list',
                        'isys_catg_port_list',
                        'isys_catg_port_list__id',
                        'isys_catg_port_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_port_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI    => [
                    C__PROPERTY__UI__ID => 'C__CATG__PORT__MAC'
                ],
                C__PROPERTY__CHECK => [
                    C__PROPERTY__CHECK__VALIDATION => [
                        FILTER_CALLBACK,
                        [
                            'options' => [
                                'isys_helper',
                                'filter_mac_address'
                            ]
                        ]
                    ]
                ]
            ]),
            'mtu'                => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO  => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__MTU',
                    C__PROPERTY__INFO__DESCRIPTION => 'MTU',
                ],
                C__PROPERTY__DATA  => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_port_list__mtu',
                    C__PROPERTY__DATA__INDEX  => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_port_list__mtu FROM isys_catg_port_list',
                        'isys_catg_port_list',
                        'isys_catg_port_list__id',
                        'isys_catg_port_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_port_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI    => [
                    C__PROPERTY__UI__ID => 'C__CATG__PORT__MTU'
                ],
                C__PROPERTY__CHECK => [
                    C__PROPERTY__CHECK__VALIDATION => [
                        FILTER_VALIDATE_INT,
                        [
                            'options' => [
                                'min_range' => 0,
                                'max_range' => 16436
                            ]
                        ]
                    ]
                ]
            ]),
            'assigned_connector' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CONNECTED_WITH',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned to connector'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_port_list__isys_catg_connector_list__id',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'connected_connector',
                    C__PROPERTY__DATA__FIELD_ALIAS => 'con_connector',
                    C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_catg_port_list__title, \' > \', isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_port_list
                            INNER JOIN isys_catg_connector_list con1 ON con1.isys_catg_connector_list__id = isys_catg_port_list__isys_catg_connector_list__id
                            LEFT JOIN isys_catg_connector_list con2 ON con2.isys_catg_connector_list__isys_cable_connection__id = con1.isys_catg_connector_list__isys_cable_connection__id
                              AND con2.isys_catg_connector_list__id != con1.isys_catg_connector_list__id
                            INNER JOIN isys_obj ON isys_obj__id = con2.isys_catg_connector_list__isys_obj__id',
                        'isys_catg_port_list',
                        'isys_catg_port_list__id',
                        'isys_catg_port_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory(['con2.isys_catg_connector_list__id != con1.isys_catg_connector_list__id']),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_port_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_port_list',
                            'LEFT',
                            'isys_catg_port_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_connector_list',
                            'LEFT',
                            'isys_catg_port_list__isys_catg_connector_list__id',
                            'isys_catg_connector_list__id',
                            '',
                            'con1',
                            'con1'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_connector_list',
                            'LEFT',
                            'isys_catg_connector_list__isys_cable_connection__id',
                            'isys_catg_connector_list__isys_cable_connection__id',
                            'con1',
                            'con2',
                            'con2'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_obj',
                            'LEFT',
                            'isys_catg_connector_list__isys_obj__id',
                            'isys_obj__id',
                            'con2'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__PORT__DEST',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strValue'                                      => new isys_callback([
                            'isys_cmdb_dao_category_g_network_port',
                            'callback_property_assigned_connector'
                        ]),
                        'p_strPopupType'                                  => 'browser_cable_connection_ng',
                        isys_popup_browser_object_ng::C__SECOND_SELECTION => true,
                        isys_popup_browser_object_ng::C__SECOND_LIST      => 'isys_cmdb_dao_category_g_network_port::object_browser',
                        isys_popup_browser_object_ng::C__CAT_FILTER       => 'C__CATG__NETWORK;C__CATG__CONTROLLER_FC_PORT;C__CATG__CABLING'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH  => false,
                    C__PROPERTY__PROVIDES__VIRTUAL => false
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
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_port_list__isys_catg_connector_list__id',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'connected',
                    C__PROPERTY__DATA__FIELD_ALIAS => 'con_connector',
                    C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\', \' > \', con2.isys_catg_connector_list__title)
                            FROM isys_catg_port_list
                            INNER JOIN isys_catg_connector_list con1 ON con1.isys_catg_connector_list__id = isys_catg_port_list__isys_catg_connector_list__id
                            LEFT JOIN isys_catg_connector_list con2 ON con2.isys_catg_connector_list__isys_cable_connection__id = con1.isys_catg_connector_list__isys_cable_connection__id
                              AND con2.isys_catg_connector_list__id != con1.isys_catg_connector_list__id
                            INNER JOIN isys_obj ON isys_obj__id = con2.isys_catg_connector_list__isys_obj__id',
                        'isys_catg_port_list',
                        'isys_catg_port_list__id',
                        'isys_catg_port_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_port_list__isys_obj__id'])
                    ),
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
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connector'
                    ]
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__VALIDATION => false
                ]
            ]),
            'cable'              => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__CABLE_NAME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Cable ID'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_connector_list__isys_cable_connection__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_catg_port_list__title, \' > \', isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_port_list
                            INNER JOIN isys_catg_connector_list ON isys_catg_connector_list__id = isys_catg_port_list__isys_catg_connector_list__id
                            LEFT JOIN isys_cable_connection ON isys_cable_connection__id = isys_catg_connector_list__isys_cable_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_cable_connection__isys_obj__id',
                        'isys_catg_port_list',
                        'isys_catg_port_list__id',
                        'isys_catg_port_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_port_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_port_list', 'LEFT', 'isys_catg_port_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_connector_list',
                            'LEFT',
                            'isys_catg_port_list__isys_catg_connector_list__id',
                            'isys_catg_connector_list__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cable_connection',
                            'LEFT',
                            'isys_catg_connector_list__isys_cable_connection__id',
                            'isys_cable_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_cable_connection__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__PORT__CABLE',
                    C__PROPERTY__UI__PARAMS => [
                        'catFilter'  => 'C__CATG__CABLE;C__CATG__CABLE_CONNECTION',
                        'p_strValue' => new isys_callback([
                            'isys_cmdb_dao_category_g_network_port',
                            'callback_property_cable'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => true,
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'cable_connection'
                    ]
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__VALIDATION => false
                ]
            ]),
            'active'             => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATP__IP__ACTIVE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Active'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_port_list__state_enabled',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_catg_port_list__state_enabled = \'1\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__YES') . '
                        	        WHEN isys_catg_port_list__state_enabled = \'0\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__NO') . ' END)
                                FROM isys_catg_port_list',
                        'isys_catg_port_list',
                        'isys_catg_port_list__id',
                        'isys_catg_port_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_port_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_port_list', 'LEFT', 'isys_catg_port_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__PORT__ACTIVE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => get_smarty_arr_YES_NO()
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_yes_or_no'
                    ]
                ]
            ]),
            'addresses'          => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_list(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__IP_ADDRESS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Host address'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_port_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_port_list',
                        'isys_catg_port_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_catg_port_list__title, \' > \', isys_cats_net_ip_addresses_list__title)
                            FROM isys_catg_port_list
                            INNER JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_catg_port_list__id = isys_catg_port_list__id
                            INNER JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
                        'isys_catg_port_list',
                        'isys_catg_port_list__id',
                        'isys_catg_port_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_port_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__PORT__IP_ADDRESS',
                    C__PROPERTY__UI__PARAMS => [
                        'p_bLinklist' => '1',
                        'p_arData'    => new isys_callback([
                            'isys_cmdb_dao_category_g_network_port',
                            'callback_property_addresses'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL => true,
                    C__PROPERTY__PROVIDES__REPORT  => false,
                    C__PROPERTY__PROVIDES__IMPORT  => false,
                    C__PROPERTY__PROVIDES__EXPORT  => false,
                    C__PROPERTY__PROVIDES__SEARCH  => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'hostaddress'
                    ]
                ]
            ]),
            'layer2_assignment'  => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LAYER2_NET',
                    C__PROPERTY__INFO__DESCRIPTION => 'Layer 2 net',
                    C__PROPERTY__INFO__TYPE        => C__PROPERTY__INFO__TYPE__N2M
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_port_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(obj.isys_obj__title, \' (VLAN: \', isys_cats_layer2_net_list__ident, \') \', \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_port_list AS main
                            INNER JOIN isys_cats_layer2_net_assigned_ports_list AS net2port ON net2port.isys_catg_port_list__id = main.isys_catg_port_list__id
                            INNER JOIN isys_obj AS obj ON obj.isys_obj__id = net2port.isys_cats_layer2_net_assigned_ports_list__isys_obj__id
                            LEFT JOIN isys_cats_layer2_net_list ON isys_cats_layer2_net_list__isys_obj__id = isys_obj__id',
                        'isys_catg_port_list',
                        'main.isys_catg_port_list__id',
                        'main.isys_catg_port_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['main.isys_catg_port_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__LAYER2__DEST',
                    C__PROPERTY__UI__PARAMS => [
                        'multiselection' => true,
                        'p_strPopupType' => 'browser_object_ng',
                        'catFilter'      => 'C__CATS__LAYER2_NET',
                        'p_strValue'     => new isys_callback([
                            'isys_cmdb_dao_category_g_network_port',
                            'callback_property_layer2_assignment'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH  => false,
                    C__PROPERTY__PROVIDES__VIRTUAL => false,
                    C__PROPERTY__PROVIDES__REPORT  => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'port_assigned_layer2_nets'
                    ]
                ]
            ]),
            'hba'                => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__CON_HBA',
                    C__PROPERTY__INFO__DESCRIPTION => 'Connected Interface (HBA)'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_port_list__isys_catg_hba_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_hba_list',
                        'isys_catg_hba_list__id'
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__PORT__INTERFACE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_network_port',
                            'callback_property_interface'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,  // Will be handled by property interface
                    C__PROPERTY__PROVIDES__LIST      => false,  // Will be handled by property interface
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__IMPORT    => false   // @see ID-3725 This property messes with the "Interface".
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_reference_value'
                    ]
                ]
            ]),
            'default_vlan'       => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__DEFAULT_VLAN',
                    C__PROPERTY__INFO__DESCRIPTION => 'Connected interface'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_port_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_cats_layer2_net_assigned_ports_list',
                        'isys_catg_port_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(obj.isys_obj__title, \' (VLAN: \', isys_cats_layer2_net_list__ident, \') \', \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_port_list AS main
                            INNER JOIN isys_cats_layer2_net_assigned_ports_list AS net2port ON net2port.isys_catg_port_list__id = main.isys_catg_port_list__id AND net2port.isys_cats_layer2_net_assigned_ports_list__default = 1
                            INNER JOIN isys_obj AS obj ON obj.isys_obj__id = net2port.isys_cats_layer2_net_assigned_ports_list__isys_obj__id
                            LEFT JOIN isys_cats_layer2_net_list ON isys_cats_layer2_net_list__isys_obj__id = isys_obj__id',
                        'isys_catg_port_list',
                        'main.isys_catg_port_list__id',
                        'main.isys_catg_port_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['main.isys_catg_port_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__PORT__DEFAULT_VLAN'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__VIRTUAL   => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'network_port_property_default_vlan'
                    ]
                ]
            ]),
            'description'        => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_port_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_port_list__description FROM isys_catg_port_list',
                        'isys_catg_port_list',
                        'isys_catg_port_list__id',
                        'isys_catg_port_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_port_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__NETWORK_PORT', 'C__CATG__NETWORK_PORT')
                ]
            ]),
            'relation_direction' => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'Relation direction',
                    C__PROPERTY__INFO__DESCRIPTION => 'Relation direction'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_connector_list__isys_catg_relation_list__id'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__IMPORT     => true,
                    C__PROPERTY__PROVIDES__EXPORT     => true,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'relation_direction'
                    ]
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__VALIDATION => false
                ]
            ])
        ];
    }

    /**
     * Dynamic property price
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function dynamic_properties()
    {
        return [
            '_layer2_assignment' => new DynamicProperty(
                'LC__CMDB__CATS__LAYER2_NET',
                'isys_catg_port_list__id',
                'isys_catg_port_list',
                [
                    $this,
                    'retrieveLayer2Assignment'
                ]
            ),
            '_host_address' => new DynamicProperty(
                'LC__CATG__IP_ADDRESS',
                'isys_catg_port_list__id',
                'isys_catg_port_list',
                [
                    $this,
                    'retrieveHost'
                ]
            )
        ];
    }

    /**
     * @param   array $categoryData
     *
     * @author  I Polianskyi
     */
    public function retrieveHost(array $categoryData)
    {
        if (!is_array($categoryData) || !isset($categoryData['isys_catg_port_list__id'])) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }
        $dao = isys_cmdb_dao_category_g_network_port::instance(isys_application::instance()->container->database);

        $portListID = $categoryData['isys_catg_port_list__id'];
        $query = 'SELECT j3.isys_cats_net_ip_addresses_list__title as title
                    FROM isys_catg_port_list as j1
                    INNER JOIN isys_catg_ip_list as j2 ON j2.isys_catg_ip_list__isys_catg_port_list__id = j1.isys_catg_port_list__id
                    INNER JOIN isys_cats_net_ip_addresses_list as j3 ON j3.isys_cats_net_ip_addresses_list__id = j2.isys_catg_ip_list__isys_cats_net_ip_addresses_list__id
                    WHERE j1.isys_catg_port_list__id = ' . $this->convert_sql_int($portListID);
        $resultSet = $dao->retrieve($query);
        $return = [];
        if (is_countable($resultSet) && count($resultSet)) {
            while ($dataSet = $resultSet->get_row()) {
                $return[] = $dataSet['title'];
            }
            return implode(', ', $return);
        }
        
        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * @param   array $categoryData
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function retrieveLayer2Assignment(array $categoryData)
    {
        if (!is_array($categoryData) || !isset($categoryData['isys_catg_port_list__id'])) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }
        $dao = isys_cmdb_dao_category_g_network_port::instance(isys_application::instance()->container->database);
        $property = $dao->get_property_by_key('layer2_assignment');

        /**
         * @var $selectObject \idoit\Module\Report\SqlQuery\Structure\SelectSubSelect
         */
        $selectObject = $property[C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT];
        $query = $selectObject->getSelectQuery() . ' WHERE ' . $selectObject->getSelectPrimaryKey() . ' = ' . $dao->convert_sql_id($categoryData['isys_catg_port_list__id']);

        $resultSet = $dao->retrieve($query);
        $return = [];
        if (is_countable($resultSet) && count($resultSet)) {
            while ($dataSet = $resultSet->get_row()) {
                $return[] = current($dataSet);
            }

            return implode(',', $return);
        } else {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database).
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed    Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $l_is_master_obj = (isset($p_category_data['properties']['relation_direction'][C__DATA__VALUE])) ? (($p_category_data['properties']['relation_direction'][C__DATA__VALUE] ==
                $p_object_id) ? true : false) : false;
            $l_cable_title = (isset($p_category_data['properties']['cable_title'])) ? $p_category_data['properties']['cable_title'][C__DATA__VALUE] : null;

            /**
             * Checking if layer 2 net exists; Nulling if not.
             */
            if (isset($p_category_data['properties']['layer2_assignment'][C__DATA__VALUE])) {
                if (isys_format_json::is_json_array($p_category_data['properties']['layer2_assignment'][C__DATA__VALUE])) {
                    $layer2Assignments = isys_format_json::decode($p_category_data['properties']['layer2_assignment'][C__DATA__VALUE]);
                    $p_category_data['properties']['layer2_assignment'][C__DATA__VALUE] = array_filter($layer2Assignments, [$this, 'obj_exists']);
                }

                if (!is_array($p_category_data['properties']['layer2_assignment'][C__DATA__VALUE]) &&
                    !$this->obj_exists($p_category_data['properties']['layer2_assignment'][C__DATA__VALUE])) {
                    $p_category_data['properties']['layer2_assignment'][C__DATA__VALUE] = null;
                }
            }

            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:

                    if (!isset($p_category_data['properties']['active'][C__DATA__VALUE]) || $p_category_data['properties']['active'][C__DATA__VALUE] === null) {
                        $p_category_data['properties']['active'][C__DATA__VALUE] = 1;
                    }

                    $l_cableID = null;

                    // @see API-70 and ID-5414 Only create/use a new cable, if the connector is set.
                    if (isset($p_category_data['properties']['assigned_connector'][C__DATA__VALUE]) && $p_category_data['properties']['assigned_connector'][C__DATA__VALUE]) {
                        if (empty($p_category_data['properties']['cable'][C__DATA__VALUE])) {
                            $l_cableID = isys_cmdb_dao_cable_connection::recycle_cable(null);
                        } else {
                            $l_cableID = $p_category_data['properties']['cable'][C__DATA__VALUE];
                        }
                    }

                    $defaultVlan = null;

                    if ($p_category_data['properties']['default_vlan'][C__DATA__VALUE] !== null) {
                        $defaultVlan = $p_category_data['properties']['default_vlan'][C__DATA__VALUE];
                    } elseif ($p_category_data['properties']['default_vlan']['id'] !== null) {
                        $defaultVlan = $p_category_data['properties']['default_vlan']['id'];
                    }

                    $p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        $p_category_data['properties']['title'][C__DATA__VALUE],
                        $p_category_data['properties']['interface'][C__DATA__VALUE],
                        $p_category_data['properties']['plug_type'][C__DATA__VALUE],
                        $p_category_data['properties']['port_type'][C__DATA__VALUE],
                        $p_category_data['properties']['port_mode'][C__DATA__VALUE],
                        $p_category_data['properties']['speed'][C__DATA__VALUE],
                        $p_category_data['properties']['speed_type'][C__DATA__VALUE],
                        $p_category_data['properties']['duplex'][C__DATA__VALUE],
                        $p_category_data['properties']['negotiation'][C__DATA__VALUE],
                        $p_category_data['properties']['standard'][C__DATA__VALUE],
                        null,
                        $p_category_data['properties']['mac'][C__DATA__VALUE],
                        $p_category_data['properties']['active'][C__DATA__VALUE],
                        $p_category_data['properties']['description'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_connector'][C__DATA__VALUE],
                        $l_cableID,
                        $l_cable_title,
                        C__RECORD_STATUS__NORMAL,
                        $p_category_data['properties']['layer2_assignment'][C__DATA__VALUE],
                        $p_category_data['properties']['connector_sibling'][C__DATA__VALUE],
                        $p_category_data['properties']['hba'][C__DATA__VALUE],
                        $l_is_master_obj,
                        $defaultVlan,
                        $p_category_data['properties']['mtu'][C__DATA__VALUE]
                    );

                    break;
                case isys_import_handler_cmdb::C__UPDATE:

                    if ($p_category_data['data_id'] > 0) {
                        // Create connector if it does not exist
                        $l_connector_id = $this->get_connector($p_category_data['data_id']);

                        if (!is_numeric($l_connector_id)) {
                            /**
                             * @var $l_daoConnection isys_cmdb_dao_category_g_connector
                             */
                            $l_daoConnection = isys_cmdb_dao_category_g_connector::instance($this->m_db);
                            $l_connector_id = $l_daoConnection->create(
                                $p_object_id,
                                C__CONNECTOR__OUTPUT,
                                null,
                                null,
                                $p_category_data['properties']['title'][C__DATA__VALUE],
                                null,
                                $p_category_data['properties']['connector_sibling'][C__DATA__VALUE],
                                null,
                                "C__CATG__NETWORK_PORT"
                            );

                            $l_strSQL = "UPDATE isys_catg_port_list SET	" . "isys_catg_port_list__isys_catg_connector_list__id = " .
                                $this->convert_sql_id($l_connector_id) . " " . "WHERE isys_catg_port_list__id = " . $this->convert_sql_id($p_category_data['data_id']);
                            $this->update($l_strSQL);
                        }

                        $this->save(
                            $p_category_data['data_id'],
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['interface'][C__DATA__VALUE],
                            $p_category_data['properties']['plug_type'][C__DATA__VALUE],
                            $p_category_data['properties']['port_type'][C__DATA__VALUE],
                            $p_category_data['properties']['port_mode'][C__DATA__VALUE],
                            $p_category_data['properties']['speed'][C__DATA__VALUE],
                            $p_category_data['properties']['speed_type'][C__DATA__VALUE],
                            $p_category_data['properties']['duplex'][C__DATA__VALUE],
                            $p_category_data['properties']['negotiation'][C__DATA__VALUE],
                            $p_category_data['properties']['standard'][C__DATA__VALUE],
                            null,
                            $p_category_data['properties']['mac'][C__DATA__VALUE],
                            $p_category_data['properties']['active'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            $p_category_data['properties']['assigned_connector'][C__DATA__VALUE],
                            $p_category_data['properties']['cable'][C__DATA__VALUE],
                            $l_cable_title,
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['layer2_assignment'][C__DATA__VALUE],
                            $p_category_data['properties']['connector_sibling'][C__DATA__VALUE],
                            $p_category_data['properties']['hba'][C__DATA__VALUE],
                            $l_is_master_obj,
                            $p_category_data['properties']['default_vlan'][C__DATA__VALUE],
                            $p_category_data['properties']['mtu'][C__DATA__VALUE]
                        );
                    }
                    break;
            }

            if (isset($p_category_data['data_id']) && $p_category_data['data_id'] > 0) {
                /**
                 * @var $l_dao_ip isys_cmdb_dao_category_g_ip
                 */
                $l_dao_ip = isys_cmdb_dao_category_g_ip::instance($this->m_db);

                if (is_array($p_category_data['properties']['addresses'][C__DATA__VALUE])) {
                    foreach ($p_category_data['properties']['addresses'][C__DATA__VALUE] as $l_ip_id) {
                        if (is_numeric($l_ip_id)) {
                            $this->attach_ip($p_category_data['data_id'], $l_ip_id);
                        } elseif (strstr($l_ip_id, '.')) {
                            $l_ip = $l_dao_ip->get_ip_by_address($l_ip_id)
                                ->get_row();

                            if (is_array($l_ip) && isset($l_ip['isys_catg_ip_list__id'])) {
                                $this->attach_ip($p_category_data['data_id'], $l_ip['isys_catg_ip_list__id']);
                            }
                        }
                    }
                } elseif (is_scalar($p_category_data['properties']['addresses'][C__DATA__VALUE])) {
                    $l_ip = $l_dao_ip->get_ip_by_address($p_category_data['properties']['addresses'][C__DATA__VALUE])
                        ->get_row();

                    if (is_array($l_ip) && isset($l_ip['isys_catg_ip_list__id'])) {
                        $this->attach_ip($p_category_data['data_id'], $l_ip['isys_catg_ip_list__id']);
                    }
                }
            }
        }

        return isset($p_category_data['data_id']) ? $p_category_data['data_id'] : false;
    }

    /**
     * Clears all ip attachments for $p_netp_port_id.
     *
     * @param   integer $p_port_id
     * @param   integer $p_ip_id
     *
     * @return  boolean
     */
    public function clear_ip_attachments($p_port_id = null, $p_ip_id = null)
    {
        if (isset($p_port_id) && $p_port_id > 0) {
            $l_delete = 'UPDATE isys_catg_ip_list SET
				isys_catg_ip_list__isys_catg_log_port_list__id = NULL,
				isys_catg_ip_list__isys_catg_port_list__id = NULL
				WHERE isys_catg_ip_list__isys_catg_port_list__id = ' . $this->convert_sql_id($p_port_id);

            $this->update($l_delete);
        }

        if (isset($p_ip_id) && $p_ip_id > 0) {
            $l_delete = 'UPDATE isys_catg_ip_list SET
				isys_catg_ip_list__isys_catg_log_port_list__id = NULL,
				isys_catg_ip_list__isys_catg_log_port_list__id = NULL
				WHERE isys_catg_ip_list__id = ' . $this->convert_sql_id($p_ip_id);

            $this->update($l_delete);
        }

        return $this->apply_update();
    }

    /**
     * Attaches an ip address to a port.
     *
     * @param   integer $p_netp_port_id
     * @param   integer $p_catg_ip_id
     *
     * @return  boolean
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     */
    public function attach_ip($p_netp_port_id, $p_catg_ip_id)
    {
        if (is_numeric($p_netp_port_id) && is_numeric($p_catg_ip_id) && $p_catg_ip_id > 0 && $p_netp_port_id > 0) {
            $l_sql = 'UPDATE isys_catg_ip_list SET
				isys_catg_ip_list__isys_catg_port_list__id = ' . $this->convert_sql_id($p_netp_port_id) . '
				WHERE isys_catg_ip_list__id = ' . $this->convert_sql_id($p_catg_ip_id) . ';';

            return ($this->update($l_sql) && $this->apply_update());
        } else {
            return false;
        }
    }

    /**
     * Saves a cable connection (port)
     *
     * @param integer $p_sourcePortID
     * @param integer $p_destPortID
     *
     * @return boolean
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function connection_save($p_sourcePortID, $p_destPortID = null, $p_cableID, $p_master_connector_id = null)
    {
        $l_dao = new isys_cmdb_dao_cable_connection($this->m_db);

        if (!is_numeric($p_destPortID)) {
            if (!is_numeric($p_sourcePortID)) {
                return true;
            }

            $l_conID = $l_dao->get_cable_connection_id_by_connector_id($p_sourcePortID);

            if ($l_conID != null) {
                $l_dao->delete_cable_connection($l_conID);
            }

            return true;
        }

        try {
            $l_dao->delete_cable_connection($l_dao->get_cable_connection_id_by_connector_id($p_sourcePortID));
            $l_dao->delete_cable_connection($l_dao->get_cable_connection_id_by_connector_id($p_destPortID));

            $l_conID = $l_dao->add_cable_connection($p_cableID);

            if (!$l_dao->save_connection($p_sourcePortID, $p_destPortID, $l_conID, $p_master_connector_id)) {
                return false;
            }
        } catch (Exception $e) {
            isys_application::instance()->container['notify']->error($e->getMessage());
        }

        return true;
    }

    /**
     * Create method.
     *
     * @param integer $p_object_id
     * @param string  $p_title
     * @param integer $p_interface_id
     * @param integer $p_plugtype_id
     * @param integer $p_porttype_id
     * @param integer $p_portmode_id
     * @param mixed   $p_portspeed  Might be an integer or an float.
     * @param integer $p_portspeedID
     * @param integer $p_duplex_id
     * @param integer $p_negotiation_id
     * @param integer $p_standard_id
     * @param unknown $p_net_object Seems unused.
     * @param string  $p_mac
     * @param integer $p_active
     * @param string  $p_description
     * @param integer $p_connectorID
     * @param integer $p_cableID
     * @param string  $p_cable_name
     * @param integer $p_status
     * @param array   $p_layer2_objects
     * @param null    $p_connector_sibling
     * @param null    $p_hba_id
     * @param bool    $p_is_master_obj
     * @param null    $p_default_layer2_id
     * @param null    $p_mtu
     *
     * @return mixed Integer of the last inserted ID, Boolean (false) On failure
     * @throws Exception
     * @throws isys_exception_dao
     */
    public function create(
        $p_object_id,
        $p_title,
        $p_interface_id,
        $p_plugtype_id,
        $p_porttype_id,
        $p_portmode_id,
        $p_portspeed,
        $p_portspeedID,
        $p_duplex_id,
        $p_negotiation_id,
        $p_standard_id,
        $p_net_object,
        $p_mac,
        $p_active,
        $p_description,
        $p_connectorID,
        $p_cableID = null,
        $p_cable_name,
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_layer2_objects = null,
        $p_connector_sibling = null,
        $p_hba_id = null,
        $p_is_master_obj = false,
        $p_default_layer2_id = null,
        $p_mtu = null
    ) {
        if ($p_portspeed > 0) {
            $p_portspeed = isys_convert::speed($p_portspeed, intval($p_portspeedID));
        }

        $l_connectortype_data = null;
        $l_port_id = 0;

        /**
         * @var $l_daoConnection isys_cmdb_dao_category_g_connector
         */
        $l_daoConnection = isys_cmdb_dao_category_g_connector::instance($this->m_db);

        if (empty($p_portmode_id)) {
            $l_port_mode_arr = $this->get_port_modes('C__PORT_MODE__STANDARD')
                ->get_row();
            $p_portmode_id = $l_port_mode_arr['isys_port_mode__id'];
        }

        // Get Connector type for the connector
        if ($p_plugtype_id > 0) {
            $l_plugtype_data = isys_factory_cmdb_dialog_dao::get_instance('isys_connection_type', $this->m_db)
                ->get_data($p_plugtype_id, null);
            $l_connectortype_data = isys_factory_cmdb_dialog_dao::get_instance('isys_connection_type', $this->m_db)
                ->get_data(null, $l_plugtype_data['title']);
        }

        $l_strTitle = $p_title;

        $l_connectorID = $l_daoConnection->create(
            $p_object_id,
            C__CONNECTOR__OUTPUT,
            null,
            ($l_connectortype_data) ? $l_connectortype_data['isys_connection_type__id'] : defined_or_default('C__CONNECTION_TYPE__RJ45'),
            $l_strTitle,
            null,
            $p_connector_sibling,
            (!empty($p_connectorID)) ? $p_connectorID : null,
            "C__CATG__NETWORK_PORT",
            $p_cableID
        );

        // @see  ID-5665  Format the mac address here, so that all input sources will be handled.
        $p_mac = $this->formatMacAddress($p_mac);

        $l_q = "INSERT INTO isys_catg_port_list SET
            isys_catg_port_list__isys_catg_netp_list__id = " . $this->convert_sql_id($p_interface_id) . ",
            isys_catg_port_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ",
            isys_catg_port_list__isys_port_negotiation__id = " . $this->convert_sql_id($p_negotiation_id) . ",
            isys_catg_port_list__isys_port_standard__id = " . $this->convert_sql_id($p_standard_id) . ",
            isys_catg_port_list__isys_port_duplex__id = " . $this->convert_sql_id($p_duplex_id) . ",
            isys_catg_port_list__isys_plug_type__id = " . $this->convert_sql_id($p_plugtype_id) . ",
            isys_catg_port_list__isys_port_type__id = " . $this->convert_sql_id($p_porttype_id) . ",
            isys_catg_port_list__isys_port_mode__id = " . $this->convert_sql_id($p_portmode_id) . ",
            isys_catg_port_list__port_speed_value = " . $this->convert_sql_text($p_portspeed) . ",
            isys_catg_port_list__isys_port_speed__id = " . $this->convert_sql_id($p_portspeedID) . ",
            isys_catg_port_list__title = " . $this->convert_sql_text($l_strTitle) . ",
            isys_catg_port_list__description = " . $this->convert_sql_text($p_description) . ",
            isys_catg_port_list__mac = " . $this->convert_sql_text($p_mac) . ",
            isys_catg_port_list__state_enabled = " . $this->convert_sql_boolean($p_active) . ",
            isys_catg_port_list__isys_catg_connector_list__id = " . $this->convert_sql_id($l_connectorID) . ",
            isys_catg_port_list__status = " . $this->convert_sql_int($p_status) . ",
            isys_catg_port_list__isys_catg_hba_list__id = " . $this->convert_sql_id($p_hba_id) . ",
            isys_catg_port_list__mtu = " . $this->convert_sql_int($p_mtu) . ";";

        $l_bRet = $this->update($l_q);

        if ($l_bRet) {
            if ($this->apply_update()) {
                $l_port_id = $this->get_last_insert_id();
                $this->attach_layer2_net($l_port_id, $p_layer2_objects, $p_default_layer2_id);
            }
        }

        $l_connectorRearID = $l_connectorID;

        if ($p_is_master_obj) {
            $l_master_connector = $l_connectorRearID;
        } else {
            $l_master_connector = $p_connectorID;
        }

        if ($p_connectorID != "") {
            // We get the cable object via connector id
            $l_cableID = isys_cmdb_dao_cable_connection::instance(isys_application::instance()->database)
                ->get_assigned_cable($l_connectorID);
            $this->connection_save($l_connectorRearID, $p_connectorID, $l_cableID, $l_master_connector);
        }

        if ($l_port_id > 0) {
            return $l_port_id;
        }

        return false;
    }

    /**
     * Delete all connections to any layer2 net
     *
     * @param int $p_cat_id PortID
     *
     * @return boolean
     */
    public function clear_layer2_attachments($p_cat_id)
    {
        $l_sql = "DELETE FROM isys_cats_layer2_net_assigned_ports_list " . "WHERE isys_catg_port_list__id = " . $this->convert_sql_id($p_cat_id) . ";";

        if ($this->update($l_sql)) {
            return $this->apply_update();
        } else {
            return false;
        }
    }

    /**
     * This method fetches the assigned host-addresses by a given port-id.
     *
     * @param   integer $p_cat_id
     * @param   boolean $p_only_primary
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_attached_ips($p_cat_id, $p_only_primary = false)
    {
        $l_sql = 'SELECT * FROM isys_catg_ip_list
			INNER JOIN isys_obj ON isys_catg_ip_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
			LEFT JOIN isys_cats_net_list ON isys_cats_net_list__isys_obj__id = isys_cats_net_ip_addresses_list__isys_obj__id
			WHERE isys_catg_ip_list__isys_catg_port_list__id = ' . $this->convert_sql_int($p_cat_id);

        if ($p_only_primary) {
            $l_sql .= ' AND isys_catg_ip_list__primary = 1';
        }

        // We need this for the "layer2 and layer3" report! Please don't remove!
        return $this->retrieve($l_sql . ' ORDER BY isys_catg_ip_list__primary DESC;');
    }

    /**
     * Gets the default vlan
     *
     * @return int|null
     */
    public function get_default_vlan_id($p_id)
    {
        if ($p_id > 0) {
            $l_sql = 'SELECT isys_cats_layer2_net_assigned_ports_list__isys_obj__id
                FROM isys_cats_layer2_net_assigned_ports_list
				WHERE isys_catg_port_list__id = ' . $this->convert_sql_id($p_id) . '
				AND isys_cats_layer2_net_assigned_ports_list__default = 1;';

            $l_res = $this->retrieve($l_sql);

            if (is_countable($l_res) && count($l_res) === 1) {
                return $l_res->get_row_value('isys_cats_layer2_net_assigned_ports_list__isys_obj__id');
            }
        }

        return null;
    }

    /**
     *
     * @param   integer $p_cat_id
     * @param   boolean $p_json
     *
     * @return  mixed  PHP or JSON array.
     * @author  Selcuk Kekec <skekec@synetics.de>
     */
    public function get_attached_layer2_net_as_array($p_cat_id, $p_json = false)
    {
        $l_res_arr = [];
        $l_res = $this->get_attached_layer2_net($p_cat_id);

        if ($l_res->num_rows()) {
            while ($l_row = $l_res->get_row()) {
                $l_res_arr[] = $l_row['object_id'];
                if ($l_row['default_vlan']) {
                    $this->m_default_vlan_id = $l_row['object_id'];
                }
            }
        }

        return ($p_json) ? isys_format_json::decode($l_res_arr) : $l_res_arr;
    }

    /**
     * Method for retrieving all attached layer2 nets.
     *
     * @param   integer $p_cat_id
     *
     * @return  isys_component_dao_result
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function get_attached_layer2_net($p_cat_id)
    {
        $l_sql = 'SELECT isys_cats_layer2_net_assigned_ports_list__isys_obj__id AS object_id,
                    isys_cats_layer2_net_list__ident AS vlan,
                    isys_obj__title AS title,
                    isys_cats_layer2_net_assigned_ports_list__default AS default_vlan
                FROM isys_cats_layer2_net_assigned_ports_list
                INNER JOIN isys_obj ON isys_cats_layer2_net_assigned_ports_list__isys_obj__id = isys_obj__id
                LEFT JOIN isys_cats_layer2_net_list ON isys_cats_layer2_net_list__isys_obj__id = isys_obj__id
                WHERE isys_catg_port_list__id = ' . $this->convert_sql_id($p_cat_id) . '
                AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Attach a layer2 net to the Port. You can deliver an array for $p_object_id.
     *
     * @param integer $p_cat_id
     * @param mixed   $p_object_id
     * @param integer $p_default
     *
     * @return boolean
     * @throws isys_exception_dao
     */
    public function attach_layer2_net($p_cat_id, $p_object_id, $p_default = 0)
    {
        $l_sql = "INSERT INTO isys_cats_layer2_net_assigned_ports_list SET
            isys_catg_port_list__id = " . $this->convert_sql_id($p_cat_id) . ",
            isys_cats_layer2_net_assigned_ports_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ",
            isys_cats_layer2_net_assigned_ports_list__default = %d,
            isys_cats_layer2_net_assigned_ports_list__isys_obj__id = '%s' ;";

        if (is_array($p_object_id)) {
            foreach ($p_object_id as $l_obj_id) {
                if ($l_obj_id > 0) {
                    $this->update(sprintf($l_sql, ($l_obj_id == $p_default ? 1 : 0), $this->convert_sql_id($l_obj_id)));
                }
            }

            return $this->apply_update();
        }

        if (!empty($p_object_id)) {
            return (($this->update(sprintf($l_sql, ($p_object_id == $p_default ? 1 : 0), $p_object_id))) ? $this->apply_update() : false);
        }

        return false;
    }

    /**
     * @param $p_port_id
     *
     * @return mixed
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_connector($p_port_id)
    {
        return $this->retrieve('SELECT isys_catg_port_list__isys_catg_connector_list__id AS con
            FROM isys_catg_port_list
            WHERE isys_catg_port_list__id = ' . $this->convert_sql_id($p_port_id) . ';')
            ->get_row_value('con');
    }

    /**
     * Method for retrieving the maximum speed of a port from a given object.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_unit_id
     *
     * @return  string
     */
    public function get_max_speed($p_obj_id, $p_unit_id = null)
    {
        $l_sql = "SELECT isys_catg_port_list__isys_port_speed__id, isys_catg_port_list__port_speed_value " . "FROM isys_catg_port_list " .
            "WHERE isys_catg_port_list__state_enabled = 1 " . "AND isys_catg_port_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id) . ";";

        $l_res = $this->retrieve($l_sql);

        $l_max_speed = 0;

        while ($l_row = $l_res->get_row()) {
            if (!is_null($p_unit_id)) {
                if ($l_max_speed <= isys_convert::speed($l_row["isys_catg_port_list__port_speed_value"], $p_unit_id, C__CONVERT_DIRECTION__BACKWARD)) {
                    $l_max_speed = isys_convert::speed($l_row["isys_catg_port_list__port_speed_value"], $p_unit_id, C__CONVERT_DIRECTION__BACKWARD);
                }
            } else {
                if ($l_max_speed <= $l_row["isys_catg_port_list__port_speed_value"]) {
                    $l_max_speed = $l_row["isys_catg_port_list__port_speed_value"];
                }
            }
        }

        return $l_max_speed;
    }

    /**
     * Method for retrieving the port modes.
     *
     * @param int|string $p_value
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function get_port_modes($p_value = null)
    {
        $l_sql = 'SELECT * FROM isys_port_mode';

        if (is_numeric($p_value)) {
            $l_sql .= ' AND isys_port_mode__id = ' . $this->convert_sql_id($p_value);
        } elseif (is_string($p_value) && strpos($p_value, 'C__')) {
            $l_sql .= ' AND isys_port_mode__const = ' . $this->convert_sql_text($p_value);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for retrieving the port types.
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_port_types()
    {
        return $this->retrieve('SELECT * FROM isys_port_type;');
    }

    /**
     * Get ports by object and/or interface id.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_interface_id
     * @param   integer $p_status
     * @param   null    $p_netp_port_id
     * @param   array   $p_filter
     * @param   null    $p_condition
     *
     * @return  isys_component_dao_result
     */
    public function get_ports($p_obj_id = null, $p_interface_id = null, $p_status = null, $p_netp_port_id = null, $p_filter = [], $p_condition = null, $p_order_by = false)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT
            isys_port_duplex__id,
            isys_port_duplex__title,
            isys_port_duplex__const,
            isys_port_mode__id,
            isys_port_mode__title,
            isys_port_mode__const,
            isys_port_speed__id,
            isys_port_speed__title,
            isys_port_speed__const,
            isys_port_negotiation__id,
            isys_port_negotiation__title,
            isys_port_negotiation__const,
            isys_connection_type__id,
            isys_connection_type__title,
            isys_connection_type__const,
            isys_port_standard__id,
            isys_port_standard__title,
            isys_port_standard__const,       
            isys_catg_netp_list.*,
            isys_port_type.*,
            isys_obj.*,
            isys_catg_connector_list.*,
            isys_cable_connection.*,
            isys_catg_port_list.*,
            connected_connector.isys_catg_connector_list__id AS con_connector,
            isys_catg_hba_list.*,
            isys_cable_connection__isys_obj__id as cableId,
            (
                SELECT GROUP_CONCAT(isys_cats_net_ip_addresses_list__title) FROM isys_catg_ip_list
                INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list.isys_cats_net_ip_addresses_list__id
                WHERE isys_catg_ip_list__isys_catg_port_list__id = isys_catg_port_list__id
            ) AS assigned_ips
			FROM isys_catg_port_list
			LEFT JOIN isys_obj ON isys_catg_port_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_catg_netp_list ON isys_catg_port_list__isys_catg_netp_list__id = isys_catg_netp_list__id
			LEFT JOIN isys_catg_hba_list ON isys_catg_port_list__isys_catg_hba_list__id = isys_catg_hba_list__id
			LEFT JOIN isys_port_type ON isys_catg_port_list__isys_port_type__id = isys_port_type__id
			LEFT JOIN isys_catg_connector_list ON isys_catg_connector_list__id = isys_catg_port_list__isys_catg_connector_list__id
			LEFT JOIN isys_cable_connection ON isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id
			LEFT JOIN isys_catg_connector_list AS connected_connector ON connected_connector.isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id AND (connected_connector.isys_catg_connector_list__id != isys_catg_connector_list.isys_catg_connector_list__id OR connected_connector.isys_catg_connector_list__id IS NULL)
			LEFT JOIN isys_port_speed ON isys_port_speed__id = isys_catg_port_list__isys_port_speed__id
            LEFT JOIN isys_port_duplex ON isys_port_duplex__id = isys_catg_port_list__isys_port_duplex__id
            LEFT JOIN isys_port_mode ON isys_port_mode__id = isys_catg_port_list__isys_port_mode__id
            LEFT JOIN isys_port_negotiation ON isys_port_negotiation__id = isys_catg_port_list__isys_port_negotiation__id   
            LEFT JOIN isys_connection_type ON isys_connection_type__id = isys_catg_port_list__isys_plug_type__id
            LEFT JOIN isys_port_standard ON isys_port_standard__id = isys_catg_port_list__isys_port_standard__id
			WHERE TRUE " . $p_condition . " ";

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_interface_id !== null) {
            $l_sql .= ' AND isys_catg_netp_list__id = ' . $this->convert_sql_id($p_interface_id);
        }

        if ($p_status !== null) {
            $l_sql .= ' AND isys_catg_port_list__status = ' . $this->convert_sql_int($p_status);
        }

        if ($p_netp_port_id !== null) {
            $l_sql .= ' AND isys_catg_port_list__id = ' . $this->convert_sql_id($p_netp_port_id);
        }

        $l_sql .= ' GROUP BY isys_catg_port_list__id ';

        if ($p_order_by) {
            $l_sql .= " ORDER BY ";

            if (is_array($p_obj_id)) {
                $l_sql .= " isys_obj__id ASC, ";
            }
            $l_sql .= "LENGTH(isys_catg_port_list__title), isys_catg_port_list__title ASC;";
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Delete port
     *
     * @param int $p_port_id
     *
     * @return bool
     */
    public function delete($p_port_id)
    {
        if (is_numeric($p_port_id)) {
            $this->begin_update();

            $l_strSQL = "DELETE FROM isys_catg_port_list " . "WHERE isys_catg_port_list__id = " . $this->convert_sql_id($p_port_id);

            if ($this->update($l_strSQL)) {
                return $this->apply_update();
            }
        }

        return false;
    }

    /**
     * Import-Handler for physical port including netport, port categories.
     *
     * @param   array $p_data Data with entries for port category 'ip'.
     *
     * @return  array
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function import($p_data, $p_object_id)
    {
        $l_status = -1;
        $l_cat = -1;
        $l_arPortID = [];

        // Handle ip and subnetmask
        if (is_array($p_data['ip']) && isset($p_data['ip'][1])) {
            $p_data['ip'] = strstr($p_data['ip'][1], '.') ? $p_data['ip'][1] : $p_data['ip'][0];
        }

        if (is_array($p_data['subnetmask']) && isset($p_data['subnetmask'][1])) {
            $p_data['subnetmask'] = strstr($p_data['subnetmask'][1], '.') ? $p_data['subnetmask'][1] : $p_data['subnetmask'][0];
        }

        if (is_array($p_data)) {
            $l_sql = 'DELETE FROM isys_catg_port_list
                WHERE isys_catg_port_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . '
                AND isys_catg_port_list__mac = ' . $this->convert_sql_text($p_data['mac']) . ';';

            if ($this->update($l_sql) && $this->apply_update()) {
                $l_objData = $this->get_object_by_id($p_object_id)
                    ->__to_array();
                $l_dao_ip = new isys_cmdb_dao_category_g_ip($this->m_db);
                $l_dao_net = new isys_cmdb_dao_category_s_net($this->m_db);

                $l_arPort = $p_data;

                $_POST['C__CATG__PORT__TITLE'] = $l_arPort["name"];
                $_POST['C__CATG__PORT__MAC'] = $l_arPort["mac"];
                $_POST['C__CATG__PORT__ACTIVE'] = 1;
                $_POST['C__CATG__PORT__SUFFIX_COUNT'] = 1;

                unset($_GET[C__CMDB__GET__CATLEVEL_1]);

                isys_module_request::get_instance()
                    ->_internal_set_private("m_post", $_POST)
                    ->_internal_set_private("m_get", $_GET);

                // Save element and create netport and port categories along with it
                $l_port_id = $this->save_element($l_cat, $l_status);

                $l_arPortID[] = $l_port_id;

                if (isset($p_data["ip"])) {
                    if (is_array($p_data["ip"])) {
                        foreach ($p_data["ip"] as $l_key => $l_ip) {
                            $l_subnetmask = $p_data["subnetmask"][$l_key];

                            /* Parse net type */
                            if (strstr($l_ip, ":")) {
                                $l_net_type = defined_or_default('C__CATS_NET_TYPE__IPV6');
                                $l_net = defined_or_default('C__OBJ__NET_GLOBAL_IPV6');

                                $l_net_ip = Ip::validate_net_ipv6($l_ip, $l_subnetmask);
                                $l_cidr_suffix = $l_subnetmask;
                                $l_range = Ip::calc_ip_range_ipv6($l_net_ip, $l_cidr_suffix);
                            } else {
                                $l_net_type = defined_or_default('C__CATS_NET_TYPE__IPV4');
                                $l_net = defined_or_default('C__OBJ__NET_GLOBAL_IPV4');

                                $l_net_ip = Ip::validate_net_ip($l_ip, $l_subnetmask, null, true);
                                $l_cidr_suffix = Ip::calc_cidr_suffix($l_subnetmask);
                                $l_range = Ip::calc_ip_range($l_net_ip, $l_subnetmask);
                            }

                            if ($l_net_ip) {
                                $l_condition = 'AND (isys_obj__title = ' . $l_dao_net->convert_sql_text($l_net_ip) . ' OR isys_cats_net_list__address = ' .
                                    $l_dao_net->convert_sql_text($l_net_ip) . ')';
                                $l_net_res = $l_dao_net->get_data(null, null, $l_condition);
                                if ($l_net_res->num_rows() > 0) {
                                    $l_net = $l_net_res->get_row_value('isys_obj__id');
                                } elseif (isset($p_data["subnetmask"][$l_key])) {

                                    // net does not exist
                                    // Create Layer-3 Net
                                    $l_net = $l_dao_net->insert_new_obj(defined_or_default('C__OBJTYPE__LAYER3_NET'), false, $l_net_ip, null, C__RECORD_STATUS__NORMAL);
                                    $l_dao_net->create(
                                        $l_net,
                                        C__RECORD_STATUS__NORMAL,
                                        $l_net_ip,
                                        $l_net_type,
                                        $l_net_ip,
                                        $l_subnetmask,
                                        '',
                                        false,
                                        $l_range['from'],
                                        $l_range['to'],
                                        null,
                                        null,
                                        '',
                                        $l_cidr_suffix
                                    );
                                }
                            }

                            /**
                             * Create ip for assignment
                             */
                            $l_ipdata = $l_dao_ip->get_ip_by_address($l_ip);

                            $l_prim_ip = $l_dao_ip->get_primary_ip($p_object_id)
                                ->get_row_value('isys_cats_net_ip_addresses_list__title');

                            if ($l_prim_ip) {
                                if ($l_prim_ip != $l_ip) {
                                    $l_primary = 0;
                                } else {
                                    $l_primary = 1;
                                }
                            } else {
                                $l_primary = 1;
                            }

                            /**
                             * These information is now stored in cats_net
                             * $p_data["subnetmask"]
                             * $p_data["gateway"]
                             */

                            if ($l_ipdata->num_rows() <= 0) {
                                $l_ip_id = $l_dao_ip->create(
                                    $p_object_id,
                                    $l_objData["isys_obj__hostname"],
                                    defined_or_default('C__CATP__IP__ASSIGN__STATIC', 2),
                                    $l_ip,
                                    $l_primary,
                                    0,
                                    [],
                                    [],
                                    1,
                                    $l_net_type,
                                    $l_net,
                                    ''
                                );
                            } else {
                                $l_iprow = $l_ipdata->get_row();
                                $l_ip_id = $l_iprow['isys_catg_ip_list__id'];

                                if ($l_ip_id > 0) {
                                    $l_dao_ip->save(
                                        $l_ip_id,
                                        $l_objData["isys_obj__hostname"],
                                        defined_or_default('C__CATP__IP__ASSIGN__STATIC', 2),
                                        $l_ip,
                                        $l_primary,
                                        0,
                                        [],
                                        [],
                                        1,
                                        $l_net_type,
                                        $l_net,
                                        ''
                                    );
                                }
                            }

                            $this->attach_ip($l_port_id, $l_ip_id);
                        }
                    } else {
                        /* Parse net type */
                        if (strstr($p_data["ip"], ":")) {
                            $l_net_type = defined_or_default('C__CATS_NET_TYPE__IPV6');
                            $l_net = defined_or_default('C__OBJ__NET_GLOBAL_IPV6');

                            $l_net_ip = Ip::validate_net_ipv6($p_data["ip"], $p_data["subnetmask"]);
                        } else {
                            $l_net_type = defined_or_default('C__CATS_NET_TYPE__IPV4');
                            $l_net = defined_or_default('C__OBJ__NET_GLOBAL_IPV4');

                            $l_net_ip = Ip::validate_net_ip($p_data["ip"], $p_data["subnetmask"], null, true);
                        }

                        if ($l_net_ip) {
                            $l_condition = 'AND (isys_obj__title = ' . $l_dao_net->convert_sql_text($l_net_ip) . ' OR isys_cats_net_list__address = ' .
                                $l_dao_net->convert_sql_text($l_net_ip) . ')';
                            $l_net_res = $l_dao_net->get_data(null, null, $l_condition);
                            if ($l_net_res->num_rows() > 0) {
                                $l_net = $l_net_res->get_row_value('isys_obj__id');
                            } elseif (isset($p_data["subnetmask"])) {
                                $l_range = Ip::calc_ip_range($l_net_ip, $p_data["subnetmask"]);
                                // net does not exist
                                // Create Layer-3 Net
                                $l_net = $l_dao_net->insert_new_obj(defined_or_default('C__OBJTYPE__LAYER3_NET'), false, $l_net_ip, null, C__RECORD_STATUS__NORMAL);
                                $l_dao_net->create(
                                    $l_net,
                                    C__RECORD_STATUS__NORMAL,
                                    $l_net_ip,
                                    $l_net_type,
                                    $l_net_ip,
                                    $p_data["subnetmask"],
                                    '',
                                    false,
                                    $l_range['from'],
                                    $l_range['to'],
                                    null,
                                    null,
                                    '',
                                    Ip::calc_cidr_suffix($p_data["subnetmask"])
                                );
                            }
                        }

                        /**
                         * Create ip for assignment
                         */
                        $l_ipdata = $l_dao_ip->get_ip_by_address($p_data["ip"]);

                        $l_prim_ip = $l_dao_ip->get_primary_ip($p_object_id)
                            ->get_row_value('isys_cats_net_ip_addresses_list__title');

                        if ($l_prim_ip) {
                            if ($l_prim_ip != $p_data["ip"]) {
                                $l_primary = 0;
                            } else {
                                $l_primary = 1;
                            }
                        } else {
                            $l_primary = 1;
                        }

                        /**
                         * These information is now stored in cats_net
                         * $p_data["subnetmask"]
                         * $p_data["gateway"]
                         */

                        if ($l_ipdata->num_rows() <= 0) {
                            $l_ip_id = $l_dao_ip->create(
                                $p_object_id,
                                $l_objData["isys_obj__hostname"],
                                defined_or_default('C__CATP__IP__ASSIGN__STATIC', 2),
                                $p_data["ip"],
                                $l_primary,
                                0,
                                [],
                                [],
                                1,
                                $l_net_type,
                                $l_net,
                                ''
                            );
                        } else {
                            $l_iprow = $l_ipdata->get_row();
                            $l_ip_id = $l_iprow['isys_catg_ip_list__id'];

                            if ($l_ip_id > 0) {
                                $l_dao_ip->save(
                                    $l_ip_id,
                                    $l_objData["isys_obj__hostname"],
                                    defined_or_default('C__CATP__IP__ASSIGN__STATIC', 2),
                                    $p_data["ip"],
                                    $l_primary,
                                    0,
                                    [],
                                    [],
                                    1,
                                    $l_net_type,
                                    $l_net,
                                    ''
                                );
                            }
                        }

                        $this->attach_ip($l_port_id, $l_ip_id);
                    }
                }
            } else {
                throw new Exception("Error while deleting existing ports.");
            }
        }

        return $l_arPortID;
    }

    /**
     * A method, which bundles the handle_ajax_request and handle_preselection.
     *
     * @param  integer $p_context
     * @param  array   $p_parameters
     *
     * @return array|string
     * @throws isys_exception_database
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function object_browser($p_context, array $p_parameters)
    {
        $language = isys_application::instance()->container->get('language');

        switch ($p_context) {
            case isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST:
                // Handle Ajax-Request.
                $l_return = [];

                $l_objects = $this->get_data(null, $_GET[C__CMDB__GET__OBJECT], '', null, C__RECORD_STATUS__NORMAL);

                if ($l_objects->num_rows() > 0) {
                    while ($l_row = $l_objects->get_row()) {
                        $l_return[] = [
                            '__checkbox__' => $l_row["isys_catg_port_list__id"],
                            'Port'         => $l_row["isys_catg_port_list__title"]
                        ];
                    }
                }

                return json_encode($l_return);

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PREPARATION:
                // Preselection
                $l_return = [
                    'category' => [],
                    'first'    => [],
                    'second'   => []
                ];

                $p_preselection = (array)$p_parameters['preselection'];

                if (!empty($p_preselection)) {
                    // Save a bit memory: Only select needed fields!
                    $l_sql = "SELECT * 
                        FROM isys_catg_ip_list
                        INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
                        LEFT JOIN isys_obj ON isys_obj__id = isys_catg_ip_list__isys_obj__id
                        WHERE isys_catg_ip_list__id " . $this->prepare_in_condition($p_preselection) . "
                        AND isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

                    $l_dao = new isys_component_dao($this->m_db);

                    $l_res = $l_dao->retrieve($l_sql);

                    if ($l_res->num_rows() > 1) {
                        while ($l_row = $l_res->get_row()) {
                            // @see  ID-6220  Also return a 'first' selection.
                            $l_preselection['first'][] = $l_row['isys_obj__id'];

                            $l_return['second'][] = [
                                $l_row['isys_catg_ip_list__id'],
                                $l_row['isys_cats_net_ip_addresses_list__title'],
                            ];
                        }
                    }
                }

                return $l_return;

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PRESELECTION:
                // @see  ID-5688  New callback case.
                $preselection = [];

                if (is_array($p_parameters['dataIds']) && count($p_parameters['dataIds'])) {
                    foreach ($p_parameters['dataIds'] as $dataId) {
                        $categoryRow = isys_cmdb_dao_category_g_connector::instance($this->m_db)
                            ->get_data($dataId)
                            ->get_row();

                        $preselection[] = [
                            $categoryRow['isys_catg_connector_list__id'],
                            $categoryRow['isys_catg_connector_list__title'],
                            $categoryRow['isys_obj__title'],
                            $language->get($categoryRow['isys_obj_type__title'])
                        ];
                    }
                }

                return [
                    'header' => [
                        '__checkbox__',
                        $language->get('LC__CATG__IP_ADDRESS'),
                        $language->get('LC__UNIVERSAL__OBJECT_TITLE'),
                        $language->get('LC__UNIVERSAL__OBJECT_TYPE')
                    ],
                    'data'   => $preselection
                ];
        }
    }

    /**
     *
     * @param   integer $p_port_id
     * @param   string  $p_title
     * @param   integer $p_interface_id
     * @param   integer $p_plugtype_id
     * @param   integer $p_porttype_id
     * @param   integer $p_portmode_id
     * @param   integer $p_portspeed
     * @param   integer $p_portspeedID
     * @param   integer $p_duplex_id
     * @param   integer $p_negotiation_id
     * @param   integer $p_standard_id
     * @param   integer $p_net_object
     * @param   string  $p_mac
     * @param   integer $p_active
     * @param   string  $p_description
     * @param   integer $p_connectorID
     * @param   integer $p_cableID
     * @param   string  $p_cable_name
     * @param   integer $p_status
     * @param    array  $p_layer2_objects
     * @param   integer $p_connector_sibling
     * @param   integer $p_hba_id
     * @param   bool    $p_is_master_obj
     * @param   integer $p_default_layer2_id
     * @param   integer $p_mtu
     *
     * @return bool
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function save(
        $p_port_id,
        $p_title,
        $p_interface_id,
        $p_plugtype_id,
        $p_porttype_id,
        $p_portmode_id,
        $p_portspeed,
        $p_portspeedID,
        $p_duplex_id,
        $p_negotiation_id,
        $p_standard_id,
        $p_net_object,
        $p_mac,
        $p_active,
        $p_description,
        $p_connectorID,
        $p_cableID,
        $p_cable_name,
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_layer2_objects = null,
        $p_connector_sibling = null,
        $p_hba_id = null,
        $p_is_master_obj = null,
        $p_default_layer2_id = null,
        $p_mtu = null
    ) {
        if ($p_portspeed > 0) {
            $p_portspeed = isys_convert::speed($p_portspeed, intval($p_portspeedID));
        }

        $success = false;
        $l_connectortype_data = null;

        // Get Connector type for the connector
        if ($p_plugtype_id > 0) {
            $l_plugtype_data = isys_factory_cmdb_dialog_dao::get_instance('isys_connection_type', $this->m_db)
                ->get_data($p_plugtype_id, null);
            $l_connectortype_data = isys_factory_cmdb_dialog_dao::get_instance('isys_connection_type', $this->m_db)
                ->get_data(null, $l_plugtype_data['title']);
        }

        // @see  ID-5665  Format the mac address here, so that all input sources will be handled.
        $p_mac = $this->formatMacAddress($p_mac);

        $l_strSQL = 'UPDATE isys_catg_port_list
            SET isys_catg_port_list__isys_catg_netp_list__id = ' . $this->convert_sql_id($p_interface_id) . ',
            isys_catg_port_list__isys_plug_type__id = ' . $this->convert_sql_id($p_plugtype_id) . ',
            isys_catg_port_list__isys_port_negotiation__id = ' . $this->convert_sql_id($p_negotiation_id) . ',
            isys_catg_port_list__isys_port_standard__id = ' . $this->convert_sql_id($p_standard_id) . ',
            isys_catg_port_list__isys_port_duplex__id = ' . $this->convert_sql_id($p_duplex_id) . ',
            isys_catg_port_list__isys_port_type__id = ' . $this->convert_sql_id($p_porttype_id) . ',
            isys_catg_port_list__isys_port_mode__id = ' . $this->convert_sql_id($p_portmode_id) . ',
            isys_catg_port_list__port_speed_value = ' . $this->convert_sql_text($p_portspeed) . ',
            isys_catg_port_list__isys_port_speed__id = ' . $this->convert_sql_id($p_portspeedID) . ',
            isys_catg_port_list__title = ' . $this->convert_sql_text($p_title) . ',
            isys_catg_port_list__description = ' . $this->convert_sql_text($p_description) . ',
            isys_catg_port_list__mac = ' . $this->convert_sql_text($p_mac) . ',
            isys_catg_port_list__state_enabled = ' . $this->convert_sql_int($p_active) . ',
            isys_catg_port_list__status = ' . $this->convert_sql_int($p_status) . ',
            isys_catg_port_list__isys_catg_hba_list__id = ' . $this->convert_sql_id($p_hba_id) . ',
            isys_catg_port_list__mtu = ' . $this->convert_sql_int($p_mtu) . '
            WHERE isys_catg_port_list__id = ' . $this->convert_sql_id($p_port_id) . ';';

        if ($this->update($l_strSQL) && $this->apply_update()) {

            /* Handle Layer2 Attachments */
            $this->clear_layer2_attachments($p_port_id);
            $this->attach_layer2_net($p_port_id, $p_layer2_objects, $p_default_layer2_id);
            $l_catg__id = $this->get_connector($p_port_id);

            if (!is_numeric($l_catg__id)) {
                throw new isys_exception_cmdb("Error: Your Port has lost its connector reference and is therefore inconsistent. " .
                    "You should remove and recreate it in order to reference any other port.");
            }

            $connectorUpdates = [
                'isys_catg_connector_list__title' => $this->convert_sql_text($p_title)
            ];

            if ($p_connector_sibling > 0) {
                $connectorUpdates['isys_catg_connector_list__isys_catg_connector_list__id'] = $this->convert_sql_id($p_connector_sibling);
            }

            if ($l_connectortype_data) {
                $connectorUpdates['isys_catg_connector_list__isys_connection_type__id'] = $this->convert_sql_id($l_connectortype_data['isys_connection_type__id']);
            }

            $success = isys_cmdb_dao_cable_connection::instance($this->m_db)
                ->handleConnectorUpdate(
                    $l_catg__id,
                    $p_is_master_obj,
                    $p_connectorID,
                    $p_cableID,
                    ($p_cable_name ?: $p_title),
                    $connectorUpdates,
                    'isys_cmdb_dao_category_g_network_port::assigned_connector',
                    $this->getCategoryTitle()
                );
        }

        return $success;
    }

    /**
     * Save global category port element.
     *
     * @param   integer &$p_cat_level       Level to save.
     * @param   integer &$p_intOldRecStatus __status of record before update.
     *
     * @return  integer
     * @throws  isys_exception_dao_cmdb
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus)
    {
        global $g_port_id;

        $l_nPortCount = 1;

        $l_posts = isys_module_request::get_instance()
            ->get_posts();
        $gets =

        $l_nPortID = $l_posts["port_id"];
        $l_nPlugtypeID = $l_posts["C__CATG__PORT__PLUG"];
        $l_PorttypeID = $l_posts["C__CATG__PORT__TYPE"];
        $l_portmode_id = $l_posts["C__CATG__PORT__MODE"];
        $l_nPortspeedID = $l_posts["C__CATG__PORT__SPEED"];
        $l_nPortSpeedValue = $l_posts["C__CATG__PORT__SPEED_VALUE"];
        $l_nDuplexID = $l_posts["C__CATG__PORT__DUPLEX"];
        $l_nNegotiationID = $l_posts["C__CATG__PORT__NEGOTIATION"];
        $l_nStandardID = $l_posts["C__CATG__PORT__STANDARD"];
        $l_cable_name = $l_posts["C__CATG__PORT__DEST__CABLE_NAME"];
        $l_cableID = $l_posts["C__CATG__PORT__CABLE__HIDDEN"];
        $l_layer2_objects = (isys_format_json::is_json_array($l_posts['C__CATG__LAYER2__DEST__HIDDEN'])) ? json_decode($l_posts['C__CATG__LAYER2__DEST__HIDDEN']) : $l_posts['C__CATG__LAYER2__DEST__HIDDEN'];
        $l_nIfaceID = null;
        $l_hba_id = null;
        $isMasterObject = isys_cmdb_dao_category_g_relation::instance(isys_application::instance()->container->get('database'))->get_objtype_configuration($_GET[C__CMDB__GET__OBJECT]);

        if (!empty($l_posts["C__CATG__PORT__INTERFACE"])) {
            $l_interface_type = substr(
                $l_posts["C__CATG__PORT__INTERFACE"],
                strpos($l_posts["C__CATG__PORT__INTERFACE"], '_') + 1,
                strlen($l_posts["C__CATG__PORT__INTERFACE"])
            );
            $l_interface_field_id = substr($l_posts["C__CATG__PORT__INTERFACE"], 0, strpos($l_posts["C__CATG__PORT__INTERFACE"], '_'));

            if (defined($l_interface_type)) {
                switch ($l_interface_type) {
                    case 'C__CATG__HBA':
                        $l_nIfaceID = null;
                        $l_hba_id = $l_interface_field_id;
                        break;
                    case 'C__CATG__NETWORK_INTERFACE':
                    case 'C__CMDB__SUBCAT__NETWORK_INTERFACE_P': // @todo  Remove in i-doit 1.12
                        $l_nIfaceID = $l_interface_field_id;
                        $l_hba_id = null;
                        break;
                }
            }
        }

        // New port or existing?
        if (!is_numeric($l_nPortID) || $l_nPortID <= 0) {
            $l_NewPort = true;

            // Determine how many ports are to be created.
            if (is_numeric($l_posts["C__CATG__PORT__SUFFIX_COUNT"])) {
                if ($l_posts["C__CATG__PORT__SUFFIX_COUNT"] > 1) {
                    $l_nPortCount = $l_posts["C__CATG__PORT__SUFFIX_COUNT"];
                }
            }
        } else {
            $l_NewPort = false;
        }

        if ($l_NewPort) {
            $l_title_arr = isys_smarty_plugin_f_title_suffix_counter::generate_title_as_array($_POST, 'C__CATG__PORT', 'C__CATG__PORT__TITLE');

            for ($i = 0;$l_nPortCount > $i;$i++) {
                $l_title = $l_title_arr[$i];
                $l_nPortID = $this->create(
                    $_GET[C__CMDB__GET__OBJECT],
                    $l_title,
                    $l_nIfaceID,
                    $l_nPlugtypeID,
                    $l_PorttypeID,
                    $l_portmode_id,
                    $l_nPortSpeedValue,
                    $l_nPortspeedID,
                    $l_nDuplexID,
                    $l_nNegotiationID,
                    $l_nStandardID,
                    $l_posts["C__CATG__PORT__NET__HIDDEN"],
                    $l_posts["C__CATG__PORT__MAC"],
                    $l_posts["C__CATG__PORT__ACTIVE"],
                    $l_posts["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                    $l_posts["C__CATG__PORT__DEST__HIDDEN"],
                    $l_cableID,
                    $l_cable_name,
                    C__RECORD_STATUS__NORMAL,
                    $l_layer2_objects,
                    null,
                    $l_hba_id,
                    $isMasterObject,
                    $l_posts['C__CATG__PORT__DEFAULT_VLAN'],
                    $l_posts['C__CATG__PORT__MTU']
                );
            }
            if ($l_nPortID > 0) {
                $l_nRetCode = null;
            }

            $p_cat_level = -1;
        } else {
            try {
                $this->save(
                    $l_nPortID,
                    $l_posts["C__CATG__PORT__TITLE"],
                    $l_nIfaceID,
                    $l_nPlugtypeID,
                    $l_PorttypeID,
                    $l_portmode_id,
                    $l_nPortSpeedValue,
                    $l_nPortspeedID,
                    $l_nDuplexID,
                    $l_nNegotiationID,
                    $l_nStandardID,
                    $l_posts["C__CATG__PORT__NET__HIDDEN"],
                    $l_posts["C__CATG__PORT__MAC"],
                    $l_posts["C__CATG__PORT__ACTIVE"],
                    $l_posts["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                    $l_posts["C__CATG__PORT__DEST__HIDDEN"],
                    $l_cableID,
                    $l_cable_name,
                    C__RECORD_STATUS__NORMAL,
                    $l_layer2_objects,
                    null,
                    $l_hba_id,
                    $isMasterObject,
                    $l_posts['C__CATG__PORT__DEFAULT_VLAN'],
                    $l_posts['C__CATG__PORT__MTU']
                );

                $l_nRetCode = null;
            } catch (isys_exception_dao_cmdb $e) {
                throw $e;
            }
        }

        // IP-Addresses.
        $l_ip_connection = explode(",", $l_posts["C__CATG__PORT__IP_ADDRESS__selected_values"]);

        $this->clear_ip_attachments($l_nPortID);
        if (count($l_ip_connection) > 0) {
            foreach ($l_ip_connection as $l_ip_id) {
                if ($l_ip_id > 0) {
                    $this->clear_ip_attachments(null, $l_ip_id);
                    $this->attach_ip($l_nPortID, $l_ip_id);
                }
            }
        }

        $g_port_id = $l_nPortID;

        return $l_nPortID;
    }

    /**
     * Compares category data for import.
     *
     * @todo Currently, every transformation (using helper methods) are skipped.
     * If your unique properties needs them, implement it!
     *
     * @param  array    $p_category_data_values
     * @param  array    $p_object_category_dataset
     * @param  array    $p_used_properties
     * @param  array    $p_comparison
     * @param  integer  $p_badness
     * @param  integer  $p_mode
     * @param  integer  $p_category_id
     * @param  string   $p_unit_key
     * @param  array    $p_category_data_ids
     * @param  mixed    $p_local_export
     * @param  boolean  $p_dataset_id_changed
     * @param  integer  $p_dataset_id
     * @param  isys_log $p_logger
     * @param  string   $p_category_name
     * @param  string   $p_table
     * @param  mixed    $p_cat_multi
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
        $l_unique_properties = [
            'isys_catg_port_list__mac'   => true,
            'isys_catg_port_list__title' => true
        ];

        $l_title = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['title']['value'];
        $l_mac = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['mac']['value'];

        unset($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['layer2_assignment']['value']);

        $l_mapping = [
            'isys_catg_port_list__title' => $l_title,
            'isys_catg_port_list__mac'   => $l_mac
        ];

        $l_candidate = [];

        // Iterate through local data sets:
        foreach ($p_object_category_dataset as $l_dataset_key => $l_dataset) {
            $p_dataset_id_changed = false;
            $p_dataset_id = $l_dataset[$p_table . '__id'];

            if (isset($p_already_used_data_ids[$p_dataset_id])) {
                // Skip it ID has already been used
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                $p_logger->debug('  Dateset ID "' . $p_dataset_id . '" has already been handled. Skipping to next entry.');
                continue;
            }

            // Test the category data identifier:
            if ($p_mode === isys_import_handler_cmdb::C__USE_IDS && $p_category_data_values['data_id'] !== $p_dataset_id) {
                //$p_logger->debug('Category data identifier is different.');
                $p_badness[$p_dataset_id]++;
                $p_dataset_id_changed = true;

                if ($p_mode === isys_import_handler_cmdb::C__USE_IDS) {
                    continue;
                }
            }

            if ($l_dataset['isys_catg_port_list__title'] == $l_title || (!empty($l_dataset['isys_catg_port_list__mac']) && $l_dataset['isys_catg_port_list__mac'] == $l_mac)) {
                // Check properties
                $p_badness[$p_dataset_id] = 0;
                foreach ($l_mapping as $l_table_key => $l_value) {
                    if ($l_dataset[$l_table_key] != $l_value) {
                        $p_badness[$p_dataset_id]++;
                        if (isset($l_unique_properties[$l_table_key])) {
                            $p_badness[$p_dataset_id] += 1000;
                            $l_candidate[$l_dataset_key] = $p_dataset_id;
                        }
                    }
                }

                if ($p_badness[$p_dataset_id] > isys_import_handler_cmdb::C__COMPARISON__THRESHOLD && $p_badness[$p_dataset_id] > 1000) {
                    //$p_logger->debug('Dataset differs completly from category data.');
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                } elseif ($p_badness[$p_dataset_id] == 0) {
                    // We found our dataset
                    //$p_logger->debug('Dataset and category data are the same.');
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;

                    return;
                } else {
                    //$p_logger->debug('Dataset differs partly from category data.');
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY][$l_dataset_key] = $p_dataset_id;
                }
            } else {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
            }
            // @todo check badness again
        }

        // In case we did not find any matching ports
        if (!isset($p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY]) && !empty($l_candidate)) {
            $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY] = $l_candidate;
        }
    }

    /**
     *
     * @param   integer $p_port_id
     * @param   integer $p_interface_id
     *
     * @return  boolean
     */
    public function attach_interface($p_port_id, $p_interface_id)
    {
        if ($p_port_id > 0 && $p_interface_id > 0) {
            $l_update = 'UPDATE isys_catg_port_list
				SET isys_catg_port_list__isys_catg_netp_list__id = ' . $this->convert_sql_id($p_interface_id) . '
				WHERE isys_catg_port_list__id = ' . $this->convert_sql_id($p_port_id);

            return ($this->update($l_update) && $this->apply_update());
        }

        return false;
    }

    /**
     * Builds an array with minimal requirement for the sync function.
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_import_array($p_data)
    {
        if (!empty($p_data['port_type'])) {
            $l_port_type = isys_import_handler::check_dialog('isys_port_type', $p_data['port_type']);
        } else {
            $l_port_type = null;
        }

        if (!empty($p_data['plug_type'])) {
            $l_plug_type = isys_import_handler::check_dialog('isys_connection_type', $p_data['plug_type']);
        } else {
            $l_plug_type = null;
        }

        if (!empty($p_data['duplex'])) {
            $l_duplex = isys_import_handler::check_dialog('isys_port_duplex', $p_data['duplex']);
        } else {
            $l_duplex = null;
        }

        if (!empty($p_data['negotiation'])) {
            $l_negotiation = isys_import_handler::check_dialog('isys_port_negotiation', $p_data['negotiation']);
        } else {
            $l_negotiation = null;
        }

        if (!empty($p_data['standard'])) {
            $l_standard = isys_import_handler::check_dialog('isys_port_standard', $p_data['standard']);
        } else {
            $l_standard = null;
        }

        if (!is_numeric($p_data['speed_type'])) {
            $l_speed_type = isys_import_handler::check_dialog('isys_port_speed', $p_data['speed_type']);
        } else {
            $l_speed_type = $p_data['speed_type'];
        }

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'title'       => [
                    'value' => $p_data['title']
                ],
                'interface'   => [
                    'value' => $p_data['interface']
                ],
                'plug_type'   => [
                    'value' => $l_plug_type
                ],
                'port_type'   => [
                    'value' => $l_port_type
                ],
                'speed'       => [
                    'value' => $p_data['speed']
                ],
                'speed_type'  => [
                    'value' => $l_speed_type
                ],
                'duplex'      => [
                    'value' => $l_duplex
                ],
                'negotiation' => [
                    'value' => $l_negotiation
                ],
                'standard'    => [
                    'value' => $l_standard
                ],
                'mac'         => [
                    'value' => $p_data['mac']
                ],
                'active'      => [
                    'value' => $p_data['active']
                ],
                'addresses'   => [
                    'value' => $p_data['addresses']
                ],
                'description' => [
                    'value' => $p_data['description']
                ]
            ]
        ];
    }

    /**
     * Function for formatting a MAC address.
     *
     * @param  string $macAddress
     *
     * @see    ID-5665
     * @return string
     */
    private function formatMacAddress($macAddress)
    {
        // We convert all sorts of mac addresses to one "default" form.
        $macAddressRaw = preg_replace('/[^0-9a-fA-F]+/', '', $macAddress);

        if ((mb_strlen($macAddressRaw) === 48 || mb_strlen($macAddressRaw) === 56) && preg_match('/^[01]+$/', $macAddressRaw)) {
            // We got a binary MAC!
            return implode(':', str_split($macAddressRaw, 8));
        }

        if ((mb_strlen($macAddressRaw) === 12 || mb_strlen($macAddressRaw) === 16) && preg_match('/^[0-9a-fA-F]+$/', $macAddressRaw)) {
            // We got a HEX MAC!
            return implode(':', str_split($macAddressRaw, 2));
        }

        return '';
    }
}
