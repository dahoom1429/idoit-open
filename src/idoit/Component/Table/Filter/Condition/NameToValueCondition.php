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
 * Inverse the name and value of inner condition
 *
 * @package idoit\Component\Table\Filter\Condition
 */
class NameToValueCondition implements ConditionInterface
{
    /**
     * @var ConditionInterface
     */
    private $condition;

    /**
     * NotCondition constructor.
     *
     * @param ConditionInterface $condition inner condition
     */
    public function __construct(ConditionInterface $condition)
    {
        $this->condition = $condition;
    }

    /**
     * @param        $name
     * @param        $value
     *
     * @return bool
     */
    public function check($name, $value)
    {
        return $this->condition->check($value, $name);
    }
}
