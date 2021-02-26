<?php

/**
 * i-doit
 * CMDB Person: Specific category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_organization_person extends isys_cmdb_ui_category_specific
{
    /**
     * Method for processing list.
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
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @see     isys_cmdb_ui_category::process_list()
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
        global $index_includes;

        $l_tpl = isys_module_request::get_instance()
            ->get_template();

        $this->object_browser_as_new([
            'name'                                          => 'C__CMDB__CATS__ORGANIZATION_PERSON__OBJECT',
            isys_popup_browser_object_ng::C__MULTISELECTION => true,
            isys_popup_browser_object_ng::C__CAT_FILTER     => 'C__CATS__PERSON',
            isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
            isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
            isys_popup_browser_object_ng::C__DATARETRIEVAL  => [
                [get_class($p_cat), 'get_data_by_object'],
                $_GET[C__CMDB__GET__OBJECT],
                [
                    'isys_obj__id',
                    'isys_obj__title',
                    'isys_obj__isys_obj_type__id',
                    'isys_obj__sysid'
                ]
            ]
        ], 'LC__UNIVERSAL__OBJECT_ADD_REMOVE', 'LC__UNIVERSAL__OBJECT_ADD_REMOVE_DESCRIPTION');

        $l_supervisor_right = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::SUPERVISOR, $_GET[C__CMDB__GET__OBJECT], $p_cat->get_category_const());

        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__ARCHIVE)
            ->set_visible(false, C__NAVBAR_BUTTON__ARCHIVE)
            ->set_active(false, C__NAVBAR_BUTTON__DELETE)
            ->set_visible(false, C__NAVBAR_BUTTON__DELETE)
            ->set_active($l_supervisor_right, C__NAVBAR_BUTTON__PURGE)
            ->set_visible($l_supervisor_right, C__NAVBAR_BUTTON__PURGE);

        $l_tpl->smarty_tom_add_rule('tom.content.bottom.buttons.*.p_bInvisible=1');
        $index_includes['contentbottomcontent'] = 'content/bottom/content/cats__organization_person.tpl';

        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, 'isys_cats_person_list__isys_obj__id');
    }

    /**
     * Constructor.
     *
     * @param   isys_component_template $p_template
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
    }
}
