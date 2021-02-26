<?php

namespace idoit\Component\Property;

interface LegacyPropertyCreatorInterface
{
    /**
     * Returns an instance of the class which implements this interface, build by given $propertyArray
     *
     * @param array $propertyArray
     */
    public static function createInstanceFromArray(array $propertyArray = []);

    /**
     * Maps attributes of the specific property class
     *
     * @param array $propertyArray
     *
     * @return mixed
     */
    public function mapAttributes(array $propertyArray);
}
