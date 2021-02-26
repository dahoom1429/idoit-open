<?php

namespace idoit\Module\Search\Index\Event;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class IndexingListener implements EventSubscriberInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ProgressBar|null
     */
    private $progressBar;

    /**
     * @var string
     */
    private $progressBarFormat = '%current%/%max% [%bar%] %percent:3s%% %elapsed:6s% %memory:6s%';

    /**
     * @var int
     */
    private $totalDocumentsMapped = 0;

    /**
     * @var int
     */
    private $totalDocumentsInserted = 0;

    /**
     * @var int
     */
    private $totalDocumentsSkipped = 0;

    /**
     * @var int
     */
    private $totalRows = 0;

    public function __construct(
        OutputInterface $output
    ) {
        $this->output = $output;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     * @codeCoverageIgnore
     */
    public static function getSubscribedEvents()
    {
        return [
            'index.error'                                  => 'onError',
            'index.start'                                  => 'beforeIndexing',
            'index.data.raw.retrieve'                      => 'onRetrieveRawData',
            'index.data.raw.execute_sql'                   => 'onExecuteSqlForRawData',
            'index.data.raw.progress.start'                => 'onRawDataProgressStart',
            'index.data.raw.progress.advance'              => 'onRawDataProgressAdvance',
            'index.data.raw.progress.finish'               => 'onRawDataProgressFinish',
            'index.data.document.mapping.progress.start'   => 'onDocumentMappingProgressStart',
            'index.data.document.mapping.progress.advance' => 'onDocumentMappingProgressAdvance',
            'index.data.document.mapping.progress.finish'  => 'onDocumentMappingProgressFinish',
            'index.data.document.mapping.progress.skipped' => 'onDocumentMappingSkipped',
            'index.data.document.insert.progress.start'    => 'onDocumentInsertProgressStart',
            'index.data.document.insert.progress.advance'  => 'onDocumentInsertProgressAdvance',
            'index.data.document.insert.progress.finish'   => 'onDocumentInsertProgressFinish',
            'index.finish'                                 => 'afterIndexing',
        ];
    }

    /**
     * @codeCoverageIgnore
     */
    public function beforeIndexing(GenericEvent $event)
    {
        $this->output->writeln(($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE ? date('H:i:s', $event->getArgument('startTime')) . '  ' : '') .
            'Start Indexing!');
        $this->output->writeln('');
    }

    /**
     * @codeCoverageIgnore
     */
    public function onRetrieveRawData(GenericEvent $event)
    {
        $this->totalRows += $event->getArgument('count');
        $this->output->writeln('Start reading ' . $event->getArgument('count') . ' rows for ' . $event->getArgument('context'));
    }

    /**
     * @codeCoverageIgnore
     */
    public function onExecuteSqlForRawData(GenericEvent $event)
    {
        $this->output->writeln('Executing sql for retrieving data...', OutputInterface::VERBOSITY_DEBUG);
        $this->output->writeln('', OutputInterface::VERBOSITY_DEBUG);
        $this->output->writeln('<info>' . $event->getArgument('sql') . '</info>', OutputInterface::VERBOSITY_DEBUG);
        $this->output->writeln('', OutputInterface::VERBOSITY_DEBUG);
    }

    public function onRawDataProgressStart(GenericEvent $event)
    {
        if ($this->output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE) {
            return;
        }

        $this->totalRows += $event->getArgument('count');
        $this->output->writeln('Start reading ' . $event->getArgument('count') . ' rows for ' . $event->getArgument('context'));

        $this->progressBar = new ProgressBar($this->output, $event->getArgument('count'));
        $this->progressBar->setFormat($this->progressBarFormat);
        $this->progressBar->start();
    }

    /**
     * @codeCoverageIgnore
     */
    public function onRawDataProgressAdvance(GenericEvent $event)
    {
        if ($this->progressBar !== null) {
            $this->progressBar->advance();
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function onRawDataProgressFinish(GenericEvent $event)
    {
        if ($this->progressBar !== null) {
            $this->progressBar->finish();
        }

        $this->output->writeln('');
        $this->output->writeln('Finished reading ' . $event->getArgument('count') . ' rows');
    }

    public function onDocumentMappingProgressStart(GenericEvent $event)
    {
        $this->output->writeln('');
        $this->output->writeln('Start mapping ' . $event->getArgument('count') . ' rows to ' . $event->getArgument('countOverall') . ' documents for ' .
            $event->getArgument('context'));

        if ($this->output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE) {
            return;
        }

        $this->progressBar = new ProgressBar($this->output, $event->getArgument('countOverall'));
        $this->progressBar->setFormat($this->progressBarFormat);
        $this->progressBar->start();
    }

    /**
     * @codeCoverageIgnore
     */
    public function onDocumentMappingProgressAdvance(GenericEvent $event)
    {
        ++$this->totalDocumentsMapped;

        if ($this->progressBar !== null) {
            $this->progressBar->advance($event->hasArgument('steps') ? $event->getArgument('steps') : 1);
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function onDocumentMappingProgressFinish(GenericEvent $event)
    {
        if ($this->progressBar !== null) {
            $this->progressBar->finish();
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function onDocumentMappingSkipped(GenericEvent $event)
    {
        $this->output->writeln('');
        $this->totalDocumentsSkipped += $event->getArgument('skipped');
        $this->output->writeln('<comment>' . $event->getArgument('skipped') . ' of '.$event->getArgument('total').' documents were skipped!</comment>');
    }

    /**
     * @codeCoverageIgnore
     */
    public function onDocumentInsertProgressStart(GenericEvent $event)
    {
        $this->output->writeln('');
        $this->output->writeln('Start ' . $event->getArgument('action') . ' documents');

        if ($this->output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE) {
            return;
        }

        $this->progressBar = new ProgressBar($this->output, $event->getArgument('count'));
        $this->progressBar->setFormat($this->progressBarFormat);
        $this->progressBar->start();
    }

    /**
     * @codeCoverageIgnore
     */
    public function onDocumentInsertProgressAdvance(GenericEvent $event)
    {
        ++$this->totalDocumentsInserted;

        if ($this->progressBar !== null) {
            $this->progressBar->advance();
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function onDocumentInsertProgressFinish(GenericEvent $event)
    {
        if ($this->progressBar !== null) {
            $this->progressBar->finish();
        }

        $this->output->writeln('');
        $this->output->writeln('Finished ' . $event->getArgument('action') . ' documents!');
        $this->output->writeln('');
    }

    /**
     * @codeCoverageIgnore
     */
    public function afterIndexing(GenericEvent $event)
    {
        $this->output->writeln(($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE ? date('H:i:s', $event->getArgument('endTime')) . '  ' : '') .
            'Finished indexing!');
        $this->output->writeln('');
        $this->output->writeln($this->totalRows . ' rows got read!');
        $this->output->writeln($this->totalDocumentsMapped . ' documents got mapped!');
        $this->output->writeln($this->totalDocumentsSkipped . ' documents got skipped while mapping, because of empty data!');
        $this->output->writeln($this->totalDocumentsInserted . ' documents got ' . $event->getArgument('action') . '!');
    }

    /**
     * @codeCoverageIgnore
     */
    public function onError(GenericEvent $event)
    {
        $this->output->writeln($event->getArgument('message'), $event->getArgument('verbosity'));

        /**
         * @var \Exception $exception
         */
        $exception = $event->getArgument('exception');
        $this->output->writeln($exception->getTrace(), OutputInterface::VERBOSITY_DEBUG);
    }
}
