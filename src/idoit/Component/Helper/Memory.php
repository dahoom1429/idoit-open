<?php

namespace idoit\Component\Helper;

use idoit\Component\Provider\Singleton;
use idoit\Exception\OutOfMemoryException;

/**
 * i-doit Memory helper
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.7.1
 */
class Memory
{
    use Singleton;

    /**
     * Unlimited memory
     *
     * @var integer
     */
    const UNLIMITED_MEMORY = -1;

    /**
     * Maximum amount of allocatable memory in bytes
     *
     * @var integer
     */
    protected $maxMemory = 0;

    /**
     * Maximum amount of allocatable memory in bytes
     *
     * @return integer
     */
    public function getMaxMemory()
    {
        return $this->maxMemory;
    }

    /**
     * Return current memory usage in bytes
     *
     * @return integer
     */
    public function getMemoryUsage()
    {
        return memory_get_usage(true);
    }

    /**
     * Return memory peak usage in bytes
     *
     * @return integer
     */
    public function getMemoryPeakUsage()
    {
        return memory_get_peak_usage(true);
    }

    /**
     * Checks wheather the given amount of memory is allocatable or not.
     *
     * @param int $amount Amount of memory to check in bytes.
     *
     * @return boolean
     */
    public function isMemoryAvailable($amount = 1024)
    {
        return !(($this->getMemoryUsage() + $amount) > $this->maxMemory);
    }

    /**
     * @param int $amount Amount of memory to check in bytes.
     *
     * @throws OutOfMemoryException
     */
    public function outOfMemoryBreak($amount = 1024)
    {
        if (!$this->isMemoryAvailable($amount)) {
            throw new OutOfMemoryException(sprintf(
                'Maximum allowed memory reached. (%s MB). You can increase this limit in your php configuration: https://php.net/manual/en/ini.core.php#ini.memory-limit',
                $this->maxMemory / 1024 / 1024
            ));
        }
    }

    /**
     * Memory constructor.
     */
    public function __construct()
    {
        // Get memory limit from ini
        $this->maxMemory = Filesize::toBytes(ini_get('memory_limit'));

        // Check whether memory limit is unlimited
        if ($this->maxMemory == self::UNLIMITED_MEMORY) {
            // Set memory limit to possible maximum
            $this->maxMemory = PHP_INT_MAX;
        }
    }
}
