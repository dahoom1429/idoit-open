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
class isys_auth_system_qr extends isys_auth_system
{
    /**
     * Container for singleton instance
     *
     * @var  isys_auth_system_qr
     */
    private static $m_instance = null;

    /**
     * Retrieve singleton instance of authorization class.
     *
     * @return  isys_auth_system_qr
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
     * Method for retrieving the "parameter" in the configuration GUI.
     *
     * @static
     * @return  array
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    public static function get_qr_config_parameter()
    {
        return [
            'global'  => 'LC__AUTH_GUI__QR_CODE_GLOBAL_CONFIGURATION',
            'objtype' => 'LC__AUTH_GUI__QR_CODE_GLOBAL_OBJECT_TYPE'
        ];
    }

    /**
     * Global configuration rights.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function global_configuration($p_right)
    {
        return $this->qr_config($p_right, 'global');
    }

    /**
     * Object type specific configuration rights.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function objtype($p_right)
    {
        return $this->qr_config($p_right, 'objtype');
    }
}