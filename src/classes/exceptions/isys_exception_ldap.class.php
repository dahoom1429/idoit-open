<?php

/**
 * i-doit
 *
 * Class for ldap exceptions.
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.2.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_exception_ldap extends isys_exception
{
    /**
     * Exception constructor.
     *
     * @param  string  $p_message
     * @param  integer $p_errorcode
     */
    public function __construct($p_message, $p_errorcode = 0, $p_write_log = true)
    {
        parent::__construct($p_message, 'LDAP exception occured', $p_errorcode, 'exception', $p_write_log);
    }
}