<?php
namespace idoit\Module\Report\SqlQuery\Structure;

use idoit\Module\Report\SqlQuery\Condition\Comparison\InCondition;
use idoit\Module\Report\SqlQuery\Condition\Comparison\LikeCondition;
use idoit\Module\Report\SqlQuery\Condition\Comparison\NotInCondition;
use idoit\Module\Report\SqlQuery\Condition\Comparison\NotNullCondition;
use idoit\Module\Report\SqlQuery\Condition\Comparison\NullCondition;
use idoit\Module\Report\SqlQuery\Condition\Filter\AbstractFilterProcessorValue;
use idoit\Module\Report\SqlQuery\Condition\Filter\Filter;
use idoit\Module\Report\SqlQuery\Condition\Filter\FilterProcessorConditionEmptyValue;
use isys_cmdb_dao;

/**
 * Container Class for a report query
 *
 * Class ReportQuery
 */
class ReportQuery
{
    /**
     * @var isys_cmdb_dao
     */
    private $dao;

    /**
     * @var string
     */
    private $query = '';

    /**
     * @var array
     */
    private $rootProperties = [];

    /**
     * @var array
     */
    private $subProperties = [];

    /**
     * @var array
     */
    private $conditions = [];

    /**
     * @var array
     */
    private $selections = [];

    /**
     * @var array
     */
    private $joins = [];

    /**
     * @var string
     */
    private $sorting = '';

    /**
     * @var int
     */
    private $limit;

    /**
     * @var Filter[]
     */
    private $dynamicFilter = [];

    /**
     * @var null
     */
    private $defaultSorting = null;

    /**
     * List of tables which should be joined at the end of the join list
     *
     * @var string[]
     */
    private $tablesToBeReordered = [
        'isys_catg_contact_list'
    ];

    /**
     * @return string
     */
    public function __toString()
    {
        if (empty($this->query)) {
            $this->buildQuery();
        }

        return $this->query;
    }

    /**
     * @return null
     */
    public function getDefaultSorting()
    {
        return $this->defaultSorting;
    }

