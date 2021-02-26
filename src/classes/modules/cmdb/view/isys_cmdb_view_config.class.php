<?php

/**
 * CMDB Configuration view
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @author      i-doit-team
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_cmdb_view_config extends isys_cmdb_view
{
    /**
     * @return  mixed
     */
    abstract public function config_process();

    /**
     * @param  array &$l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        ;
    }

    /**
     * @param  array &$l_gets
     */
    public function get_optional_parameters(&$l_gets)
    {
        ;
    }

    /**
     * Process method.
     *
     * @return mixed
     */
    public function process()
    {
        $l_posts = $this->get_module_request()
            ->get_posts();
        $l_actionproc = $this->get_action_processor();

        $this->handle_navmode($l_posts[C__GET__NAVMODE]);

        if ($this->requires_module_reload()) {
            return null;
        }

        // Process actions (if there are any).
        $l_actionproc->process();

        return $this->config_process();
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