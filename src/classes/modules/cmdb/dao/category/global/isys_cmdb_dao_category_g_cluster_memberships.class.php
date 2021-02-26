<?php

use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: Global category for cluster members
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_cluster_memberships extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'cluster_memberships';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__CLUSTER_MEMBERSHIPS';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_catg_cluster_members_list__isys_obj__id';

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
    protected $m_object_browser_property = 'connected_object';

    /**
     * Field for the object id
     *
     * @var string
     */
    protected $m_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * Category's database table.
     *
     * @var    string
     */
    protected $m_table = 'isys_catg_cluster_members_list';

    /**
     * Save global category cluster members element.
     *
     * @param   integer $p_cat_level
     * @param   integer & $p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @return  null
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        return null;
    }

    /**
     * @param int   $p_object_id
     * @param array $p_objects
     *
     * @return mixed|null
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $l_id = null;

        $l_catg_obj_id = $p_object_id;
        $l_assigned_clusters_as_array = $this->get_assigned_clusters_as_array($p_object_id);
        $l_assigned_clusters = [];

        if (is_array($l_assigned_clusters_as_array)) {
            $l_assigned_clusters = array_flip($l_assigned_clusters_as_array);
        }

        foreach ($p_objects AS $l_val) {
            if (is_numeric($l_val) && $l_val != "on") {
                unset($l_assigned_clusters[$l_val]);
                if (!$this->check_membership($l_val, $l_catg_obj_id)) {
                    $l_id = $this->create($l_val, C__RECORD_STATUS__NORMAL, $p_object_id);
                }
            }
        }

        if (count($l_assigned_clusters) > 0) {
            foreach ($l_assigned_clusters AS $l_obj_id => $l_member_id) {
                $this->delete_membership($l_member_id);
            }
        }

        return $l_id;
    }

    /**
     * Create method.
     *
     * @param   integer $p_objID
     * @param   integer $p_newRecStatus
     * @param   integer $p_connectedObjID
     * @param   string  $p_description
     *
     * @return  mixed
     */
    public function create($p_objID, $p_newRecStatus, $p_connectedObjID, $p_description = null)
    {
        $l_connection_dao = new isys_cmdb_dao_connection($this->m_db);

        $l_strSql = "INSERT INTO isys_catg_cluster_members_list SET " . "isys_catg_cluster_members_list__isys_obj__id  = " . $this->convert_sql_id($p_objID) . ", " .
            "isys_catg_cluster_members_list__description	 = " . $this->convert_sql_text($p_description) . ", " . "isys_catg_cluster_members_list__status = " .
            $this->convert_sql_id($p_newRecStatus) . ", " . "isys_catg_cluster_members_list__isys_connection__id = " .
            $this->convert_sql_id($l_connection_dao->add_connection($p_connectedObjID)) . ';';

        if ($this->update($l_strSql) && $this->apply_update()) {
            $this->m_strLogbookSQL .= $this->get_last_query();
            $l_last_id = $this->get_last_insert_id();

            $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
            $l_relation_dao->handle_relation($l_last_id, "isys_catg_cluster_members_list", defined_or_default('C__RELATION_TYPE__CLUSTER_MEMBERSHIPS'), null, $p_connectedObjID, $p_objID);

            return $l_last_id;
        } else {
            return false;
        }
    }

    /**
     * Checks if membership exists
     *
     * @param int $p_cluster_id
     * @param int $p_obj_id
     *
     * @return bool
     */
    public function check_membership($p_cluster_id, $p_obj_id)
    {

        $l_dao_cluster_members = new isys_cmdb_dao_category_g_cluster_members($this->m_db);

        $l_res = $l_dao_cluster_members->get_data(null, $p_cluster_id, " AND isys_connection__isys_obj__id = " . $this->convert_sql_id($p_obj_id));

        if ($l_res->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gets assigned Clusters.
     *
     * @param   integer $p_obj_id
     *
     * @return  isys_component_dao_result
     */
    public function get_assigned_clusters($p_obj_id)
    {
        $l_dao_cluster_members = new isys_cmdb_dao_category_g_cluster_members($this->m_db);

        return $l_dao_cluster_members->get_data(null, null, " AND isys_connection__isys_obj__id = " . $this->convert_sql_id($p_obj_id), null, C__RECORD_STATUS__NORMAL);
    }

    /**
     * Gets all assigned clusters as array
     *
     * @param $p_obj_id
     *
     * @return array
     */
    public function get_assigned_clusters_as_array($p_obj_id)
    {
        $l_sql = "SELECT isys_catg_cluster_members_list__isys_obj__id, isys_catg_cluster_members_list__id " . "FROM isys_catg_cluster_members_list " .
            "LEFT JOIN isys_connection ON isys_connection__id = isys_catg_cluster_members_list__isys_connection__id " . "WHERE isys_connection__isys_obj__id = " .
            $this->convert_sql_id($p_obj_id);
        $l_res = $this->retrieve($l_sql);
        $l_arr = [];

        while ($l_row = $l_res->get_row()) {
            $l_arr[$l_row['isys_catg_cluster_members_list__id']] = $l_row['isys_catg_cluster_members_list__isys_obj__id'];
        }

        return $l_arr;
    }

    /**
     * Deletes membership
     *
     * @param int $p_cluster_members_list_id
     *
     * @return bool
     */
    public function delete_membership($p_cluster_members_list_id)
    {
        $query = 'SELECT isys_catg_cluster_members_list__isys_catg_relation_list__id AS relationId FROM isys_catg_cluster_members_list 
          WHERE isys_catg_cluster_members_list__id = ' . $this->convert_sql_id($p_cluster_members_list_id);

        $relationId = $this->retrieve($query)
            ->get_row_value('relationId');
        $l_sql = "DELETE FROM isys_catg_cluster_members_list WHERE " . "isys_catg_cluster_members_list__id = " . $this->convert_sql_id($p_cluster_members_list_id) . ';';

        if ($this->update($l_sql) && $this->apply_update()) {
            if ($relationId) {
                $dao = isys_cmdb_dao_category_g_relation::instance(isys_application::instance()->database);
                $dao->delete_relation($relationId);
            }

            $this->m_strLogbookSQL .= $this->get_last_query();

            return true;
        } else {
            return false;
        }
    }

    /**
     * Counts the number of assigned cluster members.
     *
     * @param   integer $p_obj_id
     *
     * @return  mixed
     */
    public function get_count($p_obj_id = null)
    {
        if (!empty($p_obj_id)) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        $l_sql = "SELECT COUNT(isys_obj__id) AS count FROM isys_catg_cluster_members_list " .
            "INNER JOIN isys_connection ON isys_connection__id = isys_catg_cluster_members_list__isys_connection__id " .
            "INNER JOIN isys_obj ON isys_obj__id = isys_catg_cluster_members_list__isys_obj__id " . "WHERE TRUE ";

        if (!empty($l_obj_id)) {
            $l_sql .= "AND (isys_connection__isys_obj__id = " . $this->convert_sql_id($l_obj_id) . ") ";
        }

        $l_sql .= " AND (isys_catg_cluster_members_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ") " . "AND isys_obj__status = " .
            $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ";";

        $l_data = $this->retrieve($l_sql)
            ->__to_array();

        return $l_data["count"];
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_catg_cluster_members_list " . "INNER JOIN isys_connection ON isys_connection__id = isys_catg_cluster_members_list__isys_connection__id " .
            "INNER JOIN isys_obj ON isys_obj__id = isys_catg_cluster_members_list__isys_obj__id " . "WHERE TRUE " . $p_condition . " ";

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= "AND isys_catg_cluster_members_list__id = " . $this->convert_sql_id($p_catg_list_id) . " ";
        }

        if ($p_status !== null) {
            $l_sql .= "AND isys_catg_cluster_members_list__status = " . $this->convert_sql_int($p_status) . " ";
        }

        $l_sql .= "AND isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ";";

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
                $l_sql = ' AND (isys_connection__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
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
    public function properties()
    {
        return [
            'connected_object' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__OBJTYPE__CLUSTER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Connected object',
                    C__PROPERTY__INFO__BACKWARD    => true
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_catg_cluster_members_list__isys_obj__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__CLUSTER_MEMBERSHIPS'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_g_cluster_memberships',
                        'callback_property_relation_handler'
                    ], ['isys_cmdb_dao_category_g_cluster_memberships']),
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_connection',
                        'isys_connection__isys_obj__id'
                    ],
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\') FROM isys_catg_cluster_members_list
                              INNER JOIN isys_obj ON isys_catg_cluster_members_list__isys_obj__id = isys_obj__id
                              INNER JOIN isys_connection ON isys_connection__id = isys_catg_cluster_members_list__isys_connection__id', 'isys_connection',
                        'isys_connection__id', 'isys_connection__isys_obj__id', '', '', null,
                        \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_connection__isys_obj__id'])),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_connection', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_cluster_members_list', 'LEFT', 'isys_connection__id',
                            'isys_catg_cluster_members_list__isys_connection__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_cluster_members_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__CLUSTER__OBJ',
                    C__PROPERTY__UI__PARAMS => [
                        'multiselection'                            => true,
                        isys_popup_browser_object_ng::C__CAT_FILTER => 'C__CATG__CLUSTER_ROOT;C__CATG__CLUSTER;C__CATG__CLUSTER_SERVICE;C__CATG__CLUSTER_MEMBERS;C__CATG__CLUSTER_VITALITY',
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__LIST   => true
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
     * Synchronizes properties from an import with the database.
     *
     * @param array $p_category_data Values of category data to be saved.
     * @param int   $p_object_id     Current object identifier (from database)
     * @param int   $p_status        Decision whether category data should be created or
     *                               just updated.
     *
     * @return mixed Returns category data identifier (int) on success, true
     * (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0) {
                        return $this->create($p_category_data['properties']['connected_object'][C__DATA__VALUE], C__RECORD_STATUS__NORMAL, $p_object_id);
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    //$p_status should be C__CREATE only ;)
                    if ($p_category_data['data_id'] > 0) {
                        return $p_category_data['data_id'];
                    }

                    break;
            }
        }

        return false;
    }

    /**
     * Compares category data for import.
     *
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

            if ($l_dataset['isys_obj__id'] == $p_category_data_values['properties']['connected_object']['id']) {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;

                return;
            } else {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
            }
        }
    }
}

?>