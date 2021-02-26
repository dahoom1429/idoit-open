<?php

use idoit\AddOn\AuthableInterface;

/**
 * i-doit
 *
 * Dashboard module class for displaying widgets.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.2.0
 */
class isys_module_dashboard extends isys_module implements AuthableInterface
{
    const DISPLAY_IN_MAIN_MENU = false;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = false;

    /**
     * Variable which holds the singleton instance.
     *
     * @var  isys_module_dashboard
     */
    protected static $m_instance = null;

    /**
     * @var bool
     */
    protected static $m_licenced = true;

    /**
     * Variable which holds the dashboard DAO class.
     *
     * @var  isys_dashboard_dao
     */
    protected $m_dao = null;

    /**
     * Variable which holds the database component.
     *
     * @var  isys_component_database
     */
    protected $m_db = null;

    /**
     * Variable which holds the template component.
     *
     * @var  isys_component_template
     */
    protected $m_tpl = null;

    /**
     * Variable which holds the user specific widgets (in correct order etc.).
     *
     * @var  array
     */
    protected $m_widgets = [];

    /**
     * Singleton "instance" method.
     *
     * @static
     * @return  isys_module_dashboard
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function instance()
    {
        if (self::$m_instance === null) {
            self::$m_instance = new self;
        }

        return self::$m_instance;
    }

    /**
     * Static method for retrieving the path, to the modules templates.
     *
     * @static
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_tpl_dir()
    {
        return __DIR__ . '/templates/';
    }

    /**
     * Return main template location
     *
     * @return string
     */
    public static function get_template()
    {
        return self::get_tpl_dir() . 'main.tpl';
    }

    /**
     * Initializes the module.
     *
     * @param   isys_module_request & $p_req
     *
     * @return  isys_module_dashboard
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_db = $p_req->get_database();
        $this->m_tpl = $p_req->get_template();
        $this->m_dao = new isys_dashboard_dao($this->m_db);

        $this->m_tpl->assign('www_dir', self::get_tpl_www_dir())
            ->assign('widget_ajax_url', isys_helper_link::create_url([C__GET__AJAX_CALL => 'dashboard', C__GET__AJAX => 1]));

        return $this;
    }

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @see     isys_module_cmdb->build_tree();
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        (new isys_module_cmdb)->build_tree($p_tree, $p_system_module, $p_parent);
    }

    /**
     * Static method for retrieving the path, to the modules templates.
     *
     * @static
     * @global  array $g_dirs
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_tpl_www_dir()
    {
        global $g_config;

        return $g_config['www_dir'] . 'src/classes/modules/dashboard/templates/';
    }

    /**
     * Empty "start" method.
     */
    public function start()
    {
        ;
    }

    /**
     * Load view and assign it's content to smarty variable 'dashboard'
     */
    public function process_view()
    {
        $template = isys_application::instance()->template;

        $template->assign('dashboard', $template->fetch(self::get_tpl_dir() . 'main.tpl'))
            ->include_template('contentarea', 'content/top/main_dashboard.tpl');
    }

    /**
     * Add a widget to the stack
     *
     * @param array  $p_data
     * @param string $p_side
     *
     * @return $this
     */
    public function add_widget(array $p_data, $p_side = 'left')
    {
        $this->m_widgets[$p_side][] = $p_data;

        return $this;
    }

    /**
     * @return array
     */
    public function get_widgets()
    {
        return $this->m_widgets;
    }

    /**
     * @param array $p_widgets
     *
     * @return $this
     */
    public function set_widgets(array $p_widgets)
    {
        $this->m_widgets = $p_widgets;

        return $this;
    }

    /**
     * Method for loading the user specific dashboard widgets.
     *
     * @param   integer $p_user_id
     *
     * @return  isys_module_dashboard
     */
    public function load_user_dashboard($p_user_id = null)
    {
        if ($p_user_id === null) {
            global $g_comp_session;

            $p_user_id = $g_comp_session->get_user_id();
        }

        $l_default = false;
        $l_counter = [];
        $l_res = $this->m_dao->get_widgets_by_user($p_user_id);

        // If the user has defined no widgets, we display the defaults.
        if ($l_res->num_rows() === 0) {
            $l_default = true;
            $l_res = $this->m_dao->get_widgets_by_default();
        }

        if ($l_res->num_rows() > 0) {
            $l_cnt = 1;

            while ($l_row = $l_res->get_row()) {
                if (array_key_exists($l_row['isys_widgets__identifier'], $l_counter)) {
                    $l_counter[$l_row['isys_widgets__identifier']]++;
                } else {
                    $l_counter[$l_row['isys_widgets__identifier']] = 1;
                }

                $l_classname = 'isys_dashboard_widgets_' . $l_row['isys_widgets__identifier'];

                if (!class_exists($l_classname)) {
                    $l_classname = isys_register::factory('widget-register')
                        ->get($l_row['isys_widgets__identifier']);
                }

                if (class_exists($l_classname)) {
                    $l_configurable = isys_dashboard_widgets::factory($l_classname)
                        ->has_configuration();
                } else {
                    continue;
                }

                $this->add_widget([
                    'id'           => $l_row['isys_widgets_config__id'],
                    'title'        => isys_application::instance()->container->get('language')
                        ->get($l_row['isys_widgets__title']),
                    'unique_id'    => $l_row['isys_widgets__identifier'] . '_' . $l_counter[$l_row['isys_widgets__identifier']],
                    'identifier'   => $l_row['isys_widgets__identifier'],
                    'configurable' => (int)$l_configurable,
                    'removable'    => 1,
                    'base64'       => base64_encode(isys_format_json::encode([
                        'identifier' => $l_row['isys_widgets__identifier'],
                        'id'         => $l_row['isys_widgets_config__id']
                    ]))
                ], $l_cnt % 2 ? 'left' : 'right');

                $l_cnt++;
            }
        }

        isys_component_signalcollection::get_instance()
            ->emit('mod.dashboard.afterInitialize', $this);

        $this->m_tpl->assign('is_allowed_to_configure_dashboard', isys_auth_dashboard::instance()
            ->is_allowed_to(isys_auth::EXECUTE, 'CONFIGURE_DASHBOARD'))
            ->assign('is_allowed_to_configure_widgets', isys_auth_dashboard::instance()
                ->is_allowed_to(isys_auth::EXECUTE, 'CONFIGURE_WIDGETS'))
            ->assign('default_dashboard', (int)$l_default)
            ->assign('dashboard_js', self::get_tpl_dir() . 'dashboard.js')
            ->assign('css_path', self::get_tpl_dir() . 'style.css')
            ->assign('widgets', $this->m_widgets);

        return $this;
    }

    /**
     * @return isys_auth_dashboard
     */
    public static function getAuth()
    {
        return isys_auth_dashboard::instance();
    }
}
