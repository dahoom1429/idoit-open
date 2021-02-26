<?php

namespace idoit\Module\Search\Query;

use idoit\Component\Provider\Factory;
use idoit\Module\Search\Index\Engine\Searchable;
use idoit\Module\Search\Query\Exceptions\NoQueryEngineException;
use idoit\Module\Search\Query\Protocol\QueryResult;

/**
 * i-doit
 *
 * Search index document
 *
 * @package     idoit\Module\Search\Index
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class QueryManager
{
    use Factory;

    /**
     * @var Searchable[]
     */
    private $queryEngines = [];

    /**
     * @var Condition[]
     */
    private $conditions = [];

    /**
     * @param Searchable $engine
     *
     * @return QueryManager
     */
    public function attachEngine(Searchable $engine)
    {
        if ($engine->isAvailable()) {
            $this->queryEngines[] = $engine;
        }

        return $this;
    }

    /**
     * @param        $keyword
     * @param string $condition
     * @param bool   $negation
     * @param int    $mode
     *
     * @return $this
     */
    public function addSearchKeyword($keyword, $condition = 'AND', $negation = false, $mode = Condition::MODE_DEFAULT)
    {
        $this->addStringCondition(new StringCondition($keyword, $condition, $negation, $mode));

        return $this;
    }

    /**
     * @param StringCondition $condition
     *
     * @return $this
     */
    public function addStringCondition(StringCondition $condition)
    {
        $this->conditions[] = $condition;

        return $this;
    }

    /**
     * @param Condition $condition
     *
     * @return $this
     */
    public function addCondition(Condition $condition)
    {
        $this->conditions[] = $condition;

        return $this;
    }

    /**
     * @return QueryResult
     */
    public function search()
    {
        $result = null;

        if (!count($this->queryEngines)) {
            throw new NoQueryEngineException('There is no query engine available.');
        }

        foreach ($this->queryEngines as $engine) {

            if ($engine->isActive()) {
                $result = $engine->search($this->conditions);

                // Search in one engine only
                break;
            }

        }

        return $result;
    }

}
