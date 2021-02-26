<?php

use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: global category for virtual objects
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_virtual_object extends isys_cmdb_dao_category_g_virtual implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'virtual_object';

    /**
     * Category's constant.
     *
     * @var  string
     *
     * @fixme No standard behavior!
     */
    protected $m_category_const = 'C__CATG__OBJECT';

    /**
     * Category's identifier.
     *
     * @fixme  No standard behavior!
     * @var    integer
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATG__OBJECT;

    /**
     * Category's list DAO.
     *
     * @var  string
     */
    protected $m_list = 'isys_cmdb_dao_list_catg_object';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Flag which defines if the category is only a list with an object browser.
     *
     * @var  boolean
     */
    protected $m_object_browser_category = true;

    /**
     * Property of the object browser
     *
     * @var string
     */
    protected $m_object_browser_property = 'assigned_object';

    /**
     * Category's table.
     *
     * @var  string
     */
    protected $m_table = 'isys_catg_location_list';

    /**
     * Const for logging action type
     */
    const IS_OBJECT_ADDED = 1;

    /**
     * Const for logging action type
     */
    const IS_OBJECT_REMOVED = 2;

    /**
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {
        $l_obj_id = ($p_obj_id !== null) ? $p_obj_id : $this->m_object_id;

        $l_sql = "SELECT COUNT(isys_catg_location_list__id) AS `count`
	        FROM isys_catg_location_list
	        INNER JOIN isys_obj ON isys_obj__id =  isys_catg_location_list__parentid
	        WHERE TRUE ";

        if (!empty($l_obj_id)) {
            $l_sql .= " AND isys_catg_location_list__parentid = " . $this->convert_sql_id($l_obj_id);
        }

        $l_sql .= " AND isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return (int)current($this->retrieve($l_sql)
            ->get_row());
    }

    /**
     * @param null   $p_catg_list_id
     * @param null   $p_obj_id
     * @param string $p_condition
     * @param null   $p_filter
     * @param null   $p_status
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT *, ST_AsText(isys_catg_location_list__gps) AS isys_catg_location_list__gps, ST_X(isys_catg_location_list__gps) AS latitude, ST_Y(isys_catg_location_list__gps) AS longitude FROM isys_catg_location_list
			INNER JOIN isys_obj ON isys_obj__id =  isys_catg_location_list__isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			WHERE TRUE
			AND isys_obj_type__show_in_tree = 1 " . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_obj__status = " . $this->convert_sql_int($p_status);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= " AND isys_catg_location_list__id = " . $this->convert_sql_id($p_catg_list_id);
        }

        return $this->retrieve($l_sql . ";");
    }

    /**
     * Creates the condition to the object table
     *
     * @param   mixed $p_obj_id
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                return ' AND (isys_catg_location_list__parentid ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                return ' AND (isys_catg_location_list__parentid = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return '';
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'assigned_object' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__SOURCE__OBJECT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned Object'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_location_list__isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                                FROM isys_catg_location_list
                                INNER JOIN isys_obj ON isys_obj__id = isys_catg_location_list__isys_obj__id',
                        'isys_catg_location_list',
                        'isys_catg_location_list__id',
                        'isys_catg_location_list__parentid',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory(['isys_obj__status = ' . C__RECORD_STATUS__NORMAL]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_location_list__parentid']),
                        'isys_catg_location_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__INDEX  => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__VIRTUAL_OBJECT__ASSIGNED_OBJECTS',
                    C__PROPERTY__UI__PARAMS => [
                        'multiselection' => true,
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__IMPORT     => true,
                    C__PROPERTY__PROVIDES__EXPORT     => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => true,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ]
            ])
        ];
    }

    public function rank_records($p_objects, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        $handleLocationChanges = isys_tenantsettings::get('cmdb.chassis.handle-location-changes', false);
        $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);
        $l_dao = new isys_cmdb_dao_location($this->m_db);

        foreach ($p_objects as $l_del) {
            $l_data = $this->get_data($l_del)->__to_array();
            $l_dao->detach($this->get_object_id_by_category_id($l_del, "isys_catg_location_list"));

            $l_dao_relation->delete_relation($l_data["isys_catg_location_list__isys_catg_relation_list__id"]);

            // @see ID-4974
            if ($handleLocationChanges) {
                $chassisDao = isys_cmdb_dao_category_s_chassis::instance($this->m_db);
                $result = $chassisDao->get_slots_by_assiged_object($l_data["isys_obj__id"]);

                while ($row = $result->get_row()) {
                    $chassisDao->relations_remove($row['isys_cats_chassis_list__id'], null, $l_data["isys_obj__id"]);
                }
            }
        }

        $l_dao->save();

        return true;
    }

    /**
     * Sync method.
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  mixed
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        /* @var  $l_dao_location  isys_cmdb_dao_category_g_location */
        $l_dao_location = isys_cmdb_dao_category_g_location::instance($this->m_db);

        $l_parentid = $l_dao_location->get_parent_id_by_object($p_object_id);
        if ($l_parentid === false) {
            // Do not assign childs because the container object does not have a parent
            return true;
        }

        if (isys_import_handler_cmdb::C__CREATE) {
            $l_connected_object = $p_category_data[isys_import_handler_cmdb::C__PROPERTIES]['assigned_object'][C__DATA__VALUE];
            if (is_array($l_connected_object)) {
                $output = $this->attachObjects($p_object_id, $l_connected_object, true);
                return count(array_filter($output)) === count($output);
            }

            $l_sql = "SELECT isys_catg_location_list__id, isys_catg_location_list__parentid
							FROM isys_catg_location_list
							WHERE isys_catg_location_list__isys_obj__id = " . $this->convert_sql_id($l_connected_object) . ";";

            $l_row = $this->retrieve($l_sql)
                ->get_row();

            if (empty($l_row)) {
                $l_dao_location->create($l_connected_object, $p_object_id);
            } else {
                // We only need to save the data, if the object is beeing assigned newly.
                if ($l_row['isys_catg_location_list__parentid'] != $p_object_id) {
                    $l_dao_location->save($l_row["isys_catg_location_list__id"], $l_connected_object, $p_object_id);
                }

                return $l_row['isys_catg_location_list__id'];
            }
        }

        return true;
    }

    /**
     * This method gets called, after "category save" by signal-slot-module.
     *
     * @throws  RuntimeException
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function validate_after_save()
    {
        global $g_comp_database;

        /**
         * @var $l_dao isys_cmdb_dao_category_g_location
         */
        $l_dao = isys_cmdb_dao_category_g_location::instance($g_comp_database);
        $l_obj_id = $_GET[C__CMDB__GET__OBJECT];

        try {
            // Using "$p_dao" instead of "$this", because this gets called by "call_user_func" (which works statically).
            $l_dao->get_location_path($l_obj_id);
        } catch (RuntimeException $e) {
            // The saved parent location produces a recursion - We set the parent to NULL.
            $l_dao->reset_location($l_obj_id);

            // And now we throw the exception further towards the action handler.
            isys_application::instance()->container['notify']->error($e->getMessage());
        }
    }

    /**
     * Gets assigned objects.
     *
     * @param   integer $p_obj_id
     * @param   boolean $p_as_array
     *
     * @return  mixed
     */
    public function get_assigned_objects($p_obj_id, $p_as_array = false)
    {
        $l_sql = 'SELECT *
			FROM isys_catg_location_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_location_list__isys_obj__id
			WHERE isys_catg_location_list__parentid = ' . $this->convert_sql_id($p_obj_id) . ';';

        $l_res = $this->retrieve($l_sql);

        if ($p_as_array === false) {
            return $l_res;
        } else {
            $l_arr = [];

            while ($l_row = $l_res->get_row()) {
                $l_arr[] = $l_row['isys_obj__id'];
            }

            return $l_arr;
        }
    }

    /**
     * @param int   $p_object_id
     * @param array $p_objects
     * @param bool  $withOutput
     *
     * @return array|bool
     * @throws isys_exception_dao
     * @throws isys_exception_dao_cmdb
     * @throws isys_exception_database
     */
    public function attachObjects($p_object_id, array $p_objects, $withOutput = false)
    {
        $l_location_dao = new isys_cmdb_dao_category_g_location($this->m_db);

        // Bugfix: Sometimes "get_assigned_objects" returns no array.
        $l_assigned_objects_plain = (array)$this->get_assigned_objects($p_object_id, true);
        $l_assigned_objects = array_flip($l_assigned_objects_plain);

        $l_auth_obj = isys_auth_cmdb::instance();
        $l_not_allowed_objects = [];

        // logging newly assigned servers
        $array_new_objects = array_diff($p_objects, $l_assigned_objects_plain);
        foreach ($array_new_objects as $objectID) {
            $this->logbookEntryForServer($objectID, self::IS_OBJECT_ADDED);
        }

        $return = [];

        foreach ($p_objects as $l_popup_object) {
            $l_allowed_to_assign = $l_auth_obj->is_allowed_to(isys_auth::EDIT, 'OBJ_ID/' . $l_popup_object);

            // @see ID-5440 / Zendesk #3176 Prevent object relations from beeing deleted, when no right can be found.
            if (isset($l_assigned_objects[$l_popup_object])) {
                unset($l_assigned_objects[$l_popup_object]);
            }

            if ($l_popup_object != $p_object_id && $l_allowed_to_assign) {
                $l_sql = "SELECT isys_catg_location_list__id, isys_catg_location_list__parentid
					FROM isys_catg_location_list
					WHERE isys_catg_location_list__isys_obj__id = " . $this->convert_sql_id($l_popup_object) . ";";

                $l_row = $this->retrieve($l_sql)
                    ->get_row();

                if (!$l_row) {
                    $return[] = $l_location_dao->create($l_popup_object, $p_object_id);
                } else {
                    // We only need to save the data, if the object is beeing assigned newly.
                    if ($l_row['isys_catg_location_list__parentid'] != $p_object_id) {
                        if ($l_location_dao->save($l_row["isys_catg_location_list__id"], $l_popup_object, $p_object_id)) {
                            $return[] = $l_row["isys_catg_location_list__id"];
                        }
                    }
                }
            } else {
                if (!$l_allowed_to_assign) {
                    $l_not_allowed_objects[$l_location_dao->get_obj_type_name_by_obj_id($l_popup_object)] = true;
                } else {
                    isys_component_template_infobox::instance()
                        ->set_message('Error: Attaching own object is prohibitted.', 1, null, null, defined_or_default('C__LOGBOOK__ALERT_LEVEL__3'));
                }
            }
        }

        if (count($l_not_allowed_objects) > 0) {
            $l_message = 'Error: No rights to assign objects from object type (';
            foreach ($l_not_allowed_objects as $l_objtype_name => $l_bool) {
                $l_message .= isys_application::instance()->container->get('language')
                        ->get($l_objtype_name) . ', ';
            }
            $l_message = rtrim($l_message, ', ') . ').';
            isys_component_template_infobox::instance()
                ->set_message($l_message, 1, null, null, defined_or_default('C__LOGBOOK__ALERT_LEVEL__3'));
        }

        if (count($l_assigned_objects) > 0) {
            $handleLocationChanges = isys_tenantsettings::get('cmdb.chassis.handle-location-changes', false);

            // Now we save the objects, which were newly assigned to this object.
            foreach ($l_assigned_objects as $l_delObj => $l_val) {
                $l_sql = "SELECT isys_catg_location_list__id
					FROM isys_catg_location_list
					WHERE isys_catg_location_list__isys_obj__id = " . $this->convert_sql_id($l_delObj) . ";";

                $l_res = $this->retrieve($l_sql);
                if ($l_res->num_rows() > 0) {
                    $l_loc_id = $l_res->get_row_value('isys_catg_location_list__id');

                    $l_location_dao->save($l_loc_id, $l_delObj, null);

                    // @see  ID-4974 When saving the category, we check if the location changes need to be handled.
                    if ($handleLocationChanges) {
                        $chassisDao = isys_cmdb_dao_category_s_chassis::instance($this->m_db);
                        $result = $chassisDao->get_slots_by_assiged_object($l_delObj);

                        while ($row = $result->get_row()) {
                            $chassisDao->relations_remove($row['isys_cats_chassis_list__id'], null, $l_delObj);
                        }
                    }
                }
                // logging removed servers from a rack
                $this->logbookEntryForServer($l_delObj, self::IS_OBJECT_REMOVED);
            }
        }

        return ($withOutput === true ? $return: true);
    }

    /**
     * @param int $objectID
     * @param string $type
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function logbookEntryForServer(int $objectID, int $type)
    {
        $objectData = isys_cmdb_dao::instance($this->m_db)->get_object($objectID, true, 1)->get_row();

        if ($type == self::IS_OBJECT_REMOVED) {
            $message = $objectData['isys_obj__title'] .
                ' (Type "' .
                isys_application::instance()->container->get('language')->get($objectData['isys_obj_type__title']) .
                '"): ' .
                isys_application::instance()->container->get('language')->get('LC__CMDB__CATG__LOCATION__OBJECT_REMOVED');
        } elseif ($type == self::IS_OBJECT_ADDED) {
            $message = $objectData['isys_obj__title'] .
                ' (Type "' .
                isys_application::instance()->container->get('language')->get($objectData['isys_obj_type__title']) .
                '"): ' .
                isys_application::instance()->container->get('language')->get('LC__CMDB__CATG__LOCATION__OBJECT_ADDED');
        }

        isys_component_dao_logbook::instance($this->m_db)
            ->set_entry(
                $message,
                '',
                null,
                defined_or_default('C__LOGBOOK__ALERT_LEVEL__0', 1),
                $objectData['isys_obj__id'],
                $objectData['isys_obj__title'],
                $objectData['isys_obj_type__title'],
                $objectData['isys_obj_type__title']
            );
    }

    /**
     * Constructor.
     *
     * @param   isys_component_database $p_db
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function __construct(isys_component_database $p_db)
    {
        isys_component_signalcollection::get_instance()
            ->connect('mod.cmdb.afterCategoryEntrySave', [$this, 'validate_after_save'])
            ->connect('mod.cmdb.afterCreateCategoryEntry', [$this, 'validate_after_save']);

        return parent::__construct($p_db);
    }
}
