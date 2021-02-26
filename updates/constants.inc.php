<?php

// ------------- Configuration -------------
define("C__XML__SYSTEM", "update_sys.xml");
define("C__XML__DATA", "update_data.xml");
define("C__CHANGELOG", "CHANGELOG");
define("C__DIR__FILES", "files/");
define("C__DIR__MIGRATION", "migration/");
define("C__DIR__MODULES", "modules/");

/* Defining minimum php version for this update */
$versionConstants = [
    // PHP version requirements
    'UPDATE_PHP_VERSION_MINIMUM' => '7.2.5',
    'UPDATE_PHP_VERSION_DEPRECATED_BELOW' => '7.2.4',
    'UPDATE_PHP_VERSION_MINIMUM_RECOMMENDED' => '7.3',
    'UPDATE_PHP_VERSION_MAXIMUM' => '7.4.99',

    // MariaDB version requirements
    'UPDATE_MARIADB_VERSION_MINIMUM' => '10.1',
    'UPDATE_MARIADB_VERSION_DEPRECATED_BELOW'=> '10.2',
    'UPDATE_MARIADB_VERSION_MAXIMUM' => '10.4.99',
    'UPDATE_MARIADB_VERSION_MINIMUM_RECOMMENDED' => '10.4',

    // MySQL version requirements
    'UPDATE_MYSQL_VERSION_MINIMUM' => '5.6.0',
    'UPDATE_MYSQL_VERSION_MAXIMUM' => '5.7.99',
    'UPDATE_MYSQL_VERSION_MINIMUM_RECOMMENDED' => '5.7'
];


// Create undefined version constants
foreach ($versionConstants as $versionConstant => $versionValue) {
    // Check whether constant is already defined
    if (!defined($versionConstant)) {
        // Define it!
        define($versionConstant, $versionValue);
    }
}

/**
 * Define version check related functions
 */

if (!function_exists('checkVersion')) {
    /**
     * Check whether version meets requirements
     * defined by minimum and maximum information
     *
     * Please provide comparable version values
     * to guarantee valid handling. Therefore
     * you can use getVersion().
     *
     * @param string $version
     * @param string $minVersion
     * @param string $maxVersion
     *
     * @return bool
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    function checkVersion($version, $minVersion, $maxVersion)
    {
        return (version_compare($version, $minVersion, '>=') && version_compare($version, $maxVersion, '<='));
    }
}

if (!function_exists('checkVersionIsAbove')) {
    /**
     * Check whether version is above max version
     *
     * Please provide comparable version values
     * to guarantee valid handling. Therefore
     * you can use getVersion().
     *
     * @param string $version
     * @param string $maxVersion
     *
     * @return mixed
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    function checkVersionIsAbove($version, $maxVersion)
    {
        return version_compare($version, $maxVersion, '>');
    }
}

if (!function_exists('getVersion')) {
    /**
     * Get cleaned version string
     *
     * Some operating systems add specific stuff
     * to phpversion() and mysql which disrupts version
     * comparisan of version_compare()
     *
     * @param string $version Supposed to be the output of phpversion()
     *
     * @return string
     * @throws Exception
     */
    function getVersion($version)
    {
        // Ensure php version without os related stuff
        if (preg_match('/^\d[\d.]*/', $version, $matches) === 1) {
            return $matches[0];
        }

        // Let executer handle exceptions
        throw new Exception('Unable to determine valid version by given version information: \'' . $version . '\'');
    }
}

if (!function_exists('unpackAddon')) {
    /**
     * Function for unpacking an add-on.
     *
     * This might be necessary, if a add-on HAS TO BE UPDATED during an i-doit update.
     * For example when "add-onizing" some functionality we initially use this
     * to force the add-on installation (in the next best major update).
     *
     * This function is almost identical to "install_module_by_zip" from
     * "<i-doit>/admin/src/functions.inc.php" but will not perform any database actions.
     *
     * @param string $packageZip
     *
     * @return bool
     * @throws Exception
     */
    function unpackAddon($packageZip)
    {
        global $g_absdir;

        // Checking for zlib and the ZipArchive class to solve #4853
        if (!class_exists('ZipArchive') || !extension_loaded('zlib')) {
            throw new Exception('Error: Could not extract zip file. Please check if the zip and zlib PHP extensions are installed.');
        }

        // Unzip the package.
        if (!(new isys_update_files())->read_zip($packageZip, $g_absdir, false, true)) {
            throw new Exception('Error: Could not read zip package.');
        }

        // Check if the package.json is available.
        if (!file_exists($g_absdir . '/package.json')) {
            throw new Exception('Error: package.json was not found.');
        }

        $l_package = json_decode(file_get_contents($g_absdir . '/package.json'), true);

        // Move package.json to the add-on directory.
        rename($g_absdir . '/package.json', $g_absdir . '/src/classes/modules/' . $l_package['identifier'] . '/package.json');

        return true;
    }
}

if (!function_exists('checkIncompatibleAddons')) {
    function checkIncompatibleAddons()
    {
        // @see  ID-4172  Stop the update progress if one of the following modules (and version) could be found.
        // @see  ID-4456  Only stop the update if PHP7 is in use.
        global $g_dirs;

        // Checks if the following add-ons exist in the given version number.
        $incompatibleAddOns = [
            'viva2' => '2.0.1',
        ];

        $needToUpdate = [];

        foreach ($incompatibleAddOns as $identifier => $addOnVersion) {
            $packageJsonPath = $g_dirs['class'] . '/modules/' . $identifier . '/package.json';

            if (!file_exists($packageJsonPath)) {
                continue;
            }

            $packageJson = json_decode(file_get_contents($packageJsonPath), true);

            if (!is_array($packageJson)) {
                continue;
            }

            if (version_compare($packageJson['version'], $addOnVersion, '<')) {
                $needToUpdate[] = ($packageJson['title'] ?: ucfirst($identifier)) . ' (at least version ' . $addOnVersion . ')';
            }
        }

        return $needToUpdate;
    }
}
