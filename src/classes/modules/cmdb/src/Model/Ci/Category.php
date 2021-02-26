<?php

namespace idoit\Module\Cmdb\Model\Ci;

use idoit\Module\Cmdb\Model\Ci\Category\Data;

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
class Category implements \ArrayAccess
{
    /**
     * Category constant
     *
     * @var string
     */
    public $const;

    /**
     * Array of the categories data
     *
     * @var Data[]
     */
    public $data = [];

    /**
     * isysgui_cat id of the category
     *
     * @var int
     */
    public $id;

    /**
     * Category identifier
     *
     * @var string
     */
    public $key;

    /**
     * Object id
     *
     * @var int
     */
    public $objectId;

    /**
     * Name of the category
     *
     * @var string
     */
    public $title;

    /**
     * The categories type
     *
     * @var int
     *
     * @enum C__CMDB__CATEGORY__TYPE_GLOBAL, C__CMDB__CATEGORY__TYPE_SPECIFIC, C__CMDB__CATEGORY__TYPE_CUSTOM
     */
    public $type;

    /**
     * Category factory.
     *
     * @param int    $id
     * @param int    $objectId
     * @param string $title
     * @param int    $type
     * @param string $const
     *
     * @return Category
     */
    public static function factory($objectId, $title, $type, $const, $categoryKey, $id = null)
    {
        $category = new self();

        $category->id = $id;
        $category->objectId = $objectId;
        $category->title = $title;
        $category->type = $type;
        $category->const = $const;
        $category->key = $categoryKey;

        return $category;
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return Data
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * @param Data $data
     * @param int  $index Leave null to just attach data on top of the data array
     *
     * @return $this
     */
    public function addData(Data $data, $index = null)
    {
        if ($index) {
            $this->data[$index] = $data;
        } else {
            $this->data[] = $data;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->title;
    }
}