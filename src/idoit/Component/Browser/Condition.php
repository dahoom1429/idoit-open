<?php

namespace idoit\Component\Browser;

use isys_cmdb_dao;
use isys_component_database;
use isys_format_json;

abstract class Condition implements ConditionInterface
{
    /**
     * @var isys_component_database
     */
    protected $db;

    /**
     * @var isys_cmdb_dao
     */
    protected $dao;

    /**
     * @var mixed
     */
    protected $parameter;

    /**
     * @var FilterInterface[]
     */
    protected $filter = [];

    /**
     * @var integer
     */
    protected $contextObjectId;

    /**
     * @var bool
     */
    protected $displayObjectCount = true;

    /**
     * @param mixed $parameter
     *
     * @return $this
     */
    public function setParameter($parameter)
    {
        $this->parameter = $parameter;

        return $this;
    }

    /**
     * @param bool $displayCount
     *
     * @return $this
     */
    public function enableObjectCount($displayCount)
    {
        $this->displayObjectCount = (bool)$displayCount;

        return $this;
    }

    /**
     * @param FilterInterface $filter
     *
     * @return $this
     */
    public function registerFilter(FilterInterface $filter)
    {
        $this->filter[] = $filter;

        return $this;
    }

    /**
     * Method for quickly registering filters by a simple array.
     *
     * @param array $filterData
     *
     * @return Condition
     * @throws \Exception
     */
    public function registerFilterByArray(array $filterData)
    {
        foreach ($filterData as $filterName => $parameter) {
            $filterClass = 'idoit\\Component\\Browser\\Filter\\' . $filterName;

            if (empty($parameter) || !class_exists($filterClass)) {
                continue;
            }

            if (isys_format_json::is_json_array($parameter)) {
                $parameter = isys_format_json::decode($parameter);
            } elseif (is_scalar($parameter)) {
                if (strpos($parameter, ';') !== false) {
                    $parameter = explode(';', $parameter);
                } elseif (strpos($parameter, ',') !== false) {
                    $parameter = explode(',', $parameter);
                } else {
                    $parameter = [$parameter];
                }
            }

            /** @var FilterInterface $filterClass */
            $this->registerFilter((new $filterClass($this->db))->setParameter($parameter));
        }

        return $this;
    }

    /**
     * @param integer $contextObjectId
     *
     * @return $this
     */
    public function setContextObjectId($contextObjectId)
    {
        $this->contextObjectId = (int) $contextObjectId;

        return $this;
    }

    /**
     * @return string
     */
    protected function getFilterQueryConditions()
    {
        $conditions = [];

        foreach ($this->filter as $filter) {
            $conditions[] = $filter->getQueryCondition();
        }

        return implode(' ', $conditions);
    }

    /**
     * @inheritdoc
     */
    public function processVisitors(array $objects)
    {
        foreach ($this->filter as $filter) {
            if ($filter->hasVisitor()) {
                $objects = $filter->visit($objects);
            }
        }

        return $objects;
    }

    /**
     * @return mixed
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * Method that defines if the object order should be retained (necessary for "date" condition).
     *
     * @return boolean
     */
    public function retainObjectOrder()
    {
        return false;
    }

    /**
     * Condition constructor.
     *
     * @param isys_component_database $db
     */
    public function __construct(isys_component_database $db)
    {
        $this->db = $db;
        $this->dao = isys_cmdb_dao::instance($this->db);
    }
}
