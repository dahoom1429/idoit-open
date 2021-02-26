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
class NotNullCondition extends ConditionType implements ConditionTypeInterface
{
    /**
     * @return bool
     */
    public function isApplicable()
    {
        return (empty($this->getConditionValue()) && $this->getConditionComparison() === 'IS NOT NULL');
    }

    /**
     * @return string
     */
    public function format()
    {
        return ' (' . $this->getConditionField() . ' IS NOT NULL AND ' . $this->getConditionField() . ' != \'\') ';
    }
}
