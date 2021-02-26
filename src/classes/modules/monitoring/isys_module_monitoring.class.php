<?php

/**
 * i-doit
 *
 * Monitoring module class.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.3.0
 */
class isys_module_monitoring extends isys_module implements isys_module_interface
{
    const DISPLAY_IN_MAIN_MENU = false;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = true;

    /**
     * @var bool
     */
    protected static $m_licenced = true;

    /**
     * Variable which holds the monitoring DAO class.
     *
     * @var  isys_monitoring_dao_hosts
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
     * Attach live status of object to contenttop header
     *
     * @param $p_catdata
     */
    public static function process_content_top($p_catdata)
    {
        global $g_comp_database;

        if (defined('C__MONITORING__TYPE_LIVESTATUS') && count(isys_monitoring_dao_hosts::instance($g_comp_database)->get_data(null, C__MONITORING__TYPE_LIVESTATUS))) {
            global $index_includes;

            if (!is_array($index_includes['contenttopobjectdetail'])) {
                $index_includes['contenttopobjectdetail'] = (array)$index_includes['contenttopobjectdetail'];
            }

            $index_includes['contenttopobjectdetail'][] = __DIR__ . '/templates/contenttop/main_objectdetail_livestatus.tpl';
        }
    }

    /**
     * Initializes the module.
     *
     * @param   isys_module_request & $p_req
     *
     * @return  isys_module_monitoring
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_db = $p_req->get_database();
        $this->m_tpl = $p_req->get_template();
        $this->m_dao = new isys_monitoring_dao_hosts($this->m_db);

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
        if (!defined('C__MODULE__MONITORING')) {
            return;
        }
        global $g_dirs;

        if ($p_system_module && defined('C__MODULE__SYSTEM')) {
            $p_tree->add_node(C__MODULE__MONITORING . 0, $p_parent, isys_application::instance()->container->get('language')
                ->get('LC__MONITORING__LIVESTATUS_NDO__CONFIGURATION'), isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID => C__MODULE__MONITORING,
                C__GET__SETTINGS_PAGE => 'livestatus_ndo_config',
                C__GET__TREE_NODE     => C__MODULE__MONITORING . 0
            ]), '', $g_dirs['images'] . 'icons/silk/cog_edit.png', (int)($_GET[C__GET__TREE_NODE] == C__MODULE__MONITORING . 0));

            $p_tree->add_node(C__MODULE__MONITORING . 1, $p_parent, isys_application::instance()->container->get('language')
                ->get('LC__MONITORING__EXPORT__CONFIGURATION'), isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID => C__MODULE__MONITORING,
                C__GET__SETTINGS_PAGE => 'export_config',
                C__GET__TREE_NODE     => C__MODULE__MONITORING . 1
            ]), '', $g_dirs['images'] . 'icons/silk/pencil.png', (int)($_GET[C__GET__TREE_NODE] == C__MODULE__MONITORING . 1));
        }
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

        return $g_config['www_dir'] . 'src/classes/modules/monitoring/templates/';
    }

    /**
     * Start method.
     */
    public function start()
    {
        $this->m_tpl->assign('tpl_www_dir', self::get_tpl_www_dir());

        switch ($_GET[C__GET__SETTINGS_PAGE]) {
            default:
            case 'livestatus_ndo_config':
                $this->process_livestatus_ndo_config();
                break;

            case 'export_config':
                $this->process_export_config();
                break;
        }
    }

