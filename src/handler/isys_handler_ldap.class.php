<?php

use idoit\Console\Command\Ldap\SyncCommand;
use idoit\Console\IdoitConsoleApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * i-doit
 *
 * LDAP handler
 *
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Stücken <dstuecken@i-doit.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * @deprecated Use SyncCommand instead
 *
 */
class isys_handler_ldap extends isys_handler
{
    /**
     * @var  array
     */
    private $m_room = [];

    /**
     * Configuration
     *
     * @var array
     */
    private $m_config = [];

    /**
     * @return  boolean
     */
    public function init()
    {
        global $argv, $g_ldapconf, $g_comp_session;

        $this->m_config = isys_tenantsettings::get('ldap.config', $g_ldapconf);

        if (is_string($this->m_config)) {
            $this->m_config = isys_format_json::decode($this->m_config);
        }

        if (!$this->m_config || $this->m_config === null) {
            $this->m_config = $g_ldapconf;
        }

        if (isset($this->m_config["rooms"])) {
            if (is_array($this->m_config["rooms"])) {
                $this->m_room = $this->m_config["rooms"];
            }
        }

        $application = new IdoitConsoleApplication();
        $application->setAutoExit(false);

        $output = new ConsoleOutput();

        $output->writeln('<error>isys_handler_ldap is deprecated, please use php console.php ldap-sync instead</error>');

        $commandParams = [
            'command'    => 'ldap-sync',
            '--user'     => 'loginBefore',
            '--password' => 'loginBefore',
            '--tenantId' => 'loginBefore'
        ];

        if (isset($argv[1])) {
            $commandParams['--ldapServerId'] = $argv[1];
        }

        /**
         * @var $command \idoit\Console\Command\AbstractCommand
         */
        $command = new SyncCommand();
        $command->setSession($g_comp_session);
        $command->setContainer(\isys_application::instance()->container);
        $command->setAuth(\isys_auth_system::instance());
        $command->setConfig($this->m_config);

        $application->add($command);

        $application->run(new ArrayInput($commandParams), $output);

        return true;
    }
}
