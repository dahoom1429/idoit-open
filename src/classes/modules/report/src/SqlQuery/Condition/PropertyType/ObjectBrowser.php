<?php

namespace idoit\Module\Report\SqlQuery\Condition\PropertyType;

use idoit\Component\Property\Property;
use idoit\Module\Report\SqlQuery\Condition\ConditionType;
use idoit\Module\Report\SqlQuery\Condition\ConditionTypeInterface;

/**
 * @package     i-doit
 * @subpackage  Core
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ObjectBrowser extends ConditionType implements ConditionTypeInterface
{
    /**
     * @return bool
     */
    public function isApplicable()
    {
        $property = $this->getProperty();
        return ($property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__OBJECT_BROWSER);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function format()
    {
        $conditionData = $this->getConditionData();
        $conditionField = $this->getConditionField();
        $conditionComparison = $this->getConditionComparison();
        $conditionValue = $this->getConditionValue();
        $db = \isys_application::instance()->container->get('database');

        $condition = $conditionField . ' ' . $conditionComparison . ' \'' . $db->escape_string($conditionValue) . '\' ';

        if (strpos($conditionComparison, 'PLACEHOLDER.') !== false) {
            return '(' . $condition . ')';
        }

        if ($conditionComparison === 'IS NULL' || $conditionComparison === 'IS NOT NULL') {
            $condition = '';
        }

        if (empty($condition) || (int)$conditionValue === 0 || $conditionValue == '-1') {
            if ($conditionComparison === '=') {
                $conditionComparison = 'IS NULL';
                $condition .= ' OR ';
            }
            if ($conditionComparison === '!=') {
                $conditionComparison = 'IS NOT NULL';
                $condition .= ' AND ';
            }

            $condition .= $conditionField . ' ' . $conditionComparison;
        }

        return '(' . $condition . ')';
    }
}
