<?php

abstract class isys_export implements isys_export_iface
{
    const c__multi  = 1;
    const c__single = 0;

    /**
     * @var isys_component_database
     */
    protected $m_database;

    /**
     * @var isys_export_type_xml
     */
    protected $m_export_formatter;

    /**
     * Stores instance of the current export type
     *
     * @param  isys_export_type $p_export_formatter
     */
    public function set_export_formatter(isys_export_type &$p_export_formatter)
    {
        $this->m_export_formatter = $p_export_formatter;
    }

    public function get_export_formatter()
    {
        return $this->m_export_formatter;
    }

    /**
     * Return current database component
     *
     * @return isys_component_database
     */
    public function get_database()
    {
        return $this->m_database;
    }

    /**
     * Constructor
     *
     * @param isys_export_type        $p_export_type
     * @param isys_component_database $p_database
     */
    public function __construct($p_export_type, isys_component_database &$p_database = null)
    {

        /* Check instance of export type */
        if (is_string($p_export_type) && class_exists($p_export_type)) {
            $p_export_type = new $p_export_type();
        }

        if (!($p_export_type instanceof isys_export_type)) {
            throw new isys_exception_general("Export type is not an instance of isys_export_type or " . "any of its subclasses. Check your instantiation of isys_export.");
        }

        /* Set the export formatting instance (of type isys_export_type) */
        $this->set_export_formatter($p_export_type);

        /* Store database component */
        if (is_null($p_database) && !is_object($p_database)) {
            global $g_comp_database;
            $l_database = $g_comp_database;
        } else {
            $l_database = $p_database;
        }

        $this->m_database = $l_database;

        /* Check if database component is fine. */
        if (is_null($this->m_database)) {
            global $g_comp_session;
            if (!$g_comp_session->is_logged_in()) {
                throw new isys_exception_general("Your session is expired. You need to re-login!");
            } else {
                throw new isys_exception_general("Database component is NULL. Can't work with it! \n");
            }
        }
    }
}

/**
 * @package     i-doit
 * @subpackage  Export
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface isys_export_iface
{
    // Nothing to do here
}