<?php

namespace idoit\Module\Cmdb\Component\Browser\Filter;

use idoit\Component\Browser\Filter;
use isys_auth_cmdb_objects;

/**
 * Class AuthFilter
 *
 * @package idoit\Module\Cmdb\Component\Browser\Filter
 */
class AuthFilter extends Filter
{
    /**
     * Method for retrieving a CMDB-Status query condition by a provided parameter.
     *
     * @return string
     */
    public function getQueryCondition()
    {
        return isys_auth_cmdb_objects::instance()->get_allowed_objects_condition();
    }
}
