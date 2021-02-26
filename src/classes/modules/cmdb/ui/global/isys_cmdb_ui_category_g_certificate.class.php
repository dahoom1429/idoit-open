<?php

/**
 * i-doit
 *
 * CMDB UI: global category for templates
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Selcuk Kekec <skekec@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_certificate extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_g_certificate $p_cat
     *
     * @author  Selcuk Kekec <skekec@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];

        $this->fill_formfields($p_cat, $l_rules, $p_cat->get_general_data());

        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}