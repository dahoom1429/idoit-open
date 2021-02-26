<?php

namespace idoit\Module\Search\Index\Engine;

use idoit\Module\Search\Query\Condition;
use idoit\Module\Search\Query\Protocol\QueryResult;

/**
 * i-doit
 *
 * Searchable
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @author      Kevin Mauel <kmauel@i-doit.com>
 * @version     1.11
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface Searchable
{
    /**
     * Process search via conditions
     *
     * @param Condition[] $conditions
     *
     * @return QueryResult
     */
    public function search(array $conditions);

    /**
     * Process search via string keyword
     *
     * @param string $keyword
     *
     * @return QueryResult
     */
    public function searchString($keyword);

    /**
     * Is engine activated
     *
     * @return bool
     */
    public function isActive();

    /**
     * Is engine running and available?
     *
     * @return bool
     */
    public function isAvailable();

    /**
     * Does engine support scoring?
     *
     * @return bool
     */
    public function isScoringAvailable();
}
