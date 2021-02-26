<?php

/**
 * i-doit
 *
 * DAO: specific category list for QinQ
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_qinq extends isys_component_dao_category_table_list
{

    /**
     * Return constant of category
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__QINQ_CE');
    }

    /**
     * Return constant of category type
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Modify row data
     *
     * @param array $p_row
     *
     * @return void
     */
    public function modify_row(&$p_row)
    {
        // Retrieve Layer2 DAO
        $l_layer2_dao = isys_factory_cmdb_category_dao::get_instance_by_id(C__CMDB__CATEGORY__TYPE_SPECIFIC, defined_or_default('C__CATS__LAYER2_NET'), $this->m_db);

        // Check for data
        $l_res = $l_layer2_dao->get_data(null, $p_row['isys_obj__id']);

        if ($l_res->count()) {
            // Get row
            $l_row = $l_res->get_row();

            // Set additional data
            $p_row['vlan_id'] = $l_row['isys_cats_layer2_net_list__ident'];
            $p_row['standard_vlan'] = $l_row['isys_cats_layer2_net_list__standard'] == 1 ? '<span class="green">' . isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__YES') . '</span>' : '<span class="red">' . isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__NO') . '</span>';
            $p_row['type'] = $l_row['isys_layer2_net_type__title'];
            $p_row['sub_type'] = $l_row['isys_layer2_net_subtype__title'];
        }
    }

    /**
     * Gets fields to display in the list view.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_catg_qinq_list__id' => 'ID',
            'isys_obj__title'         => 'LC__CMDB__OBJTYPE__LAYER2_NET',
            'vlan_id'                 => 'LC__CMDB__CATG__VSWITCH__VLAN_ID',
            'standard_vlan'           => 'LC__CMDB__CATS__LAYER2_STANDARD_VLAN',
            'type'                    => 'LC__CMDB__CATS__LAYER2_TYPE',
            'sub_type'                => 'LC__CMDB__CATS__LAYER2_SUBTYPE',
        ];
    }

    /**
     * @return  string
     */
    public function make_row_link()
    {
        return isys_helper_link::create_url([
            C__CMDB__GET__OBJECT   => "[{isys_catg_qinq_list__isys_obj__id}]",
            C__CMDB__GET__VIEWMODE => C__CMDB__VIEW__LIST_CATEGORY,
            C__CMDB__GET__CATG     => defined_or_default('C__CATG__UNIVERSAL_INTERFACE'),
            C__CMDB__GET__TREEMODE => $_GET[C__CMDB__GET__TREEMODE]
        ]);
    }
}
