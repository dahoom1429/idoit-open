<?php

namespace idoit\Component\Settings;

/**
 * i-doit Static DbSystem setting component
 *
 * @package     idoit\Component
 * @author      atsapko
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

class DbSystem extends Settings
{
    private $_settings;

    /**
     * @return DbSystem
     */
    public static function factory()
    {
        global $g_db_system;

        $settings = new self();

        $settings->_settings = $g_db_system;

        return $settings;
    }

    /**
     * @param null   $key
     * @param string $default
     *
     * @return mixed
     */
    public function get($key = null, $default = '')
    {
        if ($key == null) {
            return $this->_settings;
        }

        return (isset($this->_settings[$key]) ? $this->_settings[$key] : $default);
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        $this->_settings[$key] = $value;

        return $this;
    }

    /**
     * @param array $setting
     *
     * @return void
     */
    public function extend($setting)
    {
        $this->_settings = array_merge($this->_settings, (array)$setting);
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public function remove($key)
    {
        unset($this->_settings[$key]);
    }

    /**
     * @return void
     */
    public function save()
    {
    }
}