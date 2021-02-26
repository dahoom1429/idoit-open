<?php

/**
 * i-doit - class autoloader
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

/**
 * The autoloader for our classes.
 *
 * @param string $className
 *
 * @return boolean
 */
function isys_autoload($className)
{
    try {
        global $g_dirs;

        $g_dirs['class'] = __DIR__ . '/classes/';

        // Check for autoload-cache.
        include_once __DIR__ . '/caching.inc.php';

        if (($path = isys_caching::factory('autoload')->get($className)) && is_readable($path)) {
            include_once $path;

            return true;
        }

        // The following classes begin all with 'isys_' - so we can skip everything that does not start with this string.
        if (strpos($className, 'isys_') !== 0) {
            return false;
        }

        $path = false;

        // @todo  This complete 'if/else' block should be removable since all classes SHOULD be found via classmap.
        if (strpos($className, 'isys_exception') === 0) {
            $path = $g_dirs['class'] . 'exceptions/';
        } else {
            if (strpos($className, 'isys_library') === 0) {
                $path = $g_dirs['class'] . 'libraries/';
            } else {
                if (strpos($className, 'isys_protocol') === 0) {
                    $path = $g_dirs['class'] . 'protocol/';
                } else {
                    if (strpos($className, 'isys_connector') === 0) {
                        if (strpos($className, 'isys_connector_ticketing') === 0) {
                            $path = $g_dirs['class'] . 'connector/ticketing/';
                        } else {
                            $path = $g_dirs['class'] . 'connector/';
                        }
                    } else {
                        if (strpos($className, 'isys_smarty') === 0) {
                            $path = $g_dirs['class'] . 'smarty/';
                        } else {
                            if ($className === 'isys_module' || $className === 'isys_module_dao' || $className === 'isys_module_interface') {
                                $path = $g_dirs['class'] . 'modules/';
                            } else {
                                if (strpos($className, 'isys_module') === 0) {
                                    $path = $g_dirs['class'] . 'modules/' . substr($className, 12) . '/';
                                } else {
                                    if (strpos($className, 'isys_component') === 0) {
                                        $path = $g_dirs['class'] . 'components/';
                                    } else {
                                        if (strpos($className, 'isys_format') === 0) {
                                            $path = $g_dirs['class'] . 'format/';
                                        } else {
                                            if (strpos($className, 'isys_contact') === 0) {
                                                if (strpos($className, 'isys_contact_dao') === 0) {
                                                    $path = $g_dirs['class'] . 'contact/dao/';
                                                }
                                            } else {
                                                if (strpos($className, 'isys_ajax') === 0) {
                                                    if (strpos($className, 'isys_ajax_handler') === 0) {
                                                        $path = $g_dirs['class'] . 'ajax/handler/';
                                                    } else {
                                                        $path = $g_dirs['class'] . 'ajax/';
                                                    }
                                                } else {
                                                    if (strpos($className, 'isys_auth') === 0) {
                                                        if (strpos($className, 'isys_auth_dao_') === 0 || strpos($className, 'isys_auth_module_dao') === 0) {
                                                            $path = $g_dirs['class'] . 'auth/dao/';
                                                        } else {
                                                            $path = $g_dirs['class'] . 'auth/';
                                                        }
                                                    } else {
                                                        if (strpos($className, 'isys_import') === 0) {
                                                            if (!isset($g_dirs['import'])) {
                                                                $g_dirs['import'] = $g_dirs['class'] . 'import/';
                                                            }

                                                            if (strpos($className, 'isys_import_handler') === 0) {
                                                                $path = $g_dirs['import'] . 'handler/';
                                                            } else {
                                                                $path = $g_dirs['import'];
                                                            }
                                                        } else {
                                                            if (strpos($className, 'isys_export') === 0) {
                                                                if (strpos($className, 'isys_export_type') === 0) {
                                                                    $path = $g_dirs['class'] . 'export/type/';
                                                                } else {
                                                                    if (strpos($className, 'isys_export_cmdb') === 0) {
                                                                        $path = $g_dirs['class'] . 'export/cmdb/';
                                                                    } else {
                                                                        if (strpos($className, 'isys_export_csv') === 0) {
                                                                            $path = $g_dirs['class'] . 'export/csv/';
                                                                        } else {
                                                                            $path = $g_dirs['class'] . 'export/';
                                                                        }
                                                                    }
                                                                }
                                                            } else {
                                                                if (strpos($className, 'isys_factory') === 0) {
                                                                    $path = $g_dirs['class'] . 'factory/';
                                                                } else {
                                                                    if (strpos($className, 'isys_log') === 0) {
                                                                        $path = $g_dirs['class'] . 'log/';
                                                                    } else {
                                                                        if (strpos($className, 'isys_notification') === 0) {
                                                                            $path = $g_dirs['class'] . 'notification/';
                                                                        } else {
                                                                            if (strpos($className, 'isys_report') === 0) {
                                                                                if (strpos($className, 'isys_report_view') === 0) {
                                                                                    $path = $g_dirs['class'] . 'report/views/';
                                                                                } else {
                                                                                    $path = $g_dirs['class'] . 'report/';
                                                                                }
                                                                            } else {
                                                                                if (strpos($className, 'isys_event_cmdb') === 0) {
                                                                                    $path = $g_dirs['class'] . 'event/cmdb/';
                                                                                } else {
                                                                                    if (strpos($className, 'isys_event_task') === 0) {
                                                                                        $path = $g_dirs['class'] . 'event/task/';
                                                                                    } else {
                                                                                        if (strpos($className, 'isys_event') === 0) {
                                                                                            $path = $g_dirs['class'] . 'event/';
                                                                                        } else {
                                                                                            if (strpos($className, 'isys_widget') === 0) {
                                                                                                $path = $g_dirs['class'] . 'widgets/';
                                                                                            } else {
                                                                                                if (strpos($className, 'isys_tree') === 0) {
                                                                                                    $path = $g_dirs['class'] . 'tree/';
                                                                                                } else {
                                                                                                    if (strpos($className, 'isys_graph') === 0) {
                                                                                                        $path = $g_dirs['class'] . 'graph/';
                                                                                                    } else {
                                                                                                        if (strpos($className, 'isys_handler') === 0) {
                                                                                                            $path = $g_dirs['handler'];
                                                                                                        } else {
                                                                                                            if (strpos($className, 'isys_workflow') === 0) {
                                                                                                                if (strpos($className, 'isys_workflow_dao_list') === 0) {
                                                                                                                    $path = $g_dirs['class'] . 'workflow/dao/list/';
                                                                                                                } else {
                                                                                                                    if (strpos($className, 'isys_workflow_dao') === 0) {
                                                                                                                        $path = $g_dirs['class'] . 'workflow/dao/';
                                                                                                                    } else {
                                                                                                                        if (strpos($className, 'isys_workflow_view') === 0) {
                                                                                                                            $path = $g_dirs['class'] . 'workflow/view/';
                                                                                                                        } else {
                                                                                                                            if (strpos($className, 'isys_workflow_action') === 0) {
                                                                                                                                $path = $g_dirs['class'] . 'workflow/action/';
                                                                                                                            } else {
                                                                                                                                $path = $g_dirs['class'] . 'workflow/';
                                                                                                                            }
                                                                                                                        }
                                                                                                                    }
                                                                                                                }
                                                                                                            } else {
                                                                                                                if (0 === strpos($className, 'isys_popup')) {
                                                                                                                    $path = $g_dirs['class'] . 'popups/';
                                                                                                                } else {
                                                                                                                    if (0 === strpos($className, 'isys_helper')) {
                                                                                                                        $path = $g_dirs['class'] . 'helper/';
                                                                                                                    } else {
                                                                                                                        if (0 === strpos($className, 'isys_cache')) {
                                                                                                                            $path = $g_dirs['class'] . 'cache/';
                                                                                                                        } else {
                                                                                                                            if (0 === strpos($className, 'isys_application') ||
                                                                                                                                0 === strpos($className, 'isys_callback') ||
                                                                                                                                0 === strpos($className, 'isys_request') ||
                                                                                                                                0 === strpos($className, 'isys_register') ||
                                                                                                                                0 === strpos($className, 'isys_notify') ||
                                                                                                                                0 === strpos($className, 'isys_settings') ||
                                                                                                                                0 === strpos($className, 'isys_array') ||
                                                                                                                                0 === strpos($className, 'isys_core') ||
                                                                                                                                0 === strpos($className, 'isys_tenant') ||
                                                                                                                                0 === strpos($className, 'isys_route') ||
                                                                                                                                0 === strpos($className, 'isys_request_controller') ||
                                                                                                                                0 === strpos($className, 'isys_string') ||
                                                                                                                                0 === strpos($className, 'isys_controller') ||
                                                                                                                                0 === strpos($className, 'isys_tenantsettings') ||
                                                                                                                                0 === strpos($className, 'isys_usersettings')) {
                                                                                                                                $path = $g_dirs['class'] . 'core/';
                                                                                                                            } else {
                                                                                                                                if (0 === strpos($className, 'isys_update')) {
                                                                                                                                    $path = $l_base_dir . '/updates/classes/';
                                                                                                                                } else {
                                                                                                                                    if (0 === strpos($className, 'isys_')) {
                                                                                                                                        $path = $g_dirs['class'] . 'isys/';
                                                                                                                                    }
                                                                                                                                }
                                                                                                                            }
                                                                                                                        }
                                                                                                                    }
                                                                                                                }
                                                                                                            }
                                                                                                        }
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // Check if the path is set.
        if ($path) {
            // Include the file or handle the error.
            if (file_exists($path . $className . '.class.php') && include_once($path . $className . '.class.php')) {
                // Add the new file to the autoloader.
                isys_caching::factory('autoload')->set($className, $path . $className . '.class.php');

                return true;
            }
        }

        return false;
    } catch (ErrorException $e) {
        die($e->getMessage());
    }
}

// Include composer's autoloader
$vendorDir = dirname(__DIR__) . '/vendor/';
if (file_exists($vendorDir . 'autoload.php')) {
    include_once $vendorDir . 'autoload.php';
} else {
    throw new Exception("Composers autoloader not found in {$vendorDir}. Composer may not initialized! Run 'composer install' in root directory! (https://getcomposer.org)");
}
unset($vendorDir);

try {
    // Use symfonys classmap loader, if a classmap is available.
    if (!file_exists(__DIR__ . '/classmap.inc.php')) {
        throw new \Exception('Classmap file does not exist.');
    }

    \idoit\Component\ClassLoader\MapClassLoader::factory(include_once(__DIR__ . '/classmap.inc.php'), dirname(__DIR__) . '/')->register(true);

    // Register autoloader for isys_module classes
    spl_autoload_register(function ($classname) {
        $classname = str_replace('\\', '', $classname);
        if (strpos($classname, 'isys_module') === 0) {
            $path = isys_application::instance()->app_path . '/src/classes/modules/' . substr($classname, 12) . '/';
            if (file_exists($path . $classname . '.class.php') && include_once($path . $classname . '.class.php')) {
                return true;
            }
        }

        return false;
    });
} catch (\Exception $e) {
    include_once __DIR__ . '/classes/modules/manager/isys_module_manager_autoload.class.php';
    include_once __DIR__ . '/autoload-psr4.inc.php';
}

// Fallback to legacy behaviour, if needed
spl_autoload_register('isys_autoload', false, false);

$loader = \idoit\Psr4AutoloaderClass::factory()->register();
