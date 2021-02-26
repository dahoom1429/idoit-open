<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter;

/**
 * Class UploadFormatter
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter
 */
class UploadFormatter extends Formatter implements FormatterInterface
{

    /**
     * @var string
     */
    protected $type = C__PROPERTY__UI__TYPE__UPLOAD;

    /**
     * @var bool
     */
    protected static $changeAll = false;

    /**
     * @param ValueFormatter $valueFormatter
     *
     * @return Value|void
     */
    public static function formatSource($valueFormatter)
    {
        // TODO: Implement formatSource() method.
    }

    /**
     * @param ValueFormatter $valueFormatter
     *
     * @return string|void
     */
    public static function formatCell($valueFormatter)
    {
        // TODO: Implement formatCell() method.
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
