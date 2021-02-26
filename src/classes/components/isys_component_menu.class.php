<?php

use idoit\Component\Helper\Purify;

/**
 * i-doit
 *
 * Template library
 *
 * @package    i-doit
 * @subpackage Components
 * @version    Niclas Potthast <npotthast@i-doit.org> - 2005-10-26
 * @version    Van Quyen Hoang <qhoang@i-doit.org>    -    2012-09-14
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_menu extends isys_component
{
    /**
     * @var int
     */
    private static $m_activated_menu = 0;

    /**
     * Singleton instance.
     *
     * @var  isys_locale
     */
    private static $m_instance = null;

    /**
     * @var int
     */
    private $m_active_mainmenu = 0;

    /**
     * @var isys_component_menuobj[]
     */
    private $m_arr_mo_by_nr;

    /**
     * @var array
     */
    private $m_mainmenu = [];

    /**
     * @var
     */
    private $m_mo_nr_by_name;

    /**
     * @var array
     */
    private $m_objtype_group_menu = [];

    /**
     * @var array
     */
    private $m_other_menu = [];

    /**
     * Method for retrieving the singleton instance.
     *
     * @static
     * @return  isys_component_menu
     */
    public static function instance()
    {
        if (self::$m_instance === null) {
            self::$m_instance = new self;
        }

        return self::$m_instance;
    }

    /**
     * Gets the default menu constant
     *
     * @return mixed
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function get_default_menu_as_constant()
    {
        global $g_comp_database;

        $l_sql_condition = '';
        $l_obj_join = '';
        $l_gets = isys_module_request::get_instance()
            ->get_gets();

        $l_gets = Purify::castIntValues($l_gets);

        if (isset($l_gets[C__CMDB__GET__OBJECTGROUP])) {
            $l_sql_condition = 'AND isys_obj_type_group__id = \'' . (int)$g_comp_database->escape_string($l_gets[C__CMDB__GET__OBJECTGROUP]) . '\' ';
        } elseif (isset($l_gets[C__CMDB__GET__OBJECTTYPE])) {
            $l_allowed_objecttypes = isys_auth_cmdb_object_types::instance()
                ->get_allowed_objecttypes();

            if ($l_allowed_objecttypes === true || (is_array($l_allowed_objecttypes) && in_array($l_gets[C__CMDB__GET__OBJECTTYPE], $l_allowed_objecttypes))) {
                $l_sql_condition = 'AND isys_obj_type__id = \'' . (int)$g_comp_database->escape_string($l_gets[C__CMDB__GET__OBJECTTYPE]) . '\' ';
            } elseif ($l_allowed_objecttypes === false) {
                $l_sql_condition = 'AND isys_obj_type__id = FALSE ';
            }
        } elseif (isset($l_gets[C__CMDB__GET__OBJECT])) {
            $l_obj_join = 'INNER JOIN isys_obj ON isys_obj__isys_obj_type__id = isys_obj_type__id ';
            $l_sql_condition = 'AND isys_obj__id = \'' . (int)$g_comp_database->escape_string($l_gets[C__CMDB__GET__OBJECT]) . '\' ';
        } else {
            $l_allowed_objecttypes = isys_auth_cmdb_object_types::instance()
                ->get_allowed_objecttypes();

            if (is_array($l_allowed_objecttypes) && count($l_allowed_objecttypes) > 0) {
                $l_sql_condition = 'AND isys_obj_type__id IN (' . implode(',', $l_allowed_objecttypes) . ') ';
            } elseif ($l_allowed_objecttypes === false) {
                $l_sql_condition = 'AND isys_obj_type__id = FALSE ';
            }
        }

        $l_sql = 'SELECT DISTINCT(isys_obj_type_group__const) FROM isys_obj_type
			INNER JOIN isys_obj_type_group ON isys_obj_type_group__id = isys_obj_type__isys_obj_type_group__id ' . $l_obj_join . 'WHERE isys_obj_type_group__status = ' .
            ((int)C__RECORD_STATUS__NORMAL) . ' ' . $l_sql_condition . ' ORDER BY isys_obj_type_group__sort, isys_obj_type_group__const ASC LIMIT 0,1';

        $l_res = $g_comp_database->query($l_sql);
        $l_arr = $g_comp_database->fetch_row($l_res);

        if (is_array($l_arr) && !empty($l_arr)) {
            $l_objtype_group_const = array_shift($l_arr);

            self::$m_activated_menu = $l_objtype_group_const;

            $l_const = 'C__MAINMENU__' . str_replace('C__OBJTYPE_GROUP__', '', $l_objtype_group_const);

            return defined_or_default($l_const, false);
        }

        return false;
    }

    /**
     * @param integer $p_menu
     */
    public static function set_active_menu($p_menu)
    {
        self::$m_activated_menu = $p_menu;
    }

    /**
     * @return  integer
     */
    public static function get_default_mainmenu()
    {
        return self::$m_activated_menu;
    }

    /**
     * @param      $p_name
     * @param      $p_link
     * @param      $p_title
     * @param      $p_tooltip
     * @param null $p_onclick
     *
     * @return isys_component_menuobj
     */
    public function &new_menuobj($p_name, $p_link, $p_title, $p_tooltip, $p_onclick = null)
    {
        $l_mo = new isys_component_menuobj($p_name);

        $this->m_mo_nr_by_name[$l_mo->get_member('m_name')] = $l_mo->get_member('m_nr');
        $this->m_arr_mo_by_nr[$l_mo->get_member('m_nr')] = $l_mo;

        if (is_null($p_onclick)) {
            if (strstr($p_link, "?")) {
                $l_glue = "&";
            } else {
                $l_glue = "?";
            }

            $l_mo->set_member('m_link', $p_link . $l_glue . "mNavID=" . $l_mo->get_member('m_nr'));
        } else {
            $l_mo->set_member('m_link', $p_link);
        }

        $l_mo->set_member('m_lc_tooltip', $p_tooltip);
        $l_mo->set_member('m_lc_title', $p_title);
        $l_mo->set_member('m_rn_title', isys_application::instance()->container->get('language')
            ->get($p_title));
        $l_mo->set_member('m_onclick', $p_onclick);

        return $l_mo;
    }

    /**
     * @param $p_active_menu
     *
     * @return bool
     */
    public function activate_menuobj($p_active_menu)
    {
        // switch all MenuItems to selected = 0 // deselected
        // switch MenuItems to selected if found

        $this->m_active_mainmenu = $p_active_menu;

        return true;
    }

    /**
     * @return int
     */
    public function get_active_menuobj()
    {
        return $this->m_active_mainmenu;
    }

    /**
     * @deprecated
     */
    public function translate()
    {
    }

    /**
     * @return int
     */
    public function count_new_menuobj()
    {
        //  return count of exiting menuObjs
        return is_countable($this->m_arr_mo_by_nr) ? count($this->m_arr_mo_by_nr) : 0; //
    }

    /**
     * @param $p_nr
     *
     * @return isys_component_menuobj
     */
    public function get_menuobj_by_nr($p_nr)
    {
        //  return mo by given nr
        return $this->m_arr_mo_by_nr[$p_nr];
    }

    /**
     * Sets the menu array for object type groups
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_objtype_group_menu()
    {
        global $g_comp_session;

        $l_cache_obj = isys_caching::factory('auth-' . $g_comp_session->get_user_id());

        $l_cache = $l_cache_obj->get('objtype_group_mainmenu');

        if ($l_cache === false) {
            try {
                $l_allowed_objects_condition = isys_auth_cmdb_objects::instance()
                    ->get_allowed_objects_condition();

                $l_create_object_types = isys_auth_cmdb_object_types::instance()
                    ->get_allowed_objecttypes(isys_auth::CREATE);
                if (!$l_create_object_types) {
                    $l_create_object_types = [];
                }

                $l_view_object_types = isys_auth_cmdb_object_types::instance()
                    ->get_allowed_objecttypes(isys_auth::VIEW);
                if (!$l_view_object_types) {
                    $l_view_object_types = [];
                }

                // Merge createable and viewable object types
                $l_allowed_objecttypes = $l_create_object_types + $l_view_object_types;

                if (is_array($l_allowed_objecttypes) && count($l_allowed_objecttypes) > 0) {
                    $l_sql = 'SELECT DISTINCT(isys_obj_type_group__id), isys_obj_type_group__title, isys_obj_type_group__const FROM isys_obj_type
                    INNER JOIN isys_obj_type_group ON isys_obj_type__isys_obj_type_group__id = isys_obj_type_group__id
                    WHERE isys_obj_type_group__status = ' . C__RECORD_STATUS__NORMAL . ' AND isys_obj_type__id IN (' . implode(',', $l_allowed_objecttypes) . ')
                    AND isys_obj_type_group__id != ' . defined_or_default('C__OBJTYPE_GROUP__ORPHANED');
                } elseif (!is_bool($l_allowed_objects_condition)) {
                    $l_sql = 'SELECT DISTINCT(isys_obj_type_group__id), isys_obj_type_group__title, isys_obj_type_group__const FROM isys_obj
                      INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
                      INNER JOIN isys_obj_type_group ON isys_obj_type__isys_obj_type_group__id = isys_obj_type_group__id
                      WHERE TRUE ' . $l_allowed_objects_condition . ' AND isys_obj_type_group__id != ' . defined_or_default('C__OBJTYPE_GROUP__ORPHANED');
                } else {
                    // No rights to any object type group
                    $l_sql = 'SELECT isys_obj_type_group__id, isys_obj_type_group__title, isys_obj_type_group__const FROM isys_obj_type_group
                      WHERE ' . ($l_allowed_objects_condition === true ? 'TRUE' : 'FALSE') . ' AND isys_obj_type_group__id != ' . defined_or_default('C__OBJTYPE_GROUP__ORPHANED');
                }

                $l_sql .= ' ORDER BY isys_obj_type_group__sort ASC';

                $cmdbDao = isys_application::instance()->container->get('cmdb_dao');

                $l_res = $cmdbDao->retrieve($l_sql);

                while ($l_row = $l_res->get_row()) {
                    $l_constant = 'C__MAINMENU__' . str_replace('C__OBJTYPE_GROUP__', '', $l_row['isys_obj_type_group__const']);
                    $l_group_id = $l_row['isys_obj_type_group__id'] . '0';

                    if (!defined($l_constant)) {
                        define($l_constant, $l_group_id);
                    }

                    // Checking for existence of this object type and constant
                    if (defined($l_row['isys_obj_type_group__const'])) {
                        $this->m_objtype_group_menu[constant('C__MAINMENU__' . str_replace('C__OBJTYPE_GROUP__', '', $l_row['isys_obj_type_group__const']))] = [
                            'javascript:;',
                            $l_row['isys_obj_type_group__title'],
                            'get_tree_object_type(\'' . constant($l_row['isys_obj_type_group__const']) . '\', false)',
                            null,
                            'C__MAINMENU__' . str_replace('C__OBJTYPE_GROUP__', '', $l_row['isys_obj_type_group__const']),
                            $l_row['isys_obj_type_group__const']
                        ];
                    } else {
                        // debug message: Your object type $l_row['isys_obj_type_group__const'] does not exist. Clear your i-doit temp directory to fix this.
                    }
                }

                $l_cache_obj->set('objtype_group_mainmenu', $this->m_objtype_group_menu)
                    ->save();
            } catch (isys_exception_cache $e) {
                isys_notify::warning($e->getMessage());
            } catch (isys_exception_filesystem $e) {
                isys_notify::warning($e->getMessage());
            } catch (Exception $e) {
                isys_notify::error($e->getMessage());
            }
        } else {
            $this->m_objtype_group_menu = $l_cache;
            foreach ($l_cache as $l_key => $l_menu) {
                if (!isset($l_menu[4])) {
                    continue;
                }
                define($l_menu[4], $l_key);
            }
        }

        return $this;
    }

    /**
     * Gets the active menu constant by object type.
     *
     * @param integer $p_objtype
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_active_menu_by_objtype_as_constant($p_objtype)
    {
        global $g_comp_database;

        $l_objtype_group_const = '';

        $l_sql = 'SELECT isys_obj_type_group__const
			FROM isys_obj_type
			INNER JOIN isys_obj_type_group ON isys_obj_type_group__id = isys_obj_type__isys_obj_type_group__id
			WHERE isys_obj_type__id = ' . (int)$p_objtype . ' LIMIT 0,1;';

        $l_res = $g_comp_database->query($l_sql);

        if (is_array($l_arr = $g_comp_database->fetch_row($l_res))) {
            $l_objtype_group_const = array_shift($l_arr);
        }

        if (defined($l_objtype_group_const)) {
            $l_const = 'C__MAINMENU__' . str_replace('C__OBJTYPE_GROUP__', '', $l_objtype_group_const);

            if (defined($l_const)) {
                return constant($l_const);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Returns the object type group menus
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_objecttype_group_menu()
    {
        return $this->m_objtype_group_menu;
    }

    /**
     * Sets menu-items CMDB-Explorer and Extras
     *
     * @return $this
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_other_menu()
    {
        global $g_comp_session;

        try {
            $l_cache_obj = isys_caching::factory('auth-' . $g_comp_session->get_user_id());

            $l_cache = $l_cache_obj->get('other_mainmenu');

            if ($l_cache === false) {
                $l_menu_item = [];

                if (defined('C__CMDB__VIEW__EXPLORER') && isys_auth_cmdb::instance()
                        ->is_allowed_to(isys_auth::VIEW, 'EXPLORER')) {
                    // Creating the "CMDB-Explorer" button.
                    $l_menu_item[C__MAINMENU__CMDB_EXPLORER] = $this->m_other_menu[C__MAINMENU__CMDB_EXPLORER] = [
                        isys_helper_link::create_url([
                            C__CMDB__GET__VIEWMODE      => C__CMDB__VIEW__EXPLORER,
                            C__CMDB__VISUALIZATION_TYPE => C__CMDB__VISUALIZATION_TYPE__TREE,
                            C__CMDB__VISUALIZATION_VIEW => C__CMDB__VISUALIZATION_VIEW__OBJECT
                        ], true),
                        isys_application::instance()->container->get('language')
                            ->get('LC__MODULE__CMDB__VISUALIZATION'),
                        '',
                        'cmdb-explorer'
                    ];
                }

                // Fetch the modules.
                $l_modman = isys_module_request::get_instance()
                    ->get_module_manager();

                // Fetch the modules.
                $l_modules = $l_modman->get_modules();
                $l_has_extras_rights = false;
                // Iterate through the modules and display each.
                while ($l_module = $l_modules->get_row()) {
                    if (class_exists($l_module['isys_module__class'])) {
                        if (constant($l_module['isys_module__class'] . '::DISPLAY_IN_MAIN_MENU')) {
                            if (($l_auth_instance = isys_module_manager::instance()
                                    ->get_module_auth($l_module['isys_module__id'])) && $l_auth_instance->has_any_rights_in_module()) {
                                $l_has_extras_rights = true;
                                break;
                            }
                        }

                        if (method_exists($l_module['isys_module__class'], 'get_additional_links')) {
                            foreach (call_user_func([
                                $l_module['isys_module__class'],
                                'get_additional_links'
                            ]) as $l_key => $l_content) {
                                if ($l_key == 'RELATION') {
                                    if (isys_auth_cmdb::instance()
                                            ->is_allowed_to(isys_auth::VIEW, 'OBJ_IN_TYPE/C__OBJTYPE__RELATION') || isys_auth_cmdb::instance()
                                            ->is_allowed_to(isys_auth::VIEW, 'OBJ_IN_TYPE/C__OBJTYPE__PARALLEL_RELATION')) {
                                        $l_has_extras_rights = true;
                                        break;
                                    }
                                } else {
                                    if (($l_auth_instance = isys_module_manager::instance()
                                            ->get_module_auth($l_module['isys_module__id'])) && $l_auth_instance->is_allowed_to(isys_auth::VIEW, $l_key)) {
                                        $l_has_extras_rights = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

                if ($l_has_extras_rights) {
                    $l_menu_item[C__MAINMENU__EXTRAS] = $this->m_other_menu[C__MAINMENU__EXTRAS] = [
                        '#',
                        'LC__UNIVERSAL__EXTRAS',
                        null,
                        'extras'
                    ];
                }

                $l_cache_obj->set('other_mainmenu', $l_menu_item)
                    ->save();
            } else {
                $this->m_other_menu = $l_cache;
            }
        } catch (isys_exception_cache $e) {
            isys_notify::warning($e->getMessage());
        } catch (isys_exception_filesystem $e) {
            isys_notify::warning($e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * Gets menu-items CMDB-Explorer, Workflows and Extras
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_other_menu()
    {
        return $this->m_other_menu;
    }

    /**
     * Sets menu-item my-doit
     *
     * @return $this
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_my_doit()
    {
        if (defined('C__MAINMENU__MYDOIT')) {
            $this->m_mainmenu[C__MAINMENU__MYDOIT] = [
                'javascript:;',
                'LC__NAVIGATION__MAINMENU__TITLE_MY_DOIT',
                'mydoit_trigger();',
                null
            ];
        }

        return $this;
    }

    /**
     * Gets complete top menu bar
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_mainmenu()
    {
        return $this->m_mainmenu + $this->get_objecttype_group_menu() + $this->m_other_menu;
    }

    /**
     * isys_component_menu constructor.
     */
    public function __construct()
    {
        $this->set_my_doit();
        $this->set_objtype_group_menu();
        $this->set_other_menu();
    }
}

