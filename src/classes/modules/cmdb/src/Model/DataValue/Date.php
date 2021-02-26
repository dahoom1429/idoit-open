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
class Date extends BaseValue implements DataValueInterface
{
    /**
     * @var \DateTime
     */
    protected $value = '';

    /**
     * @return \DateTime
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param \DateTime $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        if (!is_a($value, '\DateTime')) {
            throw new \InvalidArgumentException('Value has to be of type "\DateTime".');
        }

        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (is_object($this->value)) {
            return $this->value->format('Y-m-d');
        } else {
            return '';
        }
    }

    /**
     * DateTime constructor.
     *
     * @param \DateTime $value
     */
    public function __construct(\DateTime $value)
    {
        $this->setValue($value);
    }
}