<?php

/**
 * i-doit
 *
 * CMDB global category for net zone options.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.8.1
 */
class isys_cmdb_dao_category_g_net_zone_options extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'net_zone_options';

    /**
     * Method for retrieving all category properties.
     *
     * @return  array
     */
    public function properties()
    {
        return [
            'color'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__NET_ZONE_OPTIONS__COLOR',
                    C__PROPERTY__INFO__DESCRIPTION => 'Color'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_net_zone_options_list__color'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__NET_ZONE_OPTIONS__COLOR',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass'       => 'input-mini',
                        'p_strPlaceholder' => '#ffffff',
                        'default'          => '#ffffff'
                    ]
                ]
            ]),
            'domain'      => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__NET_ZONE_OPTIONS__COLOR',
                    C__PROPERTY__INFO__DESCRIPTION => 'Domain'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_net_zone_options_list__domain'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__NET_ZONE_OPTIONS__DOMAIN',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-mini'
                    ]
                ]
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_net_zone_options_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_catg_net_zone_options_list__description FROM isys_catg_net_zone_options_list',
                        'isys_catg_net_zone_options_list', 'isys_catg_net_zone_options_list__id', 'isys_catg_net_zone_options_list__isys_obj__id')
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__NET_ZONE_OPTIONS', 'C__CATG__NET_ZONE_OPTIONS')
                ]
            ])
        ];
    }
}
