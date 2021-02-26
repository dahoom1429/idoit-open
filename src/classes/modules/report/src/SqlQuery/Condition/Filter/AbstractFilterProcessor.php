<?php
namespace idoit\Module\Report\SqlQuery\Condition\Filter;

use idoit\Component\Property\Property;
use isys_cmdb_dao_category;

abstract class AbstractFilterProcessor
{
    /**
     * @var isys_cmdb_dao_category
     */
    private $dao;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $value;

    /**
     * @var array
     */
    private $processedValueIdsPositive;

    /**
     * @var array
     */
    private $processedValueIdsNegative;

    /**
     * @var int
     */
    private $id;

    /**
     * @var array
     */
    private $processedIds = [];

    /**
     * @var FilterProcessorValueInterface
     */
    private $processorConditionValue;

    /**
     * @var string
     */
    private $field;

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $field
     *
     * @return AbstractFilterProcessor
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return FilterProcessorValueInterface
     */
    public function getProcessorConditionValue()
    {
        return $this->processorConditionValue;
    }

    /**
     * @param FilterProcessorValueInterface $processorConditionValue
     *
     * @return AbstractFilterProcessor
     */
    public function setProcessorConditionValue($processorConditionValue)
    {
        $this->processorConditionValue = $processorConditionValue;

        return $this;
    }

    /**
     * @param $id
     *
     * @return AbstractFilterProcessor
     */
    public function addProcessedId($id)
    {
        $this->processedIds[$id] = true;
        return $this;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function processedIdExists($id)
    {
        return isset($this->processedIds[$id]);
    }

    /**
     * @return int[]
     */
    public function getProcessedValueIdsPositive()
    {
        return $this->processedValueIdsPositive;
    }

    /**
     * @param array $processedValuePositive
     *
     * @return AbstractFilterProcessor
     */
    public function setProcessedValueIdsPositive($processedValuePositive)
    {
        $this->processedValueIdsPositive = $processedValuePositive;

        return $this;
    }

    /**
     * @param $value
     *
     * @return AbstractFilterProcessor
     */
    public function addProcessedValueIdsPositive($value)
    {
        $this->processedValueIdsPositive[] = $value;
        return $this;
    }

    /**
     * @return int[]
     */
    public function getProcessedValueIdsNegative()
    {
        return $this->processedValueIdsNegative;
    }

    /**
     * @param array $processedValueNegative
     *
     * @return AbstractFilterProcessor
     */
    public function setProcessedValueIdsNegative($processedValueNegative)
    {
        $this->processedValueIdsNegative = $processedValueNegative;

        return $this;
    }

    /**
     * @param $value
     *
     * @return AbstractFilterProcessor
     */
    public function addProcessedValueIdsNegative($value)
    {
        $this->processedValueIdsNegative[] = $value;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return AbstractFilterProcessor
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return AbstractFilterProcessor
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return isys_cmdb_dao_category
     */
    public function getDao()
    {
        return $this->dao;
    }

    /**
     *
     * @param isys_cmdb_dao_category $dao
     *
     * @return AbstractFilterProcessor
     */
    public function setDao($dao)
    {
        $this->dao = $dao;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     *
     * @return AbstractFilterProcessor
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }
}
