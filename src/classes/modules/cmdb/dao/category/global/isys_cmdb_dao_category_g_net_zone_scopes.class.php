<?php

/**
 * i-doit
 *
 * CMDB global category for net zones.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.8.1
 */
class isys_cmdb_dao_category_g_net_zone_scopes extends isys_cmdb_dao_category_g_virtual
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'net_zone_scopes';

    /**
     * Retrieves all Layer3 nets, where the given zone is being used.
     *
     * @param integer $p_zone_obj_id
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_layer3_nets($p_zone_obj_id)
    {
        $l_sql = 'SELECT * FROM isys_cats_net_zone_list
            INNER JOIN isys_obj ON isys_cats_net_zone_list__isys_obj__id = isys_obj__id
            WHERE isys_cats_net_zone_list__isys_obj__id__zone = ' . $this->convert_sql_id($p_zone_obj_id) . '
            AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Method for returning the properties.
     *
     * @see  ID-5084  The property name contained a invalid character for queries ("-").
     *
     * @return  array
     */
    public function properties()
    {
        return [
            'from_to' => array_replace_recursive(isys_cmdb_dao_category_pattern::virtual(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__NET_ZONE_SCOPES',
                    C__PROPERTY__INFO__DESCRIPTION => 'Scopes'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_cats_net_zone_list__isys_obj__id__zone',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_obj',
                        'isys_obj__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT CONCAT(isys_cats_net_zone_list__range_from, " - ", isys_cats_net_zone_list__range_to) FROM isys_cats_net_zone_list
                            INNER JOIN isys_obj ON isys_cats_net_zone_list__isys_obj__id = isys_obj__id', 'isys_cats_net_zone_list', 'isys_cats_net_zone_list__id',
                        'isys_cats_net_zone_list__isys_obj__id__zone', '', '', idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_net_zone_list__isys_obj__id__zone']))
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ]
            ])
        ];
    }
}
