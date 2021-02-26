<?php

/**
 * i-doit
 * CMDB CPU category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @version     Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_cpu extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param isys_cmdb_dao_category $p_cat
     *
     * @return  void
     * @throws Exception
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];
        $l_catdata = $p_cat->get_general_data();

        $newEntry = isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__NEW ||
            (isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__EDIT && isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW'));

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Make rules.
        $l_rules['C__CATG__CPU_FREQUENCY']['p_strValue'] = isys_convert::frequency(
            $l_catdata['isys_catg_cpu_list__frequency'],
            $l_catdata['isys_catg_cpu_list__isys_frequency_unit__id'],
            C__CONVERT_DIRECTION__BACKWARD
        );

        // Apply rules.
        $this->get_template_component()
            ->assign('new_catg_cpu', $newEntry)
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    }
}
