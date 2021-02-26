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
 * @since       1.5.0
 */
class isys_ajax_handler_sla extends isys_ajax_handler
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
                case 'get-service-level-description':
                    $l_return['data'] = $this->get_service_level_description($_POST['service_level']);
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
     * This method returns the description of the given service level ID.
     *
     * @param   integer $p_service_level_id
     *
     * @return  array
     */
    public function get_service_level_description($p_service_level_id)
    {
        return isys_factory_cmdb_dialog_dao::get_instance('isys_sla_service_level', $this->m_database_component)
            ->get_data($p_service_level_id);
    }
}