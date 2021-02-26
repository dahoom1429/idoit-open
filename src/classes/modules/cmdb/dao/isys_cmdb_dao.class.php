<?php

use idoit\Context\Context;
use idoit\Model\QuickInfo;

/**
 * i-doit
 *
 * CMDB DAO Framework
 *
 * @author      i-doit-team <i-doit-team@i-doit.org>
 * @package     i-doit
 * @subpackage  CMDB_Low-Level_API
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao extends isys_cmdb_dao_nexgen
{
    /**
     * Array to mark properties as "changed".
     *
     * @var  array
     */
    protected static $m_changed_props = [];

    /**
     * Cache last sysid after creating new object from function insert_new_obj
     * Helpful after creating a new object in imports
     *
     * @var null
     */
    protected static $m_last_sysid = null;

    /**
     * Cache array for saving "obj-ID -> obj-title".
     *
     * @var  array
     */
    protected static $m_obj_names = [];

    /**
     * Cache array for saving "objtype-ID -> objtype-title".
     *
     * @var  array
     */
    protected static $m_obj_types = [];

    /**
     * Cache which contains category infos
     *
     * @var array
     */
    private static $m_category_cache = [];

    /**
     * Member variable which contains custom daos
     *
     * @var array
     */
    private static $m_custom_daos = [];

    /**
     * Gets the last sysid which has been created by the function insert_new_obj
     */
    public static function get_last_sysid()
    {
        return self::$m_last_sysid;
    }

    /**
     * Mark a certain property as changed.
     *
     * @static
     *
     * @param   string $p_cat
     * @param   string $p_prop
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function set_changed_prop($p_cat, $p_prop)
    {
        self::$m_changed_props[$p_cat][$p_prop] = true;
    }

    /**
     * Retrieve all changed properties.
     *
     * @static
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function get_changed_props()
    {
        return self::$m_changed_props;
    }

    /**
     * Check if a given property is marked as "changed".
     *
     * @static
     *
     * @param   string $p_cat  Use the category constant, because the ID's are not unique (because global and specific).
     * @param   string $p_prop Use the property key here.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function is_changed_prop($p_cat, $p_prop)
    {
        return (isset(self::$m_changed_props[$p_cat]) && isset(self::$m_changed_props[$p_cat][$p_prop]) && self::$m_changed_props[$p_cat][$p_prop]);
    }

    /**
     * Use this method to reset the categories and properties, marked as "changed".
     *
     * @static
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function reset_changed_props()
    {
        self::$m_changed_props = [];
    }

    /**
     * Method for setting an objects status.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_status
     *
     * @return  boolean
     */
    public function set_object_status($p_obj_id, $p_status)
    {
        isys_component_signalcollection::get_instance()
            ->emit('mod.cmdb.beforeObjectStatusChange', $p_obj_id, $p_status);

        $l_update = 'UPDATE isys_obj
			SET isys_obj__status = ' . $this->convert_sql_id($p_status) . '
			WHERE isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';';

        return ($this->update($l_update) && $this->apply_update());
    }

    /**
     * Method for setting an objects CMDB status.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_cmdb_status
     *
     * @return  boolean
     */
    public function set_object_cmdb_status($p_obj_id, $p_cmdb_status)
    {
        $l_update = 'UPDATE isys_obj
			SET isys_obj__isys_cmdb_status__id = ' . $this->convert_sql_id($p_cmdb_status) . '
			WHERE isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';';

        isys_cmdb_dao_status::instance($this->m_db)
            ->add_change($p_obj_id, $p_cmdb_status);

        return ($this->update($l_update) && $this->apply_update());
    }

    /**
     * Set custom field id for request tracker.
     *
     * @param int $p_objID
     * @param int $p_cfID
     *
     * @return bool
     * @throws isys_exception_dao
     * @deprecated Will be removed in i-doit 1.14
     */
    public function set_cf_id($p_objID, $p_cfID)
    {
        $l_update = 'UPDATE isys_obj
			SET isys_obj__rt_cf__id = ' . $this->convert_sql_id($p_cfID) . '
			WHERE isys_obj__id = ' . $this->convert_sql_id($p_objID) . ';';

        return ($this->update($l_update) && $this->apply_update());
    }

    /**
     * Get custom field id for request tracker.
     *
     * @param int $p_objID
     *
     * @return int
     * @throws isys_exception_database
     * @deprecated Will be removed in i-doit 1.14
     */
    public function get_cf_id($p_objID)
    {
        return $this
            ->retrieve('SELECT isys_obj__rt_cf__id FROM isys_obj WHERE isys_obj__id = ' . $this->convert_sql_id($p_objID) . ';')
            ->get_row_value('isys_obj__rt_cf__id');
    }

    /**
     * Get custom field id for request tracker.
     *
     * @param int $p_id
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     * @deprecated Will be removed in i-doit 1.14
     */
    public function get_obj_id_by_ticket_id($p_id)
    {
        $sql = 'SELECT isys_catg_tickets_list__isys_obj__id 
            FROM isys_catg_tickets_list 
            WHERE isys_catg_tickets_list__rt_ticket__id = ' . $this->convert_sql_id($p_id) . ';';

        return $this->retrieve($sql);
    }

    /**
     * Change status of a category.
     *
     * @param int $p_catID
     * @param int $p_status
     *
     * @return bool
     * @throws isys_exception_dao
     * @deprecated Will be removed in i-doit 1.14
     */
    public function set_catg_status($p_catID, $p_status)
    {
        $l_query = 'UPDATE isysgui_catg 
            SET isysgui_catg__status = ' . $this->convert_sql_int($p_status) . ' 
            WHERE isysgui_catg__id = ' . $this->convert_sql_id($p_catID) . ';';

        return ($this->update($l_query) && $this->apply_update());
    }

    /**
     * Deletes a category out of the isysgui storage.
     *
     * @param int $p_catID
     *
     * @return bool
     * @throws isys_exception_dao
     * @deprecated Will be removed in i-doit 1.14
     */
    public function delete_catg($p_catID)
    {
        $l_query = 'DELETE FROM isysgui_catg WHERE isysgui_catg__id = ' . $this->convert_sql_id($p_catID) . ';';

        return ($this->update($l_query) && $this->apply_update());
    }

    /**
     * Return subcategories of the parent category id $p_id.
     *
     * @param   integer $p_id
     *
     * @param bool      $p_complete
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function catg_get_subcats($p_id, $p_complete = false)
    {
        return $this->retrieve('SELECT ' . ($p_complete ? '*' : 'isysgui_catg__id') . ' FROM isysgui_catg WHERE isysgui_catg__parent = ' . $this->convert_sql_id($p_id) . ';');
    }

    /**
     * Clear existing data
     * The category_table must be the distributor-connector
     *
     * @param int    $p_object_id
     * @param string $p_category_table
     *
     * @param bool   $p_has_relation
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @todo complete this method
     */
    public function clear_data($p_object_id, $p_category_table, $p_has_relation = true)
    {
        if ($p_object_id > 0) {
            $objectId = $this->convert_sql_id($p_object_id);

            // Tables which have the field '__isys_catg_connector_list__id'
            $l_connector_tables = [
                'isys_catg_port_list'           => true,
                'isys_catg_ui_list'             => true,
                'isys_catg_fc_port_list'        => true,
                'isys_catg_pc_list'             => true,
                'isys_catg_power_supplier_list' => true,
            ];

            $l_category_table_rev = strrev($p_category_table);
            $l_category_table_for_relation = $l_category_table = (strpos($l_category_table_rev, 'tsil_') === 0) ? $p_category_table : $p_category_table . '_list';
            $l_connector = false;
            $l_delete_connectors = null;

            // Delete connector if field in table exists
            //if($this->retrieve("SHOW COLUMNS FROM ".$l_category_table." LIKE ".$this->convert_sql_text($l_connector_field))->num_rows() > 0)
            if (isset($l_connector_tables[$l_category_table])) {
                $l_connector_field = $l_category_table . '__isys_catg_connector_list__id';
                $l_delete_connectors = "DELETE isys_catg_connector_list 
                    FROM isys_catg_connector_list 
                    INNER JOIN {$l_category_table} ON {$l_connector_field} = isys_catg_connector_list__id 
                    WHERE {$l_category_table}__isys_obj__id = {$objectId};";
                $l_connector = true;
                $l_category_table_for_relation = 'isys_catg_connector_list';
            }

            // Delete relation
            if ($p_has_relation) {
                /**
                 * @var $l_relation_dao isys_cmdb_dao_category_g_relation
                 */
                $l_relation_dao = isys_cmdb_dao_category_g_relation::factory($this->get_database_component());
                $l_res = $this->retrieve("SELECT * 
                    FROM {$l_category_table_for_relation}
                    WHERE {$l_category_table_for_relation}__isys_obj__id = {$objectId};");

                while ($l_data = $l_res->get_row()) {
                    $key = $l_category_table_for_relation . '__isys_catg_relation_list__id';

                    if (isset($l_data[$key])) {
                        $l_relation_dao->delete_relation($l_data[$key]);
                    } else {
                        continue;
                    }
                }
                $l_res->free_result();
            }

            if ($l_connector === true && $l_delete_connectors !== null) {
                $this->update($l_delete_connectors);
            }

            // Delete Ip
            if ($l_category_table === 'isys_catg_ip_list') {
                $l_delete_ip_ids = '';
                $l_res = $this->retrieve("SELECT {$l_category_table}__isys_cats_net_ip_addresses_list__id 
                    FROM {$l_category_table} 
                    WHERE {$l_category_table}__isys_obj__id = {$objectId};");

                $l_delete_ip = false;
                while ($l_data = $l_res->get_row()) {
                    $key = $l_category_table . '__isys_cats_net_ip_addresses_list__id';

                    if (isset($l_data[$key]) && $l_data[$key] > 0) {
                        $l_delete_ip_ids .= $l_data[$key] . ",";
                        $l_delete_ip = true;
                    }
                }
                $l_res->free_result();
                if ($l_delete_ip) {
                    $l_delete_ip_ids = rtrim($l_delete_ip_ids, ",");
                    $l_delete = "DELETE FROM isys_cats_net_ip_addresses_list WHERE isys_cats_net_ip_addresses_list__id IN (" . $l_delete_ip_ids . ")";
                    $this->update($l_delete);
                }
            }

            // Delete layer2 assignments to ports
            if ($l_category_table === 'isys_catg_port_list') {
                $l_delete_layer2_assignment = "DELETE isys_cats_layer2_net_assigned_ports_list FROM isys_cats_layer2_net_assigned_ports_list
					INNER JOIN isys_catg_port_list AS p ON p.isys_catg_port_list__id = isys_cats_layer2_net_assigned_ports_list.isys_catg_port_list__id
					WHERE p.isys_catg_port_list__isys_obj__id = {$objectId};";
                $this->update($l_delete_layer2_assignment);
            }

            $l_sql = "DELETE FROM {$l_category_table} WHERE ({$l_category_table}__isys_obj__id = {$objectId})";

            return $this->update($l_sql) && $this->apply_update();
        }

        return true;
    }

    /**
     * Generic category delete
     *
     * Example ->delete_entry(1, "isys_catg_file_list");
     *
     * @param int|array $p_cat_id
     * @param string    $p_category_table
     *
     * @return bool
     * @throws isys_exception_database
     */
    public function delete_entry($p_cat_id, $p_category_table)
    {
        $l_delete_ip = false;
        $l_delete_relation = false;
        $l_delete_connection = false;
        $l_relation_field = '';
        $l_connection_field = '';
        $l_ip_field = '';

        if ($p_category_table === 'isys_catg_ip_list') {
            $l_ip_field = $p_category_table . '__isys_cats_net_ip_addresses_list__id ';
            $l_delete_ip = true;
        }

        $l_sql = "SHOW FIELDS FROM " . $p_category_table . " WHERE FIELD LIKE '%isys_catg_relation_list__id' OR FIELD LIKE '%isys_connection__id'";
        $l_res = $this->retrieve($l_sql);
        if ($l_res->num_rows() > 0) {
            // HAS FIELDS isys_connection__id OR isys_catg_relation_list__id
            while ($l_row = $l_res->get_row()) {
                if (strstr($l_row['Field'], 'isys_catg_relation_list__id')) {
                    $l_relation_field = $l_row['Field'];
                    $l_delete_relation = true;
                }
                if (strstr($l_row['Field'], 'isys_connection__id')) {
                    $l_connection_field = $l_row['Field'];
                    $l_delete_connection = true;
                }
            }
        }
        $l_res->free_result();

        if (is_array($p_cat_id)) {
            $l_delete_condition = $p_category_table . '__id ' . $this->prepare_in_condition($p_cat_id);
        } else {
            $l_delete_condition = $p_category_table . '__id = ' . $this->convert_sql_id($p_cat_id);
        }

        if ($l_delete_relation || $l_delete_ip || $l_delete_connection) {
            $l_sql = 'SELECT * FROM ' . $p_category_table . ' AS main ';

            if ($l_delete_relation && $l_relation_field != '') {
                $l_sql .= ' LEFT JOIN isys_catg_relation_list AS rel ON rel.isys_catg_relation_list__id = main.' . $l_relation_field;
            }
            if ($l_delete_connection && $l_connection_field != '') {
                $l_sql .= ' LEFT JOIN isys_connection AS con ON con.isys_connection__id = main.' . $l_connection_field;
            }
            if ($l_delete_ip) {
                $l_sql .= ' LEFT JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = ' . $l_ip_field;
            }

            if (is_array($p_cat_id)) {
                $l_sql .= ' WHERE main.' . $p_category_table . '__id ' . $this->prepare_in_condition($p_cat_id) . ';';
                $l_res = $this->retrieve($l_sql);

                while ($l_row = $l_res->get_row()) {
                    if ($l_delete_relation && $l_row['isys_catg_relation_list__id'] > 0) {
                        $this->delete_object($l_row['isys_catg_relation_list__isys_obj__id']);
                    }

                    if ($l_delete_connection && $l_row['isys_connection__id'] > 0) {
                        $l_delete = 'DELETE FROM isys_connection
                            WHERE isys_connection__id = ' . $this->convert_sql_id($l_row['isys_connection__id']);
                        $this->update($l_delete);
                    }

                    if ($l_delete_ip && $l_row['isys_cats_net_ip_addresses_list__id'] > 0) {
                        $l_delete = 'DELETE FROM isys_cats_net_ip_addresses_list
                            WHERE isys_cats_net_ip_addresses_list__id = ' . $this->convert_sql_id($l_row['isys_cats_net_ip_addresses_list__id']);
                        $this->update($l_delete);
                    }
                }
                $l_res->free_result();
            } else {
                $l_sql .= ' WHERE main.' . $p_category_table . '__id = ' . $this->convert_sql_id($p_cat_id) . ';';
                $l_res = $this->retrieve($l_sql);
                $l_row = $l_res->get_row();

                // Free memory
                $l_res->free_result();
                unset($l_res);

                if ($l_delete_relation && $l_row['isys_catg_relation_list__id'] > 0) {
                    $this->delete_object($l_row['isys_catg_relation_list__isys_obj__id']);
                }

                if ($l_delete_connection && $l_row['isys_connection__id'] > 0) {
                    $l_delete = 'DELETE FROM isys_connection
                        WHERE isys_connection__id = ' . $this->convert_sql_id($l_row['isys_connection__id']);
                    $this->update($l_delete);
                }

                if ($l_delete_ip && $l_row['isys_cats_net_ip_addresses_list__id'] > 0) {
                    $l_delete = 'DELETE FROM isys_cats_net_ip_addresses_list
                        WHERE isys_cats_net_ip_addresses_list__id = ' . $this->convert_sql_id($l_row['isys_cats_net_ip_addresses_list__id']);
                    $this->update($l_delete);
                }
            }
        }

        $l_sql = "DELETE FROM " . $p_category_table . " " . "WHERE (" . $l_delete_condition . ");";

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * Get categories from isysgui
     *
     * @param string $p_infotable
     * @param int|array    $p_category_id
     * @param string $p_source_table
     * @param string $p_const
     *
     * @param null   $p_parent_id
     * @param null   $p_order
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_isysgui($p_infotable = "isysgui_catg", $p_category_id = null, $p_source_table = null, $p_const = null, $p_parent_id = null, $p_order = null)
    {
        $l_infotable = $p_infotable;

        $l_q = "SELECT " . $l_infotable . "__id, " . $l_infotable . "__title, " . $l_infotable . "__const, " . $l_infotable . "__source_table, " . $l_infotable .
            "__class_name, " . $l_infotable . "__list_multi_value, " . $l_infotable . "__parent " . "FROM " . $l_infotable;

        if ($p_parent_id && $p_infotable === "isysgui_cats") {
            $l_q .= " INNER JOIN isysgui_cats_2_subcategory " . " ON isysgui_cats_2_subcategory__isysgui_cats__id__child = isysgui_cats__id ";
        }

        $l_q .= " WHERE TRUE";

        if ($p_source_table) {
            $l_q .= " AND " . $l_infotable . "__source_table='" . $p_source_table . "'";
        }

        if ($p_parent_id && $p_infotable !== "isysgui_cats") {
            $l_q .= " AND " . $l_infotable . "__parent='" . intval($p_parent_id) . "'";
        } elseif ($p_infotable === "isysgui_cats" && $p_parent_id) {
            $l_q .= " AND isysgui_cats_2_subcategory__isysgui_cats__id__parent='" . intval($p_parent_id) . "'";
        }

        if (is_array($p_category_id)) {
            $l_q .= " AND (";
            $i = 0;

            foreach ($p_category_id as $l_category_id => $l_ok) {
                if ($l_ok) {
                    $l_q .= $l_infotable . "__id='" . intval($l_category_id) . "'";
                    if (++$i < count($p_category_id)) {
                        $l_q .= " OR ";
                    }
                }
            }

            $l_q .= ")";
        } elseif ($p_category_id > 0) {
            $l_q .= " AND " . $l_infotable . "__id='" . $p_category_id . "'";
        }

        if ($p_const) {
            $l_q .= " AND " . $l_infotable . "__const='" . $p_const . "'";
        }

        $l_q .= " AND " . $l_infotable . "__status = 2";

        if (!is_null($p_order)) {
            $l_q .= " ORDER BY " . $p_order;
        }

        return $this->retrieve($l_q);
    }

    /**
     * Method for retrieving object-types.
     *
     * @param   mixed $p_id       May be an array of IDs or a single ID.
     * @param   mixed $p_constant May be an array of constants or a single one.
     * @param   mixed $p_title    May be an array of titles or a single string.
     *
     * @return  array
     * @todo    Use this function, when ever possible!
     */
    public function get_object_type($p_id = null, $p_constant = null, $p_title = null)
    {
        if (empty(self::$m_obj_types)) {
            $this->load_obj_types();
        }

        if ($p_id === null && $p_constant === null && $p_title === null) {
            return self::$m_obj_types;
        }

        // We check for given IDs.
        if (is_numeric($p_id)) {
            return self::$m_obj_types[$p_id];
        }

        if (is_array($p_id)) {
            $l_return = [];

            foreach (self::$m_obj_types as $l_id => $l_objtype) {
                if (in_array($l_id, $p_id, false)) {
                    $l_return[$l_id] = $l_objtype;
                }
            }

            return $l_return;
        }

        // We check for given constants.
        if (is_string($p_constant)) {
            $p_constant = strtoupper($p_constant);
            foreach (self::$m_obj_types as $l_objtype) {
                if ($p_constant === $l_objtype['isys_obj_type__const']) {
                    return $l_objtype;
                }
            }
        } else {
            if (is_array($p_constant)) {
                $l_return = [];

                foreach (self::$m_obj_types as $l_objtype) {
                    if (in_array($l_objtype['isys_obj_type__const'], $p_constant, false)) {
                        $l_return[] = $l_objtype;
                    }
                }

                return $l_return;
            }
        }

        // We check for a given title.
        if (is_string($p_title)) {
            foreach (self::$m_obj_types as $l_objtype) {
                if ($p_title == $l_objtype['isys_obj_type__title'] || $p_title == $l_objtype['LC_isys_obj_type__title']) {
                    return $l_objtype;
                }
            }
        } elseif (is_array($p_title)) {
            $l_return = [];

            foreach (self::$m_obj_types as $l_objtype) {
                if ($p_title === $l_objtype['isys_obj_type__title'] || $p_title === $l_objtype['LC_isys_obj_type__title']) {
                    $l_return[] = $l_objtype;
                }
            }

            return $l_return;
        }

        return [];
    }

    /**
     * Returns a result set for all object types.
     *
     * @deprecated
     *
     * @param   integer $p_obj_type__id
     * @param   string  $p_order_by
     *
     * @return  isys_component_dao_result
     * @see     get_objecttype
     */
    public function get_types($p_obj_type__id = null, $p_order_by = 'isys_obj_type__id')
    {
        $l_sql = 'SELECT * FROM isys_obj_type WHERE TRUE';

        if ($p_obj_type__id !== null) {
            $l_sql .= ' AND isys_obj_type__id = ' . $this->convert_sql_id($p_obj_type__id);
        }

        $l_sql .= ' ORDER BY ' . $p_order_by . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Creates the distrubtion connector entry and returns its id.
     * If obj_id is null, the method takes it from $_GET parameter.
     *
     * @param   string  $p_table
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function create_connector($p_table, $p_obj_id = null)
    {
        if ($p_obj_id === null) {
            $p_obj_id = $_GET[C__CMDB__GET__OBJECT];
        }

        $l_sql = 'INSERT IGNORE INTO ' . $p_table . ' SET ' . $p_table . '__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';';

        if ($this->update($l_sql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        }

        return null;
    }

    /**
     * Fetches data for autotext fields.
     *
     * @param   string $p_query    Search query.
     * @param   string $p_source   Source table.
     * @param   string $p_property Property's name (field in source table).
     *
     * @return  isys_component_dao_result
     */
    public function get_autotext($p_query, $p_source, $p_property)
    {
        $l_table = $this->m_db->escape_string($p_source);
        $l_field = $l_table . '__' . $this->m_db->escape_string($p_property);
        $l_query = 'SELECT `' . $l_table . '__id`, `' . $l_field . '` ' . 'FROM `' . $l_table . '` ' . 'WHERE ' . $l_field . ' LIKE \'%' .
            $this->m_db->escape_string($p_query) . '%\';';

        return $this->retrieve($l_query);
    }

    /**
     * Generally retrieves dialog/dialog+ data
     *
     * @param   string  $p_table_name
     * @param   integer $p_id
     * @param   string  $p_title
     * @param   string  $p_sort
     *
     * @param null      $p_const
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function get_dialog($p_table_name, $p_id = null, $p_title = null, $p_sort = "DESC", $p_const = null)
    {
        if ($p_table_name) {
            $l_sort = 'DESC';
            $l_order_by = '';

            if ($p_sort === 'ASC') {
                $l_sort = 'ASC';
            }

            $l_sql = "SELECT * FROM " . $p_table_name . " WHERE TRUE ";

            if ($p_id !== null) {
                $l_sql .= "AND (" . $p_table_name . "__id = " . $this->convert_sql_id($p_id) . ") ";
                $l_order_by = "ORDER BY " . $p_table_name . "__title " . $l_sort . ";";
            }

            if ($p_title !== null) {
                $l_sql .= "AND (" . $p_table_name . "__title = " . $this->convert_sql_text($p_title) . ") ";
            }

            if ($p_const !== null) {
                $l_sql .= "AND (" . $p_table_name . "__const = " . $this->convert_sql_text($p_const) . ") ";
            }

            return $this->retrieve($l_sql . $l_order_by);
        }

        return $this->retrieve('SELECT 1');
    }

    /**
     * @param        $p_table_name
     * @param null   $p_id
     * @param null   $p_title
     * @param string $p_sort
     * @param null   $p_const
     *
     * @return array
     */
    public function get_dialog_as_array($p_table_name, $p_id = null, $p_title = null, $p_sort = "DESC", $p_const = null)
    {
        $l_dialog = $this->get_dialog($p_table_name, $p_id, $p_title, $p_sort, $p_const);

        $l_return = [];
        while ($l_row = $l_dialog->get_row()) {
            $l_return[$l_row[$p_table_name . '__id']] = $l_row[$p_table_name . '__title'];
        }

        return $l_return;
    }

    /**
     * General dialog data retriever with title selection.
     *
     * @param   string $p_table_name
     * @param   string $p_title
     *
     * @return  isys_component_dao_result
     * @author  Dennis Stuecken
     */
    public function get_dialog_by_title($p_table_name, $p_title)
    {
        return $this->get_dialog($p_table_name, null, $p_title);
    }

    /**
     * @return array
     *
     * @param int $p_const_objType
     */
    public function get_smarty_arr_obj_by_objtype($p_const_objType)
    {
        $l_arr = [];
        $l_daoRes = $this->get_ObjExtendedInfobyObjType($p_const_objType);

        if ($l_daoRes != null) {
            while ($l_rec = $l_daoRes->get_row()) {
                $l_arr[$l_rec['isys_obj__id']] = $l_rec['isys_obj__title'];
            }
        }

        return $l_arr;
    }

    /**
     * Returns a result set with the objects and the specified catg global associated to the specified object-type-ID.
     *
     * @param   integer $p_objTypeID
     *
     * @return  isys_component_dao_result
     */
    public function get_ObjExtendedInfobyObjType($p_objTypeID = null)
    {
        $l_sql = 'SELECT * FROM isys_obj INNER JOIN isys_catg_global_list ON isys_catg_global_list__isys_obj__id = isys_obj__id WHERE TRUE ';

        if ($p_objTypeID != null) {
            $l_sql .= 'AND isys_obj.isys_obj__isys_obj_type__id = ' . $this->convert_sql_id($p_objTypeID) . ' ';
        }

        $l_sql .= 'AND isys_obj.isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Returns a result set with the object type record associated with the specified object ID.
     *
     * @param   integer $p_objid
     *
     * @return  isys_component_dao_result
     */
    public function get_type_by_object_id($p_objid)
    {
        return $this->retrieve('SELECT * FROM isys_obj INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id WHERE isys_obj__id = ' .
            $this->convert_sql_id($p_objid) . ';');
    }

    /**
     * Fetches an object with its type and its type group by its identifier.
     *
     * @param   integer $p_object_id Object identifier
     *
     * @return  isys_component_dao_result Result set
     */
    public function get_type_group_by_object_id($p_object_id)
    {
        $l_sql = 'SELECT * FROM isys_obj
			INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			LEFT JOIN isys_obj_type_group ON isys_obj_type_group__id = isys_obj_type__isys_obj_type_group__id
			WHERE isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Returns the ObjTypeID for a specific object.
     *
     * @param   integer $p_objID
     *
     * @return  integer  objTypeID
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_objTypeID($p_objID)
    {
        return (int)$this->get_type_by_object_id($p_objID)
            ->get_row_value('isys_obj__isys_obj_type__id');
    }

    /**
     * Get the object-types according to a certain global category.
     *
     * @param   array   $p_categories
     * @param   integer $p_obj_group
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_obj_type_by_catg(array $p_categories, $p_obj_group = 0)
    {
        $l_sql = 'SELECT * FROM isys_obj_type AS ot ' . 'LEFT JOIN isys_obj_type_2_isysgui_catg AS ot2catg ' .
            'ON ot.isys_obj_type__id = ot2catg.isys_obj_type_2_isysgui_catg__isys_obj_type__id ' . 'WHERE TRUE ';

        if (count($p_categories) > 0) {
            $l_sql .= 'AND ot2catg.isys_obj_type_2_isysgui_catg__isysgui_catg__id IN (' . implode(', ', $p_categories) . ') ';
        }

        // object_type__object_type_group_id''
        if ($p_obj_group > 0) {
            $l_sql .= 'AND ot.isys_obj_type__isys_obj_type_group__id = ' . $p_obj_group;
        }

        $l_sql .= ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Retrieve the name of an object-type, by giving its ID.
     *
     * @param   integer $p_objID
     *
     * @return  string
     */
    public function get_obj_type_name_by_obj_id($p_objID)
    {
        return $this->get_type_by_object_id($p_objID)
            ->get_row_value('isys_obj_type__title');
    }

    /**
     * Fetches object types from database.
     *
     * @param   mixed $p_id         May be an Object type identifier (integer) or an list of identifiers (array). Defaults to null that includes all object types.
     * @param    bool $p_with_group Determines if the query joins the objtype group or not
     *
     * @param null    $p_status
     *
     * @return isys_component_dao_result Result set
     * @throws isys_exception_database
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    public function get_objtype($p_id = null, $p_with_group = false, $p_status = null)
    {
        $l_query = 'SELECT * FROM isys_obj_type ';

        if ($p_with_group) {
            $l_query .= 'INNER JOIN isys_obj_type_group ON isys_obj_type_group__id = isys_obj_type__isys_obj_type_group__id ';
        }

        $l_query .= 'WHERE TRUE ';

        if ($p_status !== null && is_numeric($p_status)) {
            $l_query .= 'AND isys_obj_type__status = ' . $this->convert_sql_int($p_status) . ' ';
        }

        // Fetch only specific identifier(s):
        if ($p_id !== null) {
            if (is_array($p_id)) {
                $l_query .= 'AND isys_obj_type__id ' . $this->prepare_in_condition($p_id);
            } else {
                $l_query .= 'AND isys_obj_type__id = ' . $this->convert_sql_id($p_id);
            }
        }

        return $this->retrieve($l_query . ';');
    }

    /**
     * Fetches object types from database.
     *
     * @param array  $p_properties (optional) Filter by properties (associative
     *                             array of arrays or strings). Short names as keys may be used. Defaults to
     *                             null, so result won't be filtered. Examples:
     *                             'id' => 1        OR      'id' => 'C__OBJTYPE__SERVER'
     *                             'ids' => array(1, 2, 3)      OR      'ids' => array('C__OBJTYPE__SERVER', 'C__OBJTYPE__CLIENT')
     *                             'title' => ABC
     *                             'titles' => array('ABC', 'DEF')
     *                             'status' => 2
     *                             'status' => array(1, 2, 3) <- no plural of 'status'!
     *                             'type_group' => array(1, 2, 3) or constants
     *                             'enabled' => true|false <- default it booth
     *                             'container' => true|false <- default it booth
     * @param string $p_order_by   (optional) Order by one of the supported
     *                             properties. Defaults to null that means result will be ordered by object
     *                             identifiers.
     * @param string $p_sort       (optional) Order result ascending ('ASC') or
     *                             descending ('DESC'). Defaults to null that normally means 'ASC'.
     * @param int    $p_limit      (optional) Limitation: where to start and number of
     *                             elements, i.e. 0 or 0,10. Defaults to null that means no limitation.
     *
     * @return  isys_component_dao_result Result set
     *
     * @author Benjamin Heisig <bheisig@synetics.de>
     */
    public function get_object_types_by_properties($p_properties = null, $p_order_by = null, $p_sort = null, $p_limit = null)
    {
        // Main table:
        $l_table = 'isys_obj_type';

        // Base query:
        $l_query = 'SELECT * FROM isys_obj_type ' . 'INNER JOIN isys_obj_type_group ON isys_obj_type_group__id = isys_obj_type__isys_obj_type_group__id ' . 'WHERE TRUE ';

        // Parse properties:
        $l_properties = [];
        if (isset($p_properties)) {
            assert(is_array($p_properties));
            foreach ($p_properties as $l_key => $l_value) {
                assert(is_string($l_key));
                if ($l_key === $l_table . '__id' || $l_key === 'id') {
                    $l_properties['ids'] = [$l_value];
                } else {
                    if ($l_key === $l_table . '__title' || $l_key === 'title') {
                        $l_properties['titles'] = [$l_value];
                    } else {
                        if ($l_key === $l_table . '__status' || $l_key === 'status') {
                            // Special behavior because there is no plural form of
                            // 'status':
                            if (is_array($l_value)) {
                                $l_properties['status'] = $l_value;
                            } else {
                                $l_properties['status'] = [$l_value];
                            }
                        } else {
                            if ($l_key === $l_table . '__isys_obj_type_group__id' || $l_key === 'type_group') {
                                if (is_array($l_value)) {
                                    $l_properties['type_group'] = $l_value;
                                } else {
                                    $l_properties['type_group'] = [$l_value];
                                }
                            } else {
                                if ($l_key === $l_table . '__container') {
                                    $l_properties['container'] = (int)$l_value;
                                } else {
                                    if ($l_key === $l_table . '__show_in_tree') {
                                        $l_properties['enabled'] = $l_value;
                                    } else {
                                        // Assign all short cuts ('ids', 'titles',...):
                                        assert(is_array($l_value));
                                        $l_properties[$l_key] = $l_value;
                                    }
                                }
                            }
                        }
                    }
                } // if key
            } // foreach property
        } // if properties given

        // Conditions:

        if (array_key_exists('ids', $l_properties)) {
            $l_values = [];
            foreach ($l_properties['ids'] as $l_value) {
                assert(is_numeric($l_value));

                // Handling for constant-strings.
                if (!is_numeric($l_value) && is_string($l_value) && defined($l_value)) {
                    $l_value = constant($l_value);
                }

                if (is_numeric($l_value)) {
                    $l_values[] = $l_table . '__id = ' . $this->convert_sql_id($l_value);
                }
            }
            $l_query .= ' AND (' . implode(' OR ', $l_values) . ')';
        }

        if (array_key_exists('titles', $l_properties)) {
            $l_values = [];
            foreach ($l_properties['titles'] as $l_value) {
                assert(is_string($l_value));
                $l_values[] = $l_table . '__title LIKE ' . $this->convert_sql_text($l_value);
            }
            $l_query .= ' AND (' . implode(' OR ', $l_values) . ')';
        }

        if (array_key_exists('status', $l_properties)) {
            $l_values = [];
            foreach ($l_properties['status'] as $l_value) {
                assert(is_numeric($l_value));

                /* Handling for constant-strings */
                if (!is_numeric($l_value) && is_string($l_value) && defined($l_value)) {
                    $l_value = constant($l_value);
                }

                if (is_numeric($l_value)) {
                    $l_values[] = $l_table . '__status = ' . $this->convert_sql_id($l_value);
                }
            }
            $l_query .= ' AND (' . implode(' OR ', $l_values) . ')';
        } // if titles

        if (array_key_exists('type_group', $l_properties)) {
            $l_values = [];
            foreach ($l_properties['type_group'] as $l_value) {
                /* Handling for constant-strings */
                if (!is_numeric($l_value) && is_string($l_value) && defined($l_value)) {
                    $l_value = constant($l_value);
                }

                if (is_numeric($l_value)) {
                    $l_values[] = $l_table . '__isys_obj_type_group__id = ' . $this->convert_sql_id($l_value);
                }
            }
            $l_query .= ' AND (' . implode(' OR ', $l_values) . ')';
        } // if titles

        if (array_key_exists('enabled', $l_properties)) {
            $l_enabled = intval(filter_var($l_properties['enabled'], FILTER_VALIDATE_BOOLEAN));
            $l_query .= ' AND ' . $l_table . '__show_in_tree = ' . $l_enabled;
        } // if enabled

        if (array_key_exists('container', $l_properties)) {
            $l_container = $l_properties['container'];
            $l_query .= ' AND ' . $l_table . '__container = ' . $l_container;
        } // if container

        // Limitation, sort, ordering:

        if (isset($p_order_by)) {
            assert(is_string($p_order_by));
            $l_order_by = null;
            switch ($p_order_by) {
                case $l_table . '__title':
                case 'title':
                    $l_order_by = $l_table . '__title';
                    break;
                case $l_table . '__status':
                case 'status':
                    $l_order_by = $l_table . '__status';
                    break;
                case $l_table . '__id':
                case 'id':
                default:
                    $l_order_by = $l_table . '__id';
                    break;
            }

            $l_query .= ' ORDER BY ' . $l_order_by;

            if (isset($p_sort)) {
                assert(is_string($p_sort));
                $p_sort = strtoupper($p_sort);
                if ($p_sort == 'ASC' || $p_sort == 'DESC') {
                    $l_query .= ' ' . $p_sort;
                }
            } // if sort
        } // if order by

        if (isset($p_limit)) {
            $l_raw_limit = explode(',', $p_limit);
            $l_limit = [];
            assert(count($l_raw_limit) > 0 && count($l_raw_limit) <= 2);
            foreach ($l_raw_limit as $l_value) {
                assert(is_numeric($l_value) && $l_value >= 0);
                $l_limit[] = trim($l_value);
            }
            $l_query .= ' LIMIT ' . implode(', ', $l_limit);
        } // if limit

        $l_query .= ';';

        // Retrieval:
        return $this->retrieve($l_query);
    }

    /**
     * Fetches object type groups from database.
     *
     * @param array  $p_properties (optional) Filter by properties (associative
     *                             array of arrays or strings). Short names as keys may be used. Defaults to
     *                             null, so result won't be filtered. Examples:
     *                             'id' => 1        OR      'id' => 'C__OBJTYPE_GROUP__INFRASTRUCTURE'
     *                             'ids' => array(1, 2, 3)      OR      'ids' => array('C__OBJTYPE_GROUP__INFRASTRUCTURE', 'C__OBJTYPE_GROUP__INFRASTRUCTURE')
     *                             'title' => ABC
     *                             'titles' => array('ABC', 'DEF')
     *                             'status' => 2
     *                             'status' => array(1, 2, 3) or array('CONST1', 'CONST2')<- no plural of 'status'!
     *                             'constant' => array(1, 2, 3) or array('CONST1', 'CONST2')<- no plural of 'status'!
     * @param string $p_order_by   (optional) Order by one of the supported
     *                             properties. Defaults to null that means result will be ordered by object
     *                             identifiers.
     * @param string $p_sort       (optional) Order result ascending ('ASC') or
     *                             descending ('DESC'). Defaults to null that normally means 'ASC'.
     * @param int    $p_limit      (optional) Limitation: where to start and number of
     *                             elements, i.e. 0 or 0,10. Defaults to null that means no limitation.
     *
     * @return  isys_component_dao_result Result set
     *
     * @author Selcuk Kekec <skekec@synetics.de>
     */
    public function get_object_type_groups_by_properties($p_properties = null, $p_order_by = null, $p_sort = null, $p_limit = null)
    {
        // Main table:
        $l_table = 'isys_obj_type_group';

        // Base query:
        $l_query = 'SELECT * FROM isys_obj_type_group WHERE TRUE';

        // Parse properties:
        $l_properties = [];
        if (isset($p_properties)) {
            assert(is_array($p_properties));
            foreach ($p_properties as $l_key => $l_value) {
                assert(is_string($l_key));
                if ($l_key === $l_table . '__id' || $l_key === 'id') {
                    $l_properties['ids'] = [$l_value];
                } else {
                    if ($l_key === $l_table . '__title' || $l_key === 'title') {
                        $l_properties['titles'] = [$l_value];
                    } else {
                        if ($l_key === $l_table . '__status' || $l_key === 'status') {
                            // Special behavior because there is no plural form of
                            // 'status':
                            if (is_array($l_value)) {
                                $l_properties['status'] = $l_value;
                            } else {
                                $l_properties['status'] = [$l_value];
                            }
                        } else {
                            if ($l_key === $l_table . '__const' || $l_key === 'constant') {
                                // Special behavior because there is no plural form of
                                // 'status':
                                if (is_array($l_value)) {
                                    $l_properties['constant'] = $l_value;
                                } else {
                                    $l_properties['constant'] = [$l_value];
                                }
                            } else {
                                // Assign all short cuts ('ids', 'titles',...):
                                assert(is_array($l_value));
                                $l_properties[$l_key] = $l_value;
                            }
                        }
                    }
                } // if key
            } // foreach property
        } // if properties given

        // Conditions:

        if (array_key_exists('ids', $l_properties)) {
            $l_values = [];
            foreach ($l_properties['ids'] as $l_value) {
                assert(is_numeric($l_value));

                /* Handling for constant-strings */
                if (!is_numeric($l_value) && is_string($l_value) && defined($l_value)) {
                    $l_value = constant($l_value);
                }

                if (is_numeric($l_value)) {
                    $l_values[] = $l_table . '__id = ' . $this->convert_sql_id($l_value);
                }
            }
            $l_query .= ' AND (' . implode(' OR ', $l_values) . ')';
        } // if identifiers

        if (array_key_exists('titles', $l_properties)) {
            $l_values = [];
            foreach ($l_properties['titles'] as $l_value) {
                assert(is_string($l_value));
                $l_values[] = $l_table . '__title = ' . $this->convert_sql_text($l_value);
            }
            $l_query .= ' AND (' . implode(' OR ', $l_values) . ')';
        } // if titles

        if (array_key_exists('status', $l_properties)) {
            $l_values = [];
            foreach ($l_properties['status'] as $l_value) {
                assert(is_numeric($l_value));

                /* Handling for constant-strings */
                if (!is_numeric($l_value) && is_string($l_value) && defined($l_value)) {
                    $l_value = constant($l_value);
                }

                if (is_numeric($l_value)) {
                    $l_values[] = $l_table . '__status = ' . $this->convert_sql_id($l_value);
                }
            }
            $l_query .= ' AND (' . implode(' OR ', $l_values) . ')';
        } // if titles

        if (array_key_exists('constant', $l_properties)) {
            $l_values = [];
            foreach ($l_properties['constant'] as $l_value) {
                assert(is_numeric($l_value));

                $l_values[] = $l_table . '__const = ' . $this->convert_sql_text($l_value);
            }
            $l_query .= ' AND (' . implode(' OR ', $l_values) . ')';
        } // if titles

        // Limitation, sort, ordering:

        if (isset($p_order_by)) {
            assert(is_string($p_order_by));
            $l_order_by = null;
            switch ($p_order_by) {
                case $l_table . '__title':
                case 'title':
                    $l_order_by = $l_table . '__title';
                    break;
                case $l_table . '__status':
                case 'status':
                    $l_order_by = $l_table . '__status';
                    break;
                case $l_table . 'const':
                case 'constant':
                    $l_order_by = $l_table . '__const';
                    break;
                case $l_table . '__id':
                case 'id':
                default:
                    $l_order_by = $l_table . '__id';
                    break;
            }

            $l_query .= ' ORDER BY ' . $l_order_by;

            if (isset($p_sort)) {
                assert(is_string($p_sort));
                $p_sort = strtoupper($p_sort);
                if ($p_sort == 'ASC' || $p_sort == 'DESC') {
                    $l_query .= ' ' . $p_sort;
                }
            } // if sort
        } // if order by

        if (isset($p_limit)) {
            $l_raw_limit = explode(',', $p_limit);
            $l_limit = [];

            if (isset($l_raw_limit[0]) && isset($l_raw_limit[1]) && is_numeric($l_raw_limit[0]) && is_numeric($l_raw_limit[1])) {
                $l_limit[] = (int)$l_raw_limit[0];
                $l_limit[] = (int)$l_raw_limit[1];
            } else {
                $l_limit[] = (int)$p_limit;
            }
            $l_query .= ' LIMIT ' . implode(', ', $l_limit);
        } // if limit

        $l_query .= ';';

        // Retrieval:
        return $this->retrieve($l_query);
    }

    /**
     * Returns the name of the object type or empty string.
     *
     * @param   mixed $p_typeid May be an integer or constant-name.
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_objtype_name_by_id_as_string($p_typeid)
    {
        $l_objtype = $this->get_type_by_id($p_typeid);

        if ($l_objtype !== null) {
            return $l_objtype["isys_obj_type__title"];
        }

        return "";
    }

    /**
     * Retrieves a record status in form of a string.
     *
     * @param   integer $p_record_status
     *
     * @return  string
     */
    public function get_record_status_as_string($p_record_status)
    {
        $language = isys_application::instance()->container->get('language');
        $l_arData = [
            C__RECORD_STATUS__NORMAL   => $language->get('LC__CMDB__RECORD_STATUS__NORMAL'),
            C__RECORD_STATUS__ARCHIVED => $language->get('LC__CMDB__RECORD_STATUS__ARCHIVED'),
            C__RECORD_STATUS__DELETED  => $language->get('LC__CMDB__RECORD_STATUS__DELETED'),
            C__RECORD_STATUS__TEMPLATE => 'Template'
        ];

        return $l_arData[$p_record_status];
    }

    /**
     * Get the CMDB status of an object via its id.
     *
     * @param   integer $p_obj_id
     *
     * @return  string
     */
    public function get_object_cmdb_status_by_id($p_obj_id)
    {
        return $this->get_object($p_obj_id, false, 1)
            ->get_row_value('isys_obj__isys_cmdb_status__id');
    }

    /**
     * Retrieve the status of the given object.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function get_object_status_by_id($p_obj_id)
    {
        return (int)$this->get_object($p_obj_id, false, 1)
            ->get_row_value('isys_obj__status');
    }

    /**
     * Retrieves the status-name of the given object.
     *
     * @param   integer $p_obj_id
     *
     * @return  string
     * @uses    $this->get_object_status_by_id()
     */
    public function get_object_status_by_id_as_string($p_obj_id)
    {
        return $this->get_record_status_as_string($this->get_object_status_by_id($p_obj_id));
    }

    /**
     * Return bool if the specified object type should be shown in racks.
     *
     * @param   integer $p_obj_type
     *
     * @return  boolean
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function is_obj_type_in_rack($p_obj_type)
    {
        $l_strSQL = "SELECT isys_obj_type__show_in_rack
			FROM isys_obj_type
			WHERE isys_obj_type__id = " . $this->convert_sql_id($p_obj_type) . ";";

        $l_data = $this->retrieve($l_strSQL)
            ->get_row_value('isys_obj_type__show_in_rack');

        if ($l_data !== null && $l_data == "1") {
            return true;
        }

        return false;
    }

    /**
     * Returns the name of the img for an objType or empty string.
     *
     * @param   mixed $p_typeid May be an integer or constant-name.
     *
     * @return  string
     * @version Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_objtype_img_by_id_as_string($p_typeid)
    {
        $l_objtype = $this->get_type_by_id($p_typeid);

        if ($l_objtype !== null) {
            return $l_objtype["isys_obj_type__obj_img_name"];
        }

        return "";
    }

    /**
     * @param integer $p_objID
     *
     * @return string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @version Niclas Potthast <npotthast@i-doit.org> - 2005-10-05
     * @desc    Returns the name of the img for an object or an empty string if
     *       nothing is found
     * @todo    must return image object?
     */
    public function get_obj_img_by_id_as_string($p_objID)
    {
        $l_str_retcode = "";

        $l_daores = $this->get_all_catg_by_obj_type_id($p_objID);

        if ($l_daores != null) {
            while ($l_typedata = $l_daores->get_row()) {
                $l_str_retcode = $l_typedata["isys_catg_global_list__obj_img_name"];
            }
        } else {
            return $l_daores;
        }

        return $l_str_retcode;
    }

    /**
     * Returns the object id by sysid on success, false on failure.
     *
     * @param   string $p_sysid
     *
     * @return  mixed
     */
    public function get_obj_id_by_sysid($p_sysid)
    {
        $l_data = $this
            ->retrieve('SELECT isys_obj__id FROM isys_obj WHERE isys_obj__sysid = ' . $this->convert_sql_text($p_sysid) . ';')
            ->get_row_value('isys_obj__id');

        if ($l_data !== null) {
            return (int)$l_data;
        }

        return false;
    }

    /**
     * Returns the sysid by object-id on success, false on failure.
     *
     * @param   integer $p_obj_id
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_sysid_by_obj_id($p_obj_id)
    {
        return $this
            ->retrieve('SELECT isys_obj__sysid FROM isys_obj WHERE isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';')
            ->get_row_value('isys_obj__sysid');
    }

    /**
     * Returns the object id by title.
     *
     * @param   string    $p_title
     * @param   int|array $p_obj_type
     * @param   int       $p_status
     *
     * @return  integer
     */
    public function get_obj_id_by_title($p_title, $p_obj_type = null, $p_status = null)
    {
        if (!empty($p_title)) {
            $l_sql = 'SELECT isys_obj__id FROM isys_obj WHERE isys_obj__title = ' . $this->convert_sql_text($p_title);

            if (is_numeric($p_obj_type)) {
                $l_sql .= ' AND isys_obj__isys_obj_type__id = ' . $this->convert_sql_id($p_obj_type);
            } elseif (is_array($p_obj_type) && count($p_obj_type) > 0) {
                $l_sql .= ' AND isys_obj__isys_obj_type__id ' . $this->prepare_in_condition($p_obj_type);
            }

            if (is_numeric($p_status)) {
                $l_sql .= ' AND isys_obj__status = ' . $this->convert_sql_int($p_status);
            }

            return $this->retrieve($l_sql . ';')->get_row_value('isys_obj__id') ?: false;
        }

        return false;
    }

    /**
     * Returns the name of the object or empty string.
     *
     * @param   integer $p_obj_id
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_obj_name_by_id_as_string($p_obj_id)
    {
        if (is_numeric($p_obj_id) && $p_obj_id > 0) {
            if (!isset(self::$m_obj_names[$p_obj_id])) {
                self::$m_obj_names[$p_obj_id] = $this->retrieve('SELECT isys_obj__title FROM isys_obj WHERE isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';')
                    ->get_row_value('isys_obj__title');
            }

            return self::$m_obj_names[$p_obj_id];
        }

        return '';
    }

    /**
     * Checks if an object exists.
     *
     * @param   integer $objectId
     * @param   boolean $ignoreCmdbStatus
     *
     * @return  boolean
     * @throws  isys_exception_database
     */
    public function obj_exists($objectId, $ignoreCmdbStatus = true)
    {
        if ($objectId > 0) {
            $res = $this->get_object($objectId, $ignoreCmdbStatus, 1);
            return is_countable($res) && !!count($res);
        }

        return false;
    }

    /**
     * Fetches information about objects.
     *
     * @param   integer|array $objectId
     * @param   boolean $ignoreCmdbStatus
     * @param   integer|null $limit
     * @param   null|int $status
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_object($objectId = null, $ignoreCmdbStatus = false, $limit = null, $status = null)
    {
        $l_sql = 'SELECT isys_obj.*, isys_obj_type.*, isys_cmdb_status__color, isys_cmdb_status__title
			FROM isys_obj
			INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			INNER JOIN isys_cmdb_status ON isys_obj__isys_cmdb_status__id = isys_cmdb_status__id';

        if ($objectId !== null) {
            if (is_array($objectId)) {
                $l_sql .= ' WHERE isys_obj__id ' . $this->prepare_in_condition($objectId);
            } else {
                $l_sql .= ' WHERE isys_obj__id = ' . $this->convert_sql_id($objectId);
            }
        }

        if (!$ignoreCmdbStatus) {
            $l_filter = $this->prepare_status_filter();

            if (!empty($l_filter)) {
                $l_sql .= ' AND ' . $l_filter;
            }
        }

        // Check whether status filter is present
        if (!empty($status)) {
            $l_sql .= ' AND isys_obj__status = ' . $this->convert_sql_id($status);
        }

        if ($limit !== null) {
            $l_sql .= ' LIMIT ' . (int)$limit;
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Searchs for objects using $p_filter and additional comma separated object type- and group-filters.
     *
     * @param   string  $p_filter
     * @param   mixed   $p_typeFilter
     * @param   mixed   $p_groupFilter
     * @param   string  $p_condition
     * @param   boolean $p_relation
     * @param   boolean $p_relation_only
     * @param   string  $p_order_by
     * @param   boolean $p_limit
     * @param int       $p_status
     *
     * @param null      $p_obj_type_black_list
     * @param null      $p_cat_filter
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function search_objects(
        $p_filter,
        $p_typeFilter = null,
        $p_groupFilter = null,
        $p_condition = "",
        $p_relation = false,
        $p_relation_only = false,
        $p_order_by = "isys_obj__title ASC",
        $p_limit = false,
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_obj_type_black_list = null,
        $p_cat_filter = null
    ) {
        $l_typeCondition = $l_groupCondition = $l_statusFilter = $l_limit = '';

        // Retrieve all objecttypes with the category filter and add them to the typefilter
        if ($p_cat_filter !== null) {
            if (strpos($p_cat_filter, ';')) {
                $l_categories = explode(';', $p_cat_filter);
            } else {
                $l_categories = [$p_cat_filter];
            }

            $l_global_categories = array_map(function ($l_item) {
                return ' isysgui_catg__const = ' . $this->convert_sql_text($l_item);
            }, $l_categories);
            $l_specific_categories = array_map(function ($l_item) {
                return ' isysgui_cats__const = ' . $this->convert_sql_text($l_item);
            }, $l_categories);

            $l_sql = 'SELECT isys_obj_type_2_isysgui_catg__isys_obj_type__id FROM isys_obj_type_2_isysgui_catg
				INNER JOIN isysgui_catg ON isysgui_catg__id = isys_obj_type_2_isysgui_catg__id
				WHERE ' . implode(' OR ', $l_global_categories);

            $l_res = $this->retrieve($l_sql);
            if ($l_res->num_rows()) {
                while ($l_row = $l_res->get_row()) {
                    $p_typeFilter .= ';' . $l_row['isys_obj_type_2_isysgui_catg__isys_obj_type__id'];
                }
            }
            $l_res->free_result();

            $l_sql = 'SELECT isys_obj_type__id FROM isys_obj_type INNER JOIN isysgui_cats ON isysgui_cats__id = isys_obj_type__isysgui_cats__id
				WHERE ' . implode(' OR ', $l_specific_categories);

            $l_res = $this->retrieve($l_sql);
            if ($l_res->num_rows()) {
                while ($l_row = $l_res->get_row()) {
                    $p_typeFilter .= ';' . $l_row['isys_obj_type__id'];
                }
            }
            $l_res->free_result();
        }

        if ($p_relation && defined('C__OBJTYPE__RELATION')) {
            if (isset($p_typeFilter) && !empty($p_typeFilter)) {
                $p_typeFilter .= ";" . C__OBJTYPE__RELATION;
            }

            if ($p_relation_only) {
                $p_typeFilter = C__OBJTYPE__RELATION;
            }
        }

        if (isset($p_typeFilter) && !empty($p_typeFilter)) {
            $l_typeCondition = " AND isys_obj_type__id " . $this->prepare_in_condition(explode(";", $p_typeFilter));
        }

        if (isset($p_groupFilter) && !empty($p_groupFilter)) {
            $l_groupCondition = " AND isys_obj_type__isys_obj_type_group__id " . $this->prepare_in_condition(explode(";", $p_groupFilter));
        }

        if (isset($p_status) && !empty($p_status)) {
            $l_statusFilter = " AND isys_obj__status = " . $p_status . " ";
        }

        if ($p_limit) {
            $l_limit = " LIMIT " . $p_limit;
        }

        $l_obj_type_ids = filter_defined_constants([
            'C__OBJTYPE__LOCATION_GENERIC',
            'C__OBJTYPE__PARALLEL_RELATION',
            'C__OBJTYPE__SOA_STACK',
            'C__OBJTYPE__GENERIC_TEMPLATE',
            'C__OBJTYPE__CONTAINER'
        ]);

        if (!$p_relation && defined('C__OBJTYPE__RELATION')) {
            $l_obj_type_ids[] = C__OBJTYPE__RELATION;
        }

        if (isset($p_obj_type_black_list) && !empty($p_obj_type_black_list)) {
            $l_obj_type_ids = array_merge($l_obj_type_ids, explode(';', $p_obj_type_black_list));
        }

        $l_obj_type_exclude = "(isys_obj_type__id " . $this->prepare_in_condition($l_obj_type_ids, true) . ")";

        $l_sql = "SELECT isys_obj__id, isys_obj__title, isys_obj__sysid, isys_obj_type__title, isys_obj_type__id FROM isys_obj
			INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			WHERE isys_obj__title LIKE '%" . $this->m_db->escape_string($p_filter) . "%'
			AND " . $l_obj_type_exclude . " " . $l_typeCondition . $l_groupCondition . $p_condition . $l_statusFilter . " ORDER BY " . $p_order_by . $l_limit . ";";

        return $this->retrieve($l_sql);
    }

    /**
     * Wrapper method for "get_object()".
     *
     * @param   integer  $p_objid
     * @param   boolean  $p_ignore_status
     * @param   null|int $status
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_object_by_id($p_objid, $p_ignore_status = false, $status = null)
    {
        return $this->get_object($p_objid, $p_ignore_status, 1, $status);
    }

    /**
     * Returns the isysgui_catg entry by given table name.
     *
     * @param   string $table
     *
     * @return  isys_component_dao_result
     * @author  Dennis Stuecken <dstuecken@i-doit.de>
     */
    public function get_catg_by_table_name($table)
    {
        if (strpos($table, '_list') !== false) {
            $table = str_replace('_list', '', $table);
        }

        return $this->retrieve('SELECT * FROM isysgui_catg WHERE isysgui_catg__source_table = ' . $this->convert_sql_text($table) . ';');
    }

    /**
     * Returns the name of the global category or an empty string.
     *
     * @param   integer $categoryId
     *
     * @return  string
     * @author  Dennis Stuecken <dstuecken@i-doit.de>
     */
    public function get_catg_name_by_id_as_string($categoryId)
    {
        return $this
            ->retrieve('SELECT isysgui_catg__title FROM isysgui_catg WHERE isysgui_catg__id = ' . $this->convert_sql_id($categoryId) . ';')
            ->get_row_value('isysgui_catg__title');
    }

    /**
     * Returns the name of the specific category or an empty string.
     *
     * @param   integer $categoryId
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_cats_name_by_id_as_string($categoryId)
    {
        return $this
            ->retrieve('SELECT isysgui_cats__title FROM isysgui_cats WHERE isysgui_cats__id = ' . $this->convert_sql_id($categoryId) . ';')
            ->get_row_value('isysgui_cats__title');
    }

    /**
     * Returns the name of the custom category or an empty string
     *
     * @param $categoryId
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_cat_custom_name_by_id_as_string($categoryId)
    {
        return $this
            ->retrieve('SELECT isysgui_catg_custom__title FROM isysgui_catg_custom WHERE isysgui_catg_custom__id = ' . $this->convert_sql_id($categoryId) . ';')
            ->get_row_value('isysgui_catg_custom__title');
    }

    /**
     * Returns category id by its const.
     *
     * @param   string $p_const
     * @param   string $p_type
     *
     * @return  integer
     */
    public function get_cat_id_by_const_name($p_const, $p_type = 'g')
    {
        $sql = 'SELECT isysgui_cat' . $p_type . '__id AS id
            FROM isysgui_cat' . $p_type . ' 
            WHERE isysgui_cat' . $p_type . '__const = ' . $this->convert_sql_text($p_const) . ';';

        return (int)$this->retrieve($sql)->get_row_value('id');
    }

    /**
     * Returns category id by its const.
     *
     * @param   integer $p_id
     * @param   string  $p_type
     *
     * @return  integer
     */
    public function get_cat_const_by_id($p_id, $p_type = 'g')
    {
        $sql = 'SELECT isysgui_cat' . $p_type . '__const AS const
            FROM isysgui_cat' . $p_type . ' 
            WHERE isysgui_cat' . $p_type . '__id = ' . $this->convert_sql_id($p_id) . ';';

        return $this->retrieve($sql)->get_row_value('const');
    }

    /**
     * Returns the object type record specified by the objecttype ID.
     *
     * @param   int|string $p_typeid
     *
     * @return  array
     * @throws  isys_exception_dao
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_type_by_id($p_typeid)
    {
        if (is_numeric($p_typeid)) {
            return $this->get_object_type((int)$p_typeid);
        }

        if (is_string($p_typeid)) {
            return $this->get_object_type(null, $p_typeid);
        }

        if (empty($p_typeid)) {
            return null;
        }

        throw new isys_exception_dao('Objecttype (' . $p_typeid . ') is not defined.');
    }

    /**
     * Does the given object type exist?
     *
     * @param  int|string $p_typeid May be an integer or constant-name.
     *
     * @return boolean
     */
    public function check_type($p_typeid)
    {
        return $this->get_type_by_id($p_typeid) !== null;
    }

    /**
     * Returns all records from isysgui_catg which apply to the given object-type-ID.
     *
     * @param   integer $p_objtype_id
     * @param   integer $p_catg_id
     *
     * @return  isys_component_dao_result
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @todo    Set constant for active records -> isysgui_catg__status = '1'.
     */
    public function get_all_catg_by_obj_type_id($p_objtype_id, $p_catg_id = null)
    {
        $l_sql = 'SELECT *
			FROM isys_obj_type_2_isysgui_catg AS t_conn
			LEFT JOIN isysgui_catg AS t_gui ON t_conn.isys_obj_type_2_isysgui_catg__isysgui_catg__id=t_gui.isysgui_catg__id
			WHERE isysgui_catg__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' ';

        if ($p_objtype_id !== null) {
            $l_sql .= 'AND isys_obj_type_2_isysgui_catg__isys_obj_type__id = ' . $this->convert_sql_id($p_objtype_id) . ' ';
        }

        if ($p_catg_id !== null) {
            $l_sql .= 'AND isys_obj_type_2_isysgui_catg__isysgui_catg__id = ' . $this->convert_sql_id($p_catg_id) . ' ';
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Returns an array with all global categories which are connected to a specific object type for the overview view.
     * Used table: isys_obj_type_2_isysgui_catg_overview
     *
     * @param   integer $p_obj_type
     * @param   integer $p_record_status
     * @param   boolean $p_overview_only
     *
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     * @return  isys_component_dao_result
     */
    public function get_catg_by_obj_type($p_obj_type, $p_record_status = C__RECORD_STATUS__NORMAL, $p_overview_only = false)
    {
        $l_sql = 'SELECT isysgui_catg.*, isys_obj_type.* FROM isysgui_catg
            INNER JOIN isys_obj_type_2_isysgui_catg 
                ON isys_obj_type_2_isysgui_catg__isysgui_catg__id = isysgui_catg.isysgui_catg__id 
                OR isys_obj_type_2_isysgui_catg__isysgui_catg__id = isysgui_catg.isysgui_catg__parent
            INNER JOIN isys_obj_type ON isys_obj_type_2_isysgui_catg__isys_obj_type__id = isys_obj_type.isys_obj_type__id
            WHERE isysgui_catg.isysgui_catg__status = ' . $this->convert_sql_id($p_record_status) . '
            AND isys_obj_type.isys_obj_type__id = ' . $this->convert_sql_id($p_obj_type) . '
            ORDER BY isysgui_catg.isysgui_catg__sort;';

        if ($p_overview_only) {
            $l_sql = 'SELECT isysgui_catg.*, isys_obj_type_2_isysgui_catg_overview__sort, isys_obj_type.* FROM isysgui_catg
                INNER JOIN isys_obj_type_2_isysgui_catg_overview 
                    ON isys_obj_type_2_isysgui_catg_overview.isysgui_catg__id = isysgui_catg.isysgui_catg__id 
                    OR isys_obj_type_2_isysgui_catg_overview.isysgui_catg__id = isysgui_catg.isysgui_catg__parent
				INNER JOIN isys_obj_type ON isys_obj_type_2_isysgui_catg_overview.isys_obj_type__id = isys_obj_type.isys_obj_type__id
				WHERE isysgui_catg.isysgui_catg__status = ' . $this->convert_sql_id($p_record_status) . '
			    AND isys_obj_type.isys_obj_type__id = ' . $this->convert_sql_id($p_obj_type) . '
				AND isysgui_catg.isysgui_catg__overview = 1
				ORDER BY isys_obj_type_2_isysgui_catg_overview__sort;';
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Fetches all objects, matching to a certain object-type.
     *
     * @param   mixed   $p_obj_type May be an integer, or a array of integers.
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_objects_by_type($p_obj_type, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = 'SELECT * FROM isys_obj ';

        if (is_array($p_obj_type)) {
            $l_sql .= 'WHERE isys_obj__isys_obj_type__id IN (' . implode(',', $p_obj_type) . ') ';
        } else {
            $l_sql .= 'WHERE isys_obj__isys_obj_type__id = ' . $this->convert_sql_id($p_obj_type);
        }

        $l_sql .= ' AND isys_obj__status = ' . $this->convert_sql_int($p_status) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Fetches all durable global categories from database.
     *
     * @todo Cleanup isys_obj_type_2_isysgui_catg
     * @todo Switch from static version to a more dynamically version...
     *
     * @param int  $p_record_status
     * @param bool $p_overview_only
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_durable_catg($p_record_status = C__RECORD_STATUS__NORMAL, $p_overview_only = false)
    {
        $l_durable = [
            'C__CATG__GLOBAL',
            'C__CATG__PLANNING'
        ];

        $l_query = 'SELECT * FROM isysgui_catg ';

        if ($p_overview_only === true) {
            $l_query .= 'INNER JOIN sys_obj_type_2_isysgui_catg_overview ' . 'ON ' . 'isys_obj_type_2_isysgui_catg_overview.isysgui_catg__id = ' .
                'isysgui_catg.isysgui_catg__id OR ' . 'isys_obj_type_2_isysgui_catg_overview.isysgui_catg__id = ' . 'isysgui_catg.isysgui_catg__parent ';
        }

        $l_query .= 'WHERE (';

        for ($l_index = 0, $l_cdurable = count($l_durable);$l_index < $l_cdurable;$l_index++) {
            if ($l_index > 0) {
                $l_query .= ' OR ';
            }
            $l_query .= 'isysgui_catg__const = \'' . $l_durable[$l_index] . '\'';
        }
        $l_query .= ') ';

        $l_query .= 'AND isysgui_catg__status = ' . $this->convert_sql_int($p_record_status);

        return $this->retrieve($l_query);
    }

    /**
     * @param      $p_objTypeID
     * @param bool $p_overview
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_catg_custom_by_obj_type($p_objTypeID, $p_overview = false)
    {
        $l_sql = 'SELECT * 
            FROM isys_obj_type_2_isysgui_catg_custom AS oc
			INNER JOIN isysgui_catg_custom AS c ON oc.isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id = c.isysgui_catg_custom__id ';
        $l_order_by = ';';

        if ($p_overview) {
            $l_sql .= 'INNER JOIN isys_obj_type_2_isysgui_catg_custom_overview AS ovc
				ON ovc.isys_obj_type__id = oc.isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id AND
				ovc.isysgui_catg_custom__id = oc.isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id ';
            $l_order_by = ' ORDER BY isys_obj_type_2_isysgui_catg_custom_overview__sort;';
        }

        $l_sql .= 'WHERE oc.isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id = ' . $this->convert_sql_id($p_objTypeID) . ' ';

        return $this->retrieve($l_sql . $l_order_by);
    }

    /**
     * Gets all category types.
     *
     * @return  array
     */
    public function get_category_types()
    {
        return [
            C__CMDB__CATEGORY__TYPE_GLOBAL   => 'C__CMDB__CATEGORY__TYPE_GLOBAL',
            C__CMDB__CATEGORY__TYPE_SPECIFIC => 'C__CMDB__CATEGORY__TYPE_SPECIFIC',
            C__CMDB__CATEGORY__TYPE_CUSTOM   => 'C__CMDB__CATEGORY__TYPE_CUSTOM'
        ];
    }

    /**
     * Fetch all global and specific categories from database.
     *
     * @param   array $p_category_types
     *
     * @return  array
     */
    public function get_all_categories($p_category_types = [])
    {
        $l_result = [];

        if (!empty(self::$m_category_cache)) {
            if (!empty($p_category_types)) {
                $l_category_types = $p_category_types;
                foreach ($l_category_types as $l_cat_type) {
                    if (isset(self::$m_category_cache[$l_cat_type])) {
                        $l_result[$l_cat_type] = self::$m_category_cache[$l_cat_type];
                        unset($p_category_types[$l_cat_type]);
                    }
                }
                if (empty($p_category_types)) {
                    return $l_result;
                }
            } else {
                return self::$m_category_cache;
            }
        }

        $l_iterations = [
            C__CMDB__CATEGORY__TYPE_GLOBAL   => [
                'table'  => 'isysgui_catg',
                'prefix' => 'isysgui_catg__',
                'method' => 'get_all_catg'
            ],
            C__CMDB__CATEGORY__TYPE_SPECIFIC => [
                'table'  => 'isysgui_cats',
                'prefix' => 'isysgui_cats__',
                'method' => 'get_all_cats'
            ],
            C__CMDB__CATEGORY__TYPE_CUSTOM   => [
                'table'  => 'isysgui_catg_custom',
                'prefix' => 'isysgui_catg_custom__',
                'method' => 'get_all_catg_custom'
            ]
        ];

        $l_categories = [];
        foreach ($l_iterations as $l_type => $l_values) {
            if (isset($l_result[$l_type])) {
                continue;
            }

            if (is_array($p_category_types) && count($p_category_types) > 0) {
                array_walk($p_category_types, function ($p_val) {
                    return "'{$p_val}'";
                });
                $l_condition = ' AND ' . $l_values['table'] . '__type IN(' . implode(',', $p_category_types) . ')';
            } else {
                $l_condition = null;
            }

            if (method_exists($this, $l_values['method'])) {
                $l_result_set = call_user_func([
                    $this,
                    $l_values['method']
                ], null, $l_condition);
                $l_prefix = $l_values['prefix'];

                $l_categories = [];

                while ($l_row = $l_result_set->get_row()) {
                    $l_id = $l_row[$l_prefix . 'id'];

                    foreach ($l_row as $l_key => $l_value) {
                        $l_key = str_replace($l_prefix, '', $l_key);

                        $l_categories[$l_id][$l_key] = $l_value;
                    }
                }
            }

            $l_result[$l_type] = $l_categories;
            if (!isset(self::$m_category_cache[$l_type])) {
                self::$m_category_cache[$l_type] = $l_categories;
            }
        }

        return $l_result;
    }

    /**
     * Returns all records from isysgui_catg.
     *
     * @param   integer $p_isys_gui_catg__id
     * @param   string  $p_condition
     *
     * @return  isys_component_dao_result
     * @author  Andre Woesten <awoesten@i-doit.org>
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function get_all_catg($p_isys_gui_catg__id = null, $p_condition = null)
    {
        $l_sql = 'SELECT * FROM isysgui_catg  WHERE TRUE ';

        if ($p_isys_gui_catg__id !== null) {
            $l_sql .= ' AND isysgui_catg__id = ' . $this->convert_sql_id($p_isys_gui_catg__id) . ' ';
        }

        if ($p_condition !== null && !empty($p_condition)) {
            $l_sql .= $p_condition;
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Returns all records from isysgui_catg_custom.
     *
     * @param   integer $p_isys_gui_catg__id
     * @param   string  $p_condition
     *
     * @return  isys_component_dao_result
     */
    public function get_all_catg_custom($p_isys_gui_catg__id = null, $p_condition = null)
    {
        $l_sql = 'SELECT * FROM isysgui_catg_custom WHERE TRUE';

        if ($p_isys_gui_catg__id !== null) {
            if (is_array($p_isys_gui_catg__id) && count($p_isys_gui_catg__id) > 0) {
                $l_sql .= ' AND isysgui_catg_custom__id IN (' . implode(',', $p_isys_gui_catg__id) . ') ';
            } elseif (is_numeric($p_isys_gui_catg__id)) {
                $l_sql .= ' AND isysgui_catg_custom__id = ' . $this->convert_sql_id($p_isys_gui_catg__id) . ' ';
            }
        }

        if ($p_condition !== null) {
            $l_sql .= $p_condition;
        }

        $l_sql .= ' ORDER BY isysgui_catg_custom__sort ASC;';

        return $this->retrieve($l_sql);
    }

    /**
     * Returns all records from isysgui_cats.
     *
     * @param   integer $p_isys_gui_cats__id
     * @param   string  $p_condition
     *
     * @return  isys_component_dao_result
     * @author  Gezim Rugova <grugova@synetics.de>
     */
    public function get_all_cats($p_isys_gui_cats__id = null, $p_condition = null)
    {
        $l_sql = 'SELECT * FROM isysgui_cats WHERE TRUE ';

        if ($p_isys_gui_cats__id !== null) {
            $l_sql .= ' AND isysgui_cats__id = ' . $this->convert_sql_id($p_isys_gui_cats__id) . ' ';
        }

        if ($p_condition !== null && !empty($p_condition)) {
            $l_sql .= $p_condition;
        }

        return $this->retrieve($l_sql);
    }

    /**
     *
     * @param integer $p_objtype_id
     *
     * @param null    $p_condition
     * @param int     $p_status
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database @desc Returns ALL records from isysgui_catg and mark those which apply to the
     *       given object-type-iD by adding the selected column to the result
     */
    public function get_all_catg_2_objtype_id($p_objtype_id, $p_condition = null, $p_status = C__RECORD_STATUS__NORMAL)
    {
        // read all catg(s) from isysgui_catg and add a column including the selection-status
        // if selected != 0 the catg is selected for tha actual catg

        $l_object_type_condition = '';

        if ($p_objtype_id > 0) {
            $l_object_type_condition = ' AND isys_obj_type_2_isysgui_catg__isys_obj_type__id = ' . $this->convert_sql_id($p_objtype_id);
        }

        $l_strSql = 'SELECT *, (SELECT COUNT(*) FROM isys_obj_type_2_isysgui_catg WHERE isys_obj_type_2_isysgui_catg__isysgui_catg__id = isysgui_catg__id ' . $l_object_type_condition . ') selected
            FROM isysgui_catg 
            WHERE TRUE';

        if ($p_status) {
            $l_strSql .= ' AND isysgui_catg__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);
        }

        if ($p_condition) {
            $l_strSql .= ' ' . $p_condition . ' ';
        }

        return $this->retrieve($l_strSql . ' ORDER BY selected, isysgui_catg__sort;');
    }

    /**
     * @return boolean
     *
     * @param integer $p_obj_id
     * @param array   $p_arr_catd_id (array with id of isysview_catg(s))
     *
     * @version Niclas Potthast <npotthast@i-doit.org> - 2005-10-05
     * @desc    update the view of shown catd(s) for a given obj
     */
    public function update_view_all_catd_2_obj($p_obj_id, $p_arr_catd_id)
    {
        $l_bRet = false;

        // read the record for isys_catd_distributor for the given obj_id
        $l_strSql = "SELECT * " . "FROM " . "isys_catd_distributor " . "WHERE " . "isys_catd_distributor__isys_obj__id = '$p_obj_id' ";

        $l_daores = $this->retrieve($l_strSql);

        if ($l_daores != null) {
            $l_distributor = $l_daores->get_row(); // get row from sys_catd_distributor for act obj
            if ($l_distributor != null) {
                // read all catd(s) from isysgui_catd
                $l_strSql = "SELECT * " . "FROM " . "isysgui_catd " .

                    "WHERE " . "isysgui_catd__status = '" . C__RECORD_STATUS__NORMAL . "' " . // only active records
                    // @todo:
                    "ORDER BY " . "isysgui_catd__sort ASC;"; // put in right order

                $l_daores = $this->retrieve($l_strSql);

                if ($l_daores != null) {
                    while ($l_guicatd = $l_daores->get_row()) { // for each catd
                        $l_intSetVisible = 0;
                        $l_catdId = $l_guicatd['isysgui_catd__id'];
                        if (is_countable($p_arr_catd_id) && count($p_arr_catd_id) > 0) {
                            // if catg is in array, set catd as __visible
                            for ($l_i = 0;$l_i < count($p_arr_catd_id);$l_i++) {
                                if ((int)$p_arr_catd_id[$l_i] == $l_catdId) {
                                    $l_intSetVisible = 1;
                                    $l_i = count($p_arr_catd_id);
                                }
                            }
                        }
                        $l_strForeignKey = "isys_catd_distributor__" . $l_guicatd['isysgui_catd__source_table'] . "__id";

                        // store __visible state for each catd / table

                        $l_strSql = "UPDATE " . $l_guicatd['isysgui_catd__source_table'] . " " . "SET " . $l_guicatd['isysgui_catd__source_table'] . "__visible " . " = " .
                            "'" . $l_intSetVisible . "' " . // Set __visible
                            "WHERE " . $l_guicatd['isysgui_catd__source_table'] . "__id " . "= " . "'" . $l_distributor[$l_strForeignKey] . "'" . ";";

                        $l_bRet = $this->update($l_strSql);
                    }
                    $l_daores->free_result();

                    if ($l_bRet) {
                        if ($this->apply_update()) {
                            $l_bRet = true;
                        } else {
                            echo $l_strSql;
                            die("<br>error in sql");
                        }
                    } else {
                        echo $l_strSql;
                        die("<br>error in sql");
                    }
                }
            }
        }

        return $l_bRet;
    }

    /**
     * @param string  $p_title
     * @param string  $p_const
     * @param integer $p_sort
     * @param integer $p_status
     *
     * @return mixed
     * @throws isys_exception_dao
     */
    public function insert_new_objtype_group($p_title, $p_const = null, $p_sort = 65535, $p_status = C__RECORD_STATUS__NORMAL)
    {
        if ($p_const === null) {
            $p_const = "C__OBJTYPE_GROUP__" . strtoupper(str_replace('-', '_', isys_helper_upload::prepare_filename($p_title)));
        }

        $p_const = $this->generate_unique_obj_type_group_constant($p_const);

        $l_strSQL = 'INSERT INTO isys_obj_type_group SET
            isys_obj_type_group__title = ' . $this->convert_sql_text($p_title) . ',
            isys_obj_type_group__const = ' . $this->convert_sql_text($p_const) . ',
            isys_obj_type_group__sort = ' . $this->convert_sql_text($p_sort) . ',
            isys_obj_type_group__status = ' . $this->convert_sql_text($p_status) . ';';

        $l_bRet = $this->update($l_strSQL);

        if ($l_bRet) {
            $l_bRet = $this->apply_update();
        }

        $l_obj_type_group_id = $this->get_last_insert_id();

        // Update constant cache:
        if (!defined($p_const)) {
            define($p_const, $l_obj_type_group_id);
            isys_component_constant_manager::instance()
                ->clear_dcm_cache();
        }

        if ($l_bRet) {
            /*
            isys_component_signalcollection::get_instance()
                ->emit(
                    "mod.cmdb.afterObjectTypeCreated",
                    ...
                );
            */

            return $l_obj_type_group_id;
        }

        return false;
    }

    /**
     * Creates new object type.
     *
     * @param integer $p_groupid Object type group identifier
     * @param string  $p_title   Title
     * @param string  $p_const   Constant
     * @param bool    $p_selfdefined
     * @param bool    $p_container
     * @param string  $p_img_name
     * @param string  $p_icon
     * @param integer $p_sort
     * @param int     $p_status  Record status
     * @param integer $p_cats
     * @param bool    $p_show_in_tree
     * @param string  $p_sysid_prefix
     * @param integer $p_default_template
     * @param bool    $p_show_in_rack
     * @param string  $p_color
     *
     * @return mixed
     * @throws Exception
     * @throws isys_exception_dao
     */
    public function insert_new_objtype(
        $p_groupid,
        $p_title = null,
        $p_const = null,
        $p_selfdefined = true,
        $p_container = false,
        $p_img_name = null,
        $p_icon = null,
        $p_sort = 65535,
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_cats = null,
        $p_show_in_tree = true,
        $p_sysid_prefix = null,
        $p_default_template = null,
        $p_show_in_rack = false,
        $p_color = '#ffffff'
    ) {
        if (is_null($p_cats)) {
            $l_cats = "null";
        } else {
            $l_cats = $this->convert_sql_id($p_cats);
        }

        if (is_null($p_title)) {
            // $l_strObjTypeName = "OBJECT_TYPE__" . time();

            // @see ID-1138
            $l_strObjTypeName = isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__NEW_OBJECT_TYPE') . ' ' . isys_application::instance()->container->locales->fmt_datetime(time());
        } else {
            $l_strObjTypeName = $p_title;
        }

        if (is_null($p_const)) {
            $p_const = "C__" . strtoupper(str_replace(['-', '.'], '_', isys_helper_upload::prepare_filename($l_strObjTypeName)));
        }

        $p_const = $this->generate_unique_obj_type_constant($p_const);
        $l_img_name = "empty.png";

        // ID-3484
        if ($p_img_name !== null && !empty($p_img_name)) {
            $l_img_name = $p_img_name;
        }

        $l_strSQL = 'INSERT INTO isys_obj_type SET
            isys_obj_type__isys_obj_type_group__id = ' . $this->convert_sql_id($p_groupid) . ',
            isys_obj_type__isysgui_cats__id = ' . $this->convert_sql_id($l_cats) . ',
            isys_obj_type__default_template = ' . $this->convert_sql_id($p_default_template) . ',
            isys_obj_type__title = ' . $this->convert_sql_text($l_strObjTypeName) . ',
            isys_obj_type__selfdefined = ' . $this->convert_sql_boolean($p_selfdefined) . ',
            isys_obj_type__container = ' . $this->convert_sql_boolean($p_container) . ',
            isys_obj_type__obj_img_name = ' . $this->convert_sql_text($l_img_name) . ',
            isys_obj_type__icon = ' . $this->convert_sql_text($p_icon) . ',
            isys_obj_type__const = ' . $this->convert_sql_text($p_const) . ',
            isys_obj_type__sort = ' . $this->convert_sql_int($p_sort) . ',
            isys_obj_type__status = ' . $this->convert_sql_int($p_status) . ',
            isys_obj_type__show_in_tree = ' . $this->convert_sql_boolean($p_show_in_tree) . ',
            isys_obj_type__show_in_rack = ' . $this->convert_sql_boolean($p_show_in_rack) . ',
            isys_obj_type__color = ' . $this->convert_sql_text($p_color) . ',
            isys_obj_type__sysid_prefix = ' . $this->convert_sql_text($p_sysid_prefix) . ';';

        $l_bRet = $this->update($l_strSQL);

        if ($l_bRet) {
            $l_bRet = $this->apply_update();
        }

        $l_objTypeID = $this->get_last_insert_id();

        // Update constant cache:
        if (!defined($p_const)) {
            define($p_const, $l_objTypeID);
            isys_component_constant_manager::instance()
                ->clear_dcm_cache();
        }

        if ($l_bRet) {
            isys_component_signalcollection::get_instance()
                ->emit(
                    "mod.cmdb.afterObjectTypeCreated",
                    $l_objTypeID,
                    $p_title,
                    $p_const,
                    $p_cats,
                    $p_selfdefined,
                    $p_container,
                    $p_img_name,
                    $p_icon,
                    $p_sort,
                    $p_status,
                    $p_sysid_prefix,
                    $p_show_in_tree
                );

            return $l_objTypeID;
        }

        return false;
    }

    /**
     * Deletes an object type. You can only delete self defined
     * object types which have currently no objects in them
     *
     * @param integer $p_nID
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_dao
     * @author Niclas Potthast <npotthast@i-doit.org> - 2007-04-16
     */
    public function delete_object_type($p_nID)
    {
        // Check if there is an ID.
        if (!$p_nID >= 1) {
            throw new isys_exception_dao("Without ID you cannot delete an object");
        }

        $l_objTypeData = $this->get_object_type($p_nID);

        // Check if it's a deletable object type.
        if (!$this->is_object_type_deletable($p_nID)) {
            throw new Exception("Object type: " . isys_application::instance()->container->get('language')
                    ->get($l_objTypeData['isys_obj_type__title']) . " is not self-defined.");
        }

        // Check if there are objects of this type.
        $objectsByTypeRes = $this->get_objects_by_type_id($p_nID, null, 5);
        if (is_countable($objectsByTypeRes) && count($objectsByTypeRes)) {
            throw new Exception("There are objects of " . isys_application::instance()->container->get('language')
                    ->get($l_objTypeData['isys_obj_type__title']) . " existing! Delete them in order to delete this type.");
        }

        $this->begin_update();

        $l_bRet = $this->update("DELETE FROM isys_obj_type_2_isysgui_catg WHERE isys_obj_type_2_isysgui_catg__isys_obj_type__id = " . $this->convert_sql_id($p_nID) . ";");

        if ($l_bRet) {
            $l_bRet = $this->update("DELETE FROM isys_obj_type WHERE isys_obj_type__id = " . $this->convert_sql_id($p_nID) . ";");
        }

        if ($l_bRet) {
            isys_component_signalcollection::get_instance()
                ->emit("mod.cmdb.afterObjectTypeDeleted", $p_nID, $l_objTypeData);
        }

        $l_bRet = $this->apply_update();

        return $l_bRet;
    }

    /**
     * Checks if an object type is deletable.
     *
     * @param   integer $p_nID
     *
     * @return  boolean
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function is_object_type_deletable($p_nID)
    {
        $l_strSQL = "SELECT isys_obj_type__selfdefined " . "FROM isys_obj_type " . "WHERE isys_obj_type__id = '$p_nID';";
        $l_data = $this->retrieve($l_strSQL)
            ->get_row_value('isys_obj_type__selfdefined');

        if ($l_data !== null) {
            if ($l_data == "1") {
                return true;
            }
        }

        return false;
    }

    /**
     * Deletes an array of object types. True is returned if every object
     * type was deleted, false if just one could not be deleted.
     *
     * @param   array $p_arID
     *
     * @throws  Exception
     * @return  boolean
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function delete_object_types($p_arID)
    {
        $l_bRet = true;

        foreach ($p_arID as $l_val) {
            if ($l_bRet) {
                try {
                    $l_bRet = $this->delete_object_type($l_val);
                } catch (Exception $e) {
                    throw $e;
                }
            } else {
                break;
            }
        }

        return $l_bRet;
    }

    /**
     * Change the status of an object (such as server, switch, monitor, ...).
     *
     * @param   integer $p_object_id
     * @param   integer $p_direction
     * @param   string  $p_table
     * @param   array   $p_checkMethod
     * @param   boolean $p_purge
     *
     * @return  boolean
     * @throws  Exception
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_dao
     * @throws  isys_exception_database
     * @throws  isys_exception_general
     */
    public function rank_record($p_object_id, $p_direction, $p_table, $p_checkMethod = null, $p_purge = false)
    {
        $l_deleted = false;
        $l_intTargetRecStatus = C__RECORD_STATUS__NORMAL;
        $l_category = $l_category_id = null;
        $l_category_type = null;
        $l_relation_object = 0;
        $l_nObjID = $p_object_id;
        $database = isys_application::instance()->container->get('database');

        if ($p_table === 'isys_obj') {
            try {
                $session = isys_application::instance()->container->get('session');
            } catch (Exception $e) {
                global $g_comp_session;

                $session = $g_comp_session;
            }

            // @see  ID-6222  Prevent the user from ranking himself/herself.
            if ($p_direction == C__CMDB__RANK__DIRECTION_DELETE && $session instanceof isys_component_session && $p_object_id == $session->get_user_id()) {
                throw new isys_exception_general('You can not rank your own user while logged in.');
            }

            $objectRow = $this
                ->retrieve('SELECT isys_obj__undeletable, isys_obj__const FROM isys_obj WHERE isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ';')
                ->get_row();

            // @see  ID-6222  Prevent the user from ranking the admin user.
            if ($p_direction == C__CMDB__RANK__DIRECTION_DELETE && $objectRow['isys_obj__const'] === 'C__OBJ__PERSON_ADMIN') {
                throw new isys_exception_general('You can not rank the admin user.');
            }

            // @see  ID-5261  Moving the "purge check" logic up here, because we need to skip every logic.
            if ($p_purge && ($objectRow['isys_obj__undeletable'] == 1 || $objectRow['isys_obj__const'] != null)) {
                throw new isys_exception_general('Can not delete undeletable system objects.');
            }
        }

        if (method_exists($this, 'rank_element')) {
            /*
             * Category specific deletion / archiving handling.
             * Use rank_element in your category DAO class to override the default behaviour!!
             */
            return $this->rank_element($p_object_id, $p_direction, $p_table);
        }

        $l_strConstEvent = '';
        $l_connector_id = 0;
        $l_cable_connection_id = 0;
        $l_relation_id = 0;
        $connectionId = null;

        $l_bRet = false;

        $l_strSQL = "SELECT * FROM " . $p_table . " WHERE " . $p_table . "__id = " . $this->convert_sql_id($p_object_id) . ";";

        $l_ret = $this->retrieve($l_strSQL);

        if ($l_ret && $l_ret->num_rows()) {
            // Get status
            $l_row = $l_ret->get_row();
            $l_ret->free_result();

            $l_intActRecStatus = $l_row[$p_table . "__status"];

            if ($p_purge === true) {
                $l_intActRecStatus = C__RECORD_STATUS__DELETED;
            }

            // Check and set the target __status
            switch ($l_intActRecStatus) {
                case C__RECORD_STATUS__BIRTH:
                    if ($p_direction == C__CMDB__RANK__DIRECTION_DELETE) {
                        $l_intTargetRecStatus = C__RECORD_STATUS__DELETED;
                        if ($p_table === 'isys_obj') {
                            $l_strConstEvent = "C__LOGBOOK_EVENT__OBJECT_DELETED";
                        } else {
                            $l_strConstEvent = "C__LOGBOOK_EVENT__CATEGORY_DELETED";
                        }
                        $l_startOperation = true;
                    } else {
                        $l_startOperation = false;
                    }

                    break;
                case C__RECORD_STATUS__NORMAL:
                    if ($p_direction == C__CMDB__RANK__DIRECTION_DELETE) {
                        $l_intTargetRecStatus = C__RECORD_STATUS__ARCHIVED;
                        if ($p_table === 'isys_obj') {
                            $l_strConstEvent = "C__LOGBOOK_EVENT__OBJECT_ARCHIVED";
                        } else {
                            $l_strConstEvent = "C__LOGBOOK_EVENT__CATEGORY_ARCHIVED";
                        }
                        $l_startOperation = true;
                    } else {
                        $l_startOperation = false;
                    }
                    break;
                case C__RECORD_STATUS__ARCHIVED:
                    if ($p_direction == C__CMDB__RANK__DIRECTION_DELETE) {
                        $l_intTargetRecStatus = C__RECORD_STATUS__DELETED;
                        if ($p_table === 'isys_obj') {
                            $l_strConstEvent = "C__LOGBOOK_EVENT__OBJECT_DELETED";
                        } else {
                            $l_strConstEvent = "C__LOGBOOK_EVENT__CATEGORY_DELETED";
                        }
                        $l_startOperation = true;
                    } else {
                        $l_intTargetRecStatus = C__RECORD_STATUS__NORMAL;
                        if ($p_table === 'isys_obj') {
                            $l_strConstEvent = "C__LOGBOOK_EVENT__OBJECT_RECYCLED";
                        } else {
                            $l_strConstEvent = "C__LOGBOOK_EVENT__CATEGORY_RECYCLED";
                        }
                        $l_startOperation = true;
                    }
                    break;
                case C__RECORD_STATUS__DELETED:
                    if ($p_direction == C__CMDB__RANK__DIRECTION_DELETE) {
                        $l_intTargetRecStatus = C__RECORD_STATUS__PURGE;
                        if ($p_table === 'isys_obj') {
                            $l_strConstEvent = "C__LOGBOOK_EVENT__OBJECT_PURGED";
                        } else {
                            $l_strConstEvent = "C__LOGBOOK_EVENT__CATEGORY_PURGED";
                        }
                        $l_startOperation = true;
                    } else {
                        $l_intTargetRecStatus = C__RECORD_STATUS__ARCHIVED;
                        if ($p_table === 'isys_obj') {
                            $l_strConstEvent = "C__LOGBOOK_EVENT__OBJECT_RECYCLED";
                        } else {
                            $l_strConstEvent = "C__LOGBOOK_EVENT__CATEGORY_RECYCLED";
                        }
                        $l_startOperation = true;
                    }
                    break;
                default:
                    $l_startOperation = false;
                    $l_intTargetRecStatus = C__RECORD_STATUS__NORMAL;
                    break;
            }

            if ($p_checkMethod !== null) {
                /* You can integrate an external checking method, please care
                   that $p_checkMethod is an array with object and method name! */
                [$l_obj, $l_method] = $p_checkMethod;
                if (is_object($l_obj) && $l_startOperation) {
                    $l_startOperation = $l_obj->$l_method($p_direction, $l_intTargetRecStatus);
                }
            }

            if ($l_startOperation) {
                $l_strSQL = null;

                if ($p_table !== "isys_obj") {
                    $l_categoryEntryID = $p_object_id;

                    if (isset($_GET[C__CMDB__GET__OBJECT])) {
                        $l_nObjID = $_GET[C__CMDB__GET__OBJECT];
                    } elseif (isset($l_row[$p_table . '__isys_obj__id'])) {
                        $l_nObjID = $l_row[$p_table . '__isys_obj__id'];
                    }

                    //  @see  API-48 / Zendesk #1655
                    if ($p_table === 'isys_person_2_group') {
                        if ($this instanceof \isys_cmdb_dao_category_s_person_group_members) {
                            $l_nObjID = $l_row['isys_person_2_group__isys_obj__id__group'];
                        } else {
                            $l_nObjID = $l_row['isys_person_2_group__isys_obj__id__person'];
                        }
                    }

                    if (isset($_GET[C__CMDB__GET__CATG])) {
                        $l_category_id = $_GET[C__CMDB__GET__CATG];
                        $l_category_type = C__CMDB__CATEGORY__TYPE_GLOBAL;
                    } elseif (isset($_GET[C__CMDB__GET__CATS])) {
                        $l_category_id = $_GET[C__CMDB__GET__CATS];
                        $l_category_type = C__CMDB__CATEGORY__TYPE_SPECIFIC;
                    } elseif (method_exists($this, 'get_category_id')) {
                        $l_category_id = $this->get_category_id();
                        if (method_exists($this, 'get_category_type')) {
                            $l_category_type = $this->get_category_type();
                        }
                    }

                    if ($l_category_type !== null) {
                        switch ($l_category_type) {
                            case C__CMDB__CATEGORY__TYPE_GLOBAL:
                                $l_category = $this->get_catg_name_by_id_as_string($l_category_id);
                                break;
                            case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                                $l_category = $this->get_cats_name_by_id_as_string($l_category_id);
                                break;
                        }
                    }

                    $l_entryTitle = null;
                    if (method_exists($this, 'get_entry_identifier')) {
                        $l_entryTitle = $this->get_entry_identifier($l_row);
                    }
                } else {
                    $l_entryTitle = $l_row['isys_obj__title'];
                    $l_categoryEntryID = null;
                }

                $mappingCategory = [
                    C__RECORD_STATUS__DELETED => Context::CONTEXT_RANK_CATEGORY_DELETED,
                    C__RECORD_STATUS__PURGE => Context::CONTEXT_RANK_CATEGORY_PURGED,
                    C__RECORD_STATUS__ARCHIVED => Context::CONTEXT_RANK_CATEGORY_ARCHIVED,
                    C__RECORD_STATUS__NORMAL => Context::CONTEXT_RANK_CATEGORY_RECYCLED
                ];

                $mappingObject = [
                    C__RECORD_STATUS__DELETED => Context::CONTEXT_RANK_OBJECT_DELETED,
                    C__RECORD_STATUS__PURGE => Context::CONTEXT_RANK_OBJECT_PURGED,
                    C__RECORD_STATUS__ARCHIVED => Context::CONTEXT_RANK_OBJECT_ARCHIVED,
                    C__RECORD_STATUS__NORMAL => Context::CONTEXT_RANK_OBJECT_RECYCLED
                ];

                Context::instance()
                    ->setContextTechnical(($l_categoryEntryID === null ? Context::CONTEXT_RANK_OBJECT : Context::CONTEXT_RANK_CATEGORY))
                    ->setGroup(Context::CONTEXT_GROUP_DAO)
                    ->setContextCustomer(($l_categoryEntryID === null ? $mappingObject[$l_intTargetRecStatus] : $mappingCategory[$l_intTargetRecStatus]));

                /**
                 * Emit mod.cmdb.beforeRankRecord before ranking the object/category entry
                 */
                isys_component_signalcollection::get_instance()
                    ->emit(
                        "mod.cmdb.beforeRankRecord",
                        $this,
                        $l_nObjID,
                        $l_categoryEntryID,
                        $l_entryTitle,
                        $l_row,
                        $p_table,
                        $l_intActRecStatus,
                        $l_intTargetRecStatus,
                        $l_category_type,
                        $p_direction
                    );

                /**
                 * Determine relation object
                 */
                $l_dao_relation = new isys_cmdb_dao_category_g_relation($database);

                if (is_a($this, "isys_cmdb_dao_category") || $p_table === "isys_obj") {
                    if (isset($l_row[$p_table . "__isys_catg_connector_list__id"]) || $l_row['isys_obj__isys_obj_type__id'] == defined_or_default('C__OBJTYPE__CABLE')) {
                        $l_dao_cc = new isys_cmdb_dao_cable_connection($database);
                        if (isset($l_row[$p_table . "__isys_catg_connector_list__id"])) {
                            $l_dao = new isys_cmdb_dao_category_g_connector($database);
                            $l_data = $l_dao->get_data($l_row[$p_table . "__isys_catg_connector_list__id"])
                                ->__to_array();

                            $l_relation_id = $l_data["isys_catg_connector_list__isys_catg_relation_list__id"];
                            $l_relation_object = $l_dao_relation->get_object_id_by_category_id($l_relation_id, "isys_catg_relation_list");
                            $l_connector_id = $l_data["isys_catg_connector_list__id"];
                            $l_cable_connection_id = $l_dao_cc->get_cable_connection_id_by_connector_id($l_data["isys_catg_connector_list__id"]);
                        } else {
                            $l_cable_connection_id = $l_dao_cc->get_cable_connection_id_by_cable_id($l_row['isys_obj__id']);
                        }
                    } elseif (isset($l_row[$p_table . "__isys_catg_relation_list__id"])) {
                        $l_relation_id = $l_row[$p_table . "__isys_catg_relation_list__id"];
                        $l_relation_object = $l_dao_relation->get_object_id_by_category_id($l_relation_id, "isys_catg_relation_list");
                    } elseif ($l_row["isys_catg_relation_list__id"]) {
                        $l_relation_id = $l_row["isys_catg_relation_list__id"];
                        $l_relation_object = $l_row["isys_catg_relation_list__isys_obj__id"];
                    }

                    if ($l_row[$p_table . '__isys_connection__id']) {
                        $connectionId = $l_row[$p_table . '__isys_connection__id'];
                    }
                }

                switch ($l_intTargetRecStatus) {
                    // Objects/category is getting purged.
                    case C__NAVMODE__QUICK_PURGE:
                    case C__RECORD_STATUS__PURGE:

                        // Delete cable connection.
                        if ($l_cable_connection_id > 0 && isset($l_dao_cc)) {
                            $l_dao_cc->delete_cable_connection($l_cable_connection_id);
                        }

                        if ($connectionId !== null) {
                            isys_cmdb_dao_connection::instance($database)->delete($connectionId);
                        }

                        if ($p_table === "isys_obj") {
                            // Check if object is a system object or not
                            if ($l_row['isys_obj__undeletable'] == 1 || $l_row['isys_obj__const'] != null) {
                                throw new isys_exception_general("Can not delete undeletable system objects.");
                            }

                            // Delete the relation object(s) first.
                            $l_relation_object = $l_dao_relation->get_data(null, $l_nObjID);
                            if ($l_relation_object !== null) {
                                while ($l_row = $l_relation_object->get_row()) {
                                    if ($l_row["isys_catg_relation_list__isys_obj__id"] > 0 && !isset($l_done[$l_row["isys_catg_relation_list__isys_obj__id"]])) {
                                        $this->delete_object_and_relations($l_row["isys_catg_relation_list__isys_obj__id"]);
                                        $l_done[$l_row["isys_catg_relation_list__isys_obj__id"]] = true;
                                    }
                                }
                            }

                            // Delete file(s) first.
                            $l_dao_file = isys_cmdb_dao_category_s_file_version::instance($database);
                            $l_res = $l_dao_file->get_data(null, $l_nObjID);
                            if ($l_res->num_rows() > 0) {
                                while ($l_row = $l_res->get_row()) {
                                    $l_dao_file->rank_records($l_row['isys_file_version__id']);
                                }
                            }

                            //  Then delete the object.
                            $l_category = $this->get_obj_name_by_id_as_string($l_nObjID);
                            $l_deleted = $this->delete_object($l_nObjID);
                            $l_nObjID = null;
                        } else {
                            // Delete relation.
                            if ($l_relation_id > 0) {
                                $l_dao_relation->delete_relation($l_relation_id);
                            }

                            // Delete connector.
                            if ($l_connector_id > 0 && isset($l_dao_cc)) {
                                $l_dao_cc->delete_connector($l_connector_id);
                            }

                            // Delete category entry.
                            $l_strSQL = "DELETE FROM " . $p_table . " WHERE " . $p_table . "__id='" . $p_object_id . "';";
                        }

                        break;
                    // Default = Deleting or recycling in this case.
                    default:
                        // Prepare sql statement for updating the category/object entry.
                        $l_strSQL = "UPDATE {$p_table}
                            SET {$p_table}__status = " . $this->convert_sql_int($l_intTargetRecStatus) . "
                            WHERE {$p_table}__id = " . $this->convert_sql_id($p_object_id) . ';';

                        // Update connector.
                        if ($l_connector_id > 0) {
                            $l_sql = 'UPDATE isys_catg_connector_list 
                                SET isys_catg_connector_list__status = ' . $this->convert_sql_int($l_intTargetRecStatus) . '
                                WHERE isys_catg_connector_list__id = ' . $this->convert_sql_id($l_connector_id) . ";";

                            $l_bRet = $this->update($l_sql);
                        }

                        // Archive/recycle relation.
                        if ($l_relation_object > 0) {
                            $this->set_object_status($l_relation_object, $l_intTargetRecStatus);

                            if ($l_relation_id > 0) {
                                $l_sql = 'UPDATE isys_catg_relation_list 
                                    SET isys_catg_relation_list__status = ' . $this->convert_sql_int($l_intTargetRecStatus) . '
                                    WHERE isys_catg_relation_list__id = ' . $this->convert_sql_id($l_relation_id) . ";";

                                $this->update($l_sql);
                            }
                        }

                        break;
                }

                if ($l_strSQL || $l_deleted) {
                    if (!$l_deleted) {
                        $l_bRet = $this->update($l_strSQL);

                        if ($l_bRet && !$l_deleted) {
                            $l_bRet = $this->apply_update();
                        }
                    } else {
                        $l_bRet = true;
                    }

                    $l_strConstEvent = (!$l_bRet) ? $l_strConstEvent . "__NOT" : $l_strConstEvent;

                    $this->logbook_rank($l_nObjID, $l_strConstEvent, $l_strSQL, $l_category, $l_entryTitle);
                }
            } else {
                // We don't start the operation, so true can be returned, since "nothing happened".
                return true;
            }
        }

        return $l_bRet;
    }

    /**
     * Create logbook entry on ranking category entries.
     *
     * @param  integer $p_objID
     * @param  string  $p_strConst
     * @param  string  $p_strSql
     * @param  string  $p_lc_category
     * @param  string  $p_entry_identifier
     */
    public function logbook_rank($p_objID, $p_strConst, $p_strSql, $p_lc_category, $p_entry_identifier = null)
    {
        if (isset($_GET[C__CMDB__GET__OBJECTTYPE])) {
            $l_objtype = $_GET[C__CMDB__GET__OBJECTTYPE];
        } else {
            $l_objtype = $this->get_objTypeID($p_objID);
        }

        if (isset($_GET[C__CMDB__GET__OBJECT])) {
            $l_obj_id = $_GET[C__CMDB__GET__OBJECT];
        } else {
            $l_obj_id = $p_objID;
        }
        isys_event_manager::getInstance()
            ->triggerCMDBEvent($p_strConst, $p_strSql, $l_obj_id, $l_objtype, $p_lc_category, null, null, null, null, $p_entry_identifier);
    }

    /**
     * Method for deleting an object.
     *
     * Remember that this function can be really slow because every non handable foreign key constraints in isys_connection
     * gets deleted here with PHP. This produces a big JOIN while deleting.
     *
     * @param   integer $p_objID
     *
     * @return  boolean
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     * @throws  Exception|isys_exception_cmdb
     */
    public function delete_object($p_objID)
    {
        if (!is_numeric($p_objID)) {
            throw new isys_exception_cmdb("Object-ID " . $p_objID . " not numeric!");
        }

        $this->begin_update();

        try {
            // First we delete entries from related tables.
            $l_sql = 'DELETE isys_cats_net_ip_addresses_list 
                FROM isys_cats_net_ip_addresses_list 
                INNER JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id 
                WHERE isys_catg_ip_list__isys_obj__id = ' . $this->convert_sql_id($p_objID) . ';';

            $this->update($l_sql);

            $l_del_references = [
                'isys_catg_backup_list',
                'isys_catg_emergency_plan_list',
                'isys_catg_file_list',
                'isys_catg_application_list',
                'isys_catg_manual_list',
                'isys_catg_cluster_list',
                'isys_catg_cluster_members_list',
                'isys_catg_cluster_service_list',
                'isys_catg_connector_list',
                'isys_catg_contact_list',
                'isys_catg_guest_systems_list',
                'isys_catg_ip_list',
                'isys_catg_sanpool_list',
                'isys_catg_virtual_machine_list',
                'isys_cats_group_list',
                'isys_cats_organization_list',
                'isys_cats_person_list',
                'isys_catg_database_assignment_list',
                'isys_cats_database_access_list',
                'isys_cats_database_gateway_list',
                'isys_cats_replication_partner_list',
                'isys_catg_soa_stacks_list'
            ];

            // Then, check for dead connections.
            foreach ($l_del_references as $l_ref) {
                $l_sql = 'DELETE isys_connection 
                    FROM isys_connection 
                    INNER JOIN ' . $l_ref . ' ON isys_connection__id = ' . $l_ref . '__isys_connection__id
                    WHERE ' . $l_ref . '__isys_obj__id = ' . $this->convert_sql_id($p_objID) . ';';

                $this->update($l_sql);
            }

            // And then delete the object.
            $l_sql = 'DELETE FROM isys_obj WHERE isys_obj__id = ' . $this->convert_sql_id($p_objID) . ' AND isys_obj__undeletable = 0;';

            /* Emit objectDeleted signal */
            isys_component_signalcollection::get_instance()
                ->emit('mod.cmdb.objectDeleted', $p_objID, $this);

            if (!$this->update($l_sql)) {
                $this->cancel_update();
                throw new Exception("Could not delete object. This may be an undeletable system object.");
            }

            $this->apply_update();
        } catch (Exception $e) {
            $this->cancel_update();
            throw new Exception($e->getMessage());
        }

        return true;
    }

    /**
     * Deletes relation objects recursive
     *
     * @param int $p_objID
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_cmdb
     */
    public function delete_object_and_relations($p_objID)
    {
        $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->get_database_component());

        if (!is_numeric($p_objID)) {
            throw new isys_exception_cmdb("Object-ID " . $p_objID . " not numeric!");
        }

        try {
            $l_object = $l_dao_relation->get_data(null, $p_objID);
            $l_objects_to_delete = [];

            if ($l_object->num_rows() > 0) {
                while ($l_row = $l_object->get_row()) {
                    if ($l_row["isys_catg_relation_list__isys_obj__id"] > 0) {
                        $l_object_sub = $l_dao_relation->get_data(null, $l_row["isys_catg_relation_list__isys_obj__id"]);
                        if ($l_object_sub->num_rows() > 0) {
                            while ($l_row_sub = $l_object_sub->get_row()) {
                                if ($l_row_sub["isys_catg_relation_list__isys_obj__id"] != $p_objID) {
                                    $this->delete_object_and_relations($l_row_sub["isys_catg_relation_list__isys_obj__id"]);
                                }
                            }
                        }

                        $l_objects_to_delete[] = $this->convert_sql_id($l_row["isys_catg_relation_list__isys_obj__id"]);
                    }
                }
            }

            $l_objects_to_delete[] = $p_objID;

            // First we delete entries from related tables.
            $l_sql = 'DELETE isys_cats_net_ip_addresses_list 
                FROM isys_cats_net_ip_addresses_list 
                INNER JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id 
                WHERE isys_catg_ip_list__isys_obj__id IN(' . implode(',', $l_objects_to_delete) . ');';

            $this->update($l_sql);

            /**
             * Call raw object deletion
             *
             * Don't call $this->delete_object() here because in that case every possible isys_connection__isys_obj reference
             * is also deleted which results in an extremly slow performance. And because isys_connection references are not used
             * for relations it is no problem to just delete the object like this:
             */
            $l_sql = 'DELETE FROM isys_obj 
                WHERE isys_obj__id IN(' . implode(',', $l_objects_to_delete) . ') 
                AND isys_obj__undeletable = 0;';

            $this->update($l_sql);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return true;
    }

    /**
     * Update object change information.
     *
     * @param   integer|array $p_object_id
     * @param   string        $p_changed_by
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function object_changed($p_object_id, $p_changed_by = null)
    {
        if ($p_changed_by === null || empty($p_changed_by)) {
            $p_changed_by = isys_application::instance()->session->get_current_username();
        }

        if (is_array($p_object_id) && count($p_object_id)) {
            $l_condition = ' IN (' . implode(',', $p_object_id) . ') ';

            isys_component_signalcollection::get_instance()
                ->emit('mod.cmdb.objectChanged', $p_object_id, $p_changed_by);
        } else {
            $l_condition = ' = ' . $this->convert_sql_id($p_object_id) . ' ';

            isys_component_signalcollection::get_instance()
                ->emit('mod.cmdb.objectChanged', [$p_object_id], $p_changed_by);
        }

        // @see  ID-6913  No need to empty the quick info cache here, that will be taken care of by a signal callback.

        $l_sql = 'UPDATE isys_obj SET 
            isys_obj__updated = NOW(), 
            isys_obj__updated_by = ' . $this->convert_sql_text($p_changed_by) . ' 
            WHERE isys_obj__id ' . $l_condition . ';';

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * Empty Quickinfo cache in DB for specified object ids
     *
     * @param int|array $objectIds
     *
     * @return bool
     * @throws isys_exception_dao
     * @deprecated Please use `idoit\Model\Dao\Base\QuickInfo->deleteCache()` directly
     */
    public function emptyCacheQinfo($objectIds)
    {
        return QuickInfo::instance($this->m_db)->deleteCache($objectIds);
    }

    /**
     * Rank records (archive,delete,purge,recycle,..).
     *
     * @param   array   $p_objects
     * @param   integer $p_direction
     * @param   string  $p_table
     * @param   string  $p_checkMethod
     * @param   boolean $p_purge
     *
     * @return  boolean
     */
    public function rank_records($p_objects, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        if (is_array($p_objects) && count($p_objects)) {
            foreach ($p_objects as $l_objid) {
                if (method_exists($this, "pre_rank")) {
                    $this->pre_rank($l_objid, $p_direction, $p_table, $p_checkMethod);
                }

                if ($this->rank_record($l_objid, $p_direction, $p_table, $p_checkMethod, $p_purge) == false) {
                    // Cannot rank record - bad.
                    return false;
                }

                if (method_exists($this, "post_rank")) {
                    $this->post_rank($l_objid, $p_direction, $p_table, $p_checkMethod);
                }
            }

            // All records processed - good.
            return true;
        }

        // Wrong parameters - bad.
        return false;
    }

    /**
     * Returns the id of the configured default template.
     *
     * @param   integer $p_obj_type_id
     *
     * @return  integer
     */
    public function get_default_template_by_obj_type($p_obj_type_id)
    {
        $l_objtype_data = $this->get_type_by_id($p_obj_type_id);

        return $l_objtype_data["isys_obj_type__default_template"];
    }

    /**
     * Inserts a new obj, creates catd, catg.
     *
     * @param   integer $p_obj_type_id
     * @param   mixed   $p_unused
     * @param   string  $p_strTitle
     * @param   string  $p_strSYSID
     * @param int       $p_record_status
     * @param   string  $p_hostname
     * @param   integer $p_scantime
     * @param   boolean $p_import_date
     * @param   string  $p_created
     * @param   string  $p_created_by
     * @param   string  $p_updated
     * @param   string  $p_updated_by
     * @param   integer $p_category
     * @param   integer $p_purpose
     * @param   integer $p_cmdb_status
     *
     * @param null      $p_description
     *
     * @return int
     * @throws Exception
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     */
    public function insert_new_obj(
        $p_obj_type_id,
        $p_unused = null,
        $p_strTitle = null,
        $p_strSYSID = null,
        $p_record_status = C__RECORD_STATUS__BIRTH,
        $p_hostname = null,
        $p_scantime = null,
        $p_import_date = false,
        $p_created = null,
        $p_created_by = null,
        $p_updated = null,
        $p_updated_by = null,
        $p_category = null,
        $p_purpose = null,
        $p_cmdb_status = null,
        $p_description = null
    ) {
        $l_username = isys_application::instance()->session->get_current_username();

        $l_sourceTableCats = null;

        $l_strSYSID = trim($p_strSYSID);

        // Insert object.
        if (!is_numeric($p_obj_type_id)) {
            throw new isys_exception_cmdb("Object type invalid! Object not created.");
        }

        // Get creation stats.
        $l_created = $p_created === null ? 'NOW()' : $this->convert_sql_datetime($p_created);
        $l_created_by = $p_created_by === null ? $l_username : $p_created_by;

        // Get update stats.
        $l_updated_by = $p_updated_by === null ? $l_username : $p_updated_by;
        $l_updated = $p_updated === null ? 'NOW()' : $this->convert_sql_datetime($p_updated);

        $l_scantime = '';
        $l_imported = 'NULL';

        if ($p_scantime != null) {
            $l_scanstamp = strtotime($p_scantime);
            $l_scantime = date("Y-m-d H:i:s", $l_scanstamp);
        }

        if ($p_import_date) {
            $l_imported = 'NOW()';
        }

        // Inserting object.

        // Retrieve a new name, if the given one is already existing and the "unique" check is switched on.
        $p_strTitle = $this->generate_unique_obj_title($p_strTitle);

        // Retrieve CMDB Status.
        if (!defined('C__CMDB_STATUS__IN_OPERATION')) {
            $l_status_dao = new isys_cmdb_dao_status($this->m_db);
            define('C__CMDB_STATUS__IN_OPERATION', $l_status_dao->get_cmdb_status_by_const_as_int('C__CMDB_STATUS__IN_OPERATION'));
        }

        $l_cmdb_status = ($p_cmdb_status > 0) ? $p_cmdb_status : defined_or_default('C__CMDB_STATUS__IN_OPERATION');

        // sql for creating object
        $l_strSQL = "INSERT INTO isys_obj (isys_obj__title, isys_obj__isys_obj_type__id, isys_obj__isys_cmdb_status__id, isys_obj__status, " .
            "isys_obj__sysid, isys_obj__created, isys_obj__created_by, isys_obj__updated, isys_obj__updated_by, isys_obj__hostname," .
            "isys_obj__scantime, isys_obj__imported, isys_obj__description, isys_obj__owner_id) VALUES (" . $this->convert_sql_text($p_strTitle) . ", " .
            $this->convert_sql_id($p_obj_type_id) . ", " . $this->convert_sql_id($l_cmdb_status) . ", " . $this->convert_sql_id($p_record_status) . ", " .
            $this->convert_sql_text($l_strSYSID) . ", " . "{$l_created}, '{$l_created_by}', {$l_updated}, '{$l_updated_by}'," . $this->convert_sql_text($p_hostname) . ", " .
            $this->convert_sql_datetime($l_scantime) . ", " . $this->convert_sql_datetime($l_imported) . "," . $this->convert_sql_text($p_description) . ", " .
            $this->convert_sql_id(isys_application::instance()->session->get_user_id()) . ");";

        if ($l_bRet = $this->update($l_strSQL)) {
            $l_object_id = $this->get_last_insert_id();

            // Set sysid.
            if (empty($l_strSYSID)) {
                $l_strSYSID = $this->generate_sysid($p_obj_type_id, $l_object_id);

                $l_strSQL = 'UPDATE isys_obj SET isys_obj__sysid = ' . $this->convert_sql_text($l_strSYSID) . ' WHERE isys_obj__id = ' . $this->convert_sql_id($l_object_id) .
                    ';';

                if (!$this->update($l_strSQL)) {
                    throw new isys_exception_cmdb("Object creation failed. Unable to set SYSID.");
                }
            }

            // Cache last sysid
            self::$m_last_sysid = $l_strSYSID;

            if (($l_auto_inventory = trim(isys_tenantsettings::get('cmdb.objtype.' . $p_obj_type_id . '.auto-inventory-no', ''))) !== '') {
                // Just instantiate class because we only need the signal
                isys_cmdb_dao_category_g_accounting::instance($this->get_database_component());
            }
        } else {
            throw new isys_exception_cmdb("Object creation failed. (INSERT INTO isys_obj)");
        }

        $l_strSQL = "INSERT INTO isys_catg_global_list (" . "isys_catg_global_list__isys_catg_global_category__id, " . "isys_catg_global_list__isys_purpose__id, " .
            "isys_catg_global_list__isys_obj__id, " . "isys_catg_global_list__description, " . "isys_catg_global_list__status) " . "VALUES (" .
            $this->convert_sql_id($p_category) . ", " . $this->convert_sql_id($p_purpose) . ", " . $this->convert_sql_id($l_object_id) . ", " . "''," . "'" .
            C__RECORD_STATUS__NORMAL . "'" . ");";

        $l_bRet = $this->update($l_strSQL);

        if ($l_bRet) {
            // Create specific category entries for our special object types.
            if ($p_obj_type_id == defined_or_default('C__OBJTYPE__PERSON')) {
                $l_names = explode(' ', $p_strTitle);
                $l_dao_person = new isys_cmdb_dao_category_s_person_master($this->get_database_component());
                $l_dao_person->create(
                    $l_object_id,
                    '',
                    $l_names[0],
                    (isset($l_names[1]) ? str_replace('_', ' ', $l_names[1]) : ''),
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $p_description
                );
            } elseif ($p_obj_type_id == defined_or_default('C__OBJTYPE__ORGANIZATION')) {
                $l_dao_orga = new isys_cmdb_dao_category_s_organization_master($this->get_database_component());
                $l_dao_orga->create($l_object_id, C__RECORD_STATUS__NORMAL, $p_strTitle, '', '', '', '', '', '', '', null, null, $p_description);
            } elseif ($p_obj_type_id == defined_or_default('C__OBJTYPE__PERSON_GROUP')) {
                $l_dao_group = new isys_cmdb_dao_category_s_person_group_master($this->get_database_component());
                $l_dao_group->create($l_object_id, $p_strTitle);
            }

            $this->apply_update();
        }

        /* Emit createObject signal */
        isys_component_signalcollection::get_instance()
            ->emit('mod.cmdb.objectCreated', $l_object_id, $l_strSYSID, $p_obj_type_id, $p_strTitle, $l_cmdb_status, $l_username);

        return $l_object_id;
    }

    /**
     * Method for generating a SYS-ID.
     *
     * @param   integer $objectTypeId
     * @param   integer $objectId
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function generate_sysid($objectTypeId, $objectId = null)
    {
        if ($objectId === null) {
            $objectId = rand(0, 999);
        }

        $objectTypeRow = $this->get_type_by_id($objectTypeId);

        $sysIdPrefix = $objectTypeRow['isys_obj_type__sysid_prefix'] ?: C__CMDB__SYSID__PREFIX;
        $sysIdSuffix = (int)$objectId;

        if ($sysIdPrefix === C__CMDB__SYSID__PREFIX) {
            $sysIdSuffix += time();
        }

        $sysId = $sysIdPrefix . $sysIdSuffix;

        if (mb_strlen($sysId) < 13) {
            $sysId = $sysIdPrefix . str_pad($sysIdSuffix, (13 - mb_strlen($sysIdPrefix)), '0', STR_PAD_LEFT);
        }

        while ($this->get_obj_id_by_sysid($sysId)) {
            $sysIdSuffix++;
            $sysId = $sysIdPrefix . $sysIdSuffix;

            if (mb_strlen($sysId) < 13) {
                $sysId = $sysIdPrefix . str_pad($sysIdSuffix, (13 - mb_strlen($sysIdPrefix)), '0', STR_PAD_LEFT);
            }
        }

        return $sysId;
    }

    /**
     * Generates a unique object title, when "$g_unique_check['object_title']" is set to true.
     *
     * @param   string  $p_obj_title
     * @param   integer $p_object_id
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function generate_unique_obj_title($p_obj_title, $p_object_id = null)
    {
        if (isys_tenantsettings::get('cmdb.unique.object-title') && !empty($p_obj_title)) {
            $l_title = $p_obj_title;
            $l_cnt = 1;

            // The found object may not be the same... Because that would be stupid.
            while (($l_found = $this->get_obj_id_by_title($l_title)) != $p_object_id && $l_found > 0) {
                $l_title = $p_obj_title . ' #' . $l_cnt;
                $l_cnt++;
            }

            $p_obj_title = $l_title;
        }

        return $p_obj_title;
    }

    /**
     * Method for creating a unique object type group constant.
     *
     * @param   string  $p_obj_type_group_const
     * @param   integer $p_obj_type_group_id
     *
     * @return  string
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function generate_unique_obj_type_group_constant($p_obj_type_group_const, $p_obj_type_group_id = null)
    {
        $l_obj_type_const = $p_obj_type_group_const;
        $l_cnt = 1;
        $l_condition = ($p_obj_type_group_id === null ? '' : ' AND isys_obj_type_group__id != ' . $this->convert_sql_id($p_obj_type_group_id));

        while (($l_found = $this->retrieve('SELECT isys_obj_type_group__id FROM isys_obj_type_group WHERE isys_obj_type_group__const = ' .
            $this->convert_sql_text($l_obj_type_const) . $l_condition . ';')
            ->get_row_value('isys_obj_type_group__id'))) {
            $l_obj_type_const = $p_obj_type_group_const . $l_cnt;
            $l_cnt++;
        }

        return $l_obj_type_const;
    }

    /**
     * Method for creating a unique object type constant.
     *
     * @param   string  $p_obj_type_const
     * @param   integer $p_obj_type_id
     *
     * @return  string
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function generate_unique_obj_type_constant($p_obj_type_const, $p_obj_type_id = null)
    {
        $l_obj_type_const = $p_obj_type_const;
        $l_cnt = 1;
        $l_condition = ($p_obj_type_id === null ? '' : ' AND isys_obj_type__id != ' . $this->convert_sql_id($p_obj_type_id));

        while (($l_found = $this->retrieve('SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__const = ' . $this->convert_sql_text($l_obj_type_const) .
            $l_condition . ';')
            ->get_row_value('isys_obj_type__id'))) {
            $l_obj_type_const = $p_obj_type_const . $l_cnt;
            $l_cnt++;
        }

        $sanitizedConstant = str_replace(' ', '_', strtoupper(isys_glob_strip_accent(isys_glob_replace_accent($l_obj_type_const))));

        if ($sanitizedConstant != $l_obj_type_const) {
            isys_application::instance()->container->notify->warning(sprintf('Constant had wrong format, changed from %s to %s.', $l_obj_type_const, $sanitizedConstant));
        }

        return $sanitizedConstant;
    }

    /**
     * Updates an existing object in database. Includes updating object's global
     * category data in CATG_GLOBAL.
     *
     * @param  integer $p_object_id
     * @param  integer $p_object_type_id
     * @param  string  $p_title
     * @param  string  $p_description
     * @param  string  $p_sysid
     * @param  integer $p_record_status
     * @param  string  $p_hostname
     * @param  integer $p_scantime
     * @param  integer $p_created
     * @param  string  $p_created_by
     * @param  integer $p_updated
     * @param  string  $p_updated_by
     * @param  integer $p_cmdb_status
     * @param  integer $p_rt_cf_id
     * @param  integer $p_category
     * @param  integer $p_purpose
     *
     * @return boolean
     */
    public function update_object(
        $p_object_id,
        $p_object_type_id = null,
        $p_title = null,
        $p_description = null,
        $p_sysid = null,
        $p_record_status = null,
        $p_hostname = null,
        $p_scantime = null,
        $p_created = null,
        $p_created_by = null,
        $p_updated = null,
        $p_updated_by = null,
        $p_cmdb_status = null,
        $p_rt_cf_id = null,
        $p_category = null,
        $p_purpose = null
    ) {
        $p_object_id = $this->convert_sql_id($p_object_id);

        // How to update:
        $l_obj_query = 'UPDATE isys_obj SET %s WHERE isys_obj__id = ' . $p_object_id;
        $l_global_query = 'UPDATE isys_catg_global_list SET %s WHERE isys_catg_global_list__isys_obj__id = ' . $p_object_id;

        // What to update:
        $l_obj_sets = [];
        $l_global_sets = [];

        // Object type identifier:
        if ($p_object_type_id !== null) {
            $l_obj_sets['isys_obj_type__id'] = $this->convert_sql_id($p_object_type_id);
        }
        // Title:
        if ($p_title !== null) {
            $p_title = $this->generate_unique_obj_title($p_title, $p_object_id);
            $l_obj_sets['title'] = $this->convert_sql_text($p_title);
        }
        // Description:
        if ($p_description !== null) {
            $l_obj_sets['description'] = $this->convert_sql_text($p_description);
        }
        // SYSID:
        if ($p_sysid !== null) {
            $l_obj_sets['sysid'] = $this->convert_sql_text($p_sysid);
        }
        // Host name:
        if ($p_hostname !== null) {
            $l_obj_sets['hostname'] = $this->convert_sql_text($p_hostname);
        }
        // Record status:
        if ($p_record_status !== null) {
            $l_obj_sets['status'] = $this->convert_sql_int($p_record_status);
        }
        // Scan time:
        if ($p_scantime !== null) {
            $l_obj_sets['scantime'] = "'" . date('Y-m-d H:i:s', $p_scantime) . "'";
        }
        // Create time:
        if ($p_created !== null) {
            $l_obj_sets['created'] = "'" . date('Y-m-d H:i:s', $p_created) . "'";
        }
        // Created by:
        if ($p_created_by !== null) {
            $l_obj_sets['created_by'] = $this->convert_sql_text($p_created_by);
        }
        // Update time:
        if ($p_updated !== null) {
            $l_obj_sets['updated'] = "'" . date('Y-m-d H:i:s') . "'";
        }
        // Updated by:
        if ($p_updated_by !== null) {
            $l_obj_sets['updated_by'] = $this->convert_sql_text($p_updated_by);
        } else {
            // Fetch current user name:
            $l_obj_sets['updated_by'] = "'" . isys_application::instance()->container->get('session')->get_current_username() . "'";
        }
        // Category:
        if ($p_category !== null) {
            $l_global_sets['isys_catg_global_category__id'] = $this->convert_sql_int($p_category);
        }
        // Purpose:
        if ($p_purpose !== null) {
            $l_global_sets['isys_purpose__id'] = $this->convert_sql_id($p_purpose);
        }
        // CMDB status:
        if ($p_cmdb_status !== null) {
            $l_obj_sets['isys_cmdb_status__id'] = $this->convert_sql_id($p_cmdb_status);
        } elseif (!($p_cmdb_status > 0)) {
            $l_obj_sets['isys_cmdb_status__id'] = $this->convert_sql_id(defined_or_default('C__CMDB_STATUS__IN_OPERATION'));
        }

        // RT CF:
        if ($p_rt_cf_id !== null) {
            $l_obj_sets['rt_cf__id'] = $this->convert_sql_id($p_rt_cf_id);
        }

        // Create SQL query for the object table:
        $l_converted_sets = [];
        foreach ($l_obj_sets as $l_key => $l_value) {
            $l_converted_sets[] = 'isys_obj__' . $l_key . ' = ' . $l_value;
        }

        if (count($l_converted_sets)) {
            $l_query_part = implode(', ', $l_converted_sets);
            $l_obj_query = sprintf($l_obj_query, $l_query_part);

            // Send query:
            if ($this->update($l_obj_query) === false) {
                return false;
            }
        }

        // Create SQL query for the global table:
        $l_converted_sets = [];
        foreach ($l_global_sets as $l_key => $l_value) {
            $l_converted_sets[] = 'isys_catg_global_list__' . $l_key . ' = ' . $l_value;
        }

        if (count($l_converted_sets)) {
            $l_query_part = implode(', ', $l_converted_sets);
            $l_global_query = sprintf($l_global_query, $l_query_part);

            // Send query:
            if ($this->update($l_global_query) === false) {
                return false;
            }
        }

        return $this->apply_update();
    }

    /**
     * Return object by hostname - Used by the inventory.
     *
     * @param   string $p_hostname
     *
     * @return  isys_component_dao_result
     */
    public function get_object_by_hostname($p_hostname)
    {
        return $this->retrieve('SELECT * FROM isys_obj WHERE isys_obj__hostname = ' . $this->convert_sql_text($p_hostname) . ';');
    }

    /**
     * Creates an entry in isys_obj.
     *
     * @param   string  $p_title
     * @param   integer $p_obj_type
     * @param   integer $p_record_status
     * @param   integer $p_cmdb_status
     *
     * @return  integer
     * @author  Dennis Stuecken <dstuecken@i-doit.de>
     */
    public function create_object($p_title, $p_obj_type, $p_record_status = C__RECORD_STATUS__NORMAL, $p_cmdb_status = null)
    {
        return $this->insert_new_obj($p_obj_type, false, $p_title, null, $p_record_status, null, null, false, null, null, null, null, null, null, $p_cmdb_status);
    }

    /**
     * @param int    $p_obj_type
     * @param int    $p_category_id
     * @param string $p_category_const
     *
     * @return bool
     * @throws Exception
     */
    public function assign_catg($p_obj_type, $p_category_id = null, $p_category_const = null)
    {
        if ($p_category_id === null && $p_category_const === null) {
            throw new Exception("Category id AND const could not be null. You have to set at least one identifier. ");
        }

        if (!empty($p_category_const)) {
            $l_sql = 'SELECT isysgui_catg__id 
                FROM isysgui_catg 
                WHERE isysgui_catg__const = ' . $this->convert_sql_text($p_category_const) . ';';

            $p_category_id = $this->retrieve($l_sql)->get_row_value('isysgui_catg__id');
        }

        if ($p_category_id > 0) {
            $l_sql = 'DELETE FROM isys_obj_type_2_isysgui_catg 
                WHERE isys_obj_type_2_isysgui_catg__isys_obj_type__id = ' . $this->convert_sql_id($p_obj_type) . ' 
                AND isys_obj_type_2_isysgui_catg__isysgui_catg__id = ' . $this->convert_sql_id($p_category_id) . ';';

            if ($this->update($l_sql) && $this->apply_update()) {
                $l_sql = 'INSERT INTO isys_obj_type_2_isysgui_catg (isys_obj_type_2_isysgui_catg__isys_obj_type__id, isys_obj_type_2_isysgui_catg__isysgui_catg__id)
                    VALUES (' . $this->convert_sql_id($p_obj_type) . ', ' . $this->convert_sql_id($p_category_id) . ');';

                if ($this->update($l_sql)) {
                    return $this->apply_update();
                }
            }
        }

        return false;
    }

    /**
     * Updates existing objects type.
     *
     * @param   integer $p_objtype_id
     * @param   array   $p_arr_catg_id
     * @param   array   $p_arr_overview_catg
     * @param   array   $p_arr_update
     *
     * @return  boolean
     * @global  array   $g_mandator_info
     */
    public function update_objtype_by_id($p_objtype_id, $p_arr_catg_id, $p_arr_overview_catg, $p_arr_update)
    {
        $l_bRet = false;
        $l_nObjID = null;
        $l_nObjTypeID = $p_objtype_id;

        $p_arr_update['C__OBJTYPE__CONST'] = strtoupper(str_replace([
            "\"",
            "'",
            ".",
            ";",
            " "
        ], "", $p_arr_update['C__OBJTYPE__CONST']));

        if (isset($p_arr_update['C__OBJTYPE__AUTOMATED_INVENTORY_NO'])) {
            isys_tenantsettings::set('cmdb.objtype.' . $l_nObjTypeID . '.auto-inventory-no', $p_arr_update['C__OBJTYPE__AUTOMATED_INVENTORY_NO']);
        }

        $specificCategory = array_filter((array)$p_arr_overview_catg, function ($constant) {
            return strpos($constant, 'C__CATS__') === 0;
        });

        $specificCategoryIndex = count($specificCategory)
            ? array_search(current($specificCategory), $p_arr_overview_catg)
            : null;

        // Write the index of the specific category to the tenant setting.
        isys_tenantsettings::set('cmdb.objtype.' . $p_arr_update['C__OBJTYPE__CONST'] . '.specific-cat-position', $specificCategoryIndex);

        // Create update SQL.
        if (is_countable($p_arr_update) && count($p_arr_update) > 0) {
            $l_strSql = "UPDATE isys_obj_type SET
                isys_obj_type__title = " . $this->convert_sql_text($p_arr_update['C__OBJTYPE__TITLE']) . ",
                isys_obj_type__icon = " . $this->convert_sql_text($p_arr_update['C__OBJTYPE__ICON']) . ",
                isys_obj_type__isys_obj_type_group__id = " . $this->convert_sql_id($p_arr_update['C__OBJTYPE__GROUP_ID']) . ",
                isys_obj_type__isysgui_cats__id  = " . $this->convert_sql_id($p_arr_update['C__OBJTYPE__CATS_ID']) . ",
                isys_obj_type__description  = " . $this->convert_sql_text($p_arr_update['C__OBJTYPE__DESCRIPTION']) . ", ";

            if ($p_arr_update['C__OBJTYPE__SELF_DEFINED']) {
                $l_strSql .= "isys_obj_type__selfdefined = " . $this->convert_sql_boolean($p_arr_update['C__OBJTYPE__SELF_DEFINED']) . ", ";
            }

            if ($p_arr_update['C__OBJTYPE__IS_CONTAINER'] != "") {
                $l_strSql .= "isys_obj_type__container = " . $this->convert_sql_boolean($p_arr_update['C__OBJTYPE__IS_CONTAINER']) . ", ";
            }

            $p_arr_update['C__OBJTYPE__CONST'] = $this->generate_unique_obj_type_constant($p_arr_update['C__OBJTYPE__CONST'], $l_nObjTypeID);

            // @todo  Is "isys_obj_type__idoit_obj_type_number" used? Can this be removed?

            $l_strSql .= "isys_obj_type__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ",
                isys_obj_type__show_in_tree = " . $this->convert_sql_boolean($p_arr_update['C__OBJTYPE__SHOW_IN_TREE']) . ",
                isys_obj_type__idoit_obj_type_number = " . $this->convert_sql_text($p_arr_update['C__OBJTYPE__TYPE_NUMBER']) . ",
                isys_obj_type__obj_img_name = " . $this->convert_sql_text($p_arr_update['C__OBJTYPE__IMG_NAME']) . ",
                isys_obj_type__relation_master = " . $this->convert_sql_boolean($p_arr_update['C__OBJTYPE__RELATION_MASTER']) . ",
                isys_obj_type__overview = " . $this->convert_sql_boolean($p_arr_update['C__CMDB__OVERVIEW__ENTRY_POINT']) . ",
                isys_obj_type__default_template = " . $this->convert_sql_id($p_arr_update['C__CMDB__OBJTYPE__DEFAULT_TEMPLATE']) . ",
                isys_obj_type__use_template_title = " . $this->convert_sql_boolean($p_arr_update['C__CMDB__OBJTYPE__USE_TEMPLATE_TITLE']) . ",
                isys_obj_type__const = " . $this->convert_sql_text($p_arr_update['C__OBJTYPE__CONST']) . ",
                isys_obj_type__color = " . $this->convert_sql_text($p_arr_update["C__OBJTYPE__COLOR"]) . ",
                isys_obj_type__show_in_rack = " . $this->convert_sql_boolean($p_arr_update['C__OBJTYPE__INSERTION_OBJECT']) . ",
                isys_obj_type__sort = " . $this->convert_sql_int($p_arr_update['C__OBJTYPE__POSITION_IN_TREE']) . ",
                isys_obj_type__sysid_prefix = " . $this->convert_sql_text($p_arr_update['C__OBJTYPE__SYSID_PREFIX']) . "
                WHERE isys_obj_type__id = " . $this->convert_sql_id($l_nObjTypeID) . ";";

            $l_bRet = $this->update($l_strSql);
        }

        // 1 Delete all records for obj_type
        // 2 Insert (only) new records

        if ($l_bRet) {
            // Delete assigned custom categories.
            $this->update("DELETE FROM isys_obj_type_2_isysgui_catg_custom WHERE isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id = " .
                $this->convert_sql_id($l_nObjTypeID) . ";");

            // Delete assigned custom categories.
            $l_bRet = $this->update("DELETE FROM isys_obj_type_2_isysgui_catg WHERE isys_obj_type_2_isysgui_catg__isys_obj_type__id = " .
                $this->convert_sql_id($l_nObjTypeID) . ";");
        }

        if ($l_bRet && is_countable($p_arr_catg_id) && count($p_arr_catg_id) > 0) {
            $l_strSqlValuesCustomCat = $l_all_catg_arr = $l_all_catg_custom_arr = $l_strSqlValues = [];
            $l_all_catg_res = $this->get_all_catg();
            while ($l_row = $l_all_catg_res->get_row()) {
                $l_all_catg_arr[$l_row['isysgui_catg__const']] = $l_row['isysgui_catg__id'];
            }
            $l_all_catg_custom_res = $this->get_all_catg_custom();
            while ($l_row = $l_all_catg_custom_res->get_row()) {
                $l_all_catg_custom_arr[$l_row['isysgui_catg_custom__const']] = $l_row['isysgui_catg_custom__id'];
            }
            // Transaction
            for ($l_i = 0;$l_i < count($p_arr_catg_id);$l_i++) {
                if (isset($l_all_catg_arr[$p_arr_catg_id[$l_i]])) {
                    $l_strSqlValues[] = "(" . $this->convert_sql_id($p_objtype_id) . ", " . $this->convert_sql_id($l_all_catg_arr[$p_arr_catg_id[$l_i]]) . ")";
                } elseif (isset($l_all_catg_custom_arr[$p_arr_catg_id[$l_i]])) {
                    $l_strSqlValuesCustomCat[] = "(" . $this->convert_sql_id($p_objtype_id) . ", " . $this->convert_sql_id($l_all_catg_custom_arr[$p_arr_catg_id[$l_i]]) . ")";
                }
            }

            // Now insert all new records.
            $l_strSql = "INSERT INTO isys_obj_type_2_isysgui_catg (isys_obj_type_2_isysgui_catg__isys_obj_type__id, isys_obj_type_2_isysgui_catg__isysgui_catg__id) VALUES %s";
            $l_bRet = $this->update(sprintf($l_strSql, implode(', ', $l_strSqlValues)));

            if (count($l_strSqlValuesCustomCat) > 0) {
                $l_sql_insert = "INSERT INTO isys_obj_type_2_isysgui_catg_custom
					(isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id, isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id)
					VALUES %s";
                $l_bRet = $this->update(sprintf($l_sql_insert, implode(', ', $l_strSqlValuesCustomCat)));
            }
        }

        if ($l_bRet) {
            $l_overview_catg_delete = 'DELETE FROM isys_obj_type_2_isysgui_catg_overview WHERE isys_obj_type__id = ' . $this->convert_sql_id($l_nObjTypeID) . ';';
            $l_overview_catg_custom_delete = 'DELETE FROM isys_obj_type_2_isysgui_catg_custom_overview WHERE isys_obj_type__id = ' . $this->convert_sql_id($l_nObjTypeID) .
                ';';
            $l_bRet = ($this->update($l_overview_catg_delete) && $this->update($l_overview_catg_custom_delete));
        }

        if ($l_bRet && is_countable($p_arr_overview_catg) && count($p_arr_overview_catg) > 0) {
            $i = 0;

            $l_strSqlValues = $l_strSqlValuesCustomCat = [];

            // Update the overview for the current object type
            for ($l_i = 0;$l_i < count($p_arr_overview_catg);$l_i++) {
                if ($i === $specificCategoryIndex) {
                    $i++;
                }

                if (isset($l_all_catg_arr[$p_arr_overview_catg[$l_i]])) {
                    // Global category
                    $l_strSqlValues[] = '(' .
                        $this->convert_sql_id($p_objtype_id) . ', ' .
                        $this->convert_sql_id($l_all_catg_arr[$p_arr_overview_catg[$l_i]]) . ', ' .
                        $i++ . ')';
                } elseif (isset($l_all_catg_custom_arr[$p_arr_overview_catg[$l_i]])) {
                    // Custom category
                    $l_strSqlValuesCustomCat[] = '(' .
                        $this->convert_sql_id($p_objtype_id) . ', ' .
                        $this->convert_sql_id($l_all_catg_custom_arr[$p_arr_overview_catg[$l_i]]) . ', ' .
                        $i++ . ')';
                }
            }

            if (count($l_strSqlValues) > 0) {
                // Now insert all new records for global categories.
                $l_strSql = "INSERT INTO isys_obj_type_2_isysgui_catg_overview (isys_obj_type__id, isysgui_catg__id, isys_obj_type_2_isysgui_catg_overview__sort)
					VALUES %s;";
                $l_bRet = $this->update(sprintf($l_strSql, implode(', ', $l_strSqlValues)));
            }

            if (count($l_strSqlValuesCustomCat) > 0) {
                // Now insert all new records for custom categories.
                $l_strSql = "INSERT INTO isys_obj_type_2_isysgui_catg_custom_overview (isys_obj_type__id, isysgui_catg_custom__id, isys_obj_type_2_isysgui_catg_custom_overview__sort)
					VALUES %s;";
                $l_bRet = $this->update(sprintf($l_strSql, implode(', ', $l_strSqlValuesCustomCat)));
            }
        }

        if ($l_bRet) {
            $l_bRet = $this->apply_update();
        }

        // Update constant cache:
        if (is_string($p_arr_update['C__OBJTYPE__CONST']) && !defined($p_arr_update['C__OBJTYPE__CONST'])) {
            define($p_arr_update['C__OBJTYPE__CONST'], $l_nObjTypeID);
            isys_component_constant_manager::instance()
                ->clear_dcm_cache();
        }

        return $l_bRet;
    }

    /**
     * Count objects
     *
     * @param int    $p_objgroup
     * @param null   $p_objtype
     * @param bool   $p_ignore_cmdb_status
     * @param string $p_condition
     *
     * @return int
     */
    public function count_objects($p_objgroup = null, $p_objtype = null, $p_ignore_cmdb_status = false, $p_condition = "")
    {
        $l_status = C__RECORD_STATUS__NORMAL;
        if (!$p_ignore_cmdb_status && is_array($this->m_cmdb_status)) {
            if (defined('C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE') && is_numeric(array_search(constant('C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE'), $this->m_cmdb_status))) {
                $l_status = C__RECORD_STATUS__TEMPLATE;
            }
        }

        if (is_null($p_objgroup)) {
            $l_q = 'SELECT COUNT(isys_obj__id) AS count
                FROM isys_obj
                WHERE isys_obj__status = ' . $this->convert_sql_int($l_status) . ' ';

            if ($p_objtype) {
                $l_q .= ' AND isys_obj__isys_obj_type__id = ' . $this->convert_sql_id($p_objtype);
            }

            if (!$p_ignore_cmdb_status && $this->prepare_status_filter() != "") {
                $l_q .= ' AND ' . $this->prepare_status_filter();
            }

            $l_q .= $p_condition;

            $l_ret = $this->retrieve($l_q . ";");
            $l_row = $l_ret->get_row();
            $l_ret->free_result();

            return $l_row["count"];
        } else {
            $l_groups = $this->objgroup_get();

            $i = 0;

            if ($l_groups && $l_groups->num_rows()) {
                while ($l_row_g = $l_groups->get_row()) {
                    $l_q = 'SELECT COUNT(isys_obj__id) AS count
                        FROM isys_obj
                        INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
                        WHERE isys_obj_type__isys_obj_type_group__id = ' . $this->convert_sql_id($l_row_g["isys_obj_type_group__id"]) . '
                        AND isys_obj__status = ' . $this->convert_sql_int($l_status);

                    if ($p_objtype) {
                        $l_q .= ' AND isys_obj_type__id = ' . $this->convert_sql_id($p_objtype);
                    }

                    if (!$p_ignore_cmdb_status && $this->prepare_status_filter() != "") {
                        $l_q .= ' AND ' . $this->prepare_status_filter();
                    }

                    $l_q .= $p_condition;

                    $l_ret = $this->retrieve($l_q . ";");
                    $l_row = $l_ret->get_row();
                    $l_ret->free_result();

                    $i += intval($l_row["count"]);
                }
            }

            return $i;
        }
    }

    /**
     * Convert numbers in sql compliant syntax depending on system settings
     *
     * @deprecated
     * @todo        This seems to fail sometimes...? Better use "convert_sql_float"
     *
     * @param       string  $p_value
     * @param       boolean $p_bNotApo
     *
     * @return      string
     * @deprecated Will be removed in i-doit 1.14 or at least forward to $this->convert_sql_float()
     */
    public function convert_sql_decimal($p_value, $p_bNotApo = false)
    {
        if ($p_value > 0) {
            $l_nUserID = isys_application::instance()->session->get_user_id();

            $l_arDecSetting = isys_application::instance()->container->get('locales')->get_user_settings(LC_NUMERIC);

            // Replace all thousands seperators with an empty string.
            if ($l_arDecSetting['thousand_sep'] === ".") {
                $p_value = str_replace($l_arDecSetting['thousand_sep'], "", $p_value);
            } else {
                $p_value = str_replace(",", ".", $p_value);
            }

            // Replace decimal seperator if it's something else than a '.'.
            if ($l_arDecSetting['decimal_point'] !== '.') {
                $p_value = str_replace($l_arDecSetting['decimal_point'], ".", $p_value);
            }
        }

        if ($p_bNotApo) {
            return (float)$p_value;
        }

        return "'" . $p_value . "'";
    }

    /**
     * Retrieves UI data by an UI ID, I guess.
     *
     * @param   integer $p_cateID
     *
     * @return  isys_component_dao_result
     */
    public function get_available_ui_by_ui($p_cateID)
    {
        return $this->get_available_ui($p_cateID);
    }

    /**
     * @param   integer $p_cateID
     * @param   integer $p_objID
     *
     * @return  isys_component_dao_result
     */
    public function get_available_ui_by_obj($p_cateID = null, $p_objID)
    {
        return $this->get_available_ui($p_cateID, $p_objID);
    }

    /**
     * Retrieves all available uis for specified ui-record.
     *
     * @param   integer $p_cateID
     * @param   integer $p_objID
     *
     * @return  isys_component_dao_result
     */
    public function get_available_ui($p_cateID, $p_objID = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_ui_list
			INNER JOIN isys_ui_con_type ON isys_catg_ui_list__isys_ui_con_type__id = isys_ui_con_type__id
			INNER JOIN isys_ui_plugtype ON isys_catg_ui_list__isys_ui_plugtype__id = isys_ui_plugtype__id
			INNER JOIN isys_obj ON isys_catg_ui_list__isys_obj__id = isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			WHERE TRUE ';

        if ($p_cateID !== null) {
            $l_sql .= "AND isys_catg_ui_list__id  <> " . $this->convert_sql_id($p_cateID) . " ";
        }

        if ($p_objID !== null) {
            $l_sql .= "AND isys_obj__id  = " . $this->convert_sql_id($p_objID) . " ";
        }
        // if

        $l_sql .= "AND isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . "
			AND isys_catg_ui_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . "
			ORDER BY isys_obj_type__id, isys_obj__id, isys_catg_ui_list__title;";

        return $this->retrieve($l_sql);
    }

    /**
     * Returns the record entries as an array or null.
     *
     * @param string $p_tbl
     * @param int    $p_id
     *
     * @return array
     * @throws isys_exception_database
     * @deprecated Will be removed in i-doit 1.14
     */
    public function isys_get_table_data_by_id($p_tbl, $p_id)
    {
        return $this
            ->retrieve('SELECT * FROM ' . $p_tbl . ' WHERE ' . $p_tbl . '__id = ' . $this->convert_sql_id($p_id) . ';')
            ->get_row();
    }

    /**
     * Returns true, if the object type specified by its ID is a container.
     *
     * @param   integer $p_id
     *
     * @return boolean
     */
    public function isContainer($p_id)
    {
        $l_data = $this->retrieve("SELECT isys_obj_type__container FROM isys_obj_type WHERE isys_obj_type__id = " . $this->convert_sql_id($p_id) . ";")
            ->get_row_value('isys_obj_type__container');

        return ($l_data == "1");
    }

    /**
     * Gets source table from string constant
     *
     * @param $p_const
     * @param int /string $p_const
     *
     * @return string
     * @throws isys_exception_database
     */
    public function get_table_from_const($p_const, $p_category = null)
    {
        if (empty($p_category)) {
            $sql = 'SELECT isysgui_catg__source_table 
                FROM isysgui_catg 
                WHERE isysgui_catg__const = ' . $this->convert_sql_text($p_const) . '
                LIMIT 1;';

            return $this->retrieve($sql)->get_row_value('isysgui_catg__source_table');
        }

        $l_sql = 'SELECT isysgui_cat' . $p_category . '__source_table FROM isysgui_cat' . $p_category . ' WHERE ';

        if (is_numeric($p_const)) {
            $l_sql .= ' isysgui_cat' . $p_category . '__id = ' . $this->convert_sql_id($p_const);
        } else {
            $l_sql .= ' isysgui_cat' . $p_category . '__const = ' . $this->convert_sql_text($p_const);
        }

        return $this->retrieve($l_sql . ' LIMIT 1;')->get_row_value('isysgui_cat' . $p_category . '__source_table');
    }

    /**
     * Retrieve the ID of an object type by a given constant name.
     *
     * @param   string $p_const
     *
     * @return  mixed
     */
    public function get_objtype_id_by_const_string($p_const)
    {
        if (defined($p_const)) {
            return constant($p_const);
        }

        // If the constant was not yet defined, we get it from our cache.
        $l_objtype = $this->get_type_by_id($p_const);

        if ($l_objtype !== null) {
            return $l_objtype['isys_obj_type__id'];
        }

        return false;
    }

    /**
     * Checks if category has any entries.
     *
     * @todo    DS: find a more performant way of checking..
     *
     * @param   integer $objectId
     * @param   string  $className
     * @param   integer $categoryEntryId
     * @param   string  $tableName
     *
     * @return  boolean
     */
    public function check_category($objectId, $className, $categoryEntryId = null, $tableName = null)
    {
        if (!empty($className) && class_exists($className)) {
            $daoInstance = new $className($this->get_database_component());

            if ($categoryEntryId > 0 && method_exists($daoInstance, 'set_category_id')) {
                $daoInstance->set_category_id($categoryEntryId);
            }

            if (method_exists($daoInstance, 'get_count')) {
                if (strpos($tableName, '_list') === false) {
                    $tableName .= '_list';
                }

                if (method_exists($daoInstance, 'set_source_table')) {
                    $daoInstance->set_source_table($tableName);
                }

                return ($daoInstance->get_count($objectId) > 0);
            }

            $l_sql = 'SELECT COUNT(' . $tableName . '__id) as count
                FROM ' . $tableName . '
                WHERE ' . $tableName . '__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
                AND ' . $tableName . '__isys_obj__id = ' . $this->convert_sql_id($objectId) . ';';

            return $this->retrieve($l_sql)->get_row_value('count') > 0;
        }

        return false;
    }

    /**
     * Checks if category has any childs
     *
     * @param INT    $p_cat_id
     * @param string $p_table_name
     *
     * @return bool or result set
     * @deprecated
     */
    public function category_has_childs($p_cat_id, $p_table_name)
    {
        if (!strstr($p_table_name, "_list")) {
            $l_table_name = $p_table_name . "_list";
        } else {
            $l_table_name = $p_table_name;
        }

        if (strpos($l_table_name, "catg")) {
            $l_cat = "g";
        } else {
            $l_cat = "s";
        }

        $l_sql = "SELECT isysgui_cat" . $l_cat . "__id AS id, isysgui_cat" . $l_cat . "__class_name AS class_name, isysgui_cat" . $l_cat . "__source_table AS table_name " .
            "FROM isysgui_cat" . $l_cat . " " . "WHERE isysgui_cat" . $l_cat . "__parent = " . $this->convert_sql_id($p_cat_id);

        $l_res = $this->retrieve($l_sql);

        if ($l_res->num_rows() > 0) {
            $l_res->free_result();

            return $l_res;
        } else {
            $l_res->free_result();

            return false;
        }
    }

    /**
     * Checks if table exists in database
     *
     * @param   string $p_source_table
     *
     * @return  boolean
     */
    public function table_exists($p_source_table)
    {
        return (count($this->retrieve('SHOW TABLES LIKE ' . $this->convert_sql_text($p_source_table) . ';')) > 0);
    }

    /**
     * Checks if column exists in table.
     *
     * @param   string $p_source_table
     * @param   string $p_column
     *
     * @return  boolean
     * @deprecated use method fieldsExistsInTable instead
     */
    public function column_exists_in_table($p_source_table, $p_column)
    {
        return (count($this->retrieve(sprintf('SHOW COLUMNS FROM ' . $p_source_table . ' WHERE FIELD LIKE %s;', $this->convert_sql_text($p_column)))) > 0);
    }

    /**
     * Gets global category by constant.
     *
     * @param   mixed $p_catg_const May be a constant as string, or the constants value.
     *
     * @return  isys_component_dao_result
     */
    public function get_catg_by_const($p_catg_const)
    {
        if (is_numeric($p_catg_const)) {
            $l_sql_condition = " isysgui_catg__id = " . $this->convert_sql_id($p_catg_const);
        } else {
            $l_sql_condition = " isysgui_catg__const = " . $this->convert_sql_text($p_catg_const);
        }

        return $this->retrieve('SELECT * FROM isysgui_catg WHERE ' . $l_sql_condition . ';');
    }

    /**
     * Gets custom category by constant.
     *
     * @param   mixed $p_catg_const May be a constant as string, or the constants value.
     *
     * @return  isys_component_dao_result
     */
    public function get_catc_by_const($p_catg_const)
    {
        if (is_numeric($p_catg_const)) {
            $l_sql_condition = " isysgui_catg_custom__id = " . $this->convert_sql_id($p_catg_const);
        } else {
            $l_sql_condition = " isysgui_catg_custom__const = " . $this->convert_sql_text($p_catg_const);
        }

        return $this->retrieve('SELECT * FROM isysgui_catg_custom WHERE ' . $l_sql_condition . ';');
    }

    /**
     * Gets specific category by constant.
     *
     * @param   mixed $p_cats_const May be a constant as string, or the constants value.
     *
     * @return  isys_component_dao_result
     */
    public function get_cats_by_const($p_cats_const)
    {
        if (is_numeric($p_cats_const)) {
            $l_sql_condition = " isysgui_cats__id = " . $this->convert_sql_id($p_cats_const);
        } else {
            $l_sql_condition = " isysgui_cats__const = " . $this->convert_sql_text($p_cats_const);
        }

        return $this->retrieve('SELECT * FROM isysgui_cats WHERE ' . $l_sql_condition);
    }

    /**
     * Get catg|cats|catc by its constant
     *
     * @param string $p_constant  Category constant
     * @param array  $p_selection Columns: Please use prefixless columnnames here: isysgui_catg__title WRONG|RIGHT title
     *                            (if empty it will return the resultset without the prefix: array(title=>XXXX, multivalue=>xxx))
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @return array ResultSet
     */
    public function get_cat_by_const($p_constant, array $p_selection = [])
    {
        $l_methods = [
            "get_catg_by_const" => "isysgui_catg__",
            "get_cats_by_const" => "isysgui_cats__",
            "get_catc_by_const" => "isysgui_catg_custom__"
        ];

        $l_type = [
            "isysgui_catg__"        => C__CMDB__CATEGORY__TYPE_GLOBAL,
            "isysgui_cats__"        => C__CMDB__CATEGORY__TYPE_SPECIFIC,
            "isysgui_catg_custom__" => C__CMDB__CATEGORY__TYPE_CUSTOM
        ];

        foreach ($l_methods as $l_method => $l_prefix) {
            $l_return = call_user_func_array([
                $this,
                $l_method
            ], [$p_constant]);

            if (is_countable($l_return) && count($l_return) > 0) {
                $l_result = [];
                $l_row = $l_return->get_row();

                if (count($p_selection)) {
                    foreach ($p_selection as $l_postfix) {
                        if (isset($l_row[$l_prefix . $l_postfix])) {
                            $l_result[$l_postfix] = $l_row[$l_prefix . $l_postfix];
                        }
                    }
                    if (in_array('type', $p_selection)) {
                        $l_result['type'] = $l_type[$l_prefix];
                    }
                } else {
                    foreach ($l_row as $l_key => $l_value) {
                        $l_result[str_replace($l_prefix, "", $l_key)] = $l_value;
                    }
                    $l_result['type'] = $l_type[$l_prefix];
                }

                return $l_result;
            }
        }

        return [];
    }

    /**
     * Gets all objecttypes with the specified isysgui_cats__id.
     *
     * @param   integer $p_cats_id
     *
     * @return  isys_component_dao_result
     */
    public function get_objtype_by_cats_id($p_cats_id)
    {
        return $this->retrieve('SELECT * FROM isys_obj_type
			INNER JOIN isysgui_cats ON isys_obj_type__isysgui_cats__id = isysgui_cats__id
			WHERE isys_obj_type__isysgui_cats__id = ' . $this->convert_sql_id($p_cats_id) . ';');
    }

    /**
     * Method for retrieving an objects title, by a given object ID.
     *
     * @param   integer $p_obj_id
     *
     * @return  mixed
     */
    public function obj_get_title_by_id_as_string($p_obj_id)
    {
        return $this->retrieve("SELECT isys_obj__title FROM isys_obj WHERE isys_obj__id = " . $this->convert_sql_id($p_obj_id) . ";")
            ->get_row_value("isys_obj__title");
    }

    /**
     * Return subcategories of the parent category id $p_id
     *
     * @param   integer $p_id
     *
     * @param bool      $p_complete
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function cats_get_subcats($p_id, $p_complete = false)
    {
        if ($p_complete === true) {
            $l_query = 'SELECT isysgui_cats.*
				FROM isysgui_cats_2_subcategory
				INNER JOIN isysgui_cats ON isysgui_cats__id = isysgui_cats_2_subcategory__isysgui_cats__id__child
				WHERE isysgui_cats_2_subcategory__isysgui_cats__id__parent = ' . $this->convert_sql_id($p_id) . ';';
        } else {
            $l_query = 'SELECT isysgui_cats_2_subcategory__isysgui_cats__id__child
				FROM isysgui_cats_2_subcategory
				WHERE isysgui_cats_2_subcategory__isysgui_cats__id__parent = ' . $this->convert_sql_id($p_id) . ';';
        }

        return $this->retrieve($l_query);
    }

    /**
     * Return subcategories of the parent category id $p_id
     *
     * @param   integer $p_id
     *
     * @param bool      $p_complete
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function cats_get_parent_cats($p_id, $p_complete = false)
    {
        if ($p_complete === true) {
            $l_query = 'SELECT isysgui_cats.*
				FROM isysgui_cats_2_subcategory
				INNER JOIN isysgui_cats ON isysgui_cats__id = isysgui_cats_2_subcategory__isysgui_cats__id__parent
				WHERE isysgui_cats_2_subcategory__isysgui_cats__id__child = ' . $this->convert_sql_id($p_id) . ';';
        } else {
            $l_query = 'SELECT isysgui_cats_2_subcategory__isysgui_cats__id__parent
				FROM isysgui_cats_2_subcategory
				WHERE isysgui_cats_2_subcategory__isysgui_cats__id__child = ' . $this->convert_sql_id($p_id) . ';';
        }

        return $this->retrieve($l_query);
    }

    /**
     * Retrieves one or more object-type-groups by ID(s).
     *
     * @param   mixed $p_id May be an integer or an array of integers.
     *
     * @return  isys_component_dao_result
     */
    public function get_object_group_by_id($p_id = null)
    {
        $l_sql = "SELECT * FROM isys_obj_type_group WHERE TRUE";

        if ($p_id !== null) {
            if (is_numeric($p_id)) {
                $l_sql .= " AND isys_obj_type_group__id = " . $this->convert_sql_id($p_id);
            } else {
                if (is_array($p_id) && count($p_id) > 0) {
                    $l_sql .= " AND isys_obj_type_group__id " . $this->prepare_in_condition($p_id);
                }
            }
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Return all object-types, which have the specified category assigned.
     *
     * @param   integer $p_category_id   ID of the category.
     * @param   string  $p_category_type Possible values "g" (globa) and "s" (specific).
     * @param   boolean $p_as_string     Retrieve an array of constants, instead of ID's.
     * @param   boolean $p_complete_row  Retrieve the complete row instead of constants or ID's.
     *
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @return  array
     */
    public function get_object_types_by_category($p_category_id, $p_category_type = 'g', $p_as_string = true, $p_complete_row = false)
    {
        $l_return = [];
        $l_sql = 'SELECT * FROM isys_obj_type ';

        if ($p_category_type == 'g') {
            $l_sql .= 'INNER JOIN isys_obj_type_2_isysgui_catg ON isys_obj_type__id = isys_obj_type_2_isysgui_catg__isys_obj_type__id ' .
                'WHERE isys_obj_type_2_isysgui_catg__isysgui_catg__id = ' . $this->convert_sql_int($p_category_id);
        } else {
            $l_sql .= 'LEFT JOIN isysgui_cats_2_subcategory ON isysgui_cats_2_subcategory__isysgui_cats__id__parent = isys_obj_type__isysgui_cats__id ' .
                ' WHERE isys_obj_type__isysgui_cats__id = ' . $this->convert_sql_id($p_category_id) . ' OR isysgui_cats_2_subcategory__isysgui_cats__id__child = ' .
                $this->convert_sql_id($p_category_id) . ' GROUP BY isys_obj_type__id';
        }

        $l_result = $this->retrieve($l_sql);

        while ($l_row = $l_result->get_row()) {
            if ($p_complete_row) {
                $l_return[] = $l_row;
            } else {
                if ($p_as_string) {
                    $l_return[] = $l_row['isys_obj_type__const'];
                } else {
                    $l_return[] = $l_row['isys_obj_type__id'];
                }
            }
        }
        $l_result->free_result();

        return $l_return;
    }

    /**
     * Gets all objecttypes by group.
     *
     * @param   mixed   $p_object_group
     * @param   boolean $p_as_string
     *
     * @return  array
     */
    public function get_object_types_by_object_group($p_object_group = null, $p_as_string = false)
    {
        $l_sql = 'SELECT * FROM isys_obj_type WHERE TRUE ';

        if (!empty($p_object_group)) {
            if (is_array($p_object_group)) {
                $l_sql .= 'AND isys_obj_type__isys_obj_type_group__id IN (';
                foreach ($p_object_group as $l_object_type_group_id) {
                    if (is_numeric($l_object_type_group_id)) {
                        $l_sql .= $l_object_type_group_id . ',';
                    } elseif (is_string($l_object_type_group_id)) {
                        if (defined($l_object_type_group_id)) {
                            $l_object_type_group_id = constant($l_object_type_group_id);
                            $l_sql .= $l_object_type_group_id . ',';
                        }
                    }
                }
                $l_sql = rtrim($l_sql, ',') . ')';
            } elseif (is_numeric($p_object_group)) {
                $l_sql .= 'AND isys_obj_type__isys_obj_type_group__id = ' . $this->convert_sql_id($p_object_group);
            } elseif (is_string($p_object_group)) {
                if (defined($p_object_group)) {
                    $l_object_type_group_id = constant($p_object_group);
                    $l_sql .= 'AND isys_obj_type__isys_obj_type_group__id = ' . $this->convert_sql_id($l_object_type_group_id);
                }
            }
        }

        $l_arr = [];
        $l_res = $this->retrieve($l_sql);

        while ($l_row = $l_res->get_row()) {
            if ($this->count_objects_by_type($l_row['isys_obj_type__id']) > 0) {
                $l_arr[] = ($p_as_string) ? $l_row['isys_obj_type__const'] : $l_row['isys_obj_type__id'];
            }
        }
        $l_res->free_result();

        return $l_arr;
    }

    /**
     * Returns the last inserted object from the specified object type.
     *
     * @param  integer $objectTypeId
     *
     * @return integer
     * @author Van Quyen Hoang <qhoang@synetics.de>
     */
    public function get_last_obj_id_from_type($objectTypeId = null)
    {
        $sql = 'SELECT MAX(isys_obj__id) AS id FROM isys_obj WHERE TRUE ';

        if ($objectTypeId !== null) {
            $sql .= 'AND isys_obj__isys_obj_type__id = ' . $this->convert_sql_id($objectTypeId);
        }

        return (int)$this->retrieve($sql . ';')->get_row_value('id');
    }

    /**
     * Returns the value of the specified placeholder.
     *
     * @param   string  $placeholder
     * @param   integer $objId
     *
     * @return  string
     */
    public function replace_object_placeholder($placeholder, $objId)
    {
        if (is_string($placeholder) && strpos($placeholder, '%') !== false) {
            $l_object = $this->get_object($objId, false, 1)->get_row();

            $placeholders = [
                '%TITLE%'       => $l_object['isys_obj__title'],
                '%OBJTYPEID%'   => $l_object['isys_obj_type__id'],
                '%ID%'          => $l_object['isys_obj__id'],
                '%SYSID%'       => $l_object['isys_obj__sysid'],
                '%CREATED%'     => $l_object['isys_obj__created'],
                '%CREATEDBY%'   => $l_object['isys_obj__created_by'],
                '%UPDATED%'     => $l_object['isys_obj__updated'],
                '%UPDATEDBY%'   => $l_object['isys_obj__updated_by'],
                '%DESCRIPTION%' => $l_object['isys_obj__description']
            ];

            return strtr($placeholder, $placeholders);
        }

        return $placeholder;
    }

    /**
     * Returns the last id from the specified table
     *
     * @param    string $p_table
     *
     * @return    mixed
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_last_id_from_table($p_table)
    {
        $l_res = $this->retrieve('SELECT MAX(' . $p_table . '__id) AS last_id FROM ' . $p_table . ' LIMIT 0,1');

        if (is_countable($l_res) && count($l_res) > 0) {
            $l_row = $l_res->get_row();
            $l_res->free_result();

            return $l_row['last_id'];
        } else {
            $l_res->free_result();

            return false;
        }
    }

    /**
     * Gets object by hostname, serialnumber or mac address or title
     *
     * @param string       $p_hostname
     * @param string       $p_serial
     * @param string|array $p_mac
     * @param string       $p_title
     * @param string       $p_ip
     * @param int          $p_ip_long
     *
     * @param null         $p_fqdn
     * @param null         $p_objtype_filter_arr
     *
     * @return int
     * @throws isys_exception_database
     * @author     Van Quyen Hoang <qhoang@i-doit.org>
     * @deprecated use CiMatcher instead
     */
    public function get_object_by_hostname_serial_mac(
        $p_hostname = null,
        $p_serial = null,
        $p_mac = null,
        $p_title = null,
        $p_ip = null,
        $p_ip_long = null,
        $p_fqdn = null,
        $p_objtype_filter_arr = null
    ) {
        $l_ignore_obj_types = implode(', ', filter_defined_constants([
            'C__OBJTYPE__RELATION',
            'C__OBJTYPE__PARALLEL_RELATION',
            'C__OBJTYPE__SOA_STACK',
            'C__OBJTYPE__CABLE',
            'C__OBJTYPE__CONTAINER'
        ]));

        $l_main_query = 'SELECT ' . 'DISTINCT(isys_obj__id) AS id ' . 'FROM isys_obj ';

        $l_ip_condition = '';
        $l_add_ip_query = false;

        $l_query_arr = [];

        $l_ip_query = $l_main_query . ' LEFT JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_obj__id = isys_obj__id ';

        // Join for hostname
        if ($p_hostname) {
            $l_add_ip_query = true;
            $l_ip_condition .= '(isys_catg_ip_list__hostname = ' . $this->convert_sql_text($p_hostname) . ' ';
        }

        // Check fqdn as hostname
        if ($p_fqdn) {
            $l_add_ip_query = true;
            if (strlen($l_ip_condition) > 1) {
                $l_ip_condition .= 'OR ';
            } else {
                $l_ip_condition .= '(';
            }
            $l_ip_condition .= 'isys_catg_ip_list__hostname = ' . $this->convert_sql_text($p_fqdn) . ' ';
        }

        // Join for ip address
        if ($p_ip || $p_ip_long) {
            $l_add_ip_query = true;
            if (strlen($l_ip_condition) > 1) {
                $l_ip_condition .= 'OR ';
            }

            $l_ip_query .= 'LEFT JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id ';

            if ($p_ip) {
                $l_ip_condition .= 'isys_cats_net_ip_addresses_list__title = ' . $this->convert_sql_text($p_ip) . ' ';
            } elseif ($p_ip_long) {
                $l_ip_condition .= 'isys_cats_net_ip_addresses_list__ip_address_long = \'' . $p_ip_long . '\' ';
            }

            if ($p_hostname || $p_fqdn) {
                $l_ip_condition .= ') ';
            }
        } elseif ($p_hostname || $p_fqdn) {
            $l_ip_condition .= ') ';
        }

        if ($l_add_ip_query) {
            if ($p_title) {
                if (strlen($l_ip_condition) > 1) {
                    $l_ip_condition .= ' AND ';
                }

                $l_ip_condition .= ' (isys_obj__title = \'' . $p_title . '\')';
            }

            if (strlen($l_ip_condition) > 1) {
                $l_ip_condition .= ' AND ';
            }

            if ($p_objtype_filter_arr) {
                if (is_array($p_objtype_filter_arr)) {
                    $l_ip_condition .= ' (isys_obj__isys_obj_type__id IN (' . implode(',', $p_objtype_filter_arr) . ',' . $l_ignore_obj_types . ')) ';
                } elseif (is_string($p_objtype_filter_arr)) {
                    $l_ip_condition .= ' (isys_obj__isys_obj_type__id IN (' . trim($p_objtype_filter_arr, ',') . ',' . $l_ignore_obj_types . ')) ';
                }
            } else {
                $l_ip_condition .= ' (isys_obj__isys_obj_type__id NOT IN (' . $l_ignore_obj_types . ')) ';
            }

            $l_ip_query .= ' WHERE ' . $l_ip_condition . ' AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' ';
            $l_query_arr[] = $l_ip_query;
        }

        $l_model_query = $l_main_query . 'LEFT JOIN isys_catg_model_list ON isys_catg_model_list__isys_obj__id = isys_obj__id ';
        $l_model_condition = '';

        // Join for serialnumber
        if ($p_serial) {
            $l_model_condition .= 'isys_catg_model_list__serial = ' . $this->convert_sql_text($p_serial) . ' ';

            if ($p_title) {
                if (strlen($l_model_condition) > 1) {
                    $l_model_condition .= ' AND ';
                }

                $l_model_condition .= ' (isys_obj__title = \'' . $p_title . '\')';
            }

            if (strlen($l_model_condition) > 1) {
                $l_model_condition .= ' AND ';
            }

            if ($p_objtype_filter_arr) {
                if (is_array($p_objtype_filter_arr)) {
                    $l_model_condition .= ' (isys_obj__isys_obj_type__id IN (' . implode(',', $p_objtype_filter_arr) . ',' . $l_ignore_obj_types . ')) ';
                } elseif (is_string($p_objtype_filter_arr)) {
                    $l_model_condition .= ' (isys_obj__isys_obj_type__id IN (' . trim($p_objtype_filter_arr, ',') . ',' . $l_ignore_obj_types . ')) ';
                }
            } else {
                $l_model_condition .= ' (isys_obj__isys_obj_type__id NOT IN (' . $l_ignore_obj_types . ')) ';
            }

            $l_model_query .= ' WHERE ' . $l_model_condition . ' AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' ';
            $l_query_arr[] = $l_model_query;
        }

        $l_port_query = $l_main_query . ' LEFT JOIN isys_catg_port_list ON isys_catg_port_list__isys_obj__id = isys_obj__id ';

        // Join for mac-address
        if ($p_mac) {
            $l_port_condition = '';
            if (is_array($p_mac)) {
                $l_port_condition .= '(';
                foreach ($p_mac as $l_mac) {
                    $l_port_condition .= ' isys_catg_port_list__mac = \'' . $l_mac . '\' OR';
                }

                $l_port_condition = rtrim($l_port_condition, 'OR') . ')';
            } else {
                $l_port_condition .= '((isys_catg_port_list__mac = ' . $this->convert_sql_text($p_mac) . ')) ';
            }

            if ($p_title) {
                if (strlen($l_port_condition) > 1) {
                    $l_port_condition .= ' AND ';
                }

                $l_port_condition .= ' (isys_obj__title = \'' . $p_title . '\')';
            }

            if (strlen($l_port_condition) > 1) {
                $l_port_condition .= ' AND ';
            }

            if ($p_objtype_filter_arr) {
                if (is_array($p_objtype_filter_arr)) {
                    $l_port_condition .= ' (isys_obj__isys_obj_type__id IN (' . implode(',', $p_objtype_filter_arr) . ',' . $l_ignore_obj_types . ')) ';
                } elseif (is_string($p_objtype_filter_arr)) {
                    $l_port_condition .= ' (isys_obj__isys_obj_type__id IN (' . trim($p_objtype_filter_arr, ',') . ',' . $l_ignore_obj_types . ')) ';
                }
            } else {
                $l_port_condition .= ' (isys_obj__isys_obj_type__id NOT IN (' . $l_ignore_obj_types . ')) ';
            }
            $l_port_query .= ' WHERE ' . $l_port_condition . ' AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' ';
            $l_query_arr[] = $l_port_query;
        }

        if (count($l_query_arr) === 0 && $p_title !== '') {
            $l_query_arr[] = $l_main_query . ' WHERE isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' AND isys_obj__title = ' .
                $this->convert_sql_text($p_title);
        }

        if (count($l_query_arr) > 1) {
            $l_execute_query = implode(' UNION ', $l_query_arr);
        } else {
            $l_execute_query = $l_query_arr[0];
        }

        $l_resultRessource = $this->retrieve($l_execute_query);
        if ($l_resultRessource->num_rows() == 1) {
            $l_row = $l_resultRessource->get_row();
            $l_resultRessource->free_result();
            if ($l_row['id']) {
                return $l_row['id'];
            }
        }
        $l_resultRessource->free_result();

        return false;
    }

    /**
     * Gets all objecttypes by the given specific category id as array.
     *
     * @param   integer $p_cats_id
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_objtype_ids_by_cats_id_as_array($p_cats_id)
    {
        $l_return = [];
        $l_res = $this->get_objtype_by_cats_id($p_cats_id);

        while ($l_row = $l_res->get_row()) {
            $l_return[] = $l_row['isys_obj_type__id'];
        }

        $l_res->free_result();

        return $l_return;
    }

    /**
     * Gets all object type groups (only constant).
     *
     * @param   mixed $p_objtype_id
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_objtype_group_const_by_type_id($p_objtype_id)
    {
        $l_return = [];
        if (is_array($p_objtype_id)) {
            $l_sql_condition = 'WHERE isys_obj_type__id IN (' . implode(',', $p_objtype_id) . ')';
        } else {
            $l_sql_condition = 'WHERE isys_obj_type__id = ' . $this->convert_sql_id($p_objtype_id);
        }

        $l_sql = 'SELECT DISTINCT(isys_obj_type_group__id)
			FROM isys_obj_type_group
			INNER JOIN isys_obj_type ON isys_obj_type__isys_obj_type_group__id = isys_obj_type_group__id %s';

        $l_res = $this->retrieve(sprintf($l_sql, $l_sql_condition));

        if (is_countable($l_res) && count($l_res) > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_return[] = $l_row['isys_obj_type_group__id'];
            }
        }

        $l_res->free_result();

        return $l_return;
    }

    /**
     * Retrieves all global categories, assigned to a certain object type - including sub-categories.
     * Currently this only will check one level (cat > subcat) - feel free to implement a recursive logic for more level (cat > subcat > subsubcat > ...).
     *
     * @param   integer $p_objtype_id
     * @param   boolean $p_hierarchical
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function gui_get_catg_with_subcats_by_objtype_id($p_objtype_id, $p_hierarchical = false)
    {
        $l_categories = [];

        $l_q = 'SELECT T_CATG.* FROM isys_obj_type_2_isysgui_catg AS T_CONN
			LEFT JOIN isys_obj_type AS T_TYPE ON T_TYPE.isys_obj_type__id = T_CONN.isys_obj_type_2_isysgui_catg__isys_obj_type__id
			LEFT JOIN isysgui_catg AS T_CATG ON T_CATG.isysgui_catg__id = T_CONN.isys_obj_type_2_isysgui_catg__isysgui_catg__id
			WHERE TRUE ';

        // Objecttype condition
        if (is_numeric($p_objtype_id) && $p_objtype_id > 0) {
            $l_q .= ' AND T_TYPE.isys_obj_type__id = ' . $this->convert_sql_id($p_objtype_id);
        }

        $l_q .= ';';

        $l_res = $this->retrieve($l_q);

        if (is_countable($l_res) && count($l_res) > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_categories[$l_row['isysgui_catg__id']] = $l_row;
                $l_categories[$l_row['isysgui_catg__id']]['subcats'] = false;
            }
        }

        if (count($l_categories) > 0) {
            $l_res = $this->retrieve(sprintf('SELECT * FROM isysgui_catg WHERE isysgui_catg__parent %s;', $this->prepare_in_condition(array_keys($l_categories))));

            if (is_countable($l_res) && count($l_res) > 0) {
                while ($l_row = $l_res->get_row()) {
                    if ($p_hierarchical) {
                        $l_categories[$l_row['isysgui_catg__parent']]['subcats'][$l_row['isysgui_catg__id']] = $l_row;
                    } else {
                        $l_categories[$l_row['isysgui_catg__id']] = $l_row;
                    }
                }
            }

            $l_res->free_result();
        }

        return $l_categories;
    }

    /**
     * Retrieves all specific categories, assigned to a certain object type - including sub-categories.
     * Currently this only will check one level (cat > subcat) - feel free to implement a recursive logic for more level (cat > subcat > subsubcat > ...).
     *
     * @param   integer $p_objtype_id
     * @param   boolean $p_hierarchical
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function gui_get_cats_with_subcats_by_objtype_id($p_objtype_id, $p_hierarchical = false)
    {
        $l_categories = [];

        $l_q = 'SELECT T_CATS.* FROM isys_obj_type AS T_TYPE
			INNER JOIN isysgui_cats AS T_CATS ON T_TYPE.isys_obj_type__isysgui_cats__id = T_CATS.isysgui_cats__id
			WHERE T_TYPE.isys_obj_type__id = ' . $this->convert_sql_id($p_objtype_id) . ';';

        $l_res = $this->retrieve($l_q);

        if (is_countable($l_res) && count($l_res) > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_categories[$l_row['isysgui_cats__id']] = $l_row;
                $l_categories[$l_row['isysgui_cats__id']]['subcats'] = false;
            }
        }

        // Free memory
        $l_res->free_result();

        if (count($l_categories) > 0) {
            $l_res = $this->retrieve(sprintf('SELECT * FROM isysgui_cats WHERE isysgui_cats__parent %s;', $this->prepare_in_condition(array_keys($l_categories))));

            if (is_countable($l_res) && count($l_res) > 0) {
                while ($l_row = $l_res->get_row()) {
                    if ($p_hierarchical) {
                        $l_categories[$l_row['isysgui_cats__parent']]['subcats'][$l_row['isysgui_cats__id']] = $l_row;
                    } else {
                        $l_categories[$l_row['isysgui_cats__id']] = $l_row;
                        $l_categories[$l_row['isysgui_cats__id']]['parent'] = $l_row['isysgui_cats__parent'];
                    }
                }
            }

            $l_res = $this->retrieve(sprintf('SELECT cats.*, isysgui_cats_2_subcategory__isysgui_cats__id__parent AS parent
				FROM isysgui_cats_2_subcategory
				LEFT JOIN isysgui_cats AS cats ON cats.isysgui_cats__id = isysgui_cats_2_subcategory__isysgui_cats__id__child
				WHERE isysgui_cats_2_subcategory__isysgui_cats__id__parent %s;', $this->prepare_in_condition(array_keys($l_categories))));

            if (is_countable($l_res) && count($l_res) > 0) {
                while ($l_row = $l_res->get_row()) {
                    if ($p_hierarchical) {
                        $l_categories[$l_row['isysgui_cats__parent']]['subcats'][$l_row['isysgui_cats__id']] = $l_row;
                    } else {
                        $l_categories[$l_row['isysgui_cats__id']] = $l_row;
                        $l_categories[$l_row['isysgui_cats__id']]['parent'] = $l_row['parent'];
                    }
                }
            }

            // Free memory
            $l_res->free_result();
        }

        return $l_categories;
    }

    /**
     * Helper function which gets an dao instance which considers the custom categories.
     *
     * @param String   $p_class
     * @param int|null $p_custom_id Custom category ID
     *
     * @return isys_cmdb_dao_category
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_dao_instance($p_class, $p_custom_id = null)
    {
        if ($p_custom_id > 0) {
            if (isset(self::$m_custom_daos[$p_custom_id])) {
                $l_cat_dao = self::$m_custom_daos[$p_custom_id];
            } else {
                $l_cat_dao = isys_cmdb_dao_category_g_custom_fields::factory($this->get_database_component());
                $l_cat_dao->set_catg_custom_id($p_custom_id);
                self::$m_custom_daos[$p_custom_id] = $l_cat_dao;
            }
        } else {
            $l_cat_dao = $p_class::instance($this->get_database_component());
        }

        return $l_cat_dao;
    }

    /**
     * Method for loading (and caching) all object types.
     *
     * @return  isys_cmdb_dao
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function load_obj_types()
    {
        self::$m_obj_types = [];

        $l_res = $this->retrieve('SELECT * FROM isys_obj_type');

        while ($l_row = $l_res->get_row()) {
            self::$m_obj_types[$l_row['isys_obj_type__id']] = $l_row;
            self::$m_obj_types[$l_row['isys_obj_type__id']]['LC_isys_obj_type__title'] = isys_application::instance()->container->get('language')
                ->get($l_row['isys_obj_type__title']);
        }

        // Free memory
        $l_res->free_result();

        return $this;
    }

    /**
     * Gets category's identifier.
     *
     * @return int
     */
    public function get_category_id()
    {
        return 0;
    }

    /**
     * Gets category's type.
     *
     * @return int
     */
    public function get_category_type()
    {
        return 0;
    }

    /**
     * Check if table has specified field
     *
     * @param string $table
     * @param array $fields
     *
     * @return bool
     * @throws isys_exception_database
     */
    public function fieldsExistsInTable($table, array $fields)
    {
        if (empty($fields)) {
            return false;
        }

        $db = isys_application::instance()->container->get('database');
        $table = $db->escape_string($table);

        if (!$this->table_exists($table)) {
            return false;
        }

        $inConditionContent = implode(',', array_map([$this, 'convert_sql_text'], $fields));

        return count($this->retrieve('SHOW FIELDS FROM ' . $table . ' WHERE Field IN (' . $inConditionContent . ')')) === count($fields);
    }

    /**
     * Retrieves all category tables
     *
     * @param bool|null $multivalue
     *
     * @return array
     * @throws isys_exception_database
     */
    public function getAllCategoryTablesAsArray($multivalue = null)
    {
        $conditionGlobal = $conditionSpecific = '';
        $return = [];
        if (is_bool($multivalue)) {
            $conditionSpecific = 'WHERE isysgui_cats__list_multi_value = ' . ($multivalue === true ? '1': '0');
            $conditionGlobal = 'WHERE isysgui_catg__list_multi_value = ' . ($multivalue === true ? '1': '0');
        }

        $query = 'SELECT 
                (CASE 
                    WHEN LOCATE(\'_2_\', isysgui_cats__source_table) > 0 THEN isysgui_cats__source_table 
                    WHEN SUBSTRING_INDEX(isysgui_cats__source_table, \'_list\', -1) = \'\' THEN isysgui_cats__source_table
                    ELSE CONCAT(isysgui_cats__source_table, \'_list\')
                END) as t FROM isysgui_cats
                    ' . $conditionSpecific . '
                UNION
                SELECT 
                (CASE 
                    WHEN LOCATE(\'_2_\', isysgui_catg__source_table) > 0 THEN isysgui_catg__source_table 
                    WHEN SUBSTRING_INDEX(isysgui_catg__source_table, \'_list\', -1) = \'\' THEN isysgui_catg__source_table
                    ELSE CONCAT(isysgui_catg__source_table, \'_list\')
                END) as t FROM isysgui_catg
                    ' . $conditionGlobal;
        $result = $this->retrieve($query);

        while ($row = $result->get_row()) {
            $return[] = $row['t'];
        }
        return $return;
    }

    /**
     * @param $category
     *
     * @return array
     * @throws isys_exception_database
     */
    public function getObjTypeIdsByCategory($category)
    {
        $query = 'SELECT isys_obj_type_2_isysgui_catg__isys_obj_type__id as id FROM isys_obj_type_2_isysgui_catg 
            WHERE isys_obj_type_2_isysgui_catg__isysgui_catg__id = (
                SELECT isysgui_catg__id FROM isysgui_catg WHERE isysgui_catg__const = ' . $this->convert_sql_text($category) . '
            )';
        $result = $this->retrieve($query);
        $return = [];

        while ($row = $result->get_row()) {
            $return[] = $row['id'];
        }
        return $return;
    }

    /**
     * Constructor.
     *
     * @param  isys_component_database $p_db
     * @param  integer                 $p_cmdb_status
     */
    public function __construct(isys_component_database $p_db, $p_cmdb_status = null)
    {
        if (isset($_SESSION["cmdb_status"]) && !is_array($p_cmdb_status)) {
            $this->set_cmdb_status($_SESSION["cmdb_status"]);
        }

        parent::__construct($p_db);
    }
}
