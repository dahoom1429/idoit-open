<?php

namespace idoit\Module\Search\Query\Engine\Mysql;

use idoit\Module\Search\Index\Engine\Searchable;
use idoit\Module\Search\Query\Condition;
use idoit\Module\Search\Query\Protocol\QueryResult;
use idoit\Module\Search\Query\StringCondition;
use isys_application as Application;

/**
 * i-doit
 *
 * MySQL Query Engine
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class SearchEngine implements Searchable
{
    /**
     * Process search via conditions
     *
     * @param Condition[] $conditions
     *
     * @return QueryResult
     */
    public function search(array $conditions)
    {
        $query = new Query(Application::instance()->database);

        return $query->search($conditions);
    }

    /**
     * Process search via string keyword
     *
     * @param string $keyword
     *
     * @return QueryResult
     */
    public function searchString($keyword)
    {
        $query = new Query(Application::instance()->database);

        return $query->search([
            new StringCondition($keyword)
        ]);
    }

    /**
     * Is engine active and running?
     *
     * @return bool
     * @todo can be removed ?
     */
    public function isActive()
    {
        return true;
    }

    /**
     * Is engine running and available?
     *
     * @return bool
     */
    public function isAvailable()
    {
        return \isys_application::instance()->database->is_connected();
    }

    /**
     * Does engine support scoring?
     *
     * @return bool
     */
    public function isScoringAvailable()
    {
        return false;
    }
}
