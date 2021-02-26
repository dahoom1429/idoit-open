<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\FloatProperty;
use idoit\Component\Property\Type\ObjectBrowserSecondListProperty;
use idoit\Component\Property\Type\TextProperty;

/**
 * i-doit
 *
 * DAO: global category power consumers
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_power_consumer extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'power_consumer';

    protected $categoryTitle = 'LC__CMDB__CATG__POWER_CONSUMER';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  bool
     */
    protected $m_multivalued = true;

    /**
     * Main table where properties are stored persistently.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_table = 'isys_catg_pc_list';

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   mixed   $p_obj_id May be an integer or an array.
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT *,
              output.isys_catg_connector_list__isys_obj__id AS output_obj_id,
              output.isys_catg_connector_list__title AS connector_name,
              output.isys_catg_connector_list__id AS con_connector
            FROM isys_catg_pc_list
            LEFT JOIN isys_obj ON isys_obj__id = isys_catg_pc_list__isys_obj__id
            LEFT JOIN isys_catg_connector_list AS input ON isys_catg_connector_list__id = isys_catg_pc_list__isys_catg_connector_list__id
            LEFT JOIN isys_catg_connector_list AS output ON output.isys_catg_connector_list__isys_cable_connection__id = input.isys_catg_connector_list__isys_cable_connection__id
              AND output.isys_catg_connector_list__id != input.isys_catg_connector_list__id
            LEFT JOIN isys_pc_model ON isys_pc_model__id = isys_catg_pc_list__isys_pc_model__id
            LEFT JOIN isys_pc_manufacturer ON isys_pc_manufacturer__id = isys_catg_pc_list__isys_pc_manufacturer__id
            WHERE TRUE " . $p_condition . " ";

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= " AND isys_catg_pc_list__id = " . $this->convert_sql_id($p_catg_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_catg_pc_list__status = " . $this->convert_sql_id($p_status);
        }

        return $this->retrieve($l_sql);
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
                $l_sql = ' AND (isys_catg_pc_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_catg_pc_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
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
            'title'              => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_pc_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_pc_list__title FROM isys_catg_pc_list',
                        'isys_catg_pc_list',
                        'isys_catg_pc_list__id',
                        'isys_catg_pc_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_pc_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__POWER_CONSUMER__TITLE'
                ]
            ]),
            'active'             => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__ACTIVE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Active'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_pc_list__active',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_catg_pc_list__active = \'1\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__YES') . '
                        	    WHEN isys_catg_pc_list__active = \'0\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__NO') . ' END)
                             FROM isys_catg_pc_list',
                        'isys_catg_pc_list',
                        'isys_catg_pc_list__id',
                        'isys_catg_pc_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_pc_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_pc_list', 'LEFT', 'isys_catg_pc_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__POWER_CONSUMER__ACTIVE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => get_smarty_arr_YES_NO()
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_yes_or_no'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                ]
            ]),
            'manufacturer'       => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__MANUFACTURE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Manufacturer'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_pc_list__isys_pc_manufacturer__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_pc_manufacturer',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_pc_manufacturer',
                        'isys_pc_manufacturer__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_pc_manufacturer__title
                            FROM isys_catg_pc_list
                            INNER JOIN isys_pc_manufacturer ON isys_pc_manufacturer__id = isys_catg_pc_list__isys_pc_manufacturer__id',
                        'isys_catg_pc_list',
                        'isys_catg_pc_list__id',
                        'isys_catg_pc_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_pc_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_pc_list', 'LEFT', 'isys_catg_pc_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_pc_manufacturer',
                            'LEFT',
                            'isys_catg_pc_list__isys_pc_manufacturer__id',
                            'isys_pc_manufacturer__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__POWER_CONSUMER__MANUFACTURER_ID',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'   => 'isys_pc_manufacturer',
                        'p_bDbFieldNN' => '0',
                        'tab'          => '20'
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
            'model'              => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__MODEL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Model'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_pc_list__isys_pc_model__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_pc_model',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_pc_model',
                        'isys_pc_model__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_pc_model__title
                            FROM isys_catg_pc_list
                            INNER JOIN isys_pc_model ON isys_pc_model__id = isys_catg_pc_list__isys_pc_model__id',
                        'isys_catg_pc_list',
                        'isys_catg_pc_list__id',
                        'isys_catg_pc_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_pc_list__isys_obj__id'])
                    ),
                        C__PROPERTY__DATA__JOIN         => [
                            idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_pc_list', 'LEFT', 'isys_catg_pc_list__isys_obj__id', 'isys_obj__id'),
                            idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_pc_model', 'LEFT', 'isys_catg_pc_list__isys_pc_model__id', 'isys_pc_model__id')
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__POWER_CONSUMER__MODEL_ID',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'   => 'isys_pc_model',
                            'p_bDbFieldNN' => '0',
                            'tab'          => '30'
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
            'volt' => new FloatProperty(
                'C__CATG__POWER_CONSUMER__VOLT',
                'LC__CMDB__CATS__POBJ_VOLT',
                'isys_catg_pc_list__volt',
                'isys_catg_pc_list'
            ),
            'watt' => new FloatProperty(
                'C__CATG__POWER_CONSUMER__WATT',
                'LC__CMDB__CATS__POBJ_WATT',
                'isys_catg_pc_list__watt',
                'isys_catg_pc_list'
            ),
            'ampere' => new FloatProperty(
                'C__CATG__POWER_CONSUMER__AMPERE',
                'LC__CMDB__CATS__POBJ_AMPERE',
                'isys_catg_pc_list__ampere',
                'isys_catg_pc_list'
            ),
            'btu' => (new TextProperty(
                'C__CATG__POWER_CONSUMER__BTU',
                'BTU',
                'isys_catg_pc_list__btu',
                'isys_catg_pc_list'
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false
            ]),
            'assigned_connector' => (new ObjectBrowserSecondListProperty(
                'C__CATG__POWER_CONSUMER__DEST',
                'LC__CATG__POWER_SUPPLIER__DEST',
                'isys_catg_pc_list__isys_catg_connector_list__id',
                'isys_catg_pc_list',
                'isys_catg_connector_list',
                'isys_cmdb_dao_category_g_network_port::object_browser',
                null,
                [
                    'isys_export_helper',
                    'assigned_connector'
                ],
                'C__CATG__NETWORK;C__CATG__NETWORK_PORT;C__CATG__NETWORK_LOG_PORT;C__CATG__CABLING;C__CATG__CONNECTOR;C__CATG__UNIVERSAL_INTERFACE'
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__LIST => false,
            ])->mergePropertyUiParams([
                'p_strPopupType' => 'browser_cable_connection_ng',
            ])->mergePropertyData([
                Property::C__PROPERTY__DATA__REFERENCES => [],
                Property::C__PROPERTY__DATA__TABLE_ALIAS => 'output',
                Property::C__PROPERTY__DATA__FIELD_ALIAS => 'con_connector',
                Property::C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                    'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_pc_list
                            INNER JOIN isys_catg_connector_list con1 ON con1.isys_catg_connector_list__id = isys_catg_pc_list__isys_catg_connector_list__id
                            LEFT JOIN isys_catg_connector_list con2 ON con2.isys_catg_connector_list__isys_cable_connection__id = con1.isys_catg_connector_list__isys_cable_connection__id
                              AND con2.isys_catg_connector_list__id != con1.isys_catg_connector_list__id
                            INNER JOIN isys_obj ON isys_obj__id = con2.isys_catg_connector_list__isys_obj__id',
                    'isys_catg_pc_list',
                    'isys_catg_pc_list__id',
                    'isys_catg_pc_list__isys_obj__id',
                    '',
                    '',
                    idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                    idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_pc_list__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN => [
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_pc_list',
                        'LEFT',
                        'isys_catg_pc_list__isys_obj__id',
                        'isys_obj__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_connector_list',
                        'LEFT',
                        'isys_catg_pc_list__isys_catg_connector_list__id',
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
            ]),
            'connector'          => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONNECTOR__ASSIGNED_CONNECTOR',
                    C__PROPERTY__INFO__DESCRIPTION => 'Connector'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_pc_list__isys_catg_connector_list__id',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'connected',
                    C__PROPERTY__DATA__FIELD_ALIAS => 'con_connector',
                    C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT con2.isys_catg_connector_list__title
                            FROM isys_catg_pc_list
                            INNER JOIN isys_catg_connector_list con1 ON con1.isys_catg_connector_list__id = isys_catg_pc_list__isys_catg_connector_list__id
                            LEFT JOIN isys_catg_connector_list con2 ON con2.isys_catg_connector_list__isys_cable_connection__id = con1.isys_catg_connector_list__isys_cable_connection__id
                              AND con2.isys_catg_connector_list__id != con1.isys_catg_connector_list__id',
                        'isys_catg_pc_list',
                        'isys_catg_pc_list__id',
                        'isys_catg_pc_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_pc_list__isys_obj__id'])
                    ),
                    /*
                    C__PROPERTY__DATA__JOIN => idoit\Module\Report\SqlQuery\Structure\JoinSubSelect::factory(
                        'SELECT isys_catg_pc_list__id AS id, isys_catg_pc_list__isys_obj__id AS objectID,
                          con2.isys_catg_connector_list__title AS title, con2.isys_catg_connector_list__id AS reference
                        FROM isys_catg_pc_list
                        INNER JOIN isys_catg_connector_list con1 ON con1.isys_catg_connector_list__id = isys_catg_pc_list__isys_catg_connector_list__id
                        LEFT JOIN isys_catg_connector_list con2 ON con2.isys_catg_connector_list__isys_cable_connection__id = con1.isys_catg_connector_list__isys_cable_connection__id
                          AND con2.isys_catg_connector_list__id != con1.isys_catg_connector_list__id
                        ',
                        'LEFT',
                        [
                            'isys_catg_pc_list',
                            'isys_catg_connector_list'
                        ],
                        'isys_catg_pc_list__id',
                        'isys_catg_pc_list__isys_obj__id'
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
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__VIRTUAL   => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connector'
                    ]
                ]
            ]),
            'description'        => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_pc_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_pc_list__description FROM isys_catg_pc_list',
                        'isys_catg_pc_list',
                        'isys_catg_pc_list__id',
                        'isys_catg_pc_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_pc_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__POWER_CONSUMER', 'C__CATG__POWER_CONSUMER')
                ]
            ])
        ];
    }

    /**
     * Syncing method.
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  boolean
     * @see     isys_cmdb_dao_category::sync()
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $this->m_sync_catg_data = $p_category_data;
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if (($p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        C__RECORD_STATUS__NORMAL,
                        $this->get_property('title'),
                        $this->get_property('manufacturer'),
                        $this->get_property('model'),
                        $this->get_property('assigned_connector'),
                        null,
                        null,
                        $this->get_property('volt'),
                        $this->get_property('watt'),
                        $this->get_property('ampere'),
                        $this->get_property('btu'),
                        $this->get_property('description'),
                        $this->get_property('connector_sibling'),
                        $this->get_property('active')
                    ))) {
                        $l_indicator = true;
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_data = $this->get_data($p_category_data['data_id'])
                        ->get_row();
                    $l_indicator = $this->save(
                        $p_category_data['data_id'],
                        C__RECORD_STATUS__NORMAL,
                        $this->get_property('title'),
                        $this->get_property('manufacturer'),
                        $this->get_property('model'),
                        $l_data['isys_catg_pc_list__isys_catg_connector_list__id'],
                        $this->get_property('assigned_connector'),
                        null,
                        null,
                        $this->get_property('volt'),
                        $this->get_property('watt'),
                        $this->get_property('ampere'),
                        $this->get_property('btu'),
                        $this->get_property('description'),
                        $this->get_property('connector_sibling'),
                        $this->get_property('active')
                    );
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Retrieves the connector-ID by list-ID.
     *
     * @param   integer $p_pcListID
     *
     * @return  integer
     */
    public function get_connector($p_pcListID)
    {
        $l_query = "SELECT isys_catg_pc_list__isys_catg_connector_list__id AS id FROM isys_catg_pc_list WHERE isys_catg_pc_list__id = " . $this->convert_sql_id($p_pcListID);

        return $this->retrieve($l_query)
            ->get_row_value('id');
    }

    /**
     * Checks if power consumer already exists associated by the given title and object id.
     *
     * @param   string  $p_title
     * @param   integer $p_obj__id
     *
     * @return  mixed  Integer on success, null on failure.
     */
    public function get_pc_by_obj_id_and_title($p_title, $p_obj__id)
    {
        $l_sql = 'SELECT isys_catg_pc_list__id
            FROM isys_catg_pc_list
            WHERE isys_catg_pc_list__title = ' . $this->convert_sql_text($p_title) . '
            AND isys_catg_pc_list__isys_obj__id = ' . $this->convert_sql_id($p_obj__id) . ';';

        try {
            return $this->retrieve($l_sql)->get_row_value('isys_catg_pc_list__id');
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Method for saving the element.
     *
     * @param   integer $p_cat_level
     * @param   integer & $p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @return  integer  The error code or null on success.
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus, $p_create)
    {
        $l_intErrorCode = -1;

        if ($p_create) {
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__POWER_CONSUMER__TITLE'],
                $_POST['C__CATG__POWER_CONSUMER__MANUFACTURER_ID'],
                $_POST['C__CATG__POWER_CONSUMER__MODEL_ID'],
                $_POST['C__CATG__POWER_CONSUMER__DEST__HIDDEN'],
                $_POST['C__CATG__POWER_CONSUMER__DEST__CABLE_NAME'],
                $_POST['C__CATG__POWER_CONSUMER__CABLE__HIDDEN'],
                $_POST['C__CATG__POWER_CONSUMER__VOLT'],
                $_POST['C__CATG__POWER_CONSUMER__WATT'],
                $_POST['C__CATG__POWER_CONSUMER__AMPERE'],
                $_POST['C__CATG__POWER_CONSUMER__BTU'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                null,
                $_POST["C__CATG__POWER_CONSUMER__ACTIVE"]
            );

            $this->m_strLogbookSQL = $this->get_last_query();

            $p_cat_level = 1;

            return $l_id;
        }

        $l_catdata = $this->get_result()
            ->__to_array();
        $p_intOldRecStatus = $l_catdata["isys_catg_pc_list__status"];

        $l_bRet = $this->save(
            $l_catdata["isys_catg_pc_list__id"],
            C__RECORD_STATUS__NORMAL,
            $_POST['C__CATG__POWER_CONSUMER__TITLE'],
            $_POST['C__CATG__POWER_CONSUMER__MANUFACTURER_ID'],
            $_POST['C__CATG__POWER_CONSUMER__MODEL_ID'],
            $l_catdata["isys_catg_pc_list__isys_catg_connector_list__id"],
            $_POST['C__CATG__POWER_CONSUMER__DEST__HIDDEN'],
            $_POST['C__CATG__POWER_CONSUMER__DEST__CABLE_NAME'],
            $_POST['C__CATG__POWER_CONSUMER__CABLE__HIDDEN'],
            $_POST['C__CATG__POWER_CONSUMER__VOLT'],
            $_POST['C__CATG__POWER_CONSUMER__WATT'],
            $_POST['C__CATG__POWER_CONSUMER__AMPERE'],
            $_POST['C__CATG__POWER_CONSUMER__BTU'],
            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
            null,
            $_POST["C__CATG__POWER_CONSUMER__ACTIVE"]
        );

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_bRet == true ? null : $l_intErrorCode;
    }

    /**
     * Executes the operations to create the category entry referenced by isys_obj__id $p_objID
     *
     * @param  integer $p_objID
     * @param  integer $p_status
     * @param  string  $p_title
     * @param  integer $p_manufacturerID
     * @param  integer $p_modelID
     * @param  integer $p_connectorAheadID
     * @param  integer $p_cableID
     * @param  string  $p_cableName
     * @param  string  $p_volt
     * @param  string  $p_watt
     * @param  string  $p_ampere
     * @param  string  $p_btu
     * @param  string  $p_description
     *
     * @return int the newly created ID or false on failure
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create(
        $p_objID,
        $p_status,
        $p_title,
        $p_manufacturerID,
        $p_modelID,
        $p_connectorAheadID,
        $p_cableName,
        $p_cableID,
        $p_volt,
        $p_watt,
        $p_ampere,
        $p_btu,
        $p_description,
        $p_connector_sibling = null,
        $p_active = null
    ) {
        $l_daoConnector = new isys_cmdb_dao_category_g_connector($this->m_db);
        $l_daoCableConnection = new isys_cmdb_dao_cable_connection($this->m_db);
        $l_connectorRearID = $l_daoConnector->create(
            $p_objID,
            C__CONNECTOR__INPUT,
            null,
            null,
            $p_title,
            $p_description,
            $p_connector_sibling,
            null,
            "C__CATG__POWER_CONSUMER",
            $p_cableID
        );

        if ($p_connectorAheadID != null) {
            // If the cable-id is empty, we create a new cable with a nice name.
            if (empty($p_cableID)) {
                $p_cableID = isys_cmdb_dao_cable_connection::add_cable($p_cableName);
            }

            $l_daoCableConnection->delete_cable_connection($l_daoCableConnection->get_cable_connection_id_by_connector_id($p_connectorAheadID));
            $l_conID = $l_daoCableConnection->add_cable_connection($p_cableID);
            $l_daoCableConnection->save_connection($l_connectorRearID, $p_connectorAheadID, $l_conID);
        }

        $l_update = "INSERT INTO isys_catg_pc_list SET " . "isys_catg_pc_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ", " . "isys_catg_pc_list__status = " .
            $this->convert_sql_id($p_status) . ", " . "isys_catg_pc_list__title = " . $this->convert_sql_text($p_title) . ", " .
            "isys_catg_pc_list__isys_pc_manufacturer__id = " . $this->convert_sql_id($p_manufacturerID) . ", " . "isys_catg_pc_list__isys_pc_model__id = " .
            $this->convert_sql_id($p_modelID) . ", " . "isys_catg_pc_list__isys_catg_connector_list__id = " . $this->convert_sql_id($l_connectorRearID) . ", " .
            "isys_catg_pc_list__volt = " . $this->convert_sql_text($p_volt) . ", " . "isys_catg_pc_list__watt = " . $this->convert_sql_text($p_watt) . ", " .
            "isys_catg_pc_list__ampere = " . $this->convert_sql_text($p_ampere) . ", " . "isys_catg_pc_list__btu = " . $this->convert_sql_text($p_btu) . ", " .
            "isys_catg_pc_list__active = " . $this->convert_sql_boolean((bool)$p_active) . ", " . "isys_catg_pc_list__description = " .
            $this->convert_sql_text($p_description);

        if ($this->update($l_update) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    /**
     * Executes the operations to update the category entry referenced by isys_catg_pc_list__id $p_catlevel.
     *
     * @param   integer $p_catlevel
     * @param   integer $p_status
     * @param   string  $p_title
     * @param   integer $p_manufacturerID
     * @param   integer $p_modelID
     * @param   integer $p_connectorRearID
     * @param   integer $p_connectorAheadID
     * @param   string  $p_cableName
     * @param   integer $p_cableID
     * @param   string  $p_volt
     * @param   string  $p_watt
     * @param   string  $p_ampere
     * @param   string  $p_btu
     * @param   string  $p_description
     *
     * @return  integer  The newly created ID or false on failure
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save(
        $p_catlevel,
        $p_status,
        $p_title,
        $p_manufacturerID,
        $p_modelID,
        $p_connectorRearID,
        $p_connectorAheadID,
        $p_cableName,
        $p_cableID,
        $p_volt,
        $p_watt,
        $p_ampere,
        $p_btu,
        $p_description,
        $p_connector_sibling = null,
        $p_active = null
    ) {
        $success = false;
        $l_update = "UPDATE isys_catg_pc_list SET " . "isys_catg_pc_list__status = " . $this->convert_sql_id($p_status) . ", " . "isys_catg_pc_list__title = " .
            $this->convert_sql_text($p_title) . ", " . "isys_catg_pc_list__isys_pc_manufacturer__id = " . $this->convert_sql_id($p_manufacturerID) . ", " .
            "isys_catg_pc_list__isys_pc_model__id = " . $this->convert_sql_id($p_modelID) . ", " . "isys_catg_pc_list__volt = " . $this->convert_sql_text($p_volt) . ", " .
            "isys_catg_pc_list__watt = " . $this->convert_sql_text($p_watt) . ", " . "isys_catg_pc_list__ampere = " . $this->convert_sql_text($p_ampere) . ", " .
            "isys_catg_pc_list__btu = " . $this->convert_sql_text($p_btu) . ", " . "isys_catg_pc_list__active = " . $this->convert_sql_boolean((bool)$p_active) . ", " .
            "isys_catg_pc_list__description = " . $this->convert_sql_text($p_description) . " ";

        if ($p_connectorRearID != null) {
            $l_update .= ", isys_catg_pc_list__isys_catg_connector_list__id = " . $this->convert_sql_id($p_connectorRearID) . " ";
        }

        $l_update .= "WHERE isys_catg_pc_list__id = " . $this->convert_sql_id($p_catlevel);

        if ($this->update($l_update) && $this->apply_update()) {
            $connectorId = $this->get_connector($p_catlevel);

            if (!is_numeric($connectorId)) {
                throw new isys_exception_cmdb("Error: Your FC-Port has lost its connector reference and is therefore inconsistent. " .
                    "You should remove and recreate it in order to reference any other port.");
            }

            $connectorUpdates = [
                'isys_catg_connector_list__title' => $this->convert_sql_text($p_title)
            ];

            if ($p_connector_sibling > 0 && !($p_connectorAheadID == null && $p_cableID == null)) {
                $connectorUpdates['isys_catg_connector_list__isys_catg_connector_list__id'] = $this->convert_sql_id($p_connector_sibling);
            }

            $success = isys_cmdb_dao_cable_connection::instance($this->m_db)
                ->handleConnectorUpdate(
                    $connectorId,
                    false,
                    $p_connectorAheadID,
                    $p_cableID,
                    ($p_cableName ?: $p_title),
                    $connectorUpdates,
                    'isys_cmdb_dao_category_g_power_consumer::assigned_connector',
                    $this->getCategoryTitle()
                );
        }

        return $success;
    }

    /**
     * Post rank is called before a regular rank.
     *
     * @param   integer $p_list_id
     * @param   integer $p_direction
     * @param   string  $p_table
     *
     * @return  boolean
     */
    public function pre_rank($p_list_id, $p_direction, $p_table)
    {
        $l_sql = 'SELECT isys_catg_pc_list__status, isys_catg_pc_list__isys_catg_connector_list__id ' . 'FROM isys_catg_pc_list ' . 'WHERE isys_catg_pc_list__id = ' .
            $this->convert_sql_id($p_list_id) . ';';

        $l_res = $this->retrieve($l_sql);
        $l_row = $l_res->get_row();
        $l_status = $l_row["isys_catg_pc_list__status"];
        $l_connectorID = $l_row["isys_catg_pc_list__isys_catg_connector_list__id"];

        if ($l_connectorID == null) {
            return true;
        }

        $l_update = "UPDATE isys_catg_connector_list SET isys_catg_connector_list__status = ";

        switch ($l_status) {
            case C__RECORD_STATUS__NORMAL:
                if ($p_direction == C__CMDB__RANK__DIRECTION_DELETE) {
                    $l_update .= C__RECORD_STATUS__ARCHIVED;
                }
                break;

            case C__RECORD_STATUS__ARCHIVED:
                if ($p_direction == C__CMDB__RANK__DIRECTION_DELETE) {
                    $l_update .= C__RECORD_STATUS__DELETED;
                } else {
                    $l_update .= C__RECORD_STATUS__NORMAL;
                }
                break;

            case C__RECORD_STATUS__DELETED:
                if ($p_direction == C__CMDB__RANK__DIRECTION_DELETE) {
                    $l_update = "DELETE FROM isys_catg_connector_list";
                    $l_purge = true;
                } else {
                    $l_update .= C__RECORD_STATUS__ARCHIVED;
                }
                break;
        }

        $l_update .= " WHERE isys_catg_connector_list__id = " . $this->convert_sql_id($l_connectorID);

        if ($l_purge) {
            return true;
        }

        if ($this->update($l_update) && $this->apply_update()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Formats the title of the object for the object browser.
     *
     * @param   integer $p_ip_id
     * @param   boolean $p_plain
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function format_selection($p_ip_id, $p_plain = false)
    {
        // We need a DAO for the object name.
        $l_dao = isys_cmdb_dao_category_g_hba::instance($this->m_db);
        $l_quick_info = new isys_ajax_handler_quick_info();

        $l_row = $l_dao->get_data($p_ip_id)
            ->__to_array();

        $l_object_type = $l_dao->get_objTypeID($l_row["isys_catg_hba_list__isys_obj__id"]);

        if (!empty($p_ip_id)) {
            $l_editmode = ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT || isys_glob_get_param("editMode") == C__EDITMODE__ON ||
                    isys_glob_get_param("edit") == C__EDITMODE__ON || isset($this->m_params["edit"])) && !isset($this->m_params["plain"]);

            $l_title = isys_application::instance()->container->get('language')
                    ->get($l_dao->get_objtype_name_by_id_as_string($l_object_type)) . " >> " .
                $l_dao->get_obj_name_by_id_as_string($l_row["isys_catg_hba_list__isys_obj__id"]) . " >> " . $l_row["isys_catg_hba_list__title"];

            if (!$l_editmode && !$p_plain) {
                return $l_quick_info->get_quick_info($l_row["isys_catg_hba_list__isys_obj__id"], $l_title, C__LINK__OBJECT);
            } else {
                return $l_title;
            }
        }

        return isys_application::instance()->container->get('language')
            ->get("LC__CMDB__BROWSER_OBJECT__NONE_SELECTED");
    }
}
