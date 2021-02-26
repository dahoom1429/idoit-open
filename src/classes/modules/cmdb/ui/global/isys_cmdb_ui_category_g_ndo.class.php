<?php

/**
 * i-doit
 *
 * UI: global UI class for NDO category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.3.0
 */
class isys_cmdb_ui_category_g_ndo extends isys_cmdb_ui_category_global
{
    /**
     * Processes the UI for the NDO category.
     *
     * @param   isys_cmdb_dao_category_g_ndo $p_cat The corresponding category DAO
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_obj_id = $_GET[C__CMDB__GET__OBJECT];

        $l_hostname = isys_monitoring_helper::render_export_hostname($l_obj_id);

        if (class_exists('isys_cmdb_dao_category_g_nagios')) {
            $l_status_cgi_link = isys_cmdb_dao_category_g_nagios::instance($this->m_database_component)
                ->get_data(null, $l_obj_id)
                ->get_row_value('isys_monitoring_export_config__address');

            $l_rules = [
                'C__CATG__NDO__STATUS_CGI' => [
                    'p_strValue' => $l_status_cgi_link . $l_hostname
                ]
            ];
        } else {
            isys_notify::info(isys_application::instance()->container->get('language')->get('LC__UNIVERSAL__ADDON_SUGGESTION', ['Nagios']), ['sticky' => true]);
        }

        $l_ndo_host__id = isys_cmdb_dao_category_g_monitoring::instance($this->m_database_component)
            ->get_data(null, $l_obj_id)
            ->get_row_value('isys_catg_monitoring_list__isys_monitoring_hosts__id');

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons();

        $this->deactivate_commentary()
            ->get_template_component()
            ->assign('obj_id', $l_obj_id)
            ->assign('ndo_host', $l_ndo_host__id)
            ->assign('hostname', $l_hostname)
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    }
}
