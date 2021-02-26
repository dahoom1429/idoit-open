<?php

namespace idoit\Component\Helper;

use isys_application;

/**
 * i-doit IP-Helper - formerly known as "isys_helper_ip".
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.7
 */
class Ip
{
    /**
     * Variable, used for calculating the virtual net ranges.
     *
     * @var  array
     */
    private static $m_virtual_net_range = []; // function

    /**
     * Calculates cidr suffix from subnetmask.
     *
     * @static
     *
     * @param   string $p_subnet_mask
     *
     * @return  integer
     * @author  Van Quyen Hoang<qhoang@synetics.de>
     */
    public static function calc_cidr_suffix($p_subnet_mask)
    {
        $l_subnet_arr = explode('.', $p_subnet_mask);
        $l_cidr_suffix = 0;
        $l_counter = 0;
        $l_max_bits = 8;

        foreach ($l_subnet_arr as $l_mask_part) {
            $l_mask_part = intval($l_mask_part);
            $l_zeros = "";

            if ($l_mask_part < 255 && $l_counter == 0) {
                $l_binary = decbin($l_mask_part);

                if (strlen($l_binary) < $l_max_bits) {
                    for ($i = strlen($l_binary);$i < $l_max_bits;$i++) {
                        $l_zeros .= '0';
                    }

                    $l_binary = $l_zeros . $l_binary;
                }

                if ($l_binary != '0') {
                    if ($l_pos = strpos($l_binary, '0')) {
                        if ($l_pos > 0) {
                            $l_binary = substr($l_binary, 0, $l_pos);
                            $l_cidr_suffix += strlen($l_binary);
                        } else {
                            $l_cidr_suffix = 1;
                        }
                    } else {
                        $l_cidr_suffix = 1;
                    }
                }
                continue;
            } else {
                $l_binary = decbin($l_mask_part);

                if (strlen($l_binary) < $l_max_bits) {
                    for ($i = strlen($l_binary);$i < $l_max_bits;$i++) {
                        $l_zeros .= '0';
                    }

                    $l_binary = $l_zeros . $l_binary;
                }

                if ($l_binary != '0') {
                    if ($l_pos = strpos($l_binary, '0')) {
                        $l_binary = substr($l_binary, 0, $l_pos);
                        $l_cidr_suffix += strlen($l_binary);
                    } elseif (strlen(ltrim($l_binary, '0')) < 8) {
                        continue;
                    } elseif (strlen($l_binary) == 8) {
                        $l_cidr_suffix += 8;
                    }
                }

                $l_counter++;
            }
        }

        return $l_cidr_suffix;
    }

    /**
     * Calculates cidr suffix for ipv6 from the specified subnetmask.
     *
     * @static
     *
     * @param   string $p_net_mask
     *
     * @return  integer
     * @author  Van Quyen Hoang<qhoang@synetics.de>
     */
    public static function calc_cidr_suffix_ipv6($p_net_mask)
    {
        $l_subnetmask = self::validate_subnetmask_ipv6($p_net_mask);
        $l_subnetmask_arr = explode(':', $l_subnetmask);
        $l_cidr_suffix = 0;

        foreach ($l_subnetmask_arr as $l_ip_part) {
            if ($l_ip_part == 'ffff') {
                $l_cidr_suffix += 16;
            } elseif ($l_ip_part != '0' && $l_ip_part != '0000' && $l_ip_part != '') {
                $l_dec = hexdec($l_ip_part);
                $l_bin = decbin($l_dec);
                $l_bin = rtrim($l_bin, '0');

                $l_cidr_suffix += strlen($l_bin);
            }
        }

        return $l_cidr_suffix;
    }

