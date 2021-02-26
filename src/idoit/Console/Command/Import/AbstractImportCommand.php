<?php

namespace idoit\Console\Command\Import;

use idoit\Console\Command\AbstractCommand;
use idoit\Console\Command\IsysLogWrapper;
use isys_cmdb_dao;
use isys_import_handler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractImportCommand extends AbstractCommand
{
    const NAME                   = 'import';
    const DEFAULT_IMPORT_HANDLER = 'cmdb';

    /**
     * @var array
     */
    protected $importHandler;

    abstract public function getImportHandler();

    /**
     * Get name for command
     *
     * @return string
     */
    public function getCommandName()
    {
        return $this::NAME;
    }

    /**
     * Get description for command
     *
     * @return string
     */
    public function getCommandDescription()
    {
        return 'Importer';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();
        $definition->addOption(new InputOption('importFile', null, InputOption::VALUE_REQUIRED));

        $definition->addOption(new InputOption('usage', null, InputOption::VALUE_NONE, 'Detailed information about how to run this command'));

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
        $this->loadExistingImportHandler();

        if ($input->getOption('usage')) {
            $this->usage($output);

            return Command::SUCCESS;
        }

        $output->writeln('<info>Import handler initialized at </info>' . date('Y-m-d H:i:s'));

        if ($input->hasOption('force') && $input->getOption('force')) {
            $output->writeln('<info>Import starting with --force!</info>');
        }

        if (!file_exists($input->getOption('importFile'))) {
            $output->writeln(sprintf('<error>Import file %s does not exist!</error>', $input->getOption('importFile')));
            $this->usage($output);

            return Command::SUCCESS;
        }

        // Retrieve import handler
        try {
            $importHandlerClass = $this->importHandler[$this->getImportHandler()];

            if (class_exists($importHandlerClass)) {
                $output->writeln(
                    sprintf('Fetch import type %s and load handler %s.', $this->getImportHandler(), $importHandlerClass),
                    OutputInterface::VERBOSITY_VERY_VERBOSE | OutputInterface::VERBOSITY_DEBUG
                );

                $logger = IsysLogWrapper::instance();
                $logger->setOutput($output);

                /**
                 * @todo: use logger more
                 * @var $importHandler isys_import_handler
                 */
                $importHandler = new $importHandlerClass($logger, $this->container->database);
            } else {
                if (empty($importHandlerClass)) {
                    $output->writeln(sprintf('<error>Type %s not registered.</error>', $this->getImportHandler()));
                    $this->usage($output);

                    return Command::SUCCESS;
                } else {
                    $output->writeln(sprintf('<error>Class %s not found.</error>', $importHandlerClass));
                    $this->usage($output);

                    return Command::SUCCESS;
                }
            }
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Type %s not found.</error>', $input->getOption('importHandler')) . $e->getMessage());
            $this->usage($output);

            return Command::SUCCESS;
        }

        // Load file:
        $output->writeln(sprintf('Load import file %s.', $input->getOption('importFile')));

        /**
         * @var $importHandler isys_import_handler
         */
        if (method_exists($importHandler, 'load_import')) {
            $importHandler->load_import($input->getOption('importFile'));

            try {
                // Fetch data:
                if ($importHandler->parse() === false) {
                    $output->writeln('<error>Unknown parse error while parsing file.</error>');
                    $this->usage($output);

                    return Command::SUCCESS;
                }

                // Prepare data:
                $importHandler->prepare();

                // Disconnect signals
                if (method_exists($importHandler, 'disconnectSignals')) {
                    $importHandler->disconnectSignals();
                }

                // Import data:
                // Parameter for import are only used when using inventory or csv import
                if (method_exists($importHandler, 'import')) {
                    if ($importHandler->import(
                        ($input->hasOption('objectType') ? $input->getOption('objectType') : null),
                        ($input->hasOption('force') ? $input->getOption('force') : null),
                        ($input->hasOption('objectId') ? $input->getOption('objectId') : null)
                    ) ===
                        false) {
                        $output->writeln('<error>Process was aborted.</error>');
                        $this->usage($output);

                        return Command::SUCCESS;
                    }
                }

                // Fire disconnected signals
                if (method_exists($importHandler, 'fireDisconnectedSignals')) {
                    $importHandler->fireDisconnectedSignals();
                }

                /**
                 * Post processing
                 */
                $importHandler->post();

                unset($importHandler);

                $output->writeln('Import done.');

                return Command::SUCCESS;
            } catch (\Exception $e) {
                $output->writeln('<error>' . $e->getMessage() . '</error>');
                return Command::FAILURE;
            }
        }

        $output->writeln('<error>Method load_import() does not exist in ' . get_class($importHandler) . '</error>');
        $this->usage($output);
    }

    /**
     * Register currently installed import handler
     *
     * @return array
     */
    private function loadExistingImportHandler()
    {
        $handler = [];

        $notSupported = [
            'isys_import_handler.class.php',
            'isys_import_handler_cabling.class.php',
            'isys_import_handler_csv.class.php'
        ];

        $importHandlerDir = __DIR__ . '/../../../../classes/import/handler/';

        if (is_dir($importHandlerDir)) {
            $importHandlerRes = opendir($importHandlerDir);
            while ($file = readdir($importHandlerRes)) {
                if ($file != "." && $file != ".." && !in_array($file, $notSupported) && is_file($importHandlerDir . "/" . $file)) {
                    $importHandle = preg_replace("/^isys_import_handler_(.*?).class.php$/", "\\1", $file);
                    $handler[$importHandle] = "isys_import_handler_" . $importHandle;
                }
            }
        }

        $this->importHandler = $handler;
    }

    /**
     * Prints out the usage of the import handler
     *
     * @param OutputInterface $output
     */
    abstract protected function usage(OutputInterface $output);
}
