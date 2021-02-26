<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_update_person_group_member_tag extends isys_ajax_handler
{
    /**
     * Init method, which holds the necessary logic.
     */
    public function init()
    {
        global $g_dirs;

        try {
            isys_auth_cmdb::instance()->check_rights_obj_and_category(isys_auth::EDIT, $this->m_get[C__CMDB__GET__OBJECT], 'C__CATS__PERSON_GROUP_MEMBERS');
            isys_cmdb_dao_category_s_person_group_members::instance($this->m_database_component)->setPersonGroupMemberRole($this->m_post["conId"], $this->m_post["valId"]);

            echo '<img style="margin: 2px 0 0 3px;" src="' . $g_dirs["images"] . 'icons/infobox/blue.png" height="16"> <span>' .
                isys_application::instance()->container->get('language')->get('LC__CONTACT__TREE__MEMBER_HAS_BEEN_UPDATED') . '</span>';
        } catch (isys_exception_auth $e) {
            echo '<img style="margin: 2px 0 0 3px;" src="' . $g_dirs["images"] . 'icons/infoicon/error.png" height="16"> <span>' . $e->getMessage() . '</span>';
        }

        die;
    }

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     */
    public static function needs_hypergate()
    {
        return true;
    }
}
