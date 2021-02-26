<?php

/**
 * i-doit
 *
 * Class autoloader.
 *
 * @package     Modules
 * @subpackage  Templates
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_templates_autoload extends isys_module_manager_autoload
{
    /**
     * Autoloader
     *
     * @param  string $className
     *
     * @return boolean
     */
    public static function init($className)
    {
        $addOnPath = '/src/classes/modules/templates/';
        $classMap = [
            'isys_module_templates_autoload' => 'isys_module_templates_autoload.class.php',
            'isys_module_templates'          => 'isys_module_templates.class.php',
            'isys_auth_templates'            => 'auth/isys_auth_templates.class.php',
            'isys_templates_dao'             => 'dao/isys_templates_dao.class.php'
        ];

        if (isset($classMap[$className]) && parent::include_file($addOnPath . $classMap[$className])) {
            isys_cache::keyvalue()->ns('autoload')->set($className, $addOnPath . $classMap[$className]);


            return true;
        }

        return false;
    }
}
