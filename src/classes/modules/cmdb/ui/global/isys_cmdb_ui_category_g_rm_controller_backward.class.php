<?php

/**
 * i-doit
 *
 * UI: global category for Remote Management Controller
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_rm_controller_backward extends isys_cmdb_ui_category_global
{
    /**
     * Process list method.
     *
     * @param   isys_cmdb_dao_category $p_cat
     * @param   null                   $p_get_param_override
     * @param   null                   $p_strVarName
     * @param   null                   $p_strTemplateName
     * @param   boolean                $p_bCheckbox
     * @param   boolean                $p_bOrderLink
     * @param   null                   $p_db_field_name
     *
     * @return  null
     * @throws  isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function process_list(
        isys_cmdb_dao_category &$p_cat,
        $p_get_param_override = null,
        $p_strVarName = null,
        $p_strTemplateName = null,
        $p_bCheckbox = false,
        $p_bOrderLink = true,
        $p_db_field_name = null
    ) {
        $this->object_browser_as_new([
            'name'                                          => 'C__CATG__RM_CONTROLLER__ASSIGNED_OBJECT',
            isys_popup_browser_object_ng::C__MULTISELECTION => true,
            isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
            isys_popup_browser_object_ng::C__CAT_FILTER     => 'C__CATG__RM_CONTROLLER',
            isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
            isys_popup_browser_object_ng::C__DATARETRIEVAL  => [
                ['isys_cmdb_dao_category_g_rm_controller_backward', 'get_assigned_objects'],
                $_GET[C__CMDB__GET__OBJECT],
                [
                    "isys_obj__id",
                    "isys_obj__title",
                    "isys_obj__isys_obj_type__id",
                    "isys_obj__sysid"
                ]
            ],
        ], "LC__UNIVERSAL__OBJECT_ADD_REMOVE", "LC__UNIVERSAL__OBJECT_ADD_REMOVE_DESCRIPTION");

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons([C__NAVBAR_BUTTON__NEW])
            ->set_active(isys_auth_cmdb::instance()
                ->has_rights_in_obj_and_category(isys_auth::EDIT, $_GET[C__CMDB__GET__OBJECT], $p_cat->get_category_const()), C__NAVBAR_BUTTON__NEW);

        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
    }

    /**
     * UI constructor.
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("catg__rm_controller.tpl");
    }
}