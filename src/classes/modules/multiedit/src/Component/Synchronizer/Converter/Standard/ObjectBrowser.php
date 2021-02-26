<?php declare(strict_types=1);

namespace idoit\Module\Multiedit\Component\Synchronizer\Converter\Standard;

use idoit\Module\Multiedit\Component\Synchronizer\Converter\ConvertInterface;
use isys_format_json;

/**
 * Class ObjectBrowser
 *
 * @package idoit\Module\Multiedit\Component\Synchronizer\Converter\Standard
 */
class ObjectBrowser implements ConvertInterface
{
    /**
     * @var string
     */
    private $converterType = C__PROPERTY__INFO__TYPE__OBJECT_BROWSER;

    /**
     * @param $value
     *
     * @return array|bool|float|int|mixed|string|null
     * @throws \idoit\Exception\JsonException
     */
    public function convertValue($value)
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && isys_format_json::is_json_array($value)) {
            return isys_format_json::decode($value);
        }

        return $value;
    }
}
