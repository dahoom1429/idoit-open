<?php

/**
 * i-doit core classes
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_string
{
    /**
     * Split string by delimiters | ; ,
     *
     * @param $p_str
     *
     * @return array
     */
    public static function split($p_str)
    {
        return array_map('trim', preg_split('/[|;,]/', $p_str, 0, PREG_SPLIT_NO_EMPTY));
    }

    /**
     * Highlight string and preserve it's original case
     *
     * @param string $needle
     * @param string $haystack
     * @param int    $minimumLength
     *
     * @return string
     */
    public static function highlight($needle, $haystack, $minimumLength = 3)
    {
        if (strlen($needle) >= $minimumLength) {
            $needle = isys_helper::sanitize_text(strip_tags($needle));

            $ind = stripos($haystack, $needle);
            $len = strlen($needle);
            if ($ind !== false) {
                return substr($haystack, 0, $ind) . "<span class=\"searchHighlight\">" . substr($haystack, $ind, $len) . "</span>" .
                    self::highlight($needle, substr($haystack, $ind + $len));
            }
        }

        return $haystack;
    }
}