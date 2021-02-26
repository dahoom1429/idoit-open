<?php
/**
 * A simple caching implementation.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @since       0.9.9-7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

/**
 * This class will cache data as associative arrays in a file.
 *
 *     // Creating and setting new cache-data.
 *     $l_cache = isys_caching::factory('data-xyz')
 *         ->set_expiration(3600)
 *         ->set('data', 123)
 *         ->set('data2', array(1, 2, 3))
 *         ->set('data3', new Object())
 *         ->save();
 *
 *     // Getting cache-data.
 *     $l_cache = isys_caching::factory('data-xyz');
 *     $l_data = $l_cache->get('data');
 *     $l_data2 = $l_cache->get('data2');
 *     $l_data3 = $l_cache->get('data3');
 *
 * @deprecated Please use "isys_cache::keyvalue()"
 * @author     Leonard Fischer <lfischer@i-doit.org>
 */
class isys_caching
{
    // Constant for cache-filename prefix.
    const C__CACHE__PREFIX = 'cache__';

    // Constant for cache-file extension.
    const C__CACHE__EXTENSION = 'php';

    /**
     * Here we will save all our data.
     *
     * @var  array
     */
    protected static $m_data = [];

    /**
     * Array with the instance-names.
     *
     * @var  array
     */
    protected static $m_instance = [];

    /**
     * Has the cache tried to be loaded.
     *
     * @var  boolean[]
     */
    protected static $m_loaded = [];

    /**
     * Has the cache been updated/modified?
     *
     * @var  boolean[]
     */
    protected static $m_updated = [];

    /**
     * The cache-directory, so we don't have to use globals all the time.
     *
     * @var  string
     */
    protected $m_cachedir;

    /**
     * The complete dir + filename of the cache-file.
     *
     * @var  string
     */
    protected $m_cachefile;

    /**
     * The name for this cache-instance.
     *
     * @var  string
     */
    protected $m_cachename;

    /**
     * The default expiration is one week (as defined by isys_convert).
     *
     * @var  integer
     */
    protected $m_expiration = 604800;

    /**
     * Factory method for instant method-chaining. Requires a name for the cache.
     * If a cache with the same name already exists, it will be loaded.
     *
     * @param   string  $p_name       The name of this cache-instance.
     * @param   integer $p_expiration The expiration time of the cache.
     *
     * @return  isys_caching
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function factory($p_name = null, $p_expiration = null)
    {
        if (isset(self::$m_instance[$p_name])) {
            return self::$m_instance[$p_name];
        }

        return self::$m_instance[$p_name] = new isys_caching($p_name, $p_expiration);
    }

    /**
     * This static "find" method will return an array of isys_caching instances, whose names match the "$p_name" parameter.
     *
     * @param   string $p_name It is possible to use "*" als wildchar (using "glob()" function).
     *
     * @return  isys_caching[]
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function find($p_name)
    {
        $l_found = [];

        try {
            $session = isys_application::instance()->container->get('session');
        } catch (Exception $e) {
            $session = false;
        }

        $l_cachedir = isys_glob_get_temp_dir();

        // If the session-object exists, we try to receive the mandator-cache directory.
        if (is_a($session, 'isys_component_session')) {
            $l_mandator_data = $session->get_mandator_data();

            $l_cachedir .= $l_mandator_data['isys_mandator__dir_cache'] . DS;
        }

        $l_matches = glob($l_cachedir . self::C__CACHE__PREFIX . $p_name);

        if (!empty($l_matches)) {
            foreach ($l_matches as $l_match) {
                $l_cache_name = strstr(str_replace($l_cachedir . self::C__CACHE__PREFIX, '', $l_match), '.' . self::C__CACHE__EXTENSION, true);

                $l_found[$l_cache_name] = self::factory($l_cache_name);
            }
        }

        return $l_found;
    }

    /**
     * Destructor method for saving cache, when the http request ends.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function __destruct()
    {
        try {
            $this->save();
        } catch (Exception $e) {
            // Does not matter, due to deprecation of this class.
        }
    }

    /**
     * Magic getter.
     *
     * @param   string $p_key The key of the data, which should be returned.
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @uses    isys_caching::get()
     */
    public function __get($p_key)
    {
        return $this->get($p_key);
    }

    /**
     * Magic setter.
     *
     * @param   string $p_key   The key for the cached data.
     * @param   mixed  $p_value The cached data itself. Can contain a String, Boolean, Array or Object.
     *
     * @return  isys_caching
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @uses    isys_caching::add()
     */
    public function __set($p_key, $p_value)
    {
        return $this->set($p_key, $p_value);
    }

