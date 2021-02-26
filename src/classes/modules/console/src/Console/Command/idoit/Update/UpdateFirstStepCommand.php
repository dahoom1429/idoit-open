<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Module\Console\Console\Command\idoit\Update;

use idoit\Module\Console\Steps\AuthorisationStep;
use idoit\Module\Console\Steps\CollectionStep;
use idoit\Module\Console\Steps\ConstantStep;
use idoit\Module\Console\Steps\FileSystem\FileExistsCheck;
use idoit\Module\Console\Steps\FileSystem\FileMove;
use idoit\Module\Console\Steps\FileSystem\Unzip;
use idoit\Module\Console\Steps\IfCheck;
use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\PhpExtensionCheck;
use idoit\Module\Console\Steps\PhpIniCheck;
use idoit\Module\Console\Steps\Step;
use idoit\Module\Console\Steps\TemplateFile;
use idoit\Module\Console\Steps\Update\CopyUpdateFiles;
use idoit\Module\Console\Steps\VersionCheck;
use isys_application;
use isys_convert;
use isys_exception_filesystem;
use isys_update;
use isys_update_log;
use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * First step of the update - check the environment, unzip, copy files
 */
class UpdateFirstStepCommand extends UpdateBase
{
    /**
     *
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('update-1')
            ->setHidden(true);
    }

    /**
     * @return Step
     */
    protected function createStep()
    {
        global $g_absdir;

        include_once $g_absdir . '/updates/constants.inc.php';

        $db = isys_application::instance()->container->get('database_system');

        $g_temp_dir = $g_absdir . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        $g_log_dir = $g_absdir . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR;

        $debugFile = date("Y-m-d") . '-' . $this->getValue('version') . '_idoit_update.log';
        $debugLog = $g_log_dir . $debugFile;

        $log = isys_update_log::get_instance();
        register_shutdown_function(function () use ($log, $debugLog) {
            $log->write_debug(basename($debugLog));
        });

        global $g_admin_auth, $g_crypto_hash, $g_disable_addon_upload, $g_license_token;
        $admin = key($g_admin_auth);

        $environmentChecks = [
            $this->preparePhpChecks(),
            $this->prepareSqlCheck(),
        ];

        $incompatibleAddons = checkIncompatibleAddons();

        if (!empty($incompatibleAddons)) {
            $environmentChecks[] = new ConstantStep('There are incompatible add-ons: ' . implode(', ', $incompatibleAddons), false, ErrorLevel::ERROR);
        }

        $idoitVersion = (new isys_update())->get_isys_info()['version'];

        $updatePath = $this->getValue('update.path') . 'v' . $this->getValue('version');

        return new CollectionStep('i-doit update', [
            new AuthorisationStep($this->getValue('system.user'), $this->getValue('system.password')),
            new CollectionStep('Environment Check', $environmentChecks),
            new CollectionStep('Process update', [
                new VersionCheck('i-doit Version check', $this->getValue('version'), [
                        '>=' . $idoitVersion
                    ], ErrorLevel::ERROR),
                new FileExistsCheck($this->getValue('zip'), true, ErrorLevel::ERROR),
                new Unzip($this->getValue('zip'), $g_absdir),
                new FileExistsCheck($updatePath . '/update_sys.xml', ErrorLevel::ERROR),
                new CopyUpdateFiles($updatePath . '/files'),
                new IfCheck(
                    'Config file should be updated',
                    new FileExistsCheck($updatePath . '/config_template.inc.php', ErrorLevel::ERROR),
                    new CollectionStep('Upgrade config', [
                        new FileMove($this->getValue('directory.config'), $this->getValue('directory.config') . '.' . date("Y-m-d")),
                        new TemplateFile('Config File', $updatePath . '/config_template.inc.php', $this->getValue('directory.config'), [
                                '%config.db.host%'                    => $db->get_host(),
                                '%config.db.port%'                    => $db->get_port(),
                                '%config.db.username%'                => $db->get_user(),
                                '%config.db.password%'                => $db->get_pass(),
                                '%config.db.name%'                    => $db->get_db_name(),
                                '%config.adminauth.username%'         => $admin,
                                '%config.adminauth.password%'         => $g_admin_auth[$admin],
                                '%config.license.token%'              => $g_license_token,
                                '%config.crypt.hash%'                 => $g_crypto_hash,
                                '%config.admin.disable_addon_upload%' => $g_disable_addon_upload ?: 0,
                            ]),
                    ])
                ),
            ])
        ]);
    }

