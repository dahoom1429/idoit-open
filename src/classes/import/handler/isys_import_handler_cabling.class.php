<?php

/**
 * @package   i-doit
 * @subpackage
 * @author    Van Quyen Hoang <qhoang@i-doit.org>
 * @version   1.0
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_import_handler_cabling extends isys_import_handler_csv
{

    // Constants for connector creation
    const C__CABLING__INPUT_OUTPUT = 0;
    const C__CABLING__INPUT        = 1;
    const C__CABLING__OUTPUT       = 2;
    const C__CABLING__OBJECT       = 0;
    const C__CABLING__CABLE        = 1;

    // File name of the csv file
    protected $m_log;

    // Html output
    private $m_array_classes = [];

    /**
     * @var null|int
     */
    private $m_cable_type = null;

    /**
     * @var int
     */
    private $m_cabling_objects;

    /**
     * @var int
     */
    private $m_cabling_type;

    /**
     * @var null|int
     */
    private $m_connector_type = null;

    /**
     * Determines the cabling type
     *
     * @var bool
     */
    private $m_create_patch_panels = false;

    /**
     * Determines if output connector from last object should be created or not
     *
     * @var bool
     */
    private $createOutputConnector = false;

    /**
     * @var isys_cmdb_dao_category_g_connector
     */
    private $m_dao;

    /**
     * @var isys_cmdb_dao_category_g_cable
     */
    private $m_dao_cable;

    // Determines whether to check existing objects or not

    /**
     * @var isys_cmdb_dao_cable_connection
     */
    private $m_dao_cable_connection;

    /**
     * @var isys_cmdb_dao_connection
     */
    private $m_dao_connection;

    // Default object type for those objects which are between the start and end object
    private $m_data_import = [];

    // Array which contains all ids which will be imported
    private $m_file_name = '';

    // Determines the cable type in the specific category
    private $m_html_output = '';

    // Determines which wiring system is used for all connectors
    private $m_max_columns = 0;

    // Array with all needed classes
    private $m_smarty_plugin;

    // Objecttype filter
    private $m_typefilter = [];

    // For suggest
    private $m_typefilter_as_string = '';

    // Log
    private $m_wiring_system = null;

    /**
     * @var array
     */
    private $connectorOutput = [];

    /**
     * @var array
     */
    private $connectorInput = [];

    /**
     * Method for getting the html output
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_output()
    {
        return $this->m_html_output;
    }

    // do nothing

    /**
     * Method for loading the list
     *
     * @return isys_import_handler_cabling
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function load_list()
    {
        if (file_exists($this->m_file_name)) {
            if ($this->load_import($this->m_file_name)) {
                unlink($this->m_file_name);
            }
        }

        return $this;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    private function isConnectorOutput($value)
    {
        return in_array($value, $this->connectorOutput);
    }

    /**
     * @param $value
     *
     * @return bool
     */
    private function isConnectorInput($value)
    {
        return in_array($value, $this->connectorInput);
    }

    /**
     *  Reorder data and check if the keywords 'connectorOutput' or 'connectorInput' are set and switch them if the conditions are met.
     */
    private function reorderData()
    {
        $header = $this->m_data[0];

        $switchKeys = $newData = [];
        foreach ($header as $key => $title) {
            if ($key > 2 && $key % 2 === 1 && ($key + 1) % 4 === 0 && $this->isConnectorOutput($title)) {
                $switchKeys[$key] = $key + 2;
                continue;
            }

            if ($key > 2 && $key % 2 === 1 && ($key - 1) % 4 === 0 && !isset($switchKeys[($key-2)]) && $this->isConnectorInput($title)) {
                $switchKeys[($key-2)] = $key;
            }
        }

        if (!empty($switchKeys)) {
            foreach ($this->m_data as $line => $data) {
                foreach ($data as $key => $title) {
                    $newKey = (string)$key;

                    if (isset($switchKeys[$key])) {
                        $newKey = (string)$switchKeys[$key];
                    }
                    if (in_array($key, $switchKeys)) {
                        $newKey = (string)array_search($key, $switchKeys);
                    }
                    $newData[$newKey] = $title;
                }
                $this->m_data[$line] = $newData;
            }
        }
    }

    /**
     * Method for rendering the csv file in a html output
     *
     * @return isys_import_handler_cabling
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function render_list()
    {
        if (is_array($this->m_data)) {
            // List in m_data loaded
            $this->m_html_output = '<table class="mainTable" id="cabling_table">';

            $this->reorderData();

            foreach ($this->m_data as $l_key => $l_line) {
                if (!is_numeric($l_key)) {
                    continue;
                }

                if ($l_key == 0) {
                    // head
                    $this->m_max_columns = is_countable($l_line) ? count($l_line) : 0;
                    if ($this->m_max_columns == 5 || ($this->m_max_columns - 5) % 4 == 0) {
                        $this->m_max_columns++;
                    }
                    $this->render_row($l_line, $l_key, true);
                    $this->render_skipped_row($l_line);
                    $this->render_hidden_row();
                } else {
                    $this->render_row($l_line, $l_key, false);
                }
            }
            $this->m_html_output .= '</table>';
            unset($this->m_data);
        } else {
            // File does not exist
        }

        return $this;
    }

    /**
     * Method for rendering each rows
     *
     * @param      $p_line
     * @param      $p_iterator
     * @param bool $p_head
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function render_row($p_line, $p_iterator, $p_head = false)
    {
        global $g_dirs;
        if (!is_countable($p_line)) {
            return;
        }

        $lineCount = count($p_line);

        if ($p_head) {
            $this->m_html_output .= '<thead><tr >';
            $this->m_html_output .= '<th width="16px"></th>';
            $l_counter_objects = 1;
            $l_counter_connector = 1;
            $l_counter = 0;
            if ($lineCount == 5 || ($lineCount - 5) % 4 == 0) {
                $p_line[$lineCount] = $p_line[1];
            }
            foreach ($p_line as $l_key => $l_title) {
                $l_html_title = '<span>' . $l_title . '</span>';
                $newKey = $l_key;
                if ((($l_key + 1 / 2) % 2.0) > 0) {
                    if ($l_counter != $l_key) {
                        if ($l_counter == ($l_key - 2)) {
                            $dataType = 'connector_output';
                            $hiddenFieldValue = 'connectorOutput';
                        } else {
                            $dataType = 'connector_input';
                            $hiddenFieldValue = 'connectorInput';
                        }
                    } else {
                        if ($l_counter_connector % 2 == 0) {
                            $dataType = 'connector_input';
                            $hiddenFieldValue = 'connectorInput';
                        } else {
                            $dataType = 'connector_output';
                            $hiddenFieldValue = 'connectorOutput';
                        }
                    }
                    $l_counter_connector++;
                } else {
                    if ($l_counter_objects % 2 == 0) {
                        $dataType = 'cabling_cable';
                        $hiddenFieldValue = 'cablingCable';
                    } else {
                        $dataType = 'cabling_object';
                        $hiddenFieldValue = 'cablingObject';
                        if ($l_key > 0) {
                            $l_html_title .= '<img style="margin-left:10px;position:relative;top:3px;cursor:pointer;" src="images/icons/silk/arrow_switch.png" onclick="Cabling.swap_columns(this);">';
                        }
                    }
                    $l_counter_objects++;
                }
                $this->m_html_output .= '<th class="cabling_table_cell_head" data-column="' . $newKey . '" data-type="' . $dataType . '">';

                $this->m_html_output .= $l_html_title;

                $this->m_html_output .= "<input type='hidden' name='csv_row[0][" . $newKey . "]' value='" . $hiddenFieldValue . "'>";
                $this->m_html_output .= '</th>';
                $l_counter++;
            }
            $this->m_html_output .= '<th id="add_button" width="16px;">';
            $this->m_html_output .= '<div onclick="Cabling.add_columns();" title="Verkabelungskette erweitern" style="cursor: pointer;"><img class="vam" src="images/icons/plus-green.gif"></div>';
            $this->m_html_output .= '</th>';

            $this->m_html_output .= '</tr></thead>';
        } else {
            $l_counter_connector = 1;
            $l_counter_objects = 1;
            $l_counter = 0;

            $l_html_output = '<td><button type="button" class="btn" onclick="Cabling.remove_row(' . $p_iterator . ')">' . '<img src="' . $g_dirs['images'] .
                'icons/silk/cross.png" title="Reihe entfernen" />' . '</button></td>';

            if ($lineCount == 5 || ($lineCount - 5) % 4 == 0) {
                $p_line[$lineCount] = null;
            }
            foreach ($p_line as $l_key => $l_title) {
                if ((($l_key + 1 / 2) % 2.0) > 0) {
                    if ($l_counter != $l_key) {
                        if ($l_counter == ($l_key - 2)) {
                            $l_html_output .= $this->render_connector_cell($l_title, $p_iterator, $l_key, self::C__CABLING__OUTPUT, false, -2);
                        } else {
                            $l_html_output .= $this->render_connector_cell($l_title, $p_iterator, $l_key, self::C__CABLING__INPUT, false, 2);
                        }
                    } else {
                        if ($l_counter_connector % 2 == 0) {
                            $l_html_output .= $this->render_connector_cell($l_title, $p_iterator, $l_key, self::C__CABLING__INPUT, false);
                        } else {
                            $l_html_output .= $this->render_connector_cell($l_title, $p_iterator, $l_key, self::C__CABLING__OUTPUT, false);
                        }
                    }
                    $l_counter_connector++;
                } else {
                    // Object / Kabel
                    if ($l_counter_objects % 2 == 0) {
                        // Cable
                        $l_html_output .= $this->render_object_cell($l_title, $p_iterator, $l_key, self::C__CABLING__CABLE, true);
                    } else {
                        // Object
                        if ($l_key == 0) {
                            // Start Object
                            $l_html_output .= $this->render_object_cell($l_title, $p_iterator, $l_key, self::C__CABLING__OBJECT, false, false);
                        } elseif ($l_key < ($this->m_max_columns - 1) && !empty($p_line[$l_key + 4])) {
                            // Next Object exists in the csv file it is a patchpanel
                            $l_html_output .= $this->render_object_cell($l_title, $p_iterator, $l_key, self::C__CABLING__OBJECT);
                        } else {
                            // Next object does not exist in the csv file it is not a patchpanel
                            $l_html_output .= $this->render_object_cell($l_title, $p_iterator, $l_key, self::C__CABLING__OBJECT, false, true);
                        }
                    }
                    $l_counter_objects++;
                }
                $l_counter++;
            }
            $l_html_output .= '</tr>';
            $l_row_start = '<tr id="row_' . $p_iterator . '" class="import_row ' . ($p_iterator % 2 == 0 ? 'CMDBListElementsOdd' : 'CMDBListElementsEven') .
                '" style="cursor:default;">';

            $this->m_html_output .= $l_row_start . $l_html_output;
        }
    }

    /**
     * Method for getting the import log
     *
     * @return mixed
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_import_log()
    {
        return $this->m_log->flush_log(true, false);
    }

    protected function format_row($p_content, &$p_data, $p_object_id = null)
    {
        return;
    }

    /**
     * Method to import the cabling
     *
     * @return bool|void
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function import()
    {
        if (!is_countable($this->m_data_import) || count($this->m_data_import) == 0) {
            return false;
        }

        $this->m_log->notice('Starting cabling the connectors.');

        foreach ($this->m_data_import as $l_data_key => $l_data) {
            $l_counter_objects = 1;
            $l_counter_connector = 1;
            $l_counter = 0;
            foreach ($l_data as $l_key => $l_content) {
                if ((($l_key + 1 / 2) % 2.0) > 0) {
                    if ($l_counter_connector % 2 == 0) {
                        if ((isset($l_data[$l_key]) && $l_data[$l_key] != '-' && $l_data[$l_key] != '')) {
                            if ($l_key > $l_counter) {
                                // (Swapped)
                                $l_cable = $l_data[$l_key - 3];
                            } else {
                                // (Normal)
                                $l_cable = $l_data[$l_key - 1];
                            }
                            $l_current_data = $l_content;
                            $l_current_title = $this->m_data[$l_data_key + 1][$l_key];

                            // Delete connection from output
                            $this->m_dao_cable_connection->delete_cable_connection($l_current_data);
                            // Delete connection from input
                            if (isset($l_rear_connector_data)) {
                                $this->m_dao_cable_connection->delete_cable_connection($l_rear_connector_data);
                            }

                            $cableConnectionId = $this->m_dao_cable_connection->add_cable_connection($l_cable);
                            $l_list_id = $this->m_dao_cable->create_connector('isys_catg_cable_list', $l_cable);

                            if ($this->m_cable_type > 0) {
                                $cableData = $this->m_dao_cable->get_data($l_list_id)
                                    ->get_row();
                                $this->m_dao_cable->save(
                                    $l_list_id,
                                    C__RECORD_STATUS__NORMAL,
                                    $this->m_cable_type,
                                    ($cableData['isys_catg_cable_list__length'] > 0 ? isys_convert::measure(
                                        $cableData['isys_catg_cable_list__length'],
                                        $cableData['isys_catg_cable_list__isys_depth_unit__id'],
                                        C__CONVERT_DIRECTION__BACKWARD
                                    ) : 0),
                                    $cableData['isys_catg_cable_list__isys_cable_colour__id'],
                                    $cableData['isys_catg_cable_list__isys_cable_occupancy__id'],
                                    $cableData['isys_catg_cable_list__max_amount_of_fibers_leads'],
                                    $cableData['isys_catg_cable_list__description'],
                                    $cableData['isys_catg_cable_list__isys_depth_unit__id']
                                );
                            }

                            $this->m_dao_cable_connection->save_connection($l_current_data, $l_rear_connector_data, $cableConnectionId);
                            $this->m_log->notice('Connection between ' . $l_input_object . ' (' . $l_rear_connector_title . ') and ' . $l_ouput_object . ' (' .
                                $l_current_title . ') established.');
                        }
                    } else {
                        $l_rear_connector_data = $l_content;
                        $l_rear_connector_title = $this->m_data[$l_data_key + 1][$l_key];
                    }
                    $l_counter_connector++;
                } else {
                    if ($l_counter_objects % 2 != 0) {
                        $l_input_object = $this->m_data[$l_data_key + 1][$l_key];
                        $l_ouput_object = $this->m_data[$l_data_key + 1][$l_key + 4];
                    }
                    $l_counter_objects++;
                }
                $l_counter++;
            }
        }
        $this->m_log->notice('Cabling was successful.');

        return true;
    }

    private $headers = [];

    /**
     * Prepares the import array and creates all necessary data
     *
     * @return isys_import_handler_cabling|void
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function prepare()
    {
        $this->m_max_columns = isset($this->m_data[0]) && is_countable($this->m_data[0]) ? count($this->m_data[0]) : 0;

        if ($this->m_max_columns == 5 || ($this->m_max_columns - 5) % 4 == 0) {
            $this->m_max_columns++;
        }

        $this->headers = $this->m_data[0];

        foreach ($this->m_data as $l_data_key => $l_data) {
            $l_current_data = $l_data;
            if (!is_numeric($l_data_key)) {
                continue;
            }

            $l_start_object = $l_data[0];
            $l_start_obj_id = $this->m_dao->get_obj_id_by_title($l_start_object, $this->m_typefilter, C__RECORD_STATUS__NORMAL);

            $l_counter_objects = 1;
            $l_counter_connector = 1;
            if ($l_start_obj_id) {
                $l_data[0] = $l_start_obj_id;

                foreach ($l_data as $l_key => $l_title) {
                    //while(list($l_key, $l_title) = each($l_data)){
                    $l_pp_obj_id = false;

//                    if ((($l_key + 1 / 2) % 2.0) > 0) {
                    if ($this->headers[$l_key] === 'connectorOutput' || $this->headers[$l_key] === 'connectorInput') {
                        // Connectors
                        // do nothing
                        $l_connector_key = $l_key;
                    } else {
                        // Objects
                        if ($l_counter_objects % 2 != 0) {
                            if ($l_key == 0) {
                                // Start Object
                                $this->create_input_output($l_data, $l_data_key, $l_key, false, null, self::C__CABLING__OUTPUT, $this->m_cabling_type, 0);
                            } else {
                                if ($l_key < ($this->m_max_columns - 1) && !empty($l_data[$l_key + 4])) {
                                    // Patch panels
                                    $l_pp_obj_id = $this->m_dao->get_obj_id_by_title($l_title, $this->m_typefilter, C__RECORD_STATUS__NORMAL);

                                    if (!$l_pp_obj_id && $this->m_create_patch_panels) {
                                        $this->m_log->notice(isys_application::instance()->container->get('language')
                                                ->get('LC_UNIVERSAL__OBJECT') . " with titel '" . $l_title . "' not found.");
                                        $this->m_log->notice("Mode for creating non existing objects is activated.");
                                        $l_pp_obj_id = $this->m_dao->insert_new_obj($this->m_cabling_objects, false, $l_title, null, C__RECORD_STATUS__NORMAL);

                                        $this->m_log->notice(isys_application::instance()->container->get('language')
                                                ->get('LC_UNIVERSAL__OBJECT') . " '" . $l_title . "'  created with ID '" . $l_pp_obj_id . "'");
                                    } elseif (!$l_pp_obj_id) {
                                        $this->m_log->notice(isys_application::instance()->container->get('language')
                                                ->get('LC_UNIVERSAL__OBJECT') . " with titel '" . $l_title . "' not found.");
                                        $this->m_log->notice("Mode for creating non existing objects is deactivated.");
                                        $this->m_log->notice("Skipping line " . $l_data_key . " from the csv file.");
                                        break;
                                    }

                                    $l_data[$l_key] = $l_pp_obj_id;

                                    $this->create_input_output(
                                        $l_data,
                                        $l_data_key,
                                        $l_key,
                                        false,
                                        $l_connector_key,
                                        self::C__CABLING__INPUT_OUTPUT,
                                        defined_or_default('C__CATG__CONNECTOR'),
                                        $l_last_key
                                    );
                                } elseif (empty($l_data[$l_key + 4])) {
                                    // End Object
                                    $l_end_obj_id = $this->m_dao->get_obj_id_by_title($l_title, $this->m_typefilter, C__RECORD_STATUS__NORMAL);
                                    $l_data[$l_key] = $l_end_obj_id;

                                    if ($l_end_obj_id) {
                                        $this->create_input_output(
                                            $l_data,
                                            $l_data_key,
                                            $l_key,
                                            true,
                                            $l_connector_key,
                                            self::C__CABLING__INPUT_OUTPUT,
                                            $this->m_cabling_type,
                                            $l_last_key
                                        );
                                    /*if($l_connector_key > $l_key){
                                        if(isset($l_data[$l_key-1]))
                                            $this->create_input_output($l_data, $l_data_key, $l_key, $l_connector_key, self::C__CABLING__INPUT_OUTPUT, $this->m_cabling_type);
                                        else $this->create_input_output($l_data, $l_data_key, $l_key, $l_connector_key, self::C__CABLING__OUTPUT, $this->m_cabling_type);
                                    } else{
                                        if(!isset($l_data[$l_key+1]))
                                            $this->create_input_output($l_data, $l_data_key, $l_key, $l_connector_key, self::C__CABLING__INPUT, $this->m_cabling_type);
                                        else $this->create_input_output($l_data, $l_data_key, $l_key, $l_connector_key, self::C__CABLING__INPUT_OUTPUT, $this->m_cabling_type);
                                    }*/
                                    } else {
                                        $this->m_log->notice("End object in line " . ($l_data_key + 1) . " not found. Skipping cabling to '" . $l_title . "'.");
                                    }
                                    $l_pp_obj_id = true;
                                    break;
                                }
                            }
                        } else {
                            // Create cable only if connection exists
                            if (isset($l_data[$l_key + 2]) && !empty($l_data[$l_key + 2])) {
                                $l_create_cable = false;
                                $l_cable_id = false;

                                // Cable object
                                if ($l_title == '-' || $l_title == '') {
                                    $l_title = isys_application::instance()->container->get('language')
                                            ->get('LC__CMDB__OBJTYPE__CABLE') . ' ' . ($this->m_dao->get_last_id_from_table('isys_obj') + 1);
                                } else {
                                    // Check if cable exists with the title
                                    //$l_cable_id = $this->m_dao->get_obj_id_by_title($l_title, C__OBJTYPE__CABLE);
                                    $l_cable_res = $this->m_dao->retrieve('SELECT isys_obj__id FROM isys_obj WHERE isys_obj__title = ' .
                                        $this->m_dao->convert_sql_text($l_title) . '
                                        AND isys_obj__isys_obj_type__id = ' . $this->m_dao->convert_sql_id(defined_or_default('C__OBJTYPE__CABLE')));
                                    if ($l_cable_res->num_rows() > 0) {
                                        while ($l_cable_row = $l_cable_res->get_row()) {
                                            $l_cable_id = $l_cable_row['isys_obj__id'];
                                            $l_cable_connection_data = $this->m_dao_cable_connection->get_cable_connection_by_cable_id($l_cable_id)
                                                ->get_row();
                                            if ($l_cable_connection_data) {
                                                $l_cable_connection_id = $l_cable_connection_data['isys_cable_connection__id'];
                                                $l_connection_info_res = $this->m_dao_cable_connection->get_connection_info($l_cable_connection_id);

                                                if ($l_connection_info_res->num_rows() > 0) {
                                                    $l_check = 2;
                                                    while ($l_row_connector = $l_connection_info_res->get_row()) {
                                                        if ($l_row_connector['isys_catg_connector_list__title'] == $l_data[$l_key + 1] ||
                                                            $l_row_connector['isys_catg_connector_list__title'] == $l_data[$l_key - 1]) {
                                                            $l_check--;
                                                        }
                                                    }

                                                    // Set Flag to create cable
                                                    if ($l_check > 0) {
                                                        $l_create_cable = true;
                                                    } else {
                                                        // Connection already exists
                                                        $l_create_cable = false;
                                                        break;
                                                    }
                                                }
                                            } else {
                                                // We found a cable with the specified title which is not assigned to any connectors
                                                $l_create_cable = false;
                                                break;
                                            }
                                        }
                                    }
                                }

                                // Check if cable already exists and has no connection than use the existing cable else create a new one
                                if ($l_create_cable || !$l_cable_id) {
                                    $l_cable_id = $this->m_dao->insert_new_obj(defined_or_default('C__OBJTYPE__CABLE'), false, $l_title, null, C__RECORD_STATUS__NORMAL);
                                    $this->m_log->notice("Cable created '" . $l_title . "' (" . $l_cable_id . ")");
                                }

                                $l_data[$l_key] = $l_cable_id;
                                $this->m_data[$l_data_key][$l_key] = $l_title;
                            }
                        }
                        $l_counter_objects++;
                    }
                    $l_last_key = $l_key;
                }

                if ($l_pp_obj_id) {
                    $this->m_data_import[] = $l_data;
                }
            }
        }

        if (is_countable($this->m_data_import) && count($this->m_data_import) > 0) {
            $this->m_log->notice('Preparation finished.');
            $this->generate_csv_file();
        } else {
            $this->m_log->notice('No data for cabling found.');
        }

        return $this;
    }

    /**
     * Method for setting the options
     *
     *
     * @param array $p_options
     *
     * @return isys_import_handler_cabling
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_options(array $p_options)
    {
        $this->m_cabling_type = $p_options['cabling_type'];
        $this->m_connector_type = $p_options['connector_type'];
        $this->m_create_patch_panels = $p_options['create_patch_panels'];
        $this->m_cabling_objects = $p_options['cabling_objects'];
        $this->m_cable_type = $p_options['cable_type'];
        $this->m_wiring_system = $p_options['wiring_system'];
        $this->createOutputConnector = $p_options['createOutputConnector'];

        $this->m_typefilter = $p_options['typefilter'];

        if (is_array($this->m_typefilter) && defined('C__OBJTYPE__CABLE')) {
            $l_key = array_search(C__OBJTYPE__CABLE, $this->m_typefilter);
            unset($this->m_typefilter[$l_key]);
        }
        $l_typefilter_as_string = $this->m_dao->get_object_types_by_category(defined_or_default('C__CATG__CABLING'), 'g', true, false);
        $l_key = array_search('C__OBJTYPE__CABLE', $l_typefilter_as_string);
        unset($l_typefilter_as_string[$l_key]);
        $this->m_typefilter_as_string = implode(';', $l_typefilter_as_string);

        return $this;
    }

    /**
     * Gets the correct class for the specified category
     *
     * @param null $p_category_id
     *
     * @return isys_cmdb_dao_category
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function get_class_for_import($p_category_id = null)
    {
        return call_user_func([
            $this->m_array_classes[((!empty($p_category_id)) ? $p_category_id : $this->m_cabling_type)][0],
            'instance'
        ], $this->m_dao->get_database_component());
    }

    /**
     * This method renders the multiedit row
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function render_skipped_row($p_line)
    {
        if (!is_countable($p_line)) {
            return;
        }
        $l_counter = 0;
        $l_counter_objects = 1;
        $l_counter_connector = 1;
        $l_html_output = '<td style="border-bottom:#000000 solid 1px;"></td>';

        if (count($p_line) == 5 || (count($p_line) - 5) % 4 == 0) {
            $p_line[count($p_line)] = null;
        }
        foreach ($p_line as $l_key => $l_title) {
            if ((($l_key + 1 / 2) % 2.0) > 0) {
                // connector
                if ($l_counter != $l_key) {
                    if ($l_counter == ($l_key - 2)) {
                        $l_html_output .= $this->render_connector_cell(isys_application::instance()->container->get('language')
                            ->get('LC__MODULE__IMPORT__CABLING__ALL_CONNECTORS'), 'skip', $l_key, self::C__CABLING__OUTPUT, true, -2);
                    } else {
                        $l_html_output .= $this->render_connector_cell(isys_application::instance()->container->get('language')
                            ->get('LC__MODULE__IMPORT__CABLING__ALL_CONNECTORS'), 'skip', $l_key, self::C__CABLING__INPUT, true, 2);
                    }
                } else {
                    if ($l_counter_connector % 2 == 0) {
                        $l_html_output .= $this->render_connector_cell(isys_application::instance()->container->get('language')
                            ->get('LC__MODULE__IMPORT__CABLING__ALL_CONNECTORS'), 'skip', $l_key, self::C__CABLING__INPUT, true);
                    } else {
                        $l_html_output .= $this->render_connector_cell(isys_application::instance()->container->get('language')
                            ->get('LC__MODULE__IMPORT__CABLING__ALL_CONNECTORS'), 'skip', $l_key, self::C__CABLING__OUTPUT, true);
                    }
                }

                $l_counter_connector++;
            } else {
                // Cable or Object
                if ($l_counter_objects % 2 == 0) {
                    $l_html_output .= $this->render_object_cell(isys_application::instance()->container->get('language')
                        ->get('LC__MODULE__IMPORT__CABLING__ALL_OBJECTS'), 'skip', $l_key, self::C__CABLING__CABLE, true, false, true);
                } else {
                    $l_html_output .= $this->render_object_cell(isys_application::instance()->container->get('language')
                        ->get('LC__MODULE__IMPORT__CABLING__ALL_OBJECTS'), 'skip', $l_key, self::C__CABLING__OBJECT, false, false, true);
                }
                $l_counter_objects++;
            }

            $l_counter++;
        }

        $l_html_output .= '</tr>';
        $l_row_start = '<tr class="' . 'CMDBListElementsOdd' . '" style="cursor:default;">';

        $this->m_html_output .= $l_row_start . $l_html_output;
    }

    /**
     * This method renders the template row for adding a new row
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function render_hidden_row()
    {
        global $g_dirs;

        $l_counter = 0;
        $l_counter_objects = 1;
        $l_counter_connector = 1;
        $l_html_output = '<td><button type="button" class="btn"><img src="' . $g_dirs['images'] . 'icons/silk/cross.png" title="Reihe entfernen" /></button></td>';

        while ($l_counter < $this->m_max_columns) {
            if ((($l_counter + 1 / 2) % 2.0) > 0) {
                // connector
                if ($l_counter_connector % 2 == 0) {
                    $l_html_output .= $this->render_connector_cell('', 'skip2', $l_counter, self::C__CABLING__INPUT);
                } else {
                    $l_html_output .= $this->render_connector_cell('', 'skip2', $l_counter, self::C__CABLING__OUTPUT);
                }
                $l_counter_connector++;
            } else {
                // Cable or Object
                if ($l_counter_objects % 2 == 0) {
                    $l_html_output .= $this->render_object_cell('', 'skip2', $l_counter, self::C__CABLING__CABLE, true, false);
                } else {
                    $l_html_output .= $this->render_object_cell('', 'skip2', $l_counter, self::C__CABLING__OBJECT, false, false);
                }
                $l_counter_objects++;
            }

            $l_counter++;
        }

        $l_html_output .= '</tr>';
        $l_row_start = '<tr id="row_template" style="display:none;" style="cursor:default;">';

        $this->m_html_output .= $l_row_start . $l_html_output;
    }

    /**
     * Method for rendering connector cells
     *
     * @param      $p_title
     * @param bool $p_input
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function render_connector_cell($p_title, $p_row, $p_cell, $p_input_output = null, $p_multiedit = false, $p_key_addition = 0)
    {
        if ($p_input_output === null) {
            $p_input_output = defined_or_default('C__CABLING__INPUT');
        }
        global $g_dirs;

        $l_params = [
            'name'              => 'csv_row[' . $p_row . '][' . $p_cell . ']',
            'id'                => 'row_' . $p_row . '_' . ($p_cell + $p_key_addition),
            'p_strValue'        => $p_title,
            'p_bInfoIconSpacer' => '0',
            'disableInputGroup' => true
        ];
        if ($p_multiedit) {
            $l_params['p_onChange'] = 'Cabling.change_column(this);';
        }

        $l_title = $this->m_smarty_plugin->navigation_edit(isys_application::instance()->template, $l_params);

        if ($p_multiedit) {
            $l_title .= '<div class="input-group-addon input-group-addon-clickable">' . '<img src="' . $g_dirs['images'] .
                'icons/silk/cog.png" title="Optionen" onclick="$(\'title_identifier\').value=this.up(\'td\').down(\'.input\').id;Cabling.set_suffix_format_preselection(this.up(\'td\').down(\'.input\'));popup_open(\'multiedit_options\', 700, 320);$$(\'.suf\').each(function(e){e.appear();})" />' .
                '</div>';
        }

        if ($p_input_output == self::C__CABLING__INPUT) {
            $l_data_type = 'data-type="connector_input"';
        } else {
            $l_data_type = 'data-type="connector_output"';
        }

        if ($p_multiedit) {
            $l_html_output = '<td style="border-bottom:#000000 solid 1px;" data-column="' . ($p_cell + $p_key_addition) . '" data-row="' . $p_row .
                '" class="cabling_table_cell_multiedit" ' . $l_data_type . '>';
        } else {
            $l_html_output = '<td data-column="' . ($p_cell + $p_key_addition) . '" data-row="' . $p_row . '" class="cabling_table_cell_import" ' . $l_data_type . '>';
        }

        $l_html_output .= '<div class="input-group input-size-mini">' . $l_title . '</div>';
        $l_html_output .= '</td>';

        return $l_html_output;
    }

    /**
     * Method for rendering object cells
     *
     * @param      $p_title
     * @param bool $p_is_cable
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function render_object_cell(
        $p_title,
        $p_row,
        $p_cell,
        $p_cable_object = self::C__CABLING__OBJECT,
        $p_is_cable = false,
        $p_last_object = false,
        $p_multiedit = false
    ) {
        global $g_dirs;

        $l_object_id = (!empty($p_title)) ? $this->m_dao->get_obj_id_by_title($p_title, $this->m_typefilter, C__RECORD_STATUS__NORMAL) : null;
        $l_params = [
            'name'              => 'csv_row[' . $p_row . '][' . $p_cell . ']',
            'id'                => 'row_' . $p_row . '_' . $p_cell,
            'p_strValue'        => $p_title,
            'p_bInfoIconSpacer' => '0',
            'disableInputGroup' => true
        ];

        if ($p_multiedit) {
            $l_params['p_onChange'] = 'Cabling.change_column(this);';
            $l_class = 'cabling_table_cell_multiedit';
            $l_style = 'style="border-bottom:#000000 solid 1px;"';
            $l_default_bgcolor = '';
        } else {
            $l_class = 'cabling_table_cell_import';
            if ($p_row % 2 == 0) {
                $l_style = 'style="background:#DEDEDE;"';
                $l_default_bgcolor = 'data-default-background="#DEDEDE"';
            } else {
                $l_style = 'style="background:#EFEFEF;"';
                $l_default_bgcolor = 'data-default-background="#EFEFEF"';
            }

            if ($p_cable_object == self::C__CABLING__OBJECT) {
                if ($l_object_id > 0) {
                    $l_objtype = $this->m_dao->get_objTypeID($l_object_id);

                    if (!in_array($l_objtype, $this->m_typefilter)) {
                        $l_object_id = null;
                    } else {
                        if ($p_row % 2 == 0) {
                            $l_style = 'style="background:#00AB00;"';
                        } else {
                            $l_style = 'style="background:#00CF00;"';
                        }
                    }
                }

                $l_style = rtrim($l_style, '"') . 'border-left:1px solid #000000;border-right:1px solid #000000;"';
            }

            if (!empty($this->m_data[$p_row][$p_cell - 4]) && !empty($this->m_data[$p_row][$p_cell + 4]) && !$p_is_cable && $this->m_create_patch_panels) {
                if ($p_row % 2 == 0) {
                    $l_style = 'style="background:#EFEF00;"';
                } else {
                    $l_style = 'style="background:#FFFF00;"';
                }
            }

            // suggest field does not work properly because the position is not under the input field
            /*if($p_row != 'skip' && $p_row != 'skip2'){
                $l_params['p_strSuggest'] = 'object';
                $l_params['p_strSuggestView'] = 'row_'.$p_row.'_'.$p_cell;
            }*/
            // Suggestion is unusable only in IE because the suggestion list is not correctly positioned
            if ($p_row != 'skip' && $p_row != 'skip2') {
                $l_params['p_strSuggest'] = 'object_with_no_type';
                $l_params['p_strSuggestView'] = 'row_' . $p_row . '_' . $p_cell;
                $l_params["p_strSuggestParameters"] = "parameters: { " . "typeFilter: '" . $this->m_typefilter_as_string . "' " . "}, " .
                    "selectCallback: \"Cabling.check_object($('row_" . $p_row . "_" . $p_cell . "'), false, true)\"";
            }
        }

        $l_title = $this->m_smarty_plugin->navigation_edit(isys_application::instance()->template, $l_params);

        if ($p_multiedit) {
            $l_title .= '<div class="input-group-addon input-group-addon-clickable">' . '<img src="' . $g_dirs['images'] .
                'icons/silk/cog.png" title="Optionen" onclick="$(\'title_identifier\').value=this.up(\'td\').down(\'.input\').id;Cabling.set_suffix_format_preselection(this.up(\'td\').down(\'.input\'));popup_open(\'multiedit_options\', 700, 320);$$(\'.suf\').each(function(e){e.appear();})" />' .
                '</div>';
        } elseif ($p_row == 'skip2' && !$p_is_cable) {
            $l_title .= '<div class="input-group-addon input-group-addon-clickable">' . '<img src="' . $g_dirs['images'] . 'icons/silk/zoom.png" title="Objekt prüfen" />' .
                '</div>';
        } elseif (!$p_is_cable) {
            $l_title .= '<div class="input-group-addon input-group-addon-clickable">' . '<img src="' . $g_dirs['images'] .
                'icons/silk/zoom.png" title="Objekt prüfen" onclick="Cabling.check_object($(\'row_' . $p_row . '_' . $p_cell . '\'), ' . (($p_is_cable) ? 'true' : 'false') .
                ', false)">' . '</div>';
        }

        if ($p_cable_object == self::C__CABLING__OBJECT) {
            $l_data_type = 'data-type="cabling_object"';
        } else {
            $l_data_type = 'data-type="cabling_cable"';
        }

        if (!$p_multiedit && !$l_object_id && $p_title != '' && $p_title != '-' && !$p_is_cable) {
            $l_style = 'style="cursor:default;background:#e77777 url(\'' . $g_dirs['images'] .
                'gradient.png\') repeat-x;border-left:1px solid #000000;border-right:1px solid #000000;"';
            $l_html_output = '<td data-column="' . $p_cell . '" data-row="' . $p_row . '" ' . $l_style . ' ' . $l_data_type . ' class="' . $l_class . '" ' .
                $l_default_bgcolor . '>' . '<div class="input-group input-size-mini">' . $l_title . '</div>' . '</td>';
        } else {
            $l_html_output = '<td ' . $l_style . ' data-column="' . $p_cell . '" data-row="' . $p_row . '" ' . $l_data_type . ' class="' . $l_class . '" ' .
                $l_default_bgcolor . '>' . '<div class="input-group input-size-mini">' . $l_title . '</div>' . '</td>';
        }

        return $l_html_output;
    }

    /**
     * Generates a csv file which contains the imported data
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function generate_csv_file()
    {
        if (is_array($this->m_data)) {
            $l_file_name = C__IMPORT__DIRECTORY . 'cabling_import.csv';
            $l_file_handler = fopen($l_file_name, 'w');
            foreach ($this->m_data as $l_data) {
                $l_data_as_string = implode(';', $l_data) . "\n";
                fwrite($l_file_handler, $l_data_as_string);
            }
            fclose($l_file_handler);
        }
    }

    /**
     * Create method for the connectors
     *
     * @param      $p_cabling_type
     * @param      $p_obj_id
     * @param      $p_title
     * @param      $p_direction
     * @param null $p_connected_connector
     * @param null $p_cable_name
     * @param null $p_connector_sibling
     *
     * @return mixed
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function create($p_cabling_type, $p_obj_id, $p_title, $p_direction, $p_connected_connector = null, $p_cable_name = null, $p_connector_sibling = null)
    {
        $l_list_id = null;
        $l_connector_id = $this->m_dao->create(
            $p_obj_id,
            $p_direction,
            $this->m_wiring_system,
            $this->m_connector_type,
            $p_title,
            null,
            $p_connector_sibling,
            $p_connected_connector,
            $this->m_array_classes[$p_cabling_type][1]
        );
        if (is_value_in_constants($p_cabling_type, [
            'C__CATG__NETWORK_PORT',
            'C__CMDB__SUBCAT__NETWORK_PORT'
        ]) && defined('C__CATG__NETWORK_PORT')) {
            $l_dao_class = $this->get_class_for_import(constant('C__CATG__NETWORK_PORT'));
            // Cannot use the create method of the dao class. Because the connector will be created at the same time
            $l_list_id = $this->m_dao->create_connector('isys_catg_port_list', $p_obj_id);
            $this->update_category_connector('isys_catg_port_list', $l_connector_id, $l_list_id);
            $l_dao_class->save(
                $l_list_id,
                $p_title,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                true,
                '',
                $p_connected_connector,
                null,
                $p_cable_name
            );
        } elseif ($p_cabling_type == defined_or_default('C__CATG__CONTROLLER_FC_PORT')) {
            $l_dao_class = $this->get_class_for_import(constant('C__CATG__CONTROLLER_FC_PORT'));
            // Cannot use the create method of the dao class. Because the connector will be created at the same time
            $l_list_id = $this->m_dao->create_connector('isys_catg_fc_port_list', $p_obj_id);
            $this->update_category_connector('isys_catg_fc_port_list', $l_connector_id, $l_list_id);
            $l_dao_class->save($l_list_id, C__RECORD_STATUS__NORMAL, null, $p_title, null, null, null, null, null, null, $l_connector_id, $p_cable_name);
        } elseif ($p_cabling_type == defined_or_default('C__CATG__UNIVERSAL_INTERFACE')) {
            $l_dao_class = $this->get_class_for_import(constant('C__CATG__UNIVERSAL_INTERFACE'));
            // Cannot use the create method of the dao class. Because the connector will be created at the same time
            $l_list_id = $this->m_dao->create_connector('isys_catg_ui_list', $p_obj_id);
            $this->update_category_connector('isys_catg_ui_list', $l_connector_id, $l_list_id);
            $l_dao_class->save($l_list_id, C__RECORD_STATUS__NORMAL, $p_title, null, null, $l_connector_id, $p_cable_name);
        }

        return $l_connector_id;
    }

    /**
     * Helper method which updates the connector id of a specified entry
     *
     * @param $p_table
     * @param $p_connector_id
     * @param $p_id
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function update_category_connector($p_table, $p_connector_id, $p_id)
    {
        if ($p_table != '' && $p_connector_id > 0 && $p_id > 0) {
            $l_update = 'UPDATE ' . $p_table . ' SET ' . $p_table . '__isys_catg_connector_list__id = ' . $this->m_dao->convert_sql_id($p_connector_id) . ' ' . 'WHERE ' .
                $p_table . '__id = ' . $this->m_dao->convert_sql_id($p_id);

            return ($this->m_dao->update($l_update) && $this->m_dao->apply_update());
        } else {
            return false;
        }
    }

    /**
     * @param $connectorId
     * @param $type
     *
     * @return bool
     * @throws isys_exception_dao
     */
    private function updateConnectorType($connectorId, $type)
    {
        $update = 'UPDATE isys_catg_connector_list SET isys_catg_connector_list__type = ' . $this->m_dao->convert_sql_int($type) . ' 
            WHERE isys_catg_connector_list__id = ' . $this->m_dao->convert_sql_id($connectorId);
        return $this->m_dao->update($update) && $this->m_dao->apply_update();
    }

    /**
     * Method for creating input/output connectors
     *
     * @param     $p_data
     * @param     $p_row
     * @param     $p_cell
     * @param int $p_create_input_output
     * @param int $p_category_id
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function create_input_output(
        &$p_data,
        $p_row,
        $p_cell,
        $p_end_object = false,
        $p_connector_key = null,
        $p_create_input_output = self::C__CABLING__INPUT_OUTPUT,
        $p_category_id = null,
        $p_last_key = 0
    ) {
        $l_connector_input_id = null;
        $l_connector_output_id = null;
        $l_sibling_title = null;

        if ($p_category_id === null) {
            $p_category_id = defined_or_default('C__CATG__CONNECTOR');
        }
        if (!empty($p_connector_key) && $p_create_input_output != self::C__CABLING__INPUT_OUTPUT) {
            if ($p_connector_key > $p_cell) {
                $p_create_input_output = self::C__CABLING__OUTPUT;
            } else {
                $p_create_input_output = self::C__CABLING__INPUT;
            }
        }

        $previousType = $this->headers[($p_cell - 1)];
        $afterType = $this->headers[($p_cell + 1)];

        $previousConnectorType = C__CONNECTOR__INPUT;
        $afterConnectorType = C__CONNECTOR__OUTPUT;

        if ($previousType !== null && $previousType === 'connectorOutput') {
            $previousConnectorType = C__CONNECTOR__OUTPUT;
        }

        if ($afterType !== null && $afterType === 'connectorInput') {
            $afterConnectorType = C__CONNECTOR__INPUT;
        }

        if ($p_last_key > $p_cell) {
            // first output than input
            if (($p_create_input_output == self::C__CABLING__INPUT_OUTPUT || $p_create_input_output == self::C__CABLING__OUTPUT)) {
                $l_connector_output_title = $p_data[$p_cell + 1];

                if ($l_connector_output_title != '-' && !empty($l_connector_output_title)) {
                    $l_connector_output_id_res = $this->m_dao->get_connector_by_name_as_result($l_connector_output_title);

                    while ($l_row = $l_connector_output_id_res->get_row()) {
                        $l_connector_output_id = $l_row['isys_catg_connector_list__id'];
                        if ($l_row['isys_catg_connector_list__isys_cable_connection__id '] > 0) {
                            $l_assigned_object = $this->m_dao_cable_connection->get_assigned_object(
                                $l_row['isys_catg_connector_list__isys_cable_connection__id '],
                                $l_row['isys_catg_connector_list__id']
                            );
                        } else {
                            $l_assigned_object = true;
                        }

                        if (!$this->m_dao->is_connector_id_from_object($l_connector_output_id, $p_data[$p_cell]) || !$l_assigned_object) {
                            $l_connector_output_id = null;
                        } else {
                            break;
                        }
                    }
                }

                if ($l_connector_output_id == null) {
                    // Create new output connector
                    if ($l_connector_output_title == '-' || empty($l_connector_output_title)) {
                        $l_last_id = $this->m_dao->get_last_id_from_table('isys_catg_connector_list') + 1;
                        $l_connector_output_title = $this->m_dao->get_obj_name_by_id_as_string($p_data[$p_cell]) . ' ' . $l_last_id;
                    }
                    $l_connector_output_id = $this->create($p_category_id, $p_data[$p_cell], $l_connector_output_title, $afterConnectorType, null, null);
                    $this->m_log->notice("New output connector created.");
                } else {
                    $l_wl_res = $this->m_dao->get_assigned_fiber_wave_lengths(null, $l_connector_output_id);
                    $l_wl_arr = [];
                    if ($l_wl_res->num_rows() > 0) {
                        while ($l_wl_row = $l_wl_res->get_row()) {
                            $l_wl_arr[] = $l_wl_row['isys_fiber_wave_length__id'];
                        }
                    }

                    $siblingId = $this->m_dao->get_sibling_by_connector($l_connector_output_id)->get_row_value('isys_catg_connector_list__id');

                    // Update output connector
                    $this->m_dao->save(
                        $l_connector_output_id,
                        $afterConnectorType,
                        ($this->m_wiring_system ?: $this->m_dao_connection->get_object_id_by_connection($l_row['isys_catg_connector_list__isys_connection__id'])),
                        $this->m_connector_type,
                        $l_connector_output_title,
                        $l_row['isys_catg_connector_list__isys_catg_connector_list__id'],
                        $l_row['isys_catg_connector_list__description'],
                        C__RECORD_STATUS__NORMAL,
                        null,
                        null,
                        null,
                        $l_row['isys_catg_connector_list__isys_interface__id'],
                        $l_row['isys_catg_connector_list__used_fiber_lead_rx'],
                        $l_row['isys_catg_connector_list__used_fiber_lead_tx'],
                        $l_wl_arr
                    );

                    if ($siblingId) {
                        $this->updateConnectorType($siblingId, $previousConnectorType);
                    }

                    $l_connector_input_id = ($l_row['isys_catg_connector_list__isys_catg_connector_list__id'] >
                        0) ? $l_row['isys_catg_connector_list__isys_catg_connector_list__id'] : null;
                    $l_sibling_title = $this->m_dao->get_connector_name_by_id($l_connector_input_id);
                }

                $p_data[$p_cell + 1] = $l_connector_output_id;
                $this->m_data[$p_row][$p_cell + 1] = $this->m_dao->get_connector_name_by_id($l_connector_output_id);
                $this->m_log->notice("Output connector title: '" . $this->m_data[$p_row][$p_cell + 1] . "'");

                if ($p_end_object && !$this->createOutputConnector) {
                    $this->m_log->notice("Option to automatically create a end connector is deactivated skipping connector creation.");

                    return;
                }
            }
            if (($p_create_input_output == self::C__CABLING__INPUT_OUTPUT || $p_create_input_output == self::C__CABLING__INPUT) && $p_category_id == defined_or_default('C__CATG__CONNECTOR')) {
                $l_connector_input_title = trim($p_data[$p_cell - 1]);
                if ($l_sibling_title != $l_connector_input_title && $l_connector_input_id == null) {
                    if ($l_connector_input_title != '-' && !empty($l_connector_input_title) && $p_category_id == defined_or_default('C__CATG__CONNECTOR')) {
                        $l_connector_input_id_res = $this->m_dao->get_connector_by_name_as_result($l_connector_input_title);
                        while ($l_row = $l_connector_input_id_res->get_row()) {
                            $l_connector_input_id = $l_row['isys_catg_connector_list__id'];
                            if ($l_row['isys_catg_connector_list__isys_cable_connection__id '] > 0) {
                                $l_assigned_object = $this->m_dao_cable_connection->get_assigned_object(
                                    $l_row['isys_catg_connector_list__isys_cable_connection__id '],
                                    $l_row['isys_catg_connector_list__id']
                                );
                            } else {
                                $l_assigned_object = true;
                            }

                            if (!$this->m_dao->is_connector_id_from_object($l_connector_input_id, $p_data[$p_cell]) || !$l_assigned_object) {
                                $l_connector_input_id = null;
                            } else {
                                break;
                            }
                        }
                    }
                } elseif ($l_connector_input_id > 0) {
                    $l_row = $this->m_dao->get_data($l_connector_input_id)
                        ->get_row();
                }

                if ($l_connector_input_id == null) {
                    // Create new input connector
                    if ($l_connector_input_title == '-' || empty($l_connector_input_title)) {
                        $l_last_id = $this->m_dao->get_last_id_from_table('isys_catg_connector_list') + 1;
                        $l_connector_input_title = $this->m_dao->get_obj_name_by_id_as_string($p_data[$p_cell]) . ' ' . $l_last_id;
                    }
                    $l_connector_input_id = $this->create($p_category_id, $p_data[$p_cell], $l_connector_input_title, $previousConnectorType, null, null, $l_connector_output_id);
                    $this->m_log->notice("New input connector created.");
                } else {
                    // Update input connector
                    if (!$p_end_object) {
                        $l_wl_res = $this->m_dao->get_assigned_fiber_wave_lengths(null, $l_connector_input_id);
                        $l_wl_arr = [];
                        if ($l_wl_res->num_rows() > 0) {
                            while ($l_wl_row = $l_wl_res->get_row()) {
                                $l_wl_arr[] = $l_wl_row['isys_fiber_wave_length__id'];
                            }
                        }

                        $siblingId = $this->m_dao->get_sibling_by_connector($l_connector_input_id)->get_row_value('isys_catg_connector_list__id');

                        $this->m_dao->save(
                            $l_connector_input_id,
                            $previousConnectorType,
                            ($this->m_wiring_system ?: $this->m_dao_connection->get_object_id_by_connection($l_row['isys_catg_connector_list__isys_connection__id'])),
                            $this->m_connector_type,
                            ((empty($l_connector_input_title)) ? $l_row['isys_catg_connector_list__title'] : $l_connector_input_title),
                            $l_row['isys_catg_connector_list__isys_catg_connector_list__id'],
                            $l_row['isys_catg_connector_list__description'],
                            C__RECORD_STATUS__NORMAL,
                            null,
                            null,
                            null,
                            $l_row['isys_catg_connector_list__isys_interface__id'],
                            $l_row['isys_catg_connector_list__used_fiber_lead_rx'],
                            $l_row['isys_catg_connector_list__used_fiber_lead_tx'],
                            $l_wl_arr
                        );

                        if ($siblingId) {
                            $this->updateConnectorType($siblingId, $afterConnectorType);
                        }
                    } elseif ($l_connector_input_id > 0 && $l_connector_output_id > 0) {
                        $this->update_category_connector('isys_catg_connector_list', $l_connector_input_id, $l_connector_output_id);
                        $this->update_category_connector('isys_catg_connector_list', $l_connector_output_id, $l_connector_input_id);
                    }
                }

                $p_data[$p_cell - 1] = $l_connector_input_id;
                $this->m_data[$p_row][$p_cell - 1] = $this->m_dao->get_connector_name_by_id($l_connector_input_id);
                $this->m_log->notice("Input connector title: '" . $this->m_data[$p_row][$p_cell - 1] . "'");

                if ($p_end_object && !$this->createOutputConnector) {
                    $this->m_log->notice("Option to automatically create a end connector is deactivated skipping connector creation.");

                    return;
                }
            }
        } else {
            // first input than output
            //if (($p_create_input_output == self::C__CABLING__INPUT_OUTPUT || $p_create_input_output == self::C__CABLING__INPUT) && $p_category_id == defined_or_default('C__CATG__CONNECTOR') && $p_cell > 0) {
            if (($p_create_input_output == self::C__CABLING__INPUT_OUTPUT || $p_create_input_output == self::C__CABLING__INPUT) && $p_category_id == defined_or_default('C__CATG__CONNECTOR') && $p_cell > 0) {
                $l_connector_input_title = $p_data[$p_cell - 1];

                if ($l_connector_input_title != '-' && !empty($l_connector_input_title) && $p_category_id == defined_or_default('C__CATG__CONNECTOR')) {
                    $l_connector_input_id_res = $this->m_dao->get_connector_by_name_as_result($l_connector_input_title);
                    while ($l_row = $l_connector_input_id_res->get_row()) {
                        $l_connector_input_id = $l_row['isys_catg_connector_list__id'];
                        if ($l_row['isys_catg_connector_list__isys_cable_connection__id '] > 0) {
                            $l_assigned_object = $this->m_dao_cable_connection->get_assigned_object(
                                $l_row['isys_catg_connector_list__isys_cable_connection__id '],
                                $l_row['isys_catg_connector_list__id']
                            );
                        } else {
                            $l_assigned_object = true;
                        }

                        if (!$this->m_dao->is_connector_id_from_object($l_connector_input_id, $p_data[$p_cell]) || !$l_assigned_object) {
                            $l_connector_input_id = null;
                        } else {
                            break;
                        }
                    }
                }

                if ($l_connector_input_id == null) {
                    // Create new input connector
                    if ($l_connector_input_title == '-' || empty($l_connector_input_title)) {
                        $l_last_id = $this->m_dao->get_last_id_from_table('isys_catg_connector_list') + 1;
                        $l_connector_input_title = $this->m_dao->get_obj_name_by_id_as_string($p_data[$p_cell]) . ' ' . $l_last_id;
                    }
                    $l_connector_input_id = $this->create($p_category_id, $p_data[$p_cell], $l_connector_input_title, $previousConnectorType);
                    $this->m_log->notice("New input connector created.");
                } else {
                    $l_wl_res = $this->m_dao->get_assigned_fiber_wave_lengths(null, $l_connector_input_id);
                    $l_wl_arr = [];
                    if ($l_wl_res->num_rows() > 0) {
                        while ($l_wl_row = $l_wl_res->get_row()) {
                            $l_wl_arr[] = $l_wl_row['isys_fiber_wave_length__id'];
                        }
                    }

                    $siblingId = $this->m_dao->get_sibling_by_connector($l_connector_input_id)->get_row_value('isys_catg_connector_list__id');

                    // Update input connector
                    $this->m_dao->save(
                        $l_connector_input_id,
                        $previousConnectorType,
                        ($this->m_wiring_system ?: $this->m_dao_connection->get_object_id_by_connection($l_row['isys_catg_connector_list__isys_connection__id'])),
                        $this->m_connector_type,
                        $l_connector_input_title,
                        $l_row['isys_catg_connector_list__isys_catg_connector_list__id'],
                        $l_row['isys_catg_connector_list__description'],
                        C__RECORD_STATUS__NORMAL,
                        null,
                        null,
                        null,
                        $l_row['isys_catg_connector_list__isys_interface__id'],
                        $l_row['isys_catg_connector_list__used_fiber_lead_rx'],
                        $l_row['isys_catg_connector_list__used_fiber_lead_tx'],
                        $l_wl_arr
                    );

                    if ($siblingId) {
                        $this->updateConnectorType($siblingId, $afterConnectorType);
                    }

                    $l_connector_output_id = ($l_row['isys_catg_connector_list__isys_catg_connector_list__id'] >
                        0) ? $l_row['isys_catg_connector_list__isys_catg_connector_list__id'] : null;
                    $l_sibling_title = $this->m_dao->get_connector_name_by_id($l_connector_output_id);
                }

                $p_data[$p_cell - 1] = $l_connector_input_id;
                $this->m_data[$p_row][$p_cell - 1] = $this->m_dao->get_connector_name_by_id($l_connector_input_id);
                $this->m_log->notice("Input connector title: '" . $this->m_data[$p_row][$p_cell - 1] . "'");

                if ($p_end_object && !$this->createOutputConnector) {
                    $this->m_log->notice("Option to automatically create a end connector is deactivated skipping connector creation.");

                    return;
                }
            }
            if (($p_create_input_output == self::C__CABLING__INPUT_OUTPUT || $p_create_input_output == self::C__CABLING__OUTPUT)) {
                $l_data_key = $p_cell + 1;
                $l_connector_output_title = $p_data[$l_data_key];

                if ($l_sibling_title != $l_connector_output_title && $l_connector_output_id == null) {
                    if ($l_connector_output_title != '-' && !empty($l_connector_output_title)) {
                        $l_connector_output_id_res = $this->m_dao->get_connector_by_name_as_result($l_connector_output_title);
                        while ($l_row = $l_connector_output_id_res->get_row()) {
                            $l_connector_output_id = $l_row['isys_catg_connector_list__id'];
                            if ($l_row['isys_catg_connector_list__isys_cable_connection__id '] > 0) {
                                $l_assigned_object = $this->m_dao_cable_connection->get_assigned_object(
                                    $l_row['isys_catg_connector_list__isys_cable_connection__id '],
                                    $l_row['isys_catg_connector_list__id']
                                );
                            } else {
                                $l_assigned_object = true;
                            }

                            if (!$this->m_dao->is_connector_id_from_object($l_connector_output_id, $p_data[$p_cell]) || !$l_assigned_object) {
                                $l_connector_output_id = null;
                            } else {
                                break;
                            }
                        }
                    }
                } elseif ($l_connector_output_id > 0) {
                    $l_row = $this->m_dao->get_data($l_connector_output_id)
                        ->get_row();
                }
                if ($l_connector_output_id == null) {
                    // Create new output connector
                    if ($l_connector_output_title == '-' || empty($l_connector_output_title)) {
                        $l_last_id = $this->m_dao->get_last_id_from_table('isys_catg_connector_list') + 1;
                        $l_connector_output_title = $this->m_dao->get_obj_name_by_id_as_string($p_data[$p_cell]) . ' ' . $l_last_id;
                    }
                    $l_connector_output_id = $this->create(
                        $p_category_id,
                        $p_data[$p_cell],
                        $l_connector_output_title,
                        $afterConnectorType,
                        null,
                        null,
                        $l_connector_input_id
                    );
                    $this->m_log->notice("New output connector created.");
                } else {
                    // Update output connector
                    if (!$p_end_object) {
                        $l_wl_res = $this->m_dao->get_assigned_fiber_wave_lengths(null, $l_connector_output_id);
                        $l_wl_arr = [];
                        if ($l_wl_res->num_rows() > 0) {
                            while ($l_wl_row = $l_wl_res->get_row()) {
                                $l_wl_arr[] = $l_wl_row['isys_fiber_wave_length__id'];
                            }
                        }

                        $siblingId = $this->m_dao->get_sibling_by_connector($l_connector_output_id)->get_row_value('isys_catg_connector_list__id');

                        $this->m_dao->save(
                            $l_connector_output_id,
                            $afterConnectorType,
                            ($this->m_wiring_system ?: $this->m_dao_connection->get_object_id_by_connection($l_row['isys_catg_connector_list__isys_connection__id'])),
                            $this->m_connector_type,
                            ((empty($l_connector_output_title)) ? $l_row['isys_catg_connector_list__title'] : $l_connector_output_title),
                            $l_row['isys_catg_connector_list__isys_catg_connector_list__id'],
                            $l_row['isys_catg_connector_list__description'],
                            C__RECORD_STATUS__NORMAL,
                            null,
                            null,
                            null,
                            $l_row['isys_catg_connector_list__isys_interface__id'],
                            $l_row['isys_catg_connector_list__used_fiber_lead_rx'],
                            $l_row['isys_catg_connector_list__used_fiber_lead_tx'],
                            $l_wl_arr
                        );

                        if ($siblingId) {
                            $this->updateConnectorType($siblingId, $previousConnectorType);
                        }
                    } elseif ($l_connector_input_id > 0 && $l_connector_output_id > 0) {
                        $this->update_category_connector('isys_catg_connector_list', $l_connector_input_id, $l_connector_output_id);
                        $this->update_category_connector('isys_catg_connector_list', $l_connector_output_id, $l_connector_input_id);
                    }
                }

                $p_data[$l_data_key] = $l_connector_output_id;
                $this->m_data[$p_row][$l_data_key] = $this->m_dao->get_connector_name_by_id($l_connector_output_id);
                $this->m_log->notice("Output connector title: '" . $this->m_data[$p_row][$l_data_key] . "'");

                if ($p_end_object && !$this->createOutputConnector) {
                    $this->m_log->notice("Option to automatically create a end connector is deactivated skipping connector creation.");

                    return;
                }
            }
        }
    }

    public function __construct($p_log = null, $p_file_name = null, $p_data = null)
    {
        global $g_comp_database;
        $this->m_array_classes = filter_array_by_keys_of_defined_constants([
            'C__CATG__NETWORK_PORT'        => [
                'isys_cmdb_dao_category_g_network_port',
                'C__CATG__NETWORK_PORT'
            ],
            'C__CATG__CONNECTOR'           => [
                'isys_cmdb_dao_category_g_connector',
                'C__CATG__CONNECTOR'
            ],
            'C__CATG__CONTROLLER_FC_PORT'  => [
                'isys_cmdb_dao_category_g_controller_fcport',
                'C__CATG__CONTROLLER_FC_PORT'
            ],
            'C__CATG__UNIVERSAL_INTERFACE' => [
                'isys_cmdb_dao_category_g_ui',
                'C__CATG__UNIVERSAL_INTERFACE'
            ]
        ]);
        $this->m_cabling_type = defined_or_default('C__CATG__CONNECTOR');
        $this->m_cabling_objects = defined_or_default('C__OBJTYPE__PATCH_PANEL');

        $lang = isys_application::instance()->container->get('language');

        $this->connectorInput = [
            'connectorInput',
            $lang->get('LC__CATG__CONNECTOR__INPUT')
        ];

        $this->connectorOutput = [
            'connectorOutput',
            $lang->get('LC__CATG__CONNECTOR__OUTPUT')
        ];

        if (is_array($p_data)) {
            foreach ($p_data as $l_key => $l_data) {
                if (is_numeric($l_key)) {
                    $this->m_data[] = $l_data;
                }
            }
        }

        parent::__construct($p_log);

        $this->m_file_name = $p_file_name;
        $this->m_dao = isys_cmdb_dao_category_g_connector::instance($g_comp_database);
        $this->m_dao_cable = isys_cmdb_dao_category_g_cable::instance($g_comp_database);
        $this->m_dao_cable_connection = isys_cmdb_dao_cable_connection::instance($g_comp_database);
        $this->m_dao_connection = isys_cmdb_dao_connection::instance($g_comp_database);
        $this->m_smarty_plugin = new isys_smarty_plugin_f_text();
    }
}
