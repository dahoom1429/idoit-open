<?php

/**
 * i-doit
 *
 * DAO: list for voice over IP phone lines.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @since       1.0
 */
class isys_cmdb_dao_list_catg_voip_phone_line extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__VOIP_PHONE_LINE');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Method for receiving the category data.
     *
     * @param   string  $p_table
     * @param   integer $p_objID
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_table = null, $p_objID, $p_cRecStatus = null)
    {
        $l_sql = "SELECT *
			FROM isys_catg_voip_phone_line_list
			WHERE isys_catg_voip_phone_line_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . "
			AND isys_catg_voip_phone_line_list__status = " . $this->convert_sql_id(empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus) . ";";

        return $this->retrieve($l_sql);
    }

    /**
     * Exchange column to create individual links in columns.
     *
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        ;
    }

    /**
     * Method for returning the column-names.
     *
     * @param   string $p_str
     *
     * @return  array
     */
    public function get_fields($p_str = null)
    {
        return [
            "isys_catg_voip_phone_line_list__line_text_label"      => "LC__CMDB__CATG__VOIP_PHONE_LINE__LINE_TEXT_LABEL",
            "isys_catg_voip_phone_line_list__directory_number"     => "LC__CMDB__CATG__VOIP_PHONE_LINE__DIRECTORY_NUMBER",
            "isys_catg_voip_phone_line_list__calling_search_space" => "LC__CMDB__CATG__VOIP_PHONE_LINE__CALLING_SEARCH_SPACE"
        ];
    }
}