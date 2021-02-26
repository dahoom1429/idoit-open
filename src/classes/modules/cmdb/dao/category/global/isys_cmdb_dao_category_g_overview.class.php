<?php

/**
 * i-doit
 *
 * DAO: global category for object overviews.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_overview extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'overview';

    private $m_cat_prefix = "g";

    /**
     * Array with all classes, which could not be saved because of failed "validate_user_data()".
     *
     * @var  array
     */
    private $m_invalid_classes = [];

    /**
     * Holds the categoriy dao-result(s)
     *
     * @var  array
     */
    private $m_result;

    /**
     * Holds the specific category of the current object.
     *
     * @var  isys_component_dao_result
     */
    private $m_specific;

    /**
     * Return Category Data
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_catg_overview_list " . "INNER JOIN isys_obj " . "ON isys_catg_overview_list__isys_obj__id = isys_obj__id " . "WHERE TRUE ";

        $l_sql .= $p_condition;

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= " AND (isys_catg_overview_list__id = " . $this->convert_sql_id($p_catg_list_id) . ")";
        }

        if ($p_status !== null) {
            $l_sql .= " AND (isys_catg_overview_list__status = '{$p_status}')";
        }

        return $this->retrieve($l_sql . ";");
    }

    /**
     * Creates the condition to the object table
     *
     * @param int|array $p_obj_id
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                $l_sql = ' AND (isys_catg_overview_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_catg_overview_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [];
    }

    /**
     * Validate function for the overview
     *
     * @return bool
     * @throws isys_exception_cmdb
     */
    public function validate_user_data()
    {
        $l_global_categories = $_POST["g_cat_id"];
        $l_specific_categories = $_POST["g_cats_id"];
        $l_custom_categories = $_POST["g_cat_custom_id"];
        $l_object_id = $_GET[C__CMDB__GET__OBJECT];

        $l_valid = true;
        $l_ar_rules = $l_rules = $l_validation = [];
        $l_category_types = [
            C__CMDB__CATEGORY__TYPE_GLOBAL   => [],
            C__CMDB__CATEGORY__TYPE_SPECIFIC => [],
            C__CMDB__CATEGORY__TYPE_CUSTOM   => []
        ];

        /* Check if post data is valid */
        if (is_array($l_global_categories)) {
            foreach ($l_global_categories as $l_cat) {
                $l_category_types[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_cat] = true;
            }
        }

        if (is_array($l_specific_categories)) {
            foreach ($l_specific_categories as $l_cat) {
                $l_category_types[C__CMDB__CATEGORY__TYPE_SPECIFIC][$l_cat] = true;
            }
        }

        if (is_array($l_custom_categories)) {
            foreach ($l_custom_categories as $l_cat) {
                $l_category_types[C__CMDB__CATEGORY__TYPE_CUSTOM][$l_cat] = true;
            }
        }

        foreach ($l_category_types as $l_cattype => $l_categories) {
            if (is_countable($l_categories) && count($l_categories) === 0) {
                continue;
            }

            $l_dist = new isys_cmdb_dao_distributor($this->m_db, $l_object_id, $l_cattype, null, $l_categories);
            foreach ($l_categories as $l_cat_id => $l_ok) {
                if ($l_cattype == C__CMDB__CATEGORY__TYPE_GLOBAL) {
                    $l_cat_dao = $this->get_dao_by_catg_id($l_cat_id);
                    $l_cat_type_string = 'g';
                } elseif ($l_cattype == C__CMDB__CATEGORY__TYPE_CUSTOM) {
                    $l_cat_dao = $this->get_dao_by_catg_custom_id($l_cat_id);
                    $l_cat_type_string = 'g_custom';
                } else {
                    $l_cat_dao = $this->get_dao_by_cats_id($l_cat_id);
                    $l_cat_type_string = 's';
                }

                $l_category_cont = $l_dist->get_cat_const_by_id($l_cat_id, $l_cat_type_string);

                // Get instance of current category and handle the exception if error occurs.
                $l_current_cat = $l_dist->get_category($l_cat_id);

                if (!$l_current_cat) {
                    throw new isys_exception_cmdb("Could not get category DAO for category-id: " . $l_cat_id);
                }

                // Initialize category.
                $l_cat_dao->init($l_current_cat->get_result());

                if (!$l_cat_dao->validate_user_data()) {
                    $this->m_invalid_classes[] = get_class($l_cat_dao);
                    $l_valid = false;
                    $l_validation[$l_category_cont] = false;
                } else {
                    $l_validation[$l_category_cont] = true;
                }

                $l_ar_rules[$l_category_cont] = $l_cat_dao->get_additional_rules();
            }
        }

        if (count($l_ar_rules) > 0) {
            foreach ($l_ar_rules as $l_value) {
                if (is_array($l_value)) {
                    foreach ($l_value as $l_key => $l_rule) {
                        $l_rules[$l_key] = $l_rule;
                    }
                }
            }
        }

        if (count($l_rules) > 0) {
            $this->set_additional_rules($l_rules);
        }

        $this->set_validation($l_valid);

        if (count($l_validation) > 0) {
            isys_application::instance()->template->assign("cat_validation", $l_validation);
        }

        return $l_valid;
    }

    /**
     * Get overview categories.
     *
     * @param   integer $p_obj_type
     * @param   integer $p_category_type
     * @param   integer $p_rec_status
     * @param   boolean $p_overview_only
     * @param   boolean $p_cats_childs
     *
     * @return  isys_component_dao_result
     */
    public function get_categories(
        $p_obj_type,
        $p_category_type = C__CMDB__CATEGORY__TYPE_GLOBAL,
        $p_rec_status = C__RECORD_STATUS__NORMAL,
        $p_overview_only = true,
        $p_cats_childs = false
    ) {
        /**
         * @var $l_dao isys_cmdb_dao_object_type
         */
        $l_dao = isys_cmdb_dao_object_type::factory($this->get_database_component());

        if (!isset($this->m_result[$p_category_type])) {
            switch ($p_category_type) {
                case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                    $this->m_cat_prefix = "s";

                    return $this->m_specific = $l_dao->get_specific_category($p_obj_type, $p_rec_status, null, $p_cats_childs);

                case C__CMDB__CATEGORY__TYPE_CUSTOM:
                    if (class_exists('isys_custom_fields_dao')) {
                        $l_cf_module = new isys_custom_fields_dao($this->m_db);
                        $this->m_result[$p_category_type] = $l_cf_module->get_assignments(null, $p_obj_type, $p_overview_only);
                    }
                    break;

                default:
                case C__CMDB__CATEGORY__TYPE_GLOBAL:
                    $this->m_result[$p_category_type] = $this->get_catg_by_obj_type($p_obj_type, $p_rec_status, $p_overview_only);
            }
        }

        return $this->m_result[$p_category_type];
    }

    /**
     * Retrieves a specific category data as array.
     *
     * @param   integer $p_obj_type
     * @param   integer $p_obj_id
     *
     * @return  array
     */
    public function get_category_specific($p_obj_type, $p_obj_id)
    {
        return $this->get_categories_as_array($p_obj_type, $p_obj_id, C__CMDB__CATEGORY__TYPE_SPECIFIC);
    }

    /**
     * Return overview categories as array
     *
     * @param   integer $p_obj_type
     * @param   integer $p_obj_id
     * @param   integer $p_category_type
     * @param   integer $p_rec_status
     * @param   boolean $p_overview_only
     * @param   boolean $p_cats_childs
     *
     * @return  array
     */
    public function get_categories_as_array(
        $p_obj_type,
        $p_obj_id,
        $p_category_type = C__CMDB__CATEGORY__TYPE_GLOBAL,
        $p_rec_status = C__RECORD_STATUS__NORMAL,
        $p_overview_only = true,
        $p_cats_childs = false
    ) {
        // Initialize.
        $l_array = [];

        // Assign global category if needed.
        $this->assign_global_category($p_obj_type);

        $language = isys_application::instance()->container->get('language');

        $l_categories = $this->get_categories($p_obj_type, $p_category_type, $p_rec_status, $p_overview_only, $p_cats_childs);

        if ($this->m_cat_prefix) {
            $l_cat_prefix = $this->m_cat_prefix;
        }

        if (!is_null($l_categories) && $l_categories->num_rows() > 0) {
            $l_auth = isys_auth_cmdb_categories::instance();
            $l_allowed_categories = $l_auth->get_allowed_categories($p_obj_id);

            while ($l_row = $l_categories->get_row()) {
                if ($p_overview_only && ($l_allowed_categories === false ||
                        (is_array($l_allowed_categories) && !in_array($l_row['isysgui_cat' . $l_cat_prefix . '__const'], $l_allowed_categories)))) {
                    continue;
                }

                if (!$l_auth->has_rights_in_obj_and_category(isys_auth::VIEW, $p_obj_id, $l_row['isysgui_cat' . $l_cat_prefix . '__const'])) {
                    continue;
                }

                $l_category = $l_row["isysgui_cat" . $l_cat_prefix . "__id"];

                if ($l_category) {
                    $l_distcat[$l_category] = true;

                    $l_array[$l_category] = [
                        "id"             => $l_row["isysgui_cat" . $l_cat_prefix . "__id"],
                        "type"           => $l_row["isysgui_cat" . $l_cat_prefix . "__type"],
                        "title"          => $language->get($l_row["isysgui_cat" . $l_cat_prefix . "__title"]),
                        "sort"           => $l_row["isys_obj_type_2_isysgui_cat" . $l_cat_prefix . "_overview__sort"],
                        "source_table"   => $l_row["isysgui_cat" . $l_cat_prefix . "__source_table"],
                        "const"          => $l_row["isysgui_cat" . $l_cat_prefix . "__const"],
                        "obj_type_const" => $l_row["isys_obj_type__const"],
                        "multivalued"    => $l_row["isysgui_cat" . $l_cat_prefix . "__list_multi_value"],
                        "overview"       => $l_row["isysgui_cat" . $l_cat_prefix . "__overview"],
                        "image"          => $l_row["isys_obj_type__obj_img_name"],
                        "cats_id"        => $l_row["isys_obj_type__isysgui_cats__id"]
                    ];
                }
            }

            if (isset($l_array) && is_array($l_array)) {
                try {
                    $l_dist = new isys_cmdb_dao_distributor($this->m_db, $p_obj_id, $p_category_type, null, isset($l_distcat) ? $l_distcat : null);

                    if (isset($l_distcat) && is_array($l_distcat)) {
                        foreach ($l_distcat as $l_category => $l_val) {
                            $l_cat_data = $l_dist->get_category($l_category);

                            if (is_object($l_cat_data)) {
                                $l_array[$l_category]["dao"] = $l_cat_data;
                                $l_array[$l_category]["category"] = $l_cat_data->get_ui();
                            }
                        }
                    }
                } catch (Exception $e) {
                    throw $e;
                }
            }

            return $l_array;
        }

        return [];
    }

    /**
     * Return custom categories as array
     *
     * @param int $p_obj_type
     * @param int $p_obj_id
     * @param int $p_category_type
     * @param int $p_rec_status
     *
     * @return array
     */
    public function get_custom_categories_as_array(
        $p_obj_type,
        $p_obj_id,
        $p_category_type = C__CMDB__CATEGORY__TYPE_CUSTOM,
        $p_rec_status = C__RECORD_STATUS__NORMAL,
        $p_overview_only = true,
        $p_translate = true
    ) {
        $l_categories = $this->get_categories($p_obj_type, $p_category_type, $p_rec_status, $p_overview_only);

        if (!is_null($l_categories) && $l_categories->num_rows() > 0) {
            $l_auth = isys_auth_cmdb_categories::instance();
            $l_allowed_categories = $l_auth->get_allowed_categories($p_obj_id);
            $l_cat_prefix = "g_custom";
            $l_array = [];

            while ($l_row = $l_categories->get_row()) {
                if ($p_overview_only && ($l_allowed_categories === false ||
                        (is_array($l_allowed_categories) && !in_array($l_row['isysgui_cat' . $l_cat_prefix . '__const'], $l_allowed_categories)))) {
                    continue;
                }

                if (!$l_auth->has_rights_in_obj_and_category(isys_auth::VIEW, $p_obj_id, $l_row['isysgui_cat' . $l_cat_prefix . '__const'])) {
                    continue;
                }

                $l_category = $l_row["isysgui_cat" . $l_cat_prefix . "__id"];

                if ($l_category) {
                    $l_distcat[$l_category] = true;

                    $l_array[$l_category] = [
                        "id"             => $l_row["isysgui_cat" . $l_cat_prefix . "__id"],
                        "title"          => ($p_translate) ? isys_application::instance()->container->get('language')
                            ->get($l_row["isysgui_cat" . $l_cat_prefix . "__title"]) : $l_row["isysgui_cat" . $l_cat_prefix . "__title"],
                        "sort"           => ($l_row['isys_obj_type_2_isysgui_cat' . $l_cat_prefix . '_overview__sort']) ?: 0,
                        "source_table"   => "isys_catg_custom_fields_list",
                        "const"          => $l_row["isysgui_cat" . $l_cat_prefix . "__const"],
                        "obj_type_const" => $l_row["isys_obj_type__const"],
                        "multivalued"    => $l_row["isysgui_cat" . $l_cat_prefix . "__list_multi_value"],
                        "overview"       => ($l_row['isys_obj_type_2_isysgui_cat' . $l_cat_prefix . '_overview__sort'] !== null) ? 1 : 0,
                        "image"          => $l_row["isys_obj_type__obj_img_name"],
                        "cats_id"        => $l_row["isys_obj_type__isysgui_cats__id"]
                    ];
                }
            }

            if (is_array($l_array) && count($l_array)) {
                try {
                    if (class_exists('isys_cmdb_dao_category_g_custom_fields')) {
                        foreach ($l_array as $l_category => $l_val) {
                            $l_cat_data = new isys_cmdb_dao_category_g_custom_fields($this->get_database_component());
                            $l_ui = $l_cat_data->get_ui();

                            if (method_exists($l_cat_data, 'set_catg_custom_id')) {
                                $l_cat_data->set_catg_custom_id($l_category);
                            }

                            $l_array[$l_category]["dao"] = $l_cat_data;
                            $l_array[$l_category]["category"] = $l_ui;
                        }
                    }
                } catch (Exception $e) {
                    throw $e;
                }
            }

            return $l_array;
        }

        return [];
    }

    /**
     * Returns custom category dao with the correct category
     *
     * @param $p_catg_custom_id
     *
     * @return $this
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_dao_by_catg_custom_id($p_catg_custom_id)
    {
        /**
         * @var $l_dao isys_cmdb_dao_category_g_custom_fields
         */
        $l_dao = isys_cmdb_dao_category_g_custom_fields::instance($this->get_database_component());
        $l_dao->set_category_type(C__CMDB__CATEGORY__TYPE_CUSTOM);

        return $l_dao->set_catg_custom_id($p_catg_custom_id);
    }

    /**
     * Return dao object by isysgui_catg__id
     *
     * @param int $p_catg__id
     */
    public function get_dao_by_catg_id($p_catg__id)
    {
        $l_dao = $this->get_all_catg($p_catg__id);

        if (is_object($l_dao)) {
            $l_row = $l_dao->get_row();
            $l_class = $l_row["isysgui_catg__class_name"];

            if (class_exists($l_class)) {
                $l_dao_object = new $l_class($this->m_db);

                return $l_dao_object;
            }
        }

        return false;
    }

    /**
     * Return dao object by isysgui_catg__id
     *
     * @param int $p_catg__id
     */
    public function get_dao_by_cats_id($p_cats__id)
    {
        /**
         * @var $l_dao isys_cmdb_dao_object_type
         */
        $l_dao = isys_cmdb_dao_object_type::factory($this->get_database_component());

        $l_cats = $l_dao->get_specific_category(null, C__RECORD_STATUS__NORMAL, $p_cats__id);

        if (is_object($l_cats)) {
            $l_row = $l_cats->get_row();
            $l_class = $l_row["isysgui_cats__class_name"];

            if (class_exists($l_class)) {
                $l_dao_object = new $l_class($this->m_db);

                return $l_dao_object;
            }
        }

        return false;
    }

    /**
     * Save categories.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_intOldRecStatus
     *
     * @return  null
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus = C__RECORD_STATUS__BIRTH)
    {
        $l_object_id = $_GET[C__CMDB__GET__OBJECT];

        // First specific category
        if (isset($_POST["g_cats_id"])) {
            $this->save($p_cat_level, $p_intOldRecStatus, $_POST["g_cats_id"], $l_object_id, C__CMDB__CATEGORY__TYPE_SPECIFIC);
        }
        // Second global category
        if (isset($_POST["g_cat_id"])) {
            $this->save($p_cat_level, $p_intOldRecStatus, $_POST["g_cat_id"], $l_object_id, C__CMDB__CATEGORY__TYPE_GLOBAL);
        }
        if (isset($_POST["g_cat_custom_id"])) {
            $this->save($p_cat_level, $p_intOldRecStatus, $_POST["g_cat_custom_id"], $l_object_id, C__CMDB__CATEGORY__TYPE_CUSTOM);
        }

        return null;
    }

    /**
     * Save either specifor or global category
     *
     * @param   integer $p_cat_level
     * @param   integer & $p_intOldRecStatus
     * @param   array   $p_categories
     * @param   integer $p_object_id
     * @param   integer $p_cat_type
     *
     * @throws  isys_exception_cmdb
     */
    public function save($p_cat_level, &$p_intOldRecStatus, $p_categories, $p_object_id, $p_cat_type = C__CMDB__CATEGORY__TYPE_GLOBAL)
    {
        // Registry for categories skipped because of insufficient rights
        $skippedCategories = [];

        $l_catmeta = $this->nav_get_current_category_data();
        $l_action_update = new isys_cmdb_action_category_update();

        /* Get status of object */
        $l_status = $this->get_object_status_by_id($p_object_id);

        try {
            // Iterate through savable categories and save data of them.
            if (is_array($p_categories)) {
                foreach ($p_categories as $l_cat) {
                    $l_catg_const = null;

                    switch ($p_cat_type) {
                        case C__CMDB__CATEGORY__TYPE_GLOBAL:
                            $l_catg_title = $this->get_catg_name_by_id_as_string($l_cat);
                            $l_catg_const = $this->get_cat_const_by_id($l_cat, 'g');
                            break;

                        case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                            $l_catg_title = $this->get_cats_name_by_id_as_string($l_cat);
                            $l_catg_const = $this->get_cat_const_by_id($l_cat, 's');
                            break;

                        case C__CMDB__CATEGORY__TYPE_CUSTOM:
                            $l_catg_title = $this->get_cat_custom_name_by_id_as_string($l_cat);
                            $l_catg_const = $this->get_cat_const_by_id($l_cat, 'g_custom');
                            break;
                    }

                    if (!$l_catg_const) {
                        continue;
                    }

                    // Get distributor dao for specified category $l_cat.
                    $l_dist = new isys_cmdb_dao_distributor($this->m_db, $p_object_id, $p_cat_type, null, [$l_cat => true]);

                    if ($l_dist && $l_dist->count() > 0) {
                        // Get instance of current category and handle the exception if error occurs.
                        $l_current_cat = $l_dist->get_category($l_cat);

                        // Set custom category id in dao for custom categories
                        if ($p_cat_type === C__CMDB__CATEGORY__TYPE_CUSTOM && method_exists($l_current_cat, 'set_catg_custom_id')) {
                            $l_current_cat->set_catg_custom_id($l_cat);
                            $l_gui_cat_id = $l_cat;
                        } else {
                            $l_gui_cat_id = $l_current_cat->get_category_id();
                        }

                        // Retrieve the almighty general data.
                        $l_catdata = $l_current_cat->get_general_data();

                        // Check for edit right
                        $rightEvaluation = isys_auth_cmdb::instance()
                            ->has_rights_in_obj_and_category(isys_auth::EDIT, $p_object_id, $l_catg_const);

                        // Check for create right if no entry exists
                        if ($l_catdata === null) {
                            $rightEvaluation = $rightEvaluation || isys_auth_cmdb::instance()
                                    ->is_allowed_to(isys_auth_cmdb::CREATE, 'CATEGORY/' . $l_catg_const);
                        }

                        if (!$rightEvaluation) {
                            // Add category to registry
                            $skippedCategories[] = isys_application::instance()->container->get('language')->get($l_catg_title);

                            continue;
                        }

                        // Retrieve changed Data
                        $l_changes = $l_action_update->format_changes($_POST, $l_current_cat, true);
                        $l_changed = isys_cmdb_dao::get_changed_props();

                        $l_changes_compressed = serialize($l_changes);

                        if (!$l_current_cat) {
                            throw new isys_exception_cmdb("Could not get category DAO for category-id: " . $l_cat);
                        }

                        // Initialize category.
                        $l_current_cat->init($l_current_cat->get_result());

                        // Ask CMDB for category entry count.
                        $l_catcount = $this->cat_count_by_status($p_cat_type, $l_gui_cat_id, $l_catdata["isys_obj__id"]);

                        $_POST = $l_current_cat->sanitize_post_data();

                        // Prepare some variables to check, if we can skip this category.
                        $daoClassName = get_class($l_current_cat);
                        $personCommentaryConstant = 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC .
                            defined_or_default('C__CATS__PERSON_GROUP', 'C__CATS__PERSON_GROUP');
                        // @see  ID-6693  Skip the "continue" if we are saving 'isys_cmdb_dao_category_s_layer2_net', because i-doit might not recognize changes.

                        // If we find no changes for this category, we don't need to create/update.
                        // @todo: Find a more suitable solution for ID-5140 instead of whitelisting commentary field!
                        if (!isset($l_changed[$personCommentaryConstant]) && !isset($l_changed[$daoClassName]) &&
                            !in_array($daoClassName, ['isys_cmdb_dao_category_g_global', 'isys_cmdb_dao_category_s_layer2_net'], true)) {
                            continue;
                        }

                        /**
                         * @todo Re-calling get_general_data does not work anymore because
                         *       the resultset is always empty.
                         *       It is needed to reinitialize the distributor now.
                         *       Which means a lot of overhead. This has to be fixed !
                         */
                        $l_dist = new isys_cmdb_dao_distributor($this->m_db, $p_object_id, $p_cat_type, null, [$l_cat => true]);

                        $l_current_cat = $l_dist->get_category($l_cat);

                        if ($p_cat_type === C__CMDB__CATEGORY__TYPE_CUSTOM && method_exists($l_current_cat, 'set_catg_custom_id')) {
                            $l_current_cat->set_catg_custom_id($l_cat);
                        }

                        if (is_countable($l_changes) && count($l_changes) && isset($l_catg_title)) {
                            $l_current_cat->logbook_update('C__LOGBOOK_EVENT__CATEGORY_CHANGED', $l_catg_title, $l_changes_compressed);
                        }
                        if ($l_current_cat->get_object_browser_category() === true && ($l_objBrowser_key = $l_current_cat->get_object_browser_property()) !== '' &&
                            method_exists($l_current_cat, 'attachObjects')) {
                            $l_post_key = $l_current_cat->get_property_by_key($l_objBrowser_key)[C__PROPERTY__UI][C__PROPERTY__UI__ID] ?: '';

                            if (isset($_POST[$l_post_key . '__HIDDEN'])) {
                                if (isys_format_json::is_json_array($_POST[$l_post_key . '__HIDDEN'])) {
                                    $l_objects = isys_format_json::decode($_POST[$l_post_key . '__HIDDEN']);
                                } else {
                                    $l_objects = explode(',', $_POST[$l_post_key . '__HIDDEN']);
                                }
                                $l_current_cat->attachObjects((int)$_GET[C__CMDB__GET__OBJECT], $l_objects);
                            }
                        } else {
                            // Save the category.
                            if (method_exists($l_current_cat, "save_element")) {
                                $l_category_id = null;
                                if ($l_catcount === 0) {
                                    // Create the categoryentry, because its not existing.
                                    if (method_exists($l_current_cat, "create_connector")) {
                                        $l_category_id = $l_current_cat->create_connector($l_current_cat->get_table(), $p_object_id);
                                    }
                                }

                                $l_saved = $l_current_cat->save_element($p_cat_level, $l_category_id, $l_category_id ? false : true);
                            } else {
                                $l_current_cat->save_user_data(false);
                            }
                        }

                        // Emit category signal (afterCategoryEntrySave).
                        isys_component_signalcollection::get_instance()
                            ->emit("mod.cmdb.afterCategoryEntrySave", $l_current_cat, $p_cat_level, $l_saved, $p_object_id, $_POST, $l_changes);
                    }
                }
            }

            if (count($skippedCategories)) {
                isys_notify::warning(isys_application::instance()->container->get('language')->get(
                    'LC__AUTH__AUTH_EXCEPTION__MISSING_RIGHT_FOR_EDIT_CATEGORIES_IN_OVERVIEW',
                    ['<ul><li>' . implode('</li><li>', $skippedCategories) . '</li></ul>']
                ));
            }
        } catch (Exception $e) {
            throw new isys_exception_cmdb($e->getMessage());
        }
    }

    /**
     * Assign category global to overview page #4566.
     *
     * @param   integer $p_objecttype_id
     *
     * @return  boolean
     * @author Selcuk Kekec <skekec@i-doit.org>
     */
    public function assign_global_category($p_objecttype_id)
    {
        $l_sql = "SELECT isys_obj_type__id FROM isys_obj_type_2_isysgui_catg_overview
			WHERE isys_obj_type__id = " . $this->convert_sql_id($p_objecttype_id) . "
			AND isysgui_catg__id = " . defined_or_default('C__CATG__GLOBAL') . ";";

        if (!$this->retrieve($l_sql)
            ->num_rows()) {
            $l_sql = "INSERT INTO isys_obj_type_2_isysgui_catg_overview VALUES(" . $this->convert_sql_id($p_objecttype_id) . ", " . $this->convert_sql_id(defined_or_default('C__CATG__GLOBAL')) .
                ", 0)";

            return ($this->update($l_sql) && $this->apply_update());
        }

        return true;
    }

    /**
     * @param string $objectTypeConstant
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function getOrderedOverviewCategories(string $objectTypeConstant): isys_component_dao_result
    {
        $sql = 'SELECT gui.isysgui_catg__const AS constant, ov.isys_obj_type_2_isysgui_catg_overview__sort AS sort
            FROM isys_obj_type_2_isysgui_catg_overview AS ov
            INNER JOIN isys_obj_type AS ot ON ot.isys_obj_type__id = ov.isys_obj_type__id 
            INNER JOIN isysgui_catg AS gui ON gui.isysgui_catg__id = ov.isysgui_catg__id  
            WHERE ot.isys_obj_type__const = ' . $this->convert_sql_text($objectTypeConstant) . '
            
            UNION
            
            SELECT gui.isysgui_catg_custom__const AS constant, ov.isys_obj_type_2_isysgui_catg_custom_overview__sort AS sort
            FROM isys_obj_type_2_isysgui_catg_custom_overview AS ov
            INNER JOIN isys_obj_type AS ot ON ot.isys_obj_type__id = ov.isys_obj_type__id 
            INNER JOIN isysgui_catg_custom AS gui ON gui.isysgui_catg_custom__id = ov.isysgui_catg_custom__id 
            WHERE ot.isys_obj_type__const = ' . $this->convert_sql_text($objectTypeConstant) . ';';

        return $this->retrieve($sql);
    }

    /**
     * @return string
     */
    public function get_invalid_classes()
    {
        return implode(', ', $this->m_invalid_classes);
    }
}
