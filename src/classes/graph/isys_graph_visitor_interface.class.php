<?php

/**
 * @package    i-doit
 * @subpackage General
 * @author     Leonard Fischer <lfischer@i-doit.com>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface isys_graph_visitor_interface
{
    /**
     * @param   isys_graph $p_node
     *
     * @return  mixed
     */
    public function visit(isys_graph $p_node);
}