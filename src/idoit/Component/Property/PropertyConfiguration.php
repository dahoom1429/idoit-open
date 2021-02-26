<?php

namespace idoit\Component\Property;

use ReflectionClass;

/**
 * Class PropertyConfiguration
 *
 * @package idoit\Component\Property
 */
abstract class PropertyConfiguration implements \ArrayAccess
{
    /**
     * @return array
     * @throws \ReflectionException
     */
    public function toArray(): array
    {
        $return = [];
        $properties = (new ReflectionClass($this))->getProperties();

        foreach ($properties as $property) {
            $propertyName = $property->getName();

            if (!isset($this->$propertyName)) {
                continue;
            }

            if (\is_array($this->$propertyName) && empty($this->$propertyName)) {
                continue;
            }

            if (\is_object($this->$propertyName) && method_exists($this->$propertyName, 'toArray')) {
                $return[$propertyName] = $this->$propertyName->toArray();
                continue;
            }

            $return[$propertyName] = $this->$propertyName;
        }

        return $return;
    }
}
