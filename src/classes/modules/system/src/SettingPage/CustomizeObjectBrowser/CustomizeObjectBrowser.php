<?php

namespace idoit\Module\System\SettingPage\CustomizeObjectBrowser;

use idoit\Component\Browser\Retriever;
use idoit\Module\System\SettingPage\SettingPage;
use isys_auth as Auth;
use isys_cmdb_dao;
use isys_cmdb_dao_category_g_custom_fields;
use isys_component_list;
use isys_component_template_navbar;
use isys_core;
use isys_exception_general;
use isys_format_json as JSON;
use isys_helper_link as HelperLink;
use isys_helper_textformat as HelperTextformat;
use isys_module_cmdb as ModuleCmdb;
use isys_module_system as ModuleSystem;
use isys_notify as Notify;
use isys_popup_browser_object_ng as BrowserObjectNg;
use isys_tenantsettings as TenantSettings;

/**
 * Class CustomizeObjectBrowser
 *
 * @package idoit\Module\System\SettingPage
 */
class CustomizeObjectBrowser extends SettingPage
{
    /**
     * @param  integer $navMode
     *
     * @return void
     * @throws \isys_exception_database
     * @throws isys_exception_general
     */
    public function renderPage($navMode)
    {
        if (isset($_GET[C__GET__ID]) && !empty($_GET[C__GET__ID]) && $navMode != C__NAVMODE__EDIT && $navMode != C__NAVMODE__SAVE) {
            $navMode = C__NAVMODE__EDIT;
        }

        switch ($navMode) {
            default:
                $this->renderList();
                break;

            /**
             * @noinspection PhpMissingBreakStatementInspection
             */
            case C__NAVMODE__SAVE:
                list($categoryConstant, $propertyKey, $objectBrowserKey) = explode('::', $_GET[C__GET__ID]);
                $properties = (array)JSON::decode($_POST['C__CUSTOMIZE_OBJECT_BROWSER__PROPERTIES__HIDDEN_IDS']);
                $displayAttributeCategories = (bool)$_POST['C__CUSTOMIZE_OBJECT_BROWSER__SHOW_PROPERTY_CATEGORIES'];
                $objectTypes = (array)$_POST['C__CUSTOMIZE_OBJECT_BROWSER__OBJECT_TYPES'];
                $defaultObjectType = $_POST['C__CUSTOMIZE_OBJECT_BROWSER__DEFAULT_OBJECT_TYPE'];
                $defaultSortingField = $_POST['default_sorting'];
                $defaultSortingDirection = $_POST['C__CUSTOMIZE_OBJECT_BROWSER__DEFAULT_ATTRIBUTE_SORTING'];

                $this->saveData($objectBrowserKey, $properties, $objectTypes, $displayAttributeCategories, $defaultObjectType, $defaultSortingField, $defaultSortingDirection);

            // no break
            case C__NAVMODE__EDIT:
                if (isset($_POST[C__GET__ID][0])) {
                    $redirectUrl = HelperLink::create_url([
                        C__GET__MODULE_ID => defined_or_default('C__MODULE__SYSTEM'),
                        'what'            => 'customizeObjectBrowser',
                        C__GET__TREE_NODE => $_GET[C__GET__TREE_NODE],
                        C__GET__ID        => $_POST[C__GET__ID][0]
                    ], true);

                    isys_core::send_header('Location', $redirectUrl);

                    // @see ID-5822 Apparently "header('Location: ...');" does not stop the execution.
                    die;
                }

                list($categoryConstant, $propertyKey, $objectBrowserKey) = explode('::', $_GET[C__GET__ID]);

                $this->renderForm($categoryConstant, $propertyKey, $objectBrowserKey);
                break;

            case C__NAVMODE__DELETE:
            case C__NAVMODE__PURGE:
            case C__NAVMODE__QUICK_PURGE:
                $ids = $_POST['id'];

                if (is_array($ids)) {
                    $this->resetConfiguration($ids);
                }

                $this->renderList();
                break;
        }
    }

