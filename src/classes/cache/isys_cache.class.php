<?php

/**
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.6
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_cache
{
    /**
     * @var string
     */
    private static $foundType;

    /**
     * Cache options.
     *
     * @var  array
     */
    protected $m_options = [];

    /**
     * Get any available keyvalue cache with the following (default) priority:
     *   memcached > memcache > apc > xcache > fs > "none"
     *
     * Cache priority can be overwritten by passing an array:
     *   ['xcache', 'memcache', 'apc']
     *
     * @param   $cachePriority  array
     *
     * @todo    Check if we should deprecate "$cachePriority".
     * @return  isys_cache_keyvalue
     */
    public static function keyvalue($cachePriority = null)
    {
        // If we already found a caching type and no specific priority is set, we simply re-use it.
        if ($cachePriority === null && self::$foundType !== null) {
            return new self::$foundType;
        }

        try {
            $cacheRegister = $cachePriority ?: [
                'memcached',
                'memcache',
                'apc',
                'xcache',
                'fs',
            ];

            foreach ($cacheRegister as $cachingType) {
                $className = 'isys_cache_' . $cachingType;

                // Return first available cache.
                if (class_exists($className) && call_user_func([$className, 'available'])) {
                    if ($cachePriority === null) {
                        self::$foundType = $className;
                    }

                    return new $className;
                }
            }
        } catch (isys_exception_cache $e) {
        }

        if ($cachePriority === null) {
            self::$foundType = 'isys_cache_keyvalue_dummy';
        }
        return new isys_cache_keyvalue_dummy();
    }

    /**
     * Set options for cache handlers.
     *
     * @param array $p_options
     *
     * @return isys_cache
     */
    public function set_options(array $p_options = [])
    {
        $this->m_options = $p_options;

        return $this;
    }

    /**
     * Get default expiration time in seconds
     *
     * @param int $p_ttl
     *
     * @return int
     */
    public function default_expiration($p_ttl = -1)
    {
        // Return default if $p_ttl lower then 0
        if ($p_ttl < 0) {
            // Default = 1 day
            return isys_tenantsettings::get('cache.default-expiration-time', 86400);
        }

        return $p_ttl;
    }
}
