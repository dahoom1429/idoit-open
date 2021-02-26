<?php
/**
 * LDAP Library Wrapper
 *
 * Please also check the php documentation:
 * http://www.php.net/manual/en/ref.ldap.php
 *
 * @author    Dennis Stücken <dstuecken@synetics.de>
 * @copyright Dennis Stücken <dstuecken@synetics.de>
 * @version   1.0 - 29.10.2007
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 *
 */

use idoit\Component\Helper\LdapUrlGenerator;

define("C__LDAP_SCOPE__SINGLE", 0);
define("C__LDAP_SCOPE__RECURSIVE", 1);
define("C__LDAP_ENTRIES__FIRST", 0);

/**
 * LDAPI is a Wrapper for the PHP extension: php_ldap
 *
 */
class ldapi
{
    const DIR__ACTIVE_DIRECTORY          = 1;
    const DIR__NOVELL_DIRECTORY_SERVICES = 2;
    const DIR__OTHER                     = 0;
    const PHP_VERSION_LDAP_PAGING_DEPRECATION = 70400;

    /**
     * Holds the current instance
     *
     * @var ldapi
     */
    private static $m_instance;

    /**
     * @var
     */
    private $m_connection;

    /**
     * @var
     */
    private $m_hostname;

    /**
     * @var string
     */
    private $m_last_error = "";

    /**
     * @var
     */
    private $m_port;

    /**
     * Connection encoding type
     *
     * @see LdapUrlGenerator
     * @var int
     */
    private $encoding;

    /**
     * Get singleton instance
     *
     * @param string $p_hostname
     *
     * @return ldapi
     */
    public static function get_instance($p_hostname = null, $p_port = 389)
    {
        if (!is_object(self::$m_instance)) {
            self::$m_instance = new ldapi($p_hostname);
        }

        self::$m_instance->set_hostname($p_hostname);
        self::$m_instance->set_port($p_port);

        return self::$m_instance;
    }

    /**
     * @return mixed
     */
    public function get_connection()
    {
        return $this->m_connection;
    }

    /**
     * @param $p_hostname
     */
    public function set_hostname($p_hostname)
    {
        if (!empty($p_hostname)) {
            $this->m_hostname = $p_hostname;
        }
    }

    /**
     * @return mixed
     */
    public function get_hostname()
    {
        return $this->m_hostname;
    }

    /**
     * @param $p_port
     */
    public function set_port($p_port)
    {
        $this->m_port = $p_port;
    }

    /**
     * @return mixed
     */
    public function get_port()
    {
        return $this->m_port;
    }

    /**
     * Checks if the LDAP-Library is connected to a server
     *
     * @return boolean
     */
    public function is_connected()
    {
        return is_resource($this->m_connection);
    }

    /**
     * Get last LDAP-Error
     *
     * @return string
     */
    public function get_ldap_error()
    {
        if (is_resource($this->m_connection)) {
            return ldap_error($this->m_connection);
        }

        return '';
    }

    /**
     * Starts TLS
     *
     * @return boolean
     */
    public function start_tls()
    {
        try {
            if ($this->is_connected()) {
                return ldap_start_tls($this->m_connection);
            }

            return false;
        } catch (ErrorException $e) {
            throw new isys_exception_ldap($e->getMessage());
        }
    }

    /**
     * Get last LDAPI-Error
     *
     * @return string
     */
    public function get_last_error()
    {
        return $this->m_last_error;
    }

    /**
     * Escapes a string. (RFC2254)
     *
     * @return string
     */
    public function escape_string($p_str)
    {
        $l_chars = [
            '\\',
            '(',
            ')',
            '#',
            '*'
        ];
        $l_meta = [];

        foreach ($l_chars as $l_key => $l_val) {
            $l_meta[$l_key] = '\\' . dechex(ord($l_val));
        }

        return str_replace($l_chars, $l_meta, $p_str);
    }

    /**
     * Set last error message
     *
     * @param string $p_errmsg
     */
    public function error($p_errmsg)
    {
        $this->m_last_error = $p_errmsg;
    }

    /**
     * Deletes an entry of the LDAP-Directory
     *
     * @param string $p_dn
     */
    public function delete($p_dn)
    {
        return ldap_delete($this->m_connection, $p_dn);
    }

    /**
     * DN to user-friendly naming format
     *
     * @param string $p_dn
     *
     * @return string
     */
    public function dn2string($p_dn)
    {
        return ldap_dn2ufn($p_dn);
    }

    /**
     * Explodes comma separated attributes to a conform array
     *
     * @param string $p_attributes
     *
     * @return array
     */
    public function explode_attributes($p_attributes)
    {
        $l_attributes = preg_replace("/[\s]/si", "", $p_attributes);

        return explode(",", $l_attributes);
    }

