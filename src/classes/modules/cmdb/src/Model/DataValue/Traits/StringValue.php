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

trait StringValue
{

    /**
     * @var string
     */
    protected $value = '';

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException('Value has to be of type "string".');
        }

        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }
}