    /**
     * Magic isset method.
     *
     * @param   string $p_key The key of the data, which should be checked.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function __isset($p_key)
    {
        return $this->has($p_key);
    }

    /**
     * Sets a new values to the cache. This can be a string, integer, array or object.
     *
     * @deprecated  Simply use "set" instead.
     *
     * @param   string $p_key   The key for the cached data.
     * @param   mixed  $p_value The cached data itself. Can contain a String, Boolean, Array or Object.
     *
     * @return  isys_caching
     * @author      Leonard Fischer <lfischer@i-doit.org>
     */
    public function add($p_key, $p_value)
    {
        return $this->set($p_key, $p_value);
    }

    /**
     * Delete method for deleting the current cache-content and file.
     *
     * @return  isys_caching
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function clear()
    {
        try {
            unset(self::$m_data[$this->m_cachename]);

            if (file_exists($this->m_cachefile) && is_writable($this->m_cachefile)) {
                // Does not matter, due to deprecation of this class.
                unlink($this->m_cachefile);
            }
        } catch (ErrorException $e) {
            // Does not matter, due to deprecation of this class.
        }

        return $this;
    }

    /**
     * This delete method deletes all cache-files.
     *
     *     // Delete every cache-file and data.
     *     isys_caching::factory()->delete_all();
     *
     * @return  isys_caching
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @uses    isys_caching::delete_all_except()
     */
    public function delete_all()
    {
        return $this->delete_all_except([]);
    }

    /**
     * This delete method deletes all cache-files, except the ones given as parameter (array).
     *
     *     // Deleting every cache file and data except autoload-cache.
     *     isys_caching::factory()->delete_all_except(array('autoload'));
     *
     * @param   array $p_except Give an array of cache-files you don't want to delete.
     *
     * @return  isys_caching
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function delete_all_except(array $p_except)
    {
        if ($l_handle = opendir($this->m_cachedir)) {
            while (($l_file = readdir($l_handle)) !== false) {
                $l_prefix_lenght = strlen(self::C__CACHE__PREFIX);
                $l_cachename = (string)substr($l_file, $l_prefix_lenght, -4);

                // Only delete cache files!
                if (strpos($l_file, self::C__CACHE__PREFIX) === 0 && !in_array($l_cachename, $p_except, true) && !is_dir($this->m_cachedir . $l_file)) {
                    if (is_writable($this->m_cachedir . $l_file)) {
                        unlink($this->m_cachedir . $l_file);
                    }

                    unset(self::$m_data[$l_cachename]);
                }
            }

            closedir($l_handle);
        }

        return $this;
    }

    /**
     * Get one or all values from the cache.
     *
     * @param   string $p_key     The key, of the cache-data to get.
     * @param   mixed  $p_default This will be returned as default.
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get($p_key = null, $p_default = false)
    {
        if (!isset(self::$m_data[$this->m_cachename][$p_key]) && self::$m_loaded[$this->m_cachename] !== true) {
            $this->load();
        }

        if ($p_key === null) {
            return self::$m_data[$this->m_cachename];
        }

        if (is_string($p_key) && isset(self::$m_data[$this->m_cachename][$p_key])) {
            return self::$m_data[$this->m_cachename][$p_key];
        }

        return $p_default;
    }

    /**
     * Magic isset method.
     *
     * @param   string $p_key The key of the data, which should be checked.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function has($p_key)
    {
        return isset(self::$m_data[$this->m_cachename][$p_key]);
    }

    /**
     * This method will load the cache file, if it exists.
     *
     * @return  isys_caching
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @uses    isys_caching::clear()
     */
    public function load()
    {
        // If a cache-file already exists, load it.
        if (file_exists($this->m_cachefile) && (filemtime($this->m_cachefile) > (time() - $this->m_expiration))) {
            self::$m_data[$this->m_cachename] = unserialize(file_get_contents($this->m_cachefile));
            self::$m_loaded[$this->m_cachename] = true;
            self::$m_updated[$this->m_cachename] = false;
        } else {
            // Cache is expired or corrupted.
            $this->clear();
        }

        return $this;
    }

