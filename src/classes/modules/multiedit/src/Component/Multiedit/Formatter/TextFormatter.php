<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter;

use idoit\Component\Property\Property;
use idoit\Exception\Exception;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Text\NumberDouble;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Text\NumberFloat;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Text\Money;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Text\Text;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Text\Time;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Text\NumberInt;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Text\Password;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Text\TextArea;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Text\Timeperiod;
use isys_smarty_plugin_f_text;
use isys_convert;
use idoit\Module\Multiedit\Component\Multiedit\Exception\FormatSourceException;
use idoit\Module\Multiedit\Component\Multiedit\Exception\FormatCellException;

/**
 * Class TextFormatter
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter
 */
class TextFormatter extends Formatter implements FormatterInterface
{
    /**
     * @var string
     */
    protected $type = C__PROPERTY__UI__TYPE__TEXT;

    /**
     * @var array
     */
    private static $formatter = [
        C__PROPERTY__INFO__TYPE__TEXT     => Text::class,
        C__PROPERTY__INFO__TYPE__TIME     => Time::class,
        C__PROPERTY__INFO__TYPE__PASSWORD => Password::class,
        C__PROPERTY__INFO__TYPE__DOUBLE   => NumberDouble::class,
        C__PROPERTY__INFO__TYPE__INT      => NumberInt::class,
        C__PROPERTY__INFO__TYPE__FLOAT    => NumberFloat::class,
        C__PROPERTY__INFO__TYPE__MONEY    => Money::class,
        C__PROPERTY__INFO__TYPE__TEXTAREA => TextArea::class,
        C__PROPERTY__INFO__TYPE__TIMEPERIOD => Timeperiod::class,
        C__PROPERTY__INFO__TYPE__COMMENTARY => TextArea::class
    ];

    /**
     * @param ValueFormatter $valueFormatter
     *
     * @return Value
     */
    public static function formatSource($valueFormatter)
    {
        $property = $valueFormatter->getProperty();

        // Determine which popup source formatter
        $textClass = self::$formatter[$property->getInfo()
            ->getType()];
        try {
            return $textClass::formatSource($valueFormatter);
        } catch (\Exception $e) {
            $nameSpaceArr = explode('\\', $textClass);
            $type = array_pop($nameSpaceArr);
            throw new FormatSourceException("Source Data for property: '{$valueFormatter->getPropertyKey()}' could not be handled for Formatter '{$type}'. Message: " . $e->getMessage());
        }
    }

    /**
     * @param ValueFormatter $valueFormatter
     *
     * @return string|void
     */
    public static function formatCell($valueFormatter)
    {
        $property = $valueFormatter->getProperty();

        // Determine which popup cell formater
        $textClass = self::$formatter[$property->getInfo()
            ->getType()];
        try {
            return $textClass::formatCell($valueFormatter);
        } catch (\Exception $e) {
            $nameSpaceArr = explode('\\', $textClass);
            $type = array_pop($nameSpaceArr);
            throw new FormatCellException("Formating cell for property '{$valueFormatter->getPropertyKey()}' could not be handled for Formatter '{$type}'. Message: " . $e->getMessage());
        }
    }

    /**
     * @param Value    $value
     * @param Property $property
     *
     * @return mixed|void
     */
    public static function checkFilter($value, $property)
    {
    }
}
