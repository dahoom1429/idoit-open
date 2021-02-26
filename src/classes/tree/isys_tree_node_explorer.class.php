<?php

/**
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_tree_node_explorer extends isys_tree_node implements isys_tree_node_interface
{
    /**
     *
     * @return  integer
     */
    public function get_id()
    {
        return (int)$this->m_data['id'];
    }

    /**
     *
     * @param   string $p_orn
     *
     * @return  isys_tree_node_explorer
     */
    public function set_orientation($p_orn)
    {
        $this->m_data['data']["\$orn"] = $p_orn;

        return $this;
    }

    /**
     *
     * @param   string $p_name
     *
     * @return  isys_tree_node_explorer
     */
    public function set_name($p_name)
    {
        $this->m_data['name'] = $p_name;

        return $this;
    }

    /**
     *
     * @param   integer $p_subnode_count
     *
     * @return  isys_tree_node_explorer
     */
    public function set_subnodes($p_subnode_count)
    {
        $this->m_data['data']['subNodes'] = $p_subnode_count;

        return $this;
    }
}