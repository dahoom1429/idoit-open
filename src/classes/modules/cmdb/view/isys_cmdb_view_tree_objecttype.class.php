<?php

/**
 * CMDB Tree view for object types
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_view_tree_objecttype extends isys_cmdb_view_tree
{
    /**
     * Returns the view mode ID
     *
     * @return integer
     */
    public function get_id()
    {
        return C__CMDB__VIEW__TREE_OBJECTTYPE;
    }

    /**
     *
     * @param  array &$l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        parent::get_mandatory_parameters($l_gets);
    }

    /**
     * @return string
     */
    public function get_name()
    {
        return "Objekttypbaum";
    }

    /**
     *
     * @param  array &$l_gets
     */
    public function get_optional_parameters(&$l_gets)
    {
        parent::get_optional_parameters($l_gets);

        $l_gets[C__CMDB__GET__OBJECTGROUP] = true;
    }

    /**
     * Method for building the object type tree.
     */
    public function tree_build()
    {
        global $g_config, $g_dirs;

        $language = isys_application::instance()->container->get('language');
        $displayCounter = (bool)isys_tenantsettings::get('cmdb.gui.display-object-type-counter', 1);

        $l_gets = $this->get_module_request()->get_gets();
        $l_dao = $this->get_dao_cmdb();
        $l_tpl = $this->get_module_request()->get_template();

        $this->remove_ajax_parameters($l_gets);

        // Set default object group, if unset.
        if (isset($l_gets[C__CMDB__GET__OBJECTTYPE])) {
            $l_gets[C__CMDB__GET__OBJECTGROUP] = $l_dao->retrieve('SELECT isys_obj_type__isys_obj_type_group__id FROM isys_obj_type WHERE isys_obj_type__id = ' .
                $l_dao->convert_sql_id($l_gets[C__CMDB__GET__OBJECTTYPE]) . ';')
                ->get_row_value('isys_obj_type__isys_obj_type_group__id');
        } elseif (!isset($l_gets[C__CMDB__GET__OBJECTGROUP])) {
            $l_gets[C__CMDB__GET__OBJECTGROUP] = $l_dao->retrieve('SELECT isys_obj_type_group__id FROM isys_obj_type_group WHERE isys_obj_type_group__status = ' .
                $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' ORDER BY isys_obj_type_group__sort ASC LIMIT 0,1;')
                ->get_row_value('isys_obj_type_group__id');
        }

        // Determines types for the specified object type group.
        $l_typeres = $l_dao->objtype_get_by_objgroup_id($l_gets[C__CMDB__GET__OBJECTGROUP], true, C__RECORD_STATUS__NORMAL, $displayCounter);

        /**
         * This is specifically needed for the new CREATE right for object types.
         * We're initially receiving all allowed object types, and then attach them to the tree, if the user does not have any objects in them.
         * That will allow creating an object type.
         */
        $l_object_type_auth = isys_auth_cmdb_object_types::instance();
        $l_allowed_object_types = $l_object_type_auth->get_allowed_objecttypes(isys_auth::CREATE);

        // Add root node.
        $l_rootgets = $l_gets;
        $l_rootgets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__LIST_OBJECTTYPE;

        if ($l_typeres) {
            $l_groupres = $l_dao->objgroup_get_by_id($l_gets[C__CMDB__GET__OBJECTGROUP]);

            if ($l_groupres && $l_groupres->num_rows() > 0) {
                $l_groupdata = $l_groupres->get_row();
                $l_roottitle = $language->get($l_groupdata["isys_obj_type_group__title"]);
            } else {
                $l_roottitle = $language->get('LC__CMDB__OBJTYPE');
            }

            $l_root_link = isys_glob_build_ajax_url(C__FUNC__AJAX__CONTENT_BY_OBJECT_GROUP, $l_rootgets);

            $l_root = $this->m_tree->add_node(0, C__CMDB__TREE_NODE__PARENT, $l_roottitle, $l_root_link, '', $g_dirs['images'] . 'icons/silk/application_view_icons.png');

            $l_objtypeid = $l_gets[C__CMDB__GET__OBJECTTYPE];

            // We want an object list and a type tree.
            $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__LIST_OBJECT;
            $l_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECTTYPE;

            $l_type_data_arr = [];

            while ($l_typedata = $l_typeres->get_row()) {
                // If the user is allowed to see this object types because he has objects in it, remove it from the allowed object types array, so it
                // does not get added to the tree twice.
                if (isset($l_allowed_object_types[$l_typedata['isys_obj_type__id']])) {
                    unset($l_allowed_object_types[$l_typedata['isys_obj_type__id']]);
                }

                if (empty($l_typedata["isys_obj_type__show_in_tree"])) {
                    continue;
                }

                $l_type_data_arr[$language->get($l_typedata['isys_obj_type__title']) . $l_typedata['isys_obj_type__id']] = $l_typedata;
            }

            $l_dao_object_types = new \idoit\Module\Cmdb\Model\CiTypeCache(isys_application::instance()->container->get('database'));

            /**
             * Assign additional object types to l_type_data_arr that are coming from a create right on these types
             */
            if (is_array($l_allowed_object_types) && !empty($l_allowed_object_types)) {
                // Get object counts which we could not retrieve in previous query

                $countSelect = "''";

                if ($displayCounter) {
                    $countSelect = '(SELECT COUNT(isys_obj__id) 
                        FROM isys_obj WHERE isys_obj__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' 
                        AND isys_obj__isys_obj_type__id = isys_obj_type__id 
                        ' . isys_auth_cmdb_objects::instance()->get_allowed_objects_condition(isys_auth::VIEW) . ')';
                }

                $l_objCountSql = 'SELECT isys_obj_type__id, ' . $countSelect . ' AS objectCount 
                    FROM isys_obj_type 
                    WHERE isys_obj_type__id IN (' . implode(',', $l_allowed_object_types) . ')';

                $l_resObjCount = $l_dao->retrieve($l_objCountSql);
                $l_objCountForTypes = [];
                while ($l_rowObjCount = $l_resObjCount->get_row()) {
                    $l_objCountForTypes[$l_rowObjCount['isys_obj_type__id']] = $l_rowObjCount['objectCount'];
                }

                foreach ($l_allowed_object_types as $l_object_type_id) {
                    $l_object_type = $l_dao_object_types->get($l_object_type_id);

                    if ($l_object_type->groupId == $l_gets[C__CMDB__GET__OBJECTGROUP]) {
                        $l_object_type_temp = $l_object_type->toArray($l_object_type->columnMap());
                        $l_object_type_temp['objcount'] = $l_objCountForTypes[$l_object_type_id];

                        $l_type_data_arr[$language->get($l_object_type->title) . $l_object_type->id] = $l_object_type_temp;
                    }
                }
                unset($l_object_type_temp);
            }

            if (isys_tenantsettings::get('cmdb.registry.object_type_sorting', C__CMDB__VIEW__OBJECTTYPE_SORTING__AUTOMATIC) == C__CMDB__VIEW__OBJECTTYPE_SORTING__AUTOMATIC &&
                count($l_type_data_arr)) {
                ksort($l_type_data_arr);
            }

            foreach ($l_type_data_arr as $l_typedata) {
                $l_icon = "";

                if (empty($l_typedata["isys_obj_type__show_in_tree"])) {
                    continue;
                }

                $l_issel = ($l_typedata["isys_obj_type__id"] == $l_objtypeid) ? 1 : 0;

                $l_gets[C__CMDB__GET__OBJECTTYPE] = $l_typedata["isys_obj_type__id"];

                if (!empty($l_typedata["isys_obj_type__icon"])) {
                    if (strstr($l_typedata["isys_obj_type__icon"], '/')) {
                        $l_icon = $g_config['www_dir'] . $l_typedata["isys_obj_type__icon"];
                    } else {
                        $l_icon = $g_dirs["images"] . "tree/" . $l_typedata["isys_obj_type__icon"];
                    }
                }

                $l_link = "javascript:tree_obj_type_click('" . $l_typedata["isys_obj_type__id"] . "');";
                $l_title = isys_glob_escape_string(isys_helper::sanitize_text($language->get($l_dao->get_objtype_name_by_id_as_string($l_typedata["isys_obj_type__id"]))));

                if ($displayCounter) {
                    $nodeLabel = '<span' . ($l_typedata["objcount"] > 0 ? '' : ' class="obj_noentries"') . '>' . $l_title . '</span> <span>(' . $l_typedata["objcount"] . ')</span>';
                } else {
                    $nodeLabel = '<span>' . $l_title . '</span> <span class="hide">(1)</span>';
                }


                $this->m_tree->add_node(
                    $l_typedata["isys_obj_type__id"],
                    $l_root,
                    $nodeLabel,
                    $l_link,
                    '',
                    $l_icon,
                    $l_issel,
                    '',
                    '',
                    true,
                    $l_typedata["isys_obj_type__const"]
                );
            }

            $l_settings = isys_component_dao_user::instance($l_dao->get_database_component())
                ->get_user_settings();

            if (!($l_settings['isys_user_ui__tree_visible'] & 1)) {
                $l_tpl->assign('treeHide', 1);
            } else {
                $l_tpl->assign('treeHide', 0);
            }

            $this->m_tree->set_tree_sort(false);
        }

        // Sets the eye for hiding empty nodes
        $this->m_tree->set_tree_visibility(true);

        isys_component_signalcollection::get_instance()
            ->emit("mod.cmdb.extendObjectTypeTree", $this->m_tree);
    }

    /**
     *
     * @return  string
     */
    public function tree_process()
    {
        $l_proc = '';

        if (defined("C__OBJECT_DRAGNDROP") && C__OBJECT_DRAGNDROP) {
            $message = isys_application::instance()->container->get('language')->get('LC__CMDB__TREE_VIEW__OBJECTTYPE__DROP_OBJECTS_CONFIRM');
            $l_proc = "init_drops({ confirmMessage:'{$message}' });";
        }

        return $this->m_tree->process(null, $l_proc);
    }
}
