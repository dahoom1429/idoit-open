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
class isys_auth_system_licence extends isys_auth_system
{
    /**
     * Container for singleton instance
     *
     * @var  isys_auth_system_licence
     */
    private static $m_instance = null;

    /**
     * Retrieve singleton instance of authorization class.
     *
     * @return  isys_auth_system_licence
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
    public static function get_licencesettings_parameter()
    {
        return [
            'INSTALLATION' => 'LC__UNIVERSAL__LICENE_INSTALLATION',
            'OVERVIEW'     => 'LC__UNIVERSAL__LICENE_OVERVIEW'
        ];
    }

    /**
     * Licence installation rights.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function installation($p_right)
    {
        return $this->licencesettings($p_right, 'installation');
    }

    /**
     * Licence overview rights.
     *
     * @param   integer $p_right
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function overview($p_right)
    {
        return $this->licencesettings($p_right, 'overview');
    }
}