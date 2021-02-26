<?php

use idoit\Console\Command\Syslog\ParseCommand;
use idoit\Console\IdoitConsoleApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * i-doit
 * Syslog handler
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Blümer <dbluemer@i.doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * @deprecated  Use ParseCommand instead
 */
class isys_handler_syslog extends isys_handler
{
    public function init()
    {
        global $g_comp_session, $g_userconf, $g_strSplitSyslogLine;

        $application = new IdoitConsoleApplication();
        $application->setAutoExit(false);

        $output = new ConsoleOutput();

        $output->writeln('<error>isys_handler_syslog is deprecated, please use php console.php import-syslog instead</error>');

        $commandParams = [
            'command'    => 'import-syslog',
            '--user'     => 'loginBefore',
            '--password' => 'loginBefore',
            '--tenantId' => 'loginBefore'
        ];

        $config = [];

        if (!empty($g_userconf)) {
            $config = $g_userconf;
        }

        if (!empty($g_strSplitSyslogLine)) {
            $config['regexSplitSyslogLine'] = $g_strSplitSyslogLine;
        }

        /**
         * @var $command \idoit\Console\Command\AbstractCommand
         */
        $command = new ParseCommand();
        $command->setSession($g_comp_session);
        $command->setContainer(\isys_application::instance()->container);
        $command->setAuth(\isys_auth_system::instance());
        $command->setConfig($config);

        $application->add($command);

        $application->run(new ArrayInput($commandParams), $output);

        return true;
    }
}
