<?php

/**
 * @package    i-doit
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    0.9
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_shares extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param isys_cmdb_dao_category_g_shares $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_rules = [];

        $this->fill_formfields($p_cat, $l_rules, $p_cat->get_general_data());

        $l_rules['C__CATG__SHARES__VOLUME']['p_arData'] = $p_cat->get_dialog_content_drive($_GET[C__CMDB__GET__OBJECT]);

        $this->get_template_component()
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);

        $index_includes['contentbottomcontent'] = $this->activate_commentary($p_cat)
            ->get_template();
    }
}