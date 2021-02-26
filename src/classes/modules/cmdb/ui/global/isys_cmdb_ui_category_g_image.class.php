<?php

/**
 * i-doit
 *
 * CMDB image category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@synetics.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_image extends isys_cmdb_ui_category_global
{
    /**
     * Show the detail-template for subcategories of file.
     *
     * @global  array                          $index_includes
     * @global  array                          $g_dirs
     *
     * @param   isys_cmdb_dao_category_g_image &$p_cat
     *
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     * @return array|void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $g_dirs, $g_absdir;

        $l_navbar = isys_component_template_navbar::getInstance()
            ->set_save_mode('formsubmit')
            ->set_active(false, C__NAVBAR_BUTTON__PRINT);

        $l_gets = isys_module_request::get_instance()
            ->get_gets();
        $l_posts = isys_module_request::get_instance()
            ->get_posts();

        $l_catdata = $p_cat->get_data(null, $_GET[C__CMDB__GET__OBJECT])
            ->__to_array();

        $l_image_name = $l_catdata["isys_catg_image_list__image_link"];

        if ($l_posts[C__GET__NAVMODE] == C__NAVMODE__DELETE) {
            $p_cat->delete($l_catdata["isys_catg_image_list__id"]);

            $p_cat->delete(null, $l_image_name);

            unset($l_image_name);
            $l_catdata = $p_cat->get_general_data();
        }

        // Assign multipart formdata for file upload.
        $this->get_template_component()
            ->assign("encType", "multipart/form-data");

        $l_uploadedImages = [];
        $imagesDirectoryPath = isys_application::instance()->getImageDir();
        if (file_exists($imagesDirectoryPath) && is_dir($imagesDirectoryPath)) {
            $l_directory = dir($imagesDirectoryPath);
            while ($l_file = $l_directory->read()) {
                if (strpos($l_file, '.') !== 0) {
                    if (is_dir($imagesDirectoryPath . $l_file)) {
                        $subDir = dir($imagesDirectoryPath . $l_file);
                        while ($subDirFile = $subDir->read()) {
                            if (strpos($subDirFile, '.') !== 0) {
                                $l_uploadedImages[$imagesDirectoryPath . $l_file . DS . $subDirFile] = $subDirFile;
                            }
                        }
                    } else {
                        $l_uploadedImages[$imagesDirectoryPath . $l_file] = $l_file;
                    }
                }
            }
        }

        $l_rules = [];
        $l_rules["C__CATG__IMAGE_SELECTION"]["p_arData"] = $l_uploadedImages;
        $l_rules["C__CATG__IMAGE_SELECTION"]["p_strSelectedID"] = (isset($l_image_name) && $l_image_name) ? isys_application::instance()->getUploadImagePath($l_image_name) : '-1';
        $l_rules["C__CATG__IMAGE_TITLE"]["p_strValue"] = $l_catdata["isys_catg_image_list__title"];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_image_list__description"];
        $l_rules["C__CATG__IMAGE_UPLOAD"]["p_strValue"] = $l_catdata["isys_catg_image_list__image_link"];

        $l_default_image = isys_application::instance()->www_path . 'images/objecttypes/' . $p_cat->get_objtype_img_by_id_as_string($p_cat->get_objTypeID($_GET[C__CMDB__GET__OBJECT]));

        if (!empty($l_image_name)) {
            $l_dlgets = $l_gets;
            $l_dlgets[C__GET__MODULE_ID] = defined_or_default('C__MODULE__CMDB');
            $l_dlgets[C__GET__FILE_MANAGER] = "image";
            $l_dlgets["file"] = $l_image_name;

            $this->get_template_component()
                ->assign("g_image_url", isys_glob_http_build_query($l_dlgets));

            $l_navbar->add_onclick_prepend(C__NAVBAR_BUTTON__DELETE, 'if (! confirm(\'' . isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__CATG__IMAGE_DELETE_CONFIRM') . '\')) {return false;}')
                ->set_active(isys_auth_cmdb::instance()
                    ->has_rights_in_obj_and_category(isys_auth::DELETE, $_GET[C__CMDB__GET__OBJECT], $p_cat->get_category_const()), C__NAVBAR_BUTTON__DELETE)
                ->set_visible(true, C__NAVBAR_BUTTON__DELETE);
        }

        // Apply rules.
        $this->get_template_component()
            ->assign('default_image', $l_default_image)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}
