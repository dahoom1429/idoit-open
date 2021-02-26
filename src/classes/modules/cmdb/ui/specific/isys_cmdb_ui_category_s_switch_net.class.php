<?php

/**
 * i-doit
 *
 * CMDB Active Directory: Specific category switch net
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @version     Niclas Potthast <npotthast@i-doit.org>
 * @version     Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_switch_net extends isys_cmdb_ui_category_specific
{
    /**
     * Show the detail-template for specific category switch net.
     *
     * @param   isys_cmdb_dao_category_s_switch_net $p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_vlan = [];

        $l_catdata = $p_cat->get_general_data();

        // Assigning the rules.
        $l_rules["C__CMDB__CATS__SWITCH_NET__VLAN"]["p_strSelectedID"] = $l_catdata['isys_cats_switch_net_list__isys_vlan_management_protocol__id'];
        $l_rules["C__CMDB__CATS__SWITCH_NET__ROLE"]["p_strSelectedID"] = $l_catdata['isys_cats_switch_net_list__isys_switch_role__id'];
        $l_rules["C__CMDB__CATS__SWITCH_NET__SPANNING_TREE"]["p_strSelectedID"] = $l_catdata['isys_cats_switch_net_list__isys_switch_spanning_tree__id'];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_cats_switch_net_list__description"];

        // Retrieving list of assigned VLANs
        $l_vlans = $p_cat->get_assigned_vlans($_GET[C__CMDB__GET__OBJECT], 'isys_obj__title ASC');
        while ($l_vlan_row = $l_vlans->get_row()) {
            $l_vlan[] = [
                'layer2_vlan_id' => $l_vlan_row['isys_cats_layer2_net_list__ident'],
                'obj_id'         => $l_vlan_row['isys_obj__id'],
                'default'        => $l_vlan_row['isys_cats_layer2_net_assigned_ports_list__default'],
                'obj_title'      => $l_vlan_row['isys_obj__title']
            ];
        }

        // Assigning the template variables.
        $this->get_template_component()
            ->assign('vlans', $l_vlan)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}