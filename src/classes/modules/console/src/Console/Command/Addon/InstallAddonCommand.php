<?php

namespace idoit\Module\Console\Console\Command\Addon;

use idoit\Module\Console\Console\Command\AbstractConfigurableCommand;
use idoit\Module\Console\Option\ArrayOption;
use idoit\Module\Console\Option\NumberOption;
use idoit\Module\Console\Option\Option;
use idoit\Module\Console\Option\PasswordOption;
use idoit\Module\Console\Steps\Addon\ExtractAddonIdentifierFromPackage;
use idoit\Module\Console\Steps\Addon\InstallAddon;
use idoit\Module\Console\Steps\AuthorisationStep;
use idoit\Module\Console\Steps\CollectionStep;
use idoit\Module\Console\Steps\FileSystem\FileDelete;
use idoit\Module\Console\Steps\FileSystem\FileExistsCheck;
use idoit\Module\Console\Steps\FileSystem\FileMove;
use idoit\Module\Console\Steps\FileSystem\Unzip;
use idoit\Module\Console\Steps\IfCheck;
use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Step;
use isys_application;
use isys_component_constant_manager;
use isys_component_dao_mandator;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallAddonCommand extends AbstractConfigurableCommand
{
    /**
     *
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        global $g_absdir;
        global $g_db_system;

        if (!is_array($g_db_system)) {
            die("config.inc.php is not loaded! Please, install the i-doit first!\n");
        }

        $this->setName('addon-install')
            ->setDescription('Install add-on')
            ->setHelp(
                <<<TEXT
This command installs the add-on for i-doit tenants
TEXT
            );

        $this->addValue(new Option('system.user', 'i-doit Admin Username', 'admin', new InputOption('user', 'u', InputOption::VALUE_REQUIRED, 'i-doit Admin username')));
        $this->addValue(new PasswordOption(
            'system.password',
            'i-doit Admin Password',
            null,
            new InputOption('password', 'p', InputOption::VALUE_OPTIONAL, 'i-doit Admin password'),
            false
        ));
        $this->addValue(new Option('addon.zip', 'Path to add-on Zip file', null, new InputOption('zip', 'z', InputOption::VALUE_OPTIONAL, 'Path to add-on Zip file')));
        $this->addValue(new ArrayOption(new Option(
            'addon',
            'Add-on identifier',
            null,
            new InputOption('addon', 'a', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Add-on identifier')
        )));
        $this->addValue(new ArrayOption(new NumberOption(
            'tenant',
            'Tenant id',
            null,
            new InputOption('tenant', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Tenant id')
        )));

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
        $zip = $this->getValue('addon.zip');
        $addons = $this->getValue('addon');
        $tenantIds = $this->getValue('tenant');
        $tenantDao = isys_component_dao_mandator::instance($db);

        if (empty($tenantIds)) {
            $tenantIds = [];
            $res = $tenantDao->get_mandator();

            while ($tenant = $res->get_row()) {
                $tenantIds[] = $tenant['isys_mandator__id'];
            }
        }

        global $g_absdir;

        $installStep = $zip ? new CollectionStep('Install add-on via Zip', [
            new FileExistsCheck($zip, true, ErrorLevel::FATAL),
            new Unzip($zip, $g_absdir),
            new FileExistsCheck($g_absdir . '/package.json', true, ErrorLevel::ERROR),
            new ExtractAddonIdentifierFromPackage($g_absdir . '/package.json', function ($addon, $messages) use ($tenantIds, $db, $g_absdir) {
                $from = $g_absdir . '/package.json';
                $to = $g_absdir . '/src/classes/modules/' . $addon . '/package.json';

                return (new CollectionStep('Install', [
                    new IfCheck('Remove existing file', new FileExistsCheck($to), new FileDelete($to)),
                    new FileMove($from, $to),
                    new InstallAddon($addon, $tenantIds, $db)
                ]))->process($messages);
            })
        ]) : new CollectionStep('Install add-ons', array_map(function ($addon) use ($db, $tenantIds) {
            return new InstallAddon($addon, $tenantIds, $db);
        }, $addons));

        return new CollectionStep('Install add-on', [
            new AuthorisationStep($this->getValue('system.user'), $this->getValue('system.password')),
            $installStep
        ]);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $result = parent::execute($input, $output);
        // Delete cache.
        $l_deleted = [];
        $l_undeleted = [];
        isys_glob_delete_recursive(isys_glob_get_temp_dir(), $l_deleted, $l_undeleted);

        // Re-Create constant cache.
        $g_dcs = isys_component_constant_manager::instance()
            ->create_dcs_cache();

        return $result;
    }
}
