<?php

/**
 * i-doit
 *
 * Ajax tree implementation.
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Andreas Schommer
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_ajaxtree extends isys_component
{
    /**
     * The name of the tree.
     *
     * @var  string
     */
    private $m_tree_name;

    /**
     * The output javascript string.
     *
     * @var  string
     */
    private $m_tree_output;

    /**
     * Initializes the tree with the given name.
     *
     * @param   string $p_name
     * @param   string $p_datasource
     * @param   string $p_icon
     * @param   string $p_caption
     * @param   string $p_selected_nodes
     *
     * @return  boolean
     * @throws  isys_exception_general
     */
    public function init($p_name, $p_datasource, $p_icon, $p_caption, $p_selected_nodes)
    {
        global $g_dirs;

        if (!empty($p_name)) {
            $this->m_tree_name = $p_name;

            $this->m_tree_output = "var $p_name = new eTree('$p_name', '$p_datasource', '$p_icon', '$p_caption', '$p_selected_nodes', '" . $g_dirs['images'] .
                "dtree/', 'mouse-pointer node');";

            return true;
        }

        throw new isys_exception_general("Could not init tree!");
    }

    /**
     * Processes the tree and returns it as string. Opens node specified by $p_opennode.
     *
     * @return  string
     */
    public function process()
    {
        return $this->m_tree_output;
    }

    /**
     * Initializes a tree with the given $p_name. The name has to be unique since it's used in JavaScript.
     *
     * @param  string $p_name
     * @param  string $p_datasource
     * @param  string $p_icon
     * @param  string $p_caption
     * @param  string $p_selected_nodes
     */
    public function __construct($p_name, $p_datasource, $p_icon, $p_caption, $p_selected_nodes)
    {
        $this->init($p_name, $p_datasource, $p_icon, $p_caption, $p_selected_nodes);
    }
}