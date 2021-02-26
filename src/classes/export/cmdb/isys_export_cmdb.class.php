<?php

/**
 * @package     i-doit
 * @subpackage  Export CMDB
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_export_cmdb extends isys_export
{
    /**
     * @var  mixed
     */
    protected $m_export;

    /**
     * Export method.
     *
     * @abstract
     *
     * @param  array $p_object_ids
     */
    abstract public function export($p_object_ids); // function

    public function get_export()
    {
        return $this->m_export;
    }

    public function set_export($p_export)
    {
        $this->m_export = $p_export;
    }

    public function __construct(&$p_export_type, isys_component_database &$p_database = null)
    {
        parent::__construct($p_export_type, $p_database);
    }
}

?>