    /**
     * Row modifier for the configuration list.
     *
     * @param   array $p_row
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function process_livestatus_ndo_config__row_modifier(&$p_row)
    {
        global $g_dirs;

        $p_row['isys_monitoring_hosts__active'] = ($p_row['isys_monitoring_hosts__active'] > 0) ? '<span class="text-green"><img src="' . $g_dirs['images'] .
            'icons/silk/bullet_green.png" class="vam mr5" />' . isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__YES') . '</span>' : '<span class="text-red"><img src="' . $g_dirs['images'] . 'icons/silk/bullet_red.png" class="vam mr5" />' .
            isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__NO') . '</span>';
    }

    /**
     * Method for processing the monitoring configuration pages.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_livestatus_ndo_config()
    {
        $l_id = $_POST['id'][0] ?: $_GET['id'];
        $l_navmode = $_POST[C__GET__NAVMODE] ?: $_GET[C__GET__NAVMODE];

        switch ($l_navmode) {
            case C__NAVMODE__DELETE:
                $this->process_livestatus_ndo_config__delete($_POST['id']);
                $this->process_livestatus_ndo_config__list();
                break;

            case C__NAVMODE__SAVE:
                $this->process_livestatus_ndo_config__save($_POST['config_id'], $_POST);
                $this->process_livestatus_ndo_config__list();
                break;

            case C__NAVMODE__EDIT:
                $this->process_livestatus_ndo_config__edit($l_id);
                break;

            case C__NAVMODE__NEW:
                $this->process_livestatus_ndo_config__edit();
                break;

            default:
                $this->process_livestatus_ndo_config__list();
        }
    }

    /**
     * Delete-action for removing the selected configuration from the database.
     *
     * @param   mixed $p_config_id
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_livestatus_ndo_config__delete($p_config_id)
    {
        $this->m_dao->delete($p_config_id);
    }

    /**
     * Save-action for writing the configuration to the database. This is used for "CREATE" and "UPDATE".
     *
     * @param   integer $p_config_id
     * @param   array   $p_configuration
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_livestatus_ndo_config__save($p_config_id = null, array $p_configuration = [])
    {
        $l_config = [
            'title'      => $p_configuration['C__MONITORING__CONFIG__TITLE'],
            'active'     => $p_configuration['C__MONITORING__CONFIG__ACTIVE'],
            'type'       => $p_configuration['C__MONITORING__CONFIG__TYPE'],
            'connection' => $p_configuration['C__MONITORING__CONFIG__CONNECTION'],
            'path'       => $p_configuration['C__MONITORING__CONFIG__PATH'],
            'address'    => $p_configuration['C__MONITORING__CONFIG__ADDRESS'],
            'port'       => $p_configuration['C__MONITORING__CONFIG__PORT'],
            'dbname'     => $p_configuration['C__MONITORING__CONFIG__DBNAME'],
            'dbprefix'   => $p_configuration['C__MONITORING__CONFIG__DBPREFIX'],
            'username'   => $p_configuration['C__MONITORING__CONFIG__USERNAME'],
            'password'   => isys_helper_crypt::encrypt($p_configuration['C__MONITORING__CONFIG__PASSWORD'])
        ];

        if ($p_configuration['C__MONITORING__CONFIG__PASSWORD__action'] == isys_smarty_plugin_f_password::PASSWORD_UNCHANGED) {
            unset($l_config['password']);
        }

        $this->m_dao->save($p_config_id, $l_config);
    }

    /**
     * Edit-action for displaying the configuration form. This is used for "NEW" and "EDIT".
     *
     * @param   integer $p_config_id
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_livestatus_ndo_config__edit($p_config_id = null)
    {
        $l_yes_no = get_smarty_arr_YES_NO();
        $l_livestatus_connections = [
            C__MONITORING__LIVESTATUS_TYPE__TCP  => 'TCP',
            C__MONITORING__LIVESTATUS_TYPE__UNIX => 'UNIX Socket'
        ];
        $l_monitoring_type = [
            C__MONITORING__TYPE_LIVESTATUS => 'Livestatus',
            C__MONITORING__TYPE_NDO        => 'NDO',
        ];

        $l_rules = [
            'C__MONITORING__CONFIG__TITLE'      => [],
            'C__MONITORING__CONFIG__ACTIVE'     => [
                'p_strSelectedID' => 1,
                'p_arData'        => $l_yes_no
            ],
            'C__MONITORING__CONFIG__TYPE'       => [
                'p_strSelectedID' => C__MONITORING__TYPE_LIVESTATUS,
                'p_arData'        => $l_monitoring_type
            ],
            'C__MONITORING__CONFIG__CONNECTION' => [
                'p_strSelectedID' => C__MONITORING__LIVESTATUS_TYPE__TCP,
                'p_arData'        => $l_livestatus_connections,
                'p_strClass'      => 'input-small'
            ],
            'C__MONITORING__CONFIG__ADDRESS'    => [
                'p_strClass' => 'input-small'
            ],
            'C__MONITORING__CONFIG__PORT'       => [
                'p_strClass' => 'input-small'
            ],
            'C__MONITORING__CONFIG__PATH'       => [
                'p_strClass' => 'input-small'
            ],
            'C__MONITORING__CONFIG__DBNAME'     => [
                'p_strClass' => 'input-small'
            ],
            'C__MONITORING__CONFIG__DBPREFIX'   => [
                'p_strClass' => 'input-small'
            ],
            'C__MONITORING__CONFIG__USERNAME'   => [
                'p_strClass' => 'input-small'
            ],
            'C__MONITORING__CONFIG__PASSWORD'   => [
                'p_strClass' => 'input-small'
            ],
        ];

        if ($p_config_id !== null && $p_config_id > 0) {
            $l_host = isys_monitoring_dao_hosts::instance($this->m_db)
                ->get_data($p_config_id)
                ->get_row();

            $l_rules['config_id']['p_strValue'] = $p_config_id;
            $l_rules['C__MONITORING__CONFIG__TITLE']['p_strValue'] = $l_host['isys_monitoring_hosts__title'];
            $l_rules['C__MONITORING__CONFIG__ACTIVE']['p_strSelectedID'] = (isset($l_host['isys_monitoring_hosts__active']) ? $l_host['isys_monitoring_hosts__active'] : 1);
            $l_rules['C__MONITORING__CONFIG__TYPE']['p_strSelectedID'] = $l_host['isys_monitoring_hosts__type'] ?: C__MONITORING__TYPE_LIVESTATUS;
            $l_rules['C__MONITORING__CONFIG__CONNECTION']['p_strSelectedID'] = $l_host['isys_monitoring_hosts__connection'] ?: C__MONITORING__LIVESTATUS_TYPE__TCP;
            $l_rules['C__MONITORING__CONFIG__ADDRESS']['p_strValue'] = $l_host['isys_monitoring_hosts__address'];
            $l_rules['C__MONITORING__CONFIG__PORT']['p_strValue'] = $l_host['isys_monitoring_hosts__port'];
            $l_rules['C__MONITORING__CONFIG__PATH']['p_strValue'] = $l_host['isys_monitoring_hosts__path'];
            $l_rules['C__MONITORING__CONFIG__DBNAME']['p_strValue'] = $l_host['isys_monitoring_hosts__dbname'];
            $l_rules['C__MONITORING__CONFIG__DBPREFIX']['p_strValue'] = $l_host['isys_monitoring_hosts__dbprefix'];
            $l_rules['C__MONITORING__CONFIG__USERNAME']['p_strValue'] = $l_host['isys_monitoring_hosts__username'];
            $l_rules['C__MONITORING__CONFIG__PASSWORD']['p_strValue'] = isys_helper_crypt::decrypt($l_host['isys_monitoring_hosts__password']);
        }

        isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__SAVE)
            ->set_active(true, C__NAVBAR_BUTTON__CANCEL);

        $this->m_tpl->activate_editmode()
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules)
            ->include_template('contentbottomcontent', self::get_tpl_dir() . 'livestatus_ndo_config_form.tpl');
    }

    /**
     * List-action for displaying the Livestatus / NDO configurations.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_livestatus_ndo_config__list()
    {
        global $index_includes;

        $l_url_params = $_GET;
        $l_url_params[C__GET__NAVMODE] = C__NAVMODE__EDIT;
        unset($l_url_params[C__GET__MAIN_MENU__NAVIGATION_ID], $l_url_params['id']);

        $l_list_headers = [
            'isys_monitoring_hosts__id'     => 'ID',
            'isys_monitoring_hosts__active' => 'aktiv',
            'isys_monitoring_hosts__title'  => 'LC__CMDB__CATG__UI_TITLE',
            'isys_monitoring_hosts__type'   => 'LC__MONITORING__TYPE'
        ];

        $l_nagios_hosts_result = $this->m_dao->get_data();
        $l_nagios_hosts_count = count($l_nagios_hosts_result);

        $l_list = isys_factory::get_instance('isys_component_list')
            ->set_data(null, $l_nagios_hosts_result)
            ->set_row_modifier($this, 'process_livestatus_ndo_config__row_modifier')
            ->config($l_list_headers, isys_helper_link::create_url($l_url_params) . '&id=[{isys_monitoring_hosts__id}]', '[{isys_monitoring_hosts__id}]');

        if ($l_list->createTempTable()) {
            $this->m_tpl->assign('configuration_table', $l_list->getTempTableHtml());
        }

        $this->m_tpl->assign('content_title', isys_application::instance()->container->get('language')
            ->get('LC__MONITORING'));

        isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__NEW)
            ->set_active(($l_nagios_hosts_count > 0), C__NAVBAR_BUTTON__EDIT)
            ->set_active(($l_nagios_hosts_count > 0), C__NAVBAR_BUTTON__DELETE)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(true, C__NAVBAR_BUTTON__DELETE);

        $index_includes['contentbottomcontent'] = self::get_tpl_dir() . 'livestatus_ndo_config_list.tpl';
    }

    /**
     * Method for processing the Monitoring export configuration pages.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_export_config()
    {
        $l_id = $_POST['id'][0] ?: $_GET['id'];
        $l_navmode = $_POST[C__GET__NAVMODE] ?: $_GET[C__GET__NAVMODE];

        switch ($l_navmode) {
            case C__NAVMODE__DELETE:
                $this->process_export_config__delete($_POST['id']);
                $this->process_export_config__list();
                break;

            case C__NAVMODE__SAVE:
                $this->process_export_config__save($_POST['config_id'], $_POST);
                $this->process_export_config__list();
                break;

            case C__NAVMODE__EDIT:
                $this->process_export_config__edit($l_id);
                break;

            case C__NAVMODE__NEW:
                $this->process_export_config__edit();
                break;

            default:
                $this->process_export_config__list();
        }
    }

    /**
     * Delete-action for removing the selected export configuration from the database.
     *
     * @param   mixed $p_config_id
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_export_config__delete($p_config_id)
    {
        $this->m_dao->delete_export_config($p_config_id);
    }

    /**
     * Save-action for writing the export configuration to the database. This is used for "CREATE" and "UPDATE".
     *
     * @param   integer $p_config_id
     * @param   array   $p_configuration
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_export_config__save($p_config_id = null, array $p_configuration = [])
    {
        $l_options = [];

        switch ($p_configuration['C__MONITORING__MONITORING_TYPE']) {
            case 'check_mk':
                // @see ID-6237  We can no longer rely on check_mk specific code.
                $p_configuration['C__MONITORING__CHECK_MK__SITE'] = trim(preg_replace('~[^a-z0-9|_-]+~i', '_', isys_glob_replace_accent(trim($p_configuration['C__MONITORING__CHECK_MK__SITE']))), '_');

                $l_options['site'] = $p_configuration['C__MONITORING__CHECK_MK__SITE'];
                $l_options['multisite'] = (bool)$p_configuration['C__MONITORING__CHECK_MK__MULTISITE'];
                $l_options['lock_hosts'] = (bool)$p_configuration['C__MONITORING__CHECK_MK__LOCK_HOSTS'];
                $l_options['lock_folders'] = (bool)$p_configuration['C__MONITORING__CHECK_MK__LOCK_FOLDERS'];
                $l_options['master'] = (int)$p_configuration['C__MONITORING__CHECK_MK__MASTER_SITE'];
                $l_options['roles'] = $p_configuration['C__MONITORING__CHECK_MK__ROLE_EXPORT__selected_box'];
                $l_options['utf8decode'] = (bool)$p_configuration['C__MONITORING__CHECK_MK__UTF8DECODE_EXPORT'];
                break;

            default:
        }

        $this->m_dao->save_export_config($p_config_id, [
            'title'   => $p_configuration['C__MONITORING__CONFIG__TITLE'],
            'path'    => $p_configuration['C__MONITORING__CONFIG__PATH'] ?: '',
            'address' => $p_configuration['C__MONITORING__CONFIG__ADDRESS'] ?: '',
            'type'    => $p_configuration['C__MONITORING__MONITORING_TYPE'],
            'options' => isys_format_json::encode($l_options)
        ]);
    }

    /**
     * Edit-action for displaying the export configuration form. This is used for "NEW" and "EDIT".
     *
     * @param   integer $p_config_id
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_export_config__edit($p_config_id = null)
    {
        global $g_absdir;

        $l_sites = $l_config_childs = $l_contact_roles = [];

        $l_res = $this->m_dao->get_export_data();

        if (count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                $l_config = ['master' => null];

                if (isys_format_json::is_json_array($l_row['isys_monitoring_export_config__options'])) {
                    $l_config = isys_format_json::decode($l_row['isys_monitoring_export_config__options']);
                }

                if ($l_row['isys_monitoring_export_config__id'] != $p_config_id && $l_row['isys_monitoring_export_config__type'] == 'check_mk' && !($l_config['master'] > 0)) {
                    $l_sites[$l_row['isys_monitoring_export_config__id']] = $l_row['isys_monitoring_export_config__title'];
                }
            }
        }

        // Retrieve all contact roles.
        $l_roles = isys_factory_cmdb_dialog_dao::get_instance('isys_contact_tag', $this->m_db)
            ->get_data();

        if (is_array($l_roles) && count($l_roles)) {
            foreach ($l_roles as $l_role) {
                $l_contact_roles[$l_role['isys_contact_tag__id']] = [
                    'id'  => $l_role['isys_contact_tag__id'],
                    'val' => $l_role['title'],
                    'sel' => false
                ];
            }
        }

        $l_rules = [
            'config_id'                                  => [],
            'C__MONITORING__CONFIG__TITLE'               => [
                'p_strValue' => ''
            ],
            'C__MONITORING__CONFIG__ADDRESS'             => [
                'p_strPlaceholder' => 'http://internal-monitoring-host.int/',
                'p_strValue'       => ''
            ],
            'C__MONITORING__CONFIG__PATH'                => [
                'p_strPlaceholder' => $g_absdir,
                'p_strValue'       => ''
            ],
            'C__MONITORING__MONITORING_TYPE'             => [
                'p_strClass'   => 'input-mini',
                'p_bDbFieldNN' => true,
                'p_arData'     => [
                    'check_mk' => 'Check_MK',
                    'nagios'   => 'Nagios',
                ]
            ],
            'C__MONITORING__CHECK_MK__ROLE_EXPORT'       => [
                'p_arData' => []
            ],
            'C__MONITORING__CHECK_MK__SITE'              => [
                'p_strClass' => 'input-small'
            ],
            'C__MONITORING__CHECK_MK__MULTISITE'         => [
                'p_strClass'      => 'input-mini',
                'p_bDbFieldNN'    => true,
                'p_arData'        => get_smarty_arr_YES_NO(),
                'p_strSelectedID' => 0,
                'description'     => '(' . isys_application::instance()->container->get('language')
                        ->get('LC__MONITORING__CHECK_MK__MULTISITE_INFO') . ')'
            ],
            'C__MONITORING__CHECK_MK__LOCK_HOSTS'        => [
                'p_strClass'      => 'input-mini',
                'p_bDbFieldNN'    => true,
                'p_arData'        => get_smarty_arr_YES_NO(),
                'p_strSelectedID' => 1
            ],
            'C__MONITORING__CHECK_MK__LOCK_FOLDERS'      => [
                'p_strClass'      => 'input-mini',
                'p_bDbFieldNN'    => true,
                'p_arData'        => get_smarty_arr_YES_NO(),
                'p_strSelectedID' => 1
            ],
            'C__MONITORING__CHECK_MK__MASTER_SITE'       => [
                'p_strClass'      => 'input-small',
                'p_arData'        => $l_sites,
                'p_strSelectedID' => -1
            ],
            'C__MONITORING__CHECK_MK__UTF8DECODE_EXPORT' => [
                'p_strClass'      => 'input-mini',
                'p_bDbFieldNN'    => true,
                'p_arData'        => get_smarty_arr_YES_NO(),
                'p_strSelectedID' => 1
            ]
        ];

        if ($p_config_id !== null && $p_config_id > 0) {
            $l_host = isys_monitoring_dao_hosts::instance($this->m_db)
                ->get_export_data($p_config_id)
                ->get_row();
            $l_config_childs = $this->m_dao->get_child_configurations($p_config_id);

            $l_options = isys_format_json::decode($l_host['isys_monitoring_export_config__options']);

            $l_rules['config_id']['p_strValue'] = $p_config_id;
            $l_rules['C__MONITORING__CONFIG__TITLE']['p_strValue'] = $l_host['isys_monitoring_export_config__title'];
            $l_rules['C__MONITORING__CONFIG__ADDRESS']['p_strValue'] = $l_host['isys_monitoring_export_config__address'];
            $l_rules['C__MONITORING__CONFIG__PATH']['p_strValue'] = $l_host['isys_monitoring_export_config__path'];
            $l_rules['C__MONITORING__MONITORING_TYPE']['p_strSelectedID'] = $l_host['isys_monitoring_export_config__type'];
            $l_rules['C__MONITORING__CHECK_MK__SITE']['p_strValue'] = $l_options['site'];
            $l_rules['C__MONITORING__CHECK_MK__MULTISITE']['p_strSelectedID'] = (int)$l_options['multisite'];
            $l_rules['C__MONITORING__CHECK_MK__LOCK_HOSTS']['p_strSelectedID'] = (int)$l_options['lock_hosts'];
            $l_rules['C__MONITORING__CHECK_MK__LOCK_FOLDERS']['p_strSelectedID'] = (int)$l_options['lock_folders'];
            $l_rules['C__MONITORING__CHECK_MK__MASTER_SITE']['p_strSelectedID'] = $l_options['master'];
            $l_rules['C__MONITORING__CHECK_MK__UTF8DECODE_EXPORT']['p_strSelectedID'] = (int)(isset($l_options['utf8decode']) ? $l_options['utf8decode'] : 1);

            if (is_array($l_options['roles']) && count($l_options['roles'])) {
                foreach ($l_options['roles'] as $l_role) {
                    if (isset($l_contact_roles[$l_role])) {
                        $l_contact_roles[$l_role]['sel'] = true;
                    }
                }
            }

            if (count($l_config_childs)) {
                $l_rules['C__MONITORING__CHECK_MK__MASTER_SITE']['p_bDisabled'] = true;
            }
        }

        $l_rules['C__MONITORING__CHECK_MK__ROLE_EXPORT']['p_arData'] = $l_contact_roles;

        $this->m_tpl->activate_editmode()
            ->assign('config_childs', $l_config_childs)
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules)
            ->include_template('contentbottomcontent', self::get_tpl_dir() . 'export_config_form.tpl');

        isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__SAVE)
            ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
    }

    /**
     * List-action for displaying the export configurations.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_export_config__list()
    {
        global $index_includes;

        $l_url_params = $_GET;
        unset($l_url_params[C__GET__MAIN_MENU__NAVIGATION_ID], $l_url_params['id']);
        $l_url_params[C__GET__NAVMODE] = C__NAVMODE__EDIT;
        $l_url_params[C__GET__ID] = '[{isys_monitoring_export_config__id}]';

        $l_list_headers = [
            'isys_monitoring_export_config__id'      => 'ID',
            'isys_monitoring_export_config__type'    => 'LC__MONITORING__MONITORING_TYPE',
            'isys_monitoring_export_config__title'   => 'LC__CMDB__CATG__UI_TITLE',
            'isys_monitoring_export_config__path'    => 'LC__MONITORING__PATH',
            'isys_monitoring_export_config__address' => 'LC__MONITORING__ADDRESS'
        ];

        $l_nagios_hosts_result = $this->m_dao->get_export_data();
        $l_nagios_hosts_count = count($l_nagios_hosts_result);

        $l_list = isys_factory::get_instance('isys_component_list')
            ->set_data(null, $l_nagios_hosts_result)
            ->config($l_list_headers, isys_helper_link::create_url($l_url_params), '[{isys_monitoring_export_config__id}]');

        if ($l_list->createTempTable()) {
            $this->m_tpl->assign('configuration_table', $l_list->getTempTableHtml());
        }

        isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__NEW)
            ->set_active(($l_nagios_hosts_count > 0), C__NAVBAR_BUTTON__EDIT)
            ->set_active(($l_nagios_hosts_count > 0), C__NAVBAR_BUTTON__DELETE)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(true, C__NAVBAR_BUTTON__DELETE);

        $this->m_tpl->assign('content_title', isys_application::instance()->container->get('language')
            ->get('LC__MONITORING'));

        $index_includes['contentbottomcontent'] = self::get_tpl_dir() . 'export_config_list.tpl';
    }
}
