<?php

/**
 * i-doit
 *
 * CMDB UI: Global category (category type is global)
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @author      Leonard Fischer <lfischer@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_contact extends isys_cmdb_ui_category_global
{
    /**
     * Only needed for the overview
     *
     * @param isys_cmdb_dao_category $p_cat
     *
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];
        $l_rules['C__CMDB__CATG__CONTACT__CONNECTED_OBJECT']['multiselection'] = true;
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        return $this->process_list($p_cat);
    }

    /**
     * Show the list-template for subcategories of contact.
     *
     * @param   isys_cmdb_dao_category &$p_cat
     * @param   null                   $p_get_param_override
     * @param   null                   $p_strVarName
     * @param   null                   $p_strTemplateName
     * @param   boolean                $p_bCheckbox
     * @param   boolean                $p_bOrderLink
     * @param   null                   $p_db_field_name
     *
     * @return  null
     * @author  Leonard Fischer <lfischer@synetics.de>
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
        // @see ID-4894

        $this->object_browser_as_new([
            'name'                                               => 'C__CMDB__CATG__CONTACT__CONNECTED_OBJECT',
            isys_popup_browser_object_ng::C__MULTISELECTION      => true,
            isys_popup_browser_object_ng::C__FORM_SUBMIT         => true,
            isys_popup_browser_object_ng::C__CAT_FILTER          => 'C__CATS__PERSON;C__CATS__PERSON_GROUP;C__CATS__ORGANIZATION',
            isys_popup_browser_object_ng::C__RETURN_ELEMENT      => C__POST__POPUP_RECEIVER,
            isys_popup_browser_object_ng::C__OBJECT_BROWSER__TAB => [
                isys_popup_browser_object_ng::C__OBJECT_BROWSER__TAB__LOCATION => false
            ]
        ], "LC__UNIVERSAL__BUTTON_ADD", "LC__UNIVERSAL__OBJECT_ADD_DESCRIPTION");

        $l_navbar = isys_component_template_navbar::getInstance();
        $l_archive_right = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::ARCHIVE, isys_glob_get_param(C__CMDB__GET__OBJECT), 'C__CATG__CONTACT');
        $l_delete_right = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::DELETE, isys_glob_get_param(C__CMDB__GET__OBJECT), 'C__CATG__CONTACT');
        $l_supervisor_right = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::SUPERVISOR, isys_glob_get_param(C__CMDB__GET__OBJECT), 'C__CATG__CONTACT');

        switch ($_SESSION['cRecStatusListView']) {
            case C__RECORD_STATUS__NORMAL:
                $l_navbar->set_visible(true, C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_active($l_archive_right || $l_delete_right || $l_supervisor_right, C__NAVBAR_BUTTON__ARCHIVE);
                break;

            case C__RECORD_STATUS__ARCHIVED:
                $l_navbar->set_visible(true, C__NAVBAR_BUTTON__DELETE)
                    ->set_active($l_delete_right, C__NAVBAR_BUTTON__DELETE);
                break;

            case C__RECORD_STATUS__DELETED:
                $l_navbar->set_visible(true, C__NAVBAR_BUTTON__PURGE)
                    ->set_active($l_supervisor_right, C__NAVBAR_BUTTON__PURGE);
                break;
        }

        // Display the "recycle" button.
        $l_navbar->set_visible(true, C__NAVBAR_BUTTON__RECYCLE)
            ->set_active(($l_delete_right && $_SESSION['cRecStatusListView'] > C__RECORD_STATUS__NORMAL) ||
                ($l_archive_right && $_SESSION['cRecStatusListView'] == C__RECORD_STATUS__ARCHIVED), C__NAVBAR_BUTTON__RECYCLE);

        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
    }

    /**
     * Constructor.
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        $this->set_template("catg__contact.tpl");
        parent::__construct($p_template);
    }
}
