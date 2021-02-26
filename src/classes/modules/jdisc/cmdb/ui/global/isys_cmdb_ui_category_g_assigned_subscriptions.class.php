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
class isys_cmdb_ui_category_g_assigned_subscriptions extends isys_cmdb_ui_category_global
{
    /**
     * @return string
     */
    public function get_template()
    {
        return isys_module_jdisc::getPath() . 'templates/content/bottom/content/catg__assigned_subscriptions.tpl';
    }

    /**
     * Process method for displaying the template.
     *
     * @global  array                               $index_includes
     *
     * @param  isys_cmdb_dao_category  &$p_cat
     *
     * @return  void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        // Initializing some variables.
        $rules = [];
        $catData = $p_cat->get_general_data();

        // We let the system fill our form-fields.
        $this->fill_formfields($p_cat, $rules, $catData);

        $rules['C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__ASSIGNED_OBJECT']['p_strValue'] = $catData['connectedObjectId'];

        $smartyAjaxParam = [
            C__GET__AJAX      => 1,
            C__GET__AJAX_CALL => 'smartyplugin',
            'mode'            => 'edit'
        ];

        // Apply rules.
        $this->get_template_component()
            ->assign('smartyAjaxUrl', isys_helper_link::create_url($smartyAjaxParam))
            ->smarty_tom_add_rules('tom.content.bottom.content', $rules);
    }
}
