<?php
/**
 * i-doit
 *
 * Update checker
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stuecken <dstuecken@i.doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 */

use idoit\Console\Command\Idoit\UpdateCheckCommand;
use idoit\Console\IdoitConsoleApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class isys_handler_updatecheck
 */
class isys_handler_updatecheck extends isys_handler
{
    /**
     * Init method.
     *
     * @return  boolean
     */
    public function init()
    {
        $application = new IdoitConsoleApplication();
        $application->setAutoExit(false);

        $output = new ConsoleOutput();

        $output->writeln('<error>isys_handler_updatecheck is deprecated, please use php console.php system-checkforupdates instead</error>');

        $commandParams = [
            'command' => 'system-checkforupdates'
        ];

        /**
         * @var $command \idoit\Console\Command\AbstractCommand
         */
        $command = new UpdateCheckCommand();
        $command->setContainer(\isys_application::instance()->container);

        $application->add($command);

        $application->run(new ArrayInput($commandParams), $output);

        return true;
    }

    /**
     *
     * @return  boolean
     */
    public function needs_login()
    {
        return false;
    }
}
