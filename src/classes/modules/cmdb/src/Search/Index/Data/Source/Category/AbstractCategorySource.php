<?php

namespace idoit\Module\Cmdb\Search\Index\Data\Source\Category;

use idoit\Module\Cmdb\Search\Index\Data\CategoryCollector;
use idoit\Module\Search\Index\Data\Source\Config;
use idoit\Module\Search\Index\Data\Source\Indexable;
use idoit\Module\Search\Index\Document;
use idoit\Module\Search\Index\DocumentMetadata;
use idoit\Module\Search\Index\Engine\SearchEngine;
use isys_application;
use isys_cmdb_dao_category;
use isys_component_database;
use isys_exception_database_mysql;
use isys_tenantsettings;
use MySQL\Error\Server as MySQLServerErrors;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class AbstractCategorySource implements Indexable
{
    /**
     * @var isys_cmdb_dao_category
     */
    protected $categoryDao;

    /**
     * @var isys_component_database
     */
    protected $database;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Will contain the count of results from complex properties
     *
     * @var int
     */
    protected $complexPropertiesResultCount = 0;

    protected static $simpleTypes = [
        C__PROPERTY__INFO__TYPE__TEXT,
        C__PROPERTY__INFO__TYPE__COMMENTARY,
        C__PROPERTY__INFO__TYPE__DATE,
        C__PROPERTY__INFO__TYPE__DATETIME,
        C__PROPERTY__INFO__TYPE__INT,
        C__PROPERTY__INFO__TYPE__TEXTAREA
    ];

    protected static $complexTypes = [
        C__PROPERTY__INFO__TYPE__DIALOG => 'retrieveDataForSingleValue',
        C__PROPERTY__INFO__TYPE__DIALOG_PLUS => 'retrieveDataForSingleValue',
        //C__PROPERTY__INFO__TYPE__DIALOG_LIST => 'retrieveDataForMultiValue',
        //C__PROPERTY__INFO__TYPE__MULTISELECT => 'retrieveDataForMultiValue'
    ];

    /**
     * AbstractCategorySource constructor.
     *
     * @param isys_cmdb_dao_category  $categoryDao
     * @param isys_component_database $database
     */
    public function __construct(
        isys_cmdb_dao_category $categoryDao,
        isys_component_database $database
    ) {
        $this->categoryDao = $categoryDao;
        $this->database = $database;

        /**
         * @var $eventDispatcher EventDispatcherInterface
         */
        $eventDispatcher = isys_application::instance()->container->get('event_dispatcher');
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Get identifier for indexable data source
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->categoryDao->get_category_const();
    }

    /**
     * Retrieve data for index creation
     *
     * @param Config $config
     *
     * @return array
     */
    public function retrieveData(Config $config)
    {
        $language = isys_application::instance()->container->get('language');
        $selectFields = [];
        $checkEmptyFields = [];
        $sourceTable = null;
        $dataSets = [];
        $complexPropertiesResultCount = 0;

        foreach ($this->categoryDao->get_properties() as $name => $property) {
            if (
                in_array($property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE], self::$simpleTypes) &&
                $property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VIRTUAL] === false &&
                $property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH] === true
            ) {
                // For simple types the source table has to be the same for every property
                $sourceTable = $this->categoryDao->get_table() ?: (!empty($property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS])
                    ? $property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS]
                    : 'isys_catg_' . $this->categoryDao->get_category() . '_list');

                if ($sourceTable == 'isys_catg_global_list') {
                    $sourceTable = 'isys_obj';
                }
                $selectFields[] = $sourceTable . '.' . $property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];

                // Checking for empty text fields
                if ($property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__TEXT ||
                    $property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__TEXTAREA ||
                    $property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__COMMENTARY) {
                    $checkEmptyFields[] = $sourceTable . '.' . $property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] . ' <> \'\'';
                }
            }

            if (
                isset(self::$complexTypes[$property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE]]) &&
                $property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VIRTUAL] === false &&
                (
                    $property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH] === true ||
                    $property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH_INDEX] === true
                )
            ) {
                $function = self::$complexTypes[$property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE]];

                try {
                    $dataSets[$property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] = $this->{$function}($property, $config->getObjectIds(), $config->getCategoryIds());

                    if (is_countable($dataSets[$property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]])) {
                        $this->complexPropertiesResultCount += count($dataSets[$property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]]);
                    }
                } catch (isys_exception_database_mysql $exception) {
                    if ($exception->getCode() === MySQLServerErrors::ER_BAD_FIELD_ERROR) {
                        $this->eventDispatcher->dispatch(new GenericEvent($this, [
                            'exception' => $exception,
                            'message' => sprintf(
                                'Complex fields are missing (e.g. dialog plus) for category "%s" (%s), complex fields will be skipped',
                                $this->categoryDao->getCategoryTitle(),
                                $this->categoryDao->get_category_const()
                            ),
                            'verbosity' => OutputInterface::VERBOSITY_NORMAL
                        ]), 'index.error');

                        $this->eventDispatcher->dispatch(new GenericEvent($this, [
                            'exception' => $exception,
                            'message' => $exception->getMessage(),
                            'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE
                        ]), 'index.error');
                    }
                } catch (\Exception $exception) {
                    $this->eventDispatcher->dispatch(new GenericEvent($this, [
                        'exception' => $exception,
                        'message' => sprintf(
                            'An error occured for category "%s" (%s) on complex fields (e.g. dialog plus), complex fields will be skipped',
                            $this->categoryDao->getCategoryTitle(),
                            $this->categoryDao->get_category_const()
                        ),
                        'verbosity' => OutputInterface::VERBOSITY_NORMAL
                    ]), 'index.error');
                }
            }
        }

        if (empty($selectFields) && $this->complexPropertiesResultCount === 0) {
            return [];
        }

        // SQL for collecting data from simple properties, when possible with empty checks for every property
        $sql = sprintf(
            'SELECT %s.%s__id, %s.%s__status, %s, obj.isys_obj__id, obj.isys_obj__isys_obj_type__id, obj.isys_obj__status FROM %s INNER JOIN isys_obj obj ON (%s = obj.isys_obj__id) WHERE obj.isys_obj__isys_obj_type__id NOT IN (%s)',
            $sourceTable,
            $sourceTable,
            $sourceTable,
            $sourceTable,
            implode(', ', $selectFields),
            $sourceTable,
            $sourceTable === 'isys_obj' ? 'isys_obj.isys_obj__id' : $sourceTable . '.' . $sourceTable . '__isys_obj__id',
            implode(', ', filter_defined_constants(CategoryCollector::BLACKLISTED_OBJECT_TYPES))
        );

        if ($config->hasObjectIds()) {
            $sql .= ' AND obj.isys_obj__id IN (' . implode(', ', $config->getObjectIds()) . ')';
        }

        if (!empty($checkEmptyFields)) {
            $sql .= sprintf(' AND (%s)', implode(' OR ', $checkEmptyFields));
        }

        if (!isys_tenantsettings::get('search.index.include_archived_deleted_objects', false)) {
            $sql .= ' AND obj.isys_obj__status = '  . C__RECORD_STATUS__NORMAL;
            $sql .= ' AND ' . $sourceTable . '.' . $sourceTable . '__status = '  . C__RECORD_STATUS__NORMAL;
        }

        if ($config->hasCategoryIds()) {
            $sql .= ' AND ' . $sourceTable . '.' . $sourceTable . '__id IN ('  . implode(', ', $config->getCategoryIds()) . ')';
        }

        $this->eventDispatcher->dispatch(new GenericEvent($this, [
            'sql' => $sql
        ]), 'index.data.raw.execute_sql');

        try {
            $resource = $this->database->query($sql);
        } catch (isys_exception_database_mysql $exception) {
            if ($exception->getCode() === MySQLServerErrors::ER_BAD_FIELD_ERROR) {
                $this->eventDispatcher->dispatch(new GenericEvent($this, [
                    'exception' => $exception,
                    'message' => sprintf(
                        'Fields are missing for category "%s" (%s), category will be skipped',
                        $this->categoryDao->getCategoryTitle(),
                        $this->categoryDao->get_category_const()
                    ),
                    'verbosity' => OutputInterface::VERBOSITY_NORMAL
                ]), 'index.error');

                $this->eventDispatcher->dispatch(new GenericEvent($this, [
                    'exception' => $exception,
                    'message' => $exception->getMessage(),
                    'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE
                ]), 'index.error');

                return [];
            }
        } catch (\Exception $exception) {
            $this->eventDispatcher->dispatch(new GenericEvent($this, [
                'exception' => $exception,
                'message' => sprintf(
                    'An error occured for category "%s" (%s), category will be skipped',
                    $this->categoryDao->getCategoryTitle(),
                    $this->categoryDao->get_category_const()
                ),
                'verbosity' => OutputInterface::VERBOSITY_NORMAL
            ]), 'index.error');

            return [];
        }

        // Return data when at least there is one result for and/or simple and complex properties
        if ($this->complexPropertiesResultCount !== 0 || $this->database->num_rows($resource) !== 0) {
            $data = [];

            $this->eventDispatcher->dispatch(new GenericEvent($this, [
                'count'   => $this->database->num_rows($resource) + $this->complexPropertiesResultCount,
                'context' => '"' . $language->get($this->categoryDao->getCategoryTitle()) . '" (' . $this->categoryDao->get_category_const() . ')'
            ]), 'index.data.raw.progress.start');

            while ($row = $this->database->fetch_row_assoc($resource)) {
                $data[] = $row;
                $this->eventDispatcher->dispatch(new GenericEvent($this), 'index.data.raw.progress.advance');
            }

            $this->eventDispatcher->dispatch(new GenericEvent($this, [
                'count' => $this->database->num_rows($resource) + $this->complexPropertiesResultCount
            ]), 'index.data.raw.progress.finish');

            return array_merge($data, $dataSets);
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
        $documents = [];
        $documentProperties = [];
        $sourceTable = null;
        $complexPropertiesCount = 0;
        $complexTypesSourceTable = [];
        $complexTypesFields = [];

        foreach ($this->categoryDao->get_properties() as $name => $property) {
            $sourceTable = $this->categoryDao->get_table() ?: (!empty($property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS]) ? $property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] : 'isys_catg_' .
                $this->categoryDao->get_category() . '_list');
            if ($sourceTable == 'isys_catg_global_list') {
                $sourceTable = 'isys_obj';
            }

            if (in_array($property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE], self::$simpleTypes) && $property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VIRTUAL] === false &&
                $property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH] === true) {
                $documentProperties[$property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] = $name;
            }

            if (
                isset(self::$complexTypes[$property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE]]) &&
                $property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VIRTUAL] === false &&
                (
                    $property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH] === true ||
                    $property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH_INDEX] === true
                )
            ) {
                $documentProperties[$property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] = $name;
                $complexPropertiesCount++;
                $complexTypesSourceTable[$property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] = $sourceTable;
                $complexTypesFields[$property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] = $property[C__PROPERTY__DATA][C__PROPERTY__DATA__SOURCE_TABLE];
            }
        }

        $this->eventDispatcher->dispatch(new GenericEvent($this, [
            'count'        => count($data) + $this->complexPropertiesResultCount - $complexPropertiesCount,
            'countOverall' => count($data) * count($documentProperties) + $this->complexPropertiesResultCount - $complexPropertiesCount,
            'context'      => '"' . $language->get($this->categoryDao->getCategoryTitle()) . '" (' . $this->categoryDao->get_category_const() . ')'
        ]), 'index.data.document.mapping.progress.start');

        $skipped = 0;
        $documentsMappedCount = 0;

        /**
         * Index can either be a complex type field, or just the index number
         */
        foreach ($data as $index => $set) {
            foreach ($documentProperties as $column => $property) {
                $propertyData = $this->categoryDao->get_property_by_key($property);

                $documentsMappedCount++;
                if (empty($set[$column]) && !($index === $column)) {
                    $this->eventDispatcher->dispatch(new GenericEvent($this), 'index.data.document.mapping.progress.advance');
                    $skipped++;
                    continue;
                }

                if ($index === $column) {
                    foreach ($set as $complexSet) {
                        $metadata = new DocumentMetadata(
                            get_class($this->categoryDao),
                            $this->getIdentifier(),
                            $complexSet['isys_obj__isys_obj_type__id'],
                            $complexSet['isys_obj__id'],
                            $complexSet['isys_obj__status'],
                            $this->categoryDao->getCategoryTitle(),
                            $complexSet[$sourceTable . '__id'],
                            $complexSet[$sourceTable . '__status'],
                            $propertyData[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]
                        );

                        $document = new Document($metadata);
                        $document->setVersion(SearchEngine::VERSION);
                        $document->setType('cmdb');
                        $document->setKey($metadata->__toString());
                        $document->setValue($language->get($complexSet[$complexTypesFields[$column] . '__title']));
                        $document->setReference($complexSet['isys_obj__id']);

                        $documents[$document->getKey()] = $document;
                        $this->eventDispatcher->dispatch(new GenericEvent($this), 'index.data.document.mapping.progress.advance');
                        $documentsMappedCount++;
                    }

                    $documentsMappedCount--;
                } else {
                    $metadata = new DocumentMetadata(
                        get_class($this->categoryDao),
                        $this->getIdentifier(),
                        $set['isys_obj__isys_obj_type__id'],
                        $set['isys_obj__id'],
                        $set['isys_obj__status'],
                        $this->categoryDao->getCategoryTitle(),
                        $set[$sourceTable . '__id'],
                        $set[$sourceTable . '__status'],
                        $propertyData[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]
                    );

                    $document = new Document($metadata);
                    $document->setVersion(SearchEngine::VERSION);
                    $document->setType('cmdb');
                    $document->setKey($metadata->__toString());
                    $document->setValue($set[$column]);
                    $document->setReference($set['isys_obj__id']);

                    $documents[$document->getKey()] = $document;
                    $this->eventDispatcher->dispatch(new GenericEvent($this), 'index.data.document.mapping.progress.advance');
                }
            }
        }

        $this->eventDispatcher->dispatch(new GenericEvent($this), 'index.data.document.mapping.progress.finish');

        $this->eventDispatcher->dispatch(new GenericEvent($this, [
            'total' => $documentsMappedCount,
            'skipped' => $skipped
        ]), 'index.data.document.mapping.progress.skipped');

        return $documents;
    }

    /**
     * Will retrieve data for complex property, filtered by given objectIds
     *
     * @param array|\ArrayAccess $property
     * @param array              $objectIds
     * @param array              $categoryIds
     *
     * @return array
     */
    private function retrieveDataForSingleValue($property, array $objectIds, array $categoryIds)
    {
        $dialogField = $property[C__PROPERTY__DATA][C__PROPERTY__DATA__SOURCE_TABLE];

        $condition = ' AND ' . $dialogField . '__title' . ' <> \'\'';

        if (!empty($categoryIds)) {
            $condition .= ' AND isys_catg_' . $this->categoryDao->get_category() . '_list__id IN (' . implode(', ', $categoryIds) . ')';
        }

        try {
            $categoryData = $this->categoryDao->get_data(
                null,
                (!empty($objectIds) ? $objectIds : null),
                $condition
            )->__as_array();
        } catch (\Exception $exception) {
            return [];
        }

        $dataSets = [];

        $sourceTable = $this->categoryDao->get_table() ?: (!empty($property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS]) ? $property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] : 'isys_catg_' .
            $this->categoryDao->get_category() . '_list');
        if ($sourceTable == 'isys_catg_global_list') {
            $sourceTable = 'isys_obj';
        }

        foreach ($categoryData as $row) {
            $dataSets[] = [
                'isys_obj__isys_obj_type__id' => $row['isys_obj__isys_obj_type__id'],
                'isys_obj__id' => $row['isys_obj__id'],
                $dialogField. '__title' => $row[$dialogField. '__title'],
                $sourceTable. '__id' => $row[$sourceTable. '__id'],
            ];
        }

        return $dataSets;
    }
}
