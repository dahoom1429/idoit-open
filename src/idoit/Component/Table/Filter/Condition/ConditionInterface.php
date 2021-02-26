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
 * Checks if the value fulfill the condition
 *
 * @package idoit\Component\Table\Filter\Condition
 */
interface ConditionInterface
{
    /**
     * @param        $name
     * @param string $value
     *
     * @return bool
     */
    public function check($name, $value);
}
