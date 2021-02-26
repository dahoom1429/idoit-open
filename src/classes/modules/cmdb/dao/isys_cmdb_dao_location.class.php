<?php
/**
 * i-doit
 * DAO: Location-object access class.
 *
 * @package     i-doit
 * @subpackage  CMDB_Low-Level_API
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

// Fake autoloading of MPTT classes :-)
(class_exists("isys_component_dao_mptt") && interface_exists("isys_mptt_callback")) or die("Failed loading MPTT framework in " . __FILE__);

/**
 * Class isys_cmdb_dao_location
 */
class isys_cmdb_dao_location extends isys_cmdb_dao implements isys_mptt_callback
{
    /**
     * MPTT DAO object.
     *
     * @var  isys_component_dao_mptt
     */
    private $m_mptt;

    /**
     * Temporal array for MPTT results.
     *
     * @var  array
     */
    private $m_tree = [];

    private $locationFixCounter = [];

    /**
     * Retrieves a DAO result with all location types.
     *
     * @return  isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_location_types()
    {
        return $this->retrieve('SELECT * FROM isys_obj_type WHERE isys_obj_type__container <> 0;');
    }

    /**
     * Updates the position of a rack element (child).
     *
     * @param   integer $p_object_id
     * @param   integer $p_option
     * @param   integer $p_insertion
     * @param   integer $p_pos
     *
     * @return  boolean
     */
    public function update_position($p_object_id, $p_option = null, $p_insertion = null, $p_pos = null)
    {
        $l_data = $this->m_mptt->get_by_node_id($p_object_id);

        if (is_object($l_data)) {
            $l_array = $l_data->get_row(IDOIT_C__DAO_RESULT_TYPE_ARRAY);
            $l_dao_ff = isys_cmdb_dao_category_g_formfactor::instance($this->get_database_component());
            $l_changed = false;
            $l_hu_child = 1;

            $l_sql = "UPDATE isys_catg_location_list SET
				isys_catg_location_list__pos = " . $this->convert_sql_int($p_pos) . ",
				isys_catg_location_list__insertion = " . $this->convert_sql_int($p_insertion) . ",
				isys_catg_location_list__option = " . $this->convert_sql_id($p_option) . "
				WHERE isys_catg_location_list__id = " . $this->convert_sql_id($l_array["isys_catg_location_list__id"]) . ";";

            if ($l_array['isys_catg_location_list__pos'] != $p_pos || $l_array['isys_catg_location_list__insertion'] != $p_insertion ||
                $l_array['isys_catg_location_list__option'] != $p_option) {
                $l_hu_child = $l_dao_ff->get_rack_hu($l_array['isys_catg_location_list__isys_obj__id']);
                $l_changed = true;
                $l_changes_arr = [];
            }

            if ($this->update($l_sql)) {
                if ($this->apply_update()) {
                    if ($l_changed) {
                        $l_data = [
                            'hu_child'      => $l_hu_child,
                            'hu_parent'     => $l_dao_ff->get_rack_hu($l_array['isys_catg_location_list__parentid']),
                            'enclosureData' => isys_cmdb_dao_category_s_enclosure::instance($this->get_database_component())
                                ->get_data(null, $l_array['isys_catg_location_list__parentid'])
                                ->get_row(),
                            'catData'       => $l_array,
                            'postOption'    => $p_option,
                            'postPosition'  => $p_pos
                        ];
                        $this->handle_logbook_rack_position($l_changes_arr, $l_data);

                        $l_data = [
                            'oldInsertion' => $l_array['isys_catg_location_list__insertion'],
                            'newInsertion' => $p_insertion
                        ];
                        $this->handle_logbook_insertion($l_changes_arr, $l_data);

                        $l_data = [
                            'oldOption' => $l_array['isys_catg_location_list__option'],
                            'newOption' => $p_option
                        ];
                        $this->handle_logbook_option($l_changes_arr, $l_data);

                        if (count($l_changes_arr) > 0) {
                            $l_changes_arr = serialize($l_changes_arr);
                            isys_event_manager::getInstance()
                                ->triggerCMDBEvent(
                                    'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                    $this->get_last_query(),
                                    $l_array['isys_catg_location_list__parentid'],
                                    $this->get_objTypeID($l_array['isys_catg_location_list__parentid']),
                                    isys_application::instance()->container->get('language')
                                        ->get('LC__CMDB__CATS__ENCLOSURE'),
                                    $l_changes_arr
                                );
                        }
                    }

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Wrapper for handling the option for the logbook entry
     *
     * @param $p_changes
     * @param $p_data
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function handle_logbook_option(&$p_changes, $p_data)
    {
        if ($p_data['oldOption'] != $p_data['newOption']) {
            $l_option = [
                C__RACK_INSERTION__HORIZONTAL => isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__CATS__ENCLOSURE__HORIZONTAL'),
                C__RACK_INSERTION__VERTICAL   => isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__CATS__ENCLOSURE__VERTICAL')
            ];
            $l_option_from = $l_option[$p_data['oldOption']];
            $l_option_to = $l_option[$p_data['newOption']];
            $p_changes['isys_cmdb_dao_category_g_location::option'] = [
                'from' => $l_option_from,
                'to'   => $l_option_to
            ];
        }
    }

    /**
     * Wrapper for handling the insertion for the logbook entry
     *
     * @param $p_changes
     * @param $p_data
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function handle_logbook_insertion(&$p_changes, $p_data)
    {
        if ($p_data['oldInsertion'] != $p_data['newInsertion']) {
            $l_insertion_arr = [
                C__INSERTION__FRONT => isys_application::instance()->container->get('language')
                    ->get("LC__CMDB__CATG__LOCATION_FRONT"),
                C__INSERTION__REAR  => isys_application::instance()->container->get('language')
                    ->get("LC__CMDB__CATG__LOCATION_BACK"),
                C__INSERTION__BOTH  => isys_application::instance()->container->get('language')
                    ->get("LC__CMDB__CATG__LOCATION_BOTH")
            ];
            $l_insertion_from = $l_insertion_arr[$p_data['oldInsertion']];
            $l_insertion_to = $l_insertion_arr[$p_data['newInsertion']];
            $p_changes['isys_cmdb_dao_category_g_location::insertion'] = [
                'from' => $l_insertion_from,
                'to'   => $l_insertion_to
            ];
        }
    }

    /**
     * Wrapper for handleing the rack positioning for the logbook entry
     *
     * @param $p_data
     *
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function handle_logbook_rack_position(&$p_changes, $p_data)
    {
        if ($p_data['catData']['isys_catg_location_list__option'] == C__RACK_INSERTION__VERTICAL) {
            $l_pos_from = 'Slot ' . $p_data['catData']['isys_catg_location_list__pos'];
        } else {
            $l_pos_from = 'HE ';
            switch ($p_data['enclosureData']['isys_cats_enclosure_list__slot_sorting']) {
                case 'desc':
                    $l_pos_from .= ($p_data['hu_parent'] + 1 - $p_data['catData']['isys_catg_location_list__pos']) . ' -> ' .
                        ($p_data['hu_parent'] + 1 - $p_data['catData']['isys_catg_location_list__pos'] - $p_data['hu_child'] + 1);
                    break;
                case 'asc':
                default:
                    $l_pos_from .= $p_data['catData']['isys_catg_location_list__pos'] . ' -> ' .
                        ($p_data['catData']['isys_catg_location_list__pos'] + $p_data['hu_child'] - 1);
                    break;
            }
        }

        if ($p_data['postOption'] == C__RACK_INSERTION__VERTICAL) {
            $l_pos_to = 'Slot ' . $p_data['postPosition'];
        } else {
            $l_pos_to = 'HE ';
            switch ($p_data['enclosureData']['isys_cats_enclosure_list__slot_sorting']) {
                case 'desc':
                    $l_pos_to .= ($p_data['hu_parent'] + 1 - $p_data['postPosition']) . ' -> ' .
                        ($p_data['hu_parent'] + 1 - $p_data['postPosition'] - $p_data['hu_child'] + 1);
                    break;
                case 'asc':
                default:
                    $l_pos_to .= $p_data['postPosition'] . ' -> ' . ($p_data['postPosition'] + $p_data['hu_child'] - 1);
                    break;
            }
        }

        if ($l_pos_from != $l_pos_to) {
            $p_changes['isys_cmdb_dao_category_g_location::pos'] = [
                'from' => $l_pos_from,
                'to'   => $l_pos_to
            ];
        }
    }

    /**
     * Retrieve a location by a given object.
     *
     * @param integer $p_object_id
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_location_by_object_id($p_object_id)
    {
        return $this->get_location(null, null, C__RECORD_STATUS__NORMAL, $p_object_id);
    }

    /**
     * Retrieve location objects.
     *
     * @param int    $p_parent_id
     * @param bool   $p_front
     * @param int    $p_record_status
     * @param int    $p_object_id
     * @param bool   $p_show_in_tree
     * @param string $p_condition
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_location(
        $p_parent_id = null,
        $p_front = true,
        $p_record_status = C__RECORD_STATUS__NORMAL,
        $p_object_id = null,
        $p_show_in_tree = true,
        $p_condition = ''
    ) {
        $l_strSQL = 'SELECT * FROM isys_catg_location_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_location_list__isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			LEFT JOIN isys_catg_formfactor_list ON isys_catg_formfactor_list__isys_obj__id = isys_catg_location_list__isys_obj__id
			WHERE isys_obj__status = ' . $this->convert_sql_int($p_record_status) . ' 
			AND isys_catg_location_list__status = ' . $this->convert_sql_int($p_record_status) . '
			AND isys_obj__status != ' . $this->convert_sql_int(C__RECORD_STATUS__TEMPLATE);

        if ($p_parent_id !== null) {
            $l_strSQL .= ' AND isys_catg_location_list__parentid = ' . $this->convert_sql_id($p_parent_id);
        }

        if ($p_object_id !== null) {
            $l_strSQL .= ' AND isys_obj__id = ' . $this->convert_sql_id($p_object_id);
        }

        if ($p_front !== null) {
            if ($p_front) {
                $l_strSQL .= ' AND (isys_catg_location_list__insertion = 1 OR isys_catg_location_list__insertion = 2)';
            } else {
                $l_strSQL .= ' AND (isys_catg_location_list__insertion = 0 OR isys_catg_location_list__insertion = 2 OR isys_catg_location_list__insertion is null)';
            }
        }

        if ($p_parent_id !== null && $p_show_in_tree) {
            $l_strSQL .= ' AND isys_obj_type__show_in_tree = 1';
        }

        if ($p_condition != '') {
            $l_strSQL .= ' ' . $p_condition . ' ';
        }

        return $this->retrieve($l_strSQL . ";");
    }

    /**
     * Return objects which are containers or which can be shown in a rack.
     *
     * @param bool $p_bContainer
     * @param bool $p_bInRack
     * @param int  $p_record_status
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_location_objects($p_bContainer = true, $p_bInRack = true, $p_record_status = C__RECORD_STATUS__NORMAL)
    {
        $l_strSQL = 'SELECT * FROM isys_obj
            INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
            INNER JOIN isys_catg_global_list ON isys_catg_global_list__isys_obj__id = isys_obj__id
            WHERE isys_obj_type__show_in_tree = 1
            AND isys_obj_type__status = ' . $this->convert_sql_int($p_record_status);

        if ($p_bContainer) {
            $l_strSQL .= ' AND isys_obj_type__container = 1';
        }

        if ($p_bInRack) {
            $l_strSQL .= ' AND isys_obj_type__show_in_rack = 1';
        }

        return $this->retrieve($l_strSQL . ';');
    }

    /**
     * Retrieves a location type specified by $p_typeid.
     *
     * @param   integer $p_typeid
     *
     * @return  isys_component_dao_result
     */
    public function get_location_type_by_id($p_typeid)
    {
        if (is_numeric($p_typeid) && $p_typeid > 0) {
            return $this->retrieve('SELECT * FROM isys_obj_type WHERE isys_obj_type__id = ' . $this->convert_sql_id($p_typeid) . ' AND isys_obj_type__container <> 0;');
        }

        return null;
    }

    /**
     * Attaches object specified by $p_objid to location object specified by $p_parent_objid. If the object is already attached
     * to a location object, the attachment is overwritten. Returns integer to identify ID of created record.
     *
     * @param   integer $p_objid
     * @param   integer $p_parent_objid
     * @param   array   $p_extradata
     *
     * @return  integer
     * @author  dennis stuecken <dstuecken@i-doit.org>
     */
    public function attach($p_objid, $p_parent_objid, $p_extradata = null)
    {
        if ($this->obj_exists($p_objid)) {
            $l_loc_obj_type = $this->get_location_type_by_id($this->get_objTypeID($p_parent_objid));

            /* The object can only be attached, if one of these two conditions
               is met:

               a) Destination type is a container type
               b) Destination object is the root location object
            */
            if (($l_loc_obj_type && $l_loc_obj_type->num_rows() > 0) || ($p_parent_objid == $this->get_root_location_as_integer())) {
                /* Is location entry existent for location category? */
                $l_q = 'SELECT * FROM isys_catg_location_list WHERE isys_catg_location_list__isys_obj__id = ' . $this->convert_sql_id($p_objid) . ';';

                $l_dbres = $this->retrieve($l_q);

                if ($l_dbres) {

                    /* If not, add one, otherwise rebind entry to new parent */
                    $this->m_mptt->action_stack_add(($l_dbres->num_rows() == 0) ? C__MPTT__ACTION_ADD : C__MPTT__ACTION_MOVE, [
                        "node_id"   => $p_objid,
                        "parent_id" => $p_parent_objid
                    ]);

                    /* Query for location entry again */
                    $l_dbres = $this->retrieve($l_q);

                    /* Fetch record data */
                    $l_entrydata = $l_dbres->get_row();

                    /* Check for nullentries before updating */
                    $l_property = (empty($l_entrydata["isys_catg_location_list__property"])) ? 0 : $l_entrydata["isys_catg_location_list__property"];
                    $l_status = (empty($l_entrydata["isys_catg_location_list__status"])) ? 0 : $l_entrydata["isys_catg_location_list__status"];

                    /* Update location entry */
                    $this->m_mptt->action_stack_add(C__MPTT__ACTION_UPDATE, [
                        "node_id"                              => $p_objid,
                        "isys_catg_location_list__title"       => $l_entrydata["isys_catg_location_list__title"],
                        "isys_catg_location_list__description" => $l_entrydata["isys_catg_location_list__description"],
                        "isys_catg_location_list__lft"         => (int) $l_entrydata["isys_catg_location_list__lft"],
                        "isys_catg_location_list__rgt"         => (int) $l_entrydata["isys_catg_location_list__rgt"],
                        "isys_catg_location_list__property"    => (int) $l_property,
                        "isys_catg_location_list__status"      => (int) $l_status
                    ]);

                    /* Extra data? */
                    if ($p_extradata != null && is_array($p_extradata)) {
                        $l_arrupdate = ["node_id" => $p_objid];

                        foreach ($p_extradata as $l_field => $l_data) {
                            $l_arrupdate[$l_field] = $l_data;
                        }

                        $this->m_mptt->action_stack_add(C__MPTT__ACTION_UPDATE, $l_arrupdate);
                    }

                    /* Return ID of created / updated record */

                    return $l_entrydata["isys_catg_location_list__id"];
                }
            } else {
                // Location not a container.
                if ($l_loc_obj_type && $l_loc_obj_type->num_rows() <= 0) {
                    $l_row = $this->get_type_by_id($this->get_objTypeID($p_parent_objid));

                    isys_component_template_infobox::instance()
                        ->set_message("Your destination object is not a container. " . "Change your object-type config in order to add objects into \"" .
                            isys_application::instance()->container->get('language')
                                ->get($l_row["isys_obj_type__title"]) . "\".", null, null, null, defined_or_default('C__LOGBOOK__ALERT_LEVEL__3', 4));
                }
            }
        }

        return null;
    }

    /**
     * Detaches object specified by $p_objid from its location.
     *
     * @param   integer $p_objid
     *
     * @return  boolean
     */
    public function detach($p_objid)
    {
        if ($this->obj_exists($p_objid)) {
            // This is a real detach - so delete.
            $this->m_mptt->action_stack_add(C__MPTT__ACTION_DELETE, ["node_id" => $p_objid]);

            return true;
        }

        return false;
    }

    /**
     * Returns the DAO result containing the record with the object, which is mapping the root location.
     *
     * @return  isys_component_dao_result
     */
    public function get_root_location()
    {
        return $this->m_mptt->get_by_node_id(defined_or_default('C__OBJ__ROOT_LOCATION'));
    }

    /**
     * Returns the ID of the root location object.
     *
     * @return  mixed
     */
    public function get_root_location_as_integer()
    {
        $l_locres = $this->get_root_location();

        if (count($l_locres) > 0) {
            $l_rootdata = $l_locres->get_row();

            return $l_rootdata["isys_catg_location_list__isys_obj__id"];
        }

        return null;
    }

    /**
     * Return tree object with all locations
     *
     * @param   integer $p_objid
     *
     * @return  mixed
     */
    public function &get_locations_by_obj_id($p_objid)
    {
        // Create new result tree.
        $this->m_tree = [];

        // Perform read operation via MPTT.
        if ($this->m_mptt->read($p_objid, $this)) {
            // Return resulting tree.
            return $this->m_tree;
        }

        return null;
    }

    /**
     * @param $p_objid
     * @param $p_origindata
     *
     * @return isys_component_dao_result|null
     */
    public function get_path_by_obj_id($p_objid, &$p_origindata)
    {
        if ($this->obj_exists($p_objid)) {
            $l_locres = $this->m_mptt->get_by_node_id($p_objid);

            if (is_object($l_locres)) {
                if ($l_locres->num_rows()) {
                    $l_locdata = $l_locres->get_row();

                    if ($l_locdata["isys_catg_location_list__parentid"] == null) {
                        return null;
                    }

                    $l_left = $l_locdata["isys_catg_location_list__lft"];
                    $l_right = $l_locdata["isys_catg_location_list__rgt"];

                    $p_origindata = $l_locdata;

                    /* Try to find path */

                    return $this->m_mptt->get_outer_by_left_right($l_left, $l_right);
                }
            }
        }

        return null;
    }

    /**
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function _location_fix()
    {
        $this->locationFixCounter = [];

        /*
         * The location fix will try to do the following things:
         *
         * 1) re-create root location object type (if it does not exist)
         * 2) re-create root location object (if it does not exist)
         * 3) re-create root location category entry (if it does not exist)
         * 4) Remove all duplicated category entries
         * 5) Remove all category entries that have no assigned object (for when the object got deleted or so)
         * 6) Set all "parents" to the root location, whose old parents don't exist any more
         * 7) Make a "consistency" test, if there are any missing links inside the location path
         * 8) Finally re-create the MPTT (Modified Preorder Tree Traversal / nested set)
         */

        // @see ID-6330 Fix possibly missing object type, object and category entry.
        $this->fixRootLocationObjectType();
        $this->fixRootLocationObject();
        $this->fixRootLocationCategoryEntry();

        // Remove all duplicated category entries.
        $this->removeDuplicatedLocationEntries();

        // Remove all category entries that have no assigned object (for when the object got deleted or so).
        $this->removeAllOrphanedLocationEntries();

        // Set all parents to the root location, whose old parents don't exist any more.
        $this->setParentsForEntriesWithMissingParent();

        /*
         * @todo  "Consistency check" - It would be great if we could find missing objects and send their "children" to their "grandparents" (since their direct parents are gone).
         *
         * Consider this setup:
         * Root > A > B > C > D
         *
         * If we remove B without any furhter logic (simple DELETE FROM ... statement) the path will look like this:
         * Root > A > ? > C > D
         *
         * Ideally the result would look like this:
         * Root > A > C > D
         *
         * But this resolution would also be okay:
         * Root > A
         * Root > C > D
         */

        // Finally re-create the MPTT (Modified Preorder Tree Traversal / nested set)
        $this->initialize_lft_rgt();
        $this->save();

        return $this->apply_update() ? $this->regenerate_missing_relation_objects() : false;
    }

    /**
     * Method for returning some stats about the location fix.
     *
     * @return array
     */
    public function getLocationFixStats(): array
    {
        return $this->locationFixCounter;
    }

    /**
     *
     */
    public function save()
    {
        $this->m_mptt->write($this);
    }

    /**
     * @return  isys_component_dao_mptt
     */
    public function get_mptt()
    {
        return $this->m_mptt;
    }

    /**
     * Callback handler for read operations.
     *
     * @param  string  $p_table
     * @param  integer $p_level
     * @param  integer $p_id
     * @param  integer $p_node_id
     * @param  integer $p_parent_id
     * @param  string  $p_const
     * @param  integer $p_left
     * @param  integer $p_right
     * @param  mixed   $p_userdata
     * @param  string  $p_title
     */
    public function mptt_read($p_table, $p_level, $p_id, $p_node_id, $p_parent_id, $p_const, $p_left, $p_right, $p_userdata, $p_title = "")
    {
        $this->m_tree[] = [
            $p_node_id,
            $p_parent_id,
            $p_const,
            $p_level,
            $p_title
        ];
    }

    /**
     * Callback handler for write operations
     *
     * @param   integer $p_node_id
     * @param   integer $p_parent_id
     * @param   string  $p_const
     * @param   integer $p_left
     * @param   integer $p_right
     *
     * @return  boolean
     */
    public function mptt_write(&$p_node_id, &$p_parent_id, &$p_const, &$p_left, &$p_right)
    {
        return true;
    }

    /**
     * Retrieve the child locations of a given parent location.
     * If the parentID is null, the root location is retrieved.
     * Retrieves only NORMAL records.
     *
     * @param   integer $p_parentID
     * @param   boolean $p_hiderootlocation
     * @param   boolean $p_container_only
     * @param   boolean $p_consider_rights
     * @param   null    $status
     *
     * @return  isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_child_locations($p_parentID = null, $p_hiderootlocation = false, $p_container_only = false, $p_consider_rights = false, $status = null)
    {
        $l_query = "SELECT
			(SELECT COUNT(child.isys_catg_location_list__id) FROM isys_catg_location_list child ";

        $l_query .=  "INNER JOIN isys_obj childObject ON child.isys_catg_location_list__isys_obj__id = childObject.isys_obj__id " .
            "INNER JOIN isys_obj_type childType ON childObject.isys_obj__isys_obj_type__id = childType.isys_obj_type__id ";

        if ($p_container_only) {
            $l_query .= "WHERE child.isys_catg_location_list__parentid = parentObject.isys_obj__id AND childType.isys_obj_type__container = 1";
        } else {
            $l_query .= "WHERE child.isys_catg_location_list__parentid = parentObject.isys_obj__id";
        }

        if (!empty($status)) {
            $l_query .=  ' AND childObject.isys_obj__status = ' . $this->convert_sql_id($status);
        }

        $l_query .= ") AS ChildrenCount, parent.*, parentObject.*, parentType.*, isys_cmdb_status.*
			FROM isys_catg_location_list parent
			INNER JOIN isys_obj parentObject ON parent.isys_catg_location_list__isys_obj__id = parentObject.isys_obj__id
			INNER JOIN isys_obj_type parentType ON parentObject.isys_obj__isys_obj_type__id = parentType.isys_obj_type__id
			INNER JOIN isys_cmdb_status ON parentObject.isys_obj__isys_cmdb_status__id = isys_cmdb_status__id 
			WHERE TRUE ";

        if ($p_consider_rights && $p_parentID != null) {
            $l_query .= isys_auth_cmdb_objects::instance()
                    ->get_allowed_objects_condition() . ' ';
        }

        if ($p_parentID == null && defined('C__OBJ__ROOT_LOCATION')) {
            if ($p_hiderootlocation) {
                $l_query .= " AND parent.isys_catg_location_list__parentid = " . $this->convert_sql_id(C__OBJ__ROOT_LOCATION) . isys_auth_cmdb_objects::instance()
                        ->get_allowed_objects_condition();
            } else {
                $l_query .= " AND parent.isys_catg_location_list__isys_obj__id = " . $this->convert_sql_id(C__OBJ__ROOT_LOCATION);
            }
        } else {
            $l_query .= " AND parent.isys_catg_location_list__parentid = " . $this->convert_sql_id($p_parentID);
        }

        if ($p_container_only) {
            $l_query .= " AND parentType.isys_obj_type__container = 1";
        }

        $l_query .= " AND parentObject.isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . "
			GROUP BY parent.isys_catg_location_list__id
			ORDER BY parentObject.isys_obj__title;";

        return $this->retrieve($l_query);
    }

    /**
     * Method for retrieving all objects which are located underneath the given object.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_child_locations_recursive($p_obj_id)
    {
        $l_return = [];

        if ($p_obj_id > 0) {
            $l_sql = 'SELECT obj.*, objtype.*, loc.isys_catg_location_list__parentid AS parent
				FROM isys_catg_location_list loc
				LEFT JOIN isys_obj obj ON isys_obj__id = isys_catg_location_list__isys_obj__id
				LEFT JOIN isys_obj_type objtype ON isys_obj__isys_obj_type__id = isys_obj_type__id
				WHERE isys_catg_location_list__parentid = ' . $this->convert_sql_id($p_obj_id);

            $l_res = $this->retrieve($l_sql);

            if ($l_res->num_rows() > 0) {
                while ($l_row = $l_res->get_row()) {
                    $l_row['isys_obj_type__title'] = isys_application::instance()->container->get('language')
                        ->get($l_row['isys_obj_type__title']);

                    $l_return[$l_row['isys_obj__id']] = $l_row;

                    $l_return = $l_return + $this->get_child_locations_recursive($l_row['isys_obj__id']);
                }
            }
        }

        return $l_return;
    }

    /**
     * Retrieve the hierarchy of nodes above the given node up to the root.
     *
     * @param int  $nodeId
     * @param bool $hideRootLocation
     *
     * @return string
     */
    public function get_node_hierarchy($nodeId, bool $hideRootLocation = false)
    {
        $hierarchy = [];

        $iterationId = (int)$nodeId;

        if (defined('C__OBJ__ROOT_LOCATION')) {
            while ($iterationId != C__OBJ__ROOT_LOCATION && $iterationId !== 0) {
                if (in_array($iterationId, $hierarchy)) {
                    isys_notify::error('Found a recursion, the object #' . $iterationId . ' was found twice in the location path', ['sticky' => true]);

                    return '';
                }

                $hierarchy[] = $iterationId;

                $sql = 'SELECT isys_catg_location_list__parentid
                    FROM isys_catg_location_list
                    WHERE isys_catg_location_list__isys_obj__id = ' . $this->convert_sql_id($iterationId);

                $iterationId = (int)$this->retrieve($sql)->get_row_value('isys_catg_location_list__parentid');
            }

            if ($iterationId == null) {
                $hideRootLocation = false;
            }

            if (!$hideRootLocation) {
                $hierarchy[] = C__OBJ__ROOT_LOCATION;
            }
        }

        return implode(',', $hierarchy);
    }

    /**
     * Method which resets the lft nad rgt where no parents are set.
     *
     * @return  boolean
     */
    public function initialize_lft_rgt()
    {
        return $this->update("UPDATE isys_catg_location_list SET
			isys_catg_location_list__lft = NULL,
			isys_catg_location_list__rgt = NULL
			WHERE ISNULL(isys_catg_location_list__parentid)");
    }

    /**
     * Method which rebuilds all locations which have no relation object
     */
    public function regenerate_missing_relation_objects()
    {
        $l_dao = isys_cmdb_dao_category_g_relation::instance(isys_application::instance()->database);

        try {
            $l_root_location = 'SELECT isys_obj__id FROM isys_obj WHERE isys_obj__const = \'C__OBJ__ROOT_LOCATION\';';
            $l_root_location_id = $l_dao->retrieve($l_root_location)
                ->get_row_value('isys_obj__id');
            $l_sql = 'SELECT * FROM isys_catg_location_list
				WHERE isys_catg_location_list__isys_catg_relation_list__id IS NULL AND
				(isys_catg_location_list__isys_obj__id != \'' . $l_root_location_id . '\' AND isys_catg_location_list__isys_obj__id > 0);';
            $l_res = $l_dao->retrieve($l_sql);

            if ($l_res->num_rows() > 0) {
                while ($l_row = $l_res->get_row()) {
                    // rebuild missing relation for location entries
                    $l_dao->handle_relation(
                        $l_row['isys_catg_location_list__id'],
                        'isys_catg_location_list',
                        defined_or_default('C__RELATION_TYPE__LOCATION'),
                        null,
                        $l_row['isys_catg_location_list__parentid'],
                        $l_row['isys_catg_location_list__isys_obj__id']
                    );
                }
            }

            return true;
        } catch (Exception $e) {
            isys_notify::error('Error with following message: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Method for creating the "location generic" object type, in case it does not exist.
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    private function fixRootLocationObjectType()
    {
        $this->locationFixCounter['Recreated object types'] = 0;
        $checkObjectTypeSql = 'SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__const = \'C__OBJTYPE__LOCATION_GENERIC\' LIMIT 1;';

        if (!$this->retrieve($checkObjectTypeSql)->get_row_value('isys_obj_type__id')) {
            $objectTypeGroupId = $this
                ->retrieve('SELECT isys_obj_type_group__id FROM isys_obj_type_group WHERE isys_obj_type_group__const = \'C__OBJTYPE_GROUP__INFRASTRUCTURE\' LIMIT 1')
                ->get_row_value('isys_obj_type_group__id');

            $this->insert_new_objtype(
                $objectTypeGroupId,
                'LC__CMDB__OBJTYPE__LOCATION_GENERIC',
                'C__OBJTYPE__LOCATION_GENERIC',
                false,
                true,
                null,
                'images/icons/silk/house.png',
                null,
                C__RECORD_STATUS__NORMAL,
                null,
                false
            );

            isys_notify::info('The "Generic location" object type has been created, you should clear your cache!', ['life' => 5]);

            $this->locationFixCounter['Recreated object types'] = 1;

            return true;
        }

        return true;
    }

    /**
     * Method for creating the "root location", in case it does not exist.
     *
     * @return bool
     * @throws isys_exception_database
     * @throws isys_exception_dao
     */
    private function fixRootLocationObject()
    {
        $this->locationFixCounter['Recreated objects'] = 0;
        $checkObjectSql = 'SELECT isys_obj__id FROM isys_obj WHERE isys_obj__const = \'C__OBJ__ROOT_LOCATION\' LIMIT 1;';

        if (!$this->retrieve($checkObjectSql)->get_row_value('isys_obj__id')) {
            $objectTypeId = (int) $this
                ->retrieve('SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__const = \'C__OBJTYPE__LOCATION_GENERIC\' LIMIT 1;')
                ->get_row_value('isys_obj_type__id');

            $objectId = $this->insert_new_obj($objectTypeId, null, 'Root location', null, C__RECORD_STATUS__NORMAL);

            $createObjectSql = 'UPDATE isys_obj SET
                isys_obj__const = \'C__OBJ__ROOT_LOCATION\',
                isys_obj__created_by = \'system\',
                isys_obj__updated = NOW(),
                isys_obj__updated_by = \'system\',
                isys_obj__undeletable = 1
                WHERE isys_obj__id = ' . $this->convert_sql_id($objectId) . ';';

            isys_notify::info('The "Root Location" has been created, you should clear your cache!', ['life' => 5]);

            $this->locationFixCounter['Recreated objects'] = 1;

            return $this->update($createObjectSql) && $this->apply_update();
        }

        // We also set the status of the root location to "normal".
        $updateStatusQuery = 'UPDATE isys_obj 
            SET isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' 
            WHERE isys_obj__const = \'C__OBJ__ROOT_LOCATION\' 
            LIMIT 1;';

        return $this->update($updateStatusQuery) && $this->apply_update();
    }

    /**
     * Method for creating the root location category entry, in case it does not exist.
     *
     * @return bool
     * @throws isys_exception_database
     * @throws isys_exception_dao
     */
    private function fixRootLocationCategoryEntry()
    {
        $this->locationFixCounter['Recreated category entries'] = 0;
        $checkCategorySql = 'SELECT isys_catg_location_list__id 
            FROM isys_catg_location_list 
            WHERE isys_catg_location_list__isys_obj__id = (SELECT isys_obj__id FROM isys_obj WHERE isys_obj__const = \'C__OBJ__ROOT_LOCATION\' LIMIT 1);';

        if (!$this->retrieve($checkCategorySql)->get_row_value('isys_catg_location_list__id')) {
            $createCategorySql = 'INSERT INTO isys_catg_location_list SET
                isys_catg_location_list__isys_obj__id = (SELECT isys_obj__id FROM isys_obj WHERE isys_obj__const = \'C__OBJ__ROOT_LOCATION\' LIMIT 1),
                isys_catg_location_list__title = \'[LocationRoot]\',
                isys_catg_location_list__const = NULL,
                isys_catg_location_list__parentid = NULL,
                isys_catg_location_list__lft = 1,
                isys_catg_location_list__rgt = 2,
                isys_catg_location_list__pos = NULL,
                isys_catg_location_list__insertion = 1,
                isys_catg_location_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

            $this->locationFixCounter['Recreated category entries'] = 1;

            return $this->update($createCategorySql) && $this->apply_update();
        }

        // We also set the status of the root location to "normal".
        $updateStatusQuery = 'UPDATE isys_catg_location_list 
            SET isys_catg_location_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' 
            WHERE isys_catg_location_list__isys_obj__id = (SELECT isys_obj__id FROM isys_obj WHERE isys_obj__const = \'C__OBJ__ROOT_LOCATION\' LIMIT 1)
            LIMIT 1;';

        return $this->update($updateStatusQuery) && $this->apply_update();
    }

    /**
     * @return void
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    private function removeDuplicatedLocationEntries()
    {
        $this->locationFixCounter['Removed duplicated entries'] = 0;
        $this->locationFixCounter['Removed duplicated entries ERROR'] = 0;

        $sql = 'SELECT GROUP_CONCAT(isys_catg_location_list__id) AS ids, COUNT(isys_catg_location_list__isys_obj__id) AS n 
            FROM isys_catg_location_list 
            GROUP BY isys_catg_location_list__isys_obj__id 
            HAVING n > 1';

        $result = $this->retrieve($sql);

        while ($row = $result->get_row()) {
            $ids = explode(',', $row['ids']);

            // Sort the IDs to be able to take the first one (lowest) to keep it.
            sort($ids);

            // Remove the first (lowest) ID for us to keep it.
            array_shift($ids);

            foreach ($ids as $id) {
                if ($this->delete_entry($id, 'isys_catg_location_list')) {
                    $this->locationFixCounter['Removed duplicated entries'] ++;
                } else {
                    $this->locationFixCounter['Removed duplicated entries ERROR'] ++;
                }
            }
        }

        $this->apply_update();
    }

    /**
     * Remove all category entries that have no assigned object (for when the object got deleted or so).
     *
     * @return void
     * @throws isys_exception_database
     */
    private function removeAllOrphanedLocationEntries()
    {
        $this->locationFixCounter['Removed orphaned category entries'] = 0;
        $this->locationFixCounter['Removed orphaned category entries ERROR'] = 0;

        $sql = 'SELECT isys_catg_location_list__id AS entryId
            FROM isys_catg_location_list
            LEFT JOIN isys_obj ON isys_obj__id = isys_catg_location_list__isys_obj__id
            WHERE isys_obj__id IS NULL
            OR isys_catg_location_list__isys_obj__id IS NULL;';

        $result = $this->retrieve($sql);

        while ($row = $result->get_row()) {
            if ($this->delete_entry($row['entryId'], 'isys_catg_location_list')) {
                $this->locationFixCounter['Removed orphaned category entries'] ++;
            } else {
                $this->locationFixCounter['Removed orphaned category entries ERROR'] ++;
            }
        }
    }

    /**
     * @return void
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    private function setParentsForEntriesWithMissingParent()
    {
        $this->locationFixCounter['Set root location for entries with missing parents'] = 0;

        $rootLocationObjectId = (int)$this
            ->retrieve('SELECT isys_obj__id AS id FROM isys_obj WHERE isys_obj__const = \'C__OBJ__ROOT_LOCATION\' LIMIT 1;')
            ->get_row_value('id');

        $sql = 'SELECT isys_catg_location_list__id AS entryId
            FROM isys_catg_location_list
            LEFT JOIN isys_obj ON isys_obj__id = isys_catg_location_list__parentid
            WHERE isys_obj__id IS NULL
            AND isys_catg_location_list__parentid IS NOT NULL
            AND isys_catg_location_list__isys_obj__id != ' . $this->convert_sql_id($rootLocationObjectId) . ';';

        $result = $this->retrieve($sql);

        while ($row = $result->get_row()) {
            $sql = 'UPDATE isys_catg_location_list
                SET isys_catg_location_list__parentid = ' . $this->convert_sql_id($rootLocationObjectId) . '
                WHERE isys_catg_location_list__id = ' . $this->convert_sql_id($row['entryId']) . '
                LIMIT 1;';

            $this->update($sql);
            $this->apply_update();

            $this->locationFixCounter['Set root location for entries with missing parents'] ++;
        }
    }

    /**
     * Constructor
     *
     * @param  isys_component_database $p_db
     */
    public function __construct(isys_component_database &$p_db)
    {
        parent::__construct($p_db);

        $this->m_mptt = new isys_component_dao_mptt(
            $p_db,
            'isys_catg_location_list',
            'isys_catg_location_list__id',
            'isys_catg_location_list__isys_obj__id',
            'isys_catg_location_list__parentid',
            'isys_catg_location_list__const',
            'isys_catg_location_list__lft',
            'isys_catg_location_list__rgt'
        );
    }
}
