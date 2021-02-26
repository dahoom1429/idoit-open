<?php

/**
 * i-doit - Updates
 *
 * @package    i-doit
 * @subpackage Update
 * @author     Dennis Stücken <dstuecken@i-doit.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_update_xml extends isys_update
{
    private $m_nodes;

    /**
     * Gets the sql update information from the specified xml file
     * The filepath should not be relative
     *
     * @author Dennis Stücken <dstuecken@i-doit.de>
     * @return array
     */
    public function load_xml($p_file, $p_do_version_change = false)
    {
        $l_statements = [];
        $l_log = isys_update_log::get_instance();

        if (is_file($p_file)) {
            $l_xml = simplexml_load_file($p_file, 'SimpleXMLElement', LIBXML_NOCDATA);
            $l_log->debug("Loading XML-File: " . $p_file);

            if (isset($l_xml->queries->query)) {
                foreach ($l_xml->queries->query as $l_query) {
                    $l_log->debug(" - Processing XML-Node: " . strval($l_query->title) . " (" . strval($l_query->id) . ")");

                    $l_statements[] = [
                        "id"       => $l_query->id,
                        "title"    => $l_query->title,
                        "check"    => $l_query->check,
                        "errormsg" => $l_query->errormsg,
                        "sql"      => $l_query->sql,
                        "catg"     => $l_query->catg,
                        "cats"     => $l_query->cats
                    ];
                }
            } else {
                $l_log->debug("No query found in XML file.");
            }

            if ($p_do_version_change && isset($l_xml->info->version) && isset($l_xml->info->revision)) {
                $l_statements[] = $this->db_init_statement($l_xml);
            }

        } else {
            $l_log->debug("Error: XML-File: " . $p_file . " not found.");

            return false;
        }

        return $l_statements;
    }

    /**
     * @param simplexml $p_xml
     *
     * @return array
     */
    private function db_init_statement(&$p_xml)
    {

        $l_revision = $p_xml->info->revision;
        $l_version = $p_xml->info->version;

        $l_data = "
		<query>
			<id>9999</id>
			<title>Version change</title>
			<check ident=\"C_UPDATE\">isys_db_init</check>
			<errmsg></errmsg>
			<sql>
				<exec ident=\"true\">
					UPDATE `isys_db_init` SET `isys_db_init__value` = '" . $l_revision . "' WHERE `isys_db_init__key` = 'revision';
					UPDATE `isys_db_init` SET `isys_db_init__value` = '" . $l_version . "' WHERE `isys_db_init__key` = 'version';
					UPDATE `isys_db_init` SET `isys_db_init__value` = 'i-doit " . $l_version . "' WHERE `isys_db_init__key` = 'title';
				</exec>
			</sql>
		</query>";

        $l_query = new SimpleXMLElement($l_data);

        $l_statement = [
            "id"       => $l_query->id,
            "title"    => $l_query->title,
            "check"    => $l_query->check,
            "errormsg" => $l_query->errormsg,
            "sql"      => $l_query->sql,
            "catg"     => $l_query->catg,
            "cats"     => $l_query->cats
        ];

        return $l_statement;
    }
}

?>