    /**
     * Calculates ip range.
     *
     * @param   string  $p_net_address
     * @param   string  $p_subnet_mask
     * @param   boolean $p_fullrange True: Returns the "full" IP-range, False: Returns the "available" IP-range (without net-address and broadcast).
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public static function calc_ip_range($p_net_address, $p_subnet_mask, $p_fullrange = false)
    {
        if (!self::validate_ip($p_net_address) || !self::validate_ip($p_subnet_mask)) {
            return null;
        }

        if ($p_subnet_mask === '255.255.255.255') {
            $p_fullrange = true;
        }

        $l_subnet_arr = explode('.', $p_subnet_mask);
        $l_net_address_arr = explode('.', $p_net_address);

        $l_net_address_binary[0] = decbin(intval($l_net_address_arr[0]));
        $l_net_address_binary[1] = decbin(intval($l_net_address_arr[1]));
        $l_net_address_binary[2] = decbin(intval($l_net_address_arr[2]));
        $l_net_address_binary[3] = decbin(intval($l_net_address_arr[3]));

        foreach ($l_net_address_binary as $l_key => $l_ip_binary) {
            if (strlen($l_ip_binary) < 8) {
                $l_zeros = '';
                while (strlen($l_zeros) < (8 - strlen($l_ip_binary))) {
                    $l_zeros .= '0';
                }

                $l_net_address_binary[$l_key] = $l_zeros . $l_ip_binary;
            }
        }

        return [
            'from' => bindec($l_net_address_binary[0] & decbin(intval($l_subnet_arr[0]))) . '.' . bindec($l_net_address_binary[1] & decbin(intval($l_subnet_arr[1]))) . '.' .
                bindec($l_net_address_binary[2] & decbin(intval($l_subnet_arr[2]))) . '.' .
                (bindec($l_net_address_binary[3] & decbin(intval($l_subnet_arr[3]))) + (!$p_fullrange ? 1 : 0)),
            'to'   => bindec($l_net_address_binary[0] | (substr(decbin(~intval($l_subnet_arr[0])), -8) & decbin(255))) . '.' .
                bindec($l_net_address_binary[1] | (substr(decbin(~intval($l_subnet_arr[1])), -8) & decbin(255))) . '.' .
                bindec($l_net_address_binary[2] | (substr(decbin(~intval($l_subnet_arr[2])), -8) & decbin(255))) . '.' .
                (bindec($l_net_address_binary[3] | (substr(decbin(~intval($l_subnet_arr[3])), -8) & decbin(255))) - (!$p_fullrange ? 1 : 0))
        ];
    }

    /**
     * Calculates IPv6 Range with the specified net address and cidr suffix.
     *
     * @param   string  $p_net_address
     * @param   integer $p_cidr_suffix
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public static function calc_ip_range_ipv6($p_net_address, $p_cidr_suffix)
    {
        $l_subnet_mask = self::calc_subnet_by_cidr_suffix_ipv6($p_cidr_suffix, true);

        $l_subnet_arr = explode(':', $l_subnet_mask);
        $l_net_address_arr = explode(':', self::validate_ipv6($p_net_address));
        $l_net_address_arr_binary = [];
        $l_subnet_address_arr_binary = [];

        foreach ($l_net_address_arr as $l_key => $l_ip_hex) {
            if (!self::validate_ip($l_ip_hex)) {
                $l_dec = hexdec($l_ip_hex);
                $l_bin = decbin($l_dec);
                $l_bin_lenght = strlen($l_bin);
                $l_zeros = '';

                while ($l_bin_lenght < 16) {
                    $l_zeros .= '0';
                    $l_bin_lenght++;
                }

                $l_net_address_arr_binary[$l_key] = $l_zeros . $l_bin;
            } else {
                $l_ip_arr = explode('.', $l_ip_hex);
                $l_counter = 1;

                $l_net_address_arr[$l_key] = '';

                foreach ($l_ip_arr as $l_ip_part) {
                    $l_bin = decbin($l_ip_part);
                    $l_bin_lenght = strlen($l_bin);
                    $l_zeros = '';

                    while ($l_bin_lenght < 8) {
                        $l_zeros .= '0';
                        $l_bin_lenght++;
                    }

                    $l_net_address_arr_binary[$l_key] .= $l_zeros . $l_bin;

                    if ($l_counter % 2 == 0) {
                        $l_key++;
                    }

                    $l_counter++;
                }
            }
        }

        foreach ($l_subnet_arr as $l_key => $l_ip_hex) {
            $l_bin = decbin(hexdec($l_ip_hex));
            $l_bin_lenght = strlen($l_bin);
            $l_zeros = '';

            while ($l_bin_lenght < 16) {
                $l_zeros .= '0';
                $l_bin_lenght++;
            }

            $l_bin = $l_zeros . $l_bin;
            $l_subnet_address_arr_binary[$l_key] = $l_bin;
        }

        $countNets = count($l_subnet_address_arr_binary);
        for ($i = 0;$i < $countNets;$i++) {
            $l_range_from[$i] = dechex(bindec($l_net_address_arr_binary[$i] & $l_subnet_address_arr_binary[$i]));
        }

        $l_range_from_string = implode(':', $l_range_from);

        for ($i = 0;$i < $countNets;$i++) {
            $l_bin = decbin((~hexdec($l_subnet_arr[$i]) & hexdec('ffff')));
            if ($l_bin == '0') {
                $l_bin = '0000000000000000';
            }

            $l_bin_lenght = strlen($l_bin);
            $l_zeros = '';
            while ($l_bin_lenght < 16) {
                $l_zeros .= '0';
                $l_bin_lenght++;
            }

            $l_bin = $l_zeros . $l_bin;
            $l_range_to[$i] = dechex(bindec($l_net_address_arr_binary[$i] | $l_bin));
        }

        $l_range_to_string = implode(':', $l_range_to);

        return [
            'from' => $l_range_from_string,
            'to'   => $l_range_to_string
        ];
    }

    /**
     * Method for calculating the given IPv6 address +1.
     *
     * @param   string $p_address
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function calculate_next_ipv6($p_address)
    {
        // If we got a shortened IPv6 address, create the "full lenght".
        if (strlen($p_address) < 39) {
            $p_address = self::validate_ip($p_address);
        }

        $l_found_on_key = 0;

        // We have to start with the last block.
        $l_pieces = array_reverse(explode(':', $p_address));

        for ($i = 0;$i < 8;$i++) {
            if (hexdec($l_pieces[$i]) < hexdec('ffff')) {
                $l_pieces[$i] = dechex(hexdec($l_pieces[$i]) + 1);

                if (strlen($l_pieces[$i]) < 4) {
                    $l_pieces[$i] = str_repeat('0', (4 - strlen($l_pieces[$i]))) . $l_pieces[$i];
                }

                $l_found_on_key = $i;

                break;
            }
        }

        for ($i = 0;$i < $l_found_on_key;$i++) {
            $l_pieces[$i] = '0000';
        }

        return implode(':', array_reverse($l_pieces));
    }

    /**
     * Method for calculating the given IPv6 address -1.
     *
     * @param   string $p_address
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function calculate_prev_ipv6($p_address)
    {
        // If we got a shortened IPv6 address, create the "full lenght".
        if (strlen($p_address) < 39) {
            $p_address = self::validate_ip($p_address);
        }

        $l_found_on_key = 0;

        // We have to start with the last block.
        $l_pieces = array_reverse(explode(':', $p_address));

        for ($i = 0;$i < 8;$i++) {
            if (hexdec($l_pieces[$i]) > 0) {
                $l_pieces[$i] = dechex(hexdec($l_pieces[$i]) - 1);

                if (strlen($l_pieces[$i]) < 4) {
                    $l_pieces[$i] = str_repeat('0', (4 - strlen($l_pieces[$i]))) . $l_pieces[$i];
                }

                $l_found_on_key = $i;

                break;
            }
        }

        for ($i = 0;$i < $l_found_on_key;$i++) {
            $l_pieces[$i] = 'ffff';
        }

        return implode(':', array_reverse($l_pieces));
    }

    /**
     * Calculates subnetmask by cidr suffix.
     *
     * @param   integer $p_cidr_suffix
     *
     * @return  string
     * @author  Van Quyen Hoang<qhoang@synetics.de>
     */
    public static function calc_subnet_by_cidr_suffix($p_cidr_suffix)
    {
        $l_rest = $p_cidr_suffix % 8;
        $l_full_bits = $p_cidr_suffix - $l_rest;
        $l_counter = 0;
        $l_max_bits = 7;
        $l_subnet = '';
        $l_value = 0;

        while ($l_full_bits >= 8) {
            $l_subnet .= '255.';
            $l_full_bits -= 8;
            $l_counter++;
        }

        $l_subnet = rtrim($l_subnet, '.');

        if ($l_rest > 0) {
            for ($i = $l_max_bits;$i >= 0;$i--) {
                if ($l_rest > 0) {
                    $l_value += pow(2, $i);
                    $l_rest--;
                }
            }

            $l_subnet .= '.' . $l_value;
            $l_counter++;
        }

        while ($l_counter <= 3) {
            $l_subnet .= '.0';
            $l_counter++;
        }

        return ltrim($l_subnet, '.');
    }

