<?php

/**
 * i-doit
 *
 * Auth: DAO class for module authorization.
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_auth_dao extends isys_component_dao
{
    /**
     * Cached group paths by person id
     *
     * @var array
     */
    protected $m_group_paths = null;

    /**
     * Method for retrieving authorization paths.
     *
     * @param   mixed   $p_obj_id May be an "person" or "group" ID or an array of IDs.
     * @param   integer $p_module
     * @param   string  $p_condition
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_paths($p_obj_id = null, $p_module = null, $p_condition = '', $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = 'SELECT * FROM isys_auth WHERE TRUE ' . $p_condition;

        if ($p_obj_id !== null) {
            if (is_array($p_obj_id) && count($p_obj_id) > 0) {
                $l_sql .= ' AND isys_auth__isys_obj__id IN (' . implode(', ', array_map('intval', $p_obj_id)) . ') ';
            } elseif (is_numeric($p_obj_id)) {
                $l_sql .= ' AND isys_auth__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
            }
        }

        if ($p_module !== null) {
            $l_sql .= ' AND isys_auth__isys_module__id = ' . $this->convert_sql_id($p_module);
        }

        if ($p_status !== null) {
            $l_sql .= ' AND isys_auth__status = ' . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for retrieving the paths of all groups, that are assigned to a person.
     *
     * @param   integer $p_person_id
     * @param   integer $p_module
     * @param   string  $p_condition
     *
     * @return  mixed  May be an instance of "isys_component_dao_result" on success, boolean "false" if none were found.
     */
    public function get_group_paths_by_person($p_person_id, $p_module = null, $p_condition = '')
    {
        if (!$this->m_group_paths) {
            $this->m_group_paths = [];

            $l_group_dao = new isys_cmdb_dao_category_s_person_assigned_groups($this->m_db);
            $l_res = $l_group_dao->get_data(null, $p_person_id);

            if (is_countable($l_res) && count($l_res) > 0) {
                while ($l_row = $l_res->get_row()) {
                    $this->m_group_paths[] = $l_row['isys_person_2_group__isys_obj__id__group'];
                }
            }
        }

        if (is_countable($this->m_group_paths) && count($this->m_group_paths)) {
            return $this->get_paths($this->m_group_paths, $p_module, $p_condition);
        }

        return false;
    }

    /**
     * Build the paths, by the given DAO result.
     *
     * @param   isys_component_dao_result $p_res
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function build_paths_by_result(isys_component_dao_result $p_res)
    {
        $l_paths = [];

        while ($l_row = $p_res->get_row()) {
            $this->build_path($l_paths, $l_row);
        }

        return $l_paths;
    }

    /**
     * Build the paths, by the given array.
     *
     * @param   array $p_array
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function build_paths_by_array(array $p_array)
    {
        $l_paths = [];

        foreach ($p_array as $l_item) {
            $this->build_path($l_paths, $l_item);
        }

        return $l_paths;
    }

    /**
     * Auth-Path-Mother-Method: Builds a path by the given result-row.
     * Don't do this on your own - PLEASE only use this method!
     *
     * @param   array $p_paths Has to be used via reference.
     * @param   array $p_row
     *
     * @return  isys_auth_dao
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function build_path(array &$p_paths, array $p_row)
    {
        list($l_method, $l_id) = explode('/', strtolower($p_row['isys_auth__path']));

        if (empty($l_id)) {
            $l_id = isys_auth::EMPTY_ID_PARAM;
        }

        $p_row['isys_auth__type'] = (int)$p_row['isys_auth__type'];

        // Add ALL rights, if the "supervisor" right is set.
        if ($p_row['isys_auth__type'] & isys_auth::SUPERVISOR) {
            $p_row['isys_auth__type'] = (isys_auth::SUPERVISOR * 2) - 1;
        }

        if (is_array($p_paths[$l_method][$l_id])) {
            // We need to merge the rights, instead of "overwriting" them (this can lead to wrong rights).
            $p_paths[$l_method][$l_id] = array_merge($p_paths[$l_method][$l_id], isys_helper::split_bitwise($p_row['isys_auth__type']));
        } else {
            $p_paths[$l_method][$l_id] = isys_helper::split_bitwise($p_row['isys_auth__type']);
        }

        $p_paths[$l_method][$l_id] = array_unique($p_paths[$l_method][$l_id]);

        return $this;
    }

    /**
     * Method for removing all paths by a given object- or module-ID.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_module_id
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    public function remove_all_paths($p_obj_id = null, $p_module_id = null)
    {
        $l_sql = 'DELETE FROM isys_auth WHERE TRUE';

        if ($p_obj_id !== null) {
            $l_sql .= ' AND isys_auth__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        }

        if ($p_module_id !== null) {
            $l_sql .= ' AND isys_auth__isys_module__id = ' . $this->convert_sql_id($p_module_id);
        }

        if ($this->update($l_sql) && $this->apply_update()) {
            if (empty($p_obj_id)) {
                $l_person_res = $this->get_all_persons();
                while ($l_row = $l_person_res->get_row()) {
                    isys_component_signalcollection::get_instance()
                        ->emit('mod.auth.afterRemoveAllRights', $l_row['isys_obj__id'], $p_module_id);
                }
            } else {
                isys_component_signalcollection::get_instance()
                    ->emit('mod.auth.afterRemoveAllRights', $p_obj_id, $p_module_id);
            }

            return true;
        }

        return false;
    }

    /**
     * Method for saving several authorization-pahts.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_module_id
     * @param   array   $p_path_data
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    public function create_paths($p_obj_id, $p_module_id, array $p_path_data = [])
    {
        if (count($p_path_data) === 0) {
            return true;
        }

        $l_inserts = [];

        foreach ($p_path_data as $l_method => $l_params) {
            foreach ($l_params as $l_param => $l_rights) {
                $l_rights_bitwise = array_sum(array_unique($l_rights));

                if ($l_param != 0 || $l_param != -1 || $l_param == isys_auth::WILDCHAR) {
                    $l_path = strtoupper($l_method);

                    if (!empty($l_param)) {
                        $l_path .= '/' . strtoupper($l_param);
                    }

                    $l_inserts[] = '(' . $this->convert_sql_id($p_obj_id) . ',  ' . $this->convert_sql_int($l_rights_bitwise) . ',  ' . $this->convert_sql_id($p_module_id) .
                        ',  ' . $this->convert_sql_text($l_path) . ', ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ')';

                    if ($l_param == isys_auth::WILDCHAR) {
                        // Create paths without parameter for every "XYZ/*" occurance.
                        $l_inserts[] = '(' . $this->convert_sql_id($p_obj_id) . ',  ' . $this->convert_sql_int($l_rights_bitwise) . ',  ' .
                            $this->convert_sql_id($p_module_id) . ',  ' . $this->convert_sql_text(strtoupper($l_method)) . ', ' .
                            $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ')';
                    }
                }
            }
        }

        if (count($l_inserts) > 0) {
            $l_sql = 'INSERT INTO isys_auth (isys_auth__isys_obj__id, isys_auth__type, isys_auth__isys_module__id, isys_auth__path, isys_auth__status) VALUES ' .
                implode(', ', $l_inserts) . ';';

            if ($this->update($l_sql) && $this->apply_update()) {
                isys_component_signalcollection::get_instance()
                    ->emit('mod.auth.afterUpdateRights', $p_obj_id, $p_module_id, $p_path_data, 'create');

                return true;
            }
        }

        return false;
    }

    /**
     * Retrieves all persons from the right system.
     *
     * @return  isys_component_dao_result
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_all_persons()
    {
        $l_sql = 'SELECT DISTINCT(isys_obj__id) FROM isys_auth
			INNER JOIN isys_obj ON isys_auth__isys_obj__id = isys_obj__id
			WHERE isys_obj__isys_obj_type__id = ' . $this->convert_sql_id(defined_or_default('C__OBJTYPE__PERSON')) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Duplicate rights.
     *
     * @param   integer $p_from_object_id
     * @param   integer $p_to_object_id
     *
     * @return  boolean
     * @throws  isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function duplicate($p_from_object_id, $p_to_object_id)
    {
        $sqlGetModules = "SELECT isys_module__id from isys_module;";
        $l_res = $this->get_database_component()->query($sqlGetModules);
        $l_modulesIds = [];
        while ($l_row = $l_res->fetch_assoc()) {
            $l_modulesIds[] = (int)$l_row['isys_module__id'];
        }

        try {
            $l_res = $this->get_paths($p_from_object_id, null, null, C__RECORD_STATUS__NORMAL);

            if ($l_res->num_rows() > 0) {
                $l_values = [];
                $l_insert = 'INSERT INTO isys_auth (isys_auth__isys_obj__id, isys_auth__type, isys_auth__isys_module__id, isys_auth__path, isys_auth__status) VALUES ';

                $l_paths = [];
                while ($l_row = $l_res->get_row()) {
                    $l_paths[] = $l_row;
                    $l_moduleId = (int)$l_row['isys_auth__isys_module__id'];
                    if (!in_array($l_moduleId, $l_modulesIds)) {
                        continue;
                    }

                    $l_values[] = '(' . $this->convert_sql_id($p_to_object_id) . ', ' . $this->convert_sql_int($l_row['isys_auth__type']) . ', ' .
                        $this->convert_sql_int($l_moduleId) . ', ' . $this->convert_sql_text($l_row['isys_auth__path']) . ', ' .
                        $this->convert_sql_int($l_row['isys_auth__status']) . ')';
                }

                if ($this->update($l_insert . implode(',', $l_values) . ';') && $this->apply_update()) {
                    isys_component_signalcollection::get_instance()
                        ->emit('mod.auth.afterDuplicateRights', $p_from_object_id, $p_to_object_id, $l_paths);
                    unset($l_paths);

                    return true;
                }
            } else {
                // Nothing to insert
                return true;
            }
        } catch (isys_exception_general $e) {
            throw new isys_exception_general($e->getMessage());
        }

        return false;
    }
}
