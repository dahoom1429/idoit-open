<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_update_guest_system_primary extends isys_ajax_handler
{
    /**
     * Init method, which holds the necessary logic.
     */
    public function init()
    {
        global $g_dirs;

        try {
            isys_auth_cmdb::instance()
                ->check_rights_obj_and_category(isys_auth::EDIT, $this->m_post['objId'], 'C__CATG__GUEST_SYSTEMS');

            $l_dao = isys_cmdb_dao::instance($this->m_database_component);

            $l_query = "UPDATE isys_catg_virtual_machine_list
				SET isys_catg_virtual_machine_list__primary = " . $l_dao->convert_sql_id($this->m_post["valId"]) . "
				WHERE isys_catg_virtual_machine_list__id = " . $l_dao->convert_sql_id($this->m_post["conId"]) . ";";

            if ($l_dao->update($l_query) && $l_dao->apply_update()) {
                echo '<img style="margin: 2px 0 0 3px;" src="' . $g_dirs["images"] . 'icons/infobox/blue.png" height="16"> <span>' .
                    isys_application::instance()->container->get('language')
                        ->get('LC__CATG__GUEST_SYSTEM_HAS_BEEN_UPDATED') . '</span>';
            }
        } catch (isys_exception_auth $e) {
            echo '<img style="margin: 2px 0 0 3px;" src="' . $g_dirs["images"] . 'icons/infoicon/error.png" height="16"> <span>' . $e->getMessage() . '</span>';
        }

        die;
    }
}
