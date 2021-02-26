<?php

/**
 * i-doit
 *
 * DAO: specific category for chassis slots.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @since       1.0
 */
class isys_cmdb_dao_category_s_chassis_slot extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'chassis_slot';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Returns the assigned devices to a slot.
     *
     * @param   isys_request $p_req
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_assigned_devices(isys_request $p_req)
    {
        // Retrieve the available slots and prepare the dialog-list data.
        $l_return = [];

        $l_assigned_slots = array_keys($this->get_assigned_chassis_items_by_cat_id($p_req->get_category_data_id()));
        $l_slot_res = isys_cmdb_dao_category_s_chassis::instance($this->m_db)
            ->get_data(null, $p_req->get_object_id(), '', null, C__RECORD_STATUS__NORMAL);

        $l_chassis_dao = isys_cmdb_dao_category_s_chassis::instance($this->m_db);

        while ($l_slot_row = $l_slot_res->get_row()) {
            $l_return[] = [
                'id'  => $l_slot_row['isys_cats_chassis_list__id'],
                'val' => $l_chassis_dao->get_assigned_device_title_by_cat_id($l_slot_row['isys_cats_chassis_list__id'], false),
                'sel' => in_array($l_slot_row['isys_cats_chassis_list__id'], $l_assigned_slots)
            ];
        }

        return $l_return;
    }

    /**
     * Assign multiple chassis slots to a chassis item.
     *
     * @param   integer $p_cat_id
     *
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function assign_chassis_item_from_post($p_cat_id)
    {
        $currentAssignments = [];
        $selection = explode(',', $_POST['C__CMDB__CATS__CHASSIS__ITEM_ASSIGNMENT__selected_values']);

        // @see  ID-4980  We want to write logbook entries to all newly added and removed connected devices.
        $sql = 'SELECT isys_cats_chassis_list__id
            FROM isys_cats_chassis_list_2_isys_cats_chassis_slot_list
            WHERE isys_cats_chassis_slot_list__id = ' . $this->convert_sql_id($p_cat_id) . ';';

        $result = $this->retrieve($sql);

        while ($row = $result->get_row()) {
            $currentAssignments[] = $row['isys_cats_chassis_list__id'];

            if (!in_array($row['isys_cats_chassis_list__id'], $selection)) {
                $this->addLogbookEntryDeleted($row['isys_cats_chassis_list__id']);
            }
        }

        foreach ($selection as $assignment) {
            if (!in_array($assignment, $currentAssignments)) {
                $this->addLogbookEntryCreated($assignment);
            }
        }

        // First delete all assignments.
        $this->remove_chassis_item_assignments($p_cat_id);

        if (is_array($selection)) {
            foreach ($selection as $l_item) {
                $this->assign_chassis_item_to_slot($l_item, $p_cat_id);
            }
        }
    }

    /**
     * @param   integer $chassisId
     *
     * @return  void
     * @throws  isys_exception_database
     * @see     ID-4980  We want to write logbook entries to all newly attached objects.
     */
    private function addLogbookEntryCreated($chassisId)
    {
        $row = isys_cmdb_dao_category_s_chassis::instance($this->m_db)
            ->get_data($chassisId)
            ->get_row();

        if ($row['isys_connection__isys_obj__id'] > 0) {
            $objectData = $this->get_object($row['isys_connection__isys_obj__id'])
                ->get_row();
            $message = isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATS__CHASSIS_SLOTS__OBJECT_WAS_ASSIGNED', ($row['isys_obj__title'] . ' (#' . $row['isys_obj__id'] . ')'));

            isys_component_dao_logbook::instance($this->m_db)
                ->set_entry($message, '', null, defined_or_default('C__LOGBOOK__ALERT_LEVEL__0', 1), $row['isys_connection__isys_obj__id'], $objectData['isys_obj__title'],
                    $objectData['isys_obj_type__title'], 'LC__CMDB__CATS__CHASSIS_SLOTS');
        }
    }

    /**
     * @param   integer $chassisId
     *
     * @return  void
     * @throws  isys_exception_database
     * @see     ID-4980  We want to write logbook entries to all newly detached objects.
     */
    private function addLogbookEntryDeleted($chassisId)
    {
        $row = isys_cmdb_dao_category_s_chassis::instance($this->m_db)
            ->get_data($chassisId)
            ->get_row();

        if ($row['isys_connection__isys_obj__id'] > 0) {
            $objectData = $this->get_object($row['isys_connection__isys_obj__id'])
                ->get_row();
            $message = isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATS__CHASSIS_SLOTS__OBJECT_WAS_DETACHED', ($row['isys_obj__title'] . ' (#' . $row['isys_obj__id'] . ')'));

            isys_component_dao_logbook::instance($this->m_db)
                ->set_entry($message, '', null, defined_or_default('C__LOGBOOK__ALERT_LEVEL__0', 1), $row['isys_connection__isys_obj__id'], $objectData['isys_obj__title'],
                    $objectData['isys_obj_type__title'], 'LC__CMDB__CATS__CHASSIS_SLOTS');
        }
    }

    /**
     * Assign a chassis item to a chassis slot.
     *
     * @param   integer $p_chassis_cat_id
     * @param   integer $p_slot
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function assign_chassis_item_to_slot($p_chassis_cat_id, $p_slot)
    {
        if (empty($p_chassis_cat_id) || empty($p_slot)) {
            return null;
        }

        $l_sql = 'INSERT INTO  isys_cats_chassis_list_2_isys_cats_chassis_slot_list (
			isys_cats_chassis_slot_list__id,
			isys_cats_chassis_list__id
			) VALUES (
			' . $this->convert_sql_id($p_slot) . ',
			' . $this->convert_sql_id($p_chassis_cat_id) . '
			);';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Assign multiple chassis slots to a chassis item.
     *
     * @param   integer $p_cat_id
     * @param   integer $p_x_from
     * @param   integer $p_x_to
     * @param   integer $p_y_from
     * @param   integer $p_y_to
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function assign_chassis_slot_position($p_cat_id, $p_x_from, $p_x_to, $p_y_from, $p_y_to)
    {
        $l_sql = 'UPDATE isys_cats_chassis_slot_list
			SET isys_cats_chassis_slot_list__x_from = ' . $this->convert_sql_int($p_x_from) . ',
			isys_cats_chassis_slot_list__x_to = ' . $this->convert_sql_int($p_x_to) . ',
			isys_cats_chassis_slot_list__y_from = ' . $this->convert_sql_int($p_y_from) . ',
			isys_cats_chassis_slot_list__y_to = ' . $this->convert_sql_int($p_y_to) . '
			WHERE  isys_cats_chassis_slot_list__id = ' . $this->convert_sql_id($p_cat_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Method for checking, if the given position is already took by another slot.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_x_from
     * @param   integer $p_x_to
     * @param   integer $p_y_from
     * @param   integer $p_y_to
     * @param   integer $p_insertion
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function check_for_colliding_slots($p_obj_id, $p_x_from, $p_x_to, $p_y_from, $p_y_to, $p_insertion = C__INSERTION__FRONT)
    {
        // Check if the given coordinates cross or lie inside already existing coordinates.
        $l_sql = 'SELECT isys_cats_chassis_slot_list__id
			FROM isys_cats_chassis_slot_list
			WHERE isys_cats_chassis_slot_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_cats_chassis_slot_list__insertion = ' . $this->convert_sql_int($p_insertion) . '
			AND ((' . $this->convert_sql_int($p_x_from) . ' BETWEEN isys_cats_chassis_slot_list__x_from AND isys_cats_chassis_slot_list__x_to)
			OR (' . $this->convert_sql_int($p_x_to) . ' BETWEEN isys_cats_chassis_slot_list__x_from AND isys_cats_chassis_slot_list__x_to))
			AND ((' . $this->convert_sql_int($p_y_from) . ' BETWEEN isys_cats_chassis_slot_list__y_from AND isys_cats_chassis_slot_list__y_to)
			OR (' . $this->convert_sql_int($p_y_to) . ' BETWEEN isys_cats_chassis_slot_list__y_from AND isys_cats_chassis_slot_list__y_to))
			LIMIT 1;';

        if ($this->retrieve($l_sql)
                ->num_rows() > 0) {
            return true;
        }

        // Check if the given coordinates surround other coordinates.
        $l_sql = 'SELECT isys_cats_chassis_slot_list__id
			FROM isys_cats_chassis_slot_list
			WHERE isys_cats_chassis_slot_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_cats_chassis_slot_list__insertion = ' . $this->convert_sql_int($p_insertion) . '
			AND isys_cats_chassis_slot_list__x_from > ' . $this->convert_sql_int($p_x_from) . '
			AND isys_cats_chassis_slot_list__x_to < ' . $this->convert_sql_int($p_x_to) . '
			AND isys_cats_chassis_slot_list__y_from > ' . $this->convert_sql_int($p_y_from) . '
			AND isys_cats_chassis_slot_list__y_to < ' . $this->convert_sql_int($p_y_to) . '
			LIMIT 1;';

        if ($this->retrieve($l_sql)
                ->num_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Method for detaching all slots, which are outside the grid-bounds.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_x
     * @param   integer $p_y
     * @param   integer $p_insertion
     *
     * @return  isys_cmdb_dao_category_s_chassis_slot
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function detach_slots_outside_of_bounds($p_obj_id, $p_x, $p_y, $p_insertion = C__INSERTION__FRONT)
    {
        $l_sql = 'SELECT isys_cats_chassis_slot_list__id
			FROM isys_cats_chassis_slot_list
			WHERE isys_cats_chassis_slot_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			AND (isys_cats_chassis_slot_list__x_to > ' . $this->convert_sql_int($p_x - 1) . '
			OR isys_cats_chassis_slot_list__y_to > ' . $this->convert_sql_int($p_y - 1) . ')
			AND isys_cats_chassis_slot_list__insertion = ' . $this->convert_sql_int($p_insertion) . ';';

        $l_res = $this->retrieve($l_sql);

        while ($l_row = $l_res->get_row()) {
            $this->remove_assigned_slot_position($l_row['isys_cats_chassis_slot_list__id']);
        }

        return $this;
    }

    /**
     * Retrieve the chassis-items, assigned to the given slot.
     *
     * @param   integer $p_cat_id
     *
     * @return  array
     */
    public function get_assigned_chassis_items_by_cat_id($p_cat_id)
    {
        $l_return = [];
        $l_sql = 'SELECT c.*
			FROM isys_cats_chassis_list_2_isys_cats_chassis_slot_list c2cs
			LEFT JOIN isys_cats_chassis_slot_list cs ON cs.isys_cats_chassis_slot_list__id = c2cs.isys_cats_chassis_slot_list__id
			LEFT JOIN isys_cats_chassis_list c ON c.isys_cats_chassis_list__id = c2cs.isys_cats_chassis_list__id
			WHERE cs.isys_cats_chassis_slot_list__id = ' . $this->convert_sql_id($p_cat_id) . '
			AND c.isys_cats_chassis_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			AND cs.isys_cats_chassis_slot_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        $l_res = $this->retrieve($l_sql);

        while ($l_row = $l_res->get_row()) {
            $l_return[$l_row['isys_cats_chassis_list__id']] = $l_row;
        }

        return $l_return;
    }

    /**
     * Method for reseting an assigned position.
     *
     * @param   integer $p_cat_id
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function remove_assigned_slot_position($p_cat_id)
    {
        $l_sql = 'UPDATE isys_cats_chassis_slot_list
			SET isys_cats_chassis_slot_list__x_from = NULL,
			isys_cats_chassis_slot_list__x_to = NULL,
			isys_cats_chassis_slot_list__y_from = NULL,
			isys_cats_chassis_slot_list__y_to = NULL
			WHERE  isys_cats_chassis_slot_list__id = ' . $this->convert_sql_id($p_cat_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Method for removing all chassis-item assignments from a chassis-slot.
     *
     * @param   integer $p_cat_id
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function remove_chassis_item_assignments($p_cat_id)
    {
        $l_sql = 'DELETE FROM isys_cats_chassis_list_2_isys_cats_chassis_slot_list
			WHERE isys_cats_chassis_slot_list__id = ' . $this->convert_sql_id($p_cat_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Save method.
     *
     * @param   integer $p_unused_1
     * @param   integer $p_unused_2
     * @param   boolean $p_create
     *
     * @return  mixed|void
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_element($p_unused_1, $p_unused_2, $p_create)
    {
        $l_obj_id = $_GET[C__CMDB__GET__OBJECT];
        $l_insertion = $_POST['C__CMDB__CATS__CHASSIS_SLOT__INSERTION'];
        $l_connector_type = $_POST['C__CMDB__CATS__CHASSIS_SLOT__CONNECTOR_TYPE'];
        $l_description = $_POST['C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__CHASSIS_SLOT', 'C__CATS__CHASSIS_SLOT')];

        $l_amount = $_POST['C__CMDB__CATS__CHASSIS_SLOT__SUFFIX_COUNT'];

        if ($l_amount >= 1 && $p_create) {
            $l_title_arr = isys_smarty_plugin_f_title_suffix_counter::generate_title_as_array($_POST, 'C__CMDB__CATS__CHASSIS_SLOT', 'C__CMDB__CATS__CHASSIS_SLOT__TITLE');

            for ($i = 0;$i < $l_amount;$i++) {
                $l_slot_title = $l_title_arr[$i];

                $l_data = [
                    'isys_obj__id'   => $l_obj_id,
                    'status'         => C__RECORD_STATUS__NORMAL,
                    'title'          => $l_slot_title,
                    'description'    => $l_description,
                    'insertion'      => $l_insertion,
                    'connector_type' => $l_connector_type
                ];

                $this->create_data($l_data);
            }
        } else {
            $this->assign_chassis_item_from_post($_GET[C__CMDB__GET__CATLEVEL]);

            // The generic save method messed up, so we wrote an own query.
            $l_sql = 'UPDATE isys_cats_chassis_slot_list
				SET isys_cats_chassis_slot_list__isys_obj__id = ' . $this->convert_sql_id($_GET[C__CMDB__GET__OBJECT]) . ',
				isys_cats_chassis_slot_list__isys_chassis_connector_type__id = ' . $this->convert_sql_id($_POST['C__CMDB__CATS__CHASSIS_SLOT__CONNECTOR_TYPE']) . ',
				isys_cats_chassis_slot_list__insertion = ' . $this->convert_sql_int($_POST['C__CMDB__CATS__CHASSIS_SLOT__INSERTION']) . ',
				isys_cats_chassis_slot_list__title = ' . $this->convert_sql_text($_POST['C__CMDB__CATS__CHASSIS_SLOT__TITLE']) . ',
				isys_cats_chassis_slot_list__description = ' .
                $this->convert_sql_text($_POST['C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__CHASSIS_SLOT', 'C__CATS__CHASSIS_SLOT')]) . '
				WHERE isys_cats_chassis_slot_list__id = ' . $this->convert_sql_id($_GET[C__CMDB__GET__CATLEVEL]) . ';';

            if ($this->update($l_sql) && $this->apply_update()) {
                return (int)$_GET[C__CMDB__GET__CATLEVEL];
            }

            return false;
        }
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function properties()
    {
        return [
            'connector_type'   => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO   => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS__CONNECTOR_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Connector type'
                ],
                C__PROPERTY__DATA   => [
                    C__PROPERTY__DATA__FIELD        => 'isys_cats_chassis_slot_list__isys_chassis_connector_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_chassis_connector_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_chassis_connector_type',
                        'isys_chassis_connector_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_chassis_connector_type__title
                            FROM isys_cats_chassis_slot_list
                            INNER JOIN isys_chassis_connector_type ON isys_chassis_connector_type__id = isys_cats_chassis_slot_list__isys_chassis_connector_type__id',
                        'isys_cats_chassis_slot_list', 'isys_cats_chassis_slot_list__id', 'isys_cats_chassis_slot_list__isys_obj__id', '', '', null,
                        \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_chassis_slot_list__isys_obj__id'])),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_chassis_slot_list', 'LEFT', 'isys_cats_chassis_slot_list__isys_obj__id',
                            'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_chassis_connector_type', 'LEFT',
                            'isys_cats_chassis_slot_list__isys_chassis_connector_type__id', 'isys_chassis_connector_type__id')
                    ]
                ],
                C__PROPERTY__UI     => [
                    C__PROPERTY__UI__ID      => 'C__CMDB__CATS__CHASSIS_SLOT__CONNECTOR_TYPE',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_strTable' => 'isys_chassis_connector_type'
                    ],
                    C__PROPERTY__UI__DEFAULT => '-1'
                ],
                C__PROPERTY__FORMAT => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_plus'
                    ]
                ]
            ]),
            'insertion'        => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS__INSERTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Insertion'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_chassis_slot_list__insertion',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT (CASE
                                WHEN isys_cats_chassis_slot_list__insertion = ' . C__INSERTION__FRONT . ' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__FRONT') . '
                                WHEN isys_cats_chassis_slot_list__insertion = ' . C__INSERTION__REAR . ' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__REAR') . '
                            END)
                            FROM isys_cats_chassis_slot_list', 'isys_cats_chassis_slot_list', 'isys_cats_chassis_slot_list__id', 'isys_cats_chassis_slot_list__isys_obj__id',
                        '', '', null, \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_chassis_slot_list__isys_obj__id'])),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_chassis_slot_list', 'LEFT', 'isys_cats_chassis_slot_list__isys_obj__id',
                            'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__CHASSIS_SLOT__INSERTION',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_s_chassis',
                            'callback_property_insertion'
                        ])
                    ]
                ]
            ]),
            'title'            => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS_SLOT_TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_chassis_slot_list__title',
                    C__PROPERTY__DATA__INDEX  => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_cats_chassis_slot_list__title FROM isys_cats_chassis_slot_list',
                        'isys_cats_chassis_slot_list', 'isys_cats_chassis_slot_list__id', 'isys_cats_chassis_slot_list__isys_obj__id', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_chassis_slot_list__isys_obj__id']))
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__CHASSIS_SLOT__TITLE'
                ]
            ]),
            'from_x'           => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS__POSITION_X',
                    C__PROPERTY__INFO__DESCRIPTION => 'Position X'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_chassis_slot_list__x_from'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__CHASSIS__POSITION_X',
                    // Virtual field.
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL   => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__LIST      => false
                ]
            ]),
            'to_x'             => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS__POSITION_X_TO',
                    C__PROPERTY__INFO__DESCRIPTION => 'To position X'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_chassis_slot_list__x_to'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__CHASSIS__POSITION_X2',
                    // Virtual field.
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL   => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__LIST      => false
                ]
            ]),
            'from_y'           => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS__POSITION_Y',
                    C__PROPERTY__INFO__DESCRIPTION => 'Position Y'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_chassis_slot_list__y_from'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__CHASSIS__POSITION_Y',
                    // Virtual field.
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL   => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__LIST      => false
                ]
            ]),
            'to_y'             => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS__POSITION_Y_TO',
                    C__PROPERTY__INFO__DESCRIPTION => 'To position Y'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_chassis_slot_list__y_to'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__CHASSIS__POSITION_Y2',
                    // Virtual field.
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL   => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__LIST      => false
                ]
            ]),
            'assigned_devices' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_list(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES',
                    C__PROPERTY__INFO__DESCRIPTION => 'Device assignment'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_chassis_slot_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT
                              CONCAT((CASE
                                WHEN isys_connection__isys_obj__id > 0 THEN isys_obj__title
                                WHEN ch.isys_cats_chassis_list__isys_catg_hba_list__id > 0 THEN isys_catg_hba_list__title
                                WHEN ch.isys_cats_chassis_list__isys_catg_netp_list__id > 0 THEN isys_catg_netp_list__title
                                WHEN ch.isys_cats_chassis_list__isys_catg_pc_list__id > 0 THEN isys_catg_pc_list__title
                              END), " (", isys_cats_chassis_slot_list__title, ")")
                            FROM isys_cats_chassis_slot_list AS main
                            INNER JOIN isys_cats_chassis_list_2_isys_cats_chassis_slot_list AS ch2slot ON ch2slot.isys_cats_chassis_slot_list__id = main.isys_cats_chassis_slot_list__id
                            INNER JOIN isys_cats_chassis_list AS ch ON ch.isys_cats_chassis_list__id = ch2slot.isys_cats_chassis_list__id
                            LEFT JOIN isys_connection ON isys_connection__id = ch.isys_cats_chassis_list__isys_connection__id
                            LEFT JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id
                            LEFT JOIN isys_catg_hba_list ON isys_catg_hba_list__id = ch.isys_cats_chassis_list__isys_catg_hba_list__id
                            LEFT JOIN isys_catg_netp_list ON isys_catg_netp_list__id = ch.isys_cats_chassis_list__isys_catg_netp_list__id
                            LEFT JOIN isys_catg_pc_list ON isys_catg_pc_list__id = ch.isys_cats_chassis_list__isys_catg_pc_list__id', 'isys_cats_chassis_slot_list',
                        'main.isys_cats_chassis_slot_list__id', 'main.isys_cats_chassis_slot_list__isys_obj__id', '', '', null,
                        \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['main.isys_cats_chassis_slot_list__isys_obj__id']))
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__CHASSIS__ITEM_ASSIGNMENT',
                    C__PROPERTY__UI__PARAMS => [
                        'multiselection' => true,
                        'p_arData'       => new isys_callback([
                            'isys_cmdb_dao_category_s_chassis_slot',
                            'callback_property_assigned_devices'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__LIST      => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__REPORT    => true,
                    C__PROPERTY__PROVIDES__VIRTUAL   => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'chassis_devices'
                    ]
                ]
            ]),
            'description'      => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_chassis_slot_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_cats_chassis_slot_list__description FROM isys_cats_chassis_slot_list',
                        'isys_cats_chassis_slot_list', 'isys_cats_chassis_slot_list__id', 'isys_cats_chassis_slot_list__isys_obj__id', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_chassis_slot_list__isys_obj__id']))
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__CHASSIS_SLOT', 'C__CATS__CHASSIS_SLOT')
                ]
            ])
        ];
    }

    /**
     * @param array   $p_category_data
     * @param integer $p_object_id
     * @param integer $p_status
     *
     * @return mixed
     * @throws isys_exception_database
     * @throws isys_exception_validation
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $return = parent::sync($p_category_data, $p_object_id, $p_status);

        $l_chassis_id = $p_category_data['data_id'] ?: $return;

        if ($l_chassis_id > 0 && isset($p_category_data['properties']['assigned_devices'][C__DATA__VALUE]) &&
            is_array($p_category_data['properties']['assigned_devices'][C__DATA__VALUE])) {
            foreach ($p_category_data['properties']['assigned_devices'][C__DATA__VALUE] as $l_slot_item) {
                if ($l_slot_item > 0) {
                    // @see ID-4980
                    $this->addLogbookEntryCreated($l_chassis_id);
                    $this->assign_chassis_item_to_slot($l_chassis_id, $l_slot_item);
                }
            }
        }

        return $return;
    }
}
