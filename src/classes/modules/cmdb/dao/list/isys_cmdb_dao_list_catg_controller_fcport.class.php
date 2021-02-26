<?php

/**
 * i-doit
 *
 * DAO: ObjectType list for FC ports (in storage).
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_controller_fcport extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__CONTROLLER_FC_PORT');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     *
     * @param   string $p_column
     * @param   string $p_direction
     *
     * @return  string
     */
    public function get_order_condition($p_column, $p_direction)
    {
        if ($p_column == 'isys_catg_fc_port_list__title') {
            return "LENGTH(" . $p_column . ") " . $p_direction . ", " . $p_column . " " . $p_direction;
        }

        return parent::get_order_condition($p_column, $p_direction);
    }

    /**
     * Get result set for all ports for the current object.
     *
     * @param   string  $p_str
     * @param   integer $p_nID
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @todo    only ONE dataset from ip_list should be shown!
     */
    public function get_result($p_str = null, $p_nID = null, $p_cRecStatus = null)
    {
        $l_strSQL = "SELECT * FROM isys_catg_fc_port_list
			LEFT JOIN isys_fc_port_type ON isys_catg_fc_port_list__isys_fc_port_type__id = isys_fc_port_type__id
			LEFT JOIN isys_fc_port_medium ON isys_catg_fc_port_list__isys_fc_port_medium__id = isys_fc_port_medium__id
			LEFT JOIN isys_catg_hba_list ON isys_catg_fc_port_list__isys_catg_hba_list__id = isys_catg_hba_list__id
			LEFT JOIN isys_catg_connector_list ON isys_catg_connector_list__id = isys_catg_fc_port_list__isys_catg_connector_list__id
			LEFT JOIN isys_cable_connection ON isys_cable_connection__id = isys_catg_connector_list__isys_cable_connection__id
			WHERE TRUE";

        if ($p_nID !== null) {
            $l_strSQL .= ' AND isys_catg_fc_port_list__isys_obj__id = ' . $this->convert_sql_id($p_nID);
        }

        $l_cRecStatus = $p_cRecStatus ?: $this->get_rec_status();

        if ($l_cRecStatus !== null && $l_cRecStatus > 0) {
            $l_strSQL .= ' AND isys_catg_fc_port_list__status = ' . $this->convert_sql_int($l_cRecStatus);
        }

        return $this->retrieve($l_strSQL . ';');
    }

    /**
     * Exchange column to create individual links in columns.
     *
     * @param   array &$p_arrRow
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function modify_row(&$p_arrRow)
    {
        global $g_dirs;

        $p_arrRow['object_connection'] = $p_arrRow['connector_title'] = isys_tenantsettings::get('gui.empty_value', '-');

        if (isset($p_arrRow["isys_cable_connection__id"]) && $p_arrRow["isys_cable_connection__id"] > 0) {
            $l_dao = new isys_cmdb_dao_cable_connection($this->m_db);

            $l_objID = $l_dao->get_assigned_object($p_arrRow["isys_cable_connection__id"], $p_arrRow["isys_catg_connector_list__id"]);

            $l_objInfo = $l_dao->get_type_by_object_id($l_objID)
                ->get_row();

            $l_link = isys_helper_link::create_url([
                C__CMDB__GET__OBJECT     => $l_objID,
                C__CMDB__GET__OBJECTTYPE => $l_objInfo["isys_obj_type__id"],
                C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__LIST_CATEGORY,
                C__CMDB__GET__CATG       => defined_or_default('C__CATG__CONTROLLER_FC_PORT'),
                C__CMDB__GET__TREEMODE   => $_GET[C__CMDB__GET__TREEMODE]
            ]);

            // Exchange the specified column.
            $quickinfo = new isys_ajax_handler_quick_info();
            $l_strImage = '<img src="' . $g_dirs["images"] . 'icons/silk/link.png" class="vam mr5" />';
            $p_arrRow["object_connection"] = $quickinfo->get_quick_info($l_objID, $l_strImage . ' ' . $l_objInfo["isys_obj__title"], $l_link);
            $p_arrRow["connector_title"] = $l_dao->get_assigned_connector_name(
                $p_arrRow["isys_catg_fc_port_list__isys_catg_connector_list__id"],
                $p_arrRow["isys_cable_connection__id"]
            );
        }
    }

    /**
     * Returns array with table headers.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_fields()
    {
        return [
            'isys_catg_fc_port_list__title' => 'Port ' . isys_application::instance()->container->get('language')
                    ->get('LC__CATG__STORAGE_FCPORT__TITLE'),
            'isys_catg_hba_list__title'     => 'LC__CMDB__CATG__HBA',
            'isys_catg_fc_port_list__wwn'   => 'LC_FC_PORT_POPUP__CHOSEN_WWNS',
            'isys_fc_port_type__title'      => 'LC__CATG__STORAGE_FCPORT__TYPE',
            'isys_fc_port_medium__title'    => 'LC__CATG__STORAGE_FCPORT__MEDIUM',
            'object_connection'             => 'LC__CMDB__CATG__NETWORK__TARGET_OBJECT',
            'connector_title'               => 'LC__CATG__STORAGE_CONNECTION_TYPE'
        ];
    }
}
