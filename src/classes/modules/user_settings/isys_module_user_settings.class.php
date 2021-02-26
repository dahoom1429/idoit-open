<?php

/**
 * i-doit
 *
 * User settings.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_user_settings extends isys_module implements isys_module_interface
{
    const DISPLAY_IN_MAIN_MENU = false;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = true;

    /**
     * @var bool
     */
    protected static $m_licenced = true;

    private $m_pageinfo = [
        C__SETTINGS_PAGE__USER   => [
            'title'    => 'LC__SETTINGS__SYSTEM__TITLE',
            'method'   => 'user',
            'template' => 'content/bottom/content/module__settings__user.tpl'
        ],
        C__SETTINGS_PAGE__SYSTEM => [
            'title'    => 'LC__CMDB__TREE__SYSTEM__SETTINGS__USER__SYSTEM_SETTINGS',
            'method'   => 'system',
            'template' => 'modules/system_settings/index.tpl'
        ]
    ];

    /**
     * @param   isys_module_request $p_req
     *
     * @return  boolean
     */
    public function init(isys_module_request $p_req)
    {
        return true;
    }

    /**
     * Method for retrieving the breadcrumb part.
     *
     * @param   array $p_gets
     *
     * @return  array
     */
    public function breadcrumb_get(&$p_gets)
    {
        return [
            [
                isys_application::instance()->container->get('language')
                    ->get($this->m_pageinfo[$_GET[C__GET__SETTINGS_PAGE]]['title']) => null
            ]
        ];
    }

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @since   0.9.9-7
     * @see     isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        if (!defined('C__MODULE__USER_SETTINGS')) {
            return;
        }
        global $g_dirs;

        $l_objUser = isys_component_dao_user::instance(isys_application::instance()->database);

        $l_nUserID = $l_objUser->get_current_user_id();
        $l_strUserTitle = $l_objUser->get_user_title($l_nUserID);

        $l_gets = isys_module_request::get_instance()
            ->get_gets();

        $l_mod_gets = $l_gets;

        unset($l_mod_gets['what']);

        $l_parent = -1;
        $i = 0;

        if ($p_system_module) {
            $l_parent = $p_tree->find_id_by_title('Modules');
            $l_mod_gets[C__GET__MODULE_SUB_ID] = C__MODULE__USER_SETTINGS;
        }

        if (null !== $p_parent && is_int($p_parent)) {
            $l_root = $p_parent;
        } else {
            $l_root = $p_tree->add_node(C__MODULE__USER_SETTINGS . $i, $l_parent, $l_strUserTitle);
        }

        $l_mod_gets[C__GET__TREE_NODE] = C__MODULE__USER_SETTINGS . (++$i);
        $l_mod_gets[C__GET__SETTINGS_PAGE] = C__SETTINGS_PAGE__USER;
        $p_tree->add_node(
            C__MODULE__USER_SETTINGS . $i,
            $l_root,
            isys_application::instance()->container->get('language')
            ->get("LC__CMDB__TREE__SYSTEM__SETTINGS__USER__PRESENTATION"),
            isys_helper_link::create_url($l_mod_gets),
            null,
            $g_dirs["images"] . "icons/silk/application_form_edit.png",
            (int)($_GET[C__GET__TREE_NODE] == C__MODULE__USER_SETTINGS . $i)
        );

        if (defined('C__MODULE__SYSTEM')) {
            if (defined('C__MODULE__CMDB')) {
                if (isys_auth_cmdb::instance()
                    ->is_allowed_to(isys_auth::EXECUTE, 'list_config')) {
                    $p_tree->add_node(C__MODULE__USER_SETTINGS . (++$i), $l_root, isys_application::instance()->container->get('language')
                        ->get('LC__CMDB__TREE__SYSTEM__OBJECT_LIST'), isys_helper_link::create_url([
                        C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                        C__GET__MODULE_SUB_ID => C__MODULE__CMDB,
                        C__GET__TREE_NODE     => C__MODULE__USER_SETTINGS . $i,
                        C__GET__SETTINGS_PAGE => 'list'
                    ]), null, $g_dirs["images"] . 'icons/silk/table_edit.png', (int)($_GET[C__GET__TREE_NODE] == C__MODULE__USER_SETTINGS . $i));
                }
                if (isys_auth_cmdb::instance()
                    ->is_allowed_to(isys_auth::EXECUTE, 'multilist_config')) {
                    $p_tree->add_node(C__MODULE__USER_SETTINGS . (++$i), $l_root, isys_application::instance()->container->get('language')
                        ->get('LC__CMDB__TREE__SYSTEM__MULTIVALUE_LIST'), isys_helper_link::create_url([
                        C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                        C__GET__MODULE_SUB_ID => C__MODULE__CMDB,
                        C__GET__TREE_NODE     => C__MODULE__USER_SETTINGS . $i,
                        C__GET__SETTINGS_PAGE => 'catlist'
                    ]), null, $g_dirs["images"] . 'icons/silk/table_edit.png', (int)($_GET[C__GET__TREE_NODE] == C__MODULE__USER_SETTINGS . $i));
                }
            }

            $p_tree->add_node(C__MODULE__USER_SETTINGS . (++$i), $l_root, isys_application::instance()->container->get('language')
                ->get('LC__LOGIN__SETTINGS'), isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID => C__MODULE__USER_SETTINGS,
                C__GET__TREE_NODE     => C__MODULE__USER_SETTINGS . $i,
                C__GET__SETTINGS_PAGE => 'login'
            ]), null, $g_dirs["images"] . 'icons/silk/key.png', (int)($_GET[C__GET__TREE_NODE] == C__MODULE__USER_SETTINGS . $i));

            $p_tree->add_node(C__MODULE__USER_SETTINGS . (++$i), $l_root, isys_application::instance()->container->get('language')
                ->get('LC__CMDB__TREE__SYSTEM__SETTINGS__USER__SYSTEM_SETTINGS'), isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID => C__MODULE__USER_SETTINGS,
                C__GET__TREE_NODE     => C__MODULE__USER_SETTINGS . $i,
                C__GET__SETTINGS_PAGE => C__SETTINGS_PAGE__SYSTEM
            ]), null, $g_dirs["images"] . 'icons/silk/outline.png', (int)($_GET[C__GET__TREE_NODE] == C__MODULE__USER_SETTINGS . $i));
        }
    }

    /**
     * User specific settings.
     *
     * @author Dennis Stücken <dstuecken@synetics.de>
     */
    public function start()
    {
        $l_tplclass = isys_application::instance()->container->get('template');
        $l_navbar = isys_component_template_navbar::getInstance();

        if (empty($_GET[C__GET__SETTINGS_PAGE])) {
            return false;
        }

        if ($_GET[C__GET__MODULE_ID] != defined_or_default('C__MODULE__SYSTEM')) {
            $l_tree = isys_module_request::get_instance()
                ->get_menutree();
            $this->build_tree($l_tree, false);
            $l_tplclass->assign("menu_tree", $l_tree->process($_GET[C__GET__TREE_NODE]));
        }

        $l_gets = isys_module_request::get_instance()
            ->get_gets();
        $l_posts = isys_module_request::get_instance()
            ->get_posts();

        $l_objUser = isys_component_dao_user::instance(isys_application::instance()->database);
        $l_nUserID = $l_objUser->get_current_user_id();

        // navbar stuff.
        $l_navbar->set_active(true, C__NAVBAR_BUTTON__EDIT);

        if (isys_glob_get_param("navMode") == C__NAVMODE__EDIT) {
            $l_navbar->set_visible(false, C__NAVBAR_BUTTON__EDIT)
                ->set_active(true, C__NAVBAR_BUTTON__SAVE)
                ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
        }

        switch ($_GET[C__GET__SETTINGS_PAGE]) {
            case C__SETTINGS_PAGE__USER:

                // Handle navmode actions.
                if (isset($l_posts["navMode"]) && $l_posts["navMode"] == C__NAVMODE__SAVE) {
                    try {
                        $l_objUser->save_settings(C__SETTINGS_PAGE__USER, $l_posts);
                        $l_objUser->save_settings(C__SETTINGS_PAGE__THEME, $l_posts);

                        isys_notify::success(isys_application::instance()->container->get('language')
                            ->get('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
                        isys_locale::get_instance()
                            ->reset_cache(true);
                    } catch (isys_exception_general $e) {
                        $l_error = $e->getMessage();
                        isys_notify::error($l_error, ['sticky' => true]);
                    }
                }
                break;

            case 'login':
                return $this->process_user_login();
                break;
        }

        /**
         * Route into function
         */
        if ($l_nUserID > 0 && isset($this->m_pageinfo[$l_gets[C__GET__SETTINGS_PAGE]]['method'])) {
            if ($this->m_pageinfo[$l_gets[C__GET__SETTINGS_PAGE]]['method'] && method_exists($this, $this->m_pageinfo[$l_gets[C__GET__SETTINGS_PAGE]]['method'])) {
                call_user_func([
                    $this,
                    $this->m_pageinfo[$l_gets[C__GET__SETTINGS_PAGE]]['method']
                ], $l_nUserID);
            }
        }

        // Set template body.
        $l_tplclass->include_template('contentbottom', $this->m_pageinfo[$l_gets[C__GET__SETTINGS_PAGE]]['template']);

        return null;
    }

    /**
     * Method for displaying the user-settings.
     *
     * @param  integer $p_user_id
     */
    private function user($p_user_id)
    {
        $language = isys_application::instance()->container->get('language');
        $locales = isys_application::instance()->container->get('locales');
        $languages = isys_glob_get_language_constants();

        $userSettings = isys_component_dao_user::instance(isys_application::instance()->container->get('database'))
            ->get_user_settings();

        $rules = [
            'C__CATG__OVERVIEW__LANGUAGE'         => [
                'p_arData'        => $languages,
                'p_strSelectedID' => $locales->get_setting(LC_LANG),
                'p_strClass'      => 'input input-mini'
            ],
            'C__CATG__OVERVIEW__DATE_FORMAT'      => [
                'p_arData'        => $languages,
                'p_strSelectedID' => $locales->get_setting(LC_TIME),
                'p_strClass'      => 'input input-mini'
            ],
            'C__CATG__OVERVIEW__NUMERIC_FORMAT'   => [
                'p_arData'        => $languages,
                'p_strSelectedID' => $locales->get_setting(LC_NUMERIC),
                'p_strClass'      => 'input input-mini'
            ],
            'C__CATG__OVERVIEW__MONETARY_FORMAT'  => [
                'p_strTable'      => 'isys_currency',
                'p_strSelectedID' => $locales->get_setting(LC_MONETARY),
                'p_strClass'      => 'input input-mini'
            ],
            'C__CATG__OVERVIEW__DEFAULT_TREEVIEW' => [
                'p_arData'        => [
                    C__CMDB__VIEW__TREE_OBJECTTYPE => $language->get('LC__CMDB__OBJECT_VIEW'),
                    C__CMDB__VIEW__TREE_LOCATION   => $language->get('LC__CMDB__MENU_TREE_VIEW')
                ],
                'p_strSelectedID' => $userSettings['isys_user_locale__default_tree_view'] ?: C__CMDB__VIEW__TREE_OBJECTTYPE,
                'p_strClass'      => 'input input-mini'
            ],
            'C__CATG__OVERVIEW__DEFAULT_TREETYPE' => [
                'p_arData'        => [
                    C__CMDB__VIEW__TREE_LOCATION__LOCATION      => $language->get('LC__CMDB__TREE_VIEW__LOCATION'),
                    C__CMDB__VIEW__TREE_LOCATION__LOGICAL_UNITS => $language->get('LC__CMDB__TREE_VIEW__LOGICAL_UNIT'),
                    C__CMDB__VIEW__TREE_LOCATION__COMBINED      => $language->get('LC__CMDB__TREE_VIEW__COMBINED'),
                ],
                'p_strSelectedID' => $userSettings['isys_user_locale__default_tree_type'],
                'p_strClass'      => 'input input-mini'
            ],
            'C__CATG__OVERVIEW__BROWSER_LANGUAGE' => [
                'p_arData'        => get_smarty_arr_YES_NO(),
                'p_strSelectedID' => $locales->get_setting('browser_language'),
                'p_strClass'      => 'input input-mini'
            ]
        ];

        isys_application::instance()->container->get('template')
            ->assign("useBrowserLanguage", $locales->get_setting('browser_language'))
            ->smarty_tom_add_rules("tom.content.bottom", $rules);
    }

    /**
     * Process generic settings (config.inc.php)
     */
    private function system()
    {
        if (isset($_POST['settings']['user']) && is_array($_POST['settings']['user'])) {
            foreach ($_POST['settings']['user'] as $l_key => $l_value) {
                isys_usersettings::set($l_key, $l_value);
            }

            isys_notify::success(isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
        }

        isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__SAVE)
            ->set_visible(false, C__NAVBAR_BUTTON__EDIT)
            ->set_save_mode('quick');

        $l_settings = isys_usersettings::get();
        $l_definition = isys_usersettings::get_definition();
        ksort($l_definition);

        isys_application::instance()->template->activate_editmode()
            ->assign("bShowCommentary", false)
            ->assign('content_title', isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__TREE__SYSTEM__SETTINGS__USER__SYSTEM_SETTINGS') . ' (' . isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__LOGBOOK__USER') . ')')
            ->assign('definition', $l_definition)
            ->assign('disableTabs', true)
            ->assign('systemWideKey', 'user')
            ->assign('settings', $l_settings);
    }

    /**
     * Method for displaying the "change password" page.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    private function process_user_login()
    {
        global $index_includes, $g_comp_database;

        $l_rules = [];
        $l_rules["C__CONTACT__PERSON_PASSWORD"]["p_strValue"] = '';
        $l_rules["C__CONTACT__PERSON_PASSWORD_SECOND"]["p_strSelectedID"] = '';

        isys_application::instance()->template->smarty_tom_add_rules("tom.content.bottom", $l_rules);

        $l_error = '';
        $l_user_dao = isys_component_dao_user::instance($g_comp_database);
        $l_user_id = $l_user_dao->get_current_user_id();

        $l_row = $l_user_dao->get_user($l_user_id)
            ->get_row();

        if (isys_glob_get_param("navMode") == C__NAVMODE__SAVE) {
            $_POST['C__CONTACT__PERSON_PASSWORD'] = trim($_POST['C__CONTACT__PERSON_PASSWORD']);
            $_POST['C__CONTACT__PERSON_PASSWORD_SECOND'] = trim($_POST['C__CONTACT__PERSON_PASSWORD_SECOND']);
            $l_password_minlength = (int)isys_tenantsettings::get('minlength.login.password', 4);
            $personLoginDao = isys_cmdb_dao_category_s_person_login::instance($g_comp_database);
            $validationResult = $personLoginDao->validate_user_data();
            $validationRules = [];

            if (!$validationResult) {
                $validationRules = $personLoginDao->get_additional_rules();
                $l_rules = isys_glob_array_merge($l_rules, $validationRules);
            }

            if (!empty($_POST['C__CONTACT__PERSON_PASSWORD']) && strlen($_POST['C__CONTACT__PERSON_PASSWORD']) >= $l_password_minlength &&
                $_POST['C__CONTACT__PERSON_PASSWORD'] == $_POST['C__CONTACT__PERSON_PASSWORD_SECOND'] && $validationResult) {
                $personLoginDao->change_password($l_row['isys_cats_person_list__id'], $_POST['C__CONTACT__PERSON_PASSWORD']);
                isys_notify::success(isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
            } else {
                $_GET['navMode'] = C__NAVMODE__EDIT;
                $_POST['navMode'] = C__NAVMODE__EDIT;

                $l_rules["C__CONTACT__PERSON_PASSWORD"]["p_strValue"] = $_POST['C__CONTACT__PERSON_PASSWORD'];
                $l_rules["C__CONTACT__PERSON_PASSWORD_SECOND"]["p_strValue"] = $_POST['C__CONTACT__PERSON_PASSWORD_SECOND'];

                if (!$validationResult && !empty($validationRules)) {
                    $l_error = current($validationRules)['message'];
                    isys_application::instance()->template->assign('validationError', true);
                } elseif ($_POST['C__CONTACT__PERSON_PASSWORD'] != $_POST['C__CONTACT__PERSON_PASSWORD_SECOND']) {
                    $l_error = isys_application::instance()->container->get('language')
                        ->get('LC__LOGIN__PASSWORDS_DONT_MATCH');
                } else {
                    $l_error = isys_application::instance()->container->get('language')
                        ->get('LC__LOGIN__SAVE_ERROR', $l_password_minlength);
                }
                isys_notify::error($l_error, ['sticky' => true]);
            }
        }

        // navbar stuff.
        $l_navbar = isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__EDIT);

        if (isys_glob_get_param("navMode") == C__NAVMODE__EDIT) {
            $l_navbar->set_visible(false, C__NAVBAR_BUTTON__EDIT)
                ->set_active(true, C__NAVBAR_BUTTON__SAVE)
                ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
        }

        isys_application::instance()->template->assign('title', isys_application::instance()->container->get('language')
            ->get('LC__LOGIN__SETTINGS_CHANGE', [$l_row['isys_cats_person_list__title']]))
            ->smarty_tom_add_rules("tom.content.bottom", $l_rules);

        $index_includes['contentbottomcontent'] = 'content/bottom/content/module__settings__user_login.tpl';
    }
}
