<?php

class isys_cmdb_dao_list_cats_emergency_plan_assigned_obj extends isys_component_dao_category_table_list
{
    /**
     * @return int
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__EMERGENCY_PLAN_LINKED_OBJECTS');
    }

    /**
     * @return int
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    public function get_fields()
    {
        return [
            //"isys_catg_emergency_plan_list__id" => "ID",
            "isys_obj__id"                      => "ID",
            "isys_obj_type__title"              => "LC__CMDB__OBJTYPE",
            "isys_obj__title"                   => "LC__CMDB__CATG__GLOBAL_TITLE"
        ];
    }

    public function get_result($p_strTableName = null, $p_object_id, $p_cRecStatus = null)
    {
        return isys_cmdb_dao_category_s_emergency_plan_assigned_obj::instance($this->m_db)->get_assigned_objects($p_object_id, $p_cRecStatus ?: $this->get_rec_status());
    }
}
