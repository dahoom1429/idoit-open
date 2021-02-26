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
class isys_exception_api extends isys_exception
{
    /**
     * Exception topic, may contain a language constant!
     *
     * @var  string
     */
    protected $m_exception_topic = 'API exception';

    /**
     * Variable which holds the current error-code.
     *
     * @var  integer
     */
    private $m_error_code = 0;

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
     * @param  string  $p_message
     * @param  integer $p_error_code
     */
    public function __construct($p_message, $p_error_code = 0)
    {
        $this->m_error_code = $p_error_code;
        parent::__construct($p_message, '', $p_error_code, 'exception', false);
    }
}