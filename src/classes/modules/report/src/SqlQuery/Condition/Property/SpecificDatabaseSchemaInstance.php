<?php

namespace idoit\Module\Report\SqlQuery\Condition\Property;

use idoit\Module\Report\SqlQuery\Condition\ConditionType;
use idoit\Module\Report\SqlQuery\Condition\ConditionTypeInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectCondition;

/**
 * Special condition for specific category database schema property instance
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class SpecificDatabaseSchemaInstance extends ConditionType implements ConditionTypeInterface
{
    /**
     * @return bool
     */
    public function isApplicable()
    {
        $property = $this->getProperty();
        return ($property->getData()->getField() === 'isys_cats_database_schema_list__isys_cats_db_instance_list__id');
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function format()
    {
        $property = $this->getProperty();
        $conditionData = $this->getConditionData();
        $conditionField = $this->getConditionField();
        $conditionValue = $this->getConditionValue();
        $conditionComparison = $this->getConditionComparison();
        $conditionQueryObject = clone $property->getData()->getSelect();
        $conditionQuery = $conditionQueryObject->getSelectQuery();
        $conditionObjectField = $conditionQueryObject->getSelectFieldObjectID();
        $db = \isys_application::instance()->container->get('database');

        $replaceField = substr($conditionQuery, strpos($conditionQuery, 'SELECT') + 7, strpos($conditionQuery, 'FROM') - 7);
        $connectCondition = ' OR ';
        $conditions = [];

        $conditionQuery = str_replace($replaceField, $conditionObjectField . ' ', $conditionQuery);
        $conditionQueryObject->setSelectQuery($conditionQuery);

        if ($conditionComparison === '!=' || strpos($conditionComparison, 'NOT') !== false) {
            $connectCondition = ' AND ';
        }

        $inCondition = ' IN ';

        if (strpos($conditionComparison, 'NULL') === false) {
            $conditions = [
                "rel.isys_obj__title " . $conditionComparison . " '" . $db->escape_string($conditionValue) . "'",
                " {$connectCondition} inst.isys_obj__title " . $conditionComparison . " '" . $db->escape_string($conditionValue) . "'"
            ];
        } else {
            $inCondition = (strpos($conditionComparison, 'NOT') !== false) ? $inCondition :' NOT IN ';
        }

        $conditionQueryObject->setSelectCondition(SelectCondition::factory($conditions));
        $conditionQueryObject->setSelectGroupBy(null);

        return '(' . str_replace('__title', '__id', $conditionField) . " {$inCondition} ({$conditionQueryObject}) )";
    }
}
