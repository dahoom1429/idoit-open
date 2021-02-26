<?php

namespace idoit\Module\Cmdb\Search\Index\Data\Source\Category;

use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Search\Index\Data\Source\Config;
use idoit\Module\Search\Index\Document;
use idoit\Module\Search\Index\DocumentMetadata;
use idoit\Module\Search\Index\Engine\SearchEngine;
use isys_application;
use Symfony\Component\EventDispatcher\GenericEvent;

class isys_cmdb_dao_category_g_global extends AbstractCategorySource
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
        $data = parent::retrieveData($config);

        $property = $this->categoryDao->get_property_by_key('tag');

        /**
         * @var SelectSubSelect $select
         */
        $select = $property[C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT];

        foreach ($data as $key => &$row) {
            // Skip all complex properties
            if (!is_numeric($key)) {
                continue;
            }

            $tags = $this->database->retrieveArrayFromResource($this->database->query($select->getSelectQuery() . ' WHERE ' . $select->getSelectReferenceKey() . ' = \'' . $row['isys_obj__id'] . '\''));

            if (empty($tags)) {
                continue;
            }

            $tagTitles = [];

            foreach ($tags as $tag) {
                $tagTitles[] = $tag['isys_tag__title'];
            }

            $row['tag'] = $tagTitles;
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

        $property = $this->categoryDao->get_property_by_key('tag');

        $tagCount = 0;

        foreach ($data as $row) {
            if (empty($row['tag'])) {
                continue;
            }

            foreach ($row['tag'] as $tag) {
                $tagCount++;
            }
        }

        $this->eventDispatcher->dispatch(new GenericEvent($this, [
            'count'        => count($data),
            'countOverall' => count($data) + $tagCount,
            'context'      => '"' . $language->get($this->categoryDao->getCategoryTitle()) . '" (' . $this->categoryDao->get_category_const() . ')'
        ]), 'index.data.document.mapping.progress.start');

        foreach ($data as $row) {
            if (empty($row['tag'])) {
                continue;
            }

            foreach ($row['tag'] as $key => $tag) {
                $tagCount++;

                $metadata = new DocumentMetadata(
                    get_class($this->categoryDao),
                    $this->getIdentifier(),
                    $row['isys_obj__isys_obj_type__id'],
                    $row['isys_obj__id'],
                    $row['isys_obj__status'],
                    $this->categoryDao->getCategoryTitle(),
                    $row['isys_obj__id'],
                    $row['isys_obj__status'],
                    $property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]
                );

                $document = new Document($metadata);
                $document->setVersion(SearchEngine::VERSION);
                $document->setType('cmdb');
                $document->setKey($metadata->__toString() . '.' . $key);
                $document->setValue($tag);
                $document->setReference($row['isys_obj__id']);

                $documents[$document->getKey()] = $document;
            }

            $this->eventDispatcher->dispatch(new GenericEvent($this, [
                'steps' => count($row['tag'])
            ]), 'index.data.document.mapping.progress.advance');
        }

        $this->eventDispatcher->dispatch(new GenericEvent($this), 'index.data.document.mapping.progress.finish');

        return $documents;
    }
}
