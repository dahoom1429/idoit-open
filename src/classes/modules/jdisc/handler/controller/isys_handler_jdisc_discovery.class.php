<?php

use idoit\Console\IdoitConsoleApplication;
use idoit\Module\JDisc\Console\Command\JDiscDiscoveryCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * i-doit
 *
 * Handler: JDisc discovery
 *
 * @package     i-doit
 * @subpackage  Handler
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_handler_jdisc_discovery extends isys_handler
{
    /**
     * Constant for the selected "JDisc Server" parameter.
     *
     * @var string
     */
    const C__SERVER_PARAMETER = '-s';

    /**
     * Constant for the selected "Discovery Job" parameter.
     *
     * @var    string
     */
    const C__DISCOVERY_JOB_PARAMETER = '-j';

    /**
     * Constant for the usage of this handler
     *
     * @var string
     */
    const C__HELP_PARAMETER = '-h';

    /**
     * Constant for the selected device by "hostname" parameter.
     */
    const C__DEVICE_PARAMETER_NAME = '-d';

    /**
     * Constant for the selected device by "hostaddress" parameter
     */
    const C__DEVICE_PARAMETER_HOSTADDRESS = '-a';

    /**
     * Constant for showing the log while discovery
     */
    const C__DISCOVERY_SHOW_LOG = '-l';

    /**
     * Log instance for this handler.
     *
     * @var  isys_log
     */
    protected $m_log = null;

    private $m_show_log = false;

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
     * Initiates the handler.
     */
    public function init()
    {
        global $g_comp_session, $argv;
        isys_application::instance()->container->get('language');

        $l_jdisc_server = null;
        $l_device_hostname = null;
        $l_device_hostaddress = null;
        $l_jdisc_discovery_job = "Discover all";

        if (array_search(self::C__HELP_PARAMETER, $argv) !== false) {
            $this->usage();
        }

        // Retrieving the jdisc server.
        $l_slice = (array_search(self::C__SERVER_PARAMETER, $argv) !== false) ? array_search(self::C__SERVER_PARAMETER, $argv) + 1 : false;

        if ($l_slice !== false) {
            $l_cmd = array_slice($argv, $l_slice);
            $l_jdisc_server = $l_cmd[0];
        }

        // Retrieving the discovery job.
        $l_slice = (array_search(self::C__DISCOVERY_JOB_PARAMETER, $argv) !== false) ? array_search(self::C__DISCOVERY_JOB_PARAMETER, $argv) + 1 : false;
        if ($l_slice !== false) {
            $l_cmd = array_slice($argv, $l_slice);
            if (count($l_cmd) > 0) {
                $l_jdisc_discovery_job = trim(implode(' ', $l_cmd));
            } else {
                $l_jdisc_discovery_job = $l_cmd[0];
            }
        }

        // Retrieving the device hostname.
        $l_slice = (array_search(self::C__DEVICE_PARAMETER_NAME, $argv) !== false) ? array_search(self::C__DEVICE_PARAMETER_NAME, $argv) + 1 : false;
        if ($l_slice !== false) {
            $l_cmd = array_slice($argv, $l_slice);
            $l_device_hostname = $l_cmd[0];
        }

        // Retrieving the device hostname.
        $l_slice = (array_search(self::C__DEVICE_PARAMETER_HOSTADDRESS, $argv) !== false) ? array_search(self::C__DEVICE_PARAMETER_HOSTADDRESS, $argv) + 1 : false;
        if ($l_slice !== false) {
            $l_cmd = array_slice($argv, $l_slice);
            $l_device_hostaddress = $l_cmd[0];
        }

        // Retrieving the device hostname.
        $this->m_show_log = (array_search(self::C__DISCOVERY_SHOW_LOG, $argv) !== false) ? true : false;

        $application = new IdoitConsoleApplication();
        $application->setAutoExit(false);

        $output = new ConsoleOutput();

        $output->writeln('<error>isys_handler_jdisc_discovery is deprecated, please use php console.php import-jdiscdiscovery instead</error>');

        $commandParams = [
            'command'        => 'import-jdiscdiscovery',
            '--user'         => 'loginBefore',
            '--password'     => 'loginBefore',
            '--tenantId'     => 'loginBefore',
            '--discoveryJob' => $l_jdisc_discovery_job
        ];

        if ($l_jdisc_server) {
            $commandParams['--server'] = $l_jdisc_server;
        }

        if ($l_device_hostname) {
            $commandParams['--deviceHostname'] = $l_device_hostname;
        }

        if ($l_device_hostaddress) {
            $commandParams['--deviceHostAddress'] = $l_device_hostaddress;
        }

        if ((array_search(self::C__DISCOVERY_SHOW_LOG, $argv) !== false)) {
            $commandParams['--deviceHostAddress'] = true;
        }

        /**
         * @var $command \idoit\Console\Command\AbstractCommand
         */
        $command = new JDiscDiscoveryCommand();
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
        echo "Missing parameters!\n" . "You have to use the following parameters in order for the JDisc import to work:\n\n" . "  " . self::C__DISCOVERY_JOB_PARAMETER .
            " Discovery Job (optional, default: \"Discover all\")\n" . "  " . self::C__SERVER_PARAMETER . " jdisc-server-ID (optional)\n" . "  " .
            self::C__DEVICE_PARAMETER_HOSTADDRESS . " Hostaddress (optional)\n" . "  " . self::C__DEVICE_PARAMETER_NAME . " Hostname (optional)\n" . "  " .
            self::C__DISCOVERY_SHOW_LOG . " show discovery log (optional)\n\n" . "Example:\n" . "./controller -v -m jdisc_discovery -s 1 -j \"Discover all\"\n" .
            "./controller -v -m jdisc_discovery -s 1 -a 127.0.0.1 -l" . PHP_EOL;
        die;
    }
}
