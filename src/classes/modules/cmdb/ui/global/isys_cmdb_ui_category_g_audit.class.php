<?php

/**
 * i-doit
 *
 * UI: global category for audits
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_audit extends isys_cmdb_ui_category_global
{
    /**
     * @param isys_cmdb_dao_category $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $this->fill_formfields($p_cat, $l_rules, $p_cat->get_general_data())
            ->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}