    /**
     * Explode DN to array
     *
     * @param string $p_dn
     * @param string $p_with_attribute
     *
     * @return array
     */
    public function explode_dn($p_dn, $p_with_attribute)
    {
        return ldap_explode_dn($p_dn, $p_with_attribute);
    }

    /**
     * Count LDAP-Result
     *
     * @param resource $p_result
     *
     * @return int
     */
    public function count($p_result)
    {
        try {
            if (is_resource($p_result)) {
                return ldap_count_entries($this->m_connection, $p_result);
            }
        } catch (ErrorException $e) {
            throw new isys_exception_ldap($e->getMessage());
        }

        return 0;
    }

    /**
     * Get attributes from a search result entry
     *
     * @param resource $p_result
     */
    public function get_attributes($p_result)
    {
        try {
            if ($this->is_connected() && is_resource($p_result)) {
                return ldap_get_attributes($this->m_connection, $p_result);
            }
        } catch (ErrorException $e) {
            throw new isys_exception_ldap($e->getMessage());
        }

        return null;
    }

    /**
     * Get all values from a result entry
     *
     * @param resource $p_result_entry
     * @param string   $p_attribute
     *
     * @return array
     */
    public function get_values($p_result_entry, $p_attribute)
    {
        try {
            if ($this->is_connected()) {
                return ldap_get_values($this->m_connection, $p_result_entry, $p_attribute);
            }
        } catch (ErrorException $e) {
            throw new isys_exception_ldap($e->getMessage());
        }
    }

    /**
     * Get the DN of a result entry
     *
     * @param resource $p_result
     *
     * @return string
     */
    public function get_dn($p_result)
    {
        return ldap_get_dn($this->m_connection, $p_result);
    }

    /**
     * Set the value of various session-wide parameters
     *
     * @param string $p_option
     * @param string $p_neval
     */
    public function set_option($p_option, $p_neval)
    {
        return ldap_set_option($this->m_connection, $p_option, $p_neval);
    }

    /**
     * Get the current value for given option,
     * Sets p_return to the value of the specified option.
     *
     * @param string $p_option
     * @param mixed  $p_return
     *
     * @return boolean
     */
    public function get_option($p_option, &$p_return)
    {
        return ldap_get_option($this->m_connection, $p_option, $p_return);
    }

    /**
     * Reads a DN from ldap-directory
     *
     * http://de.php.net/manual/de/function.ldap-read.php
     *
     * @param string $p_dn
     * @param string $p_filter
     * @param string $p_attributes
     * @param string $p_attributes_only
     * @param int    $p_sizelimit
     * @param int    $p_timelimit
     * @param int    $p_deref
     *
     * @return resource
     */
    public function read($p_dn, $p_filter, $p_attributes = null, $p_attributes_only = null, $p_sizelimit = null, $p_timelimit = null, $p_deref = null)
    {
        try {
            return ldap_read($this->m_connection, $p_dn, $p_filter, $p_attributes, $p_attributes_only, $p_sizelimit, $p_timelimit, $p_deref);
        } catch (ErrorException $e) {
            throw new isys_exception_ldap($e->getMessage());
        }
    }

    /**
     * Single-level search through LDAP-Directory
     *
     * Check:
     * http://www.php.net/manual/en/function.ldap-list.php
     *
     * @param string $p_dn
     * @param string $p_filter
     * @param string $p_attributes
     * @param string $p_attributes_only
     * @param int    $p_sizelimit
     * @param int    $p_timelimit
     * @param int    $p_deref
     *
     * @return resource
     */
    public function get_list($p_dn, $p_filter, $p_attributes = null, $p_attributes_only = null, $p_sizelimit = null, $p_timelimit = null, $p_deref = null)
    {
        try {
            if (!is_null($p_attributes) && !is_array($p_attributes)) {
                $p_attributes = [];
            }

            return ldap_list($this->m_connection, $p_dn, $p_filter, $p_attributes, $p_attributes_only, $p_sizelimit, $p_timelimit, $p_deref);
        } catch (ErrorException $e) {
            throw new isys_exception_ldap($e->getMessage());
        }
    }

    /**
     * Frees the ldap result
     *
     * @param $p_resource
     *
     * @return boolean
     */
    public function free_result($p_resource)
    {
        if (is_resource($p_resource)) {
            return ldap_free_result($p_resource);
        } else {
            return false;
        }
    }

    /**
     * Determine if a specific value is set for given dn
     *
     * @param string $p_dn
     * @param string $p_attribute
     * @param string $p_value
     */
    public function compare($p_dn, $p_attribute, $p_value)
    {
        ldap_compare($this->m_connection, $p_dn, $p_attribute, $p_value);
    }

