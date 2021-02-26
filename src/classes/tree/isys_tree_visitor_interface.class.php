<?php

/**
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface isys_tree_visitor_interface
{
    /**
     * @param   isys_tree $p_node
     *
     * @return  mixed
     */
    public function visit(isys_tree $p_node);
}