<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Component\Table\Filter;

use idoit\Component\Table\Filter\Condition\CollectionCondition;
use idoit\Component\Table\Filter\Condition\ConstantCondition;
use idoit\Component\Table\Filter\Condition\InArrayCondition;
use idoit\Component\Table\Filter\Condition\IsEmptyCondition;
use idoit\Component\Table\Filter\Condition\IsSubstringCondition;
use idoit\Component\Table\Filter\Condition\NameToValueCondition;
use idoit\Component\Table\Filter\Condition\NotCondition;
use idoit\Component\Table\Filter\Condition\TypeCondition;
use idoit\Component\Table\Filter\Formatter\ConstantFormatter;
use idoit\Component\Table\Filter\Operation\AliasOrderByOperation;
use idoit\Component\Table\Filter\Operation\AsterixOperation;
use idoit\Component\Table\Filter\Operation\CustomOrderByOperation;
use idoit\Component\Table\Filter\Operation\DecimalStringOrderByOperation;
use idoit\Component\Table\Filter\Operation\DialogOrderByOperation;
use idoit\Component\Table\Filter\Operation\DialogSearchOperation;
use idoit\Component\Table\Filter\Operation\EqualsOperation;
use idoit\Component\Table\Filter\Operation\LikeOperation;
use idoit\Component\Table\Filter\Operation\HavingLikeOperation;
use idoit\Component\Table\Filter\Formatter\AppendFormatter;
use idoit\Component\Table\Filter\Formatter\CollectionFormatter;
use idoit\Component\Table\Filter\Formatter\IntFormatter;
use idoit\Component\Table\Filter\Formatter\PrependFormatter;
use idoit\Component\Table\Filter\Formatter\ReplaceFormatter;
use idoit\Component\Table\Filter\Formatter\SqlFormatter;
use idoit\Component\Table\Filter\Operation\LocationPathFilterOperation;
use idoit\Component\Table\Filter\Operation\LocationPathOrderByOperation;
use idoit\Component\Table\Filter\Operation\MultiselectOperation;
use idoit\Component\Table\Filter\Operation\OperationInterface;
use idoit\Component\Table\Filter\Operation\OperationOperation;
use idoit\Component\Table\Filter\Operation\OperationProvider;
use idoit\Component\Table\Filter\Operation\OrderByOperation;
use idoit\Component\Table\Filter\Operation\SkipOperation;
use idoit\Component\Table\Filter\Source\QueryMapSource;
use idoit\Component\Table\Filter\Source\QueryMultiValueSource;
use idoit\Component\Table\Filter\Source\QuerySource;
use idoit\Component\Table\Filter\Source\SessionSource;
use idoit\Component\Table\Filter\Source\SourceProvider;
use idoit\Component\Table\Filter\Source\TableConfigDefaultsPagingSource;
use idoit\Component\Table\Filter\Source\TableConfigDefaultsSource;
use idoit\Component\Table\Filter\Source\TableConfigDefaultsSortSource;
use idoit\Component\Table\Table;
use idoit\Module\Cmdb\Model\Ci\Table\Config;
use isys_cmdb_dao_list_objects;
use isys_usersettings;

/**
 * Class Configuration
 * TODO: replace with DI
 *
 * @package idoit\Component\Table\Filter
 */
