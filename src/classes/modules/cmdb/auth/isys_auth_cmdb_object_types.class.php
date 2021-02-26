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
class isys_auth_cmdb_object_types extends isys_auth_cmdb
{
    /**
     * Container for singleton instance
     *
     * @var isys_auth_cmdb_object_types
     */
    private static $m_instance = null;

    /**
     * Retrieve singleton instance of authorization class
     *
     * @return isys_auth_cmdb_object_types
     * @author Selcuk Kekec <skekec@i-doit.com>
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
     * Protected method for combining "category" paths.
     *
     * @static
     *
     * @param   array $p_objtype_paths
     *
     * @return  array
     * @author  Leonard Fischer <lficsher@i-doit.com>
     */
    public static function combine_object_types(array &$p_objtype_paths)
    {
        // Prepare some variables.
        $l_return = [];
        $l_keys = [];
        $l_last_rights_num = 0;

        // Sort the parameters, so that the foreach will do its job correctly.
        isys_auth::sort_paths_by_rights($p_objtype_paths);

        foreach ($p_objtype_paths as $l_key => $l_rights) {
            if ($l_key == self::WILDCHAR || $l_key == self::EMPTY_ID_PARAM) {
                $l_return[$l_key] = $l_rights;
                continue;
            }

            $l_rights_num = array_sum($l_rights);

            if ($l_last_rights_num == $l_rights_num) {
                $l_keys[] = $l_key;
            } else {
                if (count($l_keys)) {
                    $l_return[implode(',', $l_keys)] = isys_helper::split_bitwise($l_last_rights_num);
                }

                $l_keys = [$l_key];
            }

            $l_last_rights_num = $l_rights_num;
        }

        if (count($l_keys)) {
            $l_return[implode(',', $l_keys)] = isys_helper::split_bitwise($l_last_rights_num);
        }

        return $l_return;
    }

    /**
     * Gets all allowed object types
     *
     * @return array|bool
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_allowed_objecttypes($p_right = isys_auth::VIEW)
    {
        // Check for inactive auth system
        if (!$this->is_auth_active()) {
            return [];
        }

        $l_allowed_object_types_condition = isys_auth_cmdb_objects::instance()
            ->get_allowed_object_types_condition($p_right);

        if ($l_allowed_object_types_condition) {
            return $this->get_object_types_by_condition($l_allowed_object_types_condition);
        }

        return false;
    }

    /**
     * @param $p_condition
     *
     * @return array
     * @throws isys_exception_database
     */
    public function get_object_types_by_condition($p_condition)
    {
        $l_return = [];

        if ($p_condition) {
            $l_dao = isys_cmdb_dao_object_type::instance(isys_application::instance()->database);

            $l_otypes = $l_dao->retrieve("SELECT isys_obj_type__id FROM isys_obj_type WHERE (TRUE $p_condition);");
            while ($l_row = $l_otypes->get_row(IDOIT_C__DAO_RESULT_TYPE_ROW)) {
                $l_return[$l_row[0]] = $l_row[0];
            }
        }

        return $l_return;
    }

    /**
     * This method gets all allowed object type groups
     *
     * @global isys_component_database $g_comp_database
     * @return array|bool|mixed
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_allowed_objtype_groups()
    {
        // Check for inactive auth system
        if (!$this->is_auth_active()) {
            return true;
        }

        /** @var isys_component_database $g_comp_database */
        global $g_comp_session, $g_comp_database;

        $l_cache_obj = isys_caching::factory('auth-' . $g_comp_session->get_user_id());
        $l_cache = $l_cache_obj->get('allowed_objtype_groups');

        $l_return = [];

        if ($l_cache === false || (is_array($l_cache) && count($l_cache) == 0)) {
            $l_allowed_objtypes = $this->get_allowed_objecttypes();
            $l_sql = 'SELECT DISTINCT(isys_obj_type_group__const) FROM isys_obj_type_group INNER JOIN isys_obj_type ON isys_obj_type__isys_obj_type_group__id = isys_obj_type_group__id ';

            if (is_array($l_allowed_objtypes) && count($l_allowed_objtypes) > 0) {
                $l_sql .= 'WHERE isys_obj_type__id IN (' . implode(',', $l_allowed_objtypes) . ')';
            }

            $l_res = $g_comp_database->query($l_sql);

            if ($g_comp_database->num_rows($l_res) > 0) {
                while ($l_row = $g_comp_database->fetch_array($l_res)) {
                    $l_return[] = $l_row['isys_obj_type_group__const'];
                }
            }

            try {
                $l_cache_obj->set('allowed_objtype_groups', $l_return)
                    ->save();
            } catch (isys_exception_cache $e) {
                isys_notify::warning($e->getMessage());
            }
        } else {
            $l_return = $l_cache;
        }

