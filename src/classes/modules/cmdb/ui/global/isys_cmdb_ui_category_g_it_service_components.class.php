<?php

/**
 * i-doit
 *
 * CMDB Drive: Global category for IT-Service assignment
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @since       0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_it_service_components extends isys_cmdb_ui_category_global
{
    /**
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @return  null
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        return $this->process_list($p_cat);
    }

    /**
     * Processes category data list for multi-valued categories.
     *
     * @param   isys_cmdb_dao_category $p_cat Category's DAO
     * @param   array                  $p_get_param_override
     * @param   string                 $p_strVarName
     * @param   string                 $p_strTemplateName
     * @param   boolean                $p_bCheckbox
     * @param   boolean                $p_bOrderLink
     * @param   string                 $p_db_field_name
     *
     * @return  null
     * @throws  isys_exception_general
     * @author  Dennis Stuecken <dstuecken@synetics.de>
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
        $this->object_relation_browser_as_new([
            'name'                                                 => 'C__CMDB__CATG__IT_SERVICE_COMPONENTS__CONNECTED_OBJECT',
            isys_popup_browser_object_relation::C__MULTISELECTION  => true,
            isys_popup_browser_object_relation::C__RELATION_FILTER => "C__RELATION_TYPE__SOFTWARE;C__RELATION_TYPE__CLUSTER_SERVICE",
            isys_popup_browser_object_relation::C__FORM_SUBMIT     => true,
            isys_popup_browser_object_relation::C__RETURN_ELEMENT  => C__POST__POPUP_RECEIVER,
            isys_popup_browser_object_relation::C__DATARETRIEVAL   => [
                [get_class($p_cat), "get_data_by_object"],
                $_GET[C__CMDB__GET__OBJECT],
                [
                    "isys_connection__isys_obj__id",
                    "itsc_title",
                    "itsc_type",
                    "itsc_sysid"
                ]
            ]
            // this is where the browser tries to get a preselection from
        ], "LC__UNIVERSAL__OBJECT_ADD_REMOVE", "LC__UNIVERSAL__OBJECT_ADD_REMOVE_DESCRIPTION");

        isys_component_template_navbar::getInstance()
            ->set_active((isys_auth_cmdb::instance()
                ->has_rights_in_obj_and_category(isys_auth::EDIT, $_GET[C__CMDB__GET__OBJECT], $p_cat->get_category_const())), C__NAVBAR_BUTTON__NEW)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW)
            ->set_visible(false, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(false, C__NAVBAR_BUTTON__SAVE)
            ->set_visible(false, C__NAVBAR_BUTTON__CANCEL)
            ->set_visible(false, C__NAVBAR_BUTTON__PRINT)
            ->set_active(false, C__NAVBAR_BUTTON__EDIT);

        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
    }

    /**
     * Constructor.
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        $this->set_template("catg__guest_systems.tpl");
        parent::__construct($p_template);
    }
}
