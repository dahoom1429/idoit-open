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
class isys_cmdb_ui_category_s_chassis extends isys_cmdb_ui_category_specific
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @global  array                  $index_includes
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @return array|void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        if (!($p_cat instanceof isys_cmdb_dao_category_s_chassis)) {
            return;
        }

        $l_category_entry = null;
        $l_type = null;
        $l_rules = [];
        $l_gets = isys_module_request::get_instance()
            ->get_gets();
        $l_quickinfo = new isys_ajax_handler_quick_info();
        $l_obj_id = $l_gets[C__CMDB__GET__OBJECT];
        $l_catdata = $p_cat->get_general_data();
        $l_request = isys_request::factory()
            ->set_category_data_id($l_catdata['isys_cats_chassis_list__id'])
            ->set_object_id($l_obj_id);

        if (isset($l_catdata['isys_cats_chassis_list__isys_catg_netp_list__id'])) {
            $l_category_entry = $l_catdata['isys_cats_chassis_list__isys_catg_netp_list__id'];
            $l_type = 'C__CATG__NETWORK_INTERFACE';
            $l_category_info = $p_cat->get_local_interface($l_obj_id, $l_category_entry)
                ->__to_array();
        } else if (isset($l_catdata['isys_cats_chassis_list__isys_catg_pc_list__id'])) {
            $l_category_entry = $l_catdata['isys_cats_chassis_list__isys_catg_pc_list__id'];
            $l_type = 'C__CATG__POWER_CONSUMER';
            $l_category_info = $p_cat->get_local_power_consumer($l_obj_id, $l_category_entry)
                ->__to_array();
        } else if (isset($l_catdata['isys_cats_chassis_list__isys_catg_hba_list__id'])) {
            $l_category_entry = $l_catdata['isys_cats_chassis_list__isys_catg_hba_list__id'];
            $l_type = 'C__CATG__HBA';
            $l_category_info = $p_cat->get_local_hba($l_obj_id, $l_category_entry)
                ->__to_array();
        }

        $l_value = (isset($l_catdata['isys_connection__isys_obj__id']) ? $l_catdata['isys_connection__isys_obj__id'] : '');

        if ($l_value > 0) {
            $this->get_template_component()
                ->assign('view_field_content', isys_application::instance()->container->get('language')
                        ->get('LC__CMDB__CATS__CHASSIS__EXTERNAL_OBJECT') . ': ' .
                    $l_quickinfo->get_quick_info($l_value, $p_cat->get_obj_name_by_id_as_string($l_value), C__LINK__OBJECT));
        } else if ($l_category_entry > 0) {
            $this->get_template_component()
                ->assign('view_field_content', isys_application::instance()->container->get('language')
                        ->get('LC__CMDB__CATG__VD__LOCAL_DEVICE') . ': ' . $l_category_info['title']);
        } else {
            $this->get_template_component()
                ->assign('view_field_content', '- ' . isys_application::instance()->container->get('language')
                        ->get('LC__CMDB__CATS__CHASSIS__NOTHING_ASSIGNED') . ' -');
        }

        $l_rules['C__CMDB__CATS__CHASSIS__LOCAL_ASSIGNMENT']['p_arData'] = $p_cat->get_local_devices_as_array($l_obj_id);
        $l_rules['C__CMDB__CATS__CHASSIS__LOCAL_ASSIGNMENT']['p_strSelectedID'] = (!empty($l_category_entry) && !empty($l_type)) ? $l_category_entry . '_' . $l_type : '';
        $l_rules['C__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES']['p_strSelectedID'] = $l_value;
        $l_rules['C__CMDB__CATS__CHASSIS__ROLE']['p_strSelectedID'] = $l_catdata['isys_cats_chassis_list__isys_chassis_role__id'];
        $l_rules['C__CMDB__CATS__CHASSIS__SLOT_ASSIGNMENT']['p_bLinklist'] = !isys_glob_is_edit_mode();
        $l_rules['C__CMDB__CATS__CHASSIS__SLOT_ASSIGNMENT']['p_arData'] = $p_cat->callback_property_assigned_slots($l_request);

        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_cats_chassis_list__description"];

        $this->get_template_component()
            ->assign("editmode", isys_glob_is_edit_mode())
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }

    /**
     * Process list method.
     *
     * @param   isys_cmdb_dao_category $p_cat
     * @param   null                   $p_get_param_override
     * @param   null                   $p_strVarName
     * @param   null                   $p_strTemplateName
     * @param   bool                   $p_bCheckbox
     * @param   bool                   $p_bOrderLink
     * @param   null                   $p_db_field_name
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function process_list(
        isys_cmdb_dao_category &$p_cat,
        $p_get_param_override = null,
        $p_strVarName = null,
        $p_strTemplateName = null,
        $p_bCheckbox = true,
        $p_bOrderLink = true,
        $p_db_field_name = null
    ) {
        $this->list_view(null, $_GET[C__CMDB__GET__OBJECT], isys_cmdb_dao_list_cats_chassis::build($this->get_database_component(), $p_cat), $p_get_param_override,
            $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink);
    }
}
