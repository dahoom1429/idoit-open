<?php

/**
 * i-doit
 *
 * API exception class.
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_exception_api_validation extends isys_exception_api
{
    /**
     * Exception topic, may contain a language constant!
     *
     * @var  string
     */
    protected $m_exception_topic = 'API validation exception';

    /**
     *
     * @var array
     */
    protected $m_validation_errors = [];

    /**
     * Variable which holds the current error-code.
     *
     * @var  integer
     */
    private $m_error_code = 0;

    /**
     * @return array
     */
    public function get_validation_errors()
    {
        return $this->m_validation_errors;
    }

    /**
     * Method for returning the error code.
     *
     * @return  integer
     */
    public function get_error_code()
    {
        return $this->m_error_code;
    }

    /**
     * Exception constructor.
     *
     * @param  string $p_message
     * @param  array  $p_validation_errors
     */
    public function __construct($p_message, $p_validation_errors)
    {
        $this->m_validation_errors = $p_validation_errors;
        parent::__construct($p_message, 0);
    }
}