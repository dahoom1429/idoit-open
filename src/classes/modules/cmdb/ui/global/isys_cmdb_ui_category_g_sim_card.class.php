<?php

/**
 * i-doit
 *
 * UI: Specific cellphone category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_sim_card extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param  isys_cmdb_dao_category $dao
     *
     * @return array|void
     * @throws Exception
     */
    public function process(isys_cmdb_dao_category $dao)
    {
        $rules = [];
        $categoryData = $dao->get_general_data();

        $categoryData['isys_catg_sim_card_list__twincard'] = ($categoryData['isys_catg_sim_card_list__twincard'] ?: 0);

        $this->fill_formfields($dao, $rules, $categoryData);

        $this->get_template_component()
            ->assign('g_twincard', $categoryData['isys_catg_sim_card_list__twincard'])
            ->smarty_tom_add_rules('tom.content.bottom.content', $rules);
    }
}
