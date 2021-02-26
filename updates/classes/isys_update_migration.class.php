<?php
/**
 * i-doit - Updates.
 *
 * @package     i-doit
 * @subpackage  Update
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

define("C__SQL__FIELD", 1);
define("C__SQL__FOREIGN_KEY", 2);
define("C__SQL__REFERENCED_TABLE", 3);
define("C__SQL__REFERENCED_FIELD", 4);
define("C__SQL__CONSTRAINT", 5);

class isys_update_migration extends isys_update
{
    /**
     * @var  null
     */
    protected $m_referential_constraints = null;

    /**
     * Checks if a migration identified by $p_migration_identifier is already done.
     *
     * @param   string  $p_migration_identifier
     * @param   boolean $checkVersion
     *
     * @return  boolean
     */
    public function is_migration_done($p_migration_identifier, $checkVersion = false)
    {
        global $g_comp_database;

        $l_sql = "SELECT isys_migration__done
			FROM isys_migration
			WHERE isys_migration__title = '" . $p_migration_identifier . "'
			AND isys_migration__done = 1";

        // This is if the migration has to be called in every version or only one time
        if ($checkVersion) {
            $l_sql .= " AND isys_migration__version = '" . substr($_SESSION["update_directory"], 1, strlen($_SESSION["update_directory"])) . "';";
        }

        return $g_comp_database->num_rows($g_comp_database->query($l_sql)) > 0;
    }

    /**
     * Sets migration status to done for $p_migration_identifier.
     *
     * @param   string $p_migration_identifier
     *
     * @return  mixed
     */
    public function migration_done($p_migration_identifier)
    {
        global $g_comp_database;

        $g_comp_database->set_autocommit(true);

        $l_sql = "INSERT INTO isys_migration SET
			isys_migration__done = 1,
            isys_migration__executed_on = NOW(),
			isys_migration__version = '" . substr($_SESSION["update_directory"], 1, strlen($_SESSION["update_directory"])) . "',
			isys_migration__title = '" . $p_migration_identifier . "';";

        return $g_comp_database->query($l_sql);
    }

    public function migrate($p_path)
    {
        global $g_comp_database, $g_comp_database_system, $g_migration_log;

        $tenantDatabaseName = $g_comp_database_system->escape_string($g_comp_database->get_db_name());
        $query = $g_comp_database_system->query("SELECT isys_mandator__id FROM isys_mandator WHERE isys_mandator__db_name = '{$tenantDatabaseName}' LIMIT 1;");

        $tenantId = $g_comp_database->fetch_array($query)['isys_mandator__id'];

        $l_mig_executed = [];

        if (C__UPDATE_MIGRATION) {
            try {
                $l_log = isys_update_log::get_instance();

                $g_comp_database->query("SET FOREIGN_KEY_CHECKS = 0;");
                $g_comp_database_system->query("SET FOREIGN_KEY_CHECKS = 0;");

                $g_comp_database->set_autocommit(false);
                $g_comp_database_system->set_autocommit(false);

                if (is_dir($p_path)) {
                    // Using glob() since it also sorts the files alphabetically.
                    $l_files = glob(rtrim($p_path, "/\\") . '/*.php');
                    foreach ($l_files as $l_file) {
                        $l_file = str_replace($p_path, '', $l_file);
                        if (strpos($l_file, ".") !== 0 && strpos($l_file, '.php') > 0) {
                            if (file_exists($p_path . $l_file) && !is_dir($p_path . $l_file)) {
                                $l_log->debug("Starting migration: " . $p_path . $l_file);

                                $g_migration_log = [];
                                try {
                                    include($p_path . $l_file);

                                    $g_migration_log[] = "-";
                                    $l_mig_executed[] = $g_migration_log;
                                } catch (Exception $e) {
                                    $g_migration_log[] = "<span class=\"bold red indent\">" . $e->getMessage() . "</span>";
                                    $l_mig_executed[] = $g_migration_log;
                                }
                            }
                        }
                    }
                }

                if (is_array($g_migration_log)) {
                    foreach ($g_migration_log as $l_mig_log) {
                        $l_log->debug($l_mig_log);
                    }
                }
            } catch (Exception $e) {
                throw $e;
            }
        } else {
            $l_mig_executed[] = "<span class=\"bold grey indent\">Migration is currently deactivated. To activate the migration please set the migration constant to 'true'</span>";
        }

        return $l_mig_executed;
    }

    /**
     * Returns the corresponding foreign key name for field $p_field of table $p_table
     *
     * @param string $p_table
     * @param string $p_field
     *
     * @return string
     */
    public function get_foreign_key($p_table, $p_field = null, $p_checkTableExists = true)
    {
        global $g_comp_database;

        $l_exec = false;

        if ($p_checkTableExists) {
            $l_check = $g_comp_database->query("SHOW TABLES LIKE '" . $p_table . "';");
            if ($g_comp_database->num_rows($l_check) > 0) {
                $l_exec = true;
            }
        } else {
            $l_exec = true;
        }

        if ($l_exec) {
            $l_query = $g_comp_database->query("SHOW CREATE TABLE " . $p_table);
            $l_create = $g_comp_database->fetch_array($l_query);

            $l_sql = $l_create["Create Table"];
            $l_parsed = $this->parse_sql($l_sql);

            if ($p_field) {
                if (isset($l_parsed[$p_field])) {
                    return $l_parsed[$p_field][C__SQL__FOREIGN_KEY];
                }
            } else {
                return $l_parsed;
            }
        }

        return false;
    }

    public function delete_foreign_key($p_table, $p_fk)
    {
        global $g_comp_database;

        $l_update = "ALTER TABLE " . $p_table . " DROP FOREIGN KEY " . $p_fk;

        return $g_comp_database->query($l_update);
    }

    public function add_foreign_key($p_table, $p_field, $p_refTable, $p_refField, $p_onDelete, $p_onUpdate)
    {
        global $g_comp_database;

        $l_update = "ALTER TABLE " . $p_table . " " . "ADD FOREIGN KEY ( `" . $p_field . "` ) " . "REFERENCES `" . $p_refTable . "` (`" . $p_refField . "`) ON DELETE " .
            $p_onDelete . " ON UPDATE " . $p_onUpdate;
        $g_comp_database->query($l_update);
    }

    public function reinit_foreign_key($p_table, $p_field, $p_refTable, $p_refField, $p_onDelete, $p_onUpdate)
    {
        global $g_comp_database;

        $l_check = $g_comp_database->query("SHOW TABLES LIKE '" . $p_table . "'");
        if ($g_comp_database->num_rows($l_check) < 1) {
            return false;
        }

        while ($l_fk = $this->get_foreign_key($p_table, $p_field, false)) {
            $this->delete_foreign_key($p_table, $l_fk);
        }

        return $this->add_foreign_key($p_table, $p_field, $p_refTable, $p_refField, $p_onDelete, $p_onUpdate);
    }

    /**
     * Parses an sql string for CONSTRAINTS
     *
     * @param string $p_sql
     *
     * @return array
     */
    public function parse_sql($p_sql)
    {
        $l_fields = [];
        $l_query = explode("\n", $p_sql);
        $l_identifier = "[a-z0-9-_]+";

        $l_match = "/^.*CONSTRAINT[\s]*`([\s]*{$l_identifier}[\s]*)`[\s]*" . "FOREIGN KEY[\s]*\(`({$l_identifier})`\)[\s]*" .
            "REFERENCES[\s]*`({$l_identifier})`[\s]*\(`({$l_identifier})`\)(.*?)$/i";

        foreach ($l_query as $l_qline) {
            $l_register = [];

            if (preg_match($l_match, $l_qline, $l_register)) {
                if (preg_match(
                    "/.*?ON (DELETE|UPDATE)[\s]*(CASCADE|SET NULL|NO ACTION)[\s]*(?:ON (DELETE|UPDATE)[\s]*(CASCADE|SET NULL|NO ACTION))?/i",
                    $l_qline,
                    $l_constraint
                )) {
                    $l_constraint = [
                        $l_constraint[1] => $l_constraint[2],
                        $l_constraint[3] => $l_constraint[4],
                    ];
                } else {
                    $l_constraint = false;
                }

                $l_fields[$l_register[2]] = [
                    C__SQL__FOREIGN_KEY      => $l_register[1],
                    C__SQL__FIELD            => $l_register[2],
                    C__SQL__REFERENCED_TABLE => $l_register[3],
                    C__SQL__REFERENCED_FIELD => $l_register[4],
                    C__SQL__CONSTRAINT       => $l_constraint
                ];
            }
        }

        return $l_fields;
    }
}
