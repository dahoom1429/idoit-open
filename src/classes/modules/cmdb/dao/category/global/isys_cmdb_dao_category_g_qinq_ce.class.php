<?php

use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: global category for assigned QinQ CE-VLANs
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_qinq_ce extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'qinq_ce';

    /**
     * List component
     *
     * @var string
     */
    protected $m_list = 'isys_cmdb_dao_list_catg_qinq';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var bool
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
    protected $m_object_browser_property = 'spvlan';

    /**
     * New variable to determine if the current category is a reverse category of another one.
     *
     * @var  string
     */
    protected $m_reverse_category_of = 'isys_cmdb_dao_category_g_qinq_ce';

    /**
     * Category's table
     *
     * @var string
     */
    protected $m_table = 'isys_catg_qinq_list';

    /**
     * Method for getting the object-browsers preselection.
     *
     * @param   integer $p_obj_id
     *
     * @return  isys_component_dao_result
     */
    public function get_selected_objects($p_obj_id)
    {
        $l_dao = new isys_cmdb_dao_category_g_qinq_sp($this->m_db);

        return $l_dao->get_data_by_parent($p_obj_id);
    }

    /**
     * Create element method.
     *
     * @param   integer $p_cat_level
     * @param   integer & $p_new_id
     *
     * @return  null
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $l_dao = isys_cmdb_dao_category_g_qinq_sp::instance($this->m_db);
        $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->m_db);

        // First get assigned devices
        $l_dao_res = $l_dao->get_data_by_parent($p_object_id);
        $l_assigned_units = [];

        // Get allready assigned units
        if ($l_dao_res->num_rows() > 0) {
            while ($l_dao_row = $l_dao_res->get_row()) {
                $l_assigned_units[$l_dao_row['isys_obj__id']] = [
                    'category_id' => $l_dao_row['isys_catg_qinq_list__id'],
                    'relation_id' => $l_dao_row['isys_catg_qinq_list__isys_catg_relation_list__id'],
                ];
            }
        }

        // Now we create the new entries.
        if (is_array($p_objects)) {
            foreach ($p_objects as $l_id) {
                $this->set_ce($l_id, $p_object_id);

                // Remove from
                unset($l_assigned_units[$l_id]);
            }
        }

        // Now we delete the entries
        if (count($l_assigned_units) > 0) {
            foreach ($l_assigned_units as $l_obj_id => $l_data) {
                $this->delete_entry($l_data['category_id'], 'isys_catg_qinq_list');
                $l_relation_dao->delete_relation($l_data['relation_id']);
            }
        }
    }

    public function set_ce($p_ceID, $p_parent_id)
    {
        // Get data
        $l_dao = new isys_cmdb_dao_category_g_qinq_sp($this->m_db);
        $l_res = $l_dao->get_data(null, $p_ceID);

        // Check for category entry
        if (!$l_res->count()) {
            $l_category_id = $l_dao->create_connector('isys_catg_qinq_list', $p_ceID);
        } else {
            $l_data = $l_res->get_row();
            $l_category_id = $l_data['isys_catg_qinq_list__id'];
        }

        return $l_dao->save($l_category_id, C__RECORD_STATUS__NORMAL, $p_parent_id, $p_ceID);
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

    /**
     * @param integer $p_obj_id
     *
     * @return int
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_count($p_obj_id = null)
    {
        if (!empty($p_obj_id)) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        $l_sql = "SELECT COUNT(isys_catg_qinq_list__id) AS count FROM isys_catg_qinq_list " .
            "INNER JOIN isys_connection ON isys_connection__id = isys_catg_qinq_list__isys_connection__id " . "WHERE TRUE ";

        if (!empty($l_obj_id)) {
            $l_sql .= " AND (isys_connection__isys_obj__id = " . $this->convert_sql_id($l_obj_id) . ")";
        }

        $l_sql .= " AND (isys_catg_qinq_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ")";

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
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_dao = new isys_cmdb_dao_category_g_qinq_sp($this->m_db);

        // Consider connection field in condition
        $p_condition .= ' AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);

        return $l_dao->get_data(null, null, $p_condition, $p_filter, $p_status);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'spvlan' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__QINQ_SP__SPVLAN',
                    C__PROPERTY__INFO__DESCRIPTION => 'SP-VLAN'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_qinq_list__isys_connection__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_qinq_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_qinq_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_qinq_list__isys_obj__id',
                        'isys_connection',
                        'isys_connection__id',
                        'isys_connection__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_connection__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_connection', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_qinq_list',
                            'LEFT',
                            'isys_connection__id',
                            'isys_catg_qinq_list__isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_qinq_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__QINQ_SP__SPVLAN',
                    C__PROPERTY__UI__PARAMS => [
                        'multiselection' => true,
                        'catFilter'      => 'C__CATG__QINQ_SP',
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT     => true,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connection'
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
                $l_dao = new isys_cmdb_dao_category_g_qinq_sp($this->m_db);
                $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->m_db);

                if (is_array($p_cat_ids)) {
                    foreach ($p_cat_ids as $l_cat_id) {
                        $l_catdata = $l_dao->get_data($l_cat_id)
                            ->get_row();

                        // First delete relation.
                        if ($l_relation_dao->delete_relation($l_catdata['isys_catg_qinq_list__isys_catg_relation_list__id'])) {
                            // Then delete entry.
                            $l_dao->delete_entry($l_cat_id, 'isys_catg_qinq_list');
                        }
                    }
                }

                return true;
                break;
            default:
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
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done,
     *                 otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        return true;
    }
}
