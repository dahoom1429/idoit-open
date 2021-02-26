<?php

namespace idoit\Module\Report\SqlQuery\Condition\PropertyType;

use idoit\Module\Report\SqlQuery\Condition\ConditionType;
use idoit\Module\Report\SqlQuery\Condition\ConditionTypeInterface;
use idoit\Component\Property\Property;
use idoit\Module\Report\SqlQuery\Structure\SelectCondition;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;

/**
 * Condition type for dialog list with p_arData instance of isys_callback
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class DialogList extends ConditionType implements ConditionTypeInterface
{
    /**
     * @return bool
     */
    public function isApplicable()
    {
        $property = $this->getProperty();
        $uiParams = $property->getUi()->getParams()['p_arData'];
        return ($property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__DIALOG_LIST && ($uiParams instanceof \isys_callback));
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function format()
    {
        $property = $this->getProperty();
        $conditionData = $this->getConditionData();
        $conditionComparison = $this->getConditionComparison();
        $conditionValue = $this->getConditionValue();
        $parentConditionField = $this->getConditionField();
        $db = \isys_application::instance()->container->get('database');

        $conditionQueryObject = clone $property->getData()->getSelect();
        $conditionQueryJoinsObject = $property->getData()->getJoins();
        $sourceTable = $property->getData()->getSourceTable();

        $conditionQuery = $conditionQueryObject->getSelectQuery();
        $conditionPrimaryKey = $conditionQueryObject->getSelectPrimaryKey();
        $conditionField = $sourceTable . '__title';

        preg_match('/(?<=SELECT )\X*(?= FROM)/', $conditionQuery, $match);

        if (empty($match)) {
            return null;
        }

        $comparison = ($conditionComparison === 'LIKE' ? ' IN ': ' NOT IN ');
        $conditionQueryPattern = "{$parentConditionField} {$comparison} (%s)";

        $replaceField = $match[0];
        $conditionQuery = str_replace($replaceField, $conditionPrimaryKey, $conditionQuery);
        $conditionQueryObject->setSelectQuery($conditionQuery);

        /**
         * @var $joinObject SelectJoin
         */
        $candidateAlias = null;
        foreach ($conditionQueryJoinsObject as $joinObject) {
            if ($joinObject->getTable() === $sourceTable) {
                $candidateAlias = $joinObject->getTableAlias();
            }
        }

        if ($candidateAlias) {
            $conditionField = $candidateAlias . '.' . $conditionField;
        }

        $conditions[] = " AND {$conditionField} LIKE '{$db->escape_string($conditionValue)}'";
        $conditionQueryObject->setSelectCondition(SelectCondition::factory($conditions));
        $conditionQueryObject->setSelectGroupBy(null);
        return ' (' . sprintf($conditionQueryPattern, $conditionQueryObject) . ') ';
    }
}
