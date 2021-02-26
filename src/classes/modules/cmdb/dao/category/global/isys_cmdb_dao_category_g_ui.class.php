<?php

/**
 * i-doit
 *
 * DAO: global category for universal interfaces
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @version     Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_ui extends isys_cmdb_dao_category_global
{

    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'ui';

    /**
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__UNIVERSAL_INTERFACE';

    /**
     * Category's constant
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CATG__UNIVERSAL_INTERFACE';

    /**
     * Category's identifier
     *
     * @var    integer
     * @fixme  No standard behavior!
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATG__UNIVERSAL_INTERFACE;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Return Category Data
     *
     * @param [int $p_id]h
     * @param [int $p_obj_id]
     * @param [string $p_condition]
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT isys_obj.*, isys_ui_con_type.*, isys_ui_plugtype.*, isys_catg_ui_list.* " .
            ",connected.isys_catg_connector_list__title AS connector_name, connected.isys_catg_connector_list__id AS con_connector " . ",mine.isys_catg_connector_list__isys_catg_relation_list__id AS isys_catg_connector_list__isys_catg_relation_list__id FROM isys_catg_ui_list
            INNER JOIN isys_obj ON isys_catg_ui_list__isys_obj__id = isys_obj__id
            LEFT JOIN isys_ui_con_type ON isys_ui_con_type__id = isys_catg_ui_list__isys_ui_con_type__id
            LEFT JOIN isys_ui_plugtype ON isys_ui_plugtype__id = isys_catg_ui_list__isys_ui_plugtype__id
            LEFT JOIN isys_catg_connector_list AS mine ON mine.isys_catg_connector_list__id = isys_catg_ui_list__isys_catg_connector_list__id
            LEFT JOIN isys_cable_connection ON mine.isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id
            LEFT JOIN isys_catg_connector_list AS connected ON connected.isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id
            AND (connected.isys_catg_connector_list__id != mine.isys_catg_connector_list__id OR connected.isys_catg_connector_list__id IS NULL)
            WHERE TRUE ";

        $l_sql .= $p_condition;

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_catg_list_id)) {
            $l_sql .= " AND (isys_catg_ui_list__id = " . $this->convert_sql_id($p_catg_list_id) . ")";
        }

        if (!empty($p_status)) {
            $l_sql .= " AND (isys_catg_ui_list__status = '{$p_status}')";
        }

        return $this->retrieve($l_sql);
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
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_ui_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_ui_list__title FROM isys_catg_ui_list',
                        'isys_catg_ui_list',
                        'isys_catg_ui_list__id',
                        'isys_catg_ui_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_ui_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__UI_TITLE'
                ]
            ]),
            'type'               => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__UI_CONNECTION_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Connectiontype'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_ui_list__isys_ui_con_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_ui_con_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_ui_con_type',
                        'isys_ui_con_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_ui_con_type__title
                            FROM isys_catg_ui_list
                            INNER JOIN isys_ui_con_type ON isys_ui_con_type__id = isys_catg_ui_list__isys_ui_con_type__id',
                        'isys_catg_ui_list',
                        'isys_catg_ui_list__id',
                        'isys_catg_ui_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_ui_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_ui_list', 'LEFT', 'isys_catg_ui_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_ui_con_type',
                            'LEFT',
                            'isys_catg_ui_list__isys_ui_con_type__id',
                            'isys_ui_con_type__id'
                        )
                    ]
                    /*
                    C__PROPERTY__DATA__JOIN => idoit\Module\Report\SqlQuery\Structure\JoinSubSelect::factory(
                        'SELECT isys_catg_ui_list__id AS id, isys_catg_ui_list__isys_obj__id AS objectID,
                        isys_ui_con_type__title AS title, isys_ui_con_type__id AS reference
                        FROM isys_catg_ui_list
                        INNER JOIN isys_ui_con_type ON isys_ui_con_type__id = isys_catg_ui_list__isys_ui_con_type__id',
                        'LEFT',
                        [
                            'isys_catg_ui_list',
                            'isys_ui_con_type'
                        ],
                        'isys_catg_ui_list__id',
                        'isys_catg_ui_list__isys_obj__id'
                    )*/
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__UI_CONNECTION_TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_ui_con_type'
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
            'plug'               => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__UI_PLUG_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Plug type'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_ui_list__isys_ui_plugtype__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_ui_plugtype',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_ui_plugtype',
                        'isys_ui_plugtype__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_ui_plugtype__title
                            FROM isys_catg_ui_list
                            INNER JOIN isys_ui_plugtype ON isys_ui_plugtype__id = isys_catg_ui_list__isys_ui_plugtype__id',
                        'isys_catg_ui_list',
                        'isys_catg_ui_list__id',
                        'isys_catg_ui_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_ui_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_ui_list', 'LEFT', 'isys_catg_ui_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_ui_plugtype',
                            'LEFT',
                            'isys_catg_ui_list__isys_ui_plugtype__id',
                            'isys_ui_plugtype__id'
                        )
                    ]
                    /*
                    C__PROPERTY__DATA__JOIN => idoit\Module\Report\SqlQuery\Structure\JoinSubSelect::factory(
                        'SELECT isys_catg_ui_list__id AS id, isys_catg_ui_list__isys_obj__id AS objectID,
                        isys_ui_plugtype__title AS title, isys_ui_plugtype__id AS reference
                        FROM isys_catg_ui_list
                        INNER JOIN isys_ui_plugtype ON isys_ui_plugtype__id = isys_catg_ui_list__isys_ui_plugtype__id',
                        'LEFT',
                        [
                            'isys_catg_ui_list',
                            'isys_ui_plugtype'
                        ],
                        'isys_catg_ui_list__id',
                        'isys_catg_ui_list__isys_obj__id'
                    )*/
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__UI_PLUG_TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_ui_plugtype'
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
            'assigned_connector' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__UI_ASSIGNED_UI',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned to connector'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_ui_list__isys_catg_connector_list__id',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'connected',
                    C__PROPERTY__DATA__FIELD_ALIAS => 'con_connector',
                    C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_ui_list
                            INNER JOIN isys_catg_connector_list con1 ON con1.isys_catg_connector_list__id = isys_catg_ui_list__isys_catg_connector_list__id
                            LEFT JOIN isys_catg_connector_list con2 ON con2.isys_catg_connector_list__isys_cable_connection__id = con1.isys_catg_connector_list__isys_cable_connection__id
                              AND con2.isys_catg_connector_list__id != con1.isys_catg_connector_list__id
                            INNER JOIN isys_obj ON isys_obj__id = con2.isys_catg_connector_list__isys_obj__id',
                        'isys_catg_ui_list',
                        'isys_catg_ui_list__id',
                        'isys_catg_ui_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_ui_list__isys_obj__id'])
                    )
                    /*
                    C__PROPERTY__DATA__JOIN => idoit\Module\Report\SqlQuery\Structure\JoinSubSelect::factory(
                        'SELECT isys_catg_ui_list__id AS id, isys_catg_ui_list__isys_obj__id AS objectID,
                          isys_obj__title AS title, isys_obj__id AS reference
                        FROM isys_catg_ui_list
                        INNER JOIN isys_catg_connector_list con1 ON con1.isys_catg_connector_list__id = isys_catg_ui_list__isys_catg_connector_list__id
                        LEFT JOIN isys_catg_connector_list con2 ON con2.isys_catg_connector_list__isys_cable_connection__id = con1.isys_catg_connector_list__isys_cable_connection__id
                          AND con2.isys_catg_connector_list__id != con1.isys_catg_connector_list__id
                        INNER JOIN isys_obj ON isys_obj__id = con2.isys_catg_connector_list__isys_obj__id',
                        'LEFT',
                        [
                            'isys_catg_ui_list',
                            'isys_catg_connector_list'
                        ],
                        'isys_catg_ui_list__id',
                        'isys_catg_ui_list__isys_obj__id'
                    )*/
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__UI__ASSIGNED_UI',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPopupType'                                  => 'browser_cable_connection_ng',
                        isys_popup_browser_object_ng::C__SECOND_SELECTION => true,
                        isys_popup_browser_object_ng::C__CAT_FILTER       => 'C__CATG__NETWORK;C__CATG__CONTROLLER_FC_PORT;C__CATG__CABLING',
                        isys_popup_browser_object_ng::C__SECOND_LIST      => 'isys_cmdb_dao_category_g_connector::object_browser',
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH  => false,
                    C__PROPERTY__PROVIDES__REPORT  => true,
                    C__PROPERTY__PROVIDES__VIRTUAL => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'assigned_connector'
                    ]
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__VALIDATION => false
                ]
            ]),
            'connector'          => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONNECTOR__ASSIGNED_CONNECTOR',
                    C__PROPERTY__INFO__DESCRIPTION => 'Connector'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_ui_list__isys_catg_connector_list__id',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'connected',
                    C__PROPERTY__DATA__FIELD_ALIAS => 'con_connector',
                    C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(con2.isys_catg_connector_list__title)
                            FROM isys_catg_ui_list
                            INNER JOIN isys_catg_connector_list con1 ON con1.isys_catg_connector_list__id = isys_catg_ui_list__isys_catg_connector_list__id
                            LEFT JOIN isys_catg_connector_list con2 ON con2.isys_catg_connector_list__isys_cable_connection__id = con1.isys_catg_connector_list__isys_cable_connection__id
                              AND con2.isys_catg_connector_list__id != con1.isys_catg_connector_list__id
                            INNER JOIN isys_obj ON isys_obj__id = con2.isys_catg_connector_list__isys_obj__id',
                        'isys_catg_ui_list',
                        'isys_catg_ui_list__id',
                        'isys_catg_ui_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_ui_list__isys_obj__id'])
                    )
                    /*
                    C__PROPERTY__DATA__JOIN => idoit\Module\Report\SqlQuery\Structure\JoinSubSelect::factory(
                        'SELECT isys_catg_ui_list__id AS id, isys_catg_ui_list__isys_obj__id AS objectID,
                          con2.isys_catg_connector_list__title AS title, con2.isys_catg_connector_list__id AS reference
                        FROM isys_catg_ui_list
                        INNER JOIN isys_catg_connector_list con1 ON con1.isys_catg_connector_list__id = isys_catg_ui_list__isys_catg_connector_list__id
                        LEFT JOIN isys_catg_connector_list con2 ON con2.isys_catg_connector_list__isys_cable_connection__id = con1.isys_catg_connector_list__isys_cable_connection__id
                          AND con2.isys_catg_connector_list__id != con1.isys_catg_connector_list__id
                        ',
                        'LEFT',
                        [
                            'isys_catg_ui_list',
                            'isys_catg_connector_list'
                        ],
                        'isys_catg_ui_list__id',
                        'isys_catg_ui_list__isys_obj__id'
                    )*/
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
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__VALIDATION => false
                ]
            ]),
            'description'        => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_ui_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_ui_list__description FROM isys_catg_ui_list',
                        'isys_catg_ui_list',
                        'isys_catg_ui_list__id',
                        'isys_catg_ui_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_ui_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__UNIVERSAL_INTERFACE', 'C__CATG__UNIVERSAL_INTERFACE'),
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
                    C__PROPERTY__PROVIDES__EXPORT     => true
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

    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $this->m_sync_catg_data = $p_category_data;
            $l_is_master_obj = ($this->get_property('relation_direction')) ? (($this->get_property('relation_direction') == $p_object_id) ? true : false) : false;
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if (($p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        C__RECORD_STATUS__NORMAL,
                        $this->get_property('title'),
                        $this->get_property('type'),
                        $this->get_property('plug'),
                        $this->get_property('assigned_connector'),
                        null,
                        null,
                        $this->get_property('description'),
                        $this->get_property('connector_sibling'),
                        $l_is_master_obj
                    ))) {
                        $l_indicator = true;
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save(
                        $p_category_data['data_id'],
                        C__RECORD_STATUS__NORMAL,
                        $this->get_property('title'),
                        $this->get_property('type'),
                        $this->get_property('plug'),
                        $this->get_property('assigned_connector'),
                        null,
                        null,
                        $this->get_property('description'),
                        $this->get_property('connector_sibling'),
                        $l_is_master_obj
                    );
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Import-Handler for category memory
     *
     * @author Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function import($p_data)
    {
        if (is_array($p_data)) {
            foreach ($p_data as $l_key => $l_ui) {
                foreach ($l_ui as $l_data) {
                    $l_title = $l_data["name"];
                    $l_description = null;

                    $l_con_type = -1;
                    $l_plug_type = isys_import::check_dialog("isys_ui_plugtype", "LC__UNIVERSAL__OTHER");

                    switch ($l_key) {
                        case C__IMPORT__UI__MOUSE:
                            $l_con_type = isys_import::check_dialog("isys_ui_con_type", "LC__UI_CON_TYPE__MOUSE");
                            break;
                        case C__IMPORT__UI__KEYBOARD:
                            $l_con_type = isys_import::check_dialog("isys_ui_con_type", "LC__UI_CON_TYPE__KEYBOARD");
                            break;
                        case C__IMPORT__UI__MONITOR:
                            $l_con_type = isys_import::check_dialog("isys_ui_con_type", "Monitor");
                            break;
                        case C__IMPORT__UI__PRINTER:
                            $l_con_type = isys_import::check_dialog("isys_ui_con_type", "LC__UI_CON_TYPE__PRINTER");
                            $l_shared = ($l_data["shared"] == "Wahr" || $l_data["shared"] == "True") ? "Yes" : "No";
                            $l_local = ($l_data["local"] == "Wahr" || $l_data["local"] == "True") ? "Yes" : "No";
                            $l_default = ($l_data["default"] == "Wahr" || $l_data["default"] == "True") ? "Yes" : "No";

                            $l_description = "Location: " . $l_data["location"] . "\n" . "Default: " . $l_default . "\n" . "Shared: " . $l_shared . " (" .
                                $l_data["sharename"] . ")\n" . "Local: " . $l_local . "\n" . "Driver: " . $l_data["driver"];

                            break;
                    }

                    // Create it.
                    $this->create(
                        $_GET[C__CMDB__GET__OBJECT],
                        C__RECORD_STATUS__NORMAL,
                        $l_title,
                        $l_con_type,
                        $l_plug_type,
                        null,
                        null,
                        null,
                        $l_description
                    );
                }
            }

            return true;
        }
    }

    /**
     * Save global category odep element
     *
     * @param $p_cat_level        level to save, default 0
     * @param &$p_intOldRecStatus __status of record before update
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_intErrorCode = -1; // ErrorCode

        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_catg_ui_list__status"];

        if ($p_create) {
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__UI_TITLE'],
                $_POST['C__CATG__UI_CONNECTION_TYPE'],
                $_POST['C__CATG__UI_PLUG_TYPE'],
                $_POST['C__CATG__UI__ASSIGNED_UI__HIDDEN'],
                $_POST['C__CATG__UI__ASSIGNED_UI__CABLE_NAME'],
                $_POST['C__CATG__UI__ASSIGNED_CABLE__HIDDEN'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            if ($l_id != false) {
                $this->m_strLogbookSQL = $this->get_last_query();
                $p_cat_level = -1;
            }

            return $l_id;
        } else {
            $l_bRet = $this->save(
                $l_catdata["isys_catg_ui_list__id"],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__UI_TITLE'],
                $_POST['C__CATG__UI_CONNECTION_TYPE'],
                $_POST['C__CATG__UI_PLUG_TYPE'],
                $_POST['C__CATG__UI__ASSIGNED_UI__HIDDEN'],
                $_POST['C__CATG__UI__ASSIGNED_UI__CABLE_NAME'],
                $_POST['C__CATG__UI__ASSIGNED_CABLE__HIDDEN'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        return $l_bRet == true ? null : $l_intErrorCode;
    }

    /**
     * Executes the operations to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_newRecStatus
     * @param   string  $p_title
     * @param   integer $p_conTypeID
     * @param   integer $p_plugTypeID
     * @param   integer $p_connectorAheadID
     * @param   string  $p_cableName
     * @param   integer $p_cableID
     * @param   string  $p_description
     *
     * @return  boolean
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     */
    public function save(
        $p_cat_level,
        $p_newRecStatus,
        $p_title,
        $p_conTypeID,
        $p_plugTypeID,
        $p_connectorAheadID,
        $p_cableName = null,
        $p_cableID = null,
        $p_description = "",
        $p_connector_sibling = null,
        $p_is_master_obj = null
    ) {
        $success = false;
        $l_update = "UPDATE isys_catg_ui_list SET " . "isys_catg_ui_list__title = " . $this->convert_sql_text($p_title) . ", " . "isys_catg_ui_list__isys_ui_con_type__id = " .
            $this->convert_sql_id($p_conTypeID) . ", " . "isys_catg_ui_list__isys_ui_plugtype__id = " . $this->convert_sql_id($p_plugTypeID) . ", " .
            "isys_catg_ui_list__description = " . $this->convert_sql_text($p_description) . ", " . "isys_catg_ui_list__status = " . $this->convert_sql_id($p_newRecStatus) .
            " " . "WHERE isys_catg_ui_list__id = " . $this->convert_sql_id($p_cat_level);

        if ($this->update($l_update) && $this->apply_update()) {
            $connectorId = $this->get_connector($p_cat_level);

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
                ->handleConnectorUpdate(
                    $connectorId,
                    $p_is_master_obj,
                    $p_connectorAheadID,
                    $p_cableID,
                    ($p_cableName ?: $p_title),
                    $connectorUpdates,
                    'isys_cmdb_dao_category_g_ui::assigned_connector',
                    $this->getCategoryTitle()
                );
        }

        return $success;
    }

    public function get_connector($p_port_id)
    {
        $l_query = "SELECT isys_catg_ui_list__isys_catg_connector_list__id AS con " . "FROM isys_catg_ui_list " . "WHERE isys_catg_ui_list__id = " .
            $this->convert_sql_id($p_port_id);

        return $this->retrieve($l_query)
            ->get_row_value('con');
    }

    /**
     * Executes the query to create the category entry referenced by isys_catg_memory__id $p_fk_id.
     *
     * @param   integer $p_object_id
     * @param   integer $p_newRecStatus
     * @param   string  $p_title
     * @param   integer $p_conTypeID
     * @param   integer $p_plugTypeID
     * @param   integer $p_connectionID
     * @param   string  $p_cableName
     * @param   integer $p_cableID
     * @param   string  $p_description
     *
     * @return  mixed
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     */
    public function create(
        $p_object_id,
        $p_newRecStatus,
        $p_title,
        $p_conTypeID,
        $p_plugTypeID,
        $p_connectionID,
        $p_cableName = null,
        $p_cableID = null,
        $p_description = "",
        $p_connector_sibling = null,
        $p_is_master_obj = null
    ) {
        $l_daoConnection = isys_cmdb_dao_category_g_connector::instance($this->m_db);

        $l_connectorID = $l_daoConnection->create(
            $p_object_id,
            C__CONNECTOR__OUTPUT,
            null,
            null,
            $p_title,
            null,
            $p_connector_sibling,
            null,
            "C__CATG__UNIVERSAL_INTERFACE",
            $p_cableID
        );

        if ($p_connectionID != null) {
            $l_connectorRearID = $l_connectorID;
            $l_connectorAheadID = $p_connectionID;

            if ($p_is_master_obj) {
                $l_master_connector = $l_connectorRearID;
            } else {
                $l_master_connector = $l_connectorAheadID;
            }

            $l_dao = new isys_cmdb_dao_cable_connection($this->m_db);

            $l_dao->delete_cable_connection($l_dao->get_cable_connection_id_by_connector_id($p_connectionID));

            if (empty($p_cableID)) {
                $p_cableID = isys_cmdb_dao_cable_connection::recycle_cable($p_cableName);
            }

            $l_conID = $l_dao->add_cable_connection($p_cableID);

            if (!$l_dao->save_connection($l_connectorID, $p_connectionID, $l_conID, $l_master_connector)) {
                return false;
            }
        }

        $l_update = "INSERT INTO isys_catg_ui_list SET " . "isys_catg_ui_list__title = " . $this->convert_sql_text($p_title) . ", " .
            "isys_catg_ui_list__isys_ui_con_type__id = " . $this->convert_sql_id($p_conTypeID) . ", " . "isys_catg_ui_list__isys_ui_plugtype__id = " .
            $this->convert_sql_id($p_plugTypeID) . ", " . "isys_catg_ui_list__isys_catg_connector_list__id = " . $this->convert_sql_id($l_connectorID) . ", " .
            "isys_catg_ui_list__description = " . $this->convert_sql_text($p_description) . ", " . "isys_catg_ui_list__status = " . $this->convert_sql_id($p_newRecStatus) .
            ", " . "isys_catg_ui_list__isys_obj__id = " . $this->convert_sql_id($p_object_id);

        if ($this->update($l_update) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    public function pre_rank($p_entry_id, $p_direction, $p_table = null)
    {
        $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);
        $l_dao_connector = new isys_cmdb_dao_category_g_connector($this->m_db);

        // Get entry
        $l_res = $this->retrieve("SELECT isys_catg_ui_list__status, isys_catg_ui_list__isys_catg_connector_list__id " .
            "FROM isys_catg_ui_list WHERE isys_catg_ui_list__id = " . $this->convert_sql_id($p_entry_id));
        $l_row = $l_res->get_row();

        $l_go = true;

        // Get connector data
        $l_data = $l_dao_connector->get_data($l_row[$p_table . "__isys_catg_connector_list__id"])
            ->__to_array();

        // Get relation objects id
        $l_relation_id = $l_data["isys_catg_connector_list__isys_catg_relation_list__id"];
        $l_relation_object = $this->get_object_id_by_category_id($l_relation_id, "isys_catg_relation_list");

        switch ($p_direction) {
            case C__CMDB__RANK__DIRECTION_DELETE:
                switch ($l_row["isys_catg_ui_list__status"]) {
                    case C__RECORD_STATUS__BIRTH:
                        $l_record_status = C__RECORD_STATUS__DELETED;
                        break;
                    case C__RECORD_STATUS__NORMAL:
                        $l_record_status = C__RECORD_STATUS__ARCHIVED;
                        break;
                    case C__RECORD_STATUS__ARCHIVED:
                        $l_record_status = C__RECORD_STATUS__DELETED;
                        break;
                    case C__RECORD_STATUS__DELETED:
                        /**
                         * Delete cable connection and connector on purge
                         */
                        $l_dao = new isys_cmdb_dao_cable_connection($this->m_db);
                        $l_cableConID = $l_dao->get_cable_connection_id_by_connector_id($l_row["isys_catg_ui_list__isys_catg_connector_list__id"]);

                        $l_record_status = C__RECORD_STATUS__PURGE;

                        $l_dao->delete_cable_connection($l_cableConID);
                        $l_dao->delete_connector($l_row["isys_catg_ui_list__isys_catg_connector_list__id"]);
                        break;
                    default:
                        $l_go = false;
                }
                break;

            case C__CMDB__RANK__DIRECTION_RECYCLE:
                switch ($l_row["isys_catg_ui_list__status"]) {
                    case C__RECORD_STATUS__ARCHIVED:
                        $l_record_status = C__RECORD_STATUS__NORMAL;
                        break;
                    case C__RECORD_STATUS__DELETED:
                        $l_record_status = C__RECORD_STATUS__ARCHIVED;
                        break;
                    default:
                        $l_go = false;
                }
                break;
        }

        if ($l_go) {
            if (!empty($l_relation_object) && $l_relation_object > 0) {
                if ($l_record_status == C__RECORD_STATUS__PURGE) {
                    $l_dao_relation->delete_object($l_relation_object);
                } else {
                    $l_dao_relation->set_object_status($l_relation_object, $l_record_status);
                }
            }

            return true;
        } else {
            return true;
        }
    }

    /**
     * return array to fill select box of all available uis
     *
     * @param $p_const_objType (optional) return list of specified obj_type, if NULL all obj
     *
     * @return array
     *
     */
    public function get_smarty_arr_available_ui($p_act_ui_id = null)
    {
        $l_daoRes = $this->get_available_ui_by_ui($p_act_ui_id);
        if ($l_daoRes != null) {
            while ($l_rec = $l_daoRes->get_row()) {
                $l_arr[$l_rec['isys_catg_ui_list__id']] = $l_rec['isys_obj__title'] . " > " . $l_rec['isys_catg_ui_list__title'];
            }
        }

        return $l_arr;
    }

    /**
     * Builds an array with minimal requirements for the sync function
     *
     * @param $p_data
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_import_array($p_data)
    {
        if (!empty($p_data['type'])) {
            $l_con_type = isys_import_handler::check_dialog('isys_ui_con_type', $p_data['type']);
        } else {
            $l_con_type = null;
        }

        if (!empty($p_data['plug'])) {
            $l_plug_type = isys_import_handler::check_dialog('isys_ui_plugtype', $p_data['plug']);
        } else {
            $l_plug_type = null;
        }

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'title'       => [
                    'value' => $p_data['title']
                ],
                'type'        => [
                    'value' => $l_con_type
                ],
                'plug'        => [
                    'value' => $l_plug_type
                ],
                'description' => [
                    'value' => $p_data['description']
                ]

            ]
        ];
    }
}
