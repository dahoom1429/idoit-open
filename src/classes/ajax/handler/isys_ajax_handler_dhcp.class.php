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
class isys_ajax_handler_dhcp extends isys_ajax_handler
{
    /**
     * In this array we save the types - according to the ID's from the template and the values from "isys_net_dhcp_type".
     *
     * @var  array
     */
    protected $m_types = [];

    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        if (isset($this->m_types[$this->m_post['type']])) {
            $l_return = $this->create_dhcp_range($this->m_get[C__CMDB__GET__OBJECT], $this->m_post['from'], $this->m_post['to']);
        } else {
            // We assume we got "new-static-area" as type.
            $l_return = $this->create_static_range($this->m_get[C__CMDB__GET__OBJECT], $this->m_post['from'], $this->m_post['to']);
        }

        // After we have done our saving/merging/appending, we get the complete list for rendering the table new.
        $l_dhcp_dao = new isys_cmdb_dao_category_s_net_dhcp($this->m_database_component);
        $l_dhcp_res = $l_dhcp_dao->get_data(null, $this->m_get[C__CMDB__GET__OBJECT], '', null, C__RECORD_STATUS__NORMAL);

        while ($l_dhcp_row = $l_dhcp_res->get_row()) {
            $l_return['result_data'][] = [
                'from' => $l_dhcp_row['isys_cats_net_dhcp_list__range_from_long'],
                'to'   => $l_dhcp_row['isys_cats_net_dhcp_list__range_to_long'],
                'type' => $l_dhcp_row['isys_cats_net_dhcp_list__isys_net_dhcp_type__id']
            ];
        }

        // Echo our results and end the request.
        echo isys_format_json::encode($l_return);
        $this->_die();
    }

    /**
     * Method for creating a new dynamic/reserved DHCP ranges.
     *
     * @param   integer $p_obj_id
     * @param   string  $p_from
     * @param   string  $p_to
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function create_dhcp_range($p_obj_id, $p_from, $p_to)
    {
        $l_dhcp_dao = new isys_cmdb_dao_category_s_net_dhcp($this->m_database_component);

        return $l_dhcp_dao->check_and_merge_new_dhcp_range_inside_existing($p_obj_id, $this->m_types[$this->m_post['type']], $p_from, $p_to);
    }

    /**
     * Method for creating a new static range. Basically we just delete the DHCP addresses and relations here.
     *
     * @param   integer $p_obj_id
     * @param   string  $p_from
     * @param   string  $p_to
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function create_static_range($p_obj_id, $p_from, $p_to)
    {
        // At first we set the ip assignments to the new range-type.
        $l_g_ip = new isys_cmdb_dao_category_g_ip($this->m_database_component);
        $l_g_ip->update_ip_assignment_by_ip_range($p_obj_id, defined_or_default('C__CATP__IP__ASSIGN__STATIC'), $p_from, $p_to);

        $l_dhcp_dao = new isys_cmdb_dao_category_s_net_dhcp($this->m_database_component);

        // With this method we check, if there will be any conflicts.
        $l_conflict_ids = $l_dhcp_dao->find_range_conflicts($p_obj_id, $p_from, $p_to);

        // Now we check, if the method retrieved any conflicted ID's for us.
        if ($l_conflict_ids->num_rows() > 0) {
            while ($l_row = $l_conflict_ids->get_row()) {
                // We save all entries.
                $l_dhcp_ranges[] = $l_row;

                // And delete them - So now we can start from scratch.
                $l_dhcp_dao->delete($l_row['isys_cats_net_dhcp_list__id']);
            }

            // We set the new FROM and TO values as default, but check this in the next IF-statements.
            $l_new_range_from = $p_from;
            $l_new_range_to = $p_to;

            // We get ourselves the first and last entry of the array.
            $l_first = current($l_dhcp_ranges);
            $l_last = end($l_dhcp_ranges);

            // Please note: We can only check this because it was ordered by the FROM range.
            if ($l_first['isys_cats_net_dhcp_list__range_from_long'] < Ip::ip2long($l_new_range_from)) {
                // We create the new start-range.
                $l_dhcp_dao->create($p_obj_id, $l_first['isys_cats_net_dhcp_list__isys_net_dhcp_type__id'], null, $l_first['isys_cats_net_dhcp_list__range_from'],
                    Ip::long2ip(Ip::ip2long($l_new_range_from) - 1), $l_first['isys_cats_net_dhcp_list__description'], $l_first['isys_cats_net_dhcp_list__status']);
            }

            // In the last entry there has to be the most last range.
            if ($l_last['isys_cats_net_dhcp_list__range_to_long'] > Ip::ip2long($l_new_range_to)) {
                // And of course we create the new end-range.
                $l_dhcp_dao->create($p_obj_id, $l_last['isys_cats_net_dhcp_list__isys_net_dhcp_type__id'], null, Ip::long2ip(Ip::ip2long($l_new_range_to) + 1),
                    $l_last['isys_cats_net_dhcp_list__range_to'], $l_last['isys_cats_net_dhcp_list__description'], $l_last['isys_cats_net_dhcp_list__status']);
            }

            // Now - Instead of creating the middle peace, we do nothing.
            $l_return = ['result' => 'merged'];
        } else {
            // When the array is empty, we do nothing at all.
            $l_return = ['result' => 'success'];
        }

        return $l_return;
    }

    /**
     * isys_ajax_handler_dhcp constructor.
     *
     * @param array $p_get
     * @param array $p_post
     */
    public function __construct(array $p_get, array $p_post)
    {
        $this->m_types = filter_array_by_value_of_defined_constants([
            'new-reserved-dhcp-area' => 'C__NET__DHCP_RESERVED',
            'new-dhcp-area'          => 'C__NET__DHCP_DYNAMIC',
        ]);
        parent::__construct($p_get, $p_post);
    }
}