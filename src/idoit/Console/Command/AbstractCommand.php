<?php

namespace idoit\Console\Command;

use Exception;
use idoit\Component\ContainerFacade;
use idoit\Console\Exception\InvalidCredentials;
use isys_auth;
use isys_auth_system;
use isys_component_session;
use isys_exception_auth;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractCommand extends Command implements LoginAwareInterface
{
    /**
     * Require login by default, may be overwritten by subclasses
     */
    const REQUIRES_LOGIN = true;

    const HIDE_COMMAND = false;

    /**
     * @var isys_auth_system
     */
    protected $auth;

    /**
     * @var isys_component_session
     */
    protected $session;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var ContainerFacade
     */
    protected $container;

    /**
     * @param ContainerFacade $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function setAuth(isys_auth_system $auth)
    {
        $this->auth = $auth;
    }

    public function setSession(isys_component_session $session)
    {
        $this->session = $session;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Get name for command
     *
     * @return string
     */
    abstract public function getCommandName();

    /**
     * Get description for command
     *
     * @return string
     */
    abstract public function getCommandDescription();

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    abstract public function getCommandDefinition();

    /**
     * Checks if a command can have a config file via --config
     *
     * @return bool
     */
    abstract public function isConfigurable();

    /**
     * Returns an array of command usages
     *
     * @return string[]
     */
    abstract public function getCommandUsages();

    /**
     * Pre configure child commands
     */
    protected function configure()
    {
        $this->setName($this->getCommandName());
        $this->setDescription($this->getCommandDescription());
        $this->setHidden($this::HIDE_COMMAND);

        $commandDefinition = $this->getCommandDefinition();

        if ($this->requiresLogin()) {
            // ensure login arguments
            $commandDefinition->addOption(new InputOption('user', 'u', InputOption::VALUE_REQUIRED, 'User'));
            $commandDefinition->addOption(new InputOption('password', 'p', InputOption::VALUE_REQUIRED, 'Password'));
            $commandDefinition->addOption(new InputOption('tenantId', 'i', InputOption::VALUE_REQUIRED, 'Tenant ID', 1));
        }

        if ($this->isConfigurable()) {
            $commandDefinition->addOption(new InputOption('config', 'c', InputOption::VALUE_REQUIRED, 'Config File'));
        }

        $this->setDefinition($commandDefinition);

        foreach ($this->getCommandUsages() as $usage) {
            $this->addUsage((string)$usage);
        }
    }

    /**
     * Retrieves a config file
     *
     * @param InputInterface $input
     *
     * @return string|null
     */
    public function getConfigFile(InputInterface $input)
    {
        return $input->getOption('config');
    }

    /**
     * Login an user with User, Password and tenantId as requirements
     *
     * @param InputInterface $input
     *
     * @return bool|void
     * @throws isys_exception_auth
     */
    public function login(InputInterface $input)
    {
        if (!($input->getOption('user') && $input->getOption('password') && $input->getOption('tenantId'))) {
            throw new Exception('Missing credentials for login, please provide: ' . ($input->getOption('user') ? '' : '--user ') .
                ($input->getOption('password') ? '' : '--password ') . ($input->getOption('tenantId') ? '' : '--tenantId '));
        }

        if (!$this->session->weblogin($input->getOption('user'), $input->getOption('password'), $input->getOption('tenantId'))) {
            throw new InvalidCredentials(
                'Unable to login with given user credentials. Please check --user and --password for validity and try again.'
            );
        }

        $this->session->include_mandator_cache();

        if (!$this->auth) {
            $this->auth = isys_auth_system::instance();
        }

        $authRight = 'COMMAND/' . strtolower(substr(static::class, strrpos(static::class, '\\') + 1));

        if (!$this->auth->is_allowed_to(isys_auth::EXECUTE, $authRight)) {
            throw new isys_exception_auth('Not allowed to execute: ' . static::class . "\n\r" . "You'll need the right " . $authRight .
                ' in order to execute the command.');
        }
    }

    /**
     * Logout an user
     *
     * @return boolean
     */
    public function logout()
    {
        $this->session->logout();
    }

    /**
     * Requires command login via session ?
     *
     * @return boolean
     */
    public function requiresLogin()
    {
        return $this::REQUIRES_LOGIN;
    }
}
