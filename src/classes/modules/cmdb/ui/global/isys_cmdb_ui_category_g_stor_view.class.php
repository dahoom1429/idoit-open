<?php

/**
 * i-doit
 *
 * CMDB UI: Global category storage
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_stor_view extends isys_cmdb_ui_category_global
{
    /**
     * Show the detail-template for subcategories global storage.
     *
     * @param   isys_cmdb_dao_category_g_stor_view $p_cat
     *
     * @global  array                              $index_includes
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $this->get_template_component()
            ->assign("devices_arr", $p_cat->get_devices())
            ->assign("controllers_arr", $p_cat->get_controllers())
            ->assign("raids_arr", $p_cat->get_raids())
            ->assign("das_chains_arr", $p_cat->get_das_chains())
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons();
        $this->deactivate_commentary();
    }

    /**
     * UI constructor.
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        $this->set_template("catg__stor_view.tpl");
        parent::__construct($p_template);
    }
}

?>