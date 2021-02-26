<?php

/**
 * i-doit
 *
 * JDisc module DAO
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-9
 */
class isys_jdisc_dao extends isys_module_dao
{
    const C__CONFIGURATION = 'configuration';

    const C__PROFILES = 'profiles';

    const C__COMMON_SETTINGS = 'common_settings';

    const C__OBJECT_TYPE_ASSIGNMENTS = 'object_type_assignments';

    const C__ADDITIONAL_OPTIONS = 'additional_options';

    /**
     * Data cache
     *
     * @var array
     */
    protected $m_cache;

    /**
     * Defines the current installed JDisc version
     *
     */
    protected $m_current_version;

    /**
     * Logger
     *
     * @var isys_log
     */
    protected $m_log;

    /**
     * PDO driver
     *
     * @var isys_component_database_pdo
     */
    protected $m_pdo;

    /**
     * Current jdisc server
     *
     * @var
     */
    protected $m_server_id;

    /**
     * List of categories supported by JDisc
     *
     * @var array Array of category identifiers (integers)
     */
    protected $m_supported_categories = [
        'C__CATG__GRAPHIC'                     => true,
        'C__CATG__CPU'                         => true,
        'C__CATG__MEMORY'                      => true,
        'C__CATG__STORAGE'                     => true,
        'C__CATG__DRIVE'                       => true,
        'C__CATG__IP'                          => true,
        'C__CATG__OPERATING_SYSTEM'            => true,
        'C__CATG__APPLICATION'                 => true,
        'C__CMDB__SUBCAT__NETWORK_PORT'        => true, // @todo  Remove in i-doit 1.12
        'C__CATG__NETWORK_PORT'                => true,
        'C__CATG__MODEL'                       => true,
        'C__CATG__VIRTUAL_MACHINE'             => true,
        'C__CATG__CLUSTER_MEMBERSHIPS'         => true,
        'C__CMDB__SUBCAT__NETWORK_INTERFACE_P' => true, // @todo  Remove in i-doit 1.12
        'C__CATG__NETWORK_INTERFACE'           => true,
        'C__CATG__GUEST_SYSTEMS'               => true,
        'C__CATG__RM_CONTROLLER'               => true,
        'C__CATG__NET_LISTENER'                => true,
        'C__CATG__UNIVERSAL_INTERFACE'         => true,
        'C__CATG__CONTROLLER_FC_PORT'          => true,
        'C__CATG__LAST_LOGIN_USER'             => true,
        'C__CATG__STACK_MEMBER'                => true,
        'C__CATG__LOCATION'                    => true,
        'C__CATG__SUPPORT_ENTITLEMENT'         => true
    ];

    const C_FILTER__IPV4 = 1;
    const C_FILTER__IPV6 = 2;
    const C_FILTER__LOOPBACK = 3;
    const C_FILTER__VIRTUAL = 4;

    public static function getNetworkAdressesFilter()
    {
        $language = isys_application::instance()->container->get('language');
        return [
            [
                'id'  => self::C_FILTER__IPV4,
                'val' => $language->get('LC_IPV4'),
                'sel' => true,
                'url' => ''
            ],
            [
                'id'  => self::C_FILTER__IPV6,
                'val' => $language->get('LC_IPV6'),
                'sel' => true,
                'url' => ''
            ],
            [
                'id'  => self::C_FILTER__LOOPBACK,
                'val' => $language->get('LC_IP_LOOPBACK'),
                'sel' => true,
                'url' => ''
            ],
            [
                'id'  => self::C_FILTER__VIRTUAL,
                'val' => $language->get('LC_IP_VIRTUAL'),
                'sel' => true,
                'url' => ''
            ],
        ];
    }

    /**
     * Cache for supported categories
     *
     * @var array
     * @see get_supported_categories()
     */
    protected $m_supported_categories_cached;

    /**
     * Gets information about property groups.
     *
     * @return array Associative array
     */
    public function get_property_groups()
    {
        if (!isset($this->m_groups)) {
            $this->m_groups = [
                self::C__COMMON_SETTINGS         => [
                    'title' => isys_application::instance()->container->get('language')
                        ->get('LC__MODULE__JDISC__PROFILES__COMMON_SETTINGS')
                ],
                self::C__OBJECT_TYPE_ASSIGNMENTS => [
                    'title' => isys_application::instance()->container->get('language')
                        ->get('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS')
                ],
                self::C__ADDITIONAL_OPTIONS      => [
                    'title' => isys_application::instance()->container->get('language')
                        ->get('LC__MODULE__JDISC__ADDITIONAL_OPTIONS')
                ]
            ];
        }

        return $this->m_groups;
    }

    /**
     * Gets information about property tables.
     *
     * @return array Associative array
     */
    public function get_tables()
    {
        if (!isset($this->m_tables)) {
            $this->m_tables = [
                self::C__CONFIGURATION           => 'isys_jdisc_db',
                self::C__PROFILES                => 'isys_jdisc_profile',
                self::C__OBJECT_TYPE_ASSIGNMENTS => 'isys_jdisc_object_type_assignment'
            ];
        }

        return $this->m_tables;
    }

    /**
     * Gets information about property types.
     *
     * @return array Associative array
     */
    public function get_property_types()
    {
        if (!isset($this->m_types)) {
            $this->m_types = [
                self::C__CONFIGURATION           => [
                    'title' => isys_application::instance()->container->get('language')
                        ->get('LC__MODULE__JDISC__CONFIGURATION')
                ],
                self::C__PROFILES                => [
                    'title' => isys_application::instance()->container->get('language')
                        ->get('LC__MODULE__JDISC__PROFILES')
                ],
                self::C__OBJECT_TYPE_ASSIGNMENTS => [
                    'title' => isys_application::instance()->container->get('language')
                        ->get('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS')
                ]
            ];
        }

        return $this->m_types;
    }

    /**
     * Gets list of categories supported by JDisc suitable for dialog lists.
     *
     * @return array Index array of arrays
     */
    public function get_supported_categories()
    {
        if (!isset($this->m_supported_categories_cached)) {
            $l_cmdb_dao = isys_cmdb_dao_jdisc::instance($this->m_db);
            /** @var $l_cmdb_dao isys_cmdb_dao */

            $l_all_categories = $l_cmdb_dao->get_all_categories();
            // Only global categories are needed:
            $l_categories = [];
            foreach ($l_all_categories[C__CMDB__CATEGORY__TYPE_GLOBAL] as $l_category) {
                assert(defined($l_category["const"]));
                $l_constant = $l_category['const'];

                if (!isset($this->m_supported_categories[$l_constant])) {
                    continue;
                }

                $l_categories[$l_constant] = [
                    'id'  => $l_category['id'],
                    'val' => isys_application::instance()->container->get('language')
                        ->get($l_category['title']),
                    'sel' => true,
                    'url' => ''
                ];
            }

            $l_sort = function ($p_arr1, $p_arr2) {
                return strcmp($p_arr1['val'], $p_arr2['val']);
            };
            usort($l_categories, $l_sort);

            $this->m_supported_categories_cached = $l_categories;
        }

        return $this->m_supported_categories_cached;
    }