/**
 * Class isys_component_menuobj
 */
class isys_component_menuobj
{

    /**
     * @var
     */
    private $l_startnr_tab; // one array fits all -> set methode set/get

    /**
     * @var
     */
    private $l_vars; // Start Tabreihenfolge (hier CONSTANTE)

    /**
     * @var int
     */
    private $m_disabled;

    /**
     * @var string
     */
    private $m_lc_title;

    /**
     * @var string
     */
    private $m_lc_tooltip;

    /**
     * @var string
     */
    private $m_link;

    /**
     * @var null
     */
    private $m_menu_tree_type;

    /**
     * @var
     */
    private $m_name;

    /**
     * @var int
     */
    private $m_nr;

    /**
     * @var string
     */
    private $m_onclick;

    /**
     * @var string
     */
    private $m_rn_title;

    /**
     * @var string
     */
    private $m_rn_tooltip;

    /**
     * @var int
     */
    private $m_selected;

    /**
     * @var int
     */
    private $m_tab;

    /**
     * @param $p_key
     * @param $p_value
     *
     * @return bool
     */
    public function set($p_key, $p_value)
    {
        $this->l_vars[$p_key] = $p_value;

        return true;
    }

    /**
     * @param $p_key
     *
     * @return mixed
     */
    public function __get($p_key)
    {
        return $this->l_vars[$p_key];
    }

