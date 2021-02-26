<?php
/**
 * @author      Dennis Stuecken
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

use idoit\Module\Cmdb\Model\CiTypeCategoryAssigner;

/**
 * @param  $p_tenant_id
 */
function connect_mandator($p_tenant_id)
{
    global $g_db_system, $l_dao_mandator;

    $l_licence_mandator = $l_dao_mandator->get_mandator($p_tenant_id, 0);
    $l_dbdata = $l_licence_mandator->get_row();

    // Create connection to mandator DB.
    return isys_component_database::get_database(
        $g_db_system["type"],
        $l_dbdata["isys_mandator__db_host"],
        $l_dbdata["isys_mandator__db_port"],
        $l_dbdata["isys_mandator__db_user"],
        isys_component_dao_mandator::getPassword($l_dbdata),
        $l_dbdata["isys_mandator__db_name"]
    );
}

/**
 * Install a module zip file.
 *
 * @param   string $p_moduleFile
 * @param   string $p_tenant Input '0' for all.
 *
 * @throws  Exception
 * @return  boolean
 */
function install_module_by_zip($p_moduleFile, $p_tenant = null)
{
    global $g_absdir;

    // Checking for zlib and the ZipArchive class to solve #4853
    if (!class_exists('ZipArchive') || !extension_loaded('zlib')) {
        throw new Exception('Error: Could not extract zip file. Please check if the zip and zlib PHP extensions are installed.');
    }

    if (!(new isys_update_files())->read_zip($p_moduleFile, $g_absdir, false, true)) {
        throw new Exception('Error: Could not read zip package.');
    }

    if (!file_exists($g_absdir . '/package.json')) {
        throw new Exception('Error: package.json was not found.');
    }

    $l_package = json_decode(file_get_contents($g_absdir . '/package.json'), true);

    /**
     * Start module installation
     */
    $l_result = install_module($l_package, $p_tenant);

    // Move package.
    if (file_exists($g_absdir . '/src/classes/modules/' . $l_package['identifier'] . '/package.json')) {
        unlink($g_absdir . '/src/classes/modules/' . $l_package['identifier'] . '/package.json');
        rename($g_absdir . '/package.json', $g_absdir . '/src/classes/modules/' . $l_package['identifier'] . '/package.json');
    } else {
        rename($g_absdir . '/package.json', $g_absdir . '/src/classes/modules/' . $l_package['identifier'] . '/package.json');
    }

    return $l_result;
}

/**
 * Install module by it's identifier.
 *
 * @param array   $p_packageJSON
 * @param integer $p_tenant
 *
 * @return boolean
 * @throws Exception
 * @throws isys_exception_general
 */
