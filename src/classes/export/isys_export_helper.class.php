<?php

/**
 * i-doit
 *
 * Import/export helper
 *
 * @package     i-doit
 * @subpackage  Export
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_export_helper
{
    /**
     * Arbitrary cache
     *
     * @var array
     */
    private $cache = null;

    /**
     * Category data identifiers
     *
     * @var  array
     */
    protected $m_category_data_ids;

    /**
     * Data of imported categorys
     *
     * @var  integer
     */
    protected $m_category_ids;

    /**
     * Info about table, field and referenced tables
     *
     * @var  array
     */
    protected $m_data_info = [];

    /**
     * Reference to database component.
     *
     * @var  isys_component_database
     */
    protected $m_database;

    /**
     * Info about units
     *
     * @var  array
     */
    protected $m_format_info;

    /**
     * Mode
     *
     * @var  integer
     */
    protected $m_mode;

    /**
     * Current object identifiers; used for importing categories whose objects
     * are already imported but maybe with different identifiers. This member
     * variable helps to determine the right object identifier.
     *
     * @var array Array of integers with the original object idenfiers from the
     * import/export as keys and their correspondant identifiers from database
     * as values
     */
    protected $m_object_ids;

    /**
     * Property data of current category.
     *
     * @var  array
     */
    protected $m_property_data;

    /**
     * Property information of current category.
     *
     * @var  array
     */
    protected $m_property_info;

    /**
     * Holds current row of the export.
     *
     * @var  array
     */
    protected $m_row;

    /**
     * Info about ui
     *
     * @var  array
     */
    protected $m_ui_info;

    /**
     * Variable for convert units.
     *
     * @var string
     */
    protected $m_unit_const;

    /**
     * @var isys_component_template_language_manager
     */
    protected $language;

    /**
     * @var string
     */
    protected $cacheFileName;

    /**
     * Method for setting reference data.
     *
     * @param  array $p_data_info
     */
    public function set_reference_info($p_data_info)
    {
        $this->m_data_info = $p_data_info;
    }

    /**
     * Method for returning the reference data.
     *
     * @return  array
     */
    public function get_data_info()
    {
        return $this->m_data_info;
    }

    /**
     * @param array $p_format_info
     */
    public function set_format_info($p_format_info)
    {
        $this->m_format_info = $p_format_info;
    }

    /**
     * @return array
     */
    public function get_format_info()
    {
        return $this->m_format_info;
    }

    /**
     * @param array $p_ui_info
     */
    public function set_ui_info($p_ui_info)
    {
        $this->m_ui_info = $p_ui_info;
    }

    /**
     * @return array
     */
    public function get_ui_info()
    {
        return $this->m_ui_info;
    }

    /**
     * Sets database component (usually set by the export class).
     *
     * @param isys_component_database $p_database
     */
    public function set_database(isys_component_database &$p_database)
    {
        $this->m_database = $p_database;
    }

    /**
     * Sets current export row.
     *
     * @param array $p_row
     */
    public function set_row($p_row)
    {
        $this->m_row = $p_row;
    }

    /**
     * Gets the current object identifiers. See $m_object_ids for more
     * information.
     *
     * @return array Returns null, if there have been no object identifiers set
     * before.
     */
    public function get_object_ids()
    {
        return $this->m_object_ids;
    }

    /**
     * Sets current object identifiers.
     *
     * @param array $p_object_ids See $m_object_ids for more information.
     */
    public function set_object_ids($p_object_ids)
    {
        $this->m_object_ids = $p_object_ids;
    }

    /**
     * Gets property information of current category.
     *
     * @return array
     */
    public function get_property_info()
    {
        return $this->m_property_info;
    }

    /**
     * Sets property information of current category.
     *
     * @param array $p_property_info
     */
    public function set_property_info($p_property_info)
    {
        $this->m_property_info = $p_property_info;
    }

    /**
     * Set property data of current category.
     *
     * @param array $p_property_data
     */
    public function set_property_data($p_property_data)
    {
        $this->m_property_data = $p_property_data;
    }

    /**
     * Gets property data of current category
     *
     * @return array
     */
    public function get_property_data()
    {
        return $this->m_property_data;
    }

    /**
     * Sets category identifiers.
     *
     * @param array $p_value
     */
    public function set_category_ids($p_value)
    {
        $this->m_category_ids = $p_value;
    }

    /**
     * @param $p_value
     */
    public function set_category_data_ids($p_value)
    {
        $this->m_category_data_ids = $p_value;
    }

    /**
     * @param $p_mode
     */
    public function set_mode($p_mode)
    {
        $this->m_mode = $p_mode;
    }

    /**
     * Acts like a dialog_plus wrapper.
     *
     * @param int $p_id
     *
     * @return array
     */
    public function dialog($p_id)
    {
        return $this->dialog_plus($p_id);
    }

    /**
     * Acts like a dialog_plus_import wrapper.
     *
     * @param   string $p_title_lang
     *
     * @return  integer  Value's valid identifier existing in database
     */
    public function dialog_import($p_title_lang)
    {
        return $this->dialog_plus_import($p_title_lang);
    }

    /**
     * Object-Helper: Extracts object information by its ID.
     *
     * @param   integer $p_object_id
     *
     * @return  array
     */
    public function object($p_object_id)
    {
        if (is_numeric($p_object_id) && $p_object_id > 0) {
            $l_dao = isys_cmdb_dao::instance($this->m_database);

            $l_objectdata = $l_dao->get_object_by_id($p_object_id);

            if ($l_objectdata->num_rows() == 0) {
                return false;
            }

            $l_row = $l_objectdata->get_row();

            $l_ot = $l_dao->get_objtype($l_dao->get_objTypeID($p_object_id))
                ->get_row();

            return [
                "id"         => $p_object_id,
                "title"      => $l_row["isys_obj__title"],
                "sysid"      => $l_row["isys_obj__sysid"],
                "type"       => $l_ot["isys_obj_type__const"],
                "type_title" => $this->translate($l_row['isys_obj_type__title'])
            ];
        }

        return [];
    }

    /**
     * Import method for objects.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function object_import($p_value)
    {
        if (is_array($p_value)) {
            if (isset($this->m_object_ids[$p_value['id']]) && $p_value['id'] != $this->m_object_ids[$p_value['id']]) {
                return $this->m_object_ids[$p_value['id']];
            } else {
                return $p_value['id'];
            }
        }

        return null;
    }

    /**
     * Object-Helper: Extracts object information by its ID.
     *
     * @param   integer $p_object_id
     *
     * @return  array
     */
    public function location($p_object_id)
    {
        $l_return = $this->object($p_object_id);

        if (is_numeric($p_object_id) && $p_object_id > 0) {
            $l_return = $this->object($p_object_id);

            $l_dao = isys_cmdb_dao_category_g_location::instance($this->m_database);

            // Build location path.
            $l_lpathArr = array_reverse($l_dao->get_location_path($p_object_id));
            $l_lpathArr[] = $p_object_id;
            $l_lpath_result = [];

            foreach ($l_lpathArr as $l_objectID) {
                $l_tmp = $l_dao->get_object_by_id($l_objectID)
                    ->get_row();
                $l_lpath_result[] = $l_tmp['isys_obj__title'];
            }

            $l_return["location_path"] = implode(isys_tenantsettings::get('gui.separator.location', ' > '), $l_lpath_result);
        }

        return $l_return;
    }

    /**
     * Import method for objects.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function location_import($p_value)
    {
        return $this->object_import($p_value);
    }

    /**
     * Object-Helper: Extracts object information from isys_connection
     *
     * @param int $p_connection_id
     *
     * @return array
     */
    public function connection($p_connection_id)
    {
        /* init */
        $l_return = [];

        if ($p_connection_id > 0) {
            $l_dao = new isys_cmdb_dao_connection($this->m_database);

            $l_object_id = $l_dao->get_object_id_by_connection($p_connection_id);

            if (!is_null($l_object_id)) {
                $l_objectdata = $l_dao->get_object_by_id($l_object_id);

                if ($l_objectdata->num_rows() > 0) {
                    $l_row = $l_objectdata->get_row();

                    $l_objtype = $l_dao->get_objtype($l_dao->get_objTypeID($l_object_id));
                    $l_ot = $l_objtype->get_row();

                    $l_return = [
                        "title"         => $l_row["isys_obj__title"],
                        "id"            => $l_object_id,
                        "connection_id" => $p_connection_id,
                        "type"          => $l_ot["isys_obj_type__const"],
                        "type_title"    => $this->translate($l_row['isys_obj_type__title']),
                        "sysid"         => $l_row["isys_obj__sysid"]
                    ];
                }
            }
        }

        return $l_return;
    }

    /**
     * Import method for connections.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function connection_import($p_value)
    {
        $l_return = null;
        if (is_array($p_value)) {
            $p_value['id'] = (int)$p_value['id'];

            if ($p_value['id'] > 0) {
                if (is_array($this->m_object_ids) && isset($this->m_object_ids[$p_value['id']])) {
                    $l_return = $this->m_object_ids[$p_value['id']];
                } else {
                    return false;
                }
            }
        }

        return $l_return;
    }

    /**
     * @param $p_timestamp
     *
     * @return array
     */
    public function timestamp($p_timestamp)
    {
        return [
            'id'    => $p_timestamp,
            'title' => date('c', $p_timestamp)
        ];
    }

    /**
     * Import method for timestamps.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function timestamp_import($p_value)
    {
        return (isset($p_value['id']) ? $p_value['id'] : null);
    }

    /**
     * base64 object image export
     *
     * @param   string $p_image_name
     *
     * @return  array
     */
    public function object_image($p_image_name)
    {
        global $g_dirs;

        $l_return = [];

        if ($p_image_name) {
            $l_filename = isys_application::instance()->getUploadImagePath($p_image_name);

            if (file_exists($l_filename)) {
                $l_file = base64_encode(file_get_contents($l_filename));

                $l_return = [
                    "file_name"    => $p_image_name,
                    "title"        => "<![CDATA[" . $l_file . "]]>"
                ];
            }

            return $l_return;
        }

        return $l_return;
    }

    /**
     * Writes base64 encoded image decoded to file system.
     *
     * @global  array $g_dirs
     * @return  string  File name on success, otherwise null
     */
    public function object_image_import()
    {
        global $g_dirs;

        if (!$this->m_property_data['image']['file_name']) {
            return null;
        }
        $imagePath = isys_application::instance()->getUploadImagePath($this->m_property_data['image']['file_name']);

        if (!file_exists($imagePath)) {
            $this->m_property_data['image']['file_name'] = isys_component_filemanager::create_new_filename(
                $this->m_property_data['image']['file_name'],
                isys_import_handler_cmdb::get_stored_objectID()
            );
            $l_content = base64_decode($this->m_property_data['image'][C__DATA__VALUE], true);

            if (!isys_file_put_contents($imagePath, $l_content)) {
                return null;
            }
        }

        return $this->m_property_data['image']['file_name'];
    }

    /**
     * Get dialog plus information by id
     *
     * @param int  $p_id
     * @param bool $p_table_name
     *
     * @return array
     */
    public function dialog_plus($p_id, $p_table_name = false)
    {
        $l_return = [];

        // See ID-2365
        if ($p_id >= 0) {
            // Get corresponding table.
            if ($p_table_name) {
                $l_table = $p_table_name;
            } else {
                $l_table = $this->m_data_info[C__PROPERTY__DATA__REFERENCES][0];
            }

            if (empty($l_table)) {
                // Data are generated in the ui
                if (isset($this->m_ui_info[C__PROPERTY__UI__PARAMS]['p_arData'])) {
                    $l_dialogdata = $this->m_ui_info[C__PROPERTY__UI__PARAMS]['p_arData'];

                    if (is_object($l_dialogdata) && method_exists($l_dialogdata, 'execute')) {
                        $l_dialogdata = $l_dialogdata->execute();
                    }

                    if (is_string($l_dialogdata)) {
                        $l_dialogdata = unserialize($l_dialogdata);
                    }

                    if (isset($l_dialogdata[$p_id])) {
                        $l_return = [
                            'id'         => $p_id,
                            'title'      => $this->translate($l_dialogdata[$p_id]),
                            'const'      => '',
                            'title_lang' => $l_dialogdata[$p_id]
                        ];
                    }
                }
            } elseif ($p_id > 0) {
                // Data is in the db.
                $l_row = isys_factory_cmdb_dialog_dao::get_instance($this->m_database, $l_table)
                    ->get_data($p_id);

                if (!empty($l_row)) {
                    $l_return = [
                        "id"         => $p_id,
                        "title"      => $this->translate($l_row[$l_table . "__title"]),
                        "const"      => $l_row[$l_table . "__const"],
                        "title_lang" => $l_row[$l_table . "__title"]
                    ];
                }
            }
        }

        /**
         * Return null for empty sets
         *
         * @see ID-3116
         */
        if (!count($l_return)) {
            $l_return = null;
        }

        return $l_return;
    }

    /**
     * dialog_plus wrapper.
     *
     * @param   integer $p_id
     * @param   boolean $p_table_name
     *
     * @return  integer
     * @author Selcuk Kekec <skekec@synetics.de>
     */
    public function model_title($p_id, $p_table_name = false)
    {
        return $this->dialog_plus($p_id, $p_table_name);
    }

    /**
     * Model manufacturer-title relation handler.
     *
     * @param   mixed $p_title_lang
     *
     * @return  integer
     * @author Selcuk Kekec <skekec@synetics.de>
     */
    public function model_title_import($p_title_lang)
    {
        $l_id = null;

        if (is_array($p_title_lang)) {
            if (isset($p_title_lang[C__DATA__VALUE]) && is_array($p_title_lang[C__DATA__VALUE])) {
                $p_title_lang = $p_title_lang[C__DATA__VALUE];
            }
            if (!empty($p_title_lang["title_lang"]) || is_numeric($p_title_lang["title_lang"])) {
                $p_title_lang = $p_title_lang["title_lang"];
            } elseif (!empty($p_title_lang[C__DATA__VALUE]) || is_numeric($p_title_lang[C__DATA__VALUE])) {
                $p_title_lang = $p_title_lang[C__DATA__VALUE];
            } else {
                return null;
            }
        }

        if (isset($this->m_property_data['manufacturer'])) {
            return isys_import::check_dialog(
                'isys_model_title',
                $p_title_lang,
                null,
                (is_numeric($this->m_property_data['manufacturer'][C__DATA__VALUE]) ? $this->m_property_data['manufacturer'][C__DATA__VALUE] : $this->m_property_data['manufacturer']['id'])
            );
        } else {
            return isys_import::check_dialog('isys_model_title', $p_title_lang);
        }
    }

    /**
     * Converts dialog plus properties. Matches given value's language constant
     * with property table.
     *
     * @param   mixed  $p_title_lang
     * @param   string $p_table
     *
     * @return  integer  Value's valid identifier existing in database
     */
    public function dialog_plus_import($p_title_lang, $p_table = null)
    {
        $l_table = $p_table;
        $l_dialog_id = null;

        if (is_array($p_title_lang)) {
            if (isset($p_title_lang[C__DATA__VALUE]) && is_array($p_title_lang[C__DATA__VALUE])) {
                $p_title_lang = $p_title_lang[C__DATA__VALUE];
            }

            if (!empty($p_title_lang['id'])) {
                $l_dialog_id = $p_title_lang['id'];
            }

            if (!empty($p_title_lang["title_lang"])) {
                $p_title_lang = $p_title_lang["title_lang"];
            } elseif (!empty($p_title_lang[C__DATA__VALUE])) {
                $p_title_lang = $p_title_lang[C__DATA__VALUE];
            } else {
                return null;
            }
        }

        $l_return = null;
        if (isset($this->m_data_info[C__PROPERTY__DATA__REFERENCES])) {
            $l_table = $this->m_data_info[C__PROPERTY__DATA__REFERENCES][0];
        }

        // Check if we have to handle data from a table or "p_arData".

        if (empty($l_table)) {
            if (isset($this->m_ui_info[C__PROPERTY__UI__PARAMS]['p_arData'])) {
                $l_dialogdata = $this->m_ui_info[C__PROPERTY__UI__PARAMS]['p_arData'];

                if (is_object($l_dialogdata) && method_exists($l_dialogdata, 'execute')) {
                    $l_dialogdata = $l_dialogdata->execute();
                }

                if (is_string($l_dialogdata)) {
                    $l_dialogdata = unserialize($l_dialogdata);
                }

                // We now got all the dialog-data, so we just have to find
                foreach ($l_dialogdata as $l_id => $l_dialog) {
                    if ($this->translate($l_dialog) == $p_title_lang || $l_dialog == $p_title_lang || $l_id == $l_dialog_id) {
                        $l_return = $l_id;
                        break;
                    }
                }
            }
        } elseif (is_numeric($p_title_lang) && strpos($p_title_lang, '0') !== 0 && $l_table !== null) {
            if (call_user_func([
                'isys_cmdb_dao_dialog',
                'instance'
            ], $this->m_database)
                ->set_table($l_table)
                ->get_data($p_title_lang)) {
                $l_return = $p_title_lang;
            }
        }

        if ($l_return === null && !empty($l_table)) {
            $l_return = isys_import::check_dialog($l_table, (string)$p_title_lang);
        }

        return $l_return;
    }

    /**
     * Import method for contacts.
     *
     * @param   array $p_values
     *
     * @return  array
     */
    public function exportContactAssignment_import($p_values)
    {
        $l_type = $l_tag = '';
        $l_id = false;
        $l_obj_id = false;
        $l_values = [];
        $l_return = null;
        $l_dao = new isys_cmdb_dao($this->m_database);

        if (is_object($p_values[C__DATA__VALUE])) {
            $l_data = $p_values[C__DATA__VALUE]->get_data();
            $p_values[C__DATA__VALUE] = $l_data;
        }

        if (is_array($p_values[C__DATA__VALUE])) {
            if (is_array($this->m_object_ids) && isset($p_values['id']) && isset($this->m_object_ids[$p_values['id']])) {
                $l_obj_id = $this->m_object_ids[$p_values['id']];
                $l_type = $p_values['type'];

                foreach ($p_values as $l_key => $l_value) {
                    if ($l_key == 'headquarter' || $l_key == 'organization') {
                        if (array_key_exists($l_value, $this->m_object_ids)) {
                            $l_value = $this->m_object_ids[$l_value];
                            $p_values[$l_key] = $l_value;
                        } elseif ($l_value > 0) {
                            $objectInfo = $l_dao->get_object($l_value)
                                ->get_row();
                            if ($objectInfo) {
                                // Check if object id and title are the same
                                if ($objectInfo['isys_obj__id'] == $l_value && $objectInfo['isys_obj__title'] != $p_values[$l_key . '_title']) {
                                    $l_value = '';
                                    $p_values[$l_key] = $l_value;
                                }
                            } else {
                                $l_value = '';
                                $p_values[$l_key] = $l_value;
                            }
                        }
                    }

                    $l_values[isys_import_handler_cmdb::C__PROPERTIES][$l_key][C__DATA__VALUE] = $l_value;
                }
            } else {
                // Object is not in cache.
            }

            $p_values[C__DATA__VALUE] = $l_obj_id;
            $l_return = $p_values[C__DATA__VALUE];
        } else {
            if (is_array($this->m_object_ids) && isset($this->m_object_ids[$p_values['id']])) {
                $l_obj_id = $this->m_object_ids[$p_values['id']];
                $l_type = $p_values['type'];

                foreach ($p_values as $l_key => $l_value) {
                    if ($l_key == 'headquarter' || $l_key == 'organization') {
                        if (isset($this->m_object_ids[$l_value])) {
                            $l_value = $this->m_object_ids[$l_value];
                            $p_values[$l_key] = $l_value;
                        } else {
                            $l_value = '';
                            $p_values[$l_key] = $l_value;
                        }
                    }

                    $l_values[isys_import_handler_cmdb::C__PROPERTIES][$l_key][C__DATA__VALUE] = $l_value;
                }
            }

            $p_values[C__DATA__VALUE] = $l_obj_id;
            $l_return = $p_values;
        }

        if ($l_obj_id) {
            $l_result = $l_dao->retrieve("SELECT isys_obj_type__isysgui_cats__id FROM isys_obj_type WHERE isys_obj_type__const = " . $l_dao->convert_sql_text($l_type) . ";")
                ->get_row();
            $l_specific_category = $l_result['isys_obj_type__isysgui_cats__id'];

            if ($l_specific_category == defined_or_default('C__CATS__PERSON')) {
                $l_id = $l_dao->retrieve("SELECT isys_cats_person_list__id FROM isys_cats_person_list WHERE isys_cats_person_list__isys_obj__id = " .
                    $l_dao->convert_sql_id($l_obj_id))
                    ->get_row();
                $l_tag = 'isys_cats_person_list__id';
                $l_dao_object = isys_cmdb_dao_category_s_person_master::instance($this->m_database);
            } elseif ($l_specific_category == defined_or_default('C__CATS__PERSON_GROUP')) {
                $l_id = $l_dao->retrieve("SELECT isys_cats_person_group_list__id FROM isys_cats_person_group_list WHERE isys_cats_person_group_list__isys_obj__id = " .
                    $l_dao->convert_sql_id($l_obj_id))
                    ->get_row();
                $l_tag = 'isys_cats_person_group_list__id';
                $l_dao_object = isys_cmdb_dao_category_s_person_group_master::instance($this->m_database);
            } elseif ($l_specific_category == defined_or_default('C__CATS__ORGANIZATION')) {
                $l_id = $l_dao->retrieve("SELECT isys_cats_organization_list__id FROM isys_cats_organization_list WHERE isys_cats_organization_list__isys_obj__id = " .
                    $l_dao->convert_sql_id($l_obj_id))
                    ->get_row();
                $l_tag = 'isys_cats_organization_list__id';
                $l_dao_object = isys_cmdb_dao_category_s_organization_master::instance($this->m_database);
            }

            // Syncronize contacts.
            if (isset($l_dao_object) && is_object($l_dao_object)) {
                if ($l_id && is_array($l_id)) {
                    $l_values['data_id'] = $l_id[$l_tag];
                    $l_dao_object->sync($l_values, $l_obj_id, isys_import_handler_cmdb::C__UPDATE);
                } else {
                    $l_dao_object->sync($l_values, $l_obj_id, isys_import_handler_cmdb::C__CREATE);
                }
            }
        }

        return $l_return;
    }

    /**
     * Contact-Helper - Extracts a contact id into its corresponding data items and
     * returns an isys_exporrt_data collection like this:
     *
     *  [0] => Array
     *  (
     *    [id] => 16
     *    [title] =>
     *    [firstname] => Dennis
     *    [lastname] => Stücken
     *    [type] => "C__OBJTYPE__PERSON"
     *  ),
     * (...)
     *
     * @param int $p_contact_id
     *
     * @return mixed object instance of isys_export_data or empty array
     */
    public function contact($p_contact_id)
    {
        $l_contacts = [];

        if ($p_contact_id > 0) {

            /* Get data item dao */
            $l_data_item = new isys_contact_dao_reference($this->m_database);
            $l_cmdb_dao = new isys_cmdb_dao($this->m_database);

            /*
            if (isset($this->m_row["isys_catg_contact_list__isys_contact_tag__id"])) {
                $l_tag = $this->dialog_plus($this->m_row["isys_catg_contact_list__isys_contact_tag__id"], "isys_contact_tag");
            }
            */

            try {
                if ($l_data_item->load($p_contact_id)) {

                    /* Fetch data items as array */
                    $l_di_array = $l_data_item->get_data_item_array();

                    foreach ($l_di_array as $l_object_id => $l_tmp) {
                        $l_contacts[] = $this->export_contact($l_object_id);
                    }
                }

                return new isys_export_data($l_contacts);
            } catch (isys_exception_contact $e) {
                throw new $e;
            }
        }

        return [];
    }

    /**
     * Import method for contacts.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function contact_import($p_value)
    {
        if (!is_array($p_value[C__DATA__VALUE]) && !empty($p_value[C__DATA__VALUE])) {
            return $p_value[C__DATA__VALUE];
        } elseif (empty($p_value[C__DATA__VALUE])) {
            return null;
        }

        $l_dao_contact = new isys_contact_dao_reference($this->m_database);

        $l_contact_arr = $p_value[C__DATA__VALUE];

        $l_column = $this->m_property_info[$p_value[C__DATA__TAG]][C__DATA__VALUE];

        if (!is_null($this->m_row[$l_column])) {
            $l_dao_contact->delete($this->m_row[$l_column]);
        }

        $l_dao_contact->clear();

        if (is_countable($l_contact_arr) && count($l_contact_arr) > 0) {
            foreach ($l_contact_arr as $l_key => $l_val) {
                if ($l_val['id'] != $this->m_object_ids[$l_val['id']]) {
                    $l_contact_arr[$l_key]['id'] = $this->m_object_ids[$l_val['id']];

                    $l_value = [];
                    $l_value[isys_import_handler_cmdb::C__PROPERTIES] = $l_val;
                    $l_value[isys_import_handler_cmdb::C__PROPERTIES][C__DATA__VALUE] = $l_contact_arr[$l_key]['id'];

                    $this->exportContactAssignment_import($l_value);
                }

                $l_dao_contact->insert_data_item($l_contact_arr[$l_key]['id']);
            }
        }

        $l_dao_contact->save();

        return $l_dao_contact->get_id();
    }

    /**
     * @param  int $objectId
     *
     * @return array
     * @throws isys_exception_database
     */
    public function export_contact($objectId)
    {
        if (!is_numeric($objectId) || $objectId <= 0) {
            return [];
        }

        $cmdbDao = isys_application::instance()->container->get('cmdb_dao');

        $sql = 'SELECT isys_obj__title AS objectTitle, isys_obj__sysid AS objectSysId, isys_obj_type__const AS objectTypeConst, isysgui_cats__const AS categoryConst
            FROM isys_obj
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id 
            INNER JOIN isysgui_cats ON isysgui_cats__id = isys_obj_type__isysgui_cats__id 
            WHERE isys_obj__id = ' . $cmdbDao->convert_sql_id($objectId) . '
            LIMIT 1;';

        $objectData = $cmdbDao->retrieve($sql)->get_row();

        $objectTitle = $objectData['objectTitle'];
        $objectSysId = $objectData['objectSysId'];
        $objectTypeConstant = $objectData['objectTypeConst'];
        $specificCategoryConstant = $objectData['categoryConst'];

        if (in_array($specificCategoryConstant, ['C__CATS__PERSON', 'C__CATS__PERSON_MASTER'], true)) {
            $personData = isys_cmdb_dao_category_s_person_master::instance($this->m_database)
                ->get_data(null, $objectId)
                ->get_row();

            if ($personData) {
                return [
                    'id'                  => $objectId,
                    'title'               => $objectTitle,
                    'first_name'          => $personData['isys_cats_person_list__first_name'],
                    'last_name'           => $personData['isys_cats_person_list__last_name'],
                    'ldap_id'             => $personData['isys_cats_person_list__isys_ldap__id'],
                    'department'          => $personData['isys_cats_person_list__department'],
                    'position'            => $personData['isys_cats_person_list__position'],
                    'mail'                => $personData['isys_cats_person_list__mail_address'],
                    'phone_company'       => $personData['isys_cats_person_list__phone_company'],
                    'phone_mobile'        => $personData['isys_cats_person_list__phone_mobile'],
                    'phone_home'          => $personData['isys_cats_person_list__phone_home'],
                    'fax'                 => $personData['isys_cats_person_list__fax'],
                    'login'               => $personData['isys_cats_person_list__title'],
                    'user_pass'           => $personData['isys_cats_person_list__user_pass'],
                    'sysid'               => $objectSysId,
                    'organization'        => $personData['isys_connection__isys_obj__id'],
                    'organization_title'  => $cmdbDao->get_obj_name_by_id_as_string($personData['isys_connection__isys_obj__id']),
                    'type'                => $objectTypeConstant,
                    'salutation'          => $personData['isys_cats_person_list__salutation'],
                    'academic_degree'     => $personData['isys_cats_person_list__academic_degree'],
                    'function'            => $personData['isys_cats_person_list__function'],
                    'service_designation' => $personData['isys_cats_person_list__service_designation'],
                    'street'              => $personData['isys_cats_person_list__street'],
                    'city'                => $personData['isys_cats_person_list__city'],
                    'zip_code'            => $personData['isys_cats_person_list__zip_code'],
                    'pager'               => $personData['isys_cats_person_list__pager'],
                    'personnel_number'    => $personData['isys_cats_person_list__personnel_number']
                ];
            }

            // Get data into our return array.
            return [
                'id'                  => $objectId,
                'title'               => $objectTitle,
                'first_name'          => null,
                'last_name'           => null,
                'ldap_id'             => null,
                'department'          => null,
                'position'            => null,
                'mail'                => null,
                'phone_company'       => null,
                'phone_mobile'        => null,
                'phone_home'          => null,
                'fax'                 => null,
                'login'               => null,
                'user_pass'           => null,
                'sysid'               => $objectSysId,
                'organization'        => null,
                'organization_title'  => null,
                'type'                => $objectTypeConstant,
                'salutation'          => null,
                'academic_degree'     => null,
                'function'            => null,
                'service_designation' => null,
                'street'              => null,
                'city'                => null,
                'zip_code'            => null,
                'pager'               => null,
                'personnel_number'    => null
            ];
        }

        if (in_array($specificCategoryConstant, ['C__CATS__PERSON_GROUP', 'C__CATS__PERSON_GROUP_MASTER'], true)) {
            $personGroupData = isys_cmdb_dao_category_s_person_group_master::instance($this->m_database)
                ->get_data(null, $objectId)
                ->get_row();

            // Get data into our return array.
            if ($personGroupData) {
                return [
                    'id'            => $objectId,
                    'title'         => $personGroupData['isys_cats_person_group_list__title'],
                    'ldap_group'    => $personGroupData['isys_cats_person_group_list__ldap_group'],
                    'email_address' => $personGroupData['isys_cats_person_group_list__email_address'],
                    'phone'         => $personGroupData['isys_cats_person_group_list__phone'],
                    'right_group'   => $personGroupData['isys_cats_person_group_list__right_group'],
                    'sysid'         => $objectSysId,
                    'type'          => $objectTypeConstant
                ];
            }

            return [
                'id'            => $objectId,
                'title'         => $objectTitle,
                'ldap_group'    => null,
                'email_address' => null,
                'phone'         => null,
                'right_group'   => null,
                'sysid'         => $objectSysId,
                'type'          => $objectTypeConstant
            ];
        }

        if (in_array($specificCategoryConstant, ['C__CATS__ORGANIZATION', 'C__CATS__ORGANIZATION_MASTER_DATA'], true)) {
            $organizationData = isys_cmdb_dao_category_s_organization_master::instance($this->m_database)
                ->get_data(null, $objectId)
                ->get_row();

            $emailAddress = isys_cmdb_dao_category_g_mail_addresses::instance($this->m_database)
                ->get_primary_mail_as_string_by_obj_id($objectId);

            // Get data into our return array.
            if ($organizationData) {
                return [
                    'id'                => $objectId,
                    'title'             => $organizationData['isys_cats_organization_list__title'],
                    'telephone'         => $organizationData['isys_cats_organization_list__telephone'],
                    'fax'               => $organizationData['isys_cats_organization_list__fax'],
                    'email_address'     => $emailAddress,
                    'website'           => $organizationData['isys_cats_organization_list__website'],
                    'headquarter'       => $organizationData['isys_connection__isys_obj__id'],
                    'headquarter_title' => $cmdbDao->get_obj_name_by_id_as_string($organizationData['isys_connection__isys_obj__id']),
                    'sysid'             => $objectSysId,
                    'type'              => $objectTypeConstant
                ];
            }

            return [
                'id'                => $objectId,
                'title'             => $objectTitle,
                'telephone'         => null,
                'fax'               => null,
                'email_address'     => $emailAddress,
                'website'           => null,
                'headquarter'       => null,
                'headquarter_title' => null,
                'sysid'             => $objectSysId,
                'type'              => $objectTypeConstant
            ];
        }

        return [];
    }

    /**
     * @param $value
     *
     * @return array
     */
    public function convert($value)
    {
        $formatInfo = $this->get_format_info();

        if ($value && (is_array($formatInfo) || $formatInfo instanceof ArrayAccess)) {
            $unitConstant = $this->get_unit_const();

            if (isset($formatInfo[C__PROPERTY__FORMAT__CALLBACK][2][0]) && !empty($unitConstant)) {
                $convertMethod = $formatInfo[C__PROPERTY__FORMAT__CALLBACK][2][0];

                if (is_string($convertMethod)) {
                    return [
                        'title' => isys_convert::$convertMethod(isys_helper::filter_number($value), $unitConstant, C__CONVERT_DIRECTION__BACKWARD)
                    ];
                }
            }
        }

        return [];
    }

    /**
     * Converting data happens actually in categories.
     *
     * @param   array $p_value
     *
     * @return  integer
     */
    public function convert_import($p_value)
    {
        $l_value = '';

        if (is_array($p_value) && isset($p_value[C__DATA__VALUE])) {
            if (is_array($p_value[C__DATA__VALUE])) {
                if (isset($p_value[C__DATA__VALUE][C__DATA__VALUE])) {
                    $l_value = $p_value[C__DATA__VALUE][C__DATA__VALUE];
                } elseif (isset($p_value[C__DATA__VALUE][C__DATA__TITLE])) {
                    $l_value = $p_value[C__DATA__VALUE][C__DATA__TITLE];
                }
            } else {
                $l_value = $p_value[C__DATA__VALUE];
            }
        } else {
            return null;
        }

        $l_method = $this->m_format_info[C__PROPERTY__FORMAT__CALLBACK][2][0];

        return isys_convert::$l_method(str_replace(',', '.', $l_value), $this->get_unit_const());
    }

    /**
     * Exports a human friendly money number (1.000.000,95 EUR).
     *
     * @param   float $p_value
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function money_format($p_value)
    {
        global $g_comp_session;

        if (class_exists('isys_locale')) {
            $l_loc = isys_locale::get($this->m_database, $g_comp_session->get_user_id());

            return $l_loc->fmt_monetary($p_value);
        }

        return '';
    }

    /**
     * Imports a money value to a float.
     *
     * @param   array $p_value
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function money_format_import($p_value)
    {
        if (is_array($p_value) && array_key_exists(C__DATA__VALUE, $p_value)) {
            if (is_array($p_value[C__DATA__VALUE])) {
                if (array_key_exists(C__DATA__VALUE, $p_value[C__DATA__VALUE])) {
                    $p_value[C__DATA__VALUE] = $p_value[C__DATA__VALUE][C__DATA__VALUE];
                }
            }
            $l_value = $p_value[C__DATA__VALUE];
        } else {
            return null;
        }

        return isys_helper::filter_number($l_value);
    }

    /**
     * Extracts ip address assignments of the routing category.
     *
     * @param   integer $p_id
     *
     * @return  isys_export_data
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function routing_gateway($p_id)
    {
        $l_return = [];

        try {
            $l_dao = isys_cmdb_dao_category_g_ip::instance($this->m_database);
            $l_router_ips = $l_dao->get_ips_for_router_list_by_obj_id(null, $p_id);

            while ($l_row = $l_router_ips->get_row()) {
                $l_ip_result = $l_dao->get_data($l_row['isys_catg_ip_list__id']);

                while ($l_ip_row = $l_ip_result->get_row()) {
                    $l_title = $l_ip_row['isys_cats_net_ip_addresses_list__title'];

                    $l_return[] = [
                        'id'       => $l_ip_row['isys_catg_ip_list__id'],
                        'title'    => $l_title,
                        'hostname' => $l_ip_row['isys_catg_ip_list__hostname'],
                        'type'     => 'C__CATG__IP'
                    ];
                }
            }
        } catch (isys_exception_database $e) {
            throw new $e;
        }

        return new isys_export_data($l_return);
    }

    /**
     * Import method for the ip address assignments of the routing category.
     *
     * @param   array $p_value
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function routing_gateway_import($p_value)
    {
        $l_return = [];

        if (is_array($p_value[C__DATA__VALUE])) {
            foreach ($p_value[C__DATA__VALUE] as $l_hostadress) {
                if (is_array($l_hostadress)) {
                    $l_const = (!is_numeric($l_hostadress['type']) && defined($l_hostadress['type'])) ? constant($l_hostadress['type']) : $l_hostadress['type'];

                    if (array_key_exists($l_hostadress['id'], $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_const])) {
                        $l_return[] = $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_const][$l_hostadress['id']];
                    }
                }
            }
        }

        return $l_return;
    }

    /**
     * Extracts ip address assignments of
     *    - Ports (isys_catg_port_list)
     *  - Logical Interfaces (isys_netp_ifacel)
     *  - Cluster services
     *
     * @param   int $p_id
     *
     * @return isys_export_data
     * @throws isys_exception_general
     */
    public function hostaddress($p_id)
    {
        $l_return = [];
        $l_short_fields = null;
        $l_reference_info = $this->m_data_info[C__PROPERTY__DATA__REFERENCES];

        if (is_countable($l_reference_info) && count($l_reference_info) > 0) {
            $l_table_arr = explode('_2_', $l_reference_info[0]);
            $l_table = $l_table_arr[1];

            switch ($l_table) {
                case "isys_catg_cluster_service_list":
                    $l_short_fields = true;
                    break;
                case "isys_catg_log_port_list":
                    $l_short_fields = true;
                    break;
                default:
                    $l_short_fields = false;
                    break;
            }

            try {
                $l_dao = isys_cmdb_dao_category_g_ip::instance($this->m_database);

                // @see  ID-7593  It is possible that the $l_table variable is empty, resulting in a SQL error.
                if ($l_table == 'isys_catg_port_list' || empty($l_table)) {
                    $l_port_ips = $l_dao->get_data(null, null, ' AND isys_catg_ip_list__isys_catg_port_list__id = ' . $l_dao->convert_sql_id($p_id) . ' ');
                } else {
                    $l_port_ips = $l_dao->get_ips_by_connection_table($l_table, $p_id, null, false, $l_short_fields);
                }

                if ($l_port_ips->num_rows() > 0) {
                    while ($l_row = $l_port_ips->get_row()) {
                        $l_return[] = [
                            'id'       => $l_row['isys_catg_ip_list__id'],
                            'title'    => $l_row['isys_cats_net_ip_addresses_list__title'],
                            'hostname' => $l_row['isys_catg_ip_list__hostname'],
                            'type'     => 'C__CATG__IP'
                        ];
                    }
                }
            } catch (isys_exception_database $e) {
                throw new isys_exception_general($e->getMessage());
            }
        } else {
            throw new isys_exception_general('No reference info for hostadress assigned. Modify the properties of the category.');
        }

        return new isys_export_data($l_return);
    }

    /**
     * Import method for hostaddresses.
     *
     * @param   array $p_value
     *
     * @return  array
     */
    public function hostaddress_import($p_value)
    {
        $l_return = [];

        if (is_array($p_value[C__DATA__VALUE])) {
            foreach ($p_value[C__DATA__VALUE] as $l_hostadress) {
                if (is_array($l_hostadress)) {
                    $l_const = (!is_numeric($l_hostadress['type']) && defined($l_hostadress['type'])) ? constant($l_hostadress['type']) : $l_hostadress['type'];

                    if (array_key_exists($l_hostadress['id'], $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_const])) {
                        $l_return[] = $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_const][$l_hostadress['id']];
                    }
                } else {
                    $l_return[] = $l_hostadress;
                }
            }
        }

        return $l_return;
    }

    /**
     * @param $p_listID
     *
     * @return isys_export_data
     */
    public function ports($p_listID)
    {
        $l_return = [];

        if (empty($p_listID)) {
            $p_listID = $this->m_row["isys_catg_virtual_switch_list__id"];
        }

        if ($p_listID > 0) {
            $l_dao = isys_cmdb_dao_category_g_virtual_switch::instance($this->m_database);

            $l_ports = $l_dao->get_assigned_ports($p_listID);

            $l_return = [];

            while ($l_row = $l_ports->get_row()) {
                $l_return[] = [
                    "id"    => $l_row["isys_virtual_switch_2_port__isys_catg_port_list__id"],
                    "title" => $l_row["isys_catg_port_list__title"],
                    "type"  => "C__CATG__NETWORK_PORT"
                ];
            }
        }

        return new isys_export_data($l_return);
    }

    /**
     * Import method for ports.
     *
     * @param   array $p_values
     *
     * @return  array
     */
    public function ports_import($p_values)
    {
        $l_new_arr = [];
        $l_data = $p_values[C__DATA__VALUE];

        if (is_array($l_data) && count($l_data)) {
            foreach ($l_data as $l_value) {
                if (is_array($l_value)) {
                    if (defined('C__CATG__NETWORK_PORT') && is_array($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__NETWORK_PORT')])) {
                        if (array_key_exists($l_value['id'], $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__NETWORK_PORT')])) {
                            $l_new_arr[] = $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__NETWORK_PORT')][$l_value['id']];
                        } else {
                            $l_new_arr[] = null;
                        }
                    } else {
                        break;
                    }
                } else {
                    break;
                }
            }

            if (count($l_new_arr) > 0) {
                $l_data = $l_new_arr;
            }
        } else {
            return null;
        }

        return $l_data;
    }

    /**
     * @param $p_listID
     *
     * @return isys_export_data
     */
    public function portgroups($p_listID)
    {
        if (empty($p_listID)) {
            $p_listID = $this->m_row["isys_catg_virtual_switch_list__id"];
        }

        $l_return = [];

        if ($p_listID > 0) {
            $l_dao = isys_cmdb_dao_category_g_virtual_switch::instance($this->m_database);

            $l_ports = $l_dao->get_port_groups($p_listID);

            while ($l_row = $l_ports->get_row()) {
                $l_return[] = [
                    "id"     => $l_row["isys_virtual_port_group__id"],
                    "vlanid" => $l_row["isys_virtual_port_group__vlanid"],
                    "title"  => $l_row["isys_virtual_port_group__title"]
                    //"type"	 => "C__CATG__VIRTUAL_SWITCH"
                ];
            }
        }

        return new isys_export_data($l_return);
    }

    /**
     * Import method for portgroups.
     *
     * @param   array $p_value
     *
     * @return  array
     */
    public function portgroups_import($p_value)
    {
        $l_arr = $p_value;
        $l_new_arr = [];
        if (array_key_exists(C__DATA__VALUE, $l_arr)) {
            if (is_array($l_arr[C__DATA__VALUE]) && count($l_arr[C__DATA__VALUE]) > 0) {
                foreach ($l_arr[C__DATA__VALUE] as $l_key => $l_port_group) {
                    $l_new_arr[$l_key][0] = $l_port_group[C__DATA__VALUE];
                    $l_new_arr[$l_key][1] = $l_port_group['vlanid'];
                }
            }

            $l_arr = $l_new_arr;
        }

        return $l_arr;
    }

    /**
     * @param $p_listID
     *
     * @return isys_export_data
     */
    public function serviceconsoleports($p_listID)
    {
        if (empty($p_listID)) {
            $p_listID = $this->m_row["isys_catg_virtual_switch_list__id"];
        }

        $l_dao = isys_cmdb_dao_category_g_virtual_switch::instance($this->m_database);

        $l_ports = $l_dao->get_service_console_ports($p_listID);

        $l_return = [];

        while ($l_row = $l_ports->get_row()) {
            $l_return[] = [
                "title"  => $l_row["isys_service_console_port__title"],
                "id"     => $l_row["isys_service_console_port__id"],
                "ref_id" => $l_row["isys_service_console_port__isys_catg_ip_list__id"],
                "ip"     => $l_row["isys_cats_net_ip_addresses_list__title"],
                "type"   => "C__CATG__IP"
            ];
        }

        return new isys_export_data($l_return);
    }

    /**
     * Import method for service console ports.
     *
     * @param   array $p_value
     *
     * @return  array
     */
    public function serviceconsoleports_import($p_value)
    {
        $l_arr = $p_value;
        $l_new_arr = [];

        if (array_key_exists(C__DATA__VALUE, $l_arr)) {
            if (is_array($l_arr[C__DATA__VALUE]) && count($l_arr[C__DATA__VALUE]) > 0) {
                foreach ($l_arr[C__DATA__VALUE] as $l_key => $l_scp) {
                    $l_new_arr[$l_key][0] = $l_scp[C__DATA__VALUE];
                    if (defined('C__CATG__IP')) {
                        $l_new_arr[$l_key][1] = $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__IP')][$l_scp['ref_id']];
                    }
                }
            }

            $l_arr = $l_new_arr;
        }

        return $l_arr;
    }

    /**
     * @param $p_listID
     *
     * @return isys_export_data
     */
    public function vmkernelports($p_listID)
    {
        if (empty($p_listID)) {
            $p_listID = $this->m_row["isys_catg_virtual_switch_list__id"];
        }

        $l_return = [];

        if ($p_listID > 0) {
            $l_dao = isys_cmdb_dao_category_g_virtual_switch::instance($this->m_database);

            $l_ports = $l_dao->get_vmkernel_ports($p_listID);

            while ($l_row = $l_ports->get_row()) {
                $l_return[] = [
                    "id"     => $l_row["isys_vmkernel_port__id"],
                    "title"  => $l_row["isys_vmkernel_port__title"],
                    "ref_id" => $l_row["isys_vmkernel_port__isys_catg_ip_list__id"],
                    "ip"     => $l_row["isys_cats_net_ip_addresses_list__title"],
                    "type"   => "C__CATG__IP"
                ];
            }
        }

        return new isys_export_data($l_return);
    }

    /**
     * Import method for VM kernel ports.
     *
     * @param   array $p_value
     *
     * @return  array
     */
    public function vmkernelports_import($p_value)
    {
        $l_arr = $p_value;
        $l_new_arr = [];

        if (array_key_exists(C__DATA__VALUE, $l_arr)) {
            if (is_array($l_arr[C__DATA__VALUE]) && count($l_arr[C__DATA__VALUE]) > 0) {
                foreach ($l_arr[C__DATA__VALUE] as $l_key => $l_vmkp) {
                    $l_new_arr[$l_key][0] = $l_vmkp[C__DATA__VALUE];
                    if (defined('C__CATG__IP')) {
                        $l_new_arr[$l_key][1] = $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__IP')][$l_vmkp['ref_id']];
                    }
                }
            }
            $l_arr = $l_new_arr;
        }

        return $l_arr;
    }

    /**
     * Returns an interface node
     *
     * @param int $p_catg_netp_list_id
     *
     * @return isys_export_data
     */
    public function interface_p($p_catg_netp_list_id)
    {
        $l_interface = [];

        if ($p_catg_netp_list_id > 0) {
            $l_dao = isys_cmdb_dao_category_g_network_interface::instance($this->m_database);
            $l_ifacedata = $l_dao->get_data($p_catg_netp_list_id);

            $l_row = $l_ifacedata->get_row();

            $l_interface[] = [
                "title"        => $l_row["isys_catg_netp_list__title"],
                "id"           => $l_row["isys_catg_netp_list__id"],
                "serial"       => $l_row["isys_catg_netp_list__serial"],
                "slot"         => $l_row["isys_catg_netp_list__slotnumber"],
                "manufacturer" => $l_row["isys_iface_manufacturer__title"],
                "model"        => $l_row["isys_iface_model__title"],
                "type"         => 'C__CATG__NETWORK_INTERFACE'
            ];
        }

        return new isys_export_data($l_interface);
    }

    /**
     * Import method for interfaces.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function interface_p_import($p_value)
    {
        $l_value_id = 0;

        if (is_array($p_value[C__DATA__VALUE])) {
            if (array_key_exists('0', $p_value[C__DATA__VALUE])) {
                $l_value_id = $p_value[C__DATA__VALUE][0]['id'];
            }
        } elseif (array_key_exists('id', $p_value)) {
            $l_value_id = $p_value['id'];
        } else {
            return null;
        }

        // @todo  Clean up in i-doit 1.12
        if (defined('C__CMDB__SUBCAT__NETWORK_INTERFACE_P') && isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CMDB__SUBCAT__NETWORK_INTERFACE_P')][$l_value_id])) {
            return $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CMDB__SUBCAT__NETWORK_INTERFACE_P')][$l_value_id];
        }

        if (defined('C__CATG__NETWORK_INTERFACE') && isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__NETWORK_INTERFACE')][$l_value_id])) {
            return $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__NETWORK_INTERFACE')][$l_value_id];
        }

        return null;
    }

    // TODO

    /**
     * @param $p_fcport_id
     *
     * @return bool|isys_export_data
     * @throws isys_exception_database
     */
    public function fc_san($p_fcport_id)
    {
        $l_arr = [];
        if (!empty($p_fcport_id)) {
            $l_dao = isys_cmdb_dao_category_g_controller_fcport::instance($this->m_database);

            $l_sql = "SELECT * FROM isys_san_zoning_fc_port " .
                "INNER JOIN isys_cats_san_zoning_list ON isys_san_zoning_fc_port__isys_cats_san_zoning_list__id = isys_cats_san_zoning_list__id " .
                "INNER JOIN isys_obj ON isys_obj__id = isys_cats_san_zoning_list__isys_obj__id " .
                "INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id " . "WHERE isys_san_zoning_fc_port__isys_catg_fc_port_list__id = " .
                $l_dao->convert_sql_id($p_fcport_id) . ";";

            $l_res = $l_dao->retrieve($l_sql);

            if ($l_res->num_rows() > 0) {
                while ($l_row = $l_res->get_row()) {
                    $l_arr[] = [
                        'id'            => $l_row['isys_obj__id'],
                        'type'          => $l_row['isys_obj_type__const'],
                        'sysid'         => $l_row['isys_obj__sysid'],
                        'title'         => $l_row['isys_obj__title'],
                        'port_selected' => $l_row['isys_san_zoning_fc_port__port_selected'],
                        'wwn_selected'  => $l_row['isys_san_zoning_fc_port__wwn_selected']
                    ];
                }

                return new isys_export_data($l_arr);
            }
        }

        return false;
    }

    /**
     * Import method for FC san.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function fc_san_import($p_value)
    {
        $l_data = null;
        if (is_array($p_value) && array_key_exists(C__DATA__VALUE, $p_value)) {
            $l_data = $p_value[C__DATA__VALUE];
            $l_dao_san_zone = isys_cmdb_dao_category_s_san_zoning::instance($this->m_database);

            if (is_array($l_data) && count($l_data)) {
                foreach ($l_data as $l_key => $l_value) {
                    if (is_array($l_value)) {
                        if (array_key_exists($l_value['id'], $this->m_object_ids)) {
                            $l_obj_id = $this->m_object_ids[$l_value['id']];
                            $l_catdata = $l_dao_san_zone->get_data(null, $l_obj_id)
                                ->get_row();

                            if (!$l_catdata) {
                                $l_data[$l_key][C__DATA__VALUE] = $l_dao_san_zone->create($l_obj_id, C__RECORD_STATUS__NORMAL, $l_value['title'], null);
                            } else {
                                $l_data[$l_key][C__DATA__VALUE] = $l_catdata['isys_cats_san_zoning_list__id'];
                            }
                        }
                    } else {
                        break;
                    }
                }
            }
        }

        return $l_data;
    }

    /**
     * Export a connector sibling
     *
     * @param $p_connector_id
     *
     * @return array
     * @internal param int $p_connector_list
     */
    public function connector_sibling($p_connector_id)
    {
        if ($p_connector_id) {
            $l_dao = isys_cmdb_dao_category_g_connector::instance($this->m_database);

            $l_res = $l_dao->get_sibling_mod($p_connector_id);

            if ($l_res->num_rows() > 0) {
                $l_sibling_data = $l_res->get_row();

                if (is_numeric($l_sibling_data["isys_catg_connector_list__assigned_category"])) {
                    $l_category_data = $l_dao->get_catg_by_const($l_sibling_data["isys_catg_connector_list__assigned_category"])
                        ->get_row();
                    $l_sibling_data["isys_catg_connector_list__assigned_category"] = $l_category_data['isysgui_catg__const'];
                }

                return [
                    "id"           => $l_sibling_data["isys_catg_connector_list__id"],
                    "title"        => $l_sibling_data["isys_catg_connector_list__title"],
                    "input_output" => $l_sibling_data["isys_catg_connector_list__type"],
                    "con_type"     => $l_sibling_data["isys_connection_type__title"],
                    "type"         => $l_sibling_data["isys_catg_connector_list__assigned_category"]
                ];
            }
        }

        return false;
    }

    /**
     * Import method for connector siblings.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function connector_sibling_import($p_value)
    {
        if (is_array($p_value) && array_key_exists('id', $p_value) && defined('C__CATG__CONNECTOR')) {
            if (is_array($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__CONNECTOR')])) {
                if (array_key_exists($p_value['id'], $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__CONNECTOR')])) {
                    return $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__CONNECTOR')][$p_value['id']];
                }
            } // ie;
        }

        return null;
    }

    /**
     * Export a connector
     *
     * @param $p_connector_id
     *
     * @return array
     * @internal param int $p_connector_list
     */
    public function connector($p_connector_id)
    {
        if ($p_connector_id > 0) {
            $l_dao = isys_cmdb_dao_category_g_connector::instance($this->m_database);
            $l_connector = $l_dao->get_data($p_connector_id);

            $l_daoCable = new isys_cmdb_dao_cable_connection($this->m_database);
            $l_connected = $l_daoCable->get_assigned_connector_id($p_connector_id);
            $l_cable = $l_daoCable->get_assigned_cable($p_connector_id);

            if ($l_connector->num_rows() > 0) {
                $l_connector_data = $l_connector->get_row();

                if (is_numeric($l_connector_data["isys_catg_connector_list__assigned_category"])) {
                    $l_category = $l_dao->get_catg_by_const($l_connector_data["isys_catg_connector_list__assigned_category"])
                        ->get_row();
                    $l_connector_data["isys_catg_connector_list__assigned_category"] = $l_category["isysgui_catg__const"];
                }

                return [
                    "id"                 => $l_connector_data["isys_catg_connector_list__id"],
                    "title"              => $l_connector_data["isys_catg_connector_list__title"],
                    "connection_type"    => $l_connector_data["isys_catg_connector_list__type"],
                    "con_type"           => $l_connector_data["isys_connection_type__title"],
                    "sibling_id"         => $l_connector_data["isys_catg_connector_list__isys_catg_connector_list__id"],
                    "cable_connection"   => $l_connector_data["isys_catg_connector_list__isys_cable_connection__id"],
                    "assigned_connector" => $l_connected,
                    "cable_id"           => $l_cable,
                    "type"               => $l_connector_data["isys_catg_connector_list__assigned_category"]
                ];
            }
        }

        return [];
    }

    /**
     * Import method for a connector. Is this intended?
     *
     * @param   array $p_value
     *
     * @return  null
     */
    public function connector_import($p_value)
    {
        if (is_countable($p_value) && count($p_value) > 0 && isset($p_value['id'])) {
            $l_type = null;
            if (is_string($p_value['type']) && defined($p_value['type'])) {
                $l_type = constant($p_value['type']);
            } elseif (is_numeric($p_value['tpe'])) {
                $l_type = $p_value['type'];
            }

            if ($l_type !== null && is_array($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL])) {
                if (isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_type][$p_value['id']])) {
                    return $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_type][$p_value['id']];
                }
            }
        }

        return null;
    }

    /**
     * @param $p_uiID
     *
     * @return array|isys_export_data
     */
    public function ui_connector($p_uiID)
    {
        if ($p_uiID) {
            $l_dao = isys_cmdb_dao_category_g_ui::instance($this->m_database);
            $l_res = $l_dao->get_connector($p_uiID);

            if ($l_res->num_rows()) {
                $l_row = $l_res->get_row();
                $l_arr[] = [
                    'id'    => $l_row['isys_catg_connector_list__id'],
                    'title' => $l_row['isys_catg_connector_list__title'],
                    'type'  => 'C__CATG__CONNECTOR'
                ];

                return new isys_export_data($l_arr);
            }
        }

        return [];
    }

    /**
     * @param $p_psID
     *
     * @return array|isys_export_data
     */
    public function psupplier_connector($p_psID)
    {
        if ($p_psID) {
            $l_dao = isys_cmdb_dao_category_g_power_supplier::instance($this->m_database);
            $l_res = $l_dao->get_connector_mod($p_psID);
            $l_arr = [];

            if ($l_res->num_rows()) {
                $l_row = $l_res->get_row();
                $l_arr[] = [
                    'id'    => $l_row['isys_catg_connector_list__id'],
                    'title' => $l_row['isys_catg_connector_list__title'],
                    'type'  => 'C__CATG__POWER_SUPPLIER'
                ];

                return new isys_export_data($l_arr);
            }
        }

        return [];
    }

    /**
     * Returns an assigned connector node
     *
     * @param $p_cable_connection
     *
     * @return isys_export_data
     * @internal param int $p_catg_netp_list_id
     */
    public function cable_connection($p_cable_connection)
    {
        $l_aAssignedOobjectField = [];

        if ($p_cable_connection > 0) {
            $l_dao_cable_connection = new isys_cmdb_dao_cable_connection($this->m_database);
            $l_cable_connection = $l_dao_cable_connection->get_cable_connection($p_cable_connection);

            $l_cable_data = $l_cable_connection->get_row();

            $l_cable_object = $this->object($l_cable_data["isys_cable_connection__isys_obj__id"]);

            $l_aAssignedOobjectField[] = [
                "id"       => $l_cable_object["id"],
                "title"    => $l_cable_object["title"],
                "cable_id" => $l_cable_data["isys_cable_connection__id"],
                "sysid"    => $l_cable_object["sysid"],
                "type"     => $l_cable_object["type"]
            ];
        }

        return new isys_export_data($l_aAssignedOobjectField);
    }

    /**
     * Import method for the cable connections.
     *
     * @param   array $p_value
     *
     * @return  array
     */
    public function cable_connection_import($p_value)
    {
        /* Let us update the object-title of the cable */
        /* @var $l_dao isys_cmdb_dao */
        $l_dao = isys_cmdb_dao::instance($this->m_database);
        if (is_array($p_value['value'])) {
            if (is_array($p_value['value'][0])) {
                $l_dao->update_object($this->m_object_ids[$p_value['value'][0]['id']], null, $p_value['value'][0]['title']);

                return $this->m_object_ids[$p_value['value'][0]['id']];
            } elseif (is_numeric($p_value['value'][0])) {
                return $p_value['value'][0];
            }
        } elseif (is_numeric($p_value['value'])) {
            return $p_value['value'];
        }

        return null;
    }

    /**
     * Returns an assigned connector node.
     *
     * @param   integer $p_connector_id
     *
     * @return  mixed  Object of type isys_export_data or an empty array.
     */
    public function assigned_connector($p_connector_id)
    {
        if ($p_connector_id > 0) {
            $l_dao = isys_cmdb_dao_category_g_connector::instance($this->m_database);

            if (isset($this->m_data_info[C__PROPERTY__DATA__FIELD_ALIAS])) {
                if (isset($this->m_row)) {
                    $l_assigned_connector_id = $this->m_row[$this->m_data_info[C__PROPERTY__DATA__FIELD_ALIAS]];
                } else {
                    $l_data = $l_dao->get_data($p_connector_id)
                        ->get_row();
                    $l_assigned_connector_id = $l_data[$this->m_data_info[C__PROPERTY__DATA__FIELD_ALIAS]];
                }

                if ($l_assigned_connector_id > 0) {
                    $l_assigned_connector = $l_dao->get_data($l_assigned_connector_id)
                        ->__to_array();
                    $l_object_data = $l_dao->get_object_by_id($l_assigned_connector['isys_catg_connector_list__isys_obj__id'])
                        ->get_row();

                    $l_aAssignedOobjectField[] = [
                        'connector_type'    => $l_assigned_connector['isys_catg_connector_list__type'],
                        'name'              => $l_assigned_connector['isys_catg_connector_list__title'],
                        'id'                => $l_object_data['isys_obj__id'],
                        'title'             => $l_object_data['isys_obj__title'],
                        'sysid'             => $l_object_data['isys_obj__sysid'],
                        'type'              => $l_object_data['isys_obj_type__const'],
                        'assigned_category' => $l_assigned_connector['isys_catg_connector_list__assigned_category'],
                        'ref_id'            => $l_assigned_connector['isys_catg_connector_list__id'],
                        'ref_title'         => $l_assigned_connector['isys_catg_connector_list__title'],
                        'ref_type'          => 'C__CATG__CONNECTOR',
                        'con_type'          => $l_assigned_connector["isys_connection_type__title"]
                    ];

                    return new isys_export_data($l_aAssignedOobjectField);
                }

                return [];
            }
        }

        return [];
    }

    /**
     * Import method for the connectors.
     *
     * @param   array $p_value
     *
     * @return  mixed  Integer of the connector or null if none is found.
     */
    public function assigned_connector_import($p_value)
    {
        $l_dao = isys_cmdb_dao_category_g_connector::instance($this->m_database);

        if ($this->m_mode != isys_import_handler_cmdb::C__APPEND) {
            if (is_numeric($p_value[C__DATA__VALUE])) {
                return $p_value[C__DATA__VALUE];
            }

            if (is_array($p_value[C__DATA__VALUE])) {
                $l_obj_id = null;
                if (isset($p_value[C__DATA__VALUE][0]['id']) && array_key_exists($p_value[C__DATA__VALUE][0]['id'], $this->m_object_ids)) {
                    $l_obj_id = $this->m_object_ids[$p_value[C__DATA__VALUE][0]['id']];
                } elseif (isset($p_value[C__DATA__VALUE][0]['id']) && in_array($p_value[C__DATA__VALUE][0]['id'], $this->m_object_ids) &&
                    $this->m_mode == isys_import_handler_cmdb::C__MERGE) {
                    $l_obj_id = $p_value[C__DATA__VALUE][0]['id'];
                } else {
                    $l_sql = 'SELECT isys_obj__id FROM isys_obj WHERE isys_obj__sysid = ' . $l_dao->convert_sql_text($p_value[C__DATA__VALUE][0]['sysid']);
                    $l_res = $l_dao->retrieve($l_sql);
                    if ($l_res->num_rows() > 0) {
                        $l_obj_id = $l_res->get_row_value('isys_obj__id');
                    }
                }

                if ($l_obj_id > 0) {
                    $l_category_string = null;
                    $l_category_id = null;

                    if (is_numeric($p_value[C__DATA__VALUE][0]['assigned_category'])) {
                        $l_category_string = $l_dao->retrieve('SELECT isysgui_catg__const FROM isysgui_catg WHERE isysgui_catg__id = ' .
                            $l_dao->convert_sql_id($p_value[C__DATA__VALUE][0]['assigned_category']))
                            ->get_row();
                        $l_category_string = $l_category_string['isysgui_catg__const'];
                        $l_category_id = $p_value[C__DATA__VALUE][0]['assigned_category'];
                    } else {
                        $l_category_string = $p_value[C__DATA__VALUE][0]['assigned_category'];
                        if (defined($p_value[C__DATA__VALUE][0]['assigned_category'])) {
                            $l_category_id = constant($p_value[C__DATA__VALUE][0]['assigned_category']);
                        }
                    }

                    $l_sql = "SELECT isys_catg_connector_list__id FROM isys_catg_connector_list " . "WHERE isys_catg_connector_list__isys_obj__id = " .
                        $l_dao->convert_sql_id($l_obj_id) . " " . "AND isys_catg_connector_list__title = " . $l_dao->convert_sql_text($p_value[C__DATA__VALUE][0]['ref_title']) .
                        " " . "AND (isys_catg_connector_list__assigned_category LIKE " . $l_dao->convert_sql_text($l_category_string);

                    if ($l_category_id !== null) {
                        $l_sql .= " OR isys_catg_connector_list__assigned_category = " . $l_dao->convert_sql_id($l_category_id);
                    }
                    $l_sql .= ");";

                    $l_data = $l_dao->retrieve($l_sql)
                        ->get_row();

                    if ($l_data) {
                        return $l_data['isys_catg_connector_list__id'];
                    } else {
                        $l_id = null;
                        if (is_value_in_constants($l_category_id, ['C__CATG__NETWORK_PORT', 'C__CMDB__SUBCAT__NETWORK_PORT'])) {
                            $l_dao_object = isys_cmdb_dao_category_g_network_port::instance($this->m_database);
                            $l_id = $l_dao_object->get_connector($l_dao_object->create(
                                $l_obj_id,
                                $p_value[C__DATA__VALUE][0]['ref_title'],
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
                                null,
                                null,
                                null,
                                null,
                                C__RECORD_STATUS__NORMAL
                            ));
                        } else {
                            $l_id = $l_dao->create(
                                $l_obj_id,
                                $p_value[C__DATA__VALUE][0]['ref_type'],
                                null,
                                null,
                                $p_value[C__DATA__VALUE][0]['ref_title'],
                                null,
                                null,
                                null,
                                $p_value[C__DATA__VALUE][0]['assigned_category']
                            );
                        }

                        return $l_id;
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param $p_id
     *
     * @return array|bool
     */
    public function ui($p_id)
    {
        if (empty($p_id)) {
            return false;
        }

        $l_dao_ui = isys_cmdb_dao_category_g_ui::instance($this->m_database);

        $l_data = $l_dao_ui->get_data($p_id)
            ->__to_array();
        if (!is_array($l_data)) {
            return false;
        }

        $cacheObjectType = $this->getCacheContent('object_type_rows', $l_data['isys_obj__isys_obj_type__id']);

        if (!$cacheObjectType) {
            $cacheObjectType = $l_dao_ui->get_objtype($l_data['isys_obj__isys_obj_type__id'])
                ->get_row();

            $this->setCacheContent('object_type_rows', $l_data['isys_obj__isys_obj_type__id'], $cacheObjectType);
        }

        return [
            'id'          => $l_data['isys_obj__id'],
            'title'       => $l_data['isys_obj__title'],
            'sysid'       => $l_data['isys_obj__sysid'],
            'type'        => $cacheObjectType['isys_obj_type__const'],
            'ref_id'      => $l_data['isys_catg_ui_list__id'],
            'ref_title'   => $l_data['isys_catg_ui_list__title'],
            'ref_type'    => 'C__CATG__UNIVERSAL_INTERFACE',
            'ui_con_type' => $this->translate($l_data['isys_ui_con_type__title'])
        ];
    }

    /**
     * Import method for UI.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function ui_import($p_value)
    {
        $l_from_object = false;
        if (!empty($p_value['id']) && !empty($p_value['ref_id'])) {
            if (array_key_exists($p_value['id'], $this->m_object_ids)) {
                if (defined('C__CATG__UNIVERSAL_INTERFACE') && is_array($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__UNIVERSAL_INTERFACE')])) {
                    if (array_key_exists($p_value['ref_id'], $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__UNIVERSAL_INTERFACE')])) {
                        $l_from_object = true;
                    }
                }

                if ($l_from_object && defined('C__CATG__UNIVERSAL_INTERFACE')) {
                    return $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__UNIVERSAL_INTERFACE')][$p_value['ref_id']];
                } else {
                    $l_dao = isys_cmdb_dao_category_g_ui::instance($this->m_database);
                    $l_res = $l_dao->get_data(null, $this->m_object_ids[$p_value['id']], ' AND isys_catg_ui_list__title = ' . $l_dao->convert_sql_text($p_value['ref_title']));
                    if ($l_res->num_rows() > 0) {
                        $l_data = $l_res->get_row();
                        $l_id = $l_data['isys_catg_ui_list__id'];
                    } else {
                        $l_id = $l_dao->create($this->m_object_ids[$p_value['id']], C__RECORD_STATUS__NORMAL, $p_value['ref_title'], null, null, null, null, null, '', null);
                    }

                    return $l_id;
                }
            }

            return false;
        }

        return false;
    }

    /**
     * @param $p_port_id
     *
     * @return array
     */
    public function port($p_port_id)
    {
        $l_ret = [];

        if ($p_port_id > 0) {
            $l_dao_port = isys_cmdb_dao_category_g_network_port::instance($this->m_database);
            $l_row = $l_dao_port->get_data($p_port_id)
                ->__to_array();

            if ($l_row) {
                if ($l_row['isys_obj__id'] == $this->m_row['isys_obj__id']) {
                    $l_ret = [
                        'id'              => $l_row['isys_catg_port_list__id'],
                        'title'           => $l_row['isys_catg_port_list__title'],
                        'mac'             => $l_row['isys_catg_port_list__mac'],
                        'interface'       => $this->interface_p($l_row['isys_catg_port_list__isys_catg_netp_list__id']),
                        'enabled'         => $l_row['isys_catg_port_list__state_enabled'],
                        'number'          => $l_row['isys_catg_port_list__number'],
                        'port_speed'      => $l_row['isys_catg_port_list__port_speed_value'],
                        'port_speed_unit' => $this->dialog_plus($l_row['isys_catg_port_list__isys_port_speed__id'], 'isys_port_speed'),
                        'port_type'       => $this->dialog_plus($l_row['isys_catg_port_list__isys_port_type__id'], 'isys_port_type'),
                        'plug_type'       => $this->dialog_plus($l_row['isys_catg_port_list__isys_plug_type__id'], 'isys_connection_type'),
                        'port_duplex'     => $this->dialog_plus($l_row['isys_catg_port_list__isys_port_duplex__id'], 'isys_port_duplex'),
                        'standard'        => $this->dialog_plus($l_row['isys_catg_port_list__isys_port_standard__id'], 'isys_port_standard'),
                        'negotiation'     => $this->dialog_plus($l_row['isys_catg_port_list__isys_port_negotiation__id'], 'isys_port_negotiation'),
                        'description'     => $l_row['isys_catg_port_list__description'],
                        'type'            => 'C__CATG__NETWORK_PORT'
                    ];
                } else {
                    $l_object_type = $l_dao_port->get_objtype($l_row['isys_obj__isys_obj_type__id'])
                        ->__to_array();

                    $l_ret = [
                        'id'              => $l_row['isys_obj__id'],
                        'title'           => $l_row['isys_obj__title'],
                        'sysid'           => $l_row['isys_obj__sysid'],
                        'type'            => $l_object_type['isys_obj_type__const'],
                        'type_title'      => $this->translate($l_object_type['isys_obj_type__title']),
                        'ref_id'          => $l_row['isys_catg_port_list__id'],
                        'ref_title'       => $l_row['isys_catg_port_list__title'],
                        'ref_type'        => 'C__CATG__NETWORK_PORT',
                        'mac'             => $l_row['isys_catg_port_list__mac'],
                        'interface'       => $this->interface_p($l_row['isys_catg_port_list__isys_catg_netp_list__id']),
                        'enabled'         => $l_row['isys_catg_port_list__state_enabled'],
                        'number'          => $l_row['isys_catg_port_list__number'],
                        'port_speed'      => $l_row['isys_catg_port_list__port_speed_value'],
                        'port_speed_unit' => $this->dialog_plus($l_row['isys_catg_port_list__isys_port_speed__id'], 'isys_port_speed'),
                        'port_type'       => $this->dialog_plus($l_row['isys_catg_port_list__isys_port_type__id'], 'isys_port_type'),
                        'plug_type'       => $this->dialog_plus($l_row['isys_catg_port_list__isys_plug_type__id'], 'isys_connection_type'),
                        'port_duplex'     => $this->dialog_plus($l_row['isys_catg_port_list__isys_port_duplex__id'], 'isys_port_duplex'),
                        'standard'        => $this->dialog_plus($l_row['isys_catg_port_list__isys_port_standard__id'], 'isys_port_standard'),
                        'negotiation'     => $this->dialog_plus($l_row['isys_catg_port_list__isys_port_negotiation__id'], 'isys_port_negotiation'),
                        'description'     => $l_row['isys_catg_port_list__description'],
                    ];
                }
            }
        }

        return $l_ret;
    }

    /**
     * Import helper for ports.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function port_import($p_value)
    {
        $l_dao_port = isys_cmdb_dao_category_g_network_port::instance($this->m_database);

        if ($p_value[C__DATA__TAG] == 'local_port') {
            $category = defined_or_default('C__CATG__NETWORK_PORT', 'C__CATG__NETWORK_PORT');
            if (array_key_exists($p_value['id'], $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category])) {
                return $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category][$p_value['id']];
            }
        } elseif ($p_value[C__DATA__TAG] == 'host_port') {
            if (array_key_exists($p_value['id'], $this->m_object_ids)) {
                $l_port_data = $l_dao_port->get_data(
                    null,
                    $this->m_object_ids[$p_value['id']],
                    'AND isys_catg_port_list__title LIKE ' . $l_dao_port->convert_sql_text($p_value['ref_title']),
                    null,
                    C__RECORD_STATUS__NORMAL
                )
                    ->get_row();

                if (!$l_port_data && $this->m_mode !== isys_import_handler_cmdb::C__APPEND) {
                    $p_value['ref_id'] = $l_dao_port->create(
                        $this->m_object_ids[$p_value['id']],
                        $p_value['ref_title'],
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
                        null,
                        null,
                        null,
                        null,
                        C__RECORD_STATUS__NORMAL,
                        null,
                        null
                    );
                } elseif (is_array($l_port_data)) {
                    $p_value['ref_id'] = $l_port_data['isys_catg_port_list__id'];
                } else {
                    $p_value['ref_id'] = null;
                }

                return $p_value;
            }
        } else {
            if (!is_array($p_value[C__DATA__VALUE])) {
                if (array_key_exists($p_value['id'], $this->m_object_ids)) {
                    $l_obj_id = $this->m_object_ids[$p_value['id']];

                    $l_port_data = $l_dao_port->get_data(null, $l_obj_id, " AND isys_catg_port_list__title LIKE " . $l_dao_port->convert_sql_text($p_value['ref_title']))
                        ->get_row();

                    if (!$l_port_data && $this->m_mode !== isys_import_handler_cmdb::C__APPEND) {
                        $p_value['ref_id'] = $l_dao_port->create(
                            $l_obj_id,
                            $p_value['ref_title'],
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
                            null,
                            null,
                            null,
                            null,
                            C__RECORD_STATUS__NORMAL,
                            null,
                            null
                        );
                    } elseif ($l_port_data) {
                        $p_value['ref_id'] = $l_port_data['isys_catg_port_list__id'];
                    } else {
                        $p_value['ref_id'] = null;
                    }

                    return $p_value;
                }
            } else {
                return $p_value[C__DATA__VALUE];
            }
        }

        return null;
    }

    /**
     * Export helper for category assigned logical ports for object type layer-2 net
     *
     * @param $p_port_id
     *
     * @return array
     */
    public function logical_port($p_port_id)
    {
        $l_ret = [];

        if ($p_port_id > 0) {
            $l_dao_port = isys_cmdb_dao_category_g_network_ifacel::instance($this->m_database);
            $l_row = $l_dao_port->get_data($p_port_id)
                ->__to_array();

            if ($l_row) {
                $l_ret = [
                    'id'         => $l_row['isys_obj__id'],
                    'title'      => $l_row['isys_obj__title'],
                    'sysid'      => $l_row['isys_obj__sysid'],
                    'type'       => $l_row['isys_obj_type__const'],
                    'type_title' => $this->translate($l_row['isys_obj_type__title']),
                    'ref_id'     => $l_row['isys_catg_log_port_list__id'],
                    'ref_title'  => $l_row['isys_catg_log_port_list__title'],
                    'ref_type'   => 'C__CATG__NETWORK_LOG_PORT',
                    'mac'        => $l_row['isys_catg_log_port_list__mac']
                ];
            }
        }

        return $l_ret;
    }

    /**
     * Import helper for assigned logical ports for object type layer-2 net
     *
     * @param $p_value
     *
     * @return mixed
     */
    public function logical_port_import($p_value)
    {
        if (!is_array($p_value[C__DATA__VALUE])) {
            if (array_key_exists($p_value['id'], $this->m_object_ids)) {
                $l_obj_id = $this->m_object_ids[$p_value['id']];

                /**
                 * @var $l_dao_port isys_cmdb_dao_category_g_network_ifacel
                 */
                $l_dao_port = isys_cmdb_dao_category_g_network_ifacel::instance($this->m_database);
                $l_port_data = $l_dao_port->get_data(
                    null,
                    $l_obj_id,
                    " AND isys_catg_log_port_list.isys_catg_log_port_list__title LIKE " . $l_dao_port->convert_sql_text($p_value['ref_title'])
                )
                    ->get_row();

                if (!$l_port_data && $this->m_mode !== isys_import_handler_cmdb::C__APPEND) {
                    //($p_object_id, $p_title, $p_net, $p_active, $p_standard, $p_type, $p_ports, $p_description, $p_status = C__RECORD_STATUS__NORMAL, $p_addresses = null, $p_mac = NULL, $p_parent = null, $p_connected_logport = null)
                    $p_value['ref_id'] = $l_dao_port->create(
                        $l_obj_id,
                        $p_value['ref_title'],
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        C__RECORD_STATUS__NORMAL,
                        null,
                        $p_value['mac'],
                        null,
                        null
                    );
                } elseif ($l_port_data) {
                    $p_value['ref_id'] = $l_port_data['isys_catg_port_list__id'];
                } else {
                    $p_value['ref_id'] = null;
                }

                return $p_value;
            }
        } else {
            return $p_value[C__DATA__VALUE];
        }

        return null;
    }

    /**
     * @param $p_id
     *
     * @return array
     */
    public function storage_device($p_id)
    {
        $l_ret = [];

        if ($p_id > 0) {
            $l_row = isys_cmdb_dao_category_g_stor::instance($this->m_database)
                ->get_data($p_id)
                ->get_row();

            $l_ret = [
                "id"                     => $l_row['isys_obj__id'],
                "sysid"                  => $l_row['isys_obj__sysid'],
                "type"                   => $l_row['isys_obj_type__const'],
                "title"                  => $l_row['isys_obj__title'],
                "ref_id"                 => $p_id,
                "ref_title"              => $l_row["isys_catg_stor_list__title"],
                "ref_type"               => "C__CATG__STORAGE_DEVICE",
                "stor_device_type_id"    => $l_row['isys_stor_type__id'],
                "stor_device_type_const" => $l_row['isys_stor_type__const'],
                "stor_device_type_title" => $l_row['isys_stor_type__title']
            ];
        }

        return $l_ret;
    }

    /**
     * Import method for storage devices.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function storage_device_import($p_value)
    {
        $l_from_object = false;
        if (!empty($p_value['id']) && !empty($p_value['ref_id'])) {
            if (array_key_exists($p_value['id'], $this->m_object_ids)) {
                $subcatCategory = defined_or_default('C__CMDB__SUBCAT__STORAGE__DEVICE', 'C__CMDB__SUBCAT__STORAGE__DEVICE');
                if (is_array($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$subcatCategory])) {
                    if (array_key_exists($p_value['ref_id'], $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$subcatCategory])) {
                        $l_from_object = true;
                    }
                }

                $category = defined_or_default('C__CATG__STORAGE_DEVICE', 'C__CATG__STORAGE_DEVICE');
                if (is_array($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category])) {
                    if (array_key_exists($p_value['ref_id'], $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category])) {
                        $l_from_object = true;
                    }
                }

                if ($l_from_object) {
                    return $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$subcatCategory][$p_value['ref_id']] ?: $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category][$p_value['ref_id']];
                } else {
                    $l_dao = isys_cmdb_dao_category_g_stor::instance($this->m_database);
                    $l_res = $l_dao->get_data(
                        null,
                        $this->m_object_ids[$p_value['id']],
                        ' AND isys_catg_stor_list__title = ' . $l_dao->convert_sql_text($p_value['ref_title'])
                    );
                    if ($l_res->num_rows() > 0) {
                        $l_data = $l_res->get_row();
                        $l_id = $l_data['isys_catg_stor_list__id'];
                    } else {
                        $l_id = $l_dao->create(
                            $this->m_object_ids[$p_value['id']],
                            isys_import_handler::check_dialog('isys_stor_type', $p_value['stor_device_type_title']),
                            C__RECORD_STATUS__NORMAL,
                            $p_value['ref_title'],
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            false,
                            null,
                            ''
                        );
                    }

                    return $l_id;
                }
            }
        }

        return null;
    }

    /**
     * @param $p_id
     *
     * @return array
     */
    public function guest_systems($p_id)
    {
        return $this->object($p_id);
    }

    /**
     * Import method for guest systems.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function guest_systems_import($p_value)
    {
        if ($this->m_mode == isys_import_handler_cmdb::C__APPEND) {
            return null;
        }

        return $this->object_import($p_value);
    }

    /**
     * @param $p_id
     *
     * @return array
     */
    public function storage_raid($p_id)
    {
        if ($p_id > 0) {
            $l_dao = isys_cmdb_dao_category_g_raid::instance($this->m_database);
            $l_name = $l_dao->get_device_name($p_id);

            return [
                "id"    => $p_id,
                "title" => $l_name
            ];
        }

        return [];
    }

    /**
     * Import method for raid storage.
     *
     * @return  boolean
     */
    public function storage_raid_import()
    {
        return null;
    }

    /**
     * Export method for LDEV storage for category virtual devices
     *
     * @param $p_id
     *
     * @return array
     */
    public function storage_ldev($p_id)
    {
        if ($p_id > 0) {
            $l_dao = isys_cmdb_dao_category_g_ldevclient::instance($this->m_database);

            $l_data = $l_dao->get_data($p_id)
                ->get_row();

            $l_objtype = $l_dao->get_objtype($l_data['isys_obj__isys_obj_type__id'])
                ->get_row();

            $l_arr = [
                'id'        => $l_data['isys_obj__id'],
                'sysid'     => $l_data['isys_obj__sysid'],
                'type'      => $l_objtype['isys_obj_type__const'],
                'title'     => $l_data['isys_obj__title'],
                'ref_id'    => $l_data['isys_catg_ldevclient_list__id'],
                'ref_title' => $l_data['isys_catg_ldevclient_list__title'],
                'ref_type'  => 'C__CATG__LDEV_CLIENT'
            ];

            return $l_arr;
        }

        return [];
    }

    /**
     * Import method for LDEV storage for category virtual devices
     *
     * @param $p_value
     *
     * @return bool
     */
    public function storage_ldev_import($p_value)
    {
        $l_from_object = false;
        if (!empty($p_value['id']) && !empty($p_value['ref_id'])) {
            if (array_key_exists($p_value['id'], $this->m_object_ids)) {
                $category = defined_or_default('C__CATG__LDEV_CLIENT', 'C__CATG__LDEV_CLIENT');
                if (is_array($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category])) {
                    if (array_key_exists($p_value['ref_id'], $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category])) {
                        $l_from_object = true;
                    }
                }

                if ($l_from_object) {
                    return $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category][$p_value['ref_id']];
                } else {
                    $l_dao = isys_cmdb_dao_category_g_ldevclient::instance($this->m_database);
                    $l_res = $l_dao->get_data(
                        null,
                        $this->m_object_ids[$p_value['id']],
                        ' AND isys_catg_ldevclient_list__title = ' . $l_dao->convert_sql_text($p_value['ref_title'])
                    );
                    if ($l_res->num_rows() > 0) {
                        $l_data = $l_res->get_row();
                        $l_id = $l_data['isys_catg_ldevclient_list__id'];
                    } else {
                        $l_id = $l_dao->create($this->m_object_ids[$p_value['id']], C__RECORD_STATUS__NORMAL, $p_value['ref_title'], null, null, null, null, null);
                    }

                    return $l_id;
                }
            }
        }

        return null;
    }

    /**
     * Export method for drive storage for category virtual devices
     *
     * @param $p_id
     *
     * @return array
     */
    public function storage_drive($p_id)
    {
        if ($p_id > 0) {
            $l_dao = isys_cmdb_dao_category_g_drive::instance($this->m_database);

            $l_data = $l_dao->get_data($p_id)
                ->get_row();

            $l_objtype = $l_dao->get_objtype($l_data['isys_obj__isys_obj_type__id'])
                ->get_row();

            $l_arr = [
                'id'        => $l_data['isys_obj__id'],
                'sysid'     => $l_data['isys_obj__sysid'],
                'type'      => $l_objtype['isys_obj_type__const'],
                'title'     => $l_data['isys_obj__title'],
                'ref_id'    => $l_data['isys_catg_drive_list__id'],
                'ref_title' => $l_data['isys_catg_drive_list__title'],
                'ref_type'  => 'C__CATG__DRIVE'
            ];

            return $l_arr;
        }

        return [];
    }

    /**
     * Import method for drive storage for category virtual devices
     *
     * @param $p_value
     *
     * @return bool
     */
    public function storage_drive_import($p_value)
    {
        $l_from_object = false;
        if (!empty($p_value['id']) && !empty($p_value['ref_id'])) {
            if (array_key_exists($p_value['id'], $this->m_object_ids)) {
                $category = defined_or_default('C__CATG__DRIVE', 'C__CATG__DRIVE');
                if (is_array($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category])) {
                    if (array_key_exists($p_value['ref_id'], $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category])) {
                        $l_from_object = true;
                    }
                }

                if ($l_from_object) {
                    return $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category][$p_value['ref_id']];
                } else {
                    $l_dao = isys_cmdb_dao_category_g_drive::instance($this->m_database);
                    $l_res = $l_dao->get_data(null, $p_value['id'], ' AND isys_catg_drive_list__title = ' . $l_dao->convert_sql_text($p_value['ref_title']));
                    if ($l_res->num_rows() > 0) {
                        $l_data = $l_res->get_row();
                        $l_id = $l_data['isys_catg_drive_list__id'];
                    } else {
                        $l_id = $l_dao->create($p_value['id'], C__RECORD_STATUS__NORMAL, null, null, $p_value['ref_title']);
                    }

                    return $l_id;
                }
            }
        }

        return null;
    }

    /**
     * @param $p_id
     *
     * @return array
     */
    public function ldevclient_ldevserver($p_id)
    {
        $l_arr = [];

        if ($p_id > 0) {
            $l_dao = isys_cmdb_dao_category_g_sanpool::instance($this->m_database);
            $l_data = $l_dao->get_ldevserver_by_obj_id_or_ldev_id(null, $p_id)
                ->get_row();

            $l_arr = [
                'id'        => $l_data['isys_obj__id'],
                'sysid'     => $l_data['isys_obj__sysid'],
                'type'      => $l_data['isys_obj_type__const'],
                'title'     => $l_data['isys_obj__title'],
                'ref_id'    => $p_id,
                'ref_title' => $l_data['isys_catg_sanpool_list__title'],
                'ref_type'  => 'C__CATG__LDEV_SERVER'
            ];
        }

        return $l_arr;
    }

    /**
     * Import method for LDEV client LDEV server.
     *
     * @param $p_value
     *
     * @return mixed
     */
    public function ldevclient_ldevserver_import($p_value)
    {
        $l_dao_sanpool = isys_cmdb_dao_category_g_sanpool::instance($this->m_database);

        if (array_key_exists($p_value['id'], $this->m_object_ids)) {
            $p_value[C__DATA__VALUE] = $this->m_object_ids[$p_value['id']];
            $l_found = false;
            $category = defined_or_default('C__CATG__LDEV_SERVER', 'C__CATG__LDEV_SERVER');
            if (is_array($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category])) {
                if (array_key_exists($p_value['ref_id'], $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category])) {
                    $p_value['ref_id'] = $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category][$p_value['ref_id']];
                    $l_found = true;
                }
            }

            if ($this->m_mode !== isys_import_handler_cmdb::C__APPEND) {
                if (!$l_found) {
                    $l_data = $l_dao_sanpool->get_data(
                        null,
                        $p_value[C__DATA__VALUE],
                        " AND isys_catg_sanpool_list__title LIKE " . $l_dao_sanpool->convert_sql_text($p_value['ref_title']),
                        null,
                        C__RECORD_STATUS__NORMAL
                    )
                        ->get_row();
                    if ($l_data) {
                        $p_value['ref_id'] = $l_data['isys_catg_sanpool_list__id'];
                    } else {
                        $p_value['ref_id'] = $l_dao_sanpool->create(
                            $p_value[C__DATA__VALUE],
                            C__RECORD_STATUS__NORMAL,
                            $p_value['ref_title'],
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null
                        );
                    }
                }

                return $p_value['ref_id'];
            }
        }

        return null;
    }

    /**
     * @param $p_id
     *
     * @return array
     */
    public function ldevclient_hba($p_id)
    {
        if ($p_id > 0) {
            $l_dao = isys_cmdb_dao_category_g_hba::instance($this->m_database);
            $l_name = $l_dao->get_device_name($p_id);

            return [
                "id"    => $p_id,
                "title" => $l_name
            ];
        }

        return [];
    }

    /**
     * @param $p_id
     *
     * @return bool|isys_export_data
     */
    public function ldevclient_assigned_paths($p_id)
    {
        if ($p_id > 0) {
            $l_dao = isys_cmdb_dao_category_g_ldevclient::instance($this->m_database);

            $l_res = $l_dao->get_paths($p_id);
            $l_primary_path = $l_dao->get_primary_path($p_id);
            $l_arr = [];

            if (is_object($l_res)) {
                $l_counter = 0;
                while ($l_row = $l_res->get_row()) {
                    $l_arr[$l_counter] = [
                        "id"    => $l_row["isys_catg_fc_port_list__id"],
                        "title" => $l_row["isys_catg_fc_port_list__title"],
                        "wwn"   => $l_row["isys_catg_fc_port_list__wwn"],
                        "wwpn"  => $l_row["isys_catg_fc_port_list__wwpn"],
                        "type"  => 'C__CATG__CONTROLLER_FC_PORT'
                    ];
                    if ($l_row["isys_catg_fc_port_list__id"] == $l_primary_path["isys_catg_ldevclient_list__primary_path"]) {
                        $l_arr[$l_counter]['primary'] = "1";
                    }
                    $l_counter++;
                }
            }

            return new isys_export_data($l_arr);
        }

        return false;
    }

    /**
     * Import method for LDEV clients assigned ports.
     *
     * @param   array $p_value
     *
     * @return  array
     */
    public function ldevclient_assigned_paths_import($p_value)
    {
        if (array_key_exists(C__DATA__VALUE, $p_value) && !empty($p_value[C__DATA__VALUE])) {
            $l_data = $p_value[C__DATA__VALUE];
            $l_arr = [];

            if (is_array($l_data) && count($l_data)) {
                foreach ($l_data as $l_val) {
                    if (is_array($l_val)) {
                        $l_arr[] = $this->get_reference_value_import($l_val);
                    } else {
                        break;
                    }
                }

                if (count($l_arr) > 0) {
                    $l_data = $l_arr;
                }
            }

            return $l_data;
        }

        return null;
    }

    /**
     * @param $p_id
     *
     * @return array|isys_export_data
     */
    public function ldev_paths($p_id)
    {
        if ($p_id) {
            $l_dao = isys_cmdb_dao_category_g_sanpool::instance($this->m_database);
            $l_res = $l_dao->get_paths($p_id);
            $l_primary_path = $l_dao->get_primary_path($p_id);
            $l_arr = [];
            if ($l_res->num_rows()) {
                $l_counter = 0;
                while ($l_row = $l_res->get_row()) {
                    $l_arr[$l_counter] = [
                        'id'    => $l_row['isys_catg_fc_port_list__id'],
                        'title' => $l_row['isys_catg_fc_port_list__title'],
                        'type'  => 'C__CATG__CONTROLLER_FC_PORT'
                    ];

                    if ($l_row['isys_catg_fc_port_list__id'] == $l_primary_path['isys_catg_sanpool_list__primary_path']) {
                        $l_arr[$l_counter]['primary'] = "1";
                    }
                    $l_counter++;
                }

                return new isys_export_data($l_arr);
            }
        }

        return [];
    }

    /**
     * Import method for LDEV paths.
     *
     * @param $p_value
     *
     * @return array
     */
    public function ldev_paths_import($p_value)
    {
        if (array_key_exists(C__DATA__VALUE, $p_value) && !empty($p_value[C__DATA__VALUE])) {
            $l_data = $p_value[C__DATA__VALUE];
            $l_arr = [];

            if (is_array($l_data) && count($l_data)) {
                foreach ($l_data as $l_val) {
                    if (is_array($l_val)) {
                        $l_arr[] = $this->get_reference_value_import($l_val);
                    } else {
                        break;
                    }
                }

                if (count($l_arr) > 0) {
                    $l_data = $l_arr;
                }
            }

            return $l_data;
        }

        return null;
    }

    /**
     * @param $p_id
     *
     * @return array|isys_export_data
     */
    public function ldev_connected_devices($p_id)
    {
        if ($p_id) {
            $l_dao = isys_cmdb_dao_category_g_sanpool::instance($this->m_database);
            $l_res = $l_dao->get_connected_raids($p_id, true);
            $l_arr = [];

            if ($l_res->num_rows()) {
                while ($l_row = $l_res->get_row()) {
                    $l_arr[] = [
                        'id'    => $l_row['isys_catg_raid_list__id'],
                        'title' => $l_row['isys_catg_raid_list__title'],
                        'type'  => 'C__CATG__RAID'
                    ];
                }
            }
            unset($l_res);

            $l_res = $l_dao->get_connected_devices($p_id, true);

            if ($l_res->num_rows()) {
                while ($l_row = $l_res->get_row()) {
                    $l_arr[] = [
                        'id'    => $l_row['isys_catg_stor_list__id'],
                        'title' => $l_row['isys_catg_stor_list__title'],
                        'type'  => 'C__CATG__STORAGE_DEVICE'
                    ];
                }
            }

            return new isys_export_data($l_arr);
        }

        return [];
    }

    /**
     * Import method for LDEV connected devices.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function ldev_connected_devices_import($p_value)
    {
        if (array_key_exists(C__DATA__VALUE, $p_value) && !empty($p_value[C__DATA__VALUE])) {
            $l_data = $p_value[C__DATA__VALUE];

            if (is_array($l_data) && count($l_data)) {
                foreach ($l_data as $l_key => $l_val) {
                    if (is_array($l_val)) {
                        $l_value = $this->get_reference_value_import($l_val);
                        $l_data[$l_key][C__DATA__VALUE] = $l_value;
                    } else {
                        break;
                    }
                }
            }

            return $l_data;
        }

        return null;
    }

    /**
     * @param $p_id
     *
     * @return array|isys_export_data
     */
    public function ldev_connected_clients($p_id)
    {
        $l_arr = [];

        if ($p_id) {
            $l_dao = isys_cmdb_dao_category_g_sanpool::instance($this->m_database);
            $l_res = $l_dao->get_clients($p_id, true);

            if ($l_res->num_rows()) {
                while ($l_row = $l_res->get_row()) {
                    $l_arr[] = [
                        'id'        => $l_row['isys_obj__id'],
                        'type'      => $l_row['isys_obj_type__const'],
                        'sysid'     => $l_row['isys_obj__sysid'],
                        'title'     => $l_row['isys_obj__title'],
                        'ref_id'    => $l_row["isys_catg_ldevclient_list__id"],
                        'ref_title' => $l_row['isys_catg_ldevclient_list__title'],
                        'ref_type'  => 'C__CATG__LDEV_CLIENT'
                    ];
                }
            }

            return new isys_export_data($l_arr);
        }

        return [];
    }

    /**
     * Import method for LDEV connected clients.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function ldev_connected_clients_import($p_value)
    {
        $l_dao_ldevclient = isys_cmdb_dao_category_g_ldevclient::instance($this->m_database);

        $l_data = $p_value[C__DATA__VALUE];

        if (is_array($l_data) && count($l_data)) {
            foreach ($l_data as $l_key => $l_value) {
                if (is_array($l_value)) {
                    $l_data[$l_key][C__DATA__VALUE] = $this->m_object_ids[$l_value['id']];

                    $l_found = false;

                    $category = defined_or_default('C__CATG__LDEV_CLIENT', 'C__CATG__LDEV_CLIENT');
                    if (is_array($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category])) {
                        if (array_key_exists($l_data[$l_key]['ref_id'], $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category])) {
                            $l_data[$l_key]['ref_id'] = $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category][$l_data[$l_key]['ref_id']];
                            $l_found = true;
                        }
                    }

                    if (!$l_found) {
                        $l_ldev_data = $l_dao_ldevclient->get_data(
                            null,
                            $l_data[$l_key][C__DATA__VALUE],
                            " AND isys_catg_ldevclient_list__title LIKE " . $l_dao_ldevclient->convert_sql_text($l_data[$l_key]['ref_title']),
                            null,
                            C__RECORD_STATUS__NORMAL
                        )
                            ->get_row();

                        if (!$l_ldev_data || $this->m_mode === isys_import_handler_cmdb::C__APPEND) {
                            $l_data[$l_key]['ref_id'] = $l_dao_ldevclient->create(
                                $l_data[$l_key][C__DATA__VALUE],
                                C__RECORD_STATUS__NORMAL,
                                $l_data[$l_key]['ref_title'],
                                null,
                                null,
                                null,
                                null,
                                null
                            );
                        } else {
                            $l_data[$l_key]['ref_id'] = $l_ldev_data['isys_catg_ldevclient_list__id'];
                        }
                    }
                } else {
                    break;
                }
            }

            return $l_data;
        }

        return null;
    }

    /**
     * Export helper for finding assigned devices to chassis slots.
     *
     * @param   integer $p_id
     *
     * @return  mixed  Array if no assignments found, else: isys_export_data.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function chassis_devices($p_id)
    {
        $l_return = [];

        if ($p_id > 0) {
            $l_request = isys_request::factory()
                ->set_category_data_id($p_id)
                ->set_object_id($this->m_row['isys_obj__id']);

            $l_slots = isys_cmdb_dao_category_s_chassis_slot::instance($this->m_database)
                ->callback_property_assigned_devices($l_request);

            if (is_array($l_slots)) {
                foreach ($l_slots as $l_slot) {
                    if ($l_slot['sel'] === true) {
                        $l_slot['type'] = 'C__CATS__CHASSIS_DEVICES';
                        $l_slot['title'] = $l_slot['val'];

                        unset($l_slot['sel'], $l_slot['val']);
                        $l_return[] = $l_slot;
                    }
                }
            }

            return new isys_export_data($l_return);
        }

        return $l_return;
    }

    /**
     * Import helper for finding assigned chassis devices to chassis slots.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function chassis_devices_import()
    {
        $l_devices = $this->m_property_data['assigned_devices'][C__DATA__VALUE];

        if (!is_array($l_devices) || count($l_devices) == 0) {
            return null;
        }

        $l_result = [];

        if (defined('C__CATS__CHASSIS_SLOT')) {
            foreach ($l_devices as $l_device) {
                if (isset($l_device['id']) && isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_SPECIFIC][constant('C__CATS__CHASSIS_SLOT')][$l_device['id']])) {
                    $l_result[$this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_SPECIFIC][constant('C__CATS__CHASSIS_SLOT')][$l_device['id']]] = $l_device;
                }
            }
        }

        return $l_result;
    }

    /**
     * Export helper for finding assigned chassis slots to chassis devices.
     *
     * @param   integer $p_id
     *
     * @return  mixed  Array if no assignments found, else: isys_export_data.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function chassis_slots($p_id)
    {
        $l_return = [];

        if ($p_id > 0) {
            $l_request = isys_request::factory()
                ->set_category_data_id($p_id)
                ->set_object_id($this->m_row['isys_obj__id']);

            $l_slots = isys_cmdb_dao_category_s_chassis::instance($this->m_database)
                ->callback_property_assigned_slots($l_request);

            if (is_array($l_slots)) {
                foreach ($l_slots as $l_slot) {
                    if ($l_slot['sel'] === true) {
                        $l_slot['type'] = 'C__CATS__CHASSIS_SLOT';
                        $l_slot['title'] = $l_slot['val'];

                        unset($l_slot['sel'], $l_slot['val']);
                        $l_return[] = $l_slot;
                    }
                }
            }

            return new isys_export_data($l_return);
        }

        return $l_return;
    }

    /**
     * Import helper for finding assigned chassis slots to chassis devices.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function chassis_slots_import()
    {
        $l_slots = $this->m_property_data['assigned_slots'][C__DATA__VALUE];

        if (!is_array($l_slots) || count($l_slots) == 0) {
            return null;
        }

        $l_result = [];

        $category = defined_or_default('C__CATS__CHASSIS_SLOT', 'C__CATS__CHASSIS_SLOT');
        foreach ($l_slots as $l_slot) {
            if (isset($l_slot['id']) && isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_SPECIFIC][$category][$l_slot['id']])) {
                $l_slot['id'] = $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_SPECIFIC][$category][$l_slot['id']];
                $l_result[] = $l_slot;
            }
        }

        return $l_result;
    }

    /**
     * @param integer $logPortDataId
     *
     * @return array|isys_export_data
     */
    public function logiface_ports($logPortDataId)
    {
        if (is_numeric($logPortDataId) && $logPortDataId > 0) {
            $logicalPorts = isys_cmdb_dao_category_g_network_ifacel::instance($this->m_database)
                ->get_ports_for_ifacel($logPortDataId);

            if (is_array($logicalPorts)) {
                $return = [];

                foreach ($logicalPorts as $portId => $portTitle) {
                    $return[] = [
                        'id'    => $portId,
                        'title' => $portTitle,
                        'type'  => 'C__CATG__NETWORK_PORT'
                    ];
                }

                return new isys_export_data($return);
            }
        }

        return [];
    }

    /**
     * Imports logical interface ports.
     *
     * @param   array $logPorts
     *
     * @return  array
     */
    public function logiface_ports_import($logPorts = null)
    {
        $logPorts = $logPorts[C__DATA__VALUE] ?: $this->m_property_data['ports'][C__DATA__VALUE];

        if (!is_array($logPorts) || !count($logPorts)) {
            return null;
        }

        $l_result = [];

        $subcatCategory = defined_or_default('C__CMDB__SUBCAT__NETWORK_INTERFACE_L', 'C__CMDB__SUBCAT__NETWORK_INTERFACE_L');
        $category = defined_or_default('C__CATG__NETWORK_LOG_PORT', 'C__CATG__NETWORK_LOG_PORT');
        foreach ($logPorts as $port) {
            $portCategory = null;
            // @todo  Remove in i-doit 1.12
            if (isset($port['id']) && isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$subcatCategory][$port['id']])) {
                $l_result[$this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$subcatCategory][$port['id']]] = $port;
            }

            if (isset($port['id']) && isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category][$port['id']])) {
                $l_result[$this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category][$port['id']]] = $port;
            }

            if (isset($port['type'])) {
                $portCategory = defined_or_default($port['type']);
            }

            if ($portCategory !== null && isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$portCategory][$port['id']])) {
                $l_result[$this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$portCategory][$port['id']]] = $port;
            }
        }

        return $l_result;
    }

    /**
     * @param $p_id
     *
     * @return array|isys_export_data
     */
    public function raid($p_id)
    {
        if ($p_id > 0) {
            $l_dao = isys_cmdb_dao_category_g_raid::instance($this->m_database);

            $l_res_hardware_raid = $l_dao->get_items_from_hardware_raid($p_id);

            $l_res_software_raid = $l_dao->get_items_from_software_raid($p_id);

            $l_arr = [];

            if ($l_res_hardware_raid && $l_res_hardware_raid->num_rows() > 0) {
                while ($l_row = $l_res_hardware_raid->get_row()) {
                    $l_capacity = round($l_row['isys_catg_stor_list__capacity'] / 1024 / 1024 / 1024, 2);
                    $l_arr[] = [
                        "id"       => $l_row["isys_catg_stor_list__id"],
                        "capacity" => $l_capacity,
                        "title"    => $l_row["isys_catg_stor_list__title"],
                        "type"     => "C__CATG__STORAGE_DEVICE"
                    ];
                }
            } elseif ($l_res_software_raid && $l_res_software_raid->num_rows() > 0) {
                while ($l_row = $l_res_software_raid->get_row()) {
                    $l_capacity = round($l_row['isys_catg_drive_list__capacity'] / 1024 / 1024 / 1024, 2);
                    $l_arr[] = [
                        "id"       => $l_row["isys_catg_drive_list__id"],
                        "capacity" => $l_capacity,
                        "title"    => $l_row["isys_catg_drive_list__title"],
                        "type"     => "C__CATG__DRIVE"
                    ];
                }
            }

            return new isys_export_data($l_arr);
        }

        return [];
    }

    /**
     * Import method for raids.
     *
     * @param   array $p_value
     *
     * @return  array
     */
    public function raid_import($p_value)
    {
        if (array_key_exists(C__DATA__VALUE, $p_value) && !empty($p_value[C__DATA__VALUE])) {
            $l_new_data = [];
            $l_data = $p_value[C__DATA__VALUE];

            if (is_array($l_data) && count($l_data)) {
                foreach ($l_data as $l_val) {
                    if (is_array($l_val)) {
                        $l_new_data[] = $this->get_reference_value_import($l_val);
                    } else {
                        break;
                    }
                }

                if (count($l_new_data) > 0) {
                    $l_data = $l_new_data;
                }
            }

            return $l_data;
        }

        return null;
    }

    /**
     * Returns Storages of a raid
     *
     * @param int $p_catg_raid_list_id
     *
     * @return array|null
     */
    public function raid_capacity($p_catg_raid_list_id)
    {
        if ($p_catg_raid_list_id <= 0) {
            return false;
        }
        $l_dao = isys_cmdb_dao_category_g_raid::instance($this->m_database);
        $l_res_hardware_raid = $l_dao->get_items_from_hardware_raid($p_catg_raid_list_id);
        $l_res_software_raid = $l_dao->get_items_from_software_raid($p_catg_raid_list_id);
        $l_capacity = 0;
        if ($l_res_hardware_raid->num_rows() > 0) {
            while ($l_row = $l_res_hardware_raid->get_row()) {
                $l_capacity += $l_row['isys_catg_stor_list__capacity'];
            }
        } elseif ($l_res_software_raid->num_rows() > 0) {
            while ($l_row = $l_res_software_raid->get_row()) {
                $l_capacity += $l_row['isys_catg_drive_list__capacity'];
            }
        }
        if ($l_capacity > 0) {
            return ['title' => round($l_capacity / 1024 / 1024 / 1024, 2)];
        } else {
            return null;
        }
    }

    /**
     * Import method for raid capacity.
     *
     * @return boolean
     */
    public function raid_capacity_import()
    {
        return null;
    }

    /**
     * @param $p_id
     *
     * @return isys_export_data
     * @throws isys_exception_general
     */
    public function cluster_hostaddress($p_id)
    {
        return $this->hostaddress($p_id);
    }

    /**
     * Import method for host addresses.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function cluster_hostaddress_import($p_value)
    {
        $l_cluster_s_object = $this->m_property_data['cluster_service'][C__DATA__VALUE];

        $l_data = $p_value[C__DATA__VALUE];
        $l_dao = isys_cmdb_dao_category_g_ip::instance($this->m_database);
        $l_new_arr = [];

        if (is_array($l_data) && count($l_data)) {
            foreach ($l_data as $l_key => $l_value) {
                if (is_array($l_value)) {
                    $l_id = $this->get_data_id_by_property_and_obj_id($l_cluster_s_object, 'C__CATG__IP', $l_data[$l_key][C__DATA__VALUE], 'address');

                    if ($l_id) {
                        $l_new_arr[] = $l_id;
                    } else {
                        $l_new_arr[] = $l_dao->create(
                            $l_cluster_s_object,
                            $l_data[$l_key]['hostname'],
                            null,
                            $l_data[$l_key]['title'],
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            C__RECORD_STATUS__NORMAL
                        );
                    }
                } else {
                    break;
                }
            }
        }

        if (count($l_new_arr) > 0) {
            return $l_new_arr;
        }

        return $l_data;
    }

    /**
     *
     * @return  bool|isys_export_data
     */
    public function cluster_drives()
    {
        $l_arr = [];
        $l_dao = isys_cmdb_dao_category_g_cluster_service::instance($this->m_database);
        $l_result_set = $l_dao->get_cluster_drives($this->m_row['isys_catg_cluster_service_list__id']);

        if (($l_result_set instanceof isys_component_dao_result) === false) {
            return false;
        }

        while ($l_row = $l_result_set->get_row()) {
            $l_arr[] = [
                'type'  => 'C__CATG__CLUSTER_SERVICE',
                'id'    => $l_row['isys_catg_drive_list__id'],
                'title' => $l_row['isys_catg_drive_list__title']
            ];
        }

        return new isys_export_data($l_arr);
    }

    /**
     * Import method for the cluster drives.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function cluster_drives_import($p_value)
    {
        $l_cluster_s_object = $this->m_property_data['cluster_service'][C__DATA__VALUE];

        $l_data = $p_value[C__DATA__VALUE];

        $l_dao = isys_cmdb_dao_category_g_drive::instance($this->m_database);
        $l_new_arr = [];

        if (is_array($l_data) && count($l_data)) {
            foreach ($l_data as $l_key => $l_value) {
                if (is_array($l_value)) {
                    $l_id = $this->get_data_id_by_property_and_obj_id($l_cluster_s_object, 'C__CATG__DRIVE', $l_data[$l_key][C__DATA__VALUE], 'title');

                    if ($l_id) {
                        $l_new_arr[] = $l_id;
                    } else {
                        $l_new_arr[] = $l_dao->create($l_cluster_s_object, C__RECORD_STATUS__NORMAL, null, null, $l_data[$l_key]['title']);
                    }
                } else {
                    break;
                }
            }
        }

        if (count($l_new_arr) > 0) {
            return $l_new_arr;
        }

        return null;
    }

    /**
     * @return bool|isys_export_data
     */
    public function cluster_shares()
    {
        $l_dao = isys_cmdb_dao_category_g_cluster_service::instance($this->m_database);
        $l_result_set = $l_dao->get_cluster_shares($this->m_row['isys_catg_cluster_service_list__id']);
        if (($l_result_set instanceof isys_component_dao_result) === false) {
            return false;
        }
        $l_arr = [];
        while ($l_row = $l_result_set->get_row()) {
            $l_arr[] = [
                'type'  => 'C__CATG__SHARES',
                'id'    => $l_row['isys_catg_shares_list__id'],
                'title' => $l_row['isys_catg_shares_list__title']
            ];
        }

        return new isys_export_data($l_arr);
    }

    /**
     * Import method for cluster shares.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function cluster_shares_import($p_value)
    {
        $l_cluster_s_object = $this->m_property_data['cluster_service'][C__DATA__VALUE];

        $l_data = $p_value[C__DATA__VALUE];

        $l_dao = isys_cmdb_dao_category_g_shares::instance($this->m_database);
        $l_new_arr = [];

        if (is_array($l_data) && count($l_data)) {
            foreach ($l_data as $l_key => $l_value) {
                if (is_array($l_value)) {
                    $l_id = $this->get_data_id_by_property_and_obj_id($l_cluster_s_object, 'C__CATG__SHARES', $l_data[$l_key][C__DATA__VALUE], 'title');

                    if ($l_id) {
                        $l_new_arr[] = $l_id;
                    } else {
                        $l_new_arr[] = $l_dao->create($l_cluster_s_object, $l_data[$l_key]['title'], C__RECORD_STATUS__NORMAL, null, null, null, null);
                    }
                } else {
                    break;
                }
            }
        }

        if (count($l_new_arr) > 0) {
            return $l_new_arr;
        }

        return $l_data;
    }

    /**
     * @param $p_value
     *
     * @return isys_export_data
     */
    public function cluster_runs_on($p_value)
    {
        $l_arr = [];
        $l_dao = isys_cmdb_dao_category_g_cluster_service::instance($this->m_database);

        if ($p_value > 0) {
            $l_result_set = $l_dao->get_cluster_members($p_value);
        } else {
            $l_result_set = $l_dao->get_cluster_members($this->m_row['isys_catg_cluster_service_list__id']);
        }

        while ($l_row = $l_result_set->get_row()) {
            $l_arr[] = [
                'id'    => $l_row['isys_catg_cluster_members_list__id'],
                'title' => $l_row['isys_obj__title'],
                'type'  => 'C__CATG__CLUSTER_MEMBERS'
            ];
        }

        return new isys_export_data($l_arr);
    }

    /**
     * Import method for the "cluster runs on" category.
     *
     * @param   array $p_value
     *
     * @return  array
     */
    public function cluster_runs_on_import($p_value)
    {
        if (array_key_exists(C__DATA__VALUE, $p_value) && !empty($p_value[C__DATA__VALUE])) {
            $l_data = $p_value[C__DATA__VALUE];
            $l_new_arr = [];

            if (is_array($l_data) && count($l_data)) {
                foreach ($l_data as $l_value) {
                    if (is_array($l_value)) {
                        $l_new_arr[] = $this->get_reference_value_import($l_value);
                    } else {
                        break;
                    }
                }
            }

            if (count($l_new_arr) > 0) {
                return $l_new_arr;
            }

            return $l_data;
        }

        return null;
    }

    /**
     * @param $p_id
     *
     * @return bool|isys_export_data
     * @throws isys_exception_database
     */
    public function cluster_administration_service($p_id)
    {
        $l_dao = isys_cmdb_dao_category_g_cluster::instance($this->m_database);
        $l_query = 'SELECT * FROM isys_catg_cluster_list_2_isys_obj AS main ' . 'LEFT JOIN isys_obj sec ON sec.isys_obj__id = main.isys_obj__id ' .
            'LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id ' . 'WHERE main.isys_catg_cluster_list__id = ' . $l_dao->convert_sql_id($p_id);
        $l_result_set = $l_dao->retrieve($l_query);

        return $this->export_object_relation($l_result_set);
    }

    /**
     * @param   array $p_value
     *
     * @return  array
     */
    public function cluster_administration_service_import($p_value)
    {
        return $this->get_object_id_from_member($p_value);
    }

    /**
     * @param $p_id
     *
     * @return array
     */
    public function soa_stack_object($p_id)
    {
        $l_dao = new isys_cmdb_dao($this->m_database);

        $l_row = $l_dao->get_object_by_id($p_id)
            ->get_row();

        $l_arr = [
            "id"    => $l_row["isys_obj__id"],
            "type"  => $l_row["isys_obj_type__const"],
            "sysid" => $l_row["isys_obj__sysid"],
            "title" => $l_row["isys_obj__title"],
        ];

        return $l_arr;
    }

    /**
     * Import method for SOA stack objects.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function soa_stack_object_import($p_value)
    {
        return $this->m_object_ids[$p_value['id']];
    }

    /**
     * @param $p_id
     *
     * @return isys_export_data
     * @throws isys_exception_database
     */
    public function soa_stack_components($p_id)
    {
        $l_arr = [];

        $l_dao = isys_cmdb_dao_category_g_soa_stacks::instance($this->m_database);
        $l_assigned_components = $l_dao->get_assigned_object($p_id);

        if (is_object($l_assigned_components)) {
            if ($l_assigned_components->num_rows() > 0) {
                while ($l_row = $l_assigned_components->get_row()) {
                    if (!empty($l_row['isys_obj__id'])) {
                        $l_rel_data = $l_dao->retrieve("SELECT * FROM isys_catg_relation_list WHERE isys_catg_relation_list__isys_obj__id = " .
                            $l_dao->convert_sql_id($l_row['isys_obj__id']))
                            ->get_row();

                        $l_data = $l_dao->retrieve('SELECT * FROM isys_catg_application_list
													INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
												   	INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id
													WHERE isys_catg_application_list__isys_catg_relation_list__id = ' . $l_rel_data['isys_catg_relation_list__id'])
                            ->get_row();

                        $l_arr[] = [
                            'id'    => $l_data['isys_catg_application_list__id'],
                            'type'  => 'C__CATG__APPLICATION',
                            'title' => $l_data['isys_obj__title']
                        ];
                    }
                }
            }
        }

        return new isys_export_data($l_arr);
    }

    /**
     * Import method for the SAO stack components.
     *
     * @param   array $p_value
     *
     * @return  array
     */
    public function soa_stack_components_import($p_value)
    {
        $l_arr = [];
        if (is_array($p_value[C__DATA__VALUE]) && count($p_value[C__DATA__VALUE]) > 0 && defined('C__CATG__APPLICATION')) {
            if (is_array($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__APPLICATION')])) {
                $l_dao = isys_cmdb_dao_category_g_relation::instance($this->m_database);
                foreach ($p_value[C__DATA__VALUE] as $l_val) {
                    if (array_key_exists($l_val['id'], $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__APPLICATION')])) {
                        $l_rel_data = $l_dao->retrieve('SELECT isys_catg_relation_list__isys_obj__id FROM isys_catg_application_list ' .
                            'LEFT JOIN isys_catg_relation_list ON isys_catg_application_list__isys_catg_relation_list__id = isys_catg_relation_list__id ' .
                            'WHERE isys_catg_application_list__id = ' .
                            $l_dao->convert_sql_id($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__APPLICATION')][$l_val['id']]))
                            ->get_row();
                        $l_arr[] = $l_rel_data['isys_catg_relation_list__isys_obj__id'];
                    }
                }
            }
        }

        return $l_arr;
    }

    /**
     * @return isys_export_data
     */
    public function soa_stack_it_services()
    {
        $l_arr = [];
        $l_dao = isys_cmdb_dao_category_g_soa_stacks::instance($this->m_database);
        $l_assigned_it_services = $l_dao->get_assigned_it_services($this->m_row["isys_connection__isys_obj__id"]);
        if (is_object($l_assigned_it_services)) {
            while ($l_row = $l_assigned_it_services->get_row()) {
                $l_obj_data = $l_dao->get_object_by_id($l_row['isys_obj__id'])
                    ->get_row();
                $l_arr[] = [
                    'id'    => $l_row['isys_catg_its_components_list__isys_obj__id'],
                    'type'  => $l_obj_data['isys_obj_type__const'],
                    'sysid' => $l_row['isys_obj__sysid'],
                    'title' => $l_row['isys_obj__title'],
                ];
            }
        }

        return new isys_export_data($l_arr);
    }

    /**
     * Import method for SAO stack IT services.
     *
     * @param   array $p_value
     *
     * @return  array
     */
    public function soa_stack_it_services_import($p_value)
    {
        return $this->get_object_id_from_member($p_value);
    }

    /**
     * Translate language constant.
     *
     * @param   string $p_lang_const
     *
     * @return  string
     */
    public function translate($p_lang_const)
    {
        return $this->language->get($p_lang_const);
    }

    /**
     * @param $p_value
     *
     * @return array
     */
    public function get_yes_or_no($p_value)
    {
        $l_return = [C__DATA__VALUE => $p_value];

        switch ($p_value) {
            case "1":
                $l_return['title'] = $this->translate('LC__UNIVERSAL__YES');
                break;
            case null:
            case "0":
                $l_return['title'] = $this->translate('LC__UNIVERSAL__NO');
                break;
        }

        return $l_return;
    }

    /**
     * Import method for the Yes/No dialog fields.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function get_yes_or_no_import($p_value)
    {
        if (is_array($p_value[C__DATA__VALUE]) && array_key_exists(C__DATA__VALUE, $p_value[C__DATA__VALUE])) {
            $p_value[C__DATA__VALUE] = $p_value[C__DATA__VALUE][C__DATA__VALUE];
        }

        return $p_value[C__DATA__VALUE];
    }

    /**
     * @param $p_obj_type
     *
     * @return string
     */
    public function obj_type($p_obj_type)
    {
        $l_dao = new isys_cmdb_dao($this->m_database);

        return $this->translate($l_dao->get_objtype_name_by_id_as_string($p_obj_type));
    }

    /**
     * @param $p_value
     *
     * @return isys_export_data|null
     * @throws isys_exception_database
     */
    public function get_san_zoning_members($p_value)
    {
        $l_arr = [];
        $l_dao = isys_cmdb_dao_category_s_san_zoning::instance($this->m_database);

        $l_res = $l_dao->get_san_zoning_fc_port_result(null, $p_value);

        if ($l_res && $l_res->num_rows() > 0) {
            while ($l_row = $l_res->get_row()) {
                $sql = 'SELECT *
                    FROM isys_catg_fc_port_list
                    INNER JOIN isys_obj ON isys_obj__id = isys_catg_fc_port_list__isys_obj__id
                    INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
                    WHERE isys_catg_fc_port_list__id = ' . $l_dao->convert_sql_id($l_row["isys_san_zoning_fc_port__isys_catg_fc_port_list__id"]) . ';';

                $l_data_fc_port = $l_dao->retrieve($sql)->get_row();

                if ($l_row["isys_san_zoning_fc_port__wwn_selected"] == 0) {
                    $l_arr[] = [
                        'id'         => $l_data_fc_port['isys_catg_fc_port_list__isys_obj__id'],
                        'type'       => $l_data_fc_port['isys_obj_type__const'],
                        'type_title' => $this->translate($l_data_fc_port['isys_obj_type__title']),
                        'title'      => $l_data_fc_port['isys_obj__title'],
                        'sysid'      => $l_data_fc_port['isys_obj__sysid'],

                        'ref_id'       => $l_row['isys_san_zoning_fc_port__isys_catg_fc_port_list__id'],
                        'ref_title'    => $l_data_fc_port['isys_catg_fc_port_list__title'],
                        'ref_type'     => 'C__CATG__CONTROLLER_FC_PORT',
                        'wwn_selected' => null
                    ];
                } else {
                    $l_arr[] = [
                        'id'         => $l_data_fc_port['isys_catg_fc_port_list__isys_obj__id'],
                        'type'       => $l_data_fc_port['isys_obj_type__const'],
                        'type_title' => $this->translate($l_data_fc_port['isys_obj_type__title']),
                        'title'      => $l_data_fc_port['isys_obj__title'],
                        'sysid'      => $l_data_fc_port['isys_obj__sysid'],

                        'ref_id'       => $l_row['isys_san_zoning_fc_port__isys_catg_fc_port_list__id'],
                        'ref_title'    => $l_data_fc_port['isys_catg_fc_port_list__title'],
                        'ref_type'     => 'C__CATG__CONTROLLER_FC_PORT',
                        'wwn_selected' => $l_data_fc_port['isys_catg_fc_port_list__wwpn']
                    ];
                }
            }

            return new isys_export_data($l_arr);
        } else {
            return null;
        }
    }

    /**
     * Import method for San zoning members.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function get_san_zoning_members_import($p_value)
    {
        $l_data = $p_value[C__DATA__VALUE];
        $l_dao_fc = isys_cmdb_dao_category_g_controller_fcport::instance($this->m_database);

        foreach ($l_data as $l_key => $l_value) {
            if (is_array($l_value)) {
                if (array_key_exists($l_value['id'], $this->m_object_ids)) {
                    $l_data[$l_key][C__DATA__VALUE] = $this->m_object_ids[$l_value['id']];

                    $l_catdata = $l_dao_fc->get_data(
                        null,
                        $l_data[$l_key][C__DATA__VALUE],
                        " AND isys_catg_fc_port_list__title LIKE " . $l_dao_fc->convert_sql_text($l_data[$l_key]['ref_title']),
                        null,
                        C__RECORD_STATUS__NORMAL
                    )
                        ->get_row();

                    if ($l_catdata) {
                        $l_data[$l_key]['ref_id'] = $l_catdata['isys_catg_fc_port_list__id'];
                    } else {
                        $l_data[$l_key]['ref_id'] = $l_dao_fc->create(
                            $l_data[$l_key][C__DATA__VALUE],
                            C__RECORD_STATUS__NORMAL,
                            null,
                            $l_data[$l_key]['ref_title'],
                            null,
                            null,
                            null,
                            null,
                            null,
                            $l_data[$l_key]['wwn_selected'],
                            null
                        );
                    }
                }
            } else {
                break;
            }
        }

        return $l_data;
    }

    /**
     * @param $p_obj_id
     *
     * @return isys_export_data
     */
    public function file_objects($p_obj_id)
    {
        $l_result = [];
        $l_dao = isys_cmdb_dao_category_s_file_object::instance($this->m_database);
        $l_res = $l_dao->get_data(null, $p_obj_id);
        while ($l_row = $l_res->get_row()) {
            $l_prefix = null;
            $l_const = null;

            if ($l_row['isys_catg_manual_list__id']) {
                $l_prefix = 'isys_catg_manual_list__';
                $l_const = 'C__CATG__MANUAL';
            } elseif ($l_row['isys_catg_file_list__id']) {
                $l_prefix = 'isys_catg_file_list__';
                $l_const = 'C__CATG__FILE';
            } elseif ($l_row['isys_catg_emergency_plan_list__id']) {
                $l_prefix = 'isys_catg_emergency_plan_list__';
                $l_const = 'C__CATG__EMERGENCY_PLAN';
            }

            $l_obj_id = $l_row[$l_prefix . 'isys_obj__id'];
            $l_object_info = $l_dao->get_object_by_id($l_obj_id)
                ->__to_array();
            $l_obj_type = $l_dao->get_objtype($l_dao->get_objTypeID($l_obj_id))
                ->__to_array();
            $l_result[] = [
                'id'       => $l_obj_id,
                'title'    => $l_dao->get_obj_name_by_id_as_string($l_obj_id),
                'type'     => $l_obj_type['isys_obj_type__const'],
                'sysid'    => $l_object_info['isys_obj__sysid'],
                'category' => $l_const
            ];
        }

        return new isys_export_data($l_result);
    }

    /**
     * Import method for file objects.
     *
     * @param   array $p_value
     *
     * @return  array
     */
    public function file_objects_import($p_value)
    {
        $l_data = [];

        foreach ($p_value[C__DATA__VALUE] as $l_key => $l_val) {
            if (is_array($l_val)) {
                if (array_key_exists($l_val['id'], $this->m_object_ids)) {
                    $l_data[$l_key][C__DATA__VALUE] = $this->m_object_ids[$l_val['id']];
                }
            } else {
                break;
            }
        }

        return $l_data;
    }

    /**
     * base64 object image export.
     *
     * @param   string $p_file_name
     *
     * @return  array
     */
    public function file_version($p_file_name)
    {
        $l_return = [];

        if ($p_file_name) {
            $l_filename = isys_application::instance()->getUploadFilePath($p_file_name);

            if (file_exists($l_filename)) {
                $l_file = base64_encode(file_get_contents($l_filename));

                $l_return = [
                    "file_name"    => $p_file_name,
                    "title"        => "<![CDATA[" . $l_file  . "]]>"
                ];
            }

            return $l_return;
        }

        return $l_return;
    }

    /**
     * Writes base64 encoded image decoded to file system.
     *
     * @param $p_value
     *
     * @return mixed|string|null
     * @throws isys_exception_filesystem
     */
    public function file_version_import($p_value)
    {
        if (!$p_value['file_name'] && !$this->m_property_data['file_physical'][C__DATA__VALUE]) {
            return $p_value[C__DATA__VALUE];
        }

        $fileName = $this->m_property_data['file_physical'][C__DATA__VALUE] ?: $p_value['file_name'];
        $filePath = isys_application::instance()->getUploadFilePath($fileName);

        if (!file_exists($filePath) && isset($this->m_object_ids[isys_import_handler_cmdb::get_stored_objectID()])) {
            $fileName = isys_component_filemanager::create_new_filename(
                $fileName,
                $this->m_object_ids[isys_import_handler_cmdb::get_stored_objectID()]
            );

            $filePath = isys_application::instance()->getOrCreateUploadFileDir($fileName);
            $l_content = base64_decode($p_value[C__DATA__VALUE], true);

            if (!isys_file_put_contents($filePath . $fileName, $l_content)) {
                return null;
            }
        }

        return $fileName;
    }

    /**
     *
     * @param   mixed $p_const
     *
     * @return  array
     */
    public function get_connector_assigned_category($p_const)
    {
        if ($p_const === null) {
            return null;
        }

        $l_dao = new isys_cmdb_dao($this->m_database);
        $l_const = (!is_numeric($p_const) && defined($p_const)) ? constant($p_const) : $p_const;
        $l_data = $l_dao->get_all_catg($l_const)
            ->get_row();

        return [
            "value" => $l_data["isysgui_catg__id"],
            "const" => $l_data["isysgui_catg__const"],
            "title" => $this->translate($l_data["isysgui_catg__title"])
        ];
    }

    /**
     * Import method for assigned categories.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function get_connector_assigned_category_import($p_value)
    {
        if (array_key_exists(C__DATA__VALUE, $p_value)) {
            if (is_array($p_value[C__DATA__VALUE])) {
                $p_value = $p_value[C__DATA__VALUE];
            }
        }
        if (isset($p_value['const'])) {
            return (!is_numeric($p_value['const']) && defined($p_value['const'])) ? constant($p_value['const']) : null;
        }

        return null;
    }

    /**
     * @param $p_id
     *
     * @return array|null
     * @throws isys_exception_database
     */
    public function cluster_service_connection($p_id)
    {
        $l_result = null;
        if (!isset($p_id)) {
            return $l_result;
        }
        $l_dao = new isys_cmdb_dao_connection($this->m_database);
        $l_query = "SELECT * FROM isys_catg_cluster_members_list " .
            "INNER JOIN isys_connection ON isys_connection__id = isys_catg_cluster_members_list__isys_connection__id " .
            "INNER JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id " . "WHERE isys_catg_cluster_members_list__id = " . $p_id . ";";
        $l_res = $l_dao->retrieve($l_query);
        if ($l_res->num_rows() > 0) {
            $l_row = $l_res->get_row();
            $l_result = [
                'title' => $l_row['isys_obj__title'],
                'id'    => $l_row['isys_catg_cluster_members_list__id'],
                'type'  => 'C__CATG__CLUSTER_MEMBERS',
            ];
        }

        return $l_result;
    }

    /**
     * Import method for cluster service connections.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function cluster_service_connection_import($p_value)
    {
        return $this->get_reference_value_import($p_value);
    }

    /**
     * Gets id, title and type from referenced categorie
     *
     * @param int $p_id
     *
     * @return array
     */
    public function get_reference_value($p_id)
    {
        if (is_array($this->m_data_info[C__PROPERTY__DATA__REFERENCES]) && count($this->m_data_info[C__PROPERTY__DATA__REFERENCES]) > 0 && $p_id > 0) {
            $l_dao = new isys_cmdb_dao($this->m_database);
            $l_table = $this->m_data_info[C__PROPERTY__DATA__REFERENCES][0];

            if (!strpos($l_table, '_list') && !strpos($l_table, '_2_')) {
                $l_table .= '_list';
            }

            if ($l_table == 'isys_catg_ip_list') {
                $l_query = "SELECT " . $this->m_data_info[C__PROPERTY__DATA__REFERENCES][1] . " AS id, " . "isys_cats_net_ip_addresses_list__title AS title FROM " . $l_table .
                    " " . "INNER JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = " . $l_table . "__isys_cats_net_ip_addresses_list__id " .
                    "WHERE " . $this->m_data_info[C__PROPERTY__DATA__REFERENCES][1] . " = " . $l_dao->convert_sql_id($p_id);
            } else {
                $l_query = "SELECT " . $this->m_data_info[C__PROPERTY__DATA__REFERENCES][1] . " AS id, " . $l_table . "__title AS title FROM " . $l_table . " WHERE " .
                    $this->m_data_info[C__PROPERTY__DATA__REFERENCES][1] . " = " . $l_dao->convert_sql_id($p_id);
            }

            $l_data = $l_dao->retrieve($l_query)
                ->get_row();

            if ($l_data) {
                $l_row = $l_dao->retrieve("SELECT isysgui_catg__const FROM isysgui_catg WHERE " . "isysgui_catg__class_name NOT LIKE '%_view%' " .
                    "AND isysgui_catg__source_table = " . $l_dao->convert_sql_text(((strpos($this->m_data_info[C__PROPERTY__DATA__REFERENCES][0], '_list') &&
                        !strpos($this->m_data_info[C__PROPERTY__DATA__REFERENCES][0], '_2_')) ? str_replace(
                            '_list',
                            '',
                            $this->m_data_info[C__PROPERTY__DATA__REFERENCES][0]
                        ) : $this->m_data_info[C__PROPERTY__DATA__REFERENCES][0])))
                    ->get_row();

                $l_data['type'] = isset($l_row['isysgui_catg__const']) ? $l_row['isysgui_catg__const'] : '';

                $l_data['reference'] = $this->m_data_info[C__PROPERTY__DATA__REFERENCES][0];

                return $l_data;
            }
        }

        return [];
    }

    /**
     * Import method for referenced values.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function get_reference_value_import($p_value)
    {
        $l_dao = new isys_cmdb_dao($this->m_database);

        if (is_array($p_value[C__DATA__VALUE])) {
            while (array_key_exists(C__DATA__VALUE, $p_value) && is_array($p_value[C__DATA__VALUE])) {
                $p_value = $p_value[C__DATA__VALUE];
            }
        }

        $l_const = $p_value['type'];
        $l_catg = strpos($p_value['type'], 'C__CATG');

        if ($l_catg !== false) {
            if ($l_catg === 0) {
                $l_cat_type = C__CMDB__CATEGORY__TYPE_GLOBAL;
            } else {
                $l_cat_type = C__CMDB__CATEGORY__TYPE_SPECIFIC;
            }
        } else {
            $l_count = $l_dao->retrieve("SELECT count(isysgui_catg__id) AS `count` FROM isysgui_catg WHERE isysgui_catg__const = " . $l_dao->convert_sql_text($l_const))
                ->__to_array();

            if ($l_count['count'] > 0) {
                $l_cat_type = C__CMDB__CATEGORY__TYPE_GLOBAL;
            } else {
                $l_cat_type = C__CMDB__CATEGORY__TYPE_SPECIFIC;
            }
        }

        if (defined($l_const)) {
            return $this->m_category_data_ids[$l_cat_type][constant($l_const)][$p_value['id']];
        }

        return null;
    }

    /**
     * Exports relation data by connection
     *
     * @param int $p_con_id Connection identifier
     *
     * @return array
     */
    public function relation_connection($p_con_id)
    {
        $l_dao_con = new isys_cmdb_dao_connection($this->m_database);
        $l_con_row = $l_dao_con->get_connection($p_con_id)
            ->get_row();
        $l_query = "SELECT * FROM isys_catg_relation_list WHERE isys_catg_relation_list__isys_obj__id = " .
            $l_dao_con->convert_sql_id($l_con_row['isys_connection__isys_obj__id']);
        $l_rel_info = $l_dao_con->retrieve($l_query)
            ->get_row();
        $l_arr_master = $l_dao_con->get_object_by_id($l_rel_info['isys_catg_relation_list__isys_obj__id__master'])
            ->get_row();
        $l_arr_slave = $l_dao_con->get_object_by_id($l_rel_info['isys_catg_relation_list__isys_obj__id__slave'])
            ->get_row();
        $l_arr[0] = [
            'id'    => $l_arr_master['isys_obj__id'],
            'title' => $l_arr_master['isys_obj__title'],
            'sysid' => $l_arr_master['isys_obj__sysid'],
            'type'  => $l_arr_master['isys_obj_type__const'],
        ];
        $l_arr[1] = [
            'id'    => $l_arr_slave['isys_obj__id'],
            'title' => $l_arr_slave['isys_obj__title'],
            'sysid' => $l_arr_slave['isys_obj__sysid'],
            'type'  => $l_arr_slave['isys_obj_type__const']
        ];

        return new isys_export_data($l_arr);
    }

    /**
     * Import method for relation connections.
     *
     * @param   array $p_value
     *
     * @return  array
     */
    public function relation_connection_import($p_value)
    {
        return $this->get_object_id_from_member($p_value);
    }

    /**
     * Get relation data by object id.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     */
    public function relation_object($p_obj_id)
    {
        $l_dao = new isys_cmdb_dao($this->m_database);

        $l_rel_info = $l_dao->retrieve("SELECT * FROM isys_catg_relation_list WHERE isys_catg_relation_list__isys_obj__id = " . $l_dao->convert_sql_id($p_obj_id))
            ->get_row();

        $l_arr_master = $l_dao->get_object_by_id($l_rel_info['isys_catg_relation_list__isys_obj__id__master'])
            ->get_row();
        $l_arr_slave = $l_dao->get_object_by_id($l_rel_info['isys_catg_relation_list__isys_obj__id__slave'])
            ->get_row();

        $l_arr[0] = [
            'id'    => $l_arr_master['isys_obj__id'],
            'title' => $l_arr_master['isys_obj__title'],
            'sysid' => $l_arr_master['isys_obj__sysid'],
            'type'  => $l_arr_master['isys_obj_type__const'],
        ];

        $l_arr[1] = [
            'id'    => $l_arr_slave['isys_obj__id'],
            'title' => $l_arr_slave['isys_obj__title'],
            'sysid' => $l_arr_slave['isys_obj__sysid'],
            'type'  => $l_arr_slave['isys_obj_type__const']
        ];

        return new isys_export_data($l_arr);
    }

    /**
     * Import method for relation objects.
     *
     * @param   int $p_value
     *
     * @return  array
     */
    public function relation_object_import($p_value)
    {
        return $this->relation_connection($p_value);
    }

    /**
     * Important for categories which are handled by the connector.
     * Imports the master object of the connection
     *
     * @param $p_relation_id
     *
     * @return array|null
     */
    public function relation_direction($p_relation_id)
    {
        if (empty($p_relation_id)) {
            return null;
        }

        $l_dao = new isys_cmdb_dao_category_g_relation($this->m_database);

        $l_query = 'SELECT * FROM isys_catg_relation_list
			INNER JOIN isys_relation_type ON isys_relation_type__id = isys_catg_relation_list__isys_relation_type__id
			WHERE isys_catg_relation_list__id = ' . $l_dao->convert_sql_id($p_relation_id);

        $l_data = $l_dao->retrieve($l_query)
            ->get_row();

        $l_arr_master = $l_dao->get_object_by_id($l_data['isys_catg_relation_list__isys_obj__id__master'])
            ->get_row();

        return [
            'id'    => $l_arr_master['isys_obj__id'],
            'title' => $l_arr_master['isys_obj__title'],
            'sysid' => $l_arr_master['isys_obj__sysid'],
            'type'  => $l_arr_master['isys_obj_type__const'],
        ];
    }

    /**
     * Returns the real Object ID of the master object
     *
     * @param $p_value
     *
     * @return int|null
     */
    public function relation_direction_import($p_value)
    {
        if (isset($p_value[C__DATA__VALUE]) && $p_value['id'] > 0) {
            if (array_key_exists($p_value['id'], $this->m_object_ids)) {
                return $this->m_object_ids[$p_value['id']];
            }
        }

        return null;
    }

    /**
     * @param $p_id
     *
     * @return array
     */
    public function get_vm_host($p_id)
    {
        $l_dao = isys_cmdb_dao_category_g_virtual_machine::instance($this->m_database);
        $l_data = $l_dao->get_data(null, $p_id)
            ->get_row();

        return [
            'id'    => $l_data['isys_catg_virtual_machine_list__id'],
            'title' => '',
            'type'  => 'C__CATG__VIRTUAL_MACHINE'
        ];
    }

    /**
     * Import method for VM hosts.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function get_vm_host_import($p_value)
    {
        return isset($p_value[C__DATA__VALUE]) ? $p_value[C__DATA__VALUE] : null;
    }

    /**
     * @param $p_value
     *
     * @return array
     */
    public function virtual_device_port_group($p_value)
    {
        if (empty($p_value)) {
            return [];
        }

        $l_vs_id = $l_title = $l_vlan_id = '';

        $l_dao_vm = isys_cmdb_dao_category_g_virtual_machine::instance($this->m_database);
        $l_dao_vs = isys_cmdb_dao_category_g_virtual_switch::instance($this->m_database);

        $l_data_vm = $l_dao_vm->get_data(null, $this->m_row['isys_obj__id'])
            ->get_row();

        if (defined('C__OBJTYPE__CLUSTER') && $l_dao_vm->get_objTypeID($l_data_vm['isys_connection__isys_obj__id']) == C__OBJTYPE__CLUSTER) {
            $l_obj_id = $l_data_vm['isys_catg_virtual_machine_list__primary'];
        } else {
            $l_obj_id = $l_data_vm['isys_connection__isys_obj__id'];
        }

        $l_res = $l_dao_vs->get_data(null, $l_obj_id);

        $l_object = $l_dao_vs->get_object_by_id($l_obj_id)
            ->get_row();

        while ($l_row = $l_res->get_row()) {
            $l_res_pg = $l_dao_vs->get_port_groups($l_row['isys_catg_virtual_switch_list__id']);

            $l_vs_id = $l_row['isys_catg_virtual_switch_list__id'];
            $l_title = $l_row['isys_catg_virtual_switch_list__title'];

            while ($l_row_pg = $l_res_pg->get_row()) {
                if ($l_row_pg['isys_virtual_port_group__title'] == $p_value) {
                    $l_vlan_id = $l_row_pg['isys_virtual_port_group__vlanid'];
                    break;
                }
            }
        }

        $l_arr = [
            'id'                   => $l_obj_id,
            'title'                => $l_object['isys_obj__title'],
            'sysid'                => $l_object['isys_obj__sysid'],
            'type'                 => $l_object['isys_obj_type__const'],
            'ref_id'               => $l_vs_id,
            'ref_title'            => $l_title,
            'ref_type'             => 'C__CATG__VIRTUAL_SWITCH',
            'vs_port_group_title'  => $p_value,
            'vs_port_group_vlanid' => $l_vlan_id
        ];

        return $l_arr;
    }

    /**
     * Import method for virtual device port groups.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function virtual_device_port_group_import($p_value)
    {
        if (!is_array($p_value[C__DATA__VALUE])) {
            if (array_key_exists($p_value['id'], $this->m_object_ids)) {
                $l_obj_id = $this->m_object_ids[$p_value['id']];
                $l_dao_vs = isys_cmdb_dao_category_g_virtual_switch::instance($this->m_database);
                $l_vs_data = $l_dao_vs->retrieve("SELECT isys_catg_virtual_switch_list__id FROM isys_catg_virtual_switch_list " .
                    "WHERE isys_catg_virtual_switch_list__title LIKE " . $l_dao_vs->convert_sql_text($p_value['ref_title']) . " " .
                    "AND isys_catg_virtual_switch_list__isys_obj__id = " . $l_dao_vs->convert_sql_id($l_obj_id))
                    ->get_row();

                if (!$l_vs_data) {
                    $l_last_id = $l_dao_vs->create($l_obj_id, C__RECORD_STATUS__NORMAL, $p_value['ref_title'], null);
                    $l_dao_vs->attach_port_groups($l_last_id, [
                        [
                            $p_value['vs_port_group_title'],
                            $p_value['vs_port_group_vlanid']
                        ]
                    ]);
                } else {
                    $l_res = $l_dao_vs->get_port_groups($l_vs_data['isys_catg_virtual_switch_list__id']);
                    $l_found = false;
                    while ($l_row = $l_res->get_row()) {
                        if ($l_row['isys_virtual_port_group__title'] == $p_value['vs_port_group_title']) {
                            $l_found = true;
                            break;
                        }
                    }

                    if (!$l_found) {
                        $l_dao_vs->attach_port_groups($l_vs_data['isys_catg_virtual_switch_list__id'], [
                            [
                                $p_value['vs_port_group_title'],
                                $p_value['vs_port_group_vlanid']
                            ]
                        ]);
                    }
                }

                return $p_value;
            }
        } else {
            return $p_value[C__DATA__VALUE];
        }

        return null;
    }

    /**
     * @param $p_id
     *
     * @return array
     * @throws isys_exception_database
     */
    public function physical_file($p_id)
    {
        $l_return = [];
        $l_dao = new isys_cmdb_dao($this->m_database);

        $l_sql = "SELECT * FROM isys_file_physical
		LEFT JOIN isys_file_version ON isys_file_version__isys_file_physical__id = isys_file_physical__id
		WHERE isys_file_physical__id = " . $l_dao->convert_sql_id($p_id);

        $l_data = $l_dao->retrieve($l_sql)
            ->get_row();

        $l_filename = $l_data['isys_file_physical__filename'] ? isys_application::instance()->getUploadFilePath($l_data['isys_file_physical__filename']) : '';

        if ($l_filename && file_exists($l_filename)) {
            $l_file = base64_encode(file_get_contents($l_filename));

            $l_return = [
                "file_name" => $l_data['isys_file_physical__filename'],
                "title"     => "<![CDATA[" . $l_file . "]]>"
            ];
        }

        return $l_return;
    }

    /**
     * Import method for physical files.
     *
     * @global  array $g_dirs
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function physical_file_import($p_value)
    {
        global $g_dirs;

        $l_file = fopen($g_dirs['fileman']['target_dir'] . DIRECTORY_SEPARATOR . 'copy_' . $p_value['file_name'], 'w');

        if ($l_file === false) {
            return null;
        }

        $l_content = base64_decode($p_value[C__DATA__VALUE], true);

        if ($l_content === false) {
            return null;
        }

        if (fwrite($l_file, $l_content) === false) {
            return null;
        }

        if (fclose($l_file) === false) {
            return null;
        }

        return 'copy_' . $p_value['file_name'];
    }

    /**
     * Creates export array for a dialog multiselect field
     *
     * @param int $p_id
     *
     * @return array
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    public function dialog_multiselect($p_id)
    {
        $l_return = [];
        $l_reference_info = $this->m_data_info[C__PROPERTY__DATA__REFERENCES];

        $l_ui_info = $this->m_ui_info;

        if (is_countable($l_reference_info) && count($l_reference_info) > 0) {
            $l_table = $l_reference_info[0];
            $l_columns = explode('_2_', $l_table);

            if ($l_columns[1] == 'isys_obj') {
                // switch fields fixes ID-3173
                $l_puffer = $l_columns[1];
                $l_columns[1] = $l_columns[0];
                $l_columns[0] = $l_puffer;
            } //

            $l_dao = new isys_cmdb_dao($this->m_database);

            $l_sql = 'SELECT * FROM ' . $l_table . ' AS main ' . 'INNER JOIN ' . $l_columns[1] . ' AS ref ON main.' . $l_columns[1] . '__id = ref.' . $l_columns[1] . '__id ' .
                'WHERE ' . $l_columns[0] . '__id = ' . $l_dao->convert_sql_id($p_id);

            $l_res = $l_dao->retrieve($l_sql);

            if ($l_res && $l_res->num_rows() > 0) {
                while ($l_row = $l_res->get_row()) {
                    $l_return[] = [
                        'id'    => $l_row[$l_columns[1] . '__id'],
                        'title' => $l_row[$l_columns[1] . '__title'],
                    ];
                }

                return new isys_export_data($l_return);
            }
        } elseif (isset($l_ui_info[C__PROPERTY__UI__PARAMS]['p_arData']) && is_a($l_ui_info[C__PROPERTY__UI__PARAMS]['p_arData'], 'isys_callback')) {
            $l_request = isys_request::factory()
                ->set_category_data_id($p_id)
                ->set_object_id($this->m_row['isys_obj__id']);

            if (isset($l_ui_info['params']['p_arData']) && method_exists($l_ui_info['params']['p_arData'], 'execute')) {
                $l_data = $l_ui_info['params']['p_arData']->execute($l_request);
            } else {
                $l_data = null;
            }

            if (is_string($l_data) && isys_format_json::is_json_array($l_data)) {
                $l_data = isys_format_json::decode($l_data);
            }
            if (is_array($l_data)) {
                $l_arr = [];
                foreach ($l_data as $l_item) {
                    if ($l_item['sel']) {
                        $l_arr[] = [
                            'id'    => $l_item['id'],
                            'title' => $l_item['val']
                        ];
                    }
                }

                return new isys_export_data($l_arr);
            }
        } else {
            throw new isys_exception_general('No reference info for dialog multiselect assigned. Modify the properties of the category.');
        }

        return null;
    }

    /**
     * Imports dialog multiselect information.
     *
     * @param  array $p_value
     *
     * @return array
     */
    public function dialog_multiselect_import($p_value)
    {
        // Preperation...
        $result = [];
        $dialogTableName = null;

        /**
         * Try to determine dialog source table
         */
        if (!empty($this->m_data_info[C__PROPERTY__DATA__SOURCE_TABLE])) {
            $dialogTableName = $this->m_data_info[C__PROPERTY__DATA__SOURCE_TABLE];
        } elseif (is_array($this->m_data_info[C__PROPERTY__DATA__REFERENCES]) && count($this->m_data_info[C__PROPERTY__DATA__REFERENCES]) == 2) {
            $tables = explode('_2_', $this->m_data_info[C__PROPERTY__DATA__REFERENCES][0]);
            $dialogTableName = $tables[1];

            if ($dialogTableName === 'isys_obj') {
                $dialogTableName = $tables[0];
            }
        }

        // Check whether prerequirements are met
        if (empty($dialogTableName) || empty($p_value[C__DATA__VALUE]) || !is_array($p_value[C__DATA__VALUE])) {
            return $p_value[C__DATA__VALUE];
        }

        // Prepare job
        $data = $p_value[C__DATA__VALUE];
        $dao = new isys_cmdb_dao($this->m_database);

        // Iterate over selected data
        foreach ($data as $value) {
            // Check whether value is numeric
            if (isset($value[0]) && is_numeric($value[0])) {
                // Save it to the results and continue

                /**
                 * @todo Probablly a bug -
                 *       why do we save the whole value instead of $value[0]?
                 *
                 *       Needs to be checked - unable to provoke situation
                 *       that uses this branch
                 */
                $result[] = $value;

                continue;
            }

            // Check whether title is set
            if (!isset($value['title'])) {
                continue;
            }

            // Prepare sql statement
            $sql = 'SELECT ' . $dialogTableName . '__id FROM ' . $dialogTableName . ' WHERE ' . $dialogTableName . '__title LIKE ' . $dao->convert_sql_text($value['title']);

            $resource = $dao->retrieve($sql);

            // Check whether dialog entry already exists
            if ($resource && $resource->num_rows() > 0) {
                // Get row and write result entrey
                $row = $resource->get_row();
                $result[] = $row[$dialogTableName . '__id'];
            } else {
                // Prepare sql for inserting a new element
                $insertSql = 'INSERT INTO ' . $dialogTableName . ' SET ' .
                    $dialogTableName . '__title   = ' . $dao->convert_sql_text($value['title']) . ', ' .
                    $dialogTableName . '__status  = ' . $dao->convert_sql_int(C__RECORD_STATUS__NORMAL);

                // Persist and store newly created id in results
                if ($dao->update($insertSql) && $dao->apply_update()) {
                    $result[] = $dao->get_last_insert_id();
                }
            }
        }

        return $result;
    }

    /**
     * @param  string $p_value
     *
     * @return string
     */
    public function date($p_value)
    {
        return isys_application::instance()->container->get('locales')->fmt_date($p_value);
    }

    /**
     * Import method for date.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function date_import($p_value)
    {
        if (isset($p_value[C__DATA__VALUE]) && ($l_date = strtotime($p_value[C__DATA__VALUE]))) {
            return date('Y-m-d', $l_date);
        }

        return '';
    }

    /**
     * Export helper for method datetime.
     *
     * @param  string $p_value
     *
     * @return string
     */
    public function datetime($p_value)
    {
        return isys_application::instance()->container->get('locales')->fmt_datetime($p_value);
    }

    /**
     * Import helper for method datetime.
     *
     * @param  array $p_value
     *
     * @return string
     */
    public function datetime_import($p_value)
    {
        if (isset($p_value[C__DATA__VALUE]) && ($l_date = strtotime($p_value[C__DATA__VALUE]))) {
            return date('Y-m-d H:i:s', $l_date);
        }

        return '';
    }

    /**
     * @param $p_value
     *
     * @return mixed
     */
    public function timeperiod($p_value)
    {
        return $p_value;
    }

    /**
     * Import method for timeperiods.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function timeperiod_import($p_value)
    {
        return $p_value[C__DATA__VALUE];
    }

    /**
     * @param $p_value
     *
     * @return isys_export_data
     */
    public function ip_helper_addresses($p_value)
    {
        $l_dao = isys_cmdb_dao_category_s_layer2_net::instance(isys_application::instance()->database);
        $l_res = $l_dao->get_iphelper_adress($p_value);
        $l_finalArr = [];

        if (is_array($l_res)) {
            foreach ($l_res as $l_value) {
                $l_dialog = $this->dialog_plus($l_value['isys_cats_layer2_net_2_iphelper__isys_layer2_net_iphelper_type'], 'isys_layer2_iphelper_type');
                $l_finalArr[] = [
                    'id'         => $l_value['isys_cats_layer2_net_2_iphelper__id'],
                    'title'      => "",
                    'type_title' => $l_dialog['title_lang'],
                    'ip'         => $l_value['isys_cats_layer2_net_2_iphelper__ip'],
                ];
            }
        }

        return new isys_export_data($l_finalArr);
    }

    /**
     * @param $p_value
     *
     * @return isys_export_data
     * @throws isys_exception_general
     */
    public function layer_3_assignment($p_value)
    {
        $l_dao = isys_cmdb_dao_category_s_layer2_net::instance(isys_application::instance()->database);
        $l_res = $l_dao->get_layer3_assignments_as_array($p_value);
        $l_finalArr = [];

        if (is_array($l_res)) {
            foreach ($l_res as $l_value) {
                $l_tmpRes = $l_dao->get_object_by_id($l_value);

                if ($l_tmpRes->num_rows()) {
                    $l_value = $l_tmpRes->get_row();
                    $l_finalArr[] = [
                        'id'         => $l_value['isys_obj__id'],
                        'title'      => $l_value['isys_obj__title'],
                        'type_title' => $l_value['isys_obj__title']
                    ];
                }
            }
        }

        return new isys_export_data($l_finalArr);
    }

    /**
     * Import method for IP helper addresses.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function ip_helper_addresses_import($p_value)
    {
        return $p_value[C__DATA__VALUE];
    }

    /**
     * @param $p_value
     *
     * @return isys_export_data|null
     */
    public function log_port($p_value)
    {
        $l_arr = [];
        $l_dao = isys_cmdb_dao_category_g_network_ifacel::instance($this->m_database);

        $l_res = $l_dao->get_attached_layer_2_net($p_value, null, false, true);
        if ($l_res->num_rows() > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_object = $l_dao->get_object_by_id($l_row['isys_obj__id'])
                    ->get_row();
                $l_objtype = $l_dao->get_objtype($l_dao->get_objTypeID($l_row['isys_obj__id']))
                    ->get_row();

                $l_arr[] = [
                    'id'    => $l_object['isys_obj__id'],
                    'title' => $l_object['isys_obj__title'],
                    'sysid' => $l_object['isys_obj__sysid'],
                    'type'  => $l_objtype['isys_obj_type__const'],
                ];
            }

            return new isys_export_data($l_arr);
        } else {
            return null;
        }
    }

    /**
     * @param $p_value
     *
     * @return array|null
     */
    public function log_port_import($p_value)
    {
        $l_data = $p_value[C__DATA__VALUE];

        if (is_array($l_data)) {
            $l_arr = [];
            if (count($l_data) > 0) {
                if (isset($l_data['id'])) {
                    // One entry
                    if (isset($this->m_object_ids[$l_data['id']])) {
                        $l_arr[] = $this->m_object_ids[$l_data['id']];
                    }
                } else {
                    foreach ($l_data as $l_obj_layer) {
                        if (isset($l_obj_layer['id'])) {
                            if (isset($this->m_object_ids[$l_obj_layer['id']])) {
                                $l_arr[] = $this->m_object_ids[$l_obj_layer['id']];
                            }
                        } elseif (!is_array($l_obj_layer) && is_numeric($l_obj_layer)) {
                            if (isset($this->m_object_ids[$l_obj_layer])) {
                                $l_arr[] = $this->m_object_ids[$l_obj_layer];
                            }
                        }
                    }
                }

                return $l_arr;
            }
        }

        return null;
    }

    /**
     * @param $logPortDataId
     *
     * @return isys_export_data
     */
    public function log_port_assigned_ips($logPortDataId)
    {
        $return = [];
        $logPortDao = isys_cmdb_dao_category_g_network_ifacel::instance($this->m_database);

        $logPortCategoryRow = $logPortDao->get_data($logPortDataId)
            ->get_row();

        $ipResult = $logPortDao->get_ips_by_obj_id($logPortCategoryRow['isys_catg_log_port_list__isys_obj__id'], false, $logPortDataId);

        while ($ipRow = $ipResult->get_row()) {
            $return[] = [
                'id'       => $ipRow['isys_catg_ip_list__id'],
                'title'    => $ipRow['isys_cats_net_ip_addresses_list__title'],
                'hostname' => $ipRow['isys_catg_ip_list__hostname'],
                'obj_id'   => $ipRow['isys_catg_log_port_list__isys_obj__id'],
                'type'     => 'C__CATG__IP'
            ];
        }

        return new isys_export_data($return);
    }

    /**
     * Get the corresponding object by isys_catg_ip_list__isys_ip_assignment__id
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     *
     * @param int $p_value isys_catg_ip_list__isys_ip_assignment__id
     *
     * @return array
     */
    public function layer3_net_ip($p_value)
    {
        $l_dao = isys_cmdb_dao_category_g_ip::instance($this->m_database);
        $l_res = $l_dao->get_data(
            null,
            $this->m_row['isys_catg_ip_list__isys_obj__id'],
            " AND (isys_catg_ip_list__isys_ip_assignment__id = " . $l_dao->convert_sql_id($p_value) . ")"
        );

        if ($l_res->num_rows()) {
            $l_row = $l_res->get_row();

            return [
                'id'       => $l_row["isys_obj__id"],
                'title'    => $l_row["isys_obj__title"],
                'hostname' => $l_row["isys_catg_ip_list__hostname"],
                'sysid'    => $l_row["isys_obj__sysid"],
                'type'     => $this->translate($l_row['isys_obj_type__title']),
            ];
        }

        return [];
    }

    /**
     * Alibi
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     *
     * @param int $p_value
     *
     * @return int
     */
    public function layer3_net_ip_import($p_value)
    {
        return $p_value;
    }

    /**
     * Import method for the logical ports assigned IP's.
     *
     * @param   array $value
     *
     * @return  mixed
     */
    public function log_port_assigned_ips_import($value)
    {
        if (is_array($value[C__DATA__VALUE])) {
            $return = [];

            foreach ($value[C__DATA__VALUE] as $data) {
                // @todo  Remove in i-doit 1.12
                if (defined('C__CMDB__SUBCAT__NETWORK_INTERFACE_L') && isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CMDB__SUBCAT__NETWORK_INTERFACE_L')][$data['id']])) {
                    $return[] = $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CMDB__SUBCAT__NETWORK_INTERFACE_L')][$data['id']];
                }

                if (defined('C__CATG__IP') && isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__IP')][$data['id']])) {
                    $return[] = $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__IP')][$data['id']];
                }
            }

            if (count($return) > 0) {
                return $return;
            }
        }

        return null;
    }

    /**
     * @param integer $logPortDataId
     *
     * @return array
     */
    public function logicalPortAssignedConnection($logPortDataId)
    {
        if (is_numeric($logPortDataId) && $logPortDataId > 0) {
            $logPortDao = isys_cmdb_dao_category_g_network_ifacel::instance($this->m_database);

            $LogPortCategoryRow = $logPortDao->get_data($logPortDataId)
                ->get_row();

            return [
                // Use the same keys as the "object" method (for compability).
                'id'           => $LogPortCategoryRow['isys_obj__id'],
                'title'        => $LogPortCategoryRow['isys_obj__title'],
                'sysid'        => $LogPortCategoryRow['isys_obj__sysid'],
                'type'         => $LogPortCategoryRow['isys_obj_type__const'],
                'type_title'   => $this->translate($LogPortCategoryRow['isys_obj_type__title']),
                // Use these keys for the "*_import" method.
                'logPortID'    => $LogPortCategoryRow['isys_catg_log_port_list__id'],
                'logPortTitle' => $LogPortCategoryRow['isys_catg_log_port_list__title']
            ];
        }

        return [];
    }

    /**
     * @param array $value
     *
     * @return mixed
     */
    public function logicalPortAssignedConnection_import($value)
    {
        if (isset($value[C__DATA__VALUE]['logPortID'])) {
            return $value[C__DATA__VALUE]['logPortID'];
        }

        return null;
    }

    /**
     * @param $p_value
     *
     * @return isys_export_data
     * @throws isys_exception_database
     */
    public function port_assigned_layer2_nets($p_value)
    {
        $l_return = [];
        $l_dao = new isys_cmdb_dao($this->m_database);

        if (!empty($p_value) && $p_value > 0) {
            $l_sql = 'SELECT isys_obj.*, isys_obj_type__const FROM isys_cats_layer2_net_assigned_ports_list ' .
                'INNER JOIN isys_obj ON isys_obj__id = isys_cats_layer2_net_assigned_ports_list__isys_obj__id ' .
                'INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id ' . 'WHERE isys_catg_port_list__id = ' . $l_dao->convert_sql_id($p_value);

            $l_res = $l_dao->retrieve($l_sql);

            while ($l_row = $l_res->get_row()) {
                $l_return[] = [
                    'id'    => $l_row['isys_obj__id'],
                    'sysid' => $l_row['isys_obj__sysid'],
                    'type'  => $l_row['isys_obj_type__const'],
                    'title' => $l_row['isys_obj__title'],
                ];

                // Remember Object ID
                $this->m_object_ids[$l_row['isys_obj__id']] = $l_row['isys_obj__id'];
            }
        }

        return new isys_export_data($l_return);
    }

    /**
     * Import method for ports assigned layer2 nets.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function port_assigned_layer2_nets_import($p_value)
    {
        $l_data = $p_value[C__DATA__VALUE];
        $l_return = null;

        if (!is_array($l_data)) {
            return $l_data;
        }

        foreach ($l_data as $l_value) {
            if (is_array($l_value)) {
                if (array_key_exists($l_value['id'], $this->m_object_ids)) {
                    $l_return[] = $this->m_object_ids[$l_value['id']];
                }
            } else {
                if ($l_value > 0) {
                    if (in_array($l_value, $this->m_object_ids)) {
                        $l_return[] = $l_value;
                    }
                }
            }
        }

        return $l_return;
    }

    /**
     * @param $p_id
     *
     * @return array|isys_export_data
     * @throws isys_exception_database
     */
    public function database_instance($p_id)
    {
        $l_dao = new isys_cmdb_dao($this->m_database);

        $l_sql = 'SELECT isys_connection__isys_obj__id FROM isys_connection WHERE isys_connection__id = ' . $l_dao->convert_sql_id($p_id);
        $l_data = $l_dao->retrieve($l_sql)
            ->__to_array();

        if (defined('C__OBJTYPE__RELATION') && $l_dao->get_objTypeID($l_data['isys_connection__isys_obj__id']) == C__OBJTYPE__RELATION) {
            $l_sql = 'SELECT * FROM isys_catg_relation_list INNER JOIN isys_relation_type ON isys_relation_type__id = isys_catg_relation_list__isys_relation_type__id WHERE isys_catg_relation_list__isys_obj__id = ' .
                $l_dao->convert_sql_id($l_data['isys_connection__isys_obj__id']);
            $l_rel_data = $l_dao->retrieve($l_sql)
                ->__to_array();

            $l_master_obj = $l_dao->get_object_by_id($l_rel_data['isys_catg_relation_list__isys_obj__id__master'])
                ->__to_array();
            $l_slave_obj = $l_dao->get_object_by_id($l_rel_data['isys_catg_relation_list__isys_obj__id__slave'])
                ->__to_array();

            $l_return[] = [
                'title' => $l_master_obj['isys_obj__title'],
                'id'    => $l_master_obj['isys_obj__id'],
                'type'  => $l_master_obj['isys_obj_type__const'],
                'sysid' => $l_master_obj['isys_obj__sysid'],
            ];

            $l_return[] = [
                'title' => $l_slave_obj['isys_obj__title'],
                'id'    => $l_slave_obj['isys_obj__id'],
                'type'  => $l_slave_obj['isys_obj_type__const'],
                'sysid' => $l_slave_obj['isys_obj__sysid'],
            ];

            $l_return[] = [
                'id'         => $l_rel_data['isys_relation_type__id'],
                'title'      => $this->translate($l_rel_data['isys_relation_type__title']),
                'title_lang' => $l_rel_data['isys_relation_type__title']
            ];

            return new isys_export_data($l_return);
        } else {
            return $this->object($l_data['isys_connection__isys_obj__id']);
        }
    }

    /**
     * @param $p_value
     *
     * @return mixed|null
     * @throws isys_exception_database
     */
    public function database_instance_import($p_value)
    {
        if (isset($p_value[C__DATA__VALUE]) && is_countable($p_value[C__DATA__VALUE]) && count($p_value[C__DATA__VALUE]) > 1) {
            // relation object
            $l_master_obj = $p_value[C__DATA__VALUE][0];
            $l_slave_obj = $p_value[C__DATA__VALUE][1];
            $l_rel_type = $p_value[C__DATA__VALUE][2];

            $l_dao = new isys_cmdb_dao($this->m_database);

            if (array_key_exists($l_master_obj['id'], $this->m_object_ids) && array_key_exists($l_slave_obj['id'], $this->m_object_ids)) {
                $l_rel_type_id = isys_import_handler::check_dialog('isys_relation_type', $l_rel_type['title_lang']);

                $l_sql = 'SELECT isys_catg_relation_list__isys_obj__id FROM isys_catg_relation_list ' . 'WHERE ' . 'isys_catg_relation_list__isys_obj__id__master = ' .
                    $l_dao->convert_sql_id($this->m_object_ids[$l_master_obj['id']]) . ' ' . 'AND isys_catg_relation_list__isys_obj__id__slave = ' .
                    $l_dao->convert_sql_id($this->m_object_ids[$l_slave_obj['id']]) . ' ' . 'AND isys_catg_relation_list__isys_relation_type__id = ' .
                    $l_dao->convert_sql_int($l_rel_type_id);

                $l_res = $l_dao->retrieve($l_sql);

                if ($l_res->num_rows() > 0) {
                    $l_row = $l_res->get_row();

                    return $l_row['isys_catg_relation_list__isys_obj__id'];
                }
            }
        } else {
            // normal connection
            return $this->connection_import($p_value);
        }

        return null;
    }

    /**
     * @param $p_value
     *
     * @return null
     */
    public function logbook_changes($p_value)
    {
        return ($p_value) ? $p_value : null;
    }

    /**
     * No import function is needed it is only used for the print view
     *
     * @param $p_value
     *
     * @return mixed|null|string
     */
    public function contract_property_next_contract_end_date($p_value)
    {
        $l_dao = isys_cmdb_dao_category_s_contract::instance($this->m_database);

        if ($p_value > 0) {
            $l_sql = 'SELECT * FROM isys_cats_contract_list WHERE isys_cats_contract_list__id = ' . $l_dao->convert_sql_id($p_value);

            $l_catdata = $l_dao->retrieve($l_sql)
                ->__to_array();

            if ($l_catdata["isys_cats_contract_list__isys_contract_notice_period_type__id"] == defined_or_default('C__CONTRACT__ON_CONTRACT_END')) {
                if (!empty($l_catdata['isys_cats_contract_list__end_date']) && $l_catdata['isys_cats_contract_list__end_date'] != '1970-01-01 00:00:00' &&
                    $l_catdata['isys_cats_contract_list__end_date'] != '0000-00-00 00:00:00') {
                    $l_contract_end = rtrim($l_catdata['isys_cats_contract_list__end_date'], ' 00:00:00');
                } else {
                    $l_contract_end = $this->translate('LC__CMDB__CATS__CONTRACT__CONTRACT_EXPIRATION_DATE_IS_NOT_DEFINED');
                }
            } elseif ($l_catdata["isys_cats_contract_list__isys_contract_notice_period_type__id"] == defined_or_default('C__CONTRACT__FROM_NOTICE_DATE')) {
                if (!empty($l_catdata['isys_cats_contract_list__notice_date']) && $l_catdata['isys_cats_contract_list__notice_date'] != '1970-01-01 00:00:00' &&
                    $l_catdata['isys_cats_contract_list__notice_date'] != '0000-00-00 00:00:00') {
                    $l_contract_end = $l_dao->calculate_next_contract_end_date(
                        $l_catdata['isys_cats_contract_list__notice_date'],
                        $l_catdata['isys_cats_contract_list__notice_period'],
                        $l_catdata['isys_cats_contract_list__notice_period_unit__id']
                    );
                } else {
                    $l_contract_end = $this->translate('LC__CMDB__CATS__CONTRACT__CONTRACT_EXPIRATION_DATE_IS_NOT_DEFINED');
                }
            } else {
                $l_contract_end = '-';
            }

            return $l_contract_end;
        }

        return null;
    }

    /**
     * No import function is needed it is only used for the print view
     *
     * @param $p_value
     *
     * @return mixed|null|string
     */
    public function contract_property_next_notice_end_date($p_value)
    {
        $l_dao = isys_cmdb_dao_category_s_contract::instance($this->m_database);

        if ($p_value > 0) {
            $l_sql = 'SELECT * FROM isys_cats_contract_list WHERE isys_cats_contract_list__id = ' . $l_dao->convert_sql_id($p_value);

            $l_catdata = $l_dao->retrieve($l_sql)
                ->__to_array();

            if ($l_catdata["isys_cats_contract_list__isys_contract_notice_period_type__id"] == defined_or_default('C__CONTRACT__ON_CONTRACT_END')) {
                if (!empty($l_catdata['isys_cats_contract_list__end_date']) && $l_catdata['isys_cats_contract_list__end_date'] != '1970-01-01 00:00:00' &&
                    $l_catdata['isys_cats_contract_list__end_date'] != '0000-00-00 00:00:00') {
                    $l_expiration_date = $l_dao->calculate_noticeperiod(
                        rtrim($l_catdata['isys_cats_contract_list__end_date'], '00:00:00'),
                        $l_catdata['isys_cats_contract_list__notice_period'],
                        $l_catdata['isys_cats_contract_list__notice_period_unit__id']
                    );
                } else {
                    $l_expiration_date = $this->translate('LC__CMDB__CATS__CONTRACT__CONTRACT_EXPIRATION_DATE_IS_NOT_DEFINED');
                }
            } elseif ($l_catdata["isys_cats_contract_list__isys_contract_notice_period_type__id"] == defined_or_default('C__CONTRACT__FROM_NOTICE_DATE')) {
                if (!empty($l_catdata['isys_cats_contract_list__notice_date']) && $l_catdata['isys_cats_contract_list__notice_date'] != '1970-01-01 00:00:00' &&
                    $l_catdata['isys_cats_contract_list__notice_date'] != '0000-00-00 00:00:00') {
                    $l_expiration_date = $this->translate('LC__UNIVERSAL__ANYTIME');
                } else {
                    $l_expiration_date = $this->translate('LC__CMDB__CATS__CONTRACT__CONTRACT_EXPIRATION_DATE_IS_NOT_DEFINED');
                }
            } else {
                $l_expiration_date = '-';
            }

            return $l_expiration_date;
        }

        return null;
    }

    /**
     * Export helper property assigned_objects for category token (for customer)
     *
     * @param $p_value
     *
     * @return isys_export_data|null
     */
    public function token_property_assigned_objects($p_value)
    {
        if (class_exists('isys_cmdb_dao_category_g_token')) {
            /**
             * @var $l_dao isys_cmdb_dao_category
             */
            $l_dao = isys_cmdb_dao_category_g_token::instance($this->m_database);

            if (isset($this->m_format_info[2][0])) {
                $l_table = $this->m_format_info[2][0];
            } else {
                $l_table = 'isys_catg_token_list_2_isys_obj';
            }

            if ($p_value > 0) {
                $l_return = null;
                $l_res = $l_dao->get_assigned_objects($p_value, null, $l_table);

                if ($l_res) {
                    while ($l_row = $l_res->get_row()) {
                        $l_object = $l_dao->get_object_by_id($l_row[$l_table . '__isys_obj__id'])
                            ->get_row();

                        $l_return[] = [
                            'title' => $l_object['isys_obj__title'],
                            'id'    => $l_object['isys_obj__id'],
                            'type'  => $l_object['isys_obj_type__const'],
                            'sysid' => $l_object['isys_obj__sysid'],
                        ];
                    }
                }

                return new isys_export_data($l_return);
            }
        }

        return null;
    }

    /**
     * Import helper property assigned_objects for category token (for customer)
     *
     * @param $p_value
     *
     * @return array|null
     */
    public function token_property_assigned_objects_import($p_value)
    {
        $l_data = $p_value[C__DATA__VALUE];
        $l_return = null;

        foreach ($l_data as $l_value) {
            if (is_array($l_value)) {
                if (array_key_exists($l_value['id'], $this->m_object_ids)) {
                    $l_return[] = $this->m_object_ids[$l_value['id']];
                }
            } else {
                if ($l_value > 0) {
                    if (in_array($l_value, $this->m_object_ids)) {
                        $l_return[] = $l_value;
                    }
                }
            }
        }

        return $l_return;
    }

    /**
     * Export helper method for property controller for category hba
     *
     * @param $p_value
     *
     * @return array
     */
    public function fc_port_property_controller($p_value)
    {
        $l_dao = isys_cmdb_dao_category_g_hba::instance($this->m_database);
        $l_data = $l_dao->retrieve('SELECT isys_catg_hba_list__title FROM isys_catg_hba_list ' . 'WHERE isys_catg_hba_list__id = ' . $l_dao->convert_sql_id($p_value))
            ->__to_array();

        if ($l_data) {
            return [
                'id'    => $p_value,
                'type'  => 'C__CATG__HBA',
                'title' => $l_data['isys_catg_hba_list__title']
            ];
        } else {
            return [];
        }
    }

    /**
     * Import helper method for category hba property controller
     *
     * @param $p_value
     *
     * @return null
     */
    public function fc_port_property_controller_import($p_value)
    {
        $l_id = $p_value['id'];

        if ($l_id > 0 && defined('C__CATG__HBA') && array_key_exists($l_id, $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__HBA')])) {
            return $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__HBA')][$l_id];
        } else {
            return null;
        }
    }

    /**
     * Export helper method for category access property formatted_url
     *
     * @param $p_value
     *
     * @return null|string
     */
    public function access_property_formatted_url($p_value)
    {
        $l_data = isys_cmdb_dao_category_g_access::instance($this->m_database)
            ->get_data($p_value)
            ->get_row();

        if (!empty($l_data['isys_catg_access_list__url'])) {
            return isys_helper_link::handle_url_variables($l_data['isys_catg_access_list__url'], $l_data['isys_catg_access_list__isys_obj__id']);
        } else {
            return null;
        }
    }

    /**
     * Export Helper for property pos for global category location
     *
     * @param $p_value
     *
     * @return array|null
     */
    public function location_property_pos($p_value)
    {
        if (is_numeric($p_value) && $p_value >= 0) {
            return [
                'title'  => $p_value,
                'obj_id' => $this->m_row['isys_catg_location_list__isys_obj__id']
            ];
        } else {
            return null;
        }
    }

    /**
     * Import Helper for property pos for global category location
     *
     * @param $p_value
     *
     * @return int
     */
    public function location_property_pos_import($p_value)
    {
        // @see ID-7781
        if (is_array($p_value[C__DATA__VALUE]) && isset($p_value[C__DATA__VALUE]['title']) && is_numeric($p_value[C__DATA__VALUE]['title'])) {
            return $p_value[C__DATA__VALUE]['title'];
        }

        if (is_array($p_value) && is_numeric($p_value[C__DATA__VALUE])) {
            // Parent already in array so check in_array
            if (in_array($this->m_property_data['parent'][C__DATA__VALUE], $this->m_object_ids)) {

                // Check parent location
                $l_dao_loc = isys_cmdb_dao_category_g_location::instance($this->m_database);
                $l_res = $l_dao_loc->get_data(null, $this->m_property_data['parent'][C__DATA__VALUE]);

                if ($l_res->num_rows() > 0) {
                    $l_dao_ff = isys_cmdb_dao_category_g_formfactor::instance($this->m_database);

                    $l_ff_res = $l_dao_ff->get_data(null, $this->m_object_ids[$p_value['obj_id']]);
                    if ($l_ff_res->num_rows() > 0) {
                        $l_ff_data = $l_ff_res->get_row();
                        $l_base = $l_ff_data['isys_catg_formfactor_list__rackunits'];
                    } else {
                        $l_base = 1;
                    }

                    $l_ff_res = $l_dao_ff->get_data(null, $this->m_property_data['parent'][C__DATA__VALUE]);

                    $l_position_already_set = false;

                    if ($l_ff_res->num_rows() > 0) {
                        $l_ff_data = $l_ff_res->get_row();
                        if ($l_ff_data['isys_catg_formfactor_list__rackunits'] < ($p_value[C__DATA__VALUE] + $l_base)) {
                            $l_data = [
                                'id'          => $l_ff_data['isys_catg_formfactor_list__id'],
                                'formfactor'  => $l_ff_data['isys_catg_formfactor_list__isys_catg_formfactor_type__id'],
                                'rackunits'   => ((int)($p_value[C__DATA__VALUE] + $l_base)),
                                'unit'        => $l_ff_data['isys_catg_formfactor_list__isys_depth_unit__id'],
                                'width'       => $l_ff_data['isys_catg_formfactor_list__installation_width'],
                                'height'      => $l_ff_data['isys_catg_formfactor_list__installation_height'],
                                'depth'       => $l_ff_data['isys_catg_formfactor_list__installation_depth'],
                                'weight'      => $l_ff_data['isys_catg_formfactor_list__installation_weight'],
                                'weight_unit' => $l_ff_data['isys_catg_formfactor_list__isys_weight_unit__id'],
                                'description' => $l_ff_data['isys_catg_formfactor_list__description']
                            ];

                            if (method_exists($l_dao_ff, 'save')) {
                                $l_dao_ff->save($l_ff_data['isys_catg_formfactor_list__id'], $l_data);
                            }
                        }
                        $l_loc_info = $l_dao_loc->get_positions_in_rack($this->m_property_data['parent'][C__DATA__VALUE]);

                        $l_insertion = $this->m_property_data['insertion'][C__DATA__VALUE];

                        $l_used_pos_front = $l_used_pos_back = [];
                        if (is_array($l_loc_info['assigned_units'])) {
                            foreach ($l_loc_info['assigned_units'] as $l_assigned_obj) {
                                if ($l_assigned_obj['pos'] === null || !is_numeric($l_assigned_obj['pos'])) {
                                    continue;
                                }

                                for ($i = $l_assigned_obj['pos'];$i < ($l_assigned_obj['height'] + $l_assigned_obj['pos']);$i++) {
                                    if ($l_assigned_obj['obj_id'] != $this->m_object_ids[$p_value['obj_id']]) {
                                        switch ($l_assigned_obj['insertion']) {
                                            case C__INSERTION__REAR:
                                                $l_used_pos_back[] = $i;
                                                break;
                                            case C__INSERTION__BOTH:
                                                $l_used_pos_front[] = $i;
                                                $l_used_pos_back[] = $i;
                                                break;
                                            case C__INSERTION__FRONT:
                                            default:
                                                $l_used_pos_front[] = $i;
                                                break;
                                        }
                                    }
                                }
                            }
                        }

                        switch ($l_insertion) {
                            case C__INSERTION__REAR:
                                $l_used_pos = $l_used_pos_back;
                                break;
                            case C__INSERTION__BOTH:
                                $l_used_pos = array_merge($l_used_pos_front, $l_used_pos_back);
                                break;
                            case C__INSERTION__FRONT:
                            default:
                                $l_used_pos = $l_used_pos_front;
                                break;
                        }

                        if (is_countable($l_used_pos) && count($l_used_pos) > 0) {
                            for ($i = $p_value[C__DATA__VALUE];$i < ($p_value[C__DATA__VALUE] + $l_base);$i++) {
                                if (in_array($i, $l_used_pos)) {
                                    $l_position_already_set = true;
                                    break;
                                }
                            }
                        }
                    } else {
                        $l_arr = [
                            'isys_obj__id' => $this->m_property_data['parent'][C__DATA__VALUE],
                            'rackunits'    => ($p_value[C__DATA__VALUE] + $l_base)
                        ];
                        $l_dao_ff->create_data($l_arr);
                    }
                    if (!$l_position_already_set) {
                        return $p_value[C__DATA__VALUE];
                    }
                }
            }
        }

        return 0;
    }

    /**
     * Export Helper for property longitude for global category location.
     *
     * @return  array
     */
    public function property_callback_gps()
    {
        return [
            $this->m_row['latitude'],
            $this->m_row['longitude'],
            'latitude'  => $this->m_row['latitude'],
            'longitude' => $this->m_row['longitude']
        ];
    }

    /**
     * Export Helper for property longitude for global category location.
     *
     * @param   array $p_val
     *
     * @return  array
     */
    public function property_callback_gps_import($p_val)
    {
        if (isset($p_val[0]) && isset($p_val[1])) {
            return [
                $p_val[0],
                $p_val[1]
            ];
        }

        if (isset($p_val['key-0']) && isset($p_val['key-1'])) {
            return [
                $p_val['key-0'],
                $p_val['key-1']
            ];
        }

        if (isset($p_val['latitude']) && isset($p_val['longitude'])) {
            return [
                $p_val['latitude'],
                $p_val['longitude']
            ];
        }

        return [0, 0];
    }

    /**
     * @return string
     */
    public function property_callback_latitude()
    {
        return $this->m_row['latitude'];
    }

    /**
     * @param array $p_val
     *
     * @return string
     */
    public function property_callback_latitude_import($p_val)
    {
        return $p_val['value'];
    }

    /**
     * @return string
     */
    public function property_callback_longitude()
    {
        return $this->m_row['longitude'];
    }

    /**
     * @param array $p_val
     *
     * @return string
     */
    public function property_callback_longitude_import($p_val)
    {
        return $p_val['value'];
    }

    /**
     * Helper method which gets all info about the reference to a category from another object.
     * Third parameter in the callback property needs to be an array with the category constant as string.
     *
     * @param $p_value
     *
     * @return array|null
     */
    public function get_referenced_object_and_category($p_value)
    {
        $l_dao = new isys_cmdb_dao($this->m_database);

        $l_return = null;

        if (isset($this->m_data_info[C__PROPERTY__DATA__REFERENCES]) && $p_value > 0) {
            $l_table = $this->m_data_info[C__PROPERTY__DATA__REFERENCES][0];
            $l_cond_column = $this->m_data_info[C__PROPERTY__DATA__REFERENCES][1];

            // Build query
            $l_sql = 'SELECT * FROM ' . $l_table . ' ' . 'INNER JOIN isys_obj ON isys_obj__id = ' . $l_table . '__isys_obj__id ' .
                'INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id ' . 'WHERE ' . $l_cond_column . ' = ' . $l_dao->convert_sql_id($p_value);

            $l_row = $l_dao->retrieve($l_sql)
                ->__to_array();

            $l_return = [
                'id'          => $l_row['isys_obj__id'],
                'type'        => $l_row['isys_obj_type__const'],
                'title'       => $l_row['isys_obj__title'],
                'sysid'       => $l_row['isys_obj__sysid'],
                'ref_id'      => $l_row[$l_table . '__id'],
                'ref_type'    => $this->m_format_info[C__PROPERTY__FORMAT__CALLBACK][2][0],
                'ref_cattype' => $this->m_format_info[C__PROPERTY__FORMAT__CALLBACK][2][1],
                'ref_title'   => $l_row[$l_table . '__title']
            ];
        }

        return $l_return;
    }

    /**
     * Import helper for get_referenced_object_and_category.
     *
     * @param $p_value
     *
     * @return bool|int
     */
    public function get_referenced_object_and_category_import($p_value)
    {
        $l_from_object = false;

        if (!empty($p_value['id']) && !empty($p_value['ref_id'])) {
            if (isset($this->m_object_ids[$p_value['id']]) && defined($p_value['ref_type']) && defined($p_value['ref_cattype'])) {
                $l_cattype = constant($p_value['ref_cattype']);
                $l_cat_const = constant($p_value['ref_type']);

                if (is_array($this->m_category_data_ids[$l_cattype][$l_cat_const])) {
                    if (array_key_exists($p_value['ref_id'], $this->m_category_data_ids[$l_cattype][$l_cat_const])) {
                        $l_from_object = true;
                    }
                }

                if ($l_from_object) {
                    return $this->m_category_data_ids[$l_cattype][$l_cat_const][$p_value['ref_id']];
                } else {
                    $l_dao = new isys_cmdb_dao($this->m_database);
                    $l_table = $this->m_data_info[C__PROPERTY__DATA__REFERENCES][0];
                    if ($l_cattype == C__CMDB__CATEGORY__TYPE_GLOBAL) {
                        $l_category = $l_dao->get_catg_by_const($p_value['ref_type'])
                            ->get_row();
                        $l_class = $l_category['isysgui_catg__class_name'];
                    } else {
                        $l_category = $l_dao->get_cats_by_const($p_value['ref_type'])
                            ->get_row();
                        $l_class = $l_category['isysgui_cats__class_name'];
                    }

                    /**
                     * @var $l_dao_obj isys_cmdb_dao_category
                     */
                    $l_dao_obj = call_user_func([
                        $l_class,
                        'instance'
                    ], $this->m_database);
                    $l_res = $l_dao_obj->get_data(
                        null,
                        $this->m_object_ids[$p_value['id']],
                        ' AND ' . $l_table . '__title = ' . $l_dao->convert_sql_text($p_value['ref_title'])
                    );
                    if ($l_res->num_rows() > 0) {
                        $l_data = $l_res->get_row();
                        $l_id = $l_data[$l_table . '__id'];
                    } else {
                        $l_id = $l_dao_obj->create_connector($l_table, $p_value['id']);
                        $l_update = 'UPDATE ' . $l_table . ' SET ' . $l_table . '__title = ' . $l_dao_obj->convert_sql_text($p_value['ref_title']) . ' WHERE ' . $l_table .
                            '__id = ' . $l_dao_obj->convert_sql_id($l_id);
                        $l_dao_obj->update($l_update);
                        $l_dao_obj->apply_update();
                    }

                    return $l_id;
                }
            }
        }

        return null;
    }

    /**
     * Set a unit constant.
     *
     * @param   string $p_const
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function set_unit_const($p_const)
    {
        $this->m_unit_const = $p_const;
    }

    /**
     * Retrieve the unit constant.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_unit_const()
    {
        return $this->m_unit_const;
    }

    /**
     * no import function is needed it is only used for the print view (global category emergency plan)
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     *
     * @param $p_id
     *
     * @return null
     * @throws isys_exception_database
     */
    public function emergency_plan_property_time_needed($p_id)
    {
        $l_dao = isys_cmdb_dao_category_s_emergency_plan::instance($this->m_database);

        $l_sql = 'SELECT isys_cats_emergency_plan_list__calc_time_need, isys_unit_of_time__const, isys_unit_of_time__title FROM isys_catg_emergency_plan_list ' .
            'INNER JOIN isys_connection ON isys_connection__id = isys_catg_emergency_plan_list__isys_connection__id ' .
            'INNER JOIN isys_cats_emergency_plan_list ON isys_cats_emergency_plan_list__isys_obj__id = isys_connection__isys_obj__id ' .
            'INNER JOIN isys_unit_of_time ON isys_unit_of_time__id = isys_cats_emergency_plan_list__isys_unit_of_time__id ' . 'WHERE isys_catg_emergency_plan_list__id = ' .
            $l_dao->convert_sql_id($p_id);

        $l_res = $l_dao->retrieve($l_sql);
        if ($l_res->num_rows() > 0) {
            $l_row = $l_res->get_row();

            $l_time['title'] = isys_convert::time(
                $l_row["isys_cats_emergency_plan_list__calc_time_need"],
                $l_row["isys_unit_of_time__const"],
                C__CONVERT_DIRECTION__BACKWARD
            ) . ' ' . $this->translate($l_row['isys_unit_of_time__title']);

            return $l_time;
        }

        return null;
    }

    /**
     * no import function is needed it is only used for the print view (global category emergency plan)
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     *
     * @param $p_id
     *
     * @return null
     * @throws isys_exception_database
     */
    public function emergency_plan_property_practice_date($p_id)
    {
        $l_dao = isys_cmdb_dao_category_s_emergency_plan::instance($this->m_database);

        $l_sql = 'SELECT isys_cats_emergency_plan_list__practice_actual_date FROM isys_catg_emergency_plan_list ' .
            'INNER JOIN isys_connection ON isys_connection__id = isys_catg_emergency_plan_list__isys_connection__id ' .
            'INNER JOIN isys_cats_emergency_plan_list ON isys_cats_emergency_plan_list__isys_obj__id = isys_connection__isys_obj__id ' .
            'WHERE isys_catg_emergency_plan_list__id = ' . $l_dao->convert_sql_id($p_id);

        $l_res = $l_dao->retrieve($l_sql);
        if ($l_res->num_rows() > 0) {
            $l_row = $l_res->get_row();
            $l_arr["title"] = isys_application::instance()->container->locales->fmt_datetime($l_row['isys_cats_emergency_plan_list__practice_actual_date']);

            return $l_arr;
        }

        return null;
    }

    /**
     * Formats seconds to actual time
     *
     * @param $value
     *
     * @return string
     */
    public function sla_property_servicetimes($value)
    {
        if (isys_format_json::is_json($value)) {
            $from = '00:00';
            $to = '00:00';

            $serviceTimes = isys_format_json::decode($value);
            if (is_array($serviceTimes)) {
                $from = $serviceTimes['from'] ? isys_cmdb_dao_category_g_sla::calculate_seconds_to_time($serviceTimes['from']) : $from;
                $to = $serviceTimes['to'] ? isys_cmdb_dao_category_g_sla::calculate_seconds_to_time($serviceTimes['to']): $to;
            }
            return $from . ' - ' . $to;
        }

        return $value;
    }

    /**
     * Formats the time to seconds
     *
     * @param $p_value
     *
     * @return null|string
     */
    public function sla_property_servicetimes_import($p_value)
    {
        if (isset($p_value[C__DATA__VALUE])) {
            if (isys_format_json::is_json_array($p_value[C__DATA__VALUE]) && strpos($p_value[C__DATA__VALUE], '-') === false) {
                return $p_value[C__DATA__VALUE];
            }

            $l_arr = explode('-', $p_value[C__DATA__VALUE]);
            $l_new_arr['from'] = isys_cmdb_dao_category_g_sla::calculate_time_to_seconds(trim($l_arr[0]));
            $l_new_arr['to'] = isys_cmdb_dao_category_g_sla::calculate_time_to_seconds(trim($l_arr[1]));
            $l_return = isys_format_json::encode($l_new_arr);

            return $l_return;
        }

        return null;
    }

    /**
     * no import function is needed it is only used for the print view (global category guest systems)
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     *
     * @param $p_id
     *
     * @return array|null
     */
    public function guest_system_property_hostname($p_id)
    {
        $l_dao = isys_cmdb_dao_category_g_ip::instance($this->m_database);

        $l_res = $l_dao->get_primary_ip($p_id);
        if ($l_res->num_rows() > 0) {
            $l_row = $l_res->get_row();
            $l_arr = [
                'title' => $l_row['isys_catg_ip_list__hostname'],
                'ip'    => $l_row['isys_cats_net_ip_addresses_list__title']
            ];

            return $l_arr;
        }

        return null;
    }

    /**
     * no import function is needed it is only used for the print view (specific category organization person)
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     *
     * @param $p_value
     *
     * @return isys_export_data
     */
    public function organization_property_contact($p_value)
    {
        $l_dao_contact = isys_cmdb_dao_category_g_contact::instance($this->m_database);
        $l_contacts = [];
        $l_contacts[] = $this->export_contact($p_value);

        return new isys_export_data($l_contacts);
    }

    /**
     * no import function is needed it is only used for the print view (specific category organization person)
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     *
     * @param $p_value
     *
     * @return isys_export_data
     */
    public function person_property_contact($p_value)
    {
        $l_dao_contact = isys_cmdb_dao_category_g_contact::instance($this->m_database);
        $l_contacts = [];
        $l_contacts[] = $this->export_contact($p_value);

        return new isys_export_data($l_contacts);
    }

    /**
     * no import function is needed it is only used for the print view (specific category organization person)
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     *
     * @param $p_value
     *
     * @return array|null
     */
    public function person_property_ldap_id($p_value)
    {
        if ($p_value > 0) {
            $l_dao = isys_ldap_dao::instance($this->m_database);
            $l_res = $l_dao->get_active_servers($p_value);
            if ($l_res->num_rows() > 0) {
                $l_data = $l_res->get_row();
                $l_arr = [
                    'id'    => $l_data['isys_ldap__id'],
                    'title' => $l_data['isys_ldap__hostname'],
                    'dn'    => $l_data['isys_ldap__dn']
                ];

                return $l_arr;
            }
        }

        return null;
    }

    /**
     * no import function is needed it is only used for the print view (specific category parallel relation)
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     *
     * @param $p_value
     *
     * @return isys_export_data|null
     */
    public function parallel_rel_property_rel_pool($p_value)
    {
        if ($p_value) {
            $l_dao = isys_cmdb_dao_category_s_parallel_relation::instance($this->m_database);
            $l_arr = [];
            $l_res = $l_dao->get_relation_pool($p_value);

            while ($l_row = $l_res->get_row()) {
                $l_arr[] = [
                    'title' => $l_row['isys_obj__title']
                ];
            }

            return new isys_export_data($l_arr);
        }

        return null;
    }

    /**
     * no import function is needed it is only used for the print view (global category virtual devices)
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     *
     * @param $p_value
     *
     * @return array|null
     */
    public function virtual_dev_property_device_type($p_value)
    {
        $l_arr = null;
        if ($p_value > 0) {
            switch ($p_value) {
                case C__VIRTUAL_DEVICE__STORAGE:
                    $l_arr = [
                        'id'    => C__VIRTUAL_DEVICE__STORAGE,
                        'title' => $this->translate("LC__CATG__STORAGE")
                    ];
                    break;

                case C__VIRTUAL_DEVICE__NETWORK:
                    $l_arr = [
                        'id'    => C__VIRTUAL_DEVICE__NETWORK,
                        'title' => $this->translate("LC__CMDB__CATG__NETWORK")
                    ];
                    break;

                case C__VIRTUAL_DEVICE__INTERFACE:
                    $l_arr = [
                        'id'    => C__VIRTUAL_DEVICE__INTERFACE,
                        'title' => $this->translate("LC__CMDB__CATG__UNIVERSAL_INTERFACE")
                    ];
                    break;

                default:
                    $p_row["device_type"] = "Unknown";
                    $l_arr = [
                        'title' => "Unknown"
                    ];
            }
        }

        return $l_arr;
    }

    /**
     * Import method
     *
     * @author Selcuk Kekec <skekec@synetics.de>
     *
     * @param array $p_value
     *
     * @return int
     */
    public function virtual_dev_property_device_type_import($p_value)
    {
        return $p_value['id'];
    }

    /**
     * Export helper for global category share access property share
     *
     * @param $p_value
     *
     * @return array|null
     * @throws Exception
     * @throws isys_exception_database
     */
    public function share_access($p_value)
    {
        $l_dao = isys_cmdb_dao::instance($this->m_database);
        $l_return = null;

        if ($p_value > 0) {
            // Build query
            $l_sql = 'SELECT * FROM isys_catg_shares_list
                INNER JOIN isys_obj ON isys_obj__id = isys_catg_shares_list__isys_obj__id
                INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
                WHERE isys_catg_shares_list__id = ' . $l_dao->convert_sql_id($p_value);
            $l_row = $l_dao->retrieve($l_sql)
                ->__to_array();
            $l_return = [
                'id'        => $l_row['isys_obj__id'],
                'type'      => $l_row['isys_obj_type__const'],
                'title'     => $l_row['isys_obj__title'],
                'sysid'     => $l_row['isys_obj__sysid'],
                'ref_id'    => $l_row['isys_catg_shares_list__id'],
                'ref_title' => $l_row['isys_catg_shares_list__title'],
                'ref_type'  => 'C__CATG__SHARES'
            ];
        }

        return $l_return;
    }

    /**
     * Import helper for global category share access property share
     *
     * @param $p_value
     *
     * @return int|null
     * @throws Exception
     * @throws isys_exception_database
     */
    public function share_access_import($p_value)
    {
        if (defined($p_value['ref_type'])) {
            if (defined('C__CATG__SHARES') && isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__SHARES')][$p_value['ref_id']])) {
                return $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__SHARES')][$p_value['ref_id']];
            } else {
                $l_dao = isys_cmdb_dao::instance($this->m_database);

                // retrieve data from database
                $l_sql = 'SELECT isys_catg_shares_list__id FROM isys_catg_shares_list WHERE isys_catg_shares_list__isys_obj__id = ' . $l_dao->convert_sql_id($p_value['id']) .
                    ' AND isys_catg_shares_list__id = ' . $p_value['ref_id'];

                return $l_dao->retrieve($l_sql)
                    ->get_row_value('isys_catg_shares_list__id');
            }
        }

        return null;
    }

    /**
     * Export helper for category network port property "default_vlan"
     *
     * @param $p_value
     *
     * @return array|null
     * @throws Exception
     * @throws isys_exception_database
     */
    public function network_port_property_default_vlan($p_value)
    {
        $l_dao = isys_cmdb_dao::instance($this->m_database);
        $l_return = null;

        if ($p_value > 0) {
            // Build query
            $l_sql = 'SELECT isys_obj__id, isys_obj__sysid, isys_obj__isys_obj_type__id FROM isys_cats_layer2_net_assigned_ports_list
                INNER JOIN isys_obj ON isys_obj__id = isys_cats_layer2_net_assigned_ports_list__isys_obj__id
                WHERE isys_catg_port_list__id = ' . $l_dao->convert_sql_id($p_value) . ' AND isys_cats_layer2_net_assigned_ports_list__default = 1';
            $l_res = $l_dao->retrieve($l_sql);
            if ($l_res->num_rows() > 0) {
                $l_row = $l_res->get_row();
                $l_obj_type = $l_dao->get_object_type($l_row['isys_obj__isys_obj_type__id']);

                $l_return = [
                    'id'        => $l_row['isys_obj__id'],
                    'type'      => $l_obj_type['isys_obj_type__const'],
                    'title'     => $l_row['isys_obj__title'],
                    'sysid'     => $l_row['isys_obj__sysid'],
                    'ref_id'    => $p_value,
                    'ref_title' => null,
                    'ref_type'  => 'C__CATG__NETWORK_PORT',
                ];
            }
        }

        return $l_return;
    }

    /**
     * Import Helper for category network port property "default_vlan"
     *
     * @param $p_value
     *
     * @return mixed|null
     * @throws Exception
     * @throws isys_exception_database
     */
    public function network_port_property_default_vlan_import($p_value)
    {
        if (isset($p_value[C__DATA__VALUE])) {
            $p_value = $p_value[C__DATA__VALUE];
        }
        if (isset($p_value['ref_type']) && defined($p_value['ref_type'])) {
            if (isset($this->m_object_ids[$p_value['id']])) {
                return $this->m_object_ids[$p_value['id']];
            } else {
                $l_dao = isys_cmdb_dao::instance($this->m_database);

                // retrieve data from database
                $l_sql = 'SELECT isys_obj__id FROM isys_obj WHERE isys_obj__title = ' . $l_dao->convert_sql_id($p_value['title']) . ' AND isys_obj__isys_obj_type__id =
                    (SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__const = ' . $l_dao->convert_sql_text($p_value['type']) . ');';
                $l_res = $l_dao->retrieve($l_sql);
                if ($l_res->num_rows() > 0) {
                    return $l_res->get_row_value('isys_obj__id');
                }
            }
        }

        return null;
    }

    /**
     * @param $p_value
     *
     * @return array
     */
    protected function get_object_id_from_member($p_value)
    {
        $l_data = [];

        if (is_array($p_value[C__DATA__VALUE])) {
            $l_data = $p_value[C__DATA__VALUE];
            $l_new_arr = [];

            foreach ($l_data as $l_key => $l_value) {
                if (is_array($l_value)) {
                    $l_new_arr[$l_key] = $this->m_object_ids[$l_value['id']];
                } else {
                    break;
                }
            }

            if (count($l_new_arr) > 0) {
                $l_data = $l_new_arr;
            }
        }

        return $l_data;
    }

    /**
     * Imports additional data. Creates new entity or updates existing one.
     *
     * @param string $p_table      Database table name
     * @param string $p_property   Property tag
     * @param array  $p_attributes List of attribute names (string). If it is an
     *                             assotiative array, the key represents the original attribute name in
     *                             database and the attribute title for exports as value.
     *
     * @return mixed Entity identifier (int), otherwise false (bool)
     */
    protected function import($p_table, $p_property, $p_attributes)
    {
        $l_dao = new isys_cmdb_dao($this->m_database);

        if (!isset($this->m_property_data[$p_property]) || !is_array($this->m_property_data[$p_property]) || !is_countable($p_attributes)) {
            return false;
        }

        $l_conditions = [];
        $l_conditions_insert = [];

        $l_attribute_length = count($p_attributes);

        foreach ($p_attributes as $l_orig => $l_attribute) {
            $l_converted = null;
            if (is_numeric($this->m_property_data[$p_property][$l_attribute]) && $this->m_property_data[$p_property][$l_attribute] >= 0) {
                $l_converted = $l_dao->convert_sql_id($this->m_property_data[$p_property][$l_attribute]);
            } elseif (is_numeric($this->m_property_data[$p_property][$l_attribute])) {
                $l_converted = $l_dao->convert_sql_int($this->m_property_data[$p_property][$l_attribute]);
            } elseif (is_string($this->m_property_data[$p_property][$l_attribute])) {
                $l_converted = $l_dao->convert_sql_text($this->m_property_data[$p_property][$l_attribute]);
            } else {
                return false;
            }

            $l_name = $l_attribute;
            if (is_string($l_orig)) {
                $l_name = $l_orig;
            }

            $l_conditions[] = $p_table . '__' . $l_name . ' = ' . $l_converted;

            if ($l_name != 'id' && $l_attribute_length > 1) {
                $l_conditions_insert[] = $p_table . '__' . $l_name . ' = ' . $l_converted;

                $l_conditions = $l_conditions_insert;
            }
        }

        $l_statement_condition = implode(' AND ', $l_conditions);

        $l_query_check = 'SELECT * FROM ' . $p_table . ' WHERE ' . $l_statement_condition;

        $l_res = $l_dao->retrieve($l_query_check);
        if ($l_res->num_rows() === 1) {
            return $l_res->get_row_value($p_table . '__id');
        }

        $l_statement = implode(', ', $l_conditions_insert);

        $l_query = 'INSERT INTO ' . $p_table . ' SET ' . $l_statement;

        if ($l_dao->update($l_query) && $l_dao->apply_update()) {
            // Somehow it does not work
            //$l_last_insert_id = $l_dao->get_last_insert_id();
            //return $l_last_insert_id;

            $l_query_check = 'SELECT * FROM ' . $p_table . ' WHERE ' . implode(' AND ', $l_conditions_insert);

            $l_res = $l_dao->retrieve($l_query_check);

            if ($l_res->num_rows() === 1) {
                $l_row = $l_res->get_row();

                return $l_row[$p_table . '__id'];
            }
        }

        return false;
    }

    /**
     * Imports an object relation.
     *
     * @param string $p_property Property tag
     * @param bool   $p_sub      (optional) Look into sub properties. Defaults to false.
     *
     * @return int Related object identifier
     */
    protected function import_object_relation($p_property, $p_sub = false)
    {
        if ($p_sub === true) {
            $l_id = intval($this->m_property_data[$p_property]['sub_' . $p_property]['id']);
        } else {
            $l_id = intval($this->m_property_data[$p_property]['id']);
        }

        if ($l_id != $this->m_object_ids[$l_id]) {
            return $this->m_object_ids[$l_id];
        }

        return $l_id;
    }

    /**
     * @param $p_value
     * @param $p_source
     *
     * @return array
     */
    protected function export_dialog($p_value, $p_source)
    {
        return [
            'id'           => $p_value,
            C__DATA__TITLE => isys_application::instance()->container->get('language')
                ->get($p_source[$p_value]),
            'title_lang'   => $p_source[$p_value]
        ];
    }

    /**
     * @param $p_result_set
     *
     * @return bool|isys_export_data
     */
    protected function export_object_relation($p_result_set)
    {
        $l_result = [];
        if ($p_result_set->num_rows() == 0) {
            return false;
        }
        while ($l_row = $p_result_set->get_row()) {
            $l_result[] = [
                'id'    => $l_row['isys_obj__id'],
                'sysid' => $l_row['isys_obj__sysid'],
                'type'  => $l_row['isys_obj_type__const'],
                'title' => $l_row['isys_obj__title'],
            ];
        }

        return new isys_export_data($l_result);
    }

    /**
     * @param      $p_id
     * @param      $p_property
     * @param      $p_source
     * @param bool $p_translate
     *
     * @return isys_export_data
     */
    protected function export_list($p_id, $p_property, $p_source, $p_translate = false)
    {
        $l_result = [];
        $l_ids = '';
        if (strpos($p_id, ',')) {
            $l_ids = explode(',', $p_id);
        }
        if (is_array($l_ids)) {
            foreach ($l_ids as $l_id) {
                if ($p_translate === true) {
                    $p_source[$l_id] = isys_application::instance()->container->get('language')
                        ->get($p_source[$l_id]);
                }
                $l_result[] = [
                    'id'    => $l_id,
                    'title' => $p_source[$l_id]
                ];
            }
        } else {
            if ($p_translate === true) {
                $p_source[$p_id] = isys_application::instance()->container->get('language')
                    ->get($p_source[$p_id]);
            }
            $l_result[] = [
                'id'    => $p_id,
                'title' => $p_source[$p_id]
            ];
        }

        return new isys_export_data($l_result);
    }

    /**
     * List method for imports.
     *
     * @param   string $p_property
     * @param   array  $p_source
     *
     * @return  mixed
     */
    protected function import_list($p_property, $p_source)
    {
        $l_result = [];

        if (!isset($this->m_property_data[$p_property][C__DATA__VALUE])) {
            return null;
        } elseif (!is_array($this->m_property_data[$p_property][C__DATA__VALUE])) {
            return $this->m_property_data[$p_property][C__DATA__VALUE];
        }

        foreach ($this->m_property_data[$p_property][C__DATA__VALUE] as $l_value) {
            if (!isset($l_value['id']) || !array_key_exists($l_value['id'], $p_source)) {
                return false;
            }
            $l_result[] = $l_value['id'];
        }

        return implode(',', $l_result);
    }

    /**
     * @param $p_property
     * @param $p_source
     *
     * @return bool
     */
    protected function transform_id($p_property, $p_source)
    {
        if (!isset($this->m_property_data[$p_property]['id'])) {
            return false;
        }
        $l_id = $this->m_property_data[$p_property]['id'];
        if (!array_key_exists($l_id, $p_source)) {
            return false;
        }

        return $l_id;
    }

    /**
     * @param $p_object_id
     * @param $p_type
     * @param $p_value
     * @param $p_property
     *
     * @return bool
     * @throws isys_exception_database
     */
    private function get_data_id_by_property_and_obj_id($p_object_id, $p_type, $p_value, $p_property)
    {
        $l_dao = new isys_cmdb_dao($this->m_database);

        $l_sql = 'SELECT isysgui_catg__source_table AS source_table 
            FROM isysgui_catg
            WHERE isysgui_catg__const = ' . $l_dao->convert_sql_text($p_type) . ' 
            
            UNION
            
            SELECT isysgui_cats__source_table AS source_table 
            FROM isysgui_cats
            WHERE isysgui_cats__const = ' . $l_dao->convert_sql_text($p_type) . ';';

        $l_category = $l_dao->retrieve($l_sql)->get_row();
        $l_source_table = $l_category['source_table'];

        $l_source_table = (!strpos($l_source_table, '_list') && !strpos($l_source_table, '_2_')) ? $l_source_table . "_list" : $l_source_table;

        $l_sql = "SELECT {$l_source_table}__id
            FROM {$l_source_table}
            WHERE {$l_source_table}__{$p_property} = " . $l_dao->convert_sql_text($p_value) . "
            AND {$l_source_table}__isys_obj__id = " . $l_dao->convert_sql_id($p_object_id) . ";";

        $l_data = $l_dao->retrieve($l_sql)->get_row();

        if ($l_data) {
            return $l_data[$l_source_table . "__id"];
        } else {
            return false;
        }
    }

    /**
     * Retrieves connected router objects from a given WAN category entry.
     *
     * @param   integer $p_id
     *
     * @return  mixed
     */
    public function wan_connected_router($p_id)
    {
        $l_res = isys_cmdb_dao_category_g_wan::instance($this->m_database)
            ->get_connected_routers($p_id);

        if (is_countable($l_res) && count($l_res)) {
            $l_return = [];

            while ($l_row = $l_res->get_row()) {
                $l_return[] = [
                    'id'         => $l_row['isys_obj__id'],
                    'sysid'      => $l_row['isys_obj__sysid'],
                    'title'      => $l_row['isys_obj__title'],
                    'type'       => $l_row['isys_obj_type__const'],
                    'type_title' => $this->language->get($l_row['isys_obj_type__title'])
                ];
            }

            return new isys_export_data($l_return);
        }

        return false;
    }

    /**
     * Retrieves router object IDs from given data.
     *
     * @param   array $p_data
     *
     * @return  array
     */
    public function wan_connected_router_import($p_data)
    {
        $l_return = [];

        if (is_array($p_data[C__DATA__VALUE])) {
            foreach ($p_data[C__DATA__VALUE] as $l_data) {
                if (array_key_exists($l_data['id'], $this->m_object_ids)) {
                    $l_return[] = $this->m_object_ids[$l_data['id']];
                }
            }
        } elseif ($p_data[C__DATA__VALUE] > 0) {
            $l_return[] = $p_data[C__DATA__VALUE];
        }

        return $l_return;
    }

    /**
     * Retrieves connected net objects from a given WAN category entry.
     *
     * @param   integer $p_id
     *
     * @return  mixed
     */
    public function wan_connected_net($p_id)
    {
        $l_res = isys_cmdb_dao_category_g_wan::instance($this->m_database)
            ->get_connected_nets($p_id);

        if (is_countable($l_res) && count($l_res)) {
            $l_return = [];

            while ($l_row = $l_res->get_row()) {
                $l_return[] = [
                    'id'    => $l_row['isys_obj__id'],
                    'sysid' => $l_row['isys_obj__sysid'],
                    'title' => $l_row['isys_obj__title'],
                    'type'  => $l_row['isys_obj_type__const'],
                    'type_title'  => $this->language->get($l_row['isys_obj_type__title'])
                ];
            }

            return new isys_export_data($l_return);
        }

        return false;
    }

    /**
     * Retrieves net object IDs from given data.
     *
     * @param   array $p_data
     *
     * @return  array
     */
    public function wan_connected_net_import($p_data)
    {
        $l_return = [];

        if (is_array($p_data[C__DATA__VALUE])) {
            foreach ($p_data[C__DATA__VALUE] as $l_data) {
                if (array_key_exists($l_data['id'], $this->m_object_ids)) {
                    $l_return[] = $this->m_object_ids[$l_data['id']];
                }
            }
        } elseif ($p_data[C__DATA__VALUE] > 0) {
            $l_return[] = $p_data[C__DATA__VALUE];
        }

        return $l_return;
    }

    /**
     * Gets id, title and type from referenced categorie
     *
     * @param int $p_id
     *
     * @return array
     */
    public function net_connector_connected_listener($p_id)
    {
        $l_dao = isys_cmdb_dao_category_g_net_listener::instance($this->m_database);

        $l_data = $l_dao->retrieve('SELECT isys_obj__id AS id, isys_obj_type__const AS type, isys_obj__title AS title, isys_obj__sysid AS sysid,
          isys_catg_net_listener_list__id AS ref_id, CONCAT(isys_catg_net_listener_list__port_from, \'-\', isys_catg_net_listener_list__port_to) AS ref_title
          FROM isys_catg_net_listener_list
          INNER JOIN isys_obj ON isys_obj__id = isys_catg_net_listener_list__isys_obj__id
           INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
          WHERE isys_catg_net_listener_list__id = ' . $l_dao->convert_sql_id($p_id))
            ->get_row();

        if ($l_data) {
            $l_data['ref_type'] = 'C__CATG__NET_LISTENER';

            return $l_data;
        }

        return null;
    }

    /**
     * Import method for referenced values.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function net_connector_connected_listener_import($p_value)
    {
        $l_dao = isys_cmdb_dao::instance($this->m_database);

        if (is_array($p_value[C__DATA__VALUE])) {
            while (array_key_exists(C__DATA__VALUE, $p_value) && is_array($p_value[C__DATA__VALUE])) {
                $p_value = $p_value[C__DATA__VALUE];
            }
        }

        if (isset($p_value['id']) && isset($p_value['ref_id']) && isset($p_value['ref_type'])) {
            // Its from a new object we have to check in both objects because the found object id could be the duplicated object
            if (isset($this->m_object_ids[$p_value['id']])) {
                if ($this->m_object_ids[$p_value['id']] == $p_value['id']) {
                    // Its from an existing object
                    return $p_value['ref_id'];
                } elseif ($this->m_object_ids[$p_value['id']] != $p_value['id']) {
                    if (defined('C__CATG__NET_LISTENER') && isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__NET_LISTENER')][$p_value['ref_id']])) {
                        // Listener is from the current object
                        return $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__NET_LISTENER')][$p_value['ref_id']];
                    } else {
                        $l_port_range = explode('-', $p_value['ref_title']);

                        // Have to retrieve it from the db
                        return $l_dao->retrieve('SELECT isys_catg_net_listener_list__id FROM isys_catg_net_listener_list
                          WHERE isys_catg_net_listener_list__isys_obj__id = ' . $l_dao->convert_sql_id($p_value['id']) . '
                           AND isys_catg_net_listener_list__port_from = ' . $l_dao->convert_sql_text($l_port_range[0]) . '
                           AND isys_catg_net_listener_list__port_to = ' . $l_dao->convert_sql_text($l_port_range[0]))
                            ->get_row_value('isys_catg_net_listener_list__id');
                    }
                }
            }
        }

        return null;
    }

    /**
     * Export helper for category vrrp_member property member
     *
     * @param $p_value
     *
     * @return array|null
     * @throws isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function vrrp_member_get_logical_port($p_value)
    {
        $l_dao = isys_cmdb_dao::instance($this->m_database);

        if ($p_value > 0) {
            // Build query
            $l_sql = 'SELECT isys_obj__id, isys_obj__sysid, isys_obj_type__const, isys_catg_log_port_list__title FROM isys_catg_vrrp_member_list
                INNER JOIN isys_catg_log_port_list ON isys_catg_log_port_list__id = isys_catg_vrrp_member_list__isys_catg_log_port_list__id
                INNER JOIN isys_obj ON isys_obj__id = isys_catg_log_port_list__isys_obj__id
                INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
                WHERE isys_catg_vrrp_member_list__id = ' . $l_dao->convert_sql_id($this->m_row['isys_catg_vrrp_member_list__id']);
            $l_res = $l_dao->retrieve($l_sql);

            if ($l_res->num_rows() > 0) {
                $l_row = $l_res->get_row();
                $l_return = [
                    'id'        => $l_row['isys_obj__id'],
                    'type'      => $l_row['isys_obj_type__const'],
                    'title'     => $l_row['isys_obj__title'],
                    'sysid'     => $l_row['isys_obj__sysid'],
                    'ref_id'    => $p_value,
                    'ref_title' => $l_row['isys_catg_log_port_list__title'],
                    'ref_type'  => 'C__CATG__NETWORK_LOG_PORT',
                ];

                return $l_return;
            }
        }

        return null;
    }

    /**
     * Import helper for category vrrp_member property member
     *
     * @param $p_data
     *
     * @return mixed|null
     * @throws isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function vrrp_member_get_logical_port_import($p_data)
    {
        if (is_array($p_data) && !empty($p_data)) {
            if (isset($this->m_object_ids[$p_data['id']])) {
                $l_object_id = $this->m_object_ids[$p_data['id']];
                $l_dao = isys_cmdb_dao::instance($this->m_database);

                $l_sql = 'SELECT isys_catg_log_port_list__id
                  FROM isys_catg_log_port_list
                  WHERE isys_catg_log_port_list__isys_obj__id = ' . $l_dao->convert_sql_id($l_object_id) . '
                  AND isys_catg_log_port_list__title = ' . $l_dao->convert_sql_text($p_data['ref_title']);
                $l_res = $l_dao->retrieve($l_sql);
                if ($l_res->num_rows()) {
                    return $l_res->get_row_value('isys_catg_log_port_list__id');
                }
            }
        }

        return null;
    }

    /**
     * Reformats Inventory No if a tenantsetting for auto-inventory-no is set
     *
     * @param $p_value
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function auto_inventory_no($p_value)
    {
        if ($this->m_row === null) {
            return $p_value;
        }

        $l_dao = isys_cmdb_dao_category_g_accounting::instance($this->m_database);

        if (!isset($this->m_row['isys_obj__isys_obj_type__id']) && !isset($this->m_row['isys_obj__id']) && !isset($this->m_row['isys_obj__title']) &&
            !isset($this->m_row['isys_obj__sysid'])) {
            $l_object_data = $l_dao->get_object($this->m_row['isys_catg_accounting_list__isys_obj__id'])
                ->get_row();
        } else {
            $l_object_data = [
                'isys_obj__id'                => $this->m_row['isys_obj__id'],
                'isys_obj__isys_obj_type__id' => $this->m_row['isys_obj__isys_obj_type__id'],
                'isys_obj__title'             => $this->m_row['isys_obj__title'],
                'isys_obj__sysid'             => $this->m_row['isys_obj__sysid']
            ];
        }

        $l_format = isys_tenantsettings::get('cmdb.objtype.' . $l_object_data['isys_obj__isys_obj_type__id'] . '.auto-inventory-no', '');
        if ($l_format != '') {
            $p_value = $l_dao->replace_placeholders(
                $l_format,
                $l_object_data['isys_obj__id'],
                $l_object_data['isys_obj__isys_obj_type__id'],
                $l_object_data['isys_obj__title'],
                $l_object_data['isys_obj__sysid']
            );
        }

        return $p_value;
    }

    /**
     * @param $p_value
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function auto_inventory_no_import($p_value)
    {
        if (is_array($p_value)) {
            return $p_value[C__DATA__VALUE];
        }

        return null;
    }

    /**
     * Get cache for the specified key
     *
     * @param $nameSpace
     * @param $checkKey
     *
     * @return bool|mixed
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getCacheContent($nameSpace, $checkKey)
    {
        // Check whether cache is initialized
        if (!$this->cache) {
            return null;
        }

        $cache = $this->cache->get($nameSpace);
        if (!$cache || !isset($cache[$checkKey])) {
            return false;
        }

        return $cache[$checkKey];
    }

    /**
     * Set cache for the specified key
     *
     * @param $nameSpace
     * @param $key
     * @param $value
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setCacheContent($nameSpace, $key, $value)
    {
        // Check whether cache is initialized
        if (!$this->cache) {
            return;
        }

        $cache = $this->cache->get($nameSpace);
        if ($cache === false) {
            $cache = [$key => $value];
        } else {
            $cache[$key] = $value;
        }
        $this->cache->set($nameSpace, $cache);
    }

    /**
     * Constructor
     *
     * @param  array                   $p_row
     * @param  isys_component_database $p_database
     * @param  array                   $p_data_info
     * @param  array                   $p_format_info
     * @param  array                   $p_ui_info
     * @param  bool                    $caching
     *
     * @throws Exception
     */
    public function __construct($p_row = [], $p_database = null, $p_data_info = [], $p_format_info = [], $p_ui_info = null, $caching = true)
    {
        // Check wheter caching is desired or not
        if ($caching) {
            // Set cache file name
            $this->cacheFileName = uniqid('import_cache-', true);
            $this->cache = isys_cache::keyvalue()->ns($this->cacheFileName);
        }

        $this->m_row = $p_row;
        $this->m_data_info = $p_data_info;
        $this->m_format_info = $p_format_info;
        $this->m_database = $p_database;
        $this->m_ui_info = $p_ui_info;
        $this->language = isys_application::instance()->container->get('language');
    }

    /**
     * Destructor
     * Invalidate cache
     */
    public function __destruct()
    {
        // Check whether cache is initialized
        if ($this->cache) {
            $this->cache->ns_invalidate($this->cacheFileName);
        }
    }
}