    /**
     * @param $p_key
     * @param $p_value
     *
     * @return bool
     */
    public function __set($p_key, $p_value)
    {
        $this->l_vars[$p_key] = $p_value;

        return true;
    }

    /**
     * @param $p_key
     *
     * @return mixed
     */
    public function get($p_key)
    {
        return $this->l_vars[$p_key];
    }

    /**
     * @param $p_key
     *
     * @return mixed
     */
    public function get_member($p_key)
    {
        return $this->$p_key;
    }

    /**
     * @param $p_key
     * @param $p_value
     */
    public function set_member($p_key, $p_value)
    {
        $this->$p_key = $p_value;
    }

    /**
     * @version Niclas Potthast <npotthast@i-doit.org> - 2005-10-26
     */
    public function __construct($p_name)
    {
        static $l_obj_counter = 0;
        static $l_obj_tab_counter = 0; // Start Tabreihenfolge (hier CONSTANTE)

        $this->m_name = $p_name;
        $this->m_nr = $l_obj_counter++;

        // set standard values
        $this->m_link = "#";
        $this->m_lc_tooltip = ""; // Language constant
        $this->m_rn_tooltip = ""; // Realname shows the tooltip (after translate menu)
        $this->m_lc_title = ""; // Language constant
        $this->m_rn_title = ""; // Realname shows the title (after translate menu)
        $this->m_tab = $l_obj_tab_counter++; // tabindex
        $this->m_onclick = "";
        $this->m_selected = 0; // 0 unselected 1 selected
        $this->m_disabled = 0; // 0 anabled 	1 disabled
        $this->m_menu_tree_type = null; // set the root_menu_tree which should be displayed
    }
}
