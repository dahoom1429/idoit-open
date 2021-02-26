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
use idoit\Module\Console\Option\NumberOption;
use idoit\Module\Console\Option\Option;
use idoit\Module\Console\Option\PasswordOption;
use idoit\Module\Console\Steps\AuthorisationStep;
use idoit\Module\Console\Steps\CollectionStep;
use idoit\Module\Console\Steps\FileSystem\FileExistsCheck;
use idoit\Module\Console\Steps\License\InstallLicense;
use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Step;
use isys_application;
use Symfony\Component\Console\Input\InputOption;

class AddLicenseCommand extends AbstractConfigurableCommand
{
    /**
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        global $g_absdir;
        global $g_db_system;
        global $g_license_token;
        if (!is_array($g_db_system)) {
            die("config.inc.php is not loaded! Please, install the i-doit first!\n");
        }

        $this->setName('license-add')
            ->setDescription('Add license into i-doit')
            ->setHelp(
                <<<TEXT
This command adds the license into i-doit
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
                            'license.file',
                            'Path to license file',
                            null,
                            new InputOption('license', 'l', InputOption::VALUE_REQUIRED, 'Path to license file')
                        ));
        $this->addValue(new NumberOption(
                            'tenant',
                            'Tenant id',
                            null,
                            new InputOption('tenant', 't', InputOption::VALUE_OPTIONAL, 'Tenant id')
                        ));
        $this->addValue(new Option('license.token', 'License Token', $g_license_token));

        parent::configure();
    }

    /**
     * Create the work
     *
     * @return Step
     */
    protected function createStep()
    {
        return new CollectionStep('Add license', [
            new AuthorisationStep($this->getValue('system.user'), $this->getValue('system.password')),
            new FileExistsCheck($this->getValue('license.file'), true, ErrorLevel::ERROR),
            new InstallLicense(
                isys_application::instance()->container->get('database_system'),
                $this->getValue('license.file'),
                $this->getValue('license.token'),
                $this->getValue('tenant')
            ),
        ]);
    }
}
