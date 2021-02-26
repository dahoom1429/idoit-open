<?php

/**
 * i-doit
 *
 * Auth: Class for Auth module authorization rules.
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_auth_auth extends isys_auth implements isys_auth_interface
{
    /**
     * Container for singleton instance
     *
     * @var isys_auth_auth
     */
    private static $m_instance;

    /**
     * Retrieve singleton instance of authorization class
     *
     * @return isys_auth_auth
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
            'overview' => [
                'title'    => 'LC__AUTH_GUI__AUTH_OVERVIEW',
                'type'     => 'boolean',
                'rights'   => [isys_auth::VIEW],
                'defaults' => [isys_auth::VIEW]
            ],
            'module'   => [
                'title' => 'LC__AUTH_GUI__AUTH_MODULES',
                'type'  => 'modules'
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
        return defined_or_default('C__MODULE__AUTH');
    }

    /**
     * Get title of related module
     *
     * @return string
     */
    public function get_module_title()
    {
        return "LC__MODULE__AUTH";
    }

    /**
     * Checks, if the current user is allowed to see the auth-overview.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function overview()
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->generic_boolean('overview', new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__AUTH_EXCEPTION__MISSING_RIGHT_FOR_OVERVIEW')));
    }

    /**
     * @param   integer $p_right
     * @param   integer $p_id
     *
     * @throws  isys_exception_general
     * @throws  isys_exception_auth
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function module($p_right, $p_id)
    {
        // Check for inactive auth system
        if (!$this->is_auth_active()) {
            return true;
        }

        $l_module_const = strtoupper($p_id);

        if (!defined($l_module_const)) {
            throw new isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__EXCEPTION__CONSTANT_COULD_NOT_BE_FOUND', $l_module_const));
        }

        if (is_array($this->m_paths) && isset($this->m_paths['module']) && is_array($this->m_paths['module'])) {
            if (isset($this->m_paths['module'][isys_auth::WILDCHAR]) && in_array($p_right, $this->m_paths['module'][isys_auth::WILDCHAR])) {
                return true;
            }

            if (isset($this->m_paths['module'][$p_id]) && in_array($p_right, $this->m_paths['module'][$p_id])) {
                return true;
            }
        }

        // Retrieve the module row, to display a nice exception message.
        $l_module = isys_module_manager::instance()
            ->get_modules(constant($l_module_const))
            ->get_row();

        throw new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__AUTH_EXCEPTION__MISSING_RIGHT_FOR_MODULE', isys_application::instance()->container->get('language')
                ->get($l_module['isys_module__title'])));
    }
}
