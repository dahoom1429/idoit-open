<?php

use idoit\Module\CustomFields\PropertyTypes;

/**
 * CMDB custom fields category.
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_custom_fields extends isys_cmdb_ui_category_global
{
    /**
     * Gets custom category title
     *
     * @param   isys_cmdb_dao_category &$p_cat
     *
     * @return  string
     */
    public function gui_get_title(isys_cmdb_dao_category &$p_cat)
    {
        // Adding the language manager, for custom translations: ID-1649.
        return isys_application::instance()->container->get('language')
            ->get(isys_cmdb_dao_category_g_custom_fields::instance($p_cat->get_database_component())
                ->get_category_title($_GET[C__CMDB__GET__CATG_CUSTOM]));
    }

    /**
     * Processes the user interface.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @return  array|void
     * @throws  Exception
     * @version Van Quyen Hoang    <qhoang@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $language = isys_application::instance()->container->get('language');
        $l_gets = isys_module_request::get_instance()->get_gets();

        $l_object_id = (isset($l_gets[C__CMDB__GET__OBJECT])) ? $l_gets[C__CMDB__GET__OBJECT] : $_GET[C__CMDB__GET__OBJECT];
        $l_description = '';
        $l_catg_custom_id = $p_cat->get_catg_custom_id() ?: $_GET[C__CMDB__GET__CATG_CUSTOM];

        $l_config = $p_cat->get_config($l_catg_custom_id);
        $l_rules = [];

        /**
         * Setup rules for property visibility
         */
        foreach ($l_config as $propertyKey => $propertyConfig) {
            if (is_array($propertyConfig) && array_key_exists('visibility', $propertyConfig)) {
                switch ($propertyConfig['visibility']) {
                    case 'hidden':
                        $l_rules['C__CATG__CUSTOM__' . $propertyKey]['p_bInvisible'] = true;
                        break;
                    case 'readonly':
                        $l_rules['C__CATG__CUSTOM__' . $propertyKey]['p_bDisabled'] = true;
                        $l_rules['C__CATG__CUSTOM__' . $propertyKey]['p_bReadonly'] = true;

                        break;
                }
            }
        }

        $l_cat_info = $p_cat->get_category_info($l_catg_custom_id);
        $l_multivalued = (bool)$l_cat_info['isysgui_catg_custom__list_multi_value'];

        if (!$l_multivalued) {
            $_GET[C__CMDB__GET__CATLEVEL] = null;
        } elseif ($l_multivalued && !isset($_GET[C__CMDB__GET__CATLEVEL])) {
            $_GET[C__CMDB__GET__CATLEVEL] = $p_cat->get_data_id($l_catg_custom_id);
        }

        $l_data = $p_cat->get_data(
            $_GET[C__CMDB__GET__CATLEVEL],
            $l_object_id,
            ' AND isys_catg_custom_fields_list__isysgui_catg_custom__id = ' . $p_cat->convert_sql_id($l_catg_custom_id)
        );

        if (is_countable($l_data) && count($l_data)) {
            if ((int)$l_cat_info['isysgui_catg_custom__list_multi_value'] === 0) {
                $p_cat->set_category_entries_purgable(true);
            }

            $l_used_keys = [];

            while ($l_row = $l_data->get_row()) {
                $l_key = $l_row['isys_catg_custom_fields_list__field_key'];
                $l_tmp = $l_config[$l_key];

                if (isset($l_used_keys[$l_key])) {
                    continue;
                }

                $l_rules['C__CATG__CUSTOM__' . $l_key]['p_strValue'] = $l_row['isys_catg_custom_fields_list__field_content'];

                if (isset($l_tmp['popup'])) {
                    switch ($l_tmp['popup']) {
                        case 'dialog':
                        case 'dialog_plus':
                            // @fixes ID-1193
                            $l_rules['C__CATG__CUSTOM__' . $l_key]['p_strValue'] = null;
                            $l_rules['C__CATG__CUSTOM__' . $l_key]['p_strSelectedID'] = $l_row['isys_catg_custom_fields_list__field_content'];
                            $l_rules['C__CATG__CUSTOM__' . $l_key]['p_strTable'] = 'isys_dialog_plus_custom';
                            $l_rules['C__CATG__CUSTOM__' . $l_key]['p_identifier'] = $l_tmp['identifier'];
                            $l_rules['C__CATG__CUSTOM__' . $l_key]['condition'] = "isys_dialog_plus_custom__identifier = '" . $l_tmp['identifier'] . "'";
                            if (isset($l_tmp['multiselection'])) {
                                /**
                                 * Do not set p_strValue to field content because it is representing the ID instead of the value
                                 *
                                 * @see ID-5662
                                 */
                                $l_row['isys_catg_custom_fields_list__field_content'] = implode(
                                    ',',
                                    $p_cat->get_assigned_entries($l_row['isys_catg_custom_fields_list__field_key'], $l_row['isys_catg_custom_fields_list__data__id'])
                                );
                                $l_rules['C__CATG__CUSTOM__' . $l_key]['p_strSelectedID'] = $l_row['isys_catg_custom_fields_list__field_content'];
                                $l_rules['C__CATG__CUSTOM__' . $l_key]['multiselect'] = 1;
                                $l_rules['C__CATG__CUSTOM__' . $l_key]['p_onComplete'] = "$('C__CATG__CUSTOM__" . $l_key . "').fire('C__CATG__CUSTOM__" . $l_key .
                                    ":updated');";
                            }
                            break;
                        case 'browser_object':
                            if (isset($l_tmp['multiselection'])) {
                                $l_row['isys_catg_custom_fields_list__field_content'] = isys_format_json::encode($p_cat->get_assigned_entries(
                                    $l_row['isys_catg_custom_fields_list__field_key'],
                                    $l_row['isys_catg_custom_fields_list__data__id']
                                ));
                                $l_rules['C__CATG__CUSTOM__' . $l_key]['p_strValue'] = $l_row['isys_catg_custom_fields_list__field_content'];
                                $l_rules['C__CATG__CUSTOM__' . $l_key]['p_strSelectedID'] = $l_row['isys_catg_custom_fields_list__field_content'];
                                $l_rules['C__CATG__CUSTOM__' . $l_key][isys_popup_browser_object_ng::C__MULTISELECTION] = true;
                            }
                            break;
                        case 'report_browser':
                            if (isys_glob_is_edit_mode()) {
                                break;
                            }
                            $reportId = (int)$l_row['isys_catg_custom_fields_list__field_content'];
                            $fieldConfig = unserialize($l_row['isysgui_catg_custom__config']);

                            if (isset($fieldConfig[$l_row['isys_catg_custom_fields_list__field_key']]['identifier']) &&
                                !empty($fieldConfig[$l_row['isys_catg_custom_fields_list__field_key']]['identifier']) &&
                                is_numeric($fieldConfig[$l_row['isys_catg_custom_fields_list__field_key']]['identifier'])) {
                                $reportId = $fieldConfig[$l_row['isys_catg_custom_fields_list__field_key']]['identifier'];
                                $this->get_template_component()
                                    ->assign('disableReportField', true);
                            }

                            $reportDao = isys_report_dao::instance($this->get_database_component());
                            $report = $reportDao->get_report($reportId);

                            $reportQuery = $reportDao->replacePlaceHolders($report['isys_report__query']);

                            $l_rules['C__CATG__CUSTOM__' . $l_key]['p_strSelectedID'] = $report['isys_report__id'];
                            $this->deactivate_commentary();

                            /**
                             * @var $reportModule isys_module_report_open | isys_module_report_pro
                             */
                            $reportModule = isys_module_report::get_instance();
                            $reportModule->process_show_report($reportQuery);

                            // Get report information which was assigned to smarty by process_show_report()
                            $reportListing = $this->get_template_component()->get_template_vars('listing');

                            // Indicates faulty query execution
                            if ($reportListing === null) {
                                $this->get_template_component()->assign('reportExecutionFailed', true);
                            }

                            $this->get_template_component()
                                ->assign('report_id', $report['isys_report__id'])
                                ->assign('querybuilder', $report['isys_report__querybuilder_data'])
                                ->assign('reportTitle', $report['isys_report__title'])
                                ->assign('rowcount', $reportListing['num'])
                                ->assign('reportDescription', $report['isys_report__description'])
                                ->assign('showReportExport', false);

                    }
                }

                if (isset($l_tmp['type'])) {
                    switch ($l_tmp['type']) {
                        case 'f_link':
                            $l_rules['C__CATG__CUSTOM__' . $l_key]['p_strTarget'] = '__blank';
                            break;

                        case 'f_dialog':
                            $l_rules['C__CATG__CUSTOM__' . $l_key]['p_strSelectedID'] = $l_row['isys_catg_custom_fields_list__field_content'];
                            break;
                    }
                }

                if (is_numeric($l_row['isys_catg_custom_fields_list__field_content'])) {
                    $l_rules['C__CATG__CUSTOM__' . $l_key]['p_strSelectedID'] = $l_row['isys_catg_custom_fields_list__field_content'];
                }

                if (empty($l_description)) {
                    $l_description = $l_row['isys_catg_custom_fields_list__description'];
                }

                $l_used_keys[$l_key] = true;
            }
        }

        $l_commentary = 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_CUSTOM . $l_catg_custom_id;

        $l_data = $p_cat->get_data_by_key($l_object_id, $l_catg_custom_id, $l_commentary, $_GET[C__CMDB__GET__CATLEVEL])
            ->get_row();

        $l_rules[$l_commentary]['p_strValue'] = $l_data['isys_catg_custom_fields_list__field_content'];

        $reportBrowserWithPreselect = false;
        $hasReportBrowser = false;

        // Before assigning the field configuration, we iterate through and add some fields
        if (is_array($l_config)) {
            foreach ($l_config as $l_key => $l_field) {
                // @see  ID-641  We need to process the next lines for "backwards compatibility".
                $typeDefinition = PropertyTypes::getTypeByConfiguration($l_field, $language);

                $l_config[$l_key]['displayInTable'] = true;
                $l_config[$l_key]['template'] = isys_module_custom_fields::getPath() . 'templates/fields/not_available.tpl';

                if ($typeDefinition !== null) {
                    $l_config[$l_key]['displayInTable'] = $typeDefinition[PropertyTypes::DISPLAY_IN_TABLE];
                    $l_config[$l_key]['template'] = $typeDefinition[PropertyTypes::TEMPLATE];
                }

                if ($l_field['extra'] === 'yes-no') {
                    if ($l_rules['C__CATG__CUSTOM__' . $l_key]['p_strSelectedID'] === null && isys_glob_is_edit_mode()) {
                        switch ($l_field['default']) {
                            case -1:
                                $l_rules['C__CATG__CUSTOM__' . $l_key]['p_strSelectedID'] = -1;
                                break;

                            case 0:
                                $l_rules['C__CATG__CUSTOM__' . $l_key]['p_strSelectedID'] = 'LC__UNIVERSAL__NO';
                                break;

                            case 1:
                                $l_rules['C__CATG__CUSTOM__' . $l_key]['p_strSelectedID'] = 'LC__UNIVERSAL__YES';
                                break;
                        }
                    }

                    if ($l_rules['C__CATG__CUSTOM__' . $l_key]['p_strSelectedID'] === '-1') {
                        $l_rules['C__CATG__CUSTOM__' . $l_key]['p_strValue'] = ' - ';
                    }

                    $l_rules['C__CATG__CUSTOM__' . $l_key]['p_arData'] = [
                        'LC__UNIVERSAL__YES' => $language->get('LC__UNIVERSAL__YES'),
                        'LC__UNIVERSAL__NO'  => $language->get('LC__UNIVERSAL__NO')
                    ];
                } elseif ($l_field['extra'] === 'date-datetime') {
                    if ($l_field['default'] == '1') {
                        $l_rules['C__CATG__CUSTOM__' . $l_key]['p_bTime'] = true;
                        $l_rules['C__CATG__CUSTOM__' . $l_key]['p_strStyle'] = 'width:70%';
                    }
                } elseif ($l_field['popup'] === 'checkboxes' && !empty($l_config[$l_key]['identifier'])) {
                    // @todo this needs to be refactored, use rules instead and use an own popup class for checkboxes
                    $l_identifier = $l_config[$l_key]['identifier'];
                    $result = isys_cmdb_dao_dialog_admin::instance($this->get_database_component())->get_custom_dialog_data($l_identifier);
                    $l_arData = [];
                    while ($l_row = $result->get_row()) {
                        $l_arData[] = [
                            'id' => $l_row['isys_dialog_plus_custom__id'],
                            'title' => $l_row['isys_dialog_plus_custom__title'],
                        ];
                    }
                    $l_config[$l_key]['p_arData'] = $l_arData;

                    $l_config[$l_key]['values'] = $p_cat->get_assigned_entries($l_key, $l_data['isys_catg_custom_fields_list__data__id']);
                    $l_config[$l_key]['p_strSelectedID'] = implode(',', $l_config[$l_key]['values']);
                    $l_config[$l_key]["authorizedToEdit"] = isys_auth_dialog_admin::instance()
                        ->is_allowed_to(isys_auth::CREATE, 'CUSTOM/' . $l_identifier);

                    $l_config[$l_key]['params'] = base64_encode(isys_format_json::encode([
                        "type"                  => "f_popup",
                        "p_strPopupType"        => "dialog_plus",
                        "p_strTable"            => "isys_dialog_plus_custom",
                        "p_identifier"          => $l_identifier,
                        "condition"             => "isys_dialog_plus_custom__identifier = '" . $l_identifier . "'",
                        "name"                  => "C__CATG__CUSTOM__" . $l_key . "[]",
                        "force_dialog"          => false,
                        "p_dataIdentifier"      => "isys_cmdb_dao_category_g_custom_fields::f_popup_" . $l_key,
                        "multiselect"           => true,
                        "callback_accept"       => "$('C__CATG__CUSTOM__" . $l_key . "').fire('C__CATG__CUSTOM__" . $l_key . ":updated');",
                        "inputGroupMarginClass" => "ml20",
                        "p_bInfoIconSpacer"     => 0,
                        "inputGroupClass"       => "input-size-normal",
                        "nowiki"                => 1,
                        "p_strSelectedID"       => implode(',', $l_config[$l_key]['values']),
                        "p_bPlus"               => 'on',
                        "id"                    => "C__CATG__CUSTOM__" . $l_key,
                        "p_bDbFieldNN"          => true,
                        "p_multiple"            => true,
                        "disableInputGroup"     => true,
                    ]));
                }

                $l_rules['C__CATG__CUSTOM__' . $l_key]['p_dataIdentifier'] = 'isys_cmdb_dao_category_g_custom_fields::' . $l_field['type'] . '_' . $l_key;

                if ($l_field['popup'] === 'report_browser') {
                    $hasReportBrowser = true;
                }

                if ($l_field['popup'] === 'report_browser' && !isset($l_used_keys[$l_key]) && is_numeric($l_field['identifier'])) {
                    $reportBrowserWithPreselect = $l_key;
                }

                if ($l_field['type'] === 'f_wysiwyg') {
                    // Overwrite wysiwyg toolbar only if sanitizing data is active
                    if (isys_tenantsettings::get('cmdb.registry.sanitize_input_data', 1)) {
                        $l_wysiwig_toolbar_config = isys_smarty_plugin_f_wysiwyg::get_replaced_toolbar_configuration((isys_tenantsettings::get(
                            'gui.wysiwyg-all-controls',
                            false
                        ) ? 'full' : 'basic'));

                        $l_rules['C__CATG__CUSTOM__' . $l_key]['p_overwrite_toolbarconfig'] = 1;
                        $l_rules['C__CATG__CUSTOM__' . $l_key]['p_toolbarconfig'] = isys_format_json::encode($l_wysiwig_toolbar_config);
                    }
                }

                if (!isset($l_used_keys[$l_key])) {
                    if ($l_field['multiselection'] > 0 && $l_field['type'] == 'f_popup' && $l_field['popup'] == 'dialog_plus') {
                        $l_rules["C__CATG__CUSTOM__" . $l_key]["multiselect"] = true;
                        $l_rules["C__CATG__CUSTOM__" . $l_key]["callback_accept"] = "$('C__CATG__CUSTOM__" . $l_key . "').fire('C__CATG__CUSTOM__" . $l_key . ":updated');";
                    }

                    if ($l_field['multiselection'] > 0 && $l_field['type'] == 'f_popup' && $l_field['popup'] == 'browser_object') {
                        $l_rules["C__CATG__CUSTOM__" . $l_key][isys_popup_browser_object_ng::C__MULTISELECTION] = true;
                    }
                }
            }
        }

        $reportDao = isys_report_dao::instance($this->get_database_component());

        if ($reportBrowserWithPreselect && !isys_glob_is_edit_mode()) {
            $reportId = $l_config[$reportBrowserWithPreselect]['identifier'];
            $report = $reportDao->get_report($reportId);

            $reportQuery = $reportDao->replacePlaceHolders($report['isys_report__query']);

            // Match all *.isys_obj__title in the report query and replace them
            preg_match_all('/[0-9a-zA-Z_]*\.*isys_obj__title(?= AS)/', $reportQuery, $matches);
            if (count($matches[0])) {
                foreach ($matches[0] as $titleField) {
                    [$alias, $field] = explode('.', $titleField);

                    if (empty($field)) {
                        $field = $alias;
                        $alias = '';
                    }

                    $pattern = '/' . str_replace('.', '.', $titleField). '(?= AS)/';
                    $reportQuery = preg_replace($pattern, 'CONCAT(' . $titleField . ', \' {\', ' . ($alias ? $alias . '.isys_obj__id': 'isys_obj__id') . ', \'}\')', $reportQuery);
                }
            }

            $l_rules['C__CATG__CUSTOM__' . $reportBrowserWithPreselect]['p_strSelectedID'] = $report['isys_report__id'];
            $this->deactivate_commentary();

            /**
             * @var $reportModule isys_module_report_open | isys_module_report_pro
             */
            $reportModule = isys_module_report::get_instance();

            $reportModule->process_show_report($reportQuery, null, false, false, false, true, (bool) $report['isys_report__compressed_multivalue_results'], (bool) $report['isys_report__show_html']);

            // Has Preselection in custom category config so we cannot edit it.
            isys_component_template_navbar::getInstance()
                ->set_visible(false, C__NAVBAR_BUTTON__EDIT)
                ->set_active(false, C__NAVBAR_BUTTON__EDIT);

            $this->get_template_component()
                ->assign('trClickActive', false)
                ->assign('report_id', $report['isys_report__id'])
                ->assign('querybuilder', $report['isys_report__querybuilder_data'])
                ->assign('reportTitle', $report['isys_report__title'])
                ->assign('reportDescription', $report['isys_report__description'])
                ->assign('showReportExport', false)
                ->assign('disableReportField', true);
        }

        if ($hasReportBrowser) {
            $reportsData = [];

            foreach (
                $reportDao->get_reports(
                    null,
                    (isys_module_report::getAuth()->get_allowed_reports() ?: null),
                    null,
                    true,
                    false
                ) as $report
            ) {
                $reportsData[$report['isys_report__id']] = $report['isys_report__title'];
            }

            $this->get_template_component()
                ->assign('reports', $reportsData);
        }

        $this->get_template_component()
            ->assign('fields', $l_config)
            ->assign('catg_custom_id', $l_catg_custom_id)
            ->assign('editMode', isys_glob_is_edit_mode())
            ->assign('l_rules', $l_rules)
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);

        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__NEW);
    }

    /**
     * Process list
     *
     * @param isys_cmdb_dao_category $p_cat
     * @param null                   $p_get_param_override
     * @param null                   $p_strVarName
     * @param null                   $p_strTemplateName
     * @param bool                   $p_bCheckbox
     * @param bool                   $p_bOrderLink
     * @param null                   $p_db_field_name
     * @param null                   $p_get
     *
     * @return  null|void
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function process_list(
        isys_cmdb_dao_category &$p_cat,
        $p_get_param_override = null,
        $p_strVarName = null,
        $p_strTemplateName = null,
        $p_bCheckbox = true,
        $p_bOrderLink = true,
        $p_db_field_name = null,
        $p_get = null
    ) {
        global $index_includes;

        $language = isys_application::instance()->container->get('language');

        $l_get = (is_array($p_get) ? $p_get : $_GET);
        $l_data = [];
        unset($l_get["ajax"]);
        unset($l_get["call"]);
        $p_cat->set_catg_custom_id($l_get[C__CMDB__GET__CATG_CUSTOM]);
        $l_category_info = $p_cat->get_category_info($l_get[C__CMDB__GET__CATG_CUSTOM]);
        $l_category_const = $l_category_info['isysgui_catg_custom__const'];
        $l_config = unserialize($l_category_info['isysgui_catg_custom__config']);

        $l_dao_list = isys_cmdb_dao_list_catg_custom_fields::build($p_cat->get_database_component(), $p_cat);
        $l_current_status = $l_dao_list->get_rec_status();
        $l_navbar = isys_component_template_navbar::getInstance();
        $l_result = $l_dao_list->get_result(null, $l_get[C__CMDB__GET__OBJECT], $l_current_status, $l_get[C__CMDB__GET__CATG_CUSTOM]);
        $l_amount = $l_result->num_rows();

        if ($l_amount > 0) {
            while ($l_row = $l_result->get_row()) {
                $l_data[$l_row['isys_catg_custom_fields_list__data__id']][] = $l_row;
            }
        }

        $l_dao_list->set_properties($p_cat->get_properties())
            ->set_rows($l_data)
            ->set_config($l_config);

        $l_header_fields = $l_dao_list->get_fields();
        $l_reformated_rows = $l_dao_list->reformat_rows($l_get[C__CMDB__GET__OBJECT]);

        $l_objList = isys_component_list::factory($l_reformated_rows, null, $l_dao_list, null, ($_POST[C__GET__NAVMODE] == C__NAVMODE__EXPORT_CSV ? 'csv' : 'html'));
        $l_objList->setEnableMultiselection(true);
        $l_objList->config($l_header_fields, $l_dao_list->make_row_link(), "id", false);
        if (defined('C__MODULE__SYSTEM') && defined('C__MODULE__CMDB') && isys_auth_cmdb_object_types::instance()
            ->is_allowed_to(isys_auth::EXECUTE, 'MULTILIST_CONFIG')) {
            $l_objList->setTableConfigUrl(isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID => C__MODULE__CMDB,
                C__GET__SETTINGS_PAGE => 'cat_list',
                C__CMDB__GET__OBJECT  => $l_get[C__CMDB__GET__OBJECT],
                C__CMDB__GET__CATG    => $l_get[C__CMDB__GET__CATG],
                C__CMDB__GET__CATG_CUSTOM => $l_get[C__CMDB__GET__CATG_CUSTOM]
            ]));
        }
        $l_result = $l_objList->createTempTable();

        if (defined("C__TEMPLATE__STATUS") && C__TEMPLATE__STATUS === 1) {
            $l_arData[C__RECORD_STATUS__TEMPLATE] = "Template";
        }

        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EXPORT_CSV) {
            $l_objList->createTempTable();
        }

        $l_dao_list->get_rec_array();
        $l_arData = $l_dao_list->get_rec_array();

        $this->get_template_component()
            ->assign("conn_link", isys_helper_link::create_url($l_get))
            ->assign("dao_connector", $p_cat)
            ->assign("list_display", true)
            ->assign("objectTableList", ($l_result) ? $l_objList->getTempTableHtml() : '<div class="p10">' . $language->get('LC__CMDB__FILTER__NOTHING_FOUND_STD') . '</div>')
            ->smarty_tom_add_rule("tom.content.top.filter.p_bDisabled=0")
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_strSelectedID=" . $l_current_status)
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_arData=" . serialize($l_arData));

        $l_supervisor_right = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::SUPERVISOR, $_GET[C__CMDB__GET__OBJECT], $l_category_const);
        $l_delete_right = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::DELETE, $_GET[C__CMDB__GET__OBJECT], $l_category_const);
        $l_archive_right = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::ARCHIVE, $_GET[C__CMDB__GET__OBJECT], $l_category_const);
        $l_edit_right = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::EDIT, $_GET[C__CMDB__GET__OBJECT], $l_category_const);

        $l_quickpurge = (isys_tenantsettings::get('cmdb.quickpurge') == '1') ? true : false;

        $l_navbar->hide_all_buttons([C__NAVBAR_BUTTON__NEW])
            ->set_active(($l_amount > 0), C__NAVBAR_BUTTON__PRINT)
            ->set_active(($l_edit_right && $l_amount > 0), C__NAVBAR_BUTTON__EDIT)
            ->set_active(($l_supervisor_right && $l_quickpurge && $l_amount > 0), C__NAVBAR_BUTTON__QUICK_PURGE)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(($l_amount > 0), C__NAVBAR_BUTTON__PRINT)
            ->set_visible(($l_supervisor_right && $l_quickpurge), C__NAVBAR_BUTTON__QUICK_PURGE)
            ->set_visible(true, C__NAVBAR_BUTTON__EXPORT_AS_CSV)
            ->set_active(true, C__NAVBAR_BUTTON__EXPORT_AS_CSV);

        switch ($l_current_status) {
            case C__RECORD_STATUS__ARCHIVED:
                $l_navbar->set_active(false, C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_active($l_delete_right && $l_amount > 0, C__NAVBAR_BUTTON__DELETE)
                    ->set_active(false, C__NAVBAR_BUTTON__PURGE)
                    ->set_active(($l_archive_right || $l_delete_right) && $l_amount > 0, C__NAVBAR_BUTTON__RECYCLE)
                    ->set_visible(false, C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_visible($l_delete_right, C__NAVBAR_BUTTON__DELETE)
                    ->set_visible($l_archive_right || $l_delete_right, C__NAVBAR_BUTTON__RECYCLE)
                    ->set_visible(false, C__NAVBAR_BUTTON__PURGE);
                break;
            case C__RECORD_STATUS__DELETED:
                $l_navbar->set_active(false, C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_active(false, C__NAVBAR_BUTTON__DELETE)
                    ->set_active(false, C__NAVBAR_BUTTON__QUICK_PURGE)
                    ->set_active($l_supervisor_right && $l_amount > 0, C__NAVBAR_BUTTON__PURGE)
                    ->set_active($l_edit_right && $l_amount > 0, C__NAVBAR_BUTTON__RECYCLE)
                    ->set_visible(false, C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_visible(false, C__NAVBAR_BUTTON__DELETE)
                    ->set_visible(false, C__NAVBAR_BUTTON__QUICK_PURGE)
                    ->set_visible($l_delete_right, C__NAVBAR_BUTTON__RECYCLE)
                    ->set_visible($l_supervisor_right, C__NAVBAR_BUTTON__PURGE);
                break;
            case C__RECORD_STATUS__NORMAL:
            default:
                $l_navbar->set_active(($l_archive_right || $l_delete_right || $l_supervisor_right) && $l_amount > 0, C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_active(false, C__NAVBAR_BUTTON__DELETE)
                    ->set_active(false, C__NAVBAR_BUTTON__PURGE)
                    ->set_visible(true, C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_visible(false, C__NAVBAR_BUTTON__DELETE)
                    ->set_visible(false, C__NAVBAR_BUTTON__PURGE);
                break;
        }

        if (count($l_data) == 0) {
            $l_navbar->set_active(false, C__NAVBAR_BUTTON__EDIT)
                ->set_active(false, C__NAVBAR_BUTTON__PURGE);
        }

        // Render template and assign it to its category constant
        if (!empty($l_result)) {
            $this->get_template_component()->assign($p_strVarName, $l_objList
                ->setScoped(true)
                ->setRouteParams($l_get)
                ->setEnableMultiselection(true)->render());
        }

        $index_includes['contentbottomcontent'] = "content/bottom/content/object_table_list.tpl";
    }

    /**
     * UI constructor. Which is needed for the overview otherwise the overview category won´t work
     * with custom categories.
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        $this->set_template("catg__custom_fields.tpl");
        parent::__construct($p_template);
    }
}
