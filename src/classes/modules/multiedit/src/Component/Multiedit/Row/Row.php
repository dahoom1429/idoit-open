<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Row;

use idoit\Module\Multiedit\Component\Multiedit\Source\PropertiesSource;

/**
 * Class Row
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Row
 */
abstract class Row
{
    /**
     * @var
     */
    protected $data;

    /**
     * @var
     */
    protected $properties;

    /**
     * @var
     */
    protected $objectId;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var array
     */
    protected $objectData;

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     *
     * @return Row
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return PropertiesSource
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param PropertiesSource $properties
     *
     * @return Row
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Row
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param mixed $objectId
     *
     * @return Row
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * @param array $objectData
     *
     * @return Row
     */
    public function setObjectData($objectData)
    {
        $this->objectData = $objectData;

        return $this;
    }

    /**
     * @return array
     */
    public function getObjectData()
    {
        return $this->objectData;
    }
}
