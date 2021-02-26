<?php

namespace idoit\Component\Browser\Filter;

use idoit\Component\Browser\Filter;

/**
 * Class NetTypeFilter
 *
 * @package idoit\Component\Browser\Filter
 */
class NetTypeFilter extends Filter
{
    /**
     * Method for retrieving a object type query condition by a provided parameter.
     *
     * @return string
     */
    public function getQueryCondition()
    {
        if (is_countable($this->parameter) && count($this->parameter)) {
            $subQuery = 'SELECT isys_cats_net_list__isys_obj__id 
                FROM isys_cats_net_list
                WHERE isys_cats_net_list__isys_net_type__id ' . $this->dao->prepare_in_condition($this->getParameter());

            return ' AND isys_obj__id IN (' . $subQuery . ') ';
        }

        return '';
    }
}
