<?php

/**
 * i-doit
 *
 * Factory for CMDB category DAOs.
 *
 * @deprecated  Please use the DAO instance method!
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_factory_cmdb_category_dao extends isys_factory_dao
{
    /**
     * @var array
     */
    protected static $categories = [];

    /**
     * @param  integer                 $categoryType
     * @param  integer                 $categoryId
     * @param  isys_component_database $database
     *
     * @deprecated
     * @return mixed
     * @throws isys_exception_general
     */
    public static function get_instance_by_id($categoryType, $categoryId, isys_component_database $database)
    {
        if (is_countable(self::$categories) && count(self::$categories) === 0) {
            self::build_category_list($database);
        }

        return self::get_instance(self::$categories[$categoryType][$categoryId]['class_name'], $database);
    }

    /**
     * @param isys_component_database $database
     *
     * @deprecated
     */
    protected static function build_category_list(isys_component_database &$database)
    {
        self::$categories = isys_cmdb_dao::instance($database)->get_all_categories();
    }
}
