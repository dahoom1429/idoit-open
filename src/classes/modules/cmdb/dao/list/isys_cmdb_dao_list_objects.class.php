<?php

use idoit\Module\Cmdb\Model\Ci\Table\Config;
use idoit\Module\Cmdb\Model\Ci\Table\Property;

/**
 * i-doit
 *
 * List DAO parent - Will be used for all object types without an own list DAO.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-9
 */
class isys_cmdb_dao_list_objects extends isys_cmdb_dao_list
{
    /**
     * Member variable for additional selects.
     *
     * @var array
     */
    protected $additional_selects = [];

    /**
     * Flag if the current object list is allowed to use group by
     *
     * @var bool
     */
    protected $m_allow_group_by = true;

    /**
     * Flag if the current object list is allowed to use order by
     *
     * @var bool
     */
    protected $m_allow_order_by = true;

    /**
     * Logical operation for conditions
     *
     * true = and
     * false = or
     * @var bool
     */
    protected $operation = true;

    /**
     * Variable for the DAO result.
     *
     * @var  isys_component_dao_result
     */
    protected $m_dao_result;

    /**
     * Variable which holds the user-defined object type list (if defined).
     *
     * @var  array
     */
    protected $m_list_row = null;

    /**
     * Variable which holds the current object-type row from isys_obj_type.
     *
     * @var  array
     */
    protected $m_object_type = [];

    /**
     * Variable which holds the current user ID.
     *
     * @var  integer
     */
    protected $m_user = 0;

    /**
     * Member variable for additional conditions.
     *
     * @var array
     */
    protected $m_additional_conditions = [];

    /**
     * Member variable for additional having conditions.
     *
     * @var array
     */
    protected $m_additional_having_conditions = [];

    /**
     * @var string
     */
    protected $order_by_property = null;

    /**
     * @var string
     */
    protected $order_by_direction = null;

    /**
     * This should get switched on, if any query fails.
     *
     * @var boolean
     */
    protected $use_defaults = false;

    /**
     * @var idoit\Module\Cmdb\Model\Ci\Table\Config
     */
    protected $tableConfiguration;

    /**
     * Deactivates "GROUP BY obj_main.isys_obj__id"
     */
    public function deactivate_group_by()
    {
        $this->m_allow_group_by = false;
    }

    /**
     * Deactivates "ORDER BY ..."
     */
    public function deactivate_order_by()
    {
        $this->m_allow_order_by = false;
    }

    /**
     * Activates "Order By ..."
     */
    public function activate_order_by()
    {
        $this->m_allow_order_by = true;
    }

