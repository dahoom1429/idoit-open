<?php

/**
 * i-doit
 *
 * DAO: ObjectType list for assigned cards.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_assigned_cards extends isys_component_dao_category_table_list
{
    /**
     * This method returns the category ID.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__ASSIGNED_CARDS');
    }

    /**
     * This method returns the category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Method for retrieving the result.
     *
     * @param   string  $p_table
     * @param   integer $p_object_id
     * @param   integer $p_recStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_table = null, $p_object_id, $p_recStatus = null)
    {
        $l_cRecStatus = empty($p_recStatus) ? $this->get_rec_status() : $p_recStatus;

        if (empty($l_cRecStatus)) {
            $l_cRecStatus = C__RECORD_STATUS__NORMAL;
        }

        return isys_cmdb_dao_category_g_assigned_cards::instance($this->get_database_component())
            ->get_data(null, $p_object_id, "", null, $l_cRecStatus);
    }

    public function modify_row(&$p_arrRow)
    {
        $l_dao = isys_cmdb_dao::factory($this->get_database_component());
        $l_quickinfo = isys_factory::get_instance('isys_ajax_handler_quick_info');

        $l_title = $l_dao->get_obj_name_by_id_as_string($p_arrRow["isys_catg_assigned_cards_list__isys_obj__id__card"]);
        $p_arrRow["obj_title"] = $l_quickinfo->get_quick_info($p_arrRow["isys_catg_assigned_cards_list__isys_obj__id__card"], $l_title, C__LINK__OBJECT);
        $p_arrRow["obj_type"] = isys_application::instance()->container->get('language')
            ->get($l_dao->get_objtype_name_by_id_as_string($l_dao->get_objTypeID($p_arrRow["isys_catg_assigned_cards_list__isys_obj__id__card"])));
    }

    /**
     * This method returns the fields and translations.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "obj_title" => "LC__CMDB__CATG__TITLE",
            "obj_type"  => "LC__CMDB__CATG__TYPE"
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
