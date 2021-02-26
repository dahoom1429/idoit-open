<?php

/**
 * i-doit
 *
 * Data format.
 *
 * @package     i-doit
 * @subpackage  Data
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_format
{
    /**
     * Encodes a value.
     *
     * @param   mixed $p_value The value being encoded. Can be any type except a resource.
     *
     * @return  string  Encoded value
     */
    abstract public static function encode($p_value);

    /**
     * Decodes a value.
     *
     * @param   string $p_value The string being decoded
     *
     * @return  mixed  Decoded value
     */
    abstract public static function decode($p_value);
}