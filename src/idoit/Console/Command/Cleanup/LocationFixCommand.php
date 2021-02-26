<?php

namespace idoit\Console\Command\Cleanup;

use idoit\Console\Command\AbstractCommand;
use isys_application;
use isys_cmdb_dao_location;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LocationFixCommand
 *
 * @package   idoit\Console\Command\Cleanup
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class LocationFixCommand extends AbstractCommand
{
    const NAME = 'system-location-fix';

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
        return 'Performs the location fix from the systemtools GUI';
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
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting cleanup... ');

        try {
            $daoLocation = isys_cmdb_dao_location::instance(isys_application::instance()->container->get('database'));
            $daoLocation->_location_fix();
            $statistics = $daoLocation->getLocationFixStats();

            foreach ($statistics as $statistic => $counter) {
                // Error messages will be suffixed with ' ERROR'.
                if (substr($statistic, -5) === 'ERROR') {
                    // Only display errors if they occured.
                    if ($counter > 0) {
                        $statistic = substr($statistic, 0, -6);

                        $output->writeln("{$statistic}: <error>{$counter} errors</error>");
                    }

                    continue;
                }

                if ($counter === 0) {
                    $output->writeln("<info>{$statistic}</info>: not necessary", OutputInterface::VERBOSITY_VERY_VERBOSE);
                } else {
                    $output->writeln("<comment>{$statistic}</comment>: {$counter}", OutputInterface::VERBOSITY_VERBOSE);
                }
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln('Done');
        return Command::SUCCESS;
    }
}
