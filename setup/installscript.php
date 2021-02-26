<?php
/**
 * i-doit
 *
 * Shell-Installer
 *
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Stücken    <dstuecken@i-doit.org> - 01-2009
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

/* <config> */

/* database connection */
$l_db_host = "127.0.0.1";
$l_db_port = 3306;
$l_db_user = "root";
$l_db_pass = "";

/* idoit databases */
$l_db_system = "idoit_system";
$l_db_mandant = "idoit_data";

/* comma separated list of modules to install (dirnames in setup/modules/) */
/* example: ldap,nagios */
$l_mod_install = "ldap";

/* </config> no more changes below this needed */

/* i-doit root directory */
$g_absdir = str_replace(DIRECTORY_SEPARATOR . "setup", "", dirname(__FILE__)) . DIRECTORY_SEPARATOR;

$g_dirs = [
    "temp"    => $g_absdir . "temp/",
    "class"   => $g_absdir . "src/classes/",
    "import"  => $g_absdir . "src/classes/import/",
    "handler" => $g_absdir . "src/handler/",
];

error_reporting(E_ALL & !E_NOTICE);

/* where are the setup modules located? */
$g_module_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR;
define("C__DIR__MODULES", "modules/");
define("C__DIR__FILES", "files/");

/* include required sources */
include_once($g_absdir . "src/functions.inc.php");

/* Taken from update procedure (update.inc.php) */
$g_updatedir = $g_absdir . DIRECTORY_SEPARATOR . "updates" . DIRECTORY_SEPARATOR;

define("C__XML__SYSTEM", "update_sys.xml");
define("C__XML__DATA", "update_data.xml");

include_once($g_updatedir . "classes/isys_update.class.php");

$l_fh = opendir($g_updatedir . "classes");
while ($l_file = readdir($l_fh)) {
    if (strpos($l_file, ".") !== 0 && !include_once($g_updatedir . "classes/" . $l_file)) {
        __die("Could not load " . $g_updatedir . $l_file, __FILE__, __LINE__);
    }
}
/* -- */

/* Format module array */
if (strstr($l_mod_install, ",")) {
    $l_tmp = explode(",", $l_mod_install);
    foreach ($l_tmp as $l_m) {
        $l_mods[$l_m] = $l_m;
    }
} else {
    $l_mods[$l_mod_install] = $l_mod_install;
}

function p($p_message)
{
    printf("%s\n", $p_message);
}

/* get autoloder for components, exceptions and so on */
include_once($g_absdir . "/src/autoload.inc.php");

p("---------------------------------------------------");
p("i-doit addon script initialized.");
p("---------------------------------------------------");

p("Loading mandator database component.");

$l_comp_database = isys_component_database::get_database("mysqli", $l_db_host, $l_db_port, $l_db_user, $l_db_pass, $l_db_mandant);

p("Loading system database component.");

$l_comp_database_system = isys_component_database::get_database("mysqli", $l_db_host, $l_db_port, $l_db_user, $l_db_pass, $l_db_system);

p("Parsing module scripts..");
foreach ($l_mods as $l_module_dir => $l_module_title) {

    $l_update = new isys_update_modules();
    echo(" - Processing {$l_module_title}");

    $l_update->install(rtrim($g_module_dir, "/") . "/" . $l_module_dir, [
        $l_db_mandant
    ], [
        $l_db_mandant => $l_comp_database
    ], $l_comp_database_system);

    p(" .. OK");

}

p("Finished..");

?>