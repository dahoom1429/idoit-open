<?php

/**
 * i-doit
 *
 * Class DAO factory
 *
 * @deprecated  Please use the DAO instance method!
 * @package     i-doit
 * @subpackage  Factory
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_factory_dao
{
    /**
     * @var array
     */
    protected static $m_instances = [];

    /**
     * @param  string                  $className
     * @param  isys_component_database $database
     *
     * @deprecated
     * @return mixed
     * @throws isys_exception_general
     */
    public static function get_instance($className, isys_component_database $database)
    {
        if (!$className) {
            throw new isys_exception_general('Instance class is not set in ' . __FILE__ . ':' . __LINE__);
        }

        if (!isset(self::$m_instances[$className])) {
            self::$m_instances[$className] = new $className($database);
        }

        return self::$m_instances[$className];
    }
}
