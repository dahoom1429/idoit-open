<?php
/**
 * i-doit
 *
 * Call stylesheet data through cache/smarty.
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
header("Content-Type: text/css");

/**
 * Enabling a cache lifetime of one month
 *
 * Cache will reload after installing a module or updating i-doit because
 * of a new token parameter with the value of the last system update timestamp
 */
isys_core::expire(isys_convert::MONTH);

$tpl = isys_application::instance()->container->template;

global $g_dirs;

if (file_exists(isys_glob_get_temp_dir() . 'style.css')) {
    echo file_get_contents(isys_glob_get_temp_dir() . 'style.css');
    die;
}

// Read every file from this directory.
$l_dir = $g_dirs["css_abs"];

// Set CSS variables to use.
$tpl->assign("dir_images", $g_dirs["images"]);

isys_component_signalcollection::get_instance()
    ->emit('mod.css.beforeProcess');

try {
    $tpl->loadFilter('output', 'TrimWhiteSpaceEnhanced');
} catch (Exception $e) {
    // Do nothing.
}

try {
    if (is_dir($l_dir)) {
        if (($l_dir_handle = opendir($l_dir))) {
            while ($l_filename = readdir($l_dir_handle)) {
                if ($l_filename == 'print.css') {
                    continue;
                }

                $l_filename_full = $l_dir . "/" . $l_filename;

                if (is_file($l_filename_full) && preg_match("/\.css$/i", $l_filename)) {
                    $l_out .= $tpl->fetch($l_filename_full) . "\n";
                }
            }

            closedir($l_dir_handle);
        }
    } else {
        throw new isys_exception_filesystem('"' . $l_dir . '" is not a directory!', 'The given directory "' . $l_dir . '" is no directory or does not exist.');
    }
} catch (isys_exception $l_e) {
    die("Error while creating CSS: " . $l_e->getMessage());
}

isys_component_signalcollection::get_instance()
    ->emit('mod.css.processed', $l_out);

echo $l_out;

if (isys_settings::get('css.caching.cache-to-temp', true)) {
    isys_file_put_contents(isys_glob_get_temp_dir() . 'style.css', $l_out);
}

die;
