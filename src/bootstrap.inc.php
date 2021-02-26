<?php
/**
 * i-doit
 *
 * Global definitions.
 *
 * This file provides basic functionalities needed by all source files.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

// Load the version
include_once __DIR__ . '/version.inc.php';

// Get localization class
include_once __DIR__ . '/locales.inc.php';

// Include global functions.
include_once __DIR__ . '/functions.inc.php';

// Include debug functions, if available.
if (file_exists(__DIR__ . '/debug_functions.php')) {
    include_once __DIR__ . '/debug_functions.php';
}

// Include global constants.
include_once __DIR__ . '/constants.inc.php';

// Include autoloader
include_once __DIR__ . '/autoload.inc.php';

// Include convert class
include_once __DIR__ . '/convert.inc.php';

global $g_absdir;

$g_config = [
    'base_dir'      => $g_absdir . DIRECTORY_SEPARATOR,
    'www_dir'       => rtrim(str_replace(['src/jsonrpc.php', 'index.php'], '', @$_SERVER['SCRIPT_NAME']), '/') . '/',
    'theme'         => 'default',
    'startpage'     => 'index.php',
    'html-encoding' => 'utf-8'
];

mb_internal_encoding($g_config['html-encoding']);

// Initialize global directory configuration
if (!isset($g_dirs)) {
    $g_dirs = [];
}

// Global error/exception message.
$g_error = '';

// Internal smarty/template config.
$g_template = [
    'start_page' => 'main.tpl',
    'ajax'       => 'ajax.tpl'
];

// Get global converter.
$g_convert = new isys_convert();

// If this is set to true, there is no template display at all
$g_output_done = false;

// Call bootstrapping and load all required components
isys_application::instance()
    ->language(isset($_GET['lang']) ? $_GET['lang'] : null)
    ->bootstrap();
