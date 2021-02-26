<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stuecken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_database_gateway extends isys_component_dao_category_table_list
{
    /**
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__DATABASE_GATEWAY');
    }

    /**
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     *
     * @param  array &$p_row
     */
    public function modify_row(&$p_row)
    {
        $p_row["target"] = isys_factory::get_instance('isys_ajax_handler_quick_info')
            ->get_quick_info($p_row["isys_connection__isys_obj__id"], isys_cmdb_dao::instance($this->m_db)
                ->get_obj_name_by_id_as_string($p_row["isys_connection__isys_obj__id"]), C__LINK__OBJECT);
    }

    /**
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_cats_database_gateway_list__type" => "LC__CMDB__CATS__DATABASE_GATEWAY__GATEWAY_TYPE",
            "isys_cats_database_gateway_list__host" => "LC__CMDB__CATS__DATABASE_GATEWAY__HOST",
            "isys_cats_database_gateway_list__port" => "LC__CATD__PORT",
            "isys_cats_database_gateway_list__user" => "LC__CMDB__CATS__DATABASE_GATEWAY__USER",
            "target"                                => "LC__CMDB__CATS__DATABASE_GATEWAY__TARGET_SCHEMA"
        ];
    }
}