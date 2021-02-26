<?php

use idoit\Component\Property\Type\DynamicProperty;
use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DynamicYesNoProperty;
use idoit\Context\Context;
use idoit\Module\Report\SqlQuery\Condition\Comparison\InCondition;
use idoit\Module\Report\SqlQuery\Condition\Comparison\LikeCondition;
use idoit\Module\Report\SqlQuery\Condition\Comparison\NotInCondition;
use idoit\Module\Report\SqlQuery\Condition\Comparison\NotLikeCondition;
use idoit\Module\Report\SqlQuery\Condition\Comparison\NotNullCondition;
use idoit\Module\Report\SqlQuery\Condition\Comparison\NullCondition;
use idoit\Module\Report\SqlQuery\Condition\ComparisonProvider;
use idoit\Module\Report\SqlQuery\Condition\Filter\Filter;
use idoit\Module\Report\SqlQuery\Condition\DynamicPropertyProvider;
use idoit\Module\Report\SqlQuery\Condition\Filter\FilterProcessorConditionEmptyValue;
use idoit\Module\Report\SqlQuery\Condition\Filter\FilterProcessorConditionValue;
use idoit\Module\Report\SqlQuery\Condition\Filter\FilterProcessorDynamic;
use idoit\Module\Report\SqlQuery\Condition\Filter\FilterProcessorId;
use idoit\Module\Report\SqlQuery\Condition\Filter\FilterProcessorObjectId;
use idoit\Module\Report\SqlQuery\Condition\PropertyProvider;
use idoit\Module\Report\SqlQuery\Condition\PropertyTypeProvider;
use idoit\Module\Report\SqlQuery\Condition\DefaultCondition;
use idoit\Module\Report\SqlQuery\Structure\ReportQuery;

