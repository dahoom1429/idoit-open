<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter\Check;

use idoit\Component\Property\Property;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Value;

/**
 * Interface CheckInterface
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter\Check
 */
interface CheckInterface
{
    /**
     * @param string $propertyKey
     * @param Value[] $data
     */
    public static function check($propertyKey, $data);
}
