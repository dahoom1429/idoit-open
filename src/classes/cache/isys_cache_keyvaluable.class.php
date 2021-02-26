<?php

/**
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.6
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface isys_cache_keyvaluable
{
    /**
     * Removes a cached item.
     *
     * @param   string $p_key
     *
     * @return  isys_cache_keyvalue
     */
    public function delete($p_key);

    /**
     * Determine whether a storage entry has been set for a key.
     *
     * @param $p_key
     *
     * @return bool
     */
    public function exists($p_key);

    /**
     * Flush cache
     *
     * @return boolean
     */
    public function flush();

    /**
     * Retrieve value from cache.
     *
     * @param   string $p_key
     *
     * @return  mixed
     */
    public function get($p_key);

    /**
     * Set a cache value.
     *
     * @param string  $p_key
     * @param mixed   $p_value
     * @param integer $p_ttl "Time To Live" in seconds.
     *
     * @return  isys_cache_keyvalue
     */
    public function set($p_key, $p_value = null, $p_ttl = -1);

    /**
     * Set options for cache handlers.
     *
     * @param array $p_options
     *
     * @return
     */
    public function set_options(array $p_options = []);

    /**
     * Check wheather the cache type is available or not.
     *
     * @return  boolean
     */
    public static function available();
}