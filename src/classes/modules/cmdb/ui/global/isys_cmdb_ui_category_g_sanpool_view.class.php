<?php

/**
 * i-doit
 *
 * CMDB UI: Global category storage.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_sanpool_view extends isys_cmdb_ui_category_global
{
    /**
     * show the detail-template for subcategories global storage.
     *
     * @param  isys_cmdb_dao_category_g_sanpool_view $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $this->deactivate_commentary()
            ->get_template_component()
            ->assign("ldevclient_arr", $p_cat->get_ldevclient())
            ->assign("ldevserver_arr", $p_cat->get_ldevserver())
            ->assign("fc_port_arr", $p_cat->get_fc_port())
            ->assign("hba_arr", $p_cat->get_hba())
            ->assign("san_chains_arr", $p_cat->get_san_chains())
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");

        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__EDIT);
    }
}

?>