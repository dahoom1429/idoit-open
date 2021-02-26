<?php

namespace idoit\Component\Browser\Filter;

use idoit\Component\Browser\Filter;

/**
 * Class GlobalCategoryFilter
 *
 * @package idoit\Component\Browser\Filter
 */
class GlobalCategoryFilter extends Filter
{
    /**
     * Method for retrieving a global category query condition by a provided parameter.
     *
     * @return string
     */
    public function getQueryCondition()
    {
        if (is_countable($this->parameter) && count($this->parameter)) {
            /** @noinspection SyntaxError */
            $subSelect = 'SELECT DISTINCT isys_obj_type_2_isysgui_catg__isys_obj_type__id 
                FROM isysgui_catg 
                INNER JOIN isys_obj_type_2_isysgui_catg ON isys_obj_type_2_isysgui_catg__isysgui_catg__id = isysgui_catg__id
                WHERE isysgui_catg__id ' . $this->dao->prepare_in_condition($this->parameter) . ' 
                OR isysgui_catg__parent ' . $this->dao->prepare_in_condition($this->parameter);

            return ' AND isys_obj_type__id IN (' . $subSelect . ') ';
        }

        return '';
    }
}