    /**
     * Calculate subnetmask for ipv6 from the specified cidr suffix.
     *
     * @param   integer $p_cidr_suffix
     * @param   boolean $p_full_mask
     *
     * @return  string
     * @author  Van Quyen Hoang<qhoang@synetics.de>
     */
    public static function calc_subnet_by_cidr_suffix_ipv6($p_cidr_suffix, $p_full_mask = false)
    {
        $l_bits = 16;
        $l_blocks_setted = floor($p_cidr_suffix / $l_bits);
        $l_rest = $p_cidr_suffix % $l_bits;
        $l_counter = 0;
        $l_subnetmask = '';

        for ($i = 0;$i < $l_blocks_setted;$i++) {
            $l_subnetmask .= 'ffff:';
        }

        $l_part = '';

        while ($l_counter < $l_bits && $l_blocks_setted < 8) {
            if ($l_counter < $l_rest) {
                $l_part .= '1';
            } else {
                $l_part .= '0';
            }

            $l_counter++;
        }

        if ($l_counter > 0) {
            $l_dec = bindec($l_part);
            if ($l_dec > 0) {
                $l_subnetmask .= dechex($l_dec) . ':';
                $l_blocks_setted++;
            }
        }

        if ($p_full_mask) {
            while ($l_blocks_setted < 8) {
                $l_subnetmask .= '0000:';
                $l_blocks_setted++;
            }

            $l_subnetmask = rtrim($l_subnetmask, ':');
        } else {
            if ($l_blocks_setted < 8) {
                $l_subnetmask .= ':';
            } else {
                $l_subnetmask = rtrim($l_subnetmask, ':');
            }
        }

        return $l_subnetmask;
    }

