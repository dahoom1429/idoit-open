<?php
/**
 * i-doit
 *
 * Installer
 * Step 5
 * Config check
 *
 * @package    i-doit
 * @subpackage General
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

function process_after_posttransfer()
{
    global $g_config, $g_tpl_main, $l_next_disabled;
    global $g_osWin, $g_osUNIX;

    $s_steps = "";
    $l_errors = 0;

    $l_dbHost = "";
    $l_dbPort = "";
    $l_dbUser = "";
    $l_dbRootUser = "";
    $l_dbLink = null;

    /* General routine for configuration check */
    foreach ($g_config as $l_key => $l_data) {
        $l_success = false;
        $l_message = "";
        $l_bShowError = true;
        $l_skip = false;

        switch ($l_key) {
            case "idoit.dir":
                if (file_exists($g_config["idoit.dir"]["content"])) {
                    $l_message = "OK";
                } else {
                    $l_message = "ERROR RETRIEVING IDOIT DIR";
                }
                $l_success = true;
                break;
            case "config.dir.src":
                if (is_dir($l_data["content"]) && is_writable($l_data["content"])) {
                    $l_success = true;
                    $l_message = "WRITEABLE";
                } else {
                    $l_success = false;
                    $l_message = "WRITE-PROTECTED";
                }
                break;
            case "config.dir.www":
                $l_success = true;
                $l_message = "OK";

                break;
            case "config.dir.fileman.file":
            case "config.dir.fileman.image":
                // check for terminating DIRECTORY_SEPARATOR
                if ($l_data["content"] != "" && strrpos($l_data["content"], DIRECTORY_SEPARATOR) < (strlen($l_data["content"]) - 1)) {
                    $g_config["$l_key"]["content"] .= DIRECTORY_SEPARATOR;
                }
                // check for folder existence
                if ($l_key == ("config.dir.fileman.temp" || "config.dir.fileman.image") && $l_key != "config.dir.fileman.file" && !file_exists($l_data["content"]) &&
                    file_exists($g_config["config.dir.fileman.file"]["content"])) {
                    mkdir($l_data["content"], 0775);
                }
                if (!file_exists($l_data["content"])) {
                    $l_success = false;
                    $l_message = "DIRECTORY NOT FOUND";
                } elseif (is_dir($l_data["content"])) {
                    if (!is_writable($l_data["content"])) {
                        $l_success = "false";
                        $l_message = "WRITE-PROTECED";
                    } else {
                        $l_success = true;
                        $l_message = "FOUND";
                    }
                } else {
                    $l_success = false;
                    $l_message = "IS NOT A DIRECTORY";
                }
                break;
            case "config.db.host":
                $l_t = gethostbyname($l_data["content"]);
                if (preg_match("/^[0-9.]+$/", $l_t)) {
                    $l_success = true;
                    $l_message = "HOST FOUND";
                    $l_dbHost = $l_t;
                } else {
                    $l_success = false;
                    $l_message = "UNREACHABLE";
                }
                break;
            case "config.db.port":
                $l_t = $l_data["content"];
                if (is_numeric($l_t) && $l_t > 0 && $l_t < 65536) {
                    $l_t = intval($l_t);
                    if (@fsockopen($l_dbHost, $l_t, $t_errno, $t_errstr, 5)) {
                        $l_success = true;
                        $l_message = "CONNECTED";
                        $l_dbPort = $l_t;
                    } else {
                        $l_success = false;
                        $l_message = "ERROR (" . $t_errno . ")";
                    }
                } else {
                    $l_success = false;
                    $l_message = "INVALID";
                }
                break;
            case "config.db.username":
                if (is_string($l_data["content"])) {
                    $l_dbUser = $l_data["content"];
                    $l_success = true;
                    $l_message = "OK";
                }
                break;
            case "config.db.password":
                $l_arPassword2 = $g_config["config.db.password2"];
                $l_strPassword = $l_data["content"];
                $l_strPassword2 = $l_arPassword2["content"];

                if ($l_strPassword == $l_strPassword2) {
                    $l_success = true;
                    $l_message = "OK";
                } else {
                    $l_success = false;
                    $l_message = "retyped password not correct";

                    if ($l_data["content"] != "") {
                        $l_data["content"] = "*****";
                    }
                    break;
                }

                if ($l_data["content"] != "") {
                    $l_data["content"] = "*****";
                }

                break;
            case "config.db.password2":
                $l_success = true;
                $l_bShowError = false;
                break;
            case "config.db.root.username":
                if (is_string($l_data["content"])) {
                    $l_dbRootUser = $l_data["content"];
                    $l_success = true;
                    $l_message = "";
                }
                break;
            case "config.db.root.password":
                $l_cached_sqlmode = '';

                if (!empty($l_dbHost) && !empty($l_dbPort)) {
                    $l_dbLink = new mysqli($l_dbHost, $l_dbRootUser, $l_data["content"], "", $l_dbPort);
                    if (!$l_dbLink->connect_error) {
                        $l_success = true;
                        list($l_cached_sqlmode) = $l_dbLink->query("SELECT @@SESSION.sql_mode;")
                            ->fetch_array();
                        $l_dbLink->query("SET sql_mode=''");

                        $l_mysql = [
                            'innodb_buffer_pool_size' => [
                                0,
                                1024
                            ],
                            'query_cache_size'        => [
                                0,
                                16
                            ],
                            'max_allowed_packet'      => [
                                0,
                                64
                            ],
                            'tmp_table_size'          => [
                                0,
                                16
                            ],
                            'query_cache_limit'       => [
                                0,
                                5
                            ]
                        ];

                        foreach ($l_mysql as $l_mkey => $l_tmp) {
                            list($unused, $l_mysql[$l_mkey][0]) = $l_dbLink->query("SHOW VARIABLES LIKE '" . $l_mkey . "';")
                                ->fetch_array();
                            if (!$l_mysql[$l_mkey][0]) {
                                unset($l_mysql[$l_mkey]);
                            }
                        }

                        $l_db_version = $l_dbLink->query('SELECT VERSION() AS v;')
                            ->fetch_assoc()['v'];
                        $l_is_mariadb = stripos($l_db_version, 'maria') !== false;
                        $l_css_class = 'stepLineStatusGood';
                        $l_message = 'OK';

                        // Setting required parameters for version related checks
                        if ($l_is_mariadb) {
                            $dbTitle = 'MariaDB';
                            $minVersion = MARIADB_VERSION_MINIMUM;
                            $maxVersion = MARIADB_VERSION_MAXIMUM;
                            $recommendedVersion = MARIADB_VERSION_MINIMUM_RECOMMENDED;
                        } else {
                            $dbTitle = 'MySQL';
                            $minVersion = MYSQL_VERSION_MINIMUM;
                            $maxVersion = MYSQL_VERSION_MAXIMUM;
                            $recommendedVersion = MYSQL_VERSION_MINIMUM_RECOMMENDED;
                        }

                        try {
                            // Necessary variables
                            $dbVersionCheckClass = 'stepLineStatusGood';
                            $dbVersionCheckMessage = 'OK';

                            // Check whether version requirements are met
                            if (!checkVersion(getVersion($l_db_version), $minVersion, $maxVersion)) {
                                // Check version is above maximum suppoerted version
                                if (checkVersionIsAbove(getVersion($l_db_version), $maxVersion)) {
                                    $dbVersionCheckClass = 'stepLineStatusBad';
                                    $dbVersionCheckMessage = 'You are about to install i-doit with a MySQL/MariaDB version that is currently not officially supported. ' .
                                        'Please have a look at the official system requirements in the <a href="https://kb.i-doit.com/display/en/System+Requirements">Knowledge Base</a>.';
                                } else {
                                    // DB requirement is unmet!
                                    $dbVersionCheckClass = 'stepLineStatusBad';
                                    $dbVersionCheckMessage = 'Not between ' . $minVersion . ' and ' . $maxVersion . ' - ' . $recommendedVersion . ' recommended.';

                                    // Set this to false to reache installation stop
                                    $l_success = false;
                                }
                            } elseif ($l_is_mariadb && version_compare($l_db_version, MARIADB_VERSION_DEPRECATED_BELOW, '<')) {
                                $dbVersionCheckClass = 'stepLineStatusBad';
                                $dbVersionCheckMessage = 'We discourage the use of ' . $dbTitle . ' version below ' . MARIADB_VERSION_DEPRECATED_BELOW . '.
                                    We will drop support for it in a future release.
                                    Please upgrade ' . $dbTitle . ' to one of the stable versions.
                                    We recommend version ' . $recommendedVersion . ' for the best user experience.';
                            }
                        } catch (Exception $e) {
                            // Database system version identification not possible
                            $dbVersionCheckMessage = 'Please notice that i-doit was not able to determine a valid mysql/mariadDB version information. You can check your system to identify '.
                                'the problem or resume the installation process on your own risk.';
                            $dbVersionCheckClass = 'stepLineStatusBad';
                        }

                        // New checks for MySQL >= 5.6 or MariaDB >= 10.0
                        $s_steps .= '<tr><td></td><td class="stepLineData">' . $dbTitle . ' Version: ' . $l_db_version . '</td><td class="' . $dbVersionCheckClass . '">' . $dbVersionCheckMessage .
                            '</td></tr><tr><td colspan="3" class="stepLineSeperator"></td></tr>';
                    } else {
                        $l_success = false;
                        $l_message = "ERROR: " . $l_dbLink->connect_error;
                    }
                } else {
                    $l_success = false;
                    $l_message = "FAILED";
                }
                if ($l_data["content"] != "") {
                    $l_data["content"] = "*****";
                }

                break;
            case "config.db.root.password2":
                $l_success = true;
                $l_bShowError = false;
                break;
            case "config.db.name":
                if (!$l_dbLink->connect_error) {
                    $l_query = $l_dbLink->query("SHOW DATABASES LIKE '" . $l_data["content"] . "'");
                    if ($l_query->num_rows > 0) {
                        $l_success = false;
                        $l_message = "EXISTS. PLEASE DROP IT";
                    } else {
                        $l_success = true;
                        $l_message = "OK";
                    }
                } else {
                    $l_success = false;
                    $l_message = "NO LINK";
                }

                break;
            case "config.db.config":

                if (!$l_dbLink->connect_error) {
                    $l_success = true;
                    $l_err = false;
                    $l_message = "OK";

                    if (isset($l_mysql) && is_array($l_mysql)) {
                        foreach ($l_mysql as $l_mkey => $l_mconfig) {
                            if ($l_mconfig[0] < ($l_mconfig[1] * 1024 * 1024) && $l_mconfig[1] > 0) {
                                $l_err = true;
                                $l_data["content"] .= "<br><span style='color: #CC0000;'><strong>" . $l_mkey . "</strong> should be at least " . $l_mconfig[1] .
                                    "M (currently " . (number_format(floor($l_mconfig[0]) / 1024 / 1024, 2)) . "M)!</span>";
                            }
                        }
                    }

                    if ($l_err) {
                        $l_message = "<span style='color: #CC0000;'>Warning</span>";
                        $l_data["content"] .= '<br /><br />You should set these database variables in your <a href="https://dev.mysql.com/doc/refman/5.7/en/option-files.html">MySQL config file (my.cnf)</a>.';
                    }
                } else {
                    $l_success = false;
                    $l_message = "NO LINK";
                }
                break;
            case "config.db.mode":
                $l_data["content"] = isset($l_cached_sqlmode) ? $l_cached_sqlmode : '';
                if (is_object($l_dbLink) && !$l_dbLink->connect_error) {
                    $l_success = true;
                    $l_message = "OK";

                    if ($l_cached_sqlmode != "") {
                        $l_message = "<span style='color: #CC0000;'>Warning</span>";
                        $l_data["content"] .= "<br><span style='color: #CC0000;'>Warning: MySQL/MariaDB SQL MODE is not empty. Please disable it!</span>";
                    }
                } else {
                    $l_success = false;
                    $l_message = "NO LINK";
                }
                break;
            case "config.mandant.name":
                if (!$l_dbLink->connect_error) {
                    $l_query = $l_dbLink->query("SHOW DATABASES LIKE '" . $l_data["content"] . "'");
                    if ($l_query->num_rows > 0) {
                        $l_success = false;
                        $l_message = "EXISTS. PLEASE DROP IT";
                    } else {
                        $l_success = true;
                        $l_message = "OK";
                    }
                } else {
                    $l_success = false;
                    $l_message = "NO LINK";
                }

                break;
            case "config.mandant.title":
                if (is_string($l_data["content"]) && !empty($l_data["content"])) {
                    $l_success = true;
                    $l_message = "OK";
                } else {
                    $l_success = false;
                    $l_message = "INVALID";
                }
                break;
            case "config.mandant.autoinc":
                if (is_numeric($l_data["content"]) && (int)$l_data["content"] > 0) {
                    $l_success = true;
                    $l_message = "OK";
                } else {
                    $l_success = false;
                    $l_message = "INVALID";
                }
                break;
            case "config.adminauth.password":
                if ($l_data["content"] != "") {
                    $l_data["content"] = "*****";
                }
                $l_success = true;
                break;
            case "config.license.token":
                $l_success = true;
                break;
            default:
                $l_success = true;
                break;
        }

        if ($l_skip) {
            continue;
        }

        if ($l_data["content"] == "") {
            $l_data["content"] = "<i>n/a</i>";
        }

        if ($l_bShowError) {
            if ($l_data["name"] != "") {
                $s_steps .= "<tr>" . "<td>&nbsp;</td>" . "<td class=\"stepLineData\">" . $l_data["name"] . ": " . $l_data["content"] . "</td>" . "<td class=\"" .
                    (($l_success) ? "stepLineStatusGood" : "stepLineStatusBad") . "\">$l_message</td>" . "</tr>";
                $s_steps .= "<tr>" . "<td colspan=\"3\" class=\"stepLineSeperator\">" . "</td>" . "</tr>";
            }
        }

        if ($l_success == false) {
            $l_errors++;
        }
    }

    $l_next_disabled = !!($l_errors);

    if ($l_errors > 0) {
        $l_errorstr = $l_errors . " errors occured. Please fix the configuration in order to continue!";
    } else {
        $l_errorstr = "0 errors occured! OK - <b>i-doit</b> is ready for installation. Warming up the engines ...";
    }

    tpl_set($g_tpl_main, [
        "FRAMEWORK_CONFIG_ERRORS" => $l_errorstr,
        "FRAMEWORK_CONFIG_STEPS"  => $s_steps,
        "IDOIT_ABSDIR"            => dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR
    ]);
}
