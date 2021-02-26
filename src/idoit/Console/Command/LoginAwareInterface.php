<?php

namespace idoit\Console\Command;

use idoit\Console\Exception\InvalidCredentials;
use Symfony\Component\Console\Input\InputInterface;

interface LoginAwareInterface
{
    /**
     * Login an user with User, Password and tenantId as requirements
     *
     * @param InputInterface $input
     *
     * @return bool
     *
     * @throws InvalidCredentials
     */
    public function login(InputInterface $input);

    /**
     * Logout an user
     *
     * @return boolean
     */
    public function logout();

    /**
     * Requires command login via session ?
     *
     * @return boolean
     */
    public function requiresLogin();
}