    /**
     * Get result entries
     *
     * @param resource $p_resource
     *
     * @return array
     */
    public function get_entries($p_resource)
    {
        try {
            if (is_resource($p_resource)) {
                return ldap_get_entries($this->m_connection, $p_resource);
            } else {
                return [];
            }
        } catch (ErrorException $e) {
            throw new isys_exception_ldap($e->getMessage());
        }
    }

    /**
     * Add attribute values to current attributes
     *
     * http://www.php.net/manual/en/function.ldap-mod-add.php
     * mod_add($connect,"CN=MyGroup,OU=Groups,DC=example,DC=com",array("member" => $dn));
     *
     * @param string $p_dn
     * @param array  $p_entry
     *
     * @return boolean
     */
    public function mod_add($p_dn, $p_entry)
    {
        try {
            return @ldap_mod_add($this->m_connection, $p_dn, $p_entry);
        } catch (ErrorException $e) {
            throw new isys_exception_ldap($e->getMessage());
        }
    }

    /**
     * Replace attribute values with new ones
     *
     * @param string $p_dn
     * @param array  $p_entry
     *
     * @return boolean
     */
    public function mod_replace($p_dn, $p_entry)
    {
        try {
            return @ldap_mod_replace($this->m_connection, $p_dn, $p_entry);
        } catch (ErrorException $e) {
            throw new isys_exception_ldap($e->getMessage());
        }
    }

    /**
     * Delete attribute values from current attributes
     *
     * @param string $p_dn
     * @param array  $p_entry
     *
     * @return boolean
     */
    public function mod_del($p_dn, $p_entry)
    {
        try {
            return @ldap_mod_del($this->m_connection, $p_dn, $p_entry);
        } catch (ErrorException $e) {
            throw new isys_exception_ldap($e->getMessage());
        }
    }

    /**
     * Modify an LDAP entry
     *
     * $p_attrib["objectclass"][0] = "device";
     * $p_attrib["macAddress"][0] = "aa:bb:cc:dd:ee:ff";
     *
     * modify ($p_dn, "cn=myNetCard,ou=Networks,dc=example,dc=com", $p_attrib)
     *
     * @param string $p_dn
     * @param array  $p_entry
     *
     * @return boolean
     */
    public function modify($p_dn, $p_entry)
    {
        try {
            return ldap_modify($this->m_connection, $p_dn, $p_entry);
        } catch (ErrorException $e) {
            throw new isys_exception_ldap($e->getMessage());
        }
    }

    /**
     * Searchs the given (dn) tree
     *
     * $p_attributes :
     *   An array of the required attributes, e.g. array("mail", "sn", "cn").
     *   Note that the "dn" is always returned irrespective of which attributes types are requested.
     *
     *   Using this parameter is much more efficient than the default action
     *   (which is to return all attributes and their associated values).
     *
     *  $p_attributes_only:
     *    Should be set to 1 if only attribute types are wanted.
     *    If set to 0 both attributes types and attribute values are fetched which is the default behaviour.
     *
     * @param string $p_dn
     * @param string $p_filter
     * @param array  $p_attributes
     * @param int    $p_attributes_only
     * @param int    $p_sizelimit
     * @param int    $p_timelimit
     * @param int    $p_deref
     * @param int    $p_scope (C__LDAP_SCOPE__SINGLE|C__LDAP_SCOPE__RECURSIVE)
     * @param array  $serverctls
     *
     * @return resource
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
        try {
            if (!is_null($p_attributes) && !is_array($p_attributes)) {
                $p_attributes = [];
            }

            switch ($p_scope) {
                case C__LDAP_SCOPE__SINGLE:
                    return $this->get_list($p_dn, $p_filter, $p_attributes, $p_attributes_only, $p_sizelimit, $p_timelimit, $p_deref);
                    break;
                default:
                case C__LDAP_SCOPE__RECURSIVE:
                    if (PHP_VERSION_ID < self::PHP_VERSION_LDAP_PAGING_DEPRECATION) {
                        return @ldap_search($this->m_connection, $p_dn, $p_filter, $p_attributes, $p_attributes_only, $p_sizelimit, $p_timelimit, $p_deref);
                    } else {
                        return @ldap_search($this->m_connection, $p_dn, $p_filter, $p_attributes, $p_attributes_only, $p_sizelimit, $p_timelimit, $p_deref, $serverctls); // phpcs:ignore
                    }
                    break;
            }
        } catch (ErrorException $e) {
            throw new isys_exception_ldap($e->getMessage());
        }
    }

    /**
     * Sort LDAP result entries
     *
     * @param resource $p_result
     * @param string   $p_sort_filter
     *
     * @return bool
     */
    /*
    public function sort(&$p_result, $p_sort_filter)
    {
        try {
            return ldap_sort($this->m_connection, $p_result, $p_sort_filter);
        } catch (ErrorException $e) {
            throw new isys_exception_ldap($e->getMessage());
        }
    }
    */

