<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.5.2
 */
class isys_ajax_handler_software extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [
            'success' => true,
            'data'    => null,
            'message' => null
        ];

        try {
            switch ($_GET['func']) {
                case 'get_variants':
                    $l_return['data'] = $this->get_variants($_POST[C__CMDB__GET__OBJECT]);
                    break;

                case 'get_type':
                    $l_return['data'] = $this->get_type($_POST[C__CMDB__GET__OBJECT]);
                    break;
            }
        } catch (Exception $e) {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        }

        echo isys_format_json::encode($l_return);
        $this->_die();
    }

    /**
     * Method for retrieving all variants of the given software object.
     *
     * @param   integer $p_software_obj_id
     *
     * @return  array
     * @throws  isys_exception_general
     */
    protected function get_variants($p_software_obj_id)
    {
        return isys_cmdb_dao_category_s_application_assigned_obj::instance($this->m_database_component)
            ->get_variants($p_software_obj_id);
    }

    /**
     * Method for retrieving all variants of the given software object.
     *
     * @param   integer $p_software_obj_id
     *
     * @return  array
     * @throws  isys_exception_general
     */
    protected function get_type($p_software_obj_id)
    {
        /* @var  isys_cmdb_dao_category_g_application $l_dao */
        $l_dao = isys_cmdb_dao_category_g_application::instance($this->m_database_component);
        $l_object_type = (int)$l_dao->get_object($p_software_obj_id, false, 1)
            ->get_row_value('isys_obj_type__id');

        return $l_dao->callback_property_application_type(isys_request::factory()
            ->set_row([
                'isys_obj__id'      => $p_software_obj_id,
                'isys_obj_type__id' => $l_object_type
            ]));
    }
}