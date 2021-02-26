<?php

/**
 * i-doit
 *
 * Class autoloader.
 *
 * @package     Modules
 * @subpackage  Dashboard
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_dashboard_autoload extends isys_module_manager_autoload
{
    /**
     * Module specific autoloader.
     *
     * @param   string $className
     *
     * @return  boolean
     */
    public static function init($className)
    {
        $addOnPath = '/src/classes/modules/dashboard/';
        $classMap = [
            'isys_ajax_handler_dashboard' => 'handler/ajax/isys_ajax_handler_dashboard.class.php',
            'isys_ajax_handler_dashboard_popup' => 'handler/ajax/popup/isys_ajax_handler_dashboard_popup.class.php',
            'isys_ajax_handler_dashboard_widgets_bookmarks' => 'handler/ajax/widgets/bookmarks/isys_ajax_handler_dashboard_widgets_bookmarks.class.php',
            'isys_ajax_handler_dashboard_widgets_calculator' => 'handler/ajax/widgets/calculator/isys_ajax_handler_dashboard_widgets_calculator.class.php',
            'isys_ajax_handler_dashboard_widgets_calendar' => 'handler/ajax/widgets/calendar/isys_ajax_handler_dashboard_widgets_calendar.class.php',
            'isys_ajax_handler_dashboard_widgets_loggedinusers' => 'handler/ajax/widgets/loggedinusers/isys_ajax_handler_dashboard_widgets_loggedinusers.class.php',
            'isys_ajax_handler_dashboard_widgets_properties' => 'handler/ajax/widgets/properties/isys_ajax_handler_dashboard_widgets_properties.class.php',
            'isys_auth_dashboard' => 'auth/isys_auth_dashboard.class.php',
            'isys_dashboard_dao' => 'dao/isys_dashboard_dao.class.php',
            'isys_dashboard_widgets' => 'widgets/isys_dashboard_widgets.class.php',
            'isys_dashboard_widgets_bookmarks' => 'widgets/bookmarks/isys_dashboard_widgets_bookmarks.class.php',
            'isys_dashboard_widgets_calculator' => 'widgets/calculator/isys_dashboard_widgets_calculator.class.php',
            'isys_dashboard_widgets_calendar' => 'widgets/calendar/isys_dashboard_widgets_calendar.class.php',
            'isys_dashboard_widgets_iframe' => 'widgets/iframe/isys_dashboard_widgets_iframe.class.php',
            'isys_dashboard_widgets_loggedinusers' => 'widgets/loggedinusers/isys_dashboard_widgets_loggedinusers.class.php',
            'isys_dashboard_widgets_myobjects' => 'widgets/myobjects/isys_dashboard_widgets_myobjects.class.php',
            'isys_dashboard_widgets_notes' => 'widgets/notes/isys_dashboard_widgets_notes.class.php',
            'isys_dashboard_widgets_properties' => 'widgets/properties/isys_dashboard_widgets_properties.class.php',
            'isys_dashboard_widgets_quicklaunch' => 'widgets/quicklaunch/isys_dashboard_widgets_quicklaunch.class.php',
            'isys_dashboard_widgets_rss' => 'widgets/rss/isys_dashboard_widgets_rss.class.php',
            'isys_dashboard_widgets_tips' => 'widgets/tips/isys_dashboard_widgets_tips.class.php',
            'isys_dashboard_widgets_welcome' => 'widgets/welcome/isys_dashboard_widgets_welcome.class.php',
        ];

        if (isset($classMap[$className]) && parent::include_file($addOnPath . $classMap[$className])) {
            isys_cache::keyvalue()->ns('autoload')->set($className, $addOnPath . $classMap[$className]);

            return true;
        }

        return false;
    }
}
