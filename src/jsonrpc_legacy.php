<?php

try {
    define('WEB_CONTEXT', false);

    /* Include minimal runtime environment if this script is called directly */
    if (!isset($g_absdir)) {
        // Set error reporting.
        $l_errorReporting = E_ALL & ~E_NOTICE;
        if (defined('E_DEPRECATED')) {
            $l_errorReporting &= ~E_DEPRECATED;
        }

        if (defined('E_STRICT')) {
            $l_errorReporting &= ~E_STRICT;
        }

        error_reporting($l_errorReporting);
        $g_absdir = dirname(dirname(__FILE__));

        // Include config.
        if (file_exists("config.inc.php") && include_once("config.inc.php")) {
            // Include global and caching environment.
            include_once("bootstrap.inc.php");
            include_once("caching.inc.php");
        }
    }

    if (!class_exists("isys_locale")) {
        require_once "locales.inc.php";
    }

    if (file_exists(__DIR__ . '/classes/modules/api/init.php')) {
        require_once __DIR__ . '/classes/modules/api/init.php';

        // Call request controller.
        if (class_exists('isys_api_controller_jsonrpc')) {
            // @see   ID-934
            // @todo  Remove in i-doit 1.12
            $categoryConstantMatching = [
                'C__CMDB__SUBCAT__NETWORK_PORT'                      => 'C__CATG__NETWORK_PORT',
                'C__CMDB__SUBCAT__NETWORK_INTERFACE_P'               => 'C__CATG__NETWORK_INTERFACE',
                'C__CMDB__SUBCAT__NETWORK_INTERFACE_L'               => 'C__CATG__NETWORK_LOG_PORT',
                'C__CMDB__SUBCAT__NETWORK_PORT_OVERVIEW'             => 'C__CATG__NETWORK_PORT_OVERVIEW',
                'C__CMDB__SUBCAT__STORAGE__DEVICE'                   => 'C__CATG__STORAGE_DEVICE',
                'C__CMDB__SUBCAT__LICENCE_LIST'                      => 'C__CATS__LICENCE_LIST',
                'C__CMDB__SUBCAT__LICENCE_OVERVIEW'                  => 'C__CATS__LICENCE_OVERVIEW',
                'C__CMDB__SUBCAT__EMERGENCY_PLAN_LINKED_OBJECT_LIST' => 'C__CATS__EMERGENCY_PLAN_LINKED_OBJECTS',
                'C__CMDB__SUBCAT__EMERGENCY_PLAN'                    => 'C__CATS__EMERGENCY_PLAN_ATTRIBUTE',
                'C__CMDB__SUBCAT__WS_NET_TYPE'                       => 'C__CATS__WS_NET_TYPE',
                'C__CMDB__SUBCAT__WS_ASSIGNMENT'                     => 'C__CATS__WS_ASSIGNMENT',
                'C__CMDB__SUBCAT__FILE_OBJECTS'                      => 'C__CATS__FILE_OBJECTS',
                'C__CMDB__SUBCAT__FILE_VERSIONS'                     => 'C__CATS__FILE_VERSIONS',
                'C__CMDB__SUBCAT__FILE_ACTUAL'                       => 'C__CATS__FILE_ACTUAL'
            ];

            $request = Symfony\Component\HttpFoundation\Request::createFromGlobals();

            // Read JSON HTTP body from input stream.
            $l_api = new isys_api_controller_jsonrpc(strtr($request->getContent(), $categoryConstantMatching));

            // Handle the API call.
            $l_api->handle();
        } else {
            throw new Exception('Error: i-doit is unavailable.');
        }
    } else {
        throw new Exception('Api Module is not available.');
    }

} catch (Exception $e) {
    echo json_encode([
        'id'      => 0,
        'jsonrpc' => '2.0',
        'error'   => [
            'code'    => -1,
            'message' => $e->getMessage(),
            'data'    => null
        ]
    ]);
}
die;
