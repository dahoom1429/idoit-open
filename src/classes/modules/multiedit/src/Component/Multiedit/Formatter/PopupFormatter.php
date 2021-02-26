<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter;

use idoit\Exception\Exception;
use idoit\Module\Multiedit\Component\Multiedit\Exception\FormatSourceException;
use idoit\Module\Multiedit\Component\Multiedit\Exception\FormatCellException;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Popup\DialogPlus;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Popup\Multiselect;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Popup\ObjectBrowser;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Popup\TimeDate;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Popup\TimeDateTime;

/**
 * Class PopupFormatter
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter
 */
class PopupFormatter extends Formatter implements FormatterInterface
{
    /**
     * @var string
     */
    protected $type = C__PROPERTY__UI__TYPE__POPUP;

    /**
     * @var array
     */
    private static $formatter = [
        C__PROPERTY__INFO__TYPE__MULTISELECT    => Multiselect::class,
        C__PROPERTY__INFO__TYPE__OBJECT_BROWSER => ObjectBrowser::class,
        C__PROPERTY__INFO__TYPE__DIALOG_PLUS    => DialogPlus::class,
        C__PROPERTY__INFO__TYPE__DATE           => TimeDate::class,
        C__PROPERTY__INFO__TYPE__DATETIME       => TimeDateTime::class,
        C__PROPERTY__INFO__TYPE__N2M            => ObjectBrowser::class,
    ];

    /**
     * @param ValueFormatter $valueFormatter
     *
     * @return Value
     * @throws FormatSourceException
     */
    public static function formatSource($valueFormatter)
    {
        $property = $valueFormatter->getProperty();
        // Determine which popup source formatter
        $popupClass = self::$formatter[$property->getInfo()
            ->getType()];

        try {
            return $popupClass::formatSource($valueFormatter);
        } catch (\Exception $e) {
            $nameSpaceArr = explode('\\', $popupClass);
            $type = array_pop($nameSpaceArr);
            throw new FormatSourceException("Source Data for property: '{$valueFormatter->getPropertyKey()}' could not be handled for Formatter '{$type}'. Message: " . $e->getMessage());
        }
    }

    /**
     * @param ValueFormatter $valueFormatter
     *
     * @return string
     * @throws FormatCellException
     */
    public static function formatCell($valueFormatter)
    {
        $property = $valueFormatter->getProperty();
        // Determine which popup cell formater
        $popupClass = self::$formatter[$property->getInfo()
            ->getType()];

        try {
            return $popupClass::formatCell($valueFormatter);
        } catch (\Exception $e) {
            $nameSpaceArr = explode('\\', $popupClass);
            $type = array_pop($nameSpaceArr);
            throw new FormatCellException("Formating cell for property '{$valueFormatter->getPropertyKey()}' could not be handled for Formatter '{$type}'. Message: " . $e->getMessage());
        }
    }

    /**
     * @param Value                              $value
     * @param \idoit\Component\Property\Property $property
     *
     * @return mixed|void
     */
    public static function checkFilter($value, $property)
    {
        // TODO: Implement checkFilter() method.
    }
}