    /**
     * Method for formating the results from the database and the dynamic callbacks.
     *
     * @deprecated  This method should not be used anymore.
     *
     * @param   isys_component_dao_result $p_result
     * @param                             array [$p_list_config array]
     *
     * @return  array
     * @author      Dennis Stücken <dstuecken@i-doit.org>
     */
    public function format_result(isys_component_dao_result $p_result, $p_list_config = [])
    {
        $l_array = [];
        $i = 0;
        $l_rowarray = [];

        // Get default object list config
        if (!$p_list_config || !is_countable($p_list_config) || count($p_list_config) == 0) {
            $p_list_config = isys_format_json::decode($this->get_default_list_config());
        }

        // We use this little trick to apply the ID as the first array element.
        $p_list_config = array_reverse($p_list_config);
        $p_list_config[] = [
            C__PROPERTY_TYPE__STATIC,
            false,
            'isys_obj__id',
            '__id__',
            false,
            false
        ];
        $p_list_config = array_reverse($p_list_config);

        while ($l_row = $p_result->get_row()) {
            foreach ($p_list_config as $l_config) {
                list($l_property_type, $l_propkey, $l_rowfield, $l_title, $l_get_properties_method, $l_dynamic_callback, $l_cat_name, $l_custom_cat_const) = $l_config;
                // Category class
                $l_className = substr($l_get_properties_method, 0, strpos($l_get_properties_method, '::'));
                // Key for retrieving the value if its set
                $l_valueKey = $l_className . '__' . $l_propkey;

                $l_value = '';

                // Check which type of property we got.
                if ($l_property_type == C__PROPERTY_TYPE__STATIC) {
                    $l_property_callback = [
                        explode('::', $l_get_properties_method)[0],
                        'instance'
                    ];

                    if (is_callable($l_property_callback)) {
                        /**
                         * @var isys_cmdb_dao_category $l_instance
                         */
                        $l_instance = call_user_func($l_property_callback, $this->m_db);

                        if (defined($l_custom_cat_const) && method_exists($l_instance, 'set_catg_custom_id')) {
                            $l_instance->set_catg_custom_id(constant($l_custom_cat_const));
                        }

                        if ($l_property = $l_instance->get_property_by_key($l_propkey)) {
                            if ($l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__DATE ||
                                $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__DATETIME) {
                                $l_row[$l_rowfield] = isys_locale::get_instance()
                                    ->fmt_date($l_row[$l_rowfield]);
                            }
                        }
                    }

                    if (isset($l_row[$l_valueKey])) {
                        $l_value = isys_application::instance()->container->get('language')
                            ->get_in_text($l_row[$l_valueKey]);
                    } else {
                        // Fallback if we can retrieve the data with the database field
                        // We look for fields with "__isys_obj_id" in the name, because we want to display objects instead of ID's.
                        if (strpos($l_rowfield, '__isys_obj__id') && is_numeric($l_row[$l_rowfield])) {
                            $l_value = isys_cmdb_dao_category::dynamic_property_callback_object($l_row[$l_rowfield]);
                        } else {
                            $l_value = isys_application::instance()->container->get('language')
                                ->get($l_row[$l_rowfield]);
                        }
                    }
                } else {
                    if (is_string($l_dynamic_callback[0]) && method_exists($l_dynamic_callback[0], 'instance')) {
                        $l_dynamic_callback[0] = $l_dynamic_callback[0]::instance($this->get_database_component());
                    }

                    if (is_object($l_dynamic_callback[0]) && method_exists($l_dynamic_callback[0], $l_dynamic_callback[1])) {
                        $l_value = call_user_func($l_dynamic_callback, $l_row);
                    }
                }

                if (empty($l_value)) {
                    // Check again with other key
                    if (array_key_exists($l_title . '###' . $l_property_type, $l_row)) {
                        $l_value = isys_application::instance()->container->get('language')
                            ->get($l_row[$l_title . '###' . $l_property_type]);
                    }
                }

                $l_rowarray[isys_application::instance()->container->get('language')
                    ->get($l_title)] = $l_value;

                unset($l_value, $l_key);
            }

            $l_array[$i] = $l_rowarray;
            unset($l_rowarray);
            $i++;
        }

        return $l_array;
    }

    /**
     * Method for retrieving additional conditions to a object type.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_additional_conditions()
    {
        return (is_array($this->m_additional_conditions) ? implode(' ', $this->m_additional_conditions) : $this->m_additional_conditions);
    }

    /**
     * Method for retrieving additional conditions to a object type.
     *
     * @return array
     * @author  Pavel Abduramanov <pabduramanov@i-doit.org>
     */
    public function getAdditionalConditions()
    {
        return $this->m_additional_conditions;
    }

    /**
     * @param string $p_condition
     *
     * @return $this
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function add_additional_conditions($p_condition)
    {
        $this->m_additional_conditions[] = $p_condition;

        return $this;
    }

    /**
     * Use this method to set additional conditions (array or string).
     *
     * @param  mixed $p_condition
     *
     * @return $this
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function set_additional_conditions($p_condition)
    {
        if (!is_array($p_condition)) {
            $p_condition = [$p_condition];
        }

        $this->m_additional_conditions = $p_condition;

        return $this;
    }

    /**
     * Method for retrieving additional having conditions to a object type.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_additional_having_conditions()
    {
        $l_having = trim(is_array($this->m_additional_having_conditions) ? implode(' AND ', $this->m_additional_having_conditions) : $this->m_additional_having_conditions);

        if (!empty($l_having)) {
            return 'HAVING ' . $l_having . ' ';
        }

        return '';
    }

    /**
     * @return array
     */
    public function getAdditionalHavingConditions()
    {
        return $this->m_additional_having_conditions;
    }

    /**
     * @param string $p_condition
     *
     * @return $this
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function add_additional_having_conditions($p_condition)
    {
        $this->m_additional_having_conditions[] = $p_condition;

        return $this;
    }

    /**
     * Use this method to set additional having conditions (array or string).
     *
     * @param  mixed $p_condition
     *
     * @return $this
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function set_additional_having_conditions($p_condition)
    {
        if (!is_array($p_condition)) {
            $p_condition = [$p_condition];
        }

        $this->m_additional_having_conditions = $p_condition;

        return $this;
    }

    /**
     * Method for retrieving additional joins to a object type.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_additional_joins()
    {
        return '';
    }

    /**
     * Method for retrieving additional selects to a object type.
     *
     * @return  string
     * @author  Pavel Abduramanov <pabduramanov@i-doit.org>
     */
    public function get_additional_selects()
    {
        if (!is_array($this->additional_selects)) {
            return '';
        }
        return implode(', ', array_map(function ($value, $key) {
            return "($value) as `$key`";
        }, $this->additional_selects, array_keys($this->additional_selects)));
    }

