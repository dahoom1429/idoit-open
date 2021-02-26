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
class isys_update_config extends isys_update
{
    /**
     * @var  string
     */
    private $m_config = "config.inc.php";

    /**
     * @var  string
     */
    private $m_config_backup = "";

    /**
     * @var  string
     */
    private $m_template = "config_template.inc.php";

    /**
     * @return  string
     */
    public function get_config_backup()
    {
        return $this->m_config_backup;
    }

    /**
     * @param   string $p_path
     *
     * @return  mixed
     */
    public function backup($p_path)
    {
        $l_log = isys_update_log::get_instance();

        $l_config_path = $p_path . DIRECTORY_SEPARATOR . $this->m_config;
        $l_backup_path = $p_path . DIRECTORY_SEPARATOR . $this->m_config . "." . date('YmdHis');

        $this->m_config_backup = $l_backup_path;

        $l_log->debug("Backing up config: " . $l_config_path);
        $l_log->debug("to: " . $l_backup_path);

        // Creating a backup of the current config.inc.php.
        if (copy($l_config_path, $l_backup_path)) {
            return $l_backup_path;
        } else {
            return false;
        }
    }

    /**
     * @param   string $p_template_path
     *
     * @return  mixed
     */
    public function parse($p_template_path)
    {
        return $this->parseAndUpdateConfig($p_template_path);
    }

    /**
     * @param string $p_template_path
     * @param array $newConfigParams
     * @return bool|false|mixed|string
     */
    public function parseAndUpdateConfig(string $p_template_path, array $newConfigParams = null)
    {
        $l_file = $p_template_path . DIRECTORY_SEPARATOR . $this->m_template;

        // If config_template is existent.
        if (file_exists($l_file)) {
            // Get it as string into stack.
            $l_config = file_get_contents($l_file);

            // Get array with current i-doit config.
            $l_config_array = $this->get_config_array();

            if ($newConfigParams) {
                foreach ($newConfigParams as $paramName => $paramValue) {
                    $l_config_array[$paramName] = $paramValue;
                }
            }

            // Iterate through config array and replace the config template.
            foreach ($l_config_array as $l_key => $l_data) {
                if (!is_array($l_data)) {
                    $l_data = str_replace("\\", "\\\\", $l_data);

                    $l_config = str_replace("%" . $l_key . "%", $l_data, $l_config);
                } else {
                    $l_data = var_export($l_data, true);
                }

                $l_config = str_replace("%" . $l_key . "%", $l_data, $l_config);
            }

            return $l_config;
        } else {
            return false;
        }
    }

    /**
     * @param   string $p_data
     * @param   string $p_path
     *
     * @return  boolean
     */
    public function write($p_data, $p_path)
    {
        // Get Log-Instance.
        $l_log = isys_update_log::get_instance();

        // Path of the new config file.
        $l_new_config_file = $p_path . DIRECTORY_SEPARATOR . $this->m_config;

        // Overwrite existing i-doit config with the new one.
        $l_id = $l_log->add("Writing config to {$l_new_config_file}", C__MESSAGE, "indent");

        if (@isys_file_put_contents($l_new_config_file, $p_data)) {
            // Writing done.
            $l_log->result($l_id, C__DONE);

            return true;
        } else {
            // Writing failed.
            $l_log->result($l_id, C__ERR);

            return false;
        }
    }

    /**
     * @return  array
     */
    private function get_config_array()
    {
        global $g_db_system, $g_admin_auth, $g_crypto_hash, $g_disable_addon_upload, $g_license_token;

        $l_admin = [
            'user' => 'admin',
            'pass' => ''
        ];

        foreach ($g_admin_auth as $l_user => $l_pass) {
            $l_admin['user'] = $l_user;
            $l_admin['pass'] = $l_pass;
        }

        // @todo
        //if (empty($g_crypto_hash)) {
        //    $g_crypto_hash = sha1(uniqid('', true));
        //}

        return [
            "config.adminauth.username" => $l_admin['user'],
            "config.adminauth.password" => $l_admin['pass'],
            "config.db.host"            => $g_db_system["host"],
            "config.db.port"            => $g_db_system["port"],
            "config.db.username"        => $g_db_system["user"],
            "config.db.password"        => $g_db_system["pass"],
            "config.db.name"            => $g_db_system["name"],
            "config.db.type"            => $g_db_system["type"],
            "config.crypt.hash"         => $g_crypto_hash,
            "config.license.token"         => $g_license_token,
            "config.admin.disable_addon_upload" => $g_disable_addon_upload ?: 0
        ];
    }
}
