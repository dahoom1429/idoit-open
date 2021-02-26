<?php

namespace idoit\Module\Console\Console\Command\Tenant;

use idoit\Module\Console\Option\Option;
use idoit\Module\Console\Option\PasswordOption;
use idoit\Module\Console\Steps\AuthorisationStep;
use idoit\Module\Console\Steps\CollectionStep;
use idoit\Module\Console\Steps\Dao\TenantRemove;
use idoit\Module\License\LicenseServiceFactory;
use isys_application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RemoveTenantCommand extends AbstractTenantCommand
{
    /**
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('tenant-remove')
            ->setDescription('Remove the i-doit Tenant')
            ->setHelp(
                <<<TEXT
This command removes the i-doit Tenant application with given id
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

        parent::configure();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|mixed|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);
        if ($input->isInteractive()) {
            if (!$style->confirm('Are you sure you want to delete the tenant? <fg=red>All tenant data will be removed</fg=red>', false)) {
                return -1;
            }
        }

        return parent::execute($input, $output);
    }

    protected function createStep()
    {
        global $g_license_token;

        return new CollectionStep('Remove Tenant', [
            new AuthorisationStep($this->getValue('system.user'), $this->getValue('system.password')),
            new TenantRemove(
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
