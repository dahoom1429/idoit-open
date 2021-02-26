<?php

namespace idoit\Component\ClassLoader;

use Symfony\Component\ClassLoader\MapClassLoader as SymfonyMapClassLoader;

/**
 * i-doit classmap loader
 *
 * @package     idoit\Component
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 */
class MapClassLoader extends SymfonyMapClassLoader
{
    /**
     * @var string
     */
    protected $basePath = '';

    /**
     * @var array
     */
    protected $map = [];

    /**
     * @param array  $map
     * @param string $basePath
     *
     * @return MapClassLoader
     */
    public static function factory(array $map, $basePath = '')
    {
        return new self($map, $basePath);
    }

    /**
     * Constructor.
     *
     * @param array  $map      A map where keys are classes and values the absolute file path
     * @param string $basePath Root directory
     */
    public function __construct(array $map, $basePath = '')
    {
        $this->map = $map;

        if (file_exists($basePath)) {
            $this->basePath = $basePath;
        }
    }

    /**
     * Loads the given class or interface.
     *
     * @param string $class The name of the class
     *
     * @return bool
     */
    public function loadClass($class)
    {
        if (isset($this->map[$class]) && file_exists($this->basePath . $this->map[$class])) {
            require $this->basePath . $this->map[$class];

            return true;
        }

        return false;
    }

}