    /**
     * Fetches database configuration data from database.
     *
     * @param array $p_selections    (optional) Select only these properties. If
     *                               not set (default), all properties will be selected.
     * @param array $p_conditions    (optional) Make some conditions. Associative
     *                               array of properties as keys and the destinated values as values. Defaults
     *                               to no condition.
     * @param bool  $p_raw           (optional) Returns unformatted ouput. Defaults to
     *                               false.
     * @param bool  $p_as_result_set (optional) Returns fetched data as result
     *                               set. Defaults to false.
     *
     * @return array|isys_component_dao_result Associative array or result set
     */
    public function get_configuration($p_selections = null, $p_conditions = null, $p_raw = false, $p_as_result_set = false)
    {
        return $this->get_entities(self::C__CONFIGURATION, $p_selections, $p_conditions, $p_raw, $p_as_result_set);
    }

    /**
     * Fetches profiles from database.
     *
     * @param array $p_selections    (optional) Select only these properties. If
     *                               not set (default), all properties will be selected.
     * @param array $p_conditions    (optional) Make some conditions. Associative
     *                               array of properties as keys and the destinated values as values. Defaults
     *                               to no condition.
     * @param bool  $p_raw           (optional) Returns unformatted ouput. Defaults to
     *                               false.
     * @param bool  $p_as_result_set (optional) Returns fetched data as result
     *                               set. Defaults to false.
     *
     * @return array|isys_component_dao_result Associative array or result set
     */
    public function get_profiles($p_selections = null, $p_conditions = null, $p_raw = false, $p_as_result_set = false)
    {
        return $this->get_entities(self::C__PROFILES, $p_selections, $p_conditions, $p_raw, $p_as_result_set);
    }

    /**
     * Fetches profile by its identifier from database.
     *
     * @param array $p_selections    (optional) Select only these properties. If
     *                               not set (default), all properties will be selected.
     * @param bool  $p_raw           (optional) Returns unformatted ouput. Defaults to
     *                               false.
     * @param bool  $p_as_result_set (optional) Returns fetched data as result
     *                               set. Defaults to false.
     *
     * @return array|isys_component_dao_result Associative array or result set
     */
    public function get_profile($p_id, $p_selections = null, $p_raw = false, $p_as_result_set = false)
    {
        $l_conditions = ['id' => $p_id];

        return $this->get_entities(self::C__PROFILES, $p_selections, $l_conditions, $p_raw, $p_as_result_set);
    }

    /**
     * @param null $p_selections
     * @param null $p_conditions
     * @param bool $p_raw
     * @param bool $p_as_result_set
     *
     * @return array|isys_component_dao_result
     */
    public function get_object_type_assignments($p_selections = null, $p_conditions = null, $p_raw = false, $p_as_result_set = false)
    {
        return $this->get_entities(self::C__OBJECT_TYPE_ASSIGNMENTS, $p_selections, $p_conditions, $p_raw, $p_as_result_set);
    }

    /**
     * @param      $p_profile
     * @param null $p_selections
     * @param null $p_conditions
     * @param bool $p_raw
     * @param bool $p_as_result_set
     *
     * @return array|isys_component_dao_result
     */
    public function get_object_type_assignments_by_profile($p_profile, $p_selections = null, $p_conditions = null, $p_raw = false, $p_as_result_set = false)
    {
        assert(is_int($p_profile));
        $l_conditions = [];
        if (is_array($p_conditions)) {
            $l_conditions = $p_conditions;
        }
        $l_conditions['profile'] = $p_profile;

        return $this->get_entities(self::C__OBJECT_TYPE_ASSIGNMENTS, $p_selections, $l_conditions, $p_raw, $p_as_result_set);
    }

    /**
     * Fetches all data from database.
     *
     * @return  array
     */
    public function get_data()
    {
        return $this->get_configuration();
    }

    /**
     * Provides access to JDisc's database.
     *
     * @throws  isys_exception_general
     * @return  isys_component_database_pdo
     */
    public function get_connection($p_config_id = null)
    {
        if (isset($this->m_pdo)) {
            return $this->m_pdo;
        }

        $this->m_log->debug('Providing access to JDisc\'s database...');

        // Fetch database configuration:
        if ($p_config_id === null) {
            $l_config = end($this->get_configuration(null, ['default_server' => '1']));
            if (is_array($l_config)) {
                $p_config_id = $l_config['id'];
            } else {
                throw new isys_exception_general('JDisc configuration is missing.');
            }
        }

        try {
            if ($this->m_server_id === null) {
                $this->m_server_id = $p_config_id;
            }
            $this->switch_database($p_config_id);
        } catch (isys_exception_database $e) {
            throw new Exception(isys_application::instance()->container->get('language')
                ->get('LC__MODULE__JDISC__ERROR_COULD_NOT_CONNECT_WITH_MESSAGE', $e->getMessage()));
        }

        return $this->m_pdo;
    }

    /**
     * Fetches all JDisc device types from database. This data depends on the JDisc release.
     *
     * @return  array
     */
    public function get_jdisc_device_types()
    {
        $l_data = $this->fetch_array('SELECT * FROM devicetypelookup WHERE id > 0 ORDER BY singular;');
        if (!$l_data) {
            // Get jdisc types from local database instead from the JDisc server.
            $l_data = $this->get_local_jdisc_device_types();
        }

        return $l_data;
    }

