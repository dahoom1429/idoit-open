<?php declare(strict_types=1);

namespace idoit\Module\Multiedit\Component\Synchronizer\Converter\Standard;

use idoit\Module\Multiedit\Component\Synchronizer\Converter\ConvertInterface;

/**
 * Class Multiselect
 *
 * @package idoit\Module\Multiedit\Component\Synchronizer\Converter\Standard
 */
class Multiselect implements ConvertInterface
{
    /**
     * @var string
     */
    private $converterType = C__PROPERTY__INFO__TYPE__MULTISELECT;

    /**
     * @param $value
     *
     * @return array|false|string|string[]
     */
    public function convertValue($value)
    {
        if (is_array($value)) {
            return $value;
        }

        $delimeter = ',';

        if (is_string($value) && strpos($value, $delimeter) !== false) {
            return explode($delimeter, $value);
        }

        return $value;
    }
}
