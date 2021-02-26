<?php

/**
 * i-doit
 *
 * Class autoloader.
 *
 * @package     Modules
 * @subpackage  Statistics
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_statistics_autoload extends isys_module_manager_autoload
{
    /**
     * Autoloader
     *
     * @param string $className
     *
     * @return boolean
     */
    public static function init($className)
    {
        $addOnPath = '/src/classes/modules/statistics/';
        $classMap = [
            'isys_statistics_dao' => 'dao/isys_statistics_dao.class.php'
        ];

        if (isset($classMap[$className]) && parent::include_file($addOnPath . $classMap[$className])) {
            isys_cache::keyvalue()->ns('autoload')->set($className, $addOnPath . $classMap[$className]);

            return true;
        }

        return false;
    }
}
