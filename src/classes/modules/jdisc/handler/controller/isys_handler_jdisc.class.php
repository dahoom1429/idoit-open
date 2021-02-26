<?php

use idoit\Console\IdoitConsoleApplication;
use idoit\Module\Jdisc\Console\Command\JDiscImportCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * i-doit
 *
 * Handler: Notifications
 *
 * @package     i-doit
 * @subpackage  Handler
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_handler_jdisc extends isys_handler
{
    /**
     * Constant for the "profile" parameter.
     *
     * @var  string
     */
    const C__PROFILE_PARAMETER = '-r';
    /**
     * Constant for the "group" parameter.
     *
     * @var  string
     */
    const C__GROUP_PARAMETER = '-g';
    /**
     * Constant for the "mode" parameter.
     *
     * @var  string
     */
    const C__MODE_PARAMETER = '-x';
    /**
     * Constant for the selected "JDisc Server" parameter.
     *
     * @var  string
     */
    const C__SERVER_PARAMETER = '-s';
    /**
     * Constant which indicates if overlapping host addresses should be overwritten
     *
     * @var string
     */
    const C__HOST_ADDRESS_OVERWRITE = '-o';
    const C__DETAILED_LOGGING       = '-l';
    /**
     * Constant which decides if search index should be regenerated after the import or not
     */
    const C__REGENERATE_INDEX = '-b';

    /**
     * Desctructor
     *
     * @todo  Move it to parent class!?
     */
    public function __destruct()
    {
        $this->logout();
    }

    /**
     * Method for retrieving the handler-title.
     *
     * @return  string
     */
    protected function get_title()
    {
        return 'JDisc import';
    }

    /**
     * Initiates the handler.
     */
    public function init()
    {
        global $argv, $g_comp_session;

        $application = new IdoitConsoleApplication();
        $application->setAutoExit(false);

        $output = new ConsoleOutput();

        $output->writeln('<error>isys_handler_jdisc is deprecated, please use php console.php import-jdisc instead</error>');

        $commandParams = [
            'command'    => 'import-jdisc',
            '--user'     => 'loginBefore',
            '--password' => 'loginBefore',
            '--tenantId' => 'loginBefore'
        ];

        if (array_search(self::C__PROFILE_PARAMETER, $argv) === false) {
            $this->usage();
        }

        // Retrieving the group.
        $l_slice = (array_search(self::C__GROUP_PARAMETER, $argv) !== false) ? array_search(self::C__GROUP_PARAMETER, $argv) + 1 : false;
        if ($l_slice !== false) {
            $l_cmd = array_slice($argv, $l_slice);
            $commandParams['--group'] = $l_cmd[0];
        }

        // Retrieving the profile.
        $l_slice = (array_search(self::C__PROFILE_PARAMETER, $argv) !== false) ? array_search(self::C__PROFILE_PARAMETER, $argv) + 1 : false;
        if ($l_slice !== false) {
            $l_cmd = array_slice($argv, $l_slice);
            $commandParams['--profile'] = $l_cmd[0];
        }

        // Retrieving the jdisc server.
        $l_slice = (array_search(self::C__SERVER_PARAMETER, $argv) !== false) ? array_search(self::C__SERVER_PARAMETER, $argv) + 1 : false;
        if ($l_slice !== false) {
            $l_cmd = array_slice($argv, $l_slice);
            $commandParams['--server'] = $l_cmd[0];
        }

        // Retrieving info if overlapped host addresses should be overwritten or not.
        $commandParams['--overwriteHost'] = (array_search(self::C__HOST_ADDRESS_OVERWRITE, $argv) !== false) ? true : false;

        // Retrieving the mode.
        $l_slice = (array_search(self::C__MODE_PARAMETER, $argv) !== false ? array_search(self::C__MODE_PARAMETER, $argv) + 1 : false);
        if ($l_slice !== false) {
            $l_cmd = array_slice($argv, $l_slice);
            $commandParams['--mode'] = $l_cmd[0];
        }

        // Retrieving indicator if search index should be regenerated
        $commandParams['--regenerateSearchIndex'] = (array_search(self::C__REGENERATE_INDEX, $argv) !== false) ? true : false;

        /**
         * @var $command \idoit\Console\Command\AbstractCommand
         */
        $command = new JDiscImportCommand();
        $command->setSession($g_comp_session);
        $command->setContainer(\isys_application::instance()->container);
        $command->setAuth(\isys_auth_system::instance());

        $application->add($command);

        $application->run(new ArrayInput($commandParams), $output);
    }

    /**
     * Prints out the usage of he import handler.
     */
    public function usage()
    {
        echo "\n" . C__COLOR__LIGHT_RED . "Missing parameters!\n" . C__COLOR__NO_COLOR .
            "You have to use the following parameters in order for the JDisc import to work:\n\n" . "  " . self::C__PROFILE_PARAMETER . " profile-ID\n" . "  " .
            self::C__MODE_PARAMETER . " mode-ID (optional, default: 1)\n" . "  " . self::C__GROUP_PARAMETER . " group-ID (optional)\n\n" . "  " . self::C__SERVER_PARAMETER .
            " jdisc-server-ID (optional)\n\n" . "  " . self::C__HOST_ADDRESS_OVERWRITE . " Indicator for overwriting overlapped host addresses (optional)\n\n" . "  " .
            self::C__DETAILED_LOGGING . " Activate detailed logging (optional, memory intensive)\n\n" . "  " . self::C__REGENERATE_INDEX .
            " Activate regeneration of the search index (optional, memory intensive)\n\n" . "Possible modes are:\n" .
            "  1: Append    				- The import will only create new objects.\n" .
            "  2: Update    				- The import will try to update already existing objects.\n" .
            "  3: Overwrite 				- The import behaves like the update mode but clears all list categories of the existing object.\n" .
            "  4: Update (newly discovered) - The import clears all existing identification keys before the Update mode is triggered.\n" . PHP_EOL;

        die;
    }
}
