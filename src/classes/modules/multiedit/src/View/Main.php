<?php

namespace idoit\Module\Multiedit\View;

use idoit\Model\Dao\Base as DaoBase;
use idoit\Module\Multiedit\Model\CustomCategories;
use idoit\Module\Multiedit\Model\GlobalCategories;
use idoit\Module\Multiedit\Model\SpecificCategories;
use idoit\Module\Multiedit\Component\Filter\CategoryFilter;
use idoit\Module\Multiedit\Component\Multiedit\Exception\CategoryDataException;
use idoit\View\Base;
use idoit\View\Renderable;
use isys_application;
use isys_component_template as ComponentTemplate;
use isys_module as ModuleBase;
use isys_module_multiedit;
use isys_tenantsettings;
use isys_auth_cmdb_object_types;
use isys_auth;
use isys_notify;

/**
 *
 *
 * @package     modules
 * @subpackage  multiedit
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     1.12
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Main extends Base
{

    /**
     * @param ModuleBase        $module
     * @param ComponentTemplate $template
     * @param DaoBase           $model
     *
     * @return $this|Renderable
     * @throws \idoit\Exception\JsonException
     * @throws \isys_exception_database
     */
    public function process(ModuleBase $module, ComponentTemplate $template, DaoBase $model)
    {
        global $g_dirs;

        if (!defined('C__MODULE__PRO')) {
            \isys_notify::info('The "list edit" feature is not available in i-doit OPEN. Please select a single object to edit.', ['sticky' => true]);
            $this->paths['contentbottomcontent'] = $module::getPath() . 'templates/auth-error.tpl';

            $template->assign('errorMessage', 'The "list edit" feature is not available in i-doit OPEN. Please select a single object to edit.');
            return $this;
        }

        $authorizationMessage = '';
        $multivalueCategoies = [];
        $globalCategoriesData = [];
        $specificCategoriesData = [];
        $customCategoriesData = [];
        $rules = [];
        $categories = [];
        $objTypeGroupId = null;

        // Check if the user is allowed to view the protection requirements.
        if (!isys_module_multiedit::getAuth()->is_allowed_to(isys_auth::VIEW, 'multiedit')) {
            $this->paths['contentbottomcontent'] = $module::getPath() . 'templates/auth-error.tpl';

            $template->assign('errorMessage', _L('LC__AUTH__MULTIEDIT_EXCEPTION__MISSING_RIGHT_FOR_MULTIEDIT', _L('LC__AUTH__RIGHT_VIEW')));

            return $this;
        }

        /**
         * Set paths to templates
         */
        $this->paths['contentbottomcontent'] = $module::getPath() . 'templates/main.tpl';

        $container = isys_application::instance()->container;
        $language = $container->get('language');
        $database = $container->get('database');

        $preselection = $_GET['preselect'] ?: null;
        $selectedObjects = null;

        $filter = new CategoryFilter();

        $allowedCategories = \isys_auth_cmdb_categories::instance()
            ->get_allowed_categories();
        if (is_array($allowedCategories)) {
            $filter->setCategories($allowedCategories);
        }

        if ($preselection) {
            $selectedObjects = \isys_format_json::decode($preselection);
            $filter->setObjects($selectedObjects);

            /**
             * We only need categories if there is a preselection
             */
            try {
                $globalCategories = GlobalCategories::instance($database)
                    ->setFilter($filter)
                    ->setData();
                $globalCategoriesData = $globalCategories->getData();
                $specificCategories = SpecificCategories::instance($database)
                    ->setFilter($filter)
                    ->setData();
                $specificCategoriesData = $specificCategories->getData();
                $customCategories = CustomCategories::instance($database)
                    ->setFilter($filter)
                    ->setData();
                $customCategoriesData = $customCategories->getData();

                $multivalueCategoies = $globalCategories->getMultivalueCategories() + $specificCategories->getMultivalueCategories() +
                    $customCategories->getMultivalueCategories();

                $categories[$language->get('LC__CMDB__GLOBAL_CATEGORIES')] = $globalCategoriesData;
                $categories[$language->get('LC__CMDB__SPECIFIC_CATEGORIES')] = $specificCategoriesData;
                $categories[$language->get('LC__CMDB__CUSTOM_CATEGORIES')] = $customCategoriesData;

                asort($categories[$language->get('LC__CMDB__GLOBAL_CATEGORIES')]);
                asort($categories[$language->get('LC__CMDB__SPECIFIC_CATEGORIES')]);
                asort($categories[$language->get('LC__CMDB__CUSTOM_CATEGORIES')]);
            } catch (CategoryDataException $e) {
                isys_notify::error($e->getMessage(), ['sticky' => true]);
            }
        }

        /**
         * @var \isys_cmdb_dao $cmdbDao
         */
        $cmdbDao = $container->get('cmdb_dao');

        $allowedObjectTypes = isys_auth_cmdb_object_types::instance()
            ->get_allowed_objecttypes();

        $objType = $_GET[C__CMDB__GET__OBJECTTYPE] ?: array_shift($allowedObjectTypes);
        $objTypeGroupId = $cmdbDao->get_objtype($objType)
            ->get_row_value('isys_obj_type__isys_obj_type_group__id');

        // Assign rules.
        $rules = [
            'C__MULTIEDIT__CATEGORY'           => [
                'p_arData'        => $categories,
                'p_bDbFieldNN'    => false,
                'p_bSort'         => false,
                'p_strSelectedID' => @$_GET[C__CMDB__GET__CATG]
            ],
            'C__MULTIEDIT__OBJECTS'            => [
                \isys_popup_browser_object_ng::C__MULTISELECTION   => true,
                'p_strValue'                                       => $preselection,
                \isys_popup_browser_object_ng::C__CALLBACK__DETACH => "$('multiedit_list').update('');",
                \isys_popup_browser_object_ng::C__CALLBACK__ACCEPT => "$('multiedit-config').fire('objects:updated')",
            ],
            'C__MULTIEDIT__FILTER_OBJECT_INFO' => [
                'p_arData'        => [
                    '-1' => $language->get('LC__MODULE__MULTIEDIT__HIDE_SYSID_AND_OBJECT_ID'),
                    '1'  => $language->get('LC__MODULE__MULTIEDIT__SHOW_SYSID'),
                    '2'  => $language->get('LC__MODULE__MULTIEDIT__SHOW_OBJECT_ID'),
                    '3'  => $language->get('LC__MODULE__MULTIEDIT__SHOW_SYSID_AND_OBJECT_ID')
                ],
                'p_bSort'         => false,
                'p_strSelectedID' => '-1',
                'p_bDbFieldNN'    => true,
            ]
        ];

        \isys_component_template_navbar::getInstance()
            ->set_js_onclick('window.multiEdit.save()', C__NAVBAR_BUTTON__SAVE)
            ->set_active(true, C__NAVBAR_BUTTON__SAVE)
            ->set_js_onclick('window.multiEdit.addNewValuesPopup(this)', C__NAVBAR_BUTTON__EDIT)
            ->set_icon('icons/silk/page_add.png', C__NAVBAR_BUTTON__EDIT)
            ->set_title($language->get('LC__MODULE__MULTIEDIT__ADD_VALUES'), C__NAVBAR_BUTTON__EDIT)
            ->set_active(true, C__NAVBAR_BUTTON__EDIT);

        $template
            ->activate_editmode()
            ->assign('wwwAssetsDir', $module::getWwwPath() . 'assets/')
            ->assign('assetsDir', $module::getPath() . 'assets/')
            ->assign('objTypeGroup', $objTypeGroupId)
            ->assign('multivalueCategories', \isys_format_json::encode($multivalueCategoies))
            ->smarty_tom_add_rule('tom.content.bottom.buttons.*.p_bInvisible=1')
            ->smarty_tom_add_rule('tom.content.navbar.cRecStatus.p_bInvisible=1')
            ->smarty_tom_add_rules('tom.content.bottom.content', $rules);

        return $this;
    }
}
