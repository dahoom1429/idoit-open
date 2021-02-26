<?php

namespace idoit\Module\Report;

use idoit\Component\Provider\Factory;
use idoit\Module\Report\Protocol\Worker;
use idoit\Module\Report\Validate\Query;
use isys_application;

/**
 * Report
 *
 * @package     idoit\Module\Report
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.7.1
 */
class Report
{
    use Factory;

    /**
     * @var \isys_component_dao
     */
    private $dao;

    /**
     * Report id
     *
     * @var int
     */
    private $id;

    /**
     * Report type (e.g. 'c' for custom)
     *
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * Report SQL Query
     *
     * @var string
     */
    private $query;

    /**
     * Tenant ID
     *
     * @var int
     */
    private $tenantId;

    /**
     * @var string
     */
    private $datetime;

    /**
     * @var string
     */
    private $lastEdited;

    /**
     * @var Worker
     */
    private $worker;

    /**
     * Is report executed in html context?
     *
     * If not, don't generate any html.
     *
     * @var string
     */
    private $htmlContext = true;

    /**
     * @var bool
     */
    private $compressedMultivalueResults = false;

    /**
     * Query execution as a generator function
     */
    public function execute()
    {
        if (!empty($this->query)) {
            try {
                if (Query::validate($this->query)) {
                    /**
                     * Increase max execution time for this report
                     * Default is 5x of php's default (3000)
                     */
                    set_time_limit(\isys_tenantsettings::get('report.max_execution_time', 3000));

                    $queryLimit = 0;

                    if (stripos($this->query, 'LIMIT')) {
                        $matches = [];
                        preg_match('/LIMIT [0-9,\s]*(?=\;)/i', trim($this->query), $matches);
                        $queryLimit = (int)filter_var($matches[0], FILTER_SANITIZE_NUMBER_INT);
                        $this->query = preg_replace('/LIMIT [0-9,\s]*(?=\;)/i', '', trim($this->query));
                    }

                    if (strpos($this->query, '{')) {
                        // Remove all placeholders from query like ,'{', isys_obj__id, '}'
                        $this->query = preg_replace("/\,\s*\'\s*\{\'\,\s*[\w]+\,.\'\}\'/", '', $this->query);
                    }

                    // Array of key translations, so the key part is only translated in first row and not in each row
                    $translatedKey = [];
                    $memory = \idoit\Component\Helper\Memory::instance();

                    $i = $page = 0;
                    $rows = 0;
                    $lastSet = null;

                    while (1) {
                        $portions = $queryLimit > 0 ? $queryLimit : \isys_tenantsettings::get('report.file-export.portions', 325000);
                        $offset = $page > 0 ? $portions * $page : 0;

                        // First we append an offset to the report-query.
                        $this->query = rtrim(trim($this->query), ';');
                        $limit = ' LIMIT ' . $offset . ', ' . $portions . ';';

                        $db = $this->dao->get_database_component();
                        $l_result = $db->query($this->query . $limit);

                        $l_num = $db->num_rows($l_result);

                        // Break if there are no more entries available
                        if ($l_num === 0 || $rows >= $portions) {
                            break;
                        }

                        if ($l_num > 0) {
                            $l_first_row = true;
                            $l_callbacks = [];

                            while ($l_row = $db->fetch_row_assoc($l_result)) {
                                // Break on memory overflows
                                if ($i++ % 1000 == 0) {
                                    $memory->outOfMemoryBreak();
                                }
                                $previousValue = [];
                                if ($l_first_row) {
                                    $l_first_row = false;
                                    foreach ($l_row as $l_key => $l_row_value) {
                                        $l_origin_key = $l_key;

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
                                                    ], \isys_application::instance()->database),
                                                    $l_key_arr[1],
                                                    $l_key_arr[2]
                                                ];
                                            } elseif ($l_key_arr[0] === 'locales') { // See ID-4672
                                                $l_callbacks[$l_origin_key] = [
                                                    \isys_application::instance()->container->locales,
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
                                        // Translation has to be checked if its already been used
                                        if (in_array($l_title_key, $translatedKey)) {
                                            $i = 2;
                                            while (in_array($l_title_key, $translatedKey)) {
                                                $l_title_key .= ' ' . $i;
                                                $i++;
                                            }
                                        }

                                        // Increase performance by caching translation in first row, to have it then available in all remaining rows
                                        if ($l_title_key) {
                                            $translatedKey[$l_origin_key] = $l_title_key;
                                        }
                                    }
                                }

                                if (isset($l_row["isys_obj__id"])) {
                                    $l_row["__obj_id__"] = $l_row["isys_obj__id"];
                                }

                                if (isset($l_row["__id__"]) && !isset($l_row["__obj_id__"])) {
                                    $l_row["__obj_id__"] = $l_row["__id__"];
                                }

                                if (!isset($l_row["isys_obj__id"])) {
                                    $l_row["isys_obj__id"] = $l_row["__id__"] ?: $l_row["__obj_id__"];
                                }

                                // Fixing translations.
                                $l_fixed_row = [];

                                foreach ($l_row as $l_key => $l_value) {
                                    $l_title_key = $translatedKey[$l_key];
                                    if (!$l_title_key) {
                                        continue;
                                    }

                                    if (isset($l_callbacks[$l_key])) {
                                        if ($l_value) {
                                            $l_callback = $l_callbacks[$l_key];

                                            // Value has to be appended with the real database field key
                                            if (isset($l_callbacks[$l_key][2]) && is_string($l_callbacks[$l_key][2])) {
                                                $l_row[$l_callbacks[$l_key][2]] = $l_value;
                                            }

                                            if (strpos($l_key, 'locales::') !== false) {
                                                $callbackValue = $l_value;
                                            } else {
                                                $callbackValue = $l_row;
                                            }

                                            $l_value = call_user_func([
                                                $l_callback[0],
                                                $l_callback[1]
                                            ], $callbackValue);
                                        } else {
                                            $l_value = '';
                                        }
                                    } else {
                                        if (strpos($l_value, 'LC_') === 0) {
                                            $l_value = isys_application::instance()->container->get('language')
                                                ->get($l_value);
                                        }
                                    }

                                    if ($this->htmlContext) {
                                        $l_fixed_row[$l_title_key] = html_entity_decode($l_value, ENT_QUOTES, $GLOBALS['g_config']['html-encoding']);

                                        // This replaces all plain text links to clickable links. See ID-2604
                                        if (strpos($l_fixed_row[$l_key], '://') !== false || strpos($l_fixed_row[$l_key], 'www') !== false) {
                                            $l_fixed_row[$l_key] = \isys_helper_textformat::link_urls_in_string($l_fixed_row[$l_key], "'");
                                        }

                                        // This replaces all plain text emails to clickable links. See ID-2604
                                        if (strpos($l_fixed_row[$l_key], '@') !== false) {
                                            $l_fixed_row[$l_key] = \isys_helper_textformat::link_mailtos_in_string($l_fixed_row[$l_key], "'");
                                        }
                                    } else {
                                        $l_fixed_row[$l_title_key] = strip_tags(preg_replace("(<script[^>]*>([\\S\\s]*?)<\/script>)", '', $l_value));
                                    }

                                    if ($this->compressedMultivalueResults) {
                                        $previousValue[$l_title_key] = $l_fixed_row[$l_title_key];
                                        if (!empty($lastSet) && empty(array_diff_assoc($previousValue, $lastSet))) {
                                            $l_fixed_row[$l_title_key] = null;

                                            if (isset($l_fixed_row[$l_key])) {
                                                $l_fixed_row[$l_key] = null;
                                            }
                                        }
                                    }
                                }

                                unset($l_fixed_row['__obj_id__'], $l_fixed_row['__id__']);
                                $checkValues = array_filter($l_fixed_row, function ($item) {
                                    if (!empty($item)) {
                                        return true;
                                    }
                                });

                                if ($this->worker && !empty($checkValues)) {
                                    $this->worker->work($l_fixed_row);
                                }

                                $lastSet = $previousValue;
                                unset($l_row);
                                $rows++;
                            }

                            // Free memory result
                            $l_result->free_result();
                        }

                        $page++;
                    }
                }
            } catch (\Exception $e) {
                throw $e;
            }
        }

        //$this->worker->work($row);
        //yield $row;
    }

    /**
     * @param $bool
     *
     * @return $this
     */
    public function enableHtmlContext($bool = true)
    {
        $this->htmlContext = $bool;

        return $this;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return int
     */
    public function getTenantId()
    {
        return $this->tenantId;
    }

    /**
     * @param int $tenantId
     *
     * @return $this
     */
    public function setTenantId($tenantId)
    {
        $this->tenantId = $tenantId;

        return $this;
    }

    /**
     * @return string
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * @param string $datetime
     *
     * @return $this
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastEdited()
    {
        return $this->lastEdited;
    }

    /**
     * @param string $lastEdited
     *
     * @return $this
     */
    public function setLastEdited($lastEdited)
    {
        $this->lastEdited = $lastEdited;

        return $this;
    }

    /**
     * @return Worker
     */
    public function getWorker()
    {
        return $this->worker;
    }

    /**
     * @param Worker $worker
     *
     * @return $this
     */
    public function setWorker(Worker $worker)
    {
        $this->worker = $worker;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCompressedMultivalueResults()
    {
        return $this->compressedMultivalueResults;
    }

    /**
     * @param bool $compressedMultivalueResults
     */
    public function setCompressedMultivalueResults($compressedMultivalueResults)
    {
        $this->compressedMultivalueResults = $compressedMultivalueResults;
        return $this;
    }

    /**
     * Report constructor.
     *
     * @param \isys_component_dao $dao
     * @param  string             $query
     */
    public function __construct(\isys_component_dao $dao, $query, $title = null, $reportId = null, $reportType = null)
    {
        $this->dao = $dao;
        $this->query = $query;

        $this->title = $title;
        $this->id = $reportId;
        $this->type = $reportType;
    }
}
