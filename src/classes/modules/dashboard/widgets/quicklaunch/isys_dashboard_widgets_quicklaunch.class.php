<?php

use idoit\AddOn\AuthableInterface;

/**
 * i-doit
 *
 * Dashboard widget class
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.2
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_dashboard_widgets_quicklaunch extends isys_dashboard_widgets
{
    /**
     * Path and Filename of the template.
     *
     * @var  string
     */
    protected $m_tpl_file = '';

    /**
     * Init method.
     *
     * @return  isys_dashboard_widgets_quicklaunch
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function init($p_config = [])
    {
        $this->m_tpl_file = __DIR__ . '/templates/quicklaunch.tpl';

        return parent::init();
    }

    /**
     * Render method.
     *
     * @param   string $p_unique_id
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function render($p_unique_id)
    {
        $l_function_list = $l_configuration_list = [];

        $language = isys_application::instance()->container->get('language');

        if (defined('C__MODULE__USER_SETTING') && isys_auth_system::instance()
            ->is_allowed_to(isys_auth::VIEW, 'USERSETTINGS')) {
            $l_configuration_list[isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__USER_SETTING,
                C__GET__SETTINGS_PAGE => 1
            ])] = $language->get('LC__WIDGET__QUICKLAUNCH_USER_SETTINGS');
        }

        // Check for module-constants, to display these in the frontend.
        if (defined('C__MODULE__IMPORT') && C__MODULE__IMPORT > 0 && isys_auth_import::instance()->is_allowed_to(isys_auth::VIEW, 'IMPORT')) {
            $l_function_list[isys_helper_link::create_url([C__GET__MODULE_ID => C__MODULE__IMPORT])] = $language->get('LC__MODULE__IMPORT');
        }

        if (defined('C__MODULE__EXPORT') && C__MODULE__EXPORT > 0 && isys_auth_export::instance()->is_allowed_to(isys_auth::VIEW, 'EXPORT')) {
            $l_function_list[isys_helper_link::create_url([C__GET__MODULE_ID => C__MODULE__EXPORT])] = $language->get('LC__MODULE__EXPORT');
        }

        if (defined('C__MODULE__MULTIEDIT') && C__MODULE__MULTIEDIT > 0 && isys_auth_multiedit::instance()->is_allowed_to(isys_auth::EXECUTE, 'MULTIEDIT')) {
            $l_function_list[isys_application::instance()->www_path . 'multiedit'] = $language->get('LC__MULTIEDIT__MULTIEDIT');
        }

        if (defined('C__MODULE__TEMPLATES') && C__MODULE__TEMPLATES > 0 && isys_auth_templates::instance()->is_allowed_to(isys_auth::VIEW, 'TEMPLATES')) {
            $l_function_list[isys_helper_link::create_url([C__GET__MODULE_ID => C__MODULE__TEMPLATES])] = $language->get('LC__MODULE__TEMPLATES');
        }

        if (defined('C__MODULE__LOGBOOK') && C__MODULE__LOGBOOK > 0 && isys_auth_logbook::instance()->is_allowed_to(isys_auth::VIEW, 'LOGBOOK')) {
            $l_function_list[isys_helper_link::create_url([C__GET__MODULE_ID => C__MODULE__LOGBOOK])] = $language->get('LC__MODULE__LOGBOOK__TITLE');
        }

        if (defined('C__MODULE__DIALOG_ADMIN') && C__MODULE__DIALOG_ADMIN > 0) {
            if (isys_auth_dialog_admin::instance()
                    ->is_allowed_to(isys_auth::VIEW, 'TABLE') || isys_auth_dialog_admin::instance()
                    ->is_allowed_to(isys_auth::VIEW, 'CUSTOM')) {
                $l_function_list[isys_helper_link::create_url([C__GET__MODULE_ID => C__MODULE__DIALOG_ADMIN])] = $language->get('LC__DIALOG_ADMIN');
            }
        }

        if (defined('C__MODULE__SYSTEM') && C__MODULE__SYSTEM > 0 && isys_auth_system::instance()->is_allowed_to(isys_auth::SUPERVISOR, 'SYSTEMTOOLS')) {
            if (isys_auth_system::instance()->is_allowed_to(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/SYSTEMOVERVIEW')) {
                $l_function_list[isys_helper_link::create_url([
                    C__GET__MODULE_ID => C__MODULE__SYSTEM,
                    'what'            => 'sysoverview'
                ])] = $language->get('LC__SYSTEM__OVERVIEW');
            }

            if (isys_auth_system::instance()
                ->is_allowed_to(isys_auth::EXECUTE, 'SYSTEMTOOLS/CACHE')) {
                $l_function_list[isys_helper_link::create_url([
                    C__GET__MODULE_ID => C__MODULE__SYSTEM,
                    'what'            => 'cache'
                ])] = $language->get('LC__SETTINGS__SYSTEM__FLUSH_SYS_CACHE');
            }

            $l_configuration_list[isys_helper_link::create_url([C__GET__MODULE_ID => C__MODULE__SYSTEM])] = $language->get('LC__NAVIGATION__MAINMENU__TITLE_ADMINISTRATION');
        }

        if (defined('C__MODULE__LDAP') && C__MODULE__LDAP > 0) {
            $l_ldap_auth = isys_auth_system::instance();

            if ($l_ldap_auth->is_allowed_to(isys_auth::SUPERVISOR, 'LDAP/' . C__MODULE__LDAP . C__LDAPPAGE__CONFIG) ||
                $l_ldap_auth->is_allowed_to(isys_auth::SUPERVISOR, 'LDAP/' . C__MODULE__LDAP . C__LDAPPAGE__SERVERTYPES)) {
                $l_configuration_list[isys_helper_link::create_url([C__GET__MODULE_ID => C__MODULE__LDAP])] = $language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__LDAP');
            }
        }

        if (defined('C__MODULE__NAGIOS') && class_exists('isys_module_nagios') && isys_module_manager::instance()->is_active('nagios')) {
            if (is_a(isys_module_nagios::class, AuthableInterface::class, true) && isys_module_nagios::getAuth()->has_any_rights_in_module()) {
                $l_configuration_list[isys_helper_link::create_url([C__GET__MODULE_ID => C__MODULE__NAGIOS])] = $language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__NAGIOS');
            } elseif (is_a(isys_module_nagios::class, isys_module_authable::class, true) && isys_module_nagios::get_auth()->has_any_rights_in_module()) {
                $l_configuration_list[isys_helper_link::create_url([C__GET__MODULE_ID => C__MODULE__NAGIOS])] = $language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__NAGIOS');
            }
        }

        return $this->m_tpl
            ->assign('function_list', $l_function_list)
            ->assign('configuration_list', $l_configuration_list)
            ->assign('allow_update', isys_auth_system::instance()->is_allowed_to(isys_auth::EXECUTE, 'SYSTEMTOOLS/IDOITUPDATE'))
            ->fetch($this->m_tpl_file);
    }
}