    /**
     * This method will list up all available object browsers.
     */
    private function renderList()
    {
        ModuleCmdb::getAuth()->check(Auth::VIEW, 'object_browser_configuration');

        $allowedToEdit = ModuleCmdb::getAuth()->is_allowed_to(Auth::EDIT, 'object_browser_configuration');
        $allowedToDelete = ModuleCmdb::getAuth()->is_allowed_to(Auth::DELETE, 'object_browser_configuration');
        $dao = isys_cmdb_dao::instance($this->db);
        $categoriesList = $dao->get_all_categories();
        $objectBrowsers = [];

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons()
            ->set_js_onclick("$('navMode').setValue(" . C__NAVMODE__EDIT . "); $('isys_form').submit();", C__NAVBAR_BUTTON__EDIT)
            ->set_active($allowedToEdit, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
            ->set_active($allowedToDelete, C__NAVBAR_BUTTON__PURGE)
            ->set_visible(true, C__NAVBAR_BUTTON__PURGE);

        // @see  ID-5728  Because of some special cases, we need a blacklist of object browsers to skip.
        $blacklist = [
            'C__CATS__FILE_OBJECTS::assigned_objects::C__CATS__CMDB__FILE__OBJECTS',
            'C__CATG__RELATION::object1::C__CATG__RELATION_MASTER',
            'C__CATG__IT_SERVICE_RELATIONS::object1::C__CATG__RELATION_MASTER',
            'C__CATG__IT_SERVICE_RELATIONS::object2::C__CATG__RELATION_SLAVE',
            'C__CATG__NAGIOS_SERVICE_DEP::host::C__CATG__NAGIOS_SERVICE_DEP__HOST',
            'C__CATG__NAGIOS_SERVICE_DEP::host_dependency::C__CATG__NAGIOS_SERVICE_DEP__HOST_DEPENDENCY'
        ];

        foreach ($categoriesList as $categoryType => $categories) {
            foreach ($categories as $category) {
                $categoryDaoClass = $category['class_name'];

                if (!class_exists($categoryDaoClass) || !is_a($categoryDaoClass, 'isys_cmdb_dao_category', true)) {
                    continue;
                }

                /**
                 * @var $categoryDao \isys_cmdb_dao_category
                 */
                $categoryDao = new $categoryDaoClass($this->db);
                $customCategoryKey = '';

                if (!method_exists($categoryDao, 'get_properties')) {
                    continue;
                }

                // Custom categories need this extra step.
                if ($categoryType == C__CMDB__CATEGORY__TYPE_CUSTOM && $categoryDao instanceof isys_cmdb_dao_category_g_custom_fields) {
                    $categoryDao->set_catg_custom_id($category['id']);
                    $customCategoryKey = 'C__CATG__CUSTOM__';
                }

                $categoryTitle = $this->lang->get($category['title']);

                if ($category['parent']) {
                    if ($categoryType == C__CMDB__CATEGORY__TYPE_GLOBAL) {
                        $parentCategory = $dao->get_all_catg($category['parent'])->get_row_value('isysgui_catg__title');

                        if (!empty($parentCategory)) {
                            $categoryTitle = $this->lang->get($parentCategory) . ' &raquo; ' . $categoryTitle;
                        }
                    } elseif ($categoryType == C__CMDB__CATEGORY__TYPE_SPECIFIC) {
                        $parentCategory = $dao->get_all_cats($category['parent'])->get_row_value('isysgui_cats__title');

                        if (!empty($parentCategory)) {
                            $categoryTitle = $this->lang->get($parentCategory) . ' &raquo; ' . $categoryTitle;
                        }
                    }
                }

                $categoryProperties = $categoryDao->get_properties();
                $compatibleBrowserTypes = [
                    'browser_object_ng',
                    'browser_cable_connection_ng',
                    'browser_object_relation',
                    // @todo  Add other browser types
                ];

                foreach ($categoryProperties as $key => $property) {
                    $isTypeObjectBrowser = ($property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] === C__PROPERTY__INFO__TYPE__OBJECT_BROWSER);
                    $isUiTypeObjectBrowserNg = ($property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] === C__PROPERTY__UI__TYPE__POPUP &&
                        \in_array($property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'], $compatibleBrowserTypes, true));

                    // @see  ID-7164  Very specific logic because this object browser has the info type "n2m".
                    if ($categoryDaoClass === 'isys_cmdb_dao_category_s_layer2_net' && $key === 'layer3_assignments') {
                        $isTypeObjectBrowser = true;
                    }

                    if (!$isTypeObjectBrowser || !$isUiTypeObjectBrowserNg) {
                        continue;
                    }

                    $objectBrowserKey = $customCategoryKey . $property[C__PROPERTY__UI][C__PROPERTY__UI__ID];

                    $browserId = $category['const'] . '::' . $key . '::' . $objectBrowserKey;

                    // Skip certain object browsers.
                    if (\in_array($browserId, $blacklist, true)) {
                        continue;
                    }

                    $attributes = TenantSettings::get('cmdb.object-browser.' . $objectBrowserKey . '.attributes');

                    if (!empty($attributes) && \is_array($attributes)) {
                        $customProperties = $this->preparePropertyList($attributes);
                    } else {
                        $customProperties = $this->lang->get('LC__UNIVERSAL__DEFAULT');
                    }

                    $objectBrowsers[] = [
                        'browserId'          => $category['const'] . '::' . $key . '::' . $objectBrowserKey,
                        'categoryAndProerty' => $categoryTitle . ' / ' . $this->lang->get($property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]),
                        'customProperties'   => $customProperties
                    ];
                }
            }
        }

        usort($objectBrowsers, function ($a, $b) {
            return strcmp($a['categoryAndProerty'], $b['categoryAndProerty']);
        });

        $objectBrowserList = new isys_component_list($objectBrowsers);

        $rowHeaders = [
            'categoryAndProerty' => $this->lang->get_in_text('LC_UNIVERSAL__CATEGORY / LC_UNIVERSAL__PROPERTY'),
            'customProperties'   => 'LC__CMDB__TREE__SYSTEM__CMDB_EXPLORER__CUSTOM_PROPERTIES'
        ];

        $rowLink = HelperLink::create_url([
            C__GET__MODULE_ID => defined_or_default('C__MODULE__SYSTEM'),
            'what'            => 'customizeObjectBrowser',
            C__GET__TREE_NODE => $_GET[C__GET__TREE_NODE],
            C__GET__ID        => '[{browserId}]'
        ]);

        $objectBrowserList->config($rowHeaders, $rowLink, '[{browserId}]');

        $objectBrowserList->createTempTable();

        $this->tpl
            ->assign('content_title', $this->lang->get('LC__CMDB__TREE__SYSTEM__CMDB_EXPLORER'))
            ->assign('objectBrowserList', $objectBrowserList->getTempTableHtml())
            ->smarty_tom_add_rule('tom.content.navbar.cRecStatus.p_bInvisible=1')
            ->include_template('contentbottomcontent', ModuleSystem::getPath() . 'templates/SettingPage/CustomizeObjectBrowser/list.tpl');
    }

