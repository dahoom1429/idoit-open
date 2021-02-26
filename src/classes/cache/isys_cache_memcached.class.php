<?php

/**
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cache_memcached extends isys_cache_keyvalue
{
    /**
     * @var  Memcached
     */
    protected $memcached;

    /**
     * Check wheather memcache is available or not
     *
     * @return  boolean
     */
    public static function available()
    {
        return class_exists('Memcached');
    }

    /**
     * Delete a cache key.
     *
     * @param   string $p_key
     *
     * @return  isys_cache_memcached
     */
    public function delete($p_key)
    {
        $this->prepend_ns($p_key);

        $this->memcached->delete($p_key);

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

        return (bool)$this->memcached->get($key);
    }

    /**
     * Flush cache
     *
     * @return boolean
     */
    public function flush()
    {
        $this->memcached->flush();

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

        return $this->memcached->get($p_key, null, $this->m_options['flags'] ?: null);
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
     * @return  isys_cache_memcached
     */
    public function set($p_key, $p_value = null, $p_ttl = -1)
    {
        $this->prepend_ns($p_key);

        $this->memcached->set($p_key, $p_value, $this->default_expiration($p_ttl));

        return $this;
    }

    /**
     * Adds another memcache server.
     *
     * @param  string  $host
     * @param  integer $port
     * @param  integer $weight
     */
    public function add_server($host, $port, $weight = null)
    {
        $this->memcached->addServer($host, $port, $weight);
    }

    /**
     * Destructor for closing the connection.
     */
    public function __destruct()
    {
        $this->memcached->quit();
    }

    /**
     * Construct the memcache and connect to memcache database.
     *
     * @throws isys_exception_cache
     */
    public function __construct()
    {
        if (class_exists('Memcached')) {
            $host = isys_tenantsettings::get('memcache.host', '127.0.0.1');
            $port = isys_tenantsettings::get('memcache.port', '11211');

            $this->memcached = new Memcached();

            if (!$this->memcached->addServer($host, $port)) {
                throw new isys_exception_cache('Could not connect to memcache server on ' . $host . ':' . $port, 'memcache');
            }
        } else {
            throw new isys_exception_cache('Memcache is not available. Install the php memcache extension!', 'memcache');
        }
    }
}
