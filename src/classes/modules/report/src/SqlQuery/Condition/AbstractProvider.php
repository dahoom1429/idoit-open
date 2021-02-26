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
abstract class AbstractProvider extends ConditionType
{
    /**
     * Condition types
     *
     * @var ConditionType[]
     */
    protected $conditionTypes = [];

    /**
     * @param ConditionTypeInterface $conditionType
     *
     * @return $this
     */
    public function addConditionType(ConditionTypeInterface $conditionType)
    {
        $this->conditionTypes[] = $conditionType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function format()
    {
        foreach ($this->conditionTypes as $conditionType) {
            if (!($conditionType instanceof ConditionTypeInterface)) {
                continue;
            }

            $conditionType->setProperty($this->getProperty());
            $conditionType->setConditionData($this->getConditionData());
            $conditionType->setConditionField($this->getConditionField());
            $conditionType->setConditionValue($this->getConditionValue());
            $conditionType->setConditionUnitFieldAlias($this->getConditionUnitFieldAlias());
            $conditionType->setConditionUnitField($this->getConditionUnitField());
            $conditionType->setConditionUnitId($this->getConditionUnitId());
            $conditionType->setConditionComparison($this->getConditionComparison());

            if ($conditionType->isApplicable()) {
                return $conditionType->format();
            }
        }

        return null;
    }
}
