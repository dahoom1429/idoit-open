<?php
namespace idoit\Module\Multiedit\Component\Filter;

/**
 * Class DataSourceFilter
 *
 * @package idoit\Module\Multiedit\Component\Filter
 */
class DataSourceFilter
{

    /**
     * @var
     */
    protected $property;

    /**
     * @var
     */
    protected $value;

    /**
     * @return mixed
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param mixed $property
     *
     * @return DataSourceFilter
     */
    public function setProperty($property)
    {
        $this->property = $property;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return DataSourceFilter
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
}
