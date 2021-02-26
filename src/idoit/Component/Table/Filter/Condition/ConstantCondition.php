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
 * Always returns the constant value
 *
 * @package idoit\Component\Table\Filter\Condition
 */
class ConstantCondition implements ConditionInterface
{
    /**
     * @var bool
     */
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @param        $name
     * @param string $value
     *
     * @return bool
     */
    public function check($name, $value)
    {
        return $this->value;
    }
}
