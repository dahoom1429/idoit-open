<?php

use idoit\AddOn\AuthableInterface;

/**
 * i-doit
 *
 * Module manager.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_manager extends isys_module implements isys_module_interface
{
    /**
     * @var bool
     */
    protected static $m_licenced = true;

    // Define, if this module shall be displayed in the system-settings.
    const DISPLAY_IN_MAIN_MENU = false;

    // Define, if this module shall be displayed in the extras menu.
    const DISPLAY_IN_SYSTEM_MENU = false;

    /**
     * @var bool
     */
    private static $m_modules_loaded = false;

    /**
     * Array with modules which are on trial
     *
     * @var array
     */
    private static $m_trials = [];

    /**
     * Array with module register entries.
     *
     * @var  isys_module_register[]
     */
    protected $m_installed = [];

    /**
     * ID of active module.
     *
     * @var  integer
     */
    private $m_activemod;

    /**
     * Array with data source information.
     *
     * @var  array
     */
    private $m_datasource;

    /**
     * Array of initialized modules (init.php)
     *
     * @var array
     */
    private $m_initialized = [];

    /**
     * Array with module register entries.
     *
     * @var  isys_module_register[]
     */
    private $m_modules;

    /**
     * Cache of isys_module database table
     *
     * @var array
     */
    private $m_modules_cache = [];

    /**
     * Module request.
     *
     * @var  isys_module_request
     */
    private $m_userrequest;

    /**
     * Singleton instance of this class.
     *
     * @var  isys_module_manager
     */
    private static $m_instance = null;

    /**
     * Module auth instances
     *
     * @var array
     */
    private $m_module_auth = [];

    /**
     * @var isys_component_database
     */
    protected $database = null;

    /**
     * @return isys_module_request
     */
    public function get_request()
    {
        return $this->m_userrequest;
    }

    /**
     * Initializes the module manager
     *
     * @param   isys_module_request &$p_req
     *
     * @return  isys_module_manager
     */
    public function init(isys_module_request $p_req)
    {
        include_once('init.php');

        if (is_object($p_req)) {
            isys_component_signalcollection::get_instance()
                ->connect('system.afterChange', [
                    'isys_core',
                    'post_system_has_changed'
                ]);

            $this->m_userrequest = &$p_req;
        }

        if ($this->database == null) {
            $this->database = isys_application::instance()->container->database;
        }

        /**
         * Initialize cmdb dao based on given database component
         */
        if (is_object($this->database)) {
            $this->m_dao = isys_component_dao::instance($this->database);
        }

        /**
         * Call module loader
         */
        $this->module_loader();

        return $this;
    }

    /**
     * Return initialized modules
     */
    public function get_initialized_modules()
    {
        return $this->m_initialized;
    }

    /**
     * @param $p_module_identifier
     *
     * @return bool
     */
    public static function is_trial($p_module_identifier)
    {
        if (isset(self::$m_trials[$p_module_identifier])) {
            return self::$m_trials[$p_module_identifier];
        }

        return false;
    }

    /**
     * Uninstall add-on by identifier.
     *
     * @param       $p_identifier
     * @param array $p_mandatorDBs
     * @param array $errorMessages
     *
     * @return bool
     * @throws Exception
     */
    public function uninstallAddOn($p_identifier, array $p_mandatorDBs, array &$errorMessages)
    {
        /* Get dao instance */
        $l_log = isys_log::get_instance($p_identifier . '-uninstall');

        try {
            $l_log->notice('Uninstalling ' . $p_identifier);
            $l_log->set_auto_flush(true);

            $l_module_dir = dirname(__DIR__) . '/';
            $l_path = $l_module_dir . $p_identifier . '/';
            $l_moduleTitle = null;

            if (file_exists($l_path) && file_exists($l_path . 'package.json')) {
                /**
                 * Parse package.json
                 */
                $l_package = json_decode(file_get_contents($l_path . 'package.json'), true);

                if ($l_package) {
                    if (isset($l_package['type'])) {
                        if ($l_package['type'] == 'addon') {
                            global $g_comp_database;
                            $l_log->notice(sprintf('package.json initialized'));

                            /**
                             * Uninstall in current mandator only if we received an empty array
                             */
                            if (count($p_mandatorDBs) === 0) {
                                $p_mandatorDBs[] = $this->database;
                            }

                            // Keep the current Database component
                            $currentDB = ($g_comp_database ?: isys_application::instance()->container->database);

                            /**
                             * Call uninstall method and drop tables in all mandators
                             */
                            foreach ($p_mandatorDBs as $l_mandatorDB) {
                                // overwrite database component in container
                                isys_application::instance()->container->set('database', $l_mandatorDB);
                                // @todo: this must be removed soon. Because we already set database above for isys_application
                                $g_comp_database = $l_mandatorDB;

                                $mandatorModuleManager = new self($l_mandatorDB);
                                $l_module = $mandatorModuleManager->get_modules(null, null, null, ' AND isys_module__identifier = \'' . addslashes($p_identifier) . '\'')
                                    ->__to_array();

                                if ($l_module && $l_module[$this->m_datasource['table'] . "__id"] >= 1010) {
                                    $moduleClassName = 'isys_module_' . $p_identifier;
                                    /* Call custom uninstall method of module */
                                    if (class_exists($moduleClassName)) {
                                        if (is_a($moduleClassName, 'idoit\AddOn\InstallableInterface', true)) {
                                            $moduleClassName::uninstall($l_mandatorDB);
                                        } elseif (is_callable([$moduleClassName, 'uninstall'])) {
                                            // @todo: this must be removed soon. we should send db object here
                                            $l_log->notice(sprintf(
                                                'Calling uninstall method for mandator %s in %s',
                                                $l_mandatorDB->get_db_name(),
                                                'isys_module_' . $p_identifier
                                            ));
                                            call_user_func([$moduleClassName, 'uninstall']);
                                        //turn back global database
                                        } else {
                                            $l_log->notice(sprintf('Uninstall method in %s does not exist. Skipping custom uninstall.', 'isys_module_' . $p_identifier));
                                        }
                                    }

                                    $l_dao = isys_component_dao::instance($l_mandatorDB);
                                    $l_dao->begin_update();
                                    $l_dao->update('SET FOREIGN_KEY_CHECKS = 0;');

                                    /* Drop sql tables */
                                    if (isset($l_package['sql-tables']) && is_array($l_package['sql-tables'])) {
                                        $l_log->notice(sprintf('Dropping %d tables in mandator database %s..', count($l_package['sql-tables']), $l_mandatorDB->get_db_name()));

                                        foreach ($l_package['sql-tables'] as $l_table) {
                                            if ($l_dao->update('DROP TABLE IF EXISTS `' . $l_table . '`;')) {
                                                $l_log->notice(sprintf('%s dropped.', $l_table));
                                            }
                                        }
                                    } else {
                                        $l_log->notice('No sql-tables array found in package.json. Skipping standardized table drop.');
                                    }

                                    /* Delete module entry */
                                    if ($mandatorModuleManager->delete($p_identifier)) {
                                        $l_log->notice('Uninstall was successfully for mandator db ' . $l_mandatorDB->get_db_name() . '.');
                                        if ($l_moduleTitle === null) {
                                            $l_moduleTitle = isys_application::instance()->container->get('language')
                                                ->get($l_module[$this->m_datasource['table'] . "__title"]);
                                        }
                                    } else {
                                        throw new Exception("Could not delete module with identifier: " . $p_identifier . "<br />" .
                                            $l_mandatorDB->get_last_error_as_string());
                                    }

                                    $l_dao->apply_update();
                                } else {
                                    $errorMessage = 'Module ' . $p_identifier . ' not found in mandator db ' . $l_mandatorDB->get_db_name() .
                                        '. Skipped uninstall for mandator db ' . $l_mandatorDB->get_db_name() . '.';
                                    $l_log->error($errorMessage);
                                    $errorMessages[] = $errorMessage;
                                }
                            }

                            //turn back global database
                            isys_application::instance()->container->set('database', $currentDB);
                            // @todo this must be removed if we don´t need the global variable anymore
                            $g_comp_database = $currentDB;

                            /* Delete files */
                            if (isset($l_package['files']) && is_array($l_package['files'])) {
                                $l_log->notice(sprintf('Removing %d files and directories..', count($l_package['files'])));

                                $l_package['files'][] = 'package.json';
                                $l_directories_to_delete = [];

                                /* Delete all module related files */
                                foreach ($l_package['files'] as $l_file) {
                                    if (file_exists($l_path . $l_file)) {
                                        if (is_writable($l_path . $l_file)) {
                                            if (is_file($l_path . $l_file)) {
                                                if (@unlink($l_path . $l_file)) {
                                                    $l_log->notice(sprintf('%s deleted (file)', $l_path . $l_file));
                                                } else {
                                                    $errorMessage = sprintf('Error deleting file %s. Check your permissions.', $l_path . $l_file);
                                                    $l_log->error($errorMessage);
                                                    $errorMessages[] = $errorMessage;
                                                }
                                            } else {
                                                if (is_dir($l_path . $l_file)) {
                                                    $l_directories_to_delete[] = $l_path . $l_file;
                                                }
                                            }
                                        } else {
                                            $errorMessage = 'Could not delete ' . $l_path . $l_file . ': not allowed';
                                            $l_log->error($errorMessage);
                                            $errorMessages[] = $errorMessage;
                                        }
                                    } else {
                                        $errorMessage = $l_path . $l_file . ' was not found.';
                                        $l_log->error($errorMessage);
                                        $errorMessages[] = $errorMessage;
                                    }
                                }

                                /* Mark module directory for deletion at last */
                                $l_directories_to_delete[] = $l_path;

                                /* Delete (hopefully empty) directories as last step */
                                foreach ($l_directories_to_delete as $l_dir) {
                                    /* Check if directory is empty (exactly 2 file because of '.' and '..')*/
                                    $files = scandir($l_dir);
                                    if (is_countable($files) && count($files) === 2) {
                                        if (@rmdir($l_dir)) {
                                            $l_log->notice(sprintf('%s deleted (dir)', $l_dir));
                                        }
                                    } else {
                                        $errorMessage = sprintf('Could not delete %s since it is not empty!', $l_dir);
                                        $l_log->warning($errorMessage);
                                        $errorMessages[] = $errorMessage;
                                    }
                                }
                                $files = scandir($l_module_dir);
                                /* Last but not least, try to delete the module directory */
                                if (is_countable($files) && count($files) === 0) {
                                    rmdir($l_module_dir);
                                }
                            } else {
                                $l_log->notice('No files array found in package.json. Skipping standardized file deletion.');
                            }

                            // Call system has changed post notification.
                            $this->afterChange();

                            return ($l_moduleTitle !== null) ? $l_moduleTitle : $p_identifier;
                        } else {
                            $errorMessage = 'Could not delete module. Only addon modules can be uninstalled.';
                            $l_log->warning($errorMessage);
                            $errorMessages[] = $errorMessage;
                        }
                    } else {
                        throw new Exception('Could not delete module: package.json structure invalid.');
                    }
                }
            } else {
                $errorMessage = 'package.json for module ' . $p_identifier . ' not found. Module was not successfully uninstalled.';
                $l_log->warning($errorMessage);
                $errorMessages[] = $errorMessage;
            }

            return false;
        } catch (Exception $e) {
            /* Cancel transaction */
            if (isset($l_dao) && is_object($l_dao)) {
                $l_dao->cancel_update();
            }

            $errorMessage = 'Error while uninstalling module ' . $p_identifier . ':' . $e->getMessage();
            $l_log->error($errorMessage);
            $errorMessages[] = $errorMessage;

            throw $e;
        }
    }

    /**
     * @param                          $p_identifier
     *
     * @return mixed
     * @global isys_component_database $g_comp_database
     */
    public function delete($p_identifier)
    {
        $l_sql = "DELETE FROM " . $this->m_datasource['table'] . " WHERE " . $this->m_datasource['table'] . "__identifier = " . $this->m_dao->convert_sql_text($p_identifier) . ";";

        return $this->database->query($l_sql);
    }

    /**
     * Install module to database ($p_package = package.json content).
     *
     * @param  array $p_package
     *
     * @return bool|int
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    public function installAddOn(array $p_package)
    {
        if (is_array($p_package) && isset($p_package['identifier'])) {
            $l_dao = isys_component_dao::instance($this->database);

            if (strstr(' ', $p_package['identifier'])) {
                throw new isys_exception_general('Wrong module identifier in package.json. Spaces not allowed.');
            }

            if (isset($p_package['icon']) && is_scalar($p_package['icon'])) {
                $l_icon = $p_package['icon'];
            } else {
                if (isset($p_package['icons']['16'])) {
                    $l_icon = $p_package['icons']['16'];
                } else {
                    $l_icon = '';
                }
            }

            if (!($l_id = $this->is_installed($p_package['identifier']))) {
                $l_sql = 'INSERT INTO ' . $this->m_datasource['table'] . ' SET ' . $this->m_datasource['table'] . '__title = \'' .
                    (isset($p_package['name']) ? $p_package['name'] : $p_package['title']) . '\', ' . $this->m_datasource['table'] . '__identifier = \'' .
                    $p_package['identifier'] . '\', ' . $this->m_datasource['table'] . '__icon = \'' . $l_icon . '\', ' . $this->m_datasource['table'] .
                    '__const = \'C__MODULE__' . strtoupper($p_package['identifier']) . '\', ' . $this->m_datasource['table'] . '__persistent = \'' .
                    (isset($p_package['persistent']) ? $p_package['persistent'] : '1') . '\', ' . $this->m_datasource['table'] . '__class = \'isys_module_' .
                    $p_package['identifier'] . '\', ' . $this->m_datasource['table'] . '__status = \'' . C__RECORD_STATUS__NORMAL . '\', ' . $this->m_datasource['table'] .
                    '__date_install = NOW()';

                if ($l_dao->update($l_sql)) {
                    $l_last_id = $l_dao->get_last_insert_id();
                    $l_parent_module_id = null;

                    if (isset($p_package['parent']) && !empty($p_package['parent'])) {
                        $l_parent_module_res = $this->get_modules(null, $p_package['parent']);
                        if ($l_parent_module_res->num_rows() > 0) {
                            $l_parent_module = $l_parent_module_res->get_row();
                            $l_parent_module_id = $l_parent_module['isys_module__id'];
                        }
                    }
                    $this->set_parent_module($l_last_id, $l_parent_module_id);

                    // Call system has changed post notification.
                    $this->afterChange();

                    return $l_last_id;
                }
            } else {
                if ($l_id > 0) {
                    $l_sql = 'UPDATE ' . $this->m_datasource['table'] . ' SET ' . $this->m_datasource['table'] . '__title = \'' .
                        (isset($p_package['name']) ? $p_package['name'] : $p_package['title']) . '\', ' . $this->m_datasource['table'] . '__identifier = \'' .
                        $p_package['identifier'] . '\', ' . $this->m_datasource['table'] . '__const = \'C__MODULE__' . strtoupper($p_package['identifier']) . '\', ' .
                        $this->m_datasource['table'] . '__icon = \'' . $l_icon . '\', ' . $this->m_datasource['table'] . '__date_install = NOW(), ' .
                        $this->m_datasource['table'] . '__persistent = \'' . (isset($p_package['persistent']) ? $p_package['persistent'] : '1') . '\', ' .
                        $this->m_datasource['table'] . '__class = \'isys_module_' . $p_package['identifier'] . '\', ' . $this->m_datasource['table'] . '__status = \'' .
                        C__RECORD_STATUS__NORMAL . '\'';

                    $l_sql .= ' WHERE ' . $this->m_datasource['table'] . '__id = \'' . $l_id . '\'';

                    if ($l_dao->update($l_sql)) {
                        $l_parent_module_id = null;

                        if (isset($p_package['parent']) && !empty($p_package['parent'])) {
                            $l_parent_module_res = $this->get_modules(null, $p_package['parent']);
                            if ($l_parent_module_res->num_rows() > 0) {
                                $l_parent_module = $l_parent_module_res->get_row();
                                $l_parent_module_id = $l_parent_module['isys_module__id'];
                            }
                        }
                        $this->set_parent_module($l_id, $l_parent_module_id);

                        return $l_id;
                    }
                }
            }
        } else {
            throw new isys_exception_general('Could not install module: Invalid package received in module manager.');
        }

        return false;
    }

    /**
     * Sets the parent module for the current module.
     *
     * @param   $p_child_module_id
     * @param   $p_parent_module_id
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_parent_module($p_child_module_id, $p_parent_module_id)
    {
        $l_sql = 'UPDATE ' . $this->m_datasource['table'] . ' 
            SET ' . $this->m_datasource['table'] . '__parent = ' . $this->m_dao->convert_sql_id($p_parent_module_id) . ' 
            WHERE ' . $this->m_datasource['table'] . '__id = ' . $this->m_dao->convert_sql_id($p_child_module_id) . ';';

        return (bool) $this->database->query($l_sql);
    }

    /**
     * Activate a add-on.
     *
     * @param $p_identifier
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function activateAddOn($p_identifier)
    {
        $l_sql = "UPDATE " . $this->m_datasource['table'] . "
			SET " . $this->m_datasource['table'] . "__status = " . $this->m_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . "
			WHERE " . $this->m_datasource['table'] . "__identifier = " . $this->m_dao->convert_sql_text($p_identifier) . ";";

        if ($this->m_dao->update($l_sql) && $this->m_dao->apply_update()) {
            $moduleClassName = 'isys_module_' . $p_identifier;
            if (class_exists($moduleClassName)) {
                if (is_a($moduleClassName, 'idoit\AddOn\ActivatableInterface', true)) {
                    $moduleClassName::activate($this->database);
                } else {
                    global $g_comp_database;

                    $g_comp_database = $this->database;
                    @call_user_func('isys_module_' . $p_identifier . '::activate', $p_identifier);
                    $g_comp_database = isys_application::instance()->container->database;
                }
            }

            // Call system has changed post notification.
            $this->afterChange();

            return true;
        }

        return false;
    }

    /**
     * Deactivate a add-on
     *
     * @param $p_identifier
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function deactivateAddOn($p_identifier)
    {
        $l_sql = "UPDATE " . $this->m_datasource['table'] . "
			SET " . $this->m_datasource['table'] . "__status = " . $this->m_dao->convert_sql_int(C__RECORD_STATUS__ARCHIVED) . "
			WHERE " . $this->m_datasource['table'] . "__identifier = " . $this->m_dao->convert_sql_text($p_identifier) . ";";

        if ($this->m_dao->update($l_sql) && $this->m_dao->apply_update()) {
            $moduleClassName = 'isys_module_' . $p_identifier;
            if (class_exists($moduleClassName)) {
                if (is_a($moduleClassName, 'idoit\AddOn\ActivatableInterface', true)) {
                    $moduleClassName::deactivate($this->database);
                } else {
                    global $g_comp_database;

                    $g_comp_database = $this->database;
                    @call_user_func('isys_module_' . $p_identifier . '::deactivate', $p_identifier);
                    $g_comp_database = isys_application::instance()->container->database;
                }
            }

            // Call system has changed post notification.
            $this->afterChange();

            return true;
        }

        return false;
    }

    /**
     * Checks wheather a module is installed or not, should return the module id.
     *
     * @param string $p_identifier
     * @param bool   $p_and_active
     *
     * @return int]false
     */
    public function is_installed($p_identifier = null, $p_and_active = false)
    {
        if ($p_identifier) {
            if (!is_countable($this->m_installed) || !count($this->m_installed)) {
                $this->get_installed_modules();
            }

            if ($this->m_installed) {
                if (isset($this->m_installed[$p_identifier])) {
                    if ($p_and_active) {
                        if (!$this->m_installed[$p_identifier]['active']) {
                            return false;
                        }
                    }

                    return $this->m_installed[$p_identifier]['id'];
                }
            } else {
                // fallback
                // @todo my remove this in future (1.8)?
                if (!is_object($this->database)) {
                    return false;
                }

                $l_sql = 'SELECT ' . $this->m_datasource['table'] . '__id AS id
                    FROM ' . $this->m_datasource['table'] . '
                    WHERE ' . $this->m_datasource['table'] . '__identifier = ' . $this->m_dao->convert_sql_text($p_identifier);

                if ($p_and_active) {
                    $l_sql .= ' AND ' . $this->m_datasource['table'] . '__status = ' . $this->m_dao->convert_sql_int(C__RECORD_STATUS__NORMAL);
                }

                $l_id = $this->m_dao->retrieve($l_sql . ';')->get_row_value('id');

                return $l_id ? $l_id : false;
            }
        }

        return false;
    }

    /**
     * @param   string $p_identifier
     *
     * @return  boolean
     */
    public function is_active($p_identifier)
    {
        return $this->is_installed($p_identifier, true);
    }

    /**
     * @desc Starts module process
     */
    public function start()
    {
        ;
    }

    /**
     * Get single isys_modules row
     *
     * @param $p_id
     *
     * @return mixed
     */
    public function get_module_by_id($p_id)
    {
        return $this->m_modules_cache[$p_id];
    }

    /**
     * Get single isys_modules row
     *
     * @param $p_identifier
     *
     * @return mixed
     */
    public function get_module_by_identifier($p_identifier)
    {
        if (isset($this->m_installed[$p_identifier]['id'])) {
            return $this->m_modules_cache[$this->m_installed[$p_identifier]['id']];
        }

        return null;
    }

    /**
     * Method for retrieving rows from "isys_module".
     *
     * @param   integer $id
     * @param   string  $constant
     * @param   integer $active
     * @param   string  $condition
     *
     * @throws  isys_exception_database
     * @return  isys_component_dao_result
     */
    public function get_modules($id = null, $constant = null, $active = null, $condition = "")
    {
        if (!is_object($this->database)) {
            throw new isys_exception_database("Error. Database component not loaded.", [], 0, true);
        }

        $sql = 'SELECT ' . $this->m_datasource['table'] . '__id AS id, t_mod.*
            FROM ' . $this->m_datasource['table'] . ' AS t_mod
            WHERE TRUE';

        if ($id != null) {
            $sql .= ' AND ' . $this->m_datasource['table'] . '__id =  ' . $this->m_dao->convert_sql_id($id);
        }

        if ($constant != null) {
            $sql .= ' AND ' . $this->m_datasource['table'] . '__const = ' . $this->m_dao->convert_sql_text($constant);
        }

        if ($active) {
            $sql .= ' AND ' . $this->m_datasource['table'] . '__status = ' . $this->m_dao->convert_sql_int(C__RECORD_STATUS__NORMAL);
        }

        return $this->m_dao->retrieve($sql . ' ' . $condition . ' ORDER BY ' . $this->m_datasource['table'] . '__title ASC;');
    }

    /**
     * Enumerates all available modules by querying the module table.
     *
     * @param   boolean $p_include_inactive
     *
     * @throws  isys_exception_general
     * @return  integer
     */
    public function enum($p_include_inactive = false)
    {
        $l_enumerated = 0;
        $l_res = $this->get_modules();

        if ($l_res && $l_res->num_rows() > 0) {
            while ($l_row = $l_res->get_row(IDOIT_C__DAO_RESULT_TYPE_ARRAY)) {
                $l_mod_id = $l_row["id"];

                if ($p_include_inactive || $l_row["{$this->m_datasource['table']}__status"] == C__RECORD_STATUS__NORMAL) {
                    if (!$this->register($l_mod_id, $l_row)) {
                        throw new isys_exception_general("Could not register module $l_mod_id: " . var_export($l_row, true));
                    }

                    $l_enumerated++;
                }
            }
        }

        return $l_enumerated;
    }

    /**
     * @param $p_table
     */
    public function configure_datasource($p_table)
    {
        $this->m_datasource["table"] = $p_table;
    }

    /**
     * @param $p_field
     *
     * @return mixed
     */
    public function get_datasource($p_field)
    {
        return $this->m_datasource[$p_field];
    }

    /**
     * Function to initialize modules in specified directory
     *
     * @param string $p_directory
     */
    private function load_modules_in_directory($p_directory = 'src/classes/modules/')
    {
        if ($l_dirhandle = opendir($p_directory)) {
            while (($l_file = readdir($l_dirhandle)) !== false) {
                if (is_dir($p_directory . $l_file) && strpos($l_file, '.') !== 0) {
                    try {
                        if (file_exists($p_directory . $l_file . '/init.php')) {
                            include_once($p_directory . $l_file . '/init.php');

                            $this->m_initialized[str_replace($p_directory, '', $l_file)] = $p_directory . $l_file . '/init.php';
                        }
                    } catch (isys_exception_database $e) {
                        ;
                    } catch (Exception $e) {
                        $GLOBALS['g_error'] .= $e->getMessage() . "\n";
                        isys_application::instance()->container->get('logger')->critical($e->getMessage());
                    }
                }
            }

            closedir($l_dirhandle);
        }
    }

    /**
     * Calls a slot registration method for every persistent module
     *
     * @param bool $forceReload
     *
     * @return $this
     */
    public function module_loader(bool $forceReload = false)
    {
        try {
            /*
             * In some specific cases (for example "API") the licence info was not yet set inside the session,
             * so we might need to re-run this code to.
             */
            if (self::$m_modules_loaded && !$forceReload) {
                return $this;
            }

            // Set licence info for licenced modules.
            if (isset($_SESSION['licensed_addons'])) {
                foreach ($_SESSION['licensed_addons'] as $identifier => $addon) {
                    $moduleClassName = strtolower('isys_module_' . $identifier);
                    if (class_exists($moduleClassName)) {
                        if (is_a($moduleClassName, 'idoit\AddOn\LicensableInterface', true)) {
                            /**
                             * @var $moduleClassName idoit\AddOn\LicensableInterface
                             */
                            $moduleClassName::setLicensed($addon['licensed']);
                        } else {
                            /**
                             * @var $moduleClassName isys_module
                             */
                            $moduleClassName::set_licenced($addon['licensed']);
                        }
                    }
                }
            }

            // Initialize modules.
            $this->load_modules_in_directory(isys_application::instance()->app_path . '/src/classes/modules/');

            // Also check for to-be-initialized composer modules
            if (file_exists(isys_application::instance()->app_path . '/vendor/synetics/')) {
                $this->load_modules_in_directory(isys_application::instance()->app_path . '/vendor/synetics/');
            }

            //if ($p_load_persistant)
            {
                $l_modules = $this->get_modules(null, null, null, " AND isys_module__persistent = 1 AND isys_module__status = " . (int)C__RECORD_STATUS__NORMAL);

                while ($l_row = $l_modules->get_row()) {
                    if (class_exists($l_row["isys_module__class"])) {
                        // Register module.
                        $this->register($l_row['isys_module__id'], $l_row);

                        // Call initslots method.
                        call_user_func([
                            new $l_row["isys_module__class"](),
                            "initslots"
                        ]);
                    }
                }
            }

            $this->get_installed_modules();
        } catch (isys_exception_database $e) {
            ;
        } catch (Exception $e) {
            isys_notify::debug($e->getMessage());
        }

        self::$m_modules_loaded = true;

        return $this;
    }

    /**
     * Registers a module
     *
     * @param integer $p_id
     * @param array   $p_data
     *
     * @return boolean
     */
    public function register($p_id, $p_data)
    {
        // Create a module register entry.
        if (!is_value_in_constants($p_id, ['C__MODULE__MANAGER'])) {
            $l_regobj = new isys_module_register($p_id, $p_data, $this);
        } else {
            // If the module to be registered is the module manager, add $this to the register.
            $l_regobj = new isys_module_register($p_id, $p_data, $this, true, $this);
        }

        // Good, we have the register entry.
        if ($l_regobj) {
            // Append to register list.
            $this->m_modules[$p_id] = &$l_regobj;

            // Query register entry in order to create the object and pre-initialize the module.
            try {
                if ($this->m_userrequest instanceof isys_module_request) {
                    if ($this->m_modules[$p_id]->make_object($this->m_userrequest) == null) {
                        return false;
                    }
                } else {
                    return false;
                }
            } catch (isys_exception_general $e) {
                if (intval($_GET[C__GET__MODULE_ID]) != defined_or_default('C__MODULE__MANAGER')) {
                    isys_application::instance()->container['notify']->error($e->getMessage());
                }
            }

            // Everything has been well done, return true.
            return true;
        }

        return false;
    }

    /**
     * Unregisters a module
     *
     * @param $p_id
     *
     * @return bool
     */
    public function unregister($p_id)
    {
        if (array_key_exists($p_id, $this->m_modules)) {
            $l_regobj = $this->m_modules[$p_id];

            unset($l_regobj);
            unset($this->m_modules[$p_id]);

            return true;
        }

        return false;
    }

    /**
     * Get all modules
     *
     * @return array|isys_module_register
     */
    public function modules()
    {
        return $this->m_modules;
    }

    /**
     * Get module by id
     *
     * @param $p_id
     *
     * @return mixed
     * @throws isys_exception_general
     */
    public function get_by_id($p_id)
    {
        if (!isset($this->m_modules[$p_id])) {
            $l_res = $this->get_modules($p_id);

            if ($l_res && $l_res->num_rows() > 0) {
                $l_row = $l_res->get_row(IDOIT_C__DAO_RESULT_TYPE_ARRAY);
                $l_mod_id = $l_row["id"];

                if ($l_row["{$this->m_datasource['table']}__status"] == C__RECORD_STATUS__NORMAL) {
                    if (!$this->register($l_mod_id, $l_row)) {
                        throw new isys_exception_general("Could not register module " . isys_application::instance()->container->get('language')
                                ->get($l_row["{$this->m_datasource['table']}__title"]) . ": " . var_export($l_row, true));
                    }
                } else {
                    throw new isys_exception_general('Module ' . isys_application::instance()->container->get('language')
                            ->get($l_row["{$this->m_datasource['table']}__title"]) . ' is deactivated.');
                }
            }
        }

        return @$this->m_modules[$p_id];
    }

    /**
     * @return  integer
     */
    public function count()
    {
        return count($this->m_modules);
    }

    /**
     * Module loader.
     *
     * @param   integer       $p_id
     * @param   isys_register $p_request
     *
     * @return  isys_module
     *
     * @throws  isys_exception_general
     * @throws  Exception|isys_exception_cmdb
     */
    public function load($p_id, $p_request = null)
    {
        if (!is_numeric($p_id)) {
            throw new isys_exception_general("Could not load module $p_id : Invalid arguments for module loader!");
        }

        /**
         * @var $l_modentry isys_module_register
         */
        $l_modentry = $this->get_by_id($p_id);

        if (!$l_modentry) {
            throw new isys_exception_general("Could not load module $p_id : Couldn't get module entry!");
        }

        $this->m_activemod = $p_id;
        if (!method_exists($l_modentry, 'get_object')) {
            return $this;
        }

        /**
         * @var $l_modobj isys_module
         */
        $l_modobj = $l_modentry->get_object();

        if (!is_object($l_modobj)) {
            throw new isys_exception_general("Could not load module " . $l_modentry->get_identifier() . ": Module does not exist!");
        }

        // Check wheather module is licenced as a trial version or not.
        if ((is_a($l_modobj, 'idoit\AddOn\LicensableInterface') && !$l_modobj::isLicensed()) || !$l_modobj->is_licenced()) {
            if ($this->is_trial($l_modentry->get_data('isys_module__identifier'))) {
                $l_modobj->start_trial($l_modentry, self::$m_trials[$l_modentry->get_data('isys_module__identifier')]);
            }
        }

        // Emitting module load event.
        isys_component_signalcollection::get_instance()
            ->emit("mod.manager.onBeforeLoad", $l_modobj);

        $l_modobj->start($p_request);

        // Emitting module loaded event.
        isys_component_signalcollection::get_instance()
            ->emit("mod.manager.onAfterLoad", $l_modobj);

        return $l_modobj;
    }

    /**
     * Return active module ID.
     *
     * @return  integer
     */
    public function get_active_module()
    {
        if (is_numeric($this->m_activemod)) {
            return $this->m_activemod;
        }

        return null;
    }

    /**
     * Retrieves the singleton instance
     *
     * @return  isys_module_manager
     */
    public static function instance()
    {
        return isys_application::instance()->container->moduleManager;
    }

    /**
     * Retrives module sorting.
     *
     * @return array|bool
     */
    public function get_module_sorting()
    {
        if (is_object($this->database)) {
            $l_dao = isys_component_dao::instance($this->database);
            $l_sort_array = [];

            $l_sql = 'SELECT * FROM ' . $this->m_datasource['table'] . '_sorting
                WHERE TRUE ORDER BY ' . $this->m_datasource['table'] . '_sorting__sort ASC;';
            $l_res = $l_dao->retrieve($l_sql);

            while ($l_row = $l_res->get_row()) {
                $l_sort_array[$l_row['isys_module_sorting__title']] = $l_row['isys_module_sorting__sort'];
            }

            return $l_sort_array;
        }

        return false;
    }

    /**
     * Method for retrieving all PHP / apache package dependencies.
     *
     * @param   string $for "php" or "apache".
     *
     * @return  array
     * @throws  \idoit\Exception\JsonException
     * @throws  isys_exception_filesystem
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function getPackageDependencies($for)
    {
        global $g_dirs;

        $packageFiles = glob($g_dirs['class'] . '/modules/*/package.json');
        $dependencies = [];

        if (is_array($packageFiles) && count($packageFiles)) {
            foreach ($packageFiles as $packageFile) {
                if (file_exists($packageFile)) {
                    $jsonContent = isys_format_json::decode(file_get_contents($packageFile));

                    if (isset($jsonContent['dependencies'][$for]) && is_array($jsonContent['dependencies'][$for])) {
                        foreach ($jsonContent['dependencies'][$for] as $dependency) {
                            $moduleName = $this->language->get($jsonContent['name']);

                            if (strpos($moduleName, 'LC_') === 0) {
                                $moduleName = $jsonContent['title'];
                            }

                            $dependencies[$dependency][] = $moduleName;
                        }
                    }
                }
            }
        }

        return $dependencies;
    }

    /**
     * Function which gets active or inactive modules.
     *
     * @param   boolean  $getActive
     *
     * @return  array
     * @throws  isys_exception_database
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_active_inactive_modules($getActive = true)
    {
        $return = [];
        $sql = 'SELECT isys_module__id 
            FROM isys_module 
            WHERE isys_module__status ' . ($getActive ? '= ' : '!= ') . $this->m_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        $result = $this->m_dao->retrieve($sql);

        while ($row = $result->get_row()) {
            $return[] = $row['isys_module__id'];
        }

        return $return;
    }

    /**
     * Get auth class of module by module id.
     *
     * @param int|string $p_module_id
     *
     * @return isys_auth|bool
     */
    public function get_module_auth($p_module_id)
    {
        if (!is_numeric($p_module_id)) {
            if (defined($p_module_id)) {
                $p_module_id = constant($p_module_id);
            } else {
                throw new isys_exception_general('Unable to handle given $p_module_id');
            }
        }

        if (!isset($this->m_module_auth[$p_module_id])) {
            // Retrieve module information: Only active modules
            $l_module_res = $this->get_modules($p_module_id, null, true);

            if ($l_module_res->count()) {
                $l_module_data = $l_module_res->get_row();

                if (is_a($l_module_data['isys_module__class'], AuthableInterface::class, true)) {
                    // Check for Authable.
                    $this->m_module_auth[$p_module_id] = call_user_func([$l_module_data['isys_module__class'], 'getAuth']);
                } elseif (is_a($l_module_data['isys_module__class'], isys_module_authable::class, true)) {
                    // Check for isys_module_authable.
                    $this->m_module_auth[$p_module_id] = call_user_func([$l_module_data['isys_module__class'], 'get_auth']);
                }
            }
        }

        if (isset($this->m_module_auth[$p_module_id])) {
            return $this->m_module_auth[$p_module_id];
        }

        // fallback
        return isys_module_system::getAuth();
    }

    /**
     * @return array
     * @throws isys_exception_database
     */
    private function get_installed_modules()
    {
        if ($this->database) {
            if (!count($this->m_installed)) {
                $l_dao = isys_component_dao::instance($this->database);
                $l_sql = 'SELECT ' . $this->m_datasource['table'] . '__id AS id , ' . $this->m_datasource['table'] . '__status as status, ' . $this->m_datasource['table'] .
                    '__title as title, ' . $this->m_datasource['table'] . '__const as const, ' . $this->m_datasource['table'] . '__class as class, ' .
                    $this->m_datasource['table'] . '__icon as icon, ' . $this->m_datasource['table'] . '__identifier as identifier FROM ' . $this->m_datasource['table'];
                $l_modules = $l_dao->retrieve($l_sql . ';');

                while ($l_row = $l_modules->get_row()) {
                    $this->m_installed[$l_row['identifier']] = [
                        'id'     => $l_row['id'],
                        'active' => $l_row['status'] == C__RECORD_STATUS__NORMAL
                    ];

                    $this->m_modules_cache[$l_row['id']] = $l_row;
                }
            }
        }

        return $this->m_installed;
    }

    /**
     * Constructor.
     */
    public function __construct($database)
    {
        parent::__construct();

        $this->database = $database;

        /**
         * Create dao instance if database component is present
         *
         * Because container is responsible for build the module manager
         * service it could happen that it is not initialized at
         * building process
         */
        if (is_object($database)) {
            $this->m_dao = isys_component_dao::instance($this->database);
        }

        $this->m_modules = [];
        $this->m_datasource = ["table" => "isys_module"];
    }

    /**
     * @param       array $package
     *
     * @deprecated  Do not use this method for installing a add-on!
     * @return      bool|int
     * @throws      isys_exception_dao
     * @throws      isys_exception_database
     * @throws      isys_exception_general
     */
    public function install(array $package)
    {
        return $this->installAddOn($package);
    }

    /**
     * @param       string $identifier
     * @param       array  $mandatorDatabases
     * @param       array  &$errorMessages
     *
     * @deprecated  Do not use this method for uninstalling a add-on!
     * @return      boolean
     * @throws      Exception
     */
    public function uninstall($identifier, array $mandatorDatabases, array &$errorMessages)
    {
        return $this->uninstallAddOn($identifier, $mandatorDatabases, $errorMessages);
    }

    /**
     * @param       string $identifier
     *
     * @deprecated  Do not use this method for activating a add-on!
     * @return      bool|void
     * @throws      isys_exception_dao
     */
    public function activate($identifier)
    {
        return $this->activateAddOn($identifier);
    }

    /**
     * @param       string $identifier
     *
     * @deprecated  Do not use this method for deactivating a add-on!
     * @return      boolean
     * @throws      isys_exception_dao
     */
    public function deactivate($identifier)
    {
        return $this->deactivateAddOn($identifier);
    }

    /**
     * @throws Exception
     */
    private function afterChange()
    {
        $systemDatabase = isys_application::instance()->container->get('database_system');
        $systemDao = new isys_component_dao($systemDatabase);

        try {
            $result = $systemDao->retrieve('SELECT * FROM isys_settings WHERE isys_settings__key = ' . $systemDao->convert_sql_text('cmdb.refresh-table-configurations'));
            // @see  ID-6382  Flag all tenants to refresh their table configurations.
            if (count($result)) {
                $systemDao->update('UPDATE isys_settings SET isys_settings__value = 1 WHERE isys_settings__key = ' .
                    $systemDao->convert_sql_text('cmdb.refresh-table-configurations'));
                $systemDao->apply_update();
            } else {
                $systemDao->update("INSERT INTO isys_settings SET isys_settings__key = 'cmdb.refresh-table-configurations', isys_settings__value = 1;");
                $systemDao->apply_update();
            }
        } catch (Exception $e) {
            throw new Exception('Could not set settings with key "cmdb.refresh-table-configurations"!');
        }

        isys_glob_delete_recursive(isys_glob_get_temp_dir(), $l_deleted, $l_undeleted);
        isys_component_signalcollection::get_instance()->emit('system.afterChange');
    }
}
