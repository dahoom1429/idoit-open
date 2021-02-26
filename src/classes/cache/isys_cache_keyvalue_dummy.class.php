<?php

/**
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.6
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cache_keyvalue_dummy extends isys_cache_keyvalue implements isys_cache_keyvaluable
{

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
        return false;
    }

    /**
     * Flush cache
     *
     * @return boolean
     */
    public function flush()
    {
        return true;
    }

    /**
     *
     * Always returns NULL
     *
     * @param   string $p_key
     *
     * @return  null
     */
    public function get($p_key)
    {
        return null;
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
        return $this;
    }
}