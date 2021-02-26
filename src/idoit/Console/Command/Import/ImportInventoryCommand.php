<?php

namespace idoit\Console\Command\Import;

use isys_cmdb_dao;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportInventoryCommand extends AbstractImportCommand
{
    const NAME = 'import-hinventory';

    public function getCommandDescription()
    {
        return 'Imports files formatted in the hInventory XML syntax';
    }

    public function getImportHandler()
    {
        return 'inventory';
    }

    public function getCommandDefinition()
    {
        $definition = parent::getCommandDefinition(); // TODO: Change the autogenerated stub

        $definition->addOption(new InputOption('objectType', null, InputOption::VALUE_REQUIRED, 'Import by given object type'));

        $definition->addOption(new InputOption('objectId', null, InputOption::VALUE_REQUIRED, 'Import only given object'));

        $definition->addOption(new InputOption('force', 'f', InputOption::VALUE_NONE,
            'Force enables updating of existing objects/imports, but overwrites the imported categories'));

        return $definition;
    }

    /**
     * Prints out the usage of the import handler
     *
     * @param OutputInterface $output
     */
    protected function usage(OutputInterface $output)
    {
        $output->writeln("<comment>Usage:</comment>");
        $output->writeln("  import-hinventory --importFile inventory-export.xml [--objectType] [--objectId] [--force]");
        $output->writeln('');
        $output->writeln("  Example for importing a client with an inventory xml export:");
        $output->writeln("  import-hinventory --importFile imports/client_1.xml --objectType=10 --force");
        $output->writeln('');
        $output->writeln("  --force: Force enables updating of existing objects/imports, but overwrites the imported categories.");

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
     * Handle command parameters for h-inventory import
     *
     * @param $commandParams
     *
     * @return bool
     */
    public function validateParameters(&$commandParams)
    {
        global $argv;
        $objectId = null;

        if (!empty($_SERVER['HTTP_HOST'])) {
            $objectType = $_GET['obj_type'];
        } else {
            if (is_array($argv)) {
                $cmd = $argv;
                $objectType = $cmd[2];
            } else {
                return false;
            }
        }

        if (!is_numeric($objectType)) {
            if (defined('C__OBJTYPE__CLIENT')) {
                $objectType = C__OBJTYPE__CLIENT;
                (!empty($_GET['force'])) ? $force = true : $force = $cmd[2];

                (!empty($_GET['object_id__HIDDEN'])) ? $objectId = $_GET['object_id__HIDDEN'] : $objectId = $cmd[3];
            }
        } else {
            (!empty($_GET['force'])) ? $force = true : $force = $cmd[3];
            if (is_numeric($force)) {
                $force = false;
            }

            (!empty($_GET['object_id__HIDDEN'])) ? $objectId = $_GET['object_id__HIDDEN'] : $objectId = (isset($cmd[4])) ? $cmd[4] : $cmd[3];
        }

        if ($force == '--force') {
            $force = true;
        }

        if ($objectType) {
            $commandParams['--objectType'] = $objectType;
        }

        if ($objectId) {
            $commandParams['--objectId'] = $objectId;
        }

        if ($force) {
            $commandParams['--force'] = $force;
        }

        return true;
    }
}
