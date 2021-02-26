<?php

/**
 * i-doit
 *
 * CMDB Person: Specific category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @author      Leonard Fischer <lfischer@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_person_assigned_groups extends isys_cmdb_ui_category_specific
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @param null                     $p_get_param_override
     * @param null                     $p_strVarName
     * @param null                     $p_strTemplateName
     * @param bool                     $p_bCheckbox
     * @param bool                     $p_bOrderLink
     * @param null                     $p_db_field_name
     *
     * @return bool
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
        $l_params = [
            'name'                                          => 'C__CATS__PERSON_ASSIGNED_GROUPS__CONNECTED_OBJECT',
            isys_popup_browser_object_ng::C__MULTISELECTION => true,
            isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
            isys_popup_browser_object_ng::C__CAT_FILTER     => 'C__CATS__PERSON_GROUP',
            isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
            isys_popup_browser_object_ng::C__DATARETRIEVAL  => [
                [
                    get_class($p_cat),
                    "get_assigned_contacts"
                ],
                $_GET[C__CMDB__GET__OBJECT],
                [
                    "isys_obj__id",
                    "isys_obj__title",
                    "isys_obj__isys_obj_type__id",
                    "isys_obj__sysid"
                ]
            ],
        ];

        $l_instance = new isys_popup_browser_object_ng();

        // Special rule for this category, because only administrators shall be able to assign groups.
        $l_edit_right = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::SUPERVISOR, $_GET[C__CMDB__GET__OBJECT], $p_cat->get_category_const());

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons([C__NAVBAR_BUTTON__NEW])
            ->set_js_onclick($l_instance->get_js_handler($l_params), C__NAVBAR_BUTTON__NEW)
            ->set_title(isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__OBJECT_ADD_REMOVE'), C__NAVBAR_BUTTON__NEW)
            ->set_tooltip(isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__OBJECT_ADD_REMOVE_DESCRIPTION'), C__NAVBAR_BUTTON__NEW)
            ->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
            ->set_visible($l_edit_right, C__NAVBAR_BUTTON__NEW);

        $this->list_view("isys_person_2_group__id", $_GET[C__CMDB__GET__OBJECT],
            isys_cmdb_dao_list_cats_person_assigned_groups::build($p_cat->get_database_component(), $p_cat), null, null, null, false);

        return true;
    }

    /**
     * UI constructor.
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("cats__person_login.tpl");
    }
}
