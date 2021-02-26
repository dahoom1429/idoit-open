<?php

namespace idoit\Module\Console\Console\Command\Tenant;

use idoit\Module\Console\Option\Option;
use idoit\Module\Console\Option\PasswordOption;
use idoit\Module\Console\Steps\AuthorisationStep;
use idoit\Module\Console\Steps\CollectionStep;
use idoit\Module\Console\Steps\Dao\TenantDisable;
use idoit\Module\Console\Steps\Step;
use idoit\Module\License\LicenseServiceFactory;
use isys_application;
use Symfony\Component\Console\Input\InputOption;

class DisableTenantCommand extends AbstractTenantCommand
{
    protected function configure()
    {
        $this->setName('tenant-disable')
            ->setDescription('Disables the tenant with specific id');

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

        parent::configure();
    }

    /**
     * @return Step
     */
    protected function createStep()
    {
        global $g_license_token;

        return new CollectionStep('Disable Tenant', [
            new AuthorisationStep($this->getValue('system.user'), $this->getValue('system.password')),
            new TenantDisable(
                isys_application::instance()->container->get('database_system'),
                $this->getValue('tenant.id'),
                LicenseServiceFactory::createDefaultLicenseService(
                    isys_application::instance()->container->get('database_system'),
                    $g_license_token
                )
            )
        ]);
    }
}
