<?php

use idoit\Console\Command\Import\ImportOcsCommand;
use idoit\Console\IdoitConsoleApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * i-doit
 *
 * OCS import handler
 *
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Blümer <dbluemer@i-doit.org>
 * @author     Van Quyen Hoang <qhoang@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 */
class isys_handler_ocs extends isys_handler
{
    /**
     * destroy this
     *
     */
    public function __destruct()
    {
        if (empty($_SERVER['HTTP_HOST'])) {
            $this->logout();
        }
    }

    /**
     * Initialize handler
     *
     * @return bool
     */
    public function init()
    {
        global $argv, $g_comp_session;

        $noMessage = false;

        if ($g_comp_session->is_logged_in()) {
            $application = new IdoitConsoleApplication();
            $application->setAutoExit(false);

            $commandParams = [
                'command'    => 'import-ocs',
                '--user'     => 'loginBefore',
                '--password' => 'loginBefore',
                '--tenantId' => 'loginBefore'
            ];

            if (!empty($_SERVER['HTTP_HOST'])) {
                $output = new BufferedOutput();

                $noMessage = true;

            } else {
                $output = new ConsoleOutput();

                if (is_array($argv)) {
                    $map = [
                        '-t'      => ['--objectType', 1],
                        '-f'      => ['--file', 1],
                        '-h'      => ['--hosts', 1],
                        '-s'      => ['--snmpDevices', 0],
                        '-c'      => ['--categories', 1],
                        '-l'      => ['--logging', 1],
                        '-db'     => ['--databaseSchema', 1],
                        '--help'  => ['--help', 0],
                        '--usage' => ['--usage', 0]
                    ];

                    foreach ($map AS $key => $value) {
                        if (($pos = array_search($key, $argv)) !== false) {
                            $commandParams[$value[0]] = ($value[1]) ? $argv[$pos + $value[1]] : true;
                        }
                    }
                }

            }

            if (!$noMessage) {
                $output->writeln('<error>isys_handler_ocs is deprecated, please use php console.php import-ocs instead</error>');
            }

            /* Process import */
            try {
                /**
                 * @var $command \idoit\Console\Command\AbstractCommand
                 */
                $command = new ImportOcsCommand();
                $command->setSession($g_comp_session);
                $command->setContainer(\isys_application::instance()->container);
                $command->setAuth(\isys_auth_system::instance());

                $application->add($command);

                $application->run(new ArrayInput($commandParams), $output);

                if (!empty($_SERVER['HTTP_HOST'])) {
                    echo $output->fetch();
                }

                return true;
            } catch (Exception $e) {
                error($e->getMessage());
            }

            return true;
        }

        return false;
    }

    /**
     * Overriding default construct
     */
    public function __construct()
    {

    }
}
