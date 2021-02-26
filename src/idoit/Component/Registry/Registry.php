<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Component\Registry;

/**
 * Class Registry
 * Register the properties of components
 *
 * @package idoit\Component\Registry
 */
class Registry
{
    /**
     * @var array
     */
    protected $registered = [];

    /**
     * @param       $name
     * @param array $properties
     */
    public function register($name, $property)
    {
        $this->registered[$name] = $property;
    }

    /**
     * @param $name
     *
     * @return mixed|null
     */
    public function find($name)
    {
        if (isset($this->registered[$name])) {
            return $this->registered[$name];
        }

        return null;
    }
}
