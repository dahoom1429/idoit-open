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

class ContractOutdatedCommand extends AbstractCommand
{
    const NAME = 'contracts-outdated';

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
        return 'Updates status of outdated contracts';
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
        $this->dao = isys_cmdb_dao::instance($this->container->database);
        $output->writeln('Contracts status update starts...');
        $sql = "SELECT isys_cats_contract_list__id as id, 
                    isys_cats_contract_list__isys_obj__id as obj_id
                FROM isys_cats_contract_list
                WHERE isys_cats_contract_list__end_date < NOW()
                AND isys_cats_contract_list__end_date IS NOT NULL";
        $result = $this->dao->retrieve($sql);
        $results = [];
        while ($row = $result->get_row()) {
            $results[] = $row;
        }
        $result->free_result();
        $output->writeln('Found ' . count($results) . ' outdated contracts');

        $sql = "SELECT isys_contract_status__id as id
                FROM isys_contract_status
                WHERE isys_contract_status__title = " . $this->dao->convert_sql_text('LC__CMDB__CATS__MAINTENANCE_STATUS_FINISHED') .
                " LIMIT 1";
        $result = $this->dao->retrieve($sql);
        $statusFinished = $result->get_row_value('id');
        $result->free_result();

        foreach ($results as $contract) {
            $this->dao->set_object_cmdb_status($contract['obj_id'], C__CMDB_STATUS__INOPERATIVE);
            $this->dao->set_object_status($contract['obj_id'], C__RECORD_STATUS__ARCHIVED);
            $sql = "UPDATE isys_cats_contract_list 
                    SET isys_cats_contract_list__isys_contract_status__id = " . $this->dao->convert_sql_int($statusFinished) .
                  " WHERE isys_cats_contract_list__id = " . $this->dao->convert_sql_int($contract['id']);
            $this->dao->update($sql);
        }
        $this->dao->apply_update();

        $output->writeln('Contracts status update finished');
        return Command::SUCCESS;
    }
}
