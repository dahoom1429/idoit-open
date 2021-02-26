<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter;

use idoit\Component\Property\Property;

/**
 * Class ValueFormatter
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter
 */
class ValueFormatter
{

    /**
     * @var string
     */
    protected $propertyKey;

    /**
     * @var Property
     */
    protected $property;

    /**
     * @var string
     */
    protected $referencedPropertyKey;

    /**
     * @var Property
     */
    protected $referencedProperty;

    /**
     * @var Value
     */
    protected $referencedPropertyValue;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var int
     */
    protected $objectId;

    /**
     * @var
     */
    protected $entryId;

    /**
     * @var bool
     */
    protected $changeAllRows = false;

    /**
     * @var array
     */
    protected $dataset = [];

    /**
     * @var array
     */
    protected $rawDataset = [];

    /**
     * @var bool
     */
    protected $disabled = false;

    /**
     * @var bool
     */
    protected $deactivated = false;

    /**
     * @param bool $deactivated
     *
     * @return ValueFormatter
     */
    public function setDeactivated($deactivated)
    {
        $this->deactivated = $deactivated;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeactivated()
    {
        return $this->deactivated;
    }

    /**
     * @param bool $val
     *
     * @return ValueFormatter
     */
    public function setDisabled($val)
    {
        $this->disabled = $val;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * @return array
     */
    public function getRawDataset()
    {
        return $this->rawDataset;
    }

    /**
     * @param array $rawDataset
     *
     * @return ValueFormatter
     */
    public function setRawDataset($rawDataset)
    {
        $this->rawDataset = $rawDataset;

        return $this;
    }

    /**
     * @return array
     */
    public function getDataset()
    {
        return $this->dataset;
    }

    /**
     * @param array $dataset
     *
     * @return ValueFormatter
     */
    public function setDataset($dataset)
    {
        $this->dataset = $dataset;

        return $this;
    }

    /**
     * @return $this
     */
    public function activateChangeAllRows()
    {
        $this->changeAllRows = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isChangeAllRowsActive()
    {
        return $this->changeAllRows;
    }

    /**
     * @return string
     */
    public function getPropertyKey()
    {
        return $this->propertyKey;
    }

    /**
     * @param string $propertyKey
     *
     * @return ValueFormatter
     */
    public function setPropertyKey($propertyKey)
    {
        $this->propertyKey = $propertyKey;

        return $this;
    }

    /**
     * @return Property
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param Property $property
     *
     * @return ValueFormatter
     */
    public function setProperty($property)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * @return string
     */
    public function getReferencedPropertyKey()
    {
        return $this->referencedPropertyKey;
    }

    /**
     * @param string $referencedPropertyKey
     *
     * @return ValueFormatter
     */
    public function setReferencedPropertyKey($referencedPropertyKey)
    {
        $this->referencedPropertyKey = $referencedPropertyKey;

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetReferencedPropertyKey()
    {
        unset($this->referencedPropertyKey);

        return $this;
    }

    /**
     * @return Property
     */
    public function getReferencedProperty()
    {
        return $this->referencedProperty;
    }

    /**
     * @param Property $referencedProperty
     *
     * @return ValueFormatter
     */
    public function setReferencedProperty($referencedProperty)
    {
        $this->referencedProperty = $referencedProperty;

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetReferencedProperty()
    {
        unset($this->referencedProperty);

        return $this;
    }

    /**
     * @return Value
     */
    public function getReferencedPropertyValue()
    {
        return $this->referencedPropertyValue;
    }

    /**
     * @param Value $referencedPropertyValue
     */
    public function setReferencedPropertyValue($referencedPropertyValue)
    {
        $this->referencedPropertyValue = $referencedPropertyValue;
    }

    /**
     * @return $this
     */
    public function unsetReferencedPropertyValue()
    {
        unset($this->referencedPropertyValue);

        return $this;
    }

    /**
     * @return Value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param Value $value
     *
     * @return ValueFormatter
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param int $objectId
     *
     * @return ValueFormatter
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntryId()
    {
        return $this->entryId;
    }

    /**
     * @param mixed $entryId
     *
     * @return ValueFormatter
     */
    public function setEntryId($entryId)
    {
        $this->entryId = $entryId;

        return $this;
    }
}
