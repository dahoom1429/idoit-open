<?php

use idoit\Console\Command\Idoit\IncrementConfigCommand;
use idoit\Console\IdoitConsoleApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * i-doit
 *
 * Controller for setting the AUTO_INCREMENT value.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_handler_increment_config extends isys_handler
{
    /**
     * Init method.
     *
     * @return  boolean
     */
    public function init()
    {
        global $argv, $g_comp_session;

        if (!isset($argv[0]) || !is_numeric($argv[0]) || !($argv[0] > 1)) {
            verbose('Bitte geben Sie eine Zahl ein, auf die der AUTO_INCREMENT gesetzt werden soll.');

            return true;
        }

        $application = new IdoitConsoleApplication();
        $application->setAutoExit(false);

        $output = new ConsoleOutput();

        $output->writeln('<error>isys_handler_increment_config is deprecated, please use php console.php system-autoincrement instead</error>');

        $commandParams = [
            'command'         => 'system-autoincrement',
            '--autoIncrement' => $argv[0],
            '--user'          => 'loginBefore',
            '--password'      => 'loginBefore',
            '--tenantId'      => 'loginBefore'
        ];

        /**
         * @var $command \idoit\Console\Command\AbstractCommand
         */
        $command = new IncrementConfigCommand();
        $command->setSession($g_comp_session);
        $command->setContainer(\isys_application::instance()->container);
        $command->setAuth(\isys_auth_system::instance());

        $application->add($command);

        $application->run(new ArrayInput($commandParams), $output);

        return true;
    }
}
