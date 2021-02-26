<?php

/**
 * i-doit
 *
 * DAO: list for universal interfaces
 *
 * @package    i-doit
 * @subpackage CMDB_Category_lists
 * @version    Dennis Blümer <dbluemer@i-doit.org> - 2010-01-21
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_ui extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__UNIVERSAL_INTERFACE');
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
     *
     * @param   string  $p_str
     * @param   integer $p_object_id
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_str = null, $p_object_id, $p_cRecStatus = null)
    {
        try {
            $l_query = "SELECT isys_catg_ui_list__id, isys_catg_ui_list__title, isys_ui_con_type__title, isys_ui_plugtype__title, isys_cable_connection__id, isys_catg_connector_list__id, isys_catg_ui_list__isys_obj__id,isys_catg_ui_list__isys_catg_connector_list__id
				FROM isys_catg_ui_list
				LEFT JOIN isys_ui_con_type ON isys_ui_con_type__id = isys_catg_ui_list__isys_ui_con_type__id
				LEFT JOIN isys_ui_plugtype ON isys_ui_plugtype__id = isys_catg_ui_list__isys_ui_plugtype__id
				LEFT JOIN isys_catg_connector_list ON isys_catg_ui_list__isys_catg_connector_list__id = isys_catg_connector_list__id
				LEFT JOIN isys_cable_connection ON isys_cable_connection__id = isys_catg_connector_list__isys_cable_connection__id
				WHERE isys_catg_ui_list__isys_obj__id =  " . $this->convert_sql_id($p_object_id) . "
				AND isys_catg_ui_list__status = " . $this->convert_sql_id(empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus);

            return $this->retrieve($l_query);
        } catch (Exception $e) {
            isys_glob_display_error($e->getMessage());
        }
    }

    /**
     * Exchange column to create individual links in columns.
     *
     * @param   array &$p_arrRow
     *
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     */
    public function modify_row(&$p_arrRow)
    {
        if (!empty($p_arrRow["isys_cable_connection__id"])) {
            global $g_dirs;

            $l_strImage = "<img src=\"" . $g_dirs["images"] . "icons/silk/link.png\" class=\"vam\" />";

            $l_dao = new isys_cmdb_dao_cable_connection($this->m_db);

            $l_objID = $l_dao->get_assigned_object($p_arrRow["isys_cable_connection__id"], $p_arrRow["isys_catg_connector_list__id"]);
            $l_objInfo = $l_dao->get_type_by_object_id($l_objID)
                ->get_row();

            $l_link = isys_helper_link::create_url([
                C__CMDB__GET__OBJECT     => $l_objID,
                C__CMDB__GET__OBJECTTYPE => $l_objInfo["isys_obj_type__id"],
                C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__LIST_CATEGORY,
                C__CMDB__GET__CATG       => defined_or_default('C__CATG__UNIVERSAL_INTERFACE'),
                C__CMDB__GET__TREEMODE   => $_GET[C__CMDB__GET__TREEMODE]
            ]);

            $l_status = $this->get_ui_list_status($p_arrRow["isys_catg_connector_list__id"], $p_arrRow["isys_cable_connection__id"]);

            // exchange the specified column for status archived and deleted
            global $g_reference_colors;

            $p_arrRow["isys_obj__title"] = $l_strImage . ' ' . $l_objInfo["isys_obj__title"];
            if (isset($g_reference_colors[$l_status])) {
                $p_arrRow["isys_obj__title"] = '<div style="color:' . $g_reference_colors[$l_status] . ' ! important;">' . $p_arrRow["isys_obj__title"] . '</div>';
            }
            $quickinfo = new isys_ajax_handler_quick_info();
            $p_arrRow["isys_obj__title"] = $quickinfo->get_quick_info($l_objID, $p_arrRow["isys_obj__title"], C__LINK__OBJECT);

            $p_arrRow["connector_title"] = $l_dao->get_assigned_connector_name($p_arrRow["isys_catg_connector_list__id"], $p_arrRow["isys_cable_connection__id"]);
        }
    }

    /**
     * Method for retrieving the table fields.
     *
     * @return array
     * @author Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_fields()
    {
        return [
            "isys_catg_ui_list__title" => "LC__CMDB__CATG__UI_TITLE",
            "isys_ui_con_type__title"  => "LC__CMDB__CATG__UI_CONNECTION_TYPE",
            "isys_ui_plugtype__title"  => "LC__CMDB__CATG__UI_PLUG_TYPE",
            "isys_obj__title"          => "LC__CMDB__CATG__UI_ASSIGNED_UI",
            "connector_title"          => "LC__CATG__STORAGE_CONNECTION_TYPE"
        ];
    }

    /**
     * Get status for cable id.
     *
     * @param   integer $p_conID
     * @param   integer $p_cableConID
     *
     * @return  integer
     */
    public function get_ui_list_status($p_conID, $p_cableConID)
    {
        $l_query = "SELECT isys_obj__status
			FROM isys_catg_ui_list
			INNER JOIN isys_catg_connector_list ON isys_catg_ui_list__isys_catg_connector_list__id = isys_catg_connector_list__id
			LEFT JOIN isys_cable_connection ON isys_cable_connection__id = isys_catg_connector_list__isys_cable_connection__id
			INNER JOIN isys_obj ON isys_catg_connector_list__isys_obj__id = isys_obj__id
			WHERE isys_catg_connector_list__isys_cable_connection__id = " . $this->convert_sql_id($p_cableConID) . "
			AND isys_catg_connector_list__id != " . $this->convert_sql_id($p_conID);

        return $this->retrieve($l_query)
            ->get_row_value('isys_obj__status');
    }
}
