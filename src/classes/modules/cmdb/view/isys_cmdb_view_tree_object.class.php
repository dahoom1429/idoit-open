<?php

use idoit\Component\Helper\Purify;

/**
 * CMDB Tree view for objects
 *
 * @package    i-doit
 * @subpackage CMDB_Views
 * @author     Andre Woesten <awoesten@i-doit.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_view_tree_object extends isys_cmdb_view_tree
{
    public function get_id()
    {
        return C__CMDB__VIEW__TREE_OBJECT;
    }

    /**
     *
     * @param  array $l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        parent::get_mandatory_parameters($l_gets);
    }

    public function get_name()
    {
        return 'Objektbaum';
    }

    /**
     *
     * @param  array $l_gets
     */
    public function get_optional_parameters(&$l_gets)
    {
        parent::get_optional_parameters($l_gets);

        $l_gets[C__CMDB__GET__OBJECT] = true;
        $l_gets[C__CMDB__GET__OBJECTTYPE] = true;
        $l_gets[C__CMDB__GET__OBJECTGROUP] = true;
    }

    /**
     * Builds the object tree.
     *
     * @throws   isys_exception_cmdb
     * @global   array                   $g_dirs
     * @global   isys_component_database $g_dirs
     * @author   Dennis Stücken <dstuecken@i-doit.org>
     * @version  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function tree_build()
    {
        global $g_dirs;

        // Prepare some variables.
        $l_gets = $this->get_module_request()->get_gets();
        $l_gets = Purify::castIntValues($l_gets);
        $l_posts = $this->get_module_request()->get_posts();
        $l_nodeid = 0;
        $l_icon = null;
        $l_jumpgets = [];
        $l_bSelected = false;
        $l_object_id = (int)$l_gets[C__CMDB__GET__OBJECT];
        $template = isys_application::instance()->container->get('template');
        $database = isys_application::instance()->container->get('database');
        $language = isys_application::instance()->container->get('language');
        $l_images = $g_dirs['images'];

        $l_dao = isys_cmdb_dao_object_type::instance($database);

        if ($l_object_id <= 0) {
            throw new isys_exception_cmdb('Request problem: No object id found.');
        }

        $this->remove_ajax_parameters($l_gets);

        // Create root node.
        $l_gets[C__CMDB__GET__CATS] = null;
        $l_gets[C__CMDB__GET__CATG] = defined_or_default('C__CATG__OVERVIEW');
        $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY;

        $l_title_link = isys_glob_build_ajax_url(C__FUNC__AJAX__CONTENT_BY_OBJECT, $l_gets);

        $l_title = $l_posts['C__CATG__GLOBAL_TITLE'];

        if (empty($l_posts['C__CATG__GLOBAL_TITLE'])) {
            $l_title = $l_dao->obj_get_title_by_id_as_string($l_object_id);
        }

        $l_obj_type = $l_gets[C__CMDB__GET__OBJECTTYPE] ?: $l_dao->get_objTypeID($l_object_id);

        if ($l_obj_type !== null) {
            $l_obj_row = $l_dao->get_type_by_id($l_obj_type);

            if ($l_obj_row !== null && !empty($l_obj_row['isys_obj_type__icon'])) {
                if (strpos($l_obj_row['isys_obj_type__icon'], '/') !== false) {
                    $l_icon = isys_application::instance()->www_path . $l_obj_row['isys_obj_type__icon'];
                } else {
                    $l_icon = $l_images . 'tree/' . $l_obj_row['isys_obj_type__icon'];
                }
            }

            // Add root entry.
            $l_tree_root = $this->m_tree->add_node(
                0,
                C__CMDB__TREE_NODE__PARENT,
                str_replace('\\', '&#92;', $l_title), // Fix for ID-602 (Backslashes in Object Title)
                $l_title_link,
                '',
                $l_icon
            );

            /*********************************************************************
             * SPECIFIC CATEGORIES
             *********************************************************************/
            $l_specific_category = $l_dao->get_specific_category($l_obj_type);

            // Objects can only have one specific category assigned
            if ($l_specific_category->num_rows() == 1) {
                $l_category = $l_specific_category->get_row();

                /* Skip category when class does not exist */
                if (class_exists($l_category['isysgui_cats__class_name'])) {
                    $l_category_id = $l_category['isysgui_cats__id'];
                    $l_category_title = isys_glob_escape_string(isys_helper::sanitize_text($language->get($l_category['isysgui_cats__title'])));
                    $l_category_tooltip = $l_category_title;
                    $l_category_const = $l_category['isysgui_cats__const'];
                    $l_skip_category = false;

                    // Needs to be checked differently because of the wildcard check
                    if ($l_category_id == defined_or_default('C__CATS__BASIC_AUTH') && !isys_auth_auth::instance()->is_allowed_to(isys_auth::SUPERVISOR, 'MODULE/C__MODULE__AUTH')) {
                        $l_skip_category = true;
                    }

                    $viewRight = isys_auth_cmdb::instance()
                        ->has_rights_in_obj_and_category(isys_auth::VIEW, $l_object_id, $l_category_const);

                    // See ID-4418
                    if (!$viewRight && $l_category['childsAmount'] == 0) {
                        $l_skip_category = true;
                    }

                    if (!$l_skip_category) {
                        $l_jumpgets = $l_gets;

                        // Reset the Category selection parameters.
                        $this->reduce_catspec_parameters($l_jumpgets);

                        $l_jumpgets[C__CMDB__GET__CATS] = $l_category_id;

                        $l_jumpgets[C__CMDB__GET__VIEWMODE] = ($l_category['isysgui_cats__list_multi_value']) ? C__CMDB__VIEW__LIST_CATEGORY : C__CMDB__VIEW__CATEGORY;

                        // Determine if the node is selected.
                        if ($l_category_id == $l_gets[C__CMDB__GET__CATS]) {
                            $this->m_select_node = C__CMDB__TREE_OBJECT__INC_SPECIFIC + $l_category_id;
                        }

                        $l_link = "javascript:get_content_by_object('" . $l_object_id . "','" . $l_jumpgets[C__CMDB__GET__VIEWMODE] . "','" . $l_category_id . "','" . C__CMDB__GET__CATS . "');";

                        try {
                            // Check if category has entries.
                            if (empty($_GET[C__CMDB__GET__OBJECT])) {
                                $l_category_title = '<span class="noentries">' . $l_category_tooltip . '</span>';
                            } elseif ($l_dao->check_category($_GET[C__CMDB__GET__OBJECT], $l_category['isysgui_cats__class_name'], $l_category['isysgui_cats__id'], $l_category['isysgui_cats__source_table'])) {
                                $l_category_title = $l_category_tooltip;
                            } else {
                                $l_category_title = '<span class="noentries">' . $l_category_tooltip . '</span>';
                            }
                        } catch (Exception $l_exception) {
                            isys_notify::error($l_exception->getMessage(), ['sticky' => true]);
                            $l_category_title = '<del class="red">' . $l_category_tooltip . '</del>';
                        }

                        // Adds the tree node.
                        $l_tree_spec = $this->m_tree->add_node(
                            C__CMDB__TREE_OBJECT__INC_SPECIFIC + $l_category_id,
                            $l_tree_root,
                            $l_category_title,
                            $l_link,
                            '',
                            '',
                            0,
                            '',
                            $l_category_tooltip,
                            true,
                            $l_category_const
                        );

                        // Don't create sub entry for category net in objecttype supernet. Supernets should not have any ip addresses or dhcp ranges.
                        if (!($l_obj_type == defined_or_default('C__OBJTYPE__SUPERNET') && $l_category_id == defined_or_default('C__CATS__NET'))) {
                            try {
                                // Create the subcategory subtree.
                                $subNodesAdded = $this->tree_create_subcategory(
                                    $l_category,
                                    C__CMDB__TREE_OBJECT__INC_SPECIFIC_EXT,
                                    $l_tree_spec,
                                    C__CMDB__GET__CATS,
                                    'isysgui_cats'
                                );

                                // See ID-4418
                                if (!$subNodesAdded && !$viewRight) {
                                    $this->m_tree->remove_node(C__CMDB__TREE_OBJECT__INC_SPECIFIC + $l_category_id);
                                }
                            } catch (Exception $l_exception) {
                                isys_notify::error($l_exception->getMessage(), ['sticky' => true]);
                            }
                        }
                    }
                }
            }

            /*********************************************************************
             * GLOBAL CATEGORIES
             *********************************************************************/

            /* Then we need the global categories */
            unset($l_category);
            $globalCategoryResult = $l_dao->get_global_categories($l_obj_type);

            if (is_countable($globalCategoryResult) && count($globalCategoryResult)) {
                while ($l_category = $globalCategoryResult->get_row()) {
                    // Skip category when class does not exist.
                    if (!class_exists($l_category['isysgui_catg__class_name'])) {
                        continue;
                    }

                    $viewRight = isys_auth_cmdb::instance()->has_rights_in_obj_and_category(isys_auth::VIEW, $l_object_id, $l_category['isysgui_catg__const']);

                    // See ID-4418
                    if (!$viewRight && $l_category['childsAmount'] == 0) {
                        continue;
                    }

                    if (in_array(
                        $l_category['isysgui_catg__id'],
                        filter_defined_constants(['C__CATG__LOGBOOK', 'C__CATG__PLANNING', 'C__CATG__RELATION', 'C__CATG__VIRTUAL_TICKETS', 'C__CATG__VIRTUAL_AUTH', 'C__CATG__MULTIEDIT'])
                    )) {
                        continue;
                    }

                    // Skip VIVA category if module is available:
                    if (defined('C__CATG__VIRTUAL_VIVA') && $l_category['isysgui_catg__id'] == C__CATG__VIRTUAL_VIVA) {
                        continue;
                    }

                    // Don't show a node for the overview page.
                    if ($l_category['isysgui_catg__property'] & C__RECORD_PROPERTY__NOT_SHOW_IN_LIST) {
                        continue;
                    }

                    $l_category_id = $l_category['isysgui_catg__id'];
                    $l_category_const = $l_category['isysgui_catg__const'];
                    $l_category_tooltip = isys_glob_escape_string(isys_helper::sanitize_text($language->get($l_category['isysgui_catg__title'])));

                    $l_jumpgets = $l_gets;
                    $this->reduce_catspec_parameters($l_jumpgets);

                    $l_jumpgets[C__CMDB__GET__CATG] = $l_category_id;

                    // Determine if the node has to be selected

                    $l_bSelected = 0;

                    if (isset($_GET[C__CMDB__GET__CATG]) && $l_category['isysgui_catg__id'] == $_GET[C__CMDB__GET__CATG]) {
                        $l_bSelected = 1;
                        $this->m_select_node = C__CMDB__TREE_OBJECT__INC_GLOBAL + $l_category['isysgui_catg__id'];
                    }

                    $l_jumpgets[C__CMDB__GET__VIEWMODE] = ($l_category['isysgui_catg__list_multi_value']) ? C__CMDB__VIEW__LIST_CATEGORY : C__CMDB__VIEW__CATEGORY;

                    $l_nodeid = C__CMDB__TREE_OBJECT__INC_GLOBAL + $l_category_id;

                    $l_link = "javascript:get_content_by_object('" . $l_object_id . "','" . $l_jumpgets[C__CMDB__GET__VIEWMODE] . "','" . $l_category_id . "','" . C__CMDB__GET__CATG . "');";

                    // Check if category has entries.
                    try {
                        if (!$l_object_id) {
                            $l_category_title = '<span class="noentries">' . $l_category_tooltip . '</span>';
                        } elseif ($l_dao->check_category($l_object_id, $l_category['isysgui_catg__class_name'], $l_category['isysgui_catg__id'], $l_category['isysgui_catg__source_table'])) {
                            $l_category_title = $l_category_tooltip;
                        } else {
                            $l_category_title = '<span class="noentries">' . $l_category_tooltip . '</span>';
                        }
                    } catch (Exception $l_exception) {
                        isys_notify::error($l_exception->getMessage(), ['sticky' => true]);
                        $l_category_title = '<del class="red">' . $l_category_tooltip . '</del>';
                    }

                    $l_tree_glob = $this->m_tree->add_node(
                        $l_nodeid,
                        $l_tree_root,
                        $l_category_title,
                        $l_link,
                        '',
                        '',
                        $l_bSelected,
                        '',
                        $l_category_tooltip,
                        true,
                        $l_category_const
                    );

                    try {
                        $subNodesAdded = $this->tree_create_subcategory($l_category, C__CMDB__TREE_OBJECT__INC_GLOBAL_EXT, $l_tree_glob);
                        // See ID-4418
                        if (!$subNodesAdded && !$viewRight) {
                            $this->m_tree->remove_node($l_nodeid);
                        }
                    } catch (Exception $l_exception) {
                        isys_notify::error($l_exception->getMessage(), ['sticky' => true]);
                    }
                }
            }

            if (defined('C__MODULE__CUSTOM_FIELDS') && class_exists('isys_custom_fields_dao')) {
                $l_nodeid += 20000;

                $l_dao = new isys_custom_fields_dao($database);
                $l_categories = $l_dao->get_assignments(null, $l_obj_type);

                while ($l_row = $l_categories->get_row()) {
                    if (defined($l_row['isysgui_catg_custom__const'])) {
                        if (!isys_auth_cmdb::instance()->has_rights_in_obj_and_category(isys_auth::VIEW, $l_object_id, $l_row['isysgui_catg_custom__const'])) {
                            continue;
                        }

                        $l_nodeid++;

                        $dao = isys_cmdb_dao_category_g_custom_fields::instance($database)
                            ->set_catg_custom_id($l_row['isysgui_catg_custom__id']);

                        $viewMode = $dao->is_multivalued() ? C__CMDB__VIEW__LIST_CATEGORY : C__CMDB__VIEW__CATEGORY;
                        $l_link = "javascript:get_content_by_object('" . $l_object_id . "','" . $viewMode . "','" . defined_or_default('C__CATG__CUSTOM_FIELDS') . "','" . C__CMDB__GET__CATG . "','" . $l_row['isysgui_catg_custom__id'] . "');";

                        $l_count = $dao->get_count($l_object_id);

                        $l_category_title = isys_glob_escape_string(isys_helper::sanitize_text($language->get($l_row['isysgui_catg_custom__title'])));

                        if (empty($l_object_id) || !$l_count) {
                            $l_category_title = '<span class="noentries">' . $l_category_title . '</span>';
                        }

                        // Adding the language manager, for custom translations: ID-1649.
                        $this->m_tree->add_node(
                            $l_nodeid,
                            $l_tree_root,
                            $l_category_title,
                            $l_link,
                            '',
                            '',
                            $l_bSelected,
                            '',
                            '',
                            true,
                            $l_row['isysgui_catg_custom__const']
                        );
                    }
                }
            }

            $l_menu_sticky_links = [];

            // Prepare the sticky "CMDB-Explorer" link.
            if (defined('C__CMDB__VIEW__EXPLORER') && isys_auth_cmdb::instance()->is_allowed_to(isys_auth::VIEW, 'EXPLORER')) {
                $l_menu_sticky_links['explorer'] = [
                    'title' => 'CMDB-Explorer',
                    'icon'  => $g_dirs['images'] . 'icons/silk/chart_organisation.png',
                    'link'  => isys_helper_link::create_url([
                        C__CMDB__GET__VIEWMODE      => C__CMDB__VIEW__EXPLORER,
                        C__CMDB__GET__OBJECT        => $l_object_id,
                        C__CMDB__VISUALIZATION_TYPE => C__CMDB__VISUALIZATION_TYPE__TREE,
                        C__CMDB__VISUALIZATION_VIEW => C__CMDB__VISUALIZATION_VIEW__OBJECT,
                    ])
                ];
            }

            // Preparing the sticky "Relation" link.
            if (isys_auth_cmdb::instance()->has_rights_in_obj_and_category(isys_auth::VIEW, $l_object_id, 'C__CATG__RELATION')) {
                $l_menu_sticky_links['relation'] = [
                    'title' => $language->get('LC__CMDB__CATG__RELATION'),
                    'icon'  => $g_dirs['images'] . 'icons/silk/arrow_out.png',
                    'link'  => "javascript:get_content_by_object('" . $l_object_id . "','" . C__CMDB__VIEW__LIST_CATEGORY . "','" . defined_or_default('C__CATG__RELATION') . "','" . C__CMDB__GET__CATG . "');"
                ];
            }

            // Prepare the sticky "Planning" link.
            if (defined('C__CATG__PLANNING') && defined('C__MODULE__PRO') && isys_auth_cmdb::instance()->has_rights_in_obj_and_category(isys_auth::VIEW, $l_object_id, 'C__CATG__PLANNING')) {
                $l_menu_sticky_links['planning'] = [
                    'title' => $language->get('LC__CMDB__CATG__PLANNING'),
                    'icon'  => $g_dirs['images'] . 'icons/silk/calendar.png',
                    'link'  => "javascript:get_content_by_object('" . $l_object_id . "','" . C__CMDB__VIEW__LIST_CATEGORY . "','" . C__CATG__PLANNING . "','" . C__CMDB__GET__CATG . "');"
                ];
            }

            // Preparing the sticky "Logbook" link.
            if (isys_auth_cmdb::instance()->has_rights_in_obj_and_category(isys_auth::VIEW, $l_object_id, 'C__CATG__LOGBOOK')) {
                $l_menu_sticky_links['logbook'] = [
                    'title' => $language->get('LC__CMDB__CATG__LOGBOOK'),
                    'icon'  => $g_dirs['images'] . 'icons/silk/book_open.png',
                    'link'  => "javascript:get_content_by_object('" . $l_object_id . "','" . C__CMDB__VIEW__LIST_CATEGORY . "','" . defined_or_default('C__CATG__LOGBOOK') . "','" . C__CMDB__GET__CATG . "');"
                ];
            }

            $template->assign('menuTreeLinksBack', $this->get_back_url(C__CMDB__VIEW__LIST_OBJECT, C__CMDB__VIEW__TREE_OBJECTTYPE))
                ->assign('menuTreeStickyLinks', $l_menu_sticky_links);

            // Emit a new signal with the parameters: <isys_obj__id>, <isys_obj_type__id>
            try {
                isys_component_signalcollection::get_instance()->emit('mod.cmdb.processMenuTreeLinks', $template, 'menuTreeStickyLinks', $l_object_id, $l_obj_type);
            } catch (Exception $e) {
                isys_notify::debug($e->getMessage(), ['sticky' => true]);
            }

            $l_settings = isys_component_dao_user::instance($database)->get_user_settings();

            $template->assign('treeHide', ($l_settings['isys_user_ui__tree_visible'] & 2) ? 0 : 1);
        }

        // Sets the eye for hiding empty nodes
        $this->m_tree->set_tree_visibility(true);

        try {
            isys_component_signalcollection::get_instance()->emit('mod.cmdb.extendObjectTree', $this->m_tree);
        } catch (Exception $e) {
            isys_notify::debug($e->getMessage(), ['sticky' => true]);
        }
    }

    /**
     *
     * @return  string
     */
    public function tree_process()
    {
        return $this->m_tree->process($this->m_select_node);
    }

    /**
     * Removes all category-specific GET-parameters
     *
     * @param  array &$p_arGet
     * @param  array $p_arExceptions
     */
    protected function reduce_catspec_parameters(&$p_arGet, $p_arExceptions = null)
    {
        $l_toDelete = [
            C__CMDB__GET__CATG,
            C__CMDB__GET__CATS,
            C__CMDB__GET__CATLEVEL,
            C__CMDB__GET__CATLEVEL_1,
            C__CMDB__GET__CATLEVEL_2,
            C__CMDB__GET__CATLEVEL_3,
            C__CMDB__GET__CATLEVEL_4,
            C__CMDB__GET__CATLEVEL_5,
            C__CMDB__GET__CAT_LIST_VIEW,
            C__CMDB__GET__CAT_MENU_SELECTION
        ];

        if ($p_arExceptions) {
            $l_toDelete = array_diff($l_toDelete, $p_arExceptions);
        }

        foreach ($l_toDelete as $l_delP) {
            unset($p_arGet[$l_delP]);
        }
    }

    /**
     * Public constructor.
     *
     * @param  isys_module_request $p_modreq
     */
    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);

        $this->m_select_node = C__CMDB__TREE_NODE__PARENT;
    }
}
