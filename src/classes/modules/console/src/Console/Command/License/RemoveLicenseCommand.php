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
use idoit\Module\Console\Steps\License\RemoveLicense;
use idoit\Module\Console\Steps\Step;
use idoit\Module\License\LicenseServiceFactory;
use isys_application;
use Symfony\Component\Console\Input\InputOption;

class RemoveLicenseCommand extends AbstractConfigurableCommand
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

        $this->setName('license-remove')
            ->setDescription('Remove license from i-doit')
            ->setHelp(
                <<<TEXT
This command removes the license from i-doit
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
        $this->addValue(new NumberOption(
            'license.id',
            'License id',
            null,
            new InputOption('license', 'l', InputOption::VALUE_REQUIRED, 'License id')
        ));

        parent::configure();
    }

    /**
     * Create the work
     *
     * @return Step
     */
    protected function createStep()
    {
        global $g_license_token;
        return new CollectionStep('Remove license', [
            new AuthorisationStep($this->getValue('system.user'), $this->getValue('system.password')),
            new RemoveLicense(
                isys_application::instance()->container->get('database_system'),
                $this->getValue('license.id'),
                LicenseServiceFactory::createDefaultLicenseService(
                    isys_application::instance()->container->get('database_system'),
                    $g_license_token
                )
            ),
        ]);
    }
}
