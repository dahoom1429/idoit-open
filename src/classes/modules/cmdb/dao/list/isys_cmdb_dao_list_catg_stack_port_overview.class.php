<?php

/**
 * i-doit
 *
 * DAO: Category list for stack port overview
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_stack_port_overview extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     * Every list class must have this method to return its category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__STACK_PORT_OVERVIEW');
    }

    /**
     * Gets category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * @param string|null  $p_str
     * @param integer|null $p_obj_id
     * @param integer|null $p_cRecStatus
     *
     * @return isys_component_dao_result
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_result($p_str = null, $p_obj_id = null, $p_cRecStatus = C__RECORD_STATUS__NORMAL)
    {
        $l_obj_id = $p_obj_id ?: $this->m_cat_dao->get_object_id();
        $l_query = 'SELECT 
              isys_obj__id, 
              isys_catg_port_list__id, 
              isys_obj__title, 
              isys_catg_port_list__title, 
              isys_obj_type__title 
          FROM isys_obj
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            INNER JOIN isys_catg_port_list ON isys_catg_port_list__isys_obj__id = isys_obj__id
          WHERE isys_catg_port_list__isys_obj__id IN (
              SELECT isys_catg_stack_member_list__stack_member FROM isys_catg_stack_member_list 
              WHERE isys_catg_stack_member_list__isys_obj__id = ' . $this->convert_sql_id($l_obj_id) . ') AND isys_catg_port_list__status = ' .
            $this->convert_sql_int($p_cRecStatus) . ';';

        return $this->retrieve($l_query);
    }

    /**
     * Modify row method will be called for each row to alter its content.
     *
     * @param  array &$p_row
     */
    public function modify_row(&$p_row)
    {
        $l_quickinfo = new isys_ajax_handler_quick_info();

        $p_row['isys_catg_port_list__title'] = $l_quickinfo->get_quick_info($p_row['isys_obj__id'], $p_row['isys_catg_port_list__title'], C__LINK__CATG, false,
            [C__CMDB__GET__CATLEVEL => $p_row['isys_catg_port_list__id'], C__CMDB__GET__CATG => defined_or_default('C__CATG__NETWORK_PORT')]);
        $p_row['isys_obj__title'] = $l_quickinfo->get_quick_info($p_row['isys_obj__id'], isys_application::instance()->container->get('language')
                ->get($p_row['isys_obj_type__title']) . ' >> ' . $p_row['isys_obj__title'], C__LINK__OBJECT);
    }

    /**
     * Method for getting the table column names.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_obj__title"            => "LC__UNIVERSAL__TITLE",
            "isys_catg_port_list__title" => "LC__CATD__PORT",
        ];
    }
}
