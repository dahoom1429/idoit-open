<?php

namespace idoit\Component\Property\Configuration;

use idoit\Component\Property\LegacyPropertyCreatorInterface;
use idoit\Component\Property\Property;
use idoit\Component\Property\PropertyConfiguration;

class PropertyCheck extends PropertyConfiguration implements LegacyPropertyCreatorInterface
{
    /**
     * @var bool
     */
    protected $mandatory;

    /**
     * Either FILTER_VALIDATE_* or FILTER_CALLBACK
     *
     * @var string
     */
    protected $validationType;

    /**
     * Either options for filter_var or a callback reference
     *
     * 'options' => [
     *     'isys_helper', Class
     *     'filter_text' Static Function
     * ]
     *
     * @var array
     */
    protected $validationOptions = [];

    /**
     * Either FILTER_SANITIZE_* or FILTER_CALLBACK
     *
     * @var string
     */
    protected $sanitizationType;

    /**
     * Either options for filter_var or a callback reference
     *
     * 'options' => [
     *     'isys_helper', Class
     *     'filter_text' Static Function
     * ]
     *
     * @var array
     */
    protected $sanitizationOptions = [];

    /**
     * @var mixed
     */
    protected $uniqueObject;

    /**
     * @var mixed
     */
    protected $uniqueObjectType;

    /**
     * @var mixed
     */
    protected $uniqueGlobal;

    /**
     * Returns an instance of the class which implements this interface, build by given $propertyArray
     *
     * @param array $propertyArray
     *
     * @return PropertyCheck
     */
    public static function createInstanceFromArray(array $propertyArray = [])
    {
        $propertyCheck = new static();

        return $propertyCheck->mapAttributes($propertyArray);
    }

    /**
     * Sets all member variables
     *
     * @param array $propertyArray
     *
     * @return PropertyCheck
     */
    public function mapAttributes(array $propertyArray)
    {
        $this->mandatory = (bool)$propertyArray[Property::C__PROPERTY__CHECK__MANDATORY];
        $this->validationType = $propertyArray[Property::C__PROPERTY__CHECK__VALIDATION][0] ?: null;
        $this->validationOptions = $propertyArray[Property::C__PROPERTY__CHECK__VALIDATION][1] ?: [];
        $this->sanitizationType = $propertyArray[Property::C__PROPERTY__CHECK__SANITIZATION][0] ?: null;
        $this->sanitizationOptions = $propertyArray[Property::C__PROPERTY__CHECK__SANITIZATION][1] ?: [];
        $this->uniqueObject = $propertyArray[Property::C__PROPERTY__CHECK__UNIQUE_OBJ];
        $this->uniqueObjectType = $propertyArray[Property::C__PROPERTY__CHECK__UNIQUE_OBJTYPE];
        $this->uniqueGlobal = $propertyArray[Property::C__PROPERTY__CHECK__UNIQUE_GLOBAL];

        return $this;
    }

    /**
     * @return bool
     */
    public function isMandatory()
    {
        return $this->mandatory;
    }

    /**
     * @param bool $mandatory
     *
     * @return PropertyCheck
     */
    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;

