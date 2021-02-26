<?php

namespace idoit\Console\Command\Idoit;

use idoit\Console\Command\AbstractCommand;
use isys_auth;
use isys_auth_system_tools;
use isys_component_signalcollection;
use isys_log;
use isys_log_migration;
use isys_update;
use isys_update_files;
use isys_update_log;
use isys_update_migration;
use isys_update_property_migration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AutoUpdateCommand extends AbstractCommand
{
    const NAME         = 'system-update';
    const HIDE_COMMAND = true;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Get name for command
     *
     * @return string
     */
    public function getCommandName()
    {
        return self::NAME;
    }

    /**
     * Get description for command
     *
     * @return string
     */
    public function getCommandDescription()
    {
        return '';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();

        $definition->addOption(new InputOption('idoitVersion', null, InputOption::VALUE_REQUIRED));

        $definition->addOption(new InputOption('guessVersion', 'g', InputOption::VALUE_NONE));

        return $definition;
    }

    /**
     * Checks if a command can have a config file via --config
     *
     * @return bool
     */
    public function isConfigurable()
    {
        return true;
    }

    /**
     * Returns an array of command usages
     *
     * @return string[]
     */
    public function getCommandUsages()
    {
        return [];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        global $g_product_info, $g_versiondir, $g_updatedir, $g_absdir, $g_file_dir, $g_upd_dir, $g_log_dir;

        /* --------------------------- */
        /* Configuration ------------- */
        /* --------------------------- */
        ini_set("memory_limit", "512M");
        define("C__XML__SYSTEM", "update_sys.xml");
        define("C__XML__DATA", "update_data.xml");
        define("C__DIR__FILES", "files/");
        define("C__DIR__MIGRATION", "migration/");
        define("C__CHANGELOG", "ChangeLog");

        // Force licence status.
        $_SESSION['licenced'] = true;

        // Log directory.
        $g_log_dir = $g_absdir . '/log/';

        // Place where the i-doit update informations are stored. Where to search for updates.
        $g_updatedir = $g_absdir . '/updates/';
        $g_versiondir = $g_updatedir . 'versions/';

        $this->output->writeln("Autoup-Handler initialized (" . date("Y-m-d H:i:s") . ")");

        // Parse params sets the update_dir SESSION var.

        if (!$input->getOption('idoitVersion') && !$input->getOption('guessVersion')) {
            $this->output->writeln("Use -n flag to specify an update version or the -g flag to use the most recent one");

            return Command::SUCCESS;
        }

        // assign session variables.
        $_SESSION["update_directory"] = $input->getOption('idoitVersion') ?: "v" . $g_product_info['version'];
        $_SESSION["version_dir"] = $g_versiondir;

        $this->output->writeln("Updating to version: " . $_SESSION["update_directory"]);
        $g_upd_dir = $g_versiondir . $_SESSION["update_directory"];
        $g_file_dir = $g_upd_dir . DS . C__DIR__FILES;

        // check for read/write.
        $l_read_dirs = [
            $g_versiondir,
            $g_updatedir,
            $g_file_dir,
            $g_upd_dir,
            $g_log_dir
        ];
        $l_write_dirs = [
            $g_absdir,
            $g_versiondir,
            $g_updatedir,
            $g_file_dir,
            $g_upd_dir,
            $g_log_dir
        ];

        foreach ($l_read_dirs as $l_dir) {
            $this->helper_readable_dir($l_dir);
        }

        foreach ($l_write_dirs as $l_dir) {
            $this->helper_writeable_dir($l_dir);
        }

        $this->container->database_system->get_db_name();

        try {
            if ($this->get_includes()) {
                $this->start_update();
            } else {
                $this->output->writeln("Can't get includes. Doing nothing.");
            }
        } catch (\Exception $exception) {
            $this->output->writeln('<error>' . $exception->getMessage() . '</error>');
        }

        $this->output->writeln("Done");
        return Command::SUCCESS;
    }

    private function helper_readable_dir($p_dir)
    {
        if (!is_readable($p_dir) and is_dir($p_dir)) {
            throw new \Exception($p_dir . " is not readable");
        }
    }

    private function helper_writeable_dir($p_dir)
    {
        if (!is_writable($p_dir) and is_dir($p_dir)) {
            throw new \Exception($p_dir . " is not writable");
        }
    }

    /**
     * Get required classes.
     *
     * @return bool
     * @throws \Exception
     */
    private function get_includes()
    {
        global $g_updatedir;

        include_once $g_updatedir . "classes/isys_update.class.php";

        $l_fh = opendir($g_updatedir . "classes");

        while ($l_file = readdir($l_fh)) {
            if (strpos($l_file, ".") !== 0 && !include_once($g_updatedir . "classes/" . $l_file)) {
                throw new \Exception("Could not load " . $g_updatedir . DIRECTORY_SEPARATOR . $l_file, __FILE__, __LINE__);
            }
        }

        return true;
    }

    private function start_update()
    {
        global $g_log_dir;
        global $l_update, $l_info;

        // Check for update right
        if (defined('C__MODULE__SYSTEM')) {
            isys_auth_system_tools::instance()
                ->idoitupdate(isys_auth::EXECUTE);

            // Get log component.
            $l_log = isys_update_log::get_instance();

            // Get isys_update.
            $l_update = new isys_update();
            // Get system information.
            $l_info = $l_update->get_isys_info();

            // Get the mandant database.
            $g_databases = $l_update->get_databases();

            $l_mandant_databases = [];

            if (is_array($g_databases)) {
                foreach ($g_databases as $p_db) {
                    $l_mandant_databases[] = $p_db["name"];
                }
            }

            // assign this to the session, so the migration will work unchanged.
            $_SESSION["mandant_databases"] = $l_mandant_databases;

            $l_files = $this->get_files();

            // Trigger the update.
            $l_update->update($this->container->database_system->get_db_name(), $l_mandant_databases);

            // Write debug log.
            $l_log->write_debug();

            // The migration.
            //$this->output->writeln("Migrating database objects...");

            $l_migration_log_file = date("Y-m-d_H-i-s") . '_idoit_migration.log';

            // Load the databases.
            $l_update->get_databases();
            $l_migration = new isys_update_migration();
            $l_migration_log = [];

            global $g_upd_dir;
            if (is_array($_SESSION["mandant_databases"])) {
                if (count($_SESSION["mandant_databases"]) > 0) {
                    $this->output->writeln("Found tenant databases...");
                }

                $l_mig_log = isys_log_migration::get_instance()
                    ->set_log_file($g_log_dir . $l_migration_log_file)
                    ->set_log_level(isys_log::C__ALL);

                foreach ($_SESSION["mandant_databases"] as $l_db) {
                    $l_migration_log[$l_db] = $l_migration->migrate($g_upd_dir . "/" . C__DIR__MIGRATION);
                }

                unset($l_mig_log);
            }

            if (count($l_migration_log) <= 0) {
                $l_migration_log[$l_info["version"]][] = "No migration code needed this time.";
            }

            /*
            Property migration
            */
            $l_migration = new isys_update_property_migration();

            if (is_array($_SESSION["mandant_databases"])) {
                $l_mig_log = isys_log_migration::get_instance()
                    ->set_log_file($g_log_dir . 'prop_' . $l_migration_log_file)
                    ->set_log_level(isys_log::C__ALL);

                foreach ($_SESSION["mandant_databases"] as $l_db) {
                    $l_result[$l_db] = $l_migration->set_database($l_update->get_database($l_db))
                        ->reset_property_table()
                        ->collect_category_data()
                        ->prepare_sql_queries('g')
                        ->prepare_sql_queries('s')
                        ->execute_sql()
                        ->get_results();
                    $l_result[$l_db] = array_keys($l_result[$l_db]['migrated']);
                    sort($l_result[$l_db]);
                }
                unset($l_mig_log);
            }

            /**
             * Display result
             */
            $log = $l_log->get();
            if (!is_countable($log) || count($log) === 0) {
                $this->output->writeln('NO DATABASE CHANGES MADE!');
            } else {
                $this->output->writeln(count($log) . ' database changes made.');
            }

            if (!is_countable($l_files) || count($l_files) === 0) {
                $this->output->writeln('NO FILES UPDATED!');
            } else {
                $this->output->writeln(count($l_files) . ' files updated.');
            }

            if (C__UPDATE_MIGRATION && file_exists($g_log_dir . $l_migration_log_file)) {
                $this->output->writeln("Wrote migration log to log/" . $l_migration_log_file);
            }

            /* Call system has changed post notification */
            isys_component_signalcollection::get_instance()
                ->emit('system.afterChange');
        } else {
            throw new \Exception('Error: Could not create constants cache.');
        }
    }

    private function get_files()
    {
        global $g_file_dir;

        $l_files_array = [];
        if (strlen($_SESSION["update_directory"]) > 0) {
            if (is_dir($g_file_dir)) {
                $l_files = new isys_update_files($g_file_dir);
                $l_files_array = $l_files->getdir();
            }
        }

        return $l_files_array;
    }
}
