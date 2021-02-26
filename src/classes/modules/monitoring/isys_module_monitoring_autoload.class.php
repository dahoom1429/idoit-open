<?php

/**
 * i-doit
 *
 * Class autoloader.
 *
 * @package     Modules
 * @subpackage  Monitoring
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_monitoring_autoload extends isys_module_manager_autoload
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
        $addOnPath = '/src/classes/modules/monitoring/';
        $classMap = [
            'isys_ajax_handler_monitoring_ndo'        => 'handler/ajax/isys_ajax_handler_monitoring_ndo.class.php',
            'isys_ajax_handler_monitoring_livestatus' => 'handler/ajax/isys_ajax_handler_monitoring_livestatus.class.php',
            'isys_module_monitoring'                  => 'isys_module_monitoring.class.php',
            'isys_monitoring_dao_hosts'               => 'dao/isys_monitoring_dao_hosts.class.php',
            'isys_monitoring_dao_ndo'                 => 'dao/isys_monitoring_dao_ndo.class.php',
            'isys_monitoring_livestatus'              => 'livestatus/isys_monitoring_livestatus.class.php',
            'isys_module_monitoring_autoload'         => 'isys_module_monitoring_autoload.class.php',
            'isys_api_model_monitoring_ndo'           => 'api/isys_api_model_monitoring_ndo.class.php',
            'isys_api_model_monitoring_livestatus'    => 'api/isys_api_model_monitoring_livestatus.class.php',
            'isys_api_model_monitoring'               => 'api/isys_api_model_monitoring.class.php',
            'isys_monitoring_ndo'                     => 'ndo/isys_monitoring_ndo.class.php',
            'isys_monitoring_helper'                  => 'helper/isys_monitoring_helper.class.php',
            'isys_monitoring_widgets_not_ok_hosts'    => 'widgets/not_ok_hosts/isys_monitoring_widgets_not_ok_hosts.class.php',
        ];

        if (isset($classMap[$className]) && parent::include_file($addOnPath . $classMap[$className])) {
            isys_cache::keyvalue()->ns('autoload')->set($className, $addOnPath . $classMap[$className]);

            return true;
        }

        return false;
    }
}
