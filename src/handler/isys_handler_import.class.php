<?php

use idoit\Console\Command\Import\ImportCommand;
use idoit\Console\IdoitConsoleApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * i-doit
 *
 * Import handler
 *
 * @package    i-doit
 * @subpackage Handler
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * @deprecated Use ImportCommand instead
 */
class isys_handler_import extends isys_handler
{
    /**
     * Desctructs this object
     */
    public function __destruct()
    {
        if (empty($_SERVER['HTTP_HOST'])) {
            $this->logout();
        }
    }

    /**
     * Initializes the handler.
     *
     * A login is always needed here because controller.php?load=handler is also reachable from outside (webserver) without any permission checks.
     * To prevent a flood attack or any other malicious attack, change the view permission of controller.php in .htaccess.
     *
     * @return bool
     */
    public function init()
    {
        global $argv, $g_comp_session;

        if ($g_comp_session->is_logged_in()) {
            $application = new IdoitConsoleApplication();
            $application->setAutoExit(false);

            $file = null;
            $type = null;
            $objectType = null;
            $cmd = null;
            $noMessage = false;

            if (!empty($_SERVER['HTTP_HOST'])) {
                $output = new BufferedOutput();
                $file = $_GET['file'];
                $type = $_GET['type'];

                $noMessage = true;
            } else {
                $output = new ConsoleOutput();
                if (is_array($argv)) {
                    $cmd = $argv;
                    $file = $cmd[0];
                    $type = $cmd[1];
                } else {
                    return false;
                }
            }

            if (empty($file)) {
                return false;
            }

            $typeMapping = [
                'cmdb'       => 'import-xml',
                'hinventory' => 'import-hinventory'
            ];

            if (!isset($typeMapping[$type])) {
                return false;
            }

            // Default params
            $commandParams = [
                'command'      => $typeMapping[$type],
                '--importFile' => $file,
                '--user'       => 'loginBefore',
                '--password'   => 'loginBefore',
                '--tenantId'   => 'loginBefore'
            ];

            /**
             * @var $command \idoit\Console\Command\AbstractCommand
             */
            $command = $application->find($typeMapping[$type]);

            if (!$noMessage) {
                $output->writeln('<error>isys_handler_import is deprecated, please use php console.php ' . $typeMapping[$type] . ' instead</error>');
            }

            if (method_exists($command, 'validateParameters')) {
                // Handle specific command parameters in their own command
                if (!$command->validateParameters($commandParams)) {
                    return false;
                }
            }

            $command->setSession($g_comp_session);
            $command->setContainer(\isys_application::instance()->container);
            $command->setAuth(\isys_auth_system::instance());

            $application->add($command);

            $application->run(new ArrayInput($commandParams), $output);

            if (!empty($_SERVER['HTTP_HOST'])) {
                echo $output->fetch();
            }

            return true;
        }

        return false;
    }

    /**
     * Constructs this object
     */
    public function __construct()
    {
        global $g_comp_session, $g_absdir;

        if (!isset($_SERVER['HTTP_HOST']) && !$g_comp_session->is_logged_in()) {
            if (!defined("C__HANDLER__IMPORT")) {
                $exampleFilePath = $g_absdir . '/src/handler/config/examples/isys_handler_import.inc.php';
                $configurationDirectory = $g_absdir . '/src/handler/config';

                error("Import handler configuration not loaded.\nCheck the example in '{$exampleFilePath}' and copy it to '{$configurationDirectory}'.");
            } else {
                error('Please login.');
            }
            exit(1);
        }
    }
}
