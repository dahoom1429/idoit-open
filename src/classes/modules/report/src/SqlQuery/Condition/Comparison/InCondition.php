<?php

namespace idoit\Module\Report\SqlQuery\Condition\Comparison;

use idoit\Module\Report\SqlQuery\Condition\ConditionType;
use idoit\Module\Report\SqlQuery\Condition\ConditionTypeInterface;

/**
 * @package     i-doit
 * @subpackage  Core
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class InCondition extends ConditionType implements ConditionTypeInterface
{
    /**
     * @return bool
     */
    public function isApplicable()
    {
        return (strpos($this->getConditionValue(), ',') !== false && $this->getConditionComparison() === '=');
    }

    /**
     * @return string
     */
    public function format()
    {
        if ($this->getConditionValue()) {
            $condition = ' ' . $this->getConditionField() . ' IN (' . $this->getConditionValue() . ') ';
        } else {
            $condition = ' ' . $this->getConditionField() . ' IS NULL ';
        }

        return $condition;
    }
}