    /**
     * @return array
     */
    public function getAdditionalSelects()
    {
        return $this->additional_selects;
    }

    /**
     * @param string $select
     * @param        $key
     *
     * @return $this
     * @author Pavel Abduramanov <pabduramanov@i-doit.org>
     */
    public function add_additional_selects($select, $key)
    {
        $this->additional_selects[$key] = $select;

        return $this;
    }

    /**
     * Use this method to set additional selects (array or string).
     *
     * @param  mixed $select
     *
     * @return $this
     * @author Pavel Abduramanov <pabduramanov@i-doit.org>
     */
    public function set_additional_selects($select)
    {
        $this->additional_selects = $select;

        return $this;
    }

    /**
     * Will return a isys_component_dao_result or null.
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_dao_result()
    {
        return $this->m_dao_result;
    }

    /**
     * Method for retrieving the default JSON encoded configuration array for all object types.
     *
     * @deprecated  Please try to rewrite this method to "get_default_table_config()".
     * @return  string
     * @author      Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_default_list_config()
    {
        if (isys_tenantsettings::has('cmdb.default-object-list.config.' . $this->m_object_type['isys_obj_type__const'])) {
            return isys_format_json::decode(isys_tenantsettings::get('cmdb.default-object-list.config.' . $this->m_object_type['isys_obj_type__const']));
        }

        if (isys_tenantsettings::has('cmdb.base-object-list.config.' . $this->m_object_type['isys_obj_type__const'])) {
            return isys_format_json::decode(isys_tenantsettings::get('cmdb.base-object-list.config.' . $this->m_object_type['isys_obj_type__const']));
        }

        return isys_format_json::encode([
            [
                C__PROPERTY_TYPE__DYNAMIC,
                "_title",
                false,
                "LC__UNIVERSAL__TITLE_LINK",
                "isys_cmdb_dao_category_g_global::get_dynamic_properties",
                ["isys_cmdb_dao_category_g_global", "dynamic_property_callback_title"]
            ],
            [
                C__PROPERTY_TYPE__STATIC,
                "location_path",
                "isys_catg_location_list__parentid",
                "LC__CMDB__CATG__LOCATION_PATH",
                "isys_cmdb_dao_category_g_location::get_properties",
                false
            ],
            [
                C__PROPERTY_TYPE__DYNAMIC,
                "_created",
                false,
                "LC__TASK__DETAIL__WORKORDER__CREATION_DATE",
                "isys_cmdb_dao_category_g_global::get_dynamic_properties",
                ["isys_cmdb_dao_category_g_global", "dynamic_property_callback_created"]
            ],
            [
                C__PROPERTY_TYPE__DYNAMIC,
                "_changed",
                false,
                "LC__CMDB__LAST_CHANGE",
                "isys_cmdb_dao_category_g_global::get_dynamic_properties",
                ["isys_cmdb_dao_category_g_global", "dynamic_property_callback_changed"]
            ],
            [C__PROPERTY_TYPE__STATIC, "purpose", "isys_purpose__title", "LC__CMDB__CATG__GLOBAL_PURPOSE", "isys_cmdb_dao_category_g_global::get_properties", false],
            [
                C__PROPERTY_TYPE__DYNAMIC,
                "_cmdb_status",
                false,
                "LC__UNIVERSAL__CMDB_STATUS",
                "isys_cmdb_dao_category_g_global::get_dynamic_properties",
                ["isys_cmdb_dao_category_g_global", "dynamic_property_callback_cmdb_status"]
            ]
        ]);
    }

    /**
     * Method for retrieving the default JSON encoded configuration array for all object types.
     *
     * @return  Config
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_default_table_config()
    {
        $l_instance = null;

        if (isys_tenantsettings::has('cmdb.default-object-table.config.' . $this->m_object_type['isys_obj_type__const'])) {
            $l_config = isys_tenantsettings::get('cmdb.default-object-table.config.' . $this->m_object_type['isys_obj_type__const']);

            if (is_string($l_config) && $l_config) {
                $l_instance = unserialize($l_config);
            }
        } else {
            if (isys_tenantsettings::has('cmdb.base-object-table.config.' . $this->m_object_type['isys_obj_type__const'])) {
                $l_config = isys_tenantsettings::get('cmdb.base-object-table.config.' . $this->m_object_type['isys_obj_type__const']);

                if (is_string($l_config) && $l_config) {
                    $l_instance = unserialize($l_config);
                }
            }
        }

        if (!$l_instance || !is_a($l_instance, '\idoit\Module\Cmdb\Model\Ci\Table\Config')) {
            return (new Config())->setRowClickable(true)
                ->setFilterWildcard(true)
                ->setGroupingType(isys_cmdb_dao_category_property_ng::C__GROUPING__LIST)
                ->setSortingProperty('isys_cmdb_dao_category_g_global__title')
                ->setSortingDirection('ASC')
                ->setProperties([
                    new Property('isys_cmdb_dao_category_g_global', 'id', 'LC__CMDB__CATG__GLOBAL', 'LC__CMDB__OBJTYPE__ID', true, null, C__PROPERTY__INFO__TYPE__INT),
                    new Property('isys_cmdb_dao_category_g_global', 'title', 'LC__CMDB__CATG__GLOBAL', 'LC__UNIVERSAL__TITLE', true, null, C__PROPERTY__INFO__TYPE__TEXT),
                    new Property('isys_cmdb_dao_category_g_location', 'location_path', 'LC__CMDB__CATG__LOCATION', 'LC__CMDB__CATG__LOCATION_PATH', false, null, C__PROPERTY__INFO__TYPE__TEXT),
                    new Property('isys_cmdb_dao_category_g_global', 'created', 'LC__CMDB__CATG__GLOBAL', 'LC__TASK__DETAIL__WORKORDER__CREATION_DATE', false, null, C__PROPERTY__INFO__TYPE__TEXT),
                    new Property('isys_cmdb_dao_category_g_global', 'changed', 'LC__CMDB__CATG__GLOBAL', 'LC__CMDB__LAST_CHANGE', false, null, C__PROPERTY__INFO__TYPE__TEXT),
                    new Property('isys_cmdb_dao_category_g_global', 'cmdb_status', 'LC__CMDB__CATG__GLOBAL', 'LC__UNIVERSAL__CMDB_STATUS', true, null, C__PROPERTY__INFO__TYPE__DIALOG)
                ]);
        }

        return $l_instance;
    }

    /**
     * Method for retrieving the default list query for all objects.
     *
     * @note    DS: isys_purpose Subquery needed for performance optimization
     *
     * @return  string
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     */
    public function get_default_table_query()
    {
        if (isys_tenantsettings::has('cmdb.default-object-table.sql.' . $this->m_object_type['isys_obj_type__const'])) {
            return isys_tenantsettings::get('cmdb.default-object-table.sql.' . $this->m_object_type['isys_obj_type__const']);
        }

        if (isys_tenantsettings::has('cmdb.base-object-table.sql.' . $this->m_object_type['isys_obj_type__const'])) {
            return isys_tenantsettings::get('cmdb.base-object-table.sql.' . $this->m_object_type['isys_obj_type__const']);
        }

        return "SELECT
            obj_main.isys_obj__id AS '__id__',
            obj_main.isys_obj__id 'isys_cmdb_dao_category_g_global__id',
            obj_main.isys_obj__title 'isys_cmdb_dao_category_g_global__title',
             (SELECT  CONCAT_WS(' >  ', (SELECT CONCAT(isys_obj__title, ' {', isys_obj__id, '}') FROM isys_obj WHERE sub5.isys_catg_location_list__isys_obj__id = isys_obj__id),(SELECT CONCAT(isys_obj__title, ' {', isys_obj__id, '}') FROM isys_obj WHERE sub4.isys_catg_location_list__isys_obj__id = isys_obj__id),(SELECT CONCAT(isys_obj__title, ' {', isys_obj__id, '}') FROM isys_obj WHERE sub3.isys_catg_location_list__isys_obj__id = isys_obj__id),(SELECT CONCAT(isys_obj__title, ' {', isys_obj__id, '}') FROM isys_obj WHERE sub2.isys_catg_location_list__isys_obj__id = isys_obj__id),(SELECT CONCAT(isys_obj__title, ' {', isys_obj__id, '}') FROM isys_obj WHERE sub1.isys_catg_location_list__isys_obj__id = isys_obj__id)) AS title FROM isys_catg_location_list AS main  LEFT JOIN isys_catg_location_list AS sub1 ON sub1.isys_catg_location_list__isys_obj__id = main.isys_catg_location_list__parentid  LEFT JOIN isys_catg_location_list AS sub2 ON sub2.isys_catg_location_list__isys_obj__id = sub1.isys_catg_location_list__parentid  LEFT JOIN isys_catg_location_list AS sub3 ON sub3.isys_catg_location_list__isys_obj__id = sub2.isys_catg_location_list__parentid  LEFT JOIN isys_catg_location_list AS sub4 ON sub4.isys_catg_location_list__isys_obj__id = sub3.isys_catg_location_list__parentid  LEFT JOIN isys_catg_location_list AS sub5 ON sub5.isys_catg_location_list__isys_obj__id = sub4.isys_catg_location_list__parentid   WHERE main.isys_catg_location_list__isys_obj__id = obj_main.isys_obj__id   ) 'isys_cmdb_dao_category_g_location__location_path' ,
             (CONCAT(obj_main.isys_obj__created, ' (' , obj_main.isys_obj__created_by, ')')   ) 'isys_cmdb_dao_category_g_global__created' ,
             (CONCAT(obj_main.isys_obj__updated, ' (' , obj_main.isys_obj__updated_by, ')')   ) 'isys_cmdb_dao_category_g_global__changed' ,
             (SELECT CONCAT('{#', isys_cmdb_status__color, '} ', isys_cmdb_status__title) FROM isys_cmdb_status  WHERE obj_main.isys_obj__isys_cmdb_status__id = isys_cmdb_status__id   ) 'isys_cmdb_dao_category_g_global__cmdb_status'
            FROM isys_obj AS obj_main
            WHERE TRUE
            AND obj_main.isys_obj__isys_obj_type__id = " . $this->convert_sql_id($this->m_object_type['isys_obj_type__id']);
    }

