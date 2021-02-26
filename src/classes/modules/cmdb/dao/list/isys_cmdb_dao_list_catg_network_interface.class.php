<?php

/**
 * i-doit
 * DAO: ObjectType list for physical interfaces (subcategory of network).
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_network_interface extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__NETWORK_INTERFACE');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Modify row method will be called for each row to alter its content.
     *
     * @param   array $p_arrRow (by reference)
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function modify_row(&$p_arrRow)
    {
        global $g_dirs;

        $l_link = isys_helper_link::create_url([
            C__CMDB__GET__OBJECT => $p_arrRow['isys_obj__id'],
            C__CMDB__GET__CATG   => defined_or_default('C__CATG__NETWORK_PORT'),
            'ifaceID'            => $p_arrRow['isys_catg_netp_list__id']
        ]);

        $p_arrRow["showports"] = '<a href="' . $l_link . '" class="btn btn-small" title="' . isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATG__NETWORK__ASSO_PORTS__TOOLTIP') . '">' . '<img alt="" height="15" width="15" class="mr5" src="' . $g_dirs["images"] .
            'icons/silk/disconnect.png" />' . '<span>' . isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATG__NETWORK__ASSO_PORTS') . '</span></a>';
    }

    /**
     * Returns array with table headers.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_fields()
    {
        return [
            'isys_catg_netp_list__title'      => 'LC__CMDB__CATG__INTERFACE_L__TITLE',
            'isys_catg_netp_list__slotnumber' => 'LC__CMDB__CATG__INTERFACE_P_SLOTNUMBER',
            'isys_iface_manufacturer__title'  => 'LC__CMDB__CATG__INTERFACE_P_MANUFACTURER',
            'isys_iface_model__title'         => 'LC__CMDB__CATG__INTERFACE_P_MODEL',
            'showports'                       => 'LC__CMDB__CATG__NETWORK__ASSO_PORTS',
        ];
    }
}
