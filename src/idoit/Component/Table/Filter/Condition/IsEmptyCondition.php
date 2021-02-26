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
 * Checks, if value is empty
 *
 * @package idoit\Component\Table\Filter\Condition
 */
class IsEmptyCondition implements ConditionInterface
{
    /**
     * @param        $name
     * @param        $value
     *
     * @return bool
     */
    public function check($name, $value)
    {
        return empty($value) && $value !== '0';
    }
}
