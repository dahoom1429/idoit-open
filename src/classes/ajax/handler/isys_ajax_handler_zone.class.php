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
 * @since       1.8.1
 */
class isys_ajax_handler_zone extends isys_ajax_handler
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
            'message' => ''
        ];

        try {
            switch ($this->m_get['func']) {
                case 'create_zone_range':
                    $l_return['data'] = $this->create_zone_range($this->m_get[C__CMDB__GET__OBJECT], substr($this->m_post['type'], 9), $this->m_post['from'],
                        $this->m_post['to']);
                    break;

                case 'retrieve_zone_ranges':
                    $l_return['data'] = $this->retrieve_zone_ranges($this->m_post[C__CMDB__GET__OBJECT]);
                    break;
            }
        } catch (Exception $e) {
            $l_return = [
                'success' => false,
                'data'    => null,
                'message' => $e->getMessage()
            ];
        }

        // Echo our results and end the request.
        echo isys_format_json::encode($l_return);

        // End the request.
        $this->_die();
    }

    /**
     * Method for creating a new zone within a layer 3 net.
     *
     * @param integer $p_net_object
     * @param integer $p_type
     * @param string  $p_from
     * @param string  $p_to
     *
     * @return array
     */
    protected function create_zone_range($p_net_object, $p_type, $p_from, $p_to)
    {
        $l_return = [
            'zone' => isys_cmdb_dao_category_s_net_zone::instance($this->m_database_component)
                ->insert_zone_in_net($p_net_object, $p_type, $p_from, $p_to)
        ];

        // After we have done our saving/merging/appending, we get the complete list for rendering the table new.
        $l_return['zones'] = $this->retrieve_zone_ranges($p_net_object);

        return $l_return;
    }

    /**
     * Method for retrieving all zone ranges from a given layer 3 net.
     *
     * @param integer $p_net_object
     *
     * @return array
     */
    protected function retrieve_zone_ranges($p_net_object)
    {
        $l_return = [];

        $l_zone_options_dao = isys_cmdb_dao_category_g_net_zone_options::instance(isys_application::instance()->database);
        $l_zone_res = isys_cmdb_dao_category_s_net_zone::instance($this->m_database_component)
            ->get_data(null, $p_net_object, '', null, C__RECORD_STATUS__NORMAL);

        while ($l_zone_row = $l_zone_res->get_row()) {
            $l_zone_options = $l_zone_options_dao->get_data(null, $l_zone_row['isys_cats_net_zone_list__isys_obj__id__zone'])
                ->get_row();

            $l_return[] = [
                'from'    => $l_zone_row['isys_cats_net_zone_list__range_from_long'],
                'to'      => $l_zone_row['isys_cats_net_zone_list__range_to_long'],
                'from_ip' => $l_zone_row['isys_cats_net_zone_list__range_from'],
                'to_ip'   => $l_zone_row['isys_cats_net_zone_list__range_to'],
                'color'   => $l_zone_options['isys_catg_net_zone_options_list__color'] ?: '#ffffff',
                'domain'  => $l_zone_options['isys_catg_net_zone_options_list__domain'] ?: '',
                'name'    => $l_zone_options['isys_obj__title'] ?: $l_zone_options_dao->obj_get_title_by_id_as_string($l_zone_row['isys_cats_net_zone_list__isys_obj__id__zone']),
                'id'      => $l_zone_row['isys_cats_net_zone_list__isys_obj__id__zone']
            ];
        }

        return $l_return;
    }
}