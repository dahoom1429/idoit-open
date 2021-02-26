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
class isys_auth_cmdb_categories extends isys_auth_cmdb
{
    /**
     * Container for singleton instance
     *
     * @var isys_auth_cmdb_categories
     */
    private static $m_instance = null;

    /**
     * Retrieve singleton instance of authorization class
     *
     * @return isys_auth_cmdb_categories
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
     * Protected method for combining "category_in_*" paths.
     *
     * @static
     *
     * @param   array $p_category_paths
     *
     * @return  array
     * @author  Leonard Fischer <lficsher@i-doit.com>
     */
    public static function combine_category_with_parameter(array $p_category_paths)
    {
        // Prepare some variables.
        $l_return = [];
        $l_combination = [];

        // Sort the parameters, so that the foreach will do its job correctly.
        isys_auth::sort_paths_by_rights($p_category_paths);

        foreach ($p_category_paths as $l_key => $l_rights) {
            $l_rights_num = array_sum($l_rights);
            list($l_category, $l_param) = explode('+', $l_key);

            if (!isset($l_combination[$l_param . '#' . $l_rights_num])) {
                $l_combination[$l_param . '#' . $l_rights_num] = [$l_category];
            } else {
                $l_combination[$l_param . '#' . $l_rights_num][] = $l_category;
            }
        }

        if (count($l_combination)) {
            foreach ($l_combination as $l_identifier => $l_categories) {
                list($l_param, $l_rights) = explode('#', $l_identifier);

                $l_return[implode(',', $l_categories) . '+' . $l_param] = isys_helper::split_bitwise($l_rights);
            }
        }

        return $l_return;
    }

    /**
     * Returns an array of allowed categories (as constants) for the given object type - Can also return the
     * "isys_auth::WILDCHAR" or "isys_auth::EMPTY_ID_PARAM".
     *
     * @param   string $p_type
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_allowed_categories_by_obj_type($p_type)
    {
        $l_return = [];

        // Check for inactive auth system
        if (!$this->is_auth_active()) {
            // We only need to return the wildchar
            $l_return[] = '*';
        } else {
            if (isset($this->m_paths['category_in_obj_type']) && is_array($this->m_paths['category_in_obj_type'])) {
                foreach ($this->m_paths['category_in_obj_type'] as $l_param => $l_right) {
                    list($l_category, $l_obj_type) = explode('+', strtoupper($l_param));

                    if ($l_obj_type == $p_type) {
                        $l_return[] = $l_category;
                    }
                }
            }

            if (isset($this->m_paths['category_in_location']) && is_array($this->m_paths['category_in_location'])) {
                foreach ($this->m_paths['category_in_location'] as $l_param => $l_right) {
                    list($l_category, $l_object_id) = explode('+', $l_param);

                    $l_return[] = strtoupper($l_category);
                }
            }

            // Let us merge object type specific category rights with global ones
            if (is_array($this->m_paths['category'])) {
                $l_global_category_rights = array_keys($this->m_paths['category']);

                if (is_array($l_global_category_rights)) {
                    // Capitalize constant
                    array_walk($l_global_category_rights, function (&$p_value) {
                        $p_value = strtoupper($p_value);
                    });

                    $l_return = array_merge($l_return, $l_global_category_rights);
                }
            }
        }

        return $l_return;
    }

    /**
     * Gets all allowed categories.
     *
     * @param   integer $p_object_id
     *
     * @return  array|bool  Normally you'll get an array of allowed categories. If ALL categories are allowed, you'll simply receive boolean TRUE.
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_allowed_categories($p_object_id = null)
    {
        if (!$this->is_auth_active()) {
            // We can return a boolean here. See @return please.
            return true;
        }

        $l_return = ['C__CATG__GLOBAL'];

        $l_cache_obj = isys_caching::factory('auth-' . isys_application::instance()->session->get_user_id());
        $l_cache = $l_cache_obj->get('allowed_categories');

        if ($l_cache === false || (is_array($l_cache) && count($l_cache) == 0)) {
            if (isset($this->m_paths['category']) && is_array($this->m_paths['category'])) {
                if (isset($this->m_paths['category'][isys_auth::WILDCHAR])) {
                    $l_return = true;
                } else {
                    if (!isset($this->m_paths['category'][isys_auth::EMPTY_ID_PARAM])) {
                        foreach (array_keys($this->m_paths['category']) as $l_cat_const) {
                            $l_return[] = strtoupper($l_cat_const);
                        }

                        $l_cache_obj->set('allowed_categories', $l_return)
                            ->save();
                    }
                }
            }

            /**
             * @todo This makes no sense without an provided objectId
             */
            if ($l_return !== true && is_countable($this->m_paths['category_in_obj_type']) && count($this->m_paths['category_in_obj_type']) > 0) {
                $l_categories = array_keys($this->m_paths['category_in_obj_type']);

                foreach ($l_categories as $l_category) {
                    $l_return[] = strtoupper(strstr($l_category, '+', true));
                }
            }

            /**
             * @todo This makes no sense without an provided objectId
             */
            if ($l_return !== true && is_countable($this->m_categories_in_objects) && count($this->m_categories_in_objects) > 0) {
                if (isset($this->m_categories_in_objects[isys_auth::WILDCHAR])) {
                    $l_return = true;
                } else {
                    $l_return = array_keys($this->m_categories_in_objects + array_flip($l_return));
                }
            }

            /**
             * @todo This makes no sense without an provided objectId
             */
            if ($l_return !== true && is_countable($this->m_categories_in_locations) && count($this->m_categories_in_locations) > 0) {
                if (isset($this->m_categories_in_locations[isys_auth::WILDCHAR])) {
                    $l_return = true;
                } else {
                    $l_return = array_keys($this->m_categories_in_locations + array_flip($l_return));
                }
            }

            if ($l_return !== true && $p_object_id !== null && isset($this->m_paths['category_in_own_object'])) {
                $l_dao = isys_cmdb_dao::instance(isys_application::instance()->database);

                $l_owner_query = 'SELECT isys_obj__id 
                    FROM isys_obj 
                    WHERE isys_obj__id = ' . $l_dao->convert_sql_id($p_object_id) . ' 
                    AND ' . isys_auth_cmdb_objects::instance()
                        ->get_owner_condition() . ' 
                    LIMIT 1';

                $l_is_own_object = $l_dao->retrieve($l_owner_query)
                    ->get_row_value('isys_obj__id');

                if ($l_is_own_object && isset ($this->m_paths['category_in_own_object'][self::WILDCHAR])) {
                    $l_return = true;
                } else {
                    $l_return = array_keys($this->m_paths['category_in_own_object'] + array_flip($l_return));
                }
            }

            if (is_array($l_return)) {
                $l_return = array_map('strtoupper', $l_return);
            }
        } else {
            $l_return = $l_cache;
        }

        return $l_return;
    }
}