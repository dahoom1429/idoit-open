<?php

/**
 * i-doit
 *
 * CMDB UI class for the WAN category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @version     Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_wan extends isys_cmdb_ui_category_global
{
    /**
     * Processes view/edit mode.
     *
     * @param isys_cmdb_dao_category $p_cat
     *
     * @throws Exception
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        if (!($p_cat instanceof isys_cmdb_dao_category_g_wan)) {
            return;
        }

        $l_nets = $l_routers = $l_rules = [];
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        if ($l_catdata['isys_catg_wan_list__id'] > 0) {
            $l_router_res = $p_cat->get_connected_routers($l_catdata['isys_catg_wan_list__id']);

            if ($l_router_res->count() > 0) {
                while ($l_router_row = $l_router_res->get_row()) {
                    $l_routers[] = (int)$l_router_row['isys_obj__id'];
                }
            }

            $l_net_res = $p_cat->get_connected_nets($l_catdata['isys_catg_wan_list__id']);

            if ($l_net_res->count() > 0) {
                while ($l_net_row = $l_net_res->get_row()) {
                    $l_nets[] = (int)$l_net_row['isys_obj__id'];
                }
            }
        }

        $l_rules['C__CATG__WAN__ROUTER']['p_strValue'] = isys_format_json::encode($l_routers);
        $l_rules['C__CATG__WAN__NET']['p_strValue'] = isys_format_json::encode($l_nets);
        $l_rules['C__CATG__WAN__CAPACITY_UP']['p_strValue'] = isys_convert::speed_wan(
            $l_rules['C__CATG__WAN__CAPACITY_UP']['p_strValue'],
            $l_rules['C__CATG__WAN__CAPACITY_UP_UNIT']['p_strSelectedID'],
            C__CONVERT_DIRECTION__BACKWARD
        );
        $l_rules['C__CATG__WAN__CAPACITY_DOWN']['p_strValue'] = isys_convert::speed_wan(
            $l_rules['C__CATG__WAN__CAPACITY_DOWN']['p_strValue'],
            $l_rules['C__CATG__WAN__CAPACITY_DOWN_UNIT']['p_strSelectedID'],
            C__CONVERT_DIRECTION__BACKWARD
        );
        $l_rules['C__CATG__WAN__MAX_CAPACITY_UP']['p_strValue'] = isys_convert::speed_wan(
            $l_rules['C__CATG__WAN__MAX_CAPACITY_UP']['p_strValue'],
            $l_rules['C__CATG__WAN__MAX_CAPACITY_UP_UNIT']['p_strSelectedID'],
            C__CONVERT_DIRECTION__BACKWARD
        );
        $l_rules['C__CATG__WAN__MAX_CAPACITY_DOWN']['p_strValue'] = isys_convert::speed_wan(
            $l_rules['C__CATG__WAN__MAX_CAPACITY_DOWN']['p_strValue'],
            $l_rules['C__CATG__WAN__MAX_CAPACITY_DOWN_UNIT']['p_strSelectedID'],
            C__CONVERT_DIRECTION__BACKWARD
        );

        $this->m_template->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    }
}
