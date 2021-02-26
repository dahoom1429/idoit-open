<?php

use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: Global category CPU.
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_cpu extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     * @var string
     */
    protected $m_category = 'cpu';

    /**
     * This variable holds the language constant of the current category.
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__CPU';

    /**
     * Is category multi-valued or single-valued?
     * @var bool
     */
    protected $m_multivalued = true;

    /**
     * Dynamic property handling for getting the formatted CPU data.
     *
     * @param  array $p_row
     *
     * @return string
     * @throws isys_exception_database
     */
    public function dynamic_property_callback_frequency($p_row)
    {
        global $g_comp_database;

        $l_cpu_res = [];
        $l_object_id = isys_glob_which_isset($p_row['isys_obj__id'], $p_row['__obj_id__'], $p_row['__id__']);

        if (isset($p_row['isys_catg_cpu_list__id']) && $p_row['isys_catg_cpu_list__id'] > 0) {
            $l_cpu_res = isys_cmdb_dao_category_g_cpu::instance($g_comp_database)
                ->get_data($p_row['isys_catg_cpu_list__id']);
        } elseif ($l_object_id > 0) {
            $l_cpu_res = isys_cmdb_dao_category_g_cpu::instance($g_comp_database)
                ->get_data(null, $l_object_id);
        }

        if (is_countable($l_cpu_res) && count($l_cpu_res) > 0) {
            $l_return = [];

            while ($l_cpu_row = $l_cpu_res->get_row()) {
                $l_core_prefix = '';

                if ($l_cpu_row['isys_catg_cpu_list__cores'] > 1 && !isset($p_row['isys_catg_cpu_list__id'])) {
                    $l_core_prefix = $l_cpu_row['isys_catg_cpu_list__cores'] . '@ ';
                }

                $l_return[] = $l_core_prefix .
                    isys_convert::frequency($l_cpu_row['isys_catg_cpu_list__frequency'], $l_cpu_row['isys_frequency_unit__const'], C__CONVERT_DIRECTION__BACKWARD) . ' ' .
                    $l_cpu_row['isys_frequency_unit__title'];
            }

            // The "PHP_EOL" is necessary for reports (CSV).
            return '<ul><li>' . implode('</li>' . PHP_EOL . '<li>', $l_return) . '</li></ul>';
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Import-Handler for this category.
     *
     * @param   array $p_data
     *
     * @return  array
     * @throws isys_exception_dao
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     */
    public function import($p_data)
    {
        $l_ids = [];

        // Prepare _POST variable(s).
        if (is_numeric($p_data['NumberOfProcessors']) && $p_data['NumberOfProcessors'] > 0) {
            $_POST['C__CATG__CPU_NUMBER2CREATE'] = $p_data['NumberOfProcessors'];
        } else {
            $_POST['C__CATG__CPU_NUMBER2CREATE'] = 1;
        }

        if (is_countable($p_data) && count($p_data) > 0) {
            // Iterate through CPUs.
            foreach ($p_data as $l_cpu) {
                // Save / Create.
                $l_status = -1;

                // Cat-New: 0, Cat-Save: ?.
                $l_cat = -1;

                // Prepare additional _POST variables.
                if ($l_cpu['speed'] > 100) {
                    $l_cpu['speed'] = number_format($l_cpu['speed'] / 1000, 2);
                }

                // !empty() checks are done inside isys_import::check_dialog, so they are not needed here.
                $_POST['C__CATG__CPU_MANUFACTURER'] = isys_import::check_dialog('isys_catg_cpu_manufacturer', $l_cpu['manufacturer']);
                $_POST['C__CATG__CPU_FREQUENCY'] = $l_cpu['speed'];
                $_POST['C__CATG__CPU_TYPE'] = isys_import::check_dialog('isys_catg_cpu_type', $l_cpu['description']);
                $_POST['C__CATG__CPU_TITLE'] = $l_cpu['name'];

                // Core detection.
                if (stristr($l_cpu['name'], 'dual')) {
                    $_POST['C__CATG__CPU_CORES'] = 2;
                } elseif (stristr($l_cpu['name'], 'quad')) {
                    $_POST['C__CATG__CPU_CORES'] = 4;
                } else {
                    $_POST['C__CATG__CPU_CORES'] = 1;
                }

                $l_ids[] = $this->save_element($l_cat, $l_status, true);
            }
        }

        return $l_ids;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param    integer $p_cat_level
     * @param    integer $p_newRecStatus
     * @param    string  $p_title
     * @param    string  $p_manufacturerID
     * @param    integer $p_frequency
     * @param    integer $p_typeID
     * @param    string  $p_description
     * @param    integer $p_cores
     * @param    integer $p_frequency_unit
     *
     * @return   mixed
     * @throws isys_exception_dao
     * @author   Dennis Bluemer <dbluemer@i-doit.org>
     * @version  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_title, $p_manufacturerID, $p_frequency, $p_typeID, $p_description, $p_cores = null, $p_frequency_unit = null)
    {
        // @see  ID-6784  Do not force the "GHz" unit, if none is given.
        $l_strSql = 'UPDATE isys_catg_cpu_list SET
			isys_catg_cpu_list__title = ' . $this->convert_sql_text($p_title) . ',
			isys_catg_cpu_list__isys_catg_cpu_manufacturer__id = ' . $this->convert_sql_id($p_manufacturerID) . ',
			isys_catg_cpu_list__frequency  = ' . $this->convert_sql_text(isys_convert::frequency($p_frequency, $p_frequency_unit)) . ',
			isys_catg_cpu_list__isys_catg_cpu_type__id = ' . $this->convert_sql_id($p_typeID) . ',
			isys_catg_cpu_list__description = ' . $this->convert_sql_text($p_description) . ',
			isys_catg_cpu_list__cores = ' . $this->convert_sql_int($p_cores) . ',
			isys_catg_cpu_list__status = ' . $this->convert_sql_id($p_newRecStatus) . ',
			isys_catg_cpu_list__isys_frequency_unit__id = ' . $this->convert_sql_id($p_frequency_unit) . '
			WHERE isys_catg_cpu_list__id = ' . $this->convert_sql_id($p_cat_level) . ';';

        return $this->update($l_strSql) && $this->apply_update();
    }

    /**
     * Save element method.
     *
     * @param    integer $p_cat_level
     * @param    integer $p_intOldRecStatus
     * @param    boolean $p_create
     *
     * @return   mixed  Integer with last inserted ID or boolean false.
     * @throws isys_exception_dao
     * @version  Niclas Potthast <npotthast@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        if (isys_glob_get_param(C__CMDB__GET__CATLEVEL) == 0 && isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW') &&
            isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__SAVE) {
            $p_create = true;
        }

        $l_bRet = $l_id = null;
        $_POST['C__CATG__CPU_FREQUENCY'] = isys_helper::filter_number($_POST['C__CATG__CPU_FREQUENCY']);

        $l_catdata = $this->get_result()
            ->__to_array();
        $p_intOldRecStatus = $l_catdata["isys_catg_cpu_list__status"];

        if ($p_create) {
            // Overview page and no input was given.
            if (isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW') && $_POST['C__CATG__CPU_TITLE'] == "" && $_POST['C__CATG__CPU_MANUFACTURER'] == -1 &&
                $_POST['C__CATG__CPU_FREQUENCY'] == null && $_POST['C__CATG__CPU_TYPE'] == -1) {
                return null;
            }

            $l_nQuantity = $_POST["C__CATG__CPU_NUMBER2CREATE"] ?? 1;

            if ($l_nQuantity > 0) {
                for ($i = 1; $i <= $l_nQuantity; $i++) {
                    $l_id = $this->create(
                        $_GET[C__CMDB__GET__OBJECT],
                        C__RECORD_STATUS__NORMAL,
                        $_POST['C__CATG__CPU_TITLE'],
                        $_POST['C__CATG__CPU_MANUFACTURER'],
                        $_POST['C__CATG__CPU_FREQUENCY'],
                        $_POST['C__CATG__CPU_TYPE'],
                        $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()],
                        $_POST['C__CATG__CPU_CORES'],
                        $_POST['C__CATG__CPU_FREQUENCY_UNIT']
                    );

                    if ($l_id != false) {
                        $this->m_strLogbookSQL = $this->get_last_query();
                    }
                }

                $p_cat_level = null;

                return $l_id;
            }
        } else {
            $l_bRet = $this->save(
                $l_catdata['isys_catg_cpu_list__id'],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__CPU_TITLE'],
                $_POST['C__CATG__CPU_MANUFACTURER'],
                $_POST['C__CATG__CPU_FREQUENCY'],
                $_POST['C__CATG__CPU_TYPE'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()],
                $_POST['C__CATG__CPU_CORES'],
                $_POST['C__CATG__CPU_FREQUENCY_UNIT']
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        return $l_bRet;
    }

    /**
     * Executes the query to create the category entry referenced by isys_obj__id $p_objID.
     *
     * @param    integer $p_objID
     * @param    integer $p_newRecStatus
     * @param    string  $p_title
     * @param    integer $p_manufacturerID
     * @param    integer $p_frequency
     * @param    integer $p_typeID
     * @param    string  $p_description
     * @param    integer $p_cores
     * @param    integer $p_frequency_unit
     *
     * @return   mixed    The newly created ID as integer or boolean false.
     * @author   Dennis Bluemer <dbluemer@i-doit.org>
     * @version  Van Quyen Hoang <qhoang@synetics.de>
     * @throws isys_exception_dao
     */
    public function create($p_objID, $p_newRecStatus, $p_title, $p_manufacturerID, $p_frequency, $p_typeID, $p_description, $p_cores = null, $p_frequency_unit = null)
    {
        // @see  ID-6784  Do not force the "GHz" unit, if none is given.
        $l_strSql = 'INSERT INTO isys_catg_cpu_list SET
			isys_catg_cpu_list__title = ' . $this->convert_sql_text($p_title) . ',
			isys_catg_cpu_list__isys_catg_cpu_manufacturer__id = ' . $this->convert_sql_id($p_manufacturerID) . ',
			isys_catg_cpu_list__frequency  = ' . $this->convert_sql_text(isys_convert::frequency($p_frequency, $p_frequency_unit)) . ',
			isys_catg_cpu_list__isys_catg_cpu_type__id = ' . $this->convert_sql_id($p_typeID) . ',
			isys_catg_cpu_list__description = ' . $this->convert_sql_text($p_description) . ',
			isys_catg_cpu_list__cores = ' . $this->convert_sql_int($p_cores) . ',
			isys_catg_cpu_list__status = ' . $this->convert_sql_id($p_newRecStatus) . ',
			isys_catg_cpu_list__isys_obj__id = ' . $this->convert_sql_id($p_objID) . ',
			isys_catg_cpu_list__isys_frequency_unit__id = ' . $this->convert_sql_id($p_frequency_unit) . ';';

        if ($this->update($l_strSql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        }

        return false;
    }

    /**
     * Builds an array with minimal requirement for the sync function.
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_import_array($p_data)
    {
        $l_manufacturer = null;
        $l_type = null;

        if (!empty($p_data['manufacturer'])) {
            $l_manufacturer = isys_import_handler::check_dialog('isys_catg_cpu_manufacturer', $p_data['manufacturer']);
        }

        if (!empty($p_data['type'])) {
            $l_type = isys_import_handler::check_dialog('isys_catg_cpu_type', $p_data['type']);
        }

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'title'          => [
                    'value' => $p_data['title']
                ],
                'manufacturer'   => [
                    'value' => $l_manufacturer
                ],
                'type'           => [
                    'value' => $l_type
                ],
                'frequency'      => [
                    'value' => $p_data['frequency']
                ],
                'frequency_unit' => [
                    'value' => $p_data['frequency_unit']
                ],
                'cores'          => [
                    'value' => $p_data['cores']
                ],
                'description'    => [
                    'value' => $p_data['description']
                ]
            ]
        ];
    }

    /**
     * Compares category data for import.
     *
     * @param  array    $p_category_data_values
     * @param  array    $p_object_category_dataset
     * @param  array    $p_used_properties
     * @param  array    $p_comparison
     * @param  integer  $p_badness
     * @param  integer  $p_mode
     * @param  integer  $p_category_id
     * @param  string   $p_unit_key
     * @param  array    $p_category_data_ids
     * @param  mixed    $p_local_export
     * @param  boolean  $p_dataset_id_changed
     * @param  integer  $p_dataset_id
     * @param  isys_log $p_logger
     * @param  string   $p_category_name
     * @param  string   $p_table
     * @param  mixed    $p_cat_multi
     * @param null      $p_category_type_id
     * @param null      $p_category_ids
     * @param null      $p_object_ids
     * @param null      $p_already_used_data_ids
     */
    public function compare_category_data(
        /** @noinspection PhpUnusedParameterInspection */
        &$p_category_data_values,
        &$p_object_category_dataset,
        &$p_used_properties,
        &$p_comparison,
        &$p_badness,
        &$p_mode,
        &$p_category_id,
        &$p_unit_key,
        &$p_category_data_ids,
        &$p_local_export,
        &$p_dataset_id_changed,
        &$p_dataset_id,
        &$p_logger,
        &$p_category_name = null,
        &$p_table = null,
        &$p_cat_multi = null,
        &$p_category_type_id = null,
        &$p_category_ids = null,
        &$p_object_ids = null,
        &$p_already_used_data_ids = null
    ) {
        $l_title = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['title']['value'] ? $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['title']['value'] : $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['type']['title_lang'];
        $l_type = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['type']['value'];
        $l_frequency = strval($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['frequency']['value_converted']);
        if ($l_frequency === '') {
            $l_frequency = '0';
        }

        // Iterate through local data sets:
        foreach ($p_object_category_dataset as $l_dataset_key => $l_dataset) {
            $p_dataset_id_changed = false;
            $p_dataset_id = $l_dataset[$p_table . '__id'];

            if (isset($p_already_used_data_ids[$p_dataset_id])) {
                // ID has already been used skip entry.
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                continue;
            }

            // Test the category data identifier:
            if ($p_category_data_values['data_id'] !== null) {
                if ($p_mode === isys_import_handler_cmdb::C__USE_IDS && $p_category_data_values['data_id'] !== $p_dataset_id) {
                    //$p_logger->debug('Category data identifier is different.');
                    $p_badness[$p_dataset_id]++;
                    $p_dataset_id_changed = true;
                    if ($p_mode === isys_import_handler_cmdb::C__USE_IDS) {
                        continue;
                    }
                }
            }

            // converted frequency of the local and imported frequency
            $l_frequency_local = ceil(isys_convert::frequency(
                $l_dataset['isys_catg_cpu_list__frequency'],
                $l_dataset['isys_catg_cpu_list__isys_frequency_unit__id'],
                C__CONVERT_DIRECTION__BACKWARD
            ) * 10);
            $l_frequency_import = ceil(isys_convert::frequency($l_frequency, $l_dataset['isys_catg_cpu_list__isys_frequency_unit__id'], C__CONVERT_DIRECTION__BACKWARD) * 10);

            if ($l_dataset['isys_catg_cpu_list__title'] === $l_title && $l_dataset['isys_catg_cpu_list__isys_catg_cpu_type__id'] === $l_type &&
                $l_frequency_local === $l_frequency_import) {
                // Check properties
                // We found our dataset
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;

                return;
            }

            if (strtolower($l_dataset['isys_catg_cpu_list__title']) === $l_title && $l_dataset['isys_catg_cpu_list__frequency'] !== $l_frequency) {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY][$l_dataset_key] = $p_dataset_id;
            } else {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
            }
        }
    }

    /**
     * Dynamic property handling for retrieving the total number of cpu cores.
     *
     * @param  array $data
     *
     * @return integer
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getTotalCpuCores(array $data)
    {
        $objId = ($data['isys_catg_cpu_list__isys_obj__id'] ?: ($data['isys_obj__id'] ?: null));

        if ($objId === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        $result = $this->get_data(null, $objId);
        $cores = 0;
        while ($cpuData = $result->get_row()) {
            $cores += (int) $cpuData['isys_catg_cpu_list__cores'];
        }
        return $cores;
    }

    /**
     * Dynamic property handling for retrieving the total number of cpus.
     *
     * @param  array $data
     *
     * @return integer
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getTotalCpuSockets(array $data)
    {
        $objId = ($data['isys_catg_cpu_list__isys_obj__id'] ?: ($data['isys_obj__id'] ?: null));

        if ($objId === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        $query = 'SELECT COUNT(isys_catg_cpu_list__id) AS cpuAmount 
          FROM isys_catg_cpu_list WHERE isys_catg_cpu_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' 
          AND isys_catg_cpu_list__isys_obj__id = ' . $this->convert_sql_id($objId);

        return $this->retrieve($query)->get_row_value('cpuAmount');
    }

    /**
     * Abstract method for retrieving the dynamic properties of every category dao.
     *
     * @return array
     */
    protected function dynamic_properties()
    {
        return [
            '_frequency' =>   new DynamicProperty(
                'LC__CMDB__CATG__CPU',
                'isys_catg_cpu_list__id',
                'isys_catg_cpu_list',
                [
                    $this,
                    'dynamic_property_callback_frequency'
                ]
            ),
            '_total_cores' => new DynamicProperty(
                'LC__CMDB__CATG__CPU__TOTAL_CORES',
                'isys_catg_cpu_list__isys_obj__id',
                'isys_catg_cpu_list',
                [
                    $this,
                    'getTotalCpuCores'
                ]
            ),
            '_total_cpus' => new DynamicProperty(
                'LC__CMDB__CATG__CPU__TOTAL_CPUS',
                'isys_catg_cpu_list__isys_obj__id',
                'isys_catg_cpu_list',
                [
                    $this,
                    'getTotalCpuSockets'
                ]
            )
        ];
    }

    /**
     * Get data method for retrieving data.
     *
     * @param  int    $p_catg_list_id
     * @param  int    $p_obj_id
     * @param  string $p_condition
     * @param  array  $p_filter
     * @param  int    $p_status
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_cpu_list
			INNER JOIN isys_obj ON isys_catg_cpu_list__isys_obj__id = isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			LEFT JOIN isys_catg_cpu_manufacturer ON isys_catg_cpu_manufacturer__id = isys_catg_cpu_list__isys_catg_cpu_manufacturer__id
			LEFT JOIN isys_catg_cpu_type ON isys_catg_cpu_type__id = isys_catg_cpu_list__isys_catg_cpu_type__id
			LEFT JOIN isys_frequency_unit ON isys_catg_cpu_list__isys_frequency_unit__id = isys_frequency_unit__id
			WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter) . ' ';

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= ' AND isys_catg_cpu_list__id = ' . $this->convert_sql_id($p_catg_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= ' AND isys_catg_cpu_list__status = ' . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Creates the condition to the object table.
     *
     * @param  int|array $p_obj_id
     * @param  string    $p_alias
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        if (empty($p_obj_id)) {
            return '';
        }

        if (is_array($p_obj_id)) {
            return ' AND isys_catg_cpu_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id);
        }

        return ' AND isys_catg_cpu_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
    }

    /**
     * Create logbook entry for each cpu.
     *
     * @param  string $p_strConst
     * @param  string $p_lc_category
     * @param  array  $p_changes
     */
    public function logbook_update($p_strConst, $p_lc_category, $p_changes)
    {
        $l_mod_event_manager = isys_event_manager::getInstance();
        $l_count = (int)$_POST['C__CATG__CPU_NUMBER2CREATE'];

        if ($l_count > 1) {
            for ($i = 1; $i <= $l_count; $i++) {
                $l_mod_event_manager->triggerCMDBEvent(
                    $p_strConst,
                    $this->get_strLogbookSQL(),
                    $_GET[C__CMDB__GET__OBJECT],
                    $_GET[C__CMDB__GET__OBJECTTYPE],
                    $p_lc_category,
                    $p_changes,
                    $_POST["LogbookCommentary"]
                );
            }
        } else {
            $l_mod_event_manager->triggerCMDBEvent(
                $p_strConst,
                $this->get_strLogbookSQL(),
                $_GET[C__CMDB__GET__OBJECT],
                $_GET[C__CMDB__GET__OBJECTTYPE],
                $p_lc_category,
                $p_changes,
                $_POST["LogbookCommentary"]
            );
        }
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'title'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CPU_TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_cpu_list__title'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__CPU_TITLE'
                ]
            ]),
            'manufacturer'   => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__STORAGE_CONTROLLER_MANUFACTURER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Manufacturer'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_cpu_list__isys_catg_cpu_manufacturer__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_catg_cpu_manufacturer',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_catg_cpu_manufacturer',
                        'isys_catg_cpu_manufacturer__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_cpu_manufacturer__title FROM isys_catg_cpu_list
                            INNER JOIN isys_catg_cpu_manufacturer ON isys_catg_cpu_manufacturer__id = isys_catg_cpu_list__isys_catg_cpu_manufacturer__id',
                        'isys_catg_cpu_list',
                        'isys_catg_cpu_list__id',
                        'isys_catg_cpu_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_cpu_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_cpu_list', 'LEFT', 'isys_catg_cpu_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_cpu_manufacturer',
                            'LEFT',
                            'isys_catg_cpu_list__isys_catg_cpu_manufacturer__id',
                            'isys_catg_cpu_manufacturer__id'
                        )
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__CPU_MANUFACTURER',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_catg_cpu_manufacturer'
                    ]
                ]
            ]),
            'type'           => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CPU_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Type'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_cpu_list__isys_catg_cpu_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_catg_cpu_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_catg_cpu_type',
                        'isys_catg_cpu_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_cpu_type__title FROM isys_catg_cpu_list
                            INNER JOIN isys_catg_cpu_type ON isys_catg_cpu_type__id = isys_catg_cpu_list__isys_catg_cpu_type__id',
                        'isys_catg_cpu_list',
                        'isys_catg_cpu_list__id',
                        'isys_catg_cpu_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_cpu_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_cpu_list', 'LEFT', 'isys_catg_cpu_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_cpu_type',
                            'LEFT',
                            'isys_catg_cpu_list__isys_catg_cpu_type__id',
                            'isys_catg_cpu_type__id'
                        )
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__CPU_TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_catg_cpu_type'
                    ]
                ]
            ]),
            'frequency'      => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__FREQUENCY',
                    C__PROPERTY__INFO__DESCRIPTION => 'CPU frequency'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_cpu_list__frequency',
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_catg_cpu_list__cores, \'@\', ROUND(isys_catg_cpu_list__frequency / isys_frequency_unit__factor), \' \', isys_frequency_unit__title)
                            FROM isys_catg_cpu_list
                            INNER JOIN isys_frequency_unit ON isys_frequency_unit__id = isys_catg_cpu_list__isys_frequency_unit__id',
                        'isys_catg_cpu_list',
                        '',
                        'isys_catg_cpu_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_cpu_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_cpu_list', 'LEFT', 'isys_catg_cpu_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_frequency_unit',
                            'LEFT',
                            'isys_catg_cpu_list__isys_frequency_unit__id',
                            'isys_frequency_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__CPU_FREQUENCY',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-medium'
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['frequency']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'frequency_unit'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true,
                ]
            ]),
            'frequency_unit' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CPU_FREQUENCY_UNIT',
                    C__PROPERTY__INFO__DESCRIPTION => 'frequency unit'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_cpu_list__isys_frequency_unit__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_frequency_unit',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_frequency_unit',
                        'isys_frequency_unit__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_frequency_unit__title FROM isys_catg_cpu_list
                            INNER JOIN isys_frequency_unit ON isys_frequency_unit__id = isys_catg_cpu_list__isys_frequency_unit__id',
                        'isys_catg_cpu_list',
                        'isys_catg_cpu_list__id',
                        'isys_catg_cpu_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_cpu_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_cpu_list', 'LEFT', 'isys_catg_cpu_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_frequency_unit',
                            'LEFT',
                            'isys_catg_cpu_list__isys_frequency_unit__id',
                            'isys_frequency_unit__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__CPU_FREQUENCY_UNIT',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'   => 'isys_frequency_unit',
                        'order'        => 'isys_frequency_unit__sort',
                        'p_bSort'      => false,
                        'p_bDbFieldNN' => '1',
                        'p_strClass'   => 'input-mini',
                    ],
                    C__PROPERTY__UI__DEFAULT => 3,
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__REPORT     => true,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ],
                C__PROPERTY__CHECK => [
                    C__PROPERTY__CHECK__MANDATORY => true
                ]
            ]),
            'cores'          => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CPU_CORES',
                    C__PROPERTY__INFO__DESCRIPTION => 'CPU cores'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_cpu_list__cores',
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__CPU_CORES',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-mini'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => true,
                ]
            ]),
            'description'    => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_cpu_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__CPU', 'C__CATG__CPU')
                ]
            ])
        ];
    }

    /**
     * Sync method for import, export and duplicating.
     *
     * @param  array $p_category_data
     * @param  int   $p_object_id
     * @param  int   $p_status
     *
     * @return mixed
     * @throws isys_exception_dao
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            if ($p_status == isys_import_handler_cmdb::C__CREATE && $p_object_id > 0) {
                return $this->create(
                    $p_object_id,
                    C__RECORD_STATUS__NORMAL,
                    $p_category_data['properties']['title'][C__DATA__VALUE],
                    $p_category_data['properties']['manufacturer'][C__DATA__VALUE],
                    $p_category_data['properties']['frequency'][C__DATA__VALUE],
                    $p_category_data['properties']['type'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    $p_category_data['properties']['cores'][C__DATA__VALUE],
                    $p_category_data['properties']['frequency_unit'][C__DATA__VALUE]
                );
            }

            if ($p_status == isys_import_handler_cmdb::C__UPDATE && $p_category_data['data_id'] > 0) {
                $this->save(
                    $p_category_data['data_id'],
                    C__RECORD_STATUS__NORMAL,
                    $p_category_data['properties']['title'][C__DATA__VALUE],
                    $p_category_data['properties']['manufacturer'][C__DATA__VALUE],
                    $p_category_data['properties']['frequency'][C__DATA__VALUE],
                    $p_category_data['properties']['type'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    $p_category_data['properties']['cores'][C__DATA__VALUE],
                    $p_category_data['properties']['frequency_unit'][C__DATA__VALUE]
                );

                return $p_category_data['data_id'];
            }
        }

        return false;
    }
}
