<?php

/**
 * i-doit
 *
 * CMDB UI: specific category for layer 2 nets
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-8
 * @author      Selcuk Kekec <skekec@synetics.de>
 */
class isys_cmdb_ui_category_s_layer2_net extends isys_cmdb_ui_category_specific
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @return  void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        if (!($p_cat instanceof isys_cmdb_dao_category_s_layer2_net)) {
            return;
        }

        $l_rules = [];
        $l_cat_data = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_cat_data);

        // Add some specific rules.
        $l_rules['C__CATS__LAYER2_STANDARD_VLAN']['p_arData'] = get_smarty_arr_YES_NO();
        $l_rules['C__CATS__LAYER2__LAYER3_NET']["p_strSelectedID"] = isys_format_json::encode($p_cat->get_layer3_assignments_as_array($l_cat_data['isys_cats_layer2_net_list__id']));
        $l_rules['C__CATS__LAYER2__VRF_CAPACITY']["p_strValue"] = isys_convert::speed_wan($l_cat_data['isys_cats_layer2_net_list__vrf_capacity'],
            $l_cat_data['isys_cats_layer2_net_list__isys_wan_capacity_unit'], C__CONVERT_DIRECTION__BACKWARD);

        $this->get_template_component()
            ->assign('ip_helper_address', $p_cat->get_iphelper_adress($l_cat_data['isys_cats_layer2_net_list__id']))
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    }
}
