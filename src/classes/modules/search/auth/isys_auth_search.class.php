<?php

/**
 * i-doit
 *
 * Auth: Class for Report module authorization rules.
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_auth_search extends isys_auth implements isys_auth_interface
{
    /**
     * Container for singleton instance
     *
     * @var isys_auth_search
     */
    private static $instance;

    /**
     * Retrieve singleton instance of authorization class
     *
     * @return isys_auth_search
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    public static function instance()
    {
        // If the DAO has not been loaded yet, we initialize it now.
        if (self::$m_dao === null) {
            self::$m_dao = new isys_auth_dao(isys_application::instance()->container->get('database'));
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
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_auth_methods()
    {
        return [
            'search' => [
                'title'    => 'LC__MODULE__SEARCH__TITEL',
                'type'     => 'boolean',
                'rights'   => [isys_auth::VIEW],
                'defaults' => [isys_auth::VIEW]
            ],
        ];
    }

    /**
     * Get ID of related module
     *
     * @return int
     */
    public function get_module_id()
    {
        return defined_or_default('C__MODULE__SEARCH');
    }

    /**
     * Get title of related module
     *
     * @return string
     */
    public function get_module_title()
    {
        return 'LC__MODULE__SEARCH__TITLE';
    }

    /**
     * This method checks, if you are allowed to use the report editor.
     *
     * @throws  isys_exception_general
     * @throws  isys_exception_auth
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function search()
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->generic_boolean('search', new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__REPORT_EXCEPTION__MISSING_RIGHT_FOR_SEARCH')));
    }
}
