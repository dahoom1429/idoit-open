<?php

namespace idoit\Console\Command\Idoit;

use Exception;
use idoit\Console\Command\AbstractCommand;
use isys_settings;
use isys_update;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateUploadedFilesCommand extends AbstractCommand
{
    const NAME           = 'migrate-uploaded-files';
    const REQUIRES_LOGIN = false;

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
        return 'Migrates uploaded files in i-doit <v1.13 to v.1.14>';
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

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        global $g_absdir;

        $output->writeln('Starting migrating files... Please wait..');
        $filesPath = $g_absdir . '/upload/files/';
        $files = scandir($filesPath);
        foreach ($files as $filename) {
            if (is_file($filename)) {
                $output->writeln('Migrating ' . $filename);
                $newPath = \isys_application::instance()->getOrCreateUploadFileDir($filename);
                rename($filesPath . $filename, $newPath . $filename);
            }
        }
        $output->writeln('All files in upload folder migrated.');
        return Command::SUCCESS;
    }
}
