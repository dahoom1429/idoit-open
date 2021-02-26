<?php

/**
 * i-doit
 *
 * UI for subcategory assigned objects of specific category contract.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @version     Andre Wösten <awoesten@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_contract_allocation extends isys_cmdb_ui_category_specific
{
    /**
     * Returns the title of the specific category.
     *
     * @param   isys_cmdb_dao_category &$p_cat
     *
     * @return  string
     * @author  Andre Wösten <awoesten@i-doit.org>
     */
    public function gui_get_title(isys_cmdb_dao_category &$p_cat)
    {
        return isys_application::instance()->container->get('language')
            ->get("LC__CMDB__CATS__CONTRACT_ALLOCATION");
    }

    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @return  null
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        return $this->process_list($p_cat);
    }

    /**
     * Show the list-template for subcategory of contract allocation.
     *
     * @param isys_cmdb_dao_category $p_cat
     * @param null                   $p_get_param_override
     * @param null                   $p_strVarName
     * @param null                   $p_strTemplateName
     * @param bool                   $p_bCheckbox
     * @param bool                   $p_bOrderLink
     * @param null                   $p_db_field_name
     *
     * @return null
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
        $this->object_browser_as_new([
            'name'                                          => 'C__CMDB__CATS__CONTRACT_ALLOCATION__ASSIGNED_OBJECT',
            isys_popup_browser_object_ng::C__MULTISELECTION => true,
            isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
            isys_popup_browser_object_ng::C__CAT_FILTER     => 'C__CATG__CONTRACT_ASSIGNMENT',
            isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
            isys_popup_browser_object_ng::C__DATARETRIEVAL  => [
                [get_class($p_cat), "get_assigned_objects"],
                $_GET[C__CMDB__GET__OBJECT]
            ]
        ], "LC__UNIVERSAL__OBJECT_ADD_REMOVE", "LC__UNIVERSAL__OBJECT_ADD_REMOVE_DESCRIPTION");

        $navbar = isys_component_template_navbar::getInstance();
        $auth = isys_auth_cmdb::instance();
        $l_archive_right = $auth->has_rights_in_obj_and_category(isys_auth::ARCHIVE, isys_glob_get_param(C__CMDB__GET__OBJECT), 'C__CATS__CONTRACT_ALLOCATION');
        $l_delete_right = $auth->has_rights_in_obj_and_category(isys_auth::DELETE, isys_glob_get_param(C__CMDB__GET__OBJECT), 'C__CATS__CONTRACT_ALLOCATION');
        $l_supervisor_right = $auth->has_rights_in_obj_and_category(isys_auth::SUPERVISOR, isys_glob_get_param(C__CMDB__GET__OBJECT), 'C__CATS__CONTRACT_ALLOCATION');

        switch ($_SESSION['cRecStatusListView']) {
            case C__RECORD_STATUS__NORMAL:
                $navbar->set_visible(false, C__NAVBAR_BUTTON__PURGE)
                    ->set_visible(false, C__NAVBAR_BUTTON__RECYCLE)
                    ->set_visible(true, C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_active($l_archive_right || $l_delete_right || $l_supervisor_right, C__NAVBAR_BUTTON__ARCHIVE);
                break;

            case C__RECORD_STATUS__ARCHIVED:
                $navbar->set_visible(true, C__NAVBAR_BUTTON__DELETE)
                    ->set_visible(false, C__NAVBAR_BUTTON__PURGE)
                    ->set_active($l_delete_right, C__NAVBAR_BUTTON__DELETE);
                break;

            case C__RECORD_STATUS__DELETED:
                $navbar->set_visible(true, C__NAVBAR_BUTTON__PURGE)
                    ->set_active($l_supervisor_right, C__NAVBAR_BUTTON__PURGE);
                break;
        }

        if ($_SESSION["cRecStatusListView"] != C__RECORD_STATUS__DELETED && isys_tenantsettings::get('cmdb.quickpurge') == '1') {
            $navbar->set_visible($l_supervisor_right, C__NAVBAR_BUTTON__QUICK_PURGE)
                ->set_active($l_supervisor_right, C__NAVBAR_BUTTON__QUICK_PURGE);
        }

        // Display the "recycle" button.
        $l_recycle_btn_active = ($l_delete_right && $_SESSION['cRecStatusListView'] > C__RECORD_STATUS__NORMAL) ||
            ($l_archive_right && $_SESSION['cRecStatusListView'] == C__RECORD_STATUS__ARCHIVED);
        $navbar->set_visible($l_recycle_btn_active, C__NAVBAR_BUTTON__RECYCLE)
            ->set_active($l_recycle_btn_active, C__NAVBAR_BUTTON__RECYCLE);

        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
    }
}