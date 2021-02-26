<?php

/**
 * @version     1.13
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_migration_autoload extends isys_module_manager_autoload
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
        $addOnPath = '/src/classes/modules/migration/';
        $classMap = [
            'isys_migration_interface' => 'dao/isys_migration_interface.class.php',
            'isys_migration_dao' => 'dao/isys_migration_dao.class.php',
            'isys_migration_dao_database_objects_to_category' => 'dao/isys_migration_dao_database_objects_to_category.class.php',
        ];

        if (isset($classMap[$className]) && parent::include_file($addOnPath . $classMap[$className])) {
            isys_cache::keyvalue()->ns('autoload')->set($className, $addOnPath . $classMap[$className]);

            return true;
        }

        return false;
    }
}
