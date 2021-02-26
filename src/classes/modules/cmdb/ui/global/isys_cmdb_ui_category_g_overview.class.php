<?php

use idoit\Component\Helper\Purify;
use idoit\Module\CustomFields\PropertyTypes;

/**
 * i-doit
 *
 * CMDB UI: Overview category with content of configured categories.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_overview extends isys_cmdb_ui_category_global
{
    /**
     * In this specific case this variable will not work - because we call a lot of other UI classes which have this set to true. This is just for completeness ;)
     *
     * @var   boolean
     */
    protected $m_csv_export = false;

    /**
     * Process the category.
     *
     * @param  isys_cmdb_dao_category_g_overview &$p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $g_dirs, $index_includes;

        $l_gets = isys_module_request::get_instance()->get_gets();
        $l_gets = Purify::castIntValues($l_gets);
        $objectId = $l_gets[C__CMDB__GET__OBJECT];

        $l_auth_create = isys_auth_cmdb::instance()->has_rights_in_obj_and_category(isys_auth::CREATE, $objectId, 'C__CATG__OVERVIEW');
        $l_auth_edit = isys_auth_cmdb::instance()->has_rights_in_obj_and_category(isys_auth::EDIT, $objectId, 'C__CATG__OVERVIEW');
        $language = isys_application::instance()->container->get('language');
        $database = isys_application::instance()->container->get('database');

        // Get visible categories.
        $l_categories = $p_cat->get_categories_as_array($l_gets[C__CMDB__GET__OBJECTTYPE], $objectId);
        $l_specific_category = $p_cat->get_category_specific($l_gets[C__CMDB__GET__OBJECTTYPE], $objectId);
        $l_custom_categories = $p_cat->get_custom_categories_as_array($l_gets[C__CMDB__GET__OBJECTTYPE], $objectId);

        if (is_array($l_custom_categories) && count($l_custom_categories)) {
            foreach ($l_custom_categories as $l_value) {
                $l_categories["custom_" . $l_value['id']] = $l_value;
            }
        }

        if (is_countable($l_categories) && count($l_categories)) {
            isys_glob_sort_array_by_column($l_categories, 'sort');
        }

        if (is_array($l_specific_category) && count($l_specific_category)) {
            foreach ($l_specific_category as $l_value) {
                $l_categories["specific"] = $l_value;
            }
        }

        if (is_array($l_categories)) {
            $WysiwygToolbarConfig = null;
            // Overwrite wysiwyg toolbar only if sanitizing data is active
            if ((isys_tenantsettings::get('cmdb.registry.sanitize_input_data', 1))) {
                $WysiwygToolbarConfig = isys_smarty_plugin_f_wysiwyg::get_replaced_toolbar_configuration((isys_tenantsettings::get(
                    'gui.wysiwyg-all-controls',
                    false
                ) ? 'full' : 'basic'));
            }

            foreach ($l_categories as $l_key => $l_cat) {
                if (isset($l_cat['dao']) && is_object($l_cat['dao'])) {
                    /** @var isys_cmdb_ui_category $l_ui */
                    $l_ui = $l_cat['dao']->get_ui();
                    isys_component_signalcollection::get_instance()
                        ->emit("mod.cmdb.beforeProcess", $l_cat['dao'], $index_includes["contentbottomcontent"]);

                    $categoryType = $l_cat['dao']->get_category_type();
                    $categoryId = $l_cat['dao']->get_category_id();

                    $l_categories[$l_key]['template'] = $l_ui->get_template();
                    $l_categories[$l_key]['template_before'] = $l_ui->get_additional_template_before();
                    $l_categories[$l_key]['template_after'] = $l_ui->get_additional_template_after();

                    if (method_exists($l_cat['dao'], 'get_config') && method_exists($l_cat['dao'], 'get_catg_custom_id')) {
                        $l_categories[$l_key]['fields'] = $l_cat['dao']->get_config($l_cat['dao']->get_catg_custom_id());

                        foreach ($l_categories[$l_key]['fields'] as $key => $value) {
                            $type = PropertyTypes::getTypeByConfiguration($value, $language);

                            $l_categories[$l_key]['fields'][$key][PropertyTypes::DISPLAY_IN_TABLE] = true;
                            $l_categories[$l_key]['fields'][$key][PropertyTypes::TEMPLATE] = isys_module_custom_fields::getPath() . 'templates/fields/not_available.tpl';

                            if ($type !== null) {
                                $l_categories[$l_key]['fields'][$key][PropertyTypes::DISPLAY_IN_TABLE] = $type[PropertyTypes::DISPLAY_IN_TABLE];
                                $l_categories[$l_key]['fields'][$key][PropertyTypes::TEMPLATE] = $type[PropertyTypes::TEMPLATE];
                            }

                            // @todo this needs to be refactored it is better to use rules in the ui classes
                            if ($value['popup'] === 'checkboxes' && !empty($value['identifier'])) {
                                $identifier = $value['identifier'];

                                $dialogData = [];
                                $result = isys_cmdb_dao_dialog_admin::instance($database)->get_custom_dialog_data($identifier);

                                while ($row = $result->get_row()) {
                                    $dialogData[] = [
                                        'id' => $row['isys_dialog_plus_custom__id'],
                                        'title' => $row['isys_dialog_plus_custom__title'],
                                    ];
                                }
                                $l_categories[$l_key]['fields'][$key]['p_arData'] = $dialogData;

                                $sql = 'SELECT isys_catg_custom_fields_list__field_content 
                                    FROM isys_catg_custom_fields_list
                                    WHERE isys_catg_custom_fields_list__field_key = ' . $p_cat->convert_sql_text($key) . ' 
                                    AND isys_catg_custom_fields_list__isys_obj__id = ' . $p_cat->convert_sql_int($objectId);

                                $fieldContents = [];
                                $result = $p_cat->retrieve($sql);

                                while ($row = $result->get_row()) {
                                    $fieldContents[] = $row['isys_catg_custom_fields_list__field_content'];
                                }

                                $l_categories[$l_key]['fields'][$key]['values'] = $fieldContents;
                                $l_categories[$l_key]['fields'][$key]['p_strSelectedID'] = implode(',', $fieldContents);
                                $l_categories[$l_key]['fields'][$key]["authorizedToEdit"] = isys_auth_dialog_admin::instance()
                                    ->is_allowed_to(isys_auth::CREATE, 'CUSTOM/' . $identifier);

                                $l_categories[$l_key]['fields'][$key]['params'] = base64_encode(isys_format_json::encode([
                                    'type'                  => 'f_popup',
                                    'p_strPopupType'        => 'dialog_plus',
                                    'p_strTable'            => 'isys_dialog_plus_custom',
                                    'p_identifier'          => $identifier,
                                    'condition'             => "isys_dialog_plus_custom__identifier = '" . $identifier . "'",
                                    'name'                  => 'C__CATG__CUSTOM__' . $key . '[]',
                                    'force_dialog'          => false,
                                    'p_dataIdentifier'      => 'isys_cmdb_dao_category_g_custom_fields::f_popup_' . $key,
                                    'multiselect'           => true,
                                    'callback_accept'       => "$('C__CATG__CUSTOM__" . $key . "').fire('C__CATG__CUSTOM__" . $key . ":updated');",
                                    'inputGroupMarginClass' => 'ml20',
                                    'p_bInfoIconSpacer'     => 0,
                                    'inputGroupClass'       => 'input-size-normal',
                                    'nowiki'                => 1,
                                    'p_strSelectedID'       => implode(',', $fieldContents),
                                    'p_bPlus'               => 'on',
                                    'id'                    => 'C__CATG__CUSTOM__' . $key,
                                    'p_bDbFieldNN'          => true,
                                    'p_multiple'            => true,
                                    'disableInputGroup'     => true,
                                ]));
                            }
                        }
                    }

                    if ($_POST[C__GET__NAVMODE] != C__NAVBAR_BUTTON__NEW && $l_cat['multivalued'] && $l_cat['const'] != 'C__CATG__IP') {
                        // Check whether current category is custom field
                        if ($l_cat['dao'] instanceof isys_cmdb_dao_category_g_custom_fields) {
                            /** @var isys_cmdb_ui_category_g_custom_fields $l_ui*/
                            $l_ui->process_list(
                                $l_cat['dao'],
                                null,
                                $l_categories[$l_key]['const'],
                                null,
                                false,
                                true,
                                null,
                                array_merge($_GET, [
                                    C__CMDB__GET__CATG_CUSTOM => $l_cat['id'],
                                    C__CMDB__GET__CATG => C__CATG__CUSTOM_FIELDS,
                                    C__CMDB__GET__VIEWMODE => C__CMDB__VIEW__CATEGORY,
                                    C__CMDB__GET__TREEMODE => C__CMDB__VIEW__TREE_OBJECT
                                    ])
                            );
                        } else {
                            $l_ui->process_list($l_cat['dao'], null, $l_categories[$l_key]['const'], null, false);
                        }
                    } else {
                        if ($l_cat['const'] == 'C__CATG__IP' && method_exists($l_ui, 'show_primary_ip')) {
                            $l_ui->show_primary_ip();
                        }

                        // @see ID-5647 Set dao in ui
                        $l_ui->m_catdao = $l_cat['dao'];

                        $l_rules = $l_ui->process($l_cat['dao']);
                        if ($WysiwygToolbarConfig !== null) {
                            $l_rules['C__CMDB__CAT__COMMENTARY_' . $categoryType . $categoryId]['p_overwrite_toolbarconfig'] = 1;
                            $l_rules['C__CMDB__CAT__COMMENTARY_' . $categoryType . $categoryId]['p_toolbarconfig'] = isys_format_json::encode($WysiwygToolbarConfig);
                        }
                        $l_ui->process_ui_validation_rules($l_cat['dao'], (is_array($l_rules) && count($l_rules) ? $l_rules : []));
                    }
                }
            }

            // @See ID-4900 Have to hide all buttons because the ui classes of the categories can activate buttons which should not be visible in the overview
            isys_component_template_navbar::getInstance()->hide_all_buttons();

            switch ($_POST[C__GET__NAVMODE]) {
                case C__NAVMODE__NEW:
                    isys_component_template_navbar::getInstance()
                        ->set_visible(true, C__NAVBAR_BUTTON__SAVE)
                        ->set_active(true, C__NAVBAR_BUTTON__SAVE)
                        ->set_visible(true, C__NAVBAR_BUTTON__CANCEL)
                        ->set_active(true, C__NAVBAR_BUTTON__CANCEL)
                        ->set_visible(false, C__NAVBAR_BUTTON__PRINT)
                        ->set_visible(false, C__NAVBAR_BUTTON__PURGE)
                        ->set_visible(false, C__NAVBAR_BUTTON__EDIT);
                    break;
                case C__NAVMODE__EDIT:
                    isys_component_template_navbar::getInstance()
                        ->set_visible(true, C__NAVBAR_BUTTON__CANCEL)
                        ->set_active(true, C__NAVBAR_BUTTON__CANCEL)
                        ->set_visible(false, C__NAVBAR_BUTTON__PRINT)
                        ->set_visible(false, C__NAVBAR_BUTTON__PURGE)
                        ->set_visible(false, C__NAVBAR_BUTTON__EDIT)
                        ->set_visible(true, C__NAVBAR_BUTTON__SAVE)
                        ->set_active(true, C__NAVBAR_BUTTON__SAVE);
                    break;
                default:
                    isys_component_template_navbar::getInstance()
                        ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
                        ->set_active($l_auth_edit, C__NAVBAR_BUTTON__EDIT)
                        ->set_visible(true, C__NAVBAR_BUTTON__PRINT)
                        ->set_active(true, C__NAVBAR_BUTTON__PRINT)
                        ->set_visible(false, C__NAVBAR_BUTTON__PURGE);
                    break;
            }
        }

        $objectTypeConstant = $p_cat->get_type_by_object_id($objectId)->get_row_value('isys_obj_type__const');

        $orderMap = [];
        $orderResult = $p_cat->getOrderedOverviewCategories($objectTypeConstant);

        while ($row = $orderResult->get_row()) {
            $orderMap[$row['constant']] = $row['sort'];
        }

        if (is_array($l_specific_category) && count($l_specific_category)) {
            $index = isys_tenantsettings::get('cmdb.objtype.' . $objectTypeConstant . '.specific-cat-position');

            if (!$index) {
                unset($l_categories['specific']);
            } else {
                $orderMap[current($l_specific_category)['const']] = $index;
            }
        }

        foreach ($l_categories as &$cat) {
            $cat['sort'] = $orderMap[$cat['const']];
        }

        usort($l_categories, function ($a, $b) {
            return $a['sort'] - $b['sort'];
        });

        $configUrl = isys_helper_link::create_url([
            C__CMDB__GET__OBJECTGROUP => $_GET[C__CMDB__GET__OBJECTGROUP] ?: 2,
            C__CMDB__GET__VIEWMODE    => C__CMDB__VIEW__CONFIG_OBJECTTYPE,
            C__CMDB__GET__TREEMODE    => C__CMDB__VIEW__TREE_OBJECTTYPE,
            C__CMDB__GET__OBJECTTYPE  => $_GET[C__CMDB__GET__OBJECTTYPE]
        ]);

        // Assign stuff to the template.
        $this->get_template_component()
            ->assign("g_navmode", isys_glob_get_param(C__GET__NAVMODE))
            ->assign("g_categories", $l_categories)
            ->assign('img_dir', $g_dirs["images"])
            ->assign('auth', isys_auth_cmdb::instance())
            ->assign('auth_view_id', isys_auth::VIEW)
            ->assign('auth_edit_id', isys_auth::EDIT)
            ->assign('auth_create_id', isys_auth::CREATE)
            ->assign('obj_id', $l_gets[C__CMDB__GET__OBJECT])
            ->assign('configUrl', $configUrl)
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=0")
            ->include_template('contentbottomcontent', $this->get_template());

        $this->deactivate_commentary();

        isys_component_template_navbar::getInstance()
            ->set_active(($l_auth_edit || $l_auth_create) && $_POST[C__GET__NAVMODE] != C__NAVMODE__EDIT && $_POST[C__GET__NAVMODE] != C__NAVMODE__NEW, C__NAVBAR_BUTTON__EDIT)
            ->set_active($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT || $_POST[C__GET__NAVMODE] == C__NAVMODE__NEW, C__NAVBAR_BUTTON__SAVE)
            ->set_visible(false, C__NAVBAR_BUTTON__EXPORT_AS_CSV)
            ->set_active(false, C__NAVBAR_BUTTON__EXPORT_AS_CSV);

        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT) {
            isys_component_template_navbar::getInstance()
                ->set_active(true, C__NAVBAR_BUTTON__SAVE);
        }
    }

    /**
     * This is no multivalue category, so we use the process method here.
     *
     * @todo    Is this method even necessary?
     *
     * @param isys_cmdb_dao_category $p_cat
     * @param null                   $p_get_param_override
     * @param null                   $p_strVarName
     * @param null                   $p_strTemplateName
     * @param bool                   $p_bCheckbox
     * @param bool                   $p_bOrderLink
     * @param null                   $p_db_field_name
     *
     * @return void
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
        $this->process($p_cat);
    }
}
