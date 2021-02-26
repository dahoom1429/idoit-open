<?php

namespace idoit\Tree;

use idoit\Component\Provider\DiFactory;

/**
 * i-doit Tree Processor
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class TreeProcessor
{
    use DiFactory;

    /**
     * Process the tree by calling ->tree() on $p_controller.
     *
     * @return \isys_application
     */
    public function process(\isys_controller $p_controller, \isys_register $p_request)
    {
        /**
         * Initialize the main tree
         */
        $l_tree = \isys_component_tree::factory('menu_tree');

        /**
         * Load tree by the controller
         */
        $l_nodes = $p_controller->tree($p_request, $this->getDi()->application, $l_tree);

        if (is_object($l_nodes) && $l_nodes instanceof \idoit\Tree\Node) {
            /**
             * Payload \isys_component_tree with a \idoit\Tree\Node tree structure
             */
            $l_tree->payload($l_nodes, $p_request);

            /**
             * Process tree and assign to template
             */
            \isys_component_template::instance()
                ->assign("menu_tree", $l_tree->process());
        }

        return $this;
    }

}