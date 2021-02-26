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
class isys_cmdb_dao_category_g_cluster_members extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'cluster_members';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__CLUSTER_MEMBERS';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

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
    protected $m_object_browser_property = 'member';

    /**
     * Field for the object id
     *
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_cluster_members_list__isys_obj__id';

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT " . "isys_obj.*, isys_connection.*, isys_catg_cluster_members_list.*, " .
            "ob2.isys_obj__status, ob2.isys_obj__id as memberID, ob2.isys_obj__sysid as memberSYSID, " .
            "ob2.isys_obj__title as memberTitle, ob2.isys_obj__isys_obj_type__id as memberType " . "FROM isys_catg_cluster_members_list " .
            "INNER JOIN isys_obj ON isys_obj__id = isys_catg_cluster_members_list__isys_obj__id " .
            "LEFT JOIN isys_connection ON isys_connection__id = isys_catg_cluster_members_list__isys_connection__id " .
            "LEFT JOIN isys_obj AS ob2 ON ob2.isys_obj__id = isys_connection__isys_obj__id " . "WHERE TRUE ";

        $l_sql .= $p_condition;

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_catg_list_id)) {
            $l_sql .= " AND (isys_catg_cluster_members_list__id = " . $this->convert_sql_id($p_catg_list_id) . ")";
        }

        if (!empty($p_status)) {
            $l_sql .= " AND (isys_catg_cluster_members_list__status = '{$p_status}')";
        }

        $l_sql .= " AND ob2.isys_obj__status = '" . C__RECORD_STATUS__NORMAL . "'";

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
                $l_sql = ' AND (isys_catg_cluster_members_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_catg_cluster_members_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
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
            'member' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER_MEMBER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Cluster member'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_catg_cluster_members_list__isys_connection__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__CLUSTER_MEMBERSHIPS'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_g_cluster_members',
                        'callback_property_relation_handler'
                    ], [
                        'isys_cmdb_dao_category_g_cluster_members',
                        true
                    ]),
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\') FROM isys_obj
                              INNER JOIN isys_connection ON isys_connection__isys_obj__id = isys_obj__id
                              INNER JOIN isys_catg_cluster_members_list ON isys_catg_cluster_members_list__isys_connection__id = isys_connection__id',
                        'isys_catg_cluster_members_list',
                        'isys_catg_cluster_members_list__id',
                        'isys_catg_cluster_members_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_cluster_members_list',
                            'LEFT',
                            'isys_catg_cluster_members_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_catg_cluster_members_list__isys_connection__id',
                            'isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__CLUSTER_MEMBERS__OBJ',
                    C__PROPERTY__UI__PARAMS => [
                        'multiselection' => true
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false
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
                    if ($p_object_id > 0 && isset($p_category_data['properties']['member'][C__DATA__VALUE])) {
                        if (!is_array($p_category_data['properties']['member'][C__DATA__VALUE])) {
                            $p_category_data['properties']['member'][C__DATA__VALUE] = [$p_category_data['properties']['member'][C__DATA__VALUE]];
                        }

                        foreach ($p_category_data['properties']['member'][C__DATA__VALUE] as $member) {
                            $this->create(
                                $p_object_id,
                                C__RECORD_STATUS__NORMAL,
                                $member,
                                $p_category_data['properties']['description'][C__DATA__VALUE]
                            );
                        }

                        return true;
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $this->save(
                            $p_category_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['member'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }

                    break;
            }
        }

        return false;
    }

    /**
     * Save global category cluster members element
     *
     * @param $p_cat_level        level to save, default 0
     * @param &$p_intOldRecStatus __status of record before update
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        return null;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level
     *
     * @param int    $p_cat_level
     * @param int    $p_newRecStatus
     * @param int    $p_connectedObjID
     * @param String $p_description
     *
     * @return boolean true, if transaction executed successfully, else false
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_connectedObjID, $p_description)
    {
        $l_strSql = "UPDATE isys_catg_cluster_members_list " . "INNER JOIN isys_connection ON isys_connection__id = isys_catg_cluster_members_list__isys_connection__id " .
            "SET " . "isys_connection__isys_obj__id = " . $this->convert_sql_id($p_connectedObjID) . ", " . "isys_catg_cluster_members_list__description = " .
            $this->convert_sql_text($p_description) . ", " . "isys_catg_cluster_members_list__status = " . $this->convert_sql_id($p_newRecStatus) . " " .
            "WHERE isys_catg_cluster_members_list__id = " . $p_cat_level;

        if ($this->update($l_strSql)) {
            if ($this->apply_update()) {
                /* Create implicit relation */
                $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());

                $l_data = $this->get_data($p_cat_level)
                    ->__to_array();

                $l_relation_dao->handle_relation(
                    $p_cat_level,
                    "isys_catg_cluster_members_list",
                    defined_or_default('C__RELATION_TYPE__CLUSTER_MEMBERSHIPS'),
                    $l_data["isys_catg_cluster_members_list__isys_catg_relation_list__id"],
                    $p_connectedObjID,
                    $l_data["isys_catg_cluster_members_list__isys_obj__id"]
                );

                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param array $p_post
     *
     * @return int
     * @throws Exception
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $l_id = null;
        $l_currentMembers = [];

        /**
         * 1) Check for delete objects in $p_objects
         *  1a) Delete current connection if there is a deleted member
         * 2) Create a currentMember array to check if the entry is already existings afterwards
         */
        $l_current = $this->get_data_by_object($p_object_id);
        while ($l_row = $l_current->get_row()) {
            if (!in_array($l_row["isys_connection__isys_obj__id"], $p_objects)) {
                $this->delete_entry($l_row[$this->m_source_table . '_list__id'], $this->m_source_table . '_list');
            } else {
                $l_currentMembers[$l_row["isys_connection__isys_obj__id"]] = $l_row["isys_connection__isys_obj__id"];
            }
        }

        /**
         * Create entries
         */
        foreach ($p_objects as $l_object_id) {
            if (is_numeric($l_object_id)) {
                if (!isset($l_currentMembers[$l_object_id]) || !$l_currentMembers[$l_object_id]) {
                    $l_id = $this->create($_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__NORMAL, $l_object_id, "");
                }
            }
        }

        return $l_id;
    }

    /**
     * Executes the query to create the category entry referenced by isys_catg_cluster_members__id $p_fk_id
     *
     * @param int    $p_objID
     * @param int    $p_newRecStatus
     * @param int    $p_connectedObjID
     * @param String $p_description
     *
     * @return int the newly created ID or false
     * @author Dennis Blümer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus, $p_connectedObjID, $p_description)
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
     * Post rank is called after a regular rank
     *
     * @param int    $p_list_id
     * @param int    $p_direction
     * @param string $p_table
     *
     * @return boolean
     */
    public function post_rank($p_list_id, $p_direction, $p_table)
    {
        $l_pr_dao = new isys_cmdb_dao_category_s_parallel_relation($this->m_db);
        $l_relation = new isys_cmdb_dao_category_g_relation($this->m_db);

        $l_data = $this->get_data($p_list_id)
            ->__to_array();
        $l_reldata = $l_relation->get_data($l_data["isys_catg_cluster_members_list__isys_catg_relation_list__id"])
            ->__to_array();

        if ($l_reldata["isys_catg_relation_list__isys_obj__id"]) {
            if (isset($l_data["isys_catg_cluster_members_list__status"]) && $l_data["isys_catg_cluster_members_list__status"] == C__RECORD_STATUS__NORMAL) {
                $l_pr_dao->rank($l_reldata["isys_catg_relation_list__isys_obj__id"], C__RECORD_STATUS__NORMAL);
            } else {
                $l_pr_dao->rank($l_reldata["isys_catg_relation_list__isys_obj__id"], C__RECORD_STATUS__ARCHIVED);
            }
        }

        return true;
    }

    /**
     * Gets assigned members for the cluster. Used for retrieving a preselection for the object browser.
     *
     * @param int $p_obj_id
     *
     * @return isys_component_dao_result
     */
    public function get_assigned_members($p_obj_id)
    {
        $l_sql = "SELECT isys_obj.*, isys_catg_cluster_members_list__id FROM isys_catg_cluster_members_list " . "INNER JOIN isys_connection " .
            "ON isys_connection__id = isys_catg_cluster_members_list__isys_connection__id " . "INNER JOIN isys_obj " . "ON isys_connection__isys_obj__id = isys_obj__id " .
            "WHERE isys_obj__status = '" . C__RECORD_STATUS__NORMAL . "' " . "AND isys_catg_cluster_members_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id);

        return $this->retrieve($l_sql);
    }

    /**
     * Gets assigned members for the cluster
     *
     * @param int  $p_obj_id
     * @param bool $p_as_string
     *
     * @return mixed var
     */
    public function get_assigned_members_as_array($p_obj_id, $p_as_string = false)
    {
        $l_res = $this->get_data(null, $p_obj_id, "", null, C__RECORD_STATUS__NORMAL);
        if ($l_res->num_rows() > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_arr[] = $l_row["isys_connection__isys_obj__id"];
            }
        } else {
            return false;
        }

        if ($p_as_string) {
            return implode(",", $l_arr);
        } else {
            return $l_arr;
        }
    }
}
