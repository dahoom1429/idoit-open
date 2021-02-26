<?php
/**
 * i-doit Proxy implementation using cURL Extension
 *
 * @author  Dennis Stücken <dstuecken@synetics.de>
 * @version 1.1
 * @desc    Fetching AJAX requests via www
 * @package i-doit Report Manager
 * @uses    cURL Extension
 *
 */
error_reporting(E_ALL & ~E_NOTICE);

// Some config initializations.
$g_absdir = __DIR__ . '/';

if (!defined('ISYS_LANGUAGE_ENGLISH')) {
    define('ISYS_LANGUAGE_ENGLISH', 1);
}

try {
    // Get i-doit configuration (for proxy settings).
    include_once($g_absdir . 'src/config.inc.php');
    include_once($g_absdir . 'src/functions.inc.php');
    include_once($g_absdir . 'src/autoload.inc.php');
    include_once($g_absdir . 'src/constants.inc.php');

    isys_application::instance()
        ->language(null)
        ->bootstrap();

    if (!extension_loaded('curl')) {
        die('<strong>PHP curl extension is needed for fetching the reports via HTTP!<br /><br />Install/activate php-module: curl first.</strong>');
    }

    // ---------------------------------------------------------------------
    $l_path = (isset($_POST['path'])) ? $_POST['path'] : $_GET['path'];
    $_POST['version'] = $_GET['version'];
    $l_url = isys_settings::get('reports.browser-url', 'https://reports-ng.i-doit.org/') . $l_path;
    // ---------------------------------------------------------------------
    $l_sess_curl = curl_init($l_url);
    // ---------------------------------------------------------------------
    if (isys_settings::get('proxy.active', false)) {
        // curl_setopt($l_sess_curl, CURLOPT_HTTPPROXYTUNNEL, 1);
        curl_setopt($l_sess_curl, CURLOPT_PROXY, isys_settings::get('proxy.host') . ':' . isys_settings::get('proxy.port'));

        if (isys_settings::get('proxy.username', false)) {
            curl_setopt($l_sess_curl, CURLOPT_PROXYUSERPWD, isys_settings::get('proxy.username') . ':' . isys_settings::get('proxy.password'));
        }
    }

    // ---------------------------------------------------------------------
    // Process post parameters */
    // ---------------------------------------------------------------------
    if (is_array($_POST) && isset($_GET["path"])) {
        $l_posts = "";
        foreach ($_POST as $l_key => $l_value) {
            $l_posts .= $l_key . "=" . $l_value . "&";
        }
        rtrim($l_posts, "&");

        curl_setopt($l_sess_curl, CURLOPT_POST, true);
        curl_setopt($l_sess_curl, CURLOPT_POSTFIELDS, $l_posts);
    }

    // ---------------------------------------------------------------------
    // Set cURL-Options */
    // ---------------------------------------------------------------------
    curl_setopt($l_sess_curl, CURLOPT_HEADER, false);
    curl_setopt($l_sess_curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($l_sess_curl, CURLOPT_SSL_VERIFYPEER, false);

    // ---------------------------------------------------------------------
    // Perform cURL.Session */
    // ---------------------------------------------------------------------
    $l_responseTEXT = curl_exec($l_sess_curl);

    $l_error = curl_error($l_sess_curl);
    if (!empty($l_error)) {
        $l_proxy_config = '';

        if (isys_settings::get('proxy.active')) {
            $l_proxy_config = str_replace('array', '<strong>Proxy configuration</strong>: ', isys_settings::get('proxy.host') . ':' . isys_settings::get('proxy.port'));
        }

        $l_error_message = "<strong>Error while connecting</strong>: " . curl_errno($l_sess_curl) . " - " . $l_error . "<br />\n\n" . "<strong>URL</strong>: " . $l_url .
            "<br />" . $l_proxy_config;
        die($l_error_message);
    }

    // ---------------------------------------------------------------------
    // Set Content-Type and do output */
    // ---------------------------------------------------------------------
    if (isset($_POST["json"])) {
        header('Content-Type: application/json');
    } else {
        header('Content-Type: text/html');
    }

    echo $l_responseTEXT;

    // Close cURL Session.
    curl_close($l_sess_curl);
} catch (Exception $e) {
    echo $e->getMessage();
}
