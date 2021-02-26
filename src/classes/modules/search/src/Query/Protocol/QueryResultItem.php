<?php

namespace idoit\Module\Search\Query\Protocol;

use idoit\Module\Search\Index\DocumentMetadata;

/**
 * i-doit
 *
 * Query result protocol
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface QueryResultItem
{
    /**
     * Get document Id
     *
     * @return int
     */
    public function getDocumentId();

    /**
     * Get key
     *
     * @return string
     */
    public function getKey();

    /**
     * Return URL to result item
     *
     * @return string
     */
    public function getLink();

    /**
     * @return string
     */
    public function getStatus();

    /**
     * Get matching score
     *
     * @return float
     */
    public function getScore();

    /**
     * Get type (module identifier)
     *
     * @return string
     */
    public function getType();

    /**
     * Get fulltext value
     *
     * @return string
     */
    public function getValue();

    /**
     * QueryResultItem constructor.
     *
     * @param DocumentMetadata $documentMetadata
     * @param int              $documentId
     * @param string           $key
     * @param string           $value
     * @param double           $score
     * @param array            $conditions
     */
    public function __construct(DocumentMetadata $documentMetadata, $documentId, $key, $value, $score, array $conditions);

}
