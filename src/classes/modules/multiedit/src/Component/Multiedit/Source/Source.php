<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Source;

/**
 * Class Source
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Source
 */
abstract class Source
{

    /**
     * @var
     */
    protected $data;

    /**
     * @var
     */
    protected $dao;

    /**
     * @var int
     */
    protected $count = 0;

    /**
     * @return mixed
     */
    abstract public function formatData();

    /**
     * @return int
     */
    public function count()
    {
        return $this->count;
    }

    /**
     *
     */
    public function incrementCount()
    {
        $this->count++;
    }

    /**
     *
     */
    public function decrementCount()
    {
        $this->count--;
    }

    /**
     * @return mixed
     */
    public function getDao()
    {
        return $this->dao;
    }

    /**
     * @param mixed $dao
     *
     * @return Source
     */
    public function setDao($dao)
    {
        $this->dao = $dao;

        return $this;
    }

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
     * @return Source
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
