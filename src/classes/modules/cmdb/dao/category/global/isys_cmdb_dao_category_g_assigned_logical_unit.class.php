<?php

use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: assigned logical unit.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_assigned_logical_unit extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'assigned_logical_unit';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__ASSIGNED_LOGICAL_UNITS';

    /**
     * Category's constant.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CATG__ASSIGNED_LOGICAL_UNIT';

    /**
     * Category's identifier.
     *
     * @var    integer
     * @fixme  No standard behavior!
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATG__ASSIGNED_LOGICAL_UNIT;

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_catg_logical_unit_list__isys_obj__id';

    /**
     * @var bool
     */
    protected $m_has_relation = true;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Flag
     *
     * @var bool
     */
    protected $m_object_browser_category = true;

    /**
     * Property of the object browser
     *
     * @var string
     */
    protected $m_object_browser_property = 'assigned_object';

    /**
     * Field for the object id. This variable is needed for multiedit (for example global category guest systems or it service).
     *
     * @var  string
     */
    protected $m_object_id_field = 'isys_catg_logical_unit_list__isys_obj__id__parent';

    /**
     * New variable to determine if the current category is a reverse category of another one.
     *
     * @var  string
     */
    protected $m_reverse_category_of = 'isys_cmdb_dao_category_g_logical_unit';

    /**
     * category table
     *
     * @var string
     */
    protected $m_table = 'isys_catg_logical_unit_list';

    /**
     * Method for getting the object-browsers preselection.
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_selected_objects($p_obj_id)
    {
        $l_dao = new isys_cmdb_dao_category_g_logical_unit($this->m_db);

        return $l_dao->get_data_by_parent($p_obj_id);
    }

    /**
     * @param array $p_post
     *
     * @throws Exception
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $l_category_data_id = null;
        $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->m_db);
        $l_dao = new isys_cmdb_dao_category_g_logical_unit($this->m_db);

        // First get assigned devices
        $l_dao_res = $l_dao->get_data_by_parent($p_object_id);
        $l_assigned_units = [];

        if ($l_dao_res->num_rows() > 0) {
            while ($l_dao_row = $l_dao_res->get_row()) {
                $l_assigned_units[$l_dao_row['isys_catg_logical_unit_list__id'] . '#' .
                $l_dao_row['isys_catg_logical_unit_list__isys_catg_relation_list__id']] = $l_dao_row['isys_obj__id'];
            }
        }

        // Now we create the new entries.
        foreach ($p_objects as $l_id) {
            if (!in_array($l_id, $l_assigned_units)) {
                $l_rows = $l_dao->get_data(null, $l_id)
                    ->num_rows();

                // If there is no entry, we create a new one.
                if ($l_rows == 0) {
                    $l_category_data_id = $this->create_connector('isys_catg_logical_unit_list', $l_id);
                    $l_relation_id = null;
                } else {
                    $l_row = $l_dao->get_data(null, $l_id)
                        ->get_row();
                    $l_category_data_id = $l_row['isys_catg_logical_unit_list__id'];
                    $l_relation_id = $l_row['isys_catg_logical_unit_list__isys_catg_relation_list__id'];
                }

                $l_sql = 'UPDATE isys_catg_logical_unit_list ' . 'SET isys_catg_logical_unit_list__isys_obj__id__parent = ' . $this->convert_sql_id($p_object_id) . ' ' .
                    'WHERE isys_catg_logical_unit_list__id = ' . $this->convert_sql_id($l_category_data_id);

                if ($this->update($l_sql)) {
                    $l_relation_dao->handle_relation(
                        $l_category_data_id,
                        'isys_catg_logical_unit_list',
                        defined_or_default('C__RELATION_TYPE__LOGICAL_UNIT'),
                        $l_relation_id,
                        $_GET[C__CMDB__GET__OBJECT],
                        $l_id
                    );
                }
            } elseif (count($l_assigned_units) > 0) {
                $l_key = array_search($l_id, $l_assigned_units);
                unset($l_assigned_units[$l_key]);
            }
        }

        // Now we delete the entries
        if (count($l_assigned_units) > 0) {
            foreach ($l_assigned_units as $l_key => $l_obj_id) {
                list($l_id, $l_rel_id) = explode('#', $l_key);
                $l_relation_dao->delete_relation($l_rel_id);
                $l_dao->delete_entry($l_id, 'isys_catg_logical_unit_list');
            }
        }

        return $l_category_data_id;
    }

    /**
     * Do nothing
     *
     * @param      $p_cat_level
     * @param      $p_intOldRecStatus
     * @param bool $p_create
     *
     * @return null
     */
    public function save_element($p_cat_level, $p_intOldRecStatus, $p_create = false)
    {
        return null;
    }

    public function get_count($p_obj_id = null)
    {
        if (!empty($p_obj_id)) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        $l_sql = "SELECT COUNT(isys_catg_logical_unit_list__id) AS count FROM isys_catg_logical_unit_list " . "WHERE TRUE ";

        if (!empty($l_obj_id)) {
            $l_sql .= " AND (isys_catg_logical_unit_list__isys_obj__id__parent = " . $this->convert_sql_id($l_obj_id) . ")";
        }

        $l_sql .= " AND (isys_catg_logical_unit_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ")";

        $l_data = $this->retrieve($l_sql)
            ->__to_array();

        return $l_data["count"];
    }

    /**
     * Get data method, uses logical unit DAO.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_dao = new isys_cmdb_dao_category_g_logical_unit($this->m_db);

        //return $l_dao->get_data($p_catg_list_id, $p_obj_id, $p_condition, $p_filter, $p_status);
        return $l_dao->get_data_by_parent($p_obj_id);
    }

    /**
     * @param   integer $p_parent_object_id
     * @param   integer $p_child_object_id
     *
     * @return  array
     */
    public function has_child($p_parent_object_id, $p_child_object_id)
    {
        if ($p_parent_object_id == $p_child_object_id) {
            return true;
        }

        $l_sql = 'SELECT isys_catg_logical_unit_list__isys_obj__id AS object
            FROM isys_catg_logical_unit_list
            WHERE isys_catg_logical_unit_list__isys_obj__id__parent = ' . $this->convert_sql_id($p_parent_object_id) . '
            AND isys_catg_logical_unit_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        $l_result = $this->retrieve($l_sql);

        if (is_countable($l_result) && count($l_result)) {
            while ($l_row = $l_result->get_row()) {
                if ($this->has_child($l_row['object'], $p_child_object_id)) {
                    return true;
                }
            }
        }

        return false;
    }

    private $m_collected_children = [];

    /**
     * @param  integer $p_parent_object_id
     * @param  boolean $p_include_self
     *
     * @return  array
     */
    public function get_children($p_parent_object_id, $p_include_self = false)
    {
        $this->m_collected_children = [];

        $this->get_children_recursive($p_parent_object_id);

        if ($p_include_self) {
            $this->m_collected_children[$p_parent_object_id] = [
                'isys_obj__id'      => $p_parent_object_id,
                'isys_obj_type__id' => $this->get_objTypeID($p_parent_object_id)
            ];
        }

        return array_values($this->m_collected_children);
    }

    /**
     * @param  integer $p_parent_object_id
     *
     * @throws  isys_exception_database
     */
    private function get_children_recursive($p_parent_object_id)
    {
        $l_sql = 'SELECT isys_obj__id, isys_obj__isys_obj_type__id AS isys_obj_type__id
            FROM isys_catg_logical_unit_list
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_logical_unit_list__isys_obj__id
            WHERE isys_catg_logical_unit_list__isys_obj__id__parent = ' . $this->convert_sql_id($p_parent_object_id) . '
            AND isys_catg_logical_unit_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        $l_result = $this->retrieve($l_sql);

        if (is_countable($l_result) && count($l_result)) {
            while ($l_row = $l_result->get_row()) {
                if (!isset($this->m_collected_children[$l_row['isys_obj__id']])) {
                    $this->m_collected_children[$l_row['isys_obj__id']] = $l_row;

                    $this->get_children_recursive($l_row['isys_obj__id']);
                }
            }
        }
    }

    /**
     * Get UI method, because the UI class name breaks the standards.
     *
     * @return  isys_cmdb_ui_category_g_virtual_cabling
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function &get_ui()
    {
        return new isys_cmdb_ui_category_g_assigned_logical_unit(isys_application::instance()->template);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'assigned_object' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC_UNIVERSAL__OBJECT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned Object'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_catg_logical_unit_list__isys_obj__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__LOGICAL_UNIT'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_g_assigned_logical_unit',
                        'callback_property_relation_handler'
                    ], ['isys_cmdb_dao_category_g_assigned_logical_unit']),
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_obj',
                        'isys_obj__id'
                    ],
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(child.isys_obj__title, \' {\', child.isys_obj__id, \'}\')
                                FROM isys_catg_logical_unit_list
                                INNER JOIN isys_obj AS parent ON parent.isys_obj__id = isys_catg_logical_unit_list__isys_obj__id__parent
                                INNER JOIN isys_obj AS child ON child.isys_obj__id = isys_catg_logical_unit_list__isys_obj__id',
                        'isys_catg_logical_unit_list',
                        'isys_catg_logical_unit_list__id',
                        'isys_catg_logical_unit_list__isys_obj__id__parent',
                        '',
                        '',
                        \idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([
                            ' AND parent.isys_obj__isys_obj_type__id IN
                                    (SELECT isys_obj_type_2_isysgui_catg__isys_obj_type__id FROM isys_obj_type_2_isysgui_catg
                                    INNER JOIN isysgui_catg ON isysgui_catg__id = isys_obj_type_2_isysgui_catg__isysgui_catg__id
                                    WHERE isysgui_catg__const = \'C__CATG__ASSIGNED_LOGICAL_UNIT\')'
                        ]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_logical_unit_list__isys_obj__id__parent'])
                    ),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_logical_unit_list',
                            'LEFT',
                            'isys_catg_logical_unit_list__isys_obj__id__parent',
                            'isys_obj__id',
                            'main',
                            '',
                            'main'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_obj',
                            'LEFT',
                            'isys_catg_logical_unit_list__isys_obj__id',
                            'isys_obj__id',
                            'main',
                            'child',
                            'child'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__ASSIGNED_LOGICAL_UNITS',
                    C__PROPERTY__UI__PARAMS => [
                        'multiselection' => true,
                        'catFilter'      => 'C__CATG__ASSIGNED_WORKSTATION'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__IMPORT    => true,
                    C__PROPERTY__PROVIDES__EXPORT    => true,
                    C__PROPERTY__PROVIDES__LIST      => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT => true,
                    C__PROPERTY__PROVIDES__REPORT    => true,
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__VIRTUAL   => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ]
            ])
        ];
    }

    /**
     * Purge entries.
     *
     * @param   array $p_cat_ids
     *
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     * @return  boolean
     */
    public function rank_records($p_cat_ids, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        switch ($_POST[C__GET__NAVMODE]) {
            case C__NAVMODE__QUICK_PURGE:
            case C__NAVMODE__PURGE:
                $l_dao = new isys_cmdb_dao_category_g_logical_unit($this->m_db);
                $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->m_db);

                if (is_array($p_cat_ids)) {
                    foreach ($p_cat_ids as $l_cat_id) {
                        $l_catdata = $l_dao->get_data($l_cat_id)
                            ->get_row();

                        // First delete relation.
                        if ($l_relation_dao->delete_relation($l_catdata['isys_catg_logical_unit_list__isys_catg_relation_list__id'])) {
                            // Then delete entry.
                            $l_dao->delete_entry($l_cat_id, 'isys_catg_logical_unit_list');
                        }
                    }
                }

                return true;
        }
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
            if (($p_status == isys_import_handler_cmdb::C__CREATE || $p_status == isys_import_handler_cmdb::C__UPDATE)) {
                $l_val = [];

                if (is_array($p_category_data['properties']['assigned_object'][C__DATA__VALUE])) {
                    foreach ($p_category_data['properties']['assigned_object'][C__DATA__VALUE] as $l_obj_id) {
                        $l_val[] = $l_obj_id;
                    }
                } else {
                    $l_val[] = $p_category_data['properties']['assigned_object'][C__DATA__VALUE];
                }

                if (count($l_val) > 0) {
                    $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
                    foreach ($l_val as $l_obj_id) {
                        $l_category_data_id = $this->create_connector('isys_catg_logical_unit_list', $l_obj_id);
                        $l_relation_id = null;

                        $l_sql = 'UPDATE isys_catg_logical_unit_list ' . 'SET isys_catg_logical_unit_list__isys_obj__id__parent = ' . $this->convert_sql_id($p_object_id) .
                            ' ' . 'WHERE isys_catg_logical_unit_list__id = ' . $this->convert_sql_id($l_category_data_id);

                        if ($this->update($l_sql)) {
                            $l_relation_dao->handle_relation(
                                $l_category_data_id,
                                'isys_catg_logical_unit_list',
                                defined_or_default('C__RELATION_TYPE__LOGICAL_UNIT'),
                                $l_relation_id,
                                $p_object_id,
                                $l_obj_id
                            );
                        }
                    }
                }

                return true;
            }
        }

        return false;
    }
}
