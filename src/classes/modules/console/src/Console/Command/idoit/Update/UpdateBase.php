<?php

namespace idoit\Module\Console\Console\Command\idoit\Update;

use idoit\Module\Console\Console\Command\AbstractConfigurableCommand;
use idoit\Module\Console\Option\Option;
use idoit\Module\Console\Option\PasswordOption;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * The requirements for the command - they are common for all update subcommands
 */
abstract class UpdateBase extends AbstractConfigurableCommand
{
    /**
     *
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        global $g_absdir;

        $this->setName('update')
            ->setDescription('Update the i-doit application')
            ->setHelp(
                <<<TEXT
This command updates the i-doit application with given options
TEXT
            );
        //
        $this->addValue(new Option('system.user', 'i-doit Admin Username', 'admin', new InputOption('user', 'u', InputOption::VALUE_REQUIRED, 'i-doit Admin username')));
        $this->addValue(new PasswordOption(
            'system.password',
            'i-doit Admin Password',
            null,
            new InputOption('password', 'p', InputOption::VALUE_OPTIONAL, 'i-doit Admin password'),
            false
        ));
        $this->addValue(new Option('zip', 'Path to update Zip file', null, new InputOption('zip', 'z', InputOption::VALUE_OPTIONAL, 'Path to update Zip file')));
        $this->addValue(new Option('update.path', 'Path to updates directory', $g_absdir . '/updates/versions/'));
        $this->addValue(new Option('version', 'Version to update to', null, new InputOption('v', null, InputOption::VALUE_REQUIRED, 'Version to update to')));

        global $g_db_system;
        if (!is_array($g_db_system)) {
            die("config.inc.php is not loaded! Please, install the i-doit first!\n");
        }

        // Configuration options
        $this->addValue(new Option('db.host', 'Hostname for DB connection', $g_db_system['host']));
        $this->addValue(new Option('db.port', 'Port for DB connection', $g_db_system['port']));
        $this->addValue(new Option('db.root.user', 'Username of priviliged DB User', $g_db_system['user']));
        $this->addValue(new Option('db.root.password', 'Password of priviliged DB User', $g_db_system['pass']));
        $this->addValue(new Option('db.system.database', 'i-doit System Database name', $g_db_system['name']));
        $this->addValue(new Option('directory.config', 'Path to the config.inc.php', $g_absdir . '/src/config.inc.php'));

        parent::configure();
    }
}
