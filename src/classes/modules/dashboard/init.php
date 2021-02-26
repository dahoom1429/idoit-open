<?php
/**
 * i-doit
 *
 * Module initializer
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.2.0
 */

if (include_once('isys_module_dashboard_autoload.class.php')) {
    spl_autoload_register('isys_module_dashboard_autoload::init');
}