    /**
     * @param null $defaultSorting
     *
     * @return ReportQuery
     */
    public function setDefaultSorting($defaultSorting)
    {
        $this->defaultSorting = $defaultSorting;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     *
     * @return ReportQuery
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return array
     */
    public function getRootProperties()
    {
        return $this->rootProperties;
    }

    /**
     * @param array $rootProperties
     *
     * @return ReportQuery
     */
    public function setRootProperties($rootProperties)
    {
        $this->rootProperties = $rootProperties;

        return $this;
    }

    /**
     * @return array
     */
    public function getSubProperties()
    {
        return $this->subProperties;
    }

    /**
     * @param array $subProperties
     *
     * @return ReportQuery
     */
    public function setSubProperties($subProperties)
    {
        $this->subProperties = $subProperties;

        return $this;
    }

    /**
     * @return array
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param array $conditions
     *
     * @return ReportQuery
     */
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * @param string $condition
     *
     * @return ReportQuery
     */
    public function addCondition($condition)
    {
        $this->conditions[] = $condition;

        return $this;
    }

    /**
     * @return array
     */
    public function getSelections()
    {
        return $this->selections;
    }

    /**
     * @param array $selections
     *
     * @return ReportQuery
     */
    public function setSelections($selections)
    {
        $this->selections = $selections;

        return $this;
    }

    /**
     * @param string $selection
     *
     * @return ReportQuery
     */
    public function addSelection($selection)
    {
        $this->selections[] = $selection;

        return $this;
    }

    /**
     * @return array
     */
    public function getJoins()
    {
        return $this->joins;
    }

    /**
     * @param array $joins
     *
     * @return ReportQuery
     */
    public function setJoins($joins)
    {
        $this->joins = $joins;

        return $this;
    }

    /**
     * @param string $alias
     * @param string $join
     *
     * @return ReportQuery
     */
    public function addJoin($alias, $join)
    {
        $this->joins[$alias] = $join;

        return $this;
    }

    /**
     * @return string
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * @param string $sorting
     *
     * @return ReportQuery
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;

        return $this;
    }

    /**
     * @return array
     */
    public function getDynamicFilter()
    {
        return $this->dynamicFilter;
    }

    /**
     * @param array $dynamicFilter
     *
     * @return ReportQuery
     */
    public function setDynamicFilter($dynamicFilter)
    {
        $this->dynamicFilter = $dynamicFilter;

        return $this;
    }

    /**
     * @param Filter $conditionFilter
     *
     * @return ReportQuery
     */
    public function addDynamicFilter(Filter $conditionFilter)
    {
        $this->dynamicFilter[] = $conditionFilter;
        return $this;
    }

    /**
     * @return isys_cmdb_dao
     */
    public function getDao()
    {
        return $this->dao;
    }

    /**
     * @param isys_cmdb_dao $dao
     *
     * @return ReportQuery
     */
    public function setDao($dao)
    {
        $this->dao = $dao;

        return $this;
    }

    /**
     * @param isys_cmdb_dao $dao
     * @param array         $rootProperties
     * @param array         $subProperties
     * @param array         $selections
     * @param array         $joins
     * @param array         $conditions
     * @param string        $sorting
     * @param null          $limit
     *
     * @return ReportQuery
     */
    public static function factory(isys_cmdb_dao $dao, array $rootProperties = [], array $subProperties = [], array $selections = [], array $joins = [], array $conditions = [], $sorting = '', $limit = null)
    {
        return (new self)
            ->setDao($dao)
            ->setRootProperties($rootProperties)
            ->setSubProperties($subProperties)
            ->setSelections($selections)
            ->setJoins($joins)
            ->setConditions($conditions)
            ->setSorting($sorting)
            ->setLimit($limit);
    }

    /**
     * Build the query
     *
     * @return ReportQuery
     */
    public function buildQuery()
    {
        $this->optimizeQuery();

        $query = "SELECT \n" . implode(", \n", $this->selections) . " \n\n" . " FROM isys_obj AS obj_main \n" . implode(" \n", $this->joins) . " \n\n" . "WHERE TRUE \n"
            . rtrim(implode(" \n", $this->conditions), 'AND OR') . "\n";

        $query .= $this->sorting;

        if ($this->limit > 0) {
            $query .= ' LIMIT 0, ' . $this->limit;
        }

        $this->setQuery($query);
        return $this;
    }

    /**
     * @return void
     * @throws \isys_exception_database
     */
    public function processDynamicFilters()
    {
        // Process Query and iterate through each entry
        if (!empty($this->dynamicFilter)) {
            $dynamicFilters = array_reverse($this->dynamicFilter);
            $sorting = $this->getSorting();

            $orderyByArr = [];
            foreach ($dynamicFilters as $filter) {
                $orderyByArr[] = $filter->getField();
            }

            $this->setSorting(' ORDER BY ' . implode(', ', $orderyByArr) . ' DESC ');
            if (empty($this->query)) {
                $this->buildQuery();
            }

            $result = $this->dao->retrieve($this->query);

            $this->setSorting($sorting)->buildQuery();

            $startTime = microtime(true);

            while ($row = $result->get_row()) {
                foreach ($dynamicFilters as $filter) {
                    $value = $row[$filter->getKey()];

                    if (empty($value) || $filter->getProcessor()->processedIdExists($value)) {
                        continue;
                    }

                    $filter->getProcessor()
                        ->setId($value);

                    $filter->process();

                    $filter->getProcessor()->addProcessedId($value);
                }
            }

            foreach ($dynamicFilters as $filter) {
                $this->query = str_replace('TRUE/*' . $filter->getKey() . '*/', $this->buildDynamicCondition($filter), $this->query);
            }
        }
    }

    /**
     * @param Filter $filter
     *
     * @return string
     */
    private function buildDynamicCondition(Filter $filter)
    {
        $processedValueIdsPositive = $filter->getProcessor()->getProcessedValueIdsPositive();
        $processedValueIdsNegative = $filter->getProcessor()->getProcessedValueIdsNegative();
        $additionalCondtionOperator = '';
        $additionalCondition = '';
        $condition = '';
        $conditionQuery = null;

        if (strpos($filter->getCondition()->getConditionField(), 'isys_obj__id') !== false) {
            $property = $filter->getCondition()->getProperty();
            $sourceTable = $property->getData()->getSourceTable();
            $callback = $property->getFormat()->getCallback();

            if ($callback[0] instanceof \isys_cmdb_dao_category) {
                $objectField = $callback[0]->get_object_id_field();
            } else {
                [$daoClass] = explode('::', $filter->getKey());
                $dao = $daoClass::instance(\isys_application::instance()->container->get('database'));
                $objectField = $dao->get_object_id_field();
            }

            if ($objectField === 'isys_obj__id') {
                $objectField = $sourceTable . '__isys_obj__id';
            }

            $conditionQuery = sprintf('SELECT %s FROM %s WHERE %s', $objectField, $sourceTable, $sourceTable . '__id');
        }

        if ($filter->getCondition() instanceof LikeCondition) {
            if (!empty($processedValueIdsPositive)) {
                $condition = (new InCondition())
                    ->setConditionValue(implode(',', $processedValueIdsPositive))
                    ->setConditionField(($conditionQuery ?: $filter->getCondition()->getConditionField()))
                    ->format();
            } elseif (!empty($processedValueIdsNegative)) {
                if ($conditionQuery !== '') {
                    return '(FALSE)';
                }

                $condition = (new NotInCondition())
                    ->setConditionValue(implode(',', $processedValueIdsNegative))
                    ->setConditionField($filter->getCondition()->getConditionField())
                    ->format();
            }

            if ($filter->getProcessor()->getProcessorConditionValue() instanceof FilterProcessorConditionEmptyValue) {
                $additionalCondition = (new NullCondition())
                    ->setConditionField($filter->getCondition()->getConditionField())
                    ->format();
                $additionalCondtionOperator = ' OR ';
            }

            if ($conditionQuery) {
                $condition = (new InCondition())
                    ->setConditionValue($condition)
                    ->setConditionField($filter->getCondition()->getConditionField())
                    ->format();
            }
        } else {
            if (!empty($processedValueIdsPositive)) {
                $conditionType = new NotInCondition();
                if ($conditionQuery) {
                    $conditionType = new InCondition();
                }

                $condition = $conditionType
                    ->setConditionValue(implode(',', $processedValueIdsPositive))
                    ->setConditionField(($conditionQuery ?: $filter->getCondition()->getConditionField()))
                    ->format();
            } elseif (!empty($processedValueIdsNegative)) {
                if ($conditionQuery) {
                    return 'TRUE';
                } else {
                    $condition = (new InCondition())->setConditionValue(implode(',', $processedValueIdsNegative))
                        ->setConditionField(($conditionQuery ?: $filter->getCondition()
                            ->getConditionField()))
                        ->format();
                }
            }

            if ($filter->getProcessor()->getProcessorConditionValue() instanceof FilterProcessorConditionEmptyValue) {
                $additionalCondition = (new NotNullCondition())
                    ->setConditionField($filter->getCondition()->getConditionField())
                    ->format();
                $additionalCondtionOperator = ' AND ';
            }

            if ($conditionQuery) {
                $condition = (new NotInCondition())
                    ->setConditionValue($condition)
                    ->setConditionField($filter->getCondition()->getConditionField())
                    ->format();
            }
        }

        if (!empty($condition)) {
            $condition = ' (' . $condition . ') ' . $additionalCondtionOperator . ' ' . $additionalCondition;
        } elseif ($additionalCondition !== '') {
            $condition = $additionalCondition;
        } else {
            $condition = 'FALSE';
        }

        return ' (' . $condition . ') ';
    }

    /**
     * Helper Method to extract the field for the order by clause
     *
     * @param string $select
     *
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function retrieveFieldFrom(string $select)
    {
        // We cannot retrieve the specific field from a select statement therefore we use the default "obj_main.isys_obj__title"
        if (strpos($select, 'SELECT') > 0) {
            return 'obj_main.isys_obj__title';
        } elseif (strpos($select, 'CASE') > 0) {
            if (preg_match('/(\S*)\.(\S*)/', $select, $matches)) {
                return $matches[0];
            }
        }

        return substr($select, 0, strpos($select, ' '));
    }

    /**
     * @param null|mixed $propertyDefaultSorting
     * @param string     $sortDirection
     *
     * @return $this
     */
    public function buildSorting($propertyDefaultSorting = null, $sortDirection = 'DESC')
    {
        $selection = $this->getSelections();
        if ($propertyDefaultSorting > 0) {
            $this->sorting = ' ORDER BY ' . $this->retrieveFieldFrom($selection[$propertyDefaultSorting]) . ' ' . $sortDirection;
        } else {
            foreach ($selection as $index => $selectionPart) {
                // @todo handle alias of the selection like in object lists then we can use the alias for the order
                if (strpos($selectionPart, 'SELECT') === false && strpos($selectionPart, 'CASE') === false && $index > 0) {
                    $this->sorting = ' ORDER BY ' . $this->retrieveFieldFrom($selectionPart) . ' ' . $sortDirection;
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * @return ReportQuery
     */
    public function optimizeQuery()
    {
        $newCondtions = $newSelectionList = $newJoinList = $aliasReplacements = [];
        $joinList = $this->getJoins();
        $selectionList = $this->getSelections();
        $conditions = $this->getConditions();

        foreach ($joinList as $alias => $join) {
            if (strpos($join, 'JOIN isys_obj AS') !== false && preg_match('/\.isys_obj__id\s=\s.*\.isys_obj__id/', $join, $matches)) {
                $joinParts = explode(' ', $join);
                foreach ($joinParts as $part) {
                    if (strpos($part, '.isys_obj__id') && $alias . '.isys_obj__id' !== $part) {
                        $replaceAlias = substr($part, 0, strpos($part, '.'));
                    }
                }
                $aliasReplacements[$alias] = $replaceAlias;
            }
        }

        if (empty($aliasReplacements)) {
            return $this;
        }

        foreach ($joinList as $alias => $join) {
            if (isset($aliasReplacements[$alias])) {
                continue;
            }

            $newJoinList[$alias] = $this->replaceAliasInQueryPart($join, $aliasReplacements);
        }

        $this->setJoins($newJoinList);

        foreach ($selectionList as $select) {
            $newSelectionList[] = $this->replaceAliasInQueryPart($select, $aliasReplacements);
        }

        foreach ($conditions as $condition) {
            $newCondtions[] = $this->replaceAliasInQueryPart($condition, $aliasReplacements);
        }

        $this->setSelections($newSelectionList);
        $this->setJoins($newJoinList);
        $this->setConditions($newCondtions);

        return $this;
    }

    /**
     * @param $value
     * @param $aliasReplacements
     *
     * @return string|string[]
     */
    private function replaceAliasInQueryPart($value, $aliasReplacements)
    {
        foreach ($aliasReplacements as $aliasSearch => $aliasReplacement) {
            if (strpos($value, $aliasSearch . '.') !== false) {
                $value = str_replace($aliasSearch . '.', $aliasReplacement . '.', $value);
            }
        }
        return $value;
    }
}
