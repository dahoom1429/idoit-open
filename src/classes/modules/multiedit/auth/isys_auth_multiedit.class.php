<?php

/**
 * i-doit
 *
 * Auth: Class for i-doit authorization rules.
 *
 * @package     Modules
 * @subpackage  multiedit
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     1.12
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_auth_multiedit extends isys_auth implements isys_auth_interface
{
    /**
     * Container for singleton instance
     *
     * @var isys_auth_multiedit
     */
    private static $instance;

    /**
     * Retrieve singleton instance of authorization class
     *
     * @return isys_auth_multiedit
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function instance()
    {
        // If the DAO has not been loaded yet, we initialize it now.
        if (self::$m_dao === null) {
            global $g_comp_database;

            self::$m_dao = new isys_auth_dao($g_comp_database);
        }

        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Method for returning the available auth-methods. This will be used for the GUI.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_auth_methods()
    {
        return [
            'multiedit'                   => [
                'title'  => isys_application::instance()->container->get('language')
                    ->get('LC__AUTH_GUI__MULTIEDIT_CONDITION'),
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EXECUTE
                ]
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
        return defined_or_default('C__MODULE__MULTIEDIT');
    }

    /**
     * Get title of related module
     *
     * @return string
     */
    public function get_module_title()
    {
        return 'LC__MODULE__MULTIEDIT';
    }

    /**
     *
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function multiedit($p_right)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->generic_right($p_right, 'multiedit', self::EMPTY_ID_PARAM, new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__MULTIEDIT_EXCEPTION__MISSING_RIGHT_FOR_MULTIEDIT', [isys_auth::get_right_name($p_right)])));
    }
}