    /**
     * Method for retrieving the JSON encoded configuration array for the current object type.
     *
     * @deprecated  Please try to rewrite your code to use "get_table_config".
     *
     * @param   boolean $p_default
     *
     * @return  array
     */
    public function get_list_config($p_default = false)
    {
        $l_user_config = $this->load_user_config();

        if (!is_array($l_user_config) || $p_default || $this->use_defaults) {
            // If the user didn't define his or her own list, we take the default one provided by the DAO.
            $l_config = isys_tenantsettings::get('cmdb.default-object-list.config.' . $this->m_object_type['isys_obj_type__const'], $this->get_default_list_config());
        } else {
            $l_config = $l_user_config['isys_obj_type_list__config'];
        }

        // Fixing problematic config contents in cmdb.default-object-list.config because this could result in an empty list
        if (!is_string($l_config) || !isys_format_json::is_json($l_config)) {
            $l_config = $this->get_default_list_config();
        }

        return isys_format_json::decode($l_config);
    }

    /**
     * Method for finding out if the row shall be clickable.
     *
     * @deprecated This is a fallback, in case the new Config does not work
     * @return boolean
     */
    public function get_list_row_clickable()
    {
        $l_user_config = $this->load_user_config();

        if (is_array($l_user_config)) {
            return !!$l_user_config['isys_obj_type_list__row_clickable'];
        }

        return true;
    }

