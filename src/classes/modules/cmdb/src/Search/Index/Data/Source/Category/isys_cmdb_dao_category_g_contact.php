<?php

namespace idoit\Module\Cmdb\Search\Index\Data\Source\Category;

use idoit\Module\Search\Index\Data\Source\Config;
use idoit\Module\Search\Index\Document;
use idoit\Module\Search\Index\DocumentMetadata;
use idoit\Module\Search\Index\Engine\SearchEngine;
use isys_application;
use isys_cmdb_dao_category_property;
use Symfony\Component\EventDispatcher\GenericEvent;

class isys_cmdb_dao_category_g_contact extends AbstractCategorySource
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
        $propertyIds = [];
        $condition = '';
        $categoryDao = $this->categoryDao;
        $properties = $categoryDao->get_properties();

        $selection = [
            'contact_object' => true,
            'role' => true
        ];
        $emptyArraySelection = (new \ArrayObject($selection))->getArrayCopy();

        $indexableProperties = [];

        foreach ($properties as $key => $property) {
            if (isset($selection[$key])) {
                $indexableProperties[$key] = $property;
            }
        }

        $query = 'SELECT isys_property_2_cat__id FROM isys_property_2_cat 
            WHERE isys_property_2_cat__cat_const = ' . $categoryDao->convert_sql_text($categoryDao->get_category_const()) . ' 
            AND isys_property_2_cat__prop_key IN (' . implode(',', array_map(function ($item) use ($categoryDao) {
            return $categoryDao->convert_sql_text($item);
        }, array_keys($indexableProperties))) . ')';

        $result = $this->categoryDao->retrieve($query);
        while ($row = $result->get_row()) {
            $propertyIds[] = $row['isys_property_2_cat__id'];
        }

        $propertyDao = new isys_cmdb_dao_category_property($this->categoryDao->get_database_component());
        $joins = $propertyDao->reset()->create_property_query_join($propertyIds);
        foreach ($joins as $key => $join) {
            if (strpos($join, 'JOIN isys_catg_contact_list') !== false) {
                $selection['isys_catg_contact_list__id'] = $key . '.isys_catg_contact_list__id';
                $selection['isys_catg_contact_list__status'] = $key . '.isys_catg_contact_list__status';
                continue;
            }

            if (strpos($join, 'JOIN isys_contact_tag') !== false) {
                $selection['role'] = $key . '.isys_contact_tag__title as role ';
                continue;
            }

            if (strpos($join, 'JOIN isys_obj') !== false) {
                $selection['contact_object'] = $key . '.isys_obj__title as contact_object';

                $selection['isys_obj__isys_obj_type__id'] = $key . '.isys_obj__isys_obj_type__id';
                $selection['isys_obj__id'] = $key . '.isys_obj__id';
                $selection['isys_obj__status'] = $key . '.isys_obj__status';
            }
        }

        if (empty(array_diff_assoc($selection, $emptyArraySelection))) {
            return [];
        }

        $query = 'SELECT ' . implode(',', $selection) . ' FROM isys_obj as obj_main ' . implode(' ', $joins);

        $query .= ' WHERE ' . substr($selection['contact_object'], 0, strpos($selection['contact_object'], ' as ')) . ' IS NOT NULL ';

        if ($config->hasObjectIds()) {
            $query .= ' AND obj_main.isys_obj__id IN (' . implode(',', $config->getObjectIds()) . ')';
        }

        $data = $categoryDao->retrieve($query)->__as_array();
        $amount = count($data);
        $this->eventDispatcher->dispatch(new GenericEvent($this, [
            'count'   => $amount,
            'context' => $categoryDao->get_category_const()
        ]), 'index.data.raw.progress.retrieve');

        if ($amount > 0) {
            return $data;
        }
        return [];
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
        $overallCount = 0;

        foreach ($data as $set) {
            if (isset($set['contact_object'])) {
                $overallCount++;
            }

            if (isset($set['role'])) {
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
                $set['isys_catg_contact_list__id'],
                $set['isys_catg_contact_list__status'],
                'LC__CMDB__CATG__GLOBAL_CONTACT'
            );

            $document = new Document($metadata);
            $document->setVersion(SearchEngine::VERSION);
            $document->setType('cmdb');
            $document->setKey($metadata->__toString());
            $document->setValue($set['contact_object']);
            $document->setReference($set['isys_obj__id']);

            $documents[$document->getKey()] = $document;

            $metadata = new DocumentMetadata(
                get_class($this->categoryDao),
                $this->getIdentifier(),
                $set['isys_obj__isys_obj_type__id'],
                $set['isys_obj__id'],
                $set['isys_obj__status'],
                $this->categoryDao->getCategoryTitle(),
                $set['isys_catg_contact_list__id'],
                $set['isys_catg_contact_list__status'],
                'LC__CMDB__CONTACT_ROLE'
            );

            $document = new Document($metadata);
            $document->setVersion(SearchEngine::VERSION);
            $document->setType('cmdb');
            $document->setKey($metadata->__toString());
            $document->setValue($set['contact_object']);
            $document->setReference($set['isys_obj__id']);

            $documents[$document->getKey()] = $document;

            $this->eventDispatcher->dispatch(new GenericEvent($this, [
                'steps' => $steps
            ]), 'index.data.document.mapping.progress.advance');
        }

        $this->eventDispatcher->dispatch(new GenericEvent($this), 'index.data.document.mapping.progress.finish');

        return $documents;
    }
}
