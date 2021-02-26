<?php

use idoit\Console\Command\Ldap\SyncDistinguishedNamesCommand;
use idoit\Console\IdoitConsoleApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * i-doit
 *
 * This handler synchronizes the LDAP DN String of all persons or objects with the ldap dn category
 *
 * @package        i-doit
 * @subpackage     Handler
 * @author         Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright      synetics GmbH
 * @version        1.1
 * @license        http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * @deprecated     Use SyncDistinguishedNamesCommand instead
 */
class isys_handler_addldapdn extends isys_handler
{
    /**
     * Only for type objects.
     *
     * @var  string
     */
    private $m_ldap_dn_string = '';

    /**
     * Possible options contacts, objects.
     *
     * @var string
     */
    private $m_ldap_dn_type = 'contacts';

    /**
     * Optional: LDAP Server.
     *
     * @var  string
     */
    private $m_ldap_host = '';

    /**
     * Optional: For example C__OBJTYPE__SERVER.
     *
     * @var string
     */
    private $m_obj_type = '';

    /**
     * Initializing method.
     *
     * @return  boolean
     */
    public function init()
    {
        global $argv, $g_comp_session;

        $application = new IdoitConsoleApplication();
        $application->setAutoExit(false);

        $output = new ConsoleOutput();

        $output->writeln('<error>isys_handler_addldapdn is deprecated, please use php console.php ldap-syncdn instead</error>');
        $error = false;

        if ($this->m_ldap_dn_string == '' && $this->m_ldap_dn_type != 'contacts') {
            $output->writeln('<error>Please define variable $m_ldap_dn_string in File isys_handler_addldapdn.class.php. Example OU=Servers,DC=Test,DC=int\n</error>');
            $error = true;
        }

        if ($this->m_ldap_dn_type == '') {
            $output->writeln('<error>Please define variable $m_ldap_dn_type in File isys_handler_addldapdn.class.php. Possible options: objects,contacts\n</error>');
            $error = true;
        }

        $commandParams = [
            'command'    => 'ldap-syncdn',
            '--user'     => 'loginBefore',
            '--password' => 'loginBefore',
            '--tenantId' => 'loginBefore'
        ];

        if (in_array('-h', $argv) || $error) {
            $commandParams['--help'] = true;
        }

        if (empty($this->m_ldap_host) && is_numeric(array_search('-host', $argv))) {
            if (isset($argv[array_search('-host', $argv) + 1])) {
                $commandParams['--ldapServerId'] = $argv[array_search('-host', $argv) + 1];
            }
        }

        if (!empty($this->m_ldap_dn_type)) {
            $commandParams['--dnType'] = $this->m_ldap_dn_type;
        }

        if (!empty($this->m_ldap_dn_string)) {
            $commandParams['--dnString'] = $this->m_ldap_dn_string;
        }

        if (!empty($this->m_obj_type)) {
            $commandParams['--objectType'] = $this->m_obj_type;
        }

        /**
         * @var $command \idoit\Console\Command\AbstractCommand
         */
        $command = new SyncDistinguishedNamesCommand();
        $command->setSession($g_comp_session);
        $command->setContainer(\isys_application::instance()->container);
        $command->setAuth(\isys_auth_system::instance());

        $application->add($command);

        $application->run(new ArrayInput($commandParams), $output);

        return true;
    }
}