    /**
     * Prepares the checks of Sql version
     *
     * @return Step
     */
    private function prepareSqlCheck()
    {
        $db = isys_application::instance()->container->get('database_system');

        $result = $db->query('SELECT VERSION() AS v;');
        $row = $db->fetch_row_assoc($result);
        $rawDbVersion = $row['v'];
        $dbVersion = getVersion($rawDbVersion);
        $isMariaDb = stripos($rawDbVersion, 'maria') !== false;

        $dbTitle = $isMariaDb ? 'MariaDB' : 'MySQL';
        $dbMinimumVersion = $isMariaDb ? UPDATE_MARIADB_VERSION_MINIMUM : UPDATE_MYSQL_VERSION_MINIMUM;
        $dbMaximumVersion = $isMariaDb ? UPDATE_MARIADB_VERSION_MAXIMUM : UPDATE_MYSQL_VERSION_MAXIMUM;
        $dbRecommendedVersion = $isMariaDb ? UPDATE_MARIADB_VERSION_MINIMUM_RECOMMENDED : UPDATE_MYSQL_VERSION_MINIMUM_RECOMMENDED;

        $checks = [
            new VersionCheck($dbTitle, $dbVersion, [
                '>=' . $dbMinimumVersion,
                '<=' . $dbMaximumVersion
            ]),
            new VersionCheck($dbTitle . ' recommended check', $dbVersion, [
                '>=' . $dbRecommendedVersion,
            ], ErrorLevel::NOTIFICATION)
        ];

        if ($isMariaDb) {
            $checks[] = new VersionCheck($dbTitle . ' deprecated check', $dbVersion, [
                '>' . UPDATE_MARIADB_VERSION_DEPRECATED_BELOW
            ]);
        }

        return new CollectionStep('Sql Check', $checks);
    }

    /**
     * Prepare the checks of the PHP environment
     *
     * @return CollectionStep
     *
     * @throws isys_exception_filesystem
     */
    private function preparePhpChecks()
    {
        $phpVersion = implode('.', [PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION]);

        $configurationChecks = [
            new PhpIniCheck('magic_quotes_gpc: off', function ($value) {
                return !$value;
            }, ErrorLevel::NOTIFICATION),
            new PhpIniCheck('max_input_vars > 10000', function ($value) {
                return 0 === $value || $value >= 10000;
            }, ErrorLevel::NOTIFICATION),
            new PhpIniCheck('post_max_size > 128M', function ($value) {
                $bytes = isys_convert::to_bytes($value);

                return 0 === $bytes || $bytes >= isys_convert::to_bytes('128M');
            }, ErrorLevel::NOTIFICATION)
        ];
        $configurationChecks = array_merge($configurationChecks, array_map(function ($dependency) {
            return new PhpExtensionCheck($dependency);
        }, array_keys(isys_update::get_module_dependencies())));
        $configurationChecks = array_merge($configurationChecks, array_map(function ($dependency) {
            return new PhpExtensionCheck($dependency, ErrorLevel::NOTIFICATION);
        }, array_keys(isys_update::get_module_dependencies(null, 'apache'))));

        return new CollectionStep('PHP Check', [
            new VersionCheck('PHP', $phpVersion, [
                '>=' . UPDATE_PHP_VERSION_MINIMUM,
                '<=' . UPDATE_PHP_VERSION_MAXIMUM
            ]),
            new VersionCheck('PHP deprecated check', $phpVersion, [
                '>=' . UPDATE_PHP_VERSION_DEPRECATED_BELOW,
            ], ErrorLevel::NOTIFICATION),
            new VersionCheck('PHP recommended check', $phpVersion, [
                '>=' . UPDATE_PHP_VERSION_MINIMUM_RECOMMENDED,
            ], ErrorLevel::NOTIFICATION),
            new CollectionStep('Configuration', $configurationChecks, true, false)
        ]);
    }
}
