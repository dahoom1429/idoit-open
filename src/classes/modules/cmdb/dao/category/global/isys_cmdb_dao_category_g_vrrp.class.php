<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DialogProperty;

/**
 * i-doit
 *
 * CMDB DAO: Global category for VRRP.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @since       1.7
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_vrrp extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'vrrp';

    /**
     * Defines, if the category entry is purgable
     *
     * @var  boolean
     */
    protected $m_is_purgable = true;

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function properties()
    {
        return [
            'type' => (new DialogProperty(
                'C__CATG__VRRP__TYPE',
                'LC__CATG__VRRP__TYPE',
                'isys_catg_vrrp_list__isys_vrrp_type__id',
                'isys_catg_vrrp_list',
                'isys_vrrp_type'
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__INDEX => true
            ])->mergePropertyUiParams([
                'p_strClass' => 'input-small'
            ]),
            'vrid'        => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__VRRP__VRID',
                    C__PROPERTY__INFO__DESCRIPTION => 'VR ID'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_vrrp_list__vr_id'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__VRRP__VRID'
                ]
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_vrrp_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__VRRP', 'C__CATG__VRRP')
                ]
            ])
        ];
    }
}
