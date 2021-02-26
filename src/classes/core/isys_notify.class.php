<?php
/**
 * Class isys_notification
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @author      Leonard Fischer <lfischer@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

use dstuecken\Notify\NotificationCenter;
use dstuecken\Notify\Type\DetailedNotification;

class isys_notify
{
    /**
     * The standard notification-type.
     */
    const STANDARD = 0;

    /**
     * The success notification-type.
     */
    const SUCCESS = 1;

    /**
     * The error notification-type.
     */
    const ERROR = 2;

    /**
     * The info notification-type.
     */
    const INFO = 3;

    /**
     * The warning notification-type.
     */
    const WARNING = 4;

    /**
     * Static method for retrieving the options-array.
     *
     * @param   string  $p_destroy_callback
     * @param   string  $p_create_callback
     * @param   boolean $p_sticky
     * @param   integer $p_life
     * @param   string  $p_classname
     * @param   integer $p_width
     *
     * @return  array
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public static function options($p_destroy_callback = null, $p_create_callback = null, $p_sticky = null, $p_life = null, $p_classname = null, $p_width = null)
    {
        $l_options = [];

        if ($p_destroy_callback !== null) {
            $l_options['destroyed'] = $p_destroy_callback;
        }

        if ($p_create_callback !== null) {
            $l_options['created'] = $p_create_callback;
        }

        if ($p_sticky !== null) {
            $l_options['sticky'] = !!$p_sticky;
        }

        if ($p_life !== null) {
            $l_options['life'] = $p_life;
        }

        if ($p_classname !== null) {
            $l_options['className'] = $p_classname;
        }

        if ($p_width !== null) {
            $l_options['width'] = $p_width;
        }

        return $l_options;
    }

    /**
     * Method for displaying an default message.
     *
     * @param   string $p_message
     * @param   array  $p_options
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public static function message($p_message, $p_options = [])
    {
        isys_application::instance()->container['notify']->notify(new DetailedNotification($p_message,
            $p_options['header'] ?: isys_application::instance()->container->get('language')
                ->get('LC__NOTIFY__MESSAGE'), $p_options), NotificationCenter::DEBUG);
    }

    /**
     * Method for displaying an success.
     *
     * @param   string $p_message
     * @param   array  $p_options
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public static function success($p_message, $p_options = [])
    {
        isys_application::instance()->container['notify']->notify(new DetailedNotification($p_message,
            $p_options['header'] ?: isys_application::instance()->container->get('language')
                ->get('LC__NOTIFY__SUCCESS'), $p_options), NotificationCenter::INFO);
    }

    /**
     * Method for displaying an error.
     *
     * @param   string $p_message
     * @param   array  $p_options
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public static function error($p_message, $p_options = [])
    {
        if (ENVIRONMENT == 'development' && (class_exists('isys_module_debug_bar'))) {
            (new \idoit\Module\DebugBar\Dumper\StackTraceDumper())->execute($p_message);
        }

        isys_application::instance()->container['notify']->notify(new DetailedNotification($p_message,
            $p_options['header'] ?: isys_application::instance()->container->get('language')
                ->get('LC__NOTIFY__ERROR'), $p_options), NotificationCenter::ERROR);
    }

    /**
     * Method for displaying an error.
     *
     * @param   string $p_message
     * @param   array  $p_options
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public static function debug($p_message, $p_options = [])
    {
        if (isys_tenantsettings::get('system.devmode')) {
            isys_application::instance()->container['notify']->notify(new DetailedNotification($p_message, $p_options['header'] ?: 'DEBUG', $p_options),
                NotificationCenter::NOTICE);
        }
    }

    /**
     * Method for displaying a info.
     *
     * @param   string $p_message
     * @param   array  $p_options
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public static function info($p_message, $p_options = [])
    {
        isys_application::instance()->container['notify']->notify(new DetailedNotification($p_message,
            $p_options['header'] ?: isys_application::instance()->container->get('language')
                ->get('LC__NOTIFY__INFO'), $p_options), NotificationCenter::NOTICE);
    }

    /**
     * Method for displaying a warning.
     *
     * @param   string $p_message
     * @param   array  $p_options
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public static function warning($p_message, $p_options = [])
    {
        isys_application::instance()->container['notify']->notify(new DetailedNotification($p_message,
            $p_options['header'] ?: isys_application::instance()->container->get('language')
                ->get('LC__NOTIFY__WARNING'), $p_options), NotificationCenter::WARNING);
    }
}
