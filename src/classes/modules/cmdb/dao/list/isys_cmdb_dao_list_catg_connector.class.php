<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_connector extends isys_component_dao_category_table_list
{
    /**
     * Returns array with table headers.
     *
     * @return array
     * @throws Exception
     */
    public function get_fields()
    {
        $language = isys_application::instance()->container->get('language');

        return [
            'isys_catg_connector_list__title'             => $language->get('LC__CMDB__CATG__TITLE'),
            'isys_connection_type__title'                 => $language->get('LC__CATG__CONNECTOR__CONNECTION_TYPE'),
            'connector_name'                              => $language->get('LC__CMDB__CONNECTED_WITH'),
            'isys_catg_connector_list__assigned_category' => $language->get('LC__CMDB__CATG__CATEGORY'),
            'isys_interface__title'                       => $language->get('LC__CATG__CONNECTOR__INTERFACE'),
            'cable_object'                                => $language->get('LC__CMDB__OBJTYPE__CABLE'),
            'fiber_wave_lengths'                          => $language->get('LC__CATG__CONNECTOR__FIBER_WAVE_LENGTHS')
        ];
    }

    /**
     * Modify each row with certain contents.
     *
     * @param array $p_row
     *
     * @return array|void
     * @throws isys_exception_database
     */
    public function modify_row(&$p_row)
    {
        global $g_dirs;

        $language = isys_application::instance()->container->get('language');
        $l_dao_connection = new isys_cmdb_dao_cable_connection($this->m_db);

        $l_quick_info = new isys_ajax_handler_quick_info();
        $p_row['isys_connection__isys_obj__id'] = $l_quick_info->get_quick_info(
            $p_row['isys_connection__isys_obj__id'],
            $l_dao_connection->get_obj_name_by_id_as_string($p_row['isys_connection__isys_obj__id']),
            C__LINK__OBJECT
        );

        $l_connector_id = 0;
        $l_connector_data = $l_dao_connection
            ->get_assigned_connector($p_row['isys_catg_connector_list__id'])
            ->get_row();

        $p_row['connector_name'] = isys_tenantsettings::get('gui.empty_value', '-');

        if ($l_connector_data['isys_catg_connector_list__id'] !== null && $l_connector_data['isys_catg_connector_list__isys_obj__id'] !== null) {
            $l_connector_id = $l_connector_data['isys_catg_connector_list__id'];

            $p_row['connector_name'] = $l_quick_info->get_quick_info(
                $l_connector_data['isys_catg_connector_list__isys_obj__id'],
                $l_connector_data['isys_obj__title'] . ' &raquo; ' . $l_connector_data['isys_catg_connector_list__title'],
                C__LINK__OBJECT,
                false
            );

            $p_row['target_objectID'] = $l_connector_data['isys_obj__id'];
        }

        if (defined('C__MODULE__PRO') && C__MODULE__PRO && class_exists('isys_popup_connection')) {
            $p_row['connector_name'] = '<div class="input-group" style="float:right; width:40px;" data-connector-id="' . $l_connector_id . '">' .
                '<div class="input-group-addon input-group-addon-clickable attach" title="' . $language->get('LC__CABLE_CONNECTION__POPUP_CONNECTION_CONNECT_SELECTED_CONNECTOR') . '" style="border-right:1px solid #aaa">' .
                '<img src="' . $g_dirs['images'] . 'icons/silk/disconnect.png" />' .
                '</div><div class="input-group-addon input-group-addon-clickable detach" title="' . $language->get('LC__CABLE_CONNECTION__POPUP_CONNECTION_DISCONNECT_SELECTED_CONNECTOR') . '">' .
                '<img src="' . $g_dirs['images'] . 'icons/silk/cross.png" />' .
                '</div></div>' . '<span class="connected-connector">' . $p_row['connector_name'] . '</span>';
        }

        // Check for the assigned categories, before they get their real title.
        if ($p_row['isys_catg_connector_list__assigned_category'] !== 'C__CATG__UNIVERSAL_INTERFACE' &&
            $p_row['isys_catg_connector_list__assigned_category'] != defined_or_default('C__CATG__UNIVERSAL_INTERFACE')) {
            $p_row['isys_connection_type__title'] = ($p_row['isys_connection_type__id'] > 0) ? $language->get($p_row['isys_connection_type__title']) : '-';
        } else {
            // Because this category has an own dialog-field for it's "connection type" we need to get it seperately.
            $l_ui_dao = new isys_cmdb_dao_category_g_ui($this->m_db);
            $l_ui_row = $l_ui_dao->get_data(
                null,
                $_GET[C__CMDB__GET__OBJECT],
                'AND isys_catg_ui_list__isys_catg_connector_list__id = ' . $l_ui_dao->convert_sql_id($p_row['isys_catg_connector_list__id'])
            )
                ->get_row();

            $p_row['isys_connection_type__title'] = $language->get($l_ui_row['isys_ui_plugtype__title']);
        }

        /** @var isys_cmdb_dao_category_g_connector $l_dao */
        $l_dao = isys_cmdb_dao_category_g_connector::instance($this->m_db);
        $p_row['isys_catg_connector_list__assigned_category'] = $l_dao->get_assigned_category_title($p_row['isys_catg_connector_list__assigned_category']);

        // Column for interface.
        if (!isset($p_row['isys_interface__title'])) {
            $p_row['isys_interface__title'] = isys_tenantsettings::get('gui.empty_value', '-');
        }

        // Column for cable and its fibers/leads.
        if (isset($p_row['isys_catg_connector_list__isys_cable_connection__id'])) {
            $p_row['cable_object'] = '<span class="cable-name">' . $l_quick_info->get_quick_info($p_row['cable_id'], $p_row['cable_title'], C__LINK__OBJECT) . '</span>';
        } else {
            $p_row['cable_object'] = '<span class="cable-name">' . isys_tenantsettings::get('gui.empty_value', '-') . '</span>';
        }

        // Column for fiber wave lengths.
        $l_assigned_fiber_wave_lengths = $l_dao->get_assigned_fiber_wave_lengths($p_row['isys_obj__id'], $p_row['isys_catg_connector_list__id']);

        $l_fiber_color = [];
        /** @var isys_cmdb_dao_category_g_fiber_lead $l_fiber_lead_dao */
        $l_fiber_lead_dao = isys_cmdb_dao_category_g_fiber_lead::instance($this->m_db);
        $l_fiber_wave_lengths = '';

        if (isset($p_row['isys_catg_connector_list__used_fiber_lead_rx']) && isset($p_row['isys_catg_connector_list__used_fiber_lead_rx']) > 0) {
            $l_fiber_color[] = $l_fiber_lead_dao->get_data($p_row['isys_catg_connector_list__used_fiber_lead_rx'])
                ->get_row_value('isys_cable_colour__title');
        }

        if (isset($p_row['isys_catg_connector_list__used_fiber_lead_tx']) && isset($p_row['isys_catg_connector_list__used_fiber_lead_tx']) > 0) {
            $l_fiber_color[] = $l_fiber_lead_dao->get_data($p_row['isys_catg_connector_list__used_fiber_lead_tx'])
                ->get_row_value('isys_cable_colour__title');
        }

        if (count($l_fiber_color)) {
            $l_fiber_wave_lengths = implode(', ', $l_fiber_color);
        }

        if ($l_assigned_fiber_wave_lengths->count() === 0) {
            $p_row['fiber_wave_lengths'] = isys_tenantsettings::get('gui.empty_value', '-');
        } else {
            $l_assigned_fiber_wave_length_list = [];

            while ($l_assigned_fiber_wave_length = $l_assigned_fiber_wave_lengths->get_row()) {
                $l_assigned_fiber_wave_length_list[] = $l_assigned_fiber_wave_length['isys_fiber_wave_length__title'];
            }

            $l_fiber_wave_lengths .= (strlen($l_fiber_wave_lengths) > 0 ? ' / ' : '') . implode(', ', $l_assigned_fiber_wave_length_list);
        }

        $p_row['fiber_wave_lengths'] = $l_fiber_wave_lengths;

        return $p_row;
    }
}
