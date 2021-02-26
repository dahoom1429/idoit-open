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

namespace idoit\Module\Console\Console\Command\idoit;

use idoit\Component\Security\Hash\Password;
use idoit\Module\Console\Console\Command\AbstractConfigurableCommand;
use idoit\Module\Console\Option\Option;
use idoit\Module\Console\Option\PasswordOption;
use idoit\Module\Console\Steps\CollectionStep;
use idoit\Module\Console\Steps\FileSystem\DirectoryRightsCheck;
use idoit\Module\Console\Steps\FileSystem\FileExistsCheck;
use idoit\Module\Console\Steps\License\InstallWebLicense;
use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\PhpExtensionCheck;
use idoit\Module\Console\Steps\PhpIniCheck;
use idoit\Module\Console\Steps\Sql\CreateDatabase;
use idoit\Module\Console\Steps\Sql\CreateDatabaseUser;
use idoit\Module\Console\Steps\Sql\GrantUserOnDatabase;
use idoit\Module\Console\Steps\Sql\ImportDatabaseFromDump;
use idoit\Module\Console\Steps\TemplateFile;
use idoit\Module\Console\Steps\VersionCheck;
use isys_convert;
use Symfony\Component\Console\Input\InputOption;

class InstallCommand extends AbstractConfigurableCommand
{
    /**
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        global $g_absdir;

        $this->setName('install')
            ->setDescription('Install the i-doit application')
            ->setHelp(
                <<<TEXT
This command initialize the i-doit application with given options
TEXT
            );
        // DB connection
        $this->addValue(new Option(
                            'db.root.user',
                            'Username of priviliged DB User',
                            'root',
                            new InputOption('root-user', 'u', InputOption::VALUE_REQUIRED, 'Username of priviliged DB User')
                        ));
        $this->addValue(new PasswordOption(
                            'db.root.password',
                            'Password of priviliged DB User',
                            null,
                            new InputOption('root-password', 'p', InputOption::VALUE_OPTIONAL, 'Password of priviliged DB User'),
                            false
                        ));
        $this->addValue(new Option(
                            'db.host',
                            'Hostname for DB connection',
                            'localhost',
                            new InputOption('host', null, InputOption::VALUE_REQUIRED, 'Hostname for DB connection')
                        ));
        $this->addValue(new Option('db.port', 'Port for DB connection', 3306, new InputOption('port', null, InputOption::VALUE_REQUIRED, 'Port for DB connection')));

        $this->addValue(new Option(
                            'db.system.database',
                            'i-doit System Database name',
                            'idoit_system_temp',
                            new InputOption('database', 'd', InputOption::VALUE_REQUIRED, 'i-doit System Database name')
                        ));
        $this->addValue(new Option(
                            'db.system.user',
                            'Username of i-doit system DB',
                            'idoit',
                            new InputOption('user', 'U', InputOption::VALUE_REQUIRED, 'Username of i-doit system DB')
                        ));
        $this->addValue(new PasswordOption(
                            'db.system.password',
                            'Password of i-doit system DB',
                            null,
                            new InputOption('password', 'P', InputOption::VALUE_OPTIONAL, 'Password of i-doit system DB'),
                            false
                        ));

        //
        $this->addValue(new Option(
            'system.user',
            'I-doit System User name',
            'admin'/*,
                            new InputOption('admin-user', null, InputOption::VALUE_REQUIRED, 'i-doit Admin name')*/
        ));
        $this->addValue(new PasswordOption(
            'system.password',
            'Password for i-doit admin center',
            null,
            new InputOption('admin-password', null, InputOption::VALUE_OPTIONAL, 'Password for i-doit admin center'),
            false
        ));

        $this->addValue(new Option(
            'license.server',
            'Path for the i-doit license server',
            'https://lizenzen.i-doit.com',
            new InputOption('license-server', 'l', InputOption::VALUE_REQUIRED, 'Path for the i-doit license server')
        ));
        $this->addValue(new PasswordOption(
            'license.key',
            'License key for i-doit',
            null,
            new InputOption('key', 'k', InputOption::VALUE_OPTIONAL, 'License key for i-doit'),
            false
        ));

        // Configuration options
        $this->addValue(new Option('db.system.dump', 'I-doit System Dump path', $g_absdir . '/setup/sql/idoit_system.sql'));
        $this->addValue(new Option('directory.temp', 'Temp path', $g_absdir . '/temp'));
        $this->addValue(new Option('directory.src', 'Source path', $g_absdir . '/src'));
        $this->addValue(new Option('directory.config.template', 'Path to the template of config.inc.php', $g_absdir . '/setup/config_template.inc.php'));
        $this->addValue(new Option('directory.config.destination', 'Path to the config.inc.php', $g_absdir . '/src/config.inc.php'));
        $this->addValue(new Option('environment.random', 'Random Value', sha1(uniqid('', true))));
        parent::configure();
    }

    protected function createStep()
    {
        global $g_disable_addon_upload;
        $steps = [
            new CollectionStep('Environment Check', [
                new CollectionStep('Check directories', [
                    new DirectoryRightsCheck($this->getValue('directory.temp')),
                    new DirectoryRightsCheck($this->getValue('directory.src')),
                ]),
                new CollectionStep('PHP Extensions', [
                    new VersionCheck('PHP', implode('.', [PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION]), [
                        '>=' . PHP_VERSION_MINIMUM,
                        '<=' . PHP_VERSION_MAXIMUM
                    ]),
                    new PhpExtensionCheck('standard'),
                    new PhpExtensionCheck('pcre'),
                    new PhpExtensionCheck('session'),
                    new PhpExtensionCheck('xml'),
                    new PhpExtensionCheck('simplexml'),
                    new PhpExtensionCheck('zlib'),
                    new PhpExtensionCheck('gd'),
                    new PhpExtensionCheck('curl'),
                    new CollectionStep('MySql', [
                        new PhpExtensionCheck('pdo_mysql'),
                        new PhpExtensionCheck('mysqli'),
                        new PhpExtensionCheck('mysqlnd'),
                    ], false)
                ]),
                new CollectionStep('Configuration', [
                    new PhpIniCheck('max_input_vars', function ($value) {
                        return 0 === $value || $value >= 10000;
                    }),
                    new PhpIniCheck('post_max_size', function ($value) {
                        $bytes = isys_convert::to_bytes($value);

                        return 0 === $bytes || $bytes >= isys_convert::to_bytes('128M');
                    })
                ], true, false)
            ]),
            new CollectionStep('System DB', [
                new CreateDatabase(
                    $this->getValue('db.host'),
                    $this->getValue('db.root.user'),
                    $this->getValue('db.root.password'),
                    $this->getValue('db.system.database'),
                    $this->getValue('db.port')
                ),
                new ImportDatabaseFromDump(
                    $this->getValue('db.system.dump'),
                    $this->getValue('db.host'),
                    $this->getValue('db.root.user'),
                    $this->getValue('db.root.password'),
                    $this->getValue('db.system.database'),
                    $this->getValue('db.port')
                ),
                new CreateDatabaseUser(
                    $this->getValue('db.system.user'),
                    $this->getValue('db.system.password'),
                    $this->getValue('db.host'),
                    $this->getValue('db.root.user'),
                    $this->getValue('db.root.password'),
                    $this->getValue('db.port')
                ),
                new GrantUserOnDatabase(
                    $this->getValue('db.system.user'),
                    $this->getValue('db.system.password'),
                    $this->getValue('db.system.database'),
                    $this->getValue('db.host'),
                    $this->getValue('db.root.user'),
                    $this->getValue('db.root.password'),
                    $this->getValue('db.port')
                ),
            ]),
        ];
        $steps[] = new CollectionStep('Save Config', [
            new FileExistsCheck($this->getValue('directory.config.template'), true, ErrorLevel::ERROR),
            new FileExistsCheck($this->getValue('directory.config.destination'), false, ErrorLevel::ERROR),
            new TemplateFile(
                'Config File',
                $this->getValue('directory.config.template'),
                $this->getValue('directory.config.destination'),
                [
                    '%config.db.host%'            => $this->getValue('db.host'),
                    '%config.db.port%'            => $this->getValue('db.port'),
                    '%config.db.username%'        => $this->getValue('db.system.user'),
                    '%config.db.password%'        => $this->getValue('db.system.password'),
                    '%config.db.name%'            => $this->getValue('db.system.database'),
                    '%config.adminauth.username%' => $this->getValue('system.user'),
                    '%config.adminauth.password%' => Password::instance([
                        'password' => $this->getValue('system.password'),
                        'salt'     => $this->getValue('environment.random')
                    ])
                        ->hash(),
                    '%config.license.token%' => $this->getValue('license.key'),
                    '%config.crypt.hash%'         => $this->getValue('environment.random'),
                    '%config.admin.disable_addon_upload%' => $g_disable_addon_upload ?: 0
                ]
            )
        ]);
        if ($this->getValue('license.key')) {
            $steps[] = new InstallWebLicense(
                $this->getValue('db.host'),
                $this->getValue('db.root.user'),
                $this->getValue('db.root.password'),
                $this->getValue('db.system.database'),
                $this->getValue('db.port'),
                $this->getValue('license.server'),
                $this->getValue('license.key')
            );
        }
        return new CollectionStep('i-doit installation', $steps);
    }
}
