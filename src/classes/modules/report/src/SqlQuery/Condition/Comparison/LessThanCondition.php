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
class LessThanCondition extends ConditionType implements ConditionTypeInterface
{
    /**
     * @return bool
     */
    public function isApplicable()
    {
        return (strpos($this->getConditionComparison(), '<') !== false);
    }

    /**
     * @return string
     */
    public function format()
    {
        $value = $this->getConditionValue();
        if ($value > 0 && is_numeric($value)) {
            $value = ($value === (int)$value) ? $value: (float)$value;
            $condition = ' ' . $this->getConditionField() . ' ' . $this->getConditionComparison() . ' ' . $value . ' ';
        } else {
            $condition = ' ' . $this->getConditionField() . ' ' . $this->getConditionComparison() . ' ' .
                \isys_application::instance()->container->get('cmdb_dao')->convert_sql_text($value) . ' ';
        }

        return $condition;
    }
}
