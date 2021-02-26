<?php

namespace idoit\Component\Settings;

/**
 * i-doit Setting component
 *
 * @package     idoit\Component
 * @author      atsapko
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

abstract class Settings
{
    /**
     * @param string $key
     * @param string $default
     *
     * @return mixed
     */
    abstract public function get($key = null, $default = '');

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    abstract public function set($key, $value);

    /**
     * @param array $setting
     *
     * @return void
     */
    abstract public function extend($setting);

    /**
     * @param string $key
     *
     * @return void
     */
    abstract public function remove($key);

    /**
     * @return void
     */
    abstract public function save();
}