/**
 * i-doit
 *
 * DAO: abstraction layer for CMDB global categories.
 *
 * @package        i-doit
 * @subpackage     CMDB_Categories
 * @author         Leonard Fischer <lfischer@i-doit.org>
 * @version        Van Quyen Hoang <qhoang@i-doit.org> 19.05.2014
 * @copyright      synetics GmbH
 * @license        http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_property extends isys_cmdb_dao_category
{
    /**
     * @var \idoit\Module\Report\SqlQuery\Condition\AbstractProvider[];
     */
    private $conditionProviders;

    /**
     * Ignore these format callbacks
     *
     * @var array
     */
    public static $m_ignored_format_callbacks = [
        'location_property_pos'
    ];

    /**
     * Alias counter for the report-builder.
     *
     * @var  integer
     */
    protected $m_alias_cnt = 1;

    public static $tablesConnectedWith_2_isys_obj_suffix = [
        'isys_catg_cards_list',
    ];
    /**
     * List of aliases for the report-builder.
     *
     * @var  array
     */
    protected $m_aliases = [
        'C__CATG__GLOBAL::isys_obj' => 'obj_main',
        //'isys_catg_ip_list' => 'ip_list',
        //'isys_cats_net_ip_addresses_list' => 'ip',
        //'isys_catg_location_list' => 'loc',
        //'isys_catg_logical_unit_list' => 'log_unit'
    ];

    /**
     * @var array
     */
    protected $m_aliases_lvls = [];

    /**
     * Variable which determines if the report also displays empty values
     *
     * @var bool
     */
    protected $m_empty_values = true;

    /**
     * This variable is used to define, if all the necessary preparations have been made for creating the generic query.
     *
     * @var  boolean
     */
    protected $m_prepared_data_for_query_construction = false;

    /**
     * This array will hold all the necessary property-data!
     *
     * @var  array
     */
    protected $m_property_rows = [];

    /**
     * This array will hold all the necessary property-data!
     *
     * @var  array
     */
    protected $m_property_rows_lvls = [];

    /**
     * @var bool|null
     */
    protected $m_query_as_report = null;

    /**
     * Determines if this report has sub levels.
     *
     * @var $bool
     */
    protected $reportHasSubLvls = false;

    /**
     * List of tables which should be joined at the end of the join list
     *
     * @var string[]
     */
    private $tablesToBeReordered = [
        'isys_catg_contact_list'
    ];

    /**
     * List of special contitions for certain fields. The "%s" are ment for sprintf's parameter "condition" and "value".
     *
     * @var  array
     */
    protected $m_special_conditions = [
        'isys_obj__isys_cmdb_status__id'                    => 'obj_main.isys_obj__isys_cmdb_status__id',
        //'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id' => 'ip.isys_cats_net_ip_addresses_list__title',
        'isys_catg_location_list__parentid'                 => 'loc.isys_catg_location_list__parentid',
        'isys_catg_logical_unit_list__isys_obj__id__parent' => 'log_unit.isys_catg_logical_unit_list__isys_obj__id__parent'
    ];

    /**
     * List of special JOIN statements for certain tables.
     *
     * @todo refactor all special joins in class::dbField => true
     *
     * @var  array
     */
    protected $m_special_joins = [
        'isys_obj'                                                                                     => null,
        'isys_catg_ip_list'                                                                            => '',
        //'LEFT JOIN isys_catg_ip_list AS ip_list ON ip_list.isys_catg_ip_list__isys_obj__id = obj_main.isys_obj__id LEFT JOIN isys_cats_net_ip_addresses_list AS ip ON ip.isys_cats_net_ip_addresses_list__id = ip_list.isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
        'isys_cats_net_ip_addresses_list'                                                              => '',
        //'LEFT JOIN isys_catg_ip_list AS ip_list ON ip_list.isys_catg_ip_list__isys_obj__id = obj_main.isys_obj__id LEFT JOIN isys_cats_net_ip_addresses_list AS ip ON ip.isys_cats_net_ip_addresses_list__id = ip_list.isys_catg_ip_list__isys_cats_net_ip_addresses_list__id',
        'isys_catg_logical_unit_list'                                                                  => '',
        //'LEFT JOIN isys_catg_logical_unit_list AS log_unit ON log_unit.isys_catg_logical_unit_list__isys_obj__id = obj_main.isys_obj__id LEFT JOIN isys_obj AS log_obj ON log_unit.isys_catg_logical_unit_list__isys_obj__id__parent = log_obj.isys_obj__id',
        'isys_catg_logical_unit_list__isys_obj__id'                                                    => [
            [
                'isys_catg_logical_unit_list',
                'isys_obj',
                'isys_catg_logical_unit_list__isys_obj__id__parent',
                'isys_obj__id'
            ],
            [
                'isys_obj',
                'isys_catg_logical_unit_list',
                'isys_obj__id',
                'isys_catg_logical_unit_list__isys_obj__id'
            ]
        ],
        'isys_catg_logical_unit_list__isys_obj__id__parent'                                            => [
            [
                'isys_catg_logical_unit_list',
                'isys_obj',
                'isys_catg_logical_unit_list__isys_obj__id',
                'isys_obj__id'
            ],
            [
                'isys_obj',
                'isys_catg_logical_unit_list',
                'isys_obj__id',
                'isys_catg_logical_unit_list__isys_obj__id__parent'
            ]
        ],
        'isys_cats_net_ip_addresses_list__isys_obj__id'                                                => [
            [
                'isys_catg_ip_list',
                'isys_obj',
                'isys_catg_ip_list__isys_obj__id',
                'isys_obj__id'
            ],
            [
                'isys_cats_net_ip_addresses_list',
                'isys_catg_ip_list',
                'isys_cats_net_ip_addresses_list__id',
                'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'
            ]
        ],
        'isys_cats_person_group_list__isys_obj__id'                                                    => [
            [
                'isys_person_2_group',
                'isys_obj',
                'isys_person_2_group__isys_obj__id__person',
                'isys_obj__id'
            ],
            [
                'isys_cats_person_group_list',
                'isys_person_2_group',
                'isys_cats_person_group_list__isys_obj__id',
                'isys_person_2_group__isys_obj__id__group'
            ],
        ],
        'isys_cats_person_list__isys_obj__id'                                                          => [
            [
                'isys_person_2_group',
                'isys_obj',
                'isys_person_2_group__isys_obj__id__group',
                'isys_obj__id'
            ],
            [
                'isys_cats_person_list',
                'isys_person_2_group',
                'isys_cats_person_list__isys_obj__id',
                'isys_person_2_group__isys_obj__id__person'
            ],
        ],
        'isys_catg_contract_assignment_list__isys_obj__id'                                             => [
            [
                'isys_catg_contract_assignment_list',
                'isys_connection',
                'isys_catg_contract_assignment_list__isys_connection__id',
                'isys_connection__id'
            ]
        ],
        'isys_catg_application_list__isys_obj__id'                                                     => [
            [
                'isys_catg_application_list',
                'isys_connection',
                'isys_catg_application_list__isys_connection__id',
                'isys_connection__id'
            ]
        ],
        'isys_catg_assigned_cards_list__isys_obj__id__card'                                            => [
            [
                'isys_catg_assigned_cards_list',
                'isys_obj',
                'isys_catg_assigned_cards_list__isys_obj__id',
                'isys_obj__id'
            ],
            [
                'isys_obj',
                'isys_catg_assigned_cards_list',
                'isys_obj__id',
                'isys_catg_assigned_cards_list__isys_obj__id__card'
            ]
        ],
        'isys_catg_backup_list__isys_obj__id'                                                          => [
            [
                'isys_catg_backup_list',
                'isys_connection',
                'isys_catg_backup_list__isys_connection__id',
                'isys_connection__id'
            ]
        ],
        'isys_catg_nagios_refs_services_list__isys_obj__id__service'                                   => [
            [
                'isys_catg_nagios_refs_services_list',
                'isys_obj',
                'isys_catg_nagios_refs_services_list__isys_obj__id__host',
                'isys_obj__id'
            ],
            [
                'isys_obj',
                'isys_catg_nagios_refs_services_list',
                'isys_obj__id',
                'isys_catg_nagios_refs_services_list__isys_obj__id__service'
            ],
        ],
        'isys_catg_assigned_cards_list__isys_obj__id'                                                  => [
            [
                'isys_catg_assigned_cards_list',
                'isys_obj',
                'isys_catg_assigned_cards_list__isys_obj__id__card',
                'isys_obj__id'
            ],
            [
                'isys_obj',
                'isys_catg_assigned_cards_list',
                'isys_obj__id',
                'isys_catg_assigned_cards_list__isys_obj__id'
            ],
        ],
        'isys_catg_ldevclient_list__isys_catg_sanpool_list__id'                                        => [
            [
                'isys_catg_ldevclient_list',
                'isys_obj',
                'isys_catg_ldevclient_list__isys_obj__id',
                'isys_obj__id'
            ],
            [
                'isys_catg_sanpool_list',
                'isys_catg_ldevclient_list',
                'isys_catg_sanpool_list__id',
                'isys_catg_ldevclient_list__isys_catg_sanpool_list__id'
            ]
        ],
        'isys_catg_virtual_machine_list__isys_obj__id'                                                 => [
            [
                'isys_catg_virtual_machine_list',
                'isys_connection',
                'isys_catg_virtual_machine_list__isys_connection__id',
                'isys_connection__id'
            ]
        ],
        'isys_catg_location_list__isys_obj__id'                                                        => [
            [
                'isys_catg_location_list',
                'isys_obj',
                'isys_catg_location_list__parentid',
                'isys_obj__id'
            ],
            [
                'isys_obj',
                'isys_catg_location_list',
                'isys_obj__id',
                'isys_catg_location_list__isys_obj__id'
            ],
        ],
        'isys_cats_database_instance_list__isys_obj__id'                                               => [
            [
                'isys_cats_database_schema_list',
                'isys_connection',
                'isys_cats_database_schema_list__isys_connection__id',
                'isys_connection__id'
            ]
        ],
        'isys_cmdb_dao_category_g_relation::isys_catg_relation_list__isys_obj__id__master'             => true,
        'isys_cmdb_dao_category_g_relation::isys_catg_relation_list__isys_obj__id__slave'              => true,
        'isys_cmdb_dao_category_g_relation::isys_catg_relation_list__isys_obj__id__itservice'          => true,
        'isys_cmdb_dao_category_s_cluster_service::isys_catg_cluster_service_list__isys_obj__id'       => true,
        'isys_cmdb_dao_category_s_person_contact_assign::isys_catg_contact_list__isys_obj__id'         => true,
        'isys_cmdb_dao_category_s_person_contact_assign::isys_catg_contact_list__isys_contact_tag__id' => true,
        'isys_cmdb_dao_category_s_person_group_contact_assign::isys_catg_contact_list__isys_obj__id'   => true,
        'isys_cmdb_dao_category_s_organization_contact_assign::isys_catg_contact_list__isys_obj__id'   => true,
        'isys_cmdb_dao_category_g_itservice::isys_catg_its_components_list__isys_obj__id'              => true,
        'isys_cmdb_dao_category_g_network_ifacel::isys_catg_log_port_list__isys_catg_log_port_list__id' => true,
        'isys_cmdb_dao_category_g_location::isys_catg_location_list__parentid' => true,
        'isys_cmdb_dao_category_s_application_assigned_obj::*' => true,
        'isys_cmdb_dao_category_g_assigned_sim_cards::*' => true
    ];

    /**
     * List of special SELECT statements for certain table-fields.
     *
     * @var  array
     */
    protected $m_special_selects = [
        //'isys_catg_ip_list__isys_cats_net_ip_addresses_list__id' => "ip.isys_cats_net_ip_addresses_list__title AS 'LC__CMDB__CATG__IP__IPV4_ADDRESS'",
        'isys_catg_logical_unit_list__isys_obj__id__parent'          => [
            'isys_obj',
            'isys_catg_logical_unit_list',
            'isys_catg_logical_unit_list__isys_obj__id__parent'
        ],
        // "log_obj.isys_obj__title AS 'LC__CMDB__CATG__LOGICAL_UNIT__PARENT'",
        'isys_catg_logical_unit_list__isys_obj__id'                  => [
            'isys_obj',
            'isys_catg_logical_unit_list',
            'isys_catg_logical_unit_list__isys_obj__id'
        ],
        'isys_cats_net_ip_addresses_list__isys_obj__id'              => [
            'isys_catg_ip_list',
            'isys_cats_net_ip_addresses_list',
            'isys_cats_net_ip_addresses_list__isys_obj__id'
        ],
        'isys_cats_person_group_list__isys_obj__id'                  => [
            'isys_person_2_group',
            'isys_cats_person_group_list',
            'isys_cats_person_group_list__isys_obj__id'
        ],
        'isys_cats_person_list__isys_obj__id'                        => [
            'isys_person_2_group',
            'isys_cats_person_list',
            'isys_cats_person_list__isys_obj__id'
        ],
        'isys_catg_assigned_cards_list__isys_obj__id__card'          => [
            'isys_obj',
            'isys_catg_assigned_cards_list',
            'isys_catg_assigned_cards_list__isys_obj__id__card'
        ],
        'isys_catg_application_list__isys_obj__id'                   => [
            'isys_connection',
            'isys_catg_application_list',
            'isys_catg_application_list__isys_obj__id'
        ],
        'isys_catg_backup_list__isys_obj__id'                        => [
            'isys_connection',
            'isys_catg_backup_list',
            'isys_catg_backup_list__isys_obj__id'
        ],
        'isys_catg_its_components_list__isys_obj__id'                => [
            'isys_connection',
            'isys_catg_its_components_list',
            'isys_catg_its_components_list__isys_obj__id'
        ],
        'isys_catg_nagios_refs_services_list__isys_obj__id__service' => [
            'isys_obj',
            'isys_catg_nagios_refs_services_list',
            'isys_catg_nagios_refs_services_list__isys_obj__id__service'
        ],
        'isys_catg_assigned_cards_list__isys_obj__id'                => [
            'isys_obj',
            'isys_catg_assigned_cards_list',
            'isys_catg_assigned_cards_list__isys_obj__id'
        ],
        'isys_catg_virtual_machine_list__isys_obj__id'               => [
            'isys_connection',
            'isys_catg_virtual_machine_list',
            'isys_catg_virtual_machine_list__isys_obj__id'
        ],
        'isys_catg_location_list__isys_obj__id'                      => [
            'isys_obj',
            'isys_catg_location_list',
            'isys_catg_location_list__parentid'
        ],
        'isys_cats_database_instance_list__isys_obj__id'             => [
            'isys_connection',
            'isys_cats_database_schema_list',
            'isys_cats_database_schema_list__isys_obj__id'
        ],
        'isys_catg_relation_list__isys_obj__id__master'              => [
            'isys_obj',
            'isys_catg_relation_list',
            'isys_catg_relation_list__isys_obj__id'
        ],
        'isys_catg_relation_list__isys_obj__id__slave'               => [
            'isys_obj',
            'isys_catg_relation_list',
            'isys_catg_relation_list__isys_obj__id'
        ],
        'isys_catg_relation_list__isys_obj__id__itservice'           => [
            'isys_obj',
            'isys_catg_relation_list',
            'isys_catg_relation_list__isys_obj__id'
        ],
        'isys_catg_contact_list__isys_obj__id'                       => [
            'isys_connection',
            'isys_catg_contact_list',
            'isys_catg_contact_list__isys_obj__id'
        ],
        'isys_catg_cards_list__isys_obj__id' => [
            'isys_catg_cards_list',
            'isys_obj',
            'isys_obj__id'
        ],
        'isys_catg_cards_list__id' => [
            'isys_catg_cards_list',
            'isys_obj',
            'isys_obj__id'
        ]
    ];

    /**
     * Contains all used aliase
     *
     * @var array
     */
    private $m_already_used_aliase = [];

    /**
     * Variable which contains the data field columns which will be used for the custom categories joins only for the
     * main object.
     *
     * @var string
     */
    private $m_parent_custom_field = [];

    /**
     * Contains the referenced fields
     *
     * @var array
     */
    private $m_referenced_fields = [];

    /**
     * Variable which contains all referenced fields which will be deleted after building the query
     *
     * @var array
     */
    private $m_remove_from_selection = [];

    /**
     * Contains the sub joins from the conditions
     *
     * @var array
     */
    private $m_sub_joins = [];

    /**
     * Container for joined tables from each level
     *
     * @var array
     */
    private $joinedTables = [];

    /**
     * Status filter which will be used for multivalue categories
     *
     * @var int[]
     */
    private $multivalueStatusFilter = [];

    /**
     * @var ReportQuery
     */
    private $reportQuery;

    /**
     * @var array
     */
    private $selectedRootProperties = [];

    /**
     * @var array
     */
    private $selectedLvlsProperties = [];

    /**
     * @var array
     */
    private $selectedConditions = [];

    /**
     * @var null
     */
    private $defaultSorting = null;

    /**
     * @var bool
     */
    private $groupBy = false;

    /**
     * @var null
     */
    private $sortDirection = null;

    /**
     * @var bool
     */
    private $displayRelations = false;

    /**
     * Special array which is used for conditions
     *
     * @var array
     */
    private $m_text_fields = [
        'C__CATG__APPLICATION' => [
            'assigned_variant' => 'isys_cats_app_variant_list__variant'
        ]
    ];

    /**
     * @return ReportQuery
     */
    public function getReportQuery()
    {
        return $this->reportQuery;
    }

    /**
     * Method for setting the "query_as_report" variable from extern.
     *
     * @param   boolean $p_query_as_report
     *
     * @return  isys_cmdb_dao_category_property
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_query_as_report($p_query_as_report)
    {
        $this->m_query_as_report = !!$p_query_as_report;

        return $this;
    }

    /**
     * Resets member variables
     *
     * @return  isys_cmdb_dao_category_property
     */
    public function reset()
    {
        $this->m_already_used_aliase = [];
        $this->m_aliases_lvls = [];
        $this->m_sub_joins = [];
        $this->m_referenced_fields = [];
        $this->m_remove_from_selection = [];
        $this->m_parent_custom_field = [];
        $this->m_aliases = [
            'C__CATG__GLOBAL::isys_obj' => 'obj_main',
        ];
        $this->m_prepared_data_for_query_construction = false;
        $this->multivalueStatusFilter = [];
        $this->reportQuery = new ReportQuery();

        return $this;
    }

    /**
     * Method for recieving the property-ID's by a given array.
     *
     * @param   array $p_properties
     *
     * @return  array
     * @author  Dennis StÃ¼cken <dstuecken@synetics.de>
     * @todo    Enable this method to handle categorie-arrays with more than one property!
     */
    public function format_property_array($p_properties)
    {
        $l_property_array = [];

        foreach ($p_properties as $l_props) {
            if ($l_props['g']) {
                foreach ($l_props['g'] as $l_cat_id => $l_property) {
                    $l_tmp = $this->retrieve_properties(null, $l_cat_id, null, null, ' AND isys_property_2_cat__prop_key = \'' . $l_property[0] . '\'')
                        ->get_row();

                    if ($l_tmp) {
                        $l_property_array[] = $l_tmp['id'];
                    }
                }

                foreach ($l_props['s'] as $l_cat_id => $l_property) {
                    $l_tmp = $this->retrieve_properties(null, null, $l_cat_id, null, ' AND isys_property_2_cat__prop_key = \'' . $l_property[0] . '\'')
                        ->get_row();

                    if ($l_tmp) {
                        $l_property_array[] = $l_tmp['id'];
                    }
                }
            }
        }

        return $l_property_array;
    }

    /**
     * Creates SQL query from selected properties.
     *
     * @param   mixed   $p_properties JSON array or array
     * @param   array   $p_objects    List of objects ('isys_obj__id' has be to included!).
     * @param   boolean $p_with_object_data
     *
     * @return  string  SQL query
     * @throws  isys_exception_general
     */
    public function create_property_query($p_properties, $p_objects, $p_with_object_data = false)
    {
        try {
            if (is_string($p_properties)) {
                $l_properties = isys_format_json::decode($p_properties);
            } elseif (is_array($p_properties)) {
                $l_properties = $p_properties;
            } else {
                throw new isys_exception_general('Invalid argument.');
            }

            $l_smarty_plugin = new isys_smarty_plugin_f_property_selector();
            $l_preselection = $l_smarty_plugin->handle_preselection($l_properties);

            $l_keys = [];
            foreach ($l_preselection as $l_value) {
                $l_keys[] = $l_value['prop_id'];
            }

            if ($p_with_object_data) {
                $l_selects = $this->create_property_query_select($l_keys, false, ['obj_main.*']);
            } else {
                $l_selects = $this->create_property_query_select($l_keys, false);
            }

            $l_joins = $this->create_property_query_join($l_keys);

            $l_objects = [];

            if (is_array($p_objects) && count($p_objects)) {
                foreach ($p_objects as $l_object) {
                    $l_objects[] = $this->convert_sql_id($l_object['isys_obj__id']);
                }

                return 'SELECT ' . implode(', ', $l_selects) . ' FROM isys_obj as obj_main ' . implode(' ', $l_joins) . ' WHERE obj_main.isys_obj__id IN (' .
                    implode(',', $l_objects) . ');';
            }

            // This could happen, but shall not throw errors :)
            return 'SELECT ' . implode(', ', $l_selects) . ' FROM isys_obj as obj_main ' . implode(' ', $l_joins) . ' WHERE obj_main.isys_obj__id = NULL;';
        } catch (Exception $e) {
            throw new isys_exception_general('Failed to create property query: ' . $e->getMessage());
        } //try/catch
    }

    /**
     * Method for creating a generic list query based on configured category properties.
     *
     * @param   mixed   $p_properties
     * @param   mixed   $p_object_types Can be either a integer or a array of integers.
     * @param   mixed   $p_object_ids   Can be either a integer or a array of integers.
     * @param   array   $p_queries
     * @param   boolean $p_leave_field_identifiers
     *
     * @return  string
     * @author  Dennis StÃ¼cken <dstuecken@synetics.de>
     */
    public function create_property_query_for_lists(
        $p_properties,
        $p_object_types = null,
        $p_object_ids = null,
        $p_queries = ['obj_main.*'],
        $p_leave_field_identifiers = true,
        $p_use_property_ids_as_title = false
    ) {
        if (is_string($p_properties)) {
            $p_properties = isys_format_json::decode($p_properties);
        }

        $this->prepare_necessary_tasks((array)$p_properties);

        $l_selects = $this->create_property_query_select($p_properties, false, $p_queries, $p_leave_field_identifiers, $p_use_property_ids_as_title);

        $l_joins = $this->create_property_query_join($p_properties);

        $l_sql = "SELECT \n" . implode(", \n", $l_selects) . " \n\n" . "FROM isys_obj AS obj_main \n" . implode(" \n", $l_joins) . " \n\n" . "WHERE TRUE \n";

        if ($p_object_types !== null) {
            if (is_array($p_object_types)) {
                $l_sql .= 'AND obj_main.isys_obj__isys_obj_type__id ' . $this->prepare_in_condition($p_object_types) . " \n";
            } else {
                $l_sql .= 'AND obj_main.isys_obj__isys_obj_type__id = ' . $this->convert_sql_id($p_object_types) . " \n";
            }
        }

        if ($p_object_ids !== null) {
            if (is_array($p_object_ids)) {
                $l_sql .= 'AND obj_main.isys_obj__id ' . $this->prepare_in_condition($p_object_ids) . " \n";
            } else {
                $l_sql .= 'AND obj_main.isys_obj__id = ' . $this->convert_sql_id($p_object_ids) . " \n";
            }
        }

        return $l_sql;
    }

    /**
     * @param array $selectedRootProperties
     *
     * @return isys_cmdb_dao_category_property
     */
    public function setSelectedRootProperties(array $selectedRootProperties)
    {
        $this->selectedRootProperties = $selectedRootProperties;
        return $this;
    }

    /**
     * @param array $selectedLvlsProperties
     *
     * @return isys_cmdb_dao_category_property
     */
    public function setSelectedLvlsProperties(array $selectedLvlsProperties)
    {
        $this->selectedLvlsProperties = $selectedLvlsProperties;
        return $this;
    }

    /**
     * @param array $selectedConditions
     *
     * @return isys_cmdb_dao_category_property
     */
    public function setSelectedConditions(array $selectedConditions)
    {
        $this->selectedConditions = $selectedConditions;
        return $this;
    }

    /**
     * @param null $defaultSorting
     *
     * @return isys_cmdb_dao_category_property
     */
    public function setDefaultSorting($defaultSorting)
    {
        $this->defaultSorting = $defaultSorting;
        return $this;
    }

    /**
     * @param null $sortDirection
     *
     * @return isys_cmdb_dao_category_property
     */
    public function setSortDirection($sortDirection)
    {
        $this->sortDirection = $sortDirection;
        return $this;
    }

    /**
     * @param null $groupBy
     *
     * @return isys_cmdb_dao_category_property
     */
    public function setGroupBy($groupBy)
    {
        $this->groupBy = $groupBy;
        return $this;
    }

    /**
     * @param bool $value
     *
     * @return isys_cmdb_dao_category_property
     */
    public function setEmptyValues($value = true)
    {
        $this->m_empty_values = $value;
        return $this;
    }

    /**
     * @param bool $displayRelations
     *
     * @return isys_cmdb_dao_category_property
     */
    public function setDisplayRelations(bool $displayRelations)
    {
        $this->displayRelations = $displayRelations;
        return $this;
    }

    /**
     * Helper function to reformat selected Properties
     *
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_database
     */
    private function prepareEnvironmentSelectionByPost()
    {
        $this->setSelectedRootProperties(isys_format_json::decode($_POST['report__HIDDEN_IDS']));

        // We have some object references with some properties
        if (isset($_POST['lvls_raw']) && is_array($_POST['lvls_raw'])) {
            foreach ($_POST['lvls_raw'] as $l_lvl => $l_lvl_content) {
                if (is_array($l_lvl_content)) {
                    foreach ($l_lvl_content as $l_lvl_key => $l_lvl_properties) {
                        $l_lvl_properties = isys_format_json::decode($l_lvl_properties);
                        $_POST['lvls_raw__IDS'][$l_lvl][$l_lvl_key] = [];
                        if (is_array($l_lvl_properties)) {
                            foreach ($l_lvl_properties as $l_prop) {
                                $l_prop_arr = [];
                                foreach ($l_prop as $l_type => $l_cat_info) {
                                    foreach ($l_cat_info as $l_cat_const => $l_property) {
                                        foreach ($l_property as $l_title) {
                                            $l_catg_id = null;
                                            $l_cats_id = null;
                                            $l_catc_id = null;

                                            $l_prop_arr[] = $this->retrieve_properties(
                                                null,
                                                null,
                                                null,
                                                null,
                                                'AND isys_property_2_cat__cat_const = ' . $this->convert_sql_text($l_cat_const) . ' AND isys_property_2_cat__prop_key = ' .
                                                $this->convert_sql_text($l_title),
                                                true,
                                                null
                                            )
                                                ->get_row_value('id');
                                        }
                                    }
                                }
                                $_POST['lvls_raw__IDS'][$l_lvl][$l_lvl_key] = array_merge($_POST['lvls_raw__IDS'][$l_lvl][$l_lvl_key], $l_prop_arr);
                            }
                        }
                        $_POST['lvls_raw__IDS'][$l_lvl][$l_lvl_key] = isys_format_json::encode($_POST['lvls_raw__IDS'][$l_lvl][$l_lvl_key]);
                    }
                }
            }
            $l_lvls = $_POST['lvls_raw__IDS'];
            $this->reportHasSubLvls = true;

            foreach ($l_lvls as $l_key => $l_lvl) {
                if (!is_countable($l_lvl) || count($l_lvl) == 0) {
                    unset($l_lvls[$l_key]);
                } else {
                    foreach ($l_lvl as $l_prop_id => $l_properties) {
                        if (empty($l_properties)) {
                            unset($l_lvl[$l_prop_id]);
                            unset($l_lvls[$l_key][$l_prop_id]);
                        } else {
                            if (strpos($l_prop_id, '--') !== false) {
                                $l_prop_lvls = explode('--', $l_prop_id);
                                $l_new_prop_id = '';
                                foreach ($l_prop_lvls as $l_prop) {
                                    [$l_cat_const, $l_prop_key] = explode('-', $l_prop);
                                    $l_condition = 'AND isys_property_2_cat__cat_const = ' . $this->convert_sql_text($l_cat_const) . ' AND ' .
                                        'isys_property_2_cat__prop_key = ' . $this->convert_sql_text($l_prop_key) . ' ';
                                    $l_new_prop_id .= $this->retrieve_properties(null, null, null, null, $l_condition, true)
                                            ->get_row_value('id') . '--';
                                }
                                $l_lvls[$l_key][rtrim($l_new_prop_id, '--')] = $l_properties;
                                $l_new_lvls[rtrim($l_new_prop_id, '--')] = $l_properties;
                            } else {
                                [$l_cat_const, $l_prop_key] = explode('-', $l_prop_id);
                                $l_condition = 'AND isys_property_2_cat__cat_const = ' . $this->convert_sql_text($l_cat_const) . ' AND ' . 'isys_property_2_cat__prop_key = ' .
                                    $this->convert_sql_text($l_prop_key) . ' ';
                                $l_new_prop_id = $this->retrieve_properties(null, null, null, null, $l_condition, true)
                                    ->get_row_value('id');
                                $l_lvls[$l_key][$l_new_prop_id] = $l_properties;
                                $l_new_lvls[$l_new_prop_id] = $l_properties;
                            }

                            unset($l_lvls[$l_key][$l_prop_id]);
                        }
                    }
                }
            }
            $this->setSelectedLvlsProperties($l_new_lvls);
        }
    }

    /**
     * Helper function to reformat query conditions from $_POST
     */
    private function prepareEnvironmentConditionByPost()
    {
        // Unset condition template
        if (isset($_POST['querycondition']['#{queryConditionBlock}'])) {
            unset($_POST['querycondition']['#{queryConditionBlock}']);
        }

        // ID-4004: When using custom categories querycondition__HIDDEN is malformed for the specific condition so we skip it
        if (
            isset($_POST['querycondition__HIDDEN']) &&
            is_array($_POST['querycondition__HIDDEN'])
        ) {
            foreach ($_POST['querycondition__HIDDEN'] as $index => $condition) {
                foreach ($condition as $conditionIndex => $conditionValue) {
                    if ((isset($conditionValue['value']) &&
                            empty($conditionValue['value']) &&
                            !isset($conditionValue['subcnd']) &&
                            strpos($_POST['querycondition'][$index][$conditionIndex]['category'], 'C__CATG__CUSTOM_FIELDS') !== false) ||
                        $_POST['querycondition'][$index][$conditionIndex]['comparison'] === 'PLACEHOLDER'
                    ) {
                        unset($_POST['querycondition__HIDDEN'][$index]);
                    }
                }
            }

            $_POST['querycondition'] = array_replace_recursive($_POST['querycondition'], $_POST['querycondition__HIDDEN']);
        }

        // Prepare all the conditions for the query.
        if (isset($_POST['querycondition'])) {
            $conditions = $_POST['querycondition'];
            if (!isset($_POST['display_relations']) || !$_POST['display_relations']) {
                $this->addStaticRelationCondition($conditions);
            }
            $this->selectedConditions = $conditions;
        }
    }

    /**
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_database
     *
     * @return isys_cmdb_dao_category_property
     */
    public function prepareEnvironmentForReportByPost()
    {
        $this->set_query_as_report(true);
        $this->setDisplayRelations(!!$_POST['display_relations']);
        $this->setDefaultSorting($_POST['default_sorting']);
        $this->setEmptyValues(!!$_POST['empty_values']);
        $this->setSortDirection($_POST['sorting_direction']);
        if (isset($_POST['statusFilter']) && $_POST['statusFilter'] > 0) {
            $this->setMultivalueStatusFilter([(int)$_POST['statusFilter']]);
        }

        $this->setGroupBy((bool)$_POST['group_by_object']);
        $this->prepareEnvironmentSelectionByPost();
        $this->prepareEnvironmentConditionByPost();

        return $this;
    }

    /**
     * @param $data
     */
    private function prepareEnvironmentSelectionByData($data)
    {
        $l_lvls_testing = [];

        // JSON String in POST['*__COMPLETE'] from property selector
        if (is_array($data)) {
            $l_lvls_testing = $data;
        } elseif (is_string($data)) {
            $l_lvls_testing = isys_format_json::decode($data);
        }

        foreach ($l_lvls_testing as $l_key => $l_lvl_arr) {
            $l_lvl_cache = [];
            foreach ($l_lvl_arr as $l_lvl) {
                $l_cat_type = key($l_lvl);
                $l_current = current($l_lvl);
                $l_cat_const = key($l_current);
                $l_prop_key = isset($l_current[$l_cat_const][1]) && is_string($l_current[$l_cat_const][1]) ?
                    $l_current[$l_cat_const][1] : (isset($l_current[$l_cat_const][0]) && is_string($l_current[$l_cat_const][0]) ?
                        $l_current[$l_cat_const][0] : null);

                $l_prop_id = $this->retrieve_properties(
                    null,
                    null,
                    null,
                    null,
                    'AND isys_property_2_cat__cat_const = ' . $this->convert_sql_text($l_cat_const) . ' AND isys_property_2_cat__prop_key = ' .
                    $this->convert_sql_text($l_prop_key),
                    true,
                    null
                )
                    ->get_row_value('id');

                if ($l_key == 'root') {
                    $l_select_columns[] = $l_prop_id;
                } else {
                    $l_lvl_cache[] = $l_prop_id;
                }
            }
            if (count($l_lvl_cache) > 0) {
                if (strpos($l_key, '--') !== false) {
                    $l_lvl_cache_key = substr_count($l_key, '--') + 1;
                } else {
                    $l_lvl_cache_key = 1;
                }

                $l_key_arr = explode('--', $l_key);
                $l_key_string = '';
                foreach ($l_key_arr as $l_ref_key) {
                    [$l_cat_const, $l_prop_key] = explode('-', $l_ref_key);
                    $l_key_string .= $this->retrieve_properties(
                        null,
                        null,
                        null,
                        null,
                        'AND isys_property_2_cat__cat_const = ' . $this->convert_sql_text($l_cat_const) . ' AND isys_property_2_cat__prop_key = ' .
                        $this->convert_sql_text($l_prop_key),
                        true,
                        null
                    )
                        ->get_row_value('id');
                    if ($l_lvl_cache_key > 1) {
                        $l_key_string .= '--';
                    }
                }

                $l_key_string = rtrim($l_key_string, '--');
                $l_new_lvls[$l_key_string] = isys_format_json::encode($l_lvl_cache);
            }
        }

        $this->selectedRootProperties = $l_select_columns ?: [];
        $this->selectedLvlsProperties = $l_new_lvls ?: [];
    }

    /**
     * helper method to add the condition not to show any relation objects
     *
     * @param $conditions
     */
    private function addStaticRelationCondition(&$conditions)
    {
        $conditions = array_reverse($conditions);
        $conditions[] = 'AND';
        $conditions[] = [
            [
                'category'   => 'C__CATG__GLOBAL',
                'property'   => 'C__CATG__GLOBAL-type',
                'comparison' => '!=',
                'value'      => defined_or_default('C__OBJTYPE__RELATION'),
                'operator'   => 'AND'
            ],
            [
                'category'   => 'C__CATG__GLOBAL',
                'property'   => 'C__CATG__GLOBAL-type',
                'comparison' => '!=',
                'value'      => defined_or_default('C__OBJTYPE__PARALLEL_RELATION')
            ]
        ];
        $conditions = array_reverse($conditions);
    }

    /**
     * @param $conditions
     */
    private function prepareEnvironmentConditionByData($conditions)
    {
        if ($this->displayRelations === false) {
            $this->addStaticRelationCondition($conditions);
        }

        $this->selectedConditions = $conditions;
    }

    /**
     * @param $reportId
     *
     * @throws Exception
     */
    public function prepareEnvironmentForReportById($reportId)
    {
        $dao = isys_report_dao::instance(isys_application::instance()->container->get('database_system'));
        $reportData = $dao->get_report($reportId);
        $queryBuilderData = isys_format_json::decode($reportData['isys_report__querybuilder_data']);
        $rootRawProperties = $queryBuilderData['main_object'];
        $lvlRawProperties = $queryBuilderData['lvls'];
        $multivalueStatusFilter = (int)$queryBuilderData['statusFilter'];

        $reformattedProperties = [
            'root' => $rootRawProperties,
        ];

        foreach ($lvlRawProperties as $lvlRawProperty) {
            $reformattedProperties[key($lvlRawProperty)] = current($lvlRawProperty);
        }

        $this->prepareEnvironmentSelectionByData($reformattedProperties);
        $this->prepareEnvironmentConditionByData($queryBuilderData['conditions']);

        $this->set_query_as_report(true);
        $this->setEmptyValues(!!$reportData['isys_report__empty_values']);
        $this->setDisplayRelations(!!$reportData['isys_report__display_relations']);
        $this->setDefaultSorting($queryBuilderData['default_sorting']);
        $this->setSortDirection($queryBuilderData['sorting_direction']);

        if ($multivalueStatusFilter > 0) {
            $this->setMultivalueStatusFilter([$multivalueStatusFilter]);
        }

        return $this;
    }

    /**
     * Method for creating the dynamic SQL (Originally from the report editor).
     *
     * @param null|int    $p_limit        integer
     * @param null|mixed  $p_data       see hidden field *__COMPLETE in property selector
     * @param null|bool   $p_empty_values integer defines if 'LEFT' or 'INNER' join should be used
     * @param null|string $p_conditions
     *
     * @return  ReportQuery
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function create_property_query_for_report($p_limit = null, $p_data = null, $p_empty_values = null, $p_conditions = null)
    {
        if ($this->m_query_as_report === null && $p_data === null) {
            $this->prepareEnvironmentForReportByPost();
        }

        if ($p_empty_values !== null) {
            $this->setEmptyValues(!!$p_empty_values);
        }

        $this->reportQuery = (new ReportQuery())
            ->setDao(isys_cmdb_dao::instance(isys_application::instance()->container->get('database')));
        // Set Report Context @see ID-7701 and ID-7700
        Context::instance()->setContextCustomer(Context::CONTEXT_REPORT);

        $l_lvls = $l_conditions = $l_new_lvls = $l_select_columns = $l_selects = $l_lvl_selects = [];

        // Here we go through the selected categories.
        if (isset($p_data)) {
            $this->prepareEnvironmentSelectionByData($p_data);
        }

        // We don't want duplicates.
        $propertyIds = is_array($this->selectedRootProperties) ? array_unique($this->selectedRootProperties) : [];

        $propertySubLvlIds = array_unique($this->selectedLvlsProperties);

        // Prepare the property-rows.
        if (!$this->m_prepared_data_for_query_construction) {
            $this->prepare_necessary_tasks($propertyIds);
        }

        $this->reportQuery->setRootProperties($propertyIds)
            ->setSubProperties($propertySubLvlIds);

        // Prepare the select-part of the query.
        $l_selects = $this->create_property_query_select($this->selectedRootProperties, false, [], false, false, $this->groupBy);

        // Prepare the joins for our query.
        $l_joins = $this->create_property_query_join($propertyIds);

        if (count($propertySubLvlIds) > 0) {
            $l_lvls_select = $this->create_property_query_lvls_select($propertySubLvlIds, $l_selects, false, $this->groupBy);
            $l_lvls_join = $this->create_property_query_join_lvls($propertySubLvlIds, $l_selects, $l_lvls_select);

            $l_new_selection = [];

            // removing refenced field IDs
            if (is_countable($this->m_remove_from_selection) && count($this->m_remove_from_selection) > 0) {
                foreach ($this->m_remove_from_selection as $l_removed_selection) {
                    unset($l_lvls_select[$l_removed_selection]);
                }
            }

            foreach ($l_selects as $l_prop_key => $l_select_field) {
                $l_new_selection[$l_prop_key] = $l_select_field;
                $levelSelectionFound = false;
                foreach ($l_lvls_select as $l_assigned_prop => $l_lvl_select_field) {
                    $l_assigned_prop_key_arr = explode('--', $l_assigned_prop);
                    if ($l_assigned_prop_key_arr[0] == $l_prop_key) {
                        // Replace connection field from selection
                        $l_new_selection[$l_assigned_prop] = $l_lvl_select_field;
                        $levelSelectionFound = true;
                    }
                }
                // Remove index for the level selection because we do not want to show the ID
                if ($levelSelectionFound) {
                    unset($l_new_selection[$l_prop_key]);
                }
            }

            foreach ($l_lvls_join as $l_lvl_sql_join) {
                $l_joins = array_merge($l_joins, $l_lvl_sql_join);
            }
            $l_selects = $l_new_selection;
        }

        $this->reportQuery->setSelections(array_values($l_selects));

        // Prepare all the conditions for the query.
        if (!empty($this->selectedConditions)) {
            $l_conditions = $this->create_property_query_condition($this->selectedConditions);

            // First the normal joins
            if (is_array($l_conditions['joins']) && count($l_conditions['joins']) > 0) {
                foreach ($l_conditions['joins'] as $l_arr_joins) {
                    $l_joins = array_merge($l_joins, $l_arr_joins);
                }
            }
        }

        // Secondly the sub joins
        if (is_countable($this->m_sub_joins) && count($this->m_sub_joins) > 0) {
            foreach ($this->m_sub_joins as $l_arr_joins) {
                $l_joins = array_merge($l_joins, $l_arr_joins);
            }
        }

        $this->reportQuery->setJoins($l_joins)
            ->addCondition($l_conditions['conditions']);

        if ($p_conditions) {
            $this->reportQuery->addCondition($p_conditions);
        }

        $this->addOperatingSystemFilteringConditions($this->m_property_rows);
        $this->addOperatingSystemFilteringConditions($this->m_property_rows_lvls);

        $this->reportQuery->optimizeQuery();
        $this->reportQuery->buildSorting($this->defaultSorting, $this->sortDirection);
        $this->reportQuery->processDynamicFilters();

        return $this->reportQuery->setLimit($p_limit);
    }

    /**
     * @author Illia Polianskyi
     * @description Method for retireving additional query conditions for fetching single line for each non-empty operation system record
     * @param $p_properties
     */
    protected function addOperatingSystemFilteringConditions($p_properties)
    {
        $consts = [];
        $fieldsTitlesConsts = [];
        foreach ($p_properties as $l_prop_id => $l_prop_data) {
            if (!is_numeric($l_prop_id)) {
                continue;
            }
            $consts[] = $l_prop_data['const'];
            $fieldsTitlesConsts[] = $l_prop_data['title'];
        }
        if (
            (in_array('C__CATG__OPERATING_SYSTEM', $consts))
            && (!in_array('C__CATG__APPLICATION', $consts))
            && (!in_array('LC__CMDB__CATG__IT_SERVICE', $fieldsTitlesConsts))) {
            $aliasFor_isys_catg_application_list = '';
            foreach ($this->reportQuery->getJoins() as $alias => $joinString) {
                if (stripos($joinString, 'isys_catg_application_list') !== false) {
                    $aliasFor_isys_catg_application_list = $alias;
                    break;
                }
            }
            $aliasFor_isys_obj = '';
            $reachedConnectionAlias = false;
            foreach ($this->reportQuery->getJoins() as $alias => $joinString) {
                if (stripos($joinString, 'isys_catg_application_list') !== false) {
                    $reachedConnectionAlias = true;
                }
                if (($reachedConnectionAlias === true) && (stripos($joinString, 'isys_connection__isys_obj__id') !== false)) {
                    $aliasFor_isys_obj = $alias;
                    break;
                }
            }
            if ($aliasFor_isys_obj !== '') {
                $this->reportQuery->addCondition(' AND ( (' .
                    $aliasFor_isys_catg_application_list . '.isys_catg_application_list__id IS NULL) OR (' .
                    $aliasFor_isys_obj . '.isys_obj__title IS NOT NULL) OR (' .
                    $aliasFor_isys_catg_application_list . '.isys_catg_application_list__isys_catg_application_type__id = ' .
                    $this->convert_sql_int(C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM) . ') ) ');
            }
        }
    }

    /**
     * Create the selection for the referenced objects
     *
     * @param array $p_lvls
     * @param       $p_selects
     * @param bool  $p_select_status
     * @param bool  $p_leave_field_identifiers
     * @param bool  $p_group_by_object
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function create_property_query_lvls_select(array $p_lvls, $p_selects, $p_leave_field_identifiers = false, $p_group_by_object = false)
    {
        $l_selects = [];
        // Set Report Selection context @see ID-7701
        Context::instance()->setContextTechnical(Context::CONTEXT_REPORT_SELECTION);

        if ($this->m_query_as_report) {
            $l_alias_field = 'isys_obj__id';
        } else {
            $l_alias_field = 'isys_obj__title';
        }

        foreach ($p_lvls as $l_assigned_property => $l_properties) {
            $l_unformatted_properties = isys_format_json::decode($l_properties);
            $this->prepare_necessary_tasks_for_lvls_select($l_unformatted_properties, $l_assigned_property);
            $l_referenced_field_id = '';
            $l_referenced_title = '';

            if (strpos($l_assigned_property, '--') === false) {
                $l_referenced_field_id = substr($p_selects[$l_assigned_property], 0, strpos($p_selects[$l_assigned_property], ' AS '));
                $l_referenced_field_string = substr($p_selects[$l_assigned_property], strpos($p_selects[$l_assigned_property], ' AS ') + 4);
                $l_referenced_title = substr($l_referenced_field_string, 1, strpos($l_referenced_field_string, '###') - 1);
            } else {
                foreach ($l_selects as $l_select_key => $l_select_string) {
                    if ($l_select_key == $l_assigned_property) {
                        $l_referenced_field_id = substr($l_selects[$l_select_key], 0, strpos($l_selects[$l_select_key], ' AS '));
                        $l_referenced_field_string = substr($l_selects[$l_select_key], strpos($l_selects[$l_select_key], ' AS ') + 4);
                        $l_referenced_title = substr($l_referenced_field_string, 1, strpos($l_referenced_field_string, '###') - 1);
                        break;
                    }
                }
            }

            // And add the selected ones from the report-builder.
            foreach ($l_unformatted_properties as $l_select) {
                $l_field_name = '';
                $l_special_field = '';
                $l_special_selection = false;
                $l_cat = 'cats';
                $l_db_field = $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                $l_prop_key = $this->m_property_rows_lvls[$l_select]['key'];
                $const = $this->m_property_rows_lvls[$l_select]['const'];
                $l_table = $this->getTableFromProperty($this->m_property_rows_lvls[$l_select]['data']);

                if ($this->m_property_rows_lvls[$l_select]['catg'] != null) {
                    $l_cat = 'catg';
                } elseif ($this->m_property_rows_lvls[$l_select]['catg_custom'] != null) {
                    $l_cat = 'catg_custom';
                }

                $aliasTitleAddition = $l_cat . '_' . $this->m_property_rows_lvls[$l_select][$l_cat];

                if (!isset($this->m_property_rows_lvls[$l_select])) {
                    continue;
                }

                // We may have a selected property, which has no real table-fields (the dynamic properties for example).
                if ($l_referenced_field_id != '' && $this->m_property_rows_lvls[$l_select]['type'] == C__PROPERTY_TYPE__DYNAMIC && $this->m_query_as_report) {
                    $callbackObj = $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0];
                    $l_callback_class = (is_object($callbackObj)) ? get_class($callbackObj) : $callbackObj;

                    if (strpos($l_callback_class, 'isys_cmdb_dao_category_') === 0) {
                        $fieldAlias = $l_callback_class . '::' .
                            $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] . '::%s::' .
                            $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE] . "#" . $l_referenced_title . "###" .
                            $aliasTitleAddition;

                        if ($l_db_field == 'isys_obj__id') {
                            $l_field = str_replace('isys_obj__title', 'isys_obj__id', $l_referenced_field_id);
                            $l_selects[$l_assigned_property . '--' . $l_select] = $l_field . ' AS \'' . sprintf($fieldAlias, $l_db_field) . '\'';
                            continue;
                        }

                        if (strpos($l_db_field, '_isys_obj__id') !== false) {
                            $l_field = $l_referenced_field_id;
                            $l_selects[$l_assigned_property . '--' . $l_select] = $l_field . ' AS \'' . sprintf($fieldAlias, $l_db_field) . '\'';
                            continue;
                        }

                        if (isset($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN]) &&
                            count($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN])
                        ) {
                            $propertyJoins = $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN];
                            $referenceTable = $propertyJoins[0]->getTable();

                            if ($referenceTable === $l_table) {
                                $l_table = null;
                            }

                            $alias = 'j' . $this->retrieve_alias_lvls($referenceTable, $l_table, $l_assigned_property, $const);

                            if ($const === 'C__CATG__GLOBAL' &&
                                $referenceTable === 'isys_obj' &&
                                strpos($l_referenced_field_id, '.isys_obj__id') !== false
                            ) {
                                [$alias] = explode('.', $l_referenced_field_id);
                            }

                            $l_field = $alias . '.' . $l_db_field;
                        } else {
                            $l_field = 'j' . $this->retrieve_alias_lvls('isys_obj', $l_table, $l_assigned_property, $const) . '.' . $l_db_field;
                        }

                        $l_selects[$l_assigned_property . '--' . $l_select] = $l_field . ' AS \'' . sprintf($fieldAlias, $l_db_field) . '\'';
                    }
                    continue;
                } elseif ($this->m_property_rows_lvls[$l_select]['type'] == C__PROPERTY_TYPE__DYNAMIC) {
                    if (isset($l_db_field) && $l_table !== 'isys_obj') {
                        $l_field_alias = '';
                        if (isset($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) {
                            $l_field_alias = ' AS ' . $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
                        }

                        $l_selects[$l_select] = 'j' . $this->retrieve_alias_lvls('isys_obj', $l_table, $l_assigned_property, $const) . '.' . $l_db_field . $l_field_alias;
                    }
                    continue;
                }

                if ($l_table == 'isys_catg_custom_fields_list') {
                    $l_field_alias = $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
                    $l_field_type = $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE];
                    $uiParams = $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS];
                    $l_alias = $this->retrieve_alias_lvls($l_field_alias . '#isys_catg_custom_fields_list', null, $l_assigned_property, $const);
                    $l_output_alias = "'" . $this->m_property_rows_lvls[$l_select]['title'] . "#" . $l_referenced_title . "###" .
                        $aliasTitleAddition . "'";

                    switch ($l_field_type) {
                        case C__PROPERTY__INFO__TYPE__OBJECT_BROWSER:
                        case C__PROPERTY__INFO__TYPE__N2M:
                            $l_alias_sec = $this->retrieve_alias_lvls('isys_catg_custom_fields_list', $l_field_alias . '#isys_obj', $l_assigned_property, $const);
                            $l_field = 'j' . $l_alias_sec . '.isys_obj__title AS ' . $l_output_alias;
                            break;
                        case C__PROPERTY__INFO__TYPE__DIALOG:
                        case C__PROPERTY__INFO__TYPE__DIALOG_PLUS:
                        case C__PROPERTY__INFO__TYPE__MULTISELECT:

                            if (isset($uiParams['extra']) && $uiParams['extra'] === 'yes-no') {
                                $contentField = 'j' . $l_alias . '.' . $l_db_field;
                                $l_field = '(CASE WHEN ' . $contentField . ' < 0 THEN ' . $this->convert_sql_text(isys_tenantsettings::get('gui.empty_value', '-')) . ' ELSE ' . $contentField . ' END) AS ' . $l_output_alias;
                                break;
                            }

                            $l_alias_sec = $this->retrieve_alias_lvls('isys_catg_custom_fields_list', $l_field_alias . '#isys_dialog_plus_custom', $l_assigned_property, $const);
                            $l_field = 'j' . $l_alias_sec . '.isys_dialog_plus_custom__title AS ' . $l_output_alias;
                            break;
                        case C__PROPERTY__INFO__TYPE__DATE:
                        case C__PROPERTY__INFO__TYPE__DATETIME:
                            // See ID-2992
                            $l_field = 'j' . $l_alias . '.' . $l_db_field . ' AS ' . '\'locales::fmt_date::' . ltrim($l_output_alias, "'");
                            break;
                        default:
                            $l_field = 'j' . $l_alias . '.' . $l_db_field . ' AS ' . $l_output_alias;
                            break;
                    }

                    $l_selects[$l_assigned_property . '--' . $l_select] = $l_field;
                    continue;
                }

                // First we check for some special selected fields.
                if (isset($this->m_special_selects[$l_db_field])) {
                    // Check if its a primary field
                    if ($p_group_by_object && $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__INFO][C__PROPERTY__INFO__PRIMARY]) {
                        $l_sp_select = $this->m_special_selects[$l_db_field];
                        $l_sp_select = 'GROUP_CONCAT(DISTINCT ' . substr($l_sp_select, 0, strpos($l_sp_select, 'AS')) . ') ' .
                            substr($l_sp_select, strpos($l_sp_select, 'AS'), strlen($l_sp_select));
                        $l_selects[$l_assigned_property . '--' . $l_select] = $l_sp_select;
                        continue;
                    } elseif (is_array($this->m_special_selects[$l_db_field])) {
                        $l_special_selection = true;
                        $l_table = $this->m_special_selects[$l_db_field][0];
                        $l_special_field = (strpos($l_db_field, $l_table)) ? $l_db_field : $this->m_special_selects[$l_db_field][2];

                        $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] = $this->m_special_selects[$l_db_field][1];

                        if (empty($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES])) {
                            $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES] = [];
                        }

                        if ($l_table == 'isys_obj' && !$this->m_query_as_report) {
                            $l_special_field = 'isys_obj__title';
                            $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES] =
                                array_replace($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES], [$this->m_special_selects[$l_db_field][0]]);
                            $l_table = $this->m_special_selects[$l_db_field][1];
                        } else {
                            $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES] =
                                array_replace($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES], [$this->m_special_selects[$l_db_field][1]]);
                        }
                    } else {
                        continue;
                    }
                }

                // We might have a dialog- or object-browser field and want to handle it properly.
                $l_ui_type = $this->retrieve_ui_type($this->m_property_rows_lvls[$l_select]['data']);

                $localesMethod = '';

                switch ($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE]) {
                    case Property::C__PROPERTY__INFO__TYPE__DATETIME:
                        $localesMethod = 'locales::fmt_datetime::';
                        break;
                    case Property::C__PROPERTY__INFO__TYPE__DATE:
                        $localesMethod = 'locales::fmt_date::';
                        break;
                    case Property::C__PROPERTY__INFO__TYPE__TIMEPERIOD:
                        $localesMethod = 'locales::fmt_time::';
                        break;
                    case Property::C__PROPERTY__INFO__TYPE__MONEY:
                        $localesMethod = 'locales::fmt_monetary::';
                        break;
                    default:
                        break;
                }

                $outputAlias = (!$p_leave_field_identifiers ? " AS '" . $localesMethod . $this->m_property_rows_lvls[$l_select]['title'] . "#" . $l_referenced_title . "###" .
                    $aliasTitleAddition . "'" : $l_field_name);

                if (($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] !== null &&
                        substr($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 0, 5) == 'isys_') ||
                    $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_location') {
                    $l_alias = $this->retrieve_alias_lvls(
                        $l_table,
                        $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0],
                        $l_assigned_property,
                        $const
                    );

                    // We have to join 'job' on references to isys_connection.
                    if ($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == 'isys_connection') {
                        $l_selects[$l_assigned_property . '--' . $l_select] = "job" . $l_alias . "." . $l_alias_field . $outputAlias;
                    } elseif ($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_location') {
                        $l_alias = $this->retrieve_alias_lvls($l_table, 'isys_obj', $l_assigned_property, $const);
                        $l_selects[$l_assigned_property . '--' . $l_select] = "j" . $l_alias . "." . $l_alias_field . $outputAlias;
                    } elseif ($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == 'isys_contact') {
                        $l_alias = $this->retrieve_alias_lvls('isys_obj', 'isys_contact_2_isys_obj', $l_assigned_property, $const);
                        $l_selects[$l_assigned_property . '--' . $l_select] = "job" . $l_alias . "." . $l_alias_field . $outputAlias;
                    } else {
                        if ($l_special_selection && $l_special_field != '') {
                            $l_selects[$l_assigned_property . '--' . $l_select] = "j" . $l_alias . "." . $l_special_field . $outputAlias;
                        } elseif ($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__OBJECT_BROWSER) {
                            $l_object_field = $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . "__isys_obj__id";
                            if ($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == 'isys_obj') {
                                $l_object_field = 'isys_obj__id';
                            }
                            $l_selects[$l_assigned_property . '--' . $l_select] = "j" . $l_alias . "." . $l_object_field . $outputAlias;
                        } else {
                            $l_selects[$l_assigned_property . '--' . $l_select] = "j" . $l_alias . "." .
                                $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . "__title" . $outputAlias;
                        }

                        if (isset($this->m_text_fields[$this->m_property_rows_lvls[$l_select]['const']])) {
                            if (isset($this->m_text_fields[$this->m_property_rows_lvls[$l_select]['const']][$this->m_property_rows_lvls[$l_select]['key']])) {
                                $l_selects[$l_assigned_property . '--' . $l_select] = substr(
                                    $l_selects[$l_assigned_property . '--' . $l_select],
                                    0,
                                    (strpos($l_selects[$l_assigned_property . '--' . $l_select], '.') + 1)
                                ) .
                                    $this->m_text_fields[$this->m_property_rows_lvls[$l_select]['const']][$this->m_property_rows_lvls[$l_select]['key']] .
                                    (!$p_leave_field_identifiers ? " AS '" . $this->m_property_rows_lvls[$l_select]['title'] . "###" .
                                        $aliasTitleAddition . "'" : $l_field_name);
                            }
                        }
                    }
                } elseif ($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__OBJECT_BROWSER) {
                    if ($l_db_field == $l_table . '__isys_obj__id') {
                        if (isset($this->m_aliases_lvls[$const . '::' . 'isys_connection#' . $l_table . '#' . $l_assigned_property])) {
                            $l_alias = "j" . $this->retrieve_alias_lvls('isys_connection', $l_table, $l_assigned_property, $const);

                            $l_selects[$l_assigned_property . '--' . $l_select] = $l_alias . "." . $l_db_field . $outputAlias;
                        }
                    } elseif (strpos($l_db_field, 'isys_cable_connection__id') !== false) {
                        if (isset($this->m_aliases_lvls[$const . '::' . 'isys_obj#isys_cable_connection#' . $l_assigned_property])) {
                            $l_alias = 'j' . $this->m_aliases_lvls[$const . '::' . 'isys_obj#isys_cable_connection#' . $l_assigned_property];
                            $l_selects[$l_assigned_property . '--' . $l_select] = $l_alias . "." . $l_alias_field . $outputAlias;
                        }
                    } elseif (strpos($l_db_field, 'isys_catg_connector_list__id') !== false) {
                        if ($l_table != 'isys_catg_connector_list') {
                            if (isset($this->m_aliases_lvls[$const . '::' . $l_table . '#isys_catg_connector_list#isys_obj#' . $l_assigned_property])) {
                                $l_alias = 'j' . $this->m_aliases_lvls[$const . '::' . $l_table . '#isys_catg_connector_list#isys_obj#' . $l_assigned_property];
                                $l_selects[$l_assigned_property . '--' . $l_select] = $l_alias . "." . $l_alias_field . $outputAlias;
                            }
                        } else {
                            if (isset($this->m_aliases_lvls[$const . '::' . 'isys_obj#isys_catg_connector_list#' . $l_assigned_property])) {
                                $l_alias = 'j' . $this->m_aliases_lvls[$const . '::' . 'isys_obj#isys_catg_connector_list#' . $l_assigned_property];
                                $l_selects[$l_assigned_property . '--' . $l_select] = $l_alias . "." . $l_alias_field . $outputAlias;
                            }
                        }
                    }
                } else {
                    $l_alias = 'j' . $this->retrieve_alias_lvls($l_table, null, $l_assigned_property, $const);

                    // Then we check for special table-names inside the select.
                    if (isset($this->m_aliases_lvls[$const . '::' . $l_table])) {
                        $l_alias = 'j' . $this->m_aliases_lvls[$const . '::' . $l_table];
                    } elseif ($l_table == 'isys_logbook') {
                        $l_alias = 'j' . $this->retrieve_alias_lvls('isys_catg_logb_list', 'isys_logbook', $l_assigned_property, $const);
                    }

                    // If we got a "yes/no" dialog-field we want to display the result as such.
                    if ($l_ui_type == C__PROPERTY__UI__TYPE__DIALOG &&
                        $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'get_yes_or_no') {
                        $l_selects[$l_assigned_property . '--' . $l_select] = "(CASE " . $l_alias . "." . $l_db_field . " " .
                            "WHEN 0 THEN 'LC__UNIVERSAL__NO' WHEN 1 THEN 'LC__UNIVERSAL__YES' END)" .
                            (!$p_leave_field_identifiers ? " AS '" . $this->m_property_rows_lvls[$l_select]['title'] . "#" . $l_referenced_title . "###" .
                                $aliasTitleAddition . "'" : $l_db_field);
                    } elseif (!empty($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']) &&
                        !in_array($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1], self::$m_ignored_format_callbacks)) {
                        $data = $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'];
                        if (is_object($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']) &&
                            get_class($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']) == 'isys_callback') {
                            $data = $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']->execute();
                        }

                        if (is_array($data)) {
                            if (count($data) > 0) {
                                $l_selects_dialog = "(CASE " . $l_alias . "." . $l_db_field . " ";
                                foreach ($data as $l_key => $l_value) {
                                    if (is_array($l_value)) {
                                        if (isset($l_value['id'])) {
                                            $l_selects_dialog .= "WHEN " . $this->convert_sql_text($l_key) . " THEN " . $this->convert_sql_text($l_value['id']) . " ";
                                        } elseif (isset($l_value['value'])) {
                                            $l_selects_dialog .= "WHEN " . $this->convert_sql_text($l_key) . " THEN " . $this->convert_sql_text($l_value['value']) . " ";
                                        } else {
                                            $l_selects_dialog .= "WHEN " . $this->convert_sql_text($l_key) . " THEN " . isys_tenantsettings::get('gui.empty_value', '-') . " ";
                                        }
                                    } else {
                                        $l_selects_dialog .= "WHEN " . $this->convert_sql_text($l_key) . " THEN " . $this->convert_sql_text($l_value) . " ";
                                    }
                                }
                                $l_selects_dialog .= "END) ";
                            } else {
                                // No values found
                                $l_selects_dialog = " '" . isys_tenantsettings::get('gui.empty_value', '-') . "' ";
                            }

                            $l_selects_dialog .= (!$p_leave_field_identifiers ? " AS '" . $this->m_property_rows_lvls[$l_select]['title'] . "#" . $l_referenced_title . "###" .
                                $aliasTitleAddition . "'" : $l_db_field);
                            $l_selects[$l_assigned_property . '--' . $l_select] = $l_selects_dialog;
                        }
                    } else {
                        $l_selection = $l_alias . "." . $l_db_field;

                        if (isset($this->m_property_wrap_functions[$l_prop_key])) {
                            $l_selection = $this->wrap_mysql_function($l_prop_key, $l_selection);
                        }
                        $l_selects[$l_assigned_property . '--' . $l_select] = $l_selection . $outputAlias;
                    }
                }
            }
        }

        return $l_selects;
    }

    /**
     * Create the joins for the referenced objects
     *
     * @param array $p_lvls
     * @param array $p_selects
     * @param array $p_lvl_selects
     * @param bool  $p_from_condition
     * @param null  $p_assigned_field
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function create_property_query_join_lvls(array $p_lvls, &$p_selects = [], &$p_lvl_selects = [], $p_from_condition = false, $p_assigned_field = null)
    {
        $l_return = [];

        if ($this->m_empty_values) {
            $l_join_type = "LEFT";
        } else {
            $l_join_type = "INNER";
        }

        $l_parent_custom_field = [];
        $l_referenced_field = '';

        foreach ($p_lvls as $l_assigned_property => $l_properties) {
            $l_unformatted_properties = (isys_format_json::is_json($l_properties)) ? isys_format_json::decode($l_properties) : $l_properties;
            $l_already_joined_tables = $l_joins = [];
            $l_assigned_property = (string)$l_assigned_property;

            // We need this array to save "already joined" tables for saving a bit of performance.
            if (isset($this->m_referenced_fields[$l_assigned_property])) {
                $l_join_condition_field = $this->m_referenced_fields[$l_assigned_property];

                if (!in_array($l_assigned_property, $this->m_remove_from_selection)) {
                    $this->m_remove_from_selection[] = $l_assigned_property;
                }
            } elseif ($p_assigned_field !== null) {
                $l_join_condition_field = $p_assigned_field;
            } else {
                if (strpos($l_assigned_property, '--') === false && !$p_from_condition) {
                    $l_join_condition_field = substr($p_selects[$l_assigned_property], 0, strpos($p_selects[$l_assigned_property], ' AS'));
                    if (strpos($l_join_condition_field, 'isys_obj__title') !== false) {
                        $l_join_condition_field = str_replace('title', 'id', $l_join_condition_field);
                    }
                } else {
                    $l_join_condition_field = str_replace('title', 'id', substr($p_lvl_selects[$l_assigned_property], 0, strpos($p_lvl_selects[$l_assigned_property], ' AS')));
                }

                if (!in_array($l_assigned_property, $this->m_remove_from_selection)) {
                    $this->m_remove_from_selection[] = $l_assigned_property;
                }
            }

            // Now we create the single JOIN's.
            foreach ($this->m_property_rows_lvls as $l_prop_id => $l_prop_data) {
                if (!is_array($l_prop_data)) {
                    continue;
                }

                // We won't handle dynamic properties here.
                if ($l_prop_data['type'] == C__PROPERTY_TYPE__DYNAMIC && (!isset($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]) || strpos($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD], '_isys_obj__id') !== false)) {
                    continue;
                }

                $l_table = null;

                // We are only able to JOIN with loaded data.
                if (in_array($l_prop_id, $l_unformatted_properties)) {
                    $l_alias_sec = $l_alias_third = $l_alias_fourth = $l_alias_obj = null;
                    $const = $l_prop_data['const'];

                    if ($l_prop_data['data'] instanceof Property) {
                        if ($l_prop_data['data']->getData()->offsetExists(Property::C__PROPERTY__DATA__SOURCE_TABLE) &&
                            !$l_prop_data['data']->getData()->offsetExists(Property::C__PROPERTY__DATA__REFERENCES)) {
                            $l_table = $l_prop_data['data']->getData()->getSourceTable();
                        }
                    }

                    if ($l_table === null && $l_prop_data['type'] == C__PROPERTY_TYPE__DYNAMIC) {
                        $l_table = $l_prop_data['table'];
                    }

                    if ($l_table === null) {
                        $l_table = current(explode('__', $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]));
                    }

                    if ($l_table == 'isys_catg_custom_fields_list') {
                        $l_field_alias = $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
                        $uiParams = $l_prop_data['data'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS];
                        $l_field_key = (strpos($l_field_alias, 'C__CMDB__CAT__COMMENTARY') !== false) ? $l_field_alias : substr(
                            $l_field_alias,
                            strpos($l_field_alias, '_c_') + 1,
                            strlen($l_field_alias)
                        );
                        $l_field_type = $l_prop_data['data'][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE];
                        $l_alias = 'j' . $this->retrieve_alias_lvls($l_field_alias . '#isys_catg_custom_fields_list', null, $l_assigned_property, $const);

                        if (in_array($l_alias, $this->m_already_used_aliase)) {
                            continue;
                        }

                        if ($l_field_type == C__PROPERTY__INFO__TYPE__DIALOG || $l_field_type == C__PROPERTY__INFO__TYPE__DIALOG_PLUS ||
                            $l_field_type == C__PROPERTY__INFO__TYPE__MULTISELECT) {
                            $l_join_type = 'LEFT';
                        } elseif (!$this->m_empty_values && $l_join_type == 'LEFT') {
                            $l_join_type = "INNER";
                        }

                        $l_join_string = ' LEFT JOIN ' . $l_table . ' AS ' . $l_alias . ' ON ' . $l_join_condition_field . ' = ' . $l_alias . '.' . $l_table .
                            '__isys_obj__id AND ' . $l_alias . '.' . $l_table . '__field_key = ' . $this->convert_sql_text($l_field_key) . ' ';

                        $l_identifier = (($p_assigned_field !== null) ? str_replace('.', '', $p_assigned_field) . '--' : '') . $l_prop_data['const'];

                        if (empty($l_parent_custom_field[$l_assigned_property . '-' . $l_identifier])) {
                            $l_description_alias = $l_assigned_property . '__' . str_replace('-', '_', $l_identifier);
                            $l_parent_custom_field[$l_assigned_property . '-' . $l_identifier] = $l_description_alias . '.' . $l_table . '__data__id';

                            $l_join_string = $l_join_type . ' JOIN ' . $l_table . ' AS ' . $l_description_alias . ' ON ' . $l_join_condition_field . ' = ' . $l_description_alias . '.' .
                                $l_table . '__isys_obj__id
                                AND ' . $l_description_alias . '.' . $l_table .
                                '__isysgui_catg_custom__id = (SELECT isysgui_catg_custom__id FROM isysgui_catg_custom WHERE isysgui_catg_custom__const = ' .
                                $this->convert_sql_text($l_prop_data['const']) . ')
                                AND ' . $l_description_alias . '.' . $l_table . '__field_type = ' . $this->convert_sql_text('commentary') . ' ' . $l_join_string . '
                                AND ' . $l_alias . '.' . $l_table . '__data__id = ' . $l_parent_custom_field[$l_assigned_property . '-' . $l_identifier] . ' ';
                        } else {
                            $l_join_string .= ' AND ' . $l_alias . '.' . $l_table . '__data__id = ' . $l_parent_custom_field[$l_assigned_property . '-' . $l_identifier] . ' ';
                        }
                        $l_joins[$l_alias] = str_replace('--', '__', $l_join_string) . ' ' . $this->addMultivalueStatusFilter($l_alias, $l_table, $l_prop_data['multi']);

                        switch ($l_field_type) {
                            case C__PROPERTY__INFO__TYPE__OBJECT_BROWSER:
                            case C__PROPERTY__INFO__TYPE__N2M:
                                $l_alias_sec = 'j' . $this->retrieve_alias_lvls('isys_catg_custom_fields_list', $l_field_alias . '#isys_obj', $l_assigned_property, $const);
                                if (in_array($l_alias_sec, $this->m_already_used_aliase)) {
                                    continue; // phpcs:ignore
                                }

                                $l_joins[$l_alias_sec] = 'LEFT JOIN isys_obj AS ' . $l_alias_sec . ' ON ' . $l_alias . '.' . $l_table . '__field_content = ' . $l_alias_sec .
                                    '.isys_obj__id ';

                                $l_referenced_field = $l_alias_sec . '.isys_obj__id ';
                                break;
                            case C__PROPERTY__INFO__TYPE__DIALOG:
                            case C__PROPERTY__INFO__TYPE__DIALOG_PLUS:
                            case C__PROPERTY__INFO__TYPE__MULTISELECT:

                                if (isset($uiParams['extra']) && $uiParams['extra'] === 'yes-no') {
                                    break;
                                }

                                $l_alias_sec = 'j' .
                                    $this->retrieve_alias_lvls('isys_catg_custom_fields_list', $l_field_alias . '#isys_dialog_plus_custom', $l_assigned_property, $const);

                                $l_joins[$l_alias_sec] = 'LEFT JOIN isys_dialog_plus_custom AS ' . $l_alias_sec . ' ON ' . $l_alias . '.' . $l_table . '__field_content = ' .
                                    $l_alias_sec . '.isys_dialog_plus_custom__id ';
                                break;
                            default:
                                break;
                        }

                        if ($l_alias !== null) {
                            $this->m_already_used_aliase[] = $l_alias;
                        }
                        if ($l_alias_sec !== null) {
                            $this->m_already_used_aliase[] = $l_alias_sec;
                        }
                        continue;
                    }

                    // We have to check for an existing "predefined" alias.
                    if (isset($this->m_aliases_lvls[$const . '::' . $l_table])) {
                        $l_alias = 'j' . $this->m_aliases_lvls[$const . '::' . $l_table];
                    } else {
                        $l_alias = 'j' . $this->retrieve_alias_lvls($l_table, null, $l_assigned_property, $const);
                    }

                    $daoClass = $l_prop_data['class'];
                    $propertyField = $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                    $specialJoinKey = $daoClass . '::' . $propertyField;
                    $specialJoinKeyWildcard = $daoClass . '::*';

                    if (isset($this->m_special_joins[$l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]]) ||
                        isset($this->m_special_joins[$specialJoinKey]) || isset($this->m_special_joins[$specialJoinKeyWildcard])) {
                        $specialJoinData = $this->m_special_joins[$specialJoinKey] ?: $this->m_special_joins[$l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];

                        foreach ($specialJoinData as $l_key => $l_spec_join) {
                            $l_table = $l_spec_join[0];
                            $l_ref_table = $l_spec_join[1];
                            $l_ref_field = $l_spec_join[2];
                            $l_ref_ref_field = $l_spec_join[3];
                            $l_alias_sec = '';

                            $l_alias = "j" . $this->retrieve_alias_lvls($l_ref_table, $l_table, $l_assigned_property, $const);
                            if (in_array($l_alias, $this->m_already_used_aliase) || $l_alias == 'j' || ($l_key === 0 && $l_table === 'isys_connection')) {
                                // Alias is not set for this join just skip it
                                continue;
                            }

                            foreach ($this->m_aliases_lvls as $l_alias_key => $l_value) {
                                $l_cache_key = explode('#', $l_alias_key);
                                [$aliasConst, $aliasTable] = explode('::', $l_cache_key[0]);
                                if ($aliasTable == $l_ref_table && $l_cache_key[1] != $l_table) {
                                    $l_alias_tables = explode('#', $l_alias_key);
                                    $l_alias_sec = "j" . $this->retrieve_alias_lvls($l_alias_tables[1], $aliasTable, $l_assigned_property, $aliasConst);

                                    if ($l_alias_sec !== 'j') {
                                        break;
                                    }
                                }
                            }

                            // In Case the alias could not be retrieved
                            if ($l_alias_sec === '' || $l_alias_sec === 'j') {
                                foreach ($this->m_aliases_lvls as $l_alias_key => $l_value) {
                                    $l_cache_key = explode('#', $l_alias_key);
                                    [$aliasConst, $aliasTable] = explode('::', $l_cache_key[0]);
                                    if ($aliasTable == $l_ref_table && $l_cache_key[1] == $l_table) {
                                        $l_alias_sec = "j" . $this->retrieve_alias_lvls($l_cache_key[1], $aliasTable, $l_assigned_property, $aliasConst);

                                        if ($l_alias_sec !== 'j' && $l_alias_sec !== $l_alias) {
                                            break;
                                        }
                                    }
                                }
                            }

                            if (!in_array($l_ref_table . '#' . $l_table . '#' . $l_assigned_property, $l_already_joined_tables)) {
                                $l_already_joined_tables[] = $l_ref_table . '#' . $l_table . '#' . $l_assigned_property;
                                $this->m_already_used_aliase[] = $l_alias;
                                if ($l_key == 0 && ($p_assigned_field !== null || $l_join_condition_field !== null) && $l_ref_table == 'isys_obj') {
                                    if ($p_assigned_field !== null) {
                                        $l_assigned_field = explode('.', $p_assigned_field);
                                    } else {
                                        $l_assigned_field = explode('.', $l_join_condition_field);
                                    }

                                    $this->m_already_used_aliase[] = $l_assigned_field[0];

                                    $l_join = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " .
                                        $l_assigned_field[0] . '.' . $l_assigned_field[1] . ' = ' . $l_alias . '.' . $l_ref_field;
                                    $l_referenced_field = $p_assigned_field;
                                } elseif ($l_ref_table === 'isys_connection') {
                                    $l_join = $l_join_type . ' JOIN ' . $l_table . ' AS ' . $l_alias . ' ON ' . $l_alias . '.' . $l_ref_field . ' IN (
                                        SELECT isys_connection__id FROM isys_connection WHERE isys_connection__isys_obj__id = ' . $l_join_condition_field . '
                                    ) ';
                                    $l_referenced_field = $l_alias . "." . $l_ref_field;
                                } else {
                                    $l_join = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias_sec .
                                        '.' . $l_ref_ref_field . ' = ' . $l_alias . '.' . $l_ref_field;
                                    $l_referenced_field = $l_alias . "." . $l_ref_field;
                                }
                                $l_join .= $this->add_join_condition($l_prop_data['data'][C__PROPERTY__DATA], $l_table, $l_alias);

                                if ($l_table != 'isys_connection' && $l_table != 'isys_obj' && !strpos($l_table, '_2_')) {
                                    $l_join .= $this->addMultivalueStatusFilter($l_alias, $l_table, $l_prop_data['multi']);
                                }

                                $l_joins[$l_alias] = $l_join;
                            }
                        }
                        if (!isset($this->m_referenced_fields[$l_assigned_property . '--' . $l_prop_data['const'] . '-' . $l_prop_data['key']])) {
                            $this->m_referenced_fields[$l_assigned_property . '--' . $l_prop_data['const'] . '-' . $l_prop_data['key']] = $l_referenced_field;
                            $this->m_referenced_fields[$l_assigned_property . '--' . $l_prop_id] = $l_referenced_field;
                        }

                        if ($l_table !== 'isys_obj') {
                            $l_alias = 'j' . $this->retrieve_alias_lvls($l_table, 'isys_obj', $l_assigned_property, $const);

                            if (in_array($l_alias, $this->m_already_used_aliase) || strpos($l_referenced_field, 'isys_obj__id') === false) {
                                continue;
                            }

                            $l_joins[$l_alias] = $l_join_type . " JOIN isys_obj AS " . $l_alias . " ON " . $l_alias . '.isys_obj__id = ' . $l_referenced_field;

                            $this->m_referenced_fields[$l_assigned_property . '--' . $l_prop_data['const'] . '-' . $l_prop_data['key']] = $l_alias . '.isys_obj__id';
                            $this->m_referenced_fields[$l_assigned_property . '--' . $l_prop_id] = $l_alias . '.isys_obj__id';
                            $this->m_already_used_aliase[] = $l_alias;
                        }

                        continue;
                    }

                    // If we have a reference table, we have to join it.
                    if (($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] !== null &&
                            substr($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 0, 5) == 'isys_') ||
                        $l_prop_data['data'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_location') {
                        $l_alias_sec = 'j' . $this->retrieve_alias_lvls($l_table, $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], $l_assigned_property, $const);
                        $l_alias_third = 'job' . $this->retrieve_alias_lvls($l_table, $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], $l_assigned_property, $const);

                        if (in_array($l_alias_sec, $this->m_already_used_aliase)) {
                            continue;
                        }

                        $l_already_joined_tables[] = $l_table . '#isys_obj#' . $l_assigned_property;

                        if ($l_table == 'isys_obj') {
                            $l_alias_obj = "j" . $this->retrieve_alias_lvls($l_table, null, $l_assigned_property, $const);
                            $l_obj_field = $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];

                            if (!in_array($l_alias_obj, $this->m_already_used_aliase)) {
                                [$l_alias_obj, $l_obj_field] = explode('.', $l_join_condition_field);

                                if (strpos($l_obj_field, '__isys_obj') !== false) {
                                    if (strpos($l_alias_obj, 'job') !== false) {
                                        $l_check_alias = str_replace('job', '', $l_alias_obj);
                                        $l_alias_key_arr = explode('#', array_search($l_check_alias, $this->m_aliases_lvls));
                                        [$aliasConst, $aliasTable] = explode('::', $l_alias_key_arr[0]);
                                        $l_alias_obj = 'job' . $this->retrieve_alias_lvls($aliasTable, $l_alias_key_arr[1], $l_alias_key_arr[2], $aliasConst);
                                    } else {
                                        $l_check_alias = str_replace('j', '', $l_alias_obj);
                                        $l_alias_key_arr = explode('#', array_search($l_check_alias, $this->m_aliases_lvls));
                                        [$aliasConst, $aliasTable] = explode('::', $l_alias_key_arr[0]);
                                        $l_alias_obj = 'j' . $this->retrieve_alias_lvls($aliasTable, $l_alias_key_arr[1], $l_alias_key_arr[2], $aliasConst);
                                    }
                                    if ($l_alias_obj === 'j' || $l_alias_obj === 'job') {
                                        // Search Alias in m_aliases
                                        $l_index_arr = explode('::', array_search($l_check_alias, $this->m_aliases));
                                        foreach ($this->m_aliases as $key => $value) {
                                            if (strpos($key, $l_index_arr[0]) !== false) {
                                                $indexData[] = $value;
                                            }
                                        }
                                        $l_alias_obj .= array_pop($indexData);
                                    }
                                }
                                if (strpos($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD], 'isys_obj_type__id') !== false) {
                                    $l_obj_field = $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                                }
                            }

                            $l_joins[$l_alias_sec] = $l_join_type . " JOIN " . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . " AS " . $l_alias_sec .
                                " ON " . $l_alias_sec . "." . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . "__id = " . $l_alias_obj . '.' .
                                $l_obj_field;

                            $l_referenced_field = $l_alias_obj . '.' . $l_obj_field;
                        } elseif ($l_prop_data['data'][C__PROPERTY__INFO][C__PROPERTY__INFO__BACKWARD] === true) {
                            if ($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == "isys_connection") {
                                if (in_array($l_alias_sec, $this->m_already_used_aliase)) {
                                    continue;
                                }

                                if (!in_array($l_alias, $this->m_already_used_aliase)) {
                                    // Join the category table (isys_catg_XXXX_list).
                                    $l_joins[$l_alias] = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias . "." . $l_table . "__" .
                                        $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__id IN (
                                            SELECT isys_connection__id FROM isys_connection WHERE isys_connection__isys_obj__id = ' . $l_join_condition_field . '
                                        ) '. $this->addMultivalueStatusFilter($l_alias, $l_table, $l_prop_data['multi']);
                                }

                                // Join the object table (isys_obj).
                                $l_joins[$l_alias_third] = "INNER JOIN isys_obj AS " . $l_alias_third . " ON " . $l_alias . "." . $l_table . "__isys_obj__id = " . $l_alias_third .
                                    '.isys_obj__id ';
                            } else {
                                $this->handleBackwardProperty($l_prop_data, $l_joins, $l_join_condition_field, $l_join_type, [$l_alias, $l_alias_sec, $l_alias_third]);
                            }
                        } elseif ($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == "isys_connection") {
                            if (!in_array($l_alias, $this->m_already_used_aliase)) {
                                // Join the category table (isys_catg_XXXX_list).
                                $l_joins[$l_alias] = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias . "." . $l_table . "__isys_obj__id = " .
                                    $l_join_condition_field . ' ' . $this->addMultivalueStatusFilter($l_alias, $l_table, $l_prop_data['multi']);
                            }

                            // Join the connection table (isys_connection).
                            $joinSecond = $l_join_type . " JOIN " . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . " AS " . $l_alias_sec .
                                " ON " . $l_alias_sec . "." . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . "__id = " . $l_alias . '.' .
                                $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];

                            $joinSecond .= $this->addCategorySpecificCondition($const, $l_alias, $l_table);

                            $l_joins[$l_alias_sec] = $joinSecond;

                            // Join the object table (isys_obj).
                            $l_joins[$l_alias_third] = $l_join_type . " JOIN isys_obj AS " . $l_alias_third. " ON " . $l_alias_sec . "." .
                                $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . "__isys_obj__id = " . $l_alias_third . '.isys_obj__id';

                            $l_referenced_field = $l_alias_third . ".isys_obj__id";
                        } elseif ($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == 'isys_contact') {
                            if (!in_array($l_alias, $this->m_already_used_aliase)) {
                                $l_joins[$l_alias] = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias . "." . $l_table . "__isys_obj__id = " .
                                    $l_join_condition_field . ' ' . $this->addMultivalueStatusFilter($l_alias, $l_table, $l_prop_data['multi']);
                            }

                            $l_joins[$l_alias_sec] = $l_join_type . " JOIN " . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . " AS " . $l_alias_sec .
                                " ON " . $l_alias_sec . "." . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . "__id = " . $l_alias . '.' .
                                $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];

                            $l_alias_third = 'j' . $this->retrieve_alias_lvls(
                                $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0],
                                'isys_contact_2_isys_obj',
                                $l_assigned_property,
                                $const
                            );
                            $l_alias_fourth = 'job' . $this->retrieve_alias_lvls('isys_obj', 'isys_contact_2_isys_obj', $l_assigned_property, $const);

                            $l_joins[$l_alias_third] = "LEFT JOIN isys_contact_2_isys_obj AS " . $l_alias_third . " ON " . $l_alias_sec . "." .
                                $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . "__id = " . $l_alias_third .
                                '.isys_contact_2_isys_obj__isys_contact__id';

                            $l_joins[$l_alias_fourth] = $l_join_type . " JOIN isys_obj AS " . $l_alias_fourth . " ON " . $l_alias_fourth . ".isys_obj__id = " . $l_alias_third .
                                '.isys_contact_2_isys_obj__isys_obj__id';

                            $l_referenced_field = $l_alias_fourth . ".isys_obj__id";
                        } else {
                            if (!in_array($l_alias, $this->m_already_used_aliase)) {
                                $l_joins[$l_alias] = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias . "." . $l_table . "__isys_obj__id = " .
                                    $l_join_condition_field . ' ' . $this->addMultivalueStatusFilter($l_alias, $l_table, $l_prop_data['multi']);
                                $l_referenced_field = $l_alias . "." . $l_table . "__isys_obj__id";
                            }

                            if (!in_array($l_alias_sec, $this->m_already_used_aliase)) {
                                $l_joins[$l_alias_sec] = $l_join_type . " JOIN " . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . " AS " . $l_alias_sec .
                                    " ON " . $l_alias_sec . "." . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . "__id = " . $l_alias . '.' .
                                    $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];

                                $l_joins[$l_alias_sec] .= $this->addCategorySpecificCondition($const, $l_alias, $l_table);

                                $l_referenced_field = $l_alias_sec . "." . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . "__id";
                            }

                            // Reference to category table
                            if (!in_array(
                                $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#isys_obj#' . $l_assigned_property,
                                $l_already_joined_tables
                            ) && (strpos($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 'catg') !== false ||
                                    strpos($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 'cats') !== false) &&
                                strpos($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 'list') !== false) {
                                $l_alias_third = 'j' . $this->retrieve_alias_lvls(
                                    $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0],
                                    'isys_obj',
                                    $l_assigned_property,
                                    $const
                                );

                                if (in_array($l_alias_third, $this->m_already_used_aliase)) {
                                    continue;
                                }

                                $l_already_joined_tables[] = $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#isys_obj#' . $l_assigned_property;
                                $l_joins[$l_alias_third] = $l_join_type . " JOIN isys_obj AS " . $l_alias_third . " ON " . $l_alias_third . ".isys_obj__id = " . $l_alias_sec . "." .
                                    $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__isys_obj__id';
                            }
                        }

                        if ($l_alias !== null) {
                            $this->m_already_used_aliase[] = $l_alias;
                        }
                        if ($l_alias_sec !== null) {
                            $this->m_already_used_aliase[] = $l_alias_sec;
                        }
                        if ($l_alias_third !== null) {
                            $this->m_already_used_aliase[] = $l_alias_third;
                        }
                        if ($l_alias_fourth !== null) {
                            $this->m_already_used_aliase[] = $l_alias_fourth;
                        }
                        if ($l_alias_obj !== null) {
                            $this->m_already_used_aliase[] = $l_alias_obj;
                        }
                    } elseif ($l_prop_data['data'][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__OBJECT_BROWSER) {
                        if (strpos($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD], 'isys_cable_connection__id') !== false) {
                            if (isset($this->m_aliases_lvls[$const . '::' . $l_table . '#isys_obj#' . $l_assigned_property])) {
                                if (!in_array($l_alias, $this->m_already_used_aliase)) {
                                    $l_already_joined_tables[] = $l_table . '#isys_obj#' . $l_assigned_property;
                                    $l_joins[$l_alias] = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias . "." . $l_table . "__isys_obj__id = " .
                                        $l_join_condition_field . ' ' . $this->addMultivalueStatusFilter($l_alias, $l_table, $l_prop_data['multi']);

                                    $this->m_already_used_aliase[] = $l_alias;
                                }
                            }
                            if (isset($this->m_aliases_lvls[$const . '::' . 'isys_cable_connection#' . $l_table . '#' . $l_assigned_property])) {
                                $l_alias_sec = $l_alias;
                                $l_alias = 'j' . $this->m_aliases_lvls[$const . '::' . $l_table . '#isys_cable_connection' . '#' . $l_assigned_property];

                                if (!in_array($l_alias, $this->m_already_used_aliase)) {
                                    $l_joins[$l_alias] = $l_join_type . " JOIN isys_cable_connection AS " . $l_alias . " ON " . $l_alias . ".isys_cable_connection__id = " .
                                        $l_alias_sec . "." . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];

                                    $this->m_already_used_aliase[] = $l_alias;
                                }
                            }

                            if (isset($this->m_aliases_lvls[$const . '::' . 'isys_cable_connection#isys_obj' . '#' . $l_assigned_property])) {
                                $l_alias_sec = $l_alias;
                                $l_alias = 'j' . $this->m_aliases_lvls[$const . '::' . 'isys_obj#isys_cable_connection' . '#' . $l_assigned_property];

                                if (!in_array($l_alias, $this->m_already_used_aliase)) {
                                    $l_joins[$l_alias] = $l_join_type . " JOIN isys_obj AS " . $l_alias . " ON " . $l_alias . ".isys_obj__id = " . $l_alias_sec .
                                        ".isys_cable_connection__isys_obj__id";

                                    $l_referenced_field = $l_alias . ' . isys_obj__id';
                                    $this->m_already_used_aliase[] = $l_alias;
                                }
                            }
                            if (!isset($this->m_referenced_fields[$l_assigned_property . '--' . $l_prop_data['const'] . '-' . $l_prop_data['key']])) {
                                $this->m_referenced_fields[$l_assigned_property . '--' . $l_prop_data['const'] . '-' . $l_prop_data['key']] = $l_referenced_field;
                            }
                        } elseif (strpos($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD], 'isys_catg_connector_list__id') !== false) {
                            if (isset($this->m_aliases_lvls[$const . '::' . $l_table . '#isys_obj' . '#' . $l_assigned_property])) {
                                $l_alias = "j" . $this->m_aliases_lvls[$const . '::' . $l_table . '#isys_obj' . '#' . $l_assigned_property];
                                if (!in_array($l_alias, $this->m_already_used_aliase)) {
                                    $l_already_joined_tables[] = $l_table . '#isys_obj';
                                    $l_join_string = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias . "." . $l_table . "__isys_obj__id = " .
                                        $l_join_condition_field . ' ' . $this->addMultivalueStatusFilter($l_alias, $l_table, $l_prop_data['multi']);

                                    $l_joins[$l_alias] = $l_join_string;

                                    $this->m_already_used_aliase[] = $l_alias;
                                }
                            }
                            if (isset($this->m_aliases_lvls[$const . '::' . 'isys_catg_connector_list#' . $l_table . '#' . $l_assigned_property]) && $l_table != 'isys_catg_connector_list') {
                                $l_alias_sec = $l_alias;
                                $l_alias = 'j' . $this->m_aliases_lvls[$const . '::' . $l_table . '#isys_catg_connector_list' . '#' . $l_assigned_property];

                                if (!in_array($l_alias, $this->m_already_used_aliase)) {
                                    $l_join_string = $l_join_type . " JOIN isys_catg_connector_list AS " . $l_alias . " ON " . $l_alias . ".isys_catg_connector_list__id = " .
                                        $l_alias_sec . "." . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] .
                                        $this->addMultivalueStatusFilter($l_alias, 'isys_catg_connector_list', 1);

                                    $l_joins[$l_alias] = $l_join_string;

                                    $this->m_already_used_aliase[] = $l_alias;
                                }
                            }
                            if ($l_table != 'isys_catg_connector_list') {
                                if (isset($this->m_aliases_lvls[$const . '::' . $l_table . '#isys_catg_connector_list#isys_catg_connector_list' . '#' . $l_assigned_property])) {
                                    $l_alias_sec = $l_alias;
                                    $l_alias = 'j' . $this->m_aliases_lvls[$const . '::' . $l_table . '#isys_catg_connector_list#isys_catg_connector_list' . '#' . $l_assigned_property];
                                    if (!in_array($l_alias, $this->m_already_used_aliase)) {
                                        $l_join_string = $l_join_type . " JOIN isys_catg_connector_list AS " . $l_alias . " ON " . $l_alias .
                                            ".isys_catg_connector_list__isys_cable_connection__id = " . $l_alias_sec .
                                            ".isys_catg_connector_list__isys_cable_connection__id " . "AND " . $l_alias . ".isys_catg_connector_list__id != " . $l_alias_sec .
                                            ".isys_catg_connector_list__id " . $this->addMultivalueStatusFilter($l_alias, 'isys_catg_connector_list', 1);
                                        $l_joins[$l_alias] = $l_join_string;
                                        $this->m_already_used_aliase[] = $l_alias;
                                    }
                                }

                                if (isset($this->m_aliases_lvls[$const . '::' . 'isys_catg_connector_list#' . $l_table . '#' . $l_assigned_property])) {
                                    $l_alias_sec = $l_alias;
                                    $l_alias = 'j' . $this->m_aliases_lvls[$const . '::' . 'isys_catg_connector_list#' . $l_table . '#' . $l_assigned_property];

                                    if (in_array($l_alias, $this->m_already_used_aliase)) {
                                        continue;
                                    }

                                    $l_join_string = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias . "." . $l_table . "__isys_catg_connector_list__id = " .
                                        $l_alias_sec . ".isys_catg_connector_list__id " . $this->addMultivalueStatusFilter($l_alias, $l_table, 1);

                                    $l_joins[$l_alias] = $l_join_string;

                                    $this->m_already_used_aliase[] = $l_alias;
                                }

                                // Special Handling
                                if (isset($this->m_aliases_lvls[$const . '::' . $l_table . '#isys_catg_connector_list#isys_obj' . '#' . $l_assigned_property]) && $l_alias_sec) {
                                    $l_alias = 'j' . $this->m_aliases_lvls[$const . '::' . $l_table . '#isys_catg_connector_list#isys_obj' . '#' . $l_assigned_property];
                                    if (!in_array($l_alias, $this->m_already_used_aliase)) {
                                        $l_already_joined_tables[] = $l_table . '#isys_catg_connector_list#isys_obj';
                                        $l_joins[$l_alias] = $l_join_type . " JOIN isys_obj AS " . $l_alias . " ON " . $l_alias . ".isys_obj__id = " . $l_alias_sec .
                                            ".isys_catg_connector_list__isys_obj__id";
                                        $this->m_already_used_aliase[] = $l_alias;
                                    }
                                }
                            } else {
                                if (isset($this->m_aliases_lvls[$const . '::' . 'isys_catg_connector_list#isys_catg_connector_list' . '#' . $l_assigned_property])) {
                                    $l_alias_sec = $l_alias;
                                    $l_alias = 'j' . $this->m_aliases_lvls[$const . '::' . 'isys_catg_connector_list#isys_catg_connector_list' . '#' . $l_assigned_property];
                                    if (!in_array($l_alias, $this->m_already_used_aliase)) {
                                        $l_join_string = $l_join_type . " JOIN isys_catg_connector_list AS " . $l_alias . " ON " . $l_alias .
                                            ".isys_catg_connector_list__isys_cable_connection__id = " . $l_alias_sec .
                                            ".isys_catg_connector_list__isys_cable_connection__id " . "AND " . $l_alias . ".isys_catg_connector_list__id != " . $l_alias_sec .
                                            ".isys_catg_connector_list__id " . $this->addMultivalueStatusFilter($l_alias, 'isys_catg_connector_list', 1);
                                        $l_joins[$l_alias] = $l_join_string;
                                    }
                                }
                                // Special Handling
                                if (isset($this->m_aliases_lvls[$const . '::' . 'isys_obj#isys_catg_connector_list' . '#' . $l_assigned_property])) {
                                    $l_alias_sec = $l_alias;
                                    $l_alias = 'j' . $this->m_aliases_lvls[$const . '::' . 'isys_obj#isys_catg_connector_list' . '#' . $l_assigned_property];
                                    if (!in_array($l_alias, $this->m_already_used_aliase)) {
                                        $l_already_joined_tables[] = $l_table . '#isys_obj';
                                        $l_joins[$l_alias] = $l_join_type . " JOIN isys_obj AS " . $l_alias . " ON " . $l_alias . ".isys_obj__id = " . $l_alias_sec .
                                            ".isys_catg_connector_list__isys_obj__id";
                                        $this->m_already_used_aliase[] = $l_alias;
                                    }
                                }
                            }
                            continue;
                        }
                    } elseif ($l_table == "isys_logbook") {
                        $l_alias = 'j' . $this->retrieve_alias_lvls('isys_obj', 'isys_catg_logb_list', $l_assigned_property, $const);
                        $l_alias_sec = 'j' . $this->retrieve_alias_lvls('isys_catg_logb_list', 'isys_logbook', $l_assigned_property, $const);

                        if (!in_array($l_alias, $this->m_already_used_aliase)) {
                            // Category logbook join
                            $l_joins[$l_alias] = $l_join_type . " JOIN isys_catg_logb_list AS " . $l_alias . " ON " . $l_alias . ".isys_catg_logb_list__isys_obj__id = " .
                                $l_join_condition_field . ' ' . $this->addMultivalueStatusFilter($l_alias, 'isys_catg_logb_list', 1);
                            $this->m_already_used_aliase[] = $l_alias;
                        }
                        if (!in_array($l_alias_sec, $this->m_already_used_aliase)) {
                            // isys_logbook join
                            $l_joins[$l_alias_sec] = $l_join_type . " JOIN isys_logbook AS " . $l_alias_sec . " ON " . $l_alias_sec . ".isys_logbook__id = " . $l_alias .
                                ".isys_catg_logb_list__isys_logbook__id";
                            $this->m_already_used_aliase[] = $l_alias_sec;
                        }

                        if (isset($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0])) {
                            $l_alias_third = 'j' .
                                $this->retrieve_alias_lvls('isys_logbook', $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], $l_assigned_property, $const);

                            if (!in_array($l_alias_third, $this->m_already_used_aliase)) {
                                // Join of dialog table
                                $l_joins[$l_alias_third] = $l_join_type . " JOIN " . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . " AS " .
                                    $l_alias_third . " ON " . $l_alias_third . "." . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][1] . " = " .
                                    $l_alias_sec . "." . $l_table . "__" . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . "__id";
                                $this->m_already_used_aliase[] = $l_alias_third;
                            }
                        }
                    }

                    if (!in_array($l_table . '#isys_obj#' . $l_assigned_property, $l_already_joined_tables)) {
                        if (in_array($l_alias, $this->m_already_used_aliase)) {
                            continue;
                        }

                        $l_already_joined_tables[] = $l_table . '#isys_obj#' . $l_assigned_property;

                        if ($l_table == 'isys_obj') {
                            $l_join = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias . "." . $l_table . "__id = " . $l_join_condition_field;
                        } else {
                            $l_join = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias . "." . $l_table . "__isys_obj__id = " .
                                $l_join_condition_field . ' ' . $this->addMultivalueStatusFilter($l_alias, $l_table, $l_prop_data['multi']);
                        }
                        $l_join .= $this->add_join_condition($l_prop_data['data'][C__PROPERTY__DATA], $l_table, $l_alias);

                        $l_joins[$l_alias] = $l_join;
                        $l_referenced_field = $l_join_condition_field;
                        $this->m_already_used_aliase[] = $l_alias;
                    }
                    if (!in_array('isys_obj#' . $l_table . '#' . $l_assigned_property, $l_already_joined_tables)) {
                        if (in_array($l_alias_sec, $this->m_already_used_aliase)) {
                            continue;
                        }

                        $l_already_joined_tables[] = 'isys_obj#' . $l_table . '#' . $l_assigned_property;
                        $l_alias_sec = 'j' . $this->retrieve_alias_lvls($l_table, 'isys_obj', $l_assigned_property, $const);

                        $l_joins[$l_alias_sec] = $l_join_type . " JOIN isys_obj AS " . $l_alias_sec . " ON " . $l_alias_sec . ".isys_obj__id = " . $l_join_condition_field;
                        $l_referenced_field = $l_alias_sec . ".isys_obj__id";

                        $this->m_already_used_aliase[] = $l_alias_sec;
                        $this->m_already_used_aliase[] = $l_alias;
                    }

                    if (!isset($this->m_referenced_fields[$l_assigned_property . '--' . $l_prop_data['const'] . '-' . $l_prop_data['key']])) {
                        $this->m_referenced_fields[$l_assigned_property . '--' . $l_prop_data['const'] . '-' . $l_prop_data['key']] = $l_referenced_field;
                    }
                }
            }

            // Clearing out all duplicate joins.
            $l_return[$l_assigned_property] = array_unique($l_joins);

            $joins = array_merge_recursive($this->reportQuery->getJoins(), $l_return[$l_assigned_property]);
            $this->reportQuery->setJoins($joins);
        }

        return $l_return;
    }

    /**
     * Wrapper method which creates all necessary aliase for the referenced properties
     *
     * @param $p_lvl
     * @param $p_property_ids
     * @param $p_assigned_property
     *
     * @return $this
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function prepare_necessary_tasks_for_lvls_select($p_property_ids, $p_assigned_property)
    {
        if (is_countable($p_property_ids) && count($p_property_ids) > 0) {
            // This query will be used to receive all the necessary entries from the isys_property_2_cat table.
            $l_res = $this->retrieve_properties($p_property_ids, null, null, null, "", true, null, ' isys_property_2_cat__isysgui_catg_custom__id ASC ');
            $propertiesToBeReordered = [];

            // First we get all the needed data from the isys_property_2_cat table.
            while ($l_row = $l_res->get_row()) {
                $l_cat_dao = $this->get_dao_instance($l_row['class'], ($l_row['catg_custom'] ?: null));
                $l_properties = array_merge($l_cat_dao->get_properties(), $l_cat_dao->get_dynamic_properties());

                $l_row['data'] = $l_properties[$l_row['key']];

                $this->formatTable($l_row);

                if (in_array($l_row['table'], $this->tablesToBeReordered)) {
                    $propertiesToBeReordered[$l_row['id']] = $l_row;
                    $propertiesToBeReordered[$l_row['const'] . '-' . $l_row['key']] = $l_row;
                } else {
                    // We save every row, because we will need them quite often in the upcoming code.
                    $this->m_property_rows_lvls[$l_row['id']] = $l_row;
                    $this->m_property_rows_lvls[$l_row['const'] . '-' . $l_row['key']] = $l_row;
                }

                // Also we create table aliases for each possible join.
                $this->create_alias_lvls_select($l_row, $p_assigned_property);
            }

            if (!empty($propertiesToBeReordered)) {
                $this->m_property_rows_lvls = $propertiesToBeReordered + $this->m_property_rows_lvls;
            }
        }

        return $this;
    }

    /**
     * @param $propertyData
     *
     * @return mixed|string
     */
    private function getTableFromProperty($propertyData)
    {
        $dbField = $propertyData[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
        $sourceTable = $propertyData[C__PROPERTY__DATA][C__PROPERTY__DATA__SOURCE_TABLE];
        $references = $propertyData[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES];
        $table = '';

        if (substr_count($dbField, '__') >= 1) {
            $table = current(explode('__', $dbField));
        }

        if ($sourceTable === null || $references === null) {
            return $table;
        }

        if (is_array($references) && isset($references[0]) && $sourceTable !== $references[0]) {
            $table = $sourceTable;
        }

        return $table;
    }

    /**
     * Method for building the property-select query.
     *
     * @param   array   $p_property_ids              Array which holds the property-id's (from "isys_property_2_cat").
     * @param   boolean $p_select_status             Shall the status be selected aswell?
     * @param   array   $p_selects                   You may enter some SELECT-statements here (see $this->create_property_query_for_lists()).
     * @param   boolean $p_leave_field_identifiers   Set to true to keep the original field-names instead of "LANGUACE_CONSTANT###123".
     * @param   boolean $p_use_property_ids_as_title Set to true to retrieve the property-IDs as field-names.
     * @param   boolean $p_group_by_object
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function create_property_query_select(
        array $p_property_ids,
        $p_select_status = true,
        $p_selects = [],
        $p_leave_field_identifiers = false,
        $p_use_property_ids_as_title = false,
        $p_group_by_object = false
    ) {
        // Set Report Selection context @see ID-7701
        Context::instance()->setContextTechnical(Context::CONTEXT_REPORT_SELECTION);
        // We need to know if all the necessary stuff has been done!
        if (!$this->m_prepared_data_for_query_construction) {
            $this->prepare_necessary_tasks($p_property_ids);
        }

        // Now we prepare the SELECT's.
        $l_selects = array_merge($p_selects, [
            "obj_main.isys_obj__id AS '__id__'"
        ]);

        $l_alias_field = 'isys_obj__id';

        // Select the status as text, if the checkbox was clicked.
        if ($p_select_status) {
            $l_selects[] = "(CASE obj_main.isys_obj__status " . "WHEN 1 THEN 'LC__CMDB__RECORD_STATUS__BIRTH' " . "WHEN 2 THEN 'LC__CMDB__RECORD_STATUS__NORMAL' " .
                "WHEN 3 THEN 'LC__CMDB__RECORD_STATUS__ARCHIVED' " . "WHEN 4 THEN 'LC__CMDB__RECORD_STATUS__DELETED' END) AS 'LC__UNIVERSAL__CONDITION' ";
        }

        // If the report has no sub levels we can use the object title
        if (!$this->reportHasSubLvls) {
            $l_alias_field = 'isys_obj__title';
        }

        // And add the selected ones from the report-builder.
        foreach ($p_property_ids as $l_select) {
            if (!isset($this->m_property_rows[$l_select])) {
                continue;
            }

            $l_field_name = '';
            $l_special_field = '';
            $l_special_selection = false;
            $propertyData = $this->m_property_rows[$l_select];
            $propData = $propertyData['data'];
            $l_db_field = $propertyData['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
            $l_prop_key = $propertyData['key'];

            $const = $propertyData['const'];

            if ($p_use_property_ids_as_title) {
                $l_field_name = ' AS \'' . $l_select . '\'';
            }

            $l_cat = 'cats';
            $categoryType = C__CMDB__CATEGORY__TYPE_SPECIFIC;
            $l_table = $this->getTableFromProperty($propData);

            // We may have a selected property, which has no real table-fields (the dynamic properties for example).
            if ($propertyData['type'] == C__PROPERTY_TYPE__DYNAMIC && $this->m_query_as_report) {
                $callbackObj = $propData[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0];
                $l_callback_class = (is_object($callbackObj)) ? get_class($callbackObj) : $callbackObj;

                if (strpos($l_callback_class, 'isys_cmdb_dao_category_') === 0) {
                    $fieldAlias = $l_callback_class . '::' .
                        $propData[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] . '::%s::' .
                        $propData[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE];

                    if (strpos($l_db_field, '_isys_obj__id') !== false || $l_db_field === 'isys_obj__id') {
                        $l_field = 'obj_main.isys_obj__id ';
                        $l_selects[$l_select] = $l_field . ' AS \'' . sprintf($fieldAlias, 'isys_obj__id') . '\'';
                        continue;
                    }

                    if (isset($propData[C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN]) &&
                        count($propData[C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN])
                    ) {
                        $propertyJoins = $propData[C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN];
                        $referenceTable = $propertyJoins[0]->getTable();

                        if ($referenceTable === $l_table) {
                            $l_table = null;
                        }

                        $alias = 'j' . $this->retrieve_alias($referenceTable, $l_table, $const);

                        if ($const === 'C__CATG__GLOBAL' && $referenceTable === 'isys_obj') {
                            $alias = 'obj_main';
                        }

                        $l_field = $alias . '.' . $l_db_field;
                    } else {
                        if ($l_table != 'isys_obj') {
                            $l_field = 'j' . $this->retrieve_alias($l_table, null, $const) . '.' . $l_db_field;
                        } else {
                            $l_field = 'obj_main.' . $l_db_field;
                        }
                    }
                    $l_selects[$l_select] = $l_field . ' AS \'' . sprintf($fieldAlias, $l_db_field) . '\'';
                }
                continue;
            } elseif ($propertyData['type'] == C__PROPERTY_TYPE__DYNAMIC) {
                if (isset($l_db_field) && $l_table !== 'isys_obj') {
                    $l_field_alias = '';
                    if (isset($propData[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) {
                        $l_field_alias = ' AS ' . $propData[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
                    }

                    $l_selects[$l_select] = 'j' . $this->retrieve_alias('isys_obj', $l_table, $const) . '.' . $l_db_field . $l_field_alias;
                }
                continue;
            }

            if ($propertyData['catg'] != null) {
                $l_cat = 'catg';
                $categoryType = C__CMDB__CATEGORY__TYPE_GLOBAL;
            } elseif ($propertyData['catg_custom'] != null) {
                $l_cat = 'catg_custom';
                $categoryType = C__CMDB__CATEGORY__TYPE_CUSTOM;
            }

            $aliasTitleAddition = $categoryType . '_' . $propertyData[$l_cat];

            if ($l_table == 'isys_catg_custom_fields_list') {
                $l_field_alias = $propData[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
                $l_field_type = $propData[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE];
                $uiParams = $propData[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS];
                $l_alias = $this->retrieve_alias($l_field_alias . '#isys_catg_custom_fields_list', null, $const);
                $l_output_alias = '\'' . $propertyData['title'] . '###' . $aliasTitleAddition . '\'';

                switch ($l_field_type) {
                    case C__PROPERTY__INFO__TYPE__OBJECT_BROWSER:
                    case C__PROPERTY__INFO__TYPE__N2M:
                        $l_alias_sec = $this->retrieve_alias('isys_catg_custom_fields_list', $l_field_alias . '#isys_obj', $const);
                        $l_field = 'j' . $l_alias_sec . '.isys_obj__title AS ' . $l_output_alias;
                        break;
                    case C__PROPERTY__INFO__TYPE__DIALOG:
                    case C__PROPERTY__INFO__TYPE__DIALOG_PLUS:
                    case C__PROPERTY__INFO__TYPE__MULTISELECT:

                        if (isset($uiParams['extra']) && $uiParams['extra'] === 'yes-no') {
                            $contentField = 'j' . $l_alias . '.' . $l_db_field;
                            $l_field = '(CASE WHEN ' . $contentField . ' < 0 THEN ' . $this->convert_sql_text(isys_tenantsettings::get('gui.empty_value', '-')) . ' ELSE ' . $contentField . ' END) AS ' . $l_output_alias;
                            break;
                        }

                        $l_alias_sec = $this->retrieve_alias('isys_catg_custom_fields_list', $l_field_alias . '#isys_dialog_plus_custom', $const);
                        $l_field = 'j' . $l_alias_sec . '.isys_dialog_plus_custom__title AS ' . $l_output_alias;
                        break;
                    case C__PROPERTY__INFO__TYPE__DATE:
                        // See ID-2992
                        $l_field = 'j' . $l_alias . '.' . $l_db_field . ' AS ' . '\'locales::fmt_date::' . ltrim($l_output_alias, "'");
                        break;
                    case C__PROPERTY__INFO__TYPE__DATETIME:
                        // See ID-7507
                        $l_field = 'j' . $l_alias . '.' . $l_db_field . ' AS ' . '\'locales::fmt_datetime::' . ltrim($l_output_alias, "'");
                        break;
                    default:
                        $l_field = 'j' . $l_alias . '.' . $l_db_field . ' AS ' . $l_output_alias;
                        break;
                }

                $l_selects[$l_select] = $l_field;
                continue;
            }

            // First we check for some special selected fields.
            if (isset($this->m_special_selects[$l_db_field])) {
                // Check if its a primary field
                if ($p_group_by_object && $propData[C__PROPERTY__INFO][C__PROPERTY__INFO__PRIMARY]) {
                    $l_sp_select = $this->m_special_selects[$l_db_field];
                    $l_sp_select = 'GROUP_CONCAT(DISTINCT ' . substr($l_sp_select, 0, strpos($l_sp_select, 'AS')) . ') ' .
                        substr($l_sp_select, strpos($l_sp_select, 'AS'), strlen($l_sp_select));
                    $l_selects[$l_select] = $l_sp_select;
                    continue;
                } elseif (is_array($this->m_special_selects[$l_db_field])) {
                    $l_special_selection = true;
                    $l_table = $this->m_special_selects[$l_db_field][0];
                    $l_special_field = (strpos($l_db_field, $l_table)) ? $l_db_field : $this->m_special_selects[$l_db_field][2];

                    if (empty($propData[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES])) {
                        $propData[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES] = [];
                    }

                    if ($l_table == 'isys_obj' && !$this->m_query_as_report) {
                        $l_special_field = 'isys_obj__title';
                        $propData[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES] =
                            array_replace($propData[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES], [$this->m_special_selects[$l_db_field][0]]);
                        $l_table = $this->m_special_selects[$l_db_field][1];
                    } else {
                        $propData[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES] =
                            array_replace($propData[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES], [$this->m_special_selects[$l_db_field][1]]);
                    }
                } else {
                    $l_selects[$l_select] = $this->m_special_selects[$l_db_field];
                    continue;
                }
            }

            // We might have a dialog- or object-browser field and want to handle it properly.
            $l_ui_type = $this->retrieve_ui_type((array)$propData);

            $localesMethod = '';

            switch ($propData[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE]) {
                case Property::C__PROPERTY__INFO__TYPE__DATETIME:
                    $localesMethod = 'locales::fmt_datetime::';
                    break;
                case Property::C__PROPERTY__INFO__TYPE__DATE:
                    $localesMethod = 'locales::fmt_date::';
                    break;
                case Property::C__PROPERTY__INFO__TYPE__TIMEPERIOD:
                    $localesMethod = 'locales::fmt_time::';
                    break;
                case Property::C__PROPERTY__INFO__TYPE__MONEY:
                    $localesMethod = 'locales::fmt_monetary::';
                    break;
                default:
                    break;
            }

            $outputAlias = (!$p_leave_field_identifiers ? " AS '" . $localesMethod . $propertyData['title'] . "###" . $aliasTitleAddition .
                "'" : $l_field_name);

            if (($propData[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] !== null &&
                    substr($propData[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 0, 5) == 'isys_') ||
                $propData[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_location') {
                $l_alias = $this->retrieve_alias($l_table, $propData[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], $const);

                // We have to join 'job' on references to isys_connection.
                if ($propData[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == 'isys_connection') {
                    $l_selects[$l_select] = "job" . $l_alias . "." . $l_alias_field . $outputAlias;

                    if (!$p_use_property_ids_as_title && $p_leave_field_identifiers &&
                        isset($propData[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) {
                        $l_selects[$l_select] .= ' AS ' . $propData[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
                    }
                } elseif ($propData[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == 'isys_contact') {
                    $l_alias = $this->retrieve_alias('isys_obj', 'isys_contact_2_isys_obj', $const);
                    $l_selects[$l_select] = "job" . $l_alias . "." . $l_alias_field . $outputAlias;
                } elseif ($propData[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_location') {
                    $l_alias = $this->retrieve_alias($l_table, 'isys_obj', $const);
                    $l_selects[$l_select] = "j" . $l_alias . "." . $l_alias_field . $outputAlias;
                } else {
                    if ($l_special_selection && $l_special_field != '') {
                        $l_selects[$l_select] = "j" . $l_alias . "." . $l_special_field . $outputAlias;
                    } elseif ($propData[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__OBJECT_BROWSER) {
                        $l_object_field = $propData[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . "__isys_obj__id";
                        if ($propData[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == 'isys_obj') {
                            $l_object_field = 'isys_obj__id';
                        }

                        $l_selects[$l_select] = "j" . $l_alias . "." . $l_object_field . $outputAlias;
                    } else {
                        if ($propData[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] != 'isys_obj' &&
                            isset($this->m_aliases[$const . '::' . $propData[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]])) {
                            $l_selects[$l_select] = 'j' . $this->m_aliases[$const . '::' . $propData[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]] . "." .
                                $propData[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . "__title" . $outputAlias;
                        } else {
                            $l_selects[$l_select] = "j" . $l_alias . "." .
                                ($propData[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][2] ?: $propData[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] .
                                    "__title") . $outputAlias;
                        }
                    }

                    if (isset($this->m_text_fields[$propertyData['const']])) {
                        if (isset($this->m_text_fields[$propertyData['const']][$propertyData['key']])) {
                            $l_selects[$l_select] = substr($l_selects[$l_select], 0, (strpos($l_selects[$l_select], '.') + 1)) .
                                $this->m_text_fields[$propertyData['const']][$propertyData['key']] . $outputAlias;
                        }
                    }
                }
                continue;
            } elseif ($propData[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__OBJECT_BROWSER) {
                if ($l_db_field == $l_table . '__isys_obj__id') {
                    if (isset($this->m_aliases[$const . '::' . 'isys_connection#' . $l_table])) {
                        $l_alias = 'j' . $this->m_aliases[$const . '::' . $l_table . '#isys_connection'];

                        $l_selects[$l_select] = $l_alias . "." . $l_db_field . $outputAlias;
                    }
                    continue;
                } elseif (strpos($l_db_field, 'isys_cable_connection__id') !== false) {
                    if (isset($this->m_aliases[$const . '::' . 'isys_obj#isys_cable_connection'])) {
                        $l_alias = 'j' . $this->m_aliases[$const . '::' . 'isys_obj#isys_cable_connection'];
                        $l_selects[$l_select] = $l_alias . "." . $l_alias_field . $outputAlias;
                        ;
                    }
                    continue;
                } elseif (strpos($l_db_field, 'isys_catg_connector_list__id') !== false) {
                    if ($l_table != 'isys_catg_connector_list') {
                        if (isset($this->m_aliases[$const . '::' . $l_table . '#isys_catg_connector_list#isys_obj'])) {
                            $l_alias = 'j' . $this->m_aliases[$const . '::' . $l_table . '#isys_catg_connector_list#isys_obj'];
                            $l_selects[$l_select] = $l_alias . "." . $l_alias_field . $outputAlias;
                            ;
                            continue;
                        }
                    } else {
                        if (isset($this->m_aliases[$const . '::' . 'isys_catg_connector_list#isys_obj'])) {
                            $l_alias = 'j' . $this->m_aliases[$const . '::' . 'isys_catg_connector_list#isys_obj'];
                            $l_selects[$l_select] = $l_alias . "." . $l_alias_field . $outputAlias;
                            ;
                            continue;
                        }
                    }
                } else {
                    $key = $const . '::isys_obj#' . $l_table;
                    if (in_array($l_table, self::$tablesConnectedWith_2_isys_obj_suffix)) {
                        $key = $const . '::isys_obj#' . $l_table . '_2_isys_obj';
                    }
                    if (isset($this->m_aliases[$key])) {
                        $l_alias = 'j' . $this->m_aliases[$key];
                        $l_selects[$l_select] = $l_alias . "." . $l_alias_field . $outputAlias;
                        continue;
                    }
                }
            }

            $l_alias = 'j' . $this->retrieve_alias($l_table, null, $const);

            // Then we check for special table-names inside the select.
            if (isset($this->m_aliases[$const . '::' . $l_table])) {
                $l_alias = ($const !== 'C__CATG__GLOBAL' ? 'j' : '') . $this->m_aliases[$const . '::' . $l_table];
            } elseif (isset($this->m_aliases[$const . '::' . $l_table . '#main_obj'])) {
                $l_alias = 'j' . $this->m_aliases[$const . '::' . $l_table . '#main_obj'];
            } elseif ($l_table == 'isys_logbook') {
                $l_alias = 'j' . $this->retrieve_alias('isys_catg_logb_list', 'isys_logbook', $const);
            }

            // If we got a "yes/no" dialog-field we want to display the result as such.
            if ($l_ui_type == C__PROPERTY__UI__TYPE__DIALOG &&
                $propData[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'get_yes_or_no') {
                $l_selects[$l_select] = "(CASE " . $l_alias . "." . $l_db_field . " " . "WHEN 0 THEN 'LC__UNIVERSAL__NO' WHEN 1 THEN 'LC__UNIVERSAL__YES' END)" .
                    (!$p_leave_field_identifiers ? " AS '" . $propertyData['title'] . "###" . $aliasTitleAddition .
                        "'" : ($p_use_property_ids_as_title ? $l_field_name : $l_db_field));
            } elseif (!empty($propData[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']) &&
                !in_array($propData[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1], self::$m_ignored_format_callbacks)) {
                $data = $propData[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'];
                if (is_object($propData[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']) &&
                    get_class($propData[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']) == 'isys_callback') {
                    $data = $propData[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']->execute();
                }

                if (is_array($data)) {
                    if (count($data) > 0) {
                        $l_selects_dialog = "(CASE " . $l_alias . "." . $l_db_field . " ";
                        foreach ($data as $l_key => $l_value) {
                            if (is_array($l_value)) {
                                if (isset($l_value['value'])) {
                                    $l_selects_dialog .= "WHEN " . $this->convert_sql_text($l_key) . " THEN " . $this->convert_sql_text($l_value['value']) . " ";
                                } elseif (isset($l_value['val'])) {
                                    $l_selects_dialog .= "WHEN " . $this->convert_sql_text($l_key) . " THEN " . $this->convert_sql_text($l_value['val']) . " ";
                                } else {
                                    // Cannot extract value from array
                                    $l_selects_dialog .= "WHEN " . $this->convert_sql_text($l_key) . " THEN " . isys_tenantsettings::get('gui.empty_value', '-') . " ";
                                }
                            } else {
                                $l_selects_dialog .= "WHEN " . $this->convert_sql_text($l_key) . " THEN " . $this->convert_sql_text($l_value) . " ";
                            }
                        }
                        $l_selects_dialog .= "END) ";
                    } else {
                        // No values found
                        $l_selects_dialog = " '" . isys_tenantsettings::get('gui.empty_value', '-') . "' ";
                    }
                    $l_selects_dialog .= (!$p_leave_field_identifiers ? " AS '" . $propertyData['title'] . "###" .
                        $aliasTitleAddition . "'" : ($p_use_property_ids_as_title ? $l_field_name : $l_db_field));
                    $l_selects[$l_select] = $l_selects_dialog;
                }
            } else {
                if ($l_table == 'isys_logbook') {
                    $l_alias = 'j' . $this->retrieve_alias('isys_catg_logb_list', 'isys_logbook', $const);
                }

                $l_selection = $l_alias . "." . $l_db_field;

                if (isset($this->m_property_wrap_functions[$l_prop_key])) {
                    $l_selection = $this->wrap_mysql_function($l_prop_key, $l_selection);
                }
                $l_selects[$l_select] = $l_selection .
                    (!$p_leave_field_identifiers ? " AS '" . $localesMethod . $propertyData['title'] . "###" . $aliasTitleAddition .
                        "'" : $l_field_name);
            }
        }

        // Clearing out all duplicate selects.
        return array_unique($l_selects);
    }

    /**
     * @var array
     */
    private $m_property_wrap_functions = [
        'longitude' => 'Y',
        'latitude'  => 'X'
    ];

    /**
     * Helper Method which wraps mysql functions around the selected db field
     *
     * @param $p_key
     * @param $p_field
     *
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function wrap_mysql_function($p_key, $p_field)
    {
        return $this->m_property_wrap_functions[$p_key] . '(' . $p_field . ')';
    }

    /**
     * Method for creating the join-statements, based on the selected properties and conditions.
     *
     * @param   array   $p_property_ids The property-ID's of all the properties we need to join.
     * @param   boolean $l_main_obj
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function create_property_query_join(array $p_property_ids, $p_main_obj = true)
    {
        // We need to know if all the necessary stuff has been done!
        if (!$this->m_prepared_data_for_query_construction) {
            $this->prepare_necessary_tasks($p_property_ids);
        }

        // We need this array to save "already joined" tables for saving a bit of performance.
        $l_already_joined_tables = $l_joins = [];

        if ($p_main_obj) {
            // Our first join is to get the CMDB-status.
            $l_joins['obj_main_status'] = 'INNER JOIN isys_cmdb_status AS obj_main_status ON obj_main_status.isys_cmdb_status__id = obj_main.isys_obj__isys_cmdb_status__id';
        }

        if ($this->m_empty_values) {
            $l_join_type = "LEFT";
        } else {
            $l_join_type = "INNER";
        }

        // Now we create the single JOIN's.
        foreach ($this->m_property_rows as $l_prop_id => $l_prop_data) {
            if (!is_numeric($l_prop_id)) {
                continue;
            }
            $l_table = null;
            $l_ref_field = $l_alias_sec = $l_alias_third = $l_alias_fourth = null;
            $const = $l_prop_data['const'];

            // We won't handle dynamic properties here.
            if ($l_prop_data['type'] == C__PROPERTY_TYPE__DYNAMIC && (!isset($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]) || strpos($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD], '_isys_obj__id') !== false)) {
                continue;
            }

            // We are only able to JOIN with loaded data.
            if (in_array($l_prop_id, $p_property_ids)) {
                if ($l_prop_data['data'] instanceof Property) {
                    if ($l_prop_data['data']->getData()->offsetExists(Property::C__PROPERTY__DATA__SOURCE_TABLE) &&
                        !$l_prop_data['data']->getData()->offsetExists(Property::C__PROPERTY__DATA__REFERENCES)) {
                        $l_table = $l_prop_data['data']->getData()->getSourceTable();
                    }
                }

                if ($l_table === null && $l_prop_data['type'] == C__PROPERTY_TYPE__DYNAMIC) {
                    $l_table = $l_prop_data['table'];
                }

                if ($l_table === null) {
                    $l_table = current(explode('__', $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]));
                }

                if ($l_table == 'isys_catg_custom_fields_list') {
                    $l_field_alias = $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
                    $uiParams = $l_prop_data['data'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS];
                    $l_field_key = (strpos($l_field_alias, 'COMMENTARY') !== false) ? $l_field_alias : substr(
                        $l_field_alias,
                        strpos($l_field_alias, '_c_') + 1,
                        strlen($l_field_alias)
                    );
                    $l_field_type = $l_prop_data['data'][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE];
                    $l_alias = 'j' . $this->retrieve_alias($l_field_alias . '#isys_catg_custom_fields_list', null, $const);

                    if ($l_field_type == C__PROPERTY__INFO__TYPE__DIALOG || $l_field_type == C__PROPERTY__INFO__TYPE__DIALOG_PLUS) {
                        $l_join_type = 'LEFT';
                    } elseif (!$this->m_empty_values && $l_join_type == 'LEFT') {
                        $l_join_type = "INNER";
                    }

                    if (in_array($l_alias, $this->m_already_used_aliase)) {
                        continue;
                    }

                    $l_identifier = 'root-' . $l_prop_data['const'];

                    $l_joins_string = ' LEFT JOIN ' . $l_table . ' AS ' . $l_alias . ' ON obj_main.isys_obj__id = ' . $l_alias . '.' . $l_table . '__isys_obj__id AND ' .
                        $l_alias . '.' . $l_table . '__field_key = ' . $this->convert_sql_text($l_field_key) . ' ';

                    if (empty($this->m_parent_custom_field[$l_identifier])) {
                        $l_description_alias = 'ROOT_' . $l_prop_data['const'];

                        $this->m_parent_custom_field[$l_identifier] = $l_description_alias . '.' . $l_table . '__data__id';

                        $l_joins_string = $l_join_type . ' JOIN ' . $l_table . ' AS ' . $l_description_alias . ' ON obj_main.isys_obj__id = ' . $l_description_alias . '.' . $l_table . '__isys_obj__id
                                AND ' . $l_description_alias . '.' . $l_table .
                            '__isysgui_catg_custom__id = (SELECT isysgui_catg_custom__id FROM isysgui_catg_custom WHERE isysgui_catg_custom__const = ' .
                            $this->convert_sql_text($l_prop_data['const']) . ')
                                AND ' . $l_description_alias . '.' . $l_table . '__field_type = ' . $this->convert_sql_text('commentary') . ' ' . $l_joins_string . '
                                AND ' . $l_alias . '.' . $l_table . '__data__id = ' . $this->m_parent_custom_field[$l_identifier] . ' ';
                    } elseif ($this->m_parent_custom_field[$l_identifier] != $l_alias . '.' . $l_table . '__data__id') {
                        $l_joins_string .= ' AND ' . $l_alias . '.' . $l_table . '__data__id = ' . $this->m_parent_custom_field[$l_identifier] . ' ';
                    }

                    $l_joins_string .= $this->addMultivalueStatusFilter($l_alias, $l_table, $l_prop_data['multi']);

                    $l_joins[$l_alias] = $l_joins_string;

                    switch ($l_field_type) {
                        case C__PROPERTY__INFO__TYPE__OBJECT_BROWSER:
                        case C__PROPERTY__INFO__TYPE__N2M:
                            $l_alias_sec = 'j' . $this->retrieve_alias('isys_catg_custom_fields_list', $l_field_alias . '#isys_obj', $const);

                            if (in_array($l_alias_sec, $this->m_already_used_aliase)) {
                                continue; // phpcs:ignore
                            }

                            $l_joins[$l_alias_sec] = ' LEFT JOIN isys_obj AS ' . $l_alias_sec . ' ON ' . $l_alias . '.' . $l_table . '__field_content = ' . $l_alias_sec .
                                '.isys_obj__id ';
                            break;
                        case C__PROPERTY__INFO__TYPE__DIALOG:
                        case C__PROPERTY__INFO__TYPE__DIALOG_PLUS:
                        case C__PROPERTY__INFO__TYPE__MULTISELECT:

                            if (isset($uiParams['extra']) && $uiParams['extra'] === 'yes-no') {
                                break;
                            }

                            $l_alias_sec = 'j' . $this->retrieve_alias('isys_catg_custom_fields_list', $l_field_alias . '#isys_dialog_plus_custom', $const);

                            if (in_array($l_alias_sec, $this->m_already_used_aliase)) {
                                continue; // phpcs:ignore
                            }

                            $l_joins[$l_alias_sec] = ' LEFT JOIN isys_dialog_plus_custom AS ' . $l_alias_sec . ' ON ' . $l_alias . '.' . $l_table . '__field_content = ' .
                                $l_alias_sec . '.isys_dialog_plus_custom__id ';
                            break;
                    }
                    if ($l_alias !== null) {
                        $this->m_already_used_aliase[] = $l_alias;
                    }
                    if ($l_alias_sec !== null) {
                        $this->m_already_used_aliase[] = $l_alias_sec;
                    }
                    continue;
                }

                // We have to check for an existing "predefined" alias.
                if (isset($this->m_aliases[$const . '::' . $l_table])) {
                    $l_alias = ($const !== 'C__CATG__GLOBAL' ? 'j' : '') . $this->m_aliases[$const . '::' . $l_table];
                } else {
                    $l_alias = 'j' . $this->retrieve_alias($l_table, null, $const);
                }

                $specialJoinKey = $l_prop_data['class'] . '::' . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                $specialJoinKeyWildcard = $l_prop_data['class'] . '::*';

                if (isset($this->m_special_joins[$l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]]) ||
                    isset($this->m_special_joins[$specialJoinKey]) ||
                    isset($this->m_special_joins[$specialJoinKeyWildcard])) {
                    $specialJoinData = $this->m_special_joins[$specialJoinKey] ?: $this->m_special_joins[$l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
                    $l_alias_sec = null;
                    foreach ($specialJoinData as $key => $l_spec_join) {
                        $l_table = $l_spec_join[0];
                        $l_ref_table = $l_spec_join[1];
                        $l_ref_field = $l_spec_join[2];
                        $l_ref_ref_field = $l_spec_join[3];
                        $previousJoin = null;

                        $l_alias = 'j' . $this->retrieve_alias($l_ref_table, $l_table, $const);
                        if ($l_alias == 'j' || ($key === 0 && $l_table === 'isys_connection')) {
                            // skip join because its not set
                            if ($l_prop_data['data']['ui']['id'] !== 'C__CATG__INTERFACE_L__DEST') {
                                continue;
                            }
                        }

                        if ($l_ref_table == 'isys_obj') {
                            $l_alias_sec = 'obj_main';
                        } elseif (!$p_main_obj && $l_alias_sec !== null) {
                            // Get alias from m_already_used_aliase because we want
                            $l_alias_sec = end($this->m_already_used_aliase);
                        } elseif ($l_prop_data['data']['ui']['id'] === 'C__CATG__INTERFACE_L__DEST') {
                            $previousJoin = isset($specialJoinData[$key-1]) ? $specialJoinData[$key-1]: null;
                            // Check whether join is part of special joins and is at position greater 1
                            if (!empty($previousJoin)) {
                                $l_alias_sec = 'j' . $this->retrieve_alias($previousJoin[1], $previousJoin[0], $const);
                            }
                        } else {
                            // Get Previous Join
                            $previousJoin = isset($specialJoinData[$key-1]) ? $specialJoinData[$key-1]: null;

                            // Check whether join is part of special joins and is at position greater 1
                            if (!empty($previousJoin)) {
                                $l_alias_sec = 'j' . $this->retrieve_alias($previousJoin[1], $previousJoin[0], $const);
                            } else {
                                // Default handling: Guessing alias by reference table - dangerous attempt
                                foreach ($this->m_aliases as $l_key => $l_value) {
                                    if (substr($l_key, 0, strpos($l_key, '#')) == $l_ref_table) {
                                        $l_alias_tables = explode('#', $l_key);
                                        [$aliasConst, $aliasTable] = explode('::', $l_alias_tables[0]);
                                        $l_alias_sec = 'j' . $this->retrieve_alias($l_alias_tables[1], $aliasTable, $aliasConst);
                                        break;
                                    }
                                }
                            }
                        }

                        if (!in_array($l_ref_table . '#' . $l_table, $l_already_joined_tables) || ($l_prop_data['data']['ui']['id'] === 'C__CATG__INTERFACE_L__DEST')) {
                            $l_already_joined_tables[] = $l_ref_table . '#' . $l_table;
                            //							$this->m_already_used_aliase[] = 'j' . $l_alias;
                            if (in_array($l_alias, $this->m_already_used_aliase)) {
                                continue;
                            }

                            if ($l_ref_table == 'isys_connection') {
                                $l_join = $l_join_type . ' JOIN ' . $l_table . ' AS ' . $l_alias . ' ON ' . $l_alias . '.' . $l_ref_field . ' IN 
                                    (SELECT isys_connection__id FROM isys_connection WHERE isys_connection__isys_obj__id = obj_main.isys_obj__id) ';
                            } else {
                                $l_join = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias_sec . '.' .
                                    $l_ref_ref_field . ' = ' . $l_alias . '.' . $l_ref_field;
                            }

                            if ($l_ref_table !== 'isys_obj' && ($l_prop_data['data']['ui']['id'] !== 'C__CATG__INTERFACE_L__DEST')) {
                                $l_join .= $this->add_join_condition($l_prop_data['data'][C__PROPERTY__DATA], $l_ref_table, $l_alias_sec);
                            } elseif (($l_prop_data['data']['ui']['id'] === 'C__CATG__INTERFACE_L__DEST')) {
                                $l_join .= $this->add_join_condition($l_prop_data['data'][C__PROPERTY__DATA], $l_table, $l_alias);
                            } else {
                                $l_join .= $this->add_join_condition($l_prop_data['data'][C__PROPERTY__DATA], $l_table, $l_alias);
                            }

                            if ($l_table != 'isys_connection' && $l_table != 'isys_obj' && !strpos($l_table, '_2_')) {
                                $l_join .= $this->addMultivalueStatusFilter($l_alias, $l_table, $l_prop_data['multi']);
                            }

                            $l_joins[$l_alias] = $l_join;
                            $this->m_already_used_aliase[] = $l_alias;
                        }
                    }

                    if ($l_table !== 'isys_obj') {
                        if (!in_array($l_table . '#isys_obj', $l_already_joined_tables)) {
                            $l_already_joined_tables[] = $l_table . '#isys_obj';

                            $l_alias_sec = $l_alias;

                            $l_alias = 'j' . $this->retrieve_alias($l_table, 'isys_obj', $const);

                            if (in_array($l_alias, $this->m_already_used_aliase) || $l_alias === 'j') {
                                continue;
                            }

                            if ($l_table != 'isys_obj') {
                                $l_ref_field = $l_table . '__isys_obj__id';
                            }

                            $l_join = $l_join_type . " JOIN isys_obj AS " . $l_alias . " ON " . $l_alias . '.isys_obj__id = ' . $l_alias_sec . '.' . $l_ref_field;

                            $l_join .= $this->add_join_condition($l_prop_data['data'][C__PROPERTY__DATA], $l_table, $l_alias);
                            $l_joins[$l_alias] = $l_join;
                            $this->m_already_used_aliase[] = $l_alias;
                        }
                    }

                    continue;
                }

                // If we have a reference table, we have to join it.
                if (($l_table != $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] &&
                        $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] !== null &&
                        substr($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 0, 5) == 'isys_') ||
                    $l_prop_data['data'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_location') {
                    $l_alias_sec = 'j' . $this->retrieve_alias($l_table, $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], $const);
                    $l_alias_third = 'job' . $this->retrieve_alias($l_table, $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], $const);

                    if (in_array($l_alias_sec, $this->m_already_used_aliase)) {
                        continue;
                    }

                    $l_already_joined_tables[] = $l_table . '#isys_obj';
                    if ($l_table == 'isys_obj') {
                        if (in_array($l_alias_sec, $this->m_already_used_aliase)) {
                            continue;
                        }

                        $l_joins[$l_alias_sec] = $l_join_type . " JOIN " . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . " AS " . $l_alias_sec . " ON " .
                            $l_alias_sec . "." . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . "__id = obj_main." .
                            $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                    } elseif ($l_prop_data['data'][C__PROPERTY__INFO][C__PROPERTY__INFO__BACKWARD] === true) {
                        if ($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == "isys_connection") {
                            if (in_array($l_alias_sec, $this->m_already_used_aliase)) {
                                continue;
                            }

                            if (!in_array($l_alias, $this->m_already_used_aliase)) {
                                // Join the category table (isys_catg_XXXX_list).
                                $l_joins[$l_alias] = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias . "." . $l_table . "__" .
                                    $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__id IN (
                                        SELECT isys_connection__id FROM isys_connection WHERE isys_connection__isys_obj__id = obj_main.isys_obj__id) ' .
                                    $this->addMultivalueStatusFilter($l_alias, $l_table, $l_prop_data['multi']);
                            }

                            // Join the object table (isys_obj).
                            $l_joins[$l_alias_third] = $l_join_type . " JOIN isys_obj AS " . $l_alias_third . " ON " . $l_alias . "." . $l_table . "__isys_obj__id = " . $l_alias_third .
                                '.isys_obj__id';
                        } else {
                            $this->handleBackwardProperty($l_prop_data, $l_joins, 'obj_main.isys_obj__id', $l_join_type, [$l_alias, $l_alias_sec, $l_alias_third]);
                        }
                    } elseif ($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == 'isys_contact') {
                        if (!in_array($l_alias, $this->m_already_used_aliase)) {
                            $l_joins[$l_alias] = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias . "." . $l_table .
                                "__isys_obj__id = obj_main.isys_obj__id " . $this->addMultivalueStatusFilter($l_alias, $l_table, $l_prop_data['multi']);
                        }

                        if (in_array($l_alias_sec, $this->m_already_used_aliase)) {
                            continue;
                        }

                        $l_joins[$l_alias_sec] = $l_join_type . " JOIN " . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . " AS " . $l_alias_sec . " ON " .
                            $l_alias_sec . "." . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . "__id = " . $l_alias . '.' .
                            $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];

                        $l_alias_third = "j" . $this->retrieve_alias($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 'isys_contact_2_isys_obj', $const);
                        $l_alias_fourth = "job" . $this->retrieve_alias('isys_obj', 'isys_contact_2_isys_obj', $const);

                        $l_joins[$l_alias_third] = "LEFT JOIN isys_contact_2_isys_obj AS " . $l_alias_third . " ON " . $l_alias_sec . "." .
                            $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . "__id = " . $l_alias_third .
                            '.isys_contact_2_isys_obj__isys_contact__id';

                        $l_joins[$l_alias_fourth] = $l_join_type . " JOIN isys_obj AS " . $l_alias_fourth . " ON " . $l_alias_fourth . ".isys_obj__id = " . $l_alias_third .
                            '.isys_contact_2_isys_obj__isys_obj__id';
                    } elseif ($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == "isys_connection") {
                        if (!in_array($l_alias, $this->m_already_used_aliase)) {
                            if (isset($this->m_aliases[$const . '::' . $l_table . '#main_obj'])) {
                                $l_alias = 'j' . $this->m_aliases[$const . '::' . $l_table . '#main_obj'];
                            }

                            // Join the category table (isys_catg_XXXX_list).
                            $l_joins[$l_alias] = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias . "." . $l_table .
                                "__isys_obj__id = obj_main.isys_obj__id " . $this->addMultivalueStatusFilter($l_alias, $l_table, $l_prop_data['multi']);
                        }

                        if (in_array($l_alias_sec, $this->m_already_used_aliase)) {
                            continue;
                        }

                        // Join the connection table (isys_connection).
                        $joinSecond = $l_join_type . " JOIN " . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . " AS " . $l_alias_sec . " ON " .
                            $l_alias_sec . "." . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . "__id = " . $l_alias . '.' .
                            $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];

                        $joinSecond .= $this->addCategorySpecificCondition($const, $l_alias, $l_table);

                        $l_joins[$l_alias_sec] = $joinSecond;

                        // Join the object table (isys_obj).
                        $l_joins[$l_alias_third] = $l_join_type . " JOIN isys_obj AS " . $l_alias_third . " ON " . $l_alias_sec . "." .
                            $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . "__isys_obj__id = " . $l_alias_third . '.isys_obj__id';
                    } elseif ($l_table == "isys_logbook" && isset($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0])) {
                        $l_alias = 'j' . $this->retrieve_alias('isys_obj', 'isys_catg_logb_list', $const);
                        $l_alias_sec = 'j' . $this->retrieve_alias('isys_catg_logb_list', 'isys_logbook', $const);
                        $l_alias_third = 'j' . $this->retrieve_alias('isys_logbook', $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], $const);
                        if (!in_array($l_alias, $this->m_already_used_aliase)) {
                            // Category logbook join
                            $l_joins[$l_alias] = $l_join_type . " JOIN isys_catg_logb_list AS " . $l_alias . " ON " . $l_alias .
                                ".isys_catg_logb_list__isys_obj__id = obj_main.isys_obj__id " .
                                $this->addMultivalueStatusFilter($l_alias, 'isys_catg_logb_list', $l_prop_data['multi']);
                            $this->m_already_used_aliase[] = $l_alias;
                        }
                        if (!in_array($l_alias_sec, $this->m_already_used_aliase)) {
                            // isys_logbook join
                            $l_joins[$l_alias_sec] = $l_join_type . " JOIN isys_logbook AS " . $l_alias_sec . " ON " . $l_alias_sec . ".isys_logbook__id = " . $l_alias .
                                ".isys_catg_logb_list__isys_logbook__id";
                            $this->m_already_used_aliase[] = $l_alias_sec;
                        }
                        if (!in_array($l_alias_third, $this->m_already_used_aliase)) {
                            // Join of dialog table
                            $l_joins[$l_alias_third] = $l_join_type . " JOIN " . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . " AS " .
                                $l_alias_third . " ON " . $l_alias_third . "." . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][1] . " = " .
                                $l_alias_sec . "." . $l_table . "__" . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . "__id";
                            $this->m_already_used_aliase[] = $l_alias_third;
                        }
                    } else {
                        if (!in_array($l_alias, $this->m_already_used_aliase)) {
                            $l_joins[$l_alias] = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias . "." . $l_table .
                                "__isys_obj__id = obj_main.isys_obj__id " . $this->addMultivalueStatusFilter($l_alias, $l_table, $l_prop_data['multi']);
                        }

                        if (in_array($l_alias_sec, $this->m_already_used_aliase)) {
                            continue;
                        }

                        $l_joins[$l_alias_sec] = $l_join_type . " JOIN " . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . " AS " . $l_alias_sec . " ON " .
                            $l_alias_sec . "." . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][1] . " = " . $l_alias . '.' .
                            $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];

                        $l_joins[$l_alias_sec] .= $this->addCategorySpecificCondition($const, $l_alias, $l_table);

                        // Reference to category table
                        if (!in_array($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#isys_obj', $l_already_joined_tables) &&
                            (strpos($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 'catg') !== false ||
                                strpos($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 'cats') !== false) &&
                            strpos($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 'list') !== false) {
                            $l_alias_third = 'j' . $this->retrieve_alias($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 'isys_obj', $const);

                            if (in_array($l_alias_third, $this->m_already_used_aliase)) {
                                continue;
                            }

                            $l_already_joined_tables[] = $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#isys_obj';
                            $l_joins[$l_alias_third] = $l_join_type . " JOIN isys_obj AS " . $l_alias_third . " ON " . $l_alias_third . ".isys_obj__id = " . $l_alias_sec . "." .
                                $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__isys_obj__id';
                        }
                    }

                    if ($l_alias !== null) {
                        $this->m_already_used_aliase[] = $l_alias;
                    }
                    if ($l_alias_sec !== null) {
                        $this->m_already_used_aliase[] = $l_alias_sec;
                    }
                    if ($l_alias_third !== null) {
                        $this->m_already_used_aliase[] = $l_alias_third;
                    }
                    if ($l_alias_fourth !== null) {
                        $this->m_already_used_aliase[] = $l_alias_fourth;
                    }
                } elseif ($l_prop_data['data'][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__OBJECT_BROWSER) {
                    if ($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] == $l_table . '__isys_obj__id') {
                        if (isset($this->m_aliases[$const . '::' . 'isys_connection#' . $l_table])) {
                            $l_alias = 'j' . $this->m_aliases[$const . '::' . $l_table . '#isys_connection'];

                            if (in_array($l_alias, $this->m_already_used_aliase)) {
                                continue;
                            }

                            $l_alias_sec = 'j' . $this->m_aliases[$const . '::' . 'isys_connection#' . $l_table];

                            $l_joins[$l_alias] = $l_join_type . " JOIN isys_connection AS " . $l_alias . " ON " . $l_alias . ".isys_connection__isys_obj__id = " . $l_alias_sec . "." .
                                $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];

                            $this->m_already_used_aliase[] = $l_alias;
                        }
                    } elseif (strpos($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD], 'isys_cable_connection__id') !== false) {
                        if (isset($this->m_aliases[$const . '::' . $l_table . '#isys_obj'])) {
                            if (!in_array($l_alias, $this->m_already_used_aliase)) {
                                $l_already_joined_tables[] = $l_table . '#isys_obj';
                                $l_joins[$l_alias] = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias . "." . $l_table .
                                    "__isys_obj__id = obj_main.isys_obj__id " . $this->addMultivalueStatusFilter($l_alias, $l_table, $l_prop_data['multi']);

                                $this->m_already_used_aliase[] = $l_alias;
                            }
                        }
                        if (isset($this->m_aliases[$const . '::' . 'isys_cable_connection#' . $l_table])) {
                            $l_alias_sec = $l_alias;
                            $l_alias = 'j' . $this->m_aliases[$const . '::' . $l_table . '#isys_cable_connection'];
                            if (isset($this->m_aliases[$const . '::' . 'isys_catg_connector_list#main_obj'])) {
                                $l_alias_sec = 'j' . $this->m_aliases[$const . '::' . 'isys_catg_connector_list#isys_catg_connector_list'];
                            }

                            if (!in_array($l_alias, $this->m_already_used_aliase)) {
                                $l_joins[$l_alias] = $l_join_type . " JOIN isys_cable_connection AS " . $l_alias . " ON " . $l_alias . ".isys_cable_connection__id = " . $l_alias_sec .
                                    "." . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];

                                $this->m_already_used_aliase[] = $l_alias;
                            }
                        }
                        if (isset($this->m_aliases[$const . '::' . 'isys_cable_connection#isys_obj'])) {
                            $l_alias_sec = $l_alias;
                            $l_alias = 'j' . $this->m_aliases[$const . '::' . 'isys_obj#isys_cable_connection'];
                            if (!in_array($l_alias, $this->m_already_used_aliase)) {
                                $l_joins[$l_alias] = $l_join_type . " JOIN isys_obj AS " . $l_alias . " ON " . $l_alias . ".isys_obj__id = " . $l_alias_sec .
                                    ".isys_cable_connection__isys_obj__id";
                                $this->m_already_used_aliase[] = $l_alias;
                            }
                        }
                    } elseif (strpos($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD], 'isys_catg_connector_list__id') !== false) {
                        /**
                         * Join property table anyway before using it in joins
                         *
                         * This code snippet provides the same handling for connectors -
                         * see above "else if"-branch which handles cable connections
                         *
                         * @see ID-5471
                         */

                        // Check alias is set
                        if (isset($this->m_aliases[$const . '::' . $l_table . '#isys_obj'])) {
                            // Check whether it is unused
                            if (!in_array($l_alias, $this->m_already_used_aliase)) {
                                // Create join statement for SQL
                                $l_already_joined_tables[] = $l_table . '#isys_obj';
                                $l_joins[$l_alias] = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias . "." . $l_table .
                                    "__isys_obj__id = obj_main.isys_obj__id " . $this->addMultivalueStatusFilter($l_alias, $l_table, $l_prop_data['multi']);

                                $this->m_already_used_aliase[] = $l_alias;
                            }
                        }

                        if (isset($this->m_aliases[$const . '::' . $l_table . '#main_obj'])) {
                            $l_alias = "j" . $this->m_aliases[$const . '::' . $l_table . '#main_obj'];

                            if (!isset($l_joins[$l_alias]) && !in_array($l_alias, $this->m_already_used_aliase)) {
                                $l_already_joined_tables[] = $l_table . '#main_obj';

                                $l_join_string = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias . "." . $l_table .
                                    "__isys_obj__id = obj_main.isys_obj__id " . $this->addMultivalueStatusFilter($l_alias, $l_table, 1);

                                $l_joins[$l_alias] = $l_join_string;

                                $this->m_already_used_aliase[] = $l_alias;
                            }
                        }
                        if (isset($this->m_aliases[$const . '::' . $l_table . '#isys_catg_connector_list']) && $l_table != 'isys_catg_connector_list') {
                            $l_alias_sec = $l_alias;
                            $l_alias = 'j' . $this->m_aliases[$const . '::' . $l_table . '#isys_catg_connector_list'];

                            if (in_array($l_alias, $this->m_already_used_aliase)) {
                                continue;
                            }

                            $l_join_string = $l_join_type . " JOIN isys_catg_connector_list AS " . $l_alias . " ON " . $l_alias . ".isys_catg_connector_list__id = " .
                                $l_alias_sec . "." . $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] . ' ' .
                                $this->addMultivalueStatusFilter($l_alias, 'isys_catg_connector_list', 1);

                            $l_joins[$l_alias] = $l_join_string;

                            $this->m_already_used_aliase[] = $l_alias;
                        }
                        if ($l_table !== 'isys_catg_connector_list') {
                            if (isset($this->m_aliases[$const . '::' . $l_table . '#isys_catg_connector_list#isys_catg_connector_list'])) {
                                $l_alias_sec = $l_alias;
                                $l_alias = 'j' . $this->m_aliases[$const . '::' . $l_table . '#isys_catg_connector_list#isys_catg_connector_list'];

                                if (in_array($l_alias, $this->m_already_used_aliase)) {
                                    continue;
                                }

                                $l_join_string = $l_join_type . " JOIN isys_catg_connector_list AS " . $l_alias . " ON " . $l_alias .
                                    ".isys_catg_connector_list__isys_cable_connection__id = " . $l_alias_sec . ".isys_catg_connector_list__isys_cable_connection__id " .
                                    "AND " . $l_alias . ".isys_catg_connector_list__id != " . $l_alias_sec . ".isys_catg_connector_list__id ";
                                $l_joins[$l_alias] = $l_join_string . ' ' . $this->addMultivalueStatusFilter($l_alias, 'isys_catg_connector_list', 1);
                                $this->m_already_used_aliase[] = $l_alias;
                            }

                            if (isset($this->m_aliases[$const . '::' . 'isys_catg_connector_list#' . $l_table])) {
                                $l_alias_sec = $l_alias;
                                $l_alias = 'j' . $this->m_aliases[$const . '::' . 'isys_catg_connector_list#' . $l_table];

                                if (in_array($l_alias, $this->m_already_used_aliase)) {
                                    continue;
                                }

                                $l_join_string = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias . "." . $l_table . "__isys_catg_connector_list__id = " .
                                    $l_alias_sec . ".isys_catg_connector_list__id " . $this->addMultivalueStatusFilter($l_alias, $l_table, 1);

                                $l_joins[$l_alias] = $l_join_string;

                                $this->m_already_used_aliase[] = $l_alias;
                            }

                            if (isset($this->m_aliases[$const . '::' . $l_table . '#isys_catg_connector_list#isys_obj']) && $l_alias_sec) {
                                $l_alias = 'j' . $this->m_aliases[$const . '::' . $l_table . '#isys_catg_connector_list#isys_obj'];

                                if (in_array($l_alias, $this->m_already_used_aliase)) {
                                    continue;
                                }

                                $l_already_joined_tables[] = $l_table . '#isys_catg_connector_list#isys_obj';
                                $l_joins[$l_alias] = $l_join_type . " JOIN isys_obj AS " . $l_alias . " ON " . $l_alias . ".isys_obj__id = " . $l_alias_sec .
                                    ".isys_catg_connector_list__isys_obj__id";
                                $this->m_already_used_aliase[] = $l_alias;
                            }
                        } else {
                            if (isset($this->m_aliases[$const . '::' . 'isys_catg_connector_list#isys_catg_connector_list'])) {
                                $l_alias_sec = $l_alias;
                                $l_alias = 'j' . $this->m_aliases[$const . '::' . 'isys_catg_connector_list#isys_catg_connector_list'];
                                if (in_array($l_alias, $this->m_already_used_aliase)) {
                                    continue;
                                }
                                $l_join_string = $l_join_type . " JOIN isys_catg_connector_list AS " . $l_alias . " ON " . $l_alias .
                                    ".isys_catg_connector_list__isys_cable_connection__id = " . $l_alias_sec . ".isys_catg_connector_list__isys_cable_connection__id " .
                                    "AND " . $l_alias . ".isys_catg_connector_list__id != " . $l_alias_sec . ".isys_catg_connector_list__id ";
                                $l_joins[$l_alias] = $l_join_string . ' ' . $this->addMultivalueStatusFilter($l_alias, 'isys_catg_connector_list', 1);
                                $this->m_already_used_aliase[] = $l_alias;
                            }
                            if (isset($this->m_aliases[$const . '::' . 'isys_catg_connector_list#isys_obj'])) {
                                $l_alias_sec = $l_alias;
                                $l_alias = 'j' . $this->m_aliases[$const . '::' . 'isys_catg_connector_list#isys_obj'];
                                if (in_array($l_alias, $this->m_already_used_aliase)) {
                                    continue;
                                }
                                $l_already_joined_tables[] = $l_table . '#isys_obj';
                                $l_joins[$l_alias] = $l_join_type . " JOIN isys_obj AS " . $l_alias . " ON " . $l_alias . ".isys_obj__id = " . $l_alias_sec .
                                    ".isys_catg_connector_list__isys_obj__id";
                                $this->m_already_used_aliase[] = $l_alias;
                            }
                        }
                    } else {
                        if (!in_array($l_table . '#isys_obj', $l_already_joined_tables) && !in_array($l_alias, $this->m_already_used_aliase) && $l_alias != 'obj_main') {
                            if (in_array($l_alias, $this->m_already_used_aliase)) {
                                continue;
                            }

                            $l_already_joined_tables[] = $l_table . '#isys_obj';
                            $l_joins[$l_alias] = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias . "." . $l_table .
                                "__isys_obj__id = obj_main.isys_obj__id " . $this->addMultivalueStatusFilter($l_alias, $l_table, $l_prop_data['multi']);
                        }
                    }
                }

                if ($l_table == 'isys_logbook') {
                    if (!in_array('isys_catg_logb_list#isys_obj', $l_already_joined_tables)) {
                        $l_alias = 'j' . $this->retrieve_alias('isys_obj', 'isys_catg_logb_list', $const);

                        if (in_array($l_alias, $this->m_already_used_aliase)) {
                            continue;
                        }

                        $l_already_joined_tables[] = 'isys_catg_logb_list#isys_obj';
                        $l_joins[$l_alias] = $l_join_type . " JOIN isys_catg_logb_list AS " . $l_alias . " ON " . $l_alias .
                            ".isys_catg_logb_list__isys_obj__id = obj_main.isys_obj__id " . $this->addMultivalueStatusFilter($l_alias, 'isys_catg_logb_list', 1);
                    }

                    if (!in_array('isys_logbook#isys_catg_logb_list', $l_already_joined_tables)) {
                        $l_alias = 'j' . $this->retrieve_alias('isys_catg_logb_list', 'isys_logbook', $const);
                        $l_alias_ref = 'j' . $this->retrieve_alias('isys_obj', 'isys_catg_logb_list', $const);

                        if (in_array($l_alias, $this->m_already_used_aliase)) {
                            continue;
                        }

                        $l_already_joined_tables[] = 'isys_logbook#isys_catg_logb_list';
                        $l_joins[$l_alias] = $l_join_type . " JOIN isys_logbook AS " . $l_alias . " ON " . $l_alias . ".isys_logbook__id = " . $l_alias_ref .
                            ".isys_catg_logb_list__isys_logbook__id";
                    }
                    continue;
                }

                if (!in_array($l_table . '#isys_obj', $l_already_joined_tables) && !in_array($l_alias, $this->m_already_used_aliase) && $l_alias != 'obj_main') {
                    if (isset($this->m_aliases[$const . '::' . $l_table . '#main_obj'])) {
                        $l_alias = 'j' . $this->retrieve_alias('main_obj', $l_table, $const);
                    }

                    if (in_array($l_alias, $this->m_already_used_aliase)) {
                        continue;
                    }

                    $l_already_joined_tables[] = $l_table . '#isys_obj';

                    $referenceField = ($l_table === 'isys_obj' ? 'isys_obj__id': $l_table . '__isys_obj__id');

                    $l_join = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias . "." . $referenceField . " = obj_main.isys_obj__id " .
                        $this->addMultivalueStatusFilter($l_alias, $l_table, $l_prop_data['multi']);
                    $l_join .= $this->add_join_condition($l_prop_data['data'][C__PROPERTY__DATA], $l_table, $l_alias);
                    $l_joins[$l_alias] = $l_join;
                }

                $this->m_already_used_aliase[] = $l_alias;
            }
        }

        $joins = array_merge_recursive($this->reportQuery->getJoins(), $l_joins);
        $this->reportQuery->setJoins($joins);

        return $l_joins;
    }

    /**
     * Creates the condition for the query
     *
     * @param $p_conditions
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function create_property_query_condition($p_conditions)
    {
        $l_return = [
            'conditions' => '',
            'joins'      => []
        ];

        // Set Report Selection context @see ID-7701
        Context::instance()->setContextTechnical(Context::CONTEXT_REPORT_CONDITION);

        // In this array we will save all the conditions for the report.
        $l_condition_complete = ' AND ';

        if ($this->m_query_as_report) {
            $l_alias_field = 'isys_obj__id';
        } else {
            $l_alias_field = 'isys_obj__title';
        }

        try {
            $this->conditionProviders['comparison'] = ComparisonProvider::factory();
            $this->conditionProviders['property'] = PropertyProvider::factory();
            $this->conditionProviders['propertyType'] = PropertyTypeProvider::factory();
            $this->conditionProviders['default'] = new DefaultCondition();
            $this->conditionProviders['dynamic'] = DynamicPropertyProvider::factory();

            foreach ($p_conditions as $index => $l_condition_block) {
                if (is_array($l_condition_block)) {
                    $l_inner_condition = '';

                    // Block Condition
                    foreach ($l_condition_block as $l_condition) {
                        // First Level
                        // Join from main object
                        $l_unit_id = null;
                        $l_unit_property = null;
                        $l_unit_field = null;
                        $l_special_selection = false;
                        $l_special_field = null;
                        $l_loc_condition_lft = null;
                        $l_loc_condition_rgt = null;
                        $l_property = $l_condition['property'];
                        [$l_category, $l_prop_key] = explode('-', $l_property);
                        $l_comparison = $l_condition['comparison'];
                        $l_unit = $l_condition['unit'];
                        if (strpos($l_unit, '-') !== false) {
                            [$l_unit_id, $l_unit_property] = explode('-', $l_unit);
                        }
                        $l_operator = $l_condition['operator'];
                        $l_sub_conditions = $l_condition['subcnd'];

                        if (!isset($this->m_property_rows[$l_property])) {
                            // This query will be used to receive all the necessary entries from the isys_property_2_cat table.
                            $l_property_condition = ' AND isys_property_2_cat__cat_const = ' . $this->convert_sql_text($l_category) . ' AND isys_property_2_cat__prop_key = ' .
                                $this->convert_sql_text($l_prop_key);
                            $l_row = $this->retrieve_properties(null, null, null, null, $l_property_condition, true)
                                ->get_row();

                            $l_cat_dao = $this->get_dao_instance($l_row['class'], ($l_row['catg_custom'] ?: null));
                            $l_properties = array_merge($l_cat_dao->get_properties(), $l_cat_dao->get_dynamic_properties());
                            $l_row['data'] = $l_properties[$l_row['key']];

                            $this->formatTable($l_row);

                            // We save every row, because we will need them quite often in the upcoming code.
                            $this->m_property_rows[$l_property] = $l_row['id'];

                            if (!isset($this->m_property_rows[$l_row['id']])) {
                                $this->m_property_rows[$l_row['id']] = $l_row;
                                $this->create_alias($l_row);
                            }

                            $l_select = $l_row['id'];
                            // Also we create table aliases for each possible join.

                            // Also we save some information, so that the logic will not try to join the "isys_obj" or "isys_cmdb_status" tables.
                            $this->m_aliases[$l_row['const'] . '::' . 'isys_obj#isys_obj'] = $this->m_alias_cnt++;
                        } else {
                            $l_select = (is_array($this->m_property_rows[$l_property])) ? $this->m_property_rows[$l_property]['id']: $this->m_property_rows[$l_property];
                        }

                        $objectBrowserSecondSelection = (!isset($this->m_property_rows[$l_select]['data'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][isys_popup_browser_object_ng::C__SECOND_SELECTION]) ?
                            false : $this->m_property_rows[$l_select]['data'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][isys_popup_browser_object_ng::C__SECOND_SELECTION]);

                        $const = (!is_array($this->m_property_rows[$l_property]) ? $this->m_property_rows[$this->m_property_rows[$l_property]]['const'] : $this->m_property_rows[$l_property]['const']);

                        if ($l_unit_id !== null) {
                            $l_ignore_unit_field = false;
                            if (isset($this->m_property_rows[$l_select]['data'][C__PROPERTY__FORMAT])) {
                                if (isset($this->m_property_rows[$l_select]['data'][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][2])) {
                                    $l_ignore_unit_field = ($this->m_property_rows[$l_select]['data'][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][2][0] !== null);
                                }
                            }
                            if (!isset($this->m_property_rows[$l_category . '-' . $l_unit_property])) {
                                $l_property_condition = ' AND isys_property_2_cat__cat_const = ' . $this->convert_sql_text($l_category) .
                                    ' AND isys_property_2_cat__prop_key = ' . $this->convert_sql_text($l_unit_property);
                                $l_row = $this->retrieve_properties(null, null, null, null, $l_property_condition, true)
                                    ->get_row();

                                $l_cat_dao = $this->get_dao_instance($l_row['class'], ($l_row['catg_custom'] ?: null));
                                $l_properties = array_merge($l_cat_dao->get_properties(), $l_cat_dao->get_dynamic_properties());
                                $l_row['data'] = $l_properties[$l_row['key']];

                                $this->formatTable($l_row);

                                // We save every row, because we will need them quite often in the upcoming code.
                                $this->m_property_rows[$l_category . '-' . $l_unit_property] = $l_row;
                            }
                            if (!$l_ignore_unit_field) {
                                $l_unit_field = $this->m_property_rows[$l_category . '-' . $l_unit_property][C__PROPERTY__DATA][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                            }
                        }

                        if (!isset($l_return['joins'][$l_select]) &&
                            $this->m_property_rows[$l_select][C__PROPERTY__DATA][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] !== C__PROPERTY__INFO__TYPE__MULTISELECT) {
                            $l_return['joins'][$l_select] = $this->create_property_query_join([$l_select]);
                        }

                        $l_table = current(explode('__', $this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]));
                        $dbField = $this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                        $referenceTable = $this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] ?: null;

                        if ($l_table == 'isys_catg_custom_fields_list') {
                            $l_field_alias = $this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
                            $l_field_type = $this->m_property_rows[$l_select]['data'][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE];
                            $uiParams = $this->m_property_rows[$l_select]['data'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS];
                            $l_alias = 'j' . $this->retrieve_alias($l_field_alias . '#isys_catg_custom_fields_list', null, $const);

                            switch ($l_field_type) {
                                case C__PROPERTY__INFO__TYPE__OBJECT_BROWSER:
                                case C__PROPERTY__INFO__TYPE__N2M:
                                    $l_alias_sec = 'j' . $this->retrieve_alias('isys_catg_custom_fields_list', $l_field_alias . '#isys_obj', $const);
                                    $l_condition_field = $l_alias_sec . '.isys_obj__id';
                                    break;
                                case C__PROPERTY__INFO__TYPE__DIALOG:
                                case C__PROPERTY__INFO__TYPE__DIALOG_PLUS:
                                case C__PROPERTY__INFO__TYPE__MULTISELECT:

                                    if (isset($uiParams['extra']) && $uiParams['extra'] === 'yes-no') {
                                        $l_condition_field = $l_alias . '.' . $l_table . '__field_content';
                                        break;
                                    }

                                    $l_alias_sec = 'j' . $this->retrieve_alias('isys_catg_custom_fields_list', $l_field_alias . '#isys_dialog_plus_custom', $const);
                                    $l_condition_field = $l_alias_sec . '.isys_dialog_plus_custom__id';

                                    if ($l_field_type === C__PROPERTY__INFO__TYPE__MULTISELECT && $uiParams['popup'] !== 'checkboxes') {
                                        $l_condition_field = 'obj_main.isys_obj__id';
                                    }
                                    break;
                                default:
                                    $l_condition_field = $l_alias . '.' . $l_table . '__field_content';
                                    break;
                            }
                        } else {
                            // First we check for some special selected fields.
                            if (isset($this->m_special_selects[$dbField])) {
                                // Check if its a primary field
                                if (is_array($this->m_special_selects[$dbField])) {
                                    $l_special_selection = true;
                                    $l_table = $this->m_special_selects[$dbField][0];

                                    if ($l_table == 'isys_obj' && !$this->m_query_as_report) {
                                        $l_special_field = 'isys_obj__id';
                                        $l_table = $this->m_special_selects[$dbField][1];
                                    } else {
                                        if (empty($this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES])) {
                                            $this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES] = [];
                                        }

                                        $this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES] = array_replace(
                                            $this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES],
                                            [$this->m_special_selects[$dbField][1]]
                                        );
                                    }
                                }
                            }

                            if (($referenceTable !== null && substr($referenceTable, 0, 5) == 'isys_') ||
                                $this->m_property_rows[$l_select]['data'][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__OBJECT_BROWSER
                            ) {
                                if (($referenceTable === null || $referenceTable === 'isys_obj') &&
                                    $l_table == 'isys_obj'
                                ) {
                                    $l_ref_table_arr = explode('__', $dbField);
                                    $l_ref_table = $l_table;
                                    $l_table = $l_ref_table_arr[0];
                                    $l_special_field = 'isys_obj__id';
                                } elseif (
                                    ($this->m_property_rows[$l_select]['data'][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__OBJECT_BROWSER && !$objectBrowserSecondSelection) ||
                                    ($l_comparison === 'subcnd' && $objectBrowserSecondSelection)) {
                                    $l_ref_table = $referenceTable;
                                    if ($l_ref_table !== null) {
                                        if ($l_table == 'isys_obj' && $l_ref_table != 'isys_obj') {
                                            $l_puffer = $l_table;
                                            $l_table = $l_ref_table;
                                            $l_ref_table = $l_puffer;
                                        } elseif ($l_table != 'isys_obj' && $l_ref_table != 'isys_obj') {
                                            if ($l_ref_table != 'isys_connection') {
                                                $l_table = $l_ref_table;
                                                $l_ref_table = 'isys_obj';
                                            }
                                        }
                                        $l_special_field = 'isys_obj__id';
                                    } else {
                                        $l_ref_table = 'isys_obj';
                                    }
                                } else {
                                    $l_ref_table = $referenceTable;
                                }

                                $l_alias = $this->retrieve_alias($l_table, $l_ref_table, $const);

                                if ($l_comparison == 'under_location') {
                                    $l_alias = $this->retrieve_alias($l_ref_table, $l_table, $const);
                                    $l_condition['location_lft'] = 'j' . $l_alias . '.isys_catg_location_list__lft';
                                    $l_condition['location_rgt'] = 'j' . $l_alias . '.isys_catg_location_list__rgt';
                                }

                                // We have to join 'job' on references to isys_connection.
                                if ($l_ref_table == 'isys_connection') {
                                    if ($l_special_selection) {
                                        // it is not an jobx alias
                                        $l_alias = $this->retrieve_alias($l_table, 'isys_obj', $const);
                                        $conditionAlias = "j" . $l_alias;
                                    } else {
                                        $conditionAlias = "job" . $l_alias;
                                    }

                                    $l_condition_field = $conditionAlias . "." . $l_alias_field;
                                } elseif ($l_table == 'isys_contact') {
                                    $l_alias = $this->retrieve_alias('isys_obj', 'isys_contact_2_isys_obj', $const);
                                    $l_condition_field = "job" . $l_alias . "." . $l_alias_field;
                                } elseif ($this->m_property_rows[$l_select]['data'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_location') {
                                    $l_alias = $this->retrieve_alias($l_table, 'isys_obj', $const);
                                    $l_condition_field = "j" . $l_alias . "." . $l_alias_field;
                                } elseif (strpos($dbField, 'isys_cable_connection__id') !==
                                    false) {
                                    $l_alias = $this->retrieve_alias('isys_cable_connection', 'isys_obj', $const);
                                    $l_condition_field = "j" . $l_alias . "." . $l_alias_field;
                                } elseif (strpos($dbField, '__isys_catg_log_port_list__id') !== false) {
                                    $l_alias = $this->retrieve_alias('isys_catg_log_port_list', 'isys_catg_log_port_list', $const);
                                    $l_condition_field = 'j' . $l_alias . '.' . $l_table . '__id';
                                } elseif (strpos($dbField, 'isys_catg_connector_list__id') !==
                                    false) {
                                    if ($l_table != 'isys_catg_connector_list' || $l_comparison !== 'subcnd') {
                                        $l_alias = $this->retrieve_alias('isys_catg_connector_list', $l_table, $const);
                                        $field = 'isys_catg_connector_list__id';
                                    } else {
                                        $l_alias = $this->retrieve_alias('isys_obj', 'isys_catg_connector_list', $const);
                                        $field = 'isys_obj__id';
                                    }
                                    $l_condition_field = "j" . $l_alias . "." . $field;
                                } elseif ($this->m_property_rows[$l_select][C__PROPERTY__DATA][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__MULTISELECT ||
                                    $dbField === 'isys_cats_database_schema_list__isys_cats_db_instance_list__id') {
                                    // VQH: Find a better solution for isys_cats_database_schema_list__isys_cats_db_instance_list__id

                                    $l_alias = 'obj_main';
                                    $l_condition_field = 'obj_main.isys_obj__id';
                                } else {
                                    if ($l_special_selection) {
                                        $l_condition_field = "j" . $l_alias . "." . (($l_special_field !==
                                                null) ? $l_special_field : $dbField);
                                    } elseif ($l_ref_table == 'isys_obj') {
                                        $l_condition_field = "j" . $l_alias . ".isys_obj__id";
                                    } else {
                                        $l_condition_field = "j" . $l_alias . "." . $l_ref_table . "__id";
                                    }

                                    if (isset($this->m_text_fields[$this->m_property_rows[$l_select]['const']])) {
                                        if (isset($this->m_text_fields[$this->m_property_rows[$l_select]['const']][$this->m_property_rows[$l_select]['key']])) {
                                            $l_condition_field = substr($l_condition_field, 0, (strpos($l_condition_field, '.') + 1)) .
                                                $this->m_text_fields[$this->m_property_rows[$l_select]['const']][$this->m_property_rows[$l_select]['key']];
                                        }
                                    }
                                }
                            } else {
                                if ($l_table == 'isys_logbook') {
                                    $l_alias = 'j' . $this->retrieve_alias('isys_catg_logb_list', $l_table, $const);
                                } else {
                                    $l_alias = 'j' . $this->retrieve_alias($l_table, null, $const);
                                }

                                if ($l_special_selection && isset($this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN]) &&
                                    count($this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN])
                                ) {
                                    $propertyJoins = $this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN];
                                    $referenceTable = $propertyJoins[0]->getTable();
                                    $l_alias = 'j' . $this->retrieve_alias($referenceTable, $l_table, $const);
                                }

                                // Then we check for special table-names inside the select.
                                if (isset($this->m_aliases[$const . '::' . $l_table])) {
                                    $l_alias = $this->m_aliases[$const . '::' . $l_table];
                                }

                                if ($l_alias == 'obj_main' &&
                                    (strpos($dbField, 'isys_obj') > 0)) {
                                    $l_table = current(explode('__', $dbField));
                                    $l_alias = 'j' . $this->retrieve_alias("isys_obj", $l_table, $const);
                                }
                                $l_condition_field = $l_alias . "." . $dbField;
                            }
                        }
                        if (isset($l_condition_field) && isset($this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_FUNCTION]) &&
                            is_callable($this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_FUNCTION])) {
                            $function = $this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_FUNCTION];
                            $l_condition_field = $function($l_condition_field);
                        }

                        if ($l_comparison == 'PLACEHOLDER') {
                            $l_comparison .= '.' . $index;
                            $l_condition['comparison'] = $l_comparison;

                            if (!empty($l_condition['user_input'])) {
                                $l_condition['value'] .= ' ' . $l_condition['user_input'];
                            }
                        }

                        if ($l_comparison == 'subcnd' && is_array($l_sub_conditions)) {
                            // Sub Levels
                            // Join from category to referenced object
                            $this->m_referenced_fields[$l_property] = $l_condition_field;
                            $l_sub_inner_condition_string = $this->handle_sub_conditions($l_property, $l_sub_conditions, $l_condition_field);
                            $l_inner_condition .= ' (' . $l_sub_inner_condition_string . ') ' . $l_operator;
                        } else {
                            $l_condition['alias'] = $l_alias;
                            $l_condition['unitField'] = $l_unit_field;
                            $l_condition['unitId'] = $l_unit_id;

                            $reference = 'obj_main.isys_obj__id';

                            $l_inner_condition = $this->build_inner_condition(
                                $l_inner_condition,
                                $this->m_property_rows[$l_select]['data'],
                                $l_condition_field,
                                $l_condition,
                                $this->m_property_rows[$l_select]['type'],
                                $reference
                            );
                        }
                    }
                    $l_condition_complete .= ' (' . $l_inner_condition . ') ';
                } else {
                    // Block comparison
                    $l_condition_complete .= $l_condition_block;
                }
            }
        } catch (Exception $e) {
            isys_notify::error($e->getMessage());
        }
        $l_return['conditions'] = (trim($l_condition_complete) == 'AND') ? '' : $l_condition_complete;

        return $l_return;
    }

    /**
     * Method for retrieving properties.
     *
     * @param mixed   $p_property_id May be a array or an integer.
     * @param mixed   $p_catg_id     May be a array or an integer.
     * @param mixed   $p_cats_id     May be a array or an integer.
     * @param integer $p_provides
     * @param string  $p_condition
     * @param boolean $p_dynamic_properties
     * @param mixed   $p_catg_custom_id
     * @param string  $additonalOrdering
     *
     * @return  isys_component_dao_result
     * @throws isys_exception_database
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function retrieve_properties(
        $p_property_id = null,
        $p_catg_id = null,
        $p_cats_id = null,
        $p_provides = null,
        $p_condition = "",
        $p_dynamic_properties = false,
        $p_catg_custom_id = null,
        $additonalOrdering = ''
    ) {
        $l_condition = " WHERE TRUE ";

        if ($p_property_id !== null) {
            if (is_array($p_property_id)) {
                $l_condition .= "AND isys_property_2_cat__id " . $this->prepare_in_condition($p_property_id) . " ";
            } elseif (is_numeric($p_property_id)) {
                $l_condition .= "AND isys_property_2_cat__id = " . $this->convert_sql_id($p_property_id) . " ";
            }
        }

        if ($p_catg_id !== null) {
            if (is_array($p_catg_id)) {
                $l_condition .= "AND isys_property_2_cat__isysgui_catg__id " . $this->prepare_in_condition($p_catg_id) . " ";
            } elseif (is_numeric($p_catg_id)) {
                $l_condition .= "AND isys_property_2_cat__isysgui_catg__id = " . $this->convert_sql_id($p_catg_id) . " ";
            }
        }

        if ($p_cats_id !== null) {
            if (is_array($p_cats_id)) {
                $l_condition .= "AND isys_property_2_cat__isysgui_cats__id " . $this->prepare_in_condition($p_cats_id) . " ";
            } elseif (is_numeric($p_cats_id)) {
                $l_condition .= "AND isys_property_2_cat__isysgui_cats__id = " . $this->convert_sql_id($p_cats_id) . " ";
            }
        }

        if ($p_catg_custom_id !== null) {
            if (is_array($p_catg_custom_id)) {
                $l_condition .= "AND isys_property_2_cat__isysgui_catg_custom__id " . $this->prepare_in_condition($p_catg_custom_id) . " ";
            } elseif (is_numeric($p_catg_custom_id)) {
                $l_condition .= "AND isys_property_2_cat__isysgui_catg_custom__id = " . $this->convert_sql_id($p_catg_custom_id) . " ";
            }
        }

        if ($p_provides !== null && $p_provides > 0) {
            $l_condition .= "AND isys_property_2_cat__prop_provides & " . $this->convert_sql_int($p_provides) . " ";
        }

        // @see ID-7603 Add addional ordering so that custom categories are being joined at the end of the query
        $order = ' ORDER BY isys_property_2_cat__prop_type ASC ' . ($additonalOrdering ? ', ' . $additonalOrdering : '');

        if (!$p_dynamic_properties) {
            $l_condition .= "AND isys_property_2_cat__prop_type = " . C__PROPERTY_TYPE__STATIC . " ";
            $order = '';
        }

        $l_category_join = " LEFT JOIN isysgui_catg ON isysgui_catg__id = isys_property_2_cat__isysgui_catg__id " .
            " LEFT JOIN isysgui_cats ON isysgui_cats__id = isys_property_2_cat__isysgui_cats__id " .
            " LEFT JOIN isysgui_catg_custom ON isysgui_catg_custom__id = isys_property_2_cat__isysgui_catg_custom__id ";

        // We rename the fields for easier usage.
        $l_sql = "SELECT isys_property_2_cat__id AS 'id', " . "isys_property_2_cat__isysgui_catg__id AS 'catg', " . "isys_property_2_cat__isysgui_cats__id AS 'cats', " .
            "isys_property_2_cat__isysgui_catg_custom__id AS 'catg_custom', " . "isys_property_2_cat__cat_const AS 'const', " . "isys_property_2_cat__prop_type AS 'type', " .
            "isys_property_2_cat__prop_title AS 'title', " . "isys_property_2_cat__prop_key AS 'key', " . "(
                CASE 
                    WHEN isys_property_2_cat__isysgui_catg__id IS NOT NULL THEN isysgui_catg__list_multi_value 
                    WHEN isys_property_2_cat__isysgui_cats__id IS NOT NULL THEN isysgui_cats__list_multi_value 
                    WHEN isys_property_2_cat__isysgui_catg_custom__id IS NOT NULL THEN isysgui_catg_custom__list_multi_value 
                END
            ) AS 'multi', " . "(
                CASE 
                    WHEN isys_property_2_cat__isysgui_catg__id IS NOT NULL THEN isysgui_catg__class_name 
                    WHEN isys_property_2_cat__isysgui_cats__id IS NOT NULL THEN isysgui_cats__class_name 
                    WHEN isys_property_2_cat__isysgui_catg_custom__id IS NOT NULL THEN isysgui_catg_custom__class_name 
                END
             ) AS 'class', " . "(
                CASE 
                    WHEN isys_property_2_cat__isysgui_catg__id IS NOT NULL THEN isysgui_catg__source_table 
                    WHEN isys_property_2_cat__isysgui_cats__id IS NOT NULL THEN isysgui_cats__source_table 
                    WHEN isys_property_2_cat__isysgui_catg_custom__id IS NOT NULL THEN 'isys_catg_custom_fields_list' 
                END
             ) AS 'table', " . "(
                CASE
                    WHEN isys_property_2_cat__isysgui_catg__id IS NOT NULL THEN (SELECT isysgui_catg__parent FROM isysgui_catg WHERE isysgui_catg__id = isys_property_2_cat__isysgui_catg__id) 
                    WHEN isys_property_2_cat__isysgui_cats__id IS NOT NULL THEN (SELECT isysgui_cats_2_subcategory__isysgui_cats__id__parent FROM isysgui_cats_2_subcategory WHERE isysgui_cats_2_subcategory__isysgui_cats__id__child = isys_property_2_cat__isysgui_cats__id LIMIT 1)
                    WHEN isys_property_2_cat__isysgui_catg_custom__id IS NOT NULL THEN null 
                END
            ) AS 'parent', " . "isys_property_2_cat__prop_provides AS provides " . "FROM isys_property_2_cat " . $l_category_join . $l_condition;

        return $this->retrieve($l_sql . $p_condition . $order . ";");
    }

    /**
     * This method retrieves all categories which have at least one properties that fit the given provide-parameter.
     *
     * @param   string  $p_category_type
     * @param   integer $p_provide
     * @param   boolean $p_dynamic_property
     *
     * @return  isys_component_dao_result
     */
    public function retrieve_categories_by_provide($p_provide, $p_category_type, $p_dynamic_property = false)
    {
        $l_sql = 'SELECT * FROM isys_property_2_cat
			LEFT JOIN isysgui_catg ON isysgui_catg__id = isys_property_2_cat__isysgui_catg__id
			LEFT JOIN isysgui_cats ON isysgui_cats__id = isys_property_2_cat__isysgui_cats__id
			LEFT JOIN isysgui_catg_custom ON isysgui_catg_custom__id = isys_property_2_cat__isysgui_catg_custom__id
			WHERE isys_property_2_cat__isysgui_cat' . $p_category_type . '__id > 0';

        if ($p_provide !== null) {
            $l_sql .= ' AND isys_property_2_cat__prop_provides & ' . $this->convert_sql_int($p_provide);
        }

        if (!$p_dynamic_property) {
            $l_sql .= ' AND isys_property_2_cat__prop_type = ' . $this->convert_sql_int(C__PROPERTY_TYPE__STATIC);
        }

        return $this->retrieve($l_sql . ' GROUP BY isys_property_2_cat__isysgui_cat' . $p_category_type . '__id;');
    }

    /**
     * Method which renews the property_2_cat table
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function rebuild_properties()
    {
        $l_log = isys_log_migration::get_instance();

        if ($l_log->get_log_file() == '') {
            $l_log_file = isys_application::instance()->app_path . '/log/prop_' . date('Y-m-d') . '.log';
            $l_log->set_log_file($l_log_file);
        }

        $l_log->set_log_level(isys_log::C__ALL);

        $l_result = (new isys_update_property_migration)
            ->set_database($this->m_db)
            ->reset_property_table()
            ->collect_category_data()
            ->prepare_sql_queries('g')
            ->prepare_sql_queries('s')
            ->prepare_sql_queries('g_custom')
            ->execute_sql()
            ->get_results();

        $l_log->flush_log();

        return $l_result;
    }

    /**
     * Retrieves data by property chain.
     *
     * @param   integer $p_obj_id
     * @param   string  $p_chain
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function retrieve_chained_obj_id($p_obj_id, $p_chain)
    {
        $l_return = null;
        $l_assigned_key = null;
        $l_selects = null;
        $l_joins = [];
        $l_sub_joins = [];
        if (strpos($p_chain, '--') !== false) {
            // more than one level chain
            $l_chain_arr = explode('--', $p_chain);
            $l_obj_lvl_select = $l_obj_select = [];
            foreach ($l_chain_arr as $l_key => $l_ref_key) {
                [$l_category_const, $l_prop_key] = explode('-', $l_ref_key);

                if (!isset($this->m_property_rows[$l_ref_key])) {
                    $l_condition = ' AND isys_property_2_cat__cat_const = ' . $this->convert_sql_text($l_category_const) . ' AND isys_property_2_cat__prop_key = ' .
                        $this->convert_sql_text($l_prop_key);
                    $l_row = $this->retrieve_properties(null, null, null, null, $l_condition, true)
                        ->get_row();

                    $l_cat_dao = $this->get_dao_instance($l_row['class'], ($l_row['catg_custom'] ?: null));
                    $l_properties = array_merge($l_cat_dao->get_properties(), $l_cat_dao->get_dynamic_properties());
                    $l_row['data'] = $l_properties[$l_row['key']];

                    $this->formatTable($l_row);

                    $this->m_property_rows[$l_row['id']] = $l_row;
                    $this->m_property_rows[$l_ref_key] = $l_row['id'];
                } else {
                    $l_row = $this->m_property_rows[$this->m_property_rows[$l_ref_key]];
                }

                if ($l_key == 0 && $l_assigned_key === null) {
                    $l_prop_data['data'] = $l_row['data'];

                    $this->create_alias($l_prop_data);
                    $l_selects = $this->create_property_query_select([$l_row['id']]);
                    $l_obj_select = str_replace('title', 'id', $l_selects[$l_row['id']]);
                    $l_joins = $this->create_property_query_join([$l_row['id']]);
                    $l_assigned_key = $l_row['id'];
                } else {
                    $l_lvls_arr = [
                        $l_assigned_key => isys_format_json::encode([$l_row['id']])
                    ];

                    $l_condition_field = substr($l_obj_select, 0, strpos($l_obj_select, ' '));

                    $l_lvls_select = $this->create_property_query_lvls_select($l_lvls_arr, $l_selects);
                    $l_sub_joins[] = array_pop($this->create_property_query_join_lvls($l_lvls_arr, $l_selects, $l_obj_lvl_select, false, $l_condition_field));
                    $l_assigned_key .= '--' . $l_row['id'];

                    $l_obj_select = str_replace('__title', '__id', array_pop($l_lvls_select));
                }
            }

            if (is_countable($l_sub_joins) && count($l_sub_joins) > 0) {
                foreach ($l_sub_joins as $l_lvl_join) {
                    $l_joins = array_merge($l_joins, $l_lvl_join);
                }
            }
        } else {
            [$l_category_const, $l_prop_key] = explode('-', $p_chain);

            if (!isset($this->m_property_rows[$p_chain])) {
                $l_condition = ' AND isys_property_2_cat__cat_const = ' . $this->convert_sql_text($l_category_const) . ' AND isys_property_2_cat__prop_key = ' .
                    $this->convert_sql_text($l_prop_key);
                $l_row = $this->retrieve_properties(null, null, null, null, $l_condition, true)
                    ->get_row();

                if ($l_row !== null) {
                    $l_cat_dao = $this->get_dao_instance($l_row['class'], ($l_row['catg_custom'] ?: null));
                    $l_properties = array_merge($l_cat_dao->get_properties(), $l_cat_dao->get_dynamic_properties());
                    $l_row['data'] = $l_properties[$l_row['key']];

                    $this->formatTable($l_row);

                    $this->m_property_rows[$l_row['id']] = $l_row;
                    $this->m_property_rows[$p_chain] = $l_row['id'];
                }
            } else {
                $l_row = $this->m_property_rows[$this->m_property_rows[$p_chain]];
            }

            $l_prop_data['data'] = $l_row['data'];

            $this->create_alias($l_prop_data);
            $l_selects = $this->create_property_query_select([$l_row['id']]);

            $l_obj_select = str_replace('title', 'id', $l_selects[$l_row['id']]);
            $l_joins = $this->create_property_query_join([$l_row['id']]);
        }

        $l_sql = "SELECT \n" . $l_obj_select . " \n\n" . "FROM isys_obj AS obj_main \n" . ((is_countable($l_joins) && count($l_joins) > 0) ? implode(" \n", $l_joins) : '') . " \n\n" . "WHERE TRUE \n" .
            "AND obj_main.isys_obj__id = " . $this->convert_sql_id($p_obj_id);

        try {
            $l_res = $this->retrieve($l_sql);

            if (is_countable($l_res) && count($l_res) > 0) {
                $l_return = [];

                while ($l_obj_row = $l_res->get_row()) {
                    $l_return[] = array_pop($l_obj_row);
                }
            }
        } catch (Exception $e) {
            isys_notify::error('An Error occurred: ' . $e->getMessage() . ' File: ' . $e->getFile());
        }

        return $l_return;
    }

    /**
     * Method for creating an alias to the given properties.
     *
     * @param   array $p_props
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @todo    Change the alias namespaces to "a_table.a_column-b_table.b_column => counter".
     * @todo    Also change the parameters to "$p_from" and "$p_to".
     */
    protected function create_alias(array $p_props)
    {
        $l_table = null;
        if ($p_props['data'] instanceof Property) {
            if ($p_props['data']->getData()->offsetExists(Property::C__PROPERTY__DATA__SOURCE_TABLE) &&
                !$p_props['data']->getData()->offsetExists(Property::C__PROPERTY__DATA__REFERENCES)) {
                $l_table = $p_props['data']->getData()->getSourceTable();
            }
        }

        if ($l_table === null && $p_props['type'] == C__PROPERTY_TYPE__DYNAMIC) {
            $l_table = $p_props['table'];
        }

        if ($l_table === null) {
            $l_table = current(explode('__', $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]));
        }

        $daoClass = $p_props['class'];
        $propField = $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
        $specialJoinKey = $daoClass . '::' . $propField;
        $specialJoinKeyWildcard = $daoClass . '::*';

        if (($this->m_special_joins[$specialJoinKey] === true || $this->m_special_joins[$specialJoinKeyWildcard] === true) &&
            isset($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN])) {
            $this->createSpecialAliase($specialJoinKey, $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN], $p_props['const']);

            return;
        }

        if ($l_table == 'isys_catg_custom_fields_list') {
            $this->m_aliases[$p_props['const'] . '::' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] . '#isys_catg_custom_fields_list#isys_obj'] = $this->m_alias_cnt;
            $this->m_alias_cnt++;

            if (isset($p_props['data'][C__PROPERTY__UI][C__PROPERTY__UI__TYPE]) &&
                ($p_props['data'][C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == 'f_popup' || $p_props['data'][C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == 'popup')) {
                if ($p_props['data'][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__OBJECT_BROWSER) {
                    if (!isset($this->m_aliases[$p_props['const'] . '::' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] . '#isys_obj#isys_catg_custom_fields_list'])) {
                        $this->m_aliases[$p_props['const'] . '::' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] . '#isys_obj#isys_catg_custom_fields_list'] = $this->m_alias_cnt;
                        $this->m_alias_cnt++;
                    }
                } else {
                    if (!isset($this->m_aliases[$p_props['const'] . '::' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] .
                        '#isys_dialog_plus_custom#isys_catg_custom_fields_list'])) {
                        $this->m_aliases[$p_props['const'] . '::' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] .
                        '#isys_dialog_plus_custom#isys_catg_custom_fields_list'] = $this->m_alias_cnt;
                        $this->m_alias_cnt++;
                    }
                }
            }

            return;
        } elseif ($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] !== null &&
            strpos(' ' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 'isys_') &&
            !isset($this->m_aliases[$p_props['const'] . '::' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#' . $l_table])) {
            if ($l_table == 'isys_logbook') {
                if (!isset($this->m_aliases[$p_props['const'] . '::' . 'isys_catg_logb_list#isys_obj'])) {
                    $this->m_aliases[$p_props['const'] . '::' . 'isys_catg_logb_list#isys_obj'] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                    $this->m_aliases[$p_props['const'] . '::' . 'isys_logbook#isys_catg_logb_list'] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                }
            }

            if (!isset($this->m_aliases[$p_props['const'] . '::' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#' . $l_table])) {
                $this->m_aliases[$p_props['const'] . '::' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#' . $l_table] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }

            if ($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == 'isys_contact') {
                if (!isset($this->m_aliases[$p_props['const'] . '::' . 'isys_contact_2_isys_obj#' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]])) {
                    $this->m_aliases[$p_props['const'] . '::' . 'isys_contact_2_isys_obj#' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                }
                if (!isset($this->m_aliases[$p_props['const'] . '::' . 'isys_contact_2_isys_obj#isys_obj'])) {
                    $this->m_aliases[$p_props['const'] . '::' . 'isys_contact_2_isys_obj#isys_obj'] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                }
            }

            if (((strpos($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 'catg') !== false ||
                    strpos($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 'cats') !== false) &&
                strpos($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], '_list') !== false)) {
                if (!isset($this->m_aliases[$p_props['const'] . '::' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#isys_obj'])) {
                    $this->m_aliases[$p_props['const'] . '::' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#isys_obj'] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                }
                if (!isset($this->m_aliases[$p_props['const'] . '::' . 'isys_obj#' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]])) {
                    $this->m_aliases[$p_props['const'] . '::' . 'isys_obj#' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                }
            }

            if ($l_table == 'isys_logbook') {
                return;
            }
        } elseif ($l_table == 'isys_catg_logb_list' || $l_table == 'isys_logbook') {
            if (!isset($this->m_aliases[$p_props['const'] . '::' . 'isys_catg_logb_list#isys_obj'])) {
                $this->m_aliases[$p_props['const'] . '::' . 'isys_catg_logb_list#isys_obj'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
                $this->m_aliases[$p_props['const'] . '::' . 'isys_logbook#isys_catg_logb_list'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }

            if ($l_table == 'isys_logbook') {
                return;
            }
        } elseif ($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] == $l_table . '__isys_obj__id') {
            if ($l_table == 'isys_cats_net_ip_addresses_list') {
                if (!isset($this->m_aliases[$p_props['const'] . '::' . 'isys_catg_ip_list#isys_obj'])) {
                    $this->m_aliases[$p_props['const'] . '::' . 'isys_catg_ip_list#isys_obj'] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                    $this->m_aliases[$p_props['const'] . '::' . 'isys_cats_net_ip_addresses_list#isys_catg_ip_list'] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                    $this->m_aliases[$p_props['const'] . '::' . 'isys_obj#isys_cats_net_ip_addresses_list'] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;

                    return;
                }
            }

            if (isset($this->m_special_joins[$p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]])) {
                foreach ($this->m_special_joins[$p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] as $l_content) {
                    if (!isset($this->m_aliases[$p_props['const'] . '::' . $l_content[0] . '#' . $l_content[1]])) {
                        $this->m_aliases[$p_props['const'] . '::' . $l_content[0] . '#' . $l_content[1]] = $this->m_alias_cnt;
                        $this->m_alias_cnt++;
                    }
                    if (!isset($this->m_aliases[$p_props['const'] . '::' . $l_content[1] . '#' . $l_content[0]])) {
                        $this->m_aliases[$p_props['const'] . '::' . $l_content[1] . '#' . $l_content[0]] = $this->m_alias_cnt;
                        $this->m_alias_cnt++;
                    }
                    if (((strpos($l_content[0], 'catg') !== false || strpos($l_content[0], 'cats') !== false) && strpos($l_content[0], '_list') !== false) &&
                        !isset($this->m_aliases[$p_props['const'] . '::' . $l_content[0] . '#isys_obj'])) {
                        $this->m_aliases[$p_props['const'] . '::' . $l_content[0] . '#isys_obj'] = $this->m_alias_cnt;
                        $this->m_alias_cnt++;
                        $this->m_aliases[$p_props['const'] . '::' . 'isys_obj#' . $l_content[0]] = $this->m_alias_cnt;
                        $this->m_alias_cnt++;
                    }
                    if (((strpos($l_content[1], 'catg') !== false || strpos($l_content[1], 'cats') !== false) && strpos($l_content[1], '_list') !== false) &&
                        !isset($this->m_aliases[$p_props['const'] . '::' . $l_content[1] . '#isys_obj'])) {
                        $this->m_aliases[$p_props['const'] . '::' . $l_content[1] . '#isys_obj'] = $this->m_alias_cnt;
                        $this->m_alias_cnt++;
                        $this->m_aliases[$p_props['const'] . '::' . 'isys_obj#' . $l_content[1]] = $this->m_alias_cnt;
                        $this->m_alias_cnt++;
                    }
                }
            }
        } elseif (strpos($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD], 'isys_cable_connection__id') !== false) {
            if (!isset($this->m_aliases[$p_props['const'] . '::' . $l_table . '#isys_cable_connection'])) {
                $this->m_aliases[$p_props['const'] . '::' . $l_table . '#isys_cable_connection'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases[$p_props['const'] . '::' . 'isys_cable_connection#' . $l_table])) {
                $this->m_aliases[$p_props['const'] . '::' . 'isys_cable_connection#' . $l_table] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases[$p_props['const'] . '::' . 'isys_obj#isys_cable_connection'])) {
                $this->m_aliases[$p_props['const'] . '::' . 'isys_obj#isys_cable_connection'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases[$p_props['const'] . '::' . 'isys_cable_connection#isys_obj'])) {
                $this->m_aliases[$p_props['const'] . '::' . 'isys_cable_connection#isys_obj'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
        } elseif (strpos($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD], 'isys_catg_connector_list__id') !== false) {
            if ($l_table !== 'isys_catg_connector_list' && !isset($this->m_aliases[$p_props['const'] . '::' . $l_table . '#isys_catg_connector_list#isys_obj'])) {
                $this->m_aliases[$p_props['const'] . '::' . $l_table . '#isys_catg_connector_list#isys_catg_connector_list'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
                $this->m_aliases[$p_props['const'] . '::' . $l_table . '#isys_catg_connector_list#isys_obj'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases[$p_props['const'] . '::' . $l_table . '#isys_catg_connector_list'])) {
                $this->m_aliases[$p_props['const'] . '::' . $l_table . '#isys_catg_connector_list'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases[$p_props['const'] . '::' . 'isys_catg_connector_list#' . $l_table])) {
                $this->m_aliases[$p_props['const'] . '::' . 'isys_catg_connector_list#' . $l_table] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases[$p_props['const'] . '::' . 'isys_catg_connector_list#isys_catg_connector_list'])) {
                $this->m_aliases[$p_props['const'] . '::' . 'isys_catg_connector_list#isys_catg_connector_list'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases[$p_props['const'] . '::' . 'main_obj#' . $l_table])) {
                $this->m_aliases[$p_props['const'] . '::' . 'main_obj#' . $l_table] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases[$p_props['const'] . '::' . $l_table . '#main_obj'])) {
                $this->m_aliases[$p_props['const'] . '::' . $l_table . '#main_obj'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases[$p_props['const'] . '::' . 'isys_obj#isys_catg_connector_list'])) {
                $this->m_aliases[$p_props['const'] . '::' . 'isys_obj#isys_catg_connector_list'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases[$p_props['const'] . '::' . 'isys_catg_connector_list#isys_obj'])) {
                $this->m_aliases[$p_props['const'] . '::' . 'isys_catg_connector_list#isys_obj'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
        }

        if (!isset($this->m_aliases[$p_props['const'] . '::' . $l_table . '#isys_obj'])) {
            if (isset($this->joinedTables['root'][$l_table])) {
                $this->m_aliases[$p_props['const'] . '::' . $l_table . '#isys_obj'] = $this->joinedTables['root'][$l_table];
            } else {
                $this->m_aliases[$p_props['const'] . '::' . $l_table . '#isys_obj'] = $this->m_alias_cnt;
                $this->joinedTables['root'][$l_table] = $this->m_alias_cnt;
            }

            $this->m_alias_cnt++;
        }

        if (!isset($this->m_aliases[$p_props['const'] . '::' . 'isys_obj#' . $l_table])) {
            $this->m_aliases[$p_props['const'] . '::' . 'isys_obj#' . $l_table] = $this->m_alias_cnt;
            $this->m_alias_cnt++;
        }
    }

    /**
     * Method for creating aliase for the referenced fields
     *
     * @param       $p_lvl
     * @param array $p_props
     * @param       $p_assigned_property
     */
    protected function create_alias_lvls_select(array $p_props, $p_assigned_property)
    {
        $l_table = null;

        if ($p_props['data'] instanceof Property) {
            if ($p_props['data']->getData()->offsetExists(Property::C__PROPERTY__DATA__SOURCE_TABLE) &&
                !$p_props['data']->getData()->offsetExists(Property::C__PROPERTY__DATA__REFERENCES)) {
                $l_table = $p_props['data']->getData()->getSourceTable();
            }
        }

        if ($l_table === null && $p_props['type'] == C__PROPERTY_TYPE__DYNAMIC) {
            $l_table = $p_props['table'];
        }
        if ($l_table === null) {
            $l_table = current(explode('__', $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]));
        }

        $daoClass = $p_props['class'];
        $propField = $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
        $specialJoinKey = $daoClass . '::' . $propField;
        $specialJoinKeyWildcard = $daoClass . '::*';

        if ((isset($this->m_special_joins[$specialJoinKey]) || $this->m_special_joins[$specialJoinKeyWildcard] === true) &&
            isset($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN])) {
            $this->createSpecialAliase($specialJoinKey, $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN], $p_props['const'], $p_assigned_property);

            return;
        }

        if ($l_table == 'isys_catg_custom_fields_list') {
            if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] . '#isys_catg_custom_fields_list#isys_obj#' .
                $p_assigned_property])) {
                $this->m_aliases_lvls[$p_props['const'] . '::' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] . '#isys_catg_custom_fields_list#isys_obj#' .
                $p_assigned_property] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }

            if (isset($p_props['data'][C__PROPERTY__UI][C__PROPERTY__UI__TYPE]) &&
                ($p_props['data'][C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == 'f_popup' || $p_props['data'][C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == 'popup')) {
                if ($p_props['data'][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__OBJECT_BROWSER) {
                    if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] . '#isys_obj#isys_catg_custom_fields_list#' .
                        $p_assigned_property])) {
                        $this->m_aliases_lvls[$p_props['const'] . '::' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] . '#isys_obj#isys_catg_custom_fields_list#' .
                        $p_assigned_property] = $this->m_alias_cnt;
                        $this->m_alias_cnt++;
                    }
                } else {
                    if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] .
                        '#isys_dialog_plus_custom#isys_catg_custom_fields_list#' . $p_assigned_property])) {
                        $this->m_aliases_lvls[$p_props['const'] . '::' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] . '#isys_dialog_plus_custom#isys_catg_custom_fields_list#' .
                        $p_assigned_property] = $this->m_alias_cnt;
                        $this->m_alias_cnt++;
                    }
                }
            }

            return;
        }

        if ($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] !== null &&
            strpos(' ' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 'isys_') &&
            !isset($this->m_aliases_lvls[$p_props['const'] . '::' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#' . $l_table . '#' . $p_assigned_property])) {
            if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#' . $l_table . '#' . $p_assigned_property])) {
                $this->m_aliases_lvls[$p_props['const'] . '::' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#' . $l_table . '#' .
                $p_assigned_property] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . $l_table . '#' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#' . $p_assigned_property])) {
                $this->m_aliases_lvls[$p_props['const'] . '::' . $l_table . '#' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#' .
                $p_assigned_property] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }

            if ($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == 'isys_contact') {
                if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_contact_2_isys_obj#' . $l_table . '#' . $p_assigned_property])) {
                    $this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_contact_2_isys_obj#' . $l_table . '#' . $p_assigned_property] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                }
                if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . $l_table . '#isys_contact_2_isys_obj' . '#' . $p_assigned_property])) {
                    $this->m_aliases_lvls[$p_props['const'] . '::' . $l_table . '#isys_contact_2_isys_obj' . '#' . $p_assigned_property] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                }
                if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_contact_2_isys_obj#' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#' .
                    $p_assigned_property])) {
                    $this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_contact_2_isys_obj#' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#' .
                    $p_assigned_property] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                }
                if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_contact_2_isys_obj#isys_obj#' . $p_assigned_property])) {
                    $this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_contact_2_isys_obj#isys_obj#' . $p_assigned_property] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                }
            }

            if (((strpos($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 'catg') !== false ||
                    strpos($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 'cats') !== false) &&
                strpos($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], '_list') !== false)) {
                if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#isys_obj' . '#' . $p_assigned_property])) {
                    $this->m_aliases_lvls[$p_props['const'] . '::' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#isys_obj' . '#' .
                    $p_assigned_property] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                }
                if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_obj#' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#' . $p_assigned_property])) {
                    $this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_obj#' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#' .
                    $p_assigned_property] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                }
            }
        } elseif ($l_table == 'isys_logbook') {
            if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_catg_logb_list#isys_obj#' . $p_assigned_property])) {
                $this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_catg_logb_list#isys_obj#' . $p_assigned_property] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
                $this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_logbook#isys_catg_logb_list#' . $p_assigned_property] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }

            return;
        } elseif ($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] == $l_table . '__isys_obj__id') {
            if ($l_table == 'isys_cats_net_ip_addresses_list') {
                if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_catg_ip_list#isys_obj#' . $p_assigned_property])) {
                    $this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_catg_ip_list#isys_obj#' . $p_assigned_property] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                    $this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_cats_net_ip_addresses_list#isys_catg_ip_list#' . $p_assigned_property] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                    $this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_obj#isys_cats_net_ip_addresses_list#' . $p_assigned_property] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;

                    return;
                }
            }

            if (isset($this->m_special_joins[$p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]])) {
                foreach ($this->m_special_joins[$p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] as $l_content) {
                    if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . $l_content[0] . '#' . $l_content[1] . '#' . $p_assigned_property])) {
                        $this->m_aliases_lvls[$p_props['const'] . '::' . $l_content[0] . '#' . $l_content[1] . '#' . $p_assigned_property] = $this->m_alias_cnt;
                        $this->m_alias_cnt++;
                    }
                    if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . $l_content[1] . '#' . $l_content[0] . '#' . $p_assigned_property])) {
                        $this->m_aliases_lvls[$p_props['const'] . '::' . $l_content[1] . '#' . $l_content[0] . '#' . $p_assigned_property] = $this->m_alias_cnt;
                        $this->m_alias_cnt++;
                    }
                    if (((strpos($l_content[0], 'catg') !== false || strpos($l_content[0], 'cats') !== false) && strpos($l_content[0], '_list') !== false) &&
                        !isset($this->m_aliases_lvls[$p_props['const'] . '::' . $l_content[0] . '#isys_obj' . '#' . $p_assigned_property])) {
                        $this->m_aliases_lvls[$p_props['const'] . '::' . $l_content[0] . '#isys_obj' . '#' . $p_assigned_property] = $this->m_alias_cnt;
                        $this->m_alias_cnt++;
                        $this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_obj#' . $l_content[0] . '#' . $p_assigned_property] = $this->m_alias_cnt;
                        $this->m_alias_cnt++;
                    }
                    if (((strpos($l_content[1], 'catg') !== false || strpos($l_content[1], 'cats') !== false) && strpos($l_content[1], '_list') !== false) &&
                        !isset($this->m_aliases_lvls[$p_props['const'] . '::' . $l_content[1] . '#isys_obj' . '#' . $p_assigned_property])) {
                        $this->m_aliases_lvls[$p_props['const'] . '::' . $l_content[1] . '#isys_obj' . '#' . $p_assigned_property] = $this->m_alias_cnt;
                        $this->m_alias_cnt++;
                        $this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_obj#' . $l_content[1] . '#' . $p_assigned_property] = $this->m_alias_cnt;
                        $this->m_alias_cnt++;
                    }
                }
            }
        } elseif (strpos($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD], 'isys_cable_connection__id') !== false) {
            if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . $l_table . '#isys_cable_connection' . '#' . $p_assigned_property])) {
                $this->m_aliases_lvls[$p_props['const'] . '::' . $l_table . '#isys_cable_connection' . '#' . $p_assigned_property] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_cable_connection#' . $l_table . '#' . $p_assigned_property])) {
                $this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_cable_connection#' . $l_table . '#' . $p_assigned_property] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_obj#isys_cable_connection' . '#' . $p_assigned_property])) {
                $this->m_aliases_lvls['isys_obj#isys_cable_connection' . '#' . $p_assigned_property] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_cable_connection#isys_obj' . '#' . $p_assigned_property])) {
                $this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_cable_connection#isys_obj' . '#' . $p_assigned_property] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
        } elseif (strpos($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD], 'isys_catg_connector_list__id') !== false) {
            if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . $l_table . '#isys_catg_connector_list#isys_obj' . '#' . $p_assigned_property])) {
                $this->m_aliases_lvls[$p_props['const'] . '::' . $l_table . '#isys_catg_connector_list#isys_catg_connector_list' . '#' . $p_assigned_property] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
                $this->m_aliases_lvls[$p_props['const'] . '::' . $l_table . '#isys_catg_connector_list#isys_obj' . '#' . $p_assigned_property] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . $l_table . '#isys_catg_connector_list' . '#' . $p_assigned_property])) {
                $this->m_aliases_lvls[$p_props['const'] . '::' . $l_table . '#isys_catg_connector_list' . '#' . $p_assigned_property] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_catg_connector_list#' . $l_table . '#' . $p_assigned_property])) {
                $this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_catg_connector_list#' . $l_table . '#' . $p_assigned_property] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_catg_connector_list#isys_catg_connector_list' . '#' . $p_assigned_property])) {
                $this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_catg_connector_list#isys_catg_connector_list' . '#' . $p_assigned_property] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_obj#isys_catg_connector_list' . '#' . $p_assigned_property])) {
                $this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_obj#isys_catg_connector_list' . '#' . $p_assigned_property] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_catg_connector_list#isys_obj' . '#' . $p_assigned_property])) {
                $this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_catg_connector_list#isys_obj' . '#' . $p_assigned_property] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
        }

        if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . $l_table . '#isys_obj' . '#' . $p_assigned_property])) {
            if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . $l_table . '#isys_obj' . '#' . $p_assigned_property])) {
                if (isset($this->joinedTables[$p_assigned_property][$l_table])) {
                    $this->m_aliases_lvls[$p_props['const'] . '::' . $l_table . '#isys_obj' . '#' . $p_assigned_property] =
                        $this->joinedTables[$p_assigned_property][$l_table];
                } else {
                    $this->m_aliases_lvls[$p_props['const'] . '::' . $l_table . '#isys_obj' . '#' . $p_assigned_property] = $this->m_alias_cnt;
                    $this->joinedTables[$p_assigned_property][$l_table] = $this->m_alias_cnt;
                }
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_obj' . '#' . $l_table . '#' . $p_assigned_property])) {
                $this->m_aliases_lvls[$p_props['const'] . '::' . 'isys_obj' . '#' . $l_table . '#' . $p_assigned_property] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
        }
    }

    /**
     * Method for retrieving an previously created alias.
     *
     * @param   string $p_table
     * @param   string $p_ref
     *
     * @return  integer
     * @author    Leonard Fischer <lfischer@i-doit.org>
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function retrieve_alias_lvls($p_table, $p_ref, $p_assigned_property, $const)
    {
        if ($p_ref === null) {
            if (isset($this->m_aliases_lvls[$const . '::' . $p_table . '#isys_obj' . '#' . $p_assigned_property])) {
                return $this->m_aliases_lvls[$const . '::' . $p_table . '#isys_obj' . '#' . $p_assigned_property];
            } else {
                return $this->m_aliases_lvls[$const . '::' . $p_table . '#isys_obj'];
            }
        } else {
            if (isset($this->m_aliases_lvls[$const . '::' . $p_ref . '#' . $p_table . '#' . $p_assigned_property])) {
                return $this->m_aliases_lvls[$const . '::' . $p_ref . '#' . $p_table . '#' . $p_assigned_property];
            } else {
                return $this->m_aliases_lvls[$const . '::' . $p_ref . '#' . $p_table];
            }
        }
    }

    /**
     * This method prepares all the data, which are needed for selects, joins and conditions.
     *
     * @param   array $p_property_ids
     *
     * @return  isys_cmdb_dao_category_property
     */
    protected function prepare_necessary_tasks(array $p_property_ids)
    {
        // Also we save some information, so that the logic will not try to join the "isys_obj" or "isys_cmdb_status" tables.
        $this->m_aliases['C__CATG__GLOBAL::isys_cmdb_status#isys_obj'] = $this->m_alias_cnt;
        $this->m_aliases['C__CATG__GLOBAL::isys_obj#isys_obj'] = $this->m_alias_cnt++;

        if (count($p_property_ids) > 0) {
            // This query will be used to receive all the necessary entries from the isys_property_2_cat table.
            $l_res = $this->retrieve_properties(
                $p_property_ids,
                null,
                null,
                null,
                "",
                true,
                null,
                ' isys_property_2_cat__isysgui_catg_custom__id ASC '
            );

            $propertiesToBeReordered = [];

            // First we get all the needed data from the isys_property_2_cat table.
            while ($l_row = $l_res->get_row()) {
                $l_cat_dao = $this->get_dao_instance($l_row['class'], ($l_row['catg_custom'] ?: null));
                $l_properties = array_merge($l_cat_dao->get_properties(), $l_cat_dao->get_dynamic_properties());
                $l_row['data'] = $l_properties[$l_row['key']];

                $this->formatTable($l_row);

                if (in_array($l_row['table'], $this->tablesToBeReordered)) {
                    $propertiesToBeReordered[$l_row['id']] = $l_row;
                } else {
                    // We save every row, because we will need them quite often in the upcoming code.
                    $this->m_property_rows[$l_row['id']] = $l_row;
                }

                // Also we create table aliases for each possible join.
                $this->create_alias($l_row);
            }

            if (!empty($propertiesToBeReordered)) {
                $this->m_property_rows = $propertiesToBeReordered + $this->m_property_rows;
            }
        }

        $this->m_prepared_data_for_query_construction = true;

        return $this;
    }

    /**
     * Method for retrieving the properties of every category dao.
     *
     * @return  array
     * @author  Dennis StÃ¼cken <dstuecken@i-doit.de>
     */
    protected function properties()
    {
        return [];
    }

    /**
     * Method for retrieving an previously created alias.
     *
     * @param   string $p_table
     * @param   string $p_ref
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @todo    Change the alias namespaces to "a_table.a_column-b_table.b_column => counter"
     * @todo    Also change the parameters to "$p_from" and "$p_to".
     */
    protected function retrieve_alias($p_table, $p_ref, $const)
    {
        if ($p_ref === null) {
            return ($this->m_aliases[$const . '::' . $p_table . '#main_obj'] ?: $this->m_aliases[$const . '::' . $p_table . '#isys_obj']);
        } else {
            return $this->m_aliases[$const . '::' . $p_ref . '#' . $p_table];
        }
    }

    /**
     * Method for quick and easy ui-type decision.
     *
     * @param   array|ArrayAccess $p_property
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function retrieve_ui_type($p_property)
    {
        $l_prop_ui = $p_property[C__PROPERTY__UI];
        if ($l_prop_ui[C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DIALOG) {
            return C__PROPERTY__UI__TYPE__DIALOG;
        } elseif ($l_prop_ui[C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__POPUP && $l_prop_ui[C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'dialog_plus') {
            return C__PROPERTY__UI__TYPE__DIALOG;
        } elseif ($l_prop_ui[C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__POPUP && $l_prop_ui[C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'object_browser_ng') {
            // We assume "popup" = "object browser".
            return C__PROPERTY__UI__TYPE__POPUP;
        }

        return null;
    }

    private function handle_sub_conditions($p_assigned_key, $p_sub_conditions, $p_referenced_field)
    {
        // Join Object Table of $p_assigned_key
        $l_inner_condition = '';
        foreach ($p_sub_conditions[$p_assigned_key] as $l_condition) {
            $l_property = $l_condition['property'];
            $l_unit_id = null;
            $l_unit_property = null;
            $l_unit_field = null;
            $l_special_selection = false;
            $l_special_field = null;
            $l_ref_table = null;
            $l_ref_table_arr = null;
            $l_loc_condition_lft = null;
            $l_loc_condition_rgt = null;
            $l_assigned_key = null;
            $l_joins = [];
            [$l_category, $l_prop_key] = explode('-', $l_property);

            $l_unit = $l_condition['unit'];
            if (strpos($l_unit, '-') !== false) {
                [$l_unit_id, $l_unit_property] = explode('-', $l_unit);
            }

            if (strpos($p_assigned_key, '--') > 0) {
                $l_assigned_key_arr = explode('--', $p_assigned_key);
                if (!is_numeric($l_assigned_key_arr[0])) {
                    $l_assigned_key = '';
                    $assignedKeyCount = count($l_assigned_key_arr);
                    for ($i = 0;$i < $assignedKeyCount;$i++) {
                        [$l_category_assigned, $l_prop_key_assigned] = explode('-', $l_assigned_key_arr[$i]);

                        // This query will be used to receive all the necessary entries from the isys_property_2_cat table.
                        $l_property_condition = ' AND isys_property_2_cat__cat_const = ' . $this->convert_sql_text($l_category_assigned) .
                            ' AND isys_property_2_cat__prop_key = ' . $this->convert_sql_text($l_prop_key_assigned);
                        $l_id = $this->retrieve_properties(null, null, null, null, $l_property_condition, true)
                            ->get_row_value('id');
                        $l_assigned_key .= $l_id . '--';
                    }
                    $l_assigned_key = rtrim($l_assigned_key, '--');
                }
            } elseif (!is_numeric($p_assigned_key)) {
                [$l_category_assigned, $l_prop_key_assigned] = explode('-', $p_assigned_key);

                // This query will be used to receive all the necessary entries from the isys_property_2_cat table.
                $l_property_condition = ' AND isys_property_2_cat__cat_const = ' . $this->convert_sql_text($l_category_assigned) . ' AND isys_property_2_cat__prop_key = ' .
                    $this->convert_sql_text($l_prop_key_assigned);
                $l_assigned_key = $this->retrieve_properties(null, null, null, null, $l_property_condition, true)
                    ->get_row_value('id');
            } else {
                $l_assigned_key = $p_assigned_key;
            }

            if (!isset($this->m_property_rows_lvls[$l_property])) {
                // This query will be used to receive all the necessary entries from the isys_property_2_cat table.
                $l_property_condition = ' AND isys_property_2_cat__cat_const = ' . $this->convert_sql_text($l_category) . ' AND isys_property_2_cat__prop_key = ' .
                    $this->convert_sql_text($l_prop_key);
                $l_row = $this->retrieve_properties(null, null, null, null, $l_property_condition, true)
                    ->get_row();
                $l_cat_dao = $this->get_dao_instance($l_row['class'], ($l_row['catg_custom'] ?: null));
                $l_properties = array_merge($l_cat_dao->get_properties(), $l_cat_dao->get_dynamic_properties());
                $l_row['data'] = $l_properties[$l_row['key']];

                $this->m_property_rows_lvls[$l_property] = $l_row['id'];

                $this->formatTable($l_row);

                if (!isset($this->m_property_rows_lvls[$l_row['id']])) {
                    // We save every row, because we will need them quite often in the upcoming code.
                    $this->m_property_rows_lvls[$l_row['id']] = $l_row;
                }
                // Also we create table aliases for each possible join.
                $this->create_alias_lvls_select($l_row, $l_assigned_key);
                $l_select = $l_row['id'];
            } else {
                if (is_array($this->m_property_rows_lvls[$l_property])) {
                    $l_select = $this->m_property_rows_lvls[$l_property]['id'];
                    $l_row = $this->m_property_rows_lvls[$l_property];
                } else {
                    $l_select = $this->m_property_rows_lvls[$l_property];
                    $l_row = $this->m_property_rows_lvls[$this->m_property_rows_lvls[$l_property]];
                }
                $this->create_alias_lvls_select($l_row, $l_assigned_key);
            }

            $const = $this->m_property_rows_lvls[$l_select]['const'];

            if ($l_unit_id !== null) {
                $l_ignore_unit_field = false;
                if (isset($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__FORMAT])) {
                    if (isset($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][2])) {
                        $l_ignore_unit_field = ($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][2][0] !== null);
                    }
                }

                if (!isset($this->m_property_rows_lvls[$l_category . '-' . $l_unit_property])) {
                    $l_property_condition = ' AND isys_property_2_cat__cat_const = ' . $this->convert_sql_text($l_category) . ' AND isys_property_2_cat__prop_key = ' .
                        $this->convert_sql_text($l_unit_property);
                    $l_row = $this->retrieve_properties(null, null, null, null, $l_property_condition, true)
                        ->get_row();

                    $l_cat_dao = $this->get_dao_instance($l_row['class'], ($l_row['catg_custom'] ?: null));
                    $l_properties = array_merge($l_cat_dao->get_properties(), $l_cat_dao->get_dynamic_properties());
                    $l_row['data'] = $l_properties[$l_row['key']];

                    // We save every row, because we will need them quite often in the upcoming code.
                    $this->m_property_rows_lvls[$l_category . '-' . $l_unit_property] = $l_row;
                }

                if (!$l_ignore_unit_field) {
                    $l_unit_field = $this->m_property_rows_lvls[$l_category . '-' . $l_unit_property][C__PROPERTY__DATA][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                }
            }

            $l_dummy_selects = [];
            $l_lvls = [
                $l_assigned_key => [$l_select]
            ];

            if ($this->m_property_rows_lvls[$l_select][C__PROPERTY__DATA][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] !== C__PROPERTY__INFO__TYPE__MULTISELECT) {
                $l_joins = $this->create_property_query_join_lvls($l_lvls, $l_dummy_selects, $l_dummy_selects, true, $p_referenced_field);
            }

            if (is_countable($l_joins) && count($l_joins) > 0) {
                $l_join_key = key($l_joins);
                if (isset($this->m_sub_joins[$l_join_key])) {
                    $this->m_sub_joins[$l_join_key] = array_unique(array_merge($this->m_sub_joins[$l_join_key], $l_joins[$l_join_key]));
                } else {
                    $this->m_sub_joins += $l_joins;
                }
            }

            $l_table = current(explode('__', $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]));

            if ($l_table == 'isys_catg_custom_fields_list') {
                $l_field_alias = $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
                $l_field_type = $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE];
                $uiParams = $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS];
                $l_alias = 'j' . $this->retrieve_alias_lvls($l_field_alias . '#isys_catg_custom_fields_list', null, $l_assigned_key, $const);
                $uiParams = $this->m_property_rows[$l_select]['data'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS];

                switch ($l_field_type) {
                    case C__PROPERTY__INFO__TYPE__OBJECT_BROWSER:
                    case C__PROPERTY__INFO__TYPE__N2M:
                        $l_alias_sec = 'j' . $this->retrieve_alias_lvls('isys_catg_custom_fields_list', $l_field_alias . '#isys_obj', $l_assigned_key, $const);
                        $l_condition_field = $l_alias_sec . '.isys_obj__id';
                        break;
                    case C__PROPERTY__INFO__TYPE__DIALOG:
                    case C__PROPERTY__INFO__TYPE__DIALOG_PLUS:
                    case C__PROPERTY__INFO__TYPE__MULTISELECT:

                        if (isset($uiParams['extra']) && $uiParams['extra'] === 'yes-no') {
                            $l_condition_field = $l_alias . '.' . $l_table . '__field_content';
                            break;
                        }

                        $l_alias_sec = 'j' . $this->retrieve_alias_lvls('isys_catg_custom_fields_list', $l_field_alias . '#isys_dialog_plus_custom', $l_assigned_key, $const);
                        $l_condition_field = $l_alias_sec . '.isys_dialog_plus_custom__id';

                        if ($l_field_type === C__PROPERTY__INFO__TYPE__MULTISELECT && $uiParams['popup'] !== 'checkboxes') {
                            $l_condition_field = $p_referenced_field;
                        }
                        break;
                    default:
                        $l_condition_field = $l_alias . '.' . $l_table . '__field_content';
                        break;
                }
            } else {
                // We have to check for an existing "predefined" alias.
                if (isset($this->m_aliases_lvls[$l_table])) {
                    $l_alias = 'j' . $this->m_aliases_lvls[$l_table];
                } else {
                    $l_alias = 'j' . $this->retrieve_alias_lvls($l_table, null, $l_assigned_key, $const);
                }

                if ($l_table == 'isys_logbook') {
                    $l_ref_table = $l_table;
                    $l_table = 'isys_catg_logb_list';
                    $l_alias = 'j' . $this->retrieve_alias_lvls($l_table, $l_ref_table, $l_assigned_key, $const);
                }

                // First we check for some special selected fields.
                if (isset($this->m_special_selects[$this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]])) {
                    // Check if its a primary field
                    if (is_array($this->m_special_selects[$this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]])) {
                        $l_special_selection = true;
                        $l_table = $this->m_special_selects[$this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]][0];

                        if ($l_table == 'isys_obj' && !$this->m_query_as_report) {
                            $l_special_field = 'isys_obj__id';
                            $l_table = $this->m_special_selects[$this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]][1];
                        } else {
                            $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] = $this->m_special_selects[$this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]][1];
                        }
                    }
                }

                if (($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] !== null &&
                        substr($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 0, 5) == 'isys_') ||
                    $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_location') {
                    if (($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] === null ||
                            $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] === 'isys_obj') && $l_table == 'isys_obj') {
                        $l_ref_table_arr = explode('__', $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]);
                        $l_table = $l_ref_table_arr[0];
                        $l_ref_table = 'isys_obj';
                        $l_special_field = 'isys_obj__id';
                    } else {
                        $l_ref_table = $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0];
                    }

                    $l_alias = $this->retrieve_alias_lvls($l_table, $l_ref_table, $l_assigned_key, $const);

                    if ($l_condition['comparison'] == 'under_location') {
                        $l_alias = $this->retrieve_alias_lvls('isys_obj', $l_table, $l_assigned_key, $const);
                        $l_condition['location_lft'] = 'j' . $l_alias . '.isys_catg_location_list__lft';
                        $l_condition['location_rgt'] = 'j' . $l_alias . '.isys_catg_location_list__rgt';
                    }

                    // We have to join 'job' on references to isys_connection.
                    if ($l_ref_table == 'isys_connection') {
                        if ($l_special_selection) {
                            // it is not an jobx alias
                            $l_alias = $this->retrieve_alias_lvls($l_table, 'isys_obj', $l_assigned_key, $const);
                            $conditionAlias = "j" . $l_alias;
                        } else {
                            $conditionAlias = "job" . $l_alias;
                        }

                        $l_condition_field = $conditionAlias . ".isys_obj__id";
                    } elseif ($l_ref_table == 'isys_contact') {
                        $l_alias = $this->retrieve_alias_lvls('isys_obj', 'isys_contact_2_isys_obj', $l_assigned_key, $const);
                        $l_condition_field = "job" . $l_alias . ".isys_obj__id";
                    } elseif ($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] == 'browser_location') {
                        $l_alias = $this->retrieve_alias_lvls($l_table, 'isys_obj', $l_assigned_key, $const);
                        $l_condition_field = "j" . $l_alias . ".isys_obj__id";
                    } elseif ($this->m_property_rows_lvls[$l_select][C__PROPERTY__DATA][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] === C__PROPERTY__INFO__TYPE__MULTISELECT ||
                        $this->m_property_rows_lvls[$l_select][C__PROPERTY__DATA][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] === 'isys_cats_database_schema_list__isys_cats_db_instance_list__id') {
                        // VQH: Find a better solution for isys_cats_database_schema_list__isys_cats_db_instance_list__id
                        $l_alias = substr($p_referenced_field, 0, strpos($p_referenced_field, '.'));
                        $l_condition_field = $p_referenced_field;
                    } else {
                        if ($l_special_selection) {
                            $l_condition_field = "j" . $l_alias . "." .
                                (($l_special_field !== null) ? $l_special_field : $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]);
                        } elseif ($l_ref_table == 'isys_obj') {
                            $l_condition_field = "j" . $l_alias . ".isys_obj__id";
                        } elseif ($l_condition['comparison'] === 'subcnd') {
                            $l_condition_field = "j" . $l_alias . "." . $l_ref_table . "__isys_obj__id";
                        } else {
                            $l_condition_field = "j" . $l_alias . "." . $l_ref_table . "__id";
                        }

                        if (isset($this->m_text_fields[$this->m_property_rows_lvls[$l_select]['const']])) {
                            if (isset($this->m_text_fields[$this->m_property_rows_lvls[$l_select]['const']][$this->m_property_rows_lvls[$l_select]['key']])) {
                                $l_condition_field = substr($l_condition_field, 0, (strpos($l_condition_field, '.') + 1)) .
                                    $this->m_text_fields[$this->m_property_rows_lvls[$l_select]['const']][$this->m_property_rows_lvls[$l_select]['key']];
                            }
                        }
                    }
                } elseif (strpos($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD], 'isys_cable_connection__id') !== false) {
                    $l_alias = $this->retrieve_alias_lvls('isys_cable_connection', 'isys_obj', $l_assigned_key, $const);
                    $l_condition_field = "j" . $l_alias . ".isys_obj__id";
                } elseif (strpos($this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD], 'isys_catg_connector_list__id') !== false) {
                    if ($l_table != 'isys_catg_connector_list' || $l_condition['comparison'] !== 'subcnd') {
                        $l_alias = $this->retrieve_alias_lvls('isys_catg_connector_list', $l_table, $l_assigned_key, $const);
                        $field = "isys_catg_connector_list__id";
                    } else {
                        $l_alias = $this->retrieve_alias_lvls('isys_catg_connector_list', 'isys_obj', $l_assigned_key, $const);
                        $field = "isys_obj__id";
                    }
                    $l_condition_field = "j" . $l_alias . "." . $field;
                } else {
                    if ($l_ref_table == '' && !in_array($l_alias, $this->m_already_used_aliase)) {
                        $l_alias = 'j' . $this->retrieve_alias_lvls($l_table, 'isys_obj', $l_assigned_key, $const);
                        $l_condition_field = $l_alias . ".isys_obj__id";
                    } else {
                        $l_condition_field = $l_alias . "." . $this->m_property_rows_lvls[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                    }
                }
            }

            if ($l_condition['comparison'] == 'subcnd') {
                $l_inner_condition .= $this->handle_sub_conditions($p_assigned_key . '--' . $l_property, $p_sub_conditions, $l_condition_field) . ' ' .
                    $l_condition['operator'];
            } else {
                $l_condition['alias'] = $l_alias;
                $l_condition['unitField'] = $l_unit_field;
                $l_condition['unitId'] = $l_unit_id;

                $l_inner_condition .= $this->build_inner_condition(
                    $l_inner_condition,
                    $this->m_property_rows_lvls[$l_select]['data'],
                    $l_condition_field,
                    $l_condition,
                    $this->m_property_rows_lvls[$l_select]['type'],
                    $p_referenced_field
                );
            }
        }

        return ' (' . trim($l_inner_condition) . ') ';
    }

    /**
     * Helper method which builds the inner condition
     *
     * @param $p_inner_condition
     * @param $p_data
     * @param $p_condition_field
     * @param $p_condition
     * @param $propertyType
     * @param $referenceField
     *
     * @return string
     * @throws Exception
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function build_inner_condition($p_inner_condition, $p_data, $p_condition_field, $p_condition, $propertyType, $referenceField)
    {
        $dynamicConditionField = $p_condition_field;

        if (isset($p_data[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES]) && ($p_data[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__DIALOG ||
                $p_data[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__DIALOG_PLUS)) {
            if ((strpos($p_data[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 'catg') !== false ||
                    strpos($p_data[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 'cats') !== false) &&
                strpos($p_data[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], '_list') !== false && strpos($p_condition_field, '__id_') === false) {
                $p_condition_field = str_replace('__id', '__title', $p_condition_field);
            }
        } elseif ($p_data[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__TEXT && strpos($p_condition_field, '__id') > 0) {
            $p_condition_field = str_replace('__id', '__title', $p_condition_field);
        }

        if (isset($p_data[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK]) && $p_condition['unitId'] !== null) {
            if ($p_data[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'convert') {
                $l_method = $p_data[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][2][0];
                // convert value
                $p_condition['value'] = isys_convert::$l_method($p_condition['value'], $p_condition['unitId']);
            }
        }

        /**
         * @var $conditionQueryObject idoit\Module\Report\SqlQuery\Structure\SelectSubSelect
         */

        if ($p_condition['comparison'] == 'LIKE %...%') {
            $p_condition['comparison'] = 'LIKE';
            $p_condition['value'] = '%' . $p_condition['value'] . '%';
        }

        if ($p_condition['comparison'] == 'NOT LIKE %...%') {
            $p_condition['comparison'] = 'NOT LIKE';
            $p_condition['value'] = '%' . $p_condition['value'] . '%';
        }

        $p_condition['value'] = str_replace([
            '[',
            ']',
            '"'
        ], '', $p_condition['value']);
        $conditions = [];

        switch ($propertyType) {
            case Property::C__PROPERTY_TYPE__DYNAMIC:
                if (!($p_data instanceof DynamicProperty) && is_array($p_data)) {
                    $p_data = DynamicProperty::createInstanceFromArray($p_data);
                }
                break;
            case Property::C__PROPERTY_TYPE__STATIC:
            default:
                if (!($p_data instanceof Property) && is_array($p_data)) {
                    $p_data = Property::createInstanceFromArray($p_data);
                }
                break;
        }

        foreach ($this->conditionProviders as $conditionProvider) {
            $conditionProvider->setProperty($p_data);
            $conditionProvider->setConditionData($p_condition);
            $conditionProvider->setConditionField($p_condition_field);
            $conditionProvider->setConditionComparison($p_condition['comparison']);
            $conditionProvider->setConditionValue($p_condition['value'] ?: null);
            $conditionProvider->setConditionUnitField($p_condition['unitField'] ?: null);
            $conditionProvider->setConditionUnitId($p_condition['unitId'] ?: null);
            $conditionProvider->setConditionUnitFieldAlias($p_condition['alias'] ?: null);
        }

        $formattedCondition = null;
        if ($formattedCondition === null && ($formattedCondition = $this->conditionProviders['dynamic']->format())) {
            if ($p_data instanceof Property) {
                if ($p_data instanceof DynamicYesNoProperty) {
                    $yesNoArr = get_smarty_arr_YES_NO();
                    $p_condition['value'] = $yesNoArr[$p_condition['value']];
                }

                $callback = $p_data->getFormat()
                    ->getCallback();
                [$dynamicConditionFieldAlias] = explode('.', $dynamicConditionField);
                $class = (is_object($callback[0])) ? get_class($callback[0]) : $callback[0];
                $method = $callback[1];

                $dao = (is_object($callback[0])) ? $callback[0] : $callback[0]::instance(isys_application::instance()->container->get('database'));

                if (!$dao instanceof isys_cmdb_dao_category) {
                    return 'TRUE';
                }

                $alias = sprintf('%s::%s::DYNAMIC_PROPERTY::%s', $class, $method, count($this->reportQuery->getDynamicFilter()));
                $conditionField = $dynamicConditionField;

                if (!empty($p_condition['value']) && $p_condition['value'] !== '%%') {
                    $processorCondition = (new FilterProcessorConditionValue())
                        ->setValue(trim($p_condition['value'], '%'));
                } else {
                    $processorCondition = (new FilterProcessorConditionEmptyValue());
                }

                if (strpos($dynamicConditionField, '_isys_obj__id') !== false) {
                    $processor = (new FilterProcessorObjectId());
                    $conditionField = $dynamicConditionFieldAlias . '.' . ($dao->get_source_table() ?: $dao->get_table()) . '__id';
                    $conditionField = $referenceField;
                    $dynamicConditionField = $referenceField;
                } elseif (strpos($dynamicConditionField, '_list__id') !== false && substr_count($dynamicConditionField, '__') === 1) {
                    $processor = (new FilterProcessorId());
                } else {
                    $processor = (new FilterProcessorDynamic())
                        ->setField($dynamicConditionField);
                }

                if ($p_condition['comparison'] === 'LIKE' || $p_condition['comparison'] === '=') {
                    $conditionForFilter = new LikeCondition();
                } else {
                    $conditionForFilter = new NotLikeCondition();
                }

                $conditionForFilter
                    ->setConditionField($conditionField)
                    ->setProperty($p_data);

                $select = sprintf("%s as '%s'", $dynamicConditionField, $alias);
                $this->reportQuery->addSelection($select);
                $this->reportQuery->addDynamicFilter((new Filter())->setKey($alias)
                    ->setField($dynamicConditionField)
                    ->setCondition($conditionForFilter)
                    ->setProcessor($processor->setDao($dao)
                        ->setProcessorConditionValue($processorCondition)
                        ->setMethod($method)
                        ->setValue(str_replace('%', '', $p_condition['value']))));

                $formattedCondition .= '/*' . $alias . '*/';
            }

            $p_inner_condition .= ' ' . $formattedCondition;
        }

        // Check in property provider
        if ($formattedCondition === null && ($formattedCondition = $this->conditionProviders['property']->format())) {
            $p_inner_condition .= ' ' . $formattedCondition;
        }

        // Check in comparison provider
        if ($formattedCondition === null && ($formattedCondition = $this->conditionProviders['comparison']->format())) {
            $p_inner_condition .= ' ' . $formattedCondition;
        }

        // Check in property type provider
        if ($formattedCondition === null && ($formattedCondition = $this->conditionProviders['propertyType']->format())) {
            $p_inner_condition .= ' ' . $formattedCondition;
        }

        // Default condition
        if ($formattedCondition === null) {
            $p_inner_condition .= ' ' . $this->conditionProviders['default']->format();
        }

        return $p_inner_condition . ' ' . (isset($p_condition['operator']) ? $p_condition['operator'] : '');
    }

    /**
     * Helper which adds additional conditions to the joined table if its set in the property
     *
     * @param $p_prop_data
     * @param $p_table
     * @param $p_alias
     *
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function add_join_condition($p_prop_data, $p_table, $p_alias)
    {
        if (isset($p_prop_data[C__PROPERTY__DATA__SELECT]) && $p_alias != '') {
            /**
             * @var $l_select          idoit\Module\Report\SqlQuery\Structure\SelectSubSelect
             * @var $l_selectCondition idoit\Module\Report\SqlQuery\Structure\SelectCondition
             */
            $l_select = $p_prop_data[C__PROPERTY__DATA__SELECT];
            $l_selectCondition = $l_select->getSelectCondition();
            $l_conditions = null;
            if ($l_selectCondition) {
                $l_conditions = $l_selectCondition->getCondition();
            }

            if (is_countable($l_conditions) && count($l_conditions)) {
                $l_newCondition = [];

                foreach ($l_conditions as $l_condition) {
                    // Only if table exists in condition and if no alias is being used
                    if (strpos($l_condition, $p_table) !== false && (substr_count($l_condition, '.') < 2 || strpos($l_condition, '.') === false)) {
                        if (strpos($l_condition, '.') !== false) {
                            $l_newCondition[] = preg_replace('/\S+(?=\.)/', $p_alias, $l_condition);
                        } else {
                            // Special case if table is isys_obj
                            if ($p_table === 'isys_obj') {
                                $l_newCondition[] = str_replace($p_table . '__', $p_alias . '.' . $p_table . '__', $l_condition);
                            } else {
                                $l_newCondition[] = str_replace($p_table, $p_alias . '.' . $p_table, $l_condition);
                            }
                        }
                    }
                }
                if (count($l_newCondition)) {
                    return ' AND ' . ltrim(ltrim(implode(' ', $l_newCondition), ' AND'), ' OR') . ' ';
                }
            }
        }

        return '';
    }

    /**
     * Returns a string which contains a condition with the specified status filter for multivalue categories
     *
     * @param $alias
     * @param $table
     *
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function addMultivalueStatusFilter($alias, $table, $multiValue = 0)
    {
        $return = '';
        if (!empty($this->multivalueStatusFilter) && $multiValue > 0) {
            $return = ' AND ' . $alias . '.' . $table . '__status IN (' . implode(', ', array_map([$this, 'convert_sql_int'], $this->multivalueStatusFilter)) . ') ';
        }

        return $return;
    }

    /**
     * Setter Status filter for multivalue categories
     *
     * @param array $status
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setMultivalueStatusFilter(array $status)
    {
        $this->multivalueStatusFilter = $status;

        return $this;
    }

    /**
     * Create aliase for special joins
     *
     * @param $specialJoinKey
     * @param $propertyDataJoin
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function createSpecialAliase($specialJoinKey, $propertyDataJoin, $categoryConst, $assignedProperty = '')
    {
        $updateSpecialJoins = false;

        if ($this->m_special_joins[$specialJoinKey] === true || !isset($this->m_special_joins[$specialJoinKey])) {
            $this->m_special_joins[$specialJoinKey] = [];
            $updateSpecialJoins = true;
        }

        $lastTable = 'isys_obj';

        if ($assignedProperty !== '') {
            $aliasData = $this->m_aliases_lvls;
            $keyAddition = '#' . $assignedProperty;
        } else {
            $aliasData = $this->m_aliases;
            $keyAddition = '';
        }

        /**
         * @var $dataJoin idoit\Module\Report\SqlQuery\Structure\SelectJoin
         */
        foreach ($propertyDataJoin as $dataJoin) {
            $table = $dataJoin->getTable();
            $refTable = ($dataJoin->getRefTable()) ?: $lastTable;
            $onLeft = ($lastTable === 'isys_obj') ? $dataJoin->getOnLeft() : $dataJoin->getOnRight();
            $onRight = ($lastTable === 'isys_obj') ? $dataJoin->getOnRight() : $dataJoin->getOnLeft();
            $lastTable = $table;

            if ($updateSpecialJoins) {
                $this->m_special_joins[$specialJoinKey][] = [
                    $table,
                    $refTable,
                    $onLeft,
                    $onRight
                ];
            }

            // First Key
            $key = $categoryConst . '::' . $table . '#' . $refTable . $keyAddition;
            if (!isset($aliasData[$key])) {
                $aliasData[$key] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }

            // Second Key
            $key = $categoryConst . '::' . $refTable . '#' . $table . $keyAddition;
            if (!isset($aliasData[$key])) {
                $aliasData[$key] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }

            // Third Key
            $key = $categoryConst . '::' . $table . '#isys_obj' . $keyAddition;
            if (((strpos($table, 'catg') !== false || strpos($table, 'cats') !== false) && strpos($table, '_list') !== false) && !isset($aliasData[$key])) {
                $aliasData[$key] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
                $aliasData[$categoryConst . '::isys_obj#' . $table . $keyAddition] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }

            // Fourth Key
            $key = $categoryConst . '::' . $refTable . '#isys_obj' . $keyAddition;
            if (((strpos($refTable, 'catg') !== false || strpos($refTable, 'cats') !== false) && strpos($refTable, '_list') !== false) && !isset($aliasData[$key])) {
                $aliasData[$key] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
                $aliasData[$categoryConst . '::isys_obj#' . $refTable . $keyAddition] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
        }

        if ($assignedProperty !== '') {
            $this->m_aliases_lvls = $aliasData;
        } else {
            $this->m_aliases = $aliasData;
        }
    }

    /**
     * @param $row
     * @return void
     */
    private function formatTable(&$row)
    {
        $tableArr = explode('_', $row['table']);

        if (array_pop($tableArr) !== 'list') {
            $row['table'] = $row['table'] . '_list';
        }
    }

    /**
     * @param $const
     * @param $alias
     * @param $table
     *
     * @return string
     */
    private function addCategorySpecificCondition($const, $alias, $table)
    {
        $joinCondition = '';

        if (($const == 'C__CATG__OPERATING_SYSTEM' || $const == 'C__CATG__APPLICATION')) {
            switch ($const) {
                case 'C__CATG__OPERATING_SYSTEM':
                    $joinCondition = " AND " . $alias . "." . $table . "__isys_catg_application_priority__id = " .
                        defined_or_default('C__CATG__APPLICATION_PRIORITY__PRIMARY') . " " . $this->addMultivalueStatusFilter($alias, $table, true);
                    break;
                case 'C__CATG__APPLICATION':
                    $joinCondition = " AND (" . $alias . "." . $table . "__isys_catg_application_priority__id IS NULL " . "OR " . $alias . "." .
                        $table . "__isys_catg_application_priority__id = " . defined_or_default('C__CATG__APPLICATION_PRIORITY__SECONDARY') . ")";
                    break;
                default:
                    break;
            }
        }

        return $joinCondition;
    }

    /**
     * @param array  $propData
     * @param array  $joins
     * @param string $joinField
     * @param string $joinType
     * @param array  $aliasArray
     */
    private function handleBackwardProperty($propData, &$joins, $joinField, $joinType = 'LEFT', $aliasArray = [])
    {
        if (!isset($propData['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT])) {
            return;
        }

        /**
         * @var \idoit\Module\Report\SqlQuery\Structure\SelectSubSelect $selectSubselect
         * @var \idoit\Module\Report\SqlQuery\Structure\SelectJoin[] $selectJoins
         */
        $selectJoins = $propData['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN];
        $selectSubselect = $propData['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT];
        $conditionField = $propData['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
        $lastField = '';

        foreach ($selectJoins as $index => $join) {
            $alias = $aliasArray[$index];

            if ($index === 0) {
                $joins[$alias] = $joinType . ' JOIN ' . $join->getTable() . ' as ' . $alias . ' ON ' . $alias . '.' . $join->getOnLeft() . ' = ' . $joinField;
                $lastField = $alias . '.' . $conditionField;
                continue;
            }

            $joins[$alias] = $joinType . ' JOIN ' . $join->getTable() . ' as ' . $alias . ' ON ' . $alias . '.' . $join->getOnRight() . ' = ' . $lastField;
            $lastField = $alias . '.' . $join->getOnLeft();
        }
    }
}
