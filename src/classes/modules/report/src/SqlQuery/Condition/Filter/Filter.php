<?php
namespace idoit\Module\Report\SqlQuery\Condition\Filter;

use idoit\Component\Property\Property;
use idoit\Module\Report\SqlQuery\Condition\ConditionType;

/**
 * @package     i-doit
 * @subpackage  Core
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Filter
{
    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $key;

    /**
     * @var AbstractFilterProcessor
     */
    private $processor;

    /**
     * @var ConditionType
     */
    private $condition;

    /**
     * @return ConditionType
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param ConditionType $condition
     *
     * @return Filter
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * @return AbstractFilterProcessor
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * @param AbstractFilterProcessor $processor
     *
     * @return Filter
     */
    public function setProcessor($processor)
    {
        $this->processor = $processor;

        return $this;
    }

    /**
     * @return string
     */
    public function process()
    {
        return $this->getProcessor()->process();
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $field
     *
     * @return Filter
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @return Filter
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }
}
