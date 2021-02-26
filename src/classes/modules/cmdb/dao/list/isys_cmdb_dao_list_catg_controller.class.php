<?php

/**
 * i-doit
 *
 * DAO: ObjectType list for storage controllers.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_controller extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__CONTROLLER');
    }

    /**
     * Return constant of category type
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Returns array with table headers.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_catg_controller_list__title"    => "LC__CATG__STORAGE_CONTROLLER_TITLE",
            "isys_controller_type__title"         => "LC__CATG__STORAGE_CONTROLLER_TYPE",
            "isys_controller_manufacturer__title" => "LC__CATG__STORAGE_CONTROLLER_MANUFACTURER",
            "isys_controller_model__title"        => "LC__CATG__STORAGE_CONTROLLER_MODEL",
            "isys_catg_controller_list__firmware" => "LC__CATG__STORAGE_CONTROLLER_FIRMWARE"
        ];
    }
}
