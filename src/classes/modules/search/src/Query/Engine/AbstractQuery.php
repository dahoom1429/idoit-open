<?php

namespace idoit\Module\Search\Query\Engine;

use idoit\Module\Search\Index\DocumentMetadata;
use idoit\Module\Search\Query\Condition;
use idoit\Module\Search\Query\QueryResultItem;

/**
 * i-doit
 *
 * Abstract search query
 *
 * @package     idoit\Module\Search\Index
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class AbstractQuery
{

    /**
     * Get QueryResultItem instance
     *
     * @param string           $type
     * @param DocumentMetadata $metadata
     * @param int              $id
     * @param string           $key
     * @param string           $value
     * @param double           $score
     * @param Condition[]      $conditions
     *
     * @return QueryResultItem
     */
    protected function getQueryItemInstance($type, DocumentMetadata $metadata, $id, $key, $value, $score, array $conditions)
    {
        $className = 'idoit\\Module\\' . ucfirst($type) . '\\Search\\Query\\QueryResultItem';

        if (class_exists($className)) {
            return new $className($metadata, $id, $key, $value, $score, $conditions);
        }

        return new QueryResultItem($metadata, $id, $key, $value, $score, $conditions);
    }

}
