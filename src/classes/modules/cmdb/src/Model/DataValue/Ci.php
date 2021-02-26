<?php

namespace idoit\Module\Cmdb\Model\DataValue;

use idoit\Module\Cmdb\Model\Ci as CiReference;

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
class Ci extends BaseValue implements DataValueInterface
{
    /**
     * @var CiReference
     */
    protected $value;

    /**
     * @return CiReference
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param CiReference $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        if (!is_a($value, '\idoit\Module\Cmdb\Model\Ci')) {
            throw new \InvalidArgumentException('Value has to be of type "\idoit\Module\Cmdb\Model\Ci".');
        }

        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value . '';
    }

    /**
     * Ci constructor.
     *
     * @param CiReference $value
     */
    public function __construct(CiReference $value)
    {
        $this->setValue($value);
    }
}