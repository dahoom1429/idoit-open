<?php

/**
 * @package     i-doit
 * @subpackage  Export
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_export_data
{
    /**
     * Variable for holding our "data".
     *
     * @var  mixed
     */
    protected $m_data;

    /**
     * Method for retrieving previously saved data.
     *
     * @return  mixed
     */
    public function get_data()
    {
        return $this->m_data;
    }

    /**
     * Method for setting data.
     *
     * @param  mixed $p_data
     */
    public function set_data($p_data)
    {
        $this->m_data = $p_data;
    }

    /**
     * @desc fixing roolbar item error: E_RECOVERABLE_ERROR: Object of class isys_export_data could not be converted to string (https://rollbar.com/Synetics/i-doit/items/864/)
     */
    public function __toString()
    {
        return json_encode($this->m_data);
    }

    /**
     * Constructor
     *
     * @param  mixed $p_data
     */
    public function __construct($p_data = null)
    {
        $this->m_data = $p_data;
    }
}