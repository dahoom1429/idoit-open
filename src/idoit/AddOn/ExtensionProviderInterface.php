<?php

namespace idoit\AddOn;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * i-doit AddOns Interface for injection services into application DI container
 *
 * @package     idoit\AddOn
 * @author      atsapko
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface ExtensionProviderInterface
{
    /**
     * Returns the module's container extension.
     *
     * @return ExtensionInterface
     */
    public function getContainerExtension();
}
