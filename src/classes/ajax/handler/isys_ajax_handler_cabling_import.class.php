<?php

/**
 * AJAX Handler for Cabling import
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_cabling_import extends isys_ajax_handler
{
    /**
     * Initialize method.
     */
    public function init()
    {
        global $g_comp_database;

        if (isset($_POST['func'])) {
            $l_function = $_POST['func'];

            switch ($l_function) {
                case 'check_object':
                    $l_dao = isys_cmdb_dao::instance($g_comp_database);

                    $l_obj_id = $l_dao->get_obj_id_by_title($_POST['title'], $l_dao->get_object_types_by_category(defined_or_default('C__CATG__CABLING'), 'g', false, false),
                        C__RECORD_STATUS__NORMAL);

                    if ($l_obj_id > 0) {
                        echo $l_obj_id;
                    } else {
                        echo false;
                    }
                    break;

                default:
                    break;
            }

            die;
        }
    }
}