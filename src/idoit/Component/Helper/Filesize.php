<?php

namespace idoit\Component\Helper;

/**
 * i-doit Filesize helper
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.7
 */
class Filesize
{
    /**
     * Converts a string representation of a filesize to bytes. Example string would be '256G', '1024 M' or '1TB'.
     *
     * Can be used for working with ini_get('memory_limit').
     *
     * @param string $value
     *
     * @return int
     */
    public static function toBytes($value)
    {
        $value = trim($value, ' B');
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int)$value;
        switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'p':
                $value *= 1024;
            case 't':
                $value *= 1024;
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }
}