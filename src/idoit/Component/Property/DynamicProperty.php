<?php

namespace idoit\Component\Property;

use idoit\Component\Property\Configuration\PropertyCheck;
use idoit\Component\Property\Configuration\PropertyData;
use idoit\Component\Property\Configuration\PropertyDependency;
use idoit\Component\Property\Configuration\PropertyFormat;
use idoit\Component\Property\Configuration\PropertyInfo;
use idoit\Component\Property\Configuration\PropertyProvides;
use idoit\Component\Property\Configuration\PropertyUi;
use idoit\Component\Property\Exception\UnknownTypeException;
use idoit\Component\Property\Exception\UnsupportedConfigurationTypeException;

class DynamicProperty extends Property
{
    /**
     * Returns an instance of the class which implements this interface, build by given $propertyArray
     *
     * @param array $propertyArray
     *
     * @return Property
     */
    public static function createInstanceFromArray(array $propertyArray = [])
    {
        return (new static())->mapAttributes($propertyArray);
    }

    /**
     * Maps the property
     *
     * @param array $propertyArray
     *
     * @return Property
     * @throws Exception\UnknownTypeException
     * @throws Exception\UnsupportedConfigurationTypeException
     */
    public function mapAttributes(array $propertyArray)
    {
        $this->info = (new PropertyInfo())->mapAttributes($propertyArray[self::C__PROPERTY__INFO] ?: []);
        $this->data = (new PropertyData())->mapAttributes($propertyArray[self::C__PROPERTY__DATA] ?: []);
        $this->check = (new PropertyCheck())->mapAttributes($propertyArray[self::C__PROPERTY__CHECK] ?: []);
        $this->format = (new PropertyFormat())->mapAttributes($propertyArray[self::C__PROPERTY__FORMAT] ?: []);
        $this->ui = (new PropertyUi())->mapAttributes($propertyArray[self::C__PROPERTY__UI] ?: []);
        $this->provides = (new PropertyProvides())->mapAttributes($propertyArray[self::C__PROPERTY__PROVIDES] ?: []);
        $this->dependency = (new PropertyDependency())->mapAttributes($propertyArray[self::C__PROPERTY__DEPENDENCY] ?: []);
        return $this;
    }
}
