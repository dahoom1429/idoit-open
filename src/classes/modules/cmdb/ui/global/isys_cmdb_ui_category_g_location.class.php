<?php

/**
 * i-doit
 *
 * CMDB UI: Global category location.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_location extends isys_cmdb_ui_category_global
{
    /**
     * Processing method.
     *
     * @param   isys_cmdb_dao_category &$p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @return  void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];
        $l_catdata = $p_cat->get_general_data();
        $l_object_id = $p_cat->get_object_id();
        $l_parent_is_rack = false;
        $l_rack_quickinfo = '';
        $l_parent_is_segment = false;

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        $language = isys_application::instance()->container->get('language');

        // We will need this request-object for several callbacks.
        if ($l_catdata && $p_cat instanceof isys_cmdb_dao_category_g_location) {
            // Handle gps data:
            $this->m_template->assign('lat', $l_catdata["latitude"])
                ->assign('lng', $l_catdata["longitude"]);

            $l_rules["C__CATG__LOCATION_LATITUDE"]['p_strValue'] = $l_catdata["latitude"];
            $l_rules["C__CATG__LOCATION_LONGITUDE"]['p_strValue'] = $l_catdata["longitude"];
            $l_rules["C__CATG__LOCATION_SNMP_SYSLOCATION"]['p_strValue'] = $l_catdata['isys_catg_location_list__snmp_syslocation'];

            $l_parent_object = $p_cat->get_data(null, $l_catdata["isys_catg_location_list__parentid"], '', null, C__RECORD_STATUS__NORMAL)
                ->get_row();

            // We are inside a segment object - we'll simulate this object is located in the parent rack (if the location parent is a rack).
            if ($l_parent_object['isys_obj_type__const'] == isys_tenantsettings::get('cmdb.rack.segment-template-object-type', 'C__OBJTYPE__RACK_SEGMENT')) {
                $l_parent_parent_object_row = $p_cat->get_data(null, $l_parent_object['isys_catg_location_list__parentid'])
                    ->get_row();

                if ($l_parent_parent_object_row['isys_obj_type__isysgui_cats__id'] == defined_or_default('C__CATS__ENCLOSURE')) {
                    $l_rack_rules = [];
                    $l_assigned_slots = [];

                    $l_rules["C__CATG__LOCATION_SLOT"]["p_arData"] = [];

                    $p_cat->set_object_id($l_parent_object['isys_obj__id'])
                        ->set_object_type_id($l_parent_object['isys_obj_type__id']);

                    $this->fill_formfields($p_cat, $l_rack_rules, $l_parent_object);

                    $l_rack_quickinfo = (new isys_ajax_handler_quick_info)->get_quick_info(
                        $l_parent_parent_object_row['isys_obj__id'],
                        $language->get($l_parent_parent_object_row['isys_obj__title']),
                        C__LINK__OBJECT
                    );

                    $l_assigned_slots_result = isys_cmdb_dao_category_s_chassis::instance($p_cat->get_database_component())
                        ->get_slots_by_assiged_object($l_object_id, $l_parent_object['isys_obj__id']);

                    while ($l_assigned_slots_row = $l_assigned_slots_result->get_row()) {
                        $l_assigned_slots[] = $l_assigned_slots_row['isys_cats_chassis_slot_list__id'];
                    }

                    $l_slots_result = isys_cmdb_dao_category_s_chassis_slot::instance($p_cat->get_database_component())
                        ->get_data(null, $l_parent_object['isys_obj__id'], '', null, C__RECORD_STATUS__NORMAL);

                    while ($l_slots_row = $l_slots_result->get_row()) {
                        $l_rules["C__CATG__LOCATION_SLOT"]["p_arData"][] = [
                            'id'  => $l_slots_row['isys_cats_chassis_slot_list__id'],
                            'val' => $l_slots_row['isys_cats_chassis_slot_list__title'],
                            'sel' => in_array($l_slots_row['isys_cats_chassis_slot_list__id'], $l_assigned_slots)
                        ];
                    }

                    $l_rules['C__CATG__LOCATION_OPTION'] = $l_rack_rules['C__CATG__LOCATION_OPTION'];
                    $l_rules['C__CATG__LOCATION_INSERTION'] = $l_rack_rules['C__CATG__LOCATION_INSERTION'];
                    $l_rules['C__CATG__LOCATION_POS'] = $l_rack_rules['C__CATG__LOCATION_POS'];
                    $l_catdata = $l_parent_object;
                    $l_parent_object = $l_parent_parent_object_row;
                    $l_parent_is_segment = true;
                }
            }

            // Is the object currently located inside a rack (and also allowed to be)?
            if ($l_catdata['isys_obj_type__show_in_rack'] && $l_parent_object['isys_obj_type__isysgui_cats__id'] == defined_or_default('C__CATS__ENCLOSURE')) {
                $l_parent_is_rack = true;
                $l_positions = [];
                $l_available_positions = $p_cat->get_free_rackslots(
                    $l_catdata['isys_catg_location_list__parentid'],
                    $l_catdata['isys_catg_location_list__insertion'],
                    $l_catdata['isys_obj__id'],
                    $l_catdata['isys_catg_location_list__option']
                );

                foreach ($l_available_positions as $l_technical_slot => $l_textlabel) {
                    $l_positions[explode(';', $l_technical_slot)[0]] = $l_textlabel;
                }

                $l_rules["C__CATG__LOCATION_POS"]["p_arData"] = $l_positions;
            }
        }

        // This needs to be done, because "0" can (and will) be misinterpreted by PHP.
        $l_rules['C__CATG__LOCATION_INSERTION']['p_strSelectedID'] = $l_catdata['isys_catg_location_list__insertion'];

        if (!isset($l_rules["C__CATG__LOCATION_SLOT"])) {
            $l_rules["C__CATG__LOCATION_SLOT"] = [];
        }

        if (!isset($l_rules["C__CATG__LOCATION_SLOT"]["p_arData"]) || empty($l_rules["C__CATG__LOCATION_SLOT"]["p_arData"])) {
            $l_rules["C__CATG__LOCATION_SLOT"]["p_arData"] = [
                [
                    'id'  => -1,
                    'val' => isys_tenantsettings::get('gui.empty_value', '-'),
                    'sel' => false
                ]
            ];
        }

        if ($l_rules['C__CATG__LOCATION_OPTION']['p_strSelectedID'] <= 0 && $l_rules['C__CATG__LOCATION_POS']['p_strSelectedID'] <= 0) {
            // Set the insertion to "-1" if no option and position are set.
            $l_rules['C__CATG__LOCATION_INSERTION']['p_strSelectedID'] = -1;
        }

        if (!$l_catdata) {
            $l_catdata['isys_obj_type__show_in_rack'] = $p_cat->is_obj_type_in_rack($p_cat->get_objTypeID($l_object_id));
        }

        $this->get_template_component()
            ->assign('objectId', $l_object_id)
            ->assign('parentObjectId', $l_catdata["isys_catg_location_list__parentid"] ?: 0)
            ->assign('objectTypeAllowedInRack', (int)$l_catdata['isys_obj_type__show_in_rack'] ?: 0)
            ->assign('parent_is_rack', $l_parent_is_rack)
            ->assign('parent_is_segment', $l_parent_is_segment)
            ->assign('rackQuickinfo', $l_rack_quickinfo)
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    }
}
