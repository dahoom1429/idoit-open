<?php

/**
 * i-doit
 *
 * CMDB Active Directory: Specific category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_file extends isys_cmdb_ui_category_specific
{
    /**
     * Show the detail-template for specific category file.
     *
     * @param  isys_cmdb_dao_category $p_cat
     *
     * @return void
     * @throws Exception
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $g_dirs;

        if ($_GET['load_img'] == 1) {
            // This small part will remove any additional parameters (for security reasons).
            $_GET = [C__CMDB__GET__OBJECT => $_GET[C__CMDB__GET__OBJECT]];

            $l_mimetypes = isys_helper::get_image_mimetypes();
            $l_file = $p_cat->get_file_by_obj_id($_GET[C__CMDB__GET__OBJECT])->get_row();
            $l_fileextension = end(explode('.', $l_file['isys_file_physical__filename']));

            if (!array_key_exists($l_fileextension, $l_mimetypes) || !$l_file['isys_file_physical__filename']) {
                // The selected file seems to be no image - So we display nothing :/
                die;
            }

            header('Content-type: ' . $l_mimetypes[$l_fileextension]);
            echo file_get_contents(isys_application::instance()->getUploadFilePath($l_file['isys_file_physical__filename']));

            die;
        }

        global $index_includes;

        $database = isys_application::instance()->container->get('database');
        $language = isys_application::instance()->container->get('language');
        $locales = isys_application::instance()->container->get('locales');

        isys_component_template_navbar::getInstance()
            ->set_visible(false, C__NAVBAR_BUTTON__PRINT);

        $l_dao_cat_s_file = new isys_cmdb_dao_category_s_file($database);

        $l_catdata = $p_cat->get_general_data();

        $l_object_id = $_GET[C__CMDB__GET__OBJECT];
        $l_cats_id = $l_catdata["isys_cats_file_list__id"];

        if (!is_null($l_cats_id)) {
            $l_file_dao = $l_dao_cat_s_file->get_file_by_cats_id($l_cats_id);
            $l_active_file = $l_file_dao->get_row();
        } else {
            $l_active_file = [];
        }

        $this->get_template_component()
            ->assign("encType", "multipart/form-data");

        if (isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__NEW) {
            $_GET['editMode'] = true;
            $l_rules = [
                'LC__CMDB__CATS__FILE_HEADER' => [
                    'p_strValue' => $language->get('LC__CMDB__CATS__FILE_VERSION_NEW')
                ]
            ];

            // Do not show add. infos, because no file is uploaded yet.
            $this->get_template_component()
                ->assign("new_file_uploaded", 1);
        } else {
            // Get add. infos.
            $this->get_template_component()
                ->assign("new_file_uploaded", 0);

            $l_arData = [];

            // Get all versions of this object.
            $l_versions_by_obj = $l_dao_cat_s_file->get_versions_by_obj_id($l_object_id);
            while ($l_vrow = $l_versions_by_obj->get_row()) {
                $l_arData[$l_vrow["isys_file_version__id"]] = $l_vrow["isys_file_version__title"] . " (rev " . $l_vrow["isys_file_version__revision"] . ")";
            }

            $l_rules = [
                'LC__CMDB__CATS__FILE_HEADER' => [
                    'p_strValue' => $language->get('LC__CMDB__CATS__FILE_CURRENT')
                ],
                'C__CATS__FILE_VERSION'       => [
                    'p_arData' => $l_arData,
                    'p_strSelectedID' => $l_catdata['isys_cats_file_list__isys_file_version__id']
                ],
                'C__CATS__FILE_CATEGORY'      => [
                    'p_strSelectedID' => $l_catdata['isys_cats_file_list__isys_file_category__id']
                ],
                'C__CATS__FILE_NAME'          => [
                    'p_strValue' => str_replace(' ', '%20', $l_catdata['isys_file_physical__filename_original'])
                ]
            ];

            if (!empty($l_active_file)) {
                $l_rules['C__CATS__FILE_TITLE']['p_strValue'] = $l_active_file['isys_file_physical__filename_original'];
                $l_rules['C__CATS__FILE_VERSION_TITLE']['p_strValue'] = $l_active_file['isys_file_version__title'];
                $l_rules['C__CATS__FILE_VERSION_DESCRIPTION']['p_strValue'] = $l_active_file['isys_file_version__description'];
                $l_rules['C__CATS__FILE_MD5']['p_strValue'] = $l_active_file['isys_file_physical__md5'];
                $l_rules['C__CATS__FILE_NAME']['p_strValue'] = str_replace(' ', '%20', $l_catdata['isys_file_physical__filename_original']);
                $l_rules['C__CATS__FILE_REVISION']['p_strValue'] = $l_active_file['isys_file_version__revision'];
                $l_rules['C__CATS__FILE_UPLOAD_DATE']['p_strValue'] = $locales->fmt_datetime($l_active_file['isys_file_physical__date_uploaded'], true, false);
                $l_rules['C__CATS__FILE_DIRECTORY']['p_strValue'] = basename(isys_application::instance()->getOrCreateUploadFileDir($l_active_file['isys_file_physical__filename']));
            }

            // Get username by id.
            if (!empty($l_active_file["isys_file_physical__user_id_uploaded"])) {
                $l_dao_person = new isys_cmdb_dao_category_s_person_master($p_cat->get_database_component());
                $l_rules["C__CATS__FILE_UPLOAD_FROM"]["p_strValue"] = $l_dao_person->get_username_by_id_as_string($l_active_file["isys_file_physical__user_id_uploaded"]);
            }

            /*
              * store upload path in a hidden field
              *  -> and activate the download link
              */
            if (!empty($l_rules["C__CATS__FILE_TITLE"]["p_strValue"])) {
                $this->get_template_component()->assign("file_uploaded", 1);

                $l_rules["C__CATS__FILE_NAME"]["p_strValue"] = $l_active_file["isys_file_physical__filename_original"];
                $l_rules["C__CATS__FILE_DOWNLOAD"]["p_strLink"] = "?" . $_SERVER["QUERY_STRING"] . "&mod=cmdb&file_manager=get&f_id=" .
                    $l_active_file["isys_file_physical__id"];
            } else {
                $l_rules["C__CATS__FILE_NAME"]["p_strValue"] = "No file uploaded yet..";
                $index_includes["contentbottomcontent"] = "content/bottom/content/cats__file__none_uploaded.tpl";
            }
        }

        // @see  ID-6526  We need to set the commentary field to two rules, because there are two categories.
        $l_rules['C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__FILE')]['p_strValue'] = $l_catdata['isys_cats_file_list__description'];
        $l_rules['C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__FILE_ACTUAL')]['p_strValue'] = $l_catdata['isys_cats_file_list__description'];

        // Apply rules
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $this->activate_commentary($p_cat);

        if (empty($index_includes["contentbottomcontent"])) {
            $index_includes["contentbottomcontent"] = $this->get_template();
        }
    }
}
