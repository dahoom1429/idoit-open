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
class isys_cmdb_ui_category_g_cloud_subscriptions extends isys_cmdb_ui_category_global
{
    /**
     * @return string
     */
    public function get_template()
    {
        return isys_module_jdisc::getPath() . 'templates/content/bottom/content/catg__cloud_subscriptions.tpl';
    }

    /**
     * Process method for displaying the template.
     *
     * @global  array                               $index_includes
     *
     * @param   isys_cmdb_dao_category_g_cloud_subscriptions &$p_cat
     *
     * @return  void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        // Initializing some variables.
        $rules = [];
        $catData = $p_cat->get_general_data();

        $l_catgID = $catData['isys_catg_cloud_subscriptions_list__id'];

        // We let the system fill our form-fields.
        $this->fill_formfields($p_cat, $rules, $catData);

        $rules['C__CMDB__CATG__CLOUD_SUBSCRIPTIONS__SUBSCRIBERS']['p_strValue'] = $p_cat->getSubscribersCount($catData);
        
        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $rules);
    }

    /**
     * @param isys_cmdb_dao_category $p_cat
     * @param null                   $p_get_param_override
     * @param null                   $p_strVarName
     * @param null                   $p_strTemplateName
     * @param bool                   $p_bCheckbox
     * @param bool                   $p_bOrderLink
     * @param null                   $p_db_field_name
     *
     * @return null
     * @throws isys_exception_cmdb
     * @throws isys_exception_general
     */
    public function process_list(
        isys_cmdb_dao_category &$p_cat,
        $p_get_param_override = null,
        $p_strVarName = null,
        $p_strTemplateName = null,
        $p_bCheckbox = true,
        $p_bOrderLink = true,
        $p_db_field_name = null
    ) {
        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
    }
}
