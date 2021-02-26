<?php
/**
 * i-doit
 *
 * Index / Front Controller
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * - https://i-doit.org
 * - https://community.i-doit.com/
 */

// Determine our directory.
$g_absdir = __DIR__;

// Define the current context.
define('WEB_CONTEXT', true);

// Set error reporting.
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING);

// Set default charset to utf-8.
ini_set('default_charset', 'utf-8');

// Set maximal execution time.
if (ini_get('max_execution_time') < 600) {
    set_time_limit(600);
}

/**
 * Dies with a message.
 *
 * @param string $p_message
 */
function startup_die($p_message)
{
    echo '<style>body {background-color:transparent;} .error {background-color:#ffdddd; border:1px solid #ff4343; color: #701719; overflow:auto; padding:10px;}</style>' .
        '<div><img style="float:right; margin-left: 15px; margin-right:5px;" width="100" src="images/logo.png" /><p class="error">' . $p_message . '</p></div>';
    die();
}

function checkModRewriteRequest()
{
    $wwwDir = rtrim(str_replace('index.php', '', $_SERVER['SCRIPT_NAME']), '/') . '/';

    if (explode('?', $_SERVER['REDIRECT_URL'] ?? '/')[0] === $wwwDir . 'mod-rewrite-test') {

        header('Content-Type: application/json');
        $result = [
            'success' => true,
            'data'    => null,
            'message' => ''
        ];
        echo json_encode($result);
        die();
    }
}

// Set the memory limit to at least 128MB.
if ((int)ini_get('memory_limit') < 128) {
    ini_set('memory_limit', '128M');
}

// Set the allowed upload filesize to at least 8MB.
if ((int)ini_get('upload_max_filesize') < 8) {
    ini_set('upload_max_filesize', '8M');
}

// Allow FOPEN Wrapper for URLs.
ini_set('allow_url_fopen', '1');

// Include static contants - or abort, if not possible.
if (!@include_once($g_absdir . '/src/constants.inc.php')) {
    startup_die('Error loading file: ' . $g_absdir . '/src/constants.inc.php');
}

// Check for PHP Version and if it is compatible
$currentPhpVersion = phpversion();

// Check if the minimum PHP version is active and abort if not or if the check not possible.
if (!function_exists('version_compare') || version_compare($currentPhpVersion, PHP_VERSION_MINIMUM, '<')) {
    startup_die('You have PHP ' . $currentPhpVersion . '. You need at least PHP ' . PHP_VERSION_MINIMUM . '.');
}

try {
    // check request for mod_rewrite before config check for installation test
    checkModRewriteRequest();

    // Initialize framework.
    if (file_exists($g_absdir . '/src/config.inc.php') && include_once($g_absdir . '/src/config.inc.php')) {
        // Load the bootstrapping - or abort, if not possible.
        if (!include_once $g_absdir . '/src/bootstrap.inc.php') {
            startup_die('Could not find bootstrap.inc.php');
        }

        // Include caching implementation - or abort, if not possible.
        if (!include_once $g_absdir . '/src/caching.inc.php') {
            startup_die('Could not find caching.inc.php');
        }

        \idoit\Context\Context::instance()->setOrigin(idoit\Context\Context::ORIGIN_GUI);

        global $g_dirs;

        // Temp cleanup.
        if (isset($_GET['IDOIT_DELETE_TEMPLATES_C'])) {
            $g_clear_temp = true;
            $l_directory = $g_dirs['temp'] . 'smarty/';
        }

        if (isset($_GET['IDOIT_DELETE_TEMP'])) {
            $g_clear_temp = true;
            $l_directory = $g_dirs['temp'];
        } elseif (isset($_POST['IDOIT_DELETE_TEMP'])) {
            isys_glob_delete_recursive($g_dirs['temp'], $l_deleted, $l_undeleted);
        }

        if ($g_clear_temp && isset($l_directory)) {
            echo "Deleting temporary files ...<br>\n";

            $l_deleted = 0;
            $l_undeleted = 0;
            isys_glob_delete_recursive($l_directory, $l_deleted, $l_undeleted, (ENVIRONMENT === 'development'));
            echo "Success: $l_deleted files - Failure: $l_undeleted files!<br />\n";

            unset($l_directory);

            if (isset($_GET['ajax'])) {
                die();
            }
        }
    } else {
        if (!require_once $g_absdir . '/setup/install.inc.php') {
            startup_die('Could not start installer. Setup files not found.');
        }
        die();
    }
} catch (Exception $e) {
    if (isset($_SERVER)) {
        isys_glob_display_error(stripslashes(nl2br($e->getMessage())));
    } else {
        printf($e->getMessage());
    }
    die();
}

try {
    // Process ajax requests.
    if (isset($_GET['ajax']) && isys_application::instance()->container->get('session')->is_logged_in()) {
        require_once $g_absdir . '/src/ajax.inc.php';
    }
} catch (Exception $e) {
    if (isset($g_error) && $g_error) {
        isys_notify::error($g_error);
    }
    isys_notify::error($e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ')');
    http_response_code(500);
    die;
}

if (isset($_GET['ajax'], $g_error) && $g_error) {
    http_response_code(500);
}

try {

    // Process api requests.
    if (isset($_GET['api'])) {
        try {
            if ($_GET['api'] === 'jsonrpc') {
                include_once $g_absdir . '/src/jsonrpc.php';
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        die;
    }

    // Main request handler.
    switch ($_GET["load"]) {
        case 'api_properties':
            include_once $g_absdir . '/src/tools/php/properties.inc.php';
            break;

        case 'property_infos':
            include_once $g_absdir . '/src/tools/php/property_infos.inc.php';
            break;

        case 'css':
            include_once $g_absdir . '/src/tools/css/css.php';
            break;

        case 'mod-css':
            include_once $g_absdir . '/src/tools/css/mod-css.php';
            break;

        case 'update':
        default:
            // The hypergate is the i-doit-internal entrypoint, in which all i-doit internal requests are running.
            include_once $g_absdir . '/src/hypergate.inc.php';
            break;
    }
} catch (SmartyException $e) {
    try {
        \idoit\View\ExceptionView::factory()
            ->setDi(isys_application::instance()->container)
            ->draw($e);
    } catch (Exception $e) {
        isys_glob_display_error($e->getMessage());
        die();
    }
} catch (Exception $e) {
    isys_glob_display_error($e->getMessage());
    die();
}
