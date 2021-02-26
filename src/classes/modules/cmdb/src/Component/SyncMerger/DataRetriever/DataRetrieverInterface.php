<?php
namespace idoit\Module\Cmdb\Component\SyncMerger\DataRetriever;

use idoit\Component\Property\Property;
use isys_request;

interface DataRetrieverInterface
{
    /**
     * @param Property $property
     *
     * @return mixed
     */
    public static function isApplicable(Property $property);

    /**
     * @param Property     $property
     * @param array        $properties
     * @param array        $categoryData
     * @param array        $currentData
     * @param isys_request $request
     *
     * @return mixed|null
     */
    public function retrieveValue(Property $property, array $properties, array $categoryData, array $currentData, isys_request $request);
}
