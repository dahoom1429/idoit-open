<?php

/**
 * i-doit
 *
 * Auth: Class for i-doit authorization rules.
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.2.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.2.0
 */
class isys_auth_dashboard extends isys_auth implements isys_auth_interface
{
    /**
     * Container for singleton instance
     *
     * @var isys_auth_dashboard
     */
    private static $m_instance;

    /**
     * Retrieve singleton instance of authorization class
     *
     * @return isys_auth_dashboard
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    public static function instance()
    {
        // If the DAO has not been loaded yet, we initialize it now.
        if (self::$m_dao === null) {
            global $g_comp_database;

            self::$m_dao = new isys_auth_dao($g_comp_database);
        }

        if (self::$m_instance === null) {
            self::$m_instance = new self;
        }

        return self::$m_instance;
    }

    /**
     * Method for returning the available auth-methods. This will be used for the GUI.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_auth_methods()
    {
        return [
            'configure_dashboard'        => [
                'title'  => 'LC__AUTH_GUI__CONFIGURE_DASHBOARD',
                'type'   => 'boolean',
                'rights' => [isys_auth::EXECUTE]
            ],
            'configure_other_dashboards' => [
                'title'  => 'LC__AUTH_GUI__CONFIGURE_DASHBOARD_OF_OTHERS',
                'type'   => 'boolean',
                'rights' => [isys_auth::SUPERVISOR]
            ],
            'configure_widgets'          => [
                'title'  => 'LC__AUTH_GUI__CONFIGURE_WIDGETS',
                'type'   => 'boolean',
                'rights' => [isys_auth::EXECUTE]
            ]
        ];
    }

    /**
     * Get ID of related module
     *
     * @return int
     */
    public function get_module_id()
    {
        return defined_or_default('C__MODULE__DASHBOARD');
    }

    /**
     * Get title of related module
     *
     * @return string
     */
    public function get_module_title()
    {
        return "LC__MODULE__DASHBOARD";
    }

    /**
     * Method for checking, if the user is allowed to open and execute the dashboard configuration.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function configure_dashboard($p_right)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->generic_right($p_right, 'configure_dashboard', self::EMPTY_ID_PARAM, new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__DASHBOARD_EXCEPTION__MISSING_RIGHT_FOR_DASHBOARD_CONFIG')));
    }

    /**
     * Method for checking, if the user is allowed to configure the dashboard of other users.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function configure_other_dashboards($p_right)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->generic_right(
            $p_right,
            'configure_other_dashboards',
            self::EMPTY_ID_PARAM,
            new isys_exception_auth(isys_application::instance()->container->get('language')
                ->get('LC__AUTH__DASHBOARD_EXCEPTION__MISSING_RIGHT_FOR_OTHERS_DASHBOARD_CONFIG'))
        );
    }

    /**
     * Method for checking, if the user is allowed to configure widgets.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function configure_widgets($p_right)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->generic_right($p_right, 'configure_widgets', self::EMPTY_ID_PARAM, new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__DASHBOARD_EXCEPTION__MISSING_RIGHT_FOR_WIDGET_CONFIG')));
    }
}
