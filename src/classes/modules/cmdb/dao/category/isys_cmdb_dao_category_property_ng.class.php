<?php

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
class isys_cmdb_dao_category_property_ng extends isys_cmdb_dao_category
{
    /**
     * Constants for grouping list values as comma or as list (<li></li>)
     */
    const C__GROUPING__COMMA = 0;
    const C__GROUPING__LIST  = 1;

    /**
     * @var
     */
    private $m_grouping = 0;

    /**
     * @return mixed
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getGrouping()
    {
        return $this->m_grouping;
    }

    /**
     * @param int $p_grouping
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setGrouping($p_grouping)
    {
        $this->m_grouping = $p_grouping;

        return $this;
    }

    /**
     * Array which contains references
     *
     * @var array
     */
    private $m_reference = ['root' => 'obj_main.isys_obj__id'];

    /**
     * Helper array for reordering the properties for the joins
     *
     * @var array
     */
    private $m_property_order = [];

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

    /**
     * List of aliases for the report-builder.
     *
     * @var  array
     */
    protected $m_aliases = [
        'isys_obj' => 'obj_main'
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
     * @var bool
     */
    protected $m_query_as_report = false;

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
    private $m_parent_custom_field = '';

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
     * @var array
     */
    private $m_property_key_selections = [];

    /**
     * @var array
     */
    private $m_selects = [];

    /**
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getSelects()
    {
        return $this->m_selects;
    }

    /**
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_property_key_selections()
    {
        return $this->m_property_key_selections;
    }

    /**
     * @param array $p_value
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_property_key_selections($p_key, $p_value)
    {
        $this->m_property_key_selections[$p_key] = $p_value;
    }

    /**
     * Method for setting the "query_as_report" variable from extern.
     *
     * @param   boolean $p_query_as_report
     *
     * @return  isys_cmdb_dao_category_property_ng
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
     * @return  isys_cmdb_dao_category_property_ng
     */
    public function reset()
    {
        $this->m_already_used_aliase = [];
        $this->m_aliases_lvls = [];
        $this->m_sub_joins = [];
        $this->m_referenced_fields = [];
        $this->m_remove_from_selection = [];
        $this->m_parent_custom_field = '';
        $this->m_aliases = [
            'isys_obj' => 'obj_main'
        ];
        $this->m_alias_cnt = 1;
        $this->m_prepared_data_for_query_construction = false;

        return $this;
    }

    /**
     * Method for recieving the property-ID's by a given array.
     *
     * @param   array $p_properties
     *
     * @return  array
     * @author  Dennis Stücken <dstuecken@synetics.de>
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
    public function create_property_query($p_properties, $p_objects, $p_with_object_data = false, $p_leave_field_identifiers = false)
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
                $l_selects = $this->create_property_query_select($l_keys, ['obj_main.*'], $p_leave_field_identifiers);
            } else {
                $l_selects = $this->create_property_query_select($l_keys, [], $p_leave_field_identifiers);
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
     * @param   mixed       $p_properties
     * @param   mixed       $p_object_types Can be either a integer or a array of integers.
     * @param   mixed       $p_object_ids   Can be either a integer or a array of integers.
     * @param   array       $p_queries
     * @param   boolean     $p_leave_field_identifiers
     * @param   bool|string $groupBy
     *
     * @return  string
     * @throws \idoit\Exception\JsonException
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function create_property_query_for_lists(
        $p_properties,
        $p_object_types = null,
        $p_object_ids = null,
        $p_queries = [],
        $p_leave_field_identifiers = true,
        $groupBy = false
    ) {
        if (is_string($p_properties)) {
            $p_properties = isys_format_json::decode($p_properties);
        }

        if (!is_array($p_properties)) {
            $p_properties = [];
        }

        $this->prepare_necessary_tasks($p_properties);

        $l_selects = $this->create_property_query_select($p_properties, $p_queries, $p_leave_field_identifiers, true);

        $l_joins = $this->create_property_query_join($p_properties, true);

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

        // Add `GROUP BY` condition if needed
        $l_sql .= $this->buildGroupBy($groupBy);

        return $l_sql;
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
    public function create_property_query_select(array $p_property_ids, $p_selects = [], $p_leave_field_identifiers = false, $p_for_list = false)
    {
        // We need to know if all the necessary stuff has been done!
        if (!$this->m_prepared_data_for_query_construction) {
            $this->prepare_necessary_tasks($p_property_ids);
        }

        // Now we prepare the SELECT's.
        $l_selects = array_merge($p_selects, [
            "obj_main.isys_obj__id AS '__id__'"
        ]);

        // And add the selected ones from the report-builder.
        foreach ($p_property_ids as $l_select) {
            if (!isset($this->m_property_rows[$l_select])) {
                continue;
            }

            $l_cat = 'cats';
            $l_table = current(explode('__', $this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]));

            if ($this->m_property_rows[$l_select]['catg'] != null) {
                $l_cat = 'catg';
            } elseif ($this->m_property_rows[$l_select]['catg_custom'] != null) {
                $l_cat = 'catg_custom';
            }

            if (isset($this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT])) {
                if (!$p_leave_field_identifiers) {
                    $l_alias = $this->m_property_rows[$l_select]['title'] . "###" . $this->m_property_rows[$l_select][$l_cat];
                } else {
                    $l_alias = $this->m_property_rows[$l_select]['class'] . '__' . $this->m_property_rows[$l_select]['key'];
                    if ($this->m_property_rows[$l_select]['key'] == 'description' && $l_cat == 'catg_custom') {
                        $l_alias .= '_' . $this->m_property_rows[$l_select]['catg_custom'];
                    }
                }

                $l_alias = $this->convert_sql_text($l_alias);

                $l_joinedTableAlias = null;
                $l_alias_key = null;

                /**
                 * @var $l_selectSubSelect \idoit\Module\Report\SqlQuery\Structure\SelectSubSelect
                 * @var $selectJoins \idoit\Module\Report\SqlQuery\Structure\SelectJoin[]
                 */
                $l_selectSubSelect = clone $this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT];
                $selectJoins = $this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN];

                if ($l_table == 'isys_obj' || $l_selectSubSelect->getSelectTable() == 'isys_obj') {
                    $l_joinedTableAlias = 'obj_main';
                } else {
                    if ($l_selectSubSelect->getSelectTable() !== '' && $l_selectSubSelect->getSelectFieldObjectID() !== '') {
                        $l_table = $l_selectSubSelect->getSelectTable();
                    } elseif ($l_selectSubSelect->getSelectTable() !== '' && $l_selectSubSelect->getSelectFieldObjectID() === '') {
                        $l_alias_key = $l_selectSubSelect->getSelectTable() . '#' . $this->m_property_rows[$l_select]['table'];
                        $l_joinedTableAlias = (isset($this->m_aliases[$l_alias_key])) ? 'j' . $this->m_aliases[$l_alias_key] : null;
                    }

                    if ($l_joinedTableAlias === null && ($l_alias_num = $this->retrieve_alias($l_table))) {
                        $l_joinedTableAlias = 'j' . $l_alias_num;
                    }
                }

                $l_selectCondition = clone $l_selectSubSelect->getSelectCondition();
                $l_condition = '';

                if ($l_alias_key !== null) {
                    // No SubSelect just a field
                    $l_selectSubSelect->setSelectQuery(str_replace(
                        $l_selectSubSelect->getSelectTable(),
                        $l_joinedTableAlias . '.' . $l_selectSubSelect->getSelectTable(),
                        $l_selectSubSelect->getSelectQuery()
                    ));
                } else {
                    if ($l_selectSubSelect->getSelectTable()) {
                        if (/*($this->m_property_rows[$l_select]['provides'] & C__PROPERTY__PROVIDES__VIRTUAL || $l_joinedTableAlias === null || $l_joinedTableAlias === 'obj_main') &&*/
                        $l_selectSubSelect->getSelectFieldObjectID()) {
                            $l_condition = $l_selectSubSelect->getSelectFieldObjectID() . ' = obj_main.isys_obj__id';
                        } elseif ($l_conditionField = $l_selectSubSelect->getSelectReferenceKey()) {
                            $l_condition = ($l_joinedTableAlias ? $l_joinedTableAlias . '.' : '') .
                                $this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] . ' = ' . $l_conditionField;
                        } elseif ($l_conditionField = $l_selectSubSelect->getSelectFieldObjectID() && $l_joinedTableAlias !== 'obj_main' && $l_joinedTableAlias !== null) {
                            $l_condition = $l_joinedTableAlias . '.' . $l_selectSubSelect->getSelectTable() . '__isys_obj__id = ' . $l_conditionField;
                        } // @todo modify this if Config parameter for Grouping of the selection has been added. If grouping is active then skip this if so that the reference is the object-ID
                        elseif (($l_conditionField = $l_selectSubSelect->getSelectPrimaryKey()) && (bool)$this->m_property_rows[$l_select]['multi'] === true) {
                            $l_condition = ($l_joinedTableAlias ? $l_joinedTableAlias . '.' : '') . $l_selectSubSelect->getSelectTable() . '__id = ' . $l_conditionField;
                        } elseif ($l_selectSubSelect->getSelectTable()) {
                            $l_condition = ($l_joinedTableAlias ? $l_joinedTableAlias . '.' : '') . $l_selectSubSelect->getSelectTable() . '__id = ' .
                                $l_selectSubSelect->getSelectTable() . '__id';
                        }

                        if ($l_condition) {
                            $l_selectCondition->addCondition(' AND ' . $l_condition);
                        }

                        // Add status check only if table contains 1 '_list '
                        if ($this->fieldsExistsInTable($l_selectSubSelect->getSelectTable(), [$l_selectSubSelect->getSelectTable() . '__status'])) {
                            $l_alias_table = (strpos($l_selectSubSelect->getSelectFieldObjectID(), '.') ? substr(
                                $l_selectSubSelect->getSelectFieldObjectID(),
                                0,
                                strpos($l_selectSubSelect->getSelectFieldObjectID(), '.')
                            ) . '.' : (strpos(
                                $l_selectSubSelect->getSelectPrimaryKey(),
                                '.'
                            ) ? substr($l_selectSubSelect->getSelectPrimaryKey(), 0, strpos($l_selectSubSelect->getSelectPrimaryKey(), '.')) . '.' : ''));

                            $l_selectCondition->addCondition(' AND ' . $l_alias_table . $l_selectSubSelect->getSelectTable() . '__status = ' .
                                $this->convert_sql_int(C__RECORD_STATUS__NORMAL));
                        }
                        // @see ID-7203 Add status check on isys_obj table subquery.
                        if ($l_selectSubSelect->getSelectTable() === 'isys_connection') {
                            // We have to get the category table
                            $tableFromField = current(explode('__', $this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]));
                            if (is_array($selectJoins)) {
                                foreach ($selectJoins as $selectJoin) {
                                    if ($selectJoin->getTable() !== $l_selectSubSelect->getSelectTable() &&
                                        $this->fieldsExistsInTable($selectJoin->getTable(), [$selectJoin->getTable() . '__status'])
                                    ) {
                                        $conditionField = ($selectJoin->getTableAlias() ? $selectJoin->getTableAlias() . '.' . $selectJoin->getTable(): $selectJoin->getTable()) . '__status';
                                        $l_selectCondition->addCondition(' AND ' . $conditionField . ' = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' ');
                                    }
                                }
                            } else {
                                // @see ID-7453 Add the correct alias to prevent SQL errors (due to 'ambiguous isys_obj__status').
                                $l_selectCondition->addCondition(' AND obj_main.isys_obj__status=' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' ');
                            }
                        }
                    } elseif ($l_joinedTableAlias != 'obj_main') {
                        $l_selectSubSelect->setSelectQuery(str_replace(
                            $this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD],
                            ($l_joinedTableAlias ? $l_joinedTableAlias . '.' : '') . $this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD],
                            $l_selectSubSelect->getSelectQuery()
                        ));
                    } elseif ($this->m_property_rows[$l_select]['provides'] & C__PROPERTY__PROVIDES__VIRTUAL && $l_selectSubSelect->getSelectFieldObjectID()) {
                        $l_condition = ' AND ' . $l_selectSubSelect->getSelectFieldObjectID() . ' = obj_main.isys_obj__id';
                        $l_selectCondition->addCondition(' AND ' . $l_condition);
                    } elseif ($l_joinedTableAlias == 'obj_main') {
                        $l_selectSubSelect->setSelectQuery(str_replace('isys_obj__', 'obj_main.isys_obj__', $l_selectSubSelect->getSelectQuery()));
                    }
                }

                $l_selectSubSelect->setSelectAlias($l_alias)
                    ->setSelectCondition($l_selectCondition);

                // Handling Selection with GROUP BY and wrap the selection with the GROUP_CONCAT
                if ($l_selectSubSelect->getSelectGroupBy()
                        ->getGroupBy() && $p_for_list) {
                    if (!($l_selection = $l_selectSubSelect->getSelectGroupBy()
                        ->getGroupConcatSelection())) {
                        $l_selection = trim(substr(
                            $l_selectSubSelect->getSelectQuery(),
                            stripos($l_selectSubSelect->getSelectQuery(), 'SELECT') + 6,
                            ((substr_count($l_selectSubSelect->getSelectQuery(), 'FROM') > 1) ? strrpos(
                                $l_selectSubSelect->getSelectQuery(),
                                'FROM'
                            ) : strpos($l_selectSubSelect->getSelectQuery(), 'FROM')) - 6
                        ));

                        // In Case the selection is a CASE WHEN Condition
                        if (stripos($l_selection, ' as ') !== false && strpos($l_selection, ')') !== false && strpos($l_selection, '(') === 0) {
                            $l_selection = substr($l_selection, 0, strripos($l_selection, ')') + 1);
                        }
                    }


                    if (strpos($l_selection, 'GROUP_CONCAT') === false) {
                        switch ($this->m_grouping) {
                            case self::C__GROUPING__LIST:
                                $separator = '</li><li>';
                                $l_new_selection = 'REPLACE(CONCAT(\'<ul><li>\', %s, \'</li></ul>\'), \'<ul><li></li></ul>\', \'\')';
                                $groupConcat = 'GROUP_CONCAT(' . $l_selection . ' SEPARATOR \'</li><li>\')';

                                break;
                            case self::C__GROUPING__COMMA:
                            default:
                                $separator = ',';
                                $l_new_selection = '%s';
                                $groupConcat = 'GROUP_CONCAT(' . $l_selection . ' SEPARATOR \', \')';
                                break;
                        }

                        if ($l_selectSubSelect->getSelectLimit() !== null) {
                            $groupConcat = 'SUBSTRING_INDEX(' . $groupConcat . ', \'' . $separator . '\', ' . $l_selectSubSelect->getSelectLimit() . ')';
                        }

                        $l_new_selection = sprintf($l_new_selection, $groupConcat);
                        $l_selectSubSelect->setSelectQuery(str_replace($l_selection, $l_new_selection, $l_selectSubSelect->getSelectQuery()));
                    }
                }

                $l_selects[$l_select] = $l_selectSubSelect;

                $this->set_property_key_selections(
                    $this->m_property_rows[$l_select]['class'] . '__' . $this->m_property_rows[$l_select]['key'],
                    $l_selectSubSelect->getSelectQuery()
                );
                continue;
            }

            $l_alias = 'j' . $this->retrieve_alias($l_table);

            // Then we check for special table-names inside the select.
            if (isset($this->m_aliases[$l_table])) {
                $l_alias = $this->m_aliases[$l_table];
            } elseif (isset($this->m_aliases[$l_table . '#main_obj'])) {
                if ($l_table == 'isys_catg_connector_list') {
                    $l_alias = 'j' . $this->retrieve_alias($l_table, $l_table);
                } else {
                    $l_alias = 'j' . $this->m_aliases[$l_table . '#main_obj'];
                }
            } elseif ($l_table == 'isys_logbook') {
                $l_alias = 'j' . $this->retrieve_alias('isys_catg_logb_list', 'isys_logbook');
            }

            // @todo Add Config parameter for Grouping the selection
            if ($this->m_property_rows[$l_select]['multi']) {
                switch ($this->m_grouping) {
                    case self::C__GROUPING__LIST:
                        $l_field = 'REPLACE(CONCAT(\'<ul><li>\', GROUP_CONCAT(' . $l_alias . "." .
                            $this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] .
                            ' SEPARATOR \'</li><li>\'), \'</li></ul>\'), \'<ul><li></li></ul>\', \'\')';
                        break;
                    case self::C__GROUPING__COMMA:
                    default:
                        $l_field = 'GROUP_CONCAT(' . $l_alias . "." . $this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] .
                            ' SEPARATOR \', \')';
                        break;
                }
            } else {
                $l_field = $l_alias . "." . $this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
            }

            $l_selects[$l_select] = $l_field . ' ' .
                (!$p_leave_field_identifiers ? " AS '" . $this->m_property_rows[$l_select]['title'] . "###" . $this->m_property_rows[$l_select][$l_cat] .
                    "'" : $this->convert_sql_text($this->m_property_rows[$l_select]['class'] . '__' . $this->m_property_rows[$l_select]['key']));

            $this->set_property_key_selections(
                $this->m_property_rows[$l_select]['class'] . '__' . $this->m_property_rows[$l_select]['key'],
                $l_alias . "." . $this->m_property_rows[$l_select]['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]
            );
        }

        // Clearing out all duplicate selects.
        return array_unique($l_selects);
    }

    /**
     * Build `GROUP BY` condition
     *
     * @param bool|string $groupBy
     *
     * @return string
     */
    public function buildGroupBy($groupBy)
    {
        $condition = '';

        if ($groupBy) {
            if (!is_string($groupBy)) {
                $groupBy = 'obj_main.isys_obj__id';
            }

            $condition = ' GROUP BY ' . $groupBy . ' ';
        }

        return $condition;
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
    private function create_property_query_join(array $p_property_ids, $p_for_list = false)
    {
        // We need to know if all the necessary stuff has been done!
        if (!$this->m_prepared_data_for_query_construction) {
            $this->prepare_necessary_tasks($p_property_ids);
        }

        // We need this array to save "already joined" tables for saving a bit of performance.
        $l_already_joined_tables = $l_joins = [];

        if ($this->m_empty_values) {
            $l_join_type = "LEFT";
        } else {
            // Only root tables will be joined as INNER JOIN. Subquery joins will always be a LEFT JOIN
            $l_join_type = "INNER";
        }

        // Now we create the single JOIN's.
        foreach ($p_property_ids as $l_prop_id) {
            if (!isset($this->m_property_rows[$l_prop_id])) {
                continue;
            }

            $l_reference = $l_ref_field = $l_alias_sec = $l_alias_third = $l_alias_fourth = null;

            $l_prop_data = $this->m_property_rows[$l_prop_id];

            // We won't handle dynamic properties here.
            if (($l_prop_data['type'] == C__PROPERTY_TYPE__DYNAMIC && !isset($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD])) ||
                ($l_prop_data['provides'] & C__PROPERTY__PROVIDES__VIRTUAL)) {
                continue;
            }
            if (isset($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT])) {
                /**
                 * @var $l_selectSubSelect idoit\Module\Report\SqlQuery\Structure\SelectSubSelect
                 */
                $l_selectSubSelect = clone $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT];
                if ($l_selectSubSelect->getSelectTable() && $l_selectSubSelect->getSelectFieldObjectID() != '') {
                    // We don´t need to join any table
                    continue;
                }
            }
            {
                if ($l_prop_data['type'] == C__PROPERTY_TYPE__DYNAMIC) {
                    $l_table = $l_prop_data['table'];
                } else {
                    $l_table = current(explode('__', $l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]));
                }

                // We have to check for an existing "predefined" alias.
                if (isset($this->m_aliases[$l_table])) {
                    $l_alias = $this->m_aliases[$l_table];
                } else {
                    $l_alias = 'j' . $this->retrieve_alias($l_table);
                }

                if (isset($this->m_reference['root-' . $l_table]) && !$l_prop_data['data'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VIRTUAL]) {
                    $l_reference = $this->m_reference['root-' . $l_table];
                }

                if ($l_table == 'isys_logbook') {
                    if (!in_array('isys_catg_logb_list#isys_obj', $l_already_joined_tables)) {
                        $l_alias = 'j' . $this->retrieve_alias('isys_obj', 'isys_catg_logb_list');

                        if (in_array($l_alias, $this->m_already_used_aliase)) {
                            continue;
                        }

                        $l_already_joined_tables[] = 'isys_catg_logb_list#isys_obj';
                        $l_joins[$l_alias] = $l_join_type . " JOIN isys_catg_logb_list AS " . $l_alias . " ON " . $l_alias .
                            ".isys_catg_logb_list__isys_obj__id = obj_main.isys_obj__id";
                    }

                    if (!in_array('isys_logbook#isys_catg_logb_list', $l_already_joined_tables)) {
                        $l_alias = 'j' . $this->retrieve_alias('isys_catg_logb_list', 'isys_logbook');
                        $l_alias_ref = 'j' . $this->retrieve_alias('isys_obj', 'isys_catg_logb_list');

                        if (in_array($l_alias, $this->m_already_used_aliase)) {
                            continue;
                        }

                        $l_already_joined_tables[] = 'isys_logbook#isys_catg_logb_list';
                        $l_joins[$l_alias] = $l_join_type . " JOIN isys_logbook AS " . $l_alias . " ON " . $l_alias . ".isys_logbook__id = " . $l_alias_ref .
                            ".isys_catg_logb_list__isys_logbook__id";
                    }
                    $this->m_already_used_aliase[] = $l_alias;
                    continue;
                }

                if (isset($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN])) {
                    $l_last_table = null;
                    $l_last_alias = null;

                    /**
                     * @var $l_selectJoin idoit\Module\Report\SqlQuery\Structure\SelectJoin
                     *
                     * @todo needs to be modified see property pos from isys_cmdb_dao_category_g_location
                     */
                    foreach ($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN] as $l_index => $l_selectJoin) {
                        $l_selectJoin->setJoinType($l_join_type);
                        if ($l_last_table === null) {
                            $l_table = $l_selectJoin->getTable();
                            $l_last_alias = 'j' . $this->retrieve_alias($l_table);
                            $l_already_joined_tables[] = $l_table . '#isys_obj';

                            $l_selectJoin->setTableAlias($l_last_alias)
                                ->setOnLeftAlias($l_last_alias)
                                ->setOnRightALias('obj_main');
                        } else {
                            $l_table = $l_selectJoin->getTable();

                            if (in_array($l_table . '#' . $l_last_table, $l_already_joined_tables)) {
                                $l_last_alias = 'j' . $this->retrieve_alias($l_last_table, $l_table);
                                $l_last_table = $l_table;

                                continue;
                            }

                            if ($l_selectJoin->getRefTable()) {
                                $l_last_table = $l_selectJoin->getRefTable();
                                $l_last_alias = 'j' . $this->retrieve_alias($l_last_table);
                            }
                            $l_alias = 'j' . $this->retrieve_alias($l_last_table, $l_table);
                            $l_already_joined_tables[] = $l_table . '#' . $l_last_table;
                            $l_selectJoin->setTableAlias($l_alias)
                                ->setOnLeftAlias($l_last_alias)
                                ->setOnRightAlias($l_alias);
                            $l_last_alias = $l_alias;
                        }

                        if (!in_array($l_alias, $this->m_already_used_aliase)) {
                            $l_joins[$l_last_alias] = $l_selectJoin;
                            $this->m_already_used_aliase[] = $l_alias;
                        }

                        $l_last_table = $l_table;

                        if ($l_table == 'isys_connection' || strpos($l_table, '_2_') || !(isset($l_prop_data['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT]))) {
                            continue;
                        }
                    }

                    continue;
                }

                if (!in_array($l_table . '#isys_obj', $l_already_joined_tables) && !in_array($l_alias, $this->m_already_used_aliase) && $l_alias != 'obj_main') {
                    if (isset($this->m_aliases[$l_table . '#main_obj'])) {
                        $l_alias = 'j' . $this->retrieve_alias('main_obj', $l_table);
                    }

                    if (!in_array($l_alias, $this->m_already_used_aliase)) {
                        $l_already_joined_tables[] = $l_table . '#isys_obj';

                        if (in_array($l_table, $l_already_joined_tables) && $l_reference !== null && $l_prop_data['multi'] > 0) {
                            $l_join = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias . "." . $l_table . "__id = " . $l_reference .
                                (($this->fieldsExistsInTable($l_table, [$l_table . '__status'])) ?
                                    " AND " . $l_alias . "." . $l_table . "__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) : '');
                        } else {
                            $l_join = $l_join_type . " JOIN " . $l_table . " AS " . $l_alias . " ON " . $l_alias . "." . $l_table . "__isys_obj__id = obj_main.isys_obj__id " .
                                (($this->fieldsExistsInTable($l_table, [$l_table . '__status'])) ?
                                    "AND " . $l_alias . '.' . $l_table . '__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) : '');
                            $this->m_reference['root-' . $l_table] = $l_alias . "." . $l_table . '__id';
                        }
                        $l_joins[$l_alias] = $l_join;
                        $this->m_already_used_aliase[] = $l_alias;
                    }
                }
            }
        }
        // Clearing out all duplicate joins.
        $l_joins = array_unique($l_joins);

        return $l_joins;
    }

    /**
     * Method for retrieving properties.
     *
     * @todo    Add custom categories
     *
     * @param   mixed   $p_property_id May be a array or an integer.
     * @param   mixed   $p_catg_id     May be a array or an integer.
     * @param   mixed   $p_cats_id     May be a array or an integer.
     * @param   integer $p_provides
     * @param   string  $p_condition
     * @param   boolean $p_dynamic_properties
     * @param   mixed   $p_catg_custom_id
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function retrieve_properties(
        $p_property_id = null,
        $p_catg_id = null,
        $p_cats_id = null,
        $p_provides = null,
        $p_condition = "",
        $p_dynamic_properties = false,
        $p_catg_custom_id = null
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

        if (!$p_dynamic_properties) {
            $l_condition .= "AND isys_property_2_cat__prop_type = " . C__PROPERTY_TYPE__STATIC . " ";
        }

        $l_category_join = " LEFT JOIN isysgui_catg ON isysgui_catg__id = isys_property_2_cat__isysgui_catg__id " .
            " LEFT JOIN isysgui_cats ON isysgui_cats__id = isys_property_2_cat__isysgui_cats__id " .
            " LEFT JOIN isysgui_catg_custom ON isysgui_catg_custom__id = isys_property_2_cat__isysgui_catg_custom__id ";

        // We rename the fields for easier usage.
        $l_sql = "SELECT isys_property_2_cat__id AS 'id', " . "isys_property_2_cat__isysgui_catg__id AS 'catg', " . "isys_property_2_cat__isysgui_cats__id AS 'cats', " .
            "isys_property_2_cat__isysgui_catg_custom__id AS 'catg_custom', " . "isys_property_2_cat__cat_const AS 'const', " . "isys_property_2_cat__prop_type AS 'type', " .
            "isys_property_2_cat__prop_title AS 'title', " . "isys_property_2_cat__prop_key AS 'key', " .
            "(CASE WHEN isys_property_2_cat__isysgui_catg__id IS NOT NULL THEN isysgui_catg__list_multi_value " .
            "WHEN isys_property_2_cat__isysgui_cats__id IS NOT NULL THEN isysgui_cats__list_multi_value " .
            "WHEN isys_property_2_cat__isysgui_catg_custom__id IS NOT NULL THEN isysgui_catg_custom__list_multi_value END) AS 'multi', " .
            "(CASE WHEN isys_property_2_cat__isysgui_catg__id IS NOT NULL THEN isysgui_catg__class_name " .
            "WHEN isys_property_2_cat__isysgui_cats__id IS NOT NULL THEN isysgui_cats__class_name " .
            "WHEN isys_property_2_cat__isysgui_catg_custom__id IS NOT NULL THEN isysgui_catg_custom__class_name END) AS 'class', " .
            "(CASE WHEN isys_property_2_cat__isysgui_catg__id IS NOT NULL THEN isysgui_catg__source_table " .
            "WHEN isys_property_2_cat__isysgui_cats__id IS NOT NULL THEN isysgui_cats__source_table " .
            "WHEN isys_property_2_cat__isysgui_catg_custom__id IS NOT NULL THEN 'isys_catg_custom_fields_list' END) AS 'table', " .
            "isys_property_2_cat__prop_provides AS provides " . "FROM isys_property_2_cat " . $l_category_join . $l_condition;

        return $this->retrieve($l_sql . $p_condition . ";");
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
     * @return mixed
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
            ->set_database(isys_application::instance()->container->get('database'))
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
        if ($p_props['type'] == C__PROPERTY_TYPE__DYNAMIC) {
            $l_table = $p_props['table'];
        } else {
            $l_table = current(explode('__', $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]));
        }

        if (isset($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN])) {
            if (count($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN])) {
                $l_last_table = '';
                /**
                 * @var $l_selectJoin \idoit\Module\Report\SqlQuery\Structure\SelectJoin
                 */
                foreach ($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN] as $l_selectJoin) {
                    $l_join_table = $l_selectJoin->getTable();
                    if ($l_selectJoin->getRefTable()) {
                        $l_last_table = $l_selectJoin->getRefTable();
                    }

                    if (!isset($this->m_aliases[$l_join_table . '#isys_obj'])) {
                        $this->m_aliases[$l_join_table . '#isys_obj'] = $this->m_alias_cnt;
                        $this->m_alias_cnt++;
                    }

                    if ($l_last_table != '' && !isset($this->m_aliases[$l_join_table . '#' . $l_last_table])) {
                        $this->m_aliases[$l_join_table . '#' . $l_last_table] = $this->m_alias_cnt;
                        //$this->m_aliases[$l_join_table . '#' . $l_last_table . '#' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] = $this->m_alias_cnt;
                        $this->m_alias_cnt++;
                    }

                    $l_last_table = $l_join_table;
                }
                $this->m_property_order[] = $p_props['id'];

                return;
            }
        }

        if ($l_table == 'isys_catg_custom_fields_list') {
            $this->m_aliases[$p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] . '#isys_catg_custom_fields_list#isys_obj'] = $this->m_alias_cnt;
            $this->m_alias_cnt++;

            if (isset($p_props['data'][C__PROPERTY__UI][C__PROPERTY__UI__TYPE]) &&
                ($p_props['data'][C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == 'f_popup' || $p_props['data'][C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == 'popup')) {
                if ($p_props['data'][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__OBJECT_BROWSER) {
                    if (!isset($this->m_aliases[$p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] . '#isys_obj#isys_catg_custom_fields_list'])) {
                        $this->m_aliases[$p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] . '#isys_obj#isys_catg_custom_fields_list'] = $this->m_alias_cnt;
                        $this->m_alias_cnt++;
                    }
                } else {
                    if (!isset($this->m_aliases[$p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] .
                        '#isys_dialog_plus_custom#isys_catg_custom_fields_list'])) {
                        $this->m_aliases[$p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] .
                        '#isys_dialog_plus_custom#isys_catg_custom_fields_list'] = $this->m_alias_cnt;
                        $this->m_alias_cnt++;
                    }
                }
            }
            $this->m_property_order[] = $p_props['id'];

            return;
        } elseif (isset($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT])) {
            if ($p_props['data'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VIRTUAL] === true) {
                return;
            }

            /**
             * @var $l_selectSubSelect idoit\Module\Report\SqlQuery\Structure\SelectSubSelect
             */
            $l_selectSubSelect = clone $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT];
            $l_join_table = $l_selectSubSelect->getSelectTable();

            if ($l_join_table) {
                if (!isset($this->m_aliases[$l_join_table . '#isys_obj'])) {
                    $this->m_aliases[$l_join_table . '#isys_obj'] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                }
            }
        } elseif ($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] !== null &&
            strpos(' ' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 'isys_') &&
            !isset($this->m_aliases[$p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#' . $l_table])) {
            if ($l_table == 'isys_logbook') {
                if (!isset($this->m_aliases['isys_catg_logb_list#isys_obj'])) {
                    $this->m_aliases['isys_catg_logb_list#isys_obj'] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                    $this->m_aliases['isys_logbook#isys_catg_logb_list'] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                }
            }

            if (!isset($this->m_aliases[$p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#' . $l_table])) {
                $this->m_aliases[$p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#' . $l_table] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }

            if ($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == 'isys_contact') {
                if (!isset($this->m_aliases['isys_contact_2_isys_obj#' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]])) {
                    $this->m_aliases['isys_contact_2_isys_obj#' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                }
                if (!isset($this->m_aliases['isys_contact_2_isys_obj#isys_obj'])) {
                    $this->m_aliases['isys_contact_2_isys_obj#isys_obj'] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                }
            }

            if (((strpos($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 'catg') !== false ||
                    strpos($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], 'cats') !== false) &&
                strpos($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], '_list') !== false)) {
                if (!isset($this->m_aliases[$p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#isys_obj'])) {
                    $this->m_aliases[$p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '#isys_obj'] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                }
                if (!isset($this->m_aliases['isys_obj#' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]])) {
                    $this->m_aliases['isys_obj#' . $p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                }
            }
            $this->m_property_order[] = $p_props['id'];
            if ($l_table == 'isys_logbook') {
                return;
            }
        } elseif ($l_table == 'isys_catg_logb_list' || $l_table == 'isys_logbook') {
            if (!isset($this->m_aliases['isys_catg_logb_list#isys_obj'])) {
                $this->m_aliases['isys_catg_logb_list#isys_obj'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
                $this->m_aliases['isys_logbook#isys_catg_logb_list'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
                $this->m_property_order[] = $p_props['id'];
            }

            if ($l_table == 'isys_logbook') {
                return;
            }
        } elseif ($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] == $l_table . '__isys_obj__id') {
            if ($l_table == 'isys_cats_net_ip_addresses_list') {
                if (!isset($this->m_aliases['isys_catg_ip_list#isys_obj'])) {
                    $this->m_aliases['isys_catg_ip_list#isys_obj'] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                    $this->m_aliases['isys_cats_net_ip_addresses_list#isys_catg_ip_list'] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                    $this->m_aliases['isys_obj#isys_cats_net_ip_addresses_list'] = $this->m_alias_cnt;
                    $this->m_alias_cnt++;
                    $this->m_property_order[] = $p_props['id'];

                    return;
                }
            }
        } elseif (strpos($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD], 'isys_cable_connection__id') !== false) {
            if (!isset($this->m_aliases[$l_table . '#isys_cable_connection'])) {
                $this->m_aliases[$l_table . '#isys_cable_connection'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases['isys_cable_connection#' . $l_table])) {
                $this->m_aliases['isys_cable_connection#' . $l_table] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases['isys_obj#isys_cable_connection'])) {
                $this->m_aliases['isys_obj#isys_cable_connection'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases['isys_cable_connection#isys_obj'])) {
                $this->m_aliases['isys_cable_connection#isys_obj'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
        } elseif (strpos($p_props['data'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD], 'isys_catg_connector_list__id') !== false) {
            if ($l_table !== 'isys_catg_connector_list' && !isset($this->m_aliases[$l_table . '#isys_catg_connector_list#isys_obj'])) {
                $this->m_aliases[$l_table . '#isys_catg_connector_list#isys_catg_connector_list'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
                $this->m_aliases[$l_table . '#isys_catg_connector_list#isys_obj'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases[$l_table . '#isys_catg_connector_list'])) {
                $this->m_aliases[$l_table . '#isys_catg_connector_list'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases['isys_catg_connector_list#' . $l_table])) {
                $this->m_aliases['isys_catg_connector_list#' . $l_table] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases['isys_catg_connector_list#isys_catg_connector_list'])) {
                $this->m_aliases['isys_catg_connector_list#isys_catg_connector_list'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases['main_obj#' . $l_table])) {
                $this->m_aliases['main_obj#' . $l_table] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases[$l_table . '#main_obj'])) {
                $this->m_aliases[$l_table . '#main_obj'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases['isys_obj#isys_catg_connector_list'])) {
                $this->m_aliases['isys_obj#isys_catg_connector_list'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
            if (!isset($this->m_aliases['isys_catg_connector_list#isys_obj'])) {
                $this->m_aliases['isys_catg_connector_list#isys_obj'] = $this->m_alias_cnt;
                $this->m_alias_cnt++;
            }
        }

        if (!isset($this->m_aliases[$l_table . '#isys_obj'])) {
            $this->m_aliases[$l_table . '#isys_obj'] = $this->m_alias_cnt;
            $this->m_alias_cnt++;
        }

        if (!isset($this->m_aliases['isys_obj#' . $l_table])) {
            $this->m_aliases['isys_obj#' . $l_table] = $this->m_alias_cnt;
            $this->m_alias_cnt++;
        }

        if (!in_array($p_props['id'], $this->m_property_order)) {
            $this->m_property_order[] = $p_props['id'];
        }
    }

    /**
     * This method prepares all the data, which are needed for selects, joins and conditions.
     *
     * @param   array $p_property_ids
     *
     * @return  isys_cmdb_dao_category_property_ng
     */
    protected function prepare_necessary_tasks(array $p_property_ids)
    {
        if (count($p_property_ids) > 0) {
            // This query will be used to receive all the necessary entries from the isys_property_2_cat table.
            $l_res = $this->retrieve_properties($p_property_ids, null, null, null, "", true);

            // First we get all the needed data from the isys_property_2_cat table.
            while ($l_row = $l_res->get_row()) {
                $l_row['prop_count'] = 0;
                $l_cat_dao = $this->get_dao_instance($l_row['class'], ($l_row['catg_custom'] ?: null));
                $l_cat_properties = $l_cat_dao->get_properties();
                $l_properties = array_merge($l_cat_properties, $l_cat_dao->get_dynamic_properties());
                $l_row['data'] = $l_properties[$l_row['key']];

                $l_table_arr = explode('_', $l_row['table']);

                if (array_pop($l_table_arr) !== 'list') {
                    $l_row['table'] = $l_row['table'] . '_list';
                }

                if ($l_row['table'] == 'isys_catg_virtual_list') {
                    $l_row['table'] = ($l_cat_dao->get_table()) ?: $l_cat_dao->get_source_table();
                }

                foreach ($l_cat_properties as $l_prop) {
                    if ($l_prop[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__REPORT]) {
                        $l_row['prop_count']++;
                    }
                }

                // We save every row, because we will need them quite often in the upcoming code.
                $this->m_property_rows[$l_row['id']] = $l_row;

                // Also we create table aliases for each possible join.
                $this->create_alias($l_row);
            }
            // Ordered properties
            /*if(isset($this->m_property_order['joins']))
            {
                $l_joins = $this->m_property_order['joins'];
                unset($this->m_property_order['joins']);
                $this->m_property_order = array_merge($l_joins, $this->m_property_order);
            }*/
        }

        // Also we save some information, so that the logic will not try to join the "isys_obj" or "isys_cmdb_status" tables.
        $this->m_aliases['isys_obj#isys_obj'] = $this->m_alias_cnt++;

        $this->m_prepared_data_for_query_construction = true;

        return $this;
    }

    /**
     * Method for retrieving the properties of every category dao.
     *
     * @return  array
     * @author  Dennis Stücken <dstuecken@i-doit.de>
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
    protected function retrieve_alias($p_table, $p_ref = null)
    {
        if ($p_ref === null) {
            return $this->m_aliases[$p_table . '#isys_obj'];
        } else {
            return $this->m_aliases[$p_ref . '#' . $p_table];
        }
    }

    /**
     * Method for quick and easy ui-type decision.
     *
     * @param   array $p_property
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function retrieve_ui_type(array $p_property)
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

    /**
     * Retrieves property id by condition
     *
     * @param string $p_condition
     *
     * @return mixed
     * @throws isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_property_id_by_condition($p_condition = 'TRUE')
    {
        $l_sql = 'SELECT isys_property_2_cat__id FROM isys_property_2_cat
          LEFT JOIN isysgui_catg ON isysgui_catg__id = isys_property_2_cat__isysgui_catg__id
          LEFT JOIN isysgui_cats ON isysgui_cats__id = isys_property_2_cat__isysgui_cats__id
          LEFT JOIN isysgui_catg_custom ON isysgui_catg_custom__id = isys_property_2_cat__isysgui_catg_custom__id
          WHERE ' . $p_condition . ' LIMIT 1;';

        return $this->retrieve($l_sql)
            ->get_row_value('isys_property_2_cat__id');
    }
}
