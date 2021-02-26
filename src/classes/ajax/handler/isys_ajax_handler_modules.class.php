<?php

/**
 * AJAX Handler for fetching module-contents.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_modules extends isys_ajax_handler
{
    /**
     * Init method will be called automatically.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @see     isys_ajax_handler::init()
     */
    public function init()
    {
        global $g_dirs, $g_comp_session, $g_config;

        // Fetch the module manager.
        $l_modman = isys_module_request::get_instance()
            ->get_module_manager();

        $l_cache_obj = isys_caching::factory('auth-' . $g_comp_session->get_user_id());

        $l_cache = $l_cache_obj->get('module-dropdown');

        if ($l_cache === false) {
            // Fetch the modules.
            $l_modules = $l_modman->get_modules(null, null, true);

            // Fetch the module sorting
            $l_sort_array = $l_modman->get_module_sorting();

            $l_parent_modules = $l_links = $l_out = [];

            $l_counter = 0;
            // Iterate through the modules and display each.
            while ($l_module = $l_modules->get_row()) {
                $l_counter++;
                if (class_exists($l_module['isys_module__class'])) {
                    if (constant($l_module['isys_module__class'] . '::DISPLAY_IN_MAIN_MENU')) {
                        // Get auth class
                        $l_auth_instance = isys_module_manager::instance()
                            ->get_module_auth($l_module['isys_module__id']);

                        // Check for rights if module is authable otherwise display it
                        if ((is_a($l_auth_instance, 'isys_auth') && $l_auth_instance->has_any_rights_in_module()) || $l_auth_instance === false) {
                            $l_module_url = isys_application::instance()->www_path .
                                (defined($l_module['isys_module__class'] . '::MAIN_MENU_REWRITE_LINK') ? $l_module['isys_module__identifier'] : '?' . C__GET__MODULE_ID . '=' .
                                    $l_module['isys_module__id']);

                            if (!is_null($l_module['isys_module__parent'])) {

                                if (!isset($l_parent_modules[$l_module['isys_module__parent']])) {

                                    $l_parent_module = $l_modman->get_modules($l_module['isys_module__parent'], null, true)
                                        ->get_row();
                                    $l_parent_module_url = isys_application::instance()->www_path .
                                        (defined($l_module['isys_module__class'] . '::MAIN_MENU_REWRITE_LINK') ? $l_parent_module['isys_module__identifier'] : '?' .
                                            C__GET__MODULE_ID . '=' . $l_parent_module['isys_module__id']);

                                    if ($l_module['isys_module__parent'] == defined_or_default('C__MODULE__MANAGER')) {
                                        $l_parent_module['isys_module__title'] = 'LC__NAVIGATION__MAINMENU__TITLE_MODULE';
                                    }

                                    $l_parent_modules[$l_module['isys_module__parent']] = [
                                        'id'     => $l_parent_module['isys_module__id'],
                                        'sort'   => (isset($l_sort_array[$l_parent_module['isys_module__title']])) ? $l_sort_array[$l_parent_module['isys_module__title']] : 99 +
                                            $l_counter,
                                        'title'  => $l_parent_module['isys_module__title'],
                                        'url'    => $l_parent_module_url,
                                        'icon'   => strstr($l_module['isys_module__icon'], '/') ? $g_config['www_dir'] .
                                            $l_parent_module['isys_module__icon'] : $l_parent_module['isys_module__icon'],
                                        'childs' => []
                                    ];
                                }

                                $l_module_url = isys_application::instance()->www_path .
                                    (defined($l_module['isys_module__class'] . '::MAIN_MENU_REWRITE_LINK') ? $l_module['isys_module__identifier'] : '?' . C__GET__MODULE_ID .
                                        '=' . $l_module['isys_module__id']);

                                $l_parent_modules[$l_module['isys_module__parent']]['childs'][$l_module['isys_module__title']] = [
                                    'id'   => $l_module['isys_module__id'],
                                    'sort' => (isset($l_sort_array[$l_module['isys_module__title']])) ? $l_sort_array[$l_module['isys_module__title']] : 99 + $l_counter,
                                    'url'  => $l_module_url,
                                    'icon' => strstr($l_module['isys_module__icon'], '/') ? $g_config['www_dir'] .
                                        $l_module['isys_module__icon'] : $l_module['isys_module__icon']
                                ];

                                unset($l_links[$l_module['isys_module__title']]);
                            } else if (!method_exists($l_module['isys_module__class'], 'get_additional_links')) {
                                $l_links[$l_module['isys_module__title']] = [
                                    'id'   => $l_module['isys_module__id'],
                                    'sort' => (isset($l_sort_array[$l_module['isys_module__title']])) ? $l_sort_array[$l_module['isys_module__title']] : 99 + $l_counter,
                                    'url'  => $l_module_url,
                                    'icon' => strstr($l_module['isys_module__icon'], '/') ? $g_config['www_dir'] .
                                        $l_module['isys_module__icon'] : $l_module['isys_module__icon']
                                ];
                            }
                        }
                    }

                    if (method_exists($l_module['isys_module__class'], 'get_additional_links')) {
                        $l_additional_links = call_user_func([
                            $l_module['isys_module__class'],
                            'get_additional_links'
                        ]);

                        if (is_array($l_additional_links)) {
                            foreach ($l_additional_links AS $l_key => $l_content) {
                                if ($l_key == 'RELATION') {
                                    $l_right_relation = isys_auth_cmdb::instance()
                                        ->is_allowed_to(isys_auth::VIEW, 'OBJ_IN_TYPE/C__OBJTYPE__RELATION');
                                    $l_right_parallel_relation = isys_auth_cmdb::instance()
                                        ->is_allowed_to(isys_auth::VIEW, 'OBJ_IN_TYPE/C__OBJTYPE__PARALLEL_RELATION');
                                    if (!$l_right_relation && !$l_right_parallel_relation) {
                                        continue;
                                    }
                                } else {
                                    // Get auth
                                    $l_auth_instance = isys_module_manager::instance()
                                        ->get_module_auth($l_module['isys_module__id']);

                                    if (is_a($l_auth_instance, 'isys_auth') && !$l_auth_instance->is_allowed_to(isys_auth::VIEW, $l_key)) {
                                        continue;
                                    }
                                }

                                if (isset($l_content[2])) {
                                    if (!isset($l_parent_modules[$l_content[2]])) {
                                        if ($l_content[2] == defined_or_default('C__MODULE__MANAGER')) {
                                            $l_module['isys_module__title'] = 'LC__NAVIGATION__MAINMENU__TITLE_MODULE';
                                        }
                                        $l_parent_modules[$l_content[2]] = [
                                            'id'     => $l_module['isys_module__id'],
                                            'sort'   => (isset($l_sort_array[$l_module['isys_module__title']])) ? $l_sort_array[$l_module['isys_module__title']] : 99 +
                                                $l_counter,
                                            'title'  => $l_module['isys_module__title'],
                                            'url'    => isys_application::instance()->www_path . '?' . C__GET__MODULE_ID . '=' . $l_module['isys_module__id'],
                                            'icon'   => strstr($l_module['isys_module__icon'], '/') ? $g_config['www_dir'] .
                                                $l_module['isys_module__icon'] : $l_module['isys_module__icon'],
                                            'childs' => []
                                        ];
                                    }

                                    $l_parent_modules[$l_content[2]]['childs'][$l_content[0]] = [
                                        'id'   => $l_module['isys_module__id'],
                                        'sort' => (isset($l_sort_array[$l_content[0]])) ? $l_sort_array[$l_content[0]] : 99 + $l_counter,
                                        'url'  => isys_application::instance()->www_path . $l_content[1],
                                        'icon' => $l_content[3]
                                    ];
                                    unset($l_links[$l_content[0]]);
                                    continue;
                                }

                                $l_links[$l_content[0]] = [
                                    'id'   => $l_module['isys_module__id'],
                                    'sort' => (isset($l_sort_array[$l_content[0]])) ? $l_sort_array[$l_content[0]] : 99 + $l_counter,
                                    'url'  => isys_application::instance()->www_path . $l_content[1],
                                    'icon' => $l_content[3]
                                ];
                            }
                        } else {
                            isys_notify::debug('Error in module: ' . $l_module['isys_module__class'] . ' get_additional_links() is not returning an array.');
                        }
                    }
                }
            }

            // IP address management.
            if (defined('C__OBJTYPE__LAYER3_NET') && isys_auth_cmdb::instance()
                ->is_allowed_to(isys_auth::VIEW, 'OBJ_IN_TYPE/C__OBJTYPE__LAYER3_NET')) {
                $l_links['LC__CMDB__IP__ADDRESS_MANAGEMENT'] = [
                    'sort'    => $l_sort_array['LC__CMDB__IP__ADDRESS_MANAGEMENT'],
                    'url'     => isys_application::instance()->www_path . isys_helper_link::create_url([
                            C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__LIST_OBJECT,
                            C__CMDB__GET__OBJECTTYPE => C__OBJTYPE__LAYER3_NET
                        ]),
                    'onclick' => '',
                    'icon'    => $g_dirs['images'] . 'icons/silk/chart_organisation.png'
                ];
            }

            if (defined('C__OBJTYPE__IT_SERVICE') && class_exists('isys_module_itservice')) {
                if (defined('C__MODULE__ITSERVICE') && isys_auth_cmdb::instance()
                        ->is_allowed_to(isys_auth::VIEW, 'OBJ_IN_TYPE/C__OBJTYPE__IT_SERVICE')) {
                    $l_links['LC__MODULE__ITSERVICE']['url'] = isys_application::instance()->www_path . isys_helper_link::create_url([
                            C__GET__MODULE_ID        => C__MODULE__ITSERVICE,
                            C__GET__TREE_NODE        => C__MODULE__ITSERVICE . 2,
                            C__CMDB__GET__OBJECTTYPE => C__OBJTYPE__IT_SERVICE,
                            C__GET__SETTINGS_PAGE    => isys_module_itservice::PAGE__TYPE_LIST
                        ]);
                }
            }

            $l_cache_arr = [
                'parent' => $l_parent_modules,
                'links'  => $l_links
            ];

            try {
                $l_cache_obj->set('module-dropdown', $l_cache_arr)
                    ->save();
            } catch (Exception $e) {
                isys_notify::warning($e->getMessage());
            }
        } else {
            $l_parent_modules = $l_cache['parent'];
            $l_links = $l_cache['links'];
        }

        $l_inactive_modules = $l_modman->get_active_inactive_modules(false);

        // Bring the array in the right order, before adding the closing UL-element.
        $l_disable_class = 'nondisabled';
        $l_out = [];

        if (is_countable($l_parent_modules) && count($l_parent_modules) > 0) {
            foreach ($l_parent_modules AS $l_parent_module_id => $l_content) {
                if (in_array($l_content['id'], $l_inactive_modules)) {
                    continue;
                }

                $l_parent_module_title = $l_content['title'];
                $l_childs = $l_content['childs'];
                $l_parent_url = $l_content['url'];
                $l_parent_icon = $l_content['icon'];

                isys_glob_sort_array_by_column($l_childs, 'sort');
                if (is_countable($l_childs) && count($l_childs) > 0) {
                    $l_out_string = '<li id="parent_' . $l_parent_module_id . '" class="' . $l_disable_class . '" onmouseover="$(\'module-dropdown\').show_childs(\'childs_' .
                        $l_parent_module_id . '\');" >' . '<a href="' . $l_parent_url . '" ><img class="vam module-icon" src="' . $l_parent_icon . '" alt=" ">' . '<span>' .
                        isys_application::instance()->container->get('language')
                            ->get($l_parent_module_title) . '</span><img class="fr vam m5" src=\'' . isys_application::instance()->www_path .
                        'images/icons/tree_icon_right.png\' />' . '</a></li>';

                    $l_out_string .= '<ul id="childs_' . $l_parent_module_id . '" class="moduleChilds" style="display:none">';

                    foreach ($l_childs AS $l_child_title => $l_child_info) {
                        $l_out_string .= '<li><a href="' . $l_child_info['url'] . '">' . '<img class="vam module-icon" src="' . $l_child_info['icon'] . '" alt=" "><span>' .
                            isys_application::instance()->container->get('language')
                                ->get($l_child_title) . '</span>' . '</a></li>';
                    }

                    $l_out_string .= '</ul>';
                    $l_out[] = $l_out_string;
                }
            }
        }

        isys_glob_sort_array_by_column($l_links, 'sort');

        if (is_countable($l_links) && count($l_links) > 0) {
            foreach ($l_links AS $l_title => $l_content) {
                if (in_array($l_content['id'], $l_inactive_modules)) {
                    continue;
                }

                $l_url = $l_content['url'];
                $l_icon = $l_content['icon'];
                $l_disable_class = 'nondisabled';
                $l_onclick = '';

                if (isset($l_content['onclick'])) {
                    $l_onclick = 'onclick="' . $l_content['onclick'] . '"';
                }

                $l_out[] = '<li class="' . $l_disable_class . '"><a href="' . $l_url . '" ' . $l_onclick . ' onmouseover="$(\'module-dropdown\').close_all_childs();">' .
                    '<img class="vam module-icon" src="' . $l_icon . '" alt=" "><span>' . isys_application::instance()->container->get('language')
                        ->get($l_title) . '</span>' . '</a></li>';
            }
        }

        // Return the content.
        echo '<ul>' . implode('', $l_out) . '</ul>';

        // And die.
        $this->_die();
    }
}
