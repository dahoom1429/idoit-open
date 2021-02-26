<?php

/**
 * i-doit
 *
 * DAO: global category for physical network interfaces
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Niclas Potthast <npotthast@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_network_interface extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var string
     */
    protected $m_category = 'network_interface';

    /**
     * Category's constant
     *
     * @var string
     *
     * @fixme No standard behavior!
     */
    protected $m_category_const = 'C__CATG__NETWORK_INTERFACE';

    /**
     * Category's identifier
     *
     * @var int
     *
     * @fixme No standard behavior!
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATG__NETWORK_INTERFACE;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var bool
     */
    protected $m_multivalued = true;

    /**
     * Main table where properties are stored persistently
     *
     * @var string
     *
     * @fixme No standard behavior!
     */
    protected $m_table = 'isys_catg_netp_list';

    /**
     * Category Template
     */
    protected $m_tpl = 'catg__interface_p.tpl';

    /**
     * Category's user interface
     *
     * @var string
     *
     * @fixme No standard behavior!
     */
    protected $m_ui = 'isys_cmdb_ui_category_g_network';

    /**
     * @param $p_title
     * @param $p_obj_id
     *
     * @return isys_component_dao_result
     */
    public function get_interface_by_title($p_title, $p_obj_id)
    {
        return $this->get_data(null, $p_obj_id, "AND (isys_catg_netp_list__title = '{$p_title}')");
    }

    /**
     * @param int  $p_cat_level
     * @param int  $p_intOldRecStatus
     * @param null $p_id
     *
     * @return int|null
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        if (isys_glob_get_param(C__CMDB__GET__CATLEVEL) > 0) {
            $l_catdata = $this->get_data($_GET[C__CMDB__GET__CATLEVEL])
                ->__to_array();
        } else {
            $l_catdata = $this->get_result()
                ->__to_array();
        }

        if (!empty($p_id) && $p_id > 0) {
            $l_catdata['isys_catg_netp_list__id'] = $p_id;
        }

        if ($p_create || (!isset($l_catdata['isys_catg_netp_list__id']) || !$l_catdata['isys_catg_netp_list__id'])) {
            $l_catdata['isys_catg_netp_list__id'] = $this->create_connector($this->m_table, $this->m_object_id);
        }

        if ($l_catdata['isys_catg_netp_list__id']) {
            if ($this->save(
                $l_catdata['isys_catg_netp_list__id'],
                $_POST['C__CATG__INTERFACE_P_TITLE'],
                $_POST['C__CATG__INTERFACE_P_MANUFACTURER'],
                $_POST['C__CATG__INTERFACE_P_MODEL'],
                $_POST['C__CATG__INTERFACE_P_SERIAL'],
                $_POST['C__CATG__INTERFACE_P_SLOTNUMBER'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            )) {
                $this->m_strLogbookSQL = $this->get_last_query();

                return null;
            }
        }

        return -1;
    }

    /**
     * Create method
     *
     * @param int   $p_obj_id
     * @param       $p_title
     * @param       $p_manufacturer
     * @param       $p_model
     * @param       $p_serial
     * @param       $p_slot
     * @param       $p_description
     * @param int   $p_status
     *
     * @return bool|int|mixed
     * @throws isys_exception_dao
     */
    public function create($p_obj_id, $p_title, $p_manufacturer, $p_model, $p_serial, $p_slot, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_strSql = "INSERT INTO isys_catg_netp_list SET
			isys_catg_netp_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id) . ",
			isys_catg_netp_list__title = " . $this->convert_sql_text($p_title) . ",
			isys_catg_netp_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_netp_list__isys_iface_manufacturer__id = " . $this->convert_sql_id($p_manufacturer) . ",
			isys_catg_netp_list__isys_iface_model__id = " . $this->convert_sql_id($p_model) . ",
			isys_catg_netp_list__serial = " . $this->convert_sql_text($p_serial) . ",
			isys_catg_netp_list__slotnumber = " . $this->convert_sql_text($p_slot) . ",
			isys_catg_netp_list__status = " . $this->convert_sql_int($p_status) . ";";

        if ($this->update($l_strSql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        }

        return false;
    }

    /**
     * Save interface.
     *
     * @param  integer $p_id
     * @param  string  $p_title
     * @param  integer $p_manufacturer
     * @param  integer $p_model
     * @param  integer $p_serial
     * @param  integer $p_slot
     * @param  string  $p_description
     * @param  integer $p_status
     *
     * @return boolean
     */
    public function save($p_id, $p_title, $p_manufacturer, $p_model, $p_serial, $p_slot, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_strSql = 'UPDATE isys_catg_netp_list SET
            isys_catg_netp_list__title = ' . $this->convert_sql_text($p_title) . ',
            isys_catg_netp_list__description = ' . $this->convert_sql_text($p_description) . ',
            isys_catg_netp_list__isys_iface_manufacturer__id = ' . $this->convert_sql_id($p_manufacturer) . ',
            isys_catg_netp_list__isys_iface_model__id = ' . $this->convert_sql_id($p_model) . ',
            isys_catg_netp_list__serial = ' . $this->convert_sql_text($p_serial) . ',
            isys_catg_netp_list__slotnumber = ' . $this->convert_sql_text($p_slot) . ',
            isys_catg_netp_list__status = ' . $this->convert_sql_id($p_status) . '
            WHERE isys_catg_netp_list__id = ' . $this->convert_sql_id($p_id) . ';';

        return $this->update($l_strSql) && $this->apply_update();
    }

    /**
     * @return integer
     *
     * @param $p_cat_level level to save, standard 0
     *                     (usage by reason of universality)
     * @param &$p_new_id   returns the __id of the new record
     *
     * @version Niclas Potthast <npotthjast@i-doit.org> - 2006-03-03
     * @desc    save global category netp element, return NULL
     */
    public function attachObjects(array $p_post)
    {
        $p_new_id = -1;
        $l_intRetCode = 3;
        $l_object_id = (!empty($p_object_id)) ? $p_object_id : $_GET[C__CMDB__GET__OBJECT];

        $l_strSql = "INSERT INTO isys_catg_netp_list SET
            isys_catg_netp_list__isys_obj__id = " . $this->convert_sql_id($l_object_id) . ",
            isys_catg_netp_list__title = '',
            isys_catg_netp_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__BIRTH) . ";";

        $this->m_strLogbookSQL = $l_strSql;

        if ($this->update($l_strSql) && $this->apply_update()) {
            $l_intRetCode = null;
            $p_new_id = $this->get_last_insert_id();
        }

        return (!empty($p_object_id)) ? $p_new_id : $l_intRetCode;
    }

    /**
     * Import-Handler
     *
     * @author Dennis Stuecken <dstuecken@syneics.de>
     */
    public function import($p_data, $p_object_id)
    {
        $l_status = -1;
        $l_cat = -1;
        $l_list_id = null;

        if (is_array($p_data)) {
            foreach ($p_data as $l_key => $l_data) {
                $l_list_id = $this->create_connector($this->get_table(), $p_object_id);

                if ($l_list_id > 0) {
                    $_POST['C__CATG__INTERFACE_P_TITLE'] = $l_data["name"];
                    $_POST['C__CATG__INTERFACE_P_SLOTNUMBER'] = $l_key;
                    $_POST['C__CATG__INTERFACE_P_MANUFACTURER'] = isys_import::check_dialog("isys_iface_manufacturer", $l_data["manufacturer"]);

                    $this->save_element($l_cat, $l_status, $l_list_id);
                }

                // Create port and port categories with information for ips
                $l_catg_dao = new isys_cmdb_dao_category_g_network_port($this->m_db);

                $l_catg_dao->init($this->get_result());
                $_POST["C__CATG__PORT__INTERFACE"] = $l_list_id;

                $l_catg_dao->import($l_data, $p_object_id);
            }
        }

        return $l_list_id;
    }

    /**
     * Builds an array with minimal requirement for the sync function
     *
     * @param $p_data
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_import_array($p_data)
    {
        if (!empty($p_data['manufacturer'])) {
            $l_manufacturer = isys_import_handler::check_dialog('isys_iface_manufacturer', $p_data['manufacturer']);
        } else {
            $l_manufacturer = null;
        }

        if (!empty($p_data['model'])) {
            $l_model = isys_import_handler::check_dialog('isys_iface_model', $p_data['model']);
        } else {
            $l_model = null;
        }

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'title'        => [
                    'value' => $p_data['title']
                ],
                'manufacturer' => [
                    'value' => $l_manufacturer
                ],
                'type'         => [
                    'value' => $l_model
                ],
                'serial'       => [
                    'value' => $p_data['serial']
                ],
                'slot'         => [
                    'value' => $p_data['slot']
                ],
                'description'  => [
                    'value' => $p_data['description']
                ]
            ]
        ];
    }

    /**
     * A method, which bundles the handle_ajax_request and handle_preselection.
     *
     * @todo   LF: I could not find any usage of this. Check if this method is called and, if not, remove it.
     *
     * @param  integer $p_context
     * @param  array   $p_parameters
     *
     * @return array|string
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function object_browser($p_context, array $p_parameters)
    {
        $language = isys_application::instance()->container->get('language');

        switch ($p_context) {
            case isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST:
                // Handle Ajax-Request.
                $l_return = [];

                $l_obj = isys_cmdb_dao_category_g_network_interface::instance($this->m_db);
                $l_objects = $l_obj->get_data(null, $_GET[C__CMDB__GET__OBJECT]);

                if ($l_objects->num_rows() > 0) {
                    while ($l_row = $l_objects->get_row()) {
                        $l_return[] = [
                            '__checkbox__'                                                  => $l_row["isys_catg_netp_list__id"],
                            $language->get('LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE') => $l_row["isys_catg_netp_list__title"]
                        ];
                    }
                }

                return json_encode($l_return);

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PREPARATION:
                // Preselection
                $l_return = [
                    'category' => [],
                    'first'    => [],
                    'second'   => []
                ];

                $p_preselection = $p_parameters['preselection'];

                // When we get a JSON string, we modify it to an comma separated list.
                if (isys_format_json::is_json($p_preselection)) {
                    $p_preselection = isys_format_json::decode($p_preselection);
                }

                $p_preselection = (array)$p_preselection;

                if (!empty($p_preselection)) {
                    $l_sql = "SELECT isys_obj__id, isys_obj__isys_obj_type__id, isys_catg_netp_list__id, isys_catg_netp_list__title, isys_obj_type__title 
                        FROM isys_catg_netp_list
                        LEFT JOIN isys_obj ON isys_obj__id = isys_catg_netp_list__isys_obj__id
                        LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
                        WHERE isys_catg_netp_list__id " . $this->prepare_in_condition($p_preselection) . ";";

                    $l_res = $this->retrieve($l_sql);

                    while ($l_row = $l_res->get_row()) {
                        // @see  ID-6220  Also return a 'first' selection.
                        $l_preselection['first'][] = $l_row['isys_obj__id'];

                        $l_return['second'][] = [
                            $l_row['isys_catg_netp_list__id'],
                            $l_row['isys_catg_netp_list__title'],
                            $language->get($l_row['isys_obj_type__title']),
                        ];
                    }
                }

                return $l_return;

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PRESELECTION:
                // @see  ID-5688  New callback case.
                $preselection = [];

                if (is_array($p_parameters['dataIds']) && count($p_parameters['dataIds'])) {
                    foreach ($p_parameters['dataIds'] as $dataId) {
                        $categoryRow = $this->get_data($dataId)->get_row();

                        $preselection[] = [
                            $categoryRow['isys_catg_netp_list__id'],
                            $categoryRow['isys_catg_netp_list__title'],
                            $categoryRow['isys_obj__title'],
                            $language->get($categoryRow['isys_obj_type__title'])
                        ];
                    }
                }

                return [
                    'header' => [
                        '__checkbox__',
                        $language->get('LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE'),
                        $language->get('LC__UNIVERSAL__OBJECT_TITLE'),
                        $language->get('LC__UNIVERSAL__OBJECT_TYPE')
                    ],
                    'data'   => $preselection
                ];
        }
    }

    /**
     * Formats the title of the object for the object browser.
     *
     * @param   integer $p_id
     * @param   boolean $p_plain
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function format_selection($p_id, $p_plain = false)
    {
        // We need a DAO for the object name.
        $l_dao = isys_cmdb_dao_category_g_network_interface::instance($this->m_db);
        $l_quick_info = new isys_ajax_handler_quick_info();

        $l_row = $l_dao->get_data($p_id)
            ->__to_array();

        $l_object_type = $l_dao->get_objTypeID($l_row["isys_catg_netp_list__isys_obj__id"]);

        if (!empty($p_id)) {
            $l_editmode = ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT || isys_glob_get_param("editMode") == C__EDITMODE__ON ||
                    isys_glob_get_param("edit") == C__EDITMODE__ON || isset($this->m_params["edit"])) && !isset($this->m_params["plain"]);

            $l_title = isys_application::instance()->container->get('language')
                    ->get($l_dao->get_objtype_name_by_id_as_string($l_object_type)) . " >> " .
                $l_dao->get_obj_name_by_id_as_string($l_row["isys_catg_netp_list__isys_obj__id"]) . " >> " . $l_row["isys_catg_netp_list__title"];

            if (!$l_editmode && !$p_plain) {
                return $l_quick_info->get_quick_info($l_row["isys_catg_netp_list__isys_obj__id"], $l_title, C__LINK__OBJECT);
            } else {
                return $l_title;
            }
        }

        return isys_application::instance()->container->get('language')
            ->get("LC__CMDB__BROWSER_OBJECT__NONE_SELECTED");
    }

    /**
     * Compares category data for import.
     *
     * If your unique properties needs them, implement it!
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
     */
    public function compare_category_data(
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
        // Iterate through local data sets:
        foreach ($p_object_category_dataset as $l_dataset_key => $l_dataset) {
            $p_dataset_id_changed = false;
            $p_badness[$p_dataset_id] = 0;
            $p_dataset_id = $l_dataset[$p_table . '__id'];

            if (isset($p_already_used_data_ids[$p_dataset_id])) {
                // Skip it ID has already been used
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                $p_logger->debug('  Dateset ID "' . $p_dataset_id . '" has already been handled. Skipping to next entry.');
                continue;
            }

            // Test the category data identifier:
            if ($p_mode === isys_import_handler_cmdb::C__USE_IDS && $p_category_data_values['data_id'] !== $p_dataset_id) {
                //$p_logger->debug('Category data identifier is different.');
                $p_badness[$p_dataset_id]++;
                $p_dataset_id_changed = true;
                if ($p_mode === isys_import_handler_cmdb::C__USE_IDS) {
                    continue;
                }
            }

            if ($l_dataset['isys_catg_netp_list__title'] != $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['title']['value']) {
                $p_badness[$p_dataset_id]++;
            }
            if ($l_dataset['isys_catg_netp_list__serial'] != $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['serial']['value']) {
                $p_badness[$p_dataset_id]++;
            }
            if ($l_dataset['isys_catg_netp_list__isys_iface_manufacturer__id'] != $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['manufacturer']['value']) {
                $p_badness[$p_dataset_id]++;
            }
            if ($l_dataset['isys_catg_netp_list__isys_iface_model__id'] != $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['model']['value']) {
                $p_badness[$p_dataset_id]++;
            }

            if ($p_badness[$p_dataset_id] == 0) {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;

                return;
            } elseif ($p_badness[$p_dataset_id] > 2) {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                $l_candidate[$l_dataset_key] = $p_dataset_id;
            } elseif ($p_badness[$p_dataset_id] < 3) {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY][$l_dataset_key] = $p_dataset_id;
            }
        }

        // In case we did not find any matching ports
        if (!isset($p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY]) && !empty($l_candidate)) {
            $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY] = $l_candidate;
        }
    }

    /**
     * Returns how many entries exists. The folder always returns 1.
     *
     * @param int|null $p_obj_id
     *
     * @return bool|int
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_count($p_obj_id = null)
    {
        if ($this->get_category_id() == defined_or_default('C__CATG__NETWORK')) {
            if (class_exists('isys_cmdb_dao_category_g_network_port_overview')) {
                // ID-2721  Count the sub-categories to determine if the folder shall be displayed black or grey.
                return parent::get_count($p_obj_id) + isys_cmdb_dao_category_g_network_port_overview::instance($this->m_db)
                        ->get_count($p_obj_id);
            }
        }

        return parent::get_count($p_obj_id);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'title'        => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__LOGBOOK__TITLE'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_netp_list__title',
                    C__PROPERTY__DATA__INDEX  => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_netp_list__title FROM isys_catg_netp_list',
                        'isys_catg_netp_list',
                        'isys_catg_netp_list__id',
                        'isys_catg_netp_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_netp_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__INTERFACE_P_TITLE'
                ]
            ]),
            'manufacturer' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__INTERFACE_P_MANUFACTURER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Manufacturer'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_netp_list__isys_iface_manufacturer__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_iface_manufacturer',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_iface_manufacturer',
                        'isys_iface_manufacturer__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_iface_manufacturer__title
                            FROM isys_catg_netp_list
                            INNER JOIN isys_iface_manufacturer ON isys_iface_manufacturer__id = isys_catg_netp_list__isys_iface_manufacturer__id',
                        'isys_catg_netp_list',
                        'isys_catg_netp_list__id',
                        'isys_catg_netp_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_netp_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_netp_list', 'LEFT', 'isys_catg_netp_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_iface_manufacturer',
                            'LEFT',
                            'isys_catg_netp_list__isys_iface_manufacturer__id',
                            'isys_iface_manufacturer__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__INTERFACE_P_MANUFACTURER',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_iface_manufacturer'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_plus'
                    ]
                ]
            ]),
            'model'        => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__INTERFACE_P_MODEL',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATG__INTERFACE_P_MODEL'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_netp_list__isys_iface_model__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_iface_model',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_iface_model',
                        'isys_iface_model__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_iface_model__title
                            FROM isys_catg_netp_list
                            INNER JOIN isys_iface_model ON isys_iface_model__id = isys_catg_netp_list__isys_iface_model__id',
                        'isys_catg_netp_list',
                        'isys_catg_netp_list__id',
                        'isys_catg_netp_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_netp_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_netp_list', 'LEFT', 'isys_catg_netp_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_iface_model',
                            'LEFT',
                            'isys_catg_netp_list__isys_iface_model__id',
                            'isys_iface_model__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__INTERFACE_P_MODEL',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_iface_model'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_plus'
                    ]
                ]
            ]),
            'serial'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SERIAL',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATG__SERIAL'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_netp_list__serial',
                    C__PROPERTY__DATA__INDEX  => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_netp_list__serial FROM isys_catg_netp_list',
                        'isys_catg_netp_list',
                        'isys_catg_netp_list__id',
                        'isys_catg_netp_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_netp_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__INTERFACE_P_SERIAL'
                ]
            ]),
            'slot'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__SWITCH_COUNT_SLOT',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CATG__SWITCH_COUNT_SLOT'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_netp_list__slotnumber',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_netp_list__slotnumber FROM isys_catg_netp_list',
                        'isys_catg_netp_list',
                        'isys_catg_netp_list__id',
                        'isys_catg_netp_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_netp_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__INTERFACE_P_SLOTNUMBER'
                ]
            ]),
            'description'  => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__LOGBOOK__DESCRIPTION'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_netp_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_netp_list__description FROM isys_catg_netp_list',
                        'isys_catg_netp_list',
                        'isys_catg_netp_list__id',
                        'isys_catg_netp_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_netp_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__NETWORK_INTERFACE', 'C__CATG__NETWORK_INTERFACE')
                ]
            ])
        ];
    }

    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0) {
                        return $this->create(
                            $p_object_id,
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['manufacturer'][C__DATA__VALUE],
                            $p_category_data['properties']['model'][C__DATA__VALUE],
                            $p_category_data['properties']['serial'][C__DATA__VALUE],
                            $p_category_data['properties']['slot'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $this->save(
                            $p_category_data['data_id'],
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['manufacturer'][C__DATA__VALUE],
                            $p_category_data['properties']['model'][C__DATA__VALUE],
                            $p_category_data['properties']['serial'][C__DATA__VALUE],
                            $p_category_data['properties']['slot'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    }
}