function install_module(array $p_packageJSON, $p_tenant = null)
{
    /**
     * Initialize
     */
    global $g_absdir, $g_product_info, $l_dao_mandator, $g_comp_database_system, $g_comp_database, $g_dcs;
    $l_db_update = new isys_update_xml();

    $l_tenants = [];

    if (isset($p_packageJSON['requirements']['core'])) {
        $l_requirements = explode(' ', $p_packageJSON['requirements']['core']);

        if (!isset($l_requirements[1])) {
            throw new Exception('Invalid package.json format. Could not read requirements');
        }

        $l_current_version = $g_product_info['version'];
        $l_version_requirement = $l_requirements[1];
        $l_operator = $l_requirements[0];

        if (!version_compare($l_current_version, $l_version_requirement, $l_operator)) {
            switch ($l_requirements[0]) {
                case '>=':
                    throw new Exception(sprintf(
                        'Error: i-doit Version requirement for this add-on does not match: Core %s. Update to version %s and try again.',
                        $p_packageJSON['requirements']['core'],
                        $l_requirements[1]
                    ));
                    break;
                case '<=':
                    throw new Exception(sprintf(
                        'Error: i-doit Version requirement for this add-on does not match: Core %s. Update to version %s and try again.',
                        $p_packageJSON['requirements']['core'],
                        $l_requirements[1]
                    ));
                    break;
            }
        }
    } else {
        throw new Exception('Invalid package.json format. Core requirement missing');
    }

    if (isset($p_packageJSON['dependencies']['php']) && is_array($p_packageJSON['dependencies']['php'])) {
        foreach ($p_packageJSON['dependencies']['php'] as $l_dependency) {
            /**
             * @todo Remove this special mysql handling if it is not needed anymore
             */
            if ($l_dependency === 'mysql' && version_compare(PHP_VERSION, '5.6') === 1) {
                if (!extension_loaded('mysqli') && !extension_loaded('mysqlnd')) {
                    throw new Exception(sprintf('Error: PHP extension mysqli or mysqlnd needed for this add-on. Please install the extension and try again.'));
                }
            } else {
                if (!extension_loaded($l_dependency)) {
                    throw new Exception(sprintf('Error: PHP extension %s needed for this add-on. Please install the extension and try again.', $l_dependency));
                }
            }
        }
    }

    // Prepare mandator array.
    if ($p_tenant) {
        $l_tenants = [$p_tenant];
    } else {
        $l_tenant_result = $l_dao_mandator->get_mandator();

        while ($l_row = $l_tenant_result->get_row()) {
            $l_tenants[] = $l_row['isys_mandator__id'];
        }
    }

    // Include module installscript if available.
    if (file_exists($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install/isys_module_' . $p_packageJSON['identifier'] . '_install.class.php')) {
        include_once($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install/isys_module_' . $p_packageJSON['identifier'] . '_install.class.php');
    }

    // Delete files if necessary.
    if (file_exists($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install/update_files.xml')) {
        (new isys_update_files())->delete($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install');
    }

    // Iterate through prepared mandators and install module into each of them.
    foreach ($l_tenants as $tenantId) {
        if ($tenantId > 0) {
            // Connect mandator database $g_comp_database.
            $mandatorDatabase = connect_mandator($tenantId);

            /**
             * Module manager needs to be initialized for each tenant because it is possible that a new tenant
             * has been added and the isys_module entry does not exists for the uploaded module.
             *
             * @see ID-3547
             */

            // Install module with package.
            $moduleId = (new isys_module_manager($mandatorDatabase))->installAddOn($p_packageJSON);

            // Update Databases.
            if (file_exists($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install/update_data.xml')) {
                $l_db_update->update_database($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install/update_data.xml', $mandatorDatabase);
            }

            if (file_exists($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install/update_sys.xml')) {
                $l_db_update->update_database($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install/update_sys.xml', $g_comp_database_system);
            }

            // When a package.json already exists, this is an update.
            if (file_exists($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/package.json')) {
                $type = 'update';
            } else {
                $type = 'install';
            }

            $moduleClassName = 'isys_module_' . $p_packageJSON['identifier'];
            $updateSettings = false;

            if (class_exists($moduleClassName) && is_a($moduleClassName, 'idoit\AddOn\InstallableInterface', true)) {
                $moduleClassName::install($mandatorDatabase, $g_comp_database_system, $moduleId, $type, $tenantId);
                $updateSettings = true;
            } else {
                // Call module installscript if available.
                $l_installclass = 'isys_module_' . $p_packageJSON['identifier'] . '_install';
                if (class_exists($l_installclass)) {
                    call_user_func([$l_installclass, 'init'], $mandatorDatabase, $g_comp_database_system, $moduleId, $type, $tenantId);

                    $updateSettings = true;
                }
            }

            if ($updateSettings && is_object($g_comp_database_system)) {
                // Set installdate in system settings
                $sql = "REPLACE INTO isys_settings SET 
                    isys_settings__key = 'admin.module." . $p_packageJSON['identifier'] . ".installed', 
                    isys_settings__value = '" . time() . "', 
                    isys_settings__isys_mandator__id = '" . $tenantId . "';";
                $g_comp_database_system->query($sql);

                // Mark this tenant that the properties have to be renewed
                $sql = "REPLACE INTO isys_settings SET 
                    isys_settings__key = 'cmdb.renew-properties', 
                    isys_settings__value = 1, 
                    isys_settings__isys_mandator__id = '" . $tenantId . "';";
                $g_comp_database_system->query($sql);

                // @see ID-6684 Always remove duplicated category assignments after add-on installation (noticed via CMK2-16).
                (new CiTypeCategoryAssigner($mandatorDatabase))->deleteDuplicateAssignments();
            }
        }
    }

    // Delete cache.
    $l_deleted = [];
    $l_undeleted = [];
    isys_glob_delete_recursive(isys_glob_get_temp_dir(), $l_deleted, $l_undeleted);

    // Re-Create constant cache.
    $g_dcs = isys_component_constant_manager::instance()
        ->create_dcs_cache();

    return true;
}

/**
 * Replace config $p_config_location with template $p_config_template and data from $p_data (key => value).
 *
 * @param  string $p_config_template
 * @param  string $p_config_location
 * @param  array  $p_data
 *
 * @return bool|int
 * @throws Exception
 */
function write_config($p_config_template, $p_config_location, $p_data = [])
{
    if (file_exists($p_config_template)) {
        if (is_writable(dirname($p_config_location))) {
            return isys_file_put_contents($p_config_location, strtr(file_get_contents($p_config_template), $p_data));
        }

        throw new Exception('Config file ' . $p_config_location . ' is not writeable.');
    }

    throw new Exception('Config template ' . $p_config_template . ' dies not exist.');
}

/**
 * @param string $licenseToken
 *
 * @return void
 *
 * @throws Exception
 */
function saveLicenseToken($licenseToken)
{
    global $g_absdir, $g_db_system, $g_admin_auth, $g_crypto_hash, $g_disable_addon_upload, $g_license_token, $licenseService;

    // Update config
    write_config($g_absdir . '/setup/config_template.inc.php', $g_absdir . '/src/config.inc.php', [
        '%config.adminauth.username%' => array_keys($g_admin_auth)[0],
        '%config.adminauth.password%' => $g_admin_auth[array_keys($g_admin_auth)[0]],
        '%config.db.type%'            => $g_db_system['type'],
        '%config.db.host%'            => $g_db_system['host'],
        '%config.db.port%'            => $g_db_system['port'],
        '%config.db.username%'        => $g_db_system['user'],
        '%config.db.password%'        => $g_db_system['pass'],
        '%config.db.name%'            => $g_db_system['name'],
        '%config.crypt.hash%'         => $g_crypto_hash,
        '%config.admin.disable_addon_upload%' => $g_disable_addon_upload,
        '%config.license.token%' => $licenseToken
    ]);

    $g_license_token = $licenseToken;
    $licenseService->setEncryptionToken($licenseToken);
}
