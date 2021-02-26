<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Component\Table\Filter\Operation;

use idoit\Component\Table\Filter\Condition\ConditionInterface;
use idoit\Component\Table\Filter\Formatter\FormatterInterface;
use isys_cmdb_dao_list_objects;

/**
 * Provides basic functionality of operation: formats for column and value + conditions
 *
 * @package idoit\Component\Table\Filter
 */
abstract class Operation implements OperationInterface
{
    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * @var FormatterInterface
     */
    protected $columnFormatter;

    /**
     * @var ConditionInterface
     */
    protected $condition;

    /**
     * @param FormatterInterface $formatter
     *
     * @return Operation
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * @param FormatterInterface $formatter
     *
     * @return Operation
     */
    public function setColumnFormatter(FormatterInterface $formatter)
    {
        $this->columnFormatter = $formatter;

        return $this;
    }

    /**
     * @param ConditionInterface $condition
     *
     * @return Operation
     */
    public function setCondition(ConditionInterface $condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * Format the column name
     *
     * @param $name
     *
     * @return mixed
     */
    protected function formatColumn($name)
    {
        if (isset($this->columnFormatter)) {
            return $this->columnFormatter->format($name);
        }

        return $name;
    }

    /**
     * Format the value
     *
     * @param $value
     *
     * @return mixed
     */
    protected function formatValue($value)
    {
        if (isset($this->formatter)) {
            return $this->formatter->format($value);
        }

        return $value;
    }

    /**
     * Checks if the condition is met
     *
     * @param $filter
     *
     * @param $value
     *
     * @return bool
     */
    public function isApplicable($filter, $value)
    {
        if (isset($this->condition)) {
            return $this->condition->check($filter, $value);
        }

        return true;
    }

    /**
     * Formats the column and value and
     *
     * @param isys_cmdb_dao_list_objects $dao
     * @param                            $name
     * @param                            $value
     *
     * @return mixed
     */
    public function apply(isys_cmdb_dao_list_objects $dao, $name, $value)
    {
        $applicable = $this->isApplicable($name, $value);
        if (!$applicable) {
            return false;
        }
        $column = $this->formatColumn($name);
        $value = $this->formatValue($value);

        return $this->applyFormatted($dao, $column, $value);
    }

    /**
     * Apply the formatted values to the DAO object
     *
     * @param isys_cmdb_dao_list_objects $dao
     * @param                            $name
     * @param                            $value
     *
     * @return bool
     */
    abstract protected function applyFormatted(isys_cmdb_dao_list_objects $dao, $name, $value);
}
