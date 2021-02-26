<?php

/**
 * i-doit
 *
 * CMDB assigned logical unit: global category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_assigned_logical_unit extends isys_cmdb_ui_category_global
{
    /**
     * @param   isys_cmdb_dao_category_g_assigned_logical_unit $p_cat
     *
     * @return  null
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        return $this->process_list($p_cat);
    }

    /**
     * Method for processing the list-view.
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
     * @throws isys_exception_general
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
            'name'                                          => 'C__CATG__ASSIGNED_LOGICAL_UNITS',
            isys_popup_browser_object_ng::C__MULTISELECTION => true,
            isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
            isys_popup_browser_object_ng::C__CAT_FILTER     => "C__CATG__ASSIGNED_WORKSTATION",
            isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
            isys_popup_browser_object_ng::C__DATARETRIEVAL  => [
                [
                    'isys_cmdb_dao_category_g_assigned_logical_unit',
                    "get_selected_objects"
                ],
                $_GET[C__CMDB__GET__OBJECT],
                [
                    "isys_obj__id",
                    "isys_obj__title",
                    "isys_obj__isys_obj_type__id",
                    "isys_obj__sysid"
                ]
            ]
        ];

        $l_instance = new isys_popup_browser_object_ng();

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons()
            ->deactivate_all_buttons()
            ->set_js_onclick($l_instance->get_js_handler($l_params), C__NAVBAR_BUTTON__NEW)
            ->set_title(isys_application::instance()->container->get('language')
                ->get("LC__UNIVERSAL__OBJECT_ADD_REMOVE"), C__NAVBAR_BUTTON__NEW)
            ->set_active(isys_auth_cmdb::instance()
                ->has_rights_in_obj_and_category(isys_auth::EDIT, $_GET[C__CMDB__GET__OBJECT], $p_cat->get_category_const()), C__NAVBAR_BUTTON__NEW)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW);

        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
    }

    /**
     * Constructor.
     *
     * @todo    Is this a reversed-category or can the constructor be removed?
     *
     * @param   isys_component_template $p_template
     *
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     */
    public function __construct(isys_component_template &$p_template)
    {
        $this->set_template("catg__assigned_logical_unit.tpl");
        parent::__construct($p_template);
    }
}
