<?php

/**
 * i-doit - Updates
 *
 * @package    i-doit
 * @subpackage Update
 * @author     Dennis Stücken <dstuecken@i-doit.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 */
class isys_update_modules extends isys_update
{

    public function get_available_modules($p_path, $p_preselection = null)
    {
        global $g_comp_database;

        $l_modman = isys_module_manager::instance();

        $l_modules = [];
        $l_xml = new isys_update_xml();

        if (is_dir($p_path)) {
            $l_fh = opendir($p_path);

            while ($l_file = readdir($l_fh)) {

                if (strpos($l_file, ".") !== 0) {

                    if (is_dir($p_path . $l_file)) {

                        $l_moddata = $this->get_xml_data($p_path . $l_file . "/" . C__XML__SYSTEM);
                        $l_modules[] = $l_moddata;

                        $i = count($l_modules) - 1;
                        $l_modules[$i]["directory"] = $l_file;

                        if (is_array($p_preselection)) {
                            if (in_array($l_file, $p_preselection)) {
                                $l_modules[$i]["selected"] = true;
                            } else {
                                $l_modules[$i]["selected"] = false;
                            }
                        } else {
                            $l_modules[$i]["selected"] = false;
                        }

                        if (!is_null($g_comp_database) && isset($l_modules[$i]["const"]) && !empty($l_modules[$i]["const"])) {

                            $l_check = $l_modman->get_modules(null, $l_modules[$i]["const"]);
                            if (is_object($l_check)) {
                                $l_installed = ($l_check->num_rows() > 0);
                            }

                        } else {
                            $l_installed = true;
                        }

                        if (class_exists("isys_module_" . $l_modules[$i]["directory"]) && $l_installed) {
                            $l_modules[$i]["selected"] = true;
                            $l_modules[$i]["installed"] = true;
                        } else {
                            $l_modules[$i]["installed"] = false;
                        }

                    }
                }
            }

        }

        return $l_modules;
    }

    public function install($p_module_path, $p_active_databases, $p_tenant_databases, $p_system_database)
    {
        global $g_absdir, $g_comp_database_system, $g_upd_dir;

        $l_log = isys_update_log::get_instance();

        if (is_object($p_system_database)) {
            $g_comp_database_system = $p_system_database;
        }

        if (!is_dir($p_module_path)) {
            $l_log->add("- " . $p_module_path . " does not exist", C__ERROR, "bold");

            return -1;
        }

        $l_log = isys_update_log::get_instance();

        $l_id = $l_log->add("Module: <span class=\"grey\">" . str_replace($g_upd_dir . "/" . C__DIR__MODULES, "", $p_module_path) . "</span>", C__MESSAGE, "bold");

        if (is_object($p_system_database) || $p_system_database == $g_comp_database_system->get_db_name()) {

            /* Update system database */
            $l_id = $l_log->add("Updating system database " . $g_comp_database_system->get_db_name() . "..", C__MESSAGE, "bold indent");

            $l_db_update = $this->update_database($p_module_path . "/" . C__XML__SYSTEM, $g_comp_database_system);

            if ($l_db_update) {
                $l_log->result($l_id, C__DONE);
            } else {
                $l_log->result($l_id, C__ERR . "(" . $l_log->get_error_count() . ")", C__HIGH);

                /* Show error information on completion*/
                $_SESSION["error"] += $l_db_update;
            }
        } else {
            $l_id = $l_log->add(" - Skipped system database", C__MESSAGE, "bold indent", C__MEDIUM, C__OK);
        }

        /* Update tenant databases */
        if (is_array($p_active_databases) && count($p_active_databases) > 0) {
            foreach ($p_active_databases as $l_db_name) {
                $l_database = $p_tenant_databases[$l_db_name];
                if ($l_database instanceof isys_component_database) {
                    $l_id = $l_log->add("- " . $l_database->get_db_name(), C__MESSAGE, "bold indent");

                    if ($this->update_database($p_module_path . "/" . C__XML__DATA, $l_database)) {
                        $l_log->result($l_id, C__DONE);
                    } else {
                        $l_log->result($l_id, C__ERR . "(" . $l_log->get_error_count() . ")", C__HIGH);

                        /* Show error information on completion*/
                        $_SESSION["error"] += $l_db_update;
                    }
                } else {
                    $l_log->add(" - Database instance error.", C__ERROR);
                }
            }
        } else {

            $l_log->add(" - No database(s) selected. It is required to select the databases in order to install a module.", C__MESSAGE, "bold indent", C__HIGH, C__ERR);
        }

        /* File-Copy */
        $l_id = $l_log->add("Copying files to " . $g_absdir . "..", C__MESSAGE, "bold indent");

        $l_log->debug("Source-Directory: " . $p_module_path . "/" . C__DIR__FILES);
        $l_log->debug("--");

        $l_files = new isys_update_files($p_module_path . "/" . C__DIR__FILES);
        $l_errors = $l_files->copy();

        if ($l_errors > 0) {
            $l_log->result($l_id, C__ERR . "(" . $l_errors . ")", C__HIGH);

            /* Show error information on completion*/
            $_SESSION["error"] += $l_errors;

        } else {
            $l_log->result($l_id, C__DONE);
        }

        return true;
    }

}

?>