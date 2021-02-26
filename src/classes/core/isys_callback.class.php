<?php

/**
 * i-doit core classes
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_callback
{
    /**
     * Callback class.
     *
     * @var  string
     */
    protected $m_class = '';

    /**
     * Callback method.
     *
     * @var  string
     */
    protected $m_method = '';

    /**
     * Optional callback parameters.
     *
     * @var  array
     */
    protected $m_parameters = [];

    /**
     * This method will execute the given callback.
     *
     * @param   isys_request $p_request
     *
     * @return  mixed
     * @throws  Exception
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function execute(isys_request $p_request = null)
    {
        try {
            global $g_comp_database;

            $l_class = isys_factory::get_instance($this->m_class, $g_comp_database);
            $l_method = $this->m_method;

            if (method_exists($l_class, $l_method)) {
                if ($p_request === null) {
                    $p_request = isys_request::factory();
                }

                return $l_class->$l_method($p_request, $this->m_parameters);
            }
        } catch (Exception $e) {
            throw $e;
        }

        return null;
    }

    /**
     * Constructor, needs at least the first parameter.
     *
     * @param   array $p_callback
     * @param   array $p_parameters
     *
     * @throws  Exception
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function __construct(array $p_callback, $p_parameters = [])
    {
        if (count($p_callback) !== 2) {
            throw new Exception('Callback parameter has to be an array with two values: array("class", "method").');
        }

        list($this->m_class, $this->m_method) = $p_callback;
        $this->m_parameters = $p_parameters;
    }
}