<?php

/**
 * i-doit
 *
 * builds html-table for the logbook lists
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_list_logbook
{
    protected $m_arData = null;

    protected $m_arTableColumn = [];

    protected $m_arTableHeader = null;

    protected $m_arTablecellHtml = null;

    protected $m_bOrderLink = true;

    protected $m_bTranslate = true;

    protected $m_listdao = null;

    protected $m_resData = null;

    protected $m_strClass = "mainTable";

    protected $m_strRowLink = ""; // used list_dao

    protected $m_strTempTableName = "isys_logbook";

    /**
     * Creates the temporary table with the data from init
     *
     * @global $g_comp_database
     * @return bool
     * @author Niclas Potthast <npotthast@i-doit.de> - 2005-11-29
     *
     * @param  $p_num_rows  integer, optional parameter, returns the num_row()
     *                      by reference
     */
    public function createTempTable(&$p_num_rows = 0)
    {
        // ID-3074 Removing a lot of unused code
        return true;
    }

    /**
     * @param array  $p_arTableHeader
     * @param string $p_strRowLink
     * @param bool   $p_bTranslate
     *
     * @desc beware that the keys in m_arTableHeader have to be a column
     *       name from the temporary table, or you have to leave them empty
     */
    public function config($p_arTableHeader, $p_strRowLink = "", $p_bTranslate = true, $p_bOrderLink = true)
    {
        if (is_array($p_arTableHeader)) {
            $this->m_arTableHeader = $p_arTableHeader;
        }

        if (!empty($p_strRowLink)) {
            $this->m_strRowLink = $p_strRowLink;
        }

        if ($p_bTranslate == false) {
            $this->m_bTranslate = $p_bTranslate;
        }

        if (!$p_bOrderLink) {
            $this->m_bOrderLink = false;
        }
    }

    /**
     * @param string $p_strClass
     *
     * @author Niclas Potthast <npotthast@i-doit.de> - 2005-12-02
     * @desc   set the class for the html table
     */
    public function setTableClass($p_strClass)
    {
        if (!empty($p_strClass)) {
            $this->m_strClass = $p_strClass;
        }
    }

    /**
     * @param array $p_ararTablecellLinks
     *
     * @author Niclas Potthast <npotthast@i-doit.org> - 2005-11-29
     * @desc   sets the links for the table cells with a multidimensional array
     */
    public function setTablecellHtml($p_arTablecellHtml)
    {
        if (is_array($p_arTablecellHtml)) {
            $this->m_arTablecellHtml = $p_arTablecellHtml;
        }
    }

    /**
     * @return string
     * @author Niclas Potthast <npotthast@i-doit.org> - 2005-12-14
     * @desc   returns the name of the temporary table from which the html
     *         table is created
     */
    public function getTempTableName()
    {
        return $this->m_strTempTableName;
    }

    /**
     * Return the HTML table.
     *
     * @param null $p_filter
     *
     * @author  Niclas Potthast <npotthast@i-doit.de>
     * @return string
     * @throws isys_exception_general
     */
    public function getTempTableHtml($p_filter = null)
    {
        global $g_dirs;

        $language = isys_application::instance()->container->get('language');

        if (!$p_filter) {
            $p_filter = $_POST;
        }

        $l_navbar = isys_component_template_navbar::getInstance();
        $l_mod_event_manager = isys_event_manager::getInstance();

        $l_nRowCounter = 0;
        $l_objTemplate = new isys_component_template();
        $l_objDAORes = $this->getTableResult($p_filter);
        $l_bOrderLink = $this->m_bOrderLink;

        $logbookLevels = new isys_cmdb_dao_dialog(isys_application::instance()->database, 'isys_logbook_level');

        $l_dao_logbook = new isys_component_dao_logbook(isys_application::instance()->database);

        if (!$l_objDAORes) {
            throw new isys_exception_general(get_class($this) . ": getTempTableHtml doesn't have any results.");
        }

        $l_navbar->set_page_results($l_objDAORes->num_rows())
            ->set_active(true, C__NAVBAR_BUTTON__FORWARD)
            ->set_active(true, C__NAVBAR_BUTTON__BACK);

        $l_strRet = '<table class="' . $this->m_strClass . '">' .
            '<colgroup><col width="25" /><col width="50%" /><col width="80" /><col width="100" /><col width="135" /></colgroup>';

        if ($this->m_arTableHeader && ($l_objDAORes->num_rows() != 0)) {
            // Build table header.
            $l_strRet .= '<tr>';
            $i = 0;

            foreach ($this->m_arTableHeader as $l_key => $value) {
                $i++;

                //sort by $l_key
                if ($this->isTableColumn($l_key)) {
                    if ($l_bOrderLink) {
                        $l_strRet .= '<th title="' . $language->get("LC__UNIVERSAL__SORT") . '" onClick="document.isys_form.dir.value=\'' . isys_glob_get_order() . '\'; document.isys_form.sort.value=\'' .
                            $l_key . '\'; document.isys_form.submit();">' . $value;

                        if (isys_glob_get_param("sort") == $l_key) {
                            $l_strRet .= '<img class="fr" style="margin-top:8px;margin-right:-2px;" src="' . $g_dirs['images'] . '/list/' .
                                strtolower(isys_glob_get_param("dir")) . '.gif" />';
                        }

                        $l_strRet .= '</th>';
                    } else {
                        $l_strRet .= '<th>' . $value . '</th>';
                    }
                } else {
                    $l_strRet .= '<th>' . $value . '</th>';
                }
            }

            $l_strRet .= '</tr>';
        }

        // Is there at least one row?
        if ($l_objDAORes->num_rows() == 0) {
            $l_strRet = '<div class="m10">' . (isset($l_strTemp) ? $l_strTemp : '') . '</div>';
        } else {
            while ($l_row = $l_objDAORes->get_row(IDOIT_C__DAO_RESULT_TYPE_ALL)) {
                $l_strRowLink = "";

                // Exchange row-array by using method modify_row which is defined in the specific listDao.
                if ($this->m_listdao != null) {
                    if (is_a($this->m_listdao, "isys_component_dao_object_table_list")) {
                        $this->m_listdao->modify_row($l_row);
                    }
                }

                $l_changes = $l_dao_logbook->get_changes_as_array($l_row["isys_logbook__changes"]);
                $l_row["isys_logbook__changes"] = is_countable($l_changes) ? count($l_changes) : 0;

                //build table row
                if (!empty($this->m_strRowLink)) {
                    //search and replace VARS in link
                    $l_strRowLink = $this->m_strRowLink;

                    //replace values in [{...}] with row content
                    $this->replaceLinkValues($l_strRowLink, $l_row);
                }

                $l_strRet .= '<tr class="' . $l_objTemplate->row_background_color($l_nRowCounter) . '" style="border-top: 1px solid #31ACC2;">';

                switch ($logbookLevels->get_data($l_row['isys_logbook__isys_logbook_level__id'])['isys_logbook_level__const']) {
                    default:
                    case "C__LOGBOOK__ALERT_LEVEL__0":
                        $l_class = "LogbookListElement0";
                        break;

                    case "C__LOGBOOK__ALERT_LEVEL__1":
                        $l_class = "LogbookListElement1";
                        break;

                    case "C__LOGBOOK__ALERT_LEVEL__2":
                        $l_class = "LogbookListElement2";
                        break;

                    case "C__LOGBOOK__ALERT_LEVEL__3":
                        $l_class = "LogbookListElement3";
                        break;
                }

                $l_row['isys_logbook_level__title'] = $language->get($l_row['isys_logbook_level__title']);
                $l_row['isys_logbook_source__title'] = $language->get($l_row['isys_logbook_source__title']);

                if (isset($l_row['isys_logbook__obj_type_static'])) {
                    $l_row['isys_logbook__obj_type_static'] = $language->get($l_row['isys_logbook__obj_type_static']);
                }

                $l_row['isys_logbook__title'] = $l_mod_event_manager->translateEvent(
                    $l_row["isys_logbook__event_static"],
                    $l_row["isys_logbook__obj_name_static"],
                    $l_row["isys_logbook__category_static"],
                    $l_row["isys_logbook__obj_type_static"],
                    $l_row['isys_logbook__entry_identifier_static'],
                    $l_row['isys_logbook__changecount']
                );

                foreach ($this->m_arTableHeader as $l_key => $value) {
                    $l_strTablecellContent = $l_row["$l_key"];

                    if (is_array($this->m_arTablecellHtml)) {
                        // If a key from the array m_arTablecellHtml matches the current key from m_arTableHeader then switch the content of the table cell with the value from m_arTablecellHtml.
                        if (isset($this->m_arTablecellHtml[$l_key])) {
                            $l_strTablecellContent = $this->m_arTablecellHtml[$l_key];

                            //now parse the content for "[{...}]"
                            $this->replaceLinkValues($l_strTablecellContent, $l_row);
                        }
                    }

                    if ($l_key == "+") {
                        $l_archive = ($_POST["filter_archive"] == '1' ? 'true' : 'false');

                        $l_strRet .= '<td class="logexpand mouse-pointer" onclick="expandEntry(' . $l_row["isys_logbook__id"] . ', ' . $l_archive . ', ' . defined_or_default('C__MODULE__LOGBOOK') .
                            ');"';
                    } else {
                        $l_strRet .= '<td onclick="' . $l_strRowLink . '"';
                    }

                    $l_strTablecellContent = stripslashes($l_strTablecellContent);

                    if ($l_key == "isys_logbook_level__title") {
                        $l_strRet .= ' class="' . $l_class . '"';
                    }

                    if ($l_key == "+") {
                        $l_strRet .= ' class="center" id="ec' . $l_row["isys_logbook__id"] . '"><img src="' . $g_dirs['images'] . 'icons/silk/bullet_toggle_plus.png" /></td>';
                    } else {
                        $l_strRet .= '>' . $l_strTablecellContent . '</td>';
                    }
                }

                $l_strRet .= '</tr>' .
                    '<tr id="tr' . $l_row["isys_logbook__id"] . '" style="display:none;" class="' . $l_objTemplate->row_background_color($l_nRowCounter) . '">' .
                        '<td></td>' .
                        '<td colspan="5">' .
                        '<div id="logb' . $l_row["isys_logbook__id"] . '" style="overflow-x:auto;" onclick="collapseEntry(' . $l_row["isys_logbook__id"] . ');"></div>' .
                        '</td>' .
                    '</tr>';

                $l_nRowCounter++;
            }
        }

        return $l_strRet . '</table>';
    }

    /**
     * Return the html table (grouped).
     *
     * @param   array $p_groupRow
     *
     * @return  String
     * @throws  isys_exception_general
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     */
    public function getGroupedTableHtml($filter)
    {
        global $g_dirs;

        $languageManager = isys_application::instance()->container->get('language');

        if (empty($filter['group'])) {
            return $this->getTempTableHtml($filter);
        }

        $groupField = $filter['group'];
        unset($filter['group']);

        $l_strRet = "";
        $l_nRowCounter = 0;
        $l_objTemplate = new isys_component_template();
        $l_objDAORes = $this->getTableResult($filter);
        $l_bOrderLink = $this->m_bOrderLink;

        $l_dao_logbook = new isys_component_dao_logbook(isys_application::instance()->container->get('database'));

        if (!$l_objDAORes) {
            throw new isys_exception_general(get_class($this) . ": getTempTableHtml doesn't have any results.");
        }

        $l_strTooltip = $languageManager->get("LC__UNIVERSAL__SORT");

        $l_groupedResult = [];
        while ($l_row = $l_objDAORes->get_row()) {
            if (empty($l_row[$groupField])) {
                $l_groupedResult[$languageManager->get("LC__CMDB__OBJTYPE_GROUP__OTHER")][] = $l_row;
            } else {
                $l_groupedResult[$l_row[$groupField]][] = $l_row;
            }
        }

        $l_strRet .= "\n<table class=\"$this->m_strClass\">\n";
        $l_strRet .= "<colgroup>" . "<col width=\"25\"/>" . "<col width=\"50%\"/>" . "<col width=\"80\"/>" . "<col width=\"100\"/>" . "<col width=\"135\"/>" . "</colgroup>\n";

        if ($this->m_arTableHeader && ($l_objDAORes->num_rows() != 0)) {
            //build table header
            $l_strRet .= "\t<tr>\n";
            $i = 0;

            foreach ($this->m_arTableHeader as $l_key => $value) {
                $i++;

                //sort by $l_key
                if ($this->isTableColumn($l_key)) {
                    if ($l_bOrderLink) {
                        $l_dir = isys_glob_get_param("dir");
                        $l_sort = isys_glob_get_param("sort");

                        $l_javascript = "document.isys_form.dir.value='" . isys_glob_get_order() . "'; document.isys_form.sort.value='" . $l_key .
                            "'; document.isys_form.submit();";
                        $l_strRet .= '<th title="' . $l_strTooltip . '" onClick="' . $l_javascript . '">' . $value;

                        if ($l_sort == $l_key) {
                            $l_strRet .= ' <img src="' . $g_dirs['images'] . '/' . strtolower($l_dir) . '.png" height="10" border="0" />';
                        }

                        $l_strRet .= '</th>';
                    } else {
                        $l_strRet .= '<th>' . $value . '</th>';
                    }
                } else {
                    $l_strRet .= '<th>' . $value . '</th>';
                }
            }

            $l_strRet .= '</tr>';
        }

        //is there at least one row?
        if ($l_objDAORes->num_rows() == 0) {
            $l_strRet .= '<tr><td>' . $languageManager->get("LC__CMDB__FILTER__NOTHING_FOUND_STD") . '</td></tr>';
        } else {
            foreach ($l_groupedResult as $l_key => $l_group) {
                $l_strRet .= '<tr class="bold"><td colspan="7">' . $languageManager->get($l_key) . '</td></tr>';

                foreach ($l_group as $l_row) {
                    $l_strRowLink = "";

                    // Exchange row-array by using method modify_row which is defined in the specific listDao.
                    if ($this->m_listdao != null) {
                        if (is_a($this->m_listdao, "isys_component_dao_object_table_list")) {
                            $this->m_listdao->modify_row($l_row);
                        }
                    }

                    $l_changes = $l_dao_logbook->get_changes_as_array($l_row["isys_logbook__changes"]);
                    $l_row["isys_logbook__changes"] = is_countable($l_changes) ? count($l_changes) : 0;

                    // Build table row.
                    if (!empty($this->m_strRowLink)) {
                        // Search and replace VARS in link.
                        $l_strRowLink = $this->m_strRowLink;
                        // Replace values in [{...}] with row content.
                        $this->replaceLinkValues($l_strRowLink, $l_row);
                    }

                    // Table row with highlighting.
                    $l_strRet .= '<tr class="' . $l_objTemplate->row_background_color($l_nRowCounter) . '">';

                    switch ($l_row["isys_logbook_level__const"]) {
                        case "C__LOGBOOK__ALERT_LEVEL__0":
                            $l_class = "LogbookListElement0";
                            break;

                        case "C__LOGBOOK__ALERT_LEVEL__1":
                            $l_class = "LogbookListElement1";
                            break;

                        case "C__LOGBOOK__ALERT_LEVEL__2":
                            $l_class = "LogbookListElement2";
                            break;

                        case "C__LOGBOOK__ALERT_LEVEL__3":
                            $l_class = "LogbookListElement3";
                            break;
                        default:
                            $l_row["isys_logbook_level__const"] = "C__LOGBOOK__ALERT_LEVEL__0";
                            $l_class = "LogbookListElement0";
                            break;
                    }

                    $l_row['isys_logbook_level__title'] = $languageManager->get($l_row['isys_logbook_level__title']);
                    $l_row['isys_logbook_source__title'] = $languageManager->get($l_row['isys_logbook_source__title']);

                    if (isset($l_row['isys_logbook__obj_type_static'])) {
                        $l_row['isys_logbook__obj_type_static'] = $languageManager->get($l_row['isys_logbook__obj_type_static']);
                    }

                    foreach ($this->m_arTableHeader as $l_key => $value) {
                        $l_strTablecellContent = $l_row[$l_key];

                        $l_row["isys_logbook_source__const"] = str_replace("C__LOGBOOK_SOURCE__", "", $l_row["isys_logbook_source__const"]);

                        if (is_array($this->m_arTablecellHtml)) {
                            //if a key from the array m_arTablecellHtml matches the
                            // current key from m_arTableHeader then switch the content
                            // of the table cell with the value from m_arTablecellHtml
                            if (key_exists($l_key, $this->m_arTablecellHtml)) {
                                $l_strTablecellContent = $this->m_arTablecellHtml[$l_key];
                                //now parse the content for "[{...}]"
                                $this->replaceLinkValues($l_strTablecellContent, $l_row);
                            }
                        }

                        if ($l_key == "+") {
                            if ($_POST["filter_archive"] == "1") {
                                $l_archive = "true";
                            } else {
                                $l_archive = "false";
                            }

                            $l_strRet .= "\t\t<td class=\"logexpand mouse-pointer\" onclick=\"expandEntry(" . $l_row["isys_logbook__id"] . ", {$l_archive}, '" .
                                defined_or_default('C__MODULE__LOGBOOK') . "');";
                        } else {
                            $l_strRet .= "\t\t<td onclick=\"" . $l_strRowLink;
                        }

                        $l_strTablecellContent = stripslashes($l_strTablecellContent);

                        $l_strRet .= "\"";
                        if ($l_key == "isys_logbook_level__title") {
                            $l_strRet .= " class=\"" . $l_class . "\"";
                        }

                        if ($l_key == "+") {
                            $l_strRet .= " style=\"text-align:center;\" id=\"ec" . $l_row["isys_logbook__id"] . "\"><img src=\"" . $g_dirs['images'] .
                                "icons/silk/bullet_toggle_plus.png\" /></td>\n";
                        } else {
                            $l_strRet .= ">" . $l_strTablecellContent . "</td>\n";
                        }
                    }

                    $l_strRet .= "\t</tr>\n";
                    $l_strRet .= "<tr id=\"tr" . $l_row["isys_logbook__id"] . "\" style=\"display:none;\" class=\"" . $l_objTemplate->row_background_color($l_nRowCounter) .
                        "\">" . "<td></td>" . "<td colspan=\"5\">" . "<div id=\"logb" . $l_row["isys_logbook__id"] . "\" " . "onmouseover=\"this.style.cursor='pointer';\" " .
                        "onclick=\"collapseEntry(" . $l_row["isys_logbook__id"] . ");\"></div></td>" . "<td></td><td></td></tr>";

                    $l_nRowCounter++;
                }
            }
        }

        $l_strRet .= "</table>\n";

        return $l_strRet;
    }

    /**
     *
     * @param  array                     $p_arData
     * @param  isys_component_dao_result $p_resData
     */
    public function set_data($p_arData = null, $p_resData = null)
    {
        if (is_array($p_arData)) {
            $this->m_arData = $p_arData;
        } else {
            $this->m_resData = $p_resData;
        }
    }

    /**
     * Searches for [{...}] in strings and replaces them with the value of the row of the DAO result. the maximal count of values to be translated is 20.
     *
     * @param string $p_strString
     * @param array  $p_arRow
     *
     * @author Niclas Potthast <npotthast@i-doit.org> - 2005-12-01
     */
    protected function replaceLinkValues(&$p_strString, $p_arRow)
    {
        $l_nSecurityCounter = 0;

        // Search for "[{"
        $l_nBegin = strpos($p_strString, "[{");
        while ($l_nBegin !== false) {
            //search "}]" and save end position
            $l_nEnd = strpos($p_strString, "}]");
            //replace "[{...}]" with value from p_arRow get value to be translated
            $l_strTranslate = substr($p_strString, $l_nBegin + 2, ($l_nEnd - 2) - $l_nBegin);
            //translate value
            $l_strTranslate = $p_arRow[$l_strTranslate];
            //paste the translated string into the other string
            $p_strString = substr($p_strString, 0, $l_nBegin) . $l_strTranslate . substr($p_strString, $l_nEnd + 2);
            $l_nBegin = strpos($p_strString, "[{");
            $l_nSecurityCounter++;
            //just for security reasons we prevent infinite loops
            if ($l_nSecurityCounter == 20) {
                break;
            }
        }
    }

    /**
     * @return bool
     *
     * @param string $p_strName
     *
     * @author Niclas Potthast <npotthast@i-doit.org> - 2006-03-08
     * @desc   checks whether a string is a column name from the temp table
     */
    protected function isTableColumn($p_strName)
    {
        $l_bRet = false;
        if (in_array($p_strName, $this->m_arTableColumn)) {
            $l_bRet = true;
        }

        return $l_bRet;
    }

    /**
     * Builds a CASE String for dialog tables so that we don´t have to join the dialog tables
     * Example:
     * $p_table = 'isys_logbook_event'
     * $p_ref_field = 'isys_logbook__isys_logbook_event__id'
     * $p_as_ref_field = 'isys_logbook_event__title'
     * $p_filter_id = '1' (Optional)
     *
     * @param      $p_table
     * @param      $p_ref_field
     * @param      $p_as_ref_field
     *
     * @return string
     */
    protected function get_dialog_table_as_case($p_table, $p_ref_field, $p_as_ref_field)
    {
        $l_sql = "SELECT * FROM " . $p_table;

        // Calculate right prefix
        $columnPrefix = str_replace('isys_archive', 'isys', $p_table);

        $l_res = $this->m_listdao->retrieve($l_sql);
        $l_case = '';
        if ($l_res->num_rows() > 0) {
            $l_case = ' (CASE ' . $p_ref_field . ' ';
            while ($l_row = $l_res->get_row()) {
                $l_case .= ' WHEN ' . $l_row[$columnPrefix . '__id'] . ' THEN ' . $this->m_listdao->convert_sql_text($l_row[$columnPrefix . '__title']);
            }
            $l_case .= ' END) AS ' . $p_as_ref_field . ' ';
        }

        return $l_case;
    }

    /**
     * Remember: Certain elements in the given result set have to be already filtered. For example "cRecStatus".
     *
     * @return  isys_component_dao_result result
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     */
    protected function getTableResult($p_filter)
    {
        global $g_comp_database;
        $l_navbar = isys_component_template_navbar::getInstance();

        if (empty($this->m_strTempTableName)) {
            return null;
        }

        $l_logbook_level_case = $this->get_dialog_table_as_case($this->m_listdao->getTableName('isys_logbook_level'), 'isys_logbook__isys_logbook_level__id', 'isys_logbook_level__title');
        $l_logbook_event_case = $this->get_dialog_table_as_case($this->m_listdao->getTableName('isys_logbook_event'), 'isys_logbook__isys_logbook_event__id', 'isys_logbook_event__title');
        $l_logbook_source_case = $this->get_dialog_table_as_case($this->m_listdao->getTableName('isys_logbook_source'), 'isys_logbook__isys_logbook_source__id', 'isys_logbook_source__title');

        $l_logbook_fields = [
            'isys_logbook__id',
            'isys_logbook__isys_obj__id',
            'isys_logbook__isys_logbook_event__id',
            'isys_logbook__isys_logbook_level__id',
            'isys_logbook__isys_logbook_source__id',
            'isys_logbook__title',
            'isys_logbook__description',
            'isys_logbook__comment',
            'isys_logbook__changes',
            isys_cmdb_dao_category_g_global::build_query_date_format('isys_logbook__date', true) . ' AS isys_logbook__date',
            'isys_logbook__status',
            'isys_logbook__property',
            'isys_logbook__user_name_static',
            'isys_logbook__event_static',
            'isys_logbook__obj_name_static',
            'isys_logbook__category_static',
            'isys_logbook__entry_identifier_static',
            'isys_logbook__obj_type_static',
            'isys_logbook__isys_logbook_reason__id',
            'isys_logbook__changecount'
        ];

        $l_strSQL = "SELECT " . implode(',', $l_logbook_fields) . ' ' . (strlen($l_logbook_level_case) > 0 ? ',' . $l_logbook_level_case : '') .
            (strlen($l_logbook_event_case) > 0 ? ',' . $l_logbook_event_case : '') . (strlen($l_logbook_source_case) > 0 ? ',' . $l_logbook_source_case : '') . " FROM " .
            $this->m_strTempTableName . " ";

        // SQL statment to retrieve number of rows
        $countSql = 'SELECT COUNT(*) AS TOTAL FROM ' . $this->m_strTempTableName . ' ';

        // Flag for import log
        $joins = '';
        if (isset($p_filter['import_id'])) {
            $joins .= 'INNER JOIN isys_logbook_2_isys_import ON isys_logbook__id = isys_logbook_2_isys_import__isys_logbook__id ';
        }

        //$l_strSQL .= 'LEFT JOIN ' . $this->m_listdao->getTableName('isys_catg_logb_list') . ' ON isys_catg_logb_list__isys_logbook__id = isys_logbook__id ';
        $joins .= 'LEFT JOIN ' . $this->m_listdao->getTableName('isys_catg_logb_list') . ' ON isys_catg_logb_list__isys_logbook__id = isys_logbook__id ';

        $l_filter_text = isys_glob_get_param("filter");
        $l_filter_source = isys_glob_get_param("filter_source");
        $l_filter_alert = isys_glob_get_param("filter_alert");

        $l_filter_text = $g_comp_database->escape_string($l_filter_text);
        $l_filter_source = $g_comp_database->escape_string($l_filter_source);
        $l_filter_alert = $g_comp_database->escape_string($l_filter_alert);

        $conditions = 'WHERE TRUE ';
        if (!empty($l_filter_text)) {
            $conditions .= "AND (isys_logbook__title LIKE '%" . $l_filter_text . "%'
                OR isys_logbook__date LIKE '%" . $l_filter_text . "%'
                OR isys_logbook_level__title LIKE '%" . $l_filter_text . "%') ";
        }

        if (!empty($l_filter_source) && $l_filter_source != -1) {
            $conditions .= "AND isys_logbook__isys_logbook_source__id = '" . $l_filter_source . "' ";
        }

        if (!empty($l_filter_alert) && $l_filter_alert != -1) {
            $conditions .= "AND isys_logbook__isys_logbook_level__id = '" . $l_filter_alert . "' ";
        }

        if (isys_glob_get_param("changes_only") == "1") {
            $conditions .= "AND isys_logbook__changes != '' ";
        }

        if (isset($p_filter["filter_type"])) {
            switch ($p_filter["filter_type"]) {
                case 0:
                    $conditions .= 'AND isys_catg_logb_list__id IS NULL ';
                    break;

                case 1:
                    $conditions .= 'AND isys_catg_logb_list__id IS NOT NULL ';
                    break;

                default:
                    ;
                    break;
            }
        }

        if (isset($p_filter['import_id']) && $p_filter['import_id']) {
            $conditions .= ' AND isys_logbook_2_isys_import__isys_import__id = ' . (int)$p_filter['import_id'] . ' ';
        }

        if (isset($p_filter['object_id']) && $p_filter['object_id']) {
            if (is_array($p_filter['object_id'])) {
                $conditions .= ' AND isys_catg_logb_list__isys_obj__id IN (' . implode(',', array_filter(array_map('intval', $p_filter['object_id']))) . ') ';
            } else {
                $conditions .= ' AND isys_catg_logb_list__isys_obj__id = ' . (int)$p_filter['object_id'] . ' ';
            }
        }

        if (strstr($p_filter["filter_from__HIDDEN"], 'undefined')) {
            $p_filter["filter_from__HIDDEN"] = '';
        }
        if (strstr($p_filter["filter_to__HIDDEN"], 'undefined')) {
            $p_filter["filter_to__HIDDEN"] = '';
        }

        if (empty($p_filter["filter_from__HIDDEN"]) && !empty($p_filter["filter_from__VIEW"])) {
            $p_filter["filter_from__HIDDEN"] = $p_filter["filter_from__VIEW"];
        }

        if (empty($p_filter["filter_to__HIDDEN"]) && !empty($p_filter["filter_to__VIEW"])) {
            $p_filter["filter_to__HIDDEN"] = $p_filter["filter_to__VIEW"];
        }

        if ($p_filter["filter_from__HIDDEN"] != "") {
            $conditions .= "AND isys_logbook__date > '" . $p_filter["filter_from__HIDDEN"] . "' ";
        }

        if ($p_filter["filter_to__HIDDEN"] != "") {
            $conditions .= "AND isys_logbook__date < '" . $p_filter["filter_to__HIDDEN"] . "' ";
        }

        if (isset($p_filter["filter_user__HIDDEN"])) {
            if ($p_filter["filter_user__HIDDEN"]) {
                $l_users = isys_format_json::decode($p_filter["filter_user__HIDDEN"]);
                if (is_array($l_users) && count($l_users) > 0) {
                    $conditions .= "AND isys_logbook__isys_obj__id IN (" . implode(',', $l_users) . ') ';
                }
            }
        }

        // Use the sorting if it's set.
        $l_dir = isys_glob_get_param("dir");
        $l_sort = isys_glob_get_param("sort");

        $order = '';
        if (strlen($l_dir) > 0 && strlen($l_sort) > 0) {
            if ($this->isTableColumn($l_sort)) {
                switch ($l_dir) {
                    case 'ASC':
                        $order .= "ORDER BY LENGTH(" . $l_sort . "),  " . $l_sort . " ASC ";
                        break;
                    case 'DESC':
                        $order .= "ORDER BY LENGTH(" . $l_sort . ") DESC, " . $l_sort . " DESC ";
                        break;
                }
            }
        } else {
            // default order
            $order .= "ORDER BY isys_logbook__id DESC ";
        }

        $limit = '';
        if (isys_glob_get_param("navPageStart")) {
            $limit .= "LIMIT " . isys_glob_get_param("navPageStart") . "," . isys_glob_get_pagelimit() . " ";
        } else {
            $limit .= "LIMIT 0," . isys_glob_get_pagelimit() . " ";
        }

        $l_strSQL .= $joins . ' ' . $conditions . ' ' . $order . ' ' . $limit;
        $countSql .= $joins . ' ' . $conditions;
        $l_result = $this->m_listdao->retrieve($l_strSQL);
        $l_numResult = $this->m_listdao->retrieve($countSql);
        $l_temp = $l_numResult->get_row();

        $l_navbar->set_nav_page_count($l_temp["TOTAL"]);

        return $l_result;
    }

    /**
     * @param   array                     $p_arData
     * @param   isys_component_dao_result $p_resData
     * @param   null                      $p_listdao
     *
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @desc
     */
    public function __construct($p_arData = null, $p_resData = null, $p_listdao = null)
    {
        $this->m_listdao = $p_listdao;

        if (is_array($p_arData)) {
            $this->m_arData = $p_arData;
        } else {
            $this->m_resData = $p_resData;
        }
    }
}
