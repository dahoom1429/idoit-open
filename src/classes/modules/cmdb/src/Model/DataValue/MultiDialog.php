<?php

namespace idoit\Module\Cmdb\Model\DataValue;

/**
 * i-doit
 *
 * Ci Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class MultiDialog extends BaseValue implements DataValueInterface
{
    /**
     * @var array
     */
    protected $value;

    /**
     * @return array
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param array $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        if (!is_array($value)) {
            throw new \InvalidArgumentException('Value has to be of type "array".');
        }

        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return implode(', ', $this->value);
    }

    /**
     * Ci constructor.
     *
     * @param array $value
     */
    public function __construct(array $value)
    {
        $this->setValue($value);
    }
}