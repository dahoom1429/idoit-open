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

use idoit\Module\Console\Console\Command\AbstractConfigurableCommand;
use idoit\Module\Console\Option\Option;
use idoit\Module\Console\Option\PasswordOption;
use idoit\Module\Console\Steps\AuthorisationStep;
use idoit\Module\Console\Steps\CollectionStep;
use idoit\Module\Console\Steps\Dao\TenantsRemove;
use idoit\Module\Console\Steps\FileSystem\FileDelete;
use idoit\Module\Console\Steps\Sql\DropDatabase;
use idoit\Module\Console\Steps\Step;
use idoit\Module\License\LicenseServiceFactory;
use isys_application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UninstallCommand extends AbstractConfigurableCommand
{
    /**
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        global $g_absdir;

        $this->setName('uninstall')
            ->setDescription('Uninstall the i-doit application')
            ->setHelp(
                <<<TEXT
This command uninstall the i-doit application with given options
TEXT
            );
        //
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

        $database = isys_application::instance()->container->get('database_system');

        global $g_db_system;
        if (!is_array($g_db_system)) {
            die("config.inc.php is not loaded! Please, install the i-doit first!\n");
        }

        // Configuration options
        $this->addValue(new Option('db.host', 'Hostname for DB connection', $g_db_system['host']));
        $this->addValue(new Option('db.port', 'Port for DB connection', $g_db_system['port']));
        $this->addValue(new Option('db.root.user', 'Username of priviliged DB User', $g_db_system['user']));
        $this->addValue(new Option('db.root.password', 'Password of priviliged DB User', $g_db_system['pass']));
        $this->addValue(new Option('db.system.database', 'i-doit System Database name', $g_db_system['name']));
        $this->addValue(new Option('directory.config', 'Path to the config.inc.php', $g_absdir . '/src/config.inc.php'));
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);
        if ($input->isInteractive()) {
            if (!$style->confirm("Your i-doit installation will be <fg=red>completely removed with all tenants data</fg=red>.\nAre you sure?", false)) {
                return -1;
            }
        }
        return parent::execute($input, $output);
    }

    /**
     * @return Step
     */
    protected function createStep()
    {
        global $g_license_token;

        return new CollectionStep('i-doit uninstallation', [
            new AuthorisationStep($this->getValue('system.user'), $this->getValue('system.password')),
            new TenantsRemove(
                isys_application::instance()->container->get('database_system'),
                LicenseServiceFactory::createDefaultLicenseService(isys_application::instance()->container->get('database_system'), $g_license_token)
            ),
            new CollectionStep('Drop System DB', [
                new DropDatabase(
                    $this->getValue('db.host'),
                    $this->getValue('db.root.user'),
                    $this->getValue('db.root.password'),
                    $this->getValue('db.system.database'),
                    $this->getValue('db.port')
                )
            ]),
            new CollectionStep('Remove Config', [
                new FileDelete($this->getValue('directory.config'))
            ])
        ]);
    }
}
