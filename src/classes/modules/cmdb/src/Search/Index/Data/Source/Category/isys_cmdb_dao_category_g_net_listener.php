<?php

namespace idoit\Module\Cmdb\Search\Index\Data\Source\Category;

use idoit\Module\Search\Index\Data\Source\Config;
use idoit\Module\Search\Index\Document;
use idoit\Module\Search\Index\DocumentMetadata;
use idoit\Module\Search\Index\Engine\SearchEngine;

class isys_cmdb_dao_category_g_net_listener extends AbstractCategorySource
{
    /**
     * Retrieve data for index creation
     *
     * @param Config $config
     *
     * @return array
     */
    public function retrieveData(Config $config)
    {
        return $this->categoryDao->get_data(
            null,
            (empty($config->getObjectIds()) ? null : $config->getObjectIds()),
            (empty($config->getCategoryIds()) ? '' : ' AND isys_catg_net_listener_list__id IN (' . implode(',', $config->getCategoryIds()) . ')')
        )->__as_array();
    }

    /**
     * Map data from retrieveData to Documents
     *
     * @param array $data
     *
     * @return Document[]
     */
    public function mapDataToDocuments(array $data)
    {
        $documents = [];

        foreach ($data as $set) {
            $metadata = new DocumentMetadata(
                get_class($this->categoryDao),
                $this->getIdentifier(),
                $set['isys_obj__isys_obj_type__id'],
                $set['isys_obj__id'],
                $set['isys_obj__status'],
                $this->categoryDao->getCategoryTitle(),
                $set['isys_catg_net_listener_list__id'],
                $set['isys_catg_net_listener_list__status'],
                'LC__CMDB__CATG__DESCRIPTION'
            );

            $document = new Document($metadata);
            $document->setVersion(SearchEngine::VERSION);
            $document->setType('cmdb');
            $document->setKey($metadata->__toString());
            $document->setValue(filter_var(html_entity_decode($set['isys_catg_net_listener_list__description']), FILTER_SANITIZE_STRING));
            $document->setReference($set['isys_obj__id']);

            $documents[$document->getKey()] = $document;
        }

        return $documents;
    }
}
