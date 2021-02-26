<?php

namespace idoit\Module\Cmdb\Component\SyncMerger;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Search\Index\Data\Source\Category\isys_cmdb_dao_category_g_custom_fields;
use isys_cmdb_dao_category;

class Config
{
    const CONFIG_DATA_ID    = 'data_id';
    const CONFIG_PROPERTIES = 'properties';
    const CONFIG_CREATE = 'create';
    const CONFIG_UPDATE = 'update';
    const PROPERTY_DEFINITION        = 'definition';
    const PROPERTY_DATARETRIEVERTYPE = 'retrieverType';

    /**
     * @var isys_cmdb_dao_category
     */
    private $categoryDao;

    /**
     * @var bool
     */
    private $multiValue = false;

    /**
     * @var bool
     */
    private $customCategory = false;

    /**
     * @var array
     */
    private $currentData;

    /**
     * @var int|null
     */
    private $dataId;

    /**
     * @var array
     */
    private $missingProperties;

    /**
     * @var int
     */
    private $objectId;

    /**
     * @var Property[]
     */
    private $properties;

    /**
     * Config constructor.
     *
     * @param isys_cmdb_dao_category $categoryDao
     * @param int                    $objectId
     * @param array                  $data
     */
    public function __construct(isys_cmdb_dao_category $categoryDao, int $objectId, array $data = [])
    {
        $this->categoryDao = $categoryDao;

        if ($categoryDao instanceof isys_cmdb_dao_category_g_custom_fields) {
            $this->customCategory = true;
        }

        $this->multiValue = $categoryDao->is_multivalued();

        $this->objectId = $objectId;

        if ($data[self::CONFIG_DATA_ID]) {
            $this->dataId = (int)$data[self::CONFIG_DATA_ID];
        }

        if ($data[self::CONFIG_PROPERTIES] && is_array($data[self::CONFIG_PROPERTIES])) {
            $this->currentData = $data[self::CONFIG_PROPERTIES];
        }
    }

    /**
     * @param isys_cmdb_dao_category $categoryDao
     * @param int                    $objectId
     * @param array                  $data
     *
     * @return Config
     */
    public static function instance(isys_cmdb_dao_category $categoryDao, int $objectId, array $data)
    {
        $config = new self($categoryDao, $objectId, $data);
        $config->properties = $categoryDao->get_properties();
        $config->mapMissingProperties();

        return $config;
    }

    /**
     * @param isys_cmdb_dao_category $categoryDao
     *
     * @return Config
     */
    public function setCategoryDao(isys_cmdb_dao_category $categoryDao)
    {
        $this->categoryDao = $categoryDao;

        return $this;
    }

    /**
     * @return array
     */
    public function getMissingProperties()
    {
        return $this->missingProperties;
    }

    /**
     * @return array
     */
    public function getCurrentData()
    {
        return $this->currentData;
    }

    /**
     * @param array $currentData
     *
     * @return Config
     */
    public function setCurrentData(array $currentData)
    {
        $this->currentData = $currentData;

        return $this;
    }

    /**
     * @return $this
     */
    private function mapMissingProperties()
    {
        foreach ($this->properties as $propertyKey => $propertyDefinition) {
            if (is_array($propertyDefinition)) {
                $this->properties[$propertyKey] = $propertyDefinition = Property::createInstanceFromArray($propertyDefinition);
            }

            if (!isset($this->currentData[$propertyKey]) && ($propertyDefinition->getProvides()
                        ->isImport() && $propertyDefinition->getProvides()
                        ->isExport())) {
                $this->missingProperties[$propertyKey] = [
                    self::PROPERTY_DEFINITION => $propertyDefinition,
                    self::PROPERTY_DATARETRIEVERTYPE => PropertyConfig::instance($propertyDefinition)
                ];
            }
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getDataId()
    {
        return $this->dataId;
    }

    /**
     * @return int|null
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @return isys_cmdb_dao_category
     */
    public function getCategoryDao()
    {
        return $this->categoryDao;
    }

    /**
     * @return bool
     */
    public function isCustomCategory()
    {
        return $this->customCategory;
    }

    /**
     * @return bool
     */
    public function isMultivalueCategory()
    {
        return $this->multiValue;
    }

    /**
     * @return Property[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param $dataId
     *
     * @return $this
     */
    public function setDataId($dataId)
    {
        $this->dataId = $dataId;
        return $this;
    }
}
