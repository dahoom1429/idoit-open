<?php
namespace idoit\Module\Cmdb\Component\SyncMerger\DataRetriever;

use idoit\Component\Property\Property;
use isys_export_helper;
use isys_request;

class ByExportHelper implements DataRetrieverInterface
{
    /**
     * @var array
     */
    private static $ignoredCallbackMethods = [
        'dialog',
        'dialog_plus',
        'get_reference_value',
        'object_image',
        'cable_connection'
    ];

    /**
     * @var isys_export_helper[]
     */
    private static $helperClasses = [];

    /**
     * @param Property $property
     *
     * @return bool
     */
    public static function isApplicable(Property $property)
    {
        $callback = $property->getFormat()
            ->getCallback();

        if (is_array($callback) && !empty($callback) && !in_array($callback[1], self::$ignoredCallbackMethods)) {
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
     * @return array|mixed|null
     * @throws \Exception
     */
    public function retrieveValue(Property $property, array $properties, array $categoryData, array $currentData, isys_request $request)
    {
        $callback = $property->getFormat()->getCallback();
        $dbField = $property->getData()->getField();

        $helperClass = $callback[0];
        if (isset(self::$helperClasses[$helperClass])) {
            self::$helperClasses[$helperClass]->set_row($categoryData);
            self::$helperClasses[$helperClass]->set_reference_info($property->getData());
            self::$helperClasses[$helperClass]->set_format_info($property->getFormat());
            self::$helperClasses[$helperClass]->set_ui_info($property->getUi());
        } else {
            self::$helperClasses[$helperClass] = new $helperClass(
                $categoryData,
                \isys_application::instance()->container->get('database'),
                $property->getData(),
                $property->getFormat(),
                $property->getUi()
            );
        }

        if (($unitPropertyKey = $property->getFormat()
            ->getUnit())) {
            $unitProperty = $properties[$unitPropertyKey];
            $unitDbField = $unitProperty->getData()
                ->getField();

            if (isset($categoryData[$unitDbField])) {
                self::$helperClasses[$helperClass]->set_unit_const($categoryData[$unitDbField]);
            }
        }

        $exportMethod = $callback[1];
        $exportValue = self::$helperClasses[$helperClass]->$exportMethod($categoryData[$dbField]);

        if (is_object($exportValue) && $exportValue instanceof \isys_export_data) {
            $exportValue = $exportValue->get_data();
        }

        if (is_array($exportValue)) {
            if (isset($exportValue['id'])) {
                return $exportValue['id'];
            }

            if (isset($exportValue[C__DATA__VALUE])) {
                return $exportValue[C__DATA__VALUE];
            }

            if (isset($exportValue['title'])) {
                return $exportValue['title'];
            }

            $returnData = [];
            foreach ($exportValue as $key => $data) {
                if (is_array($data)) {
                    if (isset($data['id'])) {
                        $returnData[] = $data['id'];
                        continue;
                    }

                    if (isset($data[C__DATA__VALUE])) {
                        $returnData[] = $data[C__DATA__VALUE];
                        continue;
                    }

                    if (isset($exportValue['title'])) {
                        $returnData[] = $data['title'];
                        continue;
                    }
                }
            }
            return $returnData;
        }

        return null;
    }
}
