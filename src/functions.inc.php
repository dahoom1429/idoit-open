<?php
/**
 * i-doit
 *
 * Global Functions
 *
 * This file provides a globally available function library
 *
 * @package     i-doit
 * @subpackage  General
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

use idoit\Component\Helper\Purify;

define("C__FUNC__AJAX__CONTENT_BY_OBJECT", 0x101);
define("C__FUNC__AJAX__OBJECT_LIST", 0x102);
define("C__FUNC__AJAX__CONTENT_BY_OBJECT_GROUP", 0x103);
define("C__FUNC__AJAX__TREE", 0x104);
define("C__FUNC__AJAX__TREE_LOCATION", 0x105);

/**
 * Function for building ajax URLs.
 *
 * @param   integer $p_function
 * @param   array   $p_gets
 * @param   string  $p_category_param
 * @param   integer $p_cateID
 *
 * @return  string
 */
function isys_glob_build_ajax_url($p_function, $p_gets, $p_category_param = C__CMDB__GET__CATG, $p_cateID = null)
{
    $l_url = "";

    $p_gets = Purify::purifyParams($p_gets);

    unset($p_gets[C__GET__AJAX]);
    unset($p_gets[C__GET__AJAX_CALL]);

    switch ($p_function) {
        case C__FUNC__AJAX__TREE_LOCATION:
            unset($p_gets[C__CMDB__GET__VIEWMODE]);

            if (!is_numeric($p_gets[C__CMDB__GET__OBJECT])) {
                unset($p_gets[C__CMDB__GET__OBJECT]);
            }
            $l_url = "get_tree('" . isys_glob_build_url(isys_glob_http_build_query($p_gets)) . "&call=tree');";
            break;

        case C__FUNC__AJAX__TREE:
            unset($p_gets[C__CMDB__GET__VIEWMODE]);
            $l_url = "get_tree_object_type('" . $p_gets[C__CMDB__GET__OBJECTGROUP] . "', false);";
            break;

        case C__FUNC__AJAX__CONTENT_BY_OBJECT_GROUP:
            if ($p_gets[C__CMDB__GET__OBJECTGROUP]) {
                $l_url = "javascript:get_content_by_group(" . "'" . $p_gets[C__CMDB__GET__OBJECTGROUP] . "'," . "'" . $p_gets[C__CMDB__GET__VIEWMODE] . "'" . ");";
            }
            break;

        case C__FUNC__AJAX__CONTENT_BY_OBJECT:
            $l_url = "javascript:get_content_by_object('" . $p_gets[C__CMDB__GET__OBJECT] . "', '" . $p_gets[C__CMDB__GET__VIEWMODE] . "', '" . $p_gets[C__CMDB__GET__CATG] .
                "','" . $p_category_param . "'";

            if (!is_null($p_cateID)) {
                $l_url .= ", '" . $p_cateID . "'";
            }
            $l_url .= ");";

            break;

        case C__FUNC__AJAX__OBJECT_LIST:
        default:
            break;
    }

    return $l_url;
}

/**
 * Method for recursive striptagging.
 *
 * @param   mixed  String or Array value for stripping tags.
 *
 * @return  mixed
 */
function strip_tags_deep($p_value)
{
    return is_array($p_value) ? array_map('strip_tags_deep', $p_value) : strip_tags($p_value);
}

/**
 * Method for recursive stripslashing.
 *
 * @param   mixed  String or Array value for stripping slashes.
 *
 * @return  mixed
 */
function stripslashes_deep($p_value)
{
    return is_array($p_value) ? array_map('stripslashes_deep', $p_value) : stripslashes($p_value);
}

/**
 * Method for recursive addslashing.
 *
 * @param   mixed  String or Array value for adding slashes.
 *
 * @return  mixed
 */
function addslashes_deep($p_value)
{
    return is_array($p_value) ? array_map('addslashes_deep', $p_value) : addslashes($p_value);
}

/**
 * Replaces Special characters like ä ue ö é á (..) to a u o e a (..)
 *
 * @param string $string
 *
 * @return string
 */
function isys_glob_replace_accent($string)
{
    return str_replace(
        ['À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ'],
        ['A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o'],
        $string
    );
}

/**
 * Strips all non a-z and 0-9 characters, should be combined with isys_glob_replace_accent();
 *
 * @param   string $p_string
 * @param   string $p_replace_spaces_with
 *
 * @return  mixed
 */
function isys_glob_strip_accent($p_string, $p_replace_spaces_with = "-")
{
    return preg_replace([
        '/[^a-zA-Z0-9 \._-]/',
        '/[ -]+/',
        '/^-|-$/'
    ], [
        '',
        $p_replace_spaces_with,
        ''
    ], $p_string);
}

/**
 * Escapes a string.
 *
 * @param   string &$p_string
 *
 * @return  string
 */
