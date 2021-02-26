<?php

use idoit\Component\Helper\Ip;

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
class isys_ajax_handler_ipv6 extends isys_ajax_handler
{
    /**
     * This variable will hold the host-address category DAO.
     *
     * @var  isys_cmdb_dao_category_g_ip
     */
    protected $m_ip_dao = null;

    /**
     * Init method, which gets called from the framework.
     *
     * @global  isys_component_database $g_comp_database
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        global $g_comp_database;

        // We need the catg_ip DAO for a few awesome IPv6 methods.
        $this->m_ip_dao = new isys_cmdb_dao_category_g_ip($g_comp_database);

        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        if (!empty($_POST['method'])) {
            switch ($_POST['method']) {
                case 'calculate_ipv6_range':
                    echo isys_format_json::encode($this->calculate_ipv6_range());
                    break;

                case 'find_free_v6':
                    echo isys_format_json::encode($this->find_free_v6());
                    break;

                case 'is_ipv6_inside_range':
                    echo isys_format_json::encode($this->is_ipv6_inside_range());
                    break;

                case 'find_ipv6_matching_layer3net':
                    echo isys_format_json::encode($this->find_ipv6_matching_layer3net());
                    break;

                default:
                    echo isys_format_json::encode([]);
            }
        }

        $this->_die();
    }

    /**
     * Method for calculating an IPv6 range by an IPv6 IP and a CIDR.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function calculate_ipv6_range()
    {
        return Ip::calc_ip_range_ipv6($_POST['ip'], $_POST['cidr']);
    }

    /**
     * Method for retrieving relevant information for a certain net.
     *
     * @global  isys_component_database $g_comp_database
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function find_free_v6()
    {
        global $g_comp_database;

        $l_net_dao = new isys_cmdb_dao_category_s_net($g_comp_database);

        return $l_net_dao->find_free_ipv6_by_assignment($_POST['net_obj_id'], $_POST['ip_assignment']);
    }

    /**
     * Method for finding out if an IPv6 address lies inside a given range.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function is_ipv6_inside_range()
    {
        return Ip::is_ipv6_in_range($_POST['address'], $_POST['net_from'], $_POST['net_to']);
    }

    public function find_ipv6_matching_layer3net()
    {
        $lang = isys_application::instance()->container->get('language');
        $addressIpv6String = $_POST['address'];
        global $g_comp_database;
        $dao = isys_cmdb_dao_category_g_ip::instance($g_comp_database);
        $sql = "SELECT net.isys_cats_net_list__isys_obj__id AS `net_id`,
                net.isys_cats_net_list__address_range_from AS `from`,
                net.isys_cats_net_list__address_range_to AS `to`,
                obj.isys_obj__title as title
                FROM isys_cats_net_list as net
                INNER JOIN isys_obj as obj ON obj.isys_obj__id = net.isys_cats_net_list__isys_obj__id 
                WHERE net.isys_cats_net_list__isys_net_type__id = " . $dao->convert_sql_int(defined_or_default('C__CATS_NET_TYPE__IPV6')) .
                " AND (obj.isys_obj__const != " . $dao->convert_sql_text('C__OBJ__NET_GLOBAL_IPV6') .
                " OR obj.isys_obj__const IS NULL) 
                  AND net.isys_cats_net_list__status = " . $dao->convert_sql_int(C__RECORD_STATUS__NORMAL);
        $result = $dao->retrieve($sql);
        $matchingNet = null;
        while ($row = $result->get_row()) {
            $subnet_range_longs = [
                'from' => Ip::parseHexIpv6($row['from']),
                'to' => Ip::parseHexIpv6($row['to']),
            ];
            if (!Ip::checkIPv6InRange($addressIpv6String, $subnet_range_longs)) {
                continue;
            }
            $matchingNet = [
                'id' => $row['net_id'],
                'title' => $row['title'],
                'obj_type' => $lang->get($dao->get_obj_type_name_by_obj_id($row['net_id'])),
            ];
            break;
        }
        if (!$matchingNet) {
            $sql = "SELECT isys_obj__id AS `net_id`, isys_obj__title as title
                FROM isys_obj as obj
                WHERE obj.isys_obj__const = " . $dao->convert_sql_text('C__OBJ__NET_GLOBAL_IPV6');
            $row = $dao->retrieve($sql)->get_row();
            $matchingNet = [
                'id' => $row['net_id'],
                'title' => $row['title'],
                'obj_type' => $lang->get($dao->get_obj_type_name_by_obj_id($row['net_id'])),
            ];
        }
        return $matchingNet;
    }
}
