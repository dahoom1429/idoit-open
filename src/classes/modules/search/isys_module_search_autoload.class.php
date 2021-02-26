<?php

/**
 * i-doit
 *
 * Class autoloader for search add-on.
 *
 * @package     Modules
 * @subpackage  Search
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_search_autoload extends isys_module_manager_autoload
{
    /**
     * Module specific autoloader.
     *
     * @param  string $className
     *
     * @return boolean
     */
    public static function init($className)
    {
        $addOnPath = '/src/classes/modules/search/';
        $classMap = [
            'isys_auth_search'             => 'auth/isys_auth_search.class.php',
            'isys_module_search'           => 'isys_module_search.class.php',
            'isys_search_filter'           => 'filter/isys_search_filter.class.php',
            'isys_search_filter_string'    => 'filter/isys_search_filter_string.class.php',
            'isys_search_filter_interface' => 'filter/isys_search_filter_interface.class.php',
            'isys_module_search_autoload'  => 'isys_module_search_autoload.class.php',
        ];

        if (isset($classMap[$className]) && parent::include_file($addOnPath . $classMap[$className])) {
            isys_cache::keyvalue()->ns('autoload')->set($className, $addOnPath . $classMap[$className]);

            return true;
        }

        return false;
    }
}