    /**
     * Method for saving a configuration.
     *
     * @param  string  $objectBrowserKey
     * @param  array   $properties
     * @param  array   $objectTypes
     * @param  boolean $displayAttributeCategories
     * @param  string  $defaultObjectType
     * @param  string  $defaultSortingField
     * @param  string  $defaultSortingDirection
     *
     * @throws \isys_exception_database
     */
    private function saveData($objectBrowserKey, array $properties, array $objectTypes, $displayAttributeCategories, $defaultObjectType = null, $defaultSortingField = null, $defaultSortingDirection = null)
    {
        ModuleCmdb::getAuth()->check(Auth::EDIT, 'object_browser_configuration');

        $dao = isys_cmdb_dao::instance($this->db);

        // We need this to provide the correct sorting.
        $propertyOrder = array_flip($properties);
        $objectBrowserProperties = [];

        // Convert the property IDs to a different structure: "<category constant>::<property key>".
        $sql = 'SELECT * FROM isys_property_2_cat WHERE isys_property_2_cat__id ' . $dao->prepare_in_condition($properties) . ';';
        $result = $dao->retrieve($sql);

        while ($row = $result->get_row()) {
            $objectBrowserProperties[$propertyOrder[$row['isys_property_2_cat__id']]] = $row['isys_property_2_cat__cat_const'] . '::' . $row['isys_property_2_cat__prop_key'];
        }

        ksort($objectBrowserProperties);

        TenantSettings::set('cmdb.object-browser.' . $objectBrowserKey . '.attributes', array_values($objectBrowserProperties));
        TenantSettings::set('cmdb.object-browser.' . $objectBrowserKey . '.displayAttributeCategories', (bool) $displayAttributeCategories);
        TenantSettings::set('cmdb.object-browser.' . $objectBrowserKey . '.defaultSortingField', $defaultSortingField);
        TenantSettings::set('cmdb.object-browser.' . $objectBrowserKey . '.defaultSortingFieldIndex', $propertyOrder[$defaultSortingField]);
        TenantSettings::set('cmdb.object-browser.' . $objectBrowserKey . '.defaultSortingDirection', ($defaultSortingDirection === 'asc' ? 'asc' : 'desc'));

        if ($defaultObjectType !== null) {
            TenantSettings::set('cmdb.object-browser.' . $objectBrowserKey . '.defaultObjectType', $defaultObjectType);
        } else {
            TenantSettings::remove('cmdb.object-browser.' . $objectBrowserKey . '.defaultObjectType');
        }

        if (count($objectTypes)) {
            TenantSettings::set('cmdb.object-browser.' . $objectBrowserKey . '.objectTypes', array_values($objectTypes));
        } else {
            TenantSettings::remove('cmdb.object-browser.' . $objectBrowserKey . '.objectTypes');
        }

        Notify::success($this->lang->get('LC__INFOBOX__DATA_WAS_SAVED'));
    }

