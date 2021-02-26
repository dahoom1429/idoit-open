<?php

namespace idoit\Module\Cmdb\Component\SyncMerger\DataRetriever;

use idoit\Component\Property\Property;
use isys_request;

class ByDataset implements DataRetrieverInterface
{
    /**
     * @param Property $property
     *
     * @return bool
     */
    public static function isApplicable(Property $property)
    {
        $dbField = $property->getData()
            ->getField();
        $references = $property->getData()
            ->getReferences();

        if ($dbField && empty($references)) {
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
        $dbField = $property->getData()->getField();
        if (isset($categoryData[$dbField])) {
            return $categoryData[$dbField];
        }

        return null;
    }
}
