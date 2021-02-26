<?php

/**
 * i-doit
 *
 * DAO: List dao for jdisc custom attributes
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_jdisc_custom_attributes extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__JDISC_CUSTOM_ATTRIBUTES');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Method which helps modifying each row.
     *
     * @param  array $p_row
     */
    public function modify_row(&$p_row)
    {

    }

    /**
     * Method for retrieving the displayable fields.
     *
     * @return   array
     * @version  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_fields()
    {
        return [
            "isys_catg_jdisc_custom_attributes_list__title"   => "C__CATG__JDISC__CUSTOM_ATTRIBUTES__TITLE",
            "isys_catg_jdisc_custom_attributes_list__content" => "C__CATG__JDISC__CUSTOM_ATTRIBUTES__CONTENT",
        ];
    }
}