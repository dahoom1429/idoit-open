<?php

namespace idoit\Module\Search\Index\Engine;

use idoit\Module\Search\Index\Document;
use idoit\Module\Search\Index\Exception\DocumentExists;

/**
 * i-doit
 *
 * SearchEngine
 *
 * @package     i-doit
 * @subpackage  Search
 * @author      Kevin Mauel <kmauel@i-doit.com>
 * @version     1.11
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface SearchEngine
{
    const VERSION = 1;

    /**
     * @param Document $document
     *
     * @throws DocumentExists
     */
    public function insertDocument(Document $document);

    /**
     * @param Document $document
     */
    public function updateDocument(Document $document);

    /**
     * @param Document $document
     */
    public function deleteDocument(Document $document);

    /**
     * @param string
     *
     * @return void
     */
    public function deleteByWildcard($wildcard);

    /**
     * Retrieves unique document references
     *
     * @return int[]
     */
    public function retrieveUniqueDocumentReferences();

    /**
     * Truncates index table
     */
    public function clearIndex();

    /**
     * @return void
     */
    public function begin();

    /**
     * @return void
     */
    public function commit();
}
