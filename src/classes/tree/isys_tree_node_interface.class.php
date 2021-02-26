<?php

/**
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface isys_tree_node_interface
{
    /**
     * Visitor pattern
     *
     * @param isys_tree_visitor_interface $p_visitor
     *
     * @return mixed
     */
    public function accept(isys_tree_visitor_interface $p_visitor);

    /**
     * Adds a new node to the tree. Data can contain any type of additional data for the node.
     *
     * @param   array $p_data
     *
     * @return  isys_tree_node
     */
    public function add(isys_tree_node $p_root_node);

    /**
     * Returns any node above (parent, grandparent, ...) this node
     *
     * @param bool $p_include_self
     *
     * @return isys_tree_node[] array of nodes, sorted by nearest
     */
    public function anscestors($p_include_self = false);

    /**
     * @param isys_tree_node $p_node
     *
     * @return isys_tree_node
     */
    public function copy_to(isys_tree_node $p_node);

    /**
     * Get all descendants of current node
     *
     * @param bool $includeSelf
     *
     * @return array
     */
    public function descendants($p_include_self = false);

    /**
     * Additional Data of the current node
     *
     * @return mixed
     */
    public function get_data();

    /**
     * Name of the current node
     *
     * @return string
     */
    public function get_name();

    /**
     * @return isys_tree_node
     */
    public function get_parent();

    /**
     * Checks for an existing child
     *
     * @return bool
     */
    public function has($p_node);

    /**
     * @return bool
     */
    public function has_children();

    /**
     * @param $parent
     *
     * @return bool
     */
    public function is_child_of(isys_tree_node $parent);

    /**
     * Current indentation level
     *
     * @return int
     */
    public function level();

    /**
     * @param isys_tree $p_node
     *
     * @return mixed
     */
    public function move_to(isys_tree_node $p_node);

    /**
     * Next child
     *
     * @return isys_tree_node
     */
    public function next();

    /**
     * Previous child
     *
     * @return isys_tree_node
     */
    public function prev();

    /**
     * Remove node
     *
     * @return mixed
     */
    public function remove();

    /**
     * Set parent (isys_tree_node)
     *
     * @param isys_tree_node $parent
     *
     * @return isys_tree_node
     */
    public function set_parent(isys_tree_node $parent = null);
}