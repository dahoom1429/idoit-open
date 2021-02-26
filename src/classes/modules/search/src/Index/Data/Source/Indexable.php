<?php

namespace idoit\Module\Search\Index\Data\Source;

use idoit\Module\Search\Index\Document;

/**
 * i-doit
 *
 * Indexable
 *
 * @package     i-doit
 * @subpackage  Search
 * @author      Kevin Mauel <kmauel@i-doit.com>
 * @version     1.11
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface Indexable
{

    /**
     * Get identifier for indexable data source
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Retrieve data for index creation
     *
     * @param Config $config
     *
     * @return array
     */
    public function retrieveData(Config $config);

    /**
     * Map data from retrieveData to Documents
     *
     * @param array $data
     *
     * @return Document[]
     */
    public function mapDataToDocuments(array $data);
}
