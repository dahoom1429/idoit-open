<?php
/**
 * i-doit
 *
 * Module initializer
 *
 * @package     modules
 * @subpackage  nostalgia
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.7
 */

// Autoloader.
if (include_once('isys_module_nostalgia_autoload.class.php')) {
    spl_autoload_register('isys_module_nostalgia_autoload::init');
}

if (file_exists(__DIR__ . '/functions.inc.php')) {
    include_once __DIR__ . '/functions.inc.php';
}

if (file_exists(__DIR__ . '/constants.inc.php')) {
    include_once __DIR__ . '/constants.inc.php';
}