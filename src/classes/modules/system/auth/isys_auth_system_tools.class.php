<?php

/**
 * i-doit
 * Auth: Class for CMDB module authorization rules.
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_auth_system_tools extends isys_auth_system
{
    /**
     * Container for singleton instance.
     *
     * @var  isys_auth_system_tools
     */
    private static $m_instance = null;

    /**
     * Retrieve singleton instance of authorization class.
     *
     * @return  isys_auth_system_tools
     * @author  Selcuk Kekec <skekec@i-doit.com>
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
     * Cache rights.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function cache($p_right)
    {
        return $this->systemtools($p_right, 'cache');
    }

    /**
     * Modulemanager rights.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function modulemanager($p_right)
    {
        return $this->systemtools($p_right, 'modulemanager');
    }

    /**
     * Validation rights.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function validation($p_right)
    {
        return $this->systemtools($p_right, 'validation');
    }

    /**
     * I-doit Update rights.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function idoitupdate($p_right)
    {
        return $this->systemtools($p_right, 'idoitupdate');
    }
}