    /**
     * Set the table configuration for this instance.
     *
     * @param Config $configuration
     *
     * @return $this
     */
    public function set_table_config(Config $configuration)
    {
        $this->tableConfiguration = $configuration;

        return $this;
    }

    /**
     * @param bool $p_default
     *
     * @return Config
     */
    public function get_table_config($p_default = false)
    {
        if ($this->tableConfiguration !== null) {
            return $this->tableConfiguration;
        }

        $l_user_config = $this->load_user_config();

        if (!is_array($l_user_config) || $p_default || $this->use_defaults) {
            // If the user didn't define his or her own list, we take the default one provided by the DAO.
            $l_config = $this->get_default_table_config();
        } else {
            $l_config = $l_user_config['isys_obj_type_list__table_config'];
        }

        if (is_string($l_config) && $l_config) {
            $l_instance = unserialize($l_config);

            if ($l_instance && is_a($l_instance, '\idoit\Module\Cmdb\Model\Ci\Table\Config')) {
                return $l_instance;
            }
        }

        // Fixing problematic config contents in cmdb.default-object-list.config because this could result in an empty list
        return $this->get_default_table_config();
    }

    /**
     * This method is used by an AJAX call to get more rows, once the defined limit (default 30 pages) is reached.
     *
     * @param   mixed   $p_offset May be an integer or false to disable the offset.
     * @param   integer $p_length
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_table_offset($p_offset = 0, $p_length = 0)
    {
        if ($p_offset === false) {
            return '';
        }

        return ' LIMIT ' . ((int)$p_offset) . ', ' . ((int)$p_length);
    }

    /**
     * This method will return a SQL query to select the desired data for the object type lists.
     *
     * @param   mixed   $p_offset May be an integer or false for no offset.
     * @param   integer $p_length
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_table_query($p_offset = 0, $p_length = 0)
    {
        $l_obj_status = ($this->get_rec_status() ? $this->get_rec_status() : C__RECORD_STATUS__NORMAL);

        if (is_array($l_cmdb_status = $this->get_cmdb_status())) {
            if (defined('C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE') && is_numeric(array_search(constant('C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE'), $l_cmdb_status))) {
                $l_obj_status = C__RECORD_STATUS__TEMPLATE;
            }
        }

        /*
         * @see  ID-6004
         * It is now possible to overwrite the default table config "from outside".
         * Also we need to disable the cache usage, because this will always return the original query (without forced "comma" mode, for example).
         */
        if ($this->tableConfiguration !== null) {
            $l_table_conf = $this->tableConfiguration;
        } else {
            // Retrieve user list config or default list config
            $l_list_config = ($this->load_user_config() ?: $this->get_default_table_config());

            /**
             * @var $l_table_conf idoit\Module\Cmdb\Model\Ci\Table\Config
             */
            if (is_array($l_list_config)) {
                $l_table_conf = unserialize($l_list_config['isys_obj_type_list__table_config']);
            } elseif (is_object($l_list_config)) {
                $l_table_conf = $l_list_config;
            }

            if (!is_object($l_table_conf) || !is_a($l_table_conf, '\idoit\Module\Cmdb\Model\Ci\Table\Config')) {
                $l_table_conf = $this->get_default_table_config();
            }
        }

