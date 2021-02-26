<?php

use idoit\Module\Cmdb\Model\Matcher\Ci\CiMatcher;
use idoit\Module\Cmdb\Model\Matcher\MatchConfig;
use idoit\Module\Cmdb\Model\Matcher\Identifier\ObjectTitle;
use idoit\Module\Cmdb\Model\Matcher\Identifier\Fqdn;
use idoit\Module\Cmdb\Model\Matcher\Identifier\Hostname;
use idoit\Module\Cmdb\Model\Matcher\Identifier\IpAddress;
use idoit\Module\Cmdb\Model\Matcher\Identifier\Mac;
use idoit\Module\Cmdb\Model\Matcher\Identifier\ModelSerial;
use idoit\Module\Cmdb\Model\Matcher\Ci\MatchKeyword;

/**
 * Class isys_jdisc_dao_matching
 *
 * @author   Van Quyen Hoang <qhoang@i-doit.com>
 */
class isys_jdisc_dao_matching
{
    /**
     * @var isys_jdisc_dao_data
     */
    protected $m_dao;

    /**
     * @var
     */
    private static $m_instance;

    /**
     * @var int
     */
    private $m_server_id = null;

    /**
     * @var CiMatcher[]
     */
    private $m_ciMatcher;

    /**
     * @var isys_cache
     */
    private $m_cache;

