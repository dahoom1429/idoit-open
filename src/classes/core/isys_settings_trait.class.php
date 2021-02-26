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
trait isys_settings_trait
{
    /**
     * Cache file.
     *
     * @var  string
     */
    protected static $m_cachefile = 'settings.cache';

    /**
     * Cache directory.
     *
     * @var  string
     */
    protected static $m_cache_dir = '';

    /**
     * Settings initialized?
     *
     * @var  boolean
     */
    protected static $m_initialized = false;

    /**
     * Settings storage.
     *
     * @var  array
     */
    protected static $m_settings = [];

    /**
     * To identify changes.
     *
     * @var  boolean
     */
    private static $m_changed = false;

    /**
     * @static
     * @return  array
     */
    public static function get_definition()
    {
        return self::$m_definition;
    }

    /**
     * @static
     *
     * @param  array $settings
     */
    public static function extend(array $settings = [])
    {
        self::$m_definition = array_merge_recursive(self::$m_definition, $settings);
    }

    /**
     * Check wheather settings were initialized or not
     *
     * @static
     * @return  boolean
     */
    public static function is_initialized()
    {
        return self::$m_initialized;
    }

    /**
     * Load cached settings.
     *
     * @static
     *
     * @param   string $p_cachedir
     *
     * @throws  Exception
     */
    public static function load_cache($p_cachedir)
    {
        if (file_exists($p_cachedir . self::$m_cachefile)) {
            if (is_readable($p_cachedir . self::$m_cachefile)) {
                self::$m_settings = self::decode(file_get_contents($p_cachedir . self::$m_cachefile));
            } else {
                throw new isys_exception_filesystem($p_cachedir . self::$m_cachefile . ' not readable');
            }
        } else {
            throw new isys_exception_filesystem('Error: Cache file ' . $p_cachedir . self::$m_cachefile . ' does not exist');
        }
    }

    /**
     * Set a setting value.
     *
     * @static
     *
     * @param  string $p_key
     * @param  mixed  $p_value
     */
    public static function set($p_key, $p_value)
    {
        self::$m_changed = true;

        if (!isset(self::$m_settings[$p_key])) {
            self::$m_dao->set($p_key, $p_value)
                ->apply_update();
        }

        self::$m_settings[$p_key] = $p_value;
    }

    /**
     * Remove setting.
     *
     * @static
     *
     * @param  string $p_key
     */
    public static function remove($p_key)
    {
        unset(self::$m_settings[$p_key]);

        self::$m_dao->remove($p_key);
    }

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
    public static function get($p_key = null, $p_default = '')
    {
        if ($p_key === null) {
            return self::$m_settings;
        }

        return isset(self::$m_settings[$p_key]) && self::$m_settings[$p_key] !== '' ? self::$m_settings[$p_key] : $p_default;
    }

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
    public static function has($p_key)
    {
        return isset(self::$m_settings[$p_key]);
    }

    /**
     * Override all settings.
     *
     * @param  array $p_settings
     */
    public static function override(array $p_settings)
    {
        // Overwrite settings array.
        self::$m_settings = $p_settings;

        // Write cache.
        self::cache();

        // Save to database.
        self::$m_dao->save($p_settings, false);
    }

    /**
     * Override all settings.
     */
    public static function force_save()
    {
        // Write cache.
        self::cache();

        // Save to database.
        self::$m_dao->save(self::$m_settings);
    }

    /**
     * Before destructing the usersettings we want to save the data.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function shutdown()
    {
        if (self::$m_changed) {
            self::force_save();
        }
    }

    /**
     * Method for retrieving the cache directory.
     *
     * @static
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected static function get_cache_dir()
    {
        return isys_glob_get_temp_dir() . self::$m_cache_dir;
    }

    /**
     * Writes the cache.
     *
     * @throws  Exception
     */
    protected static function cache()
    {
        try {
            // Write settings cache.
            self::write(self::get_cache_dir() . self::$m_cachefile, self::$m_settings);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @param   string $p_file
     * @param   mixed  $p_settings
     *
     * @throws  Exception
     */
    protected static function write($p_file, $p_settings)
    {
        if (!file_exists($p_file)) {
            $directory = dirname($p_file);
            if (!is_dir($directory)) {
                $isCreated = mkdir($directory, 0775, true);
                if (!$isCreated && !is_dir($directory)) {
                    throw new isys_exception_cache('Error writing settings cache directory: ' . $directory . ' could not be written.', 'Settings');
                }
            }

            if (is_writable($directory)) {
                touch($p_file);
                chmod($p_file, 0664);
            } else {
                throw new isys_exception_cache('Error writing settings cache: ' . $p_file . ' is not writeable.', 'Settings');
            }
        }

        if (is_writable($p_file)) {
            isys_file_put_contents($p_file, self::encode($p_settings));
        } else {
            throw new isys_exception_filesystem('Error writing settings cache: ' . $p_file . ' is not writeable.');
        }
    }

    /**
     * Encode settings.
     *
     * @param   mixed $p_data
     *
     * @return  string
     */
    protected static function encode($p_data)
    {
        return isys_format_json::encode($p_data);
    }

    /**
     * Decode settings.
     *
     * @param   string $p_data
     *
     * @return  mixed
     */
    protected static function decode($p_data)
    {
        return isys_format_json::decode($p_data, true);
    }
}
