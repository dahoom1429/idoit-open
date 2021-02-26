<?php
/**
 * i-doit
 *
 * Old i-doit core functions. Keeping these for compability reasons.
 *
 * @package     modules
 * @subpackage  nostalgia
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.9
 */

if (!function_exists('_get_browser')) {
    /**
     * Returns the browser type and version.
     *
     * @deprecated
     *
     * @return  array
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    function _get_browser()
    {
        $l_arBrowser = [
            'OPERA',
            'MSIE',
            'NETSCAPE',
            'FIREFOX',
            'SAFARI',
            'KONQUEROR',
            'MOZILLA'
        ];

        $l_info['type'] = 'OTHER';

        foreach ($l_arBrowser as $l_parent) {
            if (($l_s = stripos($_SERVER['HTTP_USER_AGENT'], $l_parent)) !== false) {
                $l_f = $l_s + strlen($l_parent);
                $l_version = substr($_SERVER['HTTP_USER_AGENT'], $l_f, 5);
                $l_version = preg_replace('/[^0-9,.]/', '', $l_version);
                $l_info['type'] = $l_parent;
                $l_info['version'] = $l_version;
                break; // first match wins
            }
        }

        return $l_info;
    }
}

if (!function_exists('isys_glob_bin2ip')) {
    /**
     * This method originally comes from http://de2.php.net/ip2long by a guy named "anjo2".
     *
     * @deprecated
     *
     * @param   string $p_bin The binary
     *
     * @return  mixed  String if everything went well, null if "inet_pton" function does not exist.
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    function isys_glob_bin2ip($p_bin)
    {
        if (function_exists('inet_pton')) {
            // 32bits (ipv4).
            if (strlen($p_bin) <= 32) {
                return long2ip(base_convert($p_bin, 2, 10));
            }

            if (strlen($p_bin) != 128) {
                return false;
            }

            if (defined('AF_INET6')) {
                $l_pad = 128 - strlen($p_bin);

                for ($i = 1;$i <= $l_pad;$i++) {
                    $p_bin = "0" . $p_bin;
                }

                $l_bits = 0;
                $l_ipv6 = '';
                while ($l_bits <= 7) {
                    $l_bin_part = substr($p_bin, ($l_bits * 16), 16);
                    $l_ipv6 .= dechex(bindec($l_bin_part)) . ":";
                    $l_bits++;
                }

                return inet_ntop(inet_pton(substr($l_ipv6, 0, -1)));
            }
        }

        return null;
    }
}

if (!function_exists('isys_glob_ip2bin')) {
    /**
     * This method originally comes from http://de2.php.net/ip2long by a guy named "anjo2".
     *
     * @deprecated
     *
     * @param   string $p_ip The IP to be converted
     *
     * @return  mixed  String if everything went well, null if function "inet_pton" is not available.
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    function isys_glob_ip2bin($p_ip)
    {
        if (function_exists('inet_pton')) {
            if (filter_var($p_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
                return base_convert(ip2long($p_ip), 10, 2);
            }

            if (filter_var($p_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
                return false;
            }

            if (defined('AF_INET6')) {
                $l_ipbin = '';

                // inet_pton is only available for UNIX (PHP5) and Windows (PHP 5.3).
                if (($l_ip_n = inet_pton($p_ip)) === false) {
                    return false;
                }

                // 16 x 8 bit = 128bit (ipv6).
                $l_bits = 15;

                while ($l_bits >= 0) {
                    $l_bin = sprintf("%08b", (ord($l_ip_n[$l_bits])));
                    $l_ipbin = $l_bin . $l_ipbin;
                    $l_bits--;
                }

                return $l_ipbin;
            }
        }

        return null;
    }
}

// LF: Removed "isys_glob_assert_callback" in i-doit 1.14.

if (!function_exists('isys_glob_utf8_encode')) {
    /**
     * isys_glob_utf8_encode wrapper.
     *
     * @deprecated
     *
     * @param   $p_string
     *
     * @return  mixed
     */
    function isys_glob_utf8_encode($p_string)
    {
        return $p_string;
    }
}

