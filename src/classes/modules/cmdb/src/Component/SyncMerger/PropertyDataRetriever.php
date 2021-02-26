<?php

namespace idoit\Module\Cmdb\Component\SyncMerger;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\SyncMerger\DataRetriever\DataRetrieverException;
use isys_cmdb_dao_category_g_contact;

class PropertyDataRetriever
{
    /**
     * @var string
     */
    private $propertyKey;

    /**
     * @var Property
     */
    private $property;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $currentData;

    /**
     * @var CategoryDataRetriever
     */
    private $categoryDataRetriever;

    /**
     * @var PropertyConfig
     */
    private $propertyConfig;

    /**
     * PropertyDataRetriever constructor.
     *
     * @param        $property
     * @param Config $config
     */
    public function __construct($property, Config $config)
    {
        if (is_array($property)) {
            $property = Property::createInstanceFromArray($property);
        }
        $this->property = $property;
        $this->config = $config;
    }

    /**
     * @param                        $propertyKey
     * @param array|Property         $property
     * @param PropertyConfig         $propertyConfig
     * @param array                  $currentData
     * @param CategoryDataRetriever  $categoryDataRetriever
     * @param Config                 $config
     *
     * @return PropertyDataRetriever
     */
    public static function instance($propertyKey, $property, PropertyConfig $propertyConfig, array $currentData, CategoryDataRetriever $categoryDataRetriever, Config $config)
    {
        $instance = new self($property, $config);
        $instance->propertyKey = $propertyKey;
        $instance->currentData = $currentData;
        $instance->categoryDataRetriever = $categoryDataRetriever;
        $instance->propertyConfig = $propertyConfig;

        return $instance;
    }

    /**
     * @return bool|float|int|mixed|string
     */
    public function retrieveDataForProperty()
    {
        if ($this->categoryDataRetriever->getCount() == 0) {
            // Set Default value for new entries
            $uiData = $this->property->getUi();
            $uiParams = $uiData->getParams();
            return is_scalar($uiParams['default']) ? $uiParams['default'] : $uiData->getDefault();
        }
        try {
            return $this->retrieveValue();
        } catch (DataRetrieverException $e) {
            $e->write_log();
        }

        return null;
    }

    /**
     * @return mixed|null
     * @throws \Exception
     */
    private function retrieveValue()
    {
        if ($this->propertyKey === 'contact' && $this->config->getCategoryDao() instanceof isys_cmdb_dao_category_g_contact) {
            return null;
        }

        $dataRetriever = $this->propertyConfig->getDataRetriever();

        if ($dataRetriever === null) {
            throw new DataRetrieverException('Dataretriever type could not be processed for property: ' . $this->propertyKey);
        }

        return $dataRetriever->retrieveValue(
            $this->property,
            $this->config->getProperties(),
            $this->categoryDataRetriever->getCategoryData($this->config->getDataId()),
            $this->currentData,
            $this->categoryDataRetriever->getRequestObject($this->config->getDataId())
        );
    }
}
