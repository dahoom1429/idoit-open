<?php

namespace idoit\Module\Multiedit\Component\Multiedit;

use idoit\Module\Multiedit\Component\Multiedit\Config\Config;

/**
 * Class EditList
 *
 * @package idoit\Module\Multiedit\Component\Multiedit
 */
class EditList
{

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $list;

    /**
     * @var string
     */
    protected $header;

    /**
     * @return mixed
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param Config $config
     *
     * @return EditList
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return $this
     */
    public function init()
    {
        $categoryDao = $this->config->getDataSource()
            ->getDao();
        $properties = $this->config->getPropertySource()
            ->getData();
        $dataSource = $this->config->getDataSource();
        $objects = $this->config->getObjects();

        $objectId = null;
        if (count($objects) === 1) {
            foreach ($objects as $key => $object) {
                $objectId = $object['id'];
                break;
            }
        }

        $this->list = $this->config->getType()
            ->render($objects, $dataSource, $this->config->getPropertySource());
        $this->header = $this->config->getType()
            ->renderHeader($this->config->getPropertySource(), $objectId);

        return $this;
    }
}
