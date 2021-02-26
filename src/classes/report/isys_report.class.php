<?php

/**
 * i-doit Report Manager
 *
 * @package     i-doit
 * @subpackage  Reports
 * @author      Dennis Blümer <dbluemer@synetics.de>
 * @author      Van Quyen Hoang <qhoang@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_report
{
    /**
     * @var  boolean
     */
    public static $m_as_download = true;

    /**
     * @var string
     */
    protected $m_datetime;

    /**
     * @var  string
     */
    protected $m_description;

    /**
     * @var  boolean
     */
    protected $m_empty_values;

    /**
     * @var  boolean
     */
    protected $m_display_relations;

    /**
     * @var null
     */
    protected $m_export_output = null;

    /**
     * @var
     */
    protected $m_id;

    /**
     * @var array
     */
    protected $m_last_edited;

    /**
     * @var
     */
    protected $m_query;

    /**
     * @var string
     */
    protected $category_report = 0;

    /**
     * @var int
     */
    private $compressedMultivalueResults = 1;

    /**
     * @var
     */
    private $m_querybuilder_data;

    /**
     * @var
     */
    protected $m_report_category;

    /**
     * @var
     */
    protected $m_title;

    /**
     * @var
     */
    protected $m_type;

    /**
     * @var
     */
    protected $m_user_specific;

    /**
     * @var int
     */
    private $showHtml = 0;

    /**
     * @var int
     */
    private $keepDescriptionFormat = 0;

    /**
     * @var isys_component_database
     */
    private $database;

    /**
     * Getter method for retrieving the export output
     *
     * @return null
     */
    public function get_export_output()
    {
        return $this->m_export_output;
    }

    /**
     * Setter for the export output
     *
     * @param $p_data
     *
     * @return $this
     */
    public function set_export_output($p_data)
    {
        $this->m_export_output = $p_data;

        return $this;
    }

    /**
     * Getter method for the title.
     *
     * @return  string
     */
    public function getTitle()
    {
        return $this->m_title;
    }

    /**
     * Setter method for the title.
     *
     * @param   string $p_title
     *
     * @return  isys_report
     */
    public function setTitle($p_title)
    {
        $this->m_title = $p_title;

        return $this;
    }

    /**
     * Getter method for the description.
     *
     * @return  string
     */
    public function getDescription()
    {
        return $this->m_description;
    }

    /**
     * Setter method for the description.
     *
     * @param   string $p_description
     *
     * @return  isys_report
     */
    public function setDescription($p_description)
    {
        $this->m_description = $p_description;

        return $this;
    }

    /**
     * Getter method for the query.
     *
     * @return  string
     */
    public function getQuery()
    {
        return $this->m_query;
    }

    /**
     * Setter method for the query.
     *
     * @param   string $p_query
     *
     * @return  isys_report
     */
    public function setQuery($p_query)
    {
        $this->m_query = $p_query;

        return $this;
    }

    /**
     * Getter method for the query row.
     *
     * @return  boolean
     */
    public function get_user_specific()
    {
        return $this->m_user_specific;
    }

    /**
     * Setter method for the query row.
     *
     * @param   boolean $p_user_specific
     *
     * @return  isys_report
     */
    public function set_user_specific($p_user_specific)
    {
        $this->m_user_specific = $p_user_specific;

        return $this;
    }

    /**
     * Getter method for retrieving the member variable for the querybuilder data.
     *
     * @return mixed
     */
    public function get_querybuilder_data()
    {
        return $this->m_querybuilder_data;
    }

    /**
     * Setter method for setting the member variable for the querybuilder data.
     *
     * @param $p_data
     *
     * @return $this
     */
    public function set_querybuilder_data($p_data)
    {
        $this->m_querybuilder_data = $p_data;

        return $this;
    }

    /**
     * Getter method for retrieving the member variable for the report category id
     *
     * @return mixed
     */
    public function get_report_category()
    {
        return $this->m_report_category;
    }

    /**
     * Setter method for setting the member variable for the report category id
     *
     * @param $p_id
     *
     * @return $this
     */
    public function set_report_category($p_id)
    {
        $this->m_report_category = $p_id;

        return $this;
    }

    /**
     * Getter method for the datetime.
     *
     * @return  string
     */
    public function getDatetime()
    {
        return $this->m_datetime;
    }

    /**
     * Setter method for the datetime.
     *
     * @param   string $p_datetime
     *
     * @return  isys_report
     */
    public function setDatetime($p_datetime)
    {
        $this->m_datetime = $p_datetime;

        return $this;
    }

    /**
     * Getter method for the last edited.
     *
     * @return  string
     */
    public function getLast_edited()
    {
        return $this->m_last_edited;
    }

    /**
     * Setter method for the last edited.
     *
     * @param   string $p_last_edited
     *
     * @return  isys_report
     */
    public function setLast_edited($p_last_edited)
    {
        $this->m_last_edited = $p_last_edited;

        return $this;
    }

    /**
     * Getter method for the id.
     *
     * @return  integer
     */
    public function getId()
    {
        return (int)$this->m_id;
    }

    /**
     * Setter method for the last id.
     *
     * @param   integer $p_id
     *
     * @return  isys_report
     */
    public function setId($p_id)
    {
        $this->m_id = $p_id;

        return $this;
    }

    /**
     * Getter method for the type.
     *
     * @return  string
     */
    public function getType()
    {
        return $this->m_type;
    }

    /**
     * Setter method for the empty values.
     *
     * @param   mixed $p_value
     *
     * @return  $this
     */
    public function setEmpty_values($p_value)
    {
        $this->m_empty_values = (bool)$p_value;

        return $this;
    }

    /**
     * Getter method for the empty values
     *
     * @return  boolean
     */
    public function getEmpty_values()
    {
        return $this->m_empty_values;
    }

    /**
     * Setter method for "display relations".
     *
     * @param   mixed $p_value
     *
     * @return  $this
     */
    public function setDisplay_relations($p_value)
    {
        $this->m_display_relations = (bool)$p_value;

        return $this;
    }

    /**
     * Getter method for "display relation".
     *
     * @return  boolean
     */
    public function getDisplay_relations()
    {
        return $this->m_display_relations;
    }

    /**
     * Creates a report entry in table isys_report.
     *
     * @return int
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function store()
    {
        $session = isys_application::instance()->container->get('session');
        $dao = isys_report_dao::instance($this->database);

        $l_sql = 'INSERT INTO isys_report SET 
            isys_report__title = ' . $dao->convert_sql_text($this->m_title) . ',
            isys_report__description = ' . $dao->convert_sql_text($this->m_description) . ',
            isys_report__query = ' . $dao->convert_sql_text($this->m_query) . ',
            isys_report__mandator = ' . $dao->convert_sql_id($session->get_mandator_id()) . ',
            isys_report__user = ' . $dao->convert_sql_id($session->get_user_id()) . ',
            isys_report__const = ' . $dao->convert_sql_text($dao->generateConstant((string) $this->m_title)) . ',
            isys_report__datetime = NOW(),
            isys_report__last_edited = NOW(),
            isys_report__type = ' . $dao->convert_sql_int($this->m_type) . ',
            isys_report__category_report = ' . $dao->convert_sql_boolean($this->category_report == 'on') . ',
            isys_report__user_specific = ' . $dao->convert_sql_boolean($this->m_user_specific == 'on') . ',
            isys_report__isys_report_category__id = ' . $dao->convert_sql_id($this->m_report_category) . ',
            isys_report__empty_values = ' . $dao->convert_sql_boolean($this->m_empty_values) . ',
            isys_report__display_relations = ' . $dao->convert_sql_boolean($this->m_display_relations) . ',
            isys_report__querybuilder_data = ' . $dao->convert_sql_text($this->m_querybuilder_data) . ',
            isys_report__show_html = ' . $dao->convert_sql_boolean($this->showHtml) . ',
            isys_report__keep_description_format = ' . $dao->convert_sql_boolean($this->keepDescriptionFormat) . ',
            isys_report__compressed_multivalue_results = ' . $dao->convert_sql_boolean($this->compressedMultivalueResults) . ';';

        if ($dao->update($l_sql) && $dao->apply_update()) {
            return $this->m_id = $dao->get_last_insert_id();
        } else {
            throw new Exception("Error storing report: " . $this->database->get_last_error_as_string());
        }
    }

    /**
     * Update reports entry in table isys_report.
     *
     * @throws Exception
     */
    public function update()
    {
        $dao = isys_report_dao::instance($this->database);

        $l_sql = 'UPDATE isys_report
            SET isys_report__title = ' . $dao->convert_sql_text($this->m_title) . ',
            isys_report__description = ' . $dao->convert_sql_text($this->m_description) . ',
            isys_report__query = ' . $dao->convert_sql_text($this->m_query) . ',
            isys_report__querybuilder_data = ' . $dao->convert_sql_text($this->m_querybuilder_data) . ',
            isys_report__user_specific = ' . $dao->convert_sql_boolean($this->m_user_specific == 'on') . ', 
            isys_report__category_report = ' . $dao->convert_sql_boolean($this->category_report == 'on') . ',
            isys_report__isys_report_category__id = ' . $dao->convert_sql_id($this->m_report_category) . ',
            isys_report__empty_values = ' . $dao->convert_sql_boolean($this->m_empty_values) . ',
            isys_report__display_relations = ' . $dao->convert_sql_boolean($this->m_display_relations) . ',
            isys_report__last_edited = NOW(),
            isys_report__show_html = ' . $dao->convert_sql_boolean($this->showHtml) . ',
            isys_report__keep_description_format = ' . $dao->convert_sql_boolean($this->keepDescriptionFormat) . ',
            isys_report__compressed_multivalue_results = ' . $dao->convert_sql_boolean($this->compressedMultivalueResults) . '
            WHERE isys_report__id = ' . $dao->convert_sql_id($this->m_id) . ';';

        if (!$this->database->query($l_sql)) {
            throw new Exception("Error updating report");
        }
    }

    /**
     * Deletes a reports entry in table isys_report.
     *
     * @throws Exception
     */
    public function delete()
    {
        $l_sql = 'DELETE FROM isys_report WHERE (isys_report__id = ' . $this->getId() . ');';

        if (!$this->database->query($l_sql)) {
            throw new Exception("Error deleting report");
        }
    }

    /**
     * Checks if a title is already existing.
     *
     * @return bool
     * @throws isys_exception_database
     */
    public function exists()
    {
        $dao = isys_report_dao::instance($this->database);

        $l_sql = 'SELECT *
            FROM isys_report
            WHERE isys_report__title = ' . $dao->convert_sql_text($this->m_title) . ';';

        return count($dao->retrieve($l_sql)) > 0;
    }

    /**
     * Execute Query
     *
     * @param bool $p_title_chaining
     * @param bool $p_context_html
     *
     * @return array
     * @throws Exception
     */
    public function query($p_title_chaining = true, $p_context_html = true)
    {
        return isys_report_dao::instance($this->database)
            ->query($this->m_query, null, false, $p_title_chaining, $p_context_html);
    }

    /**
     * Reformats the report result
     *
     * @param      $result
     * @param bool $showHtml
     * @param bool $compressedMultivalueResults
     * @param null $p_limit
     *
     * @return array
     * @throws Exception
     */
    public static function reformatResult($result, $showHtml = false, $compressedMultivalueResults = false, $p_limit = null)
    {
        $language = isys_application::instance()->container->get('language');
        $return = [];
        $counter = 0;

        // This is necessary because of UTF8 and JSON complications.
        if ($result['grouped']) {
            foreach ($result['content'] as $groupName => $group) {
                $tmp = [];

                foreach ($group as $data) {
                    $tmp2 = [];

                    // With this code, we can set the ID at the first place of the table.
                    if (isset($data['__id__'])) {
                        $tmp2['__id__'] = $data['__id__'];
                    }

                    foreach ($data as $key => $value) {
                        if (in_array($key, $result['headers'])) {
                            $value = strip_tags(preg_replace('#<script(.*?)>(.*?)</script>#', '', $value), '<a><img>');

                            // The whitespace at the end fixes #3667.
                            $tmp2[$key] = $language->get_in_text($value) . '&nbsp;';
                        }
                    }

                    $tmp[] = $tmp2;
                }

                $return[$language->get($groupName)] = $tmp;
            }
        } else {
            if (is_array($result['content']) && count($result['content'])) {
                $lastSet = null;
                $skipEntryCounter = count($result['headers']);

                foreach ($result['content'] as $data) {
                    $tmp = [];
                    $skipCountdown = $skipEntryCounter;

                    if ($counter == 25 && $p_limit) {
                        break;
                    }

                    // With this code, we can set the ID at the first place of the table.
                    if (isset($data['__id__'])) {
                        $tmp['__id__'] = $data['__id__'];
                    }
                    $previousValue = ($tmp['__id__'] ?: '');

                    foreach ($data as $key => $value) {
                        if (in_array($key, $result['headers'])) {
                            $value = _LL(preg_replace('#<script(.*?)>(.*?)</script>#', '', $value)) . '&nbsp;';

                            if (!$showHtml) {
                                if (strpos($value, '</li><li>')) {
                                    $value = str_replace('</li><li>', '</li>, <li>', $value);
                                }

                                $value = nl2br(_LL(strip_tags(preg_replace('#<script(.*?)>(.*?)</script>#', '', $value), '<span><a><img>'))) . '&nbsp;';
                            }

                            if ($compressedMultivalueResults) {
                                $previousValue .= $value;
                                if (!empty($lastSet) && strpos($lastSet, $previousValue) !== false) {
                                    $value = '&nbsp;';
                                }
                            }

                            if ($value === '&nbsp;') {
                                $skipCountdown--;
                            }

                            // The whitespace at the end fixes #3667.
                            $tmp[$key] = $value;
                        }
                    }

                    if ($skipCountdown === 0) {
                        // if every value is empty then don´t show it
                        continue;
                    }

                    $return[] = $tmp;
                    $counter++;

                    $lastSet = $previousValue;
                }
            }
        }

        return $return;
    }

    /**
     * creates a new instance of isys_report.
     *
     * @param $p_params
     */
    public function __construct($p_params)
    {
        if (isset($p_params["report_id"])) {
            $this->m_id = $p_params["report_id"];
        }

        $this->m_type = $p_params["type"];
        $this->m_title = $p_params["title"];
        $this->m_description = $p_params["description"];
        $this->m_query = $p_params["query"];
        $this->m_user_specific = $p_params["userspecific"];
        $this->m_querybuilder_data = $p_params["querybuilder_data"];
        $this->m_report_category = $p_params["report_category"];
        $this->m_empty_values = $p_params['empty_values'];
        $this->m_display_relations = $p_params['display_relations'];
        $this->category_report = $p_params['category_report'];
        $this->compressedMultivalueResults = $p_params['compressed_multivalue_results'];
        $this->showHtml = $p_params['show_html'];
        $this->keepDescriptionFormat = (bool)$p_params['keep_description_format'];

        if (isset($p_params["datetime"])) {
            $this->m_datetime = $p_params["datetime"];
            $this->m_last_edited = $p_params["last_edited"];
        } else {
            $this->m_datetime = getdate();
            $this->m_last_edited = $this->m_datetime;
        }

        if (isys_report_dao::hasTenantReportTable()) {
            $this->database = isys_application::instance()->container->get('database');
        } else {
            $this->database = isys_application::instance()->container->get('database_system');
        }
    }

    /**
     * Should this export show HTML?
     *
     * @return bool
     */
    public function shouldShowHtml()
    {
        return (bool)$this->showHtml;
    }

    /**
     * Should this export keep description format for ascii charts?
     *
     * @return bool
     */
    public function shouldKeepDescriptionFormat()
    {
        return (bool)$this->keepDescriptionFormat;
    }

    /**
     * @return bool
     */
    public function isCompressedMultivalueResults()
    {
        return (bool)$this->compressedMultivalueResults;
    }

    /**
     * @return isys_component_database
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param isys_component_database $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
        return $this;
    }
}
