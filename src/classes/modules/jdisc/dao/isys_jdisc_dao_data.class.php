<?php

/**
 * i-doit
 *
 * JDisc data module DAO
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-9
 */
class isys_jdisc_dao_data extends isys_module_dao
{
    // Constants for the cache types
    const C__CACHE__PORT = 1;
    const C__CACHE__LOGICAL_PORT = 2;
    const C__CACHE__INTERFACE = 4;
    const C__CACHE__INTERFACE_CONNECTIONS = 5;
    const C__CACHE__FC_PORT = 6;
    const C__CACHE__LISTENER = 7;
    const C__CACHE__SOFTARE_LICENSES = 8;
    const C__CACHE__DATABASE = 9;
    const C__CACHE__ENDPOINT_CONNECTIONS  = 10;
    const C__CACHE__ENDPOINT_INTERFACE_CONNECTIONS = 11;

    /**
     * Cache for location objects
     *
     * @var array
     */
    public static $m_cached_locations = [];

    /**
     * Contains all tables which are defined in the jdisc database
     *
     * @var array
     */
    protected static $m_active_tables = null;

    /**
     * Cache object
     *
     * @var null
     */
    protected static $m_caching = null;

    /**
     * Flag if in clear mode
     *
     * @var bool
     */
    protected static $m_clear_mode = false;

    /**
     * Object ID of the current device
     *
     * @var null
     */
    protected static $m_current_object_id = null;

    /**
     * Object Type ID of the current device
     *
     * @var null
     */
    protected static $m_current_object_type_id = null;

    /**
     * Collects all objects which has been created in the jdisc dao classes.
     * The key is the device id from the jdisc database the value is the object id from i-doit.
     *
     * @var array
     */
    protected static $m_jdisc_to_idoit_objects = [];

    /**
     * JDisc type id for Blade chassis
     *
     * @var null
     */
    protected static $m_jdisc_type_ids = [];

    /**
     * Collects all new created objects
     *
     * @var array
     */
    protected static $m_object_ids = [];

    /**
     * Cache all object types
     *
     * @var null
     */
    protected static $m_object_types_cache = null;

    /**
     * Holds an instance of the import log.
     *
     * @var  isys_log
     */
    protected $m_log = null;

    /**
     * Holds all logbook entries from the jdisc import
     *
     * @var array
     */
    protected static $m_logbook_entries = [];

    /**
     * Variable for using the PDO in every child-class without creating a new instance.
     *
     * @var  isys_component_database_pdo
     */
    protected $m_pdo;

    /**
     * Temporary table
     *
     * @var string
     */
    protected $m_temp_table = 'temp_table_jdisc';

    /**
     * @var isys_component_template_language_manager
     */
    protected $language;

    /**
     * @return isys_log
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_log()
    {
        return $this->m_log;
    }

    /**
     * Activate clear mode for categories
     */
    public static function activate_clear_mode()
    {
        self::$m_clear_mode = true;
    }

    /**
     * Deactivate clear mode for categories
     */
    public static function deactivate_clear_mode()
    {
        self::$m_clear_mode = false;
    }

    /**
     * Get clear mode for categories
     *
     * @return bool
     */
    public static function clear_data()
    {
        return self::$m_clear_mode;
    }

    public function __destruct()
    {
        unset($this->m_log);
        unset($this->m_pdo);
    }

    /**
     * Fetches data from JDisc database.
     *
     * @param   string $p_query
     *
     * @return  PDOStatement
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function fetch($p_query)
    {
        return $this->m_pdo->query($p_query);
    }

    /**
     * Fetches all JDisc device types from database. This data depends on the JDisc release.
     *
     * @return  array
     */
    public function get_jdisc_device_types()
    {
        return $this->fetch_array('SELECT * FROM devicetypelookup ORDER BY singular;');
    }

    /**
     * Fetches all JDisc operating systems from database. This data depends on the inventory data.
     *
     * @return  array
     */
    public function get_jdisc_operating_systems()
    {
        return $this->fetch_array('SELECT * FROM operatingsystem ORDER BY osversion;');
    }

