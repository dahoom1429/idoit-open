<?php

use idoit\Console\Command\RegenerateRelationsCommand;
use idoit\Console\IdoitConsoleApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * i-doit
 *
 * Handler for regenerating relation objects
 *
 * @package     i-doit
 * @subpackage  Handler
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * @deprecated  Use RegenerateRelationsCommand instead
 */
class isys_handler_regenerate_relations extends isys_handler
{
    public function init()
    {
        global $argv, $g_comp_session;

        $application = new IdoitConsoleApplication();
        $application->setAutoExit(false);

        $output = new ConsoleOutput();

        $output->writeln('<error>isys_handler_regenerate_relations deprecated, please use php console.php system-objectrelations instead</error>');

        $commandParams = [
            'command'    => 'system-objectrelations',
            '--user'     => 'loginBefore',
            '--password' => 'loginBefore',
            '--tenantId' => 'loginBefore'
        ];

        if (array_search('-h', $argv) !== false) {
            $commandParams['--help'] = true;
        }

        if (($l_start_index = array_search('start', $argv)) === false) {
            $commandParams['--help'] = true;
        }

        if (isset($argv[$l_start_index + 1])) {
            $commandParams['--categoryConstant'] = $argv[$l_start_index + 1];
        }

        /**
         * @var $command \idoit\Console\Command\AbstractCommand
         */
        $command = new RegenerateRelationsCommand();
        $command->setSession($g_comp_session);
        $command->setContainer(\isys_application::instance()->container);
        $command->setAuth(\isys_auth_system::instance());

        $application->add($command);

        $application->run(new ArrayInput($commandParams), $output);

        return true;
    }
}
