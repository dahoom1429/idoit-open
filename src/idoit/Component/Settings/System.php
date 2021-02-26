<?php

namespace idoit\Component\Settings;

/**
 * i-doit System setting component
 *
 * @package     idoit\Component
 * @author      atsapko
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

class System extends Settings
{
    /**
     * @param \isys_component_database $db
     *
     * @return System
     */
    public static function factory(\isys_component_database $db)
    {
        \isys_settings::initialize($db);

        return new self();
    }

    /**
     * @param null   $key
     * @param string $default
     *
     * @return mixed
     */
    public function get($key = null, $default = '')
    {
        return \isys_settings::get($key, $default);
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        \isys_settings::set($key, $value);

        return $this;
    }

    /**
     * @param array $setting
     *
     * @return void
     */
    public function extend($setting)
    {
        \isys_settings::extend($setting);
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public function remove($key)
    {
        \isys_settings::remove($key);
    }

    /**
     * @return void
     */
    public function save()
    {
        \isys_settings::force_save();
    }
}