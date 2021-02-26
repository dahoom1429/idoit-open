<?php

namespace idoit\Console\Command\Idoit;

use idoit\Console\Command\AbstractCommand;
use isys_cmdb_dao;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IncrementConfigCommand extends AbstractCommand
{
    const NAME = 'system-autoincrement';

    /**
     * @var isys_cmdb_dao
     */
    private $dao;

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
        return 'Changes the initial autoincrement value for all i-doit database tables (Affects everything, Object-IDs, category entries, etc. Use with caution!)';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();

        $definition->addOption(new InputOption('autoIncrement', null, InputOption::VALUE_REQUIRED, 'Zahl auf die der AUTO_INCREMENT gesetzt werden soll'));

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
        if (!$input->getOption('autoIncrement')) {
            $output->writeln('Please provide a value for --autoIncrement!');

            return Command::SUCCESS;
        }

        $success = 0;
        $fail = 0;

        $this->dao = isys_cmdb_dao::instance($this->container->database);

        $output->writeln('Setze AUTO_INCREMENT für alle Tabellen auf ' . $input->getOption('autoIncrement'));

        $tableResult = $this->dao->retrieve('SHOW TABLES;');

        while ($tableRow = $tableResult->get_row()) {
            $table = current($tableRow);

            if ($this->setAutoIncrement($table, $input->getOption('autoIncrement'))) {
                $success++;
                $output->writeln('<info>   OK' . '</info> - Tabelle "' . $table . '"');
            } else {
                $fail++;
                $output->writeln('<error> FAIL</error> - Tabelle "' . $table . '"');
                $output->writeln('        AUTO_INCREMENT konnte für Tabelle "' . $table . '" nicht gesetzt werden.');
            }
        }

        $output->writeln('Processed ' . ($success + $fail) . ' tables, <info>' . $success . '</info> successful and <error>' . $fail . '</error> failed.');
        return Command::SUCCESS;
    }

    /**
     * @param string $table
     *
     * @param int    $incrementValue
     *
     * @return bool
     */
    private function setAutoIncrement($table, $incrementValue)
    {
        return $this->dao->update('ALTER TABLE ' . $table . ' AUTO_INCREMENT = ' . $incrementValue . ';');
    }
}
