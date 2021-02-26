<?php

/**
 * i-doit
 *
 * Class autoloader.
 *
 * @package     Modules
 * @subpackage  Ldap
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_ldap_autoload extends isys_module_manager_autoload
{
    /**
     * Module specific autoloader.
     *
     * @param   string $className
     *
     * @return  boolean
     */
    public static function init($className)
    {
        $addOnPath = '/src/classes/modules/ldap/';
        $classMap = [
            'isys_module_ldap_autoload'             => 'isys_module_ldap_autoload.class.php',
            'isys_ldap_dao_import_active_directory' => 'dao/isys_ldap_dao_import_active_directory.class.php',
            'isys_ldap_dao'                         => 'dao/isys_ldap_dao.class.php',
            'isys_ldap_dao_import'                  => 'dao/isys_ldap_dao_import.class.php',
            'isys_module_ldap'                      => 'isys_module_ldap.class.php',
        ];

        if (isset($classMap[$className]) && parent::include_file($addOnPath . $classMap[$className])) {
            isys_cache::keyvalue()->ns('autoload')->set($className, $addOnPath . $classMap[$className]);

            return true;
        }

        return false;
    }
}
