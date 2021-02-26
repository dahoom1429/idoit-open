<?php

/**
 * i-doit
 *
 * CMDB DAO exception class.
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_exception_dao_cmdb extends isys_exception_dao
{
    /**
     * Exception constructor.
     *
     * @param  string  $p_message
     * @param  string  $p_strDAO This is really really unnecessary.
     * @param  integer $p_errorcode
     * @param  object  $p_dao
     *
     * @todo   Refactor and kick "$p_strDAO" out!
     */
    public function __construct($p_message, $p_strDAO = "", $p_errorcode = 0, $p_dao = null)
    {
        if (!empty($p_dao)) {
            $l_dao = "CMDB (" . get_class($p_dao) . "): ";
        } else {
            $l_dao = '';
        }

        parent::__construct($l_dao . $p_message, $p_errorcode);
    }
}