<?php

/**
 * @package     i-doit
 * @subpackage  CMDB
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_guest_systems extends isys_component_dao_category_table_list
{
    /**
     * @return integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__GUEST_SYSTEMS');
    }

    /**
     * @return integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     *
     * @param   string  $p_table
     * @param   integer $p_object_id
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     * @author  Niclas Potthast <npotthast@i-doit.de>
     * @author  Dennis Stuecken <dstuecken@i-doit.de>
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function get_result($p_table = null, $p_object_id, $p_cRecStatus = null)
    {
        $l_dao = new isys_cmdb_dao_category_g_guest_systems($this->m_db);

        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        if (empty($l_cRecStatus)) {
            $l_cRecStatus = C__RECORD_STATUS__NORMAL;
        }

        return $l_dao->get_data(null, $p_object_id,
            " AND guest.isys_obj__status = " . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . " AND isys_catg_virtual_machine_list__status = " .
            $l_dao->convert_sql_int($l_cRecStatus), null, $l_cRecStatus);
    }

    /**
     *
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');
        $l_quickinfo = new isys_ajax_handler_quick_info($_GET, $_POST);
        $l_dao = new isys_cmdb_dao($this->m_db);
        $l_dao_ip = new isys_cmdb_dao_category_g_ip($this->m_db);
        $l_dao_cluster_members = new isys_cmdb_dao_category_g_cluster_members($this->m_db);
        $l_dialog = new isys_smarty_plugin_f_dialog();
        $l_data = [];

        $l_ip_row = $l_dao_ip->get_data(null, $p_arrRow["isys_catg_virtual_machine_list__isys_obj__id"], " AND isys_catg_ip_list__primary = 1", null, C__RECORD_STATUS__NORMAL)
            ->get_row();

        if (defined('C__OBJTYPE__CLUSTER') && $l_dao->get_objTypeID($_GET[C__CMDB__GET__OBJECT]) == C__OBJTYPE__CLUSTER) {
            $l_res_members = $l_dao_cluster_members->get_data(null, $_GET[C__CMDB__GET__OBJECT], "", null, C__RECORD_STATUS__NORMAL);

            while ($l_row_members = $l_res_members->get_row()) {
                $l_objtype_name = isys_application::instance()->container->get('language')
                    ->get($l_dao->get_objtype_name_by_id_as_string($l_dao->get_objTypeID($l_row_members["isys_connection__isys_obj__id"])));
                $l_data[$l_objtype_name][$l_row_members["isys_connection__isys_obj__id"]] = $l_dao->get_obj_name_by_id_as_string($l_row_members["isys_connection__isys_obj__id"]);
            }
        } else {
            $l_data[$_GET[C__CMDB__GET__OBJECT]] = $l_dao->get_obj_name_by_id_as_string($_GET[C__CMDB__GET__OBJECT]);
        }

        $l_params = [
            "p_strSelectedID"   => $p_arrRow["isys_catg_virtual_machine_list__primary"],
            "status"            => C__RECORD_STATUS__NORMAL,
            "order"             => "isys_catg_virtual_machine_list__id",
            "p_arData"          => $l_data,
            "p_bInfoIconSpacer" => false,
            "name"              => "C__CATG__VIRTUAL_MACHINE_TAG_" . $this->m_i++,
            "p_onChange"        => "new Ajax.Updater('infoBox', '?ajax=1&call=update_guest_system_primary', {parameters: {objId: '" . $_GET[C__CMDB__GET__OBJECT] .
                "',conId: '" . $p_arrRow["isys_catg_virtual_machine_list__id"] .
                "', valId: this.value}, method: 'post', onComplete: function(){ $('infoBox').highlight(); }});"
        ];

        $l_edit_tmp = $_GET["editMode"];
        $_GET["editMode"] = C__EDITMODE__ON;

        if (defined('C__OBJTYPE__CLUSTER') && $_GET[C__CMDB__GET__OBJECTTYPE] == C__OBJTYPE__CLUSTER) {
            $p_arrRow["primary"] = $l_dialog->navigation_edit(isys_application::instance()->template, $l_params);
        } else {
            $p_arrRow["primary"] = $l_dialog->navigation_view(isys_application::instance()->template, $l_params);
        }

        $_GET["editMode"] = $l_edit_tmp;

        $l_obj_arr = $l_dao->get_object_by_id($p_arrRow["isys_catg_virtual_machine_list__isys_obj__id"]);
        $l_row = $l_obj_arr->get_row();
        $p_arrRow["connected_title"] = $l_quickinfo->get_quick_info($l_row["isys_obj__id"], $l_row["isys_obj__title"], C__LINK__OBJECT);
        $p_arrRow["object_type"] = isys_application::instance()->container->get('language')
            ->get($l_dao->get_objtype_name_by_id_as_string($l_row["isys_obj__isys_obj_type__id"]));

        $p_arrRow["hostname"] = $l_ip_row["isys_catg_ip_list__hostname"] ?: $l_empty_value;
        $p_arrRow["ip_address"] = $l_ip_row["isys_cats_net_ip_addresses_list__title"] ?: $l_empty_value;
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
     * @return  array
     */
    public function get_fields()
    {
        return [
            "connected_title" => "LC__CMDB__CATG__GUEST_SYSTEM",
            "object_type"     => "LC__CMDB__OBJTYPE",
            "hostname"        => "LC__CATP__IP__HOSTNAME",
            "ip_address"      => "LC__CMDB__CATG__NETWORK__PRIM_IP",
            "primary"         => "LC__CMDB__CATG__GUEST_SYSTEM_RUNS_ON"
        ];
    }

    /**
     * @return  string
     */
    public function make_row_link()
    {
        return "#";
    }
}
