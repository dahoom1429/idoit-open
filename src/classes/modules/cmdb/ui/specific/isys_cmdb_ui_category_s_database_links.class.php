<?php

/**
 * i-doit
 *
 * CMDB Specific category DBMS.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dsteucken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_database_links extends isys_cmdb_ui_category_specific
{
    /**
     * Process method.
     *
     * @param  isys_cmdb_dao_category_s_database_links $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        $l_rules["C__CMDB__CATS__DATABASE_LINKS__PUBLIC"]["p_arData"] = get_smarty_arr_YES_NO();
        $l_rules["C__CMDB__CATS__DATABASE_LINKS__SCHEMA"]["p_strSelectedID"] = $l_catdata["isys_connection__isys_obj__id"];

        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}