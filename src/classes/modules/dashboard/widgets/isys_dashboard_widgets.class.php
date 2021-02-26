<?php

/**
 * i-doit
 *
 * Dashboard widget class.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.2
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_dashboard_widgets
{
    /**
     * Array for external widgets.
     *
     * @var  array
     */
    protected static $m_external = [];

    /**
     * Array for all our instances
     *
     * @var  array
     */
    protected static $m_instances = [];

    /**
     * Ajax url information
     *
     * @var array
     */
    protected $m_ajax_url = [];

    /**
     * @var array
     */
    protected $m_config = [];

    /**
     * @var isys_component_template
     */
    protected $m_tpl = null;

    /**
     * @var isys_component_template_language_manager
     */
    protected $language = null;

    /**
     * @var isys_component_database
     */
    protected $database = null;

    /**
     * Abstract render method.
     *
     * @param   string $p_unique_id
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    abstract public function render($p_unique_id);

    /**
     * Factory method for instant method chaining.
     *
     * @param   string $p_class
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function factory($p_class)
    {
        return isys_factory::get_instance($p_class);
    }

    /**
     * Method for adding an external widget.
     *
     * @static
     *
     * @param   string $p_identifier
     * @param   string $p_class
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @see     isys_register
     */
    public static function add_external_widget($p_identifier, $p_class)
    {
        isys_register::factory('widget-register')
            ->set($p_identifier, $p_class);
    }

    /**
     * Method for retrieving the class name of an external widget.
     *
     * @static
     *
     * @param   string $p_identifier
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @see     isys_register
     */
    public static function get_external_widget_class($p_identifier)
    {
        return isys_register::factory('widget-register')
            ->get($p_identifier);
    }

    /**
     * Returns a boolean value, if the current widget has an own configuration page.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function has_configuration()
    {
        return false;
    }

    /**
     * Returns a boolean value, if the current widget has an own ajax handler
     *
     * @return boolean
     */
    public function has_ajax_handler()
    {
        return false;
    }

    /**
     * Gets ajax url information
     *
     * @return array
     */
    public function get_ajax_url()
    {
        return $this->m_ajax_url;
    }

    /**
     * Sets ajax parameters
     *
     * @param $p_array
     */
    public function set_ajax_url($p_array)
    {
        $this->m_ajax_url = $p_array;
    }

    /**
     * Method for loading the widget configuration.
     * This method should return a rendered template with forms for the configuration - Use like "return $this->m_tpl->fetch('config.tpl');".
     *
     * @param   array   $p_row The current widget row from "isys_widgets".
     * @param   integer $p_id  The ID from "isys_widgets_config".
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function load_configuration(array $p_row, $p_id)
    {
        return '';
    }

    /**
     * Dummy init method.
     *
     * @param  array $p_config
     *
     * @return $this
     * @throws Exception
     * @author Leonard Fischer <lfischer@i-doit.com>
     */
    public function init($p_config = [])
    {
        $this->m_tpl = isys_application::instance()->container->get('template');
        $this->language = isys_application::instance()->container->get('language');
        $this->database = isys_application::instance()->container->get('database');
        $this->m_config = $p_config;

        return $this;
    }
}