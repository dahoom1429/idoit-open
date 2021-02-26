<?php

/**
 * i-doit core classes
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface isys_settings_interface
{
    /**
     * @static
     * @return  array
     */
    public static function get_definition();

    /**
     * @static
     *
     * @param  array $p_settings
     */
    public static function extend(array $p_settings = []);

    /**
     * Load cache.
     *
     * @static
     *
     * @param   isys_component_database $p_database
     *
     * @return  void
     */
    public static function initialize(isys_component_database $p_database);

    /**
     * Check wheather settings were initialized or not
     *
     * @static
     * @return  boolean
     */
    public static function is_initialized();

    /**
     * Load cached settings.
     *
     * @static
     *
     * @param   string $p_cachedir
     *
     * @throws  Exception
     */
    public static function load_cache($p_cachedir);

    /**
     * Set a setting value.
     *
     * @static
     *
     * @param  string $p_key
     * @param  mixed  $p_value
     */
    public static function set($p_key, $p_value);

    /**
     * Remove setting.
     *
     * @static
     *
     * @param  string $p_key
     */
    public static function remove($p_key);

    /**
     * Return a system setting
     *
     * @static
     *
     * @param   string $p_key     Setting identifier
     * @param   mixed  $p_default Default value
     *
     * @return  mixed
     */
    public static function get($p_key = null, $p_default = '');

    /**
     * Check if the given key exists.
     *
     * @static
     *
     * @param   string $p_key
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function has($p_key);

    /**
     * (Re)generates cache. Loads the cache into static::$m_settings.
     *
     * @throws  Exception
     * @return  array
     */
    public static function regenerate();

    /**
     * Override all settings.
     *
     * @param  array $p_settings
     */
    public static function override(array $p_settings);

    /**
     * Override all settings.
     */
    public static function force_save();
}