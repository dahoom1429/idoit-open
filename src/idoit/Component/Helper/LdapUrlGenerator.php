<?php

namespace idoit\Component\Helper;

/**
 * i-doit LdapUrlGenerator
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.11.1
 */
class LdapUrlGenerator
{
    /**
     * Encoding type constants
     */
    const LDAP_ENCODING_OFF      = 0;
    const LDAP_ENCODING_STARTTLS = 1;
    const LDAP_ENCODING_TLS      = 2;

    /**
     * LDAP Ports
     */
    const LDAP_DEFAULT_PORT = 389;
    const LDAP_TLS_PORT     = 636;

    /**
     * Host
     *
     * @var string
     */
    protected $hostName;

    /**
     * Port
     *
     * @var int
     */
    protected $port;

    /**
     * Encoding
     *
     * @var
     */
    protected $encoding = false;

    /**
     * Legacy mode for when a URL gets generated
     *
     * @var
     */
    private $legacyMode = false;

    /**
     * LdapUrlGenerator constructor.
     *
     * @param     $host
     * @param     $port
     * @param int    $encoding
     * @param bool   $legacyMode
     */
    public function __construct($host, $port, $encoding = LdapUrlGenerator::LDAP_ENCODING_OFF, $legacyMode = null)
    {
        // Initializing properties
        $this->setHostName($host)
            ->setPort($port)
            ->setEncoding($encoding)
            ->setLegacyMode((bool)$legacyMode);
    }

    /**
     * Get hostname
     *
     * @return string
     */
    public function getHostName()
    {
        return $this->hostName;
    }

    /**
     * Set hostname
     *
     * @param string $hostName
     *
     * @return LdapUrlGenerator
     */
    public function setHostName($hostName)
    {
        $this->hostName = $hostName;

        return $this;
    }

    /**
     * Get port
     *
     * @return int
     */
    public function getPort()
    {
        if (empty($this->port)) {
            switch ($this->getEncoding()) {
                default:
                case LdapUrlGenerator::LDAP_ENCODING_OFF:
                case LdapUrlGenerator::LDAP_ENCODING_STARTTLS:
                    return LdapUrlGenerator::LDAP_DEFAULT_PORT;
                case LdapUrlGenerator::LDAP_ENCODING_TLS:
                    return LdapUrlGenerator::LDAP_TLS_PORT;
            }
        }

        return $this->port;
    }

    /**
     * Set port
     *
     * @param int $port
     *
     * @return LdapUrlGenerator
     */
    public function setPort($port)
    {
        $this->port = (int)$port;

        return $this;
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
     * @param int $encoding
     *
     * @return LdapUrlGenerator
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * @return bool
     */
    public function getLegacyMode()
    {
        return $this->legacyMode;
    }

    /**
     * @param bool $legacyMode
     *
     * @return $this
     */
    public function setLegacyMode($legacyMode)
    {
        $this->legacyMode = (bool)$legacyMode;

        return $this;
    }

    /**
     * Get protocol scheme by encoding type
     *
     * @return string
     */
    private function getProtocolScheme()
    {
        switch ($this->getEncoding()) {
            case LdapUrlGenerator::LDAP_ENCODING_TLS:
                return 'ldaps://';

            default:
            case LdapUrlGenerator::LDAP_ENCODING_OFF:
            case LdapUrlGenerator::LDAP_ENCODING_STARTTLS:
                return 'ldap://';
        }
    }

    /**
     * Generate ldap connection url `protocol://hostname:port`.
     *
     * @See ID-6636 hostname:port is not a supported LDAP URI.
     * @See ID-6883 The LDAP URI can, in newer PHP versions, include the port.
     *
     * @return string
     */
    public function generate()
    {
        if ($this->legacyMode) {
            return $this->getProtocolScheme() . $this->getHostName();
        }

        return $this->getProtocolScheme() . $this->getHostName() . ':' . $this->getPort();
    }
}
