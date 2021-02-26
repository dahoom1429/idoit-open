<?php

namespace idoit\Console\Command\Import\Csv;

use idoit\Console\Command\AbstractCommand;
use isys_module_import_csv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListProfilesCommand extends AbstractCommand
{
    const NAME = 'import-csvprofiles';

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
        return 'List all available csv profiles';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();

        return $definition;
    }

    /**
     * Checks if a command can have a config file via --config
     *
     * @return bool
     */
    public function isConfigurable()
    {
        return false;
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
        $l_profiles = isys_module_import_csv::get_profiles();

        if (is_array($l_profiles) && count($l_profiles)) {
            $output->writeln('List of profiles:');

            foreach ($l_profiles as $l_profile) {
                $output->writeln(str_pad($l_profile['id'], 5, ' ', STR_PAD_LEFT) . ': ' . $l_profile['title']);
            }
            return Command::SUCCESS;
        } else {
            $output->writeln('<error>Attention: No profiles found! You need to provide at least one profile to import a CSV file! Please create one using the CSV import GUI.</error>');
            return Command::FAILURE;
        }
    }
}
