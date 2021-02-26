<?php

use idoit\Module\Cmdb\Model\Ci\Table\Config;

/**
 * i-doit
 *
 * Objecttype DAO
 *
 * @package    i-doit
 * @subpackage CMDB_Low-Level_API
 * @author     Dennis Stuecken <dstuecken@synetics.de>
 * @version    1.4
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_object_type extends isys_cmdb_dao_object
{
    /**
     * @var isys_array
     */
    private $m_categories = null;

    /**
     * Free memory
     *
     * @param int       $unused1
     * @param string    $unused2
     * @param bool|true $unused3
     *
     * @return $this
     */
    public function clear($unused1, $unused2, $unused3 = true)
    {
        unset($this->m_categories);

        return $this;
    }

    /**
     *
     * @param   integer $p_object_type_id
     * @param   string  $p_list_config
     * @param   string  $p_list_query
     * @param   boolean $p_list_row_click
     * @param   string  $p_default_sorting
     * @param   string  $p_sorting_direction
     * @param   integer $p_user_obj_id
     * @param   Config  $tableConfig
     *
     * @return  boolean
     * @throws  isys_exception_dao
     * @throws  isys_exception_database
     */
    public function save_list_config(
        $p_object_type_id,
        $p_list_config,
        $p_list_query,
        $p_list_row_click = true,
        $p_default_sorting = null,
        $p_sorting_direction = 'asc',
        $p_user_obj_id = null,
        Config $tableConfig = null,
        $p_default_wildcard = false,
        $p_default_broadsearch = false,
        $showEmailLinks = true
    ) {
        global $g_comp_session;

        if ($p_user_obj_id === null) {
            $p_user_obj_id = $g_comp_session->get_user_id();
        }

        $l_sql = 'SELECT * FROM isys_obj_type_list
			WHERE isys_obj_type_list__isys_obj__id = ' . $this->convert_sql_id($p_user_obj_id) . '
			AND isys_obj_type_list__isys_obj_type__id = ' . $this->convert_sql_id($p_object_type_id) . ';';

        $l_res = $this->retrieve($l_sql);

        if (is_countable($l_res) && count($l_res)) {
            $l_row = $l_res->get_row();

            // @todo The fields "config", "row_clickable", "isys_property_2_cat__id" and "sorting_direction" are deprecated, but we keep them until i-doit 1.9.
            $l_sql = 'UPDATE isys_obj_type_list SET
              isys_obj_type_list__query = ' . $this->convert_sql_text($p_list_query) . ',
              isys_obj_type_list__config = ' . $this->convert_sql_text($p_list_config) . ',
              isys_obj_type_list__table_config = ' . $this->convert_sql_text($tableConfig !== null ? serialize($tableConfig) : '') . ',
              isys_obj_type_list__row_clickable = ' . $this->convert_sql_boolean($p_list_row_click) . ',
              isys_obj_type_list__isys_property_2_cat__id = ' . $this->convert_sql_id($p_default_sorting) . ',
              isys_obj_type_list__sorting_direction = ' . $this->convert_sql_text($p_sorting_direction) . ',
              isys_obj_type_list__default_filter_wildcard = ' . $this->convert_sql_boolean($p_default_wildcard) . ',
              isys_obj_type_list__default_filter_broadsearch = ' . $this->convert_sql_boolean($p_default_broadsearch) . ',
              isys_obj_type_list__show_email_links = ' . $this->convert_sql_boolean($showEmailLinks) . '
              WHERE isys_obj_type_list__id = ' . $this->convert_sql_id($l_row['isys_obj_type_list__id']) . ';';
        } else {
            // @todo The fields "config", "row_clickable", "isys_property_2_cat__id" and "sorting_direction" are deprecated, but we keep them until i-doit 1.9.
            $l_sql = 'INSERT INTO isys_obj_type_list SET
              isys_obj_type_list__isys_obj__id = ' . $this->convert_sql_id($p_user_obj_id) . ',
              isys_obj_type_list__isys_obj_type__id = ' . $this->convert_sql_id($p_object_type_id) . ',
              isys_obj_type_list__query = ' . $this->convert_sql_text($p_list_query) . ',
              isys_obj_type_list__config = ' . $this->convert_sql_text($p_list_config) . ',
              isys_obj_type_list__table_config = ' . $this->convert_sql_text($tableConfig !== null ? serialize($tableConfig) : '') . ',
              isys_obj_type_list__row_clickable = ' . $this->convert_sql_boolean($p_list_row_click) . ',
              isys_obj_type_list__isys_property_2_cat__id = ' . $this->convert_sql_id($p_default_sorting) . ',
              isys_obj_type_list__sorting_direction = ' . $this->convert_sql_text($p_sorting_direction) . ',
              isys_obj_type_list__default_filter_wildcard = ' . $this->convert_sql_boolean($p_default_wildcard) . ',
              isys_obj_type_list__show_email_links = ' . $this->convert_sql_boolean($showEmailLinks) . ',
              isys_obj_type_list__default_filter_broadsearch = ' . $this->convert_sql_boolean($p_default_broadsearch) . ';';
        }

        // Invalidate all list caches for this object type
        isys_cache::keyvalue()
            ->ns_invalidate('list.' . $p_object_type_id);

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * Get all assigned categories for specified object type.
     *
     * @param   integer $p_object_type_id
     * @param   array   $p_category_types
     * @param   string  $p_sortBy
     *
     * @return  mixed    &isys_array
     */
    public function &get_categories(
        $p_object_type_id,
        $p_category_types = [
            C__CMDB__CATEGORY__TYPE_GLOBAL,
            C__CMDB__CATEGORY__TYPE_SPECIFIC,
            C__CMDB__CATEGORY__TYPE_CUSTOM
        ],
        $p_sortBy = 'const'
    ) {
        if (!$this->m_categories || !isset($this->m_categories[$p_object_type_id])) {
            $this->m_categories = new isys_array();

            foreach ($p_category_types as $l_cattype) {
                $l_catdata = null;

                switch ($l_cattype) {
                    case C__CMDB__CATEGORY__TYPE_GLOBAL:
                        $l_dao_result = $this->get_global_categories($p_object_type_id, C__RECORD_STATUS__NORMAL, $p_sortBy);
                        while ($l_row = $l_dao_result->get_row()) {
                            $l_replaced = [];
                            array_walk($l_row, function ($p_value, $p_key) use (&$l_replaced) {
                                $l_replaced[str_replace('isysgui_catg__', '', $p_key)] = $p_value;
                            });
                            if ($l_row['isysgui_catg__id'] > 0) {
                                $l_catdata[$l_row['isysgui_catg__id']] = $l_replaced;

                                $l_dao_result_subcats = $this->catg_get_subcats($l_row['isysgui_catg__id'], true);

                                if ($l_dao_result_subcats->num_rows() > 0) {
                                    while ($l_row = $l_dao_result_subcats->get_row()) {
                                        $l_replaced = [];
                                        array_walk($l_row, function ($p_value, $p_key) use (&$l_replaced) {
                                            $l_replaced[str_replace('isysgui_catg__', '', $p_key)] = $p_value;
                                        });

                                        $l_catdata[$l_row['isysgui_catg__id']] = $l_replaced;
                                    }
                                    $l_dao_result_subcats->free_result();
                                }
                            }

                            unset($l_row);
                        }
                        $l_dao_result->free_result();
                        break;
                    case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                        $l_dao_result = $this->get_specific_category($p_object_type_id, C__RECORD_STATUS__NORMAL, null, null, $p_sortBy);
                        while ($l_row = $l_dao_result->get_row()) {
                            $l_replaced = [];
                            array_walk($l_row, function ($p_value, $p_key) use (&$l_replaced) {
                                $l_replaced[str_replace('isysgui_cats__', '', $p_key)] = $p_value;
                            });

                            if ($l_row['isysgui_cats__id'] > 0) {
                                $l_catdata[$l_row['isysgui_cats__id']] = $l_replaced;

                                $l_dao_result_subcats = $this->cats_get_subcats($l_row['isysgui_cats__id'], true);
                                if ($l_dao_result_subcats->num_rows() > 0) {
                                    while ($l_row = $l_dao_result_subcats->get_row()) {
                                        $l_replaced = [];
                                        array_walk($l_row, function ($p_value, $p_key) use (&$l_replaced) {
                                            $l_replaced[str_replace('isysgui_cats__', '', $p_key)] = $p_value;
                                        });

                                        $l_catdata[$l_row['isysgui_cats__id']] = $l_replaced;
                                    }
                                    $l_dao_result_subcats->free_result();
                                }
                            }

                            unset($l_row);
                        }
                        $l_dao_result->free_result();
                        break;
                    case C__CMDB__CATEGORY__TYPE_CUSTOM:
                        if (class_exists('isys_custom_fields_dao')) {
                            $l_cf_module = new isys_custom_fields_dao($this->m_db);

                            //$this->m_categories[$p_object_type_id][$l_cattype] = $l_cf_module->get_assignments(null, $p_object_type_id);
                            $l_dao_result = $l_cf_module->get_assignments(null, $p_object_type_id);

                            while ($l_row = $l_dao_result->get_row()) {
                                $l_replaced = [];
                                array_walk($l_row, function ($p_value, $p_key) use (&$l_replaced) {
                                    if (strpos($p_key, 'isys_obj_type__') === false) {
                                        $l_replaced[str_replace('isysgui_catg_custom__', '', $p_key)] = $p_value;
                                    }
                                });

                                if ($l_row['isysgui_catg_custom__id'] > 0) {
                                    $l_catdata[$l_row['isysgui_catg_custom__id']] = $l_replaced;

                                    $l_dao_result_subcats = $this->cats_get_subcats($l_row['isysgui_catg_custom__id'], true);
                                    if ($l_dao_result_subcats->num_rows() > 0) {
                                        while ($l_row = $l_dao_result_subcats->get_row()) {
                                            $l_replaced = [];
                                            array_walk($l_row, function ($p_value, $p_key) use (&$l_replaced) {
                                                $l_replaced[str_replace('isysgui_catg_custom__', '', $p_key)] = $p_value;
                                            });

                                            $l_catdata[$l_row['isysgui_catg_custom__id']] = $l_replaced;
                                        }
                                        $l_dao_result_subcats->free_result();
                                    }
                                }

                                unset($l_row);
                            }
                            $l_dao_result->free_result();
                        }
                        break;
                }

                if (is_array($l_catdata)) {
                    $this->m_categories[$p_object_type_id][$l_cattype] = new isys_array($l_catdata);
                } else {
                    $this->m_categories[$p_object_type_id][$l_cattype] = new isys_array();
                }

                unset($l_catdata);
            }
        }

        return $this->m_categories[$p_object_type_id];
    }

    /**
     * Check whether the Object-type has one of the categories inside of $p_constants.
     *
     * @param int   $p_obj_type  Object-TypeID
     * @param array $p_constants Category-Constants
     *
     * @return bool
     * @throws isys_exception_database
     */
    public function has_cat($p_obj_type, $p_constants = [])
    {
        if (is_countable($p_constants) && count($p_constants) == 0) {
            return false;
        }

        // Add quotation marks around the constants for the IN condition.
        $l_conditioner = [];
        $l_is_array = false;
        if (is_array($p_constants)) {
            $l_is_array = true;
            foreach ($p_constants as $l_cats_constant) {
                $l_conditioner[] = $this->convert_sql_text($l_cats_constant);
            }
            $l_where_condition_category = "AND igc.isysgui_catg__const IN(" . implode(",", $l_conditioner) . ");";
        } else {
            $l_where_condition_category = "AND igc.isysgui_catg__const = " . $this->convert_sql_text($p_constants) . ";";
        }

        // CATG-Query.
        $l_sql = "SELECT *
			FROM isys_obj_type_2_isysgui_catg
			INNER JOIN isysgui_catg igc ON isys_obj_type_2_isysgui_catg__isysgui_catg__id = igc.isysgui_catg__id
			WHERE isys_obj_type_2_isysgui_catg__isys_obj_type__id = " . $this->convert_sql_id($p_obj_type) . " " . $l_where_condition_category;

        $res = $this->retrieve($l_sql);
        if (is_countable($res) && count($res) > 0) {
            return true;
        }

        $l_res = $this->get_specific_category($p_obj_type, C__RECORD_STATUS__NORMAL, null, true);

        if (is_countable($l_res) && count($l_res) > 0) {
            while ($l_row = $l_res->get_row()) {
                if ($l_is_array) {
                    if (in_array($l_row["isysgui_cats__const"], $p_constants)) {
                        return true;
                    }
                } else {
                    if ($l_row["isysgui_cats__const"] == $p_constants) {
                        return true;
                    }
                }
            }
        }

        // @see  ID-7099 / API-199  Custom categories were simply never even checked :/
        if (is_array($p_constants)) {
            $constants = [];

            foreach ($p_constants as $constant) {
                $constants[] = $this->convert_sql_text($constant);
            }

            $condition = 'IN (' . implode(',', $constants) . ')';
        } else {
            $condition = '= ' . $this->convert_sql_text($p_constants);
        }

        $sql = 'SELECT isys_obj_type_2_isysgui_catg_custom__id
            FROM isys_obj_type_2_isysgui_catg_custom
            INNER JOIN isysgui_catg_custom ON isysgui_catg_custom__id = isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id
            WHERE isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id = ' . $this->convert_sql_id($p_obj_type) . '
            AND isysgui_catg_custom__const ' . $condition . ';';

        return (bool)count($this->retrieve($sql));
    }

    /**
     * Get all global categories for the given object type id (as a result set).
     *
     * @param   integer $p_obj_type
     * @param   integer $p_nRecStatus
     *
     * @return  isys_component_dao_result
     * @author  dennis stuecken <dstuecken@i-doit.org>
     */
    public function get_global_categories($p_obj_type, $p_nRecStatus = C__RECORD_STATUS__NORMAL, $p_sortBy = 'const')
    {
        $l_sql = "SELECT * , 
                  (
                    SELECT COUNT(parentCheck.isysgui_catg__id) FROM isysgui_catg AS parentCheck 
                    WHERE parentCheck.isysgui_catg__parent = isys_obj_type_2_isysgui_catg__isysgui_catg__id
                  ) AS childsAmount 
                FROM isysgui_catg
				INNER JOIN isys_obj_type_2_isysgui_catg ON isysgui_catg__id = isys_obj_type_2_isysgui_catg__isysgui_catg__id
				WHERE isysgui_catg__status = " . $this->convert_sql_int($p_nRecStatus) . " ";

        if (!is_null($p_obj_type)) {
            $l_sql .= " AND isys_obj_type_2_isysgui_catg__isys_obj_type__id = " . $this->convert_sql_id($p_obj_type) . "";
        }

        switch ($p_sortBy) {
            case 'const':
                $l_sortBy = 'isysgui_catg__const';
                break;
            case 'id':
                $l_sortBy = 'isysgui_catg__id';
                break;
            default:
                $l_sortBy = 'isysgui_catg__title';
                break;
        }

        $l_sql .= " ORDER BY " . $l_sortBy . " ASC;";

        return $this->retrieve($l_sql);
    }

    /**
     * Get the specific category for object type (as a result set)
     *
     * @param int $p_obj_type
     *
     * @author dennis stuecken <dstuecken@i-doit.org> 2007-07-23
     * @return isys_component_dao_result
     */
    public function get_specific_category($p_obj_type = null, $p_nRecStatus = C__RECORD_STATUS__NORMAL, $p_category_id = null, $p_cats_childs = null, $p_sortBy = 'const')
    {
        if (empty($p_nRecStatus)) {
            $p_nRecStatus = C__RECORD_STATUS__NORMAL;
        }

        $l_sql = "SELECT *, 
            (
              SELECT COUNT(isysgui_cats_2_subcategory__isysgui_cats__id__child) FROM isysgui_cats_2_subcategory 
              WHERE isysgui_cats_2_subcategory__isysgui_cats__id__parent = isysgui_cats__id
            ) AS childsAmount  
            FROM isysgui_cats 
			INNER JOIN isys_obj_type ON isys_obj_type__isysgui_cats__id = isysgui_cats__id
			WHERE isysgui_cats__status = " . $this->convert_sql_int($p_nRecStatus);

        if (!is_null($p_category_id)) {
            $l_sql .= " AND isysgui_cats__id = " . $this->convert_sql_id($p_category_id);
        }

        if (!is_null($p_obj_type)) {
            $l_sql .= " AND isys_obj_type__id = " . $this->convert_sql_id($p_obj_type);
        }

        switch ($p_sortBy) {
            case 'const':
                $l_sortBy = 'isysgui_cats__const';
                break;
            case 'id':
                $l_sortBy = 'isysgui_cats__id';
                break;
            default:
                $l_sortBy = 'isysgui_cats__title';
                break;
        }

        $l_sql .= " ORDER BY " . $l_sortBy . " ASC;";

        if ($p_cats_childs) {
            $l_row = $this->retrieve($l_sql . ';')
                ->get_row();
            $l_spec_cats = [$l_row['isysgui_cats__id']];
            $l_childRes = $this->retrieve("SELECT * FROM isysgui_cats_2_subcategory
				INNER JOIN isysgui_cats ON isysgui_cats__id = isysgui_cats_2_subcategory__isysgui_cats__id__child
				WHERE isysgui_cats_2_subcategory__isysgui_cats__id__parent = " . $this->convert_sql_id($l_row['isysgui_cats__id']));

            if ($l_childRes->num_rows() > 0) {
                while ($l_row = $l_childRes->get_row()) {
                    $l_spec_cats[] = $l_row["isysgui_cats__id"];
                }

                if (count($l_spec_cats) > 0) {
                    return $this->retrieve("SELECT * FROM isysgui_cats WHERE isysgui_cats__id IN(" . implode(",", $l_spec_cats) . ")");
                } else {
                    return false;
                }
            }
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for refreshing all object type lists.
     *
     * @param   integer $p_object_type_id
     * @param   boolean $p_execute_query
     * @param   boolean $p_return_query_without_save
     *
     * @return  mixed
     * @throws  \idoit\Exception\JsonException
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function refresh_objtype_list_config($p_object_type_id = null, $p_execute_query = false, $p_return_query_without_save = false)
    {
        $l_condition = '';

        if ($p_object_type_id !== null) {
            $l_condition = ' WHERE isys_obj_type_list__isys_obj_type__id = ' . $this->convert_sql_id($p_object_type_id);
        }

        $l_res = $this->retrieve('SELECT * FROM isys_obj_type_list ' . $l_condition . ';');

        if (is_countable($l_res) && count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                $l_new_query = $this->refresh_objtype_list_query_by_config($l_row['isys_obj_type_list__config'], $l_row['isys_obj_type_list__isys_obj_type__id']);

                // Check the query for syntax errors.
                if ($p_execute_query) {
                    $this->retrieve($l_new_query . ' LIMIT 1;');
                }

                if ($p_return_query_without_save) {
                    return $l_new_query;
                }

                $l_sql = 'UPDATE isys_obj_type_list
                    SET isys_obj_type_list__query = ' . $this->convert_sql_text($l_new_query) . '
                    WHERE isys_obj_type_list__id = ' . $this->convert_sql_id($l_row['isys_obj_type_list__id']) . ';';

                return $this->update($l_sql) && $this->apply_update();
            }
        }
    }

    /**
     * Method for refreshing all object type lists.
     *
     * @param   string  $p_json
     * @param   integer $p_objtype
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function refresh_objtype_list_query_by_config($p_json, $p_objtype)
    {
        $l_cache = $l_properties = [];
        $l_dao = isys_cmdb_dao_category_property_ng::instance($this->m_db);
        $l_configs = isys_format_json::decode($p_json);

        foreach ($l_configs as $l_index => $l_config) {
            list($l_type, $l_prop_key, , , $l_property_method) = $l_config;

            if (!isset($l_cache[$l_property_method . '::' . $l_prop_key])) {
                // Get the property ID from isys_property_2_cat.
                $l_class = explode('::', $l_property_method)[0];

                if (!class_exists($l_class) || !method_exists($l_class, 'get_category_const')) {
                    continue;
                }

                $l_sql = 'SELECT isys_property_2_cat__id
                    FROM isys_property_2_cat
                    WHERE isys_property_2_cat__cat_const = ' . $this->convert_sql_text(isys_factory::get_instance($l_class, $this->m_db)
                        ->get_category_const()) . '
                    AND isys_property_2_cat__prop_key = ' . $this->convert_sql_text($l_prop_key) . '
                    AND isys_property_2_cat__prop_type = ' . $this->convert_sql_int($l_type) . '
                    AND isys_property_2_cat__prop_provides & ' . $this->convert_sql_int(C__PROPERTY__PROVIDES__LIST) . '
                    LIMIT 1;';

                $l_prop_res = $l_dao->retrieve($l_sql);

                // If no property was found - continue and don't process further.
                if (is_countable($l_prop_res) && count($l_prop_res)) {
                    $l_cache[$l_property_method . '::' . $l_prop_key] = $l_prop_res->get_row_value('isys_property_2_cat__id');
                } else {
                    $l_cache[$l_property_method . '::' . $l_prop_key] = false;
                }
            }

            if ($l_cache[$l_property_method . '::' . $l_prop_key] > 0) {
                // Use the "new config" in case that any old properties will not be added to the query.
                $l_properties[] = $l_cache[$l_property_method . '::' . $l_prop_key];
            }
        }

        return $l_dao->reset()
            ->create_property_query_for_lists($l_properties, $p_objtype);
    }
}
