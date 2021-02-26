<?php

use idoit\Component\Location\Coordinate;
use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DynamicProperty;
use idoit\Component\Property\Type\ObjectBrowserConnectionProperty;
use idoit\Component\Property\Type\VirtualProperty;

/**
 * i-doit
 *
 * DAO: specific category for chassis enclosure.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.0
 */
class isys_cmdb_dao_category_s_chassis extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'chassis';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'assigned_device';

    /**
     * Category's constant.
     *
     * @var  string
     */
    protected $m_category_const = 'C__CATS__CHASSIS_DEVICES';

    /**
     * @var bool
     */
    protected $m_has_relation = true;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var bool
     */
    protected $m_multivalued = true;

    /**
     * @var string
     */
    protected $m_object_id_field = 'isys_cats_chassis_list__isys_obj__id';

    /**
     * Returns the possible device types.
     *
     * @static
     * @return  array
     * @throws Exception
     */
    public static function get_assigned_device_types()
    {
        return [
            0 => isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATG__ASSIGNED_LOGICAL_UNITS__ASSIGN_BUTTON'),
            1 => isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATG__POWER_CONSUMER'),
            2 => isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATG__HBA'),
            3 => isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE')
        ];
    }

    /**
     * Returns the possible chassis-insertions.
     *
     * @static
     * @return  array
     * @throws Exception
     */
    public static function get_insertion()
    {
        return [
            C__INSERTION__FRONT => isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__FRONT'),
            C__INSERTION__REAR  => isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__REAR')
        ];
    }

    /**
     * Returns the possible device type.
     *
     * @param   isys_request $p_req
     *
     * @return  array
     * @throws Exception
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function callback_property_assigned_device_type(isys_request $p_req)
    {
        return isys_cmdb_dao_category_s_chassis::get_assigned_device_types();
    }

    /**
     * Returns the assigned slots to a device.
     *
     * @param array $row
     *
     * @return string
     * @throws isys_exception_database
     */
    public function dynamic_property_callback_assigned_slots($row)
    {
        // Retrieve the available slots and prepare the dialog-list data.
        $l_return = '';
        $glue = '';

        $l_assigned_slots = array_keys($this->get_assigned_slots_by_cat_id($row['isys_cats_chassis_list__id']));

        if (count($l_assigned_slots)) {
            $l_slot_res = isys_cmdb_dao_category_s_chassis_slot::instance($this->m_db)
                ->get_data(null, null, 'AND isys_cats_chassis_slot_list__id IN (' . implode(',', $l_assigned_slots) . ')', null, C__RECORD_STATUS__NORMAL);

            while ($l_slot_row = $l_slot_res->get_row()) {
                $l_return .= $glue . $l_slot_row['isys_cats_chassis_slot_list__title'];
                $glue = ',';
            }
        }

        return $l_return;
    }

    /**
     * @param isys_request $request
     *
     * @return array
     * @throws isys_exception_database
     */
    public function callback_property_assigned_slots(isys_request $request)
    {
        // Retrieve the available slots and prepare the dialog-list data.
        $l_return = [];

        $l_assigned_slots = array_keys($this->get_assigned_slots_by_cat_id($request->get_category_data_id()));
        $l_slot_res = isys_cmdb_dao_category_s_chassis_slot::instance($this->m_db)
            ->get_data(null, $request->get_object_id(), '', null, C__RECORD_STATUS__NORMAL);

        while ($l_slot_row = $l_slot_res->get_row()) {
            $l_return[] = [
                'id'  => $l_slot_row['isys_cats_chassis_slot_list__id'],
                'val' => $l_slot_row['isys_cats_chassis_slot_list__title'],
                'sel' => in_array($l_slot_row['isys_cats_chassis_slot_list__id'], $l_assigned_slots)
            ];
        }

        return $l_return;
    }

    /**
     * Returns the possible chassis-insertions.
     *
     * @param   isys_request $p_req
     *
     * @return  array
     * @throws Exception
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function callback_property_insertion(isys_request $p_req)
    {
        return isys_cmdb_dao_category_s_chassis::get_insertion();
    }

    /**
     * Callback method for the local devices dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @throws isys_exception_database
     */
    public function callback_property_ports(isys_request $p_request)
    {
        return $this->get_local_devices_as_array($p_request->get_object_id());
    }

    /**
     * Assign a chassis item to a chassis slot.
     *
     * @param   integer $p_chassis_cat_id
     * @param   integer $p_slot
     *
     * @return  boolean
     * @throws isys_exception_dao
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function assign_slot_to_chassis_item($p_chassis_cat_id, $p_slot)
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
     * @param   integer $categoryId
     *
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function assign_slots_from_post($categoryId)
    {
        $currentAssignments = [];
        $selection = explode(',', $_POST['C__CMDB__CATS__CHASSIS__SLOT_ASSIGNMENT__selected_values']);

        // @see  ID-4980  We want to write logbook entries to all newly added and removed connected devices.
        $sql = 'SELECT isys_cats_chassis_slot_list__id
            FROM isys_cats_chassis_list_2_isys_cats_chassis_slot_list
            WHERE isys_cats_chassis_list__id = ' . $this->convert_sql_id($categoryId) . ';';

        $result = $this->retrieve($sql);

        while ($row = $result->get_row()) {
            $currentAssignments[] = $row['isys_cats_chassis_slot_list__id'];

            if (!in_array($row['isys_cats_chassis_slot_list__id'], $selection)) {
                $this->addLogbookEntryDeleted($categoryId);
            }
        }

        foreach ($selection as $assignment) {
            if (!in_array($assignment, $currentAssignments)) {
                $this->addLogbookEntryCreated($categoryId);
            }
        }

        // First delete all assignments.
        $this->remove_slot_assignments($categoryId);

        if (is_array($selection)) {
            foreach ($selection as $slot) {
                $this->assign_slot_to_chassis_item($categoryId, $slot);
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
                ->set_entry(
                    $message,
                    '',
                    null,
                    defined_or_default('C__LOGBOOK__ALERT_LEVEL__0', 1),
                    $row['isys_connection__isys_obj__id'],
                    $objectData['isys_obj__title'],
                    $objectData['isys_obj_type__title'],
                    'LC__CMDB__CATS__CHASSIS_SLOTS'
                );
        }
    }

    /**
     * @param   integer $chassisId
     *
     * @return  void
     * @throws  isys_exception_database
     * @see     ID-4980  We want to write logbook entries to all newly detached objects.
     * @throws Exception
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
                ->set_entry(
                    $message,
                    '',
                    null,
                    defined_or_default('C__LOGBOOK__ALERT_LEVEL__0', 1),
                    $row['isys_connection__isys_obj__id'],
                    $objectData['isys_obj__title'],
                    $objectData['isys_obj_type__title'],
                    'LC__CMDB__CATS__CHASSIS_SLOTS'
                );
        }
    }

    /**
     * Create method.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_status
     * @param   integer $p_role
     * @param   string  $p_local_assignment format example: "3_C__CATG__HBA"
     * @param   integer $p_assigned_device
     * @param   string  $p_description
     *
     * @return  mixed
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_dao_cmdb
     * @throws isys_exception_database
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function create($p_obj_id, $p_status = C__RECORD_STATUS__NORMAL, $p_role, $p_local_assignment = null, $p_assigned_device = null, $p_description = null)
    {
        $l_netp_id = null;
        $l_pc_id = null;
        $l_hba_id = null;
        $l_con_id = null;

        /**
         * @var  $l_dao_connection  isys_cmdb_dao_connection
         */
        $l_dao_connection = isys_cmdb_dao_connection::instance($this->m_db);

        if ($p_assigned_device > 0) {
            $l_con_id = $l_dao_connection->add_connection($p_assigned_device);
        } else {
            if ($p_local_assignment) {
                $l_con_id = $l_dao_connection->add_connection(0);
                $l_id = substr($p_local_assignment, 0, strpos($p_local_assignment, '_'));
                $l_type = substr($p_local_assignment, strpos($p_local_assignment, '_') + 1);

                switch ($l_type) {
                    case 'C__CATG__HBA':
                        $l_hba_id = $l_id;
                        break;
                    case 'C__CATG__POWER_CONSUMER':
                        $l_pc_id = $l_id;
                        break;
                    case 'C__CATG__NETWORK_INTERFACE':
                    case 'C__CMDB__SUBCAT__NETWORK_INTERFACE_P': // @todo  Remove in i-doit 1.12
                        $l_netp_id = $l_id;
                        break;
                }
            }
        }

        $l_update = 'INSERT INTO isys_cats_chassis_list (
			isys_cats_chassis_list__isys_obj__id,
			isys_cats_chassis_list__status,
			isys_cats_chassis_list__isys_chassis_role__id,
			isys_cats_chassis_list__isys_connection__id,
			isys_cats_chassis_list__isys_catg_netp_list__id,
			isys_cats_chassis_list__isys_catg_pc_list__id,
			isys_cats_chassis_list__isys_catg_hba_list__id,
			isys_cats_chassis_list__description
			) VALUES (' . $this->convert_sql_id($p_obj_id) . ',' . $this->convert_sql_int($p_status) . ',' . $this->convert_sql_id($p_role) . ',' .
            $this->convert_sql_id($l_con_id) . ',' . $this->convert_sql_id($l_netp_id) . ',' . $this->convert_sql_id($l_pc_id) . ',' . $this->convert_sql_id($l_hba_id) . ',' .
            $this->convert_sql_text($p_description) . ');';

        if ($this->update($l_update) && $this->apply_update()) {
            $l_last_id = $this->get_last_insert_id();

            // We only need to create relations if we got ourself an object.
            if ($p_assigned_device > 0) {
                $this->relations_update($l_last_id, $p_obj_id, $p_assigned_device);

                // Disconnect assigned device to prevent multiple assignments that violates logic
                $this->disconnectAssignedDevice($p_assigned_device, $l_last_id);
            }

            return $l_last_id;
        }

        return false;
    }

    /**
     * Disconnect device from chassis
     *
     * This method will disconnect a device from an previous chassis to
     * allow reassignment to the current chassis.
     *
     * @param int   $categoryEntryId        Category Entry ID to prevent disconnecting from entry
     * @param int   $connectedDeviceId      Device ID that should be connected only one time
     *
     * !! Please execute this before INSERT/UPDATE         !!
     * !! to prevent disconnecting created/updated entries !!
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function disconnectAssignedDevice($connectedDeviceId, $categoryEntryId)
    {
        // SQL to set connections to provided device to NULL
        $sql = 'UPDATE isys_connection c
                INNER JOIN isys_cats_chassis_list cl ON cl.isys_cats_chassis_list__isys_connection__id = c.isys_connection__id
                SET c.isys_connection__isys_obj__id = NULL 
                WHERE c.isys_connection__isys_obj__id = ' . $this->convert_sql_id($connectedDeviceId);

        // Should we exclude category entry from considarition?
        if ($categoryEntryId) {
            $sql .= ' AND cl.isys_cats_chassis_list__id != '. $this->convert_sql_id($categoryEntryId);
        }

        return ($this->update($sql) && $this->apply_update());
    }

    /**
     * Retrieves the string of the assigned device (for lists and dialog-fields etc.).
     *
     * @param   integer $p_cat_id
     * @param   string  $p_type
     *
     * @return  string|array
     * @throws isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @throws Exception
     */
    public function get_assigned_device_title_by_cat_id($p_cat_id, $p_type = 'quickinfo')
    {
        $l_return = '-';

        $l_sql = "SELECT rl.isys_chassis_role__title, ob.isys_obj__id, ob.isys_obj__title, netp.isys_catg_netp_list__id, netp.isys_catg_netp_list__title, pc.isys_catg_pc_list__id, pc.isys_catg_pc_list__title, hba.isys_catg_hba_list__id, hba.isys_catg_hba_list__title
			FROM isys_cats_chassis_list
			INNER JOIN isys_connection ON isys_connection__id = isys_cats_chassis_list__isys_connection__id
			LEFT JOIN isys_chassis_role AS rl ON rl.isys_chassis_role__id = isys_cats_chassis_list__isys_chassis_role__id
			LEFT JOIN isys_obj ob ON ob.isys_obj__id = isys_connection__isys_obj__id
			LEFT JOIN isys_catg_netp_list AS netp ON netp.isys_catg_netp_list__id = isys_cats_chassis_list__isys_catg_netp_list__id
			LEFT JOIN isys_catg_pc_list AS pc ON pc.isys_catg_pc_list__id = isys_cats_chassis_list__isys_catg_pc_list__id
			LEFT JOIN isys_catg_hba_list AS hba ON hba.isys_catg_hba_list__id = isys_cats_chassis_list__isys_catg_hba_list__id
			WHERE isys_cats_chassis_list__id = " . $this->convert_sql_id($p_cat_id) . ";";

        $l_row = $this->retrieve($l_sql)
            ->get_row();

        if ($p_type == 'raw') {
            $l_return = $l_row;
        } else {
            if ($l_row['isys_obj__id'] > 0) {
                if ($p_type == 'quickinfo') {
                    $l_quickinfo = new isys_ajax_handler_quick_info();
                    $l_return = isys_application::instance()->container->get('language')
                            ->get('LC_UNIVERSAL__OBJECT') . ': ' . $l_quickinfo->get_quick_info($l_row['isys_obj__id'], $l_row['isys_obj__title'], C__LINK__OBJECT);
                } elseif ($p_type == 'short') {
                    $l_return = $l_row['isys_obj__title'];
                } else {
                    $l_return = isys_application::instance()->container->get('language')
                            ->get('LC_UNIVERSAL__OBJECT') . ': ' . $l_row['isys_obj__title'];
                }
            } else {
                if ($l_row['isys_catg_pc_list__id'] > 0) {
                    $l_return = isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATG__POWER_CONSUMER') . ': ' . $l_row['isys_catg_pc_list__title'];
                } elseif ($l_row['isys_catg_netp_list__id'] > 0) {
                    $l_return = isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE') . ': ' . $l_row['isys_catg_netp_list__title'];
                } elseif ($l_row['isys_catg_hba_list__id'] > 0) {
                    $l_return = isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATG__HBA') . ': ' . $l_row['isys_catg_hba_list__title'];
                }
            }
        }

        return $l_return;
    }

    /**
     * Retrieve all assigned objects of a chassis.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_status
     * @param   bool    $p_only_object_ids
     *
     * @return  array
     * @throws isys_exception_database
     */
    public function get_assigned_objects($p_obj_id, $p_status = C__RECORD_STATUS__NORMAL, $p_only_object_ids = false)
    {
        $l_return = [];
        $l_sql = 'SELECT obj.*, type.*, isys_cats_chassis_list.*
			FROM isys_cats_chassis_list
			LEFT JOIN isys_connection ON isys_cats_chassis_list__isys_connection__id = isys_connection__id
			LEFT JOIN isys_obj obj ON isys_connection__isys_obj__id = isys_obj__id
			LEFT JOIN isys_obj_type type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			WHERE isys_cats_chassis_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_cats_chassis_list__status = ' . $this->convert_sql_id($p_status) . '
			AND obj.isys_obj__status = ' . $this->convert_sql_id($p_status) . '
			GROUP BY isys_obj__id;';

        $l_res = $this->retrieve($l_sql);

        while ($l_row = $l_res->get_row()) {
            if ($l_row['isys_obj__id'] > 0) {
                if ($p_only_object_ids) {
                    $l_return[] = $l_row['isys_obj__id'];
                } else {
                    $l_return[] = $l_row;
                }
            }
        }

        return $l_return;
    }

    /**
     * Method for retrieving all slots, the given object is assigned to.
     *
     * @param integer $p_assigned_object_id
     * @param integer $p_chassis_object_id
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_slots_by_assiged_object($p_assigned_object_id, $p_chassis_object_id = null)
    {
        $l_sql = "SELECT ch.isys_cats_chassis_list__id, slot.*, ob.*, obt.*
			FROM isys_cats_chassis_list ch
			INNER JOIN isys_connection ON isys_connection__id = ch.isys_cats_chassis_list__isys_connection__id
			LEFT JOIN isys_obj ob ON ob.isys_obj__id = isys_connection__isys_obj__id
			LEFT JOIN isys_obj_type obt ON obt.isys_obj_type__id = ob.isys_obj__isys_obj_type__id
			INNER JOIN isys_cats_chassis_list_2_isys_cats_chassis_slot_list ch2sl ON ch2sl.isys_cats_chassis_list__id = ch.isys_cats_chassis_list__id
			INNER JOIN isys_cats_chassis_slot_list slot ON slot.isys_cats_chassis_slot_list__id = ch2sl.isys_cats_chassis_slot_list__id  	
			WHERE ob.isys_obj__id = " . $this->convert_sql_id($p_assigned_object_id) . '
			AND ch.isys_cats_chassis_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if ($p_chassis_object_id !== null && $p_chassis_object_id > 0) {
            $l_sql .= ' AND slot.isys_cats_chassis_slot_list__isys_obj__id = ' . $this->convert_sql_id($p_chassis_object_id);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Selects the assigned slots to an chassis item.
     *
     * @param   integer $p_cat_id
     *
     * @return  array
     * @throws isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_assigned_slots_by_cat_id($p_cat_id)
    {
        $l_return = [];
        $l_sql = 'SELECT cs.*
			FROM isys_cats_chassis_list_2_isys_cats_chassis_slot_list c2cs
			LEFT JOIN isys_cats_chassis_slot_list cs ON cs.isys_cats_chassis_slot_list__id = c2cs.isys_cats_chassis_slot_list__id
			LEFT JOIN isys_cats_chassis_list c ON c.isys_cats_chassis_list__id = c2cs.isys_cats_chassis_list__id
			WHERE c.isys_cats_chassis_list__id = ' . $this->convert_sql_id($p_cat_id) . '
			AND c.isys_cats_chassis_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			AND cs.isys_cats_chassis_slot_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        $l_res = $this->retrieve($l_sql);

        while ($l_row = $l_res->get_row()) {
            $l_return[$l_row['isys_cats_chassis_slot_list__id']] = $l_row;
        }

        return $l_return;
    }

    /**
     * Return Category Data
     *
     * @param   integer $p_cats_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_cats_chassis_list
			INNER JOIN isys_obj ON isys_obj__id = isys_cats_chassis_list__isys_obj__id
			LEFT JOIN isys_connection ON isys_connection__id = isys_cats_chassis_list__isys_connection__id
			LEFT JOIN isys_chassis_connector_type ON isys_cats_chassis_list__isys_chassis_connector_type__id = isys_chassis_connector_type__id
			LEFT JOIN isys_chassis_role ON isys_cats_chassis_list__isys_chassis_role__id = isys_chassis_role__id
			WHERE TRUE ' . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_cats_list_id !== null) {
            $l_sql .= ' AND (isys_cats_chassis_list__id = ' . $this->convert_sql_id($p_cats_list_id) . ')';
        }

        if ($p_status !== null) {
            $l_sql .= ' AND (isys_cats_chassis_list__status = ' . $this->convert_sql_int($p_status) . ')';
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @throws Exception
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    protected function properties()
    {
        // @see  API-178  Necessary virtual property to prevent validation errors.
        $assignedSlotsProperty = new VirtualProperty('C__CMDB__CATS__CHASSIS__SLOT_ASSIGNMENT', 'LC__CMDB__CATS__CHASSIS__ASSIGNED_SLOTS');

        // Change the property info, so that the API will work with arrays.
        $assignedSlotsProperty->setInfo(
            $assignedSlotsProperty->getInfo()->setType(Property::C__PROPERTY__INFO__TYPE__MULTISELECT)
        );

        // Set all provides to false.
        $assignedSlotsProperty->mergePropertyProvides([
            Property::C__PROPERTY__PROVIDES__SEARCH       => false,
            Property::C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
            Property::C__PROPERTY__PROVIDES__IMPORT       => false,
            Property::C__PROPERTY__PROVIDES__EXPORT       => false,
            Property::C__PROPERTY__PROVIDES__REPORT       => false,
            Property::C__PROPERTY__PROVIDES__LIST         => false,
            Property::C__PROPERTY__PROVIDES__MULTIEDIT    => false,
            Property::C__PROPERTY__PROVIDES__VALIDATION   => false,
            Property::C__PROPERTY__PROVIDES__VIRTUAL      => false,
            Property::C__PROPERTY__PROVIDES__FILTERABLE   => false
        ]);

        return [
            'role'                    => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS__ROLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Role'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_cats_chassis_list__isys_chassis_role__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_chassis_role',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_chassis_role',
                        'isys_chassis_role__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_chassis_role__title
                            FROM isys_cats_chassis_list
                            INNER JOIN isys_chassis_role ON isys_chassis_role__id = isys_cats_chassis_list__isys_chassis_role__id',
                        'isys_cats_chassis_list',
                        'isys_cats_chassis_list__id',
                        'isys_cats_chassis_list__isys_obj__id',
                        '',
                        '',
                        null,
                        \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_chassis_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_chassis_list', 'LEFT', 'isys_cats_chassis_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_chassis_role',
                            'LEFT',
                            'isys_cats_chassis_list__isys_chassis_role__id',
                            'isys_chassis_role__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__CHASSIS__ROLE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_chassis_role',
                        'p_strClass' => 'input-small'
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_plus'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false // This DOES work.
                ]
            ]),
            'assigned_device'         => (new ObjectBrowserConnectionProperty(
                'C__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES',
                'LC__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES',
                'isys_cats_chassis_list__isys_connection__id',
                'isys_cats_chassis_list'
            ))
                ->setPropertyDataRelationType(defined_or_default('C__RELATION_TYPE__CHASSIS'))
                ->setPropertyDataRelationHandler(
                    new isys_callback(
                        [
                            'isys_cmdb_dao_category_s_chassis',
                            'callback_property_relation_handler'
                        ],
                        [
                            'isys_cmdb_dao_category_s_chassis'
                        ]
                    )
                ),
            'assigned_hba'            => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES__HBA',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned hostadapter (HBA)'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_cats_chassis_list__isys_catg_hba_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_hba_list',
                        'isys_catg_hba_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_hba_list__title
                            FROM isys_cats_chassis_list
                            INNER JOIN isys_catg_hba_list ON isys_catg_hba_list__id = isys_cats_chassis_list__isys_catg_hba_list__id',
                        'isys_cats_chassis_list',
                        'isys_cats_chassis_list__id',
                        'isys_cats_chassis_list__isys_obj__id',
                        '',
                        '',
                        null,
                        \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_chassis_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_chassis_list', 'LEFT', 'isys_cats_chassis_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_hba_list',
                            'LEFT',
                            'isys_cats_chassis_list__isys_catg_hba_list__id',
                            'isys_catg_hba_list__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_ip',
                            'callback_property_assigned_categories'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false // This DOES work.
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_referenced_object_and_category',
                        [
                            'C__CATG__HBA',
                            'C__CMDB__CATEGORY__TYPE_GLOBAL'
                        ]
                    ]
                ]
            ]),
            'assigned_interface'      => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES__INTERFACE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned interface'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_cats_chassis_list__isys_catg_netp_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_netp_list',
                        'isys_catg_netp_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_netp_list__title
                            FROM isys_cats_chassis_list
                            INNER JOIN isys_catg_netp_list ON isys_catg_netp_list__id = isys_cats_chassis_list__isys_catg_netp_list__id',
                        'isys_cats_chassis_list',
                        'isys_cats_chassis_list__id',
                        'isys_cats_chassis_list__isys_obj__id',
                        '',
                        '',
                        null,
                        \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_chassis_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_chassis_list', 'LEFT', 'isys_cats_chassis_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_netp_list',
                            'LEFT',
                            'isys_cats_chassis_list__isys_catg_netp_list__id',
                            'isys_catg_netp_list__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_ip',
                            'callback_property_assigned_categories'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false // This DOES work.
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_referenced_object_and_category',
                        [
                            'C__CATG__NETWORK_INTERFACE',
                            'C__CMDB__CATEGORY__TYPE_GLOBAL'
                        ]
                    ]
                ]
            ]),
            'assigned_power_consumer' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES__POWER_CONSUMER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned power consumer'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_cats_chassis_list__isys_catg_pc_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_pc_list',
                        'isys_catg_pc_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_pc_list__title
                            FROM isys_cats_chassis_list
                            INNER JOIN isys_catg_pc_list ON isys_catg_pc_list__id = isys_cats_chassis_list__isys_catg_pc_list__id',
                        'isys_cats_chassis_list',
                        'isys_cats_chassis_list__id',
                        'isys_cats_chassis_list__isys_obj__id',
                        '',
                        '',
                        null,
                        \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_chassis_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_chassis_list', 'LEFT', 'isys_cats_chassis_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_pc_list',
                            'LEFT',
                            'isys_cats_chassis_list__isys_catg_pc_list__id',
                            'isys_catg_pc_list__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData'   => new isys_callback([
                            'isys_cmdb_dao_category_g_ip',
                            'callback_property_assigned_categories'
                        ]),
                        'p_strClass' => 'input-small'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false // This DOES work.
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_referenced_object_and_category',
                        [
                            'C__CATG__POWER_CONSUMER',
                            'C__CMDB__CATEGORY__TYPE_GLOBAL'
                        ]
                    ]
                ]
            ]),
            'assigned_slots'          => $assignedSlotsProperty,
            'description'             => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_chassis_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_chassis_list__description FROM isys_cats_chassis_list',
                        'isys_cats_chassis_list',
                        'isys_cats_chassis_list__id',
                        'isys_cats_chassis_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_chassis_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__CHASSIS_DEVICES', 'C__CATS__CHASSIS_DEVICES')
                ]
            ])
        ];
    }


    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_assigned_slots' => new DynamicProperty(
                'LC__CMDB__CATS__CHASSIS__ASSIGNED_SLOTS',
                'isys_cats_chassis_list__id',
                'isys_cats_chassis_list',
                [
                    $this,
                    'dynamic_property_callback_assigned_slots'
                ]
            )
        ];
    }

    /**
     * Rank method for handling "archive", "delete" and "recycle".
     *
     * @param   integer $p_cat_id
     * @param   integer $p_direction
     * @param   string  $p_table
     * @param   array   $p_checkMethod Callback like array('Class', 'Method').
     * @param   boolean $p_purge
     *
     * @return  boolean
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    public function rank_record($p_cat_id, $p_direction, $p_table, $p_checkMethod = null, $p_purge = false)
    {
        $l_row = $this->get_data($p_cat_id)
            ->get_row();

        if ($l_row['isys_connection__isys_obj__id'] > 0) {
            // Retrieve the location- and chassis relation from the given object.
            $l_rel_dao = isys_cmdb_dao_category_g_relation::instance($this->m_db);

            // Now we prepare the condition to get us the relation objects.
            $l_cond = 'AND isys_catg_relation_list__isys_obj__id__slave = ' . $this->convert_sql_id($l_row['isys_connection__isys_obj__id']);
            $relationTypes = filter_defined_constants([
                'C__RELATION_TYPE__CHASSIS',
                'C__RELATION_TYPE__LOCATION'
            ]);
            if (!empty($relationTypes)) {
                $l_cond .= ' AND isys_catg_relation_list__isys_relation_type__id IN (' . implode(', ', $relationTypes) . ')';
            }
            $l_res = $l_rel_dao->get_data(null, null, $l_cond);

            while ($l_rel_row = $l_res->get_row()) {
                parent::rank_record($l_rel_row['isys_catg_relation_list__id'], $p_direction, 'isys_catg_relation_list', $p_checkMethod, $p_purge);
            }

            if ($p_direction == C__CMDB__RANK__DIRECTION_DELETE && ($l_row['isys_cats_chassis_list__status'] == C__RECORD_STATUS__DELETED || $p_purge)) {
                $this->relations_remove($p_cat_id, $l_row['isys_cats_chassis_list__isys_obj__id'], $l_row['isys_connection__isys_obj__id']);
            }
        }

        return parent::rank_record($p_cat_id, $p_direction, $p_table, $p_checkMethod);
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param  array $p_category_data
     * @param  int   $p_object_id
     * @param  int   $p_status
     *
     * @return mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @throws isys_exception_cmdb
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $l_role = $p_category_data['properties']['role'][C__DATA__VALUE];
            $l_assigned_device = $p_category_data['properties']['assigned_device'][C__DATA__VALUE];
            $l_description = $p_category_data['properties']['description'][C__DATA__VALUE];

            if ($p_category_data['properties']['assigned_hba'][C__DATA__VALUE] > 0) {
                $l_local_device = $p_category_data['properties']['assigned_hba'][C__DATA__VALUE] . '_C__CATG__HBA';
            } elseif ($p_category_data['properties']['assigned_interface'][C__DATA__VALUE] > 0) {
                $l_local_device = $p_category_data['properties']['assigned_hba'][C__DATA__VALUE] . '_C__CATG__NETWORK_INTERFACE';
            } elseif ($p_category_data['properties']['assigned_power_consumer'][C__DATA__VALUE] > 0) {
                $l_local_device = $p_category_data['properties']['assigned_hba'][C__DATA__VALUE] . '_C__CATG__POWER_CONSUMER';
            } else {
                $l_local_device = null;
            }

            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                $p_category_data['data_id'] = $this->create($p_object_id, C__RECORD_STATUS__NORMAL, $l_role, $l_local_device, $l_assigned_device, $l_description);
            }

            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data:
                $l_indicator = $this->save($p_category_data['data_id'], C__RECORD_STATUS__NORMAL, $l_role, $l_local_device, $l_assigned_device, $l_description);
                // Assign slots.
                if (is_array($p_category_data['properties']['assigned_slots'][C__DATA__VALUE])) {
                    foreach ($p_category_data['properties']['assigned_slots'][C__DATA__VALUE] as $l_slot) {
                        $this->addLogbookEntryCreated($p_category_data['data_id']);
                        $this->assign_slot_to_chassis_item($p_category_data['data_id'], $l_slot['id']);
                    }
                }
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Gets local hostadapters.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_cat_id
     *
     * @return  mixed
     * @throws isys_exception_database
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_local_hba($p_obj_id, $p_cat_id = null)
    {
        $l_query = 'SELECT isys_catg_hba_list__id AS id, isys_catg_hba_list__title AS title
			FROM isys_catg_hba_list
			WHERE isys_catg_hba_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_catg_hba_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if (!empty($p_cat_id)) {
            $l_query .= ' AND isys_catg_hba_list__id = ' . $this->convert_sql_id($p_cat_id);
        }

        $l_res = $this->retrieve($l_query);
        if ($l_res->num_rows() > 0) {
            return $l_res;
        } else {
            return false;
        }
    }

    /**
     * Gets local power consumers.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_cat_id
     *
     * @return  mixed
     * @throws isys_exception_database
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_local_power_consumer($p_obj_id, $p_cat_id = null)
    {
        $l_query = 'SELECT isys_catg_pc_list__id AS id, isys_catg_pc_list__title AS title
			FROM isys_catg_pc_list
			WHERE isys_catg_pc_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_catg_pc_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if (!empty($p_cat_id)) {
            $l_query .= ' AND isys_catg_pc_list__id = ' . $this->convert_sql_id($p_cat_id);
        }

        $l_res = $this->retrieve($l_query);
        if ($l_res->num_rows() > 0) {
            return $l_res;
        } else {
            return false;
        }
    }

    /**
     * Gets local interfaces.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_cat_id
     *
     * @return  mixed
     * @throws isys_exception_database
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_local_interface($p_obj_id, $p_cat_id = null)
    {
        $l_query = 'SELECT isys_catg_netp_list__id AS id, isys_catg_netp_list__title AS title
			FROM isys_catg_netp_list
			WHERE isys_catg_netp_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_catg_netp_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if (!empty($p_cat_id)) {
            $l_query .= ' AND isys_catg_netp_list__id = ' . $this->convert_sql_id($p_cat_id);
        }

        $l_res = $this->retrieve($l_query);
        if ($l_res->num_rows() > 0) {
            return $l_res;
        } else {
            return false;
        }
    }

    /**
     * Gets local devices (hba, interfaces, power consumer) as array.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @throws isys_exception_database
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @throws Exception
     */
    public function get_local_devices_as_array($p_obj_id)
    {
        $l_arr = [];
        $l_devices = [
            isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATG__HBA')                           => [
                $this->get_local_hba($p_obj_id),
                'C__CATG__HBA'
            ],
            isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATG__POWER_CONSUMER')                => [
                $this->get_local_power_consumer($p_obj_id),
                'C__CATG__POWER_CONSUMER'
            ],
            isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE') => [
                $this->get_local_interface($p_obj_id),
                'C__CATG__NETWORK_INTERFACE'
            ]
        ];

        foreach ($l_devices as $l_lc => $l_result) {
            if ($l_result[0]) {
                while ($l_row = $l_result[0]->get_row()) {
                    $l_arr[$l_lc][$l_row['id'] . '_' . $l_result[1]] = $l_row['title'];
                }
            }
        }

        return $l_arr;
    }

    /**
     * This method helps to create the necessary "chassis" and "location" relations, when assigning a new device (object).
     *
     * @param   integer $p_cat_id
     * @param   integer $p_chassis_obj
     * @param   integer $p_assigned_obj
     *
     * @return  isys_cmdb_dao_category_s_chassis
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_dao_cmdb
     * @throws isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function relations_create($p_cat_id, $p_chassis_obj, $p_assigned_obj)
    {
        // If we got no object to assign, we can skip this method.
        if (empty($p_assigned_obj)) {
            return $this;
        }

        $l_rel_dao = isys_cmdb_dao_category_g_relation::instance($this->m_db);

        // Now we can create the new relation
        $l_rel_dao->handle_relation($p_cat_id, 'isys_cats_chassis_list', defined_or_default('C__RELATION_TYPE__CHASSIS'), null, $p_chassis_obj, $p_assigned_obj);

        // Now we handle the location relation.
        $l_loc_dao = new isys_cmdb_dao_category_g_location($this->m_db);
        $l_location = $l_loc_dao->get_data(null, $p_assigned_obj)
            ->get_row();

        // Object to Chassis location.
        if (!$l_location) {
            $l_loc_dao->create($p_assigned_obj, $p_chassis_obj);
        } else {
            if (isset($l_location['isys_catg_location_list__isys_obj__id']) && $l_location['isys_catg_location_list__isys_obj__id'] > 0) {
                $l_loc_dao->save(
                    $l_location['isys_catg_location_list__id'],
                    $l_location['isys_catg_location_list__isys_obj__id'],
                    $p_chassis_obj,
                    null,
                    null,
                    null,
                    null,
                    $l_location['isys_catg_location_list__description'],
                    null,
                    new Coordinate([
                        $l_location['latitude'],
                        $l_location['longitude']
                    ])
                );
            }
        }

        // @see  ID-5233  We need to re-set the connection because of changes done for ID-4974
        isys_cmdb_dao_connection::instance($this->m_db)
            ->update_connection($this->get_data($p_cat_id)
                ->get_row_value('isys_connection__id'), $p_assigned_obj);

        return $this;
    }

    /**
     * Method which calls relations_remove and relations_create.
     *
     * @param   integer $p_cat_id
     * @param   integer $p_chassis_obj
     * @param   integer $p_assigned_obj
     *
     * @return  isys_cmdb_dao_category_s_chassis
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_dao_cmdb
     * @throws isys_exception_database
     * @uses    $this->relations_remove()
     * @uses    $this->relations_create()
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function relations_update($p_cat_id, $p_chassis_obj, $p_assigned_obj)
    {
        return $this->relations_remove($p_cat_id, $p_chassis_obj, $p_assigned_obj)
            ->relations_create($p_cat_id, $p_chassis_obj, $p_assigned_obj);
    }

    /**
     * Method for removing the "chassis" and "location" relations of the chassis category entry and the given object.
     *
     * @param   integer $p_cat_id
     * @param   integer $p_chassis_obj
     * @param   integer $p_assigned_obj
     *
     * @return  isys_cmdb_dao_category_s_chassis
     * @throws isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function relations_remove($p_cat_id, $p_chassis_obj, $p_assigned_obj)
    {
        $l_rel_dao = isys_cmdb_dao_category_g_relation::instance($this->m_db);
        $l_loc_dao = isys_cmdb_dao_category_g_location::instance($this->m_db);
        $l_catdata = $this->get_data($p_cat_id)
            ->get_row();

        if ($p_assigned_obj > 0) {
            $l_loc_dao->reset_location($p_assigned_obj);
        }

        // First remove the already saved relation (if existing).
        if ($l_catdata['isys_cats_chassis_list__isys_catg_relation_list__id'] > 0) {
            $l_rel_dao->delete_relation($l_catdata['isys_cats_chassis_list__isys_catg_relation_list__id']);
        }

        // We check if the chassis entry already has an assigned object and delete it's relations.
        if ($l_catdata['isys_connection__isys_obj__id'] > 0) {
            $l_cond = 'AND isys_catg_relation_list__isys_obj__id__slave = ' . $this->convert_sql_id($l_catdata['isys_connection__isys_obj__id']) . '
				AND isys_catg_relation_list__isys_relation_type__id = ' . $this->convert_sql_id(defined_or_default('C__RELATION_TYPE__CHASSIS'));
            $l_rel_row = $l_rel_dao->get_data(null, null, $l_cond)
                ->get_row();

            if ($l_rel_row !== null && is_array($l_rel_row)) {
                // We found an old Chassis relation to the given object - We delete it.
                $l_rel_dao->delete_relation($l_rel_row['isys_catg_relation_list__id']);
            }

            // Also set the location of the old object to null.
            $l_loc_dao->reset_location($l_catdata['isys_connection__isys_obj__id']);

            // @see  ID-4974  Also set the connection to null.
            isys_cmdb_dao_connection::instance($this->m_db)
                ->update_connection($l_catdata['isys_connection__id'], null);
        }

        // We want to delete the old Chassis relation of the given object and therefore have to check if a relation exists.
        $l_cond = 'AND isys_catg_relation_list__isys_obj__id__slave = ' . $this->convert_sql_id($p_assigned_obj) . '
			AND isys_catg_relation_list__isys_relation_type__id = ' . $this->convert_sql_id(defined_or_default('C__RELATION_TYPE__CHASSIS'));
        $l_rel_row = $l_rel_dao->get_data(null, null, $l_cond)
            ->get_row();

        if ($l_rel_row !== null && is_array($l_rel_row)) {
            // We found an old Chassis relation to the given object - We delete it.
            $l_rel_dao->delete_relation($l_rel_row['isys_catg_relation_list__id']);
        }

        /* LF: Removed this code because ... what's its purpose!?
        if ($p_assigned_obj > 0)
        {
            $l_location = $l_loc_dao->get_data(null, $p_assigned_obj)
                ->get_row();
            $l_loc_dao->save(
                $l_location['isys_catg_location_list__id'],
                $l_location['isys_catg_location_list__isys_obj__id'],
                null
            );
        }
        */

        return $this;
    }

    /**
     * Method for removing all chassis-slot assignments from a certain chassis item.
     *
     * @param   integer $p_cat_id
     *
     * @return  boolean
     * @throws isys_exception_dao
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function remove_slot_assignments($p_cat_id)
    {
        $l_sql = 'DELETE FROM isys_cats_chassis_list_2_isys_cats_chassis_slot_list
			WHERE isys_cats_chassis_list__id = ' . $this->convert_sql_id($p_cat_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Save method.
     *
     * @param   integer $p_cat_id
     * @param   integer $p_status
     * @param   integer $p_role
     * @param   string  $p_local_assignment format example: "3_C__CATG__HBA"
     * @param   integer $p_assigned_device
     * @param   string  $p_description
     *
     * @return  boolean
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_dao_cmdb
     * @throws isys_exception_database
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save($p_cat_id, $p_status, $p_role, $p_local_assignment = null, $p_assigned_device = null, $p_description = null)
    {
        $l_netp_id = null;
        $l_pc_id = null;
        $l_hba_id = null;
        $l_con_id = null;

        $l_row = $this->get_data($p_cat_id)
            ->get_row();

        $this->relations_remove($p_cat_id, $l_row['isys_cats_chassis_list__isys_obj__id'], $p_assigned_device);

        if (!$p_assigned_device && $p_local_assignment) {
            $l_id = substr($p_local_assignment, 0, strpos($p_local_assignment, '_'));
            $l_type = substr($p_local_assignment, strpos($p_local_assignment, '_') + 1);

            switch ($l_type) {
                case 'C__CATG__HBA':
                    $l_hba_id = $l_id;
                    break;
                case 'C__CATG__POWER_CONSUMER':
                    $l_pc_id = $l_id;
                    break;
                case 'C__CATG__NETWORK_INTERFACE':
                case 'C__CMDB__SUBCAT__NETWORK_INTERFACE_P': // @todo  Remove in i-doit 1.12
                    $l_netp_id = $l_id;
                    break;
            }
        }

        // Check for assigned device
        if ($p_assigned_device > 0) {
            // Disconnect assigned device to prevent multiple assignments that violates logic
            $this->disconnectAssignedDevice($p_assigned_device, $p_cat_id);
        }

        $this->relations_create($p_cat_id, $l_row['isys_cats_chassis_list__isys_obj__id'], $p_assigned_device);

        $l_update = 'UPDATE isys_cats_chassis_list
			SET
			isys_cats_chassis_list__isys_connection__id = ' . $this->convert_sql_id($this->handle_connection($p_cat_id, $p_assigned_device)) . ',
			isys_cats_chassis_list__isys_chassis_role__id = ' . $this->convert_sql_id($p_role) . ',
			isys_cats_chassis_list__isys_catg_netp_list__id = ' . $this->convert_sql_id($l_netp_id) . ',
			isys_cats_chassis_list__isys_catg_pc_list__id = ' . $this->convert_sql_id($l_pc_id) . ',
			isys_cats_chassis_list__isys_catg_hba_list__id = ' . $this->convert_sql_id($l_hba_id) . ',
			isys_cats_chassis_list__description = ' . $this->convert_sql_text($p_description) . '
			WHERE isys_cats_chassis_list__id = ' . $this->convert_sql_id($p_cat_id) . ';';

        return ($this->update($l_update) && $this->apply_update());
    }

    /**
     * Method for saving the element.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @return  integer  The error code or null on success.
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_dao_cmdb
     * @throws isys_exception_database
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus, $p_create)
    {
        $l_intErrorCode = -1;

        if ($p_create) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $p_cat_level = 1;
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CMDB__CATS__CHASSIS__ROLE'],
                $_POST['C__CMDB__CATS__CHASSIS__LOCAL_ASSIGNMENT'],
                $_POST['C__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES__HIDDEN'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();

            $this->assign_slots_from_post($l_id);

            return $l_id;
        }

        $l_catdata = $this->get_result()
            ->__to_array();
        $p_intOldRecStatus = $l_catdata["isys_cats_chassis_list__status"];

        $l_bRet = $this->save(
            $l_catdata["isys_cats_chassis_list__id"],
            C__RECORD_STATUS__NORMAL,
            $_POST['C__CMDB__CATS__CHASSIS__ROLE'],
            $_POST['C__CMDB__CATS__CHASSIS__LOCAL_ASSIGNMENT'],
            $_POST['C__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES__HIDDEN'],
            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
        );

        $this->m_strLogbookSQL = $this->get_last_query();

        $this->assign_slots_from_post($l_catdata["isys_cats_chassis_list__id"]);

        return ($l_bRet == true) ? null : $l_intErrorCode;
    }
}
