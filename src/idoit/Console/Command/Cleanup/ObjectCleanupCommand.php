<?php

namespace idoit\Console\Command\Cleanup;

use idoit\Console\Command\AbstractCommand;
use isys_factory;
use isys_helper_textformat;
use isys_module_system;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ObjectCleanupCommand extends AbstractCommand
{
    const NAME = 'system-objectcleanup';

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
        return 'Purges optionally objects that are in the state unfinished, archived or deleted';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();
        $definition->addOption(new InputOption(
            'objectStatus',
            null,
            InputOption::VALUE_REQUIRED,
            'Use to start cleaning up the specified status:' .
                "\n<info>" . C__RECORD_STATUS__BIRTH . "</info> for '<comment>unfinished</comment>' objects" .
                "\n<info>" . C__RECORD_STATUS__ARCHIVED . "</info> for '<comment>archived</comment>' objects" .
                "\n<info>" . C__RECORD_STATUS__DELETED . "</info> for '<comment>deleted</comment>' objects"
        ));

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
        return [
            'system-objectcleanup -u admin -p admin -i 1 --objectStatus 3'
        ];
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting cleanup... ');

        $mapping = [
            C__RECORD_STATUS__BIRTH => 'unfinished',
            C__RECORD_STATUS__ARCHIVED => 'archived',
            C__RECORD_STATUS__DELETED => 'deleted'
        ];

        $objectStatus = (int)$input->getOption('objectStatus');

        if (!isset($mapping[$objectStatus])) {
            $output->writeln('<error>Please provide a valid obect status to clean up</error>');

            return Command::SUCCESS;
        }

        try {
            $count = (new isys_module_system())->cleanup_objects($objectStatus);

            $output->writeln('Unused objects with status <info>' . $mapping[$objectStatus] . '</info> deleted Total of <comment>' . $count . '</comment>.');
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }

        $output->writeln('Done');
        return Command::SUCCESS;
    }
}
