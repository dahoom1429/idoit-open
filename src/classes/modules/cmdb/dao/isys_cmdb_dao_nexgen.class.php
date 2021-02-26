<?php

use idoit\Component\Helper\Purify;

/**
 * i-doit
 *
 * CMDB DAO Framework
 *
 * @package    i-doit
 * @subpackage CMDB_Low-Level_API
 * @author     Andre Woesten <awoesten@i-doit.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_nexgen extends isys_component_dao
{
    /**
     * May hold an array of CMDB statuses.
     *
     * @var  mixed
     */
    protected $m_cmdb_status = null;

    /**
     *
     * @var  string
     */
    protected $m_cmdb_status_prefix = '';

    /**
     * Cache array.
     *
     * @var  array
     */
    private $m_cache = [];

    /**
     * Retrieve all object type groups.
     *
     * @return  isys_component_dao_result
     */
    public function objgroup_get()
    {
        return $this->retrieve('SELECT * FROM isys_obj_type_group;');
    }

    /**
     * Retrieve a single object type group, by its ID.
     *
     * @param   integer $p_objgroup_id
     *
     * @return  isys_component_dao_result
     */
    public function objgroup_get_by_id($p_objgroup_id)
    {
        return $this->retrieve('SELECT * FROM isys_obj_type_group WHERE isys_obj_type_group__id = ' . $this->convert_sql_id($p_objgroup_id) . ';');
    }

    /**
     * Count object-types.
     *
     * @param   integer $p_objtype
     * @param   integer $p_status
     * @param   boolean $p_ignore_cmdb_status
     *
     * @return  integer
     * @author  Dennis Stuecken
     */
    public function count_objects_by_type($p_objtype = null, $p_status = C__RECORD_STATUS__NORMAL, $p_ignore_cmdb_status = false)
    {
        if (!$p_ignore_cmdb_status && is_array($this->m_cmdb_status)) {
            if (defined('C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE') && is_numeric(array_search(defined_or_default('C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE'), $this->m_cmdb_status))) {
                $p_status = C__RECORD_STATUS__TEMPLATE;
            }
        }

        $l_sql = "SELECT COUNT(isys_obj__id) AS objects
			FROM isys_obj_type
			INNER JOIN isys_obj ON isys_obj__isys_obj_type__id = isys_obj_type__id
			WHERE isys_obj__status = " . $this->convert_sql_int($p_status) . " ";

        if ($p_objtype !== null) {
            $l_sql .= "AND isys_obj_type__id = " . $this->convert_sql_id($p_objtype) . " ";
        }

        $l_cmdb_status_filter = $this->prepare_status_filter();
        if (!$p_ignore_cmdb_status && $l_cmdb_status_filter != "") {
            $l_sql .= " AND " . $l_cmdb_status_filter;
        }

        $l_data = $this->retrieve($l_sql)
            ->get_row();

        return (int)$l_data["objects"];
    }

    /**
     * Create SQL condition for cmdb status.
     *
     * @param   mixed $p_status
     *
     * @return  string
     */
    public function prepare_status_filter($p_status = null)
    {
        if ($p_status !== null) {
            $this->m_cmdb_status = $p_status;
        }

        if (is_array($this->m_cmdb_status)) {
            $l_filter = array_filter($this->m_cmdb_status, function ($p_status) {
                return ($p_status > 0);
            });

            if (count($l_filter) > 0) {
                $l_template_key = false;
                if (defined('C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE') && is_numeric($l_template_key = array_search(constant('C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE'), $l_filter))) {
                    unset($l_filter[$l_template_key]);
                }

                if (count($l_filter) > 0) {
                    return " isys_obj__isys_cmdb_status__id IN (" . implode(',', $l_filter) . ") ";
                }
            }
        }

        return "";
    }

    /**
     * Method for getting a category entry status.
     *
     * @param   string  $p_source_table
     * @param   integer $p_cat_id
     *
     * @return  mixed
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function cat_get_status_by_id($p_source_table, $p_cat_id)
    {
        if ($p_source_table != null) {
            $l_res = $this->retrieve("SELECT " . $p_source_table . "__status AS status_rec " . "FROM " . $p_source_table . " WHERE " . $p_source_table . "__id = " .
                $this->convert_sql_id($p_cat_id));

            if (is_countable($l_res) && count($l_res)) {
                $l_row = $l_res->get_row();

                return $l_row["status_rec"];
            }
        }

        return null;
    }

    /**
     * Get the count of category records specified by the record status
     *
     * @param int $p_cat_type
     * @param int $p_cat_id
     * @param int $p_objID
     * @param int $p_status
     *
     * @return int
     * @throws isys_exception_database
     */
    public function cat_count_by_status($p_cat_type, $p_cat_id, $p_objID, $p_status = null)
    {
        $l_tbl_src = $this->gui_get_source_table_by_category($p_cat_type, $p_cat_id);
        if ($l_tbl_src != null) {
            $l_q = 'SELECT COUNT(*) AS count_rec
                FROM ' . $l_tbl_src . '_list
                WHERE ' . $l_tbl_src . "_list__isys_obj__id = " . $this->convert_sql_id($p_objID);

            if ($p_status) {
                $l_q .= ' AND ' . $l_tbl_src . '_list__status = ' . $this->convert_sql_int($p_status);
            }

            if ($l_tbl_src === 'isys_catg_custom_fields') {
                $l_q .= ' AND ' . $l_tbl_src . '_list__isysgui_catg_custom__id = ' . $this->convert_sql_id($p_cat_id);
            }

            $l_res = $this->retrieve($l_q . ';');
            if ($l_res->num_rows() > 0) {
                $l_row = $l_res->get_row();

                return (int)$l_row["count_rec"];
            }
        }

        return 0;
    }

    /**
     * @param $p_obj_id
     *
     * @return mixed
     * @throws Exception
     * @throws isys_exception_database
     */
    public function obj_get_title_by_id_as_string($p_obj_id)
    {
        return $this->retrieve('SELECT isys_obj__title FROM isys_obj WHERE isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';')
            ->get_row_value('isys_obj__title');
    }

    /**
     * Returns a boolean value if the given object type has the given global category assigned.
     *
     * @param   integer       $p_objtype_id
     * @param   integer|array $p_catg_id
     *
     * @return  boolean
     */
    public function objtype_is_catg_assigned($p_objtype_id, $p_catg_id)
    {
        $l_sql = 'SELECT isys_obj_type_2_isysgui_catg__id
			FROM isys_obj_type_2_isysgui_catg AS T_CONN
			LEFT JOIN isys_obj_type AS T_TYPE ON T_TYPE.isys_obj_type__id=T_CONN.isys_obj_type_2_isysgui_catg__isys_obj_type__id
			LEFT JOIN isysgui_catg AS T_CATG ON T_CATG.isysgui_catg__id=T_CONN.isys_obj_type_2_isysgui_catg__isysgui_catg__id
			WHERE T_CONN.isys_obj_type_2_isysgui_catg__isys_obj_type__id = ' . $this->convert_sql_id($p_objtype_id) . '
			AND T_CATG.isysgui_catg__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' ';

        if (is_array($p_catg_id)) {
            $l_sql .= 'AND T_CONN.isys_obj_type_2_isysgui_catg__isysgui_catg__id ' . $this->prepare_in_condition($p_catg_id);

            return ($this->retrieve($l_sql)
                    ->num_rows() == count($p_catg_id));
        } else {
            $l_sql .= 'AND T_CONN.isys_obj_type_2_isysgui_catg__isysgui_catg__id = ' . $this->convert_sql_id($p_catg_id);
            $res = $this->retrieve($l_sql . ';');
            return is_countable($res) && count($res) > 0;
        }
    }

    /**
     * Returns a boolean value if the given object type has the given global category assigned.
     *
     * @param   integer $p_objtype_id
     * @param   integer $p_catg_custom_id
     *
     * @return  boolean
     */
    public function objtype_is_catg_custom_assigned($p_objtype_id, $p_catg_custom_id)
    {
        $l_sql = 'SELECT isys_obj_type_2_isysgui_catg_custom__id FROM isys_obj_type_2_isysgui_catg_custom
            INNER JOIN isysgui_catg_custom ON isysgui_catg_custom__id = isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id
            WHERE isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id = ' . $this->convert_sql_id($p_objtype_id) . '
			AND isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id = ' . $this->convert_sql_id($p_catg_custom_id) . '
			AND isysgui_catg_custom__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        $res = $this->retrieve($l_sql);
        return is_countable($res) && count($res) > 0;
    }

    /**
     * Returns a boolean value if the given object type has the given specific category assigned.
     *
     * @param   integer $p_objtype_id
     * @param   integer $p_cats_id
     *
     * @return  boolean
     */
    public function objtype_is_cats_assigned($p_objtype_id, $p_cats_id)
    {
        $l_sql = 'SELECT isys_obj_type__id
			FROM isys_obj_type
			LEFT JOIN isysgui_cats ON isysgui_cats__id = isys_obj_type__isysgui_cats__id
			WHERE isys_obj_type__id = ' . $this->convert_sql_id($p_objtype_id) . '
			AND isys_obj_type__isysgui_cats__id = ' . $this->convert_sql_id($p_cats_id) . '
			AND isysgui_cats__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        $res = $this->retrieve($l_sql);
        return is_countable($res) && count($res) > 0;
    }

    /**
     * This method gets all object types which are assigned to the specific category of the specified object type.
     * Only if a specific category is assigned to the specified object type.
     *
     * @param   mixed   $p_obj_type_id
     * @param   boolean $p_values_as_id
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function get_objecttypes_using_cats($p_obj_type_id, $p_values_as_id = false)
    {
        $l_arr_objtypes = [];
        $l_objtype_data = $this->get_object_types($p_obj_type_id)
            ->get_row();

        if ($l_objtype_data['isys_obj_type__isysgui_cats__id'] > 0) {
            $l_sql = 'SELECT isys_obj_type__id, isys_obj_type__const
				FROM isys_obj_type
				WHERE isys_obj_type__isysgui_cats__id = ' . $this->convert_sql_id($l_objtype_data['isys_obj_type__isysgui_cats__id']);

            $l_res = $this->retrieve($l_sql);

            while ($l_row = $l_res->get_row()) {
                $l_arr_objtypes[] = ($p_values_as_id) ? $l_row['isys_obj_type__id'] : $l_row['isys_obj_type__const'];
            }
        }

        if (count($l_arr_objtypes) > 0) {
            return $l_arr_objtypes;
        } else {
            return false;
        }
    }

    /**
     * Returns a result set including the object types for the specified object group and its object-count.
     * The object-count can be additionally checked by permission.
     *
     * @param  int  $objectTypeGroupId
     * @param  bool $checkRights
     * @param  int  $status
     * @param  bool $displayObjectCount
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function objtype_get_by_objgroup_id($objectTypeGroupId, $checkRights = false, $status = C__RECORD_STATUS__NORMAL, $displayObjectCount = true)
    {
        if (is_array($this->m_cmdb_status) && defined('C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE') &&
            is_numeric(array_search(constant('C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE'), $this->m_cmdb_status, false))) {
            $status = C__RECORD_STATUS__TEMPLATE;
        }

        $status = $this->convert_sql_int($status);
        $objectTypeGroupId = $this->convert_sql_id($objectTypeGroupId);
        $l_cmdb_status_filter = $this->prepare_status_filter();

        if ($l_cmdb_status_filter !== '') {
            $l_cmdb_status_filter = ' AND ' . $l_cmdb_status_filter;
        }

        $objectTypeCondition = $condition = '';

        if ($checkRights) {
            /**
             * @see  check ID-1545
             * @todo This behaviour has to be changed!! It's inacceptable to do an IN operation for more than 100 values.
             *       This stupid code can result into thousands of values and can exceed the max_allowed_packet size.
             */
            $condition = isys_auth_cmdb_objects::instance()->get_allowed_objects_condition();
            $objectTypeCondition = isys_auth_cmdb_objects::instance()->get_allowed_object_types_condition();
        }

        $counterSql = "''";

        if ($displayObjectCount) {
            $counterSql = "(SELECT COUNT(isys_obj__id) 
                FROM isys_obj 
                WHERE isys_obj__isys_obj_type__id = isys_obj_type__id 
                {$condition} 
                AND isys_obj__status = {$status}
                {$l_cmdb_status_filter})";
        }

        $l_q = "SELECT *, {$counterSql} as objcount 
              FROM isys_obj_type 
              WHERE isys_obj_type__isys_obj_type_group__id = {$objectTypeGroupId}
              {$objectTypeCondition} 
              ORDER BY isys_obj_type__sort, isys_obj_type__title;";

        return $this->retrieve($l_q);
    }

    /**
     * Get object types by ID, constant or array of ID or constants.
     *
     * @param   mixed $p_type_id May be an integer (ID), a string (constant name) or an array consisting of integers or strings.
     *
     * @return  isys_component_dao_result
     */
    public function get_object_types($p_type_id = null, $p_visible_in_tree = null)
    {
        $l_sql = 'SELECT * FROM isys_obj_type WHERE TRUE';

        if ($p_type_id !== null) {
            if (is_numeric($p_type_id)) {
                $l_sql .= " AND isys_obj_type__id = " . $this->convert_sql_id($p_type_id);
            } elseif (is_string($p_type_id)) {
                $l_sql .= " AND isys_obj_type__const = " . $this->convert_sql_text($p_type_id);
            } elseif (is_array($p_type_id) && count($p_type_id) > 0) {
                $l_numeric = $l_constant = [];

                foreach ($p_type_id as $l_object_type) {
                    if (is_numeric($l_object_type)) {
                        $l_numeric[] = $this->convert_sql_id($l_object_type);
                    } elseif (is_string($l_object_type)) {
                        $l_constant[] = $this->convert_sql_text($l_object_type);
                    }
                }

                if (count($l_numeric) > 0) {
                    $l_sql .= " AND isys_obj_type__id IN (" . implode(',', $l_numeric) . ") ";
                }

                if (count($l_constant) > 0) {
                    $l_sql .= " AND isys_obj_type__const IN (" . implode(',', $l_constant) . ") ";
                }
            }
        }

        if ($p_visible_in_tree !== null) {
            $l_sql .= ' AND isys_obj_type__show_in_tree = ' . $this->convert_sql_boolean($p_visible_in_tree);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for counting all object types.
     *
     * @param   boolean $p_only_visible
     * @param   integer $p_status
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function count_object_types($p_only_visible = false, $p_status = null)
    {
        $l_sql = 'SELECT COUNT(*) AS cnt FROM isys_obj_type WHERE TRUE';

        if ($p_status !== null) {
            $l_sql .= ' AND isys_obj_type__status = ' . $this->convert_sql_int($p_status);
        }

        if ($p_only_visible) {
            $l_sql .= ' AND isys_obj_type__show_in_tree = 1';
        }

        $l_row = $this->retrieve($l_sql . ';')
            ->get_row();

        return (int)$l_row['cnt'];
    }

    /**
     * Fetches objects by given type.
     *
     * @param   integer $p_type   Object type identifier. Defaults to all types.
     * @param   integer $p_status Record status
     * @param   integer $p_limit  Limitation: where to start and number of elements, i.e. 0 or 0,10. Defaults to null that means no limitation.
     * @param   string  $p_filter Filter by object title
     * @param   string  $p_sort
     * @param   string  $p_condition
     *
     * @throws  Exception
     * @throws  isys_exception_database
     * @return  isys_component_dao_result
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_objects_by_type_id($p_type = null, $p_status = null, $p_limit = null, $p_filter = '', $p_sort = null, $p_condition = null)
    {
        $l_query = 'SELECT isys_obj.*, isys_obj_type__title, isys_obj_type__isys_obj_type_group__id ' . 'FROM isys_obj ' .
            'INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id ' . 'WHERE TRUE';

        if (isset($p_type)) {
            $l_query .= ' AND isys_obj__isys_obj_type__id = ' . $this->convert_sql_id($p_type);
        }

        if (isset($p_status)) {
            $l_query .= ' AND isys_obj__status = ' . $this->convert_sql_id($p_status);
        }

        if (!empty($p_filter)) {
            $l_query .= ' AND isys_obj__title LIKE \'%' . $this->m_db->escape_string($p_filter) . '%\'';
        }

        if (!empty($p_condition)) {
            $l_query .= $this->m_db->escape_string($p_condition);
        }

        $l_query .= ' ORDER BY isys_obj__title';

        if (isset($p_sort) && !empty($p_sort)) {
            $p_sort = strtoupper($p_sort);

            $l_query .= ' ' . $p_sort;
        }

        if (isset($p_limit)) {
            // Trim the commas, because it can happen, that $p_limit looks like ", 1".
            $l_raw_limit = explode(',', trim($p_limit, ','));
            $l_limit = [];

            foreach ($l_raw_limit as $l_value) {
                $l_value = trim($l_value);

                if (!empty($l_value)) {
                    $l_limit[] = $l_value;
                }
            }

            $l_query .= ' LIMIT ' . implode(', ', $l_limit);
        }

        return $this->retrieve($l_query);
    }

    /**
     * Fetches objects by given global category.
     *
     * @param   mixed   $p_catg   May be an integer or array of integers
     * @param   integer $p_status Record status
     * @param   integer $p_limit  Limitation: where to start and number of elements, i.e. 0 or 0,10. Defaults to null that means no limitation.
     * @param   string  $p_filter Filter by object title
     * @param   string  $p_sort
     * @param   string  $p_condition
     *
     * @throws  Exception
     * @throws  isys_exception_database
     * @return  isys_component_dao_result
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_objects_by_catg_id($p_catg, $p_status = null, $p_limit = null, $p_filter = '', $p_sort = null, $p_condition = null)
    {
        $l_query = 'SELECT isys_obj.*, isys_obj_type__title, isys_obj_type__isys_obj_type_group__id
            FROM isys_obj
            INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id ';

        if (is_array($p_catg)) {
            $l_query .= 'WHERE isys_obj_type__id IN (SELECT isys_obj_type_2_isysgui_catg__isys_obj_type__id FROM isys_obj_type_2_isysgui_catg WHERE isys_obj_type_2_isysgui_catg__isysgui_catg__id ' .
                $this->prepare_in_condition($p_catg) . ')';
        } else {
            $l_query .= 'WHERE isys_obj_type__id IN (SELECT isys_obj_type_2_isysgui_catg__isys_obj_type__id FROM isys_obj_type_2_isysgui_catg WHERE isys_obj_type_2_isysgui_catg__isysgui_catg__id = ' .
                $this->convert_sql_id($p_catg) . ')';
        }

        if (isset($p_status)) {
            $l_query .= ' AND isys_obj__status = ' . $this->convert_sql_id($p_status);
        }

        if (!empty($p_filter)) {
            $l_query .= ' AND isys_obj__title LIKE "%' . $this->m_db->escape_string($p_filter) . '%"';
        }

        if (!empty($p_condition)) {
            $l_query .= $this->m_db->escape_string($p_condition);
        }

        $l_query .= ' ORDER BY isys_obj__title';

        if (isset($p_sort) && !empty($p_sort)) {
            $p_sort = strtoupper($p_sort);

            $l_query .= ' ' . $p_sort;
        }

        if (isset($p_limit)) {
            // Trim the commas, because it can happen, that $p_limit looks like ", 1".
            $l_raw_limit = explode(',', trim($p_limit, ','));
            $l_limit = [];

            foreach ($l_raw_limit as $l_value) {
                $l_value = trim($l_value);

                if (!empty($l_value)) {
                    $l_limit[] = $l_value;
                }
            }

            $l_query .= ' LIMIT ' . implode(', ', $l_limit);
        }

        return $this->retrieve($l_query);
    }

    /**
     * Fetches objects by given specific category.
     *
     * @param   mixed   $p_cats   May be an integer or array of integers
     * @param   integer $p_status Record status
     * @param   integer $p_limit  Limitation: where to start and number of elements, i.e. 0 or 0,10. Defaults to null that means no limitation.
     * @param   string  $p_filter Filter by object title
     * @param   string  $p_sort
     * @param   string  $p_condition
     *
     * @throws  Exception
     * @throws  isys_exception_database
     * @return  isys_component_dao_result
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_objects_by_cats_id($p_cats, $p_status = null, $p_limit = null, $p_filter = '', $p_sort = null, $p_condition = null)
    {
        $l_query = 'SELECT isys_obj.*, isys_obj_type__title, isys_obj_type__isys_obj_type_group__id
            FROM isys_obj
            INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id ';

        if (is_array($p_cats)) {
            $l_query .= 'WHERE isys_obj_type__isysgui_cats__id ' . $this->prepare_in_condition($p_cats);
        } else {
            $l_query .= 'WHERE isys_obj_type__isysgui_cats__id = ' . $this->convert_sql_id($p_cats);
        }

        if (isset($p_status)) {
            $l_query .= ' AND isys_obj__status = ' . $this->convert_sql_id($p_status);
        }

        if (!empty($p_filter)) {
            $l_query .= ' AND isys_obj__title LIKE "%' . $this->m_db->escape_string($p_filter) . '%"';
        }

        if (!empty($p_condition)) {
            $l_query .= $this->m_db->escape_string($p_condition);
        }

        $l_query .= ' ORDER BY isys_obj__title';

        if (isset($p_sort) && !empty($p_sort)) {
            $p_sort = strtoupper($p_sort);

            $l_query .= ' ' . $p_sort;
        }

        if (isset($p_limit)) {
            // Trim the commas, because it can happen, that $p_limit looks like ", 1".
            $l_raw_limit = explode(',', trim($p_limit, ','));
            $l_limit = [];

            foreach ($l_raw_limit as $l_value) {
                $l_value = trim($l_value);

                if (!empty($l_value)) {
                    $l_limit[] = $l_value;
                }
            }

            $l_query .= ' LIMIT ' . implode(', ', $l_limit);
        }

        return $this->retrieve($l_query);
    }

    /**
     * Fetches objects optional filtered by any given property.
     *
     * @param array  $p_properties (optional) Filter by properties (associative
     *                             array). Short names as keys may be used. Currently supports only object
     *                             identifiers and title, type identifiers and title, SYSID, record status,
     *                             first name, last name and email address. Defaults to null, so result
     *                             won't be filtered.
     * @param string $p_order_by   (optional) Order by one of the supported
     *                             properties. Defaults to null that means result will be ordered by object
     *                             identifiers.
     * @param string $p_sort       (optional) Order result ascending ('ASC') or
     *                             descending ('DESC'). Defaults to null that normally means 'ASC'.
     * @param int    $p_limit      (optional) Limitation: where to start and number of
     *                             elements, i.e. 0 or 0,10. Defaults to null that means no limitation.
     *
     * @return isys_component_dao_result Result set
     *
     * @author Benjamin Heisig <bheisig@synetics.de>
     */
    public function get_objects($p_properties = null, $p_order_by = null, $p_sort = null, $p_limit = null)
    {
        $l_selections = '';

        // Check properties and convert to short names if needed:
        $l_properties = [];
        if (isset($p_properties)) {
            assert(is_array($p_properties));
            foreach ($p_properties as $l_key => $l_value) {
                assert(is_string($l_key));
                if ($l_key === 'isys_obj__id') {
                    $l_properties['ids'] = [$l_value];
                } elseif ($l_key === 'ids') {
                    assert(is_array($l_value));
                    $l_properties['ids'] = $l_value;
                } elseif ($l_key === 'isys_obj_type__id' || $l_key === 'isys_obj__isys_obj_type__id' || $l_key == 'type') {

                    /* Convert Constant in ID */
                    if (!is_numeric($l_value) && is_string($l_value) && defined($l_value)) {
                        $l_value = constant($l_value);
                    }
                    $l_properties['type'] = $l_value;
                } elseif ($l_key === 'isys_obj_type_group__id' || $l_key === 'isys_obj_type__isys_obj_type_group__id') {

                    /* Convert Constant in ID */
                    if (!is_numeric($l_value) && is_string($l_value) && defined($l_value)) {
                        $l_value = constant($l_value);
                    }
                    $l_properties['type_group'] = $l_value;
                } elseif ($l_key === 'isys_obj__title' || $l_key === 'isys_cats_person_list__title') {
                    $l_properties['title'] = $l_value;
                } elseif ($l_key === 'username') {
                    $l_properties['username'] = $l_value;
                } elseif ($l_key === 'isys_obj_type__title') {
                    $l_properties['type_title'] = $l_value;
                } elseif ($l_key === 'isys_obj__sysid') {
                    $l_properties['sysid'] = $l_value;
                } elseif ($l_key === 'isys_cats_person_list__first_name') {
                    $l_properties['first_name'] = $l_value;
                } elseif ($l_key === 'isys_cats_person_list__last_name') {
                    $l_properties['last_name'] = $l_value;
                } elseif ($l_key === 'isys_cats_person_list__mail_address') {
                    $l_properties['email'] = $l_value;
                } else {
                    // Assign all short cuts ('id', 'title',...):
                    $l_properties[$l_key] = $l_value;
                } // if key
            } // foreach property
        } // if properties given

        if (array_key_exists('email', $l_properties)) {
            $l_selections = ', isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address';
        }

        // Base query:
        $l_query = 'SELECT isys_obj.*, ' . 'isys_obj_type__title, isys_obj_type_group__title, isys_cmdb_status__title, ' .
            '(SELECT isys_catg_image_list__image_link FROM isys_catg_image_list WHERE isys_obj__id = isys_catg_image_list__isys_obj__id) AS isys_catg_image_list__image_link ' .
            $l_selections . ' ' . 'FROM isys_obj ' . 'INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id ' .
            'INNER JOIN isys_cmdb_status ON isys_obj__isys_cmdb_status__id = isys_cmdb_status__id ' .
            'INNER JOIN isys_obj_type_group ON isys_obj_type__isys_obj_type_group__id = isys_obj_type_group__id ';

        // Joins:

        if (array_key_exists('first_name', $l_properties) || array_key_exists('last_name', $l_properties) || array_key_exists('username', $l_properties)) {
            $l_query .= 'INNER JOIN isys_cats_person_list ON isys_cats_person_list__isys_obj__id = isys_obj__id ';
        } // if person

        if (array_key_exists('email', $l_properties)) {
            $l_query .= 'LEFT JOIN isys_catg_mail_addresses_list ON isys_obj__id = isys_catg_mail_addresses_list__isys_obj__id ' .
                'AND isys_catg_mail_addresses_list__primary = 1 ';
        }

        // Conditions:

        $l_query .= 'WHERE TRUE';

        if (isset($l_properties['ids'])) {
            if (is_array($l_properties['ids']) && count($l_properties['ids'])) {
                $l_ids = [];
                foreach ($l_properties['ids'] as $l_id) {
                    $l_ids[] = 'isys_obj__id = ' . $this->convert_sql_id($l_id);
                }
                $l_query .= ' AND (' . implode(' OR ', $l_ids) . ')';
            }
        }

        if (array_key_exists('title', $l_properties)) {
            $l_query .= ' AND isys_obj__title LIKE ' . $this->convert_sql_text($l_properties['title']);
        }

        if (array_key_exists('username', $l_properties)) {
            $l_query .= ' AND isys_cats_person_list__title LIKE ' . $this->convert_sql_text($l_properties['username']);
        }

        if (array_key_exists('sysid', $l_properties)) {
            $l_query .= ' AND isys_obj__sysid = ' . $this->convert_sql_text($l_properties['sysid']);
        }

        if (array_key_exists('type', $l_properties)) {
            if (is_array($l_properties['type'])) {
                if (count($l_properties['type'])) {
                    $l_query .= ' AND isys_obj__isys_obj_type__id IN (' . implode(',', $l_properties['type']) . ')';
                }
            } else {
                $l_query .= ' AND isys_obj__isys_obj_type__id = ' . $this->convert_sql_id($l_properties['type']);
            }
        }

        if (array_key_exists('exclude_type', $p_properties)) {
            if (is_array($p_properties['exclude_type'])) {
                if (count($p_properties['exclude_type'])) {
                    $l_query .= ' AND isys_obj__isys_obj_type__id NOT IN (';

                    foreach ($p_properties['exclude_type'] as $l_type) {
                        if (defined($l_type)) {
                            $l_type = constant($l_type);
                        }
                        $l_query .= $this->convert_sql_id($l_type) . ',';
                    }
                    $l_query = rtrim($l_query, ',') . ')';
                }
            } else {
                if (!is_numeric($p_properties['exclude_type']) && defined($p_properties['exclude_type'])) {
                    $l_properties['exclude_type'] = constant($p_properties['exclude_type']);
                }
                if ($p_properties['exclude_type'] > 0) {
                    $l_query .= ' AND isys_obj__isys_obj_type__id != ' . $this->convert_sql_id($p_properties['exclude_type']);
                }
            }
        }

        if (array_key_exists('type_group', $l_properties)) {
            if (!is_numeric($l_properties["type_group"]) && is_string($l_properties["type_group"]) && defined($l_properties["type_group"])) {
                $l_properties['type_group'] = constant($l_properties['type_group']);
            }

            if (is_numeric($l_properties['type_group'])) {
                $l_query .= ' AND isys_obj_type_group__id = ' . $this->convert_sql_id($l_properties['type_group']);
            }
        }

        if (array_key_exists('type_title', $l_properties)) {
            $l_query .= ' AND isys_obj_type__title LIKE ' . $this->convert_sql_text($l_properties['type_title']);
        }

        if (array_key_exists('first_name', $l_properties)) {
            $l_query .= ' AND isys_cats_person_list__first_name LIKE ' . $this->convert_sql_text($l_properties['first_name']);
        }

        if (array_key_exists('last_name', $l_properties)) {
            $l_query .= ' AND isys_cats_person_list__last_name LIKE ' . $this->convert_sql_text($l_properties['last_name']);
        }

        if (array_key_exists('email', $l_properties)) {
            $l_query .= ' AND isys_catg_mail_addresses_list__title LIKE ' . $this->convert_sql_text($l_properties['email']);
        }

        if (array_key_exists('changed_by', $l_properties)) {
            $l_query .= ' AND isys_obj__updated_by LIKE ' . $this->convert_sql_text($l_properties['changed_by']);
        }

        if (array_key_exists('created_by', $l_properties)) {
            $l_query .= ' AND isys_obj__created_by LIKE ' . $this->convert_sql_text($l_properties['created_by']);
        }

        if (array_key_exists('changed_after', $l_properties)) {
            $l_query .= ' AND isys_obj__updated > ' . $this->convert_sql_datetime($l_properties['changed_after']);
        }

        if (array_key_exists('created_after', $l_properties)) {
            $l_query .= ' AND isys_obj__created > ' . $this->convert_sql_datetime($l_properties['created_after']);
        }

        if (array_key_exists('status', $l_properties)) {
            $l_query .= ' AND isys_obj__status = ' . $this->convert_sql_id($l_properties['status']);
        }

        // Limitation, sort, ordering:

        if (isset($p_order_by)) {
            assert(is_string($p_order_by));
            $l_order_by = null;
            switch ($p_order_by) {
                case 'changed':
                case 'isys_obj__updated':
                case 'updated':
                    $l_order_by = 'isys_obj__updated';
                    break;
                case 'isys_obj_type__id':
                case 'isys_obj__isys_obj_type__id':
                case 'type':
                    $l_order_by = 'isys_obj_type__id';
                    break;
                case 'isys_obj__title':
                case 'title':
                    $l_order_by = 'isys_obj__title';
                    break;
                case 'isys_obj_type__title':
                case 'type_title':
                    $l_order_by = 'isys_obj_type__title';
                    break;
                case 'isys_obj__sysid':
                case 'sysid':
                    $l_order_by = 'isys_obj__sysid';
                    break;
                case 'isys_cats_person_list__first_name':
                case 'first_name':
                    $l_order_by = 'isys_cats_person_list__first_name';
                    break;
                case 'isys_cats_person_list__last_name':
                case 'last_name':
                    $l_order_by = 'isys_cats_person_list__last_name';
                    break;
                case 'isys_cats_person_list__mail_address':
                case 'email':
                    $l_order_by = 'isys_catg_mail_addresses_list__title';
                    break;
                case 'isys_obj__id':
                case 'id':
                default:
                    $l_order_by = 'isys_obj__id';
                    break;
            }

            $l_query .= ' ORDER BY ' . $l_order_by;

            if (isset($p_sort)) {
                $p_sort = strtoupper($p_sort);
                if ($p_sort === 'ASC' || $p_sort === 'DESC') {
                    $l_query .= ' ' . $p_sort;
                }
            }
        }

        if (isset($p_limit)) {
            $l_raw_limit = explode(',', $p_limit);
            $l_limit = [];
            foreach ($l_raw_limit as $l_value) {
                $l_limit[] = trim($l_value);
            }
            $l_query .= ' LIMIT ' . implode(', ', $l_limit);
        }

        $l_query .= ';';

        // Retrieval:
        return $this->retrieve($l_query);
    }

    /**
     * Retrieves the status of any object.
     *
     * @param   integer $p_obj_id
     *
     * @return  mixed
     */
    public function obj_get_status($p_obj_id)
    {
        $l_return = $this->retrieve('SELECT isys_obj__status FROM isys_obj WHERE isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';')
            ->get_row();

        return (is_array($l_return) ? $l_return['isys_obj__status'] : null);
    }

    /**
     * @param $p_cat_type
     * @param $p_cat_id
     *
     * @return mixed
     */
    public function gui_get_source_table_by_category($p_cat_type, $p_cat_id)
    {
        $l_catinfo = null;
        $l_catfield = null;

        switch ($p_cat_type) {
            case C__CMDB__CATEGORY__TYPE_GLOBAL:
                $l_catinfo = $this->gui_get_info_by_catg_id($p_cat_id);
                $l_catfield = "isysgui_catg__source_table";
                break;
            case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                $l_catinfo = $this->gui_get_info_by_cats_id($p_cat_id);
                $l_catfield = "isysgui_cats__source_table";
                break;
            case C__CMDB__CATEGORY__TYPE_CUSTOM:
                $l_catinfo = $this->gui_get_info_by_catg_custom_id($p_cat_id);
                $l_catfield = "isysgui_catg_custom__source_table";
                break;
        }

        if ($l_catinfo != null && !isset($this->m_cache[__METHOD__][$p_cat_type][$p_cat_id])) {
            if ($l_catinfo->count()) {
                $l_catdata = $l_catinfo->get_row();
                $l_tbl_src = $l_catdata[$l_catfield];

                if ($p_cat_type == C__CMDB__CATEGORY__TYPE_SPECIFIC || $p_cat_type == C__CMDB__CATEGORY__TYPE_CUSTOM) {
                    $l_tbl_src = str_replace("_list", "", $l_tbl_src);
                }

                $this->m_cache[__METHOD__][$p_cat_type][$p_cat_id] = $l_tbl_src;
            } else {
                $this->m_cache[__METHOD__][$p_cat_type][$p_cat_id] = '';
            }
        }

        return $this->m_cache[__METHOD__][$p_cat_type][$p_cat_id];
    }

    /**
     * @param $p_cat_type
     * @param $p_cat_id
     *
     * @return bool
     */
    public function gui_is_multivalued_by_category($p_cat_type, $p_cat_id)
    {
        $l_catinfo = $this->gui_get_info_by_category($p_cat_type, $p_cat_id);
        $l_catdata = $l_catinfo->get_row();

        switch ($p_cat_type) {
            case C__CMDB__CATEGORY__TYPE_GLOBAL:
                return !empty($l_catdata["isysgui_catg__list_multi_value"]);
            case C__CMDB__CATEGORY__TYPE_CUSTOM:
                return !empty($l_catdata["isysgui_catg_custom__list_multi_value"]);
            case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                return !empty($l_catdata["isysgui_cats__list_multi_value"]);
        }

        return false;
    }

    /**
     * @param $p_cat_type
     * @param $p_cat_id
     *
     * @return isys_component_dao_result|null
     */
    public function gui_get_info_by_category($p_cat_type, $p_cat_id)
    {
        switch ($p_cat_type) {
            case C__CMDB__CATEGORY__TYPE_GLOBAL:
                return $this->gui_get_info_by_catg_id($p_cat_id);
            case C__CMDB__CATEGORY__TYPE_CUSTOM:
                return $this->gui_get_info_by_catg_custom_id($p_cat_id);
            case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                return $this->gui_get_info_by_cats_id($p_cat_id);
        }

        return null;
    }

    /**
     * Returns a result set with information about the requested specific category.
     *
     * @param   integer $p_cats_id
     *
     * @return  isys_component_dao_result
     */
    public function gui_get_info_by_cats_id($p_cats_id)
    {
        $l_sql = 'SELECT * FROM isysgui_cats JOIN isys_tree_group ON isys_tree_group__id = 1 WHERE isysgui_cats__id = ' . $this->convert_sql_id($p_cats_id) . ';';

        if (!isset($this->m_cache[__METHOD__][$p_cats_id])) {
            $this->m_cache[__METHOD__][$p_cats_id] = $this->retrieve($l_sql);
        } else {
            $this->m_cache[__METHOD__][$p_cats_id]->reset_pointer();
        }

        return $this->m_cache[__METHOD__][$p_cats_id];
    }

    /**
     * Returns a result set with information about the requested global category.
     *
     * @param   integer $p_catg_id
     *
     * @return  isys_component_dao_result
     */
    public function gui_get_info_by_catg_id($p_catg_id)
    {
        $l_sql = 'SELECT * FROM isysgui_catg LEFT JOIN isys_tree_group ON isysgui_catg__isys_tree_group__id = isys_tree_group__id WHERE isysgui_catg__id = ' .
            $this->convert_sql_id($p_catg_id) . ';';

        if (!isset($this->m_cache[__METHOD__][$p_catg_id])) {
            $this->m_cache[__METHOD__][$p_catg_id] = $this->retrieve($l_sql);
        } else {
            $this->m_cache[__METHOD__][$p_catg_id]->reset_pointer();
        }

        return $this->m_cache[__METHOD__][$p_catg_id];
    }

    /**
     * Returns a result set with information about the requested specific category.
     *
     * @param   integer $p_catg_custom_id
     *
     * @return  isys_component_dao_result
     */
    public function gui_get_info_by_catg_custom_id($p_catg_custom_id)
    {
        $l_sql = 'SELECT * FROM isysgui_catg_custom WHERE isysgui_catg_custom__id = ' . $this->convert_sql_id($p_catg_custom_id) . ';';

        if (!isset($this->m_cache[__METHOD__][$p_catg_custom_id])) {
            $this->m_cache[__METHOD__][$p_catg_custom_id] = $this->retrieve($l_sql);
        } else {
            $this->m_cache[__METHOD__][$p_catg_custom_id]->reset_pointer();
        }

        return $this->m_cache[__METHOD__][$p_catg_custom_id];
    }

    /**
     * Returns the tablename with meta-information for the specified category type.
     *
     * @param   integer $p_cat_type
     * @param   string  &$p_cat_table
     * @param   string  &$p_cat_get
     *
     * @return  string
     */
    public function gui_get_metadata_by_category_type($p_cat_type, &$p_cat_table, &$p_cat_get)
    {
        switch ($p_cat_type) {
            case C__CMDB__CATEGORY__TYPE_GLOBAL:
                $p_cat_table = "isysgui_catg";
                $p_cat_get = C__CMDB__GET__CATG;

                return true;

            case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                $p_cat_table = "isysgui_cats";
                $p_cat_get = C__CMDB__GET__CATS;

                return true;
        }

        return false;
    }

    /**
     *
     * @param   integer $p_objtype_id
     *
     * @return  isys_component_dao_result
     */
    public function gui_get_cats_by_objtype_id($p_objtype_id)
    {
        $l_q = 'SELECT * FROM isys_obj_type AS T_TYPE
			INNER JOIN isysgui_cats AS T_CATS ON T_TYPE.isys_obj_type__isysgui_cats__id = T_CATS.isysgui_cats__id
			WHERE T_TYPE.isys_obj_type__id = ' . $p_objtype_id . ';';

        return $this->retrieve($l_q);
    }

    /**
     *
     * @param   integer $p_objtype_id
     *
     * @return  isys_component_dao_result
     */
    public function gui_get_catg_by_objtype_id($p_objtype_id)
    {
        $l_q = 'SELECT * FROM isys_obj_type_2_isysgui_catg AS T_CONN
			LEFT JOIN isys_obj_type AS T_TYPE ON T_TYPE.isys_obj_type__id = T_CONN.isys_obj_type_2_isysgui_catg__isys_obj_type__id
			LEFT JOIN isysgui_catg AS T_CATG ON T_CATG.isysgui_catg__id = T_CONN.isys_obj_type_2_isysgui_catg__isysgui_catg__id
			WHERE T_TYPE.isys_obj_type__id = ' . $this->convert_sql_id($p_objtype_id) . ';';

        return $this->retrieve($l_q);
    }

    /**
     * Retrieves all custom categories, assigned to a certain object type.
     *
     * @param   integer $p_objtype_id
     *
     * @return  isys_component_dao_result
     */
    public function gui_get_catg_custom_by_objtype_id($p_objtype_id)
    {
        $l_q = 'SELECT * FROM isys_obj_type_2_isysgui_catg_custom AS T_CUSTOM
			LEFT JOIN isys_obj_type AS T_TYPE ON T_TYPE.isys_obj_type__id = T_CUSTOM.isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id
			LEFT JOIN isysgui_catg_custom AS T_CATG ON T_CATG.isysgui_catg_custom__id = T_CUSTOM.isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id
			WHERE T_TYPE.isys_obj_type__id = ' . $this->convert_sql_id($p_objtype_id) . ';';

        return $this->retrieve($l_q);
    }

    /**
     * Evaluates common category information. Returns an array with
     * following structure:
     *
     * array (
     *  type    => Category type (global, specific or dynamic)
     *  id        => ID of the category (isysgui_cat* - table)
     *  tree    => View constant for the tree associated with the
     *             category tree
     *  get        => GET-Parameter of the current category
     *  string    => Token for the category ("cats", "catg" or "catd")
     *  table   => Source table
     * )
     *
     * If the current category can't be evaluated, null is returned.
     * You are not forced to pass the GET-Array to this function, it can
     * evaluate it on its own - based on the current module request object.
     *
     * @param array $p_gets
     *
     * @return array
     */
    public function nav_get_current_category_data($p_gets = null)
    {
        if ($p_gets && is_array($p_gets)) {
            /* Use passed GET-array */
            $l_gets = $p_gets;
        } else {
            /* Use GET-Array from current module request */
            $l_gets = isys_module_request::get_instance()
                ->get_gets();
        }

        $l_gets = Purify::castIntValues($l_gets);

        /* Initialize destination variables */
        $l_cattype = null;
        $l_catconst = null;
        $l_catview = null;
        $l_cattree = null;
        $l_catget = null;
        $l_catpref = null;

        if (isset($l_gets[C__CMDB__GET__CATG])) {
            /* If global category is set ... */
            $l_cattype = C__CMDB__CATEGORY__TYPE_GLOBAL;
            $l_catconst = $l_gets[C__CMDB__GET__CATG];
            $l_catview = C__CMDB__VIEW__CATEGORY;
            $l_cattree = C__CMDB__VIEW__TREE_OBJECT;
            $l_catget = C__CMDB__GET__CATG;
            $l_catpref = "catg";

            if ($l_catconst == defined_or_default('C__CATG__CUSTOM_FIELDS')) {
                $l_cattype = C__CMDB__CATEGORY__TYPE_CUSTOM;
                $l_catpref = "catg_custom";
                $l_catconst = $l_gets[C__CMDB__GET__CATG_CUSTOM];
                $l_catget = C__CMDB__GET__CATG_CUSTOM;
            }
        } elseif (isset($l_gets[C__CMDB__GET__CATS])) {
            /* If specific category is set ... */
            $l_cattype = C__CMDB__CATEGORY__TYPE_SPECIFIC;
            $l_catconst = $l_gets[C__CMDB__GET__CATS];
            $l_catview = C__CMDB__VIEW__CATEGORY;
            $l_cattree = C__CMDB__VIEW__TREE_OBJECT;
            $l_catget = C__CMDB__GET__CATS;
            $l_catpref = "cats";
        }

        if ($l_cattype !== null) {

            /* Everything has been well done, so determine the source
               table */
            $l_cattbl = $this->gui_get_source_table_by_category($l_cattype, $l_catconst);

            /**
             * Ensure table information is postfixed with _list
             */
            $l_cattbl_list = $l_cattbl;
            if (!(stripos(strrev($l_cattbl), 'tsil_') === 0) && !(stripos($l_cattbl, '_2_isys_obj') > 0)) {
                $l_cattbl_list = $l_cattbl . '_list';
            }

            if ($l_cattbl) {
                /* Good - now build and return array. */
                return [
                    "type"       => $l_cattype,
                    "id"         => $l_catconst,
                    "view"       => $l_catview,
                    "tree"       => $l_cattree,
                    "get"        => $l_catget,
                    "string"     => $l_catpref,
                    "table"      => $l_cattbl,
                    "table_list" => $l_cattbl_list
                ];
            }
        }

        return null;
    }

    /**
     * Set internal cmdb status.
     *
     * @param  array $p_status
     */
    public function set_cmdb_status($p_status)
    {
        $this->m_cmdb_status = $p_status;
    }

    /**
     * Get CMDB Status.
     *
     * @return  array
     */
    public function get_cmdb_status()
    {
        return $this->m_cmdb_status;
    }

    /**
     * Set internal prefix for cmdb status.
     *
     * @param  string $p_prefix
     */
    public function set_cmdb_status_prefix($p_prefix)
    {
        $this->m_cmdb_status_prefix = $p_prefix;
    }

    /**
     * get prefix for cmdb status.
     *
     * @return  string
     */
    public function get_cmdb_status_prefix()
    {
        return $this->m_cmdb_status_prefix;
    }
}
