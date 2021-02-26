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
 * Checks, if the value in the predefined list of names
 *
 * @package idoit\Component\Table\Filter\Condition
 */
class InArrayCondition implements ConditionInterface
{
    /**
     * @var array
     */
    private $allowedNames;

    /**
     * InArrayCondition constructor.
     *
     * @param array $allowedNames
     */
    public function __construct(array $allowedNames = [])
    {
        $this->allowedNames = $allowedNames;
    }

    /**
     * @param string $name
     * @param        $value
     *
     * @return bool
     */
    public function check($name, $value)
    {
        return in_array($name, $this->allowedNames);
    }
}
