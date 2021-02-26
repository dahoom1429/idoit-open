<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Source;

use idoit\Component\Property\Configuration\PropertyData;
use idoit\Component\Property\Property;
use idoit\Exception\Exception;
use idoit\Module\Multiedit\Component\Multiedit\Exception\EmptyPropertiesSourceDataException;

/**
 * Class PropertiesSource
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Source
 */
class PropertiesSource extends Source
{

    /**
     * @var Property[]
     */
    protected $data;

    /**
     * @var bool
     */
    protected $allProperties = false;

    /**
     * @return $this
     */
    public function activateAllProperties()
    {
        $this->allProperties = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function deactivateAllProperties()
    {
        $this->allProperties = false;

        return $this;
    }

    /**
     * @return $this|mixed
     * @throws EmptyPropertiesSourceDataException
     */
    public function formatData()
    {
        if (empty($this->getData())) {
            throw new EmptyPropertiesSourceDataException('Properties source is empty');
        }

        $propertyArr = [];
        foreach ($this->getData() as $categoryTitle => $properties) {
            $propertyArr[$categoryTitle] = [];
            foreach ($properties as $propertyKey => $property) {
                if ($this->allProperties ||
                    ($property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__MULTIEDIT] && !$property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VIRTUAL] &&
                        $this->allProperties === false)) {
                    try {
                        $propertyObj = ($property instanceof Property) ? $property : Property::createInstanceFromArray($property);
                        $propertyArr[$categoryTitle][$propertyKey] = $propertyObj;
                        $this->incrementCount();
                    } catch (\Exception $e) {
                        // Skip property because for example the custom categories have types like hr, html, script which are not supported
                    }
                }
            }
        }
        $this->setData($propertyArr);

        return $this;
    }
}
