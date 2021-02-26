<?php

/**
 * i-doit
 *
 * Missing function/class exception.
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.3
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_exception_missing_function extends isys_exception
{
    /**
     * Exception topic, may contain a language constant!
     *
     * @var  string
     */
    protected $m_exception_topic = 'Missing function';

    /**
     * Exception constructor.
     *
     * @param  string  $p_message
     * @param  integer $p_error_code
     */
    public function __construct($p_function = '', $p_message = null)
    {
        if (!$p_message) {
            $p_message = isys_application::instance()->container->get('language')
                ->get('LC__EXCEPTION__MISSING_FUNCTION', $p_function);
        }

        parent::__construct($p_message, '');
    }
}
