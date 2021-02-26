<?php

/**
 * i-doit
 *
 * PHP-SNMP
 *
 * @package     i-doit
 * @subpackage  Libraries
 * @author      Dennis Stuecken <dstuecken@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

if (!function_exists("snmpget")) {
    throw new Exception("PHP-SNMP Module not installed or activated (https://php.net/snmp). SNMP queries are currently not possible.");
}

class isys_library_snmp
{
    /**
     * Community.
     *
     * @var string
     */
    private $m_community = "";

    /**
     * Hostname.
     *
     * @var string
     */
    private $m_hostname = "";

    /**
     * Set Hostname.
     *
     * @param   string $p_hostname
     *
     * @return  isys_library_snmp
     */
    public function set_hostname($p_hostname)
    {
        $this->m_hostname = $p_hostname;

        return $this;
    }

    /**
     * Set community.
     *
     * @param   string $p_community
     *
     * @return  isys_library_snmp
     */
    public function set_community($p_community)
    {
        $this->m_community = $p_community;

        return $this;
    }

    /**
     * SNMPGET.
     *
     * @param   string $p_object_id
     *
     * @return  string
     */
    public function __get($p_object_id)
    {
        return $this->get_new($this->m_hostname, $this->m_community, $p_object_id);
    }

    /**
     * SNMPGET wrapper
     *
     * @param   string  $p_hostname
     * @param   string  $p_community
     * @param   string  $p_object_id
     * @param   integer $p_timeout
     * @param   integer $p_retries
     *
     * @return  string
     */
    public function get_new($p_hostname, $p_community, $p_object_id, $p_timeout = 1000000, $p_retries = 5)
    {
        try {
            if (!empty($p_hostname)) {
                return snmpget($p_hostname, $p_community, $p_object_id, $p_timeout, $p_retries);
            }
        } catch (Exception $e) {
            isys_notify::error($e->getMessage());
        }

        return false;
    }

    /**
     * SNMPGET.
     *
     * @param   string $p_object_id
     *
     * @return  string
     */
    public function get($p_object_id)
    {
        return $this->get_new($this->m_hostname, $this->m_community, $p_object_id);
    }

    /**
     * SNMP Walk mapper.
     *
     * @param   string  $p_hostname
     * @param   string  $p_community
     * @param   string  $p_object_id
     * @param   integer $p_timeout
     * @param   integer $p_retries
     *
     * @return  string
     */
    public function walk_new($p_hostname, $p_community, $p_object_id, $p_timeout = null, $p_retries = null)
    {
        try {
            return snmpwalk($p_hostname, $p_community, $p_object_id, $p_timeout, $p_retries);
        } catch (Exception $e) {
            isys_notify::error($e->getMessage());
        }

        return '';
    }

    /**
     * Generic toString() method.
     *
     * @return  string
     */
    public function __toString()
    {
        if ($this->m_hostname && $this->m_community) {
            return implode(', ', snmpwalk($this->m_hostname, $this->m_community, null));
        }

        return "";
    }

    /**
     * SNMP Walk.
     *
     * @param   string $p_object_id
     *
     * @return  string
     */
    public function walk($p_object_id = null)
    {
        return snmpwalk($this->m_hostname, $this->m_community, $p_object_id);
    }

    /**
     * Clean the given string.
     *
     * @param   string $p_string
     *
     * @return  string
     */
    public function cleanup($p_string)
    {
        return str_replace([
            "Gauge32: ",
            "\"",
            "STRING: "
        ], "", $p_string);
    }

    /**
     * Constructor.
     *
     * @param  string $p_hostname
     * @param  string $p_community
     */
    public function __construct($p_hostname = null, $p_community = null)
    {
        $this->m_hostname = $p_hostname;
        $this->m_community = $p_community;
    }
}
