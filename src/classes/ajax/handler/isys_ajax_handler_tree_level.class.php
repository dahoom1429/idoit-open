<?php

/**
 * AJAX handler for tree levels.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_tree_level extends isys_ajax_handler
{
    /**
     * @var array
     */
    private $assignedCategoryCache = [];

    /**
     * Initialize tree level handler
     *
     * @global  isys_component_database $g_comp_database
     * @return  array|void
     */
    public function init()
    {
        global $g_comp_database;

        // Retrieve id parameter. Convert -1 request to null (root node).
        if ($this->m_get['id'] == -1) {
            $l_id = null;
        } else {
            $l_id = $this->m_get['id'];
        }

        if ($this->m_get['get_obj_name']) {
            $l_location_popup = new isys_popup_browser_location();
            echo $l_location_popup->format_selection($l_id);

            $this->_die();
        }

        header('Content-Type: application/json');

        // ID-2898 - Only append the auth-condition, if this feature is enabled.
        $l_consider_rights = !!isys_tenantsettings::get('auth.use-in-location-tree', false);

        $l_dao = isys_component_dao_user::instance($g_comp_database);
        $l_dao->save_settings(C__SETTINGS_PAGE__SYSTEM, ['C__CATG__OVERVIEW__DEFAULT_TREETYPE' => $this->m_post['tree_type']]);

        // Check for "$l_id != C__OBJ__ROOT_LOCATION" because we can't authorize the root location itself ;)
        if ($l_consider_rights && $l_id > 0 && $l_id != defined_or_default('C__OBJ__ROOT_LOCATION') && !isys_auth_cmdb::instance()->is_allowed_to(isys_auth::VIEW, 'OBJ_ID/' . $l_id)) {
            if ($this->m_get['return_value']) {
                return [];
            }

            echo '[]';

            $this->_die();
        }

        // The function "isys_glob_get_param()" allows us to get different types by GET parameter (used by "relocate-ci" module).
        switch (isys_glob_get_param('tree_type')) {
            case C__CMDB__VIEW__TREE_LOCATION__LOGICAL_UNITS:
                $l_return = $this->logical($l_id, $l_consider_rights);
                break;
            case C__CMDB__VIEW__TREE_LOCATION__COMBINED:
                $l_return = $this->combined($l_id, $l_consider_rights);
                break;
            default:
            case C__CMDB__VIEW__TREE_LOCATION__LOCATION:
                $l_return = $this->location($l_id, true, $l_consider_rights);
                break;
        }

        if ($this->m_get['return_value']) {
            return $l_return;
        }

        echo isys_format_json::encode($l_return);

        $this->_die();
    }

    /**
     * Filters logical devices from the physical tree in the combined view
     *
     * @param  $p_tree_array
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function filter_logical_devices_from_physical(&$p_tree_array)
    {
        global $g_comp_database;

        $l_dao = isys_cmdb_dao::instance($g_comp_database);

        if (empty($this->assignedCategoryCache) || !is_array($this->assignedCategoryCache)) {
            $result = $l_dao->get_obj_type_by_catg(filter_defined_constants(['C__CATG__ASSIGNED_LOGICAL_UNIT', 'C__CATG__PERSON_ASSIGNED_WORKSTATION']));
            while ($row = $result->get_row()) {
                $this->assignedCategoryCache[$row['isys_obj_type_2_isysgui_catg__isysgui_catg__id']][$row['isys_obj_type_2_isysgui_catg__isys_obj_type__id']] = true;
            }
        }

        foreach ($p_tree_array as $l_key => $l_value) {
            $objTypeIdCurrentObject = $l_dao->get_objTypeID($l_value['id']);
            $objTypeIdParentObject = $l_dao->get_objTypeID($l_value['parentId']);
            if (!defined('C__CATG__ASSIGNED_LOGICAL_UNIT') || !is_array($this->assignedCategoryCache[constant('C__CATG__ASSIGNED_LOGICAL_UNIT')])) {
                continue;
            }

            if (!$this->assignedCategoryCache[constant('C__CATG__ASSIGNED_LOGICAL_UNIT')][$objTypeIdCurrentObject] &&
                !$this->assignedCategoryCache[constant('C__CATG__ASSIGNED_LOGICAL_UNIT')][$objTypeIdParentObject]) {
                $l_logical_data = $l_dao->retrieve('SELECT workstation.isys_catg_logical_unit_list__isys_obj__id__parent AS workstationObject, isys_obj__isys_obj_type__id AS objType
                            FROM isys_catg_logical_unit_list workstation
                            INNER JOIN isys_obj ON isys_obj__id = workstation.isys_catg_logical_unit_list__isys_obj__id__parent
						WHERE workstation.isys_catg_logical_unit_list__isys_obj__id = ' . $l_dao->convert_sql_id($l_value['id']))
                    ->get_row();

                $objTypeId = $l_logical_data['objType'];

                if ($objTypeId) {
                    if ($this->assignedCategoryCache[constant('C__CATG__ASSIGNED_LOGICAL_UNIT')][$objTypeId] === true) {
                        unset($p_tree_array[$l_key]);
                    }
                }
            } elseif ($this->assignedCategoryCache[constant('C__CATG__ASSIGNED_LOGICAL_UNIT')][$objTypeIdCurrentObject] && $l_value['is_physically_assigned']) {
                $query = 'SELECT isys_catg_location_list__id FROM isys_catg_logical_unit_list
                    INNER JOIN isys_catg_location_list ON isys_catg_location_list__isys_obj__id = isys_catg_logical_unit_list__isys_obj__id__parent
                    WHERE isys_catg_logical_unit_list__isys_obj__id = ' . $l_dao->convert_sql_id($l_value['id']);
                $res = $l_dao->retrieve($query);
                if (is_countable($res) && count($res)) {
                    unset($p_tree_array[$l_key]);
                }
            }
        }
    }

    /**
     * Return only logical locations.
     *
     * @param  integer $p_id
     * @param  boolean $p_consider_rights
     *
     * @return array
     */
    private function logical($p_id = -1, $p_consider_rights = false)
    {
        global $g_comp_database, $g_dirs, $g_config;

        $result = [];

        $l_dao = new isys_cmdb_dao_category_g_logical_unit($g_comp_database);
        $l_data = $l_dao->get_data_by_parent($p_id, $p_consider_rights);

        while ($l_row = $l_data->get_row()) {
            $objectType = $l_dao->get_type_by_id($l_row['isys_obj__isys_obj_type__id']);

            if (!empty($objectType['isys_obj_type__icon'])) {
                if (strpos($objectType['isys_obj_type__icon'], '/') !== false) {
                    $l_icon = $g_config['www_dir'] . $objectType['isys_obj_type__icon'];
                } else {
                    $l_icon = $g_dirs['images'] . 'tree/' . $objectType['isys_obj_type__icon'];
                }
            } else {
                $l_icon = $g_dirs['images'] . 'icons/silk/page_white.png';
            }

            if ($p_id == -1 || $p_id == defined_or_default('C__OBJ__ROOT_LOCATION') || empty($l_row['isys_catg_logical_unit_list__isys_obj__id__parent'])) {
                $l_node_root = -1;
            } else {
                $l_node_root = $l_row['isys_catg_logical_unit_list__isys_obj__id__parent'];
            }

            $l_leaf = !(bool)$l_dao->get_data_by_parent($l_row['isys_catg_logical_unit_list__isys_obj__id'])
                ->num_rows();
            $l_hyperlinks = !(isset($this->m_get['no-hyperlinks']) && $this->m_get['no-hyperlinks'] > 0);
            $l_url = $l_hyperlinks ? 'javascript:ObjectSelected(' . $l_row['isys_catg_logical_unit_list__isys_obj__id'] . ', ' . $l_row['isys_obj__isys_obj_type__id'] .
                ', \'' . $l_row ['isys_obj__title'] . '\', \'' . isys_application::instance()->container->get('language')
                    ->get($l_row ['isys_obj_type__title']) . '\', \'g_browser_Link_' . $l_row ['isys_catg_logical_unit_list__isys_obj__id'] .
                '\', this);' : 'javascript:Prototype.emptyFunction;';

            $result[] = [
                'id'                     => $l_row['isys_catg_logical_unit_list__isys_obj__id'],
                'text'                   => $l_row['isys_obj__title'],
                'icon'                   => $l_icon,
                'url'                    => $l_url,
                'parentId'               => $l_node_root,
                'is_leaf'                => $l_leaf,
                'is_logically_assigned'  => true,
                'is_physically_assigned' => false
            ];
        }

        usort($result, function ($a, $b) {
            return strnatcasecmp($a['text'], $b['text']);
        });

        return $result;
    }

    /**
     * Return both, logical and physical locations in a merged view.
     *
     * @param  integer $p_id
     * @param  boolean $p_consider_rights
     *
     * @return array
     */
    private function combined($p_id = -1, $p_consider_rights = false)
    {
        global $g_comp_database;

        $l_dao = new isys_cmdb_dao_category_g_logical_unit($g_comp_database);
        $l_dao_loc = new isys_cmdb_dao_location($g_comp_database);

        $l_return = [];

        if (!$p_id) {
            $l_merged_arr = $this->location($p_id, false, $p_consider_rights);
        } else {
            $l_merged_arr = array_merge($this->location($p_id, false, $p_consider_rights), $this->logical($p_id, $p_consider_rights));
        }

        if (is_countable($l_merged_arr) && count($l_merged_arr) > 0) {
            $this->filter_logical_devices_from_physical($l_merged_arr);
        }

        foreach ($l_merged_arr as $l_value) {
            $l_value['is_leaf'] = (!$l_dao->get_data_by_parent($l_value['id'])
                    ->num_rows() && !$l_dao_loc->get_child_locations($l_value['id'], true, isset($this->m_get['containersOnly']))
                    ->num_rows());

            $l_return[] = $l_value;
        }

        return $l_return;
    }

    /**
     * Return logical locations by its parent.
     *
     * @param  integer $p_id
     * @param  boolean $p_leaf_checking
     * @param  boolean $p_consider_rights
     *
     * @return array
     */
    private function location($p_id = -1, $p_leaf_checking = true, $p_consider_rights = false)
    {
        global $g_dirs, $g_comp_database, $g_config;

        // Determine, whether to show the root location.
        $l_hide_root = isset($this->m_get['hide_root']);

        $l_result = [];

        $l_dao = new isys_cmdb_dao_location($g_comp_database);

        // ID-3236 - Instead of simply displaying children of the root-location, display objects that we are allowed to see.
        if ($p_id === null && $p_consider_rights) {
            $l_res = isys_auth_cmdb_objects::instance()
                ->get_allowed_locations(true, $l_hide_root);
        } else {
            $l_res = $l_dao->get_child_locations($p_id, $l_hide_root, $this->m_get['containersOnly'], $p_consider_rights);
        }

        if ($l_res !== null && is_countable($l_res) && count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                // Decide whether to show node.
                // 1. Condition: If the object ID is equal to the current, the node is not added. So we avoid loopbacks in the location tree.
                if ($l_row['isys_catg_location_list__isys_obj__id'] != $this->m_get['currentObjID']) {
                    if (!empty($l_row['isys_obj_type__icon'])) {
                        if (strpos($l_row['isys_obj_type__icon'], '/') !== false) {
                            $l_icon = $g_config['www_dir'] . $l_row['isys_obj_type__icon'];
                        } else {
                            $l_icon = $g_dirs['images'] . 'tree/' . $l_row['isys_obj_type__icon'];
                        }
                    } else {
                        $l_icon = $g_dirs['images'] . 'icons/silk/page_white.png';
                    }

                    if ($l_hide_root && $l_row['isys_catg_location_list__parentid'] == defined_or_default('C__OBJ__ROOT_LOCATION')) {
                        $l_node_root = -1;
                    } else {
                        $l_node_root = $l_row['isys_catg_location_list__parentid'];
                    }

                    // Set the default callback action.
                    $l_selectCallback = 'ObjectSelected';

                    if ($this->m_get['selectCallback']) {
                        $l_selectCallback = $this->m_get['selectCallback'];
                    }

                    $l_hyperlinks = !(isset($this->m_get['no-hyperlinks']) && $this->m_get['no-hyperlinks'] > 0);
                    $l_url = $l_hyperlinks ? 'javascript:' . $l_selectCallback . '(' . $l_row['isys_catg_location_list__isys_obj__id'] . ', ' .
                        $l_row['isys_obj__isys_obj_type__id'] . ', \'' . addslashes($l_row['isys_obj__title']) . '\', \'' .
                        isys_application::instance()->container->get('language')
                            ->get($l_row['isys_obj_type__title']) . '\', \'g_browser_Link_' . $l_row['isys_catg_location_list__isys_obj__id'] .
                        '\', this);' : 'javascript:Prototype.emptyFunction;';

                    $l_result[] = [
                        'id'                     => $l_row ['isys_catg_location_list__isys_obj__id'],
                        'text'                   => $l_row['isys_obj__title'],
                        'icon'                   => $l_icon,
                        'url'                    => $l_url,
                        'parentId'               => $l_node_root,
                        'is_leaf'                => ($p_leaf_checking ? ($l_row['ChildrenCount'] == 0) : false),
                        'is_logically_assigned'  => false,
                        'is_physically_assigned' => true
                    ];
                }
            }
        }

        usort($l_result, function ($a, $b) {
            return strnatcasecmp($a['text'], $b['text']);
        });

        return $l_result;
    }
}
