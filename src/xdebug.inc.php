<?php
/**
 * i-doit
 *
 * XDebug config
 *
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Stuecken <dstuecken@i-doit.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

//to start a debug session, call: index.php?XDEBUG_SESSION_START=1
//that will set a cookie with a lifetime of one hour.

ini_set("xdebug.collect_params", "3");
ini_set("xdebug.remote_enable", "1");

ini_set("xdebug.var_display_max_depth", "25");
ini_set("xdebug.var_display_max_data", "5000");
ini_set("xdebug.var_display_max_children", "100");
ini_set("xdebug.overload_var_dump", "0");

ini_set("xdebug.extended_info", "0");
ini_set("xdebug.max_nesting_level", "1000");

ini_set("xdebug.trace_format", "1");
ini_set("xdebug.trace_options", "0");

ini_set("xdebug.file_link_format", "txmt://open?url=file://%f&line=%1");

/*
 * xdebug_start_trace($g_absdir . '/log/idoit-trace', XDEBUG_TRACE_COMPUTERIZED);
 */