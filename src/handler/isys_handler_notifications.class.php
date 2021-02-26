<?php

use idoit\Console\Command\Notification\HandleNotificationsCommand;
use idoit\Console\IdoitConsoleApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * i-doit
 *
 * Handler: Notifications
 *
 * @package     i-doit
 * @subpackage  Handler
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * @deprecated  Use HandleNotifications instead
 */
class isys_handler_notifications extends isys_handler
{
    /**
     * Initiates the handler.
     */
    public function init()
    {
        global $g_comp_session;

        $application = new IdoitConsoleApplication();
        $application->setAutoExit(false);

        $output = new ConsoleOutput();

        $output->writeln('<error>isys_handler_notifications is deprecated, please use php console.php notifications-send instead</error>');

        $commandParams = [
            'command'    => 'notifications-send',
            '--user'     => 'loginBefore',
            '--password' => 'loginBefore',
            '--tenantId' => 'loginBefore'
        ];

        /**
         * @var $command \idoit\Console\Command\AbstractCommand
         */
        $command = new HandleNotificationsCommand();
        $command->setSession($g_comp_session);
        $command->setContainer(\isys_application::instance()->container);
        $command->setAuth(\isys_auth_system::instance());

        $application->add($command);

        $application->run(new ArrayInput($commandParams), $output);
    }
}
