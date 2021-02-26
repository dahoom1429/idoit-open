<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: specific category for wide area networks (WAN).
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_wan extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'wan';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Dynamic property handling for getting the last change time of an object.
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   array                   $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function dynamic_property_callback_capacity(array $p_row)
    {
        global $g_comp_database;

        $objId = ($p_row['isys_cats_wan_list__isys_obj__id'] ?: ($p_row['isys_obj__id'] ?: null));

        if ($objId === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        $l_return = '';
        $l_dao = isys_cmdb_dao_category_s_wan::instance($g_comp_database);

        $l_row = $l_dao->get_data(null, $objId)
            ->get_row();

        if ($l_row !== null && $l_row['isys_cats_wan_list__isys_wan_capacity_unit__id'] !== null) {
            $l_unit_row = $l_dao->get_dialog('isys_wan_capacity_unit', $l_row['isys_cats_wan_list__isys_wan_capacity_unit__id'])
                ->get_row();

            $l_return = isys_convert::speed_wan($l_row['isys_cats_wan_list__capacity'], $l_unit_row['isys_wan_capacity_unit__const'], C__CONVERT_DIRECTION__BACKWARD) . ' ' .
                isys_application::instance()->container->get('language')
                    ->get($l_unit_row['isys_wan_capacity_unit__title']);
        }

        return $l_return;
    }

    /**
     * Save specific category WAN.
     *
     * @param   integer $p_cat_level
     * @param   integer & $p_intOldRecStatus
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();
        $p_intOldRecStatus = $l_catdata["isys_cats_switch_net_list__status"];

        $l_list_id = $l_catdata['isys_cats_wan_list__id'];

        if (empty($l_list_id)) {
            $l_list_id = $this->create_connector("isys_cats_wan_list", $_GET[C__CMDB__GET__OBJECT]);
        }

        $l_bRet = $this->save(
            $l_list_id,
            C__RECORD_STATUS__NORMAL,
            $_POST['C__CATS__WAN__ROLE'],
            $_POST['C__CATS__WAN__TYPE'],
            $_POST['C__CATS__WAN_CAPACITY'],
            $_POST['C__CATS__WAN__UNIT'],
            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
        );

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_bRet == true ? $l_list_id : -1;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_newRecStatus
     * @param   integer $p_roleID
     * @param   integer $p_typeID
     * @param   integer $p_capacity
     * @param   integer $p_capacityUnitID
     * @param   string  $p_description
     *
     * @return  boolean
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_roleID, $p_typeID, $p_capacity, $p_capacityUnitID, $p_description)
    {
        $l_res = $this->get_dialog("isys_wan_capacity_unit", $p_capacityUnitID);
        $l_row = $l_res->get_row();

        $l_bits = isys_convert::speed_wan($p_capacity, $l_row["isys_wan_capacity_unit__const"]);

        $l_strSql = 'UPDATE isys_cats_wan_list
            SET isys_cats_wan_list__description = ' . $this->convert_sql_text($p_description) . ',
            isys_cats_wan_list__capacity = ' . $this->convert_sql_text($l_bits) . ',
            isys_cats_wan_list__isys_wan_type__id = ' . $this->convert_sql_id($p_typeID) . ',
            isys_cats_wan_list__isys_wan_role__id = ' . $this->convert_sql_id($p_roleID) . ',
            isys_cats_wan_list__isys_wan_capacity_unit__id = ' . $this->convert_sql_id($p_capacityUnitID) . ',
            isys_cats_wan_list__status = ' . $this->convert_sql_id($p_newRecStatus) . '
            WHERE isys_cats_wan_list__id = ' . $this->convert_sql_id($p_cat_level) . ';';

        return ($this->update($l_strSql) && $this->apply_update());
    }

    /**
     * Executes the query to create the category entry.
     *
     * @param   integer $p_objID
     * @param   integer $p_newRecStatus
     * @param   integer $p_roleID
     * @param   integer $p_typeID
     * @param   integer $p_capacity
     * @param   integer $p_capacityUnitID
     * @param   string  $p_description
     *
     * @return  mixed  The newly created ID as integer or false.
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus, $p_roleID, $p_typeID, $p_capacity, $p_capacityUnitID, $p_description)
    {
        $l_res = $this->get_dialog("isys_wan_capacity_unit", $p_capacityUnitID);
        $l_row = $l_res->get_row();

        $l_bits = isys_convert::speed_wan($p_capacity, $l_row["isys_wan_capacity_unit__const"]);

        $l_strSql = 'INSERT IGNORE INTO isys_cats_wan_list
            SET isys_cats_wan_list__description = ' . $this->convert_sql_text($p_description) . ',
            isys_cats_wan_list__capacity = ' . $this->convert_sql_text($l_bits) . ',
            isys_cats_wan_list__isys_wan_type__id = ' . $this->convert_sql_id($p_typeID) . ',
            isys_cats_wan_list__isys_wan_role__id = ' . $this->convert_sql_id($p_roleID) . ',
            isys_cats_wan_list__isys_wan_capacity_unit__id = ' . $this->convert_sql_id($p_capacityUnitID) . ',
            isys_cats_wan_list__status = ' . $this->convert_sql_id($p_newRecStatus) . ',
            isys_cats_wan_list__isys_obj__id = ' . $this->convert_sql_id($p_objID) . ';';

        if ($this->update($l_strSql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function dynamic_properties()
    {
        return [
            '_capacity' => new DynamicProperty(
                'LC__CMDB__CATS__WAN_CAPACITY',
                'isys_cats_wan_list__isys_obj__id',
                'isys_cats_wan_list',
                [
                    $this,
                    'dynamic_property_callback_capacity'
                ]
            ),
        ];
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_cats_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_cats_wan_list " . "INNER JOIN isys_obj " . "ON isys_obj__id = isys_cats_wan_list__isys_obj__id " . "LEFT JOIN isys_wan_capacity_unit " .
            "ON isys_wan_capacity_unit__id = isys_cats_wan_list__isys_wan_capacity_unit__id " . "LEFT JOIN isys_wan_type " .
            "ON isys_wan_type__id = isys_cats_wan_list__isys_wan_type__id " . "LEFT JOIN isys_wan_role " . "ON isys_wan_role__id = isys_cats_wan_list__isys_wan_role__id " .
            "WHERE TRUE " . $p_condition . " ";

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_cats_list_id !== null) {
            $l_sql .= "AND isys_cats_wan_list__id = " . $this->convert_sql_id($p_cats_list_id) . " ";
        }

        if ($p_status !== null) {
            $l_sql .= "AND isys_cats_wan_list__status = " . $this->convert_sql_int($p_status) . " ";
        }

        return $this->retrieve($l_sql . ";");
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'role' => (new DialogPlusProperty(
                'C__CATS__WAN__ROLE',
                'LC__CMDB__CATS__WAN_ROLE',
                'isys_cats_wan_list__isys_wan_role__id',
                'isys_cats_wan_list',
                'isys_wan_role'
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false
            ]),
            'type' => (new DialogPlusProperty(
                'C__CATS__WAN__TYPE',
                'LC__CMDB__CATS__WAN_TYPE',
                'isys_cats_wan_list__isys_wan_type__id',
                'isys_cats_wan_list',
                'isys_wan_type'
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false
            ]),
            'capacity'      => array_replace_recursive(isys_cmdb_dao_category_pattern::double(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__WAN_CAPACITY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Capacity'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_wan_list__capacity',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(ROUND(isys_cats_wan_list__capacity / isys_wan_capacity_unit__factor), \' \', isys_wan_capacity_unit__title)
                            FROM isys_cats_wan_list
                            INNER JOIN isys_wan_capacity_unit ON isys_wan_capacity_unit__id = isys_cats_wan_list__isys_wan_capacity_unit__id',
                        'isys_cats_wan_list',
                        'isys_cats_wan_list__id',
                        'isys_cats_wan_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_wan_list', 'LEFT', 'isys_cats_wan_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_wan_capacity_unit',
                            'LEFT',
                            'isys_cats_wan_list__isys_wan_capacity_unit__id',
                            'isys_wan_capacity_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__WAN_CAPACITY',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-medium'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['speed_wan']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'capacity_unit'
                ]
            ]),
            'capacity_unit' => (new DialogPlusProperty(
                'C__CATS__WAN__UNIT',
                'LC__CMDB__CATS__WAN_CAPACTIY_UNIT',
                'isys_cats_wan_list__isys_wan_capacity_unit__id',
                'isys_cats_wan_list',
                'isys_wan_capacity_unit'
            ))->mergePropertyUiParams([
                'p_strClass'        => 'input-mini ml20',
                'p_bInfoIconSpacer' => 0
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false
            ]),
            'description'   => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_wan_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__WAN', 'C__CATS__WAN')
                ]
            ])
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            // Create category data identifier if needed.
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                $p_category_data['data_id'] = $this->create_connector('isys_cats_wan_list', $p_object_id);
            }
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data.
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    C__RECORD_STATUS__NORMAL,
                    $p_category_data['properties']['role'][C__DATA__VALUE],
                    $p_category_data['properties']['type'][C__DATA__VALUE],
                    $p_category_data['properties']['capacity'][C__DATA__VALUE],
                    $p_category_data['properties']['capacity_unit'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE]
                );
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }
}
