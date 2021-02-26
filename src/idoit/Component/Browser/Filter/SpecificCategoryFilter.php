<?php

namespace idoit\Component\Browser\Filter;

use idoit\Component\Browser\Filter;

/**
 * Class SpecificCategoryFilter
 *
 * @package idoit\Component\Browser\Filter
 */
class SpecificCategoryFilter extends Filter
{
    /**
     * Method for retrieving a specific category query condition by a provided parameter.
     *
     * @return string
     */
    public function getQueryCondition()
    {
        if (is_countable($this->parameter) && count($this->parameter)) {
            return ' AND isys_obj_type__isysgui_cats__id ' . $this->dao->prepare_in_condition($this->parameter) . ' ';
        }

        return '';
    }
}
