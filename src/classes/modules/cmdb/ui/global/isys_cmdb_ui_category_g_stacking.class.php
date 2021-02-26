<?php

/**
 * i-doit
 *
 * CMDB Global category stacking.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_stacking extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @return  boolean
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        return $this->process_list($p_cat);
    }

    /**
     * Process list method.
     *
     * @param isys_cmdb_dao_category $p_cat
     * @param null                   $p_get_param_override
     * @param null                   $p_strVarName
     * @param null                   $p_strTemplateName
     * @param bool                   $p_bCheckbox
     * @param bool                   $p_bOrderLink
     * @param null                   $p_db_field_name
     *
     * @return boolean
     * @throws isys_exception_cmdb
     * @author Van Quyen Hoang <qhoang@i-doit.org>
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
        $this->object_browser_as_new([
            'name'                                          => 'C__CMDB__CATS__CHASSIS__OBJECT',
            isys_popup_browser_object_ng::C__MULTISELECTION => true,
            isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
            isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
            isys_popup_browser_object_ng::C__DATARETRIEVAL  => [
                ['isys_cmdb_dao_category_g_stacking', "get_connected_objects"],
                $_GET[C__CMDB__GET__OBJECT]
            ]
        ], "LC__UNIVERSAL__OBJECT_ADD_REMOVE", "LC__UNIVERSAL__OBJECT_ADD_REMOVE_DESCRIPTION");

        $l_new_rights = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::EDIT, $_GET[C__CMDB__GET__OBJECT], $p_cat->get_category_const());

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons()
            ->deactivate_all_buttons()
            ->set_active($l_new_rights, C__NAVBAR_BUTTON__NEW)
            ->set_visible($l_new_rights, C__NAVBAR_BUTTON__NEW);

        $this->list_view("isys_catg_stacking", $_GET[C__CMDB__GET__OBJECT], isys_cmdb_dao_list_catg_stacking::build($p_cat->get_database_component(), $p_cat));

        return true;
    }
}
