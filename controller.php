<?php

/**
 * i-doit
 *
 * System Controller
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
try {
    if (!empty($_SERVER['HTTP_HOST'])) {
        define('WEB_CONTEXT', true);
    }
    // Set error reporting.
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING);

    // Set maximal execution time.
    set_time_limit(0);

    // Reserve 256MB as maximal memory usage for this PHP session.
    if ((int)ini_get("memory_limit") < 256) {
        ini_set("memory_limit", "256M");
    }

    // Get current directory.
    $g_absdir = dirname(__FILE__);
    chdir($g_absdir);

    if (substr(php_uname(), 0, 7) == "Windows") {
        define("C__WINDOWS", true);
    } else {
        define("C__WINDOWS", false);
    }

    // Bash colors.
    define("C__COLOR__WHITE", (C__WINDOWS || $_SERVER["HTTP_HOST"]) ? "" : "\033[1;37m");
    define("C__COLOR__BLACK", (C__WINDOWS || $_SERVER["HTTP_HOST"]) ? "" : "\033[0;30m");
    define("C__COLOR__BLUE", (C__WINDOWS || $_SERVER["HTTP_HOST"]) ? "" : "\033[0;34m");
    define("C__COLOR__GREEN", (C__WINDOWS || $_SERVER["HTTP_HOST"]) ? "" : "\033[0;32m");
    define("C__COLOR__CYAN", (C__WINDOWS || $_SERVER["HTTP_HOST"]) ? "" : "\033[0;36m");
    define("C__COLOR__RED", (C__WINDOWS || $_SERVER["HTTP_HOST"]) ? "" : "\033[0;31m");
    define("C__COLOR__PURPLE", (C__WINDOWS || $_SERVER["HTTP_HOST"]) ? "" : "\033[0;35m");
    define("C__COLOR__BROWN", (C__WINDOWS || $_SERVER["HTTP_HOST"]) ? "" : "\033[0;33m");
    define("C__COLOR__LIGHT_GRAY", (C__WINDOWS || $_SERVER["HTTP_HOST"]) ? "" : "\033[0;37m");
    define("C__COLOR__DARK_GRAY", (C__WINDOWS || $_SERVER["HTTP_HOST"]) ? "" : "\033[1;30m");
    define("C__COLOR__LIGHT_BLUE", (C__WINDOWS || $_SERVER["HTTP_HOST"]) ? "" : "\033[1;34m");
    define("C__COLOR__LIGHT_GREEN", (C__WINDOWS || $_SERVER["HTTP_HOST"]) ? "" : "\033[1;32m");
    define("C__COLOR__LIGHT_CYAN", (C__WINDOWS || $_SERVER["HTTP_HOST"]) ? "" : "\033[1;36m");
    define("C__COLOR__LIGHT_RED", (C__WINDOWS || $_SERVER["HTTP_HOST"]) ? "" : "\033[1;31m");
    define("C__COLOR__LIGHT_PURPLE", (C__WINDOWS || $_SERVER["HTTP_HOST"]) ? "" : "\033[1;35m");
    define("C__COLOR__YELLOW", (C__WINDOWS || $_SERVER["HTTP_HOST"]) ? "" : "\033[1;33m");
    define("C__COLOR__NO_COLOR", (C__WINDOWS || $_SERVER["HTTP_HOST"]) ? "" : "\033[0m");

    // Console logos.
    define("C__CONSOLE_LOGO__IDOIT", C__COLOR__WHITE . "i-do" . C__COLOR__LIGHT_RED . "it" . C__COLOR__NO_COLOR);

    // This is plain text. And UTF-8.
    header("Content-Type: text/plain");
    header("Content-Type: UTF-8");

    // Globalize g_controller.
    $g_controller = null;

    if (file_exists($g_absdir . "/src/config.inc.php")) {
        // Get config file.
        include_once $g_absdir . "/src/config.inc.php";

        /**
         * Display loading points (progress).
         *
         * @param  boolean $p_newline
         * @param  string  $p_str
         */
        function loading($p_newline = false, $p_str = ".")
        {
            if (defined("ISYS_VERBOSE")) {
                echo $p_str;

                if ($p_newline == true) {
                    echo "\n";
                }
            }
        }

        /**
         * Display message in verbose mode.
         *
         * @param  string  $p_message
         * @param  boolean $p_newline
         * @param  string  $p_star
         */
        function verbose($p_message, $p_newline = true, $p_star = "")
        {
            if (defined("ISYS_VERBOSE")) {
                echo $p_newline ? "\n" : '';

                if ($p_star) {
                    echo "[" . $p_star . "] ";
                }

                echo $p_message;
            }
        }

        /**
         * Display error message and die
         *
         * @param  string $p_message
         */
        function error($p_message, $p_star = '')
        {
            $l_message = '';

            if ($p_star != false) {
                $l_message .= "\n[" . $p_star . "] " . $p_message;
            } else {
                $l_message .= "\n" . $p_message;
            }

            if (!defined("ISYS_VERBOSE")) {
                $l_message .= "\nTry verbose mode to get more information (-v)\n";
            }

            echo $l_message;
            die();
        }

        /**
         * Parse installed handlers and register them
         *
         * @global  array $g_dirs
         * @global  array $g_controller
         */
        function get_handlers()
        {
            global $g_dirs, $g_controller;

            if (!isset($g_controller["handler"]) || !is_array($g_controller["handler"])) {
                $g_controller["handler"] = [];
            }

            $l_dir = opendir($g_dirs["handler"]);
            if (is_resource($l_dir)) {
                while ($l_file = readdir($l_dir)) {
                    if (is_file($g_dirs["handler"] . DIRECTORY_SEPARATOR . $l_file) && preg_match("/^(isys_handler_(.*))\.class\.php$/i", $l_file, $l_register)) {
                        $g_controller["handler"][$l_register[2]] = ["class" => $l_register[1]];
                    }
                }
            }

            closedir($l_dir);
        }

        /**
         * Get usage information
         *
         * @global  array  $g_controller
         *
         * @param   string $p_message
         *
         * @return  string
         */
        function get_usage($p_message = null)
        {
            global $g_controller;
            ksort($g_controller["handler"]);

            if (is_array($g_controller["handler"])) {
                $l_handlers = implode(', ', array_keys($g_controller["handler"]));
            } else {
                $l_handlers = "Currently there are no handlers installed.";
            }

            return $p_message . "\n\n" . "Usage: " . $_SERVER["PHP_SELF"] . " [OPTION] [PARAMETERS]\n" . "e.g.:  " . $_SERVER["PHP_SELF"] . " -v -m workflow\n" .
                "Options:\n" . "  -m HANDLER   Load handler HANDLER module.\n" . "  -u username  i-doit username\n" . "  -p password  i-doit password\n" .
                "  -i tenant    ID of tenant to connect to (use './tenants ls' for a list)\n" . "  -h           This help text\n" . "  -v           Verbose mode\n" .
                "  -d           Displays ALL debug messages\n" . "\n" . "HANDLER can be one of the following availlable handlers:\n" . $l_handlers . "\n\n";
        }

        // Globalize _get and _post variables.
        $g_get = $_GET;
        $g_post = $_POST;

        // Get globals.
        include_once("src/bootstrap.inc.php");

        global $g_comp_session;

        try {
            // Get available handler modules and make them accessable in $g_controller["handler"].
            get_handlers();

            // @todo May be removed because session is already initiated via bootstrap.inc.php.
            if (($g_comp_session instanceof isys_component_session) === false) {
                $g_comp_session = isys_component_session::instance(null, isys_tenantsettings::get('session'));
            }

            // Works only if handler specific options are not in between these options
            $l_opt = getopt("u:p:i:vm:hd");

            // Because i-doit's XML import uses this controller in the WebGUI (WTF?) getopt will fail:
            if ($l_opt === false) {
                // Enforce empty array:
                $l_opt = [];
            }

            $l_call_from_cli = true;

            if (isset($g_get["load"])) {
                $l_call_from_cli = false;
                $g_load = $g_get["load"];

                if (isset($g_get["verbose"])) {
                    define("ISYS_VERBOSE", true);
                }

                if (!isset($argv)) {
                    $argv = [];
                }
            } else {
                if (isset($argv)) {
                    if (isset($l_opt["d"])) {
                        define("ISYS_DEBUG", true);
                    }

                    $g_load = $l_opt["m"];

                    if (isset($l_opt["u"]) && !isset($l_opt["p"])) {
                        $l_error = ("The password cannot be empty! Define a password with -p in order to login.");
                    }

                    /**
                     * Remove controller options from arguments:
                     */

                    $argv = array_slice($argv, 1);
                    $l_shorts = ['-v'];
                    $l_longs = [
                        '-u',
                        '-p',
                        '-i',
                        '-m'
                    ];
                    $l_args = [];

                    $argumentsCount = count($argv);
                    for ($l_i = 0;$l_i < $argumentsCount;$l_i++) {
                        if (in_array($argv[$l_i], $l_shorts)) {
                            // e.g. "-v":
                            continue;
                        } else {
                            if (in_array($argv[$l_i], $l_longs)) {
                                // e.g. "-u admin":
                                $l_i++;
                                continue;
                            } else {
                                // e.g. "-uadmin":
                                foreach ($l_longs as $l_long) {
                                    if (strpos($argv[$l_i], $l_long) === 0) {
                                        continue(2);
                                    }
                                }
                            }
                        }

                        $l_args[] = $argv[$l_i];
                    }

                    $argv = $l_args;
                } else {
                    die("Missing parameter: 'load'.");
                }
            }

            if (isset($l_opt["v"])) {
                if (!defined('ISYS_VERBOSE')) {
                    define("ISYS_VERBOSE", true);
                }
            }

            $g_handler_config = $g_dirs["handler"] . "config/isys_handler_" . $g_load . ".inc.php";

            // If handler config exists, include it.
            if (file_exists($g_handler_config)) {
                include_once $g_handler_config;
            }

            if (isset($g_userconf['user']) && isset($g_userconf['pass'])) {
                $l_opt = $l_opt + [
                        'u' => $g_userconf['user'],
                        'p' => $g_userconf['pass'],
                        'i' => $g_userconf['mandator_id'],
                    ];
            }

            if (isset($l_opt["u"]) && !isset($l_opt["p"])) {
                $l_error = ("The password cannot be empty! Define a password with -p in order to login.");
            }

            if (isset($l_opt["u"]) && !isset($l_opt["i"])) {
                if (C__WINDOWS) {
                    $l_mandator_exec = "php.exe controller.php -v -m tenants";
                } else {
                    $l_mandator_exec = "./tenants";
                }

                $l_error = ("Don't forget to specify your tenant id (-i). You can view the current ids with \"" . $l_mandator_exec . " ls\"\n");
            }

            if (!isset($l_error)) {
                if (isset($l_opt["u"]) && isset($l_opt["p"])) {
                    $languageManager = isys_application::instance()->container->get('language');

                    verbose('Logging in..', false);
                    $l_logged_in = $g_comp_session->weblogin($l_opt['u'], $l_opt['p'], $l_opt['i']);
                    if ($l_logged_in) {
                        verbose("Connected to tenant: " . $g_comp_session->get_mandator_name() . " (" . $g_comp_session->get_mandator_id() . ", user: " .
                            $g_comp_session->get_current_username() . ")\n");
                        // Re-set the language (if necessary).
                        $l_lang = $g_comp_session->get_language();
                        if (!$l_lang) {
                            $l_lang = isys_locale::get_instance()
                                ->resolve_language_by_constant(isys_locale::get_instance()
                                    ->get_setting(LC_LANG)) ?: 'en';
                        }
                        if (isys_application::instance()->language != $l_lang || $languageManager->get_loaded_language() != $l_lang) {
                            $g_comp_session->set_language($l_lang);
                            isys_application::instance()
                                ->language($l_lang);
                            $languageManager->load($l_lang);
                            $languageManager->load_custom($l_lang);
                        }
                    } else {
                        $l_login_error = C__COLOR__LIGHT_RED . 'Could not login with the used login data.' . C__COLOR__NO_COLOR;
                    }
                } else {
                    $l_login_error = C__COLOR__LIGHT_RED . 'Could not login: Username or password not set.' . C__COLOR__NO_COLOR;
                }
            }

            global $g_comp_database;

            /**
             * Load modules
             */
            isys_module_manager::instance()
                ->init(isys_module_request::get_instance());

            // We'll load the custom language file, after the modules have been loaded.
            isys_application::instance()->container->get('language')
                ->load_custom();

            // Encapsulate handler.
            $l_handler_class = $g_controller["handler"][$g_load]["class"];

            if ($l_handler_class) {
                $g_handler_config = $g_dirs["handler"] . "config/" . $l_handler_class . ".inc.php";

                // If handler config exists, include it.
                if (file_exists($g_handler_config)) {
                    include_once $g_handler_config;
                }
            }

            if (isset($l_error)) {
                error($l_error);
            }

            if (!$g_load) {
                die(get_usage());
            }

            $g_mandator = $l_opt["i"];
        } catch (Exception $e) {
            error(C__COLOR__LIGHT_RED . $e->getMessage() . C__COLOR__NO_COLOR . "\n");
        }

        try {
            if (isset($l_handler_class)) {
                global $g_comp_database, $g_load;

                // Include handler.
                if ($l_handler_class && class_exists($l_handler_class)) {
                    /**
                     * Get handler class.
                     *
                     * @var $l_object isys_handler
                     */
                    $l_object = new $l_handler_class();

                    if ($l_object->needs_login() && !$g_comp_session->is_logged_in()) {
                        if (isset($l_login_error)) {
                            verbose($l_login_error);
                        }
                        error("Login failed.\n");
                    }

                    if ($g_comp_session->is_logged_in()) {
                        // Load tenant specific cache.
                        $g_comp_session->include_mandator_cache();

                        if (!isys_auth_system::instance()
                            ->is_allowed_to(isys_auth::EXECUTE, 'CONTROLLERHANDLER/' . strtoupper($l_handler_class))) {
                            throw new isys_exception_auth('No rights to execute controller handler \'' . $g_load . '\'.');
                        }
                    }

                    // Initialize handler.
                    $l_object->init();

                    // Logging out only if from cli
                    if ($l_call_from_cli) {
                        $g_comp_session->logout();
                    }

                    die;
                }
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    } else {
        die(C__COLOR__RED . $g_absdir . "/src/config.inc.php not found." . C__COLOR__NO_COLOR . "\nYou need to install i-doit first.\n");
    }

    if (isset($g_load)) {
        die(get_usage("\n\n" . C__COLOR__LIGHT_RED . "Handler: " . $g_load . " is not installed." . C__COLOR__NO_COLOR));
    }
} catch (Exception $e) {
    printf($e->getMessage());
}