    /**
     * Sets the jdisc type id for the specified jdisc type
     *
     * @param    $p_idoit_type    mixed    identifier for the array
     * @param    $p_jdisc_type    string    JDisc type as string
     */
    public function get_jdisc_type_id($p_idoit_type, $p_jdisc_type)
    {
        if (!isset(self::$m_jdisc_type_ids[$p_idoit_type])) {
            self::$m_jdisc_type_ids[$p_idoit_type] = $this->get_jdisc_type_id_by_name($p_jdisc_type);
        }

        return self::$m_jdisc_type_ids[$p_idoit_type];
    }

    /**
     * Unused "get_data()" method.
     */
    public function get_data()
    {
        // Unused.
    }

    /**
     * Returns all collected object ids so far
     *
     * @return array
     */
    public function get_object_ids()
    {
        return self::$m_object_ids;
    }

    /**
     * Adds a newly created object id to the array
     *
     * @param $p_obj_id
     */
    public function set_object_id($p_obj_id)
    {
        self::$m_object_ids[$p_obj_id] = $p_obj_id;
    }

    /**
     * Adds a newly created object id to an array with the device id from the jdisc system
     *
     * @param $p_id
     * @param $p_obj_id
     */
    public function set_jdisc_to_idoit_objects($p_id, $p_obj_id)
    {
        self::$m_jdisc_to_idoit_objects[$p_id] = $p_obj_id;
    }

    /**
     * Returns all collected object ids with the device id as key so far
     *
     * @return array
     */
    public function get_jdisc_to_idoit_objects()
    {
        return self::$m_jdisc_to_idoit_objects;
    }

    /**
     * Setter for $m_current_object_id
     *
     * @param $p_obj_id
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_current_object_id($p_obj_id)
    {
        self::$m_current_object_id = $p_obj_id;
    }

    /**
     * Getter for $m_current_object_id
     *
     * @return null
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_current_object_id()
    {
        return self::$m_current_object_id;
    }

    /**
     * Setter for $m_current_object_type_id
     *
     * @param $p_value
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_current_object_type_id($p_value)
    {
        self::$m_current_object_type_id = $p_value;
    }

    /**
     * Getter for $m_current_object_type_id
     *
     * @return null
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_current_object_type_id()
    {
        return self::$m_current_object_type_id;
    }

    /**
     * Get object type const
     *
     * @param $p_type_id
     *
     * @return mixed
     */
    public function get_object_type_const($p_type_id)
    {
        if (self::$m_object_types_cache === null) {
            self::$m_object_types_cache = new isys_array();

            $l_sql = 'SELECT isys_obj_type__id, isys_obj_type__const FROM isys_obj_type;';

            $l_query = $this->m_db->query($l_sql);
            while ($l_row = $this->m_db->fetch_row($l_query)) {
                self::$m_object_types_cache[$l_row[0]] = $l_row[1];
            }
        }

        return self::$m_object_types_cache[$p_type_id];
    }

    /**
     * Gets all active mac-addresses from the selected device
     *
     * @param $p_device_id
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_mac_addresses($p_device_id, $p_unique = false)
    {
        $l_sql = 'SELECT DISTINCT(m.ifphysaddress) AS macaddr FROM mac AS m WHERE m.ifoperstatus != 2 AND m.deviceid = ' . $this->convert_sql_id($p_device_id) .
            ' AND  m.ifphysaddress IS NOT NULL  ';

        // This addition is for retrieving mac addresses which are unique
        if ($p_unique) {
            $l_sql .= ' AND (SELECT COUNT(*) AS cnt FROM mac WHERE ifphysaddress = m.ifphysaddress GROUP BY ifphysaddress) = 1 ';
        }

        $l_res = $this->fetch($l_sql);
        $l_macaddresses = [];
        if ($this->m_pdo->num_rows($l_res) > 0) {
            while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
                $l_macaddresses[] = $l_row['macaddr'];
            }
            $this->m_pdo->free_result($l_res);
        }

        return $l_macaddresses;
    }

    /**
     * Checks if specified table exists
     *
     * @param $p_table
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function check_table($p_table)
    {
        if (self::$m_active_tables === null) {
            $this->map_tables();
        }

        return (bool)self::$m_active_tables[$p_table];
    }

    /**
     * Setter method which fills m_logbook_entries
     *
     * @param $p_value
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function set_logbook_entries($p_value)
    {
        self::$m_logbook_entries[] = $p_value;
    }

    /**
     * Getter method which retrieves the variable m_logbook_entries
     *
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function get_logbook_entries()
    {
        return self::$m_logbook_entries;
    }

    /**
     * Resets logbook entries for the current device
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function reset_logbook_entries()
    {
        self::$m_logbook_entries = [];
    }

    /**
     * Create temp table
     *
     * @param $p_data
     *
     * @throws isys_exception_dao
     */
    public function create_cache_table()
    {
        $l_query = 'CREATE TEMPORARY TABLE ' . $this->m_temp_table . ' (id INT(10) UNSIGNED, data LONGTEXT, type INT(10) UNSIGNED) ENGINE=MyISAM;';
        $this->update($l_query);
    }

