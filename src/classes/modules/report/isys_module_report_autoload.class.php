<?php

/**
 * i-doit
 *
 * Class autoloader.
 *
 * @package     Modules
 * @subpackage  Report
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_report_autoload extends isys_module_manager_autoload
{
    /**
     * Autoloader.
     *
     * @param  string $className
     *
     * @return boolean
     */
    public static function init($className)
    {
        $addOnPath = '/src/classes/modules/report/';
        $classMap = [
            'isys_ajax_handler_report' => 'handler/ajax/isys_ajax_handler_report.class.php',
            'isys_auth_dao_report' => 'auth/dao/isys_auth_dao_report.class.php',
            'isys_auth_report' => 'auth/isys_auth_report.class.php',
            'isys_dashboard_widgets_reports' => 'dashboard/widgets/reports/isys_dashboard_widgets_reports.class.php',
            'isys_handler_report' => 'handler/controller/isys_handler_report.class.php',
            'isys_module_report_open' => 'controller/isys_module_report_open.class.php',
            'isys_module_report_pro' => 'controller/isys_module_report_pro.class.php',
            'isys_popup_report' => 'popups/isys_popup_report.class.php',
            'isys_report_dao' => 'dao/isys_report_dao.class.php',
            'isys_report_export_pdf' => 'export/isys_report_export_pdf.class.php',
            'isys_report_view' => 'views/isys_report_view.class.php',
            'isys_report_view_accounting' => 'views/isys_report_view_accounting.class.php',
            'isys_report_view_cable_connections' => 'views/isys_report_view_cable_connections.class.php',
            'isys_report_view_cmdb_changes' => 'views/isys_report_view_cmdb_changes.class.php',
            'isys_report_view_devices_in_location' => 'views/isys_report_view_devices_in_location.class.php',
            'isys_report_view_devices_in_location_detailed' => 'views/isys_report_view_devices_in_location_detailed.class.php',
            'isys_report_view_import_changes' => 'views/isys_report_view_import_changes.class.php',
            'isys_report_view_it_service_cmdb_status' => 'views/isys_report_view_it_service_cmdb_status.class.php',
            'isys_report_view_layer2_nets' => 'views/isys_report_view_layer2_nets.class.php',
            'isys_report_view_layer3_nets' => 'views/isys_report_view_layer3_nets.class.php',
            'isys_report_view_network_connections' => 'views/isys_report_view_network_connections.class.php',
            'isys_report_view_network_plan' => 'views/isys_report_view_network_plan.class.php',
            'isys_report_view_no_relations' => 'views/isys_report_view_no_relations.class.php',
            'isys_report_view_open_cable_connections' => 'views/isys_report_view_open_cable_connections.class.php',
            'isys_report_view_rack_connections' => 'views/isys_report_view_rack_connections.class.php',
            'isys_report_view_relation_it_service' => 'views/isys_report_view_relation_it_service.class.php',
            'isys_report_view_upcoming_status_changes' => 'views/isys_report_view_upcoming_status_changes.class.php',
        ];

        if (isset($classMap[$className]) && parent::include_file($addOnPath . $classMap[$className])) {
            isys_cache::keyvalue()->ns('autoload')->set($className, $addOnPath . $classMap[$className]);

            return true;
        }

        return false;
    }
}
