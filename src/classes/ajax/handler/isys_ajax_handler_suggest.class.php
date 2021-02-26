<?php
/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

use idoit\Component\Browser\Condition\SearchCondition;
use idoit\Component\Browser\Retriever;

define("C__SUGGEST__MINIMUM_SEARCH__LENGTH", 3);

class isys_ajax_handler_suggest extends isys_ajax_handler
{
    /**
     * Initialize the suggestion request.
     *
     * @return  boolean
     */
    public function init()
    {
        $_POST = $this->m_post;
        $_GET = $this->m_get;
        $l_return = [];

        $language = isys_application::instance()->container->get('language');
        $searchString = false;
        $cmdbFilter = $objectTypeFilter = $categoryFilter = $objectTypeBlacklist = [];

        if (isset($_POST['search']) && mb_strlen(trim($_POST['search'])) >= C__SUGGEST__MINIMUM_SEARCH__LENGTH) {
            $searchString = trim($_POST['search']);
        }

        if (isset($_POST[isys_popup_browser_object_ng::C__CMDB_FILTER]) && !empty($_POST[isys_popup_browser_object_ng::C__CMDB_FILTER])) {
            $cmdbFilter = explode(';', $_POST[isys_popup_browser_object_ng::C__CMDB_FILTER]);
        }

        if (isset($_POST[isys_popup_browser_object_ng::C__TYPE_FILTER]) && !empty($_POST[isys_popup_browser_object_ng::C__TYPE_FILTER])) {
            $objectTypeFilter = explode(';', $_POST[isys_popup_browser_object_ng::C__TYPE_FILTER]);
        }

        if (isset($_POST[isys_popup_browser_object_ng::C__CAT_FILTER]) && !empty($_POST[isys_popup_browser_object_ng::C__CAT_FILTER])) {
            $categoryFilter = explode(';', $_POST[isys_popup_browser_object_ng::C__CAT_FILTER]);
        }

        if (isset($_POST[isys_popup_browser_object_ng::C__TYPE_BLACK_LIST]) && !empty($_POST[isys_popup_browser_object_ng::C__TYPE_BLACK_LIST])) {
            $objectTypeBlacklist = explode(';', $_POST[isys_popup_browser_object_ng::C__TYPE_BLACK_LIST]);
        }

        // @see  ID-4514  Because of some problems in our architecture, we need to pass custom filters like this: "\namespace\classname:parameter".
        if (isset($_POST[isys_popup_browser_object_ng::C__CUSTOM_FILTERS]) && !empty($_POST[isys_popup_browser_object_ng::C__CUSTOM_FILTERS])) {
            $customFilters = array_map(function ($customFilter) {
                return explode(':', $customFilter);
            }, explode(';', $_POST[isys_popup_browser_object_ng::C__CUSTOM_FILTERS]));
        }

        // Filter.
        $l_allowed_object_types = isys_popup_browser_object_ng::get_objecttype_filter($objectTypeFilter, $categoryFilter, $objectTypeBlacklist);

        $l_condition = '';

        if (isys_tenantsettings::get('auth.use-in-object-browser', false)) {
            $l_condition = isys_auth_cmdb_objects::instance()->get_allowed_objects_condition();
        }

        if (is_countable($cmdbFilter) && count($cmdbFilter)) {
            $l_status = $cmdbFilter;
            $l_status_array = [];

            foreach ($l_status as $l_cmdb_status) {
                if (defined($l_cmdb_status)) {
                    $l_status_array[] = (int)constant($l_cmdb_status);
                }
            }

            if (count($l_status_array) > 0) {
                $l_condition = ' AND isys_obj__isys_cmdb_status__id IN (' . implode(',', $l_status_array) . ') ';
            }
        }

        $l_allowed_object_types = array_flip($l_allowed_object_types);

        switch ($_GET["method"]) {
            // @see  ID-677  New "object browser" suggest method that uses the superior "Retriever" logic 8)
            case 'object-browser':
                if ($searchString) {
                    $dao = isys_cmdb_dao::instance($this->m_database_component);
                    $condition = new SearchCondition($this->m_database_component);

                    $globalCategories = array_filter($categoryFilter, function ($categoryConstant) {
                        return strpos($categoryConstant, '_CATG_') !== false;
                    });

                    $specificCategories = array_filter($categoryFilter, function ($categoryConstant) {
                        return strpos($categoryConstant, '_CATS_') !== false;
                    });

                    $filters = [
                        'CmdbStatusFilter'        => $cmdbFilter,
                        'GlobalCategoryFilter'    => $globalCategories,
                        'ObjectTypeFilter'        => $objectTypeFilter,
                        'ObjectTypeExcludeFilter' => $objectTypeBlacklist,
                        'SpecificCategoryFilter'  => $specificCategories,
                    ];

                    $condition->registerFilterByArray($filters);
                    $condition->setParameter($searchString);

                    if (is_array($customFilters) && count($customFilters)) {
                        foreach ($customFilters as $customFilter) {
                            if (!class_exists($customFilter[0]) || !is_a($customFilter[0], \idoit\Component\Browser\FilterInterface::class, true)) {
                                continue;
                            }

                            /** @var \idoit\Component\Browser\FilterInterface $customFilterInstance */
                            $customFilterInstance = new $customFilter[0]($this->m_database_component);

                            if (isys_format_json::is_json_array($customFilter[1])) {
                                $customFilter[1] = isys_format_json::decode($customFilter[1]);
                            } elseif (is_scalar($customFilter[1])) {
                                if (strpos($customFilter[1], ';') !== false) {
                                    $customFilter[1] = explode(';', $customFilter[1]);
                                } elseif (strpos($customFilter[1], ',') !== false) {
                                    $customFilter[1] = explode(',', $customFilter[1]);
                                } else {
                                    $customFilter[1] = [$customFilter[1]];
                                }
                            }

                            $condition->registerFilter($customFilterInstance->setParameter($customFilter[1]));
                        }
                    }

                    $retriever = new Retriever();
                    $retriever->setCondition($condition);
                    $retriever->setAttributes([
                        'C__CATG__GLOBAL::title',
                        'C__CATG__GLOBAL::type'
                    ]);

                    $objects = $retriever->getObjects();
                    $authCondition = '';

                    if (isys_tenantsettings::get('auth.use-in-object-browser', false)) {
                        $authCondition = isys_auth_cmdb_objects::instance()->get_allowed_objects_condition();
                    }

                    $sql = 'SELECT isys_obj__id, isys_obj__title, isys_obj_type__id, isys_obj_type__title 
                        FROM isys_obj 
                        INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
                        WHERE isys_obj__id ' . $dao->prepare_in_condition($objects) . $authCondition . ';';

                    $objectResult = $dao->retrieve($sql);

                    while ($objectRow = $objectResult->get_row()) {
                        if ($objectRow['isys_obj__id'] > 0 && isset($l_allowed_object_types[$objectRow['isys_obj_type__id']])) {
                            $objectTitle = trim($objectRow['isys_obj__title']);
                            $objectTypeTitle = trim($language->get($objectRow['isys_obj_type__title']));

                            $l_return[strtolower($objectTypeTitle . ' ' . $objectTitle)] = '<li id="' . $objectRow['isys_obj__id'] . '" title="' . isys_glob_htmlentities($objectTitle) . '">' .
                                isys_glob_htmlentities($objectTypeTitle . ' &raquo; ' . $objectTitle) .
                                '</li>';
                        }
                    }

                    ksort($l_return);

                    $l_return = array_values($l_return);
                }
                break;

            case "physical-logical-location":
                if ($searchString) {
                    $l_dao = new isys_cmdb_dao_category_g_logical_unit($this->m_database_component);
                    $l_browser = new isys_popup_browser_location();

                    // SQL for retrieving objects by name which have the logical location category and are assigned.
                    $l_containers = $l_dao->search_located_objects_by_title($searchString, true);
                    $l_object_types_workstations = $l_dao->get_object_types_by_category(defined_or_default('C__CATG__LOGICAL_UNIT'), 'g', false);
                    $l_object_types_assigned_logical_unit = $l_dao->get_object_types_by_category(defined_or_default('C__CATG__ASSIGNED_LOGICAL_UNIT'), 'g', false);
                    $l_person_assigned_logical_unit = $l_dao->get_object_types_by_category(defined_or_default('C__CATG__PERSON_ASSIGNED_WORKSTATION'), 'g', false);

                    $l_browser->set_format_str_cut(false)
                        ->set_format_object_name_cut(true)
                        ->set_format_exclude_self(false)
                        ->set_format_as_text(true);

                    while ($l_row = $l_containers->get_row()) {
                        if ($l_row["isys_obj__id"] > 0) {
                            $l_object_id = $l_row["isys_obj__id"];

                            //($p_obj_id, $p_str_cut = false, $p_object_name_cut = 100, $p_exclude_self = false, $p_as_string = false)
                            $l_title = strip_tags($l_browser->format_selection($l_object_id, true));

                            // If we found no location path we'll just receive the object title.
                            if ($l_title == $l_row['isys_obj__title']) {
                                // So we try to find the physical location of the objects parent.
                                $l_title = strip_tags($l_browser->format_selection($l_row["parent"], true));

                                if (in_array($l_row['isys_obj__isys_obj_type__id'], $l_object_types_workstations) &&
                                    in_array($l_row['parent_objtype'], $l_person_assigned_logical_unit)) {
                                    $l_title .= isys_tenantsettings::get('gui.separator.location', ' > ') . $l_row['isys_obj__title'];
                                    $l_object_id = $l_row['isys_obj__id'];
                                } elseif ($l_row['parent_parent'] > 0 && in_array($l_row['parent_parent_objtype'], $l_person_assigned_logical_unit) &&
                                    in_array($l_row['parent_objtype'], $l_object_types_assigned_logical_unit)) {
                                    // Retrieve person
                                    $l_title .= isys_tenantsettings::get('gui.separator.location', ' > ') . $l_row['parent_parent_title'];
                                    $l_title .= isys_tenantsettings::get('gui.separator.location', ' > ') . $l_row['parent_title'];
                                    $l_title .= isys_tenantsettings::get('gui.separator.location', ' > ') . $l_row['isys_obj__title'];
                                    $l_object_id = $l_row['isys_obj__id'];
                                } else {
                                    $l_object_id = $l_row["parent"];
                                }
                            }

                            if (empty($l_title)) {
                                $l_title = $l_row["isys_obj__title"];
                            }

                            if ($l_object_id > 0) {
                                $l_return[] = '<li id="' . $l_object_id . '">' . $l_title . '</li>';
                            }
                        }
                    }
                }

            // no break here!

            case "location":
                if ($searchString) {
                    $l_dao = new isys_cmdb_dao_category_g_location($this->m_database_component);
                    $l_browser = new isys_popup_browser_location();
                    $l_containers = $l_dao->get_container_objects(
                        $searchString,
                        C__RECORD_STATUS__NORMAL,
                        !!isys_tenantsettings::get('auth.use-in-location-tree', false)
                    );

                    $l_browser->set_format_str_cut(false)
                        ->set_format_object_name_cut(true)
                        ->set_format_exclude_self(false)
                        ->set_format_as_text(true);

                    while ($l_row = $l_containers->get_row()) {
                        if ($l_row["isys_obj__id"] > 0) {
                            $l_title = strip_tags($l_browser->format_selection($l_row["isys_obj__id"], true));

                            if (empty($l_title)) {
                                $l_title = $l_row["isys_obj__title"];
                            }

                            $l_return[] = '<li id="' . $l_row["isys_obj__id"] . '">' . $l_title . '</li>';
                        }
                    }
                }
                $l_return = array_unique($l_return);
                break;

            case "object_with_no_type":
            case "object":
                if ($searchString) {
                    $l_dao = new isys_cmdb_dao_category_g_global($this->m_database_component);
                    $l_data = $l_dao->search_objects($searchString, $_POST["typeFilter"], $_POST["groupFilter"], $l_condition);

                    while ($l_row = $l_data->get_row()) {
                        if ($l_row["isys_obj__id"] > 0 && isset($l_allowed_object_types[$l_row['isys_obj_type__id']])) {
                            $l_return_string = '<li id="' . $l_row["isys_obj__id"] . '" title="' . $l_row["isys_obj__title"] . ' (' .
                                isys_glob_str_stop(isys_application::instance()->container->get('language')
                                    ->get($l_row["isys_obj_type__title"]), 15) . ')">' . '<strong>' . isys_glob_str_stop($l_row["isys_obj__title"], 50) . '</strong> ';
                            if ($_GET["method"] != "object_with_no_type") {
                                $l_return_string .= '(' . isys_glob_str_stop(isys_application::instance()->container->get('language')
                                        ->get($l_row["isys_obj_type__title"]), 15) . ')</li>';
                            } else {
                                $l_return_string = rtrim($l_return_string) . '</li>';
                            }
                            $l_return[] = $l_return_string;
                        }
                    }
                }
                break;

            case 'autotext':
                if ($searchString && isset($_POST[0]) && mb_strlen($_POST[0]) > 0 && isset($_POST[1]) && mb_strlen($_POST[1]) > 0) {
                    $l_source = trim($_POST[0]);
                    $l_property = trim($_POST[1]);

                    $l_dao = new isys_cmdb_dao($this->m_database_component);
                    $l_data = $l_dao->get_autotext($searchString, $l_source, $l_property);

                    if ($l_data->num_rows() > 0) {
                        while ($l_row = $l_data->get_row()) {
                            $l_id = $l_row[$l_source . '__id'];
                            $l_title = $l_row[$l_source . '__' . $l_property];
                            $l_return[] = '<li id="' . $l_id . '" title="' . $l_title . '">' . $l_title . '</li>';
                        }
                    }
                }
                break;

            case 'category_by_attributes':
                $l_dao = isys_cmdb_dao_category_property::instance($this->m_database_component);
                $l_filter = strtolower($_POST['search']);
                $l_disable_global = !!$_POST['disableGlobal'];
                $l_disable_specific = !!$_POST['disableSpecific'];
                $l_disable_custom = !!$_POST['disableCustom'];

                // Retrieve all possible properties.
                $l_res = $l_dao->retrieve_properties();
                $parentCategories = [];

                while ($l_row = $l_res->get_row()) {
                    if ($l_disable_global && $l_row['catg'] > 0) {
                        continue;
                    }

                    if ($l_disable_specific && $l_row['cats'] > 0) {
                        continue;
                    }

                    if ($l_disable_custom && $l_row['catg_custom'] > 0) {
                        continue;
                    }

                    // Also skip the "HR" and "HTML" fields of custom categories.
                    if ($l_row['catg_custom'] > 0 && (strpos($l_row['key'], 'hr_c_') === 0 || strpos($l_row['key'], 'html_c_') === 0)) {
                        continue;
                    }

                    if ($l_row['parent'] > 0) {
                        if ($l_row['catg']) {
                            // Global category parent
                            $parentCategoryTitle = isys_application::instance()->container->get('language')
                                ->get($l_dao->get_catg_name_by_id_as_string($l_row['parent']));
                        } else {
                            // Specific category parent
                            $parentCategoryTitle = isys_application::instance()->container->get('language')
                                ->get($l_dao->get_cats_name_by_id_as_string($l_row['parent']));
                        }

                        if (!isset($parentCategories[$parentCategoryTitle]) && strpos(strtolower($parentCategoryTitle), $l_filter) !== false) {
                            $categories[$parentCategoryTitle] = true;
                            $l_return[] = '<li data-category="' . $parentCategoryTitle . '">' . $parentCategoryTitle . '</li>';
                        }
                    }
                    $l_prop_title = isys_application::instance()->container->get('language')
                        ->get($l_row['title']);

                    // Filter property
                    if (strpos(strtolower($l_prop_title), $l_filter) !== false) {
                        $l_cat_title = $l_dao->get_category_by_const_as_string($l_row['const']);

                        $l_return[] = '<li data-category="' . $l_cat_title . '">' . $l_cat_title . ' &raquo; ' . $l_prop_title . '</li>';
                    }
                }
                $l_return = array_unique($l_return);
                break;
        }

        echo "<ul>" . implode('', $l_return) . "</ul>";
        $this->_die();
    }
}
