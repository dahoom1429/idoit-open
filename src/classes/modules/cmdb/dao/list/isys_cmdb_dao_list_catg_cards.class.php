<?php

/**
 * i-doit
 *
 * DAO: ObjectType list for access.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_cards extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__CARDS');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Method for receiving the field names.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_catg_cards_list__id' => 'LC__UNIVERSAL__ID',
            'isys_catg_cards_list__title' => 'LC__CMDB__CATG__CARDS__TITLE'
        ];
    }
}
