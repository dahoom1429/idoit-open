<?php

/**
 * i-doit
 *
 * Import helper
 *
 * @package     i-doit
 * @subpackage  Import
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_import_helper
{
    /**
     * @var  array
     */
    private $m_attributes;

    /**
     * @var  string
     */
    private $m_content;

    /**
     * @var  array
     */
    private $m_variables;

    /**
     * Method for getting all attributes.
     *
     * @return  array
     */
    public function attributes()
    {
        return $this->m_attributes;
    }

    /**
     * Method for getting all attributes.
     *
     * @return  array
     */
    public function get_attributes()
    {
        return $this->m_attributes;
    }

    /**
     * Method for getting attributes.
     *
     * @param   string $p_attribute
     *
     * @return  mixed
     */
    public function get_attribute($p_attribute)
    {
        if (isset($this->m_attributes[$p_attribute])) {
            return $this->m_attributes[$p_attribute];
        } else {
            return false;
        }
    }

    /**
     * Method for setting variables.
     *
     * @param   string $p_key
     * @param   mixed  $p_value
     *
     * @return  isys_import_helper
     */
    public function set_variable($p_key, $p_value)
    {
        $this->m_variables[$p_key] = $p_value;

        return $this;
    }

    /**
     * Method for setting attributes.
     *
     * @param   string $p_attribute
     * @param   mixed  $p_value
     *
     * @return  isys_import_helper
     */
    public function set_attribute($p_attribute, $p_value)
    {
        $this->m_attributes[$p_attribute] = $p_value;

        return $this;
    }

    /**
     * Magic toString() method.
     *
     * @return  string
     */
    public function __toString()
    {
        if ($this->m_content) {
            return (string)$this->m_content;
        } else {
            return "";
        }
    }

    /**
     * Isset method.
     *
     * @param   string $p_variable
     *
     * @return  boolean
     */
    public function __isset($p_variable)
    {
        return (isset($this->m_variables[$p_variable]));
    }

    /**
     * Unsetter.
     *
     * @param  string $p_variable
     */
    public function __unset($p_variable)
    {
        unset($this->m_variables[$p_variable]);
    }

    /**
     * Getter.
     *
     * @param   string $p_variable
     *
     * @return  mixed
     */
    public function get($p_variable)
    {
        return $this->__get($p_variable);
    }

    /**
     * Megic getter.
     *
     * @param   string $p_variable
     *
     * @return  mixed
     */
    public function __get($p_variable)
    {
        if (!empty($p_variable) && array_key_exists($p_variable, $this->m_variables)) {
            return $this->m_variables[$p_variable];
        }

        return null;
    }

    /**
     * Magic setter.
     *
     * @param  string $p_variable
     * @param  mixed  $p_content
     */
    public function __set($p_variable, $p_content)
    {
        $this->m_variables[$p_variable] = $p_content;
    }

    /**
     * Constructor.
     *
     * @param  array  $p_attributes
     * @param  string $p_content
     * @param  array  $p_variables
     */
    public function __construct($p_attributes = [], $p_content = "", $p_variables = [])
    {
        $this->m_attributes = $p_attributes;
        $this->m_content = $p_content;
        $this->m_variables = $p_variables;
    }
}