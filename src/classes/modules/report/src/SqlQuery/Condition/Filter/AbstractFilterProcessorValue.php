<?php
namespace idoit\Module\Report\SqlQuery\Condition\Filter;

/**
 * Class AbstractFilterProcessorValue
 */
abstract class AbstractFilterProcessorValue
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $checkValue;

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
     * @return AbstractFilterProcessorValue
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getCheckValue()
    {
        return $this->checkValue;
    }

    /**
     * @param string $checkValue
     *
     * @return AbstractFilterProcessorValue
     */
    public function setCheckValue($checkValue)
    {
        $this->checkValue = $checkValue;

        return $this;
    }
}
