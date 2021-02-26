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
class GlobalApplicationVariant extends ConditionType implements ConditionTypeInterface
{
    /**
     * @return bool
     */
    public function isApplicable()
    {
        $property = $this->getProperty();
        return ($property->getData()->getField() === 'isys_catg_application_list__isys_cats_app_variant_list__id');
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function format()
    {
        $property = $this->getProperty();
        $conditionField = $this->getConditionField();
        $conditionValue = $this->getConditionValue();
        $conditionComparison = $this->getConditionComparison();
        $db = \isys_application::instance()->container->get('database');

        $conditionValue = "'{$db->escape_string($conditionValue)}'";

        if (strpos($conditionComparison, 'NULL') !== false) {
            unset($conditionValue);
        }

        return '(' . str_replace('__title', '__id', $conditionField) . " {$conditionComparison} {$conditionValue})";
    }
}
