<?php

/**
 * CMDB Misc view.
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_cmdb_view_misc extends isys_cmdb_view
{
    /**
     * @return  mixed
     */
    abstract public function misc_process();

    /**
     * @param  array $l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        ;
    }

    /**
     * @param  array $l_gets
     */
    public function get_optional_parameters(&$l_gets)
    {
        ;
    }

    /**
     * @return  mixed
     */
    public function process()
    {
        return $this->misc_process();
    }

    /**
     * Public constructor, for protected parent.
     *
     * @param  isys_module_request $p_modreq
     */
    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);
    }
}

?>