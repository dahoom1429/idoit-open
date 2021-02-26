<?php
/**
 * i-doit
 *
 * ldapi wrapper
 *
 * @package    i-doit
 * @subpackage Libraries
 * @author     Dennis Stuecken <dstuecken@i-doit.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

use idoit\Component\Helper\LdapUrlGenerator;

include_once(__DIR__ . "/ldapi/ldapi.class.php");
include_once(__DIR__ . "/ldapi/ldapi_acc.class.php");

class isys_library_ldap extends ldapi_acc
{
    /**
     * @var  boolean
     */
    private $m_connected = false;

    /**
     * @var \Monolog\Logger
     */
    private $m_logger = null;

    /**
     * @param            $p_hostname
     * @param            $p_dn
     * @param            $p_password
     * @param int        $p_port
     * @param int        $p_protocol_version
     * @param bool|false $p_tls
     * @param int        $p_debug_level
     *
     * @return isys_library_ldap
     */
    public static function factory($p_hostname, $p_dn, $p_password, $p_port = 389, $p_protocol_version = 3, $p_tls = false, $p_debug_level = 0)
    {
        return new self($p_hostname, $p_dn, $p_password, $p_port, $p_protocol_version, $p_tls, $p_debug_level);
    }

    /**
     * @return boolean
     */
    public function connected()
    {
        return $this->m_connected;
    }

    /**
     * @param \Monolog\Logger $p_logger
     *
     * @return $this
     */
    public function set_logger(\Monolog\Logger $p_logger)
    {
        $this->m_logger = $p_logger;

        return $this;
    }

    /**
     * Single-level search through LDAP-Directory.
     * Check: http://www.php.net/manual/en/function.ldap-list.php
     *
     * @param   string  $p_dn
     * @param   string  $p_filter
     * @param   string  $p_attributes
     * @param   string  $p_attributes_only
     * @param   integer $p_sizelimit
     * @param   integer $p_timelimit
     * @param   integer $p_deref
     *
     * @return  resource
     */
    public function read($p_dn, $p_filter, $p_attributes = null, $p_attributes_only = null, $p_sizelimit = null, $p_timelimit = null, $p_deref = null)
    {
        $this->debug(" ->read: dn=" . $p_dn . " | filter=" . $p_filter);

        parent::read($p_dn, $p_filter, $p_attributes, $p_attributes_only, $p_sizelimit, $p_timelimit, $p_deref);
    }

    /**
     * @param string $p_dn
     * @param string $p_filter
     * @param array  $p_attributes
     * @param int    $p_attributes_only
     * @param null   $p_sizelimit
     * @param null   $p_timelimit
     * @param null   $p_deref
     * @param int    $p_scope
     * @param array  $serverctls
     */
    public function search(
        $p_dn,
        $p_filter,
        $p_attributes = [],
        $p_attributes_only = 0,
        $p_sizelimit = null,
        $p_timelimit = null,
        $p_deref = null,
        $p_scope = C__LDAP_SCOPE__RECURSIVE,
        $serverctls = []
    ) {
        $this->debug(' ->search: dn=' . $p_dn . ' | filter=' . $p_filter);

        return parent::search($p_dn, $p_filter, $p_attributes, $p_attributes_only, $p_sizelimit, $p_timelimit, $p_deref, $p_scope, $serverctls);
    }

    /**
     * Searches a username in specified ldap server (connection_array) and returns its DN.
     *
     * @param   string $p_username
     * @param   array  $p_connection_array
     *
     * @throws  Exception
     * @return  mixed
     */
    public function get_user($p_username, $p_connection_array)
    {
        if ($this->is_connected()) {
            /* Get and set the timelimit */
            $l_timelimit = (!empty($p_connection_array["isys_ldap__timelimit"]) ? intval($p_connection_array["isys_ldap__timelimit"]) : C__DEFAULT__TIMELIMIT);

            $this->set_option(LDAP_OPT_TIMELIMIT, $l_timelimit);

            /**
             * Setting LDAP protocol version to 3 is necessary for retrieving utf8 strings from ldap!
             *
             * @see http://www.php.net/manual/en/ref.ldap.php#108670
             */
            $this->set_option(LDAP_OPT_PROTOCOL_VERSION, 3);

            /* Search recursive or not? */
            if ($p_connection_array["isys_ldap__recursive"] > 0) {
                $l_scope = C__LDAP_SCOPE__RECURSIVE;
            } else {
                $l_scope = C__LDAP_SCOPE__SINGLE;
            }

            /* Unpack the mapping (LDAP->Directories->LDAP-Mapping) */
            $l_mapping = unserialize($p_connection_array["isys_ldap_directory__mapping"]);

            $l_map_username = $l_mapping[C__LDAP_MAPPING__USERNAME];
            if (empty($l_map_username)) {
                $l_map_username = "cn";
            }

            /* Check if filter is valid */
            if (empty($p_connection_array["isys_ldap__filter"]) || !strstr($p_connection_array["isys_ldap__filter"], "=")) {
                $l_filter = "(" . $l_map_username . "=" . $p_username . ")";
            } else {
                if ($p_connection_array['isys_ldap__filter'][0] != "(") {
                    $l_current_filter = "(" . $p_connection_array["isys_ldap__filter"] . ")";
                } else {
                    $l_current_filter = $p_connection_array["isys_ldap__filter"];
                }

                $l_filter = "(&" . $l_current_filter . "(" . $l_map_username . "=" . $p_username . "))";
            }

            $this->set_search_path($p_connection_array["isys_ldap__user_search"]);

            $this->debug("Getting user(s) using filter: " . $l_filter . " in search-path: " . $this->get_search_path());

            $l_res = $this->search($this->get_search_path(), $l_filter, $l_mapping, 0, 1, $l_timelimit, null, $l_scope);

            if ($l_res && ($l_count = $this->count($l_res)) > 0) {
                $l_entries = $this->get_entries($l_res);
                $l_ar = $this->format_entry($l_entries[0]);

                $l_attributes = [
                    'dn' => $l_entries[0]["dn"]
                ];

                foreach ($l_mapping as $l_const => $l_attr) {
                    if (is_countable($l_ar[strtolower($l_attr)]) && count($l_ar[strtolower($l_attr)]) > 0) {
                        if ($l_const == C__LDAP_MAPPING__GROUP) {
                            $l_value = $l_ar[strtolower($l_attr)];
                        } else {
                            $l_value = $l_ar[strtolower($l_attr)][count($l_ar[strtolower($l_attr)]) - 1];
                        }

                        $l_attributes[$l_const] = $l_value;
                    }
                }

                return count($l_attributes) > 0 ? $l_attributes : false;
            } else {
                $this->debug("** No user found.");
            }
        } else {
            throw new Exception("Not connected to ldap server.");
        }

        return false;
    }

    /**
     * @param  $p_message
     */
    private function debug($p_message)
    {
        if ($this->m_logger) {
            $this->m_logger->debug($p_message);
        }

        return $this;
    }

    /**
     * Verifies if paged result is supported by the connected ldap server
     *
     * @return bool
     */
    public function isPagedResultSupported()
    {
        return ldap_control_paged_result($this->get_connection(), 0); // phpcs:ignore
    }

    /**
     * Set ldap control page result
     *
     * @param int       $pageSize
     * @param bool      $isCritical
     * @param string    $pagedResultCookie Pointer of the current search
     */
    public function ldapControlPagedResult($pageSize, $isCritical, &$pagedResultCookie)
    {
        ldap_control_paged_result($this->get_connection(), $pageSize, $isCritical, $pagedResultCookie); // phpcs:ignore
    }

    /**
     * Set ldap control page response
     *
     * @param $ldapSearchResource
     * @param $pagedResultCookie
     */
    public function ldapControlPagedResultResponse($ldapSearchResource, &$pagedResultCookie)
    {
        ldap_control_paged_result_response($this->get_connection(), $ldapSearchResource, $pagedResultCookie); // phpcs:ignore
    }

    /**
     * $p_protool_version parameter is deprecated!
     *
     * @param   string  $p_hostname
     * @param   string  $p_dn
     * @param   string  $p_password
     * @param   integer $p_port
     * @param   integer $p_protocol_version
     * @param   boolean $p_tls
     * @param   integer $p_debug_level
     *
     * @throws  Exception
     */
    public function __construct($p_hostname, $p_dn, $p_password, $p_port = 389, $p_protocol_version = 3, $p_tls = LdapUrlGenerator::LDAP_ENCODING_OFF, $p_debug_level = 0)
    {
        parent::__construct($p_hostname);
        $this->set_port($p_port);

        // Set encoding of ldap connection
        $this->setEncoding($p_tls);

        if (($this->m_connected = $this->connect())) {
            $this->set_option(LDAP_OPT_DEBUG_LEVEL, $p_debug_level);

            /**
             * Windows 2003 fix for searching the whole DC
             *
             * @see http://www.php.net/manual/de/function.ldap-search.php#45388
             */
            $this->set_option(LDAP_OPT_REFERRALS, 0);

            /**
             * Setting LDAP protocol version to 3 is necessary for retrieving utf8 strings from ldap!
             *
             * @see http://www.php.net/manual/en/ref.ldap.php#108670
             */
            $this->set_option(LDAP_OPT_PROTOCOL_VERSION, 3);

            // Check for encoding type is equal STARTTLS
            if ($p_tls == LdapUrlGenerator::LDAP_ENCODING_STARTTLS) {
                if (!$this->start_tls()) {
                    throw new Exception("Failed to start TLS (" . $this->get_ldap_error() .
                        "). Check https://php.net/ldap-start-tls for help configuring secure ldap connections. " . "Host: " . $p_hostname . ":" . $p_port . ". User: " . $p_dn .
                        ", TLS: On");
                }
            }

            if (!($this->m_connected = $this->bind($p_dn, $p_password))) {
                $this->debug("Connection failed. (" . $this->get_ldap_error() . ")");
                throw new Exception("LDAP Bind failed (" . $this->get_ldap_error() . "). " . "Host: " . $p_hostname . ":" . $p_port . ". User: " . $p_dn);
            }

            $this->debug("Connected to {$p_hostname}");
        } else {
            throw new Exception("LDAP Connection failed (" . $this->get_ldap_error() . ")");
        }
    }

    /**
     * @param $l_search
     * @param $errcode
     * @param $matcheddn
     * @param $errmsg
     * @param $referrals
     * @param $serverControls
     */
    public function ldapParseResult($l_search, &$errcode, &$matcheddn, &$errmsg, &$referrals, &$serverControls)
    {
        ldap_parse_result($this->get_connection(), $l_search, $errcode, $matcheddn, $errmsg, $referrals, $serverControls); // phpcs:ignore
    }
}
