<?php
/**
 * i-doit
 *
 * Installer
 * Step 1
 * System check
 *
 * @package    i-doit
 * @subpackage General
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

if (file_exists($g_module_dir)) {

    $l_mod_html = "";

    /* Start retrieving available modules */
    $l_update = new isys_update_modules();
    $l_modules = $l_update->get_available_modules($g_module_dir, (isset($_SESSION["modules"]) ? array_flip($_SESSION["modules"]) : null));

    foreach ($l_modules as $l_module) {
        $l_checked = "";
        if ($l_module["selected"]) {
            $l_checked = " checked=\"checked\"";
        }

        $l_mod_html .= "<tr>" . "<td>" . "<label>" . "<input type=\"checkbox\" name=\"module[" . $l_module["directory"] . "]\" value=\"" . $l_module["title"] .
            "\"{$l_checked}  />" . $l_module["title"] . "</label>" . "</td>" . "<td>" . $l_module["version"] . "</td>" . "</tr>";

    }

    tpl_set($g_tpl_step, [
        "MODULES" => $l_mod_html
    ]);

}

?>