    /**
     * Add entries into the temporary table
     *
     * Types:
     * 1 port
     * 2 logical_port
     * 3 update_port
     * 4 network_interfaces
     * 5 network_interfaces_connection
     * 6 net_listener_connections
     *
     * @param $p_id
     * @param $p_data
     * @param $p_type
     *
     * @return $this
     * @throws isys_exception_dao
     */
    public function cache_data($p_id, $p_data, $p_type)
    {
        $l_insert = 'INSERT INTO ' . $this->m_temp_table . ' (id, data, type) VALUES(' . $this->convert_sql_id($p_id) . ', ' .
            $this->convert_sql_text(isys_format_json::encode($p_data)) . ', ' . $this->convert_sql_int($p_type) . ')';
        $this->update($l_insert);

        return $this;
    }

    /**
     * Load relevant data from temporary table
     *
     * @param $p_obj_id
     *
     * @throws Exception
     */
    public function load_cache($p_obj_id, $p_condition = '')
    {
        $l_sql = 'SELECT * FROM ' . $this->m_temp_table . ' WHERE id = ' . $this->convert_sql_id($p_obj_id) . ' ' . $p_condition;

        return $this->m_db->query($l_sql);
    }

    /**
     * Drops temporary table
     *
     * @throws isys_exception_dao
     */
    public function drop_cache_table()
    {
        $l_query = 'DROP TEMPORARY TABLE ' . $this->m_temp_table . ';';
        $this->update($l_query);
    }

    /**
     * Fetches data from JDisc database.
     *
     * @param   string $p_query
     *
     * @return  array
     * @author  Benjamin Heisig <bheisig@i-doit.org>
     */
    protected function fetch_array($p_query)
    {
        $l_result_set = $this->m_pdo->query($p_query);

        $l_result = [];

        while ($l_row = $this->m_pdo->fetch_row_assoc($l_result_set)) {
            $l_result[] = $l_row;
        }

        return $l_result;
    }

    /**
     * Fetches the jdisc device type id by name
     *
     * @param $p_name
     *
     * @return bool|mixed
     */
    private function get_jdisc_type_id_by_name($p_name)
    {
        $l_condition_value = $this->convert_sql_text($p_name);
        $l_row = $this->fetch_array('SELECT id FROM devicetypelookup WHERE name ILIKE ' . $l_condition_value);

        return (is_array($l_row) && (count($l_row) == 1) ? current(current($l_row)) : false);
    }

