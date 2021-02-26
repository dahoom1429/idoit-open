<?php

/**
 * i-doit
 *
 * UI: global category for fiber/lead
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_fiber_lead extends isys_cmdb_ui_category_global
{
    /**
     * Processes view/edit mode.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @return  array
     * @throws  isys_exception_dao_cmdb
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        /**
         * @var $p_cat isys_cmdb_dao_category_g_fiber_lead
         */

        $l_quickinfo = new isys_ajax_handler_quick_info;
        $l_list_id = $p_cat->get_list_id();
        $l_rx = $p_cat->find_fiber_usage($l_list_id, 'rx')
            ->get_row();
        $l_tx = $p_cat->find_fiber_usage($l_list_id, 'tx')
            ->get_row();

        $l_rx_link = $l_tx_link = isys_tenantsettings::get('gui.empty_value', '-');

        if (is_array($l_rx)) {
            $l_rx_link = $l_quickinfo->get_quick_info($l_rx['isys_obj__id'], isys_application::instance()->container->get('language')
                    ->get($l_rx['isys_obj_type__title']) . ' > ' . $l_rx['isys_obj__title'] . ' > ' . $l_rx['isys_catg_connector_list__title'], C__LINK__CATG, false,
                [C__CMDB__GET__CATG => defined_or_default('C__CATG__CONNECTOR'), C__CMDB__GET__CATLEVEL => $l_rx['isys_catg_connector_list__id']]);
        }

        if (is_array($l_tx)) {
            $l_tx_link = $l_quickinfo->get_quick_info($l_tx['isys_obj__id'], isys_application::instance()->container->get('language')
                    ->get($l_tx['isys_obj_type__title']) . ' > ' . $l_tx['isys_obj__title'] . ' > ' . $l_tx['isys_catg_connector_list__title'], C__LINK__CATG, false,
                [C__CMDB__GET__CATG => defined_or_default('C__CATG__CONNECTOR'), C__CMDB__GET__CATLEVEL => $l_tx['isys_catg_connector_list__id']]);
        }

        $this->get_template_component()
            ->assign('connected_rx', $l_rx_link)
            ->assign('connected_tx', $l_tx_link);

        return parent::process($p_cat);
    }
}
