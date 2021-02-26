<?php

/**
 * i-doit
 *
 * Auth: Class for CMDB module authorization rules.
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_auth_cmdb_objects extends isys_auth_cmdb
{
    /**
     * Container for singleton instance
     *
     * @var isys_auth_cmdb_objects
     */
    private static $m_instance = null;

    /**
     * @var string
     */
    private $m_allowed_object_types_condition = [];

    /**
     * @var string
     */
    private $m_allowed_object_types = [];

    /**
     * @var string
     */
    private $m_allowed_objects_condition = [];

    /**
     * @param       $p_person_id
     * @param null  $p_module_id
     * @param array $p_paths
     *
     * @return isys_cache_keyvalue
     */
    public static function invalidate_cache($p_person_id, $p_module_id = null, $p_paths = [])
    {
        return isys_cache::keyvalue()
            ->ns($p_person_id)
            ->delete('auth.condition.allowed_objects')
            ->delete('auth.condition.allowed_object_types');
    }

    /**
     * Return ids of allowed object types
     *
     * @return string
     */
    public function get_allowed_object_types($p_right)
    {
        if (!isset($this->m_allowed_object_types[$p_right])) {
            $this->parse($p_right);
        }

        return $this->m_allowed_object_types[$p_right];
    }

    /**
     * Checks wheather p_right is set for p_object_type
     *
     * @return string
     */
    public function is_object_type_allowed($p_object_type, $p_right)
    {
        if (!isset($this->m_allowed_object_types[$p_right])) {
            $this->parse($p_right);
        }

        return isset($this->m_allowed_object_types[$p_right][$p_object_type]);
    }

    /**
     * Retrieve singleton instance of authorization class.
     *
     * @return  isys_auth_cmdb_objects
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public static function instance()
    {
        // If the DAO has not been loaded yet, we initialize it now.
        if (self::$m_dao === null) {
            global $g_comp_database;

            self::$m_dao = new isys_auth_dao($g_comp_database);
        }

        if (self::$m_instance === null) {
            self::$m_instance = new self;
        }

        return self::$m_instance;
    }

    /**
     * Return SQL condition to identify all object types where the user has access rights to
     *
     * @return null|string
     */
    public function get_allowed_object_types_condition($p_right = isys_auth::VIEW)
    {
        if (!isset($this->m_allowed_object_types_condition[$p_right])) {
            $this->parse($p_right);
        }

        return $this->m_allowed_object_types_condition[$p_right];
    }

    /**
     * Return SQL condition to isolate objects the user is allowed to see
     *
     * @author Dennis Stücken <dstuecken@i-doit.de>
     * @return string
     */
    public function get_allowed_objects_condition($p_right = isys_auth::VIEW)
    {
        if (!isset($this->m_allowed_objects_condition[$p_right])) {
            $this->parse($p_right);
        }

        return $this->m_allowed_objects_condition[$p_right];
    }

    /**
     * Return all allowed objects as an array. Note that this function does not include objects.
     *
     * @deprecated  DON'T USE SINCE THIS FUNCTION COULD BE VERY SLOW AND EXCEEDS THE MAX_ALLOWED_PACKETS RESTRICTION
     *
     * @param   integer $p_type_filter
     *
     * @return  array
     * @throws  isys_exception_database
     * @author      Dennis Stücken <dstuecken@i-doit.de>
     */
    public function get_allowed_objects($p_type_filter = null)
    {
        // Check for inactive auth .
        if (!$this->is_auth_active()) {
            return true;
        }

        $l_return = [];

        $l_cache_obj = isys_caching::factory('auth-' . isys_application::instance()->container->get('session')->get_user_id());
        $l_cache = $l_cache_obj->get('allowed_objects');

        if ($l_cache === false) {
            if ($p_type_filter) {
                $l_type_condition = ' AND (isys_obj__isys_obj_type__id = ' . $p_type_filter . ')';
            } else {
                $l_type_condition = '';
            }

            $l_objects = isys_cmdb_dao::factory(isys_application::instance()->database)
                ->retrieve('SELECT isys_obj__id AS id FROM isys_obj WHERE TRUE ' . $this->get_allowed_objects_condition() . $l_type_condition . ' ORDER BY isys_obj__id ASC;');

            while ($l_row = $l_objects->get_row()) {
                $l_return[$l_row['id']] = $l_row['id'];
            }

            try {
                $l_cache_obj->set('allowed_objects', $l_return)
                    ->save();
            } catch (isys_exception_filesystem $e) {
                isys_notify::warning($e->getMessage());
            }
        }

        return $l_return;
    }

    /**
     * Condition to check if object is mine
     *
     * @return string
     */
    public function get_owner_condition()
    {
        $objId = (int) isys_application::instance()->container->get('session')->get_user_id();

        if ($objId == defined_or_default('C__OBJ__PERSON_API_SYSTEM')) {
            return 'TRUE';
        }

        return 'isys_obj__owner_id = ' . $objId;
    }

    /**
     * Prepare SQL conditions.
     *
     * @param int $p_right
     *
     * @return boolean
     */
    private function parse($p_right = isys_auth::VIEW)
    {
        $l_allowed_object_types = $l_allowed_objects = $l_conditions = [];

        // Check for inactive auth system.
        if (!$this->is_auth_active()) {
            return true;
        }

        // Get Caching instance in a user namespace.
        $l_cache_obj = isys_cache::keyvalue()->ns(isys_application::instance()->container->get('session')->get_user_id() . '-' . $p_right);

        $this->m_allowed_objects_condition[$p_right] = $l_cache_obj->get("auth.condition.allowed_objects");
        $this->m_allowed_object_types_condition[$p_right] = $l_cache_obj->get('auth.condition.allowed_object_types');

        $l_object_type_wildcard = false;

        // Start evaluating if cache is not set
        if (!$this->m_allowed_objects_condition[$p_right] && !$this->m_allowed_object_types_condition[$p_right]) {
            $this->m_allowed_objects_condition[$p_right] = ' AND (' . $this->get_owner_condition() . ')';
            $this->m_allowed_object_types_condition[$p_right] = ' AND FALSE';

            if (isset($this->m_paths['obj_in_type'])) {
                // Wildcard for all objects is greater than any objtype condition, so we don't need to handle any objtype conditions when the user has access to all objects anyway.
                if (!isset($this->m_paths['obj_in_type'][isys_auth::WILDCHAR]) || !in_array($p_right, $this->m_paths['obj_in_type'][isys_auth::WILDCHAR])) {
                    if (!isset($this->m_paths['obj_in_type'][isys_auth::EMPTY_ID_PARAM]) || !in_array($p_right, $this->m_paths['obj_in_type'][isys_auth::EMPTY_ID_PARAM])) {
                        foreach ($this->m_paths['obj_in_type'] as $l_key => $l_rights) {
                            if (array_sum($l_rights) & $p_right) {
                                $l_key = strtoupper($l_key);

                                if (defined($l_key)) {
                                    $l_allowed_object_types[constant($l_key)] = constant($l_key);
                                }
                            }
                        }

                        if (count($l_allowed_object_types) > 0) {
                            $l_conditions['objtype'] = '(isys_obj__id IN (SELECT authSelect.isys_obj__id FROM isys_obj authSelect WHERE authSelect.isys_obj__isys_obj_type__id IN (' .
                                implode(',', $l_allowed_object_types) . ')))';
                        }
                    }
                } else {
                    if (in_array($p_right, $this->m_paths['obj_in_type'][isys_auth::WILDCHAR])) {
                        $l_object_type_wildcard = true;
                        $this->m_allowed_object_types_condition[$p_right] = ' AND TRUE';
                    }
                }
            }

            // Get object types from object id rights.
            if (isset($this->m_paths['obj_id'])) {
                if (!isset($this->m_paths['obj_id'][isys_auth::WILDCHAR])) {
                    if (is_array($this->m_paths['obj_id']) && count($this->m_paths['obj_id']) > 0) {
                        $l_tmp = $this->m_paths['obj_id'];
                        unset($l_tmp[isys_auth::EMPTY_ID_PARAM], $l_tmp[isys_auth::WILDCHAR]);
                        $l_allowed_objects = array_keys($l_tmp);
                        unset($l_tmp);
                    }
                } else {
                    if (in_array($p_right, $this->m_paths['obj_id'][isys_auth::WILDCHAR])) {
                        $this->m_allowed_objects_condition[$p_right] = ' AND TRUE';
                    }
                }
            }

            // Search for objects based on a location path.
            if (!$l_object_type_wildcard && isset($this->m_paths['location']) && !isset($this->m_paths['location'][isys_auth::WILDCHAR]) &&
                !isset($this->m_paths['location'][isys_auth::EMPTY_ID_PARAM])) {
                // The given ID could not be found directly, now we check the location path.

                $l_dao_location = isys_cmdb_dao_location::factory(isys_application::instance()->database);

                foreach ($this->m_paths['location'] as $l_location_id => $l_rights) {
                    if (array_sum($l_rights) & $p_right) {
                        // Get child locations of the location auth-paths.
                        $l_child_locations = $l_dao_location->get_mptt()
                            ->get_children($l_location_id, null);

                        if (is_object($l_child_locations)) {
                            while ($l_row = $l_child_locations->get_row()) {
                                // Add right for this specific object.
                                $l_allowed_objects[] = $l_row['isys_catg_location_list__isys_obj__id'];

                                // Add right for object type, which was gained in consequence of the specific object right.
                                if (!isset($l_allowed_object_types[$l_row['isys_obj_type__id']])) {
                                    $l_allowed_object_types[$l_row['isys_obj_type__id']] = $l_row['isys_obj_type__id'];
                                }
                            }
                        }
                    }
                }
            }

            // Search for objects based on a logical location path.
            if (!$l_object_type_wildcard && isset($this->m_paths['logical_location']) && !isset($this->m_paths['logical_location'][isys_auth::WILDCHAR]) &&
                !isset($this->m_paths['logical_location'][isys_auth::EMPTY_ID_PARAM])) {
                // The given ID could not be found directly, now we check the logical location path.
                $l_dao_logical_location = isys_cmdb_dao_category_g_assigned_logical_unit::factory(isys_application::instance()->database);

                foreach ($this->m_paths['logical_location'] as $l_location_id => $l_rights) {
                    if (array_sum($l_rights) & $p_right) {
                        // Get child locations of the location auth-paths.
                        $l_child_locations = $l_dao_logical_location->get_children($l_location_id, true);

                        foreach ($l_child_locations as $l_object) {
                            // Add right for this specific object.
                            $l_allowed_objects[] = $l_object['isys_obj__id'];

                            // Add right for object type, which was gained in consequence of the specific object right.
                            if (!isset($l_allowed_object_types[$l_object['isys_obj_type__id']])) {
                                $l_allowed_object_types[$l_object['isys_obj_type__id']] = $l_object['isys_obj_type__id'];
                            }
                        }
                    }
                }
            }

            // Addup all collected object ids to l_conditions:objids
            if (count($l_allowed_objects) && !$l_object_type_wildcard) {
                $l_conditions['objids'] = 'isys_obj__id IN (' . implode(',', $l_allowed_objects) . ')';
            } else {
                if ($l_object_type_wildcard) {
                    $this->m_allowed_objects_condition[$p_right] = '';
                }
            }

            // Build condition.
            if (count($l_conditions)) {
                // Allow view rights for own objects
                if ($p_right == isys_auth::VIEW) {
                    $l_conditions[] = '(' . $this->get_owner_condition() . ')';
                }

                $this->m_allowed_objects_condition[$p_right] = ' AND (' . implode(' OR ', $l_conditions) . ')';
            }

            $l_allowed_object_types = array_filter($l_allowed_object_types);

            if (count($l_allowed_object_types)) {
                $this->m_allowed_object_types_condition[$p_right] = ' AND (isys_obj_type__id IN (' . implode(',', $l_allowed_object_types) . ')';

                if (isset($l_conditions['objids'])) {
                    $this->m_allowed_object_types_condition[$p_right] .= ' OR isys_obj_type__id IN (SELECT isys_obj__isys_obj_type__id FROM isys_obj WHERE ' .
                        $l_conditions['objids'] . ')';
                }

                $this->m_allowed_object_types_condition[$p_right] .= ')';

                // Remember allowed object type ids
                $this->m_allowed_object_types[$p_right] = $l_allowed_object_types;
            } else {
                if ($this->m_allowed_objects_condition[$p_right] != '') {
                    $this->m_allowed_object_types_condition[$p_right] = ' AND (isys_obj_type__id IN (SELECT DISTINCT(isys_obj__isys_obj_type__id) FROM isys_obj WHERE TRUE ' .
                        $this->m_allowed_objects_condition[$p_right] . '))';
                }
            }

            try {
                $l_cache_obj->set('auth.condition.allowed_objects', $this->m_allowed_objects_condition[$p_right])
                    ->set('auth.condition.allowed_object_types', $this->m_allowed_object_types_condition[$p_right]);
            } catch (isys_exception_filesystem $e) {
                isys_notify::warning($e->getMessage());
            }
        }

        return true;
    }

    /**
     * Retrieve all allowed objects as "first level" (will be used, if the user has no rights on "all" objects).
     *
     * @param  boolean $p_prevent_cascades
     *
     * @param bool     $hideRoot
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_allowed_locations($p_prevent_cascades = true, $hideRoot = true)
    {
        $l_result = [];
        $l_dao = isys_cmdb_dao_category_g_location::instance(isys_application::instance()->database);
        $l_locations = array_filter(array_keys($this->m_paths['location']), 'is_numeric');

        if (isset($this->m_paths['location'][isys_auth::WILDCHAR])) {
            $l_sql = 'SELECT isys_catg_location_list__isys_obj__id
                    FROM isys_catg_location_list
                    WHERE isys_catg_location_list__parentid = ' . $l_dao->convert_sql_id(defined_or_default('C__OBJ__ROOT_LOCATION')) . ';';

            if (!$hideRoot && defined('C__OBJ__ROOT_LOCATION')) {
                $l_result[] = C__OBJ__ROOT_LOCATION;
            } else {
                $l_res = $l_dao->retrieve($l_sql);

                while ($l_row = $l_res->get_row()) {
                    $l_result[] = $l_row['isys_catg_location_list__isys_obj__id'];
                }
            }
        } else {
            if ($p_prevent_cascades) {
                $l_skip = [];

                foreach ($l_locations as $l_location) {
                    if (in_array($l_location, $l_skip)) {
                        continue;
                    }

                    // Retrieve all objects, that
                    $l_sql = 'SELECT isys_catg_location_list__isys_obj__id
                    FROM isys_catg_location_list
                    WHERE isys_catg_location_list__isys_obj__id ' . $l_dao->prepare_in_condition($l_locations) . '
                    AND isys_catg_location_list__lft > (SELECT isys_catg_location_list__lft FROM isys_catg_location_list WHERE isys_catg_location_list__isys_obj__id = ' .
                        $l_dao->convert_sql_id($l_location) . ')
                    AND isys_catg_location_list__rgt < (SELECT isys_catg_location_list__rgt FROM isys_catg_location_list WHERE isys_catg_location_list__isys_obj__id = ' .
                        $l_dao->convert_sql_id($l_location) . ');';

                    $l_result[] = $l_location;
                    $l_res = $l_dao->retrieve($l_sql);

                    while ($l_row = $l_res->get_row()) {
                        $l_skip[] = $l_row['isys_catg_location_list__isys_obj__id'];
                    }
                }
            }
        }

        if (count($l_result)) {
            $l_sql = 'SELECT
                (SELECT COUNT(child.isys_catg_location_list__id) FROM isys_catg_location_list child WHERE child.isys_catg_location_list__parentid = parentObject.isys_obj__id) AS ChildrenCount,
                parent.*,
                parentObject.*,
                parentType.*
                FROM isys_catg_location_list parent
                INNER JOIN isys_obj parentObject ON parent.isys_catg_location_list__isys_obj__id = parentObject.isys_obj__id
                INNER JOIN isys_obj_type parentType ON parentObject.isys_obj__isys_obj_type__id = parentType.isys_obj_type__id
                WHERE parent.isys_catg_location_list__isys_obj__id ' . $l_dao->prepare_in_condition($l_result) . ';';

            return $l_dao->retrieve($l_sql);
        }

        return null;
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
}
