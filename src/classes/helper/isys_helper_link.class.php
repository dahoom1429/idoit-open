<?php

/**
 * i-doit
 *
 * Helper methods for all sorts of links.
 *
 * @package     i-doit
 * @subpackage  Helper
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.1
 */
class isys_helper_link
{
    /**
     * Static method for retrieving the base URL to the local i-doit installation
     *
     * @static
     *
     * @param  boolean $p_force_https
     *
     * @return string  For example "http://localhost/idoit-pro/" or "https://192.168.10.93/"...
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public static function get_base($p_force_https = false)
    {
        // @see  ID-4952  It is now possible to define a i-doit URL in the settings.
        if ('' !== ($systemBase = isys_tenantsettings::get('system.base.uri'))) {
            $url = rtrim($systemBase, '/') . '/';

            // Check whether protocol is setted
            if (strpos($url, '://') === false) {
                // Append protocol to url
                $url = 'http://' . $url;
            }

            return ($p_force_https ? str_replace('http://', 'https://', $url) : $url);
        }

        global $g_config;

        $host = $_SERVER['HTTP_HOST'];
        if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        } elseif (isset($_SERVER['HTTP_ORIGIN']) && strpos($_SERVER['HTTP_ORIGIN'], '//')) {
            $host = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST);
        }

        $l_https = $p_force_https || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');

        return 'http' . ($l_https ? 's' : '') . '://' . $host . $g_config['www_dir'];
    }

    /**
     * Static method for retrieving an absolute or relative URL, created by the given params.
     *
     * @static
     *
     * @param  array   $p_params
     * @param  boolean $p_absolute
     *
     * @return string
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public static function create_url(array $p_params = [], $p_absolute = false)
    {
        // Because "http_build_query" uses something like "urlencode" we need to decode the strings for smarty variables.
        return ($p_absolute ? self::get_base() : '') . '?' . str_replace(
            ['%5B', '%5D', '%7B', '%7D', '%25', '%3A'],
            ['[', ']', '{', '}', '%', ':'],
            http_build_query($p_params, null, '&')
        );
    }

    /**
     * Static method for creating a link to a global category. Use this method for failsafe linking!
     *
     * @static
     *
     * @param  array   $p_params
     * @param  boolean $p_absolute
     *
     * @return string
     * @throws isys_exception_cmdb
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public static function create_catg_url(array $p_params = [], $p_absolute = false)
    {
        $l_mandatories = [
            C__CMDB__GET__OBJECT,
            C__CMDB__GET__CATG
        ];

        self::check_params($l_mandatories, $p_params);

        return self::create_url($p_params, $p_absolute);
    }

    /**
     * Static method for creating a link to the item of a global category. Use this method for failsafe linking!
     *
     * @static
     *
     * @param  array   $p_params
     * @param  boolean $p_absolute
     *
     * @return string
     * @throws isys_exception_cmdb
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public static function create_catg_item_url(array $p_params = [], $p_absolute = false)
    {
        $l_mandatories = [
            C__CMDB__GET__OBJECT,
            C__CMDB__GET__CATG,
            C__CMDB__GET__CATLEVEL
        ];

        self::check_params($l_mandatories, $p_params);

        return self::create_url($p_params, $p_absolute);
    }

    /**
     * Static method for creating a link to a specific category. Use this method for failsafe linking!
     *
     * @static
     *
     * @param  array   $p_params
     * @param  boolean $p_absolute
     *
     * @return string
     * @throws isys_exception_cmdb
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public static function create_cats_url(array $p_params = [], $p_absolute = false)
    {
        $l_mandatories = [
            C__CMDB__GET__OBJECT,
            C__CMDB__GET__CATS
        ];

        self::check_params($l_mandatories, $p_params);

        return self::create_url($p_params, $p_absolute);
    }

    /**
     * Static method for creating a link to the item of a specific category. Use this method for failsafe linking!
     *
     * @static
     *
     * @param  array   $p_params
     * @param  boolean $p_absolute
     *
     * @return string
     * @throws isys_exception_cmdb
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public static function create_cats_item_url(array $p_params = [], $p_absolute = false)
    {
        $l_mandatories = [
            C__CMDB__GET__OBJECT,
            C__CMDB__GET__CATS,
            C__CMDB__GET__CATLEVEL
        ];

        self::check_params($l_mandatories, $p_params);

        return self::create_url($p_params, $p_absolute);
    }

    /**
     * Static method for creating a link to a global category. Use this method for failsafe linking!
     *
     * @static
     *
     * @param  array   $p_params
     * @param  boolean $p_absolute
     *
     * @return string
     * @throws isys_exception_cmdb
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public static function create_catg_custom_url(array $p_params = [], $p_absolute = false)
    {
        $l_mandatories = [
            C__CMDB__GET__OBJECT,
            C__CMDB__GET__CATG_CUSTOM
        ];

        $p_params[C__CMDB__GET__CATG] = defined_or_default('C__CATG__CUSTOM_FIELDS');

        self::check_params($l_mandatories, $p_params);

        return self::create_url($p_params, $p_absolute);
    }

    /**
     * Static method for creating a link to the item of a global category. Use this method for failsafe linking!
     *
     * @static
     *
     * @param  array   $p_params
     * @param  boolean $p_absolute
     *
     * @return string
     * @throws isys_exception_cmdb
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public static function create_catg_custom_item_url(array $p_params = [], $p_absolute = false)
    {
        $l_mandatories = [
            C__CMDB__GET__OBJECT,
            C__CMDB__GET__CATG_CUSTOM,
            C__CMDB__GET__CATLEVEL
        ];

        $p_params[C__CMDB__GET__CATG] = defined_or_default('C__CATG__CUSTOM_FIELDS');

        self::check_params($l_mandatories, $p_params);

        return self::create_url($p_params, $p_absolute);
    }

    /**
     * Static method for creating a link to an file object and display it's image. If the file object is no image, nothing will be shown!
     *
     * @static
     *
     * @param   integer $p_obj_id
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function create_image_url($p_obj_id)
    {
        $l_params = [
            C__CMDB__GET__OBJECT => $p_obj_id,
            C__CMDB__GET__CATS   => defined_or_default('C__CATS__FILE'),
            'load_img'           => 1
        ];

        return self::create_url($l_params, true);
    }

    /**
     * Static method for removing one or more parameters from the given URL.
     *
     * @static
     *
     * @param   string $p_url
     * @param   mixed  $p_params May be a string, or a array of strings.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function remove_params_from_url($p_url, $p_params)
    {
        list($l_base, $l_path) = explode('?', $p_url);

        // This function works like "http_build_query()" - but backwards.
        parse_str($l_path, $l_path_array);

        if (empty($l_path_array)) {
            return $l_base;
        }

        // If we get a simple string, we just pack this inside an array for the next step.
        if (is_string($p_params)) {
            $p_params = [$p_params];
        }

        foreach ($p_params as $l_param) {
            if (array_key_exists($l_param, $l_path_array)) {
                unset($l_path_array[$l_param]);
            }
        }

        return $l_base . self::create_url($l_path_array);
    }

    /**
     * Static method for adding parameters to the given URL.
     * This method will also overwrite existing parameters, which shall be added again.
     *
     * @static
     *
     * @param   string $p_url
     * @param   array  $p_params Should hold keys and values for the new params.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function add_params_to_url($p_url, array $p_params)
    {
        list($l_base, $l_path) = explode('?', $p_url);

        parse_str($l_path, $l_path_array);

        return $l_base . self::create_url(array_merge($l_path_array, $p_params));
    }

    /**
     * Static method for rendering a "mailto" link.
     *
     * @static
     *
     * @param   mixed $p_address May be a string or a array of strings.
     * @param   array $p_params  Array for optional parameters
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function create_mailto($p_address, array $p_params = [])
    {
        $p_address = (is_array($p_address) ? implode(',', $p_address) : $p_address);

        $l_additional = [];

        foreach ($p_params as $l_param => $l_value) {
            $l_additional[] = $l_param . '=' . str_replace(' ', '%20', $l_value);
        }

        return 'mailto:' . $p_address . '?' . implode('&', $l_additional);
    }

    /**
     * Static method for handling URL variables (used in category "access", the QR codes, ...).
     *
     * @static
     *
     * @param  string  $p_url
     * @param  integer $p_obj_id
     * @param  array   $p_variable_whitelist Array of allowed variables (see "isys_helper_link::get_url_variables()" for names).
     *
     * @return string
     * @throws isys_exception_database
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public static function handle_url_variables($p_url, $p_obj_id, array $p_variable_whitelist = [])
    {
        if (!$p_obj_id) {
            return $p_url;
        }

        $l_replace_pairs = self::get_url_variables($p_obj_id);

        if (strpos(' ' . $p_url, '%ipaddress#')) {
            preg_match_all("/\%ipaddress\#\d*\%/", $p_url, $l_matches);
            if (isset($l_matches[0])) {
                $l_data = isys_cmdb_dao_category_data::initialize($p_obj_id)
                    ->path('C__CATG__IP')
                    ->data()
                    ->pluck('hostaddress')
                    ->toArray();

                foreach ($l_matches[0] as $l_key => $l_match) {
                    $l_pos = ((int)substr($l_match, strpos($l_match, '#') + 1, -1) - 1);
                    if (isset($l_data[$l_pos])) {
                        $l_replace_pairs['%ipaddress#' . ($l_pos + 1) . '%'] = $l_data[$l_pos];
                    }
                }
                isys_cmdb_dao_category_data::free($p_obj_id);
            }
        }

        // If we received a set of whitelisted variables, we remove the "not-whitelisted" ones from the array.
        if (count($p_variable_whitelist) > 0) {
            $l_replace_pairs = array_intersect_key($l_replace_pairs, array_flip($p_variable_whitelist));
        }

        return strtr($p_url, $l_replace_pairs);
    }

    /**
     * Static method for retrieving all URL variables (including values);
     *
     * @static
     *
     * @param  integer $p_obj_id
     *
     * @return array
     * @throws isys_exception_database
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public static function get_url_variables($p_obj_id)
    {
        $db = isys_application::instance()->container->get('database');
        $locales = isys_application::instance()->container->get('locales');

        $l_dao_ip = isys_cmdb_dao_category_g_ip::instance($db);
        $l_primary_ip_data = $l_dao_ip->get_primary_ip($p_obj_id)
            ->get_row();

        $l_res_ips = $l_dao_ip->get_ips_by_obj_id($p_obj_id);
        $l_counter = 0;
        $l_ip_arr = [];
        while ($l_row_ip = $l_res_ips->get_row()) {
            $l_ip_arr[] = $l_row_ip['isys_cats_net_ip_addresses_list__title'];
            if ($l_counter == 1) {
                break;
            }

            $l_counter++;
        }

        $l_obj_data = $l_dao_ip->get_object_by_id($p_obj_id)
            ->get_row();
        $l_model_data = isys_cmdb_dao_category_g_model::instance($db)
            ->get_data(null, $p_obj_id)
            ->get_row();
        $l_accounting_data = isys_cmdb_dao_category_g_accounting::instance($db)
            ->get_data(null, $p_obj_id)
            ->get_row();
        $l_location_parent = isys_cmdb_dao_category_g_location::instance($db)
            ->get_data(null, $p_obj_id)
            ->get_row_value('isys_catg_location_list__parentid');
        $l_location = isys_cmdb_dao::instance($db)
            ->get_object_by_id($l_location_parent)
            ->get_row_value('isys_obj__title');

        $l_browser = new isys_popup_browser_location();
        $l_browser->set_format_exclude_self(true)
            ->set_format_as_text(true);
        $l_location_path = $l_browser->format_selection($p_obj_id);

        return [
            '%idoit_host%'                     => rtrim(self::get_base(), '/'),
            '%hostname%'                       => $l_primary_ip_data['isys_catg_ip_list__hostname'],
            '%date_acquirement%'               => $locales->fmt_date($l_accounting_data['isys_catg_accounting_list__acquirementdate']),
            '%date_created%'                   => $locales->fmt_date($l_obj_data['isys_obj__created']),
            '%date_created_raw%'               => $l_obj_data['isys_obj__created'],
            '%date_changed%'                   => $locales->fmt_date($l_obj_data['isys_obj__updated']),
            '%date_changed_raw%'               => $l_obj_data['isys_obj__updated'],
            '%ipaddress%'                      => $l_primary_ip_data['isys_cats_net_ip_addresses_list__title'],
            '%ipaddress#1%'                    => $l_ip_arr[0],
            '%ipaddress#2%'                    => $l_ip_arr[1],
            '%objectname%'                     => $l_obj_data['isys_obj__title'],
            '%objectname_lowercase%'           => strtolower($l_obj_data['isys_obj__title']),
            '%objectname_uppercase%'           => strtoupper($l_obj_data['isys_obj__title']),
            '%objectname_formatted%'           => self::format_url_param($l_obj_data['isys_obj__title']),
            '%objectname_lowercase_formatted%' => self::format_url_param(strtolower($l_obj_data['isys_obj__title'])),
            '%objectname_uppercase_formatted%' => self::format_url_param(strtoupper($l_obj_data['isys_obj__title'])),
            '%object_type%'                    => isys_application::instance()->container->get('language')->get($l_obj_data['isys_obj_type__title']),
            '%serial_no%'                      => $l_model_data['isys_catg_model_list__serial'],
            '%model_title%'                    => $l_model_data['isys_model_title__title'],
            '%model_manufacturer%'             => $l_model_data['isys_model_manufacturer__title'],
            '%model_service_tag%'              => $l_model_data['isys_catg_model_list__service_tag'],
            '%inventory_no%'                   => $l_accounting_data['isys_catg_accounting_list__inventory_no'],
            '%objid%'                          => $p_obj_id,
            '%sysid%'                          => $l_obj_data['isys_obj__sysid'],
            '%location%'                       => $l_location,
            '%location_path%'                  => $l_location_path
        ];
    }

    /**
     * Method for formatting a URL parameter: "Peter Griffin! äöüß" -> "Peter-Griffin-aous".
     *
     * @static
     *
     * @param   string $p_parameter
     * @param   string $p_separator
     *
     * @return  string  mixed
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function format_url_param($p_parameter, $p_separator = '-')
    {
        return trim(isys_glob_strip_accent(isys_glob_replace_accent(trim($p_parameter)), $p_separator), $p_separator);
    }

    /**
     * Creates a html link. Displays the URI with optional * prefix and suffix.
     * Caution: Method is not XSS safe! This is on purpose.
     *
     * @param   string $p_uri    URI
     * @param   string $p_target Target, i. e. "_blank". Optional. Defaults to null.
     * @param   string $p_prefix Prefix for displayed URI. Optional. Defaults to "".
     * @param   string $p_suffix Suffix for displayed URI. Optional. Defaults to "".
     *
     * @return  string  Valid HTML
     */
    public static function create_anker($p_uri, $p_target = null, $p_prefix = '', $p_suffix = '')
    {
        $l_target = '';
        if (isset($p_target)) {
            $l_target = isys_glob_htmlentities(trim($p_target));
        }

        $l_href = self::prependProtocol($p_uri);

        return sprintf('<a href="%s" target="%s">%s%s%s</a>', $l_href, $l_target, $p_prefix, $l_href, $p_suffix);
    }

    /**
     * Appends "http://" if $p_url is not an absolute path (starting with "/" or a protocol).
     * Handles UNC <http://en.wikipedia.org/wiki/Path_(computing)#Uniform_Naming_Convention> as well.
     *
     * @param  string $url
     *
     * @return string
     */
    public static function prependProtocol($url)
    {
        if (
            strpos($url, '%') !== 0 && // Already contains protocol:
            strpos($url, '://') === false &&
            strpos($url, '/') !== 0 && // UNC:
            strpos($url, '\\\\') !== 0 && // UNC:
            strpos($url, '//') !== 0) {
            return 'http://' . $url;
        }

        return $url;
    }

    /**
     * Method for checking, if all mandatory fields are set.
     *
     * @static
     *
     * @param   array $p_mandatories
     * @param   array $p_params
     *
     * @throws  isys_exception_cmdb
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected static function check_params(array $p_mandatories, array $p_params = [])
    {
        foreach ($p_mandatories as $l_mandatory) {
            if (!array_key_exists($l_mandatory, $p_params)) {
                throw new isys_exception_cmdb('Broken link - Parameter "' . $l_mandatory . '" is missing.');
            }
        }
    }
}
