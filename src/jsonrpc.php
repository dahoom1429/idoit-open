<?php

/**
 * JsonRPC
 *
 * @package    i-doit
 * @subpackage API
 * @author     Selcuk Kekec <skekec@i-doit.de>
 * @version    1.10
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

// Set error reporting.
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING);

include_once __DIR__ . '/../vendor/autoload.php';

use idoit\Context\Context;

Context::instance()->setOrigin(Context::ORIGIN_API);

// Check if api is present
if (file_exists(__DIR__ . '/classes/modules/api/jsonrpc.php')) {
    // Calculate idoit root directory
    $idoitRootDirectory = dirname(__DIR__);

    // Include api entry point file
    include_once __DIR__ . '/classes/modules/api/jsonrpc.php';
} else if (file_exists(__DIR__ . '/jsonrpc_legacy.php')) {
    // Calculate idoit root directory
    $idoitRootDirectory = dirname(__DIR__);

    // Include legacy api entry point file
    include_once __DIR__ . '/jsonrpc_legacy.php';
} else {
    /**
     * Inform caller that api is not present
     */
    echo json_encode([
        'id'      => 0,
        'jsonrpc' => '2.0',
        'error'   => [
            'code'    => -1,
            'message' => 'Api Module is not available.',
            'data'    => null
        ]
    ]);

    die;
}
