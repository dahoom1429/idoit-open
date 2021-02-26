<?php

namespace idoit\Component;

use dstuecken\Notify\Handler\AbstractHandler;
use dstuecken\Notify\NotificationCenter;
use idoit\Component\Settings\System;
use idoit\Component\Settings\Tenant;
use idoit\Component\Settings\User;
use isys_component_database as Database;
use isys_component_signalcollection as SignalCollection;
use isys_component_template as Template;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\EnvPlaceholderParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * i-doit Container Facade
 *
 * Gives access to some of the most used container services by identifying them as a @property.
 *
 * @package     idoit\Component
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * @property Logger                                    logger
 * @property Database                                  database_system
 * @property Database                                  database
 * @property AbstractHandler[]                         notifyHandler
 * @property NotificationCenter                        notify
 * @property SignalCollection                          signals
 * @property Template                                  template
 * @property \isys_component_session                   session
 * @property \isys_locale                              locales
 * @property \isys_application                         application
 * @property Request                                   request
 * @property System                                    settingsSystem
 * @property Tenant                                    settingsTenant
 * @property User                                      settingsUser
 * @property \isys_module_manager                      moduleManager
 * @property \isys_component_template_language_manager language
 */
class ContainerFacade extends ContainerBuilder implements \ArrayAccess
{
    /**
     * ContainerFacade constructor.
     *
     * This was made for hacking ContainerBuilder in Symfony 3.4
     * We use ContainerFacade for both compiled and not-compiled containers
     * but in ContainerBuilder v3.4 they add Definition for itself and it raises exception
     *
     * @param ParameterBagInterface|null $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag = null)
    {
        if (!$this->isCompiled()) {
            parent::__construct($parameterBag);
        } else {
            $this->parameterBag = $parameterBag ?: new EnvPlaceholderParameterBag();
        }
    }

    /**
     * @param $id
     * @param $value
     */
    public function __set($id, $value)
    {
        $this->set($id, $value);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function __get($id)
    {
        return $this->get($id);
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function __isset($id)
    {
        return $this->has($id);
    }

    /**
     * @param $id
     */
    public function __unset($id)
    {
        $this->set($id, null);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($id)
    {
        return $this->has($id);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($id)
    {
        return $this->get($id);
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($id, $value)
    {
        $this->set($id, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($id)
    {
        $this->set($id, null);
    }

    /**
     * @inheritDoc
     */
    public function get($id, $invalidBehavior = ContainerInterface::NULL_ON_INVALID_REFERENCE)
    {
        $value = parent::get($id, $invalidBehavior);

        if ($value instanceof \Closure) {
            $value = $value($this);
            $this->set($id, $value);
        }

        return $value;
    }
}