    /**
     * Retrieves object id by device id
     *
     * @param $p_device_id
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_object_id_by_device_id($p_device_id, $p_group_name = null)
    {
        if ($this->m_cache->exists('deviceid-' . $this->m_server_id . '-' . $p_device_id) && $p_group_name === null) {
            return $this->m_cache->get('deviceid-' . $this->m_server_id . '-' . $p_device_id);
        } else {
            $l_obj_id = $this->find_object_id($p_device_id, 'deviceid-' . $this->m_server_id, $this->build_device_keywords($p_device_id), $p_group_name);
            $this->m_cache->set('deviceid-' . $this->m_server_id . '-' . $p_device_id, $l_obj_id);

            return $l_obj_id;
        }
    }

    /**
     * Retrieves object id by module id
     *
     * @param $p_module_id
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_object_id_by_module_id($p_module_id)
    {
        if ($this->m_cache->exists('moduleid-' . $this->m_server_id . '-' . $p_module_id)) {
            return $this->m_cache->get('moduleid-' . $this->m_server_id . '-' . $p_module_id);
        } else {
            $l_obj_id = $this->find_object_id('module-' . $p_module_id, 'moduleid-' . $this->m_server_id, $this->build_module_keywords($p_module_id));
            $this->m_cache->set('moduleid-' . $this->m_server_id . '-' . $p_module_id, $l_obj_id);

            return $l_obj_id;
        }
    }

    /**
     * Search object by device id or object matcher
     *
     * @param int                                                $p_device_id
     * @param \idoit\Module\Cmdb\Model\Matcher\Ci\MatchKeyword[] $p_keywords
     * @param int                                                $p_bits
     * @param int                                                $p_min_match
     *
     * @return bool|int
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function find_object_id($p_id = null, $p_identifier_key = null, array $p_keywords = [], $p_group_name = null)
    {
        $l_objectID = 0;
        // First get object id by identifier
        if ($p_id != '' && $p_identifier_key !== null) {
            // First check in cache
            if ($p_group_name === null) {
                $l_objectID = isys_cmdb_dao_category_g_identifier::get_object_id_by_identifer($p_id);

                if ($l_objectID) {
                    return $l_objectID;
                }
            }

            $l_objectID = isys_cmdb_dao_category_g_identifier::instance(isys_application::instance()->database)
                ->get_object_id_by_key_value(
                    isys_cmdb_dao_category_g_identifier::get_identifier_type(),
                    isys_cmdb_dao_category_g_identifier::get_identifier_key(),
                    $p_id,
                    $p_group_name
                );
        }

        // Second get object id by keywords
        if (!$l_objectID && count($p_keywords)) {
            $l_match = $this->m_ciMatcher->match($p_keywords);

            if ($l_match->getMatchCount() > 1) {
                $l_found_matches = $l_match->getMatchResult();
                $l_objectID = $l_match->getId();

                if (strpos($p_identifier_key, 'moduleid-')) {
                    $this->m_dao->get_log()
                        ->info('Module id "' . str_replace('module-', '', $p_id) . '" found. Using object id ' . $l_objectID . '. Found ' . $l_match->getMatchCount() .
                            ' or more objects.');
                } else {
                    $this->m_dao->get_log()
                        ->info('Device id "' . $p_id . '" found. Using object id ' . $l_objectID . '. Found ' . $l_match->getMatchCount() . ' or more objects.');
                }

                $this->m_dao->get_log()
                    ->debug('Please check the following Objects:');
                // Output which objects were found
                foreach ($l_found_matches as $l_key => $l_found_match) {
                    $this->m_dao->get_log()
                        ->debug('- Object ' . $l_found_match->getTitle() . ' (ID: ' . $l_found_match->getID() . ')');
                }
            } else {
                $l_objectID = $l_match->getId();
            }

            if ($l_objectID > 0) {
                isys_cmdb_dao_category_g_identifier::set_missing_identifiers($l_objectID, (($p_group_name !== null) ? $p_id . '-' . $p_group_name : $p_id));
            }
        }

        if ($l_objectID) {
            isys_cmdb_dao_category_g_identifier::set_object_id_by_identifier($l_objectID, (($p_group_name !== null) ? $p_id . '-' . $p_group_name : $p_id));
        }

        return ($l_objectID > 0) ? $l_objectID : false;
    }

    /**
     * Build Matchkeywords for devices
     *
     * @param int $p_device_id
     *
     * @return MatchKeyword[]
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function build_device_keywords($p_device_id)
    {
        $l_device_info = $this->m_dao->get_device_info($p_device_id);
        $l_keywords = [];
        $l_keywords[] = new MatchKeyword(ObjectTitle::KEY, $l_device_info['name']);
        $l_keywords[] = new MatchKeyword(ModelSerial::KEY, $l_device_info['serialnumber']);
        $l_keywords[] = new MatchKeyword(Hostname::KEY, $l_device_info['hostname']);
        $l_keywords[] = new MatchKeyword(IpAddress::KEY, $l_device_info['address']);
        $l_keywords[] = new MatchKeyword(Fqdn::KEY, $l_device_info['fqdn']);

        $l_unique_mac_addresses = $this->m_dao->get_mac_addresses($p_device_id, true);
        if (count($l_unique_mac_addresses)) {
            $l_keywords[] = new MatchKeyword(Mac::KEY, current($l_unique_mac_addresses));
        }

        return $l_keywords;
    }

    /**
     * Build Matchkeywords for modules
     *
     * @param int $p_module_id
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function build_module_keywords($p_module_id)
    {
        $l_module_info = $this->m_dao->get_modules_info(null, $p_module_id);
        $l_keywords = [];
        if (count($l_module_info)) {
            $l_module = current($l_module_info);

            $l_serial = trim($l_module['serial']);
            $l_raw_title = trim($l_module['title']);
            $l_title = $l_raw_title . (($l_serial != '') ? ' - ' . $l_serial : '');

            $l_keywords[] = new MatchKeyword(ModelSerial::KEY, $l_serial);
            $l_keywords[] = new MatchKeyword(ObjectTitle::KEY, $l_title);
        }

        return $l_keywords;
    }

    /**
     * Checks if the device exists in jdisc with i-doit data
     *
     * @param $p_filter_data
     *
     * @return bool
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function match_jdisc_device($p_filter_data)
    {
        if (isset(isys_application::instance()->container['jdisc_pdo'])) {
            // We only need all possible keywords
            $l_keywords[] = new \idoit\Module\Cmdb\Model\Matcher\Ci\MatchKeyword(\idoit\Module\Cmdb\Model\Matcher\Identifier\ObjectTitle::KEY, null);
            $l_keywords[] = new \idoit\Module\Cmdb\Model\Matcher\Ci\MatchKeyword(\idoit\Module\Cmdb\Model\Matcher\Identifier\ModelSerial::KEY, null);
            $l_keywords[] = new \idoit\Module\Cmdb\Model\Matcher\Ci\MatchKeyword(\idoit\Module\Cmdb\Model\Matcher\Identifier\Hostname::KEY, null);
            $l_keywords[] = new \idoit\Module\Cmdb\Model\Matcher\Ci\MatchKeyword(\idoit\Module\Cmdb\Model\Matcher\Identifier\IpAddress::KEY, null);
            $l_keywords[] = new \idoit\Module\Cmdb\Model\Matcher\Ci\MatchKeyword(\idoit\Module\Cmdb\Model\Matcher\Identifier\Fqdn::KEY, null);
            $l_keywords[] = new \idoit\Module\Cmdb\Model\Matcher\Ci\MatchKeyword(\idoit\Module\Cmdb\Model\Matcher\Identifier\Mac::KEY, null);

            $l_matchConfig = \idoit\Module\Cmdb\Model\Matcher\MatchConfig::factory($p_filter_data['match_profile'], isys_application::instance()->container);

            $l_minMatch = $l_matchConfig->getMinMatch();

            $l_data = (new \idoit\Module\Cmdb\Model\Matcher\Ci\CiDataRetriever($l_matchConfig))->dataRetrieve($p_filter_data[C__CMDB__GET__OBJECT], $l_keywords)
                ->getDataResult();

            if (count($l_data)) {
                $l_query = 'SELECT matchblock.id, Count(matchblock.id) AS matchings FROM (';
                $l_union = [];

                // Building Union select
                foreach ($l_data as $l_key => $l_value) {
                    if ($l_key != '' && method_exists($this, $l_key)) {
                        $l_union_part = $this->$l_key($l_value);

                        if ($l_union_part) {
                            $l_union[] = $l_union_part;
                        }
                    }
                }

                if (count($l_union)) {
                    $l_query .= implode(' UNION ', $l_union) . ') AS matchblock GROUP BY matchblock.id ORDER BY matchings DESC limit 1';

                    $l_result = $this->m_dao->fetch($l_query);

                    if ($l_result) {
                        $l_match = isys_application::instance()->container['jdisc_pdo']->fetch_row_assoc($l_result);
                        if ($l_match['matchings'] >= $l_minMatch) {
                            // We have a match add the device id as condition
                            isys_jdisc_dao_devices::instance(isys_application::instance()->database)
                                ->set_device_filter_condition(' AND d.id = ' . $this->m_dao->convert_sql_id($l_match['id']) . ' ');

                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check for FQDN in jdisc
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function fqdn($p_value)
    {
        if ($p_value) {
            return '(SELECT d.id, \'fqdn\' AS identifier FROM device AS d
                LEFT JOIN ip4transport AS ip4 ON ip4.deviceid = d.id
                LEFT JOIN ip6transport AS ip6 ON ip6.deviceid = d.id
                WHERE ip4.fqdn = ' . $this->m_dao->convert_sql_text($p_value) . ' OR
                    ip6.fqdn = ' . $this->m_dao->convert_sql_text($p_value) . ')';
        }
    }

    /**
     * Check for hostname in jdisc
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function hostname($p_value)
    {
        if ($p_value) {
            return '(SELECT d.id, \'hostname\' AS identifier FROM device AS d
                LEFT JOIN ip4transport AS ip4 ON ip4.deviceid = d.id
                LEFT JOIN ip6transport AS ip6 ON ip6.deviceid = d.id
                WHERE ip4.fqdn = ' . $this->m_dao->convert_sql_text($p_value) . ' OR
                    ip6.fqdn = ' . $this->m_dao->convert_sql_text($p_value) . ')';
        }
    }

    /**
     * Check for IP addresses in jdisc
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function ipAddress($p_value)
    {
        if ($p_value) {
            $l_ip_list = explode(',', $p_value);
            $l_condition = '(';
            foreach ($l_ip_list as $l_ip) {
                if (\idoit\Component\Helper\Ip::validate_ipv6($l_ip)) {
                    $l_condition .= ' ip6.address = ' . $this->m_dao->convert_sql_text($l_ip) . ' OR';
                } else {
                    $l_condition .= ' ip4.address = ' . $this->m_dao->convert_sql_int(\idoit\Component\Helper\Ip::ip2long($l_ip)) . ' OR';
                }
            }

            $l_condition = rtrim($l_condition, 'OR') . ') ';

            if ($p_value) {
                return '(SELECT d.id, \'ipAddress\' AS identifier FROM device AS d
                    LEFT JOIN ip4transport AS ip4 ON ip4.deviceid = d.id
                    LEFT JOIN ip6transport AS ip6 ON ip6.deviceid = d.id
                    WHERE ' . $l_condition . ')';
            }
        }
    }

    /**
     * Check for mac addresses in jdisc
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function mac($p_value)
    {
        if ($p_value) {
            $l_mac_list = explode(',', $p_value, 5);
            // Remove the last element we only want to check the first 4 mac addresses
            unset($l_mac_list[4]);

            return '(SELECT deviceid AS id, \'mac\' AS identifier FROM mac
                WHERE ifphysaddress = ANY(ARRAY[' . implode(', ', array_map(function ($mac) {
                return $this->m_dao->convert_sql_text($mac);
            }, array_unique($l_mac_list))) .
                ']))';
        }
    }

    /**
     * Check for device name in jdisc
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function objectTitle($p_value)
    {
        if ($p_value) {
            return '(SELECT id, \'objectTitle\' AS identifier FROM device WHERE name = ' . $this->m_dao->convert_sql_text($p_value) . ')';
        }
    }

    /**
     * Check for device serialnumber in jdisc
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function modelSerial($p_value)
    {
        if ($p_value) {
            return '(SELECT id, \'modelSerial\' AS identifier FROM device WHERE serialnumber = ' . $this->m_dao->convert_sql_text($p_value) . ')';
        }
    }

    /**
     * Factory method build singleton
     *
     * @param int                 $p_config_id isys_obj_match__id
     * @param int                 $p_server_id
     * @param isys_jdisc_dao_data $p_jdisc_dao
     *
     * @return isys_jdisc_dao_matching
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function initialize($p_config_id, $p_server_id, isys_jdisc_dao_data $p_jdisc_dao)
    {
        if (!self::$m_instance) {
            self::$m_instance = new self($p_server_id, new CiMatcher(MatchConfig::factory($p_config_id, isys_application::instance()->container)), $p_jdisc_dao);
        }

        return self::$m_instance;
    }

    /**
     * Clears identifier
     *
     * @param int          $p_type
     * @param string       $p_key
     * @param string       $p_device_id
     * @param string|array $p_group_name
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function clear_identifiers($p_type = null, $p_key = null, $p_device_id = null, $p_group_name = null)
    {
        if (is_array($p_group_name)) {
            foreach ($p_group_name as $l_group_part) {
                isys_cmdb_dao_category_g_identifier::instance(isys_application::instance()->database)
                    ->clear_identifiers($p_type, $p_key, $p_device_id, $l_group_part);
            }
        } else {
            isys_cmdb_dao_category_g_identifier::instance(isys_application::instance()->database)
                ->clear_identifiers($p_type, $p_key, $p_device_id, $p_group_name);
        }
    }

    /**
     * @return isys_jdisc_dao_matching
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function instance()
    {
        return self::$m_instance;
    }

    /**
     * @param      $p_id
     * @param null $p_group
     *
     * @return bool|mixed
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_object_id_by_identifier($p_id, $p_group = null)
    {
        if ($p_group) {
            if (is_array($p_group)) {
                foreach ($p_group as $l_group) {
                    if (($l_return = isys_cmdb_dao_category_g_identifier::instance(isys_application::instance()->database)
                        ->get_object_id_by_identifer($p_id . '-' . $l_group))) {
                        return $l_return;
                    }
                }

                return false;
            } else {
                return isys_cmdb_dao_category_g_identifier::instance(isys_application::instance()->database)
                    ->get_object_id_by_identifer($p_id . '-' . $p_group);
            }
        } else {
            return isys_cmdb_dao_category_g_identifier::instance(isys_application::instance()->database)
                ->get_object_id_by_identifer($p_id);
        }
    }

    /**
     * isys_jdisc_dao_matching constructor.
     *
     * @param int                 $p_server_id
     * @param CiMatcher           $p_matcher
     * @param isys_jdisc_dao_data $p_jdisc_dao
     */
    public function __construct($p_server_id, CiMatcher $p_matcher, isys_jdisc_dao_data $p_jdisc_dao)
    {
        $this->m_ciMatcher = $p_matcher;
        $this->m_server_id = $p_server_id;
        $this->m_dao = $p_jdisc_dao;
        $this->m_cache = isys_cache::keyvalue();
        $this->m_cache->ns('jdisc');
    }

    /**
     * Destruct
     */
    public function __destruct()
    {
        // Flush cache
        $this->m_cache->flush();
    }
}
