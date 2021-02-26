<?php

/**
 * i-doit
 *
 * Filesystem exception class.
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_exception_filesystem extends isys_exception
{
    /**
     * Exception constructor.
     *
     * @param   string  $p_message
     * @param   string  $p_extinfo
     * @param   integer $p_code
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function __construct($p_message, $p_extinfo = '', $p_code = 0)
    {
        isys_application::instance()->container->get('logger')->warning('Filesystem error: ' . $p_message);
        parent::__construct('Filesystem error: ' . $p_message, $p_extinfo, $p_code);
    }
}