class Configuration
{
    /**
     * Get the configuration for the filters operations
     *
     * @param isys_cmdb_dao_list_objects $dao
     *
     * @return OperationProvider
     */
    public static function configureFilters(isys_cmdb_dao_list_objects $dao)
    {
        $tableConfig = $dao->get_table_config();
        $operations = new OperationProvider($dao);

        $defaultFormatter = new CollectionFormatter();
        // We force the user to use "*" als wildchar by masking "%".
        $defaultFormatter->add(new ReplaceFormatter(['%', '*'], ['\%', '%']));
        // Add Wildcard on first position if in list config default wildcard is set
        if ($tableConfig->isFilterWildcard()) {
            $defaultFormatter->add(new PrependFormatter('%'));
        }
        $defaultFormatter->add(new AppendFormatter('%'));
        $defaultFormatter->add(new SqlFormatter());

        $skip = new SkipOperation();
        $skip->setCondition(new IsEmptyCondition());

        $asterix = new AsterixOperation($tableConfig, $operations);
        $operations->addOperation($asterix);

        $skipUnknown = new SkipOperation();
        $knownProperties = array_map(function ($prop) {
            return $prop->getPropertyKey();
        }, $tableConfig->getProperties());
        $skipUnknown->setCondition(new NotCondition(new InArrayCondition($knownProperties)));

        // If we filter by object title we can can add it to the where condition because obj_main is the root table
        // apply Table::DEFAULT_FILTER_FIELD as title like 'value%'
        $default = new LikeOperation();
        $default->setFormatter($defaultFormatter)
            ->setColumnFormatter(ConstantFormatter::create('obj_main.isys_obj__title'))
            ->setCondition(new InArrayCondition([Table::DEFAULT_FILTER_FIELD]));

        // default - having like 'value%'
        $fallback = new HavingLikeOperation();
        $fallback->setFormatter($defaultFormatter);

        // special handling for multiselect fields
        $multiselect = new MultiselectOperation();
        $multiselect->setFormatter($defaultFormatter)
            ->setCondition(new CollectionCondition([
                new CollectionCondition([
                    new NotCondition(new IsEmptyCondition()),
                    new TypeCondition($tableConfig, new IsSubstringCondition('multiselect'))
                ]),
                new IsSubstringCondition('isys_cmdb_dao_category_g_custom_fields__')
        ], false));

        $operation = new OperationOperation();
        $operation->setCondition(new InArrayCondition(['operation']));

        $locationFilter = new LocationPathFilterOperation();

        $operations->addOperation($operation);
        $operations->addOperation($skipUnknown);
        $operations->addOperation($default);
        $operations->addOperation($skip);
        $operations->addOperation(new DialogSearchOperation($fallback));
        $operations->addOperation($multiselect);
        $operations->addOperation($locationFilter);
        $operations->addOperation($fallback);

        return $operations;
    }

    /**
     * Facade for filter
     *
     * @param isys_cmdb_dao_list_objects $dao
     * @param                            $objectTypeID
     *
     * @return array
     */
    public static function filter(isys_cmdb_dao_list_objects $dao, $objectTypeID)
    {
        $filterSource = self::initFilterSource($dao->get_table_config(), $objectTypeID);
        $filter = $filterSource->fetch();

        $filterProvider = self::configureFilters($dao);
        $filterProvider->apply($filter);

        return $filter;
    }

    /**
     * Facade for sort
     *
     * @param isys_cmdb_dao_list_objects $dao
     *
     * @return array
     */
    public static function sort(isys_cmdb_dao_list_objects $dao, $objectTypeID)
    {
        $filterSource = self::initSortSource($dao->get_table_config(), $objectTypeID);
        $filter = $filterSource->fetch();

        $filterProvider = self::configureSort($dao);
        $filterProvider->apply($filter);

        return $filter;
    }

    /**
     * Get paging values
     *
     * @param isys_cmdb_dao_list_objects $dao
     * @param                            $objectTypeId
     *
     * @return array
     */
    public static function paging(isys_cmdb_dao_list_objects $dao, $objectTypeId)
    {
        // Initialize paging sources
        $pagingSource = self::initPagingSource($dao->get_table_config(), $objectTypeId);

        // Fetch paging value
        return $pagingSource->fetch();
    }

