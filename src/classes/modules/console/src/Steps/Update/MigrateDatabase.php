<?php

namespace idoit\Module\Console\Steps\Update;

use Exception;
use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;
use idoit\Module\Console\Steps\Step;
use isys_component_dao;
use isys_component_database;
use isys_update;
use isys_update_migration;

/**
 * Class MigrateDatabase
 */
class MigrateDatabase implements Step
{
    /**
     * @var isys_component_database
     */
    private $database;

    /**
     * @var string
     */
    private $migrationDirectory;

    /**
     * MigrateDatabase constructor.
     *
     * @param string                  $migrationDirectory
     * @param isys_component_database $database
     */
    public function __construct(string $migrationDirectory, isys_component_database $database)
    {
        $this->migrationDirectory = $migrationDirectory;
        $this->database = $database;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Migrate properties of `' . $this->database->get_db_name() . '`` with ' . $this->migrationDirectory;
    }

    /**
     * Process the work
     *
     * @param Messages $messages
     *
     * @return bool
     */
    public function process(Messages $messages)
    {
        global $g_comp_database;

        $tenantDatabase = $g_comp_database;

        $migration = new isys_update_migration();

        $messages->addMessage(new StepMessage($this, 'Start migration', ErrorLevel::DEBUG));

        try {
            $g_comp_database = $this->database;
            $logs = $migration->migrate($this->migrationDirectory);
        } catch (Exception $exception) {
            $messages->addMessage(new StepMessage($this, 'Migration failed', ErrorLevel::ERROR));

            return false;
        } finally {
            $g_comp_database = $tenantDatabase;
        }

        foreach ($logs as $log) {
            foreach ($log as $logItem) {
                $messages->addMessage(new StepMessage($this, $logItem, ErrorLevel::DEBUG));
            }
        }

        $messages->addMessage(new StepMessage($this, 'Migration is successful', ErrorLevel::INFO));

        return true;
    }
}
