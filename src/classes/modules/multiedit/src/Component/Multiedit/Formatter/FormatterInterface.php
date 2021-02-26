<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter;

use idoit\Component\Property\Property;

/**
 * Interface FormatterInterface
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter
 */
interface FormatterInterface
{

    /**
     * @param $valueFormatter ValueFormatter
     *
     * @return Value
     */
    public static function formatSource($valueFormatter);

    /**
     * @param $valueFormatter ValueFormatter
     *
     * @return string
     */
    public static function formatCell($valueFormatter);

    /**
     * @param $value    Value
     * @param $property Property
     *
     * @return mixed
     */
    public static function checkFilter($value, $property);
}
