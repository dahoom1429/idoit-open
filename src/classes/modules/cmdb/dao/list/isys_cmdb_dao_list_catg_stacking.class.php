<?php

/**
 * i-doit
 *
 * DAO: list for stacking.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 */
class isys_cmdb_dao_list_catg_stacking extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__STACKING');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Query method for this list.
     *
     * @param   string  $p_str
     * @param   integer $p_objID
     *
     * @return  isys_component_dao_result
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_result($p_str = null, $p_objID, $p_cRecStatus = null)
    {
        $l_sql = "SELECT *, isys_obj__title AS obj_title FROM isys_catg_stacking_list
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_stacking_list__isys_connection__id
			INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id
			WHERE isys_catg_stacking_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . "
			AND isys_catg_stacking_list__status = " . $this->convert_sql_int(empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus) . ";";

        return $this->retrieve($l_sql);
    }

    /**
     * Modifies row.
     *
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        $p_arrRow["obj_title"] = isys_factory::get_instance('isys_ajax_handler_quick_info')
            ->get_quick_info($p_arrRow["isys_obj__id"], $p_arrRow["isys_obj__title"], C__LINK__OBJECT);
    }

    /**
     * Flag for the rec status dialog.
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function rec_status_list_active()
    {
        return false;
    }

    /**
     * Method for returning the fields to display.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_fields()
    {
        return ["obj_title" => "LC__CMDB__CATS__CHASSIS"];
    }

    /**
     *
     * @return  string
     */
    public function make_row_link()
    {
        return "#";
    }
}