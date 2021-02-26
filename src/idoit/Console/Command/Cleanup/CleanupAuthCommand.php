<?php

namespace idoit\Console\Command\Cleanup;

use idoit\Console\Command\AbstractCommand;
use isys_auth_module_dao;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupAuthCommand extends AbstractCommand
{
    const NAME = 'auth-cleanup';

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
        return 'Cleanup all auth paths';
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Auth paths cleanup initialized (" . date("Y-m-d H:i:s") . ")");

        /* Cleanup all auth paths */
        try {
            isys_auth_module_dao::cleanup_all();
            $output->writeln("Cleanup done.");
        } catch (\Exception $e) {
            $output->writeln("<error>There was an error while cleaning up auth paths.</error>");
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
