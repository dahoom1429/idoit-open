<?php

/**
 * i-doit
 *
 * UI: global category for monitoring.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.3.0
 */
class isys_cmdb_ui_category_g_monitoring extends isys_cmdb_ui_category_global
{
    /**
     * Processes the UI for the monitoring category.
     *
     * @param isys_cmdb_dao_category $p_cat
     *
     * @return array|void
     * @throws isys_exception_cmdb
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];
        $l_obj_id = $_GET[C__CMDB__GET__OBJECT];
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Loading some special rules.
        $l_monitoring_hosts = $p_cat->callback_property_monitoring_host(isys_request::factory());
        $l_rules['C__CATG__MONITORING__HOST']['p_arData'] = $l_monitoring_hosts;

        if (((is_countable($l_monitoring_hosts) && count($l_monitoring_hosts) === 1 && isys_glob_is_edit_mode()) || !$l_catdata) && $_POST[C__GET__NAVMODE] != C__NAVMODE__CANCEL) {
            $l_rules['C__CATG__MONITORING__HOST']['p_strSelectedID'] = current(array_keys($l_monitoring_hosts));
        }

        // Preparing the host-name selection
        $l_prim_ip = isys_cmdb_dao_category_g_ip::instance($this->m_database_component)
            ->get_primary_ip($l_obj_id)
            ->get_row();

        if (!empty($l_prim_ip['isys_catg_ip_list__domain'])) {
            $l_prim_ip['isys_catg_ip_list__domain'] = '.' . trim($l_prim_ip['isys_catg_ip_list__domain']);
        }

        $l_hostname_selection = ($l_catdata['isys_catg_monitoring_list__host_name_selection'] ===
            null) ? C__MONITORING__NAME_SELECTION__HOSTNAME : $l_catdata['isys_catg_monitoring_list__host_name_selection'];

        $this->get_template_component()
            ->assign('hostname_obj_title', isys_monitoring_helper::prepare_valid_name($p_cat->get_obj_name_by_id_as_string($l_obj_id)))
            ->assign('hostname_hostname', $l_prim_ip['isys_catg_ip_list__hostname'])
            ->assign('hostname_hostname_fqdn', $l_prim_ip['isys_catg_ip_list__hostname'] . $l_prim_ip['isys_catg_ip_list__domain'])
            ->assign('host_name_view', isys_monitoring_helper::render_export_hostname($l_obj_id))
            ->assign('host_name_selection', $l_hostname_selection)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}