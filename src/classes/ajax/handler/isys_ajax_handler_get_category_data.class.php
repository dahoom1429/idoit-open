<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-8
 */
class isys_ajax_handler_get_category_data extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @global  isys_component_database $g_comp_database
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [];

        switch ($_GET['func']) {
            case 'get_data':
                $l_return = $this->get_data();
                break;

            case 'get_properties_by_database':
                $l_return = $this->get_properties_by_database();
                break;

            case 'get_filtered_properties_by_database':
                $l_return = $this->get_filtered_properties_by_database();
                break;
            case 'get_property_keys_and_names':
                $l_return = $this->get_property_keys_and_names();
                break;

            case 'is_property_sortable':
                $l_return = $this->is_property_sortable();
                break;

            case 'get_categories':
                $l_return = $this->get_categories();
                break;

            case 'format_preselection':
                $l_return = $this->format_preselection();
                break;

            case 'get_dao_classes_by_constants':
                try {
                    $l_return = [
                        'success' => true,
                        'data'    => $this->getDaoClassesByConstants(explode(',', $_POST['constants'])),
                        'message' => ''
                    ];
                } catch (Exception $e) {
                    $l_return = [
                        'success' => false,
                        'data'    => null,
                        'message' => $e->getMessage()
                    ];
                }
                break;
        }

        echo isys_format_json::encode($l_return);
        $this->_die();
    }

    /**
     * Rebuilds selected properties to a readable format for the property selector
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function format_preselection()
    {
        global $g_comp_database;
        $l_dao = new isys_smarty_plugin_f_property_selector($g_comp_database);

        return $l_dao->handle_preselection(isys_format_json::decode($_POST['data']));
    }

    /**
     * Get global / specific categories for the property selector
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function get_categories()
    {
        global $g_comp_database;
        $l_dao = new isys_smarty_plugin_f_property_selector($g_comp_database);
        $l_return = [
            'catg'        => $l_dao->get_catg($_POST['provides'], $_POST['dynamic_properties'], $_POST['consider_rights']),
            'cats'        => $l_dao->get_cats($_POST['provides'], $_POST['dynamic_properties'], $_POST['consider_rights']),
            'catg_custom' => $l_dao->get_catg_custom($_POST['provides'], $_POST['dynamic_properties'], $_POST['consider_rights'])
        ];

        return $l_return;
    }

    /**
     * Get-data method.
     *
     * It is possible to pass the following parameters per post:
     * - catsID (int)
     * - catgID (int)
     * - objID (int)
     * - condition (string)
     *
     * @return array
     * @throws isys_exception_database
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_data()
    {
        $database = isys_application::instance()->container->get('database');
        $language = isys_application::instance()->container->get('language');

        $l_dao = new isys_cmdb_dao($database);

        $l_return = [];

        // We look, if we are selecting a specific or global category.
        if (isset($_POST[C__CMDB__GET__CATS])) {
            $l_get_param = C__CMDB__GET__CATS;
            $l_cat_suffix = 's';
        } else {
            $l_get_param = C__CMDB__GET__CATG;
            $l_cat_suffix = 'g';
        }

        $l_cat_id = $_POST[$l_get_param];
        $l_object_id = (int)$_POST[C__CMDB__GET__OBJECT];
        $l_condition = $_POST['condition'];

        // Get category info.
        $l_isysgui = $l_dao->get_isysgui('isysgui_cat' . $l_cat_suffix, $l_cat_id)
            ->__to_array();

        // Check class and instantiate it.
        if (class_exists($l_isysgui['isysgui_cat' . $l_cat_suffix . '__class_name'])) {
            /**
             * IDE typehinting.
             *
             * @var  $l_cat  isys_cmdb_dao_category
             */
            if (($l_cat = new $l_isysgui['isysgui_cat' . $l_cat_suffix . '__class_name']($database))) {
                // Check if the get_data method exists.
                if (method_exists($l_cat, 'get_data')) {
                    if (isset($l_condition)) {
                        $l_catdata = $l_cat->get_data(null, null, $l_condition);
                    } else {
                        $l_catdata = $l_cat->get_data(null, $l_object_id);
                    }

                    if ($l_catdata->num_rows() > 0) {
                        while ($l_row = $l_catdata->get_row()) {
                            $l_return[] = array_map(function ($value) use ($language) { return $language->get($value); }, $l_row);
                        }
                    }
                }
            }
        }

        return $l_return;
    }

    /**
     * Retrieve the properties by the isys_property_2_cat table.
     *
     * @return array
     * @throws Exception
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_properties_by_database()
    {
        $l_dao = new isys_cmdb_dao_category_property($this->m_database_component);

        $l_return = [];
        $l_dynamic_properties = $_POST['dynamic_properties'];
        $l_allowed_prop_types = explode(',', $_POST['allowed_prop_types']);
        $l_consider_rights = ($_POST['consider_rights'] == 'true') ? true : false;
        $l_replace_dynamic_properties = $_POST['replace_dynamic_properties'];

        $l_res = $l_dao->retrieve_properties(null, null, null, $_POST['provide'], 'AND isys_property_2_cat__cat_const = ' . $l_dao->convert_sql_text($_POST['cat_const']),
            $l_dynamic_properties);

        $l_keys = [];

        while ($l_row = $l_res->get_row()) {
            $l_cat_dao = $l_dao->get_dao_instance($l_row['class'], ($l_row['catg_custom'] ?: null));
            $l_properties = array_merge($l_cat_dao->get_properties(), $l_cat_dao->get_dynamic_properties());
            $l_property = $l_properties[$l_row['key']];
            $l_property_type = $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE];
            $l_property_type_addition = (($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['multiselection'] ||
                $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__DIALOG_LIST) ? 1 : 0);

            if (is_countable($l_properties) && count($l_properties) > 1) {
                $l_prop_count = 0;
                foreach ($l_properties AS $l_key => $l_prop) {
                    if ($l_prop[C__PROPERTY__PROVIDES][$_POST['provide']] && $l_key != 'description') {
                        $l_prop_count++;
                    }
                }
            } else {
                $l_prop_count = 1;
            }

            if ($l_prop_count === 1 && $l_property_type_addition === 1) {
                $l_property_type_addition = 0;
            }

            // This can be used to display only types like "text" or "dialog", ...
            if (is_countable($l_allowed_prop_types) && count($l_allowed_prop_types) > 0 && !empty($l_allowed_prop_types[0]) && $l_consider_rights && !in_array($l_property_type, $l_allowed_prop_types)) {
                continue;
            }

            // Also skip the "HR" and "HTML" fields of custom categories.
            if ($_POST['cat_type'] == 'g_custom' && (strpos($l_row['key'], 'hr_c_') === 0 || strpos($l_row['key'], 'html_c_') === 0)) {
                continue;
            }

            if ($l_replace_dynamic_properties && $l_row['type'] == C__PROPERTY_TYPE__DYNAMIC) {
                $l_search_key = substr($l_row['key'], 1);
                if (isset($l_keys[$l_search_key])) {
                    unset($l_return[$l_keys[$l_search_key]]);
                }
            }

            $l_indexed = (int)$l_cat_dao->get_property_by_key($l_row['key'])[C__PROPERTY__DATA][C__PROPERTY__DATA__INDEX];

            $l_return[$l_row['key'] . '#' . $l_row['id'] . '#' . $l_property_type . '#' . $l_property_type_addition . '#' .
            $l_indexed] = isys_application::instance()->container->get('language')
                ->get($l_row['title']);

            $l_keys[$l_row['key']] = $l_row['key'] . '#' . $l_row['id'] . '#' . $l_property_type . '#' . $l_property_type_addition . '#' . $l_indexed;
        }

        // Sort result
        asort($l_return);

        return $l_return;
    }

    /**
     * Retrieve and filter the properties by the isys_property_2_cat table.
     *
     * @return array
     * @throws Exception
     * @author Selcuk Kekec <skekec@i-doit.org>
     */
    protected function get_filtered_properties_by_database()
    {
        // Init
        $l_dao = new isys_cmdb_dao_category_property($this->m_database_component);
        $l_return = [];
        $l_filter = strtolower($_POST['filter']);
        $l_dynamic_properties = $_POST['dynamic_properties'];
        $l_allowed_prop_types = explode(',', $_POST['allowed_prop_types']);
        $l_consider_rights = ($_POST['consider_rights'] == 'true') ? true : false;
        $l_replace_dynamic_properties = $_POST['replace_dynamic_properties'];
        $l_obj_type_id = $_POST['obj_type_id'];
        $l_custom_fields = $_POST['custom_fields'];
        $l_condition = '';

        // Handling custom fields
        if ($l_custom_fields != true) {
            $l_condition = ' AND isys_property_2_cat__isysgui_catg_custom__id IS NULL';
        }

        // Create dao res
        $l_res = $l_dao->retrieve_properties(null, null, null, $_POST['provide'], $l_condition, $l_dynamic_properties);

        $l_keys = [];

        while ($l_row = $l_res->get_row()) {
            $l_cat_dao = $l_dao->get_dao_instance($l_row['class'], ($l_row['catg_custom'] ?: null));
            $l_properties = array_merge($l_cat_dao->get_properties(), $l_cat_dao->get_dynamic_properties());
            $l_property = $l_properties[$l_row['key']];
            $l_property_type = $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE];
            $l_property_type_addition = (($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['multiselection'] ||
                $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__DIALOG_LIST) ? 1 : 0);

            if ($l_obj_type_id > 0) {
                // Check if the found category is assigned to the given object-type.
                if ($l_row['catg'] > 0 && !$l_dao->objtype_is_catg_assigned($l_obj_type_id, $l_row['catg'])) {
                    continue;
                }

                if ($l_row['cats'] > 0 && !$l_dao->objtype_is_cats_assigned($l_obj_type_id, $l_row['cats'])) {
                    continue;
                }

                // Find something for custom categories.
                if ($l_custom_fields && $l_row['catg_custom'] > 0 && !$l_dao->objtype_is_catg_custom_assigned($l_obj_type_id, $l_row['catg_custom'])) {
                    continue;
                }
            }

            // This can be used to display only types like "text" or "dialog", ...
            if (is_countable($l_allowed_prop_types) && count($l_allowed_prop_types) > 0 && !empty($l_allowed_prop_types[0]) && $l_consider_rights && !in_array($l_property_type, $l_allowed_prop_types)) {
                continue;
            }

            // Also skip the "HR" and "HTML" fields of custom categories.
            if ($_POST['cat_type'] == 'g_custom' && (strpos($l_row['key'], 'hr_c_') === 0 || strpos($l_row['key'], 'html_c_') === 0)) {
                continue;
            }

            if ($l_replace_dynamic_properties && $l_row['type'] == C__PROPERTY_TYPE__DYNAMIC) {
                $l_search_key = substr($l_row['key'], 1);
                if (array_key_exists($l_search_key, $l_keys)) {
                    unset($l_return[$l_keys[$l_search_key]]);
                }
            }

            $l_prop_title = isys_application::instance()->container->get('language')
                ->get($l_row['title']);

            // Filter property
            if (strpos(strtolower($l_prop_title), $l_filter) !== false) {
                $l_cat_type = null;
                $l_cat_title = $l_dao->get_category_by_const_as_string($l_row['const']);

                // Detect category type
                if (isset($l_row['catg'])) {
                    $l_cat_type = 'g';
                } else if (isset($l_row['cats'])) {
                    $l_cat_type = 's';
                } else if (isset($l_row['catg_custom'])) {
                    $l_cat_type = 'g_custom';
                }

                // Add property to results
                $l_return[$l_row['key'] . '#' . $l_row['id'] . '#' . $l_property_type . '#' . $l_property_type_addition] = [
                    'title'     => $l_prop_title . ' <span class="removeable-addon">(' . $l_cat_title . ')</span>',
                    'cat_type'  => $l_cat_type,
                    'cat_const' => $l_row['const'],
                    'cat_title' => $l_cat_title,
                ];

                $l_keys[$l_row['key']] = $l_row['key'] . '#' . $l_row['id'] . '#' . $l_property_type . '#' . $l_property_type_addition;
            }

        }

        asort($l_return);

        return $l_return;
    }

    /**
     * Method for loading all property keys and their translated names by a given category-constant.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_property_keys_and_names()
    {
        $l_return = [];
        $l_props = $this->get_properties_by_database();

        foreach ($l_props as $l_prop_key => $l_prop_name) {
            if (!empty($l_prop_name)) {
                $l_return[] = $l_prop_name . ': "' . current(explode('#', $l_prop_key)) . '"';
            }
        }

        return $l_return;
    }

    /**
     * This method checks if a property is sortable or not
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function is_property_sortable()
    {
        $l_prop_id = $_POST['prop_id'];
        $l_dao = new isys_cmdb_dao_category_property($this->m_database_component);
        $l_prop_arr = $l_dao->retrieve_properties($l_prop_id, null, null, null, '', true)
            ->__to_array();
        $l_cat_dao = $l_dao->get_dao_instance($l_prop_arr['class'], ($l_prop_arr['catg_custom'] ?: null));
        $l_properties = array_merge($l_cat_dao->get_properties(), $l_cat_dao->get_dynamic_properties());
        $l_property = $l_properties[$l_prop_arr['key']];

        if ($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__INDEX] || $l_prop_arr['const'] == 'C__CATG__GLOBAL') {
            return true;
        }

        return false;
    }

    /**
     * @param array $constants
     *
     * @return array
     */
    protected function getDaoClassesByConstants(array $constants = [])
    {
        if (count($constants) === 0) {
            return [];
        }

        $return = [];
        $constants = array_unique($constants);
        $dao = isys_cmdb_dao::instance($this->m_database_component);

        foreach ($constants as $constant) {
            if (defined($constant)) {
                $return[$constant] = $dao->get_cat_by_const($constant)['class_name'];
            }
        }

        return $return;
    }
}
