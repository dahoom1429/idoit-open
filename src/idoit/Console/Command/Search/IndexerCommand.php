<?php

namespace idoit\Console\Command\Search;

use idoit\Console\Command\AbstractCommand;
use idoit\Module\Cmdb\Search\Index\Data\CategoryCollector;
use idoit\Module\Search\Index\Engine\Mysql;
use idoit\Module\Search\Index\Event\NullEventDispatcher;
use idoit\Module\Search\Index\Manager;
use isys_application;
use isys_tenantsettings;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper;

class IndexerCommand extends AbstractCommand
{
    const NAME = 'search-index';

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
        return 'Deletes current search index and create it';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();

        $definition->addOption(new InputOption('update', null, InputOption::VALUE_NONE, 'Instead of only creating a new index the current index documents will be overwritten and created'));

        $definition->addOption(new InputOption(
            'category',
            null,
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            "Whitelist given categories, will overwrite configured whitelist in expert settings \n You'll find the category constants at " . isys_tenantsettings::get('system.base.uri') . "?load=api_properties"
        ));

        $definition->addOption(new InputOption('dry-run', null, InputOption::VALUE_NONE, 'Execute indexing without saving'));

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
        return [];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $searchEngine = new Mysql($this->container->get('database'));

        $documentsReferences = [];

        if ($input->getOption('update')) {
            $documentsReferences = $searchEngine->retrieveUniqueDocumentReferences();
        }

        $collector = new CategoryCollector($this->container->get('database'), [], $documentsReferences);

        if (!empty($input->getOption('category'))) {
            $collector->setWhitelistedSources($input->getOption('category'));
        }

        $dispatcher = isys_application::instance()->container->get('event_dispatcher');

        if ($output->getVerbosity() === OutputInterface::VERBOSITY_QUIET) {
            $dispatcher = new NullEventDispatcher();
        }

        $manager = new Manager($searchEngine, $dispatcher);
        $manager->addCollector($collector, 'categoryCollector');
        $manager->setOutput($output);

        if (!$input->getOption('update')) {
            $manager->clearIndex();
        }

        if ($input->getOption('update')) {
            $manager->setMode(Manager::MODE_OVERWRITE);
        }

        if ($input->getOption('dry-run')) {
            $manager->enableDryRun();
        }

        $manager->generateIndex();

        return Command::SUCCESS;
    }
}
