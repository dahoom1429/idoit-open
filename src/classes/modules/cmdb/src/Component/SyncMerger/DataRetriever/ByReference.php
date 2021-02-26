<?php
namespace idoit\Module\Cmdb\Component\SyncMerger\DataRetriever;

use idoit\Component\Property\Property;
use isys_request;

class ByReference implements DataRetrieverInterface
{
    /**
     * @param Property $property
     *
     * @return bool
     */
    public static function isApplicable(Property $property)
    {
        $references = $property->getData()
            ->getReferences();

        if (is_array($references) && isset($references[1])) {
            return true;
        }
        return false;
    }

    /**
     * @param Property     $property
     * @param array        $properties
     * @param array        $categoryData
     * @param array        $currentData
     * @param isys_request $request
     *
     * @return mixed|null
     */
    public function retrieveValue(Property $property, array $properties, array $categoryData, array $currentData, isys_request $request)
    {
        $references = $property->getData()
            ->getReferences();

        if (is_array($references)) {
            return $categoryData[$references[1]];
        }

        return null;
    }
}
