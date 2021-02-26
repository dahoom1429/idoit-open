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

use idoit\Module\Cmdb\Model\Ci\Table\Config;
use idoit\Module\Cmdb\Model\Ci\Table\Property;

/**
 * Finds the type of column in table configuration and checks the inner condition with the column type
 *
 * @package idoit\Component\Table\Filter\Condition
 */
class TypeCondition implements ConditionInterface
{
    /**
     * @var ConditionInterface
     */
    private $innerCondition;

    /**
     * @var Config
     */
    private $tableConfig;

    /**
     * TypeCondition constructor.
     *
     * @param Config             $tableConfig    - table config to get the information about types
     * @param ConditionInterface $innerCondition - inner condition to check
     */
    public function __construct(Config $tableConfig, ConditionInterface $innerCondition)
    {
        $this->innerCondition = $innerCondition;
        $this->tableConfig = $tableConfig;
    }

    /**
     * @param string $name
     * @param        $value
     *
     * @return bool
     */
    public function check($name, $value)
    {
        foreach ($this->tableConfig->getProperties() as $i => $property) {
            if ($property->getPropertyKey() === $name) {
                return $this->innerCondition->check($property->getType(), $value);
            }
        }

        return false;
    }
}
