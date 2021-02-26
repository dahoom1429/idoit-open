<?php

/**
 * @package    i-doit
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_template extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__TEMPLATE');
    }

    /**
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     *
     * @param   string  $p_str
     * @param   integer $p_fk_id
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function get_result($p_str = '', $p_fk_id, $p_cRecStatus = null)
    {
        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        $l_sql = "SELECT isys_catg_template_list__id, isys_catg_template_list__isys_catg_template__id, isys_catg_template_list__title
			FROM isys_catg_template_list
			WHERE isys_catg_template_list__isys_catg_template__id = " . $this->convert_sql_id($p_fk_id) . " ";

        if (!empty($l_cRecStatus)) {
            $l_sql .= " AND isys_catg_template_list__status = " . $this->convert_sql_int($l_cRecStatus);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_catg_template_list__title" => "LC__CMDB__CATG__TITLE"
        ];
    }
}