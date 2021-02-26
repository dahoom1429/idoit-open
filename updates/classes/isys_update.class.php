<?php

/**
 * i-doit - Updates
 *
 * @package    i-doit
 * @subpackage Update
 * @author     Dennis Stücken <dstuecken@i-doit.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_update
{
    /**
     * Mandant database component
     * @var isys_component_database
     */
    private $m_database;

    /**
     * History of all switched databases
     * @var isys_component_database[]
     */
    private $m_databases = [];

    /**
     * Array of update directories.
     * @var array
     */
    private $m_update_dirs = [];

    /**
     * Method for retrieving a modules (PHP) dependencies.
     *
     * @param   string $moduleIdentifier
     * @param   string $for
     *
     * @return  array
     * @throws  isys_exception_filesystem
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_module_dependencies($moduleIdentifier = null, $for = 'php')
    {
        global $g_dirs;

        if ($moduleIdentifier === null) {
            $packageFiles = glob($g_dirs['class'] . '/modules/*/package.json');
        } else {
            $packageFiles = [$g_dirs['class'] . '/modules/' . $moduleIdentifier . '/package.json'];

            if (!file_exists($packageFiles[0])) {
                throw new isys_exception_filesystem('No such file or directory!', 'The file ' . $packageFiles[0] . ' could not be found on your system!');
            }
        }

        if (!is_array($packageFiles) || !count($packageFiles)) {
            return [];
        }

        $dependencies = [];

        foreach ($packageFiles as $packageFile) {
            if (!file_exists($packageFile)) {
                continue;
            }

            try {
                $jsonContent = isys_format_json::decode(file_get_contents($packageFile));
            } catch (Exception $e) {
                continue;
            }

            // @see  ID-2162 We only use the "name" key, if the module is active - because if it's not, the language file will not be available and we'll see languace constants.
            $l_name_key = 'name';

            if (isset($jsonContent['identifier']) &&
                !empty($jsonContent['identifier']) &&
                $jsonContent['identifier'] !== 'pro' &&
                !isys_module_manager::instance()->is_active($jsonContent['identifier'])) {
                $l_name_key = 'identifier';
            }

            if (isset($jsonContent['dependencies'][$for]) && is_array($jsonContent['dependencies'][$for])) {
                foreach ($jsonContent['dependencies'][$for] as $dependency) {
                    $dependencies[$dependency][] = _L($jsonContent[$l_name_key]);
                }
            }
        }

        return $dependencies;
    }

    /**
     * Checks wheather an apache module is installed
     *
     * @param $p_module
     *
     * @return bool
     * @throws Exception
     */
    public static function is_webserver_module_installed($p_module)
    {
        if (method_exists('isys_core', 'is_webserver_module_installed')) {
            return isys_core::is_webserver_module_installed($p_module);
        }

        throw new Exception('Could not verify existence of Webserver Module "' . $p_module . '"');
    }

    /**
     * @param string $databaseName
     *
     * @return isys_component_database
     */
    public function get_database($databaseName)
    {
        return $this->m_databases[$databaseName];
    }

    /**
     * @param string $p_file
     *
     * @return mixed
     * @throws Exception
     */
    public function fetch_file($p_file)
    {
        if (function_exists("curl_init")) {
            $l_sess_curl = curl_init($p_file);
            /* --------------------------------------------------------------------- */
            if (isys_settings::get('proxy.active')) {
                curl_setopt($l_sess_curl, CURLOPT_PROXY, isys_settings::get('proxy.host') . ":" . isys_settings::get('proxy.port'));

                if (isys_settings::get('proxy.username')) {
                    curl_setopt($l_sess_curl, CURLOPT_PROXYUSERPWD, isys_settings::get('proxy.username') . ":" . isys_settings::get('proxy.password'));
                }
            }
            curl_setopt($l_sess_curl, CURLOPT_HEADER, false);
            curl_setopt($l_sess_curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($l_sess_curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($l_sess_curl, CURLOPT_SSL_VERIFYPEER, false);
            /* --------------------------------------------------------------------- */
            $l_responseTEXT = curl_exec($l_sess_curl);
            /* --------------------------------------------------------------------- */
            /* Error handling: */
            $l_error = curl_error($l_sess_curl);
            if (!empty($l_error)) {
                $l_proxy_config = '';

                if (isys_settings::get('proxy.active')) {
                    $l_proxy_config = str_replace('array', 'Proxy configuration: ', isys_settings::get('proxy.host') . ':' . isys_settings::get('proxy.port'));
                }

                $l_error_message = 'Error while connecting / cURL error: ' . curl_errno($l_sess_curl) . ' - ' . $l_error . PHP_EOL .
                    'Make sure this host is connected to the internet!' . PHP_EOL .
                    'Proxy settings can be configured in i-doit system settings' . PHP_EOL .
                    $l_proxy_config;

                throw new Exception($l_error_message);
            }

            /* --------------------------------------------------------------------- */

            return $l_responseTEXT;
        }

        throw new Exception('Error: PHP Curl module not installed/activated.');
    }

    /**
     * Gets the system information from the db
     *
     * @return array
     */
    public function get_isys_info()
    {
        global $g_comp_database, $g_comp_database_system;

        if (class_exists('isys_module_statistics') && is_object($g_comp_database) && method_exists('isys_module_statistics', 'get_statistics_dao')) {
            $l_stats = isys_module_statistics::get_statistics_dao($g_comp_database);

            return $l_stats->get_db_version();
        }

        $l_mret = $g_comp_database_system->query("SELECT * FROM isys_db_init;");

        $l_title = $l_revision = $l_version = '';

        while ($l_mrow = $g_comp_database_system->fetch_row_assoc($l_mret)) {
            if ($l_mrow['isys_db_init__key'] === 'version') {
                $l_version = $l_mrow['isys_db_init__value'];
            }

            if ($l_mrow['isys_db_init__key'] === 'revision') {
                $l_revision = (int)$l_mrow['isys_db_init__value'];
            }

            if ($l_mrow['isys_db_init__key'] === 'title') {
                $l_title = $l_mrow['isys_db_init__value'];
            }
        }

        return [
            'name'     => $l_title,
            'version'  => $l_version,
            'revision' => $l_revision,
            'type'     => 'System'
        ];
    }

    /**
     * Retrieves new i-doit versions of an xml file, which should be located at http://i-doit.org/updates.xml
     *
     * @param string $p_strxml
     *
     * @return array
     */
    public function get_new_versions($p_strxml)
    {
        $l_updates = [];

        if (strlen($p_strxml) > 0 && strpos($p_strxml, 'version') !== false && strpos($p_strxml, 'revision') !== false) {
            try {
                $l_xml_el = simplexml_load_string($p_strxml);

                if ($l_xml_el && $l_xml_el->updates->update) {
                    foreach ($l_xml_el->updates->update as $l_update) {
                        $l_updates[] = [
                            'title'       => (string)$l_update->title,
                            'version'     => (string)$l_update->version,
                            'revision'    => (int)$l_update->revision,
                            'release'     => (string)$l_update->release,
                            'filename'    => (string)$l_update->filename,
                            'requirement' => [
                                'version'  => (string)$l_update->requirement->version,
                                'revision' => (int)$l_update->requirement->revision
                            ]
                        ];
                    }
                }
            } catch (Exception $e) {
                return [];
            }
        }

        return $l_updates;
    }

    /**
     * Get Available Updates
     *
     * @author  Niclas Potthast <npotthast@i-doit.de>
     * @version Dennis Stücken <dstuecken@i-doit.de>
     * @return array
     */
    public function get_available_updates($p_path)
    {
        if (empty($this->m_update_dirs)) {
            $l_info = $this->get_isys_info();

            $l_arDirs = [];

            $l_resDir = opendir($p_path);
            while ($l_strDirValue = readdir($l_resDir)) {
                if (strpos($l_strDirValue, ".") !== 0 && $l_strDirValue != "images" && $l_strDirValue != "classes" && $l_strDirValue != "tpl") {
                    if (is_dir($p_path . $l_strDirValue)) {
                        $l_arDirs[] = $l_strDirValue;
                    }
                }
            }

            if (count($l_arDirs) > 0) {
                $l_dirs = [];

                //get info from update_sys.xml in every update directory
                foreach ($l_arDirs as $l_val) {
                    if (file_exists($p_path . $l_val . "/" . C__XML__SYSTEM)) {
                        $l_data = $this->get_xml_data($p_path . $l_val . "/" . C__XML__SYSTEM);

                        if (($l_data["revision"] >= $l_info["revision"] && $l_data["requirement"]["revision"] <= $l_info["revision"]) ||
                            (empty($l_data["revision"]) && empty($l_data["version"]))) {
                            $l_dirs[$l_val] = $l_data;
                        }
                    }
                }

                $this->m_update_dirs = $l_dirs;
                sort($this->m_update_dirs);
            }
        }

        return $this->m_update_dirs;
    }

    public function get_xml_data($p_file)
    {
        $l_data = [];

        if (file_exists($p_file)) {
            $l_objXML = simplexml_load_file($p_file);

            $l_strChangeLog = 'n/a';

            if (defined('C__CHANGELOG') && file_exists(dirname($p_file) . '/' . C__CHANGELOG)) {
                $l_strChangeLog = file_get_contents(dirname($p_file) . '/' . C__CHANGELOG);
            }

            if (isset($l_objXML->info)) {
                foreach ($l_objXML->info as $l_info) {
                    $l_data = [
                        'title'       => (string)$l_info->title,
                        'version'     => (string)$l_info->version,
                        'revision'    => (int)$l_info->revision,
                        'directory'   => (string)$l_info->directory,
                        'const'       => (string)$l_info->const,
                        'changelog'   => (string)$l_strChangeLog,
                        'requirement' => [
                            'revision' => (int)$l_info->requirement->revision,
                            'version'  => (string)$l_info->requirement->version
                        ]
                    ];
                }
            }
        }

        return $l_data;
    }

    /**
     * The real update procedure.
     *
     * @author Dennis Stücken <dstuecken@i-doit.de>
     */
    public function update($p_system_database, $p_databases)
    {
        global $g_comp_database_system, $g_upd_dir, $g_file_dir, $g_absdir, $g_temp_dir;

        $l_log = isys_update_log::get_instance();
        $l_db_update = [];
        $l_return = true;
        /**
         * @var $l_tenant_daos isys_component_dao[]
         */
        $l_tenant_daos = [];

        try {
            // Initialize dao
            $daoSystem = new isys_component_dao($g_comp_database_system);

            // System database.
            if ($p_system_database == $g_comp_database_system->get_db_name()) {
                $l_id = $l_log->add('Updating system database..', C__MESSAGE, 'bold');

                try {
                    $l_db_update[$l_id] = $this->update_database($g_upd_dir . DS . C__XML__SYSTEM, $g_comp_database_system, true, $daoSystem);

                    $l_log->result($l_id, C__DONE);
                } catch (Exception $e) {
                    $l_log->result($l_id, C__ERR . '(' . $l_log->get_error_count() . ')', C__HIGH);

                    // Show error and cancel transaction
                    throw $e;
                }
            } else {
                $l_log->add('Skipping system database', C__MESSAGE, 'bold', C__MEDIUM, C__OK);
            }

            // Tenant database(s).
            if (is_array($p_databases)) {
                // Load the databases.
                $this->get_databases();

                $l_log->add('Updating tenant database(s)..', C__MESSAGE, 'bold');

                foreach ($p_databases as $l_database) {
                    $l_log->reset_error_count();
                    $l_id = $l_log->add('- ' . $l_database, C__MESSAGE, 'bold');

                    try {
                        // Select the current database.
                        $l_db_instance = $this->m_databases[$l_database];

                        if ($l_db_instance instanceof isys_component_database) {
                            $l_tenant_daos[] = $l_tenant_dao = new isys_component_dao($l_db_instance);

                            $l_db_update[] = $this->update_database($g_upd_dir . DS . C__XML__DATA, $l_db_instance, true, $l_tenant_dao);

                            $l_log->result($l_id, C__DONE);
                        } else {
                            if (is_object($l_db_instance)) {
                                throw new isys_exception_database('The given database is no database, but an instance of ' . get_class($l_db_instance));
                            }

                            throw new isys_exception_database('The given database is no database, but data-type ' . gettype($l_db_instance));
                        }
                    } catch (isys_exception_database $e) {
                        $l_log->result($l_id, C__ERR . "(" . $l_log->get_error_count() . ")", C__HIGH);
                    }
                }
            } else {
                $l_log->add("Skipping tenant database(s)", C__MEDIUM, "bold", C__MEDIUM, C__OK);
            }

            // If it was all good: apply the update and commit transaction
            foreach ($l_db_update as $l_dbstat) {
                if (!$l_dbstat) {
                    // throw exception and cancel transaction
                    throw new Exception('Database error occured.');
                }
            }

            // Apply the update transaction on system database
            $daoSystem->apply_update();

            // Apply update on all tenants
            foreach ($l_tenant_daos as $l_tenant_dao) {
                $l_tenant_dao->apply_update();
            }

            // File update.
            if (strlen($g_upd_dir) > 0 && (!isset($_POST["no_file_update"]) || $_POST["no_file_update"] != "true")) {
                // ID-6346  Moved the "file copy" section somewhere else, will now be processed in an ajax request.

                // File delete.
                (new isys_update_files($g_file_dir))->delete();
            } else {
                $l_log->add("Skipped copying files..", C__MESSAGE, "bold", C__MEDIUM, C__OK);
            }

            /*
             * -----------------------------------------------------
             * Config update:
             * -----------------------------------------------------
             */
            if (strlen($g_upd_dir) > 0 && (!isset($_POST["no_config"]) || $_POST["no_config"] != "true")) {

                /* Get config handler */
                $l_config = new isys_update_config();

                /* Set the i-doit source directory */
                $l_source_directory = $g_absdir . DIRECTORY_SEPARATOR . "src";

                /* Logging.. */
                $l_log->add("Applying config file", C__MESSAGE, "bold");

                /* Create a backup of the existing config file */
                $l_backup_file = $l_config->backup($l_source_directory);
                if ($l_backup_file) {
                    /* Parse config file and return the file as string */
                    $l_new_config = $l_config->parse($g_upd_dir);
                    if ($l_new_config) {
                        /* Show backup message */
                        $l_log->add("Backing up old config", C__MESSAGE, "indent", C__LOW, C__DONE);

                        /* Start parsing and writing the new file */
                        $l_id = $l_log->add("Creating new one", C__MESSAGE, "indent");

                        /* Write the returned string to i-doit/src/config.inc.php */
                        $l_config->write($l_new_config, $l_source_directory);
                        $l_log->result($l_id, C__DONE);
                    } else {
                        $l_log->add("No config update needed this time", C__MESSAGE, "indent", C__LOW, C__DONE);
                        if (file_exists($l_backup_file)) {
                            unlink($l_backup_file);
                        }
                    }

                    /* Assign location of config backup */
                    isys_application::instance()->template->assign("config_backup", $l_backup_file);
                } else {
                    /* Backup failed*/
                    $l_log->add("Could not create config backup. Check rights.", C__MESSAGE, "indent", C__HIGH, C__ERR);
                }
            } else {
                $l_log->add("Skipped applying config file..", C__MESSAGE, "bold", C__MEDIUM, C__OK);
            }

            $l_log->debug("--");

            // Clear temp directories.
            $l_deleted = 0;
            $l_undeleted = 0;
            isys_glob_delete_recursive($g_temp_dir, $l_deleted, $l_undeleted, true);

            $l_id = $l_log->add("Deleting i-doit temp directories..", C__MESSAGE, "bold");
            $l_log->debug("Used-Temp directory: " . $g_temp_dir);

            if ($l_undeleted <= 0) {
                $l_log->result($l_id, C__DONE);
                $l_log->debug("Done. Deleted {$l_deleted} temp files.");
            } else {
                $l_log->result($l_id, $l_undeleted . "errors", C__HIGH);
                $l_log->debug("failed - could not delete {$l_undeleted} files.");
            }

            if (is_dir($g_absdir . "/src/themes/default/smarty/cache")) {
                $l_log->add("Deleting smarty cache directory", C__MESSAGE, "bold", C__MEDIUM, C__DONE);
                isys_glob_delete_recursive($g_absdir . "/src/themes/default/smarty/cache", $l_deleted, $l_undeleted);
            }

            if (is_dir($g_absdir . "/src/themes/default/smarty/templates_c")) {
                $l_log->add("Deleting smarty compile directory", C__MESSAGE, "bold", C__MEDIUM, C__DONE);
                isys_glob_delete_recursive($g_absdir . "/src/themes/default/smarty/templates_c", $l_deleted, $l_undeleted);
            }

            $l_log->debug("--");
        } catch (Exception $e) {
            // Show error information on completion.
            $_SESSION["error"] += 1;

            $l_return = false;
        }

        /**
         * @todo Remove this for the new updater
         */
        if (is_object(isys_application::instance()->template)) {
            isys_application::instance()->template->assign('g_log', $l_log->get());
        }

        return $l_return;
    }

    /**
     * Update system or mandant databases
     *
     * @param string                  $p_file
     * @param isys_component_database $p_database
     *
     * @return bool
     */
    public function update_database($p_file, isys_component_database &$p_database, $p_do_version_change = false, $p_dao = null)
    {
        global $g_comp_database;

        $g_comp_database = $p_database;

        $l_return = true;

        $l_log = isys_update_log::get_instance();

        $l_xml = new isys_update_xml();

        $l_statements = $l_xml->load_xml($p_file, $p_do_version_change);

        if (!$l_statements) {
            $l_log->add("No statements found in {$p_file}.");

            return true;
        }

        /**
         * Get and start transaction management
         */
        if (!$p_dao) {
            $p_dao = new isys_component_dao($p_database);
        }

        isys_component_signalcollection::get_instance()
            ->emit('system.onBeforeUpdateDatabase', $p_dao->get_database_component(), $l_statements, $p_file, $p_do_version_change, $_POST);

        /**
         * Turning safe mode off:
         */
        $p_database->query('SET SQL_SAFE_UPDATES = 0;');

        foreach ($l_statements as $l_statement) {
            $l_check_ident = null;
            $l_exec_ident = null;
            $l_check = null;
            $l_sql = null;
            $l_execs = null;
            $l_query = true;
            $l_return = true;

            // Get statement variables
            $l_id = $l_statement['id'];
            $l_check = $l_statement['check'];
            $l_sql = $l_statement['sql'];
            $l_title = $l_statement['title'] ?: '';
            $l_catg = (array)$l_statement['catg'];
            $l_cats = (array)$l_statement['cats'];

            // Get Execs
            if (is_object($l_sql->exec)) {
                foreach ($l_sql->exec as $l_ekey => $l_evalue) {
                    $l_attribs = $l_evalue->attributes();
                    $l_aident = $l_attribs['ident'];

                    /* Trim whitespaces right*/
                    $l_evalue = rtrim($l_evalue, " ");
                    $l_evalue = rtrim($l_evalue, "\t");

                    /* Trim whitespaces left*/
                    $l_evalue = ltrim($l_evalue, " ");
                    $l_evalue = ltrim($l_evalue, "\t");

                    // Do not use === otherwise the check fails
                    if ($l_aident == 'false') {
                        $l_execs['false'] = $l_evalue;
                    } else {
                        $l_execs['true'] = $l_evalue;
                    }
                }
                $l_execs['false'] = (isset($l_execs['false'])) ? $l_execs['false'] : false;
                $l_execs['true'] = (isset($l_execs['true'])) ? $l_execs['true'] : false;
            } else {
                $l_execs = [
                    'true'  => false,
                    'false' => false
                ];
            }

            /**
             * Get check ident
             */
            if (is_object($l_check)) {
                $l_attrib = $l_check->attributes();
                $l_check_ident = (string)$l_attrib['ident'];
            } else {
                $l_check = false;
            }

            /**
             * Do a check
             */
            if ($l_check) {
                $l_check_result = $this->check($l_check_ident, $p_database, $l_check);

                if (strlen($l_title) > 0) {
                    $l_check_title = $l_title;
                } else {
                    $l_check_title = $l_check_result['title'];
                }

                /**
                 * Log current process
                 */
                $l_id = $l_log->add(isys_glob_str_stop($l_check_title, 85, "..") . " <strong class=\"grey\">" . $l_check_result["error"] . "</strong>", C__MESSAGE, "indent");

                try {
                    switch ($l_check_ident) {
                        case "C_ADD_OBJECT_TYPE":
                            if ($l_check_result["check"] === true) {
                                $l_check = (array)$l_check;
                                $l_exp = explode(",", $l_check[0]);

                                $l_obj_type = trim($l_exp[0]);
                                $l_obj_type_title = trim($l_exp[2]);
                                $l_container = trim($l_exp[3]);

                                $l_img_name = trim($l_exp[4]);
                                $l_icon = trim($l_exp[5]);

                                /* Get tree group */
                                $l_tree_group = trim($l_exp[1]);
                                $l_data = $p_database->query("SELECT isys_obj_type_group__id FROM isys_obj_type_group WHERE (isys_obj_type_group__const = '{$l_tree_group}')");
                                $l_row = $p_database->fetch_row_assoc($l_data);
                                $l_tree_group = $l_row["isys_obj_type_group__id"];

                                if ($l_tree_group > 0) {
                                    $l_cmdb_dao = new isys_cmdb_dao($p_database);

                                    $l_obj_type__id = $l_cmdb_dao->insert_new_objtype(
                                        $l_tree_group,
                                        $l_obj_type_title,
                                        $l_obj_type,
                                        false,
                                        $l_container,
                                        $l_img_name,
                                        $l_icon
                                    );

                                    $l_exp = explode(",", $l_catg);
                                    foreach ($l_exp as $l_category) {
                                        $l_cmdb_dao->assign_catg($l_obj_type__id, null, $l_category);
                                    }

                                    if ($l_obj_type__id > 0) {
                                        $l_query = true;
                                    }
                                }
                            } else {
                                $l_query = true;
                            }
                            break;
                        case "C_DROP_FOREIGN_KEY":
                            if (isset($l_execs["true"])) {
                                $l_true_exec = trim($l_execs["true"]);
                                if (!$l_true_exec) {
                                    $l_tables = $this->explode_tables($l_check);
                                    $l_execs["true"] = "ALTER TABLE `" . $l_tables[0] . "` DROP FOREIGN KEY `%KEY_NAME%`;";
                                }
                            }

                            if ($l_check_result["check"]) {
                                $l_execs["true"] = str_replace("%KEY_NAME%", $l_check_result["key"], $l_execs["true"]);
                            } else {
                                $l_execs["true"] = "";
                            }

                            break;
                        case "C_DROP_TABLE_FOREIGN_KEYS":
                            if (isset($l_execs["true"])) {
                                $l_true_exec = trim($l_execs["true"]);

                                if (!$l_true_exec) {
                                    $l_table = trim($l_check);
                                    $l_execs["true"] = "ALTER TABLE `" . $l_table . "` DROP FOREIGN KEY `%KEY_NAME%`;\n";
                                }
                            }

                            if ($l_check_result["check"]) {
                                $l_alter_query_all = '';

                                foreach ($l_check_result["keys"] as $l_foreign_key) {
                                    $l_alter_query = $l_execs["true"];
                                    $l_alter_query_all .= str_replace("%KEY_NAME%", $l_foreign_key, $l_alter_query);
                                }

                                $l_execs["true"] = $l_alter_query_all;
                            } else {
                                $l_execs["true"] = "";
                            }

                            break;
                    }

                    if ($l_check_result["check"] === true) {

                        /**
                         * Explode queries, if there are more than one
                         */
                        $l_execs["true"] = str_replace("\r", "", $l_execs["true"]);
                        $l_tmp = explode(";\n", $l_execs["true"]);

                        /**
                         * Send query / queries
                         */
                        foreach ($l_tmp as $l_q) {
                            $l_q = trim($l_q);
                            /* Check if a query is inside */
                            if (!empty($l_q)) {
                                /**
                                 * Add the update to transaction
                                 */
                                $l_log->debug("QUERY: " . preg_replace("/\s+/", ' ', $l_q));
                                $l_query = $p_dao->get_database_component()
                                    ->query($l_q);
                                $l_log->debug("Affected rows: " . $p_dao->get_database_component()
                                        ->affected_rows($l_query));
                            }
                        }
                    } else {
                        if ($l_check_result["check"] === false) {
                            if (strlen($l_execs["false"]) > 0) {
                                $l_execs["false"] = str_replace("\r", "", $l_execs["false"]);
                                $l_tmp = explode(";\n", $l_execs["false"]);

                                foreach ($l_tmp as $l_q) {
                                    $l_q = trim($l_q);

                                    if (!empty($l_q)) {
                                        // Add the update to transaction
                                        $l_log->debug("QUERY: " . preg_replace("/\s+/", ' ', $l_q));
                                        $l_query = $p_dao->get_database_component()
                                            ->query($l_q);

                                        $l_log->debug("Affected rows: " . $p_dao->get_database_component()
                                                ->affected_rows($l_query));
                                    }
                                }

                                // Commit UPDATE and INSERT statements
                                $p_dao->apply_update();
                            } // if exec != false
                        }
                    } // if check == false

                    if ($l_query) {
                        $l_log->result($l_id, C__DONE);
                        $l_return = true;
                    } else {
                        throw new Exception('Update failure in statement: ' . ((isset($l_tmp) && is_array($l_tmp)) ? implode(', ', $l_tmp) : $l_statement["id"]));
                    }
                } catch (Exception $e) {
                    $l_log->result($l_id, C__ERR, C__HIGH);
                    $l_log->add($e->getMessage(), C__MESSAGE, "bold red indent");
                }
            }

            unset($l_check_result);
        }

        return $l_return;
    }

    /**
     * @return array
     */
    public function get_databases()
    {
        global $g_comp_database_system;

        $l_databases = [];

        $l_sql = 'SELECT *
			FROM isys_mandator
			WHERE isys_mandator__active = 1
			GROUP BY isys_mandator__db_name;';

        $l_ret = $g_comp_database_system->query($l_sql);

        if ($g_comp_database_system->num_rows($l_ret) > 0) {
            $mandatorComponentDao = isys_component_dao_mandator::instance($g_comp_database_system);

            while ($l_row = $g_comp_database_system->fetch_row_assoc($l_ret)) {
                $tenantPass = (method_exists($mandatorComponentDao, 'getPassword')?
                    isys_component_dao_mandator::getPassword($l_row): $l_row["isys_mandator__db_pass"]);

                $this->change_database(
                    $l_row['isys_mandator__db_host'],
                    $l_row['isys_mandator__db_port'],
                    $l_row['isys_mandator__db_user'],
                    $tenantPass,
                    $l_row['isys_mandator__db_name']
                );

                // Get database information (version, revision).
                if ($this->m_database->is_connected()) {
                    $l_mret = $this->m_database->query("SELECT * FROM isys_db_init;");

                    while ($l_mrow = $this->m_database->fetch_row_assoc($l_mret)) {
                        if ($l_mrow['isys_db_init__key'] === 'version') {
                            $l_version = $l_mrow['isys_db_init__value'];
                        }

                        if ($l_mrow['isys_db_init__key'] === 'revision') {
                            $l_revision = $l_mrow['isys_db_init__value'];
                        }
                    }

                    $l_databases[$l_row['isys_mandator__id']] = [
                        'name'     => $l_row['isys_mandator__db_name'],
                        'version'  => $l_version,
                        'revision' => $l_revision,
                        'type'     => 'Mandant'
                    ];

                    unset($l_version, $l_revision);
                }
            }
        }

        return $l_databases;
    }

    /**
     * Change current mandant database
     *
     * @param string $p_host
     * @param int    $p_port
     * @param string $p_user
     * @param string $p_pass
     * @param string $p_name
     */
    public function change_database($p_host, $p_port, $p_user, $p_pass, $p_name)
    {
        global $g_db_system;

        $this->m_databases[$p_name] = $this->m_database = isys_component_database::get_database($g_db_system['type'] ?: 'mysqli', $p_host, $p_port, $p_user, $p_pass, $p_name);
    }

    /**
     * Get a check conform return array (p_check:false = execute query, p_check:true = dont execute)
     *
     * @param bool|string $p_check
     * @param null        $p_title
     * @param null        $p_message
     * @param int         $p_priority
     * @param null        $p_id
     *
     * @return array
     */
    private function get_return($p_check = true, $p_title = null, $p_message = null, $p_priority = C__MEDIUM, $p_id = null)
    {
        return [
            'check'    => $p_check,
            'error'    => $p_message,
            'title'    => $p_title,
            'priority' => $p_priority,
            'id'       => $p_id
        ];
    }

    /**
     * Trims whitespaces from an array with 2 values
     *
     * @param string $p_strTable
     *
     * @return array
     * @author NP 2007-11-05
     */
    private function explode_tables($p_strTable)
    {
        $l_arTable = [];
        $l_strTable = '';
        $l_strField = '';

        $l_arTable = explode(',', $p_strTable);

        $l_arTable[0] = trim($l_arTable[0]);
        $l_arTable[1] = trim($l_arTable[1]);

        return $l_arTable;
    }

    /**
     * Checks if a query will be committed
     *
     * @param string                  $p_ident
     * @param isys_component_database $p_database
     * @param string                  $p_table
     *
     * @return array|bool
     */
    private function check($p_ident, &$p_database, $p_table = null)
    {
        if (!is_object($p_database)) {
            return false;
        }

        $l_log = isys_update_log::get_instance();
        $l_query = null;
        $l_sql = '';
        $l_return = $this->get_return(true);
        try {
            /**
             * Switch possible check methods
             */
            switch ($p_ident) {
                case 'C_ADD_FOREIGN_KEY':
                    $l_table = $this->explode_tables($p_table);

                    $l_title = "Adding FK {$l_table[1]} ..";
                    $l_migration = new isys_update_migration();

                    $l_foreign_key = $l_migration->get_foreign_key($l_table[0], $l_table[1]);

                    if ($l_foreign_key) {
                        $l_return = $this->get_return(true, $l_title, "FK already existing.");

                        $l_return["key"] = $l_foreign_key;
                    } else {
                        $l_return = $this->get_return(false, $l_title, "FK added.");
                    }

                    break;

                case 'C_DROP_FOREIGN_KEY':
                    $l_table = $this->explode_tables($p_table);

                    $l_title = "Dropping FK {$l_table[1]} ..";
                    $l_migration = new isys_update_migration();

                    $l_foreign_key = $l_migration->get_foreign_key($l_table[0], $l_table[1]);

                    if ($l_foreign_key) {
                        $l_return = $this->get_return(true, $l_title, "Dropped.");

                        $l_return["key"] = $l_foreign_key;
                    } else {
                        $l_return = $this->get_return(false, $l_title, "Table or FK does not exist.");
                    }

                    break;

                case 'C_TABLE_EXISTS':
                case 'C_DROP_TABLE':
                    $l_sql = "SHOW TABLES LIKE '" . $p_table . "';";
                    $l_query = $p_database->query($l_sql);

                    $l_title = "Dropping {$p_table} ..";

                    if ($p_database->num_rows($l_query) <= 0) {
                        $l_return = $this->get_return(false, $l_title, "Table doesn't exist.");
                    } else {
                        $l_return = $this->get_return(true, $l_title, "Dropped.");
                    }

                    break;

                case 'C_CREATE_TABLE':
                    $l_sql = "SHOW TABLES LIKE '" . $p_table . "';";
                    $l_query = $p_database->query($l_sql);

                    $l_title = "Creating table {$p_table}..";

                    if ($p_database->num_rows($l_query) > 0) {
                        $l_return = $this->get_return(true, $l_title, "Table already existing.");
                    } else {
                        $l_return = $this->get_return(false, $l_title, "Created.");
                    }

                    break;

                case 'C_ALTER_TABLE':
                    $l_sql = "SHOW TABLES LIKE '" . $p_table . "';";
                    $l_query = $p_database->query($l_sql);

                    $l_title = "Modifying table {$p_table}.. ";

                    if ($p_database->num_rows($l_query) <= 0) {
                        $l_return = $this->get_return(false, $l_title, "Table was not found.");
                    } else {
                        $l_return = $this->get_return(true, $l_title, "Modified.");
                    }

                    break;

                case 'C_INSERT_INTO':
                    $l_sql = "SHOW TABLES LIKE '" . $p_table . "';";
                    $l_query = $p_database->query($l_sql);

                    $l_title = "Inserting into table {$p_table}.. ";

                    if ($p_database->num_rows($l_query) <= 0) {
                        $l_return = $this->get_return(false, $l_title, "Table was not found.");
                    } else {
                        $l_return = $this->get_return(true, $l_title, "Inserted");
                    }

                    break;

                case 'C_UPDATE':
                    $l_sql = "SHOW TABLES LIKE '" . $p_table . "';";
                    $l_query = $p_database->query($l_sql);

                    $l_title = "Updating value in table {$p_table}.. ";

                    if ($p_database->num_rows($l_query) <= 0) {
                        $l_return = $this->get_return(false, $l_title, "Table was not found.");
                    } else {
                        $l_return = $this->get_return(true, $l_title, "Updated.");
                    }

                    break;

                case 'C_VALUE_EXISTS':
                    $l_sql = $p_table;
                    $l_query = $p_database->query($l_sql);

                    $l_title = "Updating value.. ";

                    if ($p_database->num_rows($l_query) <= 0) {
                        $l_return = $this->get_return(false, $l_title, "Value(s) inserted/updated.");
                    } else {
                        $l_return = $this->get_return(true, $l_title, "Value already existing.");
                    }
                    break;

                case 'C_ADD_INDEX':
                    $l_table = $this->explode_tables($p_table);

                    $l_sql = "SHOW INDEX FROM `" . $l_table[0] . "` " . "WHERE (`Key_name` = '" . $l_table[1] . "')";
                    $l_query = $p_database->query($l_sql);

                    $l_title = "Adding index " . $l_table[1] . " to " . $l_table[0] . ".. ";

                    if ($p_database->num_rows($l_query) > 0) {
                        $l_return = $this->get_return(true, $l_title, "Index exists.");
                    } else {
                        $l_return = $this->get_return(false, $l_title, "Added.");
                    }

                    break;

                case 'C_DROP_INDEX':
                    $l_table = $this->explode_tables($p_table);

                    $l_sql = "SHOW INDEX FROM `" . $l_table[0] . "` " . "WHERE (`Key_name` = '" . $l_table[1] . "')";
                    $l_query = $p_database->query($l_sql);

                    $l_title = "Deleting index " . $l_table[1] . " from " . $l_table[0] . ".. ";

                    if ($p_database->num_rows($l_query) <= 0) {
                        $l_return = $this->get_return(false, $l_title, "Index not found");
                    } else {
                        $l_return = $this->get_return(true, $l_title, "Deleted.");
                    }

                    break;

                case 'C_ADD_FIELD':
                    $l_table = $this->explode_tables($p_table);

                    if (strlen($l_table[1]) > 64) {
                        throw new Exception('Attention: Column limit of 64 characters reached for ' . $l_table[1]);
                    }

                    $l_sql = "SHOW COLUMNS FROM `" . $l_table[0] . "` " . "WHERE (`Field` = '" . $l_table[1] . "')";
                    $l_query = $p_database->query($l_sql);

                    $l_title = "Adding field " . $l_table[1] . " to table " . $l_table[0] . ".. ";

                    if ($p_database->num_rows($l_query) <= 0) {
                        $l_return = $this->get_return(false, $l_title, "Added.");
                    } else {
                        $l_return = $this->get_return(true, $l_title, "Already exists.");
                    }

                    break;

                case 'C_CHANGE_FIELD':
                    $l_table = $this->explode_tables($p_table);

                    $l_sql = "SHOW COLUMNS FROM `" . $l_table[0] . "` " . "WHERE (`Field` = '" . $l_table[1] . "')";
                    $l_query = $p_database->query($l_sql);

                    $l_title = "Changing field " . $l_table[1] . " in table " . $l_table[0] . ".. ";

                    if ($p_database->num_rows($l_query) <= 0) {
                        $l_return = $this->get_return("break", $l_title, "Field does not exist!", C__ERROR);
                    } else {
                        $l_return = $this->get_return(true, $l_title, "Changed.");
                    }

                    break;

                case 'C_DROP_FIELD':
                    $l_table = $this->explode_tables($p_table);

                    $l_sql = "SHOW COLUMNS FROM `" . $l_table[0] . "` " . "WHERE (`Field` = '" . $l_table[1] . "')";
                    $l_query = $p_database->query($l_sql);

                    $l_title = "Deleting field " . $l_table[1] . " from " . $l_table[0] . ".. ";

                    if ($p_database->num_rows($l_query) <= 0) {
                        $l_return = $this->get_return(false, $l_title, "Field does not exist.");
                    } else {
                        $l_return = $this->get_return(true, $l_title, "Deleted.");
                    }

                    break;

                case 'C_DELETE_VALUE':
                    $l_return = $this->get_return(true, "Deleting value from {$p_table}..", 'OK');

                    break;

                case 'C_EXECUTE':
                    $l_return = $this->get_return(true, 'Executing query..', 'OK');

                    break;

                case 'C_ADD_OBJECT_TYPE':
                    $l_exp = $this->explode_tables($p_table);

                    $l_sql = "SELECT * FROM isys_obj_type WHERE " . "(isys_obj_type__const = '" . $l_exp[0] . "');";

                    $l_query = $p_database->query($l_sql);

                    if ($p_database->num_rows($l_query) <= 0) {
                        $l_return = $this->get_return(true, 'Adding object type', 'Added.');
                    } else {
                        $l_return = $this->get_return(false, 'Adding object type', 'Already existing.');
                    }

                    break;
            }
        } catch (Exception $e) {
            $l_log->add($e->getMessage(), C__MESSAGE, "bold red indent", C__HIGH);
        }

        // Debug messages.
        $l_log->debug('--');

        if (strlen($l_sql) > 0) {
            $l_log->debug('Checking :: ' . preg_replace("/\s+/", ' ', $l_sql));
        } else {
            $l_log->debug('Checking :: No Query found.');
        }

        if ($l_return["check"]) {
            $l_log->debug('Result   :: True');
        } else {
            $l_log->debug('Result   :: False');
        }

        return $l_return;
    }

    /**
     * isys_update constructor.
     */
    public function __construct()
    {
        $this->get_isys_info();
    }
}
