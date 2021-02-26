<?php

/**
 * i-doit
 *
 * CMDB UI: Global category "Backup assigned objects".
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_backup_assigned_objects extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_g_backup_assigned_objects $p_cat
     *
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];

        $this->fill_formfields($p_cat, $l_rules, $p_cat->get_general_data());

        $this->get_template_component()
            ->assign("reverse", 1)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}