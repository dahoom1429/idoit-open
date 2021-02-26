<?php

/**
 * @version     1.13
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_migration extends isys_module
{
    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_MAIN_MENU   = false;
    const DISPLAY_IN_SYSTEM_MENU = false;
    const MAIN_MENU_REWRITE_LINK = false;

    /**
     * @var  boolean
     */
    protected static $m_licenced = true;

    /**
     * Variable which the module request class.
     *
     * @var  isys_module_request
     */
    protected $m_modreq;

    /**
     * Variable which holds the template component.
     *
     * @var  isys_component_template
     */
    protected $m_tpl;

    /**
     * Variable which holds the database component.
     *
     * @var  isys_component_database
     */
    protected $m_db;

    /**
     * Return instance of statistics dao.
     *
     * @param   $p_database
     *
     * @return  isys_migration_interface
     */
    public static function getMigrationDao($p_database, $migrationDaoClass)
    {
        if (!class_exists($migrationDaoClass)) {
            include_once('init.php');
        }

        return $migrationDaoClass::instance($p_database);
    }

    /**
     * Initializes the module.
     *
     * @param   isys_module_request & $p_req
     *
     * @return  isys_module_migration
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_modreq = $p_req;
        $this->m_db     = $p_req->get_database();

        return $this;
    } // function

    /**
     * @return $this|isys_module|isys_module_interface
     * @throws Exception
     */
    public function start()
    {
        return $this;
    } // function
}
