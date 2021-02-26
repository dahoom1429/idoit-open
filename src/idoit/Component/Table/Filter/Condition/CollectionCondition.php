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

namespace idoit\Component\Table\Filter\Condition;

/**
 * Proceeds the validation through collection of conditions
 *
 * @package idoit\Component\Table\Filter\Condition
 */
class CollectionCondition implements ConditionInterface
{
    /**
     * Operation between the conditions. true = and, false = or
     *
     * @var bool
     */
    private $operation;

    /**
     * List of conditions
     *
     * @var array
     */
    private $conditions;

    /**
     * CollectionCondition constructor.
     *
     * @param array $conditions
     * @param bool  $operation - if true = and, false = or
     */
    public function __construct(array $conditions = [], $operation = true)
    {
        $this->conditions = $conditions;
        $this->operation = $operation;
    }

    /**
     * Validate the conditions for the value
     *
     * @param string $name
     *
     * @return bool
     */
    public function check($name, $value)
    {
        foreach ($this->conditions as $condition) {
            $fulfilled = $condition->check($name, $value);
            // is OR connection and condition is true - end with true
            if (!$this->operation && $fulfilled) {
                return true;
            }
            // is AND connection and condition is not true - end with false
            if ($this->operation && !$fulfilled) {
                return false;
            }
        }
        // if OR has never ended before - the condition was never true - end with false
        // if AND has never ended before - the condition was always true - end with true
        return $this->operation;
    }
}
