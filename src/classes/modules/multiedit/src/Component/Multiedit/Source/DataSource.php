<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Source;

use idoit\Component\Property\Property;
use idoit\Exception\Exception;
use idoit\Module\Multiedit\Component\Filter\CategoryFilter;
use idoit\Module\Multiedit\Component\Filter\DataSourceFilter;
use idoit\Module\Multiedit\Component\Multiedit\Exception\EmptySourceDaoException;
use idoit\Module\Multiedit\Component\Multiedit\Exception\DataSourceFormatterException;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterManager;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterInterface;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Value;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ValueFormatter;
use idoit\Module\Multiedit\Model\CustomCategories;
use idoit\Module\Multiedit\Model\GlobalCategories;
use idoit\Module\Multiedit\Model\SpecificCategories;
use isys_cmdb_dao_category_g_custom_fields;
use isys_cmdb_dao_category;
use isys_application;

/**
 * Class DataSource
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Source
 */
class DataSource extends Source
{

    /**
     * @var Property[]
     */
    protected $formattedProperties;

    /**
     * @var array
     */
    protected $objectIds = [];

    /**
     * @var DataSourceFilter
     */
    protected $dataSourceFilter;

    /**
     * @var string
     */
    protected $authCondition = '';

    /**
     * @param string $authCondition
     *
     * @return DataSource
     */
    public function setAuthCondition($authCondition)
    {
        $this->authCondition = $authCondition;

        return $this;
    }

    /**
     * @param Property[] $formattedProperties
     *
     * @return DataSource
     */
    public function setFormattedProperties($formattedProperties)
    {
        $this->formattedProperties = $formattedProperties;

        return $this;
    }

    /**
     * @param $objectIds
     *
     * @return $this
     */
    public function setObjectIds($objectIds)
    {
        $this->objectIds = $objectIds;

        return $this;
    }

    /**
     * @param DataSourceFilter $dataSourceFilter
     *
     * @return DataSource
     */
    public function setDataSourceFilter($dataSourceFilter)
    {
        $this->dataSourceFilter = $dataSourceFilter;

        return $this;
    }

    /**
     * Format data for the list
     *
     * @return $this|mixed
     * @throws EmptySourceDaoException
     */
    public function formatData()
    {
        /**
         * @var $dao        isys_cmdb_dao_category
         * @var $property   Property
         * @var $properties Property[]
         */
        if (empty($this->getDao())) {
            throw new EmptySourceDaoException('No category is defined for the DataSource.');
        }

        $dao = current($this->getDao());
        $database = isys_application::instance()->container->get('database');

        $sourceTable = $dao->get_table();
        $categoryType = $dao->get_category_type();
        $categoryId = $dao->get_category_id();
        $categoryConst = $dao->get_category_const();

        if ($dao instanceof isys_cmdb_dao_category_g_custom_fields) {
            $categoryType = defined_or_default('C__CMDB__CATEGORY__TYPE_CUSTOM');
            $dao->set_category_type($categoryType);
            $categoryId = $dao->get_catg_custom_id();
            $categoryConst = $dao->get_catg_custom_const();
        }

        $specificCategoryModel = new SpecificCategories($database);
        $globalCategoryModel = new GlobalCategories($database);
        $customCategoryModel = new CustomCategories($database);
        $filter = new CategoryFilter();
        $filter->setCategories([$categoryId]);

        try {
            $objectsCategoryNotAssigned = [];
            $objectsNoEntries = [];
            $objectsWithEntries = [];
            $objectsNotAllowed = [];
            $allowedObjects = null;

            if ($this->authCondition != '') {
                $authQuery = 'SELECT chk.isys_obj__id as id 
                  FROM (SELECT isys_obj__id FROM isys_obj WHERE TRUE ' . $this->authCondition . ') as chk
                WHERE chk.isys_obj__id IN (' . implode(',', $this->objectIds) . ');';
                $result = $dao->retrieve($authQuery);
                $allowedObjects = [];
                if (count($result)) {
                    while ($row = $result->get_row()) {
                        $allowedObjects[$row['id']] = true;
                    }
                }
            }

            foreach ($this->objectIds as $objectId) {
                $isCategoryAllowed = true;

                try {
                    \isys_auth_cmdb_categories::instance()
                        ->check_rights_obj_and_category(\isys_auth::EDIT, $objectId, $categoryConst);
                } catch (\isys_exception_auth $e) {
                    $isCategoryAllowed = false;
                }

                if ((($allowedObjects !== null) && !isset($allowedObjects[$objectId])) || !$isCategoryAllowed) {
                    $objectsNotAllowed[$objectId] = true;
                    continue;
                }

                $result = $dao->get_data(null, $objectId, '', null, C__RECORD_STATUS__NORMAL);
                $filter->setObjects([$objectId]);

                switch ($categoryType) {
                    case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                        $check = (bool)$specificCategoryModel->resetCount()
                            ->setFilter($filter)
                            ->setData()
                            ->count();
                        break;
                    case C__CMDB__CATEGORY__TYPE_CUSTOM:
                        $check = (bool)$customCategoryModel->resetCount()
                            ->setFilter($filter)
                            ->setData()
                            ->count();
                        break;
                    case C__CMDB__CATEGORY__TYPE_GLOBAL:
                    default:
                        $check = (bool)$globalCategoryModel->resetCount()
                            ->setFilter($filter)
                            ->setData()
                            ->count();
                        break;
                }

                if (!$check) {
                    $objectsCategoryNotAssigned[$objectId] = false;
                    continue;
                }

                if (count($result)) {
                    $objectsWithEntries[$objectId] = $result;
                    continue;
                }

                $objectsNoEntries[$objectId] = [];
            }

            /*
             * Sorted array first all objects with no assignment to the category second all objects with no entries and last the rest
             * This is better than using uasort 2 times
             */
            $objects = $objectsNotAllowed + $objectsCategoryNotAssigned + $objectsNoEntries + $objectsWithEntries;
            $this->objectIds = array_keys($objects);

            foreach ($objects as $objectId => $result) {
                $this->data[$objectId] = [];

                if ($result === true) {
                    $this->data[$objectId] = true;
                    continue;
                }

                if ($result === false) {
                    $this->data[$objectId] = false;
                    continue;
                }

                if (count($result)) {
                    if ($categoryType === C__CMDB__CATEGORY__TYPE_CUSTOM) {
                        $this->handleCustomCategory($objectId, $sourceTable, $result);
                    } else {
                        $this->handleCategory($objectId, $sourceTable, $result);
                    }
                }
            }
            unset($objectIds);
        } catch (\Exception $e) {
            throw new DataSourceFormatterException($e->getMessage());
        }

        return $this;
    }

