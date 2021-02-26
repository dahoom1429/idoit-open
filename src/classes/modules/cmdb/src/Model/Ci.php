<?php

namespace idoit\Module\Cmdb\Model;

use idoit\Module\Cmdb\Model\Ci\Category;

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
class Ci
{
    /**
     * @var Category[]
     */
    public $categoryData = [];

    /**
     * @var string
     */
    public $createdBy = '';

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $sysid;

    /**
     * @var string
     */
    public $title;

    /**
     * @var CiType
     */
    public $type;

    /**
     * @var string
     */
    public $updatedBy = '';

    /**
     * Handle different Ci states. E.g. for diffing changes.
     *
     * @var Ci
     */
    //protected $state;

    /**
     * @param string $title
     * @param CiType $type
     * @param string $sysid
     *
     * @return Ci
     */
    public static function factory($id, $title, CiType $type, $sysid)
    {
        $object = new static();
        $object->id = $id;
        $object->title = $title;
        $object->type = $type;
        $object->sysid = $sysid;

        return $object;
    }

    /**
     * Return categorydata
     *
     * @return Category[]
     */
    public function getData()
    {
        return $this->categoryData;
    }

    /**
     * Return categorydata
     *
     * @param int $index
     */
    public function getDataByIndex($index)
    {
        return $this->categoryData[$index] ?: null;
    }

    /**
     * @param Category $category
     *
     * @return $this
     */
    public function addData(Category $category)
    {
        $this->categoryData[] = $category;

        return $this;
    }

    /**
     * Flatten the object structure
     *
     * @return array
     */
    public function flatten($withDynamicProperties = false)
    {
        $array = [
            'id'    => $this->id,
            'title' => $this->title,
            'type'  => $this->type->title,
            'data'  => []
        ];

        foreach ($this->getData() as $data) {
            foreach ($data->data as $index => $categories) {
                foreach ($categories->getDataValues() as $key => $value) {
                    if ($withDynamicProperties || $key[0] !== '_') {
                        $array['data'][$this->type->id . '.' . $data->key . '.' . $index . '.' . $key] = trim($value);
                    }
                }
            }
        }

        return $array;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->type . ': ' . $this->title;
    }
}