    /**
     * This method will display the form for the selected object browser.
     *
     * @param  string $categoryConstant
     * @param  string $propertyKey
     * @param  string $objectBrowserKey
     *
     * @throws \isys_exception_database
     * @throws isys_exception_general
     */
    private function renderForm($categoryConstant, $propertyKey, $objectBrowserKey)
    {
        ModuleCmdb::getAuth()->check(Auth::VIEW, 'object_browser_configuration');

        $allowedToEdit = ModuleCmdb::getAuth()->is_allowed_to(Auth::EDIT, 'object_browser_configuration');
        $dao = isys_cmdb_dao::instance($this->db);
        $category = $dao->get_cat_by_const($categoryConstant);

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons()
            ->set_js_onclick("$('navMode').setValue(" . C__NAVMODE__SAVE . "); $('isys_form').submit();", C__NAVBAR_BUTTON__SAVE)
            ->set_active($allowedToEdit, C__NAVBAR_BUTTON__SAVE);

        $categoryDaoClass = $category['class_name'];

        if (!class_exists($categoryDaoClass) || !is_a($categoryDaoClass, 'isys_cmdb_dao_category', true)) {
            throw new isys_exception_general('Category DAO "' . $categoryDaoClass . '" does not exist!');
        }

        /**
         * @var $categoryDao \isys_cmdb_dao_category
         */
        $categoryDao = new $categoryDaoClass($this->db);

        if (!method_exists($categoryDao, 'get_property_by_key')) {
            throw new isys_exception_general('Category DAO "' . $categoryDaoClass . '" does not implement "get_property_by_key" method!');
        }

        // Custom categories need this extra step.
        if ($category['type'] == C__CMDB__CATEGORY__TYPE_CUSTOM && $categoryDao instanceof isys_cmdb_dao_category_g_custom_fields) {
            $categoryDao->set_catg_custom_id($category['id']);
        }

        $categoryParentTitle = '';

        if ($category['parent']) {
            if ($category['type'] == C__CMDB__CATEGORY__TYPE_GLOBAL) {
                $parentCategory = $dao->get_all_catg($category['parent'])->get_row_value('isysgui_catg__title');

                if (!empty($parentCategory)) {
                    $categoryParentTitle = $this->lang->get($parentCategory) . ' &raquo; ';
                }
            } elseif ($category['type'] == C__CMDB__CATEGORY__TYPE_SPECIFIC) {
                $parentCategory = $dao->get_all_cats($category['parent'])->get_row_value('isysgui_cats__title');

                if (!empty($parentCategory)) {
                    $categoryParentTitle = $this->lang->get($parentCategory) . ' &raquo; ';
                }
            }
        }

        $property = $categoryDao->get_property_by_key($propertyKey);
        $selectedAttributes = TenantSettings::get('cmdb.object-browser.' . $objectBrowserKey . '.attributes', (new Retriever())->getAttributes());

        $objectTypes = [];
        $categoryFilterList = [];
        $categoryFilterObjectTypeList = [];

        // If category filters are set, we display these and prevent the user from adding own object types.
        if (isset($property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][BrowserObjectNg::C__CAT_FILTER])) {
            $categoryFilters = explode(';', $property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS][BrowserObjectNg::C__CAT_FILTER]);
            $dao = isys_cmdb_dao::instance($this->db);

