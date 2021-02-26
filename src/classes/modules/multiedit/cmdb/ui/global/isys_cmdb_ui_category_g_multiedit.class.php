<?php

use idoit\Module\Multiedit\Component\Filter\CategoryFilter;
use idoit\Module\Multiedit\Model\GlobalCategories;
use idoit\Module\Multiedit\Model\SpecificCategories;
use idoit\Module\Multiedit\Model\CustomCategories;

/**
 * Class isys_cmdb_ui_category_g_multiedit
 */
class isys_cmdb_ui_category_g_multiedit extends isys_cmdb_ui_category_g_virtual
{
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $filter = new CategoryFilter();
        $filter->setObjects([$_GET[C__CMDB__GET__OBJECT]]);
        $language = isys_application::instance()->container->get('language');
        /**
         * @var \isys_cmdb_dao  $cmdbDao
         */
        $cmdbDao = isys_cmdb_dao::instance(isys_application::instance()->container->get('database'));

        $allowedObjectTypes = isys_auth_cmdb_object_types::instance()
            ->get_allowed_objecttypes();

        \isys_component_template_navbar::getInstance()
            ->hide_all_buttons()
            ->deactivate_all_buttons()
            ->set_js_onclick('window.multiEdit.save()', C__NAVBAR_BUTTON__SAVE)
            ->set_active(true, C__NAVBAR_BUTTON__SAVE);

        $class = null;

        if (isset($_GET[C__CMDB__GET__CATG])) {
            $categoryType = C__CMDB__CATEGORY__TYPE_GLOBAL;
            $categoryId = $_GET[C__CMDB__GET__CATG];
            $class = $cmdbDao->get_catg_by_const($categoryId)->get_row_value('isysgui_catg__class_name');

            if ((int)$_GET[C__CMDB__GET__CATG] === defined_or_default('C__CATG__CUSTOM_FIELDS')) {
                $categoryType = C__CMDB__CATEGORY__TYPE_CUSTOM;
                $categoryId = $_GET[C__CMDB__GET__CATG_CUSTOM];
                $class = 'isys_cmdb_dao_category_g_custom_fields';
            }
        } else {
            $categoryType = C__CMDB__CATEGORY__TYPE_SPECIFIC;
            $categoryId = $_GET[C__CMDB__GET__CATS];
            $class = $cmdbDao->get_cats_by_const($categoryId)->get_row_value('isysgui_cats__class_name');
        }

        $categoryInfo = $categoryType . '_' . $categoryId . ':' . $class;
        $ids = $_POST['id'];

        array_walk($ids, function (&$item) {
            $item = 'object-row_' . $_GET[C__CMDB__GET__OBJECT] . '-' . $item;
        });

        $ids = isys_format_json::encode($ids);

        $this->get_template_component()
            ->activate_editmode()
            ->assign('assetsDir', isys_module_multiedit::getPath() . 'assets/')
            ->assign('wwwAssetsDir', isys_module_multiedit::getWwwPath() . 'assets/')
            ->assign('bShowCommentary', '0')
            ->assign('objectId', '['.$_GET[C__CMDB__GET__OBJECT].']')
            ->assign('categoryInfo', $categoryInfo)
            ->assign('selectedIds', $ids)
            ->smarty_tom_add_rule('tom.content.bottom.buttons.*.p_bInvisible=1')
            ->smarty_tom_add_rule('tom.content.navbar.cRecStatus.p_bInvisible=1');

        $index_includes['contentbottomcontent'] = $this->get_template();
    }

    /**
     * Get path to template file.
     *
     * @return  string
     */
    public function get_template()
    {
        return isys_module_multiedit::getPath() . 'templates/catg__multiedit.tpl';
    } // function
}
