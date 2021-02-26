<?php

namespace idoit\Component\Provider;

use idoit\Component\ContainerFacade;
use isys_application as Application;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * i-doit Container Aware Trait
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
trait DiInjectable
{
    use ContainerAwareTrait;

    /**
     * @param ContainerFacade $container
     *
     * @return mixed
     */
    public function setDi(ContainerFacade $container)
    {
        $this->setContainer($container);

        return $this;
    }

    /**
     * @return ContainerFacade
     */
    public function getDi()
    {
        if (!$this->container) {
            $this->setContainer(Application::instance()->container);
        }

        return $this->container;
    }
}