            foreach ($categoryFilters as $categoryFilter) {
                $objectTypeResult = false;
                $filteredCategory = $dao->get_cat_by_const($categoryFilter);

                $categoryFilterList[] = '"<strong>' . $this->lang->get($filteredCategory['title']) . '</strong>"';

                if ($filteredCategory['type'] == C__CMDB__CATEGORY__TYPE_GLOBAL) {
                    $objectTypeResult = $dao->get_obj_type_by_catg([$filteredCategory['id']]);
                } elseif ($filteredCategory['type'] == C__CMDB__CATEGORY__TYPE_SPECIFIC) {
                    $objectTypeResult = $dao->get_objtype_by_cats_id($filteredCategory['id']);
                }

                if ($objectTypeResult) {
                    while ($objectTypeRow = $objectTypeResult->get_row()) {
                        $objectTypes[$objectTypeRow['isys_obj_type__const']] = $categoryFilterObjectTypeList[$objectTypeRow['isys_obj_type__const']] = $this->lang->get($objectTypeRow['isys_obj_type__title']);
                    }
                }
            }
        }

        $categoryFilterObjectTypeList = array_unique(array_values($categoryFilterObjectTypeList));

        if (count($categoryFilterObjectTypeList)) {
            // Now format the object type list for the GUI.
            $categoryFilterObjectTypeList = array_map(function ($objectType) {
                return '"<strong>' . $objectType . '</strong>"';
            }, $categoryFilterObjectTypeList);
        } else {
            $objectTypeResult = $dao->get_objtype(null, false, C__RECORD_STATUS__NORMAL);

            while ($row = $objectTypeResult->get_row()) {
                $objectTypes[$row['isys_obj_type__const']] = $row['isys_obj_type__title'];
            }

            $categoryFilterObjectTypeList = [$this->lang->get('LC_UNIVERSAL__NONE')];
        }

        $rules = [
            'C__CUSTOMIZE_OBJECT_BROWSER__SHOW_PROPERTY_CATEGORIES' => [
                'p_strClass'      => 'input-mini',
                'p_bDbFieldNN'    => true,
                'p_arData'        => get_smarty_arr_YES_NO(),
                'p_strSelectedID' => (int) TenantSettings::get('cmdb.object-browser.' . $objectBrowserKey . '.displayAttributeCategories', false)
            ],
            'C__CUSTOMIZE_OBJECT_BROWSER__DEFAULT_OBJECT_TYPE'      => [
                'chosen'          => true,
                'p_strClass'      => 'input-small',
                'p_arData'        => $objectTypes,
                'p_strSelectedID' => TenantSettings::get('cmdb.object-browser.' . $objectBrowserKey . '.defaultObjectType', null)
            ],
            'C__CUSTOMIZE_OBJECT_BROWSER__PROPERTIES'               => [
                'grouping'           => false,
                'allow_sorting'      => true,
                'default_sorting'    => TenantSettings::get('cmdb.object-browser.' . $objectBrowserKey . '.defaultSortingField', 0),
                'check_sorting'      => false,
                'sortable'           => true,
                'p_bInfoIconSpacer'  => 0,
                'preselection'       => $this->preparePropertySelectorStrcuture($selectedAttributes),
                'custom_fields'      => true,
                'dynamic_properties' => false,
                'provide'            => C__PROPERTY__PROVIDES__LIST
            ],
            'C__CUSTOMIZE_OBJECT_BROWSER__OBJECT_TYPES[]'           => [
                'chosen'          => true,
                'p_arData'        => $objectTypes,
                'p_strSelectedID' => implode(',', (array)TenantSettings::get('cmdb.object-browser.' . $objectBrowserKey . '.objectTypes', [])),
                'p_multiple'      => true,
                'p_bDbFieldNN'    => true,
            ],
            'C__CUSTOMIZE_OBJECT_BROWSER__DEFAULT_ATTRIBUTE_SORTING' => [
                'p_strClass'      => 'input-mini',
                'p_bDbFieldNN'    => true,
                'p_arData'        => [
                    'asc' => 'LC__CMDB__SORTING__ASC',
                    'desc' => 'LC__CMDB__SORTING__DESC'
                ],
                'p_strSelectedID' => TenantSettings::get('cmdb.object-browser.' . $objectBrowserKey . '.defaultSortingDirection', 'asc')
            ]
        ];

        if ($allowedToEdit) {
            $this->tpl->activate_editmode();
        }

        $this->tpl
            ->assign('category', $category)
            ->assign('categoryParentTitle', $categoryParentTitle)
            ->assign('property', $property)
            ->assign('catFilter', HelperTextformat::this_this_or_that($categoryFilterList))
            ->assign('catFilterObjectTypes', HelperTextformat::this_this_and_that($categoryFilterObjectTypeList))
            ->smarty_tom_add_rules('tom.content.bottom.content', $rules)
            ->include_template('contentbottomcontent', ModuleSystem::getPath() . 'templates/SettingPage/CustomizeObjectBrowser/form.tpl');
    }

    /**
     * This method will reset selected configurations.
     *
     * @param array $keys
     *
     * @throws \Exception
     */
    private function resetConfiguration(array $keys)
    {
        ModuleCmdb::getAuth()->check(Auth::DELETE, 'object_browser_configuration');

        foreach ($keys as $key) {
            list($categoryConstant, $propertyKey, $objectBrowserKey) = explode('::', $key);

            TenantSettings::remove('cmdb.object-browser.' . $objectBrowserKey . '.displayAttributeCategories');
            TenantSettings::remove('cmdb.object-browser.' . $objectBrowserKey . '.defaultObjectType');
            TenantSettings::remove('cmdb.object-browser.' . $objectBrowserKey . '.attributes');
            TenantSettings::remove('cmdb.object-browser.' . $objectBrowserKey . '.objectTypes');
            TenantSettings::remove('cmdb.object-browser.' . $objectBrowserKey . '.defaultSortingField');
            TenantSettings::remove('cmdb.object-browser.' . $objectBrowserKey . '.defaultSortingFieldIndex');
            TenantSettings::remove('cmdb.object-browser.' . $objectBrowserKey . '.defaultSortingDirection');
            TenantSettings::regenerate();
        }
    }

    /**
     * Small helper method to prepare the "property selector" syntax.
     *
     * @param  array $propertyList
     *
     * @return string
     */
    private function preparePropertySelectorStrcuture(array $propertyList)
    {
        $preselection = [];
        $types = [
            C__CMDB__CATEGORY__TYPE_GLOBAL   => 'g',
            C__CMDB__CATEGORY__TYPE_SPECIFIC => 's',
            C__CMDB__CATEGORY__TYPE_CUSTOM   => 'g_custom'
        ];

        foreach ($propertyList as $property) {
            // Our syntax looks like "['C__CATG__GLOBAL::title','C__CATG__GLOBAL::type','C__CATG__GLOBAL::sysid']".
            list($categoryConstant, $propertyKey) = explode('::', $property);

            // We can not blindly rely on strings like "__CATG__" or "__CATS__", so we need to do this:
            $category = isys_cmdb_dao::instance($this->db)
                ->get_cat_by_const($categoryConstant);

            // We convert to a syntax that looks like: "[{"g":{"C__CATG__GLOBAL":["title"]}},{"g":{"C__CATG__GLOBAL":["type"]}},{"g":{"C__CATG__GLOBAL":["sysid"]}}]"
            $preselection[] = [
                $types[$category['type']] => [
                    $categoryConstant => [$propertyKey]
                ]
            ];
        }

        return JSON::encode($preselection);
    }

    /**
     * @param array $properties
     *
     * @return array
     */
    private function preparePropertyList(array $properties)
    {
        $limited = false;
        $propertyList = [];

        if (count($properties) > 5) {
            $limited = true;
            $properties = array_slice($properties, 0, 5);
        }

        foreach ($properties as $property) {
            list($categoryConstant, $propertyKey) = explode('::', $property);

            $category = isys_cmdb_dao::instance($this->db)->get_cat_by_const($categoryConstant);
            $categoryDaoClass = $category['class_name'];

            if (!class_exists($categoryDaoClass) || !is_a($categoryDaoClass, 'isys_cmdb_dao_category', true)) {
                continue;
            }

            $categoryDao = new $categoryDaoClass($this->db);

            if (!method_exists($categoryDao, 'get_property_by_key')) {
                continue;
            }

            $property = $categoryDao->get_property_by_key($propertyKey);

            $propertyList[] = $this->lang->get($category['title']) . ' &raquo; ' . $this->lang->get($property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]);
        }

        if ($limited) {
            $propertyList[] = '...';
        }

        return implode(', ', $propertyList);
    }
}
