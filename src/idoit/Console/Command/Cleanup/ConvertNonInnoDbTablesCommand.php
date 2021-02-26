<?php

namespace idoit\Console\Command\Cleanup;

use isys_cmdb_dao;
use isys_component_dao_result;
use Exception;
use idoit\Console\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class for converting Non INNODB category tables to INNODB.
 *
 * @package idoit\Console\Command\Idoit
 * @author  Van Quyen Hoang <qhoang@i-doit.com>
 */
class ConvertNonInnoDbTablesCommand extends AbstractCommand
{
    const NAME = 'system-convert-non-innodb-tables';

    /**
     * @var isys_cmdb_dao
     */
    private $dao;

    /**
     * @var array
     */
    private $allCategoryTables = [];

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
        return 'Converts all tables which are not in INNODB to INNODB (Affects database encoding. Use with caution!)';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();

        $definition->addOption(new InputOption('convert', null, InputOption::VALUE_NONE, 'Start converting all tables'));
        $definition->addOption(new InputOption('table', null, InputOption::VALUE_OPTIONAL, 'Table which will be checked and converted'));

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

    /**
     * Deletes a complete category entry
     *
     * @param string $table
     * @param string $objectReferenceField
     *
     * @return bool
     * @throws \isys_exception_dao
     */
    private function removeCategoryEntry($table, $objectReferenceField)
    {
        $query = "DELETE FROM {$table} WHERE {$objectReferenceField} NOT IN (SELECT isys_obj__id FROM isys_obj)";
        return $this->dao->update($query);
    }

    /**
     * Set specified db field to null
     *
     * @param string $table
     * @param string $objectReferenceField
     * @param string $referenceTable
     * @param string $referenceIdField
     *
     * @return bool
     * @throws \isys_exception_dao
     */
    private function setReferenceToNull($table, $objectReferenceField, $referenceTable, $referenceIdField)
    {
        $query = "UPDATE {$table} SET {$objectReferenceField} = NULL WHERE {$objectReferenceField} NOT IN (SELECT {$referenceIdField} FROM {$referenceTable});";
        return $this->dao->update($query);
    }

    /**
     * Handles the removal of entries where the object id does not exist and
     * setting the object field to null if its an attribute of a category
     *
     * @param OutputInterface $output
     * @param array           $referenceData
     *
     * @throws \isys_exception_dao
     */
    private function handleReferenceData($output, $referenceData)
    {
        if (in_array($referenceData['TABLE_NAME'], $this->allCategoryTables) &&
            $referenceData['REFERENCEID'] === 'isys_obj__id' &&
            $referenceData['REFERENCETABLE'] === 'isys_obj') {
            // Remove all category entries which object does not exist
            $this->removeCategoryEntry($referenceData['TABLE_NAME'], $referenceData['COLUMN_NAME']);
            $affectedRows = $this->dao->affected_after_update();
            if ($affectedRows > 0) {
                $output->writeln("  [<comment>Warning</comment>] {$affectedRows} Entries in Category Table <comment>{$referenceData['TABLE_NAME']}</comment> has been removed because the referenced object does not exists anymore!");
            } else {
                $output->writeln("  [<info>OK</info>] 0 Entries in Category Table <comment>{$referenceData['TABLE_NAME']}</comment> found which referenced object does not exists anymore.");
            }
            return true;
        } elseif ($this->dao->table_exists($referenceData['REFERENCETABLE'])) {
            // Set Entry where the object id is to null
            $this->setReferenceToNull($referenceData['TABLE_NAME'], $referenceData['COLUMN_NAME'], $referenceData['REFERENCETABLE'], $referenceData['REFERENCEID']);
            $affectedRows = $this->dao->affected_after_update();
            if ($affectedRows > 0) {
                $output->writeln("  [<comment>Warning</comment>] {$affectedRows} Entries in Category Table <comment>{$referenceData['TABLE_NAME']}</comment> where the attribute <comment>{$referenceData['COLUMN_NAME']}</comment> has been set to NULL because the referenced id does not exists anymore!");
            } else {
                $output->writeln("  [<info>OK</info>] 0 Entries in Category Table <comment>{$referenceData['TABLE_NAME']}</comment> where the attribute <comment>{$referenceData['COLUMN_NAME']}</comment> is referenced to a non existing id.");
            }
            return true;
        }
        return false;
    }

    /**
     * @param string $table
     *
     * @return isys_component_dao_result
     * @throws \isys_exception_database
     */
    private function getAffectedColumns($table)
    {
        $databaseName = $this->container->get('database')
            ->get_db_name();

        $query = "SELECT 
                TABLE_NAME, COLUMN_NAME, 
                REPLACE(COLUMN_NAME, CONCAT(TABLE_NAME, '__'), '') as REFERENCEID, 
                REPLACE(REPLACE(COLUMN_NAME, CONCAT(TABLE_NAME, '__'), ''), '__id', '') as REFERENCETABLE
            FROM information_schema.COLUMNS
            WHERE 
                  TABLE_SCHEMA = '{$databaseName}'
                  AND TABLE_NAME = '{$table}'
                  AND COLUMN_NAME LIKE '{$table}__isys_%'";

        return $this->dao->retrieve($query);
    }

