<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DialogProperty;

/**
 * i-doit
 *
 * CMDB DAO: specific category for the layer 2 nets
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-8
 * @author      Selcuk Kekec <skekec@synetics.de>
 */
class isys_cmdb_dao_category_s_layer2_net extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'layer2_net';

    /**
     * Method for adding a new IP helper to the database.
     *
     * @param   integer $p_cat_id
     * @param   string  $p_ip
     * @param   integer $p_type
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@synetics.de>
     */
    public function add_iphelper($p_cat_id, $p_ip, $p_type)
    {
        $l_sql = "INSERT INTO isys_cats_layer2_net_2_iphelper VALUES (NULL, " . $this->convert_sql_id($p_cat_id) . ", " . $this->convert_sql_id($p_type) . ", " .
            $this->convert_sql_text($p_ip) . ")";

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * Method for checking if a certain ID already exists.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@synetics.de>
     */
    public function check_id_exists($p_id)
    {
        $l_res = $this->retrieve("SELECT * FROM isys_cats_layer2_net_list WHERE isys_cats_layer2_net_list__ident = " . $this->convert_sql_text($p_id) . ";");
        $l_count = $l_res->num_rows();

        $l_row2 = $this->get_data(null, $_GET[C__CMDB__GET__OBJECT])
            ->get_row();

        if ($l_count == 0) {
            return false;
        } elseif ($l_count == 1) {
            $l_row = $l_res->get_row();

            if ($l_row['isys_cats_layer2_net_list__id'] == $l_row2['isys_cats_layer2_net_list__id']) {
                return false;
            }

            return true;
        } else {
            return true;
        }
    }

    /**
     * Removes a certain IP-helper from a layer2 net.
     *
     * @param   integer $p_cat_id
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@synetics.de>
     */
    public function clean_iphelper($p_cat_id)
    {
        $l_sql = "DELETE FROM isys_cats_layer2_net_2_iphelper
			WHERE isys_cats_layer2_net_2_iphelper__isys_cats_layer2_net_list__id = " . $this->convert_sql_id($p_cat_id) . ";";

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * Create method.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_status
     * @param   string  $p_ident
     * @param   integer $p_type
     * @param   integer $p_subtype
     * @param   integer $p_standard
     * @param   string  $p_description
     * @param   string  $p_vrf
     * @param   integer $p_vrf_capacity
     * @param   integer $p_vrf_capacity_unit
     *
     * @return  mixed  Integer with the last inserted ID or boolean false on failure.
     * @author  Selcuk Kekec <skekec@synetics.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function create(
        $p_obj_id,
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_ident = null,
        $p_type = null,
        $p_subtype = null,
        $p_standard = null,
        $p_description = '',
        $p_vrf = null,
        $p_vrf_capacity = null,
        $p_vrf_capacity_unit = null
    ) {
        $l_sql = 'INSERT IGNORE INTO isys_cats_layer2_net_list SET
			isys_cats_layer2_net_list__ident = ' . $this->convert_sql_text($p_ident) . ',
			isys_cats_layer2_net_list__isys_layer2_net_type__id = ' . $this->convert_sql_id($p_type) . ',
			isys_cats_layer2_net_list__isys_layer2_net_subtype__id = ' . $this->convert_sql_id($p_subtype) . ',
			isys_cats_layer2_net_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ',
			isys_cats_layer2_net_list__status = ' . $this->convert_sql_id($p_status) . ',
			isys_cats_layer2_net_list__description = ' . $this->convert_sql_text($p_description) . ',
			isys_cats_layer2_net_list__vrf = ' . $this->convert_sql_text($p_vrf) . ',
			isys_cats_layer2_net_list__vrf_capacity = ' . $this->convert_sql_int(isys_convert::speed_wan($p_vrf_capacity, $p_vrf_capacity_unit)) . ',
			isys_cats_layer2_net_list__isys_wan_capacity_unit = ' . $this->convert_sql_id($p_vrf_capacity_unit) . ',
			isys_cats_layer2_net_list__standard = ' . $this->convert_sql_boolean(($p_standard == true)) . ';';

        if ($this->update($l_sql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    /**
     * Get data method.
     *
     * @param   integer $p_cats_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @author  Selcuk Kekec <skekec@synetics.de>
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT *
			FROM isys_cats_layer2_net_list
			LEFT JOIN isys_layer2_net_type ON isys_cats_layer2_net_list__isys_layer2_net_type__id = isys_layer2_net_type__id
			LEFT JOIN isys_layer2_net_subtype ON isys_cats_layer2_net_list__isys_layer2_net_subtype__id  = isys_layer2_net_subtype__id
			LEFT JOIN isys_obj AS obj ON isys_cats_layer2_net_list__isys_obj__id = obj.isys_obj__id
			LEFT JOIN isys_obj_type ON isys_obj_type__id = obj.isys_obj__isys_obj_type__id
			WHERE TRUE " . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_cats_list_id !== null) {
            $l_sql .= " AND (isys_cats_layer2_net_list__id = " . $this->convert_sql_int($p_cats_list_id) . ")";
        }

        if ($p_status !== null) {
            $l_sql .= " AND (obj.isys_obj__status = " . $this->convert_sql_int($p_status) . ")";
        }

        return $this->retrieve($l_sql . ";");
    }

    /**
     * Creates the condition to the object table
     *
     * @param int|array $p_obj_id
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                $l_sql = ' AND (isys_cats_layer2_net_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_cats_layer2_net_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'vlan_id'             => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LAYER2_ID',
                    C__PROPERTY__INFO__DESCRIPTION => 'ID (VLAN)'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_layer2_net_list__ident'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__LAYER2_ID'
                ]
            ]),
            'standard'            => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LAYER2_STANDARD_VLAN',
                    C__PROPERTY__INFO__DESCRIPTION => 'Default VLAN'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_layer2_net_list__standard',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('(CASE WHEN isys_cats_layer2_net_list__standard = \'1\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__YES') . '
                                    WHEN isys_cats_layer2_net_list__standard = \'0\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__NO') . '
                                    ELSE ' . $this->convert_sql_text(isys_tenantsettings::get('gui.empty_value', '-')) . ' END)', 'isys_cats_layer2_net_list'),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_layer2_net_list',
                            'LEFT',
                            'isys_cats_layer2_net_list__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__CATS__LAYER2_STANDARD_VLAN',
                    C__PROPERTY__UI__DEFAULT => '0'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_yes_or_no'
                    ]
                ]
            ]),
            'type' => (new DialogPlusProperty(
                'C__CATS__LAYER2_TYPE',
                'LC__CMDB__CATS__LAYER2_TYPE',
                'isys_cats_layer2_net_list__isys_layer2_net_type__id',
                'isys_cats_layer2_net_list',
                'isys_layer2_net_type'
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false
            ])->setPropertyUiDefault(defined_or_default('C__LAYER2_NET__TYPE_VLAN')),
            'subtype' => (new DialogPlusProperty(
                'C__CATS__LAYER2_SUBTYPE',
                'LC__CMDB__CATS__LAYER2_SUBTYPE',
                'isys_cats_layer2_net_list__isys_layer2_net_subtype__id',
                'isys_cats_layer2_net_list',
                'isys_layer2_net_subtype'
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false
            ]),
            'ip_helper_addresses' => array_replace_recursive(isys_cmdb_dao_category_pattern::virtual(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LAYER2_IP_HELPER_ADDRESSES',
                    C__PROPERTY__INFO__TYPE        => C__PROPERTY__INFO__TYPE__MULTISELECT,
                    C__PROPERTY__INFO__DESCRIPTION => 'IP helper addresses'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_layer2_net_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_layer2_iphelper_type__title, \' \', isys_cats_layer2_net_2_iphelper__ip)
                            FROM isys_cats_layer2_net_list
                            INNER JOIN isys_cats_layer2_net_2_iphelper ON isys_cats_layer2_net_2_iphelper__isys_cats_layer2_net_list__id = isys_cats_layer2_net_list__id
                            INNER JOIN isys_layer2_iphelper_type ON isys_layer2_iphelper_type__id = isys_cats_layer2_net_2_iphelper__isys_layer2_net_iphelper_type',
                        'isys_cats_layer2_net_list',
                        'isys_cats_layer2_net_list__id',
                        'isys_cats_layer2_net_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_layer2_net_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__LAYER2_NET__IP_HELPER_ADDRESSES'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH       => false,
                    C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                    C__PROPERTY__PROVIDES__IMPORT       => true,
                    C__PROPERTY__PROVIDES__EXPORT       => true,
                    C__PROPERTY__PROVIDES__REPORT       => false,
                    C__PROPERTY__PROVIDES__LIST         => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT    => false,
                    C__PROPERTY__PROVIDES__VALIDATION   => false,
                    C__PROPERTY__PROVIDES__VIRTUAL      => true,
                    C__PROPERTY__PROVIDES__FILTERABLE   => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'ip_helper_addresses'
                    ]
                ]
            ]),
            'layer3_assignments'  => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LAYER2__LAYER3_NET',
                    C__PROPERTY__INFO__DESCRIPTION => 'Layer-3-net assignments',
                    C__PROPERTY__INFO__TYPE        => C__PROPERTY__INFO__TYPE__N2M
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_cats_layer2_net_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_cats_layer2_net_2_layer3',
                        'isys_obj__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(l3.isys_obj__title, \' {\', l3.isys_obj__id, \'}\')
                            FROM isys_cats_layer2_net_list AS main
                            INNER JOIN isys_cats_layer2_net_2_layer3 AS l2l3 ON l2l3.isys_cats_layer2_net_list__id = main.isys_cats_layer2_net_list__id
                            INNER JOIN isys_obj l3 ON l3.isys_obj__id = l2l3.isys_obj__id',
                        'isys_cats_layer2_net_list',
                        'main.isys_cats_layer2_net_list__id',
                        'main.isys_cats_layer2_net_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['main.isys_cats_layer2_net_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_layer2_net_list',
                            'LEFT',
                            'isys_cats_layer2_net_list__isys_obj__id',
                            'isys_obj__id',
                            'main',
                            '',
                            'main'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_layer2_net_2_layer3',
                            'LEFT',
                            'isys_cats_layer2_net_list__id',
                            'isys_cats_layer2_net_list__id',
                            'main',
                            'l2l3',
                            'l2l3'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_obj__id', 'isys_obj__id', 'l2l3', 'l3', 'l3')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__LAYER2__LAYER3_NET',
                    C__PROPERTY__UI__PARAMS => [
                        'multiselection' => true
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'layer_3_assignment'
                    ]
                ]
            ]),
            'vrf'                 => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATS__LAYER2__VRF',
                    C__PROPERTY__INFO__DESCRIPTION => 'VRF'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_layer2_net_list__vrf'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__LAYER2__VRF'
                ]
            ]),
            'vrf_capacity'        => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO   => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATS__LAYER2__VRF_CAPACITY',
                    C__PROPERTY__INFO__DESCRIPTION => 'VRF capacity'
                ],
                C__PROPERTY__DATA   => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_layer2_net_list__vrf_capacity',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(ROUND(isys_cats_layer2_net_list__vrf_capacity / isys_wan_capacity_unit__factor), \' \', isys_wan_capacity_unit__title)
                            FROM isys_cats_layer2_net_list
                            INNER JOIN isys_wan_capacity_unit ON isys_wan_capacity_unit__id = isys_cats_layer2_net_list__isys_wan_capacity_unit',
                        'isys_cats_layer2_net_list',
                        'isys_cats_layer2_net_list__id',
                        'isys_cats_layer2_net_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_layer2_net_list',
                            'LEFT',
                            'isys_cats_layer2_net_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_wan_capacity_unit',
                            'LEFT',
                            'isys_cats_layer2_net_list__isys_wan_capacity_unit',
                            'isys_wan_capacity_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI     => [
                    C__PROPERTY__UI__ID     => 'C__CATS__LAYER2__VRF_CAPACITY',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-medium',
                    ]
                ],
                C__PROPERTY__FORMAT => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['speed_wan']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'vrf_capacity_unit'
                ],
            ]),
            'vrf_capacity_unit' => (new DialogProperty(
                'C__CATS__LAYER2__VRF_CAPACITY_UNIT',
                'LC__CATS__LAYER2__VRF_CAPACITY_UNIT',
                'isys_cats_layer2_net_list__isys_wan_capacity_unit',
                'isys_cats_layer2_net_list',
                'isys_wan_capacity_unit'
            ))->mergePropertyUiParams([
                'p_bSort' => false,
                'p_strClass' => 'input-mini'
            ]),
            'description'         => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_layer2_net_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__LAYER2_NET', 'C__CATS__LAYER2_NET')
                ]
            ])
        ];
    }

    /**
     * Synchronize category content with $p_data
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   array                   $p_category_data
     * @param   integer                 $p_object_id
     * @param   integer                 $p_status
     *
     * @return  mixed  Boolean or integer.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            // Create category data identifier if needed.
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                $p_category_data['data_id'] = $this->create($p_object_id);
            }
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data.
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    C__RECORD_STATUS__NORMAL,
                    $p_category_data['properties']['vlan_id'][C__DATA__VALUE],
                    $p_category_data['properties']['type'][C__DATA__VALUE],
                    $p_category_data['properties']['subtype'][C__DATA__VALUE],
                    $p_category_data['properties']['standard'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    $p_category_data['properties']['vrf'][C__DATA__VALUE],
                    $p_category_data['properties']['vrf_capacity'][C__DATA__VALUE],
                    $p_category_data['properties']['vrf_capacity_unit'][C__DATA__VALUE]
                );

                $l_ip_helper = $p_category_data['properties']['ip_helper_addresses'][C__DATA__VALUE];

                if (is_array($l_ip_helper)) {
                    $this->clean_iphelper($p_category_data['data_id']);

                    foreach ($l_ip_helper as $l_iphelper) {
                        $this->add_iphelper($p_category_data['data_id'], $l_iphelper['ip'], isys_import::check_dialog('isys_layer2_iphelper_type', $l_iphelper['type_title']));
                    }
                }

                $l_layer_3 = (isset($p_category_data['properties']['layer3_assignments'][C__DATA__VALUE])) ? $p_category_data['properties']['layer3_assignments'][C__DATA__VALUE] : [];

                if (!is_array($l_layer_3) && is_numeric($l_layer_3)) {
                    $l_layer_3 = [$l_layer_3];
                }

                $this->update_layer3($p_category_data['data_id'], $l_layer_3, $p_object_id);
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Method for retrieving the address of a certain IP helper.
     *
     * @param   integer $p_cat_id
     *
     * @return  mixed  Array with data or boolean false.
     * @author  Selcuk Kekec <skekec@synetics.de>
     */
    public function get_iphelper_adress($p_cat_id)
    {
        if ($p_cat_id) {
            $l_sql = "SELECT * FROM isys_cats_layer2_net_2_iphelper WHERE isys_cats_layer2_net_2_iphelper__isys_cats_layer2_net_list__id = " . $p_cat_id . ";";

            $l_res = $this->retrieve($l_sql);

            if ($l_res->num_rows() > 0) {
                $l_res_arr = [];
                while ($l_row = $l_res->get_row()) {
                    $l_res_arr[] = $l_row;
                }

                return $l_res_arr;
            }
        }

        return false;
    }

    /**
     * This method selects all layer2 nets, but adds a suffix to the title. This is used for the object browser.
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     *
     * @param   array $p_layer2_object_ids
     *
     * @todo    Add a parameter to select only layer2 nets, assigned by a port (for dataretrieval method).
     */
    public function find_layer2_objects_with_prefixed_name($p_layer2_object_ids = [])
    {
        $l_sql_condition = ' CASE isys_cats_layer2_net_list__isys_layer2_net_subtype__id ' . 'WHEN ' . (int)defined_or_default('C__CATS__LAYER2_NET__SUBTYPE__STATIC_VLAN') .
            ' THEN CONCAT(isys_obj__title, " ", isys_cats_layer2_net_list__ident, " (Static)") ' . 'WHEN ' . (int)defined_or_default('C__CATS__LAYER2_NET__SUBTYPE__DYNAMIC_VLAN') .
            ' THEN CONCAT(isys_obj__title, " ", isys_cats_layer2_net_list__ident, " (Dynamic)") ' . 'ELSE CONCAT(isys_obj__title, "") END ';

        $l_sql = 'SELECT isys_obj__id, ' . $l_sql_condition . ' AS isys_obj__title, isys_obj_type__title, isys_obj__sysid FROM isys_obj
			LEFT JOIN isys_cats_layer2_net_list ON isys_obj__id = isys_cats_layer2_net_list__isys_obj__id
			LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_obj__isys_obj_type__id = ' . $this->convert_sql_id(defined_or_default('C__OBJTYPE__LAYER2_NET')) . '
			AND isys_obj__status = ' . $this->convert_sql_id(C__RECORD_STATUS__NORMAL);

        if ($p_layer2_object_ids && count($p_layer2_object_ids)) {
            $l_sql .= ' AND isys_obj__id IN(' . implode(',', $p_layer2_object_ids) . ')';
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for retrieving the layer2 nets.
     *
     * @param   array $p_layer2_object_ids
     *
     * @return  array
     */
    public function get_layer2_vlans(array $p_layer2_object_ids)
    {
        $l_return = [];
        $l_vlans = $this->find_layer2_objects_with_prefixed_name($p_layer2_object_ids);

        while ($l_row = $l_vlans->get_row()) {
            $l_return[] = [
                'id'    => $l_row['isys_obj__id'],
                'title' => $l_row['isys_obj__title']
            ];
        }

        return $l_return;
    }

    /**
     * Save method.
     *
     * @param   integer $p_id
     * @param   integer $p_status
     * @param   string  $p_ident
     * @param   integer $p_type
     * @param   integer $p_subtype
     * @param   integer $p_standard
     * @param   string  $p_description
     * @param   integer $p_vrf
     * @param   string  $p_vrf_capacity
     * @param   integer $p_vrf_capacity_unit
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@synetics.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save($p_id, $p_status, $p_ident, $p_type, $p_subtype, $p_standard, $p_description, $p_vrf = null, $p_vrf_capacity = null, $p_vrf_capacity_unit = null)
    {
        $l_sql = 'UPDATE  isys_cats_layer2_net_list SET
			isys_cats_layer2_net_list__ident = ' . $this->convert_sql_text($p_ident) . ',
			isys_cats_layer2_net_list__isys_layer2_net_type__id = ' . $this->convert_sql_id($p_type) . ',
			isys_cats_layer2_net_list__isys_layer2_net_subtype__id = ' . $this->convert_sql_id($p_subtype) . ',
			isys_cats_layer2_net_list__description = ' . $this->convert_sql_text($p_description) . ',
			isys_cats_layer2_net_list__status = ' . $this->convert_sql_id($p_status) . ',
			isys_cats_layer2_net_list__standard = ' . $this->convert_sql_boolean(($p_standard == true)) . ',
			isys_cats_layer2_net_list__vrf = ' . $this->convert_sql_text($p_vrf) . ',
			isys_cats_layer2_net_list__vrf_capacity = ' . $this->convert_sql_int(isys_convert::speed_wan($p_vrf_capacity, $p_vrf_capacity_unit)) . ',
			isys_cats_layer2_net_list__isys_wan_capacity_unit = ' . $this->convert_sql_id($p_vrf_capacity_unit) . '
			WHERE isys_cats_layer2_net_list__id = ' . $this->convert_sql_id($p_id) . ';';

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * Save element method.
     *
     * @param   integer & $p_cat_level
     * @param   integer & $p_intOldRecStatus
     *
     * @return  mixed  Null on success, or the error code as integer.
     * @author  Selcuk Kekec <skekec@synetics.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus)
    {
        $l_intErrorCode = -1;
        $l_catdata = $this->get_data_by_object($_GET[C__CMDB__GET__OBJECT])
            ->__to_array();

        if (!empty($l_catdata["isys_cats_layer2_net_list__id"])) {
            $l_bRet = $this->save(
                $l_catdata['isys_cats_layer2_net_list__id'],
                C__RECORD_STATUS__NORMAL, //TODO
                $_POST['C__CATS__LAYER2_ID'],
                $_POST['C__CATS__LAYER2_TYPE'],
                $_POST['C__CATS__LAYER2_SUBTYPE'],
                $_POST['C__CATS__LAYER2_STANDARD_VLAN'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()],
                $_POST['C__CATS__LAYER2__VRF'],
                $_POST['C__CATS__LAYER2__VRF_CAPACITY'],
                $_POST['C__CATS__LAYER2__VRF_CAPACITY_UNIT']
            );

            $this->update_iphelper($l_catdata['isys_cats_layer2_net_list__id'], $_POST['ip_helper']);
            $this->update_layer3($l_catdata['isys_cats_layer2_net_list__id'], isys_format_json::decode($_POST['C__CATS__LAYER2__LAYER3_NET__HIDDEN'], true) ?: [], $_GET[C__CMDB__GET__OBJECT]);

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        return $l_bRet == true ? null : $l_intErrorCode;
    }

    /**
     * Method for creating several IP helper.
     *
     * @param   integer $p_cat_id
     * @param   array   $p_iphelper
     *
     * @author  Selcuk Kekec <skekec@synetics.de>
     */
    public function update_iphelper($p_cat_id, $p_iphelper)
    {
        if ($this->clean_iphelper($p_cat_id)) {
            if (isset($p_iphelper) && is_countable($p_iphelper) && count($p_iphelper)) {
                foreach ($p_iphelper as $l_value) {
                    $this->add_iphelper($p_cat_id, $l_value['ip'], $l_value['type']);
                }
            }
        }
    }

    /**
     * @param $entryId
     *
     * @return array
     * @throws isys_exception_database
     */
    private function getAssignedLayer3($entryId)
    {
        $query = 'SELECT isys_obj__id FROM isys_cats_layer2_net_2_layer3
            WHERE isys_cats_layer2_net_list__id = ' . $this->convert_sql_id($entryId);

        $return = [];
        $result = $this->retrieve($query);
        if (count($result)) {
            while ($row = $result->get_row()) {
                $return[] = $row['isys_obj__id'];
            }
        }

        return $return;
    }

    /**
     * Method for creating several IP helper.
     *
     * @param integer $p_cat_id
     * @param array   $p_layer3
     * @param integer $objectId
     *
     * @return  boolean
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function update_layer3($p_cat_id, array $p_layer3 = [], $objectId)
    {
        $assignedLayer3Nets = $this->getAssignedLayer3($p_cat_id);
        $deleteArray = $createArray = [];
        $layer3Dao = isys_cmdb_dao_category_s_net::instance(isys_application::instance()->container->get('database'));

        if (!empty($p_layer3)) {
            $createArray = array_filter($p_layer3, function ($item) use ($assignedLayer3Nets) {
                return !in_array($item, $assignedLayer3Nets);
            });
        }

        $deleteArray = array_filter($assignedLayer3Nets, function ($item) use ($p_layer3) {
            return !in_array($item, $p_layer3);
        });

        if (!empty($createArray)) {
            foreach ($createArray as $layer3ObjectId) {
                $layer3Dao->add_layer2_assignment([$objectId => $p_cat_id], $layer3ObjectId);
            }
        }

        if (!empty($deleteArray)) {
            foreach ($deleteArray as $layer3ObjectId) {
                $layer3Dao->remove_layer2_assignment($p_cat_id, $layer3ObjectId);
            }
        }

        return true;
    }

    /**
     * @param int $p_cat_id
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function clean_layer3($p_cat_id)
    {
        $sql = 'DELETE FROM isys_cats_layer2_net_2_layer3 WHERE isys_cats_layer2_net_list__id = ' . $this->convert_sql_id($p_cat_id) . ';';

        return $this->update($sql) && $this->apply_update();
    }

    /**
     * Get layer 3 assignments.
     *
     * @param   integer $p_cat_id
     *
     * @return  array
     */
    public function get_layer3_assignments_as_array($p_cat_id)
    {
        $l_layer3s = [];
        $l_data = $this->get_layer3_assignments($p_cat_id);

        while ($l_row = $l_data->get_row()) {
            $l_layer3s[] = $l_row['isys_obj__id'];
        }

        return $l_layer3s;
    }

    /**
     * Get layer 3 assignments.
     *
     * @param   integer $p_cat_id
     *
     * @return  array
     */
    public function get_layer3_assignments($p_cat_id)
    {
        $l_sql = 'SELECT isys_obj.isys_obj__id, isys_obj.isys_obj__title ' . 'FROM isys_cats_layer2_net_2_layer3 ' .
            'INNER JOIN isys_obj ON isys_obj.isys_obj__id = isys_cats_layer2_net_2_layer3.isys_obj__id ' . 'WHERE isys_cats_layer2_net_list__id = ' .
            $this->convert_sql_id($p_cat_id) . ';';

        return $this->retrieve($l_sql);
    }
}
