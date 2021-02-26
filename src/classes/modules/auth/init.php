<?php
/**
 * i-doit
 *
 * Module initializer
 *
 * @package     modules
 * @subpackage  auth
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.4.0
 */

isys_component_signalcollection::get_instance()
    ->connect('mod.cmdb.processMenuTreeLinks', [
        'isys_module_auth',
        'process_menu_tree_links'
    ]);