<?php

namespace idoit\Component\Browser\Filter;

use idoit\Component\Browser\Filter;

/**
 * Class CmdbStatusFilter
 *
 * @package idoit\Component\Browser\Filter
 */
class CmdbStatusFilter extends Filter
{
    /**
     * Method for retrieving a CMDB-Status query condition by a provided parameter.
     *
     * @return string
     */
    public function getQueryCondition()
    {
        if (is_countable($this->parameter) && count($this->parameter)) {
            return ' AND isys_obj__isys_cmdb_status__id ' . $this->dao->prepare_in_condition($this->parameter) . ' ';
        }

        return '';
    }
}