    /**
     * Configure the sort operations
     *
     * @param isys_cmdb_dao_list_objects $dao
     *
     * @return static
     */
    public static function configureSort(isys_cmdb_dao_list_objects $dao)
    {
        $config = $dao->get_table_config();
        $operations = new OperationProvider($dao);

        // if it's location path property
        $operations->addOperation(new LocationPathOrderByOperation());

        $skipUnknown = new SkipOperation();
        $knownProperties = array_map(function ($prop) {
            return $prop->getPropertyKey();
        }, $config->getProperties());
        $skipUnknown->setCondition(new NotCondition(new InArrayCondition($knownProperties)));
        $operations->addOperation($skipUnknown);

        // if isset data-sort-alias, sort by it
        $operations->addOperation(new AliasOrderByOperation());

        // if isset data-sort - sort by it
        $operations->addOperation(new CustomOrderByOperation());

        // if it's a dialog field
        $operations->addOperation(new DialogOrderByOperation());

        // sort text with "smart" order by
        // sort money with it as well, since it has the {currency,number,{n}} format and it's simplier
        $smartText = new DecimalStringOrderByOperation();
        $smartText->setCondition(new TypeCondition($config, new CollectionCondition([
            new NameToValueCondition(new IsEmptyCondition()),
            new IsSubstringCondition('text'),
            new IsSubstringCondition('money')
        ], false)));
        $smartText->setColumnFormatter(new ReplaceFormatter(['isys_cmdb_dao_category_g_global__'], ['obj_main.isys_obj__']));
        $operations->addOperation($smartText);

        // if its isys_cmdb_dao_category_g_global__ and not in table config columns - replace prefix to obj_main
        $replaceCondition = new CollectionCondition([
            new IsSubstringCondition('isys_cmdb_dao_category_g_global__'),
            // type is not found in table config
            new NotCondition(new TypeCondition($config, new ConstantCondition(true)))
        ]);
        $replace = new OrderByOperation();
        $replace->setCondition($replaceCondition);
        $replace->setColumnFormatter(new ReplaceFormatter(['isys_cmdb_dao_category_g_global__'], ['obj_main.isys_obj__']));
        $operations->addOperation($replace);

        // default - just sort
        $operations->addOperation(new OrderByOperation());

        return $operations;
    }

    public static function initSessionSource($objectTypeID)
    {
        $memorizeFilterTimer = (int)isys_usersettings::get('gui.objectlist.remember-filter', 300);

        return new SessionSource($memorizeFilterTimer, $objectTypeID);
    }

    /**
     * Configure the filters source. GET -> SESSION -> Table Config
     */
    public static function initFilterSource(Config $config, $objectTypeID)
    {
        $source = new SourceProvider();
        $source->addSource(new QuerySource('tableFilter'));
        $source->addSource(self::initSessionSource($objectTypeID));
        $source->addSource(new TableConfigDefaultsSource($config));

        return $source;
    }

    /**
     * Configure the sort source
     *
     * @param Config $config
     * @param        $objectTypeID
     *
     * @return SourceProvider
     */
    public static function initSortSource(Config $config, $objectTypeID)
    {
        $source = new SourceProvider();
        $queryMap = new QueryMapSource();
        $queryMap->add('orderBy', 'orderByDir');
        $source->addSource($queryMap);
        $source->addSource(self::initSessionSource('sort-' . $objectTypeID));
        $source->addSource(new TableConfigDefaultsSortSource($config));

        return $source;
    }

    /**
     * Initialize paging sources
     *
     * @param Config $config
     * @param        $objectTypeId
     *
     * @return SourceProvider
     */
    public static function initPagingSource(Config $config, $objectTypeId)
    {
        // Create source provider
        $source = new SourceProvider();

        // Create and configure source for query parameters
        $queryMultiValueSource = new QueryMultiValueSource(['page', 'rowsPerPage']);
        $queryMultiValueSource->setValidatorCallable(function ($parameterName, $parameterValue) {
            return is_numeric($parameterValue);
        });

        // Adding sources for paging
        $source->addSource($queryMultiValueSource);
        $source->addSource(self::initSessionSource('paging-' . $objectTypeId));
        $source->addSource(new TableConfigDefaultsPagingSource($config));

        return $source;
    }
}
