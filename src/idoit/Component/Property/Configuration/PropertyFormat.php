<?php

namespace idoit\Component\Property\Configuration;

use idoit\Component\Property\LegacyPropertyCreatorInterface;
use idoit\Component\Property\Property;
use idoit\Component\Property\PropertyConfiguration;

class PropertyFormat extends PropertyConfiguration implements LegacyPropertyCreatorInterface
{
    /**
     * First Index: Instance or class name
     * Second Index: For instance public function name, for class name static function name
     *
     * [
     *     $this,
     *     'getTotalCapacitySdCard'
     * ]
     *
     * @var array
     */
    protected $callback = [];

    /**
     * @var string
     */
    protected $requires;

    /**
     * @var string
     */
    protected $unit;

    /**
     * Returns an instance of the class which implements this interface, build by given $propertyArray
     *
     * @param array $propertyArray
     *
     * @return PropertyFormat
     */
    public static function createInstanceFromArray(array $propertyArray = [])
    {
        $propertyFormat = new static();

        return $propertyFormat->mapAttributes($propertyArray);
    }

    /**
     * Sets all member variables
     *
     * @param array $propertyArray
     *
     * @return PropertyFormat
     */
    public function mapAttributes(array $propertyArray)
    {
        $this->callback = $propertyArray[Property::C__PROPERTY__FORMAT__CALLBACK];
        $this->requires = $propertyArray[Property::C__PROPERTY__FORMAT__REQUIRES];
        $this->unit = $propertyArray[Property::C__PROPERTY__FORMAT__UNIT];

        return $this;
    }

    /**
     * @return array
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @param array $callback
     *
     * @return PropertyFormat
     */
    public function setCallback(array $callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * @return string
     */
    public function getRequires()
    {
        return $this->requires;
    }

    /**
     * @param string $requires
     *
     * @return PropertyFormat
     */
    public function setRequires($requires)
    {
        $this->requires = $requires;

        return $this;
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param string $unit
     *
     * @return PropertyFormat
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        if ($offset === Property::C__PROPERTY__FORMAT__CALLBACK) {
            return $this->callback !== null;
        }

        if ($offset === Property::C__PROPERTY__FORMAT__REQUIRES) {
            return $this->requires !== null;
        }

        if ($offset === Property::C__PROPERTY__FORMAT__UNIT) {
            return $this->unit !== null;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        if ($offset === Property::C__PROPERTY__FORMAT__CALLBACK) {
            return $this->callback;
        }

        if ($offset === Property::C__PROPERTY__FORMAT__REQUIRES) {
            return $this->requires;
        }

        if ($offset === Property::C__PROPERTY__FORMAT__UNIT) {
            return $this->unit;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === Property::C__PROPERTY__FORMAT__CALLBACK) {
            $this->callback = $value;
        }

        if ($offset === Property::C__PROPERTY__FORMAT__REQUIRES) {
            $this->requires = $value;
        }

        if ($offset === Property::C__PROPERTY__FORMAT__UNIT) {
            $this->unit = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        if ($offset === Property::C__PROPERTY__FORMAT__CALLBACK) {
            unset($this->callback);
        }

        if ($offset === Property::C__PROPERTY__FORMAT__REQUIRES) {
            unset($this->requires);
        }

        if ($offset === Property::C__PROPERTY__FORMAT__UNIT) {
            unset($this->unit);
        }
    }
}
