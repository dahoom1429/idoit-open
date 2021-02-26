<?php

use idoit\Console\IdoitConsoleApplication;
use idoit\Module\Workflow\Console\Command\MaintenanceCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

if (!defined("C__MAINT__NOT_DAYS")) {
    define("C__MAINT__NOT_DAYS", "+0");
}

/**
 * i-doit
 *
 * Maintenance handler.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * @deprecated  Use MaintenanceCommand instead
 */
class isys_handler_maintenance extends isys_handler
{
    /**
     * @return bool
     */
    public function init()
    {
        global $g_comp_session;

        $application = new IdoitConsoleApplication();
        $application->setAutoExit(false);

        $output = new ConsoleOutput();

        $output->writeln('<error>isys_handler_maintenance is deprecated, please use php console.php system-maintenancecontract instead</error>');

        $commandParams = [
            'command'    => 'system-maintenancecontract',
            '--user'     => 'loginBefore',
            '--password' => 'loginBefore',
            '--tenantId' => 'loginBefore'
        ];

        /**
         * @var $command \idoit\Console\Command\AbstractCommand
         */
        $command = new MaintenanceCommand();
        $command->setSession($g_comp_session);
        $command->setContainer(\isys_application::instance()->container);
        $command->setAuth(\isys_auth_system::instance());

        $application->add($command);

        $application->run(new ArrayInput($commandParams), $output);

        return true;
    }
}
