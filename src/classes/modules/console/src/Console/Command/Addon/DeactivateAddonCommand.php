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

namespace idoit\Module\Console\Console\Command\Addon;

use idoit\Module\Console\Console\Command\AbstractConfigurableCommand;
use idoit\Module\Console\Option\ArrayOption;
use idoit\Module\Console\Option\NumberOption;
use idoit\Module\Console\Option\Option;
use idoit\Module\Console\Option\PasswordOption;
use idoit\Module\Console\Steps\Addon\AddonDeactivate;
use idoit\Module\Console\Steps\Addon\IsAddonInstalled;
use idoit\Module\Console\Steps\AuthorisationStep;
use idoit\Module\Console\Steps\CollectionStep;
use idoit\Module\Console\Steps\Dao\TenantExistById;
use idoit\Module\Console\Steps\Step;
use isys_application;
use isys_component_dao_mandator;
use isys_component_database;
use isys_module_manager;
use Symfony\Component\Console\Input\InputOption;

class DeactivateAddonCommand extends AbstractConfigurableCommand
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

        $this->setName('addon-deactivate')
            ->setDescription('Deactivate add-on')
            ->setHelp(
                <<<TEXT
This command deactivates the add-on into i-doit
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
        $this->addValue(new ArrayOption(new Option('addon', 'Add-on identifier', null, new InputOption('addon', 'a', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Add-on identifier'))));
        $this->addValue(new ArrayOption(new NumberOption('tenant', 'Tenant id', null, new InputOption('tenant', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Tenant id'))));

        parent::configure();
    }

    /**
     * Create the work
     *
     * @return Step
     */
    protected function createStep()
    {
        global $g_absdir;

        $db = isys_application::instance()->container->get('database_system');
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

        return new CollectionStep(
            'Deactivate add-on',
            array_merge(
                [new AuthorisationStep($this->getValue('system.user'), $this->getValue('system.password'))],
                array_reduce(array_map(function ($addon) use ($db, $tenantIds, $tenantDao) {
                    return array_map(function ($tenantId) use ($db, $addon, $tenantDao) {
                        $tenant = $tenantDao->get_mandator($tenantId, 0)->get_row();
                        if (!is_array($tenant)) {
                            return new TenantExistById($db, $tenantId);
                        }

                        $tenantDb = isys_component_database::get_database(
                            'mysql',
                            $tenant["isys_mandator__db_host"],
                            $tenant["isys_mandator__db_port"],
                            $tenant["isys_mandator__db_user"],
                            isys_component_dao_mandator::getPassword($tenant),
                            $tenant["isys_mandator__db_name"]
                        );
                        $moduleManager = new isys_module_manager($tenantDb);
                        return new CollectionStep('Deactivate ' . $addon . ' for ' . $tenantId, [
                            new CollectionStep('Check', [
                                new TenantExistById($db, $tenantId),
                                new IsAddonInstalled($addon, $moduleManager),
                            ]),
                            new AddonDeactivate($addon, $tenantId, $moduleManager),
                        ]);
                    }, $tenantIds);
                }, $addons), 'array_merge', [])
            )
        );
    }
}
