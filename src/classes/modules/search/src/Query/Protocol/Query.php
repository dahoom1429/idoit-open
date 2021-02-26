<?php

namespace idoit\Module\Search\Query\Protocol;

use idoit\Module\Search\Query\Condition;

/**
 * i-doit
 *
 * Search index query protocol
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface Query
{

    /**
     * @param Condition[] $conditions
     *
     * @return QueryResult
     */
    public function search(array $conditions);

}