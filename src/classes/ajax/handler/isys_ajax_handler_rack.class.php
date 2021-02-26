<?php

use idoit\Component\Location\Coordinate;

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-9
 */
class isys_ajax_handler_rack extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [];

        switch ($_GET['func']) {
            case 'assign_object_to_rack':
                $l_return = $this->assign_object_to_rack($_POST['rack_obj_id'], $_POST['obj_id'], $_POST['option'], $_POST['insertion'], $_POST['pos'], $_POST['chassisSlot']);
                break;

            case 'detach_object_from_rack':
                $l_return = $this->detach_object_from_rack($_POST['obj_id']);
                break;

            case 'get_free_slots':
                $l_return = $this->get_free_slots();
                break;

            case 'get_free_slots_for_location':
                $l_return = $this->get_free_slots_for_location();
                break;

            case 'get_racks_recursive':
                $l_return = $this->get_racks_recursive($_POST['obj_id']);
                break;

            case 'get_immediate_parent_rack':
                $l_return = $this->get_immediate_parent_rack($_POST['obj_id']);
                break;

            case 'get_rack_options':
                $l_return = $this->get_rack_options($_POST['obj_id']);
                break;

            case 'get_rack_insertions':
                $l_return = $this->get_rack_insertions($_POST['option']);
                break;

            case 'get_chassis_layout':
                $l_return = $this->get_chassis_layout($_POST['templateObjectId']);
                break;

            case 'get_segments':
                $l_return = $this->get_segments($_POST['rack_obj_id'], $_POST['option'], $_POST['insertion'], explode(';', $_POST['position'])[0]);
                break;

            case 'create_segment_from_template':
                $l_return = $this->create_segment_from_template($_POST['rackObjectId'], $_POST['templateObjectId'], $_POST['option'], $_POST['insertion'], $_POST['position']);
                break;

            case 'detach_slot_segmentation':
                $l_return = $this->detach_segment($_POST['rackObjectId'], $_POST['segmentationObjectId']);
                break;

            case 'pos':
                $l_return = $this->get_positions_in_rack($_POST['obj_id']);
                break;

            case 'remove_object_assignment':
                $l_return = $this->remove_object_assignment();
                break;

            case 'save_object_ru':
                $l_return = $this->save_object_ru($_POST['obj_id'], $_POST['height']);
                break;

            case 'save_position_in_location':
                $l_return = $this->save_position_in_location($_POST['positions']);
                break;
        }

        echo isys_format_json::encode($l_return);

        $this->_die();
    }

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     */
    public static function needs_hypergate()
    {
        return true;
    }

    /**
     * Method for assigning an object to a rack (position and insertion).
     *
     * @param   integer $p_rack_object_id
     * @param   integer $p_object_id
     * @param   integer $p_option
     * @param   integer $p_insertion
     * @param   integer $p_position
     * @param   integer $p_chassis_slot
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function assign_object_to_rack($p_rack_object_id, $p_object_id, $p_option, $p_insertion, $p_position, $p_chassis_slot = 0)
    {
        if ($p_chassis_slot > 0) {
            $l_chassis_dao = isys_cmdb_dao_category_s_chassis::instance($this->m_database_component);
            $l_chassis_slot_dao = isys_cmdb_dao_category_s_chassis_slot::instance($this->m_database_component);

            $l_chassis_object = $l_chassis_slot_dao->get_data($p_chassis_slot)
                ->get_row();

            $l_chassis_dao->sync([
                'properties' => [
                    'assigned_device' => [C__DATA__VALUE => $p_object_id],
                    'assigned_slots'  => [C__DATA__VALUE => [['id' => $p_chassis_slot]]]
                ]
            ], $l_chassis_object['isys_obj__id'], isys_import_handler_cmdb::C__CREATE);

            isys_cmdb_dao_category_g_location::instance($this->m_database_component)
                ->sync([
                    'properties' => [
                        'parent' => [C__DATA__VALUE => $l_chassis_object['isys_obj__id']]
                    ]
                ], $p_object_id, isys_import_handler_cmdb::C__UPDATE);
        } else {
            isys_cmdb_dao_location::instance($this->m_database_component)
                ->update_position($p_object_id, $p_option, $p_insertion, $p_position);
        }

        return array_values($this->get_assigned_objects($p_rack_object_id));
    }

    /**
     * Method for detaching an objects location.
     *
     * @param   integer $p_obj
     *
     * @return  array
     */
    protected function detach_object_from_rack($p_obj)
    {
        $l_dao = isys_cmdb_dao_category_g_location::instance($this->m_database_component);

        $l_row = $l_dao->get_data(null, $p_obj)
            ->get_row();

        try {
            // Use save method. Whole location entry has to be updated
            $l_dao->save($l_row['isys_catg_location_list__id'], $p_obj, null, $l_row['isys_catg_location_list__parentid'], null, null, null, // This can be removed
                $l_row['isys_catg_location_list__description'], $l_row['isys_catg_location_list__option']);

            return ['success' => true];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Receive the assigned objects of a given rack in a certain format.
     *
     * @param   integer $p_rack_id The object-id of the rack.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_assigned_objects($p_rack_id)
    {
        return isys_cmdb_dao_category_s_enclosure::instance($this->m_database_component)
            ->prepare_rack_data($p_rack_id, 'rack title')['objects'];
    }

    /**
     * Returns the available slots inside a rack.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_free_slots()
    {
        return isys_cmdb_dao_category_g_location::instance($this->m_database_component)
            ->get_free_rackslots($_POST['rack_obj_id'], $_POST['insertion'], $_POST['assign_obj_id'], $_POST['option'], $_POST['rackSlotSort']);
    }

    /**
     * Returns the available slots inside a rack. Slightly differs to "get_free_slots": This method always returns ascending values.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_free_slots_for_location()
    {
        return isys_cmdb_dao_category_g_location::instance($this->m_database_component)
            ->get_free_rackslots($_POST['rack_obj_id'], $_POST['insertion'], $_POST['assign_obj_id'], $_POST['option']);
    }

    /**
     * Method for retrieving a chassis layout to display a preview in the frontend.
     *
     * @param   integer $p_template_object_id
     *
     * @return  array
     */
    protected function get_chassis_layout($p_template_object_id)
    {
        try {
            return [
                'success' => true,
                'data'    => isys_cmdb_dao_category_s_enclosure::instance($this->m_database_component)
                    ->prepare_chassis_data($p_template_object_id),
                'message' => ''
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data'    => null,
                'message' => $e->getMessage()
            ];
        }
    }

    public function get_segments($p_rack_obj_id, $p_option, $p_insertion, $p_position)
    {
        try {
            $l_dao = isys_cmdb_dao_category_g_location::instance($this->m_database_component);

            $l_return = [];

            $l_condition = 'AND isys_catg_location_list__parentid = ' . $l_dao->convert_sql_id($p_rack_obj_id) . ' 
                AND isys_catg_location_list__option = ' . $l_dao->convert_sql_int($p_option) . '
                AND isys_catg_location_list__insertion = ' . $l_dao->convert_sql_int($p_insertion) . '
                AND isys_catg_location_list__pos = ' . $l_dao->convert_sql_int($p_position);

            $l_segment_row = $l_dao->get_data(null, null, $l_condition, null, C__RECORD_STATUS__NORMAL)
                ->get_row();

            if ($l_segment_row['isys_obj_type__isysgui_cats__id'] == defined_or_default('C__CATS__CHASSIS')) {
                $l_slot_result = isys_cmdb_dao_category_s_chassis_slot::instance($this->m_database_component)
                    ->get_data(null, $l_segment_row['isys_obj__id'], '', null, C__RECORD_STATUS__NORMAL);

                while ($l_slot_row = $l_slot_result->get_row()) {
                    $l_return[$l_slot_row['isys_cats_chassis_slot_list__id']] = $l_slot_row['isys_cats_chassis_slot_list__title'];
                }
            }

            return [
                'success' => true,
                'data'    => [
                    'slots'         => $l_return,
                    'rackQuickInfo' => (new isys_ajax_handler_quick_info)->get_quick_info($p_rack_obj_id, $l_dao->get_obj_name_by_id_as_string($p_rack_obj_id),
                        C__LINK__OBJECT)
                ],
                'message' => ''
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data'    => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * @param integer $p_rack_object_id
     * @param integer $p_template_object_id
     * @param integer $p_option
     * @param integer $p_insertion
     * @param integer $p_position
     *
     * @return array
     */
    protected function create_segment_from_template($p_rack_object_id, $p_template_object_id, $p_option = null, $p_insertion = null, $p_position = null)
    {
        try {
            $l_dao = isys_cmdb_dao_category_g_global::instance($this->m_database_component);
            $l_dao_chassis_view = isys_cmdb_dao_category_s_chassis_view::instance($this->m_database_component);
            $l_dao_chassis_slots = isys_cmdb_dao_category_s_chassis_slot::instance($this->m_database_component);

            $l_template = $l_dao->get_data(null, $p_template_object_id)
                ->get_row();

            $l_segment_object_name = isys_cmdb_dao_category_s_enclosure::instance($this->m_database_component)
                ->get_segment_object_name($p_rack_object_id, $l_template['isys_obj__title'], $p_position);

            $l_object = $l_dao->insert_new_obj($l_template['isys_obj_type__id'], null, $l_segment_object_name, null, C__RECORD_STATUS__NORMAL, null, null, false, null, null,
                null, null, $l_template['isys_catg_global_category__id'], $l_template['isys_purpose__id'], defined_or_default('C__CMDB_STATUS__IN_OPERATION'), $l_template['isys_obj__description']);

            // First we duplicate view data.
            $l_slot_view_row = $l_dao_chassis_view->get_data(null, $p_template_object_id)
                ->get_row();

            // ...via the SYNC method.
            $l_dao_chassis_view->sync([
                'properties' => [
                    'front_x'     => [C__DATA__VALUE => $l_slot_view_row['isys_cats_chassis_view_list__front_width']],
                    'front_y'     => [C__DATA__VALUE => $l_slot_view_row['isys_cats_chassis_view_list__front_height']],
                    'front_size'  => [C__DATA__VALUE => $l_slot_view_row['isys_cats_chassis_view_list__front_size']],
                    'rear_x'      => [C__DATA__VALUE => $l_slot_view_row['isys_cats_chassis_view_list__rear_width']],
                    'rear_y'      => [C__DATA__VALUE => $l_slot_view_row['isys_cats_chassis_view_list__rear_height']],
                    'rear_size'   => [C__DATA__VALUE => $l_slot_view_row['isys_cats_chassis_view_list__rear_size']],
                    'description' => [C__DATA__VALUE => $l_slot_view_row['isys_cats_chassis_view_list__description']]
                ]
            ], $l_object, isys_import_handler_cmdb::C__CREATE);

            // Secondly we duplicate the slot data.
            $l_slots_result = $l_dao_chassis_slots->get_data(null, $p_template_object_id);

            while ($l_slots_row = $l_slots_result->get_row()) {
                // We duplicate everything but the "assigned devices".
                $l_dao_chassis_slots->sync([
                    'properties' => [
                        'connector_type' => [C__DATA__VALUE => $l_slots_row['isys_cats_chassis_slot_list__isys_chassis_connector_type__id']],
                        'insertion'      => [C__DATA__VALUE => $l_slots_row['isys_cats_chassis_slot_list__insertion']],
                        'title'          => [C__DATA__VALUE => $l_slots_row['isys_cats_chassis_slot_list__title']],
                        'from_x'         => [C__DATA__VALUE => $l_slots_row['isys_cats_chassis_slot_list__x_from']],
                        'to_x'           => [C__DATA__VALUE => $l_slots_row['isys_cats_chassis_slot_list__x_to']],
                        'from_y'         => [C__DATA__VALUE => $l_slots_row['isys_cats_chassis_slot_list__y_from']],
                        'to_y'           => [C__DATA__VALUE => $l_slots_row['isys_cats_chassis_slot_list__y_to']],
                        // 'assigned_devices' => [C__DATA__VALUE => []],
                        'description'    => [C__DATA__VALUE => $l_slots_row['isys_cats_chassis_slot_list__description']],
                    ]
                ], $l_object, isys_import_handler_cmdb::C__CREATE);
            }

            // Remove any previously attached object.
            $l_sql = 'SELECT isys_obj__id 
                FROM isys_obj 
                INNER JOIN isys_catg_location_list ON isys_catg_location_list__isys_obj__id = isys_obj__id 
                WHERE isys_catg_location_list__parentid = ' . $l_dao->convert_sql_id($p_rack_object_id) . '
                AND isys_catg_location_list__pos = ' . $l_dao->convert_sql_int($p_position) . '
                AND isys_catg_location_list__insertion IN (' . $l_dao->convert_sql_int($p_insertion) . ',' . $l_dao->convert_sql_int(C__INSERTION__BOTH) . ');';

            $l_result = $l_dao->retrieve($l_sql);

            while ($l_row = $l_result->get_row()) {
                if ($l_row['isys_obj__id'] > 0) {
                    isys_cmdb_dao_location::instance($this->m_database_component)
                        ->update_position($l_row['isys_obj__id']);
                }
            }

            // After creating the new segment object, we move it underneath the rack and assign it to the given position and insertion.
            isys_cmdb_dao_category_g_location::instance($this->m_database_component)
                ->create($l_object, $p_rack_object_id, $p_position, $p_insertion, null, '', $p_option);

            return [
                'success' => true,
                'data'    => array_values($this->get_assigned_objects($p_rack_object_id)),
                'message' => ''
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data'    => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Method for detaching a segmentation object and re-assigning all children to the rack.
     *
     * @param $p_rack_object_id
     * @param $p_segmentation_object_id
     *
     * @return array
     */
    protected function detach_segment($p_rack_object_id, $p_segmentation_object_id)
    {
        $l_location_dao = isys_cmdb_dao_category_g_location::instance($this->m_database_component);
        $l_chassis_dao = isys_cmdb_dao_category_s_chassis_slot::instance($this->m_database_component);

        $l_assigned_objects_result = isys_cmdb_dao_category_g_virtual_object::instance($this->m_database_component)
            ->get_data(null, $p_segmentation_object_id);
        $l_chassis_item_result = $l_chassis_dao->get_data(null, $p_segmentation_object_id);

        try {
            while ($l_assigned_objects_row = $l_assigned_objects_result->get_row()) {
                // First: move all objects from the segmentation object underneath the rack (but without any positioning).
                $l_location_dao->save($l_assigned_objects_row['isys_catg_location_list__id'], $l_assigned_objects_row['isys_obj__id'], $p_rack_object_id,
                    $p_segmentation_object_id, null, null, null, $l_assigned_objects_row['isys_catg_location_list__description'], null,
                    new Coordinate([$l_assigned_objects_row['latitude'], $l_assigned_objects_row['longitude']]));
            }

            while ($l_chassis_item_row = $l_chassis_item_result->get_row()) {
                // Also remove object from all segment slots.
                $l_chassis_dao->remove_chassis_item_assignments($l_chassis_item_row['isys_cats_chassis_slot_list__id']);
            }

            // Unposition the segmentation object.
            $l_segmentation_location_row = $l_location_dao->get_data(null, $p_segmentation_object_id)
                ->get_row();
            $l_location_dao->save($l_segmentation_location_row['isys_catg_location_list__id'], $l_segmentation_location_row['isys_obj__id'], $p_rack_object_id,
                $p_rack_object_id, null, null, null, $l_segmentation_location_row['isys_catg_location_list__description'], null,
                new Coordinate([$l_segmentation_location_row['latitude'], $l_segmentation_location_row['longitude']]));

            // Finally rank the segmentation object, according to the tenant settings...
            switch (isys_tenantsettings::get('cmdb.rack.rank-detached-segment-objects', C__RACK_DETACH_SEGMENT_ACTION__NONE)) {
                default:
                case C__RACK_DETACH_SEGMENT_ACTION__NONE:

                    break;

                case C__RACK_DETACH_SEGMENT_ACTION__ARCHIVE:
                    isys_cmdb_dao::instance($this->m_database_component)
                        ->rank_record($p_segmentation_object_id, C__CMDB__RANK__DIRECTION_DELETE, 'isys_obj');
                    break;

                case C__RACK_DETACH_SEGMENT_ACTION__PURGE:
                    isys_cmdb_dao::instance($this->m_database_component)
                        ->rank_record($p_segmentation_object_id, C__CMDB__RANK__DIRECTION_DELETE, 'isys_obj', null, true);
                    break;
            }

            return [
                'success' => true,
                'data'    => array_values($this->get_assigned_objects($p_rack_object_id)),
                'message' => ''
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data'    => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Returns the options for the "position in rack" dialog.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_positions_in_rack($p_obj_id)
    {
        return isys_cmdb_dao_category_g_location::instance($this->m_database_component)
            ->get_positions_in_rack($p_obj_id);
    }

    /**
     * This method will return the object ID of a rack. Either if the given object is a rack or the immediate parent is one.
     *
     * @param integer $p_object_id
     *
     * @return array
     */
    protected function get_immediate_parent_rack($p_object_id)
    {
        try {
            $l_dao = isys_cmdb_dao_category_g_location::instance($this->m_database_component);
            $l_return = 0;

            $l_object_data = $l_dao->get_data(null, $p_object_id, '', null, C__RECORD_STATUS__NORMAL)
                ->get_row();

            if ($l_object_data['isys_obj_type__isysgui_cats__id'] == defined_or_default('C__CATS__ENCLOSURE')) {
                $l_return = $p_object_id;
            }

            /*
            $l_object_parent_data = $l_dao
                ->get_data(null, $l_object_data['isys_catg_location_list__parentid'], '', null, C__RECORD_STATUS__NORMAL)
                ->get_row();

            if ($l_object_parent_data['isys_obj_type__isysgui_cats__id'] == C__CATS__ENCLOSURE)
            {
                $l_return = $l_object_data['isys_catg_location_list__parentid'];
            }
            */

            return [
                'success' => true,
                'data'    => $l_return,
                'message' => ''
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data'    => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * We use this method to find out if the given rack is capable of vertical-slots.
     *
     * @param integer $p_object_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_rack_options($p_object_id)
    {
        $l_dao = isys_cmdb_dao_category_g_location::instance($this->m_database_component);
        $l_return = [];
        $l_specific_cat = $l_dao->get_object($p_object_id)
            ->get_row_value('isys_obj_type__isysgui_cats__id');

        if ($l_specific_cat != defined_or_default('C__CATS__ENCLOSURE')) {
            return false;
        }

        $l_options = $l_dao->callback_property_assembly_options(isys_request::factory()
            ->set_row(['isys_catg_location_list__parentid' => $p_object_id]));

        foreach ($l_options as $l_option_id => $l_option) {
            $l_return[] = [
                'id'    => $l_option_id,
                'title' => $l_option
            ];
        }

        return $l_return;
    }

    /**
     * Method for retrieving the "front", "rear" and "both" data.
     *
     * @param   integer $p_option Defines if you need the insertion-options for horizontal or vertical assignment.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_rack_insertions($p_option)
    {
        $l_insertions = isys_cmdb_dao_category_g_location::instance($this->m_database_component)
            ->callback_property_insertion(isys_request::factory());

        $l_return = [
            [
                'id'    => C__INSERTION__FRONT,
                'title' => $l_insertions[C__INSERTION__FRONT]
            ],
            [
                'id'    => C__INSERTION__REAR,
                'title' => $l_insertions[C__INSERTION__REAR]
            ]
        ];

        if ($p_option == C__RACK_INSERTION__HORIZONTAL) {
            $l_return[] = [
                'id'    => C__INSERTION__BOTH,
                'title' => $l_insertions[C__INSERTION__BOTH]
            ];
        }

        return $l_return;
    }

    /**
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_racks_recursive($p_obj_id)
    {
        $l_loc_dao = isys_cmdb_dao_location::instance($this->m_database_component);
        $l_rack_dao = isys_cmdb_dao_category_s_enclosure::instance($this->m_database_component);

        $l_return = [];

        $l_objects = $l_loc_dao->get_child_locations_recursive($p_obj_id);

        if (defined('C__OBJTYPE__ENCLOSURE')) {
            foreach ($l_objects as $l_object) {
                // We only want to load racks, so we check for the object-type.
                if ($l_object['isys_obj__isys_obj_type__id'] == C__OBJTYPE__ENCLOSURE && $l_object['isys_obj__id'] > 0 && $l_object['parent'] != $p_obj_id) {
                    $l_return[] = $l_rack_dao->prepare_rack_data($l_object['isys_obj__id'], $l_object['isys_obj__title']);
                }
            }
        }

        return $l_return;
    }

    /**
     * Method for removing an object out of the rack.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function remove_object_assignment()
    {
        isys_cmdb_dao_location::instance($this->m_database_component)
            ->update_position($_POST['obj_id']);

        return array_values($this->get_assigned_objects($_POST['rack_obj_id']));
    }

    /**
     * Method for saving the rack-units of an object.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_height
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function save_object_ru($p_obj_id, $p_height)
    {
        $l_return = [
            'success' => true,
            'data'    => null,
            'message' => ''
        ];

        try {
            // Retrieve the formfactor DAO.
            $l_dao = isys_cmdb_dao_category_g_formfactor::instance($this->m_database_component);

            // And get the data.
            $l_row = $l_dao->get_data(null, $p_obj_id)
                ->get_row();

            if (empty($l_row)) {
                $l_result = !!$l_dao->create_data(['isys_obj__id' => $p_obj_id, 'rackunits' => $p_height]);
            } else {
                $l_result = $l_dao->set_rack_hu($p_obj_id, $p_height);
            }

            $l_return['success'] = $l_result;
            $l_return['data'] = ['height' => $p_height];
        } catch (Exception $e) {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        }

        return $l_return;
    }

    protected function save_position_in_location($p_positions)
    {
        return [
            'success' => isys_cmdb_dao_category_s_enclosure::instance($this->m_database_component)
                ->save_position_in_location(isys_format_json::decode($p_positions))
        ];
    }
}
