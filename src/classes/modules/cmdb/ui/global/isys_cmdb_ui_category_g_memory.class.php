<?php

/**
 * i-doit
 *
 * CMDB Memory
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_memory extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @global  array                           $index_includes
     *
     * @param   isys_cmdb_dao_category_g_memory $p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_rules = [];

        $l_catdata = $p_cat->get_general_data();

        $l_catdata["isys_catg_memory_list__capacity"] = isys_convert::memory($l_catdata["isys_catg_memory_list__capacity"],
            $l_catdata["isys_catg_memory_list__isys_memory_unit__id"], C__CONVERT_DIRECTION__BACKWARD);

        $l_catdata["isys_catg_memory_list__capacity"] = isys_convert::formatNumber($l_catdata["isys_catg_memory_list__capacity"]);

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Apply rules.
        $this->get_template_component()
            ->assign("new_catg_memory", (isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__NEW ||
                isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__EDIT && isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW')))
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes["contentbottomcontent"] = $this->activate_commentary($p_cat)
            ->get_template();
    }
}