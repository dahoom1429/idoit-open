<?php

/**
 * i-doit
 *
 * CMDB Active Directory: Specific category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_cluster_service_assigned_obj extends isys_cmdb_ui_category_specific
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @return  void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        return;
    }

    /**
     * Process list method.
     *
     * @param   isys_cmdb_dao_category_s_cluster_service_assigned_obj $p_cat
     *
     * @return  null
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
        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__NEW)
            ->set_active(false, C__NAVBAR_BUTTON__EDIT)
            ->set_active(false, C__NAVBAR_BUTTON__PRINT)
            ->set_active(false, C__NAVBAR_BUTTON__ARCHIVE)
            ->set_visible(false, C__NAVBAR_BUTTON__NEW)
            ->set_visible(false, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(false, C__NAVBAR_BUTTON__PRINT)
            ->set_visible(false, C__NAVBAR_BUTTON__ARCHIVE);

        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, "isys_catg_cluster_service_list");
    }

    /**
     * UI constructor.
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("cats__application.tpl");
    }
}

?>