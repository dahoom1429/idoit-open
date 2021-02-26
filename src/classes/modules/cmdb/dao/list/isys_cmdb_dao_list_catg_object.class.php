<?php

/**
 * i-doit.
 *
 * DAO: Category list for contacts.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stuecken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_object extends isys_component_dao_category_table_list
{
    /**
     * Method for returning the category ID.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__OBJECT');
    }

    /**
     * Method for returning the category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Modify row method.
     *
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        $l_quick_info = new isys_ajax_handler_quick_info();

        $p_arrRow["isys_obj__title"] = $l_quick_info->get_quick_info($p_arrRow["isys_obj__id"], $p_arrRow["isys_obj__title"], C__LINK__OBJECT, 80);
    }

    /**
     * Method for returning the fields.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_obj__id"         => "LC__UNIVERSAL__ID",
            "isys_obj__title"      => "LC_UNIVERSAL__OBJECT",
            "isys_obj_type__title" => "LC__CMDB__OBJTYPE"
        ];
    }

    /**
     * Method for retrieving the row-link.
     *
     * @return  string
     */
    public function make_row_link()
    {
        return "#";
    }
}
