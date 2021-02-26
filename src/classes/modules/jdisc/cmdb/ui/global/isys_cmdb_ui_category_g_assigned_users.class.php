<?php

/**
 * i-doit
 *
 * CMDB UI: Global category (category type is accounting)
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 */
class isys_cmdb_ui_category_g_assigned_users extends isys_cmdb_ui_category_global
{
    /**
     * @return string
     */
    public function get_template()
    {
        return isys_module_jdisc::getPath() . 'templates/content/bottom/content/catg__assigned_users.tpl';
    }

    /**
     * Process method for displaying the template.
     *
     * @global  array                               $index_includes
     *
     * @param   isys_cmdb_dao_category_g_assigned_users &$p_cat
     *
     * @return  void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        // Initializing some variables.
        $l_rules = [];
        $l_catdata = $p_cat->get_general_data();

        // We let the system fill our form-fields.
        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        $l_rules['C__CMDB__CATG__ASSIGNED_USERS__ASSIGNED_OBJECT']['p_strValue'] = $l_catdata['connectedObjectId'];

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}