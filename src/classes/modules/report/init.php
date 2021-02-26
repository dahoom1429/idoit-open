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

require_once __DIR__ . '/isys_module_report_autoload.class.php';

spl_autoload_register('isys_module_report_autoload::init');

/* Register jdisc controller */
$GLOBALS['g_controller']['handler']['report'] = [
    'class' => 'isys_handler_report'
];

// Defining some constants.
define("C__REPORT__STANDARD", 0);
define("C__REPORT__CUSTOM", 1);

define("C__GET__REPORT_PAGE", "rpID");
define("C__GET__REPORT_REPORT_ID", "reportID");

define("C__REPORT_PAGE__REPORT_BROWSER", 1);
define("C__REPORT_PAGE__STANDARD_REPORTS", 2);
define("C__REPORT_PAGE__CUSTOM_REPORTS", 3);
define("C__REPORT_PAGE__QUERY_BUILDER", 4);
define("C__REPORT_PAGE__VIEWS", 5);

isys_tenantsettings::extend([
    'LC__MODULE__REPORT' => [
        'report.list.filter' => [
            'title' => 'LC__MODULE__CMDB__DEFAULT_FILTER',
            'type'        => 'select',
            'options'     => [
                'isys_report__id'              => 'ID',
                'isys_report__title'           => 'LC__UNIVERSAL__TITLE',
                'category_title'               => 'LC_UNIVERSAL__CATEGORY',
                'with_qb'                      => 'LC__REPORT__LIST__VIA_QUERY_BUILDER_CREATED',
                'isys_report__category_report' => 'LC__REPORT__FORM__CATEGORY_REPORT',
                'isys_report__description'     => 'LC__UNIVERSAL__DESCRIPTION'
            ],
            'default'     => 'isys_report__id',
            'description' => 'LC__SETTINGS__REPORT__LIST_DEFAULT_FILTER__DESCRIPTION'
        ]
    ]
]);

// Add a few widgets to the dashboard.
isys_register::factory('widget-register')
    ->set('reports', 'isys_dashboard_widgets_reports');

\idoit\Psr4AutoloaderClass::factory()
    ->addNamespace('idoit\Module\Report', __DIR__ . '/src/');
