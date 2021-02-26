<?php

namespace idoit\Module\Cmdb\Search\Index\Data\Source\Category;

use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Search\Index\Data\Source\Config;
use idoit\Module\Search\Index\Document;
use idoit\Module\Search\Index\DocumentMetadata;
use idoit\Module\Search\Index\Engine\SearchEngine;
use isys_application;
use isys_tenantsettings;
use Symfony\Component\EventDispatcher\GenericEvent;

class isys_cmdb_dao_category_g_ip extends AbstractCategorySource
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
        $condition = '';

        if (!isys_tenantsettings::get('search.index.include_archived_deleted_objects', false)) {
            $condition = ' AND mainObject.isys_obj__status = '  . C__RECORD_STATUS__NORMAL;
        }

        if ($config->hasCategoryIds()) {
            $condition .= ' AND isys_catg_' . $this->categoryDao->get_category() . '_list__id IN (' . implode(', ', $config->getCategoryIds()) . ')';
        }

        $data = $this->categoryDao->get_data(
            null,
            ($config->hasObjectIds() ? $config->getObjectIds() : null),
            $condition
        )->__as_array();

        $this->eventDispatcher->dispatch(new GenericEvent($this, [
            'count'   => count($data),
            'context' => $this->categoryDao->get_category_const()
        ]), 'index.data.raw.progress.retrieve');

        $property = $this->categoryDao->get_property_by_key('aliases');

        /**
         * @var $propertySelect SelectSubSelect
         */
        $propertySelect = $property[C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT];

        foreach ($data as &$row) {
            $row['aliases'] = $this->database->retrieveArrayFromResource($this->database->query($propertySelect->getSelectQuery() .
                " WHERE isys_catg_ip_list.isys_catg_ip_list__id = " . $row[$property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]]));
        }

        return $data;
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
        $language = isys_application::instance()->container->get('language');
        $documents = parent::mapDataToDocuments($data);

        $overallCount = 0;

        foreach ($data as $set) {
            // Two documents for each set
            $overallCount += 2;

            foreach ($set['aliases'] as $alias) {
                // One document for each alias
                $overallCount++;
            }
        }

        $this->eventDispatcher->dispatch(new GenericEvent($this, [
            'count'        => count($data),
            'countOverall' => $overallCount,
            'context'      => '"' . $language->get($this->categoryDao->getCategoryTitle()) . '" (' . $this->categoryDao->get_category_const() . ')'
        ]), 'index.data.document.mapping.progress.start');

        foreach ($data as $set) {
            $steps = 2;

            $metadata = new DocumentMetadata(
                get_class($this->categoryDao),
                $this->getIdentifier(),
                $set['isys_obj__isys_obj_type__id'],
                $set['isys_obj__id'],
                $set['isys_obj__status'],
                $this->categoryDao->getCategoryTitle(),
                $set['isys_catg_ip_list__id'],
                $set['isys_catg_ip_list__status'],
                'LC__CATG__IP_ADDRESS'
            );

            $document = new Document($metadata);
            $document->setVersion(SearchEngine::VERSION);
            $document->setType('cmdb');
            $document->setKey($metadata->__toString());
            $document->setValue($set['isys_cats_net_ip_addresses_list__title']);
            $document->setReference($set['isys_obj__id']);

            $documents[$document->getKey()] = $document;

            $metadata = new DocumentMetadata(
                get_class($this->categoryDao),
                $this->getIdentifier(),
                $set['isys_obj__isys_obj_type__id'],
                $set['isys_obj__id'],
                $set['isys_obj__status'],
                $this->categoryDao->getCategoryTitle(),
                $set['isys_catg_ip_list__id'],
                $set['isys_catg_ip_list__status'],
                'LC__CATP__IP__HOSTNAME'
            );

            $document = new Document($metadata);
            $document->setVersion(SearchEngine::VERSION);
            $document->setType('cmdb');
            $document->setKey($metadata->__toString());
            $document->setValue($set['isys_catg_ip_list__hostname'] . '.' . $set['isys_catg_ip_list__domain']);
            $document->setReference($set['isys_obj__id']);

            $documents[$document->getKey()] = $document;

            foreach ($set['aliases'] as $index => $alias) {
                // Create metadata for alias document
                $metadata = new DocumentMetadata(
                    get_class($this->categoryDao),
                    $this->getIdentifier(),
                    $set['isys_obj__isys_obj_type__id'],
                    $set['isys_obj__id'],
                    $set['isys_obj__status'],
                    $this->categoryDao->getCategoryTitle(),
                    $set['isys_catg_ip_list__id'],
                    $set['isys_catg_ip_list__status'],
                    'LC__CATG__IP__ALIASES.' . $index
                );

                $document = new Document($metadata);
                $document->setVersion(SearchEngine::VERSION);
                $document->setType('cmdb');
                $document->setKey($metadata->__toString());
                // @see  ID-5799  We had to change the query and could not use the alias.
                $document->setValue($alias['CONCAT(isys_hostaddress_pairs__hostname, ".", isys_hostaddress_pairs__domain)']);
                $document->setReference($set['isys_obj__id']);

                $documents[$document->getKey()] = $document;
                $steps++;
            }

            $this->eventDispatcher->dispatch(new GenericEvent($this, [
                'steps' => $steps
            ]), 'index.data.document.mapping.progress.advance');
        }

        $this->eventDispatcher->dispatch(new GenericEvent($this), 'index.data.document.mapping.progress.finish');

        return $documents;
    }
}
