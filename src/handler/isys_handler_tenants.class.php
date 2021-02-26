<?php

use idoit\Console\Command\Cmdb\TenantsCommand;
use idoit\Console\IdoitConsoleApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * i-doit
 *
 * Tenant handler
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 */
class isys_handler_tenants extends isys_handler
{
    /**
     * @param   array $p_params
     *
     * @return  array
     */
    public function parse($p_params)
    {
        $l_ret = [];

        $fieldMapping = [
            'title'       => '--title',
            'description' => '--description',
            'dir_cache'   => '--cacheDirectory',
            'dir_tpl'     => '--tplDirectory',
            'db_host'     => '--host',
            'db_port'     => '--port',
            'db_user'     => '--user',
            'db_pass'     => '--password',
            'sort'        => '--sort',
            'active'      => '--active',
            'lang_const'  => '', // not used anymore
            'lang_short'  => '' // not used anymore
        ];

        if (is_array($p_params)) {
            foreach ($p_params as $l_value) {
                $l_tmp = explode("=", $l_value);

                if (isset($fieldMapping[$l_tmp[0]])) {
                    $l_ret[$fieldMapping[$l_tmp[0]]] = $l_tmp[1];
                }
            }
        }

        return $l_ret;
    }

    /**
     * @return  mixed
     */
    public function login()
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            die("Running this from a webbrowser is prohibited for security reasons!");
        }
    }

    /**
     * @return  mixed
     */
    public function init()
    {
        global $argv;

        $methodMapping = [
            'ls'         => 'tenant-list',
            'add'        => 'tenant-add',
            'activate'   => 'tenant-enable',
            'deactivate' => 'tenant-disable'
        ];

        $method = isset($methodMapping[$argv[0]]) ? $methodMapping[$argv[0]] : $methodMapping['ls'];
        $additionalArguments = $argv[1];

        $application = new IdoitConsoleApplication();
        $application->setAutoExit(false);

        $output = new ConsoleOutput();

        $output->writeln('<error>isys_handler_tenants is deprecated, please use php console.php ' . $method . ' instead</error>');

        $commandParams = [
            'command' => $method
        ];

        if ($additionalArguments > 0) {
            $commandParams['--tenantId'] = $additionalArguments;
        } elseif (!empty($additionalArguments)) {

            $additionalArguments = $this->parse($argv);

            if (isset($additionalArguments['--active'])) {
                $additionalArguments['--active'] = (bool)$additionalArguments['--active'];
            }

            $commandParams += $additionalArguments;
        }

        if (!isset($methodMapping[$argv[0]])) {
            verbose("Wrong usage. I need at least one parameter");
            $this->usage();

            return false;
        }

        if ($method == 'tenant-add' && (empty($additionalArguments["--host"]) || empty($additionalArguments["--port"]) || empty($additionalArguments["--user"]) ||
                empty($additionalArguments["--title"]))) {
            $this->usage_add();

            return false;
        }

        /**
         * @var $command \idoit\Console\Command\AbstractCommand
         */
        $command = $application->find($method);
        $command->setContainer(\isys_application::instance()->container);

        $application->add($command);

        $application->run(new ArrayInput($commandParams), $output);

        return true;
    }

    /**
     * Method for defining, if this handler needs the i-doit login.
     *
     * @return  boolean
     */
    public function needs_login()
    {
        return false;
    }

    /**
     * Display the usage-info.
     */
    public function usage()
    {
        error("Usage: ./tenant parameter [tenant-id]\n" . "Parameters: activate, deactivate, ls\n\n" . "ls         - list current tenants with status\n" .
            "activate   - activates an inactive tenant\n" . "deactivate - deactivates an active tenant\n");
    }

    /**
     * Display additional usage-info.
     */
    public function usage_add()
    {
        echo "Adding tenant:\n\n" . " ./tenant add option1=value [option2=value] [..]\n" . " Options:\n\n" . "  title=Title\n" . "  db_host=localhost\n" . "  db_port=3306\n" .
            "  db_user=root\n" . "  [db_pass=password]\n" . "  [lang_const=ISYS_LANG_GERMAN]\n" . "  [lang_short=de]\n" . "  [description=Description]\n" . "  [sort=10]\n" .
            "  [active=1]\n\n" . "The values in this example are the default values.\n";
    }
}
