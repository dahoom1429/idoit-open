<?php

class isys_cmdb_ui_category_g_connection_endpoint extends isys_cmdb_ui_category_global
{
    /**
     * Show the detail-template for subcategories of application.
     *
     * @param   isys_cmdb_dao_category_g_connection_endpoint $p_cat
     *
     * @return  array|void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $rules = [];

        $catData = $p_cat->get_general_data();

        // We let the system fill our form-fields.
        $this->fill_formfields($p_cat, $rules, $catData);

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons()
            ->deactivate_all_buttons();
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $rules)
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1");
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
            ->hide_all_buttons()
            ->deactivate_all_buttons();
        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
    }
}
