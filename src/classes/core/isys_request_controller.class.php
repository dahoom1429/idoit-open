<?php

use \idoit\Component\ClassLoader\ModuleLoader;

/**
 * i-doit request controller
 *  responsible for matching routes
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_request_controller
{
    /**
     * URL matching regex
     *
     * @const string
     */
    const URL_MATCH_REGEX = '`(\\\?(?:/|\.|))(\[([^:\]]*+)(?::([^:\]]*+))?\])(\?|)`';

    /**
     * Regex for escaping non-named params ([:named])
     *
     * @const string
     */
    const ESCAPE_REGEX = '`(?<=^|\])[^\]\[\?]+?(?=\[|$)`';

    /**
     * @var isys_request_controller
     */
    private static $m_instance = null;

    /**
     * @var isys_register
     */
    private $m_request = null;

    /**
     * @var isys_route[]
     */
    private $m_routes = null;

    /**
     * @return isys_request_controller
     */
    public static function instance()
    {
        if (!self::$m_instance) {
            self::$m_instance = new isys_request_controller();
        }

        return self::$m_instance;
    }

    /**
     * @return isys_route[]
     */
    public function routes()
    {
        return $this->m_routes;
    }

    /**
     * @return isys_register
     */
    public function request()
    {
        return $this->m_request;
    }

    /**
     * @param string   $p_methods
     * @param string   $p_pattern
     * @param callable $p_function
     *
     * @return isys_request_controller
     */
    public function route($p_methods, $p_pattern, $p_function)
    {
        $this->m_routes[$p_pattern] = new isys_route($p_function, $p_pattern, isys_string::split($p_methods));

        return $this;
    }

    /**
     * You can use it in modules file init.php for creating some routes for single module
     *
     * @param string $methods HTTP methods GET or POST, or GET|POST
     * @param string $pattern Router regular expression
     * @param string $module  Module name
     * @param string $action  Controller name. If it's null name will be taken from pattern's parameter named 'action'
     * @param string $method  Called method name. If it's null name will be taken from pattern's parameter named 'method'
     *
     * @return $this
     */
    public function addModuleRoute($methods, $pattern, $module, $action = null, $method = null)
    {
        $callback = function (\isys_register $request) use ($module, $action, $method) {
            if ($action !== null) {
                $request->action = $action;
            }
            if ($method !== null) {
                $request->method = $method;
            }
            ModuleLoader::factory(isys_application::instance()->container)
                ->boot($module, $request);
        };

        $this->m_routes[$pattern] = new isys_route($callback, $pattern, isys_string::split($methods));

        return $this;
    }

    /**
     * Get the request's path
     *
     * @access public
     * @return string
     */
    public function path()
    {
        $l_uri = $this->m_request->get('REQUEST', '');

        // Strip query string
        $l_uri = strstr($l_uri, '?', true) ?: $l_uri;

        return $l_uri;
    }

    /**
     * Parse routes
     *
     * @throws Exception
     * @return boolean
     */
    public function parse()
    {
        // Initialize cache
        $l_cache = isys_cache::keyvalue();

        // Load Request method and path
        $l_req_method = $this->m_request->get('METHOD', '');
        $l_req_path = $this->path();

        // Disable further processing for index.php requests (there is a problem with the legeacy boot when this is not handled)
        if ($l_req_path === '/index.php') {
            return false;
        }

        // Initialize match response
        $l_match = false;

        /**
         * @var $l_route isys_route
         */
        foreach ($this->routes() as $l_route) {
            // Grab the properties of the route handler
            $l_method = $l_route->method();

            // Check for http method matching
            if ((is_array($l_method) && !in_array($l_req_method, $l_method)) || (is_string($l_method) && !strcasecmp($l_req_method, $l_method))) {
                continue;
            }

            //Re-set matching
            $l_match = false;
            $l_path = $l_route->path();
            $l_params = [];

            if ($l_path === '*') {
                $l_match = true;
            } elseif (isset($l_path[0]) && $l_path[0] === '@') {
                // @ is used to specify custom regex
                $l_match = preg_match('`' . substr($l_path, 1) . '`', $l_req_path, $l_params);
            } else {
                $l_expression = '';
                $l_regex = false;
                $j = 0;
                $i = 0;
                $n = isset($l_path[$i]) ? $l_path[$i] : null;

                // Find the longest non-regex substring and match it against the URI
                while (true) {
                    if (!isset($l_path[$i])) {
                        break;
                    } elseif (false === $l_regex) {
                        $c = $n;
                        $l_regex = $c === '[' || $c === '(' || $c === '.';
                        if (false === $l_regex && false !== isset($l_path[$i + 1])) {
                            $n = $l_path[$i + 1];
                            $l_regex = $n === '?' || $n === '+' || $n === '*' || $n === '{';
                        }

                        if (false === $l_regex && $c !== '/' && (!isset($l_req_path[$j]) || $c !== $l_req_path[$j])) {
                            continue 2;
                        }

                        $j++;
                    }

                    $l_expression .= $l_path[$i++];
                }

                // Catch regex route matching from cache or create it
                if (!($l_regex = $l_cache->get('route:' . $l_expression))) {
                    // Get regex string for matching it against the current route and cache it
                    $l_cache->set('route:' . $l_expression, ($l_regex = $this->compile($l_expression)));
                }

                // Is regex matching needed for this route?
                if ($l_match === false) {
                    $l_params = [];
                    $l_match = preg_match($l_regex, $l_req_path, $l_params);
                }
            }

            if ($l_match) {
                try {
                    if (isset($l_params) && is_array($l_params)) {
                        /**
                         * URL Decode the params according to RFC 3986
                         *
                         * @link http://www.faqs.org/rfcs/rfc3986
                         */
                        $l_params = array_map('rawurldecode', $l_params);

                        // Merge matched params into request register
                        $this->m_request->merge($l_params);
                    }

                    $this->handle($l_route, $this->m_request);
                    break;
                } catch (Exception $e) {
                    throw $e;
                }
            }
        }

        return $l_match;
    }

    /**
     * Compiles a route string to a regular expression
     *
     * @param string $p_route The route string to compile
     *
     * @access protected
     * @return string
     */
    protected function compile($p_route)
    {
        // First escape all of the non-named params (non [block]s) for regex-chars
        $p_route = preg_replace_callback(static::ESCAPE_REGEX, function ($match) {
            return preg_quote($match[0]);
        }, $p_route);

        // Now let's actually compile the path
        if (preg_match_all(static::URL_MATCH_REGEX, $p_route, $l_matches, PREG_SET_ORDER)) {
            $l_match_types = [
                'i'  => '[0-9]++',
                'c'  => '[A-Za-z]++',
                'a'  => '[0-9A-Za-z]++',
                'h'  => '[0-9A-Fa-f]++',
                's'  => '[0-9A-Za-z-_]++',
                '*'  => '.+?',
                '**' => '.++',
                ''   => '[^/]+?'
            ];

            foreach ($l_matches as $l_match) {
                list($l_block, $l_pre, $l_inner_block, $l_type, $l_param, $l_optional) = $l_match;

                if (isset($l_match_types[$l_type])) {
                    $l_type = $l_match_types[$l_type];

                    // Older versions of PCRE require the 'P' in (?P<named>)
                    $l_pattern = '(?:' . ($l_pre !== '' ? $l_pre : null) . '(' . ($l_param !== '' ? "?P<$l_param>" : null) . $l_type . '))' .
                        ($l_optional !== '' ? '?' : null);

                    $p_route = str_replace($l_block, $l_pattern, $p_route);
                }
            }
        }

        return "`^$p_route$`";
    }

    /**
     * @param isys_route $p_route
     */
    private function handle(isys_route $p_route, isys_register $p_request)
    {
        return call_user_func($p_route->callback(), $p_request, $this);
    }

    /**
     * @param array $p_map
     */
    private function __construct()
    {
        global $g_config;

        /**
         * Initialize
         */
        $this->m_routes = new isys_array();

        // @see  ID-7018  If your i-doit subdirectory appears in the Request URI we no longer replace that name but cut the first x characters.
        $scriptName = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
        $relativePath = $_SERVER['REQUEST_URI'];

        if ($scriptName !== '/') {
            $relativePath = substr($_SERVER['REQUEST_URI'], mb_strlen($scriptName));
        }

        // Request register
        $this->m_request = isys_register::factory('request')
            ->set('REQUEST', '/' . ltrim(rawurldecode($relativePath), '/'))
            ->set('BASE', $g_config['www_dir'])
            ->set('METHOD', isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET')
            ->set('SCRIPT_FILENAME', isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '')
            ->set('REMOTE_ADDR', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '')
            ->set('AJAX', isset($_SERVER['REMOTE_ADDR']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ? true : false)
            ->set('POST', new isys_array($_POST ? $_POST : []))
            ->set('GET', new isys_array($_GET ? $_GET : []));
    }
}