    /**
     * @param $objectId
     * @param $sourceTable
     * @param $result
     */
    private function handleCustomCategory($objectId, $sourceTable, $result)
    {
        $filterProperty = $this->dataSourceFilter->getProperty();
        $filterValue = $this->dataSourceFilter->getValue();
        $this->data[$objectId] = [];

        while ($data = $result->get_row()) {
            if (!isset($this->data[$objectId][$data['isys_catg_custom_fields_list__data__id']])) {
                $this->data[$objectId][$data['isys_catg_custom_fields_list__data__id']] = [];
            }

            foreach ($this->formattedProperties as $categoryTitle => $properties) {
                foreach ($properties as $propertyKey => $property) {
                    // isys_cmdb_dao_category_g_custom_fields__f_popup_c_1499081447925
                    $checkKey = 'isys_cmdb_dao_category_g_custom_fields__' . $data['isys_catg_custom_fields_list__field_type'] . '_' .
                        $data['isys_catg_custom_fields_list__field_key'];

                    if ($checkKey !== $propertyKey) {
                        continue;
                    }

                    $formatter = FormatterManager::getFormatterByProperty($propertyKey);

                    if ($formatter === false) {
                        $formatter = FormatterManager::getFormatterByUiType($property->getUi()
                            ->getType());
                    }

                    $formatterValue = (new ValueFormatter())->setPropertyKey($propertyKey)
                        ->setProperty($property)
                        ->setValue((new Value())->setValue($data['isys_catg_custom_fields_list__field_content']))
                        ->setObjectId($objectId)
                        ->setEntryId($data['isys_catg_custom_fields_list__data__id'])
                        ->setRawDataset($data);

                    $value = $formatter::formatSource($formatterValue);

                    if (isset($this->data[$objectId][$data['isys_catg_custom_fields_list__data__id']][$propertyKey])) {
                        $val = $value->getValue();
                        $viewVal = $value->getViewValue();

                        $this->data[$objectId][$data['isys_catg_custom_fields_list__data__id']][$propertyKey]->appendValue($val);
                        $this->data[$objectId][$data['isys_catg_custom_fields_list__data__id']][$propertyKey]->appendViewValue($viewVal);
                    } else {
                        $this->data[$objectId][$data['isys_catg_custom_fields_list__data__id']][$propertyKey] = $value;
                    }
                }
            }
        }
        $this->incrementCount();
    }

    /**
     * @param $objectId
     * @param $sourceTable
     * @param $result
     */
    private function handleCategory($objectId, $sourceTable, $result)
    {
        $filterProperty = $this->dataSourceFilter->getProperty();
        $filterValue = $this->dataSourceFilter->getValue();

        while ($data = $result->get_row()) {
            foreach ($this->formattedProperties as $categoryTitle => $properties) {
                foreach ($properties as $propertyKey => $property) {
                    $formatter = FormatterManager::getFormatterByProperty($propertyKey);

                    if ($formatter === false) {
                        $formatter = FormatterManager::getFormatterByUiType($property->getUi()
                            ->getType());
                    }

                    $formatterValue = (new ValueFormatter())->setPropertyKey($propertyKey)
                        ->setProperty($property)
                        ->setValue((new Value())->setValue($data[$property->getData()
                            ->getField()]))
                        ->setObjectId($objectId)
                        ->setEntryId($data[$sourceTable . '__id'])
                        ->setRawDataset($data);

                    if ($property->getDependency()
                            ->getPropkey() || ($callbackProperty = ($property->getFormat()
                            ->getUnit() ?: $property->getFormat()
                            ->getRequires()))) {
                        list($class, ) = explode('__', $propertyKey);

                        $referencedPropertyKey = $class . '__' . ($callbackProperty ?: $property->getDependency()
                                ->getPropkey());

                        $formatterValue->setReferencedPropertyKey($referencedPropertyKey)
                            ->setReferencedProperty($properties[$referencedPropertyKey])
                            ->setReferencedPropertyValue((new Value())->setValue($data[$properties[$referencedPropertyKey]->getData()
                                ->getField()]));
                    }

                    $value = $formatter::formatSource($formatterValue);

                    // @todo Check Filter
                    if ($filterProperty) {
                    }

                    $this->data[$objectId][$data[$sourceTable . '__id']][$propertyKey] = $value;
                }
                $this->incrementCount();
            }
        }
    }
}
