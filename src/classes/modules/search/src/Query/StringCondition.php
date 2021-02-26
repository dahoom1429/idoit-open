<?php

namespace idoit\Module\Search\Query;

/**
 * i-doit
 *
 * Search index string condition
 *
 * @package     idoit\Module\Search\Index
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class StringCondition extends Condition
{

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->keyword;
    }

}