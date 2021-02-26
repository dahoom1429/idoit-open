<?php

/**
 * i-doit
 *
 * DAO: Specific Router list.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_router extends isys_component_dao_category_table_list
{
    /**
     * Method for retrieving the category ID.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__ROUTER');
    }

    /**
     * Method for retrieving the category-type.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     * Method for modifying the single rows for displaying links or getting translations.
     *
     * @param   array & $p_row
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function modify_row(&$p_row)
    {
        // Set default values.
        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');
        $l_routing_protocol = $l_empty_value;
        $l_gateway_address = $l_empty_value;

        // Get the selected routing protocol name from the database.
        if ($p_row['isys_cats_router_list__routing_protocol'] > 0) {
            $l_sql = 'SELECT isys_routing_protocol__title
				FROM isys_routing_protocol
				WHERE isys_routing_protocol__id = ' . (int)$p_row['isys_cats_router_list__routing_protocol'] . '
				LIMIT 1;';

            $l_query = $this->retrieve($l_sql);

            if ($l_row = $l_query->get_row()) {
                // If it's available, set it.
                $l_routing_protocol = $l_row['isys_routing_protocol__title'];
            }
        }

        // Get the gateway address name from the DAO.
        $l_gateway_dao = new isys_cmdb_dao_category_g_ip($this->get_database_component());
        $l_net_dao = new isys_cmdb_dao_category_s_net($this->get_database_component());

        // Let us retrieve the right isys_catg_ip_list__ids.
        $l_sql = "SELECT isys_catg_ip_list__id
            FROM isys_catg_ip_list_2_isys_cats_router_list
            WHERE isys_cats_router_list__id = " . $l_gateway_dao->convert_sql_id($p_row['isys_cats_router_list__id']) . ";";

        $l_resGateWayAddresses = $l_gateway_dao->retrieve($l_sql);

        if (is_countable($l_resGateWayAddresses) && count($l_resGateWayAddresses)) {
            $l_ipIDs = [];

            while ($l_tmpRow = $l_resGateWayAddresses->get_row()) {
                $l_ipIDs[] = $l_tmpRow['isys_catg_ip_list__id'];
            }

            $l_gateway_result = $l_gateway_dao->get_data(null, $p_row['isys_obj__id'], " AND isys_catg_ip_list__id IN(" . implode(',', $l_ipIDs) . ")");
            $l_gateway_address = $l_already_added = [];

            while ($l_gateway_row = $l_gateway_result->get_row()) {
                if (!isset($l_already_added[$l_gateway_row['isys_cats_net_ip_addresses_list__isys_obj__id']])) {
                    // Netz-Namen selektieren.
                    $l_net_data = $l_net_dao->get_data_by_object($l_gateway_row['isys_cats_net_ip_addresses_list__isys_obj__id'])
                        ->get_row();

                    // Define a nice value to display in our list.
                    $l_address = $l_gateway_row['isys_catg_ip_list__hostname'] . ' (' . $l_net_data['isys_cats_net_list__address'] . ') - ' . $l_net_data['isys_obj__title'];

                    $l_gateway_address[] = (empty($l_address)) ? isys_application::instance()->container->get('language')
                        ->get('LC__IP__EMPTY_ADDRESS') : $l_address;

                    $l_already_added[$l_gateway_row['isys_cats_net_ip_addresses_list__isys_obj__id']] = true;
                }
            }
        }

        // Finally, assign our values.
        $p_row['isys_cats_router_list__routing_protocol'] = $l_routing_protocol;
        $p_row['isys_cats_router_list__isys_catg_ip_list__title'] = $l_gateway_address;
    }

    /**
     * Method for retrieving the fields to display in the list-view.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_fields()
    {
        return [
            'isys_cats_router_list__id'                       => 'ID',
            'isys_cats_router_list__routing_protocol'         => 'LC__CMDB__CATS__ROUTER__ROUTING_PROTOCOL',
            'isys_cats_router_list__isys_catg_ip_list__title' => 'LC__CMDB__CATS__ROUTER__GATEWAY_ADDRESS'
        ];
    }
}
