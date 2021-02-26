<?php

use idoit\Console\Command\Cleanup\CleanupAuthCommand;
use idoit\Console\IdoitConsoleApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * i-doit
 *
 * Cleanup controller for auth paths
 *
 * @package    i-doit
 * @subpackage General
 * @author     Van Quyen Hoang <qhoang@i.doit.org>
 * @version    1.1
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * @deprecated Use CleanupAuthCommand instead
 */
class isys_handler_cleanup_auth extends isys_handler
{

    public function init()
    {
        global $g_comp_session;

        if ($g_comp_session->is_logged_in()) {
            $application = new IdoitConsoleApplication();
            $application->setAutoExit(false);

            $output = new ConsoleOutput();

            $output->writeln('<error>isys_handler_cleanup_auth is deprecated, please use php console.php auth-cleanup instead</error>');

            $commandParams = [
                'command'    => 'auth-cleanup',
                '--user'     => 'loginBefore',
                '--password' => 'loginBefore',
                '--tenantId' => 'loginBefore'
            ];

            /**
             * @var $command \idoit\Console\Command\AbstractCommand
             */
            $command = new CleanupAuthCommand();
            $command->setSession($g_comp_session);
            $command->setContainer(\isys_application::instance()->container);
            $command->setAuth(\isys_auth_system::instance());

            $application->add($command);

            $application->run(new ArrayInput($commandParams), $output);
        }

        return true;
    }
}
