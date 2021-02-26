<?php

/**
 * i-doit
 *
 * Logbook archiving handler
 *
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Blümer <dbluemer@i.doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 */

use idoit\Console\Command\Logbook\ArchiveCommand;
use idoit\Console\IdoitConsoleApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class isys_handler_archivelog
 *
 * @deprecated Use ArchiveCommand instead
 */
class isys_handler_archivelog extends isys_handler
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

        $output->writeln('<error>isys_handler_archivelog is deprecated, please use php console.php logbook-archive instead</error>');

        $commandParams = [
            'command'    => 'logbook-archive',
            '--user'     => 'loginBefore',
            '--password' => 'loginBefore',
            '--tenantId' => 'loginBefore'
        ];

        /**
         * @var $command \idoit\Console\Command\AbstractCommand
         */
        $command = new ArchiveCommand();
        $command->setSession($g_comp_session);
        $command->setContainer(\isys_application::instance()->container);
        $command->setAuth(\isys_auth_system::instance());

        $application->add($command);

        $application->run(new ArrayInput($commandParams), $output);

        return true;
    }
}
