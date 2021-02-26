<?php

/**
 * i-doit
 *
 * CMDB UI: Application category (category type is global):
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Andre Wösten <awoesten@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_application extends isys_cmdb_ui_category_global
{
    /**
     * Show the detail-template for subcategories of application.
     *
     * @param   isys_cmdb_dao_category_g_application $p_cat
     *
     * @return  array|void
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        if (!empty($l_rules['C__CATG__LIC_ASSIGN__LICENSE']['p_strSelectedID'])) {
            // Check if license is in status normal
            $entry = isys_cmdb_dao_category_s_lic::instance($this->get_database_component())->get_data($l_rules['C__CATG__LIC_ASSIGN__LICENSE']['p_strSelectedID'])->__as_array();

            if (isset($entry[0]['isys_obj__status'], $entry[0]['isys_cats_lic_list__status'])
                && (
                    $entry[0]['isys_obj__status'] != C__RECORD_STATUS__NORMAL
                    || $entry[0]['isys_cats_lic_list__status'] != C__RECORD_STATUS__NORMAL
                )
            ) {
                unset($l_rules['C__CATG__LIC_ASSIGN__LICENSE']['p_strSelectedID']);
            }
        }

        $l_rules["C__CATG__APPLICATION_OBJ_APPLICATION"]["p_strSelectedID"] = $l_catdata['isys_connection__isys_obj__id'];
        $l_rules["C__CATG__APPLICATION_OBJ_APPLICATION"]["multiselection"] = (isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__NEW);
        $l_rules["C__CATG__APPLICATION_TYPE"]["p_strSelectedID"] = (($l_catdata['isys_obj__isys_obj_type__id'] ?: $_GET[C__CMDB__GET__OBJECTTYPE]) ==
            defined_or_default('C__OBJTYPE__OPERATING_SYSTEM')) ? defined_or_default('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM') : defined_or_default('C__CATG__APPLICATION_TYPE__SOFTWARE');
        // This is used for the dialog+ popup to be able to create category entries.
        $l_rules["C__CATG__APPLICATION_VERSION"]["p_strTable"] = 'isys_catg_version_list';
        $l_rules["C__CATG__APPLICATION_VERSION"]["condition"] = 'isys_catg_version_list__isys_obj__id = ' .
            $p_cat->convert_sql_id($l_catdata['isys_connection__isys_obj__id']);
        $l_rules["C__CATG__APPLICATION_VERSION"]["p_strCatTableObj"] = $p_cat->convert_sql_id($l_catdata['isys_connection__isys_obj__id']);

        $l_ajax_param = [
            C__GET__AJAX      => 1,
            C__GET__AJAX_CALL => 'software',
        ];

        $l_smarty_ajax_param = [
            C__GET__AJAX      => 1,
            C__GET__AJAX_CALL => 'smartyplugin',
            'mode'            => 'edit'
        ];

        // ID-3996: On multiple save redirect to list view
        if ($_GET[C__CMDB__GET__VIEWMODE] != C__CMDB__VIEW__LIST_OBJECT && $_POST[C__GET__NAVMODE] == C__NAVMODE__NEW) {
            $tplNavbar = isys_component_template_navbar::getInstance();
            $link = isys_helper_link::create_url([
                C__CMDB__GET__VIEWMODE => C__CMDB__VIEW__LIST_CATEGORY,
                C__CMDB__GET__OBJECT   => $_GET[C__CMDB__GET__OBJECT],
                C__CMDB__GET__CATG     => $_GET[C__CMDB__GET__CATG]
            ]);
            $callback = 'function() {window.location = \'' . $link . '\';}';
            $saveOnclick = 'document.isys_form.navMode.value=\'' . C__NAVMODE__SAVE . ' \';';
            $saveOnclick .= 'form_submit(\'\', \'post\', \'no_replacement\', null, ' . $callback . ');';
            if ($tplNavbar->get_save_mode() === 'log') {
                $saveOnclick = 'idoit.callbackManager.registerCallback(\'idoit.popup.C__CATG__APPLICATION.accept\', ' . $callback . ');';
                $saveOnclick .= 'get_commentary(\'idoit.popup.C__CATG__APPLICATION.accept\');';
            }
            $tplNavbar->set_js_onclick($saveOnclick, C__NAVBAR_BUTTON__SAVE);
        }
        $this->get_template_component()
            ->assign("hide_priority", $l_rules['C__CATG__APPLICATION_TYPE']['p_strSelectedID'] != defined_or_default('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM'))
            ->assign("application_ajax_url", isys_helper_link::create_url($l_ajax_param))
            ->assign("smarty_ajax_url", isys_helper_link::create_url($l_smarty_ajax_param))
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
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
        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
    }
}
