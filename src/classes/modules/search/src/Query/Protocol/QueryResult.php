<?php

namespace idoit\Module\Search\Query\Protocol;

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
interface QueryResult
{

    /**
     * Add a query result item to this result
     *
     * @param QueryResultItem $item
     *
     * @return mixed
     */
    public function addItem(QueryResultItem $item);

    /**
     * Get result items
     *
     * @return QueryResultItem[]
     */
    public function getResult();

}