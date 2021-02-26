<?php
/**
 * i-doit environment info collector
 *
 * @author    Dennis Blümer <dbluemer@i-doit.org>
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @copyright synetics GmbH
 */
global $g_db_system;

if (!$g_db_system && !defined("C__ADMIN_CENTER")) {
    die("This file is part of the admin center and cannot be run standalone.");
}

session_start();

$l_link = new mysqli($g_db_system["host"], $g_db_system["user"], $g_db_system["pass"], $g_db_system["name"], $g_db_system["port"]);
$l_res = $l_link->query("SELECT * FROM isys_db_init");

$l_mysql_version = mysqli_get_server_info($l_link);

$l_db_init = [];
while ($l_row = $l_res->fetch_assoc()) {
    $l_db_init[$l_row["isys_db_init__key"]] = $l_row["isys_db_init__value"];
}

$l_user_agent = $_SERVER['HTTP_USER_AGENT'];
$l_apache_version = $_SERVER['SERVER_SOFTWARE'];

$l_os_version = php_uname();

$l_php_version = phpversion();
$l_ext_mysql = extension_loaded("mysqli");
$l_ext_session = extension_loaded("session");
$l_ext_xml = extension_loaded("xml");
$l_ext_pcre = extension_loaded("pcre");
$l_ext_ldap = extension_loaded("ldap");
$l_ext_curl = extension_loaded("curl");
$l_ext_mcrypt = extension_loaded("mcrypt");

$l_out = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
$l_out .= "<environment>\n";
$l_out .= "	<title>i-doit environment info</title>\n";
$l_out .= "	<product>" . $l_db_init["title"] . " Revision " . $l_db_init["revision"] . "</product>\n";
$l_out .= "	<useragent>" . $l_user_agent . "</useragent>\n";
$l_out .= "	<serveros>" . $l_os_version . "</serveros>\n";
$l_out .= "	<webserver>" . $l_apache_version . "</webserver>\n";
$l_out .= "	<mysql>" . $l_mysql_version . "</mysql>\n";
$l_out .= "	<php>" . $l_php_version . "</php>\n";
$l_out .= "	<phpextensions>\n";
$l_out .= "		<phpextension name=\"MySQL\" version=\"" . phpversion("mysqli") . "\">" . ($l_ext_mysql != false ? "installed" : "N/A") . "</phpextension>\n";
$l_out .= "		<phpextension name=\"Session\" version=\"" . phpversion("session") . "\">" . ($l_ext_session != false ? "installed" : "N/A") . "</phpextension>\n";
$l_out .= "		<phpextension name=\"XML\" version=\"" . phpversion("xml") . "\">" . ($l_ext_xml != false ? "installed" : "N/A") . "</phpextension>\n";
$l_out .= "		<phpextension name=\"PCRE\" version=\"" . phpversion("pcre") . "\">" . ($l_ext_pcre != false ? "installed" : "N/A") . "</phpextension>\n";
$l_out .= "		<phpextension name=\"LDAP\" version=\"" . phpversion("ldap") . "\">" . ($l_ext_ldap != false ? "installed" : "N/A") . "</phpextension>\n";
$l_out .= "		<phpextension name=\"CURL\" version=\"" . phpversion("curl") . "\">" . ($l_ext_curl != false ? "installed" : "N/A") . "</phpextension>\n";
$l_out .= "		<phpextension name=\"MCRYPT\" version=\"" . phpversion("mcrypt") . "\">" . ($l_ext_mcrypt != false ? "installed" : "N/A") . "</phpextension>\n";
$l_out .= "	</phpextensions>\n";

ob_start();
phpinfo(5);
$phpinfo = ['phpinfo' => []];
if (preg_match_all(
    '#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s',
    ob_get_clean(),
    $matches,
    PREG_SET_ORDER
)) {
    foreach ($matches as $match) {
        if (strlen($match[1])) {
            $phpinfo[$match[1]] = [];
        } elseif (isset($match[3])) {
            $phpinfo[end(array_keys($phpinfo))][$match[2]] = isset($match[4]) ? [
                $match[3],
                $match[4]
            ] : $match[3];
        } else {
            $phpinfo[end(array_keys($phpinfo))][] = $match[2];
        }
    }
}

foreach ($phpinfo as $name => $section) {
    if (is_array($section) && !empty($section)) {
        $l_out .= "    <" . str_replace(" ", "_", strtolower($name)) . ">\n";
        foreach ($section as $key => $val) {
            if (is_array($val)) {
                $l_out .= "      <$key>" . strip_tags($val[0]) . ", " . strip_tags($val[1]) . "</$key>\n";
            } elseif (is_string($key)) {
                $l_out .= "      <$key>" . strip_tags($val) . "</$key>\n";
            }
        }
        $l_out .= "    </" . str_replace(" ", "_", strtolower($name)) . ">\n";
    }
}

$l_out .= "</environment>\n";

ob_end_clean();

header("Content-Type: text/xml");
header("Content-Disposition: attachment; filename=idoit-environment_" . date("Ymd") . ".xml");
header("Pragma: no-cache");

echo $l_out;
die;
