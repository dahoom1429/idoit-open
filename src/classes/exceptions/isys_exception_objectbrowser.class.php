<?php

/**
 * i-doit
 *
 * CMDB exception class.
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_exception_objectbrowser extends isys_exception
{
    /**
     * Variable for detailed error message.
     *
     * @var  string
     */
    private $m_detailed_error = '';

    /**
     * Method for retrieving the detail message.
     *
     * @return  string
     */
    public function getDetailMessage()
    {
        return $this->m_detailed_error;
    }

    /**
     * Exception constructors.
     *
     * @param  string  $p_message
     * @param  integer $p_detailed_error
     */
    public function __construct($p_message, $p_detailed_error)
    {
        $this->m_detailed_error = $p_detailed_error;
        parent::__construct($p_message, $p_detailed_error);
    }
}