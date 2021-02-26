<?php

/**
 * i-doit
 *
 * DAO: specific category for operating systems
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_operating_system extends isys_cmdb_dao_category_s_application
{
    /**
     * Get category constant
     *
     * @return string
     */
    public function get_category_const()
    {
        /**
         * We overwrite this to force handling as independent category
         * which uses all procedures of application category
         */
        return 'C__CATS__OPERATING_SYSTEM';
    }

    /**
     * Get category id
     *
     * @return int
     */
    public function get_category_id()
    {
        if (defined('C__CATS__OPERATING_SYSTEM'))
            return constant('C__CATS__OPERATING_SYSTEM');
    }
}