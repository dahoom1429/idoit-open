<?php

class isys_cmdb_ui_category_g_support_entitlement extends isys_cmdb_ui_category_global
{
    /**
     * @return string
     */
    public function get_template()
    {
        return isys_module_jdisc::getPath() . 'templates/content/bottom/content/catg__support_entitlement.tpl';
    }

    /**
     * Process method for displaying the template.
     *
     * @global  array                               $index_includes
     *
     * @param   isys_cmdb_dao_category_g_support_entitlement &$p_cat
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

        $expiredInDays = isys_tenantsettings::get('gui.empty_value', '-');

        if (!empty($catData['isys_catg_support_entitlement_list__start_date']) &&
            !empty($catData['isys_catg_support_entitlement_list__end_date'])) {
            $expiredInDays = isys_module_jdisc::expiresInDays(
                new DateTime($catData['isys_catg_support_entitlement_list__start_date']),
                new DateTime($catData['isys_catg_support_entitlement_list__end_date'])
            );
        }

        $statusMarker = isys_module_jdisc::getStatusMarker((!is_numeric($expiredInDays) ? false: ($expiredInDays >= 0)));


        // Apply rules.
        $this->get_template_component()
            ->assign('statusMarker', $statusMarker)
            ->assign('expiredInDays', $expiredInDays)
            ->smarty_tom_add_rules("tom.content.bottom.content", $rules);
    }
}
