<?php

namespace idoit\Console\Command\Search;

use idoit\Console\Command\AbstractCommand;
use idoit\Module\Search\Query\Engine\Mysql\SearchEngine;
use idoit\Module\Search\Query\QueryManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper;

class SearchCommand extends AbstractCommand
{
    const NAME = 'search';

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
        return 'Triggers a search and gives the results as formatted text table';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();
        $definition->addOption(new InputOption('searchString', null, InputOption::VALUE_REQUIRED, 'String to search for.'));

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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);

        $manager = QueryManager::factory()
            ->attachEngine(new SearchEngine())
            ->addSearchKeyword($input->getOption('searchString'));

        $queryResult = $manager->search();
        $result = $queryResult->getResult();

        $table = new Helper\Table($output);
        $table->setHeaders([
            'ID',
            'Key',
            'Found Match',
            'Score'
        ]);

        foreach ($result as $item) {
            $table->addRow([
                $item->getDocumentId(),
                $item->getKey(),
                '<info>' . htmlspecialchars_decode($item->getValue()) . '</info>',
                '<comment>' . number_format(floatval($item->getScore()), 2) . '</comment>',
            ]);
        }

        $table->render();

        // Sum it all up
        $end = microtime(true);
        $time = number_format($end - $start, 4);

        $output->writeln(sprintf("Search process took <info>%s</info>s.", $time));

        return Command::SUCCESS;
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
}
