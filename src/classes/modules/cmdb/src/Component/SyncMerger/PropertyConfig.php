<?php

namespace idoit\Module\Cmdb\Component\SyncMerger;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\SyncMerger\DataRetriever\ByDataset;
use idoit\Module\Cmdb\Component\SyncMerger\DataRetriever\ByExportHelper;
use idoit\Module\Cmdb\Component\SyncMerger\DataRetriever\ByReference;
use idoit\Module\Cmdb\Component\SyncMerger\DataRetriever\ByUiCallback;
use idoit\Module\Cmdb\Component\SyncMerger\DataRetriever\DataRetrieverInterface;

class PropertyConfig
{
    /**
     * @var DataRetrieverInterface|null
     */
    private $dataRetriever = null;

    /**
     * @param $property
     *
     * @return PropertyConfig
     */
    public static function instance($property)
    {
        $instance = new PropertyConfig();
        $instance->setDataRetriever($property);
        return $instance;
    }

    /**
     * @return DataRetrieverInterface
     */
    public function getDataRetriever(): DataRetrieverInterface
    {
        return $this->dataRetriever;
    }

    /**
     * @param Property $property
     */
    public function setDataRetriever(Property $property)
    {
        if (ByExportHelper::isApplicable($property)) {
            $this->dataRetriever = new ByExportHelper();
            return;
        }

        if (ByUiCallback::isApplicable($property)) {
            $this->dataRetriever = new ByUiCallback();
            return;
        }

        if (ByReference::isApplicable($property)) {
            $this->dataRetriever = new ByReference();
            return;
        }

        if (ByDataset::isApplicable($property)) {
            $this->dataRetriever = new ByDataset();
            return;
        }
    }
}
