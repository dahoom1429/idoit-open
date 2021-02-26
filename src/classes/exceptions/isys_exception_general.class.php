<?php

/**
 * i-doit
 *
 * Class for general exceptions.
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     1.2.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_exception_general extends isys_exception
{
    /**
     * Exception constructor.
     *
     * @param  string  $p_message
     * @param  integer $p_errorcode
     */
    public function __construct($p_message, $p_errorcode = 0, $p_write_log = true)
    {
        parent::__construct(isys_application::instance()->container->get('language')
                ->get('LC__EXCEPTION__GENERAL') . ': ' . $p_message, 'General exception occured', $p_errorcode, 'exception', $p_write_log);
    }
}
