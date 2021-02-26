<?php

/**
 * i-doit
 *
 * CMDB global category for net zone scopes.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.8.1
 */
class isys_cmdb_ui_category_g_net_zone_scopes extends isys_cmdb_ui_category_g_virtual
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_layer3_nets = [];
        $l_object_id = (int)($p_cat->get_object_id() ?: $_GET[C__CMDB__GET__OBJECT]);
        $l_quickinfo = new isys_ajax_handler_quick_info();

        // Hide all navbar buttons.
        isys_component_template_navbar::getInstance()
            ->hide_all_buttons();

        // Deactivate the comment field.
        $this->deactivate_commentary();

        $l_res = $p_cat->get_layer3_nets($l_object_id);

        while ($l_row = $l_res->get_row()) {
            $l_layer3_nets[] = [
                'id'    => $l_row['isys_obj__id'],
                'title' => $l_quickinfo->get_quick_info($l_row['isys_obj__id'], $l_row['isys_obj__title'], C__LINK__CATS, false,
                    [C__CMDB__GET__CATS => defined_or_default('C__CATS__NET_IP_ADDRESSES')]),
                'from'  => $l_row['isys_cats_net_zone_list__range_from'],
                'to'    => $l_row['isys_cats_net_zone_list__range_to'],
            ];
        }

        $this->get_template_component()
            ->assign('layer3_usage', $l_layer3_nets);
    }
}