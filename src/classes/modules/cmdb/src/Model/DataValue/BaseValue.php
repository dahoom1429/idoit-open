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
abstract class BaseValue implements DataValueInterface, \JsonSerializable
{
    /**
     * @var mixed
     */
    protected $value = '';

    /**
     * @param mixed $value
     *
     * @return static
     */
    public static function factory($value)
    {
        return new static($value);
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->value;
    }

    /**
     * DataValue constructor.
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->setValue($value);
    }
}