<?php

namespace idoit\Component\Provider;

use idoit\Component\ContainerFacade;

/**
 * i-doit Container Aware Factory Trait
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
trait DiFactory
{
    use DiInjectable;

    /**
     * Factory with $container parameter
     *
     * @param ContainerFacade $container
     *
     * @return static
     */
    public static function factory(ContainerFacade $container)
    {
        $instance = new self();

        if (method_exists($instance, 'setDi')) {
            $instance->setDi($container);
        }

        return $instance;
    }

}