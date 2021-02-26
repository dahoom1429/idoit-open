<?php

namespace idoit\Module\Report\SqlQuery\Condition\PropertyType;

use idoit\Module\Report\SqlQuery\Condition\ConditionType;
use idoit\Module\Report\SqlQuery\Condition\ConditionTypeInterface;
use idoit\Component\Property\Property;
use idoit\Module\Report\SqlQuery\Structure\SelectCondition;

/**
 * Condition type for multiselect
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Multiselect extends ConditionType implements ConditionTypeInterface
{
    /**
     * @return bool
     */
    public function isApplicable()
    {
        $uiParams = $this->getProperty()->getUi()->getParams();

        // @see ID-7549 otherwise the condition with checkboxes will not work properly
        if (isset($uiParams['popup']) && $uiParams['popup'] === 'checkboxes') {
            return false;
        }

        return $this->getProperty()->getInfo()->getType() == Property::C__PROPERTY__INFO__TYPE__MULTISELECT;
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    public function format()
    {
        $conditionQueryObject = clone $this->getProperty()->getData()->getSelect();
        $sourceTable = $this->getProperty()->getData()->getSourceTable();
        $conditionData = $this->getConditionData();
        $conditionComparison = $this->getConditionComparison();
        $conditionValue = $this->getConditionValue();
        $parentConditionField = $this->getConditionField();

        $conditionQuery = $conditionQueryObject->getSelectQuery();
        $conditionObjectField = $conditionQueryObject->getSelectFieldObjectID();
        $conditionField = $sourceTable . '__id';

        if ($conditionQueryObject->getSelectTable() === 'isys_catg_custom_fields_list') {
            $conditionField = 'isys_dialog_plus_custom__id';
        }

        preg_match('/(?<=SELECT ).[a-zA-Z0-9._]*/', $conditionQuery, $match);

        if (empty($match)) {
            return null;
        }

        $comparison = ($conditionComparison === '=' ?
            ($conditionValue === '-1' ? ' NOT IN ' : ' IN ') : ($conditionValue === '-1' ? ' IN ' : ' NOT IN '));
        $conditionQueryPattern = "{$parentConditionField} {$comparison} (%s)";

        $replaceField = $match[0];
        $conditionFieldAlias = substr($replaceField, 0, strpos($replaceField, '.'));
        $conditionQuery = str_replace($replaceField, $conditionObjectField, $conditionQuery);

        $conditionQueryObject->setSelectQuery($conditionQuery);

        if ($conditionValue === '-1') {
            $conditions[] = ' AND ' . ($conditionFieldAlias ? $conditionFieldAlias . '.' : '') . $conditionField . ' IS NOT NULL ';
        } else {
            $conditions[] = ' AND ' . ($conditionFieldAlias ? $conditionFieldAlias . '.' : '') . $conditionField . ' = \'' .
                \isys_application::instance()->container->get('database')->escape_string($conditionValue) . '\'';
        }


        $conditionQueryObject->setSelectCondition(SelectCondition::factory($conditions));
        $conditionQueryObject->setSelectGroupBy(null);
        return ' (' . sprintf($conditionQueryPattern, $conditionQueryObject) . ') ';
    }
}