    /**
     * Fetches all JDisc device types from local database. In case we don't have any connection to a JDisc Server.
     *
     * @return array
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_local_jdisc_device_types()
    {
        $l_res = $this->retrieve('SELECT isys_jdisc_device_type__description AS id, isys_jdisc_device_type__title AS singular FROM isys_jdisc_device_type;');
        $l_data = [];
        while ($l_row = $l_res->get_row()) {
            $l_data[] = $l_row;
        }

        return $l_data;
    }

    /**
     * Fetches all JDisc groups from the JDisc database.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_jdisc_groups()
    {
        return $this->fetch_array('SELECT * FROM devicegroup ORDER BY id;');
    }

    public function getCloudSubscriptions()
    {
        return $this->fetch_array('SELECT o365.*,
o365lookup.name as o365SubscriptionName,
count ( distinct duo365sr.discoveredUserId ) as subscribedusercount FROM
Office365Subscription o365
left join
DiscoveredUserOffice365Subscription duo365sr on duo365sr.subscriptionId = o365.id
left join Tenant t on o365.tenantId = t.id
left join MicrosoftAzureProductLookup
o365lookup on o365lookup.uuid =o365.uuid
GROUP BY o365.id , t.name , o365lookup.name , o365.creationTime ,
o365.modificationTime , o365.tenantId , o365.partNumber , o365.uuid , o365.uuid ,
o365.capabilityStatus , o365.consumedUnits , o365.prepaidEnabled ,
o365.prepaidSuspended , o365.prepaidWarning;');
    }

    public function getCloudUsers()
    {
        return $this->fetch_array('SELECT duc.id , duc.name as cloudUserName, duc.account as cloudUserLogin,
array_to_json(array ( select name from Office365UserSubscriptionView where userId = duc.id order by
name , uuid )) as subscrNameList,
array_to_json(array ( select partNumber from Office365UserSubscriptionView where userId = duc.id
order by name , uuid )) as subscrPartNumberList,
array_to_json(array ( select capabilityStatus from Office365UserSubscriptionView where
userId = duc.id order by name , uuid )) as subscrCapabilityStatusList,
array_to_json(array ( select uuid from Office365UserSubscriptionView where userId = duc.id order by
name , uuid )) as subscrUUIDList,
array_to_json(duc.cloudUsernameList) as cloudUsernameList ,
array_to_json(duc.cloudLoginList) as cloudLoginList 
from DiscoveredCloudUserConfiguration duc
left join Device d on duc.operatingSystemId = d.id
left join DiscoveredUserTenantRelation dutr on
dutr. discoveredUserId = duc.id
WHERE duc.cloudUserType = 4 group by duc.id , duc.name , duc.account ,
duc.cloudUserNameList , duc.cloudLoginList;');
    }

    /**
     * Fetches all JDisc operating systems from database. This data depends on the inventory data.
     *
     * @param    $p_field     string
     * @param    $p_id        int
     *
     * @return  array
     */
    public function get_jdisc_operating_systems($p_field = null, $p_id = null)
    {
        $l_query = 'SELECT ';
        if ($p_field !== null) {
            $l_query .= $p_field . ' ';
        } else {
            $l_query .= ' * ';
        }
        $l_query .= 'FROM operatingsystem ';
        if ($p_id !== null) {
            $l_query .= 'WHERE id = ' . $this->convert_sql_id($p_id) . ' ';
        }
        $l_query .= 'ORDER BY osversion;';

        return $this->fetch_array($l_query);
    }

    /**
     * Gets the current JDisc version
     *
     * @return float
     */
    public function get_version($p_jdisc_servier_id)
    {
        if ($this->m_current_version != '') {
            return $this->m_current_version;
        }

        $l_dao = $this->get_connection($p_jdisc_servier_id);
        $l_result_set = $l_dao->query('SELECT * FROM installationinfo');
        $l_arr = $l_dao->fetch_row_assoc($l_result_set);

        return (float)($l_arr['majorversion'] . '.' . $l_arr['minorversion']);
    }

    /**
     * Checks if specified profile id exists
     *
     * @param $p_id
     *
     * @return bool
     */
    public function profile_exists($p_id)
    {
        return (($this->retrieve('SELECT isys_jdisc_profile__id FROM isys_jdisc_profile WHERE isys_jdisc_profile__id = ' . $this->convert_sql_id($p_id))
                ->num_rows() > 0) ? true : false);
    }

    /**
     * Resets the default server
     *
     * @param $p_exclude int
     *
     * @return bool
     */
    public function reset_default_server($p_exclude)
    {
        $l_update = 'UPDATE isys_jdisc_db SET isys_jdisc_db__default_server = 0 WHERE isys_jdisc_db__id != ' . $this->convert_sql_id($p_exclude);

        return $this->update($l_update) && $this->apply_update();
    }

