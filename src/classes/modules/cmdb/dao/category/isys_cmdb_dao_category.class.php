<?php

use idoit\Context\Context;

/**
 * i-doit
 *
 * DAO: CMDB Category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_cmdb_dao_category extends isys_cmdb_dao
{
    const C__LOAD = 1;
    const C__SAVE = 2;

    /**
     * Will be used to limit the display of how many other objects contain the same data (in context of validation).
     */
    const UNIQUE_VALIDATION_OBJECT_COUNT = 10;

    /**
     * Category type for view only categories; used in isysgui_catx__type
     *
     * @var integer
     */
    const TYPE_VIEW = 1;

    /**
     * Category type for regular view and edit categories; used in isysgui_catx__type
     *
     * @var integer
     */
    const TYPE_EDIT = 2;

    /**
     * Category type for rear categories; used in isysgui_catx__type
     *
     * @var integer
     */
    const TYPE_REAR = 3;

    /**
     * Category type for assignment categories (Object browser on "New"); used in isysgui_catx__type
     *
     * @var integer
     */
    const TYPE_ASSIGN = 4;

    /**
     * Category type for folders; used in isysgui_catx__type
     *
     * @var integer
     */
    const TYPE_FOLDER = 10;

    /**
     * @var array
     */
    protected $m_additional_tom_rules;

    /**
     * @var array
     */
    protected $m_arrLogbookEntries = [];

    /**
     * @var boolean
     */
    protected $m_bCasesensitiv;

    /**
     * @var boolean
     */
    protected $m_bWordsonly;

    /**
     * Cached properties
     *
     * @var array
     */
    protected $m_cached_properties = [];

    /**
     * Category type.
     *
     * @var  integer
     */
    protected $m_cat_type = null;

    /**
     * Category identifier
     *
     * @var string
     */
    protected $m_category = null;

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = null;

    /**
     * Category's constant.
     *
     * @var  string
     */
    protected $m_category_const;

    /**
     * Category's identifier.
     *
     * @var  integer
     */
    protected $m_category_id;

    /**
     * Category type's abbrevation.
     *
     * @var  string
     */
    protected $m_category_type_abbr = '';

    /**
     * Category type's constant.
     *
     * @var  string
     */
    protected $m_category_type_const = '';

    /**
     * Field which holds the connected object id field if defined
     *
     * @var string
     */
    protected $m_connected_object_id_field = null;

    /**
     * DAO result with category data.
     *
     * @var  isys_component_dao_result
     */
    protected $m_daores;

    /**
     * Category's data - this NEEDS to be "unset" by default, because there are some checks later on...
     *
     * @var  array
     */
    protected $m_data;

    /**
     * Name of property which should be used as identifier
     *
     * @var string
     */
    protected $m_entry_identifier = 'title';

    /**
     * Should we generically handle a relation creation via property C__PROPERTY__DATA__RELATION_TYPE.
     *
     * @var  boolean
     */
    protected $m_has_relation = false;

    /**
     * Field for singlevalue categories which determines if the entry is purgable or not
     *
     * @var  boolean
     */
    protected $m_is_purgable = false;

    /**
     * Category's list DAO.
     *
     * @var  string
     */
    protected $m_list;

    /**
     * @var integer
     */
    protected $m_list_id;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean  Defaults to false.
     */
    protected $m_multivalued = false;

    /**
     * Defines if the category only consists of an object browser
     *
     * @var  boolean
     */
    protected $m_object_browser_category = false;

    /**
     * Property of the object browser
     *
     * @var string
     */
    protected $m_object_browser_property = '';

    /**
     * @var integer
     */
    protected $m_object_id;

    /**
     * Field for the object id. This variable is needed for multiedit (for example global category guest systems or it service).
     *
     * @var  string
     */
    protected $m_object_id_field = 'isys_obj__id';

    /**
     * @var integer
     */
    protected $m_object_type_id;

    /**
     * Prepared properties (used for getting properties quickly).
     *
     * @var  array
     */
    protected $m_prepared_properties;

    /**
     * @var boolean
     */
    protected $m_process_validated;

    /**
     * Information about manipulating, filtering, importing, exporting, and transforming data.
     *
     * @var  array
     */
    protected $m_properties = [];

    /**
     * New variable to determine if the current category is a reverse category of another one.
     *
     * @var  string
     */
    protected $m_reverse_category_of = null;

    /**
     * @var string
     */
    protected $m_source_table;

    /**
     * @deprecated ...?
     * @var string
     */
    protected $m_strLogbookDesc;

    /**
     * @var string
     */
    protected $m_strLogbookSQL;

    /**
     * @var array
     */
    protected $m_sync_catg_data;

    /**
     * Main table where properties are stored persistently.
     *
     * @var  string
     */
    protected $m_table;

    /**
     * Category's template.
     *
     * @var  string
     */
    protected $m_tpl;

    /**
     * Category's user interface.
     *
     * @var  string
     */
    protected $m_ui;

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

        if (!$this->is_multivalued()) {
            $l_sql = 'SELECT ' . $p_table . '__id FROM ' . $p_table . ' WHERE ' . $p_table . '__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';';
            if ($l_id = $this->retrieve($l_sql)
                ->get_row_value($p_table . '__id')) {
                return $l_id;
            }
        }

        $l_sql = 'INSERT IGNORE INTO ' . $p_table . ' SET ' . $p_table . '__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';';

        if ($this->update($l_sql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        }

        return null;
    }

    /**
     * Method for returning an object as "Objectype > Object" with quicklink.
     *
     * @static
     *
     * @param   integer $l_obj_id
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function dynamic_property_callback_object($l_obj_id)
    {
        if ($l_obj_id > 0) {
            global $g_comp_database;

            $l_quick_info = new isys_ajax_handler_quick_info();

            $l_row = isys_cmdb_dao::instance($g_comp_database)
                ->get_object_by_id($l_obj_id)
                ->get_row();

            return $l_quick_info->get_quick_info($l_row['isys_obj__id'], isys_application::instance()->container->get('language')
                    ->get($l_row['isys_obj_type__title']) . ' &raquo; ' . $l_row['isys_obj__title'], C__LINK__OBJECT);
        }

        return '';
    }

    /**
     * Build a category based on the specified parameters.
     *
     * @param   isys_cmdb_dao $p_dao_cmdb
     * @param   integer       $p_obj_id
     * @param   integer       $p_cat_type
     * @param   integer       $p_cat_const
     * @param   array         $p_isysgui
     * @param   string        $p_str_cat_type
     * @param   integer       $p_cat_list_id
     *
     * @throws  isys_exception_cmdb
     * @return  isys_cmdb_dao_category&
     */
    public static function &manufacture(isys_cmdb_dao $p_dao_cmdb, $p_obj_id, $p_cat_type, $p_cat_const, $p_isysgui, $p_str_cat_type, $p_cat_list_id = null)
    {
        if ($p_dao_cmdb->obj_exists($p_obj_id)) {
            $l_cat_srctable = $p_isysgui["isysgui_cat{$p_str_cat_type}__source_table"];
            $l_cat_class = $p_isysgui["isysgui_cat{$p_str_cat_type}__class_name"];

            $l_q = "";

            if (class_exists($l_cat_class)) {
                $l_cat_db = $p_dao_cmdb->get_database_component();
                /**
                 * @var $l_cat_obj isys_cmdb_dao_category
                 */
                $l_cat_obj = new $l_cat_class($l_cat_db);

                // Set some parameters.
                $l_cat_obj->set_object_id($p_obj_id);
                $l_cat_obj->set_list_id($p_cat_list_id);
                $l_cat_obj->set_source_table($l_cat_srctable);
                $l_cat_obj->set_category_type($p_cat_type);

                if (is_numeric($p_cat_const)) {
                    $l_cat_obj->set_category_id($p_cat_const);
                }
            } else {
                return null;
            }

            /*
             * Wenn $p_cat_list_id NULL ist wird bei Neuanlage von Kategorieeintrügen immer der erste Datensatz aus get_data angezeigt, anstatt eine leere Kategorie.
             * Daher diese Kondition:
             */
            if (is_null($p_cat_list_id) && $l_cat_obj->is_multivalued()) {
                $p_cat_list_id = 'FALSE';
            }

            // Get category data.
            $l_dao_res = $l_cat_obj->get_data($p_cat_list_id, $p_obj_id);

            if ($l_dao_res == null) {
                throw new isys_exception_cmdb("Could not retrieve full distributor object record ($l_q)", C__CMDB__ERROR__DISTRIBUTOR);
            }

            if ($l_cat_obj != null) {
                if ($l_cat_obj->init($l_dao_res) == true) {
                    return $l_cat_obj;
                } else {
                    throw new isys_exception_cmdb("Could not initialize: '" . get_class($l_cat_obj) . "'", C__CMDB__ERROR__CATEGORY_BUILDER);
                }
            } else {
                throw new isys_exception_cmdb("Cannot instantiate category-dao: '{$l_cat_class}'.", C__CMDB__ERROR__CATEGORY_BUILDER);
            }
        }
    }

    /**
     * Sets the object browser property
     *
     * @param $p_value
     *
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_object_browser_property($p_value)
    {
        $this->m_object_browser_property = $p_value;
    }

    /**
     * Gets the object browser property
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_object_browser_property()
    {
        return $this->m_object_browser_property;
    }

    /**
     * Get entry identifier
     *
     * @author  Selcuk Kekec <skekec@i-doit.com>
     *
     * @param   array $p_entry_data
     *
     * @return  string
     */
    public function get_entry_identifier($p_entry_data)
    {
        try {
            return $this->get_gui_value_for_property($this->m_entry_identifier, is_array($p_entry_data) ? $p_entry_data : []);
        } catch (isys_exception_cmdb $e) {
            return '';
        }
    }

    /**
     * Returns a GUI representation for the current property. No matter if this is a reference, dialog, chosen, or whatever.
     *
     * @param string $p_property
     * @param array  $p_row_data
     *
     * @return string
     */
    public function get_gui_value_for_property($p_property, array $p_row_data)
    {
        $l_return = '';
        $l_db_field = null;

        $l_property = $this->property($p_property);

        if (is_countable($l_property) && count($l_property) === 0) {
            $l_property = $this->dynamic_property($p_property);
        }

        if (is_countable($l_property) && count($l_property) > 0) {
            if (!isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]) && !isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) {
                return '';
            } else {
                if (!isset($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK])) {
                    // No helper class.
                    if ($this->get_category_type() != C__CMDB__CATEGORY__TYPE_CUSTOM) {
                        if (isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) {
                            $l_db_field = $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
                        } else {
                            if (isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD])) {
                                $l_db_field = $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                            }
                        }
                    } else {
                        if ($p_property === 'description') {
                            $l_db_field = 'commentary_' . $l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID];
                        } else {
                            $l_db_field = $p_property;
                        }
                    }

                    if ($l_db_field && isset($p_row_data[$l_db_field])) {
                        // set return value
                        $l_return = $p_row_data[$l_db_field];
                    }
                } else {
                    if (isset($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK]) && isset($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0])) {
                        // Check if helper class exists.
                        if (class_exists($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0])) {
                            // Create new instance of the helper class:
                            $l_helper = new $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0](
                                $p_row_data,
                                $this->m_db,
                                $l_property[C__PROPERTY__DATA],
                                $l_property[C__PROPERTY__FORMAT],
                                $l_property[C__PROPERTY__UI]
                            );

                            if (isset($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT]) && !empty($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT])) {
                                $l_unit_properties = $this->property($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT]);

                                if (method_exists($l_helper, 'set_unit_const')) {
                                    if (isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) {
                                        $l_const = $p_row_data[$l_unit_properties[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]];
                                    } else {
                                        $l_const = $p_row_data[$l_unit_properties[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__const'];
                                    }

                                    $l_helper->set_unit_const($l_const);
                                }
                            }

                            // Call the helper's method:
                            if (isset($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1])) {
                                if (method_exists($l_helper, $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1])) {
                                    if (isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]) &&
                                        array_key_exists($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS], $p_row_data)) {
                                        $l_data = $p_row_data[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]];
                                    } else {
                                        $l_data = $p_row_data[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
                                    }

                                    $l_return = '';
                                    if ($l_data) {
                                        $l_return = call_user_func([
                                            $l_helper,
                                            $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1]
                                        ], $l_data);
                                        // Check result before using it as array
                                        if ($l_return instanceof isys_export_data) {
                                            $l_return = $l_return->get_data();
                                        }
                                        if (!is_scalar($l_return)) {
                                            if (isset($l_return['title'])) {
                                                $l_return = $l_return['title'];
                                            } elseif (isset($l_return['title_lang'])) {
                                                $l_return = $l_return['title_lang'];
                                            } elseif (isset($l_return['value'])) {
                                                $l_return = $l_return['value'];
                                            } else {
                                                $l_return = '';
                                            }
                                        }
                                    }
                                } else {
                                    throw new isys_exception_cmdb(sprintf(
                                        'Method %s in helper class %s does not exist.',
                                        $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1],
                                        $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]
                                    ));
                                }
                            }

                            unset($l_helper);
                        }
                    }
                }
            }
        }

        return $l_return;
    }

    /**
     * Sets flag $m_object_browser_category.
     *
     * @param  $p_value
     */
    public function set_object_browser_category($p_value)
    {
        $this->m_object_browser_category = $p_value;
    }

    /**
     * Gets member variable $m_object_browser_category.
     *
     * @return  boolean
     */
    public function get_object_browser_category()
    {
        return $this->m_object_browser_category;
    }

    /**
     * Is it possible to purge the entry for the current single value category?
     *
     * @return  boolean
     * @author  Van Quyen Hoang
     */
    public function category_entries_purgable()
    {
        return (bool)$this->m_is_purgable;
    }

    /**
     * Set the possibilty to purge the entry
     *
     * @param $p_purgable
     *
     * @return $this
     */
    public function set_category_entries_purgable($p_purgable)
    {
        $this->m_is_purgable = (bool)$p_purgable;

        return $this;
    }

    /**
     * Abstract method for retrieving the properties of every category dao
     *
     * @return  array
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     */
    /*abstract*/
    /**
     * Sets conditionless query string into cache for generic function get_data.
     *
     * @param   $p_query
     *
     * @return  isys_cmdb_dao_category
     */
    public function set_conditionless_query($p_query)
    {
        isys_caching::factory('getdataconditionless')
            ->set($this->get_category_const(), addslashes($p_query));

        return $this;
    }

    /**
     * Gets conditionless query string from cache.
     *
     * @return  string
     */
    public function get_conditionless_query()
    {
        return stripslashes(isys_caching::factory('getdataconditionless')
            ->get($this->get_category_const()));
    }

    /**
     * Retrieves a single property by it's UI ID.
     *
     * @param   string  $p_const
     * @param   integer $p_get_with
     *
     * @return  mixed
     */
    public function get_property_by_ui_id($p_const, $p_get_with = null)
    {
        foreach ($this->get_properties($p_get_with) as $l_key => $l_property) {
            if ($p_const == $l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]) {
                return [$l_key => $l_property];
            }
        }

        return false;
    }

    /**
     * Gets name of category's list DAO.
     *
     * @return  string
     */
    public function get_category_list()
    {
        return $this->m_list;
    }

    /**
     * Gets user interface.
     *
     * @return  isys_cmdb_ui_category
     * @throws  isys_exception_ui
     */
    public function &get_ui()
    {
        global $index_includes;

        if (class_exists($this->m_ui)) {
            /**
             * @var isys_cmdb_ui_category
             */
            $l_ui = new $this->m_ui(isys_application::instance()->template);

            unset($index_includes['contentbottomcontentaddition']);
            unset($index_includes['contentbottomcontentadditionbefore']);

            return $l_ui->set_template($this->m_tpl);
        } else {
            throw new isys_exception_ui('UI for ' . get_class($this) . ' does not exist.');
        }
    }

    /**
     * Is category multi-valued?
     *
     * @return  boolean
     */
    public function is_multivalued()
    {
        return (bool)$this->m_multivalued;
    }

    /**
     * Returns the object id field.
     *
     * @return  string
     */
    public function get_object_id_field()
    {
        return $this->m_object_id_field;
    }

    /**
     * Returns the object id field.
     *
     * @return  string
     */
    public function get_connected_object_id_field()
    {
        return $this->m_connected_object_id_field;
    }

    /**
     * Gets potential filter rows.
     *
     * @return array
     * @todo Deprecated code inside
     */
    public function get_filter()
    {
        $l_info = $this->get_properties();

        if (!is_countable($l_info) || count($l_info) == 0) {
            return [];
        }

        $l_data = [];

        // Iterate through properties:
        foreach ($l_info as $l_key => $l_property) {
            // Skip properties that shouldn't be included:
            if (isset($l_property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH]) && $l_property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH] === false) {
                continue;
            }

            $l_field = $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
            // Field alias are not used for custom categories
            $l_field_alias = ($this->m_cat_type === C__CMDB__CATEGORY__TYPE_CUSTOM) ? null : $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
            $l_references = $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES];

            if (!empty($l_field_alias)) {
                $l_ref = $l_field_alias;
            } else {
                if (!empty($l_references) && is_array($l_references) && (!is_int(strpos($l_references[0], '_2_')) && $l_references[0] != 'isys_connection')) {
                    $l_ref = $l_references[0] . '__title';
                } else {
                    $l_ref = $l_field;
                }
            }

            $l_data[$l_ref . '::' . $l_key] = $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE];
        }

        // @todo deprecated:
//        if (isset($this->m_filter) && is_array($this->m_filter) && count($this->m_filter) > 0) {
//            return $this->m_filter;
//        } // if
        return $l_data;
    }

    /**
     * Method for setting the source table.
     *
     * @param   string $p_source_table
     *
     * @return  isys_cmdb_dao_category
     */
    public function set_source_table($p_source_table)
    {
        $this->m_source_table = $p_source_table;

        return $this;
    }

    /**
     * Set current list id (category entry id).
     *
     * @param   integer $p_list_id
     *
     * @return  isys_cmdb_dao_category
     */
    public function set_list_id($p_list_id)
    {
        $this->m_list_id = (int)$p_list_id;

        return $this;
    }

    /**
     * @param $p_list
     *
     * @return $this
     */
    public function set_list($p_list)
    {
        $this->m_list = $p_list;

        return $this;
    }

    /**
     * Returns the current category entry id.
     *
     * @return  integer
     */
    public function get_list_id()
    {
        return (int)$this->m_list_id;
    }

    /**
     * Set current object id.
     *
     * @param   integer $p_object_id
     *
     * @return  isys_cmdb_dao_category
     */
    public function set_object_id($p_object_id)
    {
        $this->m_object_id = (int)$p_object_id;

        return $this;
    }

    /**
     * Set the current object type ID.
     *
     * @param   integer $p_object_type_id
     *
     * @return  isys_cmdb_dao_category
     */
    public function set_object_type_id($p_object_type_id)
    {
        $this->m_object_type_id = (int)$p_object_type_id;

        return $this;
    }

    /**
     * Set the category type.
     *
     * @param   integer $p_setValue
     *
     * @return  isys_cmdb_dao_category
     */
    public function set_category_type($p_setValue)
    {
        $this->m_cat_type = (int)$p_setValue;

        return $this;
    }

    /**
     * Gets category's type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return (int)$this->m_cat_type;
    }

    /**
     * Gets category's name.
     *
     * @return  string
     */
    public function get_category()
    {
        return $this->m_category;
    }

    /**
     * Gets category's constant as string.
     *
     * @return  string
     */
    public function get_category_const()
    {
        return $this->m_category_const;
    }

    /**
     * Setter method for m_category_const
     *
     * @param $categoryConst
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_catgory_const($categoryConst)
    {
        $this->m_category_const = $categoryConst;
    }

    /**
     * Gets category's type as constant.
     *
     * @return  string
     */
    public function get_category_type_const()
    {
        return $this->m_category_type_const;
    }

    /**
     * Gets abbreviated category's type.
     *
     * @return  string
     */
    public function get_category_type_abbr()
    {
        return $this->m_category_type_abbr;
    }

    /**
     * Base initialization of category-DAO
     *
     * @param   isys_component_dao_result $p_daores
     *
     * @return  boolean
     * @throws  isys_exception_dao_cmdb
     */
    public function init(isys_component_dao_result &$p_daores)
    {
        if (is_object($p_daores)) {
            $this->m_daores = $p_daores;

            return true;
        }

        throw new isys_exception_dao_cmdb("Initialization of category failed, expecting object of type isys_component_dao_result.\n", self::class, 0);
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed    Returns category data identifier (int) on success, true (bool) if nothing has to be done, otherwise false.
     * @throws  isys_exception_validation
     */
    public function sync($p_category_data, $p_object_id, $p_status)
    {
        // There is nothing to import
        if (!is_countable($p_category_data[isys_import_handler_cmdb::C__PROPERTIES]) || count($p_category_data[isys_import_handler_cmdb::C__PROPERTIES]) == 0) {
            return true;
        }

        $properties = $this->get_properties();
        // There is nothing to to:
        if (!is_countable($properties) || count($properties) === 0) {
            return true;
        }

        // Assign object identifier and default record status:
        $l_data = [
            'isys_obj__id' => $p_object_id,
            'status'       => C__RECORD_STATUS__NORMAL
        ];

        // Build data array which can be handled by save() and create():
        foreach ($p_category_data['properties'] as $l_key => $l_value) {
            $l_data[$l_key] = $l_value[C__DATA__VALUE];
        }

        $l_validation = $this->validate($l_data);

        if (is_array($l_validation) &&
            Context::instance()->getContextTechnical() === Context::CONTEXT_IMPORT_XML &&
            isys_tenantsettings::get('import.validation.empty-attribute-on-error', 0)
        ) {
            foreach ($l_validation as $propertyKey => $unused) {
                $l_data[$propertyKey] = null;
            }
            $l_validation = true;
        }

        if ($l_validation !== true) {
            throw new isys_exception_validation(isys_application::instance()->container->get('language')
                ->get('LC__VALIDATION_ERROR'), $l_validation, $p_category_data['data_id']);
        }

        $l_multivalued = $this->is_multivalued();

        if (!$l_multivalued) {
            if ($this->get_data_by_object($p_object_id)
                    ->count() === 0) {
                $p_status = isys_import_handler_cmdb::C__CREATE;
            }
        }

        if ($p_status == isys_import_handler_cmdb::C__CREATE) {
            return $this->create_data($l_data);
        }

        if ($p_status == isys_import_handler_cmdb::C__UPDATE && $p_category_data['data_id'] > 0) {
            if ($l_multivalued) {
                if ($this->save_data($p_category_data['data_id'], $l_data)) {
                    return $p_category_data['data_id'];
                }

                return false;
            }

            if ($this->save_single_value($l_data['isys_obj__id'], $l_data, true)) {
                return $p_category_data['data_id'];
            }

            return false;
        }

        return true;
    }

    /**
     * Sets or RE-sets the internal dao-result.
     * Only use this if you're really know what you're doing!
     *
     * @param   isys_component_dao_result $p_daores
     *
     * @return  isys_cmdb_dao_category
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     */
    public function set_dao_result(isys_component_dao_result &$p_daores)
    {
        $this->m_daores = $p_daores;

        return $this;
    }

    /**
     * Prepare sort statement
     *
     * @param string $p_sort_by
     * @param string $p_direction
     *
     * @return string
     */
    public function sort($p_sort_by, $p_direction)
    {
        $l_sql = '';

        if (is_string($p_sort_by) && !empty($p_sort_by)) {
            $l_sql .= ' ORDER BY ' . $p_sort_by;

            switch ($p_direction) {
                case 'DESC':
                    $l_sql .= ' DESC';
                    break;
                default:
                case 'ASC':
                    $l_sql .= ' ASC';
                    break;
            }
        }

        return $l_sql;
    }

    /**
     * Returns the object id by its corresponding category id (catlevel).
     *    Attention: If $p_source_table is null, $this->m_source_table is used. This does only work if this category dao was instantiated by isys_cmdb_dao_distributor!!
     *
     * @author Dennis Stücken 10-2010
     *
     * @param int $p_id
     * @param int $p_source_table
     *
     * @uses   $this->m_source_table
     * @return int
     */
    public function get_object_id_by_category_id($p_id, $p_source_table = null)
    {
        if (is_null($p_source_table)) {
            $p_source_table = $this->m_table;
        }

        return $this->retrieve("SELECT {$p_source_table}__isys_obj__id as id FROM {$p_source_table} WHERE {$p_source_table}__id = " . $this->convert_sql_id($p_id) . ';')
            ->get_row_value('id');
    }

    /**
     * Retrieves general data. Query runs every time you run this method. And it will always return the first row from the result set. Returns null on error.
     *
     * @throws  Exception
     * @return  array
     */
    public function get_general_data()
    {
        if (is_object($this->m_daores)) {
            $l_daores = $this->retrieve($this->m_daores->get_query());

            if (is_object($l_daores)) {
                $l_daodata = $l_daores->get_row();

                if (is_array($l_daodata)) {
                    $this->m_daores = $l_daores;
                    $this->m_daores->reset_pointer();

                    return $l_daodata;
                }
            }

            return null;
        } else {
            throw new isys_exception_cmdb(get_class($this) . " :: get_general_data failed. DAO-Result empty. (In " . __FILE__ . ":" . __LINE__ . ")");
        }
    }

    /**
     * Returns the associated DAO result to this category.
     *
     * @return  isys_component_dao_result
     */
    public function get_result()
    {
        return $this->m_daores;
    }

    /**
     * Return translated category name by constant string.
     *
     * @param   string $p_catconst
     *
     * @return  string
     */
    public function get_category_by_const_as_string($p_catconst)
    {
        // @todo Change these constants in the db
        $l_arr_const_catg = [
            'C__CMDB__SUBCAT__NETWORK_INTERFACE_L',    // @todo  Remove in i-doit 1.12
            'C__CMDB__SUBCAT__NETWORK_INTERFACE_P',    // @todo  Remove in i-doit 1.12
            'C__CMDB__SUBCAT__NETWORK_PORT',           // @todo  Remove in i-doit 1.12
            'C__CMDB__SUBCAT__NETWORK_PORT_OVERVIEW',  // @todo  Remove in i-doit 1.12
            'C__CMDB__SUBCAT__STORAGE__DEVICE'         // @todo  Remove in i-doit 1.12
        ];

        $l_arr_const_cats = [
            'C__CMDB__SUBCAT__WS_NET_TYPE',                       // @todo  Remove in i-doit 1.12
            'C__CMDB__SUBCAT__WS_ASSIGNMENT',                     // @todo  Remove in i-doit 1.12
            'C__CMDB__SUBCAT__MAINTENANCE_LINKED_OBJECT_LIST',    // Does this exist?
            'C__CMDB__SUBCAT__MAINTENANCE_AGREEMENT_INFORMATION', // Does this exist?
            'C__CMDB__SUBCAT__LICENCE_OVERVIEW',                  // @todo  Remove in i-doit 1.12
            'C__CMDB__SUBCAT__LICENCE_LIST',                      // @todo  Remove in i-doit 1.12
            'C__CMDB__SUBCAT__LICENCE_GROUP',                     // Does this exist?
            'C__CMDB__SUBCAT__FILE_VERSIONS',                     // @todo  Remove in i-doit 1.12
            'C__CMDB__SUBCAT__FILE_OBJECTS',                      // @todo  Remove in i-doit 1.12
            'C__CMDB__SUBCAT__FILE_ACTUAL',                       // @todo  Remove in i-doit 1.12
            'C__CMDB__SUBCAT__EMERGENCY_PLAN_LINKED_OBJECT_LIST', // @todo  Remove in i-doit 1.12
            'C__CMDB__SUBCAT__EMERGENCY_PLAN'                     // @todo  Remove in i-doit 1.12
        ];

        // @see ID-2736
        if (!empty($p_catconst)) {
            if (strpos($p_catconst, 'C__CATG') === 0 || in_array($p_catconst, $l_arr_const_catg)) {
                $l_cat = $this->get_catg_by_const($p_catconst)
                    ->__to_array();

                return isys_application::instance()->container->get('language')
                    ->get($l_cat['isysgui_catg__title']);
            } else {
                if (strpos($p_catconst, 'C__CATS') === 0 || in_array($p_catconst, $l_arr_const_cats)) {
                    $l_cat = $this->get_cats_by_const($p_catconst)
                        ->__to_array();

                    return isys_application::instance()->container->get('language')
                        ->get($l_cat['isysgui_cats__title']);
                }
            }
        }

        return false;
    }

    /**
     * Gets the categories source-table name.
     *
     * @return  string
     */
    public function get_source_table()
    {
        return $this->m_source_table;
    }

    /**
     * Gets the categories table name.
     *
     * @return  string
     */
    public function get_table()
    {
        return $this->m_table;
    }

    /**
     * Return database field to be used as breadcrumb title
     *
     * @return string
     */
    public function get_breadcrumb_field()
    {
        return $this->get_table() . '__title';
    }

    /**
     * Returns NULL and store the _status for a records from a global category to the secound parameter by referenz OR return the (integer) ErrorCode.
     *
     * @param   integer $p_cat_level     Standard is 0
     * @param   integer &$p_intRecStatus Return the id
     *
     * @return  null
     * @todo AW: Still necessary? Only in for compatibility to old CMDB-module
     */
    public function get_rec_status($p_cat_level, &$p_intRecStatus)
    {
        $l_catdata = $this->get_general_data();
        if (($l_table_name = $this->get_source_table())) {
            if ($p_cat_level == 0) {
                if ($this->get_category_type() == C__CMDB__CATEGORY__TYPE_SPECIFIC) {
                    $p_intRecStatus = $l_catdata[$l_table_name . '__status'];
                } else {
                    $p_intRecStatus = $l_catdata[$l_table_name . '_list__status'];
                }
            }

            return null;
        }
    }

    /**
     * Set the validation.
     *
     * @param   boolean
     *
     * @return  isys_cmdb_dao_category
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function set_validation($p_bStatus)
    {
        $this->m_process_validated = $p_bStatus;

        return $this;
    }

    /**
     * Find out if the validation has been set.
     *
     * @return   boolean
     * @version  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_validation()
    {
        return $this->m_process_validated;
    }

    /**
     * Setter for additional rules for TOM. Used in methode validate_post_data to add fieldrelated information or errortext.
     *
     * @param   array $p_arrAdditionalTomRules
     *
     * @return  isys_cmdb_dao_category
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function set_additional_rules($p_arrAdditionalTomRules)
    {
        $this->m_additional_tom_rules = $p_arrAdditionalTomRules;

        return $this;
    }

    /**
     * Return the additional TOM rules.
     *
     * @return  array
     */
    public function get_additional_rules()
    {
        return $this->m_additional_tom_rules;
    }

    /**
     * Validates property data.
     *
     * @param   array $p_data                Associative array of property tags as keys and their values as values.
     * @param   mixed $p_prepend_table_field This can be used to prepend a table field alias (use boolean "true" for the default category table).
     *
     * @return  mixed  Returns true on a successful validation, otherwise an associative array with property tags as keys and error messages as values.
     * @author  Benjamin Heisig <bheisig@synetics.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function validate(array $p_data = [], $p_prepend_table_field = false)
    {
        $l_prepend = '';
        $l_result = [];
        $l_properties = $this->get_properties(C__PROPERTY__WITH__VALIDATION);

        $language = isys_application::instance()->container->get('language');

        if ($p_prepend_table_field !== false) {
            if ($p_prepend_table_field === true) {
                $l_prepend = $this->m_table . '.';
            } else {
                $l_prepend = $p_prepend_table_field . '.';
            }
        }

        if (is_array($p_data)) {
            foreach ($p_data as $l_key => $l_value) {
                // Special validation handling for inventory_no from isys_cmdb_dao_category_g_accounting
                /*
                // @see  ID-4912  Re-enabling the "inventory_no" validation.
                if ($l_key == 'inventory_no' && isys_tenantsettings::get('cmdb.objtype.' . $this->get_object_type_id() . '.auto-inventory-no') != '') {
                    continue;
                }
                */

                // If the property could not be found or Checks are not set, we don't want to waste time.
                if (!isset($l_properties[$l_key]) && !isset($l_properties[$l_key][C__PROPERTY__CHECK])) {
                    continue;
                }
                // don't validate virtual properties
                if (isset($l_properties[$l_key][C__PROPERTY__PROVIDES], $l_properties[$l_key][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VIRTUAL]) &&
                    $l_properties[$l_key][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VIRTUAL]) {
                    continue;
                }

                if (is_array($l_value) && isset($l_value[C__DATA__VALUE])) {
                    $l_value = $l_value[C__DATA__VALUE];
                }

                // Mandatory field is empty.
                if ($l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY]) {
                    // Check, if we got an empty string.
                    if (trim($l_value . '') === '') {
                        $l_result[$l_key] = $language->get('LC__UNIVERSAL__MANDATORY_FIELD_IS_EMPTY');
                        continue;
                    }

                    // Now to check for Dialog fields.
                    if ($l_value == -1 && $l_properties[$l_key][C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DIALOG) {
                        $l_result[$l_key] = $language->get('LC__UNIVERSAL__MANDATORY_FIELD_IS_EMPTY');
                        continue;
                    }

                    // Now to check for Dialog+ and Object-Browser fields.
                    if (($l_value == -1 || $l_value == 'NULL' || $l_value == '0' || (is_array($l_value) && count($l_value) === 0)) &&
                        $l_properties[$l_key][C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__POPUP) {
                        $l_result[$l_key] = $language->get('LC__UNIVERSAL__MANDATORY_FIELD_IS_EMPTY');
                        continue;
                    }
                }

                // Value is empty, but it's not a mandatory field.
                if (trim($l_value . '') === '') {
                    continue;
                } else {
                    $l_res = false;
                    $l_id = null;
                    $l_field = $l_properties[$l_key][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] ?: $l_properties[$l_key][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];

                    // Special treatment for custom categores - see ID-1871.
                    if (get_class($this) == 'isys_cmdb_dao_category_g_custom_fields') {
                        $l_field = 'isys_catg_custom_fields_list__field_content';
                        $l_id = 0;
                    }

                    try {
                        $l_message = 'LC__SETTINGS__CMDB__VALIDATION_MESSAGE__UNIQUE_GLOBAL';

                        if (!isset($l_properties[$l_key][C__PROPERTY__CHECK])) {
                            continue;
                        }

                        if ($l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_GLOBAL]) {
                            // Check Unique in Global context
                            $l_message = 'LC__SETTINGS__CMDB__VALIDATION_MESSAGE__UNIQUE_GLOBAL';
                            $l_res = $this->get_data(
                                $l_id,
                                null,
                                'AND BINARY ' . $l_prepend . $l_field . ' = ' . $this->convert_sql_text($l_value),
                                null,
                                C__RECORD_STATUS__NORMAL
                            );
                        } elseif (isset($l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJTYPE]) && $l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJTYPE] && $this->m_object_type_id > 0) {
                            // Check Unique in Object type context
                            $l_message = 'LC__SETTINGS__CMDB__VALIDATION_MESSAGE__UNIQUE_OBJTYPE';
                            $l_res = $this->get_data($l_id, null, 'AND isys_obj__isys_obj_type__id = ' . $this->convert_sql_id($this->m_object_type_id) . '
                                AND BINARY ' . $l_prepend . $l_field . ' = ' . $this->convert_sql_text($l_value), null, C__RECORD_STATUS__NORMAL);
                        } elseif (isset($l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJ]) && $l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJ] && $this->m_object_id > 0) {
                            // Check for unique field in Object context.
                            $l_message = 'LC__SETTINGS__CMDB__VALIDATION_MESSAGE__UNIQUE_OBJ';
                            $l_res = $this->get_data(
                                $l_id,
                                $this->m_object_id,
                                'AND BINARY ' . $l_prepend . $l_field . ' = ' . $this->convert_sql_text($l_value),
                                null,
                                C__RECORD_STATUS__NORMAL
                            );
                        }

                        if ($l_res !== false && is_countable($l_res) && count($l_res) > 0) {
                            $l_objects = [];

                            while ($l_row = $l_res->get_row()) {
                                if (isset($l_row['isys_obj__status']) && $l_row['isys_obj__status'] != C__RECORD_STATUS__NORMAL) {
                                    continue;
                                }

                                if ($l_row['isys_obj__id'] != $this->m_object_id || $l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJ]) {
                                    $l_objects[] = '<span>' . $language->get($l_row['isys_obj_type__title']) . ' » ' . $l_row['isys_obj__title'] . '</span>';
                                }

                                // This is necessary to not count the current table entry.
                                if (isset($l_row[$this->get_table() . '__id']) && $l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJ]) {
                                    // We simply remove the last inserted item.
                                    if ($this->get_list_id() > 0 && $l_row[$this->get_table() . '__id'] == $this->get_list_id()) {
                                        array_pop($l_objects);
                                    }
                                }
                            }

                            // Remove duplicates
                            $l_objects = array_unique($l_objects);

                            if ($l_object_count = count($l_objects)) {
                                if ($l_object_count > self::UNIQUE_VALIDATION_OBJECT_COUNT) {
                                    $l_objects = array_slice($l_objects, 0, self::UNIQUE_VALIDATION_OBJECT_COUNT);
                                    $l_objects[] = $language->get('LC__SETTINGS__CMDB__VALIDATION_MESSAGE__UNIQUE_AND_MORE', ($l_object_count - self::UNIQUE_VALIDATION_OBJECT_COUNT));
                                }

                                $l_result[$l_key] = $language->get($l_message) . '<ul class="m0 mt10 list-style-none"><li>' . implode('</li><li>', $l_objects) . '</li></ul>';

                                continue;
                            }
                        }
                    } catch (isys_exception_database $e) {
                        $e->write_log();

                        isys_notify::warning($language->get('LC__SETTINGS__CMDB__VALIDATION_MESSAGE__FIELD_NOT_FOUND_IN_TABLE', [
                                    $this->m_table,
                                    $l_field
                                ]), ['sticky' => true]);
                    }
                }

                // Validate.
                if (is_array($l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION])) {
                    if (isset($l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][0])) {
                        if ($l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][0] > 0) {
                            $l_filter = $l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][0];
                        } else {
                            if (defined($l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][0])) {
                                $l_filter = constant($l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][0]);
                            } else {
                                if ($l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][0] == 'VALIDATE_BY_TEXTFIELD') {
                                    // This case requires special treatment, because "filter_var" can not handle it!
                                    $l_strings = explode("\n", $l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][1]['value']);

                                    if (!in_array($l_value, $l_strings)) {
                                        $l_result[$l_key] = $language->get('LC__SETTINGS__CMDB__VALIDATION__BY_TEXTFIELD_ERROR');
                                    }

                                    continue;
                                }
                            }
                        }

                        if (isset($l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][1])) {
                            $l_options = $l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][1];
                        } else {
                            $l_options = null;
                        }

                        // Check, if the regular expression has delimiter.
                        if (isset($l_options['options']['regexp']) && substr($l_options['options']['regexp'], 0, 1) != substr($l_options['options']['regexp'], -1, 1)) {
                            $l_options['options']['regexp'] = '~' . $l_options['options']['regexp'] . '~';
                        }

                        if (isset($l_filter)) {
                            if ($l_filter == FILTER_VALIDATE_FLOAT) {
                                // ID-2717 If we want to validate floats, always replace the comma with a dot. This will also happen before saving.
                                $l_value = str_replace(',', '.', $l_value);
                            }

                            if (filter_var($l_value, $l_filter, $l_options) === false) {
                                switch ($l_filter) {
                                    case FILTER_VALIDATE_INT:
                                        $l_message = 'LC__SETTINGS__CMDB__VALIDATION_MESSAGE__NEEDS_TO_BE_INTEGER';
                                        break;

                                    case FILTER_VALIDATE_FLOAT:
                                        $l_message = 'LC__SETTINGS__CMDB__VALIDATION_MESSAGE__NEEDS_TO_BE_FLOAT';
                                        break;

                                    case FILTER_VALIDATE_REGEXP:
                                        $l_message = $language->get('LC__SETTINGS__CMDB__VALIDATION_MESSAGE__NEEDS_TO_BE_REGEX', $l_options['options']['regexp']);
                                        break;

                                    case FILTER_VALIDATE_EMAIL:
                                        $l_message = 'LC__SETTINGS__CMDB__VALIDATION_MESSAGE__NEEDS_TO_BE_EMAIL';
                                        break;

                                    case FILTER_VALIDATE_URL:
                                        $l_message = 'LC__SETTINGS__CMDB__VALIDATION_MESSAGE__NEEDS_TO_BE_URL';
                                        break;

                                    default:
                                        $l_message = 'LC__UNIVERSAL__FIELD_VALUE_IS_INVALID';
                                        break;
                                }

                                $l_result[$l_key] = $language->get($l_message);
                            }
                        }
                    }
                }
            }
        }

        if (is_countable($l_result) && count($l_result) === 0) {
            $l_result = true;
        }

        return $l_result;
    }

    /**
     * Validates user data and calls the template system on error.
     *
     * @return  boolean  Result of validation
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    public function validate_user_data()
    {
        $l_result = true;
        $l_rules = [];

        // Get property information.
        $l_properties = $this->get_properties();

        // Get user data.
        $l_data = $this->parse_user_data();

        // Validate properties.
        $l_validation = $this->validate($l_data);

        if ($l_validation === true) {
            $l_rules = null;
        } else {
            $l_result = false;

            foreach ($l_validation as $l_property => $l_error) {
                // This may be necessary for custom categories.
                if (is_array($l_properties[$l_property][C__PROPERTY__UI][C__PROPERTY__UI__ID])) {
                    $l_formtag = 'C__CATG__CUSTOM__' . $l_properties[$l_property][C__PROPERTY__UI][C__PROPERTY__UI__ID];
                } else {
                    $l_formtag = $l_properties[$l_property][C__PROPERTY__UI][C__PROPERTY__UI__ID];
                }

                $l_rules[$l_formtag] = [
                    'message'  => $l_error,
                    'property' => $l_property,
                    'title'    => isys_application::instance()->container->get('language')
                        ->get($l_properties[$l_property][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]),
                ];
            }
        }

        $this->set_additional_rules($l_rules)
            ->set_validation($l_result);

        return $l_result;
    }

    /**
     * Builds a generic query from array by using:
     *     array key    => as the database table name
     *     array value  => as its new value
     *       id = NULL on C__DB_GENERAL__INSERT
     *
     * Array example:
     *     array(
     *         "title" => $_POST["DATA_TITLE"],
     *         "description" => $_POST["DATA_DESCRIPTION"]
     *     );
     *
     * @param   string  $p_category
     * @param   array   $p_data
     * @param   integer $p_id
     * @param   integer $p_method
     *
     * @return  string
     */
    public function build_query($p_category, $p_data, $p_id, $p_method = C__DB_GENERAL__UPDATE)
    {
        switch ($p_method) {
            default:
            case C__DB_GENERAL__UPDATE:
                $l_sql = "UPDATE " . $p_category . " SET ";
                break;
            case C__DB_GENERAL__INSERT:
                $l_sql = "INSERT INTO " . $p_category . " SET ";
                break;
            case C__DB_GENERAL__REPLACE:
                $l_sql = "REPLACE INTO " . $p_category . " SET ";
                break;
        }

        // Determine the maximum key of p_data.
        $l_assignment = [];

        // Irerate through array and start building the sql.
        foreach ($p_data as $l_key => $l_value) {
            if (!is_null($l_value)) {
                if (strtolower($l_value) == "now()") {
                    $l_value = "NOW()";
                } else {
                    // Convert $l_value, if its a string.
                    if (is_float($l_value)) {
                        $l_value = "'" . $l_value . "'";
                    } else {
                        if (is_int($l_value)) {
                            $l_value = $this->convert_sql_id($l_value);
                        } else {
                            $l_value = $this->convert_sql_text($l_value);
                        }
                    }
                }
            } else {
                $l_value = "NULL";
            }

            // If $l_value is -1, we need to convert this to NULL.
            if ($l_value == -1 || $l_value == "'-1'") {
                $l_value = "NULL";
            }

            $l_assignment[] = $p_category . "__" . $l_key . " = " . $l_value;
        }

        $l_sql .= implode(', ', $l_assignment);

        if (!is_null($p_id)) {
            $l_sql .= " WHERE " . $p_category . "__id = " . $this->convert_sql_id($p_id);
        }

        $l_sql .= ";";

        $this->m_strLogbookSQL = $l_sql;

        return $l_sql;
    }

    /**
     * Creates new entity.
     *
     * @param   array $p_data Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  mixed  Returns created entity's identifier (int) or false (bool).
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    public function create_data($p_data)
    {
        $properties = $this->get_properties();
        // There is nothing to to:
        if (!is_countable($properties) || count($properties) === 0) {
            return true;
        }

        $l_data = $this->prepare_data($p_data);

        if ($l_data === false) {
            return false;
        }

        $l_prepared_query = $this->prepare_query($l_data);

        if (empty($l_prepared_query)) {
            return false;
        }

        $l_query = 'INSERT INTO ' . $this->m_table . ' SET ' . $l_prepared_query . ';';
        if ($this->update($l_query) && $this->apply_update()) {
            $l_id = intval($this->get_last_insert_id());

            if ($this->m_has_relation) {
                $this->handle_relation_generic($l_id, $l_data);
            }

            return $l_id;
        }

        return false;
    }

    /**
     * Updates existing entity.
     *
     * @param   integer $p_category_data_id Entity's identifier
     * @param   array   $p_data             Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  boolean
     * @throws  isys_exception_dao
     * @throws  isys_exception_dao_cmdb
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    public function save_data($p_category_data_id, $p_data)
    {
        $properties = $this->get_properties();
        // There is nothing to to:
        if (!is_countable($properties) || count($properties) === 0) {
            return true;
        }

        $l_data = $this->prepare_data($p_data);

        if ($l_data === false) {
            return false;
        }

        $l_prepared_query = $this->prepare_query($l_data);

        if (empty($l_prepared_query)) {
            return false;
        }

        $l_query = "UPDATE " . $this->m_table . " SET " . $l_prepared_query . " WHERE " . $this->m_table . "__id = " . $this->convert_sql_id($p_category_data_id) . ";";

        if ($this->update($l_query) && $this->apply_update()) {
            if ($this->m_has_relation) {
                $this->handle_relation_generic($p_category_data_id, $l_data);
            }

            return true;
        }

        return false;
    }

    /**
     * Updates existing single value category by object id instead of category id or creates new single value entry if no one exists.
     *
     * @param   integer $p_object_id
     * @param   array   $p_data
     * @param   boolean $p_autocreate Specifies if a new entry should be created when category in $p_object_id is empty
     *
     * @return  boolean
     */
    public function save_single_value($p_object_id, $p_data, $p_autocreate = true)
    {
        $l_id = $this->retrieve('SELECT ' . $this->m_table . '__id as id FROM ' . $this->m_table . ' WHERE ' . $this->m_table . '__isys_obj__id = ' .
            $this->convert_sql_id($p_object_id))
            ->get_row_value('id');

        if ($l_id > 0) {
            return $this->save_data($l_id, $p_data);
        } else {
            if ($p_autocreate) {
                // Extend data with object id.
                $p_data['isys_obj__id'] = $p_object_id;

                return $this->create_data($p_data);
            }
        }

        return false;
    }

    /**
     * Updates existing entity given by user via HTTP GET and POST.
     *
     * @param   bool $p_create Create data (or update it)?
     *
     * @return  mixed Category data's identifier (int) or false (bool), otherwise null if nothing is created/saved
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    public function save_user_data($p_create)
    {
        $l_object_id = intval($_GET[C__CMDB__GET__OBJECT]);

        $properties = $this->get_properties();
        // There is nothing to to:
        if (!is_countable($properties) || count($properties) === 0) {
            return true;
        }

        // Parse user's category data:
        $l_data = $this->parse_user_data();

        // Continue if one or more properties are given:
        if (!is_countable($l_data) || count($l_data) === 0) {
            // Nothing to do...
            return null;
        }

        // Mandatory fields (may be overwritten):
        $l_data['isys_obj__id'] = $l_object_id;
        $l_data['status'] = C__RECORD_STATUS__NORMAL;

        $l_category_data_id = null;

        $l_create = false;

        if ($this->m_multivalued === true) {
            // In overview's category a new entity will be always created:
            if (isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW')) {
                $p_create = true;
            }

            if ($p_create === true) {
                $l_create = true;
            } else {
                if (isset($_GET[C__CMDB__GET__CATLEVEL]) && $_GET[C__CMDB__GET__CATLEVEL] > 0) {
                    $l_category_data_id = intval($_GET[C__CMDB__GET__CATLEVEL]);
                } else {
                    $l_category_data_id = intval($_POST[$this->m_category_const]);
                }
            }
        } else {
            $l_category_data_id = intval($_POST[$this->m_category_const]);

            // Get existing category data:
            if (!isset($this->m_data)) {
                $this->m_data = $this->get_data_by_object($l_object_id)
                    ->__to_array();
            }

            if (is_countable($this->m_data) && count($this->m_data) > 0) {
                $l_category_data_id = $this->m_data[$this->m_table . '__id'];
            } else {
                $l_create = true;
            }
        } // if multi-valued

        // Create or update category data?
        if ($l_create) {
            // Create new entity:
            $l_category_data_id = $this->create_data($l_data);
        } else {
            // Update existing entity:
            if ($this->save_data($l_category_data_id, $l_data) === false) {
                return false;
            }
        }

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_category_data_id;
    }

    /**
     * Parses user data.
     *
     * @return  array  Associative array of property tags as keys and their values as values.
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    public function parse_user_data()
    {
        if (!isset($_POST) || !count($_POST)) {
            return [];
        }

        // Get category's properties:
        $l_properties = $this->get_properties();

        $l_data = [];

        // Iterate through properties:
        if (is_array($l_properties)) {
            foreach ($l_properties as $l_key => $l_value) {
                // @see ID-2736
                if (empty($l_key) || empty($l_value) || !isset($l_value[C__PROPERTY__UI])) {
                    continue;
                }

                if ($this->get_category_type() == C__CMDB__CATEGORY__TYPE_CUSTOM &&
                    $l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE] != C__PROPERTY__INFO__TYPE__COMMENTARY) {
                    $l_post_key = 'C__CATG__CUSTOM__' . $l_value[C__PROPERTY__UI][C__PROPERTY__UI__ID];
                } else {
                    $l_post_key = $l_value[C__PROPERTY__UI][C__PROPERTY__UI__ID];
                }

                // Try to fetch the hidden fields if possible:
                switch ($l_value[C__PROPERTY__UI][C__PROPERTY__UI__TYPE]) {
                    case C__PROPERTY__UI__TYPE__DIALOG:
                        $l_data[$l_key] = ($_POST[$l_post_key] === '-1') ? null : $_POST[$l_post_key];
                        break;

                    case C__PROPERTY__UI__TYPE__DATE:
                        $l_date = null;
                        if (isset($_POST[$l_post_key . '__HIDDEN']) && $_POST[$l_post_key . '__HIDDEN'] !== '-') {
                            $l_date .= $_POST[$l_post_key . '__HIDDEN'];
                        }

                        if (isset($l_date)) {
                            $l_data[$l_key] = $l_date;
                        }
                        break;

                    case C__PROPERTY__UI__TYPE__DATETIME:
                        $l_date = null;

                        if (isset($_POST[$l_post_key . '__VIEW']) && $_POST[$l_post_key . '__VIEW'] !== '-') {
                            $l_date = $_POST[$l_post_key . '__VIEW'];
                        } elseif (isset($_POST[$l_post_key]) && isset($_POST[$l_post_key . '__TIME'])) {
                            $l_date = $_POST[$l_post_key];
                        }

                        if ($l_date !== null && isset($_POST[$l_post_key . '__TIME']) && $_POST[$l_post_key . '__TIME'] !== '-') {
                            $l_date .= ' ' . $_POST[$l_post_key . '__TIME'];
                        }

                        if ($l_date === null && isset($_POST[$l_post_key])) {
                            $l_date = $_POST[$l_post_key];
                        }

                        // ID-3062 Bugfix
                        if ($l_date === null && isset($_POST['C__CATG__CUSTOM__' . $l_post_key . '__HIDDEN'])) {
                            $l_date = $_POST['C__CATG__CUSTOM__' . $l_post_key . '__HIDDEN'];
                        }

                        $l_data[$l_key] = $l_date;
                        break;

                    case C__PROPERTY__UI__TYPE__DIALOG_LIST:
                        // We should save data, even if its empty. This is important to detect cleared data.
                        $l_data[$l_key] = $_POST[$l_post_key . '__selected_values'];

                        // This is new, since we use "chosen" JS script as dialog_list.
                        if (empty($l_data[$l_key]) && is_array($_POST[$l_post_key . '__selected_box'])) {
                            $l_data[$l_key] = implode(',', $_POST[$l_post_key . '__selected_box']);
                        }
                        break;

                    default:
                        if (!empty($_POST[$l_post_key . '__HIDDEN']) || (isset($_POST[$l_post_key . '__HIDDEN']) && isset($_POST[$l_post_key]))) {
                            $l_data[$l_key] = $_POST[$l_post_key . '__HIDDEN'];
                        } else {
                            if (!empty($_POST[$l_post_key . '__selected_values'])) {
                                $l_data[$l_key] = $_POST[$l_post_key . '__selected_values'];
                            } else {
                                if (isset($l_post_key)) {
                                    $l_custom_key = 'C__CATG__CUSTOM__' . $l_post_key;

                                    // Custom field
                                    $l_post_key_hidden = $l_post_key . '__HIDDEN';
                                    if (isset($_POST[$l_custom_key])) {
                                        // standard values in custom categories
                                        $l_data[$l_key] = $_POST[$l_custom_key];
                                    } elseif (isset($_POST[$l_post_key_hidden])) {
                                        // hidden values in custom categories
                                        $l_data[$l_key] = $_POST[$l_post_key_hidden];
                                    } else {
                                        // default for standard categories
                                        if (isset($_POST[$l_post_key])) {
                                            $l_data[$l_key] = $_POST[$l_post_key];
                                        }

                                        if ($l_value[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__TIMEPERIOD) {
                                            $toKey = substr($l_post_key, 0, -4) . 'TO';

                                            if (isset($_POST[$toKey])) {
                                                $l_data[$l_key] = isys_format_json::encode([
                                                    'from' => $_POST[$l_post_key],
                                                    'to' => $_POST[$toKey]
                                                ]);
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        break;
                }
            }
        }

        return $l_data;
    }

    /**
     * Fetches category data from database.
     *
     * @param   integer $p_category_data_id
     * @param   mixed   $p_obj_id May be an integer, or an array of integers.
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @author  Benjamin Heisig <bheisig@synetics.de>
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function get_data($p_category_data_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_properties = $this->get_properties();
        $l_selection = '';
        $l_query_joins = '';
        $l_already_joined = [];

        $l_query = $this->get_conditionless_query();

        // Always fetch additional data for 'connection', dialog (plus)' and 'autotext' fields:
        if (empty($l_query)) {
            foreach ($l_properties as $l_property) {
                // @todo check if specific modules are installed  (example: category application nagios module)
                if (isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES]) && is_array($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES])) {
                    if (strpos($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], '_2_')) {
                        continue;
                    }

                    $l_join_it = false;
                    if (isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS])) {
                        if (!isset($l_already_joined[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS]])) {
                            if ($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == 'isys_obj') {
                                $l_selection .= $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] . '.isys_obj__title as ' .
                                    $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] . '_title, ';
                            } else {
                                $l_selection .= $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] . '.*, ';
                            }
                            $l_already_joined[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS]] = true;
                            $l_join_it = true;
                        }
                    } else {
                        if (!isset($l_already_joined[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]])) {
                            $l_selection .= $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '.*, ';
                            $l_already_joined[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]] = true;
                            $l_join_it = true;
                        }
                    }

                    if (isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) {
                        $l_selection .= ((isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS])) ? $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] .
                                '.' : ((isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0])) ? $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] .
                                '.' : '')) . $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][1] . ' AS ' .
                            $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] . ', ';

                        if ($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] === 'isys_connection' ||
                            $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'connection') {
                            $l_selection .= ((isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS])) ? $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] .
                                    '.' : ((isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0])) ? $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] .
                                    '.' : '')) . 'isys_connection__isys_obj__id AS ' . $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] . '__object, ';
                        }
                    }

                    if ($l_join_it) {
                        $l_query_joins .= 'LEFT JOIN ' . $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] .
                            ((isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS])) ? ' AS ' .
                                $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] : '') . ' ON ' . $this->m_table . '.' .
                            $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] . ' = ' .
                            ((isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS])) ? $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] .
                                '.' : ((isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0])) ? $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] .
                                '.' : '')) . '' . $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][1] . ' ';
                    }
                }
            }

            // Conditionless query
            $l_query = 'SELECT mainObject.*, isys_obj_type.*, ' . $l_selection . $this->m_table . '.* FROM isys_obj mainObject ' . 'INNER JOIN ' . $this->m_table . ' ON ' .
                $this->m_table . '__isys_obj__id = mainObject.isys_obj__id ' . 'INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id ' .
                $l_query_joins . ' ' . 'WHERE TRUE ';

            $this->set_conditionless_query($l_query);
        }

        // Filter data:
        if (isset($p_filter)) {
            $l_query .= $this->prepare_filter($p_filter);
        }

        // Reduce data by object identifier:
        if ($p_obj_id !== null) {
            $l_query .= $this->get_object_condition($p_obj_id, 'mainObject');
        }

        // Reduce data by category data identifier:
        // @fixme Misbehavior detected by some code (could be 'FALSE' or a negative integer)
        $l_go_on = true;
        if ($this->is_multivalued() === false && $p_category_data_id == 'FALSE') {
            $l_go_on = false;
        }

        if (isset($p_category_data_id) && $l_go_on) {
            $l_query .= ' AND ' . $this->m_table . '.' . $this->m_table . '__id = ' . $this->convert_sql_id($p_category_data_id);
        }

        // Reduce data by record status:
        if (isset($p_status)) {
            $l_query .= ' AND ' . $this->m_table . '.' . $this->m_table . '__status = ' . $this->convert_sql_id($p_status);
        }

        // Condition:
        if (isset($p_condition)) {
            // LF: Do NOT remove the whitespaces!
            $l_query .= ' ' . $p_condition . ' ';
        }

        unset($l_properties, $l_selection, $l_already_joined, $l_query_joins);

        // Return result set:
        return $this->retrieve($l_query);
    }

    /**
     * Simple wrapper of get_data()
     *
     * @param null   $p_category_data_id
     * @param null   $p_obj_id
     * @param string $p_condition
     * @param null   $p_filter
     * @param null   $p_status
     *
     * @return array Category result as array
     */
    public function get_data_as_array($p_category_data_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        return $this->get_data($p_category_data_id, $p_obj_id, $p_condition, $p_filter, $p_status)
            ->__as_array();
    }

    /**
     * @desc   return data object for current category by object id
     * @author Dennis Stücken <dstuecken@synetics.de>
     *
     * @param int    $p_obj_id
     * @param string $p_condition
     * @param int    $p_status
     *
     * @return isys_component_dao_result
     */
    public function get_data_by_object($p_obj_id, $p_condition = null, $p_status = null)
    {
        return $this->get_data(null, $p_obj_id, $p_condition, null, $p_status);
    }

    /**
     * @desc   return data object for current category by id
     * @author Dennis Stücken <dstuecken@synetics.de>
     *
     * @param int    $p_list_id
     * @param string $p_condition
     * @param int    $p_status
     *
     * @return isys_component_dao_result
     */
    public function get_data_by_id($p_list_id, $p_condition = null, $p_status = null)
    {
        if (is_null($p_list_id)) {
            $p_list_id = -1;
        }

        return $this->get_data($p_list_id, null, $p_condition, null, $p_status);
    }

    /**
     * Gets category's identifier.
     *
     * @return int
     */
    public function get_category_id()
    {
        return $this->m_category_id;
    }

    /**
     * Method for retrieving the name of a category.
     *
     * @return string
     * @throws isys_exception_database
     */
    public function getCategoryTitle()
    {
        if ($this->categoryTitle === null) {
            // Check for GLOBAL or SPECIFIC category. Custom categories implement their own method.
            try {
                if ($this->m_cat_type == C__CMDB__CATEGORY__TYPE_GLOBAL) {
                    $result = $this->retrieve('SELECT isysgui_catg__title AS title 
                    FROM isysgui_catg 
                    WHERE isysgui_catg__id = ' . $this->convert_sql_id($this->m_category_id) . ';');
                } else {
                    $result = $this->retrieve('SELECT isysgui_cats__title AS title 
                    FROM isysgui_cats 
                    WHERE isysgui_cats__id = ' . $this->convert_sql_id($this->m_category_id) . ';');
                }

                $this->categoryTitle = $result->get_row_value('title');
            } catch (\Exception $exception) {
                $this->categoryTitle = null;
            }
        }

        return $this->categoryTitle;
    }

    /**
     * Sets category's identifier if necessary
     *
     * @param $p_value
     *
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_category_id($p_value)
    {
        $this->m_category_id = $p_value;
    }

    /**
     * Return a specific property
     *
     * @param $p_key
     *
     * @return array
     */
    public function get_property_by_key($p_key)
    {
        if (isset($this->m_properties[$p_key])) {
            return $this->m_properties[$p_key];
        } else {
            return $this->get_properties()[$p_key] ?: null;
        }
    }

    /**
     * Gets information about manipulating, filtering, importing, exporting, and
     * transforming data. Properties will be completed with additional information.
     *
     * @param   integer $p_get_with This parameter defines, if we want the properties merged with several extra data.
     *
     * @return  array
     */
    public function get_properties($p_get_with = null)
    {
        // Returned cached property array, but only rely on it if "$p_get_with" is null.
        if (isset($this->m_cached_properties) && $this->m_cached_properties && $p_get_with === null) {
            return $this->m_cached_properties;
        }

        // ID-2997  Changed from "!isset($this->m_properties)" to "empty()" because the variable will always be set (as empty array).
        if (empty($this->m_properties)) {
            $this->m_properties = $this->properties();
        }

        $l_extended_properties = isys_component_signalcollection::get_instance()
            ->emit("mod.cmdb.extendProperties", $this->get_category_id(), $this->get_category_type());

        if (is_array($l_extended_properties)) {
            $l_extended_properties = array_shift($l_extended_properties);
            if (!empty($l_extended_properties) && is_array($l_extended_properties)) {
                $this->m_properties = array_merge($this->m_properties, $l_extended_properties);
            }
        }

        $this->m_cached_properties = is_array($this->m_properties) ? $this->m_properties : [];

        // Connect general properties with custom ones
        $this->m_cached_properties += $this->get_custom_properties();

        if ($p_get_with === null) {
            return $this->m_cached_properties;
        }

        if ($p_get_with & C__PROPERTY__WITH__VALIDATION) {
            if ($this->m_cat_type == C__CMDB__CATEGORY__TYPE_CUSTOM || $this instanceof \isys_cmdb_dao_category_g_custom_fields) {
                $l_validation = isys_caching::factory('validation_config')->get('g_custom');

                // Seems as if we got no cache! So we prepare it...
                if ($l_validation === false) {
                    $l_validation = isys_module_cmdb::create_validation_cache()->get('g_custom');
                }

                $validationKey = 'isys_cmdb_dao_category_g_custom_fields:' . $this->get_catg_custom_id();

                // If we still got no cached user-validation we can skip this.
                if ($l_validation !== false) {
                    foreach ($this->m_cached_properties as $l_key => $l_property) {
                        $this->m_cached_properties[$l_key][C__PROPERTY__CHECK] = $l_validation[$validationKey][$l_key][C__PROPERTY__CHECK];
                    }
                }
            } else {
                $l_cattype = ($this->m_cat_type == C__CMDB__CATEGORY__TYPE_GLOBAL) ? 'g' : 's';

                $l_validation = isys_caching::factory('validation_config')->get($l_cattype);

                // Seems as if we got no cache! So we prepare it...
                if ($l_validation === false) {
                    $l_validation = isys_module_cmdb::create_validation_cache()->get($l_cattype);
                }

                // If we still got no cached user-validation we can skip this.
                if ($l_validation !== false) {
                    $validationKey = get_class($this);

                    // Are there user specific validation rules defined?
                    if (isset($l_validation[$validationKey]) && is_countable($l_validation[$validationKey]) && count($l_validation[$validationKey])) {
                        foreach ($l_validation[$validationKey] as $l_key => $l_property) {
                            // Prevent overwriting DAO specific rules
                            if ($this->m_cached_properties[$l_key][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VALIDATION]) {
                                // Merge user specific validation rules with dao
                                $this->m_cached_properties[$l_key][C__PROPERTY__CHECK] = $l_property[C__PROPERTY__CHECK];
                            }

                            // But we should always be able to add "mandatory" and "unique" validation.
                            $this->m_cached_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY] = $l_property[C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY];
                            $this->m_cached_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJ] = $l_property[C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJ];
                            $this->m_cached_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJTYPE] = $l_property[C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJTYPE];
                            $this->m_cached_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_GLOBAL] = $l_property[C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_GLOBAL];
                        }
                    }
                }
            }
        }

        return $this->m_cached_properties;
    }

    /**
     * @param string $p_categoryConst
     *
     * @return int|null
     * @throws isys_exception_database
     */
    public function getCategorySpecificIdByConst(string $p_categoryConst) : ?int
    {
        $sql = "SELECT isysgui_cats__id as id
                FROM isysgui_cats
                WHERE isysgui_cats__const = " . $this->convert_sql_text($p_categoryConst) .
                " LIMIT 1";
        $id = $this->retrieve($sql)->get_row_value('id');
        return $id;
    }

    /**
     * @param string $p_categoryConst
     *
     * @return int|null
     * @throws isys_exception_database
     */
    public function getCategoryGlobalIdByConst(string $p_categoryConst) : ?int
    {
        $sql = "SELECT isysgui_catg__id as id
                FROM isysgui_catg
                WHERE isysgui_catg__const = " . $this->convert_sql_text($p_categoryConst) .
            " LIMIT 1";
        $id = $this->retrieve($sql)->get_row_value('id');
        return $id;
    }

    /**
     * @param string $p_categoryConst
     *
     * @return int|null
     * @throws isys_exception_database
     */
    public function getCategoryIdByConst(string $p_categoryConst) : ?int
    {
        $id = $this->getCategoryGlobalIdByConst($p_categoryConst);
        if (!$id) {
            $id = $this->getCategorySpecificIdByConst($p_categoryConst);
        }
        return $id;
    }

    /**
     * Retrieve custom properties
     *
     * @param bool $p_configured Get only configured properties
     *
     * @return array
     * @throws \Exception
     */
    public function get_custom_properties($p_configured = false)
    {
        // Get custom properties
        $l_properties = [];

        // Are there any custom properties
        if (method_exists($this, 'custom_properties')) {
            $l_properties = $this->custom_properties();

            if (is_array($l_properties)) {
                $l_dao_custom_properties = new isys_cmdb_dao_custom_property($this->m_db);

                foreach ($l_properties as $l_property_key => $l_property_data) {
                    // Get custom data for property from DB
                    $l_custom_data = $l_dao_custom_properties->get_data(null, $this->get_category_id(), $this->get_category_type_abbr(), $l_property_key);

                    if ($l_custom_data->num_rows()) {
                        $l_custom_data = $l_custom_data->get_row_value('isys_custom_properties__data');

                        if (!empty($l_custom_data)) {
                            // Decode custom data
                            $l_custom_data = isys_format_json::decode($l_custom_data);

                            if (is_array($l_custom_data)) {
                                // Merge custom data with property master
                                $l_properties[$l_property_key] = array_replace_recursive($l_property_data, $l_custom_data);
                            } else {
                                if ($p_configured) {
                                    unset($l_properties[$l_property_key]);
                                }
                            }
                        } else {
                            if ($p_configured) {
                                unset($l_properties[$l_property_key]);
                            }
                        }
                    } else {
                        if ($p_configured) {
                            unset($l_properties[$l_property_key]);
                        }
                    }
                }
            }
        }

        // We will allways return an array
        if (!is_array($l_properties)) {
            $l_properties = [];
        }

        return $l_properties;
    }

    /**
     * Generic save method for custom properties.
     *
     * @param   integer $p_id
     * @param   array   $p_data
     *
     * @return  bool
     * @throws  isys_exception_dao
     */
    public function save_custom_properties($p_id, $p_data)
    {
        if (!empty($p_id)) {
            $l_custom_properties = $this->get_custom_properties();

            if (is_countable($l_custom_properties) && count($l_custom_properties)) {
                // Prepare statement
                $l_sql = 'UPDATE ' . $this->get_source_table() . ' SET %s WHERE ' . $this->get_source_table() . '__id = ' . $this->convert_sql_id($p_id) . ';';

                $l_values = [];

                // Collect values
                foreach ($l_custom_properties as $l_property_key => $l_property_data) {
                    if (isset($l_property_data[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]) && !is_null($l_property_data[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD])) {
                        $l_values[] = ' ' . $l_property_data[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] . ' = ' .
                            $this->convert_sql_text($p_data[$l_property_data[C__PROPERTY__UI][C__PROPERTY__UI__ID]]);
                    }
                }

                if (count($l_values)) {
                    $l_values = implode(',', $l_values);

                    $l_sql = sprintf($l_sql, $l_values);

                    return ($this->update($l_sql) && $this->apply_update());
                }
            }
        }

        return true;
    }

    /**
     * Method for retrieving the dynamic properties which are being used to display special information inside the generic list-component.
     *
     * @return  array
     */
    public function get_dynamic_properties()
    {
        return $this->dynamic_properties();
    }

    /**
     * Wrapper method for "get_properties".
     *
     * @deprecated
     *
     * @param   integer $p_get_with
     *
     * @return  array
     */
    public function get_properties_ng($p_get_with = null)
    {
        return $this->get_properties($p_get_with);
    }

    /**
     * Retrieves the number of saved category-entries to the given object.
     *
     * @param   integer $objectId
     *
     * @return  integer
     */
    public function get_count($objectId = null)
    {
        $table = false;

        if ($objectId === null || $objectId <= 0) {
            $objectId = $this->m_object_id;
        }

        // @see ID-2736
        if (!empty($this->m_source_table)) {
            if (substr($this->m_source_table, -5) === '_list' || strpos($this->m_source_table, '_2_') !== false) {
                $table = $this->m_source_table;
            } else {
                $table = $this->m_source_table . '_list';
            }
        }

        if ($table && $objectId > 0) {
            $sql = 'SELECT COUNT(' . $table . '__id) as count
				FROM ' . $table . '
				WHERE ' . $table . '__status ' . $this->prepare_in_condition([C__RECORD_STATUS__NORMAL, C__RECORD_STATUS__TEMPLATE]) . '
				AND ' . $table . '__isys_obj__id = ' . $this->convert_sql_id($objectId) . ';';

            return (int) $this->retrieve($sql)->get_row_value('count');
        }

        return false;
    }

    /**
     *
     * @return  string
     */
    public function get_strLogbookSQL()
    {
        return $this->m_strLogbookSQL;
    }

    /**
     *
     * @param   string $p_value
     *
     * @return  isys_cmdb_dao_category
     */
    public function set_strLogbookSQL($p_value)
    {
        $this->m_strLogbookSQL = $p_value;

        return $this;
    }

    /**
     *
     * @return  array
     */
    public function get_arrLogbookEntries()
    {
        return $this->m_arrLogbookEntries;
    }

    /**
     *
     * @param   mixed $p_value
     *
     * @return  isys_cmdb_dao_category
     */
    public function set_arrLogbookEntries($p_value)
    {
        $this->m_arrLogbookEntries[] = $p_value;

        return $this;
    }

    /**
     * Creates the condition to the object table.
     *
     * @param   integer $p_obj_id May be an integer or an array of integers.
     * @param   string  $p_alias
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if ($p_obj_id !== null) {
            if (is_array($p_obj_id)) {
                $l_sql = ' AND (' . $p_alias . '.isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ')';
            } else {
                $l_sql = ' AND (' . $p_alias . '.isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ')';
            }
        }

        return $l_sql;
    }

    /**
     * Create logbook entry on category update.
     *
     * @param  string $p_strConst
     * @param  string $p_lc_category
     * @param  string $p_changes
     */
    public function logbook_update($p_strConst, $p_lc_category, $p_changes)
    {
        isys_event_manager::getInstance()
            ->triggerCMDBEvent(
                $p_strConst,
                $this->get_strLogbookSQL(),
                $_GET[C__CMDB__GET__OBJECT],
                $_GET[C__CMDB__GET__OBJECTTYPE],
                $p_lc_category,
                $p_changes,
                $_POST["LogbookCommentary"],
                $_POST["LogbookReason"]
            );
    }

    /**
     * Create logbook entry on creating category entries.
     *
     * @param  string $p_strConst
     * @param  string $p_lc_category
     */
    public function logbook_create($p_strConst, $p_lc_category)
    {
        $this->logbook_rank($_GET[C__CMDB__GET__OBJECT], $p_strConst, $this->get_strLogbookSQL(), $p_lc_category);
    }

    /**
     * Sanitizes Post Data.
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function sanitize_post_data()
    {
        // ID-2997  Changed from "!isset($this->m_properties)" to "empty()" because the variable will always be set (as empty array).
        if (empty($this->m_properties)) {
            $l_properties = $this->get_properties();
        } else {
            $l_properties = $this->m_properties;
        }

        // Get the original POST data from the symfony request object.
        $l_raw_post = isys_application::instance()->container->request->request;

        if (isset($l_properties)) {
            foreach ($l_properties as $l_prop_key => $l_prop) {
                // @see ID-2736
                if (empty($l_prop_key)) {
                    continue;
                }

                if (is_array($l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]) || $this->get_category_type() == C__CMDB__CATEGORY__TYPE_CUSTOM) {
                    // Custom field
                    $l_post_key = substr_replace($l_prop_key, 'C__CATG__CUSTOM_', 0, strpos($l_prop_key, '_c_'));
                    $l_post_key_hidden = $l_post_key . '__HIDDEN';
                    if (isset($_POST[$l_post_key_hidden])) {
                        $l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID] = $l_post_key_hidden;
                        if (!isset($l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION])) {
                            continue;
                        }
                    } elseif (isset($_POST[$l_post_key])) {
                        $l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID] = $l_post_key;
                        if (!isset($l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION])) {
                            continue;
                        }
                    }
                }

                if (isset($l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]) && array_key_exists($l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID], $_POST)) {
                    // @see  ID-5379  Skip empty values with no default.
                    if (is_scalar($_POST[$l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]]) && mb_strlen($_POST[$l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]]) === 0) {
                        continue;
                    }

                    if (is_scalar($_POST[$l_prop[C__PROPERTY__UI][C__PROPERTY__UI__DEFAULT]]) && mb_strlen($l_prop[C__PROPERTY__UI][C__PROPERTY__UI__DEFAULT]) === 0) {
                        continue;
                    }

                    if (isset($l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION])) {
                        if (is_array($l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION])) {
                            if (isset($l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION][0])) {
                                if ($l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION][0] > 0) {
                                    $l_filter = $l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION][0];
                                } else {
                                    $l_filter = constant($l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION][0]);
                                }

                                if (isset($l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION][1])) {
                                    $l_options = $l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION][1];
                                } else {
                                    $l_options = null;
                                }

                                $_POST[$l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]] = @filter_var($_POST[$l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]], $l_filter, $l_options);

                                // @see ID-3784 We want to notify the user, if his data has been modified by the sanitation.
                                if ($l_raw_post->has($l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]) && $_POST[$l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]] != $l_raw_post->get($l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID])) {
                                    isys_notify::warning(isys_application::instance()->container->get('language')
                                        ->get('LC__CMDB__SANITATION__CHANGED_VALUE', [
                                            isys_glob_htmlentities($l_raw_post->get($l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID])),
                                            isys_glob_htmlentities($_POST[$l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]])
                                        ]), ['sticky' => true]);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $_POST;
    }

    /**
     * Gets current object id.
     *
     * @return  integer
     */
    public function get_object_id()
    {
        return (int)$this->m_object_id;
    }

    /**
     * Gets current object type id.
     *
     * @return  integer
     */
    public function get_object_type_id()
    {
        return (int)$this->m_object_type_id;
    }

    /**
     * Universal Ranker. This methods ranks an category entry.
     *
     * @param   integer $p_categoryID
     * @param   integer $p_statusID
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@synetics.de>
     */
    public function update_catlevel($p_categoryID, $p_statusID)
    {
        $l_sql = "UPDATE " . $this->get_table() . " SET " . $this->get_table() . "__status = " . $this->convert_sql_id($p_statusID) . " WHERE " . $this->get_table() .
            "__id = " . $this->convert_sql_id($p_categoryID) . ";";

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * Method for deleting all entries by a given object id.
     *
     * @param   integer $p_obj_id
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function delete_entries_by_obj_id($p_obj_id)
    {
        if ($p_obj_id > 0) {
            return $this->update('DELETE FROM ' . $this->m_table . ' WHERE ' . $this->m_table . '__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';');
        }

        return false;
    }

    /**
     * This method unsets the properties. This function is import for exporting custom categories.
     */
    public function unset_properties()
    {
        $this->m_cached_properties = $this->m_properties = [];
    }

    /**
     *
     * $p_provides syntax:
     *
     * $p_provides = [C__PROPERTY__PROVIDES__REPORT, C__PROPERTY__PROVIDES__LIST]
     *
     * @param   isys_array $p_array_reference
     * @param   integer    $p_record_status
     * @param   array      $p_provides Return property values only if theses flags are set to TRUE. Set to empty array to return all properties.
     *
     * @return  isys_array
     * @throws  isys_exception_general
     */
    public function category_data(&$p_array_reference, $p_record_status = C__RECORD_STATUS__NORMAL, array $p_provides = [])
    {
        if (!$this->get_object_id()) {
            return new isys_array();
        }

        $l_properties = $this->get_properties();
        $l_dynamic_properties = $this->get_dynamic_properties();
        $i = 0;

        /**
         * Retrieve category data
         */
        $l_catdata = $this->get_data_by_object($this->get_object_id(), null, $p_record_status);

        /* Format category result */
        while ($l_row = $l_catdata->get_row()) {
            $l_current_row = new isys_array([], ArrayObject::ARRAY_AS_PROPS);

            foreach ($l_properties as $l_key => $l_propdata) {
                if (is_string($l_key)) {
                    // Only load properties with special provides flags
                    $l_provides_stop = count($p_provides) > 0;
                    foreach ($p_provides as $l_provides) {
                        // If one of the provides flags is true, go further
                        if (isset($l_propdata[C__PROPERTY__PROVIDES][$l_provides]) && $l_propdata[C__PROPERTY__PROVIDES][$l_provides] === true) {
                            $l_provides_stop = false;
                            continue;
                        }
                    }
                    if ($l_provides_stop) {
                        continue;
                    }

                    if (isset($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]) &&
                        isset($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1])) {

                        /* Call helper object to retrieve more information */
                        if (class_exists($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0])) {
                            $l_helper = new $l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0](
                                $l_row,
                                $this->get_database_component(),
                                $l_propdata[C__PROPERTY__DATA],
                                $l_propdata[C__PROPERTY__FORMAT],
                                $l_propdata[C__PROPERTY__UI]
                            );

                            /* Set the Unit constant for the convert-helper */
                            if ($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'convert') {
                                if (method_exists($l_helper, 'set_unit_const')) {
                                    $l_row_unit = $l_properties[$l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT]][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                                    $l_helper->set_unit_const($l_row[$l_row_unit]);
                                }
                            }

                            try {
                                $l_helper_data = call_user_func([
                                    $l_helper,
                                    $l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1]
                                ], $l_row[$l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]]);
                            } catch (isys_exception_general $e) {
                                throw new isys_exception_general($e->getMessage() . '. Problem occurred for property ' .
                                    $l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] . ' in ' . $this->get_category_const());
                            }

                            $l_current_row[$l_key] = $this->category_data_extract_helper_data($l_helper_data);

                            unset($l_helper_data);
                        }
                    } else {
                        $l_current_row[$l_key] = new isys_cmdb_dao_category_data_value($l_row[$l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]]);
                    }

                    unset($l_helper_class, $l_helper);
                }
            }

            // Dynamic properties.
            foreach ($l_dynamic_properties as $l_dkey => $l_dpropdata) {
                if (isset($l_dpropdata[C__PROPERTY__FORMAT])) {
                    if (isset($l_dpropdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]) &&
                        isset($l_dpropdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1])) {
                        $l_cat_dao = $l_dpropdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0];
                        $l_method = $l_dpropdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1];

                        if (method_exists($l_cat_dao, $l_method)) {
                            $l_current_row[$l_dkey] = new isys_cmdb_dao_category_data_value($l_cat_dao->$l_method($l_row, $l_row));
                        }
                    }
                }
            }

            if ($l_current_row->count() > 0) {
                if (isset($l_row[$this->m_table . '__id'])) {
                    $p_array_reference[$l_row[$this->m_table . '__id']] = $l_current_row;
                } else {
                    $p_array_reference[$i] = $l_current_row;
                }

                unset($l_current_row);
            }
        }

        // Free memory
        $l_catdata->free_result();

        unset($l_properties, $l_dynamic_properties);

        return $p_array_reference;
    }

    /**
     * @param array $p_data
     *
     * @return isys_cmdb_dao_category_data_reference|isys_cmdb_dao_category_data_value
     */
    public function category_data_extract_helper_subdata(array $p_data)
    {
        if (isset($p_data['id'])) {
            return new isys_cmdb_dao_category_data_reference((isset($p_data['ref_title'])) ? $p_data['ref_title'] : @$p_data['title'], $p_data['id'], $p_data);
        } else {
            return new isys_cmdb_dao_category_data_value((isset($p_data['ref_title'])) ? $p_data['ref_title'] : @$p_data['title'], $p_data);
        }
    }

    /**
     * Callback method which returns the master and slave object for the relation
     *
     * @param isys_request $p_request
     *
     * @return isys_array
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function callback_property_relation_handler(isys_request $p_request, $p_parameters = [])
    {
        [$l_class, $l_switch_fields] = $p_parameters;
        $l_return = [];

        if (class_exists($l_class)) {
            $l_dao = call_user_func([
                $l_class,
                'instance'
            ], isys_application::instance()->database);

            $l_data = $l_dao->get_data_by_id($p_request->get_category_data_id())
                ->get_row();

            if (isset($l_data[$l_dao->m_object_id_field])) {
                if ($l_switch_fields === true) {
                    $l_return[C__RELATION_OBJECT__MASTER] = $l_data[$l_dao->m_connected_object_id_field];
                    $l_return[C__RELATION_OBJECT__SLAVE] = $l_data[$l_dao->m_object_id_field];
                } else {
                    $l_return[C__RELATION_OBJECT__MASTER] = $l_data[$l_dao->m_object_id_field];
                    $l_return[C__RELATION_OBJECT__SLAVE] = $l_data[$l_dao->m_connected_object_id_field];
                }
            }
        }

        return $l_return;
    }

    /**
     * Method which retrieves only the specified property data
     *
     * @param null   $p_cat_data_id
     * @param null   $p_obj_id
     * @param string $p_property
     *
     * @return bool|isys_component_dao_result
     * @throws Exception
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_data_by_property($p_cat_data_id = null, $p_obj_id = null, $p_property = '')
    {
        $l_properties = $this->get_properties();
        if (isset($l_properties[$p_property])) {
            if (isset($l_properties[$p_property][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD])) {
                $l_table = $this->get_table();
                $l_data_field = $l_properties[$p_property][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                $l_join = '';
                try {
                    if ($l_properties[$p_property][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == 'isys_connection') {
                        $l_join = ' INNER JOIN isys_connection ON isys_connection__id = ' . $l_data_field;
                        $l_data_field = 'isys_connection__isys_obj__id';
                    }

                    $l_sql = 'SELECT ' . $l_data_field . ' AS ' . $p_property . ' FROM ' . $l_table . $l_join . ' WHERE TRUE ';

                    if ($p_cat_data_id !== null) {
                        $l_sql .= ' AND ' . $l_table . '__id = ' . $this->convert_sql_id($p_cat_data_id);
                    }

                    if ($p_obj_id !== null) {
                        $l_sql .= ' AND ' . $l_table . '__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
                    }

                    return $this->retrieve($l_sql);
                } catch (Exception $e) {
                    throw new Exception('Could not retrieve data with error: ' . $e->getMessage());
                }
            }
        }

        return false;
    }

    /**
     * Setter for setting category data into m_data
     *
     * @param $p_data
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_category_data($p_data)
    {
        $this->m_data = $p_data;
    }

    /**
     * Getter for retrieving category data from m_data
     *
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_category_data()
    {
        return $this->m_data;
    }

    /**
     * Method for retrieving all category properties.
     *
     * @return  array
     */
    protected function properties()
    {
        ;
    }

    /**
     * Get specific property
     *
     * @param string $p_key
     *
     * @return array
     */
    protected function property($p_key)
    {
        $l_props = $this->properties();

        return isset($l_props[$p_key]) ? $l_props[$p_key] : [];
    }

    /**
     * Get specific property
     *
     * @param string $p_key
     *
     * @return array
     */
    protected function dynamic_property($p_key)
    {
        $l_props = $this->dynamic_properties();

        return isset($l_props[$p_key]) ? $l_props[$p_key] : [];
    }

    /**
     * Abstract method for retrieving the dynamic properties of every category dao.
     *
     * @author  Dennis Stuecken <dstuecken@i-doit.de>
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [];
    }

    /**
     * @param $p_category_id
     * @param $p_connected_object_id
     *
     * @return bool|mixed
     * @throws isys_exception_cmdb
     */
    protected function handle_connection($p_category_id, $p_connected_object_id)
    {
        $l_connection = new isys_cmdb_dao_connection($this->get_database_component());
        $l_connection_id = $l_connection->retrieve_connection($this->get_table(), $p_category_id);

        if (!$l_connection_id) {
            $l_connection_id = $l_connection->attach_connection($this->get_table(), $p_category_id, null, $this->get_table() . '__isys_connection__id');
        }

        $l_connection->update_connection($l_connection_id, $p_connected_object_id);

        return $l_connection_id;
    }

    /**
     * Generic relation handling
     *
     *  To enable generic handling, set your property to
     *    [C__PROPERTY__DATA][C__PROPERTY__DATA__RELATION_TYPE] => C__RELATION_TYPE__XYZ
     *
     * @param $p_list_id
     * @param $p_data
     */
    protected function handle_relation_generic($p_list_id, $p_data)
    {
        if ($p_list_id > 0 && is_array($p_data)) {
            $l_properties = $this->get_properties();
            $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->m_db);

            foreach ($l_properties as $l_property) {
                if (isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__RELATION_TYPE]) && isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__RELATION_HANDLER])) {
                    $l_handler = $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__RELATION_HANDLER];
                    if (is_object($l_handler) && method_exists($l_handler, 'execute')) {
                        $l_relation_data = $l_handler->execute(new isys_request($p_data));

                        if (is_array($l_relation_data) && count($l_relation_data) > 1) {
                            $l_data = $this->get_data_by_id($p_list_id)
                                ->get_row();
                            $l_relation_id = @$l_data[$this->m_table . '__isys_catg_relation_list__id'] ? $l_data[$this->m_table . '__isys_catg_relation_list__id'] : null;

                            $l_relation_dao->handle_relation(
                                $p_list_id, // category id
                                $this->m_table, // table
                                $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__RELATION_TYPE], // relation type
                                $l_relation_id, // relation id
                                $l_relation_data[0], // master
                                $l_relation_data[1] // slave
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Prepares category's data before creating new category data or updating existing ones.
     *
     * @param   array $p_data
     *
     * @return  mixed Returns data which should be handled (array), otherwise false (bool)
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    protected function prepare_data($p_data)
    {
        $l_data = [];

        // Special fields:
        if (isset($p_data['id'])) {
            $l_data['id'] = $p_data['id'];
        }

        if (isset($p_data['isys_obj__id'])) {
            $l_data['isys_obj__id'] = $p_data['isys_obj__id'];
        }

        if (isset($p_data['status'])) {
            $l_data['status'] = $p_data['status'];
        }

        // Get category's properties:
        $l_properties = $this->get_properties();

        // Iterate through properties:
        foreach ($l_properties as $l_key => $l_value) {
            if (!array_key_exists($l_key, $p_data)) {
                continue;
            }

            switch ($l_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1]) {
                case 'contact':

                    $l_dao_ref = new isys_contact_dao_reference($this->m_db);

                    // @todo $p_data[$l_key] can be an json string or contact id
                    if (is_numeric($p_data[$l_key])) {
                        // case contact id
                        $l_data[$l_key] = $p_data[$l_key];
                    } else {
                        // case json string
                        $l_existing_id = null;

                        $l_contact_id = $l_dao_ref->ref_contact($p_data[$l_key], $l_existing_id);
                        unset($l_dao_ref);

                        if ($l_contact_id === false) {
                            $l_data[$l_key] = '';
                        } else {
                            $l_data[$l_key] = intval($l_contact_id);
                        }
                    }
                    break;
                case 'date':
                    // Workaround:
                    if ($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE] == C__TYPE__INT && is_numeric($p_data[$l_key])) {
                        $p_data[$l_key] = date('c', $p_data[$l_key]);
                    }

                    if (isset($p_data[$l_key]) && is_string($p_data[$l_key])) {
                        // @see  ID-5796  We set the date field to "" because non-scalar values will be skipped when creating the SQL.
                        if (empty($p_data[$l_key])) {
                            $l_data[$l_key] = '';
                            continue 2;
                        }

                        if (strpos($p_data[$l_key], ' - ')) {
                            $p_data[$l_key] = str_replace(' - ', ' ', $p_data[$l_key]);
                        }

                        $l_date = strtotime($p_data[$l_key]);
                    } else {
                        $l_date = false;
                    }

                    if ($l_date === false || $l_date < 0) {
                        $l_data[$l_key] = null;
                        continue 2;
                    }

                    $l_data[$l_key] = $l_date;

                    // One of MySQL's date types is used:
                    if ($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE] !== C__TYPE__INT) {
                        $l_format = null;
                        switch ($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE]) {
                            case 'date':
                                $l_format = 'Y-m-d';
                                break;
                            case 'datetime':
                            case 'timestamp':
                                $l_format = 'Y-m-d H:i:s';
                                break;
                            case 'time':
                                $l_format = 'H:i:s';
                                break;
                            case 'year':
                                $l_format = 'Y';
                                break;
                        }
                        $l_data[$l_key] = date($l_format, $l_data[$l_key]);
                    }
                    break;
                default:
                    if (!$l_value[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__IMPORT] && !$l_value[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__LIST] &&
                        !$l_value[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__MULTIEDIT] && !$l_value[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH] &&
                        !$l_value[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VALIDATION]) {
                        continue 2;
                    }

                    $l_data[$l_key] = $p_data[$l_key];
                    break;
            }
        }

        return $l_data;
    }

    /**
     * Prepares SQL query to create or update an entity.
     *
     * @todo    Default values
     *
     * @param   array $p_data Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @throws  isys_exception_dao_cmdb
     * @return  string  Properties' part of the sql query
     * @author  Benjamin Heisig <bheisig@synetics.de>
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    protected function prepare_query(array $p_data)
    {
        $l_result = [];

        // Add special fields if available:
        $l_specials = [
            'id',           // Category's data identifier
            'isys_obj__id', // Related object's identifier
            'status'        // Record status
        ];

        foreach ($l_specials as $l_special) {
            if (array_key_exists($l_special, $p_data)) {
                $l_result[] = $this->m_table . '__' . $l_special . ' = ' . $this->convert_sql_id($p_data[$l_special]);
            }
        }

        $l_properties = $this->get_properties();
        $l_already_specified = [];

        // Iterate through properties:
        foreach ($l_properties as $l_key => $l_value) {
            if (!array_key_exists($l_key, $p_data) || !is_scalar($p_data[$l_key])) {
                // Skip property
                continue;
            }

            $needsConversion = true;

            // @see  ID-5378  Do not use conversion, if the defaults are explicitly set to NULL or "".
            if (mb_strlen($p_data[$l_key]) === 0) {
                if ($l_value[C__PROPERTY__UI][C__PROPERTY__UI__DEFAULT] === null) {
                    $needsConversion = false;
                    $p_data[$l_key] = 'NULL';
                } elseif (mb_strlen($l_value[C__PROPERTY__UI][C__PROPERTY__UI__DEFAULT]) === 0) {
                    $needsConversion = false;
                    $p_data[$l_key] = "''";
                }
            }

            if ($needsConversion) {
                switch ($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE]) {
                    case C__TYPE__TEXT:
                    case C__TYPE__TIME:
                    case C__TYPE__TEXT_AREA:
                        $p_data[$l_key] = $this->convert_sql_text($p_data[$l_key]);
                        break;
                    case C__TYPE__DATE:
                        // @todo No special convert yet!
                        if (empty($p_data[$l_key])) {
                            $p_data[$l_key] = 'NULL';
                        } else {
                            $p_data[$l_key] = $this->convert_sql_text($p_data[$l_key]);
                        }
                        break;
                    case C__TYPE__DATE_TIME:
                        if (empty($p_data[$l_key])) {
                            $p_data[$l_key] = 'NULL';
                        } else {
                            $p_data[$l_key] = $this->convert_sql_datetime($p_data[$l_key]);
                        }
                        break;
                    case C__TYPE__INT:
                        // We check for popups and dialogs with filled "p_strTable" parameter.
                        if ($l_value[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__POPUP ||
                            ($l_value[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DIALOG &&
                                !empty($l_value[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strTable']))) {
                            $p_data[$l_key] = $this->convert_sql_id($p_data[$l_key]);
                        } else {
                            $p_data[$l_key] = $this->convert_sql_int($p_data[$l_key]);
                        }
                        break;
                    case C__TYPE__FLOAT:
                    case C__TYPE__DOUBLE:
                        $p_data[$l_key] = $this->convert_sql_float($p_data[$l_key]);
                        break;
                    // @todo Never used:
                    case 'boolean':
                        $p_data[$l_key] = $this->convert_sql_boolean($p_data[$l_key]);
                        break;
                    default:
                        throw new isys_exception_dao_cmdb(sprintf(
                            'Category %s: Cannot prepare entity because of unknown type "%s".',
                            $this->get_category_const(),
                            $l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE]
                        ), get_class($this));
                }
            }

            if ($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] && !isset($l_already_specified[$l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]]) &&
                $l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] != $this->m_table . '__id') {
                $l_result[] = $l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] . ' = ' . $p_data[$l_key];
                $l_already_specified[$l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] = true;
            }
        }

        return implode(', ', $l_result);
    }

    /**
     * Returns a sql condition which filters by status of table $p_table
     *
     * @param array|int $p_status
     * @param string $p_table
     *
     * @return string
     */
    protected function get_status_condition($p_status, $p_table = 'isys_obj')
    {
        if (!is_null($p_status)) {
            if (is_array($p_status)) {
                return ' AND ' . $p_table . '__status ' . $this->prepare_in_condition($p_status) . ' ';
            } else {
                return ' AND ' . $p_table . '__status = ' . $p_status . ' ';
            }
        }

        return '';
    }

    /**
     * Creates and SQL specific filter for use in a WHERE statement
     *
     * @param array || string $p_filter
     *
     * @return string
     *
     */
    protected function prepare_filter($p_filter)
    {
        if ($p_filter === null) {
            return '';
        }

        $l_info = $this->get_properties();

        if (is_countable($l_info) && count($l_info) == 0) {
            return '';
        }

        // New behavior:
        $this->m_bWordsonly = '%';
        $this->m_bCasesensitiv = '';
        $l_condition = '';
        $l_table = false;

        // @see ID-2736
        if (!empty($this->m_table)) {
            $l_table = (strpos($this->m_table, '_list') !== false) ? $this->m_table : ((is_int(strpos($this->m_table, '_2_')) ? $this->m_table : $this->m_table . '_list'));
        }

        if ($l_table && is_string($p_filter) && strlen($p_filter) >= (int)isys_tenantsettings::get('maxlength.search.filter', 3)) {
            $i = 0;

            foreach ($l_info as $l_value) {

                // Skip properties that shouldn't be included:
                if (isset($l_value[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH]) && $l_value[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH] === false) {
                    continue;
                }
                if ($i == 0) {
                    $l_condition .= 'AND (';
                }
                if ($i++ > 0) {
                    $l_condition .= 'OR ';
                }

                if (isset($l_value['description'])) {
                    $l_ref = $l_table . '.' . $l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                } else {
                    $l_ref = $l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                }

                if (isset($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) {
                    $l_ref = $l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
                } elseif (isset($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES])) {
                    $l_ref = $l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__title';
                }

                if ($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS]) {
                    $l_ref = $l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] . '.' . $l_ref;
                }

                $l_condition .= '(' . $l_ref . ' LIKE \'%' . addslashes($p_filter) . '%\') ';
            }
            if ($i > 0) {
                $l_condition .= ')';
            }

            return $l_condition;
        } else {
            return '';
        }
    }

    /**
     * Updates, applies update and fetches last inserted identifier.
     *
     * @param string $p_query SQL statement
     *
     * @return mixed Last inserted identifier (int) or false (bool)
     */
    protected function do_update($p_query)
    {
        if ($this->update($p_query) && $this->apply_update()) {
            return $this->get_last_insert_id();
        }

        return false;
    }

    /**
     * Method for retrieving the value of a property.
     *
     * @param   string $p_propertyKey
     *
     * @return  string
     */
    protected function get_property($p_propertyKey)
    {
        if (isset($this->m_sync_catg_data['properties'][$p_propertyKey][C__DATA__VALUE])) {
            return $this->m_sync_catg_data['properties'][$p_propertyKey][C__DATA__VALUE];
        } else {
            return null;
        }
    }

    /**
     * Get ON DUPLICATE condition for insert statement
     *
     * @param $p_object_id
     *
     * @return string
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    protected function on_duplicate($p_object_id)
    {
        return ' ON DUPLICATE KEY UPDATE ' . $this->m_table . '__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ' ';
    }

    /**
     * Determines if the category has a relation field or not
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function has_relation()
    {
        return $this->m_has_relation;
    }

    /**
     * @param   mixed $p_data
     *
     * @return  mixed
     */
    private function category_data_extract_helper_data($p_data)
    {
        if ($p_data instanceof isys_export_data) {
            $p_data = $p_data->get_data();
        }

        if (is_array($p_data)) {
            if (isset($p_data[0])) {
                $values = [];

                foreach ($p_data as $l_subdata) {
                    if (is_array($l_subdata)) {
                        $values[] = $this->category_data_extract_helper_subdata($l_subdata);
                    }
                }

                return new isys_cmdb_dao_category_data_multivalue($values);
            } else {
                return $this->category_data_extract_helper_subdata($p_data);
            }
        }

        return new isys_cmdb_dao_category_data_value($p_data);
    }

    /**
     * Constructor.
     *
     * @param  isys_component_database &$p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        parent::__construct($p_db);

        // Initiate important member variables.
        if ($this->m_category) {
            if (!isset($this->m_category_const)) {
                $this->m_category_const = strtoupper('C__' . $this->m_category_type_abbr . '__' . $this->m_category);
            }

            if (defined($this->m_category_const)) {
                if (constant($this->m_category_const) == defined_or_default('C__CATG__CUSTOM_FIELDS') && isset($_GET[C__CMDB__GET__CATG_CUSTOM])) {
                    $this->m_category_id = $_GET[C__CMDB__GET__CATG_CUSTOM];
                } else {
                    $this->m_category_id = constant($this->m_category_const);
                }
            }

            if (!isset($this->m_table)) {
                $this->m_table = 'isys_' . $this->m_category_type_abbr . '_' . $this->m_category . '_list';
            }

            if (!isset($this->m_ui)) {
                $this->m_ui = 'isys_cmdb_ui_category_' . substr($this->m_category_type_abbr, -1) . '_' . $this->m_category;
            }

            if (!isset($this->m_list) && $this->m_multivalued === true) {
                $this->m_list = 'isys_cmdb_dao_list_' . $this->m_category_type_abbr . '_' . $this->m_category;
            }

            if (!isset($this->m_tpl)) {
                $this->m_tpl = $this->m_category_type_abbr . '__' . $this->m_category . '.tpl';
            }
        }

        $this->set_validation(true);

        if (isset($_GET[C__CMDB__GET__OBJECT])) {
            $this->set_object_id($_GET[C__CMDB__GET__OBJECT]);
        }

        if (isset($_GET[C__CMDB__GET__OBJECTTYPE])) {
            $this->set_object_type_id($_GET[C__CMDB__GET__OBJECTTYPE]);
        }
    }

    /**
     * Wrap field with MySQL DATE_FORMAT() function with the user configured date format.
     *
     * @param      $p_field
     * @param bool $p_short
     *
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function build_query_date_format($p_field, $p_time_format = false, $p_short = true)
    {
        $l_date_format = isys_application::instance()->container->locales->get_date_format($p_short, false);
        $l_time_format = '';
        if ($p_time_format) {
            $l_time_format = ' ' . isys_application::instance()->container->locales->get_time_format(false, false);
        }
        $l_format = $l_date_format . $l_time_format;

        return 'DATE_FORMAT(' . $p_field . ', \'' . $l_format . '\')';
    }

    /**
     * Monetary formatter for dynamic properties
     *
     * @param array      $data
     * @param string     $columnName
     * @param int|string $objectId
     *
     * @return mixed|string
     * @throws Exception
     */
    public function dynamicMonetaryFormatter($data, $columnName, $objectId)
    {
        $value = $data[$columnName];

        if (!empty($value) || !empty($objectId)) {
            if (!isset($data[$columnName])) {
                $l_dao = static::instance(isys_application::instance()->container->get('database'));
                $value = $l_dao->get_data(null, $objectId)
                    ->get_row_value($columnName);
            }

            // Decimal seperator from the user configuration.
            $monetaryValues = explode(" ", isys_application::instance()->container->get('locales')
                ->fmt_monetary($value));

            return $monetaryValues[0] . ' ' . $monetaryValues[1];
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Check whether category is called for an object in birth
     *
     * @return bool
     */
    public function isCreationInOverview()
    {
        return  isys_glob_get_param(C__CMDB__GET__CATLEVEL) == 0 &&
                isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW') &&
                isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__SAVE;
    }
}
