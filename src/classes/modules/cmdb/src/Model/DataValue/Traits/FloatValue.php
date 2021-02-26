<?php

namespace idoit\Module\Cmdb\Model\DataValue\Traits;

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

trait FloatValue
{
    /**
     * @var double
     */
    protected $value;

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param float $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        if (!is_double($value) || !is_float($value)) {
            throw new \InvalidArgumentException('Value has to be of type "double" or "float".');
        }

        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return strval($this->value);
    }

}