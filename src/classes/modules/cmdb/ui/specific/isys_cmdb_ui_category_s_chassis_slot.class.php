<?php

/**
 * i-doit
 *
 * CMDB Specific category chassis.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.0
 */
class isys_cmdb_ui_category_s_chassis_slot extends isys_cmdb_ui_category_specific
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @global  array                  $index_includes
     *
     * @return array|void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        if (!($p_cat instanceof isys_cmdb_dao_category_s_chassis_slot)) {
            return;
        }

        $l_rules = $l_items = [];
        $l_gets = isys_module_request::get_instance()
            ->get_gets();
        $l_obj_id = $l_gets[C__CMDB__GET__OBJECT];

        $l_id = isset($_GET[C__CMDB__GET__CATLEVEL]) && $_GET[C__CMDB__GET__CATLEVEL] ? $_GET[C__CMDB__GET__CATLEVEL] : @$_POST[C__GET__ID];
        if ($l_id > 0) {
            $l_catdata = $p_cat->get_data_by_id($l_id)
                ->get_row();

            $l_request = isys_request::factory()
                ->set_category_data_id($l_catdata['isys_cats_chassis_slot_list__id'])
                ->set_object_id($l_obj_id);
            $l_items = $p_cat->callback_property_assigned_devices($l_request);

            $l_rules['C__CMDB__CATS__CHASSIS_SLOT__TITLE']['p_strValue'] = $l_catdata['isys_cats_chassis_slot_list__title'];
            $l_rules['C__CMDB__CATS__CHASSIS_SLOT__INSERTION']['p_strSelectedID'] = $l_catdata['isys_cats_chassis_slot_list__insertion'];
            $l_rules['C__CMDB__CATS__CHASSIS_SLOT__CONNECTOR_TYPE']['p_strSelectedID'] = $l_catdata['isys_cats_chassis_slot_list__isys_chassis_connector_type__id'];
            $l_rules['C__CMDB__CATS__CHASSIS__ITEM_ASSIGNMENT']['p_arData'] = $l_items;
            $l_rules['C__CMDB__CAT__COMMENTARY_' . $p_cat->get_category_type() .
            $p_cat->get_category_id()]['p_strValue'] = $l_catdata['isys_cats_chassis_slot_list__description'];
        } else {
            $l_catdata = null;
        }

        $l_rules['C__CMDB__CATS__CHASSIS_SLOT__INSERTION']['p_arData'] = isys_cmdb_dao_category_s_chassis::get_insertion();
        $l_rules['C__CMDB__CATS__CHASSIS__ITEM_ASSIGNMENT']['p_bLinklist'] = !isys_glob_is_edit_mode();

        $l_new_slot = false;

        if (!isset($l_catdata['isys_cats_chassis_slot_list__id'])) {
            $l_new_slot = true;
            $l_rules['C__CMDB__CATS__CHASSIS_SLOT__INSERTION']['p_strSelectedID'] = 1;
        }

        $this->get_template_component()
            ->assign('new_slot', $l_new_slot)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }

    /**
     * Process list method.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @param null                     $p_get_param_override
     * @param null                     $p_strVarName
     * @param null                     $p_strTemplateName
     * @param bool                     $p_bCheckbox
     * @param bool                     $p_bOrderLink
     * @param null                     $p_db_field_name
     *
     * @return bool
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
        $this->list_view("isys_cats_chassis_slot", $_GET[C__CMDB__GET__OBJECT], isys_cmdb_dao_list_cats_chassis_slot::build($this->get_database_component(), $p_cat));

        return true;
    }
}
