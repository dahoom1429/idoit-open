<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @author      Van Quyen Hoang <qhoang@i-doit.de>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_calc_ip_address extends isys_ajax_handler
{
    /**
     * @var  isys_cmdb_dao_category_g_ip
     */
    private $m_ip_address_dao;

    /**
     * Init method for this AJAX request.
     *
     * @global  isys_component_database $g_comp_database
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [];

        $this->m_ip_address_dao = isys_cmdb_dao_category_g_ip::instance($this->m_database_component);

        switch ($_GET['func']) {
            case 'find_free_v4':
                $l_return = $this->find_free_v4((int)$_POST['net_obj_id'], (int)$_POST['ip_assignment'], (int)$_POST['zone']);
                break;

            case 'is_free_v4':
                $l_return = $this->is_free_v4((int)$_POST['net_obj_id'], $_POST['ip'], $_POST[C__CMDB__GET__OBJECT]);
                break;
        }

        echo isys_format_json::encode($l_return);

        $this->_die();
    }

    /**
     * Method for finding a free IPv4 address.
     *
     * @param   integer $p_net_obj_id
     * @param   integer $p_ip_assignment
     * @param   integer $p_zone_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function find_free_v4($p_net_obj_id, $p_ip_assignment, $p_zone_obj_id)
    {
        if ($p_net_obj_id > 0 && ($p_ip_assignment > 0 || $p_zone_obj_id > 0)) {
            if (!($p_ip_assignment > 0)) {
                $p_ip_assignment = null;
            }

            if (!($p_zone_obj_id >= 0)) {
                $p_zone_obj_id = null;
            }

            return [
                'success' => true,
                'data'    => $this->m_ip_address_dao->get_free_ip($p_net_obj_id, $p_ip_assignment, $p_zone_obj_id)
            ];
        }

        return [
            'success' => false
        ];
    }

    /**
     * Method returns true/false if a given IP-address is free or not.
     *
     * @param   integer $p_net_obj_id
     * @param   string  $p_ip
     * @param   integer $p_excludeObjID
     *
     * @return  array
     * @throws  Exception
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function is_free_v4($p_net_obj_id, $p_ip, $p_excludeObjID)
    {
        return [
            'success' => !$this->m_ip_address_dao->ip_already_in_use($p_net_obj_id, $p_ip, $p_excludeObjID),
            'net'     => (($p_net_obj_id == defined_or_default('C__OBJ__NET_GLOBAL_IPV4')) ? isys_cmdb_dao_category_s_net::instance($this->m_database_component)
                ->get_matching_net_by_ipv4_address($p_ip) : null)
        ];
    }
}