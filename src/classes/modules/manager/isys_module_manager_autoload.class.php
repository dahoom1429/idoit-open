<?php

/**
 * i-doit
 *
 * Class autoloader.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_manager_autoload
{
    /**
     * Autoloader.
     *
     * @return void
     *
     * @param string $p_classname
     */
    public static function init($p_classname)
    {
    }

    /**
     * Method for including the given file.
     *
     * @param   string $p_file
     *
     * @return  boolean
     */
    public static function include_file($p_file)
    {
        global $g_absdir;

        return ($p_file !== null && file_exists($g_absdir . $p_file) && (include_once $g_absdir . $p_file));
    }
}
