<?php

namespace idoit\Component\Browser\Filter;

use idoit\Component\Browser\Filter;

/**
 * Class ObjectTypeFilter
 *
 * @package idoit\Component\Browser\Filter
 */
class ObjectTypeFilter extends Filter
{
    /**
     * Method for retrieving a object type query condition by a provided parameter.
     *
     * @return string
     */
    public function getQueryCondition()
    {
        if (is_countable($this->parameter) && count($this->parameter)) {
            return ' AND isys_obj_type__id ' . $this->dao->prepare_in_condition($this->parameter) . ' ';
        }

        return '';
    }
}
