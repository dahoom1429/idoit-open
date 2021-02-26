<?php

/**
 * i-doit
 *
 * Class autoloader.
 *
 * @package     Modules
 * @subpackage  Nostalgia
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_nostalgia_autoload extends isys_module_manager_autoload
{
    /**
     * Module specific autoloader.
     *
     * @param string $className
     *
     * @return  boolean
     */
    public static function init($className)
    {
        $addOnPath = '/src/classes/modules/nostalgia/';
        $classMap = [
            'isys_helper_ip'          => 'src/classes/helper/isys_helper_ip.class.php',
            'isys_module_authable'    => 'src/isys_module_authable.class.php',
            'isys_module_hookable'    => 'src/isys_module_hookable.class.php',
            'isys_module_installable' => 'src/isys_module_installable.class.php',
            'FPDI'                    => 'src/fpdi.php',
        ];

        if (isset($classMap[$className]) && parent::include_file($addOnPath . $classMap[$className])) {
            isys_cache::keyvalue()->ns('autoload')->set($className, $addOnPath . $classMap[$className]);

            return true;
        }

        return false;
    }
}
