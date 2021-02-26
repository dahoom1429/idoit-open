<?php

use idoit\AddOn\AuthableInterface;
use idoit\Component\Helper\Ip;

/**
 * i-doit
 *
 * JDisc module
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-9
 */
class isys_module_jdisc extends isys_module implements AuthableInterface
{
    // Defines whether this module will be displayed in the named menus:
    const DISPLAY_IN_MAIN_MENU   = false;
    const DISPLAY_IN_SYSTEM_MENU = true;

    // Currently supported JDisc Version
    const C__MODULE__JDISC__VERSION = 3.0;

    /**
     * Root node.
     */
    const C__ROOT = 'jdisc';

    /**
     * Node for import.
     */
    const C__IMPORT = 'import';

    /**
     * Parameter name to handle entities.
     */
    const C__ENTITY = 'entity';

    /**
     * No nav mode.
     */
    const C__NAVMODE__NONE = 0;

    /**
     * Constant for tree node JDisc-Configuration
     */
    const C__MODULE__JDISC__TREE_LIST_CONFIGURATION = 9;

    /**
     * Constant for tree node JDisc-Profile
     */
    const C__MODULE__JDISC__TREE_LIST_PROFILES = 10;

    /**
     * Import Mode Create
     */
    const C__IMPORT_MODE__CREATE = 1;

    /**
     * Import Mode Updat
     */
    const C__IMPORT_MODE__UPDATE = 2;

    /**
     * Import Mode Overwrite
     */
    const C__IMPORT_MODE__OVERWRITE = 3;

    /**
     * @var bool
     */
    protected static $m_licenced = true;

    /**
     * Current action (based on navigation mode).
     *
     * @var  integer
     */
    protected $m_action;

    /**
     * Flag to define if all blade connections shall be imported.
     *
     * @var  boolean
     */
    protected $m_all_blade_connections;

    /**
     * Flag to define if all clusters shall be imported.
     *
     * @var  boolean
     */
    protected $m_all_clusters;

    /**
     * Flag to define if all networks shall be imported.
     *
     * @var  boolean
     */
    protected $m_all_networks;

    /**
     * Defines if all objects without title should be imported or not
     *
     * @var
     */
    protected $m_all_no_title_objects;

    /**
     * Flag to define if all software shall be imported.
     *
     * @var  boolean
     */
    protected $m_all_software;

    /**
     * Flag to define if software licences shall be considered while importing software relations
     *
     * @var boolean
     */
    protected $m_software_licences;

    /**
     * Flag to define if software services shall be considered while importing software relations
     *
     * @var boolean
     */
    protected $importSoftwareServices;

    /**
     * Cache array for profiles.
     *
     * @var  array
     */
    protected $m_cached_profile;

    /**
     * All chassis types in JDisc
     */
    protected $m_chassis_types = [];

    /**
     * Caches all found clusters
     *
     * @var
     */
    protected $m_cluster_cache;

    /**
     * Instance of module DAO.
     *
     * @var  isys_jdisc_dao
     */
    public $m_dao;

    /**
     * Instance of database component
     *
     * @var isys_component_database
     */
    protected $m_db;

    /**
     * Current entity.
     *
     * @var  integer
     */
    protected $m_entity;

    /**
     * @var isys_export_cmdb_object
     */
    protected $m_export_obj = null;

    /**
     * Current group ID
     *
     * @var
     */
    protected $m_group_id;

    /**
     * Flag to define if custom attributes shall be imported
     *
     * @var boolean
     */
    protected $m_import_custom_attributes;

    /**
     * Instance of import module.
     *
     * @var  isys_module_import
     */
    protected $m_import_module;

    /**
     * Flag to define if jdisc server is a jedi version
     *
     * @var
     */
    protected $m_is_jedi;

    /**
     * Instance of logger.
     *
     * @var  isys_log
     */
    protected $m_log;

    /**
     * Array with Management Device connections
     *
     * @var array
     */
    protected $m_management_device_con_arr = [];

    /**
     * Template module
     *
     * @var isys_module_templates
     */
    protected $m_mod_template = null;

    /**
     * Mode for import
     *
     * @var    integer
     */
    protected $m_mode;

    /**
     * Module identifier.
     *
     * @var  integer
     */
    protected $m_module_id;

    /**
     * Current node.
     *
     * @var  integer
     */
    protected $m_node;

    /**
     * Nodes.
     *
     * @var  array
     */
    protected $m_nodes;

    /**
     * Found objects
     *
     * @var
     */
    protected $m_objects = [];

    /**
     * Current profile ID
     *
     * @var
     */
    protected $m_profile_id;

    /**
     * Current jdisc server id
     *
     * @var int
     */
    protected $m_server_id = null;

    /**
     * Flag do define if import considers default templates from object types
     *
     * @var boolean
     */
    protected $m_used_default_templates;

    /**
     * User request.
     *
     * @var  isys_module_request
     */
    protected $m_userrequest;

    /**
     * Array with VM connections.
     *
     * @var  array
     */
    protected $m_vm_con_arr = [];

    /**
     * @var bool
     */
    public $importCloudSubscriptions = false;

    /**
     * @var bool
     */
    private $importConnectionEndpoint = false;

    /**
     * Cache objtypes with the template id
     *
     * @var array
     */
    private $m_obj_type_tpls = [];

    /**
     * Cache of unknown device IDs which do not exist in the i-doit system so that we don´t have to search for them again
     *
     * @var array
     */
    private static $m_unknownDeviceIDs = [];

    /**
     * @var bool
     */
    private $m_update_objtype = false;

    /**
     * @var array Consts for title transformation select
     */
    protected $titleTransformation = [
        isys_ajax_handler_jdisc::C__JDISC__TITLE_TRANSFORM__AS_IS => 'LC__JDISC__TITLE_TRANSFORM__AS_IS',
        isys_ajax_handler_jdisc::C__JDISC__TITLE_TRANSFORM__UPPERCASE => 'LC__JDISC__TITLE_TRANSFORM__UPPERCASE',
        isys_ajax_handler_jdisc::C__JDISC__TITLE_TRANSFORM__LOWERCASE => 'LC__JDISC__TITLE_TRANSFORM__LOWERCASE'
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->m_module_id = defined_or_default('C__MODULE__JDISC');

        $this->m_log = isys_factory_log::get_instance('import_jdisc')->set_destruct_flush(false);
        $this->m_db = isys_application::instance()->container->get('database');
        $this->m_dao = new isys_jdisc_dao($this->m_db, $this->m_log);
        $this->m_import_module = new isys_module_import();
    }

    /**
     * @return bool
     */
    public function isImportConnectionEndpoint(): bool
    {
        return $this->importConnectionEndpoint;
    }

    /**
     * Checks if device ID is unknown
     *
     * @param $p_value
     *
     * @return mixed
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function isNonExisting($p_value)
    {
        return static::$m_unknownDeviceIDs[$p_value];
    }

    /**
     * Set device ID as unknown
     *
     * @param $p_value
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function setNonExistingID($p_value)
    {
        static::$m_unknownDeviceIDs[$p_value] = true;
    }

    /**
     * Static factory method for instant method chaining.
     *
     * @static
     * @return  isys_module_jdisc
     */
    public static function factory()
    {
        return new self;
    }

    /**
     * Checks whether all requirements for this module are met.
     */
    public function check_requirements()
    {
        // PDO extension:
        if (!class_exists('PDO')) {
            isys_application::instance()->container['notify']->error($this->language->get('LC__PDO__NOT_AVAILABLE'));
        }
    }

    /**
     * @return array
     */
    public function get_cached_profile()
    {
        return $this->m_cached_profile;
    }

    /**
     * Method for retrieving the PDO.
     *
     * @return  isys_component_database_pdo
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_connection($p_config_id = null)
    {
        return $this->m_dao->get_connection($p_config_id);
    }

    /**
     * Setter for m_mode (import mode). Possible constants:
     * - C__APPEND
     * - C__MERGE
     * - C__OVERWRITE
     *
     * @param   integer $p_mode
     *
     * @return  isys_module_jdisc
     */
    public function set_mode($p_mode)
    {
        $this->m_mode = $p_mode;

        return $this;
    }

    /**
     * Getter for m_mode (import mode).
     *
     * @return  integer
     */
    public function get_mode()
    {
        return $this->m_mode;
    }

    /**
     * Import method.
     *
     * @param   integer $p_group
     * @param   integer $p_profile
     * @param   mixed   $p_id
     *
     * @return  PDOStatement
     * @author    Leonard Fischer <lfischer@i-doit.org>
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function retrieve_object_result($p_group, $p_profile, $p_id = null, $p_ids_only = false)
    {
        // Create object type assignments by given profile-ID.
        $l_raw_assignments = $this->m_dao->get_object_type_assignments_by_profile($p_profile);

        // Ignore empty object type assignments:

        $l_assignments = [];

        /**
         * @var $l_dao isys_cmdb_dao_jdisc
         */
        $l_dao = isys_cmdb_dao_jdisc::instance($this->m_db);
        $l_chassis_types = [];
        if (defined('C__CATS__CHASSIS')) {
            $l_res = $l_dao->get_objtype_by_cats_id(C__CATS__CHASSIS);
            while ($l_row = $l_res->get_row()) {
                $l_chassis_types[] = $l_row['isys_obj_type__id'];
            }
        }

        $l_activated_objtypes_res = $l_dao->get_objtype(null, false, C__RECORD_STATUS__NORMAL);
        while ($l_row = $l_activated_objtypes_res->get_row()) {
            $l_active_objtypes[$l_row['isys_obj_type__id']] = true;
        }

        foreach ($l_raw_assignments as $l_key => $l_values) {
            if (($l_values['jdisc_type'] === null && $l_values['jdisc_type_customized'] === '' && $l_values['jdisc_os'] === null && $l_values['jdisc_os_customized'] === '' &&
                    $l_values['object_type'] === null) || !isset($l_active_objtypes[$l_values['object_type']])) {
                continue;
            }

            $l_assignments[$l_key] = $l_values;

            if (in_array($l_values['object_type'], $l_chassis_types)) {
                $this->m_chassis_types[] = $l_values['jdisc_type'];
            }

            if ($l_values['port_filter_type'] != null) {
                $l_arr = [];
                if (!empty($l_values['jdisc_type_customized'])) {
                    $l_type = $l_values['jdisc_type_customized'];
                } else {
                    $l_type = $l_values['jdisc_type'];
                }

                if (!empty($l_values['jdisc_os_customized'])) {
                    $l_os = $l_values['jdisc_os_customized'];
                } else {
                    $l_os = (!empty($l_values['jdisc_os'])) ? $l_values['jdisc_os'] : '*';
                    if (is_numeric($l_os) && $l_os > 0) {
                        $l_osdata = $this->m_dao->get_jdisc_operating_systems('osversion', $l_os);
                        if (isset($l_osdata[0]) && is_array($l_osdata[0])) {
                            $l_os = current($l_osdata[0]);
                        }
                    }
                }

                if (is_scalar($l_os)) {
                    $l_cached_arr = isys_jdisc_dao_network::instance($this->m_db)
                        ->get_port_filter();
                    if (count($l_cached_arr) > 0) {
                        $l_cached_arr[$l_type][$l_os] = $l_values['port_filter'];
                        $l_arr = $l_cached_arr;
                    } else {
                        $l_arr[$l_type][$l_os] = $l_values['port_filter'];
                    }
                    isys_jdisc_dao_network::instance($this->m_db)
                        ->set_port_filter($l_arr);

                    $l_cached_arr = isys_jdisc_dao_network::instance($this->m_db)
                        ->get_port_filter_import_type();
                    if (count($l_cached_arr) > 0) {
                        $l_cached_arr[$l_type][$l_os] = $l_values['port_filter_type'];
                        $l_arr = $l_cached_arr;
                    } else {
                        $l_arr[$l_type][$l_os] = $l_values['port_filter_type'];
                    }
                    isys_jdisc_dao_network::instance($this->m_db)
                        ->set_port_filter_import_type($l_arr);
                }
            }
        }

        if (count($l_assignments) === 0) {
            $l_assignments = null;
        }

