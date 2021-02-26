<?php

namespace idoit\Console\Command\Logbook;

use idoit\Console\Command\AbstractCommand;
use isys_component_dao_archive;
use isys_component_dao_logbook;
use isys_convert;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ArchiveCommand extends AbstractCommand
{
    const NAME = 'logbook-archive';

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
        return 'Archives Logbook entries (Settings are defined in the GUI)';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        return new InputDefinition();
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

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        global $g_db_system;

        // Get daos, because now we are logged in.
        $daoLogbook = new isys_component_dao_logbook($this->container->database);

        $output->writeln([
            'Logbook archiving-handler initialized - <comment>' . date("Y-m-d H:i:s") . '</comment>',
            ''
        ]);

        // Check status and add to logbook.
        try {
            $settings = $daoLogbook->getArchivingSettings();
            $localDatabase = $settings['dest'] == 0;

            if ($localDatabase) {
                $database = $this->container->database;
                $output->writeln('<info>Using local database</info>');
            } else {
                try {
                    $output->writeln('Using remote database <info>' . $settings["host"] . '</info>');

                    $database = \isys_component_database::get_database(
                        $g_db_system["type"],
                        $settings["host"],
                        $settings["port"],
                        $settings["user"],
                        $settings["pass"],
                        $settings["db"]
                    );
                } catch (\Exception $e) {
                    throw new \Exception('Logbook archiving: Failed to connect to ' . $settings["host"]);
                }
            }

            (new isys_component_dao_archive($database))
                ->setOutput($output)
                ->archive($daoLogbook, null, $settings['interval'], $localDatabase);

            $output->writeln([
                '<comment>Memory peak: ' . (memory_get_peak_usage(true) / 1024 / 1024) . ' MB</comment>',
                '',
                'Finished the process - <comment>' . date("Y-m-d H:i:s") . '</comment>'
            ]);
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
