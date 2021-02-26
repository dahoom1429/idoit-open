<?php

namespace idoit\Component\Provider;

/**
 * i-doit Factory Trait
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
trait Factory
{
    /**
     * Return instance of current class
     *
     * @return static
     */
    final public static function factory()
    {
        $args = func_get_args();

        if (count($args) > 0) {
            $instance = new \ReflectionClass(static::class);

            return $instance->newInstanceArgs($args);
        }

        return new static;
    }
}