    /**
     * Switches the database
     *
     * @param $p_id
     *
     * @throws isys_exception_general
     */
    public function switch_database($p_id)
    {
        $l_config = end($this->get_configuration(null, ['id' => $p_id]));

        if ($l_config) {
            try {
                $this->m_server_id = $p_id;
                $l_pdo = $this->m_pdo = new isys_component_database_pdo(
                    'pgsql',
                    $l_config['host'],
                    $l_config['port'],
                    $l_config['username'],
                    $l_config['password'],
                    $l_config['database']
                );

                // Add JDisc PDO to isys_application container
                isys_application::instance()->container['jdisc_pdo'] = function () use ($l_pdo) {
                    return $l_pdo;
                };

                return true;
            } catch (isys_exception_database $e) {
                throw new Exception(isys_application::instance()->container->get('language')
                    ->get('LC__MODULE__JDISC__ERROR_COULD_NOT_CONNECT_WITH_MESSAGE', $e->getMessage()));
            } catch (Exception $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * Gets all or one specific jdisc server
     *
     * @param int $p_id
     *
     * @return isys_component_dao_result
     */
    public function get_jdisc_servers($p_id = null, $p_default_server = false)
    {
        $l_sql = 'SELECT isys_jdisc_db__id, isys_jdisc_db__title, isys_jdisc_db__host, isys_jdisc_db__port, isys_jdisc_db__database,
			isys_jdisc_db__username, isys_jdisc_db__version_check, isys_jdisc_db__default_server FROM isys_jdisc_db';

        if ($p_id !== null) {
            $l_sql .= ' WHERE isys_jdisc_db__id = ' . $this->convert_sql_id($p_id);
        }
        if ($p_id === null && $p_default_server) {
            $l_sql .= ' WHERE isys_jdisc_db__default_server = 1';
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Get JDisc Server list resultset for the list view
     *
     * @param null|string $p_filter
     *
     * @return isys_component_dao_result
     */
    public function get_jdisc_server_list($p_filter = null)
    {
        $l_sql = 'SELECT isys_jdisc_db__id, isys_jdisc_db__title, isys_jdisc_db__host, isys_jdisc_db__port, isys_jdisc_db__database,
			isys_jdisc_db__username, isys_jdisc_db__version_check, isys_jdisc_db__host,
			isys_jdisc_db__discovery_username, isys_jdisc_db__discovery_password, isys_jdisc_db__discovery_port,
			isys_jdisc_db__discovery_protocol, isys_jdisc_db__default_server, isys_jdisc_db__discovery_timeout FROM isys_jdisc_db WHERE TRUE';

        if (!empty($p_filter)) {
            $l_sql .= ' AND isys_jdisc_db__host LIKE ' . $this->convert_sql_text($p_filter);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Get relevant information for the jdisc discovery
     *
     * @param $p_id
     *
     * @return mixed
     */
    public function get_jdisc_discovery_data($p_id = null, $p_default = false)
    {
        $l_sql = 'SELECT isys_jdisc_db__host, isys_jdisc_db__discovery_username, isys_jdisc_db__discovery_password,
			isys_jdisc_db__discovery_port, isys_jdisc_db__discovery_protocol, isys_jdisc_db__discovery_timeout FROM isys_jdisc_db';

        if ($p_id !== null) {
            $l_sql .= ' WHERE isys_jdisc_db__id = ' . $this->convert_sql_id($p_id);
        } elseif ($p_id === null && $p_default) {
            $l_sql .= ' WHERE isys_jdisc_db__default_server = 1';
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Checks if the configured JDisc Server is a JEDI Version or not.
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function is_jedi_version()
    {
        try {
            $l_dao = $this->get_connection();
            $l_result_set = $l_dao->query('SELECT * FROM installationinfo');
            $l_arr = $l_dao->fetch_row_assoc($l_result_set);
            if (isset($l_arr['edition'])) {
                return (strtolower($l_arr['edition']) == 'essential') ? true : false;
            } else {
                // old version
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Checks if connection can be established
     *
     * @param null $p_id
     *
     * @return bool
     */
    public function is_connected($p_id = null)
    {
        try {
            $this->get_connection($p_id);

            return true;
        } catch (isys_exception_general $e) {
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Wrapper for deleting whole category entries for the specified object id
     *
     * @param $p_table
     * @param $p_object_id
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@synetics.de>
     */
    public function clear_category($p_table, $p_object_id, $p_has_relation = true)
    {
        $this->m_log->debug('Deleting category entries from table ' . $p_table . ' with object ID "' . $p_object_id . '".');
        $l_cmdb_dao = isys_cmdb_dao_jdisc::instance($this->m_db);

        if ($l_cmdb_dao->clear_data($p_object_id, $p_table, $p_has_relation)) {
            $this->m_log->debug('Category entries successfully deleted from table ' . $p_table . ' for object ID "' . $p_object_id . '".');

            return true;
        } else {
            $this->m_log->debug('Could not delete category entries from table ' . $p_table . ' for object ID "' . $p_object_id . '".');

            return false;
        }
    }

    /**
     * @param      $p_object_id
     * @param bool $ofTypeDhcpOnly
     */
    public function clear_ip_addresses($p_object_id, $ofTypeDhcpOnly = true)
    {
        $l_category_table = 'isys_catg_ip_list';
        $l_delete_ip_ids = '';
        $l_delete_relation_ids = [];
        // select entries of cat IP bound to the object
        $l_sql = "SELECT {$l_category_table}__isys_cats_net_ip_addresses_list__id, {$l_category_table}__isys_catg_relation_list__id 
                FROM {$l_category_table} 
                WHERE {$l_category_table}__isys_obj__id = {$p_object_id}";
        // select only IPs of type dhcp
        if ($ofTypeDhcpOnly === true) {
            $l_sql .= " AND isys_catg_ip_list__isys_ip_assignment__id = " . C__CATP__IP__ASSIGN__DHCP;
        }
        $l_res = $this->m_db->query($l_sql);

        $l_delete_ip = false;
        // fetch all ids of IPs in cats ip table and relations table
        while ($l_data = $l_res->fetch_assoc()) {
            $key = $l_category_table . '__isys_cats_net_ip_addresses_list__id';
            $relationKey = $l_category_table . '__isys_catg_relation_list__id';

            if (isset($l_data[$key]) && $l_data[$key] > 0) {
                $l_delete_ip_ids .= $l_data[$key] . ",";
                $l_delete_ip = true;
            }
            if (isset($l_data[$relationKey]) && $l_data[$relationKey] > 0) {
                $l_delete_relation_ids[] = $this->m_db->escape_string($l_data[$relationKey]);
            }
        }
        $l_res->free_result();
        // delete ips entries in cats ips table
        if ($l_delete_ip) {
            $l_delete_ip_ids = rtrim($l_delete_ip_ids, ",");
            $l_delete = "DELETE FROM isys_cats_net_ip_addresses_list WHERE isys_cats_net_ip_addresses_list__id IN (" . $l_delete_ip_ids . ")";
            if ($ofTypeDhcpOnly === true) {
                $l_delete .= " AND isys_cats_net_ip_addresses_list__isys_ip_assignment__id = " . C__CATP__IP__ASSIGN__DHCP;
            }
            $this->m_db->query($l_delete);
        }

        // select all relations, responding to catg ip list of the object
        if (count($l_delete_relation_ids)) {
            $l_dao = isys_cmdb_dao::instance($this->m_db);
            $l_res = $this->m_db->query("SELECT isys_catg_relation_list__isys_obj__id
                FROM isys_catg_relation_list
                WHERE isys_catg_relation_list__id IN (" . implode(',', $l_delete_relation_ids) . ")");

            // delete ip object, bound in the relations table
            while ($l_data = $l_res->fetch_assoc()) {
                $key = 'isys_catg_relation_list__isys_obj__id';
                if (isset($l_data[$key]) && $l_data[$key] > 0) {
                    $l_dao->delete_object($l_data[$key]);
                }
            }

            $l_res->free_result();

            $l_delete = "DELETE FROM isys_catg_relation_list WHERE isys_catg_relation_list__id IN (" . implode(',', $l_delete_relation_ids) . ")";
            $this->m_db->query($l_delete);
        }

        // delete IP category entries of the object in catg ip list table
        $l_delete = "DELETE FROM isys_catg_ip_list WHERE isys_catg_ip_list__isys_obj__id = " . $this->convert_sql_int($p_object_id);
        // delete IPs of type dhcp only
        if ($ofTypeDhcpOnly === true) {
            $l_delete .= " AND isys_catg_ip_list__isys_ip_assignment__id = " . C__CATP__IP__ASSIGN__DHCP;
        }
        $this->m_db->query($l_delete);
    }

    /**
     * Save jdisc default profile to isys_obj_type
     *
     * @param int $p_obj_type_id
     * @param int $p_jdisc_profile_id
     *
     * @return boolean
     * @throws isys_exception_dao
     */
    public function set_jdisc_default_profile($p_obj_type_id, $p_jdisc_profile_id)
    {
        $l_sql = 'UPDATE isys_obj_type SET ' . 'isys_obj_type__isys_jdisc_profile__id = ' . $this->convert_sql_id($p_jdisc_profile_id) . ' ' . 'WHERE isys_obj_type__id = ' .
            $this->convert_sql_int($p_obj_type_id);

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * Get jdisc profile id by
     *
     * @param $p_obj_type_id
     *
     * @return isys_component_dao_result
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_jdisc_default_profile($p_obj_type_id)
    {
        $l_sql = 'SELECT * FROM isys_obj_type
          INNER JOIN isys_jdisc_profile ON isys_jdisc_profile__id = isys_obj_type__isys_jdisc_profile__id
          WHERE isys_obj_type__id = ' . $this->convert_sql_id($p_obj_type_id);

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Get jdisc server by jdisc profile
     *
     * @param $p_jdisc_profile_id
     *
     * @return isys_component_dao_result
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_jdisc_server_by_profile($p_jdisc_profile_id)
    {
        $l_sql = 'SELECT isys_jdisc_db.* FROM isys_jdisc_profile
          INNER JOIN isys_jdisc_db ON isys_jdisc_db__id = isys_jdisc_profile__jdisc_server
          WHERE isys_jdisc_profile__id = ' . $this->convert_sql_id($p_jdisc_profile_id);

        return $this->retrieve($l_sql . ';');
    }

    public function get_server_id()
    {
        return $this->m_server_id;
    }

    /**
     * Method which determines if the object id is the one for the imported object
     *
     * @param       $p_object_id
     * @param array $p_checks
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function check_object_id($p_object_id, array $p_checks)
    {
        if (count($p_checks) > 0) {
            $l_check_query = 'SELECT (';
            foreach ($p_checks as $l_check_method => $l_check_value) {
                $l_query = $this->$l_check_method($p_object_id, $l_check_value, true);
                $l_check_query .= '(' . $l_query . ') +';
            }
            $l_check_query = rtrim($l_check_query, '+') . ') AS obj_check';
            $l_check_count = $this->retrieve($l_check_query)
                ->get_row_value('obj_check');

            if ((count($p_checks) > 1 && $l_check_count < 2) || (count($p_checks) === 1 && $l_check_count < 1)) {
                // Check not successful
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Method which checks if the hostname belongs to the object id
     *
     * @param $p_obj_id
     * @param $p_value
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function check_hostname($p_obj_id, $p_value, $p_as_query = false)
    {
        $l_sql = 'SELECT COUNT(isys_catg_ip_list__id) FROM isys_catg_ip_list WHERE isys_catg_ip_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) .
            ' AND isys_catg_ip_list__hostname = ' . $this->convert_sql_text($p_value);
        if ($p_as_query) {
            return $l_sql;
        } else {
            return (bool)$this->retrieve($l_sql)
                ->num_rows();
        }
    }

    /**
     * Method which checks if the serial belongs to the object id
     *
     * @param $p_obj_id
     * @param $p_value
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function check_serial($p_obj_id, $p_value, $p_as_query = false)
    {
        $l_sql = 'SELECT COUNT(isys_catg_model_list__id) FROM isys_catg_model_list WHERE isys_catg_model_list__isys_obj__id = ' . $this->convert_sql_text($p_obj_id) .
            ' AND isys_catg_model_list__serial = ' . $this->convert_sql_text($p_value);
        if ($p_as_query) {
            return $l_sql;
        } else {
            return (bool)$this->retrieve($l_sql)
                ->num_rows();
        }
    }

    /**
     * Method which checks if the fqdn belongs to the object id
     *
     * @param $p_obj_id
     * @param $p_value
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function check_fqdn($p_obj_id, $p_value, $p_as_query = false)
    {
        $l_hostname = $p_value[0];
        $l_fqdn = $p_value[1];
        $l_dns = trim(str_replace($l_hostname, '', $l_fqdn), '.');

        $l_sql = 'SELECT COUNT(main.isys_catg_ip_list__id) FROM isys_catg_ip_list AS main
                INNER JOIN isys_catg_ip_list_2_isys_net_dns_domain AS con ON main.isys_catg_ip_list__id = con.isys_catg_ip_list__id
                INNER JOIN isys_net_dns_domain AS dns ON dns.isys_net_dns_domain__id = con.isys_net_dns_domain__id
                WHERE main.isys_catg_ip_list__hostname = ' . $this->convert_sql_text($l_hostname) . ' AND dns.isys_net_dns_domain__title = ' .
            $this->convert_sql_text($l_dns) . ' AND main.isys_catg_ip_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        if ($p_as_query) {
            return $l_sql;
        } else {
            return (bool)$this->retrieve($l_sql)
                ->num_rows();
        }
    }

    /**
     * Method which checks if the mac belongs to the object id
     *
     * @param $p_obj_id
     * @param $p_value
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function check_mac($p_obj_id, $p_value, $p_as_query = false)
    {
        $l_mac_condition = "('" . implode("','", $p_value) . "')";

        $l_sql = 'SELECT COUNT(isys_catg_port_list__id) FROM isys_catg_port_list WHERE isys_catg_port_list__mac IN ' . $l_mac_condition .
            ' AND isys_catg_port_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        if ($p_as_query) {
            return $l_sql;
        } else {
            return (bool)$this->retrieve($l_sql)
                ->num_rows();
        }
    }

    /**
     * Method which checks if the object title belongs to the object id
     *
     * @param $p_obj_id
     * @param $p_value
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function check_name($p_obj_id, $p_value, $p_as_query = false)
    {
        $l_sql = 'SELECT COUNT(isys_obj__id) FROM isys_obj WHERE isys_obj__title = ' . $this->convert_sql_text($p_value) . ' AND isys_obj__id = ' .
            $this->convert_sql_text($p_obj_id);
        if ($p_as_query) {
            return $l_sql;
        } else {
            return (bool)$this->retrieve($l_sql)
                ->num_rows();
        }
    }

    /**
     * Provides information about properties.
     */
    protected function build_properties()
    {
        $language = isys_application::instance()->container->get('language');
        $l_provides_all = self::C__PROPERTY__PROVIDES__VIEW + self::C__PROPERTY__PROVIDES__CREATE + self::C__PROPERTY__PROVIDES__SAVE + self::C__PROPERTY__PROVIDES__DELETE;

        $this->m_properties = [
            self::C__CONFIGURATION           => [
                'id'                 => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__CONFIGURATION__ID')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__id',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'primary_key',
                            'unsigned',
                            'auto_increment',
                            'unique'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__CONFIGURATION__ID',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__TEXT,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bInvisible' => 1
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            [
                                'options' => ['min_range' => 1]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'title'              => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__CONFIGURATION__TITLE')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__title',
                        C__PROPERTY__DATA__TYPE  => 'varchar'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CONFIGURATION__TITLE',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'host'               => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__CONFIGURATION__HOST')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__host',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'default'                => 'localhost'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CONFIGURATION__HOST',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'port'               => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__CONFIGURATION__PORT')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__port',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'unsigned'
                        ],
                        'default'                => 25321
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CONFIGURATION__PORT',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            [
                                'options' => [
                                    'min_range' => 1,
                                    'max_range' => 65535
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'database'           => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__CONFIGURATION__DATABASE')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__database',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'default'                => 'inventory'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CONFIGURATION__DATABASE',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'username'           => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => $language->get('LC__MODULE__JDISC__CONFIGURATION__USERNAME'),
                        C__PROPERTY__INFO__DESCRIPTION => $language->get('LC__MODULE__JDISC__CONFIGURATION__USERNAME__DESCRIPTION')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__username',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'default'                => 'postgresro'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CONFIGURATION__USERNAME',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'password'           => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__CONFIGURATION__PASSWORD')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__password',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'crypt'                  => true
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CONFIGURATION__PASSWORD',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT,
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'version_check'      => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__CONFIGURATION__VERSION_CHECK')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__version_check',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => 0
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__CONFIGURATION__VERSION_CHECK',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => get_smarty_arr_YES_NO()
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => false
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'discovery_username' => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => $language->get('LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_USERNAME'),
                        C__PROPERTY__INFO__DESCRIPTION => $language->get('LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_USERNAME__DESCRIPTION')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__discovery_username',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'default'                => ''
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CONFIGURATION__DISCOVERY_USERNAME',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'discovery_password' => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_PASSWORD')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__discovery_password',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'crypt'                  => true
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CONFIGURATION__DISCOVERY_PASSWORD',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'discovery_port'     => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_PORT'),
                        C__PROPERTY__INFO__DESCRIPTION => $language->get('LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_PORT_DESCRIPTION')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__discovery_port',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'unsigned'
                        ],
                        'default'                => 9000
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CONFIGURATION__DISCOVERY_PORT',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            [
                                'options' => [
                                    'min_range' => 1,
                                    'max_range' => 65535
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'discovery_protocol' => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_PROTOCOL')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__discovery_protocol',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'default'                => 'http'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CONFIGURATION__DISCOVERY_PROTOCOL',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'discovery_timeout' => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_TIMEOUT'),
                        C__PROPERTY__INFO__DESCRIPTION => $language->get('LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_TIMEOUT_DESCRIPTION')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__discovery_timeout',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'unsigned'
                        ],
                        'default'                => '100'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CONFIGURATION__DISCOVERY_TIMEOUT',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            [
                                'options' => [
                                    'min_range' => 60,
                                    'max_range' => 9999
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'discovery_import_timeout' => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_IMPORT_RETRIES'),
                        C__PROPERTY__INFO__DESCRIPTION => $language->get('LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_IMPORT_RETRIES_DESCRIPTION')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__discovery_import_retries',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'unsigned'
                        ],
                        'default'                => '1'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CONFIGURATION__DISCOVERY_IMPORT_RETRIES',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            [
                                'options' => [
                                    'min_range' => 1,
                                    'max_range' => 9
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'default_server'     => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__CONFIGURATION__DEFAULT_SERVER')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__CONFIGURATION] . '__default_server',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => 1
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__CONFIGURATION__DEFAULT_SERVER',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => get_smarty_arr_YES_NO()
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => false
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
            ],
            self::C__PROFILES                => [
                'id'                                      => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__PROFILES__ID')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__id',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'primary_key',
                            'unsigned',
                            'auto_increment',
                            'unique'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__PROFILES__ID',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__TEXT,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bInvisible' => 1
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            [
                                'options' => ['min_range' => 1]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => self::C__PROPERTY__PROVIDES__VIEW
                ],
                'jdisc_server'                            => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__IMPORT__JDISC_SERVERS'),
                        'group'                  => self::C__COMMON_SETTINGS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__jdisc_server',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => null,
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__IMPORT__JDISC_SERVERS',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => '1',
                            'p_arData'     => new isys_callback([
                                'isys_module_jdisc',
                                'callback_get_jdisc_servers_as_array'
                            ])
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'title'                                   => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__PROFILES__TITLE'),
                        'group'                  => self::C__COMMON_SETTINGS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__title',
                        C__PROPERTY__DATA__TYPE  => 'varchar'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__PROFILES__TITLE',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'description'                             => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__PROFILES__DESCRIPTION'),
                        'group'                  => self::C__COMMON_SETTINGS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__description',
                        C__PROPERTY__DATA__TYPE  => 'text',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__PROFILES__DESCRIPTION',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__TEXTAREA,
                        C__PROPERTY__UI__PARAMS => [
                            'p_nRows' => '3',
                            'p_nCols' => '55'
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_textarea'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'categories'                              => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => $language->get('LC__MODULE__JDISC__PROFILES__CATEGORIES'),
                        C__PROPERTY__INFO__DESCRIPTION => $language->get('LC__MODULE__JDISC__PROFILES__CATEGORIES__DESCRIPTION'),
                        'group'                        => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__categories',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'default'                => $this->get_supported_categories()
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__PROFILES__CATEGORIES',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG_LIST,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bLinklist' => '1'
                        ],
                        'post'                  => 'C__MODULE__JDISC__PROFILES__CATEGORIES__selected_box',
                        'default'               => $language->get('LC__MODULE__SEARCH__ALL_CATEGORIES')
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_list_of_ids'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'import_all_software'                     => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__SOFTWARE_IMPORT__IMPORT_ALL'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__import_all_software',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__ONLY_CREATE_SOFTWARE_RELATIONS',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'import_software_licences'                => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__SOFTWARE_IMPORT__LICENCES'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__import_software_licences',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__SOFTWARE_IMPORT__LICENCES',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'import_software_services'                => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__SOFTWARE_IMPORT__SERVICES'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__import_software_services',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => 1
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__SOFTWARE_IMPORT__SERVICES',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'import_cloud_subscriptions'                => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__SOFTWARE_IMPORT__CLOUD_SUBSCRIPTIONS'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__import_cloud_subscriptions',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => 0
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__SOFTWARE_IMPORT__CLOUD_SUBSCRIPTIONS',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'import_all_networks'                     => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__NETWORK_IMPORT__IMPORT_ALL'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__import_all_networks',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__ONLY_CREATE_NETWORK_RELATIONS',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'network_adresses'                              => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => $language->get('LC__MODULE__JDISC__PROFILES__NETWORK_ADRESSES'),
                        'group'                        => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__network_adresses',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'default'                => $this->getNetworkAdressesFilter()
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__PROFILES__NETWORK_ADRESSES',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG_LIST,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bLinklist' => '1',
                            'p_arData'     => $this->getNetworkAdressesFilter()
                        ],
                        'post'                  => 'C__MODULE__JDISC__PROFILES__NETWORK_ADRESSES__selected_box',
                        'default'               => $language->get('LC__MODULE__JDISC__PROFILES__NETWORK_ADRESSES')
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'import_type_dhcp_addresses'                  => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__PROFILES__IMPORT_TYPE_DHCP_ADRESSES'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__import_type_dhcp_addresses',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => 0,
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__IMPORT_TYPE_DHCP_ADRESSES',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => '1',
                            'p_arData'     => [
                                0 => $language->get('LC__MODULE__JDISC__PROFILES__IMPORT_TYPE_DHCP_ADRESSES__NORMAL'),
                                1 => $language->get('LC__MODULE__JDISC__PROFILES__IMPORT_TYPE_DHCP_ADRESSES__DHCP_IPS_UPDATE'),
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'import_all_clusters'                     => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__CLUSTER_IMPORT__IMPORT_ALL'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__import_all_clusters',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CREATE_CLUSTER',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'import_all_blade_connections'            => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__BLADE_CONNECTIONS_IMPORT__IMPORT_ALL'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__import_all_blade_connections',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CREATE_BLADE_CONNECTIONS',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'import_custom_attributes'                => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__ADD_CUSTOM_ATTRIBUTES'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__import_custom_attributes',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__IMPORT_CUSTOM_ATTRIBUTES',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'import_all_vlans'                        => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__VLAN_IMPORT__IMPORT_ALL'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__import_all_vlans',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__CREATE_VLAN_RELATIONS',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'import_type_interfaces'                  => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('Import type interfaces'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__import_type_interfaces',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => 2,
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__CHASSIS_INTERFACE_OPTION',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => '1',
                            'p_arData'     => [
                                0 => $language->get('LC__MODULE__JDISC__PROFILES__IMPORT_TYPE_INTERFACES_CHASSIS__CATEGORY_INTERFACE'),
                                1 => $language->get('LC__MODULE__JDISC__PROFILES__IMPORT_TYPE_INTERFACES_CHASSIS__CATEGORY_CHASSIS'),
                                2 => $language->get('LC__MODULE__JDISC__PROFILES__IMPORT_TYPE_INTERFACES_CHASSIS__BOTH_CATEGORIES')
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'cmdb_status'                             => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__CMDB__TREE__SYSTEM__SETTINGS_SYSTEM__CMDB_STATUS'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__cmdb_status',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => defined_or_default('C__CMDB_STATUS__IN_OPERATION'),
                        'params'                 => [
                            'unsigned'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__CMDB_STATUS',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => '1',
                            'p_bSort'      => false,
                            'p_arData'     => new isys_callback([
                                'isys_module_jdisc',
                                'callback_get_cmdb_status_as_array'
                            ])
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'use_default_templates'                   => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__CMDB__TREE__SYSTEM__SETTINGS_SYSTEM__USE_DEFAULT_TEMPLATES'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__use_default_templates',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => null,
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__USE_DEFAULT_TEMPLATES',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'software_filter'                         => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => $language->get('LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER'),
                        C__PROPERTY__INFO__DESCRIPTION => $language->get('LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER__DESCRIPTION') .
                                                          $language->get('LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER__DESCRIPTION_REGEX')  ,
                        'group'                        => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__software_filter',
                        C__PROPERTY__DATA__TYPE  => 'text',
                        'default'                => null,
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__SOFTWARE_FILTER',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXTAREA,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'software_filter_type'                    => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER_TYPE'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__software_filter_type',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => 0
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__SOFTWARE_FILTER_TYPE',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => '1',
                            'p_bSort'      => false,
                            'p_arData'     => [
                                0 => 'LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER_TYPE__WHITELIST',
                                1 => 'LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER_TYPE__BLACKLIST'
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'software_filter_type_regexp'                    => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER_TYPE_REGEXP'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__software_filter_type_regexp',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => 0
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__SOFTWARE_FILTER_TYPE_REGEXP',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => '1',
                            'p_bSort'      => false,
                            'p_arData'     => [
                                0 => 'LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER_TYPE_REGEXP__STRING',
                                1 => 'LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER_TYPE_REGEXP__REGEXP'
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'software_obj_title'                      => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__PROFILES__SOFTWARE_OBJ_TITEL'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__software_obj_title',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => 0
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__SOFTWARE_OBJ_TITEL',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => 1,
                            'p_arData'     => get_smarty_arr_YES_NO()
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => false,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'object_matching'                         => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => $language->get('LC__MODULE__JDISC__PROFILES__OBJECT_MATCHING_PROFILE'),
                        C__PROPERTY__INFO__DESCRIPTION => $language->get('LC__MODULE__JDISC__PROFILES__OBJECT_MATCHING_DESCRIPTION'),
                        'group'                        => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD        => $this->m_tables[self::C__PROFILES] . '__isys_obj_match__id',
                        C__PROPERTY__DATA__SOURCE_TABLE => 'isys_obj_match',
                        C__PROPERTY__DATA__TYPE         => 'int'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__OBJECT_MATCHING_PROFILE',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bLinklist'  => '1',
                            'p_bSort'      => false,
                            'p_strTable'   => 'isys_obj_match',
                            'p_bDbFieldNN' => 1
                        ],
                        'post'                  => 'C__MODULE__JDISC__OBJECT_IDENTIFICATION__selected_box',
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => false,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'chassis_assigned_modules_objtype'        => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__PROFILES__CHASSIS_ASSIGNED_MODULES_OBJTYPE'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD        => $this->m_tables[self::C__PROFILES] . '__isys_obj_type__id__chassis_module',
                        C__PROPERTY__DATA__SOURCE_TABLE => 'isys_obj_type',
                        C__PROPERTY__DATA__TYPE         => 'int',
                        'default'                       => defined_or_default('C__OBJTYPE__SWITCH')
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__CHASSIS_ASSIGNED_MODULES_OBJTYPE',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bLinklist'  => '1',
                            'p_bSort'      => false,
                            'p_strTable'   => 'isys_obj_type',
                            'p_bDbFieldNN' => 1
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => false,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'chassis_assigned_modules_update_objtype' => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__PROFILES__CHASSIS_ASSIGNED_MODULES_UPDATE_OBJTYPE'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__chassis_module_update_objtype',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => 0
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__CHASSIS_ASSIGNED_MODULES_UPDATE_OBJTYPE',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => 1,
                            'p_arData'     => get_smarty_arr_YES_NO()
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => false,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'update_objtype'                          => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__PROFILES__UPDATE_OBJTYPE'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__update_objtype',
                        C__PROPERTY__DATA__TYPE  => 'int'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__UPDATE_OBJTYPE',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => 1,
                            'p_arData'     => get_smarty_arr_YES_NO()
                        ],
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => false,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'update_obj_title'                        => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__PROFILES__UPDATE_OBJ_TITLE'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__update_obj_title',
                        C__PROPERTY__DATA__TYPE  => 'int'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__UPDATE_OBJ_TITLE',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__DIALOG,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => 1,
                            'p_arData'     => get_smarty_arr_YES_NO()
                        ],
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => false,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'use_simple_database_mod'                 => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__USE_SIMPLE_DATABASE_MODEL'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__use_simple_database_mod',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__USE_SIMPLE_DATABASE_MODEL',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__CHECKBOX,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'import_connection_endpoint'                 => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__IMPORT_CONNECTION_ENDPOINT'),
                        C__PROPERTY__INFO__DESCRIPTION => $language->get('LC__MODULE__JDISC__IMPORT_CONNECTION_ENDPOINT_DESCRIPTION'),
                        'group'                  => self::C__ADDITIONAL_OPTIONS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__PROFILES] . '__import_connection_endpoint',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__CONNECTION_ENDPOINT',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__CHECKBOX,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
            ],
            self::C__OBJECT_TYPE_ASSIGNMENTS => [
                'id'                    => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__ID')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__id',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'primary_key',
                            'unsigned',
                            'auto_increment',
                            'unique'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__ID',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__TEXT,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bInvisible' => 1
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            [
                                'options' => ['min_range' => 1]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => self::C__PROPERTY__PROVIDES__VIEW
                ],
                'profile'               => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PROFILE')
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__' . $this->m_tables[self::C__PROFILES] . '__id',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'unsigned'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PROFILE',
                        C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__TEXT,
                        C__PROPERTY__UI__PARAMS => [
                            'p_bInvisible' => 1
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => true,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_VALIDATE_INT,
                            [
                                'options' => ['min_range' => 1]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'jdisc_type'            => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => $language->get('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_TYPE'),
                        C__PROPERTY__INFO__DESCRIPTION => $language->get('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_TYPE__DESCRIPTION'),
                        'group'                        => self::C__OBJECT_TYPE_ASSIGNMENTS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__jdisc_type',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'unsigned'
                        ],
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_TYPE',
                        C__PROPERTY__UI__TYPE   => 'dialog_matrix',
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => '0'
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_array_of_ints'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'jdisc_type_customized' => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => $language->get('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_TYPE_CUSTOMIZED'),
                        C__PROPERTY__INFO__DESCRIPTION => $language->get('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__CUSTOMIZED__DESCRIPTION'),
                        'group'                        => self::C__OBJECT_TYPE_ASSIGNMENTS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__jdisc_type_customized',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_TYPE_CUSTOMIZED',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'jdisc_os'              => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => $language->get('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_OS'),
                        C__PROPERTY__INFO__DESCRIPTION => $language->get('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_OS__DESCRIPTION'),
                        'group'                        => self::C__OBJECT_TYPE_ASSIGNMENTS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__jdisc_os',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'unsigned'
                        ],
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_OS',
                        C__PROPERTY__UI__TYPE   => 'dialog_matrix',
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => '0'
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_array_of_ints'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'jdisc_os_customized'   => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => $language->get('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_OS_CUSTOMIZED'),
                        C__PROPERTY__INFO__DESCRIPTION => $language->get('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__CUSTOMIZED__DESCRIPTION'),
                        'group'                        => self::C__OBJECT_TYPE_ASSIGNMENTS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__jdisc_os_customized',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_OS_CUSTOMIZED',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'title_transform'              => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => $language->get('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__TITLE_TRANSFORM'),
                        C__PROPERTY__INFO__DESCRIPTION => $language->get('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__TITLE_TRANSFORM'),
                        'group'                        => self::C__OBJECT_TYPE_ASSIGNMENTS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__title_transform',
                        C__PROPERTY__DATA__TYPE  => 'int',
                        'params'                 => [
                            'unsigned'
                        ],
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__TITLE_TRANSFORM',
                        C__PROPERTY__UI__TYPE   => 'dialog_matrix',
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => '0'
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_array_of_ints'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'fqdn_addition'   => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => $language->get('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__FQDN_ADDITION'),
                        C__PROPERTY__INFO__DESCRIPTION => $language->get('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__FQDN_ADDITION'),
                        'group'                        => self::C__OBJECT_TYPE_ASSIGNMENTS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__fqdn_addition',
                        C__PROPERTY__DATA__TYPE  => 'varchar',
                        'default'                => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__FQDN_ADDITION',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__TEXT
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_text'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'object_type'           => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => $language->get('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__OBJECT_TYPE'),
                        C__PROPERTY__INFO__DESCRIPTION => $language->get('LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__OBJECT_TYPE__DESCRIPTION'),
                        'group'                        => self::C__OBJECT_TYPE_ASSIGNMENTS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__isys_obj_type__id',
                        C__PROPERTY__DATA__TYPE       => 'int',
                        'params'                      => [
                            'unsigned'
                        ],
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_obj_type',
                            'isys_obj_type__id'
                        ],
                        'default'                     => null
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__OBJECT_TYPE',
                        C__PROPERTY__UI__TYPE   => 'dialog_matrix',
                        C__PROPERTY__UI__PARAMS => [
                            'p_bDbFieldNN' => '0'
                        ]
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY  => false,
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_array_of_ints'
                                ]
                            ]
                        ]
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'port_filter'           => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('Port Filter'),
                        'group'                  => self::C__OBJECT_TYPE_ASSIGNMENTS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__port_filter',
                        C__PROPERTY__DATA__TYPE  => 'varchar'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PORT_FILTER',
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => false,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'port_filter_type'      => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('Port Filter type'),
                        'group'                  => self::C__OBJECT_TYPE_ASSIGNMENTS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__port_filter_type',
                        C__PROPERTY__DATA__TYPE  => 'varchar'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PORT_FILTER_TYPE'
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => false,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ],
                'location'              => [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE => $language->get('Location'),
                        'group'                  => self::C__OBJECT_TYPE_ASSIGNMENTS
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => $this->m_tables[self::C__OBJECT_TYPE_ASSIGNMENTS] . '__object_location__id',
                        C__PROPERTY__DATA__TYPE       => 'int',
                        'params'                      => [
                            'unsigned'
                        ],
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_obj',
                            'isys_obj__id'
                        ],
                        'default'                     => '0'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID   => 'C__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__OBJECT_LOCATION',
                        C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__POPUP,
                    ],
                    C__PROPERTY__CHECK    => [
                        C__PROPERTY__CHECK__MANDATORY => false,
                    ],
                    C__PROPERTY__PROVIDES => $l_provides_all
                ]
            ]
        ];

        return $this;
    }

    /**
     * Fetches data from JDisc database.
     *
     * @param   string $p_query SQL query
     *
     * @return  array
     */
    protected function fetch_array($p_query)
    {
        try {
            $l_dao = $this->get_connection();
            $l_result_set = $l_dao->query($p_query);
            $l_result = [];
            while ($l_row = $l_dao->fetch_row_assoc($l_result_set)) {
                $l_result[] = $l_row;
            }

            return $l_result;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Constructor.
     *
     * @param  isys_component_database $p_db  Database component
     * @param  isys_log                $p_log Logger
     */
    public function __construct(isys_component_database $p_db, $p_log)
    {
        parent::__construct($p_db);

        $this->m_log = $p_log;
        $this->get_tables();
    }
}