    /**
     * Add Foreign keys to reference table
     *
     * @param array $foreignKeyData
     *
     * @throws \isys_exception_dao
     */
    private function addForeignKeysToReference($foreignKeyData)
    {
        foreach ($foreignKeyData as $data) {
            $deleteCascade = 'SET NULL';
            if ($data['REFERENCETABLE'] === 'isys_obj') {
                $deleteCascade = 'CASCADE';
            }

            $query = "ALTER TABLE {$data['TABLE_NAME']} ADD FOREIGN KEY (`{$data['COLUMN_NAME']}`) REFERENCES `{$data['REFERENCETABLE']}` (`{$data['REFERENCEID']}`) ON DELETE {$deleteCascade} ON UPDATE CASCADE";
            $this->dao->update($query);
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \isys_exception_database
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('convert')) {
            $output->writeln([
                '<comment>You did not pass the <info>--convert</info> option, the following output will only list the affected tables and NOT convert them to engine INNODB.</comment>',
                ''
            ]);
        } else {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');

            $question = new ConfirmationQuestion('Did you backup your database? (<comment>yes</comment>/<comment>no</comment>)', true);

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('Please backup you database, before changing any tables to the INNODB engine!');

                return Command::SUCCESS;
            }

            $output->writeln('');
        }

        $specificiedTable = $input->getOption('table');

        $success = 0;
        $fail = 0;

        $this->dao = isys_cmdb_dao::instance($this->container->get('database'));

        $tables = $this->getAffectedTables($specificiedTable);
        $this->allCategoryTables = $this->dao->getAllCategoryTablesAsArray();

        if (!count($tables)) {
            if ($specificiedTable) {
                $output->writeln([
                    "Table <comment>{$specificiedTable}</comment> is already using the INNODB engine, no need for any conversion!"
                ]);
            } else {
                $output->writeln([
                    "All tables are using the INNODB engine, no need for any conversion!"
                ]);
            }
        }

        foreach ($tables as $table => $engine) {
            if ($input->getOption('convert')) {
                try {
                    $foreignKeyData = [];

                    if (in_array($table, $this->allCategoryTables)) {
                        $result = $this->getAffectedColumns($table);

                        if ($result instanceof isys_component_dao_result) {
                            while ($referenceData = $result->get_row()) {
                                if ($this->handleReferenceData($output, $referenceData)) {
                                    $foreignKeyData[] = $referenceData;
                                }
                            }
                        }
                    }

                    $alterTableQuery = "ALTER TABLE {$table} ENGINE=InnoDB;";

                    if ($this->dao->update($alterTableQuery) && $this->dao->apply_update()) {
                        $success++;
                        $output->writeln("  [<info>OK</info>] Table <comment>{$table}</comment> engine changed from <comment>{$engine}</comment> to <comment>INNODB</comment>!");

                        // Reinit Foreign Keys to Reference Table
                        if (!empty($foreignKeyData)) {
                            $this->addForeignKeysToReference($foreignKeyData);
                        }
                    } else {
                        $fail++;
                        $output->writeln("  [<error>FAIL</error>] Table <comment>{$table}</comment> engine could not be changed from <comment>{$engine}</comment> to <comment>INNODB</comment>!");
                    }
                } catch (Exception $e) {
                    $fail++;
                    $output->writeln("<error>An error occured while changing table {$table} from engine <comment>{$engine}</comment> to <comment>INNODB</comment>:</error> {$e->getMessage()}");
                }
            } else {
                $output->writeln("Found table <info>{$table}</info> with engine <comment>{$engine}</comment>.");
            }
        }

        $output->writeln([
            '',
            'Processed ' . ($success + $fail) . ' tables, <info>' . $success . '</info> successful and <error>' . $fail . '</error> failed.'
        ]);
        return Command::SUCCESS;
    }

    /**
     * This method will return all tables, which are not using the INNODB Engine.
     *
     * @param string|null $specificTable
     *
     * @return array
     * @throws \isys_exception_database
     */
    private function getAffectedTables($specificTable = null)
    {
        $databaseName = $this->container->get('database')
            ->get_db_name();
        $tablesToConvert = [];

        $tableSql = "SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = " . $this->dao->convert_sql_text($databaseName) . "
            AND ENGINE NOT LIKE 'innodb'";

        if ($specificTable !== null) {
            $tableSql .= ' AND TABLE_NAME = ' . $this->dao->convert_sql_text($specificTable);
        }

        $tableResult = $this->dao->retrieve($tableSql);

        if ($tableResult instanceof isys_component_dao_result && count($tableResult)) {
            while ($row = $tableResult->get_row()) {
                $tablesToConvert[$row['TABLE_NAME']] = $row['ENGINE'];
            }
        }

        return $tablesToConvert;
    }
}
