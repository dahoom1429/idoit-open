<?php

use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: specific category for emergency plans.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_emergency_plan extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'emergency_plan';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Dynamic property handling for retrieving the object ID.
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   array                   $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_time_needed(array $p_row)
    {
        global $g_comp_database;

        $objId = ($p_row['isys_cats_emergency_plan_list__isys_obj__id'] ?: ($p_row['isys_obj__id'] ?: null));

        if ($objId === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        $l_return = '';
        $l_dao = isys_cmdb_dao_category_s_emergency_plan::instance($g_comp_database);
        $l_row = $l_dao->get_data(null, $objId)
            ->get_row();

        if ($l_row['isys_cats_emergency_plan_list__calc_time_need'] > 0) {
            $l_unit_row = $l_dao->get_dialog('isys_unit_of_time', $l_row['isys_cats_emergency_plan_list__isys_unit_of_time__id'])
                ->get_row();

            $l_return = isys_convert::time(
                $l_row['isys_cats_emergency_plan_list__calc_time_need'],
                $l_row['isys_cats_emergency_plan_list__isys_unit_of_time__id'],
                C__CONVERT_DIRECTION__BACKWARD
            ) . ' ' . isys_application::instance()->container->get('language')
                    ->get($l_unit_row['isys_unit_of_time__title']);
        }

        return $l_return;
    }

    /**
     * @param integer $p_cat_level
     * @param integer &$p_intOldRecStatus
     *
     * @version Niclas Potthast <npotthast@i-doit.org> - 2006-11-28
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_cats_emergency_plan_list__status"];

        $l_list_id = $l_catdata["isys_cats_emergency_plan_list__id"];

        if (empty($l_list_id)) {
            $l_list_id = $this->create_connector("isys_cats_emergency_plan_list", $_GET[C__CMDB__GET__OBJECT]);
        }

        $_POST['C__CATS__EMERGENCY_PLAN_PRACTICE_ACTUAL_DATE__HIDDEN'] = date('Y-m-d H:i:s', strtotime($_POST['C__CATS__EMERGENCY_PLAN_PRACTICE_ACTUAL_DATE__HIDDEN']));

        $l_bRet = $this->save(
            $l_list_id,
            C__RECORD_STATUS__NORMAL,
            $_POST['C__CATS__EMERGENCY_PLAN_CALC_TIME_NEEDED'],
            $_POST["C__CATS__EMERGENCY_PLAN_UNIT_OF_TIME"],
            $_POST['C__CATS__EMERGENCY_PLAN_PRACTICE_ACTUAL_DATE__HIDDEN'],
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
     * @param   integer $p_time
     * @param   integer $p_timePeriodID
     * @param   mixed   $p_practiseDate
     * @param   string  $p_description
     *
     * @return  boolean
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_time, $p_timePeriodID, $p_practiseDate, $p_description)
    {
        $l_row = $this->get_dialog("isys_unit_of_time", $p_timePeriodID)
            ->get_row();

        $l_sql = 'UPDATE isys_cats_emergency_plan_list SET
			isys_cats_emergency_plan_list__isys_unit_of_time__id = ' . $this->convert_sql_id($p_timePeriodID) . ',
			isys_cats_emergency_plan_list__calc_time_need = ' . $this->convert_sql_int(isys_convert::time($p_time, $l_row['isys_unit_of_time__const'])) . ',
			isys_cats_emergency_plan_list__practice_actual_date = ' . $this->convert_sql_datetime($p_practiseDate) . ',
			isys_cats_emergency_plan_list__description = ' . $this->convert_sql_text($p_description) . ',
			isys_cats_emergency_plan_list__status = ' . $this->convert_sql_id($p_newRecStatus) . '
			WHERE isys_cats_emergency_plan_list__id = ' . $this->convert_sql_id($p_cat_level);

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Executes the query to create the category entry.
     *
     * @param   integer $p_objID
     * @param   integer $p_newRecStatus
     * @param   integer $p_time
     * @param   integer $p_timePeriodID
     * @param   mixed   $p_practiseDate
     * @param   string  $p_description
     *
     * @return  mixed
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus, $p_time, $p_timePeriodID, $p_practiseDate, $p_description)
    {
        $l_row = $this->get_dialog('isys_unit_of_time', $p_timePeriodID)
            ->get_row();

        $l_timeSeconds = isys_convert::time($p_time, $l_row['isys_unit_of_time__const']);

        $l_sql = 'INSERT IGNORE INTO isys_cats_emergency_plan_list SET
			isys_cats_emergency_plan_list__isys_unit_of_time__id = ' . $this->convert_sql_id($p_timePeriodID) . ',
			isys_cats_emergency_plan_list__calc_time_need = ' . $this->convert_sql_id($l_timeSeconds) . ',
			isys_cats_emergency_plan_list__practice_actual_date = ' . $this->convert_sql_datetime($p_practiseDate) . ',
			isys_cats_emergency_plan_list__description = ' . $this->convert_sql_text($p_description) . ',
			isys_cats_emergency_plan_list__isys_obj__id = ' . $this->convert_sql_id($p_objID) . ',
			isys_cats_emergency_plan_list__status = ' . $this->convert_sql_id($p_newRecStatus) . ';';

        if ($this->update($l_sql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    /**
     * Method for returning the dynamic properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function dynamic_properties()
    {
        return [
            '_time_needed' => new DynamicProperty(
                'LC__CMDB__CATS__EMERGENCY_PLAN_CALC_TIME_NEEDED',
                'isys_cats_emergency_plan_list__isys_obj__id',
                'isys_cats_emergency_plan_list',
                [
                    $this,
                    'dynamic_property_callback_time_needed'
                ]
            ),
        ];
    }

    /**
     * Returns how many entries exists. The folder only needs to know if there are any entries in its subcategories.
     *
     * @param null $p_obj_id
     *
     * @return int
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_count($p_obj_id = null)
    {
        if ($this->get_category_id() == defined_or_default('C__CATS__EMERGENCY_PLAN')) {
            $l_sql = 'SELECT
				(
				IFNULL((SELECT isys_cats_emergency_plan_list__id AS cnt FROM isys_cats_emergency_plan_list
					WHERE isys_cats_emergency_plan_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' LIMIT 1), 0)
				+
				IFNULL((SELECT isys_catg_emergency_plan_list__id AS cnt FROM isys_catg_emergency_plan_list
					INNER JOIN isys_connection ON isys_connection__id = isys_catg_emergency_plan_list__isys_connection__id
					WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' LIMIT 1), 0)
				)
				AS cnt';

            return $this->retrieve($l_sql)
                ->get_row_value('cnt');
        } else {
            return parent::get_count($p_obj_id);
        }
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_cats_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT * FROM isys_cats_emergency_plan_list
			INNER JOIN isys_obj ON isys_obj__id = isys_cats_emergency_plan_list__isys_obj__id
			LEFT JOIN isys_unit_of_time ON isys_cats_emergency_plan_list__isys_unit_of_time__id = isys_unit_of_time__id
			WHERE TRUE " . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_cats_list_id !== null) {
            $l_sql .= " AND isys_cats_emergency_plan_list__id = " . $this->convert_sql_id($p_cats_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_cats_emergency_plan_list__status = " . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'time_needed'   => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__EMERGENCY_PLAN_CALC_TIME_NEEDED',
                    C__PROPERTY__INFO__DESCRIPTION => 'Time need'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_emergency_plan_list__calc_time_need',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(ROUND(isys_cats_emergency_plan_list__calc_time_need / isys_unit_of_time__factor), \' \', isys_unit_of_time__title)
                            FROM isys_cats_emergency_plan_list
                            INNER JOIN isys_unit_of_time ON isys_unit_of_time__id = isys_cats_emergency_plan_list__isys_unit_of_time__id',
                        'isys_cats_emergency_plan_list',
                        'isys_cats_emergency_plan_list__id',
                        'isys_cats_emergency_plan_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_emergency_plan_list',
                            'LEFT',
                            'isys_cats_emergency_plan_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_unit_of_time',
                            'LEFT',
                            'isys_cats_emergency_plan_list__isys_unit_of_time__id',
                            'isys_unit_of_time__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__EMERGENCY_PLAN_CALC_TIME_NEEDED',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-mini'
                    ],
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['time']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'time_unit'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'time_unit' => (new DialogProperty(
                'C__CATS__EMERGENCY_PLAN_UNIT_OF_TIME',
                'LC__CMDB__CATG__UNIT',
                'isys_cats_emergency_plan_list__isys_unit_of_time__id',
                'isys_cats_emergency_plan_list',
                'isys_unit_of_time'
            ))->mergePropertyUiParams([
                'p_strClass' => 'input-mini'
            ]),
            'practice_date' => array_replace_recursive(isys_cmdb_dao_category_pattern::datetime(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__EMERGENCY_PLAN_PRACTICE_ACTUAL_DATE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Date of emergency practice'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_emergency_plan_list__practice_actual_date'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__EMERGENCY_PLAN_PRACTICE_ACTUAL_DATE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-small',
                        'p_strStyle' => 'width:70%;',
                        'p_bTime'    => true
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'description'   => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_emergency_plan_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__EMERGENCY_PLAN', 'C__CATS__EMERGENCY_PLAN')
                ]
            ])
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param array $p_category_data Values of category data to be saved.
     * @param int   $p_object_id     Current object identifier (from database)
     * @param int   $p_status        Decision whether category data should be created or
     *                               just updated.
     *
     * @return mixed Returns category data identifier (int) on success, true
     * (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                $p_category_data['data_id'] = $this->create_connector('isys_cats_emergency_plan_list', $p_object_id);
            }
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data:
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    C__RECORD_STATUS__NORMAL,
                    $p_category_data['properties']['time_needed'][C__DATA__VALUE],
                    $p_category_data['properties']['time_unit'][C__DATA__VALUE],
                    $p_category_data['properties']['practice_date'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE]
                );
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }
}
