<?php

/**
 * CMDB List view
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_cmdb_view_list extends isys_cmdb_view
{
    /**
     * List component
     *
     * @var  isys_component_list
     */
    protected $m_comp_list;

    /**
     * Abstract "list_init" method.
     *
     * @return  mixed
     */
    abstract public function list_init();

    /**
     * Abstract "list_process" method.
     *
     * @return  mixed
     */
    abstract public function list_process();

    /**
     * Returns the mandatory parameters.
     *
     * @param  array &$l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        ;
    }

    /**
     * Returns the optional parameters.
     *
     * @param  array &$l_gets
     */
    public function get_optional_parameters(&$l_gets)
    {
        ;
    }

    /**
     * Returns template destination.
     *
     * @return  string
     */
    public function get_template_destination()
    {
        return "table_rows";
    }

    /**
     * This method returns the "top" template filepath.
     *
     * @return  string
     */
    public function get_template_top()
    {
        return null;
    }

    /**
     * Empty method.
     *
     * @param $p_navmode
     */
    public function handle_navmode($p_navmode)
    {
        ;
    }

    /**
     * Process method.
     *
     * @global  array $index_includes
     * @return  mixed
     */
    public function process()
    {
        // Prepare operation data.
        $l_posts = $this->get_module_request()
            ->get_posts();
        $l_actionproc = $this->get_action_processor();

        if ($this->list_init()) {
            // Handle selected navigation mode and fill action processor.
            try {
                $this->handle_navmode($l_posts[C__GET__NAVMODE]);
            } catch (isys_exception_auth $e) {
                isys_application::instance()->container['notify']->error($e->getMessage());
                return $this->list_process();
            }

            if ($this->requires_module_reload()) {
                return null;
            }

            // Process actions (if there are any).
            $l_actionproc->process();

            return $this->list_process();
        }
    }

    /**
     * Constructor method.
     *
     * @param  isys_module_request $p_modreq
     */
    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);

        // Create "clean" list component.
        $this->m_comp_list = new isys_component_list();
    }
}

?>