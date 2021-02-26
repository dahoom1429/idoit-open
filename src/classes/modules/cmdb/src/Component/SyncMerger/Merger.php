<?php

namespace idoit\Module\Cmdb\Component\SyncMerger;

class Merger
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $syncData = [];

    /**
     * Merger constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param Config $config
     *
     * @return Merger
     */
    public static function instance(Config $config)
    {
        $merger = new self($config);
        $merger->buildSyncData();
        return $merger;
    }

    /**
     * @return array
     */
    public function getDataForSync()
    {
        if (empty($this->syncData)) {
            $this->buildSyncData();
        }
        return $this->syncData;
    }

    /**
     * Build Sync Data
     *
     * @return void
     */
    private function buildSyncData()
    {
        $missingProperties = $this->config->getMissingProperties();

        if (empty($missingProperties)) {
            // Nothing to build sync data is already complete
            $this->syncData = [
                Config::CONFIG_DATA_ID => $this->config->getDataId(),
                Config::CONFIG_PROPERTIES => $this->config->getCurrentData()
            ];
            return;
        }
        $currentData = $this->config->getCurrentData();

        $categtoryDataRetriever = CategoryDataRetriever::instance($this->config);

        foreach ($missingProperties as $propertyKey => $propertyInfo) {
            $propertyDefinition = $propertyInfo[Config::PROPERTY_DEFINITION];
            $propertyDataRetrieverType = $propertyInfo[Config::PROPERTY_DATARETRIEVERTYPE];
            $propertyRetriever = PropertyDataRetriever::instance($propertyKey, $propertyDefinition, $propertyDataRetrieverType, $currentData, $categtoryDataRetriever, $this->config);

            $currentData[$propertyKey] = $propertyRetriever->retrieveDataForProperty();
        }
        $newData = [];
        foreach ($currentData as $key => $value) {
            if (is_array($value) && (isset($value[C__DATA__VALUE]) || array_key_exists(C__DATA__VALUE, $value))) {
                $newData[$key] = $value;
                continue;
            }

            $newData[$key] = [
                C__DATA__VALUE => !empty($value) ? $value : null
            ];
        }

        $this->syncData = [
            Config::CONFIG_DATA_ID => $this->config->getDataId(),
            Config::CONFIG_PROPERTIES => $newData
        ];
    }
}
