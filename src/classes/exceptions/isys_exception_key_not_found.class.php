<?php

/**
 * i-doit
 *
 * Key Not Found exception
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.4
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_exception_key_not_found extends isys_exception
{
    /**
     * Exception constructor.
     *
     * @param  string  $p_message
     * @param  integer $p_errorcode
     */
    public function __construct($p_message, $p_errorcode = 0)
    {
        parent::__construct("Key not found: $p_message", $p_errorcode);
    }
}