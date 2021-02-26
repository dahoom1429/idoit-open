<?php

/**
 * i-doit
 *
 * CMDB exception class.
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_exception_cmdb extends isys_exception
{
    /**
     * Exception topic, may contain a language constant!
     *
     * @var  string
     */
    protected $m_exception_topic = 'CMDB exception';

    /**
     * Exception constructor.
     *
     * @param   string  $p_message
     * @param   integer $p_errorcode
     * @param   string  $p_trace
     *
     * @author  Andre Woesten <awoesten@i-doit.de>
     */
    public function __construct($p_message, $p_errorcode = 0, $p_trace = "", $p_write_log = true)
    {
        if (!is_null($p_trace)) {
            $this->m_full_trace = $p_trace;
        } else {
            $this->m_full_trace = $this->getTrace();
        }

        parent::__construct('CMDB Error: ' . $p_message, $p_errorcode, 0, 'exception', $p_write_log);
    }
}