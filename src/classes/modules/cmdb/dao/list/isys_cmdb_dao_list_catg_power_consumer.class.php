<?php

/**
 * i-doit
 *
 * DAO: List DAO for power consumer
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_power_consumer extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__POWER_CONSUMER');
    }

    /**
     * Return constant of category type
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Modify elements in array for output.
     *
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        global $g_dirs;
        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');

        $p_arrRow['object_connection'] = $l_empty_value;
        $p_arrRow['connector_title'] = $l_empty_value;
        $p_arrRow['active'] = '<img src="' . $g_dirs["images"] . 'icons/silk/' . ($p_arrRow['isys_catg_pc_list__active'] == 1 ? 'bullet_green.png' : 'bullet_red.png') .
            '" />';

        if (!empty($p_arrRow["output_obj_id"])) {
            $l_strImage = '<img src="' . $g_dirs["images"] . 'icons/silk/link.png" class="vam" />';

            $l_dao = new isys_cmdb_dao_cable_connection($this->m_db);

            $l_objInfo = $l_dao->get_type_by_object_id($p_arrRow["output_obj_id"])
                ->get_row();

            // create link obj
            $l_link = isys_helper_link::create_url([
                C__CMDB__GET__OBJECT     => $p_arrRow["output_obj_id"],
                C__CMDB__GET__OBJECTTYPE => $l_objInfo["isys_obj_type__id"],
                C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__LIST_CATEGORY,
                C__CMDB__GET__CATG       => defined_or_default('C__CATG__CONNECTOR'),
                C__CMDB__GET__TREEMODE   => $_GET[C__CMDB__GET__TREEMODE]
            ]);

            $quickinfo = new isys_ajax_handler_quick_info();
            // exchange the specified column
            $p_arrRow["object_connection"] = $quickinfo->get_quick_info($p_arrRow["output_obj_id"], $l_strImage . ' ' . $l_objInfo['isys_obj__title'], C__LINK__OBJECT);
            $p_arrRow["connector_title"] = $l_dao->get_assigned_connector_name(
                $p_arrRow["isys_catg_pc_list__isys_catg_connector_list__id"],
                $p_arrRow["isys_catg_connector_list__isys_cable_connection__id"]
            );
        }
    }

    /**
     * Method for retrieving the list-fields.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_fields()
    {
        return [
            "isys_catg_pc_list__title"    => "LC__UNIVERSAL__TITLE",
            "isys_pc_manufacturer__title" => "LC__CMDB__CATG__MANUFACTURE",
            "isys_catg_pc_list__watt"     => "LC__CMDB__CATS__POBJ_WATT",
            "object_connection"           => "LC__CMDB__CATG__NETWORK__TARGET_OBJECT",
            "connector_title"             => "LC__CATG__STORAGE_CONNECTION_TYPE",
            "active"                      => "LC__UNIVERSAL__ACTIVE"
        ];
    }
}
