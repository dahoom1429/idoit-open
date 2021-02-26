<?php

namespace idoit\Module\Search\Controller;

use idoit\Console\Command\Search\IndexerCommand;
use idoit\Console\IdoitConsoleApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * i-doit cmdb object controller
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Reindex implements \isys_controller
{
    /**
     * @var \isys_module_search
     */
    private $module;

    /**
     * @param \isys_application $p_application
     *
     * @return \isys_cmdb_dao_nexgen
     */
    public function dao(\isys_application $p_application)
    {
        return new \isys_cmdb_dao_nexgen($p_application->database);
    }

    /**
     * @action /search/reindex
     *
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     * @todo   Still used ?
     */
    public function handle(\isys_register $p_request, \isys_application $p_application)
    {
        /**
         * @todo create queue entry to postpone index creation
         */
        {
            $application = new IdoitConsoleApplication();
            $application->setAutoExit(false);

            $output = new NullOutput();

            /**
             * @var $command \idoit\Console\Command\AbstractCommand
             */
            $command = new IndexerCommand();
            $command->setSession(\isys_application::instance()->container->session);
            $command->setContainer(\isys_application::instance()->container);
            $command->setAuth(\isys_auth_system::instance());

            $application->add($command);

            $commandParams = [
                'command'    => 'SearchIndexer',
                '--user'     => 'loginBefore',
                '--password' => 'loginBefore',
                '--tenantId' => 'loginBefore',
                '--reindex'  => ''
            ];

            $application->run(new ArrayInput($commandParams), $output);

            \isys_notify::info('Index created.');
        }
    }

    /**
     * @param \isys_register       $p_request
     * @param \isys_application    $p_application
     * @param \isys_component_tree $p_tree
     *
     * @return null
     */
    public function tree(\isys_register $p_request, \isys_application $p_application, \isys_component_tree $p_tree)
    {
        return null;
    }

    /**
     * Index constructor.
     *
     * @param \isys_module_search $p_module
     */
    public function __construct(\isys_module $p_module)
    {
        $this->module = $p_module;
    }

}