function isys_glob_escape_string($p_string)
{
    global $g_comp_database;

    return str_replace("\\\\", "\\", str_replace("'", "\'", $g_comp_database->escape_string($p_string)));
}

/**
 * Displays a logbook message (But does NOT save it into logbook).
 *
 * @param   string  $p_message
 * @param   integer $p_alert_level
 *
 * @return  isys_component_template_infobox
 * @throws Exception
 */
function isys_glob_display_message($p_message, $p_alert_level = null)
{
    if ($p_alert_level === null) {
        $p_alert_level = defined_or_default('C__LOGBOOK__ALERT_LEVEL__3');
    }
    return isys_component_template_infobox::instance()
        ->set_message(isys_application::instance()->container->get('language')
            ->get($p_message), null, null, null, $p_alert_level);
}

/**
 * Returns an array with all data from $p_arrDestination append the new data from $p_arrSource and
 * override exisiting data from $p_arrSource use this function intead of array_merge.
 *
 * @param   array $p_arrDestination
 * @param   array $p_arrSource
 *
 * @return  array
 */
function isys_glob_array_merge($p_arrDestination, $p_arrSource)
{
    if (is_array($p_arrSource)) {
        foreach ($p_arrSource as $l_key_1 => $l_value_1) {
            $l_arr = $l_value_1;
            foreach ($l_arr as $l_key_2 => $l_value_2) {
                $p_arrDestination[$l_key_1][$l_key_2] = $l_value_2;
            }
        }
    }

    return $p_arrDestination;
}

/**
 * Format seconds to human readable
 *
 * @param int   $p_seconds
 *
 * @return string
 */
function isys_glob_seconds_to_human_readable($p_seconds)
{
    $l_days = floor($p_seconds / 86400);
    $p_seconds -= ($l_days * 86400);

    $l_hours = floor($p_seconds / 3600);
    $p_seconds -= ($l_hours * 3600);

    $l_minutes = floor($p_seconds / 60);
    $p_seconds -= ($l_minutes * 60);

    $l_values = [
        'day'    => $l_days,
        'hour'   => $l_hours,
        'minute' => $l_minutes,
        'second' => $p_seconds
    ];

    $parts = [];

    foreach ($l_values as $l_text => $l_value) {
        if ($l_value > 0) {
            $parts[] = $l_value . ' ' . $l_text . ($l_value > 1 ? 's' : '');
        }
    }

    return implode(' ', $parts);
}

/**
 * Gets the difference between two dates, dates need to be timestamps.
 * This is some old code (Don't know who wrote it) greatly updated by Leo (Snatched some code from the Kohana Framework).
 *
 * @param   integer $p_date_from
 * @param   integer $p_date_to
 * @param string    $p_format
 *
 * @return array
 * @author  Leonard Fischer <lfischer@i-doit.com>
 */
function isys_glob_date_diff($p_date_from = null, $p_date_to = null, $p_format = 'ymwdhis')
{
    $p_date_from = $p_date_from ?: time();
    $p_date_to = $p_date_to ?: time();
    $l_diff = abs($p_date_to - $p_date_from);
    $p_format = array_unique(str_split(strtolower($p_format)));
    $l_return = [];

    if (in_array('y', $p_format)) {
        $l_diff -= isys_convert::YEAR * ($l_return['y'] = (int)floor($l_diff / isys_convert::YEAR));
    }

    if (in_array('m', $p_format)) {
        $l_diff -= isys_convert::MONTH * ($l_return['m'] = (int)floor($l_diff / isys_convert::MONTH));
    }

    if (in_array('w', $p_format)) {
        $l_diff -= isys_convert::WEEK * ($l_return['w'] = (int)floor($l_diff / isys_convert::WEEK));
    }

    if (in_array('d', $p_format)) {
        $l_diff -= isys_convert::DAY * ($l_return['d'] = (int)floor($l_diff / isys_convert::DAY));
    }

    if (in_array('h', $p_format)) {
        $l_diff -= isys_convert::HOUR * ($l_return['h'] = (int)floor($l_diff / isys_convert::HOUR));
    }

    if (in_array('i', $p_format)) {
        $l_diff -= isys_convert::MINUTE * ($l_return['i'] = (int)floor($l_diff / isys_convert::MINUTE));
    }

    if (in_array('s', $p_format)) {
        $l_return['s'] = $l_diff;
    }

    return $l_return;
}

/**
 * Unescapes a string
 *
 * @param   string $p_str
 *
 * @return  string
 */
function isys_glob_unescape($p_str)
{
    if (is_object($p_str)) {
        return $p_str;
    }

    return str_replace("\\", "", $p_str);
}

/**
 * Returns an array with 1 = yes and 0 = no.
 *
 * @return array
 */
