<?php

/**
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.6
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cache_fs extends isys_cache_keyvalue implements isys_cache_keyvaluable
{
    /**
     * Cache expiration time in seconds
     *
     * @var int
     */
    protected $m_options = [
        'expiration' => 60
    ];

    /**
     * Cache directory.
     *
     * @var  string
     */
    private $m_directory = ''; // Set within constructor

    /**
     * Check wheather fs cache is available or not.
     *
     * @return  boolean
     */
    public static function available()
    {
        return true;
    }

    /**
     * Deletes a cache item from filesystem.
     *
     * @param   string $p_key
     *
     * @return  isys_cache_fs
     */
    public function delete($p_key)
    {
        $this->prepend_ns($p_key);

        $l_hash = md5($p_key);

        if (file_exists($l_hash)) {
            unlink($l_hash);
        }

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

        $l_hash = md5($p_key);

        return file_exists($this->m_directory . $l_hash) && (filemtime($this->m_directory . $l_hash) > (time() - (int)@$this->m_options['expiration']));
    }

    /**
     * Flush cache
     *
     * @return boolean
     */
    public function flush()
    {
        // Do not allow flushing without namespace
        if ($this->m_ns) {
            // Setup target directory path
            $targetDirectory = $this->m_directory . md5($this->m_ns);

            // Check whether namespace directory exists
            if (is_dir($targetDirectory)) {
                $deleted = $undeleted = 0;
                // Remove all content of namespace directory
                isys_glob_delete_recursive($targetDirectory, $deleted, $undeleted);

                // Remove namespace directory itself
                rmdir($targetDirectory);

                return true;
            }
        }

        return false;
    }

    /**
     * Get value of $p_key from filesystem.
     *
     * @param   string $p_key
     *
     * @return  mixed
     */
    public function get($p_key)
    {
        if ($this->m_ns) {
            $ns = md5($this->m_ns) . DIRECTORY_SEPARATOR;
        } else {
            $ns = '';
        }

        $file = $this->m_directory . $ns . md5($p_key);

        return file_exists($file) && (filemtime($file) > (time() - (int)@$this->m_options['expiration'])) ? unserialize(file_get_contents($file)) : null;
    }

    /**
     * Prepare and use a namespace
     *
     * @param string $p_namespace
     *
     * @return  isys_cache_keyvalue
     */
    public function ns($p_namespace)
    {
        $this->m_ns = $p_namespace;

        $md5ns = md5($p_namespace);
        $directoryName = $this->m_directory . $md5ns;
        if (!file_exists($directoryName)) {
            if (!mkdir($directoryName, 0775, true) && !is_dir($directoryName)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $directoryName));
            }
        }

        return $this;
    }

    /**
     * Invalidates a namespace
     *
     * @param $p_namespace
     *
     * @return isys_cache_keyvalue
     */
    public function ns_invalidate($p_namespace)
    {
        $this->ns($p_namespace)
            ->flush();

        return $this;
    }

    /**
     * Set $p_key to $p_value in filesystem persistent cache.
     *
     * @param   string  $p_key
     * @param   mixed   $p_value
     * @param   integer $p_ttl
     *
     * @return  isys_cache_fs
     */
    public function set($p_key, $p_value = null, $p_ttl = -1)
    {
        if (!$p_key && $p_key !== 0) {
            return $this;
        }

        if ($this->m_ns) {
            $ns = md5($this->m_ns) . DIRECTORY_SEPARATOR;
        } else {
            $ns = '';
        }

        $file = $this->m_directory . $ns . md5($p_key);

        if (file_exists($file) && !is_writable($file) && !is_writable($this->m_directory . $ns)) {
            return $this;
        }

        @isys_file_put_contents($file, serialize($p_value));

        return $this;
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->m_directory = rtrim(isys_glob_get_temp_dir(), '/') . '/' . isys_application::instance()->session->get_tenant_cache_dir() . '/fscache/';

        if (!is_dir($this->m_directory)) {
            if (is_writable(dirname($this->m_directory))) {
                mkdir($this->m_directory, 0775);
            } else {
                throw new isys_exception_cache('Filesystem cache not available. Could not write to: ' . dirname($this->m_directory), 'fs');
            }
        }

        if (!is_writable($this->m_directory)) {
            throw new isys_exception_cache('Filesystem cache not available. Could not write to: ' . $this->m_directory, 'fs');
        }
    }
}
