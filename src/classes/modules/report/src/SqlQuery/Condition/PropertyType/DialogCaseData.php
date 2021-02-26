<?php

namespace idoit\Module\Report\SqlQuery\Condition\PropertyType;

use idoit\Component\Property\Property;
use idoit\Module\Report\SqlQuery\Condition\ConditionType;
use idoit\Module\Report\SqlQuery\Condition\ConditionTypeInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectCondition;

/**
 * @package     i-doit
 * @subpackage  Core
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class DialogCaseData extends ConditionType implements ConditionTypeInterface
{
    /**
     * @return bool
     */
    public function isApplicable()
    {
        $property = $this->getProperty();
        $query = $property->getData()->getSelect();
        $uiParams = $property->getUi()->getParams();

        return (isset($uiParams['p_arData']) && strpos($query, 'CASE') !== false && $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__DIALOG);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function format()
    {
        $db = \isys_application::instance()->container->get('database');
        $property = $this->getProperty();
        $arData = $property->getUi()->getParams()['p_arData'];
        $conditionField = $this->getConditionField();
        $conditionComparison = $this->getConditionComparison();
        $conditionValue = $this->getConditionValue();
        $conditionData = $this->getConditionData();

        $selectObject = clone $property->getData()->getSelect();
        $selectJoins = $property->getData()->getJoins();
        $query = $selectObject->getSelectQuery();
        list($alias, $field) = explode('.', $conditionField);
        $selectCondition = [];

        $condition = $conditionField . ' ' . $conditionComparison . ' \'' . $db->escape_string($conditionValue) . '\'';

        if (empty($conditionValue)) {
            $conditionValue = (int)$conditionValue;
            if ($conditionComparison === '=') {
                $condition .= ' OR ' . $conditionField . ' IS NULL';
            }

            if ($conditionComparison === '!=') {
                $condition .= ' AND ' . $conditionField . ' IS NOT NULL';
            }
        }

        if (strpos($conditionField, $selectObject->getSelectPrimaryKey()) !== false) {
            $selectCondition[] = $selectObject->getSelectPrimaryKey() . ' = ' . $conditionField;
        }

        if (strpos($conditionField, $selectObject->getSelectFieldObjectID()) !== false) {
            $selectCondition[] = $selectObject->getSelectFieldObjectID() . ' = ' . $conditionField;
        }

        if (\isys_cmdb_dao::instance($db)->fieldsExistsInTable((!empty($selectObject->getSelectTable()) ? $selectObject->getSelectTable() : $property->getData()->getSourceTable()), [$selectObject->getSelectTable() . '__status'])) {
            $selectCondition[] = (!empty($selectCondition) ? ' AND ' : '') . $selectObject->getSelectTable() . '__status = \'' . $db->escape_string(C__RECORD_STATUS__NORMAL) . '\'';
        }

        // Special case if $conditionField is like primary key or object id field from the select
        if (count($selectCondition) > 0 && is_countable($selectJoins) && count($selectJoins) > 1) {
            $language = \isys_application::instance()->container->get('language');

            if ($arData instanceof \isys_callback) {
                $arData = $arData->execute(new \isys_request());
            }

            $cases = [];
            foreach ($arData as $id => $value) {
                if ($conditionValue == $id) {
                    $conditionValue = '\'' . $db->escape_string($id) . '\',\'' . $db->escape_string($language->get($value)) . '\'';
                    break;
                }
            }

            $query = $language->get_in_text($query);
            $selectObject->setSelectQuery($query);
            $selectObject->setSelectCondition(SelectCondition::factory($selectCondition));

            $conditionComparison = ($conditionComparison === '=' ? 'IN': 'NOT IN');

            $condition = '(' . $selectObject . ') ' . $conditionComparison . ' (' . $conditionValue . ')';
        }

        return $condition;
    }
}
