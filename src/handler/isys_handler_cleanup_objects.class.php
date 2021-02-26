<?php

use idoit\Console\Command\Cleanup\ObjectCleanupCommand;
use idoit\Console\IdoitConsoleApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * i-doit
 *
 * Handler: Cleanup objects
 *
 * @package     i-doit
 * @subpackage  Handler
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * @deprecated  Use ObjectCleanupCommand instead
 */
class isys_handler_cleanup_objects extends isys_handler
{
    public function init()
    {
        global $argv, $g_comp_session;

        $slice = array_search('-t', $argv) + 1;
        $cmd = array_slice($argv, $slice);
        $type = trim($cmd[0]);

        $application = new IdoitConsoleApplication();
        $application->setAutoExit(false);

        $output = new ConsoleOutput();

        $output->writeln('<error>isys_handler_cleanup_objects is deprecated, please use php console.php system-objectcleanup instead</error>');

        $commandParams = [
            'command'    => 'system-objectcleanup',
            '--user'     => 'loginBefore',
            '--password' => 'loginBefore',
            '--tenantId' => 'loginBefore'
        ];

        if ($type) {
            $commandParams['--objectStatus'] = $type;
        }

        /**
         * @var $command \idoit\Console\Command\AbstractCommand
         */
        $command = new ObjectCleanupCommand();
        $command->setSession($g_comp_session);
        $command->setContainer(\isys_application::instance()->container);
        $command->setAuth(\isys_auth_system::instance());

        $application->add($command);

        $application->run(new ArrayInput($commandParams), $output);

        return true;
    }
}
