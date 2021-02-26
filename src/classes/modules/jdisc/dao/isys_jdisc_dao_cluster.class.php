<?php

use idoit\Component\Helper\Ip;

/**
 * i-doit
 *
 * JDisc cluster DAO
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Van Quyen Hoang <qhoang@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.3
 */
class isys_jdisc_dao_cluster extends isys_jdisc_dao_data
{
    /**
     * ID Value in JDisc for cluster service status Running
     */
    const JDISC_CLUSTER_SERVICE_STATUS_RUNNING = 1;

    /**
     * ID Value in JDisc for cluster service status Disabled
     */
    const JDISC_CLUSTER_SERVICE_STATUS_DISABLED = 25;

    /**
     * This array caches the assignment between cluster and cluster services
     *
     * @var array
     */
    protected $m_cluster_assignment_cache = [];

    /**
     * This array will cache found clusters, so we can save database resources.
     *
     * @var  array
     */
    protected $m_cluster_cache = [];

    /**
     * This array will cache found clusterservices, so we can save database resources.
     *
     * @var  array
     */
    protected $m_clusterservice_cache = [];

    /**
     * This array will cache NOT found clusters, so we don't need to search for them over and over again.s
     *
     * @var  array
     */
    protected $m_missing_cluster_cache = [];

    /**
     * This array will cache NOT found clusterservices, so we don't need to search for them over and over again.s
     *
     * @var  array
     */
    protected $m_missing_clusterservice_cache = [];

    /**
     * This array caches all clusters which are virtual clusters
     *
     * @var array
     */
    protected $m_virtual_clusters = [];

    /**
     * This array defines which types are virtual clusters in i-doit see in jdisc db table clustertypelookup
     *
     * @var array
     */
    private $m_virtual_cluster_types = [
        9,
        10
    ];

    /**
     * This array defines which types are vrrp / hsrp clusters in i-doit see in jdisc db table clustertypelookup
     *
     * @var array
     */
    private $m_vrrp_cluster_types = [
        7,
        8
    ];

    /**
     * Method for counting all cluster-entries in JDisc.
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function count_cluster()
    {
        // I use this instead of "COUNT(*)" because you can't group during counting.
        return $this->m_pdo->num_rows($this->fetch('SELECT name FROM cluster GROUP BY name;'));
    }

    /**
     * Method for receiving the clusters, assigned to a given device.
     *
     * @param   integer $p_id
     * @param   boolean $p_raw
     * @param   boolean $p_all_clusters If set to true we create objects for every cluster JDisc could find.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_cluster_by_device($p_id, $p_raw = false, $p_all_clusters = false)
    {
        $l_return = [];
        $l_already_imported = [];

        /**
         * IDE typehinting helper.
         *
         * @var  $l_dao                isys_cmdb_dao_jdisc
         */
        $l_dao = isys_cmdb_dao_jdisc::instance($this->m_db);

        $l_sql = 'SELECT c.*, cs.name AS clusterservice, cs.status AS servicestatus, cs.id AS clusterserviceid FROM cluster AS c
			LEFT JOIN clusterdevicerelation AS cdr ON cdr.clusterid = c.id
			LEFT JOIN device AS d ON d.id = cdr.deviceid
			LEFT JOIN clusterservice AS cs ON d.id = cs.deviceid
			WHERE d.id = ' . $l_dao->convert_sql_id($p_id);

        $l_res = $this->fetch($l_sql);
        $this->m_log->debug('> Found ' . $this->m_pdo->num_rows($l_res) . ' cluster rows');

