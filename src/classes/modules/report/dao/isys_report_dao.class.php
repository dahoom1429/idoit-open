<?php

use idoit\Module\Report\SqlQuery\Placeholder\Placeholder;

/**
 * i-doit
 *
 * Template Module Dao.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_report_dao extends isys_module_dao
{
    protected static $m_instance = null;

    private $m_hidden_columns = [
        "isys_cats_person_list__user_pass" => true,
        "isys_mandator__db_host"           => true,
        "isys_mandator__db_user"           => true,
        "isys_mandator__db_pass"           => true,
        "isys_mandator__db_password"       => true,
        "isys_ldap__password"              => true,
        "isys_logbook__changes"            => true
    ];

    /**
     * @param $col
     *
     * @return bool
     */
    public function isHiddenColumn($col)
    {
        return isset($this->m_hidden_columns[$col]);
    }

    /**
     * @param string $title
     *
     * @return string
     * @throws isys_exception_database
     */
    public function generateConstant(string $title): string
    {
        if (empty($title)) {
            // If the title is empty, we use a random hash.
            $title = md5(microtime());
        }

        // We'd like a constant that is shorter than 32 characters (29 + 'C__'), so we shorten it (this is optional, since the DB field has a length of 255).
        if (mb_strlen($title) > 29) {
            $title = substr($title, 0, 29);
        }

        /*
         * Here we generate a (unique) constant for the reports by a given title.
         *
         * 1) We remove all characters that are not "constant" compatible (with help of `isys_helper_upload::prepare_filename`).
         * 2) We then replace all hyphens `-` with underscroes `_`.
         * 3) Now we replace all remaining "non word characters" with nothing.
         * 4) And finally append `C__` and transform the string to upper case.
         */
        $newConstant = 'C__' . strtoupper(preg_replace('/\W+/', '', str_replace('-', '_', isys_helper_upload::prepare_filename($title))));

        // Initial check if this constant has been used before.
        $sql = "SELECT isys_report__id AS id FROM isys_report WHERE isys_report__const = '{$newConstant}' LIMIT 1;";

        if ($this->retrieve($sql)->get_row_value('id') > 0) {
            $counter = 1;

            while ($this->retrieve("SELECT isys_report__id AS id FROM isys_report WHERE isys_report__const = '{$newConstant}{$counter}' LIMIT 1;")->get_row_value('id') > 0) {
                $counter ++;
            }

            $newConstant .= $counter;
        }

        return $newConstant;
    }

    /**
     * Method for creating a new report.
     *
     * @param  string  $p_title
     * @param  string  $p_description
     * @param  string  $p_query
     * @param  null    $deprecated
     * @param  boolean $p_standard
     * @param  null    $deprecated2
     * @param  integer $p_report_category_id
     * @param  string  $p_querybuilder_json
     * @param int      $compressedMultivalueResults
     * @param int      $showHtml
     * @param int      $keepDescriptionFormat
     *
     * @return mixed  boolean false or integer
     * @throws isys_exception_dao
     */
    public function createReport(
        $p_title,
        $p_description,
        $p_query,
        $deprecated = null,
        $p_standard = false,
        $deprecated2 = null,
        $p_report_category_id = null,
        $p_querybuilder_json = null,
        $compressedMultivalueResults = 1,
        $showHtml = 0,
        $keepDescriptionFormat = 0
    ) {
        $session = isys_application::instance()->container->get('session');

        $l_update = "INSERT INTO isys_report SET
			isys_report__title = " . $this->convert_sql_text($p_title) . ",
			isys_report__description = " . $this->convert_sql_text($p_description) . ",
			isys_report__query = " . $this->convert_sql_text($p_query) . ",
			isys_report__type = '" . ($p_standard ? 's' : 'c') . "',
			isys_report__datetime = NOW(),
			isys_report__last_edited = NOW(),
			isys_report__mandator = " . (int)$session->get_mandator_id() . ",
			isys_report__user = " . (int)$session->get_user_id() . ",
			isys_report__const = " . $this->convert_sql_text($this->generateConstant((string) $p_title)) . ",
			isys_report__isys_report_category__id = " . $this->convert_sql_id($p_report_category_id) . ",
			isys_report__querybuilder_data = " . $this->convert_sql_text($p_querybuilder_json) . ",
            isys_report__compressed_multivalue_results = " . $this->convert_sql_int($compressedMultivalueResults). ",
            isys_report__show_html = " . $this->convert_sql_int($showHtml). ",
            isys_report__keep_description_format = " . $this->convert_sql_int($keepDescriptionFormat). ",
			isys_report__user_specific = 0;";

        if ($this->update($l_update) && $this->apply_update()) {
            return $this->get_last_insert_id();
        }

        return false;
    }

    /**
     * Deletes an report.
     *
     * @param  integer $p_id
     *
     * @return boolean
     * @throws isys_exception_dao
     */
    public function deleteReport($p_id)
    {
        $l_update = "DELETE FROM isys_report WHERE isys_report__id = " . $this->convert_sql_id($p_id) . ";";

        return ($this->update($l_update) && $this->apply_update());
    }

    /**
     * Get data method. Unused.
     *
     * @return  null
     */
    public function get_data()
    {
        return null;
    }

    /**
     * Retrieve a single report.
     *
     * @param  integer $p_id
     *
     * @return array
     * @throws Exception
     */
    public function get_report($p_id)
    {
        $session = isys_application::instance()->container->get('session');

        $l_row = $this->retrieve("SELECT * FROM isys_report WHERE isys_report__id = " . (int)$p_id . ";")
            ->get_row();

        if (empty($l_row)) {
            return [];
        }

        $l_row['isys_report__query'] = $this->replacePlaceHolders($l_row['isys_report__query']);

        return $l_row;
    }

    /**
     * Method for retrieving all reports by type.
     * Types can be "C__REPORT__CUSTOM" or "C__REPORT__STANDARD".
     *
     * @param   integer $p_type
     * @param   array   $p_allowed_reports
     * @param null      $p_report_category
     * @param bool      $placeholderReports
     * @param bool      $asResource
     *
     * @return array|isys_component_dao_result
     */
    public function get_reports($p_type = null, $p_allowed_reports = null, $p_report_category = null, $placeholderReports = false, $asResource = true)
    {
        $lang = isys_application::instance()->container->get('language');
        $session = isys_application::instance()->container->get('session');

        $l_sql = "SELECT *, (CASE isys_report__user_specific WHEN 1 THEN '" . $lang->get('LC__REPORT__LIST__ONLY_YOU') . "' WHEN 0 THEN '" . $lang->get('LC__UNIVERSAL__GLOBAL') . "' END) AS 'user_specific',
				(CASE WHEN isys_report__querybuilder_data IS NULL OR isys_report__querybuilder_data = '' THEN '0' ELSE '1' END) AS 'with_qb',
				isys_report_category__title AS 'category_title',
				isys_report__category_report,
				isys_report__query
				FROM isys_report
				LEFT JOIN isys_report_category ON isys_report_category__id = isys_report__isys_report_category__id ";

        // This condition is needed to only display reports the user is allowed to see.
        if (is_array($p_allowed_reports) && count($p_allowed_reports) > 0) {
            $l_rights_condition = "isys_report__id IN (" . implode(',', $p_allowed_reports) . ") ";
        } elseif ($p_allowed_reports === false) {
            $l_rights_condition = "FALSE ";
        } else {
            $l_rights_condition = "TRUE ";
        }

        $l_condition = "WHERE " . $l_rights_condition . " ";

        if ($p_report_category !== null) {
            $l_condition .= "AND isys_report__isys_report_category__id = " . (int)$p_report_category . " ";
        }

        // Should only work for api add-on
        if ($placeholderReports === false && $asResource === true) {
            $l_condition .= "AND isys_report__category_report = 0";
        }

        $l_order = " ORDER BY isys_report__title ASC";

        if ($asResource) {
            return $this->retrieve($l_sql . $l_condition . $l_order . ";");
        }

        $reports = $this->retrieve($l_sql . $l_condition . $l_order . ";")
            ->__as_array();

        // Only allow placeholder which are internal
        if ($placeholderReports === false) {
            $reports = array_filter($reports, function (&$report) {
                return $this->hasQueryOnlyInternalPlaceholder($report['isys_report__query']);
            });
        }

        return $reports;
    }

    /**
     * @param  string  $p_query
     * @param  null    $deprecated1
     * @param  null    $deprecated2
     * @param  null    $deprecated3
     * @param  boolean $p_context_html
     *
     * @deprecated see idoit\Module\Report\Report::query
     * @return array
     * @throws Exception
     * @throws isys_exception_general
     */
    public function query($p_query, $deprecated1 = null, $deprecated2 = null, $deprecated3 = null, $p_context_html = true)
    {
        $p_query = $this->replacePlaceHolders($p_query);

        $l_listing = $l_groups = [];

        $p_query = trim($p_query);

        if (!empty($p_query)) {
            if ($this->validate_query($p_query)) {
                $l_db = isys_application::instance()->database;
                $l_result = $l_db->query($p_query);
                $l_num = $l_db->num_rows($l_result);
                $l_listing["num"] = $l_num;

                if ($l_num > 0) {
                    $l_memory = \idoit\Component\Helper\Memory::instance();
                    $l_first_row = true;
                    $dynamicCallbacksOnKey = $translatedKey = $l_callbacks = [];

                    while ($l_row = $l_db->fetch_row_assoc($l_result)) {
                        $l_origin_key = null;
                        $l_memory->outOfMemoryBreak();

                        if ($l_first_row) {
                            $l_first_row = false;
                            foreach ($l_row as $l_key => $l_row_value) {
                                $l_origin_key = $l_key;

                                if (strpos($l_key, 'DYNAMIC_PROPERTY') !== false) {
                                    $dynamicCallbacksOnKey[$l_key] = [];
                                    continue;
                                }

                                if (strpos($l_key, 'isys_cmdb_dao_category') === 0 || strpos($l_key, 'locales') === 0) {
                                    $l_key_arr = explode('::', $l_key);
                                    $l_key = array_pop($l_key_arr);

                                    // Add Callback only if the callback class exists
                                    // This increases performance by reducing class_exists calls for each row
                                    // .. also adding the category dao instance to the callback instead of retrieving it in each row
                                    if (class_exists($l_key_arr[0])) {
                                        $l_callbacks[$l_origin_key] = [
                                            call_user_func([
                                                $l_key_arr[0],
                                                'instance'
                                            ], isys_application::instance()->database),
                                            $l_key_arr[1],
                                            $l_key_arr[2]
                                        ];
                                    } elseif ($l_key_arr[0] === 'locales') { // See ID-2992
                                        $l_callbacks[$l_origin_key] = [
                                            isys_application::instance()->container->locales,
                                            $l_key_arr[1]
                                        ];
                                    }
                                }

                                if ($l_cut_to = strpos($l_key, '###')) {
                                    $l_key = substr($l_key, 0, $l_cut_to);
                                }

                                if (strpos($l_key, '#')) {
                                    $l_title_arr = explode('#', $l_key);

                                    $l_title_key = implode(' -> ', array_reverse(array_map(function ($val) {
                                        return isys_application::instance()->container->get('language')
                                            ->get($val);
                                    }, $l_title_arr)));
                                } else {
                                    $l_title_key = isys_application::instance()->container->get('language')
                                        ->get($l_key);
                                }

                                // In case the key already exists
                                // This closes #5069
                                $i = 2;
                                while (in_array($l_title_key, $translatedKey)) {
                                    $l_title_key .= ' ' . $i;
                                    $i++;
                                }

                                // Increase performance by caching translation in first row, to have it then available in all remaining rows
                                if ($l_title_key) {
                                    $translatedKey[$l_origin_key] = $l_title_key;
                                }
                            }
                        }

                        $l_row["__obj_id__"] = $l_row["__id__"] = $l_row["isys_obj__id"] = isys_glob_which_isset(
                            $l_row["isys_obj__id"],
                            $l_row["__obj_id__"],
                            $l_row["__id__"]
                        );

                        // Fixing translations.
                        $l_fixed_row = [];

                        foreach ($l_row as $l_key => $l_value) {
                            if (isset($dynamicCallbacksOnKey[$l_key])) {
                                $dynamicCallbacksOnKey[$l_key][] = $l_value;
                                continue;
                            }

                            $l_title_key = $translatedKey[$l_key];
                            if (!$l_title_key) {
                                continue;
                            }

                            if (isset($l_callbacks[$l_key])) {
                                if ($l_value !== null) {
                                    $l_callback = $l_callbacks[$l_key];
                                    $l_callback_row = $l_row;
                                    if (isset($l_callback[2])) {
                                        $l_callback_row[$l_callback[2]] = $l_value;
                                    }

                                    // See ID-2992
                                    if (is_a($l_callback[0], 'isys_locale')) {
                                        $l_callback_row = $l_value;
                                    }

                                    $l_value = call_user_func([
                                        $l_callback[0],
                                        $l_callback[1]
                                    ], $l_callback_row);
                                } else {
                                    $l_value = '';
                                }
                            }

                            // @todo See ID-3882
                            /*$dateValue = isys_application::instance()->container['locales']->fmt_date($l_value);
                            $l_value = strtotime($l_value) !== false && $dateValue && (strlen(intval($l_value)) < strlen($dateValue)) ? $dateValue : $l_value;*/

                            if ($p_context_html) {
                                $l_fixed_row[$l_title_key] = html_entity_decode($l_value, ENT_QUOTES, $GLOBALS['g_config']['html-encoding']);

                                // This replaces all plain text links to clickable links. See ID-2604
                                if (strpos($l_fixed_row[$l_title_key], '://') !== false || strpos($l_fixed_row[$l_title_key], 'www') !== false) {
                                    $l_fixed_row[$l_title_key] = isys_helper_textformat::link_urls_in_string($l_fixed_row[$l_title_key], "'");
                                }

                                // This replaces all plain text emails to clickable links. See ID-2604
                                if (strpos($l_fixed_row[$l_title_key], '@') !== false) {
                                    $l_fixed_row[$l_title_key] = isys_helper_textformat::link_mailtos_in_string($l_fixed_row[$l_title_key], "'");
                                }
                            } else {
                                $l_fixed_row[$l_title_key] = strip_tags(preg_replace("(<script[^>]*>([\\S\\s]*?)<\/script>)", '', $l_value));
                            }
                        }

                        $l_listing["content"][] = $l_fixed_row;
                    }

                    /**
                     * @var isys_cmdb_dao_category $dao
                     */
                    foreach ($dynamicCallbacksOnKey as $callbackKey => $objects) {
                        list($categoryClass, $callbackMethod) = explode('::', $callbackKey);

                        $dao = $categoryClass::instance(isys_application::instance()->container->get('database'));
                        $dao->get_data(null, $objects);
                    }


                    // Free memory result
                    $l_db->free_result($p_query);

                    // Get column names.
                    if (count($l_groups) > 0) {
                        $l_listing["grouped"] = true;
                        $l_tmp = $l_listing["content"][$l_groups[0]][0];
                    } else {
                        if (isset($l_listing["content"][0])) {
                            $l_tmp = $l_listing["content"][0];
                        } else {
                            $l_tmp = false;
                        }
                    }

                    if (is_array($l_tmp)) {
                        $l_tmp = array_keys($l_tmp);
                        $l_columns = [];

                        foreach ($l_tmp as $l_value) {
                            if (!isset($this->m_hidden_columns[$l_value]) && !preg_match("/^__[\w]+__$/i", $l_value)) {
                                $l_columns[] = isys_application::instance()->container->get('language')
                                    ->get($l_value);
                            }
                        }
                    } else {
                        $l_columns = null;
                    }

                    $l_listing["headers"] = $l_columns;
                }
            }
        }

        return $l_listing;
    }

    /**
     * Checks if a report already exists.
     *
     * @param  string $p_title
     * @param  string $p_query
     *
     * @return boolean
     * @throws isys_exception_database
     */
    public function reportExists($p_title, $p_query)
    {
        $l_sql = 'SELECT isys_report__id FROM isys_report
			WHERE isys_report__title = ' . $this->convert_sql_text($p_title) . '
			AND isys_report__query = ' . $this->convert_sql_text($p_query) . ';';

        return (count($this->retrieve($l_sql)) > 0);
    }

    /**
     * Update an existing report.
     *
     * @param   integer $p_id
     * @param   string  $p_title
     * @param   string  $p_description
     * @param   string  $p_query
     * @param   null    $deprecated
     * @param   null    $deprecated2
     * @param   integer $p_report_category_id
     * @param int       $compressedMultivalueResults
     * @param int       $showHtml
     * @param int       $keepDescriptionFormat
     *
     * @return  boolean
     * @throws isys_exception_dao
     */
    public function saveReport($p_id, $p_title, $p_description, $p_query, $deprecated = null, $deprecated2 = null, $p_report_category_id = null, $compressedMultivalueResults = 1, $showHtml = 0, $keepDescriptionFormat = 0)
    {
        $l_update = "UPDATE isys_report SET
			isys_report__title = " . $this->convert_sql_text($p_title) . ",
			isys_report__description = " . $this->convert_sql_text($p_description) . ",
			isys_report__query = " . $this->convert_sql_text($p_query) . ",
			isys_report__querybuilder_data = NULL,
			isys_report__last_edited = NOW(),
			isys_report__isys_report_category__id = " . $this->convert_sql_id($p_report_category_id) . ",
			isys_report__user_specific = 0,
			isys_report__compressed_multivalue_results = " . $this->convert_sql_int($compressedMultivalueResults). ",
			isys_report__show_html = " . $this->convert_sql_int($showHtml). ",
			isys_report__keep_description_format = " . $this->convert_sql_boolean($keepDescriptionFormat). "
			WHERE isys_report__id = " . $this->convert_sql_id($p_id) . ";";

        return ($this->update($l_update) && $this->apply_update());
    }

    /**
     * Method for validating that there are no updates, drops, truncates, ... inside the query.
     *
     * @param   string $p_query
     *
     * @return  boolean
     * @throws  Exception
     */
    public function validate_query($p_query)
    {
        return \idoit\Module\Report\Validate\Query::validate($p_query);
    }

    /**
     * Gets report title by report id.
     *
     * @param  integer $p_id
     *
     * @return string
     * @throws isys_exception_database
     */
    public function get_report_title_by_id($p_id)
    {
        $l_sql = "SELECT isys_report__title FROM isys_report WHERE isys_report__id = " . $this->convert_sql_id($p_id) . ";";

        return $this->retrieve($l_sql)
            ->get_row_value('isys_report__title');
    }

    /**
     * Modifies result row.
     *
     * @param  array $p_row
     *
     * @throws Exception
     */
    public function modify_row(&$p_row)
    {
        $lang = isys_application::instance()->container->get('language');

        $p_row['with_qb'] = ($p_row['with_qb'] == 1
            ? $lang->get("LC__UNIVERSAL__YES")
            : $lang->get("LC__UNIVERSAL__NO"));

        $p_row['isys_report__category_report'] = ($p_row['isys_report__category_report'] == 1
            ? $lang->get("LC__UNIVERSAL__YES")
            : $lang->get("LC__UNIVERSAL__NO"));
    }

    /**
     * Builds a callable for row links:
     * Either returns the standard url given as param $rowLink, or warning when report is not executable
     *
     * @param string $rowLink
     *
     * @return callable
     */
    public function buildRowLinkFunction($rowLink)
    {
        return function ($row) use ($rowLink) {
            if (empty($row)) {
                return $rowLink;
            }

            $reports = $this->get_reports(null, [$row['isys_report__id']], null, true, false);

            if ($this->hasQueryOnlyInternalPlaceholder($reports[0]['isys_report__query'])) {
                return $rowLink;
            }

            return "idoit.Notify.warning('" . isys_application::instance()->container->get('language')
                    ->get('LC__REPORT__PLACEHOLDER__NOT_EXECUTABLE', $reports[0]['isys_report__title']) . "')";
        };
    }

    /**
     * Gets data from the report category table as array.
     *
     * @param  mixed   $p_id
     * @param  boolean $p_as_array
     *
     * @return array|isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_report_categories($p_id = null, $p_as_array = true)
    {
        $l_return = [];
        $l_condition = '';

        if (is_array($p_id) && count($p_id)) {
            $l_condition = 'AND isys_report_category__id ' . $this->prepare_in_condition($p_id);
        } elseif ($p_id !== null && is_numeric($p_id)) {
            $l_condition = 'AND isys_report_category__id = ' . $this->convert_sql_id($p_id);
        } elseif (is_string($p_id)) {
            $l_condition = 'AND isys_report_category__title = ' . $this->convert_sql_text($p_id);
        }

        $l_res = $this->retrieve('SELECT * FROM isys_report_category WHERE TRUE ' . $l_condition . ' ORDER BY isys_report_category__sort ASC;');

        if ($p_as_array) {
            while ($l_row = $l_res->get_row()) {
                $l_return[] = $l_row;
            }
        } else {
            $l_return = $l_res;
        }

        return $l_return;
    }

    /**
     * Adds a new entry into the report category table.
     *
     * @param  string  $p_title
     * @param  string  $p_description
     * @param  integer $p_sorting
     *
     * @return boolean
     * @throws isys_exception_dao
     */
    public function create_category($p_title, $p_description = null, $p_sorting = 99)
    {
        $l_insert = 'INSERT INTO isys_report_category SET
			isys_report_category__title = ' . $this->convert_sql_text($p_title) . ',
			isys_report_category__description = ' . $this->convert_sql_text($p_description) . ',
			isys_report_category__sort = ' . $this->convert_sql_int($p_sorting) . ',
			isys_report_category__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        if ($this->update($l_insert) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    /**
     * Updates an existing report category entry.
     *
     * @param  integer $p_id
     * @param  string  $p_title
     * @param  string  $p_description
     * @param  integer $p_sorting
     *
     * @return boolean
     * @throws isys_exception_dao
     */
    public function update_category($p_id, $p_title, $p_description = null, $p_sorting = 99)
    {
        $l_update = 'UPDATE isys_report_category SET
			isys_report_category__title = ' . $this->convert_sql_text($p_title) . ',
			isys_report_category__description = ' . $this->convert_sql_text($p_description) . ',
			isys_report_category__sort = ' . $this->convert_sql_int($p_sorting) . '
			WHERE isys_report_category__id = ' . $this->convert_sql_id($p_id);

        return ($this->update($l_update) && $this->apply_update());
    }

    /**
     * Retrieves all reports by report category.
     *
     * @param  array|integer $p_data
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_reports_by_category($p_data = null)
    {
        $l_query = 'SELECT * FROM isys_report
			INNER JOIN isys_report_category ON isys_report__isys_report_category__id = isys_report_category__id
			WHERE TRUE';

        if ($p_data !== null) {
            if (is_array($p_data) && count($p_data)) {
                $l_query .= ' AND isys_report__isys_report_category__id ' . $this->prepare_in_condition($p_data);
            } else {
                $l_query .= ' AND isys_report__isys_report_category__id = ' . $this->convert_sql_id($p_data);
            }
        }

        return $this->retrieve($l_query . ';');
    }

    /**
     * Deletes a report category.
     *
     * @param  integer $p_report_category_id
     *
     * @return boolean
     * @throws isys_exception_dao
     */
    public function delete_report_category($p_report_category_id)
    {
        $l_sql = 'DELETE FROM isys_report_category WHERE isys_report_category__id = ' . $this->convert_sql_id($p_report_category_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Checks if report category exists.
     *
     * @param  integer $p_report_category_id
     *
     * @return integer
     * @throws isys_exception_database
     */
    public function check_report_category($p_report_category_id)
    {
        $l_sql = 'SELECT isys_report_category__id FROM isys_report_category WHERE isys_report_category__id = ' . $this->convert_sql_id($p_report_category_id) . ';';

        return count($this->retrieve($l_sql));
    }

    /**
     * Gets the default report category id (Global);
     *
     * @todo    Use constants... But not a title string :X
     * @return  mixed
     */
    public function get_default_report_category()
    {
        return $this->retrieve('SELECT isys_report_category__id FROM isys_report_category WHERE isys_report_category__title = ' . $this->convert_sql_text('Global') . ';')
            ->get_row_value('isys_report_category__id');
    }

    /**
     * Will replace every placeholder occurrence in the given query
     *
     * @param $query
     *
     * @return string
     */
    public function replacePlaceHolders($query)
    {
        // Match could look like this: PLACEHOLDER.2 'currentDatetime 2012-12-06'
        $modifiedQuery = preg_replace_callback("/(?:PLACEHOLDER(\s*|\.[0-9]*)) '(\D[^']*.)'/", function ($placeholder) {
            $userInput = '';

            if (!isset($placeholder[2])) {
                return $placeholder[0];
            }

            if (stripos($placeholder[2], ' ') !== false) {
                $userInput = ltrim(substr($placeholder[2], stripos($placeholder[2], ' ')));
            }

            $class = 'idoit\Module\Report\SqlQuery\Placeholder\\' . trim(str_replace('-', '', ucwords(str_replace($userInput, '', $placeholder[2]), '-')));

            if (!class_exists($class)) {
                return $placeholder[0];
            }

            /**
             * @var $queryPlaceholder Placeholder
             */
            $queryPlaceholder = new $class();

            return $queryPlaceholder->replacePlaceholder(str_replace($userInput, '', $placeholder[2]), $userInput);
        }, $query);

        return $modifiedQuery;
    }

    /**
     * Check if a query has only internal placeholders
     *
     * @param $query
     *
     * @return bool
     */
    public function hasQueryOnlyInternalPlaceholder($query)
    {
        $placeholders = [];

        if (preg_match_all("/(?:PLACEHOLDER(\s*|\.[0-9]*)) '(\D[^']*.)'/", $query, $placeholders) > 0 && isset($placeholders[2][0])) {
            foreach ($placeholders[2] as $placeholder) {
                $class = explode(' ', 'idoit\Module\Report\SqlQuery\Placeholder\\' . str_replace('-', '', ucwords($placeholder, '-')));

                if (count($class) !== 1) {
                    array_pop($class);
                }

                $class = $class[0];

                // Report should not be accessible then
                if (!class_exists($class)) {
                    return false;
                }

                /**
                 * @var $queryPlaceholder Placeholder
                 */
                $queryPlaceholder = new $class();

                if ($queryPlaceholder->isInternal() === false) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public static function hasTenantReportTable()
    {
        return isys_application::instance()->container->get('database')->is_table_existent('isys_report');
    }

    /**
     * @param isys_component_database $p_database
     *
     * @return isys_report_dao
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function instance(isys_component_database $p_database)
    {
        if (self::$m_instance === null) {
            if (self::hasTenantReportTable()) {
                self::$m_instance = new self(isys_application::instance()->container->get('database'));
            } else {
                self::$m_instance = new self(isys_application::instance()->container->get('database_system'));
            }
        }

        return self::$m_instance;
    }
}
