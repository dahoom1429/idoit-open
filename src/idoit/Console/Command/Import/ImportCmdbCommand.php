<?php

namespace idoit\Console\Command\Import;

use isys_cmdb_dao;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCmdbCommand extends AbstractImportCommand
{
    const NAME = 'import-xml';

    public function getCommandDescription()
    {
        return 'Imports files formatted in the i-doit XML syntax';
    }

    public function getImportHandler()
    {
        return 'cmdb';
    }

    /**
     * Prints out the usage of the import handler
     *
     * @param OutputInterface $output
     */
    protected function usage(OutputInterface $output)
    {
        $output->writeln("<comment>Usage:</comment>");
        $output->writeln("  import-xml --importFile cmdb-export.xml");
        $output->writeln('');
        $output->writeln("  Example for importing a client with an cmdb xml export:");
        $output->writeln("  import-xml --importFile imports/client_1.xml");

        $output->writeln('');
        $output->writeln("  Object Types:");

        $output->writeln("  <info>ID  Object-Type</info>");

        $l_dao = new isys_cmdb_dao($this->container->database);
        $l_otypes = $l_dao->get_types();
        while ($l_row = $l_otypes->get_row()) {

            $output->writeln('  ' . $l_row["isys_obj_type__id"] . ":  " . $l_row["isys_obj_type__const"]);

        }
    }

    /**
     * Handle command parameters for the cmdb import command
     *
     * @param $commandParams
     *
     * @return bool
     */
    public function validateParameters(&$commandParams)
    {
        return true;
    }
}