    /**
     * This method saves the cache to a file on the filesystem.
     *
     * @param   boolean $p_force Shall the cache file be forced to be written?
     *
     * @return  isys_caching|bool
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @throws  Exception
     */
    public function save($p_force = false)
    {
        // Was the cache updated or is this action beeing forced?
        if ($p_force || (isset(self::$m_updated[$this->m_cachename]) && self::$m_updated[$this->m_cachename])) {
            // Check if the cache-file has been set.
            if ($this->m_cachefile === null) {
                $this->set_paths();
            }

            // Check, if the cache-directory exists and create it, if necessary.
            if (!is_dir($this->m_cachedir)) {
                if (is_writable(dirname($this->m_cachedir))) {
                    // Create the cache directory with full rights for user+group and read/access for others
                    $isCreated = mkdir($this->m_cachedir, 0775, true);
                    if (!$isCreated && !is_dir($this->m_cachedir)) {
                        throw new isys_exception_filesystem('Could not create "' . $this->m_cachedir . '", ' . dirname($this->m_cachedir) . '!');
                    }

                    // Set permissions (must be manually set to fix umask issues).
                    chmod($this->m_cachedir, 0775);
                } else {
                    throw new isys_exception_filesystem('Could not create "' . $this->m_cachedir . '", ' . dirname($this->m_cachedir) . ' is not writeable!');
                }
            }

            // Check if the directory is available.
            if (!is_dir($this->m_cachedir) || !is_writable($this->m_cachedir)) {
                throw new isys_exception_filesystem('The cache-directory, located at "' . $this->m_cachedir . '", must be writeable!');
            }

            // Open the cache-file and check, if this action was successful.
            if ($p_force || !file_exists($this->m_cachefile)) {
                if (is_writable(dirname($this->m_cachefile))) {
                    if (!$l_cachefile = fopen($this->m_cachefile, 'w')) {
                        throw new isys_exception_filesystem('The cache-file, located at "' . $this->m_cachefile . '", can not be opened!');
                    }

                    try {
                        // Start writing the content.
                        if (!fwrite($l_cachefile, serialize(self::$m_data[$this->m_cachename]))) {
                            return false;
                        }

                        // Close the file.
                        fclose($l_cachefile);

                        // Set the file permission to allow user+group to read/write, others - read
                        if (file_exists($this->m_cachefile) && is_writable($this->m_cachefile)) {
                            chmod($this->m_cachefile, 0664);
                        }
                    } catch (Exception $e) {
                    }
                } else {
                    throw new isys_exception_filesystem('The cache-file, located at "' . $this->m_cachefile . '" is not writeable!');
                }
            }
        }

        return $this;
    }

    /**
     * Sets a new values to the cache. This can be a string, integer, array or object.
     *
     * @param   string $p_key   The key for the cached data.
     * @param   mixed  $p_value The cached data itself. Can contain a String, Boolean, Array or Object.
     *
     * @return  isys_caching
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function set($p_key, $p_value)
    {
        self::$m_updated[$this->m_cachename] = true;
        self::$m_data[$this->m_cachename][$p_key] = $p_value;

        return $this;
    }

    /**
     * This method defines the expiration date - Must be called, before loading cached values.
     *
     * @param   integer $p_expiration The expiration time in seconds.
     *
     * @return  isys_caching
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function set_expiration($p_expiration)
    {
        $this->m_expiration = (int)$p_expiration;

        return $this;
    }

    /**
     * This method sets the paths for the cache.
     *
     * @param   string $p_name Define a name for the cache-files.
     *
     * @return  isys_caching
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function set_paths($p_name = null)
    {
        if ($p_name === null) {
            $p_name = 'default';
        }

        // Only set cache-name, if it was not set before.
        if ($this->m_cachename === null) {
            $this->m_cachename = self::C__CACHE__PREFIX . isys_glob_strip_accent(isys_glob_replace_accent(strtolower($p_name)));
        }

        try {
            $session = isys_application::instance()->container->get('session');
        } catch (Exception $e) {
            $session = false;
        }

        $this->m_cachedir = isys_glob_get_temp_dir();

        // If the session-object exists, we try to receive the mandator-cache directory.
        if (is_a($session, 'isys_component_session')) {
            $l_mandator_data = $session->get_mandator_data();

            $this->m_cachedir .= $l_mandator_data['isys_mandator__dir_cache'] . DS;
        }

        $this->m_cachefile = $this->m_cachedir . $this->m_cachename . '.' . self::C__CACHE__EXTENSION;

        return $this;
    }

    /**
     * Method to prevent cloning.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function __clone()
    {
    }

    /**
     * Constructor. Requires a name for the cache. If a cache with the same name already exists,
     * it will be loaded. Preferred way is to use the static factory method!
     *
     * @param   string  $p_name       The name of this cache-instance.
     * @param   integer $p_expiration The expiration time of the cache.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @uses    isys_caching::set_expiration()
     */
    protected function __construct($p_name = null, $p_expiration = null)
    {
        if ($p_expiration !== null) {
            $this->set_expiration($p_expiration);
        }

        $this->set_paths($p_name);

        self::$m_updated[$this->m_cachename] = false;
        self::$m_loaded[$this->m_cachename] = false;
    }
}