        // Testing case
        //$l_row['name'] = 'testing_cluster';
        //$l_row['id'] = '1';
        //$l_row['clusterservice'] = 'testing_clusterservice';
        //$l_row['clusterserviceid'] = '5';
        //if(1 == 1)
        if ($this->m_pdo->num_rows($l_res) > 0) {
            while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
                $l_cluster = $this->does_cluster_exist_in_idoit($l_row['name']);
                if ($p_raw === true) {
                    $l_return[] = $l_row;
                } else {
                    // The cluster does not exist - So we create it!
                    if (!$l_cluster) {
                        // Unset the cache entry, when we create the software.
                        unset($this->m_missing_cluster_cache[$l_row['name']]);
                        if (in_array($l_row['type'], $this->m_vrrp_cluster_types)) {
                            $l_objtype = defined_or_default('C__OBJTYPE__CLUSTER_VRRP_HSRP');
                        } else {
                            $l_objtype = defined_or_default('C__OBJTYPE__CLUSTER');
                        }
                        // @todo Check for import-mode before blindly creating new objects!
                        $l_new_obj = $l_dao->insert_new_obj(
                            $l_objtype,
                            false,
                            $l_row['name'],
                            null,
                            C__RECORD_STATUS__NORMAL,
                            null,
                            date("Y-m-d H:i:s"),
                            true,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            'By JDisc import: cluster ID #' . $l_row['id']
                        );
                        parent::set_object_id($l_new_obj);
                        // We'll search again for the cluster - By now it HAS TO BE in the system.
                        $l_cluster = $this->does_cluster_exist_in_idoit($l_row['name']);
                    }

                    if (!is_array($l_cluster)) {
                        $this->m_log->warning('The cluster "' . $l_row['name'] . '" does not exist in i-doit and was not created.');
                    } else {
                        if (in_array($l_row['type'], $this->m_virtual_cluster_types) && !in_array($l_cluster['isys_obj__id'], $this->m_virtual_clusters)) {
                            $this->m_virtual_clusters[] = $l_cluster['isys_obj__id'];
                        }
                        // we cache the object id of the cluster
                        // prefix cluster_ because it´s from the device table in jdisc
                        parent::set_jdisc_to_idoit_objects('cluster_' . $l_row['id'], $l_cluster['isys_obj__id']);
                        if (!empty($l_row['clusterservice'])) {
                            if (!isset($this->m_cluster_assignment_cache[$l_cluster['isys_obj__id']])) {
                                $this->m_cluster_assignment_cache[$l_cluster['isys_obj__id']] = [];
                            }

                            /**
                             * @var $l_clusterservice array
                             */
                            $l_clusterservice = $this->does_clusterservice_exist_in_idoit($l_row['clusterservice']);
                            if (!$l_clusterservice) {
                                // Unset the cache entry, when we create the software.
                                unset($this->m_missing_clusterservice_cache[$l_row['clusterservice']]);
                                $l_new_obj = $l_dao->insert_new_obj(
                                    defined_or_default('C__OBJTYPE__CLUSTER_SERVICE'),
                                    false,
                                    $l_row['clusterservice'],
                                    null,
                                    C__RECORD_STATUS__NORMAL,
                                    null,
                                    date("Y-m-d H:i:s"),
                                    true,
                                    null,
                                    null,
                                    null,
                                    null,
                                    null,
                                    null,
                                    null,
                                    'By JDisc import: clusterservice ID #' . $l_row['clusterserviceid']
                                );
                                parent::set_object_id($l_new_obj);
                                // If the manufacturer is not empty, we try to receive it's ID.
                                // We'll search again for the clusterservice - By now it HAS TO BE in the system.
                                $l_clusterservice = $this->does_clusterservice_exist_in_idoit($l_row['clusterservice']);
                            }

                            if (!empty($l_clusterservice)) {
                                // we cache the object id of the cluster service
                                // prefix cluster_service_ because it´s from the device table in jdisc
                                parent::set_jdisc_to_idoit_objects('cluster_service_' . $l_row['clusterserviceid'], $l_clusterservice['isys_obj__id']);


                                if (!isset($this->m_cluster_assignment_cache[$l_cluster['isys_obj__id']][$l_clusterservice['isys_obj__id']])) {
                                    $this->m_cluster_assignment_cache[$l_cluster['isys_obj__id']][$l_clusterservice['isys_obj__id']] = [];
                                }

                                // cache the assignment between cluster and clusterservice and runs on
                                // Import of the assignment happens after the object has been imported
                                if (!isset($this->m_cluster_assignment_cache[$l_cluster['isys_obj__id']][$l_clusterservice['isys_obj__id']]['servicestatus'])) {
                                    $this->m_cluster_assignment_cache[$l_cluster['isys_obj__id']][$l_clusterservice['isys_obj__id']]['servicestatus'] = $l_row['servicestatus'];
                                }

                                if (!in_array($p_id, $this->m_cluster_assignment_cache[$l_cluster['isys_obj__id']][$l_clusterservice['isys_obj__id']])) {
                                    $this->m_cluster_assignment_cache[$l_cluster['isys_obj__id']][$l_clusterservice['isys_obj__id']][] = $p_id;
                                }
                            }
                        }

                        if (!in_array($l_row['name'], $l_already_imported)) {
                            $l_return[] = $this->prepare_cluster($l_cluster);
                            $l_already_imported[] = $l_row['name'];
                        }
                    }
                }
            }
        }

        if ($p_raw === true || count($l_return) == 0) {
            return $l_return;
        } else {
            return [
                C__DATA__TITLE      => isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__CATG__CLUSTER_MEMBERSHIPS'),
                'const'             => 'C__CATG__CLUSTER_MEMBERSHIPS',
                'category_type'     => C__CMDB__CATEGORY__TYPE_GLOBAL,
                'category_entities' => $l_return
            ];
        }
    }

    /**
     * Method for finding clusters in idoit.
     *
     * @param   string $p_name The name of the software.
     *
     * @return  mixed  May be an array or boolean false.
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function does_cluster_exist_in_idoit($p_name)
    {
        if (!defined('C__CATG__CLUSTER_ROOT')) {
            return false;
        }

        if (array_key_exists($p_name, $this->m_cluster_cache)) {
            $this->m_log->debug('Cluster ' . $p_name . ' exists in cache.');

            return $this->m_cluster_cache[$p_name];
        }

        if (array_key_exists($p_name, $this->m_missing_cluster_cache)) {
            $this->m_log->debug('Cluster ' . $p_name . ' does not exist in cache.');

            return false;
        }

        $l_sql = 'SELECT isys_obj__id, isys_obj__title, isys_obj_type__const, isys_obj__sysid, isys_obj_type__title FROM isys_obj
			INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			INNER JOIN isys_obj_type_2_isysgui_catg ON isys_obj_type_2_isysgui_catg__isys_obj_type__id = isys_obj_type__id
			AND isys_obj_type_2_isysgui_catg__isysgui_catg__id = ' . $this->convert_sql_id(C__CATG__CLUSTER_ROOT) . '
			WHERE isys_obj__title LIKE ' . $this->convert_sql_text($p_name) . '
			AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        if ($l_row = $this->m_db->fetch_row_assoc($this->m_db->query($l_sql))) {
            $this->m_cluster_cache[$p_name] = $l_row;
        }

        if ($l_row === false) {
            $this->m_missing_cluster_cache[$p_name] = null;
        }

        return $l_row;
    }

    /**
     * Method for finding cluster services in idoit.
     *
     * @param $p_name
     *
     * @return array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function does_clusterservice_exist_in_idoit($p_name)
    {
        if (!defined('C__CATS__CLUSTER_SERVICE')) {
            return false;
        }

        if (array_key_exists($p_name, $this->m_clusterservice_cache)) {
            $this->m_log->debug('Cluster ' . $p_name . ' exists in cache.');

            return $this->m_clusterservice_cache[$p_name];
        }

        if (array_key_exists($p_name, $this->m_missing_clusterservice_cache)) {
            $this->m_log->debug('Cluster ' . $p_name . ' does not exist in cache.');

            return [];
        }

        $l_sql = 'SELECT isys_obj__id, isys_obj__title, isys_obj_type__const, isys_obj__sysid, isys_obj_type__title FROM isys_obj
			INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			WHERE isys_obj__title LIKE ' . $this->convert_sql_text($p_name) . '
			AND isys_obj_type__isysgui_cats__id = ' . $this->convert_sql_id(C__CATS__CLUSTER_SERVICE) . '
			AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        if ($l_row = $this->m_db->fetch_row_assoc($this->m_db->query($l_sql))) {
            $this->m_clusterservice_cache[$p_name] = $l_row;
        }

        if ($l_row === false) {
            $this->m_missing_clusterservice_cache[$p_name] = null;
        }

        return $l_row;
    }

    /**
     * Gets all cluster service assignments from the selected cluster
     *
     * @param $p_cluster
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_clusterservice_assignments($p_cluster)
    {
        $l_sql = 'SELECT isys_obj.* FROM isys_catg_cluster_service_list
			LEFT JOIN isys_connection ON isys_connection__id = isys_catg_cluster_service_list__isys_connection__id
			LEFT JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id
			WHERE isys_catg_cluster_service_list__isys_obj__id = ' . $this->convert_sql_int($p_cluster);
        $l_res = $this->retrieve($l_sql);
        $l_arr = [];
        while ($l_row = $l_res->get_row()) {
            $l_arr[$l_row['isys_obj__id']] = $l_row['isys_obj__title'];
        }

        return $l_arr;
    }

    /**
     * Method for preparing the data from JDisc to a "i-doit-understandable" format.
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function prepare_cluster(array $p_data)
    {
        //$this->m_log->debug('>> Preparing cluster array');

        // We should always have the cluster in our system by now!
        if (!empty($p_data)) {
            return [
                'data_id'    => null,
                'properties' => [
                    'connected_object' => [
                        'tag'      => 'connected_object',
                        'value'    => $p_data['isys_obj__title'],
                        'id'       => $p_data['isys_obj__id'],
                        'type'     => $p_data['isys_obj_type__const'],
                        'sysid'    => $p_data['isys_obj__sysid'],
                        'lc_title' => isys_application::instance()->container->get('language')
                            ->get($p_data['isys_obj_type__title']),
                        'title'    => $p_data['isys_obj_type__title']
                    ]
                ]
            ];
        }
    }

    /**
     * Assign cluster services to cluster and assign vrrp addresses.
     *
     * @param $p_objects
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function assign_clusters($p_objects, $p_vrrp_addresses = null)
    {
        /**
         * Typehinting
         *
         * @var $l_dao_clusterservice isys_cmdb_dao_category_g_cluster_service
         * @var $l_dao_cluster        isys_cmdb_dao_category_g_cluster_members
         * @var $l_dao_ip             isys_cmdb_dao_category_g_ip
         * @var $l_dao_guest          isys_cmdb_dao_category_g_guest_systems
         */
        $l_dao_clusterservice = isys_cmdb_dao_category_g_cluster_service::instance($this->m_db);
        $l_dao_cluster = isys_cmdb_dao_category_g_cluster_members::instance($this->m_db);
        $l_dao_ip = isys_cmdb_dao_category_g_ip::instance($this->m_db);
        $l_dao_guest = isys_cmdb_dao_category_g_guest_systems::instance($this->m_db);

        $serviceStatusMap = [
            self::JDISC_CLUSTER_SERVICE_STATUS_RUNNING  => isys_cmdb_dao_category_g_cluster_service::C__CLUSTER_SERVICE__STATUS_RUNNING,
            self::JDISC_CLUSTER_SERVICE_STATUS_DISABLED => isys_cmdb_dao_category_g_cluster_service::C__CLUSTER_SERVICE__STATUS_DISABLED
        ];

        $l_cache_cluster_service_ips = [];

        if (count($this->m_cluster_assignment_cache) > 0) {
            $this->m_log->info('');
            foreach ($this->m_cluster_assignment_cache as $l_cluster_object => $l_data_cluster) {
                if (count($l_data_cluster) > 0) {
                    $l_assigned_cluster_services = $this->get_clusterservice_assignments($l_cluster_object);
                    $l_cluster_members_res = $l_dao_cluster->get_assigned_members($l_cluster_object);
                    $l_cluster_member = [];
                    while ($l_row = $l_cluster_members_res->get_row()) {
                        $l_cluster_member[$l_row['isys_obj__id']] = $l_row['isys_catg_cluster_members_list__id'];
                    }

                    foreach ($l_data_cluster as $l_cluster_service => $l_runs_on_objects) {
                        $l_runs_on = null;
                        $l_addresses = [];
                        $serviceStatus = $serviceStatusMap[$l_runs_on_objects['servicestatus']];
                        unset($l_runs_on_objects['servicestatus']);
                        $l_runs_on_objects2 = $l_runs_on_objects;
                        if (count($l_runs_on_objects) > 0) {
                            foreach ($l_runs_on_objects as $l_key => $l_deviceid) {
                                if ($l_key === 'servicestatus') {
                                    continue;
                                }

                                $l_runs_on_objects[$l_key] = $l_cluster_member[$p_objects[$l_deviceid]];
                                $l_runs_on_objects2[$l_key] = $p_objects[$l_deviceid];

                                // IPV4
                                if (isset($p_vrrp_addresses['ipv4'][$l_deviceid]) && is_array($p_vrrp_addresses['ipv4'][$l_deviceid])) {
                                    foreach ($p_vrrp_addresses['ipv4'][$l_deviceid] as $l_ip_long => $l_data_cluster_service) {
                                        $l_ip = Ip::long2ip($l_ip_long);
                                        $l_cluster_service_key = array_search($l_cluster_service, $p_objects);
                                        $l_cluster_service_key = str_replace('cluster_service_', '', $l_cluster_service_key);
                                        if (is_array($l_data_cluster_service) && in_array($l_cluster_service_key, $l_data_cluster_service)) {
                                            // cache it
                                            if (!isset($l_cache_cluster_service_ips[$l_cluster_service_key])) {
                                                $l_res = $l_dao_ip->get_data(null, $p_objects['cluster_service_' . $l_cluster_service_key]);
                                                while ($l_row = $l_res->get_row()) {
                                                    if ($l_row['isys_catg_ip_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
                                                        $l_cache_cluster_service_ips[$l_cluster_service_key][$l_row['isys_catg_ip_list__id']] = $l_row['isys_cats_net_ip_addresses_list__ip_address_long'];
                                                    } else {
                                                        $l_cache_cluster_service_ips[$l_cluster_service_key][$l_row['isys_catg_ip_list__id']] = Ip::validate_ipv6($l_row['isys_cats_net_ip_addresses_list__title']);
                                                    }
                                                }
                                            }
                                            // check if cluster service has the ip
                                            // if not create it otherwise do nothing
                                            if ((is_array($l_cache_cluster_service_ips[$l_cluster_service_key]) &&
                                                !in_array($l_ip_long, $l_cache_cluster_service_ips[$l_cluster_service_key]))) {
                                                $l_cat_entry = $l_dao_ip->create(
                                                    $l_cluster_service,
                                                    null,
                                                    null,
                                                    $l_ip,
                                                    0,
                                                    0,
                                                    null,
                                                    null,
                                                    1,
                                                    defined_or_default('C__CATS_NET_TYPE__IPV4'),
                                                    defined_or_default('C__OBJ__NET_GLOBAL_IPV4'),
                                                    ''
                                                );
                                                $l_cache_cluster_service_ips[$l_cluster_service_key][$l_cat_entry] = $l_ip_long;
                                            }
                                            // assign ip to cluster service
                                            if (isset($l_cache_cluster_service_ips[$l_cluster_service_key])) {
                                                $l_addresses = array_keys($l_cache_cluster_service_ips[$l_cluster_service_key]);
                                            }
                                        }
                                    }
                                }

                                // IPV6
                                if (isset($p_vrrp_addresses['ipv6'][$l_deviceid]) && is_array($p_vrrp_addresses['ipv6'][$l_deviceid])) {
                                    foreach ($p_vrrp_addresses['ipv6'][$l_deviceid] as $l_ip => $l_data_cluster_service) {
                                        $l_ip = Ip::validate_ipv6($l_ip);
                                        $l_cluster_service_key = array_search($l_cluster_service, $p_objects);
                                        $l_cluster_service_key = str_replace('cluster_service_', '', $l_cluster_service_key);
                                        if (is_array($l_data_cluster_service) && in_array($l_cluster_service_key, $l_data_cluster_service)) {
                                            // cache it
                                            if (!isset($l_cache_cluster_service_ips[$l_cluster_service_key])) {
                                                $l_res = $l_dao_ip->get_data(null, $p_objects['cluster_service_' . $l_cluster_service_key]);
                                                while ($l_row = $l_res->get_row()) {
                                                    $l_cache_cluster_service_ips[$l_cluster_service_key][$l_row['isys_catg_ip_list__id']] = Ip::validate_ipv6($l_row['isys_cats_net_ip_addresses_list__title']);
                                                }
                                            }

                                            // check if cluster service has the ip
                                            // if not create it otherwise do nothing
                                            if ((is_array($l_cache_cluster_service_ips[$l_cluster_service_key]) &&
                                                !in_array($l_ip, $l_cache_cluster_service_ips[$l_cluster_service_key]))) {
                                                $l_cat_entry = $l_dao_ip->create(
                                                    $l_cluster_service,
                                                    null,
                                                    null,
                                                    $l_ip,
                                                    0,
                                                    0,
                                                    null,
                                                    null,
                                                    1,
                                                    defined_or_default('C__CATS_NET_TYPE__IPV6'),
                                                    defined_or_default('C__OBJ__NET_GLOBAL_IPV6'),
                                                    ''
                                                );
                                                $l_cache_cluster_service_ips[$l_cluster_service_key][$l_cat_entry] = $l_ip;
                                            }
                                            // assign ip to cluster service
                                            if (isset($l_cache_cluster_service_ips[$l_cluster_service_key])) {
                                                $l_addresses = array_keys($l_cache_cluster_service_ips[$l_cluster_service_key]);
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if (!isset($l_assigned_cluster_services[$l_cluster_service])) {
                            $l_dao_clusterservice->create(
                                $l_cluster_object,
                                $l_cluster_service,
                                defined_or_default('C__CLUSTER_TYPE__ACTIVE_ACTIVE'),
                                $l_runs_on_objects,
                                null,
                                '',
                                C__RECORD_STATUS__NORMAL,
                                $l_addresses,
                                null,
                                null,
                                null,
                                $serviceStatus
                            );
                        } else {
                            unset($l_assigned_cluster_services[$l_cluster_service]);
                            $l_catdata = $l_dao_clusterservice->get_data(
                                null,
                                $l_cluster_object,
                                'AND isys_connection__isys_obj__id = ' . $l_dao_clusterservice->convert_sql_id($l_cluster_service)
                            )
                                ->get_row();
                            $l_addresses_res = $l_dao_clusterservice->get_cluster_addresses($l_catdata['isys_catg_cluster_service_list__id']);
                            while ($l_row = $l_addresses_res->get_row()) {
                                $l_addresses[] = $l_row['isys_catg_ip_list__id'];
                            }
                            $l_drive_res = $l_dao_clusterservice->get_cluster_drives($l_catdata['isys_catg_cluster_service_list__id']);
                            $l_drives = [];
                            while ($l_row = $l_drive_res->get_row()) {
                                $l_drives[] = $l_row['isys_catg_drive_list__id'];
                            }
                            $l_shares_res = $l_dao_clusterservice->get_cluster_shares($l_catdata['isys_catg_cluster_service_list__id']);
                            $l_shares = [];
                            while ($l_row = $l_shares_res->get_row()) {
                                $l_shares[] = $l_row['isys_catg_shares_list__id'];
                            }

                            $dbmsData = isys_cmdb_dao_category_g_cluster_service::get_dbms($l_catdata['isys_catg_cluster_service_list__isys_catg_relation_list__id']);
                            $dbmsObjectId = null;

                            if (!empty($dbmsData)) {
                                $dbmsObjectId = $dbmsData['isys_obj__id'];
                            }

                            $l_dao_clusterservice->save(
                                $l_catdata['isys_catg_cluster_service_list__id'],
                                $l_cluster_service,
                                $l_catdata['isys_catg_cluster_service_list__isys_cluster_type__id'],
                                $l_runs_on_objects,
                                $l_catdata['isys_catg_cluster_service_list__cluster_members_list__id'],
                                '',
                                C__RECORD_STATUS__NORMAL,
                                $l_addresses,
                                $l_drives,
                                $l_shares,
                                $dbmsObjectId,
                                $serviceStatus
                            );
                        }
                    }

                    // @todo detach cluster service assignments from cluster
                    if (count($l_assigned_cluster_services) > 0) {
                    }
                }
            }
        }
    }

    /**
     * Clears all clustermemberships from the object
     *
     * @param $p_obj_id int
     * @param $entryId  int
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function clear_cluster_memberships($p_obj_id = null, $entryId = null)
    {
        if (empty($p_obj_id) && empty($entryId)) {
            return false;
        }

        $l_sql = 'SELECT isys_catg_cluster_members_list__id AS id, isys_catg_cluster_members_list__isys_catg_relation_list__id AS rel_id FROM isys_catg_cluster_members_list
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_cluster_members_list__isys_connection__id
			WHERE ';

        if ($p_obj_id) {
            $l_sql .= 'isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        } elseif ($entryId) {
            $l_sql .= 'isys_catg_cluster_members_list__id = ' . $this->convert_sql_id($entryId);
        }

        $l_res = $this->retrieve($l_sql);
        if ($l_res->num_rows() > 0) {
            /**
             * @var $l_relation_dao isys_cmdb_dao_category_g_relation
             */
            $l_relation_dao = isys_cmdb_dao_category_g_relation::factory($this->get_database_component());
            while ($l_row = $l_res->get_row()) {
                $l_relation_dao->delete_relation($l_row["rel_id"]);
                $l_delete = "DELETE FROM isys_catg_cluster_members_list WHERE isys_catg_cluster_members_list__id = " . $this->convert_sql_id($l_row['id']);
                $this->update($l_delete);
            }
            $this->apply_update();
            $l_res->free_result();

            return true;
        }

        return false;
    }

    /**
     * Removes all unassigned clustermembers from the cluster
     *
     * @param $p_objects
     *
     * @throws isys_exception_general
     */
    public function update_cluster_members(&$p_objects)
    {
        $l_dao_cluster = isys_cmdb_dao_category_g_cluster_members::instance($this->m_db);
        foreach ($p_objects as $l_key => $l_obj_id) {
            if (strpos($l_key, 'cluster_') !== false && strpos($l_key, 'cluster_service') === false) {
                $l_cluster_id = substr($l_key, strpos($l_key, '_') + 1);
                if (is_numeric($l_cluster_id) && $l_cluster_id > 0) {
                    // Get all members from i-doit
                    $l_cluster_members_res = $l_dao_cluster->get_assigned_members($l_obj_id);
                    $l_members = [];
                    while ($l_row = $l_cluster_members_res->get_row()) {
                        $l_members[trim(strtolower($l_row['isys_obj__title']))] = $l_row['isys_catg_cluster_members_list__id'];
                    }

                    // Get all members of the cluster from jdisc
                    $l_sql = 'SELECT DISTINCT TRIM(LOWER(d.name)) AS name FROM cluster AS c
					LEFT JOIN clusterdevicerelation AS cdr ON cdr.clusterid = c.id
					LEFT JOIN device AS d ON d.id = cdr.deviceid
					LEFT JOIN clusterservice AS cs ON d.id = cs.deviceid
					WHERE c.id = ' . $this->convert_sql_id($l_cluster_id);
                    $l_res = $this->fetch($l_sql);

                    while ($l_row = $this->m_pdo->fetch_row_assoc($l_res)) {
                        unset($l_members[$l_row['name']]);
                    }

                    // Remove members which are not assigned in jdisc
                    if (count($l_members) > 0) {
                        foreach ($l_members as $l_member_id) {
                            $this->clear_cluster_memberships(null, $l_member_id);
                        }
                    }
                }
                unset($p_objects[$l_key]);
            } elseif (!is_numeric($l_key)) {
                unset($p_objects[$l_key]);
            }
        }
    }
}
