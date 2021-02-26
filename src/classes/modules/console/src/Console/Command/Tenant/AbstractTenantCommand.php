<?php

namespace idoit\Module\Console\Console\Command\Tenant;

use idoit\Module\Console\Console\Command\AbstractConfigurableCommand;
use idoit\Module\Console\Option\Option;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractTenantCommand extends AbstractConfigurableCommand
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

        $this->addValue(new Option(
                            'tenant.id',
                            'Tenant Id',
                            null,
                            new InputOption('tenant', 'i', InputOption::VALUE_REQUIRED, 'Tenant Id')
                        ));

        parent::configure();
    }
}
