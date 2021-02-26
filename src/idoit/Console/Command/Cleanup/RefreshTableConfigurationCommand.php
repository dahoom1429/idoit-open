<?php

namespace idoit\Console\Command\Cleanup;

use Exception;
use idoit\Console\Command\AbstractCommand;
use idoit\Module\Cmdb\Component\Table\Config\Refresher;
use isys_application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RefreshTableConfigurationCommand
 *
 * @package   idoit\Console\Command\Cleanup
 * @copyright synetics GmbH
 * @package idoit\Console\Command\Cleanup
 */
class RefreshTableConfigurationCommand extends AbstractCommand
{
    const NAME = 'system-refresh-table-configuration';

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
        return 'Refreshes all available list configurations (object types and categories)';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $inputDefinition = new InputDefinition();

        $inputDefinition->addOptions([
            new InputOption(
                'all',
                'a',
                InputOption::VALUE_NONE,
                'Will process all object type table configurations'
            ),
            new InputOption(
                'object-types',
                'o',
                InputOption::VALUE_REQUIRED,
                'Process only specific object type table configurations via their constant (it is possible to pass a comma separated list of constants)'
            )
        ]);

        return $inputDefinition;
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

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting refreshing... ');

        if (!$input->getOption('all') && empty($input->getOption('object-types'))) {
            throw new Exception('Please provide either the --all or the --object-types option.');
        }

        try {
            $database = isys_application::instance()->container->get('database');
            $dao = isys_application::instance()->container->get('cmdb_dao');
            $refresher = new Refresher($database);

            if ($input->getOption('all')) {
                $refresher->processAll();
            } else {
                $objectTypes = explode(',', $input->getOption('object-types'));

                foreach ($objectTypes as $objectType) {
                    $objectTypeQuery = 'SELECT isys_obj_type__id AS id
                        FROM isys_obj_type 
                        WHERE isys_obj_type__const = ' . $dao->convert_sql_text($objectType) . ' 
                        LIMIT 1;';

                    $id = (int)$dao->retrieve($objectTypeQuery)->get_row_value('id');

                    if ($id > 0) {
                        $output->writeln('Processing object type "<comment>' . $objectType . '</comment>"', OutputInterface::VERBOSITY_VERBOSE);
                        $refresher->processByObjectTypeConstant($objectType);
                    } else {
                        $output->writeln('No object type found for the constant "<comment>' . $objectType . '</comment>"');
                    }
                }
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }

        $output->writeln('Done');
        return Command::SUCCESS;
    }
}
