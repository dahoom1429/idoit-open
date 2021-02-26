<?php

namespace idoit\Module\System\Cleanup;

use isys_application;

/**
 * Class AbstractCleanup
 *
 * @package idoit\Module\System\Cleanup
 */
abstract class AbstractCleanup
{
    /**
     * @var \idoit\Component\ContainerFacade
     */
    protected $container;

    /**
     * AbstractCleanup constructor.
     */
    public function __construct()
    {
        $this->container = isys_application::instance()->container;
    }

    /**
     * Method for starting the cleanup process.
     */
    abstract public function process();
}
