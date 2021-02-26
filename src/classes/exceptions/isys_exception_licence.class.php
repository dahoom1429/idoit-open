<?php

/**
 * i-doit
 *
 * Class for licence exceptions.
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_exception_licence extends isys_exception
{
    /**
     * Error-type.
     *
     * @var  integer
     */
    protected $m_licence_error = 1;

    /**
     * Retrieve the current errorcode from this exception.
     *
     * @return  integer
     */
    public function get_errorcode()
    {
        return $this->m_licence_error;
    }

    /**
     * Exception Constructor.
     *
     * @param  string  $p_message
     * @param  integer $p_errorcode
     */
    public function __construct($p_message, $p_errorcode)
    {
        if ($p_errorcode != null) {
            $this->m_licence_error = $p_errorcode;
        }

        parent::__construct($p_message, $this->m_licence_error);
    }
}