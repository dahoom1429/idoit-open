<?php

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * i-doit
 *
 * Search index controller
 *
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Stücken <dstuecken@i-doit.com>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * @deprecated Use IndexerCommand or SearchCommand instead
 *
 */
class isys_handler_search_index extends isys_handler
{
    /**
     * Show usage information
     */
    public function usage($p_error = false)
    {
        echo "\n\n" . C__COLOR__LIGHT_RED . "Create a search index for search auto-suggestion.\n" . C__COLOR__NO_COLOR;

        if ($p_error) {
            echo "\n" . C__COLOR__LIGHT_RED . "Missing parameter!\n" . C__COLOR__NO_COLOR;
        }

        echo "\nUsage:\n" . "index - build a complete full-text search index.\n\n" .
            "search \"keyword\" - search within the index.\n\n" . "Example:\n" . "controller -v -m search_index index\n" .
            PHP_EOL;
        die;
    }

    /**
     * Initialize.
     */
    public function init()
    {
        global $argv, $g_comp_session;

        $functionToCommand = [
            'reindex'   => [
                'class'   => 'idoit\Console\Command\Search\IndexerCommand',
                'command' => 'search-index'
            ],
            'fullindex' => [
                'class'   => 'idoit\Console\Command\Search\IndexerCommand',
                'command' => 'search-index'
            ],
            'index' => [
                'class'   => 'idoit\Console\Command\Search\IndexerCommand',
                'command' => 'search-index'
            ],
            'search'    => [
                'class'   => 'idoit\Console\Command\Search\SearchCommand',
                'command' => 'search'
            ]
        ];

        if (!is_countable($argv) || count($argv) === 0 || !isset($functionToCommand[$argv[0]])) {
            $this->usage();
        } else {

            $application = new \idoit\Console\IdoitConsoleApplication();
            $application->setAutoExit(false);

            if (!class_exists($functionToCommand[$argv[0]]['class'])) {
                $this->usage();

                return;
            }

            $output = new ConsoleOutput();
            $output->writeln(sprintf('<error>isys_handler_search_index is deprecated, please use php console.php %s instead</error>',
                strtolower($functionToCommand[$argv[0]]['command'])));

            $className = $functionToCommand[$argv[0]]['class'];

            /**
             * @var $command \idoit\Console\Command\AbstractCommand
             */
            $command = new $className();
            $command->setSession($g_comp_session);
            $command->setContainer(isys_application::instance()->container);
            $command->setAuth(isys_auth_system::instance());

            $application->add($command);

            $params = array_slice($argv, 1);

            $commandParams = [
                'command'    => $functionToCommand[$argv[0]]['command'],
                '--user'     => 'loginBefore',
                '--password' => 'loginBefore',
                '--tenantId' => 'loginBefore'
            ];

            if ($argv[0] == 'search') {
                $commandParams['--searchString'] = $params[0];
            }

            $application->run(new ArrayInput($commandParams), $output);
        }
    }

    /**
     * @return bool
     */
    public function needs_login()
    {
        return true;
    }
}
