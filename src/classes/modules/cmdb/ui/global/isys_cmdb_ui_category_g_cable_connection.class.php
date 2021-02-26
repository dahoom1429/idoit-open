<?php

/**
 * i-doit
 *
 * UI: global category for cable connection.
 *
 * @author        Van Quyen Hoang <qhoang@i-doit.org>
 * @package       i-doit
 * @subpackage    CMDB_Categories
 * @copyright     synetics GmbH
 * @license       http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_cable_connection extends isys_cmdb_ui_category_global
{
    /**
     * @param isys_cmdb_dao_category $p_cat
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_object_id = $p_cat->get_object_id() ?: $_GET[C__CMDB__GET__OBJECT];
        $l_quick_info = new isys_ajax_handler_quick_info();
        $l_connection = [];

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons()
            ->deactivate_all_buttons();

        // This statement will retrieve all connections of the current cable object.
        $l_sql = "SELECT
                isys_catg_connector_list__id,
                isys_catg_connector_list__title,
                isys_cable_connection__id,
                isys_catg_connector_list__assigned_category,
                isys_obj__id,
                isys_obj__title
            FROM isys_catg_connector_list
            LEFT JOIN isys_cable_connection ON isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_connector_list__isys_obj__id
            WHERE isys_cable_connection__isys_obj__id = " . $p_cat->convert_sql_id($l_object_id) . ";";

        $l_res = $p_cat->retrieve($l_sql);

        if (is_countable($l_res) && count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                if (!isset($l_connection[$l_row['isys_cable_connection__id']])) {
                    $l_connection[$l_row['isys_cable_connection__id']] = [];
                }

                if ($l_row["isys_catg_connector_list__assigned_category"] != "" && $l_row["isys_catg_connector_list__assigned_category"] != null) {
                    $l_categoryA = (is_numeric($l_row["isys_catg_connector_list__assigned_category"])) ? $l_row["isys_catg_connector_list__assigned_category"] : (defined($l_row["isys_catg_connector_list__assigned_category"]) ? constant($l_row["isys_catg_connector_list__assigned_category"]) : defined_or_default('C__CATG__CONNECTOR'));
                } else {
                    $l_categoryA = defined_or_default('C__CATG__CONNECTOR');
                }

                $l_arrMaster = [
                    C__CMDB__GET__OBJECT   => $l_row['isys_obj__id'],
                    C__CMDB__GET__CATG     => $l_categoryA,
                    C__CMDB__GET__CATLEVEL => $l_row['isys_catg_connector_list__id'],
                ];

                $l_connection[$l_row['isys_cable_connection__id']][] = [
                    'object_link'     => $l_quick_info->get_quick_info($l_row['isys_obj__id'], $l_row['isys_obj__title'], C__LINK__OBJECT),
                    'connection_link' => '<a href="' . isys_helper_link::create_catg_item_url($l_arrMaster) . '">' . $l_row["isys_catg_connector_list__title"] . '</a>'
                ];
            }
        }

        $this->deactivate_commentary()
            ->get_template_component()
            ->assign('connections', $l_connection)
            ->include_template('contentbottomcontent', $this->get_template());
    }
}