function get_smarty_arr_YES_NO()
{
    try {
        $language = isys_application::instance()->container->get('language');

        return [
            '1' => $language->get('LC__UNIVERSAL__YES'),
            '0' => $language->get('LC__UNIVERSAL__NO')
        ];
    } catch (Exception $e) {
        return [
            '1' => 'Yes',
            '0' => 'No'
        ];
    }
}

// LF: Moved "isys_glob_is_valid_ip" to Nostalgia, removed in i-doit 1.15.

// LF: Moved "isys_glob_is_valid_ip6" to Nostalgia, removed in i-doit 1.15.

/**
 * Default Template Handler. Called when Smarty's file: resource is unable to load a requested file.
 *
 * @param   string                  $p_res_type Resource type (e.g. "file", "string", "eval", "resource")
 * @param   string                  $p_res_name Resource name (e.g. "foo/bar.tpl")
 * @param   string                  &$p_template_source
 * @param   integer                 &$p_template_timestamp
 * @param   isys_component_template $p_smarty_obj
 *
 * @return  mixed  Path to file or boolean true if $content and $modified have been filled, boolean false if no default template could be loaded
 * @author   Dennis Stücken <dstuecken@synetics.de>
 *
 */
function isys_glob_template_handler($p_res_type, $p_res_name, &$p_template_source, &$p_template_timestamp, isys_component_template $p_smarty_obj)
{
    if ($p_res_type === 'file' && !is_readable($p_res_name)) {
        $l_default_file = __DIR__ . '/themes/default/smarty/templates/' . $p_res_name;
        $l_default_file = str_replace('./', '', $l_default_file);

        if ($l_default_file && file_exists($l_default_file)) {
            $p_template_timestamp = time();
            $p_template_source = file_get_contents($l_default_file);

            return true;
        }
    }

    return false;
}

/**
 * Override the user's settings.
 *
 * @author Dennis Stücken <dstuecken@synetics.de>
 */
function isys_glob_override_user_settings()
{
    global $g_comp_database, $g_config, $g_dirs, $g_reference_colors;

    if (is_object($g_comp_database)) {
        $g_comp_user = isys_component_dao_user::instance($g_comp_database);
        $g_reference_colors = $g_comp_user->get_reference_coloration();
    } else {
        $g_reference_colors = [];
    }

    $g_dirs["css_abs"] = preg_replace("/themes\/(.+?)\//i", "themes/default/", $g_dirs["css_abs"]);

    // Replace "theme_images" with the current theme directory.
    if (!empty($g_dirs["theme_images"])) {
        $g_dirs["theme_images"] = preg_replace("/themes\/(.+?)\//i", "themes/default/", $g_dirs["theme_images"]);
    } else {
        $g_dirs["theme_images"] = $g_config["www_dir"] . "src/themes/default/images/";
    }

    $g_dirs["smarty"] = preg_replace("/themes\/(.+?)\//i", "themes/default/", $g_dirs["smarty"]);
    $g_dirs["theme"] = preg_replace("/themes\/(.+?)\//i", "themes/default/", $g_dirs["theme"]);
    $g_dirs["images"] = $g_config["www_dir"] . "images/";

    $g_config["theme"] = 'default';

    return true;
}

/**
 * Deletes a directory recursively.
 *
 * @param   string  $p_startdir
 * @param   string  &$p_deleted
 * @param   string  &$p_undeleted
 * @param   boolean $skipHidden
 *
 * @return  boolean
 */