    /**
     * Helper function which maps all tables which are defined in the jdisc database
     *
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     * @return void
     */
    private function map_tables()
    {
        self::$m_active_tables = new isys_array();
        $l_sql = 'SELECT tablename FROM pg_tables WHERE schemaname = ' . $this->convert_sql_text('public');

        $l_res = $this->fetch($l_sql);
        if ($this->m_pdo->num_rows($l_res) > 0) {
            while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
                self::$m_active_tables[$l_row['tablename']] = true;
            }
        }
    }

    /**
     * Retrieves device info specified by omitted fields
     *
     * @param        $p_id
     * @param string $p_fields
     *
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_device_info($p_id)
    {
        $l_sql = 'SELECT name, serialnumber, fqdn, address FROM device
            LEFT JOIN (SELECT fqdn, address, deviceid FROM ip4transport WHERE ip4transport.isdiscoverytransport = TRUE) AS ip ON ip.deviceid = device.id
            WHERE device.id = ' . $this->convert_sql_id($p_id) . ' LIMIT 1';
        $l_res = $this->fetch($l_sql);
        $l_data = $this->m_pdo->fetch_row_assoc($l_res);
        $l_data['hostname'] = null;

        if ($l_data['fqdn']) {
            $l_fqdn = $l_data['fqdn'];
            $l_fqdn_arr = explode('.', $l_fqdn);
            if (count($l_fqdn_arr) >= 3) {
                $l_data['hostname'] = $l_fqdn_arr[0];
            } else {
                $l_data['hostname'] = $l_fqdn;
            }
        }

        return $l_data;
    }

    /**
     * Method for assigned devices in switch chassis
     *
     * @param $p_id
     *
     * @return array|bool
     */
    public function get_modules_info($p_device_id = null, $p_module_id = null)
    {
        if ($p_device_id === null && $p_module_id === null) {
            return false;
        }

        $l_sql = 'SELECT DISTINCT(mod.serialnumber) AS serial, mod.id AS moduleid, mod.model AS title, mod.description, mod.manufacturer,
			ms.socketdesignation AS slot, mod.osversion AS os, mod.fwversion AS firmware
			FROM module AS MOD
			INNER JOIN moduleslot AS ms ON ms.itemid = MOD.id
			LEFT JOIN mac AS m ON MOD.id = m.moduleid
			WHERE MOD.model != \'\' AND MOD.serialnumber != \'\'';

        if ($p_device_id !== null) {
            $l_sql .= ' AND ms.deviceid = ' . $this->convert_sql_id($p_device_id);
        }

        if ($p_module_id !== null) {
            $l_sql .= ' AND mod.id = ' . $this->convert_sql_id($p_module_id);
        }

        $l_res = $this->fetch($l_sql);
        if ($l_res) {
            $l_return = [];
            $l_already_used_serials = [];
            while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
                if (!isset($l_already_used_serials[$l_row['serial']])) {
                    $l_return[] = $l_row;
                    $l_already_used_serials[$l_row['serial']] = true;
                }
            }

            return $l_return;
        }

        return false;
    }

    /**
     * Returns the jdisc discovery queue count
     *
     * @return int
     */
    public function getDiscoveryQueueCount()
    {
        $return = 0;

        $query = 'SELECT COUNT(*) as cnt FROM discoverydevicequeue;';
        $result = $this->fetch($query);
        if ($result) {
            $data = $this->m_pdo->fetch_row_assoc($result);
            $return = $data['cnt'];
        }

        return $return;
    }

    /**
     * Checks if there are any manaually triggered scans in the queue
     */
    public function checkManualScansInQueue()
    {
        // Source 1 = Manually triggered
        $query = 'SELECT COUNT(*) as cnt FROM discoverydevicequeue WHERE source = 1';
        $result = $this->fetch($query);
        if ($result) {
            $data = $this->m_pdo->fetch_row_assoc($result);
            return $data['cnt'];
        }
        return 0;
    }

    /**
     * @param isys_component_database $p_database
     *
     * @return static
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function instance(isys_component_database $p_database)
    {
        $l_class = static::class;

        if (!isset(self::$instances[$l_class])) {
            self::$instances[$l_class] = new $l_class($p_database, isys_application::instance()->container['jdisc_pdo']);
        }

        return self::$instances[$l_class];
    }

    /**
     * Constructor
     *
     * @param   isys_component_database     $p_db Database component
     * @param   isys_component_database_pdo $p_pdo
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function __construct(isys_component_database $p_db, isys_component_database_pdo $p_pdo)
    {
        parent::__construct($p_db);

        if (static::$m_caching === null) {
            //			static::$m_caching = isys_cache::keyvalue();
        }
        $this->m_log = isys_factory_log::get_instance('import_jdisc');
        $this->m_pdo = $p_pdo;
        $this->language = isys_application::instance()->container->get('language');
    }
}
