<?php

namespace idoit\Component\Property\Configuration;

use idoit\Component\Property\Exception\UnknownTypeException;
use idoit\Component\Property\LegacyPropertyCreatorInterface;
use idoit\Component\Property\Property;
use idoit\Component\Property\PropertyConfiguration;

class PropertyInfo extends PropertyConfiguration implements LegacyPropertyCreatorInterface
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @todo maybe PropertyTypeInterface?
     * @var string
     */
    protected $type;

    /**
     * @var bool
     */
    protected $primaryField;

    /**
     * @var bool
     */
    protected $backwardCompatible;

    /**
     * Returns an instance of the class which implements this interface, build by given $propertyArray
     *
     * @param array $propertyArray
     *
     * @return PropertyInfo
     *
     * @throws UnknownTypeException
     */
    public static function createInstanceFromArray(array $propertyArray = [])
    {
        if (!defined('C__PROPERTY__INFO__TYPE__' . strtoupper($propertyArray[Property::C__PROPERTY__INFO__TYPE]))) {
            throw new UnknownTypeException('Unkown type: ' . 'C__PROPERTY__INFO__TYPE__' . strtoupper($propertyArray[Property::C__PROPERTY__INFO__TYPE]));
        }

        $propertyInfo = new static();

        return $propertyInfo->mapAttributes($propertyArray);
    }

    /**
     * Sets all member variables
     *
     * @param array $propertyArray
     *
     * @return PropertyInfo
     */
    public function mapAttributes(array $propertyArray)
    {
        $this->title = $propertyArray[Property::C__PROPERTY__INFO__TITLE];
        $this->description = $propertyArray[Property::C__PROPERTY__INFO__DESCRIPTION];
        $this->type = $propertyArray[Property::C__PROPERTY__INFO__TYPE];
        $this->primaryField = (bool)$propertyArray[Property::C__PROPERTY__INFO__PRIMARY];
        $this->backwardCompatible = (bool)$propertyArray[Property::C__PROPERTY__INFO__BACKWARD];

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return PropertyInfo
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return PropertyInfo
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return PropertyInfo
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPrimaryField()
    {
        return $this->primaryField;
    }

    /**
     * @param bool $primaryField
     *
     * @return PropertyInfo
     */
    public function setPrimaryField($primaryField)
    {
        $this->primaryField = $primaryField;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBackwardCompatible()
    {
        return $this->backwardCompatible;
    }

    /**
     * @param bool $backwardCompatible
     *
     * @return PropertyInfo
     */
    public function setBackwardCompatible($backwardCompatible)
    {
        $this->backwardCompatible = $backwardCompatible;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        if ($offset === Property::C__PROPERTY__INFO__TITLE) {
            return $this->title !== null;
        }

        if ($offset === Property::C__PROPERTY__INFO__DESCRIPTION) {
            return $this->description !== null;
        }

        if ($offset === Property::C__PROPERTY__INFO__TYPE) {
            return $this->type !== null;
        }

        if ($offset === Property::C__PROPERTY__INFO__PRIMARY) {
            return $this->primaryField !== null;
        }

        if ($offset === Property::C__PROPERTY__INFO__BACKWARD) {
            return $this->backwardCompatible !== null;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        if ($offset === Property::C__PROPERTY__INFO__TITLE) {
            return $this->title;
        }

        if ($offset === Property::C__PROPERTY__INFO__DESCRIPTION) {
            return $this->description;
        }

        if ($offset === Property::C__PROPERTY__INFO__TYPE) {
            return $this->type;
        }

        if ($offset === Property::C__PROPERTY__INFO__PRIMARY) {
            return $this->primaryField;
        }

        if ($offset === Property::C__PROPERTY__INFO__BACKWARD) {
            return $this->backwardCompatible;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === Property::C__PROPERTY__INFO__TITLE) {
            $this->title = $value;
        }

        if ($offset === Property::C__PROPERTY__INFO__DESCRIPTION) {
            $this->description = $value;
        }

        if ($offset === Property::C__PROPERTY__INFO__TYPE) {
            $this->type = $value;
        }

        if ($offset === Property::C__PROPERTY__INFO__PRIMARY) {
            $this->primaryField = $value;
        }

        if ($offset === Property::C__PROPERTY__INFO__BACKWARD) {
            $this->backwardCompatible = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        if ($offset === Property::C__PROPERTY__INFO__TITLE) {
            unset($this->title);
        }

        if ($offset === Property::C__PROPERTY__INFO__DESCRIPTION) {
            unset($this->description);
        }

        if ($offset === Property::C__PROPERTY__INFO__TYPE) {
            unset($this->type);
        }

        if ($offset === Property::C__PROPERTY__INFO__PRIMARY) {
            unset($this->primaryField);
        }

        if ($offset === Property::C__PROPERTY__INFO__BACKWARD) {
            unset($this->backwardCompatible);
        }
    }
}
