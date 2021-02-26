<?php

/**
 * i-doit
 *
 * Helper methods for color handling and calculation.
 *
 * @package     i-doit
 * @subpackage  Helper
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.0
 */
class isys_helper_color
{
    /**
     * Calculates the RED, GREEN and BLUE values from a given HEX.
     *
     * @static
     *
     * @param   string  $p_hex   May be from 6 to 7 characters long (may prepend '#').
     * @param   integer $p_alpha Alpha-transparency from 0 to 100.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function calc_rgb_from_hex($p_hex, $p_alpha = null)
    {
        $l_hex = ltrim($p_hex, '#');

        // We only allow 6 character HEX values.
        if (strlen($l_hex) !== 6) {
            return false;
        }

        list($l_red, $l_green, $l_blue) = str_split($l_hex, 2);

        return [
            'r' => hexdec($l_red),
            'g' => hexdec($l_green),
            'b' => hexdec($l_blue),
            'a' => ($p_alpha / 100.0)
        ];
    }

    /**
     * This method will render a HEX string from given RGB values.
     *
     * @static
     *
     * @param   integer $p_red
     * @param   integer $p_green
     * @param   integer $p_blue
     * @param   string  $p_prepend
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function calc_hex_from_rgb($p_red, $p_green, $p_blue, $p_prepend = '#')
    {
        return $p_prepend . substr('0' . dechex($p_red), -2) . substr('0' . dechex($p_green), -2) . substr('0' . dechex($p_blue), -2);
    }

    /**
     * Returns a string like "rgb(255, 255, 255)" and "rgba(255, 255, 255, 1)".
     *
     * @static
     *
     * @param   string  $p_hex   May be from 6 to 7 characters long (may append '#').
     * @param   integer $p_alpha Alpha-transparency from 0 to 100.
     *
     * @return  string
     * @uses    isys_helper_color::calc_rgb_from_hex
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function render_rgb_from_hex($p_hex, $p_alpha = null)
    {
        $l_rgba = self::calc_rgb_from_hex($p_hex, $p_alpha);

        if ($l_rgba === false) {
            return '';
        }

        if ($p_alpha === null) {
            return 'rgb(' . $l_rgba['r'] . ', ' . $l_rgba['g'] . ', ' . $l_rgba['b'] . ')';
        }

        return 'rgba(' . $l_rgba['r'] . ', ' . $l_rgba['g'] . ', ' . $l_rgba['b'] . ', ' . $l_rgba['a'] . ')';
    }

    /**
     * This method returns a color, corresponding to a given percentage:
     *
     * @param   float  $p_percent
     * @param   string $p_prepend
     *
     * @return  string  The color as HEX string.
     * @see     http://stackoverflow.com/questions/17525215/calculate-color-values-from-green-to-red
     */
    public static function retrieve_color_by_percent($p_percent, $p_prepend = '#')
    {
        // As the function expects a value between 0 and 1, and red = 0° and green = 120° we convert the input to the appropriate hue value.
        $l_hue = $p_percent * 1.2 / 360;

        // We convert hsl to rgb (saturation 100%, lightness 50%).
        $l_rgb = self::hsl_to_rgb($l_hue, 1, .5);

        // We format to css value and return.
        return self::calc_hex_from_rgb($l_rgb['r'], $l_rgb['g'], $l_rgb['b'], $p_prepend);
    }

    /**
     * Method for calculating HSL values to RGB.
     *
     * @param   float $p_hue
     * @param   float $p_saturation
     * @param   float $p_lightness
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function hsl_to_rgb($p_hue, $p_saturation, $p_lightness)
    {
        if ($p_saturation === 0) {
            // Monochrom.
            $l_red = $l_green = $l_blue = $p_lightness;
        } else {
            $l_q = ($p_lightness < 0.5 ? $p_lightness * (1 + $p_saturation) : $p_lightness + $p_saturation - $p_lightness * $p_saturation);
            $l_p = (2 * $p_lightness - $l_q);

            $l_red = self::hue_to_rgb($l_p, $l_q, ($p_hue + 1 / 3));
            $l_green = self::hue_to_rgb($l_p, $l_q, $p_hue);
            $l_blue = self::hue_to_rgb($l_p, $l_q, ($p_hue - 1 / 3));
        }

        return [
            'r' => floor($l_red * 255),
            'g' => floor($l_green * 255),
            'b' => floor($l_blue * 255)
        ];
    }

    /**
     * Method for calculating HUE values to RGB.
     *
     * @param   float $p_p
     * @param   float $p_q
     * @param   float $p_tint
     *
     * @return  float
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function hue_to_rgb($p_p, $p_q, $p_tint)
    {
        if ($p_tint < 0) {
            $p_tint += 1;
        }

        if ($p_tint > 1) {
            $p_tint -= 1;
        }

        if ($p_tint < (1 / 6)) {
            return $p_p + ($p_q - $p_p) * 6 * $p_tint;
        }

        if ($p_tint < (1 / 2)) {
            return $p_q;
        }

        if ($p_tint < (2 / 3)) {
            return $p_p + ($p_q - $p_p) * (2 / 3 - $p_tint) * 6;
        }

        return $p_p;
    }

    /**
     * This fancy method calculates the "light" of a color.
     * Usefull for switching font-colors on light/dark background-colors.
     *
     * @param   string $p_hex
     *
     * @return  integer  The "light" value from 0-100.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function calc_color_light($p_hex)
    {
        $l_rgb = self::calc_rgb_from_hex($p_hex);

        if ($l_rgb === false) {
            return false;
        }

        unset($l_rgb['a']);

        // Because we get values from 0 to 510 (255+255) we need to divide by 5.1!
        return (max($l_rgb) + min($l_rgb)) / 5.1;
    }

    /**
     * Method for creating a HEX color by any given string.
     *
     * @param   string $p_string
     * @param   string $p_prepend
     *
     * @return  string
     */
    public static function pastel_color_from_string($p_string, $p_prepend = '#')
    {
        $l_hash = md5($p_string);
        $l_red = hexdec(substr($l_hash, 8, 2));
        $l_green = hexdec(substr($l_hash, 4, 2));
        $l_blue = hexdec(substr($l_hash, 0, 2));

        if ($l_red < 128) {
            $l_red += 128;
        }

        if ($l_green < 128) {
            $l_green += 128;
        }

        if ($l_blue < 128) {
            $l_blue += 128;
        }

        return self::calc_hex_from_rgb($l_red, $l_green, $l_blue, $p_prepend);
    }
}