<?php

/**
 * i-doit
 * DAO: global category for fiber channel ports
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_controller_fcport extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'controller_fcport';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__STORAGE_FCPORT';

    /**
     * Category's constant.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CATG__CONTROLLER_FC_PORT';

    /**
     * Category's identifier.
     *
     * @var    integer
     * @fixme  No standard behavior!
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATG__CONTROLLER_FC_PORT;

    /**
     * Category's list DAO.
     *
     * @var  string
     */
    protected $m_list = 'isys_cmdb_dao_list_catg_controller_fcport';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Main table where properties are stored persistently.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_table = 'isys_catg_fc_port_list';

    /**
     * Category's template.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_tpl = 'catg__fc_port.tpl';

    /**
     * Category's user interface.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_ui = 'isys_cmdb_ui_category_g_controller_fc_port';

    /**
     * @param isys_request $p_request
     *
     * @return array
     */
    public function callback_property_connected_controller(isys_request $p_request)
    {
        $l_return = [];
        $l_obj_id = $p_request->get_object_id();

        $l_dao_hba = new isys_cmdb_dao_category_g_hba($this->get_database_component());
        $l_res = $l_dao_hba->get_fc_controllers($l_obj_id);

        while ($l_row = $l_res->get_row()) {
            $l_return[$l_row['isys_catg_hba_list__id']] = $l_row['isys_catg_hba_list__title'];
        }

        return $l_return;
    }

    /**
     * @return mixed
     */
    public function get_subcategory_id()
    {
        return defined_or_default('C__CMDB__SUBCAT__STORAGE__FCPORT');
    }

    /**
     * Return Category Data
     *
     * @param [int $p_id]
     * @param [int $p_obj_id]
     * @param [string $p_condition]
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_strSQL = "SELECT isys_catg_fc_port_list.*, isys_fc_port_type.*, isys_fc_port_medium.*, isys_catg_hba_list.*, isys_obj.*, isys_port_speed.* " .
            ", connected.isys_catg_connector_list__title AS connector_name, connected.isys_catg_connector_list__id AS con_connector, mine.isys_catg_connector_list__isys_catg_relation_list__id AS isys_catg_connector_list__isys_catg_relation_list__id " .
            "FROM isys_catg_fc_port_list LEFT JOIN isys_fc_port_type ON isys_catg_fc_port_list__isys_fc_port_type__id = isys_fc_port_type__id " .
            "LEFT OUTER JOIN isys_fc_port_medium ON isys_catg_fc_port_list__isys_fc_port_medium__id = isys_fc_port_medium__id LEFT JOIN " .
            "isys_catg_hba_list ON isys_catg_fc_port_list__isys_catg_hba_list__id = isys_catg_hba_list__id LEFT JOIN isys_obj ON " .
            "isys_obj__id = isys_catg_fc_port_list__isys_obj__id LEFT JOIN isys_port_speed ON " . "isys_port_speed__id = isys_catg_fc_port_list__isys_port_speed__id " .

            "LEFT JOIN isys_catg_connector_list AS mine ON mine.isys_catg_connector_list__id = isys_catg_fc_port_list__isys_catg_connector_list__id " .
            "LEFT JOIN isys_cable_connection ON mine.isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id " .
            "LEFT JOIN isys_catg_connector_list AS connected ON connected.isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id " .
            "AND (connected.isys_catg_connector_list__id != mine.isys_catg_connector_list__id OR connected.isys_catg_connector_list__id IS NULL) " .

            "WHERE TRUE ";

        $l_strSQL .= $p_condition;

        if (!is_null($p_obj_id)) {
            $l_strSQL .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_status)) {
            $l_strSQL .= "AND isys_catg_fc_port_list__status = " . $this->convert_sql_id($p_status);
        }

        if (!empty($p_catg_list_id)) {
            $l_strSQL .= " AND isys_catg_fc_port_list__id = " . $this->convert_sql_id($p_catg_list_id);
        }

        return $this->retrieve($l_strSQL);
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
                $l_sql = ' AND (isys_catg_fc_port_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_catg_fc_port_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'title'                => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_fc_port_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_fc_port_list__title FROM isys_catg_fc_port_list',
                        'isys_catg_fc_port_list',
                        'isys_catg_fc_port_list__id',
                        'isys_catg_fc_port_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_fc_port_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__CONTROLLER_FC_PORT_TITLE'
                ]
            ]),
            'type'                 => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONTROLLER_FC_PORT_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Type'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_fc_port_list__isys_fc_port_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_fc_port_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_fc_port_type',
                        'isys_fc_port_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_fc_port_type__title FROM isys_catg_fc_port_list
                            INNER JOIN isys_fc_port_type ON isys_fc_port_type__id = isys_catg_fc_port_list__isys_fc_port_type__id',
                        'isys_catg_fc_port_list',
                        'isys_catg_fc_port_list__id',
                        'isys_catg_fc_port_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_fc_port_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_fc_port_list', 'LEFT', 'isys_catg_fc_port_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_fc_port_type',
                            'LEFT',
                            'isys_catg_fc_port_list__isys_fc_port_type__id',
                            'isys_fc_port_type__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__CONTROLLER_FC_PORT_TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_fc_port_type'
                    ]
                ]
            ]),
            'connected_controller' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONTROLLER_FC_CONTROLLER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Connected controller'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_fc_port_list__isys_catg_hba_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_hba_list',
                        'isys_catg_hba_list__id',
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_hba_list__title FROM isys_catg_fc_port_list
                            INNER JOIN isys_catg_hba_list ON isys_catg_hba_list__id = isys_catg_fc_port_list__isys_catg_hba_list__id',
                        'isys_catg_fc_port_list',
                        'isys_catg_fc_port_list__id',
                        'isys_catg_fc_port_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_fc_port_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_fc_port_list', 'LEFT', 'isys_catg_fc_port_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_hba_list',
                            'LEFT',
                            'isys_catg_fc_port_list__isys_catg_hba_list__id',
                            'isys_catg_hba_list__id'
                        )
                    ],
                    C__PROPERTY__DATA__INDEX      => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__CONTROLLER_FC_CONTROLLER',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_controller_fcport',
                            'callback_property_connected_controller'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'fc_port_property_controller'
                    ]
                ]
            ]),
            'connector_sibling'    => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
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
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true
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
            'medium'               => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__STORAGE_FCPORT__MEDIUM',
                    C__PROPERTY__INFO__DESCRIPTION => 'Medium'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_fc_port_list__isys_fc_port_medium__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_fc_port_medium',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_fc_port_medium',
                        'isys_fc_port_medium__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_fc_port_medium__title FROM isys_catg_fc_port_list
                            INNER JOIN isys_fc_port_medium ON isys_fc_port_medium__id = isys_catg_fc_port_list__isys_fc_port_medium__id',
                        'isys_catg_fc_port_list',
                        'isys_catg_fc_port_list__id',
                        'isys_catg_fc_port_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_fc_port_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_fc_port_list', 'LEFT', 'isys_catg_fc_port_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_fc_port_medium',
                            'LEFT',
                            'isys_catg_fc_port_list__isys_fc_port_medium__id',
                            'isys_fc_port_medium__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__CONTROLLER_FC_PORT_MEDIUM',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_fc_port_medium'
                    ]
                ]
            ]),
            'speed'                => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__SPEED',
                    C__PROPERTY__INFO__DESCRIPTION => 'Speed'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_fc_port_list__port_speed',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(ROUND(isys_catg_fc_port_list__port_speed / isys_port_speed__factor), \' \', isys_port_speed__title)
                            FROM isys_catg_fc_port_list
                            INNER JOIN isys_port_speed ON isys_port_speed__id = isys_catg_fc_port_list__isys_port_speed__id',
                        'isys_catg_fc_port_list',
                        'isys_catg_fc_port_list__id',
                        'isys_catg_fc_port_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory(['isys_catg_fc_port_list__port_speed > 0']),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_fc_port_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_fc_port_list', 'LEFT', 'isys_catg_fc_port_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_port_speed',
                            'LEFT',
                            'isys_catg_fc_port_list__isys_port_speed__id',
                            'isys_port_speed__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__FCPORT__SPEED_VALUE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-medium'
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
                    C__PROPERTY__FORMAT__UNIT     => 'speed_unit'
                ]
            ]),
            'speed_unit'           => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__SPEED_UNIT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Speed unit'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_fc_port_list__isys_port_speed__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_port_speed',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_port_speed',
                        'isys_port_speed__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_port_speed__title FROM isys_catg_fc_port_list
                            INNER JOIN isys_port_speed ON isys_port_speed__id = isys_catg_fc_port_list__isys_port_speed__id',
                        'isys_catg_fc_port_list',
                        'isys_catg_fc_port_list__id',
                        'isys_catg_fc_port_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_fc_port_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_fc_port_list', 'LEFT', 'isys_catg_fc_port_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_port_speed',
                            'LEFT',
                            'isys_catg_fc_port_list__isys_port_speed__id',
                            'isys_port_speed__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__FCPORT__SPEED',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'   => 'isys_port_speed',
                        'p_strClass'   => 'input-mini',
                        'p_bDbFieldNN' => 1
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'wwn'                  => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__STORAGE_FCPORT__NODEWWN',
                    C__PROPERTY__INFO__DESCRIPTION => 'Node WWN'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_fc_port_list__wwn',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_fc_port_list__wwn FROM isys_catg_fc_port_list',
                        'isys_catg_fc_port_list',
                        'isys_catg_fc_port_list__id',
                        'isys_catg_fc_port_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_fc_port_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__CONTROLLER_FC_PORT_NODE_WWN'
                ]
            ]),
            'wwpn'                 => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__STORAGE_FCPORT__PORTWWN',
                    C__PROPERTY__INFO__DESCRIPTION => 'Port WW(P)N'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_fc_port_list__wwpn',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_fc_port_list__wwpn FROM isys_catg_fc_port_list',
                        'isys_catg_fc_port_list',
                        'isys_catg_fc_port_list__id',
                        'isys_catg_fc_port_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_fc_port_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__CONTROLLER_FC_PORT_PORT_WWN'
                ]
            ]),
            'san_zones'            => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__FC_PORT__SAN_ZONING',
                    C__PROPERTY__INFO__DESCRIPTION => 'Connection'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_fc_port_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_fc_port_list
                            INNER JOIN isys_san_zoning_fc_port ON isys_san_zoning_fc_port__isys_catg_fc_port_list__id = isys_catg_fc_port_list__id
                            INNER JOIN isys_cats_san_zoning_list ON isys_san_zoning_fc_port__isys_cats_san_zoning_list__id = isys_cats_san_zoning_list__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_cats_san_zoning_list__isys_obj__id',
                        'isys_catg_fc_port_list',
                        'isys_catg_fc_port_list__id',
                        'isys_catg_fc_port_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_fc_port_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_fc_port_list', 'LEFT', 'isys_catg_fc_port_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_san_zoning_fc_port',
                            'LEFT',
                            'isys_catg_fc_port_list__id',
                            'isys_san_zoning_fc_port__isys_catg_fc_port_list__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_san_zoning_list',
                            'LEFT',
                            'isys_cats_san_zoning_list__id',
                            'isys_san_zoning_fc_port__isys_cats_san_zoning_list__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_cats_san_zoning_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__CONTROLLER_FCPORT__SAN_ZONES',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPopupType'  => 'browser_fc_port_san_zoning',
                        'p_strExtraField' => 'C__CATG__CONTROLLER_FC_PORT_NODE_WWN'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__LIST      => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'fc_san'
                    ]
                ]
            ]),
            'target'               => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__NETWORK__TARGET_OBJECT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Target object'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_fc_port_list__isys_catg_connector_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_fc_port_list
                            INNER JOIN isys_catg_connector_list con1 ON con1.isys_catg_connector_list__id = isys_catg_fc_port_list__isys_catg_connector_list__id
                            LEFT JOIN isys_catg_connector_list con2 ON con2.isys_catg_connector_list__isys_cable_connection__id = con1.isys_catg_connector_list__isys_cable_connection__id
                              AND con2.isys_catg_connector_list__id != con1.isys_catg_connector_list__id
                            INNER JOIN isys_obj ON isys_obj__id = con2.isys_catg_connector_list__isys_obj__id',
                        'isys_catg_fc_port_list',
                        'isys_catg_fc_port_list__id',
                        'isys_catg_fc_port_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_fc_port_list__isys_obj__id'])
                    ),
                    // @todo C__PROPERTY__DATA__JOIN
                    /*C__PROPERTY__DATA__JOIN => idoit\Module\Report\SqlQuery\Structure\JoinSubSelect::factory(
                        'SELECT isys_catg_fc_port_list__id AS id, isys_catg_fc_port_list__isys_obj__id AS objectID,
                          isys_obj__title AS title, isys_obj__id AS reference
                        FROM isys_catg_fc_port_list
                        INNER JOIN isys_catg_connector_list con1 ON con1.isys_catg_connector_list__id = isys_catg_fc_port_list__isys_catg_connector_list__id
                        LEFT JOIN isys_catg_connector_list con2 ON con2.isys_catg_connector_list__isys_cable_connection__id = con1.isys_catg_connector_list__isys_cable_connection__id
                          AND con2.isys_catg_connector_list__id != con1.isys_catg_connector_list__id
                        INNER JOIN isys_obj ON isys_obj__id = con2.isys_catg_connector_list__isys_obj__id',
                        'LEFT',
                        [
                            'isys_catg_fc_port_list',
                            'isys_catg_connector_list'
                        ],
                        'isys_catg_fc_port_list__id',
                        'isys_catg_fc_port_list__isys_obj__id'
                    )*/
                ],
                C__PROPERTY__UI => [
                    C__PROPERTY__UI__ID     => 'C__CATG__FCPORT__DEST',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPopupType'  => 'browser_cable_connection_ng',
                        'secondSelection' => true,
                        'secondList'      => 'isys_cmdb_dao_category_g_connector::object_browser'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL    => false,
                    C__PROPERTY__PROVIDES__REPORT     => true,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__SEARCH     => true,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'assigned_connector'
                    ]
                ]
            ]),
            'connector'            => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__STORAGE_CONNECTION_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Connector'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_fc_port_list__isys_catg_connector_list__id',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'connected',
                    C__PROPERTY__DATA__FIELD_ALIAS => 'con_connector',
                    C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT con2.isys_catg_connector_list__title
                            FROM isys_catg_fc_port_list
                            INNER JOIN isys_catg_connector_list con1 ON con1.isys_catg_connector_list__id = isys_catg_fc_port_list__isys_catg_connector_list__id
                            LEFT JOIN isys_catg_connector_list con2 ON con2.isys_catg_connector_list__isys_cable_connection__id = con1.isys_catg_connector_list__isys_cable_connection__id
                              AND con2.isys_catg_connector_list__id != con1.isys_catg_connector_list__id',
                        'isys_catg_fc_port_list',
                        'isys_catg_fc_port_list__id',
                        'isys_catg_fc_port_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_fc_port_list__isys_obj__id'])
                    ),
                    // @todo C__PROPERTY__DATA__JOIN
                    /*C__PROPERTY__DATA__JOIN => idoit\Module\Report\SqlQuery\Structure\JoinSubSelect::factory(
                        'SELECT isys_catg_fc_port_list__id AS id, isys_catg_fc_port_list__isys_obj__id AS objectID,
                          con2.isys_catg_connector_list__title AS title, con2.isys_catg_connector_list__id AS reference
                        FROM isys_catg_fc_port_list
                        INNER JOIN isys_catg_connector_list con1 ON con1.isys_catg_connector_list__id = isys_catg_fc_port_list__isys_catg_connector_list__id
                        LEFT JOIN isys_catg_connector_list con2 ON con2.isys_catg_connector_list__isys_cable_connection__id = con1.isys_catg_connector_list__isys_cable_connection__id
                          AND con2.isys_catg_connector_list__id != con1.isys_catg_connector_list__id
                        ',
                        'LEFT',
                        [
                            'isys_catg_fc_port_list',
                            'isys_catg_connector_list'
                        ],
                        'isys_catg_fc_port_list__id',
                        'isys_catg_fc_port_list__isys_obj__id'
                    )*/
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
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'assigned_connector'
                    ]
                ]
            ]),
            'assigned_connector'   => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONTROLLER_FC_PORT_CONNECTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Connection'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_fc_port_list__isys_catg_connector_list__id',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'connected',
                    C__PROPERTY__DATA__FIELD_ALIAS => 'con_connector',
                    C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_fc_port_list
                            INNER JOIN isys_catg_connector_list con1 ON con1.isys_catg_connector_list__id = isys_catg_fc_port_list__isys_catg_connector_list__id
                            LEFT JOIN isys_catg_connector_list con2 ON con2.isys_catg_connector_list__isys_cable_connection__id = con1.isys_catg_connector_list__isys_cable_connection__id
                              AND con2.isys_catg_connector_list__id != con1.isys_catg_connector_list__id
                            INNER JOIN isys_obj ON isys_obj__id = con2.isys_catg_connector_list__isys_obj__id',
                        'isys_catg_fc_port_list',
                        'isys_catg_fc_port_list__id',
                        'isys_catg_fc_port_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_fc_port_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN        => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_fc_port_list', 'LEFT', 'isys_catg_fc_port_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_connector_list',
                            'INNER',
                            'isys_catg_fc_port_list__isys_catg_connector_list__id',
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
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'INNER', 'isys_catg_connector_list__isys_obj__id', 'isys_obj__id', 'con2')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__FCPORT__DEST',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPopupType'  => 'browser_cable_connection_ng',
                        'secondSelection' => true,
                        'catFilter'       => 'C__CATG__NETWORK;C__CATG__CONTROLLER_FC_PORT;C__CATG__CABLING',
                        'secondList'      => 'isys_cmdb_dao_category_g_connector::object_browser'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__LIST   => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'assigned_connector'
                    ]
                ]
            ]),
            'description'          => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_fc_port_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_fc_port_list__description FROM isys_catg_fc_port_list',
                        'isys_catg_fc_port_list',
                        'isys_catg_fc_port_list__id',
                        'isys_catg_fc_port_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_fc_port_list__isys_obj__id'])
                    ),
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__CONTROLLER_FC_PORT', 'C__CATG__CONTROLLER_FC_PORT')
                ]
            ]),
            'relation_direction'   => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
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
     * Sync method for im- and export.
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  mixed
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;

        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $l_san_zones = $p_category_data['properties']['san_zones'][C__DATA__VALUE];
            $l_is_master_obj = ($p_category_data['properties']['relation_direction'][C__DATA__VALUE]) ? (($p_category_data['properties']['relation_direction'][C__DATA__VALUE] ==
                $p_object_id) ? true : false) : false;
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    $p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        C__RECORD_STATUS__NORMAL,
                        $p_category_data['properties']['connected_controller'][C__DATA__VALUE],
                        $p_category_data['properties']['title'][C__DATA__VALUE],
                        $p_category_data['properties']['type'][C__DATA__VALUE],
                        $p_category_data['properties']['medium'][C__DATA__VALUE],
                        $p_category_data['properties']['speed'][C__DATA__VALUE],
                        $p_category_data['properties']['speed_unit'][C__DATA__VALUE],
                        $p_category_data['properties']['wwn'][C__DATA__VALUE],
                        $p_category_data['properties']['wwpn'][C__DATA__VALUE],
                        $p_category_data['properties']['description'][C__DATA__VALUE],
                        $p_category_data['properties']['connector_sibling'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_connector'][C__DATA__VALUE],
                        null,
                        null,
                        $l_is_master_obj
                    );
                    if ($p_category_data['data_id']) {
                        if (is_array($l_san_zones)) {
                            foreach ($l_san_zones as $l_value) {
                                $this->attach_to_zones($p_category_data['data_id'], [$l_value[C__DATA__VALUE]]);
                                if ($l_value['port_selected']) {
                                    $this->set_san_zone_fc_port_port_selection($p_category_data['data_id'], [$l_value[C__DATA__VALUE]]);
                                }
                                if ($l_value['wwn_selected']) {
                                    $this->set_san_zone_fc_port_wwn_selection($p_category_data['data_id'], [$l_value[C__DATA__VALUE]]);
                                }
                            }
                        }
                        $l_indicator = true;
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save(
                        $p_category_data['data_id'],
                        C__RECORD_STATUS__NORMAL,
                        $p_category_data['properties']['connected_controller'][C__DATA__VALUE],
                        $p_category_data['properties']['title'][C__DATA__VALUE],
                        $p_category_data['properties']['type'][C__DATA__VALUE],
                        $p_category_data['properties']['medium'][C__DATA__VALUE],
                        $p_category_data['properties']['speed'][C__DATA__VALUE],
                        $p_category_data['properties']['speed_unit'][C__DATA__VALUE],
                        $p_category_data['properties']['wwn'][C__DATA__VALUE],
                        $p_category_data['properties']['wwpn'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_connector'][C__DATA__VALUE],
                        null,
                        null,
                        $p_category_data['properties']['description'][C__DATA__VALUE],
                        $p_category_data['properties']['connector_sibling'][C__DATA__VALUE],
                        $l_is_master_obj
                    );
                    if (is_array($l_san_zones)) {
                        $this->detach_from_zones($p_category_data['data_id']);
                        foreach ($l_san_zones as $l_value) {
                            $this->attach_to_zones($p_category_data['data_id'], [$l_value[C__DATA__VALUE]]);
                            if ($l_value['port_selected']) {
                                $this->set_san_zone_fc_port_port_selection($p_category_data['data_id'], [$l_value[C__DATA__VALUE]]);
                            }
                            if ($l_value['wwn_selected']) {
                                $this->set_san_zone_fc_port_wwn_selection($p_category_data['data_id'], [$l_value[C__DATA__VALUE]]);
                            }
                        }
                    }
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Saves a connection
     *
     * @param integer $p_sourcePortID
     * @param integer $p_destPortID
     *
     * @return bool true | false
     */
    public function connection_save($p_sourcePortID, $p_destPortID = null, $p_cableID, $p_master_connector = null)
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

            if (!$l_dao->save_connection($p_sourcePortID, $p_destPortID, $l_conID, $p_master_connector)) {
                return false;
            }
        } catch (Exception $e) {
            isys_application::instance()->container['notify']->error($e->getMessage());
        }

        return true;
    }

    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $isMasterObject = isys_cmdb_dao_category_g_relation::instance(isys_application::instance()->container->get('database'))->get_objtype_configuration($_GET[C__CMDB__GET__OBJECT]);

        if ($p_create) {
            $l_title_arr = isys_smarty_plugin_f_title_suffix_counter::generate_title_as_array($_POST, 'C__CATG__FC_PORT', 'C__CATG__CONTROLLER_FC_PORT_TITLE');
            for ($i = 0;$i < $_POST["C__CATG__FC_PORT__SUFFIX_COUNT"];$i++) {
                $l_title = $l_title_arr[$i];

                $l_id = $this->create(
                    $_GET[C__CMDB__GET__OBJECT],
                    C__RECORD_STATUS__NORMAL,
                    $_POST["C__CATG__CONTROLLER_FC_CONTROLLER"],
                    $l_title,
                    $_POST["C__CATG__CONTROLLER_FC_PORT_TYPE"],
                    $_POST["C__CATG__CONTROLLER_FC_PORT_MEDIUM"],
                    $_POST["C__CATG__FCPORT__SPEED_VALUE"],
                    $_POST["C__CATG__FCPORT__SPEED"],
                    $_POST["C__CATG__CONTROLLER_FC_PORT_NODE_WWN"],
                    $_POST["C__CATG__CONTROLLER_FC_PORT_PORT_WWN"],
                    $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                    null,
                    null,
                    null,
                    null,
                    $isMasterObject
                );

                if ($l_id != false) {
                    $this->m_strLogbookSQL = $this->get_last_query();
                    $this->attach_to_zones($l_id, $_POST["C__CMDB__CATS__SAN_ZONE__HIDDEN"]);

                    if (!empty($_POST["C__CMDB__CATS__SAN_ZONE__SELECTED_FCPORT"])) {
                        $this->set_san_zone_fc_port_port_selection($l_id, $_POST["C__CMDB__CATS__SAN_ZONE__SELECTED_FCPORT"]);
                    }

                    if (!empty($_POST["C__CMDB__CATS__SAN_ZONE__SELECTED_WWN"])) {
                        $this->set_san_zone_fc_port_wwn_selection($l_id, $_POST["C__CMDB__CATS__SAN_ZONE__SELECTED_WWN"]);
                    }
                }
            }

            $p_cat_level = -1;

            return $l_id;
        } else {
            $l_ret = $this->save(
                ($l_id = $_GET[C__CMDB__GET__CATLEVEL]),
                C__RECORD_STATUS__NORMAL,
                $_POST["C__CATG__CONTROLLER_FC_CONTROLLER"],
                $_POST["C__CATG__CONTROLLER_FC_PORT_TITLE"],
                $_POST["C__CATG__CONTROLLER_FC_PORT_TYPE"],
                $_POST["C__CATG__CONTROLLER_FC_PORT_MEDIUM"],
                $_POST["C__CATG__FCPORT__SPEED_VALUE"],
                $_POST["C__CATG__FCPORT__SPEED"],
                $_POST["C__CATG__CONTROLLER_FC_PORT_NODE_WWN"],
                $_POST["C__CATG__CONTROLLER_FC_PORT_PORT_WWN"],
                $_POST["C__CATG__FCPORT__DEST__HIDDEN"],
                $_POST["C__CATG__FCPORT__DEST__CABLE_NAME"],
                $_POST["C__CATG__FCPORT__CABLE__HIDDEN"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                null,
                $isMasterObject
            );

            $this->m_strLogbookSQL = $this->get_last_query();

            $this->detach_from_zones($l_id);
            if (!empty($_POST["C__CMDB__CATS__SAN_ZONE__HIDDEN"])) {
                $this->attach_to_zones($l_id, $_POST["C__CMDB__CATS__SAN_ZONE__HIDDEN"]);

                if (!empty($_POST["C__CMDB__CATS__SAN_ZONE__SELECTED_FCPORT"])) {
                    $this->set_san_zone_fc_port_port_selection($l_id, $_POST["C__CMDB__CATS__SAN_ZONE__SELECTED_FCPORT"]);
                }

                if (!empty($_POST["C__CMDB__CATS__SAN_ZONE__SELECTED_WWN"])) {
                    $this->set_san_zone_fc_port_wwn_selection($l_id, $_POST["C__CMDB__CATS__SAN_ZONE__SELECTED_WWN"]);
                }
            }
        }

        return $l_ret;
    }

    public function set_san_zone_fc_port_port_selection($p_id, $p_selected_zones)
    {
        if (is_array($p_selected_zones)) {
            $l_zone_arr = $p_selected_zones;
        } else {
            $l_zone_arr = isys_format_json::decode($p_selected_zones);
        }

        $l_sql_cond = [];

        if (is_array($l_zone_arr)) {
            foreach ($l_zone_arr as $l_zone_id) {
                if ($l_zone_id > 0) {
                    $l_sql_cond[] = "(isys_san_zoning_fc_port__isys_cats_san_zoning_list__id = " . $this->convert_sql_id($l_zone_id) .
                        " AND isys_san_zoning_fc_port__isys_catg_fc_port_list__id = " . $this->convert_sql_id($p_id) . ")";
                }
            }
        }

        if (count($l_sql_cond) > 0) {
            $l_sql = "UPDATE isys_san_zoning_fc_port SET isys_san_zoning_fc_port__port_selected = 1 WHERE " . implode(' OR ', $l_sql_cond);

            return ($this->update($l_sql) && $this->apply_update());
        }

        return null;
    }

    /**
     * Method for setting FC port WWN's.
     *
     * @param   integer $p_id
     * @param   mixed   $p_selected_zones Might be an PHP or JSON encoded array.
     *
     * @return  mixed
     */
    public function set_san_zone_fc_port_wwn_selection($p_id, $p_selected_zones)
    {
        if (is_array($p_selected_zones)) {
            $l_zone_arr = $p_selected_zones;
        } else {
            $l_zone_arr = isys_format_json::decode($p_selected_zones);
        }

        if (is_array($l_zone_arr) && count($l_zone_arr) > 0) {
            $l_sql_cond = [];

            foreach ($l_zone_arr as $l_zone_id) {
                if ($l_zone_id > 0) {
                    $l_sql_cond[] = "(isys_san_zoning_fc_port__isys_cats_san_zoning_list__id = " . $this->convert_sql_id($l_zone_id) .
                        " AND isys_san_zoning_fc_port__isys_catg_fc_port_list__id = " . $this->convert_sql_id($p_id) . ")";
                }
            }

            if (count($l_sql_cond) > 0) {
                $l_sql = "UPDATE isys_san_zoning_fc_port SET isys_san_zoning_fc_port__wwn_selected = 1 WHERE " . implode(' OR ', $l_sql_cond);

                return ($this->update($l_sql) && $this->apply_update());
            }
        }

        return null;
    }

    /**
     * Method for attaching new zones to a certain category entry.
     *
     * @param   integer $p_id
     * @param   mixed   $p_zones Might be an PHP or JSON encoded array.
     *
     * @return  boolean
     */
    public function attach_to_zones($p_id, $p_zones)
    {
        if (!empty($p_zones)) {
            if (is_array($p_zones)) {
                $l_zones = $p_zones;
            } else {
                $l_zones = isys_format_json::decode($p_zones, true);
            }

            if (is_array($l_zones) && count($l_zones) > 0) {
                $l_sql = "INSERT INTO isys_san_zoning_fc_port (isys_san_zoning_fc_port__id, " . "isys_san_zoning_fc_port__isys_cats_san_zoning_list__id, " .
                    "isys_san_zoning_fc_port__isys_catg_fc_port_list__id) VALUES";

                foreach ($l_zones as $l_zone) {
                    if (!empty($l_zone)) {
                        $l_sql .= "(null, " . $this->convert_sql_id($l_zone) . ", " . $this->convert_sql_id($p_id) . "),";
                    }
                }
                $l_sql = substr($l_sql, 0, -1);

                return ($this->update($l_sql) && $this->apply_update());
            }
        }

        return false;
    }

    /**
     * Method for detaching all connected zones.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function detach_from_zones($p_id)
    {
        $l_sql = 'DELETE FROM isys_san_zoning_fc_port WHERE isys_san_zoning_fc_port__isys_catg_fc_port_list__id = ' . $this->convert_sql_id($p_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Executes the operations neccessary to create an FC port and its corresponding connector for the
     * object referenced by its ID $p_objID.
     *
     * @param   integer $p_objID
     * @param   integer $p_status
     * @param   integer $p_controllerID
     * @param   string  $p_title
     * @param   integer $p_typeID
     * @param   integer $p_mediumID
     * @param   integer $p_speedValue
     * @param   integer $p_speedID
     * @param   string  $p_wwn
     * @param   string  $p_wwpn
     * @param   string  $p_description
     * @param   integer $p_connector_sibling
     *
     * @return  mixed
     */
    public function create(
        $p_objID,
        $p_status,
        $p_controllerID,
        $p_title,
        $p_typeID,
        $p_mediumID,
        $p_speedValue,
        $p_speedID,
        $p_wwn,
        $p_wwpn,
        $p_description,
        $p_connector_sibling = null,
        $p_connector_id = null,
        $p_cable_name = null,
        $p_cableID = null,
        $p_is_master_obj = null
    ) {
        $l_speedValue = isys_convert::speed($p_speedValue, $p_speedID);

        $l_daoConnection = isys_cmdb_dao_category_g_connector::instance($this->m_db);

        $l_connectorID = $l_daoConnection->create($p_objID, C__CONNECTOR__OUTPUT, null, null, $p_title, null, $p_connector_sibling, null, "C__CATG__CONTROLLER_FC_PORT");

        $l_update = 'INSERT INTO isys_catg_fc_port_list
            SET isys_catg_fc_port_list__isys_obj__id = ' . $this->convert_sql_id($p_objID) . ',
            isys_catg_fc_port_list__status = ' . $this->convert_sql_id($p_status) . ',
            isys_catg_fc_port_list__isys_catg_hba_list__id = ' . $this->convert_sql_id($p_controllerID) . ',
            isys_catg_fc_port_list__title = ' . $this->convert_sql_text($p_title) . ',
            isys_catg_fc_port_list__isys_fc_port_type__id = ' . $this->convert_sql_id($p_typeID) . ',
            isys_catg_fc_port_list__isys_fc_port_medium__id = ' . $this->convert_sql_id($p_mediumID) . ',
            isys_catg_fc_port_list__port_speed = ' . $this->convert_sql_text($l_speedValue) . ',
            isys_catg_fc_port_list__isys_port_speed__id = ' . $this->convert_sql_id($p_speedID) . ',
            isys_catg_fc_port_list__wwn = ' . $this->convert_sql_text($p_wwn) . ',
            isys_catg_fc_port_list__wwpn = ' . $this->convert_sql_text($p_wwpn) . ',
            isys_catg_fc_port_list__description = ' . $this->convert_sql_text($p_description) . ',
            isys_catg_fc_port_list__isys_catg_connector_list__id = ' . $this->convert_sql_id($l_connectorID) . ';';

        if ($this->update($l_update) && $this->apply_update()) {
            $l_last_id = $this->get_last_insert_id();
            $l_connectorRearID = $l_connectorID;
            $l_connectorAheadID = $p_connector_id;

            if ($l_connectorAheadID != "") {
                if ($p_is_master_obj) {
                    $l_master_connector = $l_connectorRearID;
                } else {
                    $l_master_connector = $l_connectorAheadID;
                }

                $l_cable_obj_ID = isys_cmdb_dao_cable_connection::add_cable();

                $this->connection_save($l_connectorRearID, $l_connectorAheadID, $l_cable_obj_ID, $l_master_connector);
            }

            return $l_last_id;
        } else {
            return false;
        }
    }

    /**
     * Executes the operations neccessary to update an FC port referenced by its ID $p_catLevel.
     *
     * @param int    $p_catLevel
     * @param int    $p_status
     * @param int    $p_controllerID
     * @param string $p_title
     * @param int    $p_typeID
     * @param int    $p_mediumID
     * @param int    $p_speedValue
     * @param int    $p_speedID
     * @param string $p_wwn
     * @param string $p_wwpn
     * @param int    $p_connector_id
     * @param int    $p_cable_name
     * @param int    $p_cable_id
     * @param string $p_description
     * @param int    $p_connector_sibling
     * @param bool   $p_is_master_obj
     *
     * @return bool
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function save(
        $p_catLevel,
        $p_status,
        $p_controllerID,
        $p_title,
        $p_typeID,
        $p_mediumID,
        $p_speedValue,
        $p_speedID,
        $p_wwn,
        $p_wwpn,
        $p_connector_id,
        $p_cable_name,
        $p_cable_id = null,
        $p_description = "",
        $p_connector_sibling = null,
        $p_is_master_obj = null
    ) {
        $l_speedValue = isys_convert::speed($p_speedValue, $p_speedID);
        $success = false;

        $l_update = 'UPDATE isys_catg_fc_port_list
            SET isys_catg_fc_port_list__status = ' . $this->convert_sql_id($p_status) . ',
            isys_catg_fc_port_list__isys_catg_hba_list__id = ' . $this->convert_sql_id($p_controllerID) . ',
            isys_catg_fc_port_list__title = ' . $this->convert_sql_text($p_title) . ',
            isys_catg_fc_port_list__isys_fc_port_type__id = ' . $this->convert_sql_id($p_typeID) . ',
            isys_catg_fc_port_list__isys_fc_port_medium__id = ' . $this->convert_sql_id($p_mediumID) . ',
            isys_catg_fc_port_list__port_speed = ' . $this->convert_sql_text($l_speedValue) . ',
            isys_catg_fc_port_list__isys_port_speed__id = ' . $this->convert_sql_id($p_speedID) . ',
            isys_catg_fc_port_list__wwn = ' . $this->convert_sql_text($p_wwn) . ',
            isys_catg_fc_port_list__wwpn = ' . $this->convert_sql_text($p_wwpn) . ',
            isys_catg_fc_port_list__description = ' . $this->convert_sql_text($p_description) . '
            WHERE isys_catg_fc_port_list__id = ' . $this->convert_sql_id($p_catLevel) . ';';

        if ($this->update($l_update) && $this->apply_update()) {
            $l_myconnector_id = $this->get_connector($p_catLevel);

            if (!is_numeric($l_myconnector_id)) {
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
                ->handleConnectorUpdate(
                    $l_myconnector_id,
                    $p_is_master_obj,
                    $p_connector_id,
                    $p_cable_id,
                    ($p_cable_name ?: $p_title),
                    $connectorUpdates,
                    'isys_cmdb_dao_category_g_controller_fcport::assigned_connector',
                    $this->getCategoryTitle()
                );
        }

        return $success;
    }

    /**
     * Retrieve the connector of a given FC port.
     *
     * @param   integer $p_cat_id
     *
     * @return  integer
     */
    public function get_connector($p_cat_id)
    {
        $l_query = 'SELECT isys_catg_fc_port_list__isys_catg_connector_list__id FROM isys_catg_fc_port_list WHERE isys_catg_fc_port_list__id = ' .
            $this->convert_sql_id($p_cat_id) . ';';

        return (int)$this->retrieve($l_query)
            ->get_row_value('isys_catg_fc_port_list__isys_catg_connector_list__id');
    }

    /**
     * Retrieve the primary path by a given sanpool ID.
     *
     * @param   integer $p_cat_id
     *
     * @return  integer
     */
    public function get_primary_path($p_cat_id)
    {
        $l_query = 'SELECT isys_catg_sanpool_list__primary_path FROM isys_catg_sanpool_list WHERE isys_catg_sanpool_list__id = ' . $this->convert_sql_id($p_cat_id) . ';';

        return (int)$this->retrieve($l_query)
            ->get_row_value('isys_catg_sanpool_list__primary_path');
    }

    /**
     *
     * @param   integer $p_objtype_id
     * @param   integer $p_fc_port_status
     * @param   integer $p_object_status
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_fc_ports_by_objecttype($p_objtype_id, $p_fc_port_status, $p_object_status = null)
    {
        $l_query = 'SELECT * FROM isys_obj_type INNER JOIN isys_obj ON isys_obj__isys_obj_type__id = isys_obj_type__id ' .
            'WHERE isys_obj__id IN (SELECT isys_catg_fc_port_list__isys_obj__id FROM isys_catg_fc_port_list WHERE isys_obj_type__id = ' .
            $this->convert_sql_id($p_objtype_id) . ')';

        if ($p_fc_port_status) {
            $l_query .= ' AND isys_catg_fc_port_list__status = ' . $this->convert_sql_int($p_fc_port_status);
        }

        if ($p_object_status) {
            $l_query .= ' AND isys_obj__status = ' . $this->convert_sql_int($p_object_status);
        }

        return $this->retrieve($l_query . ';');
    }

    /**
     *
     * @param   integer $p_obj_id
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_fc_ports_by_object($p_obj_id)
    {
        $l_query = 'SELECT * FROM isys_catg_fc_port_list WHERE isys_catg_fc_port_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) .
            ' AND isys_catg_fc_port_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return $this->retrieve($l_query);
    }

    /**
     * Method for preparing the GUI data for category "C__CATS__SAN_ZONING".
     *
     * @param   array $p_obj_ids
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function prepare_data_for_gui(array $p_obj_ids = [], $p_cat_id = null)
    {
        $l_return = [];
        if (is_array($p_obj_ids) && count($p_obj_ids) > 0) {
            // Sometimes happens, that we'll find several objects more than once.
            $p_obj_ids = array_unique($p_obj_ids);

            $l_selected_fcports = $l_selected_wwns = [];
            $l_dao = isys_cmdb_dao::instance($this->m_db);
            $l_san_zoning_dao = isys_cmdb_dao_category_s_san_zoning::instance($this->m_db);

            $l_res = $l_san_zoning_dao->get_assigned_fc_ports($p_cat_id);
            while ($l_row = $l_res->get_row()) {
                $l_selected_fcports[] = $l_row["isys_san_zoning_fc_port__isys_catg_fc_port_list__id"];
            }

            $l_res = $l_san_zoning_dao->get_assigned_wwns($p_cat_id);
            while ($l_row = $l_res->get_row()) {
                $l_selected_wwns[] = $l_row["isys_san_zoning_fc_port__isys_catg_fc_port_list__id"];
            }

            $p_obj_ids = array_unique($p_obj_ids);

            foreach ($p_obj_ids as $l_obj_id) {
                $l_fc_res = isys_cmdb_dao_category_g_controller_fcport::instance($this->m_db)
                    ->get_data(null, $l_obj_id);

                if ($l_fc_res->num_rows() > 0) {
                    while ($l_fc_row = $l_fc_res->get_row()) {
                        $l_return[$l_fc_row['isys_obj__id']][] = [
                            'obj_title'        => $l_fc_row['isys_obj__title'],
                            'obj_type_title'   => isys_application::instance()->container->get('language')
                                ->get($l_dao->get_objtype_name_by_id_as_string($l_fc_row['isys_obj__isys_obj_type__id'])),
                            'fc_port_id'       => $l_fc_row['isys_catg_fc_port_list__id'],
                            'fc_port_title'    => $l_fc_row['isys_catg_fc_port_list__title'],
                            'fc_port_selected' => in_array($l_fc_row['isys_catg_fc_port_list__id'], $l_selected_fcports),
                            'wwn_title'        => $l_fc_row['isys_catg_fc_port_list__wwn'],
                            'wwn_selected'     => in_array($l_fc_row['isys_catg_fc_port_list__id'], $l_selected_wwns),
                            'wwn_available'    => !empty($l_fc_row['isys_catg_fc_port_list__wwn'])
                        ];
                    }
                }
            }
        }

        return $l_return;
    }

    /**
     * Compares category data for import.
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
        $l_title = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['title']['value'];

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

            if ($l_dataset['isys_catg_fc_port_list__title'] == $l_title) {
                // Check properties
                // We found our dataset
                //$p_logger->debug('Dataset and category data are the same.');
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;

                return;
            } else {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
            }
        }
    }
}
