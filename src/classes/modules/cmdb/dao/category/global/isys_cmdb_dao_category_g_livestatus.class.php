<?php

use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: global virtual category for the livestatus connection.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.3.0
 */
class isys_cmdb_dao_category_g_livestatus extends isys_cmdb_dao_category_g_virtual
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'livestatus';

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'livestatus_state'        => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__MONITORING__LIVESTATUS_STATUS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Livestatus status'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_monitoring_list__active',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_monitoring_hosts__type = ' .
                        $this->convert_sql_text(C__MONITORING__TYPE_LIVESTATUS) . ' THEN
                                        isys_catg_monitoring_list__isys_obj__id ELSE NULL END)
                                FROM isys_catg_monitoring_list
                                INNER JOIN isys_monitoring_hosts ON isys_monitoring_hosts__id = isys_catg_monitoring_list__isys_monitoring_hosts__id',
                        'isys_catg_monitoring_list',
                        'isys_catg_monitoring_list__id',
                        'isys_catg_monitoring_list__isys_obj__id'
                    )
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__FILTERABLE => false
                ]
            ]),
            'livestatus_state_button' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__MONITORING__LIVESTATUS_STATUS_BUTTON',
                    C__PROPERTY__INFO__DESCRIPTION => 'Livestatus status'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_monitoring_list__active',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_monitoring_hosts__type = ' .
                        $this->convert_sql_text(C__MONITORING__TYPE_LIVESTATUS) . ' THEN
                                        isys_catg_monitoring_list__isys_obj__id ELSE NULL END)
                                FROM isys_catg_monitoring_list
                                INNER JOIN isys_monitoring_hosts ON isys_monitoring_hosts__id = isys_catg_monitoring_list__isys_monitoring_hosts__id',
                        'isys_catg_monitoring_list',
                        'isys_catg_monitoring_list__id',
                        'isys_catg_monitoring_list__isys_obj__id'
                    )
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__FILTERABLE => false
                ]
            ])
        ];
    }
}