    /**
     * Connect to LDAP-Server
     *
     * @param string $p_hostname
     * @param int    $p_port
     *
     * @return bool
     * @throws isys_exception_ldap
     */
    public function connect($p_hostname = null, $p_port = null)
    {
        try {
            if (empty($this->m_hostname)) {
                $this->set_hostname($p_hostname);
            }

            if (empty($this->m_port)) {
                $this->set_port($p_port);
            }

            // @see ID-6636, ID-6883 Enable a "legacy" mode for certain PHP versions.
            $legacyMode = PHP_VERSION_ID < 70000;

            // Create ldap link creator helper
            $ldapLinkCreator = new LdapUrlGenerator(
                $this->get_hostname(),
                $this->get_port(),
                $this->getEncoding(),
                $legacyMode
            );


            // Connect to ldap
            // @See ID-6636, ID-6883 hostname:port is not a supported LDAP URI in general so we have to add the port as parameter
            if ($legacyMode) {
                $this->m_connection = ldap_connect($ldapLinkCreator->generate(), $ldapLinkCreator->getPort());
            } else {
                $this->m_connection = ldap_connect($ldapLinkCreator->generate());
            }

            ldap_set_option($this->m_connection, LDAP_OPT_NETWORK_TIMEOUT, 2);

            if (is_resource($this->m_connection)) {
                return true;
            }

            $this->error("Unable to connect to LDAP-Server: " . $this->m_hostname . " (" . ldap_error($this->m_connection) . ")");
        } catch (ErrorException $e) {
            throw new isys_exception_ldap($e->getMessage());
        }

        return false;
    }

    /**
     * Bind to LDAP-Directory
     *
     * @param string $p_dn
     * @param string $p_password
     *
     * @return boolean
     */
    public function bind($p_dn = null, $p_password)
    {
        try {
            if (is_resource($this->m_connection)) {
                return @ldap_bind($this->m_connection, $p_dn, $p_password);
            } else {
                $this->error("Could not bind. Check connection.");

                return false;
            }
        } catch (ErrorException $e) {
            throw new isys_exception_ldap($e->getMessage());
        }
    }

    /**
     * Disconnects from LDAP-Server
     *
     * @return boolean
     */

    public function disconnect()
    {
        return $this->unbind();
    }

    /**
     * Unbind from LDAP directory
     *
     */
    public function unbind()
    {
        if (is_resource($this->m_connection)) {
            return ldap_unbind($this->m_connection);
        } else {
            return false;
        }
    }

    /**
     * Closes the LDAP-Session
     *
     * @return boolean
     */
    public function close()
    {
        if (is_resource($this->m_connection)) {
            ldap_close($this->m_connection);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns a hashed password
     *
     * @param string $p_password
     *
     * @return string
     */
    public function get_password_hash($p_password)
    {
        return "{MD5}" . base64_encode(pack("H*", md5($p_password)));
    }

    /**
     * Add entries to LDAP-Directory
     *
     * entry
     *   An array that specifies the information about the entry.
     *   The values in the entries are indexed by individual attributes.
     *   In case of multiple values for an attribute, they are indexed using
     *   integers starting with 0.
     *
     * $p_data["cn"] = "John Jones";
     * $p_data["sn"] = "Jones";
     * $p_data["mail"] = "jonj@example.com";
     * $p_data["attribut2"][0] = "value1";
     * $p_data["attribut2"][1] = "value2";
     *
     * @param string $p_dn
     * @param array  $p_data
     *
     * @return boolean
     */
    public function add($p_dn, array $p_data)
    {
        try {
            if (is_resource($this->m_connection)) {
                return @ldap_add($this->m_connection, $p_dn, $p_data);
            } else {
                $this->error("Could not add. Check connection.");

                return false;
            }
        } catch (ErrorException $e) {
            throw new isys_exception_ldap($e->getMessage());
        }
    }

    /**
     * Re-Formats an entry
     *
     * @param string $p_entry
     */
    public function format_entry($p_entry)
    {
        $l_formatted = [];

        if (is_array($p_entry)) {
            foreach ($p_entry as $l_key => $l_value) {
                if (is_array($l_value)) {
                    if (!is_numeric($l_key)) {
                        if (array_key_exists("count", $l_value)) {
                            unset($l_value["count"]);
                        }

                        $l_formatted[$l_key] = $l_value;
                    }
                }
            }
        }

        return $l_formatted;
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->unbind();
        $this->close();
    }

    /**
     * Get encoding
     *
     * @return int
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Set encoding
     *
     * @param int $encoding
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * @param null $p_hostname
     *
     * @throws Exception
     */
    public function __construct($p_hostname = null)
    {
        if (!extension_loaded("ldap")) {
            throw new isys_exception_ldap('Error: LDAP Extension not loaded.');
        }

        $this->set_hostname($p_hostname);
    }
}
