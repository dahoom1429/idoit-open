<?php

/**
 * i-doit
 *
 * Class factory
 *
 * @deprecated  Please use something else!
 * @package     i-doit
 * @subpackage  Factory
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_factory
{
    /**
     * @var array
     */
    protected static $m_instances = [];

    /**
     * @param  string $className
     * @param  mixed  $parameter
     *
     * @deprecated
     * @return mixed
     */
    public static function get_instance($className, $parameter = null)
    {
        if (isset(self::$m_instances[$className])) {
            return self::$m_instances[$className];
        }

        if (method_exists($className, 'get_instance')) {
            return (self::$m_instances[$className] = call_user_func_array([$className, 'get_instance'], $parameter));
        }

        return (self::$m_instances[$className] = new $className($parameter));
    }
}
