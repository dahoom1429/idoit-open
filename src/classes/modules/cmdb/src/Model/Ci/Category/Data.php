<?php

namespace idoit\Module\Cmdb\Model\Ci\Category;

use idoit\Module\Cmdb\Model\DataValue\BaseValue;

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
class Data implements \JsonSerializable, \ArrayAccess, \Iterator
{
    /**
     * Array of category values
     *
     * @var BaseValue[]
     */
    private $dataValues;

    /**
     * @param BaseValue[] $values
     */
    public static function factory($values)
    {
        $data = new Data();
        $data->setDataValues($values);

        return $data;
    }

    /**
     * Iterator current()
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->dataValues);
    }

    /**
     * Iterator key()
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->dataValues);
    }

    /**
     * Iterator next()
     *
     * @return $this
     */
    public function next()
    {
        return next($this->dataValues);
    }

    /**
     * Iterator rewind()
     */
    public function rewind()
    {
        reset($this->dataValues);
    }

    /**
     * Iterator valid()
     */
    public function valid()
    {
        return $this->current() !== false;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->dataValues[$name]);
    }

    /**
     * @param $name
     *
     * @return BaseValue
     */
    public function __get($name)
    {
        return $this->dataValues[$name];
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->dataValues[$name] = $value;
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->dataValues[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return Data
     */
    public function offsetGet($offset)
    {
        return $this->dataValues[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->dataValues[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->dataValues[$offset]);
    }

    /**
     * @return BaseValue[]
     */
    public function getDataValues()
    {
        return $this->dataValues;
    }

    /**
     * @param $dataValues
     */
    public function setDataValues($dataValues)
    {
        $this->dataValues = $dataValues;
    }

    /**
     * @return BaseValue[]
     */
    public function jsonSerialize()
    {
        return $this->dataValues;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize($this->dataValues);
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        $this->dataValues = unserialize($data);
    }
}