<?php

/**
 * i-doit
 *
 * DAO: list for chassis
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @since       1.0
 */
class isys_cmdb_dao_list_cats_chassis extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__CHASSIS_DEVICES');
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
     * Method for receiving the category data.
     *
     * @param   string  $p_str
     * @param   integer $p_objID
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_str = null, $p_objID, $p_cRecStatus = null)
    {
        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;
        $l_sql = 'SELECT isys_cats_chassis_list.*, rl.isys_chassis_role__title, ob.isys_obj__id, ob.isys_obj__title, netp.isys_catg_netp_list__id, netp.isys_catg_netp_list__title, pc.isys_catg_pc_list__id, pc.isys_catg_pc_list__title, hba.isys_catg_hba_list__id, hba.isys_catg_hba_list__title
			FROM isys_cats_chassis_list
			INNER JOIN isys_connection ON isys_connection__id = isys_cats_chassis_list__isys_connection__id
			LEFT JOIN isys_chassis_role AS rl ON rl.isys_chassis_role__id = isys_cats_chassis_list__isys_chassis_role__id
			LEFT JOIN isys_obj ob ON ob.isys_obj__id = isys_connection__isys_obj__id
			LEFT JOIN isys_catg_netp_list AS netp ON netp.isys_catg_netp_list__id = isys_cats_chassis_list__isys_catg_netp_list__id
			LEFT JOIN isys_catg_pc_list AS pc ON pc.isys_catg_pc_list__id = isys_cats_chassis_list__isys_catg_pc_list__id
			LEFT JOIN isys_catg_hba_list AS hba ON hba.isys_catg_hba_list__id = isys_cats_chassis_list__isys_catg_hba_list__id
			WHERE isys_cats_chassis_list__isys_obj__id = ' . $this->convert_sql_id($p_objID) . '
			AND isys_cats_chassis_list__status = ' . $this->convert_sql_int($l_cRecStatus) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Exchange column to create individual links in columns.
     *
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        // Preparing the assignment column.
        $p_arrRow['assignment'] = $this->m_cat_dao->get_assigned_device_title_by_cat_id($p_arrRow['isys_cats_chassis_list__id']);

        // Preparing the assigned slots.
        $l_assigned_slots = $this->m_cat_dao->get_assigned_slots_by_cat_id($p_arrRow['isys_cats_chassis_list__id']);

        if (is_array($l_assigned_slots) && count($l_assigned_slots) > 0) {
            $p_arrRow['assigned_slots'] = [];

            foreach ($l_assigned_slots as $l_slot) {
                $p_arrRow['assigned_slots'][] = $l_slot['isys_cats_chassis_slot_list__title'];
            }
        }
    }

    /**
     * Method for returning the column-names.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "assignment"               => "LC__UNIVERSAL__ASSIGNMENT",
            "isys_chassis_role__title" => "LC__CMDB__CATS__CHASSIS__ROLE",
            "assigned_slots"           => "LC__CMDB__CATS__CHASSIS__ASSIGNED_SLOTS",
        ];
    }
}
