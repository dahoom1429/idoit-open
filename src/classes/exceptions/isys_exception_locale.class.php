<?php

/**
 * i-doit
 * Locale exception class.
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_exception_locale extends isys_exception
{
    /**
     * Exception constructor.
     *
     * @param  string  $p_message
     * @param  integer $p_errorcode
     */
    public function __construct($p_message, $p_errorcode = 0)
    {
        parent::__construct("Locale error: $p_message", $p_errorcode);
    }
}