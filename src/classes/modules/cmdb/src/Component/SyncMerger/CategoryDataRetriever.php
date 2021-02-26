<?php

namespace idoit\Module\Cmdb\Component\SyncMerger;

use isys_request;

class CategoryDataRetriever
{

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $categoryData = [];

    /**
     * @var int
     */
    private $count = 0;

    /**
     * @var isys_request[]
     */
    private static $requestObjects = [];

    /**
     * CategoryDataRetriever constructor.
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
     * @return CategoryDataRetriever
     */
    public static function instance(Config $config)
    {
        $instance = new self($config);
        $instance->setData();
        $instance->setRequest();
        return $instance;
    }

    /**
     * @param null $dataId
     *
     * @return array
     */
    public function getCategoryData($dataId = null)
    {
        if ($dataId !== null) {
            return $this->categoryData[$dataId];
        }

        return $this->categoryData;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @return void
     */
    private function setRequest()
    {
        if ($this->count === 0) {
            return;
        }

        foreach ($this->getCategoryData() as $dataSetId => $categoryData) {
            $requestObject = new isys_request($categoryData);
            $requestObject->set_category_data_id($dataSetId);
            $requestObject->set_object_id($this->config->getObjectId());
            self::$requestObjects[$dataSetId] = $requestObject;
        }
    }

    /**
     * @param $dataId
     *
     * @return isys_request|null
     */
    public function getRequestObject($dataId = null)
    {
        if ($dataId !== null) {
            return self::$requestObjects[$dataId];
        }

        return null;
    }

    /**
     * Sets category data for the current data id, if its a single value category and data id is not set use object id to retrieve category data
     *
     * @return void
     */
    private function setData()
    {
        $dataSet = [];
        $objectId = $this->config->getObjectId();
        $dataId = $this->config->getDataId();

        if ($dataId !== null) {
            $result = $this->config->getCategoryDao()->get_data($dataId);
            if (is_countable($result) && count($result) > 0) {
                if ($this->config->isCustomCategory()) {
                    while ($currentData = $result->get_row()) {
                        $key = $currentData['isys_catg_custom_fields_list__field_type'] ===
                        'commentary' ? 'description' : $currentData['isys_catg_custom_fields_list__field_type'] . '_' .
                            $currentData['isys_catg_custom_fields_list__field_key'];
                        $dataSet[$dataId][$key] = $currentData;
                    }
                } else {
                    $dataSet[$dataId] = $result->get_row();
                }
            }
            $this->categoryData = $dataSet;
        }

        if ($objectId && !empty($objectId) && $this->config->isMultivalueCategory() === false) {
            $result = $this->config->getCategoryDao()->get_data(null, $objectId);
            $table = $this->config->getCategoryDao()->get_table();

            if (count($result)) {
                while ($row = $result->get_row()) {
                    $dataSet[$row[$table . '__id']] = $row;
                }
            }
            $this->categoryData = $dataSet;
        }

        $this->count = count($dataSet);

        if ($this->count === 1) {
            $this->config->setDataId(key($dataSet));
        }
    }
}
