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
 * Checks, if the value is a substring of the predefined line
 *
 * @package idoit\Component\Table\Filter\Condition
 */
class IsSubstringCondition implements ConditionInterface
{
    /**
     * @var string
     */
    private $line;

    public function __construct($line)
    {
        $this->line = $line;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function check($name, $value)
    {
        return strpos($name, $this->line) !== false;
    }
}
