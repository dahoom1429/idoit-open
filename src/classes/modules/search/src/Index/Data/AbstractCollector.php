<?php

namespace idoit\Module\Search\Index\Data;

use idoit\Module\Search\Index\Data\Source\Config;
use idoit\Module\Search\Index\Data\Source\Indexable;
use idoit\Module\Search\Index\Document;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * i-doit
 *
 * AbstractCollector
 *
 * @package     i-doit
 * @subpackage  Search
 * @author      Kevin Mauel <kmauel@i-doit.com>
 * @version     1.11
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class AbstractCollector
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Array of indexable data sources:
     * Keys:
     *  - instance
     *  - config
     *
     * @var array
     */
    protected $indexableDataSources = [];

    /**
     * @var string[]
     */
    protected $whitelistedSources = [];

    /**
     * @param OutputInterface $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * @return string[]
     */
    public function getWhitelistedSources()
    {
        return $this->whitelistedSources;
    }

    /**
     * @param string[] $whitelistedSources
     */
    public function setWhitelistedSources($whitelistedSources)
    {
        $this->whitelistedSources = $whitelistedSources;
    }

    /**
     * Array of indexable dataSources, (instance, config)
     *
     * @return array
     */
    abstract protected function getIndexableDataSources();

    /**
     * AbstractCollector constructor.
     */
    public function __construct()
    {
        $this->output = new NullOutput();
    }

    public function loadIndexableDataSources()
    {
        $this->indexableDataSources = $this->getIndexableDataSources();
    }

    /**
     * Retrieve data from sources
     * Will yield following keys:
     *  - identifier
     *  - data
     *  - dataSourceConfig
     */
    public function getDataFromSources()
    {
        foreach ($this->indexableDataSources as $identifier => $dataSource) {

            /**
             * @var $dataSourceInstance Indexable
             */
            $dataSourceInstance = $dataSource['instance'];

            if (!empty($this->whitelistedSources) && !in_array($identifier, $this->whitelistedSources, false)) {
                $this->output->writeln('<comment>Source ' . $identifier . ' was skipped because it\'s not whitelisted!</comment>', OutputInterface::VERBOSITY_VERBOSE);
                continue;
            }

            $this->output->writeln(($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE ? date('H:i:s') . '  ' : '') . 'Retrieving data for ' .
                $identifier, OutputInterface::VERBOSITY_VERBOSE);

            yield [
                'identifier'       => $identifier,
                'data'             => $dataSourceInstance->retrieveData($dataSource['config']),
                'dataSourceConfig' => $dataSource['config']
            ];
        }
    }

    /**
     * Retrieve documents collected from Indexable->retrieveData and map them to index documents
     *
     * @param string $identifier
     * @param array  $dataSet
     *
     * @return Document[]
     */
    public function retrieveDocumentsFromData($identifier, array $dataSet)
    {
        /**
         * @var $dataSourceInstance Indexable
         */
        $dataSourceInstance = $this->indexableDataSources[$identifier]['instance'];

        return $dataSourceInstance->mapDataToDocuments($dataSet);
    }
}
