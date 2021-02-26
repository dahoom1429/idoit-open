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

if (isys_component_session::instance()
    ->is_logged_in()) {
    /**
     * Enabling a cache lifetime of one month (but only for the full cache, which maybe for some modules is only generated after logging in)
     *
     * Cache will reload after installing a module or updating i-doit after deleting the temp/ contents
     */
    isys_core::expire(isys_convert::MONTH);
}

$l_path = isys_component_constant_manager::instance()
    ->get_fullpath();

if (file_exists($l_path . 'mod-style.css')) {
    echo file_get_contents($l_path . 'mod-style.css');
    die;
}

$l_attachCSS = isys_component_signalcollection::get_instance()
    ->emit('mod.css.attachStylesheet');

if (is_array($l_attachCSS)) {
    foreach ($l_attachCSS as $l_css) {
        if (file_exists($l_css)) {
            $l_out .= file_get_contents($l_css) . "\n";
        }
    }
}

echo $l_out;

if (is_dir($l_path) && isys_settings::get('css.caching.cache-to-temp', true)) {
    isys_file_put_contents($l_path . 'mod-style.css', $l_out);
}

die;
