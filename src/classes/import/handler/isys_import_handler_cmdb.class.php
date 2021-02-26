<?php

use idoit\Context\Context;
use idoit\Module\Cmdb\Search\Index\Signals as SearchIndexSignals;

/**
 * i-doit
 *
 * CMDB import handler.
 *
 * @package     i-doit
 * @subpackage  import
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_import_handler_cmdb extends isys_import_handler
{
    /**
     * Data structure's element key for objects.
     *
     * @var  string
     */
    const C__OBJECTS = 'objects';

    /**
     * Data structure's element key for category types.
     *
     * @var  string
     */
    const C__CATEGORY_TYPES = 'category_types';

    /**
     * Data structure's element key for categories.
     *
     * @var  string
     */
    const C__CATEGORIES = 'categories';

    /**
     * Data structure's element key for all category data.
     *
     * @var  string
     */
    const C__CATEGORY_ENTITIES = 'category_entities';

    /**
     * Data structure's element key for properties.
     *
     * @var  string
     */
    const C__PROPERTIES = 'properties';

    /**
     * Data structure's element key for priorities.
     *
     * @var  string
     */
    const C__PRIORITY = 'priority';

    /**
     * Data structure's element key to mark dummies.
     *
     * @var  string
     */
    const C__DUMMY = 'dummy';

    /**
     * Threshold for comparison.
     *
     * @var  integer
     */
    const C__COMPARISON__THRESHOLD = 4;

    /**
     * Comparison result: failed.
     *
     * @var  integer
     */
    const C__COMPARISON__FAILED = -1;

    /**
     * Comparison result: different.
     *
     * @var  integer
     */
    const C__COMPARISON__DIFFERENT = 0;

    /**
     * Comparison result: same.
     *
     * @var  integer
     */
    const C__COMPARISON__SAME = 1;

    /**
     * Comparison result: partly different.
     *
     * @var  integer
     */
    const C__COMPARISON__PARTLY = 2;

    /**
     * Import mode: Append data to existing one.
     *
     * @var  integer
     */
    const C__APPEND = 1;

    /**
     * Import mode: Merge data with existing one.
     *
     * @var  integer
     */
    const C__MERGE = 2;

    /**
     * Import mode: Overwrite existing data.
     *
     * @var  integer
     */
    const C__OVERWRITE = 4;

    /**
     * Import mode: Update existing data by identifiers.
     *
     * @var  integer
     */
    const C__USE_IDS = 8;

    /**
     * Status to keep existing data.
     *
     * @var  integer
     */
    const C__KEEP = 0;

    /**
     * Status to crete new data
     *
     * @var  integer
     */
    const C__CREATE = 1;

    /**
     * Status to update existing data
     *
     * @var  integer
     */
    const C__UPDATE = 2;

    /**
     * Status to clear existing data first
     *
     * @var  integer
     */
    const C__CLEAR = 4;

    /**
     * Status to leave existing data untouched
     *
     * @var  integer
     */
    const C__UNTOUCHED = 8;

    /**
     * Status to update existing data and add another ones, which are new
     *
     * @var  integer
     */
    const C__UPDATE_ADD = 16;

    /**
     * Variable which marks the "changed" status.
     *
     * @var  boolean
     */
    protected static $m_changed = false;

    /**
     * Static variable which defines if host address conflicts should be overwritten or not
     *
     * @var int
     */
    protected static $m_overwrite_ip_conflicts = false;

    /**
     * Name of Index to store the actual processing objectID
     *
     * @var int
     */
    protected static $m_stored_variable_name = null;

    /**
     * Information about all available categories
     *
     * @var array
     */
    protected $m_all_categories;

    /**
     * @var
     */
    protected $m_cached_category_const = [
        C__CMDB__CATEGORY__TYPE_GLOBAL   => [],
        C__CMDB__CATEGORY__TYPE_SPECIFIC => [],
        C__CMDB__CATEGORY__TYPE_CUSTOM   => []
    ];

    /**
     * Information about existing objects.
     *
     * @var array Associative array
     */
    protected $m_cached_objects = [];

    /**
     * SYSIDs and their corresponding object identifiers
     *
     * @var array Associative array
     */
    protected $m_cached_sysids = [];

    /**
     * Object identifiers by titles and constants
     *
     * @var array Associative array
     */
    protected $m_cached_title_constants = [];

    /**
     * Infomation about category DAOs
     *
     * @var array Associativa array
     */
    protected $m_cat_info = [];

    /**
     * Array with exported category data identifiers as keys and their correspondant identifiers in database as values.
     *
     * @var  array
     */
    protected $m_category_data_ids = [];

    /**
     * Array with exported category identifiers as keys and their correspondant identifiers in database as values.
     *
     * @var  array
     */
    protected $m_category_ids = [];

    /**
     * The CMDB Dao.
     *
     * @var isys_cmdb_dao
     */
    protected $m_cmdb_dao;

    /**
     * The import data array.
     *
     * @var  array
     */
    protected $m_data = [];

    /**
     * Definition of the date format.
     *
     * @var  string
     */
    protected $m_date_format = 'Y-m-d H:i:s';

    /**
     * Database connection.
     *
     * @var  isys_component_database
     */
    protected $m_db;

    /**
     * Defines what should happen to empty fields.
     *
     * @var  integer
     */
    protected $m_empty_fields;

    /**
     * I-doits event manager.
     *
     * @var  isys_event_manager
     */
    protected $m_event_manager;

    /**
     * Array of found objects to import.
     *
     * @var  array
     */
    protected $m_found_objects = [];

    /**
     * Associative array of category type identifiers as keys and an array of category identifiers as values.
     *
     * @var  array
     */
    protected $m_ignored_categories = [
        C__CMDB__CATEGORY__TYPE_GLOBAL   => [],
        C__CMDB__CATEGORY__TYPE_SPECIFIC => [],
        C__CMDB__CATEGORY__TYPE_CUSTOM   => [],
    ];

    /**
     * Current user who triggered the import
     *
     * @var null
     */
    protected $m_import_started_by = null;

    /**
     * Temporary variable with information about importing data.
     *
     * @var  array
     */
    protected $m_info = [];

    /**
     * Variable which contains constants of installed Modules
     *
     * @var array
     */
    protected $m_installed_modules = [];

    /**
     * Array which contains all logbook entries.
     *
     * @var  array
     */
    protected $m_logbook_entries = [];

    /**
     * Logbook event.
     *
     * @var  string
     */
    protected $m_logbook_event = 'C__LOGBOOK_EVENT__CATEGORY_CHANGED';

    /**
     * Mandator information array.
     *
     * @var  array
     */
    protected $m_mandator = [];

    /**
     * @var isys_module_logbook
     */
    protected $m_mod_logbook;

    /**
     * Import mode.
     *
     * @var  integer
     */
    protected $m_mode;

    /**
     * Defines how to handle multivalue categories.
     *
     * @var  integer
     */
    protected $m_multivalue_categories;

    /**
     * Variable to count the sum of objects to be imported.
     *
     * @var  integer
     */
    protected $m_object_counter = 0;

    /**
     * Determines if the imported object should be created by the import or not
     *
     * @var bool
     */
    protected $m_object_created_by_others = false;

    /**
     * Array with exported object identifiers as keys and their correspondant identifiers in database as values.
     *
     * @var  array
     */
    protected $m_object_ids = [];

    /**
     * Array of object states.
     *
     * @var  array
     */
    protected $m_object_states = [];

    /**
     * Array with exported object type identifiers as keys and their correspondant identifiers in database as values.
     *
     * @var  array
     */
    protected $m_object_type_ids = [];

    /**
     * Array with object type identifiers as keys and their correspondant constants as values
     *
     * @var  array
     */
    protected $m_object_types = [];

    /**
     * Prepared data.
     *
     * @var  array
     */
    protected $m_prepared = [];

    /**
     * Array with exported property identifiers as keys and their correspondant identifiers in database as values.
     *
     * @var  array
     */
    protected $m_property_ids = [];

    /**
     * Date of the scan time, will be used for the log.
     *
     * @var  string
     */
    protected $m_scantime = null;

    /**
     * I-doit signal and slot manager.
     *
     * @var  object
     */
    protected $m_signals;

    /**
     * Import/Export type.
     *
     * @var  integer
     */
    protected $m_type;

    /**
     *
     */
    protected $m_logbook_source;

    /**
     * @var array
     */
    protected $importCounters = [
        'created'          => 0,
        'updated'          => 0,
        'category_updated' => 0,
        'category_skipped' => 0
    ];

    /**
     * Array which contains all objects where changes were made
     *
     * @var array
     */
    protected $changesInObjectIds = [];

    /**
     * Getter for member variable $changesInObjectIds
     *
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getChangesInObjectIds()
    {
        return $this->changesInObjectIds;
    }

    /**
     * Setter for member variable $changesInObjectIds
     *
     * @param $objId
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setChangesInObjectIds($objId)
    {
        $this->changesInObjectIds[$objId] = $objId;
    }

    /**
     * Variable which holds the i-doit version number.
     *
     * @var  string
     */
    protected $m_version = ''; // function

    private $m_possible_empty_values = [
        '0000-00-00 00:00:00',
        '1970-01-01 00:00:00'
    ]; // function

    /**
     * Array which contains all new titles to the assigned object id (usage in duplicate)
     *
     * @var array
     */
    private $m_replaced_titles = [];

    /**
     * Indicator for replacing Data
     * of C__CATG__GLOBAL with actual values
     *
     * Attributes are:
     * - created, updated
     * - created_by, updated_by
     */
    private $m_update_globals = false;

    /**
     * Indicator if location fix should be triggered or not
     *
     * @var bool
     */
    private $m_trigger_location_fix = false;

    /**
     * @var int
     */
    private $importStartTime = null;

    /**
     * Sets indicator if overlapping host addresses should be overwritten or not
     *
     * @param $p_bool
     */
    public static function set_overwrite_ip_conflicts($p_bool)
    {
        self::$m_overwrite_ip_conflicts = (bool)$p_bool;
    }

    /**
     * Gets indicator for overwriting overlapping host addresses
     *
     * @return bool|int
     */
    public static function get_overwrite_ip_conflicts()
    {
        return self::$m_overwrite_ip_conflicts;
    }

    /**
     * Method for retrieving the "changed" status.
     *
     * @static
     * @return  boolean
     */
    public static function changed()
    {
        return isys_import_handler_cmdb::$m_changed;
    }

    /**
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_logbook_source($p_value)
    {
        $this->m_logbook_source = $p_value;
    }

    /**
     * Parses data from an i-doit CMDB export.
     *
     * @param string $p_xml_data XML data. Set it to null to use XML data that
     *                           is already set before. Defaults to null.
     *
     * @return bool Success?
     */
    public function parse($p_xml_data = null)
    {
        try {
            $this->m_log->info('Parsing the import file...');
            $l_xml_data = $p_xml_data;
            if (empty($p_xml_data)) {
                $l_xml_data = $this->get_xml_object();
            } else {
                $this->set_xml_object($l_xml_data);
            }

            // Export info:
            $this->m_scantime = strtotime((string)$l_xml_data->head->datetime);
            $this->m_mandator = [
                C__DATA__VALUE => (string)$l_xml_data->head->mandator,
            ];
            $l_mandator_attr = (array)$l_xml_data->head->mandator->attributes();
            $this->m_mandator = array_merge($this->m_mandator, $l_mandator_attr['@attributes']);
            $this->m_type = $l_xml_data->head->type;
            $this->m_version = $l_xml_data->head->version;

            // Initialize:
            $l_object_id = 0;
            $l_title = null;
            $l_type = null;

            if (!isset($l_xml_data->objects->object) || !is_countable($l_xml_data->objects->object) || count($l_xml_data->objects->object) <= 0) {
                throw new isys_exception_general('Import failed. File not readable or no objects inside!');
            }

            $this->m_object_counter = count($l_xml_data->objects->object);
            if ($this->m_object_counter === 1) {
                $this->m_log->info('There is 1 object waiting...');
            } else {
                $this->m_log->info('There are ' . $this->m_object_counter . ' objects waiting...');
            }

            $this->m_log->debug('Parsing objects...');

            // Iterate through objects:
            foreach ($l_xml_data->objects->object as $l_object) {
                $l_object_id = (string)$l_object->id;

                if (empty($l_object_id)) {
                    continue;
                }

                // Title:
                $l_title = (string)$l_object->title;
                // SYSID:
                $l_sysid = (string)$l_object->sysid;
                // Description:
                $l_description = (string)$l_object->description;

                // Add type attributes:

                $l_type = [];
                $l_type[C__DATA__VALUE] = (string)$l_object->type;
                $l_type_attr = (array)$l_object->type->attributes();
                foreach ($l_type_attr['@attributes'] as $l_key => $l_value) {
                    $l_type[$l_key] = $l_value;
                }
                // Created:
                $l_created = strtotime((string)$l_object->created);
                $l_created_attr = (array)$l_object->created->attributes();
                // Updated:
                $l_updated = strtotime((string)$l_object->updated);
                $l_updated_attr = (array)$l_object->updated->attributes();

                // Add head information:
                $this->m_data[$l_object_id] = [
                    C__DATA__TITLE => $l_title,
                    'id'           => $l_object_id,
                    'sysid'        => $l_sysid,
                    'type'         => $l_type,
                    'created'      => $l_created,
                    'created_by'   => $l_created_attr['@attributes']['by'],
                    'updated'      => $l_updated,
                    'updated_by'   => $l_updated_attr['@attributes']['by'],
                    'status'       => (int)$l_object->status,
                    'cmdb_status'  => (int)$l_object->cmdb_status,
                    'description'  => $l_description
                ];

                // Initialize categories:
                $this->m_data[$l_object_id][self::C__CATEGORY_TYPES] = [
                    C__CMDB__CATEGORY__TYPE_GLOBAL   => [],
                    C__CMDB__CATEGORY__TYPE_SPECIFIC => []
                ];

                $this->m_log->debug(sprintf('Found object %s (%s) of type %s.', $l_title, $l_object_id, $l_type[C__DATA__VALUE]));

                $this->m_log->debug('Parsing categories...');

                // Extract categories:
                foreach ($l_object->data->category as $l_category) {
                    // Get attributes:
                    $l_attributes = $l_category->get_attributes();
                    unset($l_category['@attributes']);

                    // Check for valid content:
                    if (!isset($l_attributes['const'])) {
                        continue;
                    }

                    $l_cat_const = $l_attributes['const'];
                    $l_cat_type = $l_attributes['category_type'];

                    if (!defined($l_cat_const)) {
                        $this->m_log->notice(printf('Category %s\'s constant has not been defined yet.', $l_cat_const));
                    }

                    $l_cat_id = constant($l_cat_const);

                    if ($l_cat_id <= 0) {
                        continue;
                    }

                    if ($this->m_trigger_location_fix === false && $l_cat_type == C__CMDB__CATEGORY__TYPE_GLOBAL && $l_cat_id == defined_or_default('C__CATG__LOCATION')) {
                        // Set flag to trigger the location fix
                        $this->m_trigger_location_fix = true;
                    }

                    // Attach information about the category:
                    $this->m_data[$l_object_id][self::C__CATEGORY_TYPES][$l_cat_type][$l_cat_id] = $l_attributes;

                    $this->m_log->debug('Parsing category data...');

                    // Extract categories data:
                    foreach ($l_category->cat_data as $l_category_data) {
                        // Extract category data information and properties:
                        $l_new_category = [];
                        $l_category_data_array = (array)$l_category_data;

                        foreach ($l_category_data_array as $l_category_data_key => $l_category_data_value) {
                            if ($l_category_data_key === '@attributes') {
                                $l_new_category = $l_category_data_value;
                                continue;
                            } elseif ($l_category_data_key === 0) {
                                // Skip empty category data:
                                continue;
                            }

                            // Recursion:
                            $l_property = $this->parse_properties($l_category_data, $l_category_data_key, $l_category_data_value);
                            $l_new_category[self::C__PROPERTIES][$l_category_data_key] = $l_property;
                        }
                        $this->m_data[$l_object_id][self::C__CATEGORY_TYPES][$l_cat_type][$l_cat_id][self::C__CATEGORY_ENTITIES][$l_new_category['data_id']] = $l_new_category;
                    }
                }
            }
        } catch (Exception $e) {
            isys_notify::warning('Error: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Prepares the import data, determines priorities and sorting the data.
     *
     * @return  isys_import_handler_cmdb
     */
    public function prepare()
    {
        try {
            $this->m_log->info('Preparing data...');

            assert(isset($this->m_data) && is_array($this->m_data));

            // Iterate through objects.
            foreach ($this->m_data as $l_object_id => $l_object_values) {
                // 1. step is to prioritize objects.

                // Initialize object's priority:
                if (!isset($this->m_prepared[$l_object_id][self::C__PRIORITY])) {
                    $this->m_prepared[$l_object_id][self::C__PRIORITY] = 0;
                }

                // Add object information:
                foreach ($l_object_values as $l_object_values_keys => $l_object_values_values) {
                    if ($l_object_values_keys !== self::C__CATEGORY_TYPES) {
                        $this->m_prepared[$l_object_id][$l_object_values_keys] = $l_object_values_values;
                    }
                }

                // Iterate trough category types:
                foreach ($l_object_values[self::C__CATEGORY_TYPES] as $l_category_type_id => $l_categories) {
                    // Iterate through categories:
                    foreach ($l_categories as $l_category_id => $l_category_values) {
                        if ($l_category_id === defined_or_default('C__CATG__LOCATION') && $l_category_values[self::C__CATEGORY_ENTITIES] === null &&
                            Context::instance()->getContextCustomer() === Context::CONTEXT_DUPLICATE) {
                            $this->m_trigger_location_fix = false;
                        }
                        // Sometimes the priority is even set without any other category values. Handle it:
                        if (!isset($this->m_prepared[$l_object_id][self::C__CATEGORIES][$l_category_type_id . '_' . $l_category_id][self::C__PRIORITY])) {
                            // Append categories to the prepared data:
                            $this->m_prepared[$l_object_id][self::C__CATEGORIES][$l_category_type_id . '_' . $l_category_id] = $l_category_values;

                            // Initialize category's priority:
                            $this->m_prepared[$l_object_id][self::C__CATEGORIES][$l_category_type_id . '_' . $l_category_id][self::C__PRIORITY] = 0;
                        } else {
                            // Append categories to the prepared data and merge it with existing priority:
                            $this->m_prepared[$l_object_id][self::C__CATEGORIES][$l_category_type_id . '_' . $l_category_id] = array_merge(
                                $l_category_values,
                                $this->m_prepared[$l_object_id][self::C__CATEGORIES][$l_category_type_id . '_' . $l_category_id]
                            );
                        }

                        // Iterate through category instances:
                        foreach ($l_category_values[self::C__CATEGORY_ENTITIES] as $l_category_data_id => $l_category_data_values) {
                            if (!isset($l_category_data_values[self::C__PROPERTIES])) {
                                // Skip empty category data:
                                continue;
                            }

                            // Iterate through properties:
                            foreach ($l_category_data_values[self::C__PROPERTIES] as $l_property_id => $l_property) {
                                // Iterate recursivly through properties:
                                $l_found_references = $this->find_references(
                                    $l_object_id,
                                    $l_category_type_id,
                                    $l_category_id,
                                    $l_category_data_id,
                                    $l_property_id,
                                    $l_property
                                );

                                if (is_countable($l_found_references) && count($l_found_references) > 0) {
                                    foreach ($l_found_references as $l_ref) {
                                        if (isset($l_ref['id']) && isset($l_ref['type']) && isset($l_ref['sysid'])) {
                                            // We've found a property that's related to an object:
                                            // Look for this referenced object:
                                            $l_found_object = $this->fetch_object_reference(
                                                $l_object_id,
                                                $l_ref['id'],
                                                $l_ref['type'],
                                                $l_ref['sysid'],
                                                $l_ref[C__DATA__VALUE]
                                            );

                                            // If false, there are two possibilities:
                                            // 1. Object was not exported.
                                            // 2. Data is corrupted.
                                            // The import function has to handle it!
                                            if ($l_found_object === true) {
                                                // Increase found objects' priorities.
                                                // Use also this object's priority:
                                                $this->m_prepared[$l_ref['id']][self::C__PRIORITY] += $this->m_prepared[$l_object_id][self::C__PRIORITY] + 1;
                                                $this->m_info[self::C__OBJECTS][$l_object_id][self::C__PRIORITY] = $this->m_prepared[$l_ref['id']][self::C__PRIORITY];
                                            }

                                            if (isset($l_ref['ref_id']) && isset($l_ref['ref_type']) && isset($l_ref['ref_title'])) {
                                                // We've found a property that's related to a category data:
                                                // Look for this referenced category data:
                                                $l_found_category_datas = $this->fetch_category_reference(
                                                    $l_object_id,
                                                    $l_ref['ref_id'],
                                                    $l_ref['ref_type'],
                                                    $l_ref['ref_title']
                                                );

                                                // If false, there are two possibilities:
                                                // 1. Category was not exported.
                                                // 2. Data is corrupted.
                                                // The import function has to handle it!
                                                if ($l_found_category_datas !== false) {
                                                    // Increase found categories' priorities:
                                                    foreach ($l_found_category_datas as $l_found_category_data) {
                                                        // @todo  Clean up in i-doit 1.12
                                                        if (($l_category_values['const'] === 'C__CATG__STORAGE_DEVICE' ||
                                                                $l_category_values['const'] === 'C__CMDB__SUBCAT__STORAGE__DEVICE') &&
                                                            $l_ref['ref_type'] === 'C__CATG__CONTROLLER') {
                                                            /*
                                                             * @todo
                                                             * If category C__CMDB__SUBCAT__STORAGE__DEVICE has a reference to a RAID group there may be a
                                                             * problem with priorities, because RAID groups may also contain references to C__CMDB__SUBCAT__STORAGE__DEVICE.
                                                             * This double referencing causes problems with other references in C__CMDB__SUBCAT__STORAGE__DEVICE, i. e. C__CATG__CONTROLLER.
                                                             * This is just a workaround:
                                                             */
                                                            $this->m_prepared[$l_object_id][self::C__CATEGORIES][$l_found_category_data['category_type_id'] . '_' .
                                                            $l_found_category_data['category_id']][self::C__PRIORITY] += $this->m_prepared[$l_object_id][self::C__CATEGORIES][$l_category_type_id .
                                                                '_' . $l_category_id][self::C__PRIORITY] + 10000;
                                                        } else {
                                                            // Use also this category's priority:
                                                            $this->m_prepared[$l_object_id][self::C__CATEGORIES][$l_found_category_data['category_type_id'] . '_' .
                                                            $l_found_category_data['category_id']][self::C__PRIORITY] += $this->m_prepared[$l_object_id][self::C__CATEGORIES][$l_category_type_id .
                                                                '_' . $l_category_id][self::C__PRIORITY] + 1;
                                                        }
                                                    }
                                                } elseif (defined($l_ref['ref_type'])) {
                                                    if (!isset($this->m_cached_category_const[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_ref['ref_type']]) &&
                                                        !isset($this->m_cached_category_const[C__CMDB__CATEGORY__TYPE_SPECIFIC][$l_ref['ref_type']]) &&
                                                        !isset($this->m_cached_category_const[C__CMDB__CATEGORY__TYPE_CUSTOM][$l_ref['ref_type']])) {
                                                        if ($this->m_cmdb_dao->get_catg_by_const($l_ref['ref_type'])
                                                            ->num_rows()) {
                                                            $this->m_cached_category_const[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_ref['ref_type']] = true;
                                                            $l_found_category_type = C__CMDB__CATEGORY__TYPE_GLOBAL;
                                                        } elseif ($this->m_cmdb_dao->get_cats_by_const($l_ref['ref_type'])
                                                            ->num_rows()) {
                                                            $this->m_cached_category_const[C__CMDB__CATEGORY__TYPE_SPECIFIC][$l_ref['ref_type']] = true;
                                                            $l_found_category_type = C__CMDB__CATEGORY__TYPE_SPECIFIC;
                                                        } else {
                                                            $this->m_cached_category_const[C__CMDB__CATEGORY__TYPE_CUSTOM][$l_ref['ref_type']] = true;
                                                            $l_found_category_type = C__CMDB__CATEGORY__TYPE_CUSTOM;
                                                        }
                                                    } else {
                                                        $l_found_category_type = ($this->m_cached_category_const[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_ref['ref_type']] ? C__CMDB__CATEGORY__TYPE_GLOBAL : ($this->m_cached_category_const[C__CMDB__CATEGORY__TYPE_SPECIFIC][$l_ref['ref_type']] ? C__CMDB__CATEGORY__TYPE_SPECIFIC : ($this->m_cached_category_const[C__CMDB__CATEGORY__TYPE_CUSTOM][$l_ref['ref_type']] ? C__CMDB__CATEGORY__TYPE_CUSTOM : false)));
                                                    }
                                                    $this->m_prepared[$l_object_id][self::C__CATEGORIES][$l_found_category_type . '_' .
                                                    constant($l_ref['ref_type'])][self::C__PRIORITY] += $this->m_prepared[$l_object_id][self::C__CATEGORIES][$l_found_category_type .
                                                        '_' . constant($l_ref['ref_type'])][self::C__PRIORITY] + 1;
                                                }
                                            }
                                        } elseif (isset($l_ref['id']) && isset($l_ref['type'])) {
                                            // We've found a property that's related to a category data:
                                            // Look for this referenced category data:
                                            $l_found_category_datas = $this->fetch_category_reference($l_object_id, $l_ref['id'], $l_ref['type'], $l_ref[C__DATA__VALUE]);

                                            // If false, there are two possibilities:
                                            // 1. Category was not exported.
                                            // 2. Data is corrupted.
                                            // The import function has to handle it!
                                            if ($l_found_category_datas !== false) {
                                                // Increase found categories' priorities:
                                                foreach ($l_found_category_datas as $l_found_category_data) {
                                                    // @todo  Clean up in i-doit 1.12
                                                    if (($l_category_values['const'] === 'C__CATG__STORAGE_DEVICE' ||
                                                            $l_category_values['const'] === 'C__CMDB__SUBCAT__STORAGE__DEVICE') && $l_ref['type'] === 'C__CATG__CONTROLLER') {
                                                        /*
                                                         * @todo
                                                         * If category C__CMDB__SUBCAT__STORAGE__DEVICE has a reference to a RAID group there may be a
                                                         * problem with priorities, because RAID groups may also contain references to C__CMDB__SUBCAT__STORAGE__DEVICE.
                                                         * This double referencing causes problems with other references in C__CMDB__SUBCAT__STORAGE__DEVICE, i. e. C__CATG__CONTROLLER.
                                                         * This is just a workaround:
                                                         */
                                                        $this->m_prepared[$l_object_id][self::C__CATEGORIES][$l_found_category_data['category_type_id'] . '_' .
                                                        $l_found_category_data['category_id']][self::C__PRIORITY] += $this->m_prepared[$l_object_id][self::C__CATEGORIES][$l_category_type_id .
                                                            '_' . $l_category_id][self::C__PRIORITY] + 10000;
                                                    } else {
                                                        // Use also this category's priority:
                                                        $this->m_prepared[$l_object_id][self::C__CATEGORIES][$l_found_category_data['category_type_id'] . '_' .
                                                        $l_found_category_data['category_id']][self::C__PRIORITY] += $this->m_prepared[$l_object_id][self::C__CATEGORIES][$l_category_type_id .
                                                            '_' . $l_category_id][self::C__PRIORITY] + 1;
                                                    }
                                                }
                                            }
                                        }
                                    } // foreach found references
                                }
                            } // foreach properties
                        } // foreach category instances

                        $categoryId = (int)$l_category_id;

                        // Hack for database and operating system. The category application has to be handled before the databases and operating system
                        if (($categoryId === defined_or_default('C__CATG__APPLICATION') ||
                                $categoryId === defined_or_default('C__CATG__DATABASE_TABLE') ||
                                $categoryId === defined_or_default('C__CATG__OPERATING_SYSTEM')) &&
                            $l_category_type_id === defined_or_default('C__CMDB__CATEGORY__TYPE_GLOBAL')
                        ) {
                            $applicationKey = $l_category_type_id . '_' . defined_or_default('C__CATG__APPLICATION');
                            $databaseKey = $l_category_type_id . '_' . defined_or_default('C__CATG__DATABASE_SA');
                            $operatingSystemKey = $l_category_type_id . '_' . defined_or_default('C__CATG__OPERATING_SYSTEM');

                            if (!isset($this->m_prepared[$l_object_id][self::C__CATEGORIES][$applicationKey])) {
                                continue;
                            }

                            $applicationPriority = $this->m_prepared[$l_object_id][self::C__CATEGORIES][$applicationKey][self::C__PRIORITY];

                            if (isset($this->m_prepared[$l_object_id][self::C__CATEGORIES][$databaseKey]) &&
                                $this->m_prepared[$l_object_id][self::C__CATEGORIES][$databaseKey][self::C__PRIORITY] >= $applicationPriority) {
                                $this->m_prepared[$l_object_id][self::C__CATEGORIES][$applicationKey][self::C__PRIORITY] +=
                                    $this->m_prepared[$l_object_id][self::C__CATEGORIES][$databaseKey][self::C__PRIORITY] + 1;
                                continue;
                            }

                            if (isset($this->m_prepared[$l_object_id][self::C__CATEGORIES][$operatingSystemKey]) &&
                                $this->m_prepared[$l_object_id][self::C__CATEGORIES][$operatingSystemKey][self::C__PRIORITY] >= $applicationPriority) {
                                $this->m_prepared[$l_object_id][self::C__CATEGORIES][$applicationKey][self::C__PRIORITY] +=
                                    $this->m_prepared[$l_object_id][self::C__CATEGORIES][$operatingSystemKey][self::C__PRIORITY] + 1;
                            }
                        }
                    } // foreach categories
                } // foreach category types

                // Sort categories by priority:
                $this->m_info['object_id'] = $l_object_id;
                if (isset($this->m_prepared[$l_object_id][self::C__CATEGORIES]) && is_array($this->m_prepared[$l_object_id][self::C__CATEGORIES])) {
                    $this->m_log->info('Sorting categories by priority...');
                    uksort($this->m_prepared[$l_object_id][self::C__CATEGORIES], [
                        $this,
                        'sort_categories'
                    ]);
                }

                unset($this->m_info['object_id']);
                $this->m_info[self::C__OBJECTS][$l_object_id][self::C__DUMMY] = false;
            } //foreach objects

            // Sort objects by priority.
            $this->m_log->info('Sorting objects by priority...');
            if (is_array($this->m_prepared)) {
                uksort($this->m_prepared, [
                    $this,
                    'sort_objects'
                ]);
            }

            // We don't need the raw import data anymore:
            unset($this->m_data);
            $this->m_log->debug('Optimizing data is done.');
        } catch (Exception $e) {
            isys_notify::warning('Error: ' . $e->getMessage());
        }

        return $this;
    }

    /**
     * Method for setting the "changed" status.
     *
     * @static
     * @todo Why is that used?
     *
     * @param  boolean $p_change
     */
    public static function set_change($p_change)
    {
        isys_import_handler_cmdb::$m_changed = $p_change;
    }

    /**
     * Store ObjectID for accessing it
     * later in isys_export_helper context
     *
     * @author Selcuk Kekec <skekec@i-doit.org>
     *
     * @param int $p_objectID
     */
    public static function store__objectID($p_objectID)
    {
        $_GET[self::$m_stored_variable_name] = $p_objectID;
    }

    /**
     * Return stored objectID
     *
     * @author Selcuk Kekec <skekec@i-doit.org>
     * @return  integer
     */
    public static function get_stored_objectID()
    {
        return (int)$_GET[self::$m_stored_variable_name];
    }

    /**
     * Setter for member variable $m_object_created_by_others
     *
     * @param bool $p_created_by_others
     *
     * @return $this
     */
    public function set_object_created_by_others($p_created_by_others = false)
    {
        $this->m_object_created_by_others = $p_created_by_others;

        return $this;
    }

    public function set_update_globals($p_bool = true)
    {
        $this->m_actual_values = $p_bool;
    }

    public function get_update_globals()
    {
        return $this->m_actual_values;
    }

    public function handle_update_globals(&$p_object_values)
    {
        if ($this->get_update_globals()) {
            global $g_comp_session;
            $p_object_values['created'] = $p_object_values['updated'] = time();
            $p_object_values['created_by'] = $p_object_values['updated_by'] = $g_comp_session->get_current_username();
        }
    }

    /**
     * Sets a title to an array container
     *
     * @param $p_new_title
     * @param $p_obj_id
     */
    public function set_replaced_title($p_new_title, $p_obj_id)
    {
        $this->m_replaced_titles[$p_obj_id] = $p_new_title;
    }

    /**
     * Gets the title for the object id if it exists
     *
     * @param $p_obj_id
     *
     * @return bool
     */
    public function get_replaced_title($p_obj_id)
    {
        if (isset($this->m_replaced_titles[$p_obj_id])) {
            return $this->m_replaced_titles[$p_obj_id];
        } else {
            return false;
        }
    }

    /**
     * Sets logbook event
     *
     * @param $p_event
     */
    public function set_logbook_event($p_event)
    {
        $this->m_logbook_event = $p_event;
    }

    /**
     * Gets logbook event
     *
     * @return string
     */
    public function get_logbook_event()
    {
        return $this->m_logbook_event;
    }

    /**
     * Method for setting the "m_data" variable from outside (used for JDisc import).
     *
     * @param   array $p_data
     *
     * @return  isys_import_handler_cmdb
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function set_data(array $p_data)
    {
        $this->m_data = $p_data;

        return $this;
    }

    /**
     * Method for setting the "m_prepared" variable from outside (used for JDisc import).
     *
     * @param   array $p_data
     *
     * @return  isys_import_handler_cmdb
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function set_prepared_data(array $p_data)
    {
        $this->m_prepared = $p_data;

        return $this;
    }

    /**
     * Sets object identifiers. Used by JDisc import.
     *
     * @param array $p_object_ids Indexed array
     *
     * @return isys_import_handler_cmdb Returns itself.
     */
    public function set_object_ids($p_object_ids)
    {
        assert(is_array($p_object_ids));
        $this->m_object_ids = $p_object_ids;

        return $this;
    }

    /**
     * Method for setting the "m_info" variable from outside (used for JDisc import).
     *
     * @param   array $p_info
     *
     * @return  isys_import_handler_cmdb
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function set_connection_info(array $p_info)
    {
        $this->m_info[self::C__OBJECTS] = $p_info;

        return $this;
    }

    /**
     * Method for setting the mandator info, version and type from outside (used for JDisc import).
     *
     * @param    string $p_type
     *
     * @return  isys_import_handler_cmdb
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_import_header($p_type = '')
    {
        $this->set_general_header($p_type)
            ->set_scantime();

        return $this;
    }

    /**
     * Sets import scan time
     *
     * @return $this
     */
    public function set_scantime()
    {
        $this->m_scantime = time();

        return $this;
    }

    /**
     * Set General info like mandator info, version and type from outside
     *
     * @param string $p_type
     *
     * @return $this
     */
    public function set_general_header($p_type = '')
    {
        global $g_comp_session, $g_product_info;
        $this->m_mandator[C__DATA__VALUE] = $g_comp_session->get_mandator_name();
        $this->m_mandator['id'] = $g_comp_session->get_mandator_id();
        $this->m_version = $g_product_info['version'];
        $this->m_type = $p_type;
        $this->m_import_started_by = $g_comp_session->get_current_username();

        return $this;
    }

    /**
     * Gets import mode.
     *
     * @return  integer  Returns null, if import mode has not been set yet.
     */
    public function get_mode()
    {
        return $this->m_mode;
    }

    /**
     * Sets import mode.
     *
     * @param   integer $p_mode
     *
     * @return  isys_import_handler_cmdb
     */
    public function set_mode($p_mode)
    {
        assert(is_int($p_mode));
        switch ($p_mode) {
            case self::C__APPEND:
                $this->m_log->notice('Export data will be appended to existing data.');
                break;

            case self::C__MERGE:
                $this->m_log->notice('Export data will be merged with existing data.');
                break;

            case self::C__OVERWRITE:
                $this->m_log->notice('Export data will overwrite existing data.');
                break;

            case self::C__USE_IDS:
                $this->m_log->notice('Export data will update existing data by identifiers.');
                break;

            default:
                throw new isys_exception_cmdb('Import mode is not supported.');
                break;
        }

        $this->m_mode = $p_mode;

        return $this;
    }

    /**
     * Sets mode for empty fields.
     *
     * @param  integer $p_mode
     */
    public function set_empty_fields_mode($p_mode)
    {
        assert(is_int($p_mode));
        switch ($p_mode) {
            case self::C__KEEP:
                $this->m_log->info('Ignore empty fields');
                break;

            case self::C__CLEAR:
                $this->m_log->info('Clear fields');
                break;

            default:
                throw new isys_exception_cmdb('Mode for empty fields is not supported.');
                break;
        }

        $this->m_empty_fields = $p_mode;
    }

    /**
     * Sets mode for multi-valued categories.
     *
     * @param  integer $p_mode
     */
    public function set_multivalue_categories_mode($p_mode)
    {
        assert(is_int($p_mode));
        switch ($p_mode) {
            case self::C__UNTOUCHED:
                $this->m_log->info('Keep category entries untouched');
                break;

            case self::C__APPEND:
                $this->m_log->info('Add category entries');
                break;

            case self::C__OVERWRITE:
                $this->m_log->info('Delete existing category entries before adding new ones');
                break;

            case self::C__UPDATE:
                $this->m_log->info('Update existing category entries');
                break;

            case self::C__UPDATE_ADD:
                $this->m_log->info('Update existing category entries and add new ones');
                break;

            default:
                throw new isys_exception_cmdb('Mode for multi-valued categories is not supported.');
                break;
        }

        $this->m_multivalue_categories = $p_mode;
    }

    /**
     * Disconnect Summary and Index Signals
     *
     * @throws Exception
     */
    public function disconnectSignals()
    {
        /**
         * Disconnect the onAfterCategoryEntrySave event to not always reindex the object in every category
         * This is extremely important!
         *
         * An Index is done for all objects at the end of the request, if enabled via parameter.
         */
        SearchIndexSignals::instance()
            ->disconnectOnAfterCategoryEntrySave();
    }

    /**
     * Fire Summary Signal and SeachIndex Signal after importing all objects and categories
     */
    public function fireDisconnectedSignals()
    {
        if (!empty($this->m_cat_info)) {
            $categoryIds = array_keys($this->m_cat_info);
            $globalCategories = $specificCategories = [];
            foreach ($categoryIds as $categoryIdString) {
                [$categoryType, $categoryId] = explode('_', $categoryIdString);
                switch ((int) $categoryType) {
                    case C__CMDB__CATEGORY__TYPE_CUSTOM:
                        $globalCategories[] = $this->m_all_categories[$categoryType][$categoryId]['const'];
                        break;
                    case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                        $specificCategories[] = $this->m_all_categories[$categoryType][$categoryId]['const'];
                        break;
                    case C__CMDB__CATEGORY__TYPE_GLOBAL:
                    default:
                        $globalCategories[] = $this->m_all_categories[$categoryType][$categoryId]['const'];
                        break;
                }
            }

            $this->m_log->notice('Index Signals triggered');

            SearchIndexSignals::instance()->onPostImport($this->importStartTime, $globalCategories, $specificCategories);
        }
    }

    /**
     * Imports CMDB data.
     *
     * @return  $this
     */
    public function import()
    {
        Context::instance()
            ->setContextTechnical(Context::CONTEXT_IMPORT_XML)
            ->setGroup(Context::CONTEXT_GROUP_IMPORT)
            ->setContextCustomer(Context::CONTEXT_IMPORT_XML);

        global $g_mandator_info;

        /*
         * NOTICE: This code is a little bit 'heavy', so we tried to comment it
         * whereever it's necessary to understand, what happens here. If you
         * want to change ANYTHING, please be aware of a consistent data
         * structure that is used whenever something is imported/exported/
         * templated/duplicated/whatever.
         */
        assert(isset($this->m_prepared) && is_array($this->m_prepared));

        $this->m_log->debug('Importing data to database...');
        $this->m_log->flush_verbosity(true, false);

        // Check import mode:
        if (!isset($this->m_mode)) {
            // Try to set it automaticly.
            $this->m_log->debug('Import mode is not set.');
            if ($this->m_mandator[C__DATA__VALUE] == $g_mandator_info['isys_mandator__title'] && $this->m_mandator['id'] == $g_mandator_info['isys_mandator__id']) {
                $this->m_log->debug('The export source is the same like this system.');
                $this->set_mode(self::C__USE_IDS);
            } else {
                $this->m_log->debug('The export source is another one then this system.');
                $this->set_mode(self::C__MERGE);
            }
        }

        // Check mode for empty fields.
        if (!isset($this->m_empty_fields)) {
            $this->m_log->debug('Mode for empty fields is not set.');
            $this->set_empty_fields_mode(self::C__KEEP);
        }

        // Check mode for multi-valued categories.
        if (!isset($this->m_multivalue_categories)) {
            $this->m_log->debug('Mode for empty fields is not set.');
            $this->set_multivalue_categories_mode(self::C__UPDATE_ADD);
        }

        // Initialize module logbook.
        if (!isset($this->m_mod_logbook) || $this->m_mod_logbook === null) {
            $this->m_mod_logbook = isys_factory::get_instance('isys_module_logbook', $this->m_db);
        }

        // Set installed modules (Nagios, ...):
        if (isys_module_manager::instance()->is_active('nagios')) {
            $this->m_installed_modules['nagios'] = true;
        }

        // Initialize log book.
        $this->m_event_manager = isys_event_manager::getInstance();

        // Initiate signals.
        $this->m_signals = isys_component_signalcollection::get_instance();

        // Print some main information:
        $this->m_log->notice('Tenant: ' . $this->m_mandator[C__DATA__VALUE]);
        $this->m_log->notice('Version: ' . $this->m_version);
        $this->m_log->notice('Exported at: ' . date($this->m_date_format, $this->m_scantime));
        $this->m_log->notice('Export-Type: ' . $this->m_type);

        // Initialize variables to cache some information.
        $this->importCounters = [
            'created'            => 0,
            'updated'            => 0,
            'category_updated'   => 0,
            'category_skipped'   => 0,
            'changesInObjectIDs' => []
        ];

        $this->m_found_objects = [];
        $this->m_object_type_ids = [];
        $this->m_object_states = [];

        // We need all existing object types from database to verify import.
        if (empty($this->m_object_types)) {
            $l_obj_types = $this->m_cmdb_dao->get_object_type();

            foreach ($l_obj_types as $l_obj_type_id => $l_obj_type_data) {
                $this->m_object_types[$l_obj_type_id] = $l_obj_type_data['isys_obj_type__const'];
            }
        }

        //$this->m_log->debug('First, create referenced objects without any exported data (dummies).');
        $this->import_dummy_objects();

        //$this->m_log->debug('Second, only objects without their categories are imported.');

        $this->import_objects();

        //$this->m_log->debug('Third, after importing only objects also import their categories.');
        $this->m_log->flush_verbosity(true, false);

        //$this->m_db->query("SET FOREIGN_KEY_CHECKS = 0");
        $this->import_categories();
        //$this->m_db->query("SET FOREIGN_KEY_CHECKS = 1");

        $this->m_log->notice('Import was successful.');
        $this->m_log->info(sprintf(
            '%s object(s) created, %s updated, %s categories updated and %s categories skipped.',
            $this->importCounters['created'],
            $this->importCounters['updated'],
            $this->importCounters['category_updated'],
            $this->importCounters['category_skipped']
        ));
        //$this->m_log->flush_verbosity(true, false);

        if (is_countable($this->m_logbook_entries) && count($this->m_logbook_entries) > 0) {
            $this->m_log->debug('Writing ' . intval($this->m_logbook_entries) . ' logbook entries.');

            foreach ($this->m_logbook_entries as $l_entry) {
                $this->m_event_manager->triggerImportEvent(
                    $this->get_logbook_event(),
                    'Import',
                    $l_entry['object_id'],
                    $l_entry['object_type_id'],
                    $l_entry['category'],
                    $l_entry['changes'],
                    (($l_entry['comment']) ?: null),
                    null,
                    null,
                    $this->m_event_manager->get_import_id(),
                    $l_entry['count_changes'],
                    $this->m_logbook_source
                );
            }

            unset($this->m_logbook_entries);
        }

        // Update the quickinfo cache
        $objectsInImport = array_keys($this->m_prepared);
        $l_changed_objects = $this->getChangesInObjectIds();
        $countOfChangedObjects = is_countable($l_changed_objects) ? count($l_changed_objects) : 0;
        $this->m_log->info('Updating changed_by attribute and deleting quickinfo cache for ' . $countOfChangedObjects . ' object(s)');
        $this->m_log->debug(' -> ' . implode(', ', $l_changed_objects));

        // Empty quickinfo cache for all imported objects
        $this->m_cmdb_dao->emptyCacheQinfo($objectsInImport);

        if (!empty($l_changed_objects) && $this->m_options['update-object-changed']) {
            foreach ($l_changed_objects as $l_obj_id) {
                $this->m_cmdb_dao->object_changed($l_obj_id, $this->m_import_started_by);
            }
        }

        return true;
    }

    /**
     * Resets all data and information about an import. Useful to 'recycle' this class instance.
     *
     * @return isys_import_handler_cmdb
     */
    public function reset()
    {
        isys_import_handler_cmdb::$m_changed = false;
        $this->m_category_data_ids = null;
        $this->m_category_ids = null;
        $this->m_data = [];
        $this->m_info = [];
        $this->m_mandator = [];
        $this->m_object_ids = [];
        $this->m_object_type_ids = [];
        $this->m_prepared = [];
        $this->m_property_ids = [];
        $this->m_scantime = null;
        $this->m_type = null;
        $this->m_version;
        $this->m_hostname;
        $this->m_cached_objects = [];
        $this->m_cached_sysids = [];
        $this->m_cached_title_constants = [];

        return $this;
    }

    /**
     * Gets object type identifiers. Useful after finishing an import.
     *
     * @return  array
     */
    public function get_object_type_ids()
    {
        return $this->m_object_type_ids;
    }

    /**
     * Gets object identifiers. Useful after finishing an import.
     *
     * @return  array
     */
    public function get_object_ids()
    {
        return $this->m_object_ids;
    }

    /**
     * Gets category identifiers. Useful after finishing an import.
     *
     * @return  array
     */
    public function get_category_ids()
    {
        return $this->m_category_ids;
    }

    /**
     * Gets category data identifiers. Useful after finishing an import.
     *
     * @return  array
     */
    public function get_category_data_ids()
    {
        return $this->m_category_data_ids;
    }

    /**
     * Gets property identifiers. Useful after finishing an import.
     *
     * @return  array
     */
    public function get_property_ids()
    {
        return $this->m_property_ids;
    }

    /**
     * Gets list of categories which will be kept untouched.
     *
     * @return  array
     */
    public function get_ignored_categories()
    {
        return $this->m_ignored_categories;
    }

    /**
     * Sets list of categories which will be kept untouched.
     *
     * @param  array $p_ignored_categories Associative array of category type identifiers as keys and an array of category identifiers as values
     */
    public function set_ignored_categories($p_ignored_categories)
    {
        assert(is_array($p_ignored_categories));
        foreach ($p_ignored_categories as $l_category_type_id => $l_category_ids) {
            assert(is_int($l_category_type_id));
            assert(is_array($l_category_ids));

            foreach ($l_category_ids as $l_category_id) {
                assert(is_int($l_category_id));
            }
        }

        $this->m_ignored_categories = $p_ignored_categories;
    }

    /**
     * Add a custom/specific/global category
     * to ignore list
     *
     * @param type $p_categoryID ID or Constant of the Category
     */
    public function ignore_global($p_categoryID)
    {
        $this->ignore_category(C__CMDB__CATEGORY__TYPE_GLOBAL, $p_categoryID);

        return $this;
    }

    /**
     * Add a custom/specific/global category
     * to ignore list
     *
     * @param type $p_categoryID ID or Constant of the Category
     */
    public function ignore_specific($p_categoryID)
    {
        $this->ignore_category(C__CMDB__CATEGORY__TYPE_SPECIFIC, $p_categoryID);

        return $this;
    }

    /**
     * Add a custom/specific/global category
     * to ignore list
     *
     * @param type $p_categoryID ID or Constant of the Category
     */
    public function ignore_custom($p_categoryID)
    {
        $this->ignore_category(C__CMDB__CATEGORY__TYPE_CUSTOM, $p_categoryID);

        return $this;
    }

    /**
     * Adds logbook entries to the member variable
     *
     * @param $p_logbook_entries
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_logbook_entries($p_logbook_entries)
    {
        if (is_countable($this->m_logbook_entries) && count($this->m_logbook_entries)) {
            $this->m_logbook_entries = array_merge($this->m_logbook_entries, $p_logbook_entries);
        } else {
            $this->m_logbook_entries = $p_logbook_entries;
        }

        return $this;
    }

    public function __destruct()
    {
        // Location fix should be called in the destructor.
        try {
            if ($this->m_trigger_location_fix) {
                // Trigger location fix only if necessary
                $l_dao = new isys_cmdb_dao_location(isys_application::instance()->database);
                $l_dao->_location_fix();
            }

            /**
             * Clear cache as well
             */
            isys_cache::keyvalue()
                ->flush();
        } catch (Exception $e) {
            $this->m_log->error('Location could not be regenerated with message: ' . $e->getMessage());
        }

        // Clear all found "auth-*" cache-files. So that it is not necessary to trigger it manually in Cache/Database
        if (is_countable($this->m_object_ids) && count($this->m_object_ids) > 0) {
            try {
                $l_cache_files = isys_caching::find('auth-*');
                array_map(function ($l_cache) {
                    $l_cache->clear();
                }, $l_cache_files);
            } catch (Exception $e) {
                $this->m_log->warning(sprintf('Could not clear cache files for %sauth-* with message: ' . $e->getMessage(), isys_glob_get_temp_dir()));
            }
        }
    }

    /**
     * Parses recursivly properties. Used for $this->parse().
     *
     * @param   mixed  $p_data SimpleXML objects and/or arrays.
     * @param          $p_key
     * @param          $p_value
     *
     * @return  array
     */
    private function parse_properties($p_data, $p_key, $p_value)
    {
        $this->m_log->debug('Parsing properties...');

        $l_result = [
            C__DATA__TAG => $p_key
        ];

        switch (gettype($p_value)) {
            case 'object':
                assert($p_value instanceof isys_library_xml);

                $l_arr = (array)$p_value;

                if (isset($l_arr[0])) {
                    $l_result[C__DATA__VALUE] = $l_arr[0];
                } else {
                    foreach ($p_value as $l_sub_key => $l_sub_value) {
                        /*
                         * @deleteme WARNING! This is a bug fix, but it could break the whole import!
                         * Original:
                         *     $l_result = array_merge($l_result, $p_value->$l_sub_key->get_attributes());
                         *     $l_sub = $this->parse_properties($p_value, $l_sub_key, $l_sub_value);
                         *     $l_result[C__DATA__VALUE][] = $l_sub;
                         * Fix:
                         */
                        $l_sub = $this->parse_properties($p_value, $l_sub_key, $l_sub_value);
                        $l_sub = array_merge($l_sub_value->get_attributes(), $l_sub);
                        $l_result[C__DATA__VALUE][] = $l_sub;
                    }
                }

                // Warning: See below...
                $l_result = array_merge($l_result, $p_value->get_attributes());
                break;

            case 'array':
                foreach ($p_value as $l_value) {
                    $l_sub = $this->parse_properties($l_value, null, $l_value);
                    $l_sub[C__DATA__TAG] = $l_sub[C__DATA__TITLE];
                    $l_result[C__DATA__VALUE][] = $l_sub;
                }
                break;

            case 'string':
                $l_result[C__DATA__VALUE] = $p_value;
                $l_result = array_merge($l_result, $p_data->$p_key->get_attributes());
                break;

            default:
                $this->m_log->warning(sprintf('Unknown property type %s.', gettype($p_value)));
                break;
        }

        // Warning: There is the possibility that an attribute
        // has the same name like a constant, e. g. 'value'
        // (C__DATA__VALUE), so this array field will
        // be overwritten! But... that's okay, because when
        // this happens, attributes like this are more
        // important. Otherwise... just change the constant ;-)

        return $l_result;
    }

    /**
     * Finds references in the raw data structure. Used by $this->prepare().
     *
     * @param int   $p_object_id        Object identifier
     * @param int   $p_category_type_id Category type identifier
     * @param int   $p_category_id      Category identifier
     * @param int   $p_category_data_id Category data identifier
     * @param int   $p_property_id      Property identifier
     * @param array $p_data_arr         (optional) Defaults to null.
     *
     * @return array Found references
     */
    private function find_references($p_object_id, $p_category_type_id, $p_category_id, $p_category_data_id, $p_property_id, $p_data_arr = null)
    {
        $l_result = [];

        // Handle the recursion:
        $l_property = $p_data_arr;

        $l_value = null;

        if ($l_property[C__DATA__VALUE]) {
            $l_value = $l_property[C__DATA__VALUE];
        } else {
            $l_value = $l_property[C__DATA__TITLE];
        }

        $l_title = $l_property[C__DATA__TITLE];

        switch (gettype($l_value)) {
            case 'string':
                $l_title = $l_value;
                break;
            case 'array':
                foreach ($l_value as $l_sub_property) {
                    // Next step:
                    $l_new_result = $this->find_references($p_object_id, $p_category_type_id, $p_category_id, $p_category_data_id, $p_property_id, $l_sub_property);
                    $l_result = array_merge($l_result, $l_new_result);
                }
                break;
        }

        $l_new_result = [];

        if (!isset($l_property[C__DATA__TITLE])) {
            $this->m_log->warning(sprintf(
                'Buggy property found! Tag: %s; object ID: %s; category type ID: %s; category ID: %s',
                $p_property_id,
                $p_object_id,
                $p_category_type_id,
                $p_category_id
            ));
        } elseif (isset($l_property['id']) && isset($l_property['type']) && isset($l_property['sysid'])) {
            // Reference to object:
            $l_new_result['id'] = $l_property['id'];
            $l_new_result['type'] = $l_property['type'];
            $l_new_result['sysid'] = $l_property['sysid'];
            if (isset($l_property['ref_id'])) {
                $l_new_result['ref_id'] = $l_property['ref_id'];
            }
            if (isset($l_property['ref_type'])) {
                $l_new_result['ref_type'] = $l_property['ref_type'];
            }
            if (isset($l_property['ref_title'])) {
                $l_new_result['ref_title'] = $l_property['ref_title'];
            }

            $l_new_result[C__DATA__VALUE] = $l_title;
            $this->m_info[self::C__OBJECTS][$l_property['id']][self::C__PROPERTIES] = $l_property;
            $this->m_log->debug(sprintf(
                'Reference to object %s with SYSID %s found. Its identifier is %s and its type is %s.',
                $l_property[C__DATA__VALUE],
                $l_property['sysid'],
                $l_property['id'],
                $l_property['type']
            ));
        } elseif (isset($l_property['id']) && isset($l_property['type'])) {
            // Reference to category:
            $l_new_result['id'] = $l_property['id'];
            $l_new_result['type'] = $l_property['type'];
            $l_new_result[C__DATA__VALUE] = $l_title;
            $this->m_log->debug(sprintf(
                'Reference to category %s found. Its identifier is %s and its type is %s.',
                $l_property[C__DATA__VALUE],
                $l_property['id'],
                $l_property['type']
            ));
        } elseif (isset($l_property['id']) && isset($l_property['const'])) {
            // Reference to other property content:
            $l_new_result['id'] = $l_property['id'];
            $l_new_result['const'] = $l_property['const'];
            $l_new_result[C__DATA__VALUE] = $l_title;
            $this->m_log->debug(sprintf(
                'Reference to another property content %s with const %s found. Its identifier is %s.',
                $l_property[C__DATA__VALUE],
                $l_property['const'],
                $l_property['id']
            ));
        }

        if (count($l_new_result) > 0) {
            $l_result[] = $l_new_result;
        }

        return $l_result;
    }

    /**
     * Looks for a referenced object.
     *
     * @param   integer $p_current_object_id Current object identifier, so it will be ignored if found.
     * @param   integer $p_object_id         Object identifier
     * @param   integer $p_type              Object type
     * @param   string  $p_sysid             SYSID
     * @param   string  $p_value             Object title
     *
     * @return  boolean  Returns true if object is found, otherwise false.
     */
    private function fetch_object_reference($p_current_object_id, $p_object_id, $p_type, $p_sysid, $p_value)
    {
        $this->m_log->debug(sprintf('Look for object %s of type %s with sysid %s and title %s...', $p_object_id, $p_type, $p_sysid, $p_value, $p_current_object_id));

        // Iterate through objects:
        foreach ($this->m_data as $l_object_id => $l_object_values) {
            if ($l_object_id === $p_current_object_id) {
                continue;
            }
            // Okay, we really don't need so much information, but let's get through:
            if ($l_object_id == $p_object_id && $l_object_values['type']['const'] == $p_type && $l_object_values['sysid'] == $p_sysid &&
                $l_object_values[C__DATA__TITLE] == $p_value) {
                $this->m_log->debug('Match found.');

                if (!isset($this->m_info[self::C__OBJECTS][$l_object_id][self::C__DUMMY])) {
                    $this->m_info[self::C__OBJECTS][$l_object_id][self::C__DUMMY] = false;
                }

                return true;
            }
        }

        if (!isset($this->m_info[self::C__OBJECTS][$l_object_id][self::C__DUMMY])) {
            $this->m_info[self::C__OBJECTS][$l_object_id][self::C__DUMMY] = true;
        }

        return false;
    }

    private function fetch_category_reference($p_object_id, $p_category_data_id, $p_property_constant, $p_property_value)
    {
        $this->m_log->debug(sprintf(
            'Look for object %s\'s property type %s with id %s and value %s...',
            $p_object_id,
            $p_property_constant,
            $p_category_data_id,
            $p_property_value
        ));

        // Set constant to int if it is a string:
        if (!is_numeric($p_property_constant)) {
            $p_property_constant = constant($p_property_constant);
        }

        $l_result = false;
        // Iterate through objects:
        foreach ($this->m_data as $l_object_id => $l_object_values) {
            if ($l_object_id !== $p_object_id) {
                // Objects should ignore themselves:
                continue;
            }
            // Iterate trough category types:
            foreach ($l_object_values[self::C__CATEGORY_TYPES] as $l_category_type_id => $l_categories) {
                // Iterate through categories:
                foreach ($l_categories as $l_category_id => $l_category_values) {

                    // Check whether correct category:
                    if ($l_category_id != $p_property_constant) {
                        continue;
                    }

                    // Iterate through category data:
                    foreach ($l_category_values[self::C__CATEGORY_ENTITIES] as $l_category_data_id => $l_category_data_values) {
                        if ($l_category_data_id != $p_category_data_id) {
                            continue;
                        }
                        // Skip empty category data:
                        if (!is_array($l_category_data_values[self::C__PROPERTIES])) {
                            continue;
                        }

                        $l_found_value = false;

                        // Check if reference is only on the category data id
                        if ($l_category_data_id == $p_property_value) {
                            $l_found_value = true;
                        } else {
                            // Iterate through properties:
                            foreach ($l_category_data_values[self::C__PROPERTIES] as $l_property) {
                                // Recursion:
                                //list($l_const, $l_value) = $this->fetch_categories_in_properties($l_property);
                                $l_arr = $this->fetch_categories_in_properties($l_property);

                                if ($l_arr[C__DATA__VALUE] == $p_property_value) {
                                    $l_found_value = true;
                                    break;
                                }
                            } // foreach property
                        }

                        if ($l_found_value === true) {
                            $this->m_log->debug(sprintf('Match found: category %s in category type %s', $l_category_id, $l_category_type_id));
                            $l_result[] = [
                                'category_type_id' => $l_category_type_id,
                                'category_id'      => $l_category_id
                            ];
                        }
                    } // foreach category data
                } // foreach category
            } // foreach category type
        } // foreach object

        return $l_result;
    }

    /**
     * Looks recursivly for referenced categories in a property.
     *
     * @param   array $p_property Property
     *
     * @return  array  Returns array of strings or nulls with keys 'const' and 'value'.
     */
    private function fetch_categories_in_properties($p_property)
    {
        $l_result = [C__DATA__VALUE => null];

        if (array_key_exists('ref_title', $p_property)) {
            $l_result[C__DATA__VALUE] = $p_property['ref_title'];
        } else {
            switch (gettype($p_property[C__DATA__VALUE])) {
                case 'string':
                    $l_result[C__DATA__VALUE] = $p_property[C__DATA__VALUE];
                    break;

                case 'array':
                    foreach ($p_property[C__DATA__VALUE] as $l_sub_property) {
                        // Recursion:
                        return $this->fetch_categories_in_properties($l_sub_property);
                    }
                    break;
            }
        }

        return $l_result;
    }

    /**
     * Sorts objects. Used for uksort().
     *
     * @param  integer $p_a_key Array key
     * @param  integer $p_b_key
     *
     * @return integer
     */
    private function sort_objects($p_a_key, $p_b_key)
    {
        $l_a_prio = $this->m_prepared[$p_a_key][self::C__PRIORITY];
        $l_b_prio = $this->m_prepared[$p_b_key][self::C__PRIORITY];

        if ($l_a_prio == $l_b_prio) {
            return 0;
        } else {
            return ($l_a_prio < $l_b_prio) ? 1 : -1;
        }
    }

    /**
     * Sorts categories for each object. Used for uksort().
     *
     * @param   integer $p_a_key Array key
     * @param   integer $p_b_key
     *
     * @return  integer
     */
    private function sort_categories($p_a_key, $p_b_key)
    {
        $l_a_category_type_id = null;
        $l_a_category_id = null;
        $l_b_category_type_id = null;
        $l_b_category_id = null;
        [$l_a_category_type_id, $l_a_category_id] = explode('_', $p_a_key);
        [$l_b_category_type_id, $l_b_category_id] = explode('_', $p_b_key);
        $l_a_prio = $this->m_prepared[$this->m_info['object_id']][self::C__CATEGORIES][$l_a_category_type_id . '_' . $l_a_category_id][self::C__PRIORITY];
        $l_b_prio = $this->m_prepared[$this->m_info['object_id']][self::C__CATEGORIES][$l_b_category_type_id . '_' . $l_b_category_id][self::C__PRIORITY];
        if ($l_a_prio == $l_b_prio) {
            return 0;
        } else {
            return ($l_a_prio < $l_b_prio) ? 1 : -1;
        }
    }

    /**
     * Imports referenced objects.
     */
    private function import_dummy_objects()
    {
        // Iterate through objects:
        if (is_array($this->m_info[self::C__OBJECTS])) {
            foreach ($this->m_info[self::C__OBJECTS] as $l_object_id => $l_object_info) {
                if ($l_object_info[self::C__DUMMY] === false || empty($l_object_id)) {
                    // Handle only dummies:
                    continue;
                }
                $this->m_log->debug(sprintf(
                    'Handle dummy object %s: %s (%s)',
                    $l_object_id,
                    $l_object_info[self::C__PROPERTIES][C__DATA__VALUE],
                    $l_object_info[self::C__PROPERTIES]['sysid']
                ));

                // Preparation:
                $this->m_object_ids[$l_object_id] = $l_object_id;

                // Look for object by its identifier in database and compare
                // result with this object:
                if (!isset($this->m_cached_objects[$l_object_id])) {
                    $this->m_cached_objects[$l_object_id] = $this->m_cmdb_dao->get_object_by_id($l_object_id);
                }

                $this->m_found_objects[$l_object_id] = $this->compare_objects(
                    $this->m_cached_objects[$l_object_id],
                    $l_object_info[self::C__PROPERTIES],
                    $this->m_object_ids[$l_object_id]
                );

                // First try:
                if ($this->m_found_objects[$l_object_id] === self::C__COMPARISON__DIFFERENT) {
                    $this->m_log->debug('Looks like current object is unknown, but we also take a look at the SYSID.');
                    // Look for object by its sysid in database and compare result
                    // with this object:
                    $l_sysid = $l_object_info[self::C__PROPERTIES]['sysid'];
                    if (!array_key_exists($l_sysid, $this->m_cached_sysids)) {
                        $this->m_cached_sysids[$l_sysid] = $this->m_cmdb_dao->get_obj_id_by_sysid($l_sysid);
                    }

                    if ($this->m_cached_sysids[$l_sysid]) {
                        if (!array_key_exists($this->m_cached_sysids[$l_sysid], $this->m_cached_objects)) {
                            $this->m_cached_objects[$this->m_cached_sysids[$l_sysid]] = $this->m_cmdb_dao->get_object_by_id($this->m_cached_sysids[$l_sysid]);
                        }
                    }
                    $this->m_found_objects[$l_object_id] = $this->compare_objects(
                        $this->m_cached_objects[$this->m_cached_sysids[$l_sysid]],
                        $l_object_info[self::C__PROPERTIES],
                        $this->m_object_ids[$l_object_id]
                    );
                }

                // Second try:
                if ($this->m_found_objects[$l_object_id] === self::C__COMPARISON__DIFFERENT) {
                    $this->m_log->debug('Looks like current object is unknown, but we also take a look at the Title and Type.');

                    // Look for object by its sysid in database and compare result
                    // with this object:
                    if (defined($l_object_info[self::C__PROPERTIES]['type'])) {
                        $l_title = $l_object_info[self::C__PROPERTIES][C__DATA__VALUE];
                        $l_constant = constant($l_object_info[self::C__PROPERTIES]['type']);
                        $l_title_constant = $l_title . '###' . $l_constant;
                        if (!array_key_exists($l_title_constant, $this->m_cached_title_constants)) {
                            $this->m_cached_title_constants[$l_title_constant] = $this->m_cmdb_dao->get_obj_id_by_title($l_title, $l_constant);
                        }
                        if ($this->m_cached_title_constants[$l_title_constant]) {
                            if (!array_key_exists($this->m_cached_title_constants[$l_title_constant], $this->m_cached_objects)) {
                                $this->m_cached_objects[$this->m_cached_title_constants[$l_title_constant]] = $this->m_cmdb_dao->get_object_by_id($this->m_cached_title_constants[$l_title_constant]);
                            }
                        }
                        $this->m_found_objects[$l_object_id] = $this->compare_objects(
                            $this->m_cached_objects[$this->m_cached_title_constants[$l_title_constant]],
                            $l_object_info[self::C__PROPERTIES],
                            $this->m_object_ids[$l_object_id]
                        );
                    }
                }

                // Next try:
                if ($this->m_found_objects[$l_object_id] === self::C__COMPARISON__SAME) {
                    $this->m_log->debug('  Match found in database.');
                    $this->m_object_type_ids[$l_object_id] = $l_object_info[self::C__PROPERTIES]['type'];
                } elseif ($this->m_found_objects[$l_object_id] === self::C__COMPARISON__PARTLY) {
                    $this->m_log->debug('  Match found in database, but it\'s not exactly the same. Updating.');
                    $l_update_title = $l_object_info[self::C__PROPERTIES][C__DATA__TITLE];
                    if (!is_array($l_object_info[self::C__PROPERTIES][C__DATA__VALUE])) {
                        $l_update_title = $l_object_info[self::C__PROPERTIES][C__DATA__VALUE];
                    }
                    try {
                        $l_status = $this->m_cmdb_dao->update_object(
                            $this->m_object_ids[$l_object_id],
                            null,
                            $l_update_title,
                            null,
                            $l_object_info[self::C__PROPERTIES]['sysid']
                        );
                        if ($l_status === false) {
                            $this->m_log->error('Failed to update database. Aborting.');

                            return false;
                        }
                    } catch (isys_exception_cmdb $e) {
                        throw $e;
                    } //try/catch
                    // Keep the object type in mind:
                    $this->m_object_type_ids[$l_object_id] = $l_object_info[self::C__PROPERTIES]['type'];
                    // Log:
                    $this->m_event_manager->triggerImportEvent(
                        'C__LOGBOOK_EVENT__OBJECT_CHANGED',
                        'CMDB-Import',
                        $this->m_object_ids[$l_object_id],
                        constant($this->m_object_type_ids[$l_object_id]),
                        $l_update_title,
                        null,
                        null,
                        null,
                        null,
                        null,
                        0,
                        $this->m_logbook_source
                    );

                    // Reset cache completely:
                    $this->m_cached_objects = [];
                    $this->m_cached_sysids = [];
                    $this->m_cached_title_constants = [];

                    $this->importCounters['updated']++;
                } elseif ($this->m_found_objects[$l_object_id] === self::C__COMPARISON__DIFFERENT) {
                    $this->m_log->debug('  No match found in database.');
                    $this->m_log->debug(sprintf('  Check whether object type %s exists.', $l_object_info[self::C__PROPERTIES]['type']));

                    if (is_numeric($l_object_info[self::C__PROPERTIES]['type_id']) && isset($this->m_object_types[$l_object_info[self::C__PROPERTIES]['type_id']])) {
                        $this->m_object_type_ids[$l_object_id] = $this->m_object_types[$l_object_info[self::C__PROPERTIES]['type_id']];
                        $this->m_log->debug('  Object type found: ' . $this->m_object_type_ids[$l_object_id]);
                    } elseif (in_array($l_object_info[self::C__PROPERTIES]['type'], $this->m_object_types)) {
                        $this->m_log->debug('  Object type already exists.');
                        $this->m_object_type_ids[$l_object_id] = $l_object_info[self::C__PROPERTIES]['type'];
                    } else {
                        $this->m_log->debug('  Object type is unknown. A dummy will be now created.');
                        $l_new_obj_type_id = $this->m_cmdb_dao->insert_new_objtype(null, null, $l_object_info[self::C__PROPERTIES]['type']);
                        $l_new_obj_type = $this->m_cmdb_dao->get_objtype($l_new_obj_type_id)
                            ->__to_array();
                        $this->m_object_type_ids[$l_object_id] = $l_new_obj_type['isys_obj_type__const'];
                        $this->m_object_types[$l_new_obj_type_id] = $l_new_obj_type['isys_obj_type__const'];
                        if (!isset($this->m_object_type_ids[$l_object_id])) {
                            throw new isys_exception_general(sprintf('  Failed to create new object type %s.', $l_object_info[self::C__PROPERTIES]['type']));
                        }
                        $this->m_log->debug(sprintf('  New object type %s created as a dummy.', $l_object_info[self::C__PROPERTIES]['type']));
                        // Log:
                        $this->m_event_manager->triggerImportEvent(
                            'C__LOGBOOK_EVENT__OBJECTTYPE_CREATED',
                            'CMDB-Import',
                            $this->m_object_ids[$l_object_id],
                            $l_new_obj_type_id,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            0,
                            $this->m_logbook_source
                        );
                    }

                    $this->m_log->debug('Now it\'s time to create new object.');
                    $l_objtype = ((!isset($l_new_obj_type_id)) ? constant($this->m_object_type_ids[$l_object_id]) : $l_new_obj_type_id);

                    $this->m_object_ids[$l_object_id] = $this->m_cmdb_dao->insert_new_obj(
                        $l_objtype,
                        false,
                        ((!isset($l_object_info[self::C__PROPERTIES][C__DATA__VALUE])) ? $l_object_info[self::C__PROPERTIES][C__DATA__TITLE] : $l_object_info[self::C__PROPERTIES][C__DATA__VALUE]),
                        $l_object_info[self::C__PROPERTIES]['sysid'],
                        C__RECORD_STATUS__NORMAL,
                        null,
                        null,
                        true,
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        defined_or_default('C__CMDB_STATUS__IN_OPERATION')
                    );
                    $this->m_log->info('Dummy object "' . $l_object_info[self::C__PROPERTIES][C__DATA__VALUE] . '" [' . $this->m_object_ids[$l_object_id] . '] created.');
                    // Log:
                    $this->m_event_manager->triggerImportEvent(
                        'C__LOGBOOK_EVENT__OBJECT_CREATED',
                        "CMDB-Import: \n" . @$l_object_info['properties']['tag'] . ': ' . @$l_object_info['properties']['value'],
                        $this->m_object_ids[$l_object_id],
                        $l_objtype,
                        null,
                        serialize([
                            'isys_cmdb_dao_category_g_global::title' => [
                                'from' => '',
                                'to'   => $l_object_info['properties']['value']
                            ]
                        ]),
                        null,
                        null,
                        $l_object_info['properties']['value'],
                        null,
                        1,
                        $this->m_logbook_source
                    );
                    $this->importCounters['created']++;

                    unset($l_new_obj_type_id);
                } //if comparison
            } //foreach dummy object
        }

        // We don't need $m_info any more:
        unset($this->m_info);

        return $this;
    }

    /**
     * @throws Exception
     * @throws isys_exception_cmdb
     * @throws isys_exception_general
     */
    private function import_objects()
    {
        // Iterate through objects:
        foreach ($this->m_prepared as $l_object_id => $l_object_values) {
            $this->m_log->info(sprintf('Importing object #%s: %s (%s)', $l_object_id, $l_object_values[C__DATA__TITLE], $l_object_values['sysid']));

            // Preparation:
            $this->m_object_ids[$l_object_id] = $l_object_id;
            $l_object_title = trim(($this->get_replaced_title($l_object_id)) ? $this->get_replaced_title($l_object_id) : $l_object_values[C__DATA__TITLE]);

            // Object has already been created
            if ($this->m_object_created_by_others === true) {
                $this->m_object_type_ids[$l_object_id] = $l_object_values['type']['id'];
                $this->m_found_objects[$l_object_id] = self::C__COMPARISON__SAME;
                continue;
            }

            // Act based on import mode:
            if ($this->m_mode !== self::C__APPEND) {
                $l_object_status = null;

                // Look for object by its identifier in database and compare result
                // with this object:
                if (!array_key_exists($l_object_id, $this->m_cached_objects)) {
                    $this->m_cached_objects[$l_object_id] = $this->m_cmdb_dao->get_object_by_id($l_object_id);
                }
                $this->m_found_objects[$l_object_id] = $this->compare_objects(
                    $this->m_cached_objects[$l_object_id],
                    $l_object_values,
                    $this->m_object_ids[$l_object_id],
                    $l_object_status
                );

                // First try:
                if ($this->m_found_objects[$l_object_id] === self::C__COMPARISON__DIFFERENT) {
                    $this->m_log->debug('Looks like current object is unknown, but we also take a look at the SYSID.');
                    // Look for object by its sysid in database and compare result with this object:
                    $l_sysid = $l_object_values['sysid'];
                    if (!array_key_exists($l_sysid, $this->m_cached_sysids)) {
                        $this->m_cached_sysids[$l_sysid] = $this->m_cmdb_dao->get_obj_id_by_sysid($l_sysid);
                    }
                    if ($this->m_cached_sysids[$l_sysid]) {
                        if (!array_key_exists($this->m_cached_sysids[$l_sysid], $this->m_cached_objects)) {
                            $this->m_cached_objects[$this->m_cached_sysids[$l_sysid]] = $this->m_cmdb_dao->get_object_by_id($this->m_cached_sysids[$l_sysid]);
                        }
                    }
                    $this->m_found_objects[$l_object_id] = $this->compare_objects(
                        $this->m_cached_objects[$this->m_cached_sysids[$l_sysid]],
                        $l_object_values,
                        $this->m_object_ids[$l_object_id],
                        $l_object_status
                    );
                }

                // Second try:
                if ($this->m_found_objects[$l_object_id] === self::C__COMPARISON__DIFFERENT) {
                    $this->m_log->debug('Looks like current object is unknown, but we also take a look at the Object Title and Object Type.');

                    // Look for object by its sysid in database and compare result
                    // with this object:
                    $l_title = trim($l_object_values['title']);
                    if (defined($l_object_values['type']['const'])) {
                        $l_constant = constant($l_object_values['type']['const']);
                        $l_title_constant = $l_title . '###' . $l_constant;
                        if (!array_key_exists($l_title_constant, $this->m_cached_title_constants)) {
                            $this->m_cached_title_constants[$l_title_constant] = $this->m_cmdb_dao->get_obj_id_by_title($l_title, $l_constant);
                        }
                        if ($this->m_cached_title_constants[$l_title_constant]) {
                            if (!array_key_exists($this->m_cached_title_constants[$l_title_constant], $this->m_cached_objects)) {
                                $this->m_cached_objects[$this->m_cached_title_constants[$l_title_constant]] = $this->m_cmdb_dao->get_object_by_id($this->m_cached_title_constants[$l_title_constant]);
                            }
                        }
                        $this->m_found_objects[$l_object_id] = $this->compare_objects(
                            $this->m_cached_objects[$this->m_cached_title_constants[$l_title_constant]],
                            $l_object_values,
                            $this->m_object_ids[$l_object_id],
                            $l_object_status
                        );
                    }
                }

                // New try:
                if ($this->m_found_objects[$l_object_id] === self::C__COMPARISON__SAME) {
                    $this->m_log->debug('Match found in database.');
                    $this->m_object_type_ids[$l_object_id] = constant($l_object_values['type']['const']);
                    $this->m_object_states[$this->m_object_ids[$l_object_id]] = self::C__KEEP;
                } elseif ($this->m_found_objects[$l_object_id] === self::C__COMPARISON__PARTLY) {
                    $this->m_log->debug('Match found in database, but it\'s not exactly the same. Updating.');
                    $l_current_obj_data = [];

                    try {
                        if (is_array($l_object_values['categories']) && defined('C__CATG__GLOBAL') &&
                            array_key_exists(C__CMDB__CATEGORY__TYPE_GLOBAL . "_" . constant('C__CATG__GLOBAL'), $l_object_values['categories'])) {
                            $l_current_obj_data = $this->m_cmdb_dao->get_object_by_id($this->m_object_ids[$l_object_id])
                                ->get_row();
                        }

                        $l_status = $this->m_cmdb_dao->update_object(
                            $this->m_object_ids[$l_object_id],
                            null,
                            $l_object_title,
                            null,
                            $l_object_values['sysid'],
                            $l_object_status,
                            null,
                            $this->m_scantime,
                            $l_object_values['created'],
                            $l_object_values['created_by'],
                            $l_object_values['updated'],
                            $l_object_values['updated_by'],
                            $l_object_values['cmdb_status']
                        );

                        if ($l_status === false) {
                            $this->m_log->error('Failed to update database. Aborting.');

                            return false;
                        }
                    } catch (isys_exception_cmdb $e) {
                        throw $e;
                    }

                    $this->m_object_type_ids[$l_object_id] = $l_object_values['type']['const'];

                    // Log:
                    if (is_array($l_object_values['categories']) && defined('C__CATG__GLOBAL') && array_key_exists(C__CMDB__CATEGORY__TYPE_GLOBAL . "_" . constant('C__CATG__GLOBAL'), $l_object_values['categories'])) {
                        $l_serialized_changes = [];
                        $l_changes = 0;
                        if ($l_current_obj_data['isys_obj__title'] != $l_object_title) {
                            $l_serialized_changes['isys_cmdb_dao_category_g_global::title'] = [
                                'from' => $l_current_obj_data['isys_obj__title'],
                                'to'   => $l_object_title
                            ];
                            $l_changes++;
                        }
                        if (isys_application::instance()->container->get('language')
                                ->get($l_current_obj_data['isys_obj_type__title']) != $l_object_values['type']['value']) {
                            $l_serialized_changes['isys_cmdb_dao_category_g_global::type'] = [
                                'from' => isys_application::instance()->container->get('language')
                                    ->get($l_current_obj_data['isys_obj_type__title']),
                                'to'   => $l_object_values['type']['value']
                            ];
                            $l_changes++;
                        }
                        if ($l_current_obj_data['isys_obj__sysid'] != $l_object_values['sysid']) {
                            $l_serialized_changes['isys_cmdb_dao_category_g_global::sysid'] = [
                                'from' => $l_current_obj_data['isys_obj__sysid'],
                                'to'   => $l_object_values['sysid']
                            ];
                            $l_changes++;
                        }
                        if ($this->m_cmdb_dao->get_record_status_as_string($l_current_obj_data['isys_obj__status']) !=
                            $this->m_cmdb_dao->get_record_status_as_string($l_object_values['status'])) {
                            $l_serialized_changes['isys_cmdb_dao_category_g_global::status'] = [
                                'from' => $this->m_cmdb_dao->get_record_status_as_string($l_current_obj_data['isys_obj__status']),
                                'to'   => $this->m_cmdb_dao->get_record_status_as_string($l_object_values['status'])
                            ];
                            $l_changes++;
                        }

                        if ($l_changes > 0) {
                            $this->m_event_manager->triggerImportEvent(
                                'C__LOGBOOK_EVENT__OBJECT_CHANGED',
                                'CMDB-Import',
                                $this->m_object_ids[$l_object_id],
                                constant($this->m_object_type_ids[$l_object_id]),
                                $l_object_values['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . "_" . defined_or_default('C__CATG__GLOBAL')]['title'],
                                (is_array($l_serialized_changes) ? serialize($l_serialized_changes) : null),
                                null,
                                null,
                                $l_object_title,
                                null,
                                $l_changes,
                                $this->m_logbook_source
                            );
                        }
                    }

                    // Reset cache completely:
                    $this->m_cached_objects = [];
                    $this->m_cached_sysids = [];
                    $this->m_cached_title_constants = [];

                    $this->importCounters['updated']++;
                    $this->m_object_states[$this->m_object_ids[$l_object_id]] = self::C__UPDATE;
                } elseif ($this->m_found_objects[$l_object_id] === self::C__COMPARISON__DIFFERENT) {
                    $this->m_log->debug('No match found in database. Create new object in a moment.');
                    $this->m_log->debug(sprintf('Check whether object type %s exists.', $l_object_values['type']['const']));

                    if (in_array($l_object_values['type']['const'], $this->m_object_types)) {
                        $this->m_log->debug('Object type already exists.');
                        $this->m_object_type_ids[$l_object_id] = $l_object_values['type']['const'];
                    } else {
                        $this->m_log->debug('Object type is unknown. It will be now created.');
                        $l_status = $this->m_cmdb_dao->insert_new_objtype(
                            $l_object_values['type']['group'],
                            $l_object_values['type']['title_lang'],
                            $l_object_values['type']['const'],
                            "1",
                            "0",
                            null,
                            null,
                            65535,
                            C__RECORD_STATUS__NORMAL,
                            null,
                            "1",
                            $l_object_values['type']['sysid_prefix']
                        );

                        if (!is_numeric($l_status)) {
                            throw new isys_exception_general(sprintf(
                                'Failed to create new object type %s in group %s with language constant %s.',
                                $l_object_values['type']['const'],
                                $l_object_values['type']['group'],
                                $l_object_values['type']['title_lang']
                            ));
                        }
                        $this->m_object_type_ids[$l_object_id] = $l_object_values['type']['const'];
                        $this->m_object_types[$l_status] = $l_object_values['type']['const'];
                        $this->m_log->debug(sprintf(
                            'New object type %s created in group %s with language constant %s.',
                            $l_object_values['type']['const'],
                            $l_object_values['type']['group'],
                            $l_object_values['type']['title_lang']
                        ));
                        // Log:
                        $this->m_event_manager->triggerImportEvent(
                            'C__LOGBOOK_EVENT__OBJECTTYPE_CREATED',
                            isys_application::instance()->container->get('language')
                            ->get('LC__UNIVERSAL__IMPORT'),
                            $this->m_object_ids[$l_object_id],
                            constant($this->m_object_type_ids[$l_object_id]),
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            0,
                            $this->m_logbook_source
                        );
                    }
                    $this->m_log->debug('Now it\'s time to create new object.');
                    $this->handle_update_globals($l_object_values);
                    $this->m_object_ids[$l_object_id] = $this->m_cmdb_dao->insert_new_obj(
                        constant($this->m_object_type_ids[$l_object_id]),
                        false,
                        $l_object_title,
                        $l_object_values['sysid'],
                        $l_object_values['status'],
                        null,
                        date($this->m_date_format, $this->m_scantime),
                        true,
                        null,
                        $l_object_values['created_by'],
                        null,
                        $l_object_values['updated_by'],
                        null,
                        null,
                        $l_object_values['cmdb_status'],
                        $l_object_values['description']
                    );
                    $this->m_log->info('<<<< New Object ' . $l_object_title . ' (' . $this->m_object_ids[$l_object_id] . ') created. >>>>');
                    // Log:
                    $this->m_event_manager->triggerImportEvent(
                        'C__LOGBOOK_EVENT__OBJECT_CREATED',
                        'CMDB-Import',
                        $this->m_object_ids[$l_object_id],
                        constant($this->m_object_type_ids[$l_object_id]),
                        null,
                        serialize([
                            'isys_cmdb_dao_category_g_global::title' => [
                                'from' => '',
                                'to'   => $l_object_title
                            ]
                        ]),
                        null,
                        null,
                        $l_object_title,
                        null,
                        1,
                        $this->m_logbook_source
                    );
                    $this->importCounters['created']++;
                    $this->m_object_states[$this->m_object_ids[$l_object_id]] = self::C__CREATE;
                } //if comparison
            } elseif ($this->m_mode === self::C__APPEND) {
                $this->m_log->debug('Create new object in a moment.');
                $this->m_log->debug(sprintf('Check whether object type %s exists.', $l_object_values['type']['const']));
                if (in_array($l_object_values['type']['const'], $this->m_object_types)) {
                    $this->m_log->debug('Object type already exists.');
                    $this->m_object_type_ids[$l_object_id] = $l_object_values['type']['const'];
                } else {
                    $this->m_log->debug('Object type is unknown. It will be now created.');
                    $l_status = $this->m_cmdb_dao->insert_new_objtype(
                        $l_object_values['type']['group'],
                        $l_object_values['type']['title_lang'],
                        $l_object_values['type']['const'],
                        "1",
                        "0",
                        null,
                        null,
                        65535,
                        C__RECORD_STATUS__NORMAL,
                        null,
                        "1",
                        $l_object_values['type']['sysid_prefix']
                    );

                    if (!is_numeric($l_status)) {
                        throw new isys_exception_general(sprintf(
                            'Failed to create new object type %s in group %s with language constant %s.',
                            $l_object_values['type']['const'],
                            $l_object_values['type']['group'],
                            $l_object_values['type']['title_lang']
                        ));
                    }
                    $this->m_object_type_ids[$l_object_id] = $l_object_values['type']['const'];
                    $this->m_object_types[$l_status] = $l_object_values['type']['const'];
                    $this->m_log->debug(sprintf(
                        'New object type %s created in group %s with language constant %s.',
                        $l_object_values['type']['const'],
                        $l_object_values['type']['group'],
                        $l_object_values['type']['title_lang']
                    ));
                    // Log:
                    $this->m_event_manager->triggerImportEvent(
                        'C__LOGBOOK_EVENT__OBJECTTYPE_CREATED',
                        isys_application::instance()->container->get('language')
                        ->get('LC__UNIVERSAL__IMPORT'),
                        $this->m_object_ids[$l_object_id],
                        constant($this->m_object_type_ids[$l_object_id]),
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        0,
                        $this->m_logbook_source
                    );
                }
                $this->m_log->debug('Now it\'s time to create new object.');
                $this->handle_update_globals($l_object_values);
                $this->m_object_ids[$l_object_id] = $this->m_cmdb_dao->insert_new_obj(
                    constant($this->m_object_type_ids[$l_object_id]),
                    false,
                    $l_object_title,
                    $l_object_values['sysid'],
                    $l_object_values['status'],
                    null,
                    date($this->m_date_format, $this->m_scantime),
                    true,
                    null,
                    $l_object_values['created_by'],
                    null,
                    $l_object_values['updated_by'],
                    null,
                    null,
                    $l_object_values['cmdb_status'],
                    $l_object_values['description']
                );
                $this->m_object_type_ids[$this->m_object_ids[$l_object_id]] = $this->m_object_type_ids[$l_object_id];

                $this->m_log->info('New object "' . $l_object_title . '" (' . $this->m_object_ids[$l_object_id] . ') created.');

                // Log:
                $this->m_event_manager->triggerImportEvent(
                    'C__LOGBOOK_EVENT__OBJECT_CREATED',
                    isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__IMPORT'),
                    $this->m_object_ids[$l_object_id],
                    constant($this->m_object_type_ids[$l_object_id]),
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    0,
                    $this->m_logbook_source
                );
                $this->importCounters['created']++;
                $this->m_object_states[$this->m_object_ids[$l_object_id]] = self::C__CREATE;
            }
        } //foreach objects
    }

    /**
     * Imports category data.
     */
    private function import_categories()
    {
        // Fetch all categories from database:
        if (!isset($this->m_all_categories)) {
            $this->m_all_categories = $this->m_cmdb_dao->get_all_categories();
        }

        $l_helper = null;
        $l_helper_arr = [];
        // Iterate through objects:
        foreach ($this->m_prepared as $l_object_id => $l_object_values) {
            $this->m_log->debug("Importing object $l_object_id: " . $l_object_values[C__DATA__TITLE] . " (" . $l_object_values['sysid'] . ")");

            /* Store OBJECTID */
            self::store__objectID($l_object_id);

            $this->m_log->flush_verbosity(true, false);

            // Initialize some information about the categories:
            $l_category_ids = [];
            $l_category_data_ids = [];
            $l_property_ids = [];

            // Remember processed dao's for this object to not process them
            // twice. (See person and person master.)
            $l_daos_processed = [];

            // Prepare id cache
            $this->m_category_ids[$l_object_id] =& $l_category_ids;
            $this->m_category_data_ids[$l_object_id] =& $l_category_data_ids;
            $this->m_property_ids[$l_object_id] =& $l_property_ids;

            // Iterate trough categories:
            if (is_array($l_object_values[self::C__CATEGORIES])) {
                foreach ($l_object_values[self::C__CATEGORIES] as $l_category_key => $l_category_values) {
                    // Determine identifiers:
                    $l_category_type_id = null;
                    $l_category_id = null;
                    // Set dataset key default 0
                    $l_dataset_key = 0;

                    // Mode may be changed 'locally' in category's context).
                    // Defaults to import mode:
                    $l_mode = $this->m_mode;
                    $l_changed_categories = false;

                    [$l_category_type_id, $l_category_id] = explode('_', $l_category_key);

                    // The custom category type needs a special handling:
                    if ($l_category_type_id == C__CMDB__CATEGORY__TYPE_CUSTOM && !is_numeric($l_category_id)) {
                        $l_category_id = defined_or_default('C__CATG__CUSTOM_FIELDS');
                        $l_category_type_id = C__CMDB__CATEGORY__TYPE_GLOBAL;
                    }

                    // Translate category name:
                    $l_category_name = isys_application::instance()->container->get('language')
                        ->get($this->m_all_categories[$l_category_type_id][$l_category_id]['title']);

                    $this->m_log->info("  Importing category \"$l_category_name\"");

                    // Check category type id:
                    if (!array_key_exists($l_category_type_id, $this->m_all_categories)) {
                        $this->m_log->debug('Category type ' . $l_category_type_id . ' does not exist. Skipping.');
                        continue;
                    }

                    if (!empty($this->m_ignored_categories) && isset($this->m_ignored_categories[$l_category_type_id]) &&
                        in_array($l_category_id, $this->m_ignored_categories[$l_category_type_id])) {
                        $this->m_log->debug('Category is black-listed. Skipping.');
                        continue;
                    }

                    // Class name of category DAO:
                    $l_cat_class = $this->m_all_categories[$l_category_type_id][$l_category_id]['class_name'];

                    // If DAO was already processed, skip this iteration
                    if (isset($l_daos_processed[$l_cat_class]) && $l_category_id != defined_or_default('C__CATG__CUSTOM_FIELDS')) {
                        $this->m_log->debug("Category $l_cat_class ($l_category_id) was already processed by the import. Skipping.");

                        continue;
                    }

                    // We need the category DAO:
                    $l_cat_dao = isys_factory_cmdb_category_dao::get_instance_by_id((int)$l_category_type_id, (int)$l_category_id, $this->m_db);

                    if (!$l_cat_class) {
                        $l_cat_class = get_class($l_cat_dao);
                    }

                    if (method_exists($l_cat_dao, 'set_object_id')) {
                        $l_cat_dao->set_object_id($l_object_id);
                    }

                    // Remember whether dao has been processed before:
                    $l_daos_processed[$l_cat_class] = true;

                    // Collect information about this category for better
                    // performance:
                    if (!isset($this->m_cat_info[$l_category_key])) {
                        $this->m_cat_info[$l_category_key] = [];
                    } //

                    // One more time using special handling for custom categories:
                    if ($l_category_id == defined_or_default('C__CATG__CUSTOM_FIELDS') && $l_category_type_id == C__CMDB__CATEGORY__TYPE_GLOBAL) {
                        $this->m_log->debug('Handle custom category ' . $l_category_values['title']);

                        // This identifier is needed to get category's data information:
                        $l_catg_custom_id = $l_category_values['id'];
                        /**
                         * @var $l_cat_dao isys_cmdb_dao_category_g_custom_fields
                         */
                        $l_cat_dao->set_catg_custom_id($l_catg_custom_id);

                        // Check whether custom category already exists:
                        $l_custom_module = isys_custom_fields_dao::instance($this->m_db);

                        // Create custom category configuration:
                        $l_config = [];
                        foreach ($l_category_values[self::C__CATEGORY_ENTITIES] as $l_category_data_values) {
                            if (isset($l_category_data_values['properties']) && is_array($l_category_data_values['properties']) &&
                                count($l_category_data_values['properties'])) {
                                foreach ($l_category_data_values['properties'] as $l_tag => $l_values) {
                                    if ($l_tag == 'description') {
                                        continue;
                                    }
                                    //$l_key = substr(strrchr($l_tag, '_'), 1);
                                    $l_key = strrchr($l_tag, 'c_');

                                    $l_type = substr($l_tag, 0, strrpos($l_tag, '_c'));
                                    $l_config[$l_key]['type'] = $l_type;
                                    $l_config[$l_key]['title'] = $l_values['title'];

                                    if ($l_type == 'f_popup') {
                                        $popupType = 'dialog_plus';
                                        if ((isset($l_values['prop_type']))) {
                                            $l_config[$l_key]['popup'] = $l_values['prop_type'];
                                            if (isset($l_values['identifier'])) {
                                                $l_config[$l_key]['identifier'] = $l_values['identifier'];
                                            }
                                        } elseif (is_array($l_values[C__DATA__VALUE])) {
                                            if (isset($l_values[C__DATA__VALUE][0]['prop_type'])) {
                                                $popupType = $l_values[C__DATA__VALUE][0]['prop_type'];
                                            }
                                            $l_config[$l_key]['popup'] = $popupType;
                                            if (isset($l_values[C__DATA__VALUE][0]['identifier'])) {
                                                $l_config[$l_key]['identifier'] = $l_values[C__DATA__VALUE][0]['identifier'];
                                            }

                                            if (isset($l_values[C__DATA__VALUE][0]['multiselection'])) {
                                                $l_config[$l_key]['multiselection'] = $l_values[C__DATA__VALUE][0]['multiselection'];
                                            }
                                        } else {
                                            $l_config[$l_key]['popup'] = $popupType;
                                            $l_config[$l_key]['identifier'] = $l_values['identifier'];
                                        }
                                    } else {
                                        if (isset($l_values['prop_type'])) {
                                            $l_config[$l_key]['extra'] = $l_values['prop_type'];
                                        }
                                    }
                                }
                            }
                        }

                        $l_serialized_config = serialize($l_config);

                        // Search by identifier, title and config:
                        $l_found = false;
                        $l_module_data = $l_custom_module->get_data(null, $l_category_values['title'])
                            ->__as_array();
                        if (is_countable($l_module_data) && count($l_module_data) > 0) {
                            foreach ($l_module_data as $l_candidate) {
                                if ($l_candidate['isysgui_catg_custom__id'] == $l_catg_custom_id) {
                                    $l_found = true;
                                } elseif ($l_candidate['isysgui_catg_custom__config'] == $l_serialized_config) {
                                    $l_found = true;
                                } else {
                                    $customConfig = unserialize($l_candidate['isysgui_catg_custom__config']);
                                    if (is_countable($customConfig) && count(array_diff_assoc($customConfig, $l_config)) === 0) {
                                        $l_found = true;
                                    }
                                }

                                if ($l_found === true) {
                                    // Update identifier:
                                    $l_catg_custom_id = $l_candidate['isysgui_catg_custom__id'];
                                    $l_cat_dao->set_catg_custom_id($l_catg_custom_id);
                                    $l_cat_dao->set_config($l_catg_custom_id);
                                    break;
                                }
                            }
                        }

                        if ($l_found === false) {
                            $this->m_log->notice('Custom category ' . $l_category_values['title'] . ' not found. Creating...');

                            $l_catg_custom_id = $l_custom_module->create(
                                $l_category_values['title'],
                                $l_config,
                                0,
                                0,
                                $l_category_values['multivalued'],
                                str_replace('C__CATG__CUSTOM_FIELDS_', '', $l_category_values['const'])
                            );

                            if ($l_catg_custom_id === false) {
                                $this->m_log->warning('Failed to create custom category ' . $l_category_values['title'] . ' [' . $l_category_values['const'] . ']. Skipping.');
                                continue;
                            }

                            $l_status = $l_custom_module->assign($l_catg_custom_id, constant($this->m_object_type_ids[$l_object_id]));
                            if ($l_status === false) {
                                $this->m_log->warning('Failed to assign custom category ' . $l_category_values['title'] . ' [' . $l_category_values['const'] .
                                    '] to object type ' . $this->m_object_type_ids[$l_object_id] . '. Skipping.');
                                continue;
                            }

                            // Update identifier:
                            $l_cat_dao->set_catg_custom_id($l_catg_custom_id);
                            $l_cat_dao->set_config($l_catg_custom_id);
                        } //if custom category is unknown

                        unset($l_custom_module);
                    } //if custom category

                    // Is it a single or a multi-value category
                    if (!isset($this->m_cat_info[$l_category_key]['is_multi-valued'])) {
                        $this->m_cat_info[$l_category_key]['is_multi-valued'] = $l_cat_dao->is_multivalued();
                    }

                    // Multi-valued categories need special care:
                    if ($this->m_cat_info[$l_category_key]['is_multi-valued']) {
                        switch ($this->m_multivalue_categories) {
                            // Leave multi-valued categories untouched to avoid many
                            // "complications":
                            case self::C__UNTOUCHED:
                                // Skip category:
                                continue 2;
                                break;
                            // Overwrite import mode for this category temporary:
                            case self::C__APPEND:
                            case self::C__OVERWRITE:
                                $l_mode = $this->m_multivalue_categories;
                                break;
                            case self::C__UPDATE:
                            case self::C__UPDATE_ADD:
                                $l_mode = self::C__MERGE;
                        }
                    }

                    // Before getting the properties we unset them for custom categories
                    if ($l_category_id == defined_or_default('C__CATG__CUSTOM_FIELDS') && $l_category_type_id == C__CMDB__CATEGORY__TYPE_GLOBAL) {
                        $l_cat_dao->unset_properties();
                    }

                    // Get property information:
                    if (!isset($this->m_cat_info[$l_category_key]['property_info'])) {
                        $this->m_cat_info[$l_category_key]['property_info'] = $l_cat_dao->get_properties(C__PROPERTY__WITH__VALIDATION);
                    }

                    // Fetch category data from database by object identifier:
                    $l_object_category_dataset = [];
                    if ($l_mode === self::C__MERGE || $l_mode === self::C__USE_IDS) {
                        if (method_exists($l_cat_dao, 'get_export_condition')) {
                            $l_condition = $l_cat_dao->get_export_condition();
                        } else {
                            $l_condition = '';
                        }

                        $l_object_category_dataset = $l_cat_dao->get_data(null, $this->m_object_ids[$l_object_id], $l_condition, null, C__RECORD_STATUS__NORMAL)
                            ->__as_array();
                        $l_cat_dao->set_category_data($l_object_category_dataset);
                    }

                    // Save category identifier:
                    $l_category_ids[$l_category_type_id][$l_category_id] = $l_cat_dao->get_category_id();

                    // We need the category's 'main' table:
                    if (!isset($this->m_cat_info[$l_category_key]['table'])) {
                        $this->m_cat_info[$l_category_key]['table'] = $l_cat_dao->get_table();
                    }

                    // Protect already used datasets:
                    $l_used_datasets = [];

                    // Don't delete new added category data:
                    $l_already_overwritten = false;

                    // Event counter:
                    $l_event_counter = [self::C__CREATE => 0];

                    $l_collected_category_changes = [];

                    if (is_array($l_category_values[self::C__CATEGORY_ENTITIES])) {
                        // Iterate through category entities:
                        foreach ($l_category_values[self::C__CATEGORY_ENTITIES] as $l_category_data_id => $l_category_data_values) {
                            $l_category_synched = false;
                            $errorMessage = '';
                            $validationErrors = null;
                            $logbookEntry = null;
                            $l_new_category_data_id = false;

                            // Skip empty categories:
                            if (count($l_category_data_values[self::C__PROPERTIES]) == 0) {
                                // You may ask, why there are even these empty
                                // categories? Whenever an empty category is edited
                                // in the GUI, an empty entity will be created in
                                // the database. But if the edit mode is aborted,
                                // the empty entity will still remain.
                                continue;
                            }

                            // Initiate used properties. Only these will be used for
                            // the import:
                            $l_used_properties = [];

                            // Iterate through properties to be imported:
                            foreach ($l_category_data_values['properties'] as $l_key => $l_value) {
                                // Check whether property is enabled for syncing:
                                $l_used_properties[$l_key] = false;

                                foreach ($this->m_cat_info[$l_category_key]['property_info'] as $l_tag => $l_info) {
                                    // Import for this property is disabled:
                                    if ($l_tag == $l_key && $l_info[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__IMPORT] === false) {
                                        unset($l_category_data_values['properties'][$l_key]);
                                        continue 2;
                                    }
                                    // Found right property:
                                    if ($l_tag == $l_key) {
                                        $l_used_properties[$l_key] = $l_info;
                                        break;
                                    }
                                }

                                // Cannot match property with given information. Skipping:
                                if ($l_used_properties[$l_key] === false) {
                                    continue;
                                }

                                // We define the helper-class and method as "False".
                                $l_class = false;
                                $l_method = false;

                                // Now we check if the helper class and method is defined and set them.
                                if (isset($l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0])) {
                                    $l_class = $l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0];
                                }

                                if (isset($l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1])) {
                                    $l_method = $l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1];
                                }

                                if ($l_class && $l_method) {
                                    if (!class_exists($l_class)) {
                                        throw new isys_exception_cmdb(sprintf('Import failed. Helping class %s for importing data is not available.', $l_class));
                                    }

                                    // Here we define the import-method.
                                    $l_import_method = $l_method . '_import';

                                    // And check if it exists.
                                    if (!method_exists($l_class, $l_import_method)) {
                                        $this->m_log->warning(sprintf(
                                            'Helping method %s for importing data is not available. Method was expected in class %s. Skipping',
                                            $l_import_method,
                                            $l_class
                                        ));
                                    } else {
                                        if (!isset($l_helper_arr[$l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]])) {
                                            $l_helper = new $l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0](
                                                [],
                                                $this->m_db,
                                                $l_used_properties[$l_key][C__PROPERTY__DATA],
                                                $l_used_properties[$l_key][C__PROPERTY__FORMAT],
                                                $l_used_properties[$l_key][C__PROPERTY__UI]
                                            );
                                            $l_helper_arr[$l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]] = $l_helper;
                                        } else {
                                            $l_helper = $l_helper_arr[$l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]];
                                            $l_helper->set_row([]);
                                            $l_helper->set_reference_info($l_used_properties[$l_key][C__PROPERTY__DATA]);
                                            $l_helper->set_format_info($l_used_properties[$l_key][C__PROPERTY__FORMAT]);
                                            $l_helper->set_ui_info($l_used_properties[$l_key][C__PROPERTY__UI]);
                                        }

                                        if (isset($l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT])) {
                                            $l_unit_key = $l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT];

                                            if (method_exists($l_helper, 'set_unit_const')) {
                                                $l_helper->set_unit_const($l_category_data_values[self::C__PROPERTIES][$l_unit_key]['const']);
                                            }
                                        }

                                        if (method_exists($l_helper, 'set_object_ids')) {
                                            $l_helper->set_object_ids($this->m_object_ids);
                                        }

                                        if (method_exists($l_helper, 'set_property_data')) {
                                            $l_helper->set_property_data($l_category_data_values[self::C__PROPERTIES]);
                                        }

                                        if (method_exists($l_helper, 'set_category_ids')) {
                                            $l_helper->set_category_ids($l_category_ids);
                                        }

                                        if (method_exists($l_helper, 'set_category_data_ids')) {
                                            $l_helper->set_category_data_ids($l_category_data_ids);
                                        }

                                        if (method_exists($l_helper, 'set_mode')) {
                                            $l_helper->set_mode($l_mode);
                                        }

                                        $l_transformed = null;

                                        // Transform only values which needs to be converted:
                                        if ((($l_import_method == 'connection_import' || $l_import_method == 'object_import') && is_array($l_value)) || $l_import_method == 'model_title_import' ||
                                            $l_import_method == 'convert_import' || $l_import_method == 'dialog_import' || $l_import_method == 'dialog_plus_import' ||
                                            $l_import_method == 'object_image_import' || (array_key_exists('ref_id', $l_category_data_values['properties'][$l_key]) &&
                                                ($l_category_data_values['properties'][$l_key]['ref_id'] === null ||
                                                    !is_numeric($l_category_data_values['properties'][$l_key][C__DATA__VALUE]))) || $l_mode === self::C__MERGE) {
                                            $l_transformed = $l_helper->$l_import_method($l_value);

                                            if ($l_transformed === false) {
                                                $this->m_log->warning(sprintf(
                                                    'Transformation failed (method %s from class %s in category %s).',
                                                    $l_import_method,
                                                    $l_class,
                                                    $l_category_name
                                                ));
                                            }
                                        } else {
                                            // Convert value for the comparison.
                                            $l_transformed = $l_category_data_values['properties'][$l_key][C__DATA__VALUE];
                                        }

                                        if ($l_import_method == 'convert_import') {
                                            $l_category_data_values['properties'][$l_key][C__DATA__VALUE . '_converted'] = $l_transformed;
                                        } else {
                                            $l_category_data_values['properties'][$l_key][C__DATA__VALUE] = $l_transformed;
                                        }

                                        unset($l_helper);
                                    }
                                }
                            }

                            // Initiate change log.
                            $l_category_changes = [];

                            // Initiate status.
                            $l_category_data_status = self::C__CREATE;

                            // Act based on import mode.
                            if ($l_mode === self::C__APPEND) {
                                $l_category_data_status = self::C__CREATE;
                                $this->m_log->debug('Ignore data from database.');
                            } elseif ($l_mode === self::C__OVERWRITE) {
                                $l_category_data_status = self::C__CREATE;

                                if ($l_already_overwritten === false && $l_cat_class != 'isys_cmdb_dao_category_g_global') {
                                    $this->m_log->debug('Deleting existing category data from database...');

                                    $l_cleared = $l_cat_dao->clear_data($this->m_object_ids[$l_object_id], $this->m_cat_info[$l_category_key]['table']);

                                    if ($l_cleared === true) {
                                        $this->m_log->info('Category data deleted.');
                                    } else {
                                        $this->m_log->warning(sprintf(
                                            'Could not delete category data for object %s (%s) from category %s.',
                                            $l_object_values[C__DATA__TITLE],
                                            $l_object_id,
                                            $l_category_name
                                        ));
                                    }

                                    $l_already_overwritten = true;
                                }
                            } elseif ($l_mode === self::C__MERGE || $l_mode === self::C__USE_IDS) {
                                // There are one or more data sets fetched from
                                // database:
                                if (is_countable($l_object_category_dataset) && count($l_object_category_dataset) == 0) {
                                    $l_category_data_status = self::C__CREATE;
                                } else {
                                    // If the category is single-valued, the standard action is to update an existing dataset, otherwise create it.
                                    if ($this->m_cat_info[$l_category_key]['is_multi-valued']) {
                                        $l_category_data_status = self::C__CREATE;
                                    } else {
                                        $l_category_data_status = self::C__UPDATE;
                                    }

                                    // Initiate some information about the comparison:
                                    $l_badness = [];
                                    $l_comparison = [];

                                    // Let's compare the data from database with the
                                    // current category data to be imported:
                                    $this->m_log->debug('Checking whether one of the datasets matches the properties of category "' . $l_category_name . '"...');

                                    $l_local_export = null;

                                    if (!isset($this->m_cat_info[$l_category_key]['can_compare'])) {
                                        $this->m_cat_info[$l_category_key]['can_compare'] = method_exists($l_cat_dao, 'compare_category_data');
                                    }

                                    // declare reference variables for compare_category_data()
                                    $l_dataset_id_changed = isset($l_dataset_id_changed) ? $l_dataset_id_changed : null;
                                    $l_dataset_id = isset($l_dataset_id) ? $l_dataset_id : null;
                                    $l_unit_key = isset($l_unit_key) ? $l_unit_key : null;

                                    // Iterate through local data sets:
                                    if ($this->m_cat_info[$l_category_key]['can_compare']) {
                                        if (method_exists($l_cat_dao, 'compare_category_data')) {
                                            $l_cat_dao->compare_category_data(
                                                $l_category_data_values,
                                                $l_object_category_dataset,
                                                $l_used_properties,
                                                $l_comparison,
                                                $l_badness,
                                                $l_mode,
                                                $l_category_id,
                                                $l_unit_key,
                                                $l_category_data_ids,
                                                $l_local_export,
                                                $l_dataset_id_changed,
                                                $l_dataset_id,
                                                $this->m_log,
                                                $l_category_name,
                                                $this->m_cat_info[$l_category_key]['table'],
                                                $this->m_cat_info[$l_category_key]['is_multi-valued'],
                                                $l_category_type_id,
                                                $l_category_ids,
                                                $this->m_object_ids,
                                                $l_used_datasets
                                            );
                                        }
                                    } elseif ($this->m_cat_info[$l_category_key]['is_multi-valued']) {
                                        $this->compare_category_data(
                                            $l_category_data_values,
                                            $l_object_category_dataset,
                                            $l_used_properties,
                                            $l_comparison,
                                            $l_badness,
                                            $l_mode,
                                            $l_category_id,
                                            $l_unit_key,
                                            $l_category_data_ids,
                                            $l_local_export,
                                            $l_dataset_id_changed,
                                            $l_dataset_id,
                                            $this->m_log,
                                            $l_category_name,
                                            $this->m_cat_info[$l_category_key]['table'],
                                            $this->m_cat_info[$l_category_key]['is_multi-valued'],
                                            $l_category_type_id,
                                            $l_category_ids[$l_category_key],
                                            $l_used_datasets
                                        );
                                    } else {
                                        if (!$this->m_cat_info[$l_category_key]['is_multi-valued'] &&
                                            isset($l_object_category_dataset[0][$this->m_cat_info[$l_category_key]['table'] . '__id'])) {
                                            $l_category_data_values['data_id'] = $l_object_category_dataset[0][$this->m_cat_info[$l_category_key]['table'] . '__id'];
                                        }

                                        if (isset($l_category_data_values['data_id']) && $this->m_empty_fields == self::C__KEEP) {
                                            $l_comparison[self::C__COMPARISON__SAME][$l_category_data_values['data_id']] = $l_category_data_values['data_id'];
                                            // Merge missing data
                                            $this->merge_missing_data(
                                                $l_category_data_values,
                                                $l_object_category_dataset,
                                                $l_category_key,
                                                0,
                                                $l_category_ids,
                                                $l_category_data_ids,
                                                $l_mode,
                                                $l_helper_arr,
                                                $l_cat_class
                                            );
                                        }
                                    }

                                    $l_candidate_found = null;

                                    if (isset($l_comparison[self::C__COMPARISON__SAME]) && is_countable($l_comparison[self::C__COMPARISON__SAME]) &&
                                        count($l_comparison[self::C__COMPARISON__SAME]) > 0) {
                                        if ($this->m_cat_info[$l_category_key]['is_multi-valued']) {
                                            foreach ($l_comparison[self::C__COMPARISON__SAME] as $l_candidate) {
                                                if (!isset($l_used_datasets[$l_candidate])) {
                                                    $l_candidate_found = $l_candidate;
                                                    $l_used_datasets[$l_candidate] = true;
                                                    $l_category_data_status = self::C__KEEP;
                                                    $l_category_data_values['data_id'] = $l_candidate;
                                                    //$this->m_log->debug('Keeping ' . $l_candidate);
                                                    break;
                                                }
                                            }
                                        } else {
                                            $l_candidate = current($l_comparison[self::C__COMPARISON__SAME]);
                                            $l_candidate_found = $l_candidate;
                                            $l_used_datasets[$l_candidate] = true;
                                            $l_category_data_status = self::C__UPDATE;
                                            $l_category_data_values['data_id'] = $l_candidate;
                                            //$this->m_log->debug('Updating ' . $l_candidate);
                                        }
                                    }

                                    if ($l_candidate_found === null && isset($l_comparison[self::C__COMPARISON__PARTLY]) &&
                                        is_countable($l_comparison[self::C__COMPARISON__PARTLY]) &&
                                        count($l_comparison[self::C__COMPARISON__PARTLY]) > 0) {
                                        foreach ($l_comparison[self::C__COMPARISON__PARTLY] as $l_dataset_key => $l_candidate) {
                                            if (!isset($l_used_datasets[$l_candidate])) {
                                                $l_used_datasets[$l_candidate] = true;
                                                $l_category_data_status = self::C__UPDATE;
                                                $l_category_data_values['data_id'] = $l_candidate;

                                                // We'll want to keep the original data
                                                // fields, if the import data fields are
                                                // *not* set or NULL:
                                                if ($this->m_empty_fields == self::C__KEEP) {
                                                    // Merge missing data
                                                    $this->merge_missing_data(
                                                        $l_category_data_values,
                                                        $l_object_category_dataset,
                                                        $l_category_key,
                                                        $l_dataset_key,
                                                        $l_category_ids,
                                                        $l_category_data_ids,
                                                        $l_mode,
                                                        $l_helper_arr,
                                                        $l_cat_class
                                                    );
                                                }

                                                break;
                                            }
                                        }
                                    } // if found partly
                                } // if count dataset
                            } // if import mode

                            if ($l_category_data_status !== self::C__KEEP) {
                                if ($l_category_data_status === self::C__CREATE) {
                                    if (!$this->m_cat_info[$l_category_key]['is_multi-valued']) {
                                        /*
                                         * New bugfix for global category. The global category entry gets created together with the object,
                                         * so we can't rely on this check here... So I added an additional check if the DAO class is "isys_cmdb_dao_category_g_global".
                                         */
                                        $l_res = $l_cat_dao->get_data(null, $this->m_object_ids[$l_object_id], '', null, C__RECORD_STATUS__NORMAL);

                                        if ($l_res->num_rows() == 1 && $l_cat_class != 'isys_cmdb_dao_category_g_global') {
                                            $l_category_data_status = self::C__UPDATE;
                                            $this->m_log->debug('  Dataset already exists; update current dataset.');
                                            // Merge existing data
                                            $l_row = $l_res->get_row();
                                            $l_category_data_values['data_id'] = $l_row[$this->m_cat_info[$l_category_key]['table'] . '__id'];

                                            // Bugfix for auto inventory no so that the automated inventory number will be generated
                                            if ($l_cat_class === 'isys_cmdb_dao_category_g_accounting' &&
                                                defined($this->m_object_type_ids[$this->m_object_ids[$l_object_id]])) {
                                                $l_obj_type_id = constant($this->m_object_type_ids[$this->m_object_ids[$l_object_id]]);
                                                if (isys_tenantsettings::get('cmdb.objtype.' . $l_obj_type_id . '.auto-inventory-no', '')) {
                                                    $l_category_data_values['properties']['inventory_no'][C__DATA__VALUE] = null;
                                                }
                                            }
                                            $this->merge_missing_data(
                                                $l_category_data_values,
                                                [$l_row],
                                                $l_category_key,
                                                $l_dataset_key,
                                                $l_category_ids,
                                                $l_category_data_ids,
                                                $l_mode,
                                                $l_helper_arr,
                                                $l_cat_class
                                            );
                                        } else {
                                            $this->m_log->debug('  Dataset will be created.');
                                        }
                                    }
                                } //if create dataset

                                if ($l_mode == self::C__MERGE && is_countable($l_object_category_dataset) && count($l_object_category_dataset) > 0 &&
                                    $l_category_id != defined_or_default('C__CATG__CUSTOM_FIELDS')) {
                                    // Skip transformation, because it's already done.
                                } else {
                                    if (isset($l_dataset_id_changed) && $l_dataset_id_changed) {
                                        $l_category_data_values['data_id'] = $l_dataset_id;
                                    }

                                    foreach ($l_category_data_values['properties'] as $l_key => $l_value) {
                                        if (!$l_used_properties[$l_key][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__IMPORT]) {
                                            continue;
                                        }

                                        // Need to unconvonvert:
                                        if (!isset($l_category_data_values['properties'][$l_key][C__DATA__VALUE . '_converted']) &&
                                            isset($l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]) &&
                                            isset($l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1])) {
                                            if (!class_exists($l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0])) {
                                                $this->m_log->warning(sprintf(
                                                    'Import failed. Helping class %s for importing data is not available. Skipping.',
                                                    $l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]
                                                ));
                                                continue;
                                            }

                                            $l_method = $l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] . "_import";

                                            if (!method_exists($l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0], $l_method)) {
                                                $this->m_log->warning(sprintf(
                                                    'Helping method %s for importing data is not available. Method was expected in class %s. Skipping',
                                                    $l_method,
                                                    $l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]
                                                ));

                                                if (isset($l_category_data_values[self::C__PROPERTIES][$l_key]['sysid'])) {
                                                    // Object reference
                                                    $l_category_data_values[self::C__PROPERTIES][$l_key][C__DATA__VALUE] = $this->m_object_ids[$l_category_data_values[self::C__PROPERTIES][$l_key]['id']];
                                                } else {
                                                    // Category reference
                                                    $l_id_set = false;
                                                    $l_type_set = false;

                                                    if (is_array($l_category_data_values[self::C__PROPERTIES][$l_key][C__DATA__VALUE])) {
                                                        foreach ($l_category_data_values[self::C__PROPERTIES][$l_key][C__DATA__VALUE] as $l_key2 => $l_val) {
                                                            if (is_array($l_val)) {
                                                                foreach ($l_val as $l_key3 => $l_val2) {
                                                                    if ($l_key3 === 'id') {
                                                                        $l_id_set = $l_val2;
                                                                    }

                                                                    if ($l_key3 === 'type') {
                                                                        $l_type_set = $l_val2;
                                                                    }
                                                                }
                                                            } else {
                                                                if ($l_key2 === 'id') {
                                                                    $l_id_set = $l_val;
                                                                }

                                                                if ($l_key2 === 'type') {
                                                                    $l_type_set = $l_val;
                                                                }
                                                            }

                                                            if ($l_id_set && $l_type_set) {
                                                                break;
                                                            }
                                                        } // foreach category data value

                                                        $this->m_log->info('It is a category reference.');
                                                    } else {
                                                        foreach ($l_category_data_values[self::C__PROPERTIES][$l_key] as $l_key2 => $l_val) {
                                                            if ($l_key2 === 'id') {
                                                                $l_id_set = $l_val;
                                                            }

                                                            if ($l_key2 === 'type') {
                                                                $l_type_set = $l_val;
                                                            }

                                                            if ($l_id_set && $l_type_set) {
                                                                break;
                                                            }
                                                        } // foreach property
                                                    }

                                                    if ($l_id_set && $l_type_set) {
                                                        if (!is_numeric($l_type_set)) {
                                                            $l_type_set = constant($l_type_set);
                                                        }

                                                        if (isset($l_category_data_ids[$l_category_type_id][$l_type_set][$l_id_set])) {
                                                            $l_category_data_values[self::C__PROPERTIES][$l_key][C__DATA__VALUE] = $l_category_data_ids[$l_category_type_id][$l_type_set][$l_id_set];
                                                        } else {
                                                            $l_category_data_values[self::C__PROPERTIES][$l_key][C__DATA__VALUE] = null;
                                                        }
                                                    }
                                                } // if type of reference

                                                continue;
                                            } //if method exists

                                            if (!class_exists($l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0])) {
                                                $this->m_log->debug(sprintf(
                                                    'Export helper class does not exist: %s. Skipping.',
                                                    $l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]
                                                ));
                                                continue;
                                            }

                                            if (!isset($l_helper_arr[$l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]])) {
                                                $l_helper = new $l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0](
                                                    isset($l_dataset) ? $l_dataset : null,
                                                    $this->m_db,
                                                    $l_used_properties[$l_key][C__PROPERTY__DATA],
                                                    $l_used_properties[$l_key][C__PROPERTY__FORMAT],
                                                    $l_used_properties[$l_key][C__PROPERTY__UI]
                                                );
                                                $l_helper_arr[$l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]] = $l_helper;
                                            } else {
                                                $l_helper = $l_helper_arr[$l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]];
                                                $l_helper->set_row((isset($l_dataset) ? $l_dataset : null));
                                                $l_helper->set_reference_info($l_used_properties[$l_key][C__PROPERTY__DATA]);
                                                $l_helper->set_format_info($l_used_properties[$l_key][C__PROPERTY__FORMAT]);
                                                $l_helper->set_ui_info($l_used_properties[$l_key][C__PROPERTY__UI]);
                                            }

                                            if (isset($l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT])) {
                                                $l_unit_key = $l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT];

                                                if (method_exists($l_helper, 'set_unit_const')) {
                                                    $l_helper->set_unit_const($l_category_data_values['properties'][$l_unit_key]['const']);
                                                }
                                            }

                                            if (method_exists($l_helper, 'set_object_ids')) {
                                                $l_helper->set_object_ids($this->m_object_ids);
                                            }

                                            if (method_exists($l_helper, 'set_property_data')) {
                                                $l_helper->set_property_data($l_category_data_values[self::C__PROPERTIES]);
                                            }

                                            if (method_exists($l_helper, 'set_category_ids')) {
                                                $l_helper->set_category_ids($l_category_ids);
                                            }

                                            if (method_exists($l_helper, 'set_category_data_ids')) {
                                                $l_helper->set_category_data_ids($l_category_data_ids);
                                            }

                                            if (method_exists($l_helper, 'set_mode')) {
                                                $l_helper->set_mode($l_mode);
                                            }

                                            $l_import = $l_helper->$l_method($l_value);

                                            if ($l_import === false) {
                                                $this->m_log->warning(sprintf(
                                                    'Transformation failed (method %s from class %s in category %s).',
                                                    $l_method,
                                                    $l_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0],
                                                    $l_category_name
                                                ));
                                            }

                                            $l_category_data_values['properties'][$l_key][C__DATA__VALUE] = $l_import;

                                            unset($l_helper);
                                        } else {
                                            if (isset($l_category_data_values[self::C__PROPERTIES][$l_key]['sysid'])) {
                                                // Object reference.
                                                $l_category_data_values[self::C__PROPERTIES][$l_key][C__DATA__VALUE] = $this->m_object_ids[$l_category_data_values[self::C__PROPERTIES][$l_key]['id']];
                                            } else {
                                                // Category reference.
                                                $l_id_set = false;
                                                $l_type_set = false;

                                                if (is_array($l_category_data_values[self::C__PROPERTIES][$l_key][C__DATA__VALUE])) {
                                                    foreach ($l_category_data_values[self::C__PROPERTIES][$l_key][C__DATA__VALUE] as $l_key2 => $l_val) {
                                                        if (is_array($l_val)) {
                                                            foreach ($l_val as $l_key3 => $l_val2) {
                                                                if ($l_key3 === 'id') {
                                                                    $l_id_set = $l_val2;
                                                                }

                                                                if ($l_key3 === 'type') {
                                                                    $l_type_set = $l_val2;
                                                                }
                                                            }
                                                        } else {
                                                            if ($l_key2 === 'id') {
                                                                $l_id_set = $l_val;
                                                            }

                                                            if ($l_key2 === 'type') {
                                                                $l_type_set = $l_val;
                                                            }
                                                        }

                                                        if ($l_id_set && $l_type_set) {
                                                            break;
                                                        }
                                                    } // foreach category data value

                                                    $this->m_log->info('It is a category reference.');
                                                } else {
                                                    foreach ($l_category_data_values[self::C__PROPERTIES][$l_key] as $l_key2 => $l_val) {
                                                        if ($l_key2 === 'id') {
                                                            $l_id_set = $l_val;
                                                        }

                                                        if ($l_key2 === 'type') {
                                                            $l_type_set = $l_val;
                                                        }

                                                        if ($l_id_set && $l_type_set) {
                                                            break;
                                                        }
                                                    } // foreach property
                                                }

                                                if ($l_id_set && $l_type_set) {
                                                    if (!is_numeric($l_type_set)) {
                                                        $l_type_set = constant($l_type_set);
                                                    }

                                                    if (isset($l_category_data_ids[$l_category_type_id][$l_type_set][$l_id_set])) {
                                                        $l_category_data_values[self::C__PROPERTIES][$l_key][C__DATA__VALUE] = $l_category_data_ids[$l_category_type_id][$l_type_set][$l_id_set];
                                                    } else {
                                                        $l_category_data_values[self::C__PROPERTIES][$l_key][C__DATA__VALUE] = null;
                                                    }
                                                } else {
                                                    // Special cases for duplication
                                                    if ($l_cat_class == 'isys_cmdb_dao_category_g_global' && ($l_new_title = $this->get_replaced_title($l_object_id))) {
                                                        // replace title in $l_category_data_values
                                                        $l_category_data_values[self::C__PROPERTIES]['title'][C__DATA__VALUE] = $l_new_title;
                                                    }
                                                }
                                                // if
                                            } // if type of reference
                                        } // if type of method
                                    } // foreach property
                                } // if transformation is needed

                                //$this->m_log->debug('Syncing category...');

                                if (is_countable($l_category_data_values[self::C__PROPERTIES]) && count($l_category_data_values[self::C__PROPERTIES]) > 0) {

                                    // If we should keep empty values in the object - remove empty fields from the values
                                    if ($this->m_empty_fields === self::C__KEEP && is_array($l_category_data_values[self::C__PROPERTIES]) &&
                                        $l_category_id === defined_or_default('C__CATG__CUSTOM_FIELDS')) {
                                        foreach ($l_category_data_values[self::C__PROPERTIES] as $field => $property) {
                                            if (empty($property['value'])) {
                                                unset($l_category_data_values[self::C__PROPERTIES][$field]);
                                            }
                                        }
                                    }

                                    // Create new logbook entry before sync because we
                                    // need the old data before its completly changed.
                                    // It's needed for all the callbacks.
                                    // Only on update to reduce logbook entries:
                                    if ($l_category_data_status === self::C__UPDATE) {
                                        $this->m_log->debug('== Dataset updated.');

                                        // This prepares changes for new category entries but it makes the import more slower
                                        if (isset($l_object_category_dataset[$l_dataset_key])) {
                                            $l_local_data = $l_object_category_dataset[$l_dataset_key];
                                        } elseif (is_countable($l_object_category_dataset) && count($l_object_category_dataset) == 1) {
                                            $l_local_data = array_pop($l_object_category_dataset);
                                        } else {
                                            $l_local_data = null;
                                        }

                                        $l_category_changes = $this->m_mod_logbook->prepare_changes($l_cat_dao, $l_local_data, $l_category_data_values);

                                        if (is_countable($l_category_changes) && count($l_category_changes) > 0) {
                                            $logbookEntry = [
                                                'object_id'      => $this->m_object_ids[$l_object_id],
                                                'object_type_id' => $this->m_object_type_ids[$l_object_id],
                                                'category'       => $l_category_name,
                                                'count_changes'  => 1
                                            ];
                                        }
                                    } elseif ($l_category_data_status === self::C__CREATE) {
                                        $this->m_log->debug('  Dataset created.');

                                        $l_category_changes = $this->m_mod_logbook->prepare_changes($l_cat_dao, null, $l_category_data_values);

                                        $logbookEntry = [
                                            'object_id'      => $this->m_object_ids[$l_object_id],
                                            'object_type_id' => $this->m_object_type_ids[$l_object_id],
                                            'category'       => $l_category_name,
                                            'count_changes'  => 1
                                        ];
                                    }

                                    try {
                                        // Emitting signal mod.cmdb.beforeCategorySync:
                                        $this->m_signals->emit(
                                            'mod.cmdb.beforeCategorySync',
                                            $l_category_id,
                                            $l_category_data_values,
                                            $this->m_object_ids[$l_object_id],
                                            $l_category_data_status,
                                            $l_category_type_id
                                        );

                                        // Set Object ID in category dao
                                        $l_cat_dao->set_object_id($this->m_object_ids[$l_object_id]);
                                        // Set Object Type ID in category dao
                                        $l_cat_dao->set_object_type_id((!is_numeric($this->m_object_type_ids[$l_object_id]) ? constant($this->m_object_type_ids[$l_object_id]) : $this->m_object_type_ids[$l_object_id]));

                                        $validationErrors = $this->validateCategoryDataBeforeSync($l_cat_dao, $l_category_data_values, $l_category_changes);

                                        // Update logbook entry changes after validation routine
                                        if ($this->m_cat_info[$l_category_key]['is_multi-valued'] && $l_event_counter[self::C__CREATE] <= isys_tenantsettings::get('logbook.changes.multivalue-threshold', 25)) {
                                            $l_collected_category_changes[$l_event_counter[self::C__CREATE]] = $l_category_changes;
                                            $logbookEntry = null;
                                            $l_event_counter[self::C__CREATE]++;
                                        } elseif ($logbookEntry !== null && is_array($logbookEntry)) {
                                            $logbookEntry['changes'] = serialize($l_category_changes);
                                        }

                                        /*
                                         * Check whether there are any validation problems
                                         * PLEASE NOTICE: Handling on validation error is supposed to be
                                         * Yes = skip dataset if there are any validation errors
                                         * No = Set invalid attribute in dataset to NULL and create entry
                                         */
                                        if (!empty($validationErrors) && is_array($validationErrors) && (bool)isys_tenantsettings::get('import.validation.break-on-error', true)) {
                                            $logbookEntry = null;
                                            throw new isys_exception_validation(
                                                isys_application::instance()->container->get('language')->get($l_cat_dao->getCategoryTitle()) . ' &raquo; ' .
                                                implode('<br/>', $validationErrors),
                                                $validationErrors
                                            );
                                        }


                                        // Sync category:
                                        $l_new_category_data_id = $l_cat_dao->sync($l_category_data_values, $this->m_object_ids[$l_object_id], $l_category_data_status);

                                        $l_category_synched = ((is_countable($l_category_changes) && count($l_category_changes)) ||
                                            $l_category_data_status === isys_import_handler_cmdb::C__CREATE) ? true : false;

                                        // Emitting signal mod.cmdb.afterCategorySync:
                                        $this->m_signals->emit(
                                            'mod.cmdb.afterCategorySync',
                                            $l_category_id,
                                            $l_category_data_values,
                                            $l_new_category_data_id,
                                            $this->m_object_ids[$l_object_id],
                                            $l_category_data_status,
                                            $l_category_type_id,
                                            $l_cat_dao
                                        );

                                        // Emit category signal (afterCategoryEntrySave).
                                        $this->m_signals->emit(
                                            "mod.cmdb.afterCategoryEntrySave",
                                            $l_cat_dao,
                                            $l_new_category_data_id,
                                            true,
                                            $this->m_object_ids[$l_object_id],
                                            $l_category_data_values,
                                            isset($l_collected_category_changes) ? $l_collected_category_changes : []
                                        );
                                    } catch (isys_exception $e) {
                                        $errorMessage = $e->getMessage();
                                        $l_new_category_data_id = false;
                                    }

                                    if ($logbookEntry !== null) {
                                        if ($errorMessage !== '') {
                                            $logbookEntry['comment'] = $errorMessage;
                                        } elseif (is_array($validationErrors)) {
                                            $logbookEntry['comment'] = implode("\n", $validationErrors);
                                            $logbookEntry['changes'] = serialize($l_category_changes);
                                        }

                                        $this->m_logbook_entries[] = $logbookEntry;
                                    }

                                    if ($l_new_category_data_id === false) {
                                        $this->m_log->warning(sprintf(
                                            '!! Syncing category %s (%s, %s) failed. %s',
                                            $l_category_name,
                                            $l_category_values['const'],
                                            $l_category_id,
                                            $errorMessage
                                        ));
                                        continue;
                                    }
                                } else {
                                    $l_category_data_ids[$l_category_type_id][$l_category_id][$l_category_data_id] = null;
                                    $this->m_log->debug(sprintf('== There is nothing to sync for the category %s skipping...', $l_category_name));
                                }
                            } //if not keep

                            if ($l_category_data_status === self::C__KEEP) {
                                $l_new_category_data_id = $l_category_data_values['data_id'];
                                $this->m_log->debug('== Dataset kept.');
                            } //if status

                            if ($l_category_synched) {
                                // Save category data's identifier for referencing categories:
                                $l_category_data_ids[$l_category_type_id][$l_category_id][$l_category_data_id] = $l_new_category_data_id;
                                // Save for statistics that categorie has changed:
                                $l_changed_categories = true;
                            }
                        } //foreach category instances
                    } // if category instances exist

                    // Create summarized logbook entry for created category entries.
                    // Skip to determine changes.
                    if ($l_changed_categories === true && $l_event_counter[self::C__CREATE] > 0 && $this->m_cat_info[$l_category_key]['is_multi-valued']) {
                        $l_serialized_changes = '';
                        if ($l_event_counter[self::C__CREATE] <= isys_tenantsettings::get('logbook.changes.multivalue-threshold', 25)) {
                            $l_serialized_changes = '';
                            if (is_array($l_collected_category_changes)) {
                                $l_serialized_changes = serialize($l_collected_category_changes);
                            }
                        }

                        $this->m_logbook_entries[] = [
                            'object_id'      => $this->m_object_ids[$l_object_id],
                            'object_type_id' => $this->m_object_type_ids[$l_object_id],
                            'category'       => $l_category_name,
                            'changes'        => $l_serialized_changes,
                            'count_changes'  => $l_event_counter[self::C__CREATE]
                        ];
                    }

                    // Update counter.
                    if ($l_changed_categories === true && $this->m_found_objects[$l_object_id] === self::C__COMPARISON__SAME) {
                        $this->setChangesInObjectIds($this->m_object_ids[$l_object_id]);
                        $this->importCounters['category_updated']++;
                    } elseif ($l_changed_categories === false && $this->m_found_objects[$l_object_id] === self::C__COMPARISON__SAME) {
                        $this->importCounters['category_skipped']++;
                    }
                } // foreach category
            }
        } // foreach object
    }

    /**
     * @param array              $p_category_data_values
     * @param array              $p_object_category_dataset
     * @param array              $p_used_properties
     * @param array              $p_comparison
     * @param array              $p_badness
     * @param integer            $p_mode
     * @param integer            $p_category_id
     * @param                    $p_unit_key
     * @param array              $p_category_data_ids
     * @param array|object       $p_local_export
     * @param boolean            $p_dataset_id_changed
     * @param integer            $p_dataset_id
     * @param isys_log           $p_logger
     * @param string             $p_category_name
     * @param string             $p_table
     * @param boolean            $p_cat_multi
     * @param integer            $p_category_type_id
     * @param array              $p_category_ids
     * @param array              $p_already_used_data_ids
     *
     * @throws isys_exception_cmdb
     */
    private function compare_category_data(
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
        &$p_already_used_data_ids = null
    ) {
        $l_count_properties = is_countable($p_category_data_values[self::C__PROPERTIES]) ? count($p_category_data_values[self::C__PROPERTIES]) : 0;
        $l_threshhold = ($l_count_properties < self::C__COMPARISON__THRESHOLD) ? ($l_count_properties - 1) : self::C__COMPARISON__THRESHOLD;

        $l_objects_setted = false;
        $l_prop_data_setted = false;
        $l_cat_ids_setted = false;
        $l_cat_data_ids_setted = false;
        $l_set_mode_setted = false;
        // Iterate through local data sets:
        foreach ($p_object_category_dataset as $l_dataset_key => $l_dataset) {
            $p_dataset_id_changed = false;
            $p_dataset_id = $l_dataset[$p_table . '__id'];

            $p_logger->debug('Comparing data for category \'' . $p_category_name . '\' in table \'' . $p_table . '\' (#' . $p_dataset_id . ')');

            // Test the category data identifier:
            // But only if data_id is set to a valid value
            if (isset($p_category_data_values['data_id']) && $p_category_data_values['data_id']) {
                if ($p_category_data_values['data_id'] !== $p_dataset_id) {
                    $p_logger->debug('  Category data identifier is different.');
                    $p_badness[$p_dataset_id]++;
                    $p_dataset_id_changed = true;

                    if ($p_mode === self::C__USE_IDS) {
                        continue;
                    }
                }
            }

            $l_helper = null;
            $l_helper_arr = [];
            // Test each property:
            foreach ($p_category_data_values[self::C__PROPERTIES] as $l_key => $l_value) {
                $l_import = $l_local = null;

                if (isset($p_used_properties[$l_key][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) {
                    $l_property_field = $p_used_properties[$l_key][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
                } else {
                    $l_property_field = $p_used_properties[$l_key][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                }

                if ($l_property_field) {
                    $l_import = $p_category_data_values['properties'][$l_key][C__DATA__VALUE];

                    /**
                     * Simply check import value against dataset value
                     */
                    if ($l_import == $l_dataset[$l_property_field]) {
                        continue;
                    }
                }

                // Need to unconvert:
                if (isset($p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]) &&
                    isset($p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1])) {
                    if (!class_exists($p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0])) {
                        throw new isys_exception_cmdb(sprintf(
                            'Import failed. Helping class %s for importing data is not available.',
                            $p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]
                        ));
                    }

                    if (!isset($l_helper_arr[$p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]])) {
                        $l_helper = new $p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0](
                            $l_dataset,
                            $this->m_db,
                            $p_used_properties[$l_key][C__PROPERTY__DATA],
                            $p_used_properties[$l_key][C__PROPERTY__FORMAT],
                            $p_used_properties[$l_key][C__PROPERTY__UI]
                        );
                        $l_helper_arr[$p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]] = $l_helper;
                        $l_objects_setted = false;
                        $l_prop_data_setted = false;
                        $l_cat_ids_setted = false;
                        $l_cat_data_ids_setted = false;
                        $l_set_mode_setted = false;
                    } else {
                        $l_helper = $l_helper_arr[$p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]];
                        $l_helper->set_row($l_dataset);
                        $l_helper->set_database($this->m_db);
                        $l_helper->set_reference_info($p_used_properties[$l_key][C__PROPERTY__DATA]);
                        $l_helper->set_format_info($p_used_properties[$l_key][C__PROPERTY__FORMAT]);
                        $l_helper->set_ui_info($p_used_properties[$l_key][C__PROPERTY__UI]);
                    }

                    if (isset($p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT])) {
                        $l_unit_key = $p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT];

                        if (method_exists($l_helper, 'set_unit_const')) {
                            $l_helper->set_unit_const($p_used_properties[self::C__PROPERTIES][$l_unit_key]['const']);
                        }
                    }

                    if (method_exists($l_helper, 'set_object_ids') && !$l_objects_setted) {
                        $l_helper->set_object_ids($this->m_object_ids);
                        $l_objects_setted = true;
                    }

                    if (method_exists($l_helper, 'set_property_data') && !$l_prop_data_setted) {
                        $l_helper->set_property_data($p_category_data_values[self::C__PROPERTIES]);
                        $l_prop_data_setted = true;
                    }

                    if (method_exists($l_helper, 'set_category_ids') && !$l_cat_ids_setted) {
                        $l_helper->set_category_ids($p_category_ids);
                        $l_cat_ids_setted = true;
                    }

                    if (method_exists($l_helper, 'set_category_data_ids') && !$l_cat_data_ids_setted) {
                        $l_helper->set_category_data_ids($p_category_data_ids);
                        $l_cat_data_ids_setted = true;
                    }

                    if (method_exists($l_helper, 'set_mode') && !$l_set_mode_setted) {
                        $l_helper->set_mode($p_mode);
                        $l_set_mode_setted = true;
                    }

                    $l_helper_method = $p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] . '_import';
                    if (!method_exists($l_helper, $l_helper_method)) {
                        $p_logger->warning(sprintf(
                            'Helping method %s for importing data is not available. Method was expected in class %s.',
                            $l_helper_method,
                            $p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]
                        ));

                        if (isset($p_category_data_values[self::C__PROPERTIES][$l_key]['sysid'])) {
                            // Object reference:
                            $p_category_data_values[self::C__PROPERTIES][$l_key][C__DATA__VALUE] = $this->m_object_ids[$p_category_data_values[self::C__PROPERTIES][$l_key]['id']];
                            $p_logger->info('It is an object reference.');
                        } else {
                            // Category reference:
                            $l_id_set = false;
                            $l_type_set = false;
                            if (is_array($p_category_data_values[self::C__PROPERTIES][$l_key][C__DATA__VALUE])) {
                                foreach ($p_category_data_values[self::C__PROPERTIES][$l_key][C__DATA__VALUE] as $l_key2 => $l_val) {
                                    if (is_array($l_val)) {
                                        foreach ($l_val as $l_key3 => $l_val2) {
                                            if ($l_key3 === 'id') {
                                                $l_id_set = $l_val2;
                                            }

                                            if ($l_key3 === 'type') {
                                                $l_type_set = $l_val2;
                                            }
                                        }
                                    } else {
                                        if ($l_key2 === 'id') {
                                            $l_id_set = $l_val;
                                        }

                                        if ($l_key2 === 'type') {
                                            $l_type_set = $l_val;
                                        }
                                    }

                                    if ($l_id_set && $l_type_set) {
                                        break;
                                    }
                                }
                            } else {
                                foreach ($p_category_data_values[self::C__PROPERTIES][$l_key] as $l_key2 => $l_val) {
                                    if ($l_key2 === 'id') {
                                        $l_id_set = $l_val;
                                    }

                                    if ($l_key2 === 'type') {
                                        $l_type_set = $l_val;
                                    }

                                    if ($l_id_set && $l_type_set) {
                                        break;
                                    }
                                }
                            }

                            if ($l_id_set && $l_type_set) {
                                if (!is_numeric($l_type_set)) {
                                    $l_type_set = constant($l_type_set);
                                }

                                if (isset($l_category_data_ids[$p_category_type_id][$l_type_set][$l_id_set])) {
                                    $p_category_data_values[self::C__PROPERTIES][$l_key][C__DATA__VALUE] = $l_category_data_ids[$p_category_type_id][$l_type_set][$l_id_set];
                                    $p_logger->info('It is a category reference.');
                                } else {
                                    $p_category_data_values[self::C__PROPERTIES][$l_key][C__DATA__VALUE] = null;
                                }
                            }
                        }
                    } else {

                        // Initiate helper class:
                        if (!class_exists($p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0])) {
                            $p_logger->debug(sprintf(
                                'Export helper class does not exist: %s. Skipping.',
                                $p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]
                            ));
                            continue;
                        }

                        if ($p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'connection') {
                            $l_local = $l_dataset['isys_connection__isys_obj__id'];
                        } else {
                            $l_local = $l_dataset[$l_property_field];
                        }

                        // Initiate variables to save original unconcerted values:
                        $l_import_unconverted = null;
                        $l_local_unconverted = null;

                        // If values has been converted, convert the local value to the right unit:
                        if (isset($p_category_data_values['properties'][$l_key][C__DATA__VALUE . '_converted'])) {
                            $l_import_unconverted = $l_import;
                            $l_local_unconverted = $l_helper->$l_helper_method([
                                C__DATA__VALUE => $l_local,
                                C__DATA__TAG   => $l_key
                            ]);
                            $l_import = $p_category_data_values['properties'][$l_key][C__DATA__VALUE . '_converted'];
                        } else {
                            if (isset($p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1])) {
                                $l_import = $l_helper->$l_helper_method($l_value);

                                // If data can only be retrieved by callback then we have to get the local data by the callback method
                                // @todo find a better solution for this
                                if ($p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] != 'connection' &&
                                    $p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] != 'get_yes_or_no') {
                                    if ($p_used_properties[$l_key][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] != 'isys_nagios_service') {
                                        $p_local_export = call_user_func([
                                            $l_helper,
                                            $p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1]
                                        ], $l_local);
                                    }
                                    if (isset($p_local_export)) {
                                        $l_local_export_data = null;
                                        if (is_object($p_local_export)) {
                                            $l_local_export_data = $p_local_export->get_data();
                                        } else {
                                            $l_local_export_data = $p_local_export;
                                        }
                                        if (is_countable($l_local_export_data) && count($l_local_export_data) > 0) {
                                            if (is_array($l_local_export_data[0])) {
                                                $l_local_export_data = $l_local_export_data[0];
                                            }
                                            if (is_array($l_local_export_data)) {
                                                if (isset($l_local_export_data['ref_id'])) {
                                                    if ($p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'exportIpReference') {
                                                        $l_local = $l_local_export_data['ref_title'];
                                                    } else {
                                                        $l_local = $l_local_export_data['ref_id'];
                                                    }
                                                } else {
                                                    $l_local = $l_local_export_data['id'];
                                                }
                                            }
                                        }
                                    }

                                    unset($p_local_export);
                                }

                                if ($l_import === false) {
                                    $p_logger->warning(sprintf(
                                        'Transformation failed (method %s from class %s in category %s).',
                                        $l_helper_method,
                                        $p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0],
                                        $p_category_name
                                    ));
                                }

                                $p_category_data_values['properties'][$l_key][C__DATA__VALUE] = $l_import;
                            }
                        } // if converted

                        // contacts are always different because the contact id is always new
                        if (gettype($l_import) != 'array' && $p_used_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] != 'contact') {
                            $l_local_check = trim((string)$l_local);
                            $l_import_check = trim((string)$l_import);

                            // Check only if local value is set or import value
                            if ((!empty($l_local_check) || !empty($l_import_check)) && $l_local != $l_import) {
                                if ((isset($l_value['const']) && $l_value['title_lang'])) {
                                    $l_import = isys_application::instance()->container->get('language')
                                        ->get($l_value['title_lang']);

                                    $l_local_arr = $l_helper->dialog_plus(
                                        $l_dataset[$l_property_field],
                                        $p_used_properties[$l_key][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]
                                    );
                                    $l_local = isys_application::instance()->container->get('language')
                                        ->get($l_local_arr['title_lang']);
                                }

                                if ($l_local != $l_import) {
                                    $p_logger->debug('  Property ' . $l_key . ' is different.');
                                    $p_badness[$p_dataset_id]++;
                                }
                            }
                        }

                        unset($l_helper, $l_import_unconverted, $l_local_unconverted);
                    }
                } else {
                    // Check normal fields for badness points
                    $l_import = $p_category_data_values[self::C__PROPERTIES][$l_key][C__DATA__VALUE];
                    if (isset($p_used_properties[$l_key][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) {
                        if (array_key_exists($p_used_properties[$l_key][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS], $l_dataset)) {
                            $l_local = $l_dataset[$p_used_properties[$l_key][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]];
                        }
                    } else {
                        $l_local = $l_dataset[$p_used_properties[$l_key][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
                    }

                    if ($l_local != $l_import) {
                        $p_logger->debug('Property ' . $l_key . ' is different.');

                        $p_badness[$p_dataset_id]++;
                    }
                }
            }

            if ($p_badness[$p_dataset_id] == 0 || ($l_threshhold == 0 && $p_badness[$p_dataset_id] == 1 && $p_category_data_values['data_id'] == null)) {
                // We found our dataset
                $p_logger->debug('  Dataset and category data are the same.');
                $p_comparison[self::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;

                return;
            } elseif ($p_badness[$p_dataset_id] > $l_threshhold && $p_cat_multi) {
                $p_logger->debug('  Dataset differs completly from category data.');
                $p_comparison[self::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
            } // @todo check badness again
            else {
                $p_logger->debug('  Dataset differs partly from category data.');
                $p_comparison[self::C__COMPARISON__PARTLY][$l_dataset_key] = $p_dataset_id;
            }
        } //foreach data set
    }

    /**
     * Compares two objects whether they are the same. One comes from the export
     * the another from the database. Used by $this->import().
     *
     * @param   resource $p_database_object Object from database
     * @param   array    & $p_import_object Reference to object from export
     * @param   integer  & $p_object_id     Reference to object identifier
     * @param integer    & $p_object_status Optional. Use this object status based on comparison and settings. Defaults to null (no change).
     *
     * @return  integer  Result
     */
    private function compare_objects($p_database_object, &$p_import_object, &$p_object_id, &$p_object_status = null)
    {
        $l_badness = 0;
        $l_title_type_exists = 0;

        if (!$p_database_object) {
            $p_object_id = -1;

            return self::C__COMPARISON__DIFFERENT;
        }
        if ($p_database_object->num_rows() !== 1) {
            $this->m_log->debug('No object found.');
            $l_badness += 1000;
        }

        $l_existing_object = $p_database_object->get_row();

        // I don´t know why but somehow $l_existing_object is sometimes empty
        if (empty($l_existing_object)) {
            $p_database_object = $p_database_object->requery();
            $l_existing_object = $p_database_object->get_row();
        }

        // Rating:
        if ((int)$l_existing_object['isys_obj__id'] !== (int)$p_import_object['id']) {
            $this->m_log->debug('No object with that ID found.');
            $l_badness++;
        }

        $l_type = null;

        if (is_array($p_import_object['type']) && isset($p_import_object['type']['const'])) {
            if (defined($p_import_object['type']['const'])) {
                $l_type = constant($p_import_object['type']['const']);
            } else {
                $l_type = $this->m_cmdb_dao->get_objtype_id_by_const_string($p_import_object['type']['const']);
            }
        } elseif (isset($p_import_object['type_id'])) {
            $l_type = $p_import_object['type_id'];
        } elseif (is_string($p_import_object['type'])) {
            foreach ($this->m_object_types as $l_id => $l_const) {
                if ($l_const === $p_import_object['type']) {
                    $l_type = $l_id;
                    break;
                }
            }
        }

        if ($l_existing_object['isys_obj__isys_obj_type__id'] != $l_type) {
            $this->m_log->debug('Object type is different.');
            $l_badness += 1000;
        } else {
            $l_title_type_exists++;
        }

        $l_title = '';

        if (isset($p_import_object[C__DATA__VALUE])) {
            if (is_array($p_import_object[C__DATA__VALUE])) {
                $l_title = array_map('trim', $p_import_object[C__DATA__VALUE]);
            } else {
                $l_title = trim($p_import_object[C__DATA__VALUE]);
            }
        } elseif (isset($p_import_object[C__DATA__TITLE])) {
            $l_title = trim($p_import_object[C__DATA__TITLE]);
        }

        if (is_array($l_title)) {
            while (is_array($l_title)) {
                $l_title = (array_key_exists(C__DATA__VALUE, $l_title)) ? $l_title[C__DATA__VALUE] : array_pop($l_title);
            }
        }

        if (strcasecmp($l_existing_object['isys_obj__title'], $l_title) !== 0) {
            $this->m_log->debug('Object title is different.');
            $l_badness++;
        } else {
            $l_title_type_exists++;
        }

        if (strcasecmp($l_existing_object['isys_obj__sysid'], $p_import_object['sysid']) !== 0) {
            $this->m_log->debug('Object SYSID is different.');

            if ($l_title_type_exists == 2) {
                $l_badness++;
            } else {
                $l_badness += 1000;
            }
        }

        // @todo Maybe we should think about a more "compact" structure than this 8-dimensional array here...
        if (defined('C__CATG__GLOBAL') && isset($p_import_object['category_types'][C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__GLOBAL')][self::C__CATEGORY_ENTITIES][$p_object_id][self::C__PROPERTIES]['description'][C__DATA__VALUE]) &&
            $l_existing_object['isys_obj__description'] !==
            $p_import_object['category_types'][C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__GLOBAL')][self::C__CATEGORY_ENTITIES][$p_object_id][self::C__PROPERTIES]['description'][C__DATA__VALUE]) {
            $this->m_log->debug('Object description is different.');
            $l_badness++;
        }

        if (isset($p_import_object['status']) && ((bool)isys_tenantsettings::get('import.object.keep-status')) === false) {
            if ($l_existing_object['isys_obj__status'] != $p_import_object['status']) {
                $this->m_log->debug('Object status is different.');
                // use status from the import
                $p_object_status = $p_import_object['status'];
                $l_badness++;
            }
        }

        /*
        if (isset($p_import_object['cmdb_status']))
        {
            if ($l_existing_object['isys_obj__isys_cmdb_status__id'] != $p_import_object['cmdb_status'])
            {
                $this->m_log->debug('Object CMDB status is different.');
                $l_badness++;
            }
        }
        */

        // Scoring:
        if ($l_badness === 0) {
            $l_result = self::C__COMPARISON__SAME;
        } elseif ($l_badness >= self::C__COMPARISON__THRESHOLD) {
            $p_object_id = -1;
            $l_result = self::C__COMPARISON__DIFFERENT;
        } else {
            $p_object_id = $l_existing_object['isys_obj__id'];
            $l_result = self::C__COMPARISON__PARTLY;
        }

        // Not set yet? Hurry up, dude:
        if (!isset($p_object_status)) {
            $p_object_status = $p_import_object['status'];
        }

        return $l_result;
    }

    /**
     * Add a custom/specific/global category
     * to ignore list
     *
     * @param type $p_categoryID ID or Constant of the Category
     */
    private function ignore_category($p_category_typeID, $p_categoryID)
    {
        if (isset($this->m_ignored_categories[$p_category_typeID])) {
            // Exists the delivered Constants ?
            if (is_string($p_categoryID) && defined($p_categoryID)) {
                $p_category_typeID = constant($p_categoryID);
            }

            if (is_numeric($p_categoryID) && !in_array($p_categoryID, $this->m_ignored_categories[$p_category_typeID])) {
                $this->m_ignored_categories[$p_category_typeID][] = $p_categoryID;
            }
        } else {
            // CategoryType does not exist
        }
    }

    /**
     * Method which merges missing data form the category
     *
     * @param $p_category_data_values
     * @param $p_object_category_dataset
     * @param $p_category_key
     * @param $p_dataset_key
     * @param $p_category_ids
     * @param $p_category_data_ids
     * @param $p_mode
     * @param $p_helper_arr
     */
    private function merge_missing_data(
        &$p_category_data_values,
        $p_object_category_dataset,
        $p_category_key,
        $p_dataset_key,
        $p_category_ids,
        $p_category_data_ids,
        $p_mode,
        &$p_helper_arr,
        $p_class_name = ''
    ) {
        // Iterate through the property infos:
        foreach ($this->m_cat_info[$p_category_key]['property_info'] as $l_property_key => $l_property_value) {
            if ($l_property_value[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__IMPORT]) {
                $l_table_value = null;
                $l_local_value_arr = [];
                if (empty($p_category_data_values['properties'][$l_property_key][C__DATA__VALUE]) ||
                    in_array($p_category_data_values['properties'][$l_property_key][C__DATA__VALUE], $this->m_possible_empty_values)) {
                    if (isset($l_property_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) {
                        $l_property_field = $l_property_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
                    } else {
                        $l_property_field = $l_property_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                    }

                    if (isset($p_object_category_dataset[$p_dataset_key]) && is_array($p_object_category_dataset[$p_dataset_key]) &&
                        isset($p_object_category_dataset[$p_dataset_key][$l_property_field])) {
                        $l_table_value = (isset($p_object_category_dataset[$p_dataset_key][$l_property_field])) ? $p_object_category_dataset[$p_dataset_key][$l_property_field] : null;

                        if ($l_table_value !== null) {
                            if (isset($l_property_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1])) {
                                // @todo find a better solution. In sync methods an object ID is needed for connections
                                if ($l_property_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'connection') {
                                    $l_table_value = $p_object_category_dataset[$p_dataset_key]['isys_connection__isys_obj__id'];
                                } else {

                                    // This part is needed otherwise the wrong values will be taken for the merging
                                    if (!isset($l_property_value[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][1]) ||
                                        !strpos($l_property_field, $l_property_value[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][1])) {
                                        if (!isset($p_helper_arr[$l_property_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]])) {
                                            $l_helper = new $l_property_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0](
                                                $p_object_category_dataset[$p_dataset_key],
                                                $this->m_db,
                                                $l_property_value[C__PROPERTY__DATA],
                                                $l_property_value[C__PROPERTY__FORMAT],
                                                $l_property_value[C__PROPERTY__UI]
                                            );
                                            $p_helper_arr[$l_property_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]] = $l_helper;
                                        } else {
                                            $l_helper = $p_helper_arr[$l_property_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]];
                                            $l_helper->set_row($p_object_category_dataset[$p_dataset_key]);
                                            $l_helper->set_reference_info($l_property_value[C__PROPERTY__DATA]);
                                            $l_helper->set_format_info($l_property_value[C__PROPERTY__FORMAT]);
                                            $l_helper->set_ui_info($l_property_value[C__PROPERTY__UI]);
                                        }

                                        if (isset($l_property_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT])) {
                                            $l_unit_key = $l_property_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT];

                                            if (method_exists($l_helper, 'set_unit_const')) {
                                                $l_helper->set_unit_const($p_category_data_values['properties'][$l_unit_key]['const']);
                                            }
                                        }

                                        if (method_exists($l_helper, 'set_object_ids')) {
                                            $l_helper->set_object_ids($this->m_object_ids);
                                        }

                                        if (method_exists($l_helper, 'set_property_data')) {
                                            $l_helper->set_property_data($p_category_data_values['properties']);
                                        }

                                        if (method_exists($l_helper, 'set_category_ids')) {
                                            $l_helper->set_category_ids($p_category_ids);
                                        }

                                        if (method_exists($l_helper, 'set_category_data_ids')) {
                                            $l_helper->set_category_data_ids($p_category_data_ids);
                                        }

                                        if (method_exists($l_helper, 'set_mode')) {
                                            $l_helper->set_mode($p_mode);
                                        }

                                        $l_local_export = call_user_func([
                                            $l_helper,
                                            $l_property_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1]
                                        ], $l_table_value);

                                        if ($l_local_export) {
                                            if (is_object($l_local_export)) {
                                                $l_local_export_data = $l_local_export->get_data();
                                            } else {
                                                $l_local_export_data = $l_local_export;
                                            }
                                            if (is_array($l_local_export_data) && count($l_local_export_data) > 0) {
                                                if ($l_property_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'assigned_connector' ||
                                                    $l_property_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'exportDnsServer') {
                                                    $l_local_value_arr[C__DATA__VALUE][0] = array_pop($l_local_export_data);
                                                } else {
                                                    foreach ($l_local_export_data as $l_local_key => $l_local_value) {
                                                        if (is_array($l_local_value) && isset($l_local_value['id'])) {
                                                            $l_local_value_arr[C__DATA__VALUE][$l_local_key] = $l_local_value['id'];
                                                        } else {
                                                            $l_local_value_arr[C__DATA__VALUE][$l_local_key] = $l_local_value;
                                                        }
                                                    }
                                                }
                                                $l_import_method = $l_property_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] . '_import';

                                                $l_table_value = $l_helper->$l_import_method($l_local_value_arr);

                                                if ($l_table_value === null) {
                                                    $l_table_value = $l_helper->$l_import_method([C__DATA__VALUE => $l_local_export_data]);
                                                }
                                            } else {
                                                $l_import_method = $l_property_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] . '_import';
                                                $l_table_value = $l_helper->$l_import_method([C__DATA__VALUE => $l_local_export_data]);
                                            }
                                        } else {
                                            $l_table_value = null;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    // Append missing data:
                    if ((!isset($p_category_data_values['properties'][$l_property_key]) || !isset($p_category_data_values['properties'][C__DATA__VALUE])) &&
                        $l_table_value !== null) {
                        $p_category_data_values['properties'][$l_property_key][C__DATA__VALUE] = $l_table_value;
                        // Don't ask why:
                        $p_category_data_values['properties'][$l_property_key][C__DATA__VALUE . '_converted'] = $l_table_value;
                    }
                    unset($l_table_value);
                } elseif (isset($p_category_data_values['properties'][$l_property_key]['sysid']) &&
                    !is_numeric($p_category_data_values['properties'][$l_property_key][C__DATA__VALUE])) {
                    // This case is if the value is not a numeric value and the property is an object reference.
                    // Otherwise the connection won´t be updated in the sync method
                    $p_category_data_values['properties'][$l_property_key][C__DATA__VALUE] = $this->m_object_ids[$p_category_data_values['properties'][$l_property_key]['id']];
                }
            }
        }
    }

    /**
     * This method checks if there are any validation errors in the category data.
     * In case that there are any validation errors the category data which will be synched is being stripped.
     *
     * @param isys_cmdb_dao_category $dao          Category DAO
     * @param array                  $categoryData Reference of the category data which will be synced
     * @param array                  $categoryChanges
     *
     * @return array|mixed
     * @throws Exception
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function validateCategoryDataBeforeSync(isys_cmdb_dao_category $dao, array &$categoryData, array &$categoryChanges)
    {
        $validateData = [];
        foreach ($categoryData['properties'] as $key => $data) {
            $validateData[$key] = $data[C__DATA__VALUE];

            if (isset($data['ref_title'])) {
                $validateData[$key] = $data['ref_title'];
            }
        } // foreach property

        $validationErrors = $dao->validate($validateData);

        if (is_array($validationErrors)) {
            // Iterate through each validation error
            foreach ($validationErrors as $propertyKey => $validationMessage) {
                $property = $dao->get_property_by_key($propertyKey);
                $attribute = isys_application::instance()->container->get('language')
                    ->get($property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]);

                $validationErrors[$propertyKey] = $attribute . ': ' . $validationMessage;
                // Empty value so that the dataset can be synced without any errors
                $categoryData['properties'][$propertyKey][C__DATA__VALUE] = null;
                // Remove property from changes
                unset($categoryChanges[get_class($dao) . '::' . $propertyKey]);
            }
        }

        return $validationErrors;
    }

    public function __construct($p_log, $p_db, $p_cmdb_dao = null)
    {
        $this->m_ignored_categories[C__CMDB__CATEGORY__TYPE_GLOBAL] = defined_or_default('C__CATG__LOGBOOK');
        $this->m_logbook_source = defined_or_default('C__LOGBOOK_SOURCE__IMPORT');
        parent::__construct($p_log, $p_db);

        $p_log->set_destruct_flush((bool)isys_tenantsettings::get('logging.cmdb.import', false));

        $this->m_cmdb_dao = ($p_cmdb_dao === null) ? isys_cmdb_dao::instance($p_db) : $p_cmdb_dao;
        $this->importStartTime = microtime(true);

        self::$m_stored_variable_name = microtime();
        self::$m_overwrite_ip_conflicts = false;
    }
}
