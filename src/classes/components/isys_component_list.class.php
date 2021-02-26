<?php

use idoit\Component\Table\Filter\Configuration;
use idoit\Component\Table\Pagerfanta\Adapter\ListDaoAdapter;
use idoit\Component\Table\Table;
use idoit\Module\Cmdb\Model\Ci\Table\Config;
use idoit\Module\Cmdb\Model\Ci\Table\Property;

/**
 * i-doit
 *
 * builds html-table for the object lists.
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Niclas Potthast <npotthast@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_list extends isys_cmdb_dao_list_objects
{
    /**
     *
     */
    const CL__DISABLE_ROW = 'component_list_disabled';

    /**
     * Flag if the current object list is allowed to use group by
     * @var bool
     */
    protected $m_allow_group_by = false;

    /**
     * @var  array
     */
    protected $m_arData = null;

    /**
     * @var  array
     */
    protected $m_arTableColumn = [];

    /**
     * @var  array
     */
    protected $m_arTableHeader = null;

    /**
     * @var  array
     */
    protected $m_arTablecellHtml = null;

    /**
     * @var  boolean
     */
    protected $m_bOrderLink = true;

    /**
     * @var  boolean
     */
    protected $m_bOverloadCursor = false;

    /**
     * @var  boolean
     */
    protected $m_bTranslate = true;

    /**
     * Is autocomplete enabled
     * @var bool
     */
    protected $autocomplete = false;

    /**
     * @var  array
     */
    protected $m_colgroups = [];

    /**
     * @var  boolean
     */
    protected $m_dragdrop = false;

    /**
     * @var  string
     */
    protected $m_id = 'isys_id';

    /**
     * @var isys_cmdb_dao_list
     */
    protected $m_listdao = null;

    /**
     * @var  isys_component_dao_result
     */
    protected $m_modified = false;

    /**
     * @var  integer
     */
    protected $m_nRecStatus = C__RECORD_STATUS__NORMAL;

    /**
     * @var  integer
     */
    protected $m_nRowID = 0;

    /**
     * @var  isys_component_dao_result
     */
    protected $m_resData = null;

    /**
     * @var  string
     */
    protected $m_row_formatter = "format_row";

    /**
     * @var  string
     */
    protected $m_row_method = "modify_row";

    /**
     * @var  object
     */
    protected $m_row_modifier = null;

    /**
     * @var  string
     */
    protected $m_strCheckboxValue = "";

    /**
     * @var  string
     */
    protected $m_strClass = "mainTable";

    /**
     * @var  string|callable
     */
    protected $m_strRowLink = "";

    /**
     * @var  string
     */
    protected $m_strTempTableName = "";

    /**
     * @var bool Defines, if list table should create own scope for its parameters or should it bind to the application scope
     */
    protected $scoped = false;

    /**
     * @var array Extra route params
     */
    protected $routeParams = [];

    /**
     * @var string
     */
    protected $tableConfigUrl = null;

    /**
     * @var bool
     */
    private $enableCheckboxes = true;

    /**
     * @var bool
     */
    private $enableFilter = true;

    /**
     * @var bool
     */
    private $enableResizeColumns = true;

    /**
     * @var string
     */
    private $ajaxMethod = 'get';

    /**
     * @var array
     */
    private $ajaxParams = [];

    /**
     * @var bool
     */
    private $enableMultiselection = false;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param bool $enableMultiselection
     */
    public function setEnableMultiselection($enableMultiselection)
    {
        $this->enableMultiselection = $enableMultiselection;

        return $this;
    }

    /**
     * Enable checkboxes (default).
     *
     * @return $this
     */
    public function enableCheckboxes()
    {
        $this->enableCheckboxes = true;

        return $this;
    }

    /**
     * Disable checkboxes.
     *
     * @return $this
     */
    public function disableCheckboxes()
    {
        $this->enableCheckboxes = false;

        return $this;
    }

    /**
     * Enable checkboxes (default).
     *
     * @return $this
     */
    public function enableFilter()
    {
        $this->enableFilter = true;

        return $this;
    }

    /**
     * Disable checkboxes.
     *
     * @return $this
     */
    public function disableFilter()
    {
        $this->enableFilter = false;

        return $this;
    }

    /**
     * Enable checkboxes (default).
     *
     * @return $this
     */
    public function enableResizeColumns()
    {
        $this->enableResizeColumns = true;

        return $this;
    }

    /**
     * Disable checkboxes.
     *
     * @return $this
     */
    public function disableResizeColumns()
    {
        $this->enableResizeColumns = false;

        return $this;
    }

    /**
     * @param null   $p_arData
     * @param null   $p_resData
     * @param null   $p_listdao
     * @param null   $p_nRecStatus
     * @param string $p_type
     *
     * @return isys_component_list_csv|isys_component_list_html
     */
    public static function factory($p_arData = null, $p_resData = null, $p_listdao = null, $p_nRecStatus = null, $p_type = 'html')
    {
        switch ($p_type) {
            default:
            case 'html':
                return new isys_component_list_html($p_arData, $p_resData, $p_listdao, $p_nRecStatus);

            case 'csv':
                return new isys_component_list_csv($p_arData, $p_resData, $p_listdao, $p_nRecStatus);
        }
    }

    /**
     * Method for activating the drag'n'drop feature.
     *
     * @return  isys_component_list
     */
    public function enable_dragndrop()
    {
        $this->m_dragdrop = true;

        return $this;
    }

    /**
     * Method for deactivating the drag'n'drop feature.
     *
     * @return  isys_component_list
     */
    public function disable_dragndrop()
    {
        $this->m_dragdrop = false;

        return $this;
    }

    /**
     * Sets a custom row modifier which is called when generating the list and has to have
     * the method modify_row(inout $p_row) implemented
     *
     * @param   object  $p_object
     * @param   boolean $p_method
     *
     * @return  isys_component_list
     * @author  Dennis Stuecken
     */
    public function set_row_modifier($p_object, $p_method = false)
    {
        if (is_object($p_object)) {
            $this->m_row_modifier = $p_object;
        }

        if ($p_method && method_exists($p_object, $p_method)) {
            $this->m_row_method = $p_method;
        }

        return $this;
    }

    /**
     * Method for setting a list DAO.
     *
     * @param  $p_listdao
     */
    public function set_listdao($p_listdao)
    {
        $this->m_listdao = $p_listdao;
    }

    public function getListDao()
    {
        return $this->m_listdao;
    }
    /**
     * Creates the temporary table with the data from init.
     *
     * @return  boolean
     * @author  Niclas Potthast <npotthast@i-doit.de>
     */
    public function createTempTable()
    {
        global $g_comp_database;

        $l_strSQLTemp = "";
        $l_objDAO = new isys_component_dao($g_comp_database);

        $l_header = $this->m_arTableHeader;

        $l_objDAO->begin_update();

        // Work with a DAO result.
        if ($this->m_resData) {
            if (is_callable($this->m_strRowLink)) {
                $link = call_user_func($this->m_strRowLink, []);
            } else {
                $link = $this->m_strRowLink;
            }
            if (($l_row_link_value = $this->getRowLinkValue($link))) {
                $l_header[$l_row_link_value] = "ID";
            }
            if (!array_key_exists($this->m_id, $l_header) && !array_key_exists("id", $l_header)) {
                $l_header[$this->m_id] = "ID";
            }

            $l_numheader = is_countable($l_header) ? count($l_header) : 0;

            // Read name, type and length for every field.
            foreach ($l_header as $l_field => $l_field_text) {
                // Write column name to member var.
                $this->m_arTableColumn[] = $l_field;

                if (strstr($l_field, "__id") || in_array($l_field, ["object_id", "object_count"]) || strpos($l_field, "_count") === strlen($l_field) - strlen('_count')) {
                    $l_type = "INT(10)";
                } else {
                    $l_type = "TEXT";
                }

                $l_field = $this->get_database_component()
                    ->escapeColumnName($l_field);
                if (empty($l_field)) {
                    continue;
                }
                $l_strSQLTemp .= $l_field . " " . $l_type . ",";
            }

            $l_strSQLTemp = rtrim($l_strSQLTemp, ",");
        } else {
            if (is_array($this->m_arData[0])) {
                $l_nArrayLength = count($this->m_arData[0]);
                $i = 0;
                foreach ($this->m_arData[0] as $key => $value) {
                    $l_type = "LONGTEXT";
                    if (is_integer($value)) {
                        $l_type = 'INT(10)';
                    }

                    $key = $this->get_database_component()->escapeColumnName($key);
                    if (empty($key)) {
                        continue;
                    }

                    $l_strSQLTemp .= "" . $key . " " . $l_type;
                    $this->m_arTableColumn[] = $key;

                    if ($i + 1 < $l_nArrayLength) {
                        $l_strSQLTemp .= ", ";
                    }

                    $i++;
                }
            }
        }

        $l_tempTableName = isys_glob_get_obj_list_table_name();

        // Save new name.
        $this->m_strTempTableName = $l_tempTableName;

        // Drop temporary table.
        $l_strSQL = "DROP TABLE IF EXISTS {$l_tempTableName};";
        $l_bEverythingGood = $l_objDAO->update($l_strSQL);

        if (strlen($l_strSQLTemp) < 3) {
            return false;
        }

        // Create temporary table.
        if ($l_bEverythingGood) {
            $l_strSQL = "CREATE TEMPORARY TABLE {$l_tempTableName} (" . $l_strSQLTemp . ") ENGINE=MyISAM;";
            $l_bEverythingGood = $l_objDAO->update($l_strSQL);
        }

        if ($l_bEverythingGood) {
            // Now insert the data from the old result/array into the new table.

            // Array to split up the SQL-Statement into smaller Queries, otherwise max_packet may be exceeded, when object lists are very large.
            $l_queries = [];

            if ($this->m_resData) {
                $l_strSQLTemp = "";
                $l_currentrow = 0;

                // Row count wrong with ports!? there is a "group by" in the result set! so the num_rows() doesnt seem to work.
                $l_rows = $this->m_resData->num_rows();

                $l_method = $l_modify_row = $l_custom_modify_row = false;

                // Exchange row-array by using method modify_row which is defined in the specific listDao.
                if ($this->m_listdao != null && is_a($this->m_listdao, "isys_component_dao_object_table_list")) {
                    $l_method = $this->m_row_method;

                    $l_modify_row = method_exists($this->m_listdao, $l_method);
                }

                // Custom row modifier.. (Not needed to be a table_list..)
                if (is_object($this->m_row_modifier) && method_exists($this->m_row_modifier, $this->m_row_method)) {
                    $l_method = $this->m_row_method;
                    $l_custom_modify_row = true;
                }

                if ($l_modify_row || $l_custom_modify_row) {
                    $this->m_modified = true;
                }

                while ($l_row_set = $this->m_resData->get_row()) {
                    $l_currentrow++;

                    if ($l_modify_row) {
                        $this->m_listdao->$l_method($l_row_set);
                    }

                    if ($l_custom_modify_row) {
                        $this->m_row_modifier->$l_method($l_row_set);
                    }

                    $i = 1;

                    foreach ($l_header as $l_key => $l_field_text) {
                        if ($l_key == $this->m_id) {
                            $l_row = $l_row_set[substr(substr($this->m_strCheckboxValue, 2), 0, strlen($this->m_strCheckboxValue) - 4)];
                        } else {
                            $l_row = $l_row_set[$l_key];

                            if (is_scalar($l_row)) {
                                if (strpos($l_row, "LC") === 0) {
                                    $l_row = isys_application::instance()->container->get('language')
                                        ->get($l_row);
                                }
                            } else {
                                if (is_array($l_row)) {
                                    $l_row = '<ul class="list-style-non"><li>' . implode('</li><li>', $l_row) . '</li></ul>';
                                } else {
                                    if (is_object($l_row) && is_a($l_row, 'isys_smarty_plugin_f')) {
                                        $l_row = $l_row->set_parameter('p_bEditMode', true)
                                            ->set_parameter('p_editMode', true)
                                            ->navigation_edit(isys_application::instance()->template);
                                    }
                                }
                            }
                        }

                        $l_strSQLTemp .= "'" . $g_comp_database->escape_string($l_row) . "'";

                        if ($i++ < $l_numheader) {
                            $l_strSQLTemp .= ',';
                        }
                    }

                    // Split up the query per 250 entries.
                    if (($l_currentrow % 250) == 0) {
                        $l_queries[] = $l_strSQLTemp;
                        unset($l_strSQLTemp);
                    } else {
                        if ($l_currentrow < $l_rows) {
                            $l_strSQLTemp .= "), (";
                        }
                    }
                }

                $this->m_resData->free_result();

                // Add the remaining entries.
                if (!empty($l_strSQLTemp)) {
                    $l_queries[] = $l_strSQLTemp;
                }
            } else {
                $l_strSQLTemp = "";
                $l_currentrow = 0;
                $l_rows = is_countable($this->m_arData) ? count($this->m_arData) : 0;

                foreach ($this->m_arData as $l_arVal) {
                    if (!is_countable($l_arVal)) {
                        continue;
                    }
                    $l_currentrow++;
                    $l_nTableFields = count($l_arVal);
                    $i = 1;

                    foreach ($l_arVal as $val) {
                        // If its a language constant, translate it.
                        if (stripos($val, "LC") === 0) {
                            $val = isys_application::instance()->container->get('language')
                                ->get($val);
                        }

                        if ($val == null) {
                            $l_strSQLTemp .= "NULL";
                        } else {
                            $l_strSQLTemp .= "'" . $g_comp_database->escape_string($val) . "'";
                        }

                        if ($i < $l_nTableFields) {
                            $l_strSQLTemp .= ", ";
                        }

                        $i++;
                    }

                    // Split up the query per 250 entries.
                    if (($l_currentrow % 250) == 0) {
                        $l_queries[] = $l_strSQLTemp;
                        $l_strSQLTemp = "";
                    } else {
                        if ($l_currentrow != $l_rows) {
                            $l_strSQLTemp .= "), (";
                        }
                    }
                }

                // Add the remaining entries.
                if (!empty($l_strSQLTemp)) {
                    $l_queries[] = $l_strSQLTemp;
                }
            }

            if (count($l_queries) > 0) {
                foreach ($l_queries as $l_sub) {
                    $l_bEverythingGood = $l_objDAO->update('INSERT INTO ' . $l_tempTableName . ' VALUES (' . $l_sub . ');');

                    if (!$l_bEverythingGood) {
                        break;
                    }
                }
            } else {
                $l_bEverythingGood = true;
            }
        } else {
            echo "Error: $l_strSQL<br />";
        }

        $l_bRet = $l_bEverythingGood;

        // Clear variable register.
        unset($l_strSQL, $l_bEverythingGood, $l_queries, $l_objDAO, $l_strSQLTemp, $l_row);

        return $l_bRet;
    }

    /**
     * Beware that the keys in m_arTableHeader have to be a column name from the temporary table, or you have to leave them empty.
     *
     * @param   array   $p_arTableHeader
     * @param   string|callable  $p_strRowLink
     * @param   string  $p_strCheckboxValue
     * @param   boolean $p_bTranslate
     * @param   boolean $p_bOrderLink
     * @param   array   $p_colgroups
     * @param   boolean $p_bOverloadCursor To overload cursor ie no link, see @tables.css CMDBListElementsOdd cursor:pointer;
     *
     * @return  isys_component_list
     * @throws  Exception
     */
    public function config(
        $p_arTableHeader,
        $p_strRowLink = "",
        $p_strCheckboxValue = "",
        $p_bTranslate = true,
        $p_bOrderLink = true,
        $p_colgroups = null,
        $p_bOverloadCursor = false
    ) {
        if (!is_array($p_arTableHeader)) {
            throw new Exception("Table headers are required to create a list.");
        } else {
            $this->m_arTableHeader = $p_arTableHeader;
        }

        if (!empty($p_strRowLink)) {
            $this->m_strRowLink = $p_strRowLink;

            if (is_string($this->m_strRowLink)) {
                $this->m_strRowLink = @str_replace(['ajax=1', 'only_content=1', 'scope=1'], [], $p_strRowLink);
            }
        }

        if ($p_strCheckboxValue != "") {
            $this->m_strCheckboxValue = $p_strCheckboxValue;
            $this->m_id = trim($this->m_strCheckboxValue, '[{}]');
        }

        if ($p_bTranslate == false) {
            $this->m_bTranslate = $p_bTranslate;
        }

        if (!$p_bOrderLink) {
            $this->m_bOrderLink = false;
        }

        if (!is_null($p_colgroups)) {
            $this->m_colgroups = $p_colgroups;
        }

        if ($p_bOverloadCursor) {
            $this->m_bOverloadCursor = true;
        }

        return $this;
    }

    /**
     * Set the class for the html table.
     *
     * @param   string $p_strClass
     *
     * @return  isys_component_list
     * @author  Niclas Potthast <npotthast@i-doit.de>
     */
    public function setTableClass($p_strClass)
    {
        if (!empty($p_strClass)) {
            $this->m_strClass = $p_strClass;
        }

        return $this;
    }

    /**
     * Sets the links for the table cells with a multidimensional array.
     *
     * @param   array $p_arTablecellHtml
     *
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function setTablecellHtml($p_arTablecellHtml)
    {
        if (is_array($p_arTablecellHtml)) {
            $this->m_arTablecellHtml = $p_arTablecellHtml;
        }
    }

    /**
     * Returns the name of the temporary table from which the html table is created.
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function getTempTableName()
    {
        return $this->m_strTempTableName;
    }

    /**
     * @param bool $scoped
     *
     * @return isys_component_list
     */
    public function setScoped($scoped)
    {
        $this->scoped = $scoped;

        return $this;
    }

    /**
     * @param isys_component_dao_result|null $daoResult
     *
     * @return string
     */
    public function getTempTableHtml(isys_component_dao_result $daoResult = null)
    {
        // special case for the rendering the result of query outside the scope
        if ($daoResult) {
            $data = [];
            while ($row = $daoResult->get_row()) {
                $data[] = $row;
            }
            $this->m_arData = $data;
            if ($this->createTempTable()) {
                return $this->render();
            }
            return '';
        }
        return $this->render();
    }

    /**
     * Render list as a table component
     * @return string
     */
    public function render()
    {
        $lang = isys_application::instance()->container->get('language');

        $tableConfig = $this->get_table_config();
        $cacheKey = $this->get_key();
        Configuration::sort($this, $cacheKey);
        $filterValues = Configuration::filter($this, $cacheKey);
        $sessionSource = Configuration::initSessionSource($cacheKey);
        if (!isset($_GET['suggestion'])) {
            if (empty($filterValues)) {
                $filterValues = $sessionSource->get();
            } else {
                $sessionSource->set($filterValues);
            }
        } else {
            $sessionSource->clear();
        }

        $filterColumns = [];
        $orderColumns = [];
        $header = [];

        if (is_countable($tableConfig->getProperties()) && count($tableConfig->getProperties()) === 0) {
            $property = new Property('', 'id', '', 'LC__CMDB__OBJTYPE__ID');
            $tableConfig->addProperty($property);
        }

        foreach ($tableConfig->getProperties() as $i => $property) {
            $header[$lang->get($property->getName())] = $lang->get($property->getName());

            $propType = $property->getType();

            if ($property->isIndexed() && ($propType != C__PROPERTY__INFO__TYPE__DIALOG)) {
                $filterColumns[$property->getPropertyKey()] = $lang->get($property->getName());

                $orderColumns[$i] = $property->getPropertyKey();
            }
        }
        $orderDefaultColumn = $tableConfig->getSortingProperty();
        $orderDefaultDirection = $tableConfig->getSortingDirection();

        // Set paging value based on query parameter
        $tableConfig->setPaging($_GET['page'] ?: 1);

        // It fixes the issue of the incorrect saved table config filter property: ID-4956
        $filterDefaultColumn = [];
        $fields = [$tableConfig->getFilterProperty(), Table::DEFAULT_FILTER_FIELD, key($filterColumns)];
        foreach ($fields as $field) {
            if ($field && isset($filterColumns[$field])) {
                $filterDefaultColumn = [
                    'title' => $filterColumns[$field],
                    'field' => $field
                ];
                break;
            }
        }
        $routeParams = [];
        parse_str($_SERVER['QUERY_STRING'], $routeParams);
        if (isset($routeParams['tableFilter'])) {
            foreach ($routeParams['tableFilter'] as $k => $v) {
                $routeParams["tableFilter[$k]"] = $v;
            }
        }
        unset($routeParams['tableFilter'], $routeParams['only_content'], $routeParams['scope']);
        if ($this->routeParams) {
            $routeParams = array_merge($routeParams, $this->routeParams);
        }
        $options = [
            'idField'               => $this->m_id,
            'tableIdField'          => 'id',
            'enableCheckboxes'      => $this->enableCheckboxes,
            'rowClick'              => $tableConfig->isRowClickable(),
            'rowClickAttribute'     => 'link',
            'keyboardCommands'      => true,
            'scoped'                => $this->scoped || $_GET[C__GET__SCOPE],
            'tableConfigURL'        => $this->tableConfigUrl,
            'dragDrop'              => $this->m_dragdrop,
            'order'                 => true,
            'orderColumns'          => $orderColumns,
            'orderDefaultColumn'    => $orderDefaultColumn,
            'orderDefaultDirection' => $orderDefaultDirection,
            'routeParams'           => $routeParams,
            'filter'                => ($this->enableFilter ? (isset($filterDefaultColumn['field']) && !empty($filterDefaultColumn['field'])) : false),
            'filterColumns'         => $this->enableFilter ? $filterColumns : [],
            'filterDefaultColumn'   => $this->enableFilter ? $filterDefaultColumn : null,
            'filterDefaultValues'   => $this->enableFilter ? $filterValues : null,
            'resizeColumns'         => $this->enableResizeColumns,
            'resizeColumnAjaxURL'   => isys_helper_link::create_url([
                C__GET__AJAX      => 1,
                C__GET__AJAX_CALL => 'table',
                'func'            => 'saveColumnWidths',
                'identifier'      => 'cmdb.obj-' . $cacheKey . '.table-columns'
            ]),
            'resizeTableAjaxURL'    => isys_helper_link::create_url([
                C__GET__AJAX      => 1,
                C__GET__AJAX_CALL => 'table',
                'func'            => 'saveTableWidth',
                'identifier'      => 'cmdb.obj-' . $cacheKey . '.table-width'
            ]),
            'columnSizes'           => isys_usersettings::get('cmdb.obj-' . $cacheKey . '.table-columns', []),
            'tableSize'             => isys_usersettings::get('cmdb.obj-' . $cacheKey . '.table-width', 0),
            'rowsPerPage'           => (int)$_GET['rowsPerPage'] ?: \isys_usersettings::get('gui.objectlist.rows-per-page', 50),
            'replacerOptions'       => $this->getReplacerOptions($tableConfig),
            'status'                => ($this->get_rec_status() ?: C__RECORD_STATUS__NORMAL),
            'ajaxMethod'            => $this->ajaxMethod,
            'ajaxParams'            => $this->ajaxParams,
            'autocomplete'          => $this->autocomplete,
            'enableMultiselection'  => $this->enableMultiselection
        ];

        // @see  ID-6902  We merge the basic options with custom ones.
        $options = array_merge_recursive($options, $this->options);

        // @see  ID-6902  We had to move this down so it's still possible to modify the data.
        $options['replacerOptions'] = isys_format_json::encode($options['replacerOptions']);

        $table = new Table(new ListDaoAdapter($this), $tableConfig, $header, $options);
        $html = $table->render(true);
        if (isys_core::is_ajax_request() && isset($_GET['only_content']) && $_GET['only_content']) {
            echo $html;
            die();
        }
        return $html;
    }

    /**
     * @param array $routeParams
     *
     * @return $this
     */
    public function setRouteParams(array $routeParams)
    {
        $this->routeParams = $routeParams;

        return $this;
    }

    /**
     * Set tableConfigUrl
     * @param string $tableConfigUrl
     *
     * @return isys_component_list
     */
    public function setTableConfigUrl($tableConfigUrl)
    {
        $this->tableConfigUrl = $tableConfigUrl;

        return $this;
    }

    /**
     * @param string $m_id
     *
     * @return isys_component_list
     */
    public function setIdField($id)
    {
        $this->m_id = $id;

        return $this;
    }

    /**
     * @param string $ajaxMethod
     *
     * @return isys_component_list
     */
    public function setAjaxMethod($ajaxMethod)
    {
        $this->ajaxMethod = $ajaxMethod;

        return $this;
    }

    /**
     * @param array $ajaxParams
     *
     * @return isys_component_list
     */
    public function setAjaxParams($ajaxParams)
    {
        $this->ajaxParams = $ajaxParams;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAutocomplete()
    {
        return $this->autocomplete;
    }

    /**
     * @param bool $autocomplete
     */
    public function setAutocomplete($autocomplete)
    {
        $this->autocomplete = $autocomplete;
    }

    /**
     *
     * @param Config $tableConfig
     *
     * @return array
     */
    protected function getReplacerOptions(Config $tableConfig)
    {
        $locale = isys_application::instance()->container->get('locales');
        $currencyConfig = $locale->get_user_settings(LC_NUMERIC);

        return [
            'memoryUnit'               => $tableConfig->getAdvancedOptionMemoryUnit(),
            'currencyUnit'             => $locale->get_currency(),
            'currencySeparator'        => $currencyConfig['thousand_sep'],
            'currencyDecimalSeparator' => $currencyConfig['decimal_point'],
            'linkEmailAddresses' => $tableConfig->getShowEmailLinks()
        ];
    }

    /**
     *
     * @param   array                     $p_arData
     * @param   isys_component_dao_result $p_resData
     *
     * @return  isys_component_list
     */
    public function set_data($p_arData = null, $p_resData = null)
    {
        if (is_array($p_arData)) {
            $this->m_arData = $p_arData;
        } else {
            $this->m_resData = $p_resData;
        }

        return $this;
    }

    /**
     *
     * @param   array $p_arData
     *
     * @return  isys_component_list
     */
    public function set_m_arTableColumn($p_arData = null)
    {
        if (is_array($p_arData)) {
            foreach ($p_arData as $l_key => $l_val) {
                $this->m_arTableColumn[] = is_numeric($l_key) ? $l_val : $l_key;
            }
        }

        return $this;
    }

    /**
     * @param string $p_strString
     * @param array  $p_arRow
     *
     * @author Dennis Stuecken <dstuecken@i-doit.org>
     * @desc   searches for [{...}] in strings and replaces them with the value
     *         of the row of the DAO result.
     *         the maximal count of values to be translated is 10.
     */
    protected function replaceLinkValues(&$p_strString, $p_arRow, $p_max_count = 10)
    {
        $i = 0;
        while (preg_match("/\[\{(.*?)\}\]/i", $p_strString, $l_reg)) {
            if (isset($p_arRow[$l_reg[1]])) {
                $p_strString = str_replace("[{" . $l_reg[1] . "}]", $p_arRow[$l_reg[1]], $p_strString);
            }
            if (++$i == $p_max_count) {
                break;
            }
        }
    }

    /**
     * @param $p_strString
     *
     * @return bool
     */
    protected function getRowLinkValue(&$p_strString)
    {
        if (preg_match("/\[\{(.*?)\}\]/i", $p_strString, $l_reg)) {
            return $l_reg[1];
        } else {
            return false;
        }
    }

    /**
     * Checks whether a string is a column name from the temp table.
     *
     * @param   string $p_strName
     *
     * @return  boolean
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    protected function isTableColumn($p_strName)
    {
        return in_array($p_strName, $this->m_arTableColumn);
    }

    /**
     * Get sorting string
     * @param null $property
     * @param null $direction
     *
     * @return string
     */
    public function get_table_sorting($property = null, $direction = null)
    {
        $fields = $this->getFields();
        if (!$property) {
            return '';
        }
        $sort = $property . ' ' . ($direction == 'ASC' ? 'ASC' : 'DESC');
        if (method_exists($this->m_listdao, "get_order_condition")) {
            $sort = $this->m_listdao->get_order_condition($property, $direction);
        }

        return ' ORDER BY ' . $sort;
    }

    /**
     * Return fields for list
     * @return array
     */
    protected function getFields()
    {
        return method_exists($this->m_listdao, 'get_fields') ? $this->m_listdao->get_fields() : $this->m_arTableHeader;
    }

    /**
     * Get query to get the data
     * @param int $offset
     * @param int $length
     *
     * @return string
     */
    public function get_table_query($offset = 0, $length = 10)
    {
        $sql = "SELECT * FROM {$this->getTempTableName()}";
        $sql = $this->addConditionsToQuery($sql, '', $offset, $length);
        return $sql;
    }

    /**
     * Load the data according to the state
     * @param int $offset
     * @param int $length
     *
     * @return array
     */
    public function load($offset = 0, $length = 10)
    {
        global $g_comp_database;
        $sql = $this->get_table_query($offset, $length);

        $res = $g_comp_database->query($sql);

        $l_modify_row = false;
        $l_custom_modify_row = false;
        $l_format_row = false;

        if (!$this->m_modified) {
            // Exchange row-array by using method modify_row which is defined in the specific listDao.
            if ($this->m_listdao != null && $this->m_listdao instanceof \isys_component_dao_object_table_list) {
                $l_method = $this->m_row_method;
                if (method_exists($this->m_listdao, $l_method)) {
                    $l_modify_row = true;
                }
            }

            // Custom row modifier.. (Not needed to be a table_list..).
            if (is_object($this->m_row_modifier) && method_exists($this->m_row_modifier, $this->m_row_method)) {
                $l_method = $this->m_row_method;
                $l_custom_modify_row = true;
            }
        }

        if ($this->m_listdao != null && $this->m_listdao instanceof \isys_component_dao_object_table_list) {
            $l_formatter_method = $this->m_row_formatter;

            if (method_exists($this->m_listdao, $l_formatter_method)) {
                $l_format_row = true;
            }
        }

        $result = [];
        while ($l_row = $res->fetch_array()) {
            foreach ($l_row as $l_key => $l_value) {
                $l_row[substr($l_key, 5)] = $l_value;
            }

            if ($l_modify_row) {
                $this->m_listdao->$l_method($l_row);
            }

            if ($l_custom_modify_row) {
                $this->m_row_modifier->$l_method($l_row);
            }

            if ($l_format_row) {
                $this->m_listdao->$l_formatter_method($l_row);
            }
            if ($this->m_strRowLink) {
                if (is_callable($this->m_strRowLink)) {
                    $link = call_user_func($this->m_strRowLink, $l_row);
                } else {
                    $link = $this->m_strRowLink;
                }
                $this->replaceLinkValues($link, $l_row);
                $l_row['link'] = $link;
            }
            $result[] = $l_row;
        }

        return $result;
    }

    /**
     *
     * @param   array                     $p_arData
     * @param   isys_component_dao_result $p_resData
     * @param   isys_cmdb_dao             $p_listdao
     * @param   integer                   $p_nRecStatus
     *
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function __construct($p_arData = null, $p_resData = null, $p_listdao = null, $p_nRecStatus = null)
    {
        global $g_comp_database;

        if (!is_object($p_listdao)) {
            $this->set_listdao(new isys_cmdb_dao($g_comp_database));
        } else {
            $this->set_listdao($p_listdao);
        }

        if (is_array($p_arData)) {
            $this->m_arData = $p_arData;
        } else {
            $this->m_resData = $p_resData;
        }

        if ($p_nRecStatus) {
            $this->m_nRecStatus = $p_nRecStatus;
        } else {
            $this->m_nRecStatus = $_SESSION["cRecStatusListView"];
        }
        parent::__construct($g_comp_database);
    }

    /**
     * Build default table config from the fields
     *
     * @return $this|Config
     */
    public function get_default_table_config()
    {
        if (isys_tenantsettings::has('cmdb.multilist-table.default-config.' . $this->get_key())) {
            $config = isys_tenantsettings::get('cmdb.multilist-table.default-config.' . $this->get_key());
            if ($config && is_string($config)) {
                $default = unserialize($config);
                if (is_array($default) && isset($default['isys_obj_type_list__table_config'])) {
                    $default = unserialize($default['isys_obj_type_list__table_config']);
                }
            }
        }
        if (isset($default) && $default instanceof Config) {
            return $default;
        }

        return $this->getConfigWithAllProperties();
    }

    /**
     * Build the Table Config with all properties of category list
     *
     * @return Config
     * @throws Exception
     */
    public function getConfigWithAllProperties()
    {
        $tableConfig = (new Config())->setRowClickable(true)
            ->setFilterWildcard(true);
        $fields = $this->getFields();

        if (is_a($this->m_listdao, 'isys_component_dao_category_table_list')) {
            $categoryId = $this->m_listdao->get_dao_category()
                ->get_category_id();
            $categoryTypeId = $this->m_listdao->get_dao_category()
                ->get_category_type();

            $extendedFields = isys_component_signalcollection::get_instance()
                ->emit("mod.cmdb.extendFieldList", $categoryId, $categoryTypeId);

            if (is_array($extendedFields)) {
                $extendedFields = array_shift($extendedFields);
            }

            if (!empty($extendedFields)) {
                $fields = array_merge($fields, $extendedFields);
            }
        }

        foreach ($fields as $property => $header) {
            $parts = explode('__', $property);
            $class = array_shift($parts);
            $prop = implode('__', $parts);
            $prop = new Property($class, $prop, $class, $header, true);
            $prop->setType('text');
            if (strlen($header) > 0) {
                $tableConfig->addProperty($prop);
            }
        }

        return $tableConfig;
    }

    /**
     * Load config from DB
     * @return Config|isys_component_list|mixed
     */
    public function load_user_config()
    {
        $uid = isys_application::instance()->container->session->get_user_id();
        $keys = ['cmdb.multilist-table.config.' . $uid . '.' . $this->get_key(), 'cmdb.multilist-table.default-config.' . $this->get_key()];
        foreach ($keys as $key) {
            if (isys_tenantsettings::has($key)) {
                $data = unserialize(isys_tenantsettings::get($key));
                if (is_array($data)) {
                    return $data;
                }
            }
        }
        return $this->get_default_table_config();
    }

    /**
     * Save default config
     * @param Config $tableConfig
     */
    public function save_default_table_config(Config $tableConfig)
    {
        $data = [
            'isys_obj_type_list__table_config'  => serialize($tableConfig),
            'isys_obj_type_list__row_clickable' => $tableConfig->isRowClickable()
        ];
        isys_tenantsettings::set('cmdb.multilist-table.default-config.' . $this->get_key(), serialize($data));
    }

    /**
     * Save user config
     * @param Config $tableConfig
     * @param null   $uid
     */
    public function save_table_config(Config $tableConfig, $uid = null)
    {
        $data = [
            'isys_obj_type_list__table_config'  => serialize($tableConfig),
            'isys_obj_type_list__row_clickable' => $tableConfig->isRowClickable()
        ];
        $uid = $uid ?: isys_application::instance()->container->session->get_user_id();
        isys_tenantsettings::set('cmdb.multilist-table.config.' . $uid . '.' . $this->get_key(), serialize($data));
    }

    /**
     * @return string
     */
    public function get_key()
    {
        if ($this->m_listdao instanceof isys_cmdb_dao_list) {
            $key = $this->m_listdao->get_category_type() . '-' .  $this->m_listdao->get_category();

            if ($this->m_listdao instanceof isys_cmdb_dao_list_catg_custom_fields) {
                $key .= '-' . $this->m_listdao->get_dao_category()->get_catg_custom_id();
            }
            return $key;
        }
        return get_class($this->m_listdao);
    }
}