if (!function_exists('isys_glob_utf8_decode')) {
    /**
     * isys_glob_utf8_decode wrapper.
     *
     * @deprecated
     *
     * @param   $p_string
     *
     * @return  mixed
     */
    function isys_glob_utf8_decode($p_string)
    {
        return $p_string;
    }
}

if (!function_exists('_L')) {
    /**
     * Get language constant from template language manager.
     *
     * @deprecated
     *
     * @param   string $p_language_constant
     * @param   mixed  $p_values
     *
     * @return  string
     */
    function _L($p_language_constant, $p_values = null)
    {
        return isys_application::instance()->container->get('language')
            ->get($p_language_constant, $p_values);
    }
}

if (!function_exists('_LL')) {
    /**
     * Get language constant from template language manager - usable for translations in strings.
     *
     * @deprecated
     *
     * @param   string $p_language_constant
     * @param   mixed  $p_values
     *
     * @return  string
     */
    function _LL($p_language_constant, $p_values = null)
    {
        return isys_application::instance()->container->get('language')
            ->get_in_text($p_language_constant);
    }
}

if (!function_exists('isys_glob_days_in_month')) {
    /**
     * Calculate the days in month.
     *
     * @deprecated
     *
     * @param  integer $p_month
     * @param  integer $p_year
     *
     * @return integer
     */
    function isys_glob_days_in_month($p_month, $p_year)
    {
        return $p_month == 2 ? ($p_year % 4 ? 28 : ($p_year % 100 ? 29 : ($p_year % 400 ? 28 : 29))) : (($p_month - 1) % 7 % 2 ? 30 : 31);
    }
}

if (!function_exists('is_valid_hostname')) {
    /**
     * Validates if given hostname is allowed.
     *
     * @deprecated
     *
     * @param   string $p_host
     *
     * @return  boolean
     */
    function is_valid_hostname($p_host)
    {
        /**
         * @todo Function seems to be unused
         */
        $l_hostnames = explode('.', $p_host);

        if (empty($p_host)) {
            return false;
        }

        if (count($l_hostnames) > 1) {
            foreach ($l_hostnames as $l_host) {
                if ($l_host !== '*' && !preg_match('/^[a-z\d\-]+$/i', $l_host)) {
                    return false;
                }
            }

            return true;
        }

        /**
         * @todo match_hostname() is not defined
         */
        return match_hostname($p_host);
    }
}

if (!function_exists('isys_glob_is_valid_hostname')) {
    /**
     * Checks if param is a valid hostname.
     *
     * @deprecated
     *
     * @param   string $p_hostname
     *
     * @return  string
     */
    function isys_glob_is_valid_hostname($p_hostname)
    {
        return preg_match('/^[a-z0-9.-_]+$/i', $p_hostname);
    }
}

if (!function_exists('isys_glob_create_tcp_address')) {
    /**
     * Builds a TCP Address.
     *
     * @deprecated
     *
     * @param   string  $p_host
     * @param   integer $p_port
     *
     * @return  string
     */
    function isys_glob_create_tcp_address($p_host, $p_port)
    {
        return $p_host . ':' . $p_port;
    }
}

if (!function_exists('isys_glob_prepare_string')) {
    /**
     * Escapes a string.
     *
     * @deprecated
     *
     * @param   string $p_string
     *
     * @return  string
     */
    function &isys_glob_prepare_string(&$p_string)
    {
        return str_replace(["\\", '"'], ["\\\\", "\\\""], $p_string);
    }
}

