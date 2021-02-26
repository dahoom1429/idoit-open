<?php

/**
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.6
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cache_memcache extends isys_cache_keyvalue
{
    /**
     * @var  Memcache
     */
    protected $m_memcache;

    /**
     * Check wheather memcache is available or not
     *
     * @return  boolean
     */
    public static function available()
    {
        return class_exists('Memcache');
    }

    /**
     * Delete a cache key.
     *
     * @param   string $p_key
     *
     * @return  isys_cache_memcache
     */
    public function delete($p_key)
    {
        $this->prepend_ns($p_key);

        $this->m_memcache->delete($p_key);

        return $this;
    }

    /**
     * Determine whether a storage entry has been set for a key.
     *
     * @param   string $key The storage entry identifier.
     *
     * @return  boolean
     */
    public function exists($key)
    {
        $this->prepend_ns($p_key);

        return (bool)$this->m_memcache->get($key);
    }

    /**
     * Flush cache
     *
     * @return boolean
     */
    public function flush()
    {
        $this->m_memcache->flush();

        return true;
    }

    /**
     *
     * @param   string $p_key
     *
     * @return  mixed
     */
    public function get($p_key)
    {
        $this->prepend_ns($p_key);

        return $this->m_memcache->get($p_key);
    }

    /**
     * Stores an item var with key on the memcached server. Parameter expire is expiration time in seconds.
     * If it's 0, the item never expires (but memcached server doesn't guarantee this item to be stored all the time,
     * it could be deleted from the cache to make place for other items).
     * You can use MEMCACHE_COMPRESSED constant as flag value if you want to use on-the-fly compression (uses zlib).
     *
     * @param   string  $p_key
     * @param   mixed   $p_value
     * @param   integer $p_ttl [optional] Expiration time of the item. If it's equal to zero, the item will never expire. You can also use Unix timestamp or a number of seconds starting from current time, but in the latter case the number of seconds may not exceed 2592000 (30 days).
     *
     * @return  isys_cache_memcache
     */
    public function set($p_key, $p_value = null, $p_ttl = -1)
    {
        $this->prepend_ns($p_key);

        $this->m_memcache->set($p_key, $p_value, $this->m_options['flags'] ?: null, $this->default_expiration($p_ttl));

        return $this;
    }

    /**
     * Adds another memcache server.
     *
     * @param  string  $p_host
     * @param  integer $p_port
     * @param  integer $p_weight
     */
    public function add_server($p_host, $p_port, $p_weight = null)
    {
        $this->m_memcache->addServer($p_host, $p_port, true, $p_weight);
    }

    /**
     * Destructor for closing the connection.
     */
    public function __destruct()
    {
        $this->m_memcache->close();
    }

    /**
     * Construct the memcache and connect to memcache database.
     *
     * @throws isys_exception_cache
     */
    public function __construct()
    {
        if (class_exists('Memcache')) {
            $l_host = isys_tenantsettings::get('memcache.host', '127.0.0.1');
            $l_port = isys_tenantsettings::get('memcache.port', '11211');

            $this->m_memcache = new Memcache();

            if (!$this->m_memcache->addServer($l_host, $l_port, true)) {
                throw new isys_exception_cache('Could not connect to memcache server on ' . $l_host . ':' . $l_port, 'memcache');
            }
        } else {
            throw new isys_exception_cache('Memcache is not available. Install the php memcache extension!', 'memcache');
        }
    }
}
