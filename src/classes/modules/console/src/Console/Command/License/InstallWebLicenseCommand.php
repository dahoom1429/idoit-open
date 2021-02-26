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

namespace idoit\Module\Console\Console\Command\License;

use idoit\Module\Console\Console\Command\AbstractConfigurableCommand;
use idoit\Module\Console\Option\Option;
use idoit\Module\Console\Option\PasswordOption;
use idoit\Module\Console\Steps\AuthorisationStep;
use idoit\Module\Console\Steps\CollectionStep;
use idoit\Module\Console\Steps\FileSystem\FileDelete;
use idoit\Module\Console\Steps\FileSystem\FileExistsCheck;
use idoit\Module\Console\Steps\License\InstallWebLicense;
use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Step;
use idoit\Module\Console\Steps\TemplateFile;
use isys_application;
use Symfony\Component\Console\Input\InputOption;

class InstallWebLicenseCommand extends AbstractConfigurableCommand
{
    /**
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        global $g_absdir;
        global $g_db_system;
        if (!is_array($g_db_system)) {
            die("config.inc.php is not loaded! Please, install the i-doit first!\n");
        }

        $this->setName('license-key')
            ->setDescription('Set license key for i-doit')
            ->setHelp(
                <<<TEXT
This command sets the license key for i-doit
TEXT
            );

        $this->addValue(new Option(
                            'system.user',
                            'i-doit Admin Username',
                            'admin',
                            new InputOption('user', 'u', InputOption::VALUE_REQUIRED, 'i-doit Admin username')
                        ));
        $this->addValue(new PasswordOption(
                            'system.password',
                            'i-doit Admin Password',
                            null,
                            new InputOption('password', 'p', InputOption::VALUE_OPTIONAL, 'i-doit Admin password'),
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
            new InputOption('key', 'k', InputOption::VALUE_OPTIONAL, 'License key for i-doit')
        ));

        $this->addValue(new Option('directory.config.template', 'Path to the template of config.inc.php', $g_absdir . '/setup/config_template.inc.php'));
        $this->addValue(new Option('directory.config.destination', 'Path to the config.inc.php', $g_absdir . '/src/config.inc.php'));

        parent::configure();
    }

    /**
     * Create the work
     *
     * @return Step
     */
    protected function createStep()
    {
        $db = isys_application::instance()->container->get('database_system');
        global $g_admin_auth, $g_crypto_hash, $g_disable_addon_upload;
        $admin = key($g_admin_auth);
        $steps = [
            new AuthorisationStep($this->getValue('system.user'), $this->getValue('system.password')),
            $steps[] = new CollectionStep('Save Config', [
                new FileExistsCheck($this->getValue('directory.config.template'), true, ErrorLevel::ERROR),
                new FileDelete($this->getValue('directory.config.destination'), true),
                new TemplateFile(
                    'Config File',
                    $this->getValue('directory.config.template'),
                    $this->getValue('directory.config.destination'),
                    [
                        '%config.db.host%'            => $db->get_host(),
                        '%config.db.port%'            => $db->get_port(),
                        '%config.db.username%'        => $db->get_user(),
                        '%config.db.password%'        => $db->get_pass(),
                        '%config.db.name%'            => $db->get_db_name(),
                        '%config.adminauth.username%' => $admin,
                        '%config.adminauth.password%' => $g_admin_auth[$admin],
                        '%config.license.token%' => $this->getValue('license.key'),
                        '%config.crypt.hash%'         => $g_crypto_hash,
                        '%config.admin.disable_addon_upload%' => $g_disable_addon_upload ?: 0
                    ]
                )
            ]),
            new InstallWebLicense(
                $db->get_host(),
                $db->get_user(),
                $db->get_pass(),
                $db->get_db_name(),
                $db->get_port(),
                $this->getValue('license.server'),
                $this->getValue('license.key')
            ),
        ];
        return new CollectionStep('Add license', $steps);
    }
}