function isys_glob_delete_recursive($p_startdir, &$p_deleted, &$p_undeleted, $skipHidden = false)
{
    // Validate directory information
    if (empty($p_startdir) || file_exists($p_startdir) === false) {
        return false;
    }

    try {
        // Delete if path is file
        if (is_file($p_startdir) || is_link($p_startdir)) {
            // Try to delete file and set statistic variables
            if (unlink($p_startdir)) {
                $p_deleted++;
                return true;
            }

            $p_undeleted++;
            return false;
        }

        // Create DirectoryIterator
        $l_files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($p_startdir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        // Iterate over directory children
        foreach ($l_files as $l_fileinfo) {
            /** @var  SplFileInfo $l_fileinfo */
            if ($l_fileinfo->isDir()) {
                // Step into sub directory
                isys_glob_delete_recursive($l_fileinfo->getRealPath(), $p_deleted, $p_undeleted, $skipHidden);

                $filenames = scandir($l_fileinfo->getRealPath());
                // Check for emptiness before attempting to delete the directory
                if (is_countable($filenames) && count($filenames) === 2 && rmdir($l_fileinfo->getRealPath()) === false) {
                    $p_undeleted++;
                } else {
                    $p_deleted++;
                }
            } else {
                // Check whether file exists
                if (file_exists($l_fileinfo->getRealPath())) {
                    // Check for dotFiles and skip them if needed
                    if ($skipHidden && substr($l_fileinfo->getBasename(), 0, 1) === '.') {
                        // During development, we'd like to keep files like ".gitkeep" or ".htaccess".
                        continue;
                    }

                    // Delete file and set statistics
                    if (unlink($l_fileinfo->getRealPath()) === false) {
                        $p_undeleted++;
                    } else {
                        $p_deleted++;
                    }
                }
            }
        }
    } catch (Exception $e) {
        $p_undeleted++;

        return false;
    }

    return true;
}

/**
 * Returns the temporary directory.
 *
 * @return  string
 */
function isys_glob_get_temp_dir()
{
    global $g_dirs;

    return $g_dirs["temp"];
}

/**
 * Returns a javascript block with $p_string in it.
 *
 * @param   string $p_string
 *
 * @return  string
 */
function isys_glob_js_print($p_string)
{
    return '<script language="javascript" type="text/javascript">
		try {
		' . $p_string . '
		} catch (e) {
			if (typeof idoit.Notify === "object") {
				idoit.Notify.error(e.message, {sticky:true});
			} else {
				alert(e.message);
			}
		}</script>';
}

/**
 * Returns a parameter which was send via get or post or false if no parameter was found.
 *
 * @param   string $p_key
 *
 * @return  mixed   Mixed value if the key is found - otherwise boolean false.
 */
function isys_glob_get_param($p_key)
{
    // @see  ID-3485  Filter parameters before returning.
    if (isset($_GET[$p_key])) {
        return filter_var($_GET[$p_key], FILTER_SANITIZE_SPECIAL_CHARS);
    }

    if (isset($_POST[$p_key])) {
        return filter_var($_POST[$p_key], FILTER_SANITIZE_SPECIAL_CHARS);
    }

    return false;
}

/**
 * give post params higher priority than get
 *
 * @param   string $p_key
 *
 * @return  mixed  post or get param
 */
function isys_glob_get_param_invert($p_key)
{
    // @see  ID-3485  Filter parameters before returning.
    if (isset($_POST[$p_key])) {
        return filter_var($_POST[$p_key], FILTER_SANITIZE_SPECIAL_CHARS);
    }

    if (isset($_GET[$p_key])) {
        return filter_var($_GET[$p_key], FILTER_SANITIZE_SPECIAL_CHARS);
    }

    return false;
}

/**
 * Returns mandator string from session variable or from the db
 *
 * @param   integer $p_id
 *
 * @return  string
 */
function isys_glob_get_mandant_name_as_string($p_id)
{
    /** @var isys_component_session $g_comp_session */
    global $g_comp_session;
    global $g_comp_database;
    global $g_db;

    /** @noinspection PhpUnusedLocalVariableInspection */
    global $g_config;

    $l_strMandatorName = $g_comp_session->get_mandator_name();

    if (mb_strlen($l_strMandatorName) > 0) {
        return $l_strMandatorName;
    }

    $l_table_mandator = "isys_mandator";

    $l_mandant_dao = $g_comp_session->get_mandator_dao($p_id);

    try {
        if (!is_null($g_comp_database) && $g_comp_database->num_rows($l_mandant_dao) > 0) {
            $l_row = $g_comp_database->fetch_array($l_mandant_dao);
            $g_comp_session->set_mandator_name($l_row['isys_mandator__title']);
        } else {
            if ($g_comp_session->logout()) {
                $l_logoutmsg = "I resetted your session now. You may just need to refresh your browser and login again.";
            } else {
                $l_logoutmsg = "You may need to restart your browser to reset your current session.";
            }

            $l_message = "Error: Could not retrieve the mandators name from System-DB. (Table: {$l_table_mandator})\n" . "Used ID: \"{$p_id}\"\n\n" .
                "If you don't see any ID, your session might be broken. " . $l_logoutmsg;

            throw new isys_exception_database($l_message, $g_db);
        }
    } catch (isys_exception_database $e) {
        // Don't make any output, but log the error.
        $e->write_log();
    }

    return $g_comp_session->get_mandator_name();
}

/**
 * Get the directory name for mandator-cache.
 *
 * @param   integer $p_id The (optional) mandator-ID.
 *
 * @return  string
 * @author  Leonard Fischer <lfischer@synetics.de>
 */
function isys_glob_get_mandator_cache_dir($p_id = null)
{
    if (null === $p_id) {
        $p_id = $_SESSION['user_mandator'];
    }

    return 'cache_' . isys_glob_get_mandant_name_as_string($p_id);
}

/**
 * Get the direction of the tabledata-order and append sql-syntax to the given parameter.
 *
 * @param   string $p_strSQL
 *
 * @return  string
 */
function isys_glob_sql_append_order($p_strSQL)
{
    if (isys_glob_get_param("sort") != false) {
        $l_sort = isys_glob_get_param("sort");
        $l_direction = isys_glob_get_param("dir");
        $p_strSQL .= " ORDER BY $l_sort $l_direction";
    }

    return $p_strSQL;
}

/**
 * Returns ASC or DESC, depending on the value in the url.
 *
 * @return  string
 */
function isys_glob_get_order()
{
    if (isys_glob_get_param('dir') === 'DESC') {
        return 'ASC';
    }

    return 'DESC';
}

// LF: Moved "isys_glob_url_remove" to Nostalgia, removed in i-doit 1.15.

/**
 * Returns a string javascript-formatted
 *
 * @param string $p_string
 *
 * @return string
 */
function isys_glob_js_string($p_string)
{
    return "'" . str_replace([
            "\\\\",
            "\n",
            "'"
        ], [
            "\\",
            "\\n",
            "\\'"
        ], $p_string) . "'";
}

/**
 * Returns a string that is either $p_url (if you pass it) or the current URI, appended with "$p_key"="$p_value".
 *
 * @param      string $p_key
 * @param      string $p_value
 * @param      string $p_url
 *
 * @return     string
 * @author     Dennis Stuecken <dstuecken@i-doit.org>
 * @version    Selcuk Kekec    <skekec@i-doit.org>
 * @deprecated Use isys_helper_link::add_params_to_url();
 */
function isys_glob_add_to_query($p_key, $p_value, $p_url = null)
{
    /* Get default get-params */
    if (empty($p_url)) {
        $p_url = $_GET;
    } else {
        /* Remove '?' from the beginning of the delivered query */
        if (is_string($p_url) && mb_strlen($p_url) && $p_url[0] == "?") {
            $p_url = substr($p_url, 1);
        }

        /* Explode it to an array */
        $p_url = explode("&", $p_url);
    }

    /* Set/Replace the given KEY in our params-array */
    $p_url[$p_key] = $p_value;

    return "?" . http_build_query($p_url);
}

/**
 * Generates a URL-encoded query string (This is a wrapper for the function http_build_query).
 * Formdata may be an array or object containing properties. A formdata array may be a simple one-dimensional structure, or an array of arrays (who in turn may contain other arrays).
 * If numeric indices are used in the base array and a numeric_prefix is provided, it will be prepended to the numeric index for elements in the base array only.
 * This is to allow for legal variable names when the data is decoded by PHP or another CGI application later on.
 *
 * @param   array $p_arData
 *
 * @return  string
 */
function isys_glob_http_build_query($p_arData)
{
    return http_build_query(((is_countable($p_arData) && count($p_arData) > 0) ? $p_arData : []), null, '&');
}

/**
 * Stops the string at a given position.
 *
 * @param   string  $string
 * @param   integer $length
 * @param   string  $appendix
 *
 * @return  string
 */
function isys_glob_cut_string($string, $length = 100, $appendix = "..")
{
    global $g_config;

    if ($length > 0 && mb_strlen($string) > $length) {
        $length -= mb_strlen($appendix);

        if (function_exists('mb_substr')) {
            $l_string = mb_substr($string, 0, $length, $g_config['html-encoding']);
        } else {
            $l_string = substr($string, 0, $length);
        }

        return $l_string . $appendix;
    }

    return $string;
}

/**
 * Stops a string and appends.
 *
 * @param   string  $string
 * @param   integer $length
 * @param   string  $appendix
 *
 * @return  string
 */
function isys_glob_str_stop($string, $length = 100, $appendix = "..")
{
    return isys_glob_cut_string($string, $length, $appendix);
}

// LF: Moved "isys_glob_datetime" to Nostalgia, removed in i-doit 1.15.

/**
 * Builds temporary table name for object lists.
 *
 * @param   string $p_tblName
 * @param   string $p_sesID
 *
 * @return  string
 * @author  Niclas Potthast <npotthast@i-doit.org>
 */
function isys_glob_get_obj_list_table_name($p_tblName = null, $p_sesID = null)
{
    /** @var isys_component_session $g_comp_session */
    global $g_comp_session;

    if ($p_sesID) {
        $l_sesID = $p_sesID;
    } else {
        $l_sesID = $g_comp_session->get_session_id();
    }

    if (!$p_tblName) {
        $l_tblName = "tempObjList_";
    } else {
        $l_tblName = $p_tblName;
    }

    return $l_tblName . md5($l_sesID);
}

/**
 * Returns a DAO result object with the table entries.
 *
 * @param   string                  $p_tbl
 * @param   isys_component_database $p_dbo
 * @param   integer                 $p_status
 * @param   string                  $p_order
 * @param   string                  $p_condition
 *
 * @return  isys_component_dao_result|null
 */
function isys_glob_get_data_by_table($p_tbl, $p_dbo = null, $p_status = C__RECORD_STATUS__NORMAL, $p_order = null, $p_condition = null)
{
    global $g_comp_database;

    // Determine database object to user
    $l_dbo = $p_dbo;

    if ($p_dbo == null) {
        $l_dbo = $g_comp_database;
    }

    if (is_object($l_dbo)) {
        // Return DAO result with table entries
        $l_sql = 'SELECT * FROM ' . $p_tbl . ' WHERE TRUE';

        if (!empty($p_condition)) {
            $l_sql .= " AND (" . $p_condition . ")";
        }

        if (!empty($p_status)) {
            $l_sql .= " AND (" . $p_tbl . "__status = " . $p_status . ") ";
        }

        if (!strpos($p_tbl, '_catg_') && !strpos($p_tbl, '_cats_')) {
            if (is_null($p_order)) {
                $l_sql .= " ORDER BY " . $p_tbl . "__title ASC;";
            } elseif ($p_order) {
                $l_sql .= " ORDER BY " . $p_order . " ASC;";
            }
        }

        return new isys_component_dao_result($l_dbo, $l_dbo->query($l_sql));
    }

    return null;
}

/**
 * Gets the current URL and adds a query to it. For example:
 * key=value&key2=value2 -> http://www.example.com/index.php?key=value&key2=value2
 *
 * @param   string $p_query
 *
 * @return  string
 */
function isys_glob_build_url($p_query)
{
    global $g_config;

    return $g_config["startpage"] . "?" . $p_query;
}

/**
 * @param  isys_module_request $p_modreq
 */
function isys_glob_merge_globals_by_modreq(isys_module_request &$p_modreq)
{
    global $GLOBALS;

    $GLOBALS["_GET"] = array_merge($GLOBALS["_GET"], $p_modreq->get_gets());
    $GLOBALS["_POST"] = array_merge($GLOBALS["_POST"], $p_modreq->get_posts());
}

/**
 * If given several parameters, this function will return the first one, which is set (not null, false, empty, ...).
 *
 * @return  mixed
 */
function isys_glob_which_isset()
{
    $l_aargs = func_get_args();

    foreach ($l_aargs as $l_arg) {
        if (@isset($l_arg)) {
            return $l_arg;
        }
    }

    return null;
}

// LF: Moved "isys_glob_mkdate" to Nostalgia, removed in i-doit 1.15.

/**
 * Returns array with all language constant-strings
 * from isys_language (system database), excluding 'ISYS_LANGUAGE_ALL'.
 *
 * @return  array
 * @throws isys_exception_database
 * @global  $g_comp_database
 */
function isys_glob_get_language_constants()
{
    $l_return = [];

    $l_res = isys_component_dao::factory(isys_application::instance()->database_system)
        ->retrieve('SELECT isys_language__title, isys_language__const FROM isys_language WHERE isys_language__const != "ISYS_LANGUAGE_ALL";');

    if (is_countable($l_res) && count($l_res) > 0) {
        while ($l_row = $l_res->get_row()) {
            $l_return[constant($l_row['isys_language__const'])] = $l_row['isys_language__title'];
        }
    }

    return $l_return;
}

/**
 * Displays an html formatted error
 *
 * @param  string $p_message
 */
function isys_glob_display_error($p_message)
{
    // Include Debug bar if it exists and it's not included already
    if (ENVIRONMENT === 'development' && file_exists(__DIR__ . '/classes/modules/debug_bar/init.php')) {
        // Module loader may not be initialized, so include debug bar initialization manually
        // This is needed to have debug bar autoloading enabled
        include_once(__DIR__ . '/classes/modules/debug_bar/init.php');

        // Dump error to debug bar if possible
        (new \idoit\Module\DebugBar\Dumper\StackTraceDumper())->execute($p_message);
    }

    ob_end_clean();

    echo '<style>body {background-color:transparent;} .error {background-color:#ffdddd; border:1px solid #ff4343; color: #701719; overflow:auto; padding:10px;}</style>' .
        '<div><img style="float:right; margin-left: 15px; margin-right:5px;" width="100" src="images/logo.png" /><p class="error">' . $p_message . '</p></div>';
}

/**
 * With this function we can be sure that we're in "edit mode".
 *
 * @author  Leonard Fischer <lfischer@synetics.de>
 * @return  boolean
 * @since   0.9.9-8
 */
function isys_glob_is_edit_mode()
{
    return isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__NEW
        || isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__EDIT
        || isys_glob_get_param(C__CMDB__GET__EDITMODE) == C__EDITMODE__ON;
}

/**
 * html_entities Wrapper.
 *
 * @param   string  $p_val
 * @param   integer $p_flags
 * @param   string  $p_encoding
 * @param   boolean $p_double_enc
 *
 * @return  string
 */
function isys_glob_htmlentities($p_val, $p_flags = ENT_QUOTES, $p_encoding = null, $p_double_enc = false)
{
    // ID-3939 "is_string()" will cause integers to vanish.
    if (is_scalar($p_val)) {
        return htmlentities($p_val, $p_flags, ($p_encoding ?: $GLOBALS['g_config']['html-encoding']), $p_double_enc);
    }

    return '';
}

/**
 * html_entity_decode Wrapper.
 *
 * @param   string  $p_val
 * @param   integer $p_flags
 * @param   string  $p_encoding
 *
 * @return  string
 */
function isys_glob_html_entity_decode($p_val, $p_flags = ENT_QUOTES, $p_encoding = null)
{
    $p_encoding = $p_encoding ?: $GLOBALS['g_config']['html-encoding'];

    if (is_string($p_val)) {
        return html_entity_decode($p_val, $p_flags, $p_encoding);
    } else {
        return '';
    }
}

// LF: Moved "isys_glob_htmlspecialchars" to Nostalgia, removed in i-doit 1.15.

/**
 * Compare two arrays by array key 'title'
 *
 * @param   mixed $p_x
 * @param   mixed $p_y
 *
 * @return  mixed
 */
function isys_glob_array_compare_title($p_x, $p_y)
{
    if (is_array($p_x) && isset($p_x['title']) && isset($p_y['title'])) {
        return strcmp($p_x['title'], $p_y['title']);
    } elseif (is_string($p_x)) {
        return strcmp($p_x, $p_y);
    } else {
        return false;
    }
}

/**
 * Returns the defined page-limit.
 *
 * @global  integer $g_page_limit
 * @return  integer
 */
function isys_glob_get_pagelimit()
{
    global $g_page_limit;

    if (!is_numeric($g_page_limit)) {
        $g_page_limit = isys_usersettings::get('gui.objectlist.rows-per-page', 50);
    }

    return $g_page_limit;
}

/**
 * Sorting mechanism for multidimensional arrays
 *
 * @param array     $p_array
 * @param   string  $p_field
 * @param   integer $p_direction
 *
 * @author        Van Quyen Hoang <qhoang@i-doit.org>
 */
function isys_glob_sort_array_by_column(array &$p_array, $p_field, $p_direction = SORT_ASC)
{
    $l_sort_array = [];

    foreach ($p_array as $l_key => $l_value) {
        $l_sort_array[$l_key] = $l_value[$p_field];
    }

    array_multisort($l_sort_array, $p_direction, $p_array);
}

/**
 * Function to find out if a given array is associative.
 *
 * @param   array $p_arr
 *
 * @return  boolean
 * @author  Leonard Fischer <lfischer@i-doit.com>
 * @see     http://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
 */
function is_assoc($p_arr)
{
    if (!is_array($p_arr)) {
        return false;
    }

    return (bool)count(array_filter(array_keys($p_arr), 'is_string'));
}

if (!function_exists('is_countable')) {
    /**
     * Check, if the $variable is countable. It's a polyfill for php@7.3 is_countable function
     *
     * @param $variable
     *
     * @return bool
     * @todo Remove this function once our minimum requirement is PHP >= 7.3.0 (probably 1.17 or 1.18?).
     */
    function is_countable($variable)
    {
        return is_array($variable) || $variable instanceof \Countable || $variable instanceof \SimpleXMLElement || $variable instanceof \ResourceBundle;
    }
}

// LF: Moved "array_find" to Nostalgia, removed in i-doit 1.15.

// LF: Moved "isys_stristr" to Nostalgia, removed in i-doit 1.15.

/**
 * The "which" command (show the full path of a command)
 *
 * @param    string $program  The command to search for
 * @param    mixed  $fallback Value to return if $program is not found
 *
 * @return  mixed A string with the full path or false if not found
 * @author  Stig Bakken <ssb@php.net>
 * @license http://opensource.org/licenses/bsd-license.php New BSD License
 * @link    http://pear.php.net/package/PEAR
 */
function system_which($program, $fallback = false)
{
    // enforce API.
    if (!is_string($program) || '' == $program) {
        return $fallback;
    }

    $isWindows = substr(PHP_OS, 0, 3) === 'WIN';

    // full path given.
    if (basename($program) !== $program) {
        $path_elements[] = dirname($program);
        $program = basename($program);
    } else {
        $path = getenv('PATH');
        if (!$path) {
            $path = getenv('Path'); // some OSes are just stupid enough to do this
        }
        $path_elements = explode(PATH_SEPARATOR, $path);
    }

    if ($isWindows) {
        $exe_suffixes = getenv('PATHEXT') ? explode(PATH_SEPARATOR, getenv('PATHEXT')) : ['.exe', '.bat', '.cmd', '.com'];

        // allow passing a command.exe param
        if (strpos($program, '.') !== false) {
            array_unshift($exe_suffixes, '');
        }
    } else {
        $exe_suffixes = [''];
    }

    foreach ($exe_suffixes as $suff) {
        foreach ($path_elements as $dir) {
            $file = $dir . DIRECTORY_SEPARATOR . $program . $suff;

            // It's possible to run a .bat on Windows that is_executable would return false for. The is_executable check is meaningless...
            if ($isWindows && file_exists($file)) {
                return $file;
            }

            if (is_executable($file)) {
                return $file;
            }
        }
    }

    return $fallback;
}

/**
 * Method for checking if internet is available.
 *
 * @return boolean
 * @see http://stackoverflow.com/questions/4860365/determine-in-php-script-if-connected-to-internet
 */
function internetAvailable()
{
    try {
        $connected = @fsockopen("www.google.com", 80);

        if ($connected) {
            fclose($connected);

            return true;
        }

        return false;
    } catch (Exception $e) {
        // Just assume something else went wrong...
        return true;
    }
}

/**
 * Check whether version meets requirements
 * defined by minimum and maximum information
 *
 * Please provide comparable version values
 * to guarantee valid handling. Therefore
 * you can use getVersion().
 *
 * @param string $version
 * @param string $minVersion
 * @param string $maxVersion
 *
 * @author Selcuk Kekec <skekec@i-doit.com>
 * @return bool
 */
function checkVersion($version, $minVersion, $maxVersion)
{
    return (version_compare($version, $minVersion, '>=') && version_compare($version, $maxVersion, '<='));
}

/**
 * Checks, if the constant is defined: if defined - returns its value, otherwise - value from $default
 *
 * @param      $constant
 * @param null $default
 *
 * @return mixed|null
 */
function defined_or_default($constant, $default = null)
{
    return defined($constant) ? constant($constant) : $default;
}

/**
 * Checks, if $value is in values of defined constants. Constants should be a string names of constants
 *
 * @param       $value
 * @param array $constants
 *
 * @return bool
 */
function is_value_in_constants($value, array $constants)
{
    return in_array($value, filter_defined_constants($constants), false);
}

/**
 * Constants - strings with names of constants to check
 *
 * Result will contain values of all defined constants
 * @param array $constants
 *
 * @return array
 */
function filter_defined_constants(array $constants)
{
    return array_map('constant', array_filter($constants, 'defined'));
}

/**
 * Filters the array - leave only the key-values, where constant with key name is defined.
 * In the result the keys will be replaced with their value of constant
 *
 * @param array $values
 *
 * @return array
 */
function filter_array_by_keys_of_defined_constants(array $values)
{
    $result = [];
    foreach ($values as $constant => $value) {
        if (defined($constant)) {
            $result[constant($constant)] = $value;
        }
    }
    return $result;
}

/**
 * Filters the array - leave only the key-values, where constant with value name is defined.
 * In the result the values will be replaced with their value of constant
 * @param array $values
 *
 * @return array
 */
function filter_array_by_value_of_defined_constants(array $values)
{
    $result = [];
    foreach ($values as $key => $constant) {
        if (defined($constant)) {
            $result[$key] = constant($constant);
        }
    }
    return $result;
}

/**
 * Check whether version is above max version
 *
 * Please provide comparable version values
 * to guarantee valid handling. Therefore
 * you can use getVersion().
 *
 * @param string $version
 * @param string $maxVersion
 *
 * @author Selcuk Kekec <skekec@i-doit.com>
 * @return mixed
 */
function checkVersionIsAbove($version, $maxVersion)
{
    return version_compare($version, $maxVersion, '>');
}

if (!function_exists('getVersion')) {
    /**
     * Get cleaned version string
     *
     * Some operating systems add specific stuff
     * to phpversion() and mysql which disrupts version
     * comparisan of version_compare()
     *
     * @param string $version Supposed to be the output of phpversion()
     *
     * @return string
     * @throws Exception
     */
    function getVersion($version)
    {
        // Ensure php version without os related stuff
        if (preg_match('/^\d[\d.]*/', $version, $matches) === 1) {
            return $matches[0];
        }

        // Let executer handle exceptions
        throw new Exception('Unable to determine valid version by given version information: \'' . $version . '\'');
    }
}

/**
 * A wrapper for the @link https://php.net/manual/en/function.file-put-contents.php to set the right permissions
 *
 * @param      $filename
 * @param      $data
 * @param int  $flags
 * @param null $context
 *
 * @return bool|int
 */
function isys_file_put_contents($filename, $data, $flags = 0, $context = null)
{
    if (!file_exists($filename)) {
        $directory = dirname($filename);
        if (!is_writable($directory)) {
            return false;
        }
        touch($filename);
        chmod($filename, 0664);
    }
    if (is_writable($filename)) {
        return file_put_contents($filename, $data, $flags, $context);
    }

    return false;
}
