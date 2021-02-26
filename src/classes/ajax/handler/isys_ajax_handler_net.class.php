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
 * @since       0.9.9-8
 */
class isys_ajax_handler_net extends isys_ajax_handler
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

        $l_return = [];

        if (isset($_GET['m'])) {
            switch ($_GET['m']) {
                case 'check_net_collision':
                    $l_obj_id = (isset($_POST['obj_id'])) ? $_POST['obj_id'] : null;
                    $l_net_type = (isset($_POST['net_type'])) ? $_POST['net_type'] : defined_or_default('C__CATS_NET_TYPE__IPV4');

                    $l_return = $this->check_net_collision($_POST['from'], $_POST['to'], $l_net_type, $l_obj_id);
                    break;
            }
        } else {
            // We need the catg_ip DAO for a few awesome IPv6 methods.
            $l_return = isys_cmdb_dao_category_s_net::instance($this->m_database_component)
                ->get_all_net_information_by_obj_id($_POST['id']);
        }

        echo isys_format_json::encode($l_return);

        $this->_die();
    }

    /**
     * Method for retrieving all nets, which collide with the given IP range.
     *
     * @param   string  $p_from
     * @param   string  $p_to
     * @param   integer $p_net_type
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    private function check_net_collision($p_from, $p_to, $p_net_type, $p_obj_id)
    {
        try {
            $l_data = null;
            $l_result = isys_cmdb_dao_category_s_net::instance($this->m_database_component)
                ->find_net_collision($p_from, $p_to, $p_net_type, $p_obj_id);

            if (is_countable($l_result) && count($l_result) > 0) {
                $l_quickinfo = new isys_ajax_handler_quick_info();
                $l_data = [];

                while ($l_row = $l_result->get_row()) {
                    $l_data[] = $l_quickinfo->get_quick_info($l_row['isys_obj__id'], isys_application::instance()->container->get('language')
                            ->get($l_row['isys_obj_type__title']) . ' &raquo; ' . $l_row['isys_obj__title'], C__LINK__OBJECT);
                }
            }

            $l_return = [
                'success' => true,
                'message' => '',
                'data'    => $l_data
            ];
        } catch (Exception $e) {
            $l_return = [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        return $l_return;
    }
}

?>
