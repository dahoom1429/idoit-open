<?php

/**
 * CMDB Misc view for blank pages.
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_view_misc_blank extends isys_cmdb_view_misc
{
    /**
     * @return  integer
     */
    public function get_id()
    {
        return C__CMDB__VIEW__MISC_BLANK;
    }

    /**
     * @param  array &$l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        parent::get_mandatory_parameters($l_gets);
    }

    /**
     * @return  string
     */
    public function get_name()
    {
        return "Leere Ansicht";
    }

    /**
     * @param  array &$l_gets
     */
    public function get_optional_parameters(&$l_gets)
    {
        parent::get_optional_parameters($l_gets);
    }

    /**
     * Empty handle-navmode method.
     *
     * @param  integer $p_navmode
     */
    public function handle_navmode($p_navmode)
    {
        ;
    }

    /**
     * @return  string
     */
    public function misc_process()
    {
        return "This is view is MISC::BLANK";
    }

    /**
     * @param  isys_module_request $p_modreq
     */
    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);
    }
}

?>