<?php
namespace idoit\Module\Report\SqlQuery\Condition;

use idoit\Component\Property\Property;

/**
 * @package     i-doit
 * @subpackage  Core
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class DefaultCondition extends ConditionType implements ConditionTypeInterface
{
    /**
     * @return bool
     */
    public function isApplicable()
    {
        return true;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function format()
    {
        $conditionField = $this->getConditionField();
        $conditionComparison = $this->getConditionComparison();
        $conditionValue = $this->getConditionValue() ?: '';
        $unitField = $this->getConditionUnitField();
        $unitId = $this->getConditionUnitId();
        $unitAlias = $this->getConditionUnitFieldAlias();
        $db = \isys_application::instance()->container->get('database');

        $condition = $conditionField . ' ' . $conditionComparison . ' ' .
            (($conditionComparison === 'IS NULL' || $conditionComparison === 'IS NOT NULL') ? '' : '\'' . $db->escape_string($conditionValue) . '\'') . ' ';

        if (isset($conditionValue) && $conditionValue === '-1') {
            if ($conditionComparison !== 'IS NULL' && $conditionComparison !== 'IS NOT NULL') {
                if ((int)$conditionValue === 0 || $conditionValue == '-1') {
                    if ($conditionComparison === '=') {
                        $l_comparison_addition = ' IS ';
                        $l_log_operator = ' OR ';
                    } else {
                        $l_comparison_addition = ' IS NOT ';
                        $l_log_operator = ' AND ';
                    }
                    $condition .= $l_log_operator . $conditionField . ' ' . $l_comparison_addition . ' NULL ';
                }
            }
        } else {
            switch ($conditionComparison) {
                case 'IS NULL':
                    $condition .= ' OR ' . $conditionField . ' = \'\' ';
                    break;
                case 'IS NOT NULL':
                    $condition .= ' AND ' . $conditionField . ' != \'\' ';
                    break;
                default:
                    break;
            }
        }

        $condition = '(' . $condition . ')';

        if ($unitField !== null) {
            $condition .= ' AND ' . $unitAlias . '.' . $unitField . ' = \'' . $db->escape_string($unitId) . '\' ';
        }

        return $condition;
    }
}
