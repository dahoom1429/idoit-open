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
class isys_cmdb_ui_category_s_database_instance extends isys_cmdb_ui_category_specific
{

    /**
     * Process method.
     *
     * @param  isys_cmdb_dao_category_s_database_instance $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        $l_connected_res = $p_cat->get_connected_database_schema($_GET[C__CMDB__GET__OBJECT], $l_catdata["isys_cats_database_instance_list__id"]);
        $l_selected_ids = [];
        while ($l_row = $l_connected_res->get_row()) {
            $l_selected_ids[] = $l_row["isys_obj__id"];
        }

        $l_rules["C__CMDB__CATS__DATABASE_INSTANCE__DBMS"]["p_strSelectedID"] = $l_catdata["isys_connection__isys_obj__id"];
        $l_rules["C__CMDB__CATS__DATABASE_INSTANCE__CONNECTED"]["p_strSelectedID"] = implode(',', $l_selected_ids);

        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}