<?php

//use \isys_controller as ControllerInterface;

/**
 * i-doit controller interface
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface isys_controller
{
    /**
     * Return dao for current view
     *
     * @return idoit\Model\Dao\Base
     */
    public function dao(isys_application $p_application);

    /**
     * Gets called when route matches current controller and there is no NavbarHandable navmode set
     *
     * @param isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function handle(isys_register $p_request, isys_application $p_application);

    /**
     * Build the left tree
     *
     * @return idoit\Tree\Node
     */
    public function tree(isys_register $p_request, isys_application $p_application, isys_component_tree $p_tree);

    /**
     * @optional Gets called BEFORE routing to the controller
     */
    //public function pre();

    /**
     * @optional Gets called AFTER routing to the controller
     */
    //public function post();

    /**
     * Dependency Injection: Every controller has to get it's corresponding module
     *
     * @param isys_module $p_module
     */
    public function __construct(isys_module $p_module);
}