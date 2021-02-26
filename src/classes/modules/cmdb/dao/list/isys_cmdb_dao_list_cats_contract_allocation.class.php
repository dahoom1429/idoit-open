<?php

/**
 * i-doit
 *
 * DAO: list for contract allocation
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Van Quyen Hoang <qhoang@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_contract_allocation extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__CONTRACT_ALLOCATION');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     * Retrieve data for catg maintenance list view.
     *
     * @param   string  $p_str
     * @param   integer $p_objID
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_general
     */
    public function get_result($p_str = null, $p_objID, $p_cRecStatus = null)
    {
        return isys_cmdb_dao_category_s_contract_allocation::instance($this->m_db)
            ->get_data(null, $p_objID, '', null, ($p_cRecStatus ?: $this->get_rec_status()));
    }

    public function modify_row(&$p_row)
    {
        $p_row["isys_obj__title"] = isys_factory::get_instance('isys_ajax_handler_quick_info')
            ->get_quick_info($p_row["isys_obj__id"], $p_row["isys_obj__title"], C__LINK__OBJECT, 80);
    }

    /**
     * Returns array with table headers
     *
     * @return array
     */
    public function get_fields()
    {
        return [
            "isys_obj__id"         => "ID",
            "isys_obj_type__title" => 'LC_UNIVERSAL__OBJECT_TYPE',
            "isys_obj__title"      => 'LC__CMDB__CATG__TITLE'
        ];
    }

    public function make_row_link(&$p_row)
    {
        return "#";
    }
}
