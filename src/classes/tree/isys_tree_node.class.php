<?php

/**
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_tree_node extends isys_tree implements isys_tree_node_interface
{

    /**
     * Internal tree data
     *
     * @var  isys_array[]
     */
    protected $m_data;

    /**
     * Parent node
     *
     * @var isys_tree_node
     */
    protected $m_parent;

    /**
     * @return array
     */
    public function get_neighbors()
    {
        $l_neighbors = $this->get_parent()
            ->get_childs();
        $l_current = $this;

        // Uses array_values to reset indexes after filter.
        return array_values(array_filter($l_neighbors, function ($p_child) use ($l_current) {
            return $p_child != $l_current;
        }));
    }

    /**
     * @param array $p_data
     *
     * @return $this
     */
    public function merge_data(array $p_data)
    {
        if ($this->m_data) {
            $this->m_data = new isys_array(array_merge_recursive($this->m_data->toArray(), $p_data));
        } else {
            $this->m_data = $p_data;
        }

        return $this;
    }

    /**
     * Return first node
     *
     * @return isys_tree_node
     */
    public function first_node()
    {
        return !empty($this->m_children) ? $this->m_children[0] : null;
    }

    /**
     * Return last node
     *
     * @return isys_tree_node
     */
    public function last_node()
    {
        return !empty($this->m_children) ? $this->m_children[count($this->m_children) - 1] : null;
    }

    /**
     * Removes a node at index $p_index and returns that node
     *
     * @param int $p_index
     *
     * @return isys_tree_node
     */
    public function remove_at($p_index)
    {
        $l_node = null;

        if (isset($this->m_children[$p_index]) && $this->m_children[$p_index] instanceof isys_tree_node) {
            $l_node = $this->m_children[$p_index]->remove();
            $this->m_children = array_values($this->m_children);
        }

        return $l_node;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->m_data['name'];
    }

    /**
     * Adds a new subnode
     *
     * @param isys_tree_node $p_node
     *
     * @return isys_tree_node
     */
    public function add(isys_tree_node $p_node)
    {
        if ($p_node !== $this) {
            $p_node->set_parent($this);

            $this->m_children[] = $p_node;
        }

        return $this;
    }

    /**
     * Returns any node above (parent, grandparent, ...) this node
     *
     * @param bool $p_include_self
     *
     * @return isys_tree_node[] array of nodes, sorted by nearest
     */
    public function anscestors($p_include_self = false)
    {
        $l_ancestors = $p_include_self ? [$this] : [];

        if (null === $this->m_parent) {
            return $l_ancestors;
        }

        return array_merge($l_ancestors, $this->m_parent->anscestors(true));

        /**
         * alternative, maybe faster? :
         *
         * @todo benchmark
         *
         * $l_parents = array();
         * $l_node = $this;
         * while ($l_parent = $l_node->get_parent()) {
         * array_unshift($l_parents, $l_parent);
         * $l_node = $l_parent;
         * }
         *
         * return $l_parents;
         */
    }

    /**
     * @desc Next node
     * @return isys_tree_node
     */
    public function next()
    {
        $l_parent = $this->get_parent();

        if ($l_parent) {
            $l_index = $l_parent->indexOf($this);

            if ($l_index < ($l_parent->count() - 1)) {
                return $l_parent->offsetGet($l_index + 1);
            }
        }

        return null;
    }

    /**
     * Remove a node
     *
     * @param isys_tree_node $p_node
     *
     * @return $this
     */
    public function remove_node(isys_tree_node $p_node, $p_unused = true)
    {
        foreach ($this->m_children as $l_key => $l_child) {
            if ($p_node === $l_child) {
                unset($this->m_children[$l_key]);
            }
        }

        $p_node->set_parent(null);

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $l_return = [];

        if (is_array($this->m_children)) {
            /**
             * @var $l_node isys_tree_node
             */
            foreach ($this->m_children as $l_node) {
                $l_return[] = $l_node->get_data();

                if ($l_node->has_children()) {
                    $l_return[count($l_return) - 1]['children'] = $l_node->toArray();
                }

            }
        }

        return $l_return;
    }

    /**
     * Walk through children and calls function $p_funcname
     *
     * @param callable $p_funcname
     * @param mixed    $p_parameter
     */
    public function walk($p_funcname, $p_parameter = null)
    {
        foreach ($this->m_children as $l_node) {
            /**
             * @var $l_node isys_tree_node
             */
            call_user_func($p_funcname, $l_node, $p_parameter);
            if ($l_node->has_children()) {
                $l_node->walk($p_funcname, $p_parameter);
            }
        }
    }

    /**
     * Searches for a node namy by string $p_search
     *
     * @param string  $p_search
     * @param boolean $p_strict
     *
     * @return array
     */
    public function search($p_search, $p_strict = false)
    {
        $l_results = [];
        $l_nodeList = $this->all_nodes();

        foreach ($l_nodeList as $l_node) {
            $l_data = $l_node->get_name();
            if ($p_strict ? ($l_data === $p_search) : ($l_data == $p_search)) {
                $l_results[] = $l_node;
            }
        }

        return $l_results;
    }

    /**
     * Method for setting data, after construction.
     *
     * @param   array $p_data
     *
     * @return  isys_tree_node
     */
    public function set_data(array $p_data)
    {
        $this->m_data = new isys_array($p_data);

        return $this;
    }

    /**
     * @param $p_node
     *
     * @return isys_tree_node
     */
    public function copy_to(isys_tree_node $p_node)
    {
        $p_node->add(clone $this);

        return $this;
    }

    /**
     * Get all descendants of current node
     *
     * @param bool $p_include_self
     *
     * @return array
     */
    public function descendants($p_include_self = false)
    {
        $l_descendants = $p_include_self ? [$this] : [];

        /**
         * @var $l_childnode isys_tree_node
         */
        foreach ($this->m_children as $l_childnode) {
            $l_descendants[] = $l_childnode;

            if ($l_childnode->has_children()) {
                $l_descendants = array_merge($l_descendants, $l_childnode->descendants());
            }
        }

        return $l_descendants;
    }

    /**
     * Get data method.
     *
     * @param   string $p_item
     * @param   mixed  $p_default
     *
     * @return  isys_array|mixed
     */
    public function get_data($p_item = null, $p_default = null)
    {
        if ($p_item === null) {
            return $this->m_data;
        }

        if (isset($this->m_data[$p_item])) {
            return $this->m_data[$p_item];
        } else {
            return $p_default;
        }
    }

    /**
     * @return string
     */
    public function get_name()
    {
        return $this->m_data['name'] ?: '';
    }

    /**
     * @return isys_tree_node
     */
    public function get_parent()
    {
        return $this->m_parent;
    }

    /**
     * Checks for an existing child
     *
     * @param isys_tree_node $p_node
     *
     * @return bool
     */
    public function has($p_node)
    {
        return count(array_filter($this->m_children, function ($p_child) use ($p_node) {
            return $p_child === $p_node;
        })) > 0;
    }

    /**
     * @param $parent
     *
     * @return bool
     */
    public function is_child_of(isys_tree_node $parent)
    {
        return $this->m_parent === $parent;
    }

    /**
     * Current indentation level
     *
     * @return int
     */
    public function level()
    {
        $l_level = 0;
        $l_current = $this;

        while (method_exists($l_current, "get_parent")) {
            $l_level++;
            $l_current = $l_current->get_parent();
        }

        return $l_level;
    }

    /**
     * @param isys_tree $p_node
     */
    public function move_to(isys_tree_node $p_node)
    {
        $this->remove();
        $p_node->add($this);

        return $this;
    }

    /**
     * @desc Previous node
     * @return isys_tree_node
     */
    public function prev()
    {
        if ($this->m_parent) {
            $myIndex = $this->m_parent->indexOf($this);

            if ($myIndex > 0) {
                return $this->m_parent->offsetGet($myIndex - 1);
            }
        }

        return null;
    }

    /**
     * @return $this
     */
    public function remove()
    {
        if (is_object($this->m_parent)) {
            $this->m_parent->remove_node($this);
        }

        return $this;
    }

    /**
     * @param isys_tree_node $parent
     *
     * @return isys_tree_node
     */
    public function set_parent(isys_tree_node $parent = null)
    {
        $this->m_parent = $parent;

        return $this;
    }

    /**
     * @param array $p_data
     */
    function __construct(array $p_data)
    {
        $this->m_data = new isys_array($p_data);
        $this->m_children = [];
        $this->m_parent = null;
    }
}