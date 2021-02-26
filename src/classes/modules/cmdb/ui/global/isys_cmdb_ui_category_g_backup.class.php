<?php

/**
 * i-doit
 *
 * CMDB UI: Global category "Backup".
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_backup extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_g_backup $p_cat
     *
     * @return  void
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        $l_rules["C__CATG__BACKUP__ASSIGNED_OBJECT"]["p_strSelectedID"] = null;

        if ($l_catdata["isys_connection__isys_obj__id"] !== null) {
            $l_rules["C__CATG__BACKUP__ASSIGNED_OBJECT"]["p_strSelectedID"] = $l_catdata["isys_connection__isys_obj__id"];
        }

        $this->get_template_component()
            ->assign('backup_type', $l_catdata["isys_catg_backup_list__isys_backup_type__id"])
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}