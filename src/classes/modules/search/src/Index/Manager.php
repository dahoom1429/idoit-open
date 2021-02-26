<?php

namespace idoit\Module\Search\Index;

use idoit\Module\Search\Index\Data\AbstractCollector;
use idoit\Module\Search\Index\Engine\SearchEngine;
use idoit\Module\Search\Index\Event\IndexingListener;
use idoit\Module\Search\Index\Exception\DocumentExists;
use isys_application;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * i-doit
 *
 * Manager
 *
 * @package     i-doit
 * @subpackage  Search
 * @author      Kevin Mauel <kmauel@i-doit.com>
 * @version     1.11
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Manager
{
    /**
     * Inserting documents into index
     */
    const MODE_CREATION = 1;

    /**
     * Updating documents in index
     */
    const MODE_UPDATE = 2;

    /**
     * Delete documents in index
     */
    const MODE_DELETE = 3;

    /**
     * Either creates or overwrites documents in index
     */
    const MODE_OVERWRITE = 4;

    /**
     * @var AbstractCollector[]
     */
    private $collectors = [];

    /**
     * @var bool
     */
    private $dryRun = false;

    /**
     * @var SearchEngine
     */
    private $engine;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var int
     */
    private $mode = self::MODE_CREATION;

    /**
     * @param OutputInterface $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * @param int $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * Enable dry run, which means execute without saving
     */
    public function enableDryRun()
    {
        $this->dryRun = true;
    }

    /**
     * Adds an index data collector to manager instance, if already exists it will overwrite existing instance of collector
     *
     * @param AbstractCollector $collector
     * @param string $collectorName
     */
    public function addCollector(AbstractCollector $collector, $collectorName)
    {
        $this->collectors[$collectorName] = $collector;
    }

    /**
     * @param string $collectorName
     *
     * @return AbstractCollector
     */
    public function getCollector($collectorName)
    {
        return $this->collectors[$collectorName];
    }

    /**
     * Manager constructor.
     *
     * @param SearchEngine             $engine
     * @param EventDispatcherInterface|array<AbstractCollector> $eventDispatcher
     */
    public function __construct(
        SearchEngine $engine,
        $eventDispatcher
    ) {
        $this->engine = $engine;
        if ($eventDispatcher instanceof EventDispatcherInterface) {
            $this->eventDispatcher = $eventDispatcher;
        } else {
            $this->eventDispatcher = isys_application::instance()->container->get('event_dispatcher');
        }
        // @todo This is a hack for compatibility. Should be removed in 1.11.1
        if (is_array($eventDispatcher)) {
            $this->collectors = $eventDispatcher;
        }

        $this->output = new NullOutput();
    }

    public function generateIndex()
    {
        $this->eventDispatcher->addSubscriber(new IndexingListener($this->output));

        $action = 'inserting';

        if ($this->mode === self::MODE_UPDATE) {
            $action = 'updating';
        }

        if ($this->mode === self::MODE_DELETE) {
            $action = 'deleting';
        }

        if ($this->mode === self::MODE_OVERWRITE) {
            $action = 'overwriting';
        }

        $this->eventDispatcher->dispatch(new GenericEvent($this, [
            'startTime' => time()
        ]), 'index.start');

        $duplicatedDocuments = 0;

        foreach ($this->collectors as $collector) {
            $collector->setOutput($this->output);
            $collector->loadIndexableDataSources();

            foreach ($collector->getDataFromSources() as $dataFromSource) {
                if (empty($dataFromSource['data'])) {
                    $this->output->writeln('<comment>No data for source (' . $dataFromSource['identifier'] . ') found!</comment>', OutputInterface::VERBOSITY_DEBUG);
                    $this->output->writeln('', OutputInterface::VERBOSITY_DEBUG);
                    continue;
                }

                $documents = $collector->retrieveDocumentsFromData($dataFromSource['identifier'], $dataFromSource['data']);

                if (empty($documents)) {
                    $this->output->writeln('<comment>No documents for collector (' . get_class($collector) . ') could be mapped!</comment>', OutputInterface::VERBOSITY_DEBUG);
                    continue;
                }

                $this->eventDispatcher->dispatch(new GenericEvent($this, [
                    'action' => $action,
                    'count'  => count($documents)
                ]), 'index.data.document.insert.progress.start');

                if ($this->mode === self::MODE_OVERWRITE) {
                    $this->engine->begin();
                    foreach ($documents as $document) {
                        // When overwriting documents, delete first possible old entries in the namespace e.g. global category with id 1
                        $keys = explode('.', $document->getKey());
                        array_pop($keys);
                        if (!$dataFromSource['dataSourceConfig']->hasCategoryIds()) {
                            array_pop($keys);
                        }
                        $wildcard = implode('.', $keys);
                        $this->engine->deleteByWildcard($wildcard . '%');
                    }
                    $this->engine->commit();
                }

                if (!$this->dryRun) {
                    $this->engine->begin();
                    foreach ($documents as $document) {
                        $document->setValue(html_entity_decode(filter_var($document->getValue(), FILTER_SANITIZE_STRING)));

                        if ($this->mode === self::MODE_CREATION) {
                            try {
                                $this->engine->insertDocument($document);
                            } catch (DocumentExists $exception) {
                                $duplicatedDocuments++;
                            }
                        }

                        if ($this->mode === self::MODE_UPDATE) {
                            $this->engine->updateDocument($document);
                        }

                        if ($this->mode === self::MODE_DELETE) {
                            $this->engine->deleteDocument($document);
                        }

                        if ($this->mode === self::MODE_OVERWRITE) {
                            try {
                                $this->engine->insertDocument($document);
                            } catch (DocumentExists $exception) {
                                $this->engine->updateDocument($document);
                                $duplicatedDocuments++;
                            }
                        }

                        $this->eventDispatcher->dispatch(new GenericEvent($this), 'index.data.document.insert.progress.advance');
                    }
                    $this->engine->commit();
                }

                $this->eventDispatcher->dispatch(new GenericEvent($this, [
                    'action' => $action
                ]), 'index.data.document.insert.progress.finish');

                unset($documents);
            }
        }

        $this->eventDispatcher->dispatch(new GenericEvent($this, [
            'action'  => $action,
            'endTime' => time()
        ]), 'index.finish');

        if ($this->mode === self::MODE_CREATION) {
            $this->output->writeln($duplicatedDocuments . ' documents of inserted documents got skipped because they were duplicated!');
        }

        if ($this->mode === self::MODE_OVERWRITE) {
            $this->output->writeln($duplicatedDocuments . ' documents of inserted documents got updated instead, because they already existed!');
        }
    }

    /**
     * return boolean
     */
    public function clearIndex()
    {
        return $this->engine->clearIndex();
    }
}
