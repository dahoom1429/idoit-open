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
class isys_cmdb_ui_category_g_itservice extends isys_cmdb_ui_category_global
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
        // @todo  Check if "C__CMDB__CATG__ITSERVICE__CONNECTED_OBJECT" can be changed to "C__CMDB__CATG__ITSERVICE__CONNECTED_OBJECT__HIDDEN".

        $this->object_browser_as_new([
            'name'                                           => 'C__CMDB__CATG__ITSERVICE__CONNECTED_OBJECT',
            isys_popup_browser_object_ng::C__MULTISELECTION  => true,
            isys_popup_browser_object_ng::C__RELATION_FILTER => "C__RELATION_TYPE__SOFTWARE;C__RELATION_TYPE__CLUSTER_SERVICE",
            isys_popup_browser_object_ng::C__FORM_SUBMIT     => true,
            isys_popup_browser_object_ng::C__CAT_FILTER      => "C__CATG__SERVICE;C__CATG__IT_SERVICE_RELATIONS;C__CATG__IT_SERVICE_COMPONENTS;C__CATG__ITS_LOGBOOK;C__CATG__ITS_TYPE",
            isys_popup_browser_object_ng::C__RETURN_ELEMENT  => 'C__CMDB__CATG__ITSERVICE__CONNECTED_OBJECT',
            isys_popup_browser_object_ng::C__DATARETRIEVAL   => [
                [
                    get_class($p_cat),
                    "get_data_by_object"
                ],
                $_GET[C__CMDB__GET__OBJECT]
            ]
        ], "LC__UNIVERSAL__OBJECT_ADD_REMOVE", "LC__UNIVERSAL__OBJECT_ADD_REMOVE_DESCRIPTION", "C__CMDB__CATG__ITSERVICE__CONNECTED_OBJECT");

        // ID-2782, ID-3052 LF: "$this->object_browser_as_new" removes all buttons (archive, delete, purge recycle), so we need to include these manually.
        $l_navbar = isys_component_template_navbar::getInstance();
        $l_auth_archive = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::ARCHIVE, $p_cat->get_object_id(), 'C__CATG__IT_SERVICE');
        $l_auth_delete = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::DELETE, $p_cat->get_object_id(), 'C__CATG__IT_SERVICE');
        $l_auth_supervisor = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::SUPERVISOR, $p_cat->get_object_id(), 'C__CATG__IT_SERVICE');

        switch ($_SESSION["cRecStatusListView"]) {
            case C__RECORD_STATUS__NORMAL:
                $l_navbar->set_visible(false, C__NAVBAR_BUTTON__PURGE)
                    ->set_visible(true, C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_active(($l_auth_archive || $l_auth_delete), C__NAVBAR_BUTTON__ARCHIVE);
                break;
            case C__RECORD_STATUS__ARCHIVED:
                $l_navbar->set_visible(false, C__NAVBAR_BUTTON__PURGE)
                    ->set_visible(true, C__NAVBAR_BUTTON__DELETE)
                    ->set_active(($l_auth_archive || $l_auth_delete), C__NAVBAR_BUTTON__DELETE)
                    ->set_visible(true, C__NAVBAR_BUTTON__RECYCLE)
                    ->set_active(($l_auth_archive || $l_auth_delete), C__NAVBAR_BUTTON__RECYCLE);
                break;
            case C__RECORD_STATUS__DELETED:
                $l_navbar->set_visible(true, C__NAVBAR_BUTTON__PURGE)
                    ->set_active($l_auth_supervisor, C__NAVBAR_BUTTON__PURGE)
                    ->set_visible(true, C__NAVBAR_BUTTON__RECYCLE)
                    ->set_active(($l_auth_archive || $l_auth_delete), C__NAVBAR_BUTTON__RECYCLE);
                break;
        }

        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
    }

    /**
     * Constructor.
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        $this->set_template("catg__itservice.tpl");
        parent::__construct($p_template);
    }
}