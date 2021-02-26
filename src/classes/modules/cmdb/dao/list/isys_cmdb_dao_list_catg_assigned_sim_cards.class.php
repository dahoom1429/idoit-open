<?php

/**
 * i-doit
 *
 * DAO: Specific Layer2 assigned ports list.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Kevin Mauel <kmauel@i-doit.com
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_assigned_sim_cards extends isys_component_dao_category_table_list
{
    /**
     * Method for retrieving the category ID.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__ASSIGNED_SIM_CARDS');
    }

    /**
     * Method for retrieving the category-type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Get result method for retrieving data to display in the table.
     *
     * @param   string  $p_str
     * @param   integer $p_objID
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_str = null, $p_objID, $p_cRecStatus = null)
    {
        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;
        $query = 'SELECT * FROM isys_catg_cards_list 
            LEFT JOIN isys_obj ON isys_obj__id = isys_catg_cards_list__isys_obj__id 
            INNER JOIN isys_connection ON isys_connection__id = isys_catg_cards_list__isys_connection__id
            WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_objID) . ' AND isys_catg_cards_list__status = ' . $l_cRecStatus;

        return $this->retrieve($query);
    }

    /**
     * Method for modifying the single rows for displaying links or getting translations.
     *
     *
     * @param   array & $p_row
     */
    public function modify_row(&$p_row)
    {
        global $g_comp_template_language_manager;

        $l_link = isys_helper_link::create_url([
            C__CMDB__GET__OBJECT     => $p_row['obj_id'],
            C__CMDB__GET__CATG       => defined_or_default('C__CATG__CARDS'),
        ]);

        $quickinfo = new isys_ajax_handler_quick_info();
        $p_row['obj_title'] = $quickinfo->get_quick_info(
            $p_row['obj_id'],
            $p_row['obj_title'] . ' (' . $g_comp_template_language_manager->get($p_row['isys_obj_type__title']) . ')',
            $l_link
        );
    }

    /**
     * Flag for the rec status dialog
     *
     * @return bool
     */
    public function rec_status_list_active()
    {
        return false;
    }

    /**
     * Method for retrieving the fields to display in the list-view.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_obj__title'  => 'LC_UNIVERSAL__OBJECT',
            'isys_catg_cards_list__title' => 'LC__CMDB__CATG__CARDS__TITLE'
        ];
    }

    /**
     * Returns the link the browser shall follow if clicked on a row.
     *
     * @return  string
     */
    public function make_row_link()
    {
        return '#';
    }
}