if (!function_exists('isys_glob_format_datetime')) {
    /**
     * Formats a datetime string.
     *
     * @deprecated Use isys_locale via container (isys_application::instance()->container->get('locales')).
     *
     * @param      string  $p_strDatetime
     * @param      boolean $p_bTime
     *
     * @return     string
     * @author     Niclas Potthast <npotthast@i-doit.org>
     */
    function isys_glob_format_datetime($p_strDatetime, $p_bTime = null)
    {
        if (strlen($p_strDatetime) >= 10) {
            if ($p_bTime) {
                return $p_strDatetime;
            }

            $p_strDatetime = substr($p_strDatetime, 0, 10);

            if (substr_count($p_strDatetime, '0000') > 0) {
                return '';
            }

            return $p_strDatetime;
        }

        return $p_strDatetime;
    }
}

if (!function_exists('isys_strlen')) {
    /**
     * Function which will return the string length. Will use mb_strlen if available.
     *
     * @param   string $p_string
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.com>
     *
     * @deprecated
     */
    function isys_strlen($p_string)
    {
        global $g_config;

        return (function_exists('mb_strlen') ? mb_strlen($p_string, $g_config['html-encoding']) : strlen($p_string));
    }
}

if (!function_exists('gettime')) {
    /**
     * Function that (in the past) returned the processing time up to the point of calling.
     *
     * @return float
     *
     * @deprecated
     */
    function gettime()
    {
        return 0.0;
    }
}

if (!function_exists('isys_glob_die')) {
    /**
     * This functions dies with a message defined by the specified parameters. $p_file should be __FILE__ and $p_line __LINE__.
     *
     * @param string $file
     * @param int    $line
     * @param string $message
     *
     * @deprecated
     */
    function isys_glob_die($file, $line, $message)
    {
        die("In {$file}/{$line}: {$message}");
    }
}

if (!function_exists('isys_glob_var_export')) {
    /**
     * Returns a variable preformatted.
     *
     * @param mixed $var
     *
     * @return string
     *
     * @deprecated
     */
    function isys_glob_var_export($var)
    {
        return '<pre>' . var_export($var, true) . '</pre>';
    }
}

if (!function_exists('isys_array_merge_keys')) {
    /**
     * Array_merge that preserves keys, truly accepts an arbitrary number of arguments, and saves space on the stack (non recursive).
     *
     * @return array
     *
     * @deprecated
     */
    function isys_array_merge_keys()
    {
        $l_result = [];
        $l_args = func_get_args();

        foreach ($l_args as $l_array) {
            foreach ($l_array as $l_key => $l_value) {
                $l_result[$l_key] = $l_value;
            }
        }

        return $l_result;
    }
}

if (!function_exists('isys_glob_str_replace')) {
    /**
     * Replace entries in $p_arr in $p_str. [KEY] is substituted by value in array.
     *
     * @param string $p_str
     * @param array  $p_arr
     *
     * @return string
     *
     * @deprecated
     */
    function isys_glob_str_replace($p_str, $p_arr)
    {
        if (is_array($p_arr)) {
            foreach ($p_arr as $l_subst => $l_val) {
                $p_str = str_replace("[" . $l_subst . "]", $l_val, $p_str);
            }

            return $p_str;
        }

        return null;
    }
}

if (!function_exists('isys_glob_reset_type')) {
    /**
     * Resets a variable type. Also detects booleans.
     *
     * @param mixed &$p_var
     *
     * @deprecated
     */
    function isys_glob_reset_type(&$p_var)
    {
        $l_vartype = gettype($p_var);

        if ($l_vartype === 'string') {
            if ($p_var === 'true' || $p_var === 'false') {
                $l_vartype = 'boolean';
            }
        }

        settype($p_var, $l_vartype);
    }
}

if (!function_exists('print_ar')) {
    /**
     * function for dumping a formatted output on screen (helpful for debugging).
     *
     * @param mixed $p_value
     *
     * @deprecated
     */
    function print_ar($p_value)
    {
        if (empty($p_value)) {
            echo 'Content is empty!';
        } else {
            echo '<pre>' . var_export($p_value, true) . '</pre>';
        }
    }
}

