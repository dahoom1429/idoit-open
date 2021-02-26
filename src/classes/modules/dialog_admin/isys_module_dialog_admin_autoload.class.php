<?php

/**
 * i-doit
 *
 * Class autoloader.
 *
 * @package     Modules
 * @subpackage  Dialog_admin
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_dialog_admin_autoload extends isys_module_manager_autoload
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
        $addOnPath = '/src/classes/modules/dialog_admin/';
        $classMap = [
            'isys_auth_dao_dialog_admin'        => 'auth/dao/isys_auth_dao_dialog_admin.class.php',
            'isys_auth_dialog_admin'            => 'auth/isys_auth_dialog_admin.class.php',
            'isys_dialog_admin_dao'             => 'dao/isys_dialog_admin_dao.class.php',
            'isys_module_dialog_admin'          => 'isys_module_dialog_admin.class.php',
            'isys_module_dialog_admin_autoload' => 'isys_module_dialog_admin_autoload.class.php',
        ];

        if (isset($classMap[$className]) && parent::include_file($addOnPath . $classMap[$className])) {
            isys_cache::keyvalue()->ns('autoload')->set($className, $addOnPath . $classMap[$className]);

            return true;
        }

        return false;
    }
}
