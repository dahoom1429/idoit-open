<?php

/**
 * i-doit
 *
 * DAO: Class for overview category
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_person_group_contact_assign extends isys_component_dao_category_table_list
{
    /**
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__PERSON_GROUP_CONTACT_ASSIGNMENT');
    }

    /**
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     *
     * @param   string  $p_str
     * @param   integer $p_objID
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_result($p_str = null, $p_objID, $p_cRecStatus = null)
    {
        $l_sql = "SELECT isys_catg_contact_list.*, o1.*, ot.*, isys_contact_tag.* FROM isys_catg_contact_list
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list.isys_catg_contact_list__isys_connection__id
			INNER JOIN isys_obj AS o1 ON isys_catg_contact_list.isys_catg_contact_list__isys_obj__id = o1.isys_obj__id
			INNER JOIN isys_obj_type AS ot ON o1.isys_obj__isys_obj_type__id = ot.isys_obj_type__id
			INNER JOIN isys_obj AS o2 ON o2.isys_obj__id = isys_connection__isys_obj__id
			LEFT JOIN isys_contact_tag ON isys_contact_tag__id = isys_catg_contact_list.isys_catg_contact_list__isys_contact_tag__id
			WHERE isys_connection__isys_obj__id = " . $this->convert_sql_id($p_objID) . " ";

        $l_cRecStatus = (int)(($p_cRecStatus === null) ? $this->get_rec_status() : $p_cRecStatus);

        if ($l_cRecStatus > 0) {
            $l_sql .= 'AND o1.isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
				AND isys_catg_contact_list.isys_catg_contact_list__status = ' . $this->convert_sql_id($l_cRecStatus);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Modify row method.
     *
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        if ($p_arrRow["isys_obj__id"] != null) {
            $quickinfo = new isys_ajax_handler_quick_info();
            $p_arrRow['isys_obj__title'] = $quickinfo->get_quick_info($p_arrRow["isys_obj__id"], $p_arrRow["isys_obj__title"], C__LINK__OBJECT);
        }
    }

    /**
     * Returns array with table headers.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_obj__title"         => "LC__CMDB__LOGBOOK__TITLE",
            "isys_obj_type__title"    => "LC__CMDB__OBJTYPE",
            "isys_contact_tag__title" => "LC__CMDB__CONTACT_ROLE"
        ];
    }

    /**
     * Make row link method.
     *
     * @param   array $urlOverwrite
     *
     * @return  string
     */
    public function make_row_link($urlOverwrite = null)
    {
        $l_sql = 'SELECT isys_obj__id, isys_obj_type__isys_obj_type_group__id, isys_obj__isys_obj_type__id
			FROM isys_obj
			INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_obj__id = ' . $this->convert_sql_id($_GET[C__CMDB__GET__OBJECT]) . ';';

        $l_catdata = $this->retrieve($l_sql)->get_row();
        $l_gets = isys_module_request::get_instance()->get_gets();

        $urlParameters = [
            C__CMDB__GET__VIEWMODE           => C__CMDB__VIEW__CATEGORY_GLOBAL,
            C__CMDB__GET__TREEMODE           => $l_gets[C__CMDB__GET__TREEMODE],
            C__CMDB__GET__OBJECT             => $l_catdata["isys_obj__id"],
            C__CMDB__GET__CATS               => defined_or_default('C__CATS__PERSON_GROUP_CONTACT_ASSIGNMENT'),
            C__CMDB__GET__OBJECTGROUP        => $l_catdata["isys_obj_type__isys_obj_type_group__id"],
            C__GET__MAIN_MENU__NAVIGATION_ID => $l_gets["mNavID"],
            C__CMDB__GET__OBJECTTYPE         => $l_catdata["isys_obj__isys_obj_type__id"],
            C__CMDB__GET__CATLEVEL           => "[{isys_catg_contact_list__id}]"
        ];

        if (is_array($urlOverwrite) && count($urlOverwrite)) {
            // @todo  Can this be done via array_merge?
            foreach ($urlOverwrite as $key => $value) {
                $urlParameters[$key] = $urlOverwrite[$key];
            }
        }

        return isys_helper_link::create_url($urlParameters);
    }
}
