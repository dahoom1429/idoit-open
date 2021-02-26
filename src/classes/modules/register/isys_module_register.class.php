<?php

/**
 * i-doit
 *
 * Module register
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_register
{
    const DISPLAY_IN_MAIN_MENU = false;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = false;

    /**
     * @var bool
     */
    protected static $m_licenced = true;

    /**
     * Array with module data from module table
     *
     * @var array
     */
    private $m_data;

    /**
     * ID of module
     *
     * @var integer
     */
    private $m_id;

    /**
     * Module identifier
     *
     * @var string
     */
    private $m_identifier;

    /**
     * Is registry entry initialized?
     *
     * @var boolean
     */
    private $m_initialized;

    /**
     * @var isys_module_manager
     */
    private $m_module_manager;

    /**
     * Reference to module object
     *
     * @var isys_module
     */
    private $m_object;

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @since   0.9.9-7
     * @see     isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
    }

    /**
     * Returns the module ID
     *
     * @return integer
     */
    public function get_id()
    {
        if (!$this->is_initialized()) {
            return null;
        }

        return $this->m_id;
    }

    /**
     * Returns the module Identifier
     *
     * @return string
     */
    public function get_identifier()
    {
        return $this->m_identifier;
    }

    /**
     * Returns the data array
     *
     * @return array|string
     */
    public function get_data($p_key = null)
    {
        if ($p_key && isset($this->m_data[$p_key])) {
            return $this->m_data[$p_key];
        }

        return $this->m_data;
    }

    /**
     * Returns the module object
     *
     * @return isys_module
     */
    public function &get_object()
    {
        if (!$this->is_initialized()) {
            return null;
        }

        return $this->m_object;
    }

    /**
     * Is the registry entry initialized?
     *
     * @return boolean
     */
    public function is_initialized()
    {
        return $this->m_initialized;
    }

    /**
     * Creates the module object and returns it
     *
     * @param isys_module_request $p_modreq
     *
     * @return isys_module
     */
    public function &make_object(isys_module_request &$p_modreq)
    {
        // If the object is already existent, return it.
        if ($this->is_initialized()) {
            return $this->m_object;
        }

        // Otherwise create object.
        if (is_object($p_modreq)) {
            $this->m_initialized = false;
            $l_class = $this->m_data['isys_module__class'];

            // Instantiate module object.
            if (!class_exists($l_class)) {
                throw new isys_exception_general('Module class ' . $l_class . ' does not exist, but it is registered in isys_module!<br />');
            }

            $this->m_object = new $l_class();
            $this->m_object->set_data($this->m_data);

            if ($this->m_object->init($p_modreq) !== false) {
                $this->m_initialized = true;
            }

            return $this->m_object;
        }

        return null;
    }

    /**
     * isys_module_register constructor.
     *
     * @param      $p_id
     * @param      $p_data
     * @param      $p_module_manager
     * @param bool $p_initialized
     * @param null $p_object
     */
    public function __construct($p_id, $p_data, $p_module_manager, $p_initialized = false, $p_object = null)
    {
        $this->m_id = $p_id;
        $this->m_data = $p_data;
        $this->m_identifier = $p_data['isys_module__identifier'] ?: $p_id;
        $this->m_initialized = $p_initialized;
        $this->m_object = $p_object;
    }
}
