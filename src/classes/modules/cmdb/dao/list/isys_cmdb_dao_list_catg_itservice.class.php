<?php

/**
 * i-doit
 * DAO: list DAO for it-services.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_itservice extends isys_component_dao_category_table_list
{
    /**
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__IT_SERVICE');
    }

    /**
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * @param   string  $p_table
     * @param   integer $p_object_id
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_table = null, $p_object_id, $p_cRecStatus = null)
    {
        $l_dao = new isys_cmdb_dao_category_g_itservice($this->get_database_component());
        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        if (empty($l_cRecStatus)) {
            $l_cRecStatus = C__RECORD_STATUS__NORMAL;
        }

        return $l_dao->get_data(null, $p_object_id, "", null, $l_cRecStatus);
    }

    /**
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        $l_dao = isys_factory::get_instance('isys_cmdb_dao', $this->get_database_component());

        $p_arrRow["obj_title"] = isys_factory::get_instance('isys_ajax_handler_quick_info')
            ->get_quick_info($p_arrRow["isys_catg_its_components_list__isys_obj__id"],
                $l_dao->get_obj_name_by_id_as_string($p_arrRow["isys_catg_its_components_list__isys_obj__id"]), C__LINK__OBJECT);

        $p_arrRow["obj_type"] = $l_dao->get_objtype_name_by_id_as_string($l_dao->get_objTypeID($p_arrRow["isys_catg_its_components_list__isys_obj__id"]));
    }

    /**
     * @return  array
     */
    public function get_fields()
    {
        return [
            'obj_title' => 'LC_UNIVERSAL__OBJECT',
            'obj_type'  => 'LC__CMDB__CATG__TYPE',
        ];
    }

    /**
     * @return  string
     */
    public function make_row_link()
    {
        return "#";
    }
}