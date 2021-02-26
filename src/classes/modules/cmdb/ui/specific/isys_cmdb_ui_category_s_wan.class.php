<?php

/**
 * i-doit
 *
 * CMDB WAN: Specific category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @version     Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_wan extends isys_cmdb_ui_category_specific
{
    /**
     * Process method.
     *
     * @param  isys_cmdb_dao_category_s_wan $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Make more complex rules.
        $l_rules["C__CATS__WAN_CAPACITY"]["p_strValue"] = isys_convert::speed_wan($l_catdata["isys_cats_wan_list__capacity"],
            $l_catdata["isys_cats_wan_list__isys_wan_capacity_unit__id"], C__CONVERT_DIRECTION__BACKWARD);

        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}