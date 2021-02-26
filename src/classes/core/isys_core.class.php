<?php

/**
 * i-doit core classes
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_core
{
    /**
     * HTTP headers (RFC 2616)
     */
    const HTTP_AcceptEnc      = 'Accept-Encoding';
    const HTTP_Accept         = 'Accept';
    const HTTP_Agent          = 'User-Agent';
    const HTTP_Allow          = 'Allow';
    const HTTP_Cache          = 'Cache-Control';
    const HTTP_Connect        = 'Connection';
    const HTTP_Content        = 'Content-Type';
    const HTTP_Disposition    = 'Content-Disposition';
    const HTTP_Encoding       = 'Content-Encoding';
    const HTTP_Expires        = 'Expires';
    const HTTP_Host           = 'Host';
    const HTTP_IfMod          = 'If-Modified-Since';
    const HTTP_IfNoneMatch    = 'If-None-Match';
    const HTTP_Keep           = 'Keep-Alive';
    const HTTP_LastMod        = 'Last-Modified';
    const HTTP_Length         = 'Content-Length';
    const HTTP_Location       = 'Location';
    const HTTP_Origin         = 'Origin';
    const HTTP_Partial        = 'Accept-Ranges';
    const HTTP_Powered        = 'X-Powered-By';
    const HTTP_RequestedWith  = 'X-Requested-With';
    const HTTP_Pragma         = 'Pragma';
    const HTTP_Referer        = 'Referer';
    const HTTP_Transfer       = 'Content-Transfer-Encoding';
    const HTTP_WebAuth        = 'WWW-Authenticate';
    const HTTP_Authorization  = 'Authorization';
    const HTTP_RPCAuthUser    = 'X-RPC-Auth-Username';
    const HTTP_RPCAuthPass    = 'X-RPC-Auth-Password';
    const HTTP_RPCAuthSession = 'X-RPC-Auth-Session';

    /**
     * HTTP Status codes
     */
    const HTTP_100 = 'Continue';
    const HTTP_101 = 'Switching Protocols';
    const HTTP_200 = 'OK';
    const HTTP_201 = 'Created';
    const HTTP_202 = 'Accepted';
    const HTTP_203 = 'Non-Authorative Information';
    const HTTP_204 = 'No Content';
    const HTTP_205 = 'Reset Content';
    const HTTP_206 = 'Partial Content';
    const HTTP_300 = 'Multiple Choices';
    const HTTP_301 = 'Moved Permanently';
    const HTTP_302 = 'Found';
    const HTTP_303 = 'See Other';
    const HTTP_304 = 'Not Modified';
    const HTTP_305 = 'Use Proxy';
    const HTTP_307 = 'Temporary Redirect';
    const HTTP_400 = 'Bad Request';
    const HTTP_401 = 'Unauthorized';
    const HTTP_402 = 'Payment Required';
    const HTTP_403 = 'Forbidden';
    const HTTP_404 = 'Not Found';
    const HTTP_405 = 'Method Not Allowed';
    const HTTP_406 = 'Not Acceptable';
    const HTTP_407 = 'Proxy Authentication Required';
    const HTTP_408 = 'Request Timeout';
    const HTTP_409 = 'Conflict';
    const HTTP_410 = 'Gone';
    const HTTP_411 = 'Length Required';
    const HTTP_412 = 'Precondition Failed';
    const HTTP_413 = 'Request Entity Too Large';
    const HTTP_414 = 'Request-URI Too Long';
    const HTTP_415 = 'Unsupported Media Type';
    const HTTP_416 = 'Requested Range Not Satisfiable';
    const HTTP_417 = 'Expectation Failed';
    const HTTP_500 = 'Internal Server Error';
    const HTTP_501 = 'Not Implemented';
    const HTTP_502 = 'Bad Gateway';
    const HTTP_503 = 'Service Unavailable';
    const HTTP_504 = 'Gateway Timeout';
    const HTTP_505 = 'HTTP Version Not Supported';

    /**
     * HTTP header storage.
     *
     * @var  array
     */
    private static $m_headers = null;

    /**
     * Installed Apache modules
     *
     * @var array
     */
    private static $m_modules = null;

    /**
     * Return i-doit request URL
     *
     * @param bool $p_include_querystring
     *
     * @return mixed
     */
    static function request_url($p_include_querystring = false)
    {
        if ($p_include_querystring) {
            return $_SERVER['REQUEST_URI'];
        }

        return 'http' . ($_SERVER['HTTPS'] ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . isys_application::instance()->www_path;
    }

    /**
     * Send HTTP status header; Return text equivalent of status code.
     *
     * @param   integer $p_code
     *
     * @return  mixed
     * @throws  Exception
     */
    static function status($p_code)
    {
        if (!defined('self::HTTP_' . $p_code)) {
            throw new Exception(sprintf('HTTP Status code %s not found', $p_code));
        }

        //Get response code.
        $l_response = constant('self::HTTP_' . $p_code);

        //Send HTTP header.
        if (PHP_SAPI != 'cli' && !headers_sent()) {
            header($_SERVER['SERVER_PROTOCOL'] . ' ' . $p_code . ' ' . $l_response);
        }

        return $l_response;
    }

    /**
     * Sends a raw HTTP header
     *
     * @param string $p_header
     * @param string $p_content
     */
    public static function send_header($p_header, $p_content)
    {
        if (!headers_sent()) {
            header($p_header . ': ' . $p_content);

            return true;
        }

        return false;
    }

    /**
     * Retrieve specific header.
     *
     * @param  string  $key
     * @param  boolean $caseSensitive
     *
     * @return mixed
     */
    public static function header($key, $caseSensitive = true)
    {
        if (self::$m_headers === null) {
            self::headers();
        }

        if (!$caseSensitive) {
            $key = strtolower($key);
            $lowerCaseHeaders = array_change_key_case(self::$m_headers, CASE_LOWER);

            return isset($lowerCaseHeaders[$key]) ? $lowerCaseHeaders[$key] : false;
        }

        return isset(self::$m_headers[$key]) ? self::$m_headers[$key] : false;
    }

    /**
     * Retrieve HTTP headers.
     *
     * @return  array
     */
    public static function headers()
    {
        if (PHP_SAPI != 'cli') {
            if (self::$m_headers !== null) {
                return self::$m_headers;
            }

            if (function_exists('getallheaders')) {
                self::$m_headers = getallheaders();
            }

            foreach ($_SERVER as $l_key => $l_value) {
                if (substr($l_key, 0, 5) == 'HTTP_') {
                    self::$m_headers[strtr(ucwords(strtolower(strtr(substr($l_key, 5), '_', ' '))), ' ', '-')] = $l_value;
                }
            }

            return self::$m_headers;
        }

        return [];
    }

    /**
     * Send HTTP header with expiration date (seconds from current time).
     *
     * @param  integer $p_secs
     */
    static function expire($p_secs = 0)
    {
        if (PHP_SAPI != 'cli' && !headers_sent()) {
            header(self::HTTP_Powered . ': i-doit');

            if ($p_secs) {
                $l_time = time();
                $l_req = self::headers();

                header_remove(self::HTTP_Pragma);
                header(self::HTTP_Expires . ': ' . gmdate('r', $l_time + $p_secs));
                header(self::HTTP_Cache . ': max-age=' . $p_secs);
                header(self::HTTP_LastMod . ': ' . gmdate('r'));

                if (isset($l_req[self::HTTP_IfMod]) && strtotime($l_req[self::HTTP_IfMod]) + $p_secs > $l_time) {
                    self::status(304);
                    die;
                }
            } else {
                header(self::HTTP_Cache . ': no-cache, no-store, must-revalidate');
            }
        }
    }

    /**
     * Static method for checking if the current request is a ajax request.
     *
     * @return  boolean
     */
    public static function is_ajax_request()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * Relocated E_WARNING handler to throw ErrorExceptions when a php warning occurred
     *
     * @param int    $p_errno
     * @param string $p_errstr
     * @param string $p_errfile
     * @param int    $p_errline
     * @param array  $p_errcontext
     *
     * @return bool
     * @throws ErrorException
     */
    public static function warning_handler($p_errno, $p_errstr, $p_errfile, $p_errline, array $p_errcontext)
    {
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }

        throw new ErrorException($p_errstr . ' (' . $p_errfile . ':' . $p_errline . ')', 0, $p_errno, $p_errfile, $p_errline);
    }

    /**
     * Post function after system has changed.
     *
     * Gets called after
     *  - a module has been installed
     *  - a module has been uninstalled
     *  - a module has been activated
     *  - a module has been deactivated
     *  - i-doit has been updated
     */
    public static function post_system_has_changed()
    {
        // Save timestamp of last system change.
        isys_settings::set('system.last-change', time());
    }

    /**
     * Checks whether an apache module is installed
     *
     * @param   string $p_module
     *
     * @return  boolean
     */
    public static function is_webserver_module_installed($p_module)
    {
        if (function_exists('apache_get_modules')) {
            if (!self::$m_modules) {
                foreach (apache_get_modules() as $module) {
                    self::$m_modules[$module] = [
                        'active' => true
                    ];
                }
            }

            return isset(self::$m_modules[$p_module]);
        } else if (isset($_SERVER['HTTP_' . strtoupper($p_module)])) {
             // Try to get information from variables setted via .htaccess by webserver
            return $_SERVER['HTTP_' . strtoupper($p_module)] === 'On';
        } else {
            return false;
        }
    }

    /**
     * Checks wheather an apache module is configured. Currently only supports mod_rewrite
     *
     * @param   string $p_module
     *
     * @return  boolean
     * @throws  Exception
     */
    public static function is_webserver_module_configured($p_module = "mod_rewrite")
    {
        if (self::is_webserver_module_installed($p_module)) {
            switch ($p_module) {
                case 'mod_rewrite':
                    if (isset(self::$m_modules[$p_module]['working'])) {
                        return self::$m_modules[$p_module]['working'];
                    }

                    /** Accept ssl certs | @see ID-3150 */
                    $context = stream_context_create([
                        "ssl" => [
                            "verify_peer"      => false,
                            "verify_peer_name" => false,
                        ],
                    ]);

                    // Check on own mod_rewrite URL
                    $response = file_get_contents(self::request_url() . 'system/rewrite-check', null, $context);

                    self::$m_modules[$p_module]['working'] = (strstr($response, 'LC__CMDB__'));

                    return self::$m_modules[$p_module]['working'];

                    break;
            }
        }

        return true;
    }
}