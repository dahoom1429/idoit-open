<?php
namespace idoit\Module\Cmdb\Component\SyncMerger\DataRetriever;

use idoit\Component\Property\Property;
use isys_format_json;
use isys_request;

class ByUiCallback implements DataRetrieverInterface
{
    /**
     * @param Property $property
     *
     * @return bool
     */
    public static function isApplicable(Property $property)
    {
        $uiParams = $property->getUi()->getParams();
        $dbField = $property->getData()->getField();
        if ($uiParams['p_arData'] instanceof \isys_callback && $dbField) {
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
     * @throws \idoit\Exception\JsonException
     */
    public function retrieveValue(Property $property, array $properties, array $categoryData, array $currentData, isys_request $request)
    {
        $uiParams = $property->getUi()->getParams();
        $data = $uiParams['p_arData']->execute($request);
        $dbField = $property->getData()->getField();

        if (isys_format_json::is_json_array($data)) {
            $data = isys_format_json::decode($data);
        }

        if (is_string($data)) {
            $data = unserialize($data);
        }

        if (isset($data[$dbField])) {
            return $data[$dbField];
        }

        return null;
    }
}
