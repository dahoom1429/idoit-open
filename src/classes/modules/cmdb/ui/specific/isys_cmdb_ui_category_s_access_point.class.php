<?php

/**
 * i-doit
 *
 * CMDB Active Directory: Specific category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @version     Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_access_point extends isys_cmdb_ui_category_specific
{
    /**
     * Define if this sub category is multivalued or not.
     *
     * @return  boolean
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function is_multivalued()
    {
        return true;
    }

    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_s_access_point $p_cat
     *
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        $l_rules["C__CATS__ACCESS_POINT_BRODCAST_SSID"]["p_arData"] = $l_rules["C__CATS__ACCESS_POINT_MAC_FILTER"]["p_arData"] = get_smarty_arr_YES_NO();

        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}