<?php

/**
 * i-doit core classes
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_register implements Countable
{
    /**
     * Instance array.
     *
     * @var  array
     */
    protected static $m_instances = [];

    /**
     * Data array.
     *
     * @var  array
     */
    protected $m_data = [];

    /**
     * Static factory method.
     *
     * @static
     *
     * @param   $p_name
     *
     * @return  isys_register
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function factory($p_name)
    {
        if (!array_key_exists($p_name, self::$m_instances)) {
            self::$m_instances[$p_name] = new self;
        }

        return self::$m_instances[$p_name];
    }

    /**
     * Method for checking, if a certain value is set.
     *
     * @param   string $p_name
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function has($p_name)
    {
        return isset($this->m_data[$p_name]);
    }

    /**
     * Setter.
     *
     * @param   string $p_name
     * @param   mixed  $p_value
     *
     * @return  isys_register
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set($p_name, $p_value = null)
    {
        $this->m_data[$p_name] = $p_value;

        return $this;
    }

    /**
     * Merge attributes
     *
     * @param array   $p_data The data to merge into the collection
     * @param boolean $p_replace
     *
     * @access public
     * @return isys_register
     */
    public function merge(array $p_data = [], $p_replace = false)
    {
        // Don't waste our time with an "array_merge" call if the array is empty
        if (!empty($p_data)) {
            if ($p_replace) {
                $this->m_data = array_replace($this->m_data, $p_data);
            } else {
                $this->m_data = array_merge($this->m_data, $p_data);
            }
        }

        return $this;
    }

    /**
     * Getter.
     *
     * @param   string $p_name
     * @param   mixed  $p_default
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get($p_name = null, $p_default = null)
    {
        if ($p_name !== null) {
            if (isset($this->m_data[$p_name])) {
                return $this->m_data[$p_name];
            }

            return $p_default;
        }

        return $this->m_data;
    }

    /**
     * Magic isset method.
     *
     * @param   string $p_name
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function __isset($p_name)
    {
        return $this->has($p_name);
    }

    /**
     * Magic getter.
     *
     * @param   string $p_name
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function __get($p_name)
    {
        return $this->get($p_name);
    }

    /**
     * Magic setter.
     *
     * @param   string $p_name
     * @param   mixed  $p_value
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function __set($p_name, $p_value = null)
    {
        $this->set($p_name, $p_value);
    }

    /**
     * Magic toString method.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function __toString()
    {
        return var_export($this->m_data, true);
    }

    /**
     * Counts all elements of an register.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function count()
    {
        return count($this->m_data);
    }
}