        return $l_return;
    }

    /**
     * Gets all object types for object type configuration list.
     *
     * @return  array|bool|mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.con>
     */
    public function get_allowed_objecttype_configs()
    {
        // Check for inactive auth system
        if (!$this->is_auth_active()) {
            return true;
        }

        global $g_comp_session;

        $l_cache_obj = isys_caching::factory('auth-' . $g_comp_session->get_user_id());
        $l_cache = $l_cache_obj->get('allowed_objtype_configs');

        $l_return = false;

        if ($l_cache === false || (is_array($l_cache) && count($l_cache) == 0)) {

            $l_wildcard = false;
            // Get object types from object in type rights
            if (isset($this->m_paths['obj_type'])) {
                if (isset($this->m_paths['obj_type'][isys_auth::WILDCHAR])) {
                    $l_wildcard = true;
                    $l_return = true;
                } else {
                    if (!isset($this->m_paths['obj_type'][isys_auth::EMPTY_ID_PARAM])) {
                        $l_return = [];
                        foreach ($this->m_paths['obj_type'] AS $l_key => $l_rights) {
                            $l_key = strtoupper($l_key);
                            if (defined($l_key)) {
                                $l_key_constant = constant($l_key);
                                $l_return[$l_key_constant] = $l_key_constant;
                            }
                        }
                    }
                }
            }

            if (!$l_wildcard) {
                $l_return = (is_countable($l_return) && count($l_return) > 0) ? $l_return : false;
            }

            try {
                $l_cache_obj->set('allowed_objtype_configs', $l_return)
                    ->save();
            } catch (isys_exception_cache $e) {
                isys_notify::warning($e->getMessage());
            }
        } else {
            $l_return = $l_cache;
        }

        return $l_return;
    }

    /**
     * Gets all object type groups for the object type configuration.
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.con>
     */
    public function get_allowed_objecttype_group_configs()
    {
        // Check for inactive auth system
        if (!$this->is_auth_active()) {
            return true;
        }

        global $g_comp_database;

        $l_objecttypes = $this->get_allowed_objecttype_configs();

        if (is_array($l_objecttypes)) {
            return isys_cmdb_dao::instance($g_comp_database)
                ->get_objtype_group_const_by_type_id($l_objecttypes);
        } else {
            if (is_bool($l_objecttypes)) {
                return $l_objecttypes;
            }
        }

        return false;
    }

    /**
     * Checks if object type is allowed.
     *
     * @param   mixed $p_obj_type
     *
     * @return  boolean
     * @throws  isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function is_allowed_in_objecttype($p_obj_type)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        if (!is_numeric($p_obj_type)) {
            if (defined($p_obj_type)) {
                $p_obj_type = constant($p_obj_type);
            } else {
                throw new isys_exception_general('Object type constant does not exist.');
            }
        }

        $l_objtypes = array_merge($this->get_allowed_objecttypes(isys_auth::VIEW), $this->get_allowed_objecttypes(isys_auth::CREATE));

        if (is_array($l_objtypes)) {
            if (count($l_objtypes) > 0) {
                return isset($l_objtypes[$p_obj_type]);
            } else {
                // WILDCARD
                return true;
            }
        } else {
            if ($l_objtypes === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks permission to see the object type
     *
     * @deprecated  Only used in OLD list component
     *
     * @param integer $p_objecttype
     *
     * @throws isys_exception_auth
     */
    public function check_in_allowed_objecttypes($p_objecttype)
    {
        if (!$this->is_allowed_in_objecttype($p_objecttype)) {
            throw new isys_exception_auth(isys_application::instance()->container->get('language')
                ->get('LC__AUTH__EXCEPTION__MISSING_RIGHTS_TO_VIEW_OBJECT_LIST', isys_application::instance()->container->get('language')
                    ->get(isys_factory_dao::get_instance('isys_cmdb_dao', self::$m_dao->get_database_component())
                        ->get_objtype_name_by_id_as_string($p_objecttype))));
        }
    }
}
