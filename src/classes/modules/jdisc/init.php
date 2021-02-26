<?php
/**
 * i-doit
 *
 * Module initializer
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

if (include_once('isys_module_jdisc_autoload.class.php')) {
    spl_autoload_register('isys_module_jdisc_autoload::init');
}

/* Register jdisc controller */
$GLOBALS['g_controller']['handler']['jdisc'] = [
    'class' => 'isys_handler_jdisc'
];
/* Register jdisc controller */
$GLOBALS['g_controller']['handler']['jdisc_discovery'] = [
    'class' => 'isys_handler_jdisc_discovery'
];

isys_register::factory('additional-dialog-admin-tables')
    ->set('isys_jdisc_status_list');

\idoit\Psr4AutoloaderClass::factory()
    ->addNamespace('idoit\Module\JDisc', __DIR__ . '/src/');
