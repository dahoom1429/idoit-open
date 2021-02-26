<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter;

use idoit\Component\Property\Property;
use isys_convert;

/**
 * Class Formatter
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter
 */
abstract class Formatter
{
    private static $helperClasses = [];

    /**
     * @param $valueFormatter ValueFormatter
     *
     * @return Value
     */
    public static function formatSource($valueFormatter)
    {
        /**
         * @var $rawData []
         */
        $valueObject = $valueFormatter->getValue();
        $value = $valueObject->getValue();
        $valueObject->setViewValue($value);
        $property = $valueFormatter->getProperty();
        $params = $property->getUi()
            ->getParams();
        $rawData = $valueFormatter->getRawDataset();

        if ($params['p_strValue'] instanceof \isys_callback) {
            $request = (new \isys_request())->set_category_data_id($valueFormatter->getEntryId())
                ->set_object_id($valueFormatter->getObjectId());

            $valueObject->setViewValue($params['p_strValue']->execute($request));
        } elseif (($callback = $property->getFormat()
            ->getCallback())) {
            list($callbackClass, $callbackMethod, $calllbackParams) = $callback;
            switch ($callbackMethod) {
                case 'convert':
                    if ($calllbackParams && ($referencedPropertyValue = $valueFormatter->getReferencedPropertyValue())) {
                        $convertMethod = $calllbackParams[0];
                        if (method_exists(isys_convert::class, $convertMethod)) {
                            $viewValue = isys_convert::$convertMethod($value, $referencedPropertyValue->getValue(), C__CONVERT_DIRECTION__BACKWARD);

                            if ($convertMethod === 'memory') {
                                $viewValue = round($viewValue, 2, PHP_ROUND_HALF_DOWN);
                            }

                            $valueObject->setViewValue($viewValue);
                        }
                    }
                    break;
                case 'exportIpReference':
                    $valueObject->setViewValue($rawData['isys_cats_net_ip_addresses_list__title']);
                    break;
                default:
                    if (!isset(self::$helperClasses[$callbackClass])) {
                        self::$helperClasses[$callbackClass] = new $callbackClass();
                        self::$helperClasses[$callbackClass]->set_reference_info($property->getData());
                        self::$helperClasses[$callbackClass]->set_format_info($property->getFormat());
                        self::$helperClasses[$callbackClass]->set_ui_info($property->getUi());
                        self::$helperClasses[$callbackClass]->set_database(\isys_application::instance()->container->get('database'));
                    }

                    $valueObject->setViewValue(self::$helperClasses[$callbackClass]->$callbackMethod($value));

                    break;
            }
        }

        return $valueObject;
    }

    /**
     * @param $valueFormatter
     *
     * @return string
     */
    public static function formatCell($valueFormatter)
    {
        return '';
    }

    /**
     * @param $value
     * @param $property
     */
    public static function checkFilter($value, $property)
    {
    }
}