    /**
     * Converts ip address to numeric value.
     *
     * @param   string $p_ip_address
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function ip2long($p_ip_address)
    {
        if (empty($p_ip_address)) {
            return null;
        }

        $l_ip_arr = explode('.', $p_ip_address);

        return $l_ip_arr[0] * pow(256, 3) + $l_ip_arr[1] * pow(256, 2) + $l_ip_arr[2] * pow(256, 1) + $l_ip_arr[3] * pow(256, 0);
    }

    /**
     * Checks if an ip address in between the specified range.
     *
     * @param   mixed $p_ip
     * @param   mixed $p_range_from
     * @param   mixed $p_range_to
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public static function is_ip_in_range($p_ip, $p_range_from, $p_range_to)
    {
        if (strpos($p_ip, '.')) {
            $p_ip = self::ip2long($p_ip);
        }

        if (strpos($p_range_from, '.')) {
            $p_range_from = self::ip2long($p_range_from);
        }

        if (strpos($p_range_to, '.')) {
            $p_range_to = self::ip2long($p_range_to);
        }

        if ($p_ip >= $p_range_from && $p_ip <= $p_range_to) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if a ipv6 Address is inside the specified ipv6 range.
     *
     * @param   string $p_address
     * @param   string $p_from
     * @param   string $p_to
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function is_ipv6_in_range($p_address, $p_from, $p_to)
    {
        $p_address = self::validate_ipv6($p_address);
        $p_from = self::validate_ipv6($p_from);
        $p_to = self::validate_ipv6($p_to);

        return (strcmp($p_address, $p_from) >= 0 && strcmp($p_address, $p_to) <= 0);
    }

    /**
     * Converts numeric value to ip address.
     *
     * @static
     *
     * @param   integer $p_iplong
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function long2ip($p_iplong)
    {
        if ($p_iplong < 1) {
            return '0.0.0.0';
        }

        if (empty($p_iplong)) {
            return null;
        }

        if (is_string($p_iplong)) {
            $p_iplong = floatval($p_iplong);
        }

        $l_ip1 = floor($p_iplong / pow(256, 3));

        $l_ip2 = floor(($p_iplong % pow(256, 3)) / pow(256, 2));
        $l_ip2 = ($l_ip2 < 0) ? $l_ip2 + 256 : $l_ip2;

        $l_ip3 = floor((($p_iplong % pow(256, 3)) % pow(256, 2)) / pow(256, 1));
        $l_ip3 = ($l_ip3 < 0) ? $l_ip3 + 256 : $l_ip3;

        $l_ip4 = floor(((($p_iplong % pow(256, 3)) % pow(256, 2)) % pow(256, 1)) / pow(256, 0));
        $l_ip4 = ($l_ip4 < 0) ? $l_ip4 + 256 : $l_ip4;

        return $l_ip1 . '.' . $l_ip2 . '.' . $l_ip3 . '.' . $l_ip4;
    }

    /**
     * Sets an outer ranged ip inside the specified ip range.
     *
     * @param   string $p_ip
     * @param   string $p_range_from
     * @param   string $p_range_to
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public static function set_ip_in_range($p_ip, $p_range_from, $p_range_to)
    {
        $l_ip_arr = explode('.', $p_ip);
        $l_from_arr = explode('.', $p_range_from);
        $l_to_arr = explode('.', $p_range_to);
        $l_new_ip_string = '';
        $l_counter = 0;

        foreach ($l_ip_arr as $l_key => $l_ip_part) {
            $l_counter++;

            if (($l_ip_part >= $l_from_arr[$l_key] && $l_ip_part <= $l_to_arr[$l_key])) {
                $l_new_ip_string .= $l_ip_part . '.';
            } elseif ($l_ip_part < $l_from_arr[$l_key]) {
                $l_new_ip_string .= $l_from_arr[$l_key] . '.';
                break;
            } elseif ($l_ip_part > $l_to_arr[$l_key]) {
                $l_new_ip_string .= $l_to_arr[$l_key] . '.';
                break;
            }
        }

        while ($l_counter < count($l_ip_arr)) {
            $l_new_ip_string .= $l_ip_arr[$l_counter] . '.';
            $l_counter++;
        }

        return rtrim($l_new_ip_string, '.');
    }

    /**
     * Checks if the specified ip is valid.
     *
     * @param   string $p_ip
     *
     * @return  boolean
     * @author  Van Quyen Hoang<qhoang@synetics.de>
     */
    public static function validate_ip($p_ip)
    {
        if (empty($p_ip) || substr_count($p_ip, '.') !== 3) {
            return false;
        }

        $l_ip_arr = $p_ip;

        if (is_string($p_ip)) {
            $l_ip_arr = explode('.', $p_ip);
        }
        if (!is_array($l_ip_arr)) {
            return false;
        }

        if (count($l_ip_arr) < 4) {
            return false;
        }

        foreach ($l_ip_arr as $l_ip_part) {
            if (!is_numeric($l_ip_part)) {
                return false;
            }

            if (intval($l_ip_part) < 0 || intval($l_ip_part) > 255) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validates ipv6 address.
     *
     * @param   string  $p_address
     * @param   boolean $p_short_view
     *
     * @return  string
     * @author  Van Quyen Hoang<qhoang@synetics.de>
     */
    public static function validate_ipv6($p_address, $p_short_view = false)
    {
        if (is_string($p_address) && $p_address) {
            if (strpos($p_address, '::') || substr($p_address, 0, 2) == '::') {
                $l_address_arr = explode(':', $p_address);
                $l_zeros = (substr($p_address, 0, 2) == '::') ? '0000:' : ((substr($p_address, strlen($p_address) - 2, 2) == '::') ? '0000:' : '');

                if (count($l_address_arr) < 8) {
                    $l_add = 8 - count($l_address_arr);

                    if (self::validate_ip($l_address_arr[count($l_address_arr) - 1])) {
                        $l_add = $l_add - 1;
                    }

                    for ($i = 0;$i <= $l_add;$i++) {
                        $l_zeros .= '0000:';
                    }
                } elseif (count($l_address_arr) == 8 && $l_zeros != '') {
                    $l_zeros = '';
                    foreach ($l_address_arr as $l_ip_part) {
                        if ($l_ip_part == '') {
                            $l_zeros .= '0000:';
                        }
                    }
                }

                $l_address_begin = (($p_address != '::') ? substr($p_address, 0, strpos($p_address, '::')) : '0000');
                $l_address_end = substr($p_address, (strpos($p_address, '::') + 2), strlen($p_address));

                $l_new_address = trim($l_address_begin . ':' . $l_zeros . $l_address_end, ':');
            } else {
                $l_new_address = $p_address;
            }

            if (!$p_short_view) {
                $l_address_arr = explode(':', $l_new_address);
                foreach ($l_address_arr as $l_key => $l_part) {
                    $l_address_arr[$l_key] = str_pad($l_part, 4, '0', STR_PAD_LEFT);
                }
                $l_new_address = implode(':', $l_address_arr);
            } else {
                $l_address_arr = explode(':', $l_new_address);
                $l_all_null = [];

                foreach ($l_address_arr as $l_key => $l_part) {
                    $l_address_arr[$l_key] = ltrim($l_part, '0');
                    if (empty($l_address_arr[$l_key])) {
                        $l_address_arr[$l_key] = '0';
                    }

                    if ($l_part == '0' || $l_part == '0000') {
                        $l_all_null[] = $l_key;
                    }
                }

                if (count($l_all_null) > 1) {
                    $l_candidates = [];

                    foreach ($l_all_null as $l_key => $l_part) {
                        $l_puffer = $l_part;
                        $countNull = count($l_all_null);
                        for ($i = $l_key + 1;$i < $countNull;$i++) {
                            if (empty($l_all_null[$i])) {
                                break;
                            }

                            $l_puffer = $l_puffer + 1;

                            if ($l_puffer != $l_all_null[$i]) {
                                continue;
                            } else {
                                $l_candidates[$l_part][] = $l_all_null[$i];
                            }
                        }
                    }

                    if (count($l_candidates) > 0) {
                        foreach ($l_candidates as $l_key => $l_part) {
                            if (empty($l_index)) {
                                $l_index = $l_key;
                            } else {
                                if (is_countable($l_part) && is_countable($l_candidates[$l_index]) && count($l_part) > count($l_candidates[$l_index])) {
                                    $l_index = $l_key;
                                }
                            }
                        }
                        array_unshift($l_candidates[$l_index], $l_index);
                        foreach ($l_candidates[$l_index] as $l_unset_block) {
                            $l_address_arr[$l_unset_block] = '_';
                        }
                    }
                    $l_new_address = implode(':', $l_address_arr);

                    if (in_array('_', $l_address_arr)) {
                        $l_start_pos = strpos($l_new_address, '_');
                        $l_end_pos = strrpos($l_new_address, '_');

                        if ($l_start_pos && !($l_address_begin = substr($l_new_address, 0, $l_start_pos))) {
                            $l_address_begin = ':';
                        }

                        if ($l_end_pos && !($l_address_end = substr($l_new_address, $l_end_pos + 1, strlen($l_new_address)))) {
                            $l_address_end = ':';
                        }

                        if ($l_address_begin && $l_address_end) {
                            $l_new_address = $l_address_begin . $l_address_end;
                        }
                    }
                } else {
                    $l_new_address = implode(':', $l_address_arr);
                }
            }

            return filter_var($l_new_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        }

        return false;
    }

    /**
     * Validates net ip.
     *
     * @param   string  $p_net_ip
     * @param   string  $p_subnetmask
     * @param   integer $p_cidr_suffix
     * @param   boolean $p_next_net_ip
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public static function validate_net_ip($p_net_ip, $p_subnetmask = null, $p_cidr_suffix = null, $p_next_net_ip = false)
    {
        if (empty($p_net_ip)) {
            return null;
        } elseif ($p_subnetmask !== null) {
            $l_subnetmask = $p_subnetmask;
        } elseif ($p_cidr_suffix !== null) {
            $l_subnetmask = self::calc_subnet_by_cidr_suffix($p_cidr_suffix);
        } else {
            return false;
        }

        if (self::validate_subnetmask($l_subnetmask)) {
            $l_range = self::calc_ip_range($p_net_ip, $l_subnetmask);

            // @See ID-6544 if from equals to then we have a net which is also the host and we have a subnetmask 255.255.255.255
            $l_calculated_net_ip_long = ($l_range['from'] === $l_range['to']) ? self::ip2long($l_range['from']) : self::ip2long($l_range['from']) - 1;
            $l_calculated_net_ip = self::long2ip($l_calculated_net_ip_long);

            if ($l_calculated_net_ip != $p_net_ip) {
                if ($p_next_net_ip) {
                    return $l_calculated_net_ip;
                } else {
                    return false;
                }
            } else {
                if ($p_next_net_ip) {
                    return $p_net_ip;
                } else {
                    return true;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * Validates ipv6 net address and returns correct ipv6 net address.
     *
     * @param   string  $p_address
     * @param   integer $p_cidr_suffix
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public static function validate_net_ipv6($p_address, $p_cidr_suffix)
    {
        $l_ip_address = self::validate_ipv6($p_address);
        $l_ip_range = self::calc_ip_range_ipv6($l_ip_address, $p_cidr_suffix);

        if ($l_ip_range['from'] != $l_ip_address) {
            $l_ip_address = $l_ip_range['from'];
        }

        return $l_ip_address;
    }

    /**
     * Validates subnetmask and returns the next appropiate mask.
     *
     * @param   string  $p_net_mask
     * @param   boolean $p_get_next_mask
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public static function validate_subnetmask($p_net_mask, $p_get_next_mask = false)
    {
        if (empty($p_net_mask)) {
            return null;
        }

        $l_mask_arr = explode('.', $p_net_mask);
        $l_cidr_suffix = self::calc_cidr_suffix($p_net_mask);

        if (intval($l_mask_arr[1]) > 0 && $l_mask_arr[0] != '255' && !$p_get_next_mask) {
            return false;
        } elseif (intval($l_mask_arr[2]) > 0 && $l_mask_arr[1] != '255' && !$p_get_next_mask) {
            return false;
        } elseif (intval($l_mask_arr[3]) > 0 && $l_mask_arr[2] != '255' && !$p_get_next_mask) {
            return false;
        }

        return self::calc_subnet_by_cidr_suffix($l_cidr_suffix);
    }

    /**
     * Validates subnetmask of ipv6 ip and returns correct ip.
     *
     * @static
     *
     * @param   string $p_ipv6_address
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public static function validate_subnetmask_ipv6($p_ipv6_address)
    {
        $l_address = explode(':', $p_ipv6_address);
        $l_counter = 0;
        $l_new_address = '';
        $l_set_zero = false;

        foreach ($l_address as $l_ip_part) {
            $l_dec = hexdec($l_ip_part);
            if ($l_dec == 65535) {
                $l_new_address .= 'ffff:';
            } elseif (!$l_set_zero && strlen($l_ip_part) > 0) {
                $l_bin = decbin($l_dec);
                $l_bit = 4;

                if (strpos($l_bin, '0')) {
                    $l_bin = substr($l_bin, 0, strpos($l_bin, '0'));
                }

                while (strlen($l_bin) > $l_bit) {
                    $l_bit += $l_bit;
                }

                while (strlen($l_bin) < 16) {
                    $l_bin .= '0';
                }

                $l_new_address .= dechex(bindec($l_bin)) . ':';
                $l_set_zero = true;
            } elseif ($l_set_zero) {
                $l_new_address .= '0000:';
            }

            $l_counter++;
        }

        while ($l_counter < 8) {
            $l_new_address .= '0000:';
            $l_counter++;
        }

        return rtrim($l_new_address, ':');
    }

    /**
     * Instance method for resetting the virtual supernet range calculator.
     *
     * @param   integer $p_from
     * @param   integer $p_to
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function virtual_supernet_range_instance($p_from, $p_to)
    {
        return self::$m_virtual_net_range = [
            'from'    => $p_from,
            'to'      => $p_to,
            'subnets' => []
        ];
    }

    /**
     * Method for adding a new subnet to the virtual supernet range calculator.
     *
     * @param   string $p_from
     * @param   string $p_to
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function add_subnet($p_from, $p_to)
    {
        $p_from = self::ip2long($p_from);
        $p_to = self::ip2long($p_to);
        $l_add = true;

        if (is_array(self::$m_virtual_net_range['subnets'])) {
            foreach (self::$m_virtual_net_range['subnets'] as &$l_subnet) {
                if ($l_subnet['from'] <= $p_from && $p_to <= $l_subnet['to']) {
                    // The given subnet lies within the range of another subnet, skip.
                    $l_add = false;
                    break;
                }

                if (($l_subnet['from'] <= $p_from && $p_from <= $l_subnet['to']) && $l_subnet['to'] <= $p_to) {
                    // The given subnet overlaps the range of another subnet, merge.
                    $l_subnet['to'] = $p_to;
                    $l_add = false;
                    break;
                }

                if ($p_from <= $l_subnet['from'] && ($l_subnet['from'] <= $p_to && $p_to <= $l_subnet['to'])) {
                    // The given subnet overlaps the range of another subnet, merge.
                    $l_subnet['from'] = $p_from;
                    $l_add = false;
                    break;
                }

                if ($p_from <= $l_subnet['from'] && $l_subnet['to'] <= $p_to) {
                    // The given subnet overlaps the complete range of another subnet, merge.
                    $l_subnet['from'] = $p_from;
                    $l_subnet['to'] = $p_to;
                    $l_add = false;
                    break;
                }
            }
        }

        if ($l_add) {
            self::$m_virtual_net_range['subnets'][] = [
                'from' => $p_from,
                'to'   => $p_to
            ];
        }
    }

    /**
     * Retrieves an array of available ranges, between the given nets.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_free_ranges_in_virtual_supernet()
    {
        $l_return = [];

        if (is_countable(self::$m_virtual_net_range['subnets']) && count(self::$m_virtual_net_range['subnets']) > 0) {
            $l_last_ip = self::$m_virtual_net_range['from'] - 1;

            foreach (self::$m_virtual_net_range['subnets'] as $l_subnet) {
                if ($l_last_ip < ($l_subnet['from'] - 1)) {
                    $l_return[] = [
                        'from' => $l_last_ip,
                        'to'   => $l_subnet['from'] - 1
                    ];
                }

                $l_last_ip = $l_subnet['to'] + 1;
            }

            if ($l_last_ip < self::$m_virtual_net_range['to']) {
                $l_return[] = [
                    'from' => $l_last_ip,
                    'to'   => self::$m_virtual_net_range['to'] + 1
                ];
            }

            return $l_return;
        }

        return [self::$m_virtual_net_range];
    }

    /**
     * Method for doing a nslookup via console. It will return a ip address or false.
     *
     * @param   string $p_hostname
     * @param   array  $p_dns_server
     *
     * @return  mixed
     * @throws  \isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function nslookup($p_hostname, array $p_dns_server = [])
    {
        if (empty($p_hostname)) {
            return false;
        }

        $l_return = [];

        $l_nslookup = system_which('nslookup');

        if ($l_nslookup === false) {
            throw new \isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATS__NET_IP_ADDRESSES__NOTIFY__NSLOOKUP_NOT_FOUND'));
        }

        $l_command = escapeshellarg($l_nslookup) . ' -type=A ' . escapeshellarg($p_hostname);

        if (!is_countable($p_dns_server) || !count($p_dns_server)) {
            $p_dns_server[] = '';
        }

        foreach ($p_dns_server as $l_dns) {
            // Empty the output and execute our command!
            $l_output = [];

            exec($l_command . ' ' . (!empty($l_dns) ? escapeshellarg($l_dns) : ''), $l_output, $l_return_val);

            $l_output = array_filter($l_output);

            if ($l_return_val != 0 || count($l_output) < 4) {
                // The return value was not zero or the output was not enough.
                continue;
            }

            $l_ip = trim(end(explode(':', end($l_output))));

            if (!static::validate_ip($l_ip) && !static::validate_ipv6($l_ip)) {
                // The found string is no IPv4 or IPv6 address.
                continue;
            }

            if (!isset($l_return[$l_ip])) {
                $l_return[$l_ip] = 0;
            }

            $l_return[$l_ip]++;
        }

        if (!count($l_return)) {
            return false;
        }

        arsort($l_return);

        return array_shift(array_keys($l_return));
    }

    /**
     * Method for doing a reverse nslookup via console. It will return a hostname or false.
     *
     * @param   string $p_ip
     *
     * @return  mixed
     * @throws  \isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function reverse_nslookup($p_ip)
    {
        if (empty($p_ip)) {
            return false;
        }

        $l_nslookup = system_which('nslookup');

        if ($l_nslookup === false) {
            throw new \isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATS__NET_IP_ADDRESSES__NOTIFY__NSLOOKUP_NOT_FOUND'));
        }

        $l_command = escapeshellarg($l_nslookup) . ' -type=A ' . escapeshellarg($p_ip);

        // Empty the output and execute our command!
        $l_output = [];

        exec($l_command, $l_output, $l_return_val);

        $l_output = array_filter($l_output);

        if ($l_return_val != 0 || count($l_output) < 3) {
            // The return value was not zero or the output was not enough.
            return false;
        }

        foreach ($l_output as $l_line) {
            if (preg_match('~([\s.]*?)[Nn]ame(\s*)[:=](\s*)(.*)~', trim($l_line), $l_matches)) {
                return trim(trim(end($l_matches)), '.');
            }
        }

        return false;
    }

    /**
     * Method for pinging multiple hosts via nmap.
     *
     * @param   string $p_ip_from
     * @param   string $p_ip_to
     *
     * @return  array
     * @throws  \isys_exception_general
     * @throws \Exception
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function nmap_ping($p_ip_from, $p_ip_to = null)
    {
        $l_return = [];
        $l_nmap = system_which('nmap');

        if ($l_nmap === false) {
            throw new \isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATS__NET_IP_ADDRESSES__NOTIFY__NMAP_NOT_FOUND'));
        }

        $l_ip_address = $p_ip_from;

        // Here we create the IP-range syntax "192.168.10-11.0-255"
        if ($p_ip_to !== null) {
            $l_ip_from = explode('.', $l_ip_address);
            $p_ip_to = explode('.', $p_ip_to);
            $l_ip_address = [];

            for ($i = 0;$i <= 3;$i++) {
                if ($l_ip_from[$i] == $p_ip_to[$i]) {
                    $l_ip_address[] = $l_ip_from[$i];
                } else {
                    $l_ip_address[] = $l_ip_from[$i] . '-' . $p_ip_to[$i];
                }
            }

            $l_ip_address = implode('.', $l_ip_address);
        }

        $l_nmap_option = \isys_tenantsettings::get('cmdb.ip-list.nmap-parameter', null);

        if ($l_nmap_option !== 'sP' && $l_nmap_option !== 'PE') {
            $l_nmap_option = 'sP';
        }

        // Build command string
        $commandString =  escapeshellarg($l_nmap) . ' -' . $l_nmap_option . ' ' . escapeshellarg($l_ip_address);

        // Execute command
        exec($commandString, $l_output, $statusCode);

        // Validate status code
        if ($statusCode !== 0) {
            throw new \Exception('Command '. $commandString . ' failed with status code '. $statusCode);
        }

        foreach ($l_output as $l_line) {
            if (preg_match('~\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}~', $l_line, $l_match)) {
                $l_return[$l_match[0]] = true;
            }
        }

        return $l_return;
    }

    /**
     * Method for pinging multiple hosts via fping.
     *
     * @param   string $ipFrom
     * @param   string $ipTo
     *
     * @return  array
     * @throws  \isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function fping_ping($ipFrom, $ipTo = null)
    {
        $l_return = [];
        $l_fping = system_which('fping');

        if ($l_fping === false) {
            throw new \isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATS__NET_IP_ADDRESSES__NOTIFY__FPING_NOT_FOUND'));
        }

        $ipFrom = escapeshellarg($ipFrom);

        if ($ipTo !== null) {
            $ipTo = escapeshellarg($ipTo);
        } else {
            $ipTo = $ipFrom;
        }

        exec(escapeshellarg($l_fping) . ' -gaq ' . $ipFrom . ' ' . $ipTo, $l_output);

        foreach ($l_output as $l_line) {
            if (preg_match('~\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}~', $l_line, $l_match)) {
                $l_return[$l_match[0]] = true;
            }
        }

        return $l_return;
    }

    /**
     * @param string $ipv6Address
     * @return array
     */
    public static function parseHexIpv6(string $ipv6Address): array
    {
        $addressParts = explode(':', $ipv6Address);
        $ipv6Array = [];
        for ($i=0; $i<8; $i++) {
            $ipv6Array[] = hexdec($addressParts[$i]);
        }
        return $ipv6Array;
    }

    /**
     * @param array $supernet_range_longs
     * @param array $subnet_range_longs
     * @return bool
     */
    public static function checkSubnetInRange(array $supernet_range_longs, array $subnet_range_longs): bool
    {
        for ($i=0; $i<8; $i++) {
            if (!(($supernet_range_longs['from'][$i] <= $subnet_range_longs['from'][$i]) && ($supernet_range_longs['to'][$i] >= $subnet_range_longs['to'][$i]))) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $ipv6Address
     * @param array  $subnet_range_longs
     *
     * @return bool
     */
    public static function checkIPv6InRange(string $ipv6Address, array $subnet_range_longs): bool
    {
        $ipv6Address = self::validate_ipv6($ipv6Address);
        if (!$ipv6Address) {
            return false;
        }
        $ipv6Array = self::parseHexIpv6($ipv6Address);
        for ($i=0; $i<8; $i++) {
            if (!(($subnet_range_longs['from'][$i] <= $ipv6Array[$i]) && ($subnet_range_longs['to'][$i] >= $ipv6Array[$i]))) {
                return false;
            }
        }
        return true;
    }
}
