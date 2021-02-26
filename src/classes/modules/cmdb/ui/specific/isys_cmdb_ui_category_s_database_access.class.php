<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dsteucken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_database_access extends isys_cmdb_ui_category_specific
{
    /**
     * Process the list-view.
     *
     * @param   isys_cmdb_dao_category $p_cat
     * @param   null                   $p_get_param_override
     * @param   string                 $p_strVarName
     * @param   string                 $p_strTemplateName
     * @param   boolean                $p_bCheckbox
     * @param   boolean                $p_bOrderLink
     * @param   string                 $p_db_field_name
     *
     * @return  null
     * @throws  isys_exception_general
     * @author  Dennis Stücken <dsteucken@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
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
            'name'                                            => 'C__CMDB__CATS__DATABASE_ACCESS__ACCESS',
            isys_popup_browser_object_ng::C__MULTISELECTION   => true,
            isys_popup_browser_object_ng::C__FORM_SUBMIT      => true,
            isys_popup_browser_object_ng::C__RETURN_ELEMENT   => C__POST__POPUP_RECEIVER,
            isys_popup_browser_object_ng::C__DATARETRIEVAL    => [
                ['isys_cmdb_dao_category_s_database_access', 'get_data_by_object'],
                $_GET[C__CMDB__GET__OBJECT],
                [
                    "isys_connection__id",
                    "assignment_title",
                    "assignment_type",
                    "assignment_sysid"
                ]
            ],
            isys_popup_browser_object_ng::C__SECOND_SELECTION => true,
            isys_popup_browser_object_ng::C__CAT_FILTER       => "C__CATS__APPLICATION;C__CATS__LICENCE;C__CATS__OPERATING_SYSTEM;C__CATS__CLUSTER_SERVICE;C__CATS__DBMS;C__CATS__DATABASE_SCHEMA;C__CATS__DATABASE_INSTANCE;C__CATS__MIDDLEWARE",
            isys_popup_browser_object_ng::C__SECOND_LIST      => [
                'isys_cmdb_dao_category_s_database_access::object_browser',
                ['typefilter' => defined_or_default('C__RELATION_TYPE__SOFTWARE')]
            ],
        ], "LC__UNIVERSAL__OBJECT_ADD_REMOVE", "LC__UNIVERSAL__OBJECT_ADD_REMOVE_DESCRIPTION");

        $l_edit_right = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::EDIT, $_GET[C__CMDB__GET__OBJECT], $p_cat->get_category_const());

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons()
            ->deactivate_all_buttons()
            ->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
            ->set_active(true, C__NAVBAR_BUTTON__PRINT)
            ->set_visible($l_edit_right, C__NAVBAR_BUTTON__NEW);

        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, true, true, "isys_cats_database_access");
    }

    /**
     * Process category view
     *
     * @param isys_cmdb_dao_category $p_cat
     *
     * @return array|null
     * @throws isys_exception_general
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        /**
         * Redirect to list processor
         *
         * @see ID-6351
         */
        return $this->process_list($p_cat);
    }

}