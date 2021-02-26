<?php

/**
 * i-doit
 *
 * CMDB Active Directory: Specific category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_router extends isys_cmdb_ui_category_specific
{
    /**
     * Show the detail-template for specific category router.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata = $p_cat->get_general_data();

        // Make the rules, like a boss.
        $l_rules['C__CATS__ROUTER__ROUTING_PROTOCOL']['p_strSelectedID'] = $l_catdata['isys_cats_router_list__routing_protocol'];
        $l_rules['C__CMDB__CAT__COMMENTARY_' . $p_cat->get_category_type() . $p_cat->get_category_id()]['p_strValue'] = $l_catdata['isys_cats_router_list__description'];

        // Prepare the gateways.
        $l_gateway = [];
        $l_gateway_dao = new isys_cmdb_dao_category_g_ip($this->m_database_component);
        $l_net_dao = new isys_cmdb_dao_category_s_net($this->m_database_component);

        $l_ports = [];
        $catId = (int)$_GET[C__CMDB__GET__CATLEVEL];
        $objId = (int)$_GET[C__CMDB__GET__OBJECT];

        if ($catId > 0) {
            $l_port_result = $l_gateway_dao->get_ips_for_router_list_by_obj_id($l_catdata['isys_cats_router_list__isys_obj__id'], $catId);

            while ($l_port_row = $l_port_result->get_row()) {
                $l_ports[] = $l_port_row['isys_catg_ip_list__id'];
            }
        }

        if ($l_catdata === null) {
            $l_catdata = ['isys_cats_router_list__isys_obj__id' => $objId];
        }

        $l_gateway_result = $l_gateway_dao->get_data_by_object($l_catdata['isys_cats_router_list__isys_obj__id']);
        $l_already_added = [];

        while ($l_gateway_row = $l_gateway_result->get_row()) {
            if (!isset($l_already_added[$l_gateway_row['isys_cats_net_ip_addresses_list__isys_obj__id']])) {
                // Netz-Namen selektieren.
                $l_net_data = $l_net_dao->get_data_by_object($l_gateway_row['isys_cats_net_ip_addresses_list__isys_obj__id'])
                    ->get_row();

                // Define a nice value to display in our list.
                $l_address = $l_gateway_row['isys_catg_ip_list__hostname'] . ' (' . $l_net_data['isys_cats_net_list__address'] . ') - ' . $l_net_data['isys_obj__title'];

                $l_gateway[] = [
                    'id'   => $l_gateway_row['isys_catg_ip_list__id'],
                    'val'  => (empty($l_address)) ? isys_application::instance()->container->get('language')
                        ->get('LC__IP__EMPTY_ADDRESS') : $l_address,
                    'sel'  => in_array($l_gateway_row['isys_catg_ip_list__id'], $l_ports),
                    'link' => ''
                ];

                $l_already_added[$l_gateway_row['isys_cats_net_ip_addresses_list__isys_obj__id']] = true;
            }
        }

        // Assign the gateway adress names.
        $l_rules['C__CATS__ROUTER__GATEWAY_ADDRESS']['p_arData'] = $l_gateway;
        $l_rules['C__CATS__ROUTER__GATEWAY_ADDRESS']['p_strSelectedID'] = $l_catdata['isys_cats_router_list__isys_catg_ip_list__id'];

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}
