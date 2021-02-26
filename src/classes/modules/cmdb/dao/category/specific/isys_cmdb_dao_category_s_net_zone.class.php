<?php

use idoit\Component\Helper\Ip;

/**
 * i-doit
 *
 * DAO: specific category for net zone
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.8.1
 */
class isys_cmdb_dao_category_s_net_zone extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'net_zone';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean  Defaults to false.
     */
    protected $m_multivalued = true;

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'zone'            => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__NET__ZONE__ZONE_OBJECT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Net zone object'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_cats_net_zone_list__isys_obj__id__zone',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_obj',
                        'isys_obj__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_obj__title
                            FROM isys_cats_net_zone_list
                            INNER JOIN isys_obj ON isys_obj__id = isys_cats_net_zone_list__isys_obj__id__zone', 'isys_cats_net_zone_list', 'isys_cats_net_zone_list__id',
                        'isys_cats_net_zone_list__isys_obj__id', '', '', idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_net_zone_list__isys_obj__id'])),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_net_zone_list', 'LEFT', 'isys_cats_net_zone_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_cats_net_zone_list__isys_obj__id__zone', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__NET_ZONE__ZONE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass'                                => 'input-small',
                        isys_popup_browser_object_ng::C__CAT_FILTER => 'C__CATG__NET_ZONE;C__CATG__NET_ZONE_OPTIONS;C__CATG__NET_ZONE_SCOPES'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'range_from'      => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__NET__ZONE_RANGE_FROM',
                    C__PROPERTY__INFO__DESCRIPTION => 'Net zone from'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_net_zone_list__range_from',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_cats_net_zone_list__range_from FROM isys_cats_net_zone_list',
                        'isys_cats_net_zone_list', 'isys_cats_net_zone_list__id', 'isys_cats_net_zone_list__isys_obj__id', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_net_zone_list__isys_obj__id']))
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATS__NET_ZONE_RANGE_FROM',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-mini'
                    ]
                ]
            ]),
            'range_from_long' => array_replace_recursive(isys_cmdb_dao_category_pattern::virtual(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__NET__ZONE_RANGE_FROM',
                    C__PROPERTY__INFO__DESCRIPTION => 'Net zone from'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_net_zone_list__range_from_long'
                ]
            ]),
            'range_to'        => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__NET__ZONE_RANGE_TO',
                    C__PROPERTY__INFO__DESCRIPTION => 'Net zone to'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_net_zone_list__range_to',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_cats_net_zone_list__range_to FROM isys_cats_net_zone_list',
                        'isys_cats_net_zone_list', 'isys_cats_net_zone_list__id', 'isys_cats_net_zone_list__isys_obj__id', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_net_zone_list__isys_obj__id']))
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATS__NET_ZONE_RANGE_TO',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-mini'
                    ]
                ]
            ]),
            'range_to_long'   => array_replace_recursive(isys_cmdb_dao_category_pattern::virtual(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__NET__ZONE_RANGE_TO',
                    C__PROPERTY__INFO__DESCRIPTION => 'Net zone to'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_net_zone_list__range_to_long'
                ]
            ]),
            'description'     => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_net_zone_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_cats_net_zone_list__description FROM isys_cats_net_zone_list',
                        'isys_cats_net_zone_list', 'isys_cats_net_zone_list__id', 'isys_cats_net_zone_list__isys_obj__id', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_net_zone_list__isys_obj__id']))
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__NET_ZONE', 'C__CATS__NET_ZONE')
                ]
            ])
        ];
    }

    /**
     * Updates existing entity.
     *
     * @param   array $p_data
     *
     * @return  boolean
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    public function create_data($p_data)
    {
        $p_data['range_from_long'] = $p_data['range_to_long'] = null;

        if (isset($p_data['range_from']) && Ip::validate_ip($p_data['range_from'])) {
            $p_data['range_from_long'] = Ip::ip2long($p_data['range_from']);
        }

        if (isset($p_data['range_to']) && Ip::validate_ip($p_data['range_to'])) {
            $p_data['range_to_long'] = Ip::ip2long($p_data['range_to']);
        }

        if (is_numeric(($entryId = parent::create_data($p_data)))) {
            // We automatically update all hostaddresses inside the range.
            isys_cmdb_dao_category_g_ip::instance($this->m_db)
                ->update_ip_zones_by_range($p_data['isys_obj__id'], $p_data['zone'], $p_data['range_from'], $p_data['range_to']);

            return $entryId;
        }
    }

    /**
     * Updates existing entity.
     *
     * @param   integer $p_data_id
     * @param   array   $p_data
     *
     * @return  boolean
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    public function save_data($p_data_id, $p_data)
    {
        $p_data['range_from_long'] = $p_data['range_to_long'] = null;

        if (isset($p_data['range_from']) && Ip::validate_ip($p_data['range_from'])) {
            $p_data['range_from_long'] = Ip::ip2long($p_data['range_from']);
        }

        if (isset($p_data['range_to']) && Ip::validate_ip($p_data['range_to'])) {
            $p_data['range_to_long'] = Ip::ip2long($p_data['range_to']);
        }

        if (parent::save_data($p_data_id, $p_data)) {
            // We automatically update all hostaddresses inside the range.
            isys_cmdb_dao_category_g_ip::instance($this->m_db)
                ->update_ip_zones_by_range($p_data['isys_obj__id'], $p_data['zone'], $p_data['range_from'], $p_data['range_to']);
        }
    }

    /**
     * Method for merging a new zone inside an existing.
     *
     * @param   integer $p_net_obj_id  The object-ID of the layer3 net.
     * @param   integer $p_zone_obj_id The object-ID of the zone.
     * @param   string  $p_from        The starting IP-address.
     * @param   string  $p_to          The ending IP-address (if left empty this parameter will be set to the "from" value).
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function insert_zone_in_net($p_net_obj_id, $p_zone_obj_id, $p_from, $p_to = null)
    {
        if ($p_to === null) {
            $p_to = $p_from;
        }

        // With this method we check, if there will be any conflicts.
        $l_conflict_res = $this->find_range_conflicts($p_net_obj_id, $p_from, $p_to);

        // Now we check, if the method retrieved any conflicted ID's for us.
        if (is_countable($l_conflict_res) && count($l_conflict_res)) {
            while ($l_row = $l_conflict_res->get_row()) {
                // We save all entries.
                $l_zones[] = $l_row;

                // And delete them - So now we can start from scratch.
                $this->delete_entry($l_row['isys_cats_net_zone_list__id'], 'isys_cats_net_zone_list');
            }

            // We set the new FROM and TO values as default, but check this in the next IF-statements.
            $l_new_range_from = $p_from;
            $l_new_range_to = $p_to;

            // We get ourselves the first and last entry of the array.
            $l_first = current($l_zones);
            $l_last = end($l_zones);

            // Please note: We can only check this because it was ordered by the FROM range.
            if ($l_first['isys_cats_net_zone_list__range_from_long'] < Ip::ip2long($l_new_range_from)) {
                // Now we check for the type - If it's the same, we merge.
                if ($l_first['isys_cats_net_zone_list__isys_obj__id__zone'] == $p_zone_obj_id) {
                    $l_new_range_from = $l_first['isys_cats_net_zone_list__range_from'];
                } else {
                    // If the types are not the same, we have to restore the first range.
                    $this->create_data([
                        'isys_obj__id' => $p_net_obj_id,
                        'range_from'   => $l_first['isys_cats_net_zone_list__range_from'],
                        'range_to'     => Ip::long2ip(Ip::ip2long($l_new_range_from) - 1),
                        'zone'         => $l_first['isys_cats_net_zone_list__isys_obj__id__zone'],
                        'description'  => $l_first['isys_cats_net_zone_list__description'],
                        'status'       => $l_first['isys_cats_net_zone_list__status']
                    ]);
                }
            }

            // In the last entry there has to be the most last range.
            if ($l_last['isys_cats_net_zone_list__range_to_long'] > Ip::ip2long($l_new_range_to)) {
                // Now we check for the type - If it's the same, we merge.
                if ($l_last['isys_cats_net_zone_list__isys_obj__id__zone'] == $p_zone_obj_id) {
                    $l_new_range_to = $l_last['isys_cats_net_zone_list__range_to'];
                } else {
                    // If the types are not the same, we have to restore the range.
                    $this->create_data([
                        'isys_obj__id' => $p_net_obj_id,
                        'range_from'   => Ip::long2ip(Ip::ip2long($l_new_range_to) + 1),
                        'range_to'     => $l_last['isys_cats_net_zone_list__range_to'],
                        'zone'         => $l_last['isys_cats_net_zone_list__isys_obj__id__zone'],
                        'description'  => $l_last['isys_cats_net_zone_list__description'],
                        'status'       => $l_last['isys_cats_net_zone_list__status']
                    ]);
                }
            }

            $this->create_data([
                'isys_obj__id' => $p_net_obj_id,
                'range_from'   => $l_new_range_from,
                'range_to'     => $l_new_range_to,
                'zone'         => $p_zone_obj_id,
                'status'       => C__RECORD_STATUS__NORMAL
            ]);

            $l_return = ['result' => 'merged'];
        } else {
            // When the array is empty, we can blindly create a new zone range.
            $this->create_data([
                'isys_obj__id' => $p_net_obj_id,
                'range_from'   => $p_from,
                'range_to'     => $p_to,
                'zone'         => $p_zone_obj_id,
                'status'       => C__RECORD_STATUS__NORMAL
            ]);

            $l_return = ['result' => 'success'];
        }

        return $l_return;
    }

    /**
     * With this method, we can find every zone range conflict, before we actually start messing with the database.
     *
     * @param   integer $p_obj_id
     * @param   string  $p_from
     * @param   string  $p_to
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function find_range_conflicts($p_obj_id, $p_from, $p_to, $p_status = C__RECORD_STATUS__NORMAL)
    {
        if (!empty($p_from) && !empty($p_to)) {
            $l_sql = 'SELECT * FROM isys_cats_net_zone_list
                WHERE (( isys_cats_net_zone_list__range_from_long BETWEEN ' . Ip::ip2long($p_from) . ' AND ' . Ip::ip2long($p_to) . ')
                    OR (isys_cats_net_zone_list__range_to_long BETWEEN ' . Ip::ip2long($p_from) . ' AND ' . Ip::ip2long($p_to) . ')
                    OR (isys_cats_net_zone_list__range_from_long <= ' . Ip::ip2long($p_from) . ' AND isys_cats_net_zone_list__range_to_long >= ' . Ip::ip2long($p_to) . ')
                    OR (isys_cats_net_zone_list__range_from_long >= ' . Ip::ip2long($p_from) . ' AND isys_cats_net_zone_list__range_to_long <= ' . Ip::ip2long($p_to) . '))
                AND isys_cats_net_zone_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
                AND isys_cats_net_zone_list__status = ' . $this->convert_sql_int($p_status) . '
                ORDER BY isys_cats_net_zone_list__range_from_long ASC;';

            return $this->retrieve($l_sql);
        }

        return null;
    }

    /**
     * Method for finding a net zone by a given IP and net object ID.
     *
     * @param integer $p_ip
     * @param integer $p_net_object_id
     *
     * @return int|null
     * @throws isys_exception_database
     */
    public function get_zone_by_ip($p_ip, $p_net_object_id)
    {
        if (empty($p_ip)) {
            return null;
        }

        $l_sql = 'SELECT isys_cats_net_zone_list__isys_obj__id__zone FROM isys_cats_net_zone_list
            WHERE isys_cats_net_zone_list__isys_obj__id = ' . $this->convert_sql_id($p_net_object_id) . '
            AND ' . Ip::ip2long($p_ip) . ' BETWEEN isys_cats_net_zone_list__range_from_long AND isys_cats_net_zone_list__range_to_long';

        $l_res = $this->retrieve($l_sql);

        if (is_countable($l_res) && count($l_res)) {
            return (int)$l_res->get_row_value('isys_cats_net_zone_list__isys_obj__id__zone');
        }

        return null;
    }
}