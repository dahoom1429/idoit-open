<?php

/**
 * Function for filtering any "non-word-characters" out of directory names (for cache dir).
 *
 * @param   string $p_dir
 *
 * @return  string
 * @author  Leonard Fischer <lfischer@i-doit.com>
 */
function filter_directory_name($p_dir)
{
    return trim(preg_replace('~([_]+|[^a-z0-9])+~', '_', strtolower($p_dir)), " _\t\n\r\0\x0B");
}

/**
 * @param   string   $p_db_name
 * @param   string   $p_dumpfile
 * @param   string   $p_output
 * @param   resource $p_dbLink
 *
 * @return  boolean
 * @author  Dennis Stücken <dstuecken@i-doit.org>
 */
function mysql_import($p_db_name, $p_dumpfile, &$p_output, $p_dbLink = null)
{
    global $g_dbLink;

    if (!is_null($p_dbLink)) {
        $g_dbLink = $p_dbLink;
    }

    $p_output = false;

    if ($g_dbLink) {
        if ($g_dbLink->select_db($p_db_name)) {
            $g_dbLink->query("SET names utf8;");

            $l_dump = file_get_contents($p_dumpfile);
            $l_queries = explode(";\r\n", $l_dump);

            if (count($l_queries) <= 1) {
                $l_queries = explode(";\n", $l_dump);
            }

            if (is_array($l_queries) && count($l_queries) > 1) {
                foreach ($l_queries as $l_line) {
                    $l_query = explode("\n", $l_line);
                    $l_sql = '';

                    foreach ($l_query as $l_value) {
                        if (!preg_match('/[\-]{2}(.*?)/', $l_value)) {
                            $l_sql .= $l_value;
                        }
                    }

                    if (!empty($l_sql)) {
                        $l_sql = trim($l_sql) . ";";

                        if ($l_sql !== ';' && strlen($l_sql) > 1 && !@$g_dbLink->query($l_sql)) {
                            $p_output = '#' . $g_dbLink->errno . ': ' . $g_dbLink->error;

                            return false;
                        }
                    }
                }
            } else {
                $p_output = "SQL-Dump ($p_dumpfile) is not well-formatted. \nMaybe an encoding or line feed problem - Try converting it to CRLF.";

                return false;
            }
        } else {
            $p_output = $g_dbLink->error;
        }
    } else {
        $p_output = 'Could not select database: not connected.';
    }

    return true;
}

/**
 * Adds a mandator to table: isys_mandator in system-database.
 *
 * @param  string   $p_title
 * @param  string   $p_description
 * @param  string   $p_dir_cache
 * @param  string   $p_dir_tpl
 * @param  string   $p_db_host
 * @param  string   $p_db_port
 * @param  string   $p_db_name
 * @param  string   $p_db_user
 * @param  string   $p_db_pass
 * @param  integer  $p_sort
 * @param  string   $p_database
 * @param  resource $p_dbLink
 *
 * @param int       $p_license_objects
 *
 * @return bool|mysqli_result
 * @throws Exception
 */
function add_mandator($p_title, $p_description, $p_dir_cache, $p_dir_tpl, $p_db_host, $p_db_port, $p_db_name, $p_db_user, $p_db_pass, $p_sort = null, $p_database = null, $p_dbLink = null, $p_license_objects = 0)
{
    global $g_dbLink, $g_config;

    if ($p_database === null) {
        $l_system_db = $g_config['config.db.name']['content'];
    } else {
        $l_system_db = $p_database;
    }

    if ($p_dbLink !== null) {
        $g_dbLink = $p_dbLink;
    } else {
        $g_dbLink = new mysqli(
            $g_config['config.db.host']['content'],
            $g_config['config.db.root.username']['content'],
            $g_config['config.db.root.password']['content'],
            $l_system_db,
            $g_config['config.db.port']['content']
        );

        if ($g_dbLink->connect_error) {
            throw new Exception($g_dbLink->connect_error);
        }

        $g_dbLink->query("SET sql_mode=''");
    }

    // Select system database.
    $g_dbLink->select_db($l_system_db);

    $p_title = (string) $p_title;

    $sql = 'INSERT INTO isys_mandator SET
		isys_mandator__title = \'' . $g_dbLink->escape_string($p_title) . '\',
		isys_mandator__description = \'' . $g_dbLink->escape_string($p_title) . '\',
		isys_mandator__dir_cache = \'' . $g_dbLink->escape_string('cache_' . filter_directory_name($p_title)) . '\',
		isys_mandator__dir_tpl = \'' . $g_dbLink->escape_string($p_dir_tpl) . '\',
		isys_mandator__db_host = \'' . $g_dbLink->escape_string($p_db_host) . '\',
		isys_mandator__db_port = \'' . (int) $p_db_port . '\',
		isys_mandator__db_name = \'' . $g_dbLink->escape_string($p_db_name) . '\',
		isys_mandator__db_user = \'' . $g_dbLink->escape_string($p_db_user) . '\',
		isys_mandator__db_pass = NULL,
		isys_mandator__db_password = \'' . $g_dbLink->escape_string(isys_helper_crypt::encrypt($p_db_pass)) . '\',
		isys_mandator__license_objects = \'' . $g_dbLink->escape_string($p_license_objects) . '\',
		isys_mandator__sort = \'' . (int) $p_sort . '\';';

    $return = $g_dbLink->query($sql);

    $tenantId = $g_dbLink->insert_id;

    global $g_dirs, $g_absdir;

    if (!mkdir($concurrentDirectory = $g_dirs['fileman']['image_dir'] . $tenantId) && !is_dir($concurrentDirectory)) {
        return $return;
    }

    if (!mkdir($concurrentDirectory = rtrim($g_absdir, '/') . '/imports/' . $tenantId) && !is_dir($concurrentDirectory)) {
        return $return;
    }

    return $return;
}
