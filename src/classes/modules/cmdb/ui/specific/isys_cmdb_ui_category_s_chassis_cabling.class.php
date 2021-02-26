<?php

/**
 * i-doit
 *
 * CMDB Specific category chassis.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_chassis_cabling extends isys_cmdb_ui_category_specific
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_s_chassis_cabling $p_cat
     *
     * @global  array                                    $index_includes
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_obj_id = (int)$_GET[C__CMDB__GET__OBJECT];
        $l_rules = $l_dialog_data = [];

        $l_objects = isys_cmdb_dao_category_s_chassis::instance($this->get_database_component())
            ->get_assigned_objects($l_obj_id);

        // Prepend the object-array with the chassis itself!
        array_unshift($l_objects, $p_cat->get_object_by_id($l_obj_id)
            ->get_row());

        // At first we prepare an object list for our dialog-fields.
        foreach ($l_objects as $l_object) {
            $l_dialog_data[$l_object['isys_obj__id']] = $l_object['isys_obj__title'];
        }

        // Add the chassis.
        $l_dialog_data[$l_obj_id] = isys_cmdb_dao_category_s_chassis::instance($this->get_database_component())
            ->get_obj_name_by_id_as_string($l_obj_id);

        foreach ($l_objects as $l_object) {
            $l_log_ports = $p_cat->get_log_ports_for_ui($l_object['isys_obj__id'], $l_rules);
            $l_ports = $p_cat->get_ports_for_ui($l_object['isys_obj__id'], $l_rules);
            $l_fc_ports = $p_cat->get_fc_ports_for_ui($l_object['isys_obj__id'], $l_rules);

            $l_log_port_dialog_name = 'C__CMDB__CATS__CHASSIS_CABLING__NEW_LOG_PORT_' . $l_object['isys_obj__id'];
            $l_log_port_l2net_name = 'C__CMDB__CATS__CHASSIS_CABLING__NEW_LOG_PORT_L2NET_' . $l_object['isys_obj__id'];

            $l_tpl_objects[] = [
                'id'                   => $l_object['isys_obj__id'],
                'title'                => $l_object['isys_obj__title'],
                'type_id'              => $l_object['isys_obj_type__id'],
                'type_title'           => isys_application::instance()->container->get('language')
                    ->get($l_object['isys_obj_type__title']),
                'counter'              => [
                    'ports'     => is_countable($l_ports) ? count($l_ports) : 0,
                    'fc_ports'  => is_countable($l_fc_ports) ? count($l_fc_ports) : 0,
                    'log_ports' => is_countable($l_log_ports) ? count($l_log_ports) : 0
                ],
                'ports'                => $l_ports,
                'log_ports'            => $l_log_ports,
                'fc_ports'             => $l_fc_ports,
                'log_port_dialog_name' => $l_log_port_dialog_name,
                'log_port_l2net_name'  => $l_log_port_l2net_name
            ];

            $l_data = $l_dialog_data;

            unset($l_data[$l_object['isys_obj__id']]);

            // Now we add the dialog-data to the form element, excluding the current object.
            $l_rules[$l_log_port_dialog_name]['p_arData'] = $l_data;
        }

        $this->get_template_component()
            ->assign('editmode', $this->get_template_component()
                ->editmode())
            ->assign('objects', $l_tpl_objects)
            ->assign('bShowCommentary', 0)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes["contentbottomcontent"] = $this->get_template();
    }
}
