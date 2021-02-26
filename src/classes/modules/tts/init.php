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

if (include_once('isys_module_tts_autoload.class.php')) {
    spl_autoload_register('isys_module_tts_autoload::init');
}

isys_component_signalcollection::get_instance()
    ->connect('mod.cmdb.processMenuTreeLinks', [
        'isys_module_tts',
        'process_menu_tree_links'
    ]);