        $l_properties = $l_table_conf->getProperties();

        $l_dao_property_ng = isys_cmdb_dao_category_property_ng::instance(isys_application::instance()->container->get('database'));
        $l_dao_property_ng->setGrouping($l_table_conf->getGroupingType());
        $l_prop_ids_arr = [];

        foreach ($l_properties as $l_prop) {
            if (is_a($l_prop, '\idoit\Module\Cmdb\Model\Ci\Table\Property')) {
                $l_cat_class = $l_prop->getClass();
                $l_prop_key = $l_prop->getKey();
                $l_catg_custom_id = $l_prop->getCustomCatID();
                $l_condition = '(isysgui_catg__class_name = ' . $this->convert_sql_text($l_cat_class) . ' OR
            isysgui_cats__class_name = ' . $this->convert_sql_text($l_cat_class) . ' OR
            isysgui_catg_custom__class_name = ' . $this->convert_sql_text($l_cat_class) . ') AND
            isys_property_2_cat__prop_key = ' . $this->convert_sql_text($l_prop_key);

                if ($l_catg_custom_id > 0) {
                    $l_condition .= ' AND isys_property_2_cat__isysgui_catg_custom__id = ' . $this->convert_sql_id($l_catg_custom_id);
                }

                $l_prop_ids_arr[] = $l_dao_property_ng->get_property_id_by_condition($l_condition);
            }
        }

        $l_return = $l_dao_property_ng->reset()
            ->create_property_query_for_lists($l_prop_ids_arr, $this->m_object_type['isys_obj_type__id']);

        // Set the additional joins BEFORE the last WHERE.
        $l_return = substr($l_return, 0, strrpos($l_return, 'WHERE')) . ' ' . $this->get_additional_joins() . ' ' . substr($l_return, strrpos($l_return, 'WHERE'));

        // We should not cache this because if we change the cmdb status, the query has to filter by that
        $l_status_filter = $this->prepare_status_filter();

        if (strpos($l_status_filter, ' isys_obj__') !== false || strpos($l_status_filter, '(isys_obj__') !== false) {
            $l_status_filter = str_replace([' isys_obj__', '(isys_obj__'], [' obj_main.isys_obj__', ' obj_main.isys_obj__'], $l_status_filter);
        }

        $l_allowed_objects_condition = isys_auth_cmdb_objects::instance()
            ->get_allowed_objects_condition(isys_auth::VIEW);

        if ($l_allowed_objects_condition != '' /* && strpos($l_allowed_objects_condition, 'obj_main') === false */) {
            $l_allowed_objects_condition = str_replace(
                ['(isys_obj__', ' isys_obj__', "\nisys_obj__"],
                ['(obj_main.isys_obj__', ' obj_main.isys_obj__', "\nobj_main.isys_obj__"],
                $l_allowed_objects_condition
            );
        }

        $l_return = $this->addConditionsToQuery(
            $l_return,
            $l_status_filter . ' AND obj_main.isys_obj__status = ' . $this->convert_sql_int($l_obj_status) . ' ' . $l_allowed_objects_condition,
            $p_offset,
            $p_length
        );

        return $l_return;
    }

    /**
     * Helper to build query string
     *
     * @param        $sql
     * @param string $extraWhere
     * @param int    $offset
     * @param int    $length
     *
     * @return string
     */
    protected function addConditionsToQuery($sql, $extraWhere = '', $offset = 0, $length = 50)
    {
        $additionalSelects = $this->get_additional_selects();
        $additionalConditions = $this->get_additional_conditions();
        $additionalHavingConditions = $this->get_additional_having_conditions();

        if (!$this->operation) {
            $cond = $this->m_additional_conditions;
            foreach ($cond as $i => &$v) {
                $v = substr($v, strlen('AND '));
            }

            $having = $this->m_additional_having_conditions;
            if (is_countable($cond) && count($cond) > 0) {
                $whereField = 'where' . rand();
                $select = 'IF(' . implode(' OR ', $cond) . ', 1, 0) as `' . $whereField . '`';
                $additionalSelects .= ('' !== $additionalSelects ? ', ' : '') . $select;
                $having[] = '`' . $whereField . '` > 0';
            }
            $additionalConditions = '';
            if (is_countable($having) && count($having) > 0) {
                $additionalHavingConditions = 'HAVING (' . implode(' OR ', $having) . ')';
            }
        }
        if ($additionalSelects) {
            $sql = substr($sql, 0, strrpos($sql, 'FROM')) . ', ' . $additionalSelects . ' ' . substr($sql, strrpos($sql, 'FROM'));
        }
        if ($extraWhere) {
            $sql .= $extraWhere;
        }
        $sql .= ' ' . $additionalConditions;
        if ($this->m_allow_group_by) {
            $sql .= ' GROUP BY obj_main.isys_obj__id';
        }
        $sql .= ' ' . $additionalHavingConditions;
        if ($this->m_allow_order_by) {
            $sql .= $this->get_table_sorting($this->order_by_property, $this->order_by_direction);
        }
        $sql .= $this->get_table_offset($offset, $length);

        return $sql;
    }

    /**
     * Method for counting, how many objects this list instance is holding.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_object_count()
    {
        $this->deactivate_order_by();
        $l_sql = $this->get_table_query(false);
        $this->activate_order_by();

        // First we modify the SQL to find out, with how many rows we are dealing...
        $l_rowcount_sql = 'SELECT COUNT(*) AS count FROM (' . rtrim($l_sql, ';') . ') AS tmp;';

        try {
            $l_rowcount = $this->retrieve($l_rowcount_sql)
                ->get_row_value('count');
        } catch (Exception $e) {
            // If our first try fails because we broke the SQL, we use this here...
            $l_rowcount = $this->retrieve($l_sql)
                ->count();
        }

        return (int)$l_rowcount;
    }

    /**
     * This method sets the object type of this instance.
     *
     * @param   mixed $p_object_type May be an ID (integer) or the row from isys_obj_type (array).
     *
     * @return  isys_cmdb_dao_list_objects
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function set_object_type($p_object_type)
    {
        if (is_array($p_object_type)) {
            $this->m_object_type = $p_object_type;
        } elseif (is_numeric($p_object_type)) {
            $this->m_object_type = isys_cmdb_dao::instance($this->get_database_component())
                ->get_objtype($p_object_type)
                ->get_row();
        }

        return $this;
    }

    /**
     * Method which returns the defined "ORDER BY" segment.
     *
     * @param  string $property
     * @param  string $direction
     *
     * @return string
     * @author Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_table_sorting($property = null, $direction = null)
    {
        if ($property !== null && $direction !== null) {
            return ' ORDER BY ' . $this->m_db->escapeColumnName($property) . ' ' . ($direction == 'ASC' ? 'ASC' : 'DESC');
        }

        if ($this->get_table_config() !== false) {
            $l_sorting_prop = $this->get_table_config()
                ->getSortingProperty();
            $l_sorting_direction = $this->get_table_config()
                ->getSortingDirection();

            if ($l_sorting_prop && $l_sorting_direction) {
                return ' ORDER BY ' . $this->m_db->escapeColumnName($l_sorting_prop) . ' ' . strtoupper($l_sorting_direction);
            }
        }

        return ' ORDER BY obj_main.isys_obj__title ASC';
    }

    /**
     * Count objects of a specific type in several statuses
     *
     * @return  array
     */
    public function get_rec_counts()
    {
        // Build SQL-Statement
        $l_sql = 'SELECT  SUM(isys_obj__status = ' . C__RECORD_STATUS__NORMAL . ') AS COUNT_NORMAL,
                          SUM(isys_obj__status = ' . C__RECORD_STATUS__ARCHIVED . ') AS COUNT_ARCHIVED,
                          SUM(isys_obj__status = ' . C__RECORD_STATUS__DELETED . ') AS COUNT_DELETED ';

        // Add status C__TEMPLATE__STATUS if defined
        if (defined("C__TEMPLATE__STATUS") && C__TEMPLATE__STATUS == 1) {
            $l_sql .= ', SUM(isys_obj__status = ' . C__RECORD_STATUS__TEMPLATE . ') AS COUNT_TEMPLATE ';
        }

        $l_sql .= 'FROM `isys_obj` WHERE `isys_obj__isys_obj_type__id` = ' . $this->convert_sql_id($this->m_object_type['isys_obj_type__id']) . ' ' .
            $this->prepare_status_filter() . ' ' . isys_auth_cmdb_objects::instance()->get_allowed_objects_condition(isys_auth::VIEW);

        // Retrieve results
        $l_row = $this->retrieve($l_sql)
            ->get_row();

        // Build array
        $l_array = [
            C__RECORD_STATUS__NORMAL   => ($l_row['COUNT_NORMAL'] > 0) ? $l_row['COUNT_NORMAL'] : 0,
            C__RECORD_STATUS__ARCHIVED => ($l_row['COUNT_ARCHIVED'] > 0) ? $l_row['COUNT_ARCHIVED'] : 0,
            C__RECORD_STATUS__DELETED  => ($l_row['COUNT_DELETED'] > 0) ? $l_row['COUNT_DELETED'] : 0,
        ];

        // Add C__STATUS__TEMPLATE
        if (isset($l_row['COUNT_TEMPLATE'])) {
            $l_array[C__RECORD_STATUS__TEMPLATE] = $l_row['COUNT_TEMPLATE'];
        }

        return $l_array;
    }

    /**
     * @param bool $operation
     *
     * @return isys_cmdb_dao_list_objects
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;

        return $this;
    }

    /**
     * Method for finding a user defined list config.
     *
     * @return  mixed  Might be an array, if the user has defined an own list. If not: null.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function load_user_config()
    {
        if ($this->m_list_row === null) {
            // SQL for finding out, if a user has created an own list configuration.
            $l_sql = "SELECT *
                FROM isys_obj_type_list
                WHERE isys_obj_type_list__isys_obj__id = " . $this->convert_sql_id($this->m_user) . "
                AND isys_obj_type_list__isys_obj_type__id = " . $this->convert_sql_id($this->m_object_type['isys_obj_type__id']) . ";";

            $l_res = $this->retrieve($l_sql);

            if (is_countable($l_res) && count($l_res)) {
                $this->m_list_row = $l_res->get_row();
            }
        }

        return $this->m_list_row;
    }

    /**
     * Constructor method to retrieve and save the current user ID.
     *
     * @param   isys_component_database $p_db
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function __construct($p_db)
    {
        // Set memory limit.
        if (($l_memlimit = isys_tenantsettings::get('system.memory-limit.object-lists', '768M'))) {
            ini_set('memory_limit', $l_memlimit);
        }

        $this->m_user = (int)isys_component_session::instance()
            ->get_user_id();

        parent::__construct($p_db);
    }

    /**
     * @param  string $p_property
     * @param  string $p_direction
     *
     * @return $this
     */
    public function set_order_by($p_property, $p_direction)
    {
        $this->order_by_property = $p_property;
        $this->order_by_direction = $p_direction;

        return $this;
    }

    /**
     * @param $p_do_id
     *
     * @return $this
     */
    public function set_defaults($p_do_id)
    {
        $this->use_defaults = !!$p_do_id;

        return $this;
    }
}
