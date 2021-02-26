<?php
namespace idoit\Module\Multiedit\Component\Filter;

class CategoryFilter {

    /**
     * @var array
     */
    protected $objects = [];

    /**
     * @var array
     */
    protected $objectTypes = [];

    /**
     * @var array
     */
    protected $categories = [];

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @return array
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * @param array $objects
     */
    public function setObjects(array $objects)
    {
        $this->objects = $objects;
    }

    /**
     * @return array
     */
    public function getObjectTypes()
    {
        return $this->objectTypes;
    }

    /**
     * @param array $objectTypes
     */
    public function setObjectTypes(array $objectTypes)
    {
        $this->objectTypes = $objectTypes;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     */
    public function setCategories(array $categories)
    {
        $this->categories = $categories;
    }
}