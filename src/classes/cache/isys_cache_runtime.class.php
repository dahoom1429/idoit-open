<?php

/**
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.6
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cache_runtime extends isys_cache_keyvalue implements isys_cache_keyvaluable
{

    private $storage = [];

    /**
     * @return  boolean
     */
    public static function available()
    {
        return true;
    }

    /**
     *
     * @param   string $p_key
     *
     * @return  isys_cache_keyvaluable|void
     */
    public function delete($p_key)
    {
        $this->prepend_ns($p_key);

        unset($this->storage[$p_key]);

        return $this;
    }

    /**
     *
     * Always returns false
     *
     * @param   string $p_key
     *
     * @return  bool
     */
    public function exists($p_key)
    {
        return isset($this->storage[$p_key]);
    }

    /**
     * Flush cache
     *
     * @return boolean
     */
    public function flush()
    {
        $this->storage = [];

        return true;
    }

    /**
     *
     * Always returns NULL
     *
     * @param   string $p_key
     *
     * @return  mixed
     */
    public function get($p_key)
    {
        $this->prepend_ns($p_key);

        return isset($this->storage[$p_key]) ? $this->storage[$p_key] : null;
    }

    /**
     *
     * @param   string  $p_key
     * @param   mixed   $p_value
     * @param   integer $p_ttl
     *
     * @return  isys_cache_keyvalue
     */
    public function set($p_key, $p_value = null, $p_ttl = -1)
    {
        $this->prepend_ns($p_key);

        $this->storage[$p_key] = $p_value;

        return $this;
    }
}