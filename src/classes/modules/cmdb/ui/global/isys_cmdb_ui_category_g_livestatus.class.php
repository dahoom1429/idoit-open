<?php

/**
 * i-doit
 *
 * UI: global UI class for livestatus category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.3.0
 */
class isys_cmdb_ui_category_g_livestatus extends isys_cmdb_ui_category_global
{
    /**
     * Processes the UI for the livestatus category.
     *
     * @param   isys_cmdb_dao_category_g_livestatus $p_cat The corresponding category DAO
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_obj_id = $_GET[C__CMDB__GET__OBJECT];

        $l_livestatus_host_id = isys_cmdb_dao_category_g_monitoring::instance($this->m_database_component)
            ->get_data(null, $l_obj_id)
            ->get_row_value('isys_catg_monitoring_list__isys_monitoring_hosts__id');

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons();

        $this->deactivate_commentary()
            ->get_template_component()
            ->assign('obj_id', $l_obj_id)
            ->assign('livestatus_host', $l_livestatus_host_id)
            ->assign('hostname', isys_monitoring_helper::render_export_hostname($l_obj_id))
            ->assign('states', isys_format_json::encode(isys_monitoring_helper::get_state_info()));
    }
}