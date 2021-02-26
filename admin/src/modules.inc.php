<?php
/**
 * @author     Dennis Stuecken
 * @package    i-doit
 * @subpackage General
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

global $g_comp_database, $g_license_token, $g_config, $g_absdir, $g_comp_database_system, $g_product_info, $l_dao_mandator, $g_disable_addon_upload;

try {
    $l_template = isys_component_template::instance();
    $l_dao_mandator = new isys_component_dao_mandator($g_comp_database_system);

    switch ($_REQUEST['action']) {
        case 'lazyinstall':
            try {
                if (isset($_POST['module']) && isset($_POST['tenant'])) {
                    // @see ID-6236 It is no longer necessary to include the CMDB-Autoloader.

                    $l_package = json_decode(file_get_contents($g_absdir . '/src/classes/modules/' . $_POST['module'] . '/package.json'), true);

                    $l_response = [
                        'success' => install_module($l_package, $_POST['tenant'] ?: null),
                        'message' => 'Add-on installed/updated successfully'
                    ];
                } else {
                    throw new Exception('Request error: Add-on & tenant not received.');
                }
            } catch (Exception $e) {
                $l_response = [
                    'error'   => $e->getMessage(),
                    'success' => false
                ];
            }

            header('Content-Type: application/json');
            echo json_encode($l_response);
            die;

        case 'add':
            try {
                if ($g_disable_addon_upload == 1) {
                    throw new \Exception('Error: You disabled uploading of Add-ons in your src/config.inc.php');
                }

                if (!isset($_POST['mandator'])) {
                    throw new Exception('Error: Select a module');
                }

                if (!isset($_FILES['module_file'])) {
                    throw new Exception("Error: Package upload failed. Doublecheck the following php.ini settings against your uploaded package: \nfile_uploads, post_max_size, upload_max_filesize, upload_tmp_dir");
                }

                if ($_FILES['module_file']['tmp_name']) {
                    // @see ID-6236 It is no longer necessary to include the CMDB-Autoloader.

                    if (install_module_by_zip($_FILES['module_file']['tmp_name'], $_POST['mandator'])) {
                        $l_template->assign("message", 'Add-on successfully installed.');
                    }
                } else {
                    switch ($_FILES['module_file']['error']) {
                        case UPLOAD_ERR_OK:
                            break;
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $message = 'file too large - limit of ' . ini_get('upload_max_filesize') .
                                ' reached, check upload_max_filesize and post_max_size in php.ini';
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $message = 'file upload was not completed';
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $message = 'zero-length file uploaded';
                            break;
                        default:
                            $message = 'internal error #' . $_FILES['newfile']['error'];
                            break;
                    }

                    throw new Exception('Error: There has been an error while uploading the file (' . $message . ')');
                }
            } catch (Exception $e) {
                $l_template->assign("error", nl2br($e->getMessage()));
            }
            break;

        case 'deactivate':
            if (isset($_REQUEST['module']) && is_array($_REQUEST['module'])) {
                foreach ($_REQUEST['module'] as $tenant => $modules) {
                    if ($tenant) {
                        $mandatorDatabase = connect_mandator($tenant);
                        $moduleManager = new isys_module_manager($mandatorDatabase);

                        foreach ($modules as $moduleIdentifier) {
                            if ($moduleIdentifier) {
                                $moduleManager->deactivateAddOn($moduleIdentifier);
                            }
                        }
                    }
                }
            }
            break;

        case 'uninstall':
            try {
                $l_moduleList = [];
                $l_mandatorDBs = [];

                if (isset($_REQUEST['module']) && is_array($_REQUEST['module'])) {
                    foreach ($_REQUEST['module'] as $l_tenant => $l_modules) {
                        if ($l_tenant) {
                            foreach ($l_modules as $l_module) {
                                if ($l_module) {
                                    $l_moduleList[] = $l_module;
                                }
                            }
                        }
                    }

                    if (count($l_moduleList) > 0) {
                        $l_tenants = $l_dao_mandator->get_mandator();
                        while ($l_row = $l_tenants->get_row()) {
                            $l_mandatorDBs[] = connect_mandator($l_row['isys_mandator__id']);
                        }

                        if (count($l_mandatorDBs) > 0) {
                            // @see ID-6236 It is no longer necessary to include the CMDB-Autoloader.

                            $l_failedToUninstall = [];

                            $l_module_manager = new isys_module_manager($l_mandatorDBs[0]);

                            foreach ($l_moduleList as $l_moduleToDelete) {
                                $errorMessages = [];
                                if (!$l_module_manager->uninstallAddOn($l_moduleToDelete, $l_mandatorDBs, $errorMessages)) {
                                    $l_file = $g_absdir . '/src/classes/modules/' . $l_moduleToDelete . '/package.json';

                                    if (file_exists($l_file) && is_readable($l_file)) {
                                        $l_package = json_decode(file_get_contents($l_file), true);

                                        if (is_array($l_package)) {
                                            $l_failedToUninstall[] = ($l_package['title'] ?: ($l_package['name'] ?: ucfirst($l_moduleToDelete))) . ' ' .
                                                implode(', ', $errorMessages);
                                        }
                                    } else {
                                        $l_failedToUninstall[] = ucfirst($l_moduleToDelete) . ' ' . implode(', ', $errorMessages);
                                    }
                                }
                            }

                            if (count($l_failedToUninstall)) {
                                throw new Exception('Failed to uninstall following modules: ' . implode(', ', $l_failedToUninstall) . '. Please check the log files.');
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                $l_template->assign("error", nl2br($e->getMessage()));
            }
            break;

        case 'activate':
            $l_db_update = new isys_update_xml();
            if (isset($_REQUEST['module']) && is_array($_REQUEST['module'])) {
                foreach ($_REQUEST['module'] as $l_tenant => $modules) {
                    $mandatorDatabase = connect_mandator($l_tenant);
                    $moduleManager = new isys_module_manager($mandatorDatabase);

                    foreach ($modules as $moduleIdentifier) {
                        if ($moduleIdentifier) {
                            $moduleManager->activateAddOn($moduleIdentifier);

                            if (file_exists($g_absdir . '/src/classes/modules/' . $moduleIdentifier . '/install/update_data.xml')) {
                                $l_db_update->update_database($g_absdir . '/src/classes/modules/' . $moduleIdentifier . '/install/update_data.xml', $mandatorDatabase);
                            }

                            if (file_exists($g_absdir . '/src/classes/modules/' . $moduleIdentifier . '/install/update_sys.xml')) {
                                $l_db_update->update_database($g_absdir . '/src/classes/modules/' . $moduleIdentifier . '/install/update_sys.xml', $g_comp_database_system);
                            }
                        }
                    }
                }
            }
            break;
    }

    /**
     * Get mandators
     */
    $l_tenants = $l_dao_mandator->get_mandator();

    /**
     * Checks if file is open version identifier
     *
     * @param $file
     *
     * @return bool
     */
    function isOpenModuleIdentifier($file)
    {
        return $file === 'open';
    }

    /**
     * Checks if file is open version identifier
     *
     * @param $file
     *
     * @return bool
     */
    function isProModuleIdentifier($file)
    {
        return $file === 'pro';
    }

    /**
     * Initialize modules
     */
    $l_directory = $g_absdir . '/src/classes/modules/';
    $l_modules = [];
    $i = 0;

    // @see  ID-6834  Only work with licenses in context of i-doit PRO.
    if (defined('C__MODULE__PRO') && C__MODULE__PRO && class_exists('idoit\\Module\\License\\LicenseServiceFactory')) {
        $licenseService = idoit\Module\License\LicenseServiceFactory::createDefaultLicenseService($g_comp_database_system, $g_license_token);
    }

    while ($l_tenant = $l_tenants->get_row()) {
        $mandatorDatabase = connect_mandator($l_tenant['isys_mandator__id']);

        $l_module_manager = new isys_module_manager($mandatorDatabase);

        $l_dirhandle = opendir($l_directory);
        $l_modules[$i] = [
            'id'       => $l_tenant['isys_mandator__id'],
            'title'    => $l_tenant['isys_mandator__title'],
            'active'   => $l_tenant['isys_mandator__active'],
            'licenced' => false,
            'expires'  => $l_tenant['isys_licence__expires'],
            'host'     => $l_tenant['isys_mandator__db_host'],
            'db'       => $l_tenant['isys_mandator__db_name']
        ];

        while (($l_file = readdir($l_dirhandle)) !== false) {
            if (is_dir($l_directory . $l_file) && strpos($l_file, '.') !== 0 && !isOpenModuleIdentifier($l_file)) {
                if (file_exists($l_directory . $l_file . '/package.json')) {
                    $l_package = json_decode(file_get_contents($l_directory . $l_file . '/package.json'), true);
                    $l_module_id = $l_module_manager->is_installed($l_package['identifier']);

                    // Pro module is always active since it does not need any database entries
                    if (isProModuleIdentifier($l_file)) {
                        $l_package['installed'] = 2;
                        $l_package['active'] = true;
                        $l_package['version'] = $g_product_info['version'];
                    } else {
                        // Don't show core add-ons anymore (see ID-4099), except pro module
                        if ($l_package['type'] === 'core') {
                            continue;
                        }
                    }

                    if ($l_package && $l_module_id) {
                        $l_package['active'] = $l_module_manager->is_active($l_package['identifier']);
                        $l_package['id'] = $l_module_id;
                        $l_package['data'] = $l_module_manager->get_modules($l_package['id'])
                            ->get_row();
                        $l_package['update'] = ($l_package['type'] !== 'core' &&
                            filemtime($l_directory . $l_file . '/package.json') > strtotime($l_package['data']['isys_module__date_install']));

                        $l_package['installed'] = 1;

                        $l_modules[$i]['modules'][] = $l_package;
                    } else {
                        if (!isProModuleIdentifier($l_file)) {
                            $l_package['installed'] = 0;
                        }

                        $l_modules[$i]['modules'][] = $l_package;
                    }
                }
            }
        }

        // @see  ID-6834  Only work with licenses in context of i-doit PRO.
        if (defined('C__MODULE__PRO') && C__MODULE__PRO) {
            $l_modules[$i]['licenced'] = $licenseService->isTenantLicensed($l_tenant['isys_mandator__id']);
        }

        $i++;
        closedir($l_dirhandle);
        unset($l_dirhandle);

        $l_tenants_array[] = $l_tenant;
    }

    $l_template->assign('modules', $l_modules);
    $l_template->assign('g_disable_addon_upload', $g_disable_addon_upload);

    if (isset($l_tenants_array)) {
        $l_template->assign('mandators', $l_tenants_array);
    }
} catch (Exception $e) {
    $l_response = [
        "error"   => true,
        "message" => $e->getMessage()
    ];

    header("Content-Type: application/json");
    echo json_encode($l_response);
    die;
}
