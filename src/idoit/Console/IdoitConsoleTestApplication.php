<?php

namespace idoit\Console;

use idoit\Console\Subscriber\CommandEvents;
use isys_application;
use Symfony\Component\Console\Application;
use Symfony\Component\EventDispatcher\EventDispatcher;

class IdoitConsoleTestApplication extends IdoitConsoleApplication
{
    /**
     * IdoitConsoleApplication constructor.
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct('i-doit console utility', isys_application::instance()->info['version']);

        $this->setCatchExceptions(false);
        $this->setAutoExit(false);
    }
}
