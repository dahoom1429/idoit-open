<?php

/**
 * i-doit
 *
 * Monitoring livestatus connector class.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.3.0
 */
class isys_monitoring_livestatus
{
    /**
     * The query resulsts will only be cached for a certain time - Default 10 minutes.
     *
     * @var  integer
     */
    const CACHE_TIME = 600;

    /**
     * Configuration array.
     *
     * @var  array
     */
    protected static $m_config = [];

    /**
     * Singleton instances.
     *
     * @var  array
     */
    protected static $m_instances = [];

    /**
     * The socket connection.
     *
     * @var  resource
     */
    protected $m_connection = null;

    /**
     * This variable indicates the currently used host.
     *
     * @var  integer
     */
    protected $m_host = null;

    /**
     * Static factory method.
     *
     * @static
     *
     * @param   integer $p_host
     *
     * @throws  isys_exception_general
     * @return  isys_monitoring_livestatus
     */
    public static function factory($p_host)
    {
        if (empty($p_host)) {
            throw new isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__NO_CONFIG'), 0, false);
        }

        if (isset(self::$m_instances[$p_host])) {
            return self::$m_instances[$p_host];
        }

        return self::$m_instances[$p_host] = new self($p_host);
    }

    /**
     * Destructor for disconnecting the socket.
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Method for connecting to the configured socket.
     *
     * @return  isys_monitoring_livestatus
     * @throws  isys_exception_general
     */
    public function connect()
    {
        try {
            $l_result = false;

            if (!extension_loaded("sockets")) {
                throw new isys_exception_general(isys_application::instance()->container->get('language')
                    ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__PHP_EXTENSION_MISSING'), 0);
            }

            // Create socket connection.
            if (self::$m_config[$this->m_host]['isys_monitoring_hosts__connection'] == C__MONITORING__LIVESTATUS_TYPE__UNIX) {
                $this->m_connection = @socket_create(AF_UNIX, SOCK_STREAM, 0);
            } elseif (self::$m_config[$this->m_host]['isys_monitoring_hosts__connection'] == C__MONITORING__LIVESTATUS_TYPE__TCP) {
                $this->m_connection = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            }

            if ($this->m_connection == false) {
                $this->m_connection = null;
                throw new isys_exception_general(isys_application::instance()->container->get('language')
                    ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__COULD_NOT_CREATE_SOCKET'), 0);
            }

            // Connect to the socket.
            if (self::$m_config[$this->m_host]['isys_monitoring_hosts__connection'] == C__MONITORING__LIVESTATUS_TYPE__UNIX) {
                $l_result = @socket_connect($this->m_connection, self::$m_config[$this->m_host]['isys_monitoring_hosts__path']);
            } elseif (self::$m_config[$this->m_host]['isys_monitoring_hosts__connection'] == C__MONITORING__LIVESTATUS_TYPE__TCP) {
                $l_result = @socket_connect(
                    $this->m_connection,
                    self::$m_config[$this->m_host]['isys_monitoring_hosts__address'],
                    self::$m_config[$this->m_host]['isys_monitoring_hosts__port']
                );
            }

            if ($l_result == false) {
                throw new isys_exception_general(isys_application::instance()->container->get('language')
                    ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__COULD_NOT_CONNECT_LIVESTATUS'), 0, false);
            }

            // Maybe set some socket options
            if (self::$m_config[$this->m_host]['isys_monitoring_hosts__connection'] == C__MONITORING__LIVESTATUS_TYPE__TCP) {
                // Disable Nagle's Alogrithm.
                if (defined('TCP_NODELAY')) {
                    @socket_set_option($this->m_connection, SOL_TCP, TCP_NODELAY, 1);
                } else {
                    // See http://bugs.php.net/bug.php?id=46360
                    @socket_set_option($this->m_connection, SOL_TCP, 1, 1);
                }
            }

            @socket_set_option($this->m_connection, SOL_SOCKET, SO_RCVTIMEO, ['sec' => isys_tenantsettings::get('monitoring.status.max-execution-time', 30), 'usec' => 0]);
            @socket_set_option($this->m_connection, SOL_SOCKET, SO_SNDTIMEO, ['sec' => isys_tenantsettings::get('monitoring.status.max-execution-time', 30), 'usec' => 0]);
        } catch (ErrorException $e) {
            throw new Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * Disconnect method.
     *
     * @return  isys_monitoring_livestatus
     */
    public function disconnect()
    {
        @socket_close($this->m_connection);
        $this->m_connection = null;

        return $this;
    }

    /**
     * Query method.
     *
     * @param   array   $p_query
     * @param   boolean $p_force Enabling "force" will skip the cache-data.
     *
     * @return  array
     * @throws  isys_exception_general
     */
    public function query(array $p_query, $p_force = false)
    {
        $p_query = array_filter($p_query);

        if (count($p_query) == 0) {
            return [];
        }

        // First we look inside our cache, if this specific query has been retrieved in the past...
        $l_query_hash = md5(implode(',', $p_query));
        $l_cache = isys_caching::factory('monitoring', self::CACHE_TIME);

        if (!$p_force && ($l_cached = $l_cache->get('query:' . $l_query_hash))) {
            return $l_cached;
        }

        // Query to get a json formated array back - also use "fixed16" header.
        @socket_write($this->m_connection, implode("\n", $p_query) . "\nOutputFormat:json\nResponseHeader: fixed16\n\n");

        // Read 16 bytes to get the status code and body size.
        $l_read = $this->read_socket(16);

        if ($l_read === false) {
            throw new isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__COULD_NOT_READ_FROM_SOCKET', socket_strerror(socket_last_error($this->m_connection))), false);
        }

        // Extract status code.
        $l_status = substr($l_read, 0, 3);

        // Extract content length.
        $l_length = intval(trim(substr($l_read, 4, 11)));

        // Read socket until end of data.
        $l_read = $this->read_socket($l_length);

        if ($l_read === false) {
            throw new isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__COULD_NOT_READ_FROM_SOCKET', socket_strerror(socket_last_error($this->m_connection))), false);
        }

        // Catch errors (Like HTTP 200 is OK).
        if ($l_status != "200") {
            throw new isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__COULD_NOT_READ_FROM_SOCKET', $l_read));
        }

        // Catch problems occured while reading? 104: Connection reset by peer
        if (socket_last_error($this->m_connection) == 104) {
            throw new isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__COULD_NOT_READ_FROM_SOCKET', socket_strerror(socket_last_error($this->m_connection))), false);
        }

        // Decode the json response
        $l_return = json_decode(utf8_encode($l_read));

        // json_decode returns null on syntax problems
        if ($l_return === null) {
            throw new isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__INVALID_FORMAT'), 0, false);
        }

        // Cache the current query response.
        $l_cache->set('query:' . $l_query_hash, $l_return);

        return $l_return;
    }

    /**
     * Method for reading data from the open socket.
     *
     * @param   integer $p_length
     *
     * @return  mixed  Will return a string with data, or boolean false on failure.
     */
    protected function read_socket($p_length)
    {
        $l_offset = 0;
        $l_socketData = '';

        while ($l_offset < $p_length) {
            if (($l_data = @socket_read($this->m_connection, $p_length - $l_offset)) === false) {
                return false;
            }

            $l_dataLen = strlen($l_data);
            $l_offset += $l_dataLen;
            $l_socketData .= $l_data;

            if ($l_dataLen == 0) {
                break;
            }
        }

        return $l_socketData;
    }

    /**
     * Private clone method - Singleton!
     */
    private function __clone()
    {
        ;
    }

    /**
     * Private constructor method - Singleton!
     *
     * @param  integer $p_host
     */
    private function __construct($p_host)
    {
        global $g_comp_database;

        $this->m_host = $p_host;

        self::$m_config[$this->m_host] = isys_monitoring_dao_hosts::instance($g_comp_database)
            ->get_data($this->m_host, C__MONITORING__TYPE_LIVESTATUS)
            ->get_row();

        $this->connect();
    }
}