        return $this;
    }

    /**
     * @return string
     */
    public function getValidationType()
    {
        return $this->validationType;
    }

    /**
     * @param string $validationType
     *
     * @return PropertyCheck
     */
    public function setValidationType($validationType)
    {
        $this->validationType = $validationType;

        return $this;
    }

    /**
     * @return array
     */
    public function getValidationOptions()
    {
        return $this->validationOptions;
    }

    /**
     * @param array $validationOptions
     *
     * @return PropertyCheck
     */
    public function setValidationOptions(array $validationOptions)
    {
        $this->validationOptions = $validationOptions;

        return $this;
    }

    /**
     * @return string
     */
    public function getSanitizationType()
    {
        return $this->sanitizationType;
    }

    /**
     * @param string $sanitizationType
     *
     * @return PropertyCheck
     */
    public function setSanitizationType($sanitizationType)
    {
        $this->sanitizationType = $sanitizationType;

        return $this;
    }

    /**
     * @return array
     */
    public function getSanitizationOptions()
    {
        return $this->sanitizationOptions;
    }

    /**
     * @param array $sanitizationOptions
     *
     * @return PropertyCheck
     */
    public function setSanitizationOptions(array $sanitizationOptions)
    {
        $this->sanitizationOptions = $sanitizationOptions;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUniqueObject()
    {
        return $this->uniqueObject;
    }

    /**
     * @param mixed $uniqueObject
     *
     * @return PropertyCheck
     */
    public function setUniqueObject($uniqueObject)
    {
        $this->uniqueObject = $uniqueObject;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUniqueObjectType()
    {
        return $this->uniqueObjectType;
    }

    /**
     * @param mixed $uniqueObjectType
     *
     * @return PropertyCheck
     */
    public function setUniqueObjectType($uniqueObjectType)
    {
        $this->uniqueObjectType = $uniqueObjectType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUniqueGlobal()
    {
        return $this->uniqueGlobal;
    }

    /**
     * @param mixed $uniqueGlobal
     *
     * @return PropertyCheck
     */
    public function setUniqueGlobal($uniqueGlobal)
    {
        $this->uniqueGlobal = $uniqueGlobal;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        if ($offset === Property::C__PROPERTY__CHECK__MANDATORY) {
            return $this->mandatory !== null;
        }

        if ($offset === Property::C__PROPERTY__CHECK__VALIDATION) {
            return $this->validationType !== null && is_countable($this->validationOptions) && count($this->validationOptions) !== 0;
        }

        if ($offset === Property::C__PROPERTY__CHECK__SANITIZATION) {
            return $this->sanitizationType !== null && is_countable($this->sanitizationOptions) && count($this->sanitizationOptions) !== 0;
        }

        if ($offset === Property::C__PROPERTY__CHECK__UNIQUE_OBJ) {
            return $this->uniqueObject !== null;
        }

        if ($offset === Property::C__PROPERTY__CHECK__UNIQUE_OBJTYPE) {
            return $this->uniqueObjectType !== null;
        }

        if ($offset === Property::C__PROPERTY__CHECK__UNIQUE_GLOBAL) {
            return $this->uniqueGlobal !== null;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        if ($offset === Property::C__PROPERTY__CHECK__MANDATORY) {
            return $this->mandatory;
        }

        if ($offset === Property::C__PROPERTY__CHECK__VALIDATION) {
            return [
                $this->validationType,
                $this->validationOptions
            ];
        }

        if ($offset === Property::C__PROPERTY__CHECK__SANITIZATION) {
            return [
                $this->sanitizationType,
                $this->sanitizationOptions
            ];
        }

        if ($offset === Property::C__PROPERTY__CHECK__UNIQUE_OBJ) {
            return $this->uniqueObject;
        }

        if ($offset === Property::C__PROPERTY__CHECK__UNIQUE_OBJTYPE) {
            return $this->uniqueObjectType;
        }

        if ($offset === Property::C__PROPERTY__CHECK__UNIQUE_GLOBAL) {
            return $this->uniqueGlobal;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === Property::C__PROPERTY__CHECK__MANDATORY) {
            $this->mandatory = (bool)$value;
        }

        if ($offset === Property::C__PROPERTY__CHECK__VALIDATION) {
            $this->validationType = $value[0] ?: null;
            $this->validationOptions = $value[1] ?: [];
        }

        if ($offset === Property::C__PROPERTY__CHECK__SANITIZATION) {
            $this->sanitizationType = $value[0] ?: null;
            $this->sanitizationOptions = $value[1] ?: [];
        }

        if ($offset === Property::C__PROPERTY__CHECK__UNIQUE_OBJ) {
            $this->uniqueObject = $value;
        }

        if ($offset === Property::C__PROPERTY__CHECK__UNIQUE_OBJTYPE) {
            $this->uniqueObjectType = $value;
        }

        if ($offset === Property::C__PROPERTY__CHECK__UNIQUE_GLOBAL) {
            $this->uniqueGlobal = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        if ($offset === Property::C__PROPERTY__CHECK__MANDATORY) {
            unset($this->mandatory);
        }

        if ($offset === Property::C__PROPERTY__CHECK__VALIDATION) {
            unset($this->validationType, $this->validationOptions);
        }

        if ($offset === Property::C__PROPERTY__CHECK__SANITIZATION) {
            unset($this->sanitizationType, $this->sanitizationOptions);
        }

        if ($offset === Property::C__PROPERTY__CHECK__UNIQUE_OBJ) {
            unset($this->uniqueObject);
        }

        if ($offset === Property::C__PROPERTY__CHECK__UNIQUE_OBJTYPE) {
            unset($this->uniqueObjectType);
        }

        if ($offset === Property::C__PROPERTY__CHECK__UNIQUE_GLOBAL) {
            unset($this->uniqueGlobal);
        }
    }
}
