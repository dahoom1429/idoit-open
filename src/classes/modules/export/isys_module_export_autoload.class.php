<?php

/**
 * i-doit
 *
 * Class autoloader.
 *
 * @package     Modules
 * @subpackage  Export
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_export_autoload extends isys_module_manager_autoload
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
        $addOnPath = '/src/classes/modules/export/';
        $classMap = [
            'isys_auth_export'            => 'auth/isys_auth_export.class.php',
            'isys_export_dao'             => 'dao/isys_export_dao.class.php',
            'isys_module_export_autoload' => 'isys_module_export_autoload.class.php',
            'isys_module_export'          => 'isys_module_export.class.php',
        ];

        if (isset($classMap[$className]) && parent::include_file($addOnPath . $classMap[$className])) {
            isys_cache::keyvalue()->ns('autoload')->set($className, $addOnPath . $classMap[$className]);

            return true;
        }

        return false;
    }
}
