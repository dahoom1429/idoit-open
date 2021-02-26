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
abstract class ConditionType
{
    /**
     * @var string
     */
    protected $conditionField;

    /**
     * @var string
     */
    protected $conditionComparison;

    /**
     * @var mixed
     */
    protected $conditionValue;

    /**
     * @var string
     */
    protected $conditionUnitField;

    /**
     * @var string
     */
    protected $conditionUnitId;

    /**
     * @var string
     */
    protected $conditionUnitFieldAlias;

    /**
     * @var array
     */
    protected $conditionData;

    /**
     * @var Property $property
     */
    protected $property;

    /**
     * @param $conditionField
     *
     * @return ConditionType
     */
    public function setConditionField($conditionField)
    {
        $this->conditionField = $conditionField;

        return $this;
    }

    /**
     * @return string
     */
    public function getConditionField()
    {
        return $this->conditionField;
    }

    /**
     * @param array $conditionData
     */
    public function setConditionData(array $conditionData)
    {
        $this->conditionData = $conditionData;
        return $this;
    }

    /**
     * @return array
     */
    public function getConditionData()
    {
        return $this->conditionData;
    }

    /**
     * @param Property $property
     */
    public function setProperty(Property $property)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * @return Property
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @return string
     */
    public function getConditionComparison()
    {
        return $this->conditionComparison;
    }

    /**
     * @param string $conditionComparison
     */
    public function setConditionComparison($conditionComparison)
    {
        $this->conditionComparison = $conditionComparison;
    }

    /**
     * @return mixed
     */
    public function getConditionValue()
    {
        return $this->conditionValue;
    }

    /**
     * @param mixed $conditionValue
     */
    public function setConditionValue($conditionValue)
    {
        $this->conditionValue = $conditionValue;
        return $this;
    }

    /**
     * @return string
     */
    public function getConditionUnitField()
    {
        return $this->conditionUnitField;
    }

    /**
     * @param string $conditionUnitField
     */
    public function setConditionUnitField($conditionUnitField)
    {
        $this->conditionUnitField = $conditionUnitField;
        return $this;
    }

    /**
     * @return string
     */
    public function getConditionUnitId()
    {
        return $this->conditionUnitId;
    }

    /**
     * @param string $conditionUnitId
     */
    public function setConditionUnitId($conditionUnitId)
    {
        $this->conditionUnitId = $conditionUnitId;
        return $this;
    }

    /**
     * @return string
     */
    public function getConditionUnitFieldAlias()
    {
        return $this->conditionUnitFieldAlias;
    }

    /**
     * @param string $conditionUnitFieldAlias
     */
    public function setConditionUnitFieldAlias($conditionUnitFieldAlias)
    {
        $this->conditionUnitFieldAlias = $conditionUnitFieldAlias;
        return $this;
    }
}
