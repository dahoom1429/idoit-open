<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_change_object_type extends isys_ajax_handler
{
    /**
     * Ajax initializer.
     *
     * @throws  Exception
     */
    public function init()
    {
        global $g_dirs;

        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_object_ids = explode(',', $_POST[C__CMDB__GET__OBJECT]);
        $l_object_type_id = (int)$_POST[C__CMDB__GET__OBJECTTYPE];
        $l_object_names = [];
        $l_return = [
            'success' => true,
            'data'    => null,
            'message' => null
        ];

        try {
            if (count($l_object_ids) && $l_object_type_id > 0) {
                $l_dao = isys_cmdb_dao::instance(isys_application::instance()->database);

                $l_otype = $l_dao->get_object_types($l_object_type_id);

                if ($l_otype->count() > 0) {
                    $l_object_ids = explode(',', $_POST[C__CMDB__GET__OBJECT]);

                    foreach ($l_object_ids as $l_object_id) {
                        $l_sql = "UPDATE isys_obj
                        SET isys_obj__isys_obj_type__id = " . $l_dao->convert_sql_id($l_object_type_id) . "
                        WHERE isys_obj__id = " . $l_dao->convert_sql_id($l_object_id) . ";";

                        if ($l_dao->update($l_sql) && $l_dao->apply_update()) {
                            $l_dao->object_changed($l_object_id);

                            $l_object_names[] = '<strong>' . $l_dao->get_obj_name_by_id_as_string($l_object_id) . '</strong>';
                        }
                    }

                    $l_return['data'] = '<img src="' . $g_dirs["images"] . '/icons/infobox/blue.png" class="mr5"><span>' .
                        sprintf(isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__OBJECT_MOVED'), isys_helper_textformat::this_this_and_that($l_object_names),
                            isys_application::instance()->container->get('language')
                                ->get($l_otype->get_row_value('isys_obj_type__title'))) . '</span>';
                } else {
                    throw new isys_exception_general('Error while changing object-type. Object-type ID #' . $l_object_type_id . ' could not be found.');
                }
            }
        } catch (Exception $e) {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        }

        echo isys_format_json::encode($l_return);
        $this->_die();
    }
}
