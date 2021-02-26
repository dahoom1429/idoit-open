<?php

namespace idoit\Module\Console\Console\Command\Tenant;

use Exception;
use isys_application;
use isys_component_dao_mandator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListTenantsCommand extends Command
{
    /**
     * Pre configure child commands
     */
    protected function configure()
    {
        $this->setName('tenant-list');
        $this->setDescription('Shows list of available tenants');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $exitCode = Command::SUCCESS;
        $table = new Table($output);

        $output->writeln('<info>Available tenants:</info>');

        $table->setHeaders(['ID', 'Title', '(host:port)', 'database', '[status]']);

        $rows = [];

        try {
            $dao = new isys_component_dao_mandator(isys_application::instance()->container->get('database_system'));
            $tenants = $dao->get_mandator(null, 0);
            while ($tenantData = $tenants->get_row()) {
                $rows[] = [
                    $tenantData["isys_mandator__id"],
                    $tenantData["isys_mandator__title"],
                    "(" . $tenantData["isys_mandator__db_host"] . ":" . $tenantData["isys_mandator__db_port"] . ")",
                    $tenantData["isys_mandator__db_name"],
                    $tenantData["isys_mandator__active"] == 1 ? 'active' : 'inactive'
                ];
            }

            $table->setRows($rows);
            $table->render();
        } catch (Exception $e) {
            $output->writeln('<error>Something went wrong with message: ' . $e->getMessage() . '</error>');
            $exitCode = Command::FAILURE;
        }

        return $exitCode;
    }
}
