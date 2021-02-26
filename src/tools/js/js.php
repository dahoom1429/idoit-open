<?php
/**
 * i-doit
 *
 * Javascrpt loader
 *
 * This file provides functions to read all javasscript files from a specific
 * directory. These files have to have the extension "js".
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

header("Content-Type: text/javascript; charset=utf-8");
header("Cache-Control: must-revalidate;");
//header("Expires: " . gmdate ("D, d M Y H:i:s", time() + 60 * 60 * 24 * 30) . " GMT");

// Disable error reporting here.
error_reporting(0);

if (extension_loaded('zlib')) {
    ob_start("ob_gzhandler");
} else {
    ob_start();
}

// Read every file from this directory.
$l_dir = dirname(__FILE__);

if (is_dir($l_dir)) {
    if ($l_dir_handle = opendir($l_dir)) {
        while ($l_filename = readdir($l_dir_handle)) {
            $l_filename_full = $l_dir . "/" . $l_filename;

            if ($l_filename != 'scripts.js') {
                if (is_file($l_filename_full) && strtolower(substr($l_filename, -2)) == 'js') {
                    echo <<<EOC
/* -------------------------------------------------------------------- */
/* i-doit / JS Inclusion: $l_filename */
/* -------------------------------------------------------------------- */

EOC;

                    echo file_get_contents($l_filename_full);
                    echo "\n\n\n";
                }
            }
        }

        closedir($l_dir_handle);
    }
}

ob_end_flush();
?>