if (!function_exists('isys_glob_mkdate')) {
    /**
     * Makes a formatted date from p_datestring using strtotime.
     *
     * @param string $p_datestring
     * @param string $p_format
     *
     * @return string
     * @deprecated
     */
    function isys_glob_mkdate($p_datestring, $p_format)
    {
        return date($p_format, strtotime($p_datestring));
    }
}

if (!function_exists('isys_glob_datetime')) {
    /**
     * Returns the current date and time in datetime syntax: "YYYY-MM-DD HH:MM".
     *
     * @return string
     * @deprecated
     */
    function isys_glob_datetime()
    {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('isys_stristr')) {
    /**
     * stristr compatibility function
     *
     * @param string $haystack
     * @param string $needle
     *
     * @return bool|false|string
     * @deprecated
     */
    function isys_stristr($haystack, $needle)
    {
        return (function_exists('mb_stristr') ? mb_stristr($haystack, $needle) : stristr($haystack, $needle));
    }
}

if (!function_exists('array_find')) {
    /**
     * This function accepts "string parts" for searching a array (other than "array_search").
     *
     * @param string $needle
     * @param array  $haystack
     *
     * @return mixed
     * @deprecated
     */
    function array_find($needle, array $haystack)
    {
        foreach ($haystack as $item) {
            if (strpos($item, $needle) !== false) {
                return $item;
            }
        }

        return null;
    }
}

if (!function_exists('isys_glob_htmlspecialchars')) {
    /**
     * html_specialchars Wrapper.
     *
     * @param string  $p_val
     * @param integer $p_flags
     * @param string  $p_encoding
     * @param boolean $p_double_enc
     *
     * @return string
     * @deprecated
     */
    function isys_glob_htmlspecialchars($p_val, $p_flags = ENT_QUOTES, $p_encoding = null, $p_double_enc = false)
    {
        $p_encoding = (empty($p_encoding)) ? $GLOBALS['g_config']['html-encoding'] : $p_encoding;

        return htmlspecialchars($p_val, $p_flags, $p_encoding, $p_double_enc);
    }
}

if (!function_exists('isys_glob_url_remove')) {
    /**
     * Removes a GET parameter from an URL.
     *
     * @param string &$p_url
     * @param string  $p_parameter
     *
     * @return string
     * @deprecated Use isys_helper_link::remove_params_from_url();
     */
    function isys_glob_url_remove($p_url, $p_parameter)
    {
        $p_url = preg_replace("/(\?)" . $p_parameter . "=(.+?)(&|$)/", "\\1", $p_url);
        $p_url = preg_replace("/(&)" . $p_parameter . "=(.+?)(&|$)/", "\\3", $p_url);

        return $p_url;
    }
}

if (!function_exists('isys_glob_is_valid_ip')) {
    /**
     * True, if param is a valid ip v4 address.
     *
     * @param string $p_ip
     *
     * @return boolean
     * @deprecated Use methods from idoit\Component\Helper\Ip
     */
    function isys_glob_is_valid_ip($p_ip)
    {
        return (preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $p_ip) == 0) ? false : true;
    }
}

if (!function_exists('isys_glob_is_valid_ip6')) {
    /**
     * Validates an ip v6 address.
     *
     * @param string $p_ip
     *
     * @return boolean
     * @deprecated Use methods from idoit\Component\Helper\Ip
     */
    function isys_glob_is_valid_ip6($p_ip)
    {
        if (preg_match('/^[A-F0-9]{0,5}:[A-F0-9:]{1,39}$/i', $p_ip)) {
            $l_p = explode(':::', $p_ip);
            if (count($l_p) > 1) {
                return false;
            }

            $l_p = explode('::', $p_ip);
            if (count($l_p) > 2) {
                return false;
            }

            $l_p = explode(':', $p_ip);

            if (count($l_p) > 8) {
                return false;
            }

            foreach ($l_p as $l_checkPart) {
                if (strlen($l_checkPart) > 4) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}
