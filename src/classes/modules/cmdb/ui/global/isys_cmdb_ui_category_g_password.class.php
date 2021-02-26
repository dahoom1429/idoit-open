<?php

/**
 * i-doit
 *
 * CMDB UI: Password category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_password extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param isys_cmdb_dao_category $p_cat
     *
     * @throws Exception
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        $l_rules["C__CATG__PASSWORD__PASSWORD"]["p_strValue"] = isys_helper_crypt::decrypt($l_catdata["isys_catg_password_list__password"]);

        // Set rules.
        $this->activate_commentary($p_cat)
            ->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}