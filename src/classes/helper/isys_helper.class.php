<?php

/**
 * i-doit
 *
 * Helper methods
 *
 * @package     i-doit
 * @subpackage  Helper
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_helper
{
    /**
     * Filters a text. Useful for filter functions.
     *
     * @param   string $p_string String that will be validated.
     *
     * @return  mixed  Returns valid string, otherwise false.
     */
    public static function filter_text($p_string)
    {
        if (is_string($p_string) && mb_strlen($p_string) <= 255) {
            return $p_string;
        }

        return false;
    }

    /**
     * Filters a textarea. Useful for filter functions.
     *
     * @param   string $p_string String that will be validated.
     *
     * @return  mixed  Returns valid string, otherwise false.
     */
    public static function filter_textarea($p_string)
    {
        if (is_string($p_string) && mb_strlen($p_string) <= 65534) {
            return $p_string;
        }

        return false;
    }

    /**
     * Filters a JSON array of IDs.
     *
     * @param   string $p_string String that will be validated.
     *
     * @return string|bool Returns valid string, otherwise false.
     */
    public static function filter_json_array_of_ids($p_string)
    {
        try {
            $l_ids = isys_format_json::decode($p_string);

            foreach ($l_ids as $l_id) {
                if (!is_numeric($l_id)) {
                    return false;
                }
            }
        } catch (Exception $e) {
            return false;
        }

        return $p_string;
    }

    /**
     * Filters a comma separated list of IDs.
     *
     * @param   string $p_string String that will be validated.
     *
     * @return  mixed  Returns valid string, otherwise false.
     */
    public static function filter_list_of_ids($p_string)
    {
        $l_ids = array_filter(explode(',', $p_string));

        foreach ($l_ids as $l_id) {
            if (!is_numeric($l_id)) {
                return false;
            }
        }

        return $p_string;
    }

    /**
     * Filters an array of integers. Note: filter_var() accepts arrays as first
     * argument but handles recursively every item, so this function accepts integers as first argument.
     *
     * @param   integer $p_value Integer that will be validated.
     *
     * @return  mixed  Returns valid integer, otherwise false.
     */
    public static function filter_array_of_ints($p_value)
    {
        if (!is_int($p_value)) {
            return false;
        }

        return $p_value;
    }

    /**
     * Filters a date or date time.
     *
     * @param   string $p_string String that will be validated.
     *
     * @return  mixed  Returns valid string, otherwise false.
     */
    public static function filter_date($p_string)
    {
        if ($p_string == 'undefined-undefined-undefined') {
            $p_string = '1970-01-01';
        } elseif (is_numeric($p_string) && strlen($p_string) > 9) {
            return (date('d-m-Y', $p_string) === false ? false : true);
        }

        $l_date = strtotime($p_string);

        if ($l_date === false) {
            return false;
        }

        return $p_string;
    }

    /**
     * Method for removing whitespaces from a string.
     *
     * @param   string $p_string
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function strip_whitespaces($p_string)
    {
        return preg_replace('~(\s)~', '', $p_string);
    }

    /**
     * Filters a combined dialog which contains the category data identifier and the category constant to resolv the referenced data set.
     *
     * @param   string $p_string Format: <id>_<constant>
     *
     * @return  mixed   Returns valid string, otherwise false.
     */
    public static function filter_combined_dialog($p_string)
    {
        // 'Empty' value:
        if ($p_string === '-1') {
            return $p_string;
        }

        $l_separator_pos = strpos($p_string, '_');

        if ($l_separator_pos === false) {
            return false;
        }

        $l_category_data_id = substr($p_string, 0, $l_separator_pos);
        $l_category_constant = substr($p_string, ($l_separator_pos + 1));

        // Invalid category data identifier.
        if (!is_numeric($l_category_data_id) || $l_category_data_id <= 0) {
            return false;
        }

        // Invalid category constant.
        if (!defined($l_category_constant)) {
            return false;
        }

        return $p_string;
    }

    /**
     * Filters a mac address.
     *
     * @param  string  $macAddress Hex or binary
     *
     * @return string|bool Returns valid string, otherwise false.
     */
    public static function filter_mac_address($macAddress)
    {
        // We convert all sorts of mac addresses to one "default" form.
        $macAddressRaw = preg_replace('/[^0-9a-fA-F]+/', '', $macAddress);

        if ((mb_strlen($macAddressRaw) === 48 || mb_strlen($macAddressRaw) === 56) && preg_match('/^[01]+$/', $macAddressRaw)) {
            // We got a binary MAC!
            return implode(':', str_split($macAddressRaw, 8));
        }

        if ((mb_strlen($macAddressRaw) === 12 || mb_strlen($macAddressRaw) === 16) && preg_match('/^[0-9a-fA-F]+$/', $macAddressRaw)) {
            // We got a HEX MAC!
            return implode(':', str_split($macAddressRaw, 2));
        }

        return false;
    }

    /**
     * This helper will parse a string like "1.000,95 Bla" to float "1000.95" (with up to four digits after the point).
     *
     * @param   string $p_string
     *
     * @return  float
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function filter_number($p_string)
    {
        // Check, if we got a positive or negative number.
        $l_sign = (substr(trim($p_string), 0, 1) === '-') ? '-' : '';

        // First we strip the currency ("GHZ", "Euro", "$", ...) including spaces.
        $p_string = self::strip_non_numeric($p_string);

        // If the number is null
        if ($p_string === null) {
            return null;
        }

        // @see ID-4191
        if ($p_string === '') {
            return 0;
        }

        // Match part which starts and ends with a digit
        $matchingResult = preg_match('/\d[\d,\.]*\d/', $p_string, $matches);

        if ($matchingResult >= 1) {
            $p_string = $matches[0];
        }

        // Check if someone wrote a string like "1.000.000".
        if (substr_count($p_string, '.') > 1) {
            $p_string = str_replace('.', '', $p_string);
        }

        // Check if someone wrote a string like "1,000,000".
        if (substr_count($p_string, ',') > 1) {
            $p_string = str_replace(',', '', $p_string);
        }

        // If we find a single point or a single comma, we use the last found one as decimal point.
        if (strpos($p_string, '.') !== false || strpos($p_string, ',') !== false) {
            if (strpos($p_string, '.') > strpos($p_string, ',')) {
                $p_string = str_replace(',', '', $p_string);
            } elseif (strpos($p_string, '.') < strpos($p_string, ',')) {
                $p_string = str_replace(['.', ','], array('', '.'), $p_string);
            } elseif (strpos($p_string, '.') === false && is_int(strpos($p_string, ','))) {
                $p_string = str_replace(',', '.', $p_string);
            }
        }

        // Finally check if number is not numeric then return null
        if (!is_numeric($p_string)) {
            return null;
        }

        // Now we replace commas with dots: "1000,10" to "1000.10" and return the rounded value.
        return round(str_replace(',', '.', $l_sign . $p_string), 4);
    }

    /**
     * Filters selection for the property selector smarty plugin.
     *
     * @param string $p_string JSON string
     *
     * @return string|bool Returns valid string, otherwise false.
     */
    public static function filter_property_selector($p_string)
    {
        try {
            $l_raw = isys_format_json::decode($p_string, true);

            foreach ($l_raw as $l_index => $l_sorted_entries) {
                if (!is_int($l_index)) {
                    return false;
                }

                foreach ($l_sorted_entries as $l_category_type => $l_category_ids) {
                    switch ($l_category_type) {
                        case 'g':
                        case 's':
                        case 'g_custom':
                            break;
                        default:
                            return false;
                    }

                    foreach ($l_category_ids as $l_category_const => $l_properties) {
                        if (is_string($l_category_const) && !defined($l_category_const)) {
                            return false;
                        }

                        if (is_numeric($l_category_const) && $l_category_const < 0) {
                            return false;
                        }

                        foreach ($l_properties as $l_property) {
                            if (!is_string($l_property) || empty($l_property)) {
                                return false;
                            }
                        }
                    }
                }
            }
        } catch (Exception $l_exception) {
            return false;
        }

        return $p_string;
    }

    /**
     * Strips everything "not-number"-like.
     *
     * @param   string $p_data
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function strip_non_numeric($p_data)
    {
        return preg_replace('/([^,\.\d])*/i', '', $p_data);
    }

    /**
     * Removes all HTML tags.
     *
     * @param  string $p_string
     *
     * @return string
     */
    public static function sanitize_text($p_string)
    {
        if (isys_tenantsettings::get('cmdb.registry.sanitize_input_data', 1)) {
            return strip_tags(str_replace(["\n", "\r", '&nbsp;', chr(194) . chr(160)], ['', '', ' ', ' '], $p_string));
        }

        return $p_string;
    }

    /**
     * Sanitizes float|double values
     *
     * @deprecated  Simply use "isys_helper::filter_number()".
     *
     * @param       mixed $p_value
     *
     * @return      float
     */
    public static function sanitize_number($p_value)
    {
        return self::filter_number($p_value);
    }

    /**
     * Public static method for retrieving an array which contains all numbers, which are included.
     * For example: 81 => array(64, 16, 1).
     *
     * @param   integer $p_number
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function split_bitwise($p_number)
    {
        $l_return = [];
        $p_number = (int)$p_number;

        for ($i = strlen(decbin($p_number));$i >= 0;$i--) {
            $l_current = pow(2, $i);

            if ($l_current & $p_number) {
                $l_return[] = $l_current;
            }
        }

        return $l_return;
    }

    /**
     * Returns an array of image mimetypes.
     *
     * @static
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function get_image_mimetypes()
    {
        return [
            'bmp'  => 'image/bmp',
            'gif'  => 'image/gif',
            'ico'  => 'image/x-icon',
            'jpe'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg'  => 'image/jpeg',
            'png'  => 'image/png',
            'svg'  => 'image/svg+xml',
            'tif'  => 'image/tiff',
            'tiff' => 'image/tiff',
        ];
    }

    /**
     * Converts all occurrences like http://kb.i-doit.com to <a href="http://kb.i-doit.com">http://kb.i-doit.com</a>
     *
     * @param $string
     *
     * @return string
     */
    public static function covertUrlsToHtmlLinks($string)
    {
        return preg_replace_callback('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', function ($url) {
            return '<a href="' . $url[0] . '" target="_blank">' . $url[0] . '</a>';
        }, $string);
    }
}
