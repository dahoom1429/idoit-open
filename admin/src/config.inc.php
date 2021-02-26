<?php
/**
 * @author     Dennis Stuecken
 * @package    i-doit
 * @subpackage General
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
global $g_comp_database, $g_config, $g_absdir, $g_comp_database_system, $g_db_system, $g_disable_addon_upload, $g_license_token;

$l_template = isys_component_template::instance();

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'save':

            try {
                $l_result = [
                    'success' => true,
                    'message' => 'Config successfully overwritten.'
                ];

                $l_new_pass = '';

                if ($_POST['admin_password'] != '' && $_POST['admin_password'] != '***') {
                    $l_new_pass = $_POST['admin_password'];
                } else {
                    foreach ($g_admin_auth as $l_username => $l_password) {
                        $l_new_pass = $l_password;
                    }
                }

                if ($_POST['db_pass'] != '' && $_POST['db_pass'] != '***') {
                    $l_db_pass = $_POST['db_pass'];
                } else {
                    $l_db_pass = $g_db_system['pass'];
                }

                /**
                 * Writing backup file
                 */
                if (is_writable($g_absdir . 'src/')) {
                    $l_backupfile = $g_absdir . 'src/config.bak.inc.php';
                    if (@copy($g_absdir . '/src/config.inc.php', $l_backupfile)) {
                        $l_result['message'] .= ' A backup was stored here: ' . $l_backupfile . '. Please move it to a save location.';
                    } else {
                        $l_result['message'] .= ' A backup file could not be created. The apache process may have a file permission problem writing to ' . $l_backupfile;
                    }
                } else {
                    $l_result['message'] .= ' <strong>A backup file could not be created. The apache process has no write permissions to ' . $g_absdir . 'src/</strong>';
                }

                $alreadyHashed = password_get_info($l_new_pass)['algo'];

                try {
                    $l_connectiontest = new isys_component_database_mysqli($_POST['db_host'], $_POST['db_port'], $_POST['db_user'], $l_db_pass, $_POST['db_name']);

                    global $g_crypto_hash;

                    /*
                    // @see ID-4822  Do not simply create a hash if none exists!
                    if (empty($g_crypto_hash)) {
                        $g_crypto_hash = sha1(uniqid('', true));
                    }
                    */

                    write_config($g_absdir . '/setup/config_template.inc.php', $g_absdir . '/src/config.inc.php', [
                        '%config.adminauth.username%' => $_POST['admin_username'],
                        '%config.adminauth.password%' => !$alreadyHashed ? addslashes(\idoit\Component\Security\Hash\Password::instance()
                            ->setPassword($l_new_pass)
                            ->hash()) : $l_new_pass,
                        '%config.db.type%'            => $_POST['db_type'],
                        '%config.db.host%'            => $_POST['db_host'],
                        '%config.db.port%'            => $_POST['db_port'],
                        '%config.db.username%'        => $_POST['db_user'],
                        '%config.db.password%'        => $l_db_pass,
                        '%config.db.name%'            => $_POST['db_name'],
                        '%config.crypt.hash%'         => $g_crypto_hash,
                        '%config.admin.disable_addon_upload%' => $g_disable_addon_upload,
                        '%config.license.token%' => !empty($_POST['license_token']) ? $_POST['license_token'] : $g_license_token
                    ]);
                } catch (isys_exception_database $e) {
                    $l_result['success'] = false;
                    $l_result['message'] = 'Connection check failed! Please review your configuration. ' . $e->getMessage();
                }
            } catch (Exception $e) {
                $l_result['success'] = false;
                $l_result['message'] = $e->getMessage();
            }

            header('Content-Type: application/json');
            echo json_encode($l_result);
            die;

            break;
    }
}

$l_config = [];
$l_configFile = $g_absdir . '/src/config.inc.php';

if (is_writable($l_configFile)) {
    $l_template->assign('configWriteable', true);

    foreach ($g_admin_auth as $l_username => $l_password) {
        $l_config['admin'] = [
            'username' => $l_username,
            'password' => $l_password
        ];
    }

    $l_config['db'] = $g_db_system;
    $l_config['license_token'] = $g_license_token;
} else {
    $l_template->assign('configFilePath', $l_configFile);
}

$l_template->assign('config', $l_config);
$l_template->assign('config_admin_disable_addon_upload', $g_disable_addon_upload);
