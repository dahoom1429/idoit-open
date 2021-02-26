<?php
/**
 * i-doit
 *
 * Module initializer
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.3.0
 */

// Global monitoring states (Livestatus & NDO).
define('C__MONITORING__STATE__OK', 0);
define('C__MONITORING__STATE__WARNING', 1);
define('C__MONITORING__STATE__CRITICAL', 2);
define('C__MONITORING__STATE__UNKNOWN', 3);

// Global monitoring host states (Livestatus & NDO).
define('C__MONITORING__STATE__UP', 0);
define('C__MONITORING__STATE__DOWN', 1);
define('C__MONITORING__STATE__UNREACHABLE', 2);

define('C__MONITORING__NAME_SELECTION__INPUT', 0);
define('C__MONITORING__NAME_SELECTION__HOSTNAME_FQDN', 1);
define('C__MONITORING__NAME_SELECTION__HOSTNAME', 2);
define('C__MONITORING__NAME_SELECTION__OBJ_ID', 3);

define('C__MONITORING__LIVESTATUS_TYPE__TCP', 'tcp');
define('C__MONITORING__LIVESTATUS_TYPE__UNIX', 'unix');

define('C__MONITORING__TYPE_LIVESTATUS', 'livestatus');
define('C__MONITORING__TYPE_NDO', 'ndo');

if (include_once('isys_module_monitoring_autoload.class.php')) {
    spl_autoload_register('isys_module_monitoring_autoload::init');
}

isys_component_signalcollection::get_instance()
    ->connect('mod.cmdb.processContentTop', ['isys_module_monitoring', 'process_content_top']);

isys_register::factory('widget-register')
    ->set('not_ok_hosts', 'isys_monitoring_widgets_not_ok_hosts');
