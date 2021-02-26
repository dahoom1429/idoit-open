<?php

namespace idoit\Module\Console\Console\Command\Tenant;

use idoit\Module\Console\Console\Command\AbstractConfigurableCommand;
use idoit\Module\Console\Option\Option;
use idoit\Module\Console\Option\PasswordOption;
use idoit\Module\Console\Steps\CollectionStep;
use idoit\Module\Console\Steps\ConstantStep;
use idoit\Module\Console\Steps\Dao\TenantAdd;
use idoit\Module\Console\Steps\Dao\TenantExist;
use idoit\Module\Console\Steps\IfCheck;
use idoit\Module\Console\Steps\Sql\CreateDatabase;
use idoit\Module\Console\Steps\Sql\CreateDatabaseUser;
use idoit\Module\Console\Steps\Sql\DatabaseExist;
use idoit\Module\Console\Steps\Sql\GrantUserOnDatabase;
use idoit\Module\Console\Steps\Sql\ImportDatabaseFromDump;
use idoit\Module\License\LicenseServiceFactory;
use isys_application;
use Symfony\Component\Console\Input\InputOption;

class AddTenantCommand extends AbstractConfigurableCommand
{
    /**
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        parent::configure();
        global $g_absdir;
        global $g_db_system;
        if (!is_array($g_db_system)) {
            die("config.inc.php is not loaded! Please, install the i-doit first!\n");
        }

        $this->setName('tenant-create')
            ->setDescription('Create tenant in i-doit')
            ->setHelp(
                <<<TEXT
This command creates the i-doit tenant with given options
TEXT
            );
        // DB connection
        $this->addValue(new Option(
                            'db.root.user',
                            'Username of priviliged DB User',
                            'root',
                            new InputOption('root-user', 'u', InputOption::VALUE_REQUIRED, 'Username of privileged DB User')
                        ));
        $this->addValue(new PasswordOption(
                            'db.root.password',
                            'Password of priviliged DB User',
                            null,
                            new InputOption('root-password', 'p', InputOption::VALUE_OPTIONAL, 'Password of privileged DB User'),
                            false
                        ));

        $this->addValue(new Option(
                            'db.tenant.user',
                            'Username of DB for new tenant',
                            'idoit',
                            new InputOption('user', 'U', InputOption::VALUE_REQUIRED, 'Username of DB for new tenant')
                        ));
        $this->addValue(new PasswordOption(
                            'db.tenant.password',
                            'Password of DB for new tenant',
                            null,
                            new InputOption('password', 'P', InputOption::VALUE_OPTIONAL, 'Password of DB for new tenant'),
                    false
                        ));
        $this->addValue(new Option(
                            'db.tenant.database',
                            'DB name for new tenant',
                            'idoit_data',
                            new InputOption('database', 'd', InputOption::VALUE_REQUIRED, 'DB name for new tenant')
                        ));
        // Tenant properties
        $this->addValue(new Option(
                            'tenant.name',
                            'Name of the new tenant',
                            'Your company name',
                            new InputOption('title', 't', InputOption::VALUE_REQUIRED, 'Name of the new tenant')
                        ));

        // Configuration options
        $this->addValue(new Option('db.tenant.dump', 'I-doit Tenant Dump path', $g_absdir . '/setup/sql/idoit_data.sql'));
        $this->addValue(new Option('db.host', 'Hostname for DB connection', $g_db_system['host']));
        $this->addValue(new Option('db.port', 'Port for DB connection', $g_db_system['port']));
        $this->addValue(new Option('db.system.user', 'Username of system DB User', $g_db_system['user']));
        $this->addValue(new Option('db.system.password', 'Password of system DB User', $g_db_system['pass']));
        $this->addValue(new Option('db.system.database', 'i-doit System Database name', $g_db_system['name']));
        parent::configure();
    }

    protected function createStep()
    {
        global $g_license_token;

        return new CollectionStep('Create Tenant', [
            new CollectionStep('DB', [
                new IfCheck(
                    'DB Exist',
                    new DatabaseExist(
                        $this->getValue('db.host'),
                        $this->getValue('db.root.user'),
                        $this->getValue('db.root.password'),
                        $this->getValue('db.tenant.database'),
                        $this->getValue('db.port')
                    ),
                    null,
                    new CollectionStep('Create DB', [
                        new CreateDatabase(
                            $this->getValue('db.host'),
                            $this->getValue('db.root.user'),
                            $this->getValue('db.root.password'),
                            $this->getValue('db.tenant.database'),
                            $this->getValue('db.port')
                        ),
                        new ImportDatabaseFromDump(
                            $this->getValue('db.tenant.dump'),
                            $this->getValue('db.host'),
                            $this->getValue('db.root.user'),
                            $this->getValue('db.root.password'),
                            $this->getValue('db.tenant.database'),
                            $this->getValue('db.port')
                        )
                    ])
                ),
                new GrantUserOnDatabase(
                    $this->getValue('db.system.user'),
                    $this->getValue('db.system.password'),
                    $this->getValue('db.tenant.database'),
                    $this->getValue('db.host'),
                    $this->getValue('db.root.user'),
                    $this->getValue('db.root.password'),
                    $this->getValue('db.port')
                ),
                new CreateDatabaseUser(
                    $this->getValue('db.tenant.user'),
                    $this->getValue('db.tenant.password'),
                    $this->getValue('db.host'),
                    $this->getValue('db.root.user'),
                    $this->getValue('db.root.password'),
                    $this->getValue('db.port')
                ),
                new GrantUserOnDatabase(
                    $this->getValue('db.tenant.user'),
                    $this->getValue('db.tenant.password'),
                    $this->getValue('db.tenant.database'),
                    $this->getValue('db.host'),
                    $this->getValue('db.root.user'),
                    $this->getValue('db.root.password'),
                    $this->getValue('db.port')
                ),
            ]),
            new IfCheck(
                'Create Tenant',
                new TenantExist(
                    isys_application::instance()->container->get('database_system'),
                    $this->getValue('tenant.name'),
                    $this->getValue('db.tenant.database')
                ),
                new ConstantStep('Tenant already exists', false),
                new TenantAdd(
                    isys_application::instance()->container->get('database_system'),
                    LicenseServiceFactory::createDefaultLicenseService(
                        isys_application::instance()->container->get('database_system'),
                        $g_license_token
                    ),
                    $this->getValue('tenant.name'),
                    $this->getValue('tenant.description'),
                    $this->getValue('db.host'),
                    $this->getValue('db.port'),
                    $this->getValue('db.tenant.user'),
                    $this->getValue('db.tenant.password'),
                    $this->getValue('db.tenant.database')
                )
            )
        ]);
    }
}