        // Retrieve devices to our given group.
        return isys_jdisc_dao_devices::instance($this->m_db)->get_devices_by_profile($p_group, $p_id, $l_assignments, $p_ids_only);
    }

    /**
     * Sets clear mode
     *
     * @param $p_value
     *
     * @return $this
     */
    public function set_clear_mode($p_value = null)
    {
        if ($p_value == isys_import_handler_cmdb::C__OVERWRITE) {
            isys_jdisc_dao_data::activate_clear_mode();
        } else {
            isys_jdisc_dao_data::deactivate_clear_mode();
        }

        return $this;
    }

    /**
     * Import method.
     *
     * @return  isys_module_jdisc
     *
     * @param   integer $p_profile
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function prepare_environment($p_profile, $p_group = null)
    {
        isys_cmdb_dao_category_g_identifier::set_identifier_key('deviceid-' . $this->m_server_id);
        isys_cmdb_dao_category_g_identifier::set_identifier_type(defined_or_default('C__CATG__IDENTIFIER_TYPE__JDISC'));
        $this->m_mod_template = new isys_module_templates();
        $this->m_export_obj = new isys_export_cmdb_object('isys_export_type_xml', $this->m_db);

        // Get the profile we shall use.
        $this->m_cached_profile = current($this->m_dao->get_profile($p_profile));
        $l_cached_categories = unserialize($this->m_cached_profile['categories']);

        $softwareDao = isys_jdisc_dao_software::instance($this->m_db);
        $deviceDao = isys_jdisc_dao_devices::instance($this->m_db);

        if (isset($this->m_cached_profile['software_filter']) && $this->m_cached_profile['software_filter'] !== '') {
            if (isset($this->m_cached_profile['software_filter_type_regexp']) && (int)$this->m_cached_profile['software_filter_type_regexp'] === 1) {
                $softwareDao->set_software_filter(
                    $this->m_cached_profile['software_filter'],
                    $this->m_cached_profile['software_filter_type'],
                    1
                );
            } else {
                $softwareDao->set_software_filter($this->m_cached_profile['software_filter'], $this->m_cached_profile['software_filter_type']);
            }
        }

        if (isset($this->m_cached_profile['chassis_assigned_modules_objtype']) && $this->m_cached_profile['chassis_assigned_modules_objtype'] > 0) {
            $deviceDao->set_module_objecttype($this->m_cached_profile['chassis_assigned_modules_objtype']);
        }

        if (isset($this->m_cached_profile['chassis_assigned_modules_update_objtype'])) {
            $deviceDao->setModuleUpdateObjtype((bool)$this->m_cached_profile['chassis_assigned_modules_update_objtype']);
        }

        if (is_array($l_cached_categories)) {
            $this->m_cached_profile['categories'] = array_flip($l_cached_categories);
        } else {
            $this->m_cached_profile['categories'] = null;
        }

        // Get all virtual machine to host connections only if the category is selected.
        if (is_array($this->m_cached_profile['categories'])) {
            if (defined('C__CATG__VIRTUAL_MACHINE') && isset($this->m_cached_profile['categories'][C__CATG__VIRTUAL_MACHINE])) {
                $this->m_vm_con_arr = $deviceDao->get_virtual_machine_connections();
            }

            if (defined('C__CATG__RM_CONTROLLER') && isset($this->m_cached_profile['categories'][C__CATG__RM_CONTROLLER])) {
                $this->m_management_device_con_arr = $deviceDao->get_management_device_connections();
            }
        }

        // Determine which field will be used for the object title for operating systems
        $softwareDao->setOsFamilyAsObjectTitle($this->m_cached_profile['software_obj_title']);
        $softwareDao->setSimpleDatabaseModel($this->m_cached_profile['use_simple_database_mod']);

        $this->m_all_software = (bool)$this->m_cached_profile['import_all_software'];
        $this->m_all_networks = (bool)$this->m_cached_profile['import_all_networks'];
        $this->m_all_clusters = (bool)$this->m_cached_profile['import_all_clusters'];
        $this->m_all_blade_connections = (bool)$this->m_cached_profile['import_all_blade_connections'];
        $this->m_all_no_title_objects = (bool)isys_tenantsettings::get('jdisc.import-unidentified-devices', false);
        $this->m_import_custom_attributes = (bool)$this->m_cached_profile['import_custom_attributes'];
        $this->m_used_default_templates = (bool)$this->m_cached_profile['use_default_templates'];
        $this->m_is_jedi = $this->m_dao->is_jedi_version();
        $this->m_software_licences = ($this->m_is_jedi) ? false : (bool)$this->m_cached_profile['import_software_licences'];
        $this->importSoftwareServices = (bool)$this->m_cached_profile['import_software_services'];
        $this->importCloudSubscriptions = (bool)$this->m_cached_profile['import_cloud_subscriptions'];
        $this->importConnectionEndpoint = (bool)$this->m_cached_profile['import_connection_endpoint'];
        $this->m_profile_id = $p_profile;
        $this->m_group_id = $p_group;

        $networkDao = isys_jdisc_dao_network::instance($this->m_db);
        $networkDao->set_import_vlans($this->m_cached_profile['import_all_vlans']);
        $networkDao->set_additional_info($this->m_cached_profile['import_type_interfaces']);

        // Create temporary table mainly for network relevant data
        $networkDao->create_cache_table();

        $deviceDao->prepare_device_environment($this->m_cached_profile);

        // Increase group concat max length to get all attached vlans from cache
        $this->m_db->query('SET SESSION group_concat_max_len = 9999999;');

        // Instantiate CiMatcher for jdisc
        isys_jdisc_dao_matching::initialize(($this->m_cached_profile['object_matching'] ?: 1), // Just in case if object_matching is not set for whatever reason
            $this->m_server_id, isys_jdisc_dao_data::instance($this->m_db));

        return $this;
    }

    /**
     * Check if software licenses can be imported
     *
     * @return boolean
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function check_import_software_licences()
    {
        return $this->m_software_licences;
    }

    /**
     * Set check if software licenses cannot be imported
     *
     * @param bool $p_value
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_check_import_software_licenses($p_value = false)
    {
        $this->m_software_licences = false;
    }

    /**
     * Import method.
     *
     * @param   array $p_row
     * @param   array $p_jdisc_to_idoit Matching from JDisc device id to i-doit object id
     * @param   array $p_object_ids
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function prepare_object_data(&$p_row, &$p_jdisc_to_idoit, &$p_object_ids = [])
    {
        // We have to reset the logbook entries foreach device
        isys_jdisc_dao_data::reset_logbook_entries();

        $this->m_log->debug('# Start preparing the object data for "' . $p_row['name'] . '"!');

        $l_object = $l_connections = [];
        // use isys_cmdb_dao instead of isys_cmdb_dao_jdisc otherwise devices with the same name but with different data won´t be created
        $l_dao = isys_cmdb_dao_jdisc::instance($this->m_db);

        // Check if object already exists for other mode C__MERGE and C__OVERWRITE.
        $l_object_id = false;
        $l_use_default_template = false;
        $l_default_template = false;
        $newObject = false;

        if ($this->m_mode == isys_import_handler_cmdb::C__MERGE || $this->m_mode == isys_import_handler_cmdb::C__OVERWRITE) {
            $l_object_id = $p_row['identifierObjID'];

            if ($l_object_id > 0) {
                // Update data
                if ($p_row['identifierID'] > 0) {
                    isys_cmdb_dao_category_g_identifier::instance($this->m_db)
                        ->save_data($p_row['identifierID'], [
                            'last_scan' => $p_row['discoverytime'],
                            'group'     => $p_row['group_name'],
                            'last_updated' => date('Y-m-d H:i:s')
                        ]);
                }
                $this->m_log->info('Device id "' . $p_row['id'] . '" found. Using object id "' . $l_object_id . '" for device: "' . $p_row['name'] . '".');
            } else {
                // Last check only if setting jdisc.import-unidentified-devices is on
                if ($p_row['name'] == '' && $this->m_all_no_title_objects === true) {
                    // We have to give the imported device a title otherwise an Object with no title will be created
                    if ($p_row['serialnumber'] != '') {
                        $p_row['name'] = $p_row['serialnumber'];
                    } else {
                        $p_row['name'] = 'JDisc-Device: ' . $p_row['id'];
                    }

                    // last attempt
                    $l_object_id = $l_dao->get_obj_id_by_title($p_row['name'], $p_row['idoit_obj_type']);
                } elseif ($l_object_id === false && $p_row['name'] === '' && $this->m_all_no_title_objects === false) {
                    $this->m_log->debug('Skipping device with empty title (' . $p_row['type_name'] . ')');
                    isys_ajax_handler_jdisc::$m_additional_stats .= 'INFO: Skipped device with empty title (' . $p_row['type_name'] . ")\n";

                    return false;
                }
            }
        }

        // Default Template
        // Check if the object type has a template
        if ($this->m_used_default_templates === true) {
            if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']])) {
                $l_default_template = $this->m_obj_type_tpls[$p_row['idoit_obj_type']];
            } else {
                $l_default_template = $l_dao->get_default_template_by_obj_type($p_row['idoit_obj_type']);
                $this->m_obj_type_tpls[$p_row['idoit_obj_type']] = ($l_default_template === null) ? false : $l_default_template;
            }
        }

        // Build default template
        if ($l_default_template !== false) {
            $l_use_default_template = true;
            $l_black_list = [];
            // Build array only once per object type
            if (!is_array($l_default_template)) {
                $l_tmp = isys_export_cmdb_object::fetch_exportable_categories();
                $l_transformed = [];
                foreach ($l_tmp as $l_category_type => $l_categories) {
                    foreach ($l_categories as $l_categoryID => $l_crap) {
                        $l_transformed[$l_category_type][] = $l_categoryID;
                    }
                }

                $l_default_template_data = $this->m_export_obj->export($l_default_template, $l_transformed, C__RECORD_STATUS__TEMPLATE)
                    ->get_export();

                $l_template_content = array_pop($l_default_template_data);
                if (is_array($l_template_content)) {
                    unset($l_template_content['head']);
                    foreach ([
                                 C__CMDB__CATEGORY__TYPE_GLOBAL,
                                 C__CMDB__CATEGORY__TYPE_SPECIFIC,
                                 C__CMDB__CATEGORY__TYPE_CUSTOM
                             ] as $l_cattype) {
                        if (isset($l_template_content[$l_cattype])) {
                            $l_template_content_copy = $l_template_content[$l_cattype];
                            foreach ($l_template_content_copy as $l_key => $l_tmp_category) {
                                if (count($l_tmp_category) === 1) {
                                    unset($l_template_content[$l_cattype][$l_key]);
                                } else {
                                    $l_head = $l_template_content[$l_cattype][$l_key]['head'];
                                    unset($l_template_content[$l_cattype][$l_key]['head']);

                                    foreach ($l_tmp_category as $l_cat_entry => $l_data) {
                                        if ($l_cat_entry === 'head') {
                                            continue;
                                        }

                                        $l_template_content[$l_cattype][$l_key][$l_cat_entry]['data_id'] = null;
                                        foreach ($l_data as $l_prop_index => $l_property) {
                                            unset($l_template_content[$l_cattype][$l_key][$l_cat_entry][$l_prop_index]);
                                            $l_tag = $l_property['tag'];
                                            $l_value = $l_property[C__DATA__VALUE];

                                            if (is_array($l_property[C__DATA__VALUE])) {
                                                if (isset($l_property[C__DATA__VALUE]['ref_id'])) {
                                                    $l_value = $l_property[C__DATA__VALUE]['ref_id'];
                                                } elseif (isset($l_property[C__DATA__VALUE]['id'])) {
                                                    $l_value = $l_property[C__DATA__VALUE]['id'];
                                                    if (isset($l_property[C__DATA__VALUE]['sysid']) && $l_property[C__DATA__VALUE]['type']) {
                                                        // Add object ID so that we know that it exists for the import
                                                        $p_object_ids[$l_value] = (int)$l_value;
                                                    }
                                                } else {
                                                    $l_value = $l_property[C__DATA__VALUE][C__DATA__VALUE];
                                                }
                                            }

                                            if (is_object($l_property[C__DATA__VALUE]) && is_a($l_property[C__DATA__VALUE], 'isys_export_data')) {
                                                $l_value = $l_property[C__DATA__VALUE]->get_data();
                                            }

                                            if (is_array($l_value)) {
                                                if (count($l_value) > 0) {
                                                    foreach ($l_value as $l_value_data) {
                                                        if (isset($l_value_data['sysid']) && isset($l_value_data['id']) && isset($l_value_data['type'])) {
                                                            // Add object ID so that we know that it exists for the import
                                                            $p_object_ids[$l_value_data['id']] = (int)$l_value_data['id'];
                                                        }
                                                    }
                                                } else {
                                                    if (isset($l_value[0]['sysid']) && isset($l_value[0]['id']) && isset($l_value[0]['type'])) {
                                                        // Add object ID so that we know that it exists for the import
                                                        $p_object_ids[$l_value[0]['id']] = (int)$l_value[0]['id'];
                                                    }
                                                }
                                            }

                                            $l_property[C__DATA__VALUE] = $l_value;
                                            $l_template_content[$l_cattype][$l_key][$l_cat_entry]['properties'][$l_tag] = $l_property;
                                        }
                                    }

                                    $l_tmp = array_values($l_template_content[$l_cattype][$l_key]);
                                    unset($l_template_content[$l_cattype][$l_key]);

                                    $l_template_content[$l_cattype][$l_key] = [
                                        'title'         => $l_head['title'],
                                        'const'         => $l_head['const'],
                                        'category_type' => $l_head['category_type']
                                    ];
                                    $l_template_content[$l_cattype][$l_key]['category_entities'] = $l_tmp;
                                }
                            }
                        }
                    }
                }
                $this->m_obj_type_tpls[$p_row['idoit_obj_type']] = $l_template_content;
            }
        }

        $l_obj_id_test = isys_jdisc_dao_matching::instance()
            ->get_object_id_by_identifier($p_row['id'], $p_row['group_name']);

        if (!$l_object_id && $p_row['idoit_obj_type'] && $p_row['name']) {
            $this->m_log->info('Creating object ' . $p_row['name']);

            $l_object_id = $l_dao->insert_new_obj($p_row['idoit_obj_type'], false, $p_row['name'], null, C__RECORD_STATUS__NORMAL);
            $newObject = true;
            if (in_array($l_object_id, $p_jdisc_to_idoit)) {
                $this->m_log->info('Skipping device ' . $p_row['name'] . ' since device with same name already exists and unique checks are enabled.');
                isys_ajax_handler_jdisc::$m_additional_stats .= 'INFO: Skipped device ' . $p_row['name'] .
                    " since device with same name already exists and unique checks are enabled.\n";

                return false;
            }

            // Cache the new object id with the device id as key
            $p_jdisc_to_idoit[$p_row['id']] = $l_object_id;

            // Add it to the cache in isys_cmdb_dao_category_g_identifier
            isys_cmdb_dao_category_g_identifier::set_missing_identifiers($l_object_id, $p_row['id']);
        } elseif ((in_array($l_object_id, isys_cmdb_dao_category_g_identifier::get_cached_objects()) && !$l_obj_id_test)) {
            // Cache the new object id with the device id as key
            $p_jdisc_to_idoit[$p_row['id']] = $l_object_id;
            isys_cmdb_dao_category_g_identifier::remove_object_id_by_identifier($p_row['id']);
        }

        if ($l_object_id > 0) {
            $p_object_ids[$l_object_id] = $l_object_id;
            if (!$l_obj_id_test || isys_cmdb_dao_category_g_identifier::is_identifier_missing($l_object_id)) {
                /**
                 * Cache device id with object id and cache device id in the missing identifiers
                 */
                $l_group_name = ((!is_array($p_row['group_name']) && strpos($p_row['group_name'], ',') !== false) ? explode(',', $p_row['group_name']) : $p_row['group_name']);
                if (!$l_obj_id_test) {
                    if (is_array($l_group_name)) {
                        foreach ($l_group_name as $l_group_part) {
                            isys_cmdb_dao_category_g_identifier::set_object_id_by_identifier($l_object_id, $p_row['id'] . '-' . $l_group_part);
                        }
                    } else {
                        $l_key = $p_row['id'];
                        if ($l_group_name != '') {
                            $l_key .= '-' . $l_group_name;
                        }
                        isys_cmdb_dao_category_g_identifier::set_object_id_by_identifier($l_object_id, $l_key);
                    }
                }
                isys_cmdb_dao_category_g_identifier::instance($this->m_db)
                    ->set_identifier(
                        $l_object_id,
                        defined_or_default('C__CATG__IDENTIFIER_TYPE__JDISC'),
                        'deviceid-' . $this->m_server_id,
                        $p_row['id'],
                        '',
                        $l_group_name,
                        $p_row['discoverytime'],
                        (($this->m_mode === isys_import_handler_cmdb::C__CREATE) ? C__RECORD_STATUS__NORMAL : C__RECORD_STATUS__ARCHIVED)
                    );
            }

            if ($p_row['name'] == '' && $p_row['serialnumber'] != '') {
                $p_row['name'] = $p_row['serialnumber'];
            } elseif ($p_row['name'] === '' && $p_row['serialnumber'] == '') {
                $p_row['name'] = 'JDisc-Device: ' . $p_row['id'];
            }

            // We now prepare the array for the import.
            $this->m_log->debug('Prepare the core object data');
            $l_object[$l_object_id] = isys_jdisc_dao_devices::instance($this->m_db)
                ->prepare_device_array($p_row, $this->m_mode, $l_object_id);
            $l_object[$l_object_id]['categories'] = [];

            // In case we cannot retrieve the object info
            if ($l_object[$l_object_id] === false) {
                $this->m_log->debug('Skipping device ' . $p_row['name'] . ': Couild not identify object type for jdisc type: ' . $p_row['type_name'] .
                    '. Check your profile.');

                return false;
            }

            $l_activated_categories = $this->m_cached_profile['categories'];
            isys_jdisc_dao_network::instance($this->m_db)
                ->set_object_id($l_object_id);
            // This prepares the location assignment if set
            if (defined('C__CATG__LOCATION') && $p_row['location'] > 0) {
                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                C__CATG__LOCATION] = isys_jdisc_dao_devices::instance($this->m_db)
                    ->prepare_location($p_row['location']);
            }

            // This is for object type access point
            if (defined('C__CATS__ACCESS_POINT') && defined('C__OBJTYPE__ACCESS_POINT') && $l_object[$l_object_id]['type']['id'] == C__OBJTYPE__ACCESS_POINT) {
                if (isys_jdisc_dao_data::clear_data() === true) {
                    $this->m_dao->clear_category('isys_cats_access_point_list', $l_object_id, false);
                }

                $this->m_log->debug('Read and preapare access point data');

                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_SPECIFIC . '_' .
                C__CATS__ACCESS_POINT] = isys_jdisc_dao_network::instance($this->m_db)
                    ->get_access_point_by_device($p_row['id']);

                if ($l_use_default_template && $newObject) {
                    if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_SPECIFIC][C__CATS__ACCESS_POINT])) {
                        $l_black_list[C__CMDB__CATEGORY__TYPE_SPECIFIC][C__CATS__ACCESS_POINT] = true;
                        if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_SPECIFIC . '_' . C__CATS__ACCESS_POINT]['category_entities'])) {
                            $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_SPECIFIC . '_' .
                            C__CATS__ACCESS_POINT]['category_entities'] = array_replace_recursive(
                                $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_SPECIFIC][C__CATS__ACCESS_POINT]['category_entities'],
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_SPECIFIC . '_' . C__CATS__ACCESS_POINT]['category_entities']
                            );
                        } else {
                            $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_SPECIFIC . '_' .
                            C__CATS__ACCESS_POINT]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_SPECIFIC][C__CATS__ACCESS_POINT]['category_entities'];
                        }
                    }
                }
            }

            if ($this->m_all_blade_connections && count($this->m_chassis_types) > 0) {
                $this->m_log->debug('Check if object has connection to a blade');
                $l_blade_info = isys_jdisc_dao_devices::instance($this->m_db)
                    ->get_blade_connection($p_row['id'], $this->m_chassis_types);
                if ($l_blade_info !== false) {
                    isys_jdisc_dao_devices::instance($this->m_db)
                        ->set_blade_connection($l_blade_info, $p_row['id']);
                }
            }

            // Special handling with module interfaces
            isys_jdisc_dao_devices::instance($this->m_db)
                ->prepare_modules($p_row['id']);

            // And here we fill the global categories.
            // This order matters!
            if (is_array($l_activated_categories)) {
                // Set Object-ID and Object-Type-ID
                isys_jdisc_dao_devices::instance($this->m_db)
                    ->set_current_object_id($l_object_id);
                isys_jdisc_dao_devices::instance($this->m_db)
                    ->set_current_object_type_id($p_row['idoit_obj_type']);

                // @See ID-5038 Import from SNMP Syslocation
                if (defined('C__CATG__LOCATION') && isset($l_activated_categories[C__CATG__LOCATION])) {
                    $this->m_log->debug('Read and prepare snmp syslocation');
                    $locationData = [];

                    if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__LOCATION])) {
                        $locationData = $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__LOCATION];
                    }

                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                    C__CATG__LOCATION] = isys_jdisc_dao_devices::instance($this->m_db)
                        ->prepareSnmpSysLocation($p_row['id'], $l_object_id, $locationData);

                    // @see ID-7685
                    $value = $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__LOCATION]['category_entities'][0]['properties']['parent']['value'];

                    if ($value) {
                        $p_object_ids[] = $value;
                    }
                }

                if (defined('C__CATG__GRAPHIC')) {
                    if (isset($l_activated_categories[C__CATG__GRAPHIC])) {
                        // Reset category graphic card, data will be deleted before dataretrieval
                        if (isys_jdisc_dao_data::clear_data() === true) {
                            $this->m_dao->clear_category('isys_catg_graphic_list', $l_object_id, false);
                        }

                        $this->m_log->debug('Read and prepare graphic card data');
                        // Get the videocontroller(s) of each device.
                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                        C__CATG__GRAPHIC] = isys_jdisc_dao_devices::instance($this->m_db)
                            ->get_videocontroller_by_device($p_row['id']);
                    }

                    if ($l_use_default_template && $newObject) {
                        if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__GRAPHIC])) {
                            $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__GRAPHIC] = true;
                            if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__GRAPHIC]['category_entities'])) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__GRAPHIC]['category_entities'] = array_merge(
                                    $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__GRAPHIC]['category_entities'],
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__GRAPHIC]['category_entities']
                                );
                            } else {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__GRAPHIC]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__GRAPHIC]['category_entities'];
                            }
                        }
                    }
                }

                if (defined('C__CATG__UNIVERSAL_INTERFACE')) {
                    if (isset($l_activated_categories[C__CATG__UNIVERSAL_INTERFACE]) && isys_jdisc_dao_software::instance($this->m_db)
                            ->check_table('devicedeviceconnection')) {
                        // Reset category universal interface
                        if (isys_jdisc_dao_data::clear_data() === true) {
                            $this->m_dao->clear_category('isys_catg_ui_list', $l_object_id, false);
                        }

                        $this->m_log->debug('Read and prepare universal interface data');
                        // Get directly attached connections of each device
                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                        C__CATG__UNIVERSAL_INTERFACE] = isys_jdisc_dao_network::instance($this->m_db)
                            ->get_universal_interface_by_device($p_row['id'], $p_jdisc_to_idoit);
                    }

                    if ($l_use_default_template && $newObject) {
                        if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__UNIVERSAL_INTERFACE])) {
                            $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__UNIVERSAL_INTERFACE] = true;
                            if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__UNIVERSAL_INTERFACE]['category_entities'])) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__UNIVERSAL_INTERFACE]['category_entities'] = array_merge(
                                    $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__UNIVERSAL_INTERFACE]['category_entities'],
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__UNIVERSAL_INTERFACE]['category_entities']
                                );
                            } else {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__UNIVERSAL_INTERFACE]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__UNIVERSAL_INTERFACE]['category_entities'];
                            }
                        }
                    }
                }

                if (defined('C__CATG__CPU') && isset($l_activated_categories[C__CATG__CPU])) {
                    // Reset category cpu, data will be deleted before dataretrieval
                    if (isys_jdisc_dao_data::clear_data() === true) {
                        $this->m_dao->clear_category('isys_catg_cpu_list', $l_object_id, false);
                    }

                    $this->m_log->debug('Read and prepare CPU data');
                    // Get the processor(s) of each device.
                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                    C__CATG__CPU] = isys_jdisc_dao_devices::instance($this->m_db)
                        ->get_processor_by_device($p_row['id']);
                }

                if ($l_use_default_template && $newObject && defined('C__CATG__CPU')) {
                    if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__CPU])) {
                        $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__CPU] = true;
                        if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__CPU]['category_entities'])) {
                            $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                            C__CATG__CPU]['category_entities'] = array_merge(
                                $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__CPU]['category_entities'],
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__CPU]['category_entities']
                            );
                        } else {
                            $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                            C__CATG__CPU]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__CPU]['category_entities'];
                        }
                    }
                }

                if (defined('C__CATG__MEMORY')) {
                    if (isset($l_activated_categories[C__CATG__MEMORY])) {
                        // Reset category memory, data will be deleted before dataretrieval
                        if (isys_jdisc_dao_data::clear_data() === true) {
                            $this->m_dao->clear_category('isys_catg_memory_list', $l_object_id, false);
                        }

                        $this->m_log->debug('Read and prepare memory (RAM) data');
                        // Get the memory of each device.
                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                        C__CATG__MEMORY] = isys_jdisc_dao_devices::instance($this->m_db)
                            ->get_memory_by_device($p_row['id']);
                    }

                    if ($l_use_default_template && $newObject) {
                        if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__MEMORY])) {
                            $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__MEMORY] = true;
                            if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__MEMORY]['category_entities'])) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__MEMORY]['category_entities'] = array_merge(
                                    $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__MEMORY]['category_entities'],
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__MEMORY]['category_entities']
                                );
                            } else {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__MEMORY]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__MEMORY]['category_entities'];
                            }
                        }
                    }
                }

                if (defined('C__CATG__STORAGE') && defined('C__CATG__STORAGE_DEVICE')) {
                    // Constant C__CATG__STORAGE is the root category for devices. For the import use C__CMDB__SUBCAT__STORAGE__DEVICE
                    if (isset($l_activated_categories[C__CATG__STORAGE])) {
                        // Reset category storage, data will be deleted before dataretrieval
                        if (isys_jdisc_dao_data::clear_data() === true) {
                            $this->m_dao->clear_category('isys_catg_stor_list', $l_object_id, false);
                        }

                        $this->m_log->debug('Read and prepare storage (HDD) data');
                        // Get the local storage devices (HDD, ...).
                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                        C__CATG__STORAGE_DEVICE] = isys_jdisc_dao_devices::instance($this->m_db)
                            ->get_physicaldisk_by_device($p_row['id']);
                    }

                    if ($l_use_default_template && $newObject) {
                        if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__STORAGE_DEVICE])) {
                            $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__STORAGE_DEVICE] = true;
                            if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__STORAGE_DEVICE]['category_entities'])) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__STORAGE_DEVICE]['category_entities'] = array_merge(
                                    $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__STORAGE_DEVICE]['category_entities'],
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__STORAGE_DEVICE]['category_entities']
                                );
                            } else {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__STORAGE_DEVICE]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__STORAGE_DEVICE]['category_entities'];
                            }
                        }
                    }
                }

                if (defined('C__CATG__DRIVE')) {
                    if (isset($l_activated_categories[C__CATG__DRIVE])) {
                        // Reset category drive, data will be deleted before dataretrieval
                        if (isys_jdisc_dao_data::clear_data() === true) {
                            $this->m_dao->clear_category('isys_catg_drive_list', $l_object_id, false);
                        }

                        $this->m_log->debug('Read and prepare drives (HDD, CD, DVD, ...) data');
                        // Get the drives (HDD, CD, DVD, ...).
                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                        C__CATG__DRIVE] = isys_jdisc_dao_devices::instance($this->m_db)
                            ->get_logicaldisk_by_device($p_row['id']);
                    }

                    if ($l_use_default_template && $newObject) {
                        if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__DRIVE])) {
                            $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__DRIVE] = true;
                            if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__DRIVE]['category_entities'])) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__DRIVE]['category_entities'] = array_merge(
                                    $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__DRIVE]['category_entities'],
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__DRIVE]['category_entities']
                                );
                            } else {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__DRIVE]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__DRIVE]['category_entities'];
                            }
                        }
                    }
                }

                isys_jdisc_dao_network::instance($this->m_db)
                    ->reset_filtered_ports_logical_ports($p_row['type'], $p_row['type_name'], $p_row['osid'], $p_row['osversion'], $p_row['idoit_obj_type'], $p_row['name']);

                if (defined('C__CATG__NETWORK_PORT') && defined('C__CATG__NETWORK_LOG_PORT')) {
                    if (isset($l_activated_categories[C__CATG__NETWORK_PORT])) {
                        if ($this->importConnectionEndpoint) {
                            $this->m_dao->clear_category(
                                'isys_catg_connection_endpoint_list',
                                $l_object_id,
                                true
                            );
                        } else {
                            // Reset category network port, data will be deleted before dataretrieval
                            if (isys_jdisc_dao_data::clear_data() === true) {
                                $this->m_dao->clear_category(
                                    'isys_catg_port_list',
                                    $l_object_id,
                                    true
                                );
                            }
                        }

                        // Reset category logical port, data will be deleted before dataretrieval
                        if (isys_jdisc_dao_data::clear_data() === true) {
                            $this->m_dao->clear_category('isys_catg_log_port_list', $l_object_id, false);
                        }

                        $this->m_log->debug('Read and prepare network port data');
                        isys_jdisc_dao_network::instance($this->m_db)
                            ->reset_filtered_ports_logical_ports(
                                $p_row['type'],
                                $p_row['type_name'],
                                $p_row['osid'],
                                $p_row['osversion'],
                                $p_row['idoit_obj_type'],
                                $p_row['name']
                            );

                        if ($this->importConnectionEndpoint) {
                            $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__CONNECTION_ENDPOINT] = isys_jdisc_dao_category_connection_endpoint::instance($this->m_db)
                                ->getDataForImport($p_row['id'], false, $p_jdisc_to_idoit, $p_object_ids, $l_object_id);
                        } else {
                            // Get Ports
                            $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__NETWORK_PORT] = isys_jdisc_dao_network::instance($this->m_db)
                                ->get_ports_by_device($p_row['id']);
                        }

                        // Get Logical Ports
                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                        C__CATG__NETWORK_LOG_PORT] = isys_jdisc_dao_network::instance($this->m_db)
                            ->get_logical_ports_by_device($p_row['id']);
                    }

                    if ($l_use_default_template && $newObject) {
                        if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__NETWORK_PORT])) {
                            $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__NETWORK_PORT] = true;
                            if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__NETWORK_PORT]['category_entities'])) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__NETWORK_PORT]['category_entities'] = array_merge(
                                    $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__NETWORK_PORT]['category_entities'],
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__NETWORK_PORT]['category_entities']
                                );
                            } else {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__NETWORK_PORT]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__NETWORK_PORT]['category_entities'];
                            }
                        }

                        if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__NETWORK_LOG_PORT])) {
                            $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__NETWORK_LOG_PORT] = true;
                            if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__NETWORK_LOG_PORT]['category_entities'])) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__NETWORK_LOG_PORT]['category_entities'] = array_merge(
                                    $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__NETWORK_LOG_PORT]['category_entities'],
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__NETWORK_LOG_PORT]['category_entities']
                                );
                            } else {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__NETWORK_LOG_PORT]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__NETWORK_LOG_PORT]['category_entities'];
                            }
                        }
                    }
                }

                if (defined('C__CATG__CONTROLLER_FC_PORT')) {
                    if (isset($l_activated_categories[C__CATG__CONTROLLER_FC_PORT])) {
                        if (isys_jdisc_dao_data::clear_data() === true) {
                            $this->m_dao->clear_category('isys_catg_fc_port_list', $l_object_id, true);
                        }

                        $this->m_log->debug('Read and prepare fc port data');

                        // Get FC Ports
                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                        C__CATG__CONTROLLER_FC_PORT] = isys_jdisc_dao_network::instance($this->m_db)
                            ->get_fc_ports_by_device($p_row['id']);
                    }

                    if ($l_use_default_template && $newObject) {
                        if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__CONTROLLER_FC_PORT])) {
                            $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__CONTROLLER_FC_PORT] = true;
                            if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__CONTROLLER_FC_PORT]['category_entities'])) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__CONTROLLER_FC_PORT]['category_entities'] = array_merge(
                                    $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__CONTROLLER_FC_PORT]['category_entities'],
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__CONTROLLER_FC_PORT]['category_entities']
                                );
                            } else {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__CONTROLLER_FC_PORT]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__CONTROLLER_FC_PORT]['category_entities'];
                            }
                        }
                    }
                }

                if (defined('C__CATG__NETWORK_INTERFACE')) {
                    if (isset($l_activated_categories[C__CATG__NETWORK_INTERFACE])) {
                        // Reset category interface, data will be deleted before dataretrieval
                        if (isys_jdisc_dao_data::clear_data() === true) {
                            $this->m_dao->clear_category('isys_catg_netp_list', $l_object_id, false);
                        }

                        $this->m_log->debug('Read and prepare network interface data');
                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                        C__CATG__NETWORK_INTERFACE] = isys_jdisc_dao_network::instance($this->m_db)
                            ->get_interfaces_by_device($p_row['id']);
                    }

                    if ($l_use_default_template && $newObject) {
                        if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__NETWORK_INTERFACE])) {
                            $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__NETWORK_INTERFACE] = true;
                            if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__NETWORK_INTERFACE]['category_entities'])) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__NETWORK_INTERFACE]['category_entities'] = array_merge(
                                    $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__NETWORK_INTERFACE]['category_entities'],
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__NETWORK_INTERFACE]['category_entities']
                                );
                            } else {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__NETWORK_INTERFACE]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__NETWORK_INTERFACE]['category_entities'];
                            }
                        }
                    }
                }

                if (defined('C__CATG__OPERATING_SYSTEM')) {
                    if (isset($l_activated_categories[C__CATG__OPERATING_SYSTEM])) {
                        // We need this data more than once, so we create these variables.
                        $this->m_log->debug('Prepare the operating system data');

                        $this->m_log->debug('Read and prepare operating system data');
                        // Get the assigned operating systems - This implementation differs from the others!!
                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                        C__CATG__OPERATING_SYSTEM] = isys_jdisc_dao_software::instance($this->m_db)
                            ->get_os_by_device($p_row['id'], false, $this->m_all_software, $p_object_ids, $l_connections, $this->m_software_licences);
                    }

                    if ($l_use_default_template && $newObject) {
                        if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__OPERATING_SYSTEM])) {
                            $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__OPERATING_SYSTEM] = true;
                            if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__OPERATING_SYSTEM]['category_entities'])) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__OPERATING_SYSTEM]['category_entities'] = array_replace_recursive(
                                    $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__OPERATING_SYSTEM]['category_entities'],
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__OPERATING_SYSTEM]['category_entities']
                                );
                            } else {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__OPERATING_SYSTEM]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__OPERATING_SYSTEM]['category_entities'];
                            }
                        }
                    }
                }

                $this->m_log->debug('Prepare the network data');

                if (defined('C__CATG__IP')) {
                    if (isset($l_activated_categories[C__CATG__IP])) {
                        $l_profile = $this->get_cached_profile();
                        $l_profileNetworkAddressesFilter = unserialize($l_profile['network_adresses']);
                        $importDhcpType = (int)$l_profile['import_type_dhcp_addresses'];

                        // Reset category hostaddress, data will be deleted before dataretrieval
                        if ((isys_jdisc_dao_data::clear_data() === true) && ($importDhcpType !== 1)) {
                            $this->m_dao->clear_category('isys_catg_ip_list', $l_object_id, true);
                        }

                        if ($importDhcpType === 1) {
                            $this->m_dao->clear_ip_addresses($l_object_id, true);
                        }

                        $this->m_log->debug('Read and prepare network (hostaddress) data');

                        // Get the hostaddresses - This implementation differs from the others!!
                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__IP] = isys_jdisc_dao_network::instance($this->m_db)
                            ->get_layer3_by_device($p_row['id'], false, $this->m_all_networks, $l_connections, $l_profileNetworkAddressesFilter, $importDhcpType);
                    }

                    if ($l_use_default_template && $newObject) {
                        if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__IP])) {
                            $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__IP] = true;
                            if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__IP]['category_entities'])) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__IP]['category_entities'] = array_merge(
                                    $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__IP]['category_entities'],
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__IP]['category_entities']
                                );
                            } else {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__IP]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__IP]['category_entities'];
                            }
                        }
                    }
                }

                if (defined('C__CATG__APPLICATION')) {
                    if (isset($l_activated_categories[C__CATG__APPLICATION])) {
                        // Reset category application, data will be deleted before dataretrieval
                        if (isys_jdisc_dao_data::clear_data() === true) {
                            $this->m_dao->clear_category('isys_catg_application_list', $l_object_id, true);
                        }

                        // We need this data more than once, so we create these variables.
                        $this->m_log->debug('Prepare the software data');
                        isys_jdisc_dao_software::instance($this->m_db)
                            ->set_current_object_id($l_object_id);

                        $this->m_log->debug('Read and prepare application data');
                        // Get the assigned applications - This implementation differs from the others!!
                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                        C__CATG__APPLICATION] = isys_jdisc_dao_software::instance($this->m_db)
                            ->get_software_by_device($p_row['id'], false, $this->m_all_software, $p_object_ids, $l_connections, $this->m_software_licences, $this->importSoftwareServices);
                    }

                    if ($l_use_default_template && $newObject) {
                        if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__APPLICATION])) {
                            $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__APPLICATION] = true;
                            if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__APPLICATION]['category_entities'])) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__APPLICATION]['category_entities'] = array_merge(
                                    $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__APPLICATION]['category_entities'],
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__APPLICATION]['category_entities']
                                );
                            } else {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__APPLICATION]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__APPLICATION]['category_entities'];
                            }
                        }
                    }

                    if (isys_jdisc_dao_software::instance($this->m_db)->isSimpleDatabaseModel()) {
                        isys_jdisc_dao_software_database::instance($this->m_db)
                            ->reset()
                            ->set_current_object_id($l_object_id);

                        $this->m_log->debug('Read and prepare database data');
                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__DATABASE] =
                            isys_jdisc_dao_software_database::instance($this->m_db)
                                ->getDbmsByDevice($p_row['id'], false, $p_object_ids, $l_connections);

                        if ($l_use_default_template && $newObject) {
                            if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__DATABASE])) {
                                $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__DATABASE] = true;
                                if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__DATABASE]['category_entities'])) {
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                    C__CATG__DATABASE]['category_entities'] = array_merge(
                                        $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__DATABASE]['category_entities'],
                                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__DATABASE]['category_entities']
                                    );
                                } else {
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                    C__CATG__DATABASE]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__DATABASE]['category_entities'];
                                }
                            }
                        }


                        $this->m_log->debug('Read and prepare database schemas data');
                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__DATABASE_SA] =
                            isys_jdisc_dao_software_database::instance($this->m_db)
                                ->getDatabasesByDevice($p_row['id'], false, $p_object_ids, $l_connections);

                        if ($l_use_default_template && $newObject) {
                            if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__DATABASE_SA])) {
                                $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__DATABASE_SA] = true;
                                if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__DATABASE_SA]['category_entities'])) {
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                    C__CATG__DATABASE_SA]['category_entities'] = array_merge(
                                        $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__DATABASE_SA]['category_entities'],
                                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__DATABASE_SA]['category_entities']
                                    );
                                } else {
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                    C__CATG__DATABASE_SA]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__DATABASE_SA]['category_entities'];
                                }
                            }
                        }

                        $this->m_log->debug('Read and prepare database table data');
                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__DATABASE_TABLE] =
                            isys_jdisc_dao_software_database::instance($this->m_db)
                                ->getDatabaseTablesByDevice($p_row['id'], false, $p_object_ids, $l_connections);

                        if ($l_use_default_template && $newObject) {
                            if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__DATABASE_TABLE])) {
                                $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__DATABASE_TABLE] = true;
                                if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__DATABASE_TABLE]['category_entities'])) {
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                    C__CATG__DATABASE_TABLE]['category_entities'] = array_merge(
                                        $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__DATABASE_TABLE]['category_entities'],
                                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__DATABASE_TABLE]['category_entities']
                                    );
                                } else {
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                    C__CATG__DATABASE_TABLE]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__DATABASE_TABLE]['category_entities'];
                                }
                            }
                        }
                    }
                }

                if (defined('C__CATG__NET_LISTENER')) {
                    if (isset($l_activated_categories[C__CATG__NET_LISTENER]) && isys_jdisc_dao_software::instance($this->m_db)
                            ->check_table('applicationinstanceport')) {
                        // Reset category application, data will be deleted before dataretrieval
                        if (isys_jdisc_dao_data::clear_data() === true) {
                            $this->m_dao->clear_category('isys_catg_net_listener_list', $l_object_id, false);
                        }

                        $this->m_log->debug('Prepare net listener data');
                        $this->m_log->debug('Read and prepare net listener data');

                        isys_jdisc_dao_software::instance($this->m_db)
                            ->handle_net_listener($p_row['id'], $l_object_id);
                    }

                    if ($l_use_default_template && $newObject) {
                        if (defined('C__CATG__APPLICATION') && isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__APPLICATION])) {
                            $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__NET_LISTENER] = true;
                            if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__NET_LISTENER]['category_entities'])) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__NET_LISTENER]['category_entities'] = array_merge(
                                    $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__NET_LISTENER]['category_entities'],
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__NET_LISTENER]['category_entities']
                                );
                            } else {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__NET_LISTENER]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__NET_LISTENER]['category_entities'];
                            }
                        }
                    }
                }

                if (defined('C__CATG__MODEL')) {
                    if (isset($l_activated_categories[C__CATG__MODEL])) {
                        $this->m_log->debug('Read and prepare model data');
                        // Get contents for model category.
                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                        C__CATG__MODEL] = isys_jdisc_dao_devices::instance($this->m_db)
                            ->prepare_model($p_row);
                    }

                    if ($l_use_default_template && $newObject) {
                        if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__MODEL])) {
                            $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__MODEL] = true;
                            if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__MODEL]['category_entities'])) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__MODEL]['category_entities'] = array_replace_recursive(
                                    $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__MODEL]['category_entities'],
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__MODEL]['category_entities']
                                );
                            } else {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__MODEL]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__MODEL]['category_entities'];
                            }
                        }
                    }
                }

                if (defined('C__CATG__LAST_LOGIN_USER')) {
                    // Last logged in user
                    if (isset($l_activated_categories[C__CATG__LAST_LOGIN_USER])) {
                        $this->m_log->debug('Read and prepare last logged in user data');
                        // Get contents for model category.
                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                        C__CATG__LAST_LOGIN_USER] = isys_jdisc_dao_devices::instance($this->m_db)
                            ->prepare_last_login_user($p_row);
                    }

                    if ($l_use_default_template && $newObject) {
                        if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__LAST_LOGIN_USER])) {
                            $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__LAST_LOGIN_USER] = true;
                            if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__LAST_LOGIN_USER]['category_entities'])) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__LAST_LOGIN_USER]['category_entities'] = array_replace_recursive(
                                    $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__LAST_LOGIN_USER]['category_entities'],
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__LAST_LOGIN_USER]['category_entities']
                                );
                            } else {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__LAST_LOGIN_USER]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__LAST_LOGIN_USER]['category_entities'];
                            }
                        }
                    }
                }

                if (defined('C__CATG__VIRTUAL_MACHINE')) {
                    // Check if object is a virtual machine or not
                    if (isset($l_activated_categories[C__CATG__VIRTUAL_MACHINE])) {
                        $this->m_log->debug('Read and prepare virtual machine (VM) data');

                        if (isset($this->m_vm_con_arr[$p_row['id']])) {
                            if (!isset($this->m_cluster_cache[$this->m_vm_con_arr[$p_row['id']]])) {
                                $l_cluster = isys_jdisc_dao_cluster::instance($this->m_db)
                                    ->get_cluster_by_device($this->m_vm_con_arr[$p_row['id']], false, $this->m_all_clusters);
                                $this->m_cluster_cache[$this->m_vm_con_arr[$p_row['id']]] = $l_cluster;
                            } else {
                                $l_cluster = $this->m_cluster_cache[$this->m_vm_con_arr[$p_row['id']]];
                            }
                            if (!isset($p_jdisc_to_idoit[$this->m_vm_con_arr[$p_row['id']]])) {
                                isys_jdisc_dao_devices::instance($this->m_db)
                                    ->set_vm_host_by_device($this->m_vm_con_arr[$p_row['id']], $p_jdisc_to_idoit, $p_object_ids);
                            }
                            $l_category_vm = isys_jdisc_dao_devices::instance($this->m_db)
                                ->prepare_virtual_machine($p_row, $this->m_vm_con_arr[$p_row['id']], $p_jdisc_to_idoit, $l_cluster);
                            if ($l_category_vm) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__VIRTUAL_MACHINE] = $l_category_vm;
                            } else {
                                $this->m_log->debug('Virtual host with device id "' . $this->m_vm_con_arr[$p_row['id']] . '" does not exist.');
                                isys_ajax_handler_jdisc::$m_additional_stats .= 'INFO: VM host for device ' . $p_row['name'] .
                                    " does not exist and is not imported with your profile configuration. Please specify an object-type mapping for device type " .
                                    $p_row['type'] . " in order to import this host.\n";
                            }
                        } else {
                            // If no virtual host is defined than remove the entry
                            $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                            C__CATG__VIRTUAL_MACHINE] = isys_jdisc_dao_devices::instance($this->m_db)
                                ->prepare_virtual_machine($p_row, null, $p_jdisc_to_idoit);
                            $this->m_log->debug('No Virtual host with device id "' . $this->m_vm_con_arr[$p_row['id']] . '" found.');
                        }
                    }

                    if ($l_use_default_template && $newObject) {
                        if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__VIRTUAL_MACHINE])) {
                            $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__VIRTUAL_MACHINE] = true;
                            if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__VIRTUAL_MACHINE]['category_entities'])) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__VIRTUAL_MACHINE]['category_entities'] = array_replace_recursive(
                                    $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__VIRTUAL_MACHINE]['category_entities'],
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__VIRTUAL_MACHINE]['category_entities']
                                );
                            } else {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__VIRTUAL_MACHINE]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__VIRTUAL_MACHINE]['category_entities'];
                            }
                        }
                    }
                }

                if (defined('C__CATG__CLUSTER_MEMBERSHIPS')) {
                    $this->m_log->debug('Prepare the cluster data');
                    $l_cluster = isys_jdisc_dao_cluster::instance($this->m_db)
                        ->get_cluster_by_device($p_row['id'], false, $this->m_all_clusters);

                    if (isset($l_activated_categories[C__CATG__CLUSTER_MEMBERSHIPS])) {
                        // Reset category cluster memberships, data will be deleted before dataretrieval
                        if (isys_jdisc_dao_data::clear_data() === true) {
                            isys_jdisc_dao_cluster::instance($this->m_db)
                                ->clear_cluster_memberships($l_object_id);
                        }

                        $this->m_log->debug('Prepare cluster assignments');

                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__CLUSTER_MEMBERSHIPS] = $l_cluster;
                    }

                    if ($l_use_default_template && $newObject) {
                        if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__CLUSTER_MEMBERSHIPS])) {
                            $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__CLUSTER_MEMBERSHIPS] = true;
                            if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__CLUSTER_MEMBERSHIPS]['category_entities'])) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__CLUSTER_MEMBERSHIPS]['category_entities'] = array_merge(
                                    $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__CLUSTER_MEMBERSHIPS]['category_entities'],
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__CLUSTER_MEMBERSHIPS]['category_entities']
                                );
                            } else {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__CLUSTER_MEMBERSHIPS]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__CLUSTER_MEMBERSHIPS]['category_entities'];
                            }
                        }
                    }
                }

                if (defined('C__CATG__GUEST_SYSTEMS')) {
                    // Handle category guest system only if no cluster is assigned
                    if (isset($l_activated_categories[C__CATG__GUEST_SYSTEMS]) && count($l_cluster) === 0) {
                        $this->m_log->debug('Read and prepare virtual computers assignment (guest systems) for the host system.');
                        // Get contents for model category.
                        isys_jdisc_dao_devices::instance($this->m_db)
                            ->handle_guest_systems($p_row['id'], $l_object_id, $l_object[$l_object_id]['type'], $p_jdisc_to_idoit, $this->m_mode, false);
                    }

                    if ($l_use_default_template && $newObject && count($l_cluster) === 0) {
                        if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__GUEST_SYSTEMS])) {
                            $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__GUEST_SYSTEMS] = true;
                            $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                            C__CATG__GUEST_SYSTEMS]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__GUEST_SYSTEMS]['category_entities'];
                        }
                    }
                }

                if (defined('C__CATG__RM_CONTROLLER')) {
                    if (isset($l_activated_categories[C__CATG__RM_CONTROLLER])) {
                        // Reset category Remote Management Controller, data will be deleted before dataretrieval
                        if (isys_jdisc_dao_data::clear_data() === true) {
                            $this->m_dao->clear_category('isys_catg_rm_controller_list', $l_object_id, true);
                        }

                        $this->m_log->debug('Read and prepare remote management controller data');

                        // Get contents for model category.
                        if (isset($this->m_management_device_con_arr[$p_row['id']])) {
                            $l_rm_controller = isys_jdisc_dao_devices::instance($this->m_db)
                                ->prepare_rm_controller($p_row['id'], $p_jdisc_to_idoit, $p_object_ids);
                            if ($l_rm_controller) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__RM_CONTROLLER] = $l_rm_controller;
                            } else {
                                $this->m_log->debug('Management Device with device id "' . $this->m_management_device_con_arr[$p_row['id']] . '" does not exist.');
                                isys_ajax_handler_jdisc::$m_additional_stats .= 'INFO: Management Device for device ' . $p_row['name'] .
                                    " does not exist and is not imported with your profile configuration. Please specify an object-type mapping for Management Devices in order to import the Management Device connection.\n";
                            }
                        } else {
                            $this->m_log->debug('No Management Device found for device "' . $p_row['name'] . '" (' . $this->m_vm_con_arr[$p_row['id']] . ') .');
                        }
                    }

                    if ($l_use_default_template && $newObject) {
                        if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__RM_CONTROLLER])) {
                            $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__RM_CONTROLLER] = true;
                            if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__RM_CONTROLLER]['category_entities'])) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__RM_CONTROLLER]['category_entities'] = array_replace_recursive(
                                    $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__RM_CONTROLLER]['category_entities'],
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__RM_CONTROLLER]['category_entities']
                                );
                            } else {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__RM_CONTROLLER]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__RM_CONTROLLER]['category_entities'];
                            }
                        }
                    }
                }

                if (defined('C__CATG__STACK_MEMBER')) {
                    if (isset($l_activated_categories[C__CATG__STACK_MEMBER])) {
                        // Reset category Remote Management Controller, data will be deleted before dataretrieval
                        if (isys_jdisc_dao_data::clear_data() === true) {
                            $this->m_dao->clear_category('isys_catg_stack_member_list', $l_object_id, true);
                        }

                        $this->m_log->debug('Read and prepare stack members in user data');
                        // Get contents for model category.
                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                        C__CATG__STACK_MEMBER] = isys_jdisc_dao_devices::instance($this->m_db)
                            ->prepare_stack_member($p_row['id'], $p_jdisc_to_idoit);
                    }

                    if ($l_use_default_template && $newObject) {
                        if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__STACK_MEMBER])) {
                            $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__STACK_MEMBER] = true;
                            if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__STACK_MEMBER]['category_entities'])) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__STACK_MEMBER]['category_entities'] = array_replace_recursive(
                                    $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__STACK_MEMBER]['category_entities'],
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__STACK_MEMBER]['category_entities']
                                );
                            } else {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__STACK_MEMBER]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__STACK_MEMBER]['category_entities'];
                            }
                        }
                    }
                }

                if (defined('C__CATG__SUPPORT_ENTITLEMENT')) {
                    if (isset($l_activated_categories[C__CATG__SUPPORT_ENTITLEMENT])) {
                        // Reset category support entitlements, data will be deleted before dataretrieval
                        if (isys_jdisc_dao_data::clear_data() === true) {
                            $this->m_dao->clear_category('isys_catg_support_entitlement_list', $l_object_id, false);
                        }

                        $this->m_log->debug('Read and prepare support entitlement data');
                        $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                            C__CATG__SUPPORT_ENTITLEMENT] = isys_jdisc_dao_category_support_entitlement::instance($this->m_db)
                                ->getDataForImport($p_row['id'], false, $p_jdisc_to_idoit, $p_object_ids);
                    }

                    if ($l_use_default_template && $newObject) {
                        if (isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__SUPPORT_ENTITLEMENT])) {
                            $l_black_list[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__SUPPORT_ENTITLEMENT] = true;
                            if (isset($l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__SUPPORT_ENTITLEMENT]['category_entities'])) {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__SUPPORT_ENTITLEMENT]['category_entities'] = array_merge(
                                    $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__SUPPORT_ENTITLEMENT]['category_entities'],
                                    $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' . C__CATG__SUPPORT_ENTITLEMENT]['category_entities']
                                );
                            } else {
                                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                                C__CATG__SUPPORT_ENTITLEMENT]['category_entities'] = $this->m_obj_type_tpls[$p_row['idoit_obj_type']][C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__SUPPORT_ENTITLEMENT]['category_entities'];
                            }
                        }
                    }
                }
            }

            if ($l_use_default_template && $newObject && isset($this->m_obj_type_tpls[$p_row['idoit_obj_type']])) {
                // Merge missing categories from the default template
                isys_jdisc_dao_devices::instance($this->m_db)
                    ->merge_default_template($l_object[$l_object_id], $this->m_obj_type_tpls[$p_row['idoit_obj_type']], $l_black_list);
            }

            // prepare custom attributes
            if (!$this->m_is_jedi && $this->m_import_custom_attributes) {
                // Reset category cluster memberships
                if (isys_jdisc_dao_data::clear_data() === true) {
                    $this->m_dao->clear_category('isys_catg_jdisc_ca_list', $l_object_id, false);
                }

                $this->m_log->debug('Prepare custom attributes');
                $l_object[$l_object_id]['categories'][C__CMDB__CATEGORY__TYPE_GLOBAL . '_' .
                defined_or_default('C__CATG__JDISC_CA')] = isys_jdisc_dao_custom_attributes::instance($this->m_db)
                    ->get_custom_attributes_by_device($p_row['id'], false);
            }

            // Mark object as non-dummy:
            $l_connections[$l_object_id][isys_import_handler_cmdb::C__DUMMY] = false;
            $p_object_ids = $p_object_ids + isys_jdisc_dao_devices::instance($this->m_db)
                    ->get_object_ids();
            $p_jdisc_to_idoit = $p_jdisc_to_idoit + isys_jdisc_dao_devices::instance($this->m_db)
                    ->get_jdisc_to_idoit_objects();
            $this->m_log->debug('# Preparation finished!');

            isys_jdisc_dao_network::instance($this->m_db)
                ->cache_data($l_object_id, null, null);
            isys_jdisc_dao_software::instance($this->m_db)
                ->cache_data($l_object_id, null, null);

            isys_jdisc_dao_software_database::instance($this->m_db)
                ->cache_data($l_object_id, null, null);

            isys_jdisc_dao_category_connection_endpoint::instance($this->m_db)
                ->cache_data($l_object_id, null, null);

            $l_return = [
                'object'      => $l_object,
                'connections' => $l_connections
            ];
            unset($l_object);
            unset($l_connections);

            return $l_return;
        }

        return false;
    }

    /**
     * Enhances the breadcrumb navigation.
     */
    public function breadcrumb_get(&$p_gets)
    {
        // Not implemented yet.
    }

    /**
     * Builds menu tree.
     *
     * @param  isys_component_tree $p_tree
     * @param  boolean             $p_system_module (optional) Is it a system module? Defaults to true.
     * @param  integer             $p_parent        (optional) Parent identifier. Defaults to null.
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        $l_parent = -1;
        if (!defined('C__MODULE__JDISC')) {
            return;
        }

        if (null !== $p_parent && is_numeric($p_parent)) {
            $l_root = $p_parent;
        } else {
            $l_root = $p_tree->add_node(C__MODULE__JDISC . '0', $l_parent, $this->language->get('LC__MODULE__JDISC'));
        }

        $l_submodule = '&' . C__GET__MODULE_SUB_ID . '=' . defined_or_default('C__MODULE__IMPORT');
        if ($p_system_module && defined('C__MODULE__SYSTEM')) {
            $p_tree->add_node(
                C__MODULE__JDISC . 9,
                $l_root,
                $this->language->get('LC__MODULE__JDISC__CONFIGURATION'),
                '?moduleID=' . C__MODULE__SYSTEM . '&what=jdisc_configuration' . '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__JDISC . '&' . C__GET__TREE_NODE . '=' .
                C__MODULE__JDISC . 9,
                null,
                'images/icons/jdisc.png',
                ($_GET['what'] === 'jdisc_configuration' ? 1 : 0),
                '',
                '',
                isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::SUPERVISOR, 'JDISC/' . C__MODULE__JDISC . '9')
            );

            $p_tree->add_node(
                C__MODULE__JDISC . 10,
                $l_root,
                $this->language->get('LC__MODULE__JDISC__PROFILES'),
                '?moduleID=' . C__MODULE__SYSTEM . '&what=jdisc_profiles' . '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__JDISC . '&' . C__GET__TREE_NODE . '=' .
                C__MODULE__JDISC . '10',
                null,
                'images/icons/jdisc.png',
                ($_GET['what'] === 'jdisc_profiles' ? 1 : 0),
                '',
                '',
                isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::SUPERVISOR, 'JDISC/' . C__MODULE__JDISC . '10')
            );
        } else {
            $p_tree->add_node(
                C__IMPORT__GET__JDISC,
                $l_root,
                $this->language->get('LC__MODULE__JDISC'),
                '?moduleID=' . C__MODULE__JDISC . '&param=' . C__IMPORT__GET__JDISC . $l_submodule . '&' . C__GET__TREE_NODE . '=' . defined_or_default('C__MODULE__IMPORT') . '3' . '&' .
                C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                '',
                'images/icons/jdisc.png',
                ($_GET['param'] == C__IMPORT__GET__JDISC ? 1 : 0)
            );
        }
    }

    /**
     * Initialize module slots
     */
    public function initslots()
    {
        isys_component_signalcollection::get_instance()
            ->connect('mod.cmdb.afterObjectTypeSave', [$this, 'slot_after_obj_type_save'])
            ->connect('mod.cmdb.viewProcessed', [$this, 'slot_view_proceessed']);
    }

    /**
     * Callback function for construction of my-doit area.
     *
     * @param   string $l_text
     * @param   string $l_link
     *
     * @return  boolean
     */
    public function mydoit_get(&$l_text, &$l_link)
    {
        return false;
    }

    /**
     * Starts module. Acts as a dispatcher for nodes and actions.
     */
    public function start()
    {
        if (!defined('C__MODULE__IMPORT') || !defined('C__MODULE__SYSTEM') || !defined('C__MODULE__JDISC')) {
            return;
        }

        global $index_includes;

        $l_gets = $this->m_userrequest->get_gets();
        $l_posts = $this->m_userrequest->get_posts();

        // Set node:
        if (array_key_exists('what', $l_gets)) {
            $this->m_node = str_replace('jdisc_', '', $l_gets['what']);
        } elseif (array_key_exists(C__GET__TREE_NODE, $l_gets)) {
            if (is_numeric($l_gets[C__GET__TREE_NODE])) {
                $this->m_node = self::C__IMPORT;
            } else {
                $this->m_node = str_replace('jdisc_', '', $l_gets[C__GET__TREE_NODE]);
            }
        } else {
            $this->m_node = self::C__IMPORT;
        }

        // Set action:

        // Default is to show list:
        if (array_key_exists(C__GET__NAVMODE, $l_posts)) {
            $this->m_action = (int)$l_posts[C__GET__NAVMODE];
        } else {
            $this->m_action = self::C__NAVMODE__NONE;
        }

        // It's a click on a list to edit an entity:
        if ($this->m_action === 0 && isset($l_gets[self::C__ENTITY])) {
            $this->m_action = C__NAVMODE__EDIT;
        }

        // Set entity:

        if (!empty($l_posts['id'])) {
            if (is_numeric($l_posts['id'])) {
                $this->m_entity = intval($l_posts['id']);
            } elseif (is_array($l_posts['id'])) {
                if (count($l_posts['id']) == 1) {
                    $this->m_entity = intval($l_posts['id'][0]);
                } else {
                    $this->m_entity = $l_posts['id'];
                }
            }
        } elseif (isset($l_gets[self::C__ENTITY])) {
            $this->m_entity = (int)$l_gets[self::C__ENTITY];
        } else {
            // Last chance to set entity:
            if (isset($l_posts['SM2__C__JDISC__PROFILE_ID']['p_strValue'])) {
                $this->m_entity = $l_posts['SM2__C__JDISC__PROFILE_ID']['p_strValue'];
            }
        }

        $l_template = $this->m_userrequest->get_template();

        // Link to JDisc import:
        $l_link_to_jdisc_import = '?moduleID=' . C__MODULE__IMPORT . '&param=' . C__IMPORT__GET__JDISC;
        $l_link_to_jdisc_configuration = '?moduleID=' . C__MODULE__SYSTEM . '&what=jdisc_configuration&moduleSubID=' . C__MODULE__IMPORT . '&treeNode=' . C__MODULE__JDISC . 9;
        $l_link_to_jdisc_profiles = '?moduleID=' . C__MODULE__SYSTEM . '&what=jdisc_profiles&moduleSubID=' . C__MODULE__IMPORT . '&treeNode=' . C__MODULE__JDISC . 10;

        $l_tpl_dir = self::getPath() . 'templates/';

        try {
            switch ($this->m_node) {
                case isys_jdisc_dao::C__CONFIGURATION:
                    isys_auth_system::instance()
                        ->check(isys_auth::VIEW, 'JDISC/' . C__MODULE__JDISC . '9');
                    $index_includes['contentbottomcontent'] = $l_tpl_dir . 'configuration.tpl';

                    $l_template->assign('link_to_jdisc_import', $l_link_to_jdisc_import);

                    switch ($this->m_action) {
                        case C__NAVMODE__SAVE:
                            if ($this->save_configuration($this->m_node)) {
                                isys_notify::success($this->language->get('LC__INFOBOX__DATA_WAS_SAVED'));
                            }
                            break;
                        case C__NAVMODE__CANCEL:
                            if ($this->m_entity > 0) {
                                if (is_numeric($l_posts['C__MODULE__JDISC__CONFIGURATION__ID'])) {
                                    $l_data = $this->load_configuration();
                                    $this->show($this->m_node, C__NAVMODE__SAVE, $l_data);
                                } else {
                                    $this->show($this->m_node, C__NAVMODE__SAVE);
                                }
                            } else {
                                $this->show_jdisc_servers();
                            }
                            break;
                        // Edit configuration:
                        case C__NAVMODE__NEW:
                            $this->show($this->m_node, $this->m_action, []);
                            break;
                        case C__NAVMODE__EDIT:
                            if ($this->m_entity > 0) {
                                $l_data = $this->load_configuration();
                                if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT) {
                                    $this->show($this->m_node, $this->m_action, $l_data);
                                } else {
                                    $this->show($this->m_node, C__NAVMODE__SAVE, $l_data);
                                }
                            } else {
                                $this->show_jdisc_servers();
                                throw new isys_exception_general($this->language->get('LC__UNIVERSAL__PLEASE_SELECT_AN_ENTRY_FROM_THE_LIST'));
                            }
                            break;
                        case C__NAVMODE__PURGE:
                            if ($this->m_entity > 0) {
                                $this->m_dao->delete($this->m_node, ['id' => $this->m_entity]);
                                $this->show_jdisc_servers();
                                isys_notify::success($this->language->get('LC__INFOBOX__DATA_WAS_DELETED'));
                            } else {
                                $this->show_jdisc_servers();
                                throw new isys_exception_general($this->language->get('LC__UNIVERSAL__PLEASE_SELECT_AN_ENTRY_FROM_THE_LIST'));
                            }
                            break;
                        case 0:
                        default:
                            $this->show_jdisc_servers();
                            break;
                    }

                    break;

                case isys_jdisc_dao::C__PROFILES:
                    isys_auth_system::instance()
                        ->check(isys_auth::VIEW, 'JDISC/' . C__MODULE__JDISC . '10');
                    $index_includes['contentbottomcontent'] = $l_tpl_dir . 'profiles.tpl';

                    $l_template->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1")
                        ->assign('link_to_jdisc_import', $l_link_to_jdisc_import)
                        ->assign('object_type_assignment_file', $l_tpl_dir . 'object_type_assignment.tpl');
                    switch ($this->m_action) {
                        case C__NAVMODE__NEW:
                            $this->show_profile($this->m_action);
                            break;
                        case C__NAVMODE__EDIT:
                            if ($this->m_entity) {
                                $l_data = $this->load_profile($this->m_entity);
                                $this->show_profile($this->m_action, $l_data);
                            } else {
                                $this->show_profiles();
                                throw new isys_exception_general($this->language->get('LC__UNIVERSAL__PLEASE_SELECT_AN_ENTRY_FROM_THE_LIST'));
                            }
                            break;
                        case C__NAVMODE__SAVE:
                            $l_id = null;
                            if (isset($l_posts['C__MODULE__JDISC__PROFILES__ID']) && !empty($l_posts['C__MODULE__JDISC__PROFILES__ID'])) {
                                $l_id = intval($l_posts['C__MODULE__JDISC__PROFILES__ID']);
                            }
                            $this->save_profile($l_id);
                            isys_notify::success($this->language->get('LC__INFOBOX__DATA_WAS_SAVED'));
                            break;
                        case C__NAVMODE__PURGE:
                            if (is_array($l_posts['id'])) {
                                // User marked one or more notifications in list
                                // mode:
                                foreach ($l_posts['id'] as $l_id) {
                                    $this->delete_profile(intval($l_id));
                                }
                                isys_notify::success($this->language->get('LC__MODULE__IMPORT__CSV__MSG__DELETE'));
                            } else {
                                $this->show_profiles();
                                throw new isys_exception_general($this->language->get('LC__UNIVERSAL__PLEASE_SELECT_AN_ENTRY_FROM_THE_LIST'));
                            }

                            $this->show_profiles();
                            break;
                        case C__NAVMODE__DUPLICATE:
                            if (isset($l_posts['id'])) {
                                $l_entities = [];

                                $l_identifier = 'C__PROFILE__';

                                foreach ($l_posts as $l_key => $l_value) {
                                    if (strpos($l_key, $l_identifier) === false) {
                                        continue;
                                    }

                                    $l_key = (int)str_replace($l_identifier, '', $l_key);
                                    $l_entities[$l_key] = $l_value;
                                }

                                $this->duplicate_profiles($l_entities);
                                isys_notify::success($this->language->get('LC__INFOBOX__DATA_WAS_DUPLICATED'));
                            }

                            $this->show_profiles();
                            break;
                        // View list:
                        case 0:
                        case C__NAVMODE__CANCEL:
                        default:
                            $this->show_profiles();
                            break;
                    }

                    break;
                case self::C__IMPORT:
                default:
                    isys_auth_import::instance()
                        ->check(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__JDISC);

                    $index_includes['contentbottomcontent'] = $l_tpl_dir . 'import.tpl';

                    $l_template->activate_editmode()
                        ->assign('link_to_jdisc_configuration', $l_link_to_jdisc_configuration)
                        ->assign('link_to_jdisc_profiles', $l_link_to_jdisc_profiles)
                        ->assign('debug_level', isys_log::C__DEBUG);

                    $this->show_import_dialog();
            }
        } catch (Exception $e) {
            isys_notify::error($e->getMessage(), ['sticky' => true]);
        }
        $this->m_dao->apply_update();
    }

    /**
     * Initiates module.
     *
     * @param   isys_module_request &$p_req
     *
     * @return  isys_module_jdisc
     */
    public function init(isys_module_request $p_req)
    {
        // Set request information:
        $this->m_userrequest = $p_req;

        $this->check_requirements();

        return $this;
    }

    /**
     * Modifies row when showing configurations. This is a callback method for
     * isys_component_list::set_row_modifier()
     *
     * @param array $p_ar_data
     */
    public function modify_configuration_rows(&$p_ar_data)
    {
        $l_yes_no = get_smarty_arr_YES_NO();
        $l_version_check = (($p_ar_data['isys_jdisc_db__version_check'] !== null) ? $p_ar_data['isys_jdisc_db__version_check'] : 0);
        $p_ar_data['isys_jdisc_db__version_check'] = $l_yes_no[$l_version_check];

        $l_default_server = (($p_ar_data['isys_jdisc_db__default_server'] !== null) ? $p_ar_data['isys_jdisc_db__default_server'] : 0);
        $p_ar_data['isys_jdisc_db__default_server'] = $l_yes_no[$l_default_server];

        $p_ar_data['isys_jdisc_profile__password'] = '***';
    }

    /**
     * Modifies row when showing profiles. This is a callback method for
     * isys_component_list::set_row_modifier()
     *
     * @param array $p_ar_data
     */
    public function modify_profile_rows(&$p_ar_data)
    {
        if (isset($p_ar_data['isys_jdisc_profile__description'])) {
            $p_ar_data['isys_jdisc_profile__description'] = nl2br($p_ar_data['isys_jdisc_profile__description']);
        }

        if (isset($p_ar_data['isys_jdisc_profile__categories'])) {
            $l_supported_categories = $this->m_dao->get_supported_categories();
            $l_selected_categories = unserialize($p_ar_data['isys_jdisc_profile__categories']);
            $l_formatted_categories = [];
            if (!is_array($l_selected_categories) || count($l_selected_categories) === 0) {
                $p_ar_data['isys_jdisc_profile__categories'] = $this->language->get('LC_UNIVERSAL__NONE_SELECTED');
            } else {
                foreach ($l_supported_categories as $l_supported_category) {
                    if (in_array($l_supported_category['id'], $l_selected_categories)) {
                        $l_formatted_categories[] = $l_supported_category['val'];
                    }
                }

                if (count($l_formatted_categories) === 0) {
                    $p_ar_data['isys_jdisc_profile__categories'] = $this->language->get('LC_UNIVERSAL__NONE_SELECTED');
                } else {
                    $l_string_to_list = function ($p_value) {
                        return '<li>' . $p_value . '</li>';
                    };
                    $l_formatted_categories = array_map($l_string_to_list, $l_formatted_categories);
                    $p_ar_data['isys_jdisc_profile__categories'] = '<ul>' . implode(PHP_EOL, $l_formatted_categories) . '</ul>';
                }
            }
        }

        $l_jdisc_server_data = $this->get_jdisc_servers($p_ar_data['isys_jdisc_profile__jdisc_server'], true)
            ->get_row();

        $p_ar_data['isys_jdisc_profile__jdisc_server'] = $l_jdisc_server_data['isys_jdisc_db__host'] . ':' . $l_jdisc_server_data['isys_jdisc_db__database'] .
            ($l_jdisc_server_data['isys_jdisc_db__title'] ? ' (' . $l_jdisc_server_data['isys_jdisc_db__title'] . ')' : '');

        $l_yes_no = get_smarty_arr_YES_NO();

        $l_software = (int)$p_ar_data['isys_jdisc_profile__import_all_software'];
        $p_ar_data['isys_jdisc_profile__import_all_software'] = $l_yes_no[$l_software];

        $l_software_licence = (int)$p_ar_data['isys_jdisc_profile__import_software_licences'];
        $p_ar_data['isys_jdisc_profile__import_software_licences'] = $l_yes_no[$l_software_licence];

        $importSoftwareServices = (int)$p_ar_data['isys_jdisc_profile__import_software_services'];
        $p_ar_data['isys_jdisc_profile__import_software_services'] = $l_yes_no[$importSoftwareServices];

        $l_networks = (int)$p_ar_data['isys_jdisc_profile__import_all_networks'];
        $p_ar_data['isys_jdisc_profile__import_all_networks'] = $l_yes_no[$l_networks];

        $l_clusters = (int)$p_ar_data['isys_jdisc_profile__import_all_clusters'];
        $p_ar_data['isys_jdisc_profile__import_all_clusters'] = $l_yes_no[$l_clusters];

        $l_blade_connections = (int)$p_ar_data['isys_jdisc_profile__import_all_blade_connections'];
        $p_ar_data['isys_jdisc_profile__import_all_blade_connections'] = $l_yes_no[$l_blade_connections];

        $l_custom_attributes = (int)$p_ar_data['isys_jdisc_profile__import_custom_attributes'];
        $p_ar_data['isys_jdisc_profile__import_custom_attributes'] = $l_yes_no[$l_custom_attributes];

        $l_use_default_templates = (int)$p_ar_data['isys_jdisc_profile__use_default_templates'];
        $p_ar_data['isys_jdisc_profile__use_default_templates'] = $l_yes_no[$l_use_default_templates];

        $l_vlans = (int)$p_ar_data['isys_jdisc_profile__import_all_vlans'];
        $p_ar_data['isys_jdisc_profile__import_all_vlans'] = $l_yes_no[$l_vlans];
    }

    /**
     * Checks for the current JDisc Version
     *
     * @return bool
     */
    public function check_version($p_jdisc_servier_id)
    {
        $l_current_version = $this->m_dao->get_version($p_jdisc_servier_id);
        $l_config = array_pop($this->m_dao->get_configuration(null, ['id' => $p_jdisc_servier_id]));

        if ((bool)$l_config['version_check'] === false) {
            return true;
        } else {
            return ($l_current_version >= self::C__MODULE__JDISC__VERSION);
        }
    }

    /**
     * Gets the currently installed JDisc version
     *
     * @return float
     */
    public function get_version($p_jdisc_server_id)
    {
        return $this->m_dao->get_version($p_jdisc_server_id);
    }

    /**
     * Prepares the filter for the device query
     *
     * @param $p_filter_type
     * @param $p_filter_data
     */
    public function prepare_filter($p_filter_type, $p_filter_data)
    {
        switch ($p_filter_type) {
            case 'filter_hostaddress':
                $this->prepare_ip_filter($p_filter_data);
                break;
        }
    }

    /**
     * Checks if jdisc profile with specified id exists
     *
     * @param $p_id
     *
     * @return bool
     */
    public function check_profile($p_id)
    {
        return $this->m_dao->profile_exists($p_id);
    }

    /**
     * Switches the database
     *
     * @param $p_id
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function switch_database($p_id)
    {
        $this->m_server_id = $p_id;

        return $this->m_dao->switch_database($p_id);
    }

    /**
     * Gets jdisc operating systems
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_jdisc_operating_systems()
    {
        return $this->m_dao->get_jdisc_operating_systems();
    }

    /**
     * Gets counts for software, network, cluster and blade chassis connections
     *
     * @return mixed
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_count_for_options()
    {
        return [
            // Import software:
            'software_counter'          => isys_jdisc_dao_software::instance($this->m_db)->count_software(),
            // Import layer 3 nets:
            'network_counter'           => isys_jdisc_dao_network::instance($this->m_db)->count_networks(),
            // Import cluster:
            'cluster_counter'           => isys_jdisc_dao_cluster::instance($this->m_db)->count_cluster(),
            // Import blade chassis:
            'blade_connections_counter' => isys_jdisc_dao_devices::instance($this->m_db)->count_chassis_connections(),
        ];
    }

    /**
     * Gets all jdisc groups
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_jdisc_groups()
    {
        return $this->m_dao->get_jdisc_groups();
    }

    /**
     * Gets all or one specific jdisc profile
     *
     * @param int  $p_id
     * @param bool $p_default_server
     *
     * @return array|isys_component_dao_result
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_jdisc_profiles($p_id = null, $p_default_server = false)
    {
        if ($p_id !== null) {
            $l_condition = ['jdisc_server' => [$p_id]];
        } else {
            $l_condition = null;
        }

        if ($p_default_server) {
            $l_condition['jdisc_server'][] = 'null';
        }

        return $this->m_dao->get_profiles(null, $l_condition);
    }

    /**
     * Checks if the selected profile is assigned to the specified jdisc server
     *
     * @param $p_profile_id
     * @param $p_jdisc_server
     *
     * @return array|isys_component_dao_result
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function check_profile_in_server($p_profile_id, $p_jdisc_server)
    {
        $l_condition = [
            'id'           => $p_profile_id,
            'jdisc_server' => $p_jdisc_server
        ];

        return $this->m_dao->get_profiles(null, $l_condition);
    }

    /**
     * @param null|string $p_filter
     *
     * @return isys_component_dao_result
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_jdisc_server_list($p_filter = null)
    {
        return $this->m_dao->get_jdisc_server_list($p_filter);
    }

    /**
     * Get jdisc discovery data
     *
     * @param      $p_id
     *
     * @param bool $p_default
     *
     * @return mixed
     */
    public function get_jdisc_discovery_data($p_id = null, $p_default = false)
    {
        return $this->m_dao->get_jdisc_discovery_data($p_id, $p_default);
    }

    /**
     * Gets all jdisc servers
     *
     * @param null $p_id
     * @param bool $p_default_server
     *
     * @return isys_component_dao_result
     */
    public function get_jdisc_servers($p_id = null, $p_default_server = false)
    {
        return $this->m_dao->get_jdisc_servers($p_id, $p_default_server);
    }

    /**
     * Gets all jdisc servers as array
     *
     * @param bool $raw
     *
     * @return array
     */
    public function get_jdisc_servers_as_array($raw = false)
    {
        $l_res = $this->get_jdisc_servers();
        $l_return = [];

        while ($l_row = $l_res->get_row()) {
            if ($raw) {
                $l_return[$l_row['isys_jdisc_db__id']] = $l_row;
            } else {
                $l_return[$l_row['isys_jdisc_db__id']] = $l_row['isys_jdisc_db__host'] . ':' . $l_row['isys_jdisc_db__port'] . ' (' .
                    ($l_row['isys_jdisc_db__title'] ? $l_row['isys_jdisc_db__title'] . ' -> ' . $l_row['isys_jdisc_db__database'] : $l_row['isys_jdisc_db__database']) . ')';
            }
        }

        return $l_return;
    }

    /**
     * Callback method for retrieving the all jdisc servers as an array
     *
     * @param isys_request $p_request
     *
     * @return string
     */
    public function callback_get_jdisc_servers_as_array(isys_request $p_request)
    {
        $l_module = isys_module_jdisc::factory();
        $l_arr = $l_module->get_jdisc_servers_as_array();

        return serialize($l_arr);
    }

    /**
     * Retrieve all mac addresses for the selected device
     *
     * @param int  $p_device_id
     * @param bool $p_unique
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_mac_addresses_by_device_id($p_device_id, $p_unique = false)
    {
        return isys_jdisc_dao_network::instance($this->m_db)
            ->get_mac_addresses($p_device_id, $p_unique);
    }

    /**
     * All Chassis types in JDisc which are defined in the profile
     *
     * @return array
     */
    public function get_jdisc_chassis_types()
    {
        return $this->m_chassis_types;
    }

    /**
     * Wrapper for checking if the current selected jdisc-server is a jedi version or not
     *
     * @return bool
     */
    public function is_jedi()
    {
        return $this->m_dao->is_jedi_version();
    }

    /**
     * Wrapper for retrieving the jdisc server by jdisc profile
     *
     * @param int $p_jdisc_profile_id
     *
     * @return array
     * @throws isys_exception_database
     */
    public function get_jdisc_server_by_profile($p_jdisc_profile_id)
    {
        return $this->m_dao->get_jdisc_server_by_profile($p_jdisc_profile_id)
            ->get_row();
    }

    /**
     * Check if web service is active or not
     *
     * @param $p_jdisc_server_id
     *
     * @return bool
     */
    public function web_service_active($p_jdisc_server_id)
    {
        $l_discovery_data = $this->m_dao->get_jdisc_discovery_data($p_jdisc_server_id)
            ->get_row();
        if (empty($l_discovery_data) || empty($l_discovery_data['isys_jdisc_db__discovery_username'])) {
            return false;
        }
        try {
            isys_jdisc_dao_discovery::get_instance()->connect(
                $l_discovery_data['isys_jdisc_db__host'],
                $l_discovery_data['isys_jdisc_db__discovery_username'],
                isys_helper_crypt::decrypt($l_discovery_data['isys_jdisc_db__discovery_password']),
                $l_discovery_data['isys_jdisc_db__discovery_port'],
                $l_discovery_data['isys_jdisc_db__discovery_protocol']
            )
                ->disconnect();
            return true;
        } catch (Exception $e) {
            // do nothing
        }
        return false;
    }

    /**
     * Called after a view was processed
     *
     * @param isys_cmdb_view $p_cmdb_view
     * @param mixed          $p_process_result
     */
    public function slot_view_proceessed($p_cmdb_view, $p_process_result)
    {
        global $index_includes, $g_absdir;

        if ($p_cmdb_view->get_id() == C__CMDB__VIEW__CONFIG_OBJECTTYPE) {
            $l_object_type = ($_POST['id'][0]) ?: $_GET[C__CMDB__GET__OBJECTTYPE];
            if ($l_object_type > 0 || $_POST[C__GET__NAVMODE] == C__NAVMODE__NEW) {
                $l_dao = new isys_jdisc_dao($this->m_db, isys_log_null::get_instance());
                $l_profiles = $l_dao->get_profiles();
                $l_dialog_data = $l_jdisc_servers = [];
                $l_default_jdisc_profile = null;
                $l_default_jdisc_server = null;
                $l_jdisc_servers_res = $l_dao->get_jdisc_server_list();
                while ($l_row = $l_jdisc_servers_res->get_row()) {
                    if ($l_row['isys_jdisc_db__default_server'] > 0) {
                        $l_default_jdisc_server = $l_row['isys_jdisc_db__id'];
                    }
                    $l_jdisc_servers[$l_row['isys_jdisc_db__id']] = $l_row['isys_jdisc_db__host'] . ':' . $l_row['isys_jdisc_db__database'] .
                        ($l_row['isys_jdisc_db__title'] ? '(' . $l_row['isys_jdisc_db__title'] . ')' : '');
                }

                if (is_array($l_profiles) && count($l_profiles)) {
                    foreach ($l_profiles as $l_id => $l_profile) {
                        $l_key = $l_profile['jdisc_server'] ?: $l_default_jdisc_server;
                        if ($l_key) {
                            $l_dialog_data[$l_jdisc_servers[$l_key]][$l_id] = $l_profile['title'];
                        }
                    }
                }

                if ($l_object_type) {
                    $l_default_jdisc_profile = $p_cmdb_view->get_dao_cmdb()
                        ->get_object_types($l_object_type)
                        ->get_row_value('isys_obj_type__isys_jdisc_profile__id');
                }
                $l_rules["C__JDISC_DEFAULT__PROFILE"] = [
                    'p_arData'        => $l_dialog_data,
                    'p_strSelectedID' => ($l_default_jdisc_profile) ? $l_default_jdisc_profile : null
                ];
                $index_includes['contentbottomcontentaddition'][] = $g_absdir . '/src/classes/modules/jdisc/templates/obj_type_config.tpl';
                isys_application::instance()->template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
            }
        }
    }

    /**
     * Called after an object type is saved
     *
     * @param int     $p_objtype_id
     * @param array   $p_posts
     * @param boolean $p_was_saved
     */
    public function slot_after_obj_type_save($p_objtype_id, $p_posts, $p_was_saved = true)
    {
        // If object type was saved correctly, go further
        if ($p_was_saved && isset($p_posts['C__JDISC_DEFAULT__PROFILE'])) {
            $l_dao = new isys_jdisc_dao($this->m_db, isys_log_null::get_instance());
            $l_dao->set_jdisc_default_profile($p_objtype_id, $p_posts['C__JDISC_DEFAULT__PROFILE']);
        }
    }

    /**
     * Callback method for retrieving the all jdisc servers as an array
     *
     * @param isys_request $p_request
     *
     * @return array
     */
    public function callback_get_cmdb_status_as_array(isys_request $p_request)
    {
        /**
         * @var $l_dao isys_cmdb_dao_status
         */
        $l_dao = isys_cmdb_dao_status::factory($this->m_db);
        $blacklistedStatuses = filter_defined_constants([
            'C__CMDB_STATUS__IDOIT_STATUS',
            'C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE'
        ]);
        $blacklistedCondition = '';
        if (!empty($blacklistedStatuses)) {
            $blacklistedCondition = ' AND isys_cmdb_status__id NOT IN (' . implode(', ', $blacklistedStatuses) . ')';
        }
        $l_res = $l_dao->get_cmdb_status(null, $blacklistedCondition);
        $l_arr = [
            '-1' => $this->language->get('LC__MODULE__JDISC__PROFILES__KEEP_CMDB_STATUS')
        ];
        while ($l_row = $l_res->get_row()) {
            $l_arr[$l_row['isys_cmdb_status__id']] = $this->language->get($l_row['isys_cmdb_status__title']);
        }

        return $l_arr;
    }

    /**
     * Gets module nodes.
     *
     * @return  array  Multi-dimensional indexed array with translated titles.
     */
    protected function get_nodes()
    {
        if (isset($this->m_nodes)) {
            return $this->m_nodes;
        }

        // Root node:
        $this->m_nodes = [
            self::C__ROOT => [
                'title' => $this->language->get('LC__MODULE__JDISC'),
                'nodes' => [
                    isys_jdisc_dao::C__CONFIGURATION => [
                        'title' => $this->language->get('LC__MODULE__JDISC__CONFIGURATION')
                    ],
                    isys_jdisc_dao::C__PROFILES      => [
                        'title' => $this->language->get('LC__MODULE__JDISC__PROFILES')
                    ],
                    self::C__IMPORT                  => [
                        'title' => $this->language->get('LC__MODULE__JDISC__IMPORT')
                    ]
                ]
            ]
        ];

        return $this->m_nodes;
    }

    /**
     * Loads configuration from database.
     *
     * @return array Associative array
     */
    protected function load_configuration()
    {
        $l_data = [];

        $l_result = end($this->m_dao->get_configuration(null, ['id' => $this->m_entity]));
        if ($l_result === false) {
            return null;
        }

        $l_data[isys_jdisc_dao::C__CONFIGURATION] = $l_result;

        return $l_data;
    }

    /**
     * Show entity in view or edit mode.
     *
     * @param string $p_type   Entity type
     * @param int    $p_mode   Show or edit/new mode?
     * @param array  $p_data   (optional) Data for all property types. Defaults to
     *                         null.
     * @param array  $p_result (optional) Validation results for all property
     *                         types. Defaults to null.
     */
    protected function show($p_type, $p_mode, $p_data = null, $p_result = null)
    {
        if (!defined('C__MODULE__JDISC')) {
            return;
        }
        $l_template = $this->m_userrequest->get_template();
        $l_navbar = isys_component_template_navbar::getInstance();

        $l_edit_right = isys_auth_system::instance()
            ->is_allowed_to(isys_auth::EDIT, 'JDISC/' . C__MODULE__JDISC . '9');

        // Mode:
        if ($p_mode === C__NAVMODE__NEW || $p_mode === C__NAVMODE__EDIT) {
            $l_template->activate_editmode();

            $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                ->set_active(true, C__NAVBAR_BUTTON__CANCEL)
                ->set_active(false, C__NAVBAR_BUTTON__EDIT)
                ->set_active(false, C__NAVBAR_BUTTON__NEW);
        } elseif ($p_mode === C__NAVMODE__SAVE) {
            $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
                ->set_active(false, C__NAVBAR_BUTTON__NEW);
        } elseif ($p_mode === C__NAVMODE__CANCEL) {
            $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
                ->set_active(false, C__NAVBAR_BUTTON__NEW);
        } elseif ($p_mode === C__NAVMODE__PURGE) {
            $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
                ->set_active(false, C__NAVBAR_BUTTON__NEW);
        } else {
            $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
                ->set_active(false, C__NAVBAR_BUTTON__NEW);
        }

        $l_navbar->set_active(false, C__NAVBAR_BUTTON__PURGE)
            ->set_visible(false, C__NAVBAR_BUTTON__ARCHIVE)
            ->set_visible(false, C__NAVBAR_BUTTON__DELETE)
            ->set_visible(false, C__NAVBAR_BUTTON__PURGE)
            ->set_visible(false, C__NAVBAR_BUTTON__RECYCLE);

        // Assign identifier:
        $l_template->assign('jdisc_id', $p_data[$p_type]['id']);

        $l_properties = $this->m_dao->get_properties();

        // Assign rules and optionally data and validation results:
        $l_data = null;
        if (is_array($p_data) && isset($p_data[$p_type])) {
            $l_data = $p_data[$p_type];
        }

        $l_result = null;
        if (is_array($p_result) && isset($p_result[$p_type])) {
            $l_result = $p_result[$p_type];
        }

        // Assign rules:
        $l_template->smarty_tom_add_rules('tom.content.bottom.content', $this->prepare_user_data_assignment($l_properties[$p_type], $l_data, $l_result));
    }

    /**
     * Shows list of profiles.
     */
    protected function show_profiles()
    {
        if (!defined('C__MODULE__JDISC') || !defined('C__MODULE__IMPORT')) {
            return;
        }
        // Manipulate list:
        $l_header = [];
        $l_template = $this->m_userrequest->get_template();
        $l_result_set = $this->m_dao->get_profiles(null, (isset($_POST['filter']) ? ['title' => $_POST['filter'] . '%'] : null), true, true);
        $l_properties = $this->m_dao->get_properties(isys_jdisc_dao::C__PROFILES);
        $l_entity_id_field = $l_properties['id'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];

        foreach ($l_properties as $l_property) {
            $l_header[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] = $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE];
        }

        // Manipulate navigation bar:
        $l_filled_list = true;
        if ($l_result_set->num_rows() === 0) {
            $l_filled_list = false;
        }

        $l_link_to_jdisc_import = '?moduleID=' . C__MODULE__IMPORT . '&param=' . C__IMPORT__GET__JDISC;
        $l_template->assign('content_title', $this->language->get('LC__MODULE__JDISC__PROFILES') . "<a id='jDiscImportLink' href='" . $l_link_to_jdisc_import . "'>" .
            $this->language->get('LC__MODULE__JDISC__IMPORT') . "</a>")
            ->assign('g_list', $this->create_list($l_result_set, $l_entity_id_field, $l_header, 'modify_profile_rows'));

        $l_edit_right = isys_auth_system::instance()
            ->is_allowed_to(isys_auth::EDIT, 'JDISC/' . C__MODULE__JDISC . '10');

        isys_component_template_navbar::getInstance()
            ->set_js_function(" onclick=\"get_popup('duplicate_jdisc_profile', null, '640', '200');\"", C__NAVBAR_BUTTON__DUPLICATE)
            ->set_active($l_edit_right, C__NAVBAR_BUTTON__DUPLICATE)
            ->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
            ->set_active((($l_filled_list) ? $l_edit_right : $l_filled_list), C__NAVBAR_BUTTON__EDIT)
            ->set_visible($l_filled_list, C__NAVBAR_BUTTON__DUPLICATE)
            ->set_active(isys_auth_system::instance()
                ->is_allowed_to(isys_auth::DELETE, 'JDISC/' . C__MODULE__JDISC . '10'), C__NAVBAR_BUTTON__PURGE)
            ->set_visible(true, C__NAVBAR_BUTTON__PURGE)
            ->set_visible(false, C__NAVBAR_BUTTON__ARCHIVE)
            ->set_visible(false, C__NAVBAR_BUTTON__DELETE)
            ->set_visible(false, C__NAVBAR_BUTTON__RECYCLE);
    }

    /**
     * Creates an HTML table list of entities.
     *
     * @param isys_component_dao_result $p_result_set
     * @param string                    $p_entity_id_field
     * @param array                     $p_columns
     * @param string                    $p_row_modifier
     * @param string                    $p_type
     *
     * @return string
     * @throws Exception
     */
    protected function create_list($p_result_set, $p_entity_id_field, $p_columns, $p_row_modifier = null, $p_type = 'jdisc_profiles')
    {
        if (!defined('C__MODULE__SYSTEM') || !defined('C__MODULE__JDISC')) {
            return;
        }
        isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__EDIT)
            ->set_active(true, C__NAVBAR_BUTTON__NEW)
            ->set_active(true, C__NAVBAR_BUTTON__PURGE);

        $l_objList = new isys_component_list();

        switch ($p_type) {
            case 'jdisc_configuration':
                $l_treenode = self::C__MODULE__JDISC__TREE_LIST_CONFIGURATION;
                break;
            case 'jdisc_profiles':
            default:
                $l_treenode = self::C__MODULE__JDISC__TREE_LIST_PROFILES;
                break;
        }

        $url = isys_helper_link::create_url([
            C__GET__MODULE_ID     => C__MODULE__SYSTEM,
            'what'                => $p_type,
            C__GET__MODULE_SUB_ID => C__MODULE__JDISC,
            C__GET__TREE_NODE     => C__MODULE__JDISC . $l_treenode,
            self::C__ENTITY       => '[{' . $p_entity_id_field . '}]'
        ]);

        $l_objList->config($p_columns, $url, '[{' . $p_entity_id_field . '}]', true, true);

        $l_objList->setIdField($p_entity_id_field);

        if (isset($p_row_modifier)) {
            $l_objList->set_row_modifier($this, $p_row_modifier);
        }

        return $l_objList->getTempTableHtml($p_result_set);
    }

    /**
     * Shows a JDisc profile.
     *
     * @param int   $p_mode   Edit mode
     * @param array $p_data   Profile data
     * @param array $p_result (optional) Validation result
     */
    protected function show_profile($p_mode, $p_data = null, $p_result = null)
    {
        if (!defined('C__MODULE__JDISC')) {
            return;
        }

        $l_template = $this->m_userrequest->get_template();
        $l_navbar = isys_component_template_navbar::getInstance();

        $l_is_jedi_version = true;
        $l_jdisc_types = [];
        $l_jdisc_operating_systems = [];
        $l_software_counter = 0;
        $l_network_counter = 0;
        $l_cluster_counter = 0;
        $l_blade_connections_counter = 0;
        $l_jdisc_server = null;
        $l_blade_connections_types = '';

        if (isys_auth_system::instance()
            ->is_allowed_to(isys_auth::EDIT, 'JDISC/' . C__MODULE__JDISC . '10')) {
            $l_template->activate_editmode();
            $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
        } else {
            $l_navbar->set_active(false, C__NAVBAR_BUTTON__SAVE)
                ->set_active(false, C__NAVBAR_BUTTON__CANCEL);
        }

        $l_navbar->set_active(false, C__NAVBAR_BUTTON__EDIT)
            ->set_active(false, C__NAVBAR_BUTTON__NEW)
            ->set_active(false, C__NAVBAR_BUTTON__PURGE)
            ->set_visible(false, C__NAVBAR_BUTTON__ARCHIVE)
            ->set_visible(false, C__NAVBAR_BUTTON__DELETE)
            ->set_visible(false, C__NAVBAR_BUTTON__RECYCLE);

        // Assign identifier.
        if (isset($p_data[isys_jdisc_dao::C__PROFILES]['id'])) {
            $l_template->assign('id', $p_data[isys_jdisc_dao::C__PROFILES]['id']);
        }

        if (isset($p_data[isys_jdisc_dao::C__PROFILES]['jdisc_server'])) {
            $l_jdisc_server = $p_data[isys_jdisc_dao::C__PROFILES]['jdisc_server'];
        }

        // Use default jdisc server
        if ($l_jdisc_server === null) {
            $l_jdisc_server = $this->get_jdisc_servers(null, true)
                ->get_row_value('isys_jdisc_db__id');
            $p_data[isys_jdisc_dao::C__PROFILES]['jdisc_server'] = $l_jdisc_server;
        }

        $l_properties = $this->m_dao->get_properties();
        // Flag which determines if connection to any JDisc Server can be established
        $l_is_connected = $this->m_dao->is_connected($l_jdisc_server);

        if (!$l_is_connected) {
            $l_server = $this->get_jdisc_server_by_profile($p_data[isys_jdisc_dao::C__PROFILES]['id']);
            isys_notify::error($this->language->get(
                'LC__MODULE__JDISC__ERROR_COULD_NOT_CONNECT_WITH_MESSAGE',
                $l_server['isys_jdisc_db__host'] . ':' . $l_server['isys_jdisc_db__port']
            ));
        }

        // JDisc device types.
        $l_entities = $this->m_dao->get_jdisc_device_types();
        foreach ($l_entities as $l_entity) {
            $l_jdisc_types[$l_entity['id']] = $l_entity['singular'];
        }

        if ($l_is_connected) {
            // JDisc operating systems.
            $l_entities = $this->m_dao->get_jdisc_operating_systems();
            foreach ($l_entities as $l_entity) {
                $l_value = $l_entity['osversion'];
                if (!empty($l_entity['osfamily'])) {
                    $l_value .= ' (' . $l_entity['osfamily'] . ')';
                }
                $l_jdisc_operating_systems[$l_entity['id']] = $l_value;
            }
            $l_jdisc_operating_systems = array_unique($l_jdisc_operating_systems);

            // Import software:
            $l_software_counter = isys_jdisc_dao_software::instance($this->m_db)
                ->count_software();

            // Import layer 3 nets:
            $l_network_counter = isys_jdisc_dao_network::instance($this->m_db)
                ->count_networks();

            // Import cluster:
            $l_cluster_counter = isys_jdisc_dao_cluster::instance($this->m_db)
                ->count_cluster();

            // Import blade chassis:
            $l_blade_connections_counter = isys_jdisc_dao_devices::instance($this->m_db)
                ->count_chassis_connections();
            $l_blade_connections_types = isys_jdisc_dao_devices::instance($this->m_db)
                ->get_chassis_connections_types();

            $l_is_jedi_version = $this->m_dao->is_jedi_version();
        }

        // i-doit object types.
        $l_result_set = isys_cmdb_dao::instance($this->m_db)
            ->get_objtype(null, false, C__RECORD_STATUS__NORMAL);

        $l_object_types = [];

        while ($l_row = $l_result_set->get_row()) {
            $l_object_types[$l_row['isys_obj_type__id']] = $this->language->get($l_row['isys_obj_type__title']);
        }

        asort($l_object_types);

        // Category selection:

        if (isset($p_data[isys_jdisc_dao::C__PROFILES]['categories']) && is_string($p_data[isys_jdisc_dao::C__PROFILES]['categories'])) {
            // Unserialize categories ($p_data came directly from database):
            $p_data[isys_jdisc_dao::C__PROFILES]['categories'] = unserialize($p_data[isys_jdisc_dao::C__PROFILES]['categories']);
        }
        if (isset($p_data[isys_jdisc_dao::C__PROFILES]['network_adresses']) && is_string($p_data[isys_jdisc_dao::C__PROFILES]['network_adresses'])) {
            // Unserialize network_adresses ($p_data came directly from database):
            $p_data[isys_jdisc_dao::C__PROFILES]['network_adresses'] = unserialize($p_data[isys_jdisc_dao::C__PROFILES]['network_adresses']);
        }

        $l_supported_categories = $this->m_dao->get_supported_categories();
        $l_categories = $l_supported_categories;
        $l_index = 0;
        foreach ($l_supported_categories as $l_supported_category) {
            if (is_array($p_data[isys_jdisc_dao::C__PROFILES]['categories'])) {
                if (!in_array($l_supported_category['id'], $p_data[isys_jdisc_dao::C__PROFILES]['categories'])) {
                    $l_categories[$l_index]['sel'] = false;
                }
            } else {
                $l_categories[$l_index]['sel'] = false;
            }

            $l_index++;
        }

        $l_properties[isys_jdisc_dao::C__PROFILES]['categories'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'] = $l_categories;

        $l_networkAddresses = isys_jdisc_dao::getNetworkAdressesFilter();
        foreach ($l_networkAddresses as $key => $l_addressType) {
            if (is_array($p_data[isys_jdisc_dao::C__PROFILES]['network_adresses'])) {
                if (!in_array($l_addressType['id'], $p_data[isys_jdisc_dao::C__PROFILES]['network_adresses'])) {
                    $l_networkAddresses[$key]['sel'] = false;
                }
            } else {
                $l_networkAddresses[$key]['sel'] = false;
            }
        }
        $l_properties[isys_jdisc_dao::C__PROFILES]['network_adresses'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'] = $l_networkAddresses;

        // Assign rules and optionally data and validation results for all used property types.
        $l_property_types = [
            isys_jdisc_dao::C__PROFILES,
            isys_jdisc_dao::C__OBJECT_TYPE_ASSIGNMENTS
        ];

        foreach ($l_property_types as $l_property_type) {
            $l_data = null;
            if (is_array($p_data) && isset($p_data[$l_property_type])) {
                $l_data = $p_data[$l_property_type];
            }
            if (isset($l_data[0]['port_filter_type']) && is_array($l_data[0]['port_filter_type'])) {
                foreach ($l_data as $l_key => $l_value) {
                    if (is_array($l_value['port_filter'])) {
                        $p_data[$l_property_type][$l_key]['port_filter'] = $l_data[$l_key]['port_filter'] = isys_format_json::encode($l_value['port_filter']);
                    }
                    if (is_array($l_value['port_filter'])) {
                        $p_data[$l_property_type][$l_key]['port_filter_type'] = $l_data[$l_key]['port_filter_type'] = isys_format_json::encode($l_value['port_filter_type']);
                    }
                }
            }

            if ($l_data['cmdb_status'] === null && $l_data['id'] > 0) {
                $l_data['cmdb_status'] = '-1';
            }

            $l_result = null;
            if (is_array($p_result) && isset($p_result[$l_property_type])) {
                $l_result = $p_result[$l_property_type];
            }
            // Assign rules:
            $l_template->smarty_tom_add_rules('tom.content.bottom.content', $this->prepare_user_data_assignment($l_properties[$l_property_type], $l_data, $l_result));
        }

        $l_template->assign('jdisc_ajax_url', "?call=jdisc&ajax=1")
            ->assign('titleTransformation', $this->titleTransformation)
            ->assign('is_jedi_version', $l_is_jedi_version)
            ->assign('jdisc_types', $l_jdisc_types)
            ->assign('import_all_software', (bool)$p_data[isys_jdisc_dao::C__PROFILES]['import_all_software'])
            ->assign('import_software_licences', (bool)$p_data[isys_jdisc_dao::C__PROFILES]['import_software_licences'])
            ->assign('import_software_services', (bool)$p_data[isys_jdisc_dao::C__PROFILES]['import_software_services'])
            ->assign('import_cloud_subscriptions', (bool)$p_data[isys_jdisc_dao::C__PROFILES]['import_cloud_subscriptions'])
            ->assign('importConnectionEndpoint', (bool)$p_data[isys_jdisc_dao::C__PROFILES]['import_connection_endpoint'])
            ->assign('import_all_networks', (bool)$p_data[isys_jdisc_dao::C__PROFILES]['import_all_networks'])
            ->assign('import_all_clusters', (bool)$p_data[isys_jdisc_dao::C__PROFILES]['import_all_clusters'])
            ->assign('import_all_blade_connections', (bool)$p_data[isys_jdisc_dao::C__PROFILES]['import_all_blade_connections'])
            ->assign('import_custom_attributes', (bool)$p_data[isys_jdisc_dao::C__PROFILES]['import_custom_attributes'])
            ->assign('use_default_templates', (bool)$p_data[isys_jdisc_dao::C__PROFILES]['use_default_templates'])
            ->assign('import_all_vlans', (bool)$p_data[isys_jdisc_dao::C__PROFILES]['import_all_vlans'])
            ->assign('jdisc_operating_systems', $l_jdisc_operating_systems)
            ->assign(
                'blade_chassis_connection_needed_types',
                '<span class="ml5">' . $this->language->get('LC__MODULE__JDISC__BLADE_CONNECTIONS_IMPORT__CONNECTION_TO_FOLLOWING_TYPES') . ' ' . $l_blade_connections_types .
                '</span>'
            )
            ->assign('object_types', $l_object_types)
            ->assign('software_counter', $l_software_counter)
            ->assign('network_counter', $l_network_counter)
            ->assign('cluster_counter', $l_cluster_counter)
            ->assign('blade_connections_counter', $l_blade_connections_counter)
            ->assign('object_type_assignments', $p_data[isys_jdisc_dao::C__OBJECT_TYPE_ASSIGNMENTS])
            ->assign('useSimpleDatabaseModel', (bool)$p_data[isys_jdisc_dao::C__PROFILES]['use_simple_database_mod']);
    }

    /**
     * Loads profile from database.
     *
     * @param int $p_id Identifier
     *
     * @return array Associative array
     */
    protected function load_profile($p_id)
    {
        $l_data = [];

        $l_data[isys_jdisc_dao::C__PROFILES] = current($this->m_dao->get_profile($p_id));
        $l_data[isys_jdisc_dao::C__OBJECT_TYPE_ASSIGNMENTS] = $this->m_dao->get_object_type_assignments_by_profile($p_id);

        return $l_data;
    }

    /**
     * Saves profile.
     *
     * @param int $p_id (optional) Profile identifier. If set, an existing
     *                  profile will be updated, otherwise a new one will be created. Defaults to
     *                  null (create).
     */
    protected function save_profile($p_id = null)
    {
        $l_data = [];
        $l_result = [];
        $l_validation_failed = false;
        $l_id = null;

        // Profile:

        $l_property_type = isys_jdisc_dao::C__PROFILES;
        $l_properties = $this->m_dao->get_properties($l_property_type);
        $l_data[$l_property_type] = $this->m_dao->transformDataByProperties($l_properties, $this->m_userrequest->get_posts());
        $l_result[$l_property_type] = $this->validate_property_data($l_properties, $l_data[$l_property_type]);

        $l_save_data = [];

        foreach ($l_properties as $l_property_id => $l_property_info) {
            // If identifier is not valid, just ignore it. A new entity will be
            // created.
            if ($l_property_id === 'id' && ((isset($l_data[$l_property_type]['id']) && $l_data[$l_property_type]['id'] < 1) || !isset($l_data[$l_property_id]['id']))) {
                $l_result[$l_property_type]['id'] = isys_module_dao::C__VALIDATION_RESULT__IGNORED;
                continue;
            }

            if (array_key_exists($l_property_id, $l_data[$l_property_type]) && array_key_exists($l_property_id, $l_result[$l_property_type]) &&
                $l_result[$l_property_type][$l_property_id] > isys_module_dao::C__VALIDATION_RESULT__IGNORED) {
                $l_validation_failed = true;
                break;
            }

            // Serialize categories:
            if ($l_property_id === 'categories') {
                $l_data[$l_property_type][$l_property_id] = serialize($l_data[$l_property_type][$l_property_id]);
            }
            if ($l_property_id === 'network_adresses') {
                $l_data[$l_property_type][$l_property_id] = serialize($l_data[$l_property_type][$l_property_id]);
            }

            // Save property only if create and save are provided:
            if (array_key_exists(C__PROPERTY__PROVIDES, $l_property_info)) {
                if ((isys_module_dao::C__PROPERTY__PROVIDES__CREATE & $l_property_info[C__PROPERTY__PROVIDES]) ||
                    (isys_module_dao::C__PROPERTY__PROVIDES__SAVE & $l_property_info[C__PROPERTY__PROVIDES])) {
                    $l_save_data[$l_property_id] = $l_data[$l_property_type][$l_property_id];
                }
            }
        }

        if (isset($p_id)) {
            // Update identifier:
            $l_save_data['id'] = $p_id;
        }

        if ($l_validation_failed === false) {
            $l_id = $this->m_dao->save($l_property_type, $l_save_data);

            // Update identifier:
            $l_data[$l_property_type]['id'] = $l_id;
            $l_result[$l_property_type]['id'] = isys_module_dao::C__VALIDATION_RESULT__NOTHING;
        }

        // Object Type Assignments:

        $l_property_type = isys_jdisc_dao::C__OBJECT_TYPE_ASSIGNMENTS;
        $l_properties = $this->m_dao->get_properties($l_property_type);
        $l_data[$l_property_type] = $this->m_dao->transformDataByProperties($l_properties, $this->m_userrequest->get_posts());
        $l_result[$l_property_type] = $this->validate_property_data($l_properties, $l_data[$l_property_type]);

        if ($l_validation_failed === false) {
            // Update notification identifier:
            $l_data[$l_property_type]['profile'] = $l_id;
            $l_result[$l_property_type]['profile'] = isys_module_dao::C__VALIDATION_RESULT__NOTHING;
        }

        foreach ($l_result[$l_property_type] as $l_property_id => $l_property_result) {
            // Ignore some properties which are unnecessary here:
            if (in_array($l_property_id, [
                'id',
                'profile'
            ])) {
                continue;
            }

            if ($l_property_result > isys_module_dao::C__VALIDATION_RESULT__IGNORED) {
                $l_validation_failed = true;
                break;
            }
        }

        $l_matrix = [
            'jdisc_type',
            'jdisc_type_customized',
            'jdisc_os',
            'jdisc_os_customized',
            'title_transform',
            'fqdn_addition',
            'object_type',
            'port_filter',
            'port_filter_type',
            'location'
        ];

        if ($l_validation_failed === false) {
            $l_append = ['profile' => $l_id];

            $l_entities = $this->matrix_2_entities($l_data[$l_property_type], $l_matrix, $l_append);
            // Cleanup before save:
            $this->m_dao->delete($l_property_type, ['profile' => $l_id]);

            foreach ($l_entities as $l_save_data) {
                $this->m_dao->save($l_property_type, $l_save_data);
            }
        }

        // Re-build matrix:
        $l_rebuilt_data = [];
        foreach ($l_data[$l_property_type] as $l_property => $l_values) {
            if (in_array($l_property, $l_matrix) && is_array($l_values)) {
                foreach ($l_values as $l_key => $l_value) {
                    $l_rebuilt_data[$l_key][$l_property] = $l_value;
                }
            }
        }
        $l_counter = 0;
        $l_rerebuilt_data = [];
        foreach ($l_rebuilt_data as $l_rebuilt_datum) {
            $l_rerebuilt_data[$l_counter] = $l_rebuilt_datum;
            // Add profile identifier and temporary entity identifier (this is
            // just helpful for identifying entities via JavaScript):
            $l_rerebuilt_data[$l_counter]['id'] = $l_counter;
            $l_rerebuilt_data[$l_counter]['profile'] = $l_id;
            $l_counter++;
        }
        $l_data[$l_property_type] = $l_rerebuilt_data;

        if ($l_validation_failed) {
            $this->show_profile(C__NAVMODE__NEW, $l_data, $l_result);
        } else {
            $this->show_profile(C__NAVMODE__SAVE, $l_data, $l_result);
        }
    }

    /**
     * Builds entities based on properties out of a combination of properties
     * called 'matrix'.
     *
     * @param array $p_data       Associative array of property names as keys and data
     *                            content as values
     * @param array $p_properties Array of property names (strings) which will
     *                            be handled
     * @param array $p_append     (optional) Associative array of other property
     *                            names as keys and some value as values to enrich the entities. Useful to
     *                            assign identifiers. Defaults to null.
     *
     * @return array Empty array or array of entities
     */
    protected function matrix_2_entities(&$p_data, $p_properties, $p_append = null)
    {
        assert(is_array($p_data));
        assert(is_array($p_properties));

        $l_entities = [];

        // Fetch property with maximum amount of values:
        $l_count = 0;

        foreach ($p_properties as $l_property) {
            if (is_array($p_data[$l_property])) {
                $l_count_property = count($p_data[$l_property]);
                if (!empty($p_data['port_filter_type'][0])) {
                    $p_data['port_filter'] = array_values($p_data['port_filter']);
                    $p_data['port_filter_type'] = array_values($p_data['port_filter_type']);
                }
                if ($l_count < $l_count_property) {
                    $l_count = $l_count_property;
                }
            }
        }

        for ($l_i = 0;$l_i < $l_count;$l_i++) {
            $l_entity = [];

            foreach ($p_properties as $l_property) {
                if (($l_property == 'port_filter' || $l_property == 'port_filter_type')) {
                    $p_data[$l_property][$l_i] = isys_format_json::encode($p_data[$l_property][$l_i]);
                }

                if (isset($p_data[$l_property][$l_i])) {
                    $l_entity[$l_property] = $p_data[$l_property][$l_i];
                } else {
                    $l_entity[$l_property] = null;
                }
            }

            if (isset($p_append)) {
                assert(is_array($p_append));

                foreach ($p_append as $l_key => $l_value) {
                    $l_entity[$l_key] = $l_value;
                }
            }

            $l_entities[] = $l_entity;
        }

        return $l_entities;
    }

    /**
     * Deletes profile.
     *
     * @param int $p_id Identifier
     */
    protected function delete_profile($p_id)
    {
        $this->m_dao->delete(isys_jdisc_dao::C__PROFILES, ['id' => $p_id]);

        $l_other_property_types = [
            isys_jdisc_dao::C__OBJECT_TYPE_ASSIGNMENTS
        ];

        foreach ($l_other_property_types as $l_property_type) {
            $this->m_dao->delete($l_property_type, ['profile' => $p_id]);
        }
    }

    /**
     * Duplicates profiles.
     *
     * @param array $p_profiles List of profile identifiers
     */
    protected function duplicate_profiles($p_profiles)
    {
        foreach ($p_profiles as $l_id => $l_title) {
            // Duplicate profile itself:
            $l_profile = current($this->m_dao->get_profile($l_id));

            unset($l_profile['id']);

            $l_profile['title'] = $l_title;

            $l_new_id = $this->m_dao->save(isys_jdisc_dao::C__PROFILES, $l_profile);

            // Duplicate object type assignments:
            $l_assignments = $this->m_dao->get_object_type_assignments_by_profile($l_id);

            foreach ($l_assignments as $l_assignment) {
                unset($l_assignment['id']);
                $l_assignment['profile'] = $l_new_id;
                $this->m_dao->save(isys_jdisc_dao::C__OBJECT_TYPE_ASSIGNMENTS, $l_assignment);
            }
        }
    }

    /**
     * Saves configuration. Creates a new one or updates an existing one.
     *
     * @param string $p_type Entity type
     *
     * @return bool
     */
    protected function save_configuration($p_type)
    {
        $l_data = [];
        $l_result = [];
        $l_validation_failed = false;

        $p_type = isys_jdisc_dao::C__CONFIGURATION;
        $l_properties = $this->m_dao->get_properties($p_type);
        $l_data[$p_type] = $this->m_dao->transformDataByProperties($l_properties, $this->m_userrequest->get_posts());
        $l_result[$p_type] = $this->validate_property_data($l_properties, $l_data[$p_type]);

        $l_save_data = [];

        foreach ($l_properties as $l_property_id => $l_property_info) {
            // If identifier is not valid, just ignore it. A new entity will be
            // created.
            if ($l_property_id == 'id' && ((isset($l_data[$p_type]['id']) && $l_data[$p_type]['id'] < 1) || !isset($l_data[$l_property_id]['id']))) {
                $l_result[$p_type]['id'] = isys_module_dao::C__VALIDATION_RESULT__IGNORED;
            }

            if (array_key_exists($l_property_id, $l_data[$p_type]) && array_key_exists($l_property_id, $l_result[$p_type]) &&
                $l_result[$p_type][$l_property_id] > isys_module_dao::C__VALIDATION_RESULT__IGNORED) {
                $l_validation_failed = true;
                break;
            }

            // Save property only if create and save are provided:
            if (array_key_exists(C__PROPERTY__PROVIDES, $l_property_info)) {
                if ((isys_module_dao::C__PROPERTY__PROVIDES__CREATE & $l_property_info[C__PROPERTY__PROVIDES]) ||
                    (isys_module_dao::C__PROPERTY__PROVIDES__SAVE & $l_property_info[C__PROPERTY__PROVIDES])) {
                    $l_save_data[$l_property_id] = $l_data[$p_type][$l_property_id];
                }
            }
        }

        if ($l_validation_failed === false) {
            if ($l_save_data['id'] === null) {
                unset($l_save_data['id']);
            }

            // LF: see ID-3436
            if ($_POST['C__MODULE__JDISC__CONFIGURATION__PASSWORD__action'] == isys_smarty_plugin_f_password::PASSWORD_UNCHANGED) {
                unset($l_save_data['password']);
            }

            // LF: see ID-3436
            if ($_POST['C__MODULE__JDISC__CONFIGURATION__PASSWORD__action'] == isys_smarty_plugin_f_password::PASSWORD_SET_EMPTY) {
                $l_save_data['password'] = '';
            }

            // LF: see ID-3436
            if ($_POST['C__MODULE__JDISC__CONFIGURATION__DISCOVERY_PASSWORD__action'] == isys_smarty_plugin_f_password::PASSWORD_UNCHANGED) {
                unset($l_save_data['discovery_password']);
            }

            // LF: see ID-3436
            if ($_POST['C__MODULE__JDISC__CONFIGURATION__DISCOVERY_PASSWORD__action'] == isys_smarty_plugin_f_password::PASSWORD_SET_EMPTY) {
                $l_save_data['discovery_password'] = '';
            }

            $l_id = $this->m_dao->save($p_type, $l_save_data);

            if ($l_save_data['default_server'] > 0 && $l_id) {
                $this->m_dao->reset_default_server($l_id);
            }

            // Update identifier:
            $l_data[$p_type]['id'] = $l_id;
            $l_result[$p_type]['id'] = isys_module_dao::C__VALIDATION_RESULT__NOTHING;
        }

        if ($l_validation_failed) {
            $this->show($p_type, C__NAVMODE__NEW, $l_data, $l_result);
            return false;
        } else {
            $this->show($p_type, C__NAVMODE__SAVE, $l_data, $l_result);
            return true;
        }
    }

    /**
     * Method for displaying the import dialog to select some options.
     *
     * @return  null
     */
    protected function show_import_dialog()
    {
        if (!defined('C__MODULE__SYSTEM') || !defined('C__MODULE__JDISC') || !defined('C__MODULE__IMPORT')) {
            return;
        }
        $l_template = $this->m_userrequest->get_template();
        $l_rules = [];
        $l_is_connected = false;

        // Check connection to JDisc:
        try {
            $this->m_dao->get_configuration();
        } catch (Exception $l_exception) {
            $l_link = isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                'what'                => 'jdisc_configuration',
                C__GET__MODULE_SUB_ID => C__MODULE__IMPORT,
                C__GET__TREE_NODE     => C__MODULE__IMPORT . '9'
            ]);

            $l_template->assign('error', sprintf($this->language->get('LC__MODULE__JDISC__BROKEN_JDISC_CONFIGURATION'), $l_exception->getMessage(), $l_link));

            return;
        }

        try {
            $l_servers_res = $this->get_jdisc_servers();

            $l_server_arr = [];
            $l_profile_data = [];
            $l_group_data = [];
            $l_default_server = null;
            $l_is_jedi_version = true;
            $l_discovery_arr = [];

            while ($l_row = $l_servers_res->get_row()) {
                $l_discovery_arr[$l_row['isys_jdisc_db__id']] = $l_server_arr[$l_row['isys_jdisc_db__id']] = $l_row['isys_jdisc_db__host'] . ':' .
                    $l_row['isys_jdisc_db__database'] . ($l_row['isys_jdisc_db__title'] ? ' (' . $l_row['isys_jdisc_db__title'] . ')' : '');
                if ($l_row['isys_jdisc_db__default_server'] > 0) {
                    $l_default_server = $l_row['isys_jdisc_db__id'];
                }
            }

            if ($l_default_server === null) {
                $l_default_server = key($l_server_arr);
            }

            $l_is_connected = $this->m_dao->is_connected($l_default_server);

            if ($l_is_connected) {
                $this->switch_database($l_default_server);
                $l_is_jedi_version = $this->m_dao->is_jedi_version();

                $l_groups = $this->get_jdisc_groups();

                foreach ($l_groups as $l_group) {
                    $l_group_data[$l_group['id']] = $l_group['name'] . ' (' . $this->language->get('LC__UNIVERSAL__ID') . ': ' . $l_group['id'] . ')';
                }

                asort($l_group_data);
            } else {
                $l_template->assign('error', $this->language->get('LC__MODULE__JDISC__ERROR_COULD_NOT_CONNECT_TO_JDISC_SERVER'));
            }

            // Check for profiles and groups:
            $l_profiles = $this->get_jdisc_profiles($l_default_server, true);

            foreach ($l_profiles as $l_profile) {
                $l_profile_data[$l_profile['id']] = $l_profile['title'];
            }

            $l_rules = [];
            $l_filter_types = [];
            $l_filter_files = [];

            if (($l_filter_dir = self::getPath() . 'templates/filter/')) {
                $l_filters = scandir($l_filter_dir);
                foreach ($l_filters as $l_filter_file) {
                    if ($l_filter_file === '.' || $l_filter_file === '..') {
                        continue;
                    }

                    if (file_exists($l_filter_dir . $l_filter_file) && is_file($l_filter_dir . $l_filter_file)) {
                        $l_filter_files[] = $l_filter_dir . $l_filter_file;
                        $l_filter_type = rtrim($l_filter_file, '.tpl');
                        $l_filter_types[$l_filter_type] = 'LC__MODULE__JDISC__IMPORT__FILTER_TYPE__' . strtoupper($l_filter_type);
                    }
                }
            }

            if (count($l_filter_types) > 0) {
                $l_rules['C__MODULE__JDISC__IMPORT__FILTER']['p_arData'] = $l_filter_types;
                $l_template->assign('filter_files', $l_filter_files);
            }

            $l_rules['C__MODULE__JDISC__IMPORT__IP_CONFLICTS']['p_arData'] = get_smarty_arr_YES_NO();
            $l_rules['C__MODULE__JDISC__IMPORT__IP_CONFLICTS']['p_strSelectedID'] = 0;
            $l_rules['C__MODULE__JDISC__IMPORT__JDISC_SERVERS']['p_arData'] = $l_server_arr;
            $l_rules['C__MODULE__JDISC__IMPORT__JDISC_SERVERS']['p_strSelectedID'] = $l_default_server;
            $l_rules['C__MODULE__JDISC__IMPORT__PROFILE']['p_arData'] = $l_profile_data;
            if (!$l_is_jedi_version) {
                $l_rules['C__MODULE__JDISC__IMPORT__GROUP']['p_arData'] = $l_group_data;
            }
            $l_rules['C__MODULE__JDISC__IMPORT__MODE']['p_arData'] = [
                '1'   => 'LC__MODULE__JDISC__IMPORT__MODE_APPEND',
                '1_1' => 'LC__MODULE__JDISC__IMPORT__MODE_APPEND_NEW_ONLY',
                '2'   => 'LC__MODULE__JDISC__IMPORT__MODE_UPDATE',
                '2_1'  => 'LC__MODULE__JDISC__IMPORT__MODE_UPDATE_NEW_DISCOVERED',
                '3'   => 'LC__MODULE__JDISC__IMPORT__MODE_OVERWRITE',
                '3_1'   => 'LC__MODULE__JDISC__IMPORT__MODE_OVERWRITE_NEW_DISCOVERED',
            ];
            $l_rules['C__MODULE__JDISC__IMPORT__MODE']['p_strSelectedID'] = 2;
            $l_rules['C__MODULE__JDISC__DISCOVERY__JDISC_SERVERS']['p_arData'] = $l_discovery_arr;

            $l_template->assign('jedi_version', $l_is_jedi_version)
                ->assign('ip_unique_check', (isys_tenantsettings::get('cmdb.unique.ip-address')) ? '0' : '1');
            if (!isys_tenantsettings::get('cmdb.unique.ip-address')) {
                $l_template->assign('ip_overwrite_warning', $this->language->get('LC__MODULE__JDISC__IMPORT__OVERWRITE_IP_ADDRESSES__DESCRIPTION_ACTIVATED'));
            } else {
                $l_template->assign('ip_overwrite_info', $this->language->get('LC__MODULE__JDISC__IMPORT__OVERWRITE_IP_ADDRESSES__DESCRIPTION_DEACTIVATED'));
            }

            if (count($l_profiles) === 0) {
                $l_template->assign('error', $this->language->get('LC__MODULE__JDISC__MISSING_PROFILES'));

                return;
            }
        } catch (Exception $e) {
            $l_template->assign('error', nl2br($e->getMessage()));
        }

        $l_template->assign('discovery_tpl', self::getPath() . 'templates/discovery.tpl')
            ->assign('is_connected', $l_is_connected)
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    }

    /* ------------------------------------------------------------------------------------------------ */
    /* SLOTS */
    /* ------------------------------------------------------------------------------------------------ */

    /**
     * Shows a list of all JDisc servers
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function show_jdisc_servers()
    {
        if (!defined('C__MODULE__JDISC') || !defined('C__MODULE__IMPORT')) {
            return;
        }
        $l_template = $this->m_userrequest->get_template();
        $l_navbar = isys_component_template_navbar::getInstance();

        $l_edit_right = isys_auth_system::instance()
            ->is_allowed_to(isys_auth::EDIT, 'JDISC/' . C__MODULE__JDISC . '9');
        $l_delete_right = isys_auth_system::instance()
            ->is_allowed_to(isys_auth::DELETE, 'JDISC/' . C__MODULE__JDISC . '9');

        $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
            ->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW)
            ->set_active($l_delete_right, C__NAVBAR_BUTTON__PURGE)
            ->set_visible(true, C__NAVBAR_BUTTON__PURGE)
            ->set_visible(false, C__NAVBAR_BUTTON__ARCHIVE)
            ->set_visible(false, C__NAVBAR_BUTTON__DELETE)
            ->set_visible(false, C__NAVBAR_BUTTON__RECYCLE);

        // Manipulate list:
        $l_header = [];
        $l_properties = $this->m_dao->get_properties(isys_jdisc_dao::C__CONFIGURATION);

        foreach ($l_properties as $l_key => $l_property) {
            if ($l_property[C__PROPERTY__DATA]['crypt'] === true || (strpos(' ' . $l_key, 'discovery_'))) {
                continue;
            }

            $l_header[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] = $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE];
        }

        //		$l_template->assign('g_list', $this->create_list($this->get_jdisc_servers(), 'isys_jdisc_db__id', $l_header, 'modify_configuration_rows', 'jdisc_configuration'));
        $l_link_to_jdisc_import = '?moduleID=' . C__MODULE__IMPORT . '&param=' . C__IMPORT__GET__JDISC;
        $l_template->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1")
            ->assign('content_title', $this->language->get('LC__MODULE__JDISC__CONFIGURATION') . "<a id='jDiscImportLink' href='" . $l_link_to_jdisc_import . "'>" .
                $this->language->get('LC__MODULE__JDISC__IMPORT') . "</a>")
            ->assign('g_list', $this->create_list(
                $this->get_jdisc_server_list(($_POST['filter'] ? $_POST['filter'] . '%' : null)),
                'isys_jdisc_db__id',
                $l_header,
                'modify_configuration_rows',
                'jdisc_configuration'
            ));
    }

    /**
     * Prepares device filter by hostaddresses
     *
     * @param $p_filter_data
     *
     * @author Van Quyen Hoang
     */
    private function prepare_ip_filter($p_filter_data)
    {
        if (empty($p_filter_data)) {
            return;
        }

        // Join
        $l_device_filter_join = ' LEFT JOIN ip4transport AS ip4 ON ip4.deviceid = d.id ';
        // Condition start
        $l_device_filter_condition = ' AND (';

        $l_ip_arr = [];
        $l_ip_list = null;

        if (strpos($p_filter_data, '|') !== false) {
            $l_ip_arr = explode('|', $p_filter_data);
            $l_ip_list = (!empty($l_ip_arr[0]) ? $l_ip_arr[0] : null);

            $l_single_ip = str_replace('*', '', $l_ip_arr[1]);
            if (Ip::validate_ipv6($l_single_ip)) {
                $l_single_ip = str_replace('*', '%', $l_ip_arr[1]);
                $l_device_filter_join .= ' LEFT JOIN ip6transport AS ip6 ON ip6.deviceid = d.id ';
                $l_device_filter_condition .= 'ip6.address = ' . $this->m_dao->convert_sql_text($l_single_ip) . ' ';
            } else {
                $l_single_ip_long = Ip::ip2long($l_single_ip);
                $l_device_filter_condition .= 'ip4.address = ' . $this->m_dao->convert_sql_text($l_single_ip_long) . ' ';
            }
        }

        if (count($l_ip_arr) == 0) {
            $l_ip_list = $p_filter_data;
        } else {
            $l_device_filter_condition .= ' OR ';
        }

        if (!empty($l_ip_list)) {
            $l_arr = explode(',', $l_ip_list);
            $l_new_arr = [];
            foreach ($l_arr as $l_ip) {
                if (Ip::validate_ipv6($l_ip)) {
                    $l_new_arr['ipv6'][] = $l_ip;
                } elseif (Ip::validate_ip($l_ip)) {
                    $l_new_arr['ipv4'][] = Ip::ip2long($l_ip);
                }
            }

            if (count($l_new_arr['ipv4']) > 0) {
                $l_device_filter_condition .= ' ( ';

                foreach ($l_new_arr['ipv4'] as $l_ip) {
                    $l_device_filter_condition .= "ip4.address = " . $this->m_dao->convert_sql_text($l_ip) . " OR ";
                }
                $l_device_filter_condition = rtrim($l_device_filter_condition, 'OR ');
                $l_device_filter_condition .= ' ) ';
            }

            if (count($l_new_arr['ipv6']) > 0) {
                $l_device_filter_join .= ' LEFT JOIN ip6transport AS ip6 ON ip6.deviceid = d.id ';
                if (count($l_new_arr['ipv4']) > 0) {
                    $l_device_filter_condition .= ' OR ';
                }

                $l_device_filter_condition .= ' ( ';

                foreach ($l_new_arr['ipv6'] as $l_ip) {
                    $l_device_filter_condition .= "ip6.address = " . $this->m_dao->convert_sql_text($l_ip) . " OR ";
                }
                $l_device_filter_condition = rtrim($l_device_filter_condition, 'OR ');
                $l_device_filter_condition .= ' ) ';
            }
        }
        $l_device_filter_condition = rtrim($l_device_filter_condition, 'OR ');
        $l_device_filter_condition .= ' ) ';

        isys_jdisc_dao_devices::instance($this->m_db)
            ->set_device_filter_join($l_device_filter_join);
        isys_jdisc_dao_devices::instance($this->m_db)
            ->set_device_filter_condition($l_device_filter_condition);
    }

    /**
     * Wrapper method which prepares the identifierObjID and identifierID
     *
     * @param PDOStatement $p_obj_res
     * @param array        $p_options
     *
     * @return array
     * @throws Exception
     * @throws isys_exception_general
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function prepare_devices(PDOStatement $p_obj_res, $p_options = [])
    {
        $l_device_arr = [];
        try {
            while ($l_obj_jdisc_row = $this->m_dao->get_connection()
                ->fetch_row_assoc($p_obj_res)) {
                $l_obj_jdisc_row['identifierObjID'] = null;
                $l_obj_jdisc_row['identifierID'] = null;
                $l_group_name = null;
                $l_group_arr = null;

                if ($l_obj_jdisc_row['group_name']) {
                    $l_group_name = $l_obj_jdisc_row['group_name'];
                    // No Group has been selected for the import but the device is in several groups
                    if (strpos($l_group_name, ',')) {
                        $l_group_arr = explode(',', $l_group_name);
                        $l_group_name = $l_group_arr;
                    }
                }

                if (defined('C__CATG__IDENTIFIER_TYPE__JDISC')) {
                    // Clear only the identifier with the specified deviceid
                    if ($p_options['clear_single_identifier']) {
                        isys_jdisc_dao_matching::instance()
                            ->clear_identifiers(C__CATG__IDENTIFIER_TYPE__JDISC, 'deviceid-' . $this->m_server_id, $l_obj_jdisc_row['id'], $l_group_name);
                    } elseif ($p_options['clear_identifiers']) {
                        isys_jdisc_dao_matching::instance()
                            ->clear_identifiers(C__CATG__IDENTIFIER_TYPE__JDISC, 'deviceid-' . $this->m_server_id, null, $l_group_name);
                    }
                }

                if (is_array($l_group_name)) {
                    $l_candidates = [];
                    // We have to check the id with every Group
                    foreach ($l_group_name as $l_group_part) {
                        $l_obj_id = isys_jdisc_dao_matching::instance()
                            ->get_object_id_by_device_id($l_obj_jdisc_row['id'], $l_group_part);
                        if ($l_obj_id) {
                            $l_candidates[$l_obj_id]++;
                        }
                    }

                    $l_candidates_amount = count($l_candidates);
                    if ($l_candidates_amount >= 1) {
                        if ($l_candidates_amount === 1) {
                            // We only have one candidate
                            $l_obj_jdisc_row['identifierObjID'] = key($l_candidates);
                        } else {
                            // We have several candidates
                            asort($l_candidates);
                            $l_last_candidate = end($l_candidates);
                            $l_last_candidate_obj_id = key($l_candidates);
                            $l_second_last_candidate = prev($l_candidates);
                            $l_second_last_candidate_obj_id = key($l_candidates);
                            // If the last candidate has been found more than the second last candidate then we take the object ID from the last candidate
                            if ($l_last_candidate > $l_second_last_candidate) {
                                end($l_candidates);
                                $l_obj_jdisc_row['identifierObjID'] = key($l_candidates);
                            } elseif ($l_last_candidate == $l_second_last_candidate && $l_last_candidate_obj_id == $l_second_last_candidate_obj_id) {
                                $l_obj_jdisc_row['identifierObjID'] = $l_last_candidate_obj_id;
                            }
                        }
                    }
                    $l_obj_jdisc_row['group_name'] = $l_group_arr;
                } else {
                    $l_obj_jdisc_row['identifierObjID'] = isys_jdisc_dao_matching::instance()
                        ->get_object_id_by_device_id($l_obj_jdisc_row['id'], $l_group_name);
                }

                if ($l_obj_jdisc_row['identifierObjID'] > 0 && !isys_cmdb_dao_category_g_identifier::is_identifier_missing($l_obj_jdisc_row['identifierObjID']) &&
                    $p_options['clear_identifiers'] === false) {
                    $l_obj_jdisc_row['identifierID'] = isys_cmdb_dao_category_g_identifier::instance($this->m_db)
                        ->get_id_by_key_value(
                            isys_cmdb_dao_category_g_identifier::get_identifier_type(),
                            isys_cmdb_dao_category_g_identifier::get_identifier_key(),
                            $l_obj_jdisc_row['id'],
                            (is_array($l_group_arr)) ? $l_group_arr : $l_group_name
                        );
                }

                $l_device_arr[] = $l_obj_jdisc_row;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        } // try/catch

        return $l_device_arr;
    }

    /**
     * Method for transformation of jdisc objects titles according to i-doit import profile options
     * @see ID-7391 transform imported objects titles
     * @author Illia Polianskyi
     * @param $p_profile
     * @param $p_device_arr
     * @return array
     */
    public function transformJdiscObjectsTitles($p_profile, $p_device_arr)
    {
        $l_raw_assignments = $this->m_dao->get_object_type_assignments_by_profile($p_profile);
        foreach ($l_raw_assignments as $l_raw_assignment) {
            $jdiscType = $l_raw_assignment['jdisc_type'];
            $fqdnAddition = $l_raw_assignment['fqdn_addition'];
            $titleTransform = $l_raw_assignment['title_transform'];
            if ((!$fqdnAddition) && (($titleTransform === 1) || !$titleTransform)) {
                continue;
            }

            $l_device_arr_processed = [];
            foreach ($p_device_arr as $device) {
                if ($device['type'] != $jdiscType) {
                    $l_device_arr_processed[] = $device;
                    continue;
                }

                $objectTitle = $device['name'];
                switch ($titleTransform) {
                    case isys_ajax_handler_jdisc::C__JDISC__TITLE_TRANSFORM__UPPERCASE:
                        $objectTitle = strtoupper($objectTitle);
                        break;
                    case isys_ajax_handler_jdisc::C__JDISC__TITLE_TRANSFORM__LOWERCASE:
                        $objectTitle = strtolower($objectTitle);
                        break;
                    default:
                        break;
                }
                if ($fqdnAddition) {
                    if (stripos($objectTitle, $fqdnAddition) === false) {
                        $objectTitle .= $fqdnAddition;
                    }
                }
                $device['name'] = $objectTitle;
                $l_device_arr_processed[] = $device;
            }
            $p_device_arr = $l_device_arr_processed;
        }
        return $p_device_arr;
    }

    /**
     * @return isys_auth_system
     */
    public static function getAuth()
    {
        return isys_auth_system::instance();
    }

    /**
     * @param      $expired
     *
     * @param bool $withText
     *
     * @return string
     * @throws Exception
     */
    public static function getStatusMarker($expired, $withText = true)
    {
        $color = '33C20A';
        $stateText = 'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__STATE_ACTIVE';
        if (!$expired) {
            $color = 'BC0A19';
            $stateText = 'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__STATE_INACTIVE';
        }

        return '<div class="cmdb-marker support-entitlement" style="background-color:#' . $color . ';"></div>' .
            ($withText ? isys_application::instance()->container->get('language')->get($stateText) : '') . '';
    }


    /**
     * Calculates the expiration of the support entitlement in days
     *
     * @param DateTime $expiration
     *
     * @return false|int
     * @throws Exception
     */
    public static function expiresInDays(DateTime $startDate, DateTime $endDate)
    {
        $now = new DateTime();
        $dateDiffStart = $startDate->diff($now);
        $addition = 1;

        if ($dateDiffStart->invert > 0) {
            // Start date not reached use $startDate for $now
            return isys_application::instance()->container->get('language')->get('LC__CMDB__CATG__SUPPORT_ENTITLEMENT__STATE_NOT_STARTED');
        }
        $dateDiff = $endDate->diff($now);

        if ($dateDiff->invert < 1 && $dateDiff->days > 0) {
            // already expired
            return isys_application::instance()->container->get('language')->get('LC__CMDB__CATG__SUPPORT_ENTITLEMENT__EXPIRED');
        }

        return $dateDiff->